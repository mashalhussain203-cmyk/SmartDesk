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

    <meta

        name="theme-color"

        content="#08090b"

    >

    <title>

        @yield('title', 'Mashal')

    </title>

    <style>

        :root {

            --bg: #08090b;

            --bg-soft: #0d0f12;

            --panel: #111419;

            --panel-2: #15191f;

            --panel-3: #1b2027;

            --text: #f6f4ef;

            --text-soft: #ddd8cf;

            --muted: #969ba3;

            --line: rgba(255, 255, 255, 0.10);

            --line-strong: rgba(255, 255, 255, 0.17);

            --gold: #d7a45f;

            --gold-light: #f1c983;

            --gold-dark: #9c6d34;

            --gold-soft: rgba(215, 164, 95, 0.12);

            --success: #5bd695;

            --success-soft: rgba(91, 214, 149, 0.11);

            --warning: #f2c66d;

            --warning-soft: rgba(242, 198, 109, 0.11);

            --danger: #f17b7b;

            --danger-soft: rgba(241, 123, 123, 0.11);

            --shadow:

                0 30px 90px rgba(0, 0, 0, 0.42);

            --shadow-soft:

                0 18px 45px rgba(0, 0, 0, 0.22);

            --radius-sm: 14px;

            --radius: 20px;

            --radius-lg: 30px;

            --font:

                Inter,

                ui-sans-serif,

                -apple-system,

                BlinkMacSystemFont,

                "Segoe UI",

                Arial,

                sans-serif;

        }





        /* ========================================================= */

        /* RESET / GLOBAL                                            */

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

            background:

                radial-gradient(

                    circle at 20% 10%,

                    rgba(215, 164, 95, 0.08),

                    transparent 24rem

                ),

                radial-gradient(

                    circle at 85% 20%,

                    rgba(84, 98, 117, 0.08),

                    transparent 24rem

                ),

                var(--bg);

            line-height: 1.6;

            -webkit-font-smoothing: antialiased;

            text-rendering: optimizeLegibility;

        }

        body::selection {

            background: var(--gold);

            color: #111111;

        }

        main {

            flex: 1;

            width: 100%;

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

        button {

            color: inherit;

        }

        img {

            max-width: 100%;

            display: block;

        }





        /* ========================================================= */

        /* CONTAINER                                                 */

        /* ========================================================= */

        .site-wrap {

            width: min(100% - 48px, 1240px);

            margin-inline: auto;

        }





        /* ========================================================= */

        /* HEADER                                                    */

        /* ========================================================= */

        .site-header {

            position: sticky;

            top: 0;

            z-index: 1000;

            border-bottom:

                1px solid rgba(255, 255, 255, 0.07);

            background:

                rgba(8, 9, 11, 0.78);

            backdrop-filter: blur(22px);

            -webkit-backdrop-filter: blur(22px);

            transition:

                background .25s ease,

                box-shadow .25s ease,

                border-color .25s ease;

        }

        .site-header.is-scrolled {

            background:

                rgba(8, 9, 11, 0.94);

            border-color:

                rgba(215, 164, 95, 0.16);

            box-shadow:

                0 16px 45px rgba(0, 0, 0, 0.28);

        }

        .nav {

            min-height: 78px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 28px;

        }





        /* ========================================================= */

        /* LOGO                                                      */

        /* ========================================================= */

        .logo {

            display: inline-flex;

            align-items: center;

            gap: 12px;

            flex-shrink: 0;

            color: var(--text);

            text-decoration: none;

            font-size: 22px;

            font-weight: 900;

            letter-spacing: -0.04em;

        }
        .logo-mark {
            position: relative;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            display: inline-grid;
            place-items: center;
            overflow: hidden;
            border: 1px solid rgba(215, 164, 95, 0.30);
            border-radius: 14px;
            background: #ffffff;
            box-shadow:
                0 10px 30px rgba(215, 164, 95, 0.20);
        }

        .logo-mark img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            object-position: center;
            border-radius: inherit;
        }

        .logo-mark::after {
            content: none;
            display: none;
        }

        .logo-word {

            display: inline-flex;

            flex-direction: column;

            line-height: 1;

        }

        .logo-word strong {

            font-size: 22px;

        }

        .logo-word small {

            margin-top: 6px;

            color: var(--muted);

            font-size: 8px;

            font-weight: 800;

            letter-spacing: .22em;

            text-transform: uppercase;

        }





        /* ========================================================= */

        /* NAVIGATION                                                */

        /* ========================================================= */

        .nav-links {

            display: flex;

            align-items: center;

            gap: 8px;

        }

        .nav-links > a:not(.nav-button):not(.nav-account) {

            position: relative;

            padding: 11px 13px;

            border-radius: 12px;

            color: #c9c7c2;

            text-decoration: none;

            font-size: 13px;

            font-weight: 750;

            transition:

                color .2s ease,

                background .2s ease;

        }

        .nav-links > a:not(.nav-button):not(.nav-account):hover,

        .nav-links > a.active:not(.nav-button):not(.nav-account) {

            color: #ffffff;

            background: rgba(255, 255, 255, 0.055);

        }

        .nav-links > a.active:not(.nav-button):not(.nav-account)::after {

            content: "";

            position: absolute;

            left: 14px;

            right: 14px;

            bottom: 4px;

            height: 2px;

            border-radius: 999px;

            background:

                linear-gradient(

                    90deg,

                    transparent,

                    var(--gold),

                    transparent

                );

        }

        .cart-count {

            min-width: 20px;

            height: 20px;

            margin-left: 5px;

            padding: 0 6px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            border-radius: 999px;

            background: var(--gold);

            color: #111111;

            font-size: 10px;

            font-weight: 900;

        }





        /* ========================================================= */

        /* NAV USER                                                  */

        /* ========================================================= */

        .nav-user {

            display: flex;

            align-items: center;

            gap: 8px;

            margin-left: 4px;

        }

        .nav-account {

            display: inline-flex;

            align-items: center;

            gap: 9px;

            padding: 6px 8px 6px 6px;

            border:

                1px solid rgba(255, 255, 255, 0.08);

            border-radius: 999px;

            background:

                rgba(255, 255, 255, 0.035);

            color: #ffffff;

            text-decoration: none;

            font-size: 12px;

            font-weight: 800;

            transition:

                border-color .2s ease,

                background .2s ease;

        }

        .nav-account:hover {

            border-color:

                rgba(215, 164, 95, 0.28);

            background:

                rgba(215, 164, 95, 0.06);

        }

        .nav-avatar {

            overflow: hidden;

            flex: 0 0 30px;

        }

        .nav-avatar img {

            width: 100%;

            height: 100%;

            display: block;

            object-fit: cover;

            border-radius: 50%;

        }

        .nav-account-copy {

            min-width: 0;

        }

        .nav-account-copy strong {

            display: block;

            max-width: 130px;

            overflow: hidden;

            color: #ffffff;

            font-size: 11px;

            font-weight: 900;

            text-overflow: ellipsis;

            white-space: nowrap;

        }

        .nav-account-copy small {

            display: block;

            margin-top: 1px;

            color: #7f858c;

            font-size: 7px;

            font-weight: 800;

            letter-spacing: .04em;

            text-transform: uppercase;

        }

        .nav-avatar {

            width: 30px;

            height: 30px;

            display: inline-grid;

            place-items: center;

            border-radius: 50%;

            background:

                linear-gradient(

                    145deg,

                    var(--gold-light),

                    var(--gold-dark)

                );

            color: #15120d;

            font-size: 11px;

            font-weight: 950;

        }





        /* ========================================================= */

        /* BUTTONS                                                   */

        /* ========================================================= */

        .nav-button,

        .primary-btn,

        .secondary-btn {

            position: relative;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 9px;

            border: 0;

            text-decoration: none;

            cursor: pointer;

            font-weight: 850;

            transition:

                transform .2s ease,

                box-shadow .2s ease,

                background .2s ease,

                border-color .2s ease,

                color .2s ease;

        }

        .nav-button {

            min-height: 40px;

            padding: 0 16px;

            border-radius: 999px;

            background:

                linear-gradient(

                    135deg,

                    var(--gold-light),

                    var(--gold)

                );

            color: #14100b;

            font-size: 12px;

            box-shadow:

                0 8px 24px rgba(215, 164, 95, 0.18);

        }

        .nav-button:hover {

            transform: translateY(-1px);

            box-shadow:

                0 12px 30px rgba(215, 164, 95, 0.25);

        }

        .nav-button.admin {

            background:

                rgba(255, 255, 255, 0.08);

            border:

                1px solid rgba(255, 255, 255, 0.11);

            color: #ffffff;

            box-shadow: none;

        }

        .nav-button.logout {

            border:

                1px solid rgba(255, 255, 255, 0.10);

            background:

                rgba(255, 255, 255, 0.04);

            color: #d7d5d0;

            box-shadow: none;

        }

        .nav-button.logout:hover,

        .nav-button.admin:hover {

            background:

                rgba(255, 255, 255, 0.09);

            border-color:

                rgba(215, 164, 95, 0.24);

            color: #ffffff;

        }

        .primary-btn {

            min-height: 50px;

            padding: 0 24px;

            border-radius: 999px;

            background:

                linear-gradient(

                    135deg,

                    var(--gold-light),

                    var(--gold)

                );

            color: #15110c;

            box-shadow:

                0 14px 38px rgba(215, 164, 95, 0.20);

            font-size: 13px;

        }

        .primary-btn:hover {

            transform: translateY(-2px);

            box-shadow:

                0 20px 48px rgba(215, 164, 95, 0.28);

        }

        .secondary-btn {

            min-height: 50px;

            padding: 0 24px;

            border:

                1px solid var(--line-strong);

            border-radius: 999px;

            background:

                rgba(255, 255, 255, 0.035);

            color: var(--text);

            font-size: 13px;

        }

        .secondary-btn:hover {

            transform: translateY(-2px);

            border-color:

                rgba(215, 164, 95, 0.35);

            background:

                rgba(215, 164, 95, 0.06);

        }





        /* ========================================================= */

        /* MOBILE NAV                                                */

        /* ========================================================= */

        .nav-toggle {

            width: 44px;

            height: 44px;

            display: none;

            place-items: center;

            border:

                1px solid var(--line);

            border-radius: 13px;

            background:

                rgba(255, 255, 255, 0.04);

            cursor: pointer;

        }

        .nav-toggle-lines,

        .nav-toggle-lines::before,

        .nav-toggle-lines::after {

            width: 18px;

            height: 2px;

            display: block;

            border-radius: 999px;

            background: #ffffff;

            transition:

                transform .2s ease,

                opacity .2s ease;

        }

        .nav-toggle-lines {

            position: relative;

        }

        .nav-toggle-lines::before,

        .nav-toggle-lines::after {

            content: "";

            position: absolute;

            left: 0;

        }

        .nav-toggle-lines::before {

            top: -6px;

        }

        .nav-toggle-lines::after {

            top: 6px;

        }

        .nav-toggle.is-open .nav-toggle-lines {

            background: transparent;

        }

        .nav-toggle.is-open .nav-toggle-lines::before {

            top: 0;

            transform: rotate(45deg);

        }

        .nav-toggle.is-open .nav-toggle-lines::after {

            top: 0;

            transform: rotate(-45deg);

        }





        /* ========================================================= */

        /* HERO                                                      */

        /* ========================================================= */

        .hero {

            position: relative;

            min-height: 620px;

            display: flex;

            align-items: center;

            overflow: hidden;

            isolation: isolate;

            border-bottom:

                1px solid rgba(255, 255, 255, 0.06);

            background:

                linear-gradient(

                    90deg,

                    rgba(7, 8, 10, 0.98) 0%,

                    rgba(7, 8, 10, 0.89) 45%,

                    rgba(7, 8, 10, 0.64) 72%,

                    rgba(7, 8, 10, 0.84) 100%

                ),

                linear-gradient(

                    135deg,

                    #0b0e12,

                    #111821

                );

        }

        .hero::before {

            content: "";

            position: absolute;

            inset: 0;

            z-index: -2;

            background:

                radial-gradient(

                    circle at 78% 34%,

                    rgba(215, 164, 95, 0.14),

                    transparent 22rem

                ),

                linear-gradient(

                    115deg,

                    transparent 0 54%,

                    rgba(255, 255, 255, 0.025) 54% 55%,

                    transparent 55% 100%

                );

        }

        .hero::after {

            content: "";

            position: absolute;

            right: -160px;

            top: 50%;

            width: 650px;

            height: 650px;

            z-index: -1;

            transform: translateY(-50%);

            border:

                1px solid rgba(215, 164, 95, 0.12);

            border-radius: 50%;

            box-shadow:

                0 0 0 100px rgba(255, 255, 255, 0.01),

                0 0 0 200px rgba(255, 255, 255, 0.008);

        }

        .hero-inner {

            width: min(100% - 48px, 1240px);

            margin-inline: auto;

            padding:

                clamp(80px, 9vw, 130px)

                0;

            display: grid;

            grid-template-columns:

                minmax(0, 1.15fr)

                minmax(300px, .75fr);

            align-items: center;

            gap:

                clamp(36px, 6vw, 86px);

        }

        .hero-copy {

            max-width: 760px;

            animation:

                mashalFadeUp .75s ease both;

        }

        .hero-kicker {

            display: inline-flex;

            align-items: center;

            gap: 10px;

            margin-bottom: 24px;

            color: var(--gold-light);

            font-size: 10px;

            font-weight: 900;

            letter-spacing: .22em;

            text-transform: uppercase;

        }

        .hero-kicker::before {

            content: "";

            width: 34px;

            height: 1px;

            background: var(--gold);

        }

        h1 {

            margin: 0 0 24px;

            font-size:

                clamp(52px, 6vw, 88px);

            line-height: .98;

            letter-spacing: -0.06em;

            font-weight: 900;

        }

        .hero-copy h1 span,

        .gold-text {

            color: var(--gold-light);

        }

        .hero-copy p {

            max-width: 650px;

            margin: 0 0 34px;

            color: #b8bbc0;

            font-size:

                clamp(16px, 1.6vw, 19px);

            line-height: 1.8;

        }

        .hero-actions {

            display: flex;

            align-items: center;

            gap: 12px;

            flex-wrap: wrap;

        }

        .hero-card {

            position: relative;

            padding: 18px;

            border:

                1px solid var(--line-strong);

            border-radius: 28px;

            background:

                linear-gradient(

                    145deg,

                    rgba(255, 255, 255, 0.08),

                    rgba(255, 255, 255, 0.025)

                );

            backdrop-filter: blur(18px);

            -webkit-backdrop-filter: blur(18px);

            box-shadow: var(--shadow);

            animation:

                mashalFadeUp .75s .14s ease both;

        }

        .hero-card::before {

            content: "";

            position: absolute;

            inset: 0;

            border-radius: inherit;

            background:

                linear-gradient(

                    145deg,

                    rgba(215, 164, 95, 0.08),

                    transparent 45%

                );

            pointer-events: none;

        }

        .mini-panel {

            position: relative;

            margin-bottom: 10px;

            padding: 20px;

            border:

                1px solid rgba(255, 255, 255, 0.07);

            border-radius: 18px;

            background:

                rgba(5, 6, 8, 0.58);

        }

        .mini-panel:last-child {

            margin-bottom: 0;

        }

        .mini-panel .small-text {

            display: block;

            margin-bottom: 7px;

            color: var(--gold);

            font-size: 9px;

            font-weight: 900;

            letter-spacing: .16em;

            text-transform: uppercase;

        }

        .mini-panel strong {

            display: block;

            margin-bottom: 4px;

            color: #ffffff;

            font-size: 24px;

            line-height: 1.2;

        }

        .mini-panel span:not(.small-text) {

            color: var(--muted);

            font-size: 12px;

        }





        /* ========================================================= */

        /* SECTIONS                                                  */

        /* ========================================================= */

        .section {

            position: relative;

            padding:

                clamp(72px, 8vw, 110px)

                0;

        }

        .section:nth-of-type(even) {

            background:

                linear-gradient(

                    180deg,

                    rgba(255, 255, 255, 0.012),

                    rgba(255, 255, 255, 0.024)

                );

        }

        .section-title {

            width: min(100% - 48px, 760px);

            margin:

                0 auto

                42px;

            text-align: center;

        }

        .section-title::before {

            content: "MASHAL";

            display: block;

            margin-bottom: 10px;

            color: var(--gold);

            font-size: 9px;

            font-weight: 900;

            letter-spacing: .26em;

        }

        .section-title h2 {

            margin: 0 0 14px;

            font-size:

                clamp(34px, 4vw, 54px);

            line-height: 1.05;

            letter-spacing: -0.045em;

        }

        .section-title p {

            margin: 0;

            color: var(--muted);

            font-size: 15px;

            line-height: 1.8;

        }





        /* ========================================================= */

        /* CARDS                                                     */

        /* ========================================================= */

        .feature-grid {

            display: grid;

            grid-template-columns:

                repeat(3, minmax(0, 1fr));

            gap: 18px;

        }

        .feature-card {

            position: relative;

            padding: 26px;

            overflow: hidden;

            border:

                1px solid var(--line);

            border-radius: 24px;

            background:

                linear-gradient(

                    145deg,

                    rgba(255, 255, 255, 0.045),

                    rgba(255, 255, 255, 0.018)

                );

            box-shadow:

                0 12px 35px rgba(0, 0, 0, 0.18);

            transition:

                transform .25s ease,

                border-color .25s ease,

                box-shadow .25s ease;

        }

        .feature-card::before {

            content: "";

            position: absolute;

            left: 0;

            top: 0;

            width: 100%;

            height: 2px;

            background:

                linear-gradient(

                    90deg,

                    transparent,

                    rgba(215, 164, 95, 0.75),

                    transparent

                );

            opacity: 0;

            transition: opacity .25s ease;

        }

        .feature-card:hover {

            transform: translateY(-6px);

            border-color:

                rgba(215, 164, 95, 0.25);

            box-shadow:

                0 24px 60px rgba(0, 0, 0, 0.30);

        }

        .feature-card:hover::before {

            opacity: 1;

        }

        .feature-card .icon {

            width: 48px;

            height: 48px;

            margin-bottom: 20px;

            display: grid;

            place-items: center;

            border:

                1px solid rgba(215, 164, 95, 0.20);

            border-radius: 14px;

            background:

                var(--gold-soft);

            color: var(--gold-light);

            font-size: 18px;

            font-weight: 900;

        }

        .feature-card h3 {

            margin: 0 0 12px;

            color: var(--text);

            font-size: 21px;

            letter-spacing: -0.025em;

        }

        .feature-card p {

            margin: 0;

            color: var(--muted);

            font-size: 14px;

            line-height: 1.75;

        }





        /* ========================================================= */

        /* CTA                                                       */

        /* ========================================================= */

        .cta-band {

            position: relative;

            overflow: hidden;

            padding: 78px 0;

            border-top:

                1px solid var(--line);

            border-bottom:

                1px solid var(--line);

            background:

                radial-gradient(

                    circle at 50% 0%,

                    rgba(215, 164, 95, 0.13),

                    transparent 28rem

                ),

                #0b0d10;

        }

        .cta-content {

            width: min(100% - 48px, 820px);

            margin-inline: auto;

            text-align: center;

        }

        .cta-content h2 {

            margin: 0 0 14px;

            font-size:

                clamp(32px, 4vw, 52px);

            line-height: 1.08;

            letter-spacing: -0.045em;

        }

        .cta-content p {

            margin:

                0 auto

                26px;

            color: var(--muted);

            line-height: 1.8;

        }





        /* ========================================================= */

        /* FORM PAGES                                                */

        /* ========================================================= */

        .form-page {

            min-height: 70vh;

            padding:

                clamp(52px, 7vw, 90px)

                0;

            background:

                radial-gradient(

                    circle at 15% 12%,

                    rgba(215, 164, 95, 0.07),

                    transparent 22rem

                );

        }

        .form-wrapper {

            max-width: 820px;

            margin-inline: auto;

            padding:

                clamp(26px, 5vw, 44px);

            border:

                1px solid var(--line);

            border-radius: 28px;

            background:

                linear-gradient(

                    145deg,

                    rgba(255, 255, 255, 0.045),

                    rgba(255, 255, 255, 0.018)

                );

            box-shadow: var(--shadow-soft);

            backdrop-filter: blur(18px);

            -webkit-backdrop-filter: blur(18px);

        }

        .form-title {

            margin: 0 0 12px;

            color: var(--text);

            font-size:

                clamp(34px, 4vw, 50px);

            line-height: 1.05;

            letter-spacing: -0.045em;

        }

        .form-sub {

            margin-bottom: 28px;

            color: var(--muted);

            font-size: 14px;

            line-height: 1.75;

        }

        .reg-form label {

            display: block;

            margin-bottom: 8px;

            color: #c6c6c4;

            font-size: 10px;

            font-weight: 900;

            letter-spacing: .14em;

            text-transform: uppercase;

        }

        .reg-form input,

        .reg-form select,

        .reg-form textarea {

            width: 100%;

            margin-bottom: 17px;

            padding: 14px 15px;

            border:

                1px solid var(--line);

            border-radius: 13px;

            outline: none;

            background:

                rgba(255, 255, 255, 0.035);

            color: var(--text);

            font-size: 14px;

            transition:

                border-color .2s ease,

                background .2s ease,

                box-shadow .2s ease;

        }

        .reg-form input::placeholder,

        .reg-form textarea::placeholder {

            color: #6f7379;

        }

        .reg-form select option {

            background: #101217;

            color: #ffffff;

        }

        .reg-form input:focus,

        .reg-form select:focus,

        .reg-form textarea:focus {

            border-color:

                rgba(215, 164, 95, 0.50);

            background:

                rgba(215, 164, 95, 0.035);

            box-shadow:

                0 0 0 4px rgba(215, 164, 95, 0.07);

        }

        .form-row {

            display: grid;

            grid-template-columns:

                repeat(2, minmax(0, 1fr));

            gap: 16px;

        }

        .small-text {

            color: var(--muted);

            font-size: 11px;

        }





        /* ========================================================= */

        /* MESSAGES                                                  */

        /* ========================================================= */

        .error,

        .success,

        .warning {

            margin-bottom: 18px;

            padding: 14px 16px;

            border-radius: 14px;

            font-size: 13px;

            backdrop-filter: blur(12px);

        }

        .error {

            border:

                1px solid rgba(241, 123, 123, 0.24);

            background: var(--danger-soft);

            color: #ffc1c1;

        }

        .success {

            border:

                1px solid rgba(91, 214, 149, 0.24);

            background: var(--success-soft);

            color: #aaf1ca;

        }

        .warning {

            border:

                1px solid rgba(242, 198, 109, 0.24);

            background: var(--warning-soft);

            color: #f7d998;

        }





        /* ========================================================= */

        /* GENERIC PANELS                                            */

        /* ========================================================= */

        .panel {

            padding: 24px;

            border:

                1px solid var(--line);

            border-radius: 22px;

            background:

                rgba(255, 255, 255, 0.028);

            box-shadow:

                0 10px 30px rgba(0, 0, 0, 0.15);

        }





        /* ========================================================= */

        /* FOOTER                                                    */

        /* ========================================================= */

        .site-footer {

            margin-top: auto;

            padding: 50px 0 34px;

            border-top:

                1px solid var(--line);

            background:

                linear-gradient(

                    180deg,

                    #0b0d10,

                    #07080a

                );

        }

        .footer-content {

            display: flex;

            align-items: flex-end;

            justify-content: space-between;

            gap: 32px;

        }

        .footer-text {

            max-width: 420px;

            margin-top: 13px;

            color: #797e85;

            font-size: 12px;

            line-height: 1.7;

        }

        .footer-links {

            display: flex;

            align-items: center;

            gap: 8px;

            flex-wrap: wrap;

        }

        .footer-links a {

            padding: 8px 10px;

            border-radius: 10px;

            color: #9fa3a9;

            text-decoration: none;

            font-size: 11px;

            font-weight: 750;

            transition:

                color .2s ease,

                background .2s ease;

        }

        .footer-links a:hover {

            color: var(--gold-light);

            background:

                rgba(255, 255, 255, 0.035);

        }

        .footer-bottom {

            margin-top: 34px;

            padding-top: 20px;

            border-top:

                1px solid rgba(255, 255, 255, 0.055);

            display: flex;

            justify-content: space-between;

            gap: 20px;

            color: #5f646a;

            font-size: 10px;

            font-weight: 700;

            letter-spacing: .08em;

            text-transform: uppercase;

        }





        /* ========================================================= */

        /* ANIMATIONS                                                */

        /* ========================================================= */

        @keyframes mashalFadeUp {

            from {

                opacity: 0;

                transform: translateY(24px);

            }

            to {

                opacity: 1;

                transform: translateY(0);

            }

        }

        .reveal {

            opacity: 0;

            transform: translateY(22px);

            transition:

                opacity .65s ease,

                transform .65s ease;

        }

        .reveal.is-visible {

            opacity: 1;

            transform: translateY(0);

        }





        /* ========================================================= */

        /* RESPONSIVE                                                */

        /* ========================================================= */

        @media (max-width: 1060px) {

            .nav-toggle {

                display: grid;

            }

            .nav {

                position: relative;

            }

            .nav-links {

                position: absolute;

                left: 0;

                right: 0;

                top: calc(100% + 1px);

                max-height: 0;

                overflow: hidden;

                display: flex;

                flex-direction: column;

                align-items: stretch;

                gap: 6px;

                padding: 0 18px;

                border-bottom:

                    1px solid transparent;

                background:

                    rgba(8, 9, 11, 0.98);

                backdrop-filter: blur(20px);

                opacity: 0;

                pointer-events: none;

                transition:

                    max-height .28s ease,

                    padding .28s ease,

                    opacity .2s ease,

                    border-color .2s ease;

            }

            .nav-links.is-open {

                max-height: 720px;

                padding: 16px 18px 22px;

                border-color:

                    var(--line);

                opacity: 1;

                pointer-events: auto;

            }

            .nav-links > a:not(.nav-button):not(.nav-account) {

                width: 100%;

            }

            .nav-user {

                margin-left: 0;

                flex-direction: column;

                align-items: stretch;

            }

            .nav-account,

            .nav-button {

                width: 100%;

                justify-content: center;

            }

            .hero-inner {

                grid-template-columns: 1fr;

            }

            .hero-card {

                max-width: 560px;

            }

        }

        @media (max-width: 900px) {

            .feature-grid,

            .form-row {

                grid-template-columns: 1fr;

            }

            .footer-content {

                align-items: flex-start;

                flex-direction: column;

            }

        }

        @media (max-width: 650px) {

            .site-wrap,

            .hero-inner,

            .section-title,

            .cta-content {

                width: min(100% - 32px, 1240px);

            }

            .nav {

                min-height: 68px;

            }

            .logo-word small {

                display: none;

            }

            .hero {

                min-height: 560px;

            }

            .hero-inner {

                padding:

                    70px 0

                    60px;

            }

            h1 {

                font-size:

                    clamp(44px, 14vw, 62px);

            }

            .hero-actions {

                align-items: stretch;

                flex-direction: column;

            }

            .primary-btn,

            .secondary-btn {

                width: 100%;

            }

            .feature-card {

                padding: 22px;

            }

            .form-wrapper {

                border-radius: 22px;

            }

            .footer-bottom {

                flex-direction: column;

            }

        }

        @media (prefers-reduced-motion: reduce) {

            *,

            *::before,

            *::after {

                scroll-behavior: auto !important;

                animation-duration: .01ms !important;

                animation-iteration-count: 1 !important;

                transition-duration: .01ms !important;

            }

        }

    </style>

    @stack('styles')

</head>





<body>

    {{-- ========================================================= --}}

    {{-- HEADER                                                     --}}

    {{-- ========================================================= --}}

    <header

        class="site-header"

        id="siteHeader"

    >

        <div class="site-wrap">

            <div class="nav">

                {{-- BRAND --}}

                <a

                    class="logo"

                    href="{{ route('home') }}"

                    aria-label="Mashal home"

                >

                    <span class="logo-mark">
                        <img
                            src="{{ asset('favicon.ico') }}?v=3"
                            alt="Mashal Automotive"
                            width="42"
                            height="42"
                        >
                    </span>

                    <span class="logo-word">

                        <strong>

                            Mashal

                        </strong>

                        <small>

                            Automotive

                        </small>

                    </span>

                </a>





                {{-- MOBILE BUTTON --}}

                <button

                    class="nav-toggle"

                    id="navToggle"

                    type="button"

                    aria-label="Menu openen"

                    aria-expanded="false"

                    aria-controls="mainNavigation"

                >

                    <span class="nav-toggle-lines"></span>

                </button>





                {{-- NAVIGATION --}}

                <nav

                    class="nav-links"

                    id="mainNavigation"

                >

                    <a

                        class="{{ request()->routeIs('home') ? 'active' : '' }}"

                        href="{{ route('home') }}"

                    >

                        Home

                    </a>

                    <a

                        class="{{ request()->routeIs('catalog') || request()->routeIs('car') ? 'active' : '' }}"

                        href="{{ route('catalog') }}"

                    >

                        Collectie

                    </a>

                    <a

                        class="{{ request()->routeIs('cart') || request()->routeIs('checkout') ? 'active' : '' }}"

                        href="{{ route('cart') }}"

                    >

                        Winkelwagen

                        @php

                            $cartCount = collect(session('cart', []))->sum('qty');

                        @endphp

                        @if ($cartCount > 0)

                            <span class="cart-count">

                                {{ $cartCount }}

                            </span>

                        @endif

                    </a>





                    @auth

                        <div class="nav-user">

                            <a

                                class="nav-account"

                                href="{{ route('account') }}"

                                title="Open mijn account"

                            >

                                <span class="nav-avatar">

                                    @if (auth()->user()->avatarUrl())

                                        <img

                                            src="{{ auth()->user()->avatarUrl() }}"

                                            alt="Profielfoto van {{ auth()->user()->name }}"

                                            loading="eager"

                                        >

                                    @else

                                        {{ auth()->user()->initials() }}

                                    @endif

                                </span>

                                <span class="nav-account-copy">

                                    <strong>

                                        {{ auth()->user()->name }}

                                    </strong>

                                    <small>

                                        @if (auth()->user()->hasProfilePhoto())

                                            Eigen profielfoto

                                        @elseif (auth()->user()->socialAvatar())

                                            Social avatar

                                        @else

                                            Mijn account

                                        @endif

                                    </small>

                                </span>

                            </a>





                            @if (auth()->user()->is_admin)

                                <a

                                    class="nav-button admin"

                                    href="{{ route('admin.dashboard') }}"

                                >

                                    Dashboard

                                </a>

                            @endif





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

                padding-top: 22px;

                position: relative;

                z-index: 20;

            "

        >

            @if (session('success'))

                <div class="success">

                    {{ session('success') }}

                </div>

            @endif





            @if (session('error'))

                <div class="error">

                    {{ session('error') }}

                </div>

            @endif





            @if ($errors->any())

                <div class="error">

                    <strong>

                        Controleer onderstaande gegevens:

                    </strong>

                    <ul

                        style="

                            margin: 8px 0 0 18px;

                            padding: 0;

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

                <div>

                    <a

                        class="logo"

                        href="{{ route('home') }}"

                    >

                        <span class="logo-mark">
                        <img
                            src="{{ asset('favicon.ico') }}?v=3"
                            alt="Mashal Automotive"
                            width="42"
                            height="42"
                        >
                    </span>

                        <span class="logo-word">

                            <strong>

                                Mashal

                            </strong>

                            <small>

                                Automotive

                            </small>

                        </span>

                    </a>

                    <div class="footer-text">

                        Premium automotive experience.

                        Ontdek geselecteerde auto's, beheer je account

                        en rond je aankoop veilig af vanuit één omgeving.

                    </div>

                </div>





                <div class="footer-links">

                    <a href="{{ route('home') }}">

                        Home

                    </a>

                    <a href="{{ route('catalog') }}">

                        Collectie

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

                                Dashboard

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





            <div class="footer-bottom">

                <span>

                    © {{ date('Y') }} Mashal Automotive

                </span>

                <span>

                    Crafted for premium mobility

                </span>

            </div>

        </div>

    </footer>





    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const header =

                document.getElementById('siteHeader');

            const navToggle =

                document.getElementById('navToggle');

            const navigation =

                document.getElementById('mainNavigation');





            // Header shadow after scrolling.

            const updateHeader = function () {

                if (!header) {

                    return;

                }

                header.classList.toggle(

                    'is-scrolled',

                    window.scrollY > 12

                );

            };

            updateHeader();

            window.addEventListener(

                'scroll',

                updateHeader,

                { passive: true }

            );





            // Mobile navigation.

            if (navToggle && navigation) {

                navToggle.addEventListener(

                    'click',

                    function () {

                        const open =

                            navigation.classList.toggle('is-open');

                        navToggle.classList.toggle(

                            'is-open',

                            open

                        );

                        navToggle.setAttribute(

                            'aria-expanded',

                            open ? 'true' : 'false'

                        );

                    }

                );





                navigation

                    .querySelectorAll('a')

                    .forEach(function (link) {

                        link.addEventListener(

                            'click',

                            function () {

                                navigation.classList.remove('is-open');

                                navToggle.classList.remove('is-open');

                                navToggle.setAttribute(

                                    'aria-expanded',

                                    'false'

                                );

                            }

                        );

                    });

            }





            // Subtle reveal animation.

            const revealTargets =

                document.querySelectorAll(

                    '.feature-card, .form-wrapper, .panel, .section-title'

                );

            revealTargets.forEach(function (element) {

                element.classList.add('reveal');

            });





            if ('IntersectionObserver' in window) {

                const observer =

                    new IntersectionObserver(

                        function (entries) {

                            entries.forEach(

                                function (entry) {

                                    if (entry.isIntersecting) {

                                        entry.target

                                            .classList

                                            .add('is-visible');

                                        observer.unobserve(

                                            entry.target

                                        );

                                    }

                                }

                            );

                        },

                        {

                            threshold: 0.10

                        }

                    );

                revealTargets.forEach(

                    function (element) {

                        observer.observe(element);

                    }

                );

            } else {

                revealTargets.forEach(

                    function (element) {

                        element.classList.add('is-visible');

                    }

                );

            }

        });

    </script>

    @stack('scripts')

</body>

</html>