@extends('layouts.site-layout')
@section('title', 'SmartDesk | Welkom')

@section('content')

{{-- ========================================================= --}}
{{-- HERO                                                       --}}
{{-- ========================================================= --}}

<section class="hero">

    <div class="hero-inner">

        {{-- HERO TEXT --}}

        <div class="hero-copy">

            <span class="hero-kicker">
                SmartDesk Auto Platform
            </span>

            <h1>
                Welkom bij SmartDesk.
            </h1>

            <p>
                Ontdek premium auto's, beheer je winkelwagen,
                plaats bestellingen en houd alles overzichtelijk
                bij vanuit je persoonlijke SmartDesk-account.
            </p>


            <div
                class="hero-actions"
                style="
                    display: flex;
                    gap: 12px;
                    flex-wrap: wrap;
                "
            >

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
        {{-- HERO CARDS                                                 --}}
        {{-- ========================================================= --}}

        <div class="hero-card">

            <div class="mini-panel">

                <span class="small-text">
                    Catalogus
                </span>

                <strong>
                    Premium
                </strong>

                <span>
                    Auto's voor iedere route
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
                    Eenvoudig via SmartDesk
                </span>

            </div>

        </div>

    </div>

</section>


{{-- ========================================================= --}}
{{-- SUCCESS MESSAGE                                            --}}
{{-- ========================================================= --}}

@if (session('success'))

    <section
        class="section"
        style="
            padding-top: 25px;
            padding-bottom: 0;
        "
    >

        <div class="site-wrap">

            <div class="success">

                <strong>
                    Gelukt!
                </strong>

                <br>

                {{ session('success') }}

            </div>

        </div>

    </section>

@endif


{{-- ========================================================= --}}
{{-- INTRO                                                      --}}
{{-- ========================================================= --}}

<section
    class="section"
    id="about"
>

    <div class="section-title">

        <h2>
            Alles voor jouw SmartDesk-account
        </h2>

        <p>
            Van auto's bekijken tot veilig bestellen:
            SmartDesk houdt het volledige proces overzichtelijk.
        </p>

    </div>


    <div class="site-wrap">

        <div class="feature-grid">

            {{-- CATALOG --}}

            <div class="feature-card">

                <div class="icon">
                    🚗
                </div>

                <h3>
                    Bekijk auto's
                </h3>

                <p>
                    Ontdek beschikbare auto's met duidelijke informatie
                    over merk, model, bouwjaar, brandstof en prijs.
                </p>

                <a
                    class="secondary-btn"
                    href="{{ route('catalog') }}"
                >
                    Naar catalogus
                </a>

            </div>


            {{-- CART --}}

            <div class="feature-card">

                <div class="icon">
                    🛒
                </div>

                <h3>
                    Winkelwagen
                </h3>

                <p>
                    Voeg auto's toe aan je winkelwagen en bekijk
                    aantallen, prijzen en je volledige totaalbedrag.
                </p>

                <a
                    class="secondary-btn"
                    href="{{ route('cart') }}"
                >
                    Bekijk winkelwagen
                </a>

            </div>


            {{-- ACCOUNT --}}

            <div class="feature-card">

                <div class="icon">
                    👤
                </div>

                <h3>
                    Persoonlijk account
                </h3>

                <p>
                    Beheer je persoonlijke gegevens,
                    wachtwoord en geplaatste bestellingen op één plek.
                </p>

                @auth

                    <a
                        class="secondary-btn"
                        href="{{ route('account') }}"
                    >
                        Mijn account
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

</section>


{{-- ========================================================= --}}
{{-- HOW IT WORKS                                               --}}
{{-- ========================================================= --}}

<section
    class="section"
    id="how-it-works"
>

    <div class="section-title">

        <h2>
            Zo werkt het
        </h2>

        <p>
            In een paar eenvoudige stappen van catalogus
            naar een bevestigde bestelling.
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
                    Kies een auto
                </h3>

                <p>
                    Bekijk de SmartDesk-catalogus en open
                    een auto voor meer informatie.
                </p>

            </div>


            {{-- STEP 2 --}}

            <div class="feature-card">

                <div class="icon">
                    02
                </div>

                <h3>
                    Voeg toe
                </h3>

                <p>
                    Voeg de gewenste auto toe aan je winkelwagen
                    en controleer je selectie.
                </p>

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
                    Log in, verifieer je e-mailadres
                    en rond daarna je bestelling veilig af.
                </p>

            </div>

        </div>

    </div>

</section>


{{-- ========================================================= --}}
{{-- SECURITY                                                   --}}
{{-- ========================================================= --}}

<section class="section">

    <div class="section-title">

        <h2>
            Veiligheid staat centraal
        </h2>

        <p>
            SmartDesk gebruikt verschillende stappen
            om je account en persoonsgegevens te beschermen.
        </p>

    </div>


    <div class="site-wrap">

        <div class="feature-grid">

            {{-- EMAIL VERIFICATION --}}

            <div class="feature-card">

                <div class="icon">
                    ✉
                </div>

                <h3>
                    E-mailverificatie
                </h3>

                <p>
                    Bij registratie of een gewijzigd e-mailadres
                    ontvang je een unieke 6-cijferige verificatiecode.
                </p>

            </div>


            {{-- PASSWORD --}}

            <div class="feature-card">

                <div class="icon">
                    🔐
                </div>

                <h3>
                    Wachtwoordbeveiliging
                </h3>

                <p>
                    Wijzig je wachtwoord vanuit je account
                    of herstel het via een tijdelijke resetlink.
                </p>

            </div>


            {{-- SECURITY EMAILS --}}

            <div class="feature-card">

                <div class="icon">
                    ✓
                </div>

                <h3>
                    Beveiligingsmeldingen
                </h3>

                <p>
                    Bij belangrijke wijzigingen ontvang je automatisch
                    een bevestiging op je e-mailadres.
                </p>

            </div>

        </div>

    </div>

</section>


{{-- ========================================================= --}}
{{-- ACCOUNT SECTION                                            --}}
{{-- ========================================================= --}}

<section class="section">

    <div class="site-wrap">

        @guest

            <div
                class="feature-card"
                style="
                    max-width: 850px;
                    margin: 0 auto;
                    text-align: center;
                    padding: 40px 30px;
                "
            >

                <div
                    class="icon"
                    style="margin: 0 auto 15px;"
                >
                    👤
                </div>

                <h2 style="margin: 0 0 12px;">
                    Nog geen SmartDesk-account?
                </h2>

                <p
                    class="form-sub"
                    style="
                        max-width: 620px;
                        margin: 0 auto 25px;
                    "
                >
                    Maak een account aan om je e-mailadres te verifiëren,
                    bestellingen te plaatsen en je bestelgeschiedenis te bekijken.
                </p>


                <div
                    class="hero-actions"
                    style="
                        justify-content: center;
                        display: flex;
                        gap: 12px;
                        flex-wrap: wrap;
                    "
                >

                    <a
                        class="primary-btn"
                        href="{{ route('register') }}"
                    >
                        Account aanmaken
                    </a>

                    <a
                        class="secondary-btn"
                        href="{{ route('login') }}"
                    >
                        Inloggen
                    </a>

                </div>

            </div>

        @else

            <div
                class="feature-card"
                style="
                    max-width: 850px;
                    margin: 0 auto;
                    padding: 35px 30px;
                "
            >

                <div
                    style="
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        gap: 25px;
                        flex-wrap: wrap;
                    "
                >

                    <div>

                        <span class="hero-kicker">
                            Ingelogd
                        </span>

                        <h2
                            style="
                                margin: 10px 0 8px;
                            "
                        >
                            Welkom terug, {{ Auth::user()->name }}.
                        </h2>

                        <p
                            class="form-sub"
                            style="margin-bottom: 0;"
                        >
                            Beheer je accountgegevens,
                            beveiliging en bestellingen vanuit je account.
                        </p>

                    </div>


                    <div>

                        @if (Auth::user()->email_verified_at)

                            <span
                                style="
                                    display: inline-block;
                                    padding: 8px 14px;
                                    border-radius: 20px;
                                    background: #e8f7ee;
                                    color: #187a42;
                                    font-weight: 600;
                                "
                            >
                                ✓ E-mail geverifieerd
                            </span>

                        @else

                            <span
                                style="
                                    display: inline-block;
                                    padding: 8px 14px;
                                    border-radius: 20px;
                                    background: #fff4df;
                                    color: #9a6500;
                                    font-weight: 600;
                                "
                            >
                                ⚠ E-mail niet geverifieerd
                            </span>

                        @endif

                    </div>

                </div>


                <div
                    class="hero-actions"
                    style="
                        margin-top: 25px;
                        display: flex;
                        gap: 12px;
                        flex-wrap: wrap;
                    "
                >

                    <a
                        class="primary-btn"
                        href="{{ route('account') }}"
                    >
                        Mijn account
                    </a>

                    <a
                        class="secondary-btn"
                        href="{{ route('catalog') }}"
                    >
                        Bekijk auto's
                    </a>

                </div>

            </div>

        @endguest

    </div>

</section>


{{-- ========================================================= --}}
{{-- CTA                                                        --}}
{{-- ========================================================= --}}

<section class="cta-band">

    <div class="cta-content">

        @guest

            <h2>
                Klaar om te beginnen?
            </h2>

            <p>
                Maak je SmartDesk-account aan,
                verifieer je e-mailadres en ontdek ons aanbod.
            </p>


            <div
                class="hero-actions"
                style="
                    justify-content: center;
                    display: flex;
                    gap: 12px;
                    flex-wrap: wrap;
                "
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
                    Bekijk catalogus
                </a>

            </div>

        @else

            <h2>
                Vind jouw volgende auto.
            </h2>

            <p>
                Bekijk de SmartDesk-catalogus en voeg
                jouw favoriete auto toe aan je winkelwagen.
            </p>


            <div
                class="hero-actions"
                style="
                    justify-content: center;
                    display: flex;
                    gap: 12px;
                    flex-wrap: wrap;
                "
            >

                <a
                    class="primary-btn"
                    href="{{ route('catalog') }}"
                >
                    Bekijk auto's
                </a>

                <a
                    class="secondary-btn"
                    href="{{ route('cart') }}"
                >
                    Mijn winkelwagen
                </a>

            </div>

        @endguest

    </div>

</section>

@endsection

