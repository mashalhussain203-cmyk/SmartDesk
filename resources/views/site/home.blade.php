@extends('layouts.site-layout')

@section('title', 'SmartDesk | Home')

@section('content')
<section class="hero">
    <div class="hero-inner">
        <div class="hero-copy">
            <span class="hero-kicker">Smart Business Platform</span>
            <h1>Organiseer je werk met vertrouwen.</h1>
            <p>SmartDesk helpt teams om processen, projecten, gebruikers en klantcontacten centraal te beheren.</p>
            <div class="hero-actions">
                <a class="primary-btn" href="{{ route('register') }}">Start nu</a>
                <a class="secondary-btn" href="#services">Bekijk diensten</a>
            </div>
        </div>

        <div class="hero-card">
            <div class="mini-panel">
                <span class="small-text">Actief vandaag</span>
                <strong>24</strong>
                <span>Gebruikers online</span>
            </div>
            <div class="mini-panel">
                <span class="small-text">Projecten</span>
                <strong>08</strong>
                <span>Open portfolio</span>
            </div>
            <div class="mini-panel">
                <span class="small-text">Systeem</span>
                <strong>Live</strong>
                <span>Alle teams verbonden</span>
            </div>
        </div>
    </div>
</section>

<section class="section" id="features">
    <div class="section-title">
        <h2>Waarom SmartDesk?</h2>
        <p>Een compleet platform voor teams die groei willen organiseren met meer overzicht, snelheid en controle.</p>
    </div>
    <div class="site-wrap">
        <div class="feature-grid">
            <div class="feature-card">
                <div class="icon">✦</div>
                <h3>Gebruikersbeheer</h3>
                <p>Maak en beheer accounts, rollen en toegang voor jouw organisatie.</p>
            </div>
            <div class="feature-card">
                <div class="icon">✓</div>
                <h3>Registratie</h3>
                <p>Nieuwe bezoekers kunnen eenvoudig een account aanmaken en direct starten.</p>
            </div>
            <div class="feature-card">
                <div class="icon">☰</div>
                <h3>Admin Dashboard</h3>
                <p>Een overzichtelijke beheerderspagina voor alle belangrijke bedrijfsgegevens.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" id="services">
    <div class="section-title">
        <h2>Onze diensten</h2>
        <p>Wij ondersteunen groei met slimme tools, begeleiding en operationele snelheid.</p>
    </div>
    <div class="site-wrap">
        <div class="feature-grid">
            <div class="feature-card">
                <div class="icon">01</div>
                <h3>Team onboarding</h3>
                <p>Wij helpen jouw team snel te werken met duidelijke processen en nieuwe accounts.</p>
            </div>
            <div class="feature-card">
                <div class="icon">02</div>
                <h3>Project management</h3>
                <p>Plan taken, volg vooruitgang en bouw een helder werkproces voor iedere afdeling.</p>
            </div>
            <div class="feature-card">
                <div class="icon">03</div>
                <h3>Business analytics</h3>
                <p>Vergelijk gebruikers, projecten en processen met heldere managementinformatie.</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="site-wrap">
        <div class="feature-grid">
            <div class="feature-card">
                <div class="icon">↗</div>
                <h3>Groeistrategie</h3>
                <p>Wij ontwikkelen een werkplan dat jouw organisatie sneller, beter en slimmer maakt.</p>
            </div>
            <div class="feature-card">
                <div class="icon">☏</div>
                <h3>Klantenservice</h3>
                <p>Wij verbeteren contactmomenten met een snel en vriendelijk klantproces.</p>
            </div>
            <div class="feature-card">
                <div class="icon">⚙</div>
                <h3>Automatisering</h3>
                <p>Automatiseer handmatige taken zodat teams meer tijd hebben voor echte groei.</p>
            </div>
        </div>
    </div>
</section>

<section class="section" id="pricing">
    <div class="section-title">
        <h2>Prijzen en plannen</h2>
        <p>Start klein, schaal later uit en werk met een platform dat groeit met jouw organisatie.</p>
    </div>
    <div class="site-wrap">
        <div class="feature-grid">
            <div class="feature-card">
                <div class="icon">Start</div>
                <h3>Starter</h3>
                <p>Voor kleine teams die snel starten met registratie, plannen en overzicht.</p>
                <p><strong>€19 / maand</strong></p>
            </div>
            <div class="feature-card">
                <div class="icon">Growth</div>
                <h3>Growth</h3>
                <p>Voor groeiende teams die meerdere processen en gebruikers willen beheren.</p>
                <p><strong>€49 / maand</strong></p>
            </div>
            <div class="feature-card">
                <div class="icon">Scale</div>
                <h3>Scale</h3>
                <p>Voor organisaties die volledige controle, rapportage en bedrijfsvoering willen.</p>
                <p><strong>€99 / maand</strong></p>
            </div>
        </div>
    </div>
</section>

<section class="cta-band">
    <div class="cta-content">
        <h2>Wij helpen jouw organisatie groeien.</h2>
        <p>Maak nu een gratis account aan en zet jouw eerste workflow binnen enkele minuten op.</p>
        <div class="hero-actions" style="justify-content:center;">
            <a class="primary-btn" href="{{ route('register') }}">Maak account aan</a>
        </div>
    </div>
</section>
@endsection
