@extends('layouts.site-layout')

@section('title', 'Mashal | Wachtwoord herstellen')

@push('styles')
<style>
    .reset-page {
        position: relative;
        min-height: calc(100vh - 78px);
        overflow: hidden;
        background: #08090b;
    }

    .reset-stage {
        min-height: calc(100vh - 78px);
        display: grid;
        grid-template-columns: minmax(0, 1.02fr) minmax(430px, .98fr);
    }

    /* ========================================================= */
    /* LEFT / CINEMATIC PANEL                                    */
    /* ========================================================= */

    .reset-visual {
        position: relative;
        min-height: 100%;
        overflow: hidden;
        isolation: isolate;
        display: flex;
        align-items: flex-end;
        padding: clamp(34px, 5vw, 72px);
        background: #0a0c0f;
    }

    .reset-visual::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -3;
        background:
            linear-gradient(
                180deg,
                rgba(4,5,7,.10),
                rgba(4,5,7,.20) 44%,
                rgba(4,5,7,.94) 100%
            ),
            linear-gradient(
                90deg,
                rgba(4,5,7,.35),
                transparent 58%
            ),
            url('https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1800&q=88')
            center / cover no-repeat;
        transform: scale(1.03);
        animation: resetVisualZoom 18s ease-in-out infinite alternate;
    }

    .reset-visual::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -2;
        background:
            radial-gradient(
                circle at 75% 20%,
                rgba(215,164,95,.17),
                transparent 20rem
            ),
            linear-gradient(
                180deg,
                transparent 72%,
                #08090b 100%
            );
        pointer-events: none;
    }

    @keyframes resetVisualZoom {
        from { transform: scale(1.03); }
        to { transform: scale(1.09); }
    }

    .reset-visual-content {
        max-width: 690px;
        animation: resetFadeUp .85s ease both;
    }

    .reset-kicker {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
        color: #efc985;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .24em;
        text-transform: uppercase;
    }

    .reset-kicker::before {
        content: "";
        width: 34px;
        height: 1px;
        background: #d7a45f;
    }

    .reset-visual h2 {
        max-width: 700px;
        margin: 0;
        color: #ffffff;
        font-size: clamp(48px, 5.6vw, 82px);
        line-height: .94;
        letter-spacing: -.065em;
        font-weight: 950;
    }

    .reset-visual h2 span {
        color: #f0c983;
    }

    .reset-visual p {
        max-width: 560px;
        margin: 24px 0 0;
        color: rgba(255,255,255,.68);
        font-size: 14px;
        line-height: 1.85;
    }

    .reset-trust-row {
        margin-top: 32px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .reset-trust {
        padding: 9px 12px;
        border: 1px solid rgba(255,255,255,.13);
        border-radius: 999px;
        background: rgba(255,255,255,.05);
        backdrop-filter: blur(12px);
        color: rgba(255,255,255,.76);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    /* ========================================================= */
    /* RIGHT / RESET PANEL                                       */
    /* ========================================================= */

    .reset-panel {
        position: relative;
        min-height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 54px 42px;
        background:
            radial-gradient(
                circle at 80% 15%,
                rgba(215,164,95,.08),
                transparent 18rem
            ),
            linear-gradient(
                180deg,
                #0b0d10,
                #08090b
            );
    }

    .reset-panel::before {
        content: "M";
        position: absolute;
        right: -28px;
        top: 12%;
        color: rgba(255,255,255,.016);
        font-size: 300px;
        font-weight: 950;
        line-height: .8;
        pointer-events: none;
        user-select: none;
    }

    .reset-shell {
        position: relative;
        z-index: 2;
        width: 100%;
        max-width: 480px;
        animation: resetFadeUp .8s .08s ease both;
    }

    @keyframes resetFadeUp {
        from {
            opacity: 0;
            transform: translateY(28px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .reset-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 36px;
    }

    .reset-brand-mark {
        width: 44px;
        height: 44px;
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

    .reset-brand-copy strong {
        display: block;
        color: #ffffff;
        font-size: 19px;
        line-height: 1;
        letter-spacing: -.03em;
    }

    .reset-brand-copy span {
        display: block;
        margin-top: 5px;
        color: #6f757c;
        font-size: 8px;
        font-weight: 850;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .reset-section-kicker {
        display: inline-block;
        margin-bottom: 10px;
        color: #b9894d;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .reset-title {
        margin: 0;
        color: #ffffff;
        font-size: clamp(38px, 4vw, 52px);
        line-height: 1;
        letter-spacing: -.055em;
        font-weight: 950;
    }

    .reset-subtitle {
        margin: 14px 0 30px;
        color: #7f858c;
        font-size: 13px;
        line-height: 1.8;
    }

    /* ========================================================= */
    /* MESSAGES                                                   */
    /* ========================================================= */

    .reset-message {
        margin-bottom: 20px;
        padding: 13px 15px;
        border-radius: 14px;
        font-size: 12px;
        line-height: 1.6;
        backdrop-filter: blur(12px);
        animation: resetFadeUp .35s ease both;
    }

    .reset-message.success {
        border: 1px solid rgba(91,214,149,.22);
        background: rgba(91,214,149,.08);
        color: #a9efc8;
    }

    .reset-message.error {
        border: 1px solid rgba(241,123,123,.22);
        background: rgba(241,123,123,.08);
        color: #ffc1c1;
    }

    .reset-message ul {
        margin: 8px 0 0 18px;
        padding: 0;
    }

    /* ========================================================= */
    /* FORM                                                       */
    /* ========================================================= */

    .reset-field {
        margin-bottom: 20px;
    }

    .reset-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 8px;
    }

    .reset-label label {
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

    .reset-input-wrap {
        position: relative;
    }

    .reset-input {
        width: 100%;
        height: 56px;
        padding: 0 46px 0 16px;
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 15px;
        outline: none;
        background: rgba(255,255,255,.035);
        color: #ffffff;
        font-size: 14px;
        transition:
            border-color .2s ease,
            background .2s ease,
            box-shadow .2s ease;
    }

    .reset-input::placeholder {
        color: #565c63;
    }

    .reset-input:focus {
        border-color: rgba(215,164,95,.46);
        background: rgba(215,164,95,.035);
        box-shadow: 0 0 0 4px rgba(215,164,95,.065);
    }

    .reset-input-icon {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #747a81;
        font-size: 14px;
        pointer-events: none;
    }

    /* ========================================================= */
    /* INFO BOX                                                   */
    /* ========================================================= */

    .reset-info {
        margin: 4px 0 24px;
        padding: 16px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        border: 1px solid rgba(215,164,95,.13);
        border-radius: 15px;
        background:
            linear-gradient(
                145deg,
                rgba(215,164,95,.065),
                rgba(215,164,95,.02)
            );
    }

    .reset-info-icon {
        flex-shrink: 0;
        width: 28px;
        height: 28px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(215,164,95,.18);
        border-radius: 50%;
        color: #dfb36d;
        font-size: 11px;
        font-weight: 900;
    }

    .reset-info strong {
        display: block;
        margin-bottom: 4px;
        color: #d9d7d2;
        font-size: 11px;
    }

    .reset-info p {
        margin: 0;
        color: #747a81;
        font-size: 10px;
        line-height: 1.7;
    }

    /* ========================================================= */
    /* SUBMIT                                                     */
    /* ========================================================= */

    .reset-submit {
        position: relative;
        width: 100%;
        min-height: 56px;
        overflow: hidden;
        border: 0;
        border-radius: 999px;
        background:
            linear-gradient(
                135deg,
                #f1cc8b,
                #ca914c
            );
        color: #14100b;
        font-size: 12px;
        font-weight: 950;
        letter-spacing: .04em;
        cursor: pointer;
        box-shadow: 0 18px 44px rgba(215,164,95,.20);
        transition:
            transform .22s ease,
            box-shadow .22s ease;
    }

    .reset-submit::after {
        content: "→";
        position: absolute;
        right: 22px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 17px;
        transition: transform .22s ease;
    }

    .reset-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 25px 58px rgba(215,164,95,.30);
    }

    .reset-submit:hover::after {
        transform: translate(4px, -50%);
    }

    /* ========================================================= */
    /* SECURITY CARD                                              */
    /* ========================================================= */

    .security-card {
        margin-top: 28px;
        padding: 19px;
        display: flex;
        align-items: flex-start;
        gap: 13px;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 17px;
        background: rgba(255,255,255,.025);
    }

    .security-card-icon {
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(215,164,95,.16);
        border-radius: 11px;
        background: rgba(215,164,95,.055);
        color: #dfb36d;
        font-size: 13px;
        font-weight: 900;
    }

    .security-card h3 {
        margin: 0 0 5px;
        color: #dcdad5;
        font-size: 12px;
    }

    .security-card p {
        margin: 0;
        color: #666c73;
        font-size: 10px;
        line-height: 1.7;
    }

    /* ========================================================= */
    /* BACK LINK                                                  */
    /* ========================================================= */

    .reset-back {
        margin-top: 24px;
        text-align: center;
    }

    .reset-back a {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #9d7950;
        text-decoration: none;
        font-size: 10px;
        font-weight: 850;
        letter-spacing: .04em;
        transition: color .2s ease, transform .2s ease;
    }

    .reset-back a:hover {
        color: #efc985;
        transform: translateX(-2px);
    }

    /* ========================================================= */
    /* RESPONSIVE                                                 */
    /* ========================================================= */

    @media (max-width: 1000px) {
        .reset-stage {
            grid-template-columns: 1fr;
        }

        .reset-visual {
            min-height: 500px;
        }

        .reset-panel {
            min-height: auto;
            padding: 70px 32px;
        }
    }

    @media (max-width: 620px) {
        .reset-page,
        .reset-stage {
            min-height: auto;
        }

        .reset-visual {
            min-height: 420px;
            padding: 38px 20px;
        }

        .reset-visual h2 {
            font-size: clamp(44px, 14vw, 62px);
        }

        .reset-panel {
            padding: 52px 18px 64px;
        }

        .reset-brand {
            margin-bottom: 30px;
        }
    }
</style>
@endpush


@section('content')

<section class="reset-page">

    <div class="reset-stage">

        {{-- ========================================================= --}}
        {{-- LEFT / BRAND EXPERIENCE                                    --}}
        {{-- ========================================================= --}}

        <div class="reset-visual">

            <div class="reset-visual-content">

                <span class="reset-kicker">
                    Mashal account security
                </span>

                <h2>
                    Herstel toegang.
                    Behoud
                    <span>controle.</span>
                </h2>

                <p>
                    Geen paniek als je je wachtwoord bent vergeten.
                    Vraag veilig een resetlink aan en stel daarna
                    een nieuw wachtwoord in voor jouw Mashal-account.
                </p>


                <div class="reset-trust-row">

                    <span class="reset-trust">
                        Secure reset
                    </span>

                    <span class="reset-trust">
                        60 min geldig
                    </span>

                    <span class="reset-trust">
                        Private account
                    </span>

                </div>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- RIGHT / RESET FORM                                        --}}
        {{-- ========================================================= --}}

        <div class="reset-panel">

            <div class="reset-shell">

                <div class="reset-brand">

                    <div class="reset-brand-mark">
                        M
                    </div>

                    <div class="reset-brand-copy">

                        <strong>
                            Mashal
                        </strong>

                        <span>
                            Automotive
                        </span>

                    </div>

                </div>


                <span class="reset-section-kicker">
                    Password recovery
                </span>

                <h1 class="reset-title">
                    Wachtwoord herstellen
                </h1>

                <p class="reset-subtitle">
                    Vul het e-mailadres van je Mashal-account in.
                    Als het adres bij een account hoort,
                    ontvang je een beveiligde resetlink.
                </p>


                {{-- SUCCESS --}}

                @if (session('success'))

                    <div class="reset-message success">

                        <strong>
                            Gelukt.
                        </strong>

                        {{ session('success') }}

                    </div>

                @endif


                {{-- ERRORS --}}

                @if ($errors->any())

                    <div class="reset-message error">

                        <strong>
                            Er ging iets mis.
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


                {{-- FORM --}}

                <form
                    method="POST"
                    action="{{ route('password.email') }}"
                >
                    @csrf


                    <div class="reset-field">

                        <div class="reset-label">

                            <label for="email">
                                E-mailadres
                            </label>

                            @error('email')
                                <span class="field-error">
                                    {{ $message }}
                                </span>
                            @enderror

                        </div>


                        <div class="reset-input-wrap">

                            <input
                                class="reset-input"
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="naam@example.com"
                                autocomplete="email"
                                required
                                autofocus
                            >

                            <span class="reset-input-icon">
                                @
                            </span>

                        </div>

                    </div>


                    {{-- INFO --}}

                    <div class="reset-info">

                        <span class="reset-info-icon">
                            i
                        </span>

                        <div>

                            <strong>
                                Hoe werkt het?
                            </strong>

                            <p>
                                Als dit e-mailadres bij een Mashal-account hoort,
                                sturen we een resetlink.
                                Deze link is 60 minuten geldig.
                            </p>

                        </div>

                    </div>


                    <button
                        class="reset-submit"
                        type="submit"
                    >
                        Verstuur resetlink
                    </button>

                </form>


                {{-- SECURITY --}}

                <div class="security-card">

                    <div class="security-card-icon">
                        ✓
                    </div>

                    <div>

                        <h3>
                            Jouw veiligheid staat voorop
                        </h3>

                        <p>
                            Mashal vraagt je nooit om je wachtwoord
                            of resetlink via e-mail, chat of telefoon
                            met iemand te delen.
                        </p>

                    </div>

                </div>


                {{-- BACK --}}

                <div class="reset-back">

                    <a href="{{ route('login') }}">
                        <span aria-hidden="true">←</span>
                        Terug naar inloggen
                    </a>

                </div>

            </div>

        </div>

    </div>

</section>

@endsection
