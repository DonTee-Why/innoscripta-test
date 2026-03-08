#!/bin/sh

set -e

echo "Setting permissions..."
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

echo "Generating application key..."
php artisan key:generate --force 2>/dev/null || true

echo "Starting PHP-FPM..."
php-fpm -D

echo "Starting Nginx..."
exec nginx -g "daemon off;"
