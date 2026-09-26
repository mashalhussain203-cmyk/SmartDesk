@section('content')
<section class="legal-page">
    <div class="legal-shell">

        <header class="legal-hero">
            <span class="legal-kicker">Mashal Studio</span>

            <h1 class="legal-title">
                Over <span>ons.</span>
            </h1>

            <p class="legal-intro">
                Mashal Studio is een digitaal platform dat creatieve tools,
                accountfuncties, beveiliging en slimme workflows samenbrengt
                in één gebruiksvriendelijke omgeving.
            </p>
        </header>

        <div class="legal-grid">

            <section class="legal-card">
                <h2>Wat Mashal Studio biedt</h2>

                <p>
                    Mashal Studio biedt een moderne webomgeving waarin gebruikers
                    afbeeldingen kunnen uploaden, beheren en bewerken en gebruik
                    kunnen maken van verschillende account-, beveiligings-
                    en AI-functies.
                </p>
            </section>

            <section class="legal-card">
                <h2>Onze focus</h2>

                <p>
                    Gebruiksgemak, duidelijke vormgeving, privacy en beveiliging
                    staan centraal. Onze functies zijn ontworpen om praktisch
                    en toegankelijk te zijn zonder onnodige complexiteit.
                </p>
            </section>

        </div>

        <section class="legal-card">
            <h2>Afbeeldingen en creatieve tools</h2>

            <p>
                Mashal Studio biedt verschillende functies voor het werken met
                afbeeldingen, waaronder uploaden, beheren en bewerken.
                Afhankelijk van de beschikbare functies kunnen gebruikers
                bijvoorbeeld afbeeldingen aanpassen, verkleinen, bijsnijden,
                roteren, comprimeren of converteren.
            </p>
        </section>

        <section class="legal-card">
            <h2>Account en beveiliging</h2>

            <p>
                Mashal Studio ondersteunt verschillende moderne
                authenticatiemethoden en beveiligingsfuncties, waaronder
                wachtwoorden, passkeys en Authenticator-verificatie.
            </p>

            <p>
                Daarnaast kunnen ondersteunde externe inlogmethoden,
                zoals TikTok Login, worden gebruikt om veilig toegang
                te krijgen tot een Mashal Studio-account.
            </p>
        </section>

        <section class="legal-card">
            <h2>Onderhoud en verbeteringen</h2>

            <p>
                Mashal Studio wordt actief onderhouden en regelmatig verbeterd.
                Functies en het ontwerp kunnen van tijd tot tijd worden bijgewerkt
                om de betrouwbaarheid, veiligheid en gebruikerservaring
                verder te verbeteren.
            </p>
        </section>

        <section class="legal-card">
            <h2>Privacy en voorwaarden</h2>

            <p>
                Meer informatie over hoe we omgaan met persoonsgegevens
                vind je in ons
                <a
                    class="legal-link"
                    href="{{ route('privacy') }}"
                >
                    Privacybeleid
                </a>.
            </p>

            <p>
                De regels voor het gebruik van Mashal Studio staan beschreven
                in onze
                <a
                    class="legal-link"
                    href="{{ route('terms') }}"
                >
                    Gebruiksvoorwaarden
                </a>.
            </p>
        </section>

        <section class="legal-card">
            <h2>Contact</h2>

            <p>
                Heb je een vraag, suggestie of probleem?
                Neem dan contact met ons op via
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
                    contactpagina
                </a>.
            </p>
        </section>

    </div>
</section>
@endsection