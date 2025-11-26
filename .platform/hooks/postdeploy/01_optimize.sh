#!/bin/bash

cd /var/app/current

# Clear and optimize caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
chmod -R 775 storage bootstrap/cache
chown -R webapp:webapp storage bootstrap/cache

echo "Post-deployment optimization completed"
