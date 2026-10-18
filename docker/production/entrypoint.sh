#!/bin/sh
set -e

# .env disuntik oleh docker-compose (env_file) semasa kontena bermula, jadi
# cache config/route/view mesti dibina SEKARANG (runtime), bukan masa
# `docker build` — .env tiada lagi pada masa build.
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
