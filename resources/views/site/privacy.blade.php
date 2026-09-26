@extends('layouts.site-layout')

@section('title', 'Mashal Studio | Privacybeleid')

@push('styles')
<style>
    .legal-page {
        min-height: 72vh;
        padding: 86px 0 110px;
        background:
            radial-gradient(circle at 12% 4%, rgba(215, 164, 95, .10), transparent 26rem),
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
        border-bottom: 1px solid rgba(239, 201, 133, .35);
        transition: border-color .2s ease;
    }

    .legal-link:hover {
        border-color: rgba(239, 201, 133, .8);
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
        border: 1px solid rgba(215, 164, 95, .18);
        border-radius: 16px;
        background: rgba(215, 164, 95, .055);
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
        border: 1px solid rgba(215, 164, 95, .28);
        border-radius: 999px;
        background: rgba(215, 164, 95, .08);
        color: #efc985;
        text-decoration: none;
        font-size: 10px;
        font-weight: 900;
        transition:
            background .2s ease,
            border-color .2s ease,
            transform .2s ease;
    }

    .contact-button:hover {
        background: rgba(215, 164, 95, .14);
        border-color: rgba(215, 164, 95, .48);
        transform: translateY(-1px);
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
                Privacy<span>beleid.</span>
            </h1>

            <p class="legal-intro">
                In dit Privacybeleid leggen we uit welke persoonsgegevens Mashal Studio
                kan verwerken, waarom deze gegevens worden verwerkt, hoe we ermee omgaan
                en welke keuzes en rechten je hebt.
            </p>
        </header>

        <section class="legal-card">
            <h2>1. Welke gegevens we kunnen verwerken</h2>

            <p>
                Afhankelijk van hoe je Mashal Studio gebruikt, kunnen onder andere
                de volgende gegevens worden verwerkt:
            </p>

            <ul>
                <li>
                    accountgegevens, zoals je naam, e-mailadres en andere gegevens
                    die nodig zijn om je account te beheren;
                </li>

                <li>
                    inlog- en beveiligingsgegevens, zoals je loginmethode,
                    apparaat, browser, IP-adres en tijdstip van toegang;
                </li>

                <li>
                    optionele browserlocatie wanneer je hiervoor expliciet
                    toestemming geeft;
                </li>

                <li>
                    gegevens en instellingen die samenhangen met passkeys,
                    Authenticator-verificatie en andere beveiligingsfuncties;
                </li>

                <li>
                    door jou geüploade afbeeldingen en bestanden;
                </li>

                <li>
                    inhoud die je zelf invoert in functies zoals AI-chat
                    of andere tools binnen Mashal Studio;
                </li>

                <li>
                    technische gegevens die nodig zijn om fouten, misbruik,
                    beveiligingsproblemen of verdachte activiteiten te onderzoeken.
                </li>
            </ul>
        </section>

        <section class="legal-card">
            <h2>2. Inloggen met TikTok en andere externe diensten</h2>

            <p>
                Mashal Studio kan gebruikers de mogelijkheid bieden om in te loggen
                via externe authenticatieproviders, waaronder TikTok.
            </p>

            <p>
                Wanneer je ervoor kiest om via TikTok in te loggen, word je naar TikTok
                doorgestuurd om toestemming te geven voor de gegevens en machtigingen
                die voor de inlogfunctie nodig zijn.
            </p>

            <p>
                Bij gebruik van TikTok Login Kit kan Mashal Studio basisprofielinformatie
                ontvangen die TikTok beschikbaar stelt binnen de door jou goedgekeurde
                machtigingen. Dit kan bijvoorbeeld je displaynaam, profielfoto en een
                accountidentificatie omvatten die nodig is om je TikTok-account aan je
                Mashal Studio-account te koppelen.
            </p>

            <p>
                Mashal Studio ontvangt je TikTok-wachtwoord niet.
            </p>

            <p>
                Wanneer je een externe authenticatieprovider gebruikt, kunnen ook
                de voorwaarden en het privacybeleid van die externe aanbieder
                van toepassing zijn.
            </p>
        </section>

        <section class="legal-card">
            <h2>3. Waarom we persoonsgegevens verwerken</h2>

            <p>
                We verwerken persoonsgegevens voor doeleinden die samenhangen met
                het aanbieden, beveiligen en verbeteren van Mashal Studio, waaronder:
            </p>

            <ul>
                <li>je account aanmaken en beheren;</li>
                <li>je veilig laten registreren en inloggen;</li>
                <li>externe inlogmethoden, zoals TikTok Login, mogelijk maken;</li>
                <li>je account en bestanden aan jou koppelen;</li>
                <li>fraude, misbruik en ongeautoriseerde toegang helpen voorkomen;</li>
                <li>door jou gekozen functies en bewerkingen uitvoeren;</li>
                <li>technische fouten en beveiligingsproblemen onderzoeken;</li>
                <li>support- en privacyverzoeken beantwoorden;</li>
                <li>de betrouwbaarheid en beveiliging van Mashal Studio verbeteren;</li>
                <li>wettelijke verplichtingen naleven wanneer dat noodzakelijk is.</li>
            </ul>
        </section>

        <section class="legal-card">
            <h2>4. Beveiliging</h2>

            <p>
                Mashal Studio gebruikt passende technische en organisatorische
                beveiligingsmaatregelen om persoonsgegevens te beschermen.
            </p>

            <p>
                Deze maatregelen kunnen onder andere bestaan uit versleutelde
                HTTPS-verbindingen, beveiligde sessies, wachtwoordbeveiliging,
                passkeys, Authenticator-verificatie, tweestapsverificatie
                en toegangscontroles.
            </p>

            <p>
                Geen enkel digitaal systeem kan absolute veiligheid garanderen.
                We nemen echter passende maatregelen om persoonsgegevens te beschermen
                tegen ongeautoriseerde toegang, verlies, misbruik of wijziging.
            </p>
        </section>

        <section class="legal-card">
            <h2>5. Cookies en lokale opslag</h2>

            <p>
                Mashal Studio kan noodzakelijke cookies en vergelijkbare
                browseropslag gebruiken voor onder andere:
            </p>

            <ul>
                <li>sessies;</li>
                <li>authenticatie;</li>
                <li>beveiliging;</li>
                <li>voorkeuren;</li>
                <li>bescherming tegen misbruik.</li>
            </ul>

            <p>
                Wanneer niet-noodzakelijke cookies of vergelijkbare technologieën
                worden gebruikt waarvoor wettelijk toestemming vereist is,
                zal deze toestemming waar nodig eerst worden gevraagd.
            </p>
        </section>

        <section class="legal-card">
            <h2>6. Externe diensten</h2>

            <p>
                Mashal Studio kan gebruikmaken van externe dienstverleners
                die nodig zijn om bepaalde functies aan te bieden, bijvoorbeeld
                voor hosting, authenticatie, e-mailfunctionaliteit of andere
                technische voorzieningen.
            </p>

            <p>
                Wanneer je bewust een externe dienst zoals TikTok gebruikt,
                verwerkt die aanbieder gegevens volgens zijn eigen voorwaarden
                en privacybeleid.
            </p>

            <p>
                Persoonsgegevens worden alleen met externe partijen gedeeld
                wanneer dit noodzakelijk is voor de betreffende functionaliteit,
                wanneer je daarvoor toestemming hebt gegeven of wanneer dit
                wettelijk noodzakelijk is.
            </p>
        </section>

        <section class="legal-card">
            <h2>7. Geüploade afbeeldingen en andere inhoud</h2>

            <p>
                Afbeeldingen, bestanden en andere inhoud die je naar Mashal Studio
                uploadt, kunnen worden verwerkt om de functies uit te voeren
                die je zelf gebruikt.
            </p>

            <p>
                Je blijft verantwoordelijk voor de inhoud die je uploadt
                en dient alleen materiaal te gebruiken waarvoor je voldoende
                rechten of toestemming hebt.
            </p>
        </section>

        <section class="legal-card">
            <h2>8. Bewaartermijnen</h2>

            <p>
                Persoonsgegevens worden niet langer bewaard dan redelijkerwijs
                noodzakelijk is voor het doel waarvoor ze zijn verzameld.
            </p>

            <p>
                De bewaartermijn kan onder andere afhangen van de duur van je account,
                de aard van de gegevens, beveiligingsvereisten, fraudepreventie,
                wettelijke verplichtingen en eventuele lopende verzoeken of geschillen.
            </p>

            <p>
                Wanneer gegevens niet langer noodzakelijk zijn, kunnen ze worden
                verwijderd of waar passend geanonimiseerd.
            </p>
        </section>

        <section class="legal-card">
            <h2>9. Account- en gegevensverwijdering</h2>

            <p>
                Je kunt contact met ons opnemen wanneer je je Mashal Studio-account
                of bijbehorende persoonsgegevens wilt laten verwijderen.
            </p>

            <p>
                Na ontvangst en verificatie van een geldig verwijderingsverzoek
                worden de betreffende persoonsgegevens verwijderd voor zover
                we deze niet langer hoeven te bewaren vanwege wettelijke verplichtingen,
                beveiliging, fraudepreventie of andere geldige redenen.
            </p>

            <div class="contact-box">
                <strong>Verwijderings- of privacyverzoek</strong>

                <span>
                    mahsalhussain203@gmail.com
                </span>

                <a
                    class="contact-button"
                    href="mailto:mahsalhussain203@gmail.com?subject=Privacy-%20of%20verwijderingsverzoek%20Mashal%20Studio"
                >
                    Verzoek versturen
                </a>
            </div>
        </section>

        <section class="legal-card">
            <h2>10. Jouw privacyrechten</h2>

            <p>
                Afhankelijk van de toepasselijke wetgeving kun je verschillende
                rechten hebben met betrekking tot je persoonsgegevens.
                Dit kan onder andere het recht omvatten op:
            </p>

            <ul>
                <li>inzage in je persoonsgegevens;</li>
                <li>correctie van onjuiste gegevens;</li>
                <li>verwijdering van persoonsgegevens;</li>
                <li>beperking van bepaalde verwerkingen;</li>
                <li>bezwaar tegen bepaalde verwerkingen;</li>
                <li>gegevensoverdraagbaarheid waar dit van toepassing is;</li>
                <li>het intrekken van eerder gegeven toestemming.</li>
            </ul>

            <p>
                Voor het uitoefenen van deze rechten kun je contact opnemen via
                <a
                    class="legal-link"
                    href="mailto:mahsalhussain203@gmail.com"
                >
                    mahsalhussain203@gmail.com
                </a>.
            </p>

            <p>
                We kunnen je vragen voldoende informatie te verstrekken om je identiteit
                en het betreffende verzoek te kunnen controleren.
            </p>
        </section>

        <section class="legal-card">
            <h2>11. Externe links en diensten</h2>

            <p>
                Mashal Studio kan links of koppelingen bevatten naar websites
                en diensten van externe partijen.
            </p>

            <p>
                Deze externe partijen hanteren hun eigen voorwaarden
                en privacybeleid. We raden je aan deze te lezen wanneer
                je van dergelijke diensten gebruikmaakt.
            </p>
        </section>

        <section class="legal-card">
            <h2>12. Wijzigingen van dit Privacybeleid</h2>

            <p>
                We kunnen dit Privacybeleid aanpassen wanneer Mashal Studio,
                onze functionaliteiten, gebruikte diensten of toepasselijke
                wet- en regelgeving verandert.
            </p>

            <p>
                De meest recente versie wordt altijd op deze pagina gepubliceerd.
            </p>
        </section>

        <section class="legal-card">
            <h2>13. Contact</h2>

            <p>
                Heb je vragen over dit Privacybeleid, je persoonsgegevens
                of een privacyverzoek? Neem dan contact met ons op.
            </p>

            <div class="contact-box">
                <strong>Mashal Studio</strong>

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

            <p style="margin-top: 16px;">
                Je kunt ook onze
                <a
                    class="legal-link"
                    href="{{ route('contact') }}"
                >
                    contactpagina
                </a>
                bezoeken of onze
                <a
                    class="legal-link"
                    href="{{ route('terms') }}"
                >
                    Gebruiksvoorwaarden
                </a>
                bekijken.
            </p>
        </section>

        <p class="legal-meta">
            Laatst bijgewerkt: 26 september 2026.
        </p>

    </div>
</section>
@endsection