#!/bin/bash

# Fix permissions and create storage directories for Laravel application
set -e

echo "=========================================="
echo "Fixing Laravel application permissions..."
echo "=========================================="

APP_DIR="/var/app/current"
PUBLIC_DIR="$APP_DIR/public"
STORAGE_DIR="$APP_DIR/storage"
BOOTSTRAP_DIR="$APP_DIR/bootstrap/cache"

# Set ownership to webapp user (EB default)
WEBAPP_USER="webapp"
WEBAPP_GROUP="webapp"

# Create all necessary storage directories
echo "Creating storage directories..."
mkdir -p $STORAGE_DIR/framework/sessions 2>/dev/null || true
mkdir -p $STORAGE_DIR/framework/views 2>/dev/null || true
mkdir -p $STORAGE_DIR/framework/cache 2>/dev/null || true
mkdir -p $STORAGE_DIR/logs 2>/dev/null || true
mkdir -p $BOOTSTRAP_DIR 2>/dev/null || true

# Set permissions
echo "Setting permissions for storage and bootstrap/cache..."
chown -R $WEBAPP_USER:$WEBAPP_GROUP $STORAGE_DIR $BOOTSTRAP_DIR 2>/dev/null || true
chmod -R 775 $STORAGE_DIR $BOOTSTRAP_DIR 2>/dev/null || true

# Ensure public directory is accessible
echo "Setting permissions for public directory..."
chown -R $WEBAPP_USER:$WEBAPP_GROUP $PUBLIC_DIR 2>/dev/null || true
chmod -R 755 $PUBLIC_DIR 2>/dev/null || true

# Verify directories exist
echo "Verifying directories..."
ls -la $STORAGE_DIR/framework/ || echo "Warning: storage/framework not found"
ls -la $STORAGE_DIR/framework/views/ || echo "Warning: storage/framework/views not found"

echo "✓ Permissions fixed"
echo "=========================================="
