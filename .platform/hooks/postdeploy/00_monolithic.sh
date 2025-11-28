#!/bin/bash
# Monolithic post-deployment hook
# Handles all post-deployment tasks in one place

set -e

echo "=========================================="
echo "Monolithic Post-Deployment Hook"
echo "=========================================="

CURRENT_DIR="/var/app/current"
STAGING_DIR="/var/app/staging"

# 1. Ensure storage directories exist in both locations
echo "Creating storage directories..."

# In current (where app runs)
if [ -d "$CURRENT_DIR" ]; then
    cd $CURRENT_DIR
    mkdir -p storage/framework/{views,sessions,cache} 2>/dev/null || true
    mkdir -p storage/logs 2>/dev/null || true
    mkdir -p bootstrap/cache 2>/dev/null || true
    chmod -R 775 storage bootstrap/cache 2>/dev/null || true
    chown -R webapp:webapp storage bootstrap/cache 2>/dev/null || true
fi

# In staging (in case Laravel resolves there)
if [ -d "$STAGING_DIR" ]; then
    mkdir -p $STAGING_DIR/storage/framework/{views,sessions,cache} 2>/dev/null || true
    mkdir -p $STAGING_DIR/storage/logs 2>/dev/null || true
    mkdir -p $STAGING_DIR/bootstrap/cache 2>/dev/null || true
    chmod -R 777 $STAGING_DIR/storage $STAGING_DIR/bootstrap/cache 2>/dev/null || true
    chown -R webapp:webapp $STAGING_DIR/storage $STAGING_DIR/bootstrap/cache 2>/dev/null || true
fi

# 2. Clear caches
echo "Clearing caches..."
if [ -d "$CURRENT_DIR" ]; then
    cd $CURRENT_DIR
    php artisan view:clear 2>/dev/null || true
    php artisan config:clear 2>/dev/null || true
    php artisan route:clear 2>/dev/null || true
    php artisan cache:clear 2>/dev/null || true
fi

# 3. Run migrations (if needed)
echo "Running migrations..."
if [ -d "$CURRENT_DIR" ]; then
    cd $CURRENT_DIR
    php artisan migrate:status > /dev/null 2>&1 || php artisan migrate:install --force 2>/dev/null || true
    php artisan migrate --force 2>/dev/null || true
fi

echo "✓ Monolithic post-deployment complete"
echo "=========================================="

