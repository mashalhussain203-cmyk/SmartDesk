(() => {







    'use strict';















    const chat = document.getElementById('guest-chat');















    if (!chat) {







        return;







    }















    const live = chat.querySelector('.lc-panel');















    if (!live) {







        return;







    }















    // =========================================================================







    // ELEMENTEN







    // =========================================================================















    const aiBody = chat.querySelector('.gc-body');







    const aiBottom = chat.querySelector('.gc-bottom');







    const reset = chat.querySelector('.gc-reset');







    const resetConfirm = chat.querySelector('.gc-reset-confirm');







    const header = chat.querySelector('#guest-chat-title');







    const subtitle = chat.querySelector('.gc-subtitle');















    const status = live.querySelector('.lc-status');







    const log = live.querySelector('.lc-log');







    const errorBox = live.querySelector('.lc-error');







    const form = live.querySelector('form');







    const input = live.querySelector('.lc-input');







    const sendButton = live.querySelector('[type="submit"]');







    const reopenButton = live.querySelector('.lc-reopen');







    const fileInput = live.querySelector('.lc-file');







    const voiceButton = live.querySelector('.lc-voice');







    const toggleButton = chat.querySelector('.guest-chat__toggle');







    const guestPanel = chat.querySelector('.guest-chat__panel');















    if (







        !status ||







        !log ||







        !errorBox ||







        !form ||







        !input ||







        !sendButton ||







        !reopenButton ||







        !fileInput ||







        !voiceButton







    ) {







        console.error('[LiveChat] Vereiste HTML-elementen ontbreken.');







        return;







    }















    // =========================================================================







    // INSTELLINGEN







    // =========================================================================















    const POLL_INTERVAL = 1500;







    const REQUEST_TIMEOUT = 30000;







    const MAX_TEXT_LENGTH = 4000;







    const MAX_FILE_SIZE = 20 * 1024 * 1024;







    const MAX_VOICE_SIZE = 15 * 1024 * 1024;







    const MAX_RECORDING_TIME = 5 * 60 * 1000;



    const TYPING_PING_MS = 1000;



    const TYPING_IDLE_MS = 2600;















    const supportedRecorderTypes = [







        'audio/webm;codecs=opus',







        'audio/webm',







        'audio/ogg;codecs=opus',







        'audio/ogg',







        'audio/mp4',







    ];















    // =========================================================================







    // STATUS







    // =========================================================================















    let loaded = false;







    let fetching = false;







    let sending = false;







    let closed = false;







    let emailMode = false;







    let stopped = false;







    let lastMessageId = 0;







    let identity = null;















    const seen = new Set();















    let mediaRecorder = null;







    let mediaStream = null;







    let audioChunks = [];







    let recorderMimeType = '';







    let recordingStartedAt = 0;







    let recordingTimer = null;







    let recordingTimeout = null;







    let lastTypingPingAt = 0;



    let typingStopTimer = null;



    let adminTypingHideTimer = null;



    let adminTypingIndicator = null;















    // =========================================================================







    // ALGEMENE HULPFUNCTIES







    // =========================================================================















    function makeUuid() {







        if (window.crypto?.randomUUID) {







            return window.crypto.randomUUID();







        }















        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(







            /[xy]/g,







            character => {







                const random = Math.random() * 16 | 0;







                const value = character === 'x'







                    ? random







                    : (random & 0x3 | 0x8);















                return value.toString(16);







            }







        );







    }















    function csrfToken() {







        return document







            .querySelector('meta[name="csrf-token"]')







            ?.getAttribute('content') || '';







    }















    function normalizeMime(mime) {







        return String(mime || '')







            .trim()







            .toLowerCase()







            .split(';')[0];







    }















    function formatBytes(bytes) {







        const value = Number(bytes || 0);















        if (value <= 0) {







            return '0 B';







        }















        if (value < 1024) {







            return `${value} B`;







        }















        if (value < 1024 * 1024) {







            return `${(value / 1024).toFixed(1)} KB`;







        }















        return `${(value / (1024 * 1024)).toFixed(1)} MB`;







    }















    function showError(message = '') {







        const text = String(message || '').trim();















        errorBox.textContent = text;







        errorBox.hidden = text === '';







    }















    function setStatus(message = '') {







        status.textContent = String(message || '');







    }















    function scrollToBottom(smooth = false) {







        if (typeof log.scrollTo === 'function') {







            log.scrollTo({







                top: log.scrollHeight,







                behavior: smooth ? 'smooth' : 'auto',







            });















            return;







        }















        log.scrollTop = log.scrollHeight;







    }















    function nearBottom() {







        return (







            log.scrollHeight







            - log.scrollTop







            - log.clientHeight







        ) < 120;







    }















    function autoResizeTextarea() {







        input.style.height = 'auto';















        const height = Math.min(







            Math.max(input.scrollHeight, 44),







            160







        );















        input.style.height = `${height}px`;







    }















    function updateControls() {







        const unavailable =







            stopped ||







            closed ||







            emailMode ||







            sending ||







            !loaded;















        input.disabled =







            stopped ||







            closed ||







            emailMode ||







            sending;















        fileInput.disabled = unavailable;







        voiceButton.disabled = unavailable;















        sendButton.disabled =







            unavailable ||







            input.value.trim() === '';







    }















    // =========================================================================







    // =========================================================================



    // LIVE TYPING INDICATOR



    // =========================================================================







    function typingEndpoint() {

        if (live.dataset.typing) {

            return live.dataset.typing;

        }



        const storeUrl = new URL(

            live.dataset.store,

            window.location.origin

        );



        storeUrl.pathname = storeUrl.pathname.replace(

            /\/messages\/?$/,

            '/typing'

        );



        return storeUrl.toString();

    }



    function ensureAdminTypingIndicator() {



        if (adminTypingIndicator) {



            return adminTypingIndicator;



        }







        const styleId = 'lc-live-typing-style';







        if (!document.getElementById(styleId)) {



            const style = document.createElement('style');



            style.id = styleId;



            style.textContent = `



                .lc-live-typing {



                    display:flex;



                    align-items:flex-end;



                    gap:8px;



                    padding:6px 10px 8px;



                }



                .lc-live-typing[hidden] { display:none !important; }



                .lc-live-typing__avatar {



                    width:28px;



                    height:28px;



                    flex:0 0 28px;



                    display:grid;



                    place-items:center;



                    overflow:hidden;



                    border-radius:50%;



                    background:#343d49;



                    color:#fff;



                    font-size:10px;



                    font-weight:800;



                }



                .lc-live-typing__avatar img {



                    width:100%;



                    height:100%;



                    object-fit:cover;



                }



                .lc-live-typing__bubble {



                    display:flex;



                    align-items:center;



                    gap:4px;



                    min-height:36px;



                    padding:10px 13px;



                    border-radius:15px 15px 15px 5px;



                    background:#e8e8ea;



                    color:#45484d;



                    box-shadow:0 5px 18px rgba(0,0,0,.10);



                }



                .lc-live-typing__dot {



                    width:6px;



                    height:6px;



                    border-radius:50%;



                    background:#878b92;



                    animation:lcLiveTypingDot 1.15s infinite ease-in-out;



                }



                .lc-live-typing__dot:nth-child(2) { animation-delay:.15s; }



                .lc-live-typing__dot:nth-child(3) { animation-delay:.30s; }



                .lc-live-typing__label {



                    align-self:center;



                    color:#858a93;



                    font-size:10px;



                }



                @keyframes lcLiveTypingDot {



                    0%, 60%, 100% { opacity:.35; transform:translateY(0); }



                    30% { opacity:1; transform:translateY(-4px); }



                }



                @media (prefers-reduced-motion: reduce) {



                    .lc-live-typing__dot { animation:none; opacity:.75; }



                }



            `;



            document.head.append(style);



        }







        const wrapper = document.createElement('div');



        wrapper.className = 'lc-live-typing';



        wrapper.hidden = true;



        wrapper.setAttribute('aria-live', 'polite');



        wrapper.innerHTML = `



            <span class="lc-live-typing__avatar" data-live-typing-avatar>M</span>



            <span class="lc-live-typing__bubble" aria-hidden="true">



                <span class="lc-live-typing__dot"></span>



                <span class="lc-live-typing__dot"></span>



                <span class="lc-live-typing__dot"></span>



            </span>



            <span class="lc-live-typing__label" data-live-typing-label>Medewerker typt…</span>



        `;







        form.parentNode?.insertBefore(wrapper, form);



        adminTypingIndicator = wrapper;



        return wrapper;



    }







    function hideAdminTyping() {



        if (adminTypingHideTimer) {



            window.clearTimeout(adminTypingHideTimer);



            adminTypingHideTimer = null;



        }







        if (adminTypingIndicator) {



            adminTypingIndicator.hidden = true;



        }



    }







    function syncAdminTyping(data) {



        const info = data?.typing?.admin || {};



        const active = Boolean(



            data?.admin_typing === true ||



            info?.active === true



        );







        if (!active || closed) {



            hideAdminTyping();



            return;



        }







        const indicator = ensureAdminTypingIndicator();



        const name = String(info?.name || 'Medewerker').trim();



        const avatar = indicator.querySelector('[data-live-typing-avatar]');



        const label = indicator.querySelector('[data-live-typing-label]');







        if (label) {



            label.textContent = `${name} typt…`;



        }







        if (avatar) {



            avatar.replaceChildren();







            if (info?.avatar) {



                const image = document.createElement('img');



                image.src = info.avatar;



                image.alt = '';



                image.loading = 'lazy';



                avatar.append(image);



            } else {



                avatar.textContent = name.charAt(0).toUpperCase() || 'M';



            }



        }







        indicator.hidden = false;







        if (adminTypingHideTimer) {



            window.clearTimeout(adminTypingHideTimer);



        }







        adminTypingHideTimer = window.setTimeout(



            hideAdminTyping,



            6500



        );



    }







    async function sendVisitorTyping(active) {



        if (



            stopped ||



            closed ||



            emailMode ||



            chat.dataset.mode !== 'human'



        ) {



            return;



        }







        try {



            await api(



                typingEndpoint(),



                'POST',



                { typing: Boolean(active) }



            );



        } catch (exception) {



            console.debug('[LiveChat] typing heartbeat mislukt', exception);



        }



    }







    function stopVisitorTyping() {



        if (typingStopTimer) {



            window.clearTimeout(typingStopTimer);



            typingStopTimer = null;



        }







        lastTypingPingAt = 0;



        void sendVisitorTyping(false);



    }







    function queueVisitorTyping() {



        if (



            stopped ||



            closed ||



            emailMode ||



            sending ||



            chat.dataset.mode !== 'human'



        ) {



            return;



        }







        const hasText = input.value.trim() !== '';







        if (!hasText) {



            stopVisitorTyping();



            return;



        }







        const now = Date.now();







        if (now - lastTypingPingAt >= TYPING_PING_MS) {



            lastTypingPingAt = now;



            void sendVisitorTyping(true);



        }







        if (typingStopTimer) {



            window.clearTimeout(typingStopTimer);



        }







        typingStopTimer = window.setTimeout(



            stopVisitorTyping,



            TYPING_IDLE_MS



        );



    }







    // API







    // =========================================================================















    async function responseJson(response) {







        try {







            return await response.json();







        } catch {







            return null;







        }







    }















    function firstValidationError(payload) {







        const errors = payload?.errors;















        if (errors && typeof errors === 'object') {







            const preferredFields = [







                'attachment',







                'body',







                'client_id',







                'type',







            ];















            for (const field of preferredFields) {







                const messages = errors[field];















                if (Array.isArray(messages) && messages.length > 0) {







                    return String(messages[0]);







                }







            }















            for (const messages of Object.values(errors)) {







                if (Array.isArray(messages) && messages.length > 0) {







                    return String(messages[0]);







                }







            }







        }















        return payload?.message







            ? String(payload.message)







            : 'De invoer is ongeldig.';







    }















    async function api(







        url,







        method = 'GET',







        data = undefined







    ) {







        const isForm = data instanceof FormData;















        const headers = {







            Accept: 'application/json',







            'X-CSRF-TOKEN': csrfToken(),







        };















        if (!isForm && data !== undefined) {







            headers['Content-Type'] = 'application/json';







        }















        const controller = new AbortController();















        const timeoutId = window.setTimeout(







            () => controller.abort(),







            REQUEST_TIMEOUT







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







                    : (







                        isForm







                            ? data







                            : JSON.stringify(data)







                    ),







                signal: controller.signal,







            });







        } catch (exception) {







            if (exception?.name === 'AbortError') {







                const timeoutError = new Error(







                    'De verbinding duurt te lang. Probeer opnieuw.'







                );















                timeoutError.name = 'TimeoutError';















                throw timeoutError;







            }















            if (!navigator.onLine) {







                throw new Error(







                    'Je internetverbinding is weggevallen.'







                );







            }















            throw new Error(







                'Live chat kon geen verbinding maken met de server.'







            );







        } finally {







            window.clearTimeout(timeoutId);







        }















        const payload = await responseJson(response);















        /*
         * /typing is ondersteunende UI.
         * Een mislukte typing-heartbeat mag de live chat niet stoppen.
         */
        const requestUrl = String(url || '');

        const auxiliaryRequest =
            /\/typing(?:[/?]|$)/.test(requestUrl);

        if (
            auxiliaryRequest
            && (
                response.status === 401
                || response.status === 403
                || response.status === 419
            )
        ) {
            throw new Error(
                'Typingstatus kon niet worden bijgewerkt.'
            );
        }



        if (response.ok) {







            return payload || {};







        }















        if (







            response.status === 401 ||







            response.status === 419







        ) {







            stopped = true;







            loaded = false;















            input.disabled = true;







            sendButton.disabled = true;







            fileInput.disabled = true;







            voiceButton.disabled = true;















            throw new Error(







                'Je sessie is verlopen. Vernieuw de pagina voordat je verdergaat.'







            );







        }















        if (response.status === 422) {







            throw new Error(







                firstValidationError(payload)







            );







        }















        if (response.status === 409) {







            throw new Error(







                payload?.message ||







                'Dit gesprek is gesloten of gewijzigd.'







            );







        }















        if (response.status === 429) {







            throw new Error(







                'Te veel verzoeken. Wacht even en probeer opnieuw.'







            );







        }















        if (response.status === 404) {







            throw new Error(







                payload?.message ||







                'Dit gesprek of bericht bestaat niet meer.'







            );







        }















        throw new Error(







            payload?.message ||







            'Live chat is tijdelijk niet bereikbaar. Probeer opnieuw.'







        );







    }















    // =========================================================================







    // PROFIELFOTO







    // =========================================================================















    function avatarNode(message) {







        const avatar = document.createElement('span');















        avatar.className = 'lc-avatar';















        const name =







            message.sender_name ||







            (







                message.sender === 'visitor'







                    ? 'Gast'







                    : 'Medewerker'







            );















        if (message.sender_avatar) {







            const image = document.createElement('img');















            image.src = message.sender_avatar;







            image.alt = '';







            image.loading = 'lazy';















            image.addEventListener(







                'error',







                () => {







                    image.remove();















                    avatar.textContent = name







                        .trim()







                        .charAt(0)







                        .toUpperCase();







                },







                { once: true }







            );















            avatar.append(image);















            return avatar;







        }















        avatar.textContent = name







            .trim()







            .charAt(0)







            .toUpperCase();















        return avatar;







    }















    // =========================================================================







    // BERICHT-INHOUD







    // =========================================================================















    function renderImage(message, item) {







        const link = document.createElement('a');















        link.href = message.attachment_url;







        link.target = '_blank';







        link.rel = 'noopener noreferrer';















        const image = document.createElement('img');















        image.className = 'lc-media-image';







        image.src = message.attachment_url;







        image.alt =







            message.attachment_name ||







            'Afbeelding';















        image.loading = 'lazy';















        link.append(image);







        item.append(link);







    }















    function renderVoice(message, item) {







        const audio = document.createElement('audio');















        audio.controls = true;







        audio.preload = 'metadata';







        audio.src = message.attachment_url;















        item.append(audio);







    }















    function renderFile(message, item) {







        const link = document.createElement('a');















        link.className = 'lc-file-link';







        link.href = message.attachment_url;







        link.target = '_blank';







        link.rel = 'noopener noreferrer';















        const name =







            message.attachment_name ||







            'Bestand openen';















        if (message.attachment_size) {







            link.textContent =







                `📎 ${name} · ${formatBytes(message.attachment_size)}`;







        } else {







            link.textContent =







                `📎 ${name}`;







        }















        item.append(link);







    }















    function messageContent(message, item) {







        if (message.body) {







            const paragraph =







                document.createElement('p');















            paragraph.textContent =







                message.body;















            item.append(paragraph);







        }















        if (!message.attachment_url) {







            return;







        }















        const mime =







            normalizeMime(







                message.attachment_mime







            );















        if (mime.startsWith('image/')) {







            renderImage(message, item);







            return;







        }















        if (







            message.type === 'voice' ||







            mime.startsWith('audio/') ||







            mime === 'video/webm' ||







            mime === 'application/ogg'







        ) {







            renderVoice(message, item);







            return;







        }















        renderFile(message, item);







    }















    // =========================================================================







    // BERICHT TOEVOEGEN







    // =========================================================================















    function appendMessage(message) {







        const id = Number(message.id);















        if (!Number.isFinite(id)) {







            return;







        }















        if (seen.has(id)) {







            return;







        }















        const shouldScroll =







            nearBottom() ||







            message.sender === 'visitor';















        seen.add(id);















        lastMessageId = Math.max(







            lastMessageId,







            id







        );















        const item =







            document.createElement('article');















        item.className =







            `lc-msg${







                message.sender === 'visitor'







                    ? ' lc-msg--visitor'







                    : ''







            }`;















        item.dataset.messageId =







            String(id);















        item.dataset.sender =







            String(message.sender || '');















        const head =







            document.createElement('div');















        head.className =







            'lc-msg-head';















        const label =







            document.createElement('small');















        label.textContent =







            message.sender_name ||







            (







                message.sender === 'visitor'







                    ? 'JIJ'







                    : 'MEDEWERKER'







            );















        head.append(







            avatarNode(message),







            label







        );















        item.append(head);















        messageContent(







            message,







            item







        );















        if (message.sender === 'visitor') {







            const remove =







                document.createElement('button');















            remove.type = 'button';







            remove.className =







                'lc-delete';















            remove.textContent =







                'Verwijderen';















            remove.addEventListener(







                'click',







                async () => {







                    if (remove.disabled) {







                        return;







                    }















                    remove.disabled = true;















                    try {







                        await api(







                            `${live.dataset.store}/${id}`,







                            'DELETE'







                        );















                        item.remove();







                        seen.delete(id);















                        showError('');







                    } catch (exception) {







                        remove.disabled = false;















                        showError(







                            exception.message







                        );







                    }







                }







            );















            item.append(remove);







        }















        log.append(item);















        if (shouldScroll) {







            scrollToBottom(true);







        }







    }















    // =========================================================================







    // POLLING







    // =========================================================================















    async function poll(force = false) {







        if (







            fetching ||







            stopped ||







            (!force && document.hidden) ||







            chat.dataset.mode !== 'human' ||







            (!force && guestPanel?.hidden)







        ) {







            return;







        }















        fetching = true;















        try {







            let data = await api(







                `${live.dataset.show}?after=${lastMessageId}`







            );















            /*







             * Als de server een andere conversation identity teruggeeft,







             * laden we DIRECT opnieuw vanaf after=0. Voorheen stopte poll()







             * hier, waardoor loaded=false bleef en het formulier daarna







             * ieder bericht stil blokkeerde.







             */







            if (







                identity &&







                data.identity &&







                identity !== data.identity







            ) {







                log.replaceChildren();







                seen.clear();







                lastMessageId = 0;







                loaded = false;







                identity = data.identity;















                setStatus(







                    'Je huidige gesprek wordt geladen…'







                );















                data = await api(







                    `${live.dataset.show}?after=0`







                );







            }















            if (data.identity) {







                identity = data.identity;







            }















            const firstLoad =







                !loaded;















            const messages =







                Array.isArray(data.messages)







                    ? data.messages







                    : [];















            messages.forEach(







                appendMessage







            );















            closed =







                data.conversation?.status







                === 'closed';















            emailMode =







                data.conversation?.delivery_channel







                === 'email';















            if (emailMode) {







                hideAdminTyping();







            } else {







                syncAdminTyping(data);







            }















            reopenButton.hidden =







                !closed







                || emailMode;















            loaded = true;















            if (emailMode) {







                const contactEmail = String(







                    data.conversation?.contact_email







                    || ''







                ).trim();















                setStatus(







                    'Dit gesprek gaat verder via e-mail'







                    + (







                        contactEmail







                            ? ` naar ${contactEmail}`







                            : ''







                    )







                    + '. Antwoord op de e-mail van Mashal Support om verder te chatten.'







                );







            } else if (closed) {







                setStatus(







                    'Dit gesprek is afgesloten. Je kunt het opnieuw openen.'







                );







            } else if (data.online) {







                setStatus(







                    'Er is een medewerker beschikbaar. Stuur gerust je bericht.'







                );







            } else {







                setStatus(







                    'Er is nu geen medewerker beschikbaar. Laat een bericht achter en kom later terug in deze chat.'







                );







            }















            if (!data.conversation) {







                status.textContent +=







                    ' Je gesprek start zodra je een bericht stuurt.';







            }















            if (firstLoad) {







                scrollToBottom(false);







            }















            if (!sending) {







                showError('');







            }















            updateControls();







        } catch (exception) {







            showError(







                exception?.message ||







                'Live chat kon niet worden bijgewerkt.'







            );







        } finally {







            fetching = false;







        }







    }















    // =========================================================================







    // BERICHT VERSTUREN







    // =========================================================================















    async function sendPayload({







        body = null,







        type = 'text',







        file = null,







    }) {







        if (







            sending ||







            stopped ||







            closed ||







            emailMode







        ) {







            return false;







        }















        sending = true;







        updateControls();















        showError('');















        try {







            let payload;















            if (file) {







                payload =







                    new FormData();















                payload.append(







                    'client_id',







                    makeUuid()







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







                    file.name ||







                    `${type}-${Date.now()}`







                );







            } else {







                payload = {







                    body,







                    type,







                    client_id:







                        makeUuid(),







                };







            }















            await api(







                live.dataset.store,







                'POST',







                payload







            );















            /*
             * De server heeft het bericht geaccepteerd.
             * Houd de composer actief, ook als de directe poll tegelijk
             * met een achtergrondpoll loopt of tijdelijk faalt.
             */
            loaded = true;



            if (type === 'text') {







                input.value = '';







                autoResizeTextarea();







            }















            await poll(true);















            return true;







        } catch (exception) {







            showError(







                exception?.name === 'TimeoutError'







                    ? 'Geen bevestiging ontvangen. Probeer opnieuw.'







                    : exception?.message ||







                      'Het bericht kon niet worden verstuurd.'







            );















            return false;







        } finally {







            sending = false;







            updateControls();







        }







    }















    // =========================================================================







    // HUMAN HANDOFF







    // =========================================================================















    async function activateHuman(







        initialBody = ''







    ) {







        chat.dataset.mode =







            'human';















        if (aiBody) {







            aiBody.hidden = true;







        }















        if (aiBottom) {







            aiBottom.hidden = true;







        }















        if (reset) {







            reset.hidden = true;







        }















        if (resetConfirm) {







            resetConfirm.hidden = true;







        }















        live.hidden = false;



        ensureAdminTypingIndicator();















        /*







         * Op mobiel kan de buitenste guest panel nog hidden zijn.







         * De gewone poll() weigerde dan te laden, waardoor loaded=false bleef.







         */







        if (guestPanel) {







            guestPanel.hidden = false;







        }















        if (header) {







            header.textContent =







                'Mashal support';







        }















        if (subtitle) {







            subtitle.textContent =







                'Live contact met een medewerker';







        }















        await poll(true);















        const message =







            String(initialBody || '')







                .trim();















        if (message) {







            await sendPayload({







                body: message,







                type: 'text',







            });







        }















        if (







            !window.matchMedia(







                '(max-width: 699px)'







            ).matches







        ) {







            input.focus();







        }







    }















    // =========================================================================







    // BESTANDEN







    // =========================================================================















    function validateFile(file) {







        if (!(file instanceof File)) {







            throw new Error(







                'Het gekozen bestand is ongeldig.'







            );







        }















        if (file.size <= 0) {







            throw new Error(







                'Het gekozen bestand is leeg.'







            );







        }















        if (file.size > MAX_FILE_SIZE) {







            throw new Error(







                'Het bestand is te groot. Maximaal 20 MB toegestaan.'







            );







        }







    }















    // =========================================================================







    // SPRAAKBERICHTEN







    // =========================================================================















    function chooseRecorderMimeType() {







        if (!window.MediaRecorder) {







            return '';







        }















        for (







            const mimeType







            of supportedRecorderTypes







        ) {







            try {







                if (







                    MediaRecorder







                        .isTypeSupported(







                            mimeType







                        )







                ) {







                    return mimeType;







                }







            } catch {







                // Volgende type proberen.







            }







        }















        return '';







    }















    function normalizedVoiceMime(







        mimeType







    ) {







        const value =







            String(mimeType || '')







                .trim()







                .toLowerCase();















        if (







            value.startsWith(







                'audio/webm'







            )







        ) {







            return 'audio/webm';







        }















        if (







            value.startsWith(







                'audio/ogg'







            )







        ) {







            return 'audio/ogg';







        }















        if (







            value.startsWith(







                'audio/mp4'







            )







        ) {







            return 'audio/mp4';







        }















        if (







            value.startsWith(







                'video/webm'







            )







        ) {







            return 'video/webm';







        }















        return normalizeMime(value)







            || 'audio/webm';







    }















    function voiceExtension(







        mimeType







    ) {







        const mime =







            normalizeMime(mimeType);















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







            mime.includes('mpeg') ||







            mime.includes('mp3')







        ) {







            return 'mp3';







        }















        return 'webm';







    }















    function clearRecordingTimers() {







        if (recordingTimer) {







            window.clearInterval(







                recordingTimer







            );















            recordingTimer = null;







        }















        if (recordingTimeout) {







            window.clearTimeout(







                recordingTimeout







            );















            recordingTimeout = null;







        }







    }















    function stopMediaStream() {







        if (!mediaStream) {







            return;







        }















        mediaStream







            .getTracks()







            .forEach(track => {







                try {







                    track.stop();







                } catch {







                    // Geen probleem.







                }







            });















        mediaStream = null;







    }















    function resetVoiceUi() {







        clearRecordingTimers();















        voiceButton.setAttribute(







            'aria-pressed',







            'false'







        );















        voiceButton.textContent =







            '🎤';















        voiceButton.title =







            'Spraakbericht opnemen';















        recordingStartedAt = 0;















        updateControls();







    }















    function cleanupRecorder() {







        stopMediaStream();







        clearRecordingTimers();















        mediaRecorder = null;







        audioChunks = [];







        recorderMimeType = '';















        resetVoiceUi();







    }















    function updateRecordingStatus() {







        if (!recordingStartedAt) {







            return;







        }















        const elapsed =







            Math.floor(







                (







                    Date.now()







                    - recordingStartedAt







                ) / 1000







            );















        const minutes =







            Math.floor(elapsed / 60);















        const seconds =







            String(







                elapsed % 60







            ).padStart(2, '0');















        setStatus(







            `Opname ${minutes}:${seconds} — klik opnieuw om te stoppen en te versturen.`







        );







    }















    async function handleRecordingStopped() {







        const recorder =







            mediaRecorder;















        const chunks =







            [...audioChunks];















        const originalMime =







            recorder?.mimeType ||







            recorderMimeType ||







            'audio/webm';















        const mime =







            normalizedVoiceMime(







                originalMime







            );















        stopMediaStream();







        clearRecordingTimers();















        mediaRecorder = null;







        audioChunks = [];







        recorderMimeType = '';















        resetVoiceUi();















        const totalBytes =







            chunks.reduce(







                (total, chunk) =>







                    total







                    + Number(







                        chunk?.size || 0







                    ),







                0







            );















        if (totalBytes <= 0) {







            showError(







                'De opname bevat geen gegevens. Probeer opnieuw.'







            );















            return;







        }















        const blob =







            new Blob(







                chunks,







                {







                    type: mime,







                }







            );















        if (blob.size <= 0) {







            showError(







                'Het spraakbericht is leeg. Probeer opnieuw.'







            );















            return;







        }















        if (







            blob.size >







            MAX_VOICE_SIZE







        ) {







            showError(







                'Het spraakbericht is te groot. Maximaal 15 MB toegestaan.'







            );















            return;







        }















        const extension =







            voiceExtension(mime);















        const file =







            new File(







                [blob],







                `spraakbericht-${Date.now()}.${extension}`,







                {







                    type: mime,







                    lastModified:







                        Date.now(),







                }







            );















        setStatus(







            `Spraakbericht wordt verstuurd (${formatBytes(file.size)})…`







        );















        const success =







            await sendPayload({







                type: 'voice',







                file,







            });















        if (!success) {







            console.error(







                '[LiveChat] Spraakbericht kon niet worden verstuurd.',







                {







                    name:







                        file.name,







                    type:







                        file.type,







                    size:







                        file.size,







                }







            );







        }







    }















    async function startRecording() {







        if (







            !navigator.mediaDevices







                ?.getUserMedia ||







            !window.MediaRecorder







        ) {







            showError(







                'Spraakopname wordt niet ondersteund in deze browser.'







            );















            return;







        }















        if (







            closed ||







            emailMode ||







            stopped ||







            sending ||







            !loaded







        ) {







            return;







        }















        showError('');















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















            recorderMimeType =







                chooseRecorderMimeType();















            const options =







                recorderMimeType







                    ? {







                        mimeType:







                            recorderMimeType,







                    }







                    : undefined;















            mediaRecorder =







                new MediaRecorder(







                    mediaStream,







                    options







                );















            mediaRecorder.addEventListener(







                'dataavailable',







                event => {







                    if (







                        event.data &&







                        event.data.size > 0







                    ) {







                        audioChunks.push(







                            event.data







                        );







                    }







                }







            );















            mediaRecorder.addEventListener(







                'error',







                event => {







                    console.error(







                        '[LiveChat] MediaRecorder fout:',







                        event







                    );















                    showError(







                        'Er ging iets mis tijdens de spraakopname.'







                    );















                    cleanupRecorder();







                }







            );















            mediaRecorder.addEventListener(







                'stop',







                () => {







                    void handleRecordingStopped();







                },







                {







                    once: true,







                }







            );















            mediaRecorder.start(250);















            recordingStartedAt =







                Date.now();















            voiceButton.setAttribute(







                'aria-pressed',







                'true'







            );















            voiceButton.textContent =







                '■';















            voiceButton.title =







                'Opname stoppen en versturen';















            updateRecordingStatus();















            recordingTimer =







                window.setInterval(







                    updateRecordingStatus,







                    1000







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







                    MAX_RECORDING_TIME







                );







        } catch (exception) {







            console.error(







                '[LiveChat] Microfoonfout:',







                exception







            );















            cleanupRecorder();















            if (







                exception?.name







                === 'NotAllowedError'







            ) {







                showError(







                    'Microfoontoegang is geweigerd. Sta microfoontoegang toe in je browser.'







                );















                return;







            }















            if (







                exception?.name







                === 'NotFoundError'







            ) {







                showError(







                    'Er is geen microfoon gevonden.'







                );















                return;







            }















            if (







                exception?.name







                === 'NotReadableError'







            ) {







                showError(







                    'De microfoon kan momenteel niet worden gebruikt.'







                );















                return;







            }















            showError(







                'Microfoontoegang is niet beschikbaar.'







            );







        }







    }















    function stopRecording() {







        if (







            !mediaRecorder ||







            mediaRecorder.state







                !== 'recording'







        ) {







            return;







        }















        voiceButton.disabled =







            true;















        try {







            mediaRecorder.requestData();







        } catch {







            // Niet iedere browser vereist dit.







        }















        mediaRecorder.stop();







    }















    // =========================================================================







    // FORMULIER







    // =========================================================================















    form.addEventListener(







        'submit',







        async event => {







            event.preventDefault();















            const body =







                input.value.trim();















            if (







                body === '' ||







                body.length >







                    MAX_TEXT_LENGTH ||







                closed ||







                emailMode ||







                stopped







            ) {







                return;







            }















            stopVisitorTyping();







            await sendPayload({







                body,







                type: 'text',







            });







        }







    );















    input.addEventListener(







        'input',







        () => {







            autoResizeTextarea();







            updateControls();







            queueVisitorTyping();







        }







    );















    input.addEventListener(







        'keydown',







        event => {







            if (







                event.key !== 'Enter' ||







                event.shiftKey ||







                event.isComposing







            ) {







                return;







            }















            event.preventDefault();















            if (







                !sendButton.disabled







            ) {







                form.requestSubmit();







            }







        }







    );















    // =========================================================================







    // BESTANDSKNOP







    // =========================================================================















    fileInput.addEventListener(







        'change',







        async () => {







            const file =







                fileInput.files?.[0];















            fileInput.value = '';















            if (!file) {







                return;







            }















            try {







                validateFile(file);















                setStatus(







                    `Bestand wordt verstuurd (${formatBytes(file.size)})…`







                );















                await sendPayload({







                    type: 'file',







                    file,







                });







            } catch (exception) {







                showError(







                    exception.message







                );







            }







        }







    );















    // =========================================================================







    // MICROFOONKNOP







    // =========================================================================















    voiceButton.addEventListener(







        'click',







        () => {







            if (







                mediaRecorder?.state







                === 'recording'







            ) {







                stopRecording();







                return;







            }















            void startRecording();







        }







    );















    // =========================================================================







    // GESPREK HEROPENEN







    // =========================================================================















    reopenButton.addEventListener(







        'click',







        async () => {







            if (







                reopenButton.disabled







            ) {







                return;







            }















            reopenButton.disabled =







                true;















            try {







                await api(







                    live.dataset.reopen,







                    'POST',







                    {}







                );















                closed = false;







                emailMode = false;















                await poll();















                input.focus();







            } catch (exception) {







                showError(







                    exception.message







                );







            } finally {







                reopenButton.disabled =







                    false;







            }







        }







    );















    // =========================================================================







    // AI -> MEDEWERKER HANDOFF







    // =========================================================================















    chat.addEventListener(







        'live-chat:handoff',







        event => {







            const body =







                String(







                    event.detail?.body ||







                    ''







                ).trim();















            void activateHuman(body).catch(exception => {







                showError(







                    exception?.message ||







                    'Live medewerker kon niet worden geopend.'







                );







            });







        }







    );















    // =========================================================================







    // CHAT OPENEN







    // =========================================================================















    toggleButton?.addEventListener(







        'click',







        () => {







            if (







                chat.dataset.mode







                === 'human'







            ) {







                void poll();







            }







        }







    );















    // =========================================================================







    // TABBLAD







    // =========================================================================















    document.addEventListener(







        'visibilitychange',







        () => {







            if (







                !document.hidden &&







                chat.dataset.mode







                    === 'human'







            ) {







                void poll();







            }







        }







    );















    // =========================================================================







    // INTERNETSTATUS







    // =========================================================================















    window.addEventListener(







        'online',







        () => {







            showError('');















            if (







                chat.dataset.mode







                === 'human'







            ) {







                setStatus(







                    'Verbinding hersteld. Gesprek wordt bijgewerkt…'







                );















                void poll();







            }















            updateControls();







        }







    );















    window.addEventListener(







        'offline',







        () => {







            setStatus(







                'Je bent offline. Controleer je internetverbinding.'







            );















            sendButton.disabled =







                true;















            fileInput.disabled =







                true;















            voiceButton.disabled =







                true;







        }







    );















    // =========================================================================







    // OPRUIMEN







    // =========================================================================















    window.addEventListener(







        'beforeunload',







        () => {







            clearRecordingTimers();







            stopVisitorTyping();



            hideAdminTyping();















            if (







                mediaRecorder?.state







                === 'recording'







            ) {







                try {







                    mediaRecorder.stop();







                } catch {







                    // Pagina sluit al.







                }







            }















            stopMediaStream();







        }







    );















    // =========================================================================







    // START







    // =========================================================================















    sendButton.disabled = true;















    voiceButton.setAttribute(







        'aria-pressed',







        'false'







    );















    autoResizeTextarea();







    updateControls();















    window.setInterval(







        () => {







            void poll();







        },







        POLL_INTERVAL







    );







})();
