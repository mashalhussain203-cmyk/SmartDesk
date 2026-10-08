<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, viewport-fit=cover"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <meta
        name="theme-color"
        content="#f7f9fc"
    >

    <title>Mashal Mail | Inbox</title>

    <style>
        /*
        |--------------------------------------------------------------------------
        | Reset
        |--------------------------------------------------------------------------
        */

        * {
            box-sizing: border-box;
        }

        html {
            min-height: 100%;
            -webkit-text-size-adjust: 100%;
        }

        body,
        h1,
        h2,
        h3,
        p {
            margin: 0;
        }

        button,
        input,
        textarea {
            font: inherit;
        }

        button,
        a {
            -webkit-tap-highlight-color: transparent;
        }

        button {
            cursor: pointer;
        }

        a {
            color: inherit;
            text-decoration: none;
        }


        /*
        |--------------------------------------------------------------------------
        | Design tokens
        |--------------------------------------------------------------------------
        */

        :root {
            --app-bg: #f7f9fc;
            --surface: #ffffff;
            --surface-soft: #f8fafc;
            --surface-hover: #f2f6fc;

            --border: #e3e8ef;
            --border-strong: #d5dce5;

            --text: #18212f;
            --text-soft: #4f5d6f;
            --muted: #6b7788;

            --primary: #8f82ff;
            --primary-hover: #6f63e8;
            --primary-soft: #e7e3ff;
            --primary-softer: #f3f1ff;

            --compose: #d9d4ff;
            --compose-hover: #c9c2ff;

            --success-bg: #e7f6ed;
            --success-text: #126c3a;

            --error-bg: #fdeceb;
            --error-text: #a62b24;

            --warning-bg: #fff6dc;
            --warning-text: #765200;

            --shadow-sm:
                0 1px 2px rgba(15, 23, 42, 0.06),
                0 1px 3px rgba(15, 23, 42, 0.08);

            --shadow-md:
                0 8px 24px rgba(15, 23, 42, 0.10);

            --shadow-lg:
                0 20px 60px rgba(15, 23, 42, 0.20);

            --radius-sm: 10px;
            --radius-md: 16px;
            --radius-lg: 22px;

            --header-height: 76px;
        }


        /*
        |--------------------------------------------------------------------------
        | Base
        |--------------------------------------------------------------------------
        */

        body {
            min-height: 100vh;
            min-height: 100dvh;

            background:
                radial-gradient(
                    circle at top right,
                    rgba(122, 108, 255, 0.06),
                    transparent 28rem
                ),
                var(--app-bg);

            color: var(--text);

            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Roboto,
                Helvetica,
                Arial,
                sans-serif;

            overflow-x: hidden;
        }

        body.compose-open {
            overflow: hidden;
        }

        .app-shell {
            min-height: 100vh;
            min-height: 100dvh;

            display: flex;
            flex-direction: column;
        }


        /*
        |--------------------------------------------------------------------------
        | Accessibility
        |--------------------------------------------------------------------------
        */

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        :focus-visible {
            outline: 3px solid rgba(122, 108, 255, 0.28);
            outline-offset: 2px;
        }


        /*
        |--------------------------------------------------------------------------
        | Topbar
        |--------------------------------------------------------------------------
        */

        .topbar {
            min-height: var(--header-height);

            position: sticky;
            top: 0;
            z-index: 40;

            display: grid;
            grid-template-columns: 250px minmax(220px, 760px) 1fr;
            align-items: center;
            gap: 20px;

            padding:
                12px
                max(18px, env(safe-area-inset-right))
                12px
                max(18px, env(safe-area-inset-left));

            background:
                rgba(247, 249, 252, 0.92);

            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);

            border-bottom:
                1px solid rgba(213, 220, 229, 0.72);
        }

        .brand {
            min-width: 0;

            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-logo {
            width: 44px;
            height: 44px;

            flex-shrink: 0;

            display: grid;
            place-items: center;

            border-radius: 14px;

            background:
                linear-gradient(
                    145deg,
                    #8f82ff,
                    #4d88ff
                );

            color: #ffffff;

            box-shadow:
                0 6px 16px rgba(122, 108, 255, 0.24);

            font-size: 19px;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .brand-copy {
            min-width: 0;

            display: flex;
            flex-direction: column;
        }

        .brand-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;

            font-size: 17px;
            font-weight: 760;
            letter-spacing: -0.02em;
        }

        .brand-description {
            margin-top: 2px;

            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;

            color: var(--muted);

            font-size: 11px;
            font-weight: 550;
        }


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        .search-area {
            width: 100%;
            min-width: 0;

            justify-self: center;
        }

        .search-form {
            width: 100%;
            min-width: 0;

            position: relative;
        }

        .search-input {
            width: 100%;
            height: 50px;

            padding:
                0 50px 0 48px;

            border:
                1px solid transparent;

            border-radius: 18px;

            background: #f0edff;

            color: var(--text);

            outline: none;

            transition:
                background 0.16s ease,
                border-color 0.16s ease,
                box-shadow 0.16s ease;
        }

        .search-input::placeholder {
            color: #697586;
        }

        .search-input:focus {
            background: #ffffff;

            border-color:
                var(--border-strong);

            box-shadow:
                var(--shadow-md);
        }

        .search-icon {
            width: 22px;
            height: 22px;

            position: absolute;
            left: 16px;
            top: 50%;

            transform: translateY(-50%);

            display: grid;
            place-items: center;

            color: var(--muted);

            pointer-events: none;
        }

        .search-clear {
            width: 34px;
            height: 34px;

            position: absolute;
            right: 9px;
            top: 50%;

            transform: translateY(-50%);

            display: grid;
            place-items: center;

            border-radius: 50%;

            color: var(--muted);

            font-size: 22px;
            line-height: 1;
        }

        .search-clear:hover {
            background:
                rgba(24, 33, 47, 0.07);

            color: var(--text);
        }


        /*
        |--------------------------------------------------------------------------
        | Header actions
        |--------------------------------------------------------------------------
        */

        .header-actions {
            min-width: 0;

            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }

        .header-button {
            min-height: 40px;

            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;

            padding: 0 14px;

            border:
                1px solid var(--border);

            border-radius: 12px;

            background:
                rgba(255, 255, 255, 0.82);

            color: var(--text-soft);

            font-size: 13px;
            font-weight: 700;
        }

        .header-button:hover {
            background: #ffffff;

            border-color:
                var(--border-strong);
        }

        .account-pill {
            max-width: 250px;
            min-width: 0;

            display: flex;
            align-items: center;
            gap: 9px;

            padding: 6px 10px 6px 7px;

            border:
                1px solid var(--border);

            border-radius: 999px;

            background:
                rgba(255, 255, 255, 0.92);
        }

        .account-avatar {
            width: 30px;
            height: 30px;

            flex-shrink: 0;

            display: grid;
            place-items: center;

            border-radius: 50%;

            background: var(--primary-soft);
            color: #5f57a8;

            font-size: 12px;
            font-weight: 800;
        }

        .account-email {
            min-width: 0;

            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;

            color: var(--text-soft);

            font-size: 12px;
            font-weight: 650;
        }


        /*
        |--------------------------------------------------------------------------
        | Desktop layout
        |--------------------------------------------------------------------------
        */

        .workspace {
            flex: 1;

            width: 100%;

            display: grid;
            grid-template-columns: 260px minmax(0, 1fr);
            gap: 18px;

            padding:
                18px
                max(18px, env(safe-area-inset-right))
                calc(24px + env(safe-area-inset-bottom))
                max(18px, env(safe-area-inset-left));
        }


        /*
        |--------------------------------------------------------------------------
        | Sidebar
        |--------------------------------------------------------------------------
        */

        .sidebar {
            min-width: 0;
        }

        .sidebar-sticky {
            position: sticky;
            top: calc(var(--header-height) + 18px);

            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .compose-button {
            min-height: 56px;

            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;

            padding: 0 20px;

            border: 0;
            border-radius: 18px;

            background: var(--compose);
            color: #08304f;

            box-shadow:
                0 5px 14px rgba(122, 108, 255, 0.12);

            font-size: 14px;
            font-weight: 760;

            transition:
                background 0.15s ease,
                transform 0.15s ease,
                box-shadow 0.15s ease;
        }

        .compose-button:hover {
            background: var(--compose-hover);

            box-shadow:
                0 7px 18px rgba(122, 108, 255, 0.17);
        }

        .compose-button:active {
            transform: scale(0.985);
        }

        .side-nav {
            display: flex;
            flex-direction: column;
            gap: 4px;

            padding: 7px;
        }

        .side-nav-link {
            min-height: 44px;

            display: flex;
            align-items: center;
            gap: 12px;

            padding: 0 14px;

            border-radius: 13px;

            color: var(--text-soft);

            font-size: 13px;
            font-weight: 650;
        }

        .side-nav-link:hover {
            background: var(--surface-hover);
        }

        .side-nav-link.active {
            background: var(--primary-soft);
            color: #5f57a8;

            font-weight: 760;
        }

        .nav-icon {
            width: 22px;

            text-align: center;

            font-size: 17px;
        }

        .connection-card {
            padding: 16px;

            border:
                1px solid var(--border);

            border-radius: 18px;

            background:
                rgba(255, 255, 255, 0.88);

            box-shadow: var(--shadow-sm);
        }

        .connection-title {
            margin-bottom: 6px;

            font-size: 13px;
            font-weight: 780;
        }

        .connection-label {
            margin-bottom: 3px;

            color: var(--muted);

            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .connection-email {
            overflow-wrap: anywhere;

            color: var(--text-soft);

            font-size: 12px;
            line-height: 1.45;
        }

        .connection-status {
            margin-top: 12px;

            display: flex;
            align-items: center;
            gap: 7px;

            color: #167343;

            font-size: 11px;
            font-weight: 700;
        }

        .status-dot {
            width: 8px;
            height: 8px;

            flex-shrink: 0;

            border-radius: 50%;

            background: #1a9b59;

            box-shadow:
                0 0 0 4px rgba(26, 155, 89, 0.10);
        }

        .disconnect-form {
            margin-top: 15px;
        }

        .disconnect-button {
            width: 100%;
            min-height: 40px;

            border:
                1px solid #f0b7b3;

            border-radius: 11px;

            background: #ffffff;

            color: var(--error-text);

            font-size: 12px;
            font-weight: 760;
        }

        .disconnect-button:hover {
            background: var(--error-bg);
        }


        /*
        |--------------------------------------------------------------------------
        | Main mailbox
        |--------------------------------------------------------------------------
        */

        .mailbox {
            min-width: 0;

            align-self: start;

            overflow: hidden;

            border:
                1px solid rgba(227, 232, 239, 0.88);

            border-radius: 22px;

            background:
                rgba(255, 255, 255, 0.96);

            box-shadow: var(--shadow-sm);
        }

        .mailbox-top {
            min-height: 70px;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;

            padding: 14px 20px;

            border-bottom:
                1px solid var(--border);
        }

        .mailbox-heading {
            min-width: 0;
        }

        .mailbox-title {
            font-size: 20px;
            font-weight: 760;
            letter-spacing: -0.02em;
        }

        .mailbox-subtitle {
            margin-top: 3px;

            color: var(--muted);

            font-size: 11px;
            line-height: 1.4;
        }

        .mailbox-tools {
            flex-shrink: 0;

            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tool-button {
            min-height: 38px;

            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;

            padding: 0 13px;

            border:
                1px solid var(--border);

            border-radius: 11px;

            background: #ffffff;

            color: var(--text-soft);

            font-size: 12px;
            font-weight: 700;
        }

        .tool-button:hover {
            background: var(--surface-hover);
        }


        /*
        |--------------------------------------------------------------------------
        | Alerts
        |--------------------------------------------------------------------------
        */

        .alerts {
            display: grid;
            gap: 10px;

            padding: 16px 16px 0;
        }

        .alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;

            padding: 13px 14px;

            border-radius: 13px;

            font-size: 13px;
            line-height: 1.5;
        }

        .alert-success {
            background: var(--success-bg);
            color: var(--success-text);
        }

        .alert-error {
            background: var(--error-bg);
            color: var(--error-text);
        }

        .alert-warning {
            background: var(--warning-bg);
            color: var(--warning-text);
        }

        .alert ul {
            margin:
                7px 0 0 18px;
            padding: 0;
        }


        /*
        |--------------------------------------------------------------------------
        | Message rows
        |--------------------------------------------------------------------------
        */

        .message-list {
            width: 100%;
        }

        .message-row {
            width: 100%;
            min-width: 0;
            min-height: 64px;

            display: grid;
            grid-template-columns:
                minmax(155px, 230px)
                minmax(0, 1fr)
                minmax(100px, 170px);

            align-items: center;
            gap: 16px;

            padding: 9px 20px;

            border-bottom:
                1px solid #edf0f4;

            background: #ffffff;

            transition:
                background 0.12s ease,
                box-shadow 0.12s ease;
        }

        .message-row:last-child {
            border-bottom: 0;
        }

        .message-row:hover {
            position: relative;
            z-index: 2;

            background: #faf9ff;

            box-shadow:
                inset 3px 0 0 var(--primary);
        }

        .message-sender {
            min-width: 0;

            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sender-avatar {
            width: 34px;
            height: 34px;

            flex-shrink: 0;

            display: grid;
            place-items: center;

            border-radius: 50%;

            background: var(--primary-softer);
            color: #7468d8;

            font-size: 12px;
            font-weight: 800;
        }

        .sender-name {
            min-width: 0;

            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;

            font-size: 13px;
            font-weight: 760;
        }

        .message-preview {
            min-width: 0;

            display: flex;
            align-items: baseline;
            gap: 7px;
        }

        .message-subject {
            max-width: 52%;
            flex-shrink: 0;

            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;

            font-size: 13px;
            font-weight: 700;
        }

        .message-snippet {
            min-width: 0;

            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;

            color: var(--muted);

            font-size: 12px;
        }

        .message-date {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;

            color: var(--muted);

            text-align: right;

            font-size: 11px;
            font-weight: 650;
        }


        /*
        |--------------------------------------------------------------------------
        | Empty / connection state
        |--------------------------------------------------------------------------
        */

        .state-panel {
            min-height: 520px;

            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;

            padding: 50px 24px;

            text-align: center;
        }

        .state-illustration {
            width: 92px;
            height: 92px;

            display: grid;
            place-items: center;

            margin-bottom: 22px;

            border-radius: 28px;

            background:
                linear-gradient(
                    145deg,
                    #f3f1ff,
                    #e4e0ff
                );

            color: var(--primary);

            box-shadow:
                inset 0 0 0 1px rgba(122, 108, 255, 0.08);

            font-size: 38px;
        }

        .state-title {
            max-width: 540px;

            font-size: 24px;
            font-weight: 790;
            letter-spacing: -0.025em;
        }

        .state-text {
            max-width: 570px;

            margin-top: 10px;

            color: var(--muted);

            font-size: 13px;
            line-height: 1.7;
        }

        .state-actions {
            margin-top: 22px;

            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
        }

        .primary-button {
            min-height: 45px;

            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;

            padding: 0 18px;

            border: 0;
            border-radius: 12px;

            background: var(--primary);
            color: #ffffff;

            box-shadow:
                0 5px 14px rgba(122, 108, 255, 0.18);

            font-size: 13px;
            font-weight: 760;
        }

        .primary-button:hover {
            background: var(--primary-hover);
        }


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        .mailbox-footer {
            min-height: 62px;

            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;

            padding: 12px 18px;

            border-top:
                1px solid var(--border);

            background: var(--surface-soft);
        }

        .next-page-button {
            min-height: 38px;

            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;

            padding: 0 14px;

            border:
                1px solid var(--border);

            border-radius: 11px;

            background: #ffffff;

            color: var(--text-soft);

            font-size: 12px;
            font-weight: 720;
        }

        .next-page-button:hover {
            background: var(--surface-hover);
        }


        /*
        |--------------------------------------------------------------------------
        | Compose overlay
        |--------------------------------------------------------------------------
        */

        .compose-overlay {
            position: fixed;
            inset: 0;
            z-index: 90;

            display: none;

            background:
                rgba(15, 23, 42, 0.38);

            backdrop-filter: blur(2px);
            -webkit-backdrop-filter: blur(2px);
        }

        .compose-overlay.active {
            display: block;
        }

        .compose-window {
            width:
                min(620px, calc(100vw - 32px));

            max-height:
                calc(100dvh - 36px);

            position: fixed;
            right: 24px;
            bottom: 0;
            z-index: 100;

            display: none;
            flex-direction: column;

            overflow: hidden;

            border:
                1px solid rgba(213, 220, 229, 0.9);

            border-bottom: 0;

            border-radius:
                20px 20px 0 0;

            background: #ffffff;

            box-shadow: var(--shadow-lg);
        }

        .compose-window.active {
            display: flex;
        }

        .compose-header {
            min-height: 52px;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;

            padding: 0 15px 0 18px;

            background: #f4f7fb;

            border-bottom:
                1px solid var(--border);
        }

        .compose-heading {
            font-size: 13px;
            font-weight: 780;
        }

        .compose-close {
            width: 36px;
            height: 36px;

            display: grid;
            place-items: center;

            border: 0;
            border-radius: 50%;

            background: transparent;

            color: var(--muted);

            font-size: 23px;
            line-height: 1;
        }

        .compose-close:hover {
            background:
                rgba(24, 33, 47, 0.08);

            color: var(--text);
        }

        .compose-form {
            min-height: 500px;

            display: flex;
            flex-direction: column;
        }

        .compose-input {
            width:
                calc(100% - 32px);

            min-height: 48px;

            margin: 0 16px;

            border: 0;

            border-bottom:
                1px solid var(--border);

            background: transparent;

            color: var(--text);

            outline: none;
        }

        .compose-textarea {
            flex: 1;

            width: 100%;
            min-height: 290px;

            padding: 17px;

            border: 0;

            background: #ffffff;

            color: var(--text);

            outline: none;

            resize: none;

            line-height: 1.65;
        }

        .compose-footer {
            min-height: 66px;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;

            padding:
                11px 16px
                calc(11px + env(safe-area-inset-bottom));

            border-top:
                1px solid var(--border);
        }

        .send-button {
            min-width: 112px;
            min-height: 42px;

            border: 0;
            border-radius: 999px;

            background: var(--primary);
            color: #ffffff;

            font-size: 13px;
            font-weight: 780;
        }

        .send-button:hover {
            background: var(--primary-hover);
        }

        .compose-from {
            min-width: 0;

            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;

            color: var(--muted);

            font-size: 11px;
        }


        /*
        |--------------------------------------------------------------------------
        | Mobile bottom navigation
        |--------------------------------------------------------------------------
        */

        .mobile-nav {
            display: none;
        }


        /*
        |--------------------------------------------------------------------------
        | Tablet
        |--------------------------------------------------------------------------
        */

        @media (max-width: 1050px) {
            .topbar {
                grid-template-columns:
                    210px minmax(180px, 1fr) auto;
            }

            .workspace {
                grid-template-columns:
                    220px minmax(0, 1fr);

                gap: 14px;
            }

            .message-row {
                grid-template-columns:
                    minmax(140px, 190px)
                    minmax(0, 1fr)
                    120px;
            }

            .account-email {
                display: none;
            }

            .account-pill {
                padding-right: 7px;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Small tablet / phone
        |--------------------------------------------------------------------------
        */

        @media (max-width: 820px) {
            :root {
                --header-height: auto;
            }

            body {
                padding-bottom:
                    calc(72px + env(safe-area-inset-bottom));
            }

            .topbar {
                position: sticky;

                display: grid;
                grid-template-columns: 1fr auto;
                gap: 10px;

                padding:
                    10px
                    max(12px, env(safe-area-inset-right))
                    10px
                    max(12px, env(safe-area-inset-left));
            }

            .brand-logo {
                width: 40px;
                height: 40px;

                border-radius: 12px;
            }

            .brand-description {
                display: none;
            }

            .search-area {
                grid-column: 1 / -1;
                grid-row: 2;
            }

            .search-input {
                height: 46px;

                border-radius: 15px;
            }

            .header-button {
                display: none;
            }

            .account-pill {
                border: 0;
                background: transparent;
            }

            .account-avatar {
                width: 36px;
                height: 36px;
            }

            .workspace {
                display: block;

                padding:
                    10px
                    max(10px, env(safe-area-inset-right))
                    16px
                    max(10px, env(safe-area-inset-left));
            }

            .sidebar {
                display: none;
            }

            .mailbox {
                border-radius: 18px;
            }

            .mailbox-top {
                min-height: 62px;

                padding: 12px 14px;
            }

            .mailbox-title {
                font-size: 18px;
            }

            .tool-button .desktop-label {
                display: none;
            }

            .tool-button {
                width: 40px;
                height: 40px;
                min-height: 40px;

                padding: 0;

                border-radius: 50%;
            }

            .message-row {
                min-height: 82px;

                grid-template-columns:
                    42px minmax(0, 1fr) auto;

                grid-template-areas:
                    "avatar sender date"
                    "avatar preview preview";

                align-items: center;

                gap:
                    3px 10px;

                padding: 11px 13px;
            }

            .message-row:hover {
                box-shadow: none;
            }

            .message-sender {
                display: contents;
            }

            .sender-avatar {
                grid-area: avatar;

                width: 40px;
                height: 40px;
            }

            .sender-name {
                grid-area: sender;

                font-size: 13px;
            }

            .message-preview {
                grid-area: preview;

                display: block;
            }

            .message-subject {
                max-width: 100%;

                display: block;

                margin-bottom: 3px;

                font-size: 12px;
            }

            .message-snippet {
                display: block;

                font-size: 11px;
            }

            .message-date {
                grid-area: date;

                max-width: 85px;

                font-size: 10px;
            }

            .mailbox-footer {
                justify-content: center;
            }

            .next-page-button {
                width: 100%;

                min-height: 44px;
            }

            .state-panel {
                min-height: 430px;

                padding:
                    40px 20px;
            }

            .state-title {
                font-size: 22px;
            }

            .mobile-nav {
                height:
                    calc(64px + env(safe-area-inset-bottom));

                position: fixed;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 70;

                display: grid;
                grid-template-columns:
                    1fr 1fr 1fr;

                padding:
                    7px
                    max(8px, env(safe-area-inset-right))
                    calc(7px + env(safe-area-inset-bottom))
                    max(8px, env(safe-area-inset-left));

                background:
                    rgba(255, 255, 255, 0.96);

                backdrop-filter: blur(18px);
                -webkit-backdrop-filter: blur(18px);

                border-top:
                    1px solid var(--border);
            }

            .mobile-nav-button {
                min-width: 0;

                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 3px;

                border: 0;
                border-radius: 13px;

                background: transparent;

                color: var(--muted);

                font-size: 10px;
                font-weight: 680;
            }

            .mobile-nav-button.active {
                color: var(--primary);
            }

            .mobile-nav-icon {
                font-size: 20px;
                line-height: 1;
            }

            .compose-window {
                width: 100%;
                height: 100dvh;
                max-height: 100dvh;

                right: 0;
                bottom: 0;

                border: 0;
                border-radius: 0;
            }

            .compose-header {
                padding-top:
                    env(safe-area-inset-top);

                min-height:
                    calc(56px + env(safe-area-inset-top));
            }

            .compose-form {
                min-height: 0;
                flex: 1;
            }

            .compose-textarea {
                min-height: 180px;
            }

            .compose-footer {
                min-height:
                    calc(66px + env(safe-area-inset-bottom));
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Very small phones
        |--------------------------------------------------------------------------
        */

        @media (max-width: 430px) {
            .brand-name {
                font-size: 16px;
            }

            .mailbox-subtitle {
                max-width: 230px;
            }

            .alerts {
                padding:
                    10px 10px 0;
            }

            .alert {
                font-size: 12px;
            }

            .message-row {
                padding:
                    10px 11px;
            }

            .state-illustration {
                width: 80px;
                height: 80px;

                border-radius: 24px;

                font-size: 32px;
            }

            .state-title {
                font-size: 20px;
            }

            .state-text {
                font-size: 12px;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Reduced motion
        |--------------------------------------------------------------------------
        */

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                transition: none !important;
                animation: none !important;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Extended mailbox navigation
        |--------------------------------------------------------------------------
        */

        .side-nav-section-title {
            padding: 8px 14px 5px;
            color: var(--muted);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .side-nav-divider {
            height: 1px;
            margin: 7px 8px;
            background: var(--border);
        }

        .nav-copy {
            min-width: 0;
            flex: 1;
        }

        .nav-label {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .party-prefix {
            color: var(--muted);
            font-size: 10px;
            font-weight: 700;
        }

        .unread-dot {
            width: 7px;
            height: 7px;
            display: inline-block;
            margin-left: 5px;
            border-radius: 50%;
            background: var(--primary);
            vertical-align: middle;
        }

        .message-row.is-unread {
            background: #faf9ff;
        }

        .message-row.is-unread .sender-name,
        .message-row.is-unread .message-subject {
            color: #5f57a8;
            font-weight: 800;
        }

        .mobile-folder-bar {
            display: none;
        }

        .folder-menu-overlay,
        .folder-menu-sheet {
            display: none;
        }

        @media (max-width: 820px) {
            .mobile-folder-bar {
                display: flex;
                gap: 8px;
                width: 100%;
                margin-bottom: 10px;
                padding: 2px 1px 5px;
                overflow-x: auto;
                overscroll-behavior-inline: contain;
                scrollbar-width: none;
                scroll-snap-type: x proximity;
            }

            .mobile-folder-bar::-webkit-scrollbar {
                display: none;
            }

            .mobile-folder-chip {
                min-height: 39px;
                flex: 0 0 auto;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 7px;
                padding: 0 13px;
                border: 1px solid var(--border);
                border-radius: 999px;
                background: #ffffff;
                color: var(--text-soft);
                scroll-snap-align: start;
                font-size: 11px;
                font-weight: 730;
                box-shadow: var(--shadow-sm);
            }

            .mobile-folder-chip.active {
                border-color: rgba(122, 108, 255, 0.18);
                background: var(--primary-soft);
                color: #5f57a8;
            }

            .folder-menu-overlay {
                position: fixed;
                inset: 0;
                z-index: 104;
                display: block;
                opacity: 0;
                visibility: hidden;
                background: rgba(15, 23, 42, 0.42);
                backdrop-filter: blur(2px);
                -webkit-backdrop-filter: blur(2px);
                transition: opacity 0.18s ease, visibility 0.18s ease;
            }

            .folder-menu-overlay.active {
                opacity: 1;
                visibility: visible;
            }

            .folder-menu-sheet {
                width: 100%;
                max-height: min(78dvh, 640px);
                position: fixed;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 105;
                display: block;
                padding: 8px 12px calc(18px + env(safe-area-inset-bottom));
                overflow-y: auto;
                border-radius: 24px 24px 0 0;
                background: #ffffff;
                box-shadow: 0 -18px 50px rgba(15, 23, 42, 0.20);
                transform: translateY(104%);
                visibility: hidden;
                transition: transform 0.22s ease, visibility 0.22s ease;
            }

            .folder-menu-sheet.active {
                transform: translateY(0);
                visibility: visible;
            }

            .folder-sheet-handle {
                width: 42px;
                height: 5px;
                margin: 2px auto 12px;
                border-radius: 999px;
                background: #d5dce5;
            }

            .folder-sheet-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                padding: 4px 5px 13px;
            }

            .folder-sheet-eyebrow {
                margin-bottom: 2px;
                color: var(--primary);
                font-size: 10px;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .folder-sheet-title {
                font-size: 20px;
                font-weight: 790;
                letter-spacing: -0.025em;
            }

            .folder-sheet-close {
                width: 40px;
                height: 40px;
                flex-shrink: 0;
                display: grid;
                place-items: center;
                border: 0;
                border-radius: 50%;
                background: var(--surface-hover);
                color: var(--text-soft);
                font-size: 24px;
            }

            .folder-sheet-list {
                display: grid;
                gap: 7px;
            }

            .folder-sheet-link {
                min-height: 64px;
                display: grid;
                grid-template-columns: 42px minmax(0, 1fr) auto;
                align-items: center;
                gap: 11px;
                padding: 8px 12px;
                border: 1px solid transparent;
                border-radius: 16px;
                background: var(--surface-soft);
            }

            .folder-sheet-link.active {
                border-color: rgba(122, 108, 255, 0.15);
                background: var(--primary-softer);
            }

            .folder-sheet-icon {
                width: 40px;
                height: 40px;
                display: grid;
                place-items: center;
                border-radius: 13px;
                background: #ffffff;
                box-shadow: var(--shadow-sm);
                font-size: 19px;
            }

            .folder-sheet-copy {
                min-width: 0;
            }

            .folder-sheet-copy strong,
            .folder-sheet-copy small {
                display: block;
            }

            .folder-sheet-copy strong {
                margin-bottom: 2px;
                color: var(--text);
                font-size: 13px;
            }

            .folder-sheet-copy small {
                overflow: hidden;
                color: var(--muted);
                text-overflow: ellipsis;
                white-space: nowrap;
                font-size: 10px;
            }

            .folder-sheet-check {
                width: 28px;
                height: 28px;
                display: grid;
                place-items: center;
                border-radius: 50%;
                background: var(--primary);
                color: #ffffff;
                font-size: 12px;
                font-weight: 800;
            }

            body.folder-menu-open {
                overflow: hidden;
            }
        }

    </style>
    <link rel="stylesheet" href="{{ asset('css/mobile-responsive.css') }}?v=20261008-3">
</head>

<body>

@php
    $currentUser = auth()->user();

    $currentEmail = $currentUser?->email ?? '';

    $gmailEmail =
        $currentUser?->google_gmail_email
        ?: $currentEmail;

    $avatarLetter =
        $currentEmail !== ''
            ? mb_strtoupper(
                mb_substr(
                    $currentEmail,
                    0,
                    1
                )
            )
            : 'M';

    $isGmailAccount =
        str_ends_with(
            strtolower($currentEmail),
            '@gmail.com'
        );

    $folder = $folder ?? 'inbox';
    $folderTitle = $folderTitle ?? 'Inbox';

    $folders = [
        'inbox' => [
            'title' => 'Inbox',
            'icon' => '📥',
            'description' => 'Je ontvangen Gmail-berichten',
            'empty_title' => 'Je inbox is leeg',
            'empty_text' => 'Er staan momenteel geen berichten in je Gmail-inbox.',
        ],
        'archive' => [
            'title' => 'Gearchiveerd',
            'icon' => '📦',
            'description' => 'Berichten die niet meer in je inbox staan',
            'empty_title' => 'Geen gearchiveerde berichten',
            'empty_text' => 'Je hebt momenteel geen berichten in het archief.',
        ],
        'sent' => [
            'title' => 'Verzonden',
            'icon' => '📤',
            'description' => 'Berichten die je via Gmail hebt verzonden',
            'empty_title' => 'Nog niets verzonden',
            'empty_text' => 'Je hebt momenteel geen verzonden berichten om te tonen.',
        ],
        'spam' => [
            'title' => 'Spam',
            'icon' => '⚠️',
            'description' => 'Berichten die Gmail als spam heeft gemarkeerd',
            'empty_title' => 'Geen spam',
            'empty_text' => 'Mooi: er staan momenteel geen berichten in je spammap.',
        ],
        'trash' => [
            'title' => 'Prullenbak',
            'icon' => '🗑️',
            'description' => 'Verwijderde Gmail-berichten',
            'empty_title' => 'Prullenbak is leeg',
            'empty_text' => 'Er staan momenteel geen berichten in je prullenbak.',
        ],
    ];

    $currentFolder = $folders[$folder] ?? $folders['inbox'];
@endphp


<div class="app-shell">

    {{-- =========================================================
         TOPBAR
    ========================================================== --}}

    <header class="topbar">

        <a
            href="{{ route('home') }}"
            class="brand"
            aria-label="Terug naar Mashal Studio"
        >
            <div class="brand-logo">
                M
            </div>

            <div class="brand-copy">
                <div class="brand-name">
                    {{ __('Mashal Mail') }}
                </div>

                <div class="brand-description">
                    {{ __('Je Gmail, rechtstreeks in Mashal Studio') }}
                </div>
            </div>
        </a>


        @if($connected ?? false)

            <div class="search-area">

                <form
                    method="GET"
                    action="{{ route('gmail.inbox') }}"
                    class="search-form"
                    role="search"
                >
                    <input
                        type="hidden"
                        name="folder"
                        value="{{ $folder }}"
                    >

                    <span
                        class="search-icon"
                        aria-hidden="true"
                    >
                        🔍
                    </span>

                    <label
                        for="gmail-search"
                        class="sr-only"
                    >
                        {{ __('Zoeken in Gmail') }}
                    </label>

                    <input
                        id="gmail-search"
                        type="search"
                        name="q"
                        class="search-input"
                        value="{{ $query ?? '' }}"
                        placeholder="Zoeken in {{ strtolower($folderTitle) }}"
                        autocomplete="off"
                        enterkeyhint="search"
                    >

                    @if(!empty($query))
                        <a
                            href="{{ route('gmail.inbox', ['folder' => $folder]) }}"
                            class="search-clear"
                            aria-label="Zoekopdracht wissen"
                        >
                            ×
                        </a>
                    @endif
                </form>

            </div>

        @else

            <div></div>

        @endif


        <div class="header-actions">

            <a
                href="{{ route('home') }}"
                class="header-button"
            >
                {{ __('← Studio') }}
            </a>

            @auth
                <div
                    class="account-pill"
                    title="{{ $currentEmail }}"
                >
                    <div class="account-avatar">
                        {{ $avatarLetter }}
                    </div>

                    <div class="account-email">
                        {{ $currentEmail }}
                    </div>
                </div>
            @endauth

        </div>

    </header>


    {{-- =========================================================
         WORKSPACE
    ========================================================== --}}

    <div class="workspace">

        {{-- =====================================================
             SIDEBAR
        ====================================================== --}}

        <aside
            class="sidebar"
            aria-label="Mail navigatie"
        >
            <div class="sidebar-sticky">

                @if($connected ?? false)

                    <button
                        type="button"
                        class="compose-button"
                        data-compose-open
                    >
                        <span aria-hidden="true">
                            ✎
                        </span>

                        {{ __('Nieuw bericht') }}
                    </button>

                @endif


                <nav class="side-nav">

                    <div class="side-nav-section-title">
                        {{ __('Mailbox') }}
                    </div>

                    @foreach($folders as $folderKey => $folderData)

                        <a
                            href="{{ route('gmail.inbox', ['folder' => $folderKey]) }}"
                            class="side-nav-link {{ $folder === $folderKey ? 'active' : '' }}"
                            @if($folder === $folderKey) aria-current="page" @endif
                        >
                            <span
                                class="nav-icon"
                                aria-hidden="true"
                            >
                                {{ $folderData['icon'] }}
                            </span>

                            <span class="nav-copy">
                                <span class="nav-label">
                                    {{ $folderData['title'] }}
                                </span>
                            </span>
                        </a>

                    @endforeach

                    <div class="side-nav-divider"></div>

                    <a
                        href="{{ route('home') }}"
                        class="side-nav-link"
                    >
                        <span
                            class="nav-icon"
                            aria-hidden="true"
                        >
                            🏠
                        </span>

                        <span class="nav-copy">
                            <span class="nav-label">
                                Mashal Studio
                            </span>
                        </span>
                    </a>

                </nav>


                @if($connected ?? false)

                    <section class="connection-card">

                        <div class="connection-label">
                            {{ __('Google-account') }}
                        </div>

                        <h2 class="connection-title">
                            {{ __('Gmail gekoppeld') }}
                        </h2>

                        <div class="connection-email">
                            {{ $gmailEmail }}
                        </div>

                        <div class="connection-status">
                            <span class="status-dot"></span>
                            {{ __('Verbonden') }}
                        </div>

                        <form
                            method="POST"
                            action="{{ route('gmail.disconnect') }}"
                            class="disconnect-form"
                            data-disconnect-form
                        >
                            @csrf

                            <button
                                type="submit"
                                class="disconnect-button"
                            >
                                {{ __('Gmail ontkoppelen') }}
                            </button>
                        </form>

                    </section>

                @endif

            </div>
        </aside>


        {{-- =====================================================
             MOBILE FOLDER BAR
        ====================================================== --}}

        @if($connected ?? false)

            <nav
                class="mobile-folder-bar"
                aria-label="Gmail mappen"
            >
                @foreach($folders as $folderKey => $folderData)

                    <a
                        href="{{ route('gmail.inbox', ['folder' => $folderKey]) }}"
                        class="mobile-folder-chip {{ $folder === $folderKey ? 'active' : '' }}"
                        @if($folder === $folderKey) aria-current="page" @endif
                    >
                        <span aria-hidden="true">
                            {{ $folderData['icon'] }}
                        </span>

                        <span>
                            {{ $folderData['title'] }}
                        </span>
                    </a>

                @endforeach
            </nav>

        @endif


        {{-- =====================================================
             MAILBOX
        ====================================================== --}}

        <main class="mailbox">

            {{-- =================================================
                 ALERTS
            ================================================== --}}

            @if(
                session('success') ||
                session('error') ||
                !empty($gmailError) ||
                $errors->any()
            )

                <div
                    class="alerts"
                    aria-live="polite"
                >

                    @if(session('success'))

                        <div class="alert alert-success">
                            <span aria-hidden="true">
                                ✓
                            </span>

                            <div>
                                {{ session('success') }}
                            </div>
                        </div>

                    @endif


                    @if(session('error'))

                        <div class="alert alert-error">
                            <span aria-hidden="true">
                                !
                            </span>

                            <div>
                                {{ session('error') }}
                            </div>
                        </div>

                    @endif


                    @if(!empty($gmailError))

                        <div class="alert alert-error">

                            <span aria-hidden="true">
                                !
                            </span>

                            <div>
                                <strong>
                                    {{ __('Gmail kon niet worden geladen.') }}
                                </strong>

                                <br>

                                {{ $gmailError }}
                            </div>

                        </div>

                    @endif


                    @if($errors->any())

                        <div class="alert alert-error">

                            <span aria-hidden="true">
                                !
                            </span>

                            <div>
                                <strong>
                                    Controleer je gegevens.
                                </strong>

                                <ul>
                                    @foreach($errors->all() as $error)
                                        <li>
                                            {{ $error }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                        </div>

                    @endif

                </div>

            @endif


            {{-- =================================================
                 NIET GEKOPPELD
            ================================================== --}}

            @if(!($connected ?? false))

                <section class="state-panel">

                    <div
                        class="state-illustration"
                        aria-hidden="true"
                    >
                        ✉
                    </div>

                    @if($isGmailAccount)

                        <h1 class="state-title">
                            {{ __('Verbind je Gmail met Mashal Mail') }}
                        </h1>

                        <p class="state-text">
                            {{ __('Na het koppelen kun je je Gmail-inbox rechtstreeks op deze website bekijken, berichten openen en nieuwe e-mails versturen zonder Gmail zelf te openen.') }}
                        </p>

                        <div class="state-actions">

                            <a
                                href="{{ route('gmail.connect') }}"
                                class="primary-button"
                            >
                                {{ __('Google Gmail koppelen') }}
                            </a>

                            <a
                                href="{{ route('home') }}"
                                class="tool-button"
                            >
                                {{ __('Terug naar Studio') }}
                            </a>

                        </div>

                    @else

                        <h1 class="state-title">
                            {{ __('Gmail is niet beschikbaar voor dit account') }}
                        </h1>

                        <p class="state-text">
                            {{ __('Deze functie is alleen zichtbaar voor Mashal Studio-accounts waarvan het geregistreerde e-mailadres eindigt op') }}
                            <strong>@gmail.com</strong>.
                        </p>

                        <div class="state-actions">

                            <a
                                href="{{ route('home') }}"
                                class="primary-button"
                            >
                                {{ __('Terug naar Mashal Studio') }}
                            </a>

                        </div>

                    @endif

                </section>


            {{-- =================================================
                 GEKOPPELD
            ================================================== --}}

            @else

                <header class="mailbox-top">

                    <div class="mailbox-heading">

                        <h1 class="mailbox-title">
                            @if(!empty($query))
                                Zoeken in {{ $folderTitle }}
                            @else
                                {{ $currentFolder['icon'] }} {{ $folderTitle }}
                            @endif
                        </h1>

                        <p class="mailbox-subtitle">
                            @if(!empty($query))
                                Resultaten voor “{{ $query }}” in {{ strtolower($folderTitle) }}
                            @else
                                {{ $currentFolder['description'] }} · {{ $gmailEmail }}
                            @endif
                        </p>

                    </div>


                    <div class="mailbox-tools">

                        <a
                            href="{{ route(
                                'gmail.inbox',
                                array_filter([
                                    'folder' => $folder,
                                    'q' => $query ?? null,
                                ])
                            ) }}"
                            class="tool-button"
                            aria-label="{{ $folderTitle }} vernieuwen"
                        >
                            <span aria-hidden="true">
                                ↻
                            </span>

                            <span class="desktop-label">
                                {{ __('Vernieuwen') }}
                            </span>
                        </a>

                    </div>

                </header>


                @if(!empty($messages) && count($messages) > 0)

                    <section
                        class="message-list"
                        aria-label="E-mailberichten"
                    >

                        @foreach($messages as $message)

                            @php
                                $partyRaw =
                                    $folder === 'sent'
                                        ? ($message['to'] ?? 'Onbekende ontvanger')
                                        : ($message['from'] ?? 'Onbekende afzender');

                                $partyDisplay =
                                    trim(
                                        preg_replace(
                                            '/<[^>]+>/',
                                            '',
                                            $partyRaw
                                        )
                                    );

                                if ($partyDisplay === '') {
                                    $partyDisplay = $partyRaw;
                                }

                                $partyInitial =
                                    mb_strtoupper(
                                        mb_substr(
                                            $partyDisplay,
                                            0,
                                            1
                                        )
                                    );

                                if ($partyInitial === '') {
                                    $partyInitial = '?';
                                }

                                $isUnread =
                                    in_array(
                                        'UNREAD',
                                        $message['label_ids'] ?? [],
                                        true
                                    );
                            @endphp

                            <a
                                href="{{ route(
                                    'gmail.show',
                                    [
                                        'id' => $message['id'],
                                        'folder' => $folder,
                                    ]
                                ) }}"
                                class="message-row {{ $isUnread ? 'is-unread' : '' }}"
                            >

                                <div class="message-sender">

                                    <div
                                        class="sender-avatar"
                                        aria-hidden="true"
                                    >
                                        {{ $partyInitial }}
                                    </div>

                                    <div
                                        class="sender-name"
                                        title="{{ $partyRaw }}"
                                    >
                                        <span class="party-prefix">
                                            {{ $folder === 'sent' ? 'Aan:' : '' }}
                                        </span>

                                        {{ $partyDisplay }}

                                        @if($isUnread)
                                            <span
                                                class="unread-dot"
                                                aria-label="Ongelezen"
                                                title="Ongelezen"
                                            ></span>
                                        @endif
                                    </div>

                                </div>


                                <div class="message-preview">

                                    <span
                                        class="message-subject"
                                        title="{{ $message['subject'] ?? '' }}"
                                    >
                                        {{ $message['subject']
                                            ?: '(Geen onderwerp)' }}
                                    </span>

                                    @if(!empty($message['snippet']))
                                        <span class="message-snippet">
                                            {{ $message['snippet'] }}
                                        </span>
                                    @endif

                                </div>


                                <div
                                    class="message-date"
                                    title="{{ $message['date'] ?? '' }}"
                                >
                                    {{ $message['date'] ?? '' }}
                                </div>

                            </a>

                        @endforeach

                    </section>


                    @if(!empty($nextPageToken))

                        <footer class="mailbox-footer">

                            <a
                                href="{{ route(
                                    'gmail.inbox',
                                    array_filter([
                                        'folder' => $folder,
                                        'q' => $query ?? null,
                                        'pageToken' => $nextPageToken,
                                    ])
                                ) }}"
                                class="next-page-button"
                            >
                                {{ __('Volgende berichten') }}
                                <span aria-hidden="true">
                                    →
                                </span>
                            </a>

                        </footer>

                    @endif


                @else

                    <section class="state-panel">

                        <div
                            class="state-illustration"
                            aria-hidden="true"
                        >
                            📭
                        </div>

                        @if(!empty($query))

                            <h2 class="state-title">
                                {{ __('Geen berichten gevonden') }}
                            </h2>

                            <p class="state-text">
                                {{ __('Er zijn geen Gmail-berichten gevonden die overeenkomen met') }}
                                <strong>{{ $query }}</strong>.
                            </p>

                            <div class="state-actions">

                                <a
                                    href="{{ route('gmail.inbox', ['folder' => $folder]) }}"
                                    class="primary-button"
                                >
                                    Terug naar {{ strtolower($folderTitle) }}
                                </a>

                            </div>

                        @else

                            <h2 class="state-title">
                                {{ $currentFolder['empty_title'] }}
                            </h2>

                            <p class="state-text">
                                {{ $currentFolder['empty_text'] }}
                            </p>

                            <div class="state-actions">

                                <button
                                    type="button"
                                    class="primary-button"
                                    data-compose-open
                                >
                                    {{ __('Nieuw bericht') }}
                                </button>

                            </div>

                        @endif

                    </section>

                @endif

            @endif

        </main>

    </div>

</div>


{{-- =============================================================
     MOBILE NAVIGATION
============================================================== --}}

@if($connected ?? false)

    <nav
        class="mobile-nav"
        aria-label="Mobiele mailnavigatie"
    >

        <button
            type="button"
            class="mobile-nav-button active"
            data-folder-menu-open
        >
            <span
                class="mobile-nav-icon"
                aria-hidden="true"
            >
                {{ $currentFolder['icon'] }}
            </span>

            {{ __('Mappen') }}
        </button>


        <button
            type="button"
            class="mobile-nav-button"
            data-compose-open
        >
            <span
                class="mobile-nav-icon"
                aria-hidden="true"
            >
                ✎
            </span>

            {{ __('Schrijven') }}
        </button>


        <a
            href="{{ route('home') }}"
            class="mobile-nav-button"
        >
            <span
                class="mobile-nav-icon"
                aria-hidden="true"
            >
                🏠
            </span>

            Studio
        </a>

    </nav>


    <div
        id="folder-menu-overlay"
        class="folder-menu-overlay"
        aria-hidden="true"
    ></div>


    <section
        id="folder-menu-sheet"
        class="folder-menu-sheet"
        role="dialog"
        aria-modal="true"
        aria-labelledby="folder-menu-title"
        aria-hidden="true"
    >

        <div class="folder-sheet-handle" aria-hidden="true"></div>

        <header class="folder-sheet-header">

            <div>
                <div class="folder-sheet-eyebrow">
                    {{ __('Mashal Mail') }}
                </div>

                <h2
                    id="folder-menu-title"
                    class="folder-sheet-title"
                >
                    {{ __('Gmail-mappen') }}
                </h2>
            </div>

            <button
                type="button"
                class="folder-sheet-close"
                data-folder-menu-close
                aria-label="Mappen sluiten"
            >
                ×
            </button>

        </header>

        <nav class="folder-sheet-list">

            @foreach($folders as $folderKey => $folderData)

                <a
                    href="{{ route('gmail.inbox', ['folder' => $folderKey]) }}"
                    class="folder-sheet-link {{ $folder === $folderKey ? 'active' : '' }}"
                    @if($folder === $folderKey) aria-current="page" @endif
                >
                    <span
                        class="folder-sheet-icon"
                        aria-hidden="true"
                    >
                        {{ $folderData['icon'] }}
                    </span>

                    <span class="folder-sheet-copy">
                        <strong>
                            {{ $folderData['title'] }}
                        </strong>

                        <small>
                            {{ $folderData['description'] }}
                        </small>
                    </span>

                    @if($folder === $folderKey)
                        <span
                            class="folder-sheet-check"
                            aria-hidden="true"
                        >
                            ✓
                        </span>
                    @endif
                </a>

            @endforeach

        </nav>

    </section>

@endif


{{-- =============================================================
     COMPOSE
============================================================== --}}

@if($connected ?? false)

    <div
        id="compose-overlay"
        class="compose-overlay"
        aria-hidden="true"
    ></div>


    <section
        id="compose-window"
        class="compose-window"
        role="dialog"
        aria-modal="true"
        aria-labelledby="compose-title"
        aria-hidden="true"
    >

        <header class="compose-header">

            <h2
                id="compose-title"
                class="compose-heading"
            >
                {{ __('Nieuw bericht') }}
            </h2>

            <button
                type="button"
                class="compose-close"
                data-compose-close
                aria-label="Berichtvenster sluiten"
            >
                ×
            </button>

        </header>


        <form
            method="POST"
            action="{{ route('gmail.send') }}"
            class="compose-form"
        >
            @csrf

            <label
                for="compose-to"
                class="sr-only"
            >
                {{ __('Ontvanger') }}
            </label>

            <input
                id="compose-to"
                type="email"
                name="to"
                class="compose-input"
                value="{{ old('to') }}"
                placeholder="Aan"
                required
                maxlength="254"
                autocomplete="email"
                inputmode="email"
            >


            <label
                for="compose-subject"
                class="sr-only"
            >
                {{ __('Onderwerp') }}
            </label>

            <input
                id="compose-subject"
                type="text"
                name="subject"
                class="compose-input"
                value="{{ old('subject') }}"
                placeholder="Onderwerp"
                maxlength="998"
            >


            <label
                for="compose-body"
                class="sr-only"
            >
                {{ __('Bericht') }}
            </label>

            <textarea
                id="compose-body"
                name="body"
                class="compose-textarea"
                placeholder="Schrijf je bericht..."
                required
                maxlength="100000"
            >{{ old('body') }}</textarea>


            <footer class="compose-footer">

                <button
                    type="submit"
                    class="send-button"
                >
                    {{ __('Verzenden') }}
                </button>

                <div
                    class="compose-from"
                    title="{{ $currentEmail }}"
                >
                    Van: {{ $currentEmail }}
                </div>

            </footer>

        </form>

    </section>

@endif


<script>
document.addEventListener('DOMContentLoaded', () => {

    const body = document.body;

    const composeWindow =
        document.getElementById('compose-window');

    const composeOverlay =
        document.getElementById('compose-overlay');

    const openButtons =
        document.querySelectorAll('[data-compose-open]');

    const closeButtons =
        document.querySelectorAll('[data-compose-close]');

    const disconnectForm =
        document.querySelector('[data-disconnect-form]');

    const folderMenuSheet =
        document.getElementById('folder-menu-sheet');

    const folderMenuOverlay =
        document.getElementById('folder-menu-overlay');

    const folderMenuOpenButtons =
        document.querySelectorAll('[data-folder-menu-open]');

    const folderMenuCloseButtons =
        document.querySelectorAll('[data-folder-menu-close]');


    function openFolderMenu() {
        if (!folderMenuSheet) {
            return;
        }

        closeCompose();

        folderMenuSheet.classList.add('active');
        folderMenuOverlay?.classList.add('active');

        folderMenuSheet.setAttribute('aria-hidden', 'false');
        folderMenuOverlay?.setAttribute('aria-hidden', 'false');

        body.classList.add('folder-menu-open');
    }


    function closeFolderMenu() {
        if (!folderMenuSheet) {
            return;
        }

        folderMenuSheet.classList.remove('active');
        folderMenuOverlay?.classList.remove('active');

        folderMenuSheet.setAttribute('aria-hidden', 'true');
        folderMenuOverlay?.setAttribute('aria-hidden', 'true');

        body.classList.remove('folder-menu-open');
    }


    function openCompose() {
        if (!composeWindow) {
            return;
        }

        closeFolderMenu();

        composeWindow.classList.add('active');

        composeOverlay?.classList.add('active');

        composeWindow.setAttribute(
            'aria-hidden',
            'false'
        );

        composeOverlay?.setAttribute(
            'aria-hidden',
            'false'
        );

        body.classList.add('compose-open');

        window.setTimeout(() => {
            const recipient =
                document.getElementById('compose-to');

            recipient?.focus();
        }, 80);
    }


    function closeCompose() {
        if (!composeWindow) {
            return;
        }

        composeWindow.classList.remove('active');

        composeOverlay?.classList.remove('active');

        composeWindow.setAttribute(
            'aria-hidden',
            'true'
        );

        composeOverlay?.setAttribute(
            'aria-hidden',
            'true'
        );

        body.classList.remove('compose-open');
    }


    openButtons.forEach((button) => {
        button.addEventListener(
            'click',
            openCompose
        );
    });


    closeButtons.forEach((button) => {
        button.addEventListener(
            'click',
            closeCompose
        );
    });


    composeOverlay?.addEventListener(
        'click',
        closeCompose
    );


    folderMenuOpenButtons.forEach((button) => {
        button.addEventListener(
            'click',
            openFolderMenu
        );
    });


    folderMenuCloseButtons.forEach((button) => {
        button.addEventListener(
            'click',
            closeFolderMenu
        );
    });


    folderMenuOverlay?.addEventListener(
        'click',
        closeFolderMenu
    );


    document.addEventListener(
        'keydown',
        (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            if (
                folderMenuSheet?.classList.contains('active')
            ) {
                closeFolderMenu();
                return;
            }

            if (
                composeWindow?.classList.contains('active')
            ) {
                closeCompose();
            }
        }
    );


    disconnectForm?.addEventListener(
        'submit',
        (event) => {
            const confirmed = window.confirm(
                'Weet je zeker dat je Gmail wilt ontkoppelen van Mashal Studio?'
            );

            if (!confirmed) {
                event.preventDefault();
            }
        }
    );


    @if($errors->any() && old('to'))
        openCompose();
    @endif

});
</script>

</body>
</html>