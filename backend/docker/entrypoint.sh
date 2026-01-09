#!/bin/sh
set -e

echo "Starting container for Woundmed..."

# Wait briefly for DB (Aptible DB is usually ready, but this avoids race conditions)
sleep 5

echo "Running Laravel migrations..."
php artisan migrate --force

echo "Starting Apache..."
exec apache2-foreground
