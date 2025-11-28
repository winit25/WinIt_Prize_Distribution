#!/bin/bash

# Ensure storage directories exist in staging AND current (in case Laravel resolves there)
# This is a runtime fix for the path resolution issue

echo "=========================================="
echo "Ensuring storage directories exist (runtime fix)..."
echo "=========================================="

# Ensure directories in staging
STAGING_DIR="/var/app/staging"
if [ -d "$STAGING_DIR" ]; then
    mkdir -p $STAGING_DIR/storage/framework/sessions 2>/dev/null || true
    mkdir -p $STAGING_DIR/storage/framework/views 2>/dev/null || true
    mkdir -p $STAGING_DIR/storage/framework/cache 2>/dev/null || true
    mkdir -p $STAGING_DIR/storage/logs 2>/dev/null || true
    mkdir -p $STAGING_DIR/bootstrap/cache 2>/dev/null || true
    chmod -R 777 $STAGING_DIR/storage $STAGING_DIR/bootstrap/cache 2>/dev/null || true
    chown -R webapp:webapp $STAGING_DIR/storage $STAGING_DIR/bootstrap/cache 2>/dev/null || true
fi

# Ensure directories in current (where app actually runs)
CURRENT_DIR="/var/app/current"
if [ -d "$CURRENT_DIR" ]; then
    cd $CURRENT_DIR
    mkdir -p storage/framework/sessions 2>/dev/null || true
    mkdir -p storage/framework/views 2>/dev/null || true
    mkdir -p storage/framework/cache 2>/dev/null || true
    mkdir -p storage/logs 2>/dev/null || true
    mkdir -p bootstrap/cache 2>/dev/null || true
    chmod -R 775 storage bootstrap/cache 2>/dev/null || true
    chown -R webapp:webapp storage bootstrap/cache 2>/dev/null || true
    
    # Clear view cache to force recompilation
    php artisan view:clear 2>/dev/null || true
fi

echo "✓ Storage directories ensured in both staging and current"
echo "=========================================="

