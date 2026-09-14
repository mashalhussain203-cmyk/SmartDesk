<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel')</title>
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
            --orange: #ffb36b;
            --shadow: rgba(12, 73, 58, 0.18);
            --danger: #d65a5a;
            --font: 'Inter', 'Segoe UI', Arial, sans-serif;
        }

        * {
            box-sizing: border-box;
            -webkit-font-smoothing: antialiased;
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: var(--font);
            min-height: 100vh;
        }

        .admin-shell {
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
            width: 270px;
            background: var(--green-dark);
            color: white;
            padding: 30px 24px;
            flex-shrink: 0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 44px;
        }

        .brand-mark {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: var(--mint);
            color: var(--green-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            font-weight: 900;
        }

        .brand-text strong {
            display: block;
            font-size: 22px;
            letter-spacing: -0.04em;
        }

        .brand-text span {
            color: #b7d8d0;
            font-size: 12px;
            display: block;
            margin-top: 5px;
        }

        .nav-title {
            color: #a8d6cd;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            margin: 24px 0 14px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #effef8;
            text-decoration: none;
            padding: 13px 14px;
            border-radius: 12px;
            margin-bottom: 10px;
            font-size: 14px;
            transition: 0.2s ease;
        }

        .nav-link svg {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
        }

        .nav-link.active,
        .nav-link:hover {
            background: var(--mint);
            color: var(--green-dark);
        }

        .profile-card {
            background: rgba(255,255,255,0.08);
            padding: 18px;
            border-radius: 18px;
            border: 1px solid rgba(255,255,255,0.1);
            margin-top: 50px;
        }

        .profile-card .small-label {
            color: #b7d8d0;
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 7px;
        }

        .profile-card .name {
            font-size: 16px;
            font-weight: 800;
        }

        .content {
            flex: 1;
            padding: 26px 42px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .page-title {
            font-size: 30px;
            font-weight: 900;
            line-height: 1.2;
            margin: 0;
            color: var(--text);
            letter-spacing: -0.04em;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .search {
            border: 1px solid var(--line);
            border-radius: 12px;
            background: var(--panel);
            padding: 11px 16px;
            min-width: 240px;
            color: var(--muted);
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: var(--green);
            color: #fff;
            border: none;
            padding: 11px 18px;
            font-size: 13px;
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 8px 16px var(--shadow);
        }

        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 22px var(--shadow);
        }

        .button.secondary {
            background: var(--panel-soft);
            color: var(--green-dark);
            border: 1px solid var(--line);
            box-shadow: none;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(160px, 1fr));
            gap: 16px;
            margin: 20px 0;
        }

        .stat-card {
            background: var(--panel);
            border-radius: 20px;
            padding: 22px;
            border: 1px solid rgba(32,127,94,0.08);
            box-shadow: 0 6px 16px rgba(10, 70, 55, 0.08);
        }

        .stat-card .stat-label {
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .stat-card .stat-num {
            font-size: 34px;
            font-weight: 900;
            color: var(--green-dark);
            margin: 14px 0 8px;
        }

        .stat-card .stat-foot {
            color: var(--muted);
            font-size: 11px;
        }

        .main-panel {
            background: var(--panel);
            border-radius: 26px;
            border: 1px solid var(--line);
            padding: 24px;
            box-shadow: 0 16px 40px rgba(10, 70, 55, 0.08);
            margin-top: 18px;
        }

        .section-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
        }

        .section-heading h2 {
            font-size: 22px;
            font-weight: 900;
            margin: 0;
            color: var(--text);
        }

        .section-heading span {
            color: var(--muted);
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            font-size: 11px;
            color: var(--muted);
            padding: 14px 10px;
            border-bottom: 1px solid var(--line);
        }

        td {
            padding: 16px 10px;
            border-bottom: 1px solid var(--line);
            font-size: 14px;
            color: var(--text);
        }

        .user-name {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
        }

        .avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--green-soft);
            color: var(--green-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
        }

        .badge {
            background: var(--green-soft);
            color: var(--green-dark);
            border-radius: 99px;
            padding: 8px 12px;
            font-size: 11px;
            font-weight: 800;
        }

        .form-wrap {
            background: var(--panel-soft);
            border-radius: 22px;
            padding: 30px;
            border: 1px solid var(--line);
            max-width: 680px;
        }

        .form-row {
            margin-bottom: 16px;
        }

        label {
            display: block;
            color: var(--muted);
            font-weight: 700;
            font-size: 11px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: var(--panel);
            padding: 13px 14px;
            font-size: 14px;
            color: var(--text);
        }

        input:focus {
            border-color: var(--green);
            outline: none;
            box-shadow: 0 0 0 3px rgba(31,138,112,0.12);
        }

        .alert {
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: 13px;
            font-weight: 700;
            color: var(--green-dark);
            background: var(--green-soft);
        }

        @media (max-width: 900px) {
            .admin-shell {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
            }

            .stats-grid {
                grid-template-columns: repeat(2, minmax(160px, 1fr));
            }
        }
    </style>
</head>
<body>
    <div class="admin-shell">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-mark">S</div>
                <div class="brand-text">
                    <strong>SmartDesk</strong>
                    <span>Admin Portal</span>
                </div>
            </div>

            <div class="nav-title">Navigatie</div>
            <nav>
                <a class="nav-link active" href="{{ route('admin.dashboard') }}">
                    <svg viewBox="0 0 24 24">
                        <path d="M3 12l9-9 9 9v9a2 2 0 0 1-2 2h-4v-6H9v6H5a2 2 0 0 1-2-2z" />
                    </svg>
                    Dashboard
                </a>
                <a class="nav-link" href="{{ route('users.index') }}">
                    <svg viewBox="0 0 24 24">
                        <path d="M16 11a4 4 0 1 0-8 0 4 4 0 0 0 8 0zM4 20a7 7 0 0 1 14 0" />
                    </svg>
                    Gebruikers
                </a>
                <a class="nav-link" href="{{ route('users.create') }}">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14" />
                    </svg>
                    Nieuwe gebruiker
                </a>
            </nav>

            <div class="profile-card">
                <div class="small-label">Systeem</div>
                <div class="name">Opslagbeheer</div>
            </div>
        </aside>

        <main class="content">
            <section class="topbar">
                <div>
                    <h1 class="page-title">Dashboard</h1>
                </div>
                <div class="topbar-actions">
                    <input class="search" type="text" placeholder="Zoeken...">
                    <a class="button" href="{{ route('users.create') }}">+ Gebruiker toevoegen</a>
                </div>
            </section>

            @if (session('success'))
                <div class="alert">
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
