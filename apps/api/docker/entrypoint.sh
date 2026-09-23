#!/bin/bash
set -e

cd /var/www/html

php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true

if [ ! -f /var/www/html/.env ]; then
    touch /var/www/html/.env
fi

if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

mkdir -p /var/www/html/storage/app/public /var/www/html/public

# Safely create storage link if public/storage doesn't already exist or is broken
if [ ! -L /var/www/html/public/storage ] && [ ! -d /var/www/html/public/storage ]; then
    echo "Creating public storage symlink..."
    php artisan storage:link || true
elif [ -L /var/www/html/public/storage ] && [ ! -e /var/www/html/public/storage ]; then
    echo "Re-creating broken storage symlink..."
    rm -f /var/www/html/public/storage
    php artisan storage:link || true
fi

# Only api (RUN_MIGRATIONS=true) reaches this block — queue and reverb set
# RUN_MIGRATIONS=false in docker-compose.yml, so they never race this.
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    SENTINEL="/var/www/html/storage/app/.db-initialized"
    if [ ! -f "$SENTINEL" ]; then
        echo "First boot detected — running migrate:fresh --seed..."
        php artisan migrate:fresh --seed --force
        touch "$SENTINEL"
    else
        echo "Database already initialized — applying any pending migrations only."
        php artisan migrate --force
    fi
fi

exec "$@"
