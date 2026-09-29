(() => {
    const root = document.getElementById('admin-live-chat');
    if (!root) return;
    const list = root.querySelector('.lca-list');
    const log = root.querySelector('.lca-log');
    const input = root.querySelector('textarea');
    const send = root.querySelector('[type="submit"]');
    const close = root.querySelector('[data-close]');
    const filter = root.querySelector('select');
    const online = root.querySelector('#lca-online');
    const error = root.querySelector('.lca-error');
    let page = 1, lastPage = 1, selected = null, last = 0, seen = new Set();
    let busy = false, stopped = false, inboxPending = false, detailPending = false, generation = 0;
    let retry = null, cachedList = '', presencePending = false;
    function fail(text) { error.textContent = text; error.hidden = !text; }
    async function api(url, method = 'GET', data) {
        const response = await fetch(url, { method, credentials: 'same-origin', cache: 'no-store',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
            body: data === undefined ? undefined : JSON.stringify(data), signal: AbortSignal.timeout(15000) });
        if (!response.ok) {
            if ([401, 403, 419].includes(response.status)) {
                stopped = true; input.disabled = true; send.disabled = true; close.disabled = true;
                list.replaceChildren(); log.replaceChildren();
                root.querySelector('[data-name]').textContent = 'Sessie beëindigd';
                root.querySelector('[data-email]').textContent = '';
                throw Error('Geen toegang meer. Vernieuw de pagina en log opnieuw in als admin.');
            }
            throw Error(response.status === 409 ? 'Dit gesprek is gewijzigd. Vernieuw de status en probeer opnieuw.'
                : response.status === 429 ? 'Even wachten: de verzoeklimiet is bereikt.' : 'Geen verbinding met de live chat. Probeer opnieuw; je tekst blijft staan.');
        }
        return response.json();
    }
    function controls() {
        const closed = !selected || selected.status === 'closed';
        input.disabled = closed || busy || stopped;
        send.disabled = closed || busy || stopped;
        close.disabled = !selected || busy || stopped;
        close.hidden = !selected;
        close.textContent = selected?.status === 'closed' ? 'Heropenen' : 'Afsluiten';
        root.querySelector('[data-status]').textContent = selected ? ({ waiting: 'Wacht op een medewerker', open: 'In gesprek', closed: 'Afgesloten' }[selected.status] || selected.status) : '';
    }
    function append(message) {
        if (seen.has(message.id)) return;
        seen.add(message.id); last = Math.max(last, Number(message.id));
        const bottom = log.scrollHeight - log.scrollTop - log.clientHeight < 100;
        const item = document.createElement('div'); item.className = 'lca-message'; item.dataset.sender = message.sender;
        const label = document.createElement('small'); label.textContent = message.sender === 'admin' ? 'MEDEWERKER' : (selected?.name || 'BEZOEKER');
        const body = document.createElement('p'); body.textContent = message.body;
        item.append(label, body); log.append(item);
        if (bottom || message.sender === 'admin') log.scrollTop = log.scrollHeight;
    }
    async function detail() {
        if (!selected || detailPending || stopped || document.hidden) return;
        const id = selected.id, version = generation;
        detailPending = true;
        try {
            const data = await api(`${root.dataset.base}/${id}?after=${last}`);
            if (version !== generation || id !== selected?.id) return;
            const first = last === 0;
            selected.status = data.conversation.status;
            data.messages.forEach(append); controls();
            if (first) log.scrollTop = log.scrollHeight;
        } catch (e) { fail(e.message); }
        finally { detailPending = false; }
    }
    async function choose(item) {
        if (busy || stopped) return;
        if (selected?.id === item.id) return;
        if (input.value.trim() && !window.confirm('Je hebt een niet-verstuurd antwoord. Ander gesprek openen?')) return;
        generation++; selected = item; last = 0; seen = new Set(); retry = null;
        input.value = ''; log.replaceChildren();
        root.querySelector('[data-name]').textContent = item.name;
        root.querySelector('[data-email]').textContent = item.email || 'Gast · geen accountgegevens';
        list.querySelectorAll('button').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.id === String(item.id))));
        controls(); await detail();
    }
    async function inbox() {
        if (inboxPending || stopped || document.hidden) return;
        inboxPending = true;
        const requestedPage = page, requestedFilter = filter.value;
        try {
            const data = await api(`${root.dataset.inbox}?page=${page}&filter=${filter.value}`);
            if (page !== requestedPage || filter.value !== requestedFilter) return;
            lastPage = data.last_page;
            const serialized = JSON.stringify(data);
            if (serialized !== cachedList) {
                cachedList = serialized; list.replaceChildren();
                if (!data.items.length) { const empty = document.createElement('p'); empty.textContent = 'Geen gesprekken in dit overzicht.'; empty.style.padding = '16px'; list.append(empty); }
                data.items.forEach(item => {
                    const button = document.createElement('button'); button.type = 'button'; button.className = 'lca-item'; button.dataset.id = item.id;
                    button.setAttribute('aria-pressed', String(item.id === selected?.id));
                    const name = document.createElement('strong'); name.textContent = item.name + (item.unread ? ` · ${item.unread} nieuw` : '');
                    const email = document.createElement('span'); email.textContent = item.email || 'Gast';
                    const status = document.createElement('span'); status.textContent = {waiting:'Wacht op antwoord',open:'In gesprek',closed:'Afgesloten'}[item.status];
                    button.append(name, email, status); button.addEventListener('click', () => choose(item)); list.append(button);
                });
            }
            root.querySelector('[data-page]').textContent = `${page} / ${lastPage}`;
            root.querySelector('[data-prev]').disabled = page <= 1;
            root.querySelector('[data-next]').disabled = page >= lastPage;
        } catch (e) { fail(e.message); }
        finally { inboxPending = false; }
    }
    async function presence() {
        if (stopped || document.hidden || presencePending) return;
        presencePending = true;
        try { await api(root.dataset.presence, 'POST', { online: online.checked }); }
        catch (e) { fail(e.message); }
        finally { presencePending = false; }
    }
    root.querySelector('form').addEventListener('submit', async event => {
        event.preventDefault();
        const body = input.value.trim();
        if (!body || !selected || busy || stopped || selected.status === 'closed') return;
        const id = selected.id;
        if (!retry || retry.body !== body) retry = { body, client_id: crypto.randomUUID() };
        busy = true; controls(); fail('');
        try { await api(`${root.dataset.base}/${id}/messages`, 'POST', retry); input.value = ''; retry = null; await detail(); await inbox(); }
        catch (e) { fail(e.message); }
        finally { busy = false; controls(); }
    });
    close.addEventListener('click', async () => {
        if (!selected || busy || stopped) return;
        busy = true; controls(); fail('');
        try { await api(`${root.dataset.base}/${selected.id}`, 'PATCH', { status: selected.status === 'closed' ? 'open' : 'closed' }); await detail(); await inbox(); }
        catch (e) { fail(e.message); }
        finally { busy = false; controls(); }
    });
    online.addEventListener('change', presence);
    filter.addEventListener('change', () => { page = 1; cachedList = ''; inbox(); });
    root.querySelector('[data-prev]').addEventListener('click', () => { if (page > 1) { page--; inbox(); } });
    root.querySelector('[data-next]').addEventListener('click', () => { if (page < lastPage) { page++; inbox(); } });
    document.addEventListener('visibilitychange', () => { if (!document.hidden) { presence(); inbox(); detail(); } });
    window.setInterval(() => { inbox(); detail(); }, 3000);
    window.setInterval(presence, 20000);
    inbox(); presence();
})();
