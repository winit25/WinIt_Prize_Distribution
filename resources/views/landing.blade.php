<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WinIt Prize Distribution</title>
    
    <!-- Favicon - Same as logo -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/winit-logo-C73aMBts (2).svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/winit-logo-C73aMBts (2).png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        /* Cross-browser compatibility fixes */
        * {
            margin: 0;
            padding: 0;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #010133;
            min-height: 100vh;
            /* Cross-browser flexbox support */
            display: -webkit-box;
            display: -webkit-flex;
            display: -moz-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: vertical;
            -webkit-box-direction: normal;
            -webkit-flex-direction: column;
            -moz-box-orient: vertical;
            -moz-box-direction: normal;
            -ms-flex-direction: column;
            flex-direction: column;
            -webkit-box-align: center;
            -webkit-align-items: center;
            -moz-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            padding: 2rem 1rem;
            /* Font rendering fixes for Safari/Edge */
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }

        .container {
            max-width: 1200px;
            width: 100%;
        }

        .header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .logo {
            max-width: 1000px;
            height: auto;
            margin-bottom: 2rem;
            shape-rendering: crispEdges;
            /* Cross-browser image rendering */
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
            image-rendering: pixelated;
            -ms-interpolation-mode: nearest-neighbor;
            /* Cross-browser filter support */
            -webkit-filter: contrast(1.1) brightness(1.05);
            -moz-filter: contrast(1.1) brightness(1.05);
            -ms-filter: contrast(1.1) brightness(1.05);
            filter: contrast(1.1) brightness(1.05);
        }

        .header h1 {
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .header h1 .winit-i {
            font-family: Georgia, 'Times New Roman', Times, serif;
            font-style: italic;
            font-weight: 400;
        }

        .header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.125rem;
            font-weight: 400;
            font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .cards-container {
            /* Cross-browser flexbox */
            display: -webkit-box;
            display: -webkit-flex;
            display: -moz-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-pack: center;
            -webkit-justify-content: center;
            -moz-box-pack: center;
            -ms-flex-pack: center;
            justify-content: center;
            gap: 2rem;
            -webkit-flex-wrap: wrap;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .card {
            background: white;
            /* Cross-browser border-radius */
            -webkit-border-radius: 1rem;
            -moz-border-radius: 1rem;
            border-radius: 1rem;
            padding: 2.5rem 2rem;
            width: 320px;
            /* Cross-browser box-shadow */
            -webkit-box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            -moz-box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            /* Cross-browser transition */
            -webkit-transition: -webkit-transform 0.3s ease, box-shadow 0.3s ease;
            -moz-transition: -moz-transform 0.3s ease, box-shadow 0.3s ease;
            -o-transition: transform 0.3s ease, box-shadow 0.3s ease;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            /* Cross-browser flexbox */
            display: -webkit-box;
            display: -webkit-flex;
            display: -moz-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: vertical;
            -webkit-box-direction: normal;
            -webkit-flex-direction: column;
            -moz-box-orient: vertical;
            -moz-box-direction: normal;
            -ms-flex-direction: column;
            flex-direction: column;
            -webkit-box-align: center;
            -webkit-align-items: center;
            -moz-box-align: center;
            -ms-flex-align: center;
            align-items: center;
        }

        .card-suregift,
        .card-uber {
            padding: 2rem 2rem 2.5rem 2rem;
        }

        .card:hover {
            -webkit-transform: translateY(-10px);
            -moz-transform: translateY(-10px);
            -ms-transform: translateY(-10px);
            transform: translateY(-10px);
            -webkit-box-shadow: 0 30px 80px rgba(0, 0, 0, 0.4);
            -moz-box-shadow: 0 30px 80px rgba(0, 0, 0, 0.4);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.4);
        }

        .card-icon {
            width: 120px;
            height: 120px;
            /* Cross-browser flexbox */
            display: -webkit-box;
            display: -webkit-flex;
            display: -moz-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-align: center;
            -webkit-align-items: center;
            -moz-box-align: center;
            -ms-flex-align: center;
            align-items: center;
            -webkit-box-pack: center;
            -webkit-justify-content: center;
            -moz-box-pack: center;
            -ms-flex-pack: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            background: white;
            -webkit-border-radius: 1rem;
            -moz-border-radius: 1rem;
            border-radius: 1rem;
            padding: 1rem;
        }

        .card-suregift .card-icon,
        .card-uber .card-icon {
            width: 200px;
            height: 200px;
            padding: 0.5rem;
            margin-bottom: 1rem;
        }

        .card-icon img {
            width: 100%;
            height: 100%;
            /* Cross-browser object-fit */
            -o-object-fit: contain;
            object-fit: contain;
            /* Cross-browser image rendering */
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
            image-rendering: pixelated;
            -ms-interpolation-mode: nearest-neighbor;
        }

        .card h2 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: #010133;
            font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .card p {
            font-size: 1rem;
            color: #6b7280;
            text-align: center;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .card-suregift p,
        .card-uber p {
            margin-bottom: 1rem;
        }

        .card-button {
            /* Cross-browser gradient */
            background: #010133; /* Fallback */
            background: -webkit-linear-gradient(135deg, #010133 0%, #020255 100%);
            background: -moz-linear-gradient(135deg, #010133 0%, #020255 100%);
            background: -o-linear-gradient(135deg, #010133 0%, #020255 100%);
            background: linear-gradient(135deg, #010133 0%, #020255 100%);
            color: white;
            padding: 1rem 2.5rem;
            -webkit-border-radius: 0.75rem;
            -moz-border-radius: 0.75rem;
            border-radius: 0.75rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            -webkit-transition: all 0.3s ease;
            -moz-transition: all 0.3s ease;
            -o-transition: all 0.3s ease;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            -webkit-box-shadow: 0 4px 15px rgba(1, 1, 51, 0.3);
            -moz-box-shadow: 0 4px 15px rgba(1, 1, 51, 0.3);
            box-shadow: 0 4px 15px rgba(1, 1, 51, 0.3);
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            /* Safari button fixes */
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .card-button:hover {
            background: -webkit-linear-gradient(135deg, #020255 0%, #030377 100%);
            background: -moz-linear-gradient(135deg, #020255 0%, #030377 100%);
            background: -o-linear-gradient(135deg, #020255 0%, #030377 100%);
            background: linear-gradient(135deg, #020255 0%, #030377 100%);
            -webkit-box-shadow: 0 6px 20px rgba(1, 1, 51, 0.5);
            -moz-box-shadow: 0 6px 20px rgba(1, 1, 51, 0.5);
            box-shadow: 0 6px 20px rgba(1, 1, 51, 0.5);
            -webkit-transform: translateY(-2px);
            -moz-transform: translateY(-2px);
            -ms-transform: translateY(-2px);
            transform: translateY(-2px);
        }

        .card-button:active {
            -webkit-transform: translateY(0);
            -moz-transform: translateY(0);
            -ms-transform: translateY(0);
            transform: translateY(0);
            -webkit-box-shadow: 0 2px 10px rgba(1, 1, 51, 0.4);
            -moz-box-shadow: 0 2px 10px rgba(1, 1, 51, 0.4);
            box-shadow: 0 2px 10px rgba(1, 1, 51, 0.4);
        }

        .footer {
            text-align: center;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 3rem;
            font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .footer p {
            margin-bottom: 0.5rem;
            font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }

            .cards-container {
                -webkit-box-orient: vertical;
                -webkit-box-direction: normal;
                -webkit-flex-direction: column;
                -moz-box-orient: vertical;
                -moz-box-direction: normal;
                -ms-flex-direction: column;
                flex-direction: column;
                -webkit-box-align: center;
                -webkit-align-items: center;
                -moz-box-align: center;
                -ms-flex-align: center;
                align-items: center;
            }

            .card {
                width: 100%;
                max-width: 350px;
            }

            .card-suregift .card-icon,
            .card-uber .card-icon {
                width: 180px;
                height: 180px;
                padding: 0.5rem;
                margin-bottom: 1rem;
            }

            .card-suregift p,
            .card-uber p {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/winit-logo-C73aMBts (2).svg') }}" alt="WinIt Logo" class="logo">
            <h1>Welcome to W<span class="winit-i">I</span>NIT Prize Distribution</h1>
            <p>Your one-stop platform for bill payments and service purchases</p>
        </div>

        <div class="cards-container">
            <!-- BuyPower Card --><br>
            <div class="card card-buypower">
                <div class="card-icon">
                    <img src="{{ asset('images/buypower-logo.svg') }}" alt="BuyPower Logo">
                </div><br><br><br>
                <h2>BuyPower</h2>
                <p>Purchase electricity tokens instantly. Quick, secure, and hassle-free power top-ups for your home or business.</p>
                <button class="card-button" onclick="window.location.href='{{ route('login') }}'">Login</button>
            </div>

            <!-- SureGift Card -->
            <div class="card card-suregift">
                <div class="card-icon">
                    <img src="{{ asset('images/suregifts_logo (2).jpeg') }}" alt="SureGifts Logo">
                </div>
                <h2>SureGift</h2>
                <p>Send digital gift cards to your loved ones. Perfect for any occasion with instant delivery and multiple options.</p>
                <button class="card-button" onclick="window.location.href='#suregift'">Login</button>
            </div>

            <!-- Uber Card -->
            <div class="card card-uber">
                <div class="card-icon">
                    <img src="{{ asset('images/uber-logo.svg') }}" alt="Uber Logo">
                </div>
                <h2>Uber</h2>
                <p>Book your ride or order food delivery. Fast, reliable transportation and food services at your fingertips.</p>
                <button class="card-button" onclick="window.location.href='#uber'">Login</button>
            </div>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} WinIt. All rights reserved.</p>
            <p>Secure payments • Instant delivery • 24/7 support</p>
        </div>
    </div>
</body>
</html>
