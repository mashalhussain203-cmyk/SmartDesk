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

    .live-voice.open,
    .live-voice.active {
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


    /* ======================================================================
       MASHAL AI — EXPERT RESPONSIVE / MOBILE APPLICATION LAYER
       ----------------------------------------------------------------------
       Deze laag houdt dezelfde interface en functies beschikbaar op desktop,
       tablet, iPhone en Android. De JavaScript-laag vult dynamische viewport-
       variabelen aan zodat de composer boven het mobiele toetsenbord blijft.
       ====================================================================== */

    :root {
        --mashal-runtime-height: calc(100dvh - 64px);
        --mashal-runtime-top: 0px;
        --mashal-visual-height: 100dvh;
        --mashal-keyboard-height: 0px;
        --mashal-safe-top: env(safe-area-inset-top, 0px);
        --mashal-safe-right: env(safe-area-inset-right, 0px);
        --mashal-safe-bottom: env(safe-area-inset-bottom, 0px);
        --mashal-safe-left: env(safe-area-inset-left, 0px);
        --mashal-mobile-gutter: 12px;
        --mashal-touch-target: 44px;
        --mashal-composer-max: 820px;
        --mashal-message-max: 820px;
        --mashal-topbar-height: 52px;
        --mashal-mobile-topbar-height: 50px;
        --mashal-sidebar-mobile-width: min(88vw, 330px);
        --mashal-transition-fast: 120ms;
        --mashal-transition-normal: 200ms;
        --mashal-ease: cubic-bezier(.2,.8,.2,1);
    }

    html.mashal-chat-page {
        overscroll-behavior: none;
        background: var(--mashal-bg);
    }

    body.mashal-chat-page {
        overscroll-behavior: none;
    }

    .mashal-chat-app {
        height: var(--mashal-runtime-height, calc(100dvh - 72px));
        max-height: var(--mashal-runtime-height, calc(100dvh - 72px));
        isolation: isolate;
        contain: layout paint;
    }

    .mashal-chat-app,
    .chat-main,
    .mashal-ai-messages,
    .chat-scroll-inner,
    .composer-shell,
    .mashal-ai-composer,
    .chat-sidebar,
    .chat-list {
        min-width: 0;
        min-height: 0;
    }

    .chat-main {
        height: 100%;
        max-height: 100%;
        overflow: hidden;
    }

    .mashal-ai-messages {
        position: relative;
        min-height: 0;
        scroll-padding-bottom: 180px;
        touch-action: pan-y;
        overflow-anchor: none;
    }

    .chat-scroll-inner {
        width: min(100%, calc(var(--mashal-message-max) + 40px));
        overflow-anchor: none;
    }

    .composer-shell {
        width: min(100%, calc(var(--mashal-composer-max) + 40px));
        transform: translateZ(0);
    }

    .ai-input,
    .chat-search,
    button,
    label,
    input,
    textarea {
        -webkit-tap-highlight-color: transparent;
    }

    button,
    .ai-file-button,
    .chat-new-button,
    .chat-row-main,
    .chat-row-delete,
    .voice-control,
    .voice-language-option {
        touch-action: manipulation;
    }

    .ai-input {
        -webkit-user-select: text;
        user-select: text;
        caret-color: #fff;
    }

    .ai-message-bubble {
        -webkit-user-select: text;
        user-select: text;
    }

    .chat-network-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        min-height: 24px;
        padding: 0 8px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 999px;
        color: #8d8d8d;
        background: rgba(255,255,255,.035);
        font-size: 9px;
        font-weight: 750;
        white-space: nowrap;
    }

    .chat-network-pill::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #68c98b;
        box-shadow: 0 0 0 3px rgba(104,201,139,.10);
    }

    .chat-network-pill.offline {
        color: #e9adad;
        border-color: rgba(255,110,110,.16);
        background: rgba(255,90,90,.06);
    }

    .chat-network-pill.offline::before {
        background: #ef7373;
        box-shadow: 0 0 0 3px rgba(239,115,115,.10);
    }

    .chat-mobile-status {
        display: none;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 24px;
        padding: 3px 10px;
        color: #8b8b8b;
        font-size: 9px;
        line-height: 1.3;
        text-align: center;
    }

    .chat-mobile-status.show {
        display: flex;
    }

    .chat-mobile-status .status-dot {
        width: 5px;
        height: 5px;
        flex: 0 0 auto;
        border-radius: 50%;
        background: currentColor;
    }

    .chat-scroll-bottom {
        position: absolute;
        z-index: 24;
        left: 50%;
        bottom: 118px;
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        translate: -50% 8px;
        border: 1px solid rgba(255,255,255,.13);
        border-radius: 50%;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        color: #e5e5e5;
        background: rgba(47,47,47,.94);
        box-shadow: 0 8px 28px rgba(0,0,0,.26);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        cursor: pointer;
        transition:
            opacity var(--mashal-transition-fast) ease,
            translate var(--mashal-transition-fast) ease,
            visibility var(--mashal-transition-fast) ease,
            background var(--mashal-transition-fast) ease;
    }

    .chat-scroll-bottom.show {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        translate: -50% 0;
    }

    .chat-scroll-bottom:hover {
        background: #3b3b3b;
    }

    .chat-scroll-bottom svg {
        width: 16px;
        height: 16px;
    }

    .ai-message-actions {
        display: flex;
        align-items: center;
        gap: 3px;
        min-height: 28px;
        margin-top: 5px;
        opacity: 0;
        transition: opacity var(--mashal-transition-fast) ease;
    }

    .ai-message:hover .ai-message-actions,
    .ai-message:focus-within .ai-message-actions {
        opacity: 1;
    }

    .ai-message.user .ai-message-actions {
        justify-content: flex-end;
    }

    .ai-message-action {
        min-width: 30px;
        height: 30px;
        display: inline-grid;
        place-items: center;
        padding: 0 8px;
        border: 0;
        border-radius: 8px;
        color: #858585;
        background: transparent;
        font: inherit;
        font-size: 10px;
        cursor: pointer;
    }

    .ai-message-action:hover,
    .ai-message-action:focus-visible {
        color: #e8e8e8;
        background: rgba(255,255,255,.06);
        outline: none;
    }

    .ai-message-action.copied {
        color: #88d7a1;
    }

    .ai-rich-content {
        min-width: 0;
        max-width: 100%;
        color: inherit;
    }

    .ai-rich-content > :first-child {
        margin-top: 0;
    }

    .ai-rich-content > :last-child {
        margin-bottom: 0;
    }

    .ai-rich-content p {
        margin: 0 0 14px;
    }

    .ai-rich-content h1,
    .ai-rich-content h2,
    .ai-rich-content h3,
    .ai-rich-content h4 {
        margin: 24px 0 10px;
        color: #f3f3f3;
        line-height: 1.3;
        letter-spacing: -.02em;
    }

    .ai-rich-content h1 {
        font-size: 1.42em;
    }

    .ai-rich-content h2 {
        font-size: 1.28em;
    }

    .ai-rich-content h3 {
        font-size: 1.15em;
    }

    .ai-rich-content h4 {
        font-size: 1.05em;
    }

    .ai-rich-content ul,
    .ai-rich-content ol {
        margin: 8px 0 15px;
        padding-left: 24px;
    }

    .ai-rich-content li {
        margin: 5px 0;
        padding-left: 2px;
    }

    .ai-rich-content blockquote {
        margin: 14px 0;
        padding: 3px 0 3px 14px;
        border-left: 3px solid rgba(255,255,255,.17);
        color: #bdbdbd;
    }

    .ai-rich-content a {
        color: #b9c8ff;
        text-decoration: underline;
        text-decoration-color: rgba(185,200,255,.40);
        text-underline-offset: 2px;
    }

    .ai-rich-content a:hover {
        color: #d8e0ff;
    }

    .ai-rich-content code {
        padding: .12em .35em;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 6px;
        color: #f0f0f0;
        background: rgba(0,0,0,.22);
        font-family:
            ui-monospace,
            SFMono-Regular,
            Menlo,
            Monaco,
            Consolas,
            'Liberation Mono',
            monospace;
        font-size: .90em;
    }

    .ai-code-block {
        position: relative;
        max-width: 100%;
        margin: 14px 0 18px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.09);
        border-radius: 12px;
        background: #171717;
    }

    .ai-code-head {
        min-height: 36px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 0 9px 0 12px;
        border-bottom: 1px solid rgba(255,255,255,.06);
        color: #848484;
        background: #1d1d1d;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .03em;
        text-transform: uppercase;
    }

    .ai-code-copy {
        min-height: 27px;
        padding: 0 8px;
        border: 0;
        border-radius: 7px;
        color: #aaa;
        background: transparent;
        font: inherit;
        font-size: 9px;
        cursor: pointer;
    }

    .ai-code-copy:hover {
        color: #fff;
        background: rgba(255,255,255,.07);
    }

    .ai-code-block pre {
        max-width: 100%;
        margin: 0;
        padding: 14px 16px;
        overflow: auto;
        overscroll-behavior-x: contain;
        -webkit-overflow-scrolling: touch;
        color: #e7e7e7;
        background: transparent;
        font-family:
            ui-monospace,
            SFMono-Regular,
            Menlo,
            Monaco,
            Consolas,
            'Liberation Mono',
            monospace;
        font-size: 12px;
        line-height: 1.55;
        tab-size: 4;
        white-space: pre;
    }

    .ai-code-block pre code {
        padding: 0;
        border: 0;
        border-radius: 0;
        color: inherit;
        background: transparent;
        font: inherit;
    }

    .ai-rich-table-wrap {
        max-width: 100%;
        margin: 14px 0 18px;
        overflow-x: auto;
        border: 1px solid rgba(255,255,255,.09);
        border-radius: 10px;
        -webkit-overflow-scrolling: touch;
    }

    .ai-rich-table {
        width: 100%;
        min-width: 460px;
        border-collapse: collapse;
        font-size: .88em;
    }

    .ai-rich-table th,
    .ai-rich-table td {
        padding: 9px 11px;
        border-right: 1px solid rgba(255,255,255,.07);
        border-bottom: 1px solid rgba(255,255,255,.07);
        text-align: left;
        vertical-align: top;
    }

    .ai-rich-table th {
        color: #f0f0f0;
        background: rgba(255,255,255,.035);
        font-weight: 700;
    }

    .ai-rich-table tr:last-child td {
        border-bottom: 0;
    }

    .ai-rich-table th:last-child,
    .ai-rich-table td:last-child {
        border-right: 0;
    }

    .ai-file-chip {
        position: relative;
        min-height: 42px;
        max-width: 290px;
    }

    .ai-file-chip-preview {
        width: 30px;
        height: 30px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        overflow: hidden;
        border-radius: 7px;
        color: #a4a4a4;
        background: rgba(255,255,255,.055);
        font-size: 9px;
        font-weight: 850;
        text-transform: uppercase;
    }

    .ai-file-chip-preview img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
    }

    .ai-file-chip-copy {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 1px;
    }

    .ai-file-chip-name {
        overflow: hidden;
        color: #ddd;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ai-file-chip-meta {
        color: #7e7e7e;
        font-size: 9px;
    }

    .ai-camera-button {
        display: none;
    }

    .ai-send.is-stopping {
        color: #fff;
        background: #4a4a4a;
    }

    .ai-send.is-stopping svg {
        display: none;
    }

    .ai-send.is-stopping::before {
        content: '';
        width: 10px;
        height: 10px;
        border-radius: 2px;
        background: #fff;
    }

    .mashal-chat-app.keyboard-open .composer-note {
        display: none;
    }

    .mashal-chat-app.keyboard-open .mashal-ai-composer {
        padding-top: 7px;
    }

    .mashal-chat-app.keyboard-open .chat-scroll-inner {
        padding-bottom: 116px;
    }

    .mashal-chat-app.keyboard-open .composer-shell {
        padding-bottom: max(5px, var(--mashal-safe-bottom));
    }

    .mashal-chat-app.sidebar-open {
        touch-action: none;
    }

    .chat-sidebar {
        overscroll-behavior: contain;
    }

    .chat-list {
        overscroll-behavior: contain;
        -webkit-overflow-scrolling: touch;
    }

    .chat-sidebar-drag-handle {
        display: none;
    }

    .live-voice.active,
    .live-voice.open {
        min-height: 100dvh;
        max-height: 100dvh;
        overflow: hidden;
        overscroll-behavior: none;
    }

    .live-voice .voice-control,
    .live-voice .voice-language-option {
        min-height: var(--mashal-touch-target);
    }

    .live-voice .voice-control.round {
        width: 52px;
        height: 52px;
    }

    .live-voice.keyboard-open {
        height: var(--mashal-visual-height);
    }

    .voice-device-hint {
        max-width: 440px;
        margin: 9px auto 0;
        color: #727272;
        font-size: 10px;
        line-height: 1.45;
        text-align: center;
    }

    /* ---------------------------------------------------------------
       Tablet landscape / compact desktop
    --------------------------------------------------------------- */

    @media (max-width: 1100px) {
        :root {
            --mashal-content-width: 760px;
            --mashal-composer-max: 760px;
            --mashal-message-max: 760px;
        }

        .chat-current-title {
            max-width: min(44vw, 360px);
        }
    }

    @media (max-width: 980px) {
        :root {
            --mashal-sidebar-width: 250px;
        }

        .chat-sidebar-button span {
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    }

    /* ---------------------------------------------------------------
       Tablet + phone drawer mode
    --------------------------------------------------------------- */

    @media (max-width: 860px) {
        html.mashal-chat-page,
        body.mashal-chat-page {
            overflow: hidden;
        }

        .mashal-chat-app {
            height: var(--mashal-runtime-height, calc(100dvh - 64px));
            max-height: var(--mashal-runtime-height, calc(100dvh - 64px));
            min-height: 300px;
            overflow: hidden;
        }

        .chat-main {
            grid-template-rows:
                var(--mashal-mobile-topbar-height)
                minmax(0,1fr)
                auto;
        }

        .chat-topbar {
            min-height: var(--mashal-mobile-topbar-height);
            padding:
                0
                max(8px, var(--mashal-safe-right))
                0
                max(8px, var(--mashal-safe-left));
        }

        .chat-topbar-button {
            min-width: var(--mashal-touch-target);
            width: var(--mashal-touch-target);
            height: var(--mashal-touch-target);
            padding: 0;
            border-radius: 12px;
        }

        .chat-sidebar {
            top: var(--mashal-runtime-top, 64px);
            bottom: 0;
            left: 0;
            height: var(--mashal-runtime-height, calc(100dvh - 64px));
            max-height: var(--mashal-runtime-height, calc(100dvh - 64px));
            width: var(--mashal-sidebar-mobile-width);
            padding-left: var(--mashal-safe-left);
            will-change: transform;
        }

        .chat-sidebar-overlay {
            top: var(--mashal-runtime-top, 64px);
            bottom: 0;
            height: var(--mashal-runtime-height, calc(100dvh - 64px));
        }

        .chat-new-button {
            min-height: var(--mashal-touch-target);
        }

        .chat-search {
            min-height: 42px;
            font-size: 16px;
        }

        .chat-row-main {
            min-height: 44px;
            padding-right: 42px;
            font-size: 13px;
        }

        .chat-row-delete {
            width: 36px;
            height: 36px;
            opacity: .78;
        }

        .chat-scroll-inner {
            width: 100%;
            padding:
                16px
                max(14px, var(--mashal-safe-right))
                156px
                max(14px, var(--mashal-safe-left));
        }

        .composer-shell {
            width: 100%;
            padding:
                0
                max(10px, var(--mashal-safe-right))
                max(8px, var(--mashal-safe-bottom))
                max(10px, var(--mashal-safe-left));
        }

        .ai-input-wrap {
            border-radius: 24px;
        }

        .ai-input {
            max-height: min(32dvh, 180px);
            padding-left: 16px;
            padding-right: 16px;
        }

        .composer-bottom {
            min-height: 48px;
            padding-bottom: 7px;
        }

        .composer-icon-button,
        .ai-file-button {
            min-width: 42px;
            height: 42px;
            padding: 0 11px;
        }

        .ai-send {
            width: 42px;
            height: 42px;
        }

        .ai-camera-button {
            display: inline-flex;
        }

        .ai-message-actions {
            opacity: 1;
        }

        .ai-message-action {
            min-width: 34px;
            height: 34px;
        }

        .chat-scroll-bottom {
            bottom: 116px;
            width: 38px;
            height: 38px;
        }

        .live-voice {
            height: var(--mashal-visual-height, 100dvh);
            min-height: var(--mashal-visual-height, 100dvh);
            max-height: var(--mashal-visual-height, 100dvh);
            padding:
                max(14px, var(--mashal-safe-top))
                max(14px, var(--mashal-safe-right))
                max(14px, var(--mashal-safe-bottom))
                max(14px, var(--mashal-safe-left));
        }

        .live-orb-wrap {
            width: min(62vw, 280px);
            margin-bottom: 24px;
        }

        .voice-control {
            min-height: 44px;
        }

        .voice-language-option {
            min-height: 40px;
        }
    }

    /* ---------------------------------------------------------------
       Phones
    --------------------------------------------------------------- */

    @media (max-width: 640px) {
        :root {
            --mashal-mobile-gutter: 10px;
        }

        .chat-title-wrap {
            gap: 4px;
            padding-inline: 4px;
        }

        .chat-current-title {
            max-width: 48vw;
            font-size: 14px;
        }

        .chat-network-pill {
            display: none;
        }

        .chat-topbar-right {
            gap: 2px;
        }

        .ai-welcome {
            width: 100%;
            margin: min(8vh, 60px) auto 28px;
        }

        .ai-welcome-brand {
            margin-bottom: 16px;
        }

        .ai-welcome-mark {
            width: 40px;
            height: 40px;
            border-radius: 12px;
        }

        .ai-welcome h1 {
            font-size: clamp(23px, 7.2vw, 29px);
        }

        .ai-welcome p {
            max-width: 92%;
            font-size: 12px;
            line-height: 1.55;
        }

        .ai-suggestions {
            gap: 7px;
            margin-top: 24px;
        }

        .ai-suggestion {
            min-height: 70px;
            padding: 11px 12px;
            border-radius: 13px;
        }

        .ai-message {
            margin-bottom: 22px;
        }

        .ai-message-bubble {
            font-size: 14px;
            line-height: 1.68;
        }

        .ai-message.user .ai-message-bubble {
            max-width: 91%;
            padding: 9px 13px;
            border-radius: 18px;
        }

        .ai-message.assistant .ai-message-meta::before {
            width: 22px;
            height: 22px;
        }

        .ai-code-block {
            margin-left: -2px;
            margin-right: -2px;
            border-radius: 10px;
        }

        .ai-code-block pre {
            padding: 12px 13px;
            font-size: 11px;
        }

        .ai-file-list {
            margin-inline: -2px;
            padding-bottom: 6px;
        }

        .ai-file-chip {
            max-width: min(78vw, 270px);
        }

        .composer-note {
            padding-top: 5px;
            font-size: 9px;
        }

        .live-voice-top {
            min-height: 48px;
        }

        .live-voice-title {
            font-size: 13px;
        }

        .live-voice-status strong {
            font-size: clamp(22px, 8vw, 30px);
        }

        .live-voice-status span {
            display: block;
            max-width: 90vw;
            margin-inline: auto;
            font-size: 12px;
        }

        .live-voice-bottom {
            gap: 11px;
        }

        .voice-language-switch {
            max-width: 100%;
        }

        .voice-language-option {
            min-width: 0;
            padding-inline: 7px;
        }
    }

    /* ---------------------------------------------------------------
       Small phones
    --------------------------------------------------------------- */

    @media (max-width: 390px) {
        .chat-current-title {
            max-width: 42vw;
            font-size: 13px;
        }

        .chat-topbar-right .chat-topbar-button:last-child {
            display: none;
        }

        .chat-scroll-inner {
            padding-left: max(10px, var(--mashal-safe-left));
            padding-right: max(10px, var(--mashal-safe-right));
        }

        .composer-shell {
            padding-left: max(7px, var(--mashal-safe-left));
            padding-right: max(7px, var(--mashal-safe-right));
        }

        .ai-input {
            padding-top: 13px;
            font-size: 16px;
        }

        .composer-left {
            gap: 1px;
        }

        .composer-icon-button,
        .ai-file-button {
            min-width: 39px;
            width: 39px;
            height: 39px;
            padding: 0;
        }

        .ai-send {
            width: 39px;
            height: 39px;
        }

        .ai-suggestion {
            padding: 10px 11px;
        }

        .ai-suggestion span {
            font-size: 10px;
        }

        .voice-language-option {
            font-size: 9px;
        }
    }

    @media (max-width: 340px) {
        .chat-current-title {
            max-width: 36vw;
        }

        .ai-welcome h1 {
            font-size: 22px;
        }

        .ai-suggestions {
            margin-top: 18px;
        }

        .voice-language-switch {
            gap: 2px;
            padding: 3px;
        }

        .voice-language-option {
            padding-inline: 5px;
        }
    }

    /* ---------------------------------------------------------------
       Short phone screens / keyboards / landscape
    --------------------------------------------------------------- */

    @media (max-height: 700px) and (max-width: 860px) {
        .ai-welcome {
            margin-top: 24px;
        }

        .ai-welcome-brand {
            margin-bottom: 12px;
        }

        .ai-suggestions {
            margin-top: 18px;
        }

        .live-orb-wrap {
            width: min(42vh, 220px);
            margin-bottom: 14px;
        }

        .live-voice-status strong {
            margin-bottom: 5px;
        }
    }

    @media (max-height: 560px) and (max-width: 860px) {
        .ai-welcome-brand {
            display: none;
        }

        .ai-welcome {
            margin-top: 12px;
        }

        .ai-welcome p {
            margin-top: 7px;
        }

        .ai-suggestions {
            grid-template-columns: repeat(2, minmax(0,1fr));
            margin-top: 12px;
        }

        .ai-suggestion {
            min-height: 58px;
        }

        .live-orb-wrap {
            width: min(34vh, 160px);
            margin-bottom: 8px;
        }

        .live-voice-bottom {
            flex-direction: row;
        }
    }

    @media (orientation: landscape) and (max-height: 520px) and (max-width: 1000px) {
        .live-voice {
            grid-template-columns: minmax(0,1fr) auto;
            grid-template-rows: auto minmax(0,1fr);
            gap: 8px 14px;
        }

        .live-voice-top {
            grid-column: 1 / -1;
        }

        .live-voice-center {
            grid-column: 1;
            grid-row: 2;
        }

        .live-voice-bottom {
            grid-column: 2;
            grid-row: 2;
            flex-direction: column;
            align-self: center;
        }

        .voice-language-switch {
            flex-direction: column;
            width: auto;
        }

        .voice-language-option {
            min-width: 74px;
        }

        .live-orb-wrap {
            width: min(42vh, 185px);
        }
    }

    /* ---------------------------------------------------------------
       Input modalities
    --------------------------------------------------------------- */

    @media (hover: none) and (pointer: coarse) {
        .chat-row-delete,
        .ai-message-actions {
            opacity: 1;
        }

        .chat-topbar-button:hover,
        .composer-icon-button:hover,
        .ai-file-button:hover,
        .chat-row-main:hover,
        .chat-new-button:hover,
        .ai-message-action:hover {
            background: initial;
        }

        .chat-topbar-button:active,
        .composer-icon-button:active,
        .ai-file-button:active,
        .chat-row-main:active,
        .chat-new-button:active,
        .ai-message-action:active {
            background: rgba(255,255,255,.08);
        }
    }

    @media (hover: hover) and (pointer: fine) {
        .ai-camera-button {
            display: none;
        }
    }

    /* ---------------------------------------------------------------
       Reduced motion / accessibility
    --------------------------------------------------------------- */

    @media (prefers-reduced-motion: reduce) {
        .mashal-chat-app *,
        .mashal-chat-app *::before,
        .mashal-chat-app *::after,
        .live-voice *,
        .live-voice *::before,
        .live-voice *::after {
            scroll-behavior: auto !important;
            animation-duration: .001ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .001ms !important;
        }
    }

    @media (prefers-contrast: more) {
        .ai-input-wrap,
        .ai-suggestion,
        .ai-code-block,
        .ai-rich-table-wrap,
        .chat-network-pill,
        .voice-language-switch {
            border-color: rgba(255,255,255,.26);
        }

        .chat-current-title,
        .ai-message-bubble,
        .ai-welcome h1 {
            color: #fff;
        }
    }

    @media (forced-colors: active) {
        .ai-send,
        .ai-welcome-mark,
        .brand-dot,
        .ai-message.assistant .ai-message-meta::before,
        .voice-language-option.active {
            forced-color-adjust: auto;
        }
    }

    @supports (-webkit-touch-callout: none) {
        .mashal-ai-messages,
        .chat-list,
        .ai-code-block pre {
            -webkit-overflow-scrolling: touch;
        }

        .ai-input {
            font-size: 16px;
        }

        .composer-shell {
            padding-bottom: max(8px, var(--mashal-safe-bottom));
        }
    }

    @supports not (height: 100dvh) {
        .mashal-chat-app {
            height: var(--mashal-runtime-height, calc(100vh - 64px));
            max-height: var(--mashal-runtime-height, calc(100vh - 64px));
        }

        .live-voice.active,
        .live-voice.open {
            height: var(--mashal-visual-height, 100vh);
            min-height: var(--mashal-visual-height, 100vh);
            max-height: var(--mashal-visual-height, 100vh);
        }
    }


    /* ======================================================================
       RESPONSIVE REGRESSION MATRIX (maintainer documentation)
       ----------------------------------------------------------------------
       De regels hieronder zijn documentatie in het Blade-bestand zodat
       toekomstige wijzigingen gericht op desktop niet ongemerkt mobiel
       breken. Elke case beschrijft een concrete toestand die deze view
       ondersteunt. Geen van deze regels verandert runtime-gedrag.
       ====================================================================== */
       QA 001: iPhone Safari portrait — empty chat welcome.
       QA 002: iPhone Safari portrait — long assistant answer.
       QA 003: iPhone Safari portrait — long user message.
       QA 004: iPhone Safari portrait — code block horizontal scroll.
       QA 005: iPhone Safari portrait — markdown table scroll.
       QA 006: iPhone Safari portrait — one uploaded image.
       QA 007: iPhone Safari portrait — five uploaded files.
       QA 008: iPhone Safari portrait — camera capture.
       QA 009: iPhone Safari portrait — software keyboard open.
       QA 010: iPhone Safari portrait — software keyboard closed.
       QA 011: iPhone Safari portrait — sidebar open.
       QA 012: iPhone Safari portrait — sidebar closed.
       QA 013: iPhone Safari portrait — offline state.
       QA 014: iPhone Safari portrait — online state.
       QA 015: iPhone Safari portrait — live voice listening.
       QA 016: iPhone Safari portrait — live voice thinking.
       QA 017: iPhone Safari portrait — live voice speaking.
       QA 018: iPhone Safari portrait — live voice muted.
       QA 019: iPhone Safari portrait — Urdu RTL content.
       QA 020: iPhone Safari portrait — English content.
       QA 021: iPhone Safari portrait — Dutch content.
       QA 022: iPhone Safari portrait — reduced motion.
       QA 023: iPhone Safari portrait — high contrast.
       QA 024: iPhone Safari portrait — very short viewport.
       QA 025: iPhone Safari landscape — empty chat welcome.
       QA 026: iPhone Safari landscape — long assistant answer.
       QA 027: iPhone Safari landscape — long user message.
       QA 028: iPhone Safari landscape — code block horizontal scroll.
       QA 029: iPhone Safari landscape — markdown table scroll.
       QA 030: iPhone Safari landscape — one uploaded image.
       QA 031: iPhone Safari landscape — five uploaded files.
       QA 032: iPhone Safari landscape — camera capture.
       QA 033: iPhone Safari landscape — software keyboard open.
       QA 034: iPhone Safari landscape — software keyboard closed.
       QA 035: iPhone Safari landscape — sidebar open.
       QA 036: iPhone Safari landscape — sidebar closed.
       QA 037: iPhone Safari landscape — offline state.
       QA 038: iPhone Safari landscape — online state.
       QA 039: iPhone Safari landscape — live voice listening.
       QA 040: iPhone Safari landscape — live voice thinking.
       QA 041: iPhone Safari landscape — live voice speaking.
       QA 042: iPhone Safari landscape — live voice muted.
       QA 043: iPhone Safari landscape — Urdu RTL content.
       QA 044: iPhone Safari landscape — English content.
       QA 045: iPhone Safari landscape — Dutch content.
       QA 046: iPhone Safari landscape — reduced motion.
       QA 047: iPhone Safari landscape — high contrast.
       QA 048: iPhone Safari landscape — very short viewport.
       QA 049: Android Chrome portrait — empty chat welcome.
       QA 050: Android Chrome portrait — long assistant answer.
       QA 051: Android Chrome portrait — long user message.
       QA 052: Android Chrome portrait — code block horizontal scroll.
       QA 053: Android Chrome portrait — markdown table scroll.
       QA 054: Android Chrome portrait — one uploaded image.
       QA 055: Android Chrome portrait — five uploaded files.
       QA 056: Android Chrome portrait — camera capture.
       QA 057: Android Chrome portrait — software keyboard open.
       QA 058: Android Chrome portrait — software keyboard closed.
       QA 059: Android Chrome portrait — sidebar open.
       QA 060: Android Chrome portrait — sidebar closed.
       QA 061: Android Chrome portrait — offline state.
       QA 062: Android Chrome portrait — online state.
       QA 063: Android Chrome portrait — live voice listening.
       QA 064: Android Chrome portrait — live voice thinking.
       QA 065: Android Chrome portrait — live voice speaking.
       QA 066: Android Chrome portrait — live voice muted.
       QA 067: Android Chrome portrait — Urdu RTL content.
       QA 068: Android Chrome portrait — English content.
       QA 069: Android Chrome portrait — Dutch content.
       QA 070: Android Chrome portrait — reduced motion.
       QA 071: Android Chrome portrait — high contrast.
       QA 072: Android Chrome portrait — very short viewport.
       QA 073: Android Chrome landscape — empty chat welcome.
       QA 074: Android Chrome landscape — long assistant answer.
       QA 075: Android Chrome landscape — long user message.
       QA 076: Android Chrome landscape — code block horizontal scroll.
       QA 077: Android Chrome landscape — markdown table scroll.
       QA 078: Android Chrome landscape — one uploaded image.
       QA 079: Android Chrome landscape — five uploaded files.
       QA 080: Android Chrome landscape — camera capture.
       QA 081: Android Chrome landscape — software keyboard open.
       QA 082: Android Chrome landscape — software keyboard closed.
       QA 083: Android Chrome landscape — sidebar open.
       QA 084: Android Chrome landscape — sidebar closed.
       QA 085: Android Chrome landscape — offline state.
       QA 086: Android Chrome landscape — online state.
       QA 087: Android Chrome landscape — live voice listening.
       QA 088: Android Chrome landscape — live voice thinking.
       QA 089: Android Chrome landscape — live voice speaking.
       QA 090: Android Chrome landscape — live voice muted.
       QA 091: Android Chrome landscape — Urdu RTL content.
       QA 092: Android Chrome landscape — English content.
       QA 093: Android Chrome landscape — Dutch content.
       QA 094: Android Chrome landscape — reduced motion.
       QA 095: Android Chrome landscape — high contrast.
       QA 096: Android Chrome landscape — very short viewport.
       QA 097: iPad Safari portrait — empty chat welcome.
       QA 098: iPad Safari portrait — long assistant answer.
       QA 099: iPad Safari portrait — long user message.
       QA 100: iPad Safari portrait — code block horizontal scroll.
       QA 101: iPad Safari portrait — markdown table scroll.
       QA 102: iPad Safari portrait — one uploaded image.
       QA 103: iPad Safari portrait — five uploaded files.
       QA 104: iPad Safari portrait — camera capture.
       QA 105: iPad Safari portrait — software keyboard open.
       QA 106: iPad Safari portrait — software keyboard closed.
       QA 107: iPad Safari portrait — sidebar open.
       QA 108: iPad Safari portrait — sidebar closed.
       QA 109: iPad Safari portrait — offline state.
       QA 110: iPad Safari portrait — online state.
       QA 111: iPad Safari portrait — live voice listening.
       QA 112: iPad Safari portrait — live voice thinking.
       QA 113: iPad Safari portrait — live voice speaking.
       QA 114: iPad Safari portrait — live voice muted.
       QA 115: iPad Safari portrait — Urdu RTL content.
       QA 116: iPad Safari portrait — English content.
       QA 117: iPad Safari portrait — Dutch content.
       QA 118: iPad Safari portrait — reduced motion.
       QA 119: iPad Safari portrait — high contrast.
       QA 120: iPad Safari portrait — very short viewport.
       QA 121: iPad Safari landscape — empty chat welcome.
       QA 122: iPad Safari landscape — long assistant answer.
       QA 123: iPad Safari landscape — long user message.
       QA 124: iPad Safari landscape — code block horizontal scroll.
       QA 125: iPad Safari landscape — markdown table scroll.
       QA 126: iPad Safari landscape — one uploaded image.
       QA 127: iPad Safari landscape — five uploaded files.
       QA 128: iPad Safari landscape — camera capture.
       QA 129: iPad Safari landscape — software keyboard open.
       QA 130: iPad Safari landscape — software keyboard closed.
       QA 131: iPad Safari landscape — sidebar open.
       QA 132: iPad Safari landscape — sidebar closed.
       QA 133: iPad Safari landscape — offline state.
       QA 134: iPad Safari landscape — online state.
       QA 135: iPad Safari landscape — live voice listening.
       QA 136: iPad Safari landscape — live voice thinking.
       QA 137: iPad Safari landscape — live voice speaking.
       QA 138: iPad Safari landscape — live voice muted.
       QA 139: iPad Safari landscape — Urdu RTL content.
       QA 140: iPad Safari landscape — English content.
       QA 141: iPad Safari landscape — Dutch content.
       QA 142: iPad Safari landscape — reduced motion.
       QA 143: iPad Safari landscape — high contrast.
       QA 144: iPad Safari landscape — very short viewport.
       QA 145: Android tablet Chrome — empty chat welcome.
       QA 146: Android tablet Chrome — long assistant answer.
       QA 147: Android tablet Chrome — long user message.
       QA 148: Android tablet Chrome — code block horizontal scroll.
       QA 149: Android tablet Chrome — markdown table scroll.
       QA 150: Android tablet Chrome — one uploaded image.
       QA 151: Android tablet Chrome — five uploaded files.
       QA 152: Android tablet Chrome — camera capture.
       QA 153: Android tablet Chrome — software keyboard open.
       QA 154: Android tablet Chrome — software keyboard closed.
       QA 155: Android tablet Chrome — sidebar open.
       QA 156: Android tablet Chrome — sidebar closed.
       QA 157: Android tablet Chrome — offline state.
       QA 158: Android tablet Chrome — online state.
       QA 159: Android tablet Chrome — live voice listening.
       QA 160: Android tablet Chrome — live voice thinking.
       QA 161: Android tablet Chrome — live voice speaking.
       QA 162: Android tablet Chrome — live voice muted.
       QA 163: Android tablet Chrome — Urdu RTL content.
       QA 164: Android tablet Chrome — English content.
       QA 165: Android tablet Chrome — Dutch content.
       QA 166: Android tablet Chrome — reduced motion.
       QA 167: Android tablet Chrome — high contrast.
       QA 168: Android tablet Chrome — very short viewport.
       QA 169: Windows Chrome touch — empty chat welcome.
       QA 170: Windows Chrome touch — long assistant answer.
       QA 171: Windows Chrome touch — long user message.
       QA 172: Windows Chrome touch — code block horizontal scroll.
       QA 173: Windows Chrome touch — markdown table scroll.
       QA 174: Windows Chrome touch — one uploaded image.
       QA 175: Windows Chrome touch — five uploaded files.
       QA 176: Windows Chrome touch — camera capture.
       QA 177: Windows Chrome touch — software keyboard open.
       QA 178: Windows Chrome touch — software keyboard closed.
       QA 179: Windows Chrome touch — sidebar open.
       QA 180: Windows Chrome touch — sidebar closed.
       QA 181: Windows Chrome touch — offline state.
       QA 182: Windows Chrome touch — online state.
       QA 183: Windows Chrome touch — live voice listening.
       QA 184: Windows Chrome touch — live voice thinking.
       QA 185: Windows Chrome touch — live voice speaking.
       QA 186: Windows Chrome touch — live voice muted.
       QA 187: Windows Chrome touch — Urdu RTL content.
       QA 188: Windows Chrome touch — English content.
       QA 189: Windows Chrome touch — Dutch content.
       QA 190: Windows Chrome touch — reduced motion.
       QA 191: Windows Chrome touch — high contrast.
       QA 192: Windows Chrome touch — very short viewport.
       QA 193: Windows Edge mouse — empty chat welcome.
       QA 194: Windows Edge mouse — long assistant answer.
       QA 195: Windows Edge mouse — long user message.
       QA 196: Windows Edge mouse — code block horizontal scroll.
       QA 197: Windows Edge mouse — markdown table scroll.
       QA 198: Windows Edge mouse — one uploaded image.
       QA 199: Windows Edge mouse — five uploaded files.
       QA 200: Windows Edge mouse — camera capture.
       QA 201: Windows Edge mouse — software keyboard open.
       QA 202: Windows Edge mouse — software keyboard closed.
       QA 203: Windows Edge mouse — sidebar open.
       QA 204: Windows Edge mouse — sidebar closed.
       QA 205: Windows Edge mouse — offline state.
       QA 206: Windows Edge mouse — online state.
       QA 207: Windows Edge mouse — live voice listening.
       QA 208: Windows Edge mouse — live voice thinking.
       QA 209: Windows Edge mouse — live voice speaking.
       QA 210: Windows Edge mouse — live voice muted.
       QA 211: Windows Edge mouse — Urdu RTL content.
       QA 212: Windows Edge mouse — English content.
       QA 213: Windows Edge mouse — Dutch content.
       QA 214: Windows Edge mouse — reduced motion.
       QA 215: Windows Edge mouse — high contrast.
       QA 216: Windows Edge mouse — very short viewport.
       QA 217: macOS Safari — empty chat welcome.
       QA 218: macOS Safari — long assistant answer.
       QA 219: macOS Safari — long user message.
       QA 220: macOS Safari — code block horizontal scroll.
       QA 221: macOS Safari — markdown table scroll.
       QA 222: macOS Safari — one uploaded image.
       QA 223: macOS Safari — five uploaded files.
       QA 224: macOS Safari — camera capture.
       QA 225: macOS Safari — software keyboard open.
       QA 226: macOS Safari — software keyboard closed.
       QA 227: macOS Safari — sidebar open.
       QA 228: macOS Safari — sidebar closed.
       QA 229: macOS Safari — offline state.
       QA 230: macOS Safari — online state.
       QA 231: macOS Safari — live voice listening.
       QA 232: macOS Safari — live voice thinking.
       QA 233: macOS Safari — live voice speaking.
       QA 234: macOS Safari — live voice muted.
       QA 235: macOS Safari — Urdu RTL content.
       QA 236: macOS Safari — English content.
       QA 237: macOS Safari — Dutch content.
       QA 238: macOS Safari — reduced motion.
       QA 239: macOS Safari — high contrast.
       QA 240: macOS Safari — very short viewport.
       QA 241: macOS Chrome — empty chat welcome.
       QA 242: macOS Chrome — long assistant answer.
       QA 243: macOS Chrome — long user message.
       QA 244: macOS Chrome — code block horizontal scroll.
       QA 245: macOS Chrome — markdown table scroll.
       QA 246: macOS Chrome — one uploaded image.
       QA 247: macOS Chrome — five uploaded files.
       QA 248: macOS Chrome — camera capture.
       QA 249: macOS Chrome — software keyboard open.
       QA 250: macOS Chrome — software keyboard closed.
       QA 251: macOS Chrome — sidebar open.
       QA 252: macOS Chrome — sidebar closed.
       QA 253: macOS Chrome — offline state.
       QA 254: macOS Chrome — online state.
       QA 255: macOS Chrome — live voice listening.
       QA 256: macOS Chrome — live voice thinking.
       QA 257: macOS Chrome — live voice speaking.
       QA 258: macOS Chrome — live voice muted.
       QA 259: macOS Chrome — Urdu RTL content.
       QA 260: macOS Chrome — English content.
       QA 261: macOS Chrome — Dutch content.
       QA 262: macOS Chrome — reduced motion.
       QA 263: macOS Chrome — high contrast.
       QA 264: macOS Chrome — very short viewport.
       QA 265: Firefox desktop — empty chat welcome.
       QA 266: Firefox desktop — long assistant answer.
       QA 267: Firefox desktop — long user message.
       QA 268: Firefox desktop — code block horizontal scroll.
       QA 269: Firefox desktop — markdown table scroll.
       QA 270: Firefox desktop — one uploaded image.
       QA 271: Firefox desktop — five uploaded files.
       QA 272: Firefox desktop — camera capture.
       QA 273: Firefox desktop — software keyboard open.
       QA 274: Firefox desktop — software keyboard closed.
       QA 275: Firefox desktop — sidebar open.
       QA 276: Firefox desktop — sidebar closed.
       QA 277: Firefox desktop — offline state.
       QA 278: Firefox desktop — online state.
       QA 279: Firefox desktop — live voice listening.
       QA 280: Firefox desktop — live voice thinking.
       QA 281: Firefox desktop — live voice speaking.
       QA 282: Firefox desktop — live voice muted.
       QA 283: Firefox desktop — Urdu RTL content.
       QA 284: Firefox desktop — English content.
       QA 285: Firefox desktop — Dutch content.
       QA 286: Firefox desktop — reduced motion.
       QA 287: Firefox desktop — high contrast.
       QA 288: Firefox desktop — very short viewport.
       ====================================================================== */

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

                    <span
                        class="chat-network-pill"
                        id="chat-network-pill"
                        aria-live="polite"
                    >
                        Online
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

        <button
            type="button"
            class="chat-scroll-bottom"
            id="chat-scroll-bottom"
            aria-label="Ga naar nieuwste bericht"
            title="Naar beneden"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path d="M6 9l6 6 6-6"/>
            </svg>
        </button>

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

                                <label
                                    class="ai-file-button ai-camera-button"
                                    for="ai-camera"
                                    title="Maak een foto"
                                    aria-label="Maak een foto"
                                >
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path d="M4 8h3l1.5-2h7L17 8h3v11H4z"/>
                                        <circle cx="12" cy="13" r="3.2"/>
                                    </svg>
                                    <span class="label">Camera</span>
                                </label>

                                <input
                                    id="ai-camera"
                                    class="ai-file-input"
                                    type="file"
                                    accept="image/*"
                                    capture="environment"
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

                <div
                    class="chat-mobile-status"
                    id="chat-mobile-status"
                    aria-live="polite"
                >
                    <span class="status-dot" aria-hidden="true"></span>
                    <span id="chat-mobile-status-text"></span>
                </div>

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

                <div class="voice-device-hint" id="voice-device-hint">
                    Op telefoon: houd je mediavolume aan en geef microfoontoegang.
                </div>
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
    const cameraInput = document.getElementById('ai-camera');
    const fileList = document.getElementById('ai-file-list');
    const scrollBottomButton = document.getElementById('chat-scroll-bottom');
    const networkPill = document.getElementById('chat-network-pill');
    const mobileStatus = document.getElementById('chat-mobile-status');
    const mobileStatusText = document.getElementById('chat-mobile-status-text');

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
    const voiceDeviceHint = document.getElementById('voice-device-hint');
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
    let selectedFilePreviewUrls = new Map();
    let sendingText = false;
    let activeTextRequestController = null;
    let wakeLockSentinel = null;
    let lastKnownViewportHeight = 0;
    let keyboardLikelyOpen = false;
    let sidebarTouchStartX = null;
    let sidebarTouchStartY = null;
    let edgeTouchStartX = null;
    let edgeTouchStartY = null;
    let draftSaveTimer = null;
    const draftStorageKey = 'mashal-ai-drafts-v1';

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
    initializeResponsiveRuntime();
    initializeNetworkRuntime();
    initializeScrollRuntime();
    initializeFileRuntime();
    initializeDraftRuntime();
    initializeClipboardAndDropRuntime();
    restoreDraftForActiveConversation();

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

        clearDraftForConversation(id);

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

        window.requestAnimationFrame(
            updateScrollBottomButton
        );
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

        if (
            role === 'assistant'
            && !loading
        ) {
            const rich =
                renderRichAssistantContent(
                    String(content || '')
                );

            bubble.appendChild(rich);
        } else {
            bubble.textContent = content;
        }

        article.append(
            meta,
            bubble
        );

        if (!loading) {
            article.appendChild(
                buildMessageActions(
                    role,
                    String(content || '')
                )
            );
        }

        messages.appendChild(article);

        scrollMessagesToBottom();

        return article;
    }

    function buildMessageActions(
        role,
        content
    ) {
        const actions =
            document.createElement('div');

        actions.className =
            'ai-message-actions';

        const copy =
            document.createElement('button');

        copy.type = 'button';
        copy.className =
            'ai-message-action';
        copy.textContent = 'Kopieer';
        copy.setAttribute(
            'aria-label',
            role === 'assistant'
                ? 'Kopieer antwoord'
                : 'Kopieer bericht'
        );

        copy.addEventListener(
            'click',
            async function () {
                const ok =
                    await copyTextToClipboard(
                        content
                    );

                if (!ok) {
                    setMobileStatus(
                        'Kopiëren lukte niet.',
                        1800
                    );
                    return;
                }

                copy.classList.add(
                    'copied'
                );
                copy.textContent = 'Gekopieerd';

                window.setTimeout(
                    function () {
                        copy.classList.remove(
                            'copied'
                        );
                        copy.textContent =
                            'Kopieer';
                    },
                    1400
                );
            }
        );

        actions.appendChild(copy);

        return actions;
    }

    function renderRichAssistantContent(
        source
    ) {
        const root =
            document.createElement('div');

        root.className =
            'ai-rich-content';

        const lines =
            normalizeMessageLines(source);

        let index = 0;
        let paragraphBuffer = [];

        const flushParagraph =
            function () {
                if (!paragraphBuffer.length) {
                    return;
                }

                const paragraph =
                    document.createElement('p');

                appendInlineMarkdown(
                    paragraph,
                    paragraphBuffer.join(' ')
                );

                root.appendChild(
                    paragraph
                );

                paragraphBuffer = [];
            };

        while (index < lines.length) {
            const line = lines[index];
            const trimmed = line.trim();

            if (trimmed.startsWith('```')) {
                flushParagraph();

                const language =
                    trimmed.slice(3).trim();

                const codeLines = [];
                index += 1;

                while (
                    index < lines.length
                    && !lines[index]
                        .trim()
                        .startsWith('```')
                ) {
                    codeLines.push(
                        lines[index]
                    );
                    index += 1;
                }

                if (
                    index < lines.length
                    && lines[index]
                        .trim()
                        .startsWith('```')
                ) {
                    index += 1;
                }

                root.appendChild(
                    buildCodeBlock(
                        codeLines.join('\n'),
                        language
                    )
                );

                continue;
            }

            if (trimmed === '') {
                flushParagraph();
                index += 1;
                continue;
            }

            const headingMatch =
                trimmed.match(
                    /^(#{1,4})\s+(.+)$/
                );

            if (headingMatch) {
                flushParagraph();

                const level =
                    Math.min(
                        4,
                        headingMatch[1].length
                    );

                const heading =
                    document.createElement(
                        'h' + level
                    );

                appendInlineMarkdown(
                    heading,
                    headingMatch[2]
                );

                root.appendChild(
                    heading
                );

                index += 1;
                continue;
            }

            if (isMarkdownTableStart(lines, index)) {
                flushParagraph();

                const tableResult =
                    buildMarkdownTable(
                        lines,
                        index
                    );

                root.appendChild(
                    tableResult.node
                );

                index =
                    tableResult.nextIndex;

                continue;
            }

            const unordered =
                trimmed.match(
                    /^[-*+]\s+(.+)$/
                );

            const ordered =
                trimmed.match(
                    /^\d+[.)]\s+(.+)$/
                );

            if (unordered || ordered) {
                flushParagraph();

                const listResult =
                    buildMarkdownList(
                        lines,
                        index,
                        Boolean(ordered)
                    );

                root.appendChild(
                    listResult.node
                );

                index =
                    listResult.nextIndex;

                continue;
            }

            if (trimmed.startsWith('> ')) {
                flushParagraph();

                const quoteLines = [];

                while (
                    index < lines.length
                    && lines[index]
                        .trim()
                        .startsWith('>')
                ) {
                    quoteLines.push(
                        lines[index]
                            .trim()
                            .replace(/^>\s?/, '')
                    );

                    index += 1;
                }

                const quote =
                    document.createElement(
                        'blockquote'
                    );

                appendInlineMarkdown(
                    quote,
                    quoteLines.join(' ')
                );

                root.appendChild(quote);
                continue;
            }

            paragraphBuffer.push(
                trimmed
            );
            index += 1;
        }

        flushParagraph();

        if (!root.childNodes.length) {
            root.textContent = source;
        }

        return root;
    }

    function normalizeMessageLines(source) {
        return String(source || '')
            .replace(/\r\n?/g, '\n')
            .split('\n');
    }

    function appendInlineMarkdown(
        parent,
        source
    ) {
        const text =
            String(source || '');

        const tokenPattern =
            /(\*\*[^*]+\*\*|`[^`]+`|\[[^\]]+\]\((?:https?:\/\/)[^)]+\))/g;

        let cursor = 0;
        let match;

        while (
            (match = tokenPattern.exec(text))
            !== null
        ) {
            if (match.index > cursor) {
                parent.appendChild(
                    document.createTextNode(
                        text.slice(
                            cursor,
                            match.index
                        )
                    )
                );
            }

            const token = match[0];

            if (
                token.startsWith('**')
                && token.endsWith('**')
            ) {
                const strong =
                    document.createElement(
                        'strong'
                    );

                strong.textContent =
                    token.slice(2, -2);

                parent.appendChild(strong);
            } else if (
                token.startsWith('`')
                && token.endsWith('`')
            ) {
                const code =
                    document.createElement(
                        'code'
                    );

                code.textContent =
                    token.slice(1, -1);

                parent.appendChild(code);
            } else {
                const linkMatch =
                    token.match(
                        /^\[([^\]]+)\]\((https?:\/\/[^)]+)\)$/
                    );

                if (linkMatch) {
                    const anchor =
                        document.createElement('a');

                    anchor.textContent =
                        linkMatch[1];
                    anchor.href =
                        linkMatch[2];
                    anchor.target = '_blank';
                    anchor.rel =
                        'noopener noreferrer';

                    parent.appendChild(
                        anchor
                    );
                } else {
                    parent.appendChild(
                        document.createTextNode(
                            token
                        )
                    );
                }
            }

            cursor =
                match.index
                + token.length;
        }

        if (cursor < text.length) {
            parent.appendChild(
                document.createTextNode(
                    text.slice(cursor)
                )
            );
        }
    }

    function buildCodeBlock(
        code,
        language
    ) {
        const wrapper =
            document.createElement('div');

        wrapper.className =
            'ai-code-block';

        const head =
            document.createElement('div');

        head.className =
            'ai-code-head';

        const lang =
            document.createElement('span');

        lang.textContent =
            language
                ? language
                : 'code';

        const copy =
            document.createElement('button');

        copy.type = 'button';
        copy.className =
            'ai-code-copy';
        copy.textContent = 'Kopieer';

        copy.addEventListener(
            'click',
            async function () {
                const ok =
                    await copyTextToClipboard(
                        code
                    );

                if (!ok) {
                    return;
                }

                copy.textContent =
                    'Gekopieerd';

                window.setTimeout(
                    function () {
                        copy.textContent =
                            'Kopieer';
                    },
                    1300
                );
            }
        );

        head.append(
            lang,
            copy
        );

        const pre =
            document.createElement('pre');

        const codeNode =
            document.createElement('code');

        codeNode.textContent = code;
        pre.appendChild(codeNode);

        wrapper.append(
            head,
            pre
        );

        return wrapper;
    }

    function buildMarkdownList(
        lines,
        startIndex,
        ordered
    ) {
        const list =
            document.createElement(
                ordered ? 'ol' : 'ul'
            );

        let index = startIndex;

        const pattern =
            ordered
                ? /^\d+[.)]\s+(.+)$/
                : /^[-*+]\s+(.+)$/;

        while (index < lines.length) {
            const match =
                lines[index]
                    .trim()
                    .match(pattern);

            if (!match) {
                break;
            }

            const item =
                document.createElement('li');

            appendInlineMarkdown(
                item,
                match[1]
            );

            list.appendChild(item);
            index += 1;
        }

        return {
            node: list,
            nextIndex: index,
        };
    }

    function isMarkdownTableStart(
        lines,
        index
    ) {
        if (
            index + 1 >= lines.length
        ) {
            return false;
        }

        const first =
            lines[index].trim();
        const separator =
            lines[index + 1].trim();

        if (
            !first.includes('|')
            || !separator.includes('|')
        ) {
            return false;
        }

        const cells =
            splitMarkdownTableRow(
                separator
            );

        return cells.length >= 2
            && cells.every(
                function (cell) {
                    return /^:?-{3,}:?$/.test(
                        cell.trim()
                    );
                }
            );
    }

    function splitMarkdownTableRow(line) {
        let value =
            String(line || '').trim();

        if (value.startsWith('|')) {
            value = value.slice(1);
        }

        if (value.endsWith('|')) {
            value = value.slice(0, -1);
        }

        return value
            .split('|')
            .map(function (cell) {
                return cell.trim();
            });
    }

    function buildMarkdownTable(
        lines,
        startIndex
    ) {
        const headers =
            splitMarkdownTableRow(
                lines[startIndex]
            );

        let index = startIndex + 2;
        const rows = [];

        while (
            index < lines.length
            && lines[index].includes('|')
            && lines[index].trim() !== ''
        ) {
            rows.push(
                splitMarkdownTableRow(
                    lines[index]
                )
            );
            index += 1;
        }

        const wrap =
            document.createElement('div');

        wrap.className =
            'ai-rich-table-wrap';

        const table =
            document.createElement('table');

        table.className =
            'ai-rich-table';

        const thead =
            document.createElement('thead');
        const headRow =
            document.createElement('tr');

        headers.forEach(
            function (cell) {
                const th =
                    document.createElement('th');

                appendInlineMarkdown(
                    th,
                    cell
                );

                headRow.appendChild(th);
            }
        );

        thead.appendChild(headRow);
        table.appendChild(thead);

        const tbody =
            document.createElement('tbody');

        rows.forEach(
            function (row) {
                const tr =
                    document.createElement('tr');

                headers.forEach(
                    function (_, cellIndex) {
                        const td =
                            document.createElement('td');

                        appendInlineMarkdown(
                            td,
                            row[cellIndex] || ''
                        );

                        tr.appendChild(td);
                    }
                );

                tbody.appendChild(tr);
            }
        );

        table.appendChild(tbody);
        wrap.appendChild(table);

        return {
            node: wrap,
            nextIndex: index,
        };
    }

    async function copyTextToClipboard(
        value
    ) {
        const text =
            String(value || '');

        if (!text) {
            return false;
        }

        try {
            if (
                navigator.clipboard
                && window.isSecureContext
            ) {
                await navigator.clipboard
                    .writeText(text);

                return true;
            }
        } catch (error) {
            // Fallback hieronder.
        }

        try {
            const area =
                document.createElement(
                    'textarea'
                );

            area.value = text;
            area.setAttribute(
                'readonly',
                ''
            );
            area.style.position = 'fixed';
            area.style.opacity = '0';
            area.style.pointerEvents = 'none';
            area.style.inset = '0 auto auto 0';

            document.body.appendChild(area);
            area.select();
            area.setSelectionRange(
                0,
                area.value.length
            );

            const ok =
                document.execCommand('copy');

            area.remove();
            return ok;
        } catch (error) {
            return false;
        }
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
            if (event.key !== 'Enter') {
                return;
            }

            const mobileLike =
                isMobileInteractionMode();

            if (
                mobileLike
                && !event.ctrlKey
                && !event.metaKey
            ) {
                /*
                 * Op telefoon is Enter een nieuwe regel. Verzenden gebeurt
                 * met de ronde verzendknop, net als in moderne chat-apps.
                 */
                return;
            }

            if (
                !event.shiftKey
                || event.ctrlKey
                || event.metaKey
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
            activeTextRequestController =
                new AbortController();
            setSendButtonState(true);

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
                        signal:
                            activeTextRequestController.signal,
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

                clearDraftForConversation(
                    activeConversationId
                );

                renderMessage(
                    'assistant',
                    payload.message
                );

                clearSelectedFiles();
            } catch (error) {
                loading?.remove();

                if (error?.name === 'AbortError') {
                    setMobileStatus(
                        'Genereren gestopt.',
                        1500
                    );
                } else {
                    setError(
                        error?.message
                        || 'Er ging iets mis.'
                    );
                }
            } finally {
                sendingText = false;
                activeTextRequestController = null;
                setSendButtonState(false);
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

    cameraInput?.addEventListener(
        'change',
        function () {
            addFiles(
                cameraInput.files
            );

            cameraInput.value = '';
        }
    );

    function addFiles(fileCollection) {
        const files =
            Array.from(
                fileCollection || []
            );

        if (!files.length) {
            return;
        }

        triggerSoftHaptic();

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

            const duplicate =
                selectedFiles.some(
                    function (existing) {
                        return existing.name === file.name
                            && existing.size === file.size
                            && existing.lastModified
                                === file.lastModified;
                    }
                );

            if (duplicate) {
                continue;
            }

            selectedFiles.push(file);
        }

        renderFiles();
    }

    function renderFiles() {
        cleanupUnusedFilePreviewUrls();
        fileList.replaceChildren();

        selectedFiles.forEach(
            function (file, index) {
                const chip =
                    document.createElement('div');

                chip.className =
                    'ai-file-chip';

                const preview =
                    document.createElement('div');

                preview.className =
                    'ai-file-chip-preview';

                if (
                    String(file.type || '')
                        .startsWith('image/')
                ) {
                    const image =
                        document.createElement('img');

                    image.alt = '';
                    image.loading = 'lazy';
                    image.src =
                        getFilePreviewUrl(file);

                    preview.appendChild(image);
                } else {
                    preview.textContent =
                        fileExtensionLabel(file.name);
                }

                const copy =
                    document.createElement('div');

                copy.className =
                    'ai-file-chip-copy';

                const name =
                    document.createElement('span');

                name.className =
                    'ai-file-chip-name';
                name.textContent = file.name;

                const meta =
                    document.createElement('span');

                meta.className =
                    'ai-file-chip-meta';
                meta.textContent =
                    formatFileSize(file.size);

                copy.append(
                    name,
                    meta
                );

                const remove =
                    document.createElement('button');

                remove.type = 'button';
                remove.textContent = '×';
                remove.setAttribute(
                    'aria-label',
                    'Verwijder ' + file.name
                );

                remove.addEventListener(
                    'click',
                    function () {
                        revokeFilePreviewUrl(file);

                        selectedFiles.splice(
                            index,
                            1
                        );

                        renderFiles();
                        announceSelectedFileCount();
                    }
                );

                chip.append(
                    preview,
                    copy,
                    remove
                );

                fileList.appendChild(
                    chip
                );
            }
        );

        announceSelectedFileCount();
    }

    function clearSelectedFiles() {
        selectedFiles.forEach(
            revokeFilePreviewUrl
        );

        selectedFiles = [];
        renderFiles();
    }

    function getFilePreviewUrl(file) {
        if (
            selectedFilePreviewUrls
                .has(file)
        ) {
            return selectedFilePreviewUrls
                .get(file);
        }

        const url =
            URL.createObjectURL(file);

        selectedFilePreviewUrls.set(
            file,
            url
        );

        return url;
    }

    function revokeFilePreviewUrl(file) {
        const url =
            selectedFilePreviewUrls
                .get(file);

        if (!url) {
            return;
        }

        try {
            URL.revokeObjectURL(url);
        } catch (error) {
            // Geen actie nodig.
        }

        selectedFilePreviewUrls.delete(file);
    }

    function cleanupUnusedFilePreviewUrls() {
        selectedFilePreviewUrls.forEach(
            function (url, file) {
                if (!selectedFiles.includes(file)) {
                    try {
                        URL.revokeObjectURL(url);
                    } catch (error) {
                        // Geen actie nodig.
                    }

                    selectedFilePreviewUrls.delete(
                        file
                    );
                }
            }
        );
    }

    function fileExtensionLabel(name) {
        const value =
            String(name || 'file');

        const parts =
            value.split('.');

        if (parts.length < 2) {
            return 'FILE';
        }

        return String(
            parts.pop() || 'FILE'
        )
            .slice(0, 5)
            .toUpperCase();
    }

    function formatFileSize(bytes) {
        const value =
            Number(bytes || 0);

        if (value < 1024) {
            return value + ' B';
        }

        if (value < 1024 * 1024) {
            return (
                value / 1024
            ).toFixed(1) + ' KB';
        }

        return (
            value / 1024 / 1024
        ).toFixed(1) + ' MB';
    }

    function announceSelectedFileCount() {
        if (!selectedFiles.length) {
            setMobileStatus('', 0);
            return;
        }

        setMobileStatus(
            selectedFiles.length
            + (
                selectedFiles.length === 1
                    ? ' bestand klaar om te versturen.'
                    : ' bestanden klaar om te versturen.'
            ),
            0
        );
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
                await requestMicrophoneStream();

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

            await requestVoiceWakeLock();
            updateVoiceDeviceHint();
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
        releaseVoiceWakeLock();

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

        window.setTimeout(
            syncResponsiveViewport,
            50
        );
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

    /* ==================================================================
       Expert responsive runtime
       ================================================================== */

    function initializeResponsiveRuntime() {
        document.documentElement
            .classList.add(
                'mashal-chat-page'
            );

        document.body
            .classList.add(
                'mashal-chat-page'
            );

        syncResponsiveViewport();
        updateInteractionModeClasses();

        window.addEventListener(
            'resize',
            handleResponsiveResize,
            {
                passive: true,
            }
        );

        window.addEventListener(
            'orientationchange',
            function () {
                window.setTimeout(
                    syncResponsiveViewport,
                    80
                );

                window.setTimeout(
                    syncResponsiveViewport,
                    320
                );
            },
            {
                passive: true,
            }
        );

        if (window.visualViewport) {
            window.visualViewport
                .addEventListener(
                    'resize',
                    handleVisualViewportChange,
                    {
                        passive: true,
                    }
                );

            window.visualViewport
                .addEventListener(
                    'scroll',
                    handleVisualViewportChange,
                    {
                        passive: true,
                    }
                );
        }

        input.addEventListener(
            'focus',
            function () {
                window.setTimeout(
                    function () {
                        syncResponsiveViewport();
                        scrollMessagesToBottom();
                    },
                    120
                );

                window.setTimeout(
                    function () {
                        syncResponsiveViewport();
                        scrollMessagesToBottom();
                    },
                    360
                );
            }
        );

        input.addEventListener(
            'blur',
            function () {
                window.setTimeout(
                    syncResponsiveViewport,
                    120
                );
            }
        );

        bindSidebarSwipeGestures();

        document.addEventListener(
            'visibilitychange',
            handleVisibilityChange
        );

        window.addEventListener(
            'pageshow',
            function () {
                syncResponsiveViewport();
                updateNetworkState();
            }
        );
    }

    function handleResponsiveResize() {
        syncResponsiveViewport();
        updateInteractionModeClasses();

        if (
            window.innerWidth > 860
        ) {
            closeSidebar();
        }
    }

    function handleVisualViewportChange() {
        const wasNearBottom =
            isNearMessageBottom(140);

        syncResponsiveViewport();

        if (wasNearBottom) {
            window.requestAnimationFrame(
                scrollMessagesToBottom
            );
        }
    }

    function syncResponsiveViewport() {
        if (!chatApp) {
            return;
        }

        const visual =
            window.visualViewport;

        const viewportHeight =
            visual
                ? visual.height
                : window.innerHeight;

        const viewportTop =
            visual
                ? visual.offsetTop
                : 0;

        const appRect =
            chatApp.getBoundingClientRect();

        let appTop =
            appRect.top;

        if (
            !Number.isFinite(appTop)
            || appTop < 0
        ) {
            appTop = 0;
        }

        const absoluteViewportBottom =
            viewportTop
            + viewportHeight;

        let availableHeight =
            absoluteViewportBottom
            - appTop;

        if (
            !Number.isFinite(availableHeight)
            || availableHeight < 300
        ) {
            availableHeight =
                Math.max(
                    300,
                    viewportHeight
                );
        }

        const layoutHeight =
            window.innerHeight;

        const keyboardHeight =
            Math.max(
                0,
                layoutHeight
                - viewportHeight
                - viewportTop
            );

        const nextKeyboardOpen =
            isMobileInteractionMode()
            && keyboardHeight > 110;

        keyboardLikelyOpen =
            nextKeyboardOpen;

        lastKnownViewportHeight =
            viewportHeight;

        document.documentElement
            .style.setProperty(
                '--mashal-runtime-height',
                Math.round(availableHeight)
                + 'px'
            );

        document.documentElement
            .style.setProperty(
                '--mashal-runtime-top',
                Math.max(
                    0,
                    Math.round(appTop)
                ) + 'px'
            );

        document.documentElement
            .style.setProperty(
                '--mashal-visual-height',
                Math.round(viewportHeight)
                + 'px'
            );

        document.documentElement
            .style.setProperty(
                '--mashal-keyboard-height',
                Math.round(keyboardHeight)
                + 'px'
            );

        chatApp.classList.toggle(
            'keyboard-open',
            nextKeyboardOpen
        );

        liveVoice?.classList.toggle(
            'keyboard-open',
            nextKeyboardOpen
        );
    }

    function updateInteractionModeClasses() {
        const mobile =
            isMobileInteractionMode();

        chatApp?.classList.toggle(
            'mobile-interaction',
            mobile
        );

        document.documentElement
            .classList.toggle(
                'mashal-mobile-interaction',
                mobile
            );
    }

    function isMobileInteractionMode() {
        return (
            window.innerWidth <= 860
            || window.matchMedia(
                '(pointer: coarse)'
            ).matches
        );
    }

    function bindSidebarSwipeGestures() {
        if (!chatApp) {
            return;
        }

        document.addEventListener(
            'touchstart',
            function (event) {
                if (
                    !isMobileInteractionMode()
                    || event.touches.length !== 1
                ) {
                    return;
                }

                const touch =
                    event.touches[0];

                if (
                    chatApp.classList
                        .contains('sidebar-open')
                ) {
                    sidebarTouchStartX =
                        touch.clientX;
                    sidebarTouchStartY =
                        touch.clientY;
                    return;
                }

                if (touch.clientX <= 22) {
                    edgeTouchStartX =
                        touch.clientX;
                    edgeTouchStartY =
                        touch.clientY;
                }
            },
            {
                passive: true,
            }
        );

        document.addEventListener(
            'touchend',
            function (event) {
                if (
                    !isMobileInteractionMode()
                    || !event.changedTouches.length
                ) {
                    resetSidebarTouchState();
                    return;
                }

                const touch =
                    event.changedTouches[0];

                if (
                    sidebarTouchStartX !== null
                ) {
                    const deltaX =
                        touch.clientX
                        - sidebarTouchStartX;

                    const deltaY =
                        Math.abs(
                            touch.clientY
                            - sidebarTouchStartY
                        );

                    if (
                        deltaX < -70
                        && deltaY < 70
                    ) {
                        closeSidebar();
                    }
                }

                if (
                    edgeTouchStartX !== null
                ) {
                    const deltaX =
                        touch.clientX
                        - edgeTouchStartX;

                    const deltaY =
                        Math.abs(
                            touch.clientY
                            - edgeTouchStartY
                        );

                    if (
                        deltaX > 75
                        && deltaY < 70
                    ) {
                        chatApp.classList.add(
                            'sidebar-open'
                        );
                    }
                }

                resetSidebarTouchState();
            },
            {
                passive: true,
            }
        );
    }

    function resetSidebarTouchState() {
        sidebarTouchStartX = null;
        sidebarTouchStartY = null;
        edgeTouchStartX = null;
        edgeTouchStartY = null;
    }

    function initializeNetworkRuntime() {
        updateNetworkState();

        window.addEventListener(
            'online',
            updateNetworkState
        );

        window.addEventListener(
            'offline',
            updateNetworkState
        );
    }

    function updateNetworkState() {
        const online =
            navigator.onLine !== false;

        if (networkPill) {
            networkPill.textContent =
                online
                    ? 'Online'
                    : 'Offline';

            networkPill.classList.toggle(
                'offline',
                !online
            );
        }

        if (!online) {
            setMobileStatus(
                'Geen internetverbinding. Bestaande chats blijven zichtbaar.',
                0
            );
        } else if (
            mobileStatusText?.textContent
                .includes('Geen internetverbinding')
        ) {
            setMobileStatus('', 0);
        }
    }

    function initializeScrollRuntime() {
        messagesViewport?.addEventListener(
            'scroll',
            updateScrollBottomButton,
            {
                passive: true,
            }
        );

        scrollBottomButton?.addEventListener(
            'click',
            function () {
                scrollMessagesToBottom();
                updateScrollBottomButton();
            }
        );

        updateScrollBottomButton();
    }

    function updateScrollBottomButton() {
        if (!scrollBottomButton) {
            return;
        }

        const show =
            !isNearMessageBottom(180)
            && messagesViewport.scrollHeight
                > messagesViewport.clientHeight
                + 220;

        scrollBottomButton
            .classList.toggle(
                'show',
                show
            );
    }

    function isNearMessageBottom(
        tolerance = 120
    ) {
        if (!messagesViewport) {
            return true;
        }

        const remaining =
            messagesViewport.scrollHeight
            - messagesViewport.scrollTop
            - messagesViewport.clientHeight;

        return remaining <= tolerance;
    }

    function initializeFileRuntime() {
        announceSelectedFileCount();
    }

    function initializeClipboardAndDropRuntime() {
        input.addEventListener(
            'paste',
            function (event) {
                const items =
                    Array.from(
                        event.clipboardData?.items
                        || []
                    );

                const imageFiles =
                    items
                        .filter(function (item) {
                            return item.kind === 'file'
                                && String(item.type || '')
                                    .startsWith('image/');
                        })
                        .map(function (item) {
                            return item.getAsFile();
                        })
                        .filter(Boolean);

                if (!imageFiles.length) {
                    return;
                }

                addFiles(imageFiles);
                setMobileStatus(
                    imageFiles.length === 1
                        ? 'Afbeelding uit klembord toegevoegd.'
                        : imageFiles.length
                            + ' afbeeldingen uit klembord toegevoegd.',
                    2200
                );
            }
        );

        chatApp?.addEventListener(
            'dragover',
            function (event) {
                if (
                    !event.dataTransfer?.types
                        ?.includes('Files')
                ) {
                    return;
                }

                event.preventDefault();
                chatApp.classList.add(
                    'dragging-files'
                );
            }
        );

        chatApp?.addEventListener(
            'dragleave',
            function (event) {
                if (
                    event.relatedTarget
                    && chatApp.contains(
                        event.relatedTarget
                    )
                ) {
                    return;
                }

                chatApp.classList.remove(
                    'dragging-files'
                );
            }
        );

        chatApp?.addEventListener(
            'drop',
            function (event) {
                const files =
                    event.dataTransfer?.files;

                if (!files?.length) {
                    return;
                }

                event.preventDefault();
                chatApp.classList.remove(
                    'dragging-files'
                );
                addFiles(files);
            }
        );
    }

    function initializeDraftRuntime() {
        input.addEventListener(
            'input',
            scheduleDraftSave
        );

        window.addEventListener(
            'pagehide',
            saveCurrentDraftImmediately
        );
    }

    function scheduleDraftSave() {
        window.clearTimeout(
            draftSaveTimer
        );

        draftSaveTimer =
            window.setTimeout(
                saveCurrentDraftImmediately,
                220
            );
    }

    function readDrafts() {
        try {
            const raw =
                localStorage.getItem(
                    draftStorageKey
                );

            const parsed =
                raw
                    ? JSON.parse(raw)
                    : {};

            return parsed
                && typeof parsed === 'object'
                && !Array.isArray(parsed)
                    ? parsed
                    : {};
        } catch (error) {
            return {};
        }
    }

    function saveCurrentDraftImmediately() {
        window.clearTimeout(
            draftSaveTimer
        );

        if (!activeConversationId) {
            return;
        }

        const drafts =
            readDrafts();

        const value =
            String(input.value || '');

        if (value.trim() === '') {
            delete drafts[
                activeConversationId
            ];
        } else {
            drafts[
                activeConversationId
            ] = value.slice(0, 12000);
        }

        try {
            localStorage.setItem(
                draftStorageKey,
                JSON.stringify(drafts)
            );
        } catch (error) {
            // Drafts zijn optioneel.
        }
    }

    function clearDraftForConversation(id) {
        if (!id) {
            return;
        }

        const drafts =
            readDrafts();

        if (!(id in drafts)) {
            return;
        }

        delete drafts[id];

        try {
            localStorage.setItem(
                draftStorageKey,
                JSON.stringify(drafts)
            );
        } catch (error) {
            // Geen actie nodig.
        }
    }

    function restoreDraftForActiveConversation() {
        const drafts =
            readDrafts();

        input.value =
            String(
                drafts[
                    activeConversationId
                ]
                || ''
            );

        autoResize();
    }

    function setMobileStatus(
        message,
        duration = 0
    ) {
        if (
            !mobileStatus
            || !mobileStatusText
        ) {
            return;
        }

        const value =
            String(message || '');

        mobileStatusText.textContent =
            value;

        mobileStatus.classList.toggle(
            'show',
            Boolean(value)
        );

        if (
            value
            && duration > 0
        ) {
            const captured = value;

            window.setTimeout(
                function () {
                    if (
                        mobileStatusText.textContent
                        === captured
                    ) {
                        mobileStatusText.textContent = '';
                        mobileStatus.classList.remove(
                            'show'
                        );
                    }
                },
                duration
            );
        }
    }

    function setSendButtonState(sending) {
        if (!sendButton) {
            return;
        }

        sendButton.disabled =
            !chatConfigured;

        sendButton.classList.toggle(
            'is-stopping',
            Boolean(sending)
        );

        sendButton.setAttribute(
            'aria-label',
            sending
                ? 'Stop genereren'
                : 'Verstuur'
        );

        sendButton.title =
            sending
                ? 'Stop genereren'
                : 'Verstuur';
    }

    sendButton?.addEventListener(
        'click',
        function (event) {
            if (
                !sendingText
                || !activeTextRequestController
            ) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            activeTextRequestController.abort();
        },
        true
    );

    function triggerSoftHaptic() {
        try {
            if (
                isMobileInteractionMode()
                && typeof navigator.vibrate
                    === 'function'
            ) {
                navigator.vibrate(8);
            }
        } catch (error) {
            // Haptics zijn optioneel.
        }
    }

    async function requestMicrophoneStream() {
        const preferred = {
            audio: {
                echoCancellation: true,
                noiseSuppression: true,
                autoGainControl: true,
                channelCount: 1,
            },
            video: false,
        };

        try {
            return await navigator.mediaDevices
                .getUserMedia(preferred);
        } catch (preferredError) {
            /*
             * Sommige mobiele browsers weigeren één van de optionele
             * constraints. Probeer daarom nog één keer met alleen audio.
             */
            try {
                return await navigator.mediaDevices
                    .getUserMedia({
                        audio: true,
                        video: false,
                    });
            } catch (basicError) {
                throw basicError;
            }
        }
    }

    async function requestVoiceWakeLock() {
        if (
            !('wakeLock' in navigator)
            || !voiceActive
        ) {
            return;
        }

        try {
            wakeLockSentinel =
                await navigator.wakeLock
                    .request('screen');

            wakeLockSentinel
                .addEventListener(
                    'release',
                    function () {
                        wakeLockSentinel = null;
                    },
                    {
                        once: true,
                    }
                );
        } catch (error) {
            wakeLockSentinel = null;
        }
    }

    async function releaseVoiceWakeLock() {
        if (!wakeLockSentinel) {
            return;
        }

        try {
            await wakeLockSentinel.release();
        } catch (error) {
            // Geen actie nodig.
        }

        wakeLockSentinel = null;
    }

    function updateVoiceDeviceHint() {
        if (!voiceDeviceHint) {
            return;
        }

        if (!isMobileInteractionMode()) {
            voiceDeviceHint.textContent =
                serverTtsConfigured
                    ? 'Server-spraak actief. Je kunt de AI onderbreken door te praten.'
                    : 'Browser-spraak actief. Je kunt de AI onderbreken door te praten.';
            return;
        }

        voiceDeviceHint.textContent =
            serverTtsConfigured
                ? 'Telefoonmodus actief · server-audio · microfoon automatisch · praat om te onderbreken.'
                : 'Telefoonmodus actief · browserstem fallback · houd mediavolume en microfoon aan.';
    }

    function handleVisibilityChange() {
        if (
            document.visibilityState === 'visible'
        ) {
            syncResponsiveViewport();

            if (
                voiceActive
                && !wakeLockSentinel
            ) {
                requestVoiceWakeLock();
            }

            if (
                audioContext?.state
                === 'suspended'
            ) {
                audioContext.resume()
                    .catch(function () {});
            }

            return;
        }

        if (
            voiceActive
            && recording
        ) {
            stopRecording();
        }
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
        function () {
            saveCurrentDraftImmediately();
            clearSelectedFiles();
            releaseVoiceWakeLock();
            cleanupVoiceResources();
        }
    );

    window.addEventListener(
        'pagehide',
        function () {
            saveCurrentDraftImmediately();
            releaseVoiceWakeLock();
        }
    );
});
</script>
@endpush
