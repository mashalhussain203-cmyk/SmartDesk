@extends('layouts.site-layout')

@section('title', 'Mashal | Nieuw wachtwoord')

@push('styles')
<style>
    .new-password-page {
        position: relative;
        min-height: calc(100vh - 78px);
        overflow: hidden;
        background: #08090b;
    }

    .new-password-stage {
        min-height: calc(100vh - 78px);
        display: grid;
        grid-template-columns: minmax(0, 1.02fr) minmax(470px, .98fr);
    }

    /* ========================================================= */
    /* LEFT / CINEMATIC PANEL                                    */
    /* ========================================================= */

    .new-password-visual {
        position: relative;
        min-height: 100%;
        overflow: hidden;
        isolation: isolate;
        display: flex;
        align-items: flex-end;
        padding: clamp(36px, 5vw, 74px);
        background: #0a0c0f;
    }

    .new-password-visual::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -3;
        background:
            linear-gradient(
                180deg,
                rgba(4,5,7,.10),
                rgba(4,5,7,.22) 42%,
                rgba(4,5,7,.94) 100%
            ),
            linear-gradient(
                90deg,
                rgba(4,5,7,.38),
                transparent 58%
            ),
            url('https://images.unsplash.com/photo-1504215680853-026ed2a45def?auto=format&fit=crop&w=1900&q=90')
            center / cover no-repeat;
        transform: scale(1.03);
        animation: newPasswordZoom 18s ease-in-out infinite alternate;
    }

    .new-password-visual::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -2;
        background:
            radial-gradient(
                circle at 78% 20%,
                rgba(215,164,95,.18),
                transparent 20rem
            ),
            linear-gradient(
                180deg,
                transparent 70%,
                #08090b 100%
            );
        pointer-events: none;
    }

    @keyframes newPasswordZoom {
        from { transform: scale(1.03); }
        to { transform: scale(1.09); }
    }

    .new-password-visual-content {
        max-width: 720px;
        animation: newPasswordFadeUp .85s ease both;
    }

    .new-password-kicker {
        display: inline-flex;
        align-items: center;
        gap: 11px;
        margin-bottom: 20px;
        color: #efc985;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .24em;
        text-transform: uppercase;
    }

    .new-password-kicker::before {
        content: "";
        width: 36px;
        height: 1px;
        background: #d7a45f;
    }

    .new-password-visual h2 {
        max-width: 720px;
        margin: 0;
        color: #ffffff;
        font-size: clamp(50px, 5.7vw, 86px);
        line-height: .92;
        letter-spacing: -.07em;
        font-weight: 950;
    }

    .new-password-visual h2 span {
        color: #f0c983;
    }

    .new-password-visual p {
        max-width: 590px;
        margin: 24px 0 0;
        color: rgba(255,255,255,.69);
        font-size: 14px;
        line-height: 1.9;
    }

    .security-badges {
        margin-top: 34px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        max-width: 690px;
    }

    .security-badge {
        padding: 14px;
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 16px;
        background: rgba(255,255,255,.045);
        backdrop-filter: blur(12px);
    }

    .security-badge small {
        display: block;
        margin-bottom: 5px;
        color: #c59558;
        font-size: 7px;
        font-weight: 900;
        letter-spacing: .13em;
        text-transform: uppercase;
    }

    .security-badge strong {
        display: block;
        color: rgba(255,255,255,.88);
        font-size: 11px;
        line-height: 1.5;
    }

    /* ========================================================= */
    /* RIGHT / FORM PANEL                                        */
    /* ========================================================= */

    .new-password-panel {
        position: relative;
        min-height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 52px 42px;
        background:
            radial-gradient(
                circle at 82% 14%,
                rgba(215,164,95,.08),
                transparent 18rem
            ),
            linear-gradient(
                180deg,
                #0b0d10,
                #08090b
            );
    }

    .new-password-panel::before {
        content: "M";
        position: absolute;
        right: -25px;
        top: 11%;
        color: rgba(255,255,255,.016);
        font-size: 310px;
        font-weight: 950;
        line-height: .8;
        pointer-events: none;
        user-select: none;
    }

    .new-password-shell {
        position: relative;
        z-index: 2;
        width: 100%;
        max-width: 520px;
        animation: newPasswordFadeUp .8s .08s ease both;
    }

    @keyframes newPasswordFadeUp {
        from {
            opacity: 0;
            transform: translateY(28px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .new-password-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 32px;
    }

    .new-password-brand-mark {
        width: 46px;
        height: 46px;
        display: grid;
        place-items: center;
        border-radius: 14px;
        background:
            linear-gradient(
                145deg,
                #f0ca86,
                #b67e3d
            );
        color: #15110c;
        font-size: 20px;
        font-weight: 950;
        box-shadow: 0 14px 34px rgba(215,164,95,.22);
    }

    .new-password-brand-copy strong {
        display: block;
        color: #ffffff;
        font-size: 19px;
        line-height: 1;
        letter-spacing: -.03em;
    }

    .new-password-brand-copy span {
        display: block;
        margin-top: 5px;
        color: #6f757c;
        font-size: 8px;
        font-weight: 850;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .new-password-section-kicker {
        display: inline-block;
        margin-bottom: 10px;
        color: #b9894d;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .new-password-title {
        margin: 0;
        color: #ffffff;
        font-size: clamp(38px, 4vw, 52px);
        line-height: 1;
        letter-spacing: -.055em;
        font-weight: 950;
    }

    .new-password-subtitle {
        margin: 14px 0 28px;
        color: #7f858c;
        font-size: 13px;
        line-height: 1.8;
    }

    /* ========================================================= */
    /* ERROR MESSAGE                                              */
    /* ========================================================= */

    .new-password-message {
        margin-bottom: 20px;
        padding: 13px 15px;
        border-radius: 14px;
        font-size: 12px;
        line-height: 1.6;
        backdrop-filter: blur(12px);
    }

    .new-password-message.error {
        border: 1px solid rgba(241,123,123,.22);
        background: rgba(241,123,123,.08);
        color: #ffc1c1;
    }

    .new-password-message ul {
        margin: 8px 0 0 18px;
        padding: 0;
    }

    /* ========================================================= */
    /* FORM                                                       */
    /* ========================================================= */

    .new-password-field {
        margin-bottom: 18px;
    }

    .new-password-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 8px;
    }

    .new-password-label label {
        color: #b9b9b6;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .field-error {
        color: #f3a1a1;
        font-size: 10px;
        font-weight: 700;
    }

    .new-password-input-wrap {
        position: relative;
    }

    .new-password-input {
        width: 100%;
        height: 56px;
        padding: 0 50px 0 16px;
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 15px;
        outline: none;
        background: rgba(255,255,255,.035);
        color: #ffffff;
        font-size: 14px;
        transition:
            border-color .2s ease,
            background .2s ease,
            box-shadow .2s ease;
    }

    .new-password-input::placeholder {
        color: #565c63;
    }

    .new-password-input:focus {
        border-color: rgba(215,164,95,.46);
        background: rgba(215,164,95,.035);
        box-shadow: 0 0 0 4px rgba(215,164,95,.065);
    }

    .new-password-input-icon {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #747a81;
        font-size: 13px;
        pointer-events: none;
    }

    .password-toggle {
        position: absolute;
        right: 9px;
        top: 50%;
        transform: translateY(-50%);
        min-width: 42px;
        height: 36px;
        padding: 0 8px;
        border: 0;
        border-radius: 10px;
        background: transparent;
        color: #8b9096;
        font-size: 10px;
        font-weight: 800;
        cursor: pointer;
        transition:
            color .2s ease,
            background .2s ease;
    }

    .password-toggle:hover {
        color: #efc985;
        background: rgba(215,164,95,.06);
    }

    /* ========================================================= */
    /* PASSWORD STRENGTH                                          */
    /* ========================================================= */

    .password-strength {
        margin-top: 10px;
    }

    .password-strength-bars {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 5px;
    }

    .password-strength-bar {
        height: 4px;
        border-radius: 999px;
        background: rgba(255,255,255,.07);
        transition: background .2s ease;
    }

    .password-strength[data-level="1"] .password-strength-bar:nth-child(1) {
        background: #d65f5f;
    }

    .password-strength[data-level="2"] .password-strength-bar:nth-child(-n+2) {
        background: #d59a50;
    }

    .password-strength[data-level="3"] .password-strength-bar:nth-child(-n+3) {
        background: #d8bd63;
    }

    .password-strength[data-level="4"] .password-strength-bar:nth-child(-n+4) {
        background: #63c98f;
    }

    .password-strength-text {
        margin-top: 7px;
        color: #62686f;
        font-size: 9px;
        line-height: 1.5;
    }

    /* ========================================================= */
    /* INFO CARDS                                                 */
    /* ========================================================= */

    .new-password-info-grid {
        margin: 4px 0 22px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .new-password-info {
        padding: 15px;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 15px;
        background: rgba(255,255,255,.025);
    }

    .new-password-info.warning {
        border-color: rgba(242,198,109,.15);
        background: rgba(242,198,109,.045);
    }

    .new-password-info small {
        display: block;
        margin-bottom: 5px;
        color: #9c7548;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .new-password-info strong {
        display: block;
        color: #d7d5d0;
        font-size: 11px;
        line-height: 1.45;
    }

    .new-password-info p {
        margin: 5px 0 0;
        color: #6f757c;
        font-size: 9px;
        line-height: 1.7;
    }

    /* ========================================================= */
    /* SUBMIT / ACTIONS                                           */
    /* ========================================================= */

    .new-password-submit {
        position: relative;
        width: 100%;
        min-height: 58px;
        overflow: hidden;
        border: 0;
        border-radius: 999px;
        background:
            linear-gradient(
                135deg,
                #f1cc8b,
                #ca914c
            );
        color: #14100b;
        font-size: 12px;
        font-weight: 950;
        letter-spacing: .04em;
        cursor: pointer;
        box-shadow: 0 18px 44px rgba(215,164,95,.20);
        transition:
            transform .22s ease,
            box-shadow .22s ease;
    }

    .new-password-submit::after {
        content: "→";
        position: absolute;
        right: 22px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 17px;
        transition: transform .22s ease;
    }

    .new-password-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 25px 58px rgba(215,164,95,.30);
    }

    .new-password-submit:hover::after {
        transform: translate(4px, -50%);
    }

    .new-password-divider {
        margin: 28px 0 22px;
        display: flex;
        align-items: center;
        gap: 14px;
        color: #4d5258;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .new-password-divider::before,
    .new-password-divider::after {
        content: "";
        flex: 1;
        height: 1px;
        background: rgba(255,255,255,.07);
    }

    .login-return-card {
        padding: 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 17px;
        background: rgba(255,255,255,.025);
    }

    .login-return-copy strong {
        display: block;
        color: #dcdad5;
        font-size: 12px;
    }

    .login-return-copy span {
        display: block;
        margin-top: 3px;
        color: #686e75;
        font-size: 10px;
        line-height: 1.5;
    }

    .login-return-link {
        flex-shrink: 0;
        min-height: 38px;
        padding: 0 14px;
        display: inline-flex;
        align-items: center;
        border: 1px solid rgba(215,164,95,.18);
        border-radius: 999px;
        background: rgba(215,164,95,.055);
        color: #dfb36d;
        text-decoration: none;
        font-size: 9px;
        font-weight: 900;
        transition:
            background .2s ease,
            border-color .2s ease,
            transform .2s ease;
    }

    .login-return-link:hover {
        transform: translateY(-1px);
        border-color: rgba(215,164,95,.34);
        background: rgba(215,164,95,.10);
    }

    .new-password-security-note {
        margin-top: 18px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        color: #555b61;
        font-size: 9px;
        line-height: 1.6;
    }

    .new-password-security-mark {
        flex-shrink: 0;
        width: 20px;
        height: 20px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 50%;
        color: #8b6a40;
        font-size: 9px;
    }

    /* ========================================================= */
    /* RESPONSIVE                                                 */
    /* ========================================================= */

    @media (max-width: 1080px) {
        .new-password-stage {
            grid-template-columns: 1fr;
        }

        .new-password-visual {
            min-height: 540px;
        }

        .new-password-panel {
            min-height: auto;
            padding: 72px 32px;
        }
    }

    @media (max-width: 680px) {
        .new-password-page,
        .new-password-stage {
            min-height: auto;
        }

        .new-password-visual {
            min-height: 460px;
            padding: 40px 20px;
        }

        .new-password-visual h2 {
            font-size: clamp(46px, 14vw, 64px);
        }

        .security-badges,
        .new-password-info-grid {
            grid-template-columns: 1fr;
        }

        .new-password-panel {
            padding: 54px 18px 66px;
        }

        .login-return-card {
            align-items: flex-start;
            flex-direction: column;
        }

        .login-return-link {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush


@section('content')

<section class="new-password-page">

    <div class="new-password-stage">

        {{-- ========================================================= --}}
        {{-- LEFT / BRAND EXPERIENCE                                    --}}
        {{-- ========================================================= --}}

        <div class="new-password-visual">

            <div class="new-password-visual-content">

                <span class="new-password-kicker">
                    Mashal Account Security
                </span>

                <h2>
                    Nieuwe sleutel.
                    Zelfde
                    <span>controle.</span>
                </h2>

                <p>
                    Stel een nieuw wachtwoord in voor je Mashal-account.
                    Kies een sterk, uniek wachtwoord en herstel veilig
                    de toegang tot je persoonlijke omgeving.
                </p>


                <div class="security-badges">

                    <div class="security-badge">

                        <small>
                            Protected
                        </small>

                        <strong>
                            Beveiligde resetflow voor jouw account
                        </strong>

                    </div>


                    <div class="security-badge">

                        <small>
                            Temporary
                        </small>

                        <strong>
                            Resetlink is 60 minuten geldig
                        </strong>

                    </div>


                    <div class="security-badge">

                        <small>
                            Secure
                        </small>

                        <strong>
                            Je oude wachtwoord wordt vervangen
                        </strong>

                    </div>

                </div>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- RIGHT / RESET FORM                                        --}}
        {{-- ========================================================= --}}

        <div class="new-password-panel">

            <div class="new-password-shell">

                <div class="new-password-brand">

                    <div class="new-password-brand-mark">
                        M
                    </div>

                    <div class="new-password-brand-copy">

                        <strong>
                            Mashal
                        </strong>

                        <span>
                            Automotive
                        </span>

                    </div>

                </div>


                <span class="new-password-section-kicker">
                    Secure password reset
                </span>

                <h1 class="new-password-title">
                    Nieuw wachtwoord
                </h1>

                <p class="new-password-subtitle">
                    Bevestig je e-mailadres en kies een nieuw wachtwoord.
                    Daarna kun je weer veilig inloggen op je Mashal-account.
                </p>


                {{-- ERRORS --}}

                @if ($errors->any())

                    <div class="new-password-message error">

                        <strong>
                            Wachtwoord wijzigen is niet gelukt.
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


                {{-- FORM --}}

                <form
                    method="POST"
                    action="{{ route('password.update', $token) }}"
                >
                    @csrf


                    {{-- EMAIL --}}

                    <div class="new-password-field">

                        <div class="new-password-label">

                            <label for="email">
                                E-mailadres
                            </label>

                            @error('email')
                                <span class="field-error">
                                    {{ $message }}
                                </span>
                            @enderror

                        </div>


                        <div class="new-password-input-wrap">

                            <input
                                class="new-password-input"
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="naam@example.com"
                                required
                                autofocus
                                autocomplete="email"
                            >

                            <span class="new-password-input-icon">
                                @
                            </span>

                        </div>

                    </div>


                    {{-- PASSWORD --}}

                    <div class="new-password-field">

                        <div class="new-password-label">

                            <label for="password">
                                Nieuw wachtwoord
                            </label>

                            @error('password')
                                <span class="field-error">
                                    {{ $message }}
                                </span>
                            @enderror

                        </div>


                        <div class="new-password-input-wrap">

                            <input
                                class="new-password-input"
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Minimaal 8 tekens"
                                required
                                minlength="8"
                                autocomplete="new-password"
                            >

                            <button
                                class="password-toggle"
                                type="button"
                                data-toggle-password="password"
                                aria-label="Wachtwoord tonen of verbergen"
                            >
                                Tonen
                            </button>

                        </div>


                        <div
                            class="password-strength"
                            id="passwordStrength"
                            data-level="0"
                        >

                            <div class="password-strength-bars">

                                <span class="password-strength-bar"></span>
                                <span class="password-strength-bar"></span>
                                <span class="password-strength-bar"></span>
                                <span class="password-strength-bar"></span>

                            </div>

                            <div
                                class="password-strength-text"
                                id="passwordStrengthText"
                            >
                                Gebruik minimaal 8 tekens.
                            </div>

                        </div>

                    </div>


                    {{-- PASSWORD CONFIRMATION --}}

                    <div class="new-password-field">

                        <div class="new-password-label">

                            <label for="password_confirmation">
                                Wachtwoord bevestigen
                            </label>

                            @error('password_confirmation')
                                <span class="field-error">
                                    {{ $message }}
                                </span>
                            @enderror

                        </div>


                        <div class="new-password-input-wrap">

                            <input
                                class="new-password-input"
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                placeholder="Herhaal je nieuwe wachtwoord"
                                required
                                minlength="8"
                                autocomplete="new-password"
                            >

                            <button
                                class="password-toggle"
                                type="button"
                                data-toggle-password="password_confirmation"
                                aria-label="Wachtwoordbevestiging tonen of verbergen"
                            >
                                Tonen
                            </button>

                        </div>

                    </div>


                    {{-- INFO --}}

                    <div class="new-password-info-grid">

                        <div class="new-password-info">

                            <small>
                                Password security
                            </small>

                            <strong>
                                Gebruik een uniek wachtwoord
                            </strong>

                            <p>
                                Kies bij voorkeur een wachtwoord
                                dat je nergens anders gebruikt.
                            </p>

                        </div>


                        <div class="new-password-info warning">

                            <small>
                                Reset window
                            </small>

                            <strong>
                                Link 60 minuten geldig
                            </strong>

                            <p>
                                Is de link verlopen?
                                Vraag dan een nieuwe resetlink aan.
                            </p>

                        </div>

                    </div>


                    {{-- SUBMIT --}}

                    <button
                        class="new-password-submit"
                        type="submit"
                    >
                        Nieuw wachtwoord opslaan
                    </button>

                </form>


                <div class="new-password-divider">
                    Klaar om terug te keren?
                </div>


                <div class="login-return-card">

                    <div class="login-return-copy">

                        <strong>
                            Terug naar inloggen
                        </strong>

                        <span>
                            Na het opslaan kun je direct
                            met je nieuwe wachtwoord inloggen.
                        </span>

                    </div>


                    <a
                        class="login-return-link"
                        href="{{ route('login') }}"
                    >
                        Inloggen
                    </a>

                </div>


                <div class="new-password-security-note">

                    <span class="new-password-security-mark">
                        ✓
                    </span>

                    <span>
                        Nadat je nieuwe wachtwoord is opgeslagen,
                        kan je oude wachtwoord niet meer worden gebruikt.
                        Mashal stuurt je ook een beveiligingsmelding per e-mail.
                    </span>

                </div>

            </div>

        </div>

    </div>

</section>

@endsection


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {

        // Password show/hide.
        document
            .querySelectorAll('[data-toggle-password]')
            .forEach(function (button) {

                button.addEventListener('click', function () {

                    const inputId =
                        button.getAttribute('data-toggle-password');

                    const input =
                        document.getElementById(inputId);

                    if (!input) {
                        return;
                    }

                    const hidden =
                        input.type === 'password';

                    input.type =
                        hidden ? 'text' : 'password';

                    button.textContent =
                        hidden ? 'Verberg' : 'Tonen';

                });

            });


        // Password strength.
        const passwordInput =
            document.getElementById('password');

        const strengthBox =
            document.getElementById('passwordStrength');

        const strengthText =
            document.getElementById('passwordStrengthText');


        if (
            passwordInput &&
            strengthBox &&
            strengthText
        ) {

            passwordInput.addEventListener('input', function () {

                const value =
                    passwordInput.value;

                let score = 0;

                if (value.length >= 8) {
                    score++;
                }

                if (
                    /[A-Z]/.test(value) &&
                    /[a-z]/.test(value)
                ) {
                    score++;
                }

                if (/\d/.test(value)) {
                    score++;
                }

                if (
                    /[^A-Za-z0-9]/.test(value) &&
                    value.length >= 10
                ) {
                    score++;
                }

                strengthBox.dataset.level =
                    String(score);

                const labels = {
                    0: 'Gebruik minimaal 8 tekens.',
                    1: 'Basiswachtwoord.',
                    2: 'Redelijk wachtwoord.',
                    3: 'Sterk wachtwoord.',
                    4: 'Zeer sterk wachtwoord.'
                };

                strengthText.textContent =
                    labels[score] || labels[0];

            });

        }

    });
</script>
@endpush
