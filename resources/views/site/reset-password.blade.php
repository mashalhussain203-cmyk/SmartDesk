@extends('layouts.site-layout')

@section('title', 'Nieuw wachtwoord | Mashal Studio')

@section(
    'meta_description',
    'Stel veilig een nieuw wachtwoord in voor je Mashal Studio-account.'
)

@push('styles')
<style>
    :root {
        --reset-bg: #07080b;
        --reset-panel: #0d1015;
        --reset-panel-2: #12161d;
        --reset-text: #f7f7f4;
        --reset-muted: #808792;
        --reset-muted-2: #5d6570;
        --reset-line: rgba(255,255,255,.072);
        --reset-gold: #e3b36b;
        --reset-gold-light: #f3d69a;
        --reset-success: #67d990;
        --reset-warning: #f0c46d;
        --reset-danger: #f47d7d;
    }

    .reset-page {
        position: relative;
        min-height: calc(100vh - 76px);
        overflow: hidden;
        color: var(--reset-text);
        background:
            radial-gradient(circle at 12% 9%, rgba(227,179,107,.08), transparent 30rem),
            radial-gradient(circle at 87% 8%, rgba(112,93,255,.055), transparent 31rem),
            linear-gradient(180deg,#07080b,#090b0f);
    }

    .reset-page::before {
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

    .reset-layout {
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
    | Left experience panel
    |--------------------------------------------------------------------------
    */

    .reset-showcase {
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

    .reset-showcase::before {
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

    .reset-showcase::after {
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

    .reset-kicker {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: #d9aa65;
        font-size: 9px;
        font-weight: 950;
        letter-spacing: .19em;
        text-transform: uppercase;
    }

    .reset-kicker::before {
        content: "";
        width: 31px;
        height: 1px;
        background: linear-gradient(90deg,#e2b36c,transparent);
    }

    .reset-headline {
        max-width: 760px;
        margin: 18px 0 0;
        color: #f9f9f6;
        font-size: clamp(56px,6.2vw,92px);
        line-height: .91;
        letter-spacing: -.072em;
        font-weight: 950;
        text-wrap: balance;
    }

    .reset-headline span {
        display: block;
        color: #f1cf91;
    }

    .reset-intro {
        max-width: 625px;
        margin: 24px 0 0;
        color: #9198a2;
        font-size: 13px;
        line-height: 1.86;
    }

    .reset-badges {
        margin-top: 28px;
        display: grid;
        grid-template-columns: repeat(3,minmax(0,1fr));
        gap: 8px;
        max-width: 700px;
    }

    .reset-badge {
        padding: 13px;
        border: 1px solid rgba(255,255,255,.06);
        border-radius: 13px;
        background: rgba(255,255,255,.012);
    }

    .reset-badge small {
        display: block;
        color: #705a3e;
        font-size: 6px;
        font-weight: 950;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .reset-badge strong {
        display: block;
        margin-top: 5px;
        color: #b7bdc5;
        font-size: 8px;
        line-height: 1.5;
    }

    /*
    |--------------------------------------------------------------------------
    | Visual flow
    |--------------------------------------------------------------------------
    */

    .reset-flow {
        margin-top: 42px;
        padding: 14px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 21px;
        background: rgba(7,9,12,.63);
        box-shadow: 0 30px 85px rgba(0,0,0,.35);
        backdrop-filter: blur(15px);
    }

    .reset-flow-top {
        min-height: 44px;
        padding: 0 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border-bottom: 1px solid rgba(255,255,255,.05);
    }

    .reset-flow-dots {
        display: flex;
        gap: 5px;
    }

    .reset-flow-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: rgba(255,255,255,.13);
    }

    .reset-flow-dot:first-child {
        background: rgba(227,179,107,.63);
    }

    .reset-flow-title,
    .reset-flow-secure {
        color: #606873;
        font-size: 6px;
        font-weight: 950;
        letter-spacing: .10em;
        text-transform: uppercase;
    }

    .reset-flow-secure {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .reset-flow-secure::before {
        content: "";
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: var(--reset-success);
    }

    .reset-flow-body {
        padding: 24px;
        display: grid;
        gap: 10px;
        background:
            linear-gradient(45deg, rgba(255,255,255,.01) 25%, transparent 25%),
            linear-gradient(-45deg, rgba(255,255,255,.01) 25%, transparent 25%),
            linear-gradient(45deg, transparent 75%, rgba(255,255,255,.01) 75%),
            linear-gradient(-45deg, transparent 75%, rgba(255,255,255,.01) 75%),
            #0a0c10;
        background-size: 20px 20px;
        background-position: 0 0, 0 10px, 10px -10px, -10px 0;
    }

    .reset-flow-step {
        padding: 14px;
        display: grid;
        grid-template-columns: 38px minmax(0,1fr) auto;
        gap: 11px;
        align-items: center;
        border: 1px solid rgba(255,255,255,.055);
        border-radius: 13px;
        background: rgba(255,255,255,.012);
    }

    .reset-flow-step.active {
        border-color: rgba(227,179,107,.13);
        background: rgba(227,179,107,.035);
    }

    .reset-flow-number {
        width: 38px;
        height: 38px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(227,179,107,.10);
        border-radius: 11px;
        color: #c69a5b;
        background: rgba(227,179,107,.025);
        font-size: 7px;
        font-weight: 950;
    }

    .reset-flow-copy strong,
    .reset-flow-copy span {
        display: block;
    }

    .reset-flow-copy strong {
        color: #cfd3d8;
        font-size: 8px;
    }

    .reset-flow-copy span {
        margin-top: 3px;
        color: #5c646e;
        font-size: 6px;
    }

    .reset-flow-status {
        min-height: 23px;
        padding: 0 7px;
        display: inline-flex;
        align-items: center;
        border: 1px solid rgba(103,217,144,.09);
        border-radius: 999px;
        color: #82ca99;
        background: rgba(103,217,144,.025);
        font-size: 6px;
        font-weight: 950;
        white-space: nowrap;
    }

    .reset-flow-status.current {
        border-color: rgba(227,179,107,.11);
        color: #d2a45f;
        background: rgba(227,179,107,.03);
    }

    .reset-flow-footer {
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

    .reset-flow-footer span:first-child {
        color: #86c998;
    }

    /*
    |--------------------------------------------------------------------------
    | Right panel
    |--------------------------------------------------------------------------
    */

    .reset-panel {
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

    .reset-shell {
        width: 100%;
        max-width: 550px;
        margin: auto;
    }

    .reset-brand {
        margin-bottom: 26px;
        display: flex;
        align-items: center;
        gap: 11px;
    }

    .reset-brand-mark {
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

    .reset-brand-copy strong,
    .reset-brand-copy span {
        display: block;
    }

    .reset-brand-copy strong {
        color: #f1f2f3;
        font-size: 13px;
    }

    .reset-brand-copy span {
        margin-top: 4px;
        color: #5d6570;
        font-size: 6px;
        font-weight: 900;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .reset-section-kicker {
        display: block;
        margin-bottom: 8px;
        color: #a37c48;
        font-size: 7px;
        font-weight: 950;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .reset-title {
        margin: 0;
        color: #f5f6f7;
        font-size: clamp(38px,4vw,52px);
        line-height: .98;
        letter-spacing: -.057em;
        font-weight: 950;
    }

    .reset-subtitle {
        max-width: 520px;
        margin: 12px 0 22px;
        color: #727984;
        font-size: 10px;
        line-height: 1.75;
    }

    .reset-message {
        margin-bottom: 13px;
        padding: 12px 13px;
        border-radius: 11px;
        font-size: 8px;
        line-height: 1.65;
    }

    .reset-message.success {
        border: 1px solid rgba(103,217,144,.12);
        color: #a2d9b3;
        background: rgba(103,217,144,.035);
    }

    .reset-message.error {
        border: 1px solid rgba(240,131,131,.12);
        color: #dda2a2;
        background: rgba(240,131,131,.035);
    }

    .reset-message ul {
        margin: 6px 0 0 15px;
        padding: 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Form
    |--------------------------------------------------------------------------
    */

    .reset-form-card {
        padding: 17px;
        border: 1px solid rgba(255,255,255,.055);
        border-radius: 16px;
        background: rgba(255,255,255,.01);
    }

    .reset-field {
        margin-bottom: 13px;
    }

    .reset-label-row {
        margin-bottom: 7px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .reset-label-row label {
        color: #959ca5;
        font-size: 7px;
        font-weight: 900;
        letter-spacing: .06em;
    }

    .reset-field-error {
        color: #dc9292;
        font-size: 7px;
        font-weight: 850;
    }

    .reset-input-wrap {
        position: relative;
    }

    .reset-input {
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

    .reset-input.with-toggle {
        padding-right: 72px;
    }

    .reset-input:focus {
        border-color: rgba(227,179,107,.27);
        background: rgba(227,179,107,.019);
        box-shadow: 0 0 0 4px rgba(227,179,107,.035);
    }

    .reset-input::placeholder {
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

    /*
    |--------------------------------------------------------------------------
    | Password strength
    |--------------------------------------------------------------------------
    */

    .password-strength {
        margin-top: 8px;
    }

    .strength-track {
        height: 5px;
        overflow: hidden;
        border-radius: 999px;
        background: rgba(255,255,255,.055);
    }

    .strength-bar {
        width: 0;
        height: 100%;
        border-radius: inherit;
        background: #707780;
        transition:
            width .2s ease,
            background .2s ease;
    }

    .strength-copy {
        margin-top: 5px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        color: #515964;
        font-size: 6px;
    }

    .password-match {
        min-height: 16px;
        margin-top: 6px;
        color: #555d67;
        font-size: 6px;
    }

    .password-match.good {
        color: #7fca97;
    }

    .password-match.bad {
        color: #d68f8f;
    }

    /*
    |--------------------------------------------------------------------------
    | Security info
    |--------------------------------------------------------------------------
    */

    .reset-info-grid {
        margin: 3px 0 14px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .reset-info {
        padding: 12px;
        border: 1px solid rgba(255,255,255,.05);
        border-radius: 12px;
        background: rgba(255,255,255,.01);
    }

    .reset-info.warning {
        border-color: rgba(240,196,109,.09);
        background: rgba(240,196,109,.02);
    }

    .reset-info small {
        display: block;
        color: #705a3e;
        font-size: 6px;
        font-weight: 950;
        letter-spacing: .09em;
        text-transform: uppercase;
    }

    .reset-info strong {
        display: block;
        margin-top: 5px;
        color: #bfc4ca;
        font-size: 7px;
        line-height: 1.5;
    }

    .reset-info p {
        margin: 4px 0 0;
        color: #59616b;
        font-size: 6px;
        line-height: 1.55;
    }

    .reset-submit {
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

    .reset-submit:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 21px 46px rgba(227,179,107,.20);
    }

    .reset-submit:disabled {
        opacity: .60;
        cursor: wait;
        transform: none;
    }

    /*
    |--------------------------------------------------------------------------
    | Return to login
    |--------------------------------------------------------------------------
    */

    .reset-divider {
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

    .reset-divider::before,
    .reset-divider::after {
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

    .login-return-card {
        padding: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        border: 1px solid rgba(255,255,255,.055);
        border-radius: 13px;
        background: rgba(255,255,255,.01);
    }

    .login-return-copy strong,
    .login-return-copy span {
        display: block;
    }

    .login-return-copy strong {
        color: #cbd0d5;
        font-size: 8px;
    }

    .login-return-copy span {
        margin-top: 3px;
        color: #5b636d;
        font-size: 6px;
        line-height: 1.5;
    }

    .login-return-link {
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

    .reset-security-note {
        margin-top: 13px;
        display: flex;
        align-items: flex-start;
        gap: 8px;
        color: #505862;
        font-size: 6px;
        line-height: 1.55;
    }

    .reset-security-mark {
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
        .reset-layout {
            grid-template-columns: 1fr;
            max-width: 900px;
        }

        .reset-showcase {
            min-height: auto;
        }

        .reset-shell {
            max-width: 620px;
        }
    }

    @media (max-width: 720px) {
        .reset-layout {
            width: min(calc(100% - 22px),900px);
            padding-top: 27px;
        }

        .reset-showcase {
            padding: 28px 20px;
            border-radius: 22px;
        }

        .reset-headline {
            font-size: clamp(49px,15vw,68px);
        }

        .reset-badges,
        .reset-info-grid {
            grid-template-columns: 1fr;
        }

        .reset-panel {
            padding: 24px 17px;
            border-radius: 22px;
        }
    }

    @media (max-width: 560px) {
        .reset-flow {
            display: none;
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
<section class="reset-page">
    <div class="reset-layout">

        <aside class="reset-showcase">
            <div>
                <span class="reset-kicker">
                    Mashal Studio security
                </span>

                <h2 class="reset-headline">
                    Nieuwe sleutel.
                    <span>Zelfde workspace.</span>
                </h2>

                <p class="reset-intro">
                    Stel veilig een nieuw wachtwoord in voor je Mashal Studio-account.
                    Je afbeeldingsprojecten, originelen en opgeslagen versies blijven
                    gewoon aan hetzelfde account gekoppeld.
                </p>

                <div class="reset-badges">
                    <div class="reset-badge">
                        <small>
                            Protected
                        </small>

                        <strong>
                            Veilige resetflow voor je account
                        </strong>
                    </div>

                    <div class="reset-badge">
                        <small>
                            Temporary
                        </small>

                        <strong>
                            Resetlink is tijdelijk geldig
                        </strong>
                    </div>

                    <div class="reset-badge">
                        <small>
                            Private
                        </small>

                        <strong>
                            Je image workspace blijft behouden
                        </strong>
                    </div>
                </div>
            </div>

            <div
                class="reset-flow"
                aria-hidden="true"
            >
                <div class="reset-flow-top">
                    <div class="reset-flow-dots">
                        <span class="reset-flow-dot"></span>
                        <span class="reset-flow-dot"></span>
                        <span class="reset-flow-dot"></span>
                    </div>

                    <span class="reset-flow-title">
                        Password recovery
                    </span>

                    <span class="reset-flow-secure">
                        Secure
                    </span>
                </div>

                <div class="reset-flow-body">
                    <div class="reset-flow-step">
                        <span class="reset-flow-number">
                            01
                        </span>

                        <span class="reset-flow-copy">
                            <strong>
                                Resetlink aangevraagd
                            </strong>

                            <span>
                                Recovery gestart via e-mail
                            </span>
                        </span>

                        <span class="reset-flow-status">
                            Done
                        </span>
                    </div>

                    <div class="reset-flow-step active">
                        <span class="reset-flow-number">
                            02
                        </span>

                        <span class="reset-flow-copy">
                            <strong>
                                Nieuw wachtwoord kiezen
                            </strong>

                            <span>
                                Sterk en uniek wachtwoord instellen
                            </span>
                        </span>

                        <span class="reset-flow-status current">
                            Current
                        </span>
                    </div>

                    <div class="reset-flow-step">
                        <span class="reset-flow-number">
                            03
                        </span>

                        <span class="reset-flow-copy">
                            <strong>
                                Opnieuw inloggen
                            </strong>

                            <span>
                                Toegang tot je workspace herstellen
                            </span>
                        </span>

                        <span class="reset-flow-status">
                            Next
                        </span>
                    </div>
                </div>

                <div class="reset-flow-footer">
                    <span>
                        Account recovery protected
                    </span>

                    <span>
                        Mashal Studio
                    </span>
                </div>
            </div>
        </aside>

        <main class="reset-panel">
            <div class="reset-shell">

                <div class="reset-brand">
                    <div class="reset-brand-mark">
                        M
                    </div>

                    <div class="reset-brand-copy">
                        <strong>
                            Mashal Studio
                        </strong>

                        <span>
                            Secure account recovery
                        </span>
                    </div>
                </div>

                <span class="reset-section-kicker">
                    Password reset
                </span>

                <h1 class="reset-title">
                    Nieuw wachtwoord
                </h1>

                <p class="reset-subtitle">
                    Bevestig het e-mailadres van je account en kies een nieuw,
                    sterk wachtwoord. Daarna kun je weer normaal inloggen.
                </p>

                @if (session('success'))
                    <div
                        class="reset-message success"
                        role="status"
                    >
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div
                        class="reset-message error"
                        role="alert"
                    >
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div
                        class="reset-message error"
                        role="alert"
                    >
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

                <form
                    id="resetPasswordForm"
                    class="reset-form-card"
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

                        <div class="reset-input-wrap">
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
                                class="password-toggle"
                                type="button"
                                data-toggle-password="password"
                                aria-label="Nieuw wachtwoord tonen of verbergen"
                            >
                                Tonen
                            </button>
                        </div>

                        <div class="password-strength">
                            <div class="strength-track">
                                <div
                                    class="strength-bar"
                                    id="passwordStrengthBar"
                                ></div>
                            </div>

                            <div class="strength-copy">
                                <span>
                                    Wachtwoordsterkte
                                </span>

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
                                class="password-toggle"
                                type="button"
                                data-toggle-password="password_confirmation"
                                aria-label="Wachtwoordbevestiging tonen of verbergen"
                            >
                                Tonen
                            </button>
                        </div>

                        <div
                            class="password-match"
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
                                Gebruik bij voorkeur een lang wachtwoord dat je
                                nergens anders gebruikt.
                            </p>
                        </div>

                        <div class="reset-info warning">
                            <small>
                                Recovery link
                            </small>

                            <strong>
                                Tijdelijke resetlink
                            </strong>

                            <p>
                                Is je link verlopen? Vraag dan vanaf de
                                wachtwoord-vergetenpagina een nieuwe aan.
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

                <div class="login-return-card">
                    <div class="login-return-copy">
                        <strong>
                            Terug naar inloggen
                        </strong>

                        <span>
                            Na een succesvolle reset log je in met je nieuwe wachtwoord.
                        </span>
                    </div>

                    <a
                        class="login-return-link"
                        href="{{ route('login') }}"
                    >
                        Inloggen
                    </a>
                </div>

                <div class="reset-security-note">
                    <span class="reset-security-mark">
                        ✓
                    </span>

                    <span>
                        Deel je resetlink of nieuwe wachtwoord nooit met anderen.
                        Mashal Studio zal nooit via chat of telefoon om je wachtwoord vragen.
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
    | Strength meter
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

        strengthBar.style.width =
            result.percent + '%';

        strengthBar.style.background =
            result.tone;

        strengthLabel.textContent =
            result.label;
    }

    /*
    |--------------------------------------------------------------------------
    | Match status
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
            matchStatus.textContent =
                '';

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

            submit.disabled =
                true;

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

            submit.disabled =
                false;

            submit.textContent =
                'Nieuw wachtwoord opslaan';
        }
    );

    updateStrength();
    updateMatchStatus();
});
</script>
@endpush
