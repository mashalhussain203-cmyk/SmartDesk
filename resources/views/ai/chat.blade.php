@extends('layouts.site-layout')

@section('title', 'Mashal AI | Live Voice')

@section(
    'meta_description',
    'Praat live met Mashal AI in Nederlands, English of Urdu, of gebruik de gewone tekstchat met bestanden.'
)

@push('styles')
<style>
    :root {
        --ai-bg: #050607;
        --ai-panel: #0d0f12;
        --ai-panel-2: #111419;
        --ai-line: rgba(255,255,255,.08);
        --ai-muted: #727983;
        --ai-text: #f2f4f6;
        --ai-accent: #e9bd76;
        --ai-danger: #ef8787;
    }

    .mashal-ai-page {
        min-height: calc(100vh - 78px);
        padding: 22px 0 54px;
        color: var(--ai-text);
        background:
            radial-gradient(circle at 15% 0%, rgba(229,182,111,.08), transparent 30rem),
            radial-gradient(circle at 85% 18%, rgba(90,100,255,.08), transparent 30rem),
            var(--ai-bg);
    }

    .mashal-ai-shell {
        width: min(100% - 24px, 1120px);
        margin-inline: auto;
    }

    .mashal-ai-card {
        height: min(800px, calc(100dvh - 128px));
        min-height: 590px;
        display: grid;
        grid-template-rows: auto minmax(0,1fr) auto auto;
        overflow: hidden;
        border: 1px solid var(--ai-line);
        border-radius: 24px;
        background: rgba(11,13,17,.96);
        box-shadow: 0 36px 100px rgba(0,0,0,.42);
    }

    .mashal-ai-header {
        padding: 13px 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border-bottom: 1px solid var(--ai-line);
    }

    .mashal-ai-brand {
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 11px;
    }

    .mashal-ai-mark {
        width: 40px;
        height: 40px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        border: 1px solid rgba(233,189,118,.18);
        border-radius: 12px;
        color: #f1c982;
        background: rgba(233,189,118,.05);
        font-size: 12px;
        font-weight: 950;
    }

    .mashal-ai-brand strong {
        display: block;
        font-size: 14px;
    }

    .mashal-ai-brand span {
        display: block;
        margin-top: 2px;
        color: var(--ai-muted);
        font-size: 10px;
    }

    .mashal-ai-header-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ai-action,
    .ai-send,
    .ai-file-button {
        min-height: 42px;
        padding: 0 13px;
        border: 1px solid var(--ai-line);
        border-radius: 11px;
        color: #c8cdd3;
        background: rgba(255,255,255,.025);
        font: inherit;
        font-size: 11px;
        font-weight: 900;
        cursor: pointer;
        touch-action: manipulation;
    }

    .ai-action.primary {
        border-color: rgba(233,189,118,.22);
        color: #191107;
        background: linear-gradient(135deg, #f0cf8a, #d89e50);
    }

    .ai-action:disabled,
    .ai-send:disabled {
        opacity: .5;
        cursor: not-allowed;
    }

    .mashal-ai-messages {
        min-height: 0;
        padding: 24px;
        overflow-y: auto;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
    }

    .ai-welcome {
        max-width: 650px;
        margin: 70px auto 30px;
        text-align: center;
    }

    .ai-welcome-orb {
        width: 68px;
        height: 68px;
        margin: 0 auto 18px;
        border-radius: 50%;
        background:
            radial-gradient(circle at 35% 30%, #fff 0 7%, #b9c7ff 20%, #6d7ff2 46%, #303669 70%, #111525 100%);
        box-shadow:
            0 0 50px rgba(112,129,255,.22),
            inset -16px -14px 30px rgba(0,0,0,.28);
    }

    .ai-welcome h1 {
        margin: 0;
        font-size: clamp(24px, 4vw, 38px);
    }

    .ai-welcome p {
        margin: 12px auto 0;
        max-width: 530px;
        color: var(--ai-muted);
        font-size: 13px;
        line-height: 1.7;
    }

    .ai-message {
        max-width: min(78%, 760px);
        margin-bottom: 17px;
    }

    .ai-message.user {
        margin-left: auto;
    }

    .ai-message.assistant {
        margin-right: auto;
    }

    .ai-message-meta {
        margin-bottom: 5px;
        color: #5d646d;
        font-size: 9px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .ai-message.user .ai-message-meta {
        text-align: right;
    }

    .ai-message-bubble {
        padding: 13px 15px;
        border: 1px solid var(--ai-line);
        border-radius: 16px;
        color: #d4d8dc;
        background: rgba(255,255,255,.025);
        font-size: 13px;
        line-height: 1.7;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .ai-message.user .ai-message-bubble {
        border-color: rgba(233,189,118,.16);
        color: #1d140a;
        background: linear-gradient(135deg, #efd08d, #d6a056);
    }

    .ai-attachments {
        min-height: 0;
        padding: 0 13px 9px;
    }

    .ai-file-row {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .ai-file-input {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        clip: rect(0,0,0,0);
        white-space: nowrap;
    }

    .ai-file-help {
        color: #59616b;
        font-size: 9px;
    }

    .ai-file-list {
        margin-top: 7px;
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .ai-file-chip {
        max-width: 260px;
        padding: 6px 8px 6px 10px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        border: 1px solid var(--ai-line);
        border-radius: 9px;
        color: #9ba2aa;
        background: rgba(255,255,255,.02);
        font-size: 10px;
    }

    .ai-file-chip span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ai-file-chip button {
        border: 0;
        color: #a0a6ae;
        background: transparent;
        cursor: pointer;
    }

    .mashal-ai-composer {
        padding: 12px;
        border-top: 1px solid var(--ai-line);
        background: rgba(8,10,13,.96);
    }

    .ai-error {
        margin-bottom: 9px;
        padding: 10px 12px;
        display: none;
        border: 1px solid rgba(239,135,135,.16);
        border-radius: 11px;
        color: #dfa1a1;
        background: rgba(239,135,135,.04);
        font-size: 11px;
    }

    .ai-error.show {
        display: block;
    }

    .ai-input-wrap {
        display: grid;
        grid-template-columns: minmax(0,1fr) auto;
        align-items: end;
        gap: 8px;
        padding: 7px;
        border: 1px solid var(--ai-line);
        border-radius: 15px;
        background: var(--ai-panel);
    }

    .ai-input {
        width: 100%;
        min-height: 46px;
        max-height: 180px;
        padding: 12px 10px;
        resize: none;
        overflow-y: auto;
        border: 0;
        outline: 0;
        color: var(--ai-text);
        background: transparent;
        font: inherit;
        font-size: 14px;
        line-height: 1.5;
    }

    .ai-send {
        min-width: 96px;
        border-color: rgba(233,189,118,.2);
        color: #171009;
        background: linear-gradient(135deg, #efd08e, #d49b50);
    }

    /* --------------------------------------------------------------------- */
    /* Live Voice                                                           */
    /* --------------------------------------------------------------------- */

    .live-voice {
        position: fixed;
        z-index: 99999;
        inset: 0;
        display: grid;
        grid-template-rows: auto minmax(0,1fr) auto;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        color: #f5f6f7;
        background:
            radial-gradient(circle at 50% 58%, rgba(73,83,155,.09), transparent 30rem),
            #020303;
        transition:
            opacity .2s ease,
            visibility .2s ease;
    }

    .live-voice.active {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    .live-voice-top {
        padding:
            max(18px, env(safe-area-inset-top))
            max(20px, env(safe-area-inset-right))
            12px
            max(20px, env(safe-area-inset-left));
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .live-voice-title {
        font-size: 15px;
        font-weight: 900;
    }

    .live-voice-title span {
        color: #848b95;
        font-weight: 500;
    }

    .live-voice-center {
        min-height: 0;
        display: grid;
        place-items: center;
        padding: 20px;
    }

    .live-voice-stage {
        display: grid;
        place-items: center;
        text-align: center;
    }

    .live-orb-wrap {
        width: min(46vw, 240px);
        aspect-ratio: 1;
        display: grid;
        place-items: center;
    }

    .live-orb {
        width: 44%;
        aspect-ratio: 1;
        border-radius: 50%;
        background:
            radial-gradient(circle at 33% 28%, #ffffff 0 7%, #d6dfff 14%, #98a9ff 31%, #6478ea 47%, #3d477f 68%, #151929 100%);
        box-shadow:
            0 0 60px rgba(108,127,255,.24),
            0 0 130px rgba(75,89,180,.10),
            inset -18px -14px 34px rgba(0,0,0,.28);
        transform: scale(1);
        transition:
            transform .16s ease,
            filter .25s ease,
            box-shadow .25s ease;
        will-change: transform;
    }

    .live-voice[data-state="listening"] .live-orb {
        animation: voiceBreath 2.5s ease-in-out infinite;
    }

    .live-voice[data-state="recording"] .live-orb {
        filter: saturate(1.12) brightness(1.12);
        box-shadow:
            0 0 76px rgba(108,127,255,.38),
            0 0 150px rgba(75,89,180,.15),
            inset -18px -14px 34px rgba(0,0,0,.25);
    }

    .live-voice[data-state="thinking"] .live-orb {
        animation: voiceThink 1.1s ease-in-out infinite;
    }

    .live-voice[data-state="speaking"] .live-orb {
        animation: voiceSpeak .75s ease-in-out infinite alternate;
    }

    .live-voice[data-state="error"] .live-orb {
        filter: saturate(.75) hue-rotate(110deg);
    }

    @keyframes voiceBreath {
        0%, 100% { transform: scale(.96); }
        50% { transform: scale(1.04); }
    }

    @keyframes voiceThink {
        0%, 100% { transform: scale(.96) rotate(-2deg); }
        50% { transform: scale(1.055) rotate(2deg); }
    }

    @keyframes voiceSpeak {
        from { transform: scale(.94); }
        to { transform: scale(1.08); }
    }

    .live-voice-status {
        margin-top: 26px;
        min-height: 52px;
    }

    .live-voice-status strong {
        display: block;
        font-size: clamp(17px, 3vw, 24px);
    }

    .live-voice-status span {
        display: block;
        margin-top: 6px;
        color: #757d87;
        font-size: 11px;
    }

    .live-voice-bottom {
        padding:
            12px
            max(18px, env(safe-area-inset-right))
            calc(18px + env(safe-area-inset-bottom))
            max(18px, env(safe-area-inset-left));
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }

    .voice-control {
        min-width: 52px;
        height: 52px;
        padding: 0 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255,255,255,.09);
        border-radius: 999px;
        color: #e4e6e9;
        background: #1c1e21;
        font: inherit;
        font-size: 13px;
        font-weight: 850;
        cursor: pointer;
        touch-action: manipulation;
    }

    .voice-control.round {
        width: 52px;
        padding: 0;
        font-size: 18px;
    }

    .voice-control.danger {
        color: #111;
        background: #fff;
    }

    .voice-control.muted {
        color: #efaaaa;
        background: #352426;
    }

    @media (max-width: 760px) {
        .mashal-ai-page {
            min-height: calc(100dvh - 64px);
            padding: 7px 0 calc(7px + env(safe-area-inset-bottom));
        }

        .mashal-ai-shell {
            width: min(100% - 8px, 1120px);
        }

        .mashal-ai-card {
            height: calc(100dvh - 82px);
            min-height: 0;
            border-radius: 17px;
        }

        .mashal-ai-header {
            padding: 9px;
        }

        .mashal-ai-brand span {
            display: none;
        }

        .mashal-ai-header-actions .ai-action:not(.primary) {
            display: none;
        }

        .mashal-ai-messages {
            padding: 14px 10px;
        }

        .ai-message {
            max-width: 91%;
        }

        .ai-message-bubble {
            font-size: 14px;
        }

        .ai-file-help {
            display: none;
        }

        .mashal-ai-composer {
            padding:
                8px
                8px
                calc(8px + env(safe-area-inset-bottom));
        }

        .ai-input {
            min-height: 48px;
            max-height: 120px;
            font-size: 16px;
        }

        .ai-send {
            min-width: 74px;
            min-height: 48px;
            padding-inline: 10px;
        }

        .live-orb-wrap {
            width: min(76vw, 310px);
        }

        .live-orb {
            width: 48%;
        }
    }
</style>
@endpush

@section('content')
<div class="mashal-ai-page">
    <div class="mashal-ai-shell">
        <section class="mashal-ai-card" aria-label="Mashal AI">
            <header class="mashal-ai-header">
                <div class="mashal-ai-brand">
                    <div class="mashal-ai-mark" aria-hidden="true">AI</div>

                    <div>
                        <strong>Mashal AI</strong>
                        <span>
                            {{ $modelName ?: 'Groq AI' }}
                            ·
                            {{ $voiceModelName ?: 'Whisper' }}
                        </span>
                    </div>
                </div>

                <div class="mashal-ai-header-actions">
                    <button
                        type="button"
                        class="ai-action"
                        id="ai-clear"
                    >
                        Wissen
                    </button>

                    <button
                        type="button"
                        class="ai-action primary"
                        id="ai-live-start"
                        @disabled(! $chatConfigured || ! $voiceConfigured)
                    >
                        🎙 Live praten
                    </button>
                </div>
            </header>

            <div
                class="mashal-ai-messages"
                id="ai-messages"
                aria-live="polite"
            >
                <div class="ai-welcome" id="ai-welcome">
                    <div class="ai-welcome-orb" aria-hidden="true"></div>

                    <h1>Waar kan ik je mee helpen?</h1>

                    <p>
                        Typ een bericht of start Live Voice.
                        Je kunt Nederlands, English of اردو spreken.
                        Mashal AI luistert, antwoordt in dezelfde taal en luistert daarna automatisch opnieuw.
                    </p>
                </div>
            </div>

            <div class="ai-attachments">
                <div class="ai-file-row">
                    <label
                        class="ai-file-button"
                        for="ai-files"
                    >
                        ＋ Bestand
                    </label>

                    <input
                        id="ai-files"
                        class="ai-file-input"
                        type="file"
                        multiple
                    >

                    <span class="ai-file-help">
                        Max. {{ $maxChatFiles ?? 5 }} bestanden,
                        {{ $maxChatFileMb ?? 10 }} MB per bestand.
                    </span>
                </div>

                <div
                    id="ai-file-list"
                    class="ai-file-list"
                ></div>
            </div>

            <div class="mashal-ai-composer">
                <div
                    id="ai-error"
                    class="ai-error"
                    role="alert"
                ></div>

                <form id="ai-form">
                    <div class="ai-input-wrap">
                        <textarea
                            id="ai-input"
                            class="ai-input"
                            rows="1"
                            maxlength="12000"
                            placeholder="Typ je bericht… / Type your message… / اپنا پیغام لکھیں…"
                            autocomplete="off"
                        ></textarea>

                        <button
                            id="ai-send"
                            class="ai-send"
                            type="submit"
                            @disabled(! $chatConfigured)
                        >
                            Verstuur
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

<div
    id="live-voice"
    class="live-voice"
    data-state="idle"
    aria-hidden="true"
>
    <div class="live-voice-top">
        <div class="live-voice-title">
            Mashal AI <span>Live</span>
        </div>

        <button
            type="button"
            class="voice-control"
            id="voice-type"
        >
            ⌨ Typen
        </button>
    </div>

    <div class="live-voice-center">
        <div class="live-voice-stage">
            <div class="live-orb-wrap">
                <div
                    id="live-orb"
                    class="live-orb"
                    aria-hidden="true"
                ></div>
            </div>

            <div class="live-voice-status" aria-live="polite">
                <strong id="voice-status-title">
                    Klaar
                </strong>

                <span id="voice-status-detail">
                    Praat Nederlands, English of اردو.
                </span>
            </div>
        </div>
    </div>

    <div class="live-voice-bottom">
        <button
            type="button"
            class="voice-control round"
            id="voice-mute"
            aria-pressed="false"
            title="Microfoon dempen"
        >
            🎤
        </button>

        <button
            type="button"
            class="voice-control danger round"
            id="voice-close"
            title="Live gesprek afsluiten"
        >
            ✕
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const messageEndpoint = @json(route('ai.chat.message'));
    const voiceEndpoint = @json(route('ai.chat.voice.turn'));
    const csrfToken = @json(csrf_token());
    const chatConfigured = @json((bool) $chatConfigured);
    const voiceConfigured = @json((bool) $voiceConfigured);
    const maxFiles = @json((int) ($maxChatFiles ?? 5));
    const maxFileBytes = @json((int) (($maxChatFileMb ?? 10) * 1024 * 1024));

    const storageKey = 'mashal-ai-history-groq-v2';

    const form = document.getElementById('ai-form');
    const input = document.getElementById('ai-input');
    const sendButton = document.getElementById('ai-send');
    const messages = document.getElementById('ai-messages');
    const welcome = document.getElementById('ai-welcome');
    const errorBox = document.getElementById('ai-error');
    const clearButton = document.getElementById('ai-clear');
    const liveStartButton = document.getElementById('ai-live-start');
    const fileInput = document.getElementById('ai-files');
    const fileList = document.getElementById('ai-file-list');

    const liveVoice = document.getElementById('live-voice');
    const voiceTypeButton = document.getElementById('voice-type');
    const voiceCloseButton = document.getElementById('voice-close');
    const voiceMuteButton = document.getElementById('voice-mute');
    const voiceStatusTitle = document.getElementById('voice-status-title');
    const voiceStatusDetail = document.getElementById('voice-status-detail');
    const liveOrb = document.getElementById('live-orb');

    let history = loadHistory();
    let selectedFiles = [];
    let sendingText = false;

    let voiceActive = false;
    let voiceMuted = false;
    let voicePending = false;
    let voiceSpeaking = false;

    let mediaStream = null;
    let mediaRecorder = null;
    let recorderChunks = [];
    let recorderMimeType = '';
    let audioContext = null;
    let analyser = null;
    let analyserData = null;
    let analyserSource = null;
    let vadFrame = null;

    let recording = false;
    let recordingStartedAt = 0;
    let lastSpeechAt = 0;
    let consecutiveVoiceFrames = 0;
    let speakingStartedAt = 0;

    const VOICE_THRESHOLD = 0.035;
    const BARGE_THRESHOLD = 0.075;
    const SILENCE_MS = 720;
    const MIN_RECORD_MS = 320;
    const MAX_RECORD_MS = 20000;
    const BARGE_ARM_MS = 600;

    renderHistory();

    function loadHistory() {
        try {
            const parsed = JSON.parse(
                localStorage.getItem(storageKey) || '[]'
            );

            if (!Array.isArray(parsed)) {
                return [];
            }

            return parsed
                .filter(function (item) {
                    return item
                        && (item.role === 'user' || item.role === 'assistant')
                        && typeof item.content === 'string';
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
                JSON.stringify(history.slice(-20))
            );
        } catch (error) {
            // Local storage is optioneel.
        }
    }

    function pushHistory(role, content) {
        const text = String(content || '').trim();

        if (!text) {
            return;
        }

        history.push({
            role: role,
            content: text,
        });

        history = history.slice(-20);
        saveHistory();
    }

    function setError(message = '') {
        errorBox.textContent = message;
        errorBox.classList.toggle(
            'show',
            Boolean(message)
        );
    }

    function renderHistory() {
        if (!history.length) {
            return;
        }

        welcome?.remove();

        history.forEach(function (item) {
            renderMessage(
                item.role,
                item.content
            );
        });
    }

    function renderMessage(role, content, loading = false) {
        welcome?.remove();

        const article = document.createElement('article');
        article.className =
            'ai-message '
            + role
            + (loading ? ' loading' : '');

        const meta = document.createElement('div');
        meta.className = 'ai-message-meta';
        meta.textContent =
            role === 'user'
                ? 'Jij'
                : 'Mashal AI';

        const bubble = document.createElement('div');
        bubble.className = 'ai-message-bubble';
        bubble.textContent = content;

        article.append(
            meta,
            bubble
        );

        messages.appendChild(article);

        messages.scrollTop =
            messages.scrollHeight;

        return article;
    }

    function appendHistoryToFormData(formData) {
        history.slice(-20).forEach(
            function (item, index) {
                formData.append(
                    'history[' + index + '][role]',
                    item.role
                );

                formData.append(
                    'history[' + index + '][content]',
                    item.content
                );
            }
        );
    }

    function autoResize() {
        input.style.height = 'auto';

        input.style.height =
            Math.min(
                input.scrollHeight,
                180
            ) + 'px';
    }

    input.addEventListener(
        'input',
        autoResize
    );

    input.addEventListener(
        'keydown',
        function (event) {
            if (
                event.key === 'Enter'
                && !event.shiftKey
            ) {
                event.preventDefault();

                if (!sendingText) {
                    form.requestSubmit();
                }
            }
        }
    );

    form.addEventListener(
        'submit',
        async function (event) {
            event.preventDefault();

            if (
                !chatConfigured
                || sendingText
            ) {
                return;
            }

            const text = input.value.trim();

            if (
                !text
                && selectedFiles.length === 0
            ) {
                setError(
                    'Typ een bericht of voeg een bestand toe.'
                );

                return;
            }

            setError('');

            const historyBefore =
                history.slice(-20);

            if (text) {
                renderMessage(
                    'user',
                    text
                );
            } else {
                renderMessage(
                    'user',
                    'Bestand(en) toegevoegd'
                );
            }

            const loading = renderMessage(
                'assistant',
                'Mashal AI denkt…',
                true
            );

            sendingText = true;
            sendButton.disabled = true;

            const data = new FormData();

            data.append(
                'message',
                text
            );

            historyBefore.forEach(
                function (item, index) {
                    data.append(
                        'history[' + index + '][role]',
                        item.role
                    );

                    data.append(
                        'history[' + index + '][content]',
                        item.content
                    );
                }
            );

            selectedFiles.forEach(
                function (file) {
                    data.append(
                        'files[]',
                        file,
                        file.name
                    );
                }
            );

            input.value = '';
            autoResize();

            try {
                const response = await fetch(
                    messageEndpoint,
                    {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: data,
                        credentials: 'same-origin',
                    }
                );

                const payload = await safeJson(
                    response
                );

                loading?.remove();

                if (!response.ok || !payload.ok) {
                    throw new Error(
                        payload.message
                        || 'Mashal AI kon geen antwoord ophalen.'
                    );
                }

                if (text) {
                    pushHistory(
                        'user',
                        text
                    );
                } else {
                    pushHistory(
                        'user',
                        'Ik heb bestanden toegevoegd om te analyseren.'
                    );
                }

                pushHistory(
                    'assistant',
                    payload.message
                );

                renderMessage(
                    'assistant',
                    payload.message
                );

                clearSelectedFiles();
            } catch (error) {
                loading?.remove();

                setError(
                    error?.message
                    || 'Er ging iets mis.'
                );
            } finally {
                sendingText = false;
                sendButton.disabled = !chatConfigured;
            }
        }
    );

    clearButton.addEventListener(
        'click',
        function () {
            history = [];
            saveHistory();

            Array.from(
                messages.children
            ).forEach(function (child) {
                child.remove();
            });

            const welcomeBlock =
                document.createElement('div');

            welcomeBlock.className =
                'ai-welcome';

            welcomeBlock.innerHTML =
                '<div class="ai-welcome-orb" aria-hidden="true"></div>'
                + '<h1>Nieuwe conversatie</h1>'
                + '<p>Typ iets of start Live Voice.</p>';

            messages.appendChild(
                welcomeBlock
            );

            clearSelectedFiles();
            setError('');
        }
    );

    fileInput.addEventListener(
        'change',
        function () {
            addFiles(
                fileInput.files
            );

            fileInput.value = '';
        }
    );

    function addFiles(fileCollection) {
        const files =
            Array.from(
                fileCollection || []
            );

        for (const file of files) {
            if (
                selectedFiles.length
                >= maxFiles
            ) {
                setError(
                    'Je kunt maximaal '
                    + maxFiles
                    + ' bestanden toevoegen.'
                );

                break;
            }

            if (
                file.size
                > maxFileBytes
            ) {
                setError(
                    '"' + file.name + '" is te groot.'
                );

                continue;
            }

            selectedFiles.push(file);
        }

        renderFiles();
    }

    function renderFiles() {
        fileList.replaceChildren();

        selectedFiles.forEach(
            function (file, index) {
                const chip =
                    document.createElement('div');

                chip.className =
                    'ai-file-chip';

                const name =
                    document.createElement('span');

                name.textContent =
                    file.name;

                const remove =
                    document.createElement('button');

                remove.type =
                    'button';

                remove.textContent =
                    '×';

                remove.addEventListener(
                    'click',
                    function () {
                        selectedFiles.splice(
                            index,
                            1
                        );

                        renderFiles();
                    }
                );

                chip.append(
                    name,
                    remove
                );

                fileList.appendChild(
                    chip
                );
            }
        );
    }

    function clearSelectedFiles() {
        selectedFiles = [];
        renderFiles();
    }

    /* ------------------------------------------------------------------ */
    /* Live voice                                                         */
    /* ------------------------------------------------------------------ */

    liveStartButton.addEventListener(
        'click',
        startLiveVoice
    );

    voiceCloseButton.addEventListener(
        'click',
        stopLiveVoice
    );

    voiceTypeButton.addEventListener(
        'click',
        function () {
            stopLiveVoice();

            window.setTimeout(
                function () {
                    input.focus();
                },
                80
            );
        }
    );

    voiceMuteButton.addEventListener(
        'click',
        function () {
            voiceMuted =
                !voiceMuted;

            voiceMuteButton.classList.toggle(
                'muted',
                voiceMuted
            );

            voiceMuteButton.setAttribute(
                'aria-pressed',
                voiceMuted
                    ? 'true'
                    : 'false'
            );

            voiceMuteButton.textContent =
                voiceMuted
                    ? '🔇'
                    : '🎤';

            if (voiceMuted) {
                if (recording) {
                    stopRecording();
                }

                setVoiceState(
                    'idle',
                    'Microfoon gedempt',
                    'Tik opnieuw op de microfoon om verder te praten.'
                );
            } else if (
                !voicePending
                && !voiceSpeaking
            ) {
                setVoiceState(
                    'listening',
                    'Ik luister…',
                    'Praat gewoon. Je hoeft niets in te drukken.'
                );
            }
        }
    );

    async function startLiveVoice() {
        if (
            !chatConfigured
            || !voiceConfigured
        ) {
            setError(
                'Live Voice is nog niet volledig geconfigureerd.'
            );

            return;
        }

        if (voiceActive) {
            return;
        }

        if (
            !navigator.mediaDevices
            || !navigator.mediaDevices.getUserMedia
            || typeof MediaRecorder === 'undefined'
        ) {
            setError(
                'Deze browser ondersteunt de benodigde microfoonfuncties niet.'
            );

            return;
        }

        setError('');

        try {
            mediaStream =
                await navigator.mediaDevices.getUserMedia({
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true,
                        autoGainControl: true,
                    },
                    video: false,
                });

            await setupAudioAnalysis(
                mediaStream
            );

            voiceActive = true;
            voiceMuted = false;
            voicePending = false;
            voiceSpeaking = false;

            liveVoice.classList.add(
                'active'
            );

            liveVoice.setAttribute(
                'aria-hidden',
                'false'
            );

            document.body.style.overflow =
                'hidden';

            setVoiceState(
                'listening',
                'Ik luister…',
                'Praat gewoon. Na een korte stilte antwoordt Mashal AI vanzelf.'
            );

            runVadLoop();
        } catch (error) {
            setError(
                'Microfoontoegang is nodig voor Live Voice.'
            );

            cleanupVoiceResources();
        }
    }

    async function setupAudioAnalysis(stream) {
        const Context =
            window.AudioContext
            || window.webkitAudioContext;

        if (!Context) {
            throw new Error(
                'AudioContext wordt niet ondersteund.'
            );
        }

        audioContext =
            new Context();

        if (
            audioContext.state === 'suspended'
        ) {
            await audioContext.resume();
        }

        analyser =
            audioContext.createAnalyser();

        analyser.fftSize =
            1024;

        analyser.smoothingTimeConstant =
            .72;

        analyserData =
            new Uint8Array(
                analyser.fftSize
            );

        analyserSource =
            audioContext.createMediaStreamSource(
                stream
            );

        analyserSource.connect(
            analyser
        );
    }

    function runVadLoop() {
        cancelAnimationFrame(
            vadFrame
        );

        const loop = function () {
            if (
                !voiceActive
                || !analyser
                || !analyserData
            ) {
                return;
            }

            const level =
                microphoneLevel();

            animateOrbByLevel(
                level
            );

            if (!voiceMuted) {
                if (voiceSpeaking) {
                    detectBargeIn(
                        level
                    );
                } else if (!voicePending) {
                    detectSpeechTurn(
                        level
                    );
                }
            }

            vadFrame =
                requestAnimationFrame(
                    loop
                );
        };

        vadFrame =
            requestAnimationFrame(
                loop
            );
    }

    function microphoneLevel() {
        analyser.getByteTimeDomainData(
            analyserData
        );

        let total = 0;

        for (
            let index = 0;
            index < analyserData.length;
            index++
        ) {
            const normalized =
                (
                    analyserData[index]
                    - 128
                )
                / 128;

            total +=
                normalized
                * normalized;
        }

        return Math.sqrt(
            total
            / analyserData.length
        );
    }

    function animateOrbByLevel(level) {
        if (
            !liveOrb
            || voiceSpeaking
        ) {
            return;
        }

        if (
            liveVoice.dataset.state
            !== 'recording'
        ) {
            return;
        }

        const extra =
            Math.min(
                .15,
                level * 1.9
            );

        liveOrb.style.transform =
            'scale('
            + (1 + extra).toFixed(3)
            + ')';
    }

    function detectSpeechTurn(level) {
        if (recording) {
            if (level > VOICE_THRESHOLD) {
                lastSpeechAt =
                    Date.now();
            }

            const elapsed =
                Date.now()
                - recordingStartedAt;

            const silence =
                Date.now()
                - lastSpeechAt;

            if (
                elapsed >= MIN_RECORD_MS
                && silence >= SILENCE_MS
            ) {
                stopRecording();

                return;
            }

            if (
                elapsed >= MAX_RECORD_MS
            ) {
                stopRecording();
            }

            return;
        }

        if (level > VOICE_THRESHOLD) {
            consecutiveVoiceFrames++;
        } else {
            consecutiveVoiceFrames =
                Math.max(
                    0,
                    consecutiveVoiceFrames - 1
                );
        }

        if (
            consecutiveVoiceFrames >= 3
        ) {
            consecutiveVoiceFrames = 0;
            startRecording();
        }
    }

    function detectBargeIn(level) {
        if (
            Date.now() - speakingStartedAt
            < BARGE_ARM_MS
        ) {
            return;
        }

        if (level > BARGE_THRESHOLD) {
            consecutiveVoiceFrames++;
        } else {
            consecutiveVoiceFrames =
                Math.max(
                    0,
                    consecutiveVoiceFrames - 1
                );
        }

        if (
            consecutiveVoiceFrames >= 4
        ) {
            consecutiveVoiceFrames = 0;

            window.speechSynthesis?.cancel();

            voiceSpeaking = false;

            setVoiceState(
                'listening',
                'Ik luister…',
                'Je hebt Mashal AI onderbroken.'
            );

            window.setTimeout(
                startRecording,
                120
            );
        }
    }

    function chooseRecorderMimeType() {
        const candidates = [
            'audio/webm;codecs=opus',
            'audio/webm',
            'audio/ogg;codecs=opus',
            'audio/mp4',
        ];

        for (const type of candidates) {
            if (
                MediaRecorder.isTypeSupported(type)
            ) {
                return type;
            }
        }

        return '';
    }

    function startRecording() {
        if (
            !voiceActive
            || voiceMuted
            || voicePending
            || recording
            || !mediaStream
        ) {
            return;
        }

        recorderMimeType =
            chooseRecorderMimeType();

        try {
            mediaRecorder =
                recorderMimeType
                    ? new MediaRecorder(
                        mediaStream,
                        {
                            mimeType:
                                recorderMimeType,
                        }
                    )
                    : new MediaRecorder(
                        mediaStream
                    );
        } catch (error) {
            setVoiceState(
                'error',
                'Opname kon niet starten',
                'Probeer Live Voice opnieuw.'
            );

            return;
        }

        recorderChunks = [];

        mediaRecorder.ondataavailable =
            function (event) {
                if (
                    event.data
                    && event.data.size > 0
                ) {
                    recorderChunks.push(
                        event.data
                    );
                }
            };

        mediaRecorder.onstop =
            handleRecorderStop;

        recording = true;

        recordingStartedAt =
            Date.now();

        lastSpeechAt =
            Date.now();

        setVoiceState(
            'recording',
            'Ik hoor je…',
            'Praat verder. Ik verstuur automatisch zodra je klaar bent.'
        );

        mediaRecorder.start(
            180
        );
    }

    function stopRecording() {
        if (
            !recording
            || !mediaRecorder
        ) {
            return;
        }

        recording = false;

        try {
            if (
                mediaRecorder.state
                !== 'inactive'
            ) {
                mediaRecorder.stop();
            }
        } catch (error) {
            // Recorder was al gestopt.
        }
    }

    async function handleRecorderStop() {
        liveOrb.style.transform = '';

        if (
            !voiceActive
            || recorderChunks.length === 0
        ) {
            return;
        }

        const blob = new Blob(
            recorderChunks,
            {
                type:
                    mediaRecorder?.mimeType
                    || recorderMimeType
                    || 'audio/webm',
            }
        );

        recorderChunks = [];

        if (blob.size < 600) {
            setVoiceState(
                'listening',
                'Ik luister…',
                'Ik hoorde te weinig. Praat opnieuw.'
            );

            return;
        }

        await sendVoiceTurn(
            blob
        );
    }

    async function sendVoiceTurn(blob) {
        if (
            !voiceActive
            || voicePending
        ) {
            return;
        }

        voicePending = true;

        setVoiceState(
            'thinking',
            'Mashal AI denkt…',
            'Je hoeft niets te klikken.'
        );

        const historyBefore =
            history.slice(-20);

        const data =
            new FormData();

        const extension =
            mimeExtension(
                blob.type
            );

        data.append(
            'audio',
            blob,
            'voice.' + extension
        );

        historyBefore.forEach(
            function (item, index) {
                data.append(
                    'history[' + index + '][role]',
                    item.role
                );

                data.append(
                    'history[' + index + '][content]',
                    item.content
                );
            }
        );

        try {
            const response =
                await fetch(
                    voiceEndpoint,
                    {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN':
                                csrfToken,
                            'Accept':
                                'application/json',
                        },
                        body: data,
                        credentials:
                            'same-origin',
                    }
                );

            const payload =
                await safeJson(
                    response
                );

            if (
                !response.ok
                || !payload.ok
            ) {
                throw new Error(
                    payload.message
                    || 'Live Voice kon geen antwoord ophalen.'
                );
            }

            pushHistory(
                'user',
                payload.transcript
            );

            pushHistory(
                'assistant',
                payload.message
            );

            /*
             * De tekstchat wordt op de achtergrond bijgewerkt.
             * Tijdens Live Voice blijft deze volledig uit beeld.
             */
            renderMessage(
                'user',
                payload.transcript
            );

            renderMessage(
                'assistant',
                payload.message
            );

            voicePending = false;

            const voiceLocale =
                detectSpeechLocale(
                    payload.transcript
                    || payload.message
                );

            await speakLiveReply(
                payload.message,
                voiceLocale
            );
        } catch (error) {
            voicePending = false;

            setVoiceState(
                'error',
                'Er ging iets mis',
                error?.message
                || 'Probeer opnieuw.'
            );

            window.setTimeout(
                function () {
                    if (
                        voiceActive
                        && !voiceMuted
                    ) {
                        setVoiceState(
                            'listening',
                            'Ik luister…',
                            'Praat opnieuw wanneer je wilt.'
                        );
                    }
                },
                1800
            );
        }
    }

    function speakLiveReply(
        text,
        preferredLocale = null
    ) {
        return new Promise(
            function (resolve) {
                if (
                    !voiceActive
                    || !('speechSynthesis' in window)
                    || !('SpeechSynthesisUtterance' in window)
                ) {
                    setVoiceState(
                        'error',
                        'Geen spraakstem beschikbaar',
                        'Je browser kan het antwoord niet hardop afspelen.'
                    );

                    resolve();

                    return;
                }

                window.speechSynthesis.cancel();

                const cleanText =
                    speechText(text);

                const locale =
                    normalizeSpeechLocale(
                        preferredLocale
                        || detectSpeechLocale(cleanText)
                    );

                const utterance =
                    new SpeechSynthesisUtterance(
                        cleanText
                    );

                utterance.lang =
                    locale;

                const voice =
                    chooseSpeechVoice(
                        locale
                    );

                if (voice) {
                    utterance.voice =
                        voice;

                    utterance.lang =
                        voice.lang
                        || locale;
                }

                utterance.rate =
                    locale.toLowerCase()
                        .startsWith('ur')
                            ? 0.96
                            : 1.02;

                utterance.pitch =
                    1;

                utterance.onstart =
                    function () {
                        voiceSpeaking = true;

                        speakingStartedAt =
                            Date.now();

                        setVoiceState(
                            'speaking',
                            voiceStateCopy(
                                locale,
                                'speakingTitle'
                            ),
                            voiceStateCopy(
                                locale,
                                'speakingDetail'
                            )
                        );
                    };

                utterance.onend =
                    function () {
                        voiceSpeaking = false;

                        if (
                            voiceActive
                            && !voiceMuted
                        ) {
                            setVoiceState(
                                'listening',
                                voiceStateCopy(
                                    locale,
                                    'listeningTitle'
                                ),
                                voiceStateCopy(
                                    locale,
                                    'listeningDetail'
                                )
                            );
                        }

                        resolve();
                    };

                utterance.onerror =
                    function () {
                        voiceSpeaking = false;

                        if (
                            voiceActive
                            && !voiceMuted
                        ) {
                            setVoiceState(
                                'listening',
                                voiceStateCopy(
                                    locale,
                                    'listeningTitle'
                                ),
                                voiceStateCopy(
                                    locale,
                                    'speechErrorDetail'
                                )
                            );
                        }

                        resolve();
                    };

                window.speechSynthesis.speak(
                    utterance
                );
            }
        );
    }

    function detectSpeechLocale(text) {
        const value =
            String(text || '')
                .trim();

        if (!value) {
            return 'nl-NL';
        }

        /*
         * Urdu-specific characters.
         */
        if (
            /[\u0679\u0688\u0691\u06BA\u06BE\u06C1\u06CC\u06D2\u06D3]/u
                .test(value)
        ) {
            return 'ur-PK';
        }

        /*
         * Arabic-script fallback.
         * In Mashal AI Live Voice behandelen we dit als Urdu.
         */
        if (
            /[\u0600-\u06FF]/u
                .test(value)
        ) {
            return 'ur-PK';
        }

        const lower =
            value.toLowerCase();

        const padded =
            ` ${lower} `;

        const dutchWords = [
            ' de ',
            ' het ',
            ' een ',
            ' ik ',
            ' jij ',
            ' je ',
            ' jouw ',
            ' van ',
            ' voor ',
            ' met ',
            ' niet ',
            ' wel ',
            ' wat ',
            ' hoe ',
            ' waarom ',
            ' bedankt ',
            ' graag ',
            ' kunnen ',
            ' deze ',
            ' dit ',
            ' dat ',
            ' zijn ',
            ' hebben ',
            ' hallo ',
            ' goed ',
        ];

        const englishWords = [
            ' the ',
            ' a ',
            ' an ',
            ' i ',
            ' you ',
            ' your ',
            ' is ',
            ' are ',
            ' with ',
            ' for ',
            ' what ',
            ' how ',
            ' why ',
            ' thanks ',
            ' thank ',
            ' please ',
            ' can ',
            ' this ',
            ' that ',
            ' have ',
            ' hello ',
            ' hi ',
            ' sure ',
            ' yes ',
            ' no ',
        ];

        let dutchScore = 0;
        let englishScore = 0;

        dutchWords.forEach(
            function (word) {
                if (padded.includes(word)) {
                    dutchScore++;
                }
            }
        );

        englishWords.forEach(
            function (word) {
                if (padded.includes(word)) {
                    englishScore++;
                }
            }
        );

        if (englishScore > dutchScore) {
            return 'en-US';
        }

        return 'nl-NL';
    }

    function normalizeSpeechLocale(locale) {
        const value =
            String(locale || '')
                .trim()
                .toLowerCase();

        if (value.startsWith('ur')) {
            return 'ur-PK';
        }

        if (value.startsWith('en')) {
            return 'en-US';
        }

        return 'nl-NL';
    }

    function chooseSpeechVoice(locale) {
        const voices =
            window.speechSynthesis.getVoices();

        if (!voices.length) {
            return null;
        }

        const wanted =
            normalizeSpeechLocale(locale)
                .toLowerCase();

        const language =
            wanted.split('-')[0];

        return (
            voices.find(
                function (voice) {
                    return (
                        voice.lang
                        && voice.lang.toLowerCase()
                            === wanted
                    );
                }
            )
            || voices.find(
                function (voice) {
                    return (
                        voice.lang
                        && voice.lang.toLowerCase()
                            .startsWith(language)
                    );
                }
            )
            || (
                language === 'ur'
                    ? voices.find(
                        function (voice) {
                            const name =
                                String(
                                    voice.name || ''
                                ).toLowerCase();

                            return (
                                name.includes('urdu')
                                || name.includes('pakistan')
                            );
                        }
                    )
                    : null
            )
            || voices.find(
                function (voice) {
                    return voice.default;
                }
            )
            || voices[0]
            || null
        );
    }

    function voiceStateCopy(
        locale,
        key
    ) {
        const language =
            normalizeSpeechLocale(locale)
                .split('-')[0];

        const copy = {
            nl: {
                speakingTitle:
                    'Mashal AI spreekt…',
                speakingDetail:
                    'Je kunt Mashal AI onderbreken door zelf te beginnen praten.',
                listeningTitle:
                    'Ik luister…',
                listeningDetail:
                    'Praat gewoon verder.',
                speechErrorDetail:
                    'Het antwoord kon niet volledig worden uitgesproken.',
            },

            en: {
                speakingTitle:
                    'Mashal AI is speaking…',
                speakingDetail:
                    'You can interrupt Mashal AI by speaking.',
                listeningTitle:
                    'I’m listening…',
                listeningDetail:
                    'Just keep talking.',
                speechErrorDetail:
                    'The response could not be spoken completely.',
            },

            ur: {
                speakingTitle:
                    'Mashal AI بول رہا ہے…',
                speakingDetail:
                    'آپ بول کر Mashal AI کو درمیان میں روک سکتے ہیں۔',
                listeningTitle:
                    'میں سن رہا ہوں…',
                listeningDetail:
                    'آپ بات جاری رکھیں۔',
                speechErrorDetail:
                    'جواب مکمل طور پر آواز میں نہیں سنایا جا سکا۔',
            },
        };

        return (
            copy[language]?.[key]
            || copy.nl[key]
            || ''
        );
    }

    function speechText(text) {
        return String(text || '')
            .replace(/```[\s\S]*?```/g, ' codeblok ')
            .replace(/[`*_#>-]/g, ' ')
            .replace(/\[(.*?)\]\((.*?)\)/g, '$1')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function setVoiceState(
        state,
        title,
        detail
    ) {
        liveVoice.dataset.state =
            state;

        voiceStatusTitle.textContent =
            title;

        voiceStatusDetail.textContent =
            detail;
    }

    function mimeExtension(mime) {
        const normalized =
            String(mime || '')
                .toLowerCase();

        if (
            normalized.includes('ogg')
        ) {
            return 'ogg';
        }

        if (
            normalized.includes('mp4')
            || normalized.includes('m4a')
        ) {
            return 'm4a';
        }

        if (
            normalized.includes('wav')
        ) {
            return 'wav';
        }

        return 'webm';
    }

    function stopLiveVoice() {
        voiceActive = false;
        voicePending = false;
        voiceSpeaking = false;
        voiceMuted = false;

        window.speechSynthesis?.cancel();

        if (recording) {
            stopRecording();
        }

        cleanupVoiceResources();

        liveVoice.classList.remove(
            'active'
        );

        liveVoice.setAttribute(
            'aria-hidden',
            'true'
        );

        liveVoice.dataset.state =
            'idle';

        voiceMuteButton.classList.remove(
            'muted'
        );

        voiceMuteButton.setAttribute(
            'aria-pressed',
            'false'
        );

        voiceMuteButton.textContent =
            '🎤';

        document.body.style.overflow =
            '';
    }

    function cleanupVoiceResources() {
        cancelAnimationFrame(
            vadFrame
        );

        vadFrame = null;

        try {
            analyserSource?.disconnect();
        } catch (error) {
            // Geen actie nodig.
        }

        analyserSource = null;
        analyser = null;
        analyserData = null;

        if (audioContext) {
            audioContext.close()
                .catch(function () {});
        }

        audioContext = null;

        if (mediaStream) {
            mediaStream
                .getTracks()
                .forEach(function (track) {
                    track.stop();
                });
        }

        mediaStream = null;
        mediaRecorder = null;
        recorderChunks = [];
        recording = false;
        consecutiveVoiceFrames = 0;
    }

    async function safeJson(response) {
        try {
            return await response.json();
        } catch (error) {
            return {
                ok: false,
                message:
                    'De server gaf geen geldig antwoord terug.',
            };
        }
    }

    window.addEventListener(
        'beforeunload',
        cleanupVoiceResources
    );
});
</script>
@endpush
