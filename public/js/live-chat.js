
(() => {
    const chat = document.getElementById('guest-chat');

    if (!chat) {
        return;
    }

    const live = chat.querySelector('.lc-panel');

    if (!live) {
        return;
    }

    const aiBody = chat.querySelector('.gc-body');
    const aiBottom = chat.querySelector('.gc-bottom');
    const reset = chat.querySelector('.gc-reset');
    const header = chat.querySelector('#guest-chat-title');
    const subtitle = chat.querySelector('.gc-subtitle');

    const status = live.querySelector('.lc-status');
    const log = live.querySelector('.lc-log');
    const error = live.querySelector('.lc-error');
    const input = live.querySelector('.lc-input');
    const send = live.querySelector('[type="submit"]');
    const reopen = live.querySelector('.lc-reopen');
    const fileInput = live.querySelector('.lc-file');
    const voiceButton = live.querySelector('.lc-voice');

    let loaded = false;
    let fetching = false;
    let sending = false;
    let closed = false;
    let last = 0;
    let stopped = false;
    let identity = null;

    let mediaRecorder = null;
    let mediaStream = null;
    let audioChunks = [];
    let selectedRecorderMime = '';

    const seen = new Set();

    send.disabled = true;

    const uuid = () => crypto.randomUUID();

    const csrf = () => {
        return document
            .querySelector('meta[name="csrf-token"]')
            ?.content || '';
    };

    function failure(message = '') {
        error.textContent = message;
        error.hidden = !message;
    }

    async function api(
        url,
        method = 'GET',
        data = undefined
    ) {
        const isForm = data instanceof FormData;

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

        const response = await fetch(
            url,
            {
                method,
                credentials: 'same-origin',
                cache: 'no-store',
                headers,

                body:
                    data === undefined
                        ? undefined
                        : isForm
                            ? data
                            : JSON.stringify(data),

                signal:
                    AbortSignal.timeout(30000),
            }
        );

        if (!response.ok) {
            if (
                response.status === 401
                || response.status === 419
            ) {
                stopped = true;
                loaded = false;

                send.disabled = true;
                input.disabled = true;
                fileInput.disabled = true;
                voiceButton.disabled = true;

                log.replaceChildren();

                throw new Error(
                    'Je sessie is verlopen. Vernieuw de pagina voordat je verdergaat.'
                );
            }

            let payload = null;

            try {
                payload = await response.json();
            } catch {
                payload = null;
            }

            if (response.status === 422) {
                const validationMessage =
                    payload?.errors?.attachment?.[0]
                    || payload?.errors?.body?.[0]
                    || payload?.errors?.client_id?.[0]
                    || payload?.message
                    || 'Dit bestand of spraakbericht wordt niet ondersteund of is te groot.';

                throw new Error(
                    validationMessage
                );
            }

            if (response.status === 429) {
                throw new Error(
                    'Te veel verzoeken. Wacht even en probeer opnieuw.'
                );
            }

            if (response.status === 409) {
                throw new Error(
                    'Dit gesprek is gesloten of gewijzigd. Controleer de status en probeer opnieuw.'
                );
            }

            throw new Error(
                payload?.message
                || 'Live chat is tijdelijk niet bereikbaar. Probeer opnieuw.'
            );
        }

        return response.json();
    }

    function avatarNode(message) {
        const avatar =
            document.createElement('span');

        avatar.className =
            'lc-avatar';

        if (message.sender_avatar) {
            const img =
                document.createElement('img');

            img.src =
                message.sender_avatar;

            img.alt = '';
            img.loading = 'lazy';

            avatar.append(img);
        } else {
            avatar.textContent = (
                message.sender_name
                || (
                    message.sender === 'visitor'
                        ? 'G'
                        : 'M'
                )
            )
                .trim()
                .charAt(0)
                .toUpperCase();
        }

        return avatar;
    }

    function messageContent(
        message,
        item
    ) {
        if (message.body) {
            const text =
                document.createElement('p');

            text.textContent =
                message.body;

            item.append(text);
        }

        if (!message.attachment_url) {
            return;
        }

        const mime = String(
            message.attachment_mime || ''
        ).toLowerCase();

        if (mime.startsWith('image/')) {
            const link =
                document.createElement('a');

            link.href =
                message.attachment_url;

            link.target =
                '_blank';

            link.rel =
                'noopener';

            const img =
                document.createElement('img');

            img.className =
                'lc-media-image';

            img.src =
                message.attachment_url;

            img.alt =
                message.attachment_name
                || 'Afbeelding';

            img.loading =
                'lazy';

            link.append(img);
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
            'lc-file-link';

        link.href =
            message.attachment_url;

        link.target =
            '_blank';

        link.rel =
            'noopener';

        link.textContent =
            `📎 ${
                message.attachment_name
                || 'Bestand openen'
            }`;

        item.append(link);
    }

    function append(message) {
        if (seen.has(message.id)) {
            return;
        }

        seen.add(message.id);

        last = Math.max(
            last,
            Number(message.id)
        );

        const nearEnd =
            log.scrollHeight
            - log.scrollTop
            - log.clientHeight
            < 100;

        const item =
            document.createElement('div');

        item.className =
            `lc-msg${
                message.sender === 'visitor'
                    ? ' lc-msg--visitor'
                    : ''
            }`;

        item.dataset.messageId =
            String(message.id);

        const head =
            document.createElement('div');

        head.className =
            'lc-msg-head';

        const label =
            document.createElement('small');

        label.textContent =
            message.sender_name
            || (
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

        if (
            message.sender === 'visitor'
        ) {
            const remove =
                document.createElement('button');

            remove.type =
                'button';

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
                            `${live.dataset.store}/${message.id}`,
                            'DELETE'
                        );

                        item.remove();

                        seen.delete(
                            message.id
                        );
                    } catch (e) {
                        remove.disabled =
                            false;

                        failure(
                            e.message
                        );
                    }
                }
            );

            item.append(remove);
        }

        log.append(item);

        if (
            nearEnd
            || message.sender === 'visitor'
        ) {
            log.scrollTop =
                log.scrollHeight;
        }
    }

    async function poll() {
        if (
            fetching
            || stopped
            || document.hidden
            || chat.dataset.mode !== 'human'
            || chat.querySelector(
                '.guest-chat__panel'
            )?.hidden
        ) {
            return;
        }

        fetching = true;

        try {
            const data = await api(
                `${live.dataset.show}?after=${last}`
            );

            if (
                identity
                && identity !== data.identity
            ) {
                log.replaceChildren();

                seen.clear();

                last = 0;
                loaded = false;

                identity =
                    data.identity;

                send.disabled = true;

                status.textContent =
                    'Je huidige gesprek wordt geladen…';

                return;
            }

            identity =
                data.identity;

            const first =
                !loaded;

            data.messages.forEach(
                append
            );

            closed =
                data.conversation?.status
                === 'closed';

            reopen.hidden =
                !closed;

            input.disabled =
                closed;

            loaded = true;

            send.disabled =
                closed || sending;

            fileInput.disabled =
                closed || sending;

            voiceButton.disabled =
                closed || sending;

            status.textContent =
                closed
                    ? 'Dit gesprek is afgesloten. Je kunt het opnieuw openen.'
                    : data.online
                        ? 'Er is een medewerker beschikbaar. Stuur gerust je bericht.'
                        : 'Er is nu geen medewerker beschikbaar. Laat een bericht achter en kom later terug in deze chat.';

            if (!data.conversation) {
                status.textContent +=
                    ' Je gesprek start zodra je een bericht stuurt.';
            }

            if (first) {
                log.scrollTop =
                    log.scrollHeight;
            }

            if (!sending) {
                failure('');
            }
        } catch (e) {
            failure(
                e.name === 'TimeoutError'
                    ? 'De verbinding duurt te lang. We proberen het opnieuw.'
                    : e.message
            );
        } finally {
            fetching = false;
        }
    }

    async function sendPayload({
        body = null,
        type = 'text',
        file = null,
    }) {
        if (
            sending
            || stopped
            || closed
        ) {
            return;
        }

        sending = true;

        send.disabled = true;
        input.disabled = true;
        fileInput.disabled = true;
        voiceButton.disabled = true;

        failure('');

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
                    || `${type}-${Date.now()}`
                );
            } else {
                payload = {
                    body,
                    type,
                    client_id: uuid(),
                };
            }

            await api(
                live.dataset.store,
                'POST',
                payload
            );

            input.value = '';

            await poll();
        } catch (e) {
            failure(
                e.name === 'TimeoutError'
                    ? 'Geen bevestiging ontvangen. Probeer opnieuw.'
                    : e.message
            );

            throw e;
        } finally {
            sending = false;

            send.disabled =
                closed || stopped;

            input.disabled =
                closed || stopped;

            fileInput.disabled =
                closed || stopped;

            voiceButton.disabled =
                closed || stopped;
        }
    }

    async function activateHuman(
        initialBody = ''
    ) {
        chat.dataset.mode =
            'human';

        aiBody.hidden = true;
        aiBottom.hidden = true;
        reset.hidden = true;

        const resetConfirm =
            chat.querySelector(
                '.gc-reset-confirm'
            );

        if (resetConfirm) {
            resetConfirm.hidden =
                true;
        }

        live.hidden = false;

        header.textContent =
            'Mashal support';

        subtitle.textContent =
            'Live contact met een medewerker';

        await poll();

        if (initialBody) {
            await sendPayload({
                body: initialBody,
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

    function stopMediaStream() {
        if (mediaStream) {
            mediaStream
                .getTracks()
                .forEach(
                    track => {
                        track.stop();
                    }
                );
        }

        mediaStream = null;
    }

    function resetRecorder() {
        stopMediaStream();

        mediaRecorder = null;
        audioChunks = [];
        selectedRecorderMime = '';

        voiceButton.setAttribute(
            'aria-pressed',
            'false'
        );

        voiceButton.textContent =
            '🎤';
    }

    function chooseRecorderMime() {
        const candidates = [
            'audio/webm;codecs=opus',
            'audio/webm',
            'audio/ogg;codecs=opus',
            'audio/ogg',
            'audio/mp4',
        ];

        for (
            const candidate
            of candidates
        ) {
            if (
                MediaRecorder.isTypeSupported(
                    candidate
                )
            ) {
                return candidate;
            }
        }

        return '';
    }

    function recorderExtension(
        mime
    ) {
        const normalized =
            String(mime || '')
                .toLowerCase();

        if (
            normalized.includes(
                'ogg'
            )
        ) {
            return 'ogg';
        }

        if (
            normalized.includes(
                'mp4'
            )
            || normalized.includes(
                'm4a'
            )
        ) {
            return 'm4a';
        }

        if (
            normalized.includes(
                'wav'
            )
        ) {
            return 'wav';
        }

        if (
            normalized.includes(
                'mpeg'
            )
            || normalized.includes(
                'mp3'
            )
        ) {
            return 'mp3';
        }

        return 'webm';
    }

    function normalizedVoiceMime(
        mime
    ) {
        const value =
            String(mime || '')
                .trim()
                .toLowerCase();

        if (!value) {
            return 'audio/webm';
        }

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

        return value.split(';')[0];
    }

    live
        .querySelector('form')
        .addEventListener(
            'submit',
            async event => {
                event.preventDefault();

                const body =
                    input.value.trim();

                if (
                    !body
                    || !loaded
                    || closed
                    || stopped
                ) {
                    return;
                }

                await sendPayload({
                    body,
                    type: 'text',
                });
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

            try {
                await sendPayload({
                    type: 'file',
                    file,
                });
            } catch {
                // fout wordt al in de UI getoond
            }
        }
    );

    voiceButton.addEventListener(
        'click',
        async () => {
            if (
                mediaRecorder?.state
                === 'recording'
            ) {
                voiceButton.disabled =
                    true;

                mediaRecorder.stop();

                return;
            }

            if (
                !navigator.mediaDevices
                    ?.getUserMedia
                || !window.MediaRecorder
            ) {
                failure(
                    'Spraakopname wordt niet ondersteund in deze browser.'
                );

                return;
            }

            if (
                closed
                || stopped
                || sending
            ) {
                return;
            }

            failure('');

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

                selectedRecorderMime =
                    chooseRecorderMime();

                const options =
                    selectedRecorderMime
                        ? {
                            mimeType:
                                selectedRecorderMime,
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
                            event.data
                            && event.data.size > 0
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
                            'MediaRecorder error:',
                            event
                        );

                        failure(
                            'Er ging iets mis tijdens de spraakopname.'
                        );

                        resetRecorder();
                    }
                );

                mediaRecorder.addEventListener(
                    'stop',
                    async () => {
                        const recorderMime =
                            mediaRecorder
                                ?.mimeType
                            || selectedRecorderMime
                            || 'audio/webm';

                        const mime =
                            normalizedVoiceMime(
                                recorderMime
                            );

                        const totalBytes =
                            audioChunks.reduce(
                                (
                                    total,
                                    chunk
                                ) =>
                                    total
                                    + chunk.size,
                                0
                            );

                        if (
                            totalBytes <= 0
                        ) {
                            resetRecorder();

                            failure(
                                'De opname bevat geen geluid. Probeer opnieuw.'
                            );

                            return;
                        }

                        const blob =
                            new Blob(
                                audioChunks,
                                {
                                    type: mime,
                                }
                            );

                        if (
                            blob.size <= 0
                        ) {
                            resetRecorder();

                            failure(
                                'Het spraakbericht is leeg. Probeer opnieuw.'
                            );

                            return;
                        }

                        const extension =
                            recorderExtension(
                                mime
                            );

                        const file =
                            new File(
                                [blob],
                                `spraakbericht-${Date.now()}.${extension}`,
                                {
                                    type: mime,
                                }
                            );

                        resetRecorder();

                        failure(
                            'Spraakbericht wordt verstuurd…'
                        );

                        try {
                            await sendPayload({
                                type: 'voice',
                                file,
                            });

                            failure('');
                        } catch (e) {
                            console.error(
                                'Voice upload mislukt:',
                                {
                                    name:
                                        file.name,

                                    type:
                                        file.type,

                                    size:
                                        file.size,

                                    error:
                                        e,
                                }
                            );
                        }
                    }
                );

                mediaRecorder.start(
                    250
                );

                voiceButton.setAttribute(
                    'aria-pressed',
                    'true'
                );

                voiceButton.textContent =
                    '■';

                failure(
                    'Opname gestart. Klik opnieuw om te stoppen en te versturen.'
                );
            } catch (e) {
                console.error(
                    'Microfoonfout:',
                    e
                );

                resetRecorder();

                if (
                    e?.name
                    === 'NotAllowedError'
                ) {
                    failure(
                        'Microfoontoegang is geweigerd. Sta microfoontoegang toe in je browser.'
                    );

                    return;
                }

                if (
                    e?.name
                    === 'NotFoundError'
                ) {
                    failure(
                        'Er is geen microfoon gevonden.'
                    );

                    return;
                }

                failure(
                    'Microfoontoegang is niet beschikbaar.'
                );
            }
        }
    );

    reopen.addEventListener(
        'click',
        async () => {
            reopen.disabled = true;

            try {
                await api(
                    live.dataset.reopen,
                    'POST',
                    {}
                );

                await poll();
            } catch (e) {
                failure(
                    e.message
                );
            } finally {
                reopen.disabled =
                    false;
            }
        }
    );

    chat.addEventListener(
        'live-chat:handoff',
        event => {
            const body =
                String(
                    event.detail?.body
                    || ''
                ).trim();

            activateHuman(body);
        }
    );

    chat
        .querySelector(
            '.guest-chat__toggle'
        )
        ?.addEventListener(
            'click',
            () => {
                if (
                    chat.dataset.mode
                    === 'human'
                ) {
                    poll();
                }
            }
        );

    document.addEventListener(
        'visibilitychange',
        () => {
            if (
                !document.hidden
                && chat.dataset.mode
                === 'human'
            ) {
                poll();
            }
        }
    );

    window.addEventListener(
        'beforeunload',
        () => {
            if (
                mediaRecorder
                && mediaRecorder.state
                    === 'recording'
            ) {
                mediaRecorder.stop();
            }

            stopMediaStream();
        }
    );

    window.setInterval(
        poll,
        3000
    );
})();