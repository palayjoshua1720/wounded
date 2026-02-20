#!/bin/sh
set -e

echo "Starting Laravel container initialization..."

php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear

echo "Running migrations..."
php artisan migrate --force || true

echo "Initialization complete. Starting Apache..."
exec apache2-foreground