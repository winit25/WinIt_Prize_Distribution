#!/bin/bash

# Fix nginx configuration for Laravel routes
# This runs after EB platform configures nginx

set -e

echo "=========================================="
echo "Configuring nginx for Laravel routes..."
echo "=========================================="

# Set document root
DOCUMENT_ROOT="/var/app/current/public"

# Update PHP config with document root, index, and try_files
PHP_CONF="/etc/nginx/conf.d/elasticbeanstalk/php.conf"
if [ -f "$PHP_CONF" ]; then
    echo "Updating $PHP_CONF..."
    cp "$PHP_CONF" "${PHP_CONF}.bak.$(date +%s)"
    
    cat > "$PHP_CONF" <<'EOF'
root /var/app/current/public;
index index.php index.html index.htm;

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
    echo "✓ Updated $PHP_CONF"
fi

# Also check main nginx.conf for server block
NGINX_CONF="/etc/nginx/nginx.conf"
if [ -f "$NGINX_CONF" ]; then
    echo "Checking main nginx.conf..."
    
    # Backup
    cp "$NGINX_CONF" "${NGINX_CONF}.bak.$(date +%s)"
    
    # Ensure index directive includes index.php
    if ! grep -q "index.*index.php" "$NGINX_CONF"; then
        echo "Adding index.php to nginx.conf..."
        sed -i '/server {/a\    index index.php index.html index.htm;' "$NGINX_CONF" || true
    fi
    
    # Ensure root is set correctly
    if ! grep -q "root.*$DOCUMENT_ROOT" "$NGINX_CONF"; then
        echo "Updating document root in nginx.conf..."
        sed -i "s|root.*;|root $DOCUMENT_ROOT;|g" "$NGINX_CONF" || true
    fi
fi

# Test and reload nginx
echo "Testing nginx configuration..."
if nginx -t 2>/dev/null; then
    echo "✓ Nginx config test passed, reloading..."
    systemctl reload nginx || service nginx reload || /etc/init.d/nginx reload || true
    echo "✓ Nginx reloaded successfully"
else
    echo "⚠️  Nginx config test failed:"
    nginx -t
fi

echo "=========================================="
echo "Nginx configuration completed"
echo "=========================================="
