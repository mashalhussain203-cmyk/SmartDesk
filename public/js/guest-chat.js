(() => {
    'use strict';

    const chat = document.getElementById('guest-chat');
    if (!chat || chat.dataset.initialized === 'true') return;
    chat.dataset.initialized = 'true';

    const panel = chat.querySelector('.guest-chat__panel');
    const toggle = chat.querySelector('.guest-chat__toggle');
    const input = chat.querySelector('.gc-bottom textarea');
    const messages = chat.querySelector('.guest-chat__messages');
    const body = chat.querySelector('.gc-body');
    const welcome = chat.querySelector('.gc-welcome');
    const send = chat.querySelector('.gc-bottom .guest-chat__send');
    const reset = chat.querySelector('.gc-reset');
    const confirmation = chat.querySelector('.gc-reset-confirm');
    const expand = chat.querySelector('.gc-expand');
    const closeButton = chat.querySelector('.guest-chat__close');
    const resetCancel = chat.querySelector('[data-reset-cancel]');
    const resetConfirm = chat.querySelector('[data-reset-confirm]');
    const suggestions = chat.querySelectorAll('[data-question]');
    const modeButtons = chat.querySelectorAll('[data-chat-mode]');
    const aiBottom = chat.querySelector('.gc-bottom');
    const livePanel = chat.querySelector('.lc-panel');
    const title = chat.querySelector('#guest-chat-title');
    const subtitle = chat.querySelector('.gc-subtitle');

    if (!panel || !toggle || !input || !messages || !body || !welcome || !send || !reset || !confirmation || !expand || !closeButton || !resetCancel || !resetConfirm) return;

    let history = [];
    let busy = false;
    let humanPromise = null;

    const isMobile = () => window.matchMedia('(max-width: 699px)').matches;

    function syncViewport() {
        if (!isMobile()) {
            chat.style.removeProperty('--gc-viewport-height');
            return;
        }
        const vv = window.visualViewport;
        const height = vv && vv.height ? vv.height : window.innerHeight;
        if (height) chat.style.setProperty('--gc-viewport-height', `${Math.round(height)}px`);
        chat.style.setProperty('--gc-viewport-top', `${Math.round(vv?.offsetTop || 0)}px`);
    }

    function resizeInput() {
        input.style.height = '42px';
        input.style.height = `${Math.min(112, Math.max(42, input.scrollHeight))}px`;
    }

    function scrollToEnd() {
        requestAnimationFrame(() => { body.scrollTop = body.scrollHeight; });
    }

    function setOpen(open) {
        panel.hidden = !open;
        chat.dataset.open = String(open);
        toggle.setAttribute('aria-expanded', String(open));
        if (open) {
            syncViewport();
            document.documentElement.classList.toggle('guest-chat-open', isMobile());
            if (!isMobile()) (chat.dataset.mode === 'human' ? chat.querySelector('.lc-input') : input)?.focus();
        } else {
            document.documentElement.classList.remove('guest-chat-open');
        }
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
            copy.type = 'button'; copy.className = 'gc-copy'; copy.textContent = 'Kopieer antwoord';
            copy.addEventListener('click', async () => {
                try { await navigator.clipboard.writeText(text); copy.textContent = 'Gekopieerd'; }
                catch { copy.textContent = 'Selecteer de tekst om te kopiëren'; }
                window.setTimeout(() => { copy.textContent = 'Kopieer antwoord'; }, 1600);
            });
            turn.append(copy);
        }
        messages.append(turn);
        scrollToEnd();
    }

    function setBusy(value) {
        busy = value;
        send.disabled = value;
        input.disabled = value;
    }

    function wantsHumanSupport(text) {
        return /\b(medewerker|mens|persoon|live\s*chat|support medewerker|klantenservice)\b/i.test(text);
    }

    function loadScript(src) {
        return new Promise((resolve, reject) => {
            if (!src) return reject(new Error('Script ontbreekt.'));
            const existing = [...document.scripts].find(s => s.src === new URL(src, location.href).href);
            if (existing?.dataset.loaded === 'true') return resolve();
            const script = existing || document.createElement('script');
            const done = () => { script.dataset.loaded = 'true'; resolve(); };
            script.addEventListener('load', done, { once: true });
            script.addEventListener('error', () => reject(new Error('Live chat kon niet worden geladen.')), { once: true });
            if (!existing) { script.src = src; script.defer = true; document.body.append(script); }
        });
    }

    async function ensureHumanModules() {
        if (humanPromise) return humanPromise;
        humanPromise = (async () => {
            await loadScript(chat.dataset.liveScript);
            // Bellen is extra zwaar en hoeft de tekstchat niet te blokkeren.
            loadScript(chat.dataset.callsScript).catch(() => {});
        })().catch(error => { humanPromise = null; throw error; });
        return humanPromise;
    }

    async function activateHuman(initialBody = '') {
        if (!livePanel) return;
        chat.dataset.mode = 'human';
        modeButtons.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.chatMode === 'human')));
        title.textContent = 'Live support';
        subtitle.textContent = 'Chat met een medewerker';
        body.hidden = true;
        aiBottom.hidden = true;
        confirmation.hidden = true;
        livePanel.hidden = false;
        try {
            await ensureHumanModules();
            chat.dispatchEvent(new CustomEvent('live-chat:handoff', { detail: { body: initialBody } }));
        } catch (error) {
            livePanel.querySelector('.lc-status').textContent = error.message || 'Live chat kon niet starten.';
        }
    }

    function activateAI() {
        chat.dataset.mode = 'ai';
        modeButtons.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.chatMode === 'ai')));
        title.textContent = 'Mashal AI';
        subtitle.textContent = 'Slimme hulp, direct in je workspace';
        body.hidden = false;
        aiBottom.hidden = false;
        if (livePanel) livePanel.hidden = true;
    }

    async function ask(question) {
        const text = String(question || '').trim().slice(0, 2000);
        if (!text || busy) return;
        if (chat.dataset.mode === 'human') return;
        if (wantsHumanSupport(text)) { input.value = ''; resizeInput(); await activateHuman(text); return; }

        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrf || !chat.dataset.endpoint) { appendMessage('De chat kon niet starten. Vernieuw de pagina en probeer opnieuw.'); return; }

        confirmation.hidden = true;
        setBusy(true);
        appendMessage(text, true);
        input.value = ''; resizeInput();
        const pending = document.createElement('p'); pending.className = 'gc-pending'; pending.textContent = 'Mashal AI denkt mee…'; messages.append(pending); scrollToEnd();
        const controller = new AbortController();
        const timeout = window.setTimeout(() => controller.abort(), 35000);

        try {
            const response = await fetch(chat.dataset.endpoint, {
                method: 'POST', credentials: 'same-origin', cache: 'no-store',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ message: text, history }), signal: controller.signal,
            });
            if (!response.ok) {
                const errors = { 419:'Je sessie is verlopen. Vernieuw de pagina.', 422:'Maak je bericht iets korter en probeer opnieuw.', 429:'Het is even te druk. Probeer het over een moment opnieuw.' };
                throw new Error(errors[response.status] || 'De AI is tijdelijk niet beschikbaar. Probeer het straks opnieuw.');
            }
            const result = await response.json();
            if (typeof result.message !== 'string' || !result.message.trim()) throw new Error('De AI gaf geen geldig antwoord. Probeer opnieuw.');
            pending.remove(); appendMessage(result.message.trim(), false, true);
            history.push({ role:'user', content:text }, { role:'assistant', content:result.message.slice(0,4000) });
            history = history.slice(-8);
        } catch (error) {
            pending.remove();
            const message = error?.name === 'AbortError' ? 'Het antwoord duurde te lang. Probeer opnieuw.' : error instanceof TypeError ? 'Geen verbinding. Controleer je internet en probeer opnieuw.' : (error?.message || 'Er ging iets mis. Probeer opnieuw.');
            appendMessage(message); input.value = text; resizeInput();
        } finally {
            clearTimeout(timeout); setBusy(false); if (!isMobile() && !panel.hidden) input.focus();
        }
    }

    toggle.addEventListener('click', () => setOpen(panel.hidden));
    closeButton.addEventListener('click', () => setOpen(false));
    expand.addEventListener('click', () => { const expanded = panel.dataset.expanded !== 'true'; panel.dataset.expanded = String(expanded); expand.setAttribute('aria-pressed', String(expanded)); });
    reset.addEventListener('click', () => { if (!busy && messages.children.length) confirmation.hidden = false; });
    resetCancel.addEventListener('click', () => { confirmation.hidden = true; });
    resetConfirm.addEventListener('click', () => { if (busy) return; history = []; messages.replaceChildren(); input.value = ''; welcome.hidden = false; confirmation.hidden = true; resizeInput(); });
    modeButtons.forEach(button => button.addEventListener('click', () => button.dataset.chatMode === 'human' ? activateHuman() : activateAI()));
    suggestions.forEach(button => button.addEventListener('click', () => ask(button.dataset.question)));
    input.addEventListener('input', resizeInput);
    input.addEventListener('keydown', event => { if (event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); input.form?.requestSubmit(); } });
    input.form.addEventListener('submit', event => { event.preventDefault(); void ask(input.value); });
    chat.addEventListener('keydown', event => { if (event.key === 'Escape' && !panel.hidden) { event.preventDefault(); setOpen(false); } });

    const viewportHandler = () => syncViewport();
    window.addEventListener('resize', viewportHandler, { passive:true });
    window.addEventListener('orientationchange', viewportHandler, { passive:true });
    window.visualViewport?.addEventListener('resize', viewportHandler, { passive:true });
    window.addEventListener('pageshow', () => { if (!panel.hidden) syncViewport(); });
    resizeInput(); syncViewport();
})();
