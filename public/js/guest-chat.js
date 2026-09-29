(() => {
    const chat = document.getElementById('guest-chat');
    if (!chat || chat.dataset.initialized) return;
    chat.dataset.initialized = 'true';
    const panel = chat.querySelector('.guest-chat__panel');
    const toggle = chat.querySelector('.guest-chat__toggle');
    const input = chat.querySelector('textarea');
    const messages = chat.querySelector('.guest-chat__messages');
    const body = chat.querySelector('.gc-body');
    const welcome = chat.querySelector('.gc-welcome');
    const send = chat.querySelector('.guest-chat__send');
    const reset = chat.querySelector('.gc-reset');
    const confirmation = chat.querySelector('.gc-reset-confirm');
    const expand = chat.querySelector('.gc-expand');
    const suggestions = chat.querySelectorAll('[data-question]');
    let history = [];
    let busy = false;

    function viewport() {
        const view = window.visualViewport;
        if (!view) return;
        // Leave browser zoom behavior intact for users who magnify the page.
        if (view.scale !== 1) return;
        chat.style.setProperty('--gc-viewport-height', `${view.height}px`);
        const keyboard = Math.max(0, window.innerHeight - view.height - view.offsetTop);
        chat.style.setProperty('--gc-keyboard-bottom', `${keyboard + 16}px`);
    }

    function resizeInput() {
        input.style.height = '44px';
        input.style.height = `${Math.min(110, Math.max(44, input.scrollHeight))}px`;
    }

    function scrollToEnd() { body.scrollTop = body.scrollHeight; }

    function setOpen(open) {
        panel.hidden = !open;
        chat.dataset.open = String(open);
        toggle.setAttribute('aria-expanded', String(open));
        if (open) {
            viewport();
            // Focus the close control on mobile so opening the chat does not open the keyboard.
            if (window.matchMedia('(max-width: 699px)').matches) chat.querySelector('.guest-chat__close').focus();
            else (chat.dataset.mode === 'human' ? chat.querySelector('.lc-input') : input).focus();
        } else toggle.focus();
    }

    function appendMessage(text, user = false, copyable = false) {
        welcome.hidden = true;
        const turn = document.createElement('div');
        turn.className = `gc-turn${user ? ' gc-turn--user' : ''}`;
        const label = document.createElement('span');
        label.className = 'gc-speaker';
        label.textContent = user ? 'JIJ' : 'MASHAL AI';
        const message = document.createElement('p');
        message.className = `guest-chat__message${user ? ' guest-chat__message--user' : ''}`;
        message.textContent = text;
        turn.append(label, message);
        if (copyable) {
            const copy = document.createElement('button');
            copy.type = 'button';
            copy.className = 'gc-copy';
            copy.textContent = 'Kopieer antwoord';
            copy.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(text);
                    copy.textContent = 'Gekopieerd';
                } catch {
                    copy.textContent = 'Selecteer de tekst om te kopiëren';
                }
                window.setTimeout(() => { copy.textContent = 'Kopieer antwoord'; }, 2500);
            });
            turn.append(copy);
        }
        messages.append(turn);
        while (messages.children.length > 40) messages.firstElementChild.remove();
        scrollToEnd();
    }

    function setBusy(value) {
        busy = value;
        send.disabled = value;
        input.disabled = value;
        reset.disabled = value;
        suggestions.forEach(button => { button.disabled = value; });
    }

    function wantsHumanSupport(text) {
        const normalized = text.toLocaleLowerCase('nl-NL').replace(/\s+/g, ' ').trim();
        const phrases = [
            'medewerker', 'live support', 'live chat', 'klantenservice', 'klantendienst',
            'iemand spreken', 'persoon spreken', 'mens spreken', 'echte persoon',
            'echt persoon', 'verbind mij', 'doorverbinden', 'helpdesk', 'support agent'
        ];
        return phrases.some(phrase => normalized.includes(phrase));
    }

    async function ask(question) {
        const text = question.trim().slice(0, 2000);
        if (!text || busy || chat.dataset.mode === 'human') return;
        if (wantsHumanSupport(text)) {
            input.value = '';
            resizeInput();
            chat.dispatchEvent(new CustomEvent('live-chat:handoff', { detail: { body: text } }));
            return;
        }
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrf || !chat.dataset.endpoint) {
            appendMessage('De chat kon niet starten. Vernieuw de pagina en probeer opnieuw.');
            return;
        }
        confirmation.hidden = true;
        setBusy(true);
        appendMessage(text, true);
        input.value = '';
        resizeInput();
        const pending = document.createElement('p');
        pending.className = 'gc-pending';
        pending.textContent = 'Mashal AI denkt mee…';
        messages.append(pending);
        scrollToEnd();
        const controller = new AbortController();
        const timeout = window.setTimeout(() => controller.abort(), 150000);
        try {
            const response = await fetch(chat.dataset.endpoint, {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ message: text, history }), signal: controller.signal,
            });
            if (!response.ok) {
                const errors = {
                    404: 'De chat is nog niet goed ingesteld. Neem contact op via de contactpagina.',
                    419: 'Je sessie is verlopen. Vernieuw de pagina en stel je vraag opnieuw.',
                    422: 'Je bericht kon niet worden verwerkt. Maak het korter en probeer opnieuw.',
                    429: 'Het is even te druk. Wacht een minuut en probeer opnieuw.',
                };
                throw new Error(errors[response.status] || 'De AI is tijdelijk niet beschikbaar. Probeer het straks opnieuw.');
            }
            const result = await response.json();
            if (typeof result.message !== 'string' || !result.message.trim()) throw new Error('De AI gaf geen antwoord. Probeer je vraag opnieuw.');
            pending.remove();
            appendMessage(result.message, false, true);
            history.push({ role: 'user', content: text }, { role: 'assistant', content: result.message.slice(0, 4000) });
            history = history.slice(-10);
        } catch (error) {
            pending.remove();
            const message = error.name === 'AbortError' ? 'Het antwoord duurde te lang. Probeer opnieuw.'
                : error instanceof TypeError ? 'Geen verbinding met de chat. Controleer je internet en probeer opnieuw.'
                : error instanceof SyntaxError ? 'De server stuurde geen geldig antwoord. Probeer het later opnieuw.' : error.message;
            appendMessage(message);
            input.value = text;
            resizeInput();
        } finally {
            window.clearTimeout(timeout);
            setBusy(false);
            if (!panel.hidden && chat.contains(document.activeElement) && !window.matchMedia('(max-width: 699px)').matches) input.focus();
        }
    }

    toggle.addEventListener('click', () => setOpen(panel.hidden));
    chat.querySelector('.guest-chat__close').addEventListener('click', () => setOpen(false));
    expand.addEventListener('click', () => {
        const expanded = panel.dataset.expanded !== 'true';
        panel.dataset.expanded = String(expanded);
        expand.setAttribute('aria-pressed', String(expanded));
        expand.setAttribute('aria-label', expanded ? 'Chat verkleinen' : 'Chat vergroten');
        expand.title = expanded ? 'Chat verkleinen' : 'Chat vergroten';
    });
    reset.addEventListener('click', () => {
        if (busy || !messages.children.length) return;
        confirmation.hidden = false;
        chat.querySelector('[data-reset-cancel]').focus();
    });
    chat.querySelector('[data-reset-cancel]').addEventListener('click', () => {
        confirmation.hidden = true;
        reset.focus();
    });
    chat.querySelector('[data-reset-confirm]').addEventListener('click', () => {
        if (busy) return;
        history = [];
        messages.replaceChildren();
        input.value = '';
        welcome.hidden = false;
        confirmation.hidden = true;
        resizeInput();
        input.focus();
    });
    chat.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !panel.hidden) {
            event.preventDefault();
            if (!confirmation.hidden) { confirmation.hidden = true; reset.focus(); }
            else setOpen(false);
        }
    });
    input.addEventListener('input', resizeInput);
    input.addEventListener('keydown', event => {
        if (event.key === 'Enter' && !event.shiftKey && !event.isComposing && !window.matchMedia('(max-width: 699px)').matches) {
            event.preventDefault();
            ask(input.value);
        }
    });
    chat.querySelector('form').addEventListener('submit', event => { event.preventDefault(); ask(input.value); });
    suggestions.forEach(button => button.addEventListener('click', () => ask(button.dataset.question)));
    window.visualViewport?.addEventListener('resize', viewport);
    window.visualViewport?.addEventListener('scroll', viewport);
    window.addEventListener('resize', viewport);
    viewport();
    chat.hidden = false;
})();
