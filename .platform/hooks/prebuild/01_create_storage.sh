#!/bin/bash

# Create storage directories BEFORE composer install runs
# This MUST run before any PHP/Laravel code executes

set -e

echo "=========================================="
echo "Creating storage directories (PRE-BUILD)..."
echo "=========================================="

# Create directories in staging (where composer runs)
STAGING_DIR="/var/app/staging"

echo "Creating storage directories in $STAGING_DIR..."

# Create all required directories
mkdir -p $STAGING_DIR/storage/framework/sessions 2>/dev/null || true
mkdir -p $STAGING_DIR/storage/framework/views 2>/dev/null || true
mkdir -p $STAGING_DIR/storage/framework/cache 2>/dev/null || true
mkdir -p $STAGING_DIR/storage/logs 2>/dev/null || true
mkdir -p $STAGING_DIR/bootstrap/cache 2>/dev/null || true

# Set permissions - use 777 during build to avoid any permission issues
chmod -R 777 $STAGING_DIR/storage 2>/dev/null || true
chmod -R 777 $STAGING_DIR/bootstrap/cache 2>/dev/null || true

# Set ownership
chown -R webapp:webapp $STAGING_DIR/storage $STAGING_DIR/bootstrap/cache 2>/dev/null || true

# Verify creation
echo "Verifying directories..."
ls -la $STAGING_DIR/storage/framework/ 2>/dev/null || echo "Warning: Could not list storage/framework"
ls -la $STAGING_DIR/storage/framework/views/ 2>/dev/null || echo "Warning: Could not list storage/framework/views"

echo "✓ Storage directories created in staging"
echo "=========================================="
