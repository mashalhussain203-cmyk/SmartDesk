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



        requestTimeoutMs: 30000,



        maxTextLength: 4000,



        maxFileBytes: 20 * 1024 * 1024,



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







        appendContent(



            message,



            item



        );







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



            return false;



        } finally {



            busy = false;



            controls();



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







    fileInput.addEventListener(



        'change',



        async () => {



            const file =



                fileInput.files?.[0];







            fileInput.value = '';







            if (!file) {



                return;



            }







            if (



                file.size



                > CONFIG.maxFileBytes



            ) {



                fail(



                    'Het bestand is te groot. Maximaal 20 MB toegestaan.'



                );







                return;



            }







            await sendPayload({



                type: 'file',



                file,



            });



        }



    );







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

            let email =

                String(selected.email || '').trim();

            if (enabling && !email) {

                email = String(

                    window.prompt(

                        'Op welk e-mailadres wil je dit gesprek voortzetten?',

                        ''

                    ) || ''

                ).trim();

                if (!email) {

                    return;

                }

            }

            if (

                !window.confirm(

                    enabling

                        ? `Gesprek voortzetten via e-mail naar ${email}?`

                        : 'Dit gesprek terugzetten naar normale live-chat?'

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

                        enabled: enabling,

                        email: enabling ? email : null,

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

                    selected.delivery_channel =

                        enabling

                            ? 'email'

                            : 'live';

                    if (enabling) {

                        selected.email = email;

                    }

                }

                await detail();

                await inbox();

                if (

                    enabling

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







    void inbox();



    void presence();



})();
