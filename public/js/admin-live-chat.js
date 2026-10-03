(() => {

    'use strict';

    const root = document.getElementById('admin-live-chat');

    if (!root) {

        return;

    }

    // =========================================================================

    // DOM

    // =========================================================================

    const list = root.querySelector('.lca-list');

    const log = root.querySelector('.lca-log');

    const input = root.querySelector('textarea');

    const send = root.querySelector('[type="submit"]');

    const closeButton = root.querySelector('[data-close]');

    const emailHandoffButton = root.querySelector('[data-email-handoff]');

    const emailSettingsButton = root.querySelector('[data-email-settings]');

    const filter = root.querySelector('select');

    const online = root.querySelector('#lca-online');

    const errorBox = root.querySelector('.lca-error');

    const fileInput = root.querySelector('.lca-file');

    const voiceButton = root.querySelector('.lca-voice');

    const nameNode = root.querySelector('[data-name]');

    const emailNode = root.querySelector('[data-email]');

    const statusNode = root.querySelector('[data-status]');

    const pageNode = root.querySelector('[data-page]');

    const prevButton = root.querySelector('[data-prev]');

    const nextButton = root.querySelector('[data-next]');

    const typingIndicator = root.querySelector('[data-typing-indicator]');

    const typingAvatar = root.querySelector('[data-typing-avatar]');

    const typingText = root.querySelector('[data-typing-text]');

    if (

        !list ||

        !log ||

        !input ||

        !send ||

        !closeButton ||

        !emailHandoffButton ||

        !emailSettingsButton ||

        !filter ||

        !online ||

        !errorBox ||

        !fileInput ||

        !voiceButton ||

        !nameNode ||

        !emailNode ||

        !statusNode ||

        !pageNode ||

        !prevButton ||

        !nextButton

    ) {

        console.error('[AdminLiveChat] Vereiste HTML-elementen ontbreken.');

        return;

    }

    // =========================================================================

    // CONFIG

    // =========================================================================

    const CONFIG = Object.freeze({

        pollMs: 3000,

        presenceMs: 20000,

        emailSyncMs: 30000,

        requestTimeoutMs: 120000,

        maxTextLength: 4000,

        maxFileBytes: 20 * 1024 * 1024,
        maxVideoBytes: 1024 * 1024 * 1024,

        maxVoiceBytes: 15 * 1024 * 1024,

        recorderSliceMs: 250,

        maxRecordingMs: 5 * 60 * 1000,

        scrollThreshold: 120,

        typingPingMs: 1000,

        typingIdleMs: 2600,

    });

    const RECORDER_MIMES = [

        'audio/webm;codecs=opus',

        'audio/webm',

        'audio/ogg;codecs=opus',

        'audio/ogg',

        'audio/mp4',

    ];

    // =========================================================================

    // STATE

    // =========================================================================

    let page = 1;

    let lastPage = 1;

    let selected = null;

    let lastMessageId = 0;

    let busy = false;

    let stopped = false;

    let inboxPending = false;

    let detailPending = false;

    let presencePending = false;

    let emailSyncPending = false;

    let selectionGeneration = 0;

    let cachedInboxSignature = '';

    let mediaRecorder = null;

    let mediaStream = null;

    let audioChunks = [];

    let recorderMime = '';

    let recordingTimeout = null;

    // Typing indicator / heartbeat state
    let visitorTypingHideTimer = null;
    let typingStopTimer = null;
    let lastTypingPingAt = 0;
    let replyTarget = null;
    let uploadPaused = false;
    let inboxSearch = '';
    let lastNotificationId = 0;
    const originalDocumentTitle = document.title;

    const seen = new Set();

    // =========================================================================

    // HELPERS

    // =========================================================================

    function csrf() {

        return document

            .querySelector('meta[name="csrf-token"]')

            ?.getAttribute('content') || '';

    }

    function uuid() {

        if (window.crypto?.randomUUID) {

            return window.crypto.randomUUID();

        }

        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(

            /[xy]/g,

            character => {

                const random = Math.random() * 16 | 0;

                const value =

                    character === 'x'

                        ? random

                        : (random & 0x3 | 0x8);

                return value.toString(16);

            }

        );

    }

    function normalizeMime(value) {

        return String(value || '')

            .trim()

            .toLowerCase()

            .split(';')[0];

    }

    function fail(message = '') {

        const text = String(message || '').trim();

        errorBox.textContent = text;

        errorBox.hidden = text === '';

    }

    function formatStatus(value) {

        return {

            waiting: 'Wacht op een medewerker',

            open: 'In gesprek',

            closed: 'Afgesloten',

        }[value] || String(value || '');

    }

    function isNearBottom() {

        return (

            log.scrollHeight

            - log.scrollTop

            - log.clientHeight

        ) < CONFIG.scrollThreshold;

    }

    function scrollBottom(smooth = false) {

        if (typeof log.scrollTo === 'function') {

            log.scrollTo({

                top: log.scrollHeight,

                behavior: smooth ? 'smooth' : 'auto',

            });

            return;

        }

        log.scrollTop = log.scrollHeight;

    }

    function autoResizeInput() {

        input.style.height = 'auto';

        input.style.height =

            `${Math.min(

                Math.max(input.scrollHeight, 44),

                160

            )}px`;

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

    function validationError(payload) {

        const errors = payload?.errors;

        if (errors && typeof errors === 'object') {

            for (const key of [

                'attachment',

                'body',

                'client_id',

                'type',

                'status',

                'online',

            ]) {

                if (

                    Array.isArray(errors[key])

                    && errors[key][0]

                ) {

                    return String(errors[key][0]);

                }

            }

            for (const value of Object.values(errors)) {

                if (

                    Array.isArray(value)

                    && value[0]

                ) {

                    return String(value[0]);

                }

            }

        }

        return payload?.message

            || 'De invoer is ongeldig.';

    }

    async function api(

        url,

        method = 'GET',

        data = undefined

    ) {

        const isForm =

            data instanceof FormData;

        const headers = {

            Accept: 'application/json',

            'X-CSRF-TOKEN': csrf(),

        };

        if (

            !isForm

            && data !== undefined

        ) {

            headers['Content-Type'] =

                'application/json';

        }

        const controller =

            new AbortController();

        const timeout =

            window.setTimeout(

                () => controller.abort(),

                CONFIG.requestTimeoutMs

            );

        let response;

        try {

            response = await fetch(

                url,

                {

                    method,

                    credentials: 'same-origin',

                    cache: 'no-store',

                    headers,

                    body:

                        data === undefined

                            ? undefined

                            : (

                                isForm

                                    ? data

                                    : JSON.stringify(data)

                            ),

                    signal:

                        controller.signal,

                }

            );

        } catch (error) {

            if (

                error?.name === 'AbortError'

            ) {

                throw new Error(

                    'De server reageert te langzaam. Probeer opnieuw.'

                );

            }

            if (!navigator.onLine) {

                throw new Error(

                    'Je internetverbinding is weggevallen.'

                );

            }

            throw new Error(

                'Geen verbinding met de live chat. Probeer opnieuw.'

            );

        } finally {

            window.clearTimeout(timeout);

        }

        const payload =

            await readJson(response);

        if (response.ok) {

            return payload || {};

        }

        const requestUrl = String(url || '');
        const auxiliaryRequest =
            /\/(?:typing|presence|email-sync)(?:[/?]|$)/.test(requestUrl);

        if (
            auxiliaryRequest
            && (
                response.status === 401
                || response.status === 403
                || response.status === 419
            )
        ) {
            throw new Error(
                'Ondersteunende live-chatstatus kon niet worden bijgewerkt.'
            );
        }

        if (

            response.status === 401

            || response.status === 403

            || response.status === 419

        ) {

            stopped = true;

            controls();

            list.replaceChildren();

            log.replaceChildren();

            nameNode.textContent =

                'Sessie beëindigd';

            emailNode.textContent =

                '';

            statusNode.textContent =

                '';

            throw new Error(

                'Geen toegang meer. Vernieuw de pagina en log opnieuw in als admin.'

            );

        }

        if (response.status === 422) {

            throw new Error(

                validationError(payload)

            );

        }

        if (response.status === 409) {

            throw new Error(

                payload?.message

                || 'Dit gesprek is gewijzigd. Vernieuw de status en probeer opnieuw.'

            );

        }

        if (response.status === 429) {

            throw new Error(

                'Even wachten: de verzoeklimiet is bereikt.'

            );

        }

        if (response.status === 404) {

            throw new Error(

                payload?.message

                || 'Dit gesprek bestaat niet meer.'

            );

        }

        throw new Error(

            payload?.message

            || 'Geen verbinding met de live chat. Probeer opnieuw.'

        );

    }

    function emailSyncEndpoint() {

        const url = new URL(

            root.dataset.base,

            window.location.origin

        );

        url.pathname = url.pathname.replace(

            /\/conversations\/?$/,

            '/email-sync'

        );

        url.search = '';

        return url.toString();

    }

    async function syncEmailReplies() {

        if (

            emailSyncPending

            || stopped

            || document.hidden

        ) {

            return;

        }

        emailSyncPending = true;

        try {

            const result = await api(

                emailSyncEndpoint(),

                'POST',

                {}

            );

            if (Number(result?.inserted || 0) > 0) {

                await inbox();

                if (selected?.id) {

                    await detail();

                }

            }

        } catch (error) {

            console.debug(

                '[AdminLiveChat] Gmail synchronisatie overgeslagen',

                error

            );

        } finally {

            emailSyncPending = false;

        }

    }

    // =========================================================================

    // =========================================================================

    // TYPING STATUS

    // =========================================================================

    function hideVisitorTyping() {

        if (visitorTypingHideTimer) {

            window.clearTimeout(visitorTypingHideTimer);

            visitorTypingHideTimer = null;

        }

        if (typingIndicator) {

            typingIndicator.hidden = true;

        }

    }

    function syncVisitorTyping(data) {

        if (!typingIndicator) {

            return;

        }

        const info = data?.typing?.visitor || {};

        const active = Boolean(

            data?.visitor_typing === true ||

            info?.active === true

        );

        if (!active || !selected || selected.status === 'closed') {

            hideVisitorTyping();

            return;

        }

        const name = String(

            info?.name || selected?.name || 'Bezoeker'

        ).trim();

        if (typingText) {

            typingText.textContent = `${name} typt…`;

        }

        if (typingAvatar) {

            typingAvatar.replaceChildren();

            if (info?.avatar) {

                const image = document.createElement('img');

                image.src = info.avatar;

                image.alt = '';

                image.loading = 'lazy';

                typingAvatar.append(image);

            } else {

                typingAvatar.textContent = name.charAt(0).toUpperCase() || 'B';

            }

        }

        typingIndicator.hidden = false;

        if (visitorTypingHideTimer) {

            window.clearTimeout(visitorTypingHideTimer);

        }

        visitorTypingHideTimer = window.setTimeout(

            hideVisitorTyping,

            6500

        );

    }

    async function sendAdminTyping(active, conversationId = selected?.id) {

        if (!conversationId || stopped) {

            return;

        }

        try {

            await api(

                `${root.dataset.base}/${conversationId}/typing`,

                'POST',

                { typing: Boolean(active) }

            );

        } catch (error) {

            // Typing is ondersteunende UI. Een mislukte heartbeat mag

            // het normale chatten niet blokkeren.

            console.debug('[AdminLiveChat] typing heartbeat mislukt', error);

        }

    }

    function stopAdminTyping(conversationId = selected?.id) {

        if (typingStopTimer) {

            window.clearTimeout(typingStopTimer);

            typingStopTimer = null;

        }

        lastTypingPingAt = 0;

        if (conversationId) {

            void sendAdminTyping(false, conversationId);

        }

    }

    function queueAdminTyping() {

        if (

            !selected ||

            selected.status === 'closed' ||

            stopped ||

            busy

        ) {

            return;

        }

        const hasText = input.value.trim() !== '';

        if (!hasText) {

            stopAdminTyping();

            return;

        }

        const now = Date.now();

        if (now - lastTypingPingAt >= CONFIG.typingPingMs) {

            lastTypingPingAt = now;

            void sendAdminTyping(true);

        }

        if (typingStopTimer) {

            window.clearTimeout(typingStopTimer);

        }

        typingStopTimer = window.setTimeout(

            () => stopAdminTyping(),

            CONFIG.typingIdleMs

        );

    }

    // CONTROLS

    // =========================================================================

    function controls() {

        const closed =

            !selected

            || selected.status === 'closed';

        input.disabled =

            closed

            || busy

            || stopped;

        fileInput.disabled =

            closed

            || busy

            || stopped;

        voiceButton.disabled =

            closed

            || busy

            || stopped;

        send.disabled =

            closed

            || busy

            || stopped

            || input.value.trim() === '';

        closeButton.disabled =

            !selected

            || busy

            || stopped;

        closeButton.hidden =

            !selected;

        closeButton.textContent =

            selected?.status === 'closed'

                ? 'Heropenen'

                : 'Afsluiten';

        emailHandoffButton.disabled =

            !selected

            || busy

            || stopped;

        emailHandoffButton.hidden =

            !selected;

        const emailMode =

            selected?.delivery_channel

            === 'email';

        emailHandoffButton.dataset.active =

            String(Boolean(emailMode));

        emailHandoffButton.textContent =

            emailMode

                ? '↩ Terug naar live chat'

                : '✉ Verder via e-mail';

        emailSettingsButton.hidden =

            !selected

            || !emailMode;

        emailSettingsButton.disabled =

            !selected

            || !emailMode

            || busy

            || stopped;

        statusNode.textContent =

            selected

                ? formatStatus(selected.status)

                    + (emailMode ? ' · E-mail' : '')

                : '';

    }

    // =========================================================================

    // AVATAR

    // =========================================================================

    function avatarNode(message) {

        const avatar =

            document.createElement('span');

        avatar.className =

            'lca-avatar';

        const name =

            message.sender_name

            || (

                message.sender === 'admin'

                    ? 'Medewerker'

                    : selected?.name

                        || 'Bezoeker'

            );

        if (message.sender_avatar) {

            const image =

                document.createElement('img');

            image.src =

                message.sender_avatar;

            image.alt = '';

            image.loading = 'lazy';

            image.addEventListener(

                'error',

                () => {

                    image.remove();

                    avatar.textContent =

                        String(name)

                            .trim()

                            .charAt(0)

                            .toUpperCase()

                        || '?';

                },

                { once: true }

            );

            avatar.append(image);

            return avatar;

        }

        avatar.textContent =

            String(name)

                .trim()

                .charAt(0)

                .toUpperCase()

            || '?';

        return avatar;

    }

    // =========================================================================

    // ATTACHMENTS

    // =========================================================================

    function appendContent(

        message,

        item

    ) {

        if (message.body) {

            const body =

                document.createElement('p');

            body.textContent =

                message.body;

            item.append(body);

        }

        if (!message.attachment_url) {

            return;

        }

        const mime =

            normalizeMime(

                message.attachment_mime

            );

        if (mime.startsWith('image/')) {

            const link =

                document.createElement('a');

            link.href =

                message.attachment_url;

            link.target =

                '_blank';

            link.rel =

                'noopener noreferrer';

            const image =

                document.createElement('img');

            image.className =

                'lca-media-image';

            image.src =

                message.attachment_url;

            image.alt =

                message.attachment_name

                || 'Afbeelding';

            image.loading =

                'lazy';

            link.append(image);

            item.append(link);

            return;

        }

        if (
            message.type === 'video'
            || (
                mime.startsWith('video/')
                && message.type !== 'voice'
            )
        ) {
            const video =
                document.createElement('video');
            video.className =
                'lca-media-video';
            video.controls = true;
            video.preload = 'metadata';
            video.playsInline = true;
            video.src =
                message.attachment_url;
            const videoMeta = document.createElement('small');
            videoMeta.style.display = 'block';
            videoMeta.style.opacity = '.7';
            videoMeta.textContent = message.attachment_size ? `${Math.round(message.attachment_size / 104857.6) / 10} MB` : '';
            video.addEventListener('loadedmetadata', () => {
                const minutes = Math.floor(video.duration / 60);
                const seconds = Math.floor(video.duration % 60).toString().padStart(2, '0');
                videoMeta.textContent = `${minutes}:${seconds}` + (message.attachment_size ? ` · ${Math.round(message.attachment_size / 104857.6) / 10} MB` : '');
            }, {once:true});

            const download =
                document.createElement('a');
            download.className =
                'lca-file-link';
            download.href =
                message.attachment_download_url || message.attachment_url;
            download.download =
                message.attachment_name || 'video';
            download.target =
                '_blank';
            download.rel =
                'noopener noreferrer';
            download.textContent =
                '⬇ Video downloaden';

            item.append(video, videoMeta, download);
            return;
        }

        if (

            message.type === 'voice'

            || mime.startsWith('audio/')

            || mime === 'video/webm'

            || mime === 'application/ogg'

        ) {

            const audio =

                document.createElement('audio');

            audio.controls = true;

            audio.preload = 'metadata';

            audio.src =

                message.attachment_url;

            item.append(audio);

            return;

        }

        const link =

            document.createElement('a');

        link.className =

            'lca-file-link';

        link.href =

            message.attachment_url;

        link.target =

            '_blank';

        link.rel =

            'noopener noreferrer';

        link.textContent =

            `📎 ${

                message.attachment_name

                || 'Bestand openen'

            }`;

        item.append(link);

    }

    // =========================================================================

    // MESSAGES

    // =========================================================================

    function appendMessage(message) {

        const id =

            Number(message.id);

        if (

            !Number.isFinite(id)

            || seen.has(id)

        ) {

            return;

        }

        const follow =

            isNearBottom()

            || message.sender === 'admin';

        seen.add(id);

        lastMessageId =

            Math.max(

                lastMessageId,

                id

            );

        const item =

            document.createElement('div');

        item.className =

            'lca-message';

        item.dataset.sender =

            String(message.sender || '');

        item.dataset.messageId =

            String(id);

        const head =

            document.createElement('div');

        head.className =

            'lca-msg-head';

        const label =

            document.createElement('small');

        label.textContent =

            (

                message.sender_name

                || (

                    message.sender === 'admin'

                        ? 'MEDEWERKER'

                        : selected?.name

                            || 'BEZOEKER'

                )

            )

            + (

                message.source === 'email'

                    ? ' · VIA E-MAIL'

                    : message.email_sent_at

                        ? ' · PER E-MAIL VERSTUURD'

                        : ''

            );

        head.append(

            avatarNode(message),

            label

        );

        item.append(head);

        if (message.reply_to) {
            const quote = document.createElement('button');
            quote.type = 'button';
            quote.className = 'lca-file-link';
            quote.style.display = 'block';
            quote.style.opacity = '.8';
            quote.style.marginBottom = '6px';
            quote.textContent = '↩ ' + (message.reply_to.body || message.reply_to.attachment_name || 'Bericht');
            quote.addEventListener('click', () => {
                log.querySelector(`[data-message-id="${message.reply_to.id}"]`)?.scrollIntoView({behavior:'smooth', block:'center'});
            });
            item.append(quote);
        }

        appendContent(

            message,

            item

        );

        const actions = document.createElement('div');
        actions.style.display = 'flex';
        actions.style.gap = '6px';
        actions.style.flexWrap = 'wrap';
        actions.style.marginTop = '7px';
        const miniButton = (label, title, handler) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = label;
            button.title = title;
            button.style.fontSize = '12px';
            button.style.padding = '3px 7px';
            button.style.borderRadius = '999px';
            button.addEventListener('click', handler);
            return button;
        };
        actions.append(miniButton('↩', 'Beantwoorden', () => {
            replyTarget = {id, label: message.body || message.attachment_name || 'bericht'};
            input.focus();
            fail('Je antwoordt op: ' + replyTarget.label);
        }));
        ['👍','❤️','😂','😮','😢','🙏'].forEach(emoji => {
            const count = Number(message.reactions?.[emoji] || 0);
            actions.append(miniButton(emoji + (count ? ` ${count}` : ''), 'Reactie', async () => {
                try {
                    await api(`${root.dataset.base}/${selected.id}/messages/${id}/reaction`, 'POST', {emoji});
                    lastMessageId = 0; seen.clear(); log.replaceChildren(); await detail();
                } catch (error) { fail(error.message); }
            }));
        });
        if (message.sender === 'admin' && message.body) {
            actions.append(miniButton('✎', 'Bericht bewerken (max. 5 minuten)', async () => {
                const body = window.prompt('Bericht bewerken:', message.body);
                if (body === null || !body.trim()) return;
                try {
                    await api(`${root.dataset.base}/${selected.id}/messages/${id}`, 'PATCH', {body: body.trim()});
                    lastMessageId = 0; seen.clear(); log.replaceChildren(); await detail();
                } catch (error) { fail(error.message); }
            }));
        }
        if (message.attachment_url) {
            actions.append(miniButton('⛶', 'Media fullscreen openen', () => window.open(message.attachment_url, '_blank', 'noopener')));
        }
        const status = document.createElement('span');
        status.style.fontSize = '11px';
        status.style.opacity = '.65';
        status.textContent = (message.edited_at ? 'bewerkt · ' : '') + (message.sender === 'admin' ? (message.read_by_other ? 'gelezen' : 'verzonden') : '');
        actions.append(status);
        item.append(actions);

        if (message.sender === 'visitor' && id > lastNotificationId) {
            lastNotificationId = id;
            if (document.hidden && 'Notification' in window && Notification.permission === 'granted') {
                new Notification(selected?.name || 'Nieuw chatbericht', {body: message.body || message.attachment_name || 'Nieuwe bijlage'});
            }
            if (document.hidden) {
                try { const ctx = new AudioContext(); const o = ctx.createOscillator(); o.connect(ctx.destination); o.start(); o.stop(ctx.currentTime + .08); } catch {}
            }
        }

        /*

         * Geen DELETE-knop hier:

         * je huidige route:list toont geen DELETE-route voor

         * /admin/live-chat/conversations/{conversation}/messages/{message}.

         * Zo vermijden we een gegarandeerde 404.

         */

        log.append(item);

        if (follow) {

            scrollBottom(true);

        }

    }

    // =========================================================================

    // DETAIL

    // =========================================================================

    async function detail() {

        if (

            !selected

            || detailPending

            || stopped

            || document.hidden

        ) {

            return;

        }

        const id =

            Number(selected.id);

        const version =

            selectionGeneration;

        detailPending = true;

        try {

            const data =

                await api(

                    `${root.dataset.base}/${id}?after=${lastMessageId}`

                );

            if (

                version !== selectionGeneration

                || id !== Number(selected?.id)

            ) {

                return;

            }

            if (!data.conversation) {

                throw new Error(

                    'Het gesprek kon niet worden geladen.'

                );

            }

            const first =

                lastMessageId === 0;

            selected.status =

                data.conversation.status

                || selected.status;

            if (data.conversation.name) {

                selected.name =

                    data.conversation.name;

            }

            if (

                Object.prototype.hasOwnProperty.call(

                    data.conversation,

                    'email'

                )

            ) {

                selected.email =

                    data.conversation.email;

            }

            selected.delivery_channel =

                data.conversation.delivery_channel

                || selected.delivery_channel

                || 'live';

            selected.email_handoff_at =

                data.conversation.email_handoff_at

                || null;

            const messages =

                Array.isArray(data.messages)

                    ? data.messages

                    : [];

            messages.forEach(

                appendMessage

            );

            nameNode.textContent =

                selected.name

                || `Gesprek #${selected.id}`;

            emailNode.textContent =

                selected.email

                    ? selected.email

                        + (

                            selected.delivery_channel === 'email'

                                ? ' · gesprek via e-mail'

                                : ''

                        )

                    : 'Gast · geen accountgegevens';

            syncVisitorTyping(data);

            controls();

            if (first) {

                scrollBottom(false);

            }

            fail('');

        } catch (error) {

            fail(error.message);

        } finally {

            detailPending = false;

        }

    }

    // =========================================================================

    // CHOOSE CONVERSATION

    // =========================================================================

    async function choose(item) {

        if (

            busy

            || stopped

            || Number(selected?.id)

                === Number(item.id)

        ) {

            return;

        }

        if (

            input.value.trim()

            && !window.confirm(

                'Je hebt een niet-verstuurd antwoord. Ander gesprek openen?'

            )

        ) {

            return;

        }

        selectionGeneration += 1;

        // Een reply hoort altijd bij het huidige gesprek.
        replyTarget = null;
        fail('');

        selected = {

            ...item,

            id: Number(item.id),

        };

        lastMessageId = 0;

        seen.clear();

        input.value = '';

        autoResizeInput();

        log.replaceChildren();

        nameNode.textContent =

            item.name

            || `Gesprek #${item.id}`;

        emailNode.textContent =

            item.email

            || 'Gast · geen accountgegevens';

        list

            .querySelectorAll('.lca-item')

            .forEach(button => {

                button.setAttribute(

                    'aria-pressed',

                    String(

                        Number(button.dataset.id)

                        === Number(item.id)

                    )

                );

            });

        controls();

        await detail();

        if (!input.disabled) {

            input.focus();

        }

    }

    // =========================================================================

    // INBOX

    // =========================================================================

    function inboxButton(item) {

        const button =

            document.createElement('button');

        button.type = 'button';

        button.className =

            'lca-item';

        button.dataset.id =

            String(item.id);

        button.setAttribute(

            'aria-pressed',

            String(

                Number(item.id)

                === Number(selected?.id)

            )

        );

        const name =

            document.createElement('strong');

        name.textContent =

            String(item.name || 'Bezoeker')

            + (

                Number(item.unread || 0) > 0

                    ? ` · ${item.unread} nieuw`

                    : ''

            );

        const email =

            document.createElement('span');

        email.textContent =

            item.email || 'Gast';

        const status =

            document.createElement('span');

        status.textContent =

            formatStatus(item.status)
            + (item.priority && item.priority !== 'normal' ? ` · ${String(item.priority).toUpperCase()}` : '')
            + (Array.isArray(item.labels) && item.labels.length ? ` · ${item.labels.map(label => '#'+label).join(' ')}` : '')

            + (

                item.delivery_channel === 'email'

                    ? ' · E-mail'

                    : ''

            );

        button.append(

            name,

            email,

            status

        );

        button.addEventListener(

            'click',

            () => {

                void choose(item);

            }

        );

        return button;

    }

    async function inbox() {

        if (

            inboxPending

            || stopped

            || document.hidden

        ) {

            return;

        }

        inboxPending = true;

        const requestedPage =

            page;

        const requestedFilter =

            filter.value;

        try {

            const url =

                new URL(

                    root.dataset.inbox,

                    window.location.origin

                );

            url.searchParams.set(

                'page',

                String(page)

            );

            url.searchParams.set(

                'filter',

                String(filter.value)

            );
            if (inboxSearch) {
                url.searchParams.set('q', inboxSearch);
            }

            const data =

                await api(

                    url.toString()

                );

            if (

                page !== requestedPage

                || filter.value

                    !== requestedFilter

            ) {

                return;

            }

            const items =

                Array.isArray(data.items)

                    ? data.items

                    : [];

            lastPage =

                Math.max(

                    1,

                    Number(

                        data.last_page

                        || 1

                    )

                );

            page =

                Math.min(

                    Math.max(

                        1,

                        Number(

                            data.page

                            || page

                        )

                    ),

                    lastPage

                );

            const signature =

                JSON.stringify({

                    items,

                    page,

                    lastPage,

                    selectedId:

                        selected?.id

                        || null,

                });

            if (

                signature

                !== cachedInboxSignature

            ) {

                cachedInboxSignature =

                    signature;

                list.replaceChildren();

                if (items.length === 0) {

                    const empty =

                        document.createElement('p');

                    empty.textContent =

                        'Geen gesprekken in dit overzicht.';

                    empty.style.padding =

                        '16px';

                    list.append(empty);

                } else {

                    const fragment =

                        document.createDocumentFragment();

                    items.forEach(item => {

                        fragment.append(

                            inboxButton(item)

                        );

                    });

                    list.append(fragment);

                }

            }

            pageNode.textContent =

                `${page} / ${lastPage}`;

            prevButton.disabled =

                page <= 1;

            nextButton.disabled =

                page >= lastPage;

            /*

             * Houd geselecteerde metadata/status actueel wanneer

             * hetzelfde gesprek nog in de inbox voorkomt.

             */

            if (selected) {

                const current =

                    items.find(

                        item =>

                            Number(item.id)

                            === Number(selected.id)

                    );

                if (current) {

                    selected = {

                        ...selected,

                        ...current,

                        id: Number(current.id),

                    };

                    controls();

                }

            }

            fail('');

        } catch (error) {

            fail(error.message);

        } finally {

            inboxPending = false;

        }

    }

    // =========================================================================

    // PRESENCE

    // =========================================================================

    async function presence() {

        if (

            stopped

            || document.hidden

            || presencePending

        ) {

            return;

        }

        presencePending = true;

        try {

            await api(

                root.dataset.presence,

                'POST',

                {

                    online:

                        Boolean(

                            online.checked

                        ),

                }

            );

        } catch (error) {

            fail(error.message);

        } finally {

            presencePending = false;

        }

    }

    // =========================================================================

    // SEND

    // =========================================================================

    async function sendPayload({

        body = null,

        type = 'text',

        file = null,
        parentMessageId = replyTarget?.id || null,

    }) {

        if (

            !selected

            || busy

            || stopped

            || selected.status === 'closed'

        ) {

            return false;

        }

        busy = true;

        controls();

        fail('');

        try {

            let payload;

            if (file) {

                payload =

                    new FormData();

                payload.append(

                    'client_id',

                    uuid()

                );

                payload.append(

                    'type',

                    type

                );

                if (body) {

                    payload.append(

                        'body',

                        body

                    );

                }
                if (parentMessageId) {
                    payload.append('parent_message_id', String(parentMessageId));
                }

                payload.append(

                    'attachment',

                    file,

                    file.name

                    || `${type}-${Date.now()}.webm`

                );

            } else {

                payload = {

                    body,

                    type,
                    parent_message_id: parentMessageId,

                    client_id:

                        uuid(),

                };

            }

            const result = await api(

                `${root.dataset.base}/${selected.id}/messages`,

                'POST',

                payload

            );

            if (type === 'text') {

                input.value = '';

                autoResizeInput();

            }

            // Het antwoorddoel is nu succesvol meegestuurd.
            // Wis het pas NA een succesvolle request, zodat een mislukte
            // verzending opnieuw geprobeerd kan worden met dezelfde reply.
            replyTarget = null;

            await detail();

            await inbox();

            if (

                result?.email_sent === false

                && result?.email_skipped !== true

                && result?.email_error

            ) {

                fail(

                    'Bericht is opgeslagen, maar de e-mail kon niet worden verstuurd: '

                    + result.email_error

                );

            }

            return true;

        } catch (error) {

            fail(error.message);

            // Bij een mislukte verzending blijft replyTarget bewust bestaan.
            // Zo kan de admin opnieuw verzenden zonder opnieuw op ↩ te klikken.
            if (replyTarget?.id) {
                window.setTimeout(() => {
                    if (replyTarget?.id && !busy) {
                        fail('Je antwoordt op: ' + (replyTarget.label || 'bericht'));
                    }
                }, 1800);
            }

            return false;

        } finally {

            busy = false;

            controls();
            queueMicrotask(controls);

        }

    }

    // =========================================================================

    // VOICE

    // =========================================================================

    function chooseRecorderMime() {

        if (!window.MediaRecorder) {

            return '';

        }

        for (

            const candidate

            of RECORDER_MIMES

        ) {

            try {

                if (

                    MediaRecorder

                        .isTypeSupported(

                            candidate

                        )

                ) {

                    return candidate;

                }

            } catch {

                // volgende proberen

            }

        }

        return '';

    }

    function voiceMime(value) {

        const mime =

            String(value || '')

                .trim()

                .toLowerCase();

        if (

            mime.startsWith(

                'audio/webm'

            )

        ) {

            return 'audio/webm';

        }

        if (

            mime.startsWith(

                'audio/ogg'

            )

        ) {

            return 'audio/ogg';

        }

        if (

            mime.startsWith(

                'audio/mp4'

            )

        ) {

            return 'audio/mp4';

        }

        if (

            mime.startsWith(

                'video/webm'

            )

        ) {

            return 'video/webm';

        }

        return normalizeMime(mime)

            || 'audio/webm';

    }

    function voiceExtension(value) {

        const mime =

            normalizeMime(value);

        if (mime.includes('ogg')) {

            return 'ogg';

        }

        if (mime.includes('mp4')) {

            return 'm4a';

        }

        if (mime.includes('wav')) {

            return 'wav';

        }

        if (

            mime.includes('mpeg')

            || mime.includes('mp3')

        ) {

            return 'mp3';

        }

        return 'webm';

    }

    function stopMediaStream() {

        mediaStream

            ?.getTracks()

            .forEach(track => {

                try {

                    track.stop();

                } catch {

                    // al gestopt

                }

            });

        mediaStream = null;

    }

    function clearRecordingTimeout() {

        if (recordingTimeout) {

            window.clearTimeout(

                recordingTimeout

            );

            recordingTimeout = null;

        }

    }

    function resetRecorderUi() {

        clearRecordingTimeout();

        voiceButton.setAttribute(

            'aria-pressed',

            'false'

        );

        voiceButton.textContent =

            '🎤';

        mediaRecorder = null;

        recorderMime = '';

        audioChunks = [];

        stopMediaStream();

        controls();

    }

    async function finishRecording(

        recorder

    ) {

        const chunks =

            [...audioChunks];

        const mime =

            voiceMime(

                recorder?.mimeType

                || recorderMime

                || 'audio/webm'

            );

        clearRecordingTimeout();

        stopMediaStream();

        mediaRecorder = null;

        recorderMime = '';

        audioChunks = [];

        voiceButton.setAttribute(

            'aria-pressed',

            'false'

        );

        voiceButton.textContent =

            '🎤';

        const bytes =

            chunks.reduce(

                (sum, chunk) =>

                    sum

                    + Number(

                        chunk?.size || 0

                    ),

                0

            );

        if (bytes <= 0) {

            fail(

                'De opname bevat geen geluid. Probeer opnieuw.'

            );

            controls();

            return;

        }

        const blob =

            new Blob(

                chunks,

                {

                    type: mime,

                }

            );

        if (

            blob.size

            > CONFIG.maxVoiceBytes

        ) {

            fail(

                'Het spraakbericht is te groot. Maximaal 15 MB toegestaan.'

            );

            controls();

            return;

        }

        const file =

            new File(

                [blob],

                `spraakbericht-${Date.now()}.${voiceExtension(mime)}`,

                {

                    type: mime,

                    lastModified:

                        Date.now(),

                }

            );

        await sendPayload({

            type: 'voice',

            file,

        });

        controls();

    }

    async function startRecording() {

        if (

            !navigator.mediaDevices

                ?.getUserMedia

            || !window.MediaRecorder

        ) {

            fail(

                'Spraakopname wordt niet ondersteund in deze browser.'

            );

            return;

        }

        if (

            !selected

            || selected.status === 'closed'

            || busy

            || stopped

        ) {

            return;

        }

        fail('');

        try {

            mediaStream =

                await navigator

                    .mediaDevices

                    .getUserMedia({

                        audio: {

                            echoCancellation:

                                true,

                            noiseSuppression:

                                true,

                            autoGainControl:

                                true,

                        },

                    });

            audioChunks = [];

            recorderMime =

                chooseRecorderMime();

            mediaRecorder =

                new MediaRecorder(

                    mediaStream,

                    recorderMime

                        ? {

                            mimeType:

                                recorderMime,

                        }

                        : undefined

                );

            const recorder =

                mediaRecorder;

            recorder.addEventListener(

                'dataavailable',

                event => {

                    if (

                        event.data

                        && event.data.size > 0

                    ) {

                        audioChunks.push(

                            event.data

                        );

                    }

                }

            );

            recorder.addEventListener(

                'error',

                event => {

                    console.error(

                        '[AdminLiveChat] MediaRecorder error',

                        event

                    );

                    fail(

                        'Er ging iets mis tijdens de spraakopname.'

                    );

                    resetRecorderUi();

                }

            );

            recorder.addEventListener(

                'stop',

                () => {

                    void finishRecording(

                        recorder

                    );

                },

                {

                    once: true,

                }

            );

            recorder.start(

                CONFIG.recorderSliceMs

            );

            voiceButton.setAttribute(

                'aria-pressed',

                'true'

            );

            voiceButton.textContent =

                '■';

            fail(

                'Opname gestart. Klik opnieuw om te stoppen en te versturen.'

            );

            recordingTimeout =

                window.setTimeout(

                    () => {

                        if (

                            mediaRecorder?.state

                            === 'recording'

                        ) {

                            mediaRecorder.stop();

                        }

                    },

                    CONFIG.maxRecordingMs

                );

        } catch (error) {

            console.error(

                '[AdminLiveChat] Microfoonfout',

                error

            );

            resetRecorderUi();

            if (

                error?.name

                === 'NotAllowedError'

            ) {

                fail(

                    'Microfoontoegang is geweigerd. Sta microfoontoegang toe in de browser.'

                );

                return;

            }

            if (

                error?.name

                === 'NotFoundError'

            ) {

                fail(

                    'Er is geen microfoon gevonden.'

                );

                return;

            }

            fail(

                'Microfoontoegang is niet beschikbaar.'

            );

        }

    }

    // =========================================================================

    // EVENTS

    // =========================================================================

    root

        .querySelector('form')

        .addEventListener(

            'submit',

            async event => {

                event.preventDefault();

                const body =

                    input.value.trim();

                if (

                    !body

                    || body.length

                        > CONFIG.maxTextLength

                ) {

                    return;

                }

                stopAdminTyping();

                await sendPayload({

                    body,

                    type: 'text',

                });

            }

        );

    input.addEventListener(

        'input',

        () => {

            autoResizeInput();

            controls();

            queueAdminTyping();

        }

    );

    input.addEventListener(

        'keydown',

        event => {

            if (

                event.key !== 'Enter'

                || event.shiftKey

                || event.isComposing

            ) {

                return;

            }

            event.preventDefault();

            if (!send.disabled) {

                root

                    .querySelector('form')

                    .requestSubmit();

            }

        }

    );

    function isVideoFile(file) {

        const mime = normalizeMime(file?.type);

        const name = String(file?.name || '').toLowerCase();

        return mime.startsWith('video/')

            || /\.(mp4|webm|mov|m4v|avi|mkv|mpeg|mpg|3gp|3g2|ogv|ts|mts|m2ts|flv|wmv)$/.test(name);

    }



    function adminUploadUrl(path = '') {
        return `${root.dataset.base}/${selected.id}/uploads${path}`;
    }

    function adminVideoUploadStorageKey(file) {
        return `admin-live-chat-video:${selected.id}:${file.name}:${file.size}:${file.lastModified}`;
    }

    function sleep(ms) {
        return new Promise(resolve => window.setTimeout(resolve, ms));
    }

    async function uploadChunkWithRetry(url, formData, attempts = 4) {
        let lastError;
        for (let attempt = 1; attempt <= attempts; attempt += 1) {
            try {
                return await api(url, 'POST', formData);
            } catch (error) {
                lastError = error;
                if (attempt < attempts) {
                    await sleep(500 * attempt);
                }
            }
        }
        throw lastError || new Error('Een videodeel kon niet worden geüpload.');
    }

    async function uploadVideoInChunks(file) {
        if (!selected || busy || stopped || selected.status === 'closed') {
            return false;
        }

        busy = true;
        controls();
        fail('');

        const storageKey = adminVideoUploadStorageKey(file);
        let uploadId = window.localStorage.getItem(storageKey) || '';

        try {
            let state = null;

            if (uploadId) {
                try {
                    state = await api(adminUploadUrl(`/${uploadId}`));
                } catch {
                    window.localStorage.removeItem(storageKey);
                    uploadId = '';
                }
            }

            if (!uploadId) {
                const started = await api(adminUploadUrl('/start'), 'POST', {
                    client_id: uuid(),
                    name: file.name,
                    mime: file.type || '',
                    size: file.size,
                });
                uploadId = String(started.upload_id || '');
                if (!uploadId) {
                    throw new Error('De video-upload kon niet worden gestart.');
                }
                window.localStorage.setItem(storageKey, uploadId);
                state = await api(adminUploadUrl(`/${uploadId}`));
            }

            const chunkSize = Number(state?.chunk_size || (5 * 1024 * 1024));
            const totalChunks = Number(state?.total_chunks || Math.ceil(file.size / chunkSize));
            const received = new Set((Array.isArray(state?.received) ? state.received : []).map(Number));

            for (let index = 0; index < totalChunks; index += 1) {
                while (uploadPaused) {
                    await new Promise(resolve => window.setTimeout(resolve, 250));
                }
                if (!received.has(index)) {
                    const start = index * chunkSize;
                    const end = Math.min(start + chunkSize, file.size);
                    const form = new FormData();
                    form.append('chunk', file.slice(start, end), `chunk-${index}.part`);
                    await uploadChunkWithRetry(adminUploadUrl(`/${uploadId}/chunks/${index}`), form);
                    received.add(index);
                }

                const percent = Math.min(100, Math.round((received.size / totalChunks) * 100));
                fail(`Video uploaden… ${percent}%`);
            }

            fail('Video verwerken…');
            const result = await api(adminUploadUrl(`/${uploadId}/complete`), 'POST', {});
            window.localStorage.removeItem(storageKey);

            replyTarget = null;
            await detail();
            await inbox();

            if (result?.email_sent === false && result?.email_skipped !== true && result?.email_error) {
                fail('Video is opgeslagen, maar de e-mail kon niet worden verstuurd: ' + result.email_error);
            } else {
                fail('');
            }

            return true;
        } catch (error) {
            fail(error?.message || 'De video kon niet worden verstuurd.');
            return false;
        } finally {
            busy = false;
            controls();
            queueMicrotask(controls);
        }
    }

    async function handleSelectedFiles(files) {
        for (const file of Array.from(files || [])) {
            const video = isVideoFile(file);
            const maxBytes = video ? CONFIG.maxVideoBytes : CONFIG.maxFileBytes;
            if (file.size > maxBytes) {
                fail(video ? 'De video is te groot. Maximaal 1 GB toegestaan.' : 'Het bestand is te groot. Maximaal 20 MB toegestaan.');
                continue;
            }
            if (video) {
                await uploadVideoInChunks(file);
            } else {
                await sendPayload({type: 'file', file});
            }
        }
    }

    fileInput.multiple = true;
    fileInput.addEventListener('change', async () => {
        const files = Array.from(fileInput.files || []);
        fileInput.value = '';
        await handleSelectedFiles(files);
    });

    ['dragenter','dragover'].forEach(name => log.addEventListener(name, event => {
        event.preventDefault();
        log.style.outline = '2px dashed currentColor';
    }));
    ['dragleave','drop'].forEach(name => log.addEventListener(name, event => {
        event.preventDefault();
        log.style.outline = '';
    }));
    log.addEventListener('drop', event => void handleSelectedFiles(event.dataTransfer?.files));


    voiceButton.addEventListener(

        'click',

        () => {

            if (

                mediaRecorder?.state

                === 'recording'

            ) {

                voiceButton.disabled =

                    true;

                try {

                    mediaRecorder

                        .requestData();

                } catch {

                    // optioneel

                }

                mediaRecorder.stop();

                return;

            }

            void startRecording();

        }

    );

    function defaultEmailSubject(conversation) {
        const id = Number(conversation?.id || 0);

        return String(
            conversation?.email_subject
            || `Mashal Support · gesprek #${id}`
        ).trim();
    }

    function defaultEmailTitle(conversation) {
        return String(
            conversation?.email_title
            || 'Mashal Support'
        ).trim();
    }

    async function saveEmailSettings({
        firstHandoff = false,
    } = {}) {
        if (
            !selected
            || busy
            || stopped
        ) {
            return;
        }

        let email = String(
            selected.email || ''
        ).trim();

        email = String(
            window.prompt(
                'E-mailadres van de klant:',
                email
            ) || ''
        ).trim();

        if (!email) {
            return;
        }

        let subject = defaultEmailSubject(selected);

        const subjectLocked = Boolean(
            selected.email_subject_locked
            || selected.gmail_thread_id
        );

        if (!subjectLocked) {
            subject = String(
                window.prompt(
                    'E-mailonderwerp voor deze thread:',
                    subject
                ) || ''
            ).trim();

            if (!subject) {
                return;
            }
        }

        let title = String(
            window.prompt(
                subjectLocked
                    ? 'Titel in de e-mail. Deze mag je blijven wijzigen; het echte onderwerp blijft vast zodat Gmail dezelfde thread behoudt:'
                    : 'Titel boven het bericht in de e-mail:',
                defaultEmailTitle(selected)
            ) || ''
        ).trim();

        if (!title) {
            title = 'Mashal Support';
        }

        const confirmation = subjectLocked
            ? `Instellingen opslaan?\n\nE-mail: ${email}\nOnderwerp (vast voor dezelfde Gmail-thread): ${subject}\nTitel in de mail: ${title}`
            : `Gesprek via e-mail starten?\n\nDe volledige chatgeschiedenis tot nu toe wordt als transcript in de eerste e-mail meegestuurd.\n\nE-mail: ${email}\nOnderwerp: ${subject}\nTitel in de mail: ${title}`;

        if (!window.confirm(confirmation)) {
            return;
        }

        busy = true;
        controls();
        fail('');

        try {
            const result = await api(
                `${root.dataset.base}/${selected.id}/email-handoff`,
                'POST',
                {
                    enabled: true,
                    email,
                    subject,
                    title,
                }
            );

            if (result?.conversation) {
                selected = {
                    ...selected,
                    ...result.conversation,
                    id: Number(
                        result.conversation.id
                        || selected.id
                    ),
                };
            } else {
                selected.delivery_channel = 'email';
                selected.email = email;
                selected.email_subject = subject;
                selected.email_title = title;
            }

            await detail();
            await inbox();

            if (
                firstHandoff
                && result?.email_sent === false
                && result?.email_error
            ) {
                fail(
                    'E-mailmodus is geactiveerd, maar de eerste e-mail kon niet worden verstuurd: '
                    + result.email_error
                );
            }
        } catch (error) {
            fail(error.message);
        } finally {
            busy = false;
            controls();
        }
    }

    emailHandoffButton.addEventListener(
        'click',
        async () => {
            if (
                !selected
                || busy
                || stopped
            ) {
                return;
            }

            const enabling =
                selected.delivery_channel !== 'email';

            if (enabling) {
                await saveEmailSettings({
                    firstHandoff: true,
                });

                return;
            }

            if (
                !window.confirm(
                    'Dit gesprek terugzetten naar normale live-chat? De Gmail-thread blijft bewaard, zodat je later in dezelfde e-mailthread verder kunt gaan.'
                )
            ) {
                return;
            }

            busy = true;
            controls();
            fail('');

            try {
                const result = await api(
                    `${root.dataset.base}/${selected.id}/email-handoff`,
                    'POST',
                    {
                        enabled: false,
                        email: null,
                    }
                );

                if (result?.conversation) {
                    selected = {
                        ...selected,
                        ...result.conversation,
                        id: Number(
                            result.conversation.id
                            || selected.id
                        ),
                    };
                } else {
                    selected.delivery_channel = 'live';
                }

                await detail();
                await inbox();
            } catch (error) {
                fail(error.message);
            } finally {
                busy = false;
                controls();
            }
        }
    );

    emailSettingsButton.addEventListener(
        'click',
        async () => {
            await saveEmailSettings({
                firstHandoff: false,
            });
        }
    );

    closeButton.addEventListener(

        'click',

        async () => {

            if (

                !selected

                || busy

                || stopped

            ) {

                return;

            }

            busy = true;

            controls();

            fail('');

            const nextStatus =

                selected.status === 'closed'

                    ? 'open'

                    : 'closed';

            try {

                await api(

                    `${root.dataset.base}/${selected.id}`,

                    'PATCH',

                    {

                        status:

                            nextStatus,

                    }

                );

                selected.status =

                    nextStatus;

                controls();

                await detail();

                await inbox();

            } catch (error) {

                fail(error.message);

            } finally {

                busy = false;

                controls();

            }

        }

    );

    online.addEventListener(

        'change',

        () => {

            void presence();

        }

    );

    filter.addEventListener(

        'change',

        () => {

            page = 1;

            cachedInboxSignature = '';

            void inbox();

        }

    );

    prevButton.addEventListener(

        'click',

        () => {

            if (page > 1) {

                page -= 1;

                cachedInboxSignature = '';

                void inbox();

            }

        }

    );

    nextButton.addEventListener(

        'click',

        () => {

            if (page < lastPage) {

                page += 1;

                cachedInboxSignature = '';

                void inbox();

            }

        }

    );

    document.addEventListener(

        'visibilitychange',

        () => {

            if (!document.hidden) {

                void presence();

                void inbox();

                void detail();

            }

        }

    );

    window.addEventListener(

        'online',

        () => {

            fail('');

            void presence();

            void inbox();

            void detail();

        }

    );

    window.addEventListener(

        'offline',

        () => {

            fail(

                'Je internetverbinding is weggevallen.'

            );

        }

    );

    window.addEventListener(

        'beforeunload',

        () => {

            clearRecordingTimeout();

            stopAdminTyping();

            hideVisitorTyping();

            if (

                mediaRecorder?.state

                === 'recording'

            ) {

                try {

                    mediaRecorder.stop();

                } catch {

                    // pagina sluit

                }

            }

            stopMediaStream();

        }

    );

    // =========================================================================
    // ADVANCED CHAT TOOLS
    // =========================================================================

    function featureUrl(path = '') {
        return `${root.dataset.base}/${selected?.id || ''}${path}`;
    }

    const advancedBar = document.createElement('div');
    advancedBar.className = 'lca-advanced-tools';
    advancedBar.style.display = 'flex';
    advancedBar.style.flexWrap = 'wrap';
    advancedBar.style.gap = '6px';
    advancedBar.style.padding = '8px';
    advancedBar.style.borderBottom = '1px solid rgba(255,255,255,.12)';
    log.parentElement?.insertBefore(advancedBar, log);

    const addTool = (label, handler) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.textContent = label;
        button.addEventListener('click', handler);
        advancedBar.append(button);
        return button;
    };

    function openInfo(title, content) {
        const overlay = document.createElement('div');
        Object.assign(overlay.style, {position:'fixed', inset:'0', zIndex:'99999', background:'rgba(0,0,0,.72)', display:'grid', placeItems:'center', padding:'20px'});
        const card = document.createElement('div');
        Object.assign(card.style, {width:'min(820px,96vw)', maxHeight:'85vh', overflow:'auto', background:'#11151b', color:'#fff', borderRadius:'16px', padding:'18px'});
        const h = document.createElement('h3'); h.textContent = title;
        const close = document.createElement('button'); close.type='button'; close.textContent='Sluiten'; close.style.float='right'; close.onclick=()=>overlay.remove();
        card.append(close,h);
        if (typeof content === 'string') { const pre=document.createElement('pre'); pre.style.whiteSpace='pre-wrap'; pre.textContent=content; card.append(pre); } else { card.append(content); }
        overlay.append(card); overlay.addEventListener('click', e=>{if(e.target===overlay) overlay.remove();}); document.body.append(overlay);
    }

    addTool('⚙ Chat', async () => {
        if (!selected) return;
        const priority = window.prompt('Prioriteit: low, normal, high of urgent', selected.priority || 'normal');
        if (!priority) return;
        const labels = window.prompt('Labels, gescheiden door komma’s:', Array.isArray(selected.labels) ? selected.labels.join(', ') : '');
        const assigned = window.prompt('Toewijzen aan admin user-ID (leeg = niet wijzigen):', selected.assigned_admin_id || '');
        const payload = {priority, labels: String(labels || '').split(',').map(v=>v.trim()).filter(Boolean)};
        if (assigned !== null && String(assigned).trim() !== '') payload.assigned_admin_id = Number(assigned);
        try {
            await api(featureUrl('/meta'), 'PATCH', payload);
            selected.priority = priority; selected.labels = payload.labels; if (payload.assigned_admin_id) selected.assigned_admin_id = payload.assigned_admin_id; await inbox();
        } catch (error) { fail(error.message); }
    });

    addTool('📝 Notities', async () => {
        if (!selected) return;
        const wrap=document.createElement('div');
        try {
            const data=await api(featureUrl('/notes'));
            (data.items||[]).forEach(n=>{const p=document.createElement('p');p.textContent=`${n.admin_name||'Admin'} · ${n.created_at}: ${n.body}`;wrap.append(p);});
            const b=document.createElement('button');b.type='button';b.textContent='+ notitie';b.onclick=async()=>{const body=window.prompt('Interne notitie:');if(body?.trim()){await api(featureUrl('/notes'),'POST',{body:body.trim()});}};wrap.prepend(b);
            openInfo('Interne notities',wrap);
        } catch(error){fail(error.message);}
    });

    addTool('🖼 Media', async () => {
        if(!selected)return;
        try { const data=await api(featureUrl('/media')); const wrap=document.createElement('div'); wrap.style.display='grid'; wrap.style.gap='10px'; (data.items||[]).forEach(m=>{const a=document.createElement('a');a.href=m.url;a.target='_blank';a.rel='noopener';a.textContent=`${m.type==='video'?'🎬':'📎'} ${m.attachment_name||'Bestand'} · ${Math.round((m.attachment_size||0)/1048576*10)/10} MB`;wrap.append(a);}); openInfo('Media & bestanden',wrap);} catch(error){fail(error.message);}
    });

    addTool('👤 Profiel', async () => { if(!selected)return; try{const d=await api(featureUrl('/profile')); openInfo('Klantprofiel', JSON.stringify(d,null,2));}catch(e){fail(e.message);} });
    addTool('🕘 Geschiedenis', async () => { if(!selected)return; try{const d=await api(featureUrl('/history')); openInfo('Gespreksgeschiedenis',(d.items||[]).map(x=>`${x.created_at} · ${x.actor_name||'Systeem'} · ${x.event_type}`).join('\n')||'Nog geen gebeurtenissen.');}catch(e){fail(e.message);} });
    addTool('📄 PDF', () => { if(selected) window.open(featureUrl('/transcript'),'_blank','noopener'); });
    addTool('🗜 ZIP', () => { if(selected) window.location.href=featureUrl('/export.zip'); });
    addTool('🚫 Blokkeer', async () => { if(!selected)return; const reason=window.prompt('Reden voor blokkeren:','Misbruik'); if(reason===null)return; try{await api(featureUrl('/block'),'POST',{reason}); selected.status='closed'; controls(); await inbox();}catch(e){fail(e.message);} });

    const searchInput=document.createElement('input');
    searchInput.type='search'; searchInput.placeholder='Zoek naam, e-mail of bericht…'; searchInput.style.width='100%'; searchInput.style.padding='9px'; searchInput.style.boxSizing='border-box';
    list.parentElement?.insertBefore(searchInput,list);
    let searchTimer=null;
    searchInput.addEventListener('input',()=>{window.clearTimeout(searchTimer);searchTimer=window.setTimeout(()=>{inboxSearch=searchInput.value.trim();page=1;cachedInboxSignature='';void inbox();},250);});

    const statsNode=document.createElement('div'); statsNode.style.fontSize='12px'; statsNode.style.padding='8px'; list.parentElement?.insertBefore(statsNode,list);
    async function loadStats(){try{const url=new URL(root.dataset.base,window.location.origin);url.pathname=url.pathname.replace(/\/conversations\/?$/,'/stats');const d=await api(url.toString());statsNode.textContent=`Actief ${d.active} · Wacht ${d.waiting} · Ongelezen ${d.unread} · Vandaag ${d.messages_today}`;document.title=d.unread?`(${d.unread}) ${originalDocumentTitle}`:originalDocumentTitle;}catch{}}

    if ('Notification' in window && Notification.permission === 'default') {
        addTool('🔔 Meldingen', () => void Notification.requestPermission());
    }

    const cameraInput=document.createElement('input'); cameraInput.type='file'; cameraInput.accept='image/*,video/*'; cameraInput.capture='environment'; cameraInput.hidden=true; root.append(cameraInput);
    addTool('📷 Camera',()=>cameraInput.click()); cameraInput.addEventListener('change',async()=>{const f=cameraInput.files?.[0];cameraInput.value='';if(f)await handleSelectedFiles([f]);});
    const pauseUploadButton = addTool('⏸ Upload', () => {
        uploadPaused = !uploadPaused;
        pauseUploadButton.textContent = uploadPaused ? '▶ Hervat upload' : '⏸ Upload';
        fail(uploadPaused ? 'Video-upload gepauzeerd. De huidige chunk wordt nog afgerond.' : 'Video-upload hervat.');
    });

    const SpeechRecognition=window.SpeechRecognition||window.webkitSpeechRecognition;
    if(SpeechRecognition){ addTool('🗣 Dicteer',()=>{const r=new SpeechRecognition();r.lang='nl-NL';r.interimResults=false;r.onresult=e=>{input.value=(input.value+' '+e.results[0][0].transcript).trim();autoResizeInput();controls();};r.onerror=()=>fail('Spraak-naar-tekst kon niet worden gestart.');r.start();}); }

    // =========================================================================

    // START

    // =========================================================================

    autoResizeInput();

    controls();

    window.setInterval(

        () => {

            void inbox();

            void detail();

        },

        CONFIG.pollMs

    );

    window.setInterval(

        () => {

            void presence();

        },

        CONFIG.presenceMs

    );

    window.setInterval(

        () => {

            void syncEmailReplies();

        },

        CONFIG.emailSyncMs

    );

    void inbox();
    void loadStats();
    window.setInterval(() => void loadStats(), 15000);

    void presence();

    window.setTimeout(

        () => void syncEmailReplies(),

        1500

    );

})();
