@extends('layouts.site-layout')

@section('title', 'Mashal | TikTok-account afronden')

@push('styles')
<style>
    .tiktok-complete-page {
        position: relative;
        min-height: calc(100vh - 78px);
        overflow: hidden;
        background: #08090b;
    }

    .tiktok-complete-stage {
        min-height: calc(100vh - 78px);
        display: grid;
        grid-template-columns: minmax(0, 1.05fr) minmax(430px, .95fr);
    }

    /* ========================================================= */
    /* LEFT / BRAND EXPERIENCE                                   */
    /* ========================================================= */

    .tiktok-complete-visual {
        position: relative;
        min-height: 100%;
        overflow: hidden;
        isolation: isolate;
        display: flex;
        align-items: flex-end;
        padding: clamp(34px, 5vw, 72px);
        background: #0a0c0f;
    }

    .tiktok-complete-visual::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -3;
        background:
            linear-gradient(
                180deg,
                rgba(5,6,8,.10),
                rgba(5,6,8,.20) 42%,
                rgba(5,6,8,.94) 100%
            ),
            linear-gradient(
                90deg,
                rgba(5,6,8,.40),
                transparent 58%
            ),
            url('https://images.unsplash.com/photo-1503736334956-4c8f8e92946d?auto=format&fit=crop&w=1800&q=88')
            center / cover no-repeat;
        transform: scale(1.03);
        animation: tiktokCompleteVisualZoom 18s ease-in-out infinite alternate;
    }

    .tiktok-complete-visual::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -2;
        background:
            radial-gradient(
                circle at 76% 18%,
                rgba(37,244,238,.10),
                transparent 18rem
            ),
            radial-gradient(
                circle at 85% 28%,
                rgba(254,44,85,.10),
                transparent 20rem
            ),
            linear-gradient(
                180deg,
                transparent 70%,
                #08090b 100%
            );
        pointer-events: none;
    }

    @keyframes tiktokCompleteVisualZoom {
        from {
            transform: scale(1.03);
        }

        to {
            transform: scale(1.09);
        }
    }

    .tiktok-complete-visual-content {
        max-width: 690px;
        animation: tiktokCompleteFadeUp .85s ease both;
    }

    .tiktok-complete-brand-kicker {
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

    .tiktok-complete-brand-kicker::before {
        content: "";
        width: 34px;
        height: 1px;
        background: #d7a45f;
    }

    .tiktok-complete-visual h2 {
        max-width: 690px;
        margin: 0;
        color: #ffffff;
        font-size: clamp(50px, 5.8vw, 86px);
        line-height: .92;
        letter-spacing: -.068em;
        font-weight: 950;
    }

    .tiktok-complete-visual h2 span {
        color: #f0c983;
    }

    .tiktok-complete-visual p {
        max-width: 560px;
        margin: 24px 0 0;
        color: rgba(255,255,255,.68);
        font-size: 14px;
        line-height: 1.85;
    }

    .tiktok-complete-trust-row {
        margin-top: 32px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .tiktok-complete-trust {
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
    /* RIGHT / FORM PANEL                                        */
    /* ========================================================= */

    .tiktok-complete-panel {
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
            radial-gradient(
                circle at 14% 20%,
                rgba(37,244,238,.035),
                transparent 14rem
            ),
            linear-gradient(
                180deg,
                #0b0d10,
                #08090b
            );
    }

    .tiktok-complete-panel::before {
        content: "M";
        position: absolute;
        right: -24px;
        top: 12%;
        color: rgba(255,255,255,.016);
        font-size: 300px;
        font-weight: 950;
        line-height: .8;
        pointer-events: none;
        user-select: none;
    }

    .tiktok-complete-shell {
        position: relative;
        z-index: 2;
        width: 100%;
        max-width: 470px;
        animation: tiktokCompleteFadeUp .8s .08s ease both;
    }

    @keyframes tiktokCompleteFadeUp {
        from {
            opacity: 0;
            transform: translateY(28px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .tiktok-complete-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 34px;
    }

    .tiktok-complete-brand-mark {
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

    .tiktok-complete-brand-copy strong {
        display: block;
        color: #ffffff;
        font-size: 19px;
        line-height: 1;
        letter-spacing: -.03em;
    }

    .tiktok-complete-brand-copy span {
        display: block;
        margin-top: 5px;
        color: #6f757c;
        font-size: 8px;
        font-weight: 850;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .tiktok-complete-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
        color: #b9894d;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .tiktok-complete-kicker::before {
        content: "♪";
        width: 22px;
        height: 22px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: #050607;
        color: #ffffff;
        box-shadow:
            -2px 0 0 rgba(37,244,238,.80),
            2px 0 0 rgba(254,44,85,.75);
        font-size: 11px;
        letter-spacing: 0;
    }

    .tiktok-complete-title {
        margin: 0;
        color: #ffffff;
        font-size: clamp(38px, 4vw, 52px);
        line-height: 1;
        letter-spacing: -.055em;
        font-weight: 950;
    }

    .tiktok-complete-subtitle {
        margin: 14px 0 26px;
        color: #7f858c;
        font-size: 13px;
        line-height: 1.8;
    }

    /* ========================================================= */
    /* TIKTOK PROFILE CARD                                       */
    /* ========================================================= */

    .tiktok-profile-card {
        margin-bottom: 22px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        border: 1px solid rgba(255,255,255,.09);
        border-radius: 18px;
        background:
            radial-gradient(circle at 0 0, rgba(37,244,238,.05), transparent 34%),
            radial-gradient(circle at 100% 100%, rgba(254,44,85,.05), transparent 34%),
            rgba(255,255,255,.025);
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,.035),
            0 16px 36px rgba(0,0,0,.12);
    }

    .tiktok-profile-avatar {
        flex-shrink: 0;
        width: 58px;
        height: 58px;
        display: grid;
        place-items: center;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 50%;
        background: #111318;
        color: #ffffff;
        font-size: 18px;
        font-weight: 950;
        box-shadow:
            -2px 0 0 rgba(37,244,238,.72),
            2px 0 0 rgba(254,44,85,.68),
            0 10px 28px rgba(0,0,0,.24);
    }

    .tiktok-profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .tiktok-profile-copy {
        min-width: 0;
        flex: 1;
    }

    .tiktok-profile-copy small {
        display: block;
        margin-bottom: 4px;
        color: #696f76;
        font-size: 8px;
        font-weight: 850;
        letter-spacing: .13em;
        text-transform: uppercase;
    }

    .tiktok-profile-copy strong {
        display: block;
        overflow: hidden;
        color: #f4f1eb;
        font-size: 14px;
        font-weight: 950;
        line-height: 1.35;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .tiktok-profile-badge {
        flex-shrink: 0;
        padding: 7px 9px;
        border: 1px solid rgba(255,255,255,.09);
        border-radius: 999px;
        background: rgba(255,255,255,.035);
        color: #a0a5ab;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    /* ========================================================= */
    /* MESSAGES                                                  */
    /* ========================================================= */

    .tiktok-complete-message {
        margin-bottom: 18px;
        padding: 13px 15px;
        border-radius: 14px;
        font-size: 12px;
        line-height: 1.6;
        backdrop-filter: blur(12px);
    }

    .tiktok-complete-message.error {
        border: 1px solid rgba(241,123,123,.22);
        background: rgba(241,123,123,.08);
        color: #ffc1c1;
    }

    .tiktok-complete-message.success {
        border: 1px solid rgba(91,214,149,.22);
        background: rgba(91,214,149,.08);
        color: #a9efc8;
    }

    .tiktok-complete-message ul {
        margin: 8px 0 0 18px;
        padding: 0;
    }

    /* ========================================================= */
    /* FORM                                                      */
    /* ========================================================= */

    .tiktok-complete-field {
        margin-bottom: 18px;
    }

    .tiktok-complete-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 8px;
    }

    .tiktok-complete-label label {
        color: #b9b9b6;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .tiktok-complete-field-error {
        color: #f3a1a1;
        font-size: 10px;
        font-weight: 700;
    }

    .tiktok-complete-input-wrap {
        position: relative;
    }

    .tiktok-complete-input {
        width: 100%;
        height: 56px;
        padding: 0 48px 0 16px;
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 15px;
        outline: none;
        background: rgba(255,255,255,.035);
        color: #ffffff;
        font-size: 14px;
        transition:
            border-color .2s ease,
            background .2s ease,
            box-shadow .2s ease,
            transform .2s ease;
    }

    .tiktok-complete-input::placeholder {
        color: #565c63;
    }

    .tiktok-complete-input:focus {
        border-color: rgba(215,164,95,.46);
        background: rgba(215,164,95,.035);
        box-shadow: 0 0 0 4px rgba(215,164,95,.065);
    }

    .tiktok-complete-input-icon {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #747a81;
        font-size: 14px;
        pointer-events: none;
    }

    .tiktok-complete-help {
        margin: 9px 2px 0;
        color: #5d636a;
        font-size: 9px;
        line-height: 1.65;
    }

    .tiktok-complete-help strong {
        color: #8d9399;
        font-weight: 850;
    }

    /* ========================================================= */
    /* SUBMIT                                                    */
    /* ========================================================= */

    .tiktok-complete-submit {
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

    .tiktok-complete-submit::after {
        content: "→";
        position: absolute;
        right: 22px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 17px;
        transition: transform .22s ease;
    }

    .tiktok-complete-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 25px 58px rgba(215,164,95,.30);
    }

    .tiktok-complete-submit:hover::after {
        transform: translate(4px, -50%);
    }

    /* ========================================================= */
    /* FOOTER / SECURITY                                         */
    /* ========================================================= */

    .tiktok-complete-security {
        margin-top: 18px;
        padding: 14px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        border: 1px solid rgba(255,255,255,.065);
        border-radius: 14px;
        background: rgba(255,255,255,.018);
        color: #555b61;
        font-size: 9px;
        line-height: 1.65;
    }

    .tiktok-complete-security-mark {
        flex-shrink: 0;
        width: 20px;
        height: 20px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(101,213,154,.14);
        border-radius: 50%;
        background: rgba(101,213,154,.045);
        color: #8fdcaf;
        font-size: 9px;
        font-weight: 950;
    }

    .tiktok-complete-back {
        margin-top: 18px;
        text-align: center;
    }

    .tiktok-complete-back a {
        color: #8b9096;
        text-decoration: none;
        font-size: 10px;
        font-weight: 800;
        transition: color .2s ease;
    }

    .tiktok-complete-back a:hover {
        color: #efc985;
    }

    /* ========================================================= */
    /* RESPONSIVE                                                */
    /* ========================================================= */

    @media (max-width: 1000px) {
        .tiktok-complete-stage {
            grid-template-columns: 1fr;
        }

        .tiktok-complete-visual {
            min-height: 500px;
        }

        .tiktok-complete-panel {
            min-height: auto;
            padding: 70px 32px;
        }
    }

    @media (max-width: 620px) {
        .tiktok-complete-page,
        .tiktok-complete-stage {
            min-height: auto;
        }

        .tiktok-complete-visual {
            min-height: 420px;
            padding: 38px 20px;
        }

        .tiktok-complete-visual h2 {
            font-size: clamp(46px, 15vw, 64px);
        }

        .tiktok-complete-panel {
            padding: 52px 18px 64px;
        }

        .tiktok-complete-brand {
            margin-bottom: 30px;
        }

        .tiktok-profile-card {
            align-items: flex-start;
        }

        .tiktok-profile-badge {
            display: none;
        }
    }
</style>
@endpush

@section('content')
<section class="tiktok-complete-page">
    <div class="tiktok-complete-stage">

        {{-- ========================================================= --}}
        {{-- LEFT / BRAND EXPERIENCE                                    --}}
        {{-- ========================================================= --}}

        <div class="tiktok-complete-visual">
            <div class="tiktok-complete-visual-content">

                <span class="tiktok-complete-brand-kicker">
                    Mashal Automotive
                </span>

                <h2>
                    Bijna klaar.
                    Nog één
                    <span>veilige stap.</span>
                </h2>

                <p>
                    Je TikTok-account is succesvol herkend.
                    Vul alleen nog je e-mailadres in om je Mashal-account
                    veilig aan te maken en je login af te ronden.
                </p>

                <div class="tiktok-complete-trust-row">
                    <span class="tiktok-complete-trust">
                        TikTok Login Kit
                    </span>

                    <span class="tiktok-complete-trust">
                        Secure account
                    </span>

                    <span class="tiktok-complete-trust">
                        Privacy first
                    </span>
                </div>

            </div>
        </div>


        {{-- ========================================================= --}}
        {{-- RIGHT / COMPLETE REGISTRATION                             --}}
        {{-- ========================================================= --}}

        <div class="tiktok-complete-panel">
            <div class="tiktok-complete-shell">

                <div class="tiktok-complete-brand">
                    <div class="tiktok-complete-brand-mark">
                        M
                    </div>

                    <div class="tiktok-complete-brand-copy">
                        <strong>
                            Mashal
                        </strong>

                        <span>
                            Automotive
                        </span>
                    </div>
                </div>

                <span class="tiktok-complete-kicker">
                    TikTok login
                </span>

                <h1 class="tiktok-complete-title">
                    Account afronden
                </h1>

                <p class="tiktok-complete-subtitle">
                    TikTok heeft je identiteit bevestigd.
                    Voor Mashal Automotive hebben we nog een geldig e-mailadres nodig.
                </p>


                {{-- ========================================================= --}}
                {{-- TIKTOK PROFILE                                            --}}
                {{-- ========================================================= --}}

                <div class="tiktok-profile-card">

                    <div class="tiktok-profile-avatar">
                        @if (!empty($tiktokAvatar))
                            <img
                                src="{{ $tiktokAvatar }}"
                                alt="TikTok profielfoto van {{ $tiktokName ?? 'TikTok gebruiker' }}"
                                referrerpolicy="no-referrer"
                            >
                        @else
                            {{ strtoupper(mb_substr((string) ($tiktokName ?? 'T'), 0, 1)) }}
                        @endif
                    </div>

                    <div class="tiktok-profile-copy">
                        <small>
                            TikTok-account
                        </small>

                        <strong>
                            {{ $tiktokName ?? 'TikTok gebruiker' }}
                        </strong>
                    </div>

                    <div class="tiktok-profile-badge">
                        Verbonden
                    </div>

                </div>


                {{-- ========================================================= --}}
                {{-- SESSION SUCCESS                                           --}}
                {{-- ========================================================= --}}

                @if (session('success'))
                    <div class="tiktok-complete-message success">
                        {{ session('success') }}
                    </div>
                @endif


                {{-- ========================================================= --}}
                {{-- SESSION ERROR                                             --}}
                {{-- ========================================================= --}}

                @if (session('error'))
                    <div class="tiktok-complete-message error">
                        {{ session('error') }}
                    </div>
                @endif


                {{-- ========================================================= --}}
                {{-- VALIDATION ERRORS                                         --}}
                {{-- ========================================================= --}}

                @if ($errors->any())
                    <div class="tiktok-complete-message error">
                        <strong>
                            Je account kon nog niet worden afgerond.
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


                {{-- ========================================================= --}}
                {{-- COMPLETE REGISTRATION FORM                                --}}
                {{-- ========================================================= --}}

                <form
                    method="POST"
                    action="{{ route('tiktok.complete.submit') }}"
                    novalidate
                >
                    @csrf

                    <div class="tiktok-complete-field">

                        <div class="tiktok-complete-label">
                            <label for="email">
                                E-mailadres
                            </label>

                            @error('email')
                                <span class="tiktok-complete-field-error">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="tiktok-complete-input-wrap">
                            <input
                                class="tiktok-complete-input"
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="naam@example.com"
                                autocomplete="email"
                                inputmode="email"
                                maxlength="255"
                                required
                                autofocus
                            >

                            <span
                                class="tiktok-complete-input-icon"
                                aria-hidden="true"
                            >
                                @
                            </span>
                        </div>

                        <div class="tiktok-complete-help">
                            Gebruik een e-mailadres waar je toegang toe hebt.
                            <strong>
                                Bestaande Mashal-accounts worden niet automatisch gekoppeld.
                            </strong>
                        </div>

                    </div>

                    <button
                        class="tiktok-complete-submit"
                        type="submit"
                    >
                        TikTok-account afronden
                    </button>
                </form>


                {{-- ========================================================= --}}
                {{-- SECURITY INFO                                             --}}
                {{-- ========================================================= --}}

                <div class="tiktok-complete-security">
                    <span
                        class="tiktok-complete-security-mark"
                        aria-hidden="true"
                    >
                        ✓
                    </span>

                    <span>
                        Mashal Automotive ontvangt nooit je TikTok-wachtwoord.
                        Je TikTok-ID, weergavenaam en profielfoto worden alleen gebruikt
                        voor het herkennen en tonen van je account binnen Mashal Automotive.
                    </span>
                </div>

                <div class="tiktok-complete-back">
                    <a href="{{ route('login') }}">
                        ← Terug naar inloggen
                    </a>
                </div>

            </div>
        </div>

    </div>
</section>
@endsection
