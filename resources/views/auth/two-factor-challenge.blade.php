<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Beveiligingscontrole | Mashal Studio</title>

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

        button,
        input {
            font: inherit;
        }

        .page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .shell {
            width: min(100%, 520px);
        }

        .brand {
            text-align: center;
            margin-bottom: 18px;
        }

        .brand-name {
            font-weight: 900;
            font-size: 20px;
            letter-spacing: -0.03em;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: 0 20px 55px rgba(17, 24, 39, 0.08);
            overflow: hidden;
        }

        .card-body {
            padding: 28px;
        }

        .icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 18px;
            display: grid;
            place-items: center;
            border-radius: 18px;
            background: #f3f4f6;
            font-size: 26px;
        }

        h1 {
            margin: 0;
            text-align: center;
            font-size: 28px;
            letter-spacing: -0.04em;
        }

        .intro {
            margin: 10px auto 22px;
            max-width: 420px;
            text-align: center;
            color: var(--muted);
            line-height: 1.65;
        }

        .account {
            margin-bottom: 20px;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: #f9fafb;
            text-align: center;
        }

        .account-name {
            font-weight: 800;
        }

        .account-email {
            margin-top: 2px;
            color: var(--muted);
            font-size: 14px;
            overflow-wrap: anywhere;
        }

        .alert {
            border-radius: 14px;
            padding: 13px 15px;
            margin-bottom: 18px;
            border: 1px solid;
            line-height: 1.55;
        }

        .alert-success {
            background: var(--success-bg);
            color: var(--success-text);
            border-color: var(--success-border);
        }

        .alert-error {
            background: var(--error-bg);
            color: var(--error-text);
            border-color: var(--error-border);
        }

        .alert-warning {
            background: var(--warning-bg);
            color: var(--warning-text);
            border-color: var(--warning-border);
        }

        .label {
            display: block;
            margin-bottom: 7px;
            font-weight: 800;
        }

        .input {
            width: 100%;
            min-height: 50px;
            border: 1px solid var(--border);
            border-radius: 13px;
            padding: 11px 13px;
            outline: none;
            background: white;
            color: var(--text);
        }

        .input:focus {
            border-color: #9ca3af;
            box-shadow: 0 0 0 4px rgba(17, 24, 39, 0.06);
        }

        .code-input {
            text-align: center;
            font-size: 26px;
            font-weight: 900;
            letter-spacing: .3em;
            font-variant-numeric: tabular-nums;
        }

        .field-error {
            margin-top: 7px;
            color: var(--error-text);
            font-size: 14px;
        }

        .btn {
            width: 100%;
            min-height: 48px;
            border: 1px solid transparent;
            border-radius: 13px;
            padding: 11px 16px;
            cursor: pointer;
            font-weight: 850;
            transition:
                background .12s ease,
                transform .12s ease;
        }

        .btn:active {
            transform: scale(.99);
        }

        .btn-primary {
            margin-top: 14px;
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

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 24px 0;
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }

        .divider::before,
        .divider::after {
            content: "";
            height: 1px;
            flex: 1;
            background: var(--border);
        }

        details {
            border: 1px solid var(--border);
            border-radius: 15px;
            overflow: hidden;
            background: #fff;
        }

        summary {
            cursor: pointer;
            padding: 15px 16px;
            font-weight: 800;
            list-style: none;
            user-select: none;
        }

        summary::-webkit-details-marker {
            display: none;
        }

        .details-body {
            border-top: 1px solid var(--border);
            padding: 16px;
        }

        .small {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.55;
        }

        .cancel-form {
            margin-top: 14px;
        }

        .footer {
            margin-top: 16px;
            text-align: center;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.55;
        }

        @media (max-width: 560px) {
            .page {
                padding: 14px;
                align-items: start;
                padding-top: 28px;
            }

            .card-body {
                padding: 20px;
            }

            h1 {
                font-size: 25px;
            }
        }
    </style>
</head>

<body>

<main class="page">

    <div class="shell">

        <div class="brand">
            <div class="brand-name">
                Mashal Studio
            </div>
        </div>

        <section class="card">

            <div class="card-body">

                <div class="icon" aria-hidden="true">
                    🔐
                </div>

                <h1>
                    Beveiligingscontrole
                </h1>

                <p class="intro">
                    Je account is beschermd met Authenticator-verificatie.
                    Voer de actuele 6-cijferige code uit je Authenticator-app in
                    om je login af te ronden.
                </p>

                <div class="account">
                    <div class="account-name">
                        {{ $user->name }}
                    </div>

                    <div class="account-email">
                        {{ $user->email }}
                    </div>
                </div>


                {{-- ============================================================
                   STATUSMELDINGEN
                   ============================================================ --}}

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
                        <strong>
                            Controleer de ingevoerde gegevens.
                        </strong>

                        <ul style="margin: 8px 0 0; padding-left: 20px;">
                            @foreach ($errors->all() as $error)
                                <li>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif


                {{-- ============================================================
                   AUTHENTICATOR CODE
                   ============================================================ --}}

                <form
                    method="POST"
                    action="{{ route('two-factor.challenge.verify') }}"
                    autocomplete="off"
                >
                    @csrf

                    <label
                        for="code"
                        class="label"
                    >
                        6-cijferige Authenticator-code
                    </label>

                    <input
                        id="code"
                        name="code"
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        minlength="6"
                        maxlength="6"
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

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Login bevestigen
                    </button>
                </form>


                <div class="divider">
                    of
                </div>


                {{-- ============================================================
                   RECOVERY CODE
                   ============================================================ --}}

                <details
                    @if ($errors->has('recovery_code'))
                        open
                    @endif
                >
                    <summary>
                        Gebruik een herstelcode
                    </summary>

                    <div class="details-body">

                        <p class="small" style="margin-top: 0;">
                            Heb je tijdelijk geen toegang tot je Authenticator-app?
                            Gebruik dan één van je opgeslagen herstelcodes.
                        </p>

                        <form
                            method="POST"
                            action="{{ route('two-factor.challenge.recovery') }}"
                            autocomplete="off"
                        >
                            @csrf

                            <label
                                for="recovery_code"
                                class="label"
                            >
                                Herstelcode
                            </label>

                            <input
                                id="recovery_code"
                                name="recovery_code"
                                type="text"
                                maxlength="100"
                                autocomplete="off"
                                class="input"
                                value="{{ old('recovery_code') }}"
                                placeholder="XXXXXXXX-XXXXXXXX"
                                spellcheck="false"
                            >

                            @error('recovery_code')
                                <div class="field-error">
                                    {{ $message }}
                                </div>
                            @enderror

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Inloggen met herstelcode
                            </button>
                        </form>

                        <p class="small" style="margin-bottom: 0;">
                            Een herstelcode kan maar één keer worden gebruikt.
                        </p>

                    </div>
                </details>


                {{-- ============================================================
                   LOGIN ANNULEREN
                   ============================================================ --}}

                <form
                    method="POST"
                    action="{{ route('two-factor.challenge.cancel') }}"
                    class="cancel-form"
                >
                    @csrf

                    <button
                        type="submit"
                        class="btn btn-secondary"
                    >
                        Login annuleren
                    </button>
                </form>

            </div>

        </section>

        <div class="footer">
            Deel je Authenticator-code of herstelcodes nooit met anderen.
            Mashal Studio zal je hier nooit per e-mail of chat om vragen.
        </div>

    </div>

</main>

<script>
    /*
    |--------------------------------------------------------------------------
    | Authenticator-code beperken tot cijfers
    |--------------------------------------------------------------------------
    */

    const codeInput = document.getElementById('code');

    if (codeInput) {
        codeInput.addEventListener('input', function () {
            this.value = this.value
                .replace(/\D/g, '')
                .slice(0, 6);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Recovery code normaliseren
    |--------------------------------------------------------------------------
    */

    const recoveryInput = document.getElementById('recovery_code');

    if (recoveryInput) {
        recoveryInput.addEventListener('input', function () {
            this.value = this.value
                .toUpperCase()
                .replace(/\s+/g, '');
        });
    }
</script>

</body>
</html>
