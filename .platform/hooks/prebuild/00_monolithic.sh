#!/bin/bash
# ============================================================================
# MONOLITHIC PRE-BUILD HOOK
# ============================================================================
# Runs BEFORE composer install to ensure storage directories exist
# and configure composer settings
# ============================================================================

set -e

echo "=========================================="
echo "Monolithic Pre-Build Hook"
echo "=========================================="

STAGING_DIR="/var/app/staging"

# 1. Create storage directories BEFORE composer runs
echo "Creating storage directories in staging..."
mkdir -p $STAGING_DIR/storage/framework/{sessions,views,cache} 2>/dev/null || true
mkdir -p $STAGING_DIR/storage/logs 2>/dev/null || true
mkdir -p $STAGING_DIR/bootstrap/cache 2>/dev/null || true
chmod -R 777 $STAGING_DIR/storage $STAGING_DIR/bootstrap/cache 2>/dev/null || true
chown -R webapp:webapp $STAGING_DIR/storage $STAGING_DIR/bootstrap/cache 2>/dev/null || true

# Verify views directory exists
if [ ! -d "$STAGING_DIR/storage/framework/views" ]; then
    echo "ERROR: views directory missing, creating..."
    mkdir -p $STAGING_DIR/storage/framework/views
    chmod 777 $STAGING_DIR/storage/framework/views
    chown webapp:webapp $STAGING_DIR/storage/framework/views 2>/dev/null || true
fi

# 2. Configure Composer to ignore platform requirements
echo "Configuring Composer..."
export COMPOSER_IGNORE_PLATFORM_REQS=1

if [ -f composer.json ]; then
    composer config --global platform.php 8.4.0 || true
    composer config --global ignore-platform-reqs php || true
fi

echo "✓ Pre-build hook completed"
echo "=========================================="

