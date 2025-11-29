#!/bin/bash

# Monolithic Structure Test Script
# Tests the monolithic service before deployment

set -e

echo "=========================================="
echo "Monolithic Structure Test Suite"
echo "=========================================="
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

PASSED=0
FAILED=0

# Test function
test_check() {
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓${NC} $1"
        ((PASSED++))
    else
        echo -e "${RED}✗${NC} $1"
        ((FAILED++))
    fi
}

# 1. PHP Syntax Check
echo "1. Checking PHP syntax..."
php -l app/Services/MonolithicService.php > /dev/null 2>&1
test_check "PHP syntax check"

# 2. Service Instantiation
echo "2. Testing service instantiation..."
php artisan tinker --execute="app(\App\Services\MonolithicService::class);" > /dev/null 2>&1
test_check "Service instantiation"

# 3. Service Registration
echo "3. Testing service registration..."
php artisan tinker --execute="
\$s1 = app(\App\Services\MonolithicService::class);
\$s2 = app(\App\Services\MonolithicService::class);
exit(\$s1 === \$s2 ? 0 : 1);
" > /dev/null 2>&1
test_check "Singleton pattern"

# 4. Method Testing
echo "4. Testing service methods..."
php artisan tinker --execute="
\$s = app(\App\Services\MonolithicService::class);
\$pass = \$s->generatePassword(12);
\$valid = \$s->validatePhoneNumber('08012345678');
\$meter = \$s->validateMeterNumber('12345678901', 'AEDC');
exit((strlen(\$pass) === 12 && \$valid && \$meter) ? 0 : 1);
" > /dev/null 2>&1
test_check "Service methods"

# 5. Route Compilation
echo "5. Testing route compilation..."
php artisan route:list > /dev/null 2>&1
test_check "Route compilation"

# 6. Config Cache
echo "6. Testing config cache..."
php artisan config:clear > /dev/null 2>&1
php artisan config:cache > /dev/null 2>&1
test_check "Config cache"

# 7. Application Boot
echo "7. Testing application boot..."
php artisan tinker --execute="app()->make('view');" > /dev/null 2>&1
test_check "Application boot"

# 8. BuyPower API Service (should still work)
echo "8. Testing BuyPower API service..."
php artisan tinker --execute="app('buypower.api');" > /dev/null 2>&1
test_check "BuyPower API service"

echo ""
echo "=========================================="
echo "Test Results"
echo "=========================================="
echo -e "${GREEN}Passed:${NC} $PASSED"
if [ $FAILED -gt 0 ]; then
    echo -e "${RED}Failed:${NC} $FAILED"
    echo ""
    echo -e "${RED}❌ Some tests failed. Please fix before deploying.${NC}"
    exit 1
else
    echo -e "${GREEN}Failed:${NC} $FAILED"
    echo ""
    echo -e "${GREEN}✅ All tests passed! Monolithic structure is ready for deployment.${NC}"
    exit 0
fi

