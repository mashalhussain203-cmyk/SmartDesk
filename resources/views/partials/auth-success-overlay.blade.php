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

        color: #f6f8fb;

        background:
            radial-gradient(
                circle at 50% 44%,
                rgba(122,108,255,.16),
                transparent 24rem
            ),
            radial-gradient(
                circle at 60% 18%,
                rgba(66,165,255,.10),
                transparent 30rem
            ),
            linear-gradient(
                180deg,
                rgba(5,6,9,.985),
                rgba(7,9,13,.992)
            );

        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);

        opacity: 0;

        transition:
            opacity .28s ease;
    }

    .mashal-auth-success-overlay::before,
    .mashal-auth-success-overlay::after {
        content: "";

        position: absolute;

        width: 1100px;
        height: 110px;

        border-radius: 50%;

        pointer-events: none;

        filter: blur(10px);

        opacity: .28;

        background:
            linear-gradient(
                180deg,
                transparent 0%,
                rgba(122,108,255,.14) 28%,
                rgba(151,143,255,.46) 48%,
                rgba(66,165,255,.22) 58%,
                transparent 78%
            );
    }

    .mashal-auth-success-overlay::before {
        top: 10%;
        left: 50%;

        transform:
            translateX(-60%)
            rotate(-24deg);
    }

    .mashal-auth-success-overlay::after {
        right: -36%;
        bottom: 11%;

        transform:
            rotate(28deg);
    }

    .mashal-auth-success-overlay.is-visible {
        opacity: 1;
    }

    .mashal-auth-success-overlay.is-leaving {
        opacity: 0;
    }

    .mashal-auth-success-panel {
        position: relative;
        isolation: isolate;

        width: min(100%, 410px);

        padding: 39px 30px 34px;

        overflow: hidden;

        text-align: center;

        border:
            1px solid rgba(122,108,255,.26);

        border-radius: 28px;

        background:
            radial-gradient(
                circle at 50% 20%,
                rgba(122,108,255,.13),
                transparent 19rem
            ),
            radial-gradient(
                circle at 85% 80%,
                rgba(66,165,255,.08),
                transparent 18rem
            ),
            linear-gradient(
                145deg,
                rgba(255,255,255,.055),
                rgba(255,255,255,.012) 45%
            ),
            rgba(10,12,18,.89);

        box-shadow:
            0 35px 100px rgba(0,0,0,.72),
            0 0 65px rgba(122,108,255,.12),
            inset 0 1px 0 rgba(255,255,255,.045);

        animation:
            mashalAuthPanelIn
            .62s
            cubic-bezier(.2,.88,.25,1.18)
            both;
    }

    .mashal-auth-success-panel::before {
        content: "";

        position: absolute;
        z-index: -1;

        width: 260px;
        height: 260px;

        left: -120px;
        top: -150px;

        border-radius: 50%;

        background:
            rgba(122,108,255,.18);

        filter:
            blur(70px);

        pointer-events: none;
    }

    .mashal-auth-success-panel::after {
        content: "";

        position: absolute;
        z-index: -1;

        width: 220px;
        height: 220px;

        right: -120px;
        bottom: -140px;

        border-radius: 50%;

        background:
            rgba(66,165,255,.13);

        filter:
            blur(65px);

        pointer-events: none;
    }

    .mashal-auth-success-shield {
        width: 52px;
        height: 52px;

        margin:
            0 auto 17px;

        display: grid;
        place-items: center;

        border:
            1px solid rgba(122,108,255,.42);

        border-radius: 15px;

        color:
            #a9a2ff;

        background:
            linear-gradient(
                145deg,
                rgba(122,108,255,.14),
                rgba(66,165,255,.045)
            );

        box-shadow:
            0 0 32px rgba(122,108,255,.18),
            inset 0 1px 0 rgba(255,255,255,.08);
    }

    .mashal-auth-success-shield svg {
        width: 26px;
        height: 26px;
    }

    .mashal-auth-success-title {
        margin: 0;

        color: #fff;

        font-size:
            clamp(27px, 6vw, 36px);

        line-height: 1.04;

        letter-spacing: -.04em;
    }

    .mashal-auth-success-title strong {
        color: transparent;

        background:
            linear-gradient(
                120deg,
                #c7c1ff 0%,
                #8d87ff 42%,
                #72b9ff 100%
            );

        -webkit-background-clip:
            text;

        background-clip:
            text;

        font-weight: 900;
    }

    .mashal-auth-success-copy {
        max-width: 330px;

        margin:
            11px auto 0;

        color:
            #8d96a5;

        font-size: 12px;

        line-height: 1.6;
    }

    .mashal-auth-success-check {
        width: 66px;
        height: 66px;

        margin:
            29px auto 27px;

        display: grid;
        place-items: center;

        border:
            2px solid rgba(122,108,255,.78);

        border-radius: 17px;

        color: #fff;

        background:
            linear-gradient(
                145deg,
                rgba(122,108,255,.22),
                rgba(66,165,255,.09)
            ),
            rgba(8,10,15,.94);

        box-shadow:
            0 0 0 11px rgba(122,108,255,.035),
            0 0 48px rgba(122,108,255,.25),
            inset 0 1px 0 rgba(255,255,255,.08);

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

        filter:
            drop-shadow(
                0 0 10px rgba(122,108,255,.42)
            );
    }

    .mashal-auth-success-button {
        position: relative;

        width:
            min(100%, 250px);

        min-height: 45px;

        overflow: hidden;

        border:
            1px solid rgba(122,108,255,.45);

        border-radius: 11px;

        color: #fff;

        background:
            linear-gradient(
                135deg,
                #7a6cff 0%,
                #5e7dff 48%,
                #42a5ff 100%
            );

        box-shadow:
            0 14px 36px rgba(81,70,214,.28),
            0 8px 24px rgba(66,165,255,.10),
            inset 0 1px 0 rgba(255,255,255,.24);

        font: inherit;

        font-size: 12px;
        font-weight: 900;
    }

    .mashal-auth-success-button::before {
        content: "";

        position: absolute;
        inset: 0;

        pointer-events: none;

        background:
            linear-gradient(
                115deg,
                transparent 12%,
                rgba(255,255,255,.19) 48%,
                transparent 80%
            );

        transform:
            translateX(-135%);

        animation:
            mashalVerifiedShine
            1.4s
            .35s
            ease
            both;
    }

    @keyframes mashalAuthPanelIn {
        from {
            opacity: 0;

            transform:
                translateY(13px)
                scale(.86);

            filter:
                blur(6px);
        }

        to {
            opacity: 1;

            transform:
                translateY(0)
                scale(1);

            filter:
                blur(0);
        }
    }

    @keyframes mashalAuthCheckPop {
        from {
            opacity: 0;

            transform:
                scale(.48)
                rotate(-12deg);
        }

        65% {
            transform:
                scale(1.08)
                rotate(3deg);
        }

        to {
            opacity: 1;

            transform:
                scale(1)
                rotate(0);
        }
    }

    @keyframes mashalVerifiedShine {
        0% {
            transform:
                translateX(-135%);
        }

        55%,
        100% {
            transform:
                translateX(135%);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .mashal-auth-success-panel,
        .mashal-auth-success-check,
        .mashal-auth-success-button::before {
            animation-duration:
                .01ms !important;

            animation-delay:
                0ms !important;
        }

        .mashal-auth-success-overlay {
            transition-duration:
                .01ms !important;
        }
    }
</style>