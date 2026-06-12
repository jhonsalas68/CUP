#!/bin/sh
set -e

# Cache configuration, routes, and views for speed
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Run database migrations
echo "Running migrations..."
php artisan migrate --force

# Start apache
echo "Starting Apache..."
exec apache2-foreground
