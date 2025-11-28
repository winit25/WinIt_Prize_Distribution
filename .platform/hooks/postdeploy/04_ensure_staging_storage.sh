#!/bin/bash

# Ensure storage directories exist in staging (in case Laravel resolves there)
# This is a runtime fix for the path resolution issue

echo "=========================================="
echo "Ensuring storage directories exist in staging (runtime fix)..."
echo "=========================================="

STAGING_DIR="/var/app/staging"

# Create directories if they don't exist
mkdir -p $STAGING_DIR/storage/framework/sessions 2>/dev/null || true
mkdir -p $STAGING_DIR/storage/framework/views 2>/dev/null || true
mkdir -p $STAGING_DIR/storage/framework/cache 2>/dev/null || true
mkdir -p $STAGING_DIR/storage/logs 2>/dev/null || true
mkdir -p $STAGING_DIR/bootstrap/cache 2>/dev/null || true

# Set permissions
chmod -R 777 $STAGING_DIR/storage $STAGING_DIR/bootstrap/cache 2>/dev/null || true
chown -R webapp:webapp $STAGING_DIR/storage $STAGING_DIR/bootstrap/cache 2>/dev/null || true

# Verify views directory
if [ ! -d "$STAGING_DIR/storage/framework/views" ]; then
    echo "ERROR: Creating views directory in staging..."
    mkdir -p $STAGING_DIR/storage/framework/views
    chmod 777 $STAGING_DIR/storage/framework/views
    chown webapp:webapp $STAGING_DIR/storage/framework/views 2>/dev/null || true
fi

echo "✓ Storage directories ensured in staging (runtime)"
echo "=========================================="

