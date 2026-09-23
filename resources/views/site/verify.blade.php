@extends('layouts.site-layout')

@section('title', 'Mashal | E-mail verifiëren')

@push('styles')
<style>
    :root {
        --otp-bg: #030303;
        --otp-panel: rgba(19, 18, 22, .70);
        --otp-border: rgba(255, 255, 255, .22);
        --otp-soft-border: rgba(255, 255, 255, .10);
        --otp-white: #f7f7f8;
        --otp-muted: #aaa9ad;
        --otp-yellow: #f4ee1f;
        --otp-yellow-2: #e8c614;
        --otp-orange: #ff6b23;
        --otp-green: #18ed7e;
        --otp-green-2: #04d773;
        --otp-danger: #ff8b8b;
    }

    .mashal-otp-page,
    .mashal-otp-page * {
        box-sizing: border-box;
    }

    .mashal-otp-page {
        position: relative;
        isolation: isolate;
        min-height: calc(100dvh - 78px);
        overflow: hidden;
        display: flex;
        justify-content: center;
        color: var(--otp-white);
        background:
            radial-gradient(circle at 50% 37%, rgba(255, 177, 0, .045), transparent 28rem),
            #020202;
    }

    /*
    |--------------------------------------------------------------------------
    | TikTok-reference background
    |--------------------------------------------------------------------------
    | Metallic black/gold ribbons made entirely with CSS. No external image
    | is required, so the verification screen remains self-contained.
    */
    .mashal-otp-bg,
    .mashal-otp-bg::before,
    .mashal-otp-bg::after {
        position: absolute;
        inset: 0;
        pointer-events: none;
    }

    .mashal-otp-bg {
        z-index: -5;
        overflow: hidden;
    }

    .mashal-otp-bg::before,
    .mashal-otp-bg::after {
        content: "";
    }

    .mashal-otp-bg::before {
        inset: -26%;
        background:
            linear-gradient(
                128deg,
                transparent 0 25%,
                rgba(255, 115, 0, .04) 29%,
                rgba(255, 141, 0, .42) 31%,
                rgba(255, 219, 108, .93) 32%,
                rgba(255, 122, 0, .34) 33.3%,
                transparent 36% 49%,
                rgba(255, 126, 0, .03) 52%,
                rgba(255, 141, 0, .46) 54%,
                rgba(255, 226, 126, .88) 55%,
                rgba(255, 123, 0, .31) 56.2%,
                transparent 59% 74%,
                rgba(255, 173, 0, .22) 77%,
                rgba(255, 230, 145, .72) 78%,
                transparent 81%
            );
        filter: blur(3px);
        opacity: .95;
        transform: rotate(-4deg) scale(1.12);
        animation: otpBackgroundDrift 12s ease-in-out infinite alternate;
    }

    .mashal-otp-bg::after {
        inset: -15%;
        background:
            linear-gradient(
                60deg,
                transparent 0 19%,
                rgba(165, 170, 180, .11) 20%,
                rgba(247, 247, 250, .44) 21%,
                rgba(89, 91, 96, .12) 22%,
                transparent 24% 62%,
                rgba(255, 152, 0, .13) 64%,
                rgba(255, 202, 81, .47) 65%,
                rgba(255, 123, 0, .11) 66%,
                transparent 68%
            );
        filter: blur(5px);
        opacity: .58;
        transform: rotate(3deg);
        animation: otpBackgroundDriftB 15s ease-in-out infinite alternate;
    }

    .otp-ribbon {
        position: absolute;
        z-index: -4;
        width: 1050px;
        height: 110px;
        border-radius: 50%;
        pointer-events: none;
        filter: blur(8px);
        opacity: .46;
        background:
            linear-gradient(
                180deg,
                transparent 0 28%,
                rgba(255, 128, 0, .10) 39%,
                rgba(255, 211, 85, .82) 49%,
                rgba(255, 127, 0, .36) 55%,
                transparent 72%
            );
    }

    .otp-ribbon.one {
        left: -340px;
        top: 180px;
        transform: rotate(31deg);
    }

    .otp-ribbon.two {
        right: -390px;
        top: 410px;
        transform: rotate(-28deg);
    }

    .otp-ribbon.three {
        left: -420px;
        bottom: 80px;
        transform: rotate(-25deg);
    }

    /*
    |--------------------------------------------------------------------------
    | Exact vertical composition
    |--------------------------------------------------------------------------
    */
    .mashal-otp-stage {
        width: min(100%, 512px);
        min-height: 910px;
        padding: 76px 22px 66px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .otp-poster-title {
        margin: 0 0 53px;
        text-align: center;
        color: #f8f8f8;
        font-family: Arial, Helvetica, sans-serif;
        font-size: clamp(35px, 9vw, 48px);
        font-weight: 400;
        line-height: .91;
        letter-spacing: -.055em;
        text-shadow: 0 5px 20px rgba(0,0,0,.65);
    }

    .otp-poster-title span {
        display: block;
        margin-top: 8px;
        color: var(--otp-orange);
        font-weight: 400;
    }

    /*
    |--------------------------------------------------------------------------
    | Main glass card
    |--------------------------------------------------------------------------
    */
    .otp-card-shell {
        position: relative;
        width: min(100%, 435px);
        height: 408px;
        flex: 0 0 auto;
        filter: drop-shadow(0 22px 36px rgba(0,0,0,.48));
    }

    .otp-card-border {
        position: absolute;
        inset: 0;
        padding: 1px;
        border-radius: 25px;
        clip-path: polygon(
            14% 0,
            100% 0,
            100% 83%,
            87% 100%,
            0 100%,
            0 18%
        );
        background:
            linear-gradient(
                142deg,
                rgba(255,255,255,.65),
                rgba(255,218,132,.18) 28%,
                rgba(255,255,255,.16) 59%,
                rgba(255,196,63,.68)
            );
    }

    .otp-card {
        position: relative;
        width: 100%;
        height: 100%;
        overflow: hidden;
        border-radius: 24px;
        clip-path: polygon(
            14% 0,
            100% 0,
            100% 83%,
            87% 100%,
            0 100%,
            0 18%
        );
        background:
            linear-gradient(137deg, rgba(255,255,255,.045), transparent 36%),
            linear-gradient(160deg, rgba(19,18,22,.78), rgba(8,8,10,.78));
        backdrop-filter: blur(25px) saturate(125%);
        -webkit-backdrop-filter: blur(25px) saturate(125%);
    }

    .otp-card::before {
        content: "";
        position: absolute;
        inset: -55%;
        z-index: 0;
        opacity: .28;
        pointer-events: none;
        background:
            linear-gradient(
                125deg,
                transparent 33%,
                rgba(255, 153, 0, .13) 43%,
                rgba(255, 226, 133, .45) 47%,
                rgba(255, 127, 0, .14) 51%,
                transparent 58%
            );
        animation: otpGlassSweep 8s ease-in-out infinite;
    }

    .otp-card::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 0;
        opacity: 0;
        background:
            radial-gradient(circle at 50% 48%, rgba(0,255,137,.22), transparent 37%),
            linear-gradient(145deg, rgba(0,126,68,.16), rgba(2,38,23,.33));
        transition: opacity .55s ease;
        pointer-events: none;
    }

    .mashal-otp-stage.is-success .otp-card::after {
        opacity: 1;
    }

    .otp-corner {
        position: absolute;
        z-index: 5;
        border: 1px solid rgba(255,255,255,.45);
        background: rgba(255,255,255,.07);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        box-shadow: inset 0 0 14px rgba(255,255,255,.05);
        pointer-events: none;
    }

    .otp-corner.top-left {
        width: 70px;
        height: 70px;
        top: -2px;
        left: -4px;
        border-radius: 16px;
        clip-path: polygon(0 0,100% 0,0 100%);
    }

    .otp-corner.bottom-right {
        width: 69px;
        height: 69px;
        right: -3px;
        bottom: -4px;
        border-radius: 16px;
        clip-path: polygon(100% 0,100% 100%,0 100%);
    }

    /*
    |--------------------------------------------------------------------------
    | Card states
    |--------------------------------------------------------------------------
    */
    .otp-state {
        position: absolute;
        inset: 0;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 31px 30px 28px;
        opacity: 1;
        transform: scale(1);
        transition:
            opacity .24s ease,
            transform .33s cubic-bezier(.2,.75,.2,1);
    }

    .otp-state[hidden] {
        display: none !important;
    }

    .otp-key-icon,
    .otp-shield-icon {
        width: 46px;
        height: 46px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        border-radius: 13px;
        margin-bottom: 13px;
    }

    .otp-key-icon {
        border: 1px solid rgba(236, 224, 28, .35);
        color: var(--otp-yellow);
        background: rgba(240,232,31,.035);
        box-shadow:
            0 0 18px rgba(240,229,33,.08),
            inset 0 0 12px rgba(240,229,33,.025);
    }

    .otp-shield-icon {
        border: 1px solid rgba(24,237,126,.34);
        color: var(--otp-green);
        background: rgba(24,237,126,.05);
        box-shadow: 0 0 20px rgba(24,237,126,.15);
    }

    .otp-key-icon svg,
    .otp-shield-icon svg {
        width: 23px;
        height: 23px;
    }

    .otp-heading {
        margin: 0;
        font-size: 23px;
        line-height: 1;
        font-weight: 850;
        letter-spacing: -.035em;
    }

    .otp-heading strong {
        color: var(--otp-yellow);
        font-weight: 900;
    }

    .otp-success .otp-heading strong {
        color: var(--otp-green);
    }

    .otp-description {
        margin: 9px 0 0;
        max-width: 340px;
        color: #9a999d;
        font-size: 10.5px;
        line-height: 1.5;
    }

    /*
    |--------------------------------------------------------------------------
    | E-mail strip - necessary for your existing backend
    |--------------------------------------------------------------------------
    | The original TikTok is only the OTP step. Your Laravel flow requires
    | an email address too, so this is deliberately kept compact and inside
    | the same glass card instead of making a separate second layout.
    */
    .otp-email-row {
        width: min(100%, 345px);
        margin-top: 16px;
        display: grid;
        grid-template-columns: 1fr 78px;
        gap: 7px;
    }

    .otp-email-input {
        min-width: 0;
        height: 38px;
        padding: 0 11px;
        border: 1px solid rgba(255,255,255,.13);
        border-radius: 10px;
        outline: 0;
        color: #f6f6f7;
        background: rgba(0,0,0,.23);
        font: inherit;
        font-size: 10px;
        transition:
            border-color .18s ease,
            box-shadow .18s ease;
    }

    .otp-email-input:focus {
        border-color: rgba(244,238,31,.57);
        box-shadow: 0 0 0 3px rgba(244,238,31,.06);
    }

    .otp-send-button {
        height: 38px;
        padding: 0 8px;
        border: 1px solid rgba(244,238,31,.25);
        border-radius: 10px;
        color: var(--otp-yellow);
        background: rgba(244,238,31,.055);
        font: inherit;
        font-size: 9px;
        font-weight: 850;
        cursor: pointer;
    }

    .otp-send-button:disabled {
        opacity: .55;
        cursor: wait;
    }

    /*
    |--------------------------------------------------------------------------
    | OTP boxes
    |--------------------------------------------------------------------------
    */
    .otp-boxes {
        width: min(100%, 360px);
        margin-top: 18px;
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 7px;
    }

    .otp-box {
        width: 100%;
        aspect-ratio: 1 / 1;
        min-width: 0;
        border: 1px solid rgba(236, 213, 23, .42);
        border-radius: 11px;
        outline: 0;
        color: #fff;
        background: rgba(7,7,9,.63);
        text-align: center;
        font: inherit;
        font-size: 20px;
        font-weight: 850;
        caret-color: var(--otp-yellow);
        box-shadow: inset 0 0 10px rgba(255,255,255,.018);
        transition:
            transform .18s ease,
            border-color .18s ease,
            box-shadow .18s ease,
            background .18s ease;
    }

    .otp-box:focus,
    .otp-box.is-active {
        border-color: #fff32e;
        background: rgba(244,238,31,.045);
        box-shadow:
            0 0 0 2px rgba(244,238,31,.11),
            0 0 18px rgba(244,221,0,.35);
        transform: translateY(-1px);
    }

    .otp-resend {
        margin-top: 20px;
        color: #969599;
        font-size: 9.5px;
        line-height: 1.4;
    }

    .otp-resend button {
        padding: 0;
        border: 0;
        color: var(--otp-yellow);
        background: transparent;
        font: inherit;
        font-weight: 850;
        cursor: pointer;
    }

    .otp-resend button:disabled {
        opacity: .58;
        cursor: default;
    }

    .otp-main-button {
        width: 210px;
        height: 37px;
        margin-top: 17px;
        border: 0;
        border-radius: 8px;
        color: #171200;
        background:
            linear-gradient(180deg, #fbef39, #dbb90a);
        box-shadow:
            0 9px 24px rgba(242,197,0,.18),
            inset 0 1px 0 rgba(255,255,255,.59);
        font: inherit;
        font-size: 10px;
        font-weight: 900;
        cursor: pointer;
        transition:
            transform .18s ease,
            filter .18s ease;
    }

    .otp-main-button:hover {
        filter: brightness(1.06);
        transform: translateY(-1px);
    }

    .otp-main-button:disabled {
        cursor: wait;
        opacity: .74;
    }

    /*
    |--------------------------------------------------------------------------
    | Error/status messages
    |--------------------------------------------------------------------------
    */
    .otp-message {
        width: min(100%, 345px);
        margin-top: 10px;
        padding: 8px 10px;
        border-radius: 9px;
        text-align: left;
        font-size: 9px;
        line-height: 1.45;
    }

    .otp-message.error {
        border: 1px solid rgba(255,112,112,.21);
        color: #ffc0c0;
        background: rgba(255,66,66,.065);
    }

    .otp-message.success {
        border: 1px solid rgba(39,244,143,.19);
        color: #a3ffd0;
        background: rgba(0,220,118,.055);
    }

    .otp-message ul {
        margin: 5px 0 0 15px;
        padding: 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Verifying state - floating/rotating digits from the video
    |--------------------------------------------------------------------------
    */
    .otp-verifying {
        justify-content: flex-start;
    }

    .otp-verifying .otp-key-icon {
        margin-top: 0;
        animation: otpKeyPulse 1.1s ease-in-out infinite;
    }

    .otp-floating-zone {
        position: relative;
        width: 210px;
        height: 183px;
        margin-top: 12px;
    }

    .otp-float {
        position: absolute;
        display: grid;
        place-items: center;
        width: 50px;
        height: 50px;
        border: 1px solid rgba(238, 203, 20, .46);
        border-radius: 11px;
        color: #fff;
        background: rgba(7,7,9,.70);
        font-size: 19px;
        font-weight: 900;
        box-shadow:
            0 0 15px rgba(255,151,0,.06),
            inset 0 0 10px rgba(255,255,255,.02);
    }

    .otp-float:nth-child(1) {
        left: 80px;
        top: 0;
        animation: otpFloatTop 1.1s ease-in-out infinite alternate;
    }

    .otp-float:nth-child(2) {
        left: 18px;
        top: 52px;
        animation: otpFloatLeft 1.18s ease-in-out infinite alternate;
    }

    .otp-float:nth-child(3) {
        right: 18px;
        top: 52px;
        animation: otpFloatRight 1.02s ease-in-out infinite alternate;
    }

    .otp-float:nth-child(4) {
        left: 80px;
        bottom: 0;
        animation: otpFloatBottom 1.16s ease-in-out infinite alternate;
    }

    .otp-float:nth-child(5),
    .otp-float:nth-child(6) {
        width: 40px;
        height: 40px;
        font-size: 15px;
        opacity: .8;
    }

    .otp-float:nth-child(5) {
        left: 34px;
        top: 3px;
        animation: otpFloatMiniA 1.23s ease-in-out infinite alternate;
    }

    .otp-float:nth-child(6) {
        right: 31px;
        bottom: 4px;
        animation: otpFloatMiniB 1.07s ease-in-out infinite alternate;
    }

    .otp-verifying .otp-resend {
        margin-top: 5px;
    }

    .otp-verifying .otp-main-button {
        margin-top: 18px;
    }

    .otp-spinner {
        display: inline-block;
        width: 14px;
        height: 14px;
        margin-right: 7px;
        vertical-align: -3px;
        border: 1.8px solid rgba(0,0,0,.28);
        border-top-color: #191500;
        border-radius: 50%;
        animation: otpSpin .7s linear infinite;
    }

    /*
    |--------------------------------------------------------------------------
    | Success state
    |--------------------------------------------------------------------------
    */
    .otp-success {
        justify-content: flex-start;
    }

    .otp-success .otp-shield-icon {
        margin-top: 0;
    }

    .otp-check {
        width: 58px;
        height: 58px;
        margin-top: 55px;
        display: grid;
        place-items: center;
        border: 3px solid var(--otp-green);
        border-radius: 13px;
        color: #fff;
        background: rgba(7,10,9,.79);
        box-shadow:
            0 0 0 9px rgba(24,237,126,.025),
            0 0 30px rgba(24,237,126,.25);
        animation: otpSuccessPop .55s cubic-bezier(.2,.88,.24,1.3) both;
    }

    .otp-check svg {
        width: 31px;
        height: 31px;
    }

    .otp-success .otp-resend {
        margin-top: 24px;
    }

    .otp-success .otp-main-button {
        width: 210px;
        color: #fff;
        background:
            linear-gradient(180deg, #1cf79a, #06d877);
        box-shadow:
            0 9px 27px rgba(0,231,125,.22),
            inset 0 1px 0 rgba(255,255,255,.50);
        cursor: default;
    }

    /*
    |--------------------------------------------------------------------------
    | Decorative lower code panel from the reference video
    |--------------------------------------------------------------------------
    | It is purely decorative and does not expose real application code.
    */
    .otp-code-panel {
        width: min(100%, 433px);
        height: 131px;
        margin-top: 66px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.16);
        border-radius: 8px;
        background: rgba(4,4,5,.87);
        box-shadow: 0 18px 36px rgba(0,0,0,.46);
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    }

    .otp-code-topbar {
        height: 29px;
        display: flex;
        align-items: center;
        padding: 0 9px;
        border-bottom: 1px solid rgba(255,255,255,.07);
    }

    .otp-code-dots {
        display: flex;
        gap: 4px;
    }

    .otp-code-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
    }

    .otp-code-dot.red { background: #ff5e64; }
    .otp-code-dot.yellow { background: #ffca3a; }
    .otp-code-dot.green { background: #38d568; }

    .otp-code-tab {
        margin-left: 12px;
        color: #e1e1e4;
        font-size: 7px;
    }

    .otp-code-react {
        margin-left: auto;
        color: #2bd9f7;
        font-size: 7px;
    }

    .otp-code-body {
        padding: 8px 10px 10px;
        color: #83848a;
        font-size: 6px;
        line-height: 1.55;
        white-space: pre;
        overflow: hidden;
    }

    .otp-code-body .pink { color: #ff56ba; }
    .otp-code-body .cyan { color: #34d5f4; }
    .otp-code-body .green { color: #73e282; }
    .otp-code-body .yellow { color: #f4d65b; }

    .otp-footer {
        margin-top: 16px;
        color: #63646a;
        text-align: center;
        font-size: 9px;
        line-height: 1.5;
    }

    .otp-footer a {
        color: #9b9ca1;
        text-decoration: none;
    }

    /*
    |--------------------------------------------------------------------------
    | Animations
    |--------------------------------------------------------------------------
    */
    @keyframes otpBackgroundDrift {
        from { transform: rotate(-4deg) scale(1.10) translate3d(-1%, -1%, 0); }
        to   { transform: rotate(-1deg) scale(1.16) translate3d(2%, 1%, 0); }
    }

    @keyframes otpBackgroundDriftB {
        from { transform: rotate(3deg) scale(1.02); }
        to   { transform: rotate(6deg) scale(1.08); }
    }

    @keyframes otpGlassSweep {
        0%,100% { transform: translateX(-38%) rotate(-2deg); }
        50%     { transform: translateX(32%) rotate(1deg); }
    }

    @keyframes otpKeyPulse {
        0%,100% {
            transform: scale(1);
            box-shadow: 0 0 15px rgba(244,238,31,.07);
        }
        50% {
            transform: scale(1.05);
            box-shadow: 0 0 28px rgba(244,238,31,.22);
        }
    }

    @keyframes otpSpin {
        to { transform: rotate(360deg); }
    }

    @keyframes otpFloatTop {
        from { transform: translate(-2px, 2px) rotate(-4deg); }
        to   { transform: translate(5px,-5px) rotate(8deg); }
    }

    @keyframes otpFloatLeft {
        from { transform: translate(2px,-3px) rotate(-17deg); }
        to   { transform: translate(-7px,5px) rotate(-27deg); }
    }

    @keyframes otpFloatRight {
        from { transform: translate(-2px,4px) rotate(15deg); }
        to   { transform: translate(8px,-4px) rotate(25deg); }
    }

    @keyframes otpFloatBottom {
        from { transform: translate(4px,-2px) rotate(-8deg); }
        to   { transform: translate(-4px,6px) rotate(7deg); }
    }

    @keyframes otpFloatMiniA {
        from { transform: translate(0,0) rotate(8deg); }
        to   { transform: translate(-7px,-4px) rotate(-8deg); }
    }

    @keyframes otpFloatMiniB {
        from { transform: translate(0,0) rotate(-5deg); }
        to   { transform: translate(7px,4px) rotate(10deg); }
    }

    @keyframes otpSuccessPop {
        0% {
            opacity: 0;
            transform: scale(.55) rotate(-10deg);
        }
        100% {
            opacity: 1;
            transform: scale(1) rotate(0);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */
    @media (max-width: 540px) {
        .mashal-otp-stage {
            width: 100%;
            min-height: 100dvh;
            padding-top: 58px;
        }

        .otp-poster-title {
            margin-bottom: 48px;
            font-size: clamp(34px, 10vw, 45px);
        }

        .otp-card-shell {
            width: min(100%, 435px);
            height: 408px;
        }

        .otp-state {
            padding-inline: 20px;
        }

        .otp-boxes {
            width: min(100%, 338px);
            gap: 6px;
        }

        .otp-email-row {
            width: min(100%, 338px);
        }

        .otp-code-panel {
            margin-top: 54px;
        }
    }

    @media (max-width: 390px) {
        .mashal-otp-stage {
            padding-inline: 12px;
        }

        .otp-card-shell {
            height: 422px;
        }

        .otp-state {
            padding-inline: 14px;
        }

        .otp-boxes {
            gap: 5px;
        }

        .otp-main-button {
            width: 205px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .mashal-otp-page *,
        .mashal-otp-page *::before,
        .mashal-otp-page *::after {
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .01ms !important;
            scroll-behavior: auto !important;
        }
    }
</style>
@endpush

@section('content')
@php
    $verifyEmail = old(
        'email',
        auth()->check()
            ? auth()->user()->email
            : request('email', '')
    );
@endphp

<section class="mashal-otp-page">
    <div class="mashal-otp-bg" aria-hidden="true"></div>
    <span class="otp-ribbon one" aria-hidden="true"></span>
    <span class="otp-ribbon two" aria-hidden="true"></span>
    <span class="otp-ribbon three" aria-hidden="true"></span>

    <main
        class="mashal-otp-stage"
        id="mashal-otp-stage"
        data-send-url="{{ route('verification.send') }}"
        data-verify-url="{{ route('verification.verify') }}"
        data-login-url="{{ route('login') }}"
    >
        <h1 class="otp-poster-title">
            Mashal OTP
            <span>Verification</span>
        </h1>

        <div class="otp-card-shell">
            <div class="otp-card-border">
                <div class="otp-card">
                    {{-- ENTRY --}}
                    <section
                        class="otp-state otp-entry"
                        id="otp-entry"
                        aria-live="polite"
                    >
                        <div class="otp-key-icon" aria-hidden="true">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <circle cx="7.5" cy="16.5" r="3.2"></circle>
                                <path d="M10 14l7.1-7.1a2.6 2.6 0 1 1 3.7 3.7L13.7 17.7"></path>
                                <path d="M16.9 7.2l2.4 2.4"></path>
                                <path d="M5.3 18.7L3 21"></path>
                            </svg>
                        </div>

                        <h2 class="otp-heading">
                            Verify <strong>OTP</strong>
                        </h2>

                        <p class="otp-description">
                            Vul de 6-cijferige beveiligingscode in die naar je e-mailadres is gestuurd.
                        </p>

                        <form
                            id="otp-send-form"
                            class="otp-email-row"
                            method="POST"
                            action="{{ route('verification.send') }}"
                            novalidate
                        >
                            @csrf

                            <input
                                class="otp-email-input"
                                id="otp-email"
                                type="email"
                                name="email"
                                value="{{ $verifyEmail }}"
                                autocomplete="email"
                                inputmode="email"
                                spellcheck="false"
                                maxlength="255"
                                placeholder="naam@example.com"
                                aria-label="E-mailadres"
                                required
                            >

                            <button
                                class="otp-send-button"
                                id="otp-send-button"
                                type="submit"
                            >
                                Stuur code
                            </button>
                        </form>

                        <form
                            id="otp-verify-form"
                            method="POST"
                            action="{{ route('verification.verify') }}"
                            novalidate
                        >
                            @csrf

                            <input
                                id="otp-email-hidden"
                                type="hidden"
                                name="email"
                                value="{{ $verifyEmail }}"
                            >

                            <input
                                id="otp-code-hidden"
                                type="hidden"
                                name="code"
                                value="{{ old('code') }}"
                            >

                            <div
                                class="otp-boxes"
                                id="otp-boxes"
                                role="group"
                                aria-label="6-cijferige verificatiecode"
                            >
                                @for ($i = 0; $i < 6; $i++)
                                    <input
                                        class="otp-box"
                                        type="text"
                                        inputmode="numeric"
                                        pattern="[0-9]*"
                                        maxlength="1"
                                        autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                                        aria-label="Cijfer {{ $i + 1 }}"
                                        data-otp-index="{{ $i }}"
                                    >
                                @endfor
                            </div>

                            <div class="otp-resend">
                                Geen code ontvangen?
                                <button
                                    id="otp-resend-button"
                                    type="button"
                                >
                                    Opnieuw sturen
                                </button>
                                <span id="otp-resend-timer"></span>
                            </div>

                            <button
                                class="otp-main-button"
                                id="otp-verify-button"
                                type="submit"
                            >
                                Verifieer &amp; ga verder&nbsp;&nbsp;→
                            </button>
                        </form>

                        @if (session('success'))
                            <div class="otp-message success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="otp-message error">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="otp-message error">
                                <strong>Verificatie is niet gelukt.</strong>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div
                            class="otp-message error"
                            id="otp-client-error"
                            hidden
                        ></div>
                    </section>

                    {{-- VERIFYING --}}
                    <section
                        class="otp-state otp-verifying"
                        id="otp-verifying"
                        hidden
                        aria-live="polite"
                    >
                        <div class="otp-key-icon" aria-hidden="true">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <circle cx="7.5" cy="16.5" r="3.2"></circle>
                                <path d="M10 14l7.1-7.1a2.6 2.6 0 1 1 3.7 3.7L13.7 17.7"></path>
                                <path d="M16.9 7.2l2.4 2.4"></path>
                            </svg>
                        </div>

                        <h2 class="otp-heading">
                            Verify <strong>OTP</strong>
                        </h2>

                        <p class="otp-description">
                            Je beveiligingscode wordt gecontroleerd.
                        </p>

                        <div
                            class="otp-floating-zone"
                            aria-hidden="true"
                        >
                            <span class="otp-float">•</span>
                            <span class="otp-float">•</span>
                            <span class="otp-float">•</span>
                            <span class="otp-float">•</span>
                            <span class="otp-float">•</span>
                            <span class="otp-float">•</span>
                        </div>

                        <div class="otp-resend">
                            Geen code ontvangen?
                            <span style="color:var(--otp-yellow)">
                                Verificatie bezig
                            </span>
                        </div>

                        <button
                            class="otp-main-button"
                            type="button"
                            disabled
                        >
                            <span class="otp-spinner" aria-hidden="true"></span>
                            Code controleren...
                        </button>
                    </section>

                    {{-- SUCCESS --}}
                    <section
                        class="otp-state otp-success"
                        id="otp-success"
                        hidden
                        aria-live="polite"
                    >
                        <div class="otp-shield-icon" aria-hidden="true">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M12 3l7 3v5c0 4.5-2.7 7.9-7 10-4.3-2.1-7-5.5-7-10V6l7-3z"></path>
                                <path d="M9.4 12.1l1.8 1.8 3.6-3.8"></path>
                            </svg>
                        </div>

                        <h2 class="otp-heading">
                            Verified <strong>Successfully</strong>
                        </h2>

                        <p class="otp-description">
                            Je beveiligingscode is bevestigd.
                        </p>

                        <div class="otp-check" aria-hidden="true">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M5 12.5l4.2 4.2L19.5 6.8"></path>
                            </svg>
                        </div>

                        <div class="otp-resend">
                            Je e-mailadres is nu veilig bevestigd.
                        </div>

                        <button
                            class="otp-main-button"
                            type="button"
                            disabled
                        >
                            ✓&nbsp;&nbsp;Verified &amp; Secured
                        </button>
                    </section>
                </div>
            </div>

            <span class="otp-corner top-left" aria-hidden="true"></span>
            <span class="otp-corner bottom-right" aria-hidden="true"></span>
        </div>

        {{-- Decorative panel from the reference video --}}
        <div class="otp-code-panel" aria-hidden="true">
            <div class="otp-code-topbar">
                <div class="otp-code-dots">
                    <span class="otp-code-dot red"></span>
                    <span class="otp-code-dot yellow"></span>
                    <span class="otp-code-dot green"></span>
                </div>
                <div class="otp-code-tab">◻&nbsp; Verification.jsx</div>
                <div class="otp-code-react">⚛&nbsp; Secure UI</div>
            </div>
            <div class="otp-code-body"><span class="pink">const</span> <span class="cyan">verification</span> = {
  state: <span class="green">'secure'</span>,
  digits: <span class="yellow">6</span>,
  protected: <span class="cyan">true</span>,
  provider: <span class="green">'Mashal'</span>
};</div>
        </div>

        <div class="otp-footer">
            Mashal · beveiligde e-mailverificatie
            · <a href="{{ route('login') }}">Terug naar inloggen</a>

            @auth
                @if (\Illuminate\Support\Facades\Route::has('account'))
                    · <a href="{{ route('account') }}">Mijn account</a>
                @endif
            @endauth
        </div>
    </main>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const stage =
        document.getElementById('mashal-otp-stage');

    const entryState =
        document.getElementById('otp-entry');

    const verifyingState =
        document.getElementById('otp-verifying');

    const successState =
        document.getElementById('otp-success');

    const sendForm =
        document.getElementById('otp-send-form');

    const verifyForm =
        document.getElementById('otp-verify-form');

    const emailInput =
        document.getElementById('otp-email');

    const hiddenEmail =
        document.getElementById('otp-email-hidden');

    const hiddenCode =
        document.getElementById('otp-code-hidden');

    const sendButton =
        document.getElementById('otp-send-button');

    const verifyButton =
        document.getElementById('otp-verify-button');

    const resendButton =
        document.getElementById('otp-resend-button');

    const resendTimer =
        document.getElementById('otp-resend-timer');

    const clientError =
        document.getElementById('otp-client-error');

    const otpInputs =
        Array.from(
            document.querySelectorAll('.otp-box')
        );

    const floatingDigits =
        Array.from(
            document.querySelectorAll('.otp-float')
        );

    const csrfToken =
        document.querySelector('meta[name="csrf-token"]')?.content
        || verifyForm?.querySelector('input[name="_token"]')?.value
        || '';

    let countdownInterval = null;
    let isVerifying = false;

    function digitsOnly(value) {
        return String(value || '')
            .replace(/\D/g, '')
            .slice(0, 6);
    }

    function currentCode() {
        return otpInputs
            .map(function (input) {
                return input.value;
            })
            .join('');
    }

    function syncEmail() {
        if (
            hiddenEmail
            && emailInput
        ) {
            hiddenEmail.value =
                emailInput.value.trim();
        }
    }

    function syncCode() {
        if (hiddenCode) {
            hiddenCode.value =
                currentCode();
        }

        otpInputs.forEach(
            function (input) {
                input.classList.toggle(
                    'is-active',
                    document.activeElement
                        === input
                );
            }
        );
    }

    function clearClientError() {
        if (!clientError) {
            return;
        }

        clientError.hidden = true;
        clientError.textContent = '';
    }

    function showClientError(message) {
        if (!clientError) {
            return;
        }

        clientError.textContent =
            String(
                message
                || 'Er ging iets mis.'
            );

        clientError.hidden = false;
    }

    function showState(name) {
        entryState.hidden =
            name !== 'entry';

        verifyingState.hidden =
            name !== 'verifying';

        successState.hidden =
            name !== 'success';

        stage.classList.toggle(
            'is-success',
            name === 'success'
        );
    }

    function putCodeIntoFloatingTiles(code) {
        const digits =
            digitsOnly(code)
                .padEnd(6, '•')
                .split('');

        floatingDigits.forEach(
            function (tile, index) {
                tile.textContent =
                    digits[index] || '•';
            }
        );
    }

    function startCountdown(seconds) {
        window.clearInterval(
            countdownInterval
        );

        let remaining =
            Math.max(
                0,
                Number(seconds) || 0
            );

        function render() {
            if (remaining <= 0) {
                resendButton.disabled = false;
                resendButton.textContent =
                    'Opnieuw sturen';

                resendTimer.textContent = '';

                window.clearInterval(
                    countdownInterval
                );

                return;
            }

            resendButton.disabled = true;

            resendTimer.textContent =
                ' in 00:'
                + String(remaining)
                    .padStart(2, '0');

            remaining -= 1;
        }

        render();

        countdownInterval =
            window.setInterval(
                render,
                1000
            );
    }

    function hydrateOldCode() {
        const oldCode =
            digitsOnly(
                hiddenCode?.value
                || ''
            );

        oldCode
            .split('')
            .forEach(
                function (digit, index) {
                    if (otpInputs[index]) {
                        otpInputs[
                            index
                        ].value = digit;
                    }
                }
            );

        syncCode();
        syncEmail();
    }

    async function sendVerificationCode() {
        clearClientError();
        syncEmail();

        const email =
            emailInput?.value.trim()
            || '';

        if (
            !email
            || !emailInput.checkValidity()
        ) {
            showClientError(
                'Vul eerst een geldig e-mailadres in.'
            );

            emailInput?.focus();

            return false;
        }

        sendButton.disabled = true;
        sendButton.textContent =
            'Bezig...';

        try {
            const body =
                new FormData();

            body.append(
                '_token',
                csrfToken
            );

            body.append(
                'email',
                email
            );

            const response =
                await fetch(
                    stage.dataset.sendUrl,
                    {
                        method: 'POST',
                        headers: {
                            'Accept':
                                'application/json',
                            'X-Requested-With':
                                'XMLHttpRequest',
                        },
                        body,
                        credentials:
                            'same-origin',
                        redirect:
                            'follow',
                    }
                );

            if (response.status === 422) {
                const data =
                    await response
                        .json()
                        .catch(
                            function () {
                                return {};
                            }
                        );

                throw new Error(
                    data?.errors?.email?.[0]
                    || data?.message
                    || 'De verificatiecode kon niet worden verstuurd.'
                );
            }

            if (response.status === 429) {
                throw new Error(
                    'Je hebt te vaak een code aangevraagd. Wacht even en probeer opnieuw.'
                );
            }

            if (!response.ok) {
                throw new Error(
                    'De verificatiecode kon niet worden verstuurd.'
                );
            }

            /*
             * TikTok-reference starts around 00:38 after receiving
             * the code, so the visible timer deliberately starts there.
             */
            startCountdown(38);

            otpInputs[0]?.focus();

            return true;
        } catch (error) {
            showClientError(
                error instanceof Error
                    ? error.message
                    : 'Er ging iets mis bij het versturen.'
            );

            return false;
        } finally {
            sendButton.disabled = false;
            sendButton.textContent =
                'Stuur code';
        }
    }

    otpInputs.forEach(
        function (input, index) {
            input.addEventListener(
                'focus',
                syncCode
            );

            input.addEventListener(
                'blur',
                syncCode
            );

            input.addEventListener(
                'input',
                function () {
                    const raw =
                        String(
                            input.value || ''
                        );

                    const clean =
                        digitsOnly(raw);

                    /*
                     * Browser/mobile OTP autofill may put all six
                     * digits into the first visible field.
                     */
                    if (clean.length > 1) {
                        clean
                            .split('')
                            .forEach(
                                function (
                                    digit,
                                    offset
                                ) {
                                    const target =
                                        otpInputs[
                                            index
                                            + offset
                                        ];

                                    if (target) {
                                        target.value =
                                            digit;
                                    }
                                }
                            );
                    } else {
                        input.value =
                            clean;
                    }

                    syncCode();
                    clearClientError();

                    if (
                        input.value !== ''
                        && otpInputs[index + 1]
                    ) {
                        otpInputs[
                            index + 1
                        ].focus();
                    }

                    if (
                        currentCode().length
                        === 6
                    ) {
                        verifyButton.focus();
                    }
                }
            );

            input.addEventListener(
                'keydown',
                function (event) {
                    if (
                        event.key === 'Backspace'
                        && input.value === ''
                        && otpInputs[index - 1]
                    ) {
                        const previous =
                            otpInputs[index - 1];

                        previous.value = '';
                        previous.focus();
                        syncCode();
                    }

                    if (
                        event.key === 'ArrowLeft'
                        && otpInputs[index - 1]
                    ) {
                        event.preventDefault();

                        otpInputs[
                            index - 1
                        ].focus();
                    }

                    if (
                        event.key === 'ArrowRight'
                        && otpInputs[index + 1]
                    ) {
                        event.preventDefault();

                        otpInputs[
                            index + 1
                        ].focus();
                    }
                }
            );

            input.addEventListener(
                'paste',
                function (event) {
                    const pasted =
                        digitsOnly(
                            event.clipboardData
                                ?.getData('text')
                            || ''
                        );

                    if (!pasted) {
                        return;
                    }

                    event.preventDefault();

                    pasted
                        .split('')
                        .forEach(
                            function (
                                digit,
                                offset
                            ) {
                                const target =
                                    otpInputs[
                                        index + offset
                                    ];

                                if (target) {
                                    target.value =
                                        digit;
                                }
                            }
                        );

                    syncCode();

                    const nextIndex =
                        Math.min(
                            index
                            + pasted.length,
                            otpInputs.length - 1
                        );

                    otpInputs[
                        nextIndex
                    ]?.focus();
                }
            );
        }
    );

    emailInput?.addEventListener(
        'input',
        syncEmail
    );

    sendForm?.addEventListener(
        'submit',
        async function (event) {
            event.preventDefault();

            await sendVerificationCode();
        }
    );

    resendButton?.addEventListener(
        'click',
        async function () {
            await sendVerificationCode();
        }
    );

    verifyForm?.addEventListener(
        'submit',
        async function (event) {
            event.preventDefault();

            if (isVerifying) {
                return;
            }

            clearClientError();
            syncEmail();
            syncCode();

            const email =
                hiddenEmail.value.trim();

            const code =
                currentCode();

            if (
                !email
                || !emailInput.checkValidity()
            ) {
                showClientError(
                    'Vul eerst een geldig e-mailadres in.'
                );

                emailInput.focus();

                return;
            }

            if (code.length !== 6) {
                showClientError(
                    'Vul de volledige 6-cijferige verificatiecode in.'
                );

                const empty =
                    otpInputs.find(
                        function (input) {
                            return (
                                input.value === ''
                            );
                        }
                    );

                empty?.focus();

                return;
            }

            isVerifying = true;
            verifyButton.disabled = true;

            putCodeIntoFloatingTiles(
                code
            );

            showState('verifying');

            /*
             * Keep the floating-number animation visible long enough
             * to reproduce the reference interaction.
             */
            await new Promise(
                function (resolve) {
                    window.setTimeout(
                        resolve,
                        780
                    );
                }
            );

            const body =
                new FormData(
                    verifyForm
                );

            try {
                const response =
                    await fetch(
                        stage.dataset.verifyUrl,
                        {
                            method: 'POST',
                            headers: {
                                'Accept':
                                    'application/json',
                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },
                            body,
                            credentials:
                                'same-origin',
                            redirect:
                                'follow',
                        }
                    );

                if (response.status === 422) {
                    const data =
                        await response
                            .json()
                            .catch(
                                function () {
                                    return {};
                                }
                            );

                    throw new Error(
                        data?.errors?.code?.[0]
                        || data?.errors?.email?.[0]
                        || data?.message
                        || 'De code kon niet worden geverifieerd.'
                    );
                }

                if (response.status === 429) {
                    throw new Error(
                        'Te veel verificatiepogingen. Wacht even en probeer opnieuw.'
                    );
                }

                if (!response.ok) {
                    throw new Error(
                        'De verificatie kon niet worden afgerond.'
                    );
                }

                const finalUrl =
                    new URL(
                        response.url
                        || stage.dataset.loginUrl,
                        window.location.origin
                    );

                const currentPath =
                    window.location.pathname
                        .replace(/\/+$/, '');

                const finalPath =
                    finalUrl.pathname
                        .replace(/\/+$/, '');

                /*
                 * The existing controller redirects invalid/expired codes
                 * back to /verify with flash errors. fetch follows that
                 * redirect, so ending on this same path means verification
                 * did not succeed.
                 */
                if (
                    finalPath
                    === currentPath
                ) {
                    throw new Error(
                        'De code is ongeldig of verlopen. Controleer de code of vraag een nieuwe aan.'
                    );
                }

                showState('success');

                /*
                 * Match the reference success frame before continuing.
                 */
                await new Promise(
                    function (resolve) {
                        window.setTimeout(
                            resolve,
                            1500
                        );
                    }
                );

                window.location.assign(
                    finalUrl.href
                );
            } catch (error) {
                showState('entry');

                showClientError(
                    error instanceof Error
                        ? error.message
                        : 'Er ging iets mis tijdens de verificatie.'
                );

                otpInputs.forEach(
                    function (input) {
                        input.value = '';
                    }
                );

                syncCode();

                otpInputs[0]?.focus();
            } finally {
                isVerifying = false;
                verifyButton.disabled = false;
            }
        }
    );

    hydrateOldCode();

    @if (session('success'))
        startCountdown(38);
    @endif
});
</script>
@endpush
