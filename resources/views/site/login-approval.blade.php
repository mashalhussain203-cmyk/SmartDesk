@extends('layouts.site-layout')

@section('title', 'Login bevestigen | Mashal Studio')

@section(
    'meta_description',
    'Bevestig een nieuwe Mashal Studio-login vanaf een apparaat waar je al bent ingelogd.'
)

@section('content')
<style>
    .approval-wait-page,
    .approval-wait-page * {
        box-sizing: border-box;
    }

    .approval-wait-page {
        --ap-bg: #030304;
        --ap-card: #101114;
        --ap-text: #f8f8f8;
        --ap-muted: #929399;
        --ap-yellow: #f4ee1f;
        --ap-orange: #ff6b23;
        --ap-danger: #ff9a9a;
        --ap-success: #9bf5c7;

        position: relative;
        isolation: isolate;
        min-height: calc(100dvh - 76px);
        padding: 58px 18px 90px;
        overflow: hidden;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        color: var(--ap-text);
        background:
            radial-gradient(circle at 18% 20%, rgba(255,107,35,.08), transparent 24rem),
            radial-gradient(circle at 82% 72%, rgba(244,238,31,.045), transparent 28rem),
            var(--ap-bg);
    }

    .approval-wait-bg {
        position: absolute;
        inset: 0;
        z-index: 0;
        pointer-events: none;
        overflow: hidden;
    }

    .approval-wait-bg::before,
    .approval-wait-bg::after {
        content: "";
        position: absolute;
        width: min(960px, 126vw);
        height: 110px;
        border-radius: 999px;
        opacity: .24;
        will-change: transform, opacity;
    }

    .approval-wait-bg::before {
        left: -40%;
        top: 17%;
        background: linear-gradient(
            180deg,
            transparent 0 28%,
            rgba(255,135,0,.07) 38%,
            rgba(255,204,69,.34) 48%,
            rgba(255,235,139,.58) 51%,
            rgba(255,112,0,.16) 59%,
            transparent 72%
        );
        transform: rotate(28deg);
        animation: approvalBgA 12s ease-in-out infinite alternate;
    }

    .approval-wait-bg::after {
        right: -45%;
        top: 59%;
        background: linear-gradient(
            180deg,
            transparent 0 28%,
            rgba(255,150,0,.05) 38%,
            rgba(255,214,87,.27) 49%,
            rgba(255,238,157,.48) 51%,
            rgba(255,112,0,.14) 59%,
            transparent 72%
        );
        transform: rotate(-27deg);
        animation: approvalBgB 15s ease-in-out infinite alternate;
    }

    @keyframes approvalBgA {
        from {
            transform: rotate(28deg) translate3d(-4%, -5px, 0);
            opacity: .17;
        }
        to {
            transform: rotate(23deg) translate3d(11%, 14px, 0);
            opacity: .30;
        }
    }

    @keyframes approvalBgB {
        from {
            transform: rotate(-27deg) translate3d(5%, 4px, 0);
            opacity: .14;
        }
        to {
            transform: rotate(-22deg) translate3d(-10%, -12px, 0);
            opacity: .27;
        }
    }

    .approval-wait-shell {
        position: relative;
        z-index: 2;
        width: min(100%, 480px);
    }

    .approval-wait-brand {
        text-align: center;
        margin-bottom: 28px;
    }

    .approval-wait-kicker {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        color: #d9bc63;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .17em;
        text-transform: uppercase;
    }

    .approval-wait-title {
        margin: 14px 0 0;
        font-size: clamp(38px, 10vw, 54px);
        line-height: .94;
        letter-spacing: -.06em;
        font-weight: 600;
        text-wrap: balance;
    }

    .approval-wait-title span {
        color: var(--ap-orange);
    }

    .approval-wait-card {
        position: relative;
        overflow: hidden;
        padding: clamp(25px, 6vw, 35px);
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 26px;
        background:
            linear-gradient(145deg, rgba(255,255,255,.045), transparent 32%),
            linear-gradient(180deg, #111216, #090a0c);
        box-shadow:
            0 28px 78px rgba(0,0,0,.42),
            inset 0 1px 0 rgba(255,255,255,.035);
    }

    .approval-wait-card::before {
        content: "";
        position: absolute;
        top: -110%;
        left: -35%;
        width: 42%;
        height: 310%;
        pointer-events: none;
        opacity: 0;
        background:
            linear-gradient(
                90deg,
                transparent,
                rgba(255,255,255,.38),
                transparent
            );
        transform: rotate(23deg);
        animation: approvalSweep 8s ease-in-out infinite;
    }

    @keyframes approvalSweep {
        0%, 62% {
            transform: translate3d(-140%, 0, 0) rotate(23deg);
            opacity: 0;
        }
        72% {
            opacity: .12;
        }
        100% {
            transform: translate3d(430%, 0, 0) rotate(23deg);
            opacity: 0;
        }
    }

    .approval-wait-card > * {
        position: relative;
        z-index: 1;
    }

    .approval-device-icon {
        width: 58px;
        height: 58px;
        display: grid;
        place-items: center;
        margin-bottom: 21px;
        border: 1px solid rgba(244,238,31,.20);
        border-radius: 17px;
        color: var(--ap-yellow);
        background: rgba(244,238,31,.045);
        font-size: 24px;
    }

    .approval-wait-heading {
        margin: 0;
        font-size: clamp(25px, 7vw, 33px);
        line-height: 1.04;
        letter-spacing: -.045em;
        font-weight: 850;
    }

    .approval-wait-copy {
        margin: 13px 0 0;
        color: var(--ap-muted);
        font-size: 12px;
        line-height: 1.75;
    }

    .approval-number-wrap {
        margin: 26px 0 20px;
        padding: 24px 18px;
        border: 1px solid rgba(244,238,31,.16);
        border-radius: 18px;
        text-align: center;
        background:
            radial-gradient(circle at 50% 50%, rgba(244,238,31,.07), transparent 75%),
            rgba(244,238,31,.025);
    }

    .approval-number-label {
        color: #989464;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .15em;
        text-transform: uppercase;
    }

    .approval-number {
        margin-top: 7px;
        color: var(--ap-yellow);
        font-size: clamp(62px, 20vw, 86px);
        line-height: .92;
        letter-spacing: -.055em;
        font-weight: 950;
    }

    .approval-wait-status {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 13px;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 12px;
        color: #b7b8bd;
        background: rgba(255,255,255,.025);
        font-size: 10px;
        line-height: 1.5;
    }

    .approval-wait-dot {
        width: 8px;
        height: 8px;
        flex: 0 0 auto;
        border-radius: 50%;
        background: var(--ap-yellow);
        box-shadow: 0 0 0 0 rgba(244,238,31,.18);
        animation: approvalPulse 1.8s ease-out infinite;
    }

    @keyframes approvalPulse {
        0% {
            box-shadow: 0 0 0 0 rgba(244,238,31,.19);
        }
        70% {
            box-shadow: 0 0 0 9px rgba(244,238,31,0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(244,238,31,0);
        }
    }

    .approval-wait-meta {
        margin-top: 13px;
        color: #696a70;
        font-size: 9px;
        line-height: 1.7;
    }

    .approval-wait-actions {
        margin-top: 20px;
        padding-top: 17px;
        border-top: 1px solid rgba(255,255,255,.055);
    }

    .approval-cancel {
        min-height: 38px;
        padding: 0 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255,255,255,.09);
        border-radius: 10px;
        color: #b7b8bc;
        background: rgba(255,255,255,.022);
        font-size: 9px;
        font-weight: 850;
        cursor: pointer;
    }

    .approval-final-message {
        display: none;
        margin-top: 14px;
        padding: 12px 13px;
        border-radius: 11px;
        font-size: 10px;
        line-height: 1.55;
    }

    .approval-final-message.success {
        display: block;
        border: 1px solid rgba(72,210,135,.18);
        color: var(--ap-success);
        background: rgba(38,146,89,.075);
    }

    .approval-final-message.error {
        display: block;
        border: 1px solid rgba(255,100,100,.18);
        color: var(--ap-danger);
        background: rgba(168,47,47,.08);
    }

    @media (max-width: 390px) {
        .approval-wait-page {
            padding: 40px 13px 74px;
        }

        .approval-wait-card {
            padding: 23px 18px;
            border-radius: 22px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .approval-wait-bg::before,
        .approval-wait-bg::after,
        .approval-wait-card::before,
        .approval-wait-dot {
            animation: none !important;
        }
    }
</style>

<section class="approval-wait-page">
    <div class="approval-wait-bg" aria-hidden="true"></div>

    <div class="approval-wait-shell">
        <header class="approval-wait-brand">
            <div class="approval-wait-kicker">
                Login approval
            </div>

            <h1 class="approval-wait-title">
                Bevestig op een
                <span>ingelogd apparaat</span>
            </h1>
        </header>

        <div class="approval-wait-card">
            <div class="approval-device-icon" aria-hidden="true">
                ✓
            </div>

            <h2 class="approval-wait-heading">
                Welk nummer zie je hier?
            </h2>

            <p class="approval-wait-copy">
                Open Mashal Studio op je tablet, telefoon of computer waar
                <strong>{{ $maskedEmail ?: 'dit account' }}</strong>
                al is ingelogd. Kies daar hetzelfde nummer als hieronder.
            </p>

            <div class="approval-number-wrap">
                <div class="approval-number-label">
                    Kies dit nummer
                </div>

                <div
                    class="approval-number"
                    aria-label="Goedkeuringsnummer {{ $approvalNumber }}"
                >
                    {{ $approvalNumber }}
                </div>
            </div>

            <div
                class="approval-wait-status"
                id="approvalWaitStatus"
                role="status"
            >
                <span
                    class="approval-wait-dot"
                    id="approvalWaitDot"
                    aria-hidden="true"
                ></span>

                <span id="approvalWaitText">
                    Wachten op goedkeuring…
                </span>
            </div>

            <div class="approval-wait-meta">
                Deze aanvraag verloopt automatisch.
                <span id="approvalCountdown"></span>
            </div>

            <div
                class="approval-final-message"
                id="approvalFinalMessage"
            ></div>

            <div class="approval-wait-actions">
                <form
                    method="POST"
                    action="{{ route('login.approval.cancel') }}"
                >
                    @csrf

                    <button
                        class="approval-cancel"
                        type="submit"
                    >
                        Login annuleren
                    </button>
                </form>
            </div>

            <form
                id="approvalCompleteForm"
                method="POST"
                action="{{ route('login.approval.complete') }}"
                hidden
            >
                @csrf
            </form>
        </div>
    </div>
</section>

<script>
(function () {
    const statusUrl =
        @json(route('login.approval.status'));

    const expiresAt =
        Number(
            @json($expiresAt)
        ) * 1000;

    let finished = false;
    let pollTimer = null;

    function byId(id) {
        return document.getElementById(id);
    }

    function setFinal(type, text) {
        const box = byId('approvalFinalMessage');

        if (!box) {
            return;
        }

        box.className =
            'approval-final-message ' + type;

        box.textContent = text;
    }

    function finishAsError(text) {
        finished = true;

        if (pollTimer) {
            window.clearTimeout(pollTimer);
        }

        const waitText =
            byId('approvalWaitText');

        const dot =
            byId('approvalWaitDot');

        if (waitText) {
            waitText.textContent =
                'Login niet goedgekeurd';
        }

        if (dot) {
            dot.style.display =
                'none';
        }

        setFinal(
            'error',
            text
        );
    }

    function completeLogin() {
        if (finished) {
            return;
        }

        finished = true;

        const waitText =
            byId('approvalWaitText');

        if (waitText) {
            waitText.textContent =
                'Goedgekeurd. Inloggen…';
        }

        setFinal(
            'success',
            'Je bestaande apparaat heeft deze login goedgekeurd.'
        );

        const form =
            byId('approvalCompleteForm');

        if (form) {
            form.submit();
        }
    }

    async function poll() {
        if (finished) {
            return;
        }

        try {
            const response =
                await fetch(
                    statusUrl,
                    {
                        method:
                            'GET',

                        headers: {
                            'Accept':
                                'application/json',
                        },

                        credentials:
                            'same-origin',

                        cache:
                            'no-store',
                    }
                );

            if (!response.ok) {
                throw new Error(
                    'status request failed'
                );
            }

            const data =
                await response.json();

            switch (data.status) {
                case 'approved':
                    completeLogin();
                    return;

                case 'rejected':
                    finishAsError(
                        'De loginpoging is op je andere apparaat geweigerd.'
                    );
                    return;

                case 'cancelled':
                    finishAsError(
                        'Deze loginpoging is geannuleerd.'
                    );
                    return;

                case 'expired':
                case 'consumed':
                    finishAsError(
                        'Deze loginbevestiging is verlopen. Log opnieuw in.'
                    );
                    return;

                default:
                    break;
            }
        } catch (error) {
            /*
             * Een tijdelijke netwerkfout mag de flow niet stoppen.
             * De volgende poll probeert het opnieuw.
             */
        }

        pollTimer =
            window.setTimeout(
                poll,
                2000
            );
    }

    function updateCountdown() {
        const node =
            byId('approvalCountdown');

        if (
            !node
            || finished
        ) {
            return;
        }

        const remaining =
            Math.max(
                0,
                Math.floor(
                    (expiresAt - Date.now())
                    / 1000
                )
            );

        const minutes =
            Math.floor(
                remaining / 60
            );

        const seconds =
            String(
                remaining % 60
            ).padStart(
                2,
                '0'
            );

        node.textContent =
            'Nog '
            + minutes
            + ':'
            + seconds
            + '.';

        if (remaining <= 0) {
            finishAsError(
                'Deze loginbevestiging is verlopen. Log opnieuw in.'
            );
        }
    }

    updateCountdown();
    window.setInterval(
        updateCountdown,
        1000
    );

    poll();
})();
</script>
@endsection
