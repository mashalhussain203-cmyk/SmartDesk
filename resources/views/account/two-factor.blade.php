<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Authenticator-beveiliging | Mashal Studio</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        :root {
            color-scheme: light;
            --bg: #f5f7fb;
            --card: #ffffff;
            --text: #111827;
            --muted: #6b7280;
            --border: #e5e7eb;
            --primary: #111827;
            --primary-hover: #1f2937;
            --success-bg: #ecfdf5;
            --success-text: #065f46;
            --success-border: #a7f3d0;
            --error-bg: #fef2f2;
            --error-text: #991b1b;
            --error-border: #fecaca;
            --warning-bg: #fffbeb;
            --warning-text: #92400e;
            --warning-border: #fde68a;
            --danger: #dc2626;
            --danger-hover: #b91c1c;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            color: var(--text);
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
        }

        a {
            color: inherit;
        }

        button,
        input {
            font: inherit;
        }

        .page {
            width: min(920px, calc(100% - 32px));
            margin: 0 auto;
            padding: 32px 0 56px;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--muted);
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover {
            color: var(--text);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 7px 11px;
            background: #fff;
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }

        .status-dot {
            width: 9px;
            height: 9px;
            border-radius: 999px;
            background: #9ca3af;
        }

        .status-dot.active {
            background: #10b981;
        }

        .status-dot.pending {
            background: #f59e0b;
        }

        .hero {
            margin-bottom: 24px;
        }

        .hero h1 {
            margin: 0 0 8px;
            font-size: clamp(28px, 5vw, 42px);
            line-height: 1.08;
            letter-spacing: -0.04em;
        }

        .hero p {
            margin: 0;
            max-width: 720px;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.7;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 22px;
            box-shadow: 0 10px 30px rgba(17, 24, 39, 0.05);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .card-body {
            padding: 24px;
        }

        .section-title {
            margin: 0 0 6px;
            font-size: 20px;
            letter-spacing: -0.02em;
        }

        .section-subtitle {
            margin: 0;
            color: var(--muted);
            line-height: 1.65;
        }

        .alert {
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 18px;
            border: 1px solid;
            line-height: 1.55;
        }

        .alert-success {
            background: var(--success-bg);
            border-color: var(--success-border);
            color: var(--success-text);
        }

        .alert-error {
            background: var(--error-bg);
            border-color: var(--error-border);
            color: var(--error-text);
        }

        .alert-warning {
            background: var(--warning-bg);
            border-color: var(--warning-border);
            color: var(--warning-text);
        }

        .button-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            border-radius: 12px;
            border: 1px solid transparent;
            padding: 10px 16px;
            text-decoration: none;
            cursor: pointer;
            font-weight: 800;
            transition:
                transform .12s ease,
                background .12s ease,
                border-color .12s ease;
        }

        .btn:active {
            transform: scale(.98);
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        .btn-secondary {
            background: white;
            color: var(--text);
            border-color: var(--border);
        }

        .btn-secondary:hover {
            background: #f9fafb;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background: var(--danger-hover);
        }

        .setup-grid {
            display: grid;
            grid-template-columns: minmax(0, 320px) minmax(0, 1fr);
            gap: 28px;
            margin-top: 24px;
            align-items: start;
        }

        .qr-wrap {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-wrap img {
            width: 100%;
            max-width: 280px;
            aspect-ratio: 1;
            object-fit: contain;
            display: block;
        }

        .steps {
            margin: 0 0 20px;
            padding-left: 20px;
            color: var(--muted);
            line-height: 1.7;
        }

        .steps li + li {
            margin-top: 6px;
        }

        .label {
            display: block;
            margin-bottom: 7px;
            font-weight: 800;
        }

        .input {
            width: 100%;
            min-height: 48px;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 10px 13px;
            background: #fff;
            color: var(--text);
            outline: none;
        }

        .input:focus {
            border-color: #9ca3af;
            box-shadow: 0 0 0 4px rgba(17, 24, 39, .06);
        }

        .code-input {
            letter-spacing: .28em;
            text-align: center;
            font-size: 24px;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
        }

        .field-error {
            color: var(--error-text);
            font-size: 14px;
            margin-top: 7px;
        }

        .secret-box {
            margin-top: 14px;
            padding: 13px 14px;
            background: #f9fafb;
            border: 1px dashed var(--border);
            border-radius: 12px;
            overflow-wrap: anywhere;
        }

        .secret-label {
            display: block;
            color: var(--muted);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-bottom: 6px;
        }

        .secret-value {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 14px;
        }

        .recovery-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-top: 16px;
        }

        .recovery-code {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 11px 12px;
            background: #f9fafb;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-weight: 800;
            text-align: center;
            overflow-wrap: anywhere;
        }

        .password-form {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 10px;
            margin-top: 16px;
        }

        .muted {
            color: var(--muted);
        }

        .small {
            font-size: 13px;
            line-height: 1.55;
        }

        .danger-zone {
            border-color: #fecaca;
        }

        .footer-note {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.55;
            text-align: center;
            margin-top: 18px;
        }

        @media (max-width: 720px) {
            .page {
                width: min(100% - 22px, 920px);
                padding-top: 18px;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .card-body {
                padding: 18px;
            }

            .setup-grid {
                grid-template-columns: 1fr;
            }

            .recovery-grid {
                grid-template-columns: 1fr;
            }

            .password-form {
                grid-template-columns: 1fr;
            }

            .password-form .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
@php
    $twoFactorEnabled = $user->two_factor_confirmed_at !== null;

    $setupPending =
        is_string($pendingSecret ?? null) &&
        ($pendingSecret ?? '') !== '' &&
        ! $twoFactorEnabled;
@endphp

<main class="page">

    <div class="topbar">
        <a href="{{ route('security.index') }}" class="back-link">
            <span aria-hidden="true">←</span>
            <span>Terug naar accountbeveiliging</span>
        </a>

        @if ($twoFactorEnabled)
            <span class="badge">
                <span class="status-dot active"></span>
                Authenticator actief
            </span>
        @elseif ($setupPending)
            <span class="badge">
                <span class="status-dot pending"></span>
                Instellen bezig
            </span>
        @else
            <span class="badge">
                <span class="status-dot"></span>
                Niet ingesteld
            </span>
        @endif
    </div>

    <section class="hero">
        <h1>Authenticator-beveiliging</h1>

        <p>
            Beveilig je Mashal Studio-account met een Authenticator-app.
            Na het scannen van de QR-code genereert je app steeds een
            nieuwe 6-cijferige verificatiecode.
        </p>
    </section>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error">
            <strong>Controleer de ingevoerde gegevens.</strong>

            <ul style="margin: 8px 0 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    @if (is_array($newRecoveryCodes ?? null) && count($newRecoveryCodes) > 0)
        <section class="card">
            <div class="card-body">

                <div class="alert alert-warning">
                    <strong>Bewaar deze herstelcodes nu.</strong><br>
                    Ze worden na het verlaten of vernieuwen van deze pagina
                    niet opnieuw in deze vorm getoond.
                </div>

                <h2 class="section-title">
                    Herstelcodes
                </h2>

                <p class="section-subtitle">
                    Gebruik één herstelcode wanneer je tijdelijk geen toegang
                    hebt tot je Authenticator-app.
                </p>

                <div class="recovery-grid">
                    @foreach ($newRecoveryCodes as $recoveryCode)
                        <div class="recovery-code">
                            {{ $recoveryCode }}
                        </div>
                    @endforeach
                </div>

                <p class="muted small" style="margin-top: 16px;">
                    Bewaar deze codes op een veilige plek, bijvoorbeeld in een
                    wachtwoordmanager.
                </p>

            </div>
        </section>
    @endif


    @if (! $twoFactorEnabled)

        @if (! $setupPending)

            <section class="card">
                <div class="card-body">

                    <h2 class="section-title">
                        Authenticator instellen
                    </h2>

                    <p class="section-subtitle">
                        Gebruik bijvoorbeeld Google Authenticator,
                        Microsoft Authenticator, 1Password of een andere
                        TOTP-compatibele app.
                    </p>

                    <div class="alert alert-warning" style="margin-top: 20px;">
                        Authenticator wordt pas actief nadat je de eerste
                        6-cijferige code succesvol hebt bevestigd.
                    </div>

                    <form
                        method="POST"
                        action="{{ route('two-factor.enable') }}"
                    >
                        @csrf

                        <div class="button-row">
                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Authenticator instellen
                            </button>
                        </div>
                    </form>

                </div>
            </section>

        @else

            <section class="card">
                <div class="card-body">

                    <h2 class="section-title">
                        Scan de QR-code
                    </h2>

                    <p class="section-subtitle">
                        Open je Authenticator-app en voeg een nieuw account toe.
                    </p>

                    <div class="setup-grid">

                        <div>

                            @if (! empty($qrCode))
                                <div class="qr-wrap">
                                    <img
                                        src="{{ $qrCode }}"
                                        alt="QR-code voor Mashal Studio Authenticator"
                                    >
                                </div>
                            @else
                                <div class="alert alert-error">
                                    De QR-code kon niet worden geladen.
                                    Annuleer de setup en probeer opnieuw.
                                </div>
                            @endif

                            @if (! empty($pendingSecret))
                                <div class="secret-box">
                                    <span class="secret-label">
                                        Handmatige sleutel
                                    </span>

                                    <span class="secret-value">
                                        {{ $pendingSecret }}
                                    </span>
                                </div>

                                <p class="muted small">
                                    Werkt scannen niet? Voeg Mashal Studio
                                    handmatig toe met deze sleutel.
                                </p>
                            @endif

                        </div>

                        <div>

                            <ol class="steps">
                                <li>Open je Authenticator-app.</li>
                                <li>Kies voor account toevoegen of QR-code scannen.</li>
                                <li>Scan de QR-code.</li>
                                <li>Vul de actuele 6-cijferige code hieronder in.</li>
                            </ol>

                            <form
                                method="POST"
                                action="{{ route('two-factor.confirm') }}"
                                autocomplete="off"
                            >
                                @csrf

                                <label
                                    class="label"
                                    for="code"
                                >
                                    6-cijferige verificatiecode
                                </label>

                                <input
                                    id="code"
                                    name="code"
                                    type="text"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    maxlength="6"
                                    minlength="6"
                                    autocomplete="one-time-code"
                                    class="input code-input"
                                    value="{{ old('code') }}"
                                    placeholder="000000"
                                    required
                                    autofocus
                                >

                                @error('code')
                                    <div class="field-error">
                                        {{ $message }}
                                    </div>
                                @enderror

                                <div class="button-row">
                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                    >
                                        Code bevestigen
                                    </button>
                                </div>
                            </form>

                            <form
                                method="POST"
                                action="{{ route('two-factor.cancel') }}"
                                style="margin-top: 10px;"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="btn btn-secondary"
                                >
                                    Instellen annuleren
                                </button>
                            </form>

                        </div>

                    </div>

                </div>
            </section>

        @endif

    @else

        <section class="card">
            <div class="card-body">

                <h2 class="section-title">
                    Authenticator is ingeschakeld
                </h2>

                <p class="section-subtitle">
                    Je Mashal Studio-account heeft nu een gekoppelde
                    Authenticator-sleutel.
                </p>

                <div class="alert alert-success" style="margin-top: 20px;">
                    <strong>Actief sinds:</strong>
                    {{ optional($user->two_factor_confirmed_at)->format('d-m-Y H:i') }}
                </div>

                <p class="muted small">
                    Verwijder Mashal Studio niet uit je Authenticator-app zolang
                    deze beveiliging actief is.
                </p>

            </div>
        </section>


        <section class="card">
            <div class="card-body">

                <h2 class="section-title">
                    Nieuwe herstelcodes
                </h2>

                <p class="section-subtitle">
                    Genereer een nieuwe set wanneer je huidige codes kwijt zijn
                    of mogelijk zijn uitgelekt. De oude set wordt daarna ongeldig.
                </p>

                <form
                    method="POST"
                    action="{{ route('two-factor.recovery-codes') }}"
                    class="password-form"
                >
                    @csrf

                    @if (! empty($user->password))
                        <div>
                            <label
                                for="recovery-password"
                                class="label"
                            >
                                Huidig wachtwoord
                            </label>

                            <input
                                id="recovery-password"
                                name="password"
                                type="password"
                                class="input"
                                autocomplete="current-password"
                                required
                            >
                        </div>
                    @else
                        <div class="muted small">
                            Je account heeft geen lokaal wachtwoord.
                        </div>
                    @endif

                    <button
                        type="submit"
                        class="btn btn-secondary"
                        style="align-self: end;"
                    >
                        Nieuwe codes maken
                    </button>
                </form>

            </div>
        </section>


        <section class="card danger-zone">
            <div class="card-body">

                <h2 class="section-title">
                    Authenticator uitschakelen
                </h2>

                <p class="section-subtitle">
                    Hierdoor worden je Authenticator-sleutel en herstelcodes
                    uit Mashal Studio verwijderd.
                </p>

                <div class="alert alert-warning" style="margin-top: 20px;">
                    Na het uitschakelen beschermt Authenticator je account
                    niet meer.
                </div>

                <form
                    method="POST"
                    action="{{ route('two-factor.disable') }}"
                    class="password-form"
                    onsubmit="return confirm('Weet je zeker dat je Authenticator-beveiliging wilt uitschakelen?');"
                >
                    @csrf
                    @method('DELETE')

                    @if (! empty($user->password))
                        <div>
                            <label
                                for="disable-password"
                                class="label"
                            >
                                Huidig wachtwoord
                            </label>

                            <input
                                id="disable-password"
                                name="password"
                                type="password"
                                class="input"
                                autocomplete="current-password"
                                required
                            >
                        </div>
                    @else
                        <div class="muted small">
                            Je account heeft geen lokaal wachtwoord.
                        </div>
                    @endif

                    <button
                        type="submit"
                        class="btn btn-danger"
                        style="align-self: end;"
                    >
                        Authenticator uitschakelen
                    </button>
                </form>

            </div>
        </section>

    @endif


    <p class="footer-note">
        Mashal Studio gebruikt standaard TOTP-codes.
        Deel je QR-code, geheime sleutel en herstelcodes nooit met anderen.
    </p>

</main>

<script>
    const verificationCodeInput = document.getElementById('code');

    if (verificationCodeInput) {
        verificationCodeInput.addEventListener('input', function () {
            this.value = this.value
                .replace(/\D/g, '')
                .slice(0, 6);
        });
    }
</script>

</body>
</html>
