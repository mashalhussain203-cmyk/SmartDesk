@extends('layouts.site-layout')

@section('title', 'Inloggen | Mashal Studio')

@section(
    'meta_description',
    'Log veilig in bij Mashal Studio met wachtwoord, e-mailcode, magic link of je favoriete social login.'
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


    .login-glass-card {
        min-height: 482px;
    }

    .login-tabs {
        margin-bottom: 13px;
        padding: 3px;
        display: grid;
        grid-template-columns: repeat(3,1fr);
        gap: 3px;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 10px;
        background: rgba(0,0,0,.18);
    }

    .login-tab {
        min-height: 34px;
        padding: 0 5px;
        border: 1px solid transparent;
        border-radius: 7px;
        color: #77767c;
        background: transparent;
        font: inherit;
        font-size: 7px;
        font-weight: 900;
        cursor: pointer;
    }

    .login-tab.active {
        border-color: rgba(244,238,31,.20);
        color: var(--glass-yellow);
        background: rgba(244,238,31,.055);
    }

    .login-panel-section {
        display: none;
    }

    .login-panel-section.active {
        display: block;
    }

    .login-options {
        margin: 3px 0 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .login-remember {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #88878d;
        font-size: 8px;
        cursor: pointer;
    }

    .login-remember input {
        width: 13px;
        height: 13px;
        accent-color: #e5ca18;
    }

    .login-passwordless-card {
        padding: 11px;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 11px;
        background: rgba(0,0,0,.16);
    }

    .login-passwordless-card + .login-passwordless-card {
        margin-top: 8px;
    }

    .login-passwordless-head {
        margin-bottom: 9px;
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }

    .login-passwordless-icon {
        width: 31px;
        height: 31px;
        flex: 0 0 31px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(244,238,31,.17);
        border-radius: 8px;
        color: var(--glass-yellow);
        background: rgba(244,238,31,.04);
        font-size: 9px;
        font-weight: 900;
    }

    .login-passwordless-copy strong {
        display: block;
        color: #dedee1;
        font-size: 8px;
    }

    .login-passwordless-copy span {
        display: block;
        margin-top: 3px;
        color: #77767c;
        font-size: 7px;
        line-height: 1.45;
    }

    .login-passwordless-form {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 6px;
    }

    .login-passwordless-form .glass-input {
        height: 38px;
    }

    .login-oauth-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 7px;
    }

    .login-oauth {
        min-height: 54px;
        padding: 0 9px;
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 10px;
        color: #e1e1e3;
        background: rgba(0,0,0,.19);
        text-decoration: none;
        transition:
            transform .18s ease,
            border-color .18s ease,
            background .18s ease;
    }

    .login-oauth:hover {
        transform: translateY(-1px);
        border-color: rgba(244,238,31,.20);
        background: rgba(244,238,31,.025);
    }

    .login-oauth-icon {
        width: 27px;
        height: 27px;
        display: grid;
        place-items: center;
        flex: 0 0 27px;
    }

    .login-oauth-icon svg {
        width: 23px;
        height: 23px;
    }

    .login-oauth-copy strong,
    .login-oauth-copy span {
        display: block;
    }

    .login-oauth-copy strong {
        font-size: 8px;
    }

    .login-oauth-copy span {
        margin-top: 2px;
        color: #717177;
        font-size: 6px;
    }

    .login-pending {
        margin-bottom: 10px;
        padding: 8px 10px;
        border: 1px solid rgba(24,237,126,.16);
        border-radius: 9px;
        color: #9bf5c7;
        background: rgba(24,237,126,.045);
        font-size: 8px;
        line-height: 1.5;
    }

    .login-register-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 9px 10px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 10px;
        background: rgba(0,0,0,.14);
    }

    .login-register-row span {
        color: #85848a;
        font-size: 7px;
        line-height: 1.45;
    }

    .login-register-row a {
        flex: 0 0 auto;
    }

    .login-security-note {
        margin-top: 10px;
        color: #69686e;
        font-size: 7px;
        line-height: 1.5;
        text-align: center;
    }

    @media (max-width: 380px) {
        .login-oauth-grid {
            grid-template-columns: 1fr;
        }
    }


    .glass-auth-switch {
        width: min(100%, 270px);
        margin: 0 auto 14px;
        padding: 3px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 11px;
        background: rgba(0,0,0,.19);
    }

    .glass-auth-switch a {
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid transparent;
        border-radius: 8px;
        color: #77767c;
        text-decoration: none;
        font-size: 8px;
        font-weight: 900;
        transition:
            color .18s ease,
            border-color .18s ease,
            background .18s ease;
    }

    .glass-auth-switch a.active {
        border-color: rgba(244,238,31,.20);
        color: var(--glass-yellow);
        background: rgba(244,238,31,.055);
    }

    .glass-auth-switch a:hover {
        color: var(--glass-yellow);
    }


    /*
    |--------------------------------------------------------------------------
    | V3 layout fix
    |--------------------------------------------------------------------------
    | De kaart heeft GEEN vaste hoogte meer. Hierdoor groeien login/register
    | automatisch mee met Wachtwoord, E-mail en Social zonder afsnijden.
    */
    .glass-card-shell {
        height: auto !important;
        min-height: 0 !important;
        overflow: visible;
    }

    .glass-card-border {
        position: relative !important;
        inset: auto !important;
        width: 100%;
        height: auto !important;
        min-height: 0 !important;
    }

    .glass-card {
        height: auto !important;
        min-height: 0 !important;
    }

    .login-glass-card,
    .register-glass-card {
        height: auto !important;
        min-height: 0 !important;
    }

    .glass-inner {
        min-height: 0;
        padding-bottom: 40px;
    }

    .glass-stage {
        min-height: 100dvh;
        padding-top: clamp(44px, 6vh, 70px);
        padding-bottom: 72px;
    }

    .glass-poster-title {
        flex: 0 0 auto;
    }

    .glass-code-panel {
        flex: 0 0 auto;
        margin-top: 46px;
    }

    @media (max-width: 540px) {
        .glass-stage {
            padding-top: 38px;
            padding-bottom: 54px;
        }

        .glass-inner {
            padding-bottom: 34px;
        }

        .glass-code-panel {
            margin-top: 38px;
        }
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
            Glassy Login
            <span>Mashal Studio</span>
        </h1>

        <div class="glass-card-shell login-glass-card">
            <div class="glass-card-border">
                <div class="glass-card">
                    <div class="glass-inner">
                        <div class="glass-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="5" y="10" width="14" height="10" rx="2"></rect>
                                <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                                <path d="M12 14v2"></path>
                            </svg>
                        </div>

                        <h2 class="glass-heading">
                            Secure <strong>Access</strong>
                        </h2>

                        <p class="glass-description">
                            Kies je inlogmethode en open veilig je persoonlijke Mashal Studio-workspace.
                        </p>

                        <nav class="glass-auth-switch" aria-label="Inloggen of registreren">
                            <a class="active" href="{{ route('login') }}" aria-current="page">
                                Inloggen
                            </a>
                            <a href="{{ route('register') }}">
                                Registreren
                            </a>
                        </nav>

                        @if (session()->has('pending_image'))
                            <div class="login-pending">
                                ✓ Je afbeelding staat klaar. Rond je login af om verder te gaan naar je editor.
                            </div>
                        @endif

                        @if (session('status'))
                            <div class="glass-message info" role="status">
                                {{ session('status') }}
                            </div>
                        @endif

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
                                <strong>Inloggen is niet gelukt.</strong>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="login-tabs" role="tablist" aria-label="Inlogmethode kiezen">
                            <button
                                class="login-tab active"
                                type="button"
                                role="tab"
                                aria-selected="true"
                                data-auth-tab="password"
                            >
                                Wachtwoord
                            </button>

                            <button
                                class="login-tab"
                                type="button"
                                role="tab"
                                aria-selected="false"
                                data-auth-tab="passwordless"
                            >
                                E-mail
                            </button>

                            <button
                                class="login-tab"
                                type="button"
                                role="tab"
                                aria-selected="false"
                                data-auth-tab="oauth"
                            >
                                Social
                            </button>
                        </div>

                        <section
                            class="login-panel-section active"
                            data-auth-panel="password"
                        >
                            <form
                                method="POST"
                                action="{{ route('login.submit') }}"
                                data-login-security-form data-auth-transition-form
                            >
                                @csrf

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
                                            placeholder="Vul je wachtwoord in"
                                            autocomplete="current-password"
                                            required
                                        >

                                        <button
                                            class="glass-password-toggle"
                                            type="button"
                                            data-toggle-password="password"
                                            aria-label="Wachtwoord tonen of verbergen"
                                        >
                                            Tonen
                                        </button>
                                    </div>
                                </div>

                                <div class="login-options">
                                    <label class="login-remember" for="remember">
                                        <input
                                            id="remember"
                                            type="checkbox"
                                            name="remember"
                                            value="1"
                                            @checked(old('remember'))
                                        >
                                        <span>Onthoud mij</span>
                                    </label>

                                    <a class="glass-small-link" href="{{ route('password.request') }}">
                                        Wachtwoord vergeten?
                                    </a>
                                </div>

                                <button
                                    class="glass-primary"
                                    type="submit"
                                    data-submit-label="Inloggen bij Mashal Studio"
                                >
                                    Inloggen &amp; doorgaan →
                                </button>
                            </form>
                        </section>

                        <section
                            class="login-panel-section"
                            data-auth-panel="passwordless"
                            hidden
                        >
                            <div class="login-passwordless-card">
                                <div class="login-passwordless-head">
                                    <span class="login-passwordless-icon">6</span>

                                    <div class="login-passwordless-copy">
                                        <strong>Eenmalige e-mailcode</strong>
                                        <span>Ontvang een tijdelijke 6-cijferige code. Geen wachtwoord nodig.</span>
                                    </div>
                                </div>

                                <form
                                    class="login-passwordless-form"
                                    method="POST"
                                    action="{{ route('email-login.send') }}"
                                    data-login-security-form data-auth-transition-form
                                >
                                    @csrf

                                    <input
                                        class="glass-input"
                                        type="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        placeholder="naam@example.com"
                                        autocomplete="email"
                                        inputmode="email"
                                        required
                                    >

                                    <button class="glass-secondary" type="submit">
                                        Stuur code
                                    </button>
                                </form>
                            </div>

                            <div class="login-passwordless-card">
                                <div class="login-passwordless-head">
                                    <span class="login-passwordless-icon">↗</span>

                                    <div class="login-passwordless-copy">
                                        <strong>Veilige loginlink</strong>
                                        <span>Ontvang een persoonlijke magic link die één keer gebruikt kan worden.</span>
                                    </div>
                                </div>

                                <form
                                    class="login-passwordless-form"
                                    method="POST"
                                    action="{{ route('email-login.link.send') }}"
                                    data-login-security-form data-auth-transition-form
                                >
                                    @csrf

                                    <input
                                        class="glass-input"
                                        type="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        placeholder="naam@example.com"
                                        autocomplete="email"
                                        inputmode="email"
                                        required
                                    >

                                    <button class="glass-secondary" type="submit">
                                        Stuur link
                                    </button>
                                </form>
                            </div>
                        </section>

                        <section
                            class="login-panel-section"
                            data-auth-panel="oauth"
                            hidden
                        >
                            <div class="login-oauth-grid">
                                <a
                                    class="login-oauth"
                                    data-login-security-oauth data-auth-transition-link
                                    href="{{ route('google.redirect') }}"
                                    aria-label="Doorgaan met Google"
                                >
                                    <span class="login-oauth-icon">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path fill="#4285F4" d="M21.805 10.023h-9.18v3.955h5.28c-.228 1.273-.918 2.352-1.956 3.078v2.559h3.168c1.855-1.708 2.928-4.227 2.928-7.219 0-.8-.072-1.57-.24-2.373Z"/>
                                            <path fill="#34A853" d="M12.625 22c2.65 0 4.873-.875 6.492-2.385l-3.168-2.559c-.88.59-2.003.94-3.324.94-2.55 0-4.71-1.724-5.486-4.04H3.865v2.64A9.812 9.812 0 0 0 12.625 22Z"/>
                                            <path fill="#FBBC05" d="M7.139 13.956a5.96 5.96 0 0 1 0-3.912V7.405H3.865A9.82 9.82 0 0 0 2.82 12c0 1.585.38 3.086 1.045 4.595l3.274-2.639Z"/>
                                            <path fill="#EA4335" d="M12.625 6.004c1.44 0 2.733.495 3.75 1.468l2.813-2.813C17.493 3.076 15.27 2 12.625 2a9.812 9.812 0 0 0-8.76 5.405l3.274 2.639c.776-2.316 2.936-4.04 5.486-4.04Z"/>
                                        </svg>
                                    </span>
                                    <span class="login-oauth-copy">
                                        <strong>Google / Gmail</strong>
                                        <span>Doorgaan</span>
                                    </span>
                                </a>

                                <a
                                    class="login-oauth"
                                    data-login-security-oauth data-auth-transition-link
                                    href="{{ route('github.redirect') }}"
                                    aria-label="Doorgaan met GitHub"
                                >
                                    <span class="login-oauth-icon">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path fill="currentColor" d="M12 .7a11.5 11.5 0 0 0-3.636 22.41c.575.105.786-.25.786-.555 0-.274-.01-1-.016-1.962-3.198.695-3.874-1.541-3.874-1.541-.523-1.329-1.277-1.683-1.277-1.683-1.044-.714.08-.699.08-.699 1.154.081 1.761 1.185 1.761 1.185 1.026 1.758 2.692 1.25 3.348.956.104-.743.402-1.25.73-1.537-2.553-.29-5.237-1.276-5.237-5.68 0-1.255.449-2.281 1.184-3.085-.118-.291-.513-1.462.113-3.048 0 0 .965-.309 3.162 1.179A10.98 10.98 0 0 1 12 8.253c.977.004 1.961.132 2.88.387 2.195-1.488 3.158-1.179 3.158-1.179.628 1.586.233 2.757.115 3.048.737.804 1.182 1.83 1.182 3.085 0 4.415-2.688 5.387-5.249 5.671.413.356.78 1.057.78 2.13 0 1.538-.014 2.778-.014 3.155 0 .308.207.666.792.553A11.502 11.502 0 0 0 12 .7Z"/>
                                        </svg>
                                    </span>
                                    <span class="login-oauth-copy">
                                        <strong>GitHub</strong>
                                        <span>Doorgaan</span>
                                    </span>
                                </a>

                                <a
                                    class="login-oauth"
                                    data-login-security-oauth data-auth-transition-link
                                    href="{{ route('facebook.redirect') }}"
                                    aria-label="Doorgaan met Facebook"
                                >
                                    <span class="login-oauth-icon" style="color:#1877f2">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path fill="currentColor" d="M13.6 22v-8h2.68l.4-3.12H13.6V8.89c0-.9.25-1.52 1.54-1.52h1.65V4.58a22.1 22.1 0 0 0-2.4-.12c-2.38 0-4.01 1.45-4.01 4.12v2.3H7.69V14h2.69v8h3.22Z"/>
                                        </svg>
                                    </span>
                                    <span class="login-oauth-copy">
                                        <strong>Facebook</strong>
                                        <span>Doorgaan</span>
                                    </span>
                                </a>

                                <a
                                    class="login-oauth"
                                    data-login-security-oauth data-auth-transition-link
                                    href="{{ route('tiktok.redirect') }}"
                                    aria-label="Doorgaan met TikTok"
                                >
                                    <span class="login-oauth-icon">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path fill="#25F4EE" d="M15.62 3.2c.39 2.31 1.7 3.69 3.98 3.84v2.63a7.9 7.9 0 0 1-3.94-.99v5.16c0 4.64-5.05 6.1-7.08 2.77-1.3-2.13-.5-5.87 3.67-6.03v2.77c-.38.06-.78.16-1.15.29-1.11.42-1.74 1.22-1.56 2.12.35 1.72 3.39 2.23 3.88-.26.08-.45.07-.9.07-1.36V3.2h2.13Z"/>
                                            <path fill="#FE2C55" d="M16.32 2.6c.39 2.31 1.7 3.69 3.98 3.84v2.63a7.9 7.9 0 0 1-3.94-.99v5.16c0 4.64-5.05 6.1-7.08 2.77-1.3-2.13-.5-5.87 3.67-6.03v2.77c-.38.06-.78.16-1.15.29-1.11.42-1.74 1.22-1.56 2.12.35 1.72 3.39 2.23 3.88-.26.08-.45.07-.9.07-1.36V2.6h2.13Z"/>
                                        </svg>
                                    </span>
                                    <span class="login-oauth-copy">
                                        <strong>TikTok</strong>
                                        <span>Doorgaan</span>
                                    </span>
                                </a>
                            </div>
                        </section>

                        <div class="glass-divider">
                            Nieuw bij Mashal Studio?
                        </div>

                        <div class="login-register-row">
                            <span>
                                Nog geen account? Maak gratis je eigen workspace.
                            </span>

                            <a class="glass-small-link" href="{{ route('register') }}">
                                Registreren
                            </a>
                        </div>

                        <div class="login-security-note">
                            ✓ Mashal Studio vraagt je nooit om je wachtwoord via e-mail, chat of telefoon te delen.
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
                <div class="glass-code-tab">◻&nbsp; Login.jsx</div>
                <div class="glass-code-badge">⚛&nbsp; Secure UI</div>
            </div>
            <div class="glass-code-body"><span class="pink">const</span> <span class="cyan">session</span> = {
  provider: <span class="green">'Mashal'</span>,
  protected: <span class="cyan">true</span>,
  methods: [<span class="green">'password'</span>, <span class="green">'email'</span>, <span class="green">'oauth'</span>],
  secure: <span class="yellow">1</span>
};</div>
        </div>

        <div class="glass-footer">
            Mashal Studio · Secure account access
        </div>
    </main>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    /*
     * Voorkom dat autofocus/browser scroll-restoration de bovenkant van
     * de glassy authpagina onder de vaste header schuift.
     */
    try {
        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }

        if (!window.location.hash) {
            window.scrollTo(0, 0);
        }
    } catch (error) {
        // Geen blokkade voor authenticatie.
    }

    const authTransitionKey =
        'mashal_auth_success_pending';

    function markAuthTransition(source) {
        try {
            window.localStorage.setItem(
                authTransitionKey,
                JSON.stringify({
                    source:
                        String(source || 'login'),
                    createdAt:
                        Date.now(),
                })
            );
        } catch (error) {
            // Login moet blijven werken als localStorage niet beschikbaar is.
        }
    }

    function clearAuthTransition() {
        try {
            window.localStorage.removeItem(
                authTransitionKey
            );
        } catch (error) {
            // Geen blokkade voor authenticatie.
        }
    }

    @if ($errors->any() || session('error'))
        clearAuthTransition();
    @endif

    document
        .querySelectorAll('[data-toggle-password]')
        .forEach(function (button) {
            button.addEventListener('click', function () {
                const input =
                    document.getElementById(
                        button.getAttribute('data-toggle-password')
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
            });
        });

    const authTabs =
        Array.from(
            document.querySelectorAll('[data-auth-tab]')
        );

    const authPanels =
        Array.from(
            document.querySelectorAll('[data-auth-panel]')
        );

    function selectAuthMethod(name) {
        authTabs.forEach(function (tab) {
            const active =
                tab.dataset.authTab === name;

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

        authPanels.forEach(function (panel) {
            const active =
                panel.dataset.authPanel === name;

            panel.classList.toggle(
                'active',
                active
            );

            panel.hidden =
                !active;
        });
    }

    authTabs.forEach(function (tab) {
        tab.addEventListener(
            'click',
            function () {
                selectAuthMethod(
                    tab.dataset.authTab
                );
            }
        );
    });

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

    const highAccuracy =
        @json(
            (bool) config(
                'login-security.precise_location.high_accuracy',
                true
            )
        );

    const geolocationTimeout =
        {{ max(
            1000,
            (int) config(
                'login-security.precise_location.timeout_ms',
                10000
            )
        ) }};

    const geolocationMaximumAge =
        {{ max(
            0,
            (int) config(
                'login-security.precise_location.maximum_age_ms',
                60000
            )
        ) }};

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

    async function getLocationPermissionState() {
        if (
            !navigator.permissions
            || typeof navigator.permissions.query !== 'function'
        ) {
            return 'prompt';
        }

        try {
            const status =
                await navigator.permissions.query({
                    name: 'geolocation'
                });

            if (
                status
                && ['granted','denied','prompt'].includes(status.state)
            ) {
                return status.state;
            }
        } catch (error) {
            return 'prompt';
        }

        return 'prompt';
    }

    async function getPreciseLocation() {
        const base = {
            latitude: null,
            longitude: null,
            location_accuracy: null,
            location_permission: 'unknown',
        };

        if (!preciseLocationEnabled) {
            return {
                ...base,
                location_permission: 'unavailable',
            };
        }

        if (
            !navigator.geolocation
            || typeof navigator.geolocation.getCurrentPosition !== 'function'
        ) {
            return {
                ...base,
                location_permission: 'unsupported',
            };
        }

        const initialPermission =
            await getLocationPermissionState();

        if (initialPermission === 'denied') {
            return {
                ...base,
                location_permission: 'denied',
            };
        }

        return new Promise(function (resolve) {
            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const coords =
                        position && position.coords
                            ? position.coords
                            : null;

                    if (!coords) {
                        resolve({
                            ...base,
                            location_permission: 'unavailable',
                        });

                        return;
                    }

                    const latitude =
                        Number(coords.latitude);

                    const longitude =
                        Number(coords.longitude);

                    const accuracy =
                        Number(coords.accuracy);

                    if (
                        !Number.isFinite(latitude)
                        || !Number.isFinite(longitude)
                    ) {
                        resolve({
                            ...base,
                            location_permission: 'unavailable',
                        });

                        return;
                    }

                    resolve({
                        latitude,
                        longitude,
                        location_accuracy:
                            Number.isFinite(accuracy)
                                ? Math.max(0, accuracy)
                                : null,
                        location_permission: 'granted',
                    });
                },
                function (error) {
                    let permission =
                        initialPermission === 'prompt'
                            ? 'unavailable'
                            : initialPermission;

                    if (error && error.code === 1) {
                        permission = 'denied';
                    }

                    resolve({
                        ...base,
                        location_permission: permission,
                    });
                },
                {
                    enableHighAccuracy: highAccuracy,
                    timeout: geolocationTimeout,
                    maximumAge: geolocationMaximumAge,
                }
            );
        });
    }

    async function collectLoginSecurityContext() {
        const location =
            await getPreciseLocation();

        return {
            browser_timezone:
                getBrowserTimezone(),

            latitude:
                location.latitude,

            longitude:
                location.longitude,

            location_accuracy:
                location.location_accuracy,

            location_permission:
                location.location_permission,
        };
    }

    async function storeLoginSecurityContext() {
        try {
            const context =
                await collectLoginSecurityContext();

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
                            JSON.stringify(context),
                    }
                );

            return response.ok;
        } catch (error) {
            return false;
        }
    }

    document
        .querySelectorAll('form[data-login-security-form data-auth-transition-form]')
        .forEach(function (form) {
            form.addEventListener(
                'submit',
                async function (event) {
                    if (
                        form.dataset.loginSecuritySubmitting === '1'
                    ) {
                        return;
                    }

                    event.preventDefault();

                    markAuthTransition(
                        form.getAttribute('action') || 'login-form'
                    );

                    form.dataset.loginSecuritySubmitting = '1';

                    const submit =
                        form.querySelector('[type="submit"]');

                    if (submit) {
                        submit.disabled = true;
                        submit.dataset.originalText =
                            submit.textContent.trim();
                        submit.textContent =
                            'Beveiliging controleren…';
                    }

                    try {
                        await storeLoginSecurityContext();
                    } finally {
                        HTMLFormElement
                            .prototype
                            .submit
                            .call(form);
                    }
                }
            );
        });

    document
        .querySelectorAll('a[data-login-security-oauth data-auth-transition-link]')
        .forEach(function (link) {
            link.addEventListener(
                'click',
                async function (event) {
                    const destination =
                        link.getAttribute('href');

                    if (!destination) {
                        return;
                    }

                    if (
                        link.dataset.loginSecurityOpening === '1'
                    ) {
                        return;
                    }

                    event.preventDefault();

                    link.dataset.loginSecurityOpening = '1';

                    link.setAttribute(
                        'aria-busy',
                        'true'
                    );

                    try {
                        await storeLoginSecurityContext();
                    } finally {
                        window.location.assign(destination);
                    }
                }
            );
        });

    window.addEventListener(
        'pageshow',
        function () {
            document
                .querySelectorAll('form[data-login-security-form data-auth-transition-form]')
                .forEach(function (form) {
                    delete form.dataset.loginSecuritySubmitting;

                    const submit =
                        form.querySelector('[type="submit"]');

                    if (!submit) {
                        return;
                    }

                    submit.disabled = false;

                    if (submit.dataset.originalText) {
                        submit.textContent =
                            submit.dataset.originalText;
                    }
                });

            document
                .querySelectorAll('a[data-login-security-oauth data-auth-transition-link]')
                .forEach(function (link) {
                    delete link.dataset.loginSecurityOpening;
                    link.removeAttribute('aria-busy');
                });
        }
    );
});
</script>
@endpush
