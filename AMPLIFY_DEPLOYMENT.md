# AWS Amplify Deployment Guide for Laravel

## Prerequisites

1. Ensure all environment variables are configured in AWS Amplify Console
2. Set up your database (RDS or external database)
3. Configure storage for file uploads (S3 recommended)

## AWS Amplify Console Configuration

### Step 1: App Settings
1. Go to your Amplify App in AWS Console
2. Navigate to **App settings** → **General**
3. Set the following:
   - **App root**: `/` (root directory)
   - **Web root**: `public` (Laravel's public directory)

### Step 2: Environment Variables
Add these environment variables in **App settings** → **Environment variables**:

**Required:**
- `APP_NAME` - Your application name
- `APP_ENV` - `production`
- `APP_KEY` - Generate with `php artisan key:generate` (or let Amplify generate it)
- `APP_DEBUG` - `false` (set to `true` only for debugging)
- `APP_URL` - Your Amplify app URL (e.g., `https://main.xxxxx.amplifyapp.com`)

**Database:**
- `DB_CONNECTION` - `mysql` or `pgsql`
- `DB_HOST` - Your database host
- `DB_PORT` - Database port (usually `3306` for MySQL)
- `DB_DATABASE` - Database name
- `DB_USERNAME` - Database username
- `DB_PASSWORD` - Database password

**BuyPower API:**
- `BUYPOWER_API_KEY` - Your BuyPower API key
- `BUYPOWER_API_URL` - BuyPower API endpoint URL

**Mail Configuration:**
- `MAIL_MAILER` - `smtp` or `ses`
- `MAIL_HOST` - SMTP host (if using SMTP)
- `MAIL_PORT` - SMTP port
- `MAIL_USERNAME` - SMTP username
- `MAIL_PASSWORD` - SMTP password
- `MAIL_ENCRYPTION` - `tls` or `ssl`
- `MAIL_FROM_ADDRESS` - Sender email address
- `MAIL_FROM_NAME` - Sender name

**Other Services:**
- `TERMII_API_KEY` - Termii SMS API key (if using SMS)
- `SHAREPOINT_CLIENT_ID` - SharePoint client ID (if using SharePoint)
- `SHAREPOINT_CLIENT_SECRET` - SharePoint client secret
- `SHAREPOINT_TENANT_ID` - SharePoint tenant ID

### Step 3: Build Settings
The `amplify.yml` file is already configured. Verify these settings:

1. **Build image**: Use a build image with PHP 8.2+ and Node.js
2. **Build commands**: Already configured in `amplify.yml`
3. **Artifacts**: Deploy entire application (not just public directory)

## Build Process

The `amplify.yml` file handles:
1. ✅ Installing Composer dependencies
2. ✅ Installing npm dependencies
3. ✅ Building frontend assets (Vite)
4. ✅ Caching Laravel configuration
5. ✅ Setting up storage directories

## Post-Deployment Steps

### 1. Run Migrations
After first deployment, run migrations:
```bash
# Connect to your Amplify app via SSH or use AWS Systems Manager
php artisan migrate --force
```

### 2. Create Storage Link
Create symbolic link for storage:
```bash
php artisan storage:link
```

### 3. Set Permissions
Ensure storage directories are writable:
```bash
chmod -R 775 storage bootstrap/cache
```

## Troubleshooting

### Build Fails
1. Check build logs in Amplify Console
2. Verify PHP version is 8.2+
3. Ensure all required environment variables are set
4. Check that Composer dependencies can be installed

### 500 Error After Deployment
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify `APP_KEY` is set
3. Ensure database connection is working
4. Check file permissions on `storage` and `bootstrap/cache`

### Assets Not Loading
1. Verify `npm run build` completed successfully
2. Check that `public/build` directory exists
3. Ensure `APP_URL` matches your Amplify domain

### Database Connection Issues
1. Verify database credentials in environment variables
2. Check security groups allow connections from Amplify
3. Ensure database is accessible from the internet (or use VPC)

## Important Notes

- **Never commit `.env` file** - Use Amplify environment variables instead
- **Storage**: Consider using S3 for file storage in production
- **Queue Workers**: Set up a separate worker process for queue jobs (not included in Amplify build)
- **Sessions**: Use database or Redis for sessions in production (not file-based)
- **Cache**: Configure Redis or Memcached for production caching

## Additional Resources

- [AWS Amplify Hosting Documentation](https://docs.aws.amazon.com/amplify/latest/userguide/welcome.html)
- [Laravel Deployment Documentation](https://laravel.com/docs/deployment)

