<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Telegram registratie afronden - {{ config('app.name', 'Mashal Automotive') }}</title>

    <meta
        name="description"
        content="Rond je Telegram-registratie veilig af."
    >

    <style>
        :root {
            color-scheme: light dark;
            --bg: #f5f7fb;
            --card: #ffffff;
            --card-soft: #f8fafc;
            --text: #111827;
            --muted: #6b7280;
            --border: #e5e7eb;
            --input: #ffffff;
            --input-border: #cbd5e1;
            --telegram: #229ed9;
            --telegram-hover: #168ac1;
            --danger-bg: #fef2f2;
            --danger-border: #fecaca;
            --danger-text: #991b1b;
            --focus: rgba(34, 158, 217, 0.22);
            --shadow: 0 20px 60px rgba(15, 23, 42, 0.12);
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #0b1120;
                --card: #111827;
                --card-soft: #172033;
                --text: #f8fafc;
                --muted: #94a3b8;
                --border: #263247;
                --input: #0f172a;
                --input-border: #334155;
                --danger-bg: rgba(127, 29, 29, 0.22);
                --danger-border: rgba(248, 113, 113, 0.35);
                --danger-text: #fecaca;
                --shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
            }
        }

        * {
            box-sizing: border-box;
        }

        html {
            min-height: 100%;
            background: var(--bg);
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
            color: var(--text);
            background:
                radial-gradient(
                    circle at top,
                    rgba(34, 158, 217, 0.12),
                    transparent 34rem
                ),
                var(--bg);
        }

        a {
            color: inherit;
        }

        .page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 18px;
        }

        .card {
            width: min(100%, 520px);
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .header {
            padding: 34px 34px 22px;
            text-align: center;
        }

        .telegram-logo {
            width: 64px;
            height: 64px;
            margin: 0 auto 18px;
            display: grid;
            place-items: center;
            border-radius: 20px;
            background: var(--telegram);
            box-shadow: 0 14px 32px rgba(34, 158, 217, 0.28);
        }

        .telegram-logo svg {
            width: 34px;
            height: 34px;
            display: block;
            fill: #ffffff;
        }

        .title {
            margin: 0;
            font-size: clamp(1.6rem, 4vw, 2rem);
            line-height: 1.2;
            letter-spacing: -0.03em;
        }

        .subtitle {
            margin: 12px auto 0;
            max-width: 420px;
            color: var(--muted);
            font-size: 0.98rem;
            line-height: 1.65;
        }

        .profile {
            margin: 0 34px 24px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 14px;
            background: var(--card-soft);
            border: 1px solid var(--border);
            border-radius: 18px;
        }

        .avatar {
            width: 52px;
            height: 52px;
            flex: 0 0 52px;
            display: grid;
            place-items: center;
            overflow: hidden;
            border-radius: 50%;
            background: var(--telegram);
            color: #ffffff;
            font-size: 1.05rem;
            font-weight: 800;
            text-transform: uppercase;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-copy {
            min-width: 0;
        }

        .profile-name {
            margin: 0;
            overflow: hidden;
            font-size: 1rem;
            font-weight: 750;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .profile-username {
            margin: 4px 0 0;
            overflow: hidden;
            color: var(--muted);
            font-size: 0.9rem;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .body {
            padding: 0 34px 34px;
        }

        .alert {
            margin-bottom: 20px;
            padding: 14px 16px;
            border: 1px solid var(--danger-border);
            border-radius: 14px;
            background: var(--danger-bg);
            color: var(--danger-text);
            font-size: 0.92rem;
            line-height: 1.5;
        }

        .alert ul {
            margin: 8px 0 0;
            padding-left: 20px;
        }

        .field {
            margin-bottom: 18px;
        }

        .label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
            font-weight: 700;
        }

        .input {
            width: 100%;
            min-height: 50px;
            padding: 0 15px;
            color: var(--text);
            background: var(--input);
            border: 1px solid var(--input-border);
            border-radius: 14px;
            outline: none;
            font: inherit;
            transition:
                border-color 150ms ease,
                box-shadow 150ms ease,
                transform 150ms ease;
        }

        .input:focus {
            border-color: var(--telegram);
            box-shadow: 0 0 0 4px var(--focus);
        }

        .input[aria-invalid="true"] {
            border-color: #ef4444;
        }

        .field-error {
            margin: 7px 0 0;
            color: #ef4444;
            font-size: 0.84rem;
            line-height: 1.4;
        }

        .hint {
            margin: 7px 0 0;
            color: var(--muted);
            font-size: 0.82rem;
            line-height: 1.5;
        }

        .button {
            width: 100%;
            min-height: 52px;
            margin-top: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: 0;
            border-radius: 14px;
            color: #ffffff;
            background: var(--telegram);
            font: inherit;
            font-weight: 800;
            cursor: pointer;
            transition:
                background 150ms ease,
                transform 150ms ease,
                box-shadow 150ms ease;
        }

        .button:hover {
            background: var(--telegram-hover);
            box-shadow: 0 10px 24px rgba(34, 158, 217, 0.22);
        }

        .button:active {
            transform: translateY(1px);
        }

        .button svg {
            width: 20px;
            height: 20px;
            fill: currentColor;
        }

        .divider {
            height: 1px;
            margin: 26px 0 20px;
            background: var(--border);
        }

        .back {
            display: block;
            text-align: center;
            color: var(--muted);
            font-size: 0.9rem;
            text-decoration: none;
        }

        .back:hover {
            color: var(--text);
            text-decoration: underline;
        }

        .privacy {
            margin: 20px 0 0;
            color: var(--muted);
            font-size: 0.78rem;
            line-height: 1.55;
            text-align: center;
        }

        @media (max-width: 560px) {
            .page {
                padding: 18px 12px;
            }

            .card {
                border-radius: 20px;
            }

            .header {
                padding: 28px 22px 20px;
            }

            .profile {
                margin: 0 22px 22px;
            }

            .body {
                padding: 0 22px 28px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                transition: none !important;
                animation: none !important;
            }
        }
    </style>
</head>

<body>
    @php
        $telegramUsername = trim((string) ($telegram['username'] ?? ''));
        $telegramPhoto = trim((string) ($telegram['photo_url'] ?? ''));
        $telegramFirstName = trim((string) ($telegram['first_name'] ?? ''));
        $telegramLastName = trim((string) ($telegram['last_name'] ?? ''));

        $telegramFullName = trim(
            $telegramFirstName . ' ' . $telegramLastName
        );

        $displayName = $telegramFullName !== ''
            ? $telegramFullName
            : ($telegramUsername !== '' ? '@' . $telegramUsername : 'Telegram gebruiker');

        $initialSource = $telegramFirstName !== ''
            ? $telegramFirstName
            : ($telegramUsername !== '' ? $telegramUsername : 'T');

        $initial = mb_strtoupper(
            mb_substr($initialSource, 0, 1)
        );
    @endphp

    <main class="page">
        <section class="card" aria-labelledby="telegram-registration-title">
            <header class="header">
                <div class="telegram-logo" aria-hidden="true">
                    <svg
                        viewBox="0 0 24 24"
                        role="img"
                        focusable="false"
                        aria-hidden="true"
                    >
                        <path d="M21.944 2.506c.281-.107.574.046.477.416l-3.566 16.827c-.084.398-.319.495-.647.309l-5.433-4.005-2.621 2.522c-.29.29-.533.532-1.093.532l.39-5.536L19.53 4.466c.438-.39-.095-.607-.68-.217L6.394 12.09l-5.363-1.676c-.585-.183-.596-.585.122-.866L21.944 2.506Z"/>
                    </svg>
                </div>

                <h1
                    id="telegram-registration-title"
                    class="title"
                >
                    Registratie afronden
                </h1>

                <p class="subtitle">
                    Je Telegram-account is succesvol gecontroleerd.
                    Vul nog je naam en e-mailadres in om je account af te ronden.
                </p>
            </header>

            <div class="profile">
                <div class="avatar" aria-hidden="true">
                    @if ($telegramPhoto !== '')
                        <img
                            src="{{ $telegramPhoto }}"
                            alt=""
                            referrerpolicy="no-referrer"
                        >
                    @else
                        {{ $initial }}
                    @endif
                </div>

                <div class="profile-copy">
                    <p class="profile-name">
                        {{ $displayName }}
                    </p>

                    <p class="profile-username">
                        @if ($telegramUsername !== '')
                            {{ '@' . $telegramUsername }}
                        @else
                            Verbonden via Telegram
                        @endif
                    </p>
                </div>
            </div>

            <div class="body">
                @if ($errors->has('telegram'))
                    <div
                        class="alert"
                        role="alert"
                    >
                        {{ $errors->first('telegram') }}
                    </div>
                @endif

                @if ($errors->any() && ! $errors->has('telegram'))
                    <div
                        class="alert"
                        role="alert"
                    >
                        Controleer de onderstaande gegevens.

                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form
                    method="POST"
                    action="{{ route('auth.telegram.complete.submit') }}"
                    novalidate
                >
                    @csrf

                    <div class="field">
                        <label
                            for="name"
                            class="label"
                        >
                            Naam
                        </label>

                        <input
                            id="name"
                            class="input"
                            type="text"
                            name="name"
                            value="{{ old('name', $suggestedName ?? $displayName) }}"
                            autocomplete="name"
                            maxlength="255"
                            required
                            autofocus
                            @error('name')
                                aria-invalid="true"
                                aria-describedby="name-error"
                            @enderror
                        >

                        @error('name')
                            <p
                                id="name-error"
                                class="field-error"
                            >
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="field">
                        <label
                            for="email"
                            class="label"
                        >
                            E-mailadres
                        </label>

                        <input
                            id="email"
                            class="input"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            inputmode="email"
                            maxlength="255"
                            required
                            @error('email')
                                aria-invalid="true"
                                aria-describedby="email-error email-hint"
                            @else
                                aria-describedby="email-hint"
                            @enderror
                        >

                        @error('email')
                            <p
                                id="email-error"
                                class="field-error"
                            >
                                {{ $message }}
                            </p>
                        @enderror

                        <p
                            id="email-hint"
                            class="hint"
                        >
                            Telegram geeft je e-mailadres niet aan deze website door.
                            Daarom moet je het hier zelf invullen en daarna verifiëren.
                        </p>
                    </div>

                    <button
                        class="button"
                        type="submit"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                            focusable="false"
                        >
                            <path d="M21.944 2.506c.281-.107.574.046.477.416l-3.566 16.827c-.084.398-.319.495-.647.309l-5.433-4.005-2.621 2.522c-.29.29-.533.532-1.093.532l.39-5.536L19.53 4.466c.438-.39-.095-.607-.68-.217L6.394 12.09l-5.363-1.676c-.585-.183-.596-.585.122-.866L21.944 2.506Z"/>
                        </svg>

                        Account afronden
                    </button>
                </form>

                <div class="divider" aria-hidden="true"></div>

                <a
                    class="back"
                    href="{{ route('login') }}"
                >
                    Terug naar inloggen
                </a>

                <p class="privacy">
                    Je Telegram-ID wordt alleen gebruikt om jouw Telegram-account
                    veilig aan je account op deze website te koppelen.
                </p>
            </div>
        </section>
    </main>
</body>
</html>
