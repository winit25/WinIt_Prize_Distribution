# Deploy Frontend to AWS Amplify - Step by Step

## ✅ Pre-requisites Complete
- ✅ Code pushed to GitHub: `github.com/winit25/WinIt_Prize_Distribution`
- ✅ Branch: `main`
- ✅ `amplify.yml` configured
- ✅ Backend API client service created
- ✅ All documentation ready

---

## 🚀 Deployment Steps

### Step 1: Open AWS Amplify Console

1. Go to: https://console.aws.amazon.com/amplify/
2. Click **"Create new app"** or **"New app"** → **"Host web app"**

### Step 2: Connect Repository

1. Select **"GitHub"** as the source
2. Click **"Continue"**
3. **Authorize AWS Amplify** to access your GitHub account
   - Click "Authorize AWS Amplify"
   - Enter your GitHub password if prompted
4. Select Repository: **"winit25/WinIt_Prize_Distribution"**
5. Select Branch: **"main"**
6. Click **"Next"**

### Step 3: Configure Build Settings

**App name:** `buypower-frontend`

**Build settings:**

The `amplify.yml` file should be auto-detected. Verify it contains:

```yaml
version: 1
frontend:
  phases:
    preBuild:
      commands:
        - echo "Installing Composer dependencies"
        - composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
        - echo "Installing Node.js dependencies"
        - npm ci || npm install
        - echo "Generating application key"
        - php artisan key:generate --force
    build:
      commands:
        - echo "Building frontend assets with Vite"
        - npm run build
        - echo "Caching Laravel configuration"
        - php artisan config:cache
        - php artisan route:cache
        - php artisan view:cache
        - echo "Creating storage link"
        - php artisan storage:link || true
  artifacts:
    baseDirectory: /
    files:
      - '**/*'
      - '.env.example'
  cache:
    paths:
      - vendor/**/*
      - node_modules/**/*
      - bootstrap/cache/**/*
```

Click **"Next"**

### Step 4: Review and Deploy

1. Review all settings
2. Click **"Save and deploy"**
3. Wait for deployment (10-15 minutes)

---

## ⚙️ Step 5: Configure Environment Variables

Once the app is deployed:

1. Go to **App settings** → **Environment variables**
2. Click **"Manage variables"**
3. Add the following variables:

### Required Environment Variables:

```env
APP_NAME=Buypower
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=UTC

# Generate this locally: php artisan key:generate --show
APP_KEY=base64:YOUR_GENERATED_KEY_HERE

# Will be: https://main.AMPLIFY_APP_ID.amplifyapp.com
APP_URL=https://main.AMPLIFY_APP_ID.amplifyapp.com

# Backend API URL (get from EB deployment)
BACKEND_API_URL=https://buypower-backend-prod.REGION.elasticbeanstalk.com
BACKEND_API_TIMEOUT=30

# Aurora RDS Database
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

# Mail Configuration (optional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=your-email@gmail.com

# Sanctum
SANCTUM_STATEFUL_DOMAINS=main.AMPLIFY_APP_ID.amplifyapp.com
```

4. Click **"Save"**
5. Go to **Actions** → **Redeploy this version**

---

## 🔧 Step 6: Configure Build Settings

1. Go to **App settings** → **Build settings**
2. Click **"Edit"**
3. **Build image:** Select `Amazon Linux 2023`
4. Under **Build specification**:
   - Ensure PHP version: `8.2`
   - Ensure Node version: `18` or `20`
5. Click **"Save"**

---

## 🔀 Step 7: Configure Redirects (Important for Laravel)

1. Go to **App settings** → **Rewrites and redirects**
2. Click **"Edit"**
3. Add this rule:

```
Source: </^[^.]+$|\.(?!(css|gif|ico|jpg|js|png|txt|svg|woff|woff2|ttf|map|json)$)([^.]+$)/>
Target: /index.php
Type: 200 (Rewrite)
```

4. Click **"Save"**

This ensures all requests go through Laravel's routing.

---

## 🔒 Step 8: Configure Security Groups for Aurora Access

Amplify needs access to Aurora RDS. Two options:

### Option A: Allow from VPC (Recommended)

1. Get your Amplify app's VPC (if deployed in VPC)
2. Update Aurora security group to allow traffic from Amplify's VPC

### Option B: Temporary Public Access (For Testing Only)

**⚠️ Not recommended for production**

```bash
# Get Aurora security group
aws rds describe-db-clusters \
  --db-cluster-identifier buypower-aurora-cluster \
  --query 'DBClusters[0].VpcSecurityGroups[0].VpcSecurityGroupId' \
  --output text

# Allow all IPs temporarily (TESTING ONLY)
aws ec2 authorize-security-group-ingress \
  --group-id YOUR_AURORA_SG \
  --protocol tcp \
  --port 3306 \
  --cidr 0.0.0.0/0
```

**Remember to restrict this after testing!**

---

## 🧪 Step 9: Test the Deployment

### Get Your Amplify URL

1. Go to your Amplify app
2. Find the URL: `https://main.AMPLIFY_APP_ID.amplifyapp.com`

### Test the Application

1. **Open URL** in browser
2. **Login:**
   - Email: `admin@buypower.com`
   - Password: `Temp#Admin2025!`
3. **Test Navigation:**
   - Dashboard
   - User Management
   - Profile
4. **Check Console:**
   - Open browser DevTools
   - Check for any errors
   - Verify API calls go to backend URL

---

## 🔄 Step 10: Update Backend CORS

Once you have the Amplify URL, update the backend:

```bash
cd /Users/hopegainlimited/Downloads/buypower-backend-api

# Update with your actual Amplify URL
eb setenv FRONTEND_URL="https://main.YOUR_AMPLIFY_ID.amplifyapp.com"
eb setenv SANCTUM_STATEFUL_DOMAINS="main.YOUR_AMPLIFY_ID.amplifyapp.com"

# Redeploy backend
eb deploy
```

---

## 📊 Monitor Deployment

### View Build Logs

1. Go to your Amplify app
2. Click on the build in progress
3. View real-time logs for each phase:
   - Provision
   - Build
   - Deploy
   - Verify

### Common Build Issues

**Issue: Composer install fails**
- Solution: Check PHP version is 8.2 in build settings

**Issue: NPM build fails**
- Solution: Ensure Node 18+ in build settings

**Issue: Permissions error**
- Solution: Add to build commands: `chmod -R 775 storage bootstrap/cache`

---

## 🎯 Post-Deployment Checklist

- [ ] Frontend accessible at Amplify URL
- [ ] Login works
- [ ] Dashboard loads
- [ ] Backend API calls successful (check browser console)
- [ ] No CORS errors
- [ ] Database connection working
- [ ] Sessions persist
- [ ] Updated backend with Amplify URL

---

## 🔧 Useful Amplify Commands

### Trigger Manual Deployment

```bash
aws amplify start-job \
  --app-id YOUR_APP_ID \
  --branch-name main \
  --job-type RELEASE
```

### Get App Info

```bash
aws amplify list-apps

aws amplify get-app --app-id YOUR_APP_ID
```

### View Environment Variables

```bash
aws amplify get-branch \
  --app-id YOUR_APP_ID \
  --branch-name main \
  --query 'branch.environmentVariables'
```

---

## 💰 Cost Estimate

**Amplify Costs:**
- **Build minutes:** $0.01 per minute (~10 min build = $0.10)
- **Hosting:** $0.15 per GB served + $0.023 per GB stored
- **First 1000 build minutes:** Free tier
- **First 15 GB served:** Free tier

**Estimated Monthly Cost:** $5-15 for low traffic

---

## 🔗 Important URLs

- **Amplify Console:** https://console.aws.amazon.com/amplify/
- **Your GitHub Repo:** https://github.com/winit25/WinIt_Prize_Distribution
- **AWS Documentation:** https://docs.aws.amazon.com/amplify/

---

## 📝 Next Steps After Deployment

1. **Custom Domain** (Optional)
   - Go to App settings → Domain management
   - Add your domain
   - Configure DNS

2. **Branch Deployments**
   - Add `develop` branch for staging
   - Configure preview deployments

3. **Notifications**
   - Set up SNS notifications for build failures
   - Configure Slack/Email alerts

4. **Monitoring**
   - Enable CloudWatch logs
   - Set up performance monitoring

---

## ✅ Deployment Complete!

Your frontend should now be:
- ✅ Deployed to Amplify
- ✅ Connected to Aurora RDS
- ✅ Making API calls to EB backend
- ✅ Auto-deploying on git push

**Frontend URL:** `https://main.YOUR_AMPLIFY_ID.amplifyapp.com`

**Login:** `admin@buypower.com` / `Temp#Admin2025!`

---

🎉 **Congratulations! Your split architecture is now deployed!**
