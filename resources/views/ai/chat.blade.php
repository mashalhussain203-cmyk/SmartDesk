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
        grid-template-rows:
            auto
            minmax(0, 1fr)
            auto
            auto
            auto;
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
        min-height: 0;
        padding: 22px;
        overflow-x: hidden;
        overflow-y: auto;
        overscroll-behavior: contain;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
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



    .mashal-ai-attachments {
        padding: 0 13px 10px;
    }

    .mashal-ai-file-row {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .mashal-ai-file-button {
        min-height: 38px;
        padding: 0 12px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 11px;
        color: #aeb4bc;
        background: rgba(255,255,255,.025);
        font-size: 11px;
        font-weight: 900;
        cursor: pointer;
    }

    .mashal-ai-file-button:hover {
        border-color: rgba(229,182,111,.18);
        color: #e9dfcf;
        background: rgba(229,182,111,.045);
    }

    .mashal-ai-file-input {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0,0,0,0);
        white-space: nowrap;
        border: 0;
    }

    .mashal-ai-file-help {
        color: #59616b;
        font-size: 9px;
        line-height: 1.45;
    }

    .mashal-ai-file-list {
        margin-top: 8px;
        display: flex;
        gap: 7px;
        flex-wrap: wrap;
    }

    .mashal-ai-file-chip {
        max-width: 100%;
        min-height: 32px;
        padding: 5px 7px 5px 10px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 10px;
        color: #9ba2ab;
        background: rgba(255,255,255,.02);
        font-size: 10px;
        font-weight: 800;
    }

    .mashal-ai-file-chip-name {
        max-width: 230px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .mashal-ai-file-remove {
        width: 24px;
        height: 24px;
        display: grid;
        place-items: center;
        border: 0;
        border-radius: 7px;
        color: #8e959e;
        background: rgba(255,255,255,.035);
        cursor: pointer;
    }

    .mashal-ai-file-remove:hover {
        color: #e3a5a5;
        background: rgba(220,100,100,.06);
    }

    .mashal-ai-card.dragging {
        outline: 1px solid rgba(229,182,111,.38);
        outline-offset: -2px;
    }

    .mashal-ai-voicebar {
        padding: 11px 13px 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .mashal-ai-voice-controls {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .mashal-ai-voice-button,
    .mashal-ai-voice-toggle,
    .mashal-ai-voice-stop {
        min-height: 38px;
        padding: 0 12px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 11px;
        color: #aeb4bc;
        background: rgba(255,255,255,.025);
        font-size: 11px;
        font-weight: 900;
        cursor: pointer;
        transition:
            border-color .18s ease,
            background .18s ease,
            color .18s ease,
            transform .18s ease;
    }

    .mashal-ai-voice-button:hover,
    .mashal-ai-voice-toggle:hover,
    .mashal-ai-voice-stop:hover {
        border-color: rgba(229,182,111,.18);
        color: #e9dfcf;
        background: rgba(229,182,111,.045);
    }

    .mashal-ai-voice-button.active,
    .mashal-ai-voice-toggle.active {
        border-color: rgba(111,213,140,.22);
        color: #d8f6df;
        background: rgba(111,213,140,.065);
        box-shadow: 0 0 0 3px rgba(111,213,140,.025);
    }

    .mashal-ai-voice-button.listening {
        animation: mashalAiPulse 1.25s ease-in-out infinite;
    }

    .mashal-ai-voice-stop {
        color: #d99b9b;
        border-color: rgba(217,105,105,.12);
    }

    .mashal-ai-voice-stop[hidden] {
        display: none !important;
    }

    .mashal-ai-voice-status {
        min-height: 24px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #666e78;
        font-size: 10px;
        font-weight: 800;
    }

    .mashal-ai-voice-dot {
        width: 7px;
        height: 7px;
        flex: 0 0 auto;
        border-radius: 50%;
        background: #505761;
    }

    .mashal-ai-voice-status.listening .mashal-ai-voice-dot {
        background: #6fd58c;
        box-shadow: 0 0 14px rgba(111,213,140,.50);
    }

    .mashal-ai-voice-status.speaking .mashal-ai-voice-dot {
        background: #e7be76;
        box-shadow: 0 0 14px rgba(231,190,118,.44);
    }

    .mashal-ai-voice-status.thinking .mashal-ai-voice-dot {
        background: #8b83ff;
        box-shadow: 0 0 14px rgba(139,131,255,.42);
    }

    .mashal-ai-voice-status.error .mashal-ai-voice-dot {
        background: #d66c6c;
        box-shadow: 0 0 14px rgba(214,108,108,.42);
    }

    .mashal-ai-voice-unsupported {
        color: #9b7777;
    }

    @keyframes mashalAiPulse {
        0%, 100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(111,213,140,.12);
        }

        50% {
            transform: scale(1.015);
            box-shadow: 0 0 0 7px rgba(111,213,140,0);
        }
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


    .mashal-ai-clear,
    .mashal-ai-suggestion,
    .mashal-ai-file-button,
    .mashal-ai-file-remove,
    .mashal-ai-voice-button,
    .mashal-ai-voice-toggle,
    .mashal-ai-voice-stop,
    .mashal-ai-send {
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
        user-select: none;
        -webkit-user-select: none;
    }

    .mashal-ai-file-button,
    .mashal-ai-voice-button,
    .mashal-ai-voice-toggle,
    .mashal-ai-voice-stop,
    .mashal-ai-send,
    .mashal-ai-clear {
        position: relative;
        z-index: 2;
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
            height: calc(100dvh - 122px);
            min-height: 0;
            border-radius: 17px;
            overflow: hidden;
        }

        @supports not (height: 100dvh) {
            .mashal-ai-card {
                height: calc(100svh - 122px);
            }
        }

        .mashal-ai-toolbar {
            padding: 9px 10px;
        }


        .mashal-ai-card {
            height: min(
                calc(100dvh - 122px),
                calc(var(--mashal-visual-height, 100dvh) - 96px)
            );
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



        .mashal-ai-attachments {
            min-width: 0;
            padding: 6px 8px;
            border-top: 1px solid rgba(255,255,255,.045);
            background: rgba(8,10,14,.96);
        }

        .mashal-ai-file-row {
            flex-wrap: nowrap;
            min-width: 0;
        }

        .mashal-ai-file-button {
            min-height: 44px;
            flex: 0 0 auto;
            font-size: 12px;
        }

        .mashal-ai-file-help {
            min-width: 0;
            width: auto;
            flex: 1 1 auto;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 9px;
        }

        .mashal-ai-file-list {
            max-height: 76px;
            overflow-x: auto;
            overflow-y: hidden;
            flex-wrap: nowrap;
            -webkit-overflow-scrolling: touch;
        }

        .mashal-ai-file-chip {
            flex: 0 0 auto;
        }

        .mashal-ai-file-chip-name {
            max-width: 170px;
        }

        .mashal-ai-voicebar {
            min-width: 0;
            padding: 6px 8px;
            align-items: center;
            flex-wrap: nowrap;
            border-top: 1px solid rgba(255,255,255,.045);
            background: rgba(8,10,14,.96);
        }

        .mashal-ai-voice-controls {
            min-width: 0;
            width: auto;
            flex: 0 1 auto;
            flex-wrap: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .mashal-ai-voice-button,
        .mashal-ai-voice-toggle,
        .mashal-ai-voice-stop {
            min-height: 44px;
            flex: 0 0 auto;
            font-size: 12px;
        }

        .mashal-ai-voice-status {
            min-width: 0;
            width: auto;
            flex: 1 1 auto;
            padding: 0 2px;
            overflow: hidden;
            font-size: 9px;
        }

        .mashal-ai-voice-status > span:last-child {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .mashal-ai-composer {
            position: relative;
            z-index: 5;
            padding:
                8px
                8px
                calc(8px + env(safe-area-inset-bottom));
            background: rgba(7,9,12,.985);
        }

        .mashal-ai-input-wrap {
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 5px;
            padding: 5px;
            border-radius: 13px;
        }

        .mashal-ai-input {
            min-width: 0;
            min-height: 48px;
            max-height: 116px;
            font-size: 16px;
        }

        .mashal-ai-send {
            min-width: 72px;
            min-height: 48px;
            padding-inline: 11px;
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



            <div class="mashal-ai-attachments">
                <div class="mashal-ai-file-row">
                    <label
                        class="mashal-ai-file-button"
                        for="mashal-ai-files"
                    >
                        📎 Bestanden
                    </label>

                    <input
                        id="mashal-ai-files"
                        class="mashal-ai-file-input"
                        type="file"
                        name="files[]"
                        multiple
                        accept=".pdf,.docx,.xlsx,.txt,.md,.markdown,.csv,.json,.xml,.html,.htm,.log,.php,.js,.ts,.jsx,.tsx,.css,.scss,.sql,.yaml,.yml,.ini,.conf,.py,.java,.c,.cpp,.h,.hpp,.cs,.go,.rs,.sh,.ps1,.rb,.swift,.dart,.vue,.svelte"
                    >

                    <span class="mashal-ai-file-help">
                        Max. {{ $maxChatFiles ?? 5 }} bestanden · {{ $maxChatFileMb ?? 10 }} MB per bestand · PDF, DOCX, XLSX, tekst en code.
                    </span>
                </div>

                <div
                    class="mashal-ai-file-list"
                    id="mashal-ai-file-list"
                    aria-live="polite"
                ></div>
            </div>

            <div class="mashal-ai-voicebar">
                <div class="mashal-ai-voice-controls">
                    <button
                        type="button"
                        class="mashal-ai-voice-button"
                        id="mashal-ai-mic"
                        aria-pressed="false"
                    >
                        🎤 Praat
                    </button>

                    <button
                        type="button"
                        class="mashal-ai-voice-toggle"
                        id="mashal-ai-voice-mode"
                        aria-pressed="false"
                        title="Na het AI-antwoord automatisch opnieuw luisteren"
                    >
                        ↻ Doorpraten
                    </button>

                    <button
                        type="button"
                        class="mashal-ai-voice-stop"
                        id="mashal-ai-voice-stop"
                        hidden
                    >
                        ⏹ Stop
                    </button>
                </div>

                <div
                    class="mashal-ai-voice-status"
                    id="mashal-ai-voice-status"
                    role="status"
                    aria-live="polite"
                >
                    <span
                        class="mashal-ai-voice-dot"
                        aria-hidden="true"
                    ></span>

                    <span id="mashal-ai-voice-status-text">
                        Microfoon gereed
                    </span>
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
                    Documenten worden tijdelijk door Laravel uitgelezen en alleen als tekstcontext naar de AI gestuurd.
                    Voor voice gebruikt de pagina de spraakfuncties van je browser; microfoontoegang vereist HTTPS en jouw toestemming.
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


    const micButton =
        document.getElementById('mashal-ai-mic');

    const voiceModeButton =
        document.getElementById('mashal-ai-voice-mode');

    const voiceStopButton =
        document.getElementById('mashal-ai-voice-stop');

    const voiceStatus =
        document.getElementById('mashal-ai-voice-status');

    const voiceStatusText =
        document.getElementById('mashal-ai-voice-status-text');


    const fileInput =
        document.getElementById('mashal-ai-files');

    const fileList =
        document.getElementById('mashal-ai-file-list');

    const chatCard =
        document.querySelector('.mashal-ai-card');

    const maxChatFiles =
        @json((int) ($maxChatFiles ?? 5));

    const maxChatFileBytes =
        @json((int) (($maxChatFileMb ?? 10) * 1024 * 1024));

    let selectedFiles = [];


    const mobileViewport =
        window.visualViewport || null;

    function updateMobileViewport() {
        if (!mobileViewport) {
            return;
        }

        document.documentElement.style.setProperty(
            '--mashal-visual-height',
            mobileViewport.height + 'px'
        );
    }

    updateMobileViewport();

    mobileViewport?.addEventListener(
        'resize',
        updateMobileViewport
    );

    const SpeechRecognitionConstructor =
        window.SpeechRecognition ||
        window.webkitSpeechRecognition ||
        null;

    const speechSynthesisSupported =
        'speechSynthesis' in window &&
        'SpeechSynthesisUtterance' in window;

    let recognition = null;
    let listening = false;
    let voiceMode = false;
    let shouldRestartListening = false;
    let voiceStoppedManually = false;
    let sendingMessage = false;

    let history =
        loadHistory();



    function formatFileSize(bytes) {
        if (!Number.isFinite(bytes)) {
            return '';
        }

        if (bytes < 1024) {
            return bytes + ' B';
        }

        if (bytes < 1024 * 1024) {
            return (
                bytes /
                1024
            ).toFixed(1) + ' KB';
        }

        return (
            bytes /
            1024 /
            1024
        ).toFixed(1) + ' MB';
    }

    function fileKey(file) {
        return [
            file.name,
            file.size,
            file.lastModified,
        ].join('::');
    }

    function addFiles(files) {
        const incoming =
            Array.from(
                files || []
            );

        for (const file of incoming) {
            if (
                selectedFiles.length >=
                maxChatFiles
            ) {
                setError(
                    'Je kunt maximaal ' +
                    maxChatFiles +
                    ' bestanden tegelijk toevoegen.'
                );

                break;
            }

            if (
                file.size >
                maxChatFileBytes
            ) {
                setError(
                    '"' +
                    file.name +
                    '" is groter dan ' +
                    Math.round(
                        maxChatFileBytes /
                        1024 /
                        1024
                    ) +
                    ' MB.'
                );

                continue;
            }

            const key =
                fileKey(file);

            if (
                selectedFiles.some(
                    function (existing) {
                        return (
                            fileKey(existing) ===
                            key
                        );
                    }
                )
            ) {
                continue;
            }

            selectedFiles.push(
                file
            );
        }

        renderSelectedFiles();
    }

    function removeFile(index) {
        selectedFiles.splice(
            index,
            1
        );

        renderSelectedFiles();
    }

    function clearSelectedFiles() {
        selectedFiles = [];

        if (fileInput) {
            fileInput.value = '';
        }

        renderSelectedFiles();
    }

    function renderSelectedFiles() {
        if (!fileList) {
            return;
        }

        fileList.replaceChildren();

        selectedFiles.forEach(
            function (file, index) {
                const chip =
                    document.createElement(
                        'div'
                    );

                chip.className =
                    'mashal-ai-file-chip';

                const name =
                    document.createElement(
                        'span'
                    );

                name.className =
                    'mashal-ai-file-chip-name';

                name.textContent =
                    file.name +
                    ' · ' +
                    formatFileSize(
                        file.size
                    );

                const remove =
                    document.createElement(
                        'button'
                    );

                remove.type =
                    'button';

                remove.className =
                    'mashal-ai-file-remove';

                remove.setAttribute(
                    'aria-label',
                    'Verwijder ' +
                    file.name
                );

                remove.textContent =
                    '×';

                remove.addEventListener(
                    'click',
                    function () {
                        removeFile(
                            index
                        );
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

    function setVoiceStatus(
        state = '',
        message = 'Microfoon gereed'
    ) {
        if (voiceStatus) {
            voiceStatus.classList.remove(
                'listening',
                'speaking',
                'thinking',
                'error'
            );

            if (state) {
                voiceStatus.classList.add(
                    state
                );
            }
        }

        if (voiceStatusText) {
            voiceStatusText.textContent =
                message;
        }
    }

    function updateVoiceButtons() {
        if (micButton) {
            micButton.classList.toggle(
                'active',
                listening
            );

            micButton.classList.toggle(
                'listening',
                listening
            );

            micButton.setAttribute(
                'aria-pressed',
                listening
                    ? 'true'
                    : 'false'
            );

            micButton.textContent =
                listening
                    ? '🎤 Luisteren…'
                    : '🎤 Praat';
        }

        if (voiceModeButton) {
            voiceModeButton.classList.toggle(
                'active',
                voiceMode
            );

            voiceModeButton.setAttribute(
                'aria-pressed',
                voiceMode
                    ? 'true'
                    : 'false'
            );

            voiceModeButton.textContent =
                voiceMode
                    ? '↻ Doorpraten aan'
                    : '↻ Doorpraten';
        }

        if (voiceStopButton) {
            voiceStopButton.hidden =
                !(
                    listening ||
                    voiceMode ||
                    (
                        speechSynthesisSupported &&
                        window.speechSynthesis.speaking
                    )
                );
        }
    }

    function stopSpeaking() {
        if (!speechSynthesisSupported) {
            return;
        }

        window.speechSynthesis.cancel();
    }

    function stopListening() {
        if (!recognition) {
            listening = false;
            updateVoiceButtons();
            return;
        }

        try {
            recognition.stop();
        } catch (error) {
            // Recognition kan al gestopt zijn.
        }

        listening = false;
        updateVoiceButtons();
    }

    function stopVoiceConversation() {
        voiceStoppedManually = true;
        shouldRestartListening = false;
        voiceMode = false;

        stopListening();
        stopSpeaking();

        setVoiceStatus(
            '',
            'Stemmodus gestopt'
        );

        updateVoiceButtons();
    }

    function createRecognition() {
        if (!SpeechRecognitionConstructor) {
            return null;
        }

        const instance =
            new SpeechRecognitionConstructor();

        instance.lang =
            document.documentElement.lang ||
            navigator.language ||
            'nl-NL';

        instance.continuous =
            false;

        instance.interimResults =
            true;

        instance.maxAlternatives =
            1;

        instance.onstart = function () {
            listening = true;

            setVoiceStatus(
                'listening',
                'Luisteren… praat nu'
            );

            updateVoiceButtons();
        };

        instance.onresult = function (event) {
            let interimText = '';
            let finalText = '';

            for (
                let index = event.resultIndex;
                index < event.results.length;
                index++
            ) {
                const transcript =
                    event.results[index][0]?.transcript ||
                    '';

                if (event.results[index].isFinal) {
                    finalText +=
                        transcript;
                } else {
                    interimText +=
                        transcript;
                }
            }

            const visibleText =
                (
                    finalText ||
                    interimText
                ).trim();

            if (
                input &&
                visibleText
            ) {
                input.value =
                    visibleText;

                autoResize();
            }

            if (
                finalText.trim() &&
                !sendingMessage
            ) {
                stopListening();

                const spokenMessage =
                    finalText.trim();

                if (input) {
                    input.value = '';
                    autoResize();
                }

                sendMessage(
                    spokenMessage,
                    true
                );
            }
        };

        instance.onerror = function (event) {
            listening = false;
            updateVoiceButtons();

            let message =
                'Microfoon kon niet worden gebruikt.';

            if (
                event.error === 'not-allowed' ||
                event.error === 'service-not-allowed'
            ) {
                message =
                    'Microfoontoegang is geweigerd. Geef deze website toestemming voor de microfoon.';
            } else if (event.error === 'no-speech') {
                message =
                    'Ik hoorde geen spraak. Probeer opnieuw.';
            } else if (event.error === 'audio-capture') {
                message =
                    'Er is geen werkende microfoon gevonden.';
            } else if (event.error === 'network') {
                message =
                    'Spraakherkenning kon de netwerkservice niet bereiken.';
            }

            setVoiceStatus(
                'error',
                message
            );
        };

        instance.onend = function () {
            listening = false;
            updateVoiceButtons();

            if (
                voiceMode &&
                shouldRestartListening &&
                !sendingMessage &&
                !voiceStoppedManually
            ) {
                window.setTimeout(
                    startListening,
                    350
                );
            } else if (
                !sendingMessage &&
                !(
                    speechSynthesisSupported &&
                    window.speechSynthesis.speaking
                )
            ) {
                setVoiceStatus(
                    '',
                    voiceMode
                        ? 'Doorpraten staat aan'
                        : 'Microfoon gereed'
                );
            }
        };

        return instance;
    }

    function startListening() {
        if (!SpeechRecognitionConstructor) {
            setVoiceStatus(
                'error',
                'Deze browser ondersteunt geen spraakherkenning. Gebruik bij voorkeur Chrome of Edge.'
            );

            micButton?.classList.add(
                'mashal-ai-voice-unsupported'
            );

            return;
        }

        if (
            sendingMessage ||
            listening
        ) {
            return;
        }

        voiceStoppedManually = false;
        shouldRestartListening = false;

        stopSpeaking();

        if (!recognition) {
            recognition =
                createRecognition();
        }

        try {
            recognition.start();
        } catch (error) {
            setVoiceStatus(
                'error',
                'De microfoon kon niet worden gestart. Probeer opnieuw.'
            );
        }
    }

    function chooseVoice() {
        if (!speechSynthesisSupported) {
            return null;
        }

        const voices =
            window.speechSynthesis.getVoices();

        if (!voices.length) {
            return null;
        }

        const preferredLanguage =
            (
                document.documentElement.lang ||
                navigator.language ||
                'nl-NL'
            ).toLowerCase();

        return (
            voices.find(function (voice) {
                return (
                    voice.lang?.toLowerCase() ===
                    preferredLanguage
                );
            }) ||
            voices.find(function (voice) {
                return (
                    voice.lang?.toLowerCase().startsWith(
                        preferredLanguage.split('-')[0]
                    )
                );
            }) ||
            voices.find(function (voice) {
                return voice.default;
            }) ||
            voices[0]
        );
    }

    function speakText(text) {
        return new Promise(function (resolve) {
            if (
                !speechSynthesisSupported ||
                !text
            ) {
                resolve();
                return;
            }

            stopListening();
            stopSpeaking();

            const utterance =
                new SpeechSynthesisUtterance(
                    text
                );

            utterance.lang =
                document.documentElement.lang ||
                navigator.language ||
                'nl-NL';

            const voice =
                chooseVoice();

            if (voice) {
                utterance.voice =
                    voice;
            }

            utterance.rate =
                1;

            utterance.pitch =
                1;

            utterance.onstart = function () {
                setVoiceStatus(
                    'speaking',
                    'Mashal AI spreekt…'
                );

                updateVoiceButtons();
            };

            utterance.onend = function () {
                setVoiceStatus(
                    '',
                    voiceMode
                        ? 'Klaar. Ik luister zo weer…'
                        : 'Microfoon gereed'
                );

                updateVoiceButtons();
                resolve();
            };

            utterance.onerror = function () {
                setVoiceStatus(
                    'error',
                    'Het antwoord kon niet hardop worden afgespeeld.'
                );

                updateVoiceButtons();
                resolve();
            };

            window.speechSynthesis.speak(
                utterance
            );
        });
    }

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

    async function sendMessage(message, fromVoice = false, files = []) {
        if (
            !configured ||
            (
                !message &&
                (!files || !files.length)
            ) ||
            sendingMessage
        ) {
            return;
        }

        sendingMessage = true;

        shouldRestartListening = false;

        if (fromVoice) {
            setVoiceStatus(
                'thinking',
                'Mashal AI denkt…'
            );
        }

        setError('');

        const historyForRequest =
            history.slice(-20);

        const visibleUserMessage =
            message ||
            (
                files && files.length
                    ? '📎 ' +
                        files.length +
                        (
                            files.length === 1
                                ? ' bestand toegevoegd'
                                : ' bestanden toegevoegd'
                        )
                    : ''
            );

        history.push({
            role: 'user',
            content: visibleUserMessage,
        });

        renderMessage(
            'user',
            visibleUserMessage
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
            const formData =
                new FormData();

            formData.append(
                'message',
                message || ''
            );

            historyForRequest.forEach(
                function (historyItem, index) {
                    formData.append(
                        'history[' +
                        index +
                        '][role]',
                        historyItem.role
                    );

                    formData.append(
                        'history[' +
                        index +
                        '][content]',
                        historyItem.content
                    );
                }
            );

            Array.from(
                files || []
            ).forEach(
                function (file) {
                    formData.append(
                        'files[]',
                        file,
                        file.name
                    );
                }
            );

            const response =
                await fetch(
                    endpoint,
                    {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        credentials: 'same-origin',
                        body: formData,
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

            if (
                files &&
                files.length
            ) {
                clearSelectedFiles();
            }

            if (
                fromVoice ||
                voiceMode
            ) {
                await speakText(
                    data.message
                );
            }

            if (
                voiceMode &&
                !voiceStoppedManually
            ) {
                shouldRestartListening = true;

                window.setTimeout(
                    startListening,
                    350
                );
            }
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

            if (fromVoice || voiceMode) {
                setVoiceStatus(
                    'error',
                    'De AI kon nu niet antwoorden.'
                );
            }
        } finally {
            sendingMessage = false;

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

            const filesToSend =
                selectedFiles.slice();

            if (
                !message &&
                filesToSend.length === 0
            ) {
                return;
            }

            if (input) {
                input.value =
                    '';

                autoResize();
            }

            await sendMessage(
                message,
                false,
                filesToSend
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
            stopVoiceConversation();
            clearSelectedFiles();

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



    document
        .querySelector('.mashal-ai-file-button')
        ?.addEventListener(
            'click',
            function (event) {
                /*
                 * Op sommige mobiele webviews werkt label->input minder
                 * betrouwbaar. Een directe user-gesture click is robuuster.
                 */
                if (
                    event.currentTarget?.tagName === 'LABEL' &&
                    fileInput
                ) {
                    event.preventDefault();

                    try {
                        fileInput.click();
                    } catch (error) {
                        // De standaard labelactie blijft de normale fallback.
                    }
                }
            }
        );

    fileInput?.addEventListener(
        'change',
        function () {
            addFiles(
                fileInput.files
            );

            /*
             * Input leegmaken zodat hetzelfde bestand later opnieuw gekozen
             * kan worden nadat het uit de selectie is verwijderd.
             */
            fileInput.value = '';
        }
    );

    chatCard?.addEventListener(
        'dragover',
        function (event) {
            if (
                !event.dataTransfer ||
                !Array.from(
                    event.dataTransfer.types || []
                ).includes('Files')
            ) {
                return;
            }

            event.preventDefault();

            chatCard.classList.add(
                'dragging'
            );
        }
    );

    chatCard?.addEventListener(
        'dragleave',
        function (event) {
            if (
                event.relatedTarget &&
                chatCard.contains(
                    event.relatedTarget
                )
            ) {
                return;
            }

            chatCard.classList.remove(
                'dragging'
            );
        }
    );

    chatCard?.addEventListener(
        'drop',
        function (event) {
            event.preventDefault();

            chatCard.classList.remove(
                'dragging'
            );

            addFiles(
                event.dataTransfer?.files
            );
        }
    );

    micButton?.addEventListener(
        'click',
        function () {
            if (listening) {
                stopListening();

                setVoiceStatus(
                    '',
                    'Microfoon gestopt'
                );

                return;
            }

            startListening();
        }
    );

    voiceModeButton?.addEventListener(
        'click',
        function () {
            voiceMode =
                !voiceMode;

            voiceStoppedManually =
                false;

            shouldRestartListening =
                false;

            updateVoiceButtons();

            if (voiceMode) {
                setVoiceStatus(
                    '',
                    'Doorpraten staat aan'
                );

                if (
                    !listening &&
                    !sendingMessage
                ) {
                    startListening();
                }
            } else {
                stopListening();
                stopSpeaking();

                setVoiceStatus(
                    '',
                    'Doorpraten staat uit'
                );
            }
        }
    );

    voiceStopButton?.addEventListener(
        'click',
        stopVoiceConversation
    );

    window.addEventListener(
        'beforeunload',
        function () {
            stopListening();
            stopSpeaking();
        }
    );

    if (!SpeechRecognitionConstructor) {
        if (micButton) {
            micButton.disabled = true;
            micButton.title =
                'Live spraakherkenning wordt niet ondersteund door deze mobiele browser.';
        }

        if (voiceModeButton) {
            voiceModeButton.disabled = true;
            voiceModeButton.title =
                'Spraakherkenning wordt niet ondersteund door deze browser.';
        }

        setVoiceStatus(
            'error',
            'Live spraakherkenning wordt door deze mobiele browser niet ondersteund. Typen, bestanden uploaden en AI-antwoorden blijven wel werken.'
        );
    } else {
        setVoiceStatus(
            '',
            'Microfoon gereed'
        );
    }

    updateVoiceButtons();

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
