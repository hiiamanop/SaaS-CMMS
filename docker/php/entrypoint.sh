#!/bin/bash
set -e

cd /var/www/html

echo "Fixing permissions..."
mkdir -p storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chown -R www-data:www-data public/storage 2>/dev/null || true
chmod -R 775 storage bootstrap/cache

echo "Running composer install..."
composer install --no-interaction --prefer-dist --optimize-autoloader

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
