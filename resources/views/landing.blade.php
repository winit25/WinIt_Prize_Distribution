<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WinIt - Pay Bills & Purchase Services</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=montserrat:400,500,600,700" rel="stylesheet" />
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: #010133;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem 1rem;
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
            max-width: 200px;
            height: auto;
            margin-bottom: 2rem;
        }

        .header h1 {
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.125rem;
            font-weight: 400;
        }

        .cards-container {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .card {
            background: white;
            border-radius: 1rem;
            padding: 2.5rem 2rem;
            width: 320px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .card-suregift,
        .card-uber {
            padding: 2rem 2rem 2.5rem 2rem;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.4);
        }

        .card-icon {
            width: 120px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            background: white;
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
            object-fit: contain;
        }

        .card h2 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: #010133;
        }

        .card p {
            font-size: 1rem;
            color: #6b7280;
            text-align: center;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .card-suregift p,
        .card-uber p {
            margin-bottom: 1rem;
        }

        .card-button {
            background: linear-gradient(135deg, #010133 0%, #020255 100%);
            color: white;
            padding: 1rem 2.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.05rem;
            box-shadow: 0 4px 15px rgba(1, 1, 51, 0.3);
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-size: 0.95rem;
        }

        .card-button:hover {
            background: linear-gradient(135deg, #020255 0%, #030377 100%);
            box-shadow: 0 6px 20px rgba(1, 1, 51, 0.5);
            transform: translateY(-2px);
        }

        .card-button:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(1, 1, 51, 0.4);
        }

        .footer {
            text-align: center;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 3rem;
        }

        .footer p {
            margin-bottom: 0.5rem;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }

            .cards-container {
                flex-direction: column;
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
            <img src="{{ asset('images/winit-logo.png') }}" alt="WinIt Logo" class="logo">
            <h1>Welcome to WinIt</h1>
            <p>Your one-stop platform for bill payments and service purchases</p>
        </div>

        <div class="cards-container">
            <!-- BuyPower Card -->
            <div class="card card-buypower">
                <div class="card-icon">
                    <img src="{{ asset('images/buypower-logo.svg') }}" alt="BuyPower Logo">
                </div>
                <h2>BuyPower</h2>
                <p>Purchase electricity tokens instantly. Quick, secure, and hassle-free power top-ups for your home or business.</p>
                <button class="card-button" onclick="window.location.href='{{ route('login') }}'">Buy Electricity</button>
            </div>

            <!-- SureGift Card -->
            <div class="card card-suregift">
                <div class="card-icon">
                    <img src="{{ asset('images/suregift-logo.svg') }}" alt="SureGift Logo">
                </div>
                <h2>SureGift</h2>
                <p>Send digital gift cards to your loved ones. Perfect for any occasion with instant delivery and multiple options.</p>
                <button class="card-button" onclick="window.location.href='#suregift'">Send Gift</button>
            </div>

            <!-- Uber Card -->
            <div class="card card-uber">
                <div class="card-icon">
                    <img src="{{ asset('images/uber-logo.svg') }}" alt="Uber Logo">
                </div>
                <h2>Uber</h2>
                <p>Book your ride or order food delivery. Fast, reliable transportation and food services at your fingertips.</p>
                <button class="card-button" onclick="window.location.href='#uber'">Get Started</button>
            </div>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} WinIt. All rights reserved.</p>
            <p>Secure payments • Instant delivery • 24/7 support</p>
        </div>
    </div>
    <!--Start of Tawk.to Script-->
    <script type="text/javascript">
    var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
    (function(){
    var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
    s1.async=true;
    s1.src='https://embed.tawk.to/691b089cdde8a31959180b3a/1ja8pj9c5';
    s1.charset='UTF-8';
    s1.setAttribute('crossorigin','*');
    s0.parentNode.insertBefore(s1,s0);
    })();
    </script>
    <!--End of Tawk.to Script-->
</body>
</html>
