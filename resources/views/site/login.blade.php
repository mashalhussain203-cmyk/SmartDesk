@extends('layouts.site-layout')

@section('title', 'Inloggen | Mashal Studio')

@section(
    'meta_description',
    'Log veilig in bij Mashal Studio met wachtwoord, e-mailcode, magic link of je favoriete social login.'
)

@push('styles')
<style>
    :root {
        --auth-bg: #050506;
        --auth-panel: #0e0e11;
        --auth-panel-2: #141418;
        --auth-panel-3: #1a1a1f;
        --auth-text: #f7f7f8;
        --auth-muted: #96969e;
        --auth-muted-2: #686870;
        --auth-line: rgba(255,255,255,.09);
        --auth-line-strong: rgba(255,255,255,.15);
        --auth-gold: #f1d84a;
        --auth-gold-2: #d8b91e;
        --auth-orange: #ff7a24;
        --auth-green: #45df8b;
        --auth-danger: #ff8f8f;
        --auth-radius: 24px;
        --auth-shadow: 0 24px 70px rgba(0,0,0,.36);
    }

    /*
    |--------------------------------------------------------------------------
    | Page
    |--------------------------------------------------------------------------
    */

    .auth-page,
    .auth-page * {
        box-sizing: border-box;
    }

    .auth-page {
        position: relative;
        width: 100%;
        min-height: calc(100dvh - var(--studio-header-height, 78px));
        overflow-x: clip;
        color: var(--auth-text);
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

    .auth-page::before {
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

    .auth-shell {
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

    .auth-intro {
        width: min(100%, 560px);
        margin: 0 auto 26px;
        text-align: center;
        animation:
            auth-enter .42s cubic-bezier(.2,.8,.2,1) both;
    }

    .auth-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        color: var(--auth-gold);
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .15em;
        text-transform: uppercase;
    }

    .auth-eyebrow::before,
    .auth-eyebrow::after {
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

    .auth-eyebrow::after {
        transform: scaleX(-1);
    }

    .auth-title {
        margin: 0;
        color: #fff;
        font-size: clamp(36px, 6vw, 62px);
        line-height: .96;
        font-weight: 930;
        letter-spacing: -.055em;
        text-wrap: balance;
    }

    .auth-title span {
        display: block;
        margin-top: 5px;
        color: var(--auth-orange);
    }

    .auth-lead {
        max-width: 500px;
        margin: 16px auto 0;
        color: var(--auth-muted);
        font-size: 12px;
        line-height: 1.7;
    }

    /*
    |--------------------------------------------------------------------------
    | Main card
    |--------------------------------------------------------------------------
    */

    .auth-card-wrap {
        width: min(100%, 470px);
        margin-inline: auto;
        animation:
            auth-enter .46s .05s cubic-bezier(.2,.8,.2,1) both;
    }

    .auth-card {
        position: relative;
        width: 100%;
        overflow: hidden;
        border: 1px solid var(--auth-line-strong);
        border-radius: var(--auth-radius);
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
        box-shadow: var(--auth-shadow);
    }

    /*
     * Geen clip-path of backdrop-filter:
     * de kaart blijft stabiel en de rechteronderhoek kan niet meer vervormen.
     */
    .auth-card::before {
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

    .auth-card::after {
        content: "";
        position: absolute;
        right: 0;
        bottom: 0;
        width: 72px;
        height: 72px;
        pointer-events: none;
        border-right: 2px solid rgba(241,216,74,.55);
        border-bottom: 2px solid rgba(241,216,74,.55);
        border-radius: 0 0 calc(var(--auth-radius) - 1px) 0;
        opacity: .72;
    }

    .auth-card-inner {
        position: relative;
        z-index: 1;
        padding: 26px;
    }

    .auth-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 13px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(241,216,74,.22);
        border-radius: 14px;
        color: var(--auth-gold);
        background: rgba(241,216,74,.045);
    }

    .auth-icon svg {
        width: 23px;
        height: 23px;
    }

    .auth-heading {
        margin: 0;
        text-align: center;
        color: #fff;
        font-size: 25px;
        line-height: 1.1;
        font-weight: 900;
        letter-spacing: -.035em;
    }

    .auth-heading strong {
        color: var(--auth-gold);
        font-weight: inherit;
    }

    .auth-description {
        max-width: 360px;
        margin: 8px auto 18px;
        color: var(--auth-muted);
        text-align: center;
        font-size: 10px;
        line-height: 1.6;
    }

    /*
    |--------------------------------------------------------------------------
    | Login / Register switch
    |--------------------------------------------------------------------------
    */

    .auth-switch {
        width: min(100%, 280px);
        margin: 0 auto 16px;
        padding: 3px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3px;
        border: 1px solid var(--auth-line);
        border-radius: 11px;
        background: rgba(0,0,0,.22);
    }

    .auth-switch a {
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

    .auth-switch a:hover {
        color: #e3d96a;
        transform: translateY(-1px);
    }

    .auth-switch a.active {
        border-color: rgba(241,216,74,.18);
        color: var(--auth-gold);
        background: rgba(241,216,74,.055);
    }

    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    */

    .auth-message,
    .auth-pending {
        margin-bottom: 11px;
        padding: 10px 11px;
        border-radius: 10px;
        font-size: 9px;
        line-height: 1.55;
    }

    .auth-message.success {
        border: 1px solid rgba(69,223,139,.18);
        color: #adf4c9;
        background: rgba(69,223,139,.055);
    }

    .auth-message.error {
        border: 1px solid rgba(255,143,143,.18);
        color: #ffc2c2;
        background: rgba(255,90,90,.055);
    }

    .auth-message.info {
        border: 1px solid rgba(241,216,74,.17);
        color: #e9dc7a;
        background: rgba(241,216,74,.045);
    }

    .auth-message ul {
        margin: 5px 0 0 16px;
        padding: 0;
    }

    .auth-pending {
        border: 1px solid rgba(69,223,139,.15);
        color: #a9efc5;
        background: rgba(69,223,139,.045);
    }

    /*
    |--------------------------------------------------------------------------
    | Method tabs
    |--------------------------------------------------------------------------
    */

    .auth-tabs {
        margin: 12px 0 14px;
        padding: 3px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 3px;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 11px;
        background: rgba(0,0,0,.18);
    }

    .auth-tab {
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

    .auth-tab:hover {
        color: #cfcfcf;
    }

    .auth-tab.active {
        border-color: rgba(241,216,74,.18);
        color: var(--auth-gold);
        background: rgba(241,216,74,.055);
    }

    .auth-tab:active {
        transform: scale(.985);
    }

    .auth-panel {
        display: none;
    }

    .auth-panel.active {
        display: block;
        animation:
            auth-panel-in .18s ease-out both;
    }

    /*
    |--------------------------------------------------------------------------
    | Fields
    |--------------------------------------------------------------------------
    */

    .auth-field {
        margin-bottom: 12px;
    }

    .auth-label-row {
        min-height: 17px;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .auth-label-row label {
        color: #a8a8ae;
        font-size: 8px;
        font-weight: 850;
        letter-spacing: .035em;
    }

    .auth-field-error {
        color: #ffacac;
        font-size: 7.5px;
        font-weight: 800;
    }

    .auth-input-wrap {
        position: relative;
    }

    .auth-input {
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

    .auth-input::placeholder {
        color: #5c5c64;
    }

    .auth-input:focus {
        border-color: rgba(241,216,74,.48);
        background: rgba(241,216,74,.02);
        box-shadow: 0 0 0 3px rgba(241,216,74,.055);
    }

    .auth-input.with-toggle {
        padding-right: 78px;
    }

    .auth-password-toggle {
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

    .auth-options {
        margin: 2px 0 13px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .auth-remember {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #898991;
        font-size: 8px;
        cursor: pointer;
    }

    .auth-remember input {
        width: 14px;
        height: 14px;
        accent-color: var(--auth-gold);
    }

    .auth-small-link {
        color: #d8c64b;
        text-decoration: none;
        font-size: 8px;
        font-weight: 850;
    }

    .auth-small-link:hover {
        color: #fff074;
    }

    /*
    |--------------------------------------------------------------------------
    | Buttons
    |--------------------------------------------------------------------------
    */

    .auth-primary,
    .auth-secondary {
        font: inherit;
        cursor: pointer;
    }

    .auth-primary {
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

    .auth-primary:hover:not(:disabled) {
        transform: translateY(-1px);
        filter: brightness(1.045);
    }

    .auth-primary:active:not(:disabled) {
        transform: scale(.992);
    }

    .auth-primary:disabled {
        opacity: .66;
        cursor: wait;
    }

    .auth-secondary {
        min-height: 39px;
        padding: 0 12px;
        border: 1px solid rgba(241,216,74,.18);
        border-radius: 9px;
        color: var(--auth-gold);
        background: rgba(241,216,74,.045);
        font-size: 8px;
        font-weight: 850;
    }

    /*
    |--------------------------------------------------------------------------
    | Passwordless
    |--------------------------------------------------------------------------
    */

    .auth-passwordless-card {
        padding: 12px;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 12px;
        background: rgba(0,0,0,.16);
    }

    .auth-passwordless-card + .auth-passwordless-card {
        margin-top: 9px;
    }

    .auth-passwordless-head {
        margin-bottom: 10px;
        display: flex;
        align-items: flex-start;
        gap: 9px;
    }

    .auth-passwordless-icon {
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(241,216,74,.16);
        border-radius: 9px;
        color: var(--auth-gold);
        background: rgba(241,216,74,.035);
        font-size: 9px;
        font-weight: 900;
    }

    .auth-passwordless-copy strong,
    .auth-passwordless-copy span {
        display: block;
    }

    .auth-passwordless-copy strong {
        color: #e1e1e4;
        font-size: 8px;
    }

    .auth-passwordless-copy span {
        margin-top: 3px;
        color: #787880;
        font-size: 7px;
        line-height: 1.5;
    }

    .auth-passwordless-form {
        display: grid;
        grid-template-columns: minmax(0,1fr) auto;
        gap: 7px;
    }

    .auth-passwordless-form .auth-input {
        height: 39px;
    }

    /*
    |--------------------------------------------------------------------------
    | OAuth
    |--------------------------------------------------------------------------
    */

    .auth-oauth-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .auth-oauth {
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

    .auth-oauth:hover {
        transform: translateY(-1px);
        border-color: rgba(241,216,74,.18);
        background: rgba(241,216,74,.025);
    }

    .auth-oauth:active {
        transform: scale(.99);
    }

    .auth-oauth-icon {
        width: 27px;
        height: 27px;
        display: grid;
        place-items: center;
        flex: 0 0 27px;
    }

    .auth-oauth-icon svg {
        width: 23px;
        height: 23px;
    }

    .auth-oauth-copy {
        min-width: 0;
    }

    .auth-oauth-copy strong,
    .auth-oauth-copy span {
        display: block;
    }

    .auth-oauth-copy strong {
        overflow: hidden;
        font-size: 8px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .auth-oauth-copy span {
        margin-top: 2px;
        color: #717179;
        font-size: 6px;
    }

    .auth-oauth-grid > .auth-oauth:last-child:nth-child(odd) {
        grid-column: 1 / -1;
    }

    /*
    |--------------------------------------------------------------------------
    | Bottom
    |--------------------------------------------------------------------------
    */

    .auth-divider {
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

    .auth-divider::before,
    .auth-divider::after {
        content: "";
        flex: 1;
        height: 1px;
        background: rgba(255,255,255,.075);
    }

    .auth-register-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 11px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 11px;
        background: rgba(0,0,0,.14);
    }

    .auth-register-row span {
        color: #85858d;
        font-size: 7px;
        line-height: 1.5;
    }

    .auth-register-row a {
        flex: 0 0 auto;
    }

    .auth-security-note {
        margin-top: 11px;
        color: #6c6c73;
        font-size: 7px;
        line-height: 1.55;
        text-align: center;
    }

    .auth-footer {
        margin-top: 18px;
        color: #606067;
        text-align: center;
        font-size: 9px;
        animation:
            auth-enter .46s .09s cubic-bezier(.2,.8,.2,1) both;
    }

    /*
    |--------------------------------------------------------------------------
    | Animation
    |--------------------------------------------------------------------------
    |
    | Alleen opacity + transform. Geen blur, clip-path of continuously moving
    | layers. Daardoor blijft de animatie composited en licht.
    |
    */

    @keyframes auth-enter {
        from {
            opacity: 0;
            transform: translate3d(0, 12px, 0) scale(.992);
        }

        to {
            opacity: 1;
            transform: translate3d(0, 0, 0) scale(1);
        }
    }

    @keyframes auth-panel-in {
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
        .auth-shell {
            width: min(calc(100% - 20px), 1080px);
            padding-top: 26px;
            padding-bottom: 48px;
        }

        .auth-intro {
            margin-bottom: 20px;
        }

        .auth-title {
            font-size: clamp(34px, 12vw, 46px);
        }

        .auth-lead {
            font-size: 11px;
        }

        .auth-card-wrap {
            width: 100%;
            max-width: 470px;
        }

        .auth-card-inner {
            padding: 22px 18px 24px;
        }

        .auth-card::after {
            width: 52px;
            height: 52px;
        }

        .auth-oauth-grid {
            gap: 7px;
        }
    }

    @media (max-width: 390px) {
        .auth-shell {
            width: min(calc(100% - 14px), 1080px);
        }

        .auth-card-inner {
            padding-inline: 14px;
        }

        .auth-tabs {
            grid-template-columns: 1fr;
        }

        .auth-oauth-grid {
            grid-template-columns: 1fr;
        }

        .auth-oauth-grid > .auth-oauth:last-child:nth-child(odd) {
            grid-column: auto;
        }

        .auth-passwordless-form {
            grid-template-columns: 1fr;
        }

        .auth-secondary {
            width: 100%;
        }

        .auth-options,
        .auth-register-row {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .auth-intro,
        .auth-card-wrap,
        .auth-footer,
        .auth-panel.active {
            animation: none !important;
        }

        .auth-page *,
        .auth-page *::before,
        .auth-page *::after {
            scroll-behavior: auto !important;
            transition-duration: .01ms !important;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Lightweight animated atmosphere
    |--------------------------------------------------------------------------
    |
    | Beweging blijft zichtbaar, maar alleen via transform + opacity.
    | Geen filter: blur(), backdrop-filter of clip-path animaties.
    |
    */

    .motion-atmosphere {
        position: absolute;
        inset: 0;
        z-index: 0;
        overflow: hidden;
        pointer-events: none;
    }

    .motion-atmosphere::before,
    .motion-atmosphere::after {
        content: "";
        position: absolute;
        width: 72vw;
        max-width: 980px;
        aspect-ratio: 1.9 / 1;
        border-radius: 999px;
        opacity: .22;
        will-change: transform, opacity;
    }

    .motion-atmosphere::before {
        left: -24vw;
        top: 10%;
        background:
            linear-gradient(
                90deg,
                transparent 0%,
                rgba(255,111,24,.08) 18%,
                rgba(255,171,24,.38) 44%,
                rgba(255,228,103,.28) 56%,
                rgba(255,123,28,.09) 78%,
                transparent 100%
            );
        transform: rotate(24deg) translate3d(0,0,0);
        animation:
            motion-ribbon-a 11s ease-in-out infinite alternate;
    }

    .motion-atmosphere::after {
        right: -26vw;
        top: 42%;
        background:
            linear-gradient(
                90deg,
                transparent 0%,
                rgba(241,216,74,.06) 16%,
                rgba(241,216,74,.30) 42%,
                rgba(255,126,31,.24) 62%,
                transparent 100%
            );
        transform: rotate(-25deg) translate3d(0,0,0);
        animation:
            motion-ribbon-b 14s ease-in-out infinite alternate;
    }

    .motion-streak {
        position: absolute;
        left: 50%;
        bottom: 9%;
        width: min(74vw, 820px);
        height: 2px;
        border-radius: 999px;
        opacity: .26;
        background:
            linear-gradient(
                90deg,
                transparent,
                rgba(255,122,36,.3),
                rgba(241,216,74,.85),
                rgba(255,122,36,.3),
                transparent
            );
        transform:
            translate3d(-50%,0,0)
            rotate(-8deg);
        transform-origin: center;
        will-change: transform, opacity;
        animation:
            motion-streak 9s ease-in-out infinite alternate;
    }

    @keyframes motion-ribbon-a {
        from {
            transform:
                rotate(24deg)
                translate3d(-3%, -2%, 0)
                scale(.98);
            opacity: .15;
        }

        to {
            transform:
                rotate(20deg)
                translate3d(10%, 7%, 0)
                scale(1.04);
            opacity: .25;
        }
    }

    @keyframes motion-ribbon-b {
        from {
            transform:
                rotate(-25deg)
                translate3d(4%, 1%, 0)
                scale(1);
            opacity: .12;
        }

        to {
            transform:
                rotate(-20deg)
                translate3d(-10%, -7%, 0)
                scale(1.05);
            opacity: .24;
        }
    }

    @keyframes motion-streak {
        from {
            transform:
                translate3d(-54%, 0, 0)
                rotate(-8deg)
                scaleX(.92);
            opacity: .16;
        }

        to {
            transform:
                translate3d(-46%, -7px, 0)
                rotate(-5deg)
                scaleX(1.03);
            opacity: .31;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Login <-> Register micro transition
    |--------------------------------------------------------------------------
    */

    .auth-nav-transition {
        transition:
            opacity .16s ease,
            transform .16s ease;
        will-change:
            opacity,
            transform;
    }

    .auth-nav-transition.is-leaving {
        opacity: 0;
        transform:
            translate3d(0, 8px, 0)
            scale(.994);
    }

    @media (max-width: 640px) {
        .motion-atmosphere::before,
        .motion-atmosphere::after {
            width: 115vw;
            opacity: .15;
        }

        .motion-streak {
            width: 92vw;
            opacity: .18;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .motion-atmosphere::before,
        .motion-atmosphere::after,
        .motion-streak {
            animation: none !important;
        }

        .auth-nav-transition {
            transition: none !important;
        }
    }

</style>
@endpush

@section('content')
<section class="auth-page auth-nav-transition" data-auth-page-root>
    <div class="motion-atmosphere" aria-hidden="true">
        <span class="motion-streak"></span>
    </div>
    <main class="auth-shell">
        <header class="auth-intro">
            <div class="auth-eyebrow">
                Secure workspace
            </div>

            <h1 class="auth-title">
                Glassy Login
                <span>Mashal Studio</span>
            </h1>

            <p class="auth-lead">
                Kies je inlogmethode en open veilig je persoonlijke
                Mashal Studio-workspace.
            </p>
        </header>

        <div class="auth-card-wrap">
            <section class="auth-card" aria-labelledby="authHeading">
                <div class="auth-card-inner">
                    <div class="auth-icon" aria-hidden="true">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <rect x="5" y="10" width="14" height="10" rx="2"></rect>
                            <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                            <path d="M12 14v2"></path>
                        </svg>
                    </div>

                    <h2 class="auth-heading" id="authHeading">
                        Secure <strong>Access</strong>
                    </h2>

                    <p class="auth-description">
                        Wachtwoord, e-mail, passkey of je favoriete social login.
                    </p>

                    <nav class="auth-switch" aria-label="Inloggen of registreren">
                        <a
                            class="active"
                            href="{{ route('login') }}"
                            aria-current="page"
                         data-auth-page-link>
                            Inloggen
                        </a>

                        <a href="{{ route('register') }}" data-auth-page-link>
                            Registreren
                        </a>
                    </nav>

                    @if (session()->has('pending_image'))
                        <div class="auth-pending">
                            ✓ Je afbeelding staat klaar. Rond je login af om
                            verder te gaan naar je editor.
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="auth-message info" role="status">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="auth-message success" role="status">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="auth-message error" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="auth-message error" role="alert">
                            <strong>Inloggen is niet gelukt.</strong>

                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @include('partials.passkeys', ['passkeyMode' => 'login'])

                    <div
                        class="auth-tabs"
                        role="tablist"
                        aria-label="Inlogmethode kiezen"
                    >
                        <button
                            class="auth-tab active"
                            type="button"
                            role="tab"
                            aria-selected="true"
                            aria-controls="auth-panel-password"
                            data-auth-tab="password"
                        >
                            Wachtwoord
                        </button>

                        <button
                            class="auth-tab"
                            type="button"
                            role="tab"
                            aria-selected="false"
                            aria-controls="auth-panel-email"
                            data-auth-tab="passwordless"
                        >
                            E-mail
                        </button>

                        <button
                            class="auth-tab"
                            type="button"
                            role="tab"
                            aria-selected="false"
                            aria-controls="auth-panel-social"
                            data-auth-tab="oauth"
                        >
                            Social
                        </button>
                    </div>

                    <section
                        class="auth-panel active"
                        id="auth-panel-password"
                        role="tabpanel"
                        data-auth-panel="password"
                    >
                        <form
                            method="POST"
                            action="{{ route('login.submit') }}"
                            data-login-security-form
                            data-auth-transition-form
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
                                >
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
                                        class="auth-input with-toggle"
                                        id="password"
                                        type="password"
                                        name="password"
                                        placeholder="Vul je wachtwoord in"
                                        autocomplete="current-password"
                                        required
                                    >

                                    <button
                                        class="auth-password-toggle"
                                        type="button"
                                        data-toggle-password="password"
                                        aria-label="Wachtwoord tonen of verbergen"
                                        aria-pressed="false"
                                    >
                                        Tonen
                                    </button>
                                </div>
                            </div>

                            <div class="auth-options">
                                <label class="auth-remember" for="remember">
                                    <input
                                        id="remember"
                                        type="checkbox"
                                        name="remember"
                                        value="1"
                                        @checked(old('remember'))
                                    >

                                    <span>Onthoud mij</span>
                                </label>

                                <a
                                    class="auth-small-link"
                                    href="{{ route('password.request') }}"
                                >
                                    Wachtwoord vergeten?
                                </a>
                            </div>

                            <button
                                class="auth-primary"
                                type="submit"
                                data-submit-label="Inloggen bij Mashal Studio"
                            >
                                Inloggen &amp; doorgaan →
                            </button>
                        </form>
                    </section>

                    <section
                        class="auth-panel"
                        id="auth-panel-email"
                        role="tabpanel"
                        data-auth-panel="passwordless"
                        hidden
                    >
                        <div class="auth-passwordless-card">
                            <div class="auth-passwordless-head">
                                <span
                                    class="auth-passwordless-icon"
                                    aria-hidden="true"
                                >
                                    6
                                </span>

                                <div class="auth-passwordless-copy">
                                    <strong>
                                        Eenmalige e-mailcode
                                    </strong>

                                    <span>
                                        Ontvang een tijdelijke 6-cijferige code.
                                        Geen wachtwoord nodig.
                                    </span>
                                </div>
                            </div>

                            <form
                                class="auth-passwordless-form"
                                method="POST"
                                action="{{ route('email-login.send') }}"
                                data-login-security-form
                                data-auth-transition-form
                            >
                                @csrf

                                <input
                                    class="auth-input"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="naam@example.com"
                                    autocomplete="email"
                                    inputmode="email"
                                    required
                                >

                                <button
                                    class="auth-secondary"
                                    type="submit"
                                >
                                    Stuur code
                                </button>
                            </form>
                        </div>

                        <div class="auth-passwordless-card">
                            <div class="auth-passwordless-head">
                                <span
                                    class="auth-passwordless-icon"
                                    aria-hidden="true"
                                >
                                    ↗
                                </span>

                                <div class="auth-passwordless-copy">
                                    <strong>
                                        Veilige loginlink
                                    </strong>

                                    <span>
                                        Ontvang een persoonlijke magic link die
                                        één keer gebruikt kan worden.
                                    </span>
                                </div>
                            </div>

                            <form
                                class="auth-passwordless-form"
                                method="POST"
                                action="{{ route('email-login.link.send') }}"
                                data-login-security-form
                                data-auth-transition-form
                            >
                                @csrf

                                <input
                                    class="auth-input"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="naam@example.com"
                                    autocomplete="email"
                                    inputmode="email"
                                    required
                                >

                                <button
                                    class="auth-secondary"
                                    type="submit"
                                >
                                    Stuur link
                                </button>
                            </form>
                        </div>
                    </section>

                    <section
                        class="auth-panel"
                        id="auth-panel-social"
                        role="tabpanel"
                        data-auth-panel="oauth"
                        hidden
                    >
                        <div class="auth-oauth-grid">
                            @if (\Illuminate\Support\Facades\Route::has('google.redirect'))
                                <a
                                    class="auth-oauth"
                                    data-login-security-oauth
                                    data-auth-transition-link
                                    href="{{ route('google.redirect') }}"
                                    aria-label="Doorgaan met Google"
                                >
                                    <span class="auth-oauth-icon">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path fill="#4285F4" d="M21.805 10.023h-9.18v3.955h5.28c-.228 1.273-.918 2.352-1.956 3.078v2.559h3.168c1.855-1.708 2.928-4.227 2.928-7.219 0-.8-.072-1.57-.24-2.373Z"/>
                                            <path fill="#34A853" d="M12.625 22c2.65 0 4.873-.875 6.492-2.385l-3.168-2.559c-.88.59-2.003.94-3.324.94-2.55 0-4.71-1.724-5.486-4.04H3.865v2.64A9.812 9.812 0 0 0 12.625 22Z"/>
                                            <path fill="#FBBC05" d="M7.139 13.956a5.96 5.96 0 0 1 0-3.912V7.405H3.865A9.82 9.82 0 0 0 2.82 12c0 1.585.38 3.086 1.045 4.595l3.274-2.639Z"/>
                                            <path fill="#EA4335" d="M12.625 6.004c1.44 0 2.733.495 3.75 1.468l2.813-2.813C17.493 3.076 15.27 2 12.625 2a9.812 9.812 0 0 0-8.76 5.405l3.274 2.639c.776-2.316 2.936-4.04 5.486-4.04Z"/>
                                        </svg>
                                    </span>

                                    <span class="auth-oauth-copy">
                                        <strong>Google / Gmail</strong>
                                        <span>Doorgaan</span>
                                    </span>
                                </a>
                            @endif

                            @if (\Illuminate\Support\Facades\Route::has('github.redirect'))
                                <a
                                    class="auth-oauth"
                                    data-login-security-oauth
                                    data-auth-transition-link
                                    href="{{ route('github.redirect') }}"
                                    aria-label="Doorgaan met GitHub"
                                >
                                    <span class="auth-oauth-icon">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path fill="currentColor" d="M12 .7a11.5 11.5 0 0 0-3.636 22.41c.575.105.786-.25.786-.555 0-.274-.01-1-.016-1.962-3.198.695-3.874-1.541-3.874-1.541-.523-1.329-1.277-1.683-1.277-1.683-1.044-.714.08-.699.08-.699 1.154.081 1.761 1.185 1.761 1.185 1.026 1.758 2.692 1.25 3.348.956.104-.743.402-1.25.73-1.537-2.553-.29-5.237-1.276-5.237-5.68 0-1.255.449-2.281 1.184-3.085-.118-.291-.513-1.462.113-3.048 0 0 .965-.309 3.162 1.179A10.98 10.98 0 0 1 12 8.253c.977.004 1.961.132 2.88.387 2.195-1.488 3.158-1.179 3.158-1.179.628 1.586.233 2.757.115 3.048.737.804 1.182 1.83 1.182 3.085 0 4.415-2.688 5.387-5.249 5.671.413.356.78 1.057.78 2.13 0 1.538-.014 2.778-.014 3.155 0 .308.207.666.792.553A11.502 11.502 0 0 0 12 .7Z"/>
                                        </svg>
                                    </span>

                                    <span class="auth-oauth-copy">
                                        <strong>GitHub</strong>
                                        <span>Doorgaan</span>
                                    </span>
                                </a>
                            @endif

                            @if (\Illuminate\Support\Facades\Route::has('facebook.redirect'))
                                <a
                                    class="auth-oauth"
                                    data-login-security-oauth
                                    data-auth-transition-link
                                    href="{{ route('facebook.redirect') }}"
                                    aria-label="Doorgaan met Facebook"
                                >
                                    <span
                                        class="auth-oauth-icon"
                                        style="color:#1877f2"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path fill="currentColor" d="M13.6 22v-8h2.68l.4-3.12H13.6V8.89c0-.9.25-1.52 1.54-1.52h1.65V4.58a22.1 22.1 0 0 0-2.4-.12c-2.38 0-4.01 1.45-4.01 4.12v2.3H7.69V14h2.69v8h3.22Z"/>
                                        </svg>
                                    </span>

                                    <span class="auth-oauth-copy">
                                        <strong>Facebook</strong>
                                        <span>Doorgaan</span>
                                    </span>
                                </a>
                            @endif

                            @if (\Illuminate\Support\Facades\Route::has('tiktok.redirect'))
                                <a
                                    class="auth-oauth"
                                    data-login-security-oauth
                                    data-auth-transition-link
                                    href="{{ route('tiktok.redirect') }}"
                                    aria-label="Doorgaan met TikTok"
                                >
                                    <span class="auth-oauth-icon">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path fill="#25F4EE" d="M15.62 3.2c.39 2.31 1.7 3.69 3.98 3.84v2.63a7.9 7.9 0 0 1-3.94-.99v5.16c0 4.64-5.05 6.1-7.08 2.77-1.3-2.13-.5-5.87 3.67-6.03v2.77c-.38.06-.78.16-1.15.29-1.11.42-1.74 1.22-1.56 2.12.35 1.72 3.39 2.23 3.88-.26.08-.45.07-.9.07-1.36V3.2h2.13Z"/>
                                            <path fill="#FE2C55" d="M16.32 2.6c.39 2.31 1.7 3.69 3.98 3.84v2.63a7.9 7.9 0 0 1-3.94-.99v5.16c0 4.64-5.05 6.1-7.08 2.77-1.3-2.13-.5-5.87 3.67-6.03v2.77c-.38.06-.78.16-1.15.29-1.11.42-1.74 1.22-1.56 2.12.35 1.72 3.39 2.23 3.88-.26.08-.45.07-.9.07-1.36V2.6h2.13Z"/>
                                        </svg>
                                    </span>

                                    <span class="auth-oauth-copy">
                                        <strong>TikTok</strong>
                                        <span>Doorgaan</span>
                                    </span>
                                </a>
                            @endif

                            @if (\Illuminate\Support\Facades\Route::has('linkedin.redirect'))
                                <a
                                    class="auth-oauth"
                                    data-login-security-oauth
                                    data-auth-transition-link
                                    href="{{ route('linkedin.redirect') }}"
                                    aria-label="Doorgaan met LinkedIn"
                                >
                                    <span
                                        class="auth-oauth-icon"
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

                                    <span class="auth-oauth-copy">
                                        <strong>LinkedIn</strong>
                                        <span>Doorgaan</span>
                                    </span>
                                </a>
                            @endif
                        </div>
                    </section>

                    <div class="auth-divider">
                        Nieuw bij Mashal Studio?
                    </div>

                    <div class="auth-register-row">
                        <span>
                            Nog geen account? Maak gratis je eigen workspace.
                        </span>

                        <a
                            class="auth-small-link"
                            href="{{ route('register') }}"
                         data-auth-page-link>
                            Registreren
                        </a>
                    </div>

                    <div class="auth-security-note">
                        ✓ Mashal Studio vraagt je nooit om je wachtwoord via
                        e-mail, chat of telefoon te delen.
                    </div>
                </div>
            </section>

            <div class="auth-footer">
                Mashal Studio · Secure account access
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
        .querySelectorAll('[data-toggle-password]')
        .forEach(function (button) {
            button.addEventListener('click', function () {
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
            });
        });

    /*
    |--------------------------------------------------------------------------
    | Auth method tabs
    |--------------------------------------------------------------------------
    */

    const tabs =
        Array.from(
            document.querySelectorAll(
                '[data-auth-tab]'
            )
        );

    const panels =
        Array.from(
            document.querySelectorAll(
                '[data-auth-panel]'
            )
        );

    function selectAuthMethod(name) {
        tabs.forEach(function (tab) {
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

        panels.forEach(function (panel) {
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

    tabs.forEach(function (tab) {
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
    | Success overlay marker
    |--------------------------------------------------------------------------
    |
    | Alleen localStorage schrijven. Geen animatievertraging vóór navigatie.
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
                            source || 'login'
                        ),
                    createdAt:
                        Date.now(),
                })
            );
        } catch (error) {
            // Authenticatie mag nooit blokkeren.
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
                        || 'login-form'
                    );

                    const submit =
                        form.querySelector(
                            '[type="submit"]'
                        );

                    if (!submit) {
                        return;
                    }

                    submit.disabled = true;

                    if (!submit.dataset.originalText) {
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
                        || 'social-login'
                    );
                }
            );
        });

    /*
    |--------------------------------------------------------------------------
    | Login security context - achtergrond, nooit blokkerend
    |--------------------------------------------------------------------------
    |
    | De oude pagina wachtte bij submit/OAuth op geolocation + fetch.
    | Hier verzamelen we securitycontext vooraf en alleen in de achtergrond.
    | Als locatietoestemming nog "prompt" is, tonen we geen permission popup.
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
            || typeof navigator.permissions.query !== 'function'
        ) {
            return 'unknown';
        }

        try {
            const result =
                await navigator.permissions.query({
                    name: 'geolocation'
                });

            return result?.state || 'unknown';
        } catch (error) {
            return 'unknown';
        }
    }

    function grantedLocation() {
        return new Promise(function (resolve) {
            if (
                !navigator.geolocation
                || typeof navigator.geolocation.getCurrentPosition
                    !== 'function'
            ) {
                resolve(null);
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const coords =
                        position?.coords;

                    if (!coords) {
                        resolve(null);
                        return;
                    }

                    resolve({
                        latitude:
                            Number(coords.latitude),
                        longitude:
                            Number(coords.longitude),
                        location_accuracy:
                            Number.isFinite(
                                Number(coords.accuracy)
                            )
                                ? Number(coords.accuracy)
                                : null,
                    });
                },
                function () {
                    resolve(null);
                },
                {
                    enableHighAccuracy: false,
                    timeout: 1400,
                    maximumAge: 120000,
                }
            );
        });
    }

    async function storeSecurityContext() {
        try {
            const permission =
                await locationPermissionState();

            let location = null;

            if (permission === 'granted') {
                location =
                    await grantedLocation();
            }

            await fetch(
                securityContextUrl,
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    keepalive: true,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
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
            // Login blijft altijd beschikbaar.
        }
    }

    if ('requestIdleCallback' in window) {
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

                    submit.disabled = false;

                    if (
                        submit.dataset.originalText
                    ) {
                        submit.textContent =
                            submit.dataset.originalText;
                    }
                });
        }
    );


    /*
    |--------------------------------------------------------------------------
    | Login <-> Register lightweight page transition
    |--------------------------------------------------------------------------
    |
    | Korte 150ms overgang. Geen blur/fold/3D-effecten.
    |
    */

    document
        .querySelectorAll('[data-auth-page-link]')
        .forEach(function (link) {
            link.addEventListener(
                'click',
                function (event) {
                    if (
                        event.defaultPrevented ||
                        event.button !== 0 ||
                        event.metaKey ||
                        event.ctrlKey ||
                        event.shiftKey ||
                        event.altKey
                    ) {
                        return;
                    }

                    const destination =
                        link.getAttribute('href');

                    const page =
                        document.querySelector(
                            '[data-auth-page-root]'
                        );

                    if (
                        !destination ||
                        !page ||
                        window.matchMedia(
                            '(prefers-reduced-motion: reduce)'
                        ).matches
                    ) {
                        return;
                    }

                    event.preventDefault();

                    page.classList.add(
                        'is-leaving'
                    );

                    window.setTimeout(
                        function () {
                            window.location.assign(
                                destination
                            );
                        },
                        150
                    );
                }
            );
        });

});
</script>
@endpush
