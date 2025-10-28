# Production Environment Configuration

## Environment Variables (.env.production)

```bash
# Application
APP_NAME="BuyPower Integration"
APP_ENV=production
APP_KEY=base64:YOUR_32_CHARACTER_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=buypower_production
DB_USERNAME=buypower_user
DB_PASSWORD=YOUR_SECURE_DATABASE_PASSWORD

# BuyPower API Configuration
BUYPOWER_API_URL=https://api.buypower.ng/v2
BUYPOWER_API_KEY=YOUR_PRODUCTION_API_KEY_HERE
BUYPOWER_USE_MOCK=false
BUYPOWER_TIMEOUT=30
BUYPOWER_BATCH_SIZE=5
BUYPOWER_DELAY_MS=2000
BUYPOWER_MAX_RETRIES=3

# Circuit Breaker Configuration
BUYPOWER_CIRCUIT_FAILURE_THRESHOLD=5
BUYPOWER_CIRCUIT_RECOVERY_TIMEOUT=60
BUYPOWER_CIRCUIT_HALF_OPEN_MAX_CALLS=3

# Queue Configuration
QUEUE_CONNECTION=database
QUEUE_FAILED_DRIVER=database-uuids

# Cache Configuration
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=YOUR_REDIS_PASSWORD
REDIS_PORT=6379

# Session Configuration
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=YOUR_EMAIL_PASSWORD
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="BuyPower Integration"

# Termii SMS Configuration
TERMII_API_KEY=YOUR_TERMII_API_KEY
TERMII_BASE_URL=https://api.ng.termii.com
TERMII_SENDER_ID=YOUR_SENDER_ID

# Logging
LOG_CHANNEL=stack
LOG_STACK=single,slack
LOG_LEVEL=warning

# Security
BCRYPT_ROUNDS=12
SESSION_SECURE_COOKIE=true
SANCTUM_STATEFUL_DOMAINS=your-domain.com
```

## Production Deployment Checklist

### 1. Security Configuration
- [ ] Remove hardcoded API keys from config files
- [ ] Set strong APP_KEY (32 characters)
- [ ] Enable HTTPS with valid SSL certificate
- [ ] Configure secure session settings
- [ ] Set up proper CORS policies
- [ ] Enable rate limiting
- [ ] Configure firewall rules

### 2. Database Setup
- [ ] Create production database
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Seed initial data: `php artisan db:seed --force`
- [ ] Set up database backups
- [ ] Configure database monitoring

### 3. Queue Configuration
- [ ] Set up queue workers: `php artisan queue:work --daemon`
- [ ] Configure supervisor for queue management
- [ ] Set up failed job monitoring
- [ ] Configure queue retry policies

### 4. Cache Configuration
- [ ] Set up Redis server
- [ ] Configure cache drivers
- [ ] Set up cache monitoring
- [ ] Configure cache warming

### 5. Monitoring Setup
- [ ] Set up application monitoring (New Relic, DataDog, etc.)
- [ ] Configure log aggregation
- [ ] Set up health check monitoring
- [ ] Configure alerting for failures
- [ ] Set up performance monitoring

### 6. Backup Strategy
- [ ] Database backups (daily)
- [ ] File system backups
- [ ] Configuration backups
- [ ] Test restore procedures

### 7. Performance Optimization
- [ ] Enable OPcache
- [ ] Configure Redis for sessions and cache
- [ ] Set up CDN for static assets
- [ ] Optimize database queries
- [ ] Configure compression

### 8. Security Hardening
- [ ] Update PHP to latest stable version
- [ ] Configure proper file permissions
- [ ] Set up intrusion detection
- [ ] Configure security headers
- [ ] Regular security audits

## Production Commands

### Initial Setup
```bash
# Install dependencies
composer install --no-dev --optimize-autoloader

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Seed initial data
php artisan db:seed --force

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link
php artisan storage:link
```

### Queue Management
```bash
# Start queue workers
php artisan queue:work --daemon --tries=3 --timeout=300

# Monitor failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### Maintenance Commands
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize application
php artisan optimize

# Check system health
php artisan health:check
```

## Monitoring Endpoints

- Health Check: `GET /health`
- System Status: `GET /status`
- Metrics: `GET /metrics`

## Security Considerations

1. **API Key Management**: Store API keys in environment variables only
2. **Database Security**: Use strong passwords and limit access
3. **File Permissions**: Set proper permissions on application files
4. **SSL/TLS**: Use HTTPS for all communications
5. **Rate Limiting**: Implement rate limiting for API endpoints
6. **Input Validation**: Validate all user inputs
7. **Error Handling**: Don't expose sensitive information in errors
8. **Logging**: Log security events and monitor for anomalies

## Performance Considerations

1. **Database Optimization**: Use proper indexes and query optimization
2. **Caching**: Implement aggressive caching strategies
3. **Queue Processing**: Use queues for heavy operations
4. **CDN**: Use CDN for static assets
5. **Monitoring**: Monitor performance metrics continuously

## Backup and Recovery

1. **Database Backups**: Daily automated backups
2. **File Backups**: Regular file system backups
3. **Configuration Backups**: Version control for configurations
4. **Recovery Testing**: Regular restore testing
5. **Disaster Recovery**: Documented recovery procedures
