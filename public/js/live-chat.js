(() => {
    'use strict';

    const root = document.getElementById('admin-live-chat');

    if (!root) {
        return;
    }

    // =========================================================================
    // DOM
    // =========================================================================

    const el = {
        online: root.querySelector('#lca-online'),
        error: root.querySelector('.lca-error'),
        list: root.querySelector('.lca-list'),
        filter: root.querySelector('[data-filter]'),
        search: root.querySelector('[data-search]'),
        refresh: root.querySelector('[data-refresh]'),
        inboxCount: root.querySelector('[data-inbox-count]'),
        prev: root.querySelector('[data-prev]'),
        next: root.querySelector('[data-next]'),
        page: root.querySelector('[data-page]'),

        name: root.querySelector('[data-name]'),
        email: root.querySelector('[data-email]'),
        status: root.querySelector('[data-status]'),
        close: root.querySelector('[data-close]'),

        log: root.querySelector('.lca-log'),
        emptyChat: root.querySelector('[data-empty-chat]'),
        scrollDown: root.querySelector('[data-scroll-down]'),

        typingIndicator: root.querySelector('[data-typing-indicator]'),
        typingText: root.querySelector('[data-typing-text]'),
        typingAvatar: root.querySelector('[data-typing-avatar]'),

        form: root.querySelector('.lca-form'),
        textarea: root.querySelector('.lca-form textarea'),
        submit: root.querySelector('.lca-form [type="submit"]'),
        file: root.querySelector('.lca-file'),
        voice: root.querySelector('.lca-voice'),
        composerState: root.querySelector('[data-composer-state]'),
        recorderState: root.querySelector('[data-recorder-state]'),

        toasts: root.querySelector('[data-toasts]'),
    };

    if (
        !el.online ||
        !el.error ||
        !el.list ||
        !el.filter ||
        !el.prev ||
        !el.next ||
        !el.page ||
        !el.name ||
        !el.email ||
        !el.status ||
        !el.close ||
        !el.log ||
        !el.form ||
        !el.textarea ||
        !el.submit ||
        !el.file ||
        !el.voice
    ) {
        console.error('[AdminLiveChat] Vereiste HTML-elementen ontbreken.');
        return;
    }

    // =========================================================================
    // CONFIG
    // =========================================================================

    const CONFIG = Object.freeze({
        inboxInterval: 5000,
        conversationInterval: 3000,
        presenceInterval: 25000,
        requestTimeout: 30000,
        maxTextLength: 4000,
        maxFileSize: 20 * 1024 * 1024,
        maxVoiceSize: 15 * 1024 * 1024,
        maxRecordingTime: 5 * 60 * 1000,
        recorderSlice: 250,
        nearBottomThreshold: 140,
    });

    const recorderTypes = [
        'audio/webm;codecs=opus',
        'audio/webm',
        'audio/ogg;codecs=opus',
        'audio/ogg',
        'audio/mp4',
    ];

    // =========================================================================
    // STATE
    // =========================================================================

    const state = {
        page: 1,
        lastPage: 1,
        filter: 'active',
        search: '',
        selectedId: null,
        selected: null,

        inboxLoading: false,
        conversationLoading: false,
        sending: false,
        closing: false,
        stopped: false,

        lastMessageId: 0,
        seen: new Set(),
        cachedItems: [],

        typing: false,
        typingName: '',
        typingAvatar: '',
        typingHideTimer: null,

        inboxTimer: null,
        conversationTimer: null,
        presenceTimer: null,
        searchTimer: null,

        recorder: null,
        stream: null,
        chunks: [],
        recorderMime: '',
        recordingStartedAt: 0,
        recordingTimer: null,
        recordingTimeout: null,
    };

    // =========================================================================
    // UTILS
    // =========================================================================

    function csrf() {
        return document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') || '';
    }

    function uuid() {
        if (crypto?.randomUUID) {
            return crypto.randomUUID();
        }

        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
            const random = Math.random() * 16 | 0;
            const value = c === 'x'
                ? random
                : (random & 0x3 | 0x8);

            return value.toString(16);
        });
    }

    function normalizeMime(value) {
        return String(value || '')
            .trim()
            .toLowerCase()
            .split(';')[0];
    }

    function formatBytes(value) {
        const bytes = Number(value || 0);

        if (bytes <= 0) return '0 B';
        if (bytes < 1024) return `${bytes} B`;
        if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;

        return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    }

    function formatDate(value) {
        if (!value) {
            return '';
        }

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return '';
        }

        const now = new Date();
        const sameDay =
            date.getFullYear() === now.getFullYear()
            && date.getMonth() === now.getMonth()
            && date.getDate() === now.getDate();

        if (sameDay) {
            return new Intl.DateTimeFormat('nl-NL', {
                hour: '2-digit',
                minute: '2-digit',
            }).format(date);
        }

        return new Intl.DateTimeFormat('nl-NL', {
            day: '2-digit',
            month: 'short',
            hour: '2-digit',
            minute: '2-digit',
        }).format(date);
    }

    function escapeSelector(value) {
        if (window.CSS?.escape) {
            return CSS.escape(String(value));
        }

        return String(value).replace(/["\\]/g, '\\$&');
    }

    function isNearBottom() {
        return (
            el.log.scrollHeight
            - el.log.scrollTop
            - el.log.clientHeight
        ) < CONFIG.nearBottomThreshold;
    }

    function scrollBottom(smooth = false) {
        el.log.scrollTo({
            top: el.log.scrollHeight,
            behavior: smooth ? 'smooth' : 'auto',
        });
    }

    function autoResize() {
        el.textarea.style.height = 'auto';

        const height = Math.min(
            Math.max(el.textarea.scrollHeight, 44),
            150
        );

        el.textarea.style.height = `${height}px`;
    }

    // =========================================================================
    // UI FEEDBACK
    // =========================================================================

    function showError(message = '') {
        const text = String(message || '').trim();

        el.error.textContent = text;
        el.error.hidden = text === '';
    }

    function toast(message, kind = 'info', ttl = 3200) {
        if (!el.toasts) {
            return;
        }

        const item = document.createElement('div');

        item.className = 'lca-toast';
        item.dataset.kind = kind;
        item.textContent = String(message || '');

        el.toasts.append(item);

        window.setTimeout(() => {
            item.remove();
        }, ttl);
    }

    function setComposerState(text) {
        if (el.composerState) {
            el.composerState.textContent = text;
        }
    }

    function setRecorderState(text = '') {
        if (el.recorderState) {
            el.recorderState.textContent = text;
        }
    }

    function updateControls() {
        const selected = Boolean(state.selectedId);
        const closed = state.selected?.status === 'closed';

        el.textarea.disabled =
            !selected ||
            closed ||
            state.sending ||
            state.stopped;

        el.file.disabled =
            !selected ||
            closed ||
            state.sending ||
            state.stopped;

        el.voice.disabled =
            !selected ||
            closed ||
            state.sending ||
            state.stopped;

        el.submit.disabled =
            !selected ||
            closed ||
            state.sending ||
            state.stopped ||
            el.textarea.value.trim() === '';

        el.close.hidden = !selected;
        el.close.disabled =
            !selected ||
            state.closing;

        if (!selected) {
            setComposerState('Selecteer een gesprek om te antwoorden.');
        } else if (closed) {
            setComposerState('Dit gesprek is afgesloten.');
        } else if (state.sending) {
            setComposerState('Bericht wordt verstuurd…');
        } else {
            setComposerState('Enter = verzenden · Shift+Enter = nieuwe regel');
        }
    }

    // =========================================================================
    // API
    // =========================================================================

    async function readJson(response) {
        try {
            return await response.json();
        } catch {
            return null;
        }
    }

    function validationMessage(payload) {
        const errors = payload?.errors;

        if (errors && typeof errors === 'object') {
            const preferred = ['attachment', 'body', 'client_id', 'status', 'online'];

            for (const field of preferred) {
                const messages = errors[field];

                if (Array.isArray(messages) && messages[0]) {
                    return String(messages[0]);
                }
            }

            for (const messages of Object.values(errors)) {
                if (Array.isArray(messages) && messages[0]) {
                    return String(messages[0]);
                }
            }
        }

        return payload?.message || 'De invoer is ongeldig.';
    }

    async function api(url, method = 'GET', data = undefined) {
        const isForm = data instanceof FormData;

        const headers = {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrf(),
        };

        if (!isForm && data !== undefined) {
            headers['Content-Type'] = 'application/json';
        }

        const controller = new AbortController();
        const timeout = window.setTimeout(
            () => controller.abort(),
            CONFIG.requestTimeout
        );

        let response;

        try {
            response = await fetch(url, {
                method,
                credentials: 'same-origin',
                cache: 'no-store',
                headers,
                body: data === undefined
                    ? undefined
                    : isForm
                        ? data
                        : JSON.stringify(data),
                signal: controller.signal,
            });
        } catch (error) {
            if (error?.name === 'AbortError') {
                throw new Error('De server reageert te langzaam. Probeer opnieuw.');
            }

            if (!navigator.onLine) {
                throw new Error('Je internetverbinding is weggevallen.');
            }

            throw new Error('De server kon niet worden bereikt.');
        } finally {
            window.clearTimeout(timeout);
        }

        const payload = await readJson(response);

        if (response.ok) {
            return payload || {};
        }

        if (response.status === 401 || response.status === 419) {
            state.stopped = true;
            updateControls();

            throw new Error(
                'Je adminsessie is verlopen. Vernieuw de pagina.'
            );
        }

        if (response.status === 403) {
            throw new Error('Je hebt geen toegang tot deze live chat.');
        }

        if (response.status === 404) {
            throw new Error(payload?.message || 'Het gesprek bestaat niet meer.');
        }

        if (response.status === 409) {
            throw new Error(payload?.message || 'Het gesprek is ondertussen gewijzigd.');
        }

        if (response.status === 422) {
            throw new Error(validationMessage(payload));
        }

        if (response.status === 429) {
            throw new Error('Te veel verzoeken. Wacht even en probeer opnieuw.');
        }

        throw new Error(payload?.message || 'Er ging iets mis in de live chat.');
    }

    // =========================================================================
    // AVATAR
    // =========================================================================

    function avatarElement(url, name, className = 'lca-avatar') {
        const avatar = document.createElement('span');

        avatar.className = className;

        const fallback = String(name || '?')
            .trim()
            .charAt(0)
            .toUpperCase() || '?';

        if (url) {
            const img = document.createElement('img');

            img.src = url;
            img.alt = '';
            img.loading = 'lazy';

            img.addEventListener('error', () => {
                img.remove();
                avatar.textContent = fallback;
            }, { once: true });

            avatar.append(img);
        } else {
            avatar.textContent = fallback;
        }

        return avatar;
    }

    // =========================================================================
    // INBOX
    // =========================================================================

    function filteredInboxItems() {
        const query = state.search.trim().toLowerCase();

        if (!query) {
            return state.cachedItems;
        }

        return state.cachedItems.filter(item => {
            const haystack = [
                item.name,
                item.email,
                item.kind,
                item.status,
            ]
                .filter(Boolean)
                .join(' ')
                .toLowerCase();

            return haystack.includes(query);
        });
    }

    function inboxItem(item) {
        const button = document.createElement('button');

        button.type = 'button';
        button.className = 'lca-item';
        button.dataset.conversationId = String(item.id);
        button.setAttribute(
            'aria-pressed',
            String(Number(item.id) === Number(state.selectedId))
        );

        const avatar = avatarElement(
            item.avatar,
            item.name,
            'lca-item__avatar'
        );

        const body = document.createElement('span');
        body.className = 'lca-item__body';

        const nameRow = document.createElement('span');
        nameRow.className = 'lca-item__name-row';

        const name = document.createElement('strong');
        name.textContent = item.name || `Gesprek #${item.id}`;

        const kind = document.createElement('span');
        kind.className = 'lca-kind';
        kind.textContent = item.kind === 'account' ? 'account' : 'gast';

        nameRow.append(name, kind);

        const meta = document.createElement('span');
        meta.className = 'lca-item__meta';
        meta.textContent = item.email || (
            item.status === 'closed'
                ? 'Afgesloten gesprek'
                : 'Live supportgesprek'
        );

        body.append(nameRow, meta);

        const time = document.createElement('span');
        time.className = 'lca-item__time';
        time.textContent = formatDate(item.last_message_at);

        button.append(avatar, body, time);

        const unread = Number(item.unread || 0);

        if (unread > 0) {
            const badge = document.createElement('span');
            badge.className = 'lca-unread';
            badge.textContent = unread > 99 ? '99+' : String(unread);
            button.append(badge);
        }

        button.addEventListener('click', () => {
            void selectConversation(Number(item.id));
        });

        return button;
    }

    function renderInbox() {
        el.list.replaceChildren();

        const items = filteredInboxItems();

        el.inboxCount.textContent =
            `${items.length} gesprek${items.length === 1 ? '' : 'ken'}`;

        if (items.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'lca-list-empty';
            empty.textContent = state.search
                ? 'Geen gesprekken gevonden in deze pagina.'
                : 'Geen gesprekken gevonden.';

            el.list.append(empty);
            return;
        }

        const fragment = document.createDocumentFragment();

        items.forEach(item => {
            fragment.append(inboxItem(item));
        });

        el.list.append(fragment);
    }

    function updatePagination() {
        el.page.textContent =
            `Pagina ${state.page} van ${Math.max(1, state.lastPage)}`;

        el.prev.disabled =
            state.page <= 1 ||
            state.inboxLoading;

        el.next.disabled =
            state.page >= state.lastPage ||
            state.inboxLoading;
    }

    async function loadInbox({ quiet = false } = {}) {
        if (state.inboxLoading || state.stopped) {
            return;
        }

        state.inboxLoading = true;
        updatePagination();

        if (!quiet && state.cachedItems.length === 0) {
            el.list.innerHTML =
                '<p class="lca-list-empty">Gesprekken laden…</p>';
        }

        try {
            const url = new URL(
                root.dataset.inbox,
                window.location.origin
            );

            url.searchParams.set('page', String(state.page));
            url.searchParams.set('filter', state.filter);

            const data = await api(url.toString());

            state.cachedItems =
                Array.isArray(data.items)
                    ? data.items
                    : [];

            state.page = Number(data.page || 1);
            state.lastPage = Math.max(
                1,
                Number(data.last_page || 1)
            );

            renderInbox();
            showError('');
        } catch (error) {
            if (!quiet) {
                showError(error.message);
            }
        } finally {
            state.inboxLoading = false;
            updatePagination();
        }
    }

    // =========================================================================
    // TYPING INDICATOR
    // =========================================================================

    function clearTypingHideTimer() {
        if (state.typingHideTimer) {
            window.clearTimeout(state.typingHideTimer);
            state.typingHideTimer = null;
        }
    }

    function typingPayload(data) {
        const explicitTyping =
            data?.typing === true
            || data?.visitor_typing === true
            || data?.is_typing === true
            || data?.typing?.active === true;

        let name =
            data?.typing_name
            || data?.typing_user?.name
            || data?.typing?.name
            || state.selected?.name
            || 'Bezoeker';

        let avatar =
            data?.typing_avatar
            || data?.typing_user?.avatar
            || data?.typing?.avatar
            || state.selected?.avatar
            || '';

        return {
            active: Boolean(explicitTyping),
            name: String(name || 'Bezoeker'),
            avatar: String(avatar || ''),
        };
    }

    function setTypingAvatar(name, avatarUrl = '') {
        if (!el.typingAvatar) {
            return;
        }

        el.typingAvatar.replaceChildren();

        const fallback =
            String(name || 'B')
                .trim()
                .charAt(0)
                .toUpperCase()
            || 'B';

        if (!avatarUrl) {
            el.typingAvatar.textContent = fallback;
            return;
        }

        const image = document.createElement('img');

        image.src = avatarUrl;
        image.alt = '';

        image.addEventListener(
            'error',
            () => {
                image.remove();
                el.typingAvatar.textContent = fallback;
            },
            { once: true }
        );

        el.typingAvatar.append(image);
    }

    function showTypingIndicator(
        name = 'Bezoeker',
        avatar = ''
    ) {
        if (!el.typingIndicator) {
            return;
        }

        clearTypingHideTimer();

        state.typing = true;
        state.typingName = String(name || 'Bezoeker');
        state.typingAvatar = String(avatar || '');

        if (el.typingText) {
            el.typingText.textContent =
                `${state.typingName} typt…`;
        }

        setTypingAvatar(
            state.typingName,
            state.typingAvatar
        );

        el.typingIndicator.hidden = false;

        /*
         * Safety timeout:
         * als een volgende poll geen "typing=false" bereikt,
         * blijft de indicator nooit eindeloos zichtbaar.
         */
        state.typingHideTimer = window.setTimeout(
            () => {
                hideTypingIndicator();
            },
            5000
        );
    }

    function hideTypingIndicator() {
        clearTypingHideTimer();

        state.typing = false;
        state.typingName = '';
        state.typingAvatar = '';

        if (el.typingIndicator) {
            el.typingIndicator.hidden = true;
        }
    }

    function syncTypingIndicator(data) {
        if (!state.selectedId) {
            hideTypingIndicator();
            return;
        }

        const typing = typingPayload(data);

        if (typing.active) {
            showTypingIndicator(
                typing.name,
                typing.avatar
            );
            return;
        }

        hideTypingIndicator();
    }

    // =========================================================================
    // CONVERSATION HEADER
    // =========================================================================

    function updateConversationHeader() {
        if (!state.selected) {
            el.name.textContent = 'Kies een gesprek';
            el.email.textContent =
                'Selecteer links een gesprek om de berichten te openen.';
            el.status.textContent = 'Geen gesprek geselecteerd';
            updateControls();
            return;
        }

        el.name.textContent =
            state.selected.name
            || `Gesprek #${state.selected.id}`;

        el.email.textContent =
            state.selected.email
            || (
                state.selected.kind === 'guest'
                    ? 'Gast zonder account'
                    : 'Geen e-mailadres beschikbaar'
            );

        el.status.textContent =
            state.selected.status === 'closed'
                ? 'Afgesloten'
                : 'Actief';

        el.close.textContent =
            state.selected.status === 'closed'
                ? 'Heropenen'
                : 'Afsluiten';

        updateControls();
    }

    // =========================================================================
    // MESSAGE RENDERING
    // =========================================================================

    function attachmentImage(message, container) {
        const link = document.createElement('a');
        link.href = message.attachment_url;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';

        const img = document.createElement('img');
        img.className = 'lca-media-image';
        img.src = message.attachment_url;
        img.alt = message.attachment_name || 'Afbeelding';
        img.loading = 'lazy';

        link.append(img);
        container.append(link);
    }

    function attachmentAudio(message, container) {
        const audio = document.createElement('audio');
        audio.controls = true;
        audio.preload = 'metadata';
        audio.src = message.attachment_url;

        container.append(audio);
    }

    function attachmentFile(message, container) {
        const link = document.createElement('a');
        link.className = 'lca-file-link';
        link.href = message.attachment_url;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';

        const name = message.attachment_name || 'Bestand openen';

        link.textContent = message.attachment_size
            ? `📎 ${name} · ${formatBytes(message.attachment_size)}`
            : `📎 ${name}`;

        container.append(link);
    }

    function renderAttachment(message, container) {
        if (!message.attachment_url) {
            return;
        }

        const mime = normalizeMime(message.attachment_mime);

        if (mime.startsWith('image/')) {
            attachmentImage(message, container);
            return;
        }

        if (
            message.type === 'voice'
            || mime.startsWith('audio/')
            || mime === 'video/webm'
            || mime === 'application/ogg'
        ) {
            attachmentAudio(message, container);
            return;
        }

        attachmentFile(message, container);
    }

    function messageElement(message) {
        const article = document.createElement('article');

        article.className = 'lca-message';
        article.dataset.sender = String(message.sender || '');
        article.dataset.messageId = String(message.id);

        const head = document.createElement('div');
        head.className = 'lca-msg-head';

        const label =
            message.sender_name
            || (
                message.sender === 'admin'
                    ? 'MEDEWERKER'
                    : 'BEZOEKER'
            );

        head.append(
            avatarElement(message.sender_avatar, label),
            Object.assign(document.createElement('small'), {
                textContent: label,
            })
        );

        article.append(head);

        if (message.body) {
            const body = document.createElement('p');
            body.textContent = message.body;
            article.append(body);
        }

        renderAttachment(message, article);

        if (message.sender === 'admin') {
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'lca-delete';
            remove.textContent = 'Verwijderen';

            remove.addEventListener('click', async () => {
                if (remove.disabled || !state.selectedId) {
                    return;
                }

                remove.disabled = true;

                try {
                    await api(
                        `${root.dataset.base}/${state.selectedId}/messages/${message.id}`,
                        'DELETE'
                    );

                    article.remove();
                    state.seen.delete(Number(message.id));

                    toast('Bericht verwijderd.', 'success');
                } catch (error) {
                    remove.disabled = false;
                    showError(error.message);
                }
            });

            article.append(remove);
        }

        return article;
    }

    function appendMessages(messages, { initial = false } = {}) {
        if (!Array.isArray(messages)) {
            return;
        }

        const shouldFollow =
            initial ||
            isNearBottom();

        const fragment = document.createDocumentFragment();

        for (const message of messages) {
            const id = Number(message.id);

            if (!Number.isFinite(id) || state.seen.has(id)) {
                continue;
            }

            state.seen.add(id);
            state.lastMessageId = Math.max(state.lastMessageId, id);

            fragment.append(messageElement(message));
        }

        if (fragment.childNodes.length > 0) {
            el.emptyChat?.remove();
            el.log.append(fragment);
        }

        if (shouldFollow) {
            scrollBottom(initial ? false : true);
        }

        updateScrollDown();
    }

    function resetMessages() {
        hideTypingIndicator();

        state.seen.clear();
        state.lastMessageId = 0;

        el.log.replaceChildren();

        const empty = document.createElement('div');
        empty.className = 'lca-empty-chat';
        empty.dataset.emptyChat = '';

        const inner = document.createElement('div');

        const title = document.createElement('strong');
        title.textContent = 'Gesprek laden…';

        const text = document.createElement('p');
        text.textContent = 'De berichten worden opgehaald.';

        inner.append(title, text);
        empty.append(inner);
        el.log.append(empty);

        el.emptyChat = empty;
    }

    function updateScrollDown() {
        if (!el.scrollDown) {
            return;
        }

        el.scrollDown.hidden =
            !state.selectedId ||
            isNearBottom();
    }

    // =========================================================================
    // SELECT / POLL CONVERSATION
    // =========================================================================

    function selectedFromInbox(id) {
        return state.cachedItems.find(
            item => Number(item.id) === Number(id)
        ) || null;
    }

    async function selectConversation(id) {
        if (!Number.isFinite(Number(id))) {
            return;
        }

        if (state.selectedId === Number(id)) {
            await loadConversation({ reset: false });
            return;
        }

        state.selectedId = Number(id);
        state.selected = selectedFromInbox(id);

        resetMessages();
        updateConversationHeader();
        renderInbox();

        await loadConversation({ reset: true });

        el.textarea.focus();
    }

    async function loadConversation({ reset = false, quiet = false } = {}) {
        if (
            !state.selectedId ||
            state.conversationLoading ||
            state.stopped
        ) {
            return;
        }

        state.conversationLoading = true;

        try {
            const after = reset ? 0 : state.lastMessageId;

            const data = await api(
                `${root.dataset.base}/${state.selectedId}?after=${after}`
            );

            if (reset) {
                state.seen.clear();
                state.lastMessageId = 0;
                el.log.replaceChildren();
            }

            syncTypingIndicator(data);

            if (data.conversation) {
                state.selected = {
                    ...(state.selected || {}),
                    ...data.conversation,
                    id: Number(data.conversation.id || state.selectedId),
                };
            }

            appendMessages(
                Array.isArray(data.messages)
                    ? data.messages
                    : [],
                { initial: reset }
            );

            if (
                reset
                && (!Array.isArray(data.messages) || data.messages.length === 0)
            ) {
                const empty = document.createElement('div');
                empty.className = 'lca-empty-chat';

                empty.innerHTML =
                    '<div><strong>Nog geen berichten</strong><p>Het gesprek is leeg.</p></div>';

                el.log.append(empty);
            }

            updateConversationHeader();

            // The show endpoint marks admin unread state as read.
            const inboxItem = state.cachedItems.find(
                item => Number(item.id) === Number(state.selectedId)
            );

            if (inboxItem) {
                inboxItem.unread = 0;
            }

            renderInbox();

            if (!quiet) {
                showError('');
            }
        } catch (error) {
            if (!quiet) {
                showError(error.message);
            }
        } finally {
            state.conversationLoading = false;
        }
    }

    // =========================================================================
    // SEND MESSAGE
    // =========================================================================

    async function sendMessage({
        body = null,
        type = 'text',
        file = null,
    }) {
        if (
            !state.selectedId ||
            state.sending ||
            state.stopped ||
            state.selected?.status === 'closed'
        ) {
            return false;
        }

        state.sending = true;
        hideTypingIndicator();
        updateControls();
        showError('');

        try {
            let payload;

            if (file) {
                payload = new FormData();
                payload.append('client_id', uuid());
                payload.append('type', type);

                if (body) {
                    payload.append('body', body);
                }

                payload.append(
                    'attachment',
                    file,
                    file.name || `${type}-${Date.now()}`
                );
            } else {
                payload = {
                    client_id: uuid(),
                    type,
                    body,
                };
            }

            await api(
                `${root.dataset.base}/${state.selectedId}/messages`,
                'POST',
                payload
            );

            if (type === 'text') {
                el.textarea.value = '';
                autoResize();
            }

            await loadConversation({ reset: false });
            await loadInbox({ quiet: true });

            return true;
        } catch (error) {
            showError(error.message);
            return false;
        } finally {
            state.sending = false;
            updateControls();
        }
    }

    // =========================================================================
    // CLOSE / REOPEN
    // =========================================================================

    async function changeConversationStatus() {
        if (!state.selectedId || state.closing) {
            return;
        }

        state.closing = true;
        hideTypingIndicator();
        updateControls();

        const target =
            state.selected?.status === 'closed'
                ? 'open'
                : 'closed';

        try {
            await api(
                `${root.dataset.base}/${state.selectedId}`,
                'PATCH',
                { status: target }
            );

            if (state.selected) {
                state.selected.status =
                    target === 'closed'
                        ? 'closed'
                        : 'open';
            }

            toast(
                target === 'closed'
                    ? 'Gesprek afgesloten.'
                    : 'Gesprek heropend.',
                'success'
            );

            await loadInbox({ quiet: true });

            // With "active" filter, closing can remove it from inbox,
            // but keep selected conversation usable in the current detail.
            updateConversationHeader();
        } catch (error) {
            showError(error.message);
        } finally {
            state.closing = false;
            updateControls();
        }
    }

    // =========================================================================
    // PRESENCE
    // =========================================================================

    async function sendPresence(online = el.online.checked) {
        if (state.stopped) {
            return;
        }

        try {
            await api(
                root.dataset.presence,
                'POST',
                { online: Boolean(online) }
            );
        } catch (error) {
            showError(error.message);
        }
    }

    function restartPresenceTimer() {
        if (state.presenceTimer) {
            window.clearInterval(state.presenceTimer);
        }

        state.presenceTimer = window.setInterval(() => {
            if (
                !document.hidden
                && el.online.checked
            ) {
                void sendPresence(true);
            }
        }, CONFIG.presenceInterval);
    }

    // =========================================================================
    // VOICE RECORDER
    // =========================================================================

    function chooseRecorderMime() {
        if (!window.MediaRecorder) {
            return '';
        }

        for (const type of recorderTypes) {
            try {
                if (MediaRecorder.isTypeSupported(type)) {
                    return type;
                }
            } catch {
                // Try next type.
            }
        }

        return '';
    }

    function voiceMime(value) {
        const mime = String(value || '')
            .trim()
            .toLowerCase();

        if (mime.startsWith('audio/webm')) return 'audio/webm';
        if (mime.startsWith('audio/ogg')) return 'audio/ogg';
        if (mime.startsWith('audio/mp4')) return 'audio/mp4';
        if (mime.startsWith('audio/mpeg')) return 'audio/mpeg';
        if (mime.startsWith('audio/wav')) return 'audio/wav';
        if (mime.startsWith('video/webm')) return 'video/webm';

        return normalizeMime(mime) || 'audio/webm';
    }

    function voiceExtension(mime) {
        const value = normalizeMime(mime);

        if (value.includes('ogg')) return 'ogg';
        if (value.includes('mp4') || value.includes('m4a')) return 'm4a';
        if (value.includes('wav')) return 'wav';
        if (value.includes('mpeg') || value.includes('mp3')) return 'mp3';

        return 'webm';
    }

    function clearRecorderTimers() {
        if (state.recordingTimer) {
            window.clearInterval(state.recordingTimer);
            state.recordingTimer = null;
        }

        if (state.recordingTimeout) {
            window.clearTimeout(state.recordingTimeout);
            state.recordingTimeout = null;
        }
    }

    function stopStream() {
        state.stream?.getTracks().forEach(track => {
            try {
                track.stop();
            } catch {
                // already stopped
            }
        });

        state.stream = null;
    }

    function resetRecorderUi() {
        clearRecorderTimers();

        state.recordingStartedAt = 0;

        el.voice.setAttribute('aria-pressed', 'false');
        el.voice.textContent = '🎤';
        el.voice.title = 'Spraakbericht opnemen';

        setRecorderState('');
        updateControls();
    }

    function cleanupRecorder() {
        clearRecorderTimers();
        stopStream();

        state.recorder = null;
        state.chunks = [];
        state.recorderMime = '';

        resetRecorderUi();
    }

    function recordingTick() {
        if (!state.recordingStartedAt) {
            return;
        }

        const seconds = Math.floor(
            (Date.now() - state.recordingStartedAt) / 1000
        );

        const minutes = Math.floor(seconds / 60);
        const rest = String(seconds % 60).padStart(2, '0');

        setRecorderState(`● Opname ${minutes}:${rest}`);
    }

    async function finishRecording() {
        const recorder = state.recorder;
        const chunks = [...state.chunks];

        const mime = voiceMime(
            recorder?.mimeType
            || state.recorderMime
            || 'audio/webm'
        );

        stopStream();
        clearRecorderTimers();

        state.recorder = null;
        state.chunks = [];
        state.recorderMime = '';

        resetRecorderUi();

        const size = chunks.reduce(
            (sum, chunk) => sum + Number(chunk?.size || 0),
            0
        );

        if (size <= 0) {
            showError('De opname is leeg. Probeer opnieuw.');
            return;
        }

        const blob = new Blob(chunks, { type: mime });

        if (blob.size > CONFIG.maxVoiceSize) {
            showError('Het spraakbericht is te groot. Maximaal 15 MB toegestaan.');
            return;
        }

        const file = new File(
            [blob],
            `spraakbericht-${Date.now()}.${voiceExtension(mime)}`,
            {
                type: mime,
                lastModified: Date.now(),
            }
        );

        setComposerState(
            `Spraakbericht wordt verstuurd (${formatBytes(file.size)})…`
        );

        const success = await sendMessage({
            type: 'voice',
            file,
        });

        if (success) {
            toast('Spraakbericht verstuurd.', 'success');
        }
    }

    async function startRecording() {
        if (
            !navigator.mediaDevices?.getUserMedia
            || !window.MediaRecorder
        ) {
            showError('Spraakopname wordt niet ondersteund in deze browser.');
            return;
        }

        if (
            !state.selectedId
            || state.selected?.status === 'closed'
            || state.sending
        ) {
            return;
        }

        try {
            state.stream = await navigator.mediaDevices.getUserMedia({
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true,
                },
            });

            state.chunks = [];
            state.recorderMime = chooseRecorderMime();

            state.recorder = new MediaRecorder(
                state.stream,
                state.recorderMime
                    ? { mimeType: state.recorderMime }
                    : undefined
            );

            const recorder = state.recorder;

            recorder.addEventListener('dataavailable', event => {
                if (event.data && event.data.size > 0) {
                    state.chunks.push(event.data);
                }
            });

            recorder.addEventListener('error', event => {
                console.error('[AdminLiveChat] recorder error', event);
                showError('Er ging iets mis tijdens de spraakopname.');
                cleanupRecorder();
            });

            recorder.addEventListener('stop', () => {
                void finishRecording();
            }, { once: true });

            recorder.start(CONFIG.recorderSlice);

            state.recordingStartedAt = Date.now();

            el.voice.setAttribute('aria-pressed', 'true');
            el.voice.textContent = '■';
            el.voice.title = 'Opname stoppen en versturen';

            recordingTick();

            state.recordingTimer = window.setInterval(
                recordingTick,
                1000
            );

            state.recordingTimeout = window.setTimeout(() => {
                if (state.recorder?.state === 'recording') {
                    state.recorder.stop();
                }
            }, CONFIG.maxRecordingTime);
        } catch (error) {
            console.error('[AdminLiveChat] microphone error', error);

            cleanupRecorder();

            if (error?.name === 'NotAllowedError') {
                showError(
                    'Microfoontoegang is geweigerd. Sta microfoontoegang toe in de browser.'
                );
                return;
            }

            if (error?.name === 'NotFoundError') {
                showError('Er is geen microfoon gevonden.');
                return;
            }

            if (error?.name === 'NotReadableError') {
                showError('De microfoon kan momenteel niet worden gebruikt.');
                return;
            }

            showError('Microfoontoegang is niet beschikbaar.');
        }
    }

    function stopRecording() {
        if (state.recorder?.state !== 'recording') {
            return;
        }

        el.voice.disabled = true;

        try {
            state.recorder.requestData();
        } catch {
            // optional
        }

        state.recorder.stop();
    }

    // =========================================================================
    // FILES
    // =========================================================================

    function validateFile(file) {
        if (!(file instanceof File)) {
            throw new Error('Selecteer een geldig bestand.');
        }

        if (file.size <= 0) {
            throw new Error('Het bestand is leeg.');
        }

        if (file.size > CONFIG.maxFileSize) {
            throw new Error('Het bestand is te groot. Maximaal 20 MB toegestaan.');
        }
    }

    // =========================================================================
    // POLL TIMERS
    // =========================================================================

    function restartInboxTimer() {
        if (state.inboxTimer) {
            window.clearInterval(state.inboxTimer);
        }

        state.inboxTimer = window.setInterval(() => {
            if (!document.hidden) {
                void loadInbox({ quiet: true });
            }
        }, CONFIG.inboxInterval);
    }

    function restartConversationTimer() {
        if (state.conversationTimer) {
            window.clearInterval(state.conversationTimer);
        }

        state.conversationTimer = window.setInterval(() => {
            if (!document.hidden && state.selectedId) {
                void loadConversation({ reset: false, quiet: true });
            }
        }, CONFIG.conversationInterval);
    }

    // =========================================================================
    // EVENTS
    // =========================================================================

    el.filter.addEventListener('change', () => {
        state.filter = el.filter.value === 'closed'
            ? 'closed'
            : 'active';

        state.page = 1;

        void loadInbox();
    });

    el.search?.addEventListener('input', () => {
        if (state.searchTimer) {
            window.clearTimeout(state.searchTimer);
        }

        state.searchTimer = window.setTimeout(() => {
            state.search = el.search.value || '';
            renderInbox();
        }, 120);
    });

    el.refresh?.addEventListener('click', async () => {
        await loadInbox();

        if (state.selectedId) {
            await loadConversation({ reset: false });
        }
    });

    el.prev.addEventListener('click', () => {
        if (state.page <= 1) {
            return;
        }

        state.page -= 1;
        void loadInbox();
    });

    el.next.addEventListener('click', () => {
        if (state.page >= state.lastPage) {
            return;
        }

        state.page += 1;
        void loadInbox();
    });

    el.form.addEventListener('submit', async event => {
        event.preventDefault();

        const body = el.textarea.value.trim();

        if (
            body === ''
            || body.length > CONFIG.maxTextLength
            || !state.selectedId
        ) {
            return;
        }

        await sendMessage({
            body,
            type: 'text',
        });
    });

    el.textarea.addEventListener('input', () => {
        autoResize();
        updateControls();
    });

    el.textarea.addEventListener('keydown', event => {
        if (
            event.key !== 'Enter'
            || event.shiftKey
            || event.isComposing
        ) {
            return;
        }

        event.preventDefault();

        if (!el.submit.disabled) {
            el.form.requestSubmit();
        }
    });

    el.file.addEventListener('change', async () => {
        const file = el.file.files?.[0];
        el.file.value = '';

        if (!file) {
            return;
        }

        try {
            validateFile(file);

            setComposerState(
                `Bestand wordt verstuurd (${formatBytes(file.size)})…`
            );

            const success = await sendMessage({
                type: 'file',
                file,
            });

            if (success) {
                toast('Bestand verstuurd.', 'success');
            }
        } catch (error) {
            showError(error.message);
        }
    });

    el.voice.addEventListener('click', () => {
        if (state.recorder?.state === 'recording') {
            stopRecording();
            return;
        }

        void startRecording();
    });

    el.close.addEventListener('click', () => {
        void changeConversationStatus();
    });

    el.online.addEventListener('change', () => {
        void sendPresence(el.online.checked);
    });

    el.scrollDown?.addEventListener('click', () => {
        scrollBottom(true);
    });

    el.log.addEventListener('scroll', updateScrollDown, {
        passive: true,
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            if (el.online.checked) {
                void sendPresence(false);
            }

            return;
        }

        if (el.online.checked) {
            void sendPresence(true);
        }

        void loadInbox({ quiet: true });

        if (state.selectedId) {
            void loadConversation({ reset: false, quiet: true });
        }
    });

    window.addEventListener('online', () => {
        showError('');
        void loadInbox({ quiet: true });

        if (state.selectedId) {
            void loadConversation({ reset: false, quiet: true });
        }
    });

    window.addEventListener('offline', () => {
        hideTypingIndicator();
        showError('Je internetverbinding is weggevallen.');
    });

    window.addEventListener('beforeunload', () => {
        clearTypingHideTimer();
        clearRecorderTimers();
        stopStream();

        if (state.recorder?.state === 'recording') {
            try {
                state.recorder.stop();
            } catch {
                // shutting down
            }
        }

        if (state.inboxTimer) {
            window.clearInterval(state.inboxTimer);
        }

        if (state.conversationTimer) {
            window.clearInterval(state.conversationTimer);
        }

        if (state.presenceTimer) {
            window.clearInterval(state.presenceTimer);
        }

        if (el.online.checked) {
            // Keepalive fetch: do not await during unload.
            try {
                fetch(root.dataset.presence, {
                    method: 'POST',
                    credentials: 'same-origin',
                    keepalive: true,
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf(),
                    },
                    body: JSON.stringify({ online: false }),
                });
            } catch {
                // no-op
            }
        }
    });

    // =========================================================================
    // INIT
    // =========================================================================

    // Alleen voor snelle handmatige test in DevTools.
    // window.AdminLiveChatTyping.show('Naam') / .hide()
    window.AdminLiveChatTyping = {
        show(name = 'Bezoeker', avatar = '') {
            showTypingIndicator(name, avatar);
        },

        hide() {
            hideTypingIndicator();
        },
    };

    function init() {
        state.filter = el.filter.value === 'closed'
            ? 'closed'
            : 'active';

        autoResize();
        updateControls();
        updatePagination();

        void loadInbox();

        if (el.online.checked) {
            void sendPresence(true);
        }

        restartInboxTimer();
        restartConversationTimer();
        restartPresenceTimer();
    }

    init();
})();
