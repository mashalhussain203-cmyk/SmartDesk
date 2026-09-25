@extends('layouts.site-layout')

@section('title', 'Hersteladres bevestigen | Mashal Studio')

@section(
    'meta_description',
    'Controleer de 6-cijferige code om je herstel-e-mailadres veilig te bevestigen.'
)

@push('styles')
<style>
    :root {
        --verify-bg: #020202;
        --verify-card: rgba(15,15,18,.94);
        --verify-card-2: rgba(8,8,10,.97);
        --verify-text: #f7f7f8;
        --verify-muted: #8f8e94;
        --verify-yellow: #f4ee1f;
        --verify-orange: #ff6b23;
        --verify-danger: #ff9b9b;
        --verify-success: #9bf5c7;
    }

    .forgot-verify-page,
    .forgot-verify-page * {
        box-sizing: border-box;
    }

    .forgot-verify-page {
        position: relative;
        isolation: isolate;
        min-height: calc(100dvh - 76px);
        overflow: hidden;
        display: flex;
        justify-content: center;
        color: var(--verify-text);
        background:
            radial-gradient(
                circle at 50% 28%,
                rgba(255,177,0,.05),
                transparent 28rem
            ),
            var(--verify-bg);
    }

    /*
    |--------------------------------------------------------------------------
    | Animated background
    |--------------------------------------------------------------------------
    */

    .forgot-verify-bg {
        position: absolute;
        inset: 0;
        z-index: -4;
        overflow: hidden;
        pointer-events: none;
    }

    .forgot-verify-bg::before,
    .forgot-verify-bg::after {
        content: "";
        position: absolute;
        width: min(1000px, 110vw);
        height: 130px;
        border-radius: 999px;
        opacity: .25;
        will-change: transform, opacity;
    }

    .forgot-verify-bg::before {
        left: -34%;
        top: 18%;
        background:
            linear-gradient(
                180deg,
                transparent 0 28%,
                rgba(255,126,0,.06) 36%,
                rgba(255,195,53,.36) 47%,
                rgba(255,233,130,.68) 50%,
                rgba(255,126,0,.20) 57%,
                transparent 72%
            );
        transform:
            rotate(29deg)
            translate3d(0,0,0);
        animation:
            forgotVerifyBgA
            12s ease-in-out infinite alternate;
    }

    .forgot-verify-bg::after {
        right: -38%;
        top: 54%;
        background:
            linear-gradient(
                180deg,
                transparent 0 28%,
                rgba(255,147,0,.05) 38%,
                rgba(255,210,81,.28) 48%,
                rgba(255,236,145,.55) 51%,
                rgba(255,119,0,.17) 58%,
                transparent 72%
            );
        transform:
            rotate(-26deg)
            translate3d(0,0,0);
        animation:
            forgotVerifyBgB
            15s ease-in-out infinite alternate;
    }

    .forgot-verify-streak {
        position: absolute;
        left: 50%;
        bottom: 12%;
        width: min(760px, 78vw);
        height: 2px;
        border-radius: 999px;
        opacity: .22;
        background:
            linear-gradient(
                90deg,
                transparent,
                rgba(255,125,25,.25),
                rgba(241,216,74,.78),
                rgba(255,125,25,.25),
                transparent
            );
        transform:
            translate3d(-50%,0,0)
            rotate(-8deg);
        will-change: transform, opacity;
        animation:
            forgotVerifyStreak
            9s ease-in-out infinite alternate;
    }

    @keyframes forgotVerifyBgA {
        from {
            transform:
                rotate(29deg)
                translate3d(-4%,-5px,0)
                scale(.98);
            opacity: .18;
        }

        to {
            transform:
                rotate(24deg)
                translate3d(13%,16px,0)
                scale(1.04);
            opacity: .32;
        }
    }

    @keyframes forgotVerifyBgB {
        from {
            transform:
                rotate(-26deg)
                translate3d(5%,5px,0);
            opacity: .16;
        }

        to {
            transform:
                rotate(-21deg)
                translate3d(-12%,-14px,0)
                scale(1.05);
            opacity: .30;
        }
    }

    @keyframes forgotVerifyStreak {
        from {
            transform:
                translate3d(-54%,0,0)
                rotate(-8deg);
            opacity: .14;
        }

        to {
            transform:
                translate3d(-46%,-8px,0)
                rotate(-5deg);
            opacity: .28;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Stage
    |--------------------------------------------------------------------------
    */

    .forgot-verify-stage {
        width: min(100%, 540px);
        min-height: 100dvh;
        padding:
            clamp(48px, 7vh, 78px)
            22px
            72px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .forgot-verify-title {
        margin: 0 0 42px;
        text-align: center;
        color: #f8f8f8;
        font-family:
            Arial,
            Helvetica,
            sans-serif;
        font-size:
            clamp(
                35px,
                9vw,
                48px
            );
        font-weight: 400;
        line-height: .92;
        letter-spacing: -.055em;
        text-shadow:
            0 5px 20px
            rgba(0,0,0,.58);
    }

    .forgot-verify-title span {
        display: block;
        margin-top: 8px;
        color: var(--verify-orange);
    }

    /*
    |--------------------------------------------------------------------------
    | Card
    |--------------------------------------------------------------------------
    */

    .forgot-verify-card-shell {
        position: relative;
        width: min(100%, 440px);
        border-radius: 25px;
        box-shadow:
            0 22px 42px
            rgba(0,0,0,.42);
    }

    .forgot-verify-card-border {
        position: relative;
        padding: 1px;
        border-radius: 25px;
        background:
            linear-gradient(
                142deg,
                rgba(255,255,255,.46),
                rgba(255,218,132,.14) 28%,
                rgba(255,255,255,.10) 60%,
                rgba(255,196,63,.48)
            );
    }

    .forgot-verify-card {
        position: relative;
        overflow: hidden;
        border-radius: 24px;
        background:
            linear-gradient(
                137deg,
                rgba(255,255,255,.035),
                transparent 36%
            ),
            linear-gradient(
                160deg,
                var(--verify-card),
                var(--verify-card-2)
            );
    }

    .forgot-verify-card::before {
        content: "";
        position: absolute;
        inset: -55%;
        z-index: 0;
        pointer-events: none;
        opacity: .22;
        background:
            linear-gradient(
                125deg,
                transparent 33%,
                rgba(255,153,0,.09) 43%,
                rgba(255,226,133,.34) 47%,
                rgba(255,127,0,.10) 51%,
                transparent 58%
            );
        animation:
            forgotVerifySweep
            8s ease-in-out infinite;
        will-change: transform;
    }

    @keyframes forgotVerifySweep {
        0%,
        100% {
            transform:
                translateX(-36%)
                rotate(-2deg);
        }

        50% {
            transform:
                translateX(31%)
                rotate(1deg);
        }
    }

    .forgot-verify-corner {
        position: absolute;
        z-index: 3;
        width: 56px;
        height: 56px;
        pointer-events: none;
        opacity: .72;
    }

    .forgot-verify-corner.top-left {
        top: 0;
        left: 0;
        border-top:
            1px solid
            rgba(255,238,139,.55);
        border-left:
            1px solid
            rgba(255,238,139,.55);
        border-radius:
            24px 0 0 0;
        background:
            linear-gradient(
                135deg,
                rgba(255,255,255,.07),
                rgba(255,218,92,.025) 48%,
                transparent 72%
            );
    }

    .forgot-verify-corner.bottom-right {
        right: 0;
        bottom: 0;
        border-right:
            1px solid
            rgba(255,205,72,.55);
        border-bottom:
            1px solid
            rgba(255,205,72,.55);
        border-radius:
            0 0 24px 0;
        background:
            linear-gradient(
                315deg,
                rgba(255,255,255,.06),
                rgba(255,187,48,.025) 48%,
                transparent 72%
            );
    }

    .forgot-verify-inner {
        position: relative;
        z-index: 2;
        padding:
            32px
            30px
            30px;
    }

    .forgot-verify-icon {
        width: 50px;
        height: 50px;
        margin: 0 auto 14px;
        display: grid;
        place-items: center;
        border:
            1px solid
            rgba(244,238,31,.26);
        border-radius: 14px;
        color: var(--verify-yellow);
        background:
            rgba(244,238,31,.035);
        box-shadow:
            0 0 20px
            rgba(240,229,33,.07);
    }

    .forgot-verify-icon svg {
        width: 25px;
        height: 25px;
    }

    .forgot-verify-heading {
        margin: 0;
        text-align: center;
        font-size: 24px;
        line-height: 1;
        font-weight: 850;
        letter-spacing: -.035em;
    }

    .forgot-verify-heading strong {
        color: var(--verify-yellow);
        font-weight: 900;
    }

    .forgot-verify-description {
        max-width: 355px;
        margin: 10px auto 16px;
        color: var(--verify-muted);
        text-align: center;
        font-size: 10px;
        line-height: 1.58;
    }

    .forgot-verify-destination {
        width: fit-content;
        max-width: 100%;
        margin: 0 auto 18px;
        padding: 7px 10px;
        border:
            1px solid
            rgba(244,238,31,.14);
        border-radius: 9px;
        color: #d8d39c;
        background:
            rgba(244,238,31,.035);
        text-align: center;
        font-size: 8px;
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    */

    .forgot-verify-message {
        margin: 0 0 12px;
        padding: 10px 11px;
        border-radius: 10px;
        font-size: 9px;
        line-height: 1.5;
    }

    .forgot-verify-message.info {
        border:
            1px solid
            rgba(244,238,31,.16);
        color: #e8e082;
        background:
            rgba(244,238,31,.04);
    }

    .forgot-verify-message.success {
        border:
            1px solid
            rgba(39,244,143,.17);
        color: var(--verify-success);
        background:
            rgba(24,237,126,.045);
    }

    .forgot-verify-message.error {
        border:
            1px solid
            rgba(255,112,112,.20);
        color: #ffc1c1;
        background:
            rgba(255,66,66,.06);
    }

    .forgot-verify-message ul {
        margin: 6px 0 0 16px;
        padding: 0;
    }

    /*
    |--------------------------------------------------------------------------
    | OTP code inputs
    |--------------------------------------------------------------------------
    */

    .forgot-verify-code-label {
        display: block;
        margin: 0 0 8px;
        color: #a8a7ac;
        font-size: 8px;
        font-weight: 850;
        letter-spacing: .04em;
        text-align: center;
    }

    .forgot-verify-code-grid {
        display: grid;
        grid-template-columns:
            repeat(6, minmax(0, 1fr));
        gap: 7px;
        margin-bottom: 13px;
    }

    .forgot-verify-digit {
        width: 100%;
        aspect-ratio: 1 / 1.08;
        min-width: 0;
        border:
            1px solid
            rgba(255,255,255,.14);
        border-radius: 11px;
        outline: 0;
        color: #f8f8f8;
        background:
            rgba(0,0,0,.26);
        text-align: center;
        font: inherit;
        font-size:
            clamp(
                18px,
                5vw,
                24px
            );
        font-weight: 900;
        transition:
            border-color .16s ease,
            box-shadow .16s ease,
            background .16s ease,
            transform .16s ease;
    }

    .forgot-verify-digit:focus {
        border-color:
            rgba(244,238,31,.58);
        background:
            rgba(244,238,31,.035);
        box-shadow:
            0 0 0 3px
            rgba(244,238,31,.055);
        transform:
            translateY(-1px);
    }

    .forgot-verify-digit.filled {
        border-color:
            rgba(244,238,31,.25);
        color: var(--verify-yellow);
        background:
            rgba(244,238,31,.045);
    }

    .forgot-verify-code-error {
        margin:
            -2px
            0
            11px;
        color: var(--verify-danger);
        text-align: center;
        font-size: 8px;
        font-weight: 800;
    }

    .forgot-verify-primary {
        width: 100%;
        min-height: 44px;
        border: 0;
        border-radius: 10px;
        color: #171200;
        background:
            linear-gradient(
                180deg,
                #fbef39,
                #dbb90a
            );
        box-shadow:
            0 9px 24px
            rgba(242,197,0,.15),
            inset 0 1px 0
            rgba(255,255,255,.55);
        font: inherit;
        font-size: 9px;
        font-weight: 900;
        cursor: pointer;
        transition:
            transform .18s ease,
            opacity .18s ease;
    }

    .forgot-verify-primary:hover:not(:disabled) {
        transform:
            translateY(-1px);
    }

    .forgot-verify-primary:disabled {
        cursor: wait;
        opacity: .62;
    }

    /*
    |--------------------------------------------------------------------------
    | Resend / navigation
    |--------------------------------------------------------------------------
    */

    .forgot-verify-resend {
        margin-top: 13px;
        padding: 12px;
        border:
            1px solid
            rgba(255,255,255,.07);
        border-radius: 10px;
        background:
            rgba(0,0,0,.13);
        text-align: center;
    }

    .forgot-verify-resend-text {
        margin: 0 0 8px;
        color: #747379;
        font-size: 7.5px;
        line-height: 1.5;
    }

    .forgot-verify-secondary {
        min-height: 34px;
        padding: 0 12px;
        border:
            1px solid
            rgba(244,238,31,.20);
        border-radius: 9px;
        color: var(--verify-yellow);
        background:
            rgba(244,238,31,.045);
        font: inherit;
        font-size: 8px;
        font-weight: 850;
        cursor: pointer;
        transition:
            transform .18s ease,
            border-color .18s ease,
            background .18s ease;
    }

    .forgot-verify-secondary:hover:not(:disabled) {
        transform:
            translateY(-1px);
        border-color:
            rgba(244,238,31,.35);
        background:
            rgba(244,238,31,.07);
    }

    .forgot-verify-secondary:disabled {
        cursor: wait;
        opacity: .58;
    }

    .forgot-verify-note {
        margin: 13px 0 0;
        color: #69686e;
        text-align: center;
        font-size: 7.5px;
        line-height: 1.55;
    }

    .forgot-verify-actions {
        margin-top: 16px;
        padding-top: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-top:
            1px solid
            rgba(255,255,255,.07);
    }

    .forgot-verify-back {
        min-height: 34px;
        padding: 0 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border:
            1px solid
            rgba(255,255,255,.09);
        border-radius: 9px;
        color: #b5b4b9;
        background:
            rgba(255,255,255,.025);
        text-decoration: none;
        font-size: 8px;
        font-weight: 850;
        transition:
            color .18s ease,
            border-color .18s ease,
            transform .18s ease;
    }

    .forgot-verify-back:hover {
        color: var(--verify-yellow);
        border-color:
            rgba(244,238,31,.20);
        transform:
            translateY(-1px);
    }

    /*
    |--------------------------------------------------------------------------
    | Entry animation
    |--------------------------------------------------------------------------
    */

    .forgot-verify-title,
    .forgot-verify-card-shell {
        animation:
            forgotVerifyEnter
            .42s
            cubic-bezier(.18,.9,.22,1)
            both;
    }

    .forgot-verify-card-shell {
        animation-delay: .05s;
    }

    @keyframes forgotVerifyEnter {
        from {
            opacity: 0;
            transform:
                translate3d(0,12px,0)
                scale(.985);
        }

        to {
            opacity: 1;
            transform:
                translate3d(0,0,0)
                scale(1);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media (max-width: 540px) {
        .forgot-verify-stage {
            width: 100%;
            padding:
                46px
                12px
                54px;
        }

        .forgot-verify-title {
            margin-bottom: 36px;
        }

        .forgot-verify-inner {
            padding:
                28px
                19px
                26px;
        }

        .forgot-verify-code-grid {
            gap: 5px;
        }

        .forgot-verify-bg::before,
        .forgot-verify-bg::after {
            width: 125vw;
            height: 108px;
        }

        .forgot-verify-corner {
            width: 46px;
            height: 46px;
        }
    }

    @media (max-width: 360px) {
        .forgot-verify-code-grid {
            gap: 4px;
        }

        .forgot-verify-digit {
            border-radius: 9px;
            font-size: 18px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .forgot-verify-bg::before,
        .forgot-verify-bg::after,
        .forgot-verify-streak,
        .forgot-verify-card::before,
        .forgot-verify-title,
        .forgot-verify-card-shell {
            animation: none !important;
        }

        .forgot-verify-digit,
        .forgot-verify-primary,
        .forgot-verify-secondary,
        .forgot-verify-back {
            transition: none !important;
        }
    }
</style>
@endpush

@section('content')
<section class="forgot-verify-page">
    <div class="forgot-verify-bg" aria-hidden="true">
        <span class="forgot-verify-streak"></span>
    </div>

    <main class="forgot-verify-stage">
        <h1 class="forgot-verify-title">
            Verify Recovery Email
            <span>Mashal Studio</span>
        </h1>

        <div class="forgot-verify-card-shell">
            <div class="forgot-verify-card-border">
                <section
                    class="forgot-verify-card"
                    aria-labelledby="forgotVerifyHeading"
                >
                    <span
                        class="forgot-verify-corner top-left"
                        aria-hidden="true"
                    ></span>

                    <span
                        class="forgot-verify-corner bottom-right"
                        aria-hidden="true"
                    ></span>

                    <div class="forgot-verify-inner">
                        <div
                            class="forgot-verify-icon"
                            aria-hidden="true"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <rect
                                    x="4.5"
                                    y="6.5"
                                    width="15"
                                    height="11"
                                    rx="2"
                                ></rect>

                                <path
                                    d="m5.5 8 6.5 5 6.5-5"
                                ></path>

                                <path
                                    d="M17.5 15.5 19 17l2.5-3"
                                ></path>
                            </svg>
                        </div>

                        <h2
                            class="forgot-verify-heading"
                            id="forgotVerifyHeading"
                        >
                            Bevestig je
                            <strong>hersteladres</strong>
                        </h2>

                        <p class="forgot-verify-description">
                            Voer de 6-cijferige code in die naar je nieuwe herstel-e-mailadres is gestuurd.
                        </p>

                        @if (!empty($maskedRecoveryEmail))
                            <div class="forgot-verify-destination">
                                Code verstuurd naar
                                {{ $maskedRecoveryEmail }}
                            </div>
                        @endif

                        @if (session('status'))
                            <div
                                class="forgot-verify-message info"
                                role="status"
                            >
                                {{ session('status') }}
                            </div>
                        @endif

                        @if (session('success'))
                            <div
                                class="forgot-verify-message success"
                                role="status"
                            >
                                {{ session('success') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div
                                class="forgot-verify-message error"
                                role="alert"
                            >
                                <strong>
                                    Controleer de herstelcode.
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
                            method="POST"
                            action="{{ route('account.recovery-email.verify.submit') }}"
                            id="forgotVerifyForm"
                            autocomplete="one-time-code"
                        >
                            @csrf

                            <label
                                class="forgot-verify-code-label"
                                for="forgotVerifyDigit1"
                            >
                                6-cijferige verificatiecode
                            </label>

                            <input
                                type="hidden"
                                name="code"
                                id="forgotVerifyCode"
                                value="{{ old('code') }}"
                            >

                            <div
                                class="forgot-verify-code-grid"
                                id="forgotVerifyCodeGrid"
                                role="group"
                                aria-label="6-cijferige verificatiecode"
                            >
                                @for ($i = 1; $i <= 6; $i++)
                                    <input
                                        class="forgot-verify-digit"
                                        id="forgotVerifyDigit{{ $i }}"
                                        type="text"
                                        inputmode="numeric"
                                        pattern="[0-9]*"
                                        maxlength="1"
                                        autocomplete="{{ $i === 1 ? 'one-time-code' : 'off' }}"
                                        aria-label="Cijfer {{ $i }} van 6"
                                        data-code-index="{{ $i - 1 }}"
                                    >
                                @endfor
                            </div>

                            @error('code')
                                <div class="forgot-verify-code-error">
                                    {{ $message }}
                                </div>
                            @enderror

                            <button
                                class="forgot-verify-primary"
                                id="forgotVerifySubmit"
                                type="submit"
                            >
                                Code controleren →
                            </button>
                        </form>

                        <div class="forgot-verify-resend">
                            <p class="forgot-verify-resend-text">
                                Geen code ontvangen? Je kunt een nieuwe
                                herstelcode aanvragen.
                            </p>

                            <form
                                method="POST"
                                action="{{ route('account.recovery-email.resend') }}"
                                id="forgotVerifyResendForm"
                            >
                                @csrf

                                <button
                                    class="forgot-verify-secondary"
                                    id="forgotVerifyResend"
                                    type="submit"
                                >
                                    Nieuwe code sturen
                                </button>
                            </form>
                        </div>

                        <p class="forgot-verify-note">
                            De code is tijdelijk geldig. Na meerdere onjuiste pogingen moet de verificatie opnieuw worden gestart.
                        </p>

                        <div class="forgot-verify-actions">
                            <a
                                class="forgot-verify-back"
                                href="{{ route('account') . '#recovery' }}"
                            >
                                ← Terug naar account
                            </a>

                            <a
                                class="forgot-verify-back"
                                href="{{ route('account') }}"
                            >
                                Naar account
                            </a>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {
        const form =
            document.getElementById(
                'forgotVerifyForm'
            );

        const codeInput =
            document.getElementById(
                'forgotVerifyCode'
            );

        const submit =
            document.getElementById(
                'forgotVerifySubmit'
            );

        const digits =
            Array.from(
                document.querySelectorAll(
                    '.forgot-verify-digit'
                )
            );

        const resendForm =
            document.getElementById(
                'forgotVerifyResendForm'
            );

        const resendButton =
            document.getElementById(
                'forgotVerifyResend'
            );

        if (
            !form
            || !codeInput
            || !submit
            || digits.length !== 6
        ) {
            return;
        }

        function cleanCode(value) {
            return String(value || '')
                .replace(/\D/g, '')
                .slice(0, 6);
        }

        function syncHiddenCode() {
            const code =
                digits
                    .map(function (input) {
                        return cleanCode(
                            input.value
                        ).slice(0, 1);
                    })
                    .join('');

            codeInput.value = code;

            digits.forEach(
                function (input) {
                    input.classList.toggle(
                        'filled',
                        input.value !== ''
                    );
                }
            );

            return code;
        }

        function fillCode(value) {
            const code =
                cleanCode(value);

            digits.forEach(
                function (input, index) {
                    input.value =
                        code[index] || '';
                }
            );

            syncHiddenCode();

            const nextIndex =
                Math.min(
                    code.length,
                    digits.length - 1
                );

            digits[nextIndex].focus();

            if (code.length === 6) {
                digits[5].focus();
            }
        }

        function focusDigit(index) {
            if (
                index < 0
                || index >= digits.length
            ) {
                return;
            }

            digits[index].focus();
            digits[index].select();
        }

        digits.forEach(
            function (input, index) {
                input.addEventListener(
                    'input',
                    function () {
                        const value =
                            cleanCode(
                                input.value
                            );

                        if (value.length > 1) {
                            fillCode(value);
                            return;
                        }

                        input.value =
                            value.slice(0, 1);

                        syncHiddenCode();

                        if (
                            input.value !== ''
                            && index < digits.length - 1
                        ) {
                            focusDigit(
                                index + 1
                            );
                        }
                    }
                );

                input.addEventListener(
                    'keydown',
                    function (event) {
                        if (
                            event.key === 'Backspace'
                            && input.value === ''
                            && index > 0
                        ) {
                            event.preventDefault();

                            digits[index - 1].value = '';

                            syncHiddenCode();

                            focusDigit(
                                index - 1
                            );

                            return;
                        }

                        if (
                            event.key === 'ArrowLeft'
                            && index > 0
                        ) {
                            event.preventDefault();

                            focusDigit(
                                index - 1
                            );

                            return;
                        }

                        if (
                            event.key === 'ArrowRight'
                            && index < digits.length - 1
                        ) {
                            event.preventDefault();

                            focusDigit(
                                index + 1
                            );
                        }
                    }
                );

                input.addEventListener(
                    'paste',
                    function (event) {
                        const pasted =
                            event.clipboardData
                                ? event.clipboardData.getData(
                                    'text'
                                )
                                : '';

                        const code =
                            cleanCode(
                                pasted
                            );

                        if (!code) {
                            return;
                        }

                        event.preventDefault();

                        fillCode(code);
                    }
                );
            }
        );

        const initialCode =
            cleanCode(
                codeInput.value
            );

        if (initialCode) {
            fillCode(initialCode);
        } else {
            digits[0].focus();
        }

        form.addEventListener(
            'submit',
            function (event) {
                const code =
                    syncHiddenCode();

                if (code.length !== 6) {
                    event.preventDefault();

                    focusDigit(
                        Math.min(
                            code.length,
                            5
                        )
                    );

                    return;
                }

                submit.disabled = true;
                submit.textContent =
                    'Controleren…';
            }
        );

        if (
            resendForm
            && resendButton
        ) {
            resendForm.addEventListener(
                'submit',
                function () {
                    resendButton.disabled = true;
                    resendButton.textContent =
                        'Versturen…';
                }
            );
        }
    }
);
</script>
@endpush
