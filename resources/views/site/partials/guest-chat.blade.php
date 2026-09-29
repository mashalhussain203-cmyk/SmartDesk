<aside
    class="guest-chat"
    id="guest-chat"
    aria-label="AI-chat voor bezoekers"
    hidden
    data-endpoint="{{ route('guest-chat.message') }}"
>
    <section
        class="guest-chat__panel"
        id="guest-chat-panel"
        aria-labelledby="guest-chat-title"
        hidden
    >
        <header class="guest-chat__header">
            <div>
                <h2 id="guest-chat-title">Mashal AI</h2>
                <p>AI-assistent · geen login nodig</p>
            </div>

            <button
                type="button"
                class="guest-chat__close"
                aria-label="Chat sluiten"
            >×</button>
        </header>

        <div
            class="guest-chat__messages"
            role="log"
            aria-live="polite"
            aria-relevant="additions"
            aria-label="Chatberichten"
        >
            <p class="guest-chat__message">Hoi! Ik ben Mashal AI. Stel gerust je vraag — ik help met uitleg, ideeën, teksten, code en meer.</p>
        </div>

        <div
            class="guest-chat__suggestions"
            aria-label="Voorbeeldvragen"
        >
            <button
                type="button"
                data-question="Wat kun je allemaal voor mij doen?"
            >Wat kun je?</button>

            <button
                type="button"
                data-question="Help mij een professionele e-mail schrijven."
            >Tekst schrijven</button>

            <button
                type="button"
                data-question="Hoe kan ik afbeeldingen bewerken op Mashal Studio?"
            >Websitehulp</button>
        </div>

        <form class="guest-chat__form">
            <input
                type="text"
                aria-label="Je vraag"
                placeholder="Stel je vraag…"
                maxlength="2000"
                autocomplete="off"
                required
            >

            <button
                class="guest-chat__send"
                type="submit"
            >Stuur</button>
        </form>

        <p class="guest-chat__notice">
            Je berichten worden naar Groq gestuurd om een AI-antwoord
            te maken. AI kan fouten maken. Deel geen wachtwoorden
            of gevoelige gegevens.
        </p>
    </section>

    <button
        type="button"
        class="guest-chat__toggle"
        aria-expanded="false"
        aria-controls="guest-chat-panel"
    >Vraag het Mashal AI</button>
</aside>

<script src="{{ asset('js/guest-chat.js') }}?v=3" defer></script>