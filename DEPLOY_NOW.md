# Deploy Split Architecture - Quick Guide

## ✅ Status Check

### Aurora RDS
```bash
# Check Aurora cluster
aws rds describe-db-clusters --db-cluster-identifier buypower-aurora-cluster --query 'DBClusters[0].{Status:Status,Endpoint:Endpoint}'
```

**Current Status:**
- ✅ Cluster: `available`
- ✅ Endpoint: `buypower-aurora-cluster.cluster-cuv4gm4eus9h.us-east-1.rds.amazonaws.com`
- 🔄 Instance: `buypower-aurora-instance-1` is creating (wait 5-10 min)
- 🔑 Password: `BuyP0wer#2025Secure!`
- 👤 Username: `admin`
- 🗄️ Database: `buypower_db`

---

## 🚀 Deployment Steps

### Step 1: Wait for Aurora Instance (5-10 minutes)

```bash
# Check status
aws rds describe-db-instances --db-instance-identifier buypower-aurora-instance-1 --query 'DBInstances[0].DBInstanceStatus'

# Wait until it shows "available"
```

### Step 2: Deploy Backend to Elastic Beanstalk

```bash
cd /Users/hopegainlimited/Downloads/buypower-backend-api

# Initialize EB (use default options)
eb init -p "PHP 8.2 running on 64bit Amazon Linux 2023" buypower-backend-api --region us-east-1

# Create environment
eb create buypower-backend-prod \
  --instance-type t3.small \
  --region us-east-1 \
  --elb-type application

# This takes 5-10 minutes
```

### Step 3: Configure Backend Environment Variables

```bash
cd /Users/hopegainlimited/Downloads/buypower-backend-api

# Generate app key first
php artisan key:generate --show
# Copy the output (base64:...)

# Set environment variables
eb setenv \
  APP_NAME="Buypower Backend API" \
  APP_ENV=production \
  APP_DEBUG=false \
  APP_KEY="base64:YOUR_KEY_FROM_ABOVE" \
  APP_URL="https://buypower-backend-prod.elasticbeanstalk.com" \
  FRONTEND_URL="https://main.AMPLIFY_ID.amplifyapp.com" \
  DB_CONNECTION=mysql \
  DB_HOST="buypower-aurora-cluster.cluster-cuv4gm4eus9h.us-east-1.rds.amazonaws.com" \
  DB_PORT=3306 \
  DB_DATABASE=buypower_db \
  DB_USERNAME=admin \
  DB_PASSWORD="BuyP0wer#2025Secure!" \
  SESSION_DRIVER=database \
  CACHE_STORE=database \
  QUEUE_CONNECTION=database
```

### Step 4: Run Migrations on Backend

```bash
# SSH into EB instance
eb ssh buypower-backend-prod

# Run migrations
cd /var/app/current
php artisan migrate --force

# Exit
exit
```

### Step 5: Test Backend API

```bash
# Get backend URL
eb status | grep CNAME

# Test health endpoint
curl https://buypower-backend-prod.REGION.elasticbeanstalk.com/health
```

### Step 6: Deploy Frontend to Amplify

**Option A: Via AWS Console (Recommended)**

1. Go to [AWS Amplify Console](https://console.aws.amazon.com/amplify/)
2. Click **"New app"** → **"Host web app"**
3. Connect your Git repository (GitHub/GitLab/Bitbucket)
   - Or upload `/Users/hopegainlimited/Downloads/buypower` as ZIP
4. Select branch: `main`
5. Build settings should auto-detect Laravel
6. Click **"Next"** and **"Save and deploy"**

**Option B: Via Amplify CLI**

```bash
# Install Amplify CLI
npm install -g @aws-amplify/cli

# Initialize
cd /Users/hopegainlimited/Downloads/buypower
amplify init

# Add hosting
amplify add hosting

# Deploy
amplify publish
```

### Step 7: Configure Frontend Environment Variables in Amplify

In Amplify Console → App Settings → Environment variables:

```env
APP_NAME=Buypower
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY
APP_DEBUG=false
APP_URL=https://main.AMPLIFY_ID.amplifyapp.com

# Backend API URL (from Step 5)
BACKEND_API_URL=https://buypower-backend-prod.REGION.elasticbeanstalk.com
BACKEND_API_TIMEOUT=30

# Aurora RDS
DB_CONNECTION=mysql
DB_HOST=buypower-aurora-cluster.cluster-cuv4gm4eus9h.us-east-1.rds.amazonaws.com
DB_PORT=3306
DB_DATABASE=buypower_db
DB_USERNAME=admin
DB_PASSWORD=BuyP0wer#2025Secure!

# Session & Cache
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync
```

### Step 8: Update Backend CORS

Once Amplify is deployed, get the URL and update backend:

```bash
cd /Users/hopegainlimited/Downloads/buypower-backend-api

# Update FRONTEND_URL
eb setenv FRONTEND_URL="https://main.YOUR_AMPLIFY_ID.amplifyapp.com"
eb setenv SANCTUM_STATEFUL_DOMAINS="main.YOUR_AMPLIFY_ID.amplifyapp.com"

# Redeploy
eb deploy
```

### Step 9: Configure Security Groups

**Allow Amplify & EB to access Aurora:**

```bash
# Get EB security group
aws elasticbeanstalk describe-environment-resources \
  --environment-name buypower-backend-prod \
  --query 'EnvironmentResources.Instances[0].Id' \
  --output text

# Get instance details to find security group
aws ec2 describe-instances --instance-ids YOUR_INSTANCE_ID \
  --query 'Reservations[0].Instances[0].SecurityGroups[0].GroupId' \
  --output text

# Get Aurora security group
aws rds describe-db-clusters \
  --db-cluster-identifier buypower-aurora-cluster \
  --query 'DBClusters[0].VpcSecurityGroups[0].VpcSecurityGroupId' \
  --output text

# Add rule to Aurora SG to allow EB
aws ec2 authorize-security-group-ingress \
  --group-id AURORA_SG_ID \
  --protocol tcp \
  --port 3306 \
  --source-group EB_SG_ID \
  --region us-east-1
```

---

## 🧪 Testing

### Test Backend API

```bash
# Health check
curl https://YOUR_BACKEND_URL/health

# API health
curl https://YOUR_BACKEND_URL/api/health
```

### Test Frontend

1. Open: `https://main.YOUR_AMPLIFY_ID.amplifyapp.com`
2. Login with: `admin@buypower.com` / `Temp#Admin2025!`
3. Test navigation
4. Try bulk operations (should call backend API)

### Check Logs

**Backend:**
```bash
cd /Users/hopegainlimited/Downloads/buypower-backend-api
eb logs
```

**Frontend:**
- Check Amplify Console → Logs

---

## 📋 Quick Commands Reference

### Backend (Elastic Beanstalk)

```bash
cd /Users/hopegainlimited/Downloads/buypower-backend-api

# Deploy changes
eb deploy

# View logs
eb logs
eb logs --stream

# SSH
eb ssh

# Status
eb status

# Open in browser
eb open

# Environment info
eb printenv
```

### Frontend (Amplify)

```bash
cd /Users/hopegainlimited/Downloads/buypower

# Redeploy
# Just push to Git, Amplify auto-deploys

# Or via CLI
amplify publish
```

### Aurora RDS

```bash
# Check cluster status
aws rds describe-db-clusters \
  --db-cluster-identifier buypower-aurora-cluster

# Check instance status
aws rds describe-db-instances \
  --db-instance-identifier buypower-aurora-instance-1

# Connect to database
mysql -h buypower-aurora-cluster.cluster-cuv4gm4eus9h.us-east-1.rds.amazonaws.com \
  -u admin -p buypower_db
# Password: BuyP0wer#2025Secure!
```

---

## 🔧 Troubleshooting

### Backend can't connect to Aurora

1. Check security groups allow connection
2. Verify DB credentials in environment variables
3. Test connection:
   ```bash
   eb ssh
   mysql -h $DB_HOST -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE
   ```

### Frontend can't reach Backend

1. Check BACKEND_API_URL is correct
2. Verify CORS settings in backend
3. Check browser console for errors

### Deployment fails

1. Check logs: `eb logs` or Amplify Console
2. Verify environment variables are set
3. Check IAM permissions

---

## 💰 Cost Estimate

**Monthly Costs:**

- **Aurora RDS (db.t4g.medium):** ~$50/month
- **Elastic Beanstalk (t3.small):** ~$15/month
- **Amplify (low traffic):** ~$5-15/month
- **Data Transfer:** ~$10/month

**Total:** ~$80-90/month for development/staging

---

## 🎯 Next Steps

1. ✅ Aurora RDS created (instance creating)
2. ⬜ Deploy backend to EB
3. ⬜ Configure environment variables
4. ⬜ Run migrations
5. ⬜ Deploy frontend to Amplify
6. ⬜ Update CORS settings
7. ⬜ Configure security groups
8. ⬜ Test end-to-end
9. ⬜ Set up custom domains (optional)
10. ⬜ Configure monitoring/alerts

---

**Ready to deploy!** Start with Step 1 (waiting for Aurora instance) and proceed through the steps.
