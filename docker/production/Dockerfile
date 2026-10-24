# Imej production yang ringan — BUKAN imej Sail (dev-oriented, ada Xdebug,
# Playwright, Node toolchain penuh, dsb). Multi-stage: compile dependency PHP
# & aset frontend dalam stage berasingan, runtime akhir hanya bawa apa yang
# perlu untuk serve aplikasi (PHP-FPM + Caddy, diuruskan oleh Supervisor).

# ---- Stage 1: Dependency PHP (Composer) ----
# composer:2 tidak bawa ext-gd secara lalai — phpoffice/phpspreadsheet
# (dependency maatwebsite/excel) perlukannya semasa resolve dependency,
# walaupun gd sebenarnya hanya digunakan semasa runtime (dipasang dalam
# stage runtime di bawah). --ignore-platform-req=ext-gd selamat di sini
# kerana stage ini sekadar muat turun/resolve pakej, bukan jalankan kod.
FROM composer:2 AS composer-deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-req=ext-gd
COPY . .
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

# ---- Stage 2: Aset frontend (Vite/Tailwind) ----
FROM node:22-alpine AS node-deps
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY --from=composer-deps /app /app
RUN npm run build

# ---- Stage 3: Runtime ----
# Keperluan SEBENAR (bukan sekadar composer.json "^8.3") ialah PHP >=8.4 —
# disahkan melalui ujian build sebenar (pelbagai komponen Symfony yang
# ditarik oleh Laravel 13 memerlukan 8.4+). PHP 8.5 cuba dahulu untuk padan
# persis dengan Sail (dev), TETAPI ext-intl (C++) gagal dibina secara
# konsisten dengan ralat "cp: can't stat 'modules/*'" — disahkan ralat yang
# SAMA berlaku pada Alpine/musl MAHUPUN Debian/glibc, jadi ia bug pada
# proses build intl PHP 8.5 sendiri (versi sangat baharu), bukan isu OS.
# PHP 8.4 (stabil, memenuhi keperluan >=8.4 sebenar) dipilih sebagai
# pengganti yang selamat.
#
# Guna varian Debian (bukan Alpine) — glibc/gcc lebih stabil untuk
# ekstensi C++ seperti intl, dan lebih padan dengan Sail (Ubuntu/apt).
# Dipin ke 8.4.27 — tampalan PHP2Shell (CVE-2026-109618, CVE-2026-109619).
FROM php:8.4.27-fpm-bookworm AS runtime

RUN apt-get update && apt-get install -y --no-install-recommends \
        curl gnupg ca-certificates apt-transport-https \
    && curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' | gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg \
    && curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' -o /etc/apt/sources.list.d/caddy-stable.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        caddy \
        supervisor \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        gd \
        zip \
        bcmath \
        intl \
        mbstring \
        opcache \
        pcntl \
    && apt-get purge -y --auto-remove curl gnupg apt-transport-https \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=composer-deps /app /var/www/html
COPY --from=node-deps /app/public/build /var/www/html/public/build

COPY docker/production/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/production/Caddyfile /etc/caddy/Caddyfile
COPY docker/production/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/production/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80 443

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
