@extends('layouts.site-layout')

@section('title', 'Inloggen | Mashal Studio')

@section(
    'meta_description',
    'Log veilig in bij Mashal Studio met wachtwoord, e-mailcode, magic link of je favoriete social login.'
)

@push('styles')
<style>
    :root {
        --login-bg: #07080b;
        --login-panel: #0d1015;
        --login-panel-2: #12161d;
        --login-text: #f7f7f4;
        --login-muted: #808792;
        --login-muted-2: #5d6570;
        --login-line: rgba(255,255,255,.072);
        --login-gold: #e3b36b;
        --login-gold-light: #f3d69a;
        --login-success: #67d990;
        --login-danger: #f47d7d;
    }

    .login-page {
        position: relative;
        min-height: calc(100vh - 76px);
        overflow: hidden;
        color: var(--login-text);
        background:
            radial-gradient(circle at 11% 8%, rgba(227,179,107,.08), transparent 30rem),
            radial-gradient(circle at 88% 10%, rgba(105,91,255,.055), transparent 31rem),
            linear-gradient(180deg,#07080b,#090b0f);
    }

    .login-page::before {
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

    .login-layout {
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

    .login-showcase {
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

    .login-showcase::before {
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

    .login-showcase::after {
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

    .login-kicker {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: #d9aa65;
        font-size: 9px;
        font-weight: 950;
        letter-spacing: .19em;
        text-transform: uppercase;
    }

    .login-kicker::before {
        content: "";
        width: 31px;
        height: 1px;
        background: linear-gradient(90deg,#e2b36c,transparent);
    }

    .login-headline {
        max-width: 760px;
        margin: 18px 0 0;
        color: #f9f9f6;
        font-size: clamp(56px,6.2vw,92px);
        line-height: .91;
        letter-spacing: -.072em;
        font-weight: 950;
        text-wrap: balance;
    }

    .login-headline span {
        display: block;
        color: #f1cf91;
    }

    .login-intro {
        max-width: 625px;
        margin: 24px 0 0;
        color: #9198a2;
        font-size: 13px;
        line-height: 1.86;
    }

    .login-trust {
        margin-top: 28px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .login-trust-pill {
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

    .login-trust-pill::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--login-success);
        box-shadow: 0 0 0 4px rgba(103,217,144,.05);
    }

    .pending-upload {
        margin-top: 17px;
        padding: 14px;
        display: flex;
        align-items: flex-start;
        gap: 11px;
        border: 1px solid rgba(103,217,144,.12);
        border-radius: 14px;
        color: #92cfa5;
        background: rgba(103,217,144,.035);
    }

    .pending-upload-mark {
        width: 30px;
        height: 30px;
        flex: 0 0 30px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(103,217,144,.14);
        border-radius: 9px;
        font-size: 9px;
        font-weight: 950;
    }

    .pending-upload strong {
        display: block;
        color: #b9dec4;
        font-size: 9px;
    }

    .pending-upload span {
        display: block;
        margin-top: 3px;
        color: #6f987b;
        font-size: 8px;
        line-height: 1.6;
    }

    /*
    |--------------------------------------------------------------------------
    | Mock editor
    |--------------------------------------------------------------------------
    */

    .workspace-preview {
        margin-top: 42px;
        padding: 12px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 21px;
        background: rgba(7,9,12,.63);
        box-shadow: 0 30px 85px rgba(0,0,0,.35);
        backdrop-filter: blur(15px);
    }

    .preview-topbar {
        min-height: 44px;
        padding: 0 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border-bottom: 1px solid rgba(255,255,255,.05);
    }

    .preview-dots {
        display: flex;
        gap: 5px;
    }

    .preview-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: rgba(255,255,255,.13);
    }

    .preview-dot:first-child {
        background: rgba(227,179,107,.63);
    }

    .preview-title,
    .preview-secure {
        color: #606873;
        font-size: 6px;
        font-weight: 950;
        letter-spacing: .10em;
        text-transform: uppercase;
    }

    .preview-secure {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .preview-secure::before {
        content: "";
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: var(--login-success);
    }

    .preview-body {
        min-height: 295px;
        display: grid;
        grid-template-columns: 66px minmax(0,1fr) 112px;
    }

    .preview-toolbar {
        padding: 11px 7px;
        display: grid;
        align-content: start;
        gap: 6px;
        border-right: 1px solid rgba(255,255,255,.045);
    }

    .preview-tool {
        aspect-ratio: 1;
        display: grid;
        place-items: center;
        border: 1px solid rgba(255,255,255,.045);
        border-radius: 9px;
        color: #515964;
        background: rgba(255,255,255,.01);
        font-size: 8px;
        font-weight: 950;
    }

    .preview-tool.active {
        border-color: rgba(227,179,107,.11);
        color: #d2a35f;
        background: rgba(227,179,107,.035);
    }

    .preview-canvas {
        position: relative;
        min-width: 0;
        padding: 22px;
        display: grid;
        place-items: center;
        background:
            linear-gradient(45deg, rgba(255,255,255,.01) 25%, transparent 25%),
            linear-gradient(-45deg, rgba(255,255,255,.01) 25%, transparent 25%),
            linear-gradient(45deg, transparent 75%, rgba(255,255,255,.01) 75%),
            linear-gradient(-45deg, transparent 75%, rgba(255,255,255,.01) 75%),
            #0a0c10;
        background-size: 20px 20px;
        background-position: 0 0, 0 10px, 10px -10px, -10px 0;
    }

    .preview-image {
        position: relative;
        width: min(100%,330px);
        aspect-ratio: 16/10;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.09);
        border-radius: 12px;
        background:
            radial-gradient(circle at 70% 26%, rgba(227,179,107,.28), transparent 9rem),
            radial-gradient(circle at 26% 73%, rgba(93,111,255,.17), transparent 10rem),
            linear-gradient(145deg,#242a34,#0d1116);
        box-shadow: 0 25px 65px rgba(0,0,0,.38);
    }

    .preview-image::before {
        content: "";
        position: absolute;
        left: 12%;
        top: 14%;
        width: 31%;
        height: 67%;
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 45% 55% 39% 61% / 51% 42% 58% 49%;
        background: linear-gradient(145deg,rgba(255,255,255,.11),rgba(255,255,255,.018));
        transform: rotate(-8deg);
    }

    .preview-image::after {
        content: "";
        position: absolute;
        right: 10%;
        top: 20%;
        width: 40%;
        height: 58%;
        border: 1px solid rgba(227,179,107,.13);
        border-radius: 17px;
        background: linear-gradient(145deg,rgba(227,179,107,.11),rgba(255,255,255,.014));
        transform: rotate(5deg);
    }

    .preview-properties {
        padding: 11px 9px;
        display: grid;
        align-content: start;
        gap: 7px;
        border-left: 1px solid rgba(255,255,255,.045);
    }

    .preview-property {
        padding: 8px;
        border: 1px solid rgba(255,255,255,.045);
        border-radius: 8px;
        background: rgba(255,255,255,.01);
    }

    .preview-property small {
        display: block;
        color: #4f5761;
        font-size: 5px;
        font-weight: 900;
        letter-spacing: .09em;
        text-transform: uppercase;
    }

    .preview-property strong {
        display: block;
        margin-top: 4px;
        color: #99a0a9;
        font-size: 7px;
    }

    .preview-footer {
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

    .preview-footer span:first-child {
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
    | Auth panel
    |--------------------------------------------------------------------------
    */

    .auth-panel {
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

    .auth-shell {
        width: 100%;
        max-width: 550px;
        margin: auto;
    }

    .auth-brand {
        margin-bottom: 26px;
        display: flex;
        align-items: center;
        gap: 11px;
    }

    .auth-brand-mark {
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

    .auth-brand-copy strong,
    .auth-brand-copy span {
        display: block;
    }

    .auth-brand-copy strong {
        color: #f1f2f3;
        font-size: 13px;
    }

    .auth-brand-copy span {
        margin-top: 4px;
        color: #5d6570;
        font-size: 6px;
        font-weight: 900;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .auth-kicker {
        display: block;
        margin-bottom: 8px;
        color: #a37c48;
        font-size: 7px;
        font-weight: 950;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .auth-title {
        margin: 0;
        color: #f5f6f7;
        font-size: clamp(38px,4vw,52px);
        line-height: .98;
        letter-spacing: -.057em;
        font-weight: 950;
    }

    .auth-subtitle {
        max-width: 520px;
        margin: 12px 0 22px;
        color: #727984;
        font-size: 10px;
        line-height: 1.75;
    }

    .auth-message {
        margin-bottom: 13px;
        padding: 12px 13px;
        border-radius: 11px;
        font-size: 8px;
        line-height: 1.65;
    }

    .auth-message.success {
        border: 1px solid rgba(103,217,144,.12);
        color: #a2d9b3;
        background: rgba(103,217,144,.035);
    }

    .auth-message.error {
        border: 1px solid rgba(240,131,131,.12);
        color: #dda2a2;
        background: rgba(240,131,131,.035);
    }

    .auth-message.info {
        border: 1px solid rgba(227,179,107,.11);
        color: #c4a775;
        background: rgba(227,179,107,.03);
    }

    .auth-message ul {
        margin: 6px 0 0 15px;
        padding: 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Tabs
    |--------------------------------------------------------------------------
    */

    .auth-tabs {
        margin-bottom: 16px;
        padding: 4px;
        display: grid;
        grid-template-columns: repeat(3,1fr);
        gap: 4px;
        border: 1px solid rgba(255,255,255,.055);
        border-radius: 13px;
        background: rgba(255,255,255,.01);
    }

    .auth-tab {
        min-height: 39px;
        padding: 0 8px;
        border: 1px solid transparent;
        border-radius: 9px;
        color: #646c76;
        background: transparent;
        font-size: 7px;
        font-weight: 900;
        cursor: pointer;
        transition:
            color .2s ease,
            border-color .2s ease,
            background .2s ease;
    }

    .auth-tab.active {
        border-color: rgba(227,179,107,.10);
        color: #d3a666;
        background: rgba(227,179,107,.035);
    }

    .auth-panel-section {
        display: none;
    }

    .auth-panel-section.active {
        display: block;
    }

    /*
    |--------------------------------------------------------------------------
    | Password login
    |--------------------------------------------------------------------------
    */

    .password-card,
    .passwordless-card {
        padding: 17px;
        border: 1px solid rgba(255,255,255,.055);
        border-radius: 16px;
        background: rgba(255,255,255,.01);
    }

    .auth-field {
        margin-bottom: 13px;
    }

    .auth-label-row {
        margin-bottom: 7px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .auth-label-row label {
        color: #959ca5;
        font-size: 7px;
        font-weight: 900;
        letter-spacing: .06em;
    }

    .auth-field-error {
        color: #dc9292;
        font-size: 7px;
        font-weight: 850;
    }

    .auth-input-wrap {
        position: relative;
    }

    .auth-input {
        width: 100%;
        min-height: 49px;
        padding: 0 42px 0 13px;
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

    .auth-input:focus {
        border-color: rgba(227,179,107,.27);
        background: rgba(227,179,107,.019);
        box-shadow: 0 0 0 4px rgba(227,179,107,.035);
    }

    .auth-input::placeholder {
        color: #4b535d;
    }

    .auth-input-icon {
        position: absolute;
        right: 13px;
        top: 50%;
        transform: translateY(-50%);
        color: #5b636d;
        font-size: 9px;
        pointer-events: none;
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

    .auth-options {
        margin: 3px 0 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 13px;
    }

    .remember {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #727984;
        font-size: 7px;
        cursor: pointer;
    }

    .remember input {
        width: 14px;
        height: 14px;
        accent-color: #d5a05a;
    }

    .text-link {
        color: #b68a4e;
        text-decoration: none;
        font-size: 7px;
        font-weight: 900;
    }

    .text-link:hover {
        color: #e2b571;
    }

    .auth-submit {
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

    .auth-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 21px 46px rgba(227,179,107,.20);
    }

    .auth-submit:disabled {
        opacity: .60;
        cursor: wait;
        transform: none;
    }

    /*
    |--------------------------------------------------------------------------
    | Passwordless
    |--------------------------------------------------------------------------
    */

    .passwordless-card + .passwordless-card {
        margin-top: 9px;
    }

    .passwordless-head {
        margin-bottom: 13px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .passwordless-icon {
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(227,179,107,.12);
        border-radius: 11px;
        color: #d6a864;
        background: rgba(227,179,107,.035);
        font-size: 10px;
        font-weight: 950;
    }

    .passwordless-copy strong,
    .passwordless-copy span {
        display: block;
    }

    .passwordless-copy strong {
        color: #d5d8dc;
        font-size: 9px;
    }

    .passwordless-copy span {
        margin-top: 3px;
        color: #626a74;
        font-size: 7px;
        line-height: 1.55;
    }

    .passwordless-form {
        display: grid;
        grid-template-columns: minmax(0,1fr) auto;
        gap: 7px;
    }

    .passwordless-input {
        min-width: 0;
        min-height: 44px;
        padding: 0 11px;
        border: 1px solid rgba(255,255,255,.065);
        border-radius: 10px;
        outline: none;
        color: #e0e3e6;
        background: rgba(255,255,255,.014);
        font-size: 8px;
    }

    .passwordless-input:focus {
        border-color: rgba(227,179,107,.23);
        box-shadow: 0 0 0 4px rgba(227,179,107,.03);
    }

    .passwordless-submit {
        min-height: 44px;
        padding: 0 13px;
        border: 1px solid rgba(227,179,107,.13);
        border-radius: 10px;
        color: #d0a15f;
        background: rgba(227,179,107,.035);
        font-size: 7px;
        font-weight: 950;
        cursor: pointer;
        white-space: nowrap;
    }

    .passwordless-submit:disabled {
        opacity: .55;
        cursor: wait;
    }

    .passwordless-note {
        margin-top: 8px;
        color: #505862;
        font-size: 6px;
        line-height: 1.55;
    }

    /*
    |--------------------------------------------------------------------------
    | OAuth
    |--------------------------------------------------------------------------
    */

    .oauth-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .oauth-button {
        min-height: 62px;
        padding: 0 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 13px;
        color: #cdd1d6;
        background:
            linear-gradient(145deg,rgba(255,255,255,.022),rgba(255,255,255,.006));
        text-decoration: none;
        transition:
            transform .2s ease,
            border-color .2s ease,
            background .2s ease;
    }

    .oauth-button:hover {
        transform: translateY(-2px);
        border-color: rgba(227,179,107,.15);
        background:
            linear-gradient(145deg,rgba(227,179,107,.04),rgba(255,255,255,.008));
    }

    .oauth-icon {
        width: 33px;
        height: 33px;
        flex: 0 0 33px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(255,255,255,.06);
        border-radius: 10px;
        font-size: 10px;
        font-weight: 950;
    }

    .oauth-icon.google {
        color: #4285f4;
        background: #f3f3f3;
    }

    .oauth-icon.github {
        color: #fff;
        background: #15181c;
    }

    .oauth-icon.facebook {
        color: #fff;
        background: #1877f2;
    }

    .oauth-icon.tiktok {
        color: #fff;
        background: linear-gradient(135deg,#122f31,#2a0d15);
        text-shadow: -1px 0 #25f4ee, 1px 0 #fe2c55;
    }

    .oauth-copy {
        min-width: 0;
    }

    .oauth-copy strong,
    .oauth-copy span {
        display: block;
    }

    .oauth-copy strong {
        color: #d7dade;
        font-size: 8px;
    }

    .oauth-copy span {
        margin-top: 3px;
        color: #59616b;
        font-size: 6px;
        line-height: 1.4;
    }

    /*
    |--------------------------------------------------------------------------
    | Bottom cards
    |--------------------------------------------------------------------------
    */

    .auth-separator {
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

    .auth-separator::before,
    .auth-separator::after {
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

    .register-card {
        padding: 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        border: 1px solid rgba(255,255,255,.055);
        border-radius: 13px;
        background: rgba(255,255,255,.01);
    }

    .register-copy strong,
    .register-copy span {
        display: block;
    }

    .register-copy strong {
        color: #cbd0d5;
        font-size: 8px;
    }

    .register-copy span {
        margin-top: 3px;
        color: #5b636d;
        font-size: 6px;
        line-height: 1.5;
    }

    .register-link {
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

    .security-note {
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
        .login-layout {
            grid-template-columns: 1fr;
            max-width: 900px;
        }

        .login-showcase {
            min-height: auto;
        }

        .auth-shell {
            max-width: 620px;
        }
    }

    @media (max-width: 720px) {
        .login-layout {
            width: min(calc(100% - 22px),900px);
            padding-top: 27px;
        }

        .login-showcase {
            padding: 28px 20px;
            border-radius: 22px;
        }

        .login-headline {
            font-size: clamp(49px,15vw,68px);
        }

        .preview-body {
            grid-template-columns: 56px minmax(0,1fr);
        }

        .preview-properties {
            display: none;
        }

        .showcase-stats {
            grid-template-columns: 1fr;
        }

        .auth-panel {
            padding: 24px 17px;
            border-radius: 22px;
        }
    }

    @media (max-width: 560px) {
        .workspace-preview {
            display: none;
        }

        .auth-tabs,
        .oauth-grid,
        .passwordless-form {
            grid-template-columns: 1fr;
        }

        .passwordless-submit {
            width: 100%;
        }

        .auth-options,
        .register-card {
            align-items: flex-start;
            flex-direction: column;
        }

        .register-link {
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
<section class="login-page">
    <div class="login-layout">

        <aside class="login-showcase">
            <div>
                <span class="login-kicker">
                    Mashal Image Studio
                </span>

                <h2 class="login-headline">
                    Je afbeelding wacht.
                    <span>Log in en ga door.</span>
                </h2>

                <p class="login-intro">
                    Open je persoonlijke image workspace, ga terug naar je
                    geüploade afbeelding en beheer originelen, bewerkingen
                    en opgeslagen versies vanuit één account.
                </p>

                <div class="login-trust">
                    <span class="login-trust-pill">
                        Private workspace
                    </span>

                    <span class="login-trust-pill">
                        Account ownership
                    </span>

                    <span class="login-trust-pill">
                        Secure login context
                    </span>
                </div>

                @if (session()->has('pending_image'))
                    <div class="pending-upload">
                        <span class="pending-upload-mark">
                            ✓
                        </span>

                        <div>
                            <strong>
                                Je afbeelding staat klaar
                            </strong>

                            <span>
                                Rond je login af. Daarna kan Mashal Studio
                                de tijdelijke upload aan jouw account koppelen
                                en je direct naar de editor sturen.
                            </span>
                        </div>
                    </div>
                @endif
            </div>

            <div
                class="workspace-preview"
                aria-hidden="true"
            >
                <div class="preview-topbar">
                    <div class="preview-dots">
                        <span class="preview-dot"></span>
                        <span class="preview-dot"></span>
                        <span class="preview-dot"></span>
                    </div>

                    <span class="preview-title">
                        Mashal Studio editor
                    </span>

                    <span class="preview-secure">
                        Private
                    </span>
                </div>

                <div class="preview-body">
                    <div class="preview-toolbar">
                        <span class="preview-tool active">↔</span>
                        <span class="preview-tool">⌗</span>
                        <span class="preview-tool">↻</span>
                        <span class="preview-tool">↓</span>
                        <span class="preview-tool">◇</span>
                    </div>

                    <div class="preview-canvas">
                        <div class="preview-image"></div>
                    </div>

                    <div class="preview-properties">
                        <div class="preview-property">
                            <small>Width</small>
                            <strong>1080 px</strong>
                        </div>

                        <div class="preview-property">
                            <small>Height</small>
                            <strong>1350 px</strong>
                        </div>

                        <div class="preview-property">
                            <small>Format</small>
                            <strong>WEBP</strong>
                        </div>

                        <div class="preview-property">
                            <small>Quality</small>
                            <strong>84%</strong>
                        </div>
                    </div>
                </div>

                <div class="preview-footer">
                    <span>Original preserved</span>
                    <span>Resize · Crop · Compress · Convert</span>
                </div>
            </div>

            <div class="showcase-stats">
                <div class="showcase-stat">
                    <small>Upload</small>
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

        <main class="auth-panel">
            <div class="auth-shell">

                <div class="auth-brand">
                    <div class="auth-brand-mark">
                        M
                    </div>

                    <div class="auth-brand-copy">
                        <strong>
                            Mashal Studio
                        </strong>

                        <span>
                            Secure image workspace
                        </span>
                    </div>
                </div>

                <span class="auth-kicker">
                    Secure account access
                </span>

                <h1 class="auth-title">
                    Inloggen
                </h1>

                <p class="auth-subtitle">
                    Kies hoe je wilt inloggen. Je kunt je wachtwoord,
                    een eenmalige e-mailcode, een magic link of een gekoppelde
                    externe provider gebruiken.
                </p>

                @if (session('status'))
                    <div
                        class="auth-message info"
                        role="status"
                    >
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('success'))
                    <div
                        class="auth-message success"
                        role="status"
                    >
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div
                        class="auth-message error"
                        role="alert"
                    >
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div
                        class="auth-message error"
                        role="alert"
                    >
                        <strong>
                            Inloggen is niet gelukt.
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

                <div
                    class="auth-tabs"
                    role="tablist"
                    aria-label="Inlogmethode kiezen"
                >
                    <button
                        class="auth-tab active"
                        id="authTabPassword"
                        type="button"
                        role="tab"
                        aria-selected="true"
                        aria-controls="authPanelPassword"
                        data-auth-tab="password"
                    >
                        Wachtwoord
                    </button>

                    <button
                        class="auth-tab"
                        id="authTabEmail"
                        type="button"
                        role="tab"
                        aria-selected="false"
                        aria-controls="authPanelEmail"
                        data-auth-tab="passwordless"
                    >
                        E-mail
                    </button>

                    <button
                        class="auth-tab"
                        id="authTabOauth"
                        type="button"
                        role="tab"
                        aria-selected="false"
                        aria-controls="authPanelOauth"
                        data-auth-tab="oauth"
                    >
                        Social
                    </button>
                </div>

                <section
                    class="auth-panel-section active"
                    id="authPanelPassword"
                    role="tabpanel"
                    aria-labelledby="authTabPassword"
                    data-auth-panel="password"
                >
                    <form
                        class="password-card"
                        method="POST"
                        action="{{ route('login.submit') }}"
                        data-login-security-form
                    >
                        @csrf

                        <div class="auth-field">
                            <div class="auth-label-row">
                                <label for="email">
                                    E-mailadres
                                </label>

                                @error('email')
                                    <span class="auth-field-error">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="auth-input-wrap">
                                <input
                                    class="auth-input"
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
                                    autofocus
                                >

                                <span
                                    class="auth-input-icon"
                                    aria-hidden="true"
                                >
                                    @
                                </span>
                            </div>
                        </div>

                        <div class="auth-field">
                            <div class="auth-label-row">
                                <label for="password">
                                    Wachtwoord
                                </label>

                                @error('password')
                                    <span class="auth-field-error">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="auth-input-wrap">
                                <input
                                    class="auth-input"
                                    id="password"
                                    type="password"
                                    name="password"
                                    placeholder="Vul je wachtwoord in"
                                    autocomplete="current-password"
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
                        </div>

                        <div class="auth-options">
                            <label
                                class="remember"
                                for="remember"
                            >
                                <input
                                    id="remember"
                                    type="checkbox"
                                    name="remember"
                                    value="1"
                                    @checked(old('remember'))
                                >

                                <span>
                                    Onthoud mij
                                </span>
                            </label>

                            <a
                                class="text-link"
                                href="{{ route('password.request') }}"
                            >
                                Wachtwoord vergeten?
                            </a>
                        </div>

                        <button
                            class="auth-submit"
                            type="submit"
                            data-submit-label="Inloggen bij Mashal Studio"
                        >
                            Inloggen bij Mashal Studio
                        </button>
                    </form>
                </section>

                <section
                    class="auth-panel-section"
                    id="authPanelEmail"
                    role="tabpanel"
                    aria-labelledby="authTabEmail"
                    data-auth-panel="passwordless"
                    hidden
                >
                    <div class="passwordless-card">
                        <div class="passwordless-head">
                            <span class="passwordless-icon">
                                6
                            </span>

                            <div class="passwordless-copy">
                                <strong>
                                    Eenmalige e-mailcode
                                </strong>

                                <span>
                                    Ontvang een tijdelijke 6-cijferige code.
                                    Je wachtwoord is niet nodig.
                                </span>
                            </div>
                        </div>

                        <form
                            class="passwordless-form"
                            method="POST"
                            action="{{ route('email-login.send') }}"
                            data-login-security-form
                        >
                            @csrf

                            <input
                                class="passwordless-input"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="naam@example.com"
                                autocomplete="email"
                                inputmode="email"
                                aria-label="E-mailadres voor e-mailcode"
                                required
                            >

                            <button
                                class="passwordless-submit"
                                type="submit"
                                data-submit-label="Stuur code"
                            >
                                Stuur code
                            </button>
                        </form>

                        <div class="passwordless-note">
                            De code is 5 minuten geldig.
                        </div>
                    </div>

                    <div class="passwordless-card">
                        <div class="passwordless-head">
                            <span class="passwordless-icon">
                                ↗
                            </span>

                            <div class="passwordless-copy">
                                <strong>
                                    Veilige loginlink
                                </strong>

                                <span>
                                    Ontvang een persoonlijke magic link.
                                    Geen wachtwoord en geen code nodig.
                                </span>
                            </div>
                        </div>

                        <form
                            class="passwordless-form"
                            method="POST"
                            action="{{ route('email-login.link.send') }}"
                            data-login-security-form
                        >
                            @csrf

                            <input
                                class="passwordless-input"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="naam@example.com"
                                autocomplete="email"
                                inputmode="email"
                                aria-label="E-mailadres voor veilige loginlink"
                                required
                            >

                            <button
                                class="passwordless-submit"
                                type="submit"
                                data-submit-label="Stuur loginlink"
                            >
                                Stuur loginlink
                            </button>
                        </form>

                        <div class="passwordless-note">
                            De link is 10 minuten geldig en kan één keer worden gebruikt.
                        </div>
                    </div>
                </section>

                <section
                    class="auth-panel-section"
                    id="authPanelOauth"
                    role="tabpanel"
                    aria-labelledby="authTabOauth"
                    data-auth-panel="oauth"
                    hidden
                >
                    <div class="oauth-grid">
                        <a
                            class="oauth-button"
                            data-login-security-oauth
                            href="{{ route('google.redirect') }}"
                            aria-label="Doorgaan met Google"
                        >
                            <span class="oauth-icon google">
                                G
                            </span>

                            <span class="oauth-copy">
                                <strong>Google</strong>
                                <span>Doorgaan met Google</span>
                            </span>
                        </a>

                        <a
                            class="oauth-button"
                            data-login-security-oauth
                            href="{{ route('github.redirect') }}"
                            aria-label="Doorgaan met GitHub"
                        >
                            <span class="oauth-icon github">
                                GH
                            </span>

                            <span class="oauth-copy">
                                <strong>GitHub</strong>
                                <span>Doorgaan met GitHub</span>
                            </span>
                        </a>

                        <a
                            class="oauth-button"
                            data-login-security-oauth
                            href="{{ route('facebook.redirect') }}"
                            aria-label="Doorgaan met Facebook"
                        >
                            <span class="oauth-icon facebook">
                                f
                            </span>

                            <span class="oauth-copy">
                                <strong>Facebook</strong>
                                <span>Doorgaan met Facebook</span>
                            </span>
                        </a>

                        <a
                            class="oauth-button"
                            data-login-security-oauth
                            href="{{ route('tiktok.redirect') }}"
                            aria-label="Doorgaan met TikTok"
                        >
                            <span class="oauth-icon tiktok">
                                ♪
                            </span>

                            <span class="oauth-copy">
                                <strong>TikTok</strong>
                                <span>Doorgaan met TikTok</span>
                            </span>
                        </a>
                    </div>

                    <div class="security-note">
                        <span class="security-mark">
                            ✓
                        </span>

                        <span>
                            Je wordt doorgestuurd naar de gekozen provider.
                            Mashal Studio ontvangt niet het wachtwoord van je
                            Google-, GitHub-, Facebook- of TikTok-account.
                        </span>
                    </div>
                </section>

                <div class="auth-separator">
                    Nieuw bij Mashal Studio?
                </div>

                <div class="register-card">
                    <div class="register-copy">
                        <strong>
                            Nog geen account?
                        </strong>

                        <span>
                            Maak gratis een account en ga daarna verder
                            naar je persoonlijke image workspace.
                        </span>
                    </div>

                    <a
                        class="register-link"
                        href="{{ route('register') }}"
                    >
                        Registreren
                    </a>
                </div>

                <div class="security-note">
                    <span class="security-mark">
                        ✓
                    </span>

                    <span>
                        Mashal Studio vraagt je nooit om je wachtwoord
                        via e-mail, chat of telefoon te delen.
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
    /*
    |--------------------------------------------------------------------------
    | Password visibility
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('[data-toggle-password]')
        .forEach(function (button) {
            button.addEventListener('click', function () {
                const inputId =
                    button.getAttribute(
                        'data-toggle-password'
                    );

                const input =
                    document.getElementById(
                        inputId
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

    /*
    |--------------------------------------------------------------------------
    | Login method tabs
    |--------------------------------------------------------------------------
    */

    const authTabs =
        Array.from(
            document.querySelectorAll(
                '[data-auth-tab]'
            )
        );

    const authPanels =
        Array.from(
            document.querySelectorAll(
                '[data-auth-panel]'
            )
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

    /*
    |--------------------------------------------------------------------------
    | Login security context
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
            !navigator.permissions ||
            typeof navigator.permissions.query !==
                'function'
        ) {
            return 'prompt';
        }

        try {
            const status =
                await navigator.permissions.query({
                    name: 'geolocation'
                });

            if (
                status &&
                [
                    'granted',
                    'denied',
                    'prompt'
                ].includes(status.state)
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
            !navigator.geolocation ||
            typeof navigator.geolocation.getCurrentPosition !==
                'function'
        ) {
            return {
                ...base,
                location_permission: 'unsupported',
            };
        }

        const initialPermission =
            await getLocationPermissionState();

        if (
            initialPermission ===
            'denied'
        ) {
            return {
                ...base,
                location_permission: 'denied',
            };
        }

        return new Promise(function (resolve) {
            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const coords =
                        position &&
                        position.coords
                            ? position.coords
                            : null;

                    if (!coords) {
                        resolve({
                            ...base,
                            location_permission:
                                'unavailable',
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
                        !Number.isFinite(latitude) ||
                        !Number.isFinite(longitude)
                    ) {
                        resolve({
                            ...base,
                            location_permission:
                                'unavailable',
                        });

                        return;
                    }

                    resolve({
                        latitude,
                        longitude,
                        location_accuracy:
                            Number.isFinite(accuracy)
                                ? Math.max(
                                    0,
                                    accuracy
                                )
                                : null,
                        location_permission:
                            'granted',
                    });
                },

                function (error) {
                    let permission =
                        initialPermission === 'prompt'
                            ? 'unavailable'
                            : initialPermission;

                    if (
                        error &&
                        error.code === 1
                    ) {
                        permission =
                            'denied';
                    }

                    resolve({
                        ...base,
                        location_permission:
                            permission,
                    });
                },

                {
                    enableHighAccuracy:
                        highAccuracy,

                    timeout:
                        geolocationTimeout,

                    maximumAge:
                        geolocationMaximumAge,
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
                                context
                            ),
                    }
                );

            return response.ok;
        } catch (error) {
            return false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Protected form submits
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(
            'form[data-login-security-form]'
        )
        .forEach(function (form) {
            form.addEventListener(
                'submit',
                async function (event) {
                    if (
                        form.dataset
                            .loginSecuritySubmitting ===
                        '1'
                    ) {
                        return;
                    }

                    event.preventDefault();

                    form.dataset
                        .loginSecuritySubmitting =
                        '1';

                    const submit =
                        form.querySelector(
                            '[type="submit"]'
                        );

                    if (submit) {
                        submit.disabled =
                            true;

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

    /*
    |--------------------------------------------------------------------------
    | OAuth redirects
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(
            'a[data-login-security-oauth]'
        )
        .forEach(function (link) {
            link.addEventListener(
                'click',
                async function (event) {
                    const destination =
                        link.getAttribute(
                            'href'
                        );

                    if (!destination) {
                        return;
                    }

                    if (
                        link.dataset
                            .loginSecurityOpening ===
                        '1'
                    ) {
                        return;
                    }

                    event.preventDefault();

                    link.dataset
                        .loginSecurityOpening =
                        '1';

                    link.setAttribute(
                        'aria-busy',
                        'true'
                    );

                    try {
                        await storeLoginSecurityContext();
                    } finally {
                        window.location.assign(
                            destination
                        );
                    }
                }
            );
        });

    /*
    |--------------------------------------------------------------------------
    | Restore buttons after browser back/forward cache
    |--------------------------------------------------------------------------
    */

    window.addEventListener(
        'pageshow',
        function () {
            document
                .querySelectorAll(
                    'form[data-login-security-form]'
                )
                .forEach(function (form) {
                    delete form.dataset
                        .loginSecuritySubmitting;

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
                        submit.dataset
                            .originalText
                    ) {
                        submit.textContent =
                            submit.dataset
                                .originalText;
                    }
                });

            document
                .querySelectorAll(
                    'a[data-login-security-oauth]'
                )
                .forEach(function (link) {
                    delete link.dataset
                        .loginSecurityOpening;

                    link.removeAttribute(
                        'aria-busy'
                    );
                });
        }
    );
});
</script>
@endpush
