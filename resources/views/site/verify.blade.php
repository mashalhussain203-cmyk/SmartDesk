@extends('layouts.site-layout')

@section('title', 'E-mail verifiëren | Mashal Studio')

@section(
    'meta_description',
    'Verifieer veilig je e-mailadres met de 6-cijferige code van Mashal Studio.'
)

@push('styles')
<style>
    :root {
        --verify-bg: #050506;
        --verify-panel: #0e0e11;
        --verify-panel-2: #141418;
        --verify-text: #f7f7f8;
        --verify-muted: #96969e;
        --verify-muted-2: #686870;
        --verify-line: rgba(255,255,255,.09);
        --verify-line-strong: rgba(255,255,255,.15);
        --verify-gold: #f1d84a;
        --verify-gold-2: #d8b91e;
        --verify-orange: #ff7a24;
        --verify-green: #45df8b;
        --verify-danger: #ff8f8f;
        --verify-radius: 24px;
        --verify-shadow: 0 24px 70px rgba(0,0,0,.36);
    }

    .verify-page,
    .verify-page * {
        box-sizing: border-box;
    }

    .verify-page {
        position: relative;
        width: 100%;
        min-height: calc(100dvh - var(--studio-header-height, 78px));
        overflow-x: clip;
        color: var(--verify-text);
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

    .verify-page::before {
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

    .verify-shell {
        position: relative;
        z-index: 1;
        width: min(calc(100% - 32px), 1080px);
        margin-inline: auto;
        padding: clamp(34px, 5vw, 72px) 0 70px;
    }

    .verify-intro {
        width: min(100%, 620px);
        margin: 0 auto 26px;
        text-align: center;
        animation:
            verify-enter .42s cubic-bezier(.2,.8,.2,1) both;
    }

    .verify-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        color: var(--verify-gold);
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .15em;
        text-transform: uppercase;
    }

    .verify-eyebrow::before,
    .verify-eyebrow::after {
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

    .verify-eyebrow::after {
        transform: scaleX(-1);
    }

    .verify-title {
        margin: 0;
        color: #fff;
        font-size: clamp(36px, 6vw, 62px);
        line-height: .96;
        font-weight: 930;
        letter-spacing: -.055em;
        text-wrap: balance;
    }

    .verify-title span {
        display: block;
        margin-top: 5px;
        color: var(--verify-orange);
    }

    .verify-lead {
        max-width: 540px;
        margin: 16px auto 0;
        color: var(--verify-muted);
        font-size: 12px;
        line-height: 1.7;
    }

    .verify-card-wrap {
        width: min(100%, 500px);
        margin-inline: auto;
        animation:
            verify-enter .46s .05s cubic-bezier(.2,.8,.2,1) both;
    }

    .verify-card {
        position: relative;
        width: 100%;
        overflow: hidden;
        border: 1px solid var(--verify-line-strong);
        border-radius: var(--verify-radius);
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
        box-shadow: var(--verify-shadow);
    }

    .verify-card::before {
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

    .verify-card::after {
        content: "";
        position: absolute;
        right: 0;
        bottom: 0;
        width: 72px;
        height: 72px;
        pointer-events: none;
        border-right: 2px solid rgba(241,216,74,.55);
        border-bottom: 2px solid rgba(241,216,74,.55);
        border-radius: 0 0 calc(var(--verify-radius) - 1px) 0;
        opacity: .72;
    }

    .verify-card-inner {
        position: relative;
        z-index: 1;
        padding: 28px;
    }

    .verify-state[hidden] {
        display: none !important;
    }

    .verify-state.is-entering {
        animation:
            verify-state-in .18s ease-out both;
    }

    .verify-icon {
        width: 48px;
        height: 48px;
        margin: 0 auto 13px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(241,216,74,.22);
        border-radius: 14px;
        color: var(--verify-gold);
        background: rgba(241,216,74,.045);
    }

    .verify-icon.success {
        border-color: rgba(69,223,139,.22);
        color: var(--verify-green);
        background: rgba(69,223,139,.045);
    }

    .verify-icon svg {
        width: 23px;
        height: 23px;
    }

    .verify-heading {
        margin: 0;
        text-align: center;
        color: #fff;
        font-size: 25px;
        line-height: 1.1;
        font-weight: 900;
        letter-spacing: -.035em;
    }

    .verify-heading strong {
        color: var(--verify-gold);
        font-weight: inherit;
    }

    .verify-heading.success strong {
        color: var(--verify-green);
    }

    .verify-description {
        max-width: 390px;
        margin: 8px auto 18px;
        color: var(--verify-muted);
        text-align: center;
        font-size: 10px;
        line-height: 1.6;
    }

    .verify-message {
        margin-bottom: 11px;
        padding: 10px 11px;
        border-radius: 10px;
        font-size: 9px;
        line-height: 1.55;
    }

    .verify-message.success {
        border: 1px solid rgba(69,223,139,.18);
        color: #adf4c9;
        background: rgba(69,223,139,.055);
    }

    .verify-message.error {
        border: 1px solid rgba(255,143,143,.18);
        color: #ffc2c2;
        background: rgba(255,90,90,.055);
    }

    .verify-message ul {
        margin: 5px 0 0 16px;
        padding: 0;
    }

    .verify-email-row {
        display: grid;
        grid-template-columns: minmax(0,1fr) auto;
        gap: 8px;
        margin-bottom: 16px;
    }

    .verify-input {
        width: 100%;
        min-width: 0;
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

    .verify-input::placeholder {
        color: #5c5c64;
    }

    .verify-input:focus {
        border-color: rgba(241,216,74,.48);
        background: rgba(241,216,74,.02);
        box-shadow: 0 0 0 3px rgba(241,216,74,.055);
    }

    .verify-send-button {
        min-height: 44px;
        padding: 0 13px;
        border: 1px solid rgba(241,216,74,.18);
        border-radius: 10px;
        color: var(--verify-gold);
        background: rgba(241,216,74,.045);
        font: inherit;
        font-size: 8px;
        font-weight: 900;
        cursor: pointer;
        white-space: nowrap;
        transition:
            transform .14s ease,
            background .14s ease;
    }

    .verify-send-button:hover:not(:disabled) {
        transform: translateY(-1px);
        background: rgba(241,216,74,.07);
    }

    .verify-send-button:disabled {
        opacity: .58;
        cursor: wait;
    }

    .verify-code-label {
        display: block;
        margin-bottom: 8px;
        color: #8c8c94;
        font-size: 8px;
        font-weight: 850;
        text-align: center;
        letter-spacing: .035em;
    }

    .verify-boxes {
        width: min(100%, 390px);
        margin-inline: auto;
        display: grid;
        grid-template-columns: repeat(6, minmax(0,1fr));
        gap: 8px;
    }

    .verify-box {
        width: 100%;
        min-width: 0;
        aspect-ratio: 1 / 1;
        border: 1px solid rgba(241,216,74,.28);
        border-radius: 11px;
        outline: none;
        color: #fff;
        background: rgba(0,0,0,.28);
        text-align: center;
        font: inherit;
        font-size: clamp(17px, 5vw, 21px);
        font-weight: 900;
        caret-color: var(--verify-gold);
        transition:
            transform .14s ease,
            border-color .14s ease,
            background .14s ease,
            box-shadow .14s ease;
    }

    .verify-box:focus {
        border-color: var(--verify-gold);
        background: rgba(241,216,74,.035);
        box-shadow: 0 0 0 2px rgba(241,216,74,.075);
        transform: translateY(-1px);
    }

    .verify-actions {
        margin-top: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .verify-resend {
        color: #85858d;
        font-size: 8px;
        line-height: 1.5;
    }

    .verify-resend button {
        padding: 0;
        border: 0;
        color: var(--verify-gold);
        background: transparent;
        font: inherit;
        font-weight: 900;
        cursor: pointer;
    }

    .verify-resend button:disabled {
        opacity: .55;
        cursor: default;
    }

    .verify-main-button {
        min-height: 42px;
        padding: 0 18px;
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
        font-size: 8px;
        font-weight: 950;
        cursor: pointer;
        white-space: nowrap;
        transition:
            transform .15s ease,
            filter .15s ease;
    }

    .verify-main-button:hover:not(:disabled) {
        transform: translateY(-1px);
        filter: brightness(1.045);
    }

    .verify-main-button:active:not(:disabled) {
        transform: scale(.992);
    }

    .verify-main-button:disabled {
        opacity: .66;
        cursor: wait;
    }

    .verify-loading {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 18px 0 8px;
        text-align: center;
    }

    .verify-spinner {
        width: 34px;
        height: 34px;
        margin: 9px auto 18px;
        border: 3px solid rgba(241,216,74,.14);
        border-top-color: var(--verify-gold);
        border-radius: 50%;
        animation: verify-spin .7s linear infinite;
    }

    .verify-loading-copy {
        color: var(--verify-muted);
        font-size: 10px;
        line-height: 1.6;
    }

    .verify-success {
        padding: 14px 0 8px;
        text-align: center;
    }

    .verify-check {
        width: 64px;
        height: 64px;
        margin: 18px auto;
        display: grid;
        place-items: center;
        border: 2px solid rgba(69,223,139,.7);
        border-radius: 18px;
        color: var(--verify-green);
        background: rgba(69,223,139,.045);
        animation:
            verify-success-in .3s cubic-bezier(.2,.8,.2,1) both;
    }

    .verify-check svg {
        width: 31px;
        height: 31px;
    }

    .verify-success-copy {
        color: var(--verify-muted);
        font-size: 10px;
        line-height: 1.6;
    }

    .verify-divider {
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

    .verify-divider::before,
    .verify-divider::after {
        content: "";
        flex: 1;
        height: 1px;
        background: rgba(255,255,255,.075);
    }

    .verify-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px 11px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 11px;
        background: rgba(0,0,0,.14);
    }

    .verify-bottom-copy strong,
    .verify-bottom-copy span {
        display: block;
    }

    .verify-bottom-copy strong {
        color: #d2d2d5;
        font-size: 8px;
    }

    .verify-bottom-copy span {
        margin-top: 3px;
        color: #75757d;
        font-size: 7px;
        line-height: 1.5;
    }

    .verify-bottom-link {
        min-height: 34px;
        padding: 0 11px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        border: 1px solid rgba(241,216,74,.18);
        border-radius: 9px;
        color: var(--verify-gold);
        background: rgba(241,216,74,.045);
        text-decoration: none;
        font-size: 7px;
        font-weight: 900;
        transition:
            transform .14s ease,
            background .14s ease;
    }

    .verify-bottom-link:hover {
        transform: translateY(-1px);
        background: rgba(241,216,74,.07);
    }

    .verify-security-note {
        margin-top: 12px;
        display: flex;
        align-items: flex-start;
        gap: 8px;
        color: #6c6c73;
        font-size: 7px;
        line-height: 1.55;
    }

    .verify-security-mark {
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

    .verify-footer {
        margin-top: 18px;
        color: #606067;
        text-align: center;
        font-size: 9px;
        animation:
            verify-enter .46s .09s cubic-bezier(.2,.8,.2,1) both;
    }

    @keyframes verify-enter {
        from {
            opacity: 0;
            transform: translate3d(0, 12px, 0) scale(.992);
        }

        to {
            opacity: 1;
            transform: translate3d(0, 0, 0) scale(1);
        }
    }

    @keyframes verify-state-in {
        from {
            opacity: 0;
            transform: translate3d(0, 5px, 0);
        }

        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    @keyframes verify-spin {
        to {
            transform: rotate(360deg);
        }
    }

    @keyframes verify-success-in {
        from {
            opacity: 0;
            transform: scale(.9);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    @media (max-width: 640px) {
        .verify-shell {
            width: min(calc(100% - 20px), 1080px);
            padding-top: 26px;
            padding-bottom: 48px;
        }

        .verify-intro {
            margin-bottom: 20px;
        }

        .verify-title {
            font-size: clamp(34px, 12vw, 46px);
        }

        .verify-lead {
            font-size: 11px;
        }

        .verify-card-wrap {
            width: 100%;
            max-width: 500px;
        }

        .verify-card-inner {
            padding: 22px 18px 24px;
        }

        .verify-card::after {
            width: 52px;
            height: 52px;
        }

        .verify-boxes {
            gap: 6px;
        }

        .verify-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .verify-main-button {
            width: 100%;
        }
    }

    @media (max-width: 390px) {
        .verify-shell {
            width: min(calc(100% - 14px), 1080px);
        }

        .verify-card-inner {
            padding-inline: 14px;
        }

        .verify-email-row {
            grid-template-columns: 1fr;
        }

        .verify-send-button {
            width: 100%;
        }

        .verify-boxes {
            gap: 5px;
        }

        .verify-bottom {
            align-items: flex-start;
            flex-direction: column;
        }

        .verify-bottom-link {
            width: 100%;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .verify-intro,
        .verify-card-wrap,
        .verify-footer,
        .verify-state.is-entering,
        .verify-check {
            animation: none !important;
        }

        .verify-page *,
        .verify-page *::before,
        .verify-page *::after {
            scroll-behavior: auto !important;
            transition-duration: .01ms !important;
        }

        .verify-spinner {
            animation-duration: 1.4s;
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
            : session('email', request('email', ''))
    );
@endphp

<section class="verify-page">
    <main
        class="verify-shell"
        id="verifyStage"
        data-send-url="{{ route('verification.send') }}"
        data-verify-url="{{ route('verification.verify') }}"
        data-login-url="{{ route('login') }}"
    >
        <header class="verify-intro">
            <div class="verify-eyebrow">
                Secure verification
            </div>

            <h1 class="verify-title">
                E-mail verifiëren
                <span>Mashal Studio</span>
            </h1>

            <p class="verify-lead">
                Vul de 6-cijferige beveiligingscode uit je e-mail in
                om je account veilig te activeren.
            </p>
        </header>

        <div class="verify-card-wrap">
            <section class="verify-card" aria-labelledby="verifyHeading">
                <div class="verify-card-inner">
                    <section
                        class="verify-state is-entering"
                        id="verifyEntry"
                        aria-live="polite"
                    >
                        <div class="verify-icon" aria-hidden="true">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <rect x="4" y="5" width="16" height="14" rx="3"></rect>
                                <path d="M4 8l8 5 8-5"></path>
                            </svg>
                        </div>

                        <h2 class="verify-heading" id="verifyHeading">
                            Verify <strong>OTP</strong>
                        </h2>

                        <p class="verify-description">
                            Controleer je e-mailadres en voer daarna de
                            6-cijferige code in.
                        </p>

                        @if (session('success'))
                            <div class="verify-message success" role="status">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="verify-message error" role="alert">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="verify-message error" role="alert">
                                <strong>
                                    Verificatie is niet gelukt.
                                </strong>

                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div
                            class="verify-message error"
                            id="verifyClientError"
                            role="alert"
                            hidden
                        ></div>

                        <form
                            id="verifySendForm"
                            method="POST"
                            action="{{ route('verification.send') }}"
                        >
                            @csrf

                            <div class="verify-email-row">
                                <input
                                    class="verify-input"
                                    id="verifyEmail"
                                    type="email"
                                    name="email"
                                    value="{{ $verifyEmail }}"
                                    autocomplete="email"
                                    inputmode="email"
                                    autocapitalize="none"
                                    spellcheck="false"
                                    maxlength="255"
                                    placeholder="naam@example.com"
                                    aria-label="E-mailadres"
                                    required
                                >

                                <button
                                    class="verify-send-button"
                                    id="verifySendButton"
                                    type="submit"
                                >
                                    Stuur code
                                </button>
                            </div>
                        </form>

                        <form
                            id="verifyCodeForm"
                            method="POST"
                            action="{{ route('verification.verify') }}"
                        >
                            @csrf

                            <input
                                id="verifyEmailHidden"
                                type="hidden"
                                name="email"
                                value="{{ $verifyEmail }}"
                            >

                            <input
                                id="verifyCodeHidden"
                                type="hidden"
                                name="code"
                                value="{{ old('code') }}"
                            >

                            <span class="verify-code-label">
                                6-cijferige verificatiecode
                            </span>

                            <div
                                class="verify-boxes"
                                id="verifyBoxes"
                                role="group"
                                aria-label="6-cijferige verificatiecode"
                            >
                                @for ($i = 0; $i < 6; $i++)
                                    <input
                                        class="verify-box"
                                        type="text"
                                        inputmode="numeric"
                                        pattern="[0-9]*"
                                        maxlength="1"
                                        autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                                        aria-label="Cijfer {{ $i + 1 }}"
                                        data-verify-index="{{ $i }}"
                                    >
                                @endfor
                            </div>

                            <div class="verify-actions">
                                <div class="verify-resend">
                                    Geen code ontvangen?

                                    <button
                                        id="verifyResendButton"
                                        type="button"
                                    >
                                        Opnieuw sturen
                                    </button>

                                    <span id="verifyResendTimer"></span>
                                </div>

                                <button
                                    class="verify-main-button"
                                    id="verifySubmitButton"
                                    type="submit"
                                >
                                    Verifieer &amp; ga verder →
                                </button>
                            </div>
                        </form>
                    </section>

                    <section
                        class="verify-state verify-loading"
                        id="verifyLoading"
                        hidden
                        aria-live="polite"
                    >
                        <div class="verify-icon" aria-hidden="true">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <rect x="4" y="5" width="16" height="14" rx="3"></rect>
                                <path d="M4 8l8 5 8-5"></path>
                            </svg>
                        </div>

                        <h2 class="verify-heading">
                            Code <strong>controleren</strong>
                        </h2>

                        <div
                            class="verify-spinner"
                            aria-hidden="true"
                        ></div>

                        <p class="verify-loading-copy">
                            Een moment. We controleren je beveiligingscode.
                        </p>
                    </section>

                    <section
                        class="verify-state verify-success"
                        id="verifySuccess"
                        hidden
                        aria-live="polite"
                    >
                        <div class="verify-icon success" aria-hidden="true">
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

                        <h2 class="verify-heading success">
                            Verified <strong>Successfully</strong>
                        </h2>

                        <div class="verify-check" aria-hidden="true">
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

                        <p class="verify-success-copy">
                            Je e-mailadres is veilig bevestigd.
                            Je wordt direct doorgestuurd.
                        </p>
                    </section>

                    <div class="verify-divider">
                        Accountbeveiliging
                    </div>

                    <div class="verify-bottom">
                        <div class="verify-bottom-copy">
                            <strong>
                                Terug naar inloggen
                            </strong>

                            <span>
                                Gebruik alleen codes die je zelf van
                                Mashal Studio hebt aangevraagd.
                            </span>
                        </div>

                        <a
                            class="verify-bottom-link"
                            href="{{ route('login') }}"
                        >
                            Inloggen
                        </a>
                    </div>

                    <div class="verify-security-note">
                        <span
                            class="verify-security-mark"
                            aria-hidden="true"
                        >
                            ✓
                        </span>

                        <span>
                            Deel je verificatiecode nooit met anderen.
                            Mashal Studio vraagt je code niet via chat of telefoon.
                        </span>
                    </div>
                </div>
            </section>

            <div class="verify-footer">
                Mashal Studio · Secure e-mail verification
            </div>
        </div>
    </main>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const stage =
        document.getElementById(
            'verifyStage'
        );

    const entry =
        document.getElementById(
            'verifyEntry'
        );

    const loading =
        document.getElementById(
            'verifyLoading'
        );

    const success =
        document.getElementById(
            'verifySuccess'
        );

    const sendForm =
        document.getElementById(
            'verifySendForm'
        );

    const verifyForm =
        document.getElementById(
            'verifyCodeForm'
        );

    const emailInput =
        document.getElementById(
            'verifyEmail'
        );

    const hiddenEmail =
        document.getElementById(
            'verifyEmailHidden'
        );

    const hiddenCode =
        document.getElementById(
            'verifyCodeHidden'
        );

    const sendButton =
        document.getElementById(
            'verifySendButton'
        );

    const submitButton =
        document.getElementById(
            'verifySubmitButton'
        );

    const resendButton =
        document.getElementById(
            'verifyResendButton'
        );

    const resendTimer =
        document.getElementById(
            'verifyResendTimer'
        );

    const clientError =
        document.getElementById(
            'verifyClientError'
        );

    const otpInputs =
        Array.from(
            document.querySelectorAll(
                '[data-verify-index]'
            )
        );

    const csrfToken =
        verifyForm
            ?.querySelector(
                'input[name="_token"]'
            )
            ?.value
        || '';

    let countdownInterval =
        null;

    let isVerifying =
        false;

    function digitsOnly(value) {
        return String(
            value || ''
        )
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
            hiddenEmail &&
            emailInput
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
    }

    function clearClientError() {
        if (!clientError) {
            return;
        }

        clientError.hidden =
            true;

        clientError.textContent =
            '';
    }

    function showClientError(message) {
        if (!clientError) {
            return;
        }

        clientError.textContent =
            String(
                message ||
                'Er ging iets mis.'
            );

        clientError.hidden =
            false;
    }

    function showState(name) {
        if (
            !entry ||
            !loading ||
            !success
        ) {
            return;
        }

        const states = {
            entry,
            loading,
            success,
        };

        Object
            .entries(states)
            .forEach(function ([key, element]) {
                const active =
                    key === name;

                element.hidden =
                    !active;

                element.classList.remove(
                    'is-entering'
                );

                if (active) {
                    window.requestAnimationFrame(
                        function () {
                            element.classList.add(
                                'is-entering'
                            );
                        }
                    );
                }
            });
    }

    function startCountdown(seconds) {
        window.clearInterval(
            countdownInterval
        );

        if (
            !resendButton ||
            !resendTimer
        ) {
            return;
        }

        let remaining =
            Math.max(
                0,
                Number(seconds) || 0
            );

        function render() {
            if (remaining <= 0) {
                resendButton.disabled =
                    false;

                resendButton.textContent =
                    'Opnieuw sturen';

                resendTimer.textContent =
                    '';

                window.clearInterval(
                    countdownInterval
                );

                return;
            }

            resendButton.disabled =
                true;

            resendTimer.textContent =
                ' in 00:' +
                String(remaining)
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
        const code =
            digitsOnly(
                hiddenCode?.value || ''
            );

        code
            .split('')
            .forEach(
                function (digit, index) {
                    if (otpInputs[index]) {
                        otpInputs[index].value =
                            digit;
                    }
                }
            );

        syncEmail();
        syncCode();
    }

    function fillOtpFrom(
        startIndex,
        value
    ) {
        const digits =
            digitsOnly(value);

        if (!digits) {
            return;
        }

        digits
            .split('')
            .forEach(
                function (digit, offset) {
                    const target =
                        otpInputs[
                            startIndex + offset
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
                startIndex +
                    digits.length,
                otpInputs.length - 1
            );

        otpInputs[
            nextIndex
        ]?.focus();

        if (
            currentCode().length === 6
        ) {
            submitButton?.focus();
        }
    }

    async function sendVerificationCode() {
        clearClientError();
        syncEmail();

        const email =
            emailInput
                ?.value
                .trim()
        || '';

        if (
            !email ||
            !emailInput?.checkValidity()
        ) {
            showClientError(
                'Vul eerst een geldig e-mailadres in.'
            );

            emailInput?.focus();

            return false;
        }

        if (sendButton) {
            sendButton.disabled =
                true;

            sendButton.textContent =
                'Bezig…';
        }

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

            const finalUrl =
                new URL(
                    response.url ||
                    window.location.href,
                    window.location.origin
                );

            const currentPath =
                window.location.pathname
                    .replace(/\/+$/, '');

            const finalPath =
                finalUrl.pathname
                    .replace(/\/+$/, '');

            /*
             * Als de backend bijvoorbeeld aangeeft dat het e-mailadres
             * al geverifieerd is en naar /login stuurt, volgen we dat
             * meteen in plaats van onnodig op deze pagina te blijven.
             */
            if (
                finalPath &&
                finalPath !== currentPath
            ) {
                window.location.assign(
                    finalUrl.href
                );

                return true;
            }

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
            if (sendButton) {
                sendButton.disabled =
                    false;

                sendButton.textContent =
                    'Stuur code';
            }
        }
    }

    otpInputs.forEach(
        function (input, index) {
            input.addEventListener(
                'input',
                function () {
                    clearClientError();

                    const raw =
                        String(
                            input.value || ''
                        );

                    const clean =
                        digitsOnly(raw);

                    if (clean.length > 1) {
                        fillOtpFrom(
                            index,
                            clean
                        );

                        return;
                    }

                    input.value =
                        clean;

                    syncCode();

                    if (
                        clean &&
                        otpInputs[index + 1]
                    ) {
                        otpInputs[
                            index + 1
                        ].focus();
                    }

                    if (
                        currentCode().length
                        === 6
                    ) {
                        submitButton?.focus();
                    }
                }
            );

            input.addEventListener(
                'keydown',
                function (event) {
                    if (
                        event.key ===
                            'Backspace' &&
                        input.value === '' &&
                        otpInputs[index - 1]
                    ) {
                        const previous =
                            otpInputs[
                                index - 1
                            ];

                        previous.value =
                            '';

                        previous.focus();

                        syncCode();

                        return;
                    }

                    if (
                        event.key ===
                            'ArrowLeft' &&
                        otpInputs[index - 1]
                    ) {
                        event.preventDefault();

                        otpInputs[
                            index - 1
                        ].focus();

                        return;
                    }

                    if (
                        event.key ===
                            'ArrowRight' &&
                        otpInputs[index + 1]
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
                            event
                                .clipboardData
                                ?.getData('text')
                            || ''
                        );

                    if (!pasted) {
                        return;
                    }

                    event.preventDefault();

                    fillOtpFrom(
                        index,
                        pasted
                    );
                }
            );
        }
    );

    emailInput?.addEventListener(
        'input',
        function () {
            syncEmail();
            clearClientError();
        }
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
                hiddenEmail
                    ?.value
                    .trim()
            || '';

            const code =
                currentCode();

            if (
                !email ||
                !emailInput?.checkValidity()
            ) {
                showClientError(
                    'Vul eerst een geldig e-mailadres in.'
                );

                emailInput?.focus();

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

            isVerifying =
                true;

            if (submitButton) {
                submitButton.disabled =
                    true;
            }

            showState(
                'loading'
            );

            try {
                const body =
                    new FormData(
                        verifyForm
                    );

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
                        response.url ||
                        stage.dataset.loginUrl,
                        window.location.origin
                    );

                const currentPath =
                    window.location.pathname
                        .replace(/\/+$/, '');

                const finalPath =
                    finalUrl.pathname
                        .replace(/\/+$/, '');

                if (
                    finalPath ===
                    currentPath
                ) {
                    throw new Error(
                        'De code is ongeldig of verlopen. Controleer de code of vraag een nieuwe aan.'
                    );
                }

                showState(
                    'success'
                );

                try {
                    window.localStorage.removeItem(
                        'mashal_auth_success_pending'
                    );
                } catch (error) {
                    //
                }

                /*
                 * Geen kunstmatige vertraging meer.
                 * Eén paint-frame voor de successtate en direct door.
                 */
                window.requestAnimationFrame(
                    function () {
                        window.location.assign(
                            finalUrl.href
                        );
                    }
                );
            } catch (error) {
                showState(
                    'entry'
                );

                showClientError(
                    error instanceof Error
                        ? error.message
                        : 'Er ging iets mis tijdens de verificatie.'
                );

                otpInputs.forEach(
                    function (input) {
                        input.value =
                            '';
                    }
                );

                syncCode();

                otpInputs[0]?.focus();
            } finally {
                isVerifying =
                    false;

                if (submitButton) {
                    submitButton.disabled =
                        false;
                }
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
