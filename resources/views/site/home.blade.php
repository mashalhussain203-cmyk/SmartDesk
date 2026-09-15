@extends('layouts.site-layout')

@section('title', 'SmartDesk | Home')

@section('content')

{{-- ========================================================= --}}
{{-- HERO                                                       --}}
{{-- ========================================================= --}}

<section class="hero">

```
<div class="hero-inner">

    <div class="hero-copy">

        <span class="hero-kicker">
            SmartDesk Auto Platform
        </span>

        <h1>
            Vind jouw volgende auto met vertrouwen.
        </h1>

        <p>
            Bekijk ons aanbod, voeg jouw favoriete auto toe aan de winkelwagen
            en beheer bestellingen eenvoudig vanuit één persoonlijk SmartDesk-account.
        </p>

        <div class="hero-actions">

            <a
                class="primary-btn"
                href="{{ route('catalog') }}"
            >
                Bekijk auto's
            </a>

            @guest
                <a
                    class="secondary-btn"
                    href="{{ route('register') }}"
                >
                    Account aanmaken
                </a>
            @else
                <a
                    class="secondary-btn"
                    href="{{ route('account') }}"
                >
                    Mijn account
                </a>
            @endguest

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- HERO INFORMATION CARDS                                    --}}
    {{-- ========================================================= --}}

    <div class="hero-card">

        <div class="mini-panel">

            <span class="small-text">
                Catalogus
            </span>

            <strong>
                04
            </strong>

            <span>
                Premium auto's beschikbaar
            </span>

        </div>


        <div class="mini-panel">

            <span class="small-text">
                Account
            </span>

            <strong>
                Veilig
            </strong>

            <span>
                Met e-mailverificatie
            </span>

        </div>


        <div class="mini-panel">

            <span class="small-text">
                Bestellen
            </span>

            <strong>
                Online
            </strong>

            <span>
                Direct via SmartDesk
            </span>

        </div>

    </div>

</div>
```

</section>

{{-- ========================================================= --}}
{{-- WHY SMARTDESK                                              --}}
{{-- ========================================================= --}}

<section
    class="section"
    id="features"
>

```
<div class="section-title">

    <h2>
        Waarom SmartDesk?
    </h2>

    <p>
        Een eenvoudige manier om auto's te bekijken,
        je account veilig te beheren en bestellingen centraal bij te houden.
    </p>

</div>


<div class="site-wrap">

    <div class="feature-grid">

        {{-- ACCOUNT --}}

        <div class="feature-card">

            <div class="icon">
                ✓
            </div>

            <h3>
                Veilig account
            </h3>

            <p>
                Maak een persoonlijk account aan en bevestig je
                e-mailadres met een veilige verificatiecode.
            </p>

        </div>


        {{-- CATALOG --}}

        <div class="feature-card">

            <div class="icon">
                🚗
            </div>

            <h3>
                Duidelijke catalogus
            </h3>

            <p>
                Bekijk auto's met informatie over merk,
                model, bouwjaar, brandstof en prijs.
            </p>

        </div>


        {{-- ORDERS --}}

        <div class="feature-card">

            <div class="icon">
                ☰
            </div>

            <h3>
                Bestellingen beheren
            </h3>

            <p>
                Bekijk je geplaatste bestellingen,
                bestelnummer, totaalbedrag en actuele status
                vanuit je account.
            </p>

        </div>

    </div>

</div>
```

</section>

{{-- ========================================================= --}}
{{-- HOW IT WORKS                                               --}}
{{-- ========================================================= --}}

<section
    class="section"
    id="services"
>

```
<div class="section-title">

    <h2>
        Zo werkt SmartDesk
    </h2>

    <p>
        Van auto bekijken tot bestelling plaatsen
        in een paar duidelijke stappen.
    </p>

</div>


<div class="site-wrap">

    <div class="feature-grid">

        {{-- STEP 1 --}}

        <div class="feature-card">

            <div class="icon">
                01
            </div>

            <h3>
                Bekijk het aanbod
            </h3>

            <p>
                Ga naar de catalogus en ontdek de beschikbare auto's
                met duidelijke productinformatie en prijzen.
            </p>

            <a
                class="secondary-btn"
                href="{{ route('catalog') }}"
            >
                Naar catalogus
            </a>

        </div>


        {{-- STEP 2 --}}

        <div class="feature-card">

            <div class="icon">
                02
            </div>

            <h3>
                Voeg toe aan je winkelwagen
            </h3>

            <p>
                Kies een auto en voeg deze toe aan je winkelwagen.
                Bekijk daarna je selectie en het totale bedrag.
            </p>

            <a
                class="secondary-btn"
                href="{{ route('cart') }}"
            >
                Bekijk winkelwagen
            </a>

        </div>


        {{-- STEP 3 --}}

        <div class="feature-card">

            <div class="icon">
                03
            </div>

            <h3>
                Plaats je bestelling
            </h3>

            <p>
                Log in, verifieer je e-mailadres en rond je bestelling
                veilig af via de SmartDesk-checkout.
            </p>

            @auth
                <a
                    class="secondary-btn"
                    href="{{ route('account') }}"
                >
                    Naar mijn account
                </a>
            @else
                <a
                    class="secondary-btn"
                    href="{{ route('login') }}"
                >
                    Inloggen
                </a>
            @endauth

        </div>

    </div>

</div>
```

</section>

{{-- ========================================================= --}}
{{-- ACCOUNT & SECURITY                                        --}}
{{-- ========================================================= --}}

<section class="section">

```
<div class="section-title">

    <h2>
        Jouw account, jouw controle
    </h2>

    <p>
        Beheer je gegevens en beveiliging eenvoudig vanuit je persoonlijke account.
    </p>

</div>


<div class="site-wrap">

    <div class="feature-grid">

        {{-- PERSONAL DATA --}}

        <div class="feature-card">

            <div class="icon">
                👤
            </div>

            <h3>
                Persoonlijke gegevens
            </h3>

            <p>
                Pas je naam en e-mailadres eenvoudig aan
                vanuit je persoonlijke accountpagina.
            </p>

        </div>


        {{-- EMAIL VERIFICATION --}}

        <div class="feature-card">

            <div class="icon">
                ✉
            </div>

            <h3>
                E-mailverificatie
            </h3>

            <p>
                Nieuwe en gewijzigde e-mailadressen worden bevestigd
                met een unieke verificatiecode.
            </p>

        </div>


        {{-- PASSWORD SECURITY --}}

        <div class="feature-card">

            <div class="icon">
                🔐
            </div>

            <h3>
                Wachtwoordbeveiliging
            </h3>

            <p>
                Wijzig je wachtwoord vanuit je account
                of herstel het veilig via een tijdelijke resetlink.
            </p>

        </div>

    </div>

</div>


</section>

{{-- ========================================================= --}}
{{-- SHOPPING EXPERIENCE                                       --}}
{{-- ========================================================= --}}

<section class="section">


<div class="section-title">

    <h2>
        Van selectie tot bestelling
    </h2>

    <p>
        SmartDesk houdt het bestelproces overzichtelijk
        vanaf de eerste klik tot de bevestigingsmail.
    </p>

</div>


<div class="site-wrap">

    <div class="feature-grid">

        {{-- CART --}}

        <div class="feature-card">

            <div class="icon">
                🛒
            </div>

            <h3>
                Winkelwagen
            </h3>

            <p>
                Bekijk geselecteerde auto's,
                aantallen, subtotale bedragen en het volledige totaal.
            </p>

        </div>


        {{-- CHECKOUT --}}

        <div class="feature-card">

            <div class="icon">
                ✓
            </div>

            <h3>
                Veilige checkout
            </h3>

            <p>
                Alleen ingelogde gebruikers met een geverifieerd
                e-mailadres kunnen een bestelling plaatsen.
            </p>

        </div>


        {{-- CONFIRMATION --}}

        <div class="feature-card">

            <div class="icon">
                ✉
            </div>

            <h3>
                Bestelbevestiging
            </h3>

            <p>
                Na het plaatsen van een bestelling ontvang je
                automatisch een bevestiging met je bestelnummer en overzicht.
            </p>

        </div>

    </div>

</div>
```

</section>

{{-- ========================================================= --}}
{{-- FEATURED CARS                                              --}}
{{-- ========================================================= --}}

<section
    class="section"
    id="catalog"
>

```
<div class="section-title">

    <h2>
        Ontdek onze auto's
    </h2>

    <p>
        Premium modellen voor dagelijks gebruik,
        zakelijke ritten en lange reizen.
    </p>

</div>


<div class="site-wrap">

    <div class="feature-grid">

        <div class="feature-card">

            <div class="icon">
                Audi
            </div>

            <h3>
                Audi A6 Sportback
            </h3>

            <p>
                Premium comfort, hybride aandrijving
                en moderne technologie.
            </p>

            <p>
                <strong>
                    Vanaf €48.990
                </strong>
            </p>

        </div>


        <div class="feature-card">

            <div class="icon">
                BMW
            </div>

            <h3>
                BMW X5
            </h3>

            <p>
                Een ruime SUV met krachtige prestaties
                en een luxe uitstraling.
            </p>

            <p>
                <strong>
                    Vanaf €67.850
                </strong>
            </p>

        </div>


        <div class="feature-card">

            <div class="icon">
                VW
            </div>

            <h3>
                Volkswagen Golf GTI
            </h3>

            <p>
                Sportief, praktisch en geschikt
                voor dagelijks gebruik.
            </p>

            <p>
                <strong>
                    Vanaf €34.990
                </strong>
            </p>

        </div>

    </div>


    <div
        class="hero-actions"
        style="
            justify-content: center;
            margin-top: 30px;
        "
    >

        <a
            class="primary-btn"
            href="{{ route('catalog') }}"
        >
            Bekijk volledige catalogus
        </a>

    </div>

</div>


</section>

{{-- ========================================================= --}}
{{-- CTA                                                        --}}
{{-- ========================================================= --}}

<section class="cta-band">


<div class="cta-content">

    @guest

        <h2>
            Klaar om SmartDesk te gebruiken?
        </h2>

        <p>
            Maak gratis een account aan,
            verifieer je e-mailadres en ontdek ons volledige aanbod.
        </p>

        <div
            class="hero-actions"
            style="justify-content: center;"
        >

            <a
                class="primary-btn"
                href="{{ route('register') }}"
            >
                Maak account aan
            </a>

            <a
                class="secondary-btn"
                href="{{ route('catalog') }}"
            >
                Bekijk auto's
            </a>

        </div>

    @else

        <h2>
            Welkom terug, {{ Auth::user()->name }}.
        </h2>

        <p>
            Bekijk het nieuwste aanbod of ga direct naar je account
            om je gegevens en bestellingen te beheren.
        </p>

        <div
            class="hero-actions"
            style="justify-content: center;"
        >

            <a
                class="primary-btn"
                href="{{ route('catalog') }}"
            >
                Bekijk auto's
            </a>

            <a
                class="secondary-btn"
                href="{{ route('account') }}"
            >
                Mijn account
            </a>

        </div>

    @endguest

</div>


</section>

@endsection