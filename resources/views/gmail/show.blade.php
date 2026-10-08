<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ur' ? 'rtl' : 'ltr' }}">

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



    <title>

        {{ $message['subject'] ?? 'E-mail' }} | Mashal Mail

    </title>



    <style>

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



            --primary: #0b57d0;

            --primary-hover: #0847ac;

            --primary-soft: #dce9ff;



            --success-bg: #e7f6ed;

            --success-text: #126c3a;



            --error-bg: #fdeceb;

            --error-text: #a62b24;



            --warning-bg: #fff6dc;

            --warning-text: #765200;



            --danger-bg: #fff0ef;

            --danger-text: #9b2018;



            --shadow-sm:

                0 1px 2px rgba(15, 23, 42, 0.06),

                0 1px 3px rgba(15, 23, 42, 0.08);



            --shadow-md:

                0 8px 24px rgba(15, 23, 42, 0.10);



            --shadow-lg:

                0 20px 60px rgba(15, 23, 42, 0.20);

        }



        body {

            min-height: 100vh;

            min-height: 100dvh;



            background:

                radial-gradient(

                    circle at top right,

                    rgba(11, 87, 208, 0.06),

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



        :focus-visible {

            outline: 3px solid rgba(11, 87, 208, 0.28);

            outline-offset: 2px;

        }



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



        /*

        |--------------------------------------------------------------------------

        | Topbar

        |--------------------------------------------------------------------------

        */



        .topbar {

            min-height: 76px;



            position: sticky;

            top: 0;

            z-index: 40;



            display: flex;

            align-items: center;

            gap: 18px;



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

                    #0b57d0,

                    #4285f4

                );



            color: #ffffff;



            box-shadow:

                0 6px 16px rgba(11, 87, 208, 0.24);



            font-size: 19px;

            font-weight: 800;

        }



        .brand-copy {

            min-width: 0;

        }



        .brand-title {

            font-size: 17px;

            font-weight: 760;

            letter-spacing: -0.02em;

        }



        .brand-subtitle {

            margin-top: 2px;



            color: var(--muted);



            font-size: 11px;

        }



        .topbar-actions {

            margin-left: auto;



            display: flex;

            align-items: center;

            gap: 9px;

        }



        .top-button {

            min-height: 40px;



            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 7px;



            padding: 0 14px;



            border:

                1px solid var(--border);



            border-radius: 12px;



            background: #ffffff;



            color: var(--text-soft);



            font-size: 12px;

            font-weight: 720;

        }



        .top-button:hover {

            background: var(--surface-hover);

        }



        .account-avatar {

            width: 38px;

            height: 38px;



            display: grid;

            place-items: center;



            border-radius: 50%;



            background: var(--primary-soft);

            color: #174ea6;



            font-size: 13px;

            font-weight: 800;

        }



        /*

        |--------------------------------------------------------------------------

        | Reader

        |--------------------------------------------------------------------------

        */



        .reader {

            width:

                min(1050px, calc(100% - 32px));



            margin:

                20px auto

                calc(40px + env(safe-area-inset-bottom));

        }



        /*

        |--------------------------------------------------------------------------

        | Mailbox context

        |--------------------------------------------------------------------------

        */



        .mailbox-context {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 14px;



            margin-bottom: 12px;

            padding: 12px 14px;



            border:

                1px solid var(--border);



            border-radius: 16px;



            background:

                rgba(255, 255, 255, 0.92);



            box-shadow: var(--shadow-sm);

        }



        .mailbox-context-left {

            min-width: 0;



            display: flex;

            align-items: center;

            gap: 10px;

        }



        .mailbox-context-icon {

            width: 38px;

            height: 38px;



            flex-shrink: 0;



            display: grid;

            place-items: center;



            border-radius: 12px;



            background: var(--primary-soft);



            font-size: 18px;

        }



        .mailbox-context-copy {

            min-width: 0;

        }



        .mailbox-context-label {

            color: var(--muted);



            font-size: 10px;

            font-weight: 750;

            letter-spacing: 0.06em;

            text-transform: uppercase;

        }



        .mailbox-context-title {

            margin-top: 2px;



            font-size: 13px;

            font-weight: 780;

        }



        .mailbox-badge {

            flex-shrink: 0;



            display: inline-flex;

            align-items: center;

            justify-content: center;



            min-height: 30px;



            padding: 0 10px;



            border-radius: 999px;



            background: var(--surface-soft);

            color: var(--text-soft);



            font-size: 10px;

            font-weight: 760;

        }



        /*

        |--------------------------------------------------------------------------

        | Folder warning

        |--------------------------------------------------------------------------

        */



        .folder-notice {

            margin-bottom: 12px;



            display: flex;

            align-items: flex-start;

            gap: 10px;



            padding: 13px 14px;



            border-radius: 14px;



            font-size: 12px;

            line-height: 1.55;

        }



        .folder-notice.spam {

            background: var(--warning-bg);

            color: var(--warning-text);

        }



        .folder-notice.trash {

            background: var(--danger-bg);

            color: var(--danger-text);

        }



        /*

        |--------------------------------------------------------------------------

        | Toolbar

        |--------------------------------------------------------------------------

        */



        .reader-toolbar {

            min-height: 58px;



            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 12px;



            margin-bottom: 12px;



            padding: 8px 10px;



            border:

                1px solid var(--border);



            border-radius: 16px;



            background:

                rgba(255, 255, 255, 0.93);



            box-shadow: var(--shadow-sm);

        }



        .toolbar-group {

            display: flex;

            align-items: center;

            gap: 7px;

        }



        .toolbar-button {

            min-height: 40px;



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

            font-weight: 720;

        }



        .toolbar-button:hover {

            background: var(--surface-hover);

        }



        .toolbar-button.primary {

            border-color: transparent;



            background: var(--primary);

            color: #ffffff;

        }



        .toolbar-button.primary:hover {

            background: var(--primary-hover);

        }



        .icon-button {

            width: 40px;

            height: 40px;



            display: grid;

            place-items: center;



            border:

                1px solid var(--border);



            border-radius: 50%;



            background: #ffffff;



            color: var(--text-soft);



            font-size: 17px;

        }



        .icon-button:hover {

            background: var(--surface-hover);

        }



        /*

        |--------------------------------------------------------------------------

        | Alerts

        |--------------------------------------------------------------------------

        */



        .alert {

            margin-bottom: 12px;



            padding: 13px 15px;



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



        .alert ul {

            margin:

                7px 0 0 18px;



            padding: 0;

        }



        /*

        |--------------------------------------------------------------------------

        | Message

        |--------------------------------------------------------------------------

        */



        .message-card {

            overflow: hidden;



            border:

                1px solid var(--border);



            border-radius: 22px;



            background: #ffffff;



            box-shadow: var(--shadow-sm);

        }



        .message-title-area {

            padding: 26px 28px 20px;



            border-bottom:

                1px solid var(--border);

        }



        .message-title-row {

            display: flex;

            align-items: flex-start;

            justify-content: space-between;

            gap: 20px;

        }



        .message-subject-wrap {

            min-width: 0;

        }



        .message-subject {

            min-width: 0;



            overflow-wrap: anywhere;



            font-size:

                clamp(22px, 3.5vw, 31px);



            font-weight: 680;

            letter-spacing: -0.03em;

            line-height: 1.28;

        }



        .message-labels {

            margin-top: 10px;



            display: flex;

            align-items: center;

            flex-wrap: wrap;

            gap: 6px;

        }



        .message-label {

            display: inline-flex;

            align-items: center;



            min-height: 25px;



            padding: 0 9px;



            border-radius: 999px;



            background: var(--surface-soft);

            color: var(--muted);



            font-size: 9px;

            font-weight: 760;

            letter-spacing: 0.03em;

            text-transform: uppercase;

        }



        .message-label.unread {

            background: var(--primary-soft);

            color: #174ea6;

        }



        .message-label.spam {

            background: var(--warning-bg);

            color: var(--warning-text);

        }



        .message-label.trash {

            background: var(--danger-bg);

            color: var(--danger-text);

        }



        .message-date {

            flex-shrink: 0;



            padding-top: 6px;



            color: var(--muted);



            text-align: right;



            font-size: 11px;

            line-height: 1.45;

        }



        /*

        |--------------------------------------------------------------------------

        | Sender information

        |--------------------------------------------------------------------------

        */



        .sender-area {

            display: grid;

            grid-template-columns:

                48px minmax(0, 1fr) auto;



            align-items: start;

            gap: 13px;



            padding: 19px 28px;



            border-bottom:

                1px solid var(--border);

        }



        .sender-avatar {

            width: 46px;

            height: 46px;



            display: grid;

            place-items: center;



            border-radius: 50%;



            background:

                linear-gradient(

                    145deg,

                    #dce9ff,

                    #eef4ff

                );



            color: #245298;



            font-size: 17px;

            font-weight: 800;

        }



        .sender-copy {

            min-width: 0;

        }



        .sender-line {

            min-width: 0;



            display: flex;

            align-items: baseline;

            flex-wrap: wrap;

            gap: 6px;

        }



        .sender-name {

            min-width: 0;



            overflow-wrap: anywhere;



            font-size: 13px;

            font-weight: 780;

        }



        .sender-email {

            color: var(--muted);



            font-size: 11px;



            overflow-wrap: anywhere;

        }



        .sent-to-line {

            margin-top: 4px;



            color: var(--muted);



            font-size: 11px;

            line-height: 1.45;

        }



        .recipient-trigger {

            margin-top: 6px;



            display: inline-flex;

            align-items: center;

            gap: 5px;



            padding: 0;



            border: 0;



            background: transparent;



            color: var(--muted);



            font-size: 11px;

            font-weight: 650;

        }



        .recipient-trigger:hover {

            color: var(--text);

        }



        .recipient-details {

            max-width: 660px;



            display: none;



            margin-top: 12px;



            padding: 13px;



            border:

                1px solid var(--border);



            border-radius: 12px;



            background: var(--surface-soft);

        }



        .recipient-details.active {

            display: block;

        }



        .recipient-row {

            display: grid;

            grid-template-columns:

                56px minmax(0, 1fr);



            gap: 8px;



            margin-bottom: 7px;



            font-size: 11px;

            line-height: 1.5;

        }



        .recipient-row:last-child {

            margin-bottom: 0;

        }



        .recipient-label {

            color: var(--muted);

        }



        .recipient-value {

            overflow-wrap: anywhere;



            color: var(--text-soft);

        }



        .quick-reply {

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

            font-weight: 720;

        }



        .quick-reply:hover {

            background: var(--surface-hover);

        }



        /*

        |--------------------------------------------------------------------------

        | Message body

        |--------------------------------------------------------------------------

        */



        .message-body {

            min-height: 360px;



            padding: 32px 28px 46px;



            color: #202124;



            font-family:

                Arial,

                Helvetica,

                sans-serif;



            font-size: 15px;

            line-height: 1.75;



            white-space: pre-wrap;

            overflow-wrap: anywhere;

            word-break: break-word;

        }



        .empty-message {

            color: var(--muted);



            font-style: italic;

        }



        /*

        |--------------------------------------------------------------------------

        | Footer actions

        |--------------------------------------------------------------------------

        */



        .message-actions {

            display: flex;

            align-items: center;

            flex-wrap: wrap;

            gap: 9px;



            padding: 20px 28px 26px;



            border-top:

                1px solid var(--border);



            background:

                linear-gradient(

                    to bottom,

                    #ffffff,

                    #fbfcfe

                );

        }



        .action-button {

            min-height: 42px;



            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;



            padding: 0 17px;



            border:

                1px solid var(--border);



            border-radius: 999px;



            background: #ffffff;



            color: var(--text-soft);



            font-size: 12px;

            font-weight: 750;

        }



        .action-button:hover {

            background: var(--surface-hover);

        }



        .action-button.primary {

            border-color: transparent;



            background: var(--primary);

            color: #ffffff;

        }



        .action-button.primary:hover {

            background: var(--primary-hover);

        }



        /*

        |--------------------------------------------------------------------------

        | Compose

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

                1px solid var(--border);



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



            padding:

                0 15px 0 18px;



            background: #f4f7fb;



            border-bottom:

                1px solid var(--border);

        }



        .compose-title {

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

        }



        .compose-close:hover {

            background:

                rgba(24, 33, 47, 0.08);

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



            outline: none;



            background: transparent;



            color: var(--text);

        }



        .compose-textarea {

            flex: 1;



            width: 100%;

            min-height: 290px;



            padding: 17px;



            border: 0;



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



        .compose-sender {

            min-width: 0;



            overflow: hidden;

            text-overflow: ellipsis;

            white-space: nowrap;



            color: var(--muted);



            font-size: 11px;

        }



        /*

        |--------------------------------------------------------------------------

        | Mobile actions

        |--------------------------------------------------------------------------

        */



        .mobile-actions {

            display: none;

        }



        /*

        |--------------------------------------------------------------------------

        | Mobile

        |--------------------------------------------------------------------------

        */



        @media (max-width: 760px) {

            body {

                padding-bottom:

                    calc(70px + env(safe-area-inset-bottom));

            }



            .topbar {

                min-height: 62px;



                padding:

                    9px

                    max(12px, env(safe-area-inset-right))

                    9px

                    max(12px, env(safe-area-inset-left));

            }



            .brand-logo {

                width: 40px;

                height: 40px;



                border-radius: 12px;

            }



            .brand-subtitle {

                display: none;

            }



            .top-button {

                display: none;

            }



            .reader {

                width:

                    min(100% - 20px, 1050px);



                margin:

                    10px auto

                    20px;

            }



            .mailbox-context {

                padding: 10px 12px;



                border-radius: 14px;

            }



            .mailbox-context-icon {

                width: 36px;

                height: 36px;

            }



            .reader-toolbar {

                min-height: 52px;



                padding: 6px 8px;

            }



            .toolbar-button .desktop-label {

                display: none;

            }



            .toolbar-button {

                width: 40px;

                height: 40px;

                min-height: 40px;



                padding: 0;



                border-radius: 50%;

            }



            .message-card {

                border-radius: 17px;

            }



            .message-title-area {

                padding:

                    20px 17px 16px;

            }



            .message-title-row {

                flex-direction: column;

                gap: 9px;

            }



            .message-subject {

                font-size:

                    clamp(20px, 6vw, 25px);

            }



            .message-date {

                padding-top: 0;



                text-align: left;

            }



            .sender-area {

                grid-template-columns:

                    42px minmax(0, 1fr);



                gap: 11px;



                padding:

                    16px 17px;

            }



            .sender-avatar {

                width: 40px;

                height: 40px;



                font-size: 15px;

            }



            .sender-area > .quick-reply {

                display: none;

            }



            .sender-line {

                display: block;

            }



            .sender-email {

                display: block;



                margin-top: 2px;

            }



            .recipient-row {

                grid-template-columns:

                    46px minmax(0, 1fr);

            }



            .message-body {

                min-height: 300px;



                padding:

                    24px 17px 34px;



                font-size: 14px;

                line-height: 1.72;

            }



            .message-actions {

                display: none;

            }



            .mobile-actions {

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



            .mobile-action {

                display: flex;

                flex-direction: column;

                align-items: center;

                justify-content: center;

                gap: 3px;



                border: 0;

                border-radius: 12px;



                background: transparent;



                color: var(--muted);



                font-size: 10px;

                font-weight: 680;

            }



            .mobile-action.primary {

                color: var(--primary);

            }



            .mobile-action-icon {

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

                min-height:

                    calc(56px + env(safe-area-inset-top));



                padding-top:

                    env(safe-area-inset-top);

            }



            .compose-form {

                min-height: 0;

                flex: 1;

            }



            .compose-textarea {

                min-height: 180px;

            }

        }



        @media (max-width: 420px) {

            .brand-title {

                font-size: 16px;

            }



            .reader {

                width:

                    calc(100% - 14px);

            }



            .mailbox-badge {

                display: none;

            }



            .message-title-area {

                padding:

                    18px 14px 14px;

            }



            .sender-area {

                padding:

                    14px;

            }



            .message-body {

                padding:

                    21px 14px 30px;

            }

        }



        @media (prefers-reduced-motion: reduce) {

            *,

            *::before,

            *::after {

                transition: none !important;

                animation: none !important;

            }

        }

    </style>

    <link rel="stylesheet" href="{{ asset('css/mobile-responsive.css') }}?v=20261008">
</head>



<body>



@php

    /*

    |--------------------------------------------------------------------------

    | Gebruiker

    |--------------------------------------------------------------------------

    */



    $currentUser = auth()->user();



    $currentEmail =

        $currentUser?->email

        ?? '';



    $accountInitial =

        $currentEmail !== ''

            ? mb_strtoupper(

                mb_substr(

                    $currentEmail,

                    0,

                    1

                )

            )

            : 'M';





    /*

    |--------------------------------------------------------------------------

    | Huidige Gmail-map

    |--------------------------------------------------------------------------

    */



    $allowedFolders = [

        'inbox',

        'archive',

        'sent',

        'spam',

        'trash',

    ];



    $folder =

        strtolower(

            trim(

                (string) (

                    $folder

                    ?? 'inbox'

                )

            )

        );



    if (

        !in_array(

            $folder,

            $allowedFolders,

            true

        )

    ) {

        $folder = 'inbox';

    }



    $folderInfo = [

        'inbox' => [

            'title' => 'Inbox',

            'icon' => '📥',

        ],



        'archive' => [

            'title' => 'Gearchiveerd',

            'icon' => '📦',

        ],



        'sent' => [

            'title' => 'Verzonden',

            'icon' => '📤',

        ],



        'spam' => [

            'title' => 'Spam',

            'icon' => '⚠️',

        ],



        'trash' => [

            'title' => 'Prullenbak',

            'icon' => '🗑️',

        ],

    ];



    $activeFolder =

        $folderInfo[$folder];



    $backUrl =

        route(

            'gmail.inbox',

            [

                'folder' => $folder,

            ]

        );



    $refreshUrl =

        route(

            'gmail.show',

            [

                'id' => $message['id'],

                'folder' => $folder,

            ]

        );





    /*

    |--------------------------------------------------------------------------

    | Berichtgegevens

    |--------------------------------------------------------------------------

    */



    $fromRaw =

        $message['from']

        ?? '';



    $toRaw =

        $message['to']

        ?? '';



    $ccRaw =

        $message['cc']

        ?? '';



    $labelIds =

        $message['label_ids']

        ?? [];



    if (!is_array($labelIds)) {

        $labelIds = [];

    }





    /*

    |--------------------------------------------------------------------------

    | Afzendernaam

    |--------------------------------------------------------------------------

    */



    $senderName =

        trim(

            preg_replace(

                '/<[^>]+>/',

                '',

                $fromRaw

            )

        );



    if ($senderName === '') {

        $senderName =

            $fromRaw !== ''

                ? $fromRaw

                : 'Onbekende afzender';

    }



    $senderInitial =

        mb_strtoupper(

            mb_substr(

                $senderName,

                0,

                1

            )

        );



    if ($senderInitial === '') {

        $senderInitial = '?';

    }





    /*

    |--------------------------------------------------------------------------

    | Antwoordadres

    |--------------------------------------------------------------------------

    |

    | In Verzonden beantwoorden we aan de oorspronkelijke ontvanger.

    | In andere mappen antwoorden we aan de afzender.

    |

    */



    $replyTargetRaw =

        $folder === 'sent'

            ? $toRaw

            : $fromRaw;



    $replyEmail = '';



    if (

        preg_match(

            '/<([^<>@\s]+@[^<>@\s]+)>/',

            $replyTargetRaw,

            $matches

        )

    ) {

        $replyEmail =

            trim(

                $matches[1]

            );



    } elseif (

        filter_var(

            trim($replyTargetRaw),

            FILTER_VALIDATE_EMAIL

        )

    ) {

        $replyEmail =

            trim(

                $replyTargetRaw

            );

    }





    /*

    |--------------------------------------------------------------------------

    | Reply subject

    |--------------------------------------------------------------------------

    */



    $originalSubject =

        $message['subject']

        ?? '';



    $replySubject =

        preg_match(

            '/^**\s***r&#x65;**\s***:/i',

            $originalSubject

        )

            ? $originalSubject

            : 'Re: ' . $originalSubject;





    /*

    |--------------------------------------------------------------------------

    | Status

    |--------------------------------------------------------------------------

    */



    $isUnread =

        in_array(

            'UNREAD',

            $labelIds,

            true

        );



    $isSpam =

        $folder === 'spam'

        || in_array(

            'SPAM',

            $labelIds,

            true

        );



    $isTrash =

        $folder === 'trash'

        || in_array(

            'TRASH',

            $labelIds,

            true

        );

@endphp





<header class="topbar">



    <a

        href="{{ $backUrl }}"

        class="brand"

        aria-label="Terug naar {{ $activeFolder['title'] }}"

    >



        <div class="brand-logo">

            M

        </div>



        <div class="brand-copy">



            <div class="brand-title">

                {{ __('Mashal Mail') }}

            </div>



            <div class="brand-subtitle">

                {{ $activeFolder['title'] }} · Bericht bekijken

            </div>



        </div>



    </a>





    <div class="topbar-actions">
    <div class="standalone-language-switcher" aria-label="{{ __('Taal kiezen') }}">
        @include('partials.language-switcher')
    </div>



        <a

            href="{{ $backUrl }}"

            class="top-button"

        >

            ← {{ $activeFolder['title'] }}

        </a>



        <a

            href="{{ route('home') }}"

            class="top-button"

        >

            Mashal Studio

        </a>



        <div

            class="account-avatar"

            title="{{ $currentEmail }}"

        >

            {{ $accountInitial }}

        </div>



    </div>



</header>





<main class="reader">



    {{-- =========================================================

         MAPCONTEXT

    ========================================================== --}}



    <section

        class="mailbox-context"

        aria-label="Huidige Gmail-map"

    >



        <div class="mailbox-context-left">



            <div

                class="mailbox-context-icon"

                aria-hidden="true"

            >

                {{ $activeFolder['icon'] }}

            </div>



            <div class="mailbox-context-copy">



                <div class="mailbox-context-label">

                    {{ __('Huidige map') }}

                </div>



                <div class="mailbox-context-title">

                    {{ $activeFolder['title'] }}

                </div>



            </div>



        </div>





        <div class="mailbox-badge">

            Gmail

        </div>



    </section>





    {{-- =========================================================

         FOLDER WAARSCHUWINGEN

    ========================================================== --}}



    @if($folder === 'spam')



        <div class="folder-notice spam">

            <span aria-hidden="true">

                ⚠️

            </span>



            <div>

                <strong>{{ __('Dit bericht staat in Spam.') }}</strong>

                {{ __('We tonen de inhoud als gewone tekst en voeren geen externe HTML of scripts uit.') }}

            </div>

        </div>



    @endif





    @if($folder === 'trash')



        <div class="folder-notice trash">

            <span aria-hidden="true">

                🗑️

            </span>



            <div>

                <strong>{{ __('Dit bericht staat in de Prullenbak.') }}</strong>

                {{ __('Op dit moment kan Mashal Mail het bericht bekijken, maar nog niet herstellen of definitief verwijderen.') }}

            </div>

        </div>



    @endif





    {{-- =========================================================

         MELDINGEN

    ========================================================== --}}



    @if(session('success'))



        <div

            class="alert alert-success"

            aria-live="polite"

        >

            {{ session('success') }}

        </div>



    @endif





    @if(session('error'))



        <div

            class="alert alert-error"

            aria-live="polite"

        >

            {{ session('error') }}

        </div>



    @endif





    @if($errors->any())



        <div

            class="alert alert-error"

            aria-live="polite"

        >

            <strong>

                {{ __('Controleer de gegevens.') }}

            </strong>



            <ul>

                @foreach($errors->all() as $error)

                    <li>

                        {{ $error }}

                    </li>

                @endforeach

            </ul>

        </div>



    @endif





    {{-- =========================================================

         TOOLBAR

    ========================================================== --}}



    <nav

        class="reader-toolbar"

        aria-label="E-mailacties"

    >



        <div class="toolbar-group">



            <a

                href="{{ $backUrl }}"

                class="toolbar-button"

                aria-label="Terug naar {{ $activeFolder['title'] }}"

            >

                ←



                <span class="desktop-label">

                    {{ $activeFolder['title'] }}

                </span>

            </a>





            <a

                href="{{ $refreshUrl }}"

                class="icon-button"

                aria-label="Bericht vernieuwen"

                title="{{ __('Vernieuwen') }}"

            >

                ↻

            </a>



        </div>





        <div class="toolbar-group">



            @if($replyEmail !== '')



                <button

                    type="button"

                    class="toolbar-button primary"

                    data-reply-open

                >

                    ↩



                    <span class="desktop-label">

                        {{ __('Beantwoorden') }}

                    </span>

                </button>



            @endif



        </div>



    </nav>





    {{-- =========================================================

         MESSAGE

    ========================================================== --}}



    <article class="message-card">



        {{-- =====================================================

             SUBJECT

        ====================================================== --}}



        <header class="message-title-area">



            <div class="message-title-row">



                <div class="message-subject-wrap">



                    <h1 class="message-subject">

                        {{ $message['subject']

                            ?: '(Geen onderwerp)' }}

                    </h1>





                    <div class="message-labels">



                        <span class="message-label">

                            {{ $activeFolder['title'] }}

                        </span>



                        @if($isUnread)

                            <span class="message-label unread">

                                {{ __('Ongelezen') }}

                            </span>

                        @endif



                        @if($isSpam)

                            <span class="message-label spam">

                                {{ __('Spam') }}

                            </span>

                        @endif



                        @if($isTrash)

                            <span class="message-label trash">

                                {{ __('Prullenbak') }}

                            </span>

                        @endif



                    </div>



                </div>





                @if(!empty($message['date']))



                    <time class="message-date">

                        {{ $message['date'] }}

                    </time>



                @endif



            </div>



        </header>





        {{-- =====================================================

             SENDER

        ====================================================== --}}



        <section class="sender-area">



            <div

                class="sender-avatar"

                aria-hidden="true"

            >

                {{ $senderInitial }}

            </div>





            <div class="sender-copy">



                <div class="sender-line">



                    <strong class="sender-name">

                        {{ $senderName }}

                    </strong>



                    @if($fromRaw !== '')

                        <span class="sender-email">

                            {{ $fromRaw }}

                        </span>

                    @endif



                </div>





                @if($folder === 'sent' && $toRaw !== '')



                    <div class="sent-to-line">

                        Aan: {{ $toRaw }}

                    </div>



                @endif





                <button

                    type="button"

                    class="recipient-trigger"

                    id="recipient-trigger"

                    aria-expanded="false"

                    aria-controls="recipient-details"

                >

                    {{ __('Berichtdetails') }}



                    <span

                        id="recipient-arrow"

                        aria-hidden="true"

                    >

                        ▾

                    </span>

                </button>





                <div

                    id="recipient-details"

                    class="recipient-details"

                >



                    <div class="recipient-row">



                        <div class="recipient-label">

                            {{ __('Van') }}

                        </div>



                        <div class="recipient-value">

                            {{ $fromRaw !== ''

                                ? $fromRaw

                                : '(Onbekend)' }}

                        </div>



                    </div>





                    <div class="recipient-row">



                        <div class="recipient-label">

                            {{ __('Aan') }}

                        </div>



                        <div class="recipient-value">

                            {{ $toRaw !== ''

                                ? $toRaw

                                : $currentEmail }}

                        </div>



                    </div>





                    @if($ccRaw !== '')



                        <div class="recipient-row">



                            <div class="recipient-label">

                                {{ __('Cc') }}

                            </div>



                            <div class="recipient-value">

                                {{ $ccRaw }}

                            </div>



                        </div>



                    @endif





                    @if(!empty($message['date']))



                        <div class="recipient-row">



                            <div class="recipient-label">

                                {{ __('Datum') }}

                            </div>



                            <div class="recipient-value">

                                {{ $message['date'] }}

                            </div>



                        </div>



                    @endif





                    @if(!empty($message['thread_id']))



                        <div class="recipient-row">



                            <div class="recipient-label">

                                {{ __('Thread') }}

                            </div>



                            <div class="recipient-value">

                                {{ $message['thread_id'] }}

                            </div>



                        </div>



                    @endif



                </div>



            </div>





            @if($replyEmail !== '')



                <button

                    type="button"

                    class="quick-reply"

                    data-reply-open

                >

                    {{ __('↩ Antwoorden') }}

                </button>



            @endif



        </section>





        {{-- =====================================================

             BODY

        ====================================================== --}}



        <section class="message-body">@if(!empty($message['body'])){{ $message['body'] }}@elseif(!empty($message['snippet'])){{ $message['snippet'] }}@else<span class="empty-message">{{ __('Deze e-mail bevat geen leesbare tekst.') }}</span>@endif</section>





        {{-- =====================================================

             DESKTOP ACTIONS

        ====================================================== --}}



        <footer class="message-actions">



            @if($replyEmail !== '')



                <button

                    type="button"

                    class="action-button primary"

                    data-reply-open

                >

                    {{ __('↩ Beantwoorden') }}

                </button>



            @endif





            <a

                href="{{ $backUrl }}"

                class="action-button"

            >

                ←

                Terug naar {{ $activeFolder['title'] }}

            </a>





            <a

                href="{{ route(

                    'gmail.inbox',

                    [

                        'folder' => 'inbox',

                    ]

                ) }}"

                class="action-button"

            >

                {{ __('📥 Inbox') }}

            </a>





            <a

                href="{{ route('home') }}"

                class="action-button"

            >

                {{ __('🏠 Mashal Studio') }}

            </a>



        </footer>



    </article>



</main>





{{-- =============================================================

     MOBILE ACTION BAR

\============================================================== --}}



<nav

    class="mobile-actions"

    aria-label="Mobiele e-mailacties"

>



    <a

        href="{{ $backUrl }}"

        class="mobile-action"

    >

        <span

            class="mobile-action-icon"

            aria-hidden="true"

        >

            ←

        </span>



        {{ $activeFolder['title'] }}

    </a>





    @if($replyEmail !== '')



        <button

            type="button"

            class="mobile-action primary"

            data-reply-open

        >

            <span

                class="mobile-action-icon"

                aria-hidden="true"

            >

                ↩

            </span>



            {{ __('Antwoorden') }}

        </button>



    @else



        <a

            href="{{ route(

                'gmail.inbox',

                [

                    'folder' => 'inbox',

                ]

            ) }}"

            class="mobile-action"

        >

            <span

                class="mobile-action-icon"

                aria-hidden="true"

            >

                📥

            </span>



            Inbox

        </a>



    @endif





    <a

        href="{{ route('home') }}"

        class="mobile-action"

    >

        <span

            class="mobile-action-icon"

            aria-hidden="true"

        >

            🏠

        </span>



        Studio

    </a>



</nav>





{{-- =============================================================

     REPLY WINDOW

\============================================================== --}}



@if($replyEmail !== '')



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

                class="compose-title"

            >

                {{ __('Bericht beantwoorden') }}

            </h2>



            <button

                type="button"

                class="compose-close"

                data-reply-close

                aria-label="Antwoordvenster sluiten"

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

                for="reply-to"

                class="sr-only"

            >

                {{ __('Ontvanger') }}

            </label>



            <input

                id="reply-to"

                type="email"

                name="to"

                class="compose-input"

                value="{{ old(

                    'to',

                    $replyEmail

                ) }}"

                placeholder="{{ __('Aan') }}"

                required

                maxlength="254"

                autocomplete="email"

                inputmode="email"

            >





            <label

                for="reply-subject"

                class="sr-only"

            >

                {{ __('Onderwerp') }}

            </label>



            <input

                id="reply-subject"

                type="text"

                name="subject"

                class="compose-input"

                value="{{ old(

                    'subject',

                    $replySubject

                ) }}"

                placeholder="{{ __('Onderwerp') }}"

                maxlength="998"

            >





            <label

                for="reply-body"

                class="sr-only"

            >

                {{ __('Antwoord') }}

            </label>



            <textarea

                id="reply-body"

                name="body"

                class="compose-textarea"

                placeholder="Schrijf je antwoord..."

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

                    class="compose-sender"

                    title="{{ $currentEmail }}"

                >

                    Van: {{ $currentEmail }}

                </div>



            </footer>



        </form>



    </section>



@endif





<script>

document.addEventListener(

    'DOMContentLoaded',

    () => {



        /*

        |--------------------------------------------------------------------------

        | Berichtdetails

        |--------------------------------------------------------------------------

        */



        const recipientTrigger =

            document.getElementById(

                'recipient-trigger'

            );



        const recipientDetails =

            document.getElementById(

                'recipient-details'

            );



        const recipientArrow =

            document.getElementById(

                'recipient-arrow'

            );





        recipientTrigger?.addEventListener(

            'click',

            () => {



                const isOpen =

                    recipientDetails

                        ?.classList

                        .toggle('active');



                recipientTrigger.setAttribute(

                    'aria-expanded',

                    isOpen

                        ? 'true'

                        : 'false'

                );



                if (recipientArrow) {

                    recipientArrow.textContent =

                        isOpen

                            ? '▴'

                            : '▾';

                }

            }

        );





        /*

        |--------------------------------------------------------------------------

        | Reply compose

        |--------------------------------------------------------------------------

        */



        const body =

            document.body;



        const composeWindow =

            document.getElementById(

                'compose-window'

            );



        const composeOverlay =

            document.getElementById(

                'compose-overlay'

            );



        const openButtons =

            document.querySelectorAll(

                '[data-reply-open]'

            );



        const closeButtons =

            document.querySelectorAll(

                '[data-reply-close]'

            );





        function openReply() {

            if (!composeWindow) {

                return;

            }



            composeWindow.classList.add(

                'active'

            );



            composeOverlay?.classList.add(

                'active'

            );



            composeWindow.setAttribute(

                'aria-hidden',

                'false'

            );



            composeOverlay?.setAttribute(

                'aria-hidden',

                'false'

            );



            body.classList.add(

                'compose-open'

            );



            window.setTimeout(

                () => {

                    document

                        .getElementById(

                            'reply-body'

                        )

                        ?.focus();

                },

                80

            );

        }





        function closeReply() {

            if (!composeWindow) {

                return;

            }



            composeWindow.classList.remove(

                'active'

            );



            composeOverlay?.classList.remove(

                'active'

            );



            composeWindow.setAttribute(

                'aria-hidden',

                'true'

            );



            composeOverlay?.setAttribute(

                'aria-hidden',

                'true'

            );



            body.classList.remove(

                'compose-open'

            );

        }





        openButtons.forEach(

            (button) => {

                button.addEventListener(

                    'click',

                    openReply

                );

            }

        );





        closeButtons.forEach(

            (button) => {

                button.addEventListener(

                    'click',

                    closeReply

                );

            }

        );





        composeOverlay?.addEventListener(

            'click',

            closeReply

        );





        document.addEventListener(

            'keydown',

            (event) => {



                if (

                    event.key === 'Escape' &&

                    composeWindow

                        ?.classList

                        .contains('active')

                ) {

                    closeReply();

                }

            }

        );





        /*

        |--------------------------------------------------------------------------

        | Validatiefout

        |--------------------------------------------------------------------------

        */



        @if($errors->any() && old('to'))

            openReply();

        @endif

    }

);

</script>



</body>

</html>
