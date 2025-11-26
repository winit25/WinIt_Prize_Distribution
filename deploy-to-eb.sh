#!/bin/bash

# Elastic Beanstalk Deployment Script
# This script helps you deploy your Laravel application to AWS Elastic Beanstalk

set -e

echo "================================"
echo "Elastic Beanstalk Deployment"
echo "================================"
echo ""

# Check if EB CLI is installed
if ! command -v eb &> /dev/null; then
    echo "❌ EB CLI is not installed."
    echo ""
    echo "Install it with:"
    echo "  pip install awsebcli --upgrade --user"
    echo ""
    exit 1
fi

# Check if already initialized
if [ ! -d ".elasticbeanstalk" ]; then
    echo "📦 Initializing Elastic Beanstalk..."
    echo ""
    echo "You'll be prompted to:"
    echo "  1. Select your AWS region"
    echo "  2. Choose application name (suggest: buypower)"
    echo "  3. Select platform: PHP"
    echo "  4. Select platform version: PHP 8.2"
    echo "  5. Setup SSH (recommended: yes)"
    echo ""
    read -p "Press Enter to continue..."
    
    eb init
    
    echo ""
    echo "✅ EB initialized successfully!"
else
    echo "✅ EB already initialized"
fi

echo ""
echo "================================"
echo "Next Steps"
echo "================================"
echo ""
echo "1. Generate your Laravel APP_KEY:"
echo "   php artisan key:generate --show"
echo ""
echo "2. Create EB environment with database:"
echo "   eb create buypower-production --database"
echo ""
echo "3. Set environment variables:"
echo "   eb setenv APP_KEY=\"base64:YOUR_KEY_HERE\" \\"
echo "     APP_URL=\"http://your-eb-url.elasticbeanstalk.com\" \\"
echo "     DB_CONNECTION=mysql \\"
echo "     DB_HOST=\"your-rds-endpoint\" \\"
echo "     DB_DATABASE=ebdb \\"
echo "     DB_USERNAME=admin \\"
echo "     DB_PASSWORD=\"your-password\""
echo ""
echo "4. Deploy:"
echo "   eb deploy"
echo ""
echo "5. Open your app:"
echo "   eb open"
echo ""
echo "📖 For detailed instructions, see ELASTIC_BEANSTALK_DEPLOYMENT.md"
echo ""
