# AWS RDS MySQL Setup Guide for Laravel

This guide will help you set up an AWS RDS MySQL database and configure automatic migrations for your Laravel application on AWS Amplify.

## Step 1: Create AWS RDS MySQL Instance

### Via AWS Console:

1. **Navigate to RDS Console**
   - Go to AWS Console → RDS → Databases
   - Click "Create database"

2. **Choose Database Creation Method**
   - Select "Standard create"

3. **Engine Options**
   - **Engine type**: MySQL
   - **Version**: MySQL 8.0.x (recommended) or MySQL 5.7.x
   - **Templates**: 
     - For production: Production
     - For development/testing: Free tier (if eligible)

4. **Settings**
   - **DB instance identifier**: `buypower-db` (or your preferred name)
   - **Master username**: `admin` (or your preferred username)
   - **Master password**: Create a strong password (save this!)
   - **Confirm password**: Re-enter the password

5. **Instance Configuration**
   - **DB instance class**: 
     - Free tier: `db.t3.micro` or `db.t2.micro`
     - Production: `db.t3.small` or higher based on your needs

6. **Storage**
   - **Storage type**: General Purpose SSD (gp3)
   - **Allocated storage**: 20 GB (minimum, adjust based on needs)
   - **Storage autoscaling**: Enable (recommended)

7. **Connectivity**
   - **VPC**: Default VPC (or your custom VPC)
   - **Subnet group**: Default (or create custom)
   - **Public access**: 
     - **Yes** (for Amplify to connect) - Required for Amplify Hosting
     - **No** (if using VPC peering or VPN)
   - **VPC security group**: Create new or use existing
   - **Availability Zone**: No preference (or select specific)
   - **Database port**: `3306` (default MySQL port)

8. **Database Authentication**
   - **Database authentication**: Password authentication

9. **Additional Configuration**
   - **Initial database name**: `buypower` (or your preferred name)
   - **DB parameter group**: Default
   - **Backup retention period**: 7 days (recommended)
   - **Enable encryption**: Yes (recommended for production)
   - **Enable Enhanced monitoring**: Optional

10. **Click "Create database"**

### Wait for Database Creation
- This process takes 5-15 minutes
- Status will change from "Creating" to "Available"

## Step 2: Configure Security Group

1. **Find Your RDS Instance**
   - Go to RDS → Databases → Select your database
   - Click on the "Connectivity & security" tab

2. **Edit Security Group**
   - Click on the Security Group link
   - Click "Edit inbound rules"
   - Add rule:
     - **Type**: MySQL/Aurora
     - **Protocol**: TCP
     - **Port**: 3306
     - **Source**: 
       - For Amplify: `0.0.0.0/0` (allows all IPs - use with caution)
       - Better: Use Amplify's IP ranges or your specific IP

## Step 3: Get Database Connection Details

After your RDS instance is available:

1. **Endpoint**
   - Go to RDS → Databases → Select your database
   - Copy the **Endpoint** (e.g., `buypower-db.xxxxx.us-east-1.rds.amazonaws.com`)
   - Copy the **Port** (usually `3306`)

2. **Credentials**
   - **Username**: The master username you set (e.g., `admin`)
   - **Password**: The password you created

## Step 4: Configure AWS Amplify Environment Variables

1. **Go to AWS Amplify Console**
   - Navigate to your Amplify App
   - Go to **App settings** → **Environment variables**

2. **Add Database Variables**
   ```
   DB_CONNECTION=mysql
   DB_HOST=your-rds-endpoint.xxxxx.us-east-1.rds.amazonaws.com
   DB_PORT=3306
   DB_DATABASE=buypower
   DB_USERNAME=admin
   DB_PASSWORD=your-secure-password
   DB_CHARSET=utf8mb4
   DB_COLLATION=utf8mb4_unicode_ci
   ```

3. **Optional SSL Configuration** (if using SSL)
   ```
   MYSQL_ATTR_SSL_CA=/path/to/ca-cert.pem
   DB_SSL_VERIFY=false
   ```

## Step 5: Test Database Connection

### Option 1: Via Laravel Tinker (Local)
```bash
php artisan tinker
DB::connection()->getPdo();
```

### Option 2: Via MySQL Client
```bash
mysql -h your-rds-endpoint.xxxxx.us-east-1.rds.amazonaws.com -u admin -p
# Enter password when prompted
```

## Step 6: Automatic Migrations

The `amplify.yml` file is already configured to run migrations automatically during build. It will:

1. Check if database credentials are set
2. Run `php artisan migrate --force` automatically
3. Skip migrations if database is not configured

### Migration Behavior:
- ✅ **First deployment**: Creates all tables
- ✅ **Subsequent deployments**: Only runs new migrations
- ✅ **Safe**: Uses `--force` flag (required for production)
- ✅ **Idempotent**: Won't re-run completed migrations

## Step 7: Verify Migration Success

After deployment:

1. **Check Build Logs**
   - Go to Amplify Console → Your App → Build history
   - Look for: "Running database migrations..."
   - Should see: "Migration completed successfully"

2. **Verify Tables**
   - Connect to your RDS database
   - Run: `SHOW TABLES;`
   - Should see all Laravel tables (users, migrations, batch_uploads, etc.)

## Troubleshooting

### Connection Timeout
- **Issue**: Cannot connect to RDS from Amplify
- **Solution**: 
  - Ensure Security Group allows inbound connections on port 3306
  - Verify RDS instance has "Public access" enabled
  - Check that endpoint is correct

### Migration Fails During Build
- **Issue**: Migrations fail in Amplify build
- **Solution**:
  - Verify all DB_* environment variables are set correctly
  - Check build logs for specific error messages
  - Ensure database exists (RDS creates it automatically if specified)
  - Verify user has CREATE TABLE permissions

### SSL Connection Issues
- **Issue**: SSL connection errors
- **Solution**:
  - Set `DB_SSL_VERIFY=false` in environment variables
  - Or download RDS CA certificate and configure `MYSQL_ATTR_SSL_CA`

### Database Not Found
- **Issue**: "Database 'buypower' not found"
- **Solution**:
  - Ensure `DB_DATABASE` matches the initial database name set in RDS
  - Or create the database manually:
    ```sql
    CREATE DATABASE buypower CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    ```

## Security Best Practices

1. **Use Strong Passwords**: Generate complex passwords for database users
2. **Restrict Security Group**: Only allow connections from Amplify IPs (if possible)
3. **Enable Encryption**: Use RDS encryption at rest
4. **Regular Backups**: Configure automated backups
5. **Use Secrets Manager**: Store database credentials in AWS Secrets Manager (advanced)
6. **Network Isolation**: Use VPC for production (requires VPC peering or VPN)

## Cost Optimization

1. **Use Free Tier**: If eligible, use `db.t3.micro` or `db.t2.micro`
2. **Stop When Not in Use**: Stop RDS instance when not in use (dev/test)
3. **Reserved Instances**: For production, consider Reserved Instances for savings
4. **Storage Optimization**: Monitor storage usage and adjust as needed

## Additional Resources

- [AWS RDS Documentation](https://docs.aws.amazon.com/rds/)
- [Laravel Database Configuration](https://laravel.com/docs/database)
- [AWS Amplify Environment Variables](https://docs.aws.amazon.com/amplify/latest/userguide/environment-variables.html)

## Quick Reference: Environment Variables

Add these to AWS Amplify Console → Environment Variables:

```bash
# Database Configuration
DB_CONNECTION=mysql
DB_HOST=your-rds-endpoint.rds.amazonaws.com
DB_PORT=3306
DB_DATABASE=buypower
DB_USERNAME=admin
DB_PASSWORD=your-secure-password

# Optional SSL
DB_SSL_VERIFY=false
```

After adding these variables, trigger a new deployment to run migrations automatically!

