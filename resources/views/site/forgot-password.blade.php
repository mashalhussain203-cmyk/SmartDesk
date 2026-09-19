@extends('layouts.site-layout')

@section('title', 'Wachtwoord herstellen | Mashal Studio')

@section(
    'meta_description',
    'Vraag veilig een wachtwoord-resetlink aan voor je Mashal Studio-account.'
)

@push('styles')
<style>
    .recovery-page {
        position: relative;
        min-height: calc(100vh - 76px);
        overflow: hidden;
        color: #f7f7f4;
        background:
            radial-gradient(
                circle at 13% 16%,
                rgba(227, 179, 107, .085),
                transparent 27rem
            ),
            radial-gradient(
                circle at 86% 12%,
                rgba(112, 93, 255, .055),
                transparent 30rem
            ),
            linear-gradient(
                180deg,
                #07080b,
                #090b0f 52%,
                #07080b
            );
    }

    .recovery-page::before {
        content: "RECOVERY";
        position: absolute;
        right: -72px;
        top: 25px;
        pointer-events: none;
        user-select: none;
        color: rgba(255, 255, 255, .012);
        font-size: clamp(120px, 17vw, 275px);
        font-weight: 950;
        line-height: .8;
        letter-spacing: -.085em;
    }

    .recovery-stage {
        position: relative;
        z-index: 2;
        min-height: calc(100vh - 76px);
        display: grid;
        grid-template-columns:
            minmax(0, 1.03fr)
            minmax(420px, .97fr);
    }

    /*
    |--------------------------------------------------------------------------
    | Visual panel
    |--------------------------------------------------------------------------
    */

    .recovery-visual {
        position: relative;
        min-height: 100%;
        overflow: hidden;
        isolation: isolate;
        display: flex;
        align-items: flex-end;
        padding: clamp(38px, 5vw, 76px);
        border-right: 1px solid rgba(255, 255, 255, .055);
        background:
            radial-gradient(
                circle at 22% 20%,
                rgba(227, 179, 107, .12),
                transparent 21rem
            ),
            radial-gradient(
                circle at 82% 26%,
                rgba(102, 92, 255, .08),
                transparent 24rem
            ),
            linear-gradient(
                145deg,
                #0d1015,
                #090b0f 58%,
                #07080b
            );
    }

    .recovery-visual::before {
        content: "";
        position: absolute;
        z-index: -2;
        inset: 0;
        opacity: .13;
        background-image:
            linear-gradient(
                rgba(255, 255, 255, .025) 1px,
                transparent 1px
            ),
            linear-gradient(
                90deg,
                rgba(255, 255, 255, .025) 1px,
                transparent 1px
            );
        background-size: 68px 68px;
        mask-image:
            linear-gradient(
                to bottom,
                #000,
                transparent 88%
            );
    }

    .recovery-visual::after {
        content: "";
        position: absolute;
        z-index: -1;
        width: min(58vw, 640px);
        aspect-ratio: 1 / 1;
        right: -18%;
        top: 8%;
        border: 1px solid rgba(227, 179, 107, .08);
        border-radius: 50%;
        box-shadow:
            0 0 0 70px rgba(227, 179, 107, .015),
            0 0 0 140px rgba(227, 179, 107, .008);
        animation:
            recoveryOrbit
            14s
            ease-in-out
            infinite
            alternate;
    }

    @keyframes recoveryOrbit {
        from {
            transform:
                translate3d(0, 0, 0)
                scale(1);
        }

        to {
            transform:
                translate3d(-22px, 18px, 0)
                scale(1.05);
        }
    }

    .recovery-visual-content {
        max-width: 720px;
        animation:
            recoveryFadeUp
            .72s
            ease
            both;
    }

    .recovery-kicker {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
        color: #efc985;
        font-size: 9px;
        font-weight: 950;
        letter-spacing: .22em;
        text-transform: uppercase;
    }

    .recovery-kicker::before {
        content: "";
        width: 34px;
        height: 1px;
        background:
            linear-gradient(
                90deg,
                #d7a45f,
                transparent
            );
    }

    .recovery-visual h2 {
        max-width: 720px;
        margin: 0;
        color: #ffffff;
        font-size: clamp(50px, 5.7vw, 84px);
        line-height: .94;
        letter-spacing: -.066em;
        font-weight: 950;
        text-wrap: balance;
    }

    .recovery-visual h2 span {
        color: #f0c983;
    }

    .recovery-visual p {
        max-width: 590px;
        margin: 24px 0 0;
        color: rgba(255, 255, 255, .66);
        font-size: 13px;
        line-height: 1.85;
    }

    .recovery-trust-row {
        margin-top: 30px;
        display: flex;
        gap: 9px;
        flex-wrap: wrap;
    }

    .recovery-trust {
        min-height: 32px;
        padding: 0 11px;
        display: inline-flex;
        align-items: center;
        border: 1px solid rgba(255, 255, 255, .10);
        border-radius: 999px;
        color: rgba(255, 255, 255, .72);
        background: rgba(255, 255, 255, .035);
        backdrop-filter: blur(12px);
        font-size: 8px;
        font-weight: 850;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .recovery-proof {
        margin-top: 34px;
        display: grid;
        grid-template-columns:
            repeat(3, minmax(0, 1fr));
        gap: 9px;
        max-width: 620px;
    }

    .recovery-proof-card {
        padding: 14px;
        border: 1px solid rgba(255, 255, 255, .06);
        border-radius: 14px;
        background: rgba(255, 255, 255, .018);
    }

    .recovery-proof-card small {
        display: block;
        color: #5f6670;
        font-size: 7px;
        font-weight: 950;
        letter-spacing: .11em;
        text-transform: uppercase;
    }

    .recovery-proof-card strong {
        display: block;
        margin-top: 6px;
        color: #cfd3d8;
        font-size: 10px;
    }

    /*
    |--------------------------------------------------------------------------
    | Form panel
    |--------------------------------------------------------------------------
    */

    .recovery-panel {
        position: relative;
        min-height: 100%;
        padding: 56px 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        background:
            radial-gradient(
                circle at 82% 14%,
                rgba(227, 179, 107, .075),
                transparent 18rem
            ),
            linear-gradient(
                180deg,
                #0b0d10,
                #08090b
            );
    }

    .recovery-panel::before {
        content: "M";
        position: absolute;
        right: -28px;
        top: 10%;
        pointer-events: none;
        user-select: none;
        color: rgba(255, 255, 255, .015);
        font-size: 300px;
        font-weight: 950;
        line-height: .8;
    }

    .recovery-shell {
        position: relative;
        z-index: 2;
        width: 100%;
        max-width: 490px;
        animation:
            recoveryFadeUp
            .72s
            .07s
            ease
            both;
    }

    @keyframes recoveryFadeUp {
        from {
            opacity: 0;
            transform: translateY(24px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .recovery-brand {
        margin-bottom: 34px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .recovery-brand-mark {
        width: 44px;
        height: 44px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(227, 179, 107, .18);
        border-radius: 14px;
        color: #171009;
        background:
            linear-gradient(
                145deg,
                #f0ca86,
                #b67e3d
            );
        box-shadow:
            0 14px 34px rgba(215, 164, 95, .20);
        font-size: 18px;
        font-weight: 950;
    }

    .recovery-brand-copy strong {
        display: block;
        color: #ffffff;
        font-size: 18px;
        line-height: 1;
        letter-spacing: -.03em;
    }

    .recovery-brand-copy span {
        display: block;
        margin-top: 5px;
        color: #666d76;
        font-size: 7px;
        font-weight: 900;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .recovery-section-kicker {
        display: inline-block;
        margin-bottom: 10px;
        color: #b9894d;
        font-size: 9px;
        font-weight: 950;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .recovery-title {
        margin: 0;
        color: #ffffff;
        font-size: clamp(38px, 4vw, 54px);
        line-height: .98;
        letter-spacing: -.055em;
        font-weight: 950;
        text-wrap: balance;
    }

    .recovery-subtitle {
        margin: 14px 0 28px;
        color: #7f858c;
        font-size: 12px;
        line-height: 1.8;
    }

    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    */

    .recovery-message {
        margin-bottom: 18px;
        padding: 13px 15px;
        border-radius: 13px;
        font-size: 10px;
        line-height: 1.65;
        backdrop-filter: blur(12px);
    }

    .recovery-message.success {
        border: 1px solid rgba(91, 214, 149, .18);
        color: #a9efc8;
        background: rgba(91, 214, 149, .06);
    }

    .recovery-message.error {
        border: 1px solid rgba(241, 123, 123, .18);
        color: #ffc1c1;
        background: rgba(241, 123, 123, .06);
    }

    .recovery-message strong {
        display: block;
        margin-bottom: 3px;
    }

    .recovery-message ul {
        margin: 7px 0 0 16px;
        padding: 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Form
    |--------------------------------------------------------------------------
    */

    .recovery-form {
        margin: 0;
    }

    .recovery-field {
        margin-bottom: 18px;
    }

    .recovery-label {
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    .recovery-label label {
        margin: 0;
        color: #b9b9b6;
        font-size: 8px;
        font-weight: 950;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .recovery-field-error {
        color: #f3a1a1;
        font-size: 9px;
        font-weight: 800;
    }

    .recovery-input-wrap {
        position: relative;
    }

    .recovery-input {
        width: 100%;
        min-height: 56px;
        padding: 0 48px 0 16px;
        border: 1px solid rgba(255, 255, 255, .09);
        border-radius: 15px;
        outline: none;
        color: #ffffff;
        background: rgba(255, 255, 255, .032);
        font-size: 13px;
        transition:
            border-color .2s ease,
            background .2s ease,
            box-shadow .2s ease;
    }

    .recovery-input::placeholder {
        color: #565c63;
    }

    .recovery-input:focus {
        border-color: rgba(215, 164, 95, .42);
        background: rgba(215, 164, 95, .032);
        box-shadow:
            0 0 0 4px rgba(215, 164, 95, .055);
    }

    .recovery-input[aria-invalid="true"] {
        border-color: rgba(241, 123, 123, .36);
    }

    .recovery-input-icon {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #747a81;
        font-size: 13px;
        font-weight: 900;
        pointer-events: none;
    }

    .recovery-hint {
        margin-top: 7px;
        color: #575e67;
        font-size: 8px;
        line-height: 1.55;
    }

    .recovery-info {
        margin: 4px 0 22px;
        padding: 15px;
        display: flex;
        align-items: flex-start;
        gap: 11px;
        border: 1px solid rgba(215, 164, 95, .11);
        border-radius: 14px;
        background:
            linear-gradient(
                145deg,
                rgba(215, 164, 95, .05),
                rgba(215, 164, 95, .015)
            );
    }

    .recovery-info-icon {
        width: 28px;
        height: 28px;
        flex: 0 0 28px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(215, 164, 95, .16);
        border-radius: 50%;
        color: #dfb36d;
        font-size: 10px;
        font-weight: 950;
    }

    .recovery-info strong {
        display: block;
        margin-bottom: 4px;
        color: #d9d7d2;
        font-size: 10px;
    }

    .recovery-info p {
        margin: 0;
        color: #747a81;
        font-size: 9px;
        line-height: 1.7;
    }

    .recovery-submit {
        position: relative;
        width: 100%;
        min-height: 56px;
        overflow: hidden;
        border: 0;
        border-radius: 999px;
        color: #14100b;
        background:
            linear-gradient(
                135deg,
                #f1cc8b,
                #ca914c
            );
        box-shadow:
            0 18px 44px rgba(215, 164, 95, .20);
        font-size: 11px;
        font-weight: 950;
        letter-spacing: .03em;
        cursor: pointer;
        transition:
            transform .22s ease,
            box-shadow .22s ease,
            opacity .22s ease;
    }

    .recovery-submit::after {
        content: "→";
        position: absolute;
        right: 22px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 16px;
        transition: transform .22s ease;
    }

    .recovery-submit:hover {
        transform: translateY(-2px);
        box-shadow:
            0 25px 58px rgba(215, 164, 95, .29);
    }

    .recovery-submit:hover::after {
        transform: translate(4px, -50%);
    }

    .recovery-submit:disabled {
        opacity: .62;
        cursor: wait;
        transform: none;
    }

    /*
    |--------------------------------------------------------------------------
    | Security and navigation
    |--------------------------------------------------------------------------
    */

    .recovery-security-card {
        margin-top: 26px;
        padding: 17px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        border: 1px solid rgba(255, 255, 255, .07);
        border-radius: 16px;
        background: rgba(255, 255, 255, .022);
    }

    .recovery-security-icon {
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(215, 164, 95, .14);
        border-radius: 11px;
        color: #dfb36d;
        background: rgba(215, 164, 95, .045);
        font-size: 12px;
        font-weight: 950;
    }

    .recovery-security-card h3 {
        margin: 0 0 5px;
        color: #dcdad5;
        font-size: 11px;
    }

    .recovery-security-card p {
        margin: 0;
        color: #666c73;
        font-size: 9px;
        line-height: 1.7;
    }

    .recovery-back {
        margin-top: 22px;
        text-align: center;
    }

    .recovery-back a {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #9d7950;
        text-decoration: none;
        font-size: 9px;
        font-weight: 850;
        letter-spacing: .04em;
        transition:
            color .2s ease,
            transform .2s ease;
    }

    .recovery-back a:hover {
        color: #efc985;
        transform: translateX(-2px);
    }

    .recovery-help {
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px solid rgba(255, 255, 255, .05);
        color: #555d66;
        font-size: 8px;
        line-height: 1.65;
        text-align: center;
    }

    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1020px) {
        .recovery-stage {
            grid-template-columns: 1fr;
        }

        .recovery-visual {
            min-height: 520px;
            border-right: 0;
            border-bottom:
                1px solid rgba(255, 255, 255, .055);
        }

        .recovery-panel {
            min-height: auto;
            padding: 72px 32px;
        }
    }

    @media (max-width: 640px) {
        .recovery-page,
        .recovery-stage {
            min-height: auto;
        }

        .recovery-visual {
            min-height: 430px;
            padding: 38px 20px;
        }

        .recovery-visual h2 {
            font-size: clamp(44px, 14vw, 62px);
        }

        .recovery-visual p {
            font-size: 11px;
        }

        .recovery-proof {
            grid-template-columns: 1fr;
        }

        .recovery-panel {
            padding: 52px 18px 64px;
        }

        .recovery-brand {
            margin-bottom: 30px;
        }

        .recovery-title {
            font-size: clamp(38px, 11vw, 50px);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .01ms !important;
        }
    }
</style>
@endpush

@section('content')
<section class="recovery-page">
    <div class="recovery-stage">

        <aside
            class="recovery-visual"
            aria-label="Account recovery informatie"
        >
            <div class="recovery-visual-content">
                <span class="recovery-kicker">
                    Mashal Studio security
                </span>

                <h2>
                    Herstel toegang.
                    Behoud
                    <span>controle.</span>
                </h2>

                <p>
                    Ben je je wachtwoord vergeten? Vraag veilig een
                    resetlink aan via het e-mailadres van je account.
                    Je afbeeldingsprojecten en opgeslagen versies blijven
                    gewoon aan hetzelfde account gekoppeld.
                </p>

                <div
                    class="recovery-trust-row"
                    aria-label="Beveiligingskenmerken"
                >
                    <span class="recovery-trust">
                        Secure reset
                    </span>

                    <span class="recovery-trust">
                        Private account
                    </span>

                    <span class="recovery-trust">
                        E-mail verification
                    </span>
                </div>

                <div class="recovery-proof">
                    <div class="recovery-proof-card">
                        <small>
                            Stap 01
                        </small>

                        <strong>
                            Vul je e-mail in
                        </strong>
                    </div>

                    <div class="recovery-proof-card">
                        <small>
                            Stap 02
                        </small>

                        <strong>
                            Open de resetlink
                        </strong>
                    </div>

                    <div class="recovery-proof-card">
                        <small>
                            Stap 03
                        </small>

                        <strong>
                            Kies nieuw wachtwoord
                        </strong>
                    </div>
                </div>
            </div>
        </aside>

        <div class="recovery-panel">
            <div class="recovery-shell">

                <div class="recovery-brand">
                    <div
                        class="recovery-brand-mark"
                        aria-hidden="true"
                    >
                        M
                    </div>

                    <div class="recovery-brand-copy">
                        <strong>
                            Mashal Studio
                        </strong>

                        <span>
                            Account recovery
                        </span>
                    </div>
                </div>

                <span class="recovery-section-kicker">
                    Password recovery
                </span>

                <h1 class="recovery-title">
                    Wachtwoord herstellen
                </h1>

                <p class="recovery-subtitle">
                    Vul het e-mailadres van je Mashal Studio-account in.
                    Als het adres bij een account hoort, kan de bestaande
                    herstelprocedure een resetlink versturen.
                </p>

                @if (session('success'))
                    <div
                        class="recovery-message success"
                        role="status"
                    >
                        <strong>
                            Gelukt
                        </strong>

                        {{ session('success') }}
                    </div>
                @endif

                @if (session('status'))
                    <div
                        class="recovery-message success"
                        role="status"
                    >
                        <strong>
                            Controleer je inbox
                        </strong>

                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div
                        class="recovery-message error"
                        role="alert"
                    >
                        <strong>
                            Er ging iets mis
                        </strong>

                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div
                        class="recovery-message error"
                        role="alert"
                    >
                        <strong>
                            Controleer je gegevens
                        </strong>

                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form
                    class="recovery-form"
                    id="passwordRecoveryForm"
                    method="POST"
                    action="{{ route('password.email') }}"
                >
                    @csrf

                    <div class="recovery-field">
                        <div class="recovery-label">
                            <label for="email">
                                E-mailadres
                            </label>

                            @error('email')
                                <span class="recovery-field-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="recovery-input-wrap">
                            <input
                                class="recovery-input"
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="naam@example.com"
                                autocomplete="email"
                                inputmode="email"
                                autocapitalize="none"
                                spellcheck="false"
                                aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                                aria-describedby="emailHint"
                                required
                                autofocus
                            >

                            <span
                                class="recovery-input-icon"
                                aria-hidden="true"
                            >
                                @
                            </span>
                        </div>

                        <div
                            class="recovery-hint"
                            id="emailHint"
                        >
                            Gebruik hetzelfde e-mailadres waarmee je je account hebt aangemaakt.
                        </div>
                    </div>

                    <div class="recovery-info">
                        <span
                            class="recovery-info-icon"
                            aria-hidden="true"
                        >
                            i
                        </span>

                        <div>
                            <strong>
                                Hoe werkt het?
                            </strong>

                            <p>
                                Na het verzenden volg je de resetlink uit de
                                herstelmail. Deel die link nooit met iemand anders.
                            </p>
                        </div>
                    </div>

                    <button
                        class="recovery-submit"
                        id="passwordRecoverySubmit"
                        type="submit"
                    >
                        Verstuur resetlink
                    </button>
                </form>

                <div class="recovery-security-card">
                    <div
                        class="recovery-security-icon"
                        aria-hidden="true"
                    >
                        ✓
                    </div>

                    <div>
                        <h3>
                            Jouw veiligheid staat voorop
                        </h3>

                        <p>
                            Mashal Studio vraagt je nooit om je wachtwoord
                            of een persoonlijke resetlink met iemand te delen
                            via chat, telefoon of social media.
                        </p>
                    </div>
                </div>

                <div class="recovery-back">
                    <a href="{{ route('login') }}">
                        <span aria-hidden="true">
                            ←
                        </span>

                        Terug naar inloggen
                    </a>
                </div>

                <div class="recovery-help">
                    Heb je nog toegang tot je account? Gebruik dan je
                    accountinstellingen om je wachtwoord direct te wijzigen.
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form =
        document.getElementById(
            'passwordRecoveryForm'
        );

    const submit =
        document.getElementById(
            'passwordRecoverySubmit'
        );

    const email =
        document.getElementById(
            'email'
        );

    form?.addEventListener(
        'submit',
        function () {
            if (!submit) {
                return;
            }

            submit.disabled = true;
            submit.textContent =
                'Resetlink versturen…';
        }
    );

    email?.addEventListener(
        'input',
        function () {
            email.removeAttribute(
                'aria-invalid'
            );
        }
    );

    window.addEventListener(
        'pageshow',
        function () {
            if (!submit) {
                return;
            }

            submit.disabled = false;
            submit.textContent =
                'Verstuur resetlink';
        }
    );
});
</script>
@endpush
