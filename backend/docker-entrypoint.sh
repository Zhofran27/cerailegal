#!/bin/bash
set -e

PORT=${PORT:-80}
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/g" /etc/apache2/sites-available/000-default.conf

mkdir -p /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/app/documents \
         /var/www/html/storage/logs

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

if [ -n "$APP_KEY" ]; then
    echo "Caching Laravel config and routes..."
    php artisan config:cache || true
    php artisan route:cache || true
fi

if [ -n "$DB_HOST" ]; then
    echo "Running database migrations and seeders..."
    php artisan migrate --force || true
    php artisan db:seed --force || true
fi

echo "Starting Apache web server on port ${PORT}..."
exec apache2-foreground

