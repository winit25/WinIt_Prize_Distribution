#!/bin/bash

# Fix nginx configuration for Laravel routes
# This runs after EB platform configures nginx

set -e

echo "Configuring nginx for Laravel..."

# Find the nginx PHP config file
NGINX_PHP_CONF="/etc/nginx/conf.d/elasticbeanstalk/php.conf"

# Backup original
if [ -f "$NGINX_PHP_CONF" ]; then
    cp "$NGINX_PHP_CONF" "${NGINX_PHP_CONF}.bak.$(date +%s)"
fi

# Create Laravel-friendly config
cat > "$NGINX_PHP_CONF" <<'EOF'
location / {
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
}
EOF

# Test and reload nginx
if nginx -t 2>/dev/null; then
    echo "Nginx config test passed, reloading..."
    systemctl reload nginx || service nginx reload || true
    echo "Nginx reloaded successfully"
else
    echo "WARNING: Nginx config test failed, not reloading"
    # Restore backup if test fails
    if [ -f "${NGINX_PHP_CONF}.bak.$(date +%s)" ]; then
        mv "${NGINX_PHP_CONF}.bak.$(date +%s)" "$NGINX_PHP_CONF"
    fi
fi

echo "Nginx configuration updated for Laravel"

