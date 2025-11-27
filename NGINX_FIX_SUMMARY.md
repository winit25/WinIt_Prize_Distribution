# Nginx Laravel Route Fix - Summary

## Problem
Laravel routes (like `/login`) return 404 errors on AWS Elastic Beanstalk AL2023 PHP platform, while the homepage works.

## Root Cause
EB AL2023 uses **Nginx** (not Apache), and the default PHP configuration doesn't include the `try_files` directive needed for Laravel's front controller pattern. The `.htaccess` file is ignored since nginx doesn't use it.

## Attempted Solutions

### 1. ✅ `.ebextensions/06_nginx_laravel.config`
- Uses `files` directive to create `/etc/nginx/conf.d/elasticbeanstalk/php.conf`
- **Status**: Deployed but may be overwritten by platform

### 2. ✅ `.platform/hooks/postdeploy/02_fix_nginx_laravel.sh`
- Post-deployment hook that checks multiple nginx config locations
- Updates config with `try_files` directive
- **Status**: Deployed, needs verification

## Current Status
- **Homepage (/):** 500 (database/application error)
- **Login (/login):** 404 (nginx routing issue)

## Next Steps

### Option 1: Verify Hook Execution
Check if the postdeploy hook is actually running:
```bash
eb logs | grep -A 20 "02_fix_nginx"
```

### Option 2: Manual SSH Verification
SSH into the instance and verify nginx config:
```bash
eb ssh --setup  # First time only
eb ssh
sudo cat /etc/nginx/conf.d/elasticbeanstalk/php.conf
sudo nginx -t
```

### Option 3: Use EB's Proxy Configuration
EB AL2023 might use a proxy configuration. Check:
- `/etc/nginx/conf.d/proxy.conf`
- `/etc/nginx/nginx.conf` server blocks

### Option 4: Switch to Apache Platform
If nginx continues to be problematic:
```bash
eb platform select
# Choose: 64bit Amazon Linux 2 v5.x.x running PHP 8.x (Apache)
```

### Option 5: Containerize with Docker
Full control over nginx configuration:
- Create `Dockerfile` with custom nginx config
- Use EB's Docker platform

## Files Created
- `.ebextensions/06_nginx_laravel.config` - Nginx config via ebextensions
- `.platform/hooks/postdeploy/02_fix_nginx_laravel.sh` - Post-deploy hook

## Testing
```bash
# Test routes
curl -I http://buypower-prod.eba-s3qg2nnk.us-east-1.elasticbeanstalk.com/
curl -I http://buypower-prod.eba-s3qg2nnk.us-east-1.elasticbeanstalk.com/login
```

