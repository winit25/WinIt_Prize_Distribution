<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Performance Optimization Settings
    |--------------------------------------------------------------------------
    |
    | These settings help optimize the application performance by configuring
    | various Laravel components for better speed and efficiency.
    |
    */

    'cache' => [
        'dashboard_stats' => 120, // 2 minutes
        'api_status' => 30, // 30 seconds
        'batch_history' => 60, // 1 minute
        'recent_data' => 60, // 1 minute
    ],

    'api' => [
        'timeout' => 3, // 3 seconds
        'retry_attempts' => 2,
        'retry_delay' => 100, // milliseconds
    ],

    'frontend' => [
        'polling_interval' => 60000, // 60 seconds
        'enable_visibility_check' => true,
        'enable_abort_controller' => true,
    ],

    'database' => [
        'enable_query_logging' => false, // Disable in production
        'connection_pooling' => true,
    ],

    'optimization' => [
        'enable_route_caching' => true,
        'enable_config_caching' => true,
        'enable_view_caching' => true,
        'enable_autoloader_optimization' => true,
    ],
];
