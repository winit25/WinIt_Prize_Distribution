#!/bin/bash

# Run database migrations after deployment
cd /var/app/current

echo "Running database migrations..."
php artisan migrate --force

echo "Migrations completed!"
