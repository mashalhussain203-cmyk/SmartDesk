@extends('layouts.site-layout')

@section('title', 'Over ons | Mashal Studio')

@push('styles')
<style>
    .legal-page {
        min-height: 100vh;
        padding: 72px 18px 90px;
        color: #f5f5f7;
        background:
            radial-gradient(circle at 12% 4%, rgba(122, 108, 255, .10), transparent 26rem),
            radial-gradient(circle at 92% 16%, rgba(66, 165, 255, .045), transparent 28rem),
            #08090b;
    }

    .legal-shell {
        width: min(100% - 16px, 980px);
        margin: 0 auto;
    }

    .legal-hero {
        margin-bottom: 30px;
    }

    .legal-kicker {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
        color: #a99fff;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .18em;
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
        color: #ffffff;
        font-size: clamp(40px, 6vw, 72px);
        line-height: .98;
        letter-spacing: -.055em;
    }

    .legal-title span,
    .legal-highlight,
    .legal-link {
        color: #a99fff;
    }

    .legal-intro {
        max-width: 760px;
        margin: 18px 0 0;
        color: #a6a8ae;
        font-size: 15px;
        line-height: 1.8;
    }

    .legal-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 16px;
    }

    .legal-card {
        margin-bottom: 16px;
        padding: 24px;
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: 20px;
        background:
            linear-gradient(
                145deg,
                rgba(255, 255, 255, .04),
                rgba(255, 255, 255, .015)
            );
        box-shadow: 0 16px 44px rgba(0, 0, 0, .18);
    }

    .legal-card h2 {
        margin: 0 0 10px;
        color: #ffffff;
        font-size: 19px;
        letter-spacing: -.02em;
    }

    .legal-card p {
        margin: 0;
        color: #8f949c;
        font-size: 13px;
        line-height: 1.8;
    }

    .legal-card p + p {
        margin-top: 14px;
    }

    .legal-link {
        border-bottom: 1px solid rgba(169, 159, 255, .35);
        text-decoration: none;
        transition:
            color .2s ease,
            border-color .2s ease;
    }

    .legal-link:hover {
        color: #c3bcff;
        border-bottom-color: rgba(169, 159, 255, .80);
    }

    .contact-box {
        border-color: rgba(122, 108, 255, .18);
        background: rgba(122, 108, 255, .055);
    }

    .contact-button {
        border-color: rgba(122, 108, 255, .28);
        background: rgba(122, 108, 255, .08);
        color: #a99fff;
    }

    .contact-button:hover {
        border-color: rgba(122, 108, 255, .48);
        background: rgba(122, 108, 255, .14);
    }

    @media (max-width: 720px) {
        .legal-page {
            padding: 50px 12px 70px;
        }

        .legal-grid {
            grid-template-columns: 1fr;
        }

        .legal-card {
            padding: 20px;
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
                {{ __('Over') }} <span>{{ __('ons.') }}</span>
            </h1>

            <p class="legal-intro">
                {{ __('Mashal Studio is een digitaal platform dat creatieve tools, accountfuncties, beveiliging en slimme workflows samenbrengt in één gebruiksvriendelijke omgeving.') }}
            </p>
        </header>

        <div class="legal-grid">

            <section class="legal-card">
                <h2>{{ __('Wat Mashal Studio biedt') }}</h2>

                <p>
                    {{ __('Mashal Studio biedt een moderne webomgeving waarin gebruikers afbeeldingen kunnen uploaden, beheren en bewerken en gebruik kunnen maken van verschillende account-, beveiligings- en AI-functies.') }}
                </p>
            </section>

            <section class="legal-card">
                <h2>{{ __('Onze focus') }}</h2>

                <p>
                    {{ __('Gebruiksgemak, duidelijke vormgeving, privacy en beveiliging staan centraal. Onze functies zijn ontworpen om praktisch en toegankelijk te zijn zonder onnodige complexiteit.') }}
                </p>
            </section>

        </div>

        <section class="legal-card">
            <h2>{{ __('Afbeeldingen en creatieve tools') }}</h2>

            <p>
                {{ __('Mashal Studio biedt verschillende functies voor het werken met afbeeldingen, waaronder uploaden, beheren en bewerken. Afhankelijk van de beschikbare functies kunnen gebruikers bijvoorbeeld afbeeldingen aanpassen, verkleinen, bijsnijden, roteren, comprimeren of converteren.') }}
            </p>
        </section>

        <section class="legal-card">
            <h2>{{ __('Account en beveiliging') }}</h2>

            <p>
                {{ __('Mashal Studio ondersteunt verschillende moderne authenticatiemethoden en beveiligingsfuncties, waaronder wachtwoorden, passkeys en Authenticator-verificatie.') }}
            </p>

            <p>
                {{ __('Daarnaast kunnen ondersteunde externe inlogmethoden, zoals TikTok Login, worden gebruikt om veilig toegang te krijgen tot een Mashal Studio-account.') }}
            </p>
        </section>

        <section class="legal-card">
            <h2>{{ __('Onderhoud en verbeteringen') }}</h2>

            <p>
                {{ __('Mashal Studio wordt actief onderhouden en regelmatig verbeterd. Functies en het ontwerp kunnen van tijd tot tijd worden bijgewerkt om de betrouwbaarheid, veiligheid en gebruikerservaring verder te verbeteren.') }}
            </p>
        </section>

        <section class="legal-card">
            <h2>{{ __('Privacy en voorwaarden') }}</h2>

            <p>
                {{ __('Meer informatie over hoe we omgaan met persoonsgegevens vind je in ons') }}
                <a
                    class="legal-link"
                    href="{{ route('privacy') }}"
                >
                    {{ __('Privacybeleid') }}
                </a>.
            </p>

            <p>
                {{ __('De regels voor het gebruik van Mashal Studio staan beschreven in onze') }}
                <a
                    class="legal-link"
                    href="{{ route('terms') }}"
                >
                    {{ __('Gebruiksvoorwaarden') }}
                </a>.
            </p>
        </section>

        <section class="legal-card">
            <h2>{{ __('Contact') }}</h2>

            <p>
                {{ __('Heb je een vraag, suggestie of probleem? Neem dan contact met ons op via') }}
                <a
                    class="legal-link"
                    href="mailto:mahsalhussain203@gmail.com"
                >
                    mahsalhussain203@gmail.com
                </a>
                of bezoek onze
                <a
                    class="legal-link"
                    href="{{ route('contact') }}"
                >
                    {{ __('contactpagina') }}
                </a>.
            </p>
        </section>

    </div>
</section>
@endsection
