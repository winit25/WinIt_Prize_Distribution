#!/bin/bash
# ============================================================================
# MONOLITHIC POST-DEPLOYMENT HOOK
# ============================================================================
# Runs AFTER deployment to ensure everything is configured correctly
# ============================================================================

set -e

echo "=========================================="
echo "Monolithic Post-Deployment Hook"
echo "=========================================="

CURRENT_DIR="/var/app/current"
STAGING_DIR="/var/app/staging"

# 1. Ensure storage directories exist in BOTH locations
echo "Ensuring storage directories exist..."

# In current (where app runs)
if [ -d "$CURRENT_DIR" ]; then
    cd $CURRENT_DIR
    mkdir -p storage/framework/{views,sessions,cache} 2>/dev/null || true
    mkdir -p storage/logs 2>/dev/null || true
    mkdir -p bootstrap/cache 2>/dev/null || true
    chmod -R 775 storage bootstrap/cache 2>/dev/null || true
    chown -R webapp:webapp storage bootstrap/cache 2>/dev/null || true
fi

# In staging (in case Laravel resolves there)
if [ -d "$STAGING_DIR" ]; then
    mkdir -p $STAGING_DIR/storage/framework/{views,sessions,cache} 2>/dev/null || true
    mkdir -p $STAGING_DIR/storage/logs 2>/dev/null || true
    mkdir -p $STAGING_DIR/bootstrap/cache 2>/dev/null || true
    chmod -R 777 $STAGING_DIR/storage $STAGING_DIR/bootstrap/cache 2>/dev/null || true
    chown -R webapp:webapp $STAGING_DIR/storage $STAGING_DIR/bootstrap/cache 2>/dev/null || true
fi

# 2. Fix Nginx configuration for Laravel routes
echo "Configuring Nginx..."
PHP_CONF="/etc/nginx/conf.d/elasticbeanstalk/php.conf"
if [ -f "$PHP_CONF" ]; then
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
    nginx -t 2>/dev/null && systemctl reload nginx 2>/dev/null || true
fi

# 3. Clear caches
echo "Clearing caches..."
if [ -d "$CURRENT_DIR" ]; then
    cd $CURRENT_DIR
    php artisan view:clear 2>/dev/null || true
    php artisan config:clear 2>/dev/null || true
    php artisan route:clear 2>/dev/null || true
    php artisan cache:clear 2>/dev/null || true
fi

# 4. Run migrations (if needed)
echo "Running migrations..."
if [ -d "$CURRENT_DIR" ]; then
    cd $CURRENT_DIR
    php artisan migrate:status > /dev/null 2>&1 || php artisan migrate:install --force 2>/dev/null || true
    php artisan migrate --force 2>/dev/null || true
    
    # Seed superadmin if needed
    php artisan db:seed --class=SuperAdminSeeder --force 2>/dev/null || true
fi

echo "✓ Post-deployment hook completed"
echo "=========================================="
