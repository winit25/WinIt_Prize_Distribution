<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SharePoint Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Microsoft SharePoint/OneDrive integration
    |
    */

    'client_id' => env('SHAREPOINT_CLIENT_ID'),
    'client_secret' => env('SHAREPOINT_CLIENT_SECRET'),
    'tenant_id' => env('SHAREPOINT_TENANT_ID'),
    
    'enabled' => env('SHAREPOINT_ENABLED', false),
];

