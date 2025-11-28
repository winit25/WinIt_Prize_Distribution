#!/bin/bash
# Configure composer to ignore platform requirements for PHP version
# This allows Laravel 12 (requires PHP 8.4) to install on PHP 8.2

echo "Configuring Composer to ignore platform requirements..."

# Set composer to ignore platform requirements via environment variable
export COMPOSER_IGNORE_PLATFORM_REQS=1

# Also create a composer config file
if [ -f composer.json ]; then
    composer config --global platform.php 8.4.0 || true
    composer config --global ignore-platform-reqs php || true
fi

echo "Composer platform configuration complete"

