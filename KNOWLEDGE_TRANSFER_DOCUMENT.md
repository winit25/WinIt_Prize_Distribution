# Knowledge Transfer Document
## BuyPower Electricity Token Distribution System

**Version:** 1.0  
**Last Updated:** December 2024  
**Application Type:** Web Application (Laravel 12)

---

## Table of Contents

1. [Application Overview](#application-overview)
2. [Technology Stack](#technology-stack)
3. [System Architecture](#system-architecture)
4. [Installation & Setup](#installation--setup)
5. [Configuration](#configuration)
6. [Database Structure](#database-structure)
7. [Key Features & Modules](#key-features--modules)
8. [User Roles & Permissions](#user-roles--permissions)
9. [API Integrations](#api-integrations)
10. [Security Features](#security-features)
11. [Key Workflows](#key-workflows)
12. [File Structure](#file-structure)
13. [Troubleshooting](#troubleshooting)
14. [Deployment](#deployment)

---

## Application Overview

### Purpose
The BuyPower Electricity Token Distribution System is a web-based application for managing bulk electricity token vending operations. It integrates with BuyPower API to vend electricity tokens to multiple recipients, handles batch processing, notifications, and provides comprehensive activity logging.

### Key Capabilities
- **Bulk Token Vending:** Upload CSV files to vend electricity tokens to multiple recipients
- **Batch Processing:** Process multiple recipients in batches with error handling
- **User Management:** Role-based access control with permissions
- **Activity Logging:** Comprehensive audit trail of all system activities
- **Notifications:** Email and SMS notifications for transactions
- **Device Binding:** Security feature that ties user sessions to specific devices
- **SharePoint Integration:** Upload CSV files directly from SharePoint
- **Search & Filtering:** Advanced search capabilities across transactions, recipients, and batches

---

## Technology Stack

### Backend
- **Framework:** Laravel 12
- **PHP Version:** 8.2+
- **Database:** MySQL
- **Queue System:** Synchronous (can be configured for async)

### Frontend
- **CSS Framework:** Bootstrap 5.3
- **JavaScript:** Vanilla JavaScript, Alpine.js
- **Build Tool:** Vite 7.0
- **Styling:** Tailwind CSS 3.1
- **Icons:** Font Awesome 6.4

### External Services
- **BuyPower API:** Electricity token vending
- **Termii SMS:** SMS notifications
- **SMTP:** Email notifications
- **Microsoft Graph API:** SharePoint integration
- **Tawk.to:** Live chat widget for customer support

---

## System Architecture

### MVC Pattern
The application follows Laravel's MVC (Model-View-Controller) architecture:

- **Models:** Located in `app/Models/`
- **Views:** Located in `resources/views/`
- **Controllers:** Located in `app/Http/Controllers/`
- **Services:** Located in `app/Services/` (Business logic layer)

### Key Components

#### Services Layer
- `BuyPowerApiService`: Handles all BuyPower API interactions
- `NotificationService`: Manages email and SMS notifications
- `ActivityLoggingService`: Logs all system activities
- `CircuitBreakerService`: Implements circuit breaker pattern for API resilience
- `SharePointService`: Handles SharePoint file downloads
- `CsvEncryptionService`: Handles encrypted CSV file processing

#### Middleware
- `CheckPermission`: Enforces role-based permissions
- `CheckDeviceFingerprint`: Validates device binding
- `ForcePasswordChange`: Forces password change on first login
- `SecurityHeaders`: Adds security headers to responses

---

## Installation & Setup

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js and npm
- MySQL 5.7+ or MariaDB 10.3+
- Web server (Apache/Nginx)

### Installation Steps

1. **Clone Repository**
   ```bash
   git clone <repository-url>
   cd buypower
   ```

2. **Install PHP Dependencies**
   ```bash
   composer install
   ```

3. **Install Node Dependencies**
   ```bash
   npm install
   ```

4. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure .env File**
   - Set database credentials
   - Set BuyPower API key: `BUYPOWER_API_KEY`
   - Set email configuration (SMTP)
   - Set Termii API key: `TERMII_API_KEY`
   - Configure SharePoint credentials (if using)

6. **Run Migrations**
   ```bash
   php artisan migrate
   ```

7. **Build Assets**
   ```bash
   npm run build
   ```

8. **Start Development Server**
   ```bash
   php artisan serve
   ```

---

## Configuration

### Environment Variables

#### Database
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=buypower
DB_USERNAME=root
DB_PASSWORD=
```

#### BuyPower API
```env
BUYPOWER_API_URL=https://idev.buypower.ng/v2
BUYPOWER_API_KEY=your_api_key_here
BUYPOWER_TIMEOUT=30
BUYPOWER_BATCH_SIZE=5
BUYPOWER_DELAY_MS=2000
BUYPOWER_MAX_RETRIES=3
```

#### Email (SMTP)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

#### SMS (Termii)
```env
TERMII_API_KEY=your_termii_api_key
TERMII_SENDER_ID=your_sender_id
```

#### SharePoint (Optional)
```env
SHAREPOINT_CLIENT_ID=your_client_id
SHAREPOINT_CLIENT_SECRET=your_client_secret
SHAREPOINT_TENANT_ID=your_tenant_id
```

#### Tawk.to Live Chat
```env
TAWK_ENABLED=true
TAWK_PROPERTY_ID=5a94119808274a85a2a3388d7b5754f1
TAWK_WIDGET_ID=55f66dbf
```

### Configuration Files

- `config/buypower.php`: BuyPower API configuration
- `config/sharepoint.php`: SharePoint integration settings
- `config/mail.php`: Email configuration
- `config/services.php`: Third-party service configurations
- `config/tawk.php`: Tawk.to live chat widget configuration

---

## Database Structure

### Core Tables

#### `users`
- User accounts and authentication
- Fields: `id`, `name`, `email`, `password`, `email_verified_at`, `must_change_password`
- Relationships: `roles`, `deviceFingerprints`, `activityLogs`

#### `roles`
- User roles (Super Admin, Admin, etc.)
- Fields: `id`, `name`, `slug`, `description`, `is_active`
- Relationships: `users`, `permissions`

#### `permissions`
- System permissions
- Fields: `id`, `name`, `slug`, `description`, `category`, `is_active`
- Relationships: `roles`

#### `batch_uploads`
- CSV batch upload records
- Fields: `id`, `user_id`, `batch_name`, `filename`, `status`, `total_recipients`, `processed_recipients`, `total_amount`
- Status values: `uploaded`, `processing`, `completed`, `failed`

#### `recipients`
- Recipients from CSV uploads
- Fields: `id`, `batch_upload_id`, `name`, `phone_number`, `meter_number`, `disco`, `amount`, `status`
- Relationships: `batchUpload`, `transaction`

#### `transactions`
- Individual token vending transactions
- Fields: `id`, `recipient_id`, `batch_upload_id`, `phone_number`, `amount`, `status`, `token`, `units`, `buypower_reference`, `order_id`, `api_response`, `error_message`
- Status values: `pending`, `processing`, `success`, `failed`

#### `activity_logs`
- System activity audit trail
- Fields: `id`, `action`, `event`, `description`, `causer_type`, `causer_id`, `subject_type`, `subject_id`, `properties`, `ip_address`, `user_agent`
- Tracks all user actions and system events

#### `device_fingerprints`
- Device binding records
- Fields: `id`, `user_id`, `fingerprint_hash`, `device_name`, `is_active`, `last_used_at`
- Used for device-lock security feature

### Key Relationships

- `User` → `Role` (Many-to-Many)
- `Role` → `Permission` (Many-to-Many)
- `BatchUpload` → `Recipient` (One-to-Many)
- `Recipient` → `Transaction` (One-to-One)
- `User` → `DeviceFingerprint` (One-to-Many)
- `User` → `ActivityLog` (One-to-Many via causer)

---

## Key Features & Modules

### 1. Bulk Token Vending

**Location:** `app/Http/Controllers/BulkTokenController.php`

**Features:**
- CSV file upload (direct or from SharePoint)
- Password-protected upload access
- Batch processing with progress tracking
- Error handling and retry logic
- Transaction status tracking

**Workflow:**
1. User uploads CSV file
2. System validates CSV format
3. Creates batch record
4. Processes recipients in batches
5. Vends tokens via BuyPower API
6. Sends notifications (email/SMS)
7. Updates transaction status

### 2. User Management

**Location:** `app/Http/Controllers/UserController.php`

**Features:**
- Create, edit, delete users
- Assign roles and permissions
- Password reset functionality
- User activation/deactivation
- Only superadmins can delete accounts

### 3. Activity Logging

**Location:** `app/Services/ActivityLoggingService.php`, `app/Http/Controllers/ActivityLogController.php`

**Features:**
- Logs all user actions
- IP address tracking
- Device information tracking
- Permission-based filtering
- Search and filter capabilities

### 4. Device Binding

**Location:** `app/Http/Middleware/CheckDeviceFingerprint.php`, `app/Models/DeviceFingerprint.php`

**Features:**
- Ties user sessions to specific devices
- Prevents unauthorized device access
- Superadmin bypass capability
- Device reset functionality for admins

### 5. Notifications

**Location:** `app/Services/NotificationService.php`, `app/Services/EmailNotificationService.php`, `app/Services/TermiiSmsService.php`

**Features:**
- Email notifications for transactions
- SMS notifications via Termii
- Batch notification settings
- Notification history

### 6. Search & Filtering

**Location:** `app/Http/Controllers/SearchController.php`

**Features:**
- Search across transactions, recipients, batches
- Filter by date range, status, disco, user
- Advanced filtering options
- Input sanitization for security

---

## User Roles & Permissions

### Roles

1. **Super Admin**
   - Full system access
   - Can delete accounts
   - Bypasses device binding
   - Manages all users and permissions

2. **Admin**
   - Manages users (except deletion)
   - Views all activity logs
   - Manages batches and transactions

3. **Operator**
   - Uploads CSV files
   - Processes batches
   - Views transactions

4. **Audit**
   - Read-only access
   - Views reports and logs
   - Cannot modify data

### Permissions

Key permissions:
- `upload-csv`: Upload CSV files
- `view-transactions`: View transactions
- `manage-users`: Manage user accounts
- `view-activity-logs`: View activity logs
- `manage-permissions`: Manage roles and permissions

**Location:** `app/Http/Middleware/CheckPermission.php`

---

## API Integrations

### BuyPower API

**Service:** `app/Services/BuyPowerApiService.php`

**Endpoints Used:**
- `POST /vend`: Create and vend electricity token
- `GET /electricity/order/{orderId}`: Get order status
- `GET /transactions`: Get transaction history (for health check)

**Features:**
- Circuit breaker pattern for resilience
- Retry logic with exponential backoff
- Comprehensive error handling
- Request/response logging (sanitized)

**Configuration:**
```php
// config/buypower.php
'api_url' => env('BUYPOWER_API_URL', 'https://idev.buypower.ng/v2'),
'api_key' => env('BUYPOWER_API_KEY'),
'timeout' => env('BUYPOWER_TIMEOUT', 30),
```

### Termii SMS API

**Service:** `app/Services/TermiiSmsService.php`

**Features:**
- Send SMS notifications
- Transaction status updates
- Error notifications

### SharePoint Integration

**Service:** `app/Services/SharePointService.php`

**Features:**
- Download CSV files from SharePoint
- Microsoft Graph API integration
- OAuth2 authentication

### Tawk.to Live Chat

**Configuration:** `config/tawk.php`

**Features:**
- Live chat widget on all pages
- Customer support integration
- Configurable via environment variables
- Can be enabled/disabled easily

**Integration:**
- Widget script added to all layout files (`sidebar.blade.php`, `app.blade.php`, `guest.blade.php`, `landing.blade.php`)
- Appears on authenticated and guest pages
- Uses property ID and widget ID from configuration
- Property ID: `5a94119808274a85a2a3388d7b5754f1`
- Widget ID: `55f66dbf`

**Configuration:**
```php
// config/tawk.php
'enabled' => env('TAWK_ENABLED', true),
'property_id' => env('TAWK_PROPERTY_ID', '5a94119808274a85a2a3388d7b5754f1'),
'widget_id' => env('TAWK_WIDGET_ID', '55f66dbf'),
```

---

## Security Features

### 1. Authentication & Authorization
- Laravel Breeze authentication
- Role-based access control (RBAC)
- Permission-based route protection
- CSRF protection on all forms

### 2. Device Binding
- Device fingerprinting
- Single device per user (except superadmin)
- Device mismatch detection
- Admin device reset capability

### 3. Password Security
- Bcrypt hashing
- Minimum 8 characters
- Forced password change on first login
- Password reset functionality

### 4. Input Validation & Sanitization
- SQL injection prevention
- XSS prevention
- Input sanitization for search queries
- Parameterized queries

### 5. Security Headers
- X-Content-Type-Options
- X-Frame-Options
- X-XSS-Protection
- Content-Security-Policy
- Strict-Transport-Security (HSTS)

### 6. Activity Logging
- IP address tracking
- User agent tracking
- Comprehensive audit trail
- Permission-based log access

### 7. Rate Limiting
- Password reset: 3 requests/minute
- Device management: 10-60 requests/minute
- Login: 6 requests/minute

---

## Key Workflows

### 1. CSV Upload & Processing

```
1. User logs in
2. Navigates to Upload CSV page
3. Enters password for verification
4. Uploads CSV file (or provides SharePoint URL)
5. System validates CSV format
6. Creates batch record
7. Parses recipients
8. User initiates batch processing
9. System processes recipients in batches
10. For each recipient:
    - Validates data
    - Calls BuyPower API
    - Creates transaction record
    - Sends notifications
    - Updates status
11. Batch completion notification
```

### 2. User Registration & First Login

```
1. Admin creates user account
2. System sets must_change_password = true
3. Temporary password sent via email
4. User logs in
5. Redirected to password change page
6. User sets new password
7. must_change_password = false
8. User activated (email_verified_at set)
9. Redirected to dashboard
```

### 3. Device Binding Flow

```
1. User logs in
2. System generates device fingerprint
3. Checks if device is registered
4. If not registered:
   - Registers new device
   - Allows access
5. If registered:
   - Validates fingerprint
   - If match: Allow access
   - If mismatch: Deny access (except superadmin)
   - Logs security alert
```

---

## File Structure

### Key Directories

```
buypower/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Application controllers
│   │   └── Middleware/         # Custom middleware
│   ├── Models/                 # Eloquent models
│   ├── Services/               # Business logic services
│   └── Providers/              # Service providers
├── config/                     # Configuration files
├── database/
│   └── migrations/             # Database migrations
├── resources/
│   └── views/                  # Blade templates
├── routes/
│   └── web.php                 # Web routes
└── public/                      # Public assets
```

### Important Files

- `routes/web.php`: All application routes
- `bootstrap/app.php`: Application bootstrap and middleware registration
- `config/buypower.php`: BuyPower API configuration
- `.env`: Environment variables (not in version control)

---

## Troubleshooting

### Common Issues

#### 1. API Key Not Working
**Symptoms:** 401 Unauthorized errors, transactions failing

**Solutions:**
- Verify `BUYPOWER_API_KEY` in `.env` file
- Check API key is valid and not expired
- Verify API key matches environment (sandbox vs production)
- Check application logs for specific error messages

#### 2. CSV Upload Failing
**Symptoms:** Upload errors, validation failures

**Solutions:**
- Verify CSV format matches expected structure
- Check file size (max 5MB)
- Verify user has `upload-csv` permission
- Check password verification is working

#### 3. Device Binding Issues
**Symptoms:** Users locked out, device mismatch errors

**Solutions:**
- Superadmin can bypass device checks
- Admin can reset user devices
- Check `device_fingerprints` table exists
- Verify middleware is registered correctly

#### 4. Email Not Sending
**Symptoms:** No email notifications

**Solutions:**
- Verify SMTP configuration in `.env`
- Check email credentials are correct
- Verify `MAIL_FROM_ADDRESS` is set
- Check application logs for email errors

#### 5. Database Connection Issues
**Symptoms:** Database errors, connection refused

**Solutions:**
- Verify database credentials in `.env`
- Check database server is running
- Verify database exists
- Check user has proper permissions

### Log Files

- **Application Logs:** `storage/logs/laravel.log`
- **Error Logs:** Check web server error logs
- **Activity Logs:** View in application UI at `/activity-logs`

---

## Deployment

### Production Checklist

1. **Environment Setup**
   - [ ] Copy `.env.example` to `.env`
   - [ ] Set `APP_ENV=production`
   - [ ] Set `APP_DEBUG=false`
   - [ ] Generate application key: `php artisan key:generate`

2. **Database**
   - [ ] Configure production database credentials
   - [ ] Run migrations: `php artisan migrate --force`
   - [ ] Seed initial data (if needed)

3. **Configuration**
   - [ ] Set production API keys
   - [ ] Configure SMTP settings
   - [ ] Set up Termii API key
   - [ ] Configure SharePoint (if using)

4. **Optimization**
   - [ ] Clear config cache: `php artisan config:clear`
   - [ ] Cache config: `php artisan config:cache`
   - [ ] Cache routes: `php artisan route:cache`
   - [ ] Cache views: `php artisan view:cache`
   - [ ] Optimize autoloader: `composer install --optimize-autoloader --no-dev`

5. **Assets**
   - [ ] Build production assets: `npm run build`
   - [ ] Verify assets are accessible

6. **Permissions**
   - [ ] Set proper file permissions
   - [ ] Ensure `storage/` and `bootstrap/cache/` are writable

7. **Security**
   - [ ] Verify `.env` file permissions (600)
   - [ ] Check security headers are enabled
   - [ ] Verify HTTPS is configured
   - [ ] Review user roles and permissions

### Server Requirements

- PHP 8.2+
- MySQL 5.7+ or MariaDB 10.3+
- Web server (Apache/Nginx)
- Composer
- Node.js and npm

### Maintenance Commands

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Check application status
php artisan about
```

---

## Support & Contact

### Key Contacts
- **Technical Support:** [Contact Information]
- **BuyPower API Support:** [Contact Information]
- **System Administrator:** [Contact Information]

### Documentation
- Laravel Documentation: https://laravel.com/docs
- BuyPower API Documentation: [API Documentation URL]

---

## Appendix

### A. CSV Format

**Required Columns:**
- `name`: Recipient name
- `phone_number`: Phone number (10-11 digits)
- `meter_number`: Meter number (10-11 digits)
- `amount`: Amount (₦100 - ₦100,000)
- `disco`: DISCO code (EKO, IKEJA, ABUJA, etc.)

**Optional Columns:**
- `customer_name`: Customer name
- `address`: Customer address
- `meter_type`: prepaid or postpaid (default: prepaid)

### B. DISCO Codes

Valid DISCO codes:
- `EKO` - Eko Electricity Distribution Company
- `IKEJA` - Ikeja Electric
- `ABUJA` - Abuja Electricity Distribution Company
- `IBADAN` - Ibadan Electricity Distribution Company
- `ENUGU` - Enugu Electricity Distribution Company
- `PH` - Port Harcourt Electricity Distribution Company
- `JOS` - Jos Electricity Distribution Company
- `KADUNA` - Kaduna Electricity Distribution Company
- `KANO` - Kano Electricity Distribution Company
- `BH` - Benin Electricity Distribution Company

### C. Transaction Statuses

- `pending`: Transaction created but not processed
- `processing`: Transaction being processed
- `success`: Token vended successfully
- `failed`: Transaction failed

### D. Batch Statuses

- `uploaded`: CSV uploaded, ready for processing
- `processing`: Batch being processed
- `completed`: All recipients processed successfully
- `failed`: Batch processing failed

---

**Document Version:** 1.0  
**Last Updated:** December 2024  
**Maintained By:** Development Team

