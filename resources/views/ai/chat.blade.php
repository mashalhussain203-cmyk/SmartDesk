@extends('layouts.site-layout')

@section('title', 'Mashal AI | Chat')

@section(
    'meta_description',
    'Chat met Mashal AI via een veilige server-side Modal AI-integratie.'
)

@push('styles')
<style>
    .mashal-ai-page {
        min-height: calc(100vh - 78px);
        padding: 34px 0 70px;
        color: #f5f6f7;
        background:
            radial-gradient(circle at 14% 8%, rgba(229, 182, 111, .10), transparent 28rem),
            radial-gradient(circle at 88% 22%, rgba(91, 91, 255, .08), transparent 32rem),
            linear-gradient(180deg, #07080b, #090b0f);
    }

    .mashal-ai-shell {
        width: min(100% - 28px, 1180px);
        margin-inline: auto;
    }

    .mashal-ai-hero {
        margin-bottom: 18px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 22px;
        background: rgba(255,255,255,.025);
        backdrop-filter: blur(18px);
    }

    .mashal-ai-brand {
        display: flex;
        align-items: center;
        gap: 13px;
        min-width: 0;
    }

    .mashal-ai-mark {
        width: 46px;
        height: 46px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        border: 1px solid rgba(229,182,111,.20);
        border-radius: 14px;
        color: #f0ca89;
        background: rgba(229,182,111,.07);
        font-weight: 950;
        box-shadow: inset 0 0 30px rgba(229,182,111,.025);
    }

    .mashal-ai-brand h1 {
        margin: 0;
        font-size: clamp(18px, 3vw, 27px);
        line-height: 1.1;
    }

    .mashal-ai-brand p {
        margin: 5px 0 0;
        color: #737a84;
        font-size: 12px;
    }

    .mashal-ai-status {
        min-height: 36px;
        padding: 0 12px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        flex: 0 0 auto;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 999px;
        color: #8d949e;
        background: rgba(255,255,255,.02);
        font-size: 11px;
        font-weight: 850;
    }

    .mashal-ai-status::before {
        content: "";
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #6fd58c;
        box-shadow: 0 0 12px rgba(111,213,140,.44);
    }

    .mashal-ai-status.offline::before {
        background: #d66c6c;
        box-shadow: 0 0 12px rgba(214,108,108,.38);
    }

    .mashal-ai-card {
        height: min(760px, calc(100svh - 180px));
        min-height: 560px;
        display: grid;
        grid-template-rows: auto minmax(0, 1fr) auto;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 26px;
        background: #0b0d12;
        box-shadow: 0 35px 100px rgba(0,0,0,.42);
    }

    .mashal-ai-toolbar {
        padding: 12px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        border-bottom: 1px solid rgba(255,255,255,.06);
        background: rgba(255,255,255,.012);
    }

    .mashal-ai-toolbar-copy strong {
        display: block;
        color: #d6d9dd;
        font-size: 12px;
    }

    .mashal-ai-toolbar-copy span {
        display: block;
        margin-top: 3px;
        color: #606771;
        font-size: 10px;
    }

    .mashal-ai-clear {
        min-height: 36px;
        padding: 0 12px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 10px;
        color: #9ca2aa;
        background: rgba(255,255,255,.02);
        font-size: 11px;
        font-weight: 850;
        cursor: pointer;
    }

    .mashal-ai-messages {
        padding: 22px;
        overflow-y: auto;
        overscroll-behavior: contain;
        scroll-behavior: smooth;
    }

    .mashal-ai-welcome {
        max-width: 690px;
        margin: 58px auto;
        text-align: center;
    }

    .mashal-ai-welcome-icon {
        width: 62px;
        height: 62px;
        margin: 0 auto 16px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(229,182,111,.14);
        border-radius: 19px;
        color: #eac481;
        background: rgba(229,182,111,.045);
        font-size: 21px;
    }

    .mashal-ai-welcome h2 {
        margin: 0;
        color: #e1e4e7;
        font-size: clamp(22px, 4vw, 34px);
    }

    .mashal-ai-welcome p {
        margin: 12px auto 0;
        max-width: 560px;
        color: #707781;
        font-size: 13px;
        line-height: 1.7;
    }

    .mashal-ai-suggestions {
        margin-top: 20px;
        display: flex;
        justify-content: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .mashal-ai-suggestion {
        min-height: 38px;
        padding: 0 12px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 999px;
        color: #8d949e;
        background: rgba(255,255,255,.018);
        font-size: 10px;
        font-weight: 800;
        cursor: pointer;
    }

    .mashal-ai-message {
        max-width: min(78%, 760px);
        margin-bottom: 16px;
    }

    .mashal-ai-message.user {
        margin-left: auto;
    }

    .mashal-ai-message.assistant {
        margin-right: auto;
    }

    .mashal-ai-message-meta {
        margin-bottom: 5px;
        color: #59606a;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .mashal-ai-message.user .mashal-ai-message-meta {
        text-align: right;
    }

    .mashal-ai-bubble {
        padding: 13px 15px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 16px;
        color: #cdd1d5;
        background: rgba(255,255,255,.025);
        font-size: 13px;
        line-height: 1.7;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .mashal-ai-message.user .mashal-ai-bubble {
        border-color: rgba(229,182,111,.14);
        color: #18110a;
        background: linear-gradient(135deg, #efd08e, #d49b50);
    }

    .mashal-ai-message.loading .mashal-ai-bubble {
        color: #777e87;
    }

    .mashal-ai-composer {
        padding: 13px;
        border-top: 1px solid rgba(255,255,255,.06);
        background: rgba(7,9,12,.92);
        backdrop-filter: blur(18px);
    }

    .mashal-ai-input-wrap {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: end;
        gap: 9px;
        padding: 8px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 16px;
        background: #0d1015;
    }

    .mashal-ai-input {
        width: 100%;
        max-height: 180px;
        min-height: 46px;
        padding: 12px 11px;
        resize: none;
        overflow-y: auto;
        border: 0;
        outline: 0;
        color: #e0e3e6;
        background: transparent;
        font: inherit;
        font-size: 13px;
        line-height: 1.55;
    }

    .mashal-ai-input::placeholder {
        color: #565d66;
    }

    .mashal-ai-send {
        min-width: 100px;
        min-height: 46px;
        padding: 0 15px;
        border: 1px solid rgba(229,182,111,.18);
        border-radius: 12px;
        color: #171009;
        background: linear-gradient(135deg, #efd08e, #d49b50);
        font-size: 11px;
        font-weight: 950;
        cursor: pointer;
    }

    .mashal-ai-send:disabled {
        opacity: .52;
        cursor: not-allowed;
    }

    .mashal-ai-note {
        margin: 8px 4px 0;
        color: #525963;
        font-size: 9px;
        line-height: 1.5;
    }

    .mashal-ai-error {
        margin: 0 0 12px;
        padding: 11px 13px;
        display: none;
        border: 1px solid rgba(244,125,125,.13);
        border-radius: 12px;
        color: #d89999;
        background: rgba(244,125,125,.035);
        font-size: 11px;
    }

    .mashal-ai-error.show {
        display: block;
    }

    @media (max-width: 760px) {
        .mashal-ai-page {
            min-height: calc(100svh - 64px);
            padding: 8px 0 calc(8px + env(safe-area-inset-bottom));
        }

        .mashal-ai-shell {
            width: min(100% - 8px, 1180px);
        }

        .mashal-ai-hero {
            margin-bottom: 7px;
            padding: 11px;
            border-radius: 15px;
        }

        .mashal-ai-mark {
            width: 40px;
            height: 40px;
            border-radius: 12px;
        }

        .mashal-ai-brand p {
            font-size: 11px;
        }

        .mashal-ai-status {
            display: none;
        }

        .mashal-ai-card {
            height: calc(100svh - 132px);
            min-height: 0;
            border-radius: 17px;
        }

        .mashal-ai-toolbar {
            padding: 9px 10px;
        }

        .mashal-ai-messages {
            padding: 14px 10px;
        }

        .mashal-ai-welcome {
            margin: 36px auto;
        }

        .mashal-ai-welcome p {
            font-size: 13px;
        }

        .mashal-ai-message {
            max-width: 90%;
        }

        .mashal-ai-bubble {
            font-size: 14px;
        }

        .mashal-ai-composer {
            padding: 8px;
        }

        .mashal-ai-input-wrap {
            gap: 5px;
            padding: 5px;
            border-radius: 13px;
        }

        .mashal-ai-input {
            min-height: 48px;
            font-size: 16px;
        }

        .mashal-ai-send {
            min-width: 76px;
            min-height: 48px;
            padding-inline: 12px;
            font-size: 13px;
        }

        .mashal-ai-note {
            font-size: 10px;
        }
    }
</style>
@endpush

@section('content')
<div class="mashal-ai-page">
    <div class="mashal-ai-shell">
        <header class="mashal-ai-hero">
            <div class="mashal-ai-brand">
                <div class="mashal-ai-mark" aria-hidden="true">AI</div>

                <div>
                    <h1>Mashal AI</h1>
                    <p>
                        Een aparte AI-assistent, veilig verbonden met jouw Modal endpoint.
                    </p>
                </div>
            </div>

            <div
                class="mashal-ai-status {{ $chatConfigured ? '' : 'offline' }}"
                id="chat-config-status"
            >
                {{ $chatConfigured ? 'Modal verbonden' : 'Configuratie ontbreekt' }}
            </div>
        </header>

        <section class="mashal-ai-card" aria-label="Mashal AI chat">
            <div class="mashal-ai-toolbar">
                <div class="mashal-ai-toolbar-copy">
                    <strong>Nieuwe conversatie</strong>
                    <span>
                        {{ $modelName !== '' ? $modelName : 'Modal AI endpoint' }}
                    </span>
                </div>

                <button
                    type="button"
                    class="mashal-ai-clear"
                    id="mashal-ai-clear"
                >
                    Chat wissen
                </button>
            </div>

            <div
                class="mashal-ai-messages"
                id="mashal-ai-messages"
                aria-live="polite"
            >
                <div
                    class="mashal-ai-welcome"
                    id="mashal-ai-welcome"
                >
                    <div class="mashal-ai-welcome-icon" aria-hidden="true">✦</div>

                    <h2>Waar kan ik je mee helpen?</h2>

                    <p>
                        Stel een vraag, laat tekst uitleggen, brainstorm over ideeën
                        of gebruik Mashal AI als assistent tijdens je werk.
                    </p>

                    <div class="mashal-ai-suggestions">
                        <button
                            type="button"
                            class="mashal-ai-suggestion"
                            data-suggestion="Leg dit onderwerp eenvoudig aan mij uit."
                        >
                            Iets uitleggen
                        </button>

                        <button
                            type="button"
                            class="mashal-ai-suggestion"
                            data-suggestion="Help mij ideeën te bedenken voor mijn project."
                        >
                            Ideeën bedenken
                        </button>

                        <button
                            type="button"
                            class="mashal-ai-suggestion"
                            data-suggestion="Help mij deze tekst duidelijker en professioneler te schrijven."
                        >
                            Tekst verbeteren
                        </button>
                    </div>
                </div>
            </div>

            <div class="mashal-ai-composer">
                <div
                    class="mashal-ai-error"
                    id="mashal-ai-error"
                    role="alert"
                ></div>

                <form id="mashal-ai-form">
                    <div class="mashal-ai-input-wrap">
                        <textarea
                            id="mashal-ai-input"
                            class="mashal-ai-input"
                            rows="1"
                            maxlength="12000"
                            placeholder="Typ je bericht…"
                            autocomplete="off"
                            required
                        ></textarea>

                        <button
                            id="mashal-ai-send"
                            class="mashal-ai-send"
                            type="submit"
                            @disabled(! $chatConfigured)
                        >
                            Verstuur
                        </button>
                    </div>
                </form>

                <p class="mashal-ai-note">
                    Je Modal API-key blijft server-side in Railway en wordt nooit naar de browser gestuurd.
                </p>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const endpoint =
        @json(route('ai.chat.message'));

    const csrfToken =
        @json(csrf_token());

    const configured =
        @json((bool) $chatConfigured);

    const storageKey =
        'mashal-ai-history-v1';

    const form =
        document.getElementById('mashal-ai-form');

    const input =
        document.getElementById('mashal-ai-input');

    const sendButton =
        document.getElementById('mashal-ai-send');

    const messagesContainer =
        document.getElementById('mashal-ai-messages');

    const welcome =
        document.getElementById('mashal-ai-welcome');

    const errorBox =
        document.getElementById('mashal-ai-error');

    const clearButton =
        document.getElementById('mashal-ai-clear');

    let history =
        loadHistory();

    function loadHistory() {
        try {
            const raw =
                localStorage.getItem(
                    storageKey
                );

            if (!raw) {
                return [];
            }

            const parsed =
                JSON.parse(raw);

            if (!Array.isArray(parsed)) {
                return [];
            }

            return parsed
                .filter(function (item) {
                    return (
                        item &&
                        (
                            item.role === 'user' ||
                            item.role === 'assistant'
                        ) &&
                        typeof item.content === 'string'
                    );
                })
                .slice(-20);
        } catch (error) {
            return [];
        }
    }

    function saveHistory() {
        try {
            localStorage.setItem(
                storageKey,
                JSON.stringify(
                    history.slice(-20)
                )
            );
        } catch (error) {
            // Local storage is optioneel.
        }
    }

    function setError(message = '') {
        if (!errorBox) {
            return;
        }

        errorBox.textContent =
            message;

        errorBox.classList.toggle(
            'show',
            Boolean(message)
        );
    }

    function scrollToBottom() {
        if (!messagesContainer) {
            return;
        }

        messagesContainer.scrollTop =
            messagesContainer.scrollHeight;
    }

    function renderMessage(
        role,
        content,
        loading = false
    ) {
        if (!messagesContainer) {
            return null;
        }

        welcome?.remove();

        const wrapper =
            document.createElement('article');

        wrapper.className =
            'mashal-ai-message ' +
            role +
            (loading ? ' loading' : '');

        const meta =
            document.createElement('div');

        meta.className =
            'mashal-ai-message-meta';

        meta.textContent =
            role === 'user'
                ? 'Jij'
                : 'Mashal AI';

        const bubble =
            document.createElement('div');

        bubble.className =
            'mashal-ai-bubble';

        /*
         * textContent voorkomt dat model-output als HTML wordt uitgevoerd.
         */
        bubble.textContent =
            content;

        wrapper.append(
            meta,
            bubble
        );

        messagesContainer.appendChild(
            wrapper
        );

        scrollToBottom();

        return wrapper;
    }

    function renderHistory() {
        if (!history.length) {
            return;
        }

        history.forEach(
            function (message) {
                renderMessage(
                    message.role,
                    message.content
                );
            }
        );
    }

    function autoResize() {
        if (!input) {
            return;
        }

        input.style.height =
            'auto';

        input.style.height =
            Math.min(
                input.scrollHeight,
                180
            ) + 'px';
    }

    async function sendMessage(message) {
        if (
            !configured ||
            !message
        ) {
            return;
        }

        setError('');

        const historyForRequest =
            history.slice(-20);

        history.push({
            role: 'user',
            content: message,
        });

        renderMessage(
            'user',
            message
        );

        const loadingMessage =
            renderMessage(
                'assistant',
                'Mashal AI denkt…',
                true
            );

        if (sendButton) {
            sendButton.disabled =
                true;

            sendButton.textContent =
                'Bezig…';
        }

        try {
            const response =
                await fetch(
                    endpoint,
                    {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            message: message,
                            history: historyForRequest,
                        }),
                    }
                );

            let data = null;

            try {
                data =
                    await response.json();
            } catch (error) {
                data = null;
            }

            if (
                !response.ok ||
                !data ||
                data.ok !== true ||
                typeof data.message !== 'string'
            ) {
                throw new Error(
                    data?.message ||
                    'Er kon geen AI-antwoord worden opgehaald.'
                );
            }

            loadingMessage?.remove();

            history.push({
                role: 'assistant',
                content: data.message,
            });

            history =
                history.slice(-20);

            saveHistory();

            renderMessage(
                'assistant',
                data.message
            );
        } catch (error) {
            loadingMessage?.remove();

            /*
             * Het userbericht blijft zichtbaar, maar wordt uit de opgeslagen
             * context verwijderd zodat een mislukte request niet dubbel komt.
             */
            history.pop();

            setError(
                error instanceof Error
                    ? error.message
                    : 'Er ging iets mis.'
            );
        } finally {
            if (sendButton) {
                sendButton.disabled =
                    !configured;

                sendButton.textContent =
                    'Verstuur';
            }

            input?.focus();
        }
    }

    form?.addEventListener(
        'submit',
        async function (event) {
            event.preventDefault();

            const message =
                input?.value.trim() ||
                '';

            if (!message) {
                return;
            }

            if (input) {
                input.value =
                    '';

                autoResize();
            }

            await sendMessage(
                message
            );
        }
    );

    input?.addEventListener(
        'input',
        autoResize
    );

    input?.addEventListener(
        'keydown',
        function (event) {
            if (
                event.key === 'Enter' &&
                !event.shiftKey &&
                !event.isComposing
            ) {
                event.preventDefault();

                form?.requestSubmit();
            }
        }
    );

    document
        .querySelectorAll(
            '[data-suggestion]'
        )
        .forEach(function (button) {
            button.addEventListener(
                'click',
                function () {
                    if (!input) {
                        return;
                    }

                    input.value =
                        button.dataset.suggestion ||
                        '';

                    autoResize();
                    input.focus();
                }
            );
        });

    clearButton?.addEventListener(
        'click',
        function () {
            history = [];

            try {
                localStorage.removeItem(
                    storageKey
                );
            } catch (error) {
                // Geen actie nodig.
            }

            window.location.reload();
        }
    );

    renderHistory();
    autoResize();

    if (!configured) {
        setError(
            'Modal AI is nog niet volledig geconfigureerd op de server.'
        );
    }
});
</script>
@endpush
