@extends('layouts.site-layout')

@section('title', 'Mashal Studio | Over ons')

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
            <h1 class="legal-title">Over <span>ons.</span></h1>
            <p class="legal-intro">
                Mashal Studio is een digitaal platform dat creatieve tools, accountfuncties,
                beveiliging en slimme workflows samenbrengt in één omgeving.
            </p>
        </header>

        <div class="legal-grid">
            <section class="legal-card">
                <h2>Wat we bouwen</h2>
                <p>
                    We ontwikkelen een moderne webomgeving waarin gebruikers onder andere met afbeeldingen,
                    AI-functies, accountbeheer en beveiligingsopties kunnen werken.
                </p>
            </section>

            <section class="legal-card">
                <h2>Onze focus</h2>
                <p>
                    Gebruiksgemak, duidelijke vormgeving, privacy en beveiliging staan centraal.
                    Functionaliteit moet praktisch zijn zonder onnodige complexiteit voor de gebruiker.
                </p>
            </section>
        </div>

        <section class="legal-card">
            <h2>Beveiliging als onderdeel van het product</h2>
            <p>
                Mashal Studio ondersteunt meerdere moderne authenticatiemethoden, waaronder wachtwoorden,
                passkeys en Authenticator-verificatie. De bedoeling is gebruikers meer controle te geven
                over hoe zij hun account beschermen.
            </p>
        </section>

        <section class="legal-card">
            <h2>Ontwikkeling</h2>
            <p>
                Mashal Studio wordt actief ontwikkeld. Daardoor kunnen functies, ontwerp en beschikbaarheid
                in de loop van de tijd veranderen of worden uitgebreid.
            </p>
        </section>

        <section class="legal-card">
            <h2>Contact</h2>
            <p>
                Heb je een vraag, suggestie of probleem? Ga dan naar onze
                <a class="legal-link" href="{{ route('contact') }}">contactpagina</a>.
            </p>
        </section>
    </div>
</section>
@endsection
