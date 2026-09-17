<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="UTF-8">

    <meta

        name="viewport"

        content="width=device-width, initial-scale=1.0"

    >

    <meta

        name="csrf-token"

        content="{{ csrf_token() }}"

    >

    <meta

        name="robots"

        content="noindex, nofollow"

    >

    <meta

        name="color-scheme"

        content="dark"

    >

    <title>

        @yield('title', 'Mashal Admin')

    </title>



    <style>

        :root {

            --bg: #08090b;

            --bg-soft: #0d1014;

            --panel: #111419;

            --panel-soft: #15191f;

            --panel-hover: #191e25;

            --text: #f6f4ef;

            --text-soft: #d8d4cc;

            --muted: #8b9199;

            --muted-2: #646a72;

            --line: rgba(255, 255, 255, 0.075);

            --line-strong: rgba(215, 164, 95, 0.24);

            --gold: #d7a45f;

            --gold-light: #f1c983;

            --gold-dark: #9c6d34;

            --success: #65d59a;

            --success-soft: rgba(101, 213, 154, 0.08);

            --warning: #e8bf73;

            --warning-soft: rgba(232, 191, 115, 0.08);

            --danger: #ef8f8f;

            --danger-soft: rgba(239, 143, 143, 0.08);

            --info: #8fb6ec;

            --info-soft: rgba(143, 182, 236, 0.08);

            --shadow:

                0 28px 80px rgba(0, 0, 0, 0.24);

            --font:

                Inter,

                "Segoe UI",

                Arial,

                Helvetica,

                sans-serif;

        }



        /* ========================================================= */

        /* GLOBAL                                                     */

        /* ========================================================= */

        * {

            box-sizing: border-box;

            -webkit-font-smoothing: antialiased;

        }

        html {

            scroll-behavior: smooth;

        }

        body {

            margin: 0;

            min-height: 100vh;

            background:

                radial-gradient(

                    circle at 82% 8%,

                    rgba(215, 164, 95, 0.075),

                    transparent 24rem

                ),

                linear-gradient(

                    180deg,

                    #090a0c 0%,

                    #08090b 100%

                );

            color: var(--text);

            font-family: var(--font);

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

        ::selection {

            background: rgba(215, 164, 95, 0.28);

            color: #ffffff;

        }



        /* ========================================================= */

        /* ADMIN SHELL                                                */

        /* ========================================================= */

        .admin-shell {

            min-height: 100vh;

            display: flex;

        }



        /* ========================================================= */

        /* SIDEBAR                                                    */

        /* ========================================================= */

        .sidebar {

            position: sticky;

            top: 0;

            width: 294px;

            height: 100vh;

            flex-shrink: 0;

            display: flex;

            flex-direction: column;

            overflow-y: auto;

            padding: 28px 20px;

            border-right: 1px solid var(--line);

            background:

                radial-gradient(

                    circle at 30% 0%,

                    rgba(215, 164, 95, 0.08),

                    transparent 17rem

                ),

                linear-gradient(

                    180deg,

                    #0d1014,

                    #0a0c0f

                );

            box-shadow:

                18px 0 55px rgba(0, 0, 0, 0.12);

        }



        /* ========================================================= */

        /* BRAND                                                      */

        /* ========================================================= */

        .brand {

            display: flex;

            align-items: center;

            gap: 12px;

            margin-bottom: 34px;

            color: #ffffff;

            text-decoration: none;

        }



        .brand-mark {

            width: 48px;

            height: 48px;

            flex-shrink: 0;

            display: grid;

            place-items: center;

            border: 1px solid rgba(215, 164, 95, 0.4);

            border-radius: 15px;

            background:

                linear-gradient(

                    145deg,

                    var(--gold-light),

                    var(--gold)

                );

            color: #15110c;

            font-size: 20px;

            font-weight: 950;

            box-shadow:

                0 10px 30px rgba(215, 164, 95, 0.16);

        }



        .brand-text strong {

            display: block;

            color: #ffffff;

            font-size: 21px;

            line-height: 1.05;

            font-weight: 950;

            letter-spacing: -0.04em;

        }



        .brand-text span {

            display: block;

            margin-top: 5px;

            color: #737a82;

            font-size: 9px;

            line-height: 1.4;

            font-weight: 800;

            letter-spacing: 0.14em;

            text-transform: uppercase;

        }



        /* ========================================================= */

        /* SIDEBAR DIVIDER                                           */

        /* ========================================================= */

        .sidebar-separator {

            height: 1px;

            margin: 4px 0 20px;

            background:

                linear-gradient(

                    90deg,

                    rgba(215, 164, 95, 0.3),

                    transparent

                );

        }



        /* ========================================================= */

        /* NAVIGATION                                                 */

        /* ========================================================= */

        .nav-title {

            margin: 22px 10px 10px;

            color: #5f646b;

            font-size: 8px;

            font-weight: 900;

            text-transform: uppercase;

            letter-spacing: 0.18em;

        }



        .sidebar nav {

            display: grid;

            gap: 5px;

        }



        .nav-link {

            position: relative;

            display: flex;

            align-items: center;

            gap: 11px;

            min-height: 46px;

            padding: 0 13px;

            border: 1px solid transparent;

            border-radius: 13px;

            color: #a9adb3;

            text-decoration: none;

            font-size: 11px;

            font-weight: 750;

            transition:

                background 0.2s ease,

                border-color 0.2s ease,

                color 0.2s ease,

                transform 0.2s ease;

        }



        .nav-link svg {

            width: 18px;

            height: 18px;

            flex-shrink: 0;

            fill: none;

            stroke: currentColor;

            stroke-width: 1.8;

            stroke-linecap: round;

            stroke-linejoin: round;

        }



        .nav-link:hover {

            transform: translateX(2px);

            border-color: rgba(215, 164, 95, 0.12);

            background:

                rgba(215, 164, 95, 0.045);

            color: #eeeae3;

        }



        .nav-link.active {

            border-color:

                rgba(215, 164, 95, 0.2);

            background:

                linear-gradient(

                    90deg,

                    rgba(215, 164, 95, 0.13),

                    rgba(215, 164, 95, 0.045)

                );

            color: var(--gold-light);

        }



        .nav-link.active::before {

            content: "";

            position: absolute;

            left: -1px;

            top: 10px;

            bottom: 10px;

            width: 2px;

            border-radius: 999px;

            background: var(--gold);

            box-shadow:

                0 0 12px rgba(215, 164, 95, 0.5);

        }



        /* ========================================================= */

        /* SIDEBAR PROFILE                                           */

        /* ========================================================= */

        .sidebar-footer {

            margin-top: auto;

            padding-top: 30px;

        }



        .profile-card {

            padding: 16px;

            border:

                1px solid var(--line);

            border-radius: 18px;

            background:

                rgba(255, 255, 255, 0.025);

        }



        .profile-head {
            display: flex;
            align-items: center;
            gap: 11px;
            min-width: 0;
        }

        .profile-avatar {
            width: 46px;
            height: 46px;
            flex: 0 0 46px;
            display: grid;
            place-items: center;
            overflow: hidden;
            border: 1px solid rgba(215, 164, 95, 0.22);
            border-radius: 14px;
            background:
                linear-gradient(
                    145deg,
                    var(--gold-light),
                    var(--gold)
                );
            color: #15110c;
            font-size: 15px;
            font-weight: 950;
            box-shadow:
                0 10px 28px rgba(215, 164, 95, 0.14);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }

        .profile-copy {
            min-width: 0;
        }

        .profile-photo-source {
            margin-top: 7px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--muted-2);
            font-size: 7px;
            font-weight: 850;
            letter-spacing: .06em;
            text-transform: uppercase;
        }

        .profile-photo-source::before {
            content: "";
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: var(--gold);
            box-shadow: 0 0 10px rgba(215,164,95,.35);
        }

        .profile-label {

            margin-bottom: 7px;

            color: var(--gold-dark);

            font-size: 8px;

            font-weight: 900;

            text-transform: uppercase;

            letter-spacing: 0.14em;

        }



        .profile-name {

            color: #f2f0eb;

            font-size: 13px;

            font-weight: 900;

            word-break: break-word;

        }



        .profile-email {

            margin-top: 5px;

            color: var(--muted);

            font-size: 9px;

            line-height: 1.55;

            word-break: break-word;

        }



        .admin-badge {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            margin-top: 11px;

            padding: 7px 9px;

            border:

                1px solid rgba(101, 213, 154, 0.14);

            border-radius: 999px;

            background:

                rgba(101, 213, 154, 0.05);

            color: #9ce7bc;

            font-size: 8px;

            font-weight: 900;

            text-transform: uppercase;

            letter-spacing: 0.06em;

        }



        .admin-badge::before {

            content: "";

            width: 6px;

            height: 6px;

            border-radius: 50%;

            background: currentColor;

            box-shadow:

                0 0 12px currentColor;

        }



        /* ========================================================= */

        /* LOGOUT                                                     */

        /* ========================================================= */

        .logout-form {

            margin-top: 13px;

        }



        .logout-button {

            width: 100%;

            min-height: 40px;

            display: flex;

            align-items: center;

            justify-content: center;

            border:

                1px solid rgba(239, 143, 143, 0.12);

            border-radius: 11px;

            background:

                rgba(239, 143, 143, 0.04);

            color: #c98e8e;

            cursor: pointer;

            font-size: 9px;

            font-weight: 850;

            transition:

                background 0.2s ease,

                border-color 0.2s ease,

                color 0.2s ease;

        }



        .logout-button:hover {

            border-color:

                rgba(239, 143, 143, 0.2);

            background:

                rgba(239, 143, 143, 0.08);

            color: #f0aaaa;

        }



        /* ========================================================= */

        /* CONTENT                                                    */

        /* ========================================================= */

        .content {

            min-width: 0;

            flex: 1;

            padding:

                24px

                clamp(22px, 3.2vw, 46px)

                54px;

        }



        /* ========================================================= */

        /* TOPBAR                                                     */

        /* ========================================================= */

        .topbar {

            position: relative;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 24px;

            margin-bottom: 22px;

            padding: 18px 0 20px;

            border-bottom:

                1px solid var(--line);

        }



        .topbar::after {

            content: "";

            position: absolute;

            left: 0;

            bottom: -1px;

            width: 110px;

            height: 1px;

            background:

                linear-gradient(

                    90deg,

                    var(--gold),

                    transparent

                );

        }



        .page-title {

            margin: 0;

            color: var(--text);

            font-size:

                clamp(28px, 3.4vw, 42px);

            font-weight: 950;

            line-height: 1.05;

            letter-spacing: -0.055em;

        }



        .page-subtitle {

            display: block;

            max-width: 640px;

            margin-top: 7px;

            color: var(--muted);

            font-size: 10px;

            line-height: 1.7;

        }



        .topbar-profile {
            min-height: 42px;
            padding: 5px 10px 5px 5px;
            display: inline-flex;
            align-items: center;
            gap: 9px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: rgba(255,255,255,.025);
            color: var(--text-soft);
            text-decoration: none;
            transition:
                transform .2s ease,
                border-color .2s ease,
                background .2s ease;
        }

        .topbar-profile:hover {
            transform: translateY(-1px);
            border-color: var(--line-strong);
            background: rgba(215,164,95,.055);
        }

        .topbar-profile-avatar {
            width: 32px;
            height: 32px;
            flex: 0 0 32px;
            display: grid;
            place-items: center;
            overflow: hidden;
            border-radius: 50%;
            background:
                linear-gradient(
                    145deg,
                    var(--gold-light),
                    var(--gold)
                );
            color: #15110c;
            font-size: 10px;
            font-weight: 950;
        }

        .topbar-profile-avatar img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }

        .topbar-profile-copy {
            min-width: 0;
        }

        .topbar-profile-copy strong {
            display: block;
            max-width: 150px;
            overflow: hidden;
            color: #f2f0eb;
            font-size: 9px;
            font-weight: 900;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .topbar-profile-copy span {
            display: block;
            margin-top: 2px;
            color: var(--muted-2);
            font-size: 7px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .topbar-actions {

            display: flex;

            align-items: center;

            gap: 9px;

            flex-wrap: wrap;

        }



        /* ========================================================= */

        /* BUTTONS                                                    */

        /* ========================================================= */

        .button {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            min-height: 42px;

            padding: 0 15px;

            border:

                1px solid transparent;

            border-radius: 999px;

            background:

                linear-gradient(

                    135deg,

                    var(--gold-light),

                    var(--gold)

                );

            color: #17110b;

            text-decoration: none;

            font-size: 9px;

            font-weight: 900;

            cursor: pointer;

            box-shadow:

                0 10px 24px rgba(215, 164, 95, 0.14);

            transition:

                transform 0.2s ease,

                box-shadow 0.2s ease,

                background 0.2s ease,

                border-color 0.2s ease;

        }



        .button:hover {

            transform: translateY(-2px);

            box-shadow:

                0 16px 32px rgba(215, 164, 95, 0.2);

        }



        .button.secondary {

            border-color: var(--line);

            background:

                rgba(255, 255, 255, 0.025);

            color: #d8d4cc;

            box-shadow: none;

        }



        .button.secondary:hover {

            border-color:

                var(--line-strong);

            background:

                rgba(215, 164, 95, 0.055);

            color: var(--gold-light);

        }



        .button.danger {

            border-color:

                rgba(239, 143, 143, 0.15);

            background:

                rgba(239, 143, 143, 0.07);

            color: #efaaaa;

            box-shadow: none;

        }



        .button.danger:hover {

            background:

                rgba(239, 143, 143, 0.11);

        }



        /* ========================================================= */

        /* MESSAGES                                                   */

        /* ========================================================= */

        .alert,

        .success,

        .error,

        .warning {

            position: relative;

            margin-bottom: 18px;

            padding: 15px 17px;

            border-radius: 14px;

            font-size: 11px;

            line-height: 1.7;

        }



        .alert,

        .success {

            border:

                1px solid rgba(101, 213, 154, 0.18);

            background:

                var(--success-soft);

            color: #a8e8c2;

        }



        .error {

            border:

                1px solid rgba(239, 143, 143, 0.18);

            background:

                var(--danger-soft);

            color: #efaaaa;

        }



        .warning {

            border:

                1px solid rgba(232, 191, 115, 0.18);

            background:

                var(--warning-soft);

            color: #e8c887;

        }



        /* ========================================================= */

        /* STATS                                                      */

        /* ========================================================= */

        .stats-grid {

            display: grid;

            grid-template-columns:

                repeat(4, minmax(160px, 1fr));

            gap: 14px;

            margin: 18px 0;

        }



        .stat-card {

            position: relative;

            overflow: hidden;

            padding: 20px;

            border:

                1px solid var(--line);

            border-radius: 20px;

            background:

                linear-gradient(

                    145deg,

                    var(--panel),

                    #0e1115

                );

            box-shadow:

                0 16px 46px rgba(0, 0, 0, 0.12);

            transition:

                transform 0.2s ease,

                border-color 0.2s ease;

        }



        .stat-card:hover {

            transform: translateY(-3px);

            border-color:

                var(--line-strong);

        }



        .stat-card::after {

            content: "";

            position: absolute;

            right: -35px;

            bottom: -45px;

            width: 130px;

            height: 130px;

            border-radius: 50%;

            background:

                radial-gradient(

                    circle,

                    rgba(215, 164, 95, 0.08),

                    transparent 70%

                );

            pointer-events: none;

        }



        .stat-label {

            color: var(--muted);

            font-size: 8px;

            font-weight: 900;

            text-transform: uppercase;

            letter-spacing: 0.14em;

        }



        .stat-num {

            margin: 15px 0 8px;

            color: #ffffff;

            font-size: 36px;

            line-height: 1;

            font-weight: 950;

            letter-spacing: -0.05em;

        }



        .stat-foot {

            color: var(--muted-2);

            font-size: 9px;

            line-height: 1.55;

        }



        /* ========================================================= */

        /* PANELS                                                     */

        /* ========================================================= */

        .main-panel {

            margin-top: 18px;

            padding: 24px;

            border:

                1px solid var(--line);

            border-radius: 24px;

            background:

                linear-gradient(

                    145deg,

                    rgba(17, 20, 25, 0.98),

                    rgba(13, 16, 20, 0.98)

                );

            box-shadow:

                0 20px 56px rgba(0, 0, 0, 0.14);

        }



        .section-heading {

            display: flex;

            align-items: flex-end;

            justify-content: space-between;

            gap: 18px;

            margin-bottom: 20px;

        }



        .section-heading h2 {

            margin: 0;

            color: var(--text);

            font-size: 22px;

            font-weight: 950;

            letter-spacing: -0.035em;

        }



        .section-heading span {

            display: block;

            margin-top: 6px;

            color: var(--muted);

            font-size: 10px;

            line-height: 1.6;

        }



        /* ========================================================= */

        /* TABLES                                                     */

        /* ========================================================= */

        .table-wrap {

            width: 100%;

            overflow-x: auto;

            border:

                1px solid var(--line);

            border-radius: 16px;

            background:

                rgba(0, 0, 0, 0.09);

        }



        table {

            width: 100%;

            border-collapse: collapse;

        }



        th {

            padding: 13px 12px;

            border-bottom:

                1px solid var(--line);

            background:

                rgba(255, 255, 255, 0.02);

            color: #70767e;

            text-align: left;

            font-size: 8px;

            font-weight: 900;

            text-transform: uppercase;

            letter-spacing: 0.12em;

            white-space: nowrap;

        }



        td {

            padding: 14px 12px;

            border-bottom:

                1px solid rgba(255, 255, 255, 0.045);

            color: var(--text-soft);

            font-size: 10px;

            vertical-align: middle;

        }



        tbody tr:last-child td {

            border-bottom: none;

        }



        tbody tr:hover {

            background:

                rgba(215, 164, 95, 0.025);

        }



        /* ========================================================= */

        /* USER                                                       */

        /* ========================================================= */

        .user-name {

            display: flex;

            align-items: center;

            gap: 10px;

            font-weight: 700;

        }



        .avatar {

            width: 38px;

            height: 38px;

            flex-shrink: 0;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            border:

                1px solid rgba(215, 164, 95, 0.16);

            border-radius: 12px;

            background:

                rgba(215, 164, 95, 0.07);

            color: var(--gold-light);

            font-weight: 950;

        }



        /* ========================================================= */

        /* BADGES                                                     */

        /* ========================================================= */

        .badge {

            display: inline-flex;

            align-items: center;

            padding: 7px 10px;

            border:

                1px solid var(--line);

            border-radius: 999px;

            background:

                rgba(255, 255, 255, 0.025);

            color: #b8bcc1;

            font-size: 8px;

            font-weight: 900;

            white-space: nowrap;

        }



        .badge.admin {

            border-color:

                rgba(143, 182, 236, 0.16);

            background:

                var(--info-soft);

            color: var(--info);

        }



        .badge.success {

            border-color:

                rgba(101, 213, 154, 0.16);

            background:

                var(--success-soft);

            color: #9ce7bc;

            margin: 0;

        }



        .badge.warning {

            border-color:

                rgba(232, 191, 115, 0.16);

            background:

                var(--warning-soft);

            color: #e8c887;

            margin: 0;

        }



        /* ========================================================= */

        /* FORMS                                                      */

        /* ========================================================= */

        .form-wrap {

            max-width: 760px;

            padding: 26px;

            border:

                1px solid var(--line);

            border-radius: 22px;

            background:

                var(--panel);

        }



        .form-row {

            margin-bottom: 18px;

        }



        label {

            display: block;

            margin-bottom: 8px;

            color: var(--muted);

            font-size: 9px;

            font-weight: 900;

            text-transform: uppercase;

            letter-spacing: 0.1em;

        }



        input,

        select,

        textarea {

            width: 100%;

            padding: 13px 14px;

            border:

                1px solid var(--line);

            border-radius: 12px;

            background:

                var(--panel-soft);

            color: var(--text);

            font-size: 12px;

            transition:

                border-color 0.2s ease,

                box-shadow 0.2s ease,

                background 0.2s ease;

        }



        input::placeholder,

        textarea::placeholder {

            color: #5f646b;

        }



        textarea {

            min-height: 120px;

            resize: vertical;

        }



        input:focus,

        select:focus,

        textarea:focus {

            border-color:

                rgba(215, 164, 95, 0.4);

            outline: none;

            background:

                #171b21;

            box-shadow:

                0 0 0 3px

                rgba(215, 164, 95, 0.07);

        }



        input:disabled,

        select:disabled,

        textarea:disabled {

            opacity: 0.58;

            cursor: not-allowed;

        }



        /* ========================================================= */

        /* ACCESS STATES                                              */

        /* ========================================================= */

        .access-page {

            position: relative;

            min-height: 100vh;

            display: grid;

            place-items: center;

            overflow: hidden;

            padding: 24px;

            background:

                radial-gradient(

                    circle at 50% 0%,

                    rgba(215, 164, 95, 0.1),

                    transparent 26rem

                );

        }



        .access-page::before {

            content: "M";

            position: absolute;

            right: -55px;

            bottom: -95px;

            color:

                rgba(255, 255, 255, 0.02);

            font-size:

                clamp(240px, 45vw, 620px);

            line-height: 0.75;

            font-weight: 950;

            letter-spacing: -0.1em;

            pointer-events: none;

        }



        .access-card {

            position: relative;

            z-index: 1;

            width: 100%;

            max-width: 520px;

            padding: 34px;

            border:

                1px solid var(--line);

            border-radius: 24px;

            background:

                linear-gradient(

                    145deg,

                    rgba(17, 20, 25, 0.98),

                    rgba(12, 15, 19, 0.98)

                );

            text-align: center;

            box-shadow:

                var(--shadow);

        }



        .access-icon {

            width: 64px;

            height: 64px;

            margin: 0 auto 20px;

            display: grid;

            place-items: center;

            border-radius: 18px;

            font-size: 24px;

            font-weight: 950;

        }



        .access-icon.danger {

            border:

                1px solid rgba(239, 143, 143, 0.16);

            background:

                rgba(239, 143, 143, 0.07);

            color: #efaaaa;

        }



        .access-icon.brand {

            margin-bottom: 20px;

            border:

                1px solid rgba(215, 164, 95, 0.22);

            background:

                linear-gradient(

                    145deg,

                    var(--gold-light),

                    var(--gold)

                );

            color: #15110c;

        }



        .access-card h1 {

            margin: 0;

            color: #ffffff;

            font-size: 28px;

            line-height: 1.1;

            font-weight: 950;

            letter-spacing: -0.045em;

        }



        .access-card p {

            margin: 14px 0 24px;

            color: var(--muted);

            font-size: 11px;

            line-height: 1.75;

        }



        /* ========================================================= */

        /* RESPONSIVE                                                 */

        /* ========================================================= */

        @media (max-width: 1000px) {

            .stats-grid {

                grid-template-columns:

                    repeat(2, minmax(160px, 1fr));

            }

        }



        @media (max-width: 900px) {

            .admin-shell {

                flex-direction: column;

            }



            .sidebar {

                position: relative;

                width: 100%;

                height: auto;

                min-height: auto;

                overflow: visible;

                padding:

                    20px;

                border-right: none;

                border-bottom:

                    1px solid var(--line);

            }



            .brand {

                margin-bottom: 20px;

            }



            .sidebar-separator {

                display: none;

            }



            .sidebar nav {

                grid-template-columns:

                    repeat(2, minmax(0, 1fr));

            }



            .nav-title {

                margin:

                    18px

                    8px

                    8px;

            }



            .sidebar-footer {

                margin-top: 20px;

                padding-top: 0;

            }



            .profile-card {

                display: grid;

                grid-template-columns:

                    minmax(0, 1fr)

                    auto;

                gap: 10px 18px;

                align-items: center;

            }



            .logout-form {

                grid-column: 2;

                grid-row: 1 / span 4;

                margin: 0;

            }



            .logout-button {

                min-width: 120px;

            }



            .content {

                padding:

                    22px;

            }



            .topbar,

            .section-heading {

                align-items: flex-start;

                flex-direction: column;

            }

        }



        @media (max-width: 620px) {

            .content {

                padding:

                    16px;

            }



            .sidebar {

                padding:

                    18px 14px;

            }



            .sidebar nav {

                grid-template-columns: 1fr;

            }



            .profile-card {

                display: block;

            }



            .logout-form {

                margin-top: 12px;

            }



            .logout-button {

                width: 100%;

            }



            .main-panel {

                padding: 18px;

                border-radius: 19px;

            }



            .form-wrap {

                padding: 18px;

            }



            .stats-grid {

                grid-template-columns: 1fr;

            }



            .topbar-actions {

                width: 100%;

            }



            .topbar-actions .button,
            .topbar-profile {

                width: 100%;

            }

            .topbar-profile {
                justify-content: flex-start;
            }



            .access-card {

                padding: 26px 20px;

            }

        }

    </style>



    {{-- ========================================================= --}}

    {{-- PAGE-SPECIFIC STYLES                                       --}}

    {{-- ========================================================= --}}

    @stack('styles')

</head>



<body>

    {{-- ========================================================= --}}

    {{-- AUTHENTICATED ADMIN                                        --}}

    {{-- ========================================================= --}}

    @auth

        @if (auth()->user()->is_admin)

            <div class="admin-shell">



                {{-- ===================================================== --}}

                {{-- SIDEBAR                                                 --}}

                {{-- ===================================================== --}}

                <aside class="sidebar">



                    {{-- BRAND --}}

                    <a

                        class="brand"

                        href="{{ route('admin.dashboard') }}"

                    >

                        <div class="brand-mark">

                            M

                        </div>



                        <div class="brand-text">

                            <strong>

                                Mashal

                            </strong>

                            <span>

                                Automotive Admin

                            </span>

                        </div>

                    </a>



                    <div class="sidebar-separator"></div>



                    {{-- ================================================= --}}

                    {{-- ADMIN NAVIGATION                                    --}}

                    {{-- ================================================= --}}

                    <div class="nav-title">

                        Beheer

                    </div>



                    <nav>

                        {{-- DASHBOARD --}}

                        <a

                            class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"

                            href="{{ route('admin.dashboard') }}"

                        >

                            <svg viewBox="0 0 24 24">

                                <path

                                    d="M3 12l9-9 9 9v9a2 2 0 0 1-2 2h-4v-6H9v6H5a2 2 0 0 1-2-2z"

                                />

                            </svg>

                            <span>

                                Dashboard

                            </span>

                        </a>



                        {{-- USERS --}}

                        <a

                            class="nav-link {{ request()->routeIs('users.index') || request()->routeIs('users.edit') ? 'active' : '' }}"

                            href="{{ route('users.index') }}"

                        >

                            <svg viewBox="0 0 24 24">

                                <path

                                    d="M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0zM4 20a7 7 0 0 1 14 0"

                                />

                            </svg>

                            <span>

                                Gebruikers beheren

                            </span>

                        </a>



                        {{-- CREATE USER --}}

                        <a

                            class="nav-link {{ request()->routeIs('users.create') ? 'active' : '' }}"

                            href="{{ route('users.create') }}"

                        >

                            <svg viewBox="0 0 24 24">

                                <path

                                    d="M12 5v14M5 12h14"

                                />

                            </svg>

                            <span>

                                Gebruiker toevoegen

                            </span>

                        </a>

                    </nav>



                    {{-- ================================================= --}}

                    {{-- WEBSITE                                            --}}

                    {{-- ================================================= --}}

                    <div class="nav-title">

                        Mashal Automotive

                    </div>



                    <nav>

                        {{-- WEBSITE --}}

                        <a

                            class="nav-link"

                            href="{{ route('home') }}"

                        >

                            <svg viewBox="0 0 24 24">

                                <path

                                    d="M3 12l9-9 9 9M5 10v11h14V10"

                                />

                            </svg>

                            <span>

                                Website bekijken

                            </span>

                        </a>



                        {{-- CATALOG --}}

                        <a

                            class="nav-link"

                            href="{{ route('catalog') }}"

                        >

                            <svg viewBox="0 0 24 24">

                                <path

                                    d="M3 17h18M5 17l2-7h10l2 7M7 17v2M17 17v2"

                                />

                            </svg>

                            <span>

                                Catalogus

                            </span>

                        </a>



                        {{-- CART --}}

                        <a

                            class="nav-link"

                            href="{{ route('cart') }}"

                        >

                            <svg viewBox="0 0 24 24">

                                <path

                                    d="M3 4h2l2 11h10l3-8H6M9 20h.01M17 20h.01"

                                />

                            </svg>

                            <span>

                                Winkelwagen

                            </span>

                        </a>



                        {{-- ACCOUNT --}}

                        <a

                            class="nav-link"

                            href="{{ route('account') }}"

                        >

                            <svg viewBox="0 0 24 24">

                                <path

                                    d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM4 21a8 8 0 0 1 16 0"

                                />

                            </svg>

                            <span>

                                Mijn account

                            </span>

                        </a>

                    </nav>



                    {{-- ================================================= --}}

                    {{-- PROFILE                                            --}}

                    {{-- ================================================= --}}

                    <div class="sidebar-footer">

                        <div class="profile-card">

                            <div class="profile-head">
                                <div class="profile-avatar">
                                    @if (auth()->user()->avatarUrl())
                                        <img
                                            src="{{ auth()->user()->avatarUrl() }}"
                                            alt="Profielfoto van {{ auth()->user()->name }}"
                                            loading="eager"
                                        >
                                    @else
                                        {{ auth()->user()->initials() }}
                                    @endif
                                </div>

                                <div class="profile-copy">
                                    <div class="profile-label">
                                        Ingelogd als
                                    </div>

                                    <div class="profile-name">
                                        {{ auth()->user()->name }}
                                    </div>

                                    <div class="profile-email">
                                        {{ auth()->user()->email }}
                                    </div>
                                </div>
                            </div>

                            @if (auth()->user()->hasProfilePhoto())
                                <span class="profile-photo-source">
                                    Eigen profielfoto
                                </span>
                            @elseif (auth()->user()->socialAvatar())
                                <span class="profile-photo-source">
                                    Social avatar
                                </span>
                            @else
                                <span class="profile-photo-source">
                                    Initialen
                                </span>
                            @endif

                            <span class="admin-badge">
                                Administrator
                            </span>


                            {{-- LOGOUT --}}

                            <form

                                class="logout-form"

                                method="POST"

                                action="{{ route('logout') }}"

                            >

                                @csrf



                                <button

                                    class="logout-button"

                                    type="submit"

                                >

                                    Uitloggen

                                </button>

                            </form>

                        </div>

                    </div>

                </aside>



                {{-- ===================================================== --}}

                {{-- MAIN CONTENT                                            --}}

                {{-- ===================================================== --}}

                <main class="content">



                    {{-- TOPBAR --}}

                    <section class="topbar">

                        <div>

                            <h1 class="page-title">

                                @yield('page-title', 'Mashal Admin')

                            </h1>



                            <span class="page-subtitle">

                                Beheer gebruikers, rechten en accountgegevens

                                binnen Mashal Automotive.

                            </span>

                        </div>



                        <div class="topbar-actions">

                            <a
                                class="topbar-profile"
                                href="{{ route('account') }}"
                                title="Open mijn account"
                            >
                                <span class="topbar-profile-avatar">
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

                                <span class="topbar-profile-copy">
                                    <strong>
                                        {{ auth()->user()->name }}
                                    </strong>
                                    <span>
                                        Mijn account
                                    </span>
                                </span>
                            </a>


                            <a

                                class="button secondary"

                                href="{{ route('users.index') }}"

                            >

                                Gebruikers beheren

                            </a>



                            <a

                                class="button"

                                href="{{ route('users.create') }}"

                            >

                                + Nieuwe gebruiker

                            </a>

                        </div>

                    </section>



                    {{-- ================================================= --}}

                    {{-- SUCCESS                                            --}}

                    {{-- ================================================= --}}

                    @if (session('success'))

                        <div class="success">

                            <strong>

                                Gelukt!

                            </strong>

                            <br>

                            {{ session('success') }}

                        </div>

                    @endif



                    {{-- ================================================= --}}

                    {{-- ERROR                                              --}}

                    {{-- ================================================= --}}

                    @if (session('error'))

                        <div class="error">

                            <strong>

                                Er ging iets mis.

                            </strong>

                            <br>

                            {{ session('error') }}

                        </div>

                    @endif



                    {{-- ================================================= --}}

                    {{-- VALIDATION ERRORS                                   --}}

                    {{-- ================================================= --}}

                    @if ($errors->any())

                        <div class="error">

                            <strong>

                                Controleer onderstaande gegevens:

                            </strong>

                            <ul

                                style="

                                    margin: 10px 0 0 18px;

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



                    {{-- ================================================= --}}

                    {{-- PAGE CONTENT                                        --}}

                    {{-- ================================================= --}}

                    @yield('content')

                </main>

            </div>

        @else

            {{-- ========================================================= --}}

            {{-- LOGGED IN BUT NOT ADMIN                                    --}}

            {{-- ========================================================= --}}

            <main class="access-page">

                <div class="access-card">

                    <div class="access-icon danger">

                        !

                    </div>



                    <h1>

                        Geen administratorrechten

                    </h1>



                    <p>

                        Je bent ingelogd, maar je account heeft geen

                        administratorrechten voor de Mashal-beheeromgeving.

                        Ga terug naar je account om de gewone website te gebruiken.

                    </p>



                    <a

                        class="button"

                        href="{{ route('account') }}"

                    >

                        Naar mijn account

                    </a>

                </div>

            </main>

        @endif



    @else

        {{-- ============================================================= --}}

        {{-- NOT LOGGED IN                                                  --}}

        {{-- ============================================================= --}}

        <main class="access-page">

            <div class="access-card">

                <div class="access-icon brand">

                    M

                </div>



                <h1>

                    Mashal Admin

                </h1>



                <p>

                    Je moet ingelogd zijn met een administratoraccount

                    om de Mashal Automotive-beheeromgeving te openen.

                </p>



                <a

                    class="button"

                    href="{{ route('login') }}"

                >

                    Inloggen

                </a>

            </div>

        </main>

    @endauth



    {{-- ========================================================= --}}

    {{-- PAGE-SPECIFIC SCRIPTS                                      --}}

    {{-- ========================================================= --}}

    @stack('scripts')

</body>

</html>