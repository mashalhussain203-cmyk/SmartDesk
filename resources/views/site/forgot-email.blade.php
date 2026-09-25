@extends('layouts.site-layout')

@section('title', 'E-mailadres vergeten | Mashal Studio')

@section(
    'meta_description',
    'Vind veilig je Mashal Studio-account terug met je naam en herstel-e-mailadres.'
)

@push('styles')
<style>
    :root {
        --recovery-bg: #020202;
        --recovery-card: rgba(15,15,18,.94);
        --recovery-card-2: rgba(8,8,10,.97);
        --recovery-line: rgba(255,255,255,.12);
        --recovery-line-strong: rgba(255,224,100,.34);
        --recovery-text: #f7f7f8;
        --recovery-muted: #8f8e94;
        --recovery-yellow: #f4ee1f;
        --recovery-yellow-2: #d9b70b;
        --recovery-orange: #ff6b23;
        --recovery-danger: #ff9b9b;
    }

    .forgot-email-page,
    .forgot-email-page * {
        box-sizing: border-box;
    }

    .forgot-email-page {
        position: relative;
        isolation: isolate;
        min-height: calc(100dvh - 76px);
        overflow: hidden;
        display: flex;
        justify-content: center;
        color: var(--recovery-text);
        background:
            radial-gradient(
                circle at 50% 28%,
                rgba(255,177,0,.05),
                transparent 28rem
            ),
            var(--recovery-bg);
    }

    /*
    |--------------------------------------------------------------------------
    | Lightweight moving background
    |--------------------------------------------------------------------------
    */

    .forgot-email-bg {
        position: absolute;
        inset: 0;
        z-index: -4;
        overflow: hidden;
        pointer-events: none;
    }

    .forgot-email-bg::before,
    .forgot-email-bg::after {
        content: "";
        position: absolute;
        width: min(1000px, 110vw);
        height: 130px;
        border-radius: 999px;
        opacity: .25;
        will-change: transform, opacity;
    }

    .forgot-email-bg::before {
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
            forgotBgA 12s ease-in-out infinite alternate;
    }

    .forgot-email-bg::after {
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
            forgotBgB 15s ease-in-out infinite alternate;
    }

    .forgot-email-streak {
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
            forgotStreak 9s ease-in-out infinite alternate;
    }

    @keyframes forgotBgA {
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

    @keyframes forgotBgB {
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

    @keyframes forgotStreak {
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

    .forgot-email-stage {
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

    .forgot-email-title {
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

    .forgot-email-title span {
        display: block;
        margin-top: 8px;
        color: var(--recovery-orange);
    }

    /*
    |--------------------------------------------------------------------------
    | Card
    |--------------------------------------------------------------------------
    */

    .forgot-email-card-shell {
        position: relative;
        width: min(100%, 440px);
        border-radius: 25px;
        box-shadow:
            0 22px 42px
            rgba(0,0,0,.42);
    }

    .forgot-email-card-border {
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

    .forgot-email-card {
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
                var(--recovery-card),
                var(--recovery-card-2)
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Moving sweep inside card
    |--------------------------------------------------------------------------
    */

    .forgot-email-card::before {
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
            forgotCardSweep
            8s ease-in-out infinite;
        will-change: transform;
    }

    @keyframes forgotCardSweep {
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

    .forgot-email-corner {
        position: absolute;
        z-index: 3;
        width: 56px;
        height: 56px;
        pointer-events: none;
        opacity: .72;
    }

    .forgot-email-corner.top-left {
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

    .forgot-email-corner.bottom-right {
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

    .forgot-email-inner {
        position: relative;
        z-index: 2;
        padding:
            32px
            30px
            30px;
    }

    .forgot-email-icon {
        width: 50px;
        height: 50px;
        margin: 0 auto 14px;
        display: grid;
        place-items: center;
        border:
            1px solid
            rgba(244,238,31,.26);
        border-radius: 14px;
        color: var(--recovery-yellow);
        background:
            rgba(244,238,31,.035);
        box-shadow:
            0 0 20px
            rgba(240,229,33,.07);
    }

    .forgot-email-icon svg {
        width: 25px;
        height: 25px;
    }

    .forgot-email-heading {
        margin: 0;
        text-align: center;
        font-size: 24px;
        line-height: 1;
        font-weight: 850;
        letter-spacing: -.035em;
    }

    .forgot-email-heading strong {
        color: var(--recovery-yellow);
        font-weight: 900;
    }

    .forgot-email-description {
        max-width: 350px;
        margin: 10px auto 20px;
        color: var(--recovery-muted);
        text-align: center;
        font-size: 10px;
        line-height: 1.58;
    }

    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    */

    .forgot-email-message {
        margin: 0 0 12px;
        padding: 10px 11px;
        border-radius: 10px;
        font-size: 9px;
        line-height: 1.5;
    }

    .forgot-email-message.info {
        border:
            1px solid
            rgba(244,238,31,.16);
        color: #e8e082;
        background:
            rgba(244,238,31,.04);
    }

    .forgot-email-message.error {
        border:
            1px solid
            rgba(255,112,112,.20);
        color: #ffc1c1;
        background:
            rgba(255,66,66,.06);
    }

    .forgot-email-message ul {
        margin: 6px 0 0 16px;
        padding: 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Form
    |--------------------------------------------------------------------------
    */

    .forgot-email-field {
        margin-bottom: 12px;
    }

    .forgot-email-label-row {
        min-height: 17px;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .forgot-email-label-row label {
        color: #a8a7ac;
        font-size: 8px;
        font-weight: 850;
        letter-spacing: .04em;
    }

    .forgot-email-field-error {
        color: var(--recovery-danger);
        font-size: 7.5px;
        font-weight: 800;
    }

    .forgot-email-input {
        width: 100%;
        height: 44px;
        padding: 0 13px;
        border:
            1px solid
            rgba(255,255,255,.13);
        border-radius: 10px;
        outline: 0;
        color: #f4f4f5;
        background:
            rgba(0,0,0,.24);
        font: inherit;
        font-size: 10px;
        transition:
            border-color .18s ease,
            box-shadow .18s ease,
            background .18s ease;
    }

    .forgot-email-input:focus {
        border-color:
            rgba(244,238,31,.55);
        background:
            rgba(244,238,31,.025);
        box-shadow:
            0 0 0 3px
            rgba(244,238,31,.055);
    }

    .forgot-email-input::placeholder {
        color: #606066;
    }

    .forgot-email-primary {
        width: 100%;
        min-height: 44px;
        margin-top: 3px;
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

    .forgot-email-primary:hover:not(:disabled) {
        transform:
            translateY(-1px);
    }

    .forgot-email-primary:disabled {
        cursor: wait;
        opacity: .62;
    }

    .forgot-email-note {
        margin: 13px 0 0;
        color: #6f6e74;
        text-align: center;
        font-size: 7.5px;
        line-height: 1.55;
    }

    /*
    |--------------------------------------------------------------------------
    | Bottom actions
    |--------------------------------------------------------------------------
    */

    .forgot-email-actions {
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

    .forgot-email-back {
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

    .forgot-email-back:hover {
        color: var(--recovery-yellow);
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

    .forgot-email-title,
    .forgot-email-card-shell {
        animation:
            forgotEmailEnter
            .42s
            cubic-bezier(.18,.9,.22,1)
            both;
    }

    .forgot-email-card-shell {
        animation-delay: .05s;
    }

    @keyframes forgotEmailEnter {
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
        .forgot-email-stage {
            width: 100%;
            padding:
                46px
                12px
                54px;
        }

        .forgot-email-title {
            margin-bottom: 36px;
        }

        .forgot-email-inner {
            padding:
                28px
                19px
                26px;
        }

        .forgot-email-bg::before,
        .forgot-email-bg::after {
            width: 125vw;
            height: 108px;
        }

        .forgot-email-corner {
            width: 46px;
            height: 46px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .forgot-email-bg::before,
        .forgot-email-bg::after,
        .forgot-email-streak,
        .forgot-email-card::before,
        .forgot-email-title,
        .forgot-email-card-shell {
            animation: none !important;
        }

        .forgot-email-primary,
        .forgot-email-back,
        .forgot-email-input {
            transition: none !important;
        }
    }
</style>
@endpush

@section('content')
<section class="forgot-email-page">
    <div class="forgot-email-bg" aria-hidden="true">
        <span class="forgot-email-streak"></span>
    </div>

    <main class="forgot-email-stage">
        <h1 class="forgot-email-title">
            Account Recovery
            <span>Mashal Studio</span>
        </h1>

        <div class="forgot-email-card-shell">
            <div class="forgot-email-card-border">
                <section
                    class="forgot-email-card"
                    aria-labelledby="forgotEmailHeading"
                >
                    <span
                        class="forgot-email-corner top-left"
                        aria-hidden="true"
                    ></span>

                    <span
                        class="forgot-email-corner bottom-right"
                        aria-hidden="true"
                    ></span>

                    <div class="forgot-email-inner">
                        <div
                            class="forgot-email-icon"
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
                                <circle
                                    cx="12"
                                    cy="8"
                                    r="3.25"
                                ></circle>

                                <path
                                    d="M5.5 19c.8-3.5 3-5.3 6.5-5.3 1.45 0 2.7.3 3.7.9"
                                ></path>

                                <path
                                    d="M17.2 14.6v5.1"
                                ></path>

                                <path
                                    d="M14.65 17.15h5.1"
                                ></path>
                            </svg>
                        </div>

                        <h2
                            class="forgot-email-heading"
                            id="forgotEmailHeading"
                        >
                            E-mailadres
                            <strong>vergeten?</strong>
                        </h2>

                        <p class="forgot-email-description">
                            Vul je naam en herstel-e-mailadres in.
                            Als de gegevens overeenkomen met een account,
                            sturen we een 6-cijferige verificatiecode.
                        </p>

                        @if (session('status'))
                            <div
                                class="forgot-email-message info"
                                role="status"
                            >
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div
                                class="forgot-email-message error"
                                role="alert"
                            >
                                <strong>
                                    Controleer de ingevulde gegevens.
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
                            action="{{ route('email.forgot.identify') }}"
                            id="forgotEmailForm"
                        >
                            @csrf

                            <div class="forgot-email-field">
                                <div class="forgot-email-label-row">
                                    <label for="first_name">
                                        Voornaam
                                    </label>

                                    @error('first_name')
                                        <span class="forgot-email-field-error">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <input
                                    class="forgot-email-input"
                                    id="first_name"
                                    type="text"
                                    name="first_name"
                                    value="{{ old('first_name') }}"
                                    placeholder="Voornaam"
                                    autocomplete="given-name"
                                    maxlength="100"
                                    required
                                    autofocus
                                >
                            </div>

                            <div class="forgot-email-field">
                                <div class="forgot-email-label-row">
                                    <label for="last_name">
                                        Achternaam
                                    </label>

                                    @error('last_name')
                                        <span class="forgot-email-field-error">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <input
                                    class="forgot-email-input"
                                    id="last_name"
                                    type="text"
                                    name="last_name"
                                    value="{{ old('last_name') }}"
                                    placeholder="Achternaam"
                                    autocomplete="family-name"
                                    maxlength="100"
                                    required
                                >
                            </div>

                            <div class="forgot-email-field">
                                <div class="forgot-email-label-row">
                                    <label for="recovery_email">
                                        Herstel-e-mailadres
                                    </label>

                                    @error('recovery_email')
                                        <span class="forgot-email-field-error">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <input
                                    class="forgot-email-input"
                                    id="recovery_email"
                                    type="email"
                                    name="recovery_email"
                                    value="{{ old('recovery_email') }}"
                                    placeholder="herstel@example.com"
                                    autocomplete="email"
                                    inputmode="email"
                                    autocapitalize="none"
                                    spellcheck="false"
                                    maxlength="255"
                                    required
                                >
                            </div>

                            <button
                                class="forgot-email-primary"
                                id="forgotEmailSubmit"
                                type="submit"
                            >
                                Account zoeken →
                            </button>
                        </form>

                        <p class="forgot-email-note">
                            Uit veiligheid vertellen we vóór verificatie
                            niet of een account bestaat. Alleen na een juiste
                            herstelcode wordt het gekoppelde e-mailadres getoond.
                        </p>

                        <div class="forgot-email-actions">
                            <a
                                class="forgot-email-back"
                                href="{{ route('login') }}"
                            >
                                ← Terug naar inloggen
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
                'forgotEmailForm'
            );

        const submit =
            document.getElementById(
                'forgotEmailSubmit'
            );

        if (
            !form
            || !submit
        ) {
            return;
        }

        form.addEventListener(
            'submit',
            function () {
                if (!form.checkValidity()) {
                    return;
                }

                submit.disabled = true;
                submit.textContent =
                    'Controleren…';
            }
        );
    }
);
</script>
@endpush
