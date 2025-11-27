#!/bin/bash
# Script to create superadmin on AWS Elastic Beanstalk
# Run this after SSH into EB instance

cd /var/app/current
php artisan admin:create-superadmin --email=superadmin@buypower.com --password=SuperAdmin@2025

echo ""
echo "✅ Superadmin created!"
echo "Email: superadmin@buypower.com"
echo "Password: SuperAdmin@2025"

