#!/bin/bash

# Create storage directories BEFORE composer install runs
# This prevents Laravel from failing when it tries to compile views during package discovery

set -e

echo "=========================================="
echo "Creating storage directories (pre-build)..."
echo "=========================================="

# Create directories in staging (where composer runs)
STAGING_DIR="/var/app/staging"

if [ -d "$STAGING_DIR" ]; then
    echo "Creating storage directories in $STAGING_DIR..."
    
    mkdir -p $STAGING_DIR/storage/framework/sessions
    mkdir -p $STAGING_DIR/storage/framework/views
    mkdir -p $STAGING_DIR/storage/framework/cache
    mkdir -p $STAGING_DIR/storage/logs
    mkdir -p $STAGING_DIR/bootstrap/cache
    
    # Set permissions - use 777 during build to avoid any permission issues
    chmod -R 777 $STAGING_DIR/storage
    chmod -R 777 $STAGING_DIR/bootstrap/cache
    
    # Set ownership
    chown -R webapp:webapp $STAGING_DIR/storage $STAGING_DIR/bootstrap/cache || true
    
    echo "✓ Storage directories created in staging"
    ls -la $STAGING_DIR/storage/framework/ || echo "Warning: Could not list storage/framework"
else
    echo "Warning: Staging directory not found, creating in current directory..."
    mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache
    chmod -R 777 storage bootstrap/cache
fi

echo "=========================================="
echo "Pre-build storage setup completed"
echo "=========================================="

