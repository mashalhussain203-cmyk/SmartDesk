@extends('layouts.site-layout')

@section('title', 'Registreren | Mashal Studio')

@section(
    'meta_description',
    'Maak een Mashal Studio-account aan en beheer je persoonlijke afbeeldingen, bewerkingen en opgeslagen versies.'
)

@push('styles')
<style>
    :root {
        --register-bg: #07080b;
        --register-panel: #0d1015;
        --register-panel-2: #12161d;
        --register-text: #f7f7f4;
        --register-muted: #808792;
        --register-muted-2: #5d6570;
        --register-line: rgba(255,255,255,.072);
        --register-gold: #e3b36b;
        --register-gold-light: #f3d69a;
        --register-success: #67d990;
        --register-danger: #f47d7d;
    }

    .register-page {
        position: relative;
        min-height: calc(100vh - 76px);
        overflow: hidden;
        color: var(--register-text);
        background:
            radial-gradient(circle at 12% 10%, rgba(227,179,107,.08), transparent 30rem),
            radial-gradient(circle at 88% 8%, rgba(112,93,255,.055), transparent 31rem),
            linear-gradient(180deg,#07080b,#090b0f);
    }

    .register-page::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        opacity: .11;
        background-image:
            linear-gradient(rgba(255,255,255,.02) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,.02) 1px, transparent 1px);
        background-size: 72px 72px;
        mask-image: linear-gradient(to bottom,#000,transparent 84%);
    }

    .register-layout {
        position: relative;
        z-index: 2;
        width: min(calc(100% - 40px), 1420px);
        min-height: calc(100vh - 76px);
        margin-inline: auto;
        padding: 42px 0 64px;
        display: grid;
        grid-template-columns: minmax(0,1.04fr) minmax(440px,.96fr);
        gap: 24px;
        align-items: stretch;
    }

    /*
    |--------------------------------------------------------------------------
    | Showcase
    |--------------------------------------------------------------------------
    */

    .register-showcase {
        position: relative;
        min-height: 720px;
        padding: clamp(34px,4.7vw,62px);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 30px;
        background:
            radial-gradient(circle at 78% 15%, rgba(227,179,107,.13), transparent 20rem),
            radial-gradient(circle at 18% 82%, rgba(93,111,255,.09), transparent 24rem),
            linear-gradient(145deg, rgba(255,255,255,.038), rgba(255,255,255,.007)),
            #0b0e13;
        box-shadow: 0 40px 110px rgba(0,0,0,.33);
        isolation: isolate;
    }

    .register-showcase::before {
        content: "M";
        position: absolute;
        z-index: -1;
        right: -42px;
        bottom: -150px;
        color: rgba(255,255,255,.017);
        font-size: 455px;
        font-weight: 950;
        line-height: .8;
        letter-spacing: -.09em;
        pointer-events: none;
    }

    .register-showcase::after {
        content: "";
        position: absolute;
        z-index: -2;
        width: 420px;
        height: 420px;
        right: -175px;
        top: 18%;
        border: 1px solid rgba(227,179,107,.065);
        border-radius: 50%;
        box-shadow:
            0 0 0 75px rgba(227,179,107,.011),
            0 0 0 150px rgba(227,179,107,.006);
    }

    .register-kicker {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: #d9aa65;
        font-size: 9px;
        font-weight: 950;
        letter-spacing: .19em;
        text-transform: uppercase;
    }

    .register-kicker::before {
        content: "";
        width: 31px;
        height: 1px;
        background: linear-gradient(90deg,#e2b36c,transparent);
    }

    .register-headline {
        max-width: 760px;
        margin: 18px 0 0;
        color: #f9f9f6;
        font-size: clamp(56px,6.2vw,92px);
        line-height: .91;
        letter-spacing: -.072em;
        font-weight: 950;
        text-wrap: balance;
    }

    .register-headline span {
        display: block;
        color: #f1cf91;
    }

    .register-intro {
        max-width: 625px;
        margin: 24px 0 0;
        color: #9198a2;
        font-size: 13px;
        line-height: 1.86;
    }

    .register-trust {
        margin-top: 28px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .register-trust-pill {
        min-height: 30px;
        padding: 0 10px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 999px;
        color: #7e8590;
        background: rgba(255,255,255,.013);
        font-size: 7px;
        font-weight: 900;
        letter-spacing: .05em;
    }

    .register-trust-pill::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--register-success);
        box-shadow: 0 0 0 4px rgba(103,217,144,.05);
    }

    /*
    |--------------------------------------------------------------------------
    | Workspace preview
    |--------------------------------------------------------------------------
    */

    .register-preview {
        margin-top: 42px;
        padding: 13px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 21px;
        background: rgba(7,9,12,.63);
        box-shadow: 0 30px 85px rgba(0,0,0,.35);
        backdrop-filter: blur(15px);
    }

    .register-preview-top {
        min-height: 44px;
        padding: 0 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border-bottom: 1px solid rgba(255,255,255,.05);
    }

    .register-preview-dots {
        display: flex;
        gap: 5px;
    }

    .register-preview-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: rgba(255,255,255,.13);
    }

    .register-preview-dot:first-child {
        background: rgba(227,179,107,.63);
    }

    .register-preview-title,
    .register-preview-secure {
        color: #606873;
        font-size: 6px;
        font-weight: 950;
        letter-spacing: .10em;
        text-transform: uppercase;
    }

    .register-preview-secure {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .register-preview-secure::before {
        content: "";
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: var(--register-success);
    }

    .register-preview-body {
        min-height: 300px;
        padding: 18px;
        display: grid;
        grid-template-columns: repeat(3, minmax(0,1fr));
        gap: 10px;
        align-content: center;
        background:
            linear-gradient(45deg, rgba(255,255,255,.01) 25%, transparent 25%),
            linear-gradient(-45deg, rgba(255,255,255,.01) 25%, transparent 25%),
            linear-gradient(45deg, transparent 75%, rgba(255,255,255,.01) 75%),
            linear-gradient(-45deg, transparent 75%, rgba(255,255,255,.01) 75%),
            #0a0c10;
        background-size: 20px 20px;
        background-position: 0 0, 0 10px, 10px -10px, -10px 0;
    }

    .register-preview-card {
        position: relative;
        overflow: hidden;
        aspect-ratio: 1;
        border: 1px solid rgba(255,255,255,.06);
        border-radius: 14px;
        background:
            radial-gradient(circle at 70% 25%, rgba(227,179,107,.26), transparent 6rem),
            radial-gradient(circle at 28% 72%, rgba(93,111,255,.15), transparent 7rem),
            linear-gradient(145deg,#242a34,#0d1116);
    }

    .register-preview-card:nth-child(2) {
        background:
            radial-gradient(circle at 35% 35%, rgba(103,217,144,.16), transparent 6rem),
            linear-gradient(145deg,#1e2524,#0d1211);
    }

    .register-preview-card:nth-child(3) {
        background:
            radial-gradient(circle at 65% 30%, rgba(112,93,255,.22), transparent 6rem),
            linear-gradient(145deg,#1a1d29,#0c0f17);
    }

    .register-preview-label {
        position: absolute;
        left: 9px;
        bottom: 9px;
        min-height: 23px;
        padding: 0 7px;
        display: inline-flex;
        align-items: center;
        border-radius: 7px;
        color: #d7dae0;
        background: rgba(6,8,11,.67);
        font-size: 6px;
        font-weight: 900;
    }

    .register-preview-footer {
        min-height: 42px;
        padding: 0 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        border-top: 1px solid rgba(255,255,255,.045);
        color: #525a64;
        font-size: 6px;
        font-weight: 800;
    }

    .register-preview-footer span:first-child {
        color: #86c998;
    }

    .showcase-stats {
        margin-top: 28px;
        display: grid;
        grid-template-columns: repeat(3,minmax(0,1fr));
        gap: 8px;
    }

    .showcase-stat {
        padding: 13px;
        border: 1px solid rgba(255,255,255,.05);
        border-radius: 12px;
        background: rgba(255,255,255,.01);
    }

    .showcase-stat small {
        display: block;
        color: #4c545e;
        font-size: 6px;
        font-weight: 950;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .showcase-stat strong {
        display: block;
        margin-top: 5px;
        color: #a9b0b8;
        font-size: 8px;
    }

    /*
    |--------------------------------------------------------------------------
    | Registration panel
    |--------------------------------------------------------------------------
    */

    .register-panel {
        min-width: 0;
        padding: 30px;
        display: flex;
        align-items: stretch;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 30px;
        background:
            radial-gradient(circle at 84% 6%, rgba(227,179,107,.065), transparent 17rem),
            linear-gradient(180deg, rgba(255,255,255,.023), rgba(255,255,255,.005)),
            #0b0e13;
        box-shadow: 0 40px 110px rgba(0,0,0,.27);
    }

    .register-shell {
        width: 100%;
        max-width: 550px;
        margin: auto;
    }

    .register-brand {
        margin-bottom: 26px;
        display: flex;
        align-items: center;
        gap: 11px;
    }

    .register-brand-mark {
        width: 44px;
        height: 44px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(227,179,107,.17);
        border-radius: 14px;
        color: #171009;
        background: linear-gradient(145deg,#f1d193,#c98e47);
        box-shadow: 0 14px 34px rgba(227,179,107,.16);
        font-size: 15px;
        font-weight: 950;
    }

    .register-brand-copy strong,
    .register-brand-copy span {
        display: block;
    }

    .register-brand-copy strong {
        color: #f1f2f3;
        font-size: 13px;
    }

    .register-brand-copy span {
        margin-top: 4px;
        color: #5d6570;
        font-size: 6px;
        font-weight: 900;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .register-section-kicker {
        display: block;
        margin-bottom: 8px;
        color: #a37c48;
        font-size: 7px;
        font-weight: 950;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .register-title {
        margin: 0;
        color: #f5f6f7;
        font-size: clamp(38px,4vw,52px);
        line-height: .98;
        letter-spacing: -.057em;
        font-weight: 950;
    }

    .register-subtitle {
        max-width: 520px;
        margin: 12px 0 22px;
        color: #727984;
        font-size: 10px;
        line-height: 1.75;
    }

    .register-message {
        margin-bottom: 13px;
        padding: 12px 13px;
        border-radius: 11px;
        font-size: 8px;
        line-height: 1.65;
    }

    .register-message.success {
        border: 1px solid rgba(103,217,144,.12);
        color: #a2d9b3;
        background: rgba(103,217,144,.035);
    }

    .register-message.error {
        border: 1px solid rgba(240,131,131,.12);
        color: #dda2a2;
        background: rgba(240,131,131,.035);
    }

    .register-message ul {
        margin: 6px 0 0 15px;
        padding: 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Form
    |--------------------------------------------------------------------------
    */

    .register-form-card {
        padding: 17px;
        border: 1px solid rgba(255,255,255,.055);
        border-radius: 16px;
        background: rgba(255,255,255,.01);
    }

    .register-field {
        margin-bottom: 13px;
    }

    .register-label-row {
        margin-bottom: 7px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .register-label-row label {
        color: #959ca5;
        font-size: 7px;
        font-weight: 900;
        letter-spacing: .06em;
    }

    .field-error {
        color: #dc9292;
        font-size: 7px;
        font-weight: 850;
    }

    .register-input-wrap {
        position: relative;
    }

    .register-input {
        width: 100%;
        min-height: 49px;
        padding: 0 13px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 11px;
        outline: none;
        color: #e5e7ea;
        background: rgba(255,255,255,.015);
        font-size: 9px;
        transition:
            border-color .2s ease,
            background .2s ease,
            box-shadow .2s ease;
    }

    .register-input.with-toggle {
        padding-right: 72px;
    }

    .register-input:focus {
        border-color: rgba(227,179,107,.27);
        background: rgba(227,179,107,.019);
        box-shadow: 0 0 0 4px rgba(227,179,107,.035);
    }

    .register-input::placeholder {
        color: #4b535d;
    }

    .password-toggle {
        position: absolute;
        right: 6px;
        top: 50%;
        min-height: 33px;
        padding: 0 8px;
        transform: translateY(-50%);
        border: 1px solid rgba(255,255,255,.045);
        border-radius: 8px;
        color: #626a74;
        background: #101319;
        font-size: 6px;
        font-weight: 900;
        cursor: pointer;
    }

    .password-toggle:hover {
        border-color: rgba(227,179,107,.11);
        color: #d1a35f;
    }

    .password-strength {
        margin-top: 8px;
    }

    .password-strength-track {
        height: 5px;
        overflow: hidden;
        border-radius: 999px;
        background: rgba(255,255,255,.055);
    }

    .password-strength-bar {
        width: 0;
        height: 100%;
        border-radius: inherit;
        background: #707780;
        transition: width .2s ease;
    }

    .password-strength-copy {
        margin-top: 5px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        color: #515964;
        font-size: 6px;
    }

    .register-options {
        margin: 3px 0 14px;
    }

    .terms-label {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        color: #707781;
        font-size: 7px;
        line-height: 1.6;
        cursor: pointer;
    }

    .terms-label input {
        width: 14px;
        height: 14px;
        flex: 0 0 14px;
        margin-top: 1px;
        accent-color: #d5a05a;
    }

    .register-submit {
        width: 100%;
        min-height: 49px;
        padding: 0 16px;
        border: 0;
        border-radius: 11px;
        color: #171009;
        background: linear-gradient(135deg,#f1d193,#d39a50);
        box-shadow: 0 16px 36px rgba(227,179,107,.13);
        font-size: 8px;
        font-weight: 950;
        cursor: pointer;
        transition:
            transform .2s ease,
            box-shadow .2s ease;
    }

    .register-submit:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 21px 46px rgba(227,179,107,.20);
    }

    .register-submit:disabled {
        opacity: .60;
        cursor: wait;
        transform: none;
    }

    .register-security-status {
        min-height: 18px;
        margin-top: 9px;
        color: #535b65;
        font-size: 6px;
        line-height: 1.5;
        text-align: center;
    }

    .register-security-status.is-active {
        color: #c69d62;
    }

    /*
    |--------------------------------------------------------------------------
    | Existing account
    |--------------------------------------------------------------------------
    */

    .register-divider {
        margin: 18px 0;
        display: flex;
        align-items: center;
        gap: 11px;
        color: #4b535d;
        font-size: 6px;
        font-weight: 900;
        letter-spacing: .11em;
        text-transform: uppercase;
    }

    .register-divider::before,
    .register-divider::after {
        content: "";
        flex: 1;
        height: 1px;
        background:
            linear-gradient(
                90deg,
                transparent,
                rgba(255,255,255,.065),
                transparent
            );
    }

    .login-card {
        padding: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        border: 1px solid rgba(255,255,255,.055);
        border-radius: 13px;
        background: rgba(255,255,255,.01);
    }

    .login-card-copy strong,
    .login-card-copy span {
        display: block;
    }

    .login-card-copy strong {
        color: #cbd0d5;
        font-size: 8px;
    }

    .login-card-copy span {
        margin-top: 3px;
        color: #5b636d;
        font-size: 6px;
        line-height: 1.5;
    }

    .login-card-link {
        min-height: 35px;
        padding: 0 11px;
        display: inline-flex;
        align-items: center;
        flex: 0 0 auto;
        border: 1px solid rgba(227,179,107,.11);
        border-radius: 9px;
        color: #c29455;
        background: rgba(227,179,107,.025);
        text-decoration: none;
        font-size: 6px;
        font-weight: 950;
    }

    .register-security-note {
        margin-top: 13px;
        display: flex;
        align-items: flex-start;
        gap: 8px;
        color: #505862;
        font-size: 6px;
        line-height: 1.55;
    }

    .security-mark {
        width: 19px;
        height: 19px;
        flex: 0 0 19px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(103,217,144,.09);
        border-radius: 50%;
        color: #7cc393;
        background: rgba(103,217,144,.02);
        font-size: 6px;
        font-weight: 950;
    }

    @media (max-width: 1100px) {
        .register-layout {
            grid-template-columns: 1fr;
            max-width: 900px;
        }

        .register-showcase {
            min-height: auto;
        }

        .register-shell {
            max-width: 620px;
        }
    }

    @media (max-width: 720px) {
        .register-layout {
            width: min(calc(100% - 22px),900px);
            padding-top: 27px;
        }

        .register-showcase {
            padding: 28px 20px;
            border-radius: 22px;
        }

        .register-headline {
            font-size: clamp(49px,15vw,68px);
        }

        .showcase-stats {
            grid-template-columns: 1fr;
        }

        .register-panel {
            padding: 24px 17px;
            border-radius: 22px;
        }
    }

    @media (max-width: 560px) {
        .register-preview {
            display: none;
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
<section class="register-page">
    <div class="register-layout">

        <aside class="register-showcase">
            <div>
                <span class="register-kicker">
                    Mashal Image Studio
                </span>

                <h2 class="register-headline">
                    Maak je workspace.
                    <span>Bewaar elk beeld.</span>
                </h2>

                <p class="register-intro">
                    Maak een persoonlijk Mashal Studio-account aan en beheer
                    daarna je originele afbeeldingen, editorbewerkingen en
                    opgeslagen versies vanuit één private workspace.
                </p>

                <div class="register-trust">
                    <span class="register-trust-pill">
                        Private workspace
                    </span>

                    <span class="register-trust-pill">
                        Secure registration
                    </span>

                    <span class="register-trust-pill">
                        Image ownership
                    </span>
                </div>
            </div>

            <div
                class="register-preview"
                aria-hidden="true"
            >
                <div class="register-preview-top">
                    <div class="register-preview-dots">
                        <span class="register-preview-dot"></span>
                        <span class="register-preview-dot"></span>
                        <span class="register-preview-dot"></span>
                    </div>

                    <span class="register-preview-title">
                        Personal image library
                    </span>

                    <span class="register-preview-secure">
                        Private
                    </span>
                </div>

                <div class="register-preview-body">
                    <div class="register-preview-card">
                        <span class="register-preview-label">
                            Original
                        </span>
                    </div>

                    <div class="register-preview-card">
                        <span class="register-preview-label">
                            Resize
                        </span>
                    </div>

                    <div class="register-preview-card">
                        <span class="register-preview-label">
                            WEBP
                        </span>
                    </div>
                </div>

                <div class="register-preview-footer">
                    <span>Original preserved</span>
                    <span>Resize · Crop · Convert · Versions</span>
                </div>
            </div>

            <div class="showcase-stats">
                <div class="showcase-stat">
                    <small>Uploads</small>
                    <strong>JPG · PNG · WEBP</strong>
                </div>

                <div class="showcase-stat">
                    <small>Workspace</small>
                    <strong>Persoonlijk account</strong>
                </div>

                <div class="showcase-stat">
                    <small>Versions</small>
                    <strong>Origineel behouden</strong>
                </div>
            </div>
        </aside>

        <main class="register-panel">
            <div class="register-shell">

                <div class="register-brand">
                    <div class="register-brand-mark">
                        M
                    </div>

                    <div class="register-brand-copy">
                        <strong>
                            Mashal Studio
                        </strong>

                        <span>
                            Personal image workspace
                        </span>
                    </div>
                </div>

                <span class="register-section-kicker">
                    New account
                </span>

                <h1 class="register-title">
                    Account aanmaken
                </h1>

                <p class="register-subtitle">
                    Maak je account aan en start daarna direct met uploaden,
                    bewerken en bewaren van je afbeeldingen.
                </p>

                @if (session('success'))
                    <div
                        class="register-message success"
                        role="status"
                    >
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div
                        class="register-message error"
                        role="alert"
                    >
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div
                        class="register-message error"
                        role="alert"
                    >
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

                <form
                    id="registerForm"
                    class="register-form-card"
                    method="POST"
                    action="{{ route('register.submit') }}"
                    data-login-security-form
                >
                    @csrf

                    <div class="register-field">
                        <div class="register-label-row">
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
                                maxlength="255"
                                required
                                autofocus
                            >
                        </div>
                    </div>

                    <div class="register-field">
                        <div class="register-label-row">
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
                                inputmode="email"
                                autocapitalize="none"
                                spellcheck="false"
                                maxlength="255"
                                required
                            >
                        </div>
                    </div>

                    <div class="register-field">
                        <div class="register-label-row">
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
                                class="password-toggle"
                                type="button"
                                data-password-toggle="password"
                                aria-label="Wachtwoord tonen of verbergen"
                            >
                                Tonen
                            </button>
                        </div>

                        <div class="password-strength">
                            <div class="password-strength-track">
                                <div
                                    class="password-strength-bar"
                                    id="passwordStrengthBar"
                                ></div>
                            </div>

                            <div class="password-strength-copy">
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
                                class="password-toggle"
                                type="button"
                                data-password-toggle="password_confirmation"
                                aria-label="Wachtwoordbevestiging tonen of verbergen"
                            >
                                Tonen
                            </button>
                        </div>
                    </div>

                    <div class="register-options">
                        <label class="terms-label">
                            <input
                                type="checkbox"
                                name="terms"
                                value="1"
                                @checked(old('terms'))
                                required
                            >

                            <span>
                                Ik ga akkoord met de voorwaarden en begrijp
                                dat technische beveiligingsgegevens zoals
                                IP-adres, apparaat, browser en timezone voor
                                accountbeveiliging kunnen worden verwerkt.
                            </span>
                        </label>
                    </div>

                    <button
                        id="registerSubmitButton"
                        class="register-submit"
                        type="submit"
                    >
                        Account aanmaken
                    </button>

                    <div
                        id="registerSecurityStatus"
                        class="register-security-status"
                        aria-live="polite"
                    ></div>
                </form>

                <div class="register-divider">
                    Heb je al een account?
                </div>

                <div class="login-card">
                    <div class="login-card-copy">
                        <strong>
                            Welkom terug
                        </strong>

                        <span>
                            Log in met wachtwoord, e-mailcode,
                            magic link of social login.
                        </span>
                    </div>

                    <a
                        class="login-card-link"
                        href="{{ route('login') }}"
                    >
                        Inloggen
                    </a>
                </div>

                <div class="register-security-note">
                    <span class="security-mark">
                        ✓
                    </span>

                    <span>
                        Browser-timezone wordt vóór registratie als beveiligingscontext opgeslagen.
                        Precieze browserlocatie wordt alleen gevraagd wanneer die functie is ingeschakeld
                        en je browser toestemming geeft. Een weigering blokkeert registratie niet.
                    </span>
                </div>
            </div>
        </main>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const form =
        document.getElementById(
            'registerForm'
        );

    const submitButton =
        document.getElementById(
            'registerSubmitButton'
        );

    const statusElement =
        document.getElementById(
            'registerSecurityStatus'
        );

    const password =
        document.getElementById(
            'password'
        );

    const strengthBar =
        document.getElementById(
            'passwordStrengthBar'
        );

    const strengthLabel =
        document.getElementById(
            'passwordStrengthLabel'
        );

    if (!form) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Password visibility
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(
            '[data-password-toggle]'
        )
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

    /*
    |--------------------------------------------------------------------------
    | Password strength
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Registration security context
    |--------------------------------------------------------------------------
    */

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

    let securityContextHandled =
        false;

    function setStatus(
        message,
        active = false
    ) {
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
                : 'Account aanmaken';
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

                        credentials:
                            'same-origin',

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
                            JSON.stringify(
                                payload
                            ),
                    }
                );

            return response.ok;
        } catch (error) {
            return false;
        }
    }

    async function getPermissionState() {
        if (
            !navigator.permissions ||
            typeof navigator.permissions.query !==
                'function'
        ) {
            return null;
        }

        try {
            const result =
                await navigator.permissions.query({
                    name: 'geolocation'
                });

            return (
                result.state ||
                null
            );
        } catch (error) {
            return null;
        }
    }

    function getCurrentLocation() {
        return new Promise(
            function (resolve) {
                if (
                    !navigator.geolocation ||
                    typeof navigator.geolocation
                        .getCurrentPosition !==
                        'function'
                ) {
                    resolve({
                        permission:
                            'unsupported',

                        latitude:
                            null,

                        longitude:
                            null,

                        accuracy:
                            null,
                    });

                    return;
                }

                navigator.geolocation
                    .getCurrentPosition(
                        function (position) {
                            resolve({
                                permission:
                                    'granted',

                                latitude:
                                    Number.isFinite(
                                        position.coords.latitude
                                    )
                                        ? position.coords.latitude
                                        : null,

                                longitude:
                                    Number.isFinite(
                                        position.coords.longitude
                                    )
                                        ? position.coords.longitude
                                        : null,

                                accuracy:
                                    Number.isFinite(
                                        position.coords.accuracy
                                    )
                                        ? position.coords.accuracy
                                        : null,
                            });
                        },

                        function (error) {
                            let permission =
                                'unavailable';

                            if (
                                error &&
                                error.code === 1
                            ) {
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

            latitude:
                null,

            longitude:
                null,

            location_accuracy:
                null,

            location_permission:
                'unknown',
        };

        if (!preciseLocationEnabled) {
            payload.location_permission =
                'unavailable';

            return await storeSecurityContext(
                payload
            );
        }

        if (
            !navigator.geolocation ||
            typeof navigator.geolocation
                .getCurrentPosition !==
                'function'
        ) {
            payload.location_permission =
                'unsupported';

            return await storeSecurityContext(
                payload
            );
        }

        const permissionState =
            await getPermissionState();

        if (
            permissionState ===
            'denied'
        ) {
            payload.location_permission =
                'denied';

            return await storeSecurityContext(
                payload
            );
        }

        if (
            permissionState ===
            'prompt'
        ) {
            payload.location_permission =
                'prompt';
        }

        const location =
            await getCurrentLocation();

        payload.location_permission =
            location.permission ||
            'unknown';

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

    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

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
                /*
                 * Security-context mag registratie niet blokkeren.
                 */
            }

            securityContextHandled =
                true;

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
                document
                    .visibilityState ===
                'hidden'
            ) {
                return;
            }

            securityContextHandled =
                false;

            setSubmitting(false);

            setStatus('');
        }
    );
});
</script>
@endpush
