<style>
    #guest-chat {
        --chat-gold: #e6bd7b;
        --chat-text: #f6f3ed;
        --chat-muted: #a5a7ae;
        position: fixed;
        right: 24px;
        bottom: max(24px, env(safe-area-inset-bottom));
        z-index: 1000;
        color: var(--chat-text);
        font: 14px/1.6 system-ui, -apple-system, sans-serif;
        text-align: left;
        color-scheme: dark;
    }

    #guest-chat,
    #guest-chat * {
        box-sizing: border-box;
    }

    #guest-chat[hidden],
    #guest-chat [hidden] {
        display: none !important;
    }

    #guest-chat button,
    #guest-chat input {
        font: inherit;
    }

    #guest-chat button {
        cursor: pointer;
    }

    #guest-chat button:disabled {
        cursor: wait;
        opacity: .5;
    }

    #guest-chat button:focus-visible,
    #guest-chat input:focus-visible,
    #guest-chat a:focus-visible {
        outline: 2px solid var(--chat-gold);
        outline-offset: 3px;
    }

    #guest-chat svg {
        display: block;
        width: 22px;
        height: 22px;
        flex-shrink: 0;
    }

    /* Zwevende chatknop */
    #guest-chat .guest-chat__toggle {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 64px;
        margin-left: auto;
        padding: 10px 19px 10px 10px;
        border: 1px solid #e6bd7b55;
        border-radius: 999px;
        background: linear-gradient(135deg, #29241c, #111317);
        color: var(--chat-text);
        box-shadow: 0 12px 40px #0008, inset 0 1px #ffffff0d;
        text-align: left;
        transition: transform .2s, border-color .2s;
    }

    #guest-chat .guest-chat__toggle:hover {
        transform: translateY(-3px);
        border-color: var(--chat-gold);
    }

    #guest-chat .guest-chat__icon {
        display: grid;
        place-items: center;
        flex-shrink: 0;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f7dfae, #ce9b50);
        color: #241a0c;
    }

    #guest-chat .guest-chat__toggle-copy {
        display: flex;
        flex-direction: column;
        gap: 1px;
    }

    #guest-chat .guest-chat__toggle-copy strong {
        font-size: 14px;
        font-weight: 650;
    }

    #guest-chat .guest-chat__toggle-copy small {
        color: #b9b4aa;
        font-size: 11px;
    }

    /* Chatvenster */
    #guest-chat .guest-chat__panel {
        display: flex;
        flex-direction: column;
        width: min(390px, calc(100vw - 32px));
        height: 560px;
        max-height: calc(100dvh - 120px);
        margin-bottom: 14px;
        overflow: hidden;
        border: 1px solid #e6bd7b33;
        border-radius: 24px;
        background: #111318;
        box-shadow: 0 24px 80px #0009;
    }

    #guest-chat .guest-chat__header {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
        padding: 18px;
        border-bottom: 1px solid #ffffff0d;
        background: linear-gradient(120deg, #252119, #14161b);
    }

    #guest-chat .guest-chat__header h2 {
        margin: 0;
        color: var(--chat-text);
        font: 650 16px/1.4 system-ui, sans-serif;
        letter-spacing: -.3px;
    }

    #guest-chat .guest-chat__header p {
        margin: 3px 0 0;
        color: #b9b5ae;
        font-size: 12px;
    }

    #guest-chat .guest-chat__close {
        display: grid;
        place-items: center;
        flex-shrink: 0;
        width: 44px;
        height: 44px;
        margin-left: auto;
        padding: 0;
        border: 1px solid #ffffff12;
        border-radius: 50%;
        background: #ffffff05;
        color: #d4d0c8;
    }

    #guest-chat .guest-chat__close:hover {
        background: #ffffff12;
    }

    /* Berichten */
    #guest-chat .guest-chat__messages {
        flex: 1;
        min-height: 70px;
        padding: 20px 18px 8px;
        overflow-y: auto;
        overscroll-behavior: contain;
        scrollbar-width: thin;
        scrollbar-color: #49443a transparent;
    }

    #guest-chat .guest-chat__message {
        width: fit-content;
        max-width: 94%;
        margin: 0 0 14px;
        padding: 12px 15px;
        border: 1px solid #ffffff0b;
        border-radius: 4px 17px 17px;
        background: #1c1f26;
        color: #e8e6e2;
        font-size: 14px;
        line-height: 1.7;
        overflow-wrap: anywhere;
        white-space: pre-wrap;
    }

    #guest-chat .guest-chat__message--user {
        margin-left: auto;
        border: 0;
        border-radius: 17px 4px 17px 17px;
        background: #e6bd7b;
        color: #241b0f;
    }

    #guest-chat .guest-chat__message a {
        display: block;
        margin-top: 8px;
        color: #f0cd93;
        text-decoration: underline;
    }

    /* Voorbeeldvragen */
    #guest-chat .guest-chat__suggestions {
        display: flex;
        flex-wrap: wrap;
        flex-shrink: 0;
        gap: 7px;
        padding: 8px 18px 14px;
    }

    #guest-chat .guest-chat__suggestions button {
        padding: 8px 11px;
        border: 1px solid #ffffff18;
        border-radius: 10px;
        background: #ffffff03;
        color: #decba9;
        font-size: 12px;
    }

    #guest-chat .guest-chat__suggestions button:hover {
        border-color: #e6bd7b66;
        background: #e6bd7b12;
    }

    /* Bericht invoeren */
    #guest-chat .guest-chat__form {
        display: flex;
        align-items: center;
        flex-shrink: 0;
        gap: 8px;
        margin: 0 14px;
        padding: 6px;
        border: 1px solid #ffffff24;
        border-radius: 16px;
        background: #090b0e;
    }

    #guest-chat .guest-chat__form:focus-within {
        border-color: #e6bd7b88;
    }

    #guest-chat .guest-chat__form input {
        width: 100%;
        min-width: 0;
        padding: 10px;
        border: 0;
        border-radius: 10px;
        background: transparent;
        color: var(--chat-text);
        font-size: 16px;
    }

    #guest-chat .guest-chat__form input::placeholder {
        color: #92969e;
    }

    #guest-chat .guest-chat__send {
        display: grid;
        place-items: center;
        flex-shrink: 0;
        width: 44px;
        height: 44px;
        padding: 0;
        border: 0;
        border-radius: 12px;
        background: linear-gradient(135deg, #f1d39b, #d2a15b);
        color: #21180b;
    }

    #guest-chat .guest-chat__notice {
        flex-shrink: 0;
        margin: 0;
        padding: 12px 18px 15px;
        color: var(--chat-muted);
        font-size: 10px;
        line-height: 1.5;
        text-align: center;
    }

    @media (max-width: 480px) {
        #guest-chat {
            right: 16px;
            bottom: max(16px, env(safe-area-inset-bottom));
        }

        #guest-chat .guest-chat__panel {
            max-height: calc(100dvh - 112px);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #guest-chat .guest-chat__toggle {
            transition: none;
        }
    }
</style>

<aside
    class="guest-chat"
    id="guest-chat"
    aria-label="Mashal AI-chat"
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
            <span class="guest-chat__icon" aria-hidden="true">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="m12 3 2.6 6.4L21 12l-6.4 2.6L12 21l-2.6-6.4L3 12l6.4-2.6L12 3Z"/>
                </svg>
            </span>

            <div>
                <h2 id="guest-chat-title">Mashal AI</h2>
                <p>Je AI-assistent · zonder inloggen</p>
            </div>

            <button
                type="button"
                class="guest-chat__close"
                aria-label="Chat sluiten"
            >
                <svg
                    aria-hidden="true"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                >
                    <path d="m6 6 12 12M18 6 6 18"/>
                </svg>
            </button>
        </header>

        <div
            class="guest-chat__messages"
            role="log"
            aria-live="polite"
            aria-relevant="additions"
            aria-label="Chatberichten"
        >
            <p class="guest-chat__message">Hoi, ik ben Mashal AI.

Waar kan ik je mee helpen? Stel een vraag, laat me meedenken of vraag hulp bij Mashal Studio.</p>
        </div>

        <div
            class="guest-chat__suggestions"
            aria-label="Voorbeeldvragen"
        >
            <button
                type="button"
                data-question="Wat kun je allemaal voor mij doen?"
            >Ontdek wat ik kan</button>

            <button
                type="button"
                data-question="Help mij een professionele e-mail schrijven."
            >Help met schrijven</button>

            <button
                type="button"
                data-question="Hoe kan ik afbeeldingen bewerken op Mashal Studio?"
            >Hulp bij de website</button>
        </div>

        <form class="guest-chat__form">
            <input
                type="text"
                aria-label="Je bericht aan Mashal AI"
                placeholder="Vraag het Mashal AI…"
                maxlength="2000"
                autocomplete="off"
                required
            >

            <button
                class="guest-chat__send"
                type="submit"
                aria-label="Bericht versturen"
            >
                <svg
                    aria-hidden="true"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M12 19V5m-6 6 6-6 6 6"/>
                </svg>
            </button>
        </form>

        <p class="guest-chat__notice">
            AI kan fouten maken. Controleer belangrijke informatie.
        </p>
    </section>

    <button
        type="button"
        class="guest-chat__toggle"
        aria-expanded="false"
        aria-controls="guest-chat-panel"
        aria-label="Chatten met Mashal AI"
    >
        <span class="guest-chat__icon" aria-hidden="true">
            <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.7"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8v.5Z"/>
                <path d="m12.5 7 .9 2.6L16 10.5l-2.6.9-.9 2.6-.9-2.6-2.6-.9 2.6-.9.9-2.6Z"/>
            </svg>
        </span>

        <span class="guest-chat__toggle-copy">
            <strong>Chat met Mashal AI</strong>
            <small>Stel gerust je vraag</small>
        </span>
    </button>
</aside>

<script src="{{ asset('js/guest-chat.js') }}?v=5" defer></script>