# Local Testing Guide - Split Architecture

This guide helps you test the split architecture locally before deploying to AWS.

## Quick Start

### Option 1: Use the Test Script (Recommended)

```bash
./test-split-architecture.sh
```

This will:
- Start backend API on port 8001
- Start frontend on port 8000
- Test both servers
- Show access URLs

**To stop:** Press `Ctrl+C`

### Option 2: Manual Setup

#### Terminal 1 - Backend API
```bash
cd /Users/hopegainlimited/Downloads/buypower-backend-api
php artisan serve --port=8001
```

#### Terminal 2 - Frontend
```bash
cd /Users/hopegainlimited/Downloads/buypower
php artisan serve --port=8000
```

## Testing Checklist

### 1. Verify Backend API is Running

Open a new terminal and test:

```bash
# Test health endpoint
curl http://localhost:8001/health

# Expected response:
# {"status":"healthy","database":"connected","timestamp":"..."}

# Test API health
curl http://localhost:8001/api/health

# Expected response:
# {"status":"ok","service":"buypower-backend-api","timestamp":"..."}
```

### 2. Verify Frontend is Running

```bash
# Test frontend
curl -I http://localhost:8000

# Should return HTTP 200
```

### 3. Test Frontend Can Reach Backend

Add this temporary test route to `routes/web.php`:

```php
Route::get('/test-backend-connection', function() {
    $api = app(\App\Services\BackendApiClient::class);
    $result = $api->testConnection();
    return response()->json($result);
});
```

Then test:
```bash
curl http://localhost:8000/test-backend-connection
```

Expected response:
```json
{
  "success": true,
  "message": "Backend API is reachable",
  "data": {
    "status": "healthy",
    "database": "connected"
  }
}
```

### 4. Test Authentication Flow

1. **Open browser:** http://localhost:8000
2. **Register/Login:** Create a test account
3. **Check session:** Verify you stay logged in
4. **Open DevTools:** Check Network tab for any errors

### 5. Test API Token Generation

After logging in, check if token is generated:

Add temporary test route:
```php
Route::get('/test-token', function() {
    if (!Auth::check()) {
        return 'Not authenticated';
    }
    
    $token = session('backend_api_token');
    return response()->json([
        'has_token' => !empty($token),
        'token_preview' => $token ? substr($token, 0, 20) . '...' : null
    ]);
})->middleware('auth');
```

Visit: http://localhost:8000/test-token (after login)

### 6. Test Backend API Calls (Manual)

You'll need to update controllers first. For now, test the API directly:

```bash
# First, get a token (after logging in via web)
# Then test an authenticated endpoint:

TOKEN="your-token-here"

curl -H "Authorization: Bearer $TOKEN" \
     http://localhost:8001/api/user

# Should return user data
```

## What to Test

### ✅ Must Test Before Deployment

1. **Authentication**
   - [ ] Can register new user
   - [ ] Can log in
   - [ ] Can log out
   - [ ] Session persists across page reloads
   - [ ] Password reset works

2. **Database Connection**
   - [ ] Both apps connect to same database
   - [ ] Can view users in dashboard
   - [ ] Can create/edit users

3. **Backend API Connection**
   - [ ] Frontend can reach backend health endpoint
   - [ ] CORS doesn't block requests
   - [ ] API tokens are generated after login

4. **Views & Assets**
   - [ ] All pages load without errors
   - [ ] CSS/JS assets load correctly
   - [ ] Navigation works
   - [ ] Forms display properly

### ⚠️ Known Limitations in Local Testing

**Controllers Not Updated Yet:**
- Bulk token upload won't work (needs BackendApiClient integration)
- Bulk airtime/DSTV won't work
- Transaction management won't work

**To fully test these:**
1. Follow `CONTROLLER_PROXY_GUIDE.md`
2. Update the controllers to use BackendApiClient
3. Restart both servers
4. Test bulk operations

## Troubleshooting

### Backend Won't Start

**Error:** `Address already in use`
```bash
# Find and kill process on port 8001
lsof -ti:8001 | xargs kill -9
```

### Frontend Won't Start

**Error:** `Address already in use`
```bash
# Find and kill process on port 8000
lsof -ti:8000 | xargs kill -9
```

### Database Connection Failed

**Check:**
1. MySQL is running: `mysql.server status`
2. Database exists: `mysql -u root -e "SHOW DATABASES;"`
3. Create if needed: `mysql -u root -e "CREATE DATABASE buypower;"`

### Backend API Not Reachable from Frontend

**Check `.env` files:**

Backend (`buypower-backend-api/.env`):
```env
APP_URL=http://localhost:8001
FRONTEND_URL=http://localhost:8000
```

Frontend (`buypower/.env`):
```env
BACKEND_API_URL=http://localhost:8001
```

### CORS Errors

**Update backend CORS config:**

`buypower-backend-api/config/cors.php`:
```php
'allowed_origins' => [
    'http://localhost:8000',
],
```

Then restart backend server.

### No API Token Generated

**Check:**
1. Sanctum migrations ran: `php artisan migrate:status`
2. User model uses HasApiTokens trait
3. Middleware is applied to routes

## Verify Architecture Split

### Check Backend (Port 8001)

```bash
# Should have NO views
ls buypower-backend-api/resources/views
# Should output: No such file or directory

# Should have API routes only
cat buypower-backend-api/routes/api.php | grep "Route::"
# Should show API routes

# Should have minimal web routes
cat buypower-backend-api/routes/web.php
# Should only have health/status routes
```

### Check Frontend (Port 8000)

```bash
# Should have all views
ls buypower/resources/views
# Should show all Blade templates

# Should have BackendApiClient
cat buypower/app/Services/BackendApiClient.php
# Should show the HTTP client service
```

## Performance Testing

### Test Response Times

```bash
# Backend API
time curl -s http://localhost:8001/api/health

# Frontend
time curl -s http://localhost:8000
```

Both should respond in < 100ms locally.

## Next Steps

Once local testing passes:

1. **Update Controllers** - Follow `CONTROLLER_PROXY_GUIDE.md`
2. **Test Integration** - Test bulk operations work
3. **Commit Changes** - Push to Git
4. **Deploy Backend** - Follow `BACKEND_DEPLOYMENT.md`
5. **Deploy Frontend** - Follow `FRONTEND_DEPLOYMENT.md`

## Clean Up After Testing

```bash
# Stop servers (if using script)
# Press Ctrl+C

# Or kill manually
lsof -ti:8000,8001 | xargs kill -9

# Remove test routes
# Delete temporary test routes from routes/web.php
```

## Test Data

For testing, you'll need:
- At least one test user account
- Sample CSV files (already in project)
- Test transaction data (create via UI)

## Monitoring During Testing

Watch both terminals for errors:
- **Backend Terminal:** Look for 500 errors, exceptions
- **Frontend Terminal:** Look for connection errors
- **Browser Console:** Look for JS errors, failed API calls
- **Browser Network Tab:** Verify API calls go to port 8001

## Success Criteria

✅ **Ready to deploy when:**
1. Both servers start without errors
2. Frontend can authenticate users
3. Frontend can reach backend API
4. No CORS errors in browser
5. Database connections work on both
6. Controllers updated to use BackendApiClient
7. Bulk operations work end-to-end

---

**Having issues?** Check the troubleshooting section or review:
- `CONTROLLER_PROXY_GUIDE.md`
- `BACKEND_DEPLOYMENT.md`
- `FRONTEND_DEPLOYMENT.md`
