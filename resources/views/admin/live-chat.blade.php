@extends('layouts.admin-layout')

@section('title', 'Live chat | Mashal Admin')
@section('page-title', 'Live chat')

@section('content')
<style>
    .lca {
        --bg: #0d1015;
        --panel: #13171d;
        --panel-2: #191e26;
        --panel-3: #222834;
        --border: rgba(255, 255, 255, 0.08);
        --border-strong: rgba(255, 255, 255, 0.14);
        --text: #f7f3eb;
        --muted: #9ea4ae;
        --accent: #e7bf82;
        --accent-soft: rgba(231, 191, 130, 0.12);
        --danger: #b75858;
        --success: #68b789;
        --shadow: 0 24px 70px rgba(0, 0, 0, 0.28);

        color: var(--text);
        font: 14px/1.5 Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .lca * {
        box-sizing: border-box;
    }

    .lca [hidden] {
        display: none !important;
    }

    .lca button,
    .lca textarea,
    .lca select,
    .lca input {
        font: inherit;
    }

    .lca button,
    .lca select {
        cursor: pointer;
    }

    .lca button:disabled,
    .lca select:disabled,
    .lca textarea:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    .lca :is(button, textarea, select, input):focus-visible {
        outline: 2px solid var(--accent);
        outline-offset: 2px;
    }

    .lca-shell {
        display: flex;
        flex-direction: column;
        gap: 18px;
        width: 100%;
        min-width: 0;
    }

    .lca-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
        padding: 20px 22px;
        border: 1px solid var(--border);
        border-radius: 20px;
        background:
            linear-gradient(180deg, rgba(255,255,255,0.025), transparent),
            var(--panel);
        box-shadow: var(--shadow);
    }

    .lca-top__copy {
        min-width: 0;
    }

    .lca-top__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
        color: var(--accent);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .lca-top h1 {
        margin: 0;
        font-size: clamp(22px, 2vw, 30px);
        line-height: 1.15;
        letter-spacing: -0.02em;
    }

    .lca-top p {
        margin: 7px 0 0;
        color: var(--muted);
        font-size: 13px;
    }

    .lca-presence {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 260px;
        padding: 12px 14px;
        border: 1px solid var(--border);
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.025);
    }

    .lca-presence input {
        width: 18px;
        height: 18px;
        accent-color: var(--success);
        flex: 0 0 auto;
    }

    .lca-presence__text {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .lca-presence__text strong {
        font-size: 13px;
        font-weight: 700;
    }

    .lca-presence__text small {
        color: var(--muted);
        font-size: 11px;
    }

    .lca-error {
        margin: 0;
        padding: 12px 14px;
        border: 1px solid rgba(255, 121, 121, 0.2);
        border-radius: 12px;
        background: rgba(183, 88, 88, 0.08);
        color: #ffbcbc;
    }

    .lca-grid {
        display: grid;
        grid-template-columns: 330px minmax(0, 1fr);
        min-height: 620px;
        height: min(76vh, 900px);
        overflow: hidden;
        border: 1px solid var(--border);
        border-radius: 22px;
        background: var(--bg);
        box-shadow: var(--shadow);
    }

    .lca-inbox {
        display: flex;
        flex-direction: column;
        min-width: 0;
        min-height: 0;
        border-right: 1px solid var(--border);
        background: #10141a;
    }

    .lca-inbox__top {
        padding: 16px;
        border-bottom: 1px solid var(--border);
    }

    .lca-inbox__title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
    }

    .lca-inbox__title strong {
        font-size: 13px;
    }

    .lca-inbox__title span {
        color: var(--muted);
        font-size: 11px;
    }

    .lca-filter {
        display: grid;
        grid-template-columns: 1fr;
    }

    .lca select,
    .lca button {
        color: var(--text);
        background: var(--panel-2);
        border: 1px solid var(--border-strong);
        border-radius: 11px;
        padding: 9px 12px;
        transition:
            border-color 0.18s ease,
            background 0.18s ease,
            transform 0.18s ease;
    }

    .lca button:hover:not(:disabled),
    .lca select:hover:not(:disabled) {
        border-color: rgba(231, 191, 130, 0.35);
        background: #202630;
    }

    .lca-list {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        overscroll-behavior: contain;
        scrollbar-width: thin;
    }

    .lca-item {
        display: block;
        width: 100%;
        padding: 16px !important;
        border: 0 !important;
        border-bottom: 1px solid rgba(255,255,255,0.05) !important;
        border-radius: 0 !important;
        background: transparent !important;
        color: var(--text) !important;
        text-align: left;
        overflow: hidden;
        transition: background 0.18s ease;
    }

    .lca-item:hover {
        background: rgba(255, 255, 255, 0.025) !important;
    }

    .lca-item[aria-pressed="true"] {
        background: var(--accent-soft) !important;
        box-shadow: inset 3px 0 var(--accent);
    }

    .lca-item strong,
    .lca-item span {
        display: block;
        min-width: 0;
    }

    .lca-item strong {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 13px;
    }

    .lca-item span {
        margin-top: 3px;
        color: var(--muted);
        font-size: 11px;
        overflow-wrap: anywhere;
    }

    .lca-pages {
        display: grid;
        grid-template-columns: 42px 1fr 42px;
        align-items: center;
        gap: 8px;
        padding: 12px;
        border-top: 1px solid var(--border);
        font-size: 12px;
        background: rgba(255,255,255,0.015);
    }

    .lca-pages span {
        text-align: center;
        color: var(--muted);
    }

    .lca-pages button {
        width: 42px;
        height: 38px;
        padding: 0;
        display: grid;
        place-items: center;
    }

    .lca-detail {
        display: grid;
        grid-template-rows: auto minmax(0, 1fr) auto;
        min-width: 0;
        min-height: 0;
        background:
            radial-gradient(circle at top right, rgba(231,191,130,0.025), transparent 28%),
            var(--bg);
    }

    .lca-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        min-width: 0;
        padding: 16px 18px;
        border-bottom: 1px solid var(--border);
        background: rgba(255,255,255,0.015);
    }

    .lca-heading__identity {
        min-width: 0;
    }

    .lca-heading strong {
        display: block;
        font-size: 15px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .lca-heading p {
        margin: 3px 0 0;
        color: var(--muted);
        overflow-wrap: anywhere;
        font-size: 11px;
    }

    .lca-heading [data-close] {
        flex: 0 0 auto;
    }

    .lca-log {
        min-width: 0;
        min-height: 0;
        overflow-y: auto;
        padding: 24px;
        scroll-behavior: smooth;
        scrollbar-width: thin;
    }

    .lca-message {
        width: fit-content;
        max-width: min(720px, 76%);
        padding: 12px 14px;
        margin: 0 0 14px;
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 15px 15px 15px 5px;
        background: var(--panel-3);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }

    .lca-message[data-sender="admin"] {
        margin-left: auto;
        border-color: rgba(231,191,130,0.25);
        border-radius: 15px 15px 5px 15px;
        background: linear-gradient(180deg, #e8c58f, #dcb779);
        color: #21180d;
    }

    .lca-msg-head {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 7px;
    }

    .lca-msg-head small {
        opacity: 0.72;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.03em;
    }

    .lca-avatar {
        width: 30px;
        height: 30px;
        flex: 0 0 30px;
        border-radius: 50%;
        overflow: hidden;
        display: grid;
        place-items: center;
        background: #363d49;
        color: #fff;
        font-size: 11px;
        font-weight: 800;
        border: 1px solid rgba(255,255,255,0.1);
    }

    .lca-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .lca-message p {
        margin: 4px 0 0;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
        font-size: 14px;
        line-height: 1.55;
    }

    .lca-media-image {
        display: block;
        max-width: min(360px, 100%);
        max-height: 340px;
        margin-top: 8px;
        border-radius: 12px;
        object-fit: cover;
    }

    .lca-message audio {
        display: block;
        width: min(360px, 100%);
        margin-top: 8px;
    }

    .lca-file-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 8px;
        color: inherit;
        text-decoration: none;
        font-weight: 600;
    }

    .lca-file-link:hover {
        text-decoration: underline;
    }

    .lca-delete {
        margin-top: 8px !important;
        padding: 2px 0 !important;
        border: 0 !important;
        background: transparent !important;
        color: inherit !important;
        opacity: 0.58;
        font-size: 10px !important;
    }

    .lca-delete:hover {
        opacity: 1;
        text-decoration: underline;
    }

    .lca-composer-wrap {
        padding: 14px;
        border-top: 1px solid var(--border);
        background:
            linear-gradient(180deg, rgba(255,255,255,0.01), rgba(255,255,255,0.025)),
            #0d1014;
    }

    .lca-form {
        display: grid;
        grid-template-columns: 44px 44px minmax(0, 1fr) auto;
        gap: 10px;
        align-items: end;
        width: 100%;
        min-width: 0;
    }

    .lca-tool {
        display: grid;
        place-items: center;
        width: 44px;
        height: 44px;
        padding: 0 !important;
        border-radius: 12px !important;
        flex: 0 0 44px;
        font-size: 17px;
    }

    .lca-tool[aria-pressed="true"] {
        background: #7d3434 !important;
        border-color: rgba(255,255,255,0.14) !important;
        color: #fff !important;
        box-shadow: 0 0 0 3px rgba(125, 52, 52, 0.18);
    }

    .lca textarea {
        width: 100%;
        min-width: 0;
        min-height: 48px;
        max-height: 160px;
        resize: vertical;
        background: #080b0f;
        color: var(--text);
        border: 1px solid var(--border-strong);
        border-radius: 12px;
        padding: 12px 14px;
        font-size: 14px;
        line-height: 1.45;
    }

    .lca textarea::placeholder {
        color: #777f8a;
    }

    .lca-submit {
        min-width: 96px;
        height: 44px;
        padding-inline: 18px !important;
        font-weight: 700;
        color: #22180d !important;
        background: linear-gradient(180deg, #efcd97, #d8ae70) !important;
        border-color: transparent !important;
        box-shadow: 0 8px 22px rgba(216, 174, 112, 0.16);
    }

    .lca-submit:hover:not(:disabled) {
        transform: translateY(-1px);
        background: linear-gradient(180deg, #f2d5a5, #dfb879) !important;
    }

    @media (max-width: 1100px) {
        .lca-grid {
            grid-template-columns: 285px minmax(0, 1fr);
        }

        .lca-message {
            max-width: 84%;
        }
    }

    @media (max-width: 850px) {
        .lca-grid {
            grid-template-columns: 1fr;
            height: auto;
            min-height: 0;
        }

        .lca-inbox {
            max-height: 320px;
            border-right: 0;
            border-bottom: 1px solid var(--border);
        }

        .lca-detail {
            min-height: 560px;
            height: 68vh;
        }

        .lca-top {
            align-items: stretch;
        }

        .lca-presence {
            width: 100%;
            min-width: 0;
        }
    }

    @media (max-width: 640px) {
        .lca-log {
            padding: 14px;
        }

        .lca-heading {
            padding: 14px;
        }

        .lca-message {
            max-width: 92%;
        }

        .lca-form {
            grid-template-columns: 44px 44px minmax(0, 1fr);
        }

        .lca-submit {
            grid-column: 1 / -1;
            width: 100%;
        }

        .lca-composer-wrap {
            padding: 10px;
        }
    }
</style>

<div
    class="lca"
    id="admin-live-chat"
    data-inbox="{{ route('admin.live-chat.conversations') }}"
    data-presence="{{ route('admin.live-chat.presence') }}"
    data-base="{{ route('admin.live-chat.conversations') }}"
>
    <div class="lca-shell">
        <header class="lca-top">
            <div class="lca-top__copy">
                <div class="lca-top__eyebrow">
                    <span>●</span>
                    Support desk
                </div>

                <h1>Live gesprekken</h1>

                <p>
                    Beheer gesprekken met gasten en ingelogde bezoekers vanuit één inbox.
                </p>
            </div>

            <label class="lca-presence">
                <input
                    type="checkbox"
                    id="lca-online"
                    checked
                >

                <span class="lca-presence__text">
                    <strong>Beschikbaar voor live chat</strong>
                    <small>Actief zolang dit tabblad zichtbaar is.</small>
                </span>
            </label>
        </header>

        <p
            class="lca-error"
            role="status"
            hidden
        ></p>

        <div class="lca-grid">
            <aside
                class="lca-inbox"
                aria-label="Gesprekken"
            >
                <div class="lca-inbox__top">
                    <div class="lca-inbox__title">
                        <strong>Inbox</strong>
                        <span>Live support</span>
                    </div>

                    <div class="lca-filter">
                        <select aria-label="Gesprekken filteren">
                            <option value="active">
                                Actieve gesprekken
                            </option>

                            <option value="closed">
                                Afgesloten gesprekken
                            </option>
                        </select>
                    </div>
                </div>

                <div class="lca-list">
                    <p style="padding:16px;color:#9ea4ae">
                        Gesprekken laden…
                    </p>
                </div>

                <div class="lca-pages">
                    <button
                        type="button"
                        data-prev
                        aria-label="Vorige pagina"
                        title="Vorige pagina"
                    >
                        ←
                    </button>

                    <span data-page></span>

                    <button
                        type="button"
                        data-next
                        aria-label="Volgende pagina"
                        title="Volgende pagina"
                    >
                        →
                    </button>
                </div>
            </aside>

            <section
                class="lca-detail"
                aria-label="Geselecteerd gesprek"
            >
                <header class="lca-heading">
                    <div class="lca-heading__identity">
                        <strong data-name>
                            Kies een gesprek
                        </strong>

                        <p data-email>
                            Hier verschijnen naam en e-mailadres van ingelogde bezoekers.
                        </p>

                        <p data-status></p>
                    </div>

                    <button
                        type="button"
                        data-close
                        hidden
                    >
                        Afsluiten
                    </button>
                </header>

                <div
                    class="lca-log"
                    role="log"
                    aria-live="polite"
                    aria-relevant="additions"
                    aria-label="Berichten"
                ></div>

                <div class="lca-composer-wrap">
                    <form class="lca-form">
                        <label
                            class="lca-tool"
                            title="Bestand versturen"
                            aria-label="Bestand versturen"
                        >
                            📎

                            <input
                                class="lca-file"
                                type="file"
                                accept="image/jpeg,image/png,image/webp,image/gif,application/pdf,text/plain,.doc,.docx,.xls,.xlsx"
                                hidden
                            >
                        </label>

                        <button
                            type="button"
                            class="lca-tool lca-voice"
                            title="Spraakbericht opnemen"
                            aria-label="Spraakbericht opnemen"
                            aria-pressed="false"
                        >
                            🎤
                        </button>

                        <textarea
                            aria-label="Antwoord aan bezoeker"
                            placeholder="Typ je antwoord…"
                            maxlength="4000"
                            required
                            disabled
                        ></textarea>

                        <button
                            type="submit"
                            class="lca-submit"
                            disabled
                        >
                            Verstuur
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </div>
</div>

<script
    src="{{ asset('js/admin-live-chat.js') }}?v=3"
    defer
></script>
@endsection