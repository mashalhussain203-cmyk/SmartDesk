```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @yield('title', 'SmartDesk')
    </title>


    <style>

        :root {
            --bg: #f7f9f8;

            --panel: #ffffff;
            --panel-soft: #f8fbfa;

            --text: #14251f;
            --muted: #60716d;

            --line: #dce9e2;

            --green: #0d7458;
            --green-dark: #0e4d3a;
            --green-soft: #ddf7ee;

            --sage: #a5d7ca;

            --cream: #fffaf3;

            --orange: #fdba76;
            --orange-dark: #d98939;

            --danger: #b84d4d;
            --danger-soft: #fdecec;

            --success: #187a42;
            --success-soft: #e8f7ee;

            --warning: #9a6500;
            --warning-soft: #fff4df;

            --shadow: rgba(16, 75, 58, 0.14);

            --font:
                Inter,
                "Segoe UI",
                Arial,
                sans-serif;
        }


        /* ========================================================= */
        /* GLOBAL                                                     */
        /* ========================================================= */

        * {
            box-sizing: border-box;
        }


        html {
            scroll-behavior: smooth;
        }


        body {
            margin: 0;

            min-height: 100vh;

            display: flex;
            flex-direction: column;

            font-family: var(--font);

            color: var(--text);

            background: var(--bg);

            line-height: 1.6;
        }


        main {
            flex: 1;
        }


        a {
            color: inherit;
        }


        button,
        input,
        select,
        textarea {
            font: inherit;
        }


        img {
            max-width: 100%;
            display: block;
        }


        /* ========================================================= */
        /* CONTAINER                                                  */
        /* ========================================================= */

        .site-wrap {
            width: 100%;

            max-width: 1180px;

            margin: 0 auto;

            padding-left: 24px;
            padding-right: 24px;
        }


        /* ========================================================= */
        /* HEADER                                                     */
        /* ========================================================= */

        .site-header {
            position: sticky;

            top: 0;

            z-index: 100;

            padding: 15px 0;

            background: var(--green-dark);
            color: #ffffff;

            box-shadow:
                0 10px 30px var(--shadow);
        }


        .nav {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 24px;
        }


        /* ========================================================= */
        /* LOGO                                                       */
        /* ========================================================= */

        .logo {
            display: flex;
            align-items: center;

            gap: 12px;

            color: #ffffff;

            text-decoration: none;

            font-size: 24px;
            font-weight: 900;

            letter-spacing: -0.04em;

            flex-shrink: 0;
        }


        .logo-mark {
            width: 40px;
            height: 40px;

            border-radius: 50%;

            display: flex;
            align-items: center;
            justify-content: center;

            background: var(--sage);
            color: var(--green-dark);

            font-size: 20px;
            font-weight: 900;
        }


        /* ========================================================= */
        /* NAVIGATION                                                 */
        /* ========================================================= */

        .nav-links {
            display: flex;
            align-items: center;

            gap: 22px;
        }


        .nav-links > a {
            position: relative;

            color: #dff5ed;

            text-decoration: none;

            font-size: 14px;
            font-weight: 700;

            transition: color 0.2s ease;
        }


        .nav-links > a:hover,
        .nav-links > a.active {
            color: var(--orange);
        }


        .nav-links > a.active::after {
            content: "";

            position: absolute;

            left: 0;
            right: 0;
            bottom: -8px;

            height: 2px;

            border-radius: 2px;

            background: var(--orange);
        }


        /* ========================================================= */
        /* NAV USER                                                   */
        /* ========================================================= */

        .nav-user {
            display: flex;
            align-items: center;

            gap: 10px;

            padding-left: 8px;
        }


        .nav-account {
            display: flex;
            align-items: center;

            gap: 8px;

            text-decoration: none;

            color: #ffffff;

            font-size: 13px;
            font-weight: 800;
        }


        .nav-avatar {
            width: 32px;
            height: 32px;

            border-radius: 50%;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            background: var(--green-soft);
            color: var(--green-dark);

            font-size: 12px;
            font-weight: 900;
        }


        /* ========================================================= */
        /* BUTTONS                                                    */
        /* ========================================================= */

        .nav-button,
        .primary-btn,
        .secondary-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            text-decoration: none;

            cursor: pointer;

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease;
        }


        .nav-button {
            padding: 10px 17px;

            border: none;
            border-radius: 999px;

            background: var(--orange);
            color: var(--green-dark);

            font-size: 13px;
            font-weight: 900;
        }


        .nav-button:hover {
            background: #ffc58c;

            transform: translateY(-1px);
        }


        .nav-button.admin {
            background: var(--sage);
        }


        .nav-button.logout {
            border: 1px solid rgba(255, 255, 255, 0.25);

            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
        }


        .nav-button.logout:hover {
            background: rgba(255, 255, 255, 0.16);
        }


        .primary-btn {
            padding: 14px 24px;

            border-radius: 999px;

            background: var(--orange);
            color: var(--green-dark);

            font-weight: 900;

            box-shadow:
                0 14px 40px var(--shadow);
        }


        .primary-btn:hover {
            transform: translateY(-2px);
        }


        .secondary-btn {
            padding: 14px 24px;

            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 999px;

            background: transparent;
            color: #ffffff;

            font-weight: 700;
        }


        /* ========================================================= */
        /* HERO                                                       */
        /* ========================================================= */

        .hero {
            position: relative;

            min-height: 420px;

            display: flex;
            align-items: center;

            overflow: hidden;

            background:
                linear-gradient(
                    120deg,
                    var(--green-dark),
                    var(--green)
                );

            color: #ffffff;
        }


        .hero-inner {
            width: 100%;

            max-width: 1180px;

            margin: 0 auto;

            padding: 56px 24px;

            display: grid;

            grid-template-columns:
                1.15fr 0.85fr;

            align-items: center;

            gap: 40px;
        }


        .hero-copy {
            max-width: 620px;
        }


        .hero-kicker {
            display: inline-block;

            margin-bottom: 16px;

            padding: 7px 12px;

            border-radius: 999px;

            background: var(--sage);
            color: var(--green-dark);

            font-size: 11px;
            font-weight: 900;

            letter-spacing: 0.16em;

            text-transform: uppercase;
        }


        h1 {
            margin: 0 0 20px;

            font-size:
                clamp(48px, 4vw, 72px);

            line-height: 1;

            letter-spacing: -0.06em;
        }


        .hero-copy p {
            margin: 0 0 28px;

            color: #dceeed;

            font-size: 18px;

            line-height: 1.8;
        }


        .hero-actions {
            display: flex;
            align-items: center;

            gap: 16px;

            flex-wrap: wrap;
        }


        .hero-card {
            padding: 24px;

            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 28px;

            background: rgba(255, 255, 255, 0.08);

            backdrop-filter: blur(10px);

            box-shadow:
                0 20px 80px rgba(0, 0, 0, 0.2);
        }


        .mini-panel {
            margin-bottom: 14px;

            padding: 22px;

            border-radius: 22px;

            background: var(--cream);
            color: var(--text);

            box-shadow:
                0 8px 30px var(--shadow);
        }


        .mini-panel:last-child {
            margin-bottom: 0;
        }


        .mini-panel strong {
            display: block;

            color: var(--green-dark);

            font-size: 24px;
        }


        .mini-panel span {
            color: var(--muted);

            font-size: 13px;
            font-weight: 700;
        }


        /* ========================================================= */
        /* SECTIONS                                                   */
        /* ========================================================= */

        .section {
            padding: 52px 0;
        }


        .section-title {
            margin-bottom: 32px;

            text-align: center;
        }


        .section-title h2 {
            margin: 0 0 10px;

            font-size:
                clamp(34px, 3vw, 50px);

            letter-spacing: -0.04em;
        }


        .section-title p {
            max-width: 680px;

            margin: 0 auto;

            color: var(--muted);
        }


        /* ========================================================= */
        /* CARDS                                                      */
        /* ========================================================= */

        .feature-grid {
            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;
        }


        .feature-card {
            padding: 28px;

            border: 1px solid var(--line);
            border-radius: 24px;

            background: var(--cream);

            box-shadow:
                0 12px 30px var(--shadow);
        }


        .feature-card .icon {
            width: 46px;
            height: 46px;

            margin-bottom: 16px;

            border-radius: 50%;

            display: flex;
            align-items: center;
            justify-content: center;

            background: var(--green-soft);
            color: var(--green-dark);

            font-size: 22px;
        }


        .feature-card h3 {
            margin: 0 0 14px;

            color: var(--text);

            font-size: 22px;
        }


        .feature-card p {
            margin: 0;

            color: var(--muted);

            font-size: 14px;
        }


        /* ========================================================= */
        /* CTA                                                        */
        /* ========================================================= */

        .cta-band {
            padding: 44px 0;

            background: var(--green-dark);
            color: #ffffff;
        }


        .cta-content {
            max-width: 1000px;

            margin: 0 auto;

            text-align: center;
        }


        .cta-content h2 {
            margin: 0 0 12px;

            font-size:
                clamp(32px, 3vw, 44px);

            letter-spacing: -0.04em;
        }


        .cta-content p {
            color: #d0f7e9;
        }


        /* ========================================================= */
        /* FORM PAGES                                                 */
        /* ========================================================= */

        .form-page {
            padding: 60px 0;

            background: var(--bg);
        }


        .form-wrapper {
            max-width: 760px;

            margin: 0 auto;

            padding: 40px;

            border: 1px solid var(--line);
            border-radius: 30px;

            background: #ffffff;

            box-shadow:
                0 16px 60px var(--shadow);
        }


        .form-title {
            margin: 0 0 12px;

            color: var(--text);

            font-size:
                clamp(34px, 3vw, 44px);

            letter-spacing: -0.04em;
        }


        .form-sub {
            margin-bottom: 28px;

            color: var(--muted);

            font-size: 15px;
        }


        .reg-form label {
            display: block;

            margin-bottom: 8px;

            color: var(--muted);

            font-size: 11px;
            font-weight: 800;

            letter-spacing: 0.13em;

            text-transform: uppercase;
        }


        .reg-form input,
        .reg-form select,
        .reg-form textarea {
            width: 100%;

            margin-bottom: 17px;

            padding: 14px;

            border: 1px solid var(--line);
            border-radius: 12px;

            background: var(--bg);

            color: var(--text);

            font-size: 15px;
        }


        .reg-form input:focus,
        .reg-form select:focus,
        .reg-form textarea:focus {
            border-color: var(--green);

            outline: none;

            box-shadow:
                0 0 0 4px rgba(13, 116, 88, 0.14);
        }


        .form-row {
            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 16px;
        }


        .small-text {
            color: var(--muted);

            font-size: 12px;
        }


        /* ========================================================= */
        /* MESSAGES                                                   */
        /* ========================================================= */

        .error {
            margin-bottom: 14px;

            padding: 12px 14px;

            border: 1px solid #efcaca;
            border-radius: 12px;

            background: var(--danger-soft);
            color: var(--danger);

            font-size: 12px;
            font-weight: 700;
        }


        .success {
            margin-bottom: 18px;

            padding: 14px 16px;

            border: 1px solid #cbead7;
            border-radius: 12px;

            background: var(--success-soft);
            color: var(--success);

            font-weight: 700;
        }


        .warning {
            margin-bottom: 18px;

            padding: 14px 16px;

            border: 1px solid #f0dfb9;
            border-radius: 12px;

            background: var(--warning-soft);
            color: var(--warning);

            font-size: 13px;
        }


        /* ========================================================= */
        /* GENERIC PANELS                                             */
        /* ========================================================= */

        .panel {
            padding: 24px;

            border: 1px solid var(--line);
            border-radius: 22px;

            background: var(--panel);

            box-shadow:
                0 10px 30px var(--shadow);
        }


        /* ========================================================= */
        /* FOOTER                                                     */
        /* ========================================================= */

        .site-footer {
            margin-top: auto;

            padding: 36px 0;

            background: var(--green-dark);
            color: #eafbf6;
        }


        .footer-content {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 24px;
        }


        .footer-text {
            color: #bddbd2;

            font-size: 12px;
        }


        .footer-links {
            display: flex;
            align-items: center;

            gap: 18px;

            flex-wrap: wrap;
        }


        .footer-links a {
            color: #dff5ed;

            text-decoration: none;

            font-size: 12px;
            font-weight: 700;
        }


        .footer-links a:hover {
            color: var(--orange);
        }


        /* ========================================================= */
        /* RESPONSIVE                                                 */
        /* ========================================================= */

        @media (max-width: 1000px) {

            .nav {
                align-items: flex-start;

                flex-direction: column;
            }


            .nav-links {
                width: 100%;

                flex-wrap: wrap;
            }


            .nav-user {
                padding-left: 0;
            }

        }


        @media (max-width: 900px) {

            .hero-inner,
            .feature-grid,
            .form-row {
                grid-template-columns: 1fr;
            }


            .hero-inner {
                padding-top: 44px;
                padding-bottom: 44px;
            }


            .footer-content {
                align-items: flex-start;

                flex-direction: column;
            }

        }


        @media (max-width: 650px) {

            .site-wrap {
                padding-left: 18px;
                padding-right: 18px;
            }


            .site-header {
                position: relative;
            }


            .nav-links {
                flex-direction: column;
                align-items: stretch;

                gap: 8px;
            }


            .nav-links > a {
                padding: 8px 0;
            }


            .nav-links > a.active::after {
                display: none;
            }


            .nav-user {
                width: 100%;

                flex-direction: column;
                align-items: stretch;
            }


            .nav-account {
                padding: 8px 0;
            }


            .nav-button {
                width: 100%;
            }


            .hero-actions {
                align-items: stretch;

                flex-direction: column;
            }


            .primary-btn,
            .secondary-btn {
                width: 100%;
            }


            .form-wrapper {
                padding: 24px;

                border-radius: 20px;
            }


            h1 {
                font-size: 42px;
            }

        }

    </style>

</head>


<body>

    {{-- ========================================================= --}}
    {{-- HEADER                                                     --}}
    {{-- ========================================================= --}}

    <header class="site-header">

        <div class="site-wrap">

            <div class="nav">


                {{-- ========================================================= --}}
                {{-- LOGO                                                       --}}
                {{-- ========================================================= --}}

                <a
                    class="logo"
                    href="{{ route('home') }}"
                >

                    <span class="logo-mark">
                        S
                    </span>

                    <span>
                        SmartDesk
                    </span>

                </a>


                {{-- ========================================================= --}}
                {{-- NAVIGATION                                                 --}}
                {{-- ========================================================= --}}

                <nav class="nav-links">


                    {{-- HOME --}}

                    <a
                        class="{{ request()->routeIs('home') ? 'active' : '' }}"
                        href="{{ route('home') }}"
                    >
                        Home
                    </a>


                    {{-- CATALOG --}}

                    <a
                        class="{{ request()->routeIs('catalog') || request()->routeIs('car') ? 'active' : '' }}"
                        href="{{ route('catalog') }}"
                    >
                        Auto's
                    </a>


                    {{-- CART --}}

                    <a
                        class="{{ request()->routeIs('cart') ? 'active' : '' }}"
                        href="{{ route('cart') }}"
                    >
                        Winkelwagen

                        @php
                            $cartCount = collect(session('cart', []))->sum('qty');
                        @endphp

                        @if ($cartCount > 0)
                            ({{ $cartCount }})
                        @endif

                    </a>


                    {{-- ========================================================= --}}
                    {{-- AUTHENTICATED USER                                          --}}
                    {{-- ========================================================= --}}

                    @auth

                        <div class="nav-user">


                            {{-- ACCOUNT --}}

                            <a
                                class="nav-account"
                                href="{{ route('account') }}"
                            >

                                <span class="nav-avatar">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </span>

                                <span>
                                    {{ auth()->user()->name }}
                                </span>

                            </a>


                            {{-- ADMIN --}}

                            @if (auth()->user()->is_admin)

                                <a
                                    class="nav-button admin"
                                    href="{{ route('admin.dashboard') }}"
                                >
                                    Admin
                                </a>

                            @endif


                            {{-- LOGOUT --}}

                            <form
                                method="POST"
                                action="{{ route('logout') }}"
                                style="margin: 0;"
                            >

                                @csrf

                                <button
                                    class="nav-button logout"
                                    type="submit"
                                >
                                    Uitloggen
                                </button>

                            </form>

                        </div>


                    {{-- ========================================================= --}}
                    {{-- GUEST                                                      --}}
                    {{-- ========================================================= --}}

                    @else

                        <a
                            class="{{ request()->routeIs('login') ? 'active' : '' }}"
                            href="{{ route('login') }}"
                        >
                            Inloggen
                        </a>


                        <a
                            class="nav-button"
                            href="{{ route('register') }}"
                        >
                            Registreren
                        </a>

                    @endauth

                </nav>

            </div>

        </div>

    </header>


    {{-- ========================================================= --}}
    {{-- FLASH MESSAGES                                             --}}
    {{-- ========================================================= --}}

    @if (
        session('success') ||
        session('error') ||
        $errors->any()
    )

        <div
            class="site-wrap"
            style="
                padding-top: 20px;
            "
        >

            {{-- SUCCESS --}}

            @if (session('success'))

                <div class="success">

                    {{ session('success') }}

                </div>

            @endif


            {{-- SESSION ERROR --}}

            @if (session('error'))

                <div class="error">

                    {{ session('error') }}

                </div>

            @endif


            {{-- VALIDATION ERRORS --}}

            @if ($errors->any())

                <div class="error">

                    <strong>
                        Controleer onderstaande gegevens:
                    </strong>

                    <ul
                        style="
                            margin: 8px 0 0 18px;
                        "
                    >

                        @foreach ($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif

        </div>

    @endif


    {{-- ========================================================= --}}
    {{-- PAGE CONTENT                                               --}}
    {{-- ========================================================= --}}

    <main>

        @yield('content')

    </main>


    {{-- ========================================================= --}}
    {{-- FOOTER                                                     --}}
    {{-- ========================================================= --}}

    <footer class="site-footer">

        <div class="site-wrap">

            <div class="footer-content">


                {{-- BRAND --}}

                <div>

                    <a
                        class="logo"
                        href="{{ route('home') }}"
                    >

                        <span class="logo-mark">
                            S
                        </span>

                        <span>
                            SmartDesk
                        </span>

                    </a>

                    <div
                        class="footer-text"
                        style="
                            margin-top: 10px;
                        "
                    >
                        Jouw platform voor auto's,
                        accounts en veilige bestellingen.
                    </div>

                </div>


                {{-- LINKS --}}

                <div class="footer-links">

                    <a href="{{ route('home') }}">
                        Home
                    </a>

                    <a href="{{ route('catalog') }}">
                        Auto's
                    </a>

                    <a href="{{ route('cart') }}">
                        Winkelwagen
                    </a>


                    @auth

                        <a href="{{ route('account') }}">
                            Mijn account
                        </a>


                        @if (auth()->user()->is_admin)

                            <a href="{{ route('admin.dashboard') }}">
                                Admin
                            </a>

                        @endif


                    @else

                        <a href="{{ route('login') }}">
                            Inloggen
                        </a>

                        <a href="{{ route('register') }}">
                            Registreren
                        </a>

                    @endauth

                </div>

            </div>

        </div>

    </footer>

</body>

</html>
```
