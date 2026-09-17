@extends('layouts.site-layout')

@section('title', 'Mashal | Registreren')

@push('styles')
<style>
    .register-page {
        position: relative;
        min-height: calc(100vh - 78px);
        overflow: hidden;
        background: #08090b;
    }

    .register-stage {
        min-height: calc(100vh - 78px);
        display: grid;
        grid-template-columns: minmax(0, 1.02fr) minmax(470px, .98fr);
    }

    /* ========================================================= */
    /* LEFT / CINEMATIC BRAND PANEL                              */
    /* ========================================================= */

    .register-visual {
        position: relative;
        min-height: 100%;
        overflow: hidden;
        isolation: isolate;
        display: flex;
        align-items: flex-end;
        padding: clamp(36px, 5vw, 74px);
        background: #0a0c0f;
    }

    .register-visual::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -3;
        background:
            linear-gradient(
                180deg,
                rgba(4,5,7,.10),
                rgba(4,5,7,.22) 40%,
                rgba(4,5,7,.94) 100%
            ),
            linear-gradient(
                90deg,
                rgba(4,5,7,.38),
                transparent 58%
            ),
            url('https://images.unsplash.com/photo-1503736334956-4c8f8e92946d?auto=format&fit=crop&w=1900&q=90')
            center / cover no-repeat;
        transform: scale(1.03);
        animation: registerVisualZoom 18s ease-in-out infinite alternate;
    }

    .register-visual::after {
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

    @keyframes registerVisualZoom {
        from { transform: scale(1.03); }
        to { transform: scale(1.09); }
    }

    .register-visual-content {
        max-width: 720px;
        animation: registerFadeUp .85s ease both;
    }

    .register-kicker {
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

    .register-kicker::before {
        content: "";
        width: 36px;
        height: 1px;
        background: #d7a45f;
    }

    .register-visual h2 {
        max-width: 720px;
        margin: 0;
        color: #ffffff;
        font-size: clamp(52px, 5.8vw, 88px);
        line-height: .92;
        letter-spacing: -.07em;
        font-weight: 950;
    }

    .register-visual h2 span {
        color: #f0c983;
    }

    .register-visual p {
        max-width: 590px;
        margin: 24px 0 0;
        color: rgba(255,255,255,.69);
        font-size: 14px;
        line-height: 1.9;
    }

    .register-benefits {
        margin-top: 34px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        max-width: 690px;
    }

    .register-benefit {
        padding: 14px;
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 16px;
        background: rgba(255,255,255,.045);
        backdrop-filter: blur(12px);
    }

    .register-benefit small {
        display: block;
        margin-bottom: 5px;
        color: #c59558;
        font-size: 7px;
        font-weight: 900;
        letter-spacing: .13em;
        text-transform: uppercase;
    }

    .register-benefit strong {
        display: block;
        color: rgba(255,255,255,.88);
        font-size: 11px;
        line-height: 1.5;
    }

    /* ========================================================= */
    /* RIGHT / REGISTER PANEL                                    */
    /* ========================================================= */

    .register-panel {
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

    .register-panel::before {
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

    .register-shell {
        position: relative;
        z-index: 2;
        width: 100%;
        max-width: 520px;
        animation: registerFadeUp .8s .08s ease both;
    }

    @keyframes registerFadeUp {
        from {
            opacity: 0;
            transform: translateY(28px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .register-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 32px;
    }

    .register-brand-mark {
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

    .register-brand-copy strong {
        display: block;
        color: #ffffff;
        font-size: 19px;
        line-height: 1;
        letter-spacing: -.03em;
    }

    .register-brand-copy span {
        display: block;
        margin-top: 5px;
        color: #6f757c;
        font-size: 8px;
        font-weight: 850;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .register-section-kicker {
        display: inline-block;
        margin-bottom: 10px;
        color: #b9894d;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .register-title {
        margin: 0;
        color: #ffffff;
        font-size: clamp(38px, 4vw, 52px);
        line-height: 1;
        letter-spacing: -.055em;
        font-weight: 950;
    }

    .register-subtitle {
        margin: 14px 0 28px;
        color: #7f858c;
        font-size: 13px;
        line-height: 1.8;
    }

    /* ========================================================= */
    /* ERROR MESSAGE                                              */
    /* ========================================================= */

    .register-message {
        margin-bottom: 20px;
        padding: 13px 15px;
        border-radius: 14px;
        font-size: 12px;
        line-height: 1.6;
        backdrop-filter: blur(12px);
    }

    .register-message.error {
        border: 1px solid rgba(241,123,123,.22);
        background: rgba(241,123,123,.08);
        color: #ffc1c1;
    }

    .register-message ul {
        margin: 8px 0 0 18px;
        padding: 0;
    }

    /* ========================================================= */
    /* FORM                                                       */
    /* ========================================================= */

    .register-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .register-field {
        margin-bottom: 17px;
    }

    .register-field.full {
        grid-column: 1 / -1;
    }

    .register-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 8px;
    }

    .register-label label {
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

    .register-input-wrap {
        position: relative;
    }

    .register-input {
        width: 100%;
        height: 56px;
        padding: 0 48px 0 16px;
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

    .register-input::placeholder {
        color: #565c63;
    }

    .register-input:focus {
        border-color: rgba(215,164,95,.46);
        background: rgba(215,164,95,.035);
        box-shadow: 0 0 0 4px rgba(215,164,95,.065);
    }

    .register-input-icon {
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
    /* ACCOUNT INFO                                               */
    /* ========================================================= */

    .registration-info {
        margin: 5px 0 22px;
        padding: 16px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        border: 1px solid rgba(215,164,95,.13);
        border-radius: 15px;
        background:
            linear-gradient(
                145deg,
                rgba(215,164,95,.065),
                rgba(215,164,95,.02)
            );
    }

    .registration-info-mark {
        flex-shrink: 0;
        width: 28px;
        height: 28px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(215,164,95,.18);
        border-radius: 50%;
        color: #dfb36d;
        font-size: 10px;
        font-weight: 900;
    }

    .registration-info strong {
        display: block;
        margin-bottom: 4px;
        color: #d9d7d2;
        font-size: 11px;
    }

    .registration-info p {
        margin: 0;
        color: #747a81;
        font-size: 10px;
        line-height: 1.7;
    }

    /* ========================================================= */
    /* SUBMIT                                                     */
    /* ========================================================= */

    .register-submit {
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

    .register-submit::after {
        content: "→";
        position: absolute;
        right: 22px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 17px;
        transition: transform .22s ease;
    }

    .register-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 25px 58px rgba(215,164,95,.30);
    }

    .register-submit:hover::after {
        transform: translate(4px, -50%);
    }

    /* ========================================================= */
    /* LOGIN CARD                                                 */
    /* ========================================================= */

    .register-divider {
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

    .register-divider::before,
    .register-divider::after {
        content: "";
        flex: 1;
        height: 1px;
        background: rgba(255,255,255,.07);
    }

    .login-card {
        padding: 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 17px;
        background: rgba(255,255,255,.025);
    }

    .login-card-copy strong {
        display: block;
        color: #dcdad5;
        font-size: 12px;
    }

    .login-card-copy span {
        display: block;
        margin-top: 3px;
        color: #686e75;
        font-size: 10px;
        line-height: 1.5;
    }

    .login-card-link {
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

    .login-card-link:hover {
        transform: translateY(-1px);
        border-color: rgba(215,164,95,.34);
        background: rgba(215,164,95,.10);
    }

    /* ========================================================= */
    /* PRIVACY / SECURITY                                         */
    /* ========================================================= */

    .register-security {
        margin-top: 18px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        color: #555b61;
        font-size: 9px;
        line-height: 1.6;
    }

    .register-security-mark {
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
        .register-stage {
            grid-template-columns: 1fr;
        }

        .register-visual {
            min-height: 560px;
        }

        .register-panel {
            min-height: auto;
            padding: 72px 32px;
        }
    }

    @media (max-width: 680px) {
        .register-page,
        .register-stage {
            min-height: auto;
        }

        .register-visual {
            min-height: 470px;
            padding: 40px 20px;
        }

        .register-visual h2 {
            font-size: clamp(48px, 14.5vw, 66px);
        }

        .register-benefits {
            grid-template-columns: 1fr;
        }

        .register-panel {
            padding: 54px 18px 66px;
        }

        .register-form-grid {
            grid-template-columns: 1fr;
        }

        .register-field.full {
            grid-column: auto;
        }

        .login-card {
            align-items: flex-start;
            flex-direction: column;
        }

        .login-card-link {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush


@section('content')

<section class="register-page">

    <div class="register-stage">

        {{-- ========================================================= --}}
        {{-- LEFT / EXPERIENCE                                         --}}
        {{-- ========================================================= --}}

        <div class="register-visual">

            <div class="register-visual-content">

                <span class="register-kicker">
                    Join Mashal Automotive
                </span>

                <h2>
                    Jouw volgende rit
                    begint met
                    <span>één account.</span>
                </h2>

                <p>
                    Maak je persoonlijke Mashal-account aan
                    en krijg toegang tot je voertuigselectie,
                    beveiligde checkout, orderhistorie
                    en accountbeheer in één premium omgeving.
                </p>


                <div class="register-benefits">

                    <div class="register-benefit">

                        <small>
                            Personal
                        </small>

                        <strong>
                            Eén account voor jouw volledige Mashal-ervaring
                        </strong>

                    </div>


                    <div class="register-benefit">

                        <small>
                            Verified
                        </small>

                        <strong>
                            Beveiligde toegang via e-mailverificatie
                        </strong>

                    </div>


                    <div class="register-benefit">

                        <small>
                            Orders
                        </small>

                        <strong>
                            Bestellingen en status altijd inzichtelijk
                        </strong>

                    </div>

                </div>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- RIGHT / REGISTER FORM                                     --}}
        {{-- ========================================================= --}}

        <div class="register-panel">

            <div class="register-shell">

                <div class="register-brand">

                    <div class="register-brand-mark">
                        M
                    </div>

                    <div class="register-brand-copy">

                        <strong>
                            Mashal
                        </strong>

                        <span>
                            Automotive
                        </span>

                    </div>

                </div>


                <span class="register-section-kicker">
                    Create your account
                </span>

                <h1 class="register-title">
                    Registreren
                </h1>

                <p class="register-subtitle">
                    Vul je gegevens in.
                    Na registratie sturen we je een verificatiecode
                    om je e-mailadres te bevestigen.
                </p>


                {{-- ERRORS --}}

                @if ($errors->any())

                    <div class="register-message error">

                        <strong>
                            Registreren is niet gelukt.
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
                    action="{{ route('register.submit') }}"
                    id="registerForm"
                >
                    @csrf


                    <div class="register-form-grid">

                        {{-- NAME --}}

                        <div class="register-field full">

                            <div class="register-label">

                                <label for="name">
                                    Naam
                                </label>

                                @error('name')
                                    <span class="field-error">
                                        {{ $message }}
                                    </span>
                                @enderror

                            </div>


                            <div class="register-input-wrap">

                                <input
                                    class="register-input"
                                    id="name"
                                    type="text"
                                    name="name"
                                    value="{{ old('name') }}"
                                    placeholder="Jouw volledige naam"
                                    autocomplete="name"
                                    required
                                    autofocus
                                >

                                <span class="register-input-icon">
                                    ◇
                                </span>

                            </div>

                        </div>


                        {{-- EMAIL --}}

                        <div class="register-field full">

                            <div class="register-label">

                                <label for="email">
                                    E-mailadres
                                </label>

                                @error('email')
                                    <span class="field-error">
                                        {{ $message }}
                                    </span>
                                @enderror

                            </div>


                            <div class="register-input-wrap">

                                <input
                                    class="register-input"
                                    id="email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="naam@example.com"
                                    autocomplete="email"
                                    required
                                >

                                <span class="register-input-icon">
                                    @
                                </span>

                            </div>

                        </div>


                        {{-- PASSWORD --}}

                        <div class="register-field">

                            <div class="register-label">

                                <label for="password">
                                    Wachtwoord
                                </label>

                                @error('password')
                                    <span class="field-error">
                                        {{ $message }}
                                    </span>
                                @enderror

                            </div>


                            <div class="register-input-wrap">

                                <input
                                    class="register-input"
                                    id="password"
                                    type="password"
                                    name="password"
                                    placeholder="Minimaal 8 tekens"
                                    autocomplete="new-password"
                                    minlength="8"
                                    required
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

                        <div class="register-field">

                            <div class="register-label">

                                <label for="password_confirmation">
                                    Wachtwoord bevestigen
                                </label>

                                @error('password_confirmation')
                                    <span class="field-error">
                                        {{ $message }}
                                    </span>
                                @enderror

                            </div>


                            <div class="register-input-wrap">

                                <input
                                    class="register-input"
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    placeholder="Herhaal je wachtwoord"
                                    autocomplete="new-password"
                                    minlength="8"
                                    required
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

                    </div>


                    {{-- INFO --}}

                    <div class="registration-info">

                        <span class="registration-info-mark">
                            i
                        </span>

                        <div>

                            <strong>
                                Na registratie
                            </strong>

                            <p>
                                Je ontvangt een verificatiecode per e-mail.
                                Na verificatie kun je alle functies gebruiken,
                                waaronder het plaatsen van bestellingen.
                            </p>

                        </div>

                    </div>


                    {{-- SUBMIT --}}

                    <button
                        class="register-submit"
                        type="submit"
                    >
                        Account aanmaken
                    </button>

                </form>


                <div class="register-divider">
                    Al onderdeel van Mashal?
                </div>


                <div class="login-card">

                    <div class="login-card-copy">

                        <strong>
                            Heb je al een account?
                        </strong>

                        <span>
                            Log in en ga direct verder
                            met jouw selectie en bestellingen.
                        </span>

                    </div>


                    <a
                        class="login-card-link"
                        href="{{ route('login') }}"
                    >
                        Inloggen
                    </a>

                </div>


                <div class="register-security">

                    <span class="register-security-mark">
                        ✓
                    </span>

                    <span>
                        Gebruik een uniek wachtwoord
                        en deel je verificatiecode nooit met anderen.
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

                    const isHidden =
                        input.type === 'password';

                    input.type =
                        isHidden ? 'text' : 'password';

                    button.textContent =
                        isHidden ? 'Verberg' : 'Tonen';

                });

            });


        // Simple visual password strength meter.
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

                if (/[A-Z]/.test(value) && /[a-z]/.test(value)) {
                    score++;
                }

                if (/\d/.test(value)) {
                    score++;
                }

                if (/[^A-Za-z0-9]/.test(value) && value.length >= 10) {
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
