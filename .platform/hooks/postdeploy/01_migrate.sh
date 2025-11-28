#!/bin/bash

# Run database migrations after deployment
cd /var/app/current

echo "Running database migrations..."
php artisan migrate --force

# If migrations table doesn't exist, install it first
if ! php artisan migrate:status 2>/dev/null; then
    echo "Migrations table not found, running fresh migrations..."
    php artisan migrate:install --force || true
    php artisan migrate --force
fi

# Seed database (creates superadmin user)
echo "Seeding database (creating superadmin)..."
php artisan db:seed --class=SuperAdminSeeder --force || true

echo "Migrations and seeding completed!"
