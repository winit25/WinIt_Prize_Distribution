<?php

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    |
    | Most templating systems load templates from disk. Here you may specify
    | an array of paths that should be checked for your views. Of course
    | the usual Laravel view path has already been registered for you.
    |
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | This option determines where all the compiled Blade templates will be
    | stored for your application. Typically, this is within the storage
    | directory. However, as usual, you are free to change this value.
    |
    */

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        function() {
            // Resolve compiled path correctly on server
            $basePath = is_dir('/var/app/current') ? '/var/app/current' : base_path();
            $compiledPath = $basePath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'views';
            
            // Ensure directory exists
            if (!is_dir($compiledPath)) {
                @mkdir($compiledPath, 0777, true);
                @chmod($compiledPath, 0777);
            }
            
            return realpath($compiledPath) ?: $compiledPath;
        }()
    ),

];

