@extends('layouts.site-layout')

@section('title', 'Registreren | Mashal Studio')

@section(
    'meta_description',
    'Maak een Mashal Studio-account aan en beheer je persoonlijke afbeeldingen, bewerkingen en opgeslagen versies.'
)

@push('styles')
<style>

    :root {
        --glass-bg: #020202;
        --glass-card: rgba(18,18,22,.73);
        --glass-line: rgba(255,255,255,.18);
        --glass-soft: rgba(255,255,255,.08);
        --glass-text: #f7f7f8;
        --glass-muted: #9d9ca1;
        --glass-yellow: #f4ee1f;
        --glass-yellow-2: #d9b70b;
        --glass-orange: #ff6b23;
        --glass-green: #18ed7e;
        --glass-danger: #ff8b8b;
    }

    .glass-auth-page,
    .glass-auth-page * {
        box-sizing: border-box;
    }

    .glass-auth-page {
        position: relative;
        isolation: isolate;
        min-height: calc(100dvh - 76px);
        overflow: hidden;
        display: flex;
        justify-content: center;
        color: var(--glass-text);
        background:
            radial-gradient(circle at 50% 34%, rgba(255,177,0,.045), transparent 29rem),
            #020202;
    }

    .glass-bg,
    .glass-bg::before,
    .glass-bg::after {
        position: absolute;
        inset: 0;
        pointer-events: none;
    }

    .glass-bg {
        z-index: -5;
        overflow: hidden;
    }

    .glass-bg::before,
    .glass-bg::after {
        content: "";
    }

    .glass-bg::before {
        inset: -26%;
        background:
            linear-gradient(
                128deg,
                transparent 0 25%,
                rgba(255,115,0,.04) 29%,
                rgba(255,141,0,.42) 31%,
                rgba(255,219,108,.94) 32%,
                rgba(255,122,0,.34) 33.3%,
                transparent 36% 49%,
                rgba(255,126,0,.03) 52%,
                rgba(255,141,0,.45) 54%,
                rgba(255,226,126,.87) 55%,
                rgba(255,123,0,.31) 56.2%,
                transparent 59% 74%,
                rgba(255,173,0,.21) 77%,
                rgba(255,230,145,.72) 78%,
                transparent 81%
            );
        filter: blur(3px);
        opacity: .95;
        transform: rotate(-4deg) scale(1.12);
        animation: glassBgA 12s ease-in-out infinite alternate;
    }

    .glass-bg::after {
        inset: -15%;
        background:
            linear-gradient(
                60deg,
                transparent 0 19%,
                rgba(165,170,180,.11) 20%,
                rgba(247,247,250,.42) 21%,
                rgba(89,91,96,.12) 22%,
                transparent 24% 62%,
                rgba(255,152,0,.12) 64%,
                rgba(255,202,81,.45) 65%,
                rgba(255,123,0,.10) 66%,
                transparent 68%
            );
        filter: blur(5px);
        opacity: .56;
        transform: rotate(3deg);
        animation: glassBgB 15s ease-in-out infinite alternate;
    }

    .glass-ribbon {
        position: absolute;
        z-index: -4;
        width: 1050px;
        height: 110px;
        border-radius: 50%;
        pointer-events: none;
        filter: blur(8px);
        opacity: .45;
        background:
            linear-gradient(
                180deg,
                transparent 0 28%,
                rgba(255,128,0,.10) 39%,
                rgba(255,211,85,.81) 49%,
                rgba(255,127,0,.35) 55%,
                transparent 72%
            );
    }

    .glass-ribbon.one {
        left: -340px;
        top: 180px;
        transform: rotate(31deg);
    }

    .glass-ribbon.two {
        right: -390px;
        top: 460px;
        transform: rotate(-28deg);
    }

    .glass-ribbon.three {
        left: -420px;
        bottom: 80px;
        transform: rotate(-25deg);
    }

    .glass-stage {
        width: min(100%, 512px);
        min-height: 910px;
        padding: 70px 22px 64px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .glass-poster-title {
        margin: 0 0 48px;
        text-align: center;
        color: #f8f8f8;
        font-family: Arial, Helvetica, sans-serif;
        font-size: clamp(35px, 9vw, 48px);
        font-weight: 400;
        line-height: .91;
        letter-spacing: -.055em;
        text-shadow: 0 5px 20px rgba(0,0,0,.65);
    }

    .glass-poster-title span {
        display: block;
        margin-top: 8px;
        color: var(--glass-orange);
        font-weight: 400;
    }

    .glass-card-shell {
        position: relative;
        width: min(100%, 435px);
        flex: 0 0 auto;
        filter: drop-shadow(0 22px 36px rgba(0,0,0,.48));
    }

    .glass-card-border {
        position: absolute;
        inset: 0;
        padding: 1px;
        border-radius: 25px;
        clip-path: polygon(14% 0,100% 0,100% 86%,88% 100%,0 100%,0 12%);
        background:
            linear-gradient(
                142deg,
                rgba(255,255,255,.65),
                rgba(255,218,132,.18) 28%,
                rgba(255,255,255,.16) 59%,
                rgba(255,196,63,.68)
            );
    }

    .glass-card {
        position: relative;
        width: 100%;
        min-height: 100%;
        overflow: hidden;
        border-radius: 24px;
        clip-path: polygon(14% 0,100% 0,100% 86%,88% 100%,0 100%,0 12%);
        background:
            linear-gradient(137deg, rgba(255,255,255,.045), transparent 36%),
            linear-gradient(160deg, rgba(19,18,22,.78), rgba(8,8,10,.79));
        backdrop-filter: blur(25px) saturate(125%);
        -webkit-backdrop-filter: blur(25px) saturate(125%);
    }

    .glass-card::before {
        content: "";
        position: absolute;
        inset: -55%;
        z-index: 0;
        opacity: .27;
        pointer-events: none;
        background:
            linear-gradient(
                125deg,
                transparent 33%,
                rgba(255,153,0,.13) 43%,
                rgba(255,226,133,.44) 47%,
                rgba(255,127,0,.14) 51%,
                transparent 58%
            );
        animation: glassSweep 8s ease-in-out infinite;
    }

    .glass-corner {
        position: absolute;
        z-index: 5;
        border: 1px solid rgba(255,255,255,.44);
        background: rgba(255,255,255,.07);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        box-shadow: inset 0 0 14px rgba(255,255,255,.05);
        pointer-events: none;
    }

    .glass-corner.top-left {
        width: 70px;
        height: 70px;
        top: -2px;
        left: -4px;
        border-radius: 16px;
        clip-path: polygon(0 0,100% 0,0 100%);
    }

    .glass-corner.bottom-right {
        width: 69px;
        height: 69px;
        right: -3px;
        bottom: -4px;
        border-radius: 16px;
        clip-path: polygon(100% 0,100% 100%,0 100%);
    }

    .glass-inner {
        position: relative;
        z-index: 2;
        padding: 31px 30px 30px;
    }

    .glass-icon {
        width: 46px;
        height: 46px;
        margin: 0 auto 13px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(236,224,28,.35);
        border-radius: 13px;
        color: var(--glass-yellow);
        background: rgba(240,232,31,.035);
        box-shadow:
            0 0 18px rgba(240,229,33,.08),
            inset 0 0 12px rgba(240,229,33,.025);
    }

    .glass-icon svg {
        width: 23px;
        height: 23px;
    }

    .glass-heading {
        margin: 0;
        text-align: center;
        font-size: 24px;
        line-height: 1;
        font-weight: 850;
        letter-spacing: -.035em;
    }

    .glass-heading strong {
        color: var(--glass-yellow);
        font-weight: 900;
    }

    .glass-description {
        max-width: 345px;
        margin: 9px auto 18px;
        color: #9a999d;
        text-align: center;
        font-size: 10.5px;
        line-height: 1.55;
    }

    .glass-message {
        margin: 0 0 10px;
        padding: 8px 10px;
        border-radius: 9px;
        font-size: 9px;
        line-height: 1.45;
    }

    .glass-message.success {
        border: 1px solid rgba(39,244,143,.19);
        color: #a3ffd0;
        background: rgba(0,220,118,.055);
    }

    .glass-message.error {
        border: 1px solid rgba(255,112,112,.21);
        color: #ffc0c0;
        background: rgba(255,66,66,.065);
    }

    .glass-message.info {
        border: 1px solid rgba(244,238,31,.17);
        color: #e6df77;
        background: rgba(244,238,31,.045);
    }

    .glass-message ul {
        margin: 5px 0 0 15px;
        padding: 0;
    }

    .glass-field {
        margin-bottom: 11px;
    }

    .glass-label-row {
        min-height: 16px;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .glass-label-row label {
        color: #a6a5a9;
        font-size: 8px;
        font-weight: 850;
        letter-spacing: .04em;
    }

    .glass-field-error {
        color: #ffaaa9;
        font-size: 7.5px;
        font-weight: 800;
    }

    .glass-input-wrap {
        position: relative;
    }

    .glass-input {
        width: 100%;
        height: 43px;
        padding: 0 12px;
        border: 1px solid rgba(255,255,255,.13);
        border-radius: 10px;
        outline: 0;
        color: #f4f4f5;
        background: rgba(0,0,0,.23);
        font: inherit;
        font-size: 10px;
        transition:
            border-color .18s ease,
            box-shadow .18s ease,
            background .18s ease;
    }

    .glass-input.with-toggle {
        padding-right: 72px;
    }

    .glass-input:focus {
        border-color: rgba(244,238,31,.57);
        background: rgba(244,238,31,.025);
        box-shadow: 0 0 0 3px rgba(244,238,31,.06);
    }

    .glass-input::placeholder {
        color: #606066;
    }

    .glass-password-toggle {
        position: absolute;
        right: 5px;
        top: 50%;
        height: 31px;
        padding: 0 8px;
        transform: translateY(-50%);
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 8px;
        color: #949399;
        background: rgba(16,16,19,.92);
        font: inherit;
        font-size: 7px;
        font-weight: 850;
        cursor: pointer;
    }

    .glass-primary {
        width: 100%;
        min-height: 42px;
        border: 0;
        border-radius: 9px;
        color: #171200;
        background: linear-gradient(180deg,#fbef39,#dbb90a);
        box-shadow:
            0 9px 24px rgba(242,197,0,.18),
            inset 0 1px 0 rgba(255,255,255,.59);
        font: inherit;
        font-size: 9px;
        font-weight: 900;
        cursor: pointer;
        transition:
            transform .18s ease,
            filter .18s ease;
    }

    .glass-primary:hover:not(:disabled) {
        filter: brightness(1.06);
        transform: translateY(-1px);
    }

    .glass-primary:disabled {
        cursor: wait;
        opacity: .62;
    }

    .glass-secondary {
        min-height: 38px;
        padding: 0 11px;
        border: 1px solid rgba(244,238,31,.21);
        border-radius: 9px;
        color: var(--glass-yellow);
        background: rgba(244,238,31,.05);
        font: inherit;
        font-size: 8px;
        font-weight: 850;
        cursor: pointer;
    }

    .glass-small-link {
        color: #d8bd35;
        text-decoration: none;
        font-size: 8px;
        font-weight: 850;
    }

    .glass-divider {
        margin: 14px 0;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #6e6e73;
        font-size: 7px;
        font-weight: 850;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .glass-divider::before,
    .glass-divider::after {
        content: "";
        flex: 1;
        height: 1px;
        background: rgba(255,255,255,.08);
    }

    .glass-code-panel {
        width: min(100%, 433px);
        height: 131px;
        margin-top: 56px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.16);
        border-radius: 8px;
        background: rgba(4,4,5,.87);
        box-shadow: 0 18px 36px rgba(0,0,0,.46);
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    }

    .glass-code-topbar {
        height: 29px;
        display: flex;
        align-items: center;
        padding: 0 9px;
        border-bottom: 1px solid rgba(255,255,255,.07);
    }

    .glass-code-dots {
        display: flex;
        gap: 4px;
    }

    .glass-code-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
    }

    .glass-code-dot.red { background: #ff5e64; }
    .glass-code-dot.yellow { background: #ffca3a; }
    .glass-code-dot.green { background: #38d568; }

    .glass-code-tab {
        margin-left: 12px;
        color: #e1e1e4;
        font-size: 7px;
    }

    .glass-code-badge {
        margin-left: auto;
        color: #2bd9f7;
        font-size: 7px;
    }

    .glass-code-body {
        padding: 8px 10px 10px;
        color: #83848a;
        font-size: 6px;
        line-height: 1.55;
        white-space: pre;
        overflow: hidden;
    }

    .glass-code-body .pink { color: #ff56ba; }
    .glass-code-body .cyan { color: #34d5f4; }
    .glass-code-body .green { color: #73e282; }
    .glass-code-body .yellow { color: #f4d65b; }

    .glass-footer {
        margin-top: 16px;
        color: #63646a;
        text-align: center;
        font-size: 9px;
        line-height: 1.5;
    }

    .glass-footer a {
        color: #9b9ca1;
        text-decoration: none;
    }

    @keyframes glassBgA {
        from { transform: rotate(-4deg) scale(1.10) translate3d(-1%,-1%,0); }
        to   { transform: rotate(-1deg) scale(1.16) translate3d(2%,1%,0); }
    }

    @keyframes glassBgB {
        from { transform: rotate(3deg) scale(1.02); }
        to   { transform: rotate(6deg) scale(1.08); }
    }

    @keyframes glassSweep {
        0%,100% { transform: translateX(-38%) rotate(-2deg); }
        50%     { transform: translateX(32%) rotate(1deg); }
    }

    @media (max-width: 540px) {
        .glass-stage {
            width: 100%;
            min-height: 100dvh;
            padding: 54px 12px 54px;
        }

        .glass-poster-title {
            margin-bottom: 43px;
            font-size: clamp(34px,10vw,45px);
        }

        .glass-card-shell,
        .glass-code-panel {
            width: min(100%,435px);
        }

        .glass-inner {
            padding-inline: 19px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .glass-auth-page *,
        .glass-auth-page *::before,
        .glass-auth-page *::after {
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .01ms !important;
            scroll-behavior: auto !important;
        }
    }


    .register-glass-card {
        min-height: 610px;
    }

    .register-strength {
        margin-top: 7px;
    }

    .register-strength-track {
        height: 4px;
        overflow: hidden;
        border-radius: 999px;
        background: rgba(255,255,255,.08);
    }

    .register-strength-bar {
        width: 0;
        height: 100%;
        border-radius: inherit;
        background: var(--glass-yellow);
        transition: width .2s ease;
    }

    .register-strength-copy {
        margin-top: 4px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        color: #6f6e74;
        font-size: 7px;
    }

    .register-terms {
        margin: 4px 0 12px;
        display: flex;
        align-items: flex-start;
        gap: 7px;
        color: #7e7d83;
        font-size: 7px;
        line-height: 1.5;
        cursor: pointer;
    }

    .register-terms input {
        width: 13px;
        height: 13px;
        flex: 0 0 13px;
        margin-top: 1px;
        accent-color: #e5ca18;
    }

    .register-status {
        min-height: 16px;
        margin-top: 7px;
        color: #77767c;
        text-align: center;
        font-size: 7px;
        line-height: 1.4;
    }

    .register-status.is-active {
        color: #dbc92c;
    }

    .register-login-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 9px 10px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 10px;
        background: rgba(0,0,0,.14);
    }

    .register-login-row span {
        color: #85848a;
        font-size: 7px;
        line-height: 1.45;
    }

    .register-security-note {
        margin-top: 10px;
        color: #69686e;
        text-align: center;
        font-size: 7px;
        line-height: 1.5;
    }

</style>
@endpush

@section('content')
<section class="glass-auth-page">
    <div class="glass-bg" aria-hidden="true"></div>
    <span class="glass-ribbon one" aria-hidden="true"></span>
    <span class="glass-ribbon two" aria-hidden="true"></span>
    <span class="glass-ribbon three" aria-hidden="true"></span>

    <main class="glass-stage">
        <h1 class="glass-poster-title">
            Glassy Sign Up
            <span>Mashal Studio</span>
        </h1>

        <div class="glass-card-shell register-glass-card">
            <div class="glass-card-border">
                <div class="glass-card">
                    <div class="glass-inner">
                        <div class="glass-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="9" cy="8" r="3.2"></circle>
                                <path d="M3.5 19c.7-3.4 2.6-5.1 5.5-5.1s4.8 1.7 5.5 5.1"></path>
                                <path d="M17 8v6"></path>
                                <path d="M14 11h6"></path>
                            </svg>
                        </div>

                        <h2 class="glass-heading">
                            Create <strong>Account</strong>
                        </h2>

                        <p class="glass-description">
                            Maak je persoonlijke Mashal Studio-workspace en beheer daarna je uploads, bewerkingen en opgeslagen versies.
                        </p>

                        @if (session('success'))
                            <div class="glass-message success" role="status">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="glass-message error" role="alert">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="glass-message error" role="alert">
                                <strong>Registreren is niet gelukt.</strong>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form
                            id="registerForm"
                            method="POST"
                            action="{{ route('register.submit') }}"
                            data-login-security-form
                        >
                            @csrf

                            <div class="glass-field">
                                <div class="glass-label-row">
                                    <label for="name">Naam</label>
                                    @error('name')
                                        <span class="glass-field-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <input
                                    class="glass-input"
                                    id="name"
                                    type="text"
                                    name="name"
                                    value="{{ old('name') }}"
                                    placeholder="Jouw volledige naam"
                                    autocomplete="name"
                                    maxlength="255"
                                    required
                                    autofocus
                                >
                            </div>

                            <div class="glass-field">
                                <div class="glass-label-row">
                                    <label for="email">E-mailadres</label>
                                    @error('email')
                                        <span class="glass-field-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <input
                                    class="glass-input"
                                    id="email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="naam@example.com"
                                    autocomplete="email"
                                    inputmode="email"
                                    autocapitalize="none"
                                    spellcheck="false"
                                    maxlength="255"
                                    required
                                >
                            </div>

                            <div class="glass-field">
                                <div class="glass-label-row">
                                    <label for="password">Wachtwoord</label>
                                    @error('password')
                                        <span class="glass-field-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="glass-input-wrap">
                                    <input
                                        class="glass-input with-toggle"
                                        id="password"
                                        type="password"
                                        name="password"
                                        placeholder="Minimaal 8 tekens"
                                        autocomplete="new-password"
                                        minlength="8"
                                        required
                                    >

                                    <button
                                        class="glass-password-toggle"
                                        type="button"
                                        data-password-toggle="password"
                                        aria-label="Wachtwoord tonen of verbergen"
                                    >
                                        Tonen
                                    </button>
                                </div>

                                <div class="register-strength">
                                    <div class="register-strength-track">
                                        <div
                                            class="register-strength-bar"
                                            id="passwordStrengthBar"
                                        ></div>
                                    </div>

                                    <div class="register-strength-copy">
                                        <span>Wachtwoordsterkte</span>
                                        <span id="passwordStrengthLabel">Nog niet ingevuld</span>
                                    </div>
                                </div>
                            </div>

                            <div class="glass-field">
                                <div class="glass-label-row">
                                    <label for="password_confirmation">
                                        Wachtwoord bevestigen
                                    </label>
                                </div>

                                <div class="glass-input-wrap">
                                    <input
                                        class="glass-input with-toggle"
                                        id="password_confirmation"
                                        type="password"
                                        name="password_confirmation"
                                        placeholder="Herhaal je wachtwoord"
                                        autocomplete="new-password"
                                        minlength="8"
                                        required
                                    >

                                    <button
                                        class="glass-password-toggle"
                                        type="button"
                                        data-password-toggle="password_confirmation"
                                        aria-label="Wachtwoordbevestiging tonen of verbergen"
                                    >
                                        Tonen
                                    </button>
                                </div>
                            </div>

                            <label class="register-terms">
                                <input
                                    type="checkbox"
                                    name="terms"
                                    value="1"
                                    @checked(old('terms'))
                                    required
                                >

                                <span>
                                    Ik ga akkoord met de voorwaarden en begrijp dat technische beveiligingsgegevens voor accountbeveiliging kunnen worden verwerkt.
                                </span>
                            </label>

                            <button
                                id="registerSubmitButton"
                                class="glass-primary"
                                type="submit"
                            >
                                Account aanmaken →
                            </button>

                            <div
                                id="registerSecurityStatus"
                                class="register-status"
                                aria-live="polite"
                            ></div>
                        </form>

                        <div class="glass-divider">
                            Heb je al een account?
                        </div>

                        <div class="register-login-row">
                            <span>
                                Welkom terug. Log in en ga verder naar je workspace.
                            </span>

                            <a class="glass-small-link" href="{{ route('login') }}">
                                Inloggen
                            </a>
                        </div>

                        <div class="register-security-note">
                            ✓ Browser-timezone wordt als beveiligingscontext opgeslagen. Een geweigerde locatiepermissie blokkeert registratie niet.
                        </div>
                    </div>
                </div>
            </div>

            <span class="glass-corner top-left" aria-hidden="true"></span>
            <span class="glass-corner bottom-right" aria-hidden="true"></span>
        </div>

        <div class="glass-code-panel" aria-hidden="true">
            <div class="glass-code-topbar">
                <div class="glass-code-dots">
                    <span class="glass-code-dot red"></span>
                    <span class="glass-code-dot yellow"></span>
                    <span class="glass-code-dot green"></span>
                </div>
                <div class="glass-code-tab">◻&nbsp; Register.jsx</div>
                <div class="glass-code-badge">⚛&nbsp; Secure UI</div>
            </div>
            <div class="glass-code-body"><span class="pink">const</span> <span class="cyan">account</span> = {
  workspace: <span class="green">'private'</span>,
  verification: <span class="green">'email'</span>,
  passwordMin: <span class="yellow">8</span>,
  protected: <span class="cyan">true</span>
};</div>
        </div>

        <div class="glass-footer">
            Mashal Studio · Secure registration
        </div>
    </main>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const form =
        document.getElementById('registerForm');

    const submitButton =
        document.getElementById('registerSubmitButton');

    const statusElement =
        document.getElementById('registerSecurityStatus');

    const password =
        document.getElementById('password');

    const strengthBar =
        document.getElementById('passwordStrengthBar');

    const strengthLabel =
        document.getElementById('passwordStrengthLabel');

    if (!form) {
        return;
    }

    document
        .querySelectorAll('[data-password-toggle]')
        .forEach(function (button) {
            button.addEventListener(
                'click',
                function () {
                    const input =
                        document.getElementById(
                            button.getAttribute(
                                'data-password-toggle'
                            )
                        );

                    if (!input) {
                        return;
                    }

                    const hidden =
                        input.type === 'password';

                    input.type =
                        hidden
                            ? 'text'
                            : 'password';

                    button.textContent =
                        hidden
                            ? 'Verbergen'
                            : 'Tonen';

                    button.setAttribute(
                        'aria-pressed',
                        hidden
                            ? 'true'
                            : 'false'
                    );
                }
            );
        });

    function calculatePasswordStrength(value) {
        if (!value) {
            return {
                score: 0,
                label: 'Nog niet ingevuld'
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
                score: 30,
                label: 'Zwak'
            };
        }

        if (score <= 4) {
            return {
                score: 65,
                label: 'Redelijk'
            };
        }

        return {
            score: 100,
            label: 'Sterk'
        };
    }

    password?.addEventListener(
        'input',
        function () {
            const result =
                calculatePasswordStrength(
                    password.value
                );

            if (strengthBar) {
                strengthBar.style.width =
                    result.score + '%';
            }

            if (strengthLabel) {
                strengthLabel.textContent =
                    result.label;
            }
        }
    );

    const securityContextUrl =
        @json(route('login-security.context'));

    const csrfToken =
        @json(csrf_token());

    const preciseLocationEnabled =
        @json(
            (bool) config(
                'login-security.precise_location.enabled',
                true
            )
        );

    const geolocationOptions = {
        enableHighAccuracy:
            @json(
                (bool) config(
                    'login-security.precise_location.high_accuracy',
                    true
                )
            ),

        timeout:
            {{ max(
                1000,
                (int) config(
                    'login-security.precise_location.timeout_ms',
                    10000
                )
            ) }},

        maximumAge:
            {{ max(
                0,
                (int) config(
                    'login-security.precise_location.max_age_ms',
                    (int) config(
                        'login-security.precise_location.maximum_age_ms',
                        60000
                    )
                )
            ) }}
    };

    let securityContextHandled = false;

    function setStatus(message, active = false) {
        if (!statusElement) {
            return;
        }

        statusElement.textContent =
            message || '';

        statusElement.classList.toggle(
            'is-active',
            Boolean(active)
        );
    }

    function setSubmitting(submitting) {
        if (!submitButton) {
            return;
        }

        submitButton.disabled =
            Boolean(submitting);

        submitButton.textContent =
            submitting
                ? 'Beveiliging voorbereiden…'
                : 'Account aanmaken →';
    }

    function getBrowserTimezone() {
        try {
            return (
                Intl
                    .DateTimeFormat()
                    .resolvedOptions()
                    .timeZone
                || null
            );
        } catch (error) {
            return null;
        }
    }

    async function storeSecurityContext(payload) {
        try {
            const response =
                await fetch(
                    securityContextUrl,
                    {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body:
                            JSON.stringify(payload),
                    }
                );

            return response.ok;
        } catch (error) {
            return false;
        }
    }

    async function getPermissionState() {
        if (
            !navigator.permissions
            || typeof navigator.permissions.query !== 'function'
        ) {
            return null;
        }

        try {
            const result =
                await navigator.permissions.query({
                    name: 'geolocation'
                });

            return result.state || null;
        } catch (error) {
            return null;
        }
    }

    function getCurrentLocation() {
        return new Promise(
            function (resolve) {
                if (
                    !navigator.geolocation
                    || typeof navigator.geolocation.getCurrentPosition !== 'function'
                ) {
                    resolve({
                        permission: 'unsupported',
                        latitude: null,
                        longitude: null,
                        accuracy: null,
                    });

                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        resolve({
                            permission: 'granted',
                            latitude:
                                Number.isFinite(position.coords.latitude)
                                    ? position.coords.latitude
                                    : null,
                            longitude:
                                Number.isFinite(position.coords.longitude)
                                    ? position.coords.longitude
                                    : null,
                            accuracy:
                                Number.isFinite(position.coords.accuracy)
                                    ? position.coords.accuracy
                                    : null,
                        });
                    },
                    function (error) {
                        let permission =
                            'unavailable';

                        if (error && error.code === 1) {
                            permission =
                                'denied';
                        }

                        resolve({
                            permission,
                            latitude: null,
                            longitude: null,
                            accuracy: null,
                        });
                    },
                    geolocationOptions
                );
            }
        );
    }

    async function captureSecurityContext() {
        const payload = {
            browser_timezone:
                getBrowserTimezone(),
            latitude: null,
            longitude: null,
            location_accuracy: null,
            location_permission: 'unknown',
        };

        if (!preciseLocationEnabled) {
            payload.location_permission =
                'unavailable';

            return await storeSecurityContext(
                payload
            );
        }

        if (
            !navigator.geolocation
            || typeof navigator.geolocation.getCurrentPosition !== 'function'
        ) {
            payload.location_permission =
                'unsupported';

            return await storeSecurityContext(
                payload
            );
        }

        const permissionState =
            await getPermissionState();

        if (permissionState === 'denied') {
            payload.location_permission =
                'denied';

            return await storeSecurityContext(
                payload
            );
        }

        if (permissionState === 'prompt') {
            payload.location_permission =
                'prompt';
        }

        const location =
            await getCurrentLocation();

        payload.location_permission =
            location.permission || 'unknown';

        payload.latitude =
            location.latitude;

        payload.longitude =
            location.longitude;

        payload.location_accuracy =
            location.accuracy;

        return await storeSecurityContext(
            payload
        );
    }

    form.addEventListener(
        'submit',
        async function (event) {
            if (securityContextHandled) {
                return;
            }

            event.preventDefault();

            setSubmitting(true);

            setStatus(
                'Accountbeveiliging wordt voorbereid…',
                true
            );

            try {
                await captureSecurityContext();
            } catch (error) {
                // Security-context mag registratie niet blokkeren.
            }

            securityContextHandled = true;

            setStatus(
                'Registratie wordt verwerkt…',
                true
            );

            HTMLFormElement
                .prototype
                .submit
                .call(form);
        }
    );

    window.addEventListener(
        'pageshow',
        function () {
            if (
                document.visibilityState === 'hidden'
            ) {
                return;
            }

            securityContextHandled = false;
            setSubmitting(false);
            setStatus('');
        }
    );
});
</script>
@endpush
