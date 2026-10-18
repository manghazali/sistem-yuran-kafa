# Panduan Deploy ke DigitalOcean

Panduan ini untuk deploy Sistem Yuran KAFA ke satu DigitalOcean Droplet
menggunakan Docker. Anggaran kos: **Droplet asas USD6/bulan** (1 vCPU, 1GB
RAM) sudah memadai untuk trafik sekolah yang kecil.

## 0. Prasyarat

- Akaun DigitalOcean (digitalocean.com — daftar kad kredit/debit).
- Domain yang anda miliki (untuk SSL automatik melalui Caddy). Tanpa domain,
  anda masih boleh akses melalui IP tetapi TANPA HTTPS.
- Repo ini sudah di-push ke GitHub (`origin` sudah ditetapkan ke
  `github.com:manghazali/sistem-yuran-kafa.git`) — **PENTING**: semua kerja
  terkini perlu di-commit & push dahulu sebelum droplet boleh `git pull`
  kod yang betul. Beritahu saya bila anda nak saya commit & push.

## 1. Cipta Droplet

1. DigitalOcean → **Create** → **Droplets**.
2. **Image**: Ubuntu 24.04 (LTS) x64.
3. **Plan**: Basic → Regular → $6/mo (1 vCPU / 1GB) cukup untuk mula; boleh
   naik taraf kemudian tanpa buat semula droplet.
4. **Region**: pilih yang paling dekat dengan majoriti pengguna (cth:
   Singapore).
5. **Authentication**: SSH Key (disyorkan, lebih selamat daripada kata
   laluan) — upload kunci awam (`~/.ssh/id_ed25519.pub` atau sama) anda.
6. Create — catat alamat IP droplet selepas siap (contoh: `178.128.x.x`).

## 2. Tetapkan DNS (jika ada domain)

Di pembekal domain anda, tambah rekod:

| Jenis | Nama | Nilai            |
|-------|------|-------------------|
| A     | @    | IP droplet anda   |
| A     | www  | IP droplet anda   |

Tunggu ~5-30 minit untuk propagasi DNS sebelum teruskan ke langkah SSL.

## 3. Persediaan Awal Server

SSH masuk sebagai root buat kali pertama:

```bash
ssh root@IP_DROPLET_ANDA
```

Cipta pengguna bukan-root dan pasang firewall asas:

```bash
adduser deploy
usermod -aG sudo deploy
ufw allow OpenSSH
ufw allow 80
ufw allow 443
ufw enable
```

Log keluar, log masuk semula sebagai `deploy` untuk langkah selebihnya.

## 4. Pasang Docker

```bash
curl -fsSL https://get.docker.com | sudo sh
sudo usermod -aG docker $USER
newgrp docker
```

Sahkan: `docker --version` dan `docker compose version`.

## 5. Clone Repo

```bash
git clone git@github.com:manghazali/sistem-yuran-kafa.git
cd sistem-yuran-kafa
```

(Jika SSH key GitHub belum ditetapkan pada droplet, guna HTTPS clone
sebagai alternatif: `git clone https://github.com/manghazali/sistem-yuran-kafa.git`)

## 6. Konfigurasi .env

```bash
cp .env.production.example .env
nano .env
```

Isi SEMUA nilai `GANTI_DENGAN_...`:
- `APP_URL` dan `APP_DOMAIN` — domain sebenar anda.
- `DB_PASSWORD` — kata laluan kuat baharu (bukan `password` macam local).
- `MAIL_PASSWORD` — API key Resend (sama atau baharu — **wajib guna domain
  yang disahkan di Resend untuk `MAIL_FROM_ADDRESS`, bukan
  `onboarding@resend.dev`**, supaya boleh hantar emel kepada sesiapa sahaja,
  bukan setakat emel akaun Resend sendiri).
- `PAYMENT_GATEWAY_MERCHANT_ID` / `PAYMENT_GATEWAY_SECRET_KEY` — kelayakan
  PRODUCTION sebenar daripada pembekal gerbang pembayaran anda (bukan
  sandbox), dan tukar `PAYMENT_GATEWAY_SANDBOX=false`.

Jana `APP_KEY` baharu (JANGAN guna APP_KEY dari local):

```bash
docker run --rm -v "$PWD":/app -w /app composer:2 sh -c \
  "php -r \"echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;\""
```

Salin output itu ke `APP_KEY=` dalam `.env`.

## 7. Bina & Jalankan

```bash
docker compose -f docker-compose.prod.yml up -d --build
```

Ini akan bina imej production (bukan imej Sail dev), dan mulakan 4
perkhidmatan: `app` (Caddy + PHP-FPM, port 80/443), `queue`, `scheduler`,
`mysql`.

## 8. Migrasi Pangkalan Data

```bash
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
```

Jika anda ada seeder data awal (kategori yuran, akaun admin pertama, dsb):

```bash
docker compose -f docker-compose.prod.yml exec app php artisan db:seed --force
```

## 9. Sahkan

- Lawat `https://domain-anda.com` — Caddy akan automatik dapatkan sijil
  SSL Let's Encrypt pada percubaan pertama (ambil beberapa saat).
- Log masuk dengan akaun admin.
- Semak queue berfungsi: `docker compose -f docker-compose.prod.yml logs queue --tail 50`.
- Semak scheduler berfungsi: `docker compose -f docker-compose.prod.yml logs scheduler --tail 50`.

## Kemas Kini / Deploy Semula

Apabila ada perubahan kod baharu (selepas `git push` dari local):

```bash
cd sistem-yuran-kafa
git pull
docker compose -f docker-compose.prod.yml up -d --build
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
```

`--build` akan bina semula imej dan mulakan semula semua kontena (termasuk
`php-fpm`/`caddy` dalam `app`) — ini PERLU kerana `opcache.validate_timestamps=0`
bermakna PHP tidak semak fail berubah secara automatik; mesti restart
kontena untuk kod baharu berkuat kuasa.

## Sandaran (Backup)

Pangkalan data tersimpan dalam Docker volume `mysql-data`. Jadualkan
sandaran berkala (contoh, cron harian):

```bash
docker compose -f docker-compose.prod.yml exec mysql \
  mysqldump -u root -p"$DB_PASSWORD" laravel > backup-$(date +%Y%m%d).sql
```

Simpan salinan `backup-*.sql` di luar droplet (cth: muat naik ke DigitalOcean
Spaces/S3) — jangan bergantung semata-mata pada droplet yang sama.

## Had Diketahui / Perkara Belum Dibuat

- **Tiada redis** — sesi/cache/queue guna pemacu `database` (cukup untuk
  skala kecil ini; boleh naik taraf ke Redis kemudian jika perlu).
- **MySQL dalam kontena yang sama** — untuk production lebih besar/kritikal,
  pertimbang DigitalOcean Managed Database (automatik backup & failover)
  dan bukan kontena MySQL sendiri.
- **Tiada monitoring/alerting** automatik ditetapkan (cth: Uptime Kuma,
  Sentry) — boleh ditambah kemudian jika diperlukan.
