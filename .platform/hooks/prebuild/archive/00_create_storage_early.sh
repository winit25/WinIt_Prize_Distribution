#!/bin/bash

# Create storage directories VERY EARLY - before anything else runs
# This MUST be the first thing that runs to prevent view compilation errors

set -e

echo "=========================================="
echo "Creating storage directories (EARLY PRE-BUILD)..."
echo "=========================================="

# Create directories in staging (where composer runs)
STAGING_DIR="/var/app/staging"

echo "Creating storage directories in $STAGING_DIR..."

# Create base directories first
mkdir -p $STAGING_DIR/storage 2>/dev/null || true
mkdir -p $STAGING_DIR/storage/framework 2>/dev/null || true
mkdir -p $STAGING_DIR/storage/framework/sessions 2>/dev/null || true
mkdir -p $STAGING_DIR/storage/framework/views 2>/dev/null || true
mkdir -p $STAGING_DIR/storage/framework/cache 2>/dev/null || true
mkdir -p $STAGING_DIR/storage/logs 2>/dev/null || true
mkdir -p $STAGING_DIR/bootstrap 2>/dev/null || true
mkdir -p $STAGING_DIR/bootstrap/cache 2>/dev/null || true

# Set permissions - use 777 during build to avoid any permission issues
chmod -R 777 $STAGING_DIR/storage 2>/dev/null || true
chmod -R 777 $STAGING_DIR/bootstrap/cache 2>/dev/null || true

# Set ownership
chown -R webapp:webapp $STAGING_DIR/storage $STAGING_DIR/bootstrap/cache 2>/dev/null || true

# Verify views directory specifically
if [ ! -d "$STAGING_DIR/storage/framework/views" ]; then
    echo "ERROR: views directory missing, creating..."
    mkdir -p $STAGING_DIR/storage/framework/views
    chmod 777 $STAGING_DIR/storage/framework/views
    chown webapp:webapp $STAGING_DIR/storage/framework/views 2>/dev/null || true
fi

# Final verification
echo "Verifying directories exist..."
test -d $STAGING_DIR/storage/framework/views && echo "✓ views directory exists" || echo "✗ views directory missing"
test -d $STAGING_DIR/storage/framework/cache && echo "✓ cache directory exists" || echo "✗ cache directory missing"
test -d $STAGING_DIR/storage/framework/sessions && echo "✓ sessions directory exists" || echo "✗ sessions directory missing"

echo "✓ Storage directories created in staging (early)"
echo "=========================================="

