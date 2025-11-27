# Aurora RDS Setup Guide

This guide walks you through setting up Amazon Aurora MySQL for your split architecture (Amplify Frontend + Elastic Beanstalk Backend).

## Overview

Aurora RDS will be the **shared database** for both:
- **Frontend (Amplify)**: User authentication, sessions, user management
- **Backend (Elastic Beanstalk)**: Bulk operations, transactions, business logic

## Prerequisites

- AWS CLI installed and configured
- AWS account with appropriate permissions
- Knowledge of your AWS region (e.g., `us-east-1`)

## Option 1: Create Aurora Cluster via AWS Console (Recommended for First-Time Setup)

### Step 1: Navigate to RDS Console

1. Go to [AWS RDS Console](https://console.aws.amazon.com/rds/)
2. Click **"Create database"**

### Step 2: Choose Database Creation Method

- Select **"Standard create"**
- Engine type: **Amazon Aurora**
- Edition: **Amazon Aurora MySQL-Compatible Edition**
- Engine version: **Aurora MySQL 8.0.mysql_aurora.3.05.2** (or latest 8.0 compatible)

### Step 3: Configure Database Settings

**Templates:**
- Development/Testing: Select **"Dev/Test"**
- Production: Select **"Production"**

**DB Cluster Identifier:**
```
buypower-aurora-cluster
```

**Credentials:**
- Master username: `admin`
- Master password: **(Create a strong password and save it securely)**
- Confirm password

**💡 Important:** Save these credentials - you'll need them for both Amplify and EB environment variables.

### Step 4: Instance Configuration

**DB Instance Class:**
- Development: `db.t3.small` (~$30/month) or `db.t4g.medium` (ARM-based, cheaper)
- Production: `db.r6g.large` or higher

**Multi-AZ Deployment:**
- Development: **"Don't create an Aurora Replica"**
- Production: **"Create an Aurora Replica"** (for high availability)

### Step 5: Connectivity

**Compute Resource:**
- Select **"Don't connect to an EC2 compute resource"** (we'll configure manually)

**Network Type:**
- IPv4

**Virtual Private Cloud (VPC):**
- Select your default VPC or create a new one

**DB Subnet Group:**
- Use default or create new

**Public Access:**
- **No** (security best practice)

**VPC Security Group:**
- Create new security group: `buypower-aurora-sg`
- Or select existing security group

**Availability Zone:**
- No preference (recommended)

**Database Port:**
- **3306** (MySQL default)

### Step 6: Database Authentication

- Select **"Password authentication"**

### Step 7: Additional Configuration

**Initial Database Name:**
```
buypower_db
```

**DB Cluster Parameter Group:**
- Use default: `default.aurora-mysql8.0`

**DB Parameter Group:**
- Use default: `default.aurora-mysql8.0`

**Backup:**
- Backup retention period: **7 days** (development) or **30 days** (production)
- Backup window: Choose a time window (e.g., 03:00-04:00 UTC)

**Encryption:**
- ✅ Enable encryption (recommended)
- Use default AWS KMS key or create custom key

**Backtrack:**
- Optional: Enable for point-in-time recovery (up to 72 hours)

**Maintenance:**
- Enable auto minor version upgrade: **Yes**
- Maintenance window: Choose a time window

**Deletion Protection:**
- Development: **Disable**
- Production: **Enable**

### Step 8: Create Database

Click **"Create database"** - this will take 5-10 minutes.

### Step 9: Configure Security Group

Once the cluster is created:

1. Navigate to your Aurora cluster
2. Click on the **"Connectivity & security"** tab
3. Note the **Writer endpoint** (e.g., `buypower-aurora-cluster.cluster-xxxxx.us-east-1.rds.amazonaws.com`)
4. Click on the **Security Group** (e.g., `buypower-aurora-sg`)
5. Click **"Edit inbound rules"**
6. Add rules:

**For Elastic Beanstalk:**
```
Type: MySQL/Aurora
Protocol: TCP
Port: 3306
Source: <EB-security-group> or <EB-VPC-CIDR>
Description: Allow EB Backend to access Aurora
```

**For Amplify:**
```
Type: MySQL/Aurora
Protocol: TCP
Port: 3306
Source: <Amplify-IP-ranges> or allow from VPC
Description: Allow Amplify Frontend to access Aurora
```

**Note:** Amplify uses dynamic IPs. You may need to:
- Allow entire VPC CIDR block, OR
- Use VPC Peering/PrivateLink, OR
- For testing: Temporarily allow your IP

### Step 10: Get Connection Details

From the Aurora cluster page, note:

- **Writer Endpoint:** `buypower-aurora-cluster.cluster-xxxxx.region.rds.amazonaws.com`
- **Reader Endpoint:** `buypower-aurora-cluster.cluster-ro-xxxxx.region.rds.amazonaws.com`
- **Port:** `3306`
- **Database Name:** `buypower_db`
- **Username:** `admin`
- **Password:** **(your saved password)**

---

## Option 2: Create Aurora Cluster via AWS CLI

### Step 1: Create DB Subnet Group

```bash
aws rds create-db-subnet-group \
  --db-subnet-group-name buypower-aurora-subnet-group \
  --db-subnet-group-description "Subnet group for Buypower Aurora cluster" \
  --subnet-ids subnet-xxxxx subnet-yyyyy \
  --region us-east-1
```

Replace `subnet-xxxxx` and `subnet-yyyyy` with your VPC subnet IDs.

### Step 2: Create Security Group

```bash
# Create security group
aws ec2 create-security-group \
  --group-name buypower-aurora-sg \
  --description "Security group for Buypower Aurora RDS" \
  --vpc-id vpc-xxxxx \
  --region us-east-1

# Note the security group ID from the output
SG_ID="sg-xxxxx"

# Allow MySQL access from EB security group or VPC CIDR
aws ec2 authorize-security-group-ingress \
  --group-id $SG_ID \
  --protocol tcp \
  --port 3306 \
  --cidr 10.0.0.0/16 \
  --region us-east-1
```

### Step 3: Create Aurora Cluster

```bash
aws rds create-db-cluster \
  --db-cluster-identifier buypower-aurora-cluster \
  --engine aurora-mysql \
  --engine-version 8.0.mysql_aurora.3.05.2 \
  --master-username admin \
  --master-user-password "YOUR_STRONG_PASSWORD_HERE" \
  --database-name buypower_db \
  --db-subnet-group-name buypower-aurora-subnet-group \
  --vpc-security-group-ids $SG_ID \
  --backup-retention-period 7 \
  --preferred-backup-window "03:00-04:00" \
  --preferred-maintenance-window "sun:04:00-sun:05:00" \
  --storage-encrypted \
  --region us-east-1
```

### Step 4: Create Aurora Instance

```bash
aws rds create-db-instance \
  --db-instance-identifier buypower-aurora-instance-1 \
  --db-instance-class db.t3.small \
  --engine aurora-mysql \
  --db-cluster-identifier buypower-aurora-cluster \
  --publicly-accessible false \
  --region us-east-1
```

### Step 5: Wait for Cluster to be Available

```bash
aws rds wait db-cluster-available \
  --db-cluster-identifier buypower-aurora-cluster \
  --region us-east-1

echo "Aurora cluster is now available!"
```

### Step 6: Get Connection Details

```bash
aws rds describe-db-clusters \
  --db-cluster-identifier buypower-aurora-cluster \
  --region us-east-1 \
  --query 'DBClusters[0].[Endpoint,ReaderEndpoint,Port,DatabaseName]' \
  --output table
```

---

## Configure Environment Variables

Once Aurora is created, you'll need to set these environment variables:

### For Backend (Elastic Beanstalk)

```bash
eb setenv \
  DB_CONNECTION=mysql \
  DB_HOST="buypower-aurora-cluster.cluster-xxxxx.us-east-1.rds.amazonaws.com" \
  DB_PORT=3306 \
  DB_DATABASE=buypower_db \
  DB_USERNAME=admin \
  DB_PASSWORD="your-password-here"
```

### For Frontend (AWS Amplify)

In Amplify Console:
1. Go to your app → Environment variables
2. Add:
```
DB_CONNECTION=mysql
DB_HOST=buypower-aurora-cluster.cluster-xxxxx.us-east-1.rds.amazonaws.com
DB_PORT=3306
DB_DATABASE=buypower_db
DB_USERNAME=admin
DB_PASSWORD=your-password-here
```

---

## Run Database Migrations

### Option 1: From Elastic Beanstalk (Recommended)

After deploying your backend to EB:

```bash
# SSH into EB instance
eb ssh

# Navigate to app directory
cd /var/app/current

# Run migrations
php artisan migrate --force

# Seed database (if needed)
php artisan db:seed --force
```

### Option 2: From Local Machine (Testing Only)

**⚠️ Warning:** Only use this for testing. Requires temporary public access to Aurora.

```bash
# Update local .env with Aurora credentials
DB_HOST=buypower-aurora-cluster.cluster-xxxxx.us-east-1.rds.amazonaws.com
DB_PORT=3306
DB_DATABASE=buypower_db
DB_USERNAME=admin
DB_PASSWORD=your-password

# Temporarily allow your IP in security group, then:
php artisan migrate
php artisan db:seed
```

---

## Testing Connection

### Test from Backend (EB)

Create a test route in `routes/web.php`:

```php
Route::get('/test-db', function () {
    try {
        DB::connection()->getPdo();
        return response()->json([
            'success' => true,
            'message' => 'Database connection successful',
            'database' => config('database.connections.mysql.database'),
            'host' => config('database.connections.mysql.host')
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Database connection failed',
            'error' => $e->getMessage()
        ], 500);
    }
});
```

Access: `https://your-eb-env.elasticbeanstalk.com/test-db`

---

## Cost Estimates

### Development/Testing
- **db.t3.small** (2 vCPU, 2GB RAM): ~$40/month
- **db.t4g.medium** (2 vCPU, 4GB RAM, ARM): ~$50/month
- Storage: $0.10/GB/month (first 20GB free with Aurora)
- Backups: Free up to DB size

**Total: ~$40-60/month**

### Production (with High Availability)
- **db.r6g.large** (2 vCPU, 16GB RAM): ~$180/month
- **Aurora Replica** (for read scaling): ~$180/month
- Storage: $0.10/GB/month
- Backups: Free up to DB size

**Total: ~$360-400/month**

---

## Security Best Practices

1. ✅ **No Public Access**: Never enable public access in production
2. ✅ **Encryption**: Enable encryption at rest
3. ✅ **SSL/TLS**: Enforce SSL connections (configure in Laravel)
4. ✅ **Security Groups**: Restrict access to only EB and Amplify
5. ✅ **IAM Authentication**: Consider using IAM database authentication
6. ✅ **Secrets Manager**: Store credentials in AWS Secrets Manager
7. ✅ **Deletion Protection**: Enable in production
8. ✅ **Automated Backups**: Set appropriate retention period

---

## Monitoring

### CloudWatch Metrics
- CPU Utilization
- Database Connections
- Replica Lag
- Storage Usage

### Set Up Alarms

```bash
aws cloudwatch put-metric-alarm \
  --alarm-name buypower-aurora-high-cpu \
  --alarm-description "Alert when CPU exceeds 80%" \
  --metric-name CPUUtilization \
  --namespace AWS/RDS \
  --statistic Average \
  --period 300 \
  --threshold 80 \
  --comparison-operator GreaterThanThreshold \
  --evaluation-periods 2 \
  --dimensions Name=DBClusterIdentifier,Value=buypower-aurora-cluster
```

---

## Troubleshooting

### Connection Timeout
- Check security group rules
- Verify VPC and subnet configuration
- Ensure EB/Amplify is in same VPC or has VPC peering

### Authentication Failed
- Verify credentials
- Check if user has proper permissions
- Try resetting master password

### Too Many Connections
- Increase `max_connections` parameter
- Scale up instance size
- Add read replicas

---

## Next Steps

- ✅ Aurora RDS cluster created and configured
- ⬜ Create backend API (Phase 2)
- ⬜ Update frontend to call backend API (Phase 3)
- ⬜ Deploy to EB and Amplify (Phase 4)

---

## Useful Commands

```bash
# Check cluster status
aws rds describe-db-clusters --db-cluster-identifier buypower-aurora-cluster

# Modify cluster
aws rds modify-db-cluster --db-cluster-identifier buypower-aurora-cluster --apply-immediately

# Create snapshot
aws rds create-db-cluster-snapshot \
  --db-cluster-snapshot-identifier buypower-snapshot-$(date +%Y%m%d) \
  --db-cluster-identifier buypower-aurora-cluster

# Delete cluster (CAREFUL!)
aws rds delete-db-cluster \
  --db-cluster-identifier buypower-aurora-cluster \
  --skip-final-snapshot
```

---

**Ready to proceed?** Once Aurora is set up, move to Phase 2: Backend API creation.
