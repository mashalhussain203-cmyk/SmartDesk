@extends('layouts.admin-layout')



@section('title', 'Live chat | Mashal Admin')

@section('page-title', 'Live chat')



@section('content')

<style>

    /* ==========================================================================

       Mashal Admin Live Chat

       ========================================================================== */



    .lca {

        --lca-bg: #0a0d12;

        --lca-bg-soft: #0e1218;

        --lca-panel: #12171f;

        --lca-panel-2: #171d26;

        --lca-panel-3: #1d2530;

        --lca-panel-4: #252e3b;



        --lca-border: rgba(255, 255, 255, 0.075);

        --lca-border-strong: rgba(255, 255, 255, 0.14);



        --lca-text: #f7f3eb;

        --lca-text-soft: #d9dce2;

        --lca-muted: #969daa;

        --lca-muted-2: #757d8a;



        --lca-accent: #e8be7e;

        --lca-accent-2: #f3d29f;

        --lca-accent-soft: rgba(232, 190, 126, 0.11);



        --lca-success: #6bc895;

        --lca-success-soft: rgba(107, 200, 149, 0.10);



        --lca-warning: #e8b861;

        --lca-warning-soft: rgba(232, 184, 97, 0.10);



        --lca-danger: #e27373;

        --lca-danger-soft: rgba(226, 115, 115, 0.09);



        --lca-radius-xs: 8px;

        --lca-radius-sm: 11px;

        --lca-radius-md: 14px;

        --lca-radius-lg: 18px;

        --lca-radius-xl: 22px;



        --lca-shadow-sm: 0 10px 28px rgba(0, 0, 0, 0.18);

        --lca-shadow-lg: 0 30px 90px rgba(0, 0, 0, 0.34);



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

        opacity: 0.42;

        cursor: not-allowed;

    }



    .lca :is(button, textarea, select, input):focus-visible {

        outline: 2px solid var(--lca-accent);

        outline-offset: 2px;

    }



    .lca a {

        color: inherit;

    }



    .lca-shell {

        display: grid;

        gap: 16px;

        width: 100%;

        min-width: 0;

    }



    /* ==========================================================================

       Dashboard header

       ========================================================================== */



    .lca-top {

        position: relative;

        overflow: hidden;



        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 22px;

        flex-wrap: wrap;



        padding: 22px 24px;



        border: 1px solid var(--lca-border);

        border-radius: var(--lca-radius-xl);



        background:

            radial-gradient(

                circle at 88% -15%,

                rgba(232, 190, 126, 0.15),

                transparent 36%

            ),

            linear-gradient(

                180deg,

                rgba(255, 255, 255, 0.025),

                transparent

            ),

            var(--lca-panel);



        box-shadow: var(--lca-shadow-lg);

    }



    .lca-top::after {

        content: "";



        position: absolute;

        inset: auto -80px -100px auto;



        width: 230px;

        height: 230px;



        border-radius: 50%;



        background:

            radial-gradient(

                circle,

                rgba(232, 190, 126, 0.07),

                transparent 68%

            );



        pointer-events: none;

    }



    .lca-top__copy {

        position: relative;

        z-index: 1;



        min-width: 0;

        max-width: 720px;

    }



    .lca-eyebrow {

        display: inline-flex;

        align-items: center;

        gap: 9px;



        margin-bottom: 7px;



        color: var(--lca-accent);



        font-size: 10px;

        font-weight: 800;

        letter-spacing: 0.15em;

        text-transform: uppercase;

    }



    .lca-eyebrow__dot {

        width: 8px;

        height: 8px;



        border-radius: 50%;



        background: var(--lca-success);



        box-shadow:

            0 0 0 5px rgba(107, 200, 149, 0.08),

            0 0 18px rgba(107, 200, 149, 0.24);

    }



    .lca-top h1 {

        margin: 0;



        font-size: clamp(24px, 2.5vw, 33px);

        line-height: 1.12;

        letter-spacing: -0.028em;

    }



    .lca-top p {

        margin: 8px 0 0;



        color: var(--lca-muted);



        font-size: 12px;

    }



    .lca-presence {

        position: relative;

        z-index: 1;



        display: flex;

        align-items: center;

        gap: 12px;



        min-width: min(100%, 300px);



        padding: 12px 14px;



        border: 1px solid var(--lca-border);

        border-radius: var(--lca-radius-md);



        background:

            linear-gradient(

                180deg,

                rgba(255, 255, 255, 0.025),

                rgba(255, 255, 255, 0.01)

            );



        cursor: pointer;

    }



    .lca-presence:hover {

        border-color: rgba(232, 190, 126, 0.22);

    }



    .lca-presence input {

        width: 19px;

        height: 19px;

        flex: 0 0 19px;



        accent-color: var(--lca-success);

    }



    .lca-presence__copy {

        display: grid;

        gap: 1px;



        min-width: 0;

    }



    .lca-presence__copy strong {

        font-size: 12px;

        font-weight: 750;

    }



    .lca-presence__copy small {

        color: var(--lca-muted);



        font-size: 10px;

    }



    /* ==========================================================================

       Small dashboard stats

       ========================================================================== */



    .lca-stats {

        display: grid;

        grid-template-columns:

            repeat(3, minmax(0, 1fr));



        gap: 10px;

    }



    .lca-stat {

        display: flex;

        align-items: center;

        gap: 11px;



        min-width: 0;



        padding: 11px 13px;



        border: 1px solid var(--lca-border);

        border-radius: var(--lca-radius-md);



        background:

            linear-gradient(

                180deg,

                rgba(255, 255, 255, 0.02),

                transparent

            ),

            var(--lca-panel);

    }



    .lca-stat__icon {

        width: 36px;

        height: 36px;

        flex: 0 0 36px;



        display: grid;

        place-items: center;



        border-radius: 10px;



        background: var(--lca-accent-soft);



        font-size: 15px;

    }



    .lca-stat__copy {

        display: grid;

        gap: 1px;



        min-width: 0;

    }



    .lca-stat__copy strong {

        font-size: 12px;

    }



    .lca-stat__copy small {

        color: var(--lca-muted);



        font-size: 9px;

    }



    /* ==========================================================================

       Errors

       ========================================================================== */



    .lca-error {

        margin: 0;



        padding: 12px 14px;



        border: 1px solid rgba(226, 115, 115, 0.25);

        border-radius: var(--lca-radius-sm);



        background:

            linear-gradient(

                180deg,

                rgba(226, 115, 115, 0.09),

                rgba(226, 115, 115, 0.05)

            );



        color: #ffc4c4;



        box-shadow: var(--lca-shadow-sm);

    }



    /* ==========================================================================

       Main layout

       ========================================================================== */



    .lca-grid {

        display: grid;

        grid-template-columns:

            minmax(290px, 340px)

            minmax(0, 1fr);



        min-height: 650px;

        height: min(77vh, 940px);



        overflow: hidden;



        border: 1px solid var(--lca-border);

        border-radius: var(--lca-radius-xl);



        background: var(--lca-bg);



        box-shadow: var(--lca-shadow-lg);

    }



    /* ==========================================================================

       Inbox

       ========================================================================== */



    .lca-inbox {

        display: grid;

        grid-template-rows:

            auto

            minmax(0, 1fr)

            auto;



        min-width: 0;

        min-height: 0;



        border-right: 1px solid var(--lca-border);



        background:

            linear-gradient(

                180deg,

                rgba(255, 255, 255, 0.012),

                transparent 40%

            ),

            #0f1319;

    }



    .lca-inbox__head {

        display: grid;

        gap: 12px;



        padding: 15px;



        border-bottom: 1px solid var(--lca-border);

    }



    .lca-inbox__title {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 10px;

    }



    .lca-inbox__title strong {

        font-size: 13px;

    }



    .lca-inbox__title span {

        color: var(--lca-muted);



        font-size: 10px;

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



        opacity: 0.5;



        pointer-events: none;

    }



    .lca-search input {

        width: 100%;

        min-width: 0;

        height: 40px;



        padding:

            8px

            11px

            8px

            34px;



        border: 1px solid var(--lca-border-strong);

        border-radius: var(--lca-radius-sm);



        background: #090c10;

        color: var(--lca-text);

    }



    .lca-search input::placeholder {

        color: var(--lca-muted-2);

    }



    .lca-filter {

        display: grid;

        grid-template-columns:

            minmax(0, 1fr)

            40px;



        gap: 8px;

    }



    .lca select,

    .lca button {

        border: 1px solid var(--lca-border-strong);

        border-radius: var(--lca-radius-sm);



        background: var(--lca-panel-2);

        color: var(--lca-text);



        transition:

            background 0.18s ease,

            border-color 0.18s ease,

            transform 0.18s ease,

            opacity 0.18s ease;

    }



    .lca select {

        width: 100%;



        padding: 8px 10px;

    }



    .lca button {

        padding: 8px 11px;

    }



    .lca button:hover:not(:disabled),

    .lca select:hover:not(:disabled) {

        border-color:

            rgba(232, 190, 126, 0.34);



        background: #202733;

    }



    .lca-icon-button {

        display: grid;

        place-items: center;



        width: 40px;

        height: 40px;



        padding: 0 !important;



        font-size: 16px;

    }



    /* Conversation list */



    .lca-list {

        min-height: 0;



        overflow-y: auto;



        overscroll-behavior: contain;

        scrollbar-width: thin;

    }



    .lca-list-empty {

        margin: 0;



        padding: 28px 16px;



        color: var(--lca-muted);



        text-align: center;

        font-size: 11px;

    }



    .lca-item {

        position: relative;



        display: grid !important;

        grid-template-columns:

            42px

            minmax(0, 1fr)

            auto;



        gap: 10px;



        width: 100%;

        min-width: 0;



        padding: 13px 14px !important;



        border: 0 !important;

        border-bottom:

            1px solid

            rgba(255, 255, 255, 0.045) !important;

        border-radius: 0 !important;



        background:

            transparent !important;



        color:

            var(--lca-text) !important;



        text-align: left;

    }



    .lca-item:hover {

        background:

            rgba(255, 255, 255, 0.025) !important;

    }



    .lca-item[aria-pressed="true"] {

        background:

            linear-gradient(

                90deg,

                rgba(232, 190, 126, 0.13),

                rgba(232, 190, 126, 0.05)

            ) !important;



        box-shadow:

            inset 3px 0

            var(--lca-accent);

    }



    .lca-item__avatar {

        width: 42px;

        height: 42px;



        display: grid;

        place-items: center;



        overflow: hidden;



        border:

            1px solid

            rgba(255, 255, 255, 0.09);

        border-radius: 50%;



        background: #313945;

        color: #fff;



        font-size: 12px;

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

        display: flex;

        align-items: center;

        gap: 6px;



        min-width: 0;

    }



    .lca-item__name-row strong {

        overflow: hidden;



        text-overflow: ellipsis;

        white-space: nowrap;



        font-size: 12px;

    }



    .lca-kind {

        flex: 0 0 auto;



        padding:

            2px

            5px;



        border-radius: 999px;



        background:

            rgba(255, 255, 255, 0.05);



        color: var(--lca-muted);



        font-size: 8px;

        letter-spacing: 0.05em;

        text-transform: uppercase;

    }



    .lca-item__meta {

        display: block;



        margin-top: 3px;



        overflow: hidden;



        color: var(--lca-muted);



        font-size: 10px;



        text-overflow: ellipsis;

        white-space: nowrap;

    }



    .lca-item__time {

        color: var(--lca-muted-2);



        font-size: 9px;



        white-space: nowrap;

    }



    .lca-unread {

        position: absolute;

        right: 12px;

        bottom: 12px;



        min-width: 20px;

        height: 20px;



        display: grid;

        place-items: center;



        padding: 0 6px;



        border-radius: 999px;



        background: var(--lca-accent);

        color: #211609;



        font-size: 9px;

        font-weight: 800;

    }



    /* Pagination */



    .lca-pages {

        display: grid;

        grid-template-columns:

            40px

            minmax(0, 1fr)

            40px;



        align-items: center;



        gap: 8px;



        padding: 10px;



        border-top: 1px solid var(--lca-border);



        background:

            rgba(255, 255, 255, 0.012);

    }



    .lca-pages span {

        min-width: 0;



        color: var(--lca-muted);



        text-align: center;

        font-size: 10px;

    }



    .lca-pages button {

        width: 40px;

        height: 36px;



        padding: 0;

    }



    /* ==========================================================================

       Conversation panel

       ========================================================================== */



    .lca-detail {

        position: relative;



        display: grid;

        grid-template-rows:

            auto

            minmax(0, 1fr)

            auto;



        min-width: 0;

        min-height: 0;



        background:

            radial-gradient(

                circle at 90% 5%,

                rgba(232, 190, 126, 0.04),

                transparent 27%

            ),

            var(--lca-bg);

    }



    /* Header */



    .lca-heading {

        display: flex;

        align-items: center;

        justify-content: space-between;



        gap: 14px;



        min-width: 0;



        padding: 14px 18px;



        border-bottom: 1px solid var(--lca-border);



        background:

            linear-gradient(

                180deg,

                rgba(255, 255, 255, 0.018),

                rgba(255, 255, 255, 0.008)

            );

    }



    .lca-heading__identity {

        display: grid;

        gap: 2px;



        min-width: 0;

    }



    .lca-heading__name-row {

        display: flex;

        align-items: center;

        gap: 8px;



        min-width: 0;

    }



    .lca-heading strong {

        overflow: hidden;



        font-size: 14px;



        text-overflow: ellipsis;

        white-space: nowrap;

    }



    .lca-heading p {

        margin: 0;



        color: var(--lca-muted);



        overflow-wrap: anywhere;



        font-size: 10px;

    }



    .lca-status-chip {

        display: inline-flex;

        align-items: center;

        gap: 5px;



        width: fit-content;



        margin-top: 4px;



        padding:

            3px

            7px;



        border-radius: 999px;



        background:

            rgba(255, 255, 255, 0.04);



        color: var(--lca-muted);



        font-size: 9px;

    }



    .lca-status-chip::before {

        content: "";



        width: 6px;

        height: 6px;



        border-radius: 50%;



        background: var(--lca-success);

    }



    .lca-heading__actions {

        display: flex;

        align-items: center;

        gap: 8px;



        flex: 0 0 auto;

    }



    .lca-close {

        color: #ffbcbc !important;



        border-color:

            rgba(226, 115, 115, 0.24) !important;



        background:

            rgba(226, 115, 115, 0.07) !important;

    }



    .lca-email-handoff {

        color: #f8d49a !important;

        border-color:

            rgba(232, 190, 126, 0.30) !important;

        background:

            rgba(232, 190, 126, 0.09) !important;

    }



    .lca-email-handoff[data-active="true"] {

        color: #b9f6ce !important;

        border-color:

            rgba(83, 196, 125, 0.30) !important;

        background:

            rgba(83, 196, 125, 0.10) !important;

    }



    /* Message log */



    .lca-log {

        min-width: 0;

        min-height: 0;



        overflow-y: auto;



        padding: 24px;



        scroll-behavior: smooth;

        scrollbar-width: thin;

        overscroll-behavior: contain;

    }



    .lca-log::before {

        content: "";



        display: block;



        height: 1px;

    }



    .lca-empty-chat {

        height: 100%;

        min-height: 240px;



        display: grid;

        place-items: center;



        padding: 30px;



        color: var(--lca-muted);



        text-align: center;

    }



    .lca-empty-chat__icon {

        width: 58px;

        height: 58px;



        display: grid;

        place-items: center;



        margin: 0 auto 12px;



        border:

            1px solid

            var(--lca-border);



        border-radius: 18px;



        background:

            linear-gradient(

                180deg,

                rgba(232, 190, 126, 0.08),

                rgba(232, 190, 126, 0.025)

            );



        font-size: 23px;

    }



    .lca-empty-chat strong {

        display: block;



        color: var(--lca-text);



        font-size: 14px;

    }



    .lca-empty-chat p {

        margin:

            6px

            0

            0;



        max-width: 320px;



        font-size: 11px;

    }



    /* Messages */



    .lca-message {

        width: fit-content;

        max-width: min(760px, 78%);



        padding: 11px 13px;



        margin:

            0

            0

            13px;



        border:

            1px solid

            rgba(255, 255, 255, 0.055);



        border-radius:

            15px

            15px

            15px

            5px;



        background:

            linear-gradient(

                180deg,

                #222a35,

                #1d242e

            );



        box-shadow:

            0 8px 24px

            rgba(0, 0, 0, 0.13);

    }



    .lca-message[data-sender="admin"] {

        margin-left: auto;



        border-color:

            rgba(232, 190, 126, 0.24);



        border-radius:

            15px

            15px

            5px

            15px;



        background:

            linear-gradient(

                180deg,

                #eccb94,

                #dcb475

            );



        color: #21170b;

    }



    .lca-msg-head {

        display: flex;

        align-items: center;

        gap: 8px;



        margin-bottom: 6px;

    }



    .lca-msg-head small {

        opacity: 0.72;



        font-size: 9px;

        font-weight: 800;

        letter-spacing: 0.03em;

    }



    .lca-avatar {

        width: 28px;

        height: 28px;

        flex: 0 0 28px;



        display: grid;

        place-items: center;



        overflow: hidden;



        border-radius: 50%;



        background: #343d4a;

        color: #fff;



        font-size: 10px;

        font-weight: 800;

    }



    .lca-avatar img {

        width: 100%;

        height: 100%;



        object-fit: cover;

    }



    .lca-message p {

        margin:

            3px

            0

            0;



        white-space: pre-wrap;

        overflow-wrap: anywhere;



        font-size: 13px;

        line-height: 1.55;

    }



    .lca-media-image {

        display: block;



        max-width: min(390px, 100%);

        max-height: 370px;



        margin-top: 8px;



        border-radius: 11px;



        object-fit: cover;



        box-shadow:

            0 8px 20px

            rgba(0, 0, 0, 0.14);

    }



    .lca-message audio {

        display: block;



        width: min(370px, 100%);



        margin-top: 8px;

    }



    .lca-file-link {

        display: inline-flex;

        align-items: center;

        gap: 7px;



        margin-top: 8px;



        text-decoration: none;

        font-weight: 650;

    }



    .lca-file-link:hover {

        text-decoration: underline;

    }



    .lca-delete {

        margin-top: 7px !important;



        padding: 2px 0 !important;



        border: 0 !important;



        background: transparent !important;



        color: inherit !important;



        opacity: 0.56;



        font-size: 9px !important;

    }



    .lca-delete:hover {

        opacity: 1;



        text-decoration: underline;

    }



    /* Scroll down */



    .lca-scroll-down {

        position: absolute;

        right: 18px;

        bottom: 100px;

        z-index: 5;



        width: 40px;

        height: 40px;



        display: grid;

        place-items: center;



        padding: 0 !important;



        border-radius: 50% !important;



        box-shadow:

            0 12px 30px

            rgba(0, 0, 0, 0.3);

    }





    /* ==========================================================================

       Typing indicator

       ========================================================================== */



    .lca-typing {

        display: flex;

        align-items: flex-end;

        gap: 9px;



        min-height: 46px;



        padding:

            7px

            22px

            5px;



        animation:

            lcaTypingAppear

            0.18s

            ease-out;

    }



    .lca-typing[hidden] {

        display: none !important;

    }



    .lca-typing__avatar {

        width: 28px;

        height: 28px;

        flex: 0 0 28px;



        display: grid;

        place-items: center;



        overflow: hidden;



        border:

            1px solid

            rgba(255, 255, 255, 0.08);



        border-radius: 50%;



        background: #343d49;

        color: #fff;



        font-size: 10px;

        font-weight: 800;

    }



    .lca-typing__avatar img {

        width: 100%;

        height: 100%;



        object-fit: cover;

    }



    .lca-typing__bubble {

        display: flex;

        align-items: center;

        gap: 4px;



        min-height: 36px;



        padding:

            10px

            13px;



        border:

            1px solid

            rgba(255, 255, 255, 0.055);



        border-radius:

            15px

            15px

            15px

            5px;



        background:

            linear-gradient(

                180deg,

                #222a35,

                #1d242e

            );



        box-shadow:

            0 8px 24px

            rgba(0, 0, 0, 0.12);

    }



    .lca-typing__dot {

        width: 6px;

        height: 6px;



        border-radius: 50%;



        background: #9299a4;



        animation:

            lcaTypingDot

            1.2s

            infinite

            ease-in-out;

    }



    .lca-typing__dot:nth-child(1) {

        animation-delay: 0s;

    }



    .lca-typing__dot:nth-child(2) {

        animation-delay: 0.15s;

    }



    .lca-typing__dot:nth-child(3) {

        animation-delay: 0.30s;

    }



    .lca-typing__label {

        align-self: center;



        color: var(--lca-muted-2);



        font-size: 9px;

    }



    @keyframes lcaTypingDot {

        0%,

        60%,

        100% {

            opacity: 0.35;

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



    @media (prefers-reduced-motion: reduce) {

        .lca-typing {

            animation: none;

        }



        .lca-typing__dot {

            animation: none;

            opacity: 0.7;

        }

    }



    /* ==========================================================================

       Composer

       ========================================================================== */



    .lca-composer-wrap {

        position: relative;



        padding:

            11px

            14px

            14px;



        border-top: 1px solid var(--lca-border);



        background:

            linear-gradient(

                180deg,

                rgba(255, 255, 255, 0.01),

                rgba(255, 255, 255, 0.022)

            ),

            #0d1015;

    }



    .lca-composer-info {

        min-height: 18px;



        display: flex;

        align-items: center;

        justify-content: space-between;



        gap: 12px;



        margin-bottom: 7px;



        color: var(--lca-muted-2);



        font-size: 9px;

    }



    .lca-recorder-state {

        color: var(--lca-accent);



        font-weight: 750;

    }



    .lca-form {

        display: grid;

        grid-template-columns:

            44px

            44px

            minmax(0, 1fr)

            auto;



        gap: 9px;



        align-items: end;



        width: 100%;

        min-width: 0;

    }



    .lca-tool {

        width: 44px;

        height: 44px;

        flex: 0 0 44px;



        display: grid;

        place-items: center;



        padding: 0 !important;



        border-radius: 11px !important;



        font-size: 17px;



        user-select: none;

    }



    .lca-tool[aria-pressed="true"] {

        border-color:

            rgba(226, 115, 115, 0.4) !important;



        background:

            rgba(226, 115, 115, 0.18) !important;



        color: #fff !important;



        box-shadow:

            0 0 0 3px

            rgba(226, 115, 115, 0.08);

    }



    .lca textarea {

        width: 100%;

        min-width: 0;



        min-height: 44px;

        max-height: 150px;



        resize: none;



        padding:

            11px

            13px;



        border:

            1px solid

            var(--lca-border-strong);



        border-radius:

            11px;



        background: #080b0f;

        color: var(--lca-text);



        font-size: 13px;

        line-height: 1.45;

    }



    .lca textarea::placeholder {

        color: var(--lca-muted-2);

    }



    .lca-submit {

        min-width: 100px;

        height: 44px;



        padding-inline: 18px !important;



        border-color: transparent !important;



        background:

            linear-gradient(

                180deg,

                #f0cf9a,

                #dbae6d

            ) !important;



        color:

            #211609 !important;



        font-weight: 800;



        box-shadow:

            0 10px 24px

            rgba(219, 174, 109, 0.14);

    }



    .lca-submit:hover:not(:disabled) {

        transform: translateY(-1px);



        background:

            linear-gradient(

                180deg,

                #f5d9ab,

                #e0b979

            ) !important;

    }



    /* ==========================================================================

       Toasts

       ========================================================================== */



    .lca-toast-stack {

        position: fixed;

        right: 22px;

        bottom: 22px;

        z-index: 9999;



        display: grid;

        gap: 8px;



        width:

            min(

                360px,

                calc(100vw - 32px)

            );



        pointer-events: none;

    }



    .lca-toast {

        padding:

            11px

            13px;



        border:

            1px solid

            var(--lca-border-strong);



        border-radius:

            var(--lca-radius-sm);



        background: #171d25;

        color: var(--lca-text);



        box-shadow:

            0 16px 40px

            rgba(0, 0, 0, 0.36);



        font-size: 11px;



        animation:

            lcaToastIn

            0.18s

            ease-out;

    }



    .lca-toast[data-kind="success"] {

        border-color:

            rgba(107, 200, 149, 0.28);

    }



    .lca-toast[data-kind="error"] {

        border-color:

            rgba(226, 115, 115, 0.30);



        color: #ffc6c6;

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

       Responsive

       ========================================================================== */



    @media (max-width: 1150px) {

        .lca-grid {

            grid-template-columns:

                290px

                minmax(0, 1fr);

        }



        .lca-message {

            max-width: 84%;

        }



        .lca-stats {

            grid-template-columns:

                repeat(2, minmax(0, 1fr));

        }

    }



    @media (max-width: 860px) {

        .lca-grid {

            grid-template-columns: 1fr;



            height: auto;

            min-height: 0;

        }



        .lca-inbox {

            max-height: 340px;



            border-right: 0;

            border-bottom:

                1px solid

                var(--lca-border);

        }



        .lca-detail {

            min-height: 590px;

            height: 69vh;

        }



        .lca-presence {

            width: 100%;

        }



        .lca-stats {

            grid-template-columns: 1fr;

        }

    }



    @media (max-width: 650px) {

        .lca-top {

            padding: 16px;

        }



        .lca-log {

            padding: 14px;

        }



        .lca-heading {

            align-items: flex-start;



            padding: 13px;

        }



        .lca-heading__actions {

            align-self: center;

        }



        .lca-message {

            max-width: 94%;

        }



        .lca-form {

            grid-template-columns:

                44px

                44px

                minmax(0, 1fr);

        }



        .lca-submit {

            grid-column: 1 / -1;



            width: 100%;

        }



        .lca-composer-wrap {

            padding: 10px;

        }



        .lca-composer-info {

            align-items: flex-start;

            flex-direction: column;

            gap: 2px;

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



<script

    src="{{ asset('js/admin-live-chat.js') }}?v=60"

    defer

></script>

@endsection
