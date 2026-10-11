# FASA 1 — Sistem Pengurusan Yuran Pelajar KAFA
## Projek Laravel + Pengesahan (Login) + Peranan & Kebenaran + Pangkalan Data

Pakej ini menyambung terus dari setup Docker/Sail yang anda sudah jalankan
(`sistem-yuran-kafa`). Ia mengandungi **fail custom sahaja** — bukan seluruh
projek Laravel — supaya anda gabungkan dengan projek Sail sedia ada.

---

## 1. Pastikan projek Sail anda sudah berjalan

Jika belum, dari folder `sistem-yuran-kafa`:

```powershell
.\vendor\bin\sail up -d
```

Jika Breeze belum dipasang:

```powershell
.\vendor\bin\sail composer require laravel/breeze --dev
.\vendor\bin\sail artisan breeze:install blade
.\vendor\bin\sail npm install
.\vendor\bin\sail npm run build
```

## 2. Pasang Spatie laravel-permission

```powershell
.\vendor\bin\sail composer require spatie/laravel-permission
.\vendor\bin\sail artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

## 3. Kemaskini `.env`

Sail sudah set `DB_HOST=mysql` secara automatik. Tambah/kemaskini baris ini
sahaja:

```env
APP_NAME="SISTEM PENGURUSAN YURAN PELAJAR KAFA"
APP_LOCALE=ms
```

## 4. Salin fail-fail dalam pakej ini ke projek anda

| Fail dalam pakej ini | Destinasi dalam `sistem-yuran-kafa/` |
|---|---|
| `database/migrations/2024_01_01_000001_add_profile_fields_to_users_table.php` | `database/migrations/` |
| `database/seeders/RoleAndPermissionSeeder.php` | `database/seeders/` |
| `database/seeders/DatabaseSeeder.php` | `database/seeders/DatabaseSeeder.php` (timpa) |
| `app/Models/User.php` | `app/Models/User.php` (timpa) |
| `app/Http/Controllers/DashboardController.php` | `app/Http/Controllers/` |
| `app/Http/Middleware/RedirectIfNotRole.php` | `app/Http/Middleware/` |
| `bootstrap/app.php` | `bootstrap/app.php` (timpa) |
| `routes/web.php` | `routes/web.php` (timpa) |
| `resources/views/dashboard.blade.php` | `resources/views/dashboard.blade.php` (timpa) |
| `resources/views/dashboard/admin.blade.php` | `resources/views/dashboard/` |
| `resources/views/dashboard/guru.blade.php` | `resources/views/dashboard/` |
| `resources/views/dashboard/penjaga.blade.php` | `resources/views/dashboard/` |

## 5. Jalankan migrasi & seeder

```powershell
.\vendor\bin\sail artisan migrate
.\vendor\bin\sail artisan db:seed --class=RoleAndPermissionSeeder
```

Ini akan cipta 4 peranan: **Admin, Akauntan, Guru, Penjaga**, dan satu akaun
Admin sedia guna:

```
Emel:    admin@kafa.test
Kata laluan: password
```

## 6. Buka aplikasi

```
http://localhost/login
```

Log masuk dengan akaun admin di atas — anda akan dibawa terus ke papan
pemuka mengikut peranan (`/dashboard/admin`).

---

## Apa yang ada dalam FASA 1

- ✅ Login, daftar, log keluar, reset kata laluan, pengesahan emel (Breeze)
- ✅ 4 peranan: **Admin, Akauntan, Guru, Penjaga** (Spatie laravel-permission)
- ✅ Kebenaran (permissions) yang dipetakan untuk semua fasa akan datang:
  `urus-yuran`, `rekod-bayaran`, `lihat-invois-sendiri`, dsb.
- ✅ Model `User` dengan medan tambahan: `phone`, `ic_number` (No. KP), `is_active`
- ✅ Papan pemuka berasingan mengikut peranan (Admin/Akauntan, Guru, Penjaga)
- ✅ Middleware `role:` untuk sekat laluan mengikut peranan
- ✅ Nama aplikasi: **SISTEM PENGURUSAN YURAN PELAJAR KAFA**

## Seterusnya: FASA 2

Setelah anda sahkan FASA 1 berjalan lancar, kita akan bina **Pelajar,
Penjaga, Guru, Kelas** — model, migrasi, hubungan (relationships), dan CRUD.
