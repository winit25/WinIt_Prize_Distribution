# Elastic Beanstalk Deployment Guide

This guide will help you deploy your Laravel application to AWS Elastic Beanstalk.

## Prerequisites

1. **AWS CLI** - Install and configure
   ```bash
   # Install AWS CLI
   curl "https://awscli.amazonaws.com/AWSCLIV2.pkg" -o "AWSCLIV2.pkg"
   sudo installer -pkg AWSCLIV2.pkg -target /
   
   # Configure AWS credentials
   aws configure
   ```

2. **EB CLI** - Install Elastic Beanstalk CLI
   ```bash
   pip install awsebcli --upgrade --user
   ```

## Step 1: Initialize Elastic Beanstalk

```bash
# Initialize EB in your project
eb init

# Follow the prompts:
# - Select your region (e.g., us-east-1)
# - Enter application name: buypower
# - Select platform: PHP
# - Select platform version: PHP 8.2 (or latest available)
# - Setup SSH: Yes (optional but recommended)
```

## Step 2: Create RDS Database

```bash
# Create environment with RDS database
eb create buypower-production \
  --database \
  --database.engine mysql \
  --database.version 8.0 \
  --database.instance db.t3.micro \
  --database.username admin

# You'll be prompted for database password - save it securely
```

Or create environment first, then add RDS later:
```bash
# Create environment without database
eb create buypower-production

# Add RDS later via AWS Console:
# 1. Go to Elastic Beanstalk > Environments > Configuration
# 2. Edit Database settings
# 3. Add RDS instance
```

## Step 3: Configure Environment Variables

Set your Laravel environment variables in Elastic Beanstalk:

```bash
# Set environment variables
eb setenv \
  APP_KEY="base64:YOUR_APP_KEY_HERE" \
  APP_URL="http://your-eb-url.elasticbeanstalk.com" \
  DB_CONNECTION=mysql \
  DB_HOST="your-rds-endpoint.rds.amazonaws.com" \
  DB_PORT=3306 \
  DB_DATABASE=ebdb \
  DB_USERNAME=admin \
  DB_PASSWORD="your-db-password"

# Add your BuyPower API credentials
eb setenv \
  BUYPOWER_API_URL="your-api-url" \
  BUYPOWER_API_KEY="your-api-key" \
  BUYPOWER_SECRET_KEY="your-secret-key"
```

**IMPORTANT:** Generate APP_KEY locally first:
```bash
php artisan key:generate --show
# Copy the output and use it in eb setenv
```

## Step 4: Deploy Your Application

```bash
# Deploy to Elastic Beanstalk
eb deploy

# Monitor deployment
eb logs --stream
```

## Step 5: Access Your Application

```bash
# Open your application in browser
eb open

# Check status
eb status

# View logs
eb logs
```

## Environment Variables Required

Make sure to set these environment variables in EB:

### Required:
- `APP_KEY` - Laravel application key (generate with `php artisan key:generate --show`)
- `APP_URL` - Your application URL
- `DB_HOST` - RDS endpoint
- `DB_DATABASE` - Database name (usually `ebdb`)
- `DB_USERNAME` - Database username
- `DB_PASSWORD` - Database password

### Optional (based on your .env):
- `BUYPOWER_API_URL`
- `BUYPOWER_API_KEY`
- `BUYPOWER_SECRET_KEY`
- `MAIL_MAILER`
- `MAIL_HOST`
- `MAIL_PORT`
- `MAIL_USERNAME`
- `MAIL_PASSWORD`

## Configuration Files Created

- `.ebextensions/` - Deployment configuration
  - `01_packages.config` - System packages
  - `02_laravel.config` - Laravel-specific setup
  - `03_apache.config` - Apache configuration
  - `04_environment.config` - Environment settings
- `.platform/hooks/postdeploy/` - Post-deployment scripts
- `.ebignore` - Files to exclude from deployment

## Common Commands

```bash
# Deploy
eb deploy

# View logs
eb logs
eb logs --stream

# SSH into instance
eb ssh

# Check health
eb health

# Scale instances
eb scale 2

# Terminate environment
eb terminate buypower-production
```

## Scaling & Production Settings

### Enable Auto Scaling:
```bash
eb config
# Edit the configuration file and set:
# - Min instances: 2
# - Max instances: 4
# - Scaling trigger: CPU > 70%
```

### Enable HTTPS:
1. Request SSL certificate in AWS Certificate Manager
2. Configure Load Balancer to use HTTPS
3. Update APP_URL to use https://

```bash
# Add HTTPS listener via console or CLI
aws elasticbeanstalk update-environment \
  --environment-name buypower-production \
  --option-settings \
  Namespace=aws:elbv2:listener:443,OptionName=Protocol,Value=HTTPS \
  Namespace=aws:elbv2:listener:443,OptionName=SSLCertificateArns,Value=arn:aws:acm:REGION:ACCOUNT:certificate/CERT_ID
```

## Troubleshooting

### View detailed logs:
```bash
eb logs --all
```

### SSH into instance:
```bash
eb ssh
cd /var/app/current
php artisan --version
```

### Check Laravel logs:
```bash
eb logs | grep -A 20 "PHP Fatal error"
```

### Clear caches:
```bash
eb ssh
cd /var/app/current
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## Cost Optimization

- Start with **t3.micro** or **t3.small** instances
- Use **RDS t3.micro** for database
- Enable auto-scaling to handle traffic spikes
- Consider using **Reserved Instances** for production

## Database Migrations

Migrations run automatically on deployment (leader_only setting ensures they run once).

To run migrations manually:
```bash
eb ssh
cd /var/app/current
php artisan migrate
```

## Monitoring

- CloudWatch logs are automatically configured
- Monitor via AWS Console: Elastic Beanstalk > Monitoring
- Set up CloudWatch alarms for CPU, memory, and errors

## Rollback

If deployment fails:
```bash
# List versions
eb appversion

# Deploy specific version
eb deploy --version v1
```

## Next Steps

1. Set up custom domain name
2. Configure SSL certificate
3. Set up CloudWatch alarms
4. Configure backup strategy for RDS
5. Set up CI/CD pipeline (GitHub Actions, CodePipeline)

## Support

For issues or questions:
- AWS EB Docs: https://docs.aws.amazon.com/elasticbeanstalk/
- Laravel Deployment: https://laravel.com/docs/deployment
