#!/bin/bash

# Fix nginx configuration for Laravel routes
# This runs after EB platform configures nginx

set -e

echo "=========================================="
echo "Configuring nginx for Laravel routes..."
echo "=========================================="

# Possible nginx config locations for EB AL2023 PHP
NGINX_CONFIGS=(
    "/etc/nginx/conf.d/elasticbeanstalk/php.conf"
    "/etc/nginx/conf.d/php.conf"
    "/etc/nginx/conf.d/default.conf"
    "/etc/nginx/sites-available/elasticbeanstalk"
    "/etc/nginx/sites-enabled/elasticbeanstalk"
)

LARAVEL_CONFIG='location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/run/php-fpm/www.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}

location ~ /\.(?!well-known).* {
    deny all;
}'

# Find and update nginx config
CONFIG_UPDATED=false
for config_file in "${NGINX_CONFIGS[@]}"; do
    if [ -f "$config_file" ]; then
        echo "Found nginx config: $config_file"
        
        # Backup original
        cp "$config_file" "${config_file}.bak.$(date +%s)" 2>/dev/null || true
        
        # Check if it already has try_files
        if ! grep -q "try_files" "$config_file"; then
            echo "Updating $config_file with Laravel config..."
            echo "$LARAVEL_CONFIG" > "$config_file"
            CONFIG_UPDATED=true
        else
            echo "$config_file already has try_files directive"
        fi
    fi
done

# Also check main nginx.conf for server block
if [ -f "/etc/nginx/nginx.conf" ]; then
    echo "Checking main nginx.conf..."
    if ! grep -q "try_files" /etc/nginx/nginx.conf; then
        # Try to add try_files to the server block
        if grep -q "location /" /etc/nginx/nginx.conf; then
            echo "Found location / in nginx.conf, updating..."
            sed -i.bak '/location \//a\    try_files $uri $uri/ /index.php?$query_string;' /etc/nginx/nginx.conf || true
            CONFIG_UPDATED=true
        fi
    fi
fi

# Test and reload nginx if config was updated
if [ "$CONFIG_UPDATED" = true ]; then
    echo "Testing nginx configuration..."
    if nginx -t 2>/dev/null; then
        echo "Nginx config test passed, reloading..."
        systemctl reload nginx || service nginx reload || /etc/init.d/nginx reload || true
        echo "✓ Nginx reloaded successfully"
    else
        echo "WARNING: Nginx config test failed"
        nginx -t
    fi
else
    echo "No nginx config files found or already configured"
fi

echo "=========================================="
echo "Nginx configuration check completed"
echo "=========================================="
