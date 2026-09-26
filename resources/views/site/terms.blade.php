@extends('layouts.site-layout')

@section('title', 'Mashal Studio | Voorwaarden')

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

    .legal-link {
        color: #efc985;
        text-decoration: none;
        border-bottom: 1px solid rgba(239,201,133,.35);
    }

    .legal-link:hover {
        border-color: rgba(239,201,133,.8);
    }

    .legal-meta {
        margin-top: 28px;
        color: #666d74;
        font-size: 10px;
        line-height: 1.6;
    }
</style>

@endpush

@section('content')
<section class="legal-page">
    <div class="legal-shell">

        <header class="legal-hero">
            <span class="legal-kicker">Mashal Studio</span>

            <h1 class="legal-title">
                Gebruiks<span>voorwaarden.</span>
            </h1>

            <p class="legal-intro">
                Deze gebruiksvoorwaarden zijn van toepassing op het gebruik van Mashal Studio
                en beschrijven de rechten en verantwoordelijkheden van gebruikers van onze dienst.
            </p>
        </header>

        <section class="legal-card">
            <h2>1. Gebruik van Mashal Studio</h2>

            <p>
                Mashal Studio biedt een online workspace waarmee gebruikers afbeeldingen kunnen
                uploaden, beheren en bewerken en gebruik kunnen maken van beschikbare AI-functionaliteiten.
            </p>

            <p>
                Je mag Mashal Studio uitsluitend gebruiken voor rechtmatige doeleinden en op een manier
                die de werking, beveiliging of beschikbaarheid van de dienst niet verstoort.
            </p>
        </section>

        <section class="legal-card">
            <h2>2. Accounts en beveiliging</h2>

            <p>
                Wanneer je een account gebruikt, ben je verantwoordelijk voor het beschermen van je
                accountgegevens en authenticatiemiddelen.
            </p>

            <p>
                Deel geen wachtwoorden, passkeys, Authenticator-codes of recovery codes met anderen.
                Neem contact met ons op wanneer je vermoedt dat iemand ongeautoriseerde toegang tot
                je account heeft verkregen.
            </p>
        </section>

        <section class="legal-card">
            <h2>3. Verboden gebruik</h2>

            <p>Het is onder andere niet toegestaan om:</p>

            <ul>
                <li>Mashal Studio te gebruiken voor illegale, frauduleuze of schadelijke activiteiten;</li>
                <li>beveiligingsmaatregelen te omzeilen of ongeautoriseerde toegang te verkrijgen;</li>
                <li>malware, schadelijke code, spam of misleidende inhoud te verspreiden;</li>
                <li>de beschikbaarheid of werking van Mashal Studio opzettelijk te verstoren;</li>
                <li>inbreuk te maken op intellectuele eigendomsrechten, privacyrechten of andere rechten van derden;</li>
                <li>de dienst te gebruiken op een manier die strijdig is met toepasselijke wet- en regelgeving.</li>
            </ul>
        </section>

        <section class="legal-card">
            <h2>4. Inhoud van gebruikers</h2>

            <p>
                Je behoudt de verantwoordelijkheid voor afbeeldingen, teksten en andere inhoud die je
                uploadt, invoert of verwerkt via Mashal Studio.
            </p>

            <p>
                Je verklaart dat je voldoende rechten of toestemming hebt voor materiaal dat je via
                onze dienst gebruikt.
            </p>
        </section>

        <section class="legal-card">
            <h2>5. Beschikbaarheid en onderhoud</h2>

            <p>
                Mashal Studio is een actieve online dienst die regelmatig wordt onderhouden en verbeterd.
                We kunnen functies aanpassen, verbeteren, toevoegen of verwijderen wanneer dit nodig is
                voor de werking, beveiliging of verdere verbetering van de dienst.
            </p>

            <p>
                De dienst kan tijdelijk geheel of gedeeltelijk niet beschikbaar zijn vanwege gepland
                onderhoud, beveiligingsmaatregelen, technische storingen of omstandigheden buiten onze controle.
            </p>
        </section>

        <section class="legal-card">
            <h2>6. Diensten en integraties van derden</h2>

            <p>
                Mashal Studio kan integraties of functionaliteiten bevatten die worden geleverd door
                externe dienstverleners of platforms.
            </p>

            <p>
                Voor het gebruik van dergelijke externe diensten kunnen aanvullende voorwaarden en
                privacyregels van de betreffende aanbieder gelden.
            </p>
        </section>

        <section class="legal-card">
            <h2>7. Aansprakelijkheid</h2>

            <p>
                We streven ernaar Mashal Studio veilig en betrouwbaar beschikbaar te stellen.
                We kunnen echter niet garanderen dat de dienst altijd zonder onderbrekingen, fouten
                of technische problemen functioneert.
            </p>

            <p>
                Voor zover toegestaan onder toepasselijk recht is onze aansprakelijkheid beperkt tot
                directe schade waarvoor wij wettelijk aansprakelijk kunnen worden gehouden.
            </p>
        </section>

        <section class="legal-card">
            <h2>8. Opschorting of beëindiging</h2>

            <p>
                We kunnen toegang tot Mashal Studio tijdelijk beperken, opschorten of beëindigen wanneer
                een gebruiker deze voorwaarden schendt, de beveiliging van de dienst in gevaar brengt,
                de dienst misbruikt of handelt in strijd met toepasselijke wet- en regelgeving.
            </p>
        </section>

        <section class="legal-card">
            <h2>9. Privacy</h2>

            <p>
                Informatie over de verwerking en bescherming van persoonsgegevens staat beschreven in ons
                <a class="legal-link" href="{{ route('privacy') }}">
                    privacybeleid
                </a>.
            </p>
        </section>

        <section class="legal-card">
            <h2>10. Wijzigingen van deze voorwaarden</h2>

            <p>
                We kunnen deze gebruiksvoorwaarden aanpassen wanneer Mashal Studio, onze dienstverlening
                of toepasselijke regelgeving verandert.
            </p>

            <p>
                De meest actuele versie van deze voorwaarden wordt altijd op deze pagina gepubliceerd.
            </p>
        </section>

        <section class="legal-card">
            <h2>11. Contact</h2>

            <p>
                Heb je vragen over deze gebruiksvoorwaarden of over Mashal Studio?
                Neem dan contact met ons op via onze
                <a class="legal-link" href="{{ route('contact') }}">
                    contactpagina
                </a>.
            </p>
        </section>

        <p class="legal-meta">
            Laatst bijgewerkt: 26 september 2026.
        </p>

    </div>
</section>
@endsection