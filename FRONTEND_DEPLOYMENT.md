# Frontend Deployment Guide - AWS Amplify

This guide covers deploying the Buypower Frontend (Laravel with Blade views) to AWS Amplify.

## Architecture Overview

```
┌────────────────────────────────────────────────────────┐
│              AWS Amplify Frontend                      │
│          (Laravel + Blade + Vite Assets)               │
│                                                         │
│  ┌─────────────┐  ┌─────────────┐  ┌──────────────┐  │
│  │  Auth Pages │  │  Dashboard  │  │ User Mgmt    │  │
│  │  • Login    │  │  • Overview │  │ • Profile    │  │
│  │  • Register │  │  • Stats    │  │ • Settings   │  │
│  │  • Reset PW │  │  • Nav      │  │ • Roles      │  │
│  └─────────────┘  └─────────────┘  └──────────────┘  │
│         │                │                  │          │
│         └────────────────┼──────────────────┘          │
│                          │                             │
│                   HTTP Client                          │
│             (BackendApiClient Service)                 │
│                          │                             │
└──────────────────────────┼─────────────────────────────┘
                           │
                           │ API Calls (Sanctum Token)
                           ▼
               ┌───────────────────────┐
               │  Backend API (EB)     │
               │  Business Logic       │
               └───────────────────────┘
```

## Phase 3: Frontend Deployment to Amplify

### Prerequisites

1. **Backend API deployed** - Must be running on Elastic Beanstalk
2. **Aurora RDS setup** - Database must be accessible to both frontend and backend
3. **AWS Account** - With Amplify access
4. **GitHub/GitLab repo** - For automatic deployments (recommended)

### Step 1: Prepare Frontend Code

#### Update Controllers (IMPORTANT)

Before deploying, you MUST update your controllers to proxy to the backend API. See `CONTROLLER_PROXY_GUIDE.md` for detailed instructions.

**Quick summary:**
```bash
# Controllers to update:
- app/Http/Controllers/BulkTokenController.php
- app/Http/Controllers/BulkAirtimeController.php
- app/Http/Controllers/BulkDstvController.php
- app/Http/Controllers/NotificationController.php
```

Example update:
```php
use App\Services\BackendApiClient;

public function __construct(BackendApiClient $backendApi)
{
    $this->backendApi = $backendApi;
}

public function upload(Request $request)
{
    $response = $this->backendApi->upload(
        'bulk-token/upload',
        'csv_file',
        $request->file('csv_file')
    );
    
    // Return response to view
}
```

#### Install Dependencies Locally

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Generate app key
php artisan key:generate
```

#### Test Locally

```bash
# Set backend API URL in .env
BACKEND_API_URL=https://buypower-backend-prod.elasticbeanstalk.com

# Start local server
php artisan serve

# In another terminal, build assets
npm run dev

# Test at http://localhost:8000
```

### Step 2: Push Code to Git Repository

Amplify works best with Git-based deployments.

```bash
# Initialize git (if not already done)
git init

# Add all files
git add .

# Commit
git commit -m "Prepare frontend for Amplify deployment"

# Add remote (GitHub/GitLab/Bitbucket)
git remote add origin https://github.com/yourusername/buypower-frontend.git

# Push
git push -u origin main
```

### Step 3: Create Amplify App

#### Option 1: Via AWS Console (Recommended)

1. Go to [AWS Amplify Console](https://console.aws.amazon.com/amplify/)
2. Click **"New app"** → **"Host web app"**
3. Select your Git provider (GitHub, GitLab, Bitbucket)
4. Authorize AWS Amplify to access your repository
5. Select the repository and branch (e.g., `main`)
6. Click **"Next"**

#### Configure Build Settings

Amplify should auto-detect Laravel/PHP. Verify the build settings match `amplify.yml`:

```yaml
version: 1
frontend:
  phases:
    preBuild:
      commands:
        - composer install --no-dev --optimize-autoloader
        - npm ci || npm install
        - php artisan key:generate --force
    build:
      commands:
        - npm run build
        - php artisan config:cache
        - php artisan route:cache
        - php artisan view:cache
  artifacts:
    baseDirectory: /
    files:
      - '**/*'
```

Click **"Next"**

#### Review and Save

Review settings and click **"Save and deploy"**

### Step 4: Configure Environment Variables

Once the app is created:

1. Go to **App settings** → **Environment variables**
2. Add the following variables:

```env
APP_NAME=Buypower
APP_ENV=production
APP_KEY=base64:your-generated-key-here
APP_DEBUG=false
APP_URL=https://main.xxxxxx.amplifyapp.com

# Backend API (Elastic Beanstalk)
BACKEND_API_URL=https://buypower-backend-prod.elasticbeanstalk.com
BACKEND_API_TIMEOUT=30

# Aurora RDS Database
DB_CONNECTION=mysql
DB_HOST=buypower-aurora-cluster.cluster-xxxxx.us-east-1.rds.amazonaws.com
DB_PORT=3306
DB_DATABASE=buypower_db
DB_USERNAME=admin
DB_PASSWORD=your-secure-password

# Session & Cache
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync

# Mail (optional - for password resets, etc.)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=your-email@gmail.com

# Sanctum
SANCTUM_STATEFUL_DOMAINS=main.xxxxxx.amplifyapp.com
```

**Important:** Get `APP_KEY` by running locally:
```bash
php artisan key:generate --show
```

3. Click **"Save"**

### Step 5: Configure Amplify for Laravel

#### Add Build Image Settings

1. Go to **App settings** → **Build settings**
2. Select **Build image**: `Amazon Linux 2023`
3. Add build environment:
   - PHP version: `8.2`
   - Node version: `18` or `20`

#### Configure Rewrites and Redirects

1. Go to **App settings** → **Rewrites and redirects**
2. Add the following rule for Laravel routing:

```
Source: </^[^.]+$|\.(?!(css|gif|ico|jpg|js|png|txt|svg|woff|woff2|ttf|map|json)$)([^.]+$)/>
Target: /index.php
Type: 200 (Rewrite)
```

This ensures all requests go through Laravel's `index.php`.

### Step 6: Configure Aurora RDS Access

Amplify needs network access to Aurora RDS.

**Option 1: Allow Amplify IPs (Simple but less secure)**

1. Go to **RDS Console** → Aurora Cluster → Security Groups
2. Edit inbound rules
3. Add rule:
   - Type: MySQL/Aurora
   - Port: 3306
   - Source: Allow from VPC CIDR or Amplify IP ranges
   - Description: "Allow Amplify Frontend"

**Option 2: Use VPC (More secure)**

1. Configure Amplify to use your VPC
2. Ensure VPC has access to Aurora RDS
3. Configure security groups accordingly

### Step 7: Deploy

After configuring environment variables:

1. Go to your Amplify app
2. The build should trigger automatically
3. Monitor the build in real-time
4. Build takes ~5-10 minutes

**Build Steps:**
- Provision
- Build (runs amplify.yml commands)
- Deploy
- Verify

### Step 8: Update Backend CORS

Once frontend is deployed, update backend to allow requests from Amplify:

```bash
# SSH into EB backend or update .env
eb setenv FRONTEND_URL="https://main.d1234567.amplifyapp.com"

# Update SANCTUM_STATEFUL_DOMAINS
eb setenv SANCTUM_STATEFUL_DOMAINS="main.d1234567.amplifyapp.com"

# Redeploy backend
cd buypower-backend-api
eb deploy
```

### Step 9: Test Frontend

1. **Get Amplify URL** from console (e.g., `https://main.d1234567.amplifyapp.com`)

2. **Test authentication:**
   - Go to login page
   - Create account or log in
   - Verify session works

3. **Test backend integration:**
   - Try uploading a CSV (should call backend API)
   - Check batch status
   - Verify transactions load

4. **Test routes:**
   - Dashboard
   - User management
   - Profile
   - Bulk operations

### Step 10: Set Up Custom Domain (Optional)

1. Go to **App settings** → **Domain management**
2. Click **"Add domain"**
3. Enter your domain (e.g., `app.yourdomain.com`)
4. Follow DNS verification steps
5. Amplify will automatically provision SSL certificate

## Monitoring & Debugging

### View Build Logs

1. Go to your Amplify app
2. Click on a build
3. View detailed logs for each phase

### View Application Logs

Amplify doesn't provide runtime logs directly. For Laravel logs:

1. **Enable Laravel logging to CloudWatch** (optional)
2. **Check errors via UI** - Laravel will show errors in debug mode
3. **Monitor via backend** - Backend logs will show API errors

### Common Build Errors

#### Composer Install Fails
```
Error: Your requirements could not be resolved
```

**Solution:** Ensure `composer.json` and `composer.lock` are committed and compatible with PHP 8.2

#### NPM Build Fails
```
Error: Cannot find module 'vite'
```

**Solution:** Ensure `package.json` and `package-lock.json` are committed

#### PHP Version Mismatch
```
Error: This package requires php ^8.2
```

**Solution:** Update build settings to use PHP 8.2

### Common Runtime Errors

#### 500 Internal Server Error
- Check `APP_KEY` is set
- Verify Aurora RDS connection
- Check file permissions

#### CORS Error
- Update backend `FRONTEND_URL`
- Verify backend CORS configuration
- Check Sanctum domains match

#### Database Connection Failed
- Verify `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD`
- Check Aurora security group allows Amplify
- Test connection from backend

## Auto-Deployment

Amplify automatically deploys on git push:

```bash
# Make changes
git add .
git commit -m "Update feature"
git push origin main

# Amplify will automatically detect and deploy
```

## Manual Redeploy

To trigger a manual build:

1. Go to Amplify Console
2. Click your app
3. Click **"Redeploy this version"**

Or via CLI:
```bash
aws amplify start-job \
  --app-id YOUR_APP_ID \
  --branch-name main \
  --job-type RELEASE
```

## Performance Optimization

### Enable Caching

1. Go to **App settings** → **Performance**
2. Enable caching for static assets
3. Set cache TTL (e.g., 1 day for assets)

### Optimize Build Time

1. **Use build cache:**
   ```yaml
   cache:
     paths:
       - vendor/**/*
       - node_modules/**/*
   ```

2. **Skip unnecessary steps:**
   - Remove unused packages
   - Optimize Composer/NPM installs

## Scaling

Amplify automatically scales based on traffic:
- No configuration needed
- Handles traffic spikes
- Global CDN distribution

## Cost Estimate

**AWS Amplify Pricing:**
- **Build**: $0.01 per build minute
- **Hosting**: $0.15 per GB served + $0.023 per GB stored
- **Typical Monthly Cost**: $5-20 for low traffic, $50-100 for medium traffic

## Security

1. ✅ **HTTPS only** - Amplify provides free SSL
2. ✅ **Environment variables** - Stored securely
3. ✅ **Branch-based deployments** - Separate prod/staging
4. ✅ **Access control** - Password protect branches
5. ✅ **CORS** - Properly configured with backend

## Troubleshooting

### Frontend Can't Reach Backend

**Check:**
1. Backend API URL is correct in env vars
2. Backend is running: `curl https://backend-api-url/health`
3. CORS is configured correctly
4. Sanctum tokens are being generated

**Test:**
```php
// Add temporary route to test backend connectivity
Route::get('/test-backend', function() {
    $api = app(\App\Services\BackendApiClient::class);
    return $api->testConnection();
});
```

### Database Connection Issues

**Check:**
1. Aurora RDS is running
2. Security groups allow Amplify
3. Database credentials are correct
4. VPC configuration (if using)

### Session Not Persisting

**Check:**
1. `SESSION_DRIVER=database`
2. Sessions table exists (run migrations)
3. `SESSION_DOMAIN` is set correctly

## Branch Deployments

Deploy different branches for staging/production:

1. **Main branch** → Production (`main.d1234.amplifyapp.com`)
2. **Develop branch** → Staging (`develop.d1234.amplifyapp.com`)
3. **Feature branches** → Preview deployments

Configure in Amplify Console → Branch settings

## Useful Commands

```bash
# Install Amplify CLI
npm install -g @aws-amplify/cli

# Configure Amplify CLI
amplify configure

# Pull Amplify environment
amplify pull --appId YOUR_APP_ID

# View app status
aws amplify list-apps

# View branches
aws amplify list-branches --app-id YOUR_APP_ID

# Trigger deployment
aws amplify start-job \
  --app-id YOUR_APP_ID \
  --branch-name main \
  --job-type RELEASE
```

## Next Steps

- ✅ Frontend deployed to Amplify
- ✅ Connected to Aurora RDS
- ✅ Integrated with backend API
- ⬜ Set up custom domain
- ⬜ Configure monitoring/alerts
- ⬜ Set up staging environment
- ⬜ Add CI/CD tests
- ⬜ Performance optimization

---

**Frontend URL:** `https://main.xxxxxxx.amplifyapp.com`
**Backend API:** `https://buypower-backend-prod.elasticbeanstalk.com`
**Database:** Aurora RDS (shared)

Your split architecture is now complete!
