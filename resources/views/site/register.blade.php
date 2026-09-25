@extends('layouts.site-layout')

@section('title', 'Registreren | Mashal Studio')

@section(
    'meta_description',
    'Maak een Mashal Studio-account aan met wachtwoord, e-mailcode, magic link of social login.'
)

@push('styles')
<style>
    :root {
        --register-bg: #050506;
        --register-panel: #0e0e11;
        --register-panel-2: #141418;
        --register-text: #f7f7f8;
        --register-muted: #96969e;
        --register-muted-2: #686870;
        --register-line: rgba(255,255,255,.09);
        --register-line-strong: rgba(255,255,255,.15);
        --register-gold: #f1d84a;
        --register-gold-2: #d8b91e;
        --register-orange: #ff7a24;
        --register-green: #45df8b;
        --register-danger: #ff8f8f;
        --register-radius: 24px;
        --register-shadow: 0 24px 70px rgba(0,0,0,.36);
    }

    .register-page,
    .register-page * {
        box-sizing: border-box;
    }

    .register-page {
        position: relative;
        width: 100%;
        min-height: calc(100dvh - var(--studio-header-height, 78px));
        overflow-x: clip;
        color: var(--register-text);
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

    .register-page::before {
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
        mask-image:
            linear-gradient(
                to bottom,
                #000,
                transparent 74%
            );
    }

    .register-shell {
        position: relative;
        z-index: 1;
        width: min(calc(100% - 32px), 1080px);
        margin-inline: auto;
        padding: clamp(34px, 5vw, 72px) 0 70px;
    }

    /*
    |--------------------------------------------------------------------------
    | Intro
    |--------------------------------------------------------------------------
    */

    .register-intro {
        width: min(100%, 590px);
        margin: 0 auto 26px;
        text-align: center;
        animation:
            register-enter .42s cubic-bezier(.2,.8,.2,1) both;
    }

    .register-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        color: var(--register-gold);
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .15em;
        text-transform: uppercase;
    }

    .register-eyebrow::before,
    .register-eyebrow::after {
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

    .register-eyebrow::after {
        transform: scaleX(-1);
    }

    .register-title {
        margin: 0;
        color: #fff;
        font-size: clamp(36px, 6vw, 62px);
        line-height: .96;
        font-weight: 930;
        letter-spacing: -.055em;
        text-wrap: balance;
    }

    .register-title span {
        display: block;
        margin-top: 5px;
        color: var(--register-orange);
    }

    .register-lead {
        max-width: 520px;
        margin: 16px auto 0;
        color: var(--register-muted);
        font-size: 12px;
        line-height: 1.7;
    }

    /*
    |--------------------------------------------------------------------------
    | Card
    |--------------------------------------------------------------------------
    */

    .register-card-wrap {
        width: min(100%, 500px);
        margin-inline: auto;
        animation:
            register-enter .46s .05s cubic-bezier(.2,.8,.2,1) both;
    }

    .register-card {
        position: relative;
        width: 100%;
        overflow: hidden;
        border: 1px solid var(--register-line-strong);
        border-radius: var(--register-radius);
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
        box-shadow: var(--register-shadow);
    }

    /*
     * Geen clip-path, geen glazen driehoeken en geen backdrop-filter.
     * Daardoor blijft de kaart ook rechts onder perfect rond en stabiel.
     */
    .register-card::before {
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

    .register-card::after {
        content: "";
        position: absolute;
        right: 0;
        bottom: 0;
        width: 68px;
        height: 68px;
        pointer-events: none;
        border-right: 2px solid rgba(241,216,74,.42);
        border-bottom: 2px solid rgba(241,216,74,.42);
        border-radius: 0 0 calc(var(--register-radius) - 1px) 0;
        opacity: .7;
    }

    .register-card-inner {
        position: relative;
        z-index: 1;
        padding: 27px;
    }

    .register-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 13px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(241,216,74,.22);
        border-radius: 14px;
        color: var(--register-gold);
        background: rgba(241,216,74,.045);
    }

    .register-icon svg {
        width: 23px;
        height: 23px;
    }

    .register-heading {
        margin: 0;
        text-align: center;
        color: #fff;
        font-size: 25px;
        line-height: 1.1;
        font-weight: 900;
        letter-spacing: -.035em;
    }

    .register-heading strong {
        color: var(--register-gold);
        font-weight: inherit;
    }

    .register-description {
        max-width: 380px;
        margin: 8px auto 18px;
        color: var(--register-muted);
        text-align: center;
        font-size: 10px;
        line-height: 1.6;
    }

    /*
    |--------------------------------------------------------------------------
    | Login / Register switch
    |--------------------------------------------------------------------------
    */

    .register-auth-switch {
        width: min(100%, 280px);
        margin: 0 auto 16px;
        padding: 3px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3px;
        border: 1px solid var(--register-line);
        border-radius: 11px;
        background: rgba(0,0,0,.22);
    }

    .register-auth-switch a {
        min-height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid transparent;
        border-radius: 8px;
        color: #777780;
        text-decoration: none;
        font-size: 8px;
        font-weight: 900;
        transition:
            color .15s ease,
            background .15s ease,
            border-color .15s ease,
            transform .15s ease;
    }

    .register-auth-switch a:hover {
        color: #e3d96a;
        transform: translateY(-1px);
    }

    .register-auth-switch a.active {
        border-color: rgba(241,216,74,.18);
        color: var(--register-gold);
        background: rgba(241,216,74,.055);
    }

    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    */

    .register-message {
        margin-bottom: 11px;
        padding: 10px 11px;
        border-radius: 10px;
        font-size: 9px;
        line-height: 1.55;
    }

    .register-message.success {
        border: 1px solid rgba(69,223,139,.18);
        color: #adf4c9;
        background: rgba(69,223,139,.055);
    }

    .register-message.error {
        border: 1px solid rgba(255,143,143,.18);
        color: #ffc2c2;
        background: rgba(255,90,90,.055);
    }

    .register-message.info {
        border: 1px solid rgba(241,216,74,.17);
        color: #e9dc7a;
        background: rgba(241,216,74,.045);
    }

    .register-message ul {
        margin: 5px 0 0 16px;
        padding: 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Method tabs
    |--------------------------------------------------------------------------
    */

    .register-tabs {
        margin: 12px 0 14px;
        padding: 3px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 3px;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 11px;
        background: rgba(0,0,0,.18);
    }

    .register-tab {
        min-height: 36px;
        padding: 0 7px;
        border: 1px solid transparent;
        border-radius: 8px;
        color: #74747c;
        background: transparent;
        font: inherit;
        font-size: 8px;
        font-weight: 900;
        cursor: pointer;
        transition:
            color .14s ease,
            background .14s ease,
            border-color .14s ease,
            transform .14s ease;
    }

    .register-tab:hover {
        color: #cfcfcf;
    }

    .register-tab.active {
        border-color: rgba(241,216,74,.18);
        color: var(--register-gold);
        background: rgba(241,216,74,.055);
    }

    .register-tab:active {
        transform: scale(.985);
    }

    .register-panel {
        display: none;
    }

    .register-panel.active {
        display: block;
        animation:
            register-panel-in .18s ease-out both;
    }

    /*
    |--------------------------------------------------------------------------
    | Fields
    |--------------------------------------------------------------------------
    */

    .register-field {
        margin-bottom: 12px;
    }

    .register-label-row {
        min-height: 17px;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .register-label-row label {
        color: #a8a8ae;
        font-size: 8px;
        font-weight: 850;
        letter-spacing: .035em;
    }

    .register-field-error {
        color: #ffacac;
        font-size: 7.5px;
        font-weight: 800;
    }

    .register-input-wrap {
        position: relative;
    }

    .register-input {
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

    .register-input::placeholder {
        color: #5c5c64;
    }

    .register-input:focus {
        border-color: rgba(241,216,74,.48);
        background: rgba(241,216,74,.02);
        box-shadow: 0 0 0 3px rgba(241,216,74,.055);
    }

    .register-input.with-toggle {
        padding-right: 78px;
    }

    .register-password-toggle {
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

    /*
    |--------------------------------------------------------------------------
    | Password strength
    |--------------------------------------------------------------------------
    */

    .register-strength {
        margin-top: 8px;
    }

    .register-strength-track {
        height: 5px;
        overflow: hidden;
        border-radius: 999px;
        background: rgba(255,255,255,.055);
    }

    .register-strength-bar {
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

    .register-strength-copy {
        margin-top: 6px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        color: #65656d;
        font-size: 7px;
    }

    .register-match {
        min-height: 18px;
        margin-top: 6px;
        color: #65656d;
        font-size: 7px;
    }

    .register-match.good {
        color: #86d4a1;
    }

    .register-match.bad {
        color: #e29b9b;
    }

    /*
    |--------------------------------------------------------------------------
    | Terms
    |--------------------------------------------------------------------------
    */

    .register-terms {
        margin: 3px 0 13px;
        display: flex;
        align-items: flex-start;
        gap: 8px;
        color: #85858d;
        font-size: 8px;
        line-height: 1.55;
        cursor: pointer;
    }

    .register-terms input {
        width: 14px;
        height: 14px;
        flex: 0 0 14px;
        margin-top: 1px;
        accent-color: var(--register-gold);
    }

    .register-terms a {
        color: #d8c64b;
        text-decoration: none;
        font-weight: 850;
    }

    /*
    |--------------------------------------------------------------------------
    | Buttons
    |--------------------------------------------------------------------------
    */

    .register-primary,
    .register-secondary {
        font: inherit;
        cursor: pointer;
    }

    .register-primary {
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
        font-size: 9px;
        font-weight: 950;
        transition:
            transform .15s ease,
            filter .15s ease;
    }

    .register-primary:hover:not(:disabled) {
        transform: translateY(-1px);
        filter: brightness(1.045);
    }

    .register-primary:active:not(:disabled) {
        transform: scale(.992);
    }

    .register-primary:disabled {
        opacity: .66;
        cursor: wait;
    }

    .register-secondary {
        min-height: 39px;
        padding: 0 12px;
        border: 1px solid rgba(241,216,74,.18);
        border-radius: 9px;
        color: var(--register-gold);
        background: rgba(241,216,74,.045);
        font-size: 8px;
        font-weight: 850;
    }

    /*
    |--------------------------------------------------------------------------
    | Passwordless
    |--------------------------------------------------------------------------
    */

    .register-passwordless-card {
        padding: 12px;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 12px;
        background: rgba(0,0,0,.16);
    }

    .register-passwordless-card + .register-passwordless-card {
        margin-top: 9px;
    }

    .register-passwordless-head {
        margin-bottom: 10px;
        display: flex;
        align-items: flex-start;
        gap: 9px;
    }

    .register-passwordless-icon {
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(241,216,74,.16);
        border-radius: 9px;
        color: var(--register-gold);
        background: rgba(241,216,74,.035);
        font-size: 9px;
        font-weight: 900;
    }

    .register-passwordless-copy strong,
    .register-passwordless-copy span {
        display: block;
    }

    .register-passwordless-copy strong {
        color: #e1e1e4;
        font-size: 8px;
    }

    .register-passwordless-copy span {
        margin-top: 3px;
        color: #787880;
        font-size: 7px;
        line-height: 1.5;
    }

    .register-passwordless-form {
        display: grid;
        grid-template-columns: minmax(0,1fr) auto;
        gap: 7px;
    }

    .register-passwordless-form .register-input {
        height: 39px;
    }

    /*
    |--------------------------------------------------------------------------
    | OAuth
    |--------------------------------------------------------------------------
    */

    .register-oauth-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .register-oauth {
        min-height: 56px;
        padding: 0 10px;
        display: flex;
        align-items: center;
        gap: 9px;
        border: 1px solid rgba(255,255,255,.095);
        border-radius: 11px;
        color: #e5e5e7;
        background: rgba(0,0,0,.18);
        text-decoration: none;
        transition:
            transform .14s ease,
            border-color .14s ease,
            background .14s ease;
    }

    .register-oauth:hover {
        transform: translateY(-1px);
        border-color: rgba(241,216,74,.18);
        background: rgba(241,216,74,.025);
    }

    .register-oauth:active {
        transform: scale(.99);
    }

    .register-oauth-icon {
        width: 27px;
        height: 27px;
        display: grid;
        place-items: center;
        flex: 0 0 27px;
    }

    .register-oauth-icon svg {
        width: 23px;
        height: 23px;
    }

    .register-oauth-copy {
        min-width: 0;
    }

    .register-oauth-copy strong,
    .register-oauth-copy span {
        display: block;
    }

    .register-oauth-copy strong {
        overflow: hidden;
        font-size: 8px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .register-oauth-copy span {
        margin-top: 2px;
        color: #717179;
        font-size: 6px;
    }

    .register-oauth-grid > .register-oauth:last-child:nth-child(odd) {
        grid-column: 1 / -1;
    }

    /*
    |--------------------------------------------------------------------------
    | Bottom
    |--------------------------------------------------------------------------
    */

    .register-divider {
        margin: 15px 0;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #6d6d74;
        font-size: 7px;
        font-weight: 850;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .register-divider::before,
    .register-divider::after {
        content: "";
        flex: 1;
        height: 1px;
        background: rgba(255,255,255,.075);
    }

    .register-login-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 11px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 11px;
        background: rgba(0,0,0,.14);
    }

    .register-login-row span {
        color: #85858d;
        font-size: 7px;
        line-height: 1.5;
    }

    .register-small-link {
        color: #d8c64b;
        text-decoration: none;
        font-size: 8px;
        font-weight: 850;
        flex: 0 0 auto;
    }

    .register-small-link:hover {
        color: #fff074;
    }

    .register-security-note {
        margin-top: 11px;
        color: #6c6c73;
        font-size: 7px;
        line-height: 1.55;
        text-align: center;
    }

    .register-footer {
        margin-top: 18px;
        color: #606067;
        text-align: center;
        font-size: 9px;
        animation:
            register-enter .46s .09s cubic-bezier(.2,.8,.2,1) both;
    }

    /*
    |--------------------------------------------------------------------------
    | Animations
    |--------------------------------------------------------------------------
    |
    | Alleen opacity + transform. Geen blur, clip-path, fold of moving ribbons.
    |
    */

    @keyframes register-enter {
        from {
            opacity: 0;
            transform: translate3d(0, 12px, 0) scale(.992);
        }

        to {
            opacity: 1;
            transform: translate3d(0, 0, 0) scale(1);
        }
    }

    @keyframes register-panel-in {
        from {
            opacity: 0;
            transform: translate3d(0, 5px, 0);
        }

        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media (max-width: 640px) {
        .register-shell {
            width: min(calc(100% - 20px), 1080px);
            padding-top: 26px;
            padding-bottom: 48px;
        }

        .register-intro {
            margin-bottom: 20px;
        }

        .register-title {
            font-size: clamp(34px, 12vw, 46px);
        }

        .register-lead {
            font-size: 11px;
        }

        .register-card-wrap {
            width: 100%;
            max-width: 500px;
        }

        .register-card-inner {
            padding: 22px 18px 24px;
        }

        .register-card::after {
            width: 52px;
            height: 52px;
        }

        .register-oauth-grid {
            gap: 7px;
        }
    }

    @media (max-width: 390px) {
        .register-shell {
            width: min(calc(100% - 14px), 1080px);
        }

        .register-card-inner {
            padding-inline: 14px;
        }

        .register-tabs {
            grid-template-columns: 1fr;
        }

        .register-oauth-grid {
            grid-template-columns: 1fr;
        }

        .register-oauth-grid > .register-oauth:last-child:nth-child(odd) {
            grid-column: auto;
        }

        .register-passwordless-form {
            grid-template-columns: 1fr;
        }

        .register-secondary {
            width: 100%;
        }

        .register-login-row {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .register-intro,
        .register-card-wrap,
        .register-footer,
        .register-panel.active {
            animation: none !important;
        }

        .register-page *,
        .register-page *::before,
        .register-page *::after {
            scroll-behavior: auto !important;
            transition-duration: .01ms !important;
        }
    }
</style>
@endpush

@section('content')
<section class="register-page">
    <main class="register-shell">
        <header class="register-intro">
            <div class="register-eyebrow">
                Secure workspace
            </div>

            <h1 class="register-title">
                Glassy Sign Up
                <span>Mashal Studio</span>
            </h1>

            <p class="register-lead">
                Maak je persoonlijke Mashal Studio-account aan met
                wachtwoord, e-mail of je favoriete social login.
            </p>
        </header>

        <div class="register-card-wrap">
            <section class="register-card" aria-labelledby="registerHeading">
                <div class="register-card-inner">
                    <div class="register-icon" aria-hidden="true">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <circle cx="9" cy="8" r="3.2"></circle>
                            <path d="M3.5 19c.7-3.4 2.6-5.1 5.5-5.1s4.8 1.7 5.5 5.1"></path>
                            <path d="M17 8v6"></path>
                            <path d="M14 11h6"></path>
                        </svg>
                    </div>

                    <h2 class="register-heading" id="registerHeading">
                        Create <strong>Account</strong>
                    </h2>

                    <p class="register-description">
                        Kies hoe je je nieuwe Mashal Studio-account wilt aanmaken.
                    </p>

                    <nav
                        class="register-auth-switch"
                        aria-label="Inloggen of registreren"
                    >
                        <a href="{{ route('login') }}">
                            Inloggen
                        </a>

                        <a
                            class="active"
                            href="{{ route('register') }}"
                            aria-current="page"
                        >
                            Registreren
                        </a>
                    </nav>

                    @if (session('success'))
                        <div class="register-message success" role="status">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="register-message error" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="register-message error" role="alert">
                            <strong>
                                Registreren is niet gelukt.
                            </strong>

                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div
                        class="register-tabs"
                        role="tablist"
                        aria-label="Registratiemethode kiezen"
                    >
                        <button
                            class="register-tab active"
                            type="button"
                            role="tab"
                            aria-selected="true"
                            aria-controls="register-panel-password"
                            data-register-tab="password"
                        >
                            Wachtwoord
                        </button>

                        <button
                            class="register-tab"
                            type="button"
                            role="tab"
                            aria-selected="false"
                            aria-controls="register-panel-email"
                            data-register-tab="email"
                        >
                            E-mail
                        </button>

                        <button
                            class="register-tab"
                            type="button"
                            role="tab"
                            aria-selected="false"
                            aria-controls="register-panel-social"
                            data-register-tab="social"
                        >
                            Social
                        </button>
                    </div>

                    <section
                        class="register-panel active"
                        id="register-panel-password"
                        role="tabpanel"
                        data-register-panel="password"
                    >
                        <form
                            id="registerForm"
                            method="POST"
                            action="{{ route('register.submit') }}"
                            data-register-security-form
                            data-auth-transition-form
                        >
                            @csrf

                            <div class="register-field">
                                <div class="register-label-row">
                                    <label for="name">
                                        Naam
                                    </label>

                                    @error('name')
                                        <span class="register-field-error">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <input
                                    class="register-input"
                                    id="name"
                                    type="text"
                                    name="name"
                                    value="{{ old('name') }}"
                                    placeholder="Jouw volledige naam"
                                    autocomplete="name"
                                    maxlength="255"
                                    required
                                >
                            </div>

                            <div class="register-field">
                                <div class="register-label-row">
                                    <label for="email">
                                        E-mailadres
                                    </label>

                                    @error('email')
                                        <span class="register-field-error">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <input
                                    class="register-input"
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

                            <div class="register-field">
                                <div class="register-label-row">
                                    <label for="password">
                                        Wachtwoord
                                    </label>

                                    @error('password')
                                        <span class="register-field-error">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="register-input-wrap">
                                    <input
                                        class="register-input with-toggle"
                                        id="password"
                                        type="password"
                                        name="password"
                                        placeholder="Minimaal 8 tekens"
                                        autocomplete="new-password"
                                        minlength="8"
                                        required
                                    >

                                    <button
                                        class="register-password-toggle"
                                        type="button"
                                        data-toggle-password="password"
                                        aria-label="Wachtwoord tonen of verbergen"
                                        aria-pressed="false"
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
                                        <span>
                                            Wachtwoordsterkte
                                        </span>

                                        <span id="passwordStrengthLabel">
                                            Nog niet ingevuld
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="register-field">
                                <div class="register-label-row">
                                    <label for="password_confirmation">
                                        Wachtwoord bevestigen
                                    </label>
                                </div>

                                <div class="register-input-wrap">
                                    <input
                                        class="register-input with-toggle"
                                        id="password_confirmation"
                                        type="password"
                                        name="password_confirmation"
                                        placeholder="Herhaal je wachtwoord"
                                        autocomplete="new-password"
                                        minlength="8"
                                        required
                                    >

                                    <button
                                        class="register-password-toggle"
                                        type="button"
                                        data-toggle-password="password_confirmation"
                                        aria-label="Wachtwoordbevestiging tonen of verbergen"
                                        aria-pressed="false"
                                    >
                                        Tonen
                                    </button>
                                </div>

                                <div
                                    class="register-match"
                                    id="passwordMatchStatus"
                                    aria-live="polite"
                                ></div>
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
                                    Ik ga akkoord met de
                                    @if (\Illuminate\Support\Facades\Route::has('terms'))
                                        <a href="{{ route('terms') }}">
                                            voorwaarden
                                        </a>
                                    @else
                                        voorwaarden
                                    @endif
                                    en begrijp dat mijn account via e-mail
                                    wordt geverifieerd.
                                </span>
                            </label>

                            <button
                                class="register-primary"
                                id="registerSubmitButton"
                                type="submit"
                            >
                                Account aanmaken →
                            </button>
                        </form>
                    </section>

                    <section
                        class="register-panel"
                        id="register-panel-email"
                        role="tabpanel"
                        data-register-panel="email"
                        hidden
                    >
                        <div class="register-passwordless-card">
                            <div class="register-passwordless-head">
                                <span
                                    class="register-passwordless-icon"
                                    aria-hidden="true"
                                >
                                    6
                                </span>

                                <div class="register-passwordless-copy">
                                    <strong>
                                        Registreren met e-mailcode
                                    </strong>

                                    <span>
                                        Ontvang een tijdelijke 6-cijferige code.
                                        Als het adres nog niet bestaat, kan de
                                        e-mailflow je account aanmaken.
                                    </span>
                                </div>
                            </div>

                            <form
                                class="register-passwordless-form"
                                method="POST"
                                action="{{ route('email-login.send') }}"
                                data-register-security-form
                                data-auth-transition-form
                            >
                                @csrf

                                <input
                                    class="register-input"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="naam@example.com"
                                    autocomplete="email"
                                    inputmode="email"
                                    required
                                >

                                <button
                                    class="register-secondary"
                                    type="submit"
                                >
                                    Stuur code
                                </button>
                            </form>
                        </div>

                        <div class="register-passwordless-card">
                            <div class="register-passwordless-head">
                                <span
                                    class="register-passwordless-icon"
                                    aria-hidden="true"
                                >
                                    ↗
                                </span>

                                <div class="register-passwordless-copy">
                                    <strong>
                                        Registreren met magic link
                                    </strong>

                                    <span>
                                        Ontvang een veilige eenmalige link en
                                        ga zonder wachtwoord verder.
                                    </span>
                                </div>
                            </div>

                            <form
                                class="register-passwordless-form"
                                method="POST"
                                action="{{ route('email-login.link.send') }}"
                                data-register-security-form
                                data-auth-transition-form
                            >
                                @csrf

                                <input
                                    class="register-input"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="naam@example.com"
                                    autocomplete="email"
                                    inputmode="email"
                                    required
                                >

                                <button
                                    class="register-secondary"
                                    type="submit"
                                >
                                    Stuur link
                                </button>
                            </form>
                        </div>
                    </section>

                    <section
                        class="register-panel"
                        id="register-panel-social"
                        role="tabpanel"
                        data-register-panel="social"
                        hidden
                    >
                        <div class="register-oauth-grid">
                            @if (\Illuminate\Support\Facades\Route::has('google.redirect'))
                                <a
                                    class="register-oauth"
                                    data-register-security-oauth
                                    data-auth-transition-link
                                    href="{{ route('google.redirect') }}"
                                    aria-label="Account maken met Google"
                                >
                                    <span class="register-oauth-icon">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path fill="#4285F4" d="M21.805 10.023h-9.18v3.955h5.28c-.228 1.273-.918 2.352-1.956 3.078v2.559h3.168c1.855-1.708 2.928-4.227 2.928-7.219 0-.8-.072-1.57-.24-2.373Z"/>
                                            <path fill="#34A853" d="M12.625 22c2.65 0 4.873-.875 6.492-2.385l-3.168-2.559c-.88.59-2.003.94-3.324.94-2.55 0-4.71-1.724-5.486-4.04H3.865v2.64A9.812 9.812 0 0 0 12.625 22Z"/>
                                            <path fill="#FBBC05" d="M7.139 13.956a5.96 5.96 0 0 1 0-3.912V7.405H3.865A9.82 9.82 0 0 0 2.82 12c0 1.585.38 3.086 1.045 4.595l3.274-2.639Z"/>
                                            <path fill="#EA4335" d="M12.625 6.004c1.44 0 2.733.495 3.75 1.468l2.813-2.813C17.493 3.076 15.27 2 12.625 2a9.812 9.812 0 0 0-8.76 5.405l3.274 2.639c.776-2.316 2.936-4.04 5.486-4.04Z"/>
                                        </svg>
                                    </span>

                                    <span class="register-oauth-copy">
                                        <strong>Google / Gmail</strong>
                                        <span>Account maken</span>
                                    </span>
                                </a>
                            @endif

                            @if (\Illuminate\Support\Facades\Route::has('github.redirect'))
                                <a
                                    class="register-oauth"
                                    data-register-security-oauth
                                    data-auth-transition-link
                                    href="{{ route('github.redirect') }}"
                                    aria-label="Account maken met GitHub"
                                >
                                    <span class="register-oauth-icon">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path fill="currentColor" d="M12 .7a11.5 11.5 0 0 0-3.636 22.41c.575.105.786-.25.786-.555 0-.274-.01-1-.016-1.962-3.198.695-3.874-1.541-3.874-1.541-.523-1.329-1.277-1.683-1.277-1.683-1.044-.714.08-.699.08-.699 1.154.081 1.761 1.185 1.761 1.185 1.026 1.758 2.692 1.25 3.348.956.104-.743.402-1.25.73-1.537-2.553-.29-5.237-1.276-5.237-5.68 0-1.255.449-2.281 1.184-3.085-.118-.291-.513-1.462.113-3.048 0 0 .965-.309 3.162 1.179A10.98 10.98 0 0 1 12 8.253c.977.004 1.961.132 2.88.387 2.195-1.488 3.158-1.179 3.158-1.179.628 1.586.233 2.757.115 3.048.737.804 1.182 1.83 1.182 3.085 0 4.415-2.688 5.387-5.249 5.671.413.356.78 1.057.78 2.13 0 1.538-.014 2.778-.014 3.155 0 .308.207.666.792.553A11.502 11.502 0 0 0 12 .7Z"/>
                                        </svg>
                                    </span>

                                    <span class="register-oauth-copy">
                                        <strong>GitHub</strong>
                                        <span>Account maken</span>
                                    </span>
                                </a>
                            @endif

                            @if (\Illuminate\Support\Facades\Route::has('facebook.redirect'))
                                <a
                                    class="register-oauth"
                                    data-register-security-oauth
                                    data-auth-transition-link
                                    href="{{ route('facebook.redirect') }}"
                                    aria-label="Account maken met Facebook"
                                >
                                    <span
                                        class="register-oauth-icon"
                                        style="color:#1877f2"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path fill="currentColor" d="M13.6 22v-8h2.68l.4-3.12H13.6V8.89c0-.9.25-1.52 1.54-1.52h1.65V4.58a22.1 22.1 0 0 0-2.4-.12c-2.38 0-4.01 1.45-4.01 4.12v2.3H7.69V14h2.69v8h3.22Z"/>
                                        </svg>
                                    </span>

                                    <span class="register-oauth-copy">
                                        <strong>Facebook</strong>
                                        <span>Account maken</span>
                                    </span>
                                </a>
                            @endif

                            @if (\Illuminate\Support\Facades\Route::has('tiktok.redirect'))
                                <a
                                    class="register-oauth"
                                    data-register-security-oauth
                                    data-auth-transition-link
                                    href="{{ route('tiktok.redirect') }}"
                                    aria-label="Account maken met TikTok"
                                >
                                    <span class="register-oauth-icon">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path fill="#25F4EE" d="M15.62 3.2c.39 2.31 1.7 3.69 3.98 3.84v2.63a7.9 7.9 0 0 1-3.94-.99v5.16c0 4.64-5.05 6.1-7.08 2.77-1.3-2.13-.5-5.87 3.67-6.03v2.77c-.38.06-.78.16-1.15.29-1.11.42-1.74 1.22-1.56 2.12.35 1.72 3.39 2.23 3.88-.26.08-.45.07-.9.07-1.36V3.2h2.13Z"/>
                                            <path fill="#FE2C55" d="M16.32 2.6c.39 2.31 1.7 3.69 3.98 3.84v2.63a7.9 7.9 0 0 1-3.94-.99v5.16c0 4.64-5.05 6.1-7.08 2.77-1.3-2.13-.5-5.87 3.67-6.03v2.77c-.38.06-.78.16-1.15.29-1.11.42-1.74 1.22-1.56 2.12.35 1.72 3.39 2.23 3.88-.26.08-.45.07-.9.07-1.36V2.6h2.13Z"/>
                                        </svg>
                                    </span>

                                    <span class="register-oauth-copy">
                                        <strong>TikTok</strong>
                                        <span>Account maken</span>
                                    </span>
                                </a>
                            @endif

                            @if (\Illuminate\Support\Facades\Route::has('linkedin.redirect'))
                                <a
                                    class="register-oauth"
                                    data-register-security-oauth
                                    data-auth-transition-link
                                    href="{{ route('linkedin.redirect') }}"
                                    aria-label="Account maken met LinkedIn"
                                >
                                    <span
                                        class="register-oauth-icon"
                                        aria-hidden="true"
                                    >
                                        <span
                                            style="
                                                display:grid;
                                                place-items:center;
                                                width:22px;
                                                height:22px;
                                                border-radius:5px;
                                                background:#0A66C2;
                                                color:#fff;
                                                font-size:12px;
                                                font-weight:900;
                                                letter-spacing:-.04em;
                                            "
                                        >
                                            in
                                        </span>
                                    </span>

                                    <span class="register-oauth-copy">
                                        <strong>LinkedIn</strong>
                                        <span>Account maken</span>
                                    </span>
                                </a>
                            @endif
                        </div>
                    </section>

                    <div class="register-divider">
                        Al een Mashal-account?
                    </div>

                    <div class="register-login-row">
                        <span>
                            Gebruik dezelfde methodes om veilig in te loggen.
                        </span>

                        <a
                            class="register-small-link"
                            href="{{ route('login') }}"
                        >
                            Naar inloggen
                        </a>
                    </div>

                    <div class="register-security-note">
                        ✓ Na wachtwoordregistratie bevestig je eerst je
                        e-mailadres voordat je account volledig actief is.
                    </div>
                </div>
            </section>

            <div class="register-footer">
                Mashal Studio · Secure registration
            </div>
        </div>
    </main>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

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
    | Registration method tabs
    |--------------------------------------------------------------------------
    */

    const tabs =
        Array.from(
            document.querySelectorAll(
                '[data-register-tab]'
            )
        );

    const panels =
        Array.from(
            document.querySelectorAll(
                '[data-register-panel]'
            )
        );

    function selectRegisterMethod(name) {
        tabs.forEach(function (tab) {
            const active =
                tab.dataset.registerTab === name;

            tab.classList.toggle(
                'active',
                active
            );

            tab.setAttribute(
                'aria-selected',
                active
                    ? 'true'
                    : 'false'
            );
        });

        panels.forEach(function (panel) {
            const active =
                panel.dataset.registerPanel === name;

            panel.classList.toggle(
                'active',
                active
            );

            panel.hidden =
                !active;
        });
    }

    tabs.forEach(function (tab) {
        tab.addEventListener(
            'click',
            function () {
                selectRegisterMethod(
                    tab.dataset.registerTab
                );
            }
        );
    });

    /*
    |--------------------------------------------------------------------------
    | Password strength + confirmation
    |--------------------------------------------------------------------------
    */

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
    | Authentication transition marker
    |--------------------------------------------------------------------------
    |
    | Alleen opslagmarker. Geen kunstmatige animatievertraging vóór navigatie.
    |
    */

    const authTransitionKey =
        'mashal_auth_success_pending';

    function markAuthTransition(source) {
        try {
            window.localStorage.setItem(
                authTransitionKey,
                JSON.stringify({
                    source:
                        String(
                            source ||
                            'register'
                        ),
                    createdAt:
                        Date.now(),
                })
            );
        } catch (error) {
            // Registratie mag nooit blokkeren.
        }
    }

    @if ($errors->any() || session('error'))
        try {
            window.localStorage.removeItem(
                authTransitionKey
            );
        } catch (error) {
            //
        }
    @endif

    document
        .querySelectorAll(
            'form[data-auth-transition-form]'
        )
        .forEach(function (form) {
            form.addEventListener(
                'submit',
                function () {
                    markAuthTransition(
                        form.getAttribute('action')
                        || 'register-form'
                    );

                    const submit =
                        form.querySelector(
                            '[type="submit"]'
                        );

                    if (!submit) {
                        return;
                    }

                    submit.disabled =
                        true;

                    if (
                        !submit.dataset.originalText
                    ) {
                        submit.dataset.originalText =
                            submit.textContent.trim();
                    }

                    submit.textContent =
                        'Bezig…';
                }
            );
        });

    document
        .querySelectorAll(
            'a[data-auth-transition-link]'
        )
        .forEach(function (link) {
            link.addEventListener(
                'click',
                function () {
                    markAuthTransition(
                        link.getAttribute('href')
                        || 'social-register'
                    );
                }
            );
        });

    /*
    |--------------------------------------------------------------------------
    | Login security context - background only
    |--------------------------------------------------------------------------
    |
    | De oude registratie wachtte op geolocation + een fetch voordat het
    | formulier of OAuth mocht doorgaan. Nu gebeurt dit vooraf en alleen in de
    | achtergrond. Er wordt nooit een locatie-popup geopend wanneer toestemming
    | nog niet eerder is gegeven.
    |
    */

    const securityContextUrl =
        @json(route('login-security.context'));

    const csrfToken =
        @json(csrf_token());

    function browserTimezone() {
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

    async function locationPermissionState() {
        if (
            !navigator.permissions
            || typeof navigator.permissions.query
                !== 'function'
        ) {
            return 'unknown';
        }

        try {
            const result =
                await navigator.permissions.query({
                    name: 'geolocation'
                });

            return result?.state
                || 'unknown';
        } catch (error) {
            return 'unknown';
        }
    }

    function grantedLocation() {
        return new Promise(
            function (resolve) {
                if (
                    !navigator.geolocation
                    || typeof navigator
                        .geolocation
                        .getCurrentPosition
                        !== 'function'
                ) {
                    resolve(null);
                    return;
                }

                navigator.geolocation
                    .getCurrentPosition(
                        function (position) {
                            const coords =
                                position?.coords;

                            if (!coords) {
                                resolve(null);
                                return;
                            }

                            resolve({
                                latitude:
                                    Number(
                                        coords.latitude
                                    ),
                                longitude:
                                    Number(
                                        coords.longitude
                                    ),
                                location_accuracy:
                                    Number.isFinite(
                                        Number(
                                            coords.accuracy
                                        )
                                    )
                                        ? Number(
                                            coords.accuracy
                                        )
                                        : null,
                            });
                        },
                        function () {
                            resolve(null);
                        },
                        {
                            enableHighAccuracy:
                                false,
                            timeout:
                                1400,
                            maximumAge:
                                120000,
                        }
                    );
            }
        );
    }

    async function storeSecurityContext() {
        try {
            const permission =
                await locationPermissionState();

            let location =
                null;

            if (
                permission ===
                'granted'
            ) {
                location =
                    await grantedLocation();
            }

            await fetch(
                securityContextUrl,
                {
                    method: 'POST',
                    credentials:
                        'same-origin',
                    keepalive:
                        true,
                    headers: {
                        'Accept':
                            'application/json',
                        'Content-Type':
                            'application/json',
                        'X-CSRF-TOKEN':
                            csrfToken,
                        'X-Requested-With':
                            'XMLHttpRequest',
                    },
                    body:
                        JSON.stringify({
                            browser_timezone:
                                browserTimezone(),
                            latitude:
                                location?.latitude
                                ?? null,
                            longitude:
                                location?.longitude
                                ?? null,
                            location_accuracy:
                                location?.location_accuracy
                                ?? null,
                            location_permission:
                                permission,
                        }),
                }
            );
        } catch (error) {
            // Registratie blijft altijd beschikbaar.
        }
    }

    if (
        'requestIdleCallback'
        in window
    ) {
        window.requestIdleCallback(
            function () {
                void storeSecurityContext();
            },
            {
                timeout: 900
            }
        );
    } else {
        window.setTimeout(
            function () {
                void storeSecurityContext();
            },
            250
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Back-forward cache reset
    |--------------------------------------------------------------------------
    */

    window.addEventListener(
        'pageshow',
        function () {
            document
                .querySelectorAll(
                    'form[data-auth-transition-form]'
                )
                .forEach(function (form) {
                    const submit =
                        form.querySelector(
                            '[type="submit"]'
                        );

                    if (!submit) {
                        return;
                    }

                    submit.disabled =
                        false;

                    if (
                        submit.dataset.originalText
                    ) {
                        submit.textContent =
                            submit.dataset.originalText;
                    }
                });
        }
    );

    updateStrength();
    updateMatchStatus();
});
</script>
@endpush
