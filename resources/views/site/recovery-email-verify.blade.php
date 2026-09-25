@extends('layouts.site-layout')

@section('title', 'Hersteladres bevestigen | Mashal Studio')

@section(
    'meta_description',
    'Controleer de 6-cijferige code om je herstel-e-mailadres veilig te bevestigen.'
)

@section('content')
<style>
    .recovery-verify-page,
    .recovery-verify-page * {
        box-sizing: border-box;
    }

    .recovery-verify-page {
        --rv-bg: #050506;
        --rv-panel: #0f1013;
        --rv-panel-2: #090a0c;
        --rv-border: rgba(255,255,255,.10);
        --rv-text: #f7f7f8;
        --rv-muted: #929399;
        --rv-yellow: #f4ee1f;
        --rv-orange: #ff6b23;
        --rv-danger: #ff9e9e;
        --rv-success: #9bf5c7;

        position: relative;
        isolation: isolate;
        width: 100%;
        min-height: calc(100dvh - 76px);
        padding: 54px 18px 80px;
        overflow: hidden;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        color: var(--rv-text);
        background:
            radial-gradient(circle at 18% 16%, rgba(255,107,35,.08), transparent 24rem),
            radial-gradient(circle at 84% 72%, rgba(244,238,31,.045), transparent 28rem),
            var(--rv-bg);
    }

    .recovery-verify-bg {
        position: absolute;
        inset: 0;
        z-index: 0;
        overflow: hidden;
        pointer-events: none;
    }

    .recovery-verify-bg::before,
    .recovery-verify-bg::after {
        content: "";
        position: absolute;
        width: min(920px, 125vw);
        height: 105px;
        border-radius: 999px;
        opacity: .26;
        will-change: transform, opacity;
    }

    .recovery-verify-bg::before {
        left: -42%;
        top: 14%;
        background:
            linear-gradient(
                180deg,
                transparent 0 28%,
                rgba(255,132,0,.07) 38%,
                rgba(255,200,57,.34) 48%,
                rgba(255,233,130,.62) 51%,
                rgba(255,114,0,.18) 59%,
                transparent 72%
            );
        transform: rotate(28deg);
        animation: rvBgA 12s ease-in-out infinite alternate;
    }

    .recovery-verify-bg::after {
        right: -46%;
        top: 58%;
        background:
            linear-gradient(
                180deg,
                transparent 0 28%,
                rgba(255,152,0,.05) 38%,
                rgba(255,213,87,.28) 49%,
                rgba(255,238,157,.50) 51%,
                rgba(255,112,0,.14) 59%,
                transparent 72%
            );
        transform: rotate(-27deg);
        animation: rvBgB 15s ease-in-out infinite alternate;
    }

    @keyframes rvBgA {
        from {
            transform: rotate(28deg) translate3d(-4%, -4px, 0);
            opacity: .18;
        }

        to {
            transform: rotate(23deg) translate3d(11%, 13px, 0);
            opacity: .30;
        }
    }

    @keyframes rvBgB {
        from {
            transform: rotate(-27deg) translate3d(5%, 4px, 0);
            opacity: .15;
        }

        to {
            transform: rotate(-22deg) translate3d(-10%, -12px, 0);
            opacity: .28;
        }
    }

    .recovery-verify-shell {
        position: relative;
        z-index: 2;
        width: min(100%, 470px);
        margin-inline: auto;
    }

    .recovery-verify-brand {
        margin: 0 0 28px;
        text-align: center;
    }

    .recovery-verify-kicker {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        color: #d7bd65;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .17em;
        text-transform: uppercase;
    }

    .recovery-verify-kicker::before,
    .recovery-verify-kicker::after {
        content: "";
        width: 28px;
        height: 1px;
        background:
            linear-gradient(
                90deg,
                transparent,
                rgba(244,238,31,.66)
            );
    }

    .recovery-verify-kicker::after {
        transform: scaleX(-1);
    }

    .recovery-verify-title {
        margin: 14px 0 0;
        color: #fafafa;
        font-family: Arial, Helvetica, sans-serif;
        font-size: clamp(36px, 10vw, 52px);
        font-weight: 500;
        line-height: .94;
        letter-spacing: -.055em;
        text-wrap: balance;
    }

    .recovery-verify-title span {
        color: var(--rv-orange);
    }

    .recovery-verify-card {
        position: relative;
        overflow: hidden;
        width: 100%;
        padding: clamp(25px, 6vw, 34px);
        border: 1px solid var(--rv-border);
        border-radius: 25px;
        background:
            linear-gradient(145deg, rgba(255,255,255,.045), transparent 32%),
            linear-gradient(180deg, var(--rv-panel), var(--rv-panel-2));
        box-shadow:
            0 28px 75px rgba(0,0,0,.40),
            inset 0 1px 0 rgba(255,255,255,.035);
    }

    .recovery-verify-card::before {
        content: "";
        position: absolute;
        top: -110%;
        left: -35%;
        width: 42%;
        height: 310%;
        pointer-events: none;
        opacity: .16;
        background:
            linear-gradient(
                90deg,
                transparent,
                rgba(255,255,255,.38),
                transparent
            );
        transform: rotate(23deg);
        animation: rvSweep 8s ease-in-out infinite;
    }

    @keyframes rvSweep {
        0%,
        62% {
            transform: translate3d(-140%, 0, 0) rotate(23deg);
            opacity: 0;
        }

        72% {
            opacity: .13;
        }

        100% {
            transform: translate3d(430%, 0, 0) rotate(23deg);
            opacity: 0;
        }
    }

    .recovery-verify-card > * {
        position: relative;
        z-index: 1;
    }

    .recovery-verify-icon {
        width: 54px;
        height: 54px;
        display: grid;
        place-items: center;
        margin-bottom: 20px;
        border: 1px solid rgba(244,238,31,.20);
        border-radius: 16px;
        color: var(--rv-yellow);
        background: rgba(244,238,31,.045);
    }

    .recovery-verify-icon svg {
        width: 26px;
        height: 26px;
    }

    .recovery-verify-heading {
        margin: 0;
        color: #f8f8f9;
        font-size: clamp(25px, 7vw, 32px);
        font-weight: 850;
        line-height: 1.03;
        letter-spacing: -.045em;
    }

    .recovery-verify-heading strong {
        color: var(--rv-yellow);
        font-weight: inherit;
    }

    .recovery-verify-description {
        margin: 13px 0 0;
        color: var(--rv-muted);
        font-size: 12px;
        line-height: 1.75;
    }

    .recovery-verify-destination {
        margin-top: 18px;
        padding: 11px 13px;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 11px;
        color: #c4c5c9;
        background: rgba(255,255,255,.025);
        font-size: 10px;
        line-height: 1.55;
        word-break: break-word;
    }

    .recovery-verify-message {
        margin-top: 16px;
        padding: 12px 13px;
        border-radius: 11px;
        font-size: 10px;
        line-height: 1.55;
    }

    .recovery-verify-message.info {
        border: 1px solid rgba(80,195,160,.16);
        color: #b9e8d8;
        background: rgba(41,132,105,.075);
    }

    .recovery-verify-message.success {
        border: 1px solid rgba(72,210,135,.18);
        color: var(--rv-success);
        background: rgba(38,146,89,.075);
    }

    .recovery-verify-message.error {
        border: 1px solid rgba(255,100,100,.18);
        color: var(--rv-danger);
        background: rgba(168,47,47,.08);
    }

    .recovery-verify-message ul {
        margin: 7px 0 0;
        padding-left: 17px;
    }

    .recovery-verify-form {
        margin-top: 24px;
    }

    .recovery-verify-label {
        display: block;
        margin-bottom: 10px;
        color: #c9c9cd;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .recovery-code-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 7px;
    }

    .recovery-code-digit {
        width: 100%;
        min-width: 0;
        aspect-ratio: .88;
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 12px;
        outline: none;
        color: #ffffff;
        background: rgba(255,255,255,.026);
        text-align: center;
        font-size: clamp(20px, 6vw, 27px);
        font-weight: 900;
        caret-color: var(--rv-yellow);
        transition:
            border-color .18s ease,
            background .18s ease,
            transform .18s ease,
            box-shadow .18s ease;
    }

    .recovery-code-digit:focus {
        border-color: rgba(244,238,31,.48);
        background: rgba(244,238,31,.045);
        box-shadow: 0 0 0 3px rgba(244,238,31,.055);
        transform: translateY(-1px);
    }

    .recovery-code-digit.filled {
        border-color: rgba(255,107,35,.30);
    }

    .recovery-code-error {
        margin-top: 9px;
        color: var(--rv-danger);
        font-size: 9px;
        line-height: 1.55;
    }

    .recovery-verify-primary,
    .recovery-verify-secondary,
    .recovery-verify-back {
        border: 0;
        text-decoration: none;
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
    }

    .recovery-verify-primary {
        width: 100%;
        min-height: 48px;
        margin-top: 16px;
        padding: 0 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        color: #171400;
        background:
            linear-gradient(
                135deg,
                #f6f01d,
                #d6b30f
            );
        box-shadow: 0 12px 28px rgba(214,179,15,.12);
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .025em;
    }

    .recovery-verify-primary:disabled,
    .recovery-verify-secondary:disabled {
        cursor: wait;
        opacity: .64;
    }

    .recovery-verify-resend {
        margin-top: 22px;
        padding-top: 18px;
        border-top: 1px solid rgba(255,255,255,.065);
    }

    .recovery-verify-resend p {
        margin: 0 0 10px;
        color: #787980;
        font-size: 9px;
        line-height: 1.55;
    }

    .recovery-verify-secondary {
        min-height: 38px;
        padding: 0 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255,255,255,.09);
        border-radius: 10px;
        color: #d1d1d4;
        background: rgba(255,255,255,.025);
        font-size: 9px;
        font-weight: 850;
    }

    .recovery-verify-note {
        margin: 17px 0 0;
        color: #66676d;
        font-size: 8px;
        line-height: 1.65;
    }

    .recovery-verify-actions {
        margin-top: 20px;
        padding-top: 18px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        border-top: 1px solid rgba(255,255,255,.055);
    }

    .recovery-verify-back {
        min-height: 36px;
        padding: 0 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 9px;
        color: #aaaab0;
        background: rgba(255,255,255,.018);
        font-size: 8px;
        font-weight: 850;
    }

    @media (max-width: 390px) {
        .recovery-verify-page {
            padding-inline: 13px;
            padding-top: 38px;
        }

        .recovery-verify-card {
            padding: 23px 17px;
            border-radius: 21px;
        }

        .recovery-code-grid {
            gap: 5px;
        }

        .recovery-code-digit {
            border-radius: 10px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .recovery-verify-bg::before,
        .recovery-verify-bg::after,
        .recovery-verify-card::before {
            animation: none !important;
        }

        .recovery-code-digit,
        .recovery-verify-primary,
        .recovery-verify-secondary,
        .recovery-verify-back {
            transition: none !important;
        }
    }
</style>

<section class="recovery-verify-page">
    <div class="recovery-verify-bg" aria-hidden="true"></div>

    <div class="recovery-verify-shell">
        <header class="recovery-verify-brand">
            <div class="recovery-verify-kicker">
                Account security
            </div>

            <h1 class="recovery-verify-title">
                Bevestig je
                <span>hersteladres</span>
            </h1>
        </header>

        <div class="recovery-verify-card">
            <div class="recovery-verify-icon" aria-hidden="true">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <rect
                        x="4"
                        y="6"
                        width="16"
                        height="12"
                        rx="2"
                    ></rect>
                    <path d="m5.5 8 6.5 5 6.5-5"></path>
                    <path d="M17 15.5 18.5 17l2.5-3"></path>
                </svg>
            </div>

            <h2 class="recovery-verify-heading">
                Controleer de
                <strong>6-cijferige code</strong>
            </h2>

            <p class="recovery-verify-description">
                We hebben een verificatiecode naar je nieuwe
                herstel-e-mailadres gestuurd. Vul de code hieronder in om
                het adres veilig te activeren.
            </p>

            @if (!empty($maskedRecoveryEmail))
                <div class="recovery-verify-destination">
                    Code verstuurd naar
                    <strong>{{ $maskedRecoveryEmail }}</strong>
                </div>
            @endif

            @if (session('status'))
                <div
                    class="recovery-verify-message info"
                    role="status"
                >
                    {{ session('status') }}
                </div>
            @endif

            @if (session('success'))
                <div
                    class="recovery-verify-message success"
                    role="status"
                >
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div
                    class="recovery-verify-message error"
                    role="alert"
                >
                    <strong>Controleer de verificatie.</strong>

                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                class="recovery-verify-form"
                method="POST"
                action="{{ route('account.recovery-email.verify.submit') }}"
                id="recoveryVerifyForm"
            >
                @csrf

                <label
                    class="recovery-verify-label"
                    for="recoveryDigit1"
                >
                    Verificatiecode
                </label>

                <input
                    type="hidden"
                    name="code"
                    id="recoveryCode"
                    value="{{ old('code') }}"
                >

                <div
                    class="recovery-code-grid"
                    role="group"
                    aria-label="6-cijferige verificatiecode"
                >
                    @for ($i = 1; $i <= 6; $i++)
                        <input
                            class="recovery-code-digit"
                            id="recoveryDigit{{ $i }}"
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            maxlength="1"
                            autocomplete="{{ $i === 1 ? 'one-time-code' : 'off' }}"
                            aria-label="Cijfer {{ $i }} van 6"
                            data-index="{{ $i - 1 }}"
                        >
                    @endfor
                </div>

                @error('code')
                    <div class="recovery-code-error">
                        {{ $message }}
                    </div>
                @enderror

                <button
                    class="recovery-verify-primary"
                    id="recoveryVerifySubmit"
                    type="submit"
                >
                    Code bevestigen →
                </button>
            </form>

            <div class="recovery-verify-resend">
                <p>
                    Geen code ontvangen? Vraag een nieuwe code aan.
                </p>

                <form
                    method="POST"
                    action="{{ route('account.recovery-email.resend') }}"
                    id="recoveryResendForm"
                >
                    @csrf

                    <button
                        class="recovery-verify-secondary"
                        id="recoveryResendButton"
                        type="submit"
                    >
                        Nieuwe code sturen
                    </button>
                </form>
            </div>

            <p class="recovery-verify-note">
                De code is tijdelijk geldig. Na meerdere onjuiste pogingen
                moet de verificatie opnieuw worden gestart.
            </p>

            <div class="recovery-verify-actions">
                <a
                    class="recovery-verify-back"
                    href="{{ route('account') }}#recovery"
                >
                    ← Terug naar account
                </a>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    function ready(callback) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, {
                once: true
            });
        } else {
            callback();
        }
    }

    ready(function () {
        const form =
            document.getElementById('recoveryVerifyForm');

        const hiddenCode =
            document.getElementById('recoveryCode');

        const submitButton =
            document.getElementById('recoveryVerifySubmit');

        const digits =
            Array.from(
                document.querySelectorAll(
                    '.recovery-code-digit'
                )
            );

        const resendForm =
            document.getElementById('recoveryResendForm');

        const resendButton =
            document.getElementById('recoveryResendButton');

        if (
            !form
            || !hiddenCode
            || !submitButton
            || digits.length !== 6
        ) {
            return;
        }

        function clean(value) {
            return String(value || '')
                .replace(/\D/g, '')
                .slice(0, 6);
        }

        function sync() {
            const code =
                digits
                    .map(function (input) {
                        return clean(input.value).slice(0, 1);
                    })
                    .join('');

            hiddenCode.value = code;

            digits.forEach(function (input) {
                input.classList.toggle(
                    'filled',
                    input.value !== ''
                );
            });

            return code;
        }

        function fill(value) {
            const code = clean(value);

            digits.forEach(function (input, index) {
                input.value = code[index] || '';
            });

            sync();

            const focusIndex =
                Math.min(
                    code.length,
                    digits.length - 1
                );

            digits[focusIndex].focus();
        }

        digits.forEach(function (input, index) {
            input.addEventListener('input', function () {
                const value = clean(input.value);

                if (value.length > 1) {
                    fill(value);
                    return;
                }

                input.value = value.slice(0, 1);
                sync();

                if (
                    input.value !== ''
                    && index < digits.length - 1
                ) {
                    digits[index + 1].focus();
                }
            });

            input.addEventListener('keydown', function (event) {
                if (
                    event.key === 'Backspace'
                    && input.value === ''
                    && index > 0
                ) {
                    digits[index - 1].focus();
                    return;
                }

                if (
                    event.key === 'ArrowLeft'
                    && index > 0
                ) {
                    event.preventDefault();
                    digits[index - 1].focus();
                }

                if (
                    event.key === 'ArrowRight'
                    && index < digits.length - 1
                ) {
                    event.preventDefault();
                    digits[index + 1].focus();
                }
            });

            input.addEventListener('paste', function (event) {
                const pasted =
                    clean(
                        event.clipboardData
                            ? event.clipboardData.getData('text')
                            : ''
                    );

                if (pasted.length > 1) {
                    event.preventDefault();
                    fill(pasted);
                }
            });
        });

        const oldCode = clean(hiddenCode.value);

        if (oldCode !== '') {
            fill(oldCode);
        } else {
            digits[0].focus();
        }

        form.addEventListener('submit', function (event) {
            const code = sync();

            if (code.length !== 6) {
                event.preventDefault();

                const index =
                    Math.min(
                        code.length,
                        digits.length - 1
                    );

                digits[index].focus();
                return;
            }

            submitButton.disabled = true;
            submitButton.textContent = 'Controleren…';
        });

        if (
            resendForm
            && resendButton
        ) {
            resendForm.addEventListener('submit', function () {
                resendButton.disabled = true;
                resendButton.textContent = 'Versturen…';
            });
        }
    });
})();
</script>
@endsection
