<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <meta
        name="theme-color"
        content="#07080b"
    >

    <meta
        name="color-scheme"
        content="dark"
    >

    <meta
        name="description"
        content="@yield('meta_description', 'Mashal Studio — upload, edit and manage images in your private workspace.')"
    >

    <title>
        @yield('title', 'Mashal Studio')
    </title>

    @php
        $layoutUser = auth()->user();

        $hasHome = \Illuminate\Support\Facades\Route::has('home');
        $hasLogin = \Illuminate\Support\Facades\Route::has('login');
        $hasRegister = \Illuminate\Support\Facades\Route::has('register');
        $hasLogout = \Illuminate\Support\Facades\Route::has('logout');
        $hasImagesIndex = \Illuminate\Support\Facades\Route::has('images.index');
        $hasAccount = \Illuminate\Support\Facades\Route::has('account');
        $hasSecurity = \Illuminate\Support\Facades\Route::has('security.index');
        $hasAdmin = \Illuminate\Support\Facades\Route::has('admin.dashboard');

        $layoutInitials = 'M';
        $layoutAvatarUrl = null;
        $layoutImageCount = null;
        $layoutIsAdmin = (bool) ($layoutUser->is_admin ?? false);

        if ($layoutUser) {
            if (
                method_exists($layoutUser, 'initials') &&
                $layoutUser->initials()
            ) {
                $layoutInitials = (string) $layoutUser->initials();
            } else {
                $nameParts = preg_split(
                    '/\s+/',
                    trim((string) $layoutUser->name)
                ) ?: [];

                $layoutInitials = '';

                foreach (array_slice($nameParts, 0, 2) as $namePart) {
                    $layoutInitials .= mb_strtoupper(
                        mb_substr($namePart, 0, 1)
                    );
                }

                if ($layoutInitials === '') {
                    $layoutInitials = 'M';
                }
            }

            if (
                method_exists($layoutUser, 'avatarUrl')
            ) {
                $layoutAvatarUrl = $layoutUser->avatarUrl();
            }

            if (
                class_exists(\App\Models\Image::class)
            ) {
                try {
                    $layoutImageCount = \App\Models\Image::query()
                        ->where('user_id', $layoutUser->id)
                        ->count();
                } catch (\Throwable $exception) {
                    $layoutImageCount = null;
                }
            }
        }
    @endphp

    <style>
        :root {
            --studio-bg: #07080b;
            --studio-bg-deep: #050608;
            --studio-surface: #0d1015;
            --studio-surface-2: #12161d;
            --studio-surface-3: #171c24;

            --studio-text: #f7f7f4;
            --studio-text-soft: #d8dce1;
            --studio-muted: #8a929d;
            --studio-muted-2: #646c76;
            --studio-muted-3: #474e57;

            --studio-line: rgba(255, 255, 255, .072);
            --studio-line-strong: rgba(255, 255, 255, .12);

            --studio-gold: #e3b36b;
            --studio-gold-light: #f3d69a;
            --studio-gold-deep: #b77d38;
            --studio-gold-soft: rgba(227, 179, 107, .075);

            --studio-success: #67d990;
            --studio-warning: #f0c46d;
            --studio-danger: #f47d7d;

            --studio-header-height: 76px;
            --studio-shell: 1380px;

            --studio-radius-sm: 12px;
            --studio-radius: 18px;
            --studio-radius-lg: 26px;

            --studio-shadow:
                0 34px 100px rgba(0, 0, 0, .40);

            --studio-shadow-soft:
                0 18px 48px rgba(0, 0, 0, .24);

            --studio-font:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Arial,
                sans-serif;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html {
            min-width: 320px;
            min-height: 100%;
            scroll-behavior: smooth;
            background: var(--studio-bg);
        }

        body {
            min-width: 320px;
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            color: var(--studio-text);
            background:
                radial-gradient(
                    circle at 12% 7%,
                    rgba(227, 179, 107, .07),
                    transparent 30rem
                ),
                radial-gradient(
                    circle at 88% 12%,
                    rgba(93, 116, 255, .05),
                    transparent 32rem
                ),
                linear-gradient(
                    180deg,
                    #07080b,
                    #080a0e 48%,
                    #07080b
                );
            font-family: var(--studio-font);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }

        body.studio-menu-open {
            overflow: hidden;
        }

        a {
            color: inherit;
        }

        button,
        input,
        select,
        textarea {
            font: inherit;
        }

        button {
            color: inherit;
        }

        img,
        picture,
        video,
        canvas,
        svg {
            max-width: 100%;
        }

        img {
            display: block;
        }

        [hidden] {
            display: none !important;
        }

        ::selection {
            color: #171009;
            background: var(--studio-gold-light);
        }

        :focus-visible {
            outline: 2px solid rgba(243, 214, 154, .82);
            outline-offset: 3px;
        }

        .studio-skip-link {
            position: fixed;
            z-index: 99999;
            left: 16px;
            top: 12px;
            min-height: 40px;
            padding: 0 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transform: translateY(-160%);
            border-radius: 10px;
            color: #171009;
            background: var(--studio-gold-light);
            text-decoration: none;
            font-size: 9px;
            font-weight: 950;
            transition: transform .2s ease;
        }

        .studio-skip-link:focus {
            transform: translateY(0);
        }

        .studio-atmosphere {
            position: fixed;
            z-index: -10;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .studio-atmosphere::before {
            content: "";
            position: absolute;
            inset: 0;
            opacity: .12;
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
            background-size: 72px 72px;
            mask-image:
                linear-gradient(
                    to bottom,
                    #000,
                    transparent 84%
                );
        }

        .studio-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: .14;
            animation:
                studioOrbFloat
                18s
                ease-in-out
                infinite
                alternate;
        }

        .studio-orb.one {
            width: 430px;
            height: 430px;
            left: -170px;
            top: 20vh;
            background: rgba(227,179,107,.18);
        }

        .studio-orb.two {
            width: 520px;
            height: 520px;
            right: -230px;
            top: 8vh;
            background: rgba(108,92,255,.12);
            animation-delay: -7s;
        }

        .studio-orb.three {
            width: 420px;
            height: 420px;
            right: 25%;
            bottom: -220px;
            background: rgba(87,181,255,.09);
            animation-delay: -12s;
        }

        @keyframes studioOrbFloat {
            from {
                transform:
                    translate3d(0,0,0)
                    scale(1);
            }

            to {
                transform:
                    translate3d(45px,32px,0)
                    scale(1.10);
            }
        }

        .studio-progress {
            position: fixed;
            z-index: 1500;
            left: 0;
            top: 0;
            width: 100%;
            height: 2px;
            pointer-events: none;
        }

        .studio-progress-bar {
            width: 0;
            height: 100%;
            background:
                linear-gradient(
                    90deg,
                    var(--studio-gold-deep),
                    var(--studio-gold-light),
                    #fff0c5
                );
            box-shadow:
                0 0 20px rgba(227,179,107,.34);
        }

        .studio-shell {
            width:
                min(
                    calc(100% - 40px),
                    var(--studio-shell)
                );
            margin-inline: auto;
        }

        .studio-header {
            position: sticky;
            z-index: 1000;
            top: 0;
            border-bottom:
                1px solid rgba(255,255,255,.052);
            background:
                rgba(7,8,11,.72);
            backdrop-filter:
                blur(24px)
                saturate(135%);
            -webkit-backdrop-filter:
                blur(24px)
                saturate(135%);
            transition:
                background .22s ease,
                border-color .22s ease,
                box-shadow .22s ease;
        }

        .studio-header.is-scrolled {
            border-color:
                rgba(227,179,107,.10);
            background:
                rgba(7,8,11,.93);
            box-shadow:
                0 18px 50px rgba(0,0,0,.25);
        }

        .studio-nav {
            min-height: var(--studio-header-height);
            display: grid;
            grid-template-columns:
                auto
                minmax(0,1fr)
                auto;
            align-items: center;
            gap: 26px;
        }

        .studio-brand {
            min-width: max-content;
            display: inline-flex;
            align-items: center;
            gap: 11px;
            color: var(--studio-text);
            text-decoration: none;
        }

        .studio-brand-mark {
            position: relative;
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            display: grid;
            place-items: center;
            overflow: hidden;
            border:
                1px solid rgba(227,179,107,.18);
            border-radius: 14px;
            color: var(--studio-gold-light);
            background:
                linear-gradient(
                    145deg,
                    rgba(227,179,107,.13),
                    rgba(227,179,107,.025)
                );
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,.05),
                0 14px 34px rgba(0,0,0,.18);
            font-size: 14px;
            font-weight: 950;
            letter-spacing: -.06em;
        }

        .studio-brand-mark::after {
            content: "";
            position: absolute;
            width: 26px;
            height: 26px;
            right: -14px;
            top: -14px;
            border-radius: 50%;
            background: rgba(255,255,255,.12);
            filter: blur(6px);
        }

        .studio-brand-copy {
            min-width: 0;
            display: flex;
            flex-direction: column;
            line-height: 1;
        }

        .studio-brand-copy strong {
            color: #f7f7f4;
            font-size: 14px;
            font-weight: 950;
            letter-spacing: -.03em;
        }

        .studio-brand-copy small {
            margin-top: 6px;
            color: #666d77;
            font-size: 7px;
            font-weight: 900;
            letter-spacing: .18em;
            text-transform: uppercase;
        }

        .studio-nav-center {
            min-width: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .studio-nav-link {
            position: relative;
            min-height: 42px;
            padding: 0 13px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid transparent;
            border-radius: 12px;
            color: #858c96;
            background: transparent;
            text-decoration: none;
            font-size: 10px;
            font-weight: 850;
            transition:
                color .2s ease,
                background .2s ease,
                border-color .2s ease,
                transform .2s ease;
        }

        .studio-nav-link:hover {
            color: #e4e6e8;
            background: rgba(255,255,255,.025);
        }

        .studio-nav-link.active {
            border-color:
                rgba(227,179,107,.11);
            color: #e3bd81;
            background:
                rgba(227,179,107,.045);
        }

        .studio-nav-link.active::after {
            content: "";
            position: absolute;
            left: 14px;
            right: 14px;
            bottom: 4px;
            height: 1px;
            background:
                linear-gradient(
                    90deg,
                    transparent,
                    var(--studio-gold),
                    transparent
                );
        }

        .studio-nav-count {
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border:
                1px solid rgba(227,179,107,.14);
            border-radius: 999px;
            color: #d6aa69;
            background:
                rgba(227,179,107,.055);
            font-size: 7px;
            font-weight: 950;
        }

        .studio-nav-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
        }

        .studio-action-link,
        .studio-action-button {
            min-height: 41px;
            padding: 0 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border:
                1px solid rgba(255,255,255,.075);
            border-radius: 11px;
            color: #b8bdc5;
            background:
                rgba(255,255,255,.018);
            text-decoration: none;
            font-size: 9px;
            font-weight: 900;
            cursor: pointer;
            transition:
                transform .2s ease,
                color .2s ease,
                border-color .2s ease,
                background .2s ease,
                box-shadow .2s ease;
        }

        .studio-action-link:hover,
        .studio-action-button:hover {
            transform: translateY(-1px);
            border-color:
                rgba(227,179,107,.16);
            color: #eceef0;
            background:
                rgba(227,179,107,.035);
        }

        .studio-action-link.primary,
        .studio-action-button.primary {
            border-color:
                rgba(227,179,107,.18);
            color: #171009;
            background:
                linear-gradient(
                    135deg,
                    var(--studio-gold-light),
                    #d39a4f
                );
            box-shadow:
                0 14px 35px rgba(227,179,107,.13);
        }

        .studio-account-wrap {
            position: relative;
        }

        .studio-account-trigger {
            min-height: 43px;
            max-width: 230px;
            padding: 5px 9px 5px 5px;
            display: flex;
            align-items: center;
            gap: 9px;
            border:
                1px solid rgba(255,255,255,.075);
            border-radius: 999px;
            color: #d3d6db;
            background:
                rgba(255,255,255,.018);
            cursor: pointer;
            transition:
                border-color .2s ease,
                background .2s ease,
                box-shadow .2s ease;
        }

        .studio-account-trigger:hover,
        .studio-account-trigger[aria-expanded="true"] {
            border-color:
                rgba(227,179,107,.18);
            background:
                rgba(227,179,107,.035);
            box-shadow:
                0 12px 34px rgba(0,0,0,.18);
        }

        .studio-account-avatar {
            width: 31px;
            height: 31px;
            flex: 0 0 31px;
            display: grid;
            place-items: center;
            overflow: hidden;
            border:
                1px solid rgba(227,179,107,.15);
            border-radius: 50%;
            color: #e4ba79;
            background:
                rgba(227,179,107,.07);
            font-size: 9px;
            font-weight: 950;
        }

        .studio-account-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .studio-account-trigger-copy {
            min-width: 0;
            flex: 1;
            text-align: left;
        }

        .studio-account-trigger-copy strong,
        .studio-account-trigger-copy small {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .studio-account-trigger-copy strong {
            color: #dfe2e5;
            font-size: 9px;
            font-weight: 900;
        }

        .studio-account-trigger-copy small {
            margin-top: 2px;
            color: #5f6670;
            font-size: 7px;
            font-weight: 800;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .studio-account-chevron {
            color: #686f79;
            font-size: 9px;
            transition: transform .2s ease;
        }

        .studio-account-trigger[aria-expanded="true"]
        .studio-account-chevron {
            transform: rotate(180deg);
        }

        .studio-account-menu {
            position: absolute;
            z-index: 1050;
            right: 0;
            top: calc(100% + 12px);
            width:
                min(
                    330px,
                    calc(100vw - 32px)
                );
            overflow: hidden;
            border:
                1px solid rgba(255,255,255,.085);
            border-radius: 19px;
            background:
                rgba(11,13,18,.97);
            box-shadow:
                0 34px 100px rgba(0,0,0,.43);
            backdrop-filter: blur(24px);
            opacity: 0;
            visibility: hidden;
            transform:
                translateY(-8px)
                scale(.98);
            transform-origin: top right;
            pointer-events: none;
            transition:
                opacity .18s ease,
                visibility .18s ease,
                transform .18s ease;
        }

        .studio-account-menu.is-open {
            opacity: 1;
            visibility: visible;
            transform:
                translateY(0)
                scale(1);
            pointer-events: auto;
        }

        .studio-account-menu-head {
            padding: 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom:
                1px solid rgba(255,255,255,.055);
            background:
                linear-gradient(
                    145deg,
                    rgba(227,179,107,.045),
                    transparent
                );
        }

        .studio-account-menu-avatar {
            width: 45px;
            height: 45px;
            flex: 0 0 45px;
            display: grid;
            place-items: center;
            overflow: hidden;
            border:
                1px solid rgba(227,179,107,.15);
            border-radius: 14px;
            color: #e6bc7b;
            background:
                rgba(227,179,107,.06);
            font-size: 13px;
            font-weight: 950;
        }

        .studio-account-menu-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .studio-account-menu-user {
            min-width: 0;
        }

        .studio-account-menu-user strong,
        .studio-account-menu-user span {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .studio-account-menu-user strong {
            color: #edeef0;
            font-size: 11px;
        }

        .studio-account-menu-user span {
            margin-top: 3px;
            color: #68707a;
            font-size: 8px;
        }

        .studio-account-menu-body {
            padding: 9px;
        }

        .studio-account-menu-link,
        .studio-account-menu-logout {
            width: 100%;
            min-height: 44px;
            padding: 0 11px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            border: 1px solid transparent;
            border-radius: 11px;
            color: #959ca5;
            background: transparent;
            text-decoration: none;
            font-size: 9px;
            font-weight: 850;
            cursor: pointer;
            transition:
                color .2s ease,
                border-color .2s ease,
                background .2s ease;
        }

        .studio-account-menu-link:hover {
            border-color:
                rgba(227,179,107,.09);
            color: #d6d9dd;
            background:
                rgba(227,179,107,.035);
        }

        .studio-account-menu-logout {
            color: #c98282;
        }

        .studio-account-menu-logout:hover {
            border-color:
                rgba(244,125,125,.10);
            background:
                rgba(244,125,125,.035);
        }

        .studio-account-menu-divider {
            height: 1px;
            margin: 8px 3px;
            background:
                rgba(255,255,255,.055);
        }

        .studio-menu-toggle {
            width: 43px;
            height: 43px;
            display: none;
            place-items: center;
            border:
                1px solid rgba(255,255,255,.075);
            border-radius: 12px;
            color: #fff;
            background:
                rgba(255,255,255,.018);
            cursor: pointer;
        }

        .studio-menu-lines,
        .studio-menu-lines::before,
        .studio-menu-lines::after {
            width: 18px;
            height: 1.5px;
            display: block;
            border-radius: 999px;
            background: currentColor;
            transition:
                transform .2s ease,
                opacity .2s ease,
                top .2s ease;
        }

        .studio-menu-lines {
            position: relative;
        }

        .studio-menu-lines::before,
        .studio-menu-lines::after {
            content: "";
            position: absolute;
            left: 0;
        }

        .studio-menu-lines::before {
            top: -6px;
        }

        .studio-menu-lines::after {
            top: 6px;
        }

        .studio-menu-toggle[aria-expanded="true"]
        .studio-menu-lines {
            background: transparent;
        }

        .studio-menu-toggle[aria-expanded="true"]
        .studio-menu-lines::before {
            top: 0;
            transform: rotate(45deg);
        }

        .studio-menu-toggle[aria-expanded="true"]
        .studio-menu-lines::after {
            top: 0;
            transform: rotate(-45deg);
        }

        .studio-mobile-overlay {
            position: fixed;
            z-index: 1090;
            inset: 0;
            display: none;
            background:
                rgba(0,0,0,.60);
            backdrop-filter: blur(5px);
            opacity: 0;
            pointer-events: none;
            transition: opacity .2s ease;
        }

        .studio-mobile-overlay.is-open {
            opacity: 1;
            pointer-events: auto;
        }

        .studio-mobile-drawer {
            position: fixed;
            z-index: 1100;
            right: 0;
            top: 0;
            bottom: 0;
            width:
                min(
                    390px,
                    92vw
                );
            padding: 18px;
            display: none;
            flex-direction: column;
            overflow-y: auto;
            border-left:
                1px solid rgba(255,255,255,.08);
            background:
                rgba(8,10,14,.98);
            box-shadow:
                -30px 0 90px rgba(0,0,0,.42);
            backdrop-filter: blur(24px);
            transform: translateX(105%);
            transition:
                transform .25s cubic-bezier(.2,.7,.2,1);
        }

        .studio-mobile-drawer.is-open {
            transform: translateX(0);
        }

        .studio-mobile-head {
            min-height: 50px;
            padding-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            border-bottom:
                1px solid rgba(255,255,255,.06);
        }

        .studio-mobile-head strong {
            color: #e8eaec;
            font-size: 11px;
        }

        .studio-mobile-close {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border:
                1px solid rgba(255,255,255,.07);
            border-radius: 11px;
            color: #a0a6ae;
            background:
                rgba(255,255,255,.02);
            font-size: 16px;
            cursor: pointer;
        }

        .studio-mobile-user {
            margin-top: 16px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            border:
                1px solid rgba(227,179,107,.09);
            border-radius: 15px;
            background:
                rgba(227,179,107,.025);
        }

        .studio-mobile-user-copy {
            min-width: 0;
        }

        .studio-mobile-user-copy strong,
        .studio-mobile-user-copy span {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .studio-mobile-user-copy strong {
            color: #dfe2e5;
            font-size: 10px;
        }

        .studio-mobile-user-copy span {
            margin-top: 3px;
            color: #666d77;
            font-size: 8px;
        }

        .studio-mobile-nav {
            margin-top: 18px;
            display: grid;
            gap: 6px;
        }

        .studio-mobile-nav-label {
            margin: 14px 7px 6px;
            color: #4f5660;
            font-size: 7px;
            font-weight: 950;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .studio-mobile-link {
            min-height: 48px;
            padding: 0 13px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            border: 1px solid transparent;
            border-radius: 12px;
            color: #8f969f;
            text-decoration: none;
            font-size: 10px;
            font-weight: 850;
        }

        .studio-mobile-link:hover,
        .studio-mobile-link.active {
            border-color:
                rgba(227,179,107,.10);
            color: #dfb979;
            background:
                rgba(227,179,107,.035);
        }

        .studio-mobile-actions {
            margin-top: auto;
            padding-top: 24px;
            display: grid;
            gap: 8px;
        }

        .studio-mobile-actions .studio-action-link,
        .studio-mobile-actions .studio-action-button {
            width: 100%;
            min-height: 48px;
        }

        .studio-flash-stack {
            position: relative;
            z-index: 50;
            width:
                min(
                    calc(100% - 32px),
                    1180px
                );
            margin: 17px auto -2px;
            display: grid;
            gap: 9px;
        }

        .studio-flash {
            position: relative;
            padding: 14px 42px 14px 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            overflow: hidden;
            border:
                1px solid rgba(255,255,255,.075);
            border-radius: 14px;
            color: #a7adb5;
            background:
                rgba(13,16,21,.90);
            box-shadow:
                0 15px 40px rgba(0,0,0,.20);
            backdrop-filter: blur(16px);
            font-size: 10px;
            line-height: 1.65;
        }

        .studio-flash::before {
            content: "";
            width: 7px;
            height: 7px;
            flex: 0 0 7px;
            margin-top: 5px;
            border-radius: 50%;
            background: #848b95;
            box-shadow:
                0 0 0 5px rgba(132,139,149,.06);
        }

        .studio-flash.success {
            border-color:
                rgba(103,217,144,.14);
            color: #acdfbb;
            background:
                rgba(103,217,144,.045);
        }

        .studio-flash.success::before {
            background: var(--studio-success);
        }

        .studio-flash.warning {
            border-color:
                rgba(240,196,109,.15);
            color: #d7bd8b;
            background:
                rgba(240,196,109,.045);
        }

        .studio-flash.warning::before {
            background: var(--studio-warning);
        }

        .studio-flash.error {
            border-color:
                rgba(244,125,125,.15);
            color: #e1a4a4;
            background:
                rgba(244,125,125,.045);
        }

        .studio-flash.error::before {
            background: var(--studio-danger);
        }

        .studio-flash-content {
            min-width: 0;
            flex: 1;
        }

        .studio-flash-content strong {
            display: block;
            margin-bottom: 2px;
            color: inherit;
            font-size: 10px;
        }

        .studio-flash-list {
            margin: 6px 0 0 16px;
            padding: 0;
            display: grid;
            gap: 3px;
        }

        .studio-flash-close {
            position: absolute;
            right: 10px;
            top: 10px;
            width: 28px;
            height: 28px;
            display: grid;
            place-items: center;
            border:
                1px solid rgba(255,255,255,.06);
            border-radius: 8px;
            color: #646b75;
            background:
                rgba(255,255,255,.015);
            font-size: 12px;
            cursor: pointer;
        }

        .studio-main {
            position: relative;
            z-index: 1;
            flex: 1;
            width: 100%;
            min-height:
                calc(
                    100vh -
                    var(--studio-header-height)
                );
        }

        .studio-quick-upload {
            position: fixed;
            z-index: 800;
            right: 20px;
            bottom: 20px;
            min-height: 50px;
            padding: 0 18px 0 12px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border:
                1px solid rgba(227,179,107,.20);
            border-radius: 999px;
            color: #181109;
            background:
                linear-gradient(
                    135deg,
                    var(--studio-gold-light),
                    #d19a51
                );
            box-shadow:
                0 20px 55px rgba(0,0,0,.35),
                0 10px 30px rgba(227,179,107,.14);
            text-decoration: none;
            font-size: 9px;
            font-weight: 950;
            transition:
                transform .2s ease,
                box-shadow .2s ease;
        }

        .studio-quick-upload:hover {
            transform: translateY(-3px);
            box-shadow:
                0 26px 66px rgba(0,0,0,.38),
                0 14px 38px rgba(227,179,107,.19);
        }

        .studio-quick-upload-icon {
            width: 30px;
            height: 30px;
            display: grid;
            place-items: center;
            border:
                1px solid rgba(24,17,9,.11);
            border-radius: 50%;
            background:
                rgba(255,255,255,.19);
            font-size: 13px;
        }

        .studio-page-head {
            padding: 72px 0 38px;
        }

        .studio-page-kicker {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--studio-gold);
            font-size: 9px;
            font-weight: 950;
            letter-spacing: .18em;
            text-transform: uppercase;
        }

        .studio-page-kicker::before {
            content: "";
            width: 30px;
            height: 1px;
            background:
                linear-gradient(
                    90deg,
                    var(--studio-gold),
                    transparent
                );
        }

        .studio-page-title {
            max-width: 840px;
            margin: 14px 0 0;
            color: #f7f7f4;
            font-size:
                clamp(
                    44px,
                    5vw,
                    74px
                );
            font-weight: 950;
            line-height: .98;
            letter-spacing: -.06em;
        }

        .studio-page-description {
            max-width: 680px;
            margin: 18px 0 0;
            color: #858c96;
            font-size: 13px;
            line-height: 1.85;
        }

        .studio-panel {
            border:
                1px solid var(--studio-line);
            border-radius:
                var(--studio-radius-lg);
            background:
                linear-gradient(
                    145deg,
                    rgba(255,255,255,.035),
                    rgba(255,255,255,.008)
                ),
                var(--studio-surface);
            box-shadow:
                var(--studio-shadow-soft);
        }

        .studio-footer {
            position: relative;
            z-index: 1;
            margin-top: auto;
            border-top:
                1px solid rgba(255,255,255,.055);
            background:
                linear-gradient(
                    180deg,
                    rgba(10,12,16,.96),
                    #060709
                );
        }

        .studio-footer-main {
            padding: 60px 0 40px;
            display: grid;
            grid-template-columns:
                minmax(0,1.25fr)
                repeat(3,minmax(130px,.7fr));
            gap: 46px;
        }

        .studio-footer-about {
            max-width: 480px;
        }

        .studio-footer-logo {
            display: inline-flex;
            align-items: center;
            gap: 11px;
            color: #f0f1f2;
            text-decoration: none;
        }

        .studio-footer-logo .studio-brand-mark {
            width: 39px;
            height: 39px;
            flex-basis: 39px;
        }

        .studio-footer-about p {
            margin: 18px 0 0;
            color: #676e78;
            font-size: 10px;
            line-height: 1.8;
        }

        .studio-footer-trust {
            margin-top: 18px;
            display: flex;
            align-items: center;
            gap: 9px;
            color: #69717a;
            font-size: 8px;
            font-weight: 850;
        }

        .studio-footer-trust::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--studio-success);
            box-shadow:
                0 0 0 5px rgba(103,217,144,.05);
        }

        .studio-footer-column h3 {
            margin: 0 0 15px;
            color: #c7cbd1;
            font-size: 9px;
            font-weight: 950;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .studio-footer-links {
            display: grid;
            gap: 9px;
        }

        .studio-footer-links a {
            width: fit-content;
            color: #6f7680;
            text-decoration: none;
            font-size: 9px;
            font-weight: 800;
            transition:
                color .2s ease,
                transform .2s ease;
        }

        .studio-footer-links a:hover {
            color: #cfa566;
            transform: translateX(2px);
        }

        .studio-footer-bottom {
            min-height: 68px;
            padding: 17px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            border-top:
                1px solid rgba(255,255,255,.045);
            color: #4f5660;
            font-size: 8px;
            font-weight: 800;
            letter-spacing: .05em;
        }

        .studio-footer-bottom-links {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .is-loading {
            opacity: .62 !important;
            pointer-events: none !important;
            cursor: wait !important;
        }

        .studio-reveal {
            opacity: 0;
            transform: translateY(16px);
            transition:
                opacity .55s ease,
                transform .55s ease;
        }

        .studio-reveal.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 1120px) {
            .studio-nav-center,
            .studio-nav-actions > .desktop-only,
            .studio-account-wrap {
                display: none;
            }

            .studio-nav {
                grid-template-columns:
                    auto
                    minmax(0,1fr)
                    auto;
            }

            .studio-menu-toggle,
            .studio-mobile-drawer,
            .studio-mobile-overlay {
                display: flex;
            }

            .studio-menu-toggle {
                display: grid;
            }

            .studio-mobile-overlay {
                display: block;
            }
        }

        @media (max-width: 900px) {
            .studio-footer-main {
                grid-template-columns:
                    1.2fr 1fr;
            }
        }

        @media (max-width: 640px) {
            :root {
                --studio-header-height: 70px;
            }

            .studio-shell {
                width:
                    min(
                        calc(100% - 24px),
                        var(--studio-shell)
                    );
            }

            .studio-brand-copy small {
                display: none;
            }

            .studio-action-link.primary.desktop-only {
                display: none;
            }

            .studio-footer-main {
                grid-template-columns: 1fr;
                gap: 30px;
                padding-top: 44px;
            }

            .studio-footer-bottom {
                align-items: flex-start;
                flex-direction: column;
            }

            .studio-quick-upload {
                right: 12px;
                bottom: 12px;
            }

            .studio-quick-upload > span:last-child {
                display: none;
            }

            .studio-quick-upload {
                width: 48px;
                height: 48px;
                padding: 0;
                justify-content: center;
            }

            .studio-quick-upload-icon {
                width: 30px;
                height: 30px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            html {
                scroll-behavior: auto;
            }

            *,
            *::before,
            *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
            }

            .studio-reveal {
                opacity: 1;
                transform: none;
            }
        }

        @media print {
            .studio-header,
            .studio-footer,
            .studio-progress,
            .studio-mobile-drawer,
            .studio-mobile-overlay,
            .studio-quick-upload,
            .studio-flash-stack {
                display: none !important;
            }

            body {
                color: #000;
                background: #fff;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
    <a
        class="studio-skip-link"
        href="#studioMain"
    >
        Ga naar inhoud
    </a>

    <div
        class="studio-atmosphere"
        aria-hidden="true"
    >
        <span class="studio-orb one"></span>
        <span class="studio-orb two"></span>
        <span class="studio-orb three"></span>
    </div>

    <div
        class="studio-progress"
        aria-hidden="true"
    >
        <div
            class="studio-progress-bar"
            id="studioProgressBar"
        ></div>
    </div>

    <header
        class="studio-header"
        id="studioHeader"
    >
        <div class="studio-shell">
            <div class="studio-nav">
                <a
                    class="studio-brand"
                    href="{{ $hasHome ? route('home') : '#' }}"
                    aria-label="Mashal Studio home"
                >
                    <span class="studio-brand-mark">
                        M
                    </span>

                    <span class="studio-brand-copy">
                        <strong>
                            Mashal Studio
                        </strong>

                        <small>
                            Image workspace
                        </small>
                    </span>
                </a>

                <nav
                    class="studio-nav-center"
                    aria-label="Hoofdnavigatie"
                >
                    @if ($hasHome)
                        <a
                            class="studio-nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                            href="{{ route('home') }}"
                        >
                            Studio
                        </a>
                    @endif

                    @auth
                        @if ($hasImagesIndex)
                            <a
                                class="studio-nav-link {{ request()->routeIs('images.*') ? 'active' : '' }}"
                                href="{{ route('images.index') }}"
                            >
                                Mijn afbeeldingen

                                @if ($layoutImageCount !== null)
                                    <span class="studio-nav-count">
                                        {{ $layoutImageCount > 999 ? '999+' : $layoutImageCount }}
                                    </span>
                                @endif
                            </a>
                        @endif

                        @if ($hasAccount)
                            <a
                                class="studio-nav-link {{ request()->routeIs('account') ? 'active' : '' }}"
                                href="{{ route('account') }}"
                            >
                                Account
                            </a>
                        @endif
                    @endauth
                </nav>

                <div class="studio-nav-actions">
                    @guest
                        @if ($hasLogin)
                            <a
                                class="studio-action-link desktop-only"
                                href="{{ route('login') }}"
                            >
                                Inloggen
                            </a>
                        @endif

                        @if ($hasRegister)
                            <a
                                class="studio-action-link primary desktop-only"
                                href="{{ route('register') }}"
                            >
                                Account maken
                            </a>
                        @endif
                    @else
                        <a
                            class="studio-action-link primary desktop-only"
                            href="{{ route('home') }}#upload"
                        >
                            + Nieuwe afbeelding
                        </a>

                        <div class="studio-account-wrap">
                            <button
                                class="studio-account-trigger"
                                id="studioAccountTrigger"
                                type="button"
                                aria-expanded="false"
                                aria-controls="studioAccountMenu"
                            >
                                <span class="studio-account-avatar">
                                    @if ($layoutAvatarUrl)
                                        <img
                                            src="{{ $layoutAvatarUrl }}"
                                            alt=""
                                        >
                                    @else
                                        {{ $layoutInitials }}
                                    @endif
                                </span>

                                <span class="studio-account-trigger-copy">
                                    <strong>
                                        {{ $layoutUser->name }}
                                    </strong>

                                    <small>
                                        Mijn workspace
                                    </small>
                                </span>

                                <span
                                    class="studio-account-chevron"
                                    aria-hidden="true"
                                >
                                    ▾
                                </span>
                            </button>

                            <div
                                class="studio-account-menu"
                                id="studioAccountMenu"
                                aria-hidden="true"
                            >
                                <div class="studio-account-menu-head">
                                    <span class="studio-account-menu-avatar">
                                        @if ($layoutAvatarUrl)
                                            <img
                                                src="{{ $layoutAvatarUrl }}"
                                                alt=""
                                            >
                                        @else
                                            {{ $layoutInitials }}
                                        @endif
                                    </span>

                                    <span class="studio-account-menu-user">
                                        <strong>
                                            {{ $layoutUser->name }}
                                        </strong>

                                        <span>
                                            {{ $layoutUser->email }}
                                        </span>
                                    </span>
                                </div>

                                <div class="studio-account-menu-body">
                                    @if ($hasImagesIndex)
                                        <a
                                            class="studio-account-menu-link"
                                            href="{{ route('images.index') }}"
                                        >
                                            <span>
                                                Mijn afbeeldingen
                                            </span>

                                            <span>
                                                {{ $layoutImageCount ?? '—' }}
                                            </span>
                                        </a>
                                    @endif

                                    @if ($hasAccount)
                                        <a
                                            class="studio-account-menu-link"
                                            href="{{ route('account') }}"
                                        >
                                            <span>
                                                Accountinstellingen
                                            </span>

                                            <span>
                                                →
                                            </span>
                                        </a>
                                    @endif

                                    @if ($hasSecurity)
                                        <a
                                            class="studio-account-menu-link"
                                            href="{{ route('security.index') }}"
                                        >
                                            <span>
                                                Beveiliging
                                            </span>

                                            <span>
                                                →
                                            </span>
                                        </a>
                                    @endif

                                    @if (
                                        $layoutIsAdmin &&
                                        $hasAdmin
                                    )
                                        <div class="studio-account-menu-divider"></div>

                                        <a
                                            class="studio-account-menu-link"
                                            href="{{ route('admin.dashboard') }}"
                                        >
                                            <span>
                                                Admin dashboard
                                            </span>

                                            <span>
                                                →
                                            </span>
                                        </a>
                                    @endif

                                    @if ($hasLogout)
                                        <div class="studio-account-menu-divider"></div>

                                        <form
                                            method="POST"
                                            action="{{ route('logout') }}"
                                        >
                                            @csrf

                                            <button
                                                class="studio-account-menu-logout"
                                                type="submit"
                                            >
                                                <span>
                                                    Uitloggen
                                                </span>

                                                <span>
                                                    →
                                                </span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endguest

                    <button
                        class="studio-menu-toggle"
                        id="studioMenuToggle"
                        type="button"
                        aria-label="Menu openen"
                        aria-expanded="false"
                        aria-controls="studioMobileDrawer"
                    >
                        <span class="studio-menu-lines"></span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <div
        class="studio-mobile-overlay"
        id="studioMobileOverlay"
        aria-hidden="true"
    ></div>

    <aside
        class="studio-mobile-drawer"
        id="studioMobileDrawer"
        aria-hidden="true"
        aria-label="Mobiele navigatie"
    >
        <div class="studio-mobile-head">
            <strong>
                Mashal Studio
            </strong>

            <button
                class="studio-mobile-close"
                id="studioMobileClose"
                type="button"
                aria-label="Menu sluiten"
            >
                ×
            </button>
        </div>

        @auth
            <div class="studio-mobile-user">
                <span class="studio-account-menu-avatar">
                    @if ($layoutAvatarUrl)
                        <img
                            src="{{ $layoutAvatarUrl }}"
                            alt=""
                        >
                    @else
                        {{ $layoutInitials }}
                    @endif
                </span>

                <span class="studio-mobile-user-copy">
                    <strong>
                        {{ $layoutUser->name }}
                    </strong>

                    <span>
                        {{ $layoutUser->email }}
                    </span>
                </span>
            </div>
        @endauth

        <nav class="studio-mobile-nav">
            <div class="studio-mobile-nav-label">
                Studio
            </div>

            @if ($hasHome)
                <a
                    class="studio-mobile-link {{ request()->routeIs('home') ? 'active' : '' }}"
                    href="{{ route('home') }}"
                >
                    <span>
                        Home / Studio
                    </span>

                    <span>
                        ◇
                    </span>
                </a>

                <a
                    class="studio-mobile-link"
                    href="{{ route('home') }}#upload"
                >
                    <span>
                        Nieuwe afbeelding
                    </span>

                    <span>
                        ↑
                    </span>
                </a>
            @endif

            @auth
                @if ($hasImagesIndex)
                    <a
                        class="studio-mobile-link {{ request()->routeIs('images.*') ? 'active' : '' }}"
                        href="{{ route('images.index') }}"
                    >
                        <span>
                            Mijn afbeeldingen
                        </span>

                        <span>
                            {{ $layoutImageCount ?? '▦' }}
                        </span>
                    </a>
                @endif

                <div class="studio-mobile-nav-label">
                    Account
                </div>

                @if ($hasAccount)
                    <a
                        class="studio-mobile-link {{ request()->routeIs('account') ? 'active' : '' }}"
                        href="{{ route('account') }}"
                    >
                        <span>
                            Accountinstellingen
                        </span>

                        <span>
                            →
                        </span>
                    </a>
                @endif

                @if ($hasSecurity)
                    <a
                        class="studio-mobile-link {{ request()->routeIs('security.*') ? 'active' : '' }}"
                        href="{{ route('security.index') }}"
                    >
                        <span>
                            Beveiliging
                        </span>

                        <span>
                            ◈
                        </span>
                    </a>
                @endif

                @if (
                    $layoutIsAdmin &&
                    $hasAdmin
                )
                    <a
                        class="studio-mobile-link {{ request()->routeIs('admin.*') ? 'active' : '' }}"
                        href="{{ route('admin.dashboard') }}"
                    >
                        <span>
                            Admin dashboard
                        </span>

                        <span>
                            →
                        </span>
                    </a>
                @endif
            @endauth
        </nav>

        <div class="studio-mobile-actions">
            @guest
                @if ($hasLogin)
                    <a
                        class="studio-action-link"
                        href="{{ route('login') }}"
                    >
                        Inloggen
                    </a>
                @endif

                @if ($hasRegister)
                    <a
                        class="studio-action-link primary"
                        href="{{ route('register') }}"
                    >
                        Gratis account maken
                    </a>
                @endif
            @else
                @if ($hasLogout)
                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                    >
                        @csrf

                        <button
                            class="studio-action-button"
                            type="submit"
                        >
                            Uitloggen
                        </button>
                    </form>
                @endif
            @endguest
        </div>
    </aside>

    @if (
        session('status') ||
        session('success') ||
        session('error') ||
        session('warning') ||
        $errors->any()
    )
        <div
            class="studio-flash-stack"
            id="studioFlashStack"
        >
            @if (session('status'))
                <div
                    class="studio-flash success"
                    data-studio-flash
                >
                    <div class="studio-flash-content">
                        {{ session('status') }}
                    </div>

                    <button
                        class="studio-flash-close"
                        type="button"
                        aria-label="Melding sluiten"
                        data-flash-close
                    >
                        ×
                    </button>
                </div>
            @endif

            @if (session('success'))
                <div
                    class="studio-flash success"
                    data-studio-flash
                >
                    <div class="studio-flash-content">
                        {{ session('success') }}
                    </div>

                    <button
                        class="studio-flash-close"
                        type="button"
                        aria-label="Melding sluiten"
                        data-flash-close
                    >
                        ×
                    </button>
                </div>
            @endif

            @if (session('warning'))
                <div
                    class="studio-flash warning"
                    data-studio-flash
                >
                    <div class="studio-flash-content">
                        {{ session('warning') }}
                    </div>

                    <button
                        class="studio-flash-close"
                        type="button"
                        aria-label="Melding sluiten"
                        data-flash-close
                    >
                        ×
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div
                    class="studio-flash error"
                    data-studio-flash
                >
                    <div class="studio-flash-content">
                        {{ session('error') }}
                    </div>

                    <button
                        class="studio-flash-close"
                        type="button"
                        aria-label="Melding sluiten"
                        data-flash-close
                    >
                        ×
                    </button>
                </div>
            @endif

            @if ($errors->any())
                <div
                    class="studio-flash error"
                    data-studio-flash
                >
                    <div class="studio-flash-content">
                        <strong>
                            Controleer onderstaande gegevens:
                        </strong>

                        <ul class="studio-flash-list">
                            @foreach ($errors->all() as $error)
                                <li>
                                    {{ $error }}
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <button
                        class="studio-flash-close"
                        type="button"
                        aria-label="Melding sluiten"
                        data-flash-close
                    >
                        ×
                    </button>
                </div>
            @endif
        </div>
    @endif

    <main
        class="studio-main"
        id="studioMain"
    >
        @yield('content')
    </main>

    @if (
        $hasHome &&
        ! request()->routeIs('home')
    )
        <a
            class="studio-quick-upload"
            href="{{ route('home') }}#upload"
            aria-label="Nieuwe afbeelding uploaden"
        >
            <span
                class="studio-quick-upload-icon"
                aria-hidden="true"
            >
                ↑
            </span>

            <span>
                Nieuwe afbeelding
            </span>
        </a>
    @endif

    <footer class="studio-footer">
        <div class="studio-shell">
            <div class="studio-footer-main">
                <div class="studio-footer-about">
                    <a
                        class="studio-footer-logo"
                        href="{{ $hasHome ? route('home') : '#' }}"
                    >
                        <span class="studio-brand-mark">
                            M
                        </span>

                        <span class="studio-brand-copy">
                            <strong>
                                Mashal Studio
                            </strong>

                            <small>
                                Image workspace
                            </small>
                        </span>
                    </a>

                    <p>
                        Upload afbeeldingen, beheer originelen en sla nieuwe
                        bewerkingen op als aparte versies binnen je eigen
                        persoonlijke workspace.
                    </p>

                    <div class="studio-footer-trust">
                        Private image workspace
                    </div>
                </div>

                <div class="studio-footer-column">
                    <h3>
                        Studio
                    </h3>

                    <div class="studio-footer-links">
                        @if ($hasHome)
                            <a href="{{ route('home') }}">
                                Home
                            </a>

                            <a href="{{ route('home') }}#upload">
                                Upload afbeelding
                            </a>
                        @endif

                        @auth
                            @if ($hasImagesIndex)
                                <a href="{{ route('images.index') }}">
                                    Mijn afbeeldingen
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>

                <div class="studio-footer-column">
                    <h3>
                        Account
                    </h3>

                    <div class="studio-footer-links">
                        @guest
                            @if ($hasLogin)
                                <a href="{{ route('login') }}">
                                    Inloggen
                                </a>
                            @endif

                            @if ($hasRegister)
                                <a href="{{ route('register') }}">
                                    Registreren
                                </a>
                            @endif
                        @else
                            @if ($hasAccount)
                                <a href="{{ route('account') }}">
                                    Accountinstellingen
                                </a>
                            @endif

                            @if ($hasSecurity)
                                <a href="{{ route('security.index') }}">
                                    Beveiliging
                                </a>
                            @endif
                        @endguest
                    </div>
                </div>

                <div class="studio-footer-column">
                    <h3>
                        Workspace
                    </h3>

                    <div class="studio-footer-links">
                        @if ($hasHome)
                            <a href="{{ route('home') }}#upload">
                                Nieuw project
                            </a>
                        @endif

                        @auth
                            @if ($hasImagesIndex)
                                <a href="{{ route('images.index') }}">
                                    Projectbibliotheek
                                </a>
                            @endif

                            @if (
                                $layoutIsAdmin &&
                                $hasAdmin
                            )
                                <a href="{{ route('admin.dashboard') }}">
                                    Admin
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>

            <div class="studio-footer-bottom">
                <span>
                    © {{ date('Y') }} Mashal Studio
                </span>

                <div class="studio-footer-bottom-links">
                    <span>
                        JPG · PNG · WEBP
                    </span>

                    <span>
                        Private storage
                    </span>
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {
                const body =
                    document.body;

                const header =
                    document.getElementById(
                        'studioHeader'
                    );

                const progressBar =
                    document.getElementById(
                        'studioProgressBar'
                    );

                const menuToggle =
                    document.getElementById(
                        'studioMenuToggle'
                    );

                const mobileDrawer =
                    document.getElementById(
                        'studioMobileDrawer'
                    );

                const mobileOverlay =
                    document.getElementById(
                        'studioMobileOverlay'
                    );

                const mobileClose =
                    document.getElementById(
                        'studioMobileClose'
                    );

                const accountTrigger =
                    document.getElementById(
                        'studioAccountTrigger'
                    );

                const accountMenu =
                    document.getElementById(
                        'studioAccountMenu'
                    );

                let mobileMenuOpen =
                    false;

                let accountMenuOpen =
                    false;

                function updateHeader() {
                    if (!header) {
                        return;
                    }

                    header.classList.toggle(
                        'is-scrolled',
                        window.scrollY > 12
                    );
                }

                function updateProgress() {
                    if (!progressBar) {
                        return;
                    }

                    const root =
                        document.documentElement;

                    const scrollTop =
                        window.scrollY ||
                        root.scrollTop;

                    const scrollable =
                        root.scrollHeight -
                        window.innerHeight;

                    const percentage =
                        scrollable > 0
                            ? Math.min(
                                100,
                                Math.max(
                                    0,
                                    scrollTop /
                                    scrollable *
                                    100
                                )
                            )
                            : 0;

                    progressBar.style.width =
                        percentage + '%';
                }

                function setMobileMenu(open) {
                    mobileMenuOpen =
                        Boolean(open);

                    menuToggle?.setAttribute(
                        'aria-expanded',
                        mobileMenuOpen
                            ? 'true'
                            : 'false'
                    );

                    menuToggle?.setAttribute(
                        'aria-label',
                        mobileMenuOpen
                            ? 'Menu sluiten'
                            : 'Menu openen'
                    );

                    mobileDrawer?.classList.toggle(
                        'is-open',
                        mobileMenuOpen
                    );

                    mobileDrawer?.setAttribute(
                        'aria-hidden',
                        mobileMenuOpen
                            ? 'false'
                            : 'true'
                    );

                    mobileOverlay?.classList.toggle(
                        'is-open',
                        mobileMenuOpen
                    );

                    mobileOverlay?.setAttribute(
                        'aria-hidden',
                        mobileMenuOpen
                            ? 'false'
                            : 'true'
                    );

                    body.classList.toggle(
                        'studio-menu-open',
                        mobileMenuOpen
                    );
                }

                function setAccountMenu(open) {
                    accountMenuOpen =
                        Boolean(open);

                    accountTrigger?.setAttribute(
                        'aria-expanded',
                        accountMenuOpen
                            ? 'true'
                            : 'false'
                    );

                    accountMenu?.classList.toggle(
                        'is-open',
                        accountMenuOpen
                    );

                    accountMenu?.setAttribute(
                        'aria-hidden',
                        accountMenuOpen
                            ? 'false'
                            : 'true'
                    );
                }

                menuToggle?.addEventListener(
                    'click',
                    function () {
                        setAccountMenu(false);

                        setMobileMenu(
                            !mobileMenuOpen
                        );
                    }
                );

                mobileClose?.addEventListener(
                    'click',
                    function () {
                        setMobileMenu(false);
                    }
                );

                mobileOverlay?.addEventListener(
                    'click',
                    function () {
                        setMobileMenu(false);
                    }
                );

                if (
                    accountTrigger &&
                    accountMenu
                ) {
                    accountTrigger.addEventListener(
                        'click',
                        function (event) {
                            event.stopPropagation();

                            setMobileMenu(false);

                            setAccountMenu(
                                !accountMenuOpen
                            );
                        }
                    );

                    accountMenu.addEventListener(
                        'click',
                        function (event) {
                            event.stopPropagation();
                        }
                    );

                    document.addEventListener(
                        'click',
                        function () {
                            setAccountMenu(false);
                        }
                    );
                }

                document.addEventListener(
                    'keydown',
                    function (event) {
                        if (
                            event.key === 'Escape'
                        ) {
                            setMobileMenu(false);
                            setAccountMenu(false);
                        }
                    }
                );

                mobileDrawer
                    ?.querySelectorAll('a')
                    .forEach(
                        function (link) {
                            link.addEventListener(
                                'click',
                                function () {
                                    setMobileMenu(false);
                                }
                            );
                        }
                    );

                document
                    .querySelectorAll(
                        '[data-flash-close]'
                    )
                    .forEach(
                        function (button) {
                            button.addEventListener(
                                'click',
                                function () {
                                    const flash =
                                        button.closest(
                                            '[data-studio-flash]'
                                        );

                                    if (!flash) {
                                        return;
                                    }

                                    flash.remove();
                                }
                            );
                        }
                    );

                const revealElements =
                    Array.from(
                        document.querySelectorAll(
                            '.studio-reveal'
                        )
                    );

                if (
                    'IntersectionObserver' in window &&
                    revealElements.length
                ) {
                    const observer =
                        new IntersectionObserver(
                            function (entries) {
                                entries.forEach(
                                    function (entry) {
                                        if (
                                            !entry.isIntersecting
                                        ) {
                                            return;
                                        }

                                        entry.target.classList.add(
                                            'is-visible'
                                        );

                                        observer.unobserve(
                                            entry.target
                                        );
                                    }
                                );
                            },
                            {
                                threshold: .12,
                                rootMargin:
                                    '0px 0px -30px 0px'
                            }
                        );

                    revealElements.forEach(
                        function (element) {
                            observer.observe(
                                element
                            );
                        }
                    );
                } else {
                    revealElements.forEach(
                        function (element) {
                            element.classList.add(
                                'is-visible'
                            );
                        }
                    );
                }

                document
                    .querySelectorAll(
                        'form'
                    )
                    .forEach(
                        function (form) {
                            form.addEventListener(
                                'submit',
                                function () {
                                    const submit =
                                        form.querySelector(
                                            '[type="submit"]'
                                        );

                                    if (!submit) {
                                        return;
                                    }

                                    if (
                                        submit.dataset.noLoading ===
                                        'true'
                                    ) {
                                        return;
                                    }

                                    submit.classList.add(
                                        'is-loading'
                                    );

                                    submit.setAttribute(
                                        'aria-busy',
                                        'true'
                                    );
                                }
                            );
                        }
                    );

                window.addEventListener(
                    'pageshow',
                    function () {
                        document
                            .querySelectorAll(
                                '.is-loading'
                            )
                            .forEach(
                                function (element) {
                                    element.classList.remove(
                                        'is-loading'
                                    );

                                    element.removeAttribute(
                                        'aria-busy'
                                    );
                                }
                            );
                    }
                );

                updateHeader();
                updateProgress();

                window.addEventListener(
                    'scroll',
                    function () {
                        updateHeader();
                        updateProgress();
                    },
                    {
                        passive: true
                    }
                );

                window.addEventListener(
                    'resize',
                    function () {
                        if (
                            window.innerWidth >
                            1120
                        ) {
                            setMobileMenu(false);
                        }
                    }
                );
            }
        );
    </script>

    @stack('scripts')
</body>
</html>
