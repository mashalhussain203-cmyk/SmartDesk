(() => {
    const chat = document.getElementById('guest-chat');
    const live = chat?.querySelector('.lc-panel');
    if (!live) return;
    const modes = chat.querySelectorAll('[data-chat-mode]');
    const aiBody = chat.querySelector('.gc-body');
    const aiBottom = chat.querySelector('.gc-bottom');
    const reset = chat.querySelector('.gc-reset');
    const header = chat.querySelector('#guest-chat-title');
    const subtitle = chat.querySelector('.gc-subtitle');
    const status = live.querySelector('.lc-status');
    const log = live.querySelector('.lc-log');
    const error = live.querySelector('.lc-error');
    const input = live.querySelector('textarea');
    const send = live.querySelector('[type="submit"]');
    const reopen = live.querySelector('.lc-reopen');
    let loaded = false, fetching = false, sending = false, closed = false, last = 0;
    let retry = null, stopped = false, identity = null;
    send.disabled = true;
    const seen = new Set();
    const uuid = () => crypto.randomUUID();

    function failure(message) { error.textContent = message; error.hidden = !message; }
    async function api(url, method = 'GET', data) {
        const response = await fetch(url, {
            method, credentials: 'same-origin', cache: 'no-store',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
            body: data === undefined ? undefined : JSON.stringify(data), signal: AbortSignal.timeout(15000),
        });
        if (!response.ok) {
            if ([401, 419].includes(response.status)) {
                stopped = true;
                loaded = false;
                send.disabled = true;
                input.disabled = true;
                log.replaceChildren();
                throw Error('Je sessie is verlopen. Vernieuw de pagina voordat je verdergaat.');
            }
            throw Error(response.status === 429 ? 'Te veel verzoeken. Wacht even en probeer opnieuw.'
                : response.status === 409 ? 'Dit gesprek is gesloten of gewijzigd. Controleer de status en probeer opnieuw.'
                : 'Live chat is tijdelijk niet bereikbaar. Je tekst blijft staan; probeer opnieuw.');
        }
        return response.json();
    }
    function append(message) {
        if (seen.has(message.id)) return;
        seen.add(message.id);
        last = Math.max(last, Number(message.id));
        const nearEnd = log.scrollHeight - log.scrollTop - log.clientHeight < 100;
        const item = document.createElement('div');
        item.className = `lc-msg${message.sender === 'visitor' ? ' lc-msg--visitor' : ''}`;
        const label = document.createElement('small');
        label.textContent = message.sender === 'visitor' ? 'JIJ' : 'MEDEWERKER';
        const text = document.createElement('p'); text.textContent = message.body;
        item.append(label, text); log.append(item);
        if (nearEnd || message.sender === 'visitor') log.scrollTop = log.scrollHeight;
    }
    async function poll() {
        if (fetching || stopped || document.hidden || chat.dataset.mode !== 'human' || chat.querySelector('.guest-chat__panel').hidden) return;
        fetching = true;
        try {
            const data = await api(`${live.dataset.show}?after=${last}`);
            if (identity && identity !== data.identity) {
                log.replaceChildren(); seen.clear(); last = 0; retry = null; loaded = false;
                input.value = ''; identity = data.identity; send.disabled = true;
                status.textContent = 'Je huidige gesprek wordt geladen…';
                return;
            }
            identity = data.identity;
            const first = !loaded;
            data.messages.forEach(append);
            closed = data.conversation?.status === 'closed';
            reopen.hidden = !closed;
            input.disabled = closed;
            loaded = true;
            send.disabled = closed || sending;
            status.textContent = closed ? 'Dit gesprek is afgesloten. Je kunt het opnieuw openen.'
                : data.online ? 'Er is een medewerker beschikbaar. Stuur gerust je bericht.'
                : 'Er is nu geen medewerker beschikbaar. Laat een bericht achter en kom later terug in deze chat.';
            if (!data.conversation) status.textContent += ' Je gesprek start zodra je een bericht stuurt.';
            if (first) log.scrollTop = log.scrollHeight;
            if (!sending) failure('');
        } catch (e) { failure(e.name === 'TimeoutError' ? 'De verbinding duurt te lang. We proberen het opnieuw.' : e.message); }
        finally { fetching = false; }
    }
    modes.forEach(button => button.addEventListener('click', () => {
        if (sending) return;
        const human = button.dataset.chatMode === 'human';
        chat.dataset.mode = human ? 'human' : 'ai';
        modes.forEach(item => item.setAttribute('aria-pressed', String(item === button)));
        aiBody.hidden = human; aiBottom.hidden = human; reset.hidden = human;
        chat.querySelector('.gc-reset-confirm').hidden = true;
        live.hidden = !human;
        header.textContent = human ? 'Mashal support' : 'Mashal AI';
        subtitle.textContent = human ? 'Live contact met een medewerker' : 'Je assistent voor ideeën & antwoorden';
        if (human) poll();
    }));
    live.querySelector('form').addEventListener('submit', async event => {
        event.preventDefault();
        const body = input.value.trim();
        if (!body || sending || !loaded || closed || stopped) return;
        if (!retry || retry.body !== body) retry = { body, client_id: uuid() };
        sending = true; send.disabled = true; input.disabled = true;
        modes.forEach(button => { button.disabled = true; });
        failure('');
        try {
            await api(live.dataset.store, 'POST', retry);
            input.value = ''; retry = null;
            await poll();
        } catch (e) { failure(e.name === 'TimeoutError' ? 'Geen bevestiging ontvangen. Klik opnieuw op versturen; je bericht wordt niet dubbel opgeslagen.' : e.message); }
        finally {
            sending = false; send.disabled = closed || stopped; input.disabled = closed || stopped;
            modes.forEach(button => { button.disabled = false; });
        }
    });
    reopen.addEventListener('click', async () => {
        reopen.disabled = true;
        try { await api(live.dataset.reopen, 'POST', {}); await poll(); }
        catch (e) { failure(e.message); }
        finally { reopen.disabled = false; }
    });
    chat.querySelector('.guest-chat__toggle').addEventListener('click', () => { if (chat.dataset.mode === 'human') poll(); });
    document.addEventListener('visibilitychange', () => { if (!document.hidden) poll(); });
    window.setInterval(poll, 3000);
})();
