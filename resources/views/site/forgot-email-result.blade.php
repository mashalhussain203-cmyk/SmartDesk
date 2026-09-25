@extends('layouts.site-layout')

@section('title', 'Account gevonden | Mashal Studio')

@section(
    'meta_description',
    'Je Mashal Studio-account is veilig teruggevonden na succesvolle verificatie.'
)

@push('styles')
<style>
    :root {
        --result-bg: #020202;
        --result-card: rgba(15,15,18,.94);
        --result-card-2: rgba(8,8,10,.97);
        --result-text: #f7f7f8;
        --result-muted: #8f8e94;
        --result-yellow: #f4ee1f;
        --result-orange: #ff6b23;
        --result-success: #18ed7e;
    }

    .forgot-result-page,
    .forgot-result-page * {
        box-sizing: border-box;
    }

    .forgot-result-page {
        position: relative;
        isolation: isolate;
        min-height: calc(100dvh - 76px);
        overflow: hidden;
        display: flex;
        justify-content: center;
        color: var(--result-text);
        background:
            radial-gradient(
                circle at 50% 28%,
                rgba(255,177,0,.05),
                transparent 28rem
            ),
            var(--result-bg);
    }

    /*
    |--------------------------------------------------------------------------
    | Animated background
    |--------------------------------------------------------------------------
    */

    .forgot-result-bg {
        position: absolute;
        inset: 0;
        z-index: -4;
        overflow: hidden;
        pointer-events: none;
    }

    .forgot-result-bg::before,
    .forgot-result-bg::after {
        content: "";
        position: absolute;
        width: min(1000px, 110vw);
        height: 130px;
        border-radius: 999px;
        opacity: .25;
        will-change: transform, opacity;
    }

    .forgot-result-bg::before {
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
            forgotResultBgA
            12s ease-in-out infinite alternate;
    }

    .forgot-result-bg::after {
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
            forgotResultBgB
            15s ease-in-out infinite alternate;
    }

    .forgot-result-streak {
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
            forgotResultStreak
            9s ease-in-out infinite alternate;
    }

    @keyframes forgotResultBgA {
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

    @keyframes forgotResultBgB {
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

    @keyframes forgotResultStreak {
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

    .forgot-result-stage {
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

    .forgot-result-title {
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

    .forgot-result-title span {
        display: block;
        margin-top: 8px;
        color: var(--result-orange);
    }

    /*
    |--------------------------------------------------------------------------
    | Card
    |--------------------------------------------------------------------------
    */

    .forgot-result-card-shell {
        position: relative;
        width: min(100%, 440px);
        border-radius: 25px;
        box-shadow:
            0 22px 42px
            rgba(0,0,0,.42);
    }

    .forgot-result-card-border {
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

    .forgot-result-card {
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
                var(--result-card),
                var(--result-card-2)
            );
    }

    .forgot-result-card::before {
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
            forgotResultSweep
            8s ease-in-out infinite;
        will-change: transform;
    }

    @keyframes forgotResultSweep {
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

    .forgot-result-corner {
        position: absolute;
        z-index: 3;
        width: 56px;
        height: 56px;
        pointer-events: none;
        opacity: .72;
    }

    .forgot-result-corner.top-left {
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

    .forgot-result-corner.bottom-right {
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

    .forgot-result-inner {
        position: relative;
        z-index: 2;
        padding:
            32px
            30px
            30px;
    }

    /*
    |--------------------------------------------------------------------------
    | Success icon
    |--------------------------------------------------------------------------
    */

    .forgot-result-icon {
        position: relative;
        width: 56px;
        height: 56px;
        margin: 0 auto 15px;
        display: grid;
        place-items: center;
        border:
            1px solid
            rgba(24,237,126,.30);
        border-radius: 16px;
        color: var(--result-success);
        background:
            rgba(24,237,126,.045);
        box-shadow:
            0 0 24px
            rgba(24,237,126,.08);
    }

    .forgot-result-icon::after {
        content: "";
        position: absolute;
        inset: 6px;
        border:
            1px solid
            rgba(24,237,126,.10);
        border-radius: 11px;
    }

    .forgot-result-icon svg {
        width: 28px;
        height: 28px;
        position: relative;
        z-index: 1;
    }

    .forgot-result-heading {
        margin: 0;
        text-align: center;
        font-size: 24px;
        line-height: 1;
        font-weight: 850;
        letter-spacing: -.035em;
    }

    .forgot-result-heading strong {
        color: var(--result-yellow);
        font-weight: 900;
    }

    .forgot-result-description {
        max-width: 355px;
        margin: 10px auto 18px;
        color: var(--result-muted);
        text-align: center;
        font-size: 10px;
        line-height: 1.58;
    }

    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    */

    .forgot-result-message {
        margin: 0 0 13px;
        padding: 10px 11px;
        border-radius: 10px;
        font-size: 9px;
        line-height: 1.5;
    }

    .forgot-result-message.success {
        border:
            1px solid
            rgba(39,244,143,.17);
        color: #9bf5c7;
        background:
            rgba(24,237,126,.045);
    }

    /*
    |--------------------------------------------------------------------------
    | Recovered account
    |--------------------------------------------------------------------------
    */

    .forgot-result-account {
        margin: 0 0 15px;
        padding: 15px;
        border:
            1px solid
            rgba(244,238,31,.13);
        border-radius: 13px;
        background:
            linear-gradient(
                135deg,
                rgba(244,238,31,.035),
                rgba(255,255,255,.015)
            ),
            rgba(0,0,0,.18);
    }

    .forgot-result-account-label {
        margin: 0 0 8px;
        color: #77767c;
        text-align: center;
        font-size: 7px;
        font-weight: 900;
        letter-spacing: .10em;
        text-transform: uppercase;
    }

    .forgot-result-name {
        margin: 0 0 7px;
        color: #e4e4e6;
        text-align: center;
        font-size: 10px;
        font-weight: 800;
        line-height: 1.4;
    }

    .forgot-result-email {
        display: block;
        width: 100%;
        padding: 11px 12px;
        border:
            1px solid
            rgba(255,255,255,.09);
        border-radius: 10px;
        color: var(--result-yellow);
        background:
            rgba(0,0,0,.24);
        text-align: center;
        font-size: 12px;
        font-weight: 900;
        line-height: 1.4;
        overflow-wrap: anywhere;
        user-select: all;
    }

    .forgot-result-copy {
        width: 100%;
        min-height: 36px;
        margin-top: 8px;
        border:
            1px solid
            rgba(255,255,255,.09);
        border-radius: 9px;
        color: #c2c1c6;
        background:
            rgba(255,255,255,.025);
        font: inherit;
        font-size: 8px;
        font-weight: 850;
        cursor: pointer;
        transition:
            color .18s ease,
            border-color .18s ease,
            transform .18s ease;
    }

    .forgot-result-copy:hover {
        color: var(--result-yellow);
        border-color:
            rgba(244,238,31,.20);
        transform:
            translateY(-1px);
    }

    /*
    |--------------------------------------------------------------------------
    | Primary actions
    |--------------------------------------------------------------------------
    */

    .forgot-result-primary {
        width: 100%;
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
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
        text-decoration: none;
        font-size: 9px;
        font-weight: 900;
        transition:
            transform .18s ease,
            opacity .18s ease;
    }

    .forgot-result-primary:hover {
        transform:
            translateY(-1px);
    }

    .forgot-result-secondary-grid {
        margin-top: 9px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .forgot-result-secondary {
        min-height: 39px;
        padding: 0 10px;
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
        text-align: center;
        font-size: 8px;
        font-weight: 850;
        line-height: 1.35;
        transition:
            color .18s ease,
            border-color .18s ease,
            transform .18s ease;
    }

    .forgot-result-secondary:hover {
        color: var(--result-yellow);
        border-color:
            rgba(244,238,31,.20);
        transform:
            translateY(-1px);
    }

    .forgot-result-note {
        margin: 14px 0 0;
        color: #69686e;
        text-align: center;
        font-size: 7.5px;
        line-height: 1.55;
    }

    /*
    |--------------------------------------------------------------------------
    | Entry animation
    |--------------------------------------------------------------------------
    */

    .forgot-result-title,
    .forgot-result-card-shell {
        animation:
            forgotResultEnter
            .42s
            cubic-bezier(.18,.9,.22,1)
            both;
    }

    .forgot-result-card-shell {
        animation-delay: .05s;
    }

    @keyframes forgotResultEnter {
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
        .forgot-result-stage {
            width: 100%;
            padding:
                46px
                12px
                54px;
        }

        .forgot-result-title {
            margin-bottom: 36px;
        }

        .forgot-result-inner {
            padding:
                28px
                19px
                26px;
        }

        .forgot-result-secondary-grid {
            grid-template-columns: 1fr;
        }

        .forgot-result-bg::before,
        .forgot-result-bg::after {
            width: 125vw;
            height: 108px;
        }

        .forgot-result-corner {
            width: 46px;
            height: 46px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .forgot-result-bg::before,
        .forgot-result-bg::after,
        .forgot-result-streak,
        .forgot-result-card::before,
        .forgot-result-title,
        .forgot-result-card-shell {
            animation: none !important;
        }

        .forgot-result-primary,
        .forgot-result-secondary,
        .forgot-result-copy {
            transition: none !important;
        }
    }
</style>
@endpush

@section('content')
<section class="forgot-result-page">
    <div class="forgot-result-bg" aria-hidden="true">
        <span class="forgot-result-streak"></span>
    </div>

    <main class="forgot-result-stage">
        <h1 class="forgot-result-title">
            Account Found
            <span>Mashal Studio</span>
        </h1>

        <div class="forgot-result-card-shell">
            <div class="forgot-result-card-border">
                <section
                    class="forgot-result-card"
                    aria-labelledby="forgotResultHeading"
                >
                    <span
                        class="forgot-result-corner top-left"
                        aria-hidden="true"
                    ></span>

                    <span
                        class="forgot-result-corner bottom-right"
                        aria-hidden="true"
                    ></span>

                    <div class="forgot-result-inner">
                        <div
                            class="forgot-result-icon"
                            aria-hidden="true"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    d="m5 12 4 4L19 6"
                                ></path>
                            </svg>
                        </div>

                        <h2
                            class="forgot-result-heading"
                            id="forgotResultHeading"
                        >
                            Account
                            <strong>gevonden</strong>
                        </h2>

                        <p class="forgot-result-description">
                            Je herstelcode is succesvol gecontroleerd.
                            Hieronder staat het e-mailadres dat bij je
                            Mashal Studio-account hoort.
                        </p>

                        @if (session('success'))
                            <div
                                class="forgot-result-message success"
                                role="status"
                            >
                                {{ session('success') }}
                            </div>
                        @endif

                        <div class="forgot-result-account">
                            <p class="forgot-result-account-label">
                                Teruggevonden account
                            </p>

                            @if (!empty($recoveredName))
                                <p class="forgot-result-name">
                                    {{ $recoveredName }}
                                </p>
                            @endif

                            <span
                                class="forgot-result-email"
                                id="recoveredEmail"
                            >
                                {{ $recoveredEmail }}
                            </span>

                            <button
                                class="forgot-result-copy"
                                id="copyRecoveredEmail"
                                type="button"
                            >
                                E-mailadres kopiëren
                            </button>
                        </div>

                        <a
                            class="forgot-result-primary"
                            href="{{ route('login', ['email' => $recoveredEmail]) }}"
                        >
                            Inloggen met dit account →
                        </a>

                        <div class="forgot-result-secondary-grid">
                            <a
                                class="forgot-result-secondary"
                                href="{{ route('password.request') }}"
                            >
                                Wachtwoord vergeten?
                            </a>

                            <a
                                class="forgot-result-secondary"
                                href="{{ route('email.forgot') }}"
                            >
                                Ander account zoeken
                            </a>
                        </div>

                        <p class="forgot-result-note">
                            Deze recovery-uitkomst is tijdelijk beschikbaar.
                            Sluit deze pagina wanneer je klaar bent op een
                            gedeeld apparaat.
                        </p>
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
        const email =
            document.getElementById(
                'recoveredEmail'
            );

        const copyButton =
            document.getElementById(
                'copyRecoveredEmail'
            );

        if (
            !email
            || !copyButton
        ) {
            return;
        }

        copyButton.addEventListener(
            'click',
            async function () {
                const value =
                    email.textContent.trim();

                if (!value) {
                    return;
                }

                const originalText =
                    copyButton.textContent;

                try {
                    if (
                        navigator.clipboard
                        && window.isSecureContext
                    ) {
                        await navigator.clipboard.writeText(
                            value
                        );
                    } else {
                        const textarea =
                            document.createElement(
                                'textarea'
                            );

                        textarea.value =
                            value;

                        textarea.setAttribute(
                            'readonly',
                            ''
                        );

                        textarea.style.position =
                            'fixed';

                        textarea.style.opacity =
                            '0';

                        document.body.appendChild(
                            textarea
                        );

                        textarea.select();

                        document.execCommand(
                            'copy'
                        );

                        textarea.remove();
                    }

                    copyButton.textContent =
                        'Gekopieerd ✓';
                } catch (error) {
                    copyButton.textContent =
                        'Selecteer het adres hierboven';
                }

                window.setTimeout(
                    function () {
                        copyButton.textContent =
                            originalText;
                    },
                    1600
                );
            }
        );
    }
);
</script>
@endpush
