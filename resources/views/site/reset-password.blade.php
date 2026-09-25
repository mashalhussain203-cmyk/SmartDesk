@extends('layouts.site-layout')

@section('title', 'Nieuw wachtwoord | Mashal Studio')

@section(
    'meta_description',
    'Stel veilig een nieuw wachtwoord in voor je Mashal Studio-account.'
)

@push('styles')
<style>
    :root {
        --reset-bg: #050506;
        --reset-panel: #0e0e11;
        --reset-panel-2: #141418;
        --reset-text: #f7f7f8;
        --reset-muted: #96969e;
        --reset-muted-2: #686870;
        --reset-line: rgba(255,255,255,.09);
        --reset-line-strong: rgba(255,255,255,.15);
        --reset-gold: #f1d84a;
        --reset-gold-2: #d8b91e;
        --reset-orange: #ff7a24;
        --reset-green: #45df8b;
        --reset-danger: #ff8f8f;
        --reset-radius: 24px;
        --reset-shadow: 0 24px 70px rgba(0,0,0,.36);
    }

    .reset-page,
    .reset-page * {
        box-sizing: border-box;
    }

    .reset-page {
        position: relative;
        width: 100%;
        min-height: calc(100dvh - var(--studio-header-height, 78px));
        overflow-x: clip;
        color: var(--reset-text);
        background:
            radial-gradient(
                circle at 14% 8%,
                rgba(255,125,30,.11),
                transparent 31rem
            ),
            radial-gradient(
                circle at 88% 16%,
                rgba(241,216,74,.06),
                transparent 26rem
            ),
            linear-gradient(
                180deg,
                #050506 0%,
                #080809 48%,
                #050506 100%
            );
    }

    .reset-page::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        opacity: .22;
        background-image:
            linear-gradient(
                rgba(255,255,255,.018) 1px,
                transparent 1px
            ),
            linear-gradient(
                90deg,
                rgba(255,255,255,.018) 1px,
                transparent 1px
            );
        background-size: 64px 64px;
        mask-image: linear-gradient(
            to bottom,
            #000,
            transparent 74%
        );
    }

    .reset-shell {
        position: relative;
        z-index: 1;
        width: min(calc(100% - 32px), 1080px);
        margin-inline: auto;
        padding: clamp(34px, 5vw, 72px) 0 70px;
    }

    .reset-intro {
        width: min(100%, 620px);
        margin: 0 auto 26px;
        text-align: center;
        animation:
            reset-enter .42s cubic-bezier(.2,.8,.2,1) both;
    }

    .reset-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        color: var(--reset-gold);
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .15em;
        text-transform: uppercase;
    }

    .reset-eyebrow::before,
    .reset-eyebrow::after {
        content: "";
        width: 22px;
        height: 1px;
        background:
            linear-gradient(
                90deg,
                transparent,
                rgba(241,216,74,.68)
            );
    }

    .reset-eyebrow::after {
        transform: scaleX(-1);
    }

    .reset-title {
        margin: 0;
        color: #fff;
        font-size: clamp(36px, 6vw, 62px);
        line-height: .96;
        font-weight: 930;
        letter-spacing: -.055em;
        text-wrap: balance;
    }

    .reset-title span {
        display: block;
        margin-top: 5px;
        color: var(--reset-orange);
    }

    .reset-lead {
        max-width: 540px;
        margin: 16px auto 0;
        color: var(--reset-muted);
        font-size: 12px;
        line-height: 1.7;
    }

    .reset-card-wrap {
        width: min(100%, 500px);
        margin-inline: auto;
        animation:
            reset-enter .46s .05s cubic-bezier(.2,.8,.2,1) both;
    }

    .reset-card {
        position: relative;
        width: 100%;
        overflow: hidden;
        border: 1px solid var(--reset-line-strong);
        border-radius: var(--reset-radius);
        background:
            linear-gradient(
                155deg,
                rgba(255,255,255,.038),
                transparent 38%
            ),
            linear-gradient(
                180deg,
                rgba(20,20,24,.98),
                rgba(9,9,11,.985)
            );
        box-shadow: var(--reset-shadow);
    }

    .reset-card::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        border-radius: inherit;
        background:
            linear-gradient(
                135deg,
                rgba(241,216,74,.11),
                transparent 28%,
                transparent 74%,
                rgba(255,122,36,.055)
            );
    }

    .reset-card::after {
        content: "";
        position: absolute;
        right: 0;
        bottom: 0;
        width: 72px;
        height: 72px;
        pointer-events: none;
        border-right: 2px solid rgba(241,216,74,.55);
        border-bottom: 2px solid rgba(241,216,74,.55);
        border-radius: 0 0 calc(var(--reset-radius) - 1px) 0;
        opacity: .72;
    }

    .reset-card-inner {
        position: relative;
        z-index: 1;
        padding: 28px;
    }

    .reset-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 13px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(241,216,74,.22);
        border-radius: 14px;
        color: var(--reset-gold);
        background: rgba(241,216,74,.045);
    }

    .reset-icon svg {
        width: 23px;
        height: 23px;
    }

    .reset-heading {
        margin: 0;
        text-align: center;
        color: #fff;
        font-size: 25px;
        line-height: 1.1;
        font-weight: 900;
        letter-spacing: -.035em;
    }

    .reset-heading strong {
        color: var(--reset-gold);
        font-weight: inherit;
    }

    .reset-description {
        max-width: 390px;
        margin: 8px auto 18px;
        color: var(--reset-muted);
        text-align: center;
        font-size: 10px;
        line-height: 1.6;
    }

    .reset-message {
        margin-bottom: 11px;
        padding: 10px 11px;
        border-radius: 10px;
        font-size: 9px;
        line-height: 1.55;
    }

    .reset-message.success {
        border: 1px solid rgba(69,223,139,.18);
        color: #adf4c9;
        background: rgba(69,223,139,.055);
    }

    .reset-message.error {
        border: 1px solid rgba(255,143,143,.18);
        color: #ffc2c2;
        background: rgba(255,90,90,.055);
    }

    .reset-message ul {
        margin: 5px 0 0 16px;
        padding: 0;
    }

    .reset-field {
        margin-bottom: 13px;
    }

    .reset-label-row {
        min-height: 17px;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .reset-label-row label {
        color: #a8a8ae;
        font-size: 8px;
        font-weight: 850;
        letter-spacing: .035em;
    }

    .reset-field-error {
        color: #ffacac;
        font-size: 7.5px;
        font-weight: 800;
    }

    .reset-input-wrap {
        position: relative;
    }

    .reset-input {
        width: 100%;
        height: 44px;
        padding: 0 13px;
        border: 1px solid rgba(255,255,255,.115);
        border-radius: 11px;
        outline: none;
        color: #f5f5f6;
        background: rgba(0,0,0,.26);
        font: inherit;
        font-size: 10px;
        transition:
            border-color .15s ease,
            background .15s ease,
            box-shadow .15s ease;
    }

    .reset-input::placeholder {
        color: #5c5c64;
    }

    .reset-input:focus {
        border-color: rgba(241,216,74,.48);
        background: rgba(241,216,74,.02);
        box-shadow: 0 0 0 3px rgba(241,216,74,.055);
    }

    .reset-input.with-toggle {
        padding-right: 78px;
    }

    .reset-password-toggle {
        position: absolute;
        right: 6px;
        top: 50%;
        min-width: 64px;
        height: 32px;
        padding: 0 9px;
        transform: translateY(-50%);
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 8px;
        color: #94949b;
        background: #161619;
        font: inherit;
        font-size: 7px;
        font-weight: 850;
        cursor: pointer;
    }

    .reset-strength {
        margin-top: 8px;
    }

    .reset-strength-track {
        height: 5px;
        overflow: hidden;
        border-radius: 999px;
        background: rgba(255,255,255,.055);
    }

    .reset-strength-bar {
        width: 100%;
        height: 100%;
        border-radius: inherit;
        background: #707780;
        transform: scaleX(0);
        transform-origin: left center;
        transition:
            transform .18s ease,
            background-color .18s ease;
    }

    .reset-strength-copy {
        margin-top: 6px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        color: #65656d;
        font-size: 7px;
    }

    .reset-match {
        min-height: 18px;
        margin-top: 6px;
        color: #65656d;
        font-size: 7px;
    }

    .reset-match.good {
        color: #86d4a1;
    }

    .reset-match.bad {
        color: #e29b9b;
    }

    .reset-info-grid {
        margin: 4px 0 14px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .reset-info {
        padding: 12px;
        border: 1px solid rgba(255,255,255,.065);
        border-radius: 12px;
        background: rgba(255,255,255,.012);
    }

    .reset-info small {
        display: block;
        color: #846a45;
        font-size: 6px;
        font-weight: 950;
        letter-spacing: .09em;
        text-transform: uppercase;
    }

    .reset-info strong {
        display: block;
        margin-top: 5px;
        color: #c5c8cd;
        font-size: 8px;
        line-height: 1.45;
    }

    .reset-info p {
        margin: 4px 0 0;
        color: #66666e;
        font-size: 7px;
        line-height: 1.55;
    }

    .reset-submit {
        width: 100%;
        min-height: 44px;
        border: 0;
        border-radius: 10px;
        color: #171300;
        background:
            linear-gradient(
                180deg,
                #fff04b,
                #d9b70d
            );
        box-shadow:
            0 10px 26px rgba(216,183,13,.14),
            inset 0 1px 0 rgba(255,255,255,.5);
        font: inherit;
        font-size: 9px;
        font-weight: 950;
        cursor: pointer;
        transition:
            transform .15s ease,
            filter .15s ease;
    }

    .reset-submit:hover:not(:disabled) {
        transform: translateY(-1px);
        filter: brightness(1.045);
    }

    .reset-submit:active:not(:disabled) {
        transform: scale(.992);
    }

    .reset-submit:disabled {
        opacity: .66;
        cursor: wait;
    }

    .reset-divider {
        margin: 16px 0;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #6d6d74;
        font-size: 7px;
        font-weight: 850;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .reset-divider::before,
    .reset-divider::after {
        content: "";
        flex: 1;
        height: 1px;
        background: rgba(255,255,255,.075);
    }

    .reset-return {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 11px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 11px;
        background: rgba(0,0,0,.14);
    }

    .reset-return-copy strong,
    .reset-return-copy span {
        display: block;
    }

    .reset-return-copy strong {
        color: #d2d2d5;
        font-size: 8px;
    }

    .reset-return-copy span {
        margin-top: 3px;
        color: #75757d;
        font-size: 7px;
        line-height: 1.5;
    }

    .reset-return-link {
        min-height: 34px;
        padding: 0 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        border: 1px solid rgba(241,216,74,.18);
        border-radius: 9px;
        color: var(--reset-gold);
        background: rgba(241,216,74,.045);
        text-decoration: none;
        font-size: 7px;
        font-weight: 900;
        transition:
            transform .14s ease,
            background .14s ease;
    }

    .reset-return-link:hover {
        transform: translateY(-1px);
        background: rgba(241,216,74,.07);
    }

    .reset-security-note {
        margin-top: 12px;
        display: flex;
        align-items: flex-start;
        gap: 8px;
        color: #6c6c73;
        font-size: 7px;
        line-height: 1.55;
    }

    .reset-security-mark {
        width: 20px;
        height: 20px;
        flex: 0 0 20px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(69,223,139,.12);
        border-radius: 50%;
        color: #83d09f;
        background: rgba(69,223,139,.025);
        font-size: 7px;
        font-weight: 900;
    }

    .reset-footer {
        margin-top: 18px;
        color: #606067;
        text-align: center;
        font-size: 9px;
        animation:
            reset-enter .46s .09s cubic-bezier(.2,.8,.2,1) both;
    }

    /*
    |--------------------------------------------------------------------------
    | Smooth animation
    |--------------------------------------------------------------------------
    |
    | Alleen opacity + transform. Geen blur, backdrop-filter, clip-path
    | of continu bewegende elementen.
    |
    */

    @keyframes reset-enter {
        from {
            opacity: 0;
            transform: translate3d(0, 12px, 0) scale(.992);
        }

        to {
            opacity: 1;
            transform: translate3d(0, 0, 0) scale(1);
        }
    }

    @media (max-width: 640px) {
        .reset-shell {
            width: min(calc(100% - 20px), 1080px);
            padding-top: 26px;
            padding-bottom: 48px;
        }

        .reset-intro {
            margin-bottom: 20px;
        }

        .reset-title {
            font-size: clamp(34px, 12vw, 46px);
        }

        .reset-lead {
            font-size: 11px;
        }

        .reset-card-wrap {
            width: 100%;
            max-width: 500px;
        }

        .reset-card-inner {
            padding: 22px 18px 24px;
        }

        .reset-card::after {
            width: 52px;
            height: 52px;
        }

        .reset-info-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 390px) {
        .reset-shell {
            width: min(calc(100% - 14px), 1080px);
        }

        .reset-card-inner {
            padding-inline: 14px;
        }

        .reset-return {
            align-items: flex-start;
            flex-direction: column;
        }

        .reset-return-link {
            width: 100%;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .reset-intro,
        .reset-card-wrap,
        .reset-footer {
            animation: none !important;
        }

        .reset-page *,
        .reset-page *::before,
        .reset-page *::after {
            scroll-behavior: auto !important;
            transition-duration: .01ms !important;
        }
    }
</style>
@endpush

@section('content')
<section class="reset-page">
    <main class="reset-shell">
        <header class="reset-intro">
            <div class="reset-eyebrow">
                Secure recovery
            </div>

            <h1 class="reset-title">
                Nieuw wachtwoord
                <span>Mashal Studio</span>
            </h1>

            <p class="reset-lead">
                Stel een nieuw, sterk wachtwoord in. Je projecten,
                afbeeldingen en opgeslagen versies blijven gewoon
                gekoppeld aan hetzelfde account.
            </p>
        </header>

        <div class="reset-card-wrap">
            <section class="reset-card" aria-labelledby="resetHeading">
                <div class="reset-card-inner">
                    <div class="reset-icon" aria-hidden="true">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M7 10V7a5 5 0 0 1 10 0v3"></path>
                            <rect x="5" y="10" width="14" height="10" rx="2"></rect>
                            <path d="M12 14v2"></path>
                        </svg>
                    </div>

                    <h2 class="reset-heading" id="resetHeading">
                        Secure <strong>Reset</strong>
                    </h2>

                    <p class="reset-description">
                        Bevestig je e-mailadres en kies daarna een nieuw
                        wachtwoord voor je Mashal Studio-account.
                    </p>

                    @if (session('success'))
                        <div class="reset-message success" role="status">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="reset-message error" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="reset-message error" role="alert">
                            <strong>
                                Wachtwoord wijzigen is niet gelukt.
                            </strong>

                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form
                        id="resetPasswordForm"
                        method="POST"
                        action="{{ route('password.update', $token) }}"
                    >
                        @csrf

                        <div class="reset-field">
                            <div class="reset-label-row">
                                <label for="email">
                                    E-mailadres
                                </label>

                                @error('email')
                                    <span class="reset-field-error">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <input
                                class="reset-input"
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email', request('email')) }}"
                                placeholder="naam@example.com"
                                autocomplete="email"
                                inputmode="email"
                                autocapitalize="none"
                                spellcheck="false"
                                required
                                autofocus
                            >
                        </div>

                        <div class="reset-field">
                            <div class="reset-label-row">
                                <label for="password">
                                    Nieuw wachtwoord
                                </label>

                                @error('password')
                                    <span class="reset-field-error">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="reset-input-wrap">
                                <input
                                    class="reset-input with-toggle"
                                    id="password"
                                    type="password"
                                    name="password"
                                    placeholder="Minimaal 8 tekens"
                                    autocomplete="new-password"
                                    minlength="8"
                                    required
                                >

                                <button
                                    class="reset-password-toggle"
                                    type="button"
                                    data-toggle-password="password"
                                    aria-label="Nieuw wachtwoord tonen of verbergen"
                                    aria-pressed="false"
                                >
                                    Tonen
                                </button>
                            </div>

                            <div class="reset-strength">
                                <div class="reset-strength-track">
                                    <div
                                        class="reset-strength-bar"
                                        id="passwordStrengthBar"
                                    ></div>
                                </div>

                                <div class="reset-strength-copy">
                                    <span>Wachtwoordsterkte</span>
                                    <span id="passwordStrengthLabel">
                                        Nog niet ingevuld
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="reset-field">
                            <div class="reset-label-row">
                                <label for="password_confirmation">
                                    Wachtwoord bevestigen
                                </label>

                                @error('password_confirmation')
                                    <span class="reset-field-error">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="reset-input-wrap">
                                <input
                                    class="reset-input with-toggle"
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    placeholder="Herhaal je nieuwe wachtwoord"
                                    autocomplete="new-password"
                                    minlength="8"
                                    required
                                >

                                <button
                                    class="reset-password-toggle"
                                    type="button"
                                    data-toggle-password="password_confirmation"
                                    aria-label="Wachtwoordbevestiging tonen of verbergen"
                                    aria-pressed="false"
                                >
                                    Tonen
                                </button>
                            </div>

                            <div
                                class="reset-match"
                                id="passwordMatchStatus"
                                aria-live="polite"
                            ></div>
                        </div>

                        <div class="reset-info-grid">
                            <div class="reset-info">
                                <small>
                                    Password security
                                </small>

                                <strong>
                                    Gebruik een uniek wachtwoord
                                </strong>

                                <p>
                                    Kies bij voorkeur een lang wachtwoord dat
                                    je nergens anders gebruikt.
                                </p>
                            </div>

                            <div class="reset-info">
                                <small>
                                    Recovery link
                                </small>

                                <strong>
                                    Tijdelijke resetlink
                                </strong>

                                <p>
                                    Is je link verlopen? Vraag dan een nieuwe
                                    resetlink aan.
                                </p>
                            </div>
                        </div>

                        <button
                            class="reset-submit"
                            id="resetPasswordSubmit"
                            type="submit"
                        >
                            Nieuw wachtwoord opslaan
                        </button>
                    </form>

                    <div class="reset-divider">
                        Klaar om terug te keren?
                    </div>

                    <div class="reset-return">
                        <div class="reset-return-copy">
                            <strong>
                                Terug naar inloggen
                            </strong>

                            <span>
                                Na een succesvolle reset log je in met je
                                nieuwe wachtwoord.
                            </span>
                        </div>

                        <a
                            class="reset-return-link"
                            href="{{ route('login') }}"
                        >
                            Inloggen
                        </a>
                    </div>

                    <div class="reset-security-note">
                        <span
                            class="reset-security-mark"
                            aria-hidden="true"
                        >
                            ✓
                        </span>

                        <span>
                            Deel je resetlink of nieuwe wachtwoord nooit
                            met anderen. Mashal Studio vraagt je nooit via
                            chat of telefoon om je wachtwoord.
                        </span>
                    </div>
                </div>
            </section>

            <div class="reset-footer">
                Mashal Studio · Secure account recovery
            </div>
        </div>
    </main>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const form =
        document.getElementById(
            'resetPasswordForm'
        );

    const submit =
        document.getElementById(
            'resetPasswordSubmit'
        );

    const password =
        document.getElementById(
            'password'
        );

    const confirmation =
        document.getElementById(
            'password_confirmation'
        );

    const strengthBar =
        document.getElementById(
            'passwordStrengthBar'
        );

    const strengthLabel =
        document.getElementById(
            'passwordStrengthLabel'
        );

    const matchStatus =
        document.getElementById(
            'passwordMatchStatus'
        );

    /*
    |--------------------------------------------------------------------------
    | Password visibility
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(
            '[data-toggle-password]'
        )
        .forEach(function (button) {
            button.addEventListener(
                'click',
                function () {
                    const input =
                        document.getElementById(
                            button.getAttribute(
                                'data-toggle-password'
                            )
                        );

                    if (!input) {
                        return;
                    }

                    const showing =
                        input.type === 'password';

                    input.type =
                        showing
                            ? 'text'
                            : 'password';

                    button.textContent =
                        showing
                            ? 'Verbergen'
                            : 'Tonen';

                    button.setAttribute(
                        'aria-pressed',
                        showing
                            ? 'true'
                            : 'false'
                    );
                }
            );
        });

    /*
    |--------------------------------------------------------------------------
    | Password strength
    |--------------------------------------------------------------------------
    */

    function calculateStrength(value) {
        if (!value) {
            return {
                percent: 0,
                label: 'Nog niet ingevuld',
                tone: '#707780'
            };
        }

        let score = 0;

        if (value.length >= 8) score++;
        if (value.length >= 12) score++;
        if (/[A-Z]/.test(value)) score++;
        if (/[a-z]/.test(value)) score++;
        if (/\d/.test(value)) score++;
        if (/[^A-Za-z0-9]/.test(value)) score++;

        if (score <= 2) {
            return {
                percent: 30,
                label: 'Zwak',
                tone: '#d66f6f'
            };
        }

        if (score <= 4) {
            return {
                percent: 65,
                label: 'Redelijk',
                tone: '#d4a55d'
            };
        }

        return {
            percent: 100,
            label: 'Sterk',
            tone: '#69c98f'
        };
    }

    function updateStrength() {
        if (
            !password ||
            !strengthBar ||
            !strengthLabel
        ) {
            return;
        }

        const result =
            calculateStrength(
                password.value
            );

        strengthBar.style.transform =
            'scaleX(' +
            (result.percent / 100) +
            ')';

        strengthBar.style.backgroundColor =
            result.tone;

        strengthLabel.textContent =
            result.label;
    }

    /*
    |--------------------------------------------------------------------------
    | Confirmation match
    |--------------------------------------------------------------------------
    */

    function updateMatchStatus() {
        if (
            !password ||
            !confirmation ||
            !matchStatus
        ) {
            return;
        }

        matchStatus.classList.remove(
            'good',
            'bad'
        );

        if (!confirmation.value) {
            matchStatus.textContent = '';
            return;
        }

        if (
            password.value ===
            confirmation.value
        ) {
            matchStatus.textContent =
                'Wachtwoorden komen overeen.';

            matchStatus.classList.add(
                'good'
            );

            return;
        }

        matchStatus.textContent =
            'Wachtwoorden komen nog niet overeen.';

        matchStatus.classList.add(
            'bad'
        );
    }

    password?.addEventListener(
        'input',
        function () {
            updateStrength();
            updateMatchStatus();
        }
    );

    confirmation?.addEventListener(
        'input',
        updateMatchStatus
    );

    /*
    |--------------------------------------------------------------------------
    | Submit state
    |--------------------------------------------------------------------------
    */

    form?.addEventListener(
        'submit',
        function () {
            if (!submit) {
                return;
            }

            submit.disabled = true;
            submit.textContent =
                'Nieuw wachtwoord opslaan…';
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
                'Nieuw wachtwoord opslaan';
        }
    );

    updateStrength();
    updateMatchStatus();
});
</script>
@endpush
