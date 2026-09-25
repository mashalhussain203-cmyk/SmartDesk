@extends('layouts.site-layout')

@section('title', 'Mashal Studio | Privacybeleid')

@push('styles')

<style>
    .legal-page {
        min-height: 72vh;
        padding: 86px 0 110px;
        background:
            radial-gradient(circle at 12% 4%, rgba(215, 164, 95, .10), transparent 26rem),
            radial-gradient(circle at 92% 16%, rgba(255,255,255,.025), transparent 28rem),
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
        color: #d7a45f;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .2em;
        text-transform: uppercase;
    }

    .legal-kicker::before {
        content: "";
        width: 30px;
        height: 1px;
        background: #d7a45f;
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
        color: #efc985;
    }

    .legal-intro {
        max-width: 800px;
        margin: 18px 0 0;
        color: #858b92;
        font-size: 14px;
        line-height: 1.85;
    }

    .legal-card {
        margin-bottom: 18px;
        padding: 24px;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 22px;
        background: linear-gradient(145deg, rgba(255,255,255,.038), rgba(255,255,255,.014));
        box-shadow: 0 14px 36px rgba(0,0,0,.16);
    }

    .legal-card h2 {
        margin: 0 0 10px;
        color: #fff;
        font-size: 19px;
        letter-spacing: -.025em;
    }

    .legal-card h3 {
        margin: 22px 0 8px;
        color: #e7e7e3;
        font-size: 15px;
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

    .legal-highlight {
        color: #efc985;
        font-weight: 800;
    }

    .legal-link {
        color: #efc985;
        text-decoration: none;
        border-bottom: 1px solid rgba(239,201,133,.35);
    }

    .legal-link:hover {
        border-color: rgba(239,201,133,.8);
    }

    .legal-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .legal-meta {
        margin-top: 28px;
        color: #666d74;
        font-size: 10px;
        line-height: 1.6;
    }

    .contact-box {
        padding: 18px;
        border: 1px solid rgba(215,164,95,.18);
        border-radius: 16px;
        background: rgba(215,164,95,.055);
    }

    .contact-box strong {
        display: block;
        color: #fff;
        font-size: 13px;
        margin-bottom: 5px;
    }

    .contact-box span {
        color: #8f949a;
        font-size: 11px;
        line-height: 1.6;
    }

    .contact-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        margin-top: 14px;
        padding: 0 16px;
        border: 1px solid rgba(215,164,95,.28);
        border-radius: 999px;
        background: rgba(215,164,95,.08);
        color: #efc985;
        text-decoration: none;
        font-size: 10px;
        font-weight: 900;
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
            <h1 class="legal-title">Privacy<span>beleid.</span></h1>
            <p class="legal-intro">
                In dit privacybeleid leggen we uit welke persoonsgegevens Mashal Studio kan verwerken,
                waarom dat gebeurt en welke keuzes je daarbij hebt.
            </p>
        </header>

        <section class="legal-card">
            <h2>1. Welke gegevens we kunnen verwerken</h2>
            <p>Afhankelijk van hoe je Mashal Studio gebruikt, kunnen onder andere de volgende gegevens worden verwerkt:</p>
            <ul>
                <li>accountgegevens, zoals naam en e-mailadres;</li>
                <li>inlog- en beveiligingsgegevens, zoals loginmethode, apparaat, browser, IP-adres en tijdstip;</li>
                <li>optionele browserlocatie wanneer je daarvoor expliciet toestemming geeft;</li>
                <li>passkey-registraties en instellingen voor Authenticator-verificatie;</li>
                <li>door jou geüploade afbeeldingen en bestanden;</li>
                <li>inhoud die je zelf invoert in functies zoals AI-chat of andere tools;</li>
                <li>technische gegevens die nodig zijn om fouten, misbruik en beveiligingsincidenten te onderzoeken.</li>
            </ul>
        </section>

        <section class="legal-card">
            <h2>2. Waarom we gegevens verwerken</h2>
            <p>We gebruiken persoonsgegevens alleen voor legitieme doeleinden die met de dienst samenhangen, zoals:</p>
            <ul>
                <li>je account aanmaken en beheren;</li>
                <li>je veilig laten inloggen;</li>
                <li>fraude, misbruik en ongeautoriseerde toegang helpen voorkomen;</li>
                <li>de werking, prestaties en betrouwbaarheid van de website verbeteren;</li>
                <li>supportvragen beantwoorden;</li>
                <li>wettelijke verplichtingen naleven wanneer dat noodzakelijk is.</li>
            </ul>
        </section>

        <section class="legal-card">
            <h2>3. Beveiliging</h2>
            <p>
                Mashal Studio gebruikt beveiligingsmaatregelen zoals versleutelde verbindingen,
                sessiebeveiliging, passkeys en optionele tweestapsverificatie. Geen enkel systeem
                kan absolute veiligheid garanderen, maar we proberen persoonsgegevens passend te beschermen.
            </p>
        </section>

        <section class="legal-card">
            <h2>4. Cookies en lokale opslag</h2>
            <p>
                De website kan noodzakelijke cookies of vergelijkbare opslag gebruiken voor onder andere
                sessies, authenticatie, beveiliging en voorkeuren. Niet-noodzakelijke cookies, bijvoorbeeld
                voor advertenties of analytics, horen alleen te worden gebruikt wanneer daarvoor een geldige
                grondslag of toestemming bestaat.
            </p>
        </section>

        <section class="legal-card">
            <h2>5. Advertenties en externe diensten</h2>
            <p>
                Mashal Studio kan in de toekomst advertentie- of analysetiensten gebruiken, zoals Google AdSense.
                Zulke externe partijen kunnen volgens hun eigen voorwaarden en privacybeleid gegevens verwerken.
                Waar wettelijk vereist wordt hiervoor eerst toestemming gevraagd.
            </p>
        </section>

        <section class="legal-card">
            <h2>6. Bewaartermijnen</h2>
            <p>
                Gegevens worden niet langer bewaard dan redelijkerwijs nodig is voor het doel waarvoor ze zijn
                verzameld, tenzij een langere bewaartermijn noodzakelijk is vanwege beveiliging, geschillen of
                wettelijke verplichtingen.
            </p>
        </section>

        <section class="legal-card">
            <h2>7. Jouw rechten</h2>
            <p>
                Afhankelijk van de toepasselijke wetgeving kun je rechten hebben zoals inzage, correctie,
                verwijdering, beperking van verwerking, bezwaar en gegevensoverdraagbaarheid.
            </p>
            <p>
                Voor privacyvragen kun je contact opnemen via de gegevens op de
                <a class="legal-link" href="{{ route('contact') }}">contactpagina</a>.
            </p>
        </section>

        <section class="legal-card">
            <h2>8. Wijzigingen</h2>
            <p>
                Dit privacybeleid kan worden aangepast wanneer de website, functionaliteit of regelgeving verandert.
                De meest recente versie wordt op deze pagina gepubliceerd.
            </p>
        </section>

        <p class="legal-meta">
            Laatst bijgewerkt: 25 september 2026.
        </p>
    </div>
</section>
@endsection
