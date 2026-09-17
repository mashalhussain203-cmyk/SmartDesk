@extends('layouts.admin-layout')

@section('title', 'Mashal Admin | Nieuwe gebruiker')
@section('page-title', 'Nieuwe gebruiker')

@push('styles')
<style>
    .user-create-page {
        position: relative;
        overflow: hidden;
        padding: 6px 0 34px;
    }

    .user-create-page::before {
        content: "M";
        position: absolute;
        right: -42px;
        top: 15px;
        color: rgba(255,255,255,.018);
        font-size: 260px;
        font-weight: 950;
        line-height: .8;
        pointer-events: none;
        user-select: none;
    }

    /* ========================================================= */
    /* HEADER                                                     */
    /* ========================================================= */

    .create-hero {
        position: relative;
        z-index: 2;
        margin-bottom: 24px;
        padding: 28px;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: end;
        gap: 28px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 24px;
        background:
            radial-gradient(
                circle at 90% 10%,
                rgba(215,164,95,.10),
                transparent 18rem
            ),
            linear-gradient(
                145deg,
                rgba(255,255,255,.045),
                rgba(255,255,255,.015)
            );
        box-shadow: 0 18px 50px rgba(0,0,0,.18);
        overflow: hidden;
    }

    .create-hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 11px;
        color: #d6a45f;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .19em;
        text-transform: uppercase;
    }

    .create-hero-kicker::before {
        content: "";
        width: 28px;
        height: 1px;
        background: #d6a45f;
    }

    .create-hero h2 {
        margin: 0;
        color: #fff;
        font-size: clamp(28px, 3.5vw, 44px);
        line-height: 1;
        letter-spacing: -.045em;
    }

    .create-hero p {
        max-width: 690px;
        margin: 11px 0 0;
        color: #7d838a;
        font-size: 12px;
        line-height: 1.75;
    }

    .hero-back {
        min-height: 42px;
        padding: 0 15px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 999px;
        background: rgba(255,255,255,.025);
        color: #b8b7b2;
        text-decoration: none;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .04em;
        transition:
            transform .2s ease,
            border-color .2s ease,
            background .2s ease,
            color .2s ease;
    }

    .hero-back:hover {
        transform: translateY(-2px);
        border-color: rgba(215,164,95,.25);
        background: rgba(215,164,95,.055);
        color: #efc985;
    }

    /* ========================================================= */
    /* MESSAGES                                                   */
    /* ========================================================= */

    .create-message {
        position: relative;
        z-index: 2;
        margin-bottom: 18px;
        padding: 14px 16px;
        border-radius: 14px;
        font-size: 12px;
        line-height: 1.6;
    }

    .create-message.success {
        border: 1px solid rgba(91,214,149,.20);
        background: rgba(91,214,149,.07);
        color: #a9efc8;
    }

    .create-message.error {
        border: 1px solid rgba(241,123,123,.20);
        background: rgba(241,123,123,.07);
        color: #ffc1c1;
    }

    .create-message ul {
        margin: 8px 0 0 18px;
        padding: 0;
    }

    /* ========================================================= */
    /* LAYOUT                                                     */
    /* ========================================================= */

    .create-layout {
        position: relative;
        z-index: 2;
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(300px, .8fr);
        gap: 22px;
        align-items: start;
    }

    .create-form-card,
    .create-aside-card {
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 22px;
        background:
            linear-gradient(
                145deg,
                rgba(255,255,255,.04),
                rgba(255,255,255,.014)
            );
        box-shadow: 0 16px 44px rgba(0,0,0,.17);
    }

    .create-form-card {
        padding: 26px;
    }

    .create-aside {
        position: sticky;
        top: 92px;
        display: grid;
        gap: 14px;
    }

    .create-aside-card {
        padding: 20px;
    }

    /* ========================================================= */
    /* SECTION HEAD                                               */
    /* ========================================================= */

    .form-section + .form-section {
        margin-top: 30px;
        padding-top: 26px;
        border-top: 1px solid rgba(255,255,255,.07);
    }

    .form-section-head {
        margin-bottom: 19px;
    }

    .form-section-kicker {
        display: block;
        margin-bottom: 6px;
        color: #9e7442;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .form-section-head h3 {
        margin: 0;
        color: #fff;
        font-size: 20px;
        letter-spacing: -.03em;
    }

    .form-section-head p {
        margin: 7px 0 0;
        color: #70767d;
        font-size: 10px;
        line-height: 1.7;
    }

    /* ========================================================= */
    /* FORM                                                       */
    /* ========================================================= */

    .create-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0,1fr));
        gap: 15px;
    }

    .create-field {
        margin-bottom: 16px;
    }

    .create-field.full {
        grid-column: 1 / -1;
    }

    .create-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 8px;
    }

    .create-label label {
        color: #b8b9b6;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .field-error {
        color: #f3a1a1;
        font-size: 9px;
        font-weight: 750;
    }

    .input-wrap {
        position: relative;
    }

    .create-input {
        width: 100%;
        height: 52px;
        padding: 0 46px 0 15px;
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 14px;
        outline: none;
        background: rgba(255,255,255,.03);
        color: #fff;
        font-size: 12px;
        transition:
            border-color .2s ease,
            background .2s ease,
            box-shadow .2s ease;
    }

    .create-input::placeholder {
        color: #565c63;
    }

    .create-input:focus {
        border-color: rgba(215,164,95,.42);
        background: rgba(215,164,95,.03);
        box-shadow: 0 0 0 4px rgba(215,164,95,.06);
    }

    .input-icon {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6d737a;
        font-size: 11px;
        pointer-events: none;
    }

    .password-toggle {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        height: 34px;
        min-width: 42px;
        padding: 0 8px;
        border: 0;
        border-radius: 10px;
        background: transparent;
        color: #7d838a;
        font-size: 8px;
        font-weight: 850;
        cursor: pointer;
        transition: color .2s ease, background .2s ease;
    }

    .password-toggle:hover {
        color: #efc985;
        background: rgba(215,164,95,.06);
    }

    .field-help {
        display: block;
        margin-top: 7px;
        color: #62686f;
        font-size: 9px;
        line-height: 1.5;
    }

    /* ========================================================= */
    /* PASSWORD STRENGTH                                         */
    /* ========================================================= */

    .password-strength {
        margin-top: 9px;
    }

    .password-strength-bars {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 5px;
    }

    .password-strength-bar {
        height: 4px;
        border-radius: 999px;
        background: rgba(255,255,255,.07);
    }

    .password-strength[data-level="1"] .password-strength-bar:nth-child(1) {
        background: #d65f5f;
    }

    .password-strength[data-level="2"] .password-strength-bar:nth-child(-n+2) {
        background: #d59a50;
    }

    .password-strength[data-level="3"] .password-strength-bar:nth-child(-n+3) {
        background: #d8bd63;
    }

    .password-strength[data-level="4"] .password-strength-bar:nth-child(-n+4) {
        background: #63c98f;
    }

    .password-strength-text {
        margin-top: 7px;
        color: #62686f;
        font-size: 8px;
    }

    /* ========================================================= */
    /* PERMISSIONS                                                */
    /* ========================================================= */

    .permission-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .permission-card {
        position: relative;
        padding: 17px;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 16px;
        background: rgba(255,255,255,.022);
        transition:
            border-color .2s ease,
            background .2s ease,
            transform .2s ease;
    }

    .permission-card:hover {
        transform: translateY(-2px);
        border-color: rgba(215,164,95,.18);
    }

    .permission-label {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        margin: 0;
        cursor: pointer;
        color: inherit;
        text-transform: none;
        letter-spacing: normal;
    }

    .permission-label input {
        flex-shrink: 0;
        width: 17px;
        height: 17px;
        margin: 2px 0 0;
        accent-color: #d7a45f;
    }

    .permission-copy strong {
        display: block;
        color: #dedcd7;
        font-size: 11px;
        line-height: 1.4;
    }

    .permission-copy span {
        display: block;
        margin-top: 5px;
        color: #6c7279;
        font-size: 9px;
        line-height: 1.65;
    }

    /* ========================================================= */
    /* ACTIONS                                                    */
    /* ========================================================= */

    .create-actions {
        margin-top: 26px;
        padding-top: 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
        border-top: 1px solid rgba(255,255,255,.07);
    }

    .create-primary,
    .create-secondary {
        min-height: 48px;
        padding: 0 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 950;
        letter-spacing: .04em;
        text-decoration: none;
        cursor: pointer;
        transition:
            transform .2s ease,
            box-shadow .2s ease,
            border-color .2s ease,
            background .2s ease;
    }

    .create-primary {
        border: 0;
        background:
            linear-gradient(
                135deg,
                #f1cc8b,
                #ca914c
            );
        color: #14100b;
        box-shadow: 0 14px 34px rgba(215,164,95,.18);
    }

    .create-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 22px 48px rgba(215,164,95,.28);
    }

    .create-secondary {
        border: 1px solid rgba(255,255,255,.09);
        background: rgba(255,255,255,.025);
        color: #a9aca9;
    }

    .create-secondary:hover {
        transform: translateY(-2px);
        border-color: rgba(215,164,95,.22);
        color: #efc985;
        background: rgba(215,164,95,.05);
    }

    /* ========================================================= */
    /* SIDEBAR CARDS                                              */
    /* ========================================================= */

    .aside-kicker {
        display: block;
        margin-bottom: 7px;
        color: #9e7442;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .15em;
        text-transform: uppercase;
    }

    .create-aside-card h3 {
        margin: 0 0 8px;
        color: #fff;
        font-size: 17px;
        letter-spacing: -.025em;
    }

    .create-aside-card p {
        margin: 0;
        color: #6f757c;
        font-size: 10px;
        line-height: 1.75;
    }

    .aside-list {
        margin-top: 14px;
        display: grid;
        gap: 10px;
    }

    .aside-list-item {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        color: #858a90;
        font-size: 9px;
        line-height: 1.6;
    }

    .aside-list-mark {
        flex-shrink: 0;
        width: 20px;
        height: 20px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(215,164,95,.14);
        border-radius: 50%;
        color: #c8995a;
        font-size: 8px;
    }

    .create-aside-card.warning {
        border-color: rgba(242,198,109,.16);
        background:
            linear-gradient(
                145deg,
                rgba(242,198,109,.055),
                rgba(242,198,109,.018)
            );
    }

    .create-aside-card.warning h3 {
        color: #ecd28f;
    }

    .create-aside-card.warning p {
        color: #8b7b5b;
    }

    .preview-card {
        padding: 18px;
    }

    .preview-user {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .preview-avatar {
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
        font-size: 17px;
        font-weight: 950;
    }

    .preview-copy strong {
        display: block;
        color: #e4e2dd;
        font-size: 11px;
    }

    .preview-copy span {
        display: block;
        margin-top: 3px;
        color: #666c73;
        font-size: 9px;
        word-break: break-word;
    }

    .preview-tags {
        margin-top: 14px;
        display: flex;
        gap: 7px;
        flex-wrap: wrap;
    }

    .preview-tag {
        padding: 6px 8px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 999px;
        background: rgba(255,255,255,.025);
        color: #8d9298;
        font-size: 7px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .preview-tag.active {
        border-color: rgba(215,164,95,.17);
        background: rgba(215,164,95,.055);
        color: #d8ac6a;
    }

    /* ========================================================= */
    /* RESPONSIVE                                                 */
    /* ========================================================= */

    @media (max-width: 1050px) {
        .create-hero,
        .create-layout {
            grid-template-columns: 1fr;
        }

        .create-aside {
            position: static;
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 720px) {
        .create-form-grid,
        .permission-grid,
        .create-aside {
            grid-template-columns: 1fr;
        }

        .create-field.full {
            grid-column: auto;
        }

        .create-form-card {
            padding: 21px;
        }

        .create-hero {
            padding: 22px;
        }

        .create-actions {
            align-items: stretch;
            flex-direction: column-reverse;
        }

        .create-primary,
        .create-secondary {
            width: 100%;
        }
    }
</style>
@endpush


@section('content')

<section class="user-create-page">

    {{-- ========================================================= --}}
    {{-- HERO                                                       --}}
    {{-- ========================================================= --}}

    <div class="create-hero">

        <div>

            <span class="create-hero-kicker">
                Mashal User Management
            </span>

            <h2>
                Nieuwe gebruiker aanmaken
            </h2>

            <p>
                Maak handmatig een nieuw Mashal-account aan,
                bepaal direct de verificatiestatus
                en geef alleen indien nodig administratorrechten.
            </p>

        </div>


        <a
            class="hero-back"
            href="{{ route('users.index') }}"
        >
            ← Terug naar gebruikers
        </a>

    </div>


    {{-- ========================================================= --}}
    {{-- MESSAGES                                                   --}}
    {{-- ========================================================= --}}

    @if (session('success'))

        <div class="create-message success">

            <strong>
                Gebruiker aangemaakt.
            </strong>

            {{ session('success') }}

        </div>

    @endif


    @if ($errors->any())

        <div class="create-message error">

            <strong>
                De gebruiker kon niet worden aangemaakt.
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


    <div class="create-layout">

        {{-- ========================================================= --}}
        {{-- FORM                                                       --}}
        {{-- ========================================================= --}}

        <div class="create-form-card">

            <form
                method="POST"
                action="{{ route('users.store') }}"
                id="createUserForm"
            >
                @csrf


                {{-- ================================================= --}}
                {{-- PERSONAL DATA                                     --}}
                {{-- ================================================= --}}

                <section class="form-section">

                    <div class="form-section-head">

                        <span class="form-section-kicker">
                            01 / Identity
                        </span>

                        <h3>
                            Persoonlijke gegevens
                        </h3>

                        <p>
                            Vul de basisgegevens in waarmee
                            de gebruiker toegang krijgt tot Mashal.
                        </p>

                    </div>


                    <div class="create-form-grid">

                        {{-- NAME --}}

                        <div class="create-field">

                            <div class="create-label">

                                <label for="name">
                                    Naam
                                </label>

                                @error('name')
                                    <span class="field-error">
                                        {{ $message }}
                                    </span>
                                @enderror

                            </div>


                            <div class="input-wrap">

                                <input
                                    class="create-input"
                                    id="name"
                                    type="text"
                                    name="name"
                                    value="{{ old('name') }}"
                                    placeholder="Voornaam en achternaam"
                                    autocomplete="name"
                                    required
                                    autofocus
                                >

                                <span class="input-icon">
                                    ◇
                                </span>

                            </div>

                        </div>


                        {{-- EMAIL --}}

                        <div class="create-field">

                            <div class="create-label">

                                <label for="email">
                                    E-mailadres
                                </label>

                                @error('email')
                                    <span class="field-error">
                                        {{ $message }}
                                    </span>
                                @enderror

                            </div>


                            <div class="input-wrap">

                                <input
                                    class="create-input"
                                    id="email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="naam@example.com"
                                    autocomplete="email"
                                    required
                                >

                                <span class="input-icon">
                                    @
                                </span>

                            </div>

                        </div>

                    </div>

                </section>


                {{-- ================================================= --}}
                {{-- PASSWORD                                          --}}
                {{-- ================================================= --}}

                <section class="form-section">

                    <div class="form-section-head">

                        <span class="form-section-kicker">
                            02 / Credentials
                        </span>

                        <h3>
                            Toegang & wachtwoord
                        </h3>

                        <p>
                            Stel een sterk tijdelijk of definitief wachtwoord in
                            en bevestig het voordat het account wordt opgeslagen.
                        </p>

                    </div>


                    <div class="create-form-grid">

                        <div class="create-field">

                            <div class="create-label">

                                <label for="password">
                                    Wachtwoord
                                </label>

                                @error('password')
                                    <span class="field-error">
                                        {{ $message }}
                                    </span>
                                @enderror

                            </div>


                            <div class="input-wrap">

                                <input
                                    class="create-input"
                                    id="password"
                                    type="password"
                                    name="password"
                                    placeholder="Minimaal 8 tekens"
                                    autocomplete="new-password"
                                    minlength="8"
                                    required
                                >

                                <button
                                    class="password-toggle"
                                    type="button"
                                    data-toggle-password="password"
                                    aria-label="Wachtwoord tonen of verbergen"
                                >
                                    Tonen
                                </button>

                            </div>


                            <div
                                class="password-strength"
                                id="passwordStrength"
                                data-level="0"
                            >

                                <div class="password-strength-bars">
                                    <span class="password-strength-bar"></span>
                                    <span class="password-strength-bar"></span>
                                    <span class="password-strength-bar"></span>
                                    <span class="password-strength-bar"></span>
                                </div>

                                <div
                                    class="password-strength-text"
                                    id="passwordStrengthText"
                                >
                                    Gebruik minimaal 8 tekens.
                                </div>

                            </div>

                        </div>


                        <div class="create-field">

                            <div class="create-label">

                                <label for="password_confirmation">
                                    Wachtwoord bevestigen
                                </label>

                                @error('password_confirmation')
                                    <span class="field-error">
                                        {{ $message }}
                                    </span>
                                @enderror

                            </div>


                            <div class="input-wrap">

                                <input
                                    class="create-input"
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    placeholder="Herhaal het wachtwoord"
                                    autocomplete="new-password"
                                    minlength="8"
                                    required
                                >

                                <button
                                    class="password-toggle"
                                    type="button"
                                    data-toggle-password="password_confirmation"
                                    aria-label="Wachtwoordbevestiging tonen of verbergen"
                                >
                                    Tonen
                                </button>

                            </div>

                        </div>

                    </div>

                </section>


                {{-- ================================================= --}}
                {{-- RIGHTS / VERIFICATION                             --}}
                {{-- ================================================= --}}

                <section class="form-section">

                    <div class="form-section-head">

                        <span class="form-section-kicker">
                            03 / Access
                        </span>

                        <h3>
                            Verificatie & rechten
                        </h3>

                        <p>
                            Kies bewust welke status en rechten
                            het nieuwe account direct krijgt.
                        </p>

                    </div>


                    <div class="permission-grid">

                        {{-- VERIFIED --}}

                        <div class="permission-card">

                            <label
                                class="permission-label"
                                for="email_verified"
                            >

                                <input
                                    id="email_verified"
                                    type="checkbox"
                                    name="email_verified"
                                    value="1"
                                    {{ old('email_verified') ? 'checked' : '' }}
                                >

                                <span class="permission-copy">

                                    <strong>
                                        E-mailadres direct verifiëren
                                    </strong>

                                    <span>
                                        Indien uitgeschakeld moet de gebruiker
                                        het e-mailadres zelf bevestigen.
                                    </span>

                                </span>

                            </label>

                        </div>


                        {{-- ADMIN --}}

                        <div class="permission-card">

                            <label
                                class="permission-label"
                                for="is_admin"
                            >

                                <input
                                    id="is_admin"
                                    type="checkbox"
                                    name="is_admin"
                                    value="1"
                                    {{ old('is_admin') ? 'checked' : '' }}
                                >

                                <span class="permission-copy">

                                    <strong>
                                        Administratorrechten
                                    </strong>

                                    <span>
                                        Geeft toegang tot beheertaken
                                        en gevoelige gebruikersfuncties.
                                    </span>

                                </span>

                            </label>

                        </div>

                    </div>

                </section>


                {{-- ================================================= --}}
                {{-- ACTIONS                                           --}}
                {{-- ================================================= --}}

                <div class="create-actions">

                    <a
                        class="create-secondary"
                        href="{{ route('users.index') }}"
                    >
                        Annuleren
                    </a>


                    <button
                        class="create-primary"
                        type="submit"
                    >
                        Gebruiker aanmaken
                        <span aria-hidden="true">→</span>
                    </button>

                </div>

            </form>

        </div>


        {{-- ========================================================= --}}
        {{-- SIDEBAR                                                    --}}
        {{-- ========================================================= --}}

        <aside class="create-aside">

            {{-- LIVE PREVIEW --}}

            <div class="create-aside-card preview-card">

                <span class="aside-kicker">
                    Live preview
                </span>

                <div class="preview-user">

                    <div
                        class="preview-avatar"
                        id="previewAvatar"
                    >
                        M
                    </div>

                    <div class="preview-copy">

                        <strong id="previewName">
                            Nieuwe gebruiker
                        </strong>

                        <span id="previewEmail">
                            naam@example.com
                        </span>

                    </div>

                </div>


                <div class="preview-tags">

                    <span
                        class="preview-tag"
                        id="previewVerified"
                    >
                        Niet geverifieerd
                    </span>

                    <span
                        class="preview-tag"
                        id="previewAdmin"
                    >
                        Gebruiker
                    </span>

                </div>

            </div>


            {{-- ACCOUNT FLOW --}}

            <div class="create-aside-card">

                <span class="aside-kicker">
                    Account flow
                </span>

                <h3>
                    Wat gebeurt er daarna?
                </h3>

                <p>
                    Na aanmaken wordt het account direct opgeslagen
                    en kan Mashal de bijbehorende accountmail verzenden.
                </p>


                <div class="aside-list">

                    <div class="aside-list-item">

                        <span class="aside-list-mark">
                            1
                        </span>

                        <span>
                            Naam, e-mail en wachtwoord
                            worden opgeslagen.
                        </span>

                    </div>


                    <div class="aside-list-item">

                        <span class="aside-list-mark">
                            2
                        </span>

                        <span>
                            De gekozen verificatiestatus
                            wordt toegepast.
                        </span>

                    </div>


                    <div class="aside-list-item">

                        <span class="aside-list-mark">
                            3
                        </span>

                        <span>
                            Administratorrechten worden
                            alleen toegekend indien aangevinkt.
                        </span>

                    </div>

                </div>

            </div>


            {{-- SECURITY WARNING --}}

            <div class="create-aside-card warning">

                <span class="aside-kicker">
                    Security notice
                </span>

                <h3>
                    Let op met adminrechten
                </h3>

                <p>
                    Geef administratorrechten alleen aan personen
                    die beheerrechten daadwerkelijk nodig hebben.
                    Een administrator kan gebruikersgegevens wijzigen
                    en accounts verwijderen.
                </p>

            </div>

        </aside>

    </div>

</section>

@endsection


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {

        const nameInput =
            document.getElementById('name');

        const emailInput =
            document.getElementById('email');

        const verifiedInput =
            document.getElementById('email_verified');

        const adminInput =
            document.getElementById('is_admin');

        const previewAvatar =
            document.getElementById('previewAvatar');

        const previewName =
            document.getElementById('previewName');

        const previewEmail =
            document.getElementById('previewEmail');

        const previewVerified =
            document.getElementById('previewVerified');

        const previewAdmin =
            document.getElementById('previewAdmin');


        function updatePreview() {

            const name =
                nameInput && nameInput.value.trim()
                    ? nameInput.value.trim()
                    : 'Nieuwe gebruiker';

            const email =
                emailInput && emailInput.value.trim()
                    ? emailInput.value.trim()
                    : 'naam@example.com';

            if (previewName) {
                previewName.textContent = name;
            }

            if (previewEmail) {
                previewEmail.textContent = email;
            }

            if (previewAvatar) {
                previewAvatar.textContent =
                    name.charAt(0).toUpperCase() || 'M';
            }

            if (
                previewVerified &&
                verifiedInput
            ) {
                previewVerified.textContent =
                    verifiedInput.checked
                        ? 'Geverifieerd'
                        : 'Niet geverifieerd';

                previewVerified.classList.toggle(
                    'active',
                    verifiedInput.checked
                );
            }

            if (
                previewAdmin &&
                adminInput
            ) {
                previewAdmin.textContent =
                    adminInput.checked
                        ? 'Administrator'
                        : 'Gebruiker';

                previewAdmin.classList.toggle(
                    'active',
                    adminInput.checked
                );
            }

        }


        [
            nameInput,
            emailInput
        ].forEach(function (input) {

            if (input) {
                input.addEventListener(
                    'input',
                    updatePreview
                );
            }

        });


        [
            verifiedInput,
            adminInput
        ].forEach(function (input) {

            if (input) {
                input.addEventListener(
                    'change',
                    updatePreview
                );
            }

        });


        updatePreview();


        // Show / hide passwords.
        document
            .querySelectorAll('[data-toggle-password]')
            .forEach(function (button) {

                button.addEventListener('click', function () {

                    const inputId =
                        button.getAttribute('data-toggle-password');

                    const input =
                        document.getElementById(inputId);

                    if (!input) {
                        return;
                    }

                    const hidden =
                        input.type === 'password';

                    input.type =
                        hidden ? 'text' : 'password';

                    button.textContent =
                        hidden ? 'Verberg' : 'Tonen';

                });

            });


        // Visual password strength.
        const passwordInput =
            document.getElementById('password');

        const strengthBox =
            document.getElementById('passwordStrength');

        const strengthText =
            document.getElementById('passwordStrengthText');


        if (
            passwordInput &&
            strengthBox &&
            strengthText
        ) {

            passwordInput.addEventListener('input', function () {

                const value =
                    passwordInput.value;

                let score = 0;

                if (value.length >= 8) {
                    score++;
                }

                if (
                    /[A-Z]/.test(value) &&
                    /[a-z]/.test(value)
                ) {
                    score++;
                }

                if (/\d/.test(value)) {
                    score++;
                }

                if (
                    /[^A-Za-z0-9]/.test(value) &&
                    value.length >= 10
                ) {
                    score++;
                }

                strengthBox.dataset.level =
                    String(score);

                const labels = {
                    0: 'Gebruik minimaal 8 tekens.',
                    1: 'Basiswachtwoord.',
                    2: 'Redelijk wachtwoord.',
                    3: 'Sterk wachtwoord.',
                    4: 'Zeer sterk wachtwoord.'
                };

                strengthText.textContent =
                    labels[score] || labels[0];

            });

        }

    });
</script>
@endpush
