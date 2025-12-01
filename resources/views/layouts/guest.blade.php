<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Favicon - Same as logo -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/winit-logo-C73aMBts (2).svg') }}">
        <link rel="icon" type="image/png" href="{{ asset('images/winit-logo-C73aMBts (2).png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <style>
            @media (max-width: 1440px) {
                .min-h-screen {
                    padding: 15px;
                    overflow-x: hidden;
                    overflow-y: auto;
                }
                .sm\:max-w-md {
                    max-width: 90% !important;
                    max-height: 90vh;
                    overflow-y: auto;
                }
                .sm\:px-6 {
                    padding-left: 1.25rem !important;
                    padding-right: 1.25rem !important;
                }
                .sm\:py-6 {
                    padding-top: 1.5rem !important;
                    padding-bottom: 1.5rem !important;
                }
            }
        </style>
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 px-4">
            <div class="mb-4 sm:mb-6">
                <a href="/">
                    <x-application-logo class="w-16 h-16 sm:w-20 sm:h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-4 sm:mt-6 px-4 sm:px-6 py-4 sm:py-6 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Clear any stale session data on page load
                sessionStorage.clear();
                
                // Refresh CSRF token from meta tag
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                if (csrfMeta) {
                    const csrfInputs = document.querySelectorAll('input[name="_token"]');
                    csrfInputs.forEach(function(input) {
                        input.value = csrfMeta.getAttribute('content');
                    });
                }
                
                // Handle 419 CSRF errors
                if (window.location.search.includes('419') || window.location.search.includes('csrf')) {
                    sessionStorage.clear();
                    // Auto-refresh to get fresh CSRF token
                    setTimeout(function() {
                        window.location.href = window.location.pathname;
                    }, 2000);
                }
            });
        </script>
    </body>
</html>
