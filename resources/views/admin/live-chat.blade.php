@extends('layouts.admin-layout')

@section('title', 'Live chat | Mashal Admin')
@section('page-title', 'Live chat')

@section('content')
<style>
    .lca {
        --ink: #111318;
        --ink-soft: #5f6673;
        --paper: #f4f1ea;
        --panel: #ffffff;
        --panel-soft: #f8f7f3;
        --line: #d8d4cb;
        --line-dark: #17191f;
        --blue: #2457ff;
        --blue-soft: #e9efff;
        --green: #1d8f5b;
        --red: #c93d35;
        --yellow: #e6b84a;
        --shadow: 0 18px 50px rgba(20, 23, 30, .08);

        min-width: 0;
        color: var(--ink);
        font: 14px/1.45 Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .lca *, .lca *::before, .lca *::after { box-sizing: border-box; }
    .lca [hidden] { display: none !important; }
    .lca button, .lca input, .lca select, .lca textarea { font: inherit; }
    .lca button, .lca select, .lca label { -webkit-tap-highlight-color: transparent; }
    .lca button, .lca select, .lca label[for] { cursor: pointer; }
    .lca :is(button,input,select,textarea):focus-visible { outline: 3px solid rgba(36,87,255,.25); outline-offset: 2px; }
    .lca button:disabled, .lca input:disabled, .lca select:disabled, .lca textarea:disabled { opacity: .42; cursor: not-allowed; }
    .lca a { color: inherit; }

    .lca-shell { display: grid; gap: 14px; min-width: 0; }

    /* top strip */
    .lca-top {
        display: grid;
        grid-template-columns: minmax(0,1fr) auto;
        gap: 18px;
        align-items: end;
        padding: 30px 32px 26px;
        border: 1px solid var(--line-dark);
        background: var(--paper);
        box-shadow: 8px 8px 0 var(--line-dark);
    }
    .lca-eyebrow {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 9px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .18em;
        text-transform: uppercase;
    }
    .lca-eyebrow__dot { width: 9px; height: 9px; border-radius: 50%; background: var(--green); box-shadow: 0 0 0 4px rgba(29,143,91,.12); }
    .lca-top h1 { margin: 0; max-width: 900px; font-size: clamp(38px, 5vw, 72px); line-height: .92; letter-spacing: -.055em; font-weight: 900; }
    .lca-top p { margin: 13px 0 0; max-width: 720px; color: var(--ink-soft); font-size: 13px; }
    .lca-presence {
        display: grid;
        grid-template-columns: auto minmax(0,1fr);
        gap: 11px;
        align-items: center;
        min-width: 280px;
        padding: 12px 14px;
        border: 1px solid var(--line-dark);
        background: #fff;
    }
    .lca-presence input { width: 20px; height: 20px; accent-color: var(--green); }
    .lca-presence__copy { display: grid; gap: 1px; }
    .lca-presence__copy strong { font-size: 12px; }
    .lca-presence__copy small { color: var(--ink-soft); font-size: 9px; }

    /* stats become a single utility rail */
    .lca-stats {
        display: grid;
        grid-template-columns: repeat(3,minmax(0,1fr));
        border: 1px solid var(--line-dark);
        background: var(--panel);
    }
    .lca-stat { display: flex; align-items: center; gap: 10px; min-width: 0; padding: 11px 14px; border-right: 1px solid var(--line); }
    .lca-stat:last-child { border-right: 0; }
    .lca-stat__icon { width: 28px; height: 28px; display: grid; place-items: center; flex: 0 0 28px; border: 1px solid var(--line-dark); background: var(--blue-soft); font-size: 13px; }
    .lca-stat__copy { display: grid; gap: 1px; min-width: 0; }
    .lca-stat__copy strong { font-size: 11px; font-weight: 800; }
    .lca-stat__copy small { color: var(--ink-soft); font-size: 9px; }

    .lca-error { margin: 0; padding: 11px 13px; border: 1px solid var(--red); background: #fff0ee; color: #8a201b; font-size: 11px; }

    /* workspace */
    .lca-grid {
        display: grid;
        grid-template-columns: minmax(280px, 330px) minmax(0,1fr);
        min-height: 650px;
        height: min(76vh, 900px);
        overflow: hidden;
        border: 1px solid var(--line-dark);
        background: #fff;
        box-shadow: var(--shadow);
    }

    /* inbox */
    .lca-inbox { display: grid; grid-template-rows: auto minmax(0,1fr) auto; min-width: 0; min-height: 0; border-right: 1px solid var(--line-dark); background: var(--panel-soft); }
    .lca-inbox__head { display: grid; gap: 10px; padding: 14px; border-bottom: 1px solid var(--line-dark); background: #fff; }
    .lca-inbox__title { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .lca-inbox__title strong { font-size: 15px; letter-spacing: -.02em; }
    .lca-inbox__title span { padding: 4px 7px; border: 1px solid var(--line); color: var(--ink-soft); font-size: 8px; text-transform: uppercase; letter-spacing: .12em; }

    .lca-search { position: relative; display: flex; align-items: center; }
    .lca-search__icon { position: absolute; left: 11px; color: var(--ink-soft); pointer-events: none; }
    .lca-search input, .lca select, .lca textarea {
        border: 1px solid var(--line-dark);
        border-radius: 0;
        background: #fff;
        color: var(--ink);
    }
    .lca-search input { width: 100%; height: 40px; padding: 8px 10px 8px 34px; }
    .lca-search input::placeholder, .lca textarea::placeholder { color: #9b9fa6; }

    .lca-filter { display: grid; grid-template-columns: minmax(0,1fr) 40px; gap: 7px; }
    .lca select { width: 100%; height: 40px; padding: 7px 10px; }
    .lca button { border: 1px solid var(--line-dark); border-radius: 0; background: #fff; color: var(--ink); transition: transform .15s ease, background .15s ease, color .15s ease; }
    .lca button:hover:not(:disabled) { background: var(--ink); color: #fff; }
    .lca-icon-button { width: 40px; height: 40px; padding: 0; display: grid; place-items: center; font-size: 16px; }

    .lca-list { min-height: 0; overflow-y: auto; overscroll-behavior: contain; scrollbar-width: thin; }
    .lca-list-empty { margin: 0; padding: 30px 18px; color: var(--ink-soft); text-align: center; font-size: 11px; }

    .lca-item {
        position: relative;
        display: grid !important;
        grid-template-columns: 38px minmax(0,1fr) auto;
        gap: 10px;
        width: 100%;
        min-width: 0;
        padding: 12px 13px !important;
        border: 0 !important;
        border-bottom: 1px solid var(--line) !important;
        background: transparent !important;
        color: var(--ink) !important;
        text-align: left;
    }
    .lca-item:hover { background: #fff !important; color: var(--ink) !important; }
    .lca-item[aria-pressed="true"] { background: var(--blue) !important; color: #fff !important; box-shadow: inset 4px 0 0 var(--line-dark); }
    .lca-item[aria-pressed="true"] :is(.lca-item__meta,.lca-item__time,.lca-kind) { color: rgba(255,255,255,.76) !important; }
    .lca-item__avatar { width: 38px; height: 38px; display: grid; place-items: center; overflow: hidden; border: 1px solid var(--line-dark); border-radius: 50%; background: #f0eee8; color: var(--ink); font-size: 11px; font-weight: 900; }
    .lca-item__avatar img { width: 100%; height: 100%; object-fit: cover; }
    .lca-item__body { min-width: 0; }
    .lca-item__name-row { display: flex; align-items: center; gap: 6px; min-width: 0; }
    .lca-item__name-row strong { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 11px; }
    .lca-kind { flex: 0 0 auto; padding: 2px 5px; border: 1px solid currentColor; color: var(--ink-soft); font-size: 7px; letter-spacing: .08em; text-transform: uppercase; }
    .lca-item__meta { display: block; margin-top: 3px; overflow: hidden; color: var(--ink-soft); font-size: 9px; text-overflow: ellipsis; white-space: nowrap; }
    .lca-item__time { color: var(--ink-soft); font-size: 8px; white-space: nowrap; }
    .lca-unread { position: absolute; right: 10px; bottom: 10px; min-width: 20px; height: 20px; display: grid; place-items: center; padding: 0 6px; border: 1px solid var(--line-dark); border-radius: 0; background: var(--yellow); color: var(--ink); font-size: 8px; font-weight: 900; }

    .lca-pages { display: grid; grid-template-columns: 40px minmax(0,1fr) 40px; gap: 7px; align-items: center; padding: 9px; border-top: 1px solid var(--line-dark); background: #fff; }
    .lca-pages span { color: var(--ink-soft); text-align: center; font-size: 9px; }
    .lca-pages button { width: 40px; height: 36px; padding: 0; }

    /* detail */
    .lca-detail { position: relative; display: grid; grid-template-rows: auto minmax(0,1fr) auto auto; min-width: 0; min-height: 0; background: #fff; }
    .lca-heading { display: flex; align-items: center; justify-content: space-between; gap: 14px; min-width: 0; padding: 15px 18px; border-bottom: 1px solid var(--line-dark); background: var(--paper); }
    .lca-heading__identity { display: grid; gap: 2px; min-width: 0; }
    .lca-heading__name-row { display: flex; align-items: center; gap: 8px; min-width: 0; }
    .lca-heading strong { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 16px; letter-spacing: -.02em; }
    .lca-heading p { margin: 0; color: var(--ink-soft); overflow-wrap: anywhere; font-size: 9px; }
    .lca-status-chip { display: inline-flex; align-items: center; gap: 6px; width: fit-content; margin-top: 5px; padding: 3px 6px; border: 1px solid var(--line); background: #fff; color: var(--ink-soft); font-size: 8px; text-transform: uppercase; letter-spacing: .06em; }
    .lca-status-chip::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: var(--green); }
    .lca-heading__actions { display: flex; align-items: center; gap: 7px; flex: 0 0 auto; }
    .lca-heading__actions button { padding: 8px 10px; font-size: 9px; }
    .lca-close { border-color: var(--red) !important; color: var(--red) !important; }
    .lca-close:hover { background: var(--red) !important; color: #fff !important; }
    .lca-email-handoff { border-color: var(--blue) !important; color: var(--blue) !important; }
    .lca-email-handoff:hover, .lca-email-handoff[data-active="true"] { background: var(--blue) !important; color: #fff !important; }

    .lca-log { min-width: 0; min-height: 0; overflow-y: auto; padding: 26px; scroll-behavior: smooth; scrollbar-width: thin; overscroll-behavior: contain; background-image: linear-gradient(rgba(17,19,24,.035) 1px, transparent 1px), linear-gradient(90deg, rgba(17,19,24,.035) 1px, transparent 1px); background-size: 28px 28px; }
    .lca-empty-chat { height: 100%; min-height: 240px; display: grid; place-items: center; padding: 30px; color: var(--ink-soft); text-align: center; }
    .lca-empty-chat__icon { width: 64px; height: 64px; display: grid; place-items: center; margin: 0 auto 12px; border: 1px solid var(--line-dark); background: var(--blue); color: #fff; font-size: 24px; transform: rotate(-3deg); }
    .lca-empty-chat strong { display: block; color: var(--ink); font-size: 16px; }
    .lca-empty-chat p { max-width: 330px; margin: 6px auto 0; font-size: 10px; }

    .lca-message { width: fit-content; max-width: min(720px,76%); margin: 0 0 16px; padding: 11px 12px; border: 1px solid var(--line-dark); background: #fff; box-shadow: 4px 4px 0 rgba(17,19,24,.10); }
    .lca-message[data-sender="admin"] { margin-left: auto; background: var(--blue); color: #fff; box-shadow: 4px 4px 0 #102d99; }
    .lca-msg-head { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
    .lca-msg-head small { opacity: .7; font-size: 8px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
    .lca-avatar { width: 27px; height: 27px; flex: 0 0 27px; display: grid; place-items: center; overflow: hidden; border: 1px solid currentColor; border-radius: 50%; background: #f0eee8; color: var(--ink); font-size: 9px; font-weight: 900; }
    .lca-message[data-sender="admin"] .lca-avatar { background: #fff; color: var(--blue); border-color: #fff; }
    .lca-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .lca-message p { margin: 3px 0 0; white-space: pre-wrap; overflow-wrap: anywhere; font-size: 12px; line-height: 1.55; }
    .lca-media-image { display: block; max-width: min(390px,100%); max-height: 370px; margin-top: 8px; border: 1px solid currentColor; object-fit: cover; }
    .lca-message audio { display: block; width: min(370px,100%); margin-top: 8px; }
    .lca-file-link { display: inline-flex; align-items: center; gap: 7px; margin-top: 8px; font-weight: 700; }
    .lca-delete { margin-top: 7px !important; padding: 2px 0 !important; border: 0 !important; background: transparent !important; color: inherit !important; opacity: .58; font-size: 8px !important; }
    .lca-delete:hover { opacity: 1; text-decoration: underline; }

    .lca-scroll-down { position: absolute; right: 18px; bottom: 104px; z-index: 5; width: 40px; height: 40px; display: grid; place-items: center; padding: 0 !important; border-radius: 50% !important; background: var(--ink) !important; color: #fff !important; box-shadow: 0 10px 25px rgba(17,19,24,.22); }

    .lca-typing { display: flex; align-items: center; gap: 8px; min-height: 44px; padding: 7px 18px; border-top: 1px solid var(--line); background: #faf9f6; }
    .lca-typing__avatar { width: 26px; height: 26px; display: grid; place-items: center; overflow: hidden; border: 1px solid var(--line-dark); border-radius: 50%; background: #fff; font-size: 9px; font-weight: 900; }
    .lca-typing__avatar img { width: 100%; height: 100%; object-fit: cover; }
    .lca-typing__bubble { display: flex; align-items: center; gap: 4px; padding: 9px 11px; border: 1px solid var(--line-dark); background: #fff; }
    .lca-typing__dot { width: 5px; height: 5px; border-radius: 50%; background: var(--blue); animation: lcaTypingDot 1.1s infinite ease-in-out; }
    .lca-typing__dot:nth-child(2){ animation-delay:.12s; }
    .lca-typing__dot:nth-child(3){ animation-delay:.24s; }
    .lca-typing__label { color: var(--ink-soft); font-size: 8px; }
    @keyframes lcaTypingDot { 0%,60%,100%{opacity:.25;transform:translateY(0)} 30%{opacity:1;transform:translateY(-3px)} }

    /* composer */
    .lca-composer-wrap { padding: 11px 14px 14px; border-top: 1px solid var(--line-dark); background: var(--paper); }
    .lca-composer-info { min-height: 18px; display: flex; justify-content: space-between; gap: 12px; margin-bottom: 6px; color: var(--ink-soft); font-size: 8px; }
    .lca-recorder-state { color: var(--red); font-weight: 800; }
    .lca-form { display: grid; grid-template-columns: 44px 44px minmax(0,1fr) auto; gap: 8px; align-items: end; width: 100%; min-width: 0; }
    .lca-tool { width: 44px; height: 44px; display: grid; place-items: center; padding: 0 !important; border: 1px solid var(--line-dark); background: #fff; user-select: none; font-size: 16px; }
    .lca-tool:hover { background: var(--ink); color: #fff; }
    .lca-tool[aria-pressed="true"] { background: var(--red) !important; color: #fff !important; border-color: var(--red) !important; }
    .lca textarea { width: 100%; min-width: 0; min-height: 44px; max-height: 150px; resize: none; padding: 11px 12px; line-height: 1.45; }
    .lca-submit { min-width: 108px; height: 44px; padding: 0 18px !important; border-color: var(--blue) !important; background: var(--blue) !important; color: #fff !important; font-weight: 900; }
    .lca-submit:hover:not(:disabled) { transform: translateY(-2px); background: #173dcc !important; }

    .lca-toast-stack { position: fixed; right: 22px; bottom: 22px; z-index: 9999; display: grid; gap: 8px; width: min(360px,calc(100vw - 32px)); pointer-events: none; }
    .lca-toast { padding: 11px 13px; border: 1px solid var(--line-dark); background: #fff; color: var(--ink); box-shadow: 5px 5px 0 var(--line-dark); font-size: 10px; }
    .lca-toast[data-kind="success"] { border-color: var(--green); }
    .lca-toast[data-kind="error"] { border-color: var(--red); color: #8a201b; }

    @media (max-width: 1080px) {
        .lca-top { grid-template-columns: 1fr; align-items: start; }
        .lca-presence { min-width: 0; width: 100%; }
        .lca-grid { grid-template-columns: 290px minmax(0,1fr); }
    }
    @media (max-width: 820px) {
        .lca-top { padding: 24px; box-shadow: 5px 5px 0 var(--line-dark); }
        .lca-top h1 { font-size: clamp(38px,12vw,64px); }
        .lca-stats { grid-template-columns: 1fr; }
        .lca-stat { border-right: 0; border-bottom: 1px solid var(--line); }
        .lca-stat:last-child { border-bottom: 0; }
        .lca-grid { grid-template-columns: 1fr; height: auto; min-height: 0; }
        .lca-inbox { max-height: 350px; border-right: 0; border-bottom: 1px solid var(--line-dark); }
        .lca-detail { min-height: 610px; height: 72vh; }
        .lca-heading { align-items: flex-start; }
        .lca-heading__actions { flex-wrap: wrap; justify-content: flex-end; }
    }
    @media (max-width: 620px) {
        .lca-top { padding: 18px; }
        .lca-top h1 { font-size: 44px; }
        .lca-log { padding: 14px; }
        .lca-message { max-width: 92%; }
        .lca-heading { padding: 12px; }
        .lca-form { grid-template-columns: 44px 44px minmax(0,1fr); }
        .lca-submit { grid-column: 1 / -1; width: 100%; }
        .lca-composer-wrap { padding: 10px; }
        .lca-composer-info { flex-direction: column; gap: 2px; }
    }

    @media (prefers-reduced-motion: reduce) {
        .lca *, .lca *::before, .lca *::after { scroll-behavior: auto !important; animation: none !important; transition: none !important; }
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
                <div class="lca-eyebrow"><span class="lca-eyebrow__dot"></span> Mashal Support / Live</div>
                <h1>Support<br>zonder ruis.</h1>
                <p>Alle actieve gesprekken, uploads en voiceberichten in één compacte werkruimte.</p>
            </div>

            <label class="lca-presence">
                <input type="checkbox" id="lca-online" checked>
                <span class="lca-presence__copy">
                    <strong>Live beschikbaar</strong>
                    <small>Je blijft zichtbaar zolang dit tabblad actief is.</small>
                </span>
            </label>
        </header>

        <div class="lca-stats">
            <div class="lca-stat">
                <div class="lca-stat__icon">01</div>
                <div class="lca-stat__copy"><strong data-inbox-count>—</strong><small>Gesprekken op deze pagina</small></div>
            </div>
            <div class="lca-stat">
                <div class="lca-stat__icon">↻</div>
                <div class="lca-stat__copy"><strong>Realtime sync</strong><small>Nieuwe activiteit wordt automatisch geladen</small></div>
            </div>
            <div class="lca-stat">
                <div class="lca-stat__icon">+</div>
                <div class="lca-stat__copy"><strong>Rich replies</strong><small>Tekst, bestanden, afbeeldingen en voice</small></div>
            </div>
        </div>

        <p class="lca-error" role="status" aria-live="polite" hidden></p>

        <div class="lca-grid">
            <aside class="lca-inbox" aria-label="Gesprekken">
                <div class="lca-inbox__head">
                    <div class="lca-inbox__title"><strong>Inbox</strong><span>Live queue</span></div>

                    <div class="lca-search">
                        <span class="lca-search__icon" aria-hidden="true">⌕</span>
                        <input type="search" data-search placeholder="Zoek naam of e-mailadres…" aria-label="Gesprekken zoeken" autocomplete="off">
                    </div>

                    <div class="lca-filter">
                        <select data-filter aria-label="Gesprekken filteren">
                            <option value="active">Actieve gesprekken</option>
                            <option value="closed">Afgesloten gesprekken</option>
                        </select>
                        <button type="button" class="lca-icon-button" data-refresh title="Inbox verversen" aria-label="Inbox verversen">↻</button>
                    </div>
                </div>

                <div class="lca-list">
                    <p class="lca-list-empty">Gesprekken laden…</p>
                </div>

                <div class="lca-pages">
                    <button type="button" data-prev aria-label="Vorige pagina" title="Vorige pagina">←</button>
                    <span data-page>Pagina —</span>
                    <button type="button" data-next aria-label="Volgende pagina" title="Volgende pagina">→</button>
                </div>
            </aside>

            <section class="lca-detail" aria-label="Geselecteerd gesprek">
                <header class="lca-heading">
                    <div class="lca-heading__identity">
                        <div class="lca-heading__name-row"><strong data-name>Kies een gesprek</strong></div>
                        <p data-email>Selecteer links een gesprek om de berichten te openen.</p>
                        <span class="lca-status-chip" data-status>Geen gesprek geselecteerd</span>
                    </div>

                    <div class="lca-heading__actions">
                        <button type="button" class="lca-email-handoff" data-email-handoff data-active="false" hidden>✉ Verder via e-mail</button>
                        <button type="button" class="lca-email-handoff" data-email-settings hidden title="E-mailadres, onderwerp en titel beheren">✎ E-mail / titel</button>
                        <button type="button" class="lca-close" data-close hidden>Afsluiten</button>
                    </div>
                </header>

                <div class="lca-log" role="log" aria-live="polite" aria-relevant="additions" aria-label="Berichten">
                    <div class="lca-empty-chat" data-empty-chat>
                        <div>
                            <div class="lca-empty-chat__icon">↗</div>
                            <strong>Open een gesprek</strong>
                            <p>Berichten, documenten, afbeeldingen en spraakberichten verschijnen hier.</p>
                        </div>
                    </div>
                </div>

                <button type="button" class="lca-scroll-down" data-scroll-down title="Naar nieuwste bericht" aria-label="Naar nieuwste bericht" hidden>↓</button>

                <div class="lca-typing" data-typing-indicator aria-live="polite" aria-label="Bezoeker is aan het typen" hidden>
                    <span class="lca-typing__avatar" data-typing-avatar aria-hidden="true">B</span>
                    <div class="lca-typing__bubble" aria-hidden="true">
                        <span class="lca-typing__dot"></span>
                        <span class="lca-typing__dot"></span>
                        <span class="lca-typing__dot"></span>
                    </div>
                    <span class="lca-typing__label" data-typing-text>Bezoeker typt…</span>
                </div>

                <div class="lca-composer-wrap">
                    <div class="lca-composer-info">
                        <span data-composer-state>Selecteer een gesprek om te antwoorden.</span>
                        <span class="lca-recorder-state" data-recorder-state></span>
                    </div>

                    <form class="lca-form" autocomplete="off">
                        <label class="lca-tool" title="Bestand versturen" aria-label="Bestand versturen">
                            ＋
                            <input class="lca-file" type="file" accept="image/jpeg,image/png,image/webp,image/gif,application/pdf,text/plain,.doc,.docx,.xls,.xlsx" hidden>
                        </label>

                        <button type="button" class="lca-tool lca-voice" title="Spraakbericht opnemen" aria-label="Spraakbericht opnemen" aria-pressed="false">●</button>

                        <textarea aria-label="Antwoord aan bezoeker" placeholder="Schrijf een antwoord…" maxlength="4000" rows="1" required disabled></textarea>

                        <button type="submit" class="lca-submit" disabled>Verstuur →</button>
                    </form>
                </div>
            </section>
        </div>

        <div class="lca-toast-stack" data-toasts aria-live="polite" aria-atomic="false"></div>
    </div>
</div>

<script src="{{ asset('js/admin-live-chat.js') }}?v=60" defer></script>
@endsection
