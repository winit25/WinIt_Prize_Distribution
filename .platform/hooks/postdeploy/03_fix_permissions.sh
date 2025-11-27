#!/bin/bash

# Fix file permissions for Laravel
set -e

echo "=========================================="
echo "Fixing Laravel file permissions..."
echo "=========================================="

APP_DIR="/var/app/current"
PUBLIC_DIR="$APP_DIR/public"

# Set correct ownership (webapp user for EB)
chown -R webapp:webapp "$APP_DIR" || chown -R nginx:nginx "$APP_DIR" || true

# Set directory permissions
find "$APP_DIR" -type d -exec chmod 755 {} \;

# Set file permissions
find "$APP_DIR" -type f -exec chmod 644 {} \;

# Make scripts executable
find "$APP_DIR" -name "*.sh" -exec chmod +x {} \;

# Ensure storage and bootstrap/cache are writable
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null || true
chown -R webapp:webapp "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null || chown -R nginx:nginx "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null || true

# Ensure public directory is accessible
chmod 755 "$PUBLIC_DIR" 2>/dev/null || true
chmod 644 "$PUBLIC_DIR/index.php" 2>/dev/null || true

echo "✓ Permissions fixed"
echo "=========================================="

