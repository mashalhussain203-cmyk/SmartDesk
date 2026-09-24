@auth
@php
    $serverAuthSuccess =
        session()->has('auth_success_animation');
@endphp

<div
    id="mashal-auth-success-overlay"
    class="mashal-auth-success-overlay{{ $serverAuthSuccess ? ' is-visible' : '' }}"
    @unless($serverAuthSuccess) hidden @endunless
    aria-live="assertive"
    aria-atomic="true"
>
    <div class="mashal-auth-success-panel">
        <div class="mashal-auth-success-shield" aria-hidden="true">
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <path d="M12 3l7 3v5c0 4.6-2.8 8-7 10-4.2-2-7-5.4-7-10V6l7-3z"></path>
                <path d="M9.5 12l1.7 1.7L15 10"></path>
            </svg>
        </div>

        <h2 class="mashal-auth-success-title">
            Verified <strong>Successfully</strong>
        </h2>

        <p class="mashal-auth-success-copy">
            Je beveiligde aanmelding is bevestigd. Mashal Studio wordt nu geopend.
        </p>

        <div class="mashal-auth-success-check" aria-hidden="true">
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

        <button
            class="mashal-auth-success-button"
            type="button"
            disabled
        >
            ✓&nbsp;&nbsp;Verified &amp; Secured
        </button>
    </div>
</div>

<style>
    .mashal-auth-success-overlay[hidden] {
        display: none !important;
    }

    .mashal-auth-success-overlay {
        position: fixed;
        inset: 0;
        z-index: 2147483000;
        display: grid;
        place-items: center;
        padding: 20px;
        overflow: hidden;
        color: #f7f7f8;
        background:
            radial-gradient(
                circle at 50% 44%,
                rgba(24,237,126,.13),
                transparent 23rem
            ),
            radial-gradient(
                circle at 50% 18%,
                rgba(255,181,0,.055),
                transparent 27rem
            ),
            rgba(2,2,2,.988);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        opacity: 0;
        transition: opacity .28s ease;
    }

    .mashal-auth-success-overlay::before,
    .mashal-auth-success-overlay::after {
        content: "";
        position: absolute;
        width: 1100px;
        height: 110px;
        border-radius: 50%;
        pointer-events: none;
        filter: blur(8px);
        opacity: .26;
        background:
            linear-gradient(
                180deg,
                transparent,
                rgba(255,198,54,.16),
                rgba(255,230,132,.54),
                rgba(255,122,0,.14),
                transparent
            );
    }

    .mashal-auth-success-overlay::before {
        top: 10%;
        left: 50%;
        transform: translateX(-60%) rotate(-24deg);
    }

    .mashal-auth-success-overlay::after {
        right: -36%;
        bottom: 11%;
        transform: rotate(28deg);
    }

    .mashal-auth-success-overlay.is-visible {
        opacity: 1;
    }

    .mashal-auth-success-overlay.is-leaving {
        opacity: 0;
    }

    .mashal-auth-success-panel {
        position: relative;
        width: min(100%, 410px);
        padding: 39px 30px 34px;
        overflow: hidden;
        text-align: center;
        border: 1px solid rgba(24,237,126,.25);
        border-radius: 28px;
        background:
            radial-gradient(
                circle at 50% 54%,
                rgba(24,237,126,.12),
                transparent 42%
            ),
            linear-gradient(
                145deg,
                rgba(255,255,255,.05),
                transparent 45%
            ),
            rgba(10,13,12,.84);
        box-shadow:
            0 35px 100px rgba(0,0,0,.76),
            0 0 58px rgba(24,237,126,.12);
        animation:
            mashalAuthPanelIn
            .62s
            cubic-bezier(.2,.88,.25,1.18)
            both;
    }

    .mashal-auth-success-shield {
        width: 52px;
        height: 52px;
        margin: 0 auto 17px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(24,237,126,.38);
        border-radius: 15px;
        color: #18ed7e;
        background: rgba(24,237,126,.07);
        box-shadow:
            0 0 28px rgba(24,237,126,.19);
    }

    .mashal-auth-success-shield svg {
        width: 26px;
        height: 26px;
    }

    .mashal-auth-success-title {
        margin: 0;
        font-size: clamp(27px, 6vw, 36px);
        line-height: 1.04;
        letter-spacing: -.04em;
    }

    .mashal-auth-success-title strong {
        color: #18ed7e;
        font-weight: 900;
    }

    .mashal-auth-success-copy {
        max-width: 330px;
        margin: 11px auto 0;
        color: #9b9ba0;
        font-size: 12px;
        line-height: 1.6;
    }

    .mashal-auth-success-check {
        width: 66px;
        height: 66px;
        margin: 29px auto 27px;
        display: grid;
        place-items: center;
        border: 3px solid #18ed7e;
        border-radius: 17px;
        color: #fff;
        background: rgba(5,11,8,.84);
        box-shadow:
            0 0 0 11px rgba(24,237,126,.028),
            0 0 45px rgba(24,237,126,.31);
        animation:
            mashalAuthCheckPop
            .62s
            .14s
            cubic-bezier(.2,.88,.25,1.3)
            both;
    }

    .mashal-auth-success-check svg {
        width: 36px;
        height: 36px;
    }

    .mashal-auth-success-button {
        width: min(100%, 250px);
        min-height: 45px;
        border: 0;
        border-radius: 11px;
        color: #fff;
        background:
            linear-gradient(
                180deg,
                #25fca0,
                #09d879
            );
        box-shadow:
            0 10px 32px rgba(0,235,130,.24),
            inset 0 1px 0 rgba(255,255,255,.51);
        font: inherit;
        font-size: 12px;
        font-weight: 900;
    }

    @keyframes mashalAuthPanelIn {
        from {
            opacity: 0;
            transform:
                translateY(13px)
                scale(.86);
            filter: blur(6px);
        }

        to {
            opacity: 1;
            transform:
                translateY(0)
                scale(1);
            filter: blur(0);
        }
    }

    @keyframes mashalAuthCheckPop {
        from {
            opacity: 0;
            transform:
                scale(.48)
                rotate(-12deg);
        }

        to {
            opacity: 1;
            transform:
                scale(1)
                rotate(0);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .mashal-auth-success-panel,
        .mashal-auth-success-check {
            animation-duration: .01ms !important;
            animation-delay: 0ms !important;
        }

        .mashal-auth-success-overlay {
            transition-duration: .01ms !important;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const overlay =
        document.getElementById(
            'mashal-auth-success-overlay'
        );

    if (!overlay) {
        return;
    }

    const serverSuccess =
        @json($serverAuthSuccess);

    let browserSuccess = false;

    /*
     * Fallback voor bestaande V2/V5 auth-links:
     * OAuth/e-mailcode/magic-link konden al een tijdelijke browserflag zetten.
     * De server-side Login-event flag is de primaire en betrouwbare trigger.
     */
    try {
        const raw =
            window.localStorage.getItem(
                'mashal_auth_success_pending'
            );

        if (raw) {
            const payload =
                JSON.parse(raw);

            const createdAt =
                Number(
                    payload?.createdAt
                    || 0
                );

            browserSuccess =
                createdAt > 0
                && (
                    Date.now()
                    - createdAt
                ) < (30 * 60 * 1000);
        }
    } catch (error) {
        browserSuccess = false;
    }

    if (
        !serverSuccess
        && !browserSuccess
    ) {
        return;
    }

    overlay.hidden = false;

    window.requestAnimationFrame(
        function () {
            overlay.classList.add(
                'is-visible'
            );
        }
    );

    window.setTimeout(
        function () {
            overlay.classList.add(
                'is-leaving'
            );

            window.setTimeout(
                function () {
                    overlay.hidden = true;

                    try {
                        window.localStorage.removeItem(
                            'mashal_auth_success_pending'
                        );
                    } catch (error) {
                        // Geen blokkade.
                    }
                },
                300
            );
        },
        1750
    );
});
</script>
@endauth
