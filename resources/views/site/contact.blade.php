@extends('layouts.site-layout')

@section('title', 'Mashal Studio | Contact')

@push('styles')
<style>
    .legal-page {
        min-height: 72vh;
        padding: 86px 0 110px;
        background:
            radial-gradient(circle at 12% 4%, rgba(122, 108, 255, .10), transparent 26rem),
            radial-gradient(circle at 92% 16%, rgba(77, 136, 255, .06), transparent 28rem),
            #08090b;
    }

    .legal-shell {
        width: min(100% - 48px, 1040px);
        margin-inline: auto;
    }

    .legal-hero {
        margin-bottom: 34px;
    }

    .legal-kicker {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
        color: #8f82ff;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .2em;
        text-transform: uppercase;
    }

    .legal-kicker::before {
        content: "";
        width: 30px;
        height: 1px;
        background: linear-gradient(
            90deg,
            #8f82ff,
            #4d88ff
        );
    }

    .legal-title {
        margin: 0;
        color: #fff;
        font-size: clamp(42px, 7vw, 76px);
        line-height: .96;
        letter-spacing: -.055em;
        font-weight: 950;
    }

    .legal-title span {
        color: #a99fff;
    }

    .legal-intro {
        max-width: 800px;
        margin: 18px 0 0;
        color: #858b92;
        font-size: 14px;
        line-height: 1.85;
    }

    .legal-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .legal-card {
        margin-bottom: 18px;
        padding: 24px;
        border: 1px solid rgba(255, 255, 255, .075);
        border-radius: 22px;
        background: linear-gradient(
            145deg,
            rgba(255, 255, 255, .038),
            rgba(255, 255, 255, .014)
        );
        box-shadow: 0 14px 36px rgba(0, 0, 0, .16);
    }

    .legal-card h2 {
        margin: 0 0 10px;
        color: #fff;
        font-size: 19px;
        letter-spacing: -.025em;
    }

    .legal-card p,
    .legal-card li {
        color: #8a9097;
        font-size: 12px;
        line-height: 1.8;
    }

    .legal-card p {
        margin: 0 0 12px;
    }

    .legal-card p:last-child {
        margin-bottom: 0;
    }

    .legal-card ul {
        margin: 10px 0 0;
        padding-left: 20px;
    }

    .legal-card li + li {
        margin-top: 6px;
    }

    .contact-box {
        padding: 18px;
        border: 1px solid rgba(122, 108, 255, .18);
        border-radius: 16px;
        background: rgba(122, 108, 255, .055);
    }

    .contact-box strong {
        display: block;
        margin-bottom: 5px;
        color: #fff;
        font-size: 13px;
    }

    .contact-box span {
        display: block;
        color: #8f949a;
        font-size: 11px;
        line-height: 1.6;
        word-break: break-word;
    }

    .contact-form-card {
        margin-bottom: 18px;
        padding: 28px;
        border: 1px solid rgba(143, 130, 255, .16);
        border-radius: 24px;
        background:
            linear-gradient(
                145deg,
                rgba(143, 130, 255, .06),
                rgba(77, 136, 255, .025)
            ),
            rgba(255, 255, 255, .018);
        box-shadow:
            0 18px 45px rgba(0, 0, 0, .20),
            inset 0 1px 0 rgba(255, 255, 255, .025);
    }

    .contact-form-card h2 {
        margin: 0 0 8px;
        color: #fff;
        font-size: 22px;
        letter-spacing: -.03em;
    }

    .contact-form-intro {
        margin: 0 0 24px;
        color: #858b92;
        font-size: 12px;
        line-height: 1.75;
    }

    .contact-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .contact-field {
        margin-bottom: 16px;
    }

    .contact-field-full {
        grid-column: 1 / -1;
    }

    .contact-label {
        display: block;
        margin-bottom: 8px;
        color: #d8d9df;
        font-size: 11px;
        font-weight: 800;
    }

    .contact-input,
    .contact-textarea {
        width: 100%;
        box-sizing: border-box;
        border: 1px solid rgba(255, 255, 255, .09);
        outline: none;
        border-radius: 14px;
        background: rgba(255, 255, 255, .035);
        color: #fff;
        font: inherit;
        font-size: 13px;
        transition:
            border-color .2s ease,
            background .2s ease,
            box-shadow .2s ease;
    }

    .contact-input {
        min-height: 48px;
        padding: 0 14px;
    }

    .contact-textarea {
        min-height: 170px;
        padding: 14px;
        resize: vertical;
        line-height: 1.6;
    }

    .contact-input::placeholder,
    .contact-textarea::placeholder {
        color: #5e646b;
    }

    .contact-input:focus,
    .contact-textarea:focus {
        border-color: rgba(143, 130, 255, .65);
        background: rgba(143, 130, 255, .045);
        box-shadow: 0 0 0 4px rgba(143, 130, 255, .08);
    }

    .contact-input.is-invalid,
    .contact-textarea.is-invalid {
        border-color: rgba(239, 68, 68, .55);
    }

    .contact-error {
        margin-top: 7px;
        color: #ff8f8f;
        font-size: 10px;
        line-height: 1.55;
    }

    .contact-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 46px;
        padding: 0 21px;
        border: 1px solid rgba(169, 159, 255, .25);
        border-radius: 999px;
        background: linear-gradient(
            135deg,
            #8f82ff,
            #6f63e8,
            #4d88ff
        );
        color: #f8f8ff;
        cursor: pointer;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .01em;
        transition:
            transform .2s ease,
            box-shadow .2s ease,
            opacity .2s ease;
    }

    .contact-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 28px rgba(111, 99, 232, .24);
    }

    .contact-submit:active {
        transform: translateY(0);
    }

    .contact-alert {
        margin-bottom: 20px;
        padding: 14px 16px;
        border-radius: 14px;
        font-size: 11px;
        line-height: 1.65;
    }

    .contact-alert-success {
        border: 1px solid rgba(34, 197, 94, .25);
        background: rgba(34, 197, 94, .08);
        color: #8de8a8;
    }

    .contact-alert-error {
        border: 1px solid rgba(239, 68, 68, .25);
        background: rgba(239, 68, 68, .08);
        color: #ff9e9e;
    }

    .contact-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        margin-top: 14px;
        padding: 0 16px;
        border: 1px solid rgba(122, 108, 255, .28);
        border-radius: 999px;
        background: rgba(122, 108, 255, .08);
        color: #a99fff;
        text-decoration: none;
        font-size: 10px;
        font-weight: 900;
        transition:
            background .2s ease,
            border-color .2s ease,
            transform .2s ease;
    }

    .contact-button:hover {
        background: rgba(122, 108, 255, .14);
        border-color: rgba(122, 108, 255, .48);
        transform: translateY(-1px);
    }

    .legal-link {
        color: #a99fff;
        text-decoration: none;
        border-bottom: 1px solid rgba(169, 159, 255, .35);
        transition: border-color .2s ease;
    }

    .legal-link:hover {
        border-color: rgba(169, 159, 255, .8);
    }

    .contact-note {
        color: #666d74;
        font-size: 10px;
        line-height: 1.7;
    }

    @media (max-width: 720px) {
        .legal-page {
            padding: 62px 0 82px;
        }

        .legal-shell {
            width: min(100% - 30px, 1040px);
        }

        .legal-grid,
        .contact-form-grid {
            grid-template-columns: 1fr;
        }

        .legal-card,
        .contact-form-card {
            padding: 19px;
        }

        .contact-field-full {
            grid-column: auto;
        }
    }
</style>
@endpush

@section('content')
<section class="legal-page">
    <div class="legal-shell">

        <header class="legal-hero">
            <span class="legal-kicker">
                Mashal Studio
            </span>

            <h1 class="legal-title">
                {{ __('Neem') }} <span>{{ __('contact op.') }}</span>
            </h1>

            <p class="legal-intro">
                {{ __('Heb je een vraag over je account, privacy, beveiliging of het gebruik van Mashal Studio? Vul het formulier hieronder in. We hebben je bericht dan direct binnen en nemen zo snel mogelijk contact met je op.') }}
            </p>
        </header>

        <section class="contact-form-card">
            <h2>{{ __('Stuur ons een bericht') }}</h2>

            <p class="contact-form-intro">
                {{ __('Vul je gegevens en toelichting in. Na het verzenden ontvang je automatisch een bevestiging op het opgegeven e-mailadres.') }}
            </p>

            @if (session('success'))
                <div class="contact-alert contact-alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="contact-alert contact-alert-error">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="contact-alert contact-alert-error">
                    {{ __('Controleer de ingevulde gegevens en probeer het opnieuw.') }}
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('contact.send') }}"
                novalidate
            >
                @csrf

                <div class="contact-form-grid">

                    <div class="contact-field">
                        <label
                            class="contact-label"
                            for="first_name"
                        >
                            {{ __('Voornaam') }}
                        </label>

                        <input
                            class="contact-input @error('first_name') is-invalid @enderror"
                            id="first_name"
                            type="text"
                            name="first_name"
                            value="{{ old('first_name', auth()->user()?->first_name ?? auth()->user()?->name) }}"
                            maxlength="100"
                            autocomplete="given-name"
                            placeholder="{{ __('Je voornaam') }}"
                            required
                        >

                        @error('first_name')
                            <div class="contact-error">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="contact-field">
                        <label
                            class="contact-label"
                            for="last_name"
                        >
                            {{ __('Achternaam') }}
                        </label>

                        <input
                            class="contact-input @error('last_name') is-invalid @enderror"
                            id="last_name"
                            type="text"
                            name="last_name"
                            value="{{ old('last_name', auth()->user()?->last_name) }}"
                            maxlength="100"
                            autocomplete="family-name"
                            placeholder="{{ __('Je achternaam') }}"
                            required
                        >

                        @error('last_name')
                            <div class="contact-error">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="contact-field contact-field-full">
                        <label
                            class="contact-label"
                            for="email"
                        >
                            {{ __('E-mailadres') }}
                        </label>

                        <input
                            class="contact-input @error('email') is-invalid @enderror"
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email', auth()->user()?->email) }}"
                            maxlength="255"
                            autocomplete="email"
                            placeholder="jij@example.com"
                            required
                        >

                        @error('email')
                            <div class="contact-error">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="contact-field contact-field-full">
                        <label
                            class="contact-label"
                            for="message"
                        >
                            {{ __('Toelichting') }}
                        </label>

                        <textarea
                            class="contact-textarea @error('message') is-invalid @enderror"
                            id="message"
                            name="message"
                            minlength="10"
                            maxlength="5000"
                            placeholder="Vertel ons waarmee we je kunnen helpen..."
                            required
                        >{{ old('message') }}</textarea>

                        @error('message')
                            <div class="contact-error">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                </div>

                <button
                    class="contact-submit"
                    type="submit"
                >
                    {{ __('Bericht versturen') }}
                </button>
            </form>
        </section>

        <div class="legal-grid">

            <section class="legal-card">
                <h2>{{ __('Contact') }}</h2>

                <div class="contact-box">
                    <strong>{{ __('E-mailadres') }}</strong>

                    <span>
                        mashalhussain203@gmail.com
                    </span>

                    <a
                        class="contact-button"
                        href="mailto:mashalhussain203@gmail.com"
                    >
                        {{ __('E-mail sturen') }}
                    </a>
                </div>
            </section>

            <section class="legal-card">
                <h2>{{ __('Waarmee kunnen we helpen?') }}</h2>

                <ul>
                    <li>{{ __('problemen met inloggen of toegang tot je account;') }}</li>
                    <li>{{ __('vragen over TikTok-login of andere inlogmethoden;') }}</li>
                    <li>{{ __('vragen over passkeys of Authenticator;') }}</li>
                    <li>{{ __('privacy- en gegevensverzoeken;') }}</li>
                    <li>{{ __('technische problemen met Mashal Studio;') }}</li>
                    <li>{{ __('beveiligingsmeldingen;') }}</li>
                    <li>{{ __('algemene vragen, feedback en suggesties.') }}</li>
                </ul>
            </section>

        </div>

        <section class="legal-card">
            <h2>{{ __('Account en inloggen') }}</h2>

            <p>
                Heb je problemen met inloggen, het aanmaken van een account
                of het gebruiken van een externe inlogmethode zoals TikTok?
                Beschrijf het probleem dan zo duidelijk mogelijk in het formulier.
            </p>

            <p>
                Deel daarbij nooit je wachtwoord, Authenticator-code,
                recoverycode of andere geheime beveiligingsgegevens.
            </p>
        </section>

        <section class="legal-card">
            <h2>{{ __('Privacy- en gegevensverzoeken') }}</h2>

            <p>
                Voor vragen over je persoonsgegevens, inzage, correctie,
                verwijdering van gegevens of andere privacygerelateerde verzoeken
                kun je het contactformulier gebruiken of e-mailen naar
                <a
                    class="legal-link"
                    href="mailto:mashalhussain203@gmail.com"
                >
                    mashalhussain203@gmail.com
                </a>.
            </p>

            <p>
                Meer informatie over hoe Mashal Studio persoonsgegevens verwerkt,
                vind je in ons
                <a
                    class="legal-link"
                    href="{{ route('privacy') }}"
                >
                    {{ __('Privacybeleid') }}
                </a>.
            </p>
        </section>

        <section class="legal-card">
            <h2>{{ __('Beveiligingsmeldingen') }}</h2>

            <p>
                Denk je dat er sprake is van ongeautoriseerde toegang,
                misbruik, een kwetsbaarheid of een ander beveiligingsprobleem
                binnen Mashal Studio? Neem dan zo snel mogelijk contact met ons op.
            </p>

            <p>
                Beschrijf zo duidelijk mogelijk wat je hebt waargenomen.
                Deel geen wachtwoorden, verificatiecodes, recoverycodes
                of andere vertrouwelijke inloggegevens.
            </p>
        </section>

        <section class="legal-card">
            <h2>{{ __('Juridische informatie') }}</h2>

            <p>
                Bekijk voor meer informatie ook ons
                <a
                    class="legal-link"
                    href="{{ route('privacy') }}"
                >
                    {{ __('Privacybeleid') }}
                </a>
                en onze
                <a
                    class="legal-link"
                    href="{{ route('terms') }}"
                >
                    {{ __('Gebruiksvoorwaarden') }}
                </a>.
            </p>
        </section>

        <section class="legal-card">
            <h2>{{ __('Reactietijd') }}</h2>

            <p>
                We proberen vragen en verzoeken zo snel mogelijk te beoordelen
                en te beantwoorden. De reactietijd kan verschillen afhankelijk
                van het onderwerp, de complexiteit van het verzoek
                en de beschikbaarheid van ondersteuning.
            </p>

            <p class="contact-note">
                Na het verzenden van het formulier ontvang je automatisch
                een bevestigingsmail op het opgegeven e-mailadres.
            </p>
        </section>

    </div>
</section>
@endsection