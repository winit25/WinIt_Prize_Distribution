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

# Fix storage and bootstrap cache permissions
echo "Setting permissions for storage and bootstrap/cache..."
chown -R $WEBAPP_USER:$WEBAPP_GROUP $STORAGE_DIR $BOOTSTRAP_DIR 2>/dev/null || true
chmod -R 775 $STORAGE_DIR $BOOTSTRAP_DIR 2>/dev/null || true

# Ensure public directory is accessible
echo "Setting permissions for public directory..."
chown -R $WEBAPP_USER:$WEBAPP_GROUP $PUBLIC_DIR 2>/dev/null || true
chmod -R 755 $PUBLIC_DIR 2>/dev/null || true

# Ensure storage subdirectories have correct permissions
echo "Setting permissions for storage subdirectories..."
find $STORAGE_DIR -type d -exec chmod 775 {} \; 2>/dev/null || true
find $STORAGE_DIR -type f -exec chmod 664 {} \; 2>/dev/null || true
find $BOOTSTRAP_DIR -type d -exec chmod 775 {} \; 2>/dev/null || true
find $BOOTSTRAP_DIR -type f -exec chmod 664 {} \; 2>/dev/null || true

echo "✓ Permissions fixed"
echo "=========================================="
