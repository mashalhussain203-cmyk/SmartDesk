@extends('layouts.admin-layout')







@section('title', 'Live chat | Mashal Admin')



@section('page-title', 'Live chat')







@section('content')



<style>
    .lca {
        --lca-bg: #0c1016;
        --lca-bg-2: #0f141c;
        --lca-panel: #121821;
        --lca-panel-2: #161e29;
        --lca-panel-3: #1b2431;
        --lca-line: rgba(255,255,255,.075);
        --lca-line-strong: rgba(255,255,255,.13);
        --lca-text: #f7f3ec;
        --lca-text-2: #d7dbe2;
        --lca-muted: #8e97a5;
        --lca-muted-2: #6f7886;
        --lca-accent: #d9b77c;
        --lca-accent-2: #f2d7aa;
        --lca-accent-soft: rgba(217,183,124,.10);
        --lca-green: #52c78b;
        --lca-green-soft: rgba(82,199,139,.11);
        --lca-red: #ee7474;
        --lca-red-soft: rgba(238,116,116,.10);
        --lca-shadow: 0 22px 70px rgba(0,0,0,.28);
        --lca-radius: 16px;
        --lca-radius-sm: 11px;

        width: 100%;
        min-width: 0;
        color: var(--lca-text);
        font: 13px/1.5 Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .lca *, .lca *::before, .lca *::after { box-sizing: border-box; }
    .lca [hidden] { display: none !important; }
    .lca button, .lca input, .lca select, .lca textarea { font: inherit; }
    .lca button, .lca select, .lca label { -webkit-tap-highlight-color: transparent; }
    .lca button, .lca select, .lca label[for] { cursor: pointer; }
    .lca a { color: inherit; }
    .lca :is(button,input,select,textarea):focus-visible {
        outline: 2px solid var(--lca-accent);
        outline-offset: 2px;
    }
    .lca :is(button,input,select,textarea):disabled { opacity: .42; cursor: not-allowed; }

    .lca-shell {
        display: grid;
        gap: 12px;
        width: 100%;
        min-width: 0;
    }

    /* ---------- compact executive header ---------- */
    .lca-top {
        position: relative;
        overflow: hidden;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 24px;
        min-height: 118px;
        padding: 20px 22px;
        border: 1px solid var(--lca-line);
        border-radius: var(--lca-radius);
        background:
            radial-gradient(circle at 92% 0%, rgba(217,183,124,.13), transparent 28%),
            linear-gradient(135deg, rgba(255,255,255,.025), transparent 42%),
            var(--lca-panel);
        box-shadow: var(--lca-shadow);
    }
    .lca-top::after {
        content: "";
        position: absolute;
        width: 240px;
        height: 240px;
        right: -135px;
        top: -150px;
        border: 1px solid rgba(217,183,124,.12);
        border-radius: 50%;
        pointer-events: none;
    }
    .lca-top__copy { position: relative; z-index: 1; min-width: 0; }
    .lca-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 5px;
        color: var(--lca-accent);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .16em;
        text-transform: uppercase;
    }
    .lca-eyebrow__dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--lca-green);
        box-shadow: 0 0 0 4px rgba(82,199,139,.10), 0 0 16px rgba(82,199,139,.26);
    }
    .lca-top h1 {
        margin: 0;
        color: var(--lca-text);
        font-size: clamp(25px, 2.2vw, 34px);
        line-height: 1.07;
        letter-spacing: -.035em;
        font-weight: 760;
    }
    .lca-top p {
        max-width: 690px;
        margin: 6px 0 0;
        color: var(--lca-muted);
        font-size: 11px;
    }

    .lca-presence {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: 32px minmax(0,1fr);
        align-items: center;
        gap: 10px;
        min-width: 260px;
        padding: 10px 12px;
        border: 1px solid var(--lca-line-strong);
        border-radius: 12px;
        background: rgba(8,12,17,.52);
        transition: border-color .18s ease, background .18s ease, transform .18s ease;
    }
    .lca-presence:hover {
        border-color: rgba(217,183,124,.34);
        background: rgba(12,17,24,.82);
        transform: translateY(-1px);
    }
    .lca-presence input {
        appearance: none;
        width: 32px;
        height: 18px;
        margin: 0;
        border: 1px solid var(--lca-line-strong);
        border-radius: 999px;
        background: #252d38;
        position: relative;
        transition: .18s ease;
    }
    .lca-presence input::after {
        content: "";
        position: absolute;
        width: 12px;
        height: 12px;
        left: 2px;
        top: 2px;
        border-radius: 50%;
        background: #89919e;
        transition: .18s ease;
    }
    .lca-presence input:checked { background: rgba(82,199,139,.18); border-color: rgba(82,199,139,.40); }
    .lca-presence input:checked::after { left: 16px; background: var(--lca-green); box-shadow: 0 0 10px rgba(82,199,139,.35); }
    .lca-presence__copy { display: grid; gap: 1px; min-width: 0; }
    .lca-presence__copy strong { color: var(--lca-text-2); font-size: 10.5px; font-weight: 700; }
    .lca-presence__copy small { color: var(--lca-muted-2); font-size: 8.5px; }

    /* ---------- status strip ---------- */
    .lca-stats {
        display: grid;
        grid-template-columns: repeat(3,minmax(0,1fr));
        gap: 8px;
    }
    .lca-stat {
        display: grid;
        grid-template-columns: 34px minmax(0,1fr);
        align-items: center;
        gap: 10px;
        min-width: 0;
        min-height: 54px;
        padding: 9px 11px;
        border: 1px solid var(--lca-line);
        border-radius: 12px;
        background: linear-gradient(180deg, rgba(255,255,255,.018), transparent), var(--lca-panel);
    }
    .lca-stat__icon {
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(217,183,124,.14);
        border-radius: 10px;
        background: var(--lca-accent-soft);
        font-size: 14px;
        filter: saturate(.75);
    }
    .lca-stat__copy { min-width: 0; display: grid; gap: 0; }
    .lca-stat__copy strong { color: var(--lca-text-2); font-size: 10.5px; font-weight: 720; }
    .lca-stat__copy small { color: var(--lca-muted-2); font-size: 8.5px; }

    .lca-error {
        margin: 0;
        padding: 10px 12px;
        border: 1px solid rgba(238,116,116,.30);
        border-radius: 10px;
        background: var(--lca-red-soft);
        color: #ffc3c3;
        font-size: 10px;
    }

    /* ---------- application workspace ---------- */
    .lca-grid {
        display: grid;
        grid-template-columns: minmax(285px, 330px) minmax(0,1fr);
        min-height: 620px;
        height: clamp(620px, 70vh, 840px);
        overflow: hidden;
        border: 1px solid var(--lca-line);
        border-radius: var(--lca-radius);
        background: var(--lca-bg);
        box-shadow: var(--lca-shadow);
    }

    /* inbox */
    .lca-inbox {
        display: grid;
        grid-template-rows: auto minmax(0,1fr) auto;
        min-width: 0;
        min-height: 0;
        border-right: 1px solid var(--lca-line);
        background: #0e131a;
    }
    .lca-inbox__head {
        display: grid;
        gap: 9px;
        padding: 13px;
        border-bottom: 1px solid var(--lca-line);
        background: #10161e;
    }
    .lca-inbox__title { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .lca-inbox__title strong { font-size: 12px; font-weight: 720; }
    .lca-inbox__title span {
        color: var(--lca-muted-2);
        font-size: 8px;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .lca-search { position: relative; display: flex; align-items: center; }
    .lca-search__icon { position: absolute; left: 11px; color: var(--lca-muted-2); pointer-events: none; font-size: 14px; }
    .lca-search input,
    .lca select,
    .lca textarea {
        border: 1px solid var(--lca-line-strong);
        border-radius: 10px;
        background: #090d12;
        color: var(--lca-text);
        transition: border-color .16s ease, background .16s ease, box-shadow .16s ease;
    }
    .lca-search input:focus,
    .lca select:focus,
    .lca textarea:focus {
        border-color: rgba(217,183,124,.42);
        background: #0b1016;
        box-shadow: 0 0 0 3px rgba(217,183,124,.055);
    }
    .lca-search input { width: 100%; height: 37px; padding: 8px 10px 8px 33px; font-size: 10px; }
    .lca-search input::placeholder, .lca textarea::placeholder { color: #5f6875; }

    .lca-filter { display: grid; grid-template-columns: minmax(0,1fr) 37px; gap: 7px; }
    .lca select { width: 100%; height: 37px; padding: 7px 9px; color: var(--lca-text-2); font-size: 9.5px; }
    .lca button {
        border: 1px solid var(--lca-line-strong);
        border-radius: 10px;
        background: #151c25;
        color: var(--lca-text-2);
        transition: border-color .16s ease, background .16s ease, transform .16s ease, color .16s ease;
    }
    .lca button:hover:not(:disabled) {
        border-color: rgba(217,183,124,.30);
        background: #1b2430;
        color: #fff;
    }
    .lca button:active:not(:disabled) { transform: translateY(1px); }
    .lca-icon-button { width: 37px; height: 37px; padding: 0; display: grid; place-items: center; font-size: 14px; }

    .lca-list { min-height: 0; overflow-y: auto; overscroll-behavior: contain; scrollbar-width: thin; scrollbar-color: #303846 transparent; }
    .lca-list-empty { margin: 0; padding: 26px 14px; color: var(--lca-muted); text-align: center; font-size: 9.5px; }
    .lca-item {
        position: relative;
        display: grid !important;
        grid-template-columns: 38px minmax(0,1fr) auto;
        gap: 9px;
        width: 100%;
        min-width: 0;
        padding: 11px 12px !important;
        border: 0 !important;
        border-bottom: 1px solid rgba(255,255,255,.045) !important;
        border-radius: 0 !important;
        background: transparent !important;
        color: var(--lca-text) !important;
        text-align: left;
        box-shadow: none !important;
    }
    .lca-item:hover { background: rgba(255,255,255,.025) !important; }
    .lca-item[aria-pressed="true"] {
        background: linear-gradient(90deg, rgba(217,183,124,.13), rgba(217,183,124,.045)) !important;
        box-shadow: inset 2px 0 0 var(--lca-accent) !important;
    }
    .lca-item__avatar {
        width: 38px; height: 38px; display: grid; place-items: center; overflow: hidden;
        border: 1px solid rgba(255,255,255,.09); border-radius: 12px;
        background: linear-gradient(145deg, #2a3340, #1b222c); color: #fff;
        font-size: 10px; font-weight: 750;
    }
    .lca-item__avatar img { width: 100%; height: 100%; object-fit: cover; }
    .lca-item__body { min-width: 0; }
    .lca-item__name-row { display: flex; align-items: center; gap: 6px; min-width: 0; }
    .lca-item__name-row strong { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 10.5px; font-weight: 700; }
    .lca-kind { flex: 0 0 auto; padding: 2px 5px; border-radius: 999px; background: rgba(255,255,255,.05); color: var(--lca-muted); font-size: 6.8px; letter-spacing: .05em; text-transform: uppercase; }
    .lca-item__meta { display: block; margin-top: 2px; overflow: hidden; color: var(--lca-muted); font-size: 8.5px; text-overflow: ellipsis; white-space: nowrap; }
    .lca-item__time { color: var(--lca-muted-2); font-size: 7.7px; white-space: nowrap; }
    .lca-unread { position: absolute; right: 10px; bottom: 10px; min-width: 18px; height: 18px; display: grid; place-items: center; padding: 0 5px; border-radius: 999px; background: var(--lca-accent); color: #201609; font-size: 7.5px; font-weight: 850; }

    .lca-pages { display: grid; grid-template-columns: 35px minmax(0,1fr) 35px; gap: 7px; align-items: center; padding: 8px; border-top: 1px solid var(--lca-line); background: #10161e; }
    .lca-pages span { color: var(--lca-muted-2); text-align: center; font-size: 8px; }
    .lca-pages button { width: 35px; height: 32px; padding: 0; }

    /* conversation */
    .lca-detail {
        position: relative;
        display: grid;
        grid-template-rows: auto minmax(0,1fr) auto auto;
        min-width: 0;
        min-height: 0;
        background: var(--lca-bg);
    }
    .lca-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        min-width: 0;
        min-height: 66px;
        padding: 11px 15px;
        border-bottom: 1px solid var(--lca-line);
        background: rgba(17,23,31,.96);
        backdrop-filter: blur(12px);
    }
    .lca-heading__identity { display: grid; gap: 1px; min-width: 0; }
    .lca-heading__name-row { display: flex; align-items: center; gap: 7px; min-width: 0; }
    .lca-heading strong { overflow: hidden; font-size: 12px; font-weight: 730; text-overflow: ellipsis; white-space: nowrap; }
    .lca-heading p { margin: 0; color: var(--lca-muted); overflow-wrap: anywhere; font-size: 8.5px; }
    .lca-status-chip { display: inline-flex; align-items: center; gap: 5px; width: fit-content; margin-top: 3px; padding: 3px 7px; border: 1px solid var(--lca-line); border-radius: 999px; background: rgba(255,255,255,.025); color: var(--lca-muted); font-size: 7.5px; }
    .lca-status-chip::before { content: ""; width: 5px; height: 5px; border-radius: 50%; background: var(--lca-green); box-shadow: 0 0 8px rgba(82,199,139,.3); }
    .lca-heading__actions { display: flex; align-items: center; gap: 6px; flex: 0 0 auto; }
    .lca-heading__actions button { min-height: 34px; padding: 7px 9px; font-size: 8.3px; font-weight: 650; }
    .lca-email-handoff { color: #f3d49e !important; border-color: rgba(217,183,124,.24) !important; background: rgba(217,183,124,.07) !important; }
    .lca-email-handoff:hover, .lca-email-handoff[data-active="true"] { border-color: rgba(217,183,124,.42) !important; background: rgba(217,183,124,.14) !important; }
    .lca-close { color: #ffb2b2 !important; border-color: rgba(238,116,116,.23) !important; background: rgba(238,116,116,.06) !important; }
    .lca-close:hover { background: rgba(238,116,116,.13) !important; }

    .lca-log {
        min-width: 0;
        min-height: 0;
        overflow-y: auto;
        padding: 20px 22px;
        scroll-behavior: smooth;
        scrollbar-width: thin;
        scrollbar-color: #303846 transparent;
        overscroll-behavior: contain;
        background:
            radial-gradient(circle at 84% 7%, rgba(217,183,124,.035), transparent 26%),
            linear-gradient(180deg, #0c1016, #0b0f14);
    }
    .lca-empty-chat { height: 100%; min-height: 220px; display: grid; place-items: center; padding: 28px; color: var(--lca-muted); text-align: center; }
    .lca-empty-chat__icon { width: 54px; height: 54px; display: grid; place-items: center; margin: 0 auto 10px; border: 1px solid var(--lca-line-strong); border-radius: 16px; background: linear-gradient(145deg, rgba(217,183,124,.12), rgba(217,183,124,.025)); font-size: 20px; box-shadow: inset 0 1px rgba(255,255,255,.04); }
    .lca-empty-chat strong { display: block; color: var(--lca-text-2); font-size: 12px; font-weight: 700; }
    .lca-empty-chat p { max-width: 330px; margin: 5px auto 0; font-size: 9px; }

    .lca-message {
        width: fit-content;
        max-width: min(700px,76%);
        margin: 0 0 12px;
        padding: 10px 12px;
        border: 1px solid rgba(255,255,255,.065);
        border-radius: 14px 14px 14px 5px;
        background: linear-gradient(180deg, #1b2430, #171e27);
        color: var(--lca-text-2);
        box-shadow: 0 8px 22px rgba(0,0,0,.12);
    }
    .lca-message[data-sender="admin"] {
        margin-left: auto;
        border-color: rgba(217,183,124,.24);
        border-radius: 14px 14px 5px 14px;
        background: linear-gradient(180deg, #e8c88f, #d7ae6d);
        color: #24190d;
    }
    .lca-msg-head { display: flex; align-items: center; gap: 7px; margin-bottom: 5px; }
    .lca-msg-head small { opacity: .7; font-size: 7.5px; font-weight: 780; letter-spacing: .03em; }
    .lca-avatar { width: 25px; height: 25px; flex: 0 0 25px; display: grid; place-items: center; overflow: hidden; border-radius: 8px; background: #2f3946; color: #fff; font-size: 8.5px; font-weight: 800; }
    .lca-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .lca-message p { margin: 2px 0 0; white-space: pre-wrap; overflow-wrap: anywhere; font-size: 11px; line-height: 1.5; }
    .lca-media-image { display: block; max-width: min(360px,100%); max-height: 340px; margin-top: 7px; border-radius: 10px; object-fit: cover; box-shadow: 0 8px 20px rgba(0,0,0,.14); }
    .lca-message audio { display: block; width: min(350px,100%); margin-top: 7px; }
    .lca-file-link { display: inline-flex; align-items: center; gap: 6px; margin-top: 7px; text-decoration: none; font-weight: 650; }
    .lca-file-link:hover { text-decoration: underline; }
    .lca-delete { margin-top: 6px !important; padding: 2px 0 !important; border: 0 !important; background: transparent !important; color: inherit !important; opacity: .5; font-size: 7.5px !important; }
    .lca-delete:hover { opacity: 1; text-decoration: underline; }

    .lca-scroll-down { position: absolute; right: 16px; bottom: 88px; z-index: 5; width: 36px; height: 36px; display: grid; place-items: center; padding: 0 !important; border-radius: 50% !important; box-shadow: 0 10px 26px rgba(0,0,0,.30); }

    /* typing */
    .lca-typing { display: flex; align-items: flex-end; gap: 8px; min-height: 40px; padding: 6px 20px 4px; background: #0c1016; }
    .lca-typing[hidden] { display: none !important; }
    .lca-typing__avatar { width: 25px; height: 25px; flex: 0 0 25px; display: grid; place-items: center; overflow: hidden; border: 1px solid var(--lca-line); border-radius: 8px; background: #2c3541; color: #fff; font-size: 8px; font-weight: 800; }
    .lca-typing__avatar img { width: 100%; height: 100%; object-fit: cover; }
    .lca-typing__bubble { display: flex; align-items: center; gap: 3px; min-height: 32px; padding: 9px 11px; border: 1px solid var(--lca-line); border-radius: 13px 13px 13px 5px; background: #171e27; }
    .lca-typing__dot { width: 5px; height: 5px; border-radius: 50%; background: #9099a5; animation: lcaTypingDot 1.15s infinite ease-in-out; }
    .lca-typing__dot:nth-child(2) { animation-delay: .14s; }
    .lca-typing__dot:nth-child(3) { animation-delay: .28s; }
    .lca-typing__label { align-self: center; color: var(--lca-muted-2); font-size: 7.5px; }
    @keyframes lcaTypingDot { 0%,60%,100%{opacity:.32;transform:translateY(0)} 30%{opacity:1;transform:translateY(-3px)} }

    /* composer */
    .lca-composer-wrap { position: relative; padding: 9px 11px 11px; border-top: 1px solid var(--lca-line); background: #10161e; }
    .lca-composer-info { min-height: 15px; display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 5px; color: var(--lca-muted-2); font-size: 7.7px; }
    .lca-recorder-state { color: var(--lca-accent); font-weight: 700; }
    .lca-form { display: grid; grid-template-columns: 40px 40px minmax(0,1fr) auto; gap: 7px; align-items: end; width: 100%; min-width: 0; }
    .lca-tool { width: 40px; height: 40px; flex: 0 0 40px; display: grid; place-items: center; padding: 0 !important; border-radius: 10px !important; font-size: 15px; user-select: none; }
    .lca-tool[aria-pressed="true"] { border-color: rgba(238,116,116,.38) !important; background: rgba(238,116,116,.15) !important; color: #fff !important; box-shadow: 0 0 0 3px rgba(238,116,116,.06); }
    .lca textarea { width: 100%; min-width: 0; min-height: 40px; max-height: 135px; resize: none; padding: 10px 11px; font-size: 10.5px; line-height: 1.4; }
    .lca-submit { min-width: 88px; height: 40px; padding-inline: 14px !important; border-color: transparent !important; background: linear-gradient(180deg, #edcf9a, #d9b170) !important; color: #21170b !important; font-size: 9px !important; font-weight: 800; box-shadow: 0 8px 20px rgba(217,177,112,.10); }
    .lca-submit:hover:not(:disabled) { background: linear-gradient(180deg, #f3d9aa, #dfba7b) !important; transform: translateY(-1px); }

    /* toasts */
    .lca-toast-stack { position: fixed; right: 18px; bottom: 18px; z-index: 9999; display: grid; gap: 7px; width: min(340px,calc(100vw - 28px)); pointer-events: none; }
    .lca-toast { padding: 10px 12px; border: 1px solid var(--lca-line-strong); border-radius: 10px; background: #171f29; color: var(--lca-text); box-shadow: 0 14px 38px rgba(0,0,0,.34); font-size: 9.5px; }
    .lca-toast[data-kind="success"] { border-color: rgba(82,199,139,.30); }
    .lca-toast[data-kind="error"] { border-color: rgba(238,116,116,.32); color: #ffc5c5; }

    @media (prefers-reduced-motion: reduce) {
        .lca *, .lca *::before, .lca *::after { scroll-behavior: auto !important; transition: none !important; animation: none !important; }
    }
    @media (max-width: 1180px) {
        .lca-grid { grid-template-columns: 280px minmax(0,1fr); }
        .lca-heading__actions button { padding-inline: 8px; }
    }
    @media (max-width: 900px) {
        .lca-top { grid-template-columns: 1fr; align-items: stretch; min-height: 0; }
        .lca-presence { min-width: 0; width: 100%; }
        .lca-grid { grid-template-columns: 1fr; height: auto; min-height: 0; }
        .lca-inbox { max-height: 320px; border-right: 0; border-bottom: 1px solid var(--lca-line); }
        .lca-detail { min-height: 570px; height: 68vh; }
    }
    @media (max-width: 680px) {
        .lca-top { padding: 16px; border-radius: 13px; }
        .lca-top h1 { font-size: 25px; }
        .lca-stats { grid-template-columns: 1fr; }
        .lca-grid { border-radius: 13px; }
        .lca-heading { align-items: flex-start; padding: 10px 11px; }
        .lca-heading__actions { flex-wrap: wrap; justify-content: flex-end; }
        .lca-log { padding: 14px 12px; }
        .lca-message { max-width: 92%; }
        .lca-form { grid-template-columns: 40px 40px minmax(0,1fr); }
        .lca-submit { grid-column: 1 / -1; width: 100%; }
        .lca-composer-info { align-items: flex-start; flex-direction: column; gap: 2px; }
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







        {{-- ================================================================



             Header



             ================================================================ --}}







        <header class="lca-top">



            <div class="lca-top__copy">



                <div class="lca-eyebrow">



                    <span class="lca-eyebrow__dot"></span>



                    Mashal Support Desk



                </div>







                <h1>



                    Live gesprekken



                </h1>







                <p>



                    Beheer bezoekers, berichten, documenten en spraakberichten vanuit één centrale supportomgeving.



                </p>



            </div>







            <label class="lca-presence">



                <input



                    type="checkbox"



                    id="lca-online"



                    checked



                >







                <span class="lca-presence__copy">



                    <strong>



                        Beschikbaar voor live chat



                    </strong>







                    <small>



                        Presence blijft actief zolang dit tabblad zichtbaar is.



                    </small>



                </span>



            </label>



        </header>







        {{-- ================================================================



             Status cards



             ================================================================ --}}







        <div class="lca-stats">



            <div class="lca-stat">



                <div class="lca-stat__icon">



                    💬



                </div>







                <div class="lca-stat__copy">



                    <strong data-inbox-count>



                        —



                    </strong>







                    <small>



                        Gesprekken op huidige pagina



                    </small>



                </div>



            </div>







            <div class="lca-stat">



                <div class="lca-stat__icon">



                    ⚡



                </div>







                <div class="lca-stat__copy">



                    <strong>



                        Realtime



                    </strong>







                    <small>



                        Automatische synchronisatie actief



                    </small>



                </div>



            </div>







            <div class="lca-stat">



                <div class="lca-stat__icon">



                    🎙️



                </div>







                <div class="lca-stat__copy">



                    <strong>



                        Media support



                    </strong>







                    <small>



                        Afbeeldingen, bestanden en voice



                    </small>



                </div>



            </div>



        </div>







        {{-- ================================================================



             Error output



             ================================================================ --}}







        <p



            class="lca-error"



            role="status"



            aria-live="polite"



            hidden



        ></p>







        {{-- ================================================================



             Main workspace



             ================================================================ --}}







        <div class="lca-grid">







            {{-- ============================================================



                 Inbox



                 ============================================================ --}}







            <aside



                class="lca-inbox"



                aria-label="Gesprekken"



            >



                <div class="lca-inbox__head">



                    <div class="lca-inbox__title">



                        <strong>



                            Inbox



                        </strong>







                        <span>



                            Live support



                        </span>



                    </div>







                    <div class="lca-search">



                        <span



                            class="lca-search__icon"



                            aria-hidden="true"



                        >



                            ⌕



                        </span>







                        <input



                            type="search"



                            data-search



                            placeholder="Zoek naam of e-mailadres…"



                            aria-label="Gesprekken zoeken"



                            autocomplete="off"



                        >



                    </div>







                    <div class="lca-filter">



                        <select



                            data-filter



                            aria-label="Gesprekken filteren"



                        >



                            <option value="active">



                                Actieve gesprekken



                            </option>







                            <option value="closed">



                                Afgesloten gesprekken



                            </option>



                        </select>







                        <button



                            type="button"



                            class="lca-icon-button"



                            data-refresh



                            title="Inbox verversen"



                            aria-label="Inbox verversen"



                        >



                            ↻



                        </button>



                    </div>



                </div>







                <div class="lca-list">



                    <p class="lca-list-empty">



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







                    <span data-page>



                        Pagina —



                    </span>







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







            {{-- ============================================================



                 Conversation



                 ============================================================ --}}







            <section



                class="lca-detail"



                aria-label="Geselecteerd gesprek"



            >







                {{-- Header --}}







                <header class="lca-heading">



                    <div class="lca-heading__identity">



                        <div class="lca-heading__name-row">



                            <strong data-name>



                                Kies een gesprek



                            </strong>



                        </div>







                        <p data-email>



                            Selecteer links een gesprek om de berichten te openen.



                        </p>







                        <span



                            class="lca-status-chip"



                            data-status



                        >



                            Geen gesprek geselecteerd



                        </span>



                    </div>







                    <div class="lca-heading__actions">



                        <button



                            type="button"



                            class="lca-email-handoff"



                            data-email-handoff



                            data-active="false"



                            hidden



                        >



                            ✉ Verder via e-mail



                        </button>







                        <button



                            type="button"



                            class="lca-email-handoff"



                            data-email-settings



                            hidden



                            title="E-mailadres, onderwerp en titel beheren"



                        >



                            ✎ E-mail / titel



                        </button>







                        <button



                            type="button"



                            class="lca-close"



                            data-close



                            hidden



                        >



                            Afsluiten



                        </button>



                    </div>



                </header>







                {{-- Messages --}}







                <div



                    class="lca-log"



                    role="log"



                    aria-live="polite"



                    aria-relevant="additions"



                    aria-label="Berichten"



                >



                    <div



                        class="lca-empty-chat"



                        data-empty-chat



                    >



                        <div>



                            <div class="lca-empty-chat__icon">



                                💬



                            </div>







                            <strong>



                                Selecteer een gesprek



                            </strong>







                            <p>



                                Berichten, documenten, afbeeldingen en spraakberichten verschijnen hier.



                            </p>



                        </div>



                    </div>



                </div>







                {{-- Scroll helper --}}







                <button



                    type="button"



                    class="lca-scroll-down"



                    data-scroll-down



                    title="Naar nieuwste bericht"



                    aria-label="Naar nieuwste bericht"



                    hidden



                >



                    ↓



                </button>











                {{-- Typing indicator --}}







                <div



                    class="lca-typing"



                    data-typing-indicator



                    aria-live="polite"



                    aria-label="Bezoeker is aan het typen"



                    hidden



                >



                    <span



                        class="lca-typing__avatar"



                        data-typing-avatar



                        aria-hidden="true"



                    >



                        B



                    </span>







                    <div



                        class="lca-typing__bubble"



                        aria-hidden="true"



                    >



                        <span class="lca-typing__dot"></span>



                        <span class="lca-typing__dot"></span>



                        <span class="lca-typing__dot"></span>



                    </div>







                    <span



                        class="lca-typing__label"



                        data-typing-text



                    >



                        Bezoeker typt…



                    </span>



                </div>







                {{-- Composer --}}







                <div class="lca-composer-wrap">



                    <div class="lca-composer-info">



                        <span data-composer-state>



                            Selecteer een gesprek om te antwoorden.



                        </span>







                        <span



                            class="lca-recorder-state"



                            data-recorder-state



                        ></span>



                    </div>







                    <form



                        class="lca-form"



                        autocomplete="off"



                    >

                        @csrf



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



                            rows="1"



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







        {{-- ================================================================



             Toasts



             ================================================================ --}}







        <div



            class="lca-toast-stack"



            data-toasts



            aria-live="polite"



            aria-atomic="false"



        ></div>



    </div>



</div>









<script src="{{ asset('js/admin-live-chat.js') }}?v=60" defer></script>



@endsection
