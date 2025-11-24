#!/bin/bash

# Laravel MySQL Configuration Script for XAMPP
# This script helps configure Laravel to use MySQL with XAMPP

echo "=========================================="
echo "Laravel MySQL Configuration Script"
echo "=========================================="
echo ""

# Check if .env file exists
if [ ! -f .env ]; then
    echo "❌ .env file not found!"
    echo "Creating .env from .env.example..."
    if [ -f .env.example ]; then
        cp .env.example .env
        php artisan key:generate
        echo "✅ .env file created"
    else
        echo "❌ .env.example not found. Please create .env manually."
        exit 1
    fi
fi

echo ""
echo "Checking MySQL connection..."
echo ""

# Test MySQL connection
mysql -u root -e "SELECT 1" 2>/dev/null
if [ $? -eq 0 ]; then
    echo "✅ MySQL is running and accessible"
else
    echo "⚠️  Could not connect to MySQL"
    echo "Please ensure:"
    echo "  1. XAMPP MySQL is started in XAMPP Control Panel"
    echo "  2. MySQL is running on port 3306"
    echo ""
    read -p "Continue anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

echo ""
echo "Updating .env file with MySQL configuration..."
echo ""

# Update .env file with MySQL settings
if grep -q "DB_CONNECTION=sqlite" .env; then
    sed -i '' 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' .env
    echo "✅ Changed DB_CONNECTION from sqlite to mysql"
fi

if grep -q "^DB_HOST=" .env; then
    sed -i '' 's/^DB_HOST=.*/DB_HOST=127.0.0.1/' .env
else
    echo "DB_HOST=127.0.0.1" >> .env
fi
echo "✅ Set DB_HOST=127.0.0.1"

if grep -q "^DB_PORT=" .env; then
    sed -i '' 's/^DB_PORT=.*/DB_PORT=3306/' .env
else
    echo "DB_PORT=3306" >> .env
fi
echo "✅ Set DB_PORT=3306"

# Ask for database name
echo ""
read -p "Enter database name (default: buypower): " db_name
db_name=${db_name:-buypower}

if grep -q "^DB_DATABASE=" .env; then
    sed -i '' "s/^DB_DATABASE=.*/DB_DATABASE=$db_name/" .env
else
    echo "DB_DATABASE=$db_name" >> .env
fi
echo "✅ Set DB_DATABASE=$db_name"

# Ask for username
echo ""
read -p "Enter MySQL username (default: root): " db_user
db_user=${db_user:-root}

if grep -q "^DB_USERNAME=" .env; then
    sed -i '' "s/^DB_USERNAME=.*/DB_USERNAME=$db_user/" .env
else
    echo "DB_USERNAME=$db_user" >> .env
fi
echo "✅ Set DB_USERNAME=$db_user"

# Ask for password
echo ""
read -sp "Enter MySQL password (default: empty, press Enter): " db_pass
echo ""

if grep -q "^DB_PASSWORD=" .env; then
    if [ -z "$db_pass" ]; then
        sed -i '' 's/^DB_PASSWORD=.*/DB_PASSWORD=/' .env
    else
        sed -i '' "s/^DB_PASSWORD=.*/DB_PASSWORD=$db_pass/" .env
    fi
else
    if [ -z "$db_pass" ]; then
        echo "DB_PASSWORD=" >> .env
    else
        echo "DB_PASSWORD=$db_pass" >> .env
    fi
fi
echo "✅ Set DB_PASSWORD"

echo ""
echo "=========================================="
echo "Creating database if it doesn't exist..."
echo "=========================================="
echo ""

# Create database
if [ -z "$db_pass" ]; then
    mysql -u "$db_user" -e "CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
else
    mysql -u "$db_user" -p"$db_pass" -e "CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null
fi

if [ $? -eq 0 ]; then
    echo "✅ Database '$db_name' created or already exists"
else
    echo "⚠️  Could not create database. Please create it manually in phpMyAdmin."
    echo "   Database name: $db_name"
    echo "   Collation: utf8mb4_unicode_ci"
fi

echo ""
echo "=========================================="
echo "Running Laravel migrations..."
echo "=========================================="
echo ""

# Run migrations
php artisan migrate --force

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Migrations completed successfully!"
else
    echo ""
    echo "⚠️  Migrations failed. Please check the error messages above."
    exit 1
fi

echo ""
echo "=========================================="
echo "✅ Configuration Complete!"
echo "=========================================="
echo ""
echo "Your Laravel application is now configured to use MySQL."
echo ""
echo "Next steps:"
echo "  1. Access phpMyAdmin: http://localhost/phpmyadmin"
echo "  2. Check your database: $db_name"
echo "  3. Run your Laravel app: php artisan serve"
echo ""
echo "Database Configuration:"
echo "  - Host: 127.0.0.1"
echo "  - Port: 3306"
echo "  - Database: $db_name"
echo "  - Username: $db_user"
echo "  - Password: " $([ -z "$db_pass" ] && echo "(empty)" || echo "***")
echo ""

