@extends('layouts.site-layout')

@section('title', 'Mashal | E-mail verifiëren')

@push('styles')
<style>
    .verify-page {
        position: relative;
        min-height: calc(100vh - 78px);
        overflow: hidden;
        background: #08090b;
    }

    .verify-stage {
        min-height: calc(100vh - 78px);
        display: grid;
        grid-template-columns: minmax(0, 1.02fr) minmax(470px, .98fr);
    }

    /* ========================================================= */
    /* LEFT / CINEMATIC PANEL                                    */
    /* ========================================================= */

    .verify-visual {
        position: relative;
        min-height: 100%;
        overflow: hidden;
        isolation: isolate;
        display: flex;
        align-items: flex-end;
        padding: clamp(36px, 5vw, 74px);
        background: #0a0c0f;
    }

    .verify-visual::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -3;
        background:
            linear-gradient(
                180deg,
                rgba(4,5,7,.10),
                rgba(4,5,7,.22) 42%,
                rgba(4,5,7,.94) 100%
            ),
            linear-gradient(
                90deg,
                rgba(4,5,7,.40),
                transparent 58%
            ),
            url('https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=1900&q=90')
            center / cover no-repeat;
        transform: scale(1.03);
        animation: verifyVisualZoom 18s ease-in-out infinite alternate;
    }

    .verify-visual::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -2;
        background:
            radial-gradient(
                circle at 78% 20%,
                rgba(215,164,95,.18),
                transparent 20rem
            ),
            linear-gradient(
                180deg,
                transparent 70%,
                #08090b 100%
            );
        pointer-events: none;
    }

    @keyframes verifyVisualZoom {
        from { transform: scale(1.03); }
        to { transform: scale(1.09); }
    }

    .verify-visual-content {
        max-width: 720px;
        animation: verifyFadeUp .85s ease both;
    }

    .verify-kicker {
        display: inline-flex;
        align-items: center;
        gap: 11px;
        margin-bottom: 20px;
        color: #efc985;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .24em;
        text-transform: uppercase;
    }

    .verify-kicker::before {
        content: "";
        width: 36px;
        height: 1px;
        background: #d7a45f;
    }

    .verify-visual h2 {
        max-width: 720px;
        margin: 0;
        color: #ffffff;
        font-size: clamp(50px, 5.7vw, 86px);
        line-height: .92;
        letter-spacing: -.07em;
        font-weight: 950;
    }

    .verify-visual h2 span {
        color: #f0c983;
    }

    .verify-visual p {
        max-width: 590px;
        margin: 24px 0 0;
        color: rgba(255,255,255,.69);
        font-size: 14px;
        line-height: 1.9;
    }

    .verify-benefits {
        margin-top: 34px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        max-width: 690px;
    }

    .verify-benefit {
        padding: 14px;
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 16px;
        background: rgba(255,255,255,.045);
        backdrop-filter: blur(12px);
    }

    .verify-benefit small {
        display: block;
        margin-bottom: 5px;
        color: #c59558;
        font-size: 7px;
        font-weight: 900;
        letter-spacing: .13em;
        text-transform: uppercase;
    }

    .verify-benefit strong {
        display: block;
        color: rgba(255,255,255,.88);
        font-size: 11px;
        line-height: 1.5;
    }

    /* ========================================================= */
    /* RIGHT / VERIFICATION PANEL                                */
    /* ========================================================= */

    .verify-panel {
        position: relative;
        min-height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 52px 42px;
        background:
            radial-gradient(
                circle at 82% 14%,
                rgba(215,164,95,.08),
                transparent 18rem
            ),
            linear-gradient(
                180deg,
                #0b0d10,
                #08090b
            );
    }

    .verify-panel::before {
        content: "M";
        position: absolute;
        right: -25px;
        top: 11%;
        color: rgba(255,255,255,.016);
        font-size: 310px;
        font-weight: 950;
        line-height: .8;
        pointer-events: none;
        user-select: none;
    }

    .verify-shell {
        position: relative;
        z-index: 2;
        width: 100%;
        max-width: 560px;
        animation: verifyFadeUp .8s .08s ease both;
    }

    @keyframes verifyFadeUp {
        from {
            opacity: 0;
            transform: translateY(28px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .verify-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 32px;
    }

    .verify-brand-mark {
        width: 46px;
        height: 46px;
        display: grid;
        place-items: center;
        border-radius: 14px;
        background:
            linear-gradient(
                145deg,
                #f0ca86,
                #b67e3d
            );
        color: #15110c;
        font-size: 20px;
        font-weight: 950;
        box-shadow: 0 14px 34px rgba(215,164,95,.22);
    }

    .verify-brand-copy strong {
        display: block;
        color: #ffffff;
        font-size: 19px;
        line-height: 1;
        letter-spacing: -.03em;
    }

    .verify-brand-copy span {
        display: block;
        margin-top: 5px;
        color: #6f757c;
        font-size: 8px;
        font-weight: 850;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .verify-section-kicker {
        display: inline-block;
        margin-bottom: 10px;
        color: #b9894d;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .verify-title {
        margin: 0;
        color: #ffffff;
        font-size: clamp(38px, 4vw, 52px);
        line-height: 1;
        letter-spacing: -.055em;
        font-weight: 950;
    }

    .verify-subtitle {
        margin: 14px 0 28px;
        color: #7f858c;
        font-size: 13px;
        line-height: 1.8;
    }

    /* ========================================================= */
    /* MESSAGES                                                   */
    /* ========================================================= */

    .verify-message {
        margin-bottom: 20px;
        padding: 13px 15px;
        border-radius: 14px;
        font-size: 12px;
        line-height: 1.6;
        backdrop-filter: blur(12px);
    }

    .verify-message.success {
        border: 1px solid rgba(91,214,149,.22);
        background: rgba(91,214,149,.08);
        color: #a9efc8;
    }

    .verify-message.error {
        border: 1px solid rgba(241,123,123,.22);
        background: rgba(241,123,123,.08);
        color: #ffc1c1;
    }

    .verify-message ul {
        margin: 8px 0 0 18px;
        padding: 0;
    }

    /* ========================================================= */
    /* STEP CARDS                                                 */
    /* ========================================================= */

    .verify-steps {
        display: grid;
        gap: 14px;
    }

    .verify-step-card {
        position: relative;
        overflow: hidden;
        padding: 20px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 20px;
        background:
            linear-gradient(
                145deg,
                rgba(255,255,255,.042),
                rgba(255,255,255,.014)
            );
    }

    .verify-step-card::before {
        content: "";
        position: absolute;
        right: -50px;
        top: -50px;
        width: 140px;
        height: 140px;
        border-radius: 50%;
        background: rgba(215,164,95,.045);
        pointer-events: none;
    }

    .verify-step-head {
        display: flex;
        align-items: flex-start;
        gap: 13px;
        margin-bottom: 18px;
    }

    .verify-step-number {
        flex-shrink: 0;
        width: 38px;
        height: 38px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(215,164,95,.19);
        border-radius: 50%;
        background: rgba(215,164,95,.06);
        color: #e1b46d;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .07em;
    }

    .verify-step-copy strong {
        display: block;
        color: #ffffff;
        font-size: 15px;
        letter-spacing: -.02em;
    }

    .verify-step-copy p {
        margin: 5px 0 0;
        color: #747a81;
        font-size: 10px;
        line-height: 1.7;
    }

    /* ========================================================= */
    /* FIELDS                                                     */
    /* ========================================================= */

    .verify-field {
        margin-bottom: 15px;
    }

    .verify-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 8px;
    }

    .verify-label label {
        color: #b9b9b6;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .field-error {
        color: #f3a1a1;
        font-size: 10px;
        font-weight: 700;
    }

    .verify-input-wrap {
        position: relative;
    }

    .verify-input {
        width: 100%;
        height: 54px;
        padding: 0 46px 0 15px;
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 14px;
        outline: none;
        background: rgba(255,255,255,.035);
        color: #ffffff;
        font-size: 13px;
        transition:
            border-color .2s ease,
            background .2s ease,
            box-shadow .2s ease;
    }

    .verify-input::placeholder {
        color: #565c63;
    }

    .verify-input:focus {
        border-color: rgba(215,164,95,.46);
        background: rgba(215,164,95,.035);
        box-shadow: 0 0 0 4px rgba(215,164,95,.065);
    }

    .verify-input-icon {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6f757c;
        font-size: 12px;
        pointer-events: none;
    }

    .verify-code-input {
        height: 64px;
        padding: 0 16px;
        text-align: center;
        letter-spacing: .38em;
        font-size: 23px;
        font-weight: 850;
        font-variant-numeric: tabular-nums;
    }

    .verify-code-input::placeholder {
        letter-spacing: .28em;
    }

    .verify-code-hint {
        margin-top: 7px;
        color: #62686f;
        font-size: 9px;
        line-height: 1.6;
    }

    /* ========================================================= */
    /* BUTTONS                                                    */
    /* ========================================================= */

    .verify-action {
        width: 100%;
        min-height: 50px;
        border: 0;
        border-radius: 999px;
        background:
            linear-gradient(
                135deg,
                #f1cc8b,
                #ca914c
            );
        color: #14100b;
        font-size: 10px;
        font-weight: 950;
        letter-spacing: .03em;
        cursor: pointer;
        box-shadow: 0 14px 34px rgba(215,164,95,.18);
        transition:
            transform .2s ease,
            box-shadow .2s ease;
    }

    .verify-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 22px 48px rgba(215,164,95,.28);
    }

    /* ========================================================= */
    /* INFO / SECURITY                                            */
    /* ========================================================= */

    .verify-info-grid {
        margin-top: 16px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .verify-info {
        padding: 14px;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 15px;
        background: rgba(255,255,255,.024);
    }

    .verify-info.warning {
        border-color: rgba(242,198,109,.15);
        background: rgba(242,198,109,.045);
    }

    .verify-info small {
        display: block;
        margin-bottom: 5px;
        color: #9d7547;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .verify-info strong {
        display: block;
        color: #d7d5d0;
        font-size: 11px;
    }

    .verify-info p {
        margin: 5px 0 0;
        color: #6d737a;
        font-size: 9px;
        line-height: 1.7;
    }

    .verify-divider {
        margin: 26px 0 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        color: #4d5258;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .verify-divider::before,
    .verify-divider::after {
        content: "";
        flex: 1;
        height: 1px;
        background: rgba(255,255,255,.07);
    }

    .verify-footer-card {
        padding: 17px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 17px;
        background: rgba(255,255,255,.025);
    }

    .verify-footer-copy strong {
        display: block;
        color: #dcdad5;
        font-size: 11px;
    }

    .verify-footer-copy span {
        display: block;
        margin-top: 3px;
        color: #686e75;
        font-size: 9px;
        line-height: 1.5;
    }

    .verify-footer-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .verify-footer-link {
        min-height: 38px;
        padding: 0 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(215,164,95,.18);
        border-radius: 999px;
        background: rgba(215,164,95,.055);
        color: #dfb36d;
        text-decoration: none;
        font-size: 9px;
        font-weight: 900;
        transition:
            transform .2s ease,
            border-color .2s ease,
            background .2s ease;
    }

    .verify-footer-link.secondary {
        border-color: rgba(255,255,255,.09);
        background: rgba(255,255,255,.025);
        color: #a6a9ad;
    }

    .verify-footer-link:hover {
        transform: translateY(-1px);
        border-color: rgba(215,164,95,.34);
        background: rgba(215,164,95,.10);
    }

    .verify-security-note {
        margin-top: 17px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        color: #555b61;
        font-size: 9px;
        line-height: 1.6;
    }

    .verify-security-mark {
        flex-shrink: 0;
        width: 20px;
        height: 20px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 50%;
        color: #8b6a40;
        font-size: 9px;
    }

    /* ========================================================= */
    /* RESPONSIVE                                                 */
    /* ========================================================= */

    @media (max-width: 1080px) {
        .verify-stage {
            grid-template-columns: 1fr;
        }

        .verify-visual {
            min-height: 540px;
        }

        .verify-panel {
            min-height: auto;
            padding: 72px 32px;
        }
    }

    @media (max-width: 680px) {
        .verify-page,
        .verify-stage {
            min-height: auto;
        }

        .verify-visual {
            min-height: 460px;
            padding: 40px 20px;
        }

        .verify-visual h2 {
            font-size: clamp(46px, 14vw, 64px);
        }

        .verify-benefits,
        .verify-info-grid {
            grid-template-columns: 1fr;
        }

        .verify-panel {
            padding: 54px 18px 66px;
        }

        .verify-footer-card {
            align-items: flex-start;
            flex-direction: column;
        }

        .verify-footer-actions {
            width: 100%;
            justify-content: stretch;
        }

        .verify-footer-link {
            flex: 1;
        }
    }
</style>
@endpush


@section('content')

<section class="verify-page">

    <div class="verify-stage">

        {{-- ========================================================= --}}
        {{-- LEFT / EXPERIENCE                                         --}}
        {{-- ========================================================= --}}

        <div class="verify-visual">

            <div class="verify-visual-content">

                <span class="verify-kicker">
                    Mashal Identity Verification
                </span>

                <h2>
                    Bevestig wie je bent.
                    Ontgrendel
                    <span>alles.</span>
                </h2>

                <p>
                    Met e-mailverificatie beschermen we jouw account
                    en zorgen we dat bestellingen alleen vanuit
                    een bevestigd Mashal-profiel worden geplaatst.
                </p>


                <div class="verify-benefits">

                    <div class="verify-benefit">

                        <small>
                            Verified
                        </small>

                        <strong>
                            Bevestigde toegang tot je account
                        </strong>

                    </div>


                    <div class="verify-benefit">

                        <small>
                            Protected
                        </small>

                        <strong>
                            Extra beveiliging voor checkout
                        </strong>

                    </div>


                    <div class="verify-benefit">

                        <small>
                            Simple
                        </small>

                        <strong>
                            Bevestigen met een 6-cijferige code
                        </strong>

                    </div>

                </div>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- RIGHT / VERIFICATION                                      --}}
        {{-- ========================================================= --}}

        <div class="verify-panel">

            <div class="verify-shell">

                <div class="verify-brand">

                    <div class="verify-brand-mark">
                        M
                    </div>

                    <div class="verify-brand-copy">

                        <strong>
                            Mashal
                        </strong>

                        <span>
                            Automotive
                        </span>

                    </div>

                </div>


                <span class="verify-section-kicker">
                    Secure verification
                </span>

                <h1 class="verify-title">
                    E-mail verifiëren
                </h1>

                <p class="verify-subtitle">
                    Vraag een verificatiecode aan,
                    open de e-mail van Mashal
                    en bevestig daarna je adres met de 6-cijferige code.
                </p>


                {{-- ================================================= --}}
                {{-- MESSAGES                                          --}}
                {{-- ================================================= --}}

                @if (session('success'))

                    <div class="verify-message success">

                        <strong>
                            Gelukt.
                        </strong>

                        {{ session('success') }}

                    </div>

                @endif


                @if ($errors->any())

                    <div class="verify-message error">

                        <strong>
                            Verificatie is niet gelukt.
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


                {{-- ================================================= --}}
                {{-- TWO-STEP FLOW                                      --}}
                {{-- ================================================= --}}

                <div class="verify-steps">

                    {{-- STEP 1 --}}

                    <section class="verify-step-card">

                        <div class="verify-step-head">

                            <span class="verify-step-number">
                                01
                            </span>

                            <div class="verify-step-copy">

                                <strong>
                                    Vraag je verificatiecode aan
                                </strong>

                                <p>
                                    Vul het e-mailadres in
                                    dat je voor je Mashal-account gebruikt.
                                </p>

                            </div>

                        </div>


                        <form
                            method="POST"
                            action="{{ route('verification.send') }}"
                        >
                            @csrf


                            <div class="verify-field">

                                <div class="verify-label">

                                    <label for="verification_email_send">
                                        E-mailadres
                                    </label>

                                    @error('email')
                                        <span class="field-error">
                                            {{ $message }}
                                        </span>
                                    @enderror

                                </div>


                                <div class="verify-input-wrap">

                                    <input
                                        class="verify-input"
                                        id="verification_email_send"
                                        type="email"
                                        name="email"
                                        value="{{ old('email', auth()->check() ? auth()->user()->email : '') }}"
                                        placeholder="naam@example.com"
                                        required
                                        autocomplete="email"
                                    >

                                    <span class="verify-input-icon">
                                        @
                                    </span>

                                </div>

                            </div>


                            <button
                                class="verify-action"
                                type="submit"
                            >
                                Verstuur verificatiecode
                            </button>

                        </form>

                    </section>


                    {{-- STEP 2 --}}

                    <section class="verify-step-card">

                        <div class="verify-step-head">

                            <span class="verify-step-number">
                                02
                            </span>

                            <div class="verify-step-copy">

                                <strong>
                                    Bevestig de ontvangen code
                                </strong>

                                <p>
                                    Vul hetzelfde e-mailadres
                                    en de 6 cijfers uit de Mashal-e-mail in.
                                </p>

                            </div>

                        </div>


                        <form
                            method="POST"
                            action="{{ route('verification.verify') }}"
                        >
                            @csrf


                            <div class="verify-field">

                                <div class="verify-label">

                                    <label for="verification_email_verify">
                                        E-mailadres
                                    </label>

                                </div>


                                <div class="verify-input-wrap">

                                    <input
                                        class="verify-input"
                                        id="verification_email_verify"
                                        type="email"
                                        name="email"
                                        value="{{ old('email', auth()->check() ? auth()->user()->email : '') }}"
                                        placeholder="naam@example.com"
                                        required
                                        autocomplete="email"
                                    >

                                    <span class="verify-input-icon">
                                        @
                                    </span>

                                </div>

                            </div>


                            <div class="verify-field">

                                <div class="verify-label">

                                    <label for="code">
                                        Verificatiecode
                                    </label>

                                    @error('code')
                                        <span class="field-error">
                                            {{ $message }}
                                        </span>
                                    @enderror

                                </div>


                                <div class="verify-input-wrap">

                                    <input
                                        class="verify-input verify-code-input"
                                        id="code"
                                        type="text"
                                        name="code"
                                        value="{{ old('code') }}"
                                        placeholder="123456"
                                        required
                                        inputmode="numeric"
                                        pattern="[0-9]{6}"
                                        maxlength="6"
                                        autocomplete="one-time-code"
                                    >

                                </div>


                                <div class="verify-code-hint">
                                    Vul precies de 6 cijfers uit de e-mail in.
                                </div>

                            </div>


                            <button
                                class="verify-action"
                                type="submit"
                            >
                                Code controleren
                            </button>

                        </form>

                    </section>

                </div>


                {{-- ================================================= --}}
                {{-- INFO                                               --}}
                {{-- ================================================= --}}

                <div class="verify-info-grid">

                    <div class="verify-info warning">

                        <small>
                            Geldigheid
                        </small>

                        <strong>
                            15 minuten
                        </strong>

                        <p>
                            Is de code verlopen?
                            Vraag dan hierboven een nieuwe code aan.
                        </p>

                    </div>


                    <div class="verify-info">

                        <small>
                            Account security
                        </small>

                        <strong>
                            Deel je code nooit
                        </strong>

                        <p>
                            Mashal vraagt je nooit
                            om je code via chat of telefoon te delen.
                        </p>

                    </div>

                </div>


                <div class="verify-divider">
                    Terug naar je account
                </div>


                {{-- FOOTER ACTIONS --}}

                <div class="verify-footer-card">

                    <div class="verify-footer-copy">

                        <strong>
                            Klaar of later verder?
                        </strong>

                        <span>
                            Je kunt altijd terugkeren
                            om de verificatie af te ronden.
                        </span>

                    </div>


                    <div class="verify-footer-actions">

                        <a
                            class="verify-footer-link secondary"
                            href="{{ route('login') }}"
                        >
                            Inloggen
                        </a>

                        @auth

                            <a
                                class="verify-footer-link"
                                href="{{ route('account') }}"
                            >
                                Mijn account
                            </a>

                        @endauth

                    </div>

                </div>


                <div class="verify-security-note">

                    <span class="verify-security-mark">
                        ✓
                    </span>

                    <span>
                        Controleer altijd of de e-mail afkomstig is
                        van Mashal voordat je een verificatiecode gebruikt.
                    </span>

                </div>

            </div>

        </div>

    </div>

</section>

@endsection


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {

        const codeInput =
            document.getElementById('code');

        if (codeInput) {

            codeInput.addEventListener('input', function () {

                codeInput.value =
                    codeInput.value
                        .replace(/\D/g, '')
                        .slice(0, 6);

            });

        }

    });
</script>
@endpush
