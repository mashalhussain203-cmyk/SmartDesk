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

    <title>
        @yield('title', 'SmartDesk Admin')
    </title>


    <style>

        :root {
            --bg: #eef4f4;

            --panel: #ffffff;
            --panel-soft: #f8fbfb;

            --text: #14211f;
            --muted: #60716e;

            --line: #dce9e6;

            --green: #1f8a70;
            --green-dark: #11583f;
            --green-soft: #dcf8ee;

            --mint: #87d9bd;

            --success: #187a42;
            --success-soft: #e8f7ee;

            --warning: #9a6500;
            --warning-soft: #fff4df;

            --danger: #c74646;
            --danger-soft: #fdecec;

            --info: #174a82;
            --info-soft: #dcecff;

            --shadow: rgba(12, 73, 58, 0.14);

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
            -webkit-font-smoothing: antialiased;
        }


        html {
            scroll-behavior: smooth;
        }


        body {
            margin: 0;
            min-height: 100vh;

            background: var(--bg);
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
            width: 285px;
            min-height: 100vh;

            background: var(--green-dark);
            color: #ffffff;

            padding: 30px 24px;

            flex-shrink: 0;

            display: flex;
            flex-direction: column;
        }


        /* ========================================================= */
        /* BRAND                                                      */
        /* ========================================================= */

        .brand {
            display: flex;
            align-items: center;

            gap: 12px;

            margin-bottom: 38px;

            color: #ffffff;
            text-decoration: none;
        }


        .brand-mark {
            width: 44px;
            height: 44px;

            border-radius: 50%;

            background: var(--mint);
            color: var(--green-dark);

            display: flex;
            align-items: center;
            justify-content: center;

            flex-shrink: 0;

            font-size: 23px;
            font-weight: 900;
        }


        .brand-text strong {
            display: block;

            font-size: 22px;
            font-weight: 900;

            letter-spacing: -0.04em;
        }


        .brand-text span {
            display: block;

            margin-top: 4px;

            color: #b7d8d0;

            font-size: 12px;
        }


        /* ========================================================= */
        /* NAVIGATION                                                 */
        /* ========================================================= */

        .nav-title {
            margin: 25px 0 12px;

            color: #a8d6cd;

            font-size: 10px;
            font-weight: 800;

            text-transform: uppercase;

            letter-spacing: 0.15em;
        }


        .nav-link {
            display: flex;
            align-items: center;

            gap: 12px;

            margin-bottom: 7px;

            padding: 13px 14px;

            border-radius: 12px;

            color: #effef8;

            text-decoration: none;

            font-size: 14px;
            font-weight: 600;

            transition:
                background 0.2s ease,
                color 0.2s ease,
                transform 0.2s ease;
        }


        .nav-link svg {
            width: 20px;
            height: 20px;

            fill: none;
            stroke: currentColor;
            stroke-width: 2;

            stroke-linecap: round;
            stroke-linejoin: round;

            flex-shrink: 0;
        }


        .nav-link:hover,
        .nav-link.active {
            background: var(--mint);
            color: var(--green-dark);
        }


        .nav-link:hover {
            transform: translateX(2px);
        }


        /* ========================================================= */
        /* SIDEBAR PROFILE                                           */
        /* ========================================================= */

        .sidebar-footer {
            margin-top: auto;
            padding-top: 35px;
        }


        .profile-card {
            padding: 18px;

            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 18px;

            background: rgba(255, 255, 255, 0.08);
        }


        .profile-label {
            margin-bottom: 7px;

            color: #b7d8d0;

            font-size: 10px;
            font-weight: 800;

            text-transform: uppercase;

            letter-spacing: 0.1em;
        }


        .profile-name {
            font-size: 15px;
            font-weight: 800;

            word-break: break-word;
        }


        .profile-email {
            margin-top: 5px;

            color: #d8eee9;

            font-size: 11px;

            word-break: break-word;
        }


        .admin-badge {
            display: inline-flex;

            margin-top: 10px;

            padding: 6px 10px;

            border-radius: 999px;

            background: var(--mint);
            color: var(--green-dark);

            font-size: 10px;
            font-weight: 900;

            text-transform: uppercase;

            letter-spacing: 0.08em;
        }


        /* ========================================================= */
        /* LOGOUT                                                     */
        /* ========================================================= */

        .logout-form {
            margin-top: 14px;
        }


        .logout-button {
            width: 100%;

            padding: 11px 14px;

            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 10px;

            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;

            cursor: pointer;

            font-size: 12px;
            font-weight: 800;

            transition:
                background 0.2s ease,
                border-color 0.2s ease;
        }


        .logout-button:hover {
            background: rgba(255, 255, 255, 0.16);
            border-color: rgba(255, 255, 255, 0.28);
        }


        /* ========================================================= */
        /* CONTENT                                                    */
        /* ========================================================= */

        .content {
            flex: 1;

            min-width: 0;

            padding: 26px 42px 50px;
        }


        /* ========================================================= */
        /* TOPBAR                                                     */
        /* ========================================================= */

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 20px;

            margin-bottom: 24px;
        }


        .page-title {
            margin: 0;

            color: var(--text);

            font-size: 30px;
            font-weight: 900;

            line-height: 1.2;

            letter-spacing: -0.04em;
        }


        .page-subtitle {
            display: block;

            margin-top: 6px;

            color: var(--muted);

            font-size: 12px;
        }


        .topbar-actions {
            display: flex;
            align-items: center;

            gap: 12px;

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

            padding: 11px 18px;

            border: none;
            border-radius: 12px;

            background: var(--green);
            color: #ffffff;

            text-decoration: none;

            font-size: 13px;
            font-weight: 800;

            cursor: pointer;

            box-shadow:
                0 8px 16px var(--shadow);

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease;
        }


        .button:hover {
            transform: translateY(-1px);

            box-shadow:
                0 12px 22px var(--shadow);
        }


        .button.secondary {
            background: var(--panel-soft);
            color: var(--green-dark);

            border: 1px solid var(--line);

            box-shadow: none;
        }


        .button.danger {
            background: var(--danger);

            box-shadow: none;
        }


        .button.danger:hover {
            background: #aa3838;
        }


        /* ========================================================= */
        /* MESSAGES                                                   */
        /* ========================================================= */

        .alert,
        .success,
        .error,
        .warning {
            margin-bottom: 20px;

            padding: 14px 16px;

            border-radius: 12px;

            font-size: 13px;
            line-height: 1.6;
        }


        .alert,
        .success {
            border: 1px solid #cbead7;

            background: var(--success-soft);
            color: var(--success);
        }


        .error {
            border: 1px solid #efcaca;

            background: var(--danger-soft);
            color: var(--danger);
        }


        .warning {
            border: 1px solid #f0dfb9;

            background: var(--warning-soft);
            color: var(--warning);
        }


        /* ========================================================= */
        /* STATS                                                      */
        /* ========================================================= */

        .stats-grid {
            display: grid;

            grid-template-columns:
                repeat(4, minmax(160px, 1fr));

            gap: 16px;

            margin: 20px 0;
        }


        .stat-card {
            padding: 22px;

            border: 1px solid rgba(32, 127, 94, 0.08);
            border-radius: 20px;

            background: var(--panel);

            box-shadow:
                0 6px 16px rgba(10, 70, 55, 0.08);
        }


        .stat-label {
            color: var(--muted);

            font-size: 11px;
            font-weight: 700;

            text-transform: uppercase;

            letter-spacing: 0.12em;
        }


        .stat-num {
            margin: 14px 0 8px;

            color: var(--green-dark);

            font-size: 34px;
            font-weight: 900;
        }


        .stat-foot {
            color: var(--muted);

            font-size: 11px;
        }


        /* ========================================================= */
        /* PANELS                                                     */
        /* ========================================================= */

        .main-panel {
            margin-top: 18px;

            padding: 24px;

            border: 1px solid var(--line);
            border-radius: 26px;

            background: var(--panel);

            box-shadow:
                0 16px 40px rgba(10, 70, 55, 0.08);
        }


        .section-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 16px;

            margin-bottom: 22px;
        }


        .section-heading h2 {
            margin: 0;

            color: var(--text);

            font-size: 22px;
            font-weight: 900;
        }


        .section-heading span {
            display: block;

            margin-top: 5px;

            color: var(--muted);

            font-size: 12px;
        }


        /* ========================================================= */
        /* TABLES                                                     */
        /* ========================================================= */

        .table-wrap {
            width: 100%;

            overflow-x: auto;
        }


        table {
            width: 100%;

            border-collapse: collapse;
        }


        th {
            padding: 14px 10px;

            border-bottom: 1px solid var(--line);

            color: var(--muted);

            text-align: left;

            font-size: 11px;
            font-weight: 800;

            text-transform: uppercase;

            letter-spacing: 0.12em;

            white-space: nowrap;
        }


        td {
            padding: 16px 10px;

            border-bottom: 1px solid var(--line);

            color: var(--text);

            font-size: 14px;

            vertical-align: middle;
        }


        tbody tr:last-child td {
            border-bottom: none;
        }


        tbody tr:hover {
            background: #fbfdfd;
        }


        /* ========================================================= */
        /* USER                                                      */
        /* ========================================================= */

        .user-name {
            display: flex;
            align-items: center;

            gap: 10px;

            font-weight: 700;
        }


        .avatar {
            width: 36px;
            height: 36px;

            border-radius: 50%;

            background: var(--green-soft);
            color: var(--green-dark);

            display: inline-flex;
            align-items: center;
            justify-content: center;

            flex-shrink: 0;

            font-weight: 900;
        }


        /* ========================================================= */
        /* BADGES                                                     */
        /* ========================================================= */

        .badge {
            display: inline-flex;
            align-items: center;

            padding: 7px 11px;

            border-radius: 999px;

            background: var(--green-soft);
            color: var(--green-dark);

            font-size: 11px;
            font-weight: 800;
        }


        .badge.admin {
            background: var(--info-soft);
            color: var(--info);
        }


        .badge.success {
            background: var(--success-soft);
            color: var(--success);

            border: none;

            margin: 0;
        }


        .badge.warning {
            background: var(--warning-soft);
            color: var(--warning);

            border: none;

            margin: 0;
        }


        /* ========================================================= */
        /* FORMS                                                      */
        /* ========================================================= */

        .form-wrap {
            max-width: 720px;

            padding: 30px;

            border: 1px solid var(--line);
            border-radius: 22px;

            background: var(--panel-soft);
        }


        .form-row {
            margin-bottom: 18px;
        }


        label {
            display: block;

            margin-bottom: 8px;

            color: var(--muted);

            font-size: 11px;
            font-weight: 800;

            text-transform: uppercase;

            letter-spacing: 0.1em;
        }


        input,
        select,
        textarea {
            width: 100%;

            padding: 13px 14px;

            border: 1px solid var(--line);
            border-radius: 12px;

            background: var(--panel);
            color: var(--text);

            font-size: 14px;

            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease;
        }


        textarea {
            min-height: 120px;

            resize: vertical;
        }


        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--green);

            outline: none;

            box-shadow:
                0 0 0 3px rgba(31, 138, 112, 0.12);
        }


        input:disabled,
        select:disabled,
        textarea:disabled {
            opacity: 0.65;

            cursor: not-allowed;
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
                width: 100%;
                min-height: auto;
            }


            .sidebar-footer {
                margin-top: 25px;
                padding-top: 0;
            }


            .content {
                padding: 24px;
            }


            .topbar,
            .section-heading {
                align-items: flex-start;
                flex-direction: column;
            }

        }


        @media (max-width: 600px) {

            .content {
                padding: 18px;
            }


            .sidebar {
                padding: 24px 18px;
            }


            .main-panel {
                padding: 18px;

                border-radius: 18px;
            }


            .form-wrap {
                padding: 20px;
            }


            .stats-grid {
                grid-template-columns: 1fr;
            }


            .topbar-actions {
                width: 100%;
            }


            .topbar-actions .button {
                width: 100%;
            }

        }

    </style>

</head>


<body>

    {{-- ========================================================= --}}
    {{-- AUTHENTICATED ADMIN                                        --}}
    {{-- ========================================================= --}}

    @auth

        @if (auth()->user()->is_admin)

            <div class="admin-shell">


                {{-- ========================================================= --}}
                {{-- SIDEBAR                                                    --}}
                {{-- ========================================================= --}}

                <aside class="sidebar">


                    {{-- BRAND --}}

                    <a
                        class="brand"
                        href="{{ route('admin.dashboard') }}"
                    >

                        <div class="brand-mark">
                            S
                        </div>

                        <div class="brand-text">

                            <strong>
                                SmartDesk
                            </strong>

                            <span>
                                Admin beheeromgeving
                            </span>

                        </div>

                    </a>


                    {{-- ========================================================= --}}
                    {{-- ADMIN NAVIGATION                                           --}}
                    {{-- ========================================================= --}}

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


                    {{-- ========================================================= --}}
                    {{-- WEBSITE                                                    --}}
                    {{-- ========================================================= --}}

                    <div class="nav-title">
                        SmartDesk
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


                    {{-- ========================================================= --}}
                    {{-- PROFILE                                                    --}}
                    {{-- ========================================================= --}}

                    <div class="sidebar-footer">

                        <div class="profile-card">

                            <div class="profile-label">
                                Ingelogd als
                            </div>


                            <div class="profile-name">
                                {{ auth()->user()->name }}
                            </div>


                            <div class="profile-email">
                                {{ auth()->user()->email }}
                            </div>


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


                {{-- ========================================================= --}}
                {{-- MAIN CONTENT                                                --}}
                {{-- ========================================================= --}}

                <main class="content">


                    {{-- TOPBAR --}}

                    <section class="topbar">

                        <div>

                            <h1 class="page-title">
                                @yield('page-title', 'SmartDesk Admin')
                            </h1>

                            <span class="page-subtitle">
                                Beheer gebruikers, rechten en SmartDesk-accountgegevens.
                            </span>

                        </div>


                        <div class="topbar-actions">

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


                    {{-- ========================================================= --}}
                    {{-- SUCCESS                                                    --}}
                    {{-- ========================================================= --}}

                    @if (session('success'))

                        <div class="success">

                            <strong>
                                Gelukt!
                            </strong>

                            <br>

                            {{ session('success') }}

                        </div>

                    @endif


                    {{-- ========================================================= --}}
                    {{-- ERROR                                                      --}}
                    {{-- ========================================================= --}}

                    @if (session('error'))

                        <div class="error">

                            <strong>
                                Er ging iets mis.
                            </strong>

                            <br>

                            {{ session('error') }}

                        </div>

                    @endif


                    {{-- ========================================================= --}}
                    {{-- VALIDATION ERRORS                                          --}}
                    {{-- ========================================================= --}}

                    @if ($errors->any())

                        <div class="error">

                            <strong>
                                Controleer onderstaande gegevens:
                            </strong>

                            <ul style="margin: 10px 0 0 20px;">

                                @foreach ($errors->all() as $error)

                                    <li>
                                        {{ $error }}
                                    </li>

                                @endforeach

                            </ul>

                        </div>

                    @endif


                    {{-- ========================================================= --}}
                    {{-- PAGE CONTENT                                               --}}
                    {{-- ========================================================= --}}

                    @yield('content')

                </main>

            </div>

        @else

            {{-- ========================================================= --}}
            {{-- LOGGED IN BUT NOT ADMIN                                    --}}
            {{-- ========================================================= --}}

            <main
                style="
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 24px;
                "
            >

                <div
                    style="
                        width: 100%;
                        max-width: 500px;
                        padding: 35px;
                        border: 1px solid #dce9e6;
                        border-radius: 20px;
                        background: #ffffff;
                        text-align: center;
                    "
                >

                    <div
                        style="
                            width: 60px;
                            height: 60px;
                            margin: 0 auto 20px;
                            border-radius: 50%;
                            background: #fdecec;
                            color: #c74646;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 26px;
                            font-weight: 900;
                        "
                    >
                        !
                    </div>


                    <h1 style="margin: 0 0 12px;">
                        Geen administratorrechten
                    </h1>


                    <p
                        style="
                            margin: 0 0 25px;
                            color: #60716e;
                            line-height: 1.6;
                        "
                    >
                        Je bent wel ingelogd, maar je account heeft geen
                        administratorrechten voor deze beheeromgeving.
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

        {{-- ========================================================= --}}
        {{-- NOT LOGGED IN                                              --}}
        {{-- ========================================================= --}}

        <main
            style="
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 24px;
            "
        >

            <div
                style="
                    width: 100%;
                    max-width: 500px;
                    padding: 35px;
                    border: 1px solid #dce9e6;
                    border-radius: 20px;
                    background: #ffffff;
                    text-align: center;
                "
            >

                <div
                    style="
                        width: 60px;
                        height: 60px;
                        margin: 0 auto 20px;
                        border-radius: 50%;
                        background: #dcf8ee;
                        color: #11583f;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 24px;
                        font-weight: 900;
                    "
                >
                    S
                </div>


                <h1 style="margin: 0 0 12px;">
                    SmartDesk Admin
                </h1>


                <p
                    style="
                        margin: 0 0 25px;
                        color: #60716e;
                        line-height: 1.6;
                    "
                >
                    Je moet ingelogd zijn met een administratoraccount
                    om het SmartDesk-beheer te openen.
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

</body>

</html>

