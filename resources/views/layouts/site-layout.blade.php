<!doctype html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SmartDesk')</title>
    <style>
        :root {
            --bg: #f7f9f8;
            --text: #14251f;
            --muted: #60716d;
            --line: #dce9e2;
            --green: #0d7458;
            --green-dark: #0e4d3a;
            --green-soft: #ddf7ee;
            --sage: #a5d7ca;
            --cream: #fffaf3;
            --orange: #fdba76;
            --shadow: rgba(16,75,58,.14);
            --danger: #b84d4d;
            --font: 'Inter', 'Segoe UI', Arial, sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: var(--font);
            color: var(--text);
            background: var(--bg);
            line-height: 1.6;
        }

        a {
            color: inherit;
        }

        .site-header {
            background: var(--green-dark);
            color: #fff;
            padding: 16px 0;
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: 0 10px 30px var(--shadow);
        }

        .site-wrap {
            max-width: 1180px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 24px;
            font-weight: 900;
            letter-spacing: -0.04em;
            color: #fff;
            text-decoration: none;
        }

        .logo-mark {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--sage);
            color: var(--green-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 900;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #dff5ed;
            font-weight: 700;
            font-size: 14px;
        }

        .nav-links a.active,
        .nav-links a:hover {
            color: var(--orange);
        }

        .nav-button {
            background: var(--orange);
            color: var(--green-dark);
            padding: 11px 18px;
            border-radius: 999px;
            font-weight: 900;
            font-size: 13px;
            text-decoration: none;
            border: none;
        }

        .hero {
            background: linear-gradient(120deg, var(--green-dark), var(--green));
            color: #fff;
            min-height: 420px;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero-inner {
            max-width: 1180px;
            margin: 0 auto;
            padding: 56px 24px;
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            align-items: center;
            gap: 40px;
        }

        .hero-copy {
            max-width: 620px;
        }

        .hero-kicker {
            display: inline-block;
            padding: 7px 12px;
            background: var(--sage);
            color: var(--green-dark);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 0.16em;
            border-radius: 99px;
            text-transform: uppercase;
            margin-bottom: 16px;
        }

        h1 {
            font-size: clamp(50px, 4vw, 72px);
            line-height: 1.0;
            letter-spacing: -0.06em;
            margin: 0 0 20px;
        }

        .hero-copy p {
            font-size: 18px;
            line-height: 1.8;
            color: #dceeed;
            margin: 0 0 28px;
        }

        .hero-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .primary-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--orange);
            color: var(--green-dark);
            border-radius: 999px;
            text-decoration: none;
            font-weight: 900;
            padding: 14px 24px;
            box-shadow: 0 14px 40px var(--shadow);
        }

        .secondary-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            color: #fff;
            border: 1px solid rgba(255,255,255,.6);
            border-radius: 999px;
            text-decoration: none;
            font-weight: 700;
            padding: 14px 24px;
        }

        .hero-card {
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.3);
            border-radius: 28px;
            padding: 24px;
            backdrop-filter: blur(10px);
            box-shadow: 0 20px 80px rgba(0,0,0,.2);
        }

        .mini-panel {
            background: var(--cream);
            color: var(--text);
            border-radius: 22px;
            padding: 22px;
            margin-bottom: 14px;
            box-shadow: 0 8px 30px var(--shadow);
        }

        .mini-panel strong {
            font-size: 24px;
            color: var(--green-dark);
            display: block;
        }

        .mini-panel span {
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }

        .section {
            padding: 44px 0;
        }

        .section-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .section-title h2 {
            font-size: clamp(36px, 3vw, 50px);
            letter-spacing: -0.04em;
            margin: 0 0 10px;
        }

        .section-title p {
            color: var(--muted);
            max-width: 680px;
            margin: 0 auto;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .feature-card {
            background: var(--cream);
            border: 1px solid var(--line);
            padding: 28px;
            border-radius: 24px;
            box-shadow: 0 12px 30px var(--shadow);
        }

        .feature-card .icon {
            width: 46px;
            height: 46px;
            background: var(--green-soft);
            color: var(--green-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 22px;
            margin-bottom: 16px;
        }

        .feature-card h3 {
            font-size: 22px;
            margin: 0 0 14px;
            color: var(--text);
        }

        .feature-card p {
            color: var(--muted);
            font-size: 14px;
            margin: 0;
        }

        .cta-band {
            background: var(--green-dark);
            color: #fff;
            padding: 40px 0;
        }

        .cta-content {
            max-width: 1000px;
            margin: 0 auto;
            text-align: center;
        }

        .cta-content h2 {
            margin: 0 0 12px;
            font-size: clamp(34px, 3vw, 44px);
            letter-spacing: -0.04em;
        }

        .cta-content p {
            color: #d0f7e9;
        }

        .form-page {
            padding: 60px 0;
            background: var(--bg);
        }

        .form-wrapper {
            max-width: 760px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 30px;
            padding: 40px;
            box-shadow: 0 16px 60px var(--shadow);
        }

        .form-title {
            font-size: clamp(34px, 3vw, 44px);
            letter-spacing: -0.04em;
            margin: 0 0 12px;
            color: var(--text);
        }

        .form-sub {
            color: var(--muted);
            font-size: 15px;
            margin-bottom: 28px;
        }

        .reg-form label {
            display: block;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.13em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .reg-form input {
            width: 100%;
            padding: 14px 14px;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: var(--bg);
            font-size: 15px;
            margin-bottom: 17px;
        }

        .reg-form input:focus {
            outline: none;
            border-color: var(--green);
            box-shadow: 0 0 0 4px rgba(13,116,88,.14);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .small-text {
            color: var(--muted);
            font-size: 12px;
        }

        .error {
            color: var(--danger);
            font-size: 12px;
            font-weight: 700;
            margin-top: -10px;
            margin-bottom: 12px;
        }

        .success {
            padding: 14px 16px;
            background: var(--green-soft);
            color: var(--green-dark);
            border-radius: 12px;
            font-weight: 700;
            margin-bottom: 18px;
        }

        .site-footer {
            background: var(--green-dark);
            color: #eafbf6;
            padding: 30px 0;
        }

        @media (max-width: 900px) {
            .hero-inner,
            .feature-grid,
            .form-row {
                grid-template-columns: 1fr;
            }

            .nav {
                flex-wrap: wrap;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="site-wrap nav">
            <a class="logo" href="{{ route('home') }}">
                <span class="logo-mark">S</span>
                <span>SmartDesk</span>
            </a>
            <nav class="nav-links">
                <a class="active" href="{{ route('home') }}">Home</a>
                <a href="#features">Features</a>
                <a href="#services">Services</a>
                <a href="#pricing">Pricing</a>
                <a href="{{ route('catalog') }}">Auto's</a>
                <a href="{{ route('cart') }}">Winkelwagen</a>
                @auth
                    <a href="{{ route('account') }}">Mijn account ({{ Auth::user()->name }})</a>
                    <form method="POST" action="{{ route('logout') }}" style="display:inline">
                        @csrf
                        <button class="nav-button" type="submit">Uitloggen</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Inloggen</a>
                    <a class="nav-button" href="{{ route('register') }}">Register</a>
                @endauth
            </nav>
        </div>
    </header>

    @yield('content')

    <footer class="site-footer">
        <div class="site-wrap">
            <div class="logo">
                <span class="logo-mark">S</span>
                <span>SmartDesk</span>
            </div>
        </div>
    </footer>
</body>
</html>
