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
    const fileInput = root.querySelector('.lca-file');
    const voiceButton = root.querySelector('.lca-voice');

    let page = 1, lastPage = 1, selected = null, last = 0, seen = new Set();
    let busy = false, stopped = false, inboxPending = false, detailPending = false, generation = 0;
    let cachedList = '', presencePending = false;
    let mediaRecorder = null, mediaStream = null, audioChunks = [];

    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
    const fail = text => { error.textContent = text; error.hidden = !text; };

    async function api(url, method = 'GET', data) {
        const isForm = data instanceof FormData;
        const headers = { Accept: 'application/json', 'X-CSRF-TOKEN': csrf() };
        if (!isForm && data !== undefined) headers['Content-Type'] = 'application/json';
        const response = await fetch(url, {
            method, credentials: 'same-origin', cache: 'no-store', headers,
            body: data === undefined ? undefined : (isForm ? data : JSON.stringify(data)),
            signal: AbortSignal.timeout(30000),
        });
        if (!response.ok) {
            if ([401, 403, 419].includes(response.status)) {
                stopped = true; input.disabled = true; send.disabled = true; close.disabled = true;
                list.replaceChildren(); log.replaceChildren();
                root.querySelector('[data-name]').textContent = 'Sessie beëindigd';
                root.querySelector('[data-email]').textContent = '';
                throw Error('Geen toegang meer. Vernieuw de pagina en log opnieuw in als admin.');
            }
            if (response.status === 422) throw Error('Dit bestand of spraakbericht wordt niet ondersteund of is te groot.');
            throw Error(response.status === 409 ? 'Dit gesprek is gewijzigd. Vernieuw de status en probeer opnieuw.'
                : response.status === 429 ? 'Even wachten: de verzoeklimiet is bereikt.' : 'Geen verbinding met de live chat. Probeer opnieuw.');
        }
        return response.json();
    }

    function controls() {
        const closed = !selected || selected.status === 'closed';
        input.disabled = closed || busy || stopped;
        send.disabled = closed || busy || stopped;
        fileInput.disabled = closed || busy || stopped;
        voiceButton.disabled = closed || busy || stopped;
        close.disabled = !selected || busy || stopped;
        close.hidden = !selected;
        close.textContent = selected?.status === 'closed' ? 'Heropenen' : 'Afsluiten';
        root.querySelector('[data-status]').textContent = selected ? ({ waiting: 'Wacht op een medewerker', open: 'In gesprek', closed: 'Afgesloten' }[selected.status] || selected.status) : '';
    }

    function avatarNode(message) {
        const avatar = document.createElement('span');
        avatar.className = 'lca-avatar';
        if (message.sender_avatar) {
            const img = document.createElement('img'); img.src = message.sender_avatar; img.alt = ''; img.loading = 'lazy'; avatar.append(img);
        } else {
            avatar.textContent = (message.sender_name || 'G').trim().charAt(0).toUpperCase();
        }
        return avatar;
    }

    function appendContent(message, item) {
        if (message.body) {
            const body = document.createElement('p'); body.textContent = message.body; item.append(body);
        }
        if (!message.attachment_url) return;
        if ((message.attachment_mime || '').startsWith('image/')) {
            const link = document.createElement('a'); link.href = message.attachment_url; link.target = '_blank'; link.rel = 'noopener';
            const img = document.createElement('img'); img.className = 'lca-media-image'; img.src = message.attachment_url; img.alt = message.attachment_name || 'Afbeelding'; img.loading = 'lazy';
            link.append(img); item.append(link); return;
        }
        if (message.type === 'voice' || (message.attachment_mime || '').startsWith('audio/')) {
            const audio = document.createElement('audio'); audio.controls = true; audio.preload = 'metadata'; audio.src = message.attachment_url; item.append(audio); return;
        }
        const link = document.createElement('a'); link.className = 'lca-file-link'; link.href = message.attachment_url; link.target = '_blank'; link.rel = 'noopener';
        link.textContent = `📎 ${message.attachment_name || 'Bestand openen'}`; item.append(link);
    }

    function append(message) {
        if (seen.has(message.id)) return;
        seen.add(message.id); last = Math.max(last, Number(message.id));
        const bottom = log.scrollHeight - log.scrollTop - log.clientHeight < 100;
        const item = document.createElement('div'); item.className = 'lca-message'; item.dataset.sender = message.sender; item.dataset.messageId = message.id;
        const head = document.createElement('div'); head.className = 'lca-msg-head';
        const label = document.createElement('small'); label.textContent = message.sender_name || (message.sender === 'admin' ? 'MEDEWERKER' : (selected?.name || 'BEZOEKER'));
        head.append(avatarNode(message), label); item.append(head); appendContent(message, item);

        const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'lca-delete'; remove.textContent = 'Verwijderen';
        remove.addEventListener('click', async () => {
            if (!selected || remove.disabled) return;
            remove.disabled = true;
            try { await api(`${root.dataset.base}/${selected.id}/messages/${message.id}`, 'DELETE'); item.remove(); }
            catch (e) { remove.disabled = false; fail(e.message); }
        });
        item.append(remove); log.append(item);
        if (bottom || message.sender === 'admin') log.scrollTop = log.scrollHeight;
    }

    async function detail() {
        if (!selected || detailPending || stopped || document.hidden) return;
        const id = selected.id, version = generation; detailPending = true;
        try {
            const data = await api(`${root.dataset.base}/${id}?after=${last}`);
            if (version !== generation || id !== selected?.id) return;
            const first = last === 0; selected.status = data.conversation.status; data.messages.forEach(append); controls();
            if (first) log.scrollTop = log.scrollHeight;
        } catch (e) { fail(e.message); }
        finally { detailPending = false; }
    }

    async function choose(item) {
        if (busy || stopped || selected?.id === item.id) return;
        if (input.value.trim() && !window.confirm('Je hebt een niet-verstuurd antwoord. Ander gesprek openen?')) return;
        generation++; selected = item; last = 0; seen = new Set(); input.value = ''; log.replaceChildren();
        root.querySelector('[data-name]').textContent = item.name;
        root.querySelector('[data-email]').textContent = item.email || 'Gast · geen accountgegevens';
        list.querySelectorAll('button').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.id === String(item.id))));
        controls(); await detail();
    }

    async function inbox() {
        if (inboxPending || stopped || document.hidden) return;
        inboxPending = true; const requestedPage = page, requestedFilter = filter.value;
        try {
            const data = await api(`${root.dataset.inbox}?page=${page}&filter=${filter.value}`);
            if (page !== requestedPage || filter.value !== requestedFilter) return;
            lastPage = data.last_page; const serialized = JSON.stringify(data);
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
            root.querySelector('[data-prev]').disabled = page <= 1; root.querySelector('[data-next]').disabled = page >= lastPage;
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

    async function sendPayload({ body = null, type = 'text', file = null }) {
        if (!selected || busy || stopped || selected.status === 'closed') return;
        busy = true; controls(); fail('');
        try {
            let payload;
            if (file) {
                payload = new FormData(); payload.append('client_id', crypto.randomUUID()); payload.append('type', type);
                if (body) payload.append('body', body); payload.append('attachment', file, file.name || `${type}-${Date.now()}.webm`);
            } else payload = { body, type, client_id: crypto.randomUUID() };
            await api(`${root.dataset.base}/${selected.id}/messages`, 'POST', payload);
            input.value = ''; await detail(); await inbox();
        } catch (e) { fail(e.message); }
        finally { busy = false; controls(); }
    }

    root.querySelector('form').addEventListener('submit', async event => {
        event.preventDefault(); const body = input.value.trim(); if (!body) return; await sendPayload({ body, type: 'text' });
    });

    fileInput.addEventListener('change', async () => {
        const file = fileInput.files?.[0]; fileInput.value = ''; if (file) await sendPayload({ type: 'file', file });
    });

    voiceButton.addEventListener('click', async () => {
        if (mediaRecorder?.state === 'recording') { mediaRecorder.stop(); voiceButton.setAttribute('aria-pressed', 'false'); voiceButton.textContent = '🎤'; return; }
        if (!navigator.mediaDevices?.getUserMedia || !window.MediaRecorder) { fail('Spraakopname wordt niet ondersteund in deze browser.'); return; }
        try {
            mediaStream = await navigator.mediaDevices.getUserMedia({ audio: true }); audioChunks = []; mediaRecorder = new MediaRecorder(mediaStream);
            mediaRecorder.addEventListener('dataavailable', event => { if (event.data.size > 0) audioChunks.push(event.data); });
            mediaRecorder.addEventListener('stop', async () => {
                const mime = mediaRecorder.mimeType || 'audio/webm'; const blob = new Blob(audioChunks, { type: mime });
                const extension = mime.includes('ogg') ? 'ogg' : mime.includes('mp4') ? 'm4a' : 'webm';
                const file = new File([blob], `spraakbericht-${Date.now()}.${extension}`, { type: mime });
                mediaStream?.getTracks().forEach(track => track.stop()); mediaStream = null; mediaRecorder = null;
                await sendPayload({ type: 'voice', file });
            });
            mediaRecorder.start(); voiceButton.setAttribute('aria-pressed', 'true'); voiceButton.textContent = '■';
            fail('Opname gestart. Klik opnieuw om te stoppen en te versturen.');
        } catch { fail('Microfoontoegang is geweigerd of niet beschikbaar.'); }
    });

    close.addEventListener('click', async () => {
        if (!selected || busy || stopped) return; busy = true; controls(); fail('');
        try { await api(`${root.dataset.base}/${selected.id}`, 'PATCH', { status: selected.status === 'closed' ? 'open' : 'closed' }); await detail(); await inbox(); }
        catch (e) { fail(e.message); }
        finally { busy = false; controls(); }
    });

    online.addEventListener('change', presence);
    filter.addEventListener('change', () => { page = 1; cachedList = ''; inbox(); });
    root.querySelector('[data-prev]').addEventListener('click', () => { if (page > 1) { page--; inbox(); } });
    root.querySelector('[data-next]').addEventListener('click', () => { if (page < lastPage) { page++; inbox(); } });
    document.addEventListener('visibilitychange', () => { if (!document.hidden) { presence(); inbox(); detail(); } });
    window.setInterval(() => { inbox(); detail(); }, 3000); window.setInterval(presence, 20000);
    inbox(); presence();
})();
