#!/bin/bash

# Fix permissions and create storage directories for Laravel application
set -e

echo "=========================================="
echo "Fixing Laravel application permissions..."
echo "=========================================="

APP_DIR="/var/app/current"
STAGING_DIR="/var/app/staging"
PUBLIC_DIR="$APP_DIR/public"
STORAGE_DIR="$APP_DIR/storage"
BOOTSTRAP_DIR="$APP_DIR/bootstrap/cache"

# Set ownership to webapp user (EB default)
WEBAPP_USER="webapp"
WEBAPP_GROUP="webapp"

# Create all necessary storage directories in CURRENT
echo "Creating storage directories in $APP_DIR..."
mkdir -p $STORAGE_DIR/framework/sessions 2>/dev/null || true
mkdir -p $STORAGE_DIR/framework/views 2>/dev/null || true
mkdir -p $STORAGE_DIR/framework/cache 2>/dev/null || true
mkdir -p $STORAGE_DIR/logs 2>/dev/null || true
mkdir -p $BOOTSTRAP_DIR 2>/dev/null || true
mkdir -p $STORAGE_DIR/app/public 2>/dev/null || true
mkdir -p $STORAGE_DIR/app/private 2>/dev/null || true
mkdir -p $STORAGE_DIR/app/temp 2>/dev/null || true

# Also create in staging (in case Laravel references it)
echo "Creating storage directories in $STAGING_DIR..."
mkdir -p $STAGING_DIR/storage/framework/sessions 2>/dev/null || true
mkdir -p $STAGING_DIR/storage/framework/views 2>/dev/null || true
mkdir -p $STAGING_DIR/storage/framework/cache 2>/dev/null || true
mkdir -p $STAGING_DIR/storage/logs 2>/dev/null || true
mkdir -p $STAGING_DIR/bootstrap/cache 2>/dev/null || true
mkdir -p $STAGING_DIR/storage/app/public 2>/dev/null || true
mkdir -p $STAGING_DIR/storage/app/private 2>/dev/null || true
mkdir -p $STAGING_DIR/storage/app/temp 2>/dev/null || true

# Set ownership
echo "Setting ownership to $WEBAPP_USER:$WEBAPP_GROUP..."
chown -R $WEBAPP_USER:$WEBAPP_GROUP $STORAGE_DIR $BOOTSTRAP_DIR 2>/dev/null || true
chown -R $WEBAPP_USER:$WEBAPP_GROUP $STAGING_DIR/storage $STAGING_DIR/bootstrap/cache 2>/dev/null || true

# Set permissions
echo "Setting permissions..."
chmod -R 775 $STORAGE_DIR 2>/dev/null || true
chmod -R 775 $BOOTSTRAP_DIR 2>/dev/null || true
chmod -R 775 $STAGING_DIR/storage 2>/dev/null || true
chmod -R 775 $STAGING_DIR/bootstrap/cache 2>/dev/null || true

# Ensure public directory is accessible
echo "Setting permissions for public directory..."
chown -R $WEBAPP_USER:$WEBAPP_GROUP $PUBLIC_DIR 2>/dev/null || true
chmod -R 755 $PUBLIC_DIR 2>/dev/null || true

# Ensure storage link exists
if [ ! -L "$PUBLIC_DIR/storage" ]; then
    echo "Creating storage symlink..."
    cd $PUBLIC_DIR
    ln -sf ../storage/app/public storage 2>/dev/null || true
fi

echo "✓ Permissions and directories fixed"
echo "=========================================="
