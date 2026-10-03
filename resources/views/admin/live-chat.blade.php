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


/* ==========================================================================
   MASHAL SUPPORT — EXPERT CONSOLE V3
   Visual-only override. All existing JavaScript hooks remain untouched.
   ========================================================================== */

.lca {
    --lca-bg: #0a0d12;
    --lca-bg-soft: #0d1117;
    --lca-panel: #10151d;
    --lca-panel-2: #141a23;
    --lca-panel-3: #18202a;
    --lca-panel-4: #202a36;
    --lca-border: rgba(255,255,255,.075);
    --lca-border-strong: rgba(255,255,255,.13);
    --lca-text: #f4f7fb;
    --lca-text-soft: #cbd3df;
    --lca-muted: #8792a2;
    --lca-muted-2: #626d7b;
    --lca-accent: #5b8cff;
    --lca-accent-2: #7c5cff;
    --lca-accent-soft: rgba(91,140,255,.12);
    --lca-success: #43d39e;
    --lca-warning: #ffca65;
    --lca-danger: #ff667d;
    max-width: 1680px;
    margin: 0 auto;
    letter-spacing: -.005em;
}

.lca::before {
    background:
        radial-gradient(circle at 76% -12%, rgba(91,140,255,.12), transparent 31rem),
        radial-gradient(circle at 2% 42%, rgba(124,92,255,.06), transparent 27rem),
        #080b10;
}

.lca::after {
    opacity: .07;
    background-size: 48px 48px;
}

.lca-shell { gap: 14px; }

/* Header */
.lca-top {
    min-height: 78px;
    padding: 17px 20px 17px 22px;
    border-radius: 18px;
    border-color: rgba(255,255,255,.08);
    background: rgba(15,20,28,.94);
    box-shadow: 0 18px 56px rgba(0,0,0,.22);
    backdrop-filter: blur(18px);
}

.lca-top::before {
    width: 2px;
    top: 18px;
    bottom: 18px;
    border-radius: 99px;
    background: linear-gradient(180deg, #74a0ff, #7c5cff);
    box-shadow: 0 0 24px rgba(91,140,255,.35);
}

.lca-top::after { display: none; }

.lca-eyebrow {
    color: #7f8da1;
    font-size: 9px;
    font-weight: 750;
    letter-spacing: .14em;
}

.lca-eyebrow__dot {
    background: #43d39e;
    box-shadow: 0 0 0 4px rgba(67,211,158,.08), 0 0 14px rgba(67,211,158,.4);
}

.lca-top h1 {
    margin-top: 4px;
    color: #f7f9fc;
    font-size: clamp(21px, 2vw, 27px);
    line-height: 1.1;
    font-weight: 760;
    letter-spacing: -.035em;
}

.lca-top p {
    margin-top: 5px;
    color: #7f8997;
    font-size: 11px;
}

.lca-presence {
    min-width: 260px;
    padding: 10px 13px;
    gap: 11px;
    border: 1px solid rgba(67,211,158,.14);
    border-radius: 13px;
    background: rgba(67,211,158,.045);
}

.lca-presence__copy strong { color: #dfe8e5; font-size: 10px; }
.lca-presence__copy small { color: #6d7b78; font-size: 8px; }

/* Stats */
.lca-stats {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
}

.lca-stat {
    min-height: 64px;
    padding: 12px 14px;
    gap: 11px;
    border: 1px solid rgba(255,255,255,.065);
    border-radius: 14px;
    background: rgba(14,19,26,.78);
    box-shadow: none;
}

.lca-stat:hover {
    border-color: rgba(91,140,255,.16);
    background: #111720;
}

.lca-stat__icon {
    width: 34px;
    height: 34px;
    border: 1px solid rgba(91,140,255,.12);
    border-radius: 10px;
    color: #91adff;
    background: rgba(91,140,255,.06);
    font-size: 14px;
    filter: grayscale(.15);
}

.lca-stat__copy strong { color: #e9edf4; font-size: 12px; font-weight: 720; }
.lca-stat__copy small { color: #6f7a89; font-size: 8px; }

/* Main workspace */
.lca-grid {
    height: clamp(650px, calc(100vh - 260px), 930px);
    min-height: 650px;
    grid-template-columns: 350px minmax(0, 1fr);
    gap: 0;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,.085);
    border-radius: 20px;
    background: #0d1219;
    box-shadow: 0 30px 90px rgba(0,0,0,.30);
}

/* Inbox */
.lca-inbox {
    border: 0;
    border-right: 1px solid rgba(255,255,255,.07);
    border-radius: 0;
    background: #0e131a;
    box-shadow: none;
}

.lca-inbox__head {
    padding: 17px 15px 14px;
    gap: 12px;
    border-bottom: 1px solid rgba(255,255,255,.07);
    background: rgba(14,19,26,.98);
}

.lca-inbox__title strong {
    color: #f0f3f7;
    font-size: 14px;
    font-weight: 720;
    letter-spacing: -.02em;
}

.lca-inbox__title span {
    color: #667282;
    font-size: 8px;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.lca-search {
    min-height: 42px;
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 11px;
    background: #0a0f15;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.015);
}

.lca-search:focus-within {
    border-color: rgba(91,140,255,.45);
    box-shadow: 0 0 0 3px rgba(91,140,255,.08);
}

.lca-search input { color: #dce2ea; font-size: 11px; }
.lca-search input::placeholder { color: #505a68; }
.lca-search__icon { color: #667384; }

.lca-filter select,
.lca-icon-button,
.lca-pages button {
    min-height: 38px;
    border: 1px solid rgba(255,255,255,.075) !important;
    border-radius: 10px !important;
    color: #8995a5 !important;
    background: #111720 !important;
}

.lca-filter select:hover,
.lca-icon-button:hover,
.lca-pages button:hover:not(:disabled) {
    color: #dbe2ec !important;
    border-color: rgba(91,140,255,.22) !important;
    background: #151d28 !important;
}

.lca-list { padding: 7px; }

.lca-item {
    min-height: 72px;
    margin-bottom: 3px;
    padding: 12px 11px !important;
    grid-template-columns: 44px minmax(0, 1fr) auto;
    gap: 11px;
    border: 1px solid transparent !important;
    border-radius: 12px !important;
}

.lca-item:hover {
    border-color: rgba(255,255,255,.05) !important;
    background: rgba(255,255,255,.025) !important;
}

.lca-item[aria-pressed="true"] {
    border-color: rgba(91,140,255,.18) !important;
    background: linear-gradient(90deg, rgba(91,140,255,.13), rgba(91,140,255,.035)) !important;
    box-shadow: inset 2px 0 #5b8cff;
}

.lca-item[aria-pressed="true"]::after { background: #75a0ff; box-shadow: 0 0 12px rgba(91,140,255,.52); }

.lca-item__avatar {
    width: 44px;
    height: 44px;
    border-radius: 13px;
    border-color: rgba(255,255,255,.09);
    background: linear-gradient(145deg, #263140, #161d27);
    font-size: 11px;
}

.lca-item__name-row strong { color: #e2e7ed; font-size: 11px; font-weight: 690; }
.lca-item__meta { margin-top: 4px; color: #687586; font-size: 9px; }
.lca-item__time { color: #596575; font-size: 8px; }
.lca-kind { color: #788395; border-color: rgba(255,255,255,.07); background: #151b24; font-size: 6px; }

.lca-unread {
    min-width: 19px;
    height: 19px;
    right: 9px;
    bottom: 8px;
    color: white;
    background: #5b8cff;
    box-shadow: 0 5px 16px rgba(91,140,255,.24);
}

.lca-pages {
    min-height: 52px;
    padding: 8px 10px;
    border-color: rgba(255,255,255,.07);
    background: #0c1117;
}

.lca-pages span { color: #687485; font-size: 8px; }

/* Conversation */
.lca-detail {
    border: 0;
    border-radius: 0;
    background: #0b1016;
    box-shadow: none;
}

.lca-heading {
    min-height: 80px;
    padding: 14px 18px;
    border-color: rgba(255,255,255,.07);
    background: rgba(13,18,25,.96);
    backdrop-filter: blur(18px);
}

.lca-heading strong { color: #f0f3f8; font-size: 14px; font-weight: 720; }
.lca-heading p { margin-top: 2px; color: #707c8d; font-size: 9px; }

.lca-status-chip {
    margin-top: 6px;
    padding: 4px 8px;
    color: #84a497;
    border-color: rgba(67,211,158,.14);
    background: rgba(67,211,158,.045);
    font-size: 8px;
}

.lca-heading__actions { gap: 7px; flex-wrap: wrap; justify-content: flex-end; }

.lca-heading__actions button {
    min-height: 36px;
    padding: 0 11px;
    border-radius: 10px !important;
    font-size: 8px;
    font-weight: 680;
}

.lca-email-handoff {
    color: #a7b4c7 !important;
    border-color: rgba(91,140,255,.16) !important;
    background: rgba(91,140,255,.055) !important;
}

.lca-email-handoff:hover:not(:disabled) {
    color: #e1e7f0 !important;
    border-color: rgba(91,140,255,.34) !important;
    background: rgba(91,140,255,.10) !important;
}

.lca-close {
    color: #d98a98 !important;
    border-color: rgba(255,102,125,.14) !important;
    background: rgba(255,102,125,.045) !important;
}

/* Message canvas */
.lca-log {
    padding: 28px clamp(24px, 4vw, 58px) 34px;
    background:
        radial-gradient(circle at 50% 0%, rgba(91,140,255,.035), transparent 25rem),
        #0b1016;
    mask-image: none;
}

.lca-empty-chat__icon {
    border-color: rgba(91,140,255,.14);
    color: #8babff;
    background: rgba(91,140,255,.055);
}

.lca-empty-chat strong { color: #e6eaf0; font-size: 14px; }
.lca-empty-chat p { color: #697586; font-size: 10px; }

.lca-message {
    max-width: min(720px, 72%);
    margin-bottom: 13px;
    padding: 10px 12px 11px;
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 15px 15px 15px 5px;
    color: #e0e5ec;
    background: #151c25;
    box-shadow: 0 10px 28px rgba(0,0,0,.12);
}

.lca-message[data-sender="admin"] {
    border-color: rgba(91,140,255,.30);
    border-radius: 15px 15px 5px 15px;
    color: #fff;
    background: linear-gradient(145deg, #4f7ff3, #416fdf);
    box-shadow: 0 12px 28px rgba(36,77,177,.18);
}

.lca-message p { font-size: 11px; line-height: 1.58; }
.lca-msg-head small { font-size: 7px; opacity: .65; }
.lca-avatar { border-radius: 9px; background: #27313e; }
.lca-message[data-sender="admin"] .lca-avatar { background: rgba(0,0,0,.13); }

.lca-media-image,
.lca-media-video {
    border-color: rgba(255,255,255,.10);
    border-radius: 12px;
    box-shadow: 0 10px 26px rgba(0,0,0,.18);
}

.lca-file-link {
    padding: 7px 9px;
    border: 1px solid rgba(255,255,255,.10);
    border-radius: 9px;
    background: rgba(255,255,255,.045);
    text-decoration: none;
}

.lca-file-link:hover { background: rgba(255,255,255,.075); text-decoration: none; }

/* Reply state inserted by JS */
.lca-reply-preview {
    margin: 0 0 8px;
    padding: 9px 11px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    border: 1px solid rgba(91,140,255,.14);
    border-radius: 10px;
    color: #9ca9ba;
    background: rgba(91,140,255,.055);
    font-size: 9px;
}

.lca-reply-preview button {
    width: 24px;
    height: 24px;
    border: 0;
    border-radius: 7px;
    color: #8c98a8;
    background: rgba(255,255,255,.05);
}

/* Typing */
.lca-typing { padding: 0 22px 8px; }
.lca-typing__bubble { border-color: rgba(255,255,255,.08); background: #151c25; }
.lca-typing__dot { background: #718096; }
.lca-typing__label { color: #6f7c8d; }

/* Composer */
.lca-composer-wrap {
    padding: 10px 14px 13px;
    border-top: 1px solid rgba(255,255,255,.07);
    background: rgba(13,18,25,.97);
    backdrop-filter: blur(18px);
}

.lca-composer-wrap::before { display: none; }

.lca-composer-info {
    min-height: 20px;
    padding: 0 2px 7px;
    color: #667386;
    font-size: 8px;
}

.lca-form {
    min-height: 54px;
    padding: 6px;
    gap: 6px;
    border: 1px solid rgba(255,255,255,.09);
    border-radius: 15px;
    background: #0a0f15;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.02), 0 8px 26px rgba(0,0,0,.14);
}

.lca-form:focus-within {
    border-color: rgba(91,140,255,.35);
    box-shadow: 0 0 0 3px rgba(91,140,255,.065), 0 8px 26px rgba(0,0,0,.14);
}

.lca-tool {
    width: 40px;
    min-width: 40px;
    height: 40px;
    border: 0 !important;
    border-radius: 10px !important;
    color: #7f8b9c !important;
    background: transparent !important;
}

.lca-tool:hover:not(:disabled) {
    color: #dce2ea !important;
    background: rgba(255,255,255,.055) !important;
}

.lca-form textarea {
    min-height: 40px;
    max-height: 160px;
    padding: 10px 8px;
    border: 0 !important;
    color: #e8edf4;
    background: transparent !important;
    box-shadow: none !important;
    font-size: 11px;
}

.lca-form textarea::placeholder { color: #566170; }

.lca-submit {
    min-width: 92px;
    min-height: 40px;
    padding: 0 16px;
    border: 0 !important;
    border-radius: 11px !important;
    color: #fff !important;
    background: linear-gradient(135deg, #5b8cff, #6d66f2) !important;
    box-shadow: 0 8px 22px rgba(69,105,210,.24);
    font-size: 9px;
    font-weight: 760;
}

.lca-submit:hover:not(:disabled) {
    transform: translateY(-1px);
    filter: brightness(1.06);
    box-shadow: 0 11px 28px rgba(69,105,210,.30);
}

/* Errors / toasts */
.lca-error {
    border-color: rgba(255,102,125,.16);
    border-radius: 12px;
    color: #ff9bad;
    background: rgba(255,102,125,.055);
}

.lca-toast {
    border-radius: 12px;
    border-color: rgba(255,255,255,.10);
    background: #151c25;
    box-shadow: 0 18px 48px rgba(0,0,0,.30);
}

/* Scrollbars */
.lca-list::-webkit-scrollbar,
.lca-log::-webkit-scrollbar { width: 8px; }
.lca-list::-webkit-scrollbar-track,
.lca-log::-webkit-scrollbar-track { background: transparent; }
.lca-list::-webkit-scrollbar-thumb,
.lca-log::-webkit-scrollbar-thumb {
    border: 2px solid transparent;
    border-radius: 99px;
    background: rgba(255,255,255,.12);
    background-clip: padding-box;
}

@media (max-width: 1180px) {
    .lca-grid { grid-template-columns: 310px minmax(0, 1fr); }
    .lca-message { max-width: 82%; }
    .lca-top p { display: none; }
}

@media (max-width: 880px) {
    .lca-top { grid-template-columns: 1fr; gap: 12px; }
    .lca-presence { min-width: 0; width: 100%; }
    .lca-stats { grid-template-columns: 1fr; }
    .lca-stat:nth-child(n+2) { display: none; }
    .lca-grid {
        height: auto;
        min-height: 0;
        grid-template-columns: 1fr;
        overflow: visible;
    }
    .lca-inbox {
        min-height: 430px;
        max-height: 520px;
        border-right: 0;
        border-bottom: 1px solid rgba(255,255,255,.07);
    }
    .lca-detail { min-height: 680px; }
    .lca-heading { align-items: flex-start; flex-direction: column; }
    .lca-heading__actions { width: 100%; justify-content: flex-start; }
    .lca-log { min-height: 430px; padding-inline: 16px; }
    .lca-message { max-width: 90%; }
}

@media (max-width: 560px) {
    .lca-top { padding: 15px; border-radius: 14px; }
    .lca-grid { border-radius: 14px; }
    .lca-inbox__head { padding: 13px 11px; }
    .lca-list { padding: 5px; }
    .lca-item { grid-template-columns: 40px minmax(0,1fr) auto; }
    .lca-item__avatar { width: 40px; height: 40px; }
    .lca-heading { padding: 12px; }
    .lca-heading__actions button { flex: 1 1 auto; }
    .lca-composer-wrap { padding: 8px; }
    .lca-form { grid-template-columns: 38px 38px minmax(0,1fr); }
    .lca-submit { grid-column: 1 / -1; width: 100%; }
}



/* ==========================================================================\n   FULL PAGE / ADMIN CHROME REDESIGN\n   These overrides load only on the live-chat page because this stylesheet\n   lives inside this Blade view. Other admin pages remain untouched.\n   ========================================================================== */

body {
    background:
        radial-gradient(circle at 75% -10%, rgba(89, 139, 255, .12), transparent 32rem),
        radial-gradient(circle at 5% 100%, rgba(200, 255, 98, .06), transparent 28rem),
        #05070a !important;
}

.admin-app {
    min-height: 100vh;
    background: transparent !important;
}

/* Sidebar becomes a real operator-console rail */
.admin-sidebar {
    top: 14px !important;
    bottom: 14px !important;
    left: 14px !important;
    height: calc(100vh - 28px) !important;
    border: 1px solid rgba(255,255,255,.075) !important;
    border-radius: 22px !important;
    background:
        linear-gradient(180deg, rgba(255,255,255,.025), transparent 24%),
        rgba(8, 11, 16, .96) !important;
    box-shadow: 0 30px 90px rgba(0,0,0,.38) !important;
    overflow: hidden !important;
    backdrop-filter: blur(18px);
}

.admin-sidebar::before {
    content: "";
    position: absolute;
    inset: 0 auto 0 0;
    width: 2px;
    background: linear-gradient(180deg, #c8ff62, #68e7ff 48%, transparent 88%);
    opacity: .75;
    pointer-events: none;
}

.admin-brand-row {
    padding-top: 17px !important;
    padding-bottom: 14px !important;
}

.admin-brand-mark {
    border-radius: 12px !important;
    background: linear-gradient(145deg, #d8ff8e, #a8eb39) !important;
    color: #071008 !important;
    box-shadow: 0 8px 30px rgba(200,255,98,.16) !important;
}

.admin-profile {
    margin: 4px 10px 10px !important;
    border: 1px solid rgba(255,255,255,.06) !important;
    border-radius: 15px !important;
    background: rgba(255,255,255,.025) !important;
}

.admin-sidebar-search {
    margin-inline: 10px !important;
    border: 1px solid rgba(255,255,255,.065) !important;
    border-radius: 13px !important;
    background: rgba(255,255,255,.025) !important;
}

.admin-nav {
    padding-inline: 9px !important;
}

.admin-nav-link {
    min-height: 50px !important;
    border-radius: 13px !important;
    border: 1px solid transparent !important;
    transition: background .18s ease, border-color .18s ease, transform .18s ease !important;
}

.admin-nav-link:hover {
    transform: translateX(2px);
    border-color: rgba(255,255,255,.06) !important;
    background: rgba(255,255,255,.035) !important;
}

.admin-nav-link.active {
    border-color: rgba(200,255,98,.16) !important;
    background:
        linear-gradient(90deg, rgba(200,255,98,.10), rgba(104,231,255,.035)) !important;
    box-shadow: inset 3px 0 0 #c8ff62 !important;
}

.admin-nav-link.active .admin-nav-icon {
    color: #dfff9e !important;
}

/* Main workspace */
.admin-main {
    min-height: 100vh !important;
    padding-top: 14px !important;
    padding-right: 14px !important;
    padding-bottom: 14px !important;
}

.admin-topbar {
    min-height: 76px !important;
    margin-bottom: 12px !important;
    padding: 12px 18px !important;
    border: 1px solid rgba(255,255,255,.07) !important;
    border-radius: 20px !important;
    background:
        linear-gradient(180deg, rgba(255,255,255,.018), transparent),
        rgba(9,13,18,.92) !important;
    box-shadow: 0 20px 55px rgba(0,0,0,.22) !important;
    backdrop-filter: blur(16px);
}

.admin-topbar-copy {
    gap: 4px !important;
}

.admin-page-title {
    font-size: clamp(20px, 2vw, 28px) !important;
    letter-spacing: -.035em !important;
    font-weight: 760 !important;
}

.admin-topbar-copy::before {
    content: "SUPPORT COMMAND CENTER";
    display: block;
    color: #84909b;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .16em;
}

.admin-topbar-actions > * {
    border-radius: 12px !important;
}

.admin-content {
    padding: 0 !important;
    max-width: none !important;
    width: 100% !important;
}

/* Make the live workspace use the available viewport like a real support desk */
.lca {
    min-height: calc(100vh - 116px);
}

.lca-shell {
    min-height: calc(100vh - 116px);
}

.lca-top {
    min-height: 86px;
}

.lca-grid {
    min-height: min(760px, calc(100vh - 292px));
    height: calc(100vh - 292px);
}

.lca-inbox,
.lca-detail {
    min-height: 0;
}

.lca-list,
.lca-log {
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,.16) transparent;
}

.lca-list::-webkit-scrollbar,
.lca-log::-webkit-scrollbar {
    width: 7px;
}

.lca-list::-webkit-scrollbar-thumb,
.lca-log::-webkit-scrollbar-thumb {
    border-radius: 999px;
    background: rgba(255,255,255,.14);
}

/* Wider desktop support view */
@media (min-width: 1180px) {
    .lca-grid {
        grid-template-columns: minmax(300px, 360px) minmax(0, 1fr) !important;
    }
}

/* Tablet */
@media (max-width: 1100px) {
    .admin-main {
        padding: 10px !important;
    }

    .admin-topbar {
        border-radius: 16px !important;
    }

    .lca,
    .lca-shell {
        min-height: auto;
    }

    .lca-grid {
        height: auto;
        min-height: 680px;
    }
}

/* Mobile: keep the page purposeful instead of looking like squeezed desktop */
@media (max-width: 760px) {
    body {
        background: #06090d !important;
    }

    .admin-main {
        padding: 8px !important;
    }

    .admin-topbar {
        min-height: 64px !important;
        margin-bottom: 8px !important;
        padding: 10px 12px !important;
        border-radius: 14px !important;
    }

    .admin-topbar-copy::before {
        font-size: 9px;
    }

    .admin-page-title {
        font-size: 20px !important;
    }

    .lca-top,
    .lca-stats {
        border-radius: 15px;
    }

    .lca-grid {
        min-height: 0;
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



                    Mashal Support · Operator Console



                </div>







                <h1>



                    Live Support



                </h1>







                <p>



                    Beheer realtime klantgesprekken, media en e-mailhandoff vanuit één professionele supportworkspace.



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



                        Gesprekken in deze inbox



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



                        Realtime synchronisatie actief



                    </small>



                </div>



            </div>







            <div class="lca-stat">



                <div class="lca-stat__icon">



                    🎙️



                </div>







                <div class="lca-stat__copy">



                    <strong>



                        Bestanden & media



                    </strong>







                    <small>



                        Afbeeldingen, video, voice en documenten



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



    src="{{ asset('js/admin-live-chat.js') }}?v=80"



    defer



></script>




<script src="{{ asset('js/live-chat-calls.js') }}?v=7" defer></script>

@endsection
