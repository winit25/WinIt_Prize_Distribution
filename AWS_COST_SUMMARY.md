# AWS Cost Summary & Configuration

## ✅ Services Currently Active

### 1. AWS Elastic Beanstalk
- **Platform:** PHP 8.2 on Amazon Linux 2023
- **Environment:** buypower-prod
- **Estimated Cost:** $35-55/month
  - EC2 Instances (t3.small): ~$15-30/month per instance
  - Load Balancer: ~$16-20/month
  - Data Transfer: ~$0.09/GB (first 10GB free)

### 2. Amazon Aurora RDS (MySQL)
- **Cluster:** buypower-aurora-cluster
- **Instance:** db.t4g.medium
- **Estimated Cost:** $55-75/month
  - Instance: ~$50-70/month
  - Storage (20GB): ~$2.30/month
  - Backup Storage: ~$0.095/GB/month
  - I/O Requests: ~$0.20 per 1M requests

### 3. AWS CloudWatch
- **Purpose:** Logs and monitoring
- **Estimated Cost:** $1-5/month
  - Log Ingestion: ~$0.50/GB
  - Log Storage: ~$0.03/GB/month

## 📊 Total Estimated Monthly Cost: **$91-135/month**

---

## ❌ Removed/Unused Services

### Amazon S3
- **Status:** Configuration commented out
- **Reason:** Using local storage instead
- **Savings:** $0 (wasn't being used)

### Amazon SES (Simple Email Service)
- **Status:** Configuration commented out
- **Reason:** Not actively sending emails via SES
- **Savings:** $0 (wasn't being used)

---

## 💡 Cost Optimization Tips

1. **Reserved Instances:** Save up to 40% on EC2 and RDS
2. **Right-size instances:** Monitor usage and adjust instance sizes
3. **Aurora Serverless v2:** Consider for variable workloads
4. **CloudWatch Log Retention:** Set retention policies to reduce storage costs
5. **Data Transfer:** Optimize to stay within free tier limits

---

## 🔐 Superadmin Auto-Creation

The superadmin user is now **automatically created** during deployment via `SuperAdminSeeder`.

**Credentials:**
- **Email:** `superadmin@buypower.com`
- **Password:** `SuperAdmin@2025`

**How it works:**
1. `SuperAdminSeeder` runs automatically after migrations
2. Creates super-admin role if it doesn't exist
3. Assigns all permissions to super-admin role
4. Creates/updates superadmin user
5. Assigns super-admin role to user

**Deployment Hook:** `.platform/hooks/postdeploy/01_migrate.sh`

---

## 📝 Notes

- All unused AWS service configurations have been removed/commented out
- Only actively used services remain configured
- Superadmin is created automatically on every deployment
- Cost estimates are based on typical usage patterns

