@extends('layouts.site-layout')

@section('title', 'Mashal AI | Live Voice')

@section(
    'meta_description',
    'Praat live met Mashal AI in Nederlands, English of Urdu, of gebruik de gewone tekstchat met bestanden.'
)

@push('styles')
<style>
    :root {
        --mashal-bg: #212121;
        --mashal-sidebar: #171717;
        --mashal-sidebar-hover: #2a2a2a;
        --mashal-panel: #2f2f2f;
        --mashal-panel-hover: #3a3a3a;
        --mashal-border: rgba(255,255,255,.10);
        --mashal-text: #ececec;
        --mashal-muted: #a6a6a6;
        --mashal-subtle: #7d7d7d;
        --mashal-user: #303030;
        --mashal-accent: #ffffff;
        --mashal-danger: #ff6b6b;
        --mashal-sidebar-width: 272px;
        --mashal-content-width: 780px;
        --mashal-radius: 18px;
    }

    .mashal-chat-app,
    .mashal-chat-app * {
        box-sizing: border-box;
    }

    .mashal-chat-app {
        position: relative;
        display: flex;
        height: calc(100dvh - 72px);
        min-height: 620px;
        overflow: hidden;
        color: var(--mashal-text);
        background: var(--mashal-bg);
        font-family: inherit;
    }

    /* ---------------------------------------------------------------
       Sidebar
    --------------------------------------------------------------- */

    .chat-sidebar {
        position: relative;
        z-index: 30;
        width: var(--mashal-sidebar-width);
        flex: 0 0 var(--mashal-sidebar-width);
        display: flex;
        flex-direction: column;
        min-height: 0;
        background: var(--mashal-sidebar);
        border-right: 1px solid rgba(255,255,255,.04);
    }

    .chat-sidebar-head {
        padding: 10px 10px 8px;
    }

    .chat-new-button {
        width: 100%;
        min-height: 44px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0 11px;
        border: 0;
        border-radius: 10px;
        color: var(--mashal-text);
        background: transparent;
        font: inherit;
        font-size: 14px;
        font-weight: 600;
        text-align: left;
        cursor: pointer;
        transition: background .16s ease;
    }

    .chat-new-button:hover {
        background: var(--mashal-sidebar-hover);
    }

    .chat-new-button .brand-dot {
        width: 28px;
        height: 28px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 9px;
        background: #fff;
        color: #111;
        font-size: 10px;
        font-weight: 950;
    }

    .chat-new-button svg,
    .chat-sidebar-button svg,
    .chat-topbar-button svg,
    .composer-icon-button svg,
    .chat-row-delete svg,
    .ai-send svg {
        width: 18px;
        height: 18px;
        display: block;
    }

    .chat-search-wrap {
        padding: 2px 10px 8px;
    }

    .chat-search {
        width: 100%;
        min-height: 36px;
        padding: 0 11px;
        border: 1px solid transparent;
        border-radius: 9px;
        outline: 0;
        color: var(--mashal-text);
        background: transparent;
        font: inherit;
        font-size: 12px;
        transition: border-color .16s ease, background .16s ease;
    }

    .chat-search::placeholder {
        color: #777;
    }

    .chat-search:focus {
        border-color: rgba(255,255,255,.10);
        background: rgba(255,255,255,.04);
    }

    .chat-sidebar-label {
        padding: 10px 16px 7px;
        color: #777;
        font-size: 11px;
        font-weight: 700;
    }

    .chat-list {
        min-height: 0;
        flex: 1 1 auto;
        overflow-y: auto;
        padding: 0 8px 12px;
        scrollbar-width: thin;
        scrollbar-color: #3b3b3b transparent;
    }

    .chat-row {
        position: relative;
        margin: 1px 0;
    }

    .chat-row-main {
        width: 100%;
        min-height: 39px;
        display: flex;
        align-items: center;
        padding: 0 34px 0 10px;
        border: 0;
        border-radius: 9px;
        color: #d7d7d7;
        background: transparent;
        font: inherit;
        font-size: 13px;
        text-align: left;
        cursor: pointer;
        transition: background .14s ease;
    }

    .chat-row-main:hover,
    .chat-row.active .chat-row-main {
        background: var(--mashal-sidebar-hover);
    }

    .chat-row-title {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .chat-row-delete {
        position: absolute;
        top: 50%;
        right: 5px;
        width: 28px;
        height: 28px;
        display: grid;
        place-items: center;
        translate: 0 -50%;
        border: 0;
        border-radius: 7px;
        opacity: 0;
        color: #a9a9a9;
        background: transparent;
        cursor: pointer;
        transition: opacity .14s ease, background .14s ease, color .14s ease;
    }

    .chat-row:hover .chat-row-delete,
    .chat-row.active .chat-row-delete {
        opacity: 1;
    }

    .chat-row-delete:hover {
        color: #fff;
        background: #3a3a3a;
    }

    .chat-list-empty {
        padding: 16px 10px;
        color: #747474;
        font-size: 12px;
        line-height: 1.5;
    }

    .chat-sidebar-footer {
        padding: 8px;
        border-top: 1px solid rgba(255,255,255,.05);
    }

    .chat-sidebar-button {
        width: 100%;
        min-height: 42px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0 10px;
        border: 0;
        border-radius: 9px;
        color: #c9c9c9;
        background: transparent;
        font: inherit;
        font-size: 12px;
        text-align: left;
    }

    .chat-sidebar-button strong {
        display: block;
        color: #e6e6e6;
        font-size: 12px;
    }

    .chat-sidebar-button span {
        display: block;
        margin-top: 1px;
        color: #777;
        font-size: 10px;
    }

    .chat-sidebar-overlay {
        display: none;
    }

    /* ---------------------------------------------------------------
       Main chat
    --------------------------------------------------------------- */

    .chat-main {
        min-width: 0;
        min-height: 0;
        flex: 1 1 auto;
        display: grid;
        grid-template-rows: 52px minmax(0,1fr) auto;
        background: var(--mashal-bg);
    }

    .chat-topbar {
        position: relative;
        z-index: 15;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        min-width: 0;
        padding: 0 14px;
        background: rgba(33,33,33,.90);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
    }

    .chat-topbar-left,
    .chat-topbar-right {
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .chat-title-wrap {
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 7px 9px;
        border-radius: 8px;
    }

    .chat-current-title {
        max-width: min(48vw, 440px);
        overflow: hidden;
        color: #f1f1f1;
        font-size: 15px;
        font-weight: 650;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .chat-model-pill {
        flex: 0 0 auto;
        padding: 3px 7px;
        border-radius: 999px;
        color: #8c8c8c;
        background: rgba(255,255,255,.04);
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .02em;
    }

    .chat-topbar-button {
        min-width: 36px;
        height: 36px;
        display: inline-grid;
        place-items: center;
        padding: 0 10px;
        border: 0;
        border-radius: 9px;
        color: #d5d5d5;
        background: transparent;
        font: inherit;
        font-size: 12px;
        cursor: pointer;
        transition: background .14s ease, color .14s ease;
    }

    .chat-topbar-button:hover {
        color: #fff;
        background: rgba(255,255,255,.08);
    }

    .chat-sidebar-toggle {
        display: none;
    }

    .mashal-ai-messages {
        min-height: 0;
        overflow-y: auto;
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
        scroll-behavior: smooth;
        scrollbar-width: thin;
        scrollbar-color: #454545 transparent;
    }

    .chat-scroll-inner {
        width: min(100%, calc(var(--mashal-content-width) + 40px));
        min-height: 100%;
        margin: 0 auto;
        padding: 22px 20px 170px;
    }

    /* ---------------------------------------------------------------
       Welcome screen
    --------------------------------------------------------------- */

    .ai-welcome {
        width: min(100%, 680px);
        margin: min(13vh, 110px) auto 40px;
    }

    .ai-welcome-brand {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 22px;
    }

    .ai-welcome-mark {
        width: 44px;
        height: 44px;
        display: grid;
        place-items: center;
        border-radius: 14px;
        color: #101010;
        background: #fff;
        box-shadow: 0 8px 30px rgba(0,0,0,.18);
        font-size: 12px;
        font-weight: 950;
    }

    .ai-welcome h1 {
        margin: 0;
        color: #f4f4f4;
        font-size: clamp(25px, 3vw, 32px);
        font-weight: 650;
        letter-spacing: -.03em;
        text-align: center;
    }

    .ai-welcome p {
        max-width: 590px;
        margin: 12px auto 0;
        color: #8f8f8f;
        font-size: 13px;
        line-height: 1.65;
        text-align: center;
    }

    .ai-suggestions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0,1fr));
        gap: 8px;
        margin-top: 32px;
    }

    .ai-suggestion {
        min-height: 78px;
        padding: 13px 14px;
        border: 1px solid rgba(255,255,255,.09);
        border-radius: 14px;
        color: #d9d9d9;
        background: transparent;
        font: inherit;
        text-align: left;
        cursor: pointer;
        transition: background .14s ease, border-color .14s ease;
    }

    .ai-suggestion:hover {
        border-color: rgba(255,255,255,.16);
        background: rgba(255,255,255,.035);
    }

    .ai-suggestion strong {
        display: block;
        margin-bottom: 4px;
        color: #ededed;
        font-size: 12px;
    }

    .ai-suggestion span {
        color: #8d8d8d;
        font-size: 11px;
        line-height: 1.45;
    }

    /* ---------------------------------------------------------------
       Messages
    --------------------------------------------------------------- */

    .ai-message {
        width: 100%;
        max-width: 100%;
        margin: 0 auto 30px;
    }

    .ai-message.user {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }

    .ai-message-meta {
        margin: 0 0 7px;
        color: #777;
        font-size: 10px;
        font-weight: 650;
    }

    .ai-message.user .ai-message-meta {
        display: none;
    }

    .ai-message.assistant .ai-message-meta {
        padding-left: 2px;
    }

    .ai-message.assistant .ai-message-meta::before {
        content: "M";
        width: 23px;
        height: 23px;
        display: inline-grid;
        place-items: center;
        margin-right: 8px;
        border-radius: 7px;
        color: #111;
        background: #fff;
        font-size: 9px;
        font-weight: 950;
        vertical-align: middle;
    }

    .ai-message-bubble {
        max-width: 100%;
        color: #ececec;
        font-size: 15px;
        line-height: 1.72;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .ai-message.assistant .ai-message-bubble {
        padding: 0 2px;
    }

    .ai-message.user .ai-message-bubble {
        max-width: min(78%, 620px);
        padding: 10px 15px;
        border-radius: 20px;
        background: var(--mashal-user);
        line-height: 1.55;
    }

    .ai-message.loading .ai-message-bubble {
        color: #9a9a9a;
    }

    .ai-message.loading .ai-message-bubble::after {
        content: "";
        display: inline-block;
        width: 5px;
        height: 5px;
        margin-left: 7px;
        border-radius: 50%;
        background: currentColor;
        animation: ai-thinking 1s infinite alternate;
        vertical-align: middle;
    }

    @keyframes ai-thinking {
        from { opacity: .25; transform: translateY(1px); }
        to { opacity: 1; transform: translateY(-1px); }
    }

    /* ---------------------------------------------------------------
       Composer
    --------------------------------------------------------------- */

    .composer-shell {
        position: relative;
        z-index: 20;
        width: min(100%, calc(var(--mashal-content-width) + 40px));
        margin: 0 auto;
        padding: 0 20px max(13px, env(safe-area-inset-bottom));
        background:
            linear-gradient(
                to bottom,
                rgba(33,33,33,0),
                rgba(33,33,33,.93) 18%,
                var(--mashal-bg) 42%
            );
    }

    .mashal-ai-composer {
        position: relative;
        padding-top: 18px;
    }

    .ai-error {
        display: none;
        margin: 0 6px 8px;
        padding: 8px 11px;
        border: 1px solid rgba(255,107,107,.22);
        border-radius: 10px;
        color: #ffb0b0;
        background: rgba(255,80,80,.07);
        font-size: 11px;
        line-height: 1.5;
    }

    .ai-error.show {
        display: block;
    }

    .ai-input-wrap {
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 26px;
        background: var(--mashal-panel);
        box-shadow:
            0 0 0 1px rgba(0,0,0,.08),
            0 8px 28px rgba(0,0,0,.20);
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .ai-input-wrap:focus-within {
        border-color: rgba(255,255,255,.16);
        box-shadow:
            0 0 0 1px rgba(0,0,0,.08),
            0 10px 34px rgba(0,0,0,.23);
    }

    .ai-input {
        width: 100%;
        min-height: 54px;
        max-height: 190px;
        display: block;
        resize: none;
        padding: 16px 18px 8px;
        border: 0;
        outline: 0;
        color: #f1f1f1;
        background: transparent;
        font: inherit;
        font-size: 15px;
        line-height: 1.45;
        overflow-y: auto;
    }

    .ai-input::placeholder {
        color: #929292;
    }

    .composer-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        min-height: 48px;
        padding: 4px 8px 8px 10px;
    }

    .composer-left,
    .composer-right {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .composer-icon-button,
    .ai-file-button {
        min-width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 10px;
        border: 0;
        border-radius: 999px;
        color: #d4d4d4;
        background: transparent;
        font: inherit;
        font-size: 11px;
        font-weight: 650;
        cursor: pointer;
        transition: background .14s ease, color .14s ease;
    }

    .composer-icon-button:hover,
    .ai-file-button:hover {
        color: #fff;
        background: rgba(255,255,255,.09);
    }

    .composer-icon-button .label {
        display: inline;
    }

    .ai-file-input {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        opacity: 0;
        pointer-events: none;
    }

    .ai-send {
        width: 36px;
        height: 36px;
        display: grid;
        place-items: center;
        border: 0;
        border-radius: 50%;
        color: #151515;
        background: #fff;
        cursor: pointer;
        transition: transform .12s ease, opacity .12s ease;
    }

    .ai-send:hover:not(:disabled) {
        transform: scale(1.04);
    }

    .ai-send:disabled {
        opacity: .32;
        cursor: not-allowed;
    }

    .ai-file-list {
        display: flex;
        gap: 7px;
        overflow-x: auto;
        padding: 0 4px 8px;
        scrollbar-width: none;
    }

    .ai-file-list:empty {
        display: none;
    }

    .ai-file-list::-webkit-scrollbar {
        display: none;
    }

    .ai-file-chip {
        max-width: 250px;
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 7px 8px 7px 11px;
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 12px;
        color: #d7d7d7;
        background: #292929;
        font-size: 11px;
    }

    .ai-file-chip span {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ai-file-chip button {
        width: 22px;
        height: 22px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        border: 0;
        border-radius: 50%;
        color: #aaa;
        background: #3b3b3b;
        cursor: pointer;
    }

    .composer-note {
        padding: 7px 12px 0;
        color: #747474;
        font-size: 10px;
        line-height: 1.35;
        text-align: center;
    }

    /* ---------------------------------------------------------------
       Live Voice
    --------------------------------------------------------------- */

    .live-voice {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: grid;
        grid-template-rows: auto minmax(0,1fr) auto;
        padding:
            max(18px, env(safe-area-inset-top))
            max(18px, env(safe-area-inset-right))
            max(20px, env(safe-area-inset-bottom))
            max(18px, env(safe-area-inset-left));
        color: #f5f5f5;
        background:
            radial-gradient(circle at 50% 40%, rgba(100,110,255,.16), transparent 34rem),
            #0d0d0d;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transform: scale(1.01);
        transition: opacity .18s ease, visibility .18s ease, transform .18s ease;
    }

    .live-voice.open {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        transform: scale(1);
    }

    .live-voice-top,
    .live-voice-bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    .live-voice-title {
        font-size: 14px;
        font-weight: 800;
    }

    .live-voice-title span {
        margin-left: 6px;
        padding: 3px 7px;
        border-radius: 999px;
        color: #111;
        background: #fff;
        font-size: 9px;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .live-voice-center {
        min-height: 0;
        display: grid;
        place-items: center;
    }

    .live-voice-stage {
        width: min(100%, 620px);
        text-align: center;
    }

    .live-orb-wrap {
        width: min(58vw, 310px);
        aspect-ratio: 1;
        display: grid;
        place-items: center;
        margin: 0 auto 32px;
    }

    .live-orb {
        width: 62%;
        aspect-ratio: 1;
        border-radius: 50%;
        background:
            radial-gradient(circle at 33% 28%, #fff 0 4%, #dfe2ff 12%, #939bff 34%, #5b63c5 57%, #282d65 76%, #11152e 100%);
        box-shadow:
            0 0 44px rgba(124,132,255,.24),
            0 0 100px rgba(124,132,255,.14),
            inset -24px -18px 42px rgba(0,0,0,.28);
        transition: transform .16s ease, filter .16s ease;
    }

    .live-voice[data-state="listening"] .live-orb {
        animation: orb-listen 1.9s ease-in-out infinite;
    }

    .live-voice[data-state="thinking"] .live-orb {
        animation: orb-think 1.1s ease-in-out infinite;
        filter: saturate(1.16);
    }

    .live-voice[data-state="speaking"] .live-orb {
        animation: orb-speak .72s ease-in-out infinite alternate;
        filter: brightness(1.12) saturate(1.2);
    }

    @keyframes orb-listen {
        0%,100% { transform: scale(.98); }
        50% { transform: scale(1.04); }
    }

    @keyframes orb-think {
        0%,100% { transform: scale(.96) rotate(-2deg); }
        50% { transform: scale(1.05) rotate(2deg); }
    }

    @keyframes orb-speak {
        from { transform: scale(.98); }
        to { transform: scale(1.10); }
    }

    .live-voice-status strong {
        display: block;
        margin-bottom: 8px;
        font-size: clamp(22px, 4vw, 34px);
        font-weight: 650;
    }

    .live-voice-status span {
        color: #8f8f8f;
        font-size: 13px;
        line-height: 1.5;
    }

    .voice-control {
        min-height: 40px;
        padding: 0 14px;
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 999px;
        color: #eee;
        background: rgba(255,255,255,.06);
        font: inherit;
        font-size: 11px;
        font-weight: 750;
        cursor: pointer;
        touch-action: manipulation;
    }

    .voice-control.round {
        width: 48px;
        height: 48px;
        min-height: 48px;
        padding: 0;
        font-size: 17px;
    }

    .voice-control.danger {
        color: #fff;
        background: #d95454;
        border-color: transparent;
    }

    .voice-language-switch {
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 4px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 999px;
        background: rgba(255,255,255,.04);
    }

    .voice-language-option {
        min-height: 34px;
        padding: 0 12px;
        border: 0;
        border-radius: 999px;
        color: #a7a7a7;
        background: transparent;
        font: inherit;
        font-size: 10px;
        font-weight: 800;
        cursor: pointer;
    }

    .voice-language-option.active {
        color: #151515;
        background: #fff;
    }

    .voice-main-controls {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* ---------------------------------------------------------------
       Responsive
    --------------------------------------------------------------- */

    @media (max-width: 860px) {
        .mashal-chat-app {
            height: calc(100dvh - 64px);
            min-height: 520px;
        }

        .chat-sidebar {
            position: fixed;
            inset: 64px auto 0 0;
            z-index: 100;
            width: min(86vw, 300px);
            transform: translateX(-102%);
            box-shadow: 18px 0 40px rgba(0,0,0,.32);
            transition: transform .20s ease;
        }

        .mashal-chat-app.sidebar-open .chat-sidebar {
            transform: translateX(0);
        }

        .chat-sidebar-overlay {
            position: fixed;
            inset: 64px 0 0;
            z-index: 90;
            display: block;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            background: rgba(0,0,0,.56);
            transition: opacity .18s ease, visibility .18s ease;
        }

        .mashal-chat-app.sidebar-open .chat-sidebar-overlay {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .chat-sidebar-toggle {
            display: inline-grid;
        }

        .chat-current-title {
            max-width: 45vw;
        }

        .chat-model-pill {
            display: none;
        }

        .chat-scroll-inner {
            padding-inline: 14px;
            padding-bottom: 150px;
        }

        .composer-shell {
            padding-inline: 10px;
        }

        .ai-message.user .ai-message-bubble {
            max-width: 88%;
        }

        .ai-welcome {
            margin-top: 8vh;
        }
    }

    @media (max-width: 560px) {
        .chat-topbar {
            padding-inline: 8px;
        }

        .chat-current-title {
            max-width: 46vw;
            font-size: 14px;
        }

        .chat-scroll-inner {
            padding-top: 14px;
        }

        .ai-welcome {
            margin-top: 5vh;
        }

        .ai-welcome h1 {
            font-size: 25px;
        }

        .ai-welcome p {
            font-size: 12px;
        }

        .ai-suggestions {
            grid-template-columns: 1fr;
        }

        .ai-suggestion:nth-child(n+3) {
            display: none;
        }

        .ai-message {
            margin-bottom: 24px;
        }

        .ai-message-bubble {
            font-size: 14px;
        }

        .ai-input {
            font-size: 16px; /* voorkomt iOS auto-zoom */
        }

        .composer-icon-button .label {
            display: none;
        }

        .composer-note {
            font-size: 9px;
        }

        .live-voice {
            padding-inline: 14px;
        }

        .live-voice-bottom {
            flex-direction: column;
            justify-content: center;
        }

        .voice-language-switch {
            width: min(100%, 360px);
            justify-content: center;
        }

        .voice-language-option {
            flex: 1 1 0;
            padding-inline: 8px;
        }
    }
</style>
@endpush

@section('content')
<div class="mashal-chat-app" id="mashal-chat-app">
    <aside class="chat-sidebar" id="chat-sidebar" aria-label="Gesprekken">
        <div class="chat-sidebar-head">
            <button
                type="button"
                class="chat-new-button"
                data-new-chat
            >
                <span class="brand-dot" aria-hidden="true">M</span>
                <span>Nieuwe chat</span>

                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" style="margin-left:auto">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
            </button>
        </div>

        <div class="chat-search-wrap">
            <input
                type="search"
                id="chat-search"
                class="chat-search"
                placeholder="Zoek chats"
                autocomplete="off"
            >
        </div>

        <div class="chat-sidebar-label">Chats</div>

        <div
            class="chat-list"
            id="chat-list"
            aria-live="polite"
        ></div>

        <div class="chat-sidebar-footer">
            <div class="chat-sidebar-button">
                <span class="brand-dot" aria-hidden="true" style="width:28px;height:28px;display:grid;place-items:center;border-radius:8px;background:#fff;color:#111;font-size:9px;font-weight:950">AI</span>

                <div>
                    <strong>Mashal AI</strong>
                    <span>
                        {{ $modelName ?: 'Groq AI' }}
                        ·
                        {{ $voiceModelName ?: 'Whisper' }}
                    </span>
                </div>
            </div>
        </div>
    </aside>

    <button
        type="button"
        class="chat-sidebar-overlay"
        id="chat-sidebar-overlay"
        aria-label="Sluit zijbalk"
    ></button>

    <main class="chat-main">
        <header class="chat-topbar">
            <div class="chat-topbar-left">
                <button
                    type="button"
                    class="chat-topbar-button chat-sidebar-toggle"
                    id="chat-sidebar-toggle"
                    aria-label="Open chats"
                    title="Chats"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <div class="chat-title-wrap">
                    <span
                        id="chat-current-title"
                        class="chat-current-title"
                    >
                        Mashal AI
                    </span>

                    <span class="chat-model-pill">
                        {{ $modelName ?: 'Groq' }}
                    </span>
                </div>
            </div>

            <div class="chat-topbar-right">
                <button
                    type="button"
                    class="chat-topbar-button"
                    data-new-chat
                    aria-label="Nieuwe chat"
                    title="Nieuwe chat"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                </button>

                <button
                    type="button"
                    class="chat-topbar-button"
                    id="ai-clear"
                    aria-label="Verwijder deze chat"
                    title="Verwijder deze chat"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path d="M4 7h16M9 7V4h6v3M8 10v7M12 10v7M16 10v7M6 7l1 14h10l1-14"/>
                    </svg>
                </button>
            </div>
        </header>

        <div
            class="mashal-ai-messages"
            id="ai-messages"
            aria-live="polite"
        >
            <div class="chat-scroll-inner" id="chat-scroll-inner">
                <div class="ai-welcome" id="ai-welcome">
                    <div class="ai-welcome-brand">
                        <div class="ai-welcome-mark" aria-hidden="true">M</div>
                    </div>

                    <h1>Waar kan ik je mee helpen?</h1>

                    <p>
                        Praat met Mashal AI, upload foto's of documenten en laat ze
                        uitleggen, samenvatten of analyseren. Live Voice ondersteunt
                        Nederlands, English en اردو.
                    </p>

                    <div class="ai-suggestions">
                        <button type="button" class="ai-suggestion" data-prompt="Leg dit document duidelijk voor mij uit.">
                            <strong>Document begrijpen</strong>
                            <span>Upload PDF, Word, Excel of PowerPoint en vraag wat het betekent.</span>
                        </button>

                        <button type="button" class="ai-suggestion" data-prompt="Analyseer deze afbeelding en beschrijf alles wat belangrijk is.">
                            <strong>Afbeelding analyseren</strong>
                            <span>Stuur JPG, PNG of WEBP en laat tekst en details uitlezen.</span>
                        </button>

                        <button type="button" class="ai-suggestion" data-prompt="Help me stap voor stap met mijn vraag.">
                            <strong>Stap voor stap helpen</strong>
                            <span>Vraag uitleg, code, planning of praktisch advies.</span>
                        </button>

                        <button type="button" class="ai-suggestion" data-prompt="Vat dit kort en duidelijk samen.">
                            <strong>Samenvatten</strong>
                            <span>Maak lange tekst of documenten snel begrijpelijk.</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="composer-shell">
            <div class="mashal-ai-composer">
                <div
                    id="ai-error"
                    class="ai-error"
                    role="alert"
                ></div>

                <form id="ai-form">
                    <div
                        id="ai-file-list"
                        class="ai-file-list"
                    ></div>

                    <div class="ai-input-wrap">
                        <textarea
                            id="ai-input"
                            class="ai-input"
                            rows="1"
                            maxlength="12000"
                            placeholder="Stuur een bericht naar Mashal AI"
                            autocomplete="off"
                        ></textarea>

                        <div class="composer-bottom">
                            <div class="composer-left">
                                <label
                                    class="ai-file-button"
                                    for="ai-files"
                                    title="Foto of bestand toevoegen"
                                >
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path d="M12 5v14M5 12h14"/>
                                    </svg>

                                    <span class="label">Bestand</span>
                                </label>

                                <input
                                    id="ai-files"
                                    class="ai-file-input"
                                    type="file"
                                    multiple
                                    accept=".pdf,.docx,.xlsx,.pptx,.txt,.md,.csv,.json,.xml,.html,.log,.php,.js,.ts,.css,.sql,.yaml,.yml,.py,.java,.c,.cpp,.cs,.go,.rs,.sh,.ps1,.rb,.swift,.dart,.vue,.svelte,.jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp,application/pdf"
                                >

                                <button
                                    type="button"
                                    class="composer-icon-button"
                                    id="ai-live-start"
                                    @disabled(! $chatConfigured || ! $voiceConfigured)
                                    title="Live praten"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <rect x="9" y="3" width="6" height="12" rx="3"/>
                                        <path d="M5 11a7 7 0 0 0 14 0M12 18v3M9 21h6"/>
                                    </svg>
                                    <span class="label">Live</span>
                                </button>
                            </div>

                            <div class="composer-right">
                                <button
                                    id="ai-send"
                                    class="ai-send"
                                    type="submit"
                                    aria-label="Verstuur"
                                    title="Verstuur"
                                    @disabled(! $chatConfigured)
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" aria-hidden="true">
                                        <path d="M12 19V5M6 11l6-6 6 6"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="composer-note">
                    Mashal AI kan fouten maken. Controleer belangrijke informatie.
                    Max. {{ $maxChatFiles ?? 5 }} bestanden van {{ $maxChatFileMb ?? 10 }} MB.
                </div>
            </div>
        </div>
    </main>
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
        <div
            class="voice-language-switch"
            id="voice-language-switch"
            role="group"
            aria-label="Live Voice taal"
        >
            <button type="button" class="voice-language-option active" data-voice-language="auto">🌐 Auto</button>
            <button type="button" class="voice-language-option" data-voice-language="nl">NL</button>
            <button type="button" class="voice-language-option" data-voice-language="en">EN</button>
            <button type="button" class="voice-language-option" data-voice-language="ur">اردو</button>
        </div>

        <div class="voice-main-controls">
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
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const messageEndpoint = @json(route('ai.chat.message'));
    const voiceEndpoint = @json(route('ai.chat.voice.turn'));
    const ttsEndpoint = @json(route('ai.chat.voice.tts'));
    const csrfToken = @json(csrf_token());
    const chatConfigured = @json((bool) $chatConfigured);
    const voiceConfigured = @json((bool) $voiceConfigured);
    const serverTtsConfigured = @json((bool) ($serverTtsConfigured ?? false));
    const maxFiles = @json((int) ($maxChatFiles ?? 5));
    const maxFileBytes = @json((int) (($maxChatFileMb ?? 10) * 1024 * 1024));

    const conversationsStorageKey = 'mashal-ai-conversations-v1';
    const activeConversationStorageKey = 'mashal-ai-active-conversation-v1';
    const legacyHistoryStorageKey = 'mashal-ai-history-groq-v2';
    const voiceLanguageStorageKey = 'mashal-ai-live-language-v1';

    const form = document.getElementById('ai-form');
    const input = document.getElementById('ai-input');
    const sendButton = document.getElementById('ai-send');
    const messagesViewport = document.getElementById('ai-messages');
    const messages = document.getElementById('chat-scroll-inner');
    const errorBox = document.getElementById('ai-error');
    const clearButton = document.getElementById('ai-clear');
    const liveStartButton = document.getElementById('ai-live-start');
    const fileInput = document.getElementById('ai-files');
    const fileList = document.getElementById('ai-file-list');

    const chatApp = document.getElementById('mashal-chat-app');
    const sidebar = document.getElementById('chat-sidebar');
    const sidebarToggle = document.getElementById('chat-sidebar-toggle');
    const sidebarOverlay = document.getElementById('chat-sidebar-overlay');
    const chatList = document.getElementById('chat-list');
    const chatSearch = document.getElementById('chat-search');
    const chatCurrentTitle = document.getElementById('chat-current-title');
    const newChatButtons = Array.from(
        document.querySelectorAll('[data-new-chat]')
    );

    const liveVoice = document.getElementById('live-voice');
    const voiceTypeButton = document.getElementById('voice-type');
    const voiceCloseButton = document.getElementById('voice-close');
    const voiceMuteButton = document.getElementById('voice-mute');
    const voiceStatusTitle = document.getElementById('voice-status-title');
    const voiceStatusDetail = document.getElementById('voice-status-detail');
    const liveOrb = document.getElementById('live-orb');
    const voiceLanguageButtons = Array.from(
        document.querySelectorAll('[data-voice-language]')
    );

    let conversations = loadConversations();
    let activeConversationId = loadActiveConversationId();

    if (!getConversationById(activeConversationId)) {
        activeConversationId =
            conversations[0]?.id
            || null;
    }

    if (!activeConversationId) {
        const firstConversation =
            makeConversation();

        conversations = [
            firstConversation,
        ];

        activeConversationId =
            firstConversation.id;

        saveConversations();
    }

    let history =
        getActiveConversation()?.messages
        || [];

    let selectedFiles = [];
    let sendingText = false;

    let voiceActive = false;
    let voiceMuted = false;
    let voicePending = false;
    let voiceSpeaking = false;
    let selectedVoiceLanguage = loadVoiceLanguage();
    let speechVoices = [];

    const serverVoiceAudio =
        new Audio();

    serverVoiceAudio.preload =
        'auto';

    serverVoiceAudio.playsInline =
        true;

    let serverVoiceObjectUrl =
        null;

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
    renderConversationList();
    updateCurrentChatTitle();
    refreshSpeechVoices();
    renderVoiceLanguageSwitch();
    bindConversationUi();

    if ('speechSynthesis' in window) {
        window.speechSynthesis.addEventListener?.(
            'voiceschanged',
            refreshSpeechVoices
        );

        window.speechSynthesis.onvoiceschanged =
            refreshSpeechVoices;
    }

    function makeConversation(
        title = 'Nieuwe chat',
        initialMessages = []
    ) {
        const id =
            (
                window.crypto
                && typeof window.crypto.randomUUID === 'function'
            )
                ? window.crypto.randomUUID()
                : (
                    'chat-'
                    + Date.now()
                    + '-'
                    + Math.random()
                        .toString(36)
                        .slice(2, 10)
                );

        const now =
            Date.now();

        return {
            id: id,
            title: String(title || 'Nieuwe chat'),
            createdAt: now,
            updatedAt: now,
            messages: Array.isArray(initialMessages)
                ? initialMessages
                : [],
        };
    }

    function sanitizeMessages(items) {
        if (!Array.isArray(items)) {
            return [];
        }

        return items
            .filter(function (item) {
                return item
                    && (
                        item.role === 'user'
                        || item.role === 'assistant'
                    )
                    && typeof item.content === 'string';
            })
            .map(function (item) {
                return {
                    role: item.role,
                    content: item.content,
                };
            })
            .slice(-100);
    }

    function loadConversations() {
        try {
            const raw =
                localStorage.getItem(
                    conversationsStorageKey
                );

            const parsed =
                raw
                    ? JSON.parse(raw)
                    : null;

            if (Array.isArray(parsed)) {
                const cleaned =
                    parsed
                        .filter(function (chat) {
                            return chat
                                && typeof chat.id === 'string';
                        })
                        .map(function (chat) {
                            return {
                                id: chat.id,
                                title:
                                    String(
                                        chat.title
                                        || 'Nieuwe chat'
                                    ),
                                createdAt:
                                    Number(
                                        chat.createdAt
                                        || Date.now()
                                    ),
                                updatedAt:
                                    Number(
                                        chat.updatedAt
                                        || Date.now()
                                    ),
                                messages:
                                    sanitizeMessages(
                                        chat.messages
                                    ),
                            };
                        })
                        .slice(0, 60);

                if (cleaned.length) {
                    return cleaned;
                }
            }

            /*
             * Migreer automatisch de oude enkele chat zodat de gebruiker
             * niets kwijtraakt bij de nieuwe sidebar-layout.
             */
            const legacy =
                JSON.parse(
                    localStorage.getItem(
                        legacyHistoryStorageKey
                    )
                    || '[]'
                );

            const legacyMessages =
                sanitizeMessages(legacy);

            if (legacyMessages.length) {
                const firstUserMessage =
                    legacyMessages.find(
                        function (item) {
                            return item.role === 'user';
                        }
                    );

                return [
                    makeConversation(
                        conversationTitleFromText(
                            firstUserMessage?.content
                            || 'Bestaande chat'
                        ),
                        legacyMessages
                    ),
                ];
            }
        } catch (error) {
            // Maak hieronder een lege chat.
        }

        return [
            makeConversation(),
        ];
    }

    function loadActiveConversationId() {
        try {
            return (
                localStorage.getItem(
                    activeConversationStorageKey
                )
                || conversations[0]?.id
                || null
            );
        } catch (error) {
            return (
                conversations[0]?.id
                || null
            );
        }
    }

    function getConversationById(id) {
        return conversations.find(
            function (chat) {
                return chat.id === id;
            }
        ) || null;
    }

    function getActiveConversation() {
        return getConversationById(
            activeConversationId
        );
    }

    function saveConversations() {
        try {
            localStorage.setItem(
                conversationsStorageKey,
                JSON.stringify(
                    conversations.slice(0, 60)
                )
            );

            localStorage.setItem(
                activeConversationStorageKey,
                activeConversationId
            );
        } catch (error) {
            // Local storage is optioneel.
        }
    }

    function saveHistory() {
        const chat =
            getActiveConversation();

        if (!chat) {
            return;
        }

        chat.messages =
            sanitizeMessages(history);

        chat.updatedAt =
            Date.now();

        saveConversations();
        renderConversationList(
            chatSearch?.value
            || ''
        );
        updateCurrentChatTitle();
    }

    function pushHistory(role, content) {
        const text =
            String(
                content
                || ''
            ).trim();

        if (!text) {
            return;
        }

        history.push({
            role: role,
            content: text,
        });

        history =
            history.slice(-100);

        const chat =
            getActiveConversation();

        if (
            chat
            && role === 'user'
            && (
                !chat.title
                || chat.title === 'Nieuwe chat'
            )
        ) {
            chat.title =
                conversationTitleFromText(
                    text
                );
        }

        saveHistory();
    }

    function conversationTitleFromText(text) {
        const clean =
            String(text || '')
                .replace(/\s+/g, ' ')
                .trim();

        if (!clean) {
            return 'Nieuwe chat';
        }

        const title =
            clean.slice(0, 44);

        return clean.length > 44
            ? title + '…'
            : title;
    }

    function renderConversationList(
        query = ''
    ) {
        if (!chatList) {
            return;
        }

        const search =
            String(query || '')
                .trim()
                .toLowerCase();

        chatList.replaceChildren();

        const ordered =
            conversations
                .slice()
                .sort(
                    function (a, b) {
                        return (
                            Number(b.updatedAt || 0)
                            - Number(a.updatedAt || 0)
                        );
                    }
                )
                .filter(
                    function (chat) {
                        if (!search) {
                            return true;
                        }

                        return String(
                            chat.title
                            || ''
                        )
                            .toLowerCase()
                            .includes(search);
                    }
                );

        if (!ordered.length) {
            const empty =
                document.createElement('div');

            empty.className =
                'chat-list-empty';

            empty.textContent =
                search
                    ? 'Geen chats gevonden.'
                    : 'Nog geen chats.';

            chatList.appendChild(
                empty
            );

            return;
        }

        ordered.forEach(
            function (chat) {
                const row =
                    document.createElement('div');

                row.className =
                    'chat-row'
                    + (
                        chat.id === activeConversationId
                            ? ' active'
                            : ''
                    );

                const main =
                    document.createElement('button');

                main.type =
                    'button';

                main.className =
                    'chat-row-main';

                main.title =
                    chat.title
                    || 'Nieuwe chat';

                const title =
                    document.createElement('span');

                title.className =
                    'chat-row-title';

                title.textContent =
                    chat.title
                    || 'Nieuwe chat';

                main.appendChild(
                    title
                );

                main.addEventListener(
                    'click',
                    function () {
                        switchConversation(
                            chat.id
                        );
                    }
                );

                const remove =
                    document.createElement('button');

                remove.type =
                    'button';

                remove.className =
                    'chat-row-delete';

                remove.setAttribute(
                    'aria-label',
                    'Verwijder chat'
                );

                remove.title =
                    'Verwijder chat';

                remove.innerHTML =
                    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">'
                    + '<path d="M5 7h14M9 7V4h6v3M8 10v8M12 10v8M16 10v8M7 7l1 14h8l1-14"/>'
                    + '</svg>';

                remove.addEventListener(
                    'click',
                    function (event) {
                        event.stopPropagation();

                        deleteConversation(
                            chat.id
                        );
                    }
                );

                row.append(
                    main,
                    remove
                );

                chatList.appendChild(
                    row
                );
            }
        );
    }

    function updateCurrentChatTitle() {
        if (!chatCurrentTitle) {
            return;
        }

        chatCurrentTitle.textContent =
            getActiveConversation()?.title
            || 'Mashal AI';
    }

    function switchConversation(id) {
        if (
            sendingText
            || voicePending
        ) {
            setError(
                'Wacht tot het huidige antwoord klaar is voordat je van chat wisselt.'
            );

            return;
        }

        const chat =
            getConversationById(id);

        if (!chat) {
            return;
        }

        activeConversationId =
            chat.id;

        history =
            sanitizeMessages(
                chat.messages
            );

        clearSelectedFiles();
        setError('');
        saveConversations();
        renderCurrentConversation();
        renderConversationList(
            chatSearch?.value
            || ''
        );
        updateCurrentChatTitle();
        closeSidebar();

        window.setTimeout(
            function () {
                input?.focus();
            },
            0
        );
    }

    function createNewConversation() {
        if (
            sendingText
            || voicePending
        ) {
            setError(
                'Wacht tot het huidige antwoord klaar is voordat je een nieuwe chat opent.'
            );

            return;
        }

        const current =
            getActiveConversation();

        if (
            current
            && history.length === 0
        ) {
            clearSelectedFiles();
            setError('');
            renderCurrentConversation();
            closeSidebar();
            input?.focus();
            return;
        }

        const chat =
            makeConversation();

        conversations.unshift(
            chat
        );

        activeConversationId =
            chat.id;

        history = [];

        clearSelectedFiles();
        setError('');
        saveConversations();
        renderCurrentConversation();
        renderConversationList(
            chatSearch?.value
            || ''
        );
        updateCurrentChatTitle();
        closeSidebar();

        input?.focus();
    }

    function deleteConversation(id) {
        const chat =
            getConversationById(id);

        if (!chat) {
            return;
        }

        const confirmed =
            window.confirm(
                'Deze chat verwijderen?'
            );

        if (!confirmed) {
            return;
        }

        conversations =
            conversations.filter(
                function (item) {
                    return item.id !== id;
                }
            );

        if (!conversations.length) {
            conversations = [
                makeConversation(),
            ];
        }

        if (activeConversationId === id) {
            activeConversationId =
                conversations[0].id;

            history =
                sanitizeMessages(
                    conversations[0].messages
                );

            clearSelectedFiles();
            setError('');
            renderCurrentConversation();
        }

        saveConversations();
        renderConversationList(
            chatSearch?.value
            || ''
        );
        updateCurrentChatTitle();
    }

    function buildWelcome() {
        const wrapper =
            document.createElement('div');

        wrapper.className =
            'ai-welcome';

        wrapper.id =
            'ai-welcome';

        wrapper.innerHTML =
            '<div class="ai-welcome-brand">'
            + '<div class="ai-welcome-mark" aria-hidden="true">M</div>'
            + '</div>'
            + '<h1>Waar kan ik je mee helpen?</h1>'
            + '<p>Praat met Mashal AI, upload foto\'s of documenten en laat ze uitleggen, samenvatten of analyseren. Live Voice ondersteunt Nederlands, English en اردو.</p>'
            + '<div class="ai-suggestions">'
            + '<button type="button" class="ai-suggestion" data-prompt="Leg dit document duidelijk voor mij uit."><strong>Document begrijpen</strong><span>Upload PDF, Word, Excel of PowerPoint en vraag wat het betekent.</span></button>'
            + '<button type="button" class="ai-suggestion" data-prompt="Analyseer deze afbeelding en beschrijf alles wat belangrijk is."><strong>Afbeelding analyseren</strong><span>Stuur JPG, PNG of WEBP en laat tekst en details uitlezen.</span></button>'
            + '<button type="button" class="ai-suggestion" data-prompt="Help me stap voor stap met mijn vraag."><strong>Stap voor stap helpen</strong><span>Vraag uitleg, code, planning of praktisch advies.</span></button>'
            + '<button type="button" class="ai-suggestion" data-prompt="Vat dit kort en duidelijk samen."><strong>Samenvatten</strong><span>Maak lange tekst of documenten snel begrijpelijk.</span></button>'
            + '</div>';

        return wrapper;
    }

    function renderCurrentConversation() {
        messages.replaceChildren();

        if (!history.length) {
            messages.appendChild(
                buildWelcome()
            );

            messagesViewport.scrollTop = 0;
            return;
        }

        history.forEach(
            function (item) {
                renderMessage(
                    item.role,
                    item.content,
                    false,
                    false
                );
            }
        );

        scrollMessagesToBottom();
    }

    function renderHistory() {
        renderCurrentConversation();
    }

    function bindConversationUi() {
        newChatButtons.forEach(
            function (button) {
                button.addEventListener(
                    'click',
                    createNewConversation
                );
            }
        );

        chatSearch?.addEventListener(
            'input',
            function () {
                renderConversationList(
                    chatSearch.value
                );
            }
        );

        sidebarToggle?.addEventListener(
            'click',
            function () {
                chatApp.classList.toggle(
                    'sidebar-open'
                );
            }
        );

        sidebarOverlay?.addEventListener(
            'click',
            closeSidebar
        );

        messages.addEventListener(
            'click',
            function (event) {
                const suggestion =
                    event.target.closest(
                        '[data-prompt]'
                    );

                if (!suggestion) {
                    return;
                }

                input.value =
                    suggestion.dataset.prompt
                    || '';

                autoResize();
                input.focus();
            }
        );

        window.addEventListener(
            'keydown',
            function (event) {
                if (event.key === 'Escape') {
                    closeSidebar();
                }
            }
        );
    }

    function closeSidebar() {
        chatApp?.classList.remove(
            'sidebar-open'
        );
    }

    function scrollMessagesToBottom() {
        if (!messagesViewport) {
            return;
        }

        messagesViewport.scrollTop =
            messagesViewport.scrollHeight;
    }

    function setError(message = '') {
        errorBox.textContent = message;
        errorBox.classList.toggle(
            'show',
            Boolean(message)
        );
    }


    function renderMessage(role, content, loading = false) {
        messages.querySelector('.ai-welcome')?.remove();

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

        scrollMessagesToBottom();

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

            const visibleUserText =
                text
                || 'Analyseer de toegevoegde bestanden.';

            const visibleFileNames =
                selectedFiles
                    .map(function (file) {
                        return file.name;
                    })
                    .join(', ');

            renderMessage(
                'user',
                visibleFileNames
                    ? (
                        visibleUserText
                        + '\n\n📎 '
                        + visibleFileNames
                    )
                    : visibleUserText
            );

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
                        'Ik heb bestanden of afbeeldingen toegevoegd. Lees en analyseer ze.'
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
            deleteConversation(
                activeConversationId
            );
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

    function loadVoiceLanguage() {
        try {
            const value = String(
                localStorage.getItem(
                    voiceLanguageStorageKey
                ) || 'auto'
            ).toLowerCase();

            return ['auto', 'nl', 'en', 'ur']
                .includes(value)
                    ? value
                    : 'auto';
        } catch (error) {
            return 'auto';
        }
    }

    function saveVoiceLanguage() {
        try {
            localStorage.setItem(
                voiceLanguageStorageKey,
                selectedVoiceLanguage
            );
        } catch (error) {
            // Local storage is optioneel.
        }
    }

    function renderVoiceLanguageSwitch() {
        voiceLanguageButtons.forEach(
            function (button) {
                const active =
                    button.dataset.voiceLanguage
                    === selectedVoiceLanguage;

                button.classList.toggle(
                    'active',
                    active
                );

                button.setAttribute(
                    'aria-pressed',
                    active ? 'true' : 'false'
                );
            }
        );
    }

    function voiceLanguageLocale() {
        return matchVoiceLanguage(
            selectedVoiceLanguage
        );
    }

    function matchVoiceLanguage(language) {
        const value =
            String(language || 'auto')
                .toLowerCase();

        if (value === 'ur') {
            return 'ur-PK';
        }

        if (value === 'en') {
            return 'en-US';
        }

        if (value === 'nl') {
            return 'nl-NL';
        }

        return null;
    }

    function selectedLanguageLabel() {
        return {
            auto: 'Auto',
            nl: 'Nederlands',
            en: 'English',
            ur: 'اردو',
        }[selectedVoiceLanguage] || 'Auto';
    }

    function refreshSpeechVoices() {
        if (!('speechSynthesis' in window)) {
            speechVoices = [];
            return;
        }

        speechVoices =
            window.speechSynthesis.getVoices()
            || [];
    }

    async function unlockVoicePlayback() {
        /*
         * Deze functie draait direct vanuit de klik op "Live praten".
         * Dat is belangrijk voor iPhone/Android autoplay-beperkingen.
         */
        try {
            if (audioContext?.state === 'suspended') {
                await audioContext.resume();
            }
        } catch (error) {
            // AudioContext wordt later opnieuw geprobeerd.
        }

        if (
            'speechSynthesis' in window
            && 'SpeechSynthesisUtterance' in window
        ) {
            try {
                refreshSpeechVoices();

                window.speechSynthesis.cancel();
                window.speechSynthesis.resume();

                const primer =
                    new SpeechSynthesisUtterance('.');

                primer.lang =
                    voiceLanguageLocale()
                    || 'nl-NL';

                primer.volume = 0;
                primer.rate = 10;

                window.speechSynthesis.speak(
                    primer
                );

                window.setTimeout(
                    function () {
                        window.speechSynthesis.cancel();
                    },
                    50
                );
            } catch (error) {
                // Browser-TTS is alleen fallback.
            }
        }

        try {
            /*
             * Korte stille WAV om HTMLAudio in mobiele browsers te unlocken.
             */
            const silentWav =
                'data:audio/wav;base64,UklGRigAAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQQAAACAgICA';

            serverVoiceAudio.src =
                silentWav;

            serverVoiceAudio.volume =
                0;

            await serverVoiceAudio.play();

            serverVoiceAudio.pause();
            serverVoiceAudio.currentTime = 0;
            serverVoiceAudio.volume = 1;
            serverVoiceAudio.removeAttribute('src');
            serverVoiceAudio.load();
        } catch (error) {
            /*
             * Niet fataal. Sommige browsers accepteren de echte MP3 later
             * alsnog omdat de Live Voice-knop al een gebruikersactie was.
             */
            serverVoiceAudio.volume = 1;
        }
    }

    /* ------------------------------------------------------------------ */
    /* Live voice                                                         */
    /* ------------------------------------------------------------------ */

    voiceLanguageButtons.forEach(
        function (button) {
            button.addEventListener(
                'click',
                function () {
                    const language =
                        String(
                            button.dataset.voiceLanguage
                            || 'auto'
                        ).toLowerCase();

                    if (
                        !['auto', 'nl', 'en', 'ur']
                            .includes(language)
                    ) {
                        return;
                    }

                    selectedVoiceLanguage =
                        language;

                    saveVoiceLanguage();
                    renderVoiceLanguageSwitch();
                    refreshSpeechVoices();

                    cancelVoicePlayback();
                    voiceSpeaking = false;

                    const locale =
                        voiceLanguageLocale()
                        || 'nl-NL';

                    if (voiceActive) {
                        setVoiceState(
                            'listening',
                            voiceStateCopy(
                                locale,
                                'listeningTitle'
                            ),
                            selectedVoiceLanguage === 'auto'
                                ? 'Automatische taalherkenning staat aan.'
                                : 'Taal: ' + selectedLanguageLabel()
                        );
                    }
                }
            );
        }
    );

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

        /*
         * Belangrijk op iPhone/Android: ontgrendel TTS direct vanuit de
         * gebruikersklik, vóór de eerste await/getUserMedia-call.
         */
        await unlockVoicePlayback();

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

            const startLocale =
                voiceLanguageLocale()
                || 'nl-NL';

            setVoiceState(
                'listening',
                voiceStateCopy(
                    startLocale,
                    'listeningTitle'
                ),
                selectedVoiceLanguage === 'auto'
                    ? 'Praat Nederlands, English of اردو. Taal wordt automatisch herkend.'
                    : 'Taal: ' + selectedLanguageLabel()
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

            cancelVoicePlayback();

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

        data.append(
            'language',
            selectedVoiceLanguage
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
                voiceLanguageLocale()
                || detectSpeechLocale(
                    payload.transcript
                    || payload.message
                );

            await speakLiveReply(
                payload.message,
                payload.reply_locale
                    || voiceLocale
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

    async function speakLiveReply(
        text,
        preferredLocale = null
    ) {
        if (!voiceActive) {
            return;
        }

        const cleanText =
            speechText(text);

        if (!cleanText) {
            return;
        }

        const locale =
            normalizeSpeechLocale(
                preferredLocale
                || voiceLanguageLocale()
                || detectSpeechLocale(cleanText)
            );

        cancelVoicePlayback();

        voiceSpeaking = true;
        speakingStartedAt =
            Date.now();

        setVoiceState(
            'speaking',
            voiceStateCopy(
                locale,
                'speakingTitle'
            ),
            serverTtsConfigured
                ? serverVoiceDetail(locale)
                : voiceStateCopy(
                    locale,
                    'speakingDetail'
                )
        );

        let spoken = false;

        if (serverTtsConfigured) {
            spoken =
                await speakWithServerTts(
                    cleanText,
                    locale
                );
        }

        /*
         * Browser-TTS blijft fallback als Azure tijdelijk niet beschikbaar is.
         */
        if (
            !spoken
            && voiceActive
            && voiceSpeaking
        ) {
            spoken =
                await speakWithBrowserTts(
                    cleanText,
                    locale
                );
        }

        voiceSpeaking = false;

        if (
            !spoken
            && voiceActive
        ) {
            setVoiceState(
                'error',
                locale.startsWith('ur')
                    ? 'Urdu-spraak niet beschikbaar'
                    : 'Spraak kon niet worden afgespeeld',
                locale.startsWith('ur')
                    ? (
                        serverTtsConfigured
                            ? 'De server kon de Urdu-audio niet afspelen. Controleer het mediavolume en probeer opnieuw.'
                            : 'Configureer Azure Speech voor betrouwbare Urdu-spraak op telefoon en desktop.'
                    )
                    : 'Controleer je mediavolume en probeer opnieuw.'
            );

            return;
        }

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
    }

    async function speakWithServerTts(
        text,
        locale
    ) {
        try {
            const response =
                await fetch(
                    ttsEndpoint,
                    {
                        method: 'POST',

                        headers: {
                            'X-CSRF-TOKEN':
                                csrfToken,

                            'Accept':
                                'audio/mpeg, application/json',

                            'Content-Type':
                                'application/json',
                        },

                        credentials:
                            'same-origin',

                        body:
                            JSON.stringify({
                                text: text,
                                locale: locale,
                            }),
                    }
                );

            if (!response.ok) {
                return false;
            }

            const contentType =
                String(
                    response.headers.get('content-type')
                    || ''
                ).toLowerCase();

            if (!contentType.includes('audio/')) {
                return false;
            }

            const blob =
                await response.blob();

            if (!blob.size) {
                return false;
            }

            revokeServerVoiceUrl();

            serverVoiceObjectUrl =
                URL.createObjectURL(
                    blob
                );

            serverVoiceAudio.src =
                serverVoiceObjectUrl;

            serverVoiceAudio.volume =
                1;

            serverVoiceAudio.currentTime =
                0;

            return await new Promise(
                function (resolve) {
                    let settled = false;

                    const finish =
                        function (result) {
                            if (settled) {
                                return;
                            }

                            settled = true;

                            serverVoiceAudio.onended =
                                null;

                            serverVoiceAudio.onerror =
                                null;

                            resolve(result);
                        };

                    serverVoiceAudio.onended =
                        function () {
                            finish(true);
                        };

                    serverVoiceAudio.onerror =
                        function () {
                            finish(false);
                        };

                    serverVoiceAudio.play()
                        .catch(
                            function () {
                                finish(false);
                            }
                        );
                }
            );
        } catch (error) {
            return false;
        } finally {
            revokeServerVoiceUrl();
        }
    }

    async function speakWithBrowserTts(
        text,
        locale
    ) {
        if (
            !('speechSynthesis' in window)
            || !('SpeechSynthesisUtterance' in window)
        ) {
            return false;
        }

        refreshSpeechVoices();

        if (
            locale.startsWith('ur')
            && !hasBrowserVoiceForLocale(locale)
        ) {
            return false;
        }

        const chunks =
            splitSpeechText(
                text,
                180
            );

        window.speechSynthesis.cancel();
        window.speechSynthesis.resume();

        for (const chunk of chunks) {
            if (
                !voiceActive
                || !voiceSpeaking
            ) {
                return false;
            }

            const ok =
                await speakBrowserChunk(
                    chunk,
                    locale
                );

            if (!ok) {
                return false;
            }
        }

        return true;
    }

    function speakBrowserChunk(
        text,
        locale
    ) {
        return new Promise(
            function (resolve) {
                const utterance =
                    new SpeechSynthesisUtterance(
                        text
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

                utterance.volume = 1;
                utterance.pitch = 1;

                utterance.rate =
                    locale.toLowerCase()
                        .startsWith('ur')
                            ? 0.94
                            : 1.0;

                let finished = false;

                const finish =
                    function (result) {
                        if (finished) {
                            return;
                        }

                        finished = true;
                        resolve(result);
                    };

                utterance.onend =
                    function () {
                        finish(true);
                    };

                utterance.onerror =
                    function () {
                        finish(false);
                    };

                try {
                    window.speechSynthesis.resume();
                    window.speechSynthesis.speak(
                        utterance
                    );
                } catch (error) {
                    finish(false);
                }
            }
        );
    }

    function hasBrowserVoiceForLocale(
        locale
    ) {
        refreshSpeechVoices();

        const wanted =
            normalizeSpeechLocale(locale)
                .toLowerCase();

        const language =
            wanted.split('-')[0];

        return speechVoices.some(
            function (voice) {
                const lang =
                    String(
                        voice.lang || ''
                    ).toLowerCase();

                const name =
                    String(
                        voice.name || ''
                    ).toLowerCase();

                return (
                    lang === wanted
                    || lang.startsWith(language)
                    || (
                        language === 'ur'
                        && (
                            name.includes('urdu')
                            || name.includes('pakistan')
                        )
                    )
                );
            }
        );
    }

    function serverVoiceDetail(
        locale
    ) {
        if (locale.startsWith('ur')) {
            return 'Urdu server-stem wordt afgespeeld…';
        }

        if (locale.startsWith('en')) {
            return 'Server voice is playing…';
        }

        return 'Serverstem wordt afgespeeld…';
    }

    function cancelVoicePlayback() {
        try {
            window.speechSynthesis?.cancel();
        } catch (error) {
            // Geen actie nodig.
        }

        try {
            serverVoiceAudio.pause();
            serverVoiceAudio.currentTime = 0;
        } catch (error) {
            // Geen actie nodig.
        }

        revokeServerVoiceUrl();
    }

    function revokeServerVoiceUrl() {
        if (!serverVoiceObjectUrl) {
            return;
        }

        try {
            URL.revokeObjectURL(
                serverVoiceObjectUrl
            );
        } catch (error) {
            // Geen actie nodig.
        }

        serverVoiceObjectUrl =
            null;
    }

    function splitSpeechText(
        text,
        maxLength = 180
    ) {
        const value =
            String(text || '')
                .trim();

        if (
            !value
            || value.length <= maxLength
        ) {
            return value ? [value] : [];
        }

        const sentences =
            value.match(/[^.!?؟]+[.!?؟]?/gu)
            || [value];

        const chunks = [];
        let current = '';

        sentences.forEach(
            function (sentence) {
                const next =
                    (current + ' ' + sentence)
                        .trim();

                if (
                    current
                    && next.length > maxLength
                ) {
                    chunks.push(current);
                    current = sentence.trim();
                    return;
                }

                current = next;
            }
        );

        if (current) {
            chunks.push(current);
        }

        return chunks;
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
        refreshSpeechVoices();

        const voices =
            speechVoices;

        if (!voices.length) {
            return null;
        }

        const wanted =
            normalizeSpeechLocale(locale)
                .toLowerCase();

        const language =
            wanted.split('-')[0];

        const exact =
            voices.find(
                function (voice) {
                    return (
                        voice.lang
                        && voice.lang.toLowerCase()
                            === wanted
                    );
                }
            );

        if (exact) {
            return exact;
        }

        const sameLanguage =
            voices.find(
                function (voice) {
                    return (
                        voice.lang
                        && voice.lang.toLowerCase()
                            .startsWith(language)
                    );
                }
            );

        if (sameLanguage) {
            return sameLanguage;
        }

        if (language === 'ur') {
            return voices.find(
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
            ) || null;
        }

        return (
            voices.find(
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

        cancelVoicePlayback();

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
