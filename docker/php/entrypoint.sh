#!/bin/bash
set -e

cd /var/www/html

echo "Fixing permissions..."
mkdir -p storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chown -R www-data:www-data public/storage 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# Auto-create .env if not exists
if [ ! -f .env ]; then
    echo "Creating .env from .env.docker..."
    if [ -f .env.docker ]; then
        cp .env.docker .env
    elif [ -f .env.example ]; then
        cp .env.example .env
    fi
fi

echo "Running composer install..."
composer install --no-interaction --prefer-dist --optimize-autoloader

# Ensure APP_KEY exists and is valid
if [ -f .env ] && { ! grep -q "^APP_KEY=base64:.\{40,\}" .env || grep -q "APP_KEY=base64:YOUR_APP_KEY_HERE" .env; }; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

echo "Waiting for database connection..."
MAX_TRIES=30
COUNT=0
until php -r "try { new PDO('mysql:host=' . (getenv('DB_HOST') ?: 'mysql') . ';port=' . (getenv('DB_PORT') ?: '3306') . ';dbname=' . (getenv('DB_DATABASE') ?: 'cmms_dev'), getenv('DB_USERNAME') ?: 'cmms_user', getenv('DB_PASSWORD') ?: 'secret', [PDO::ATTR_TIMEOUT => 3]); exit(0); } catch (Throwable \$e) { exit(1); }"; do
    COUNT=$((COUNT + 1))
    if [ $COUNT -ge $MAX_TRIES ]; then
        echo "Warning: Database connection timeout after ${MAX_TRIES} attempts. Skipping auto-migration."
        break
    fi
    echo "Waiting for MySQL (${COUNT}/${MAX_TRIES})..."
    sleep 2
done

if [ $COUNT -lt $MAX_TRIES ]; then
    echo "Running database migrations..."
    php artisan migrate --force

    echo "Running initial database seeds (all PV modules T01-T07, inverters, users)..."
    php artisan db:seed --force
fi

echo "Running php artisan optimize..."
php artisan optimize --no-interaction

echo "Running php artisan storage:link..."
php artisan storage:link --no-interaction || true

if [ "$#" -gt 0 ]; then
    exec "$@"
else
    echo "Starting PHP-FPM..."
    exec docker-php-entrypoint php-fpm
fi
