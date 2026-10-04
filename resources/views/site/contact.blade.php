@extends('layouts.site-layout')

@section('title', 'Mashal Studio | Contact')

@push('styles')
<style>
    .legal-page {
        min-height: 72vh;
        padding: 86px 0 110px;
        background:
            radial-gradient(circle at 12% 4%, rgba(122, 108, 255, .10), transparent 26rem),
            radial-gradient(circle at 92% 16%, rgba(255, 255, 255, .025), transparent 28rem),
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
        background: #8f82ff;
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

        .legal-grid {
            grid-template-columns: 1fr;
        }

        .legal-card {
            padding: 19px;
        }
    }
</style>
@endpush

@section('content')
<section class="legal-page">
    <div class="legal-shell">

        <header class="legal-hero">
            <span class="legal-kicker">Mashal Studio</span>

            <h1 class="legal-title">
                Neem <span>contact op.</span>
            </h1>

            <p class="legal-intro">
                Heb je een vraag over je account, privacy, beveiliging
                of het gebruik van Mashal Studio? Neem gerust contact met ons op.
                We helpen je graag verder.
            </p>
        </header>

        <div class="legal-grid">

            <section class="legal-card">
                <h2>Contact</h2>

                <div class="contact-box">
                    <strong>E-mailadres</strong>

                    <span>
                        mahsalhussain203@gmail.com
                    </span>

                    <a
                        class="contact-button"
                        href="mailto:mahsalhussain203@gmail.com"
                    >
                        E-mail sturen
                    </a>
                </div>
            </section>

            <section class="legal-card">
                <h2>Waarmee kunnen we helpen?</h2>

                <ul>
                    <li>problemen met inloggen of toegang tot je account;</li>
                    <li>vragen over TikTok-login of andere inlogmethoden;</li>
                    <li>vragen over passkeys of Authenticator;</li>
                    <li>privacy- en gegevensverzoeken;</li>
                    <li>technische problemen met Mashal Studio;</li>
                    <li>beveiligingsmeldingen;</li>
                    <li>algemene vragen, feedback en suggesties.</li>
                </ul>
            </section>

        </div>

        <section class="legal-card">
            <h2>Account en inloggen</h2>

            <p>
                Heb je problemen met inloggen, het aanmaken van een account
                of het gebruiken van een externe inlogmethode zoals TikTok?
                Stuur ons dan een e-mail met een korte omschrijving van het probleem.
            </p>

            <p>
                Deel daarbij nooit je wachtwoord, Authenticator-code,
                recoverycode of andere geheime beveiligingsgegevens.
            </p>
        </section>

        <section class="legal-card">
            <h2>Privacy- en gegevensverzoeken</h2>

            <p>
                Voor vragen over je persoonsgegevens, inzage, correctie,
                verwijdering van gegevens of andere privacygerelateerde verzoeken
                kun je contact opnemen via
                <a
                    class="legal-link"
                    href="mailto:mahsalhussain203@gmail.com"
                >
                    mahsalhussain203@gmail.com
                </a>.
            </p>

            <p>
                Meer informatie over hoe Mashal Studio persoonsgegevens verwerkt,
                vind je in ons
                <a
                    class="legal-link"
                    href="{{ route('privacy') }}"
                >
                    Privacybeleid
                </a>.
            </p>
        </section>

        <section class="legal-card">
            <h2>Beveiligingsmeldingen</h2>

            <p>
                Denk je dat er sprake is van ongeautoriseerde toegang,
                misbruik, een kwetsbaarheid of een ander beveiligingsprobleem
                binnen Mashal Studio? Neem dan zo snel mogelijk contact met ons op.
            </p>

            <p>
                Vermeld in het onderwerp van je e-mail duidelijk dat het om
                een beveiligingsmelding gaat en beschrijf wat je hebt waargenomen.
                Deel geen wachtwoorden, verificatiecodes, recoverycodes
                of andere vertrouwelijke inloggegevens.
            </p>
        </section>

        <section class="legal-card">
            <h2>Juridische informatie</h2>

            <p>
                Bekijk voor meer informatie ook ons
                <a
                    class="legal-link"
                    href="{{ route('privacy') }}"
                >
                    Privacybeleid
                </a>
                en onze
                <a
                    class="legal-link"
                    href="{{ route('terms') }}"
                >
                    Gebruiksvoorwaarden
                </a>.
            </p>
        </section>

        <section class="legal-card">
            <h2>Reactietijd</h2>

            <p>
                We proberen vragen en verzoeken zo snel mogelijk te beoordelen
                en te beantwoorden. De reactietijd kan verschillen afhankelijk
                van het onderwerp, de complexiteit van het verzoek
                en de beschikbaarheid van ondersteuning.
            </p>

            <p class="contact-note">
                Voor dringende beveiligings- of privacykwesties kun je dit
                duidelijk vermelden in het onderwerp van je e-mail.
            </p>
        </section>

    </div>
</section>
@endsection
