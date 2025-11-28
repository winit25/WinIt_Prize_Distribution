#!/bin/bash
# Configure composer to ignore platform requirements for PHP version
# This allows Laravel 12 (requires PHP 8.4) to install on PHP 8.2
# ALSO ensure storage directories exist before composer runs

echo "Configuring Composer and ensuring storage directories exist..."

# CRITICAL: Ensure storage directories exist BEFORE composer install
STAGING_DIR="/var/app/staging"
if [ -d "$STAGING_DIR" ]; then
    echo "Creating storage directories before composer install..."
    mkdir -p $STAGING_DIR/storage/framework/{sessions,views,cache} 2>/dev/null || true
    mkdir -p $STAGING_DIR/storage/logs 2>/dev/null || true
    mkdir -p $STAGING_DIR/bootstrap/cache 2>/dev/null || true
    chmod -R 777 $STAGING_DIR/storage $STAGING_DIR/bootstrap/cache 2>/dev/null || true
    chown -R webapp:webapp $STAGING_DIR/storage $STAGING_DIR/bootstrap/cache 2>/dev/null || true
    
    # Verify views directory
    if [ ! -d "$STAGING_DIR/storage/framework/views" ]; then
        echo "ERROR: Creating views directory again..."
        mkdir -p $STAGING_DIR/storage/framework/views
        chmod 777 $STAGING_DIR/storage/framework/views
        chown webapp:webapp $STAGING_DIR/storage/framework/views 2>/dev/null || true
    fi
fi

# Set composer to ignore platform requirements via environment variable
export COMPOSER_IGNORE_PLATFORM_REQS=1

# Also create a composer config file
if [ -f composer.json ]; then
    composer config --global platform.php 8.4.0 || true
    composer config --global ignore-platform-reqs php || true
fi

echo "Composer platform configuration complete"

