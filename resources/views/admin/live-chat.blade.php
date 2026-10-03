@extends('layouts.admin-layout')







@section('title', 'Live chat | Mashal Admin')



@section('page-title', 'Live chat')







@section('content')



<style>
/* ==========================================================================
   MASHAL SUPPORT // OPERATOR CONSOLE
   Signature redesign — preserves existing JS hooks and DOM structure
   ========================================================================== */

.lca {
    --lca-bg: #05070a;
    --lca-bg-soft: #090d12;
    --lca-panel: #0c1117;
    --lca-panel-2: #101720;
    --lca-panel-3: #151e28;
    --lca-panel-4: #1b2631;

    --lca-border: rgba(255,255,255,.065);
    --lca-border-strong: rgba(255,255,255,.12);

    --lca-text: #f3f7f9;
    --lca-text-soft: #cbd4da;
    --lca-muted: #7e8993;
    --lca-muted-2: #56616c;

    --lca-accent: #c8ff62;
    --lca-accent-2: #68e7ff;
    --lca-accent-soft: rgba(200,255,98,.09);

    --lca-success: #67e6a3;
    --lca-success-soft: rgba(103,230,163,.09);
    --lca-warning: #ffd16f;
    --lca-warning-soft: rgba(255,209,111,.09);
    --lca-danger: #ff7f96;
    --lca-danger-soft: rgba(255,127,150,.08);

    --lca-radius-xs: 7px;
    --lca-radius-sm: 10px;
    --lca-radius-md: 13px;
    --lca-radius-lg: 17px;
    --lca-radius-xl: 22px;

    --lca-shadow-sm: 0 12px 34px rgba(0,0,0,.20);
    --lca-shadow-lg: 0 38px 120px rgba(0,0,0,.42);

    position: relative;
    isolation: isolate;

    width: 100%;
    min-width: 0;

    color: var(--lca-text);

    font:
        14px/1.55 Inter,
        ui-sans-serif,
        system-ui,
        -apple-system,
        BlinkMacSystemFont,
        "Segoe UI",
        sans-serif;
}

.lca::before {
    content: "";

    position: fixed;
    z-index: -2;

    inset: 0;

    pointer-events: none;

    background:
        radial-gradient(circle at 78% 0%, rgba(104,231,255,.055), transparent 30rem),
        radial-gradient(circle at 8% 40%, rgba(200,255,98,.035), transparent 24rem),
        linear-gradient(180deg, #05070a 0%, #070a0e 55%, #05070a 100%);
}

.lca::after {
    content: "";

    position: fixed;
    z-index: -1;

    inset: 0;

    pointer-events: none;

    opacity: .16;

    background-image:
        linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);

    background-size:
        44px 44px;

    mask-image:
        linear-gradient(to bottom, #000 0%, rgba(0,0,0,.55) 50%, transparent 100%);
}

.lca *,
.lca *::before,
.lca *::after {
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
.lca select,
.lca label {
    -webkit-tap-highlight-color: transparent;
}

.lca button,
.lca select {
    cursor: pointer;
}

.lca button:disabled,
.lca select:disabled,
.lca textarea:disabled,
.lca input:disabled {
    opacity: .4;
    cursor: not-allowed;
}

.lca :is(button, textarea, select, input):focus-visible {
    outline:
        2px solid rgba(200,255,98,.82);

    outline-offset:
        2px;
}

.lca a {
    color: inherit;
}

.lca-shell {
    display: grid;
    gap: 12px;

    width: 100%;
    min-width: 0;
}

/* ==========================================================================
   OPERATOR BAR
   ========================================================================== */

.lca-top {
    position: relative;

    min-height: 92px;

    padding:
        18px 20px;

    display: grid;

    grid-template-columns:
        minmax(0, 1fr)
        auto;

    align-items: center;

    gap: 24px;

    overflow: hidden;

    border:
        1px solid var(--lca-border);

    border-radius:
        18px;

    background:
        linear-gradient(180deg, rgba(255,255,255,.018), transparent),
        rgba(9,13,18,.93);

    box-shadow:
        var(--lca-shadow-sm);
}

.lca-top::before {
    content: "";

    position: absolute;

    left: 0;
    top: 0;
    bottom: 0;

    width: 3px;

    background:
        linear-gradient(
            180deg,
            var(--lca-accent),
            var(--lca-accent-2)
        );

    box-shadow:
        0 0 24px rgba(200,255,98,.25);
}

.lca-top::after {
    content: "SUPPORT // LIVE";

    position: absolute;

    right: 18px;
    bottom: 9px;

    color:
        rgba(255,255,255,.025);

    font-size: 32px;
    font-weight: 900;
    letter-spacing: -.04em;

    pointer-events: none;
}

.lca-top__copy {
    position: relative;
    z-index: 1;

    min-width: 0;
    max-width: 740px;
}

.lca-eyebrow {
    display: inline-flex;

    align-items: center;

    gap: 8px;

    margin-bottom: 6px;

    color:
        #98a3ad;

    font-size: 8px;
    font-weight: 850;
    letter-spacing: .16em;

    text-transform: uppercase;
}

.lca-eyebrow__dot {
    width: 7px;
    height: 7px;

    border-radius: 2px;

    background:
        var(--lca-success);

    box-shadow:
        0 0 0 4px rgba(103,230,163,.07),
        0 0 14px rgba(103,230,163,.28);
}

.lca-top h1 {
    margin: 0;

    color:
        #f4f8fa;

    font-size:
        clamp(26px, 3vw, 40px);

    line-height: 1;

    font-weight: 690;
    letter-spacing: -.055em;
}

.lca-top p {
    max-width: 700px;

    margin:
        8px 0 0;

    color:
        var(--lca-muted);

    font-size: 10px;
}

/* ==========================================================================
   PRESENCE SWITCH
   ========================================================================== */

.lca-presence {
    position: relative;
    z-index: 2;

    min-width:
        min(100%, 320px);

    padding:
        10px 12px;

    display: grid;

    grid-template-columns:
        38px
        minmax(0, 1fr);

    align-items: center;

    gap: 10px;

    border:
        1px solid var(--lca-border);

    border-radius:
        12px;

    background:
        rgba(255,255,255,.018);

    cursor: pointer;

    transition:
        border-color .18s ease,
        background .18s ease,
        transform .18s ease;
}

.lca-presence:hover {
    transform:
        translateY(-1px);

    border-color:
        rgba(200,255,98,.18);

    background:
        rgba(200,255,98,.035);
}

.lca-presence input {
    appearance: none;

    position: relative;

    width: 38px;
    height: 22px;

    margin: 0;

    border:
        1px solid rgba(255,255,255,.12);

    border-radius:
        999px;

    background:
        #151c24;

    cursor: pointer;

    transition:
        background .18s ease,
        border-color .18s ease;
}

.lca-presence input::after {
    content: "";

    position: absolute;

    left: 3px;
    top: 3px;

    width: 14px;
    height: 14px;

    border-radius: 50%;

    background:
        #707b86;

    transition:
        transform .18s cubic-bezier(.16,1,.3,1),
        background .18s ease;
}

.lca-presence input:checked {
    border-color:
        rgba(103,230,163,.22);

    background:
        rgba(103,230,163,.13);
}

.lca-presence input:checked::after {
    transform:
        translateX(16px);

    background:
        var(--lca-success);

    box-shadow:
        0 0 12px rgba(103,230,163,.38);
}

.lca-presence__copy {
    min-width: 0;

    display: grid;
    gap: 2px;
}

.lca-presence__copy strong {
    color:
        #dce4e9;

    font-size: 9px;
    font-weight: 760;
}

.lca-presence__copy small {
    color:
        #5f6974;

    font-size: 7px;
}

/* ==========================================================================
   TELEMETRY STRIP
   ========================================================================== */

.lca-stats {
    display: grid;

    grid-template-columns:
        repeat(3, minmax(0, 1fr));

    gap: 8px;
}

.lca-stat {
    position: relative;

    min-height: 70px;

    padding:
        12px 14px;

    display: grid;

    grid-template-columns:
        34px
        minmax(0, 1fr);

    align-items: center;

    gap: 10px;

    overflow: hidden;

    border:
        1px solid var(--lca-border);

    border-radius:
        13px;

    background:
        rgba(10,14,19,.86);
}

.lca-stat::after {
    content: "";

    position: absolute;

    left: 0;
    right: 0;
    bottom: 0;

    height: 1px;

    opacity: .38;

    background:
        linear-gradient(
            90deg,
            transparent,
            rgba(200,255,98,.28),
            transparent
        );
}

.lca-stat__icon {
    width: 34px;
    height: 34px;

    display: grid;

    place-items: center;

    border:
        1px solid var(--lca-border);

    border-radius:
        9px;

    color:
        var(--lca-accent);

    background:
        rgba(255,255,255,.018);

    font-size: 13px;

    filter:
        grayscale(.2);
}

.lca-stat__copy {
    min-width: 0;

    display: grid;
    gap: 1px;
}

.lca-stat__copy strong {
    color:
        #e8edf1;

    font-size: 11px;
    font-weight: 760;
}

.lca-stat__copy small {
    overflow: hidden;

    color:
        #596470;

    font-size: 7px;

    text-overflow: ellipsis;
    white-space: nowrap;
}

/* ==========================================================================
   ERROR
   ========================================================================== */

.lca-error {
    margin: 0;

    padding:
        11px 13px;

    border:
        1px solid rgba(255,127,150,.20);

    border-radius:
        10px;

    color:
        #f3aeb9;

    background:
        rgba(255,127,150,.055);

    font-size: 9px;
}

/* ==========================================================================
   MAIN OPERATOR WORKSPACE
   ========================================================================== */

.lca-grid {
    position: relative;

    display: grid;

    grid-template-columns:
        minmax(300px, 350px)
        minmax(0, 1fr);

    min-height:
        680px;

    height:
        min(78vh, 980px);

    overflow: hidden;

    border:
        1px solid var(--lca-border);

    border-radius:
        20px;

    background:
        #070a0e;

    box-shadow:
        var(--lca-shadow-lg);
}

/* ==========================================================================
   INBOX
   ========================================================================== */

.lca-inbox {
    display: grid;

    grid-template-rows:
        auto
        minmax(0, 1fr)
        auto;

    min-width: 0;
    min-height: 0;

    border-right:
        1px solid var(--lca-border);

    background:
        linear-gradient(
            180deg,
            rgba(255,255,255,.012),
            transparent 35%
        ),
        #0a0e13;
}

.lca-inbox__head {
    display: grid;

    gap: 10px;

    padding:
        14px;

    border-bottom:
        1px solid var(--lca-border);

    background:
        rgba(255,255,255,.008);
}

.lca-inbox__title {
    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 10px;
}

.lca-inbox__title strong {
    color:
        #e6ebef;

    font-size: 11px;
    font-weight: 780;

    letter-spacing: -.02em;
}

.lca-inbox__title span {
    color:
        #56616c;

    font-size: 7px;
    font-weight: 750;

    letter-spacing: .08em;

    text-transform: uppercase;
}

/* Search */

.lca-search {
    position: relative;

    display: flex;

    align-items: center;
}

.lca-search__icon {
    position: absolute;

    left: 11px;

    color:
        #68737f;

    pointer-events: none;
}

.lca-search input {
    width: 100%;
    min-width: 0;
    height: 40px;

    padding:
        8px 11px 8px 33px;

    border:
        1px solid var(--lca-border);

    border-radius:
        9px;

    color:
        var(--lca-text);

    background:
        #070a0e;

    font-size: 9px;

    transition:
        border-color .18s ease,
        box-shadow .18s ease;
}

.lca-search input:focus {
    border-color:
        rgba(104,231,255,.22);

    box-shadow:
        0 0 0 3px rgba(104,231,255,.035);
}

.lca-search input::placeholder {
    color:
        #4d5864;
}

.lca-filter {
    display: grid;

    grid-template-columns:
        minmax(0, 1fr)
        40px;

    gap: 7px;
}

.lca select,
.lca button {
    border:
        1px solid var(--lca-border);

    border-radius:
        9px;

    color:
        var(--lca-text);

    background:
        #10161e;

    transition:
        background .18s ease,
        border-color .18s ease,
        transform .18s ease,
        opacity .18s ease;
}

.lca select {
    width: 100%;

    padding:
        8px 10px;

    color:
        #aab4bd;

    font-size: 8px;
}

.lca button {
    padding:
        8px 11px;
}

.lca button:hover:not(:disabled),
.lca select:hover:not(:disabled) {
    border-color:
        rgba(200,255,98,.16);

    background:
        #151d26;
}

.lca-icon-button {
    width: 40px;
    height: 40px;

    padding: 0 !important;

    display: grid;
    place-items: center;

    color:
        #8d98a3;

    font-size: 14px;
}

/* Conversation list */

.lca-list {
    min-height: 0;

    overflow-y: auto;

    overscroll-behavior: contain;

    scrollbar-width: thin;
    scrollbar-color:
        rgba(255,255,255,.13)
        transparent;
}

.lca-list-empty {
    margin: 0;

    padding:
        30px 16px;

    color:
        #59636f;

    text-align: center;

    font-size: 9px;
}

.lca-item {
    position: relative;

    width: 100%;
    min-width: 0;

    padding:
        12px 13px !important;

    display:
        grid !important;

    grid-template-columns:
        40px
        minmax(0, 1fr)
        auto;

    gap: 10px;

    border:
        0 !important;

    border-bottom:
        1px solid rgba(255,255,255,.04) !important;

    border-radius:
        0 !important;

    color:
        var(--lca-text) !important;

    background:
        transparent !important;

    text-align: left;

    transition:
        background .16s ease,
        transform .16s ease !important;
}

.lca-item:hover {
    background:
        rgba(255,255,255,.022) !important;
}

.lca-item[aria-pressed="true"] {
    background:
        linear-gradient(
            90deg,
            rgba(200,255,98,.09),
            rgba(104,231,255,.025)
        ) !important;

    box-shadow:
        inset 2px 0
        var(--lca-accent);
}

.lca-item[aria-pressed="true"]::after {
    content: "";

    position: absolute;

    right: 10px;
    top: 10px;

    width: 5px;
    height: 5px;

    border-radius: 50%;

    background:
        var(--lca-accent);

    box-shadow:
        0 0 10px rgba(200,255,98,.45);
}

.lca-item__avatar {
    width: 40px;
    height: 40px;

    display: grid;
    place-items: center;

    overflow: hidden;

    border:
        1px solid rgba(255,255,255,.08);

    border-radius:
        11px;

    color:
        #f0f4f6;

    background:
        linear-gradient(
            145deg,
            #202a34,
            #151d25
        );

    font-size: 10px;
    font-weight: 800;
}

.lca-item__avatar img {
    width: 100%;
    height: 100%;

    object-fit: cover;
}

.lca-item__body {
    min-width: 0;
}

.lca-item__name-row {
    min-width: 0;

    display: flex;

    align-items: center;

    gap: 6px;
}

.lca-item__name-row strong {
    overflow: hidden;

    color:
        #dce2e6;

    font-size: 10px;
    font-weight: 750;

    text-overflow: ellipsis;
    white-space: nowrap;
}

.lca-kind {
    flex: 0 0 auto;

    padding:
        2px 5px;

    border:
        1px solid rgba(255,255,255,.055);

    border-radius:
        5px;

    color:
        #596571;

    background:
        rgba(255,255,255,.018);

    font-size: 6px;
    font-weight: 800;
    letter-spacing: .07em;

    text-transform: uppercase;
}

.lca-item__meta {
    display: block;

    margin-top: 3px;

    overflow: hidden;

    color:
        #5e6975;

    font-size: 8px;

    text-overflow: ellipsis;
    white-space: nowrap;
}

.lca-item__time {
    color:
        #4e5964;

    font-size: 7px;

    white-space: nowrap;
}

.lca-unread {
    position: absolute;

    right: 10px;
    bottom: 9px;

    min-width: 18px;
    height: 18px;

    padding:
        0 5px;

    display: grid;
    place-items: center;

    border-radius:
        999px;

    color:
        #172009;

    background:
        var(--lca-accent);

    font-size: 7px;
    font-weight: 900;
}

/* Pagination */

.lca-pages {
    min-height: 54px;

    padding:
        9px;

    display: grid;

    grid-template-columns:
        38px
        minmax(0, 1fr)
        38px;

    align-items: center;

    gap: 7px;

    border-top:
        1px solid var(--lca-border);

    background:
        rgba(255,255,255,.008);
}

.lca-pages span {
    min-width: 0;

    color:
        #59646f;

    text-align: center;

    font-size: 7px;
}

.lca-pages button {
    width: 38px;
    height: 34px;

    padding: 0;
}

/* ==========================================================================
   CONVERSATION STAGE
   ========================================================================== */

.lca-detail {
    position: relative;

    min-width: 0;
    min-height: 0;

    display: grid;

    grid-template-rows:
        auto
        minmax(0, 1fr)
        auto;

    background:
        radial-gradient(circle at 90% 5%, rgba(104,231,255,.028), transparent 24%),
        #070a0e;
}

/* Conversation header */

.lca-heading {
    position: relative;
    z-index: 4;

    min-width: 0;

    padding:
        13px 16px;

    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 14px;

    border-bottom:
        1px solid var(--lca-border);

    background:
        rgba(8,11,15,.90);

    backdrop-filter:
        blur(12px);

    -webkit-backdrop-filter:
        blur(12px);
}

.lca-heading__identity {
    min-width: 0;

    display: grid;

    gap: 2px;
}

.lca-heading__name-row {
    min-width: 0;

    display: flex;

    align-items: center;

    gap: 7px;
}

.lca-heading strong {
    overflow: hidden;

    color:
        #e8edf1;

    font-size: 12px;
    font-weight: 770;

    text-overflow: ellipsis;
    white-space: nowrap;
}

.lca-heading p {
    margin: 0;

    overflow-wrap: anywhere;

    color:
        #5e6975;

    font-size: 8px;
}

.lca-status-chip {
    width: fit-content;

    margin-top: 4px;

    padding:
        3px 7px;

    display: inline-flex;

    align-items: center;

    gap: 5px;

    border:
        1px solid rgba(103,230,163,.09);

    border-radius:
        999px;

    color:
        #7c8b84;

    background:
        rgba(103,230,163,.035);

    font-size: 7px;
}

.lca-status-chip::before {
    content: "";

    width: 5px;
    height: 5px;

    border-radius: 50%;

    background:
        var(--lca-success);

    box-shadow:
        0 0 9px rgba(103,230,163,.42);
}

.lca-heading__actions {
    flex: 0 0 auto;

    display: flex;

    align-items: center;

    gap: 6px;
}

.lca-heading__actions button {
    min-height: 35px;

    padding:
        0 9px;

    color:
        #89949f;

    font-size: 7px;
    font-weight: 760;
}

.lca-close {
    color:
        #e693a1 !important;

    border-color:
        rgba(255,127,150,.14) !important;

    background:
        rgba(255,127,150,.035) !important;
}

.lca-email-handoff {
    color:
        #aeb9c1 !important;

    border-color:
        rgba(104,231,255,.12) !important;

    background:
        rgba(104,231,255,.025) !important;
}

.lca-email-handoff[data-active="true"] {
    color:
        #a3e9c2 !important;

    border-color:
        rgba(103,230,163,.20) !important;

    background:
        rgba(103,230,163,.06) !important;
}

/* ==========================================================================
   MESSAGE LOG
   ========================================================================== */

.lca-log {
    min-width: 0;
    min-height: 0;

    overflow-y: auto;

    padding:
        28px clamp(18px, 3vw, 42px);

    scroll-behavior: smooth;

    scrollbar-width: thin;
    scrollbar-color:
        rgba(255,255,255,.12)
        transparent;

    overscroll-behavior: contain;

    background-image:
        linear-gradient(
            rgba(255,255,255,.018) 1px,
            transparent 1px
        ),
        linear-gradient(
            90deg,
            rgba(255,255,255,.018) 1px,
            transparent 1px
        );

    background-size:
        32px 32px;

    mask-image:
        linear-gradient(
            to bottom,
            transparent 0,
            #000 8%,
            #000 92%,
            transparent 100%
        );
}

.lca-log::before {
    content: "";

    display: block;

    height: 1px;
}

/* Empty */

.lca-empty-chat {
    min-height: 100%;
    height: 100%;

    padding:
        30px;

    display: grid;
    place-items: center;

    color:
        #63707b;

    text-align: center;
}

.lca-empty-chat__icon {
    width: 62px;
    height: 62px;

    margin:
        0 auto 14px;

    display: grid;
    place-items: center;

    border:
        1px solid rgba(104,231,255,.10);

    border-radius:
        18px;

    color:
        #7deaff;

    background:
        linear-gradient(
            145deg,
            rgba(104,231,255,.07),
            rgba(200,255,98,.02)
        );

    box-shadow:
        0 20px 60px rgba(0,0,0,.22);

    font-size: 22px;

    filter:
        grayscale(.15);
}

.lca-empty-chat strong {
    display: block;

    color:
        #dfe5e9;

    font-size: 12px;
}

.lca-empty-chat p {
    max-width: 340px;

    margin:
        6px 0 0;

    color:
        #596570;

    font-size: 9px;
}

/* Messages */

.lca-message {
    width: fit-content;

    max-width:
        min(760px, 74%);

    margin:
        0 0 14px;

    padding:
        10px 12px;

    border:
        1px solid rgba(255,255,255,.055);

    border-radius:
        13px 13px 13px 4px;

    color:
        #dbe2e7;

    background:
        linear-gradient(
            180deg,
            #111923,
            #0e151d
        );

    box-shadow:
        0 9px 28px rgba(0,0,0,.14);

    animation:
        lcaMessageIn .22s cubic-bezier(.16,1,.3,1);
}

.lca-message[data-sender="admin"] {
    margin-left:
        auto;

    border-color:
        rgba(200,255,98,.18);

    border-radius:
        13px 13px 4px 13px;

    color:
        #172006;

    background:
        linear-gradient(
            155deg,
            #d4ff83,
            #b7ee55
        );

    box-shadow:
        0 12px 30px rgba(120,170,45,.11);
}

@keyframes lcaMessageIn {
    from {
        opacity: 0;

        transform:
            translateY(6px)
            scale(.99);
    }

    to {
        opacity: 1;

        transform:
            translateY(0)
            scale(1);
    }
}

.lca-msg-head {
    margin-bottom:
        5px;

    display: flex;

    align-items: center;

    gap: 7px;
}

.lca-msg-head small {
    opacity: .65;

    font-size: 7px;
    font-weight: 800;

    letter-spacing: .03em;
}

.lca-avatar {
    width: 26px;
    height: 26px;

    flex:
        0 0 26px;

    display: grid;
    place-items: center;

    overflow: hidden;

    border-radius:
        8px;

    color:
        #fff;

    background:
        #222d37;

    font-size: 8px;
    font-weight: 800;
}

.lca-avatar img {
    width: 100%;
    height: 100%;

    object-fit: cover;
}

.lca-message p {
    margin:
        2px 0 0;

    white-space: pre-wrap;
    overflow-wrap: anywhere;

    font-size: 11px;
    line-height: 1.58;
}

.lca-media-image,
.lca-media-video {
    display: block;

    max-width:
        min(420px, 100%);

    max-height:
        390px;

    margin-top:
        8px;

    border:
        1px solid rgba(255,255,255,.07);

    border-radius:
        10px;

    object-fit: cover;

    box-shadow:
        0 10px 28px rgba(0,0,0,.19);
}

.lca-message audio {
    display: block;

    width:
        min(390px, 100%);

    margin-top:
        8px;
}

.lca-file-link {
    margin-top:
        8px;

    display: inline-flex;

    align-items: center;

    gap: 7px;

    font-weight: 700;

    text-decoration: none;
}

.lca-file-link:hover {
    text-decoration: underline;
}

.lca-delete {
    margin-top:
        7px !important;

    padding:
        2px 0 !important;

    border:
        0 !important;

    color:
        inherit !important;

    background:
        transparent !important;

    opacity: .45;

    font-size:
        7px !important;
}

.lca-delete:hover {
    opacity: 1;

    text-decoration: underline;
}

/* Scroll helper */

.lca-scroll-down {
    position: absolute;

    z-index: 5;

    right: 18px;
    bottom: 104px;

    width: 38px;
    height: 38px;

    padding: 0 !important;

    display: grid;
    place-items: center;

    border:
        1px solid rgba(200,255,98,.16) !important;

    border-radius:
        11px !important;

    color:
        #d8ff8c;

    background:
        rgba(13,18,23,.94) !important;

    box-shadow:
        0 14px 38px rgba(0,0,0,.30);
}

/* ==========================================================================
   TYPING
   ========================================================================== */

.lca-typing {
    min-height: 43px;

    padding:
        6px 20px 4px;

    display: flex;

    align-items: flex-end;

    gap: 8px;

    animation:
        lcaTypingAppear .18s ease-out;
}

.lca-typing__avatar {
    width: 25px;
    height: 25px;

    flex:
        0 0 25px;

    display: grid;
    place-items: center;

    overflow: hidden;

    border:
        1px solid var(--lca-border);

    border-radius:
        8px;

    color:
        #fff;

    background:
        #202a34;

    font-size: 8px;
    font-weight: 800;
}

.lca-typing__avatar img {
    width: 100%;
    height: 100%;

    object-fit: cover;
}

.lca-typing__bubble {
    min-height: 32px;

    padding:
        9px 11px;

    display: flex;

    align-items: center;

    gap: 4px;

    border:
        1px solid rgba(255,255,255,.055);

    border-radius:
        12px 12px 12px 4px;

    background:
        #111923;
}

.lca-typing__dot {
    width: 5px;
    height: 5px;

    border-radius: 50%;

    background:
        #78838e;

    animation:
        lcaTypingDot 1.2s infinite ease-in-out;
}

.lca-typing__dot:nth-child(2) {
    animation-delay: .15s;
}

.lca-typing__dot:nth-child(3) {
    animation-delay: .30s;
}

.lca-typing__label {
    align-self: center;

    color:
        #56616c;

    font-size: 7px;
}

@keyframes lcaTypingDot {
    0%,
    60%,
    100% {
        opacity: .35;
        transform: translateY(0);
    }

    30% {
        opacity: 1;
        transform: translateY(-4px);
    }
}

@keyframes lcaTypingAppear {
    from {
        opacity: 0;
        transform: translateY(4px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ==========================================================================
   COMMAND COMPOSER
   ========================================================================== */

.lca-composer-wrap {
    position: relative;

    padding:
        10px 13px 13px;

    border-top:
        1px solid var(--lca-border);

    background:
        linear-gradient(
            180deg,
            rgba(255,255,255,.006),
            rgba(255,255,255,.016)
        ),
        #090d12;
}

.lca-composer-wrap::before {
    content: "";

    position: absolute;

    left: 15%;
    right: 15%;
    top: -1px;

    height: 1px;

    background:
        linear-gradient(
            90deg,
            transparent,
            rgba(200,255,98,.16),
            rgba(104,231,255,.12),
            transparent
        );
}

.lca-composer-info {
    min-height: 16px;

    margin-bottom:
        6px;

    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 12px;

    color:
        #56616c;

    font-size: 7px;
}

.lca-recorder-state {
    color:
        #98dfea;

    font-weight: 760;
}

.lca-form {
    width: 100%;
    min-width: 0;

    display: grid;

    grid-template-columns:
        42px
        42px
        minmax(0, 1fr)
        auto;

    gap: 7px;

    align-items: end;
}

.lca-tool {
    width: 42px;
    height: 42px;

    flex:
        0 0 42px;

    padding:
        0 !important;

    display: grid;
    place-items: center;

    border-radius:
        10px !important;

    color:
        #89949f;

    font-size: 14px;

    user-select: none;
}

.lca-tool[aria-pressed="true"] {
    border-color:
        rgba(255,127,150,.28) !important;

    color:
        #ffb0bd !important;

    background:
        rgba(255,127,150,.08) !important;

    box-shadow:
        0 0 0 3px rgba(255,127,150,.04);
}

.lca textarea {
    width: 100%;
    min-width: 0;

    min-height: 42px;
    max-height: 150px;

    padding:
        10px 12px;

    resize: none;

    border:
        1px solid var(--lca-border-strong);

    border-radius:
        10px;

    color:
        var(--lca-text);

    background:
        #06090d;

    font-size: 10px;
    line-height: 1.45;

    transition:
        border-color .18s ease,
        box-shadow .18s ease;
}

.lca textarea:focus {
    border-color:
        rgba(104,231,255,.22);

    box-shadow:
        0 0 0 3px rgba(104,231,255,.035);
}

.lca textarea::placeholder {
    color:
        #4c5762;
}

.lca-submit {
    min-width: 92px;
    height: 42px;

    padding-inline:
        16px !important;

    border:
        1px solid rgba(200,255,98,.22) !important;

    color:
        #172006 !important;

    background:
        linear-gradient(
            155deg,
            #d5ff84,
            #b5ea54
        ) !important;

    box-shadow:
        0 10px 28px rgba(116,163,43,.11);

    font-size: 8px;
    font-weight: 850;
}

.lca-submit:hover:not(:disabled) {
    transform:
        translateY(-1px);

    background:
        linear-gradient(
            155deg,
            #ddff99,
            #c0f15f
        ) !important;
}

/* ==========================================================================
   TOASTS
   ========================================================================== */

.lca-toast-stack {
    position: fixed;

    z-index: 9999;

    right: 18px;
    bottom: 18px;

    width:
        min(350px, calc(100vw - 28px));

    display: grid;

    gap: 7px;

    pointer-events: none;
}

.lca-toast {
    padding:
        10px 12px;

    border:
        1px solid var(--lca-border-strong);

    border-radius:
        10px;

    color:
        #dbe1e6;

    background:
        rgba(15,21,28,.97);

    box-shadow:
        0 16px 44px rgba(0,0,0,.38);

    font-size: 8px;

    animation:
        lcaToastIn .18s ease-out;
}

.lca-toast[data-kind="success"] {
    border-color:
        rgba(103,230,163,.22);
}

.lca-toast[data-kind="error"] {
    border-color:
        rgba(255,127,150,.23);

    color:
        #efadb8;
}

@keyframes lcaToastIn {
    from {
        opacity: 0;
        transform: translateY(8px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ==========================================================================
   RESPONSIVE
   ========================================================================== */

@media (max-width: 1180px) {
    .lca-grid {
        grid-template-columns:
            300px
            minmax(0, 1fr);
    }

    .lca-message {
        max-width: 82%;
    }
}

@media (max-width: 920px) {
    .lca-top {
        grid-template-columns: 1fr;
    }

    .lca-presence {
        width: 100%;
    }

    .lca-grid {
        grid-template-columns: 1fr;

        height: auto;
        min-height: 0;
    }

    .lca-inbox {
        max-height: 360px;

        border-right: 0;

        border-bottom:
            1px solid var(--lca-border);
    }

    .lca-detail {
        min-height: 620px;
        height: 70vh;
    }
}

@media (max-width: 680px) {
    .lca-shell {
        gap: 9px;
    }

    .lca-top {
        padding: 15px;

        border-radius: 15px;
    }

    .lca-top::after {
        display: none;
    }

    .lca-stats {
        grid-template-columns: 1fr;
    }

    .lca-stat {
        min-height: 60px;
    }

    .lca-grid {
        border-radius: 15px;
    }

    .lca-heading {
        align-items: flex-start;

        padding: 11px;
    }

    .lca-heading__actions {
        align-self: center;

        gap: 4px;
    }

    .lca-heading__actions button {
        max-width: 96px;

        overflow: hidden;

        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .lca-log {
        padding: 15px 12px;
    }

    .lca-message {
        max-width: 92%;
    }

    .lca-form {
        grid-template-columns:
            42px
            42px
            minmax(0, 1fr);
    }

    .lca-submit {
        grid-column:
            1 / -1;

        width: 100%;
    }

    .lca-composer-wrap {
        padding: 9px;
    }

    .lca-composer-info {
        align-items: flex-start;

        flex-direction: column;

        gap: 2px;
    }
}

@media (max-width: 460px) {
    .lca-top h1 {
        font-size: 26px;
    }

    .lca-presence {
        grid-template-columns: 36px 1fr;
    }

    .lca-heading {
        flex-direction: column;
    }

    .lca-heading__actions {
        width: 100%;

        justify-content: flex-start;

        flex-wrap: wrap;
    }

    .lca-detail {
        min-height: 660px;
        height: 72vh;
    }

    .lca-message {
        max-width: 96%;
    }
}

@media (prefers-reduced-motion: reduce) {
    .lca *,
    .lca *::before,
    .lca *::after {
        scroll-behavior: auto !important;

        animation-duration: .001ms !important;
        animation-iteration-count: 1 !important;

        transition-duration: .001ms !important;
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



                        <label



                            class="lca-tool"



                            title="Bestand versturen"



                            aria-label="Bestand versturen"



                        >



                            📎







                            <input



                                class="lca-file"



                                type="file"



                                accept="image/jpeg,image/png,image/webp,image/gif,video/*,.mp4,.webm,.mov,.m4v,.avi,.mkv,.mpeg,.mpg,.3gp,.3g2,.ogv,.ts,.mts,.m2ts,.flv,.wmv,application/pdf,text/plain,.doc,.docx,.xls,.xlsx"



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








<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('admin-live-chat');
    const list = root?.querySelector('.lca-list');
    const log = root?.querySelector('.lca-log');
    const search = root?.querySelector('[data-search]');

    if (!root) {
        return;
    }

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!reducedMotion && 'MutationObserver' in window) {
        const animateNewChildren = function (container) {
            if (!container) {
                return;
            }

            const observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    mutation.addedNodes.forEach(function (node) {
                        if (!(node instanceof HTMLElement)) {
                            return;
                        }

                        if (!node.matches('.lca-item, .lca-message, .lca-toast')) {
                            return;
                        }

                        node.animate(
                            [
                                {
                                    opacity: 0,
                                    transform: 'translateY(7px)'
                                },
                                {
                                    opacity: 1,
                                    transform: 'translateY(0)'
                                }
                            ],
                            {
                                duration: 230,
                                easing: 'cubic-bezier(.16,1,.3,1)'
                            }
                        );
                    });
                });
            });

            observer.observe(container, {
                childList: true
            });
        };

        animateNewChildren(list);
        animateNewChildren(log);
    }

    document.addEventListener('keydown', function (event) {
        const active = document.activeElement;
        const typing =
            active &&
            (
                active.tagName === 'INPUT' ||
                active.tagName === 'TEXTAREA' ||
                active.isContentEditable
            );

        if (!typing && event.key === '/') {
            event.preventDefault();
            search?.focus();
        }
    });
});
</script>


<script



    src="{{ asset('js/admin-live-chat.js') }}?v=64"



    defer



></script>



@endsection
