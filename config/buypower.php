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

    'api_url' => env('BUYPOWER_API_URL', 'https://api.buypower.ng/v2'),

    'api_key' => env('BUYPOWER_API_KEY'),

    'timeout' => env('BUYPOWER_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Processing Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for batch processing
    |
    */

    'batch_size' => env('BUYPOWER_BATCH_SIZE', 10), // Process 10 recipients at a time
    
    'delay_between_requests' => env('BUYPOWER_DELAY_MS', 1000), // Delay in milliseconds

    'max_retries' => env('BUYPOWER_MAX_RETRIES', 3),

];