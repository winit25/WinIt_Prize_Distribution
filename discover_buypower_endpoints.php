<?php

/**
 * BuyPower API Endpoint Discovery Script
 * 
 * This script tests various possible endpoint combinations to help
 * discover the correct BuyPower API endpoints.
 */

require __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiUrl = $_ENV['BUYPOWER_API_URL'] ?? 'https://idev.buypower.ng/v2';
$apiKey = $_ENV['BUYPOWER_API_KEY'] ?? '';

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║      BuyPower API Endpoint Discovery Script               ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "API Base URL: {$apiUrl}\n";
echo "Testing possible endpoints...\n\n";

// Common endpoint patterns to test
$endpointsToTest = [
    '/balance' => 'GET',
    '/account/balance' => 'GET',
    '/orders' => 'POST',
    '/order' => 'POST',
    '/electricity/orders' => 'POST',
    '/electricity/order' => 'POST',
    '/electricity/create-order' => 'POST',
    '/electricity/create' => 'POST',
    '/vend' => 'POST',
    '/electricity/vend' => 'POST',
    '/transactions' => 'GET',
    '/discos' => 'GET',
    '/disco' => 'GET',
];

// Test payload for POST requests
$testPayload = [
    'orderId' => 'TEST_DISCOVERY',
    'vendType' => 'prepaid',
    'amount' => '1000.00',
    'phone' => '08036120008',
    'meter' => '12345678901',
    'disco' => 'IKEJA',
    'vertical' => 'ELECTRICITY',
    'paymentType' => 'B2B'
];

$results = [];

foreach ($endpointsToTest as $endpoint => $method) {
    echo "Testing: {$method} {$endpoint} ... ";
    
    $ch = curl_init($apiUrl . $endpoint);
    
    $headers = [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
        'Accept: application/json',
    ];
    
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testPayload));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $responseData = json_decode($response, true);
    
    // Store result
    $results[] = [
        'endpoint' => $endpoint,
        'method' => $method,
        'http_code' => $httpCode,
        'response' => $responseData,
        'error' => $error
    ];
    
    // Color code the output
    if ($httpCode == 404) {
        echo "❌ 404 (Not Found)\n";
    } elseif ($httpCode >= 200 && $httpCode < 300) {
        echo "✅ {$httpCode} (Exists!)\n";
    } elseif ($httpCode == 401) {
        echo "🔐 401 (Unauthorized - but endpoint exists!)\n";
    } elseif ($httpCode == 400 || $httpCode == 422) {
        echo "⚠️  {$httpCode} (Validation error - endpoint exists!)\n";
    } elseif ($httpCode == 0) {
        echo "🔌 Connection Error\n";
    } else {
        echo "ℹ️  {$httpCode}\n";
    }
}

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║                  Detailed Results                          ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

foreach ($results as $result) {
    // Skip 404 errors for brevity
    if ($result['http_code'] == 404) {
        continue;
    }
    
    echo "─────────────────────────────────────────────────────────────\n";
    echo "Endpoint: {$result['method']} {$result['endpoint']}\n";
    echo "HTTP Code: {$result['http_code']}\n";
    
    if (!empty($result['error'])) {
        echo "Error: {$result['error']}\n";
    }
    
    if (!empty($result['response'])) {
        echo "Response:\n";
        echo json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
    }
    
    echo "\n";
}

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║                  Recommendations                           ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "1. Check which endpoints returned non-404 responses above\n";
echo "2. Contact BuyPower support for official API documentation\n";
echo "3. Ask for the correct endpoints for:\n";
echo "   - Creating electricity orders\n";
echo "   - Vending tokens\n";
echo "   - Checking order status\n";
echo "   - Getting account balance\n\n";

echo "BuyPower Support: support@buypower.ng\n";
echo "BuyPower Website: https://buypower.ng\n\n";
