# WARP.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Overview

This is a **Laravel 12 application** that integrates with the BuyPower API to send electricity tokens in bulk. The system allows users to upload CSV files containing recipient information and processes them through the BuyPower API to generate and distribute electricity tokens for Nigerian electricity distribution companies (DISCOs).

## Common Development Commands

### Development Environment Setup
```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Set up environment file
copy .env.example .env

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Start development server with all services
composer dev
```

### Development Commands
```bash
# Start Laravel development server only
php artisan serve

# Start Vite development server for frontend assets
npm run dev

# Build production assets
npm run build

# Run background queue processing
php artisan queue:listen

# View application logs
php artisan pail --timeout=0

# Run tests
composer test
# Or directly:
php artisan test

# Run a specific test file
php artisan test tests/Feature/BulkTokenTest.php

# Run code formatting (Laravel Pint)
vendor/bin/pint

# Clear application caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### BuyPower-Specific Commands
```bash
# Process a specific batch manually
php artisan buypower:process-batch {batch_id}

# Monitor batch processing with real-time logs
php artisan pail --filter="BuyPower"
```

### Database Commands
```bash
# Reset and re-run migrations
php artisan migrate:refresh

# Seed database with test data
php artisan db:seed

# Create a new migration
php artisan make:migration create_example_table

# Create a new model
php artisan make:model ExampleModel -m
```

## Code Architecture

### Application Structure

This application follows Laravel's MVC architecture with additional service layers for external API integration:

**Core Models:**
- `BatchUpload`: Manages CSV upload batches and tracks processing statistics
- `Recipient`: Individual recipients from CSV uploads with validation and processing status
- `Transaction`: Records of BuyPower API transactions with tokens and status tracking
- `User`: Standard Laravel user authentication (currently unused but available)

**Key Controllers:**
- `BulkTokenController`: Main controller handling CSV uploads, batch processing, and status monitoring

**Services:**
- `BuyPowerApiService`: Comprehensive wrapper for BuyPower API operations including order creation, token vending, and status checking

**Commands:**
- `ProcessBatchCommand`: Artisan command for processing token batches with rate limiting and retry logic

### Data Flow Architecture

1. **CSV Upload & Validation**: Users upload CSV files through web interface
2. **Batch Creation**: System validates CSV structure and creates batch records
3. **Background Processing**: Batch processing is initiated via Artisan command
4. **API Integration**: Recipients are processed through BuyPower API with rate limiting
5. **Status Tracking**: Real-time updates on processing progress and success rates
6. **Token Distribution**: Successful transactions generate downloadable token receipts

### API Integration Pattern

The `BuyPowerApiService` implements a two-step process for token generation:
1. **Create Order**: Establishes order with recipient details
2. **Vend Token**: Generates and delivers the actual electricity token

The service includes comprehensive error handling, retry logic, and response logging for debugging.

### CSV Processing Requirements

CSV files must contain these required columns:
- `name`: Recipient name
- `address`: Recipient address  
- `phone_number`: Nigerian mobile number (supports multiple formats)
- `disco`: Distribution company code (AEDC, BEDC, EKEDC, etc.)
- `meter_number`: 10-15 digit meter number
- `meter_type`: 'prepaid' or 'postpaid'
- `amount`: Token amount in Naira

Optional columns:
- `customer_name`: Alternative customer name

### Configuration

**Environment Variables:**
- `BUYPOWER_API_KEY`: BuyPower API authentication key
- `BUYPOWER_API_URL`: API endpoint (defaults to production)
- `BUYPOWER_TIMEOUT`: HTTP request timeout in seconds
- `BUYPOWER_BATCH_SIZE`: Recipients processed per batch (default: 10)
- `BUYPOWER_DELAY_MS`: Milliseconds delay between API requests
- `BUYPOWER_MAX_RETRIES`: Maximum retry attempts for failed requests

### Database Schema

**batch_uploads**: Tracks CSV upload batches with processing statistics
**recipients**: Individual recipient records with validation and status
**transactions**: BuyPower API transaction records with tokens and responses

All models include proper timestamps, relationships, and computed attributes for completion tracking.

### Rate Limiting & Error Handling

The system implements sophisticated rate limiting and error handling:
- Configurable delays between API requests to prevent rate limiting
- Automatic retry logic with exponential backoff
- Comprehensive logging for debugging failed transactions
- Status tracking at both batch and individual recipient levels

### Frontend Integration

Uses **Vite** with **TailwindCSS** for asset compilation:
- Entry points: `resources/css/app.css` and `resources/js/app.js`
- Hot reloading available in development mode
- Production builds are optimized and versioned

### Testing Strategy

PHPUnit configured for both Feature and Unit tests:
- Feature tests cover full request/response cycles
- Unit tests focus on individual service methods
- SQLite in-memory database for testing
- Separate testing environment configuration

## Development Notes

- The application uses **database queues** by default for background processing
- **SQLite** is the default database for development (configured in .env.example)
- All BuyPower API interactions are logged extensively for debugging
- CSV parsing includes robust validation for Nigerian phone numbers and DISCO codes
- Transaction records store complete API responses for audit trails
- Batch processing supports partial completion and resume functionality

## Valid DISCO Codes

When working with CSV files or testing, use these valid Nigerian electricity distribution company codes:
`AEDC`, `BEDC`, `EKEDC`, `EEDC`, `IBEDC`, `IKEDC`, `JEDC`, `KAEDCO`, `KEDCO`, `PHED`, `YEDC`

## Phone Number Formats

The system accepts Nigerian phone numbers in multiple formats:
- `234XXXXXXXXX` (with country code)
- `0XXXXXXXXX` (with leading zero)
- `XXXXXXXXX` (without prefix)

All formats are normalized to `234XXXXXXXXX` format for API calls.