<?php

return [

    /*
    |--------------------------------------------------------------------------
    | BuyPower API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for BuyPower API integration
    |
    */

    'api_url' => env('BUYPOWER_API_URL', 'https://idev.buypower.ng/v2'),

    'api_key' => env('BUYPOWER_API_KEY'),

    'timeout' => env('BUYPOWER_TIMEOUT', 30),

    'use_mock' => env('BUYPOWER_USE_MOCK', false),

    /*
    |--------------------------------------------------------------------------
    | Processing Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for batch processing
    |
    */

    'batch_size' => env('BUYPOWER_BATCH_SIZE', 5), // Process 5 recipients at a time for stability
    
    'delay_between_requests' => env('BUYPOWER_DELAY_MS', 2000), // 2 second delay between requests

    'max_retries' => env('BUYPOWER_MAX_RETRIES', 3),

    /*
    |--------------------------------------------------------------------------
    | Circuit Breaker Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for circuit breaker pattern
    |
    */

    'circuit_breaker' => [
        'failure_threshold' => env('BUYPOWER_CIRCUIT_FAILURE_THRESHOLD', 5),
        'recovery_timeout' => env('BUYPOWER_CIRCUIT_RECOVERY_TIMEOUT', 60),
        'half_open_max_calls' => env('BUYPOWER_CIRCUIT_HALF_OPEN_MAX_CALLS', 3),
    ],

];