@auth
<div
    id="mashal-auth-success-overlay"
    class="mashal-auth-success-overlay"
    hidden
    aria-live="assertive"
    aria-atomic="true"
>
    <div class="mashal-auth-success-card">
        <div class="mashal-auth-success-shield" aria-hidden="true">
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.4"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <path d="M5 12.5l4.2 4.2L19.5 6.8"></path>
            </svg>
        </div>

        <div class="mashal-auth-success-kicker">
            VERIFIED &amp; SECURED
        </div>

        <h2>
            Gelukt
        </h2>

        <p>
            Je bent veilig geverifieerd. Je Mashal Studio-workspace wordt geopend.
        </p>

        <div class="mashal-auth-success-loader" aria-hidden="true"></div>
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
        color: #f7f7f8;
        background:
            radial-gradient(circle at 50% 44%, rgba(24,237,126,.10), transparent 22rem),
            radial-gradient(circle at 50% 24%, rgba(255,194,0,.06), transparent 25rem),
            rgba(2,2,2,.985);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        opacity: 0;
        transition: opacity .22s ease;
    }

    .mashal-auth-success-overlay.is-visible {
        opacity: 1;
    }

    .mashal-auth-success-overlay.is-leaving {
        opacity: 0;
    }

    .mashal-auth-success-card {
        width: min(100%, 390px);
        padding: 42px 28px;
        text-align: center;
        border: 1px solid rgba(24,237,126,.22);
        border-radius: 26px;
        background:
            linear-gradient(145deg, rgba(24,237,126,.07), rgba(255,255,255,.018)),
            rgba(11,13,13,.84);
        box-shadow:
            0 30px 100px rgba(0,0,0,.72),
            0 0 55px rgba(24,237,126,.11);
        animation: mashalAuthSuccessPop .55s cubic-bezier(.2,.88,.25,1.2) both;
    }

    .mashal-auth-success-shield {
        width: 72px;
        height: 72px;
        margin: 0 auto 21px;
        display: grid;
        place-items: center;
        border: 3px solid #18ed7e;
        border-radius: 18px;
        color: #fff;
        background: rgba(5,10,7,.82);
        box-shadow:
            0 0 0 12px rgba(24,237,126,.025),
            0 0 46px rgba(24,237,126,.28);
    }

    .mashal-auth-success-shield svg {
        width: 39px;
        height: 39px;
    }

    .mashal-auth-success-kicker {
        margin-bottom: 8px;
        color: #18ed7e;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .16em;
    }

    .mashal-auth-success-card h2 {
        margin: 0;
        font-size: 37px;
        line-height: 1;
        letter-spacing: -.05em;
    }

    .mashal-auth-success-card p {
        max-width: 300px;
        margin: 12px auto 0;
        color: #9a9ba0;
        font-size: 11px;
        line-height: 1.65;
    }

    .mashal-auth-success-loader {
        width: 150px;
        height: 3px;
        margin: 25px auto 0;
        overflow: hidden;
        border-radius: 999px;
        background: rgba(255,255,255,.08);
    }

    .mashal-auth-success-loader::before {
        content: "";
        display: block;
        width: 100%;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg,#18ed7e,#8affbf);
        transform-origin: left;
        animation: mashalAuthProgress 1.45s linear both;
    }

    @keyframes mashalAuthSuccessPop {
        from {
            opacity: 0;
            transform: scale(.78) translateY(12px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    @keyframes mashalAuthProgress {
        from { transform: scaleX(0); }
        to { transform: scaleX(1); }
    }

    @media (prefers-reduced-motion: reduce) {
        .mashal-auth-success-card,
        .mashal-auth-success-loader::before {
            animation-duration: .01ms !important;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const key =
        'mashal_auth_success_pending';

    const overlay =
        document.getElementById(
            'mashal-auth-success-overlay'
        );

    if (!overlay) {
        return;
    }

    let payload = null;

    try {
        const raw =
            window.localStorage.getItem(key);

        if (!raw) {
            return;
        }

        payload =
            JSON.parse(raw);
    } catch (error) {
        try {
            window.localStorage.removeItem(key);
        } catch (storageError) {
            // Niets doen.
        }

        return;
    }

    const createdAt =
        Number(payload?.createdAt || 0);

    const fresh =
        createdAt > 0
        && (
            Date.now() - createdAt
        ) < (30 * 60 * 1000);

    if (!fresh) {
        try {
            window.localStorage.removeItem(key);
        } catch (error) {
            // Niets doen.
        }

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
                            key
                        );
                    } catch (error) {
                        // Niets doen.
                    }
                },
                260
            );
        },
        1550
    );
});
</script>
@endauth
