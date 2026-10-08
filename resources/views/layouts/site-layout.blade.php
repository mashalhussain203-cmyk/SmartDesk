<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ur' ? 'rtl' : 'ltr' }}">
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
        content="#050609"
    >

    <meta
        name="color-scheme"
        content="dark"
    >

    <meta
        name="description"
        content="@yield('meta_description', 'Mashal Studio — upload, edit, resize and save images in your private image workspace.')"
    >

    <title>
        @yield('title', 'Mashal Studio')
    </title>

    @php
        $layoutUser = auth()->user();

        $hasImagesIndex = \Illuminate\Support\Facades\Route::has('images.index');
        $hasImagesUpload = \Illuminate\Support\Facades\Route::has('images.upload');
        $hasAiChat = \Illuminate\Support\Facades\Route::has('ai.chat');
        $hasTikTokCounter = \Illuminate\Support\Facades\Route::has('tiktok-counter.index');
        $hasTikTokFollowerCounter = \Illuminate\Support\Facades\Route::has('tiktok-follower-counter.index');
        $hasLiveCounts = \Illuminate\Support\Facades\Route::has('live-counts.index');
        $hasAccount = \Illuminate\Support\Facades\Route::has('account');
        $hasSecurity = \Illuminate\Support\Facades\Route::has('security.index');
        $hasAdmin = \Illuminate\Support\Facades\Route::has('admin.dashboard');

        $hasAbout = \Illuminate\Support\Facades\Route::has('about');
        $hasContact = \Illuminate\Support\Facades\Route::has('contact');
        $hasPrivacy = \Illuminate\Support\Facades\Route::has('privacy');
        $hasTerms = \Illuminate\Support\Facades\Route::has('terms');

        $layoutInitials = 'M';

        if ($layoutUser) {
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

        $layoutIsAdmin = (bool) ($layoutUser->is_admin ?? false);

        /*
        |--------------------------------------------------------------------------
        | Performance: geen globale image count query
        |--------------------------------------------------------------------------
        |
        | Deze layout wordt op bijna iedere pagina gerenderd. Een COUNT-query
        | vanuit de layout voegt daardoor voor iedere ingelogde request een extra
        | database-roundtrip toe. De navigatie blijft gewoon werken; alleen het
        | live aantal wordt hier niet meer opgehaald.
        |
        */

        $layoutImageCount = null;
    @endphp

    <style>
        :root {
            --studio-bg:
                #07080b;

            --studio-bg-deep:
                #050608;

            --studio-bg-soft:
                #0b0d12;

            --studio-surface:
                #0e1116;

            --studio-surface-2:
                #12161c;

            --studio-surface-3:
                #171c24;

            --studio-surface-glass:
                rgba(12, 15, 20, .78);

            --studio-text:
                #f7f7f4;

            --studio-text-soft:
                #d8dbe0;

            --studio-muted:
                #8d949e;

            --studio-muted-2:
                #656d77;

            --studio-muted-3:
                #4c535c;

            --studio-line:
                rgba(255, 255, 255, .075);

            --studio-line-strong:
                rgba(255, 255, 255, .13);

            --studio-gold:
                #8f82ff;

            --studio-gold-light:
                #a99fff;

            --studio-gold-deep:
                #5f7dff;

            --studio-gold-soft:
                rgba(122, 108, 255, .08);

            --studio-cyan:
                #73d7ff;

            --studio-purple:
                #9f8cff;

            --studio-success:
                #67d990;

            --studio-success-soft:
                rgba(103, 217, 144, .08);

            --studio-warning:
                #f0c46d;

            --studio-warning-soft:
                rgba(240, 196, 109, .08);

            --studio-danger:
                #f47d7d;

            --studio-danger-soft:
                rgba(244, 125, 125, .08);

            --studio-shadow:
                0 38px 110px rgba(0, 0, 0, .42);

            --studio-shadow-soft:
                0 20px 55px rgba(0, 0, 0, .25);

            --studio-radius-xs:
                9px;

            --studio-radius-sm:
                13px;

            --studio-radius:
                18px;

            --studio-radius-lg:
                26px;

            --studio-radius-xl:
                34px;

            --studio-header-height:
                78px;

            --studio-shell:
                1360px;

            --studio-visual-height:
                100dvh;

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

        /*
        |--------------------------------------------------------------------------
        | Reset
        |--------------------------------------------------------------------------
        */

        *,
        *::before,
        *::after {
            box-sizing:
                border-box;
        }

        html {
            min-width:
                320px;

            scroll-behavior:
                smooth;

            background:
                var(--studio-bg);
        }

        body {
            min-width:
                320px;

            min-height:
                100vh;

            margin:
                0;

            display:
                flex;

            flex-direction:
                column;

            overflow-x:
                hidden;

            color:
                var(--studio-text);

            background:
                var(--studio-bg);

            font-family:
                var(--studio-font);

            line-height:
                1.6;

            -webkit-font-smoothing:
                antialiased;

            text-rendering:
                optimizeLegibility;
        }

        body.studio-menu-open {
            overflow:
                hidden;

            overscroll-behavior:
                none;

            touch-action:
                none;
        }

        body.studio-menu-open .studio-mobile-drawer {
            touch-action:
                pan-y;
        }

        body::selection {
            color:
                #171009;

            background:
                var(--studio-gold-light);
        }

        a {
            color:
                inherit;
        }

        button,
        input,
        select,
        textarea {
            font:
                inherit;
        }

        button {
            color:
                inherit;
        }

        img,
        picture,
        video,
        canvas,
        svg {
            max-width:
                100%;
        }

        img {
            display:
                block;
        }

        [hidden] {
            display:
                none !important;
        }

        /*
        |--------------------------------------------------------------------------
        | Accessibility
        |--------------------------------------------------------------------------
        */

        .studio-skip-link {
            position:
                fixed;

            z-index:
                99999;

            left:
                18px;

            top:
                14px;

            min-height:
                42px;

            padding:
                0 16px;

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            transform:
                translateY(-160%);

            border:
                1px solid rgba(122, 108, 255, .30);

            border-radius:
                11px;

            color:
                #171009;

            background:
                var(--studio-gold-light);

            text-decoration:
                none;

            font-size:
                10px;

            font-weight:
                950;

            transition:
                transform .2s ease;
        }

        .studio-skip-link:focus {
            transform:
                translateY(0);
        }

        :focus-visible {
            outline:
                2px solid rgba(169, 159, 255, .82);

            outline-offset:
                3px;
        }

        /*
        |--------------------------------------------------------------------------
        | Global atmosphere
        |--------------------------------------------------------------------------
        */

        .studio-atmosphere {
            position:
                fixed;

            z-index:
                -100;

            inset:
                0;

            overflow:
                hidden;

            pointer-events:
                none;

            background:
                linear-gradient(
                    180deg,
                    #07080b 0%,
                    #080a0e 52%,
                    #07080b 100%
                );
        }

        .studio-atmosphere::before {
            content:
                "";

            position:
                absolute;

            inset:
                0;

            background:
                radial-gradient(
                    circle at 10% 12%,
                    rgba(122, 108, 255, .075),
                    transparent 29rem
                ),
                radial-gradient(
                    circle at 88% 8%,
                    rgba(115, 215, 255, .04),
                    transparent 31rem
                ),
                radial-gradient(
                    circle at 74% 82%,
                    rgba(159, 140, 255, .045),
                    transparent 32rem
                );
        }

        .studio-atmosphere::after {
            content:
                "";

            position:
                absolute;

            inset:
                0;

            opacity:
                .14;

            background-image:
                linear-gradient(
                    rgba(255, 255, 255, .02) 1px,
                    transparent 1px
                ),
                linear-gradient(
                    90deg,
                    rgba(255, 255, 255, .02) 1px,
                    transparent 1px
                );

            background-size:
                72px 72px;

            mask-image:
                linear-gradient(
                    to bottom,
                    #000,
                    transparent 78%
                );
        }

        .studio-orb {
            position:
                absolute;

            border-radius:
                50%;

            filter:
                blur(64px);

            opacity:
                .12;

            animation:
                studioOrbDrift 28s ease-in-out infinite alternate;

            will-change:
                transform;
        }

        .studio-orb.one {
            width:
                420px;

            height:
                420px;

            left:
                -130px;

            top:
                23vh;

            background:
                rgba(122, 108, 255, .18);
        }

        .studio-orb.two {
            width:
                520px;

            height:
                520px;

            right:
                -210px;

            top:
                7vh;

            background:
                rgba(106, 91, 255, .11);

            animation-delay:
                -6s;
        }

        .studio-orb.three {
            width:
                380px;

            height:
                380px;

            right:
                22%;

            bottom:
                -150px;

            background:
                rgba(78, 179, 255, .08);

            animation-delay:
                -11s;
        }

        @keyframes studioOrbDrift {
            from {
                transform:
                    translate3d(0, 0, 0)
                    scale(1);
            }

            to {
                transform:
                    translate3d(50px, 35px, 0)
                    scale(1.12);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Scroll progress
        |--------------------------------------------------------------------------
        */

        .studio-progress {
            position:
                fixed;

            z-index:
                1200;

            left:
                0;

            top:
                0;

            width:
                100%;

            height:
                2px;

            pointer-events:
                none;

            background:
                transparent;
        }

        .studio-progress-bar {
            width:
                100%;

            height:
                100%;

            border-radius:
                999px;

            background:
                linear-gradient(
                    90deg,
                    var(--studio-gold-deep),
                    var(--studio-gold-light),
                    #d7d3ff
                );

            box-shadow:
                0 0 18px rgba(122, 108, 255, .28);

            transform:
                scaleX(0);

            transform-origin:
                left center;

            will-change:
                transform;
        }

        /*
        |--------------------------------------------------------------------------
        | Shared shell
        |--------------------------------------------------------------------------
        */

        .studio-shell {
            width:
                min(
                    calc(100% - 44px),
                    var(--studio-shell)
                );

            margin-inline:
                auto;
        }

        /*
        |--------------------------------------------------------------------------
        | Header
        |--------------------------------------------------------------------------
        */

        .studio-header {
            position:
                sticky;

            z-index:
                1000;

            top:
                0;

            width:
                100%;

            border-bottom:
                1px solid rgba(255, 255, 255, .055);

            background:
                rgba(7, 8, 11, .72);

            backdrop-filter:
                blur(14px)
                saturate(120%);

            -webkit-backdrop-filter:
                blur(14px)
                saturate(120%);

            transition:
                background .24s ease,
                border-color .24s ease,
                box-shadow .24s ease;
        }

        .studio-header.is-scrolled {
            border-color:
                rgba(122, 108, 255, .11);

            background:
                rgba(7, 8, 11, .92);

            box-shadow:
                0 18px 50px rgba(0, 0, 0, .26);
        }

        .studio-nav {
            min-height:
                var(--studio-header-height);

            display:
                grid;

            grid-template-columns:
                auto
                minmax(0, 1fr)
                auto;

            align-items:
                center;

            gap:
                28px;
        }

        /*
        |--------------------------------------------------------------------------
        | Brand
        |--------------------------------------------------------------------------
        */

        .studio-brand {
            min-width:
                max-content;

            display:
                inline-flex;

            align-items:
                center;

            gap:
                12px;

            color:
                var(--studio-text);

            text-decoration:
                none;
        }

        .studio-brand-mark {
            position:
                relative;

            width:
                43px;

            height:
                43px;

            flex:
                0 0 43px;

            display:
                grid;

            place-items:
                center;

            overflow:
                hidden;

            border:
                1px solid rgba(122, 108, 255, .19);

            border-radius:
                14px;

            color:
                var(--studio-gold-light);

            background:
                linear-gradient(
                    145deg,
                    rgba(122, 108, 255, .13),
                    rgba(122, 108, 255, .025)
                );

            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, .055),
                0 14px 34px rgba(0, 0, 0, .19);

            font-size:
                15px;

            font-weight:
                950;

            letter-spacing:
                -.06em;
        }

        .studio-brand-mark::before {
            content:
                "";

            position:
                absolute;

            width:
                28px;

            height:
                28px;

            left:
                -15px;

            top:
                -15px;

            border-radius:
                50%;

            background:
                rgba(255, 255, 255, .12);

            filter:
                blur(6px);
        }

        .studio-brand-copy {
            min-width:
                0;

            display:
                flex;

            flex-direction:
                column;

            line-height:
                1;
        }

        .studio-brand-copy strong {
            color:
                #f7f7f4;

            font-size:
                14px;

            font-weight:
                950;

            letter-spacing:
                -.03em;
        }

        .studio-brand-copy small {
            margin-top:
                6px;

            color:
                #666d77;

            font-size:
                7px;

            font-weight:
                900;

            letter-spacing:
                .19em;

            text-transform:
                uppercase;
        }

        /*
        |--------------------------------------------------------------------------
        | Desktop navigation
        |--------------------------------------------------------------------------
        */

        .studio-nav-center {
            min-width:
                0;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            gap:
                4px;
        }

        .studio-nav-link {
            position:
                relative;

            min-height:
                42px;

            padding:
                0 13px;

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            gap:
                8px;

            border:
                1px solid transparent;

            border-radius:
                12px;

            color:
                #858c96;

            background:
                transparent;

            text-decoration:
                none;

            font-size:
                10px;

            font-weight:
                850;

            letter-spacing:
                .005em;

            transition:
                color .2s ease,
                background .2s ease,
                border-color .2s ease,
                transform .2s ease;
        }

        .studio-nav-link:hover {
            color:
                #e5e7ea;

            background:
                rgba(255, 255, 255, .025);
        }

        .studio-nav-link.active {
            border-color:
                rgba(122, 108, 255, .11);

            color:
                #e3bd81;

            background:
                rgba(122, 108, 255, .045);
        }

        .studio-nav-link.ai-link {
            border-color:
                rgba(132, 116, 255, .10);

            color:
                #aaa2ff;

            background:
                linear-gradient(
                    135deg,
                    rgba(132, 116, 255, .045),
                    rgba(122, 108, 255, .025)
                );
        }

        .studio-nav-link.ai-link:hover {
            border-color:
                rgba(132, 116, 255, .20);

            color:
                #d4cfff;

            background:
                linear-gradient(
                    135deg,
                    rgba(132, 116, 255, .085),
                    rgba(122, 108, 255, .04)
                );
        }

        .studio-nav-link.ai-link.active {
            border-color:
                rgba(132, 116, 255, .22);

            color:
                #dedaff;

            background:
                linear-gradient(
                    135deg,
                    rgba(132, 116, 255, .11),
                    rgba(122, 108, 255, .045)
                );

            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, .025),
                0 10px 28px rgba(64, 52, 160, .10);
        }

        .studio-ai-badge {
            min-width:
                24px;

            height:
                20px;

            padding:
                0 6px;

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            border:
                1px solid rgba(132, 116, 255, .20);

            border-radius:
                999px;

            color:
                #c9c4ff;

            background:
                rgba(132, 116, 255, .08);

            font-size:
                7px;

            font-weight:
                950;

            letter-spacing:
                .06em;
        }

        .studio-nav-link.active::after {
            content:
                "";

            position:
                absolute;

            left:
                14px;

            right:
                14px;

            bottom:
                4px;

            height:
                1px;

            border-radius:
                999px;

            background:
                linear-gradient(
                    90deg,
                    transparent,
                    var(--studio-gold),
                    transparent
                );
        }

        .studio-nav-icon {
            width:
                20px;

            height:
                20px;

            display:
                grid;

            place-items:
                center;

            color:
                currentColor;

            font-size:
                9px;

            opacity:
                .82;
        }

        .studio-nav-count {
            min-width:
                20px;

            height:
                20px;

            padding:
                0 6px;

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            border:
                1px solid rgba(122, 108, 255, .14);

            border-radius:
                999px;

            color:
                #9f95ff;

            background:
                rgba(122, 108, 255, .055);

            font-size:
                7px;

            font-weight:
                950;
        }

        /*
        |--------------------------------------------------------------------------
        | Nav actions
        |--------------------------------------------------------------------------
        */

        .studio-nav-actions {
            display:
                flex;

            align-items:
                center;

            justify-content:
                flex-end;

            gap:
                8px;
        }

        .studio-action-link,
        .studio-action-button {
            min-height:
                41px;

            padding:
                0 14px;

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            gap:
                8px;

            border:
                1px solid rgba(255, 255, 255, .075);

            border-radius:
                11px;

            color:
                #b8bdc5;

            background:
                rgba(255, 255, 255, .018);

            text-decoration:
                none;

            font-size:
                9px;

            font-weight:
                900;

            cursor:
                pointer;

            transition:
                transform .2s ease,
                color .2s ease,
                border-color .2s ease,
                background .2s ease,
                box-shadow .2s ease;
        }

        .studio-action-link:hover,
        .studio-action-button:hover {
            transform:
                translateY(-1px);

            border-color:
                rgba(122, 108, 255, .16);

            color:
                #eceef0;

            background:
                rgba(122, 108, 255, .035);
        }

        .studio-action-link.primary,
        .studio-action-button.primary {
            border-color:
                rgba(122, 108, 255, .18);

            color:
                #171009;

            background:
                linear-gradient(
                    135deg,
                    var(--studio-gold-light),
                    #4d88ff
                );

            box-shadow:
                0 14px 35px rgba(122, 108, 255, .13);
        }

        .studio-action-link.primary:hover,
        .studio-action-button.primary:hover {
            color:
                #171009;

            background:
                linear-gradient(
                    135deg,
                    #b5adff,
                    #5d91ff
                );

            box-shadow:
                0 18px 45px rgba(122, 108, 255, .20);
        }

        /*
        |--------------------------------------------------------------------------
        | Account trigger
        |--------------------------------------------------------------------------
        */

        .studio-account-wrap {
            position:
                relative;
        }

        .studio-account-trigger {
            min-height:
                43px;

            max-width:
                220px;

            padding:
                5px 9px 5px 5px;

            display:
                flex;

            align-items:
                center;

            gap:
                9px;

            border:
                1px solid rgba(255, 255, 255, .075);

            border-radius:
                999px;

            color:
                #d3d6db;

            background:
                rgba(255, 255, 255, .018);

            cursor:
                pointer;

            transition:
                border-color .2s ease,
                background .2s ease,
                box-shadow .2s ease;
        }

        .studio-account-trigger:hover,
        .studio-account-trigger[aria-expanded="true"] {
            border-color:
                rgba(122, 108, 255, .18);

            background:
                rgba(122, 108, 255, .035);

            box-shadow:
                0 12px 34px rgba(0, 0, 0, .18);
        }

        .studio-account-avatar {
            width:
                31px;

            height:
                31px;

            flex:
                0 0 31px;

            display:
                grid;

            place-items:
                center;

            overflow:
                hidden;

            border:
                1px solid rgba(122, 108, 255, .15);

            border-radius:
                50%;

            color:
                #a99fff;

            background:
                linear-gradient(
                    145deg,
                    rgba(122, 108, 255, .14),
                    rgba(122, 108, 255, .035)
                );

            font-size:
                9px;

            font-weight:
                950;
        }

        .studio-account-avatar img {
            width:
                100%;

            height:
                100%;

            object-fit:
                cover;

            border-radius:
                inherit;
        }

        .studio-account-trigger-copy {
            min-width:
                0;

            flex:
                1;
        }

        .studio-account-trigger-copy strong {
            display:
                block;

            overflow:
                hidden;

            color:
                #dfe2e5;

            font-size:
                9px;

            font-weight:
                900;

            text-overflow:
                ellipsis;

            white-space:
                nowrap;
        }

        .studio-account-trigger-copy small {
            display:
                block;

            margin-top:
                2px;

            color:
                #5f6670;

            font-size:
                7px;

            font-weight:
                800;

            letter-spacing:
                .06em;

            text-transform:
                uppercase;
        }

        .studio-account-chevron {
            width:
                19px;

            height:
                19px;

            flex:
                0 0 19px;

            display:
                grid;

            place-items:
                center;

            color:
                #6b727c;

            font-size:
                9px;

            transition:
                transform .2s ease;
        }

        .studio-account-trigger[aria-expanded="true"]
        .studio-account-chevron {
            transform:
                rotate(180deg);
        }

        /*
        |--------------------------------------------------------------------------
        | Account dropdown
        |--------------------------------------------------------------------------
        */

        .studio-account-menu {
            position:
                absolute;

            z-index:
                1050;

            right:
                0;

            top:
                calc(100% + 12px);

            width:
                min(330px, calc(100vw - 32px));

            overflow:
                hidden;

            border:
                1px solid rgba(255, 255, 255, .085);

            border-radius:
                19px;

            background:
                rgba(11, 13, 18, .96);

            box-shadow:
                0 34px 100px rgba(0, 0, 0, .43);

            backdrop-filter:
                blur(24px)
                saturate(130%);

            -webkit-backdrop-filter:
                blur(24px)
                saturate(130%);

            opacity:
                0;

            visibility:
                hidden;

            transform:
                translateY(-8px)
                scale(.98);

            transform-origin:
                top right;

            pointer-events:
                none;

            transition:
                opacity .18s ease,
                visibility .18s ease,
                transform .18s ease;
        }

        .studio-account-menu.is-open {
            opacity:
                1;

            visibility:
                visible;

            transform:
                translateY(0)
                scale(1);

            pointer-events:
                auto;
        }

        .studio-account-menu-head {
            padding:
                19px;

            display:
                flex;

            align-items:
                center;

            gap:
                13px;

            border-bottom:
                1px solid rgba(255, 255, 255, .055);

            background:
                linear-gradient(
                    145deg,
                    rgba(122, 108, 255, .045),
                    transparent
                );
        }

        .studio-account-menu-avatar {
            width:
                45px;

            height:
                45px;

            flex:
                0 0 45px;

            display:
                grid;

            place-items:
                center;

            overflow:
                hidden;

            border:
                1px solid rgba(122, 108, 255, .15);

            border-radius:
                15px;

            color:
                #a99fff;

            background:
                rgba(122, 108, 255, .06);

            font-size:
                13px;

            font-weight:
                950;
        }

        .studio-account-menu-avatar img {
            width:
                100%;

            height:
                100%;

            object-fit:
                cover;
        }

        .studio-account-menu-user {
            min-width:
                0;
        }

        .studio-account-menu-user strong {
            display:
                block;

            overflow:
                hidden;

            color:
                #edeef0;

            font-size:
                11px;

            text-overflow:
                ellipsis;

            white-space:
                nowrap;
        }

        .studio-account-menu-user span {
            display:
                block;

            margin-top:
                3px;

            overflow:
                hidden;

            color:
                #68707a;

            font-size:
                8px;

            text-overflow:
                ellipsis;

            white-space:
                nowrap;
        }

        .studio-account-menu-body {
            padding:
                9px;
        }

        .studio-account-menu-link {
            min-height:
                44px;

            padding:
                0 11px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            gap:
                14px;

            border:
                1px solid transparent;

            border-radius:
                11px;

            color:
                #959ca5;

            text-decoration:
                none;

            font-size:
                9px;

            font-weight:
                850;

            transition:
                color .2s ease,
                border-color .2s ease,
                background .2s ease;
        }

        .studio-account-menu-link:hover {
            border-color:
                rgba(122, 108, 255, .09);

            color:
                #d6d9dd;

            background:
                rgba(122, 108, 255, .035);
        }

        .studio-account-menu-link span:last-child {
            color:
                #505761;

            font-size:
                8px;
        }

        .studio-account-menu-divider {
            height:
                1px;

            margin:
                8px 3px;

            background:
                rgba(255, 255, 255, .055);
        }

        .studio-account-menu-form {
            margin:
                0;
        }

        .studio-account-menu-logout {
            width:
                100%;

            min-height:
                44px;

            padding:
                0 11px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            gap:
                14px;

            border:
                1px solid transparent;

            border-radius:
                11px;

            color:
                #c98282;

            background:
                transparent;

            font-size:
                9px;

            font-weight:
                900;

            cursor:
                pointer;
        }

        .studio-account-menu-logout:hover {
            border-color:
                rgba(244, 125, 125, .10);

            background:
                rgba(244, 125, 125, .035);
        }

        /*
        |--------------------------------------------------------------------------
        | Mobile toggle — cinematic hamburger
        |--------------------------------------------------------------------------
        */

        .studio-menu-toggle {
            --menu-size: 46px;
            --menu-line: #f3f1ff;
            --menu-accent: #8f82ff;
            --menu-accent-2: #42a5ff;

            position: relative;
            isolation: isolate;
            width: var(--menu-size);
            height: var(--menu-size);
            display: none;
            place-items: center;
            overflow: hidden;
            padding: 0;
            border: 1px solid rgba(255, 255, 255, .09);
            border-radius: 14px;
            color: var(--menu-line);
            background:
                linear-gradient(145deg, rgba(255,255,255,.055), rgba(255,255,255,.012));
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,.075),
                0 12px 32px rgba(0,0,0,.22);
            cursor: pointer;
            transform: translateZ(0);
            transition:
                border-color .35s ease,
                background .35s ease,
                box-shadow .35s ease,
                transform .45s cubic-bezier(.16,1,.3,1);
        }

        .studio-menu-toggle::before,
        .studio-menu-toggle::after {
            content: "";
            position: absolute;
            pointer-events: none;
        }

        .studio-menu-toggle::before {
            z-index: -2;
            width: 68px;
            height: 68px;
            border-radius: 50%;
            background:
                conic-gradient(
                    from 0deg,
                    transparent 0 18%,
                    rgba(122,108,255,.92) 25%,
                    transparent 34% 54%,
                    rgba(143,124,255,.82) 63%,
                    transparent 72% 100%
                );
            opacity: 0;
            transform: scale(.55) rotate(-140deg);
            transition:
                opacity .3s ease,
                transform .65s cubic-bezier(.16,1,.3,1);
        }

        .studio-menu-toggle::after {
            z-index: -1;
            inset: 3px;
            border-radius: 11px;
            background: rgba(8,10,14,.96);
        }

        .studio-menu-toggle:hover {
            border-color: rgba(122,108,255,.28);
            background:
                linear-gradient(145deg, rgba(122,108,255,.095), rgba(143,124,255,.035));
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,.10),
                0 16px 38px rgba(0,0,0,.30),
                0 0 0 1px rgba(122,108,255,.035);
            transform: translateY(-1px) scale(1.025);
        }

        .studio-menu-toggle:active {
            transform: translateY(0) scale(.94) rotate(-2deg);
        }

        .studio-menu-orbit {
            position: absolute;
            z-index: 1;
            inset: 6px;
            border: 1px solid rgba(122,108,255,.16);
            border-radius: 50%;
            opacity: .30;
            transform: scale(.72) rotate(0deg);
            transition:
                opacity .35s ease,
                border-color .35s ease,
                transform .65s cubic-bezier(.16,1,.3,1);
        }

        .studio-menu-orbit::before,
        .studio-menu-orbit::after {
            content: "";
            position: absolute;
            width: 4px;
            height: 4px;
            border-radius: 50%;
        }

        .studio-menu-orbit::before {
            left: 1px;
            top: 7px;
            background: var(--menu-accent);
            box-shadow: 0 0 10px rgba(122,108,255,.86);
        }

        .studio-menu-orbit::after {
            right: 3px;
            bottom: 5px;
            background: var(--menu-accent-2);
            box-shadow: 0 0 10px rgba(143,124,255,.82);
        }

        .studio-menu-lines {
            position: relative;
            z-index: 3;
            width: 22px;
            height: 18px;
            display: block;
            transform: rotate(0deg) scale(1);
            transition: transform .65s cubic-bezier(.16,1,.3,1);
        }

        .studio-menu-line {
            position: absolute;
            left: 50%;
            height: 2px;
            display: block;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--menu-line), #bdb7ff);
            transform-origin: center;
            transition:
                width .45s cubic-bezier(.16,1,.3,1),
                top .45s cubic-bezier(.16,1,.3,1),
                opacity .25s ease,
                background .35s ease,
                box-shadow .35s ease,
                transform .6s cubic-bezier(.16,1,.3,1);
        }

        .studio-menu-line:nth-child(1) {
            width: 20px;
            top: 1px;
            transform: translateX(-50%);
        }

        .studio-menu-line:nth-child(2) {
            width: 14px;
            top: 8px;
            transform: translateX(-34%);
        }

        .studio-menu-line:nth-child(3) {
            width: 18px;
            top: 15px;
            transform: translateX(-58%);
        }

        .studio-menu-toggle:hover .studio-menu-line:nth-child(1) {
            transform: translateX(-46%);
        }

        .studio-menu-toggle:hover .studio-menu-line:nth-child(2) {
            width: 19px;
            transform: translateX(-51%);
        }

        .studio-menu-toggle:hover .studio-menu-line:nth-child(3) {
            transform: translateX(-54%);
        }

        .studio-menu-toggle[aria-expanded="true"] {
            border-color: rgba(122,108,255,.34);
            background:
                linear-gradient(145deg, rgba(122,108,255,.12), rgba(143,124,255,.07));
            box-shadow:
                inset 0 1px 0 rgba(255,255,255,.11),
                0 18px 50px rgba(0,0,0,.34),
                0 0 26px rgba(122,108,255,.10);
            transform: rotate(180deg) scale(1.04);
        }

        .studio-menu-toggle[aria-expanded="true"]::before {
            opacity: .95;
            transform: scale(1) rotate(220deg);
            animation: studioMenuAura 2.6s linear infinite;
        }

        .studio-menu-toggle[aria-expanded="true"] .studio-menu-orbit {
            border-color: rgba(122,108,255,.30);
            opacity: 1;
            transform: scale(1) rotate(360deg);
            animation: studioMenuOrbit 2.2s linear infinite;
        }

        .studio-menu-toggle[aria-expanded="true"] .studio-menu-lines {
            transform: rotate(-180deg) scale(.94);
        }

        .studio-menu-toggle[aria-expanded="true"] .studio-menu-line {
            top: 8px;
            width: 22px;
            background: linear-gradient(90deg, #d7d3ff, #ffffff);
            box-shadow: 0 0 13px rgba(122,108,255,.24);
        }

        .studio-menu-toggle[aria-expanded="true"] .studio-menu-line:nth-child(1) {
            transform: translateX(-50%) rotate(45deg);
        }

        .studio-menu-toggle[aria-expanded="true"] .studio-menu-line:nth-child(2) {
            opacity: 0;
            transform: translateX(-50%) scaleX(.05) rotate(180deg);
        }

        .studio-menu-toggle[aria-expanded="true"] .studio-menu-line:nth-child(3) {
            transform: translateX(-50%) rotate(-45deg);
        }

        .studio-menu-toggle[aria-expanded="true"]:hover {
            transform: rotate(180deg) scale(1.09);
        }

        @keyframes studioMenuAura {
            0% { transform: scale(.95) rotate(0deg); }
            50% { transform: scale(1.08) rotate(180deg); }
            100% { transform: scale(.95) rotate(360deg); }
        }

        @keyframes studioMenuOrbit {
            to { transform: scale(1) rotate(720deg); }
        }

        /*
        |--------------------------------------------------------------------------
        | Mobile drawer
        |--------------------------------------------------------------------------
        */

        .studio-mobile-overlay {
            position:
                fixed;

            z-index:
                1090;

            inset:
                0;

            display:
                none;

            background:
                rgba(0, 0, 0, .56);

            backdrop-filter:
                blur(5px);

            -webkit-backdrop-filter:
                blur(5px);

            opacity:
                0;

            visibility:
                hidden;

            pointer-events:
                none;

            touch-action:
                none;

            transition:
                opacity .2s ease,
                visibility .2s ease;
        }

        .studio-mobile-overlay.is-open {
            opacity:
                1;

            visibility:
                visible;

            pointer-events:
                auto;
        }

        .studio-mobile-drawer {
            position:
                fixed;

            z-index:
                1100;

            right:
                0;

            top:
                0;

            bottom:
                0;

            width:
                min(390px, 92vw);

            padding:
                calc(18px + env(safe-area-inset-top))
                calc(18px + env(safe-area-inset-right))
                calc(18px + env(safe-area-inset-bottom))
                18px;

            display:
                none;

            flex-direction:
                column;

            overflow-y:
                auto;

            border-left:
                1px solid rgba(255, 255, 255, .08);

            background:
                rgba(8, 10, 14, .97);

            box-shadow:
                -30px 0 90px rgba(0, 0, 0, .42);

            backdrop-filter:
                blur(24px);

            -webkit-backdrop-filter:
                blur(24px);

            transform:
                translate3d(105%, 0, 0);

            visibility:
                hidden;

            pointer-events:
                none;

            overscroll-behavior:
                contain;

            -webkit-overflow-scrolling:
                touch;

            touch-action:
                pan-y;

            opacity:
                .18;

            transform-origin:
                right center;

            transition:
                transform .72s cubic-bezier(.16, 1, .3, 1),
                opacity .38s ease,
                visibility .72s ease;
        }

        .studio-mobile-drawer.is-open {
            opacity:
                1;

            transform:
                translate3d(0, 0, 0);

            visibility:
                visible;

            pointer-events:
                auto;
        }

        .studio-mobile-drawer .studio-mobile-head,
        .studio-mobile-drawer .studio-mobile-user,
        .studio-mobile-drawer .studio-mobile-nav > *,
        .studio-mobile-drawer .studio-mobile-actions > *,
        .studio-mobile-drawer .expert-mobile-footer > * {
            opacity:
                0;

            transform:
                translate3d(22px, 8px, 0)
                scale(.985);

            filter:
                blur(5px);

            transition:
                opacity .42s ease,
                transform .62s cubic-bezier(.16, 1, .3, 1),
                filter .42s ease;
        }

        .studio-mobile-drawer.is-open .studio-mobile-head,
        .studio-mobile-drawer.is-open .studio-mobile-user,
        .studio-mobile-drawer.is-open .studio-mobile-nav > *,
        .studio-mobile-drawer.is-open .studio-mobile-actions > *,
        .studio-mobile-drawer.is-open .expert-mobile-footer > * {
            opacity:
                1;

            transform:
                translate3d(0, 0, 0)
                scale(1);

            filter:
                blur(0);
        }

        .studio-mobile-drawer.is-open .studio-mobile-head { transition-delay: .12s; }
        .studio-mobile-drawer.is-open .studio-mobile-user { transition-delay: .18s; }
        .studio-mobile-drawer.is-open .studio-mobile-nav > :nth-child(1) { transition-delay: .22s; }
        .studio-mobile-drawer.is-open .studio-mobile-nav > :nth-child(2) { transition-delay: .26s; }
        .studio-mobile-drawer.is-open .studio-mobile-nav > :nth-child(3) { transition-delay: .30s; }
        .studio-mobile-drawer.is-open .studio-mobile-nav > :nth-child(4) { transition-delay: .34s; }
        .studio-mobile-drawer.is-open .studio-mobile-nav > :nth-child(5) { transition-delay: .38s; }
        .studio-mobile-drawer.is-open .studio-mobile-nav > :nth-child(n+6) { transition-delay: .42s; }
        .studio-mobile-drawer.is-open .studio-mobile-actions > *,
        .studio-mobile-drawer.is-open .expert-mobile-footer > * { transition-delay: .46s; }

        .studio-mobile-head {
            min-height:
                50px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            gap:
                20px;

            padding-bottom:
                16px;

            border-bottom:
                1px solid rgba(255, 255, 255, .06);
        }

        .studio-mobile-head strong {
            color:
                #e8eaec;

            font-size:
                11px;
        }

        .studio-mobile-close {
            width:
                38px;

            height:
                38px;

            display:
                grid;

            place-items:
                center;

            border:
                1px solid rgba(255, 255, 255, .07);

            border-radius:
                11px;

            color:
                #a0a6ae;

            background:
                rgba(255, 255, 255, .02);

            font-size:
                16px;

            cursor:
                pointer;
        }

        .studio-mobile-user {
            margin-top:
                16px;

            padding:
                16px;

            display:
                flex;

            align-items:
                center;

            gap:
                12px;

            border:
                1px solid rgba(122, 108, 255, .09);

            border-radius:
                15px;

            background:
                rgba(122, 108, 255, .025);
        }

        .studio-mobile-user .studio-account-menu-avatar {
            width:
                42px;

            height:
                42px;

            flex-basis:
                42px;
        }

        .studio-mobile-user strong,
        .studio-mobile-user span {
            display:
                block;
        }

        .studio-mobile-user strong {
            color:
                #dfe2e5;

            font-size:
                10px;
        }

        .studio-mobile-user span {
            margin-top:
                3px;

            color:
                #666d77;

            font-size:
                8px;
        }

        .studio-mobile-nav {
            margin-top:
                18px;

            display:
                grid;

            gap:
                6px;
        }

        .studio-mobile-nav-label {
            margin:
                14px 7px 6px;

            color:
                #4f5660;

            font-size:
                7px;

            font-weight:
                950;

            letter-spacing:
                .16em;

            text-transform:
                uppercase;
        }

        .studio-mobile-link {
            min-height:
                48px;

            padding:
                0 13px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            gap:
                15px;

            border:
                1px solid transparent;

            border-radius:
                12px;

            color:
                #8f969f;

            text-decoration:
                none;

            font-size:
                10px;

            font-weight:
                850;
        }

        .studio-mobile-link:hover,
        .studio-mobile-link.active {
            border-color:
                rgba(122, 108, 255, .10);

            color:
                #a99fff;

            background:
                rgba(122, 108, 255, .035);
        }

        .studio-mobile-link span:last-child {
            color:
                #4f5660;

            font-size:
                9px;
        }

        .studio-mobile-actions {
            margin-top:
                auto;

            padding-top:
                24px;

            display:
                grid;

            gap:
                8px;
        }

        .studio-mobile-actions .studio-action-link,
        .studio-mobile-actions .studio-action-button {
            width:
                100%;

            min-height:
                48px;
        }

        /*
        |--------------------------------------------------------------------------
        | Main
        |--------------------------------------------------------------------------
        */

        .studio-main {
            position:
                relative;

            z-index:
                1;

            flex:
                1;

            width:
                100%;

            min-height:
                calc(100dvh - var(--studio-header-height));
        }

        /*
        |--------------------------------------------------------------------------
        | Flash messages
        |--------------------------------------------------------------------------
        */

        .studio-flash-stack {
            position:
                relative;

            z-index:
                50;

            width:
                min(
                    calc(100% - 32px),
                    1180px
                );

            margin:
                17px auto -2px;

            display:
                grid;

            gap:
                9px;
        }

        .studio-flash {
            position:
                relative;

            padding:
                14px 42px 14px 16px;

            display:
                flex;

            align-items:
                flex-start;

            gap:
                12px;

            overflow:
                hidden;

            border:
                1px solid rgba(255, 255, 255, .075);

            border-radius:
                14px;

            color:
                #a7adb5;

            background:
                rgba(13, 16, 21, .88);

            box-shadow:
                0 15px 40px rgba(0, 0, 0, .20);

            backdrop-filter:
                blur(16px);

            -webkit-backdrop-filter:
                blur(16px);

            font-size:
                10px;

            line-height:
                1.65;
        }

        .studio-flash::before {
            content:
                "";

            width:
                7px;

            height:
                7px;

            flex:
                0 0 7px;

            margin-top:
                5px;

            border-radius:
                50%;

            background:
                #848b95;

            box-shadow:
                0 0 0 5px rgba(132, 139, 149, .06);
        }

        .studio-flash.success {
            border-color:
                rgba(103, 217, 144, .14);

            color:
                #acdfbb;

            background:
                rgba(103, 217, 144, .045);
        }

        .studio-flash.success::before {
            background:
                var(--studio-success);

            box-shadow:
                0 0 0 5px rgba(103, 217, 144, .06);
        }

        .studio-flash.warning {
            border-color:
                rgba(240, 196, 109, .15);

            color:
                #d7bd8b;

            background:
                rgba(240, 196, 109, .045);
        }

        .studio-flash.warning::before {
            background:
                var(--studio-warning);

            box-shadow:
                0 0 0 5px rgba(240, 196, 109, .06);
        }

        .studio-flash.error {
            border-color:
                rgba(244, 125, 125, .15);

            color:
                #e1a4a4;

            background:
                rgba(244, 125, 125, .045);
        }

        .studio-flash.error::before {
            background:
                var(--studio-danger);

            box-shadow:
                0 0 0 5px rgba(244, 125, 125, .06);
        }

        .studio-flash-content {
            min-width:
                0;

            flex:
                1;
        }

        .studio-flash-content strong {
            display:
                block;

            margin-bottom:
                2px;

            color:
                inherit;

            font-size:
                10px;
        }

        .studio-flash-list {
            margin:
                6px 0 0 16px;

            padding:
                0;

            display:
                grid;

            gap:
                3px;
        }

        .studio-flash-close {
            position:
                absolute;

            right:
                10px;

            top:
                10px;

            width:
                28px;

            height:
                28px;

            display:
                grid;

            place-items:
                center;

            border:
                1px solid rgba(255, 255, 255, .06);

            border-radius:
                8px;

            color:
                #646b75;

            background:
                rgba(255, 255, 255, .015);

            font-size:
                12px;

            cursor:
                pointer;
        }
/*
        |--------------------------------------------------------------------------
        | Generic content helpers
        |--------------------------------------------------------------------------
        */

        .studio-page-head {
            padding:
                72px 0 38px;
        }

        .studio-page-kicker {
            display:
                inline-flex;

            align-items:
                center;

            gap:
                10px;

            color:
                var(--studio-gold);

            font-size:
                9px;

            font-weight:
                950;

            letter-spacing:
                .18em;

            text-transform:
                uppercase;
        }

        .studio-page-kicker::before {
            content:
                "";

            width:
                30px;

            height:
                1px;

            background:
                linear-gradient(
                    90deg,
                    var(--studio-gold),
                    transparent
                );
        }

        .studio-page-title {
            max-width:
                840px;

            margin:
                14px 0 0;

            color:
                #f7f7f4;

            font-size:
                clamp(44px, 5vw, 74px);

            font-weight:
                950;

            line-height:
                .98;

            letter-spacing:
                -.06em;
        }

        .studio-page-description {
            max-width:
                680px;

            margin:
                18px 0 0;

            color:
                #858c96;

            font-size:
                13px;

            line-height:
                1.85;
        }

        .studio-panel {
            border:
                1px solid var(--studio-line);

            border-radius:
                var(--studio-radius-lg);

            background:
                linear-gradient(
                    145deg,
                    rgba(255, 255, 255, .035),
                    rgba(255, 255, 255, .008)
                ),
                var(--studio-surface);

            box-shadow:
                var(--studio-shadow-soft);
        }

        /*
        |--------------------------------------------------------------------------
        | Footer
        |--------------------------------------------------------------------------
        */

        .studio-footer {
            position:
                relative;

            z-index:
                1;

            margin-top:
                auto;

            overflow:
                hidden;

            border-top:
                1px solid rgba(122, 108, 255, .16);

            background:
                radial-gradient(
                    circle at 14% 0%,
                    rgba(122, 108, 255, .10),
                    transparent 28rem
                ),
                radial-gradient(
                    circle at 86% 100%,
                    rgba(66, 165, 255, .07),
                    transparent 30rem
                ),
                linear-gradient(
                    180deg,
                    rgba(8, 10, 16, .98),
                    #050609
                );
        }

        .studio-footer::before {
            content:
                "";

            position:
                absolute;

            inset:
                0;

            pointer-events:
                none;

            opacity:
                .22;

            background-image:
                linear-gradient(
                    rgba(255,255,255,.025) 1px,
                    transparent 1px
                ),
                linear-gradient(
                    90deg,
                    rgba(255,255,255,.025) 1px,
                    transparent 1px
                );

            background-size:
                64px 64px;

            mask-image:
                linear-gradient(
                    to bottom,
                    #000,
                    transparent 92%
                );
        }

        .studio-footer-main,
        .studio-footer-bottom {
            position:
                relative;

            z-index:
                1;
        }

        .studio-footer-main {
            padding:
                64px 0 42px;

            display:
                grid;

            grid-template-columns:
                minmax(260px, 1.35fr)
                repeat(4, minmax(120px, .62fr));

            gap:
                36px;
        }

        .studio-footer-about {
            max-width:
                480px;
        }

        .studio-footer-logo {
            display:
                inline-flex;

            align-items:
                center;

            gap:
                11px;

            color:
                #f0f2f7;

            text-decoration:
                none;
        }

        .studio-footer-logo .studio-brand-mark {
            width:
                39px;

            height:
                39px;

            flex-basis:
                39px;

            border-color:
                rgba(122, 108, 255, .28);

            color:
                #c7c1ff;

            background:
                linear-gradient(
                    145deg,
                    rgba(122,108,255,.16),
                    rgba(66,165,255,.045)
                );

            box-shadow:
                inset 0 1px 0 rgba(255,255,255,.07),
                0 14px 34px rgba(42, 37, 120, .18),
                0 0 24px rgba(122,108,255,.08);
        }

        .studio-footer-about p {
            margin:
                18px 0 0;

            color:
                #747d8b;

            font-size:
                10px;

            line-height:
                1.8;
        }

        .studio-footer-trust {
            margin-top:
                18px;

            display:
                flex;

            align-items:
                center;

            gap:
                9px;

            color:
                #788191;

            font-size:
                8px;

            font-weight:
                850;
        }

        .studio-footer-trust::before {
            content:
                "";

            width:
                6px;

            height:
                6px;

            border-radius:
                50%;

            background:
                #8df0d0;

            box-shadow:
                0 0 0 5px rgba(141, 240, 208, .05),
                0 0 14px rgba(141, 240, 208, .18);
        }

        .studio-footer-column h3 {
            margin:
                0 0 15px;

            color:
                #bdb8ff;

            font-size:
                9px;

            font-weight:
                950;

            letter-spacing:
                .12em;

            text-transform:
                uppercase;
        }

        .studio-footer-links {
            display:
                grid;

            gap:
                9px;
        }

        .studio-footer-links a {
            width:
                fit-content;

            color:
                #717a88;

            text-decoration:
                none;

            font-size:
                9px;

            font-weight:
                800;

            transition:
                color .2s ease,
                transform .2s ease,
                text-shadow .2s ease;
        }

        .studio-footer-links a:hover {
            color:
                #9f96ff;

            transform:
                translateX(2px);

            text-shadow:
                0 0 18px rgba(122,108,255,.24);
        }

        .studio-footer-bottom {
            min-height:
                68px;

            padding:
                17px 0;

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            gap:
                20px;

            border-top:
                1px solid rgba(122, 108, 255, .10);

            color:
                #596273;

            font-size:
                8px;

            font-weight:
                800;

            letter-spacing:
                .05em;
        }

        .studio-footer-bottom-links {
            display:
                flex;

            align-items:
                center;

            gap:
                16px;

            flex-wrap:
                wrap;
        }

        .studio-footer-bottom-links a {
            color:
                #606a7a;

            text-decoration:
                none;

            transition:
                color .2s ease,
                text-shadow .2s ease;
        }

        .studio-footer-bottom-links a:hover {
            color:
                #8e88ff;

            text-shadow:
                0 0 16px rgba(122,108,255,.22);
        }

        /*
        |--------------------------------------------------------------------------
        | Toast / transient notices
        |--------------------------------------------------------------------------
        */

        .studio-toast-region {
            position:
                fixed;

            z-index:
                1300;

            right:
                18px;

            top:
                calc(var(--studio-header-height) + 18px);

            width:
                min(370px, calc(100vw - 36px));

            display:
                grid;

            gap:
                9px;

            pointer-events:
                none;
        }

        .studio-toast {
            padding:
                13px 15px;

            display:
                flex;

            align-items:
                flex-start;

            gap:
                10px;

            border:
                1px solid rgba(255, 255, 255, .075);

            border-radius:
                13px;

            color:
                #a0a7b0;

            background:
                rgba(12, 15, 20, .94);

            box-shadow:
                0 20px 55px rgba(0, 0, 0, .32);

            backdrop-filter:
                blur(18px);

            opacity:
                0;

            transform:
                translateY(-8px);

            pointer-events:
                auto;

            animation:
                studioToastIn .22s ease forwards;

            font-size:
                9px;

            line-height:
                1.6;
        }

        @keyframes studioToastIn {
            to {
                opacity:
                    1;

                transform:
                    translateY(0);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Shared loading state
        |--------------------------------------------------------------------------
        */

        .is-loading {
            cursor:
                wait !important;

            opacity:
                .68 !important;

            pointer-events:
                none !important;
        }

        /*
        |--------------------------------------------------------------------------
        | Reveal
        |--------------------------------------------------------------------------
        */

        .studio-reveal {
            opacity:
                0;

            transform:
                translateY(18px);

            transition:
                opacity .6s ease,
                transform .6s ease;
        }

        .studio-reveal.is-visible {
            opacity:
                1;

            transform:
                translateY(0);
        }


        .studio-menu-toggle,
        .studio-mobile-close,
        .studio-mobile-link,
        .studio-action-link,
        .studio-action-button,
        .studio-account-trigger,
        .studio-account-menu-link,
        .studio-account-menu-logout,
        .studio-flash-close,
        .Mashal Hussain {
            touch-action:
                manipulation;

            -webkit-tap-highlight-color:
                transparent;
        }

        .studio-menu-toggle,
        .studio-mobile-close,
        .studio-account-trigger,
        .studio-action-button,
        .studio-account-menu-logout,
        .studio-flash-close {
            -webkit-appearance:
                none;

            appearance:
                none;
        }

        /*
        |--------------------------------------------------------------------------
        | Responsive - large tablet
        |--------------------------------------------------------------------------
        */

        @media (max-width: 1120px) {
            .studio-nav {
                grid-template-columns:
                    auto
                    1fr
                    auto;
            }

            .studio-nav-center {
                display:
                    none;
            }

            .studio-menu-toggle {
                display:
                    grid;
            }

            .studio-mobile-overlay,
            .studio-mobile-drawer {
                display:
                    flex;
            }

            .studio-nav-actions .studio-action-link.desktop-secondary {
                display:
                    none;
            }

            .studio-footer-main {
                grid-template-columns:
                    1.2fr
                    repeat(2, 1fr);
            }

            .studio-footer-column:last-child {
                grid-column:
                    auto;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Responsive - tablet
        |--------------------------------------------------------------------------
        */

        @media (max-width: 820px) {
            :root {
                --studio-header-height:
                    70px;
            }

            .studio-shell {
                width:
                    min(
                        calc(100% - 30px),
                        var(--studio-shell)
                    );
            }

            .studio-brand-copy small {
                display:
                    none;
            }

            .studio-account-trigger-copy {
                display:
                    none;
            }

            .studio-account-trigger {
                padding-right:
                    5px;
            }

            .studio-account-chevron {
                display:
                    none;
            }

            .studio-footer-main {
                grid-template-columns:
                    1fr
                    1fr;

                gap:
                    36px;
            }

            .studio-footer-about {
                grid-column:
                    1 / -1;
            }

            .studio-footer-column:last-child {
                grid-column:
                    auto;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Responsive - mobile
        |--------------------------------------------------------------------------
        */

        @media (max-width: 620px) {

            /*
            |--------------------------------------------------------------------------
            | Mobile performance
            |--------------------------------------------------------------------------
            |
            | Grote blur-lagen en continu geanimeerde orbs zijn relatief duur op
            | mobiele GPU's. De uitstraling blijft donker/goud, maar de zwaarste
            | compositing-effecten worden op kleine schermen beperkt.
            |
            */

            .studio-orb {
                animation:
                    none;

                filter:
                    blur(44px);

                opacity:
                    .07;

                will-change:
                    auto;
            }

            .studio-orb.two,
            .studio-orb.three {
                display:
                    none;
            }

            .studio-header {
                backdrop-filter:
                    none;

                -webkit-backdrop-filter:
                    none;

                background:
                    rgba(7, 8, 11, .96);

                padding-top:
                    env(safe-area-inset-top);
            }

            .studio-menu-toggle {
                width:
                    46px;

                height:
                    46px;

                flex:
                    0 0 46px;

                position:
                    relative;

                z-index:
                    3;
            }

            .studio-mobile-close {
                width:
                    44px;

                height:
                    44px;

                flex:
                    0 0 44px;
            }

            .studio-mobile-drawer {
                width:
                    min(390px, 94vw);

                max-width:
                    100%;

                height:
                    100dvh;

                min-height:
                    100svh;
            }

            .studio-mobile-link {
                min-height:
                    52px;

                padding:
                    0 14px;

                font-size:
                    11px;
            }

            .studio-mobile-actions .studio-action-link,
            .studio-mobile-actions .studio-action-button {
                min-height:
                    52px;
            }

            .studio-shell {
                width:
                    min(
                        calc(100% - 22px),
                        var(--studio-shell)
                    );
            }

            .studio-nav {
                min-height:
                    68px;

                gap:
                    10px;
            }

            .studio-brand-mark {
                width:
                    39px;

                height:
                    39px;

                flex-basis:
                    39px;
            }

            .studio-brand-copy strong {
                font-size:
                    12px;
            }

            .studio-nav-actions {
                gap:
                    6px;
            }

            .studio-nav-actions .studio-action-link.primary {
                display:
                    none;
            }

            .studio-account-trigger {
                max-width:
                    44px;
            }

            .Mashal Hussain {
                right:
                    10px;

                bottom:
                    10px;

                min-height:
                    48px;

                padding-right:
                    14px;
            }

            .Mashal Hussain span:last-child {
                display:
                    none;
            }

            .studio-flash-stack {
                width:
                    min(
                        calc(100% - 22px),
                        1180px
                    );
            }

            .studio-footer-main {
                grid-template-columns:
                    1fr;

                padding:
                    48px 0 32px;
            }

            .studio-footer-about,
            .studio-footer-column,
            .studio-footer-column:last-child {
                grid-column:
                    auto;
            }

            .studio-footer-bottom {
                align-items:
                    flex-start;

                flex-direction:
                    column;
            }

            .studio-page-head {
                padding-top:
                    50px;
            }

            .studio-page-title {
                font-size:
                    clamp(40px, 13vw, 58px);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Reduced motion
        |--------------------------------------------------------------------------
        */

        @media (prefers-reduced-motion: reduce) {
            html {
                scroll-behavior:
                    auto;
            }

            *,
            *::before,
            *::after {
                animation-duration:
                    .01ms !important;

                animation-iteration-count:
                    1 !important;

                transition-duration:
                    .01ms !important;
            }

            .studio-orb {
                display:
                    none;
            }

            .studio-reveal {
                opacity:
                    1;

                transform:
                    none;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Print
        |--------------------------------------------------------------------------
        */

        @media print {
            .studio-header,
            .studio-footer,
            .Mashal Hussain,
            .studio-progress,
            .studio-atmosphere,
            .studio-mobile-overlay,
            .studio-mobile-drawer {
                display:
                    none !important;
            }

            body {
                color:
                    #000;

                background:
                    #fff;
            }

            .studio-main {
                min-height:
                    auto;
            }
        }
    
        /*
        |--------------------------------------------------------------------------
        | Ultra Smooth performance mode
        |--------------------------------------------------------------------------
        |
        | Bewust minder GPU-compositing: semi-transparante achtergronden blijven,
        | maar live backdrop blur, geanimeerde orbs en de scroll-progresslaag
        | worden uitgeschakeld. Dit houdt het design donker/goud en maakt scrollen
        | merkbaar lichter op mobiel én desktop.
        |
        */

        .studio-progress {
            display:
                none !important;
        }

        .studio-orb {
            display:
                none !important;
        }

        .studio-atmosphere::after {
            display:
                none !important;
        }

        .studio-header {
            background:
                rgba(7, 8, 11, .97) !important;

            backdrop-filter:
                none !important;

            -webkit-backdrop-filter:
                none !important;

            box-shadow:
                none;
        }

        .studio-header.is-scrolled {
            background:
                rgba(7, 8, 11, .985) !important;

            box-shadow:
                0 8px 24px rgba(0, 0, 0, .18);
        }

        .studio-account-menu,
        .studio-mobile-overlay,
        .studio-mobile-drawer,
        .studio-flash,
        .studio-toast {
            backdrop-filter:
                none !important;

            -webkit-backdrop-filter:
                none !important;
        }

        @media (max-width: 900px) {
            .studio-atmosphere::before {
                opacity:
                    .65;
            }

            .studio-header,
            .studio-header.is-scrolled {
                box-shadow:
                    none !important;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior:
                    auto !important;

                animation-duration:
                    .001ms !important;

                animation-iteration-count:
                    1 !important;

                transition-duration:
                    .001ms !important;
            }
        }

    
        @media (prefers-reduced-motion: reduce) {
            .studio-menu-toggle,
            .studio-menu-toggle::before,
            .studio-menu-orbit,
            .studio-menu-lines,
            .studio-menu-line,
            .studio-mobile-drawer,
            .studio-mobile-drawer .studio-mobile-head,
            .studio-mobile-drawer .studio-mobile-user,
            .studio-mobile-drawer .studio-mobile-nav > *,
            .studio-mobile-drawer .studio-mobile-actions > *,
            .studio-mobile-drawer .expert-mobile-footer > * {
                animation: none !important;
                transition-duration: .01ms !important;
                transition-delay: 0ms !important;
            }
        }

    </style>


    <style id="mashal-expert-navbar-system">
        /*
        |--------------------------------------------------------------------------
        | Expert navigation system
        |--------------------------------------------------------------------------
        */

        :root {
            --nav-bg:
                rgba(6, 7, 11, .82);

            --nav-bg-solid:
                rgba(6, 7, 11, .96);

            --nav-panel:
                #0d1017;

            --nav-panel-2:
                #121722;

            --nav-line:
                rgba(255, 255, 255, .075);

            --nav-line-strong:
                rgba(255, 255, 255, .135);

            --nav-text:
                #f6f8fb;

            --nav-muted:
                #7d8795;

            --nav-accent:
                #7a6cff;

            --nav-accent-2:
                #4b9cff;

            --nav-green:
                #82efbd;

            --nav-danger:
                #ff879b;
        }

        /*
        | Header shell
        */

        .studio-header-expert {
            position:
                sticky;

            top:
                0;

            z-index:
                1000;

            width:
                100%;

            border-bottom:
                1px solid rgba(255, 255, 255, .055);

            background:
                var(--nav-bg) !important;

            backdrop-filter:
                blur(22px)
                saturate(140%) !important;

            -webkit-backdrop-filter:
                blur(22px)
                saturate(140%) !important;

            transition:
                background .26s ease,
                border-color .26s ease,
                box-shadow .26s ease;
        }

        .studio-header-expert::before {
            content:
                "";

            position:
                absolute;

            inset:
                auto 0 -1px;

            height:
                1px;

            opacity:
                .55;

            pointer-events:
                none;

            background:
                linear-gradient(
                    90deg,
                    transparent 5%,
                    rgba(122, 108, 255, .16),
                    rgba(75, 156, 255, .14),
                    transparent 95%
                );
        }

        .studio-header-expert.is-scrolled {
            border-color:
                rgba(122, 108, 255, .12);

            background:
                var(--nav-bg-solid) !important;

            box-shadow:
                0 20px 60px rgba(0, 0, 0, .28);
        }

        .expert-nav {
            min-height:
                76px;

            display:
                grid;

            grid-template-columns:
                minmax(330px, auto)
                minmax(0, 1fr)
                auto;

            align-items:
                center;

            gap:
                24px;
        }

        /*
        | Left cluster
        */

        .expert-nav-left {
            min-width:
                0;

            display:
                flex;

            align-items:
                center;

            gap:
                14px;
        }

        .expert-brand {
            min-width:
                max-content;

            display:
                inline-flex;

            align-items:
                center;

            gap:
                11px;

            color:
                inherit;

            text-decoration:
                none;
        }

        .expert-brand-mark {
            position:
                relative;

            width:
                42px;

            height:
                42px;

            flex:
                0 0 42px;

            display:
                grid;

            place-items:
                center;

            overflow:
                hidden;

            border:
                1px solid rgba(122, 108, 255, .28);

            border-radius:
                12px;

            color:
                #ffffff;

            background:
                radial-gradient(
                    circle at 25% 20%,
                    rgba(255, 255, 255, .20),
                    transparent 28%
                ),
                linear-gradient(
                    145deg,
                    #806eff,
                    #5b66ef 50%,
                    #3e91e8
                );

            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, .22),
                0 14px 38px rgba(72, 64, 204, .24);
        }

        .expert-brand-glyph {
            font-size:
                14px;

            font-weight:
                900;

            letter-spacing:
                -.08em;
        }

        .expert-brand-status {
            position:
                absolute;

            right:
                5px;

            bottom:
                5px;

            width:
                6px;

            height:
                6px;

            border:
                1px solid rgba(6, 7, 11, .9);

            border-radius:
                50%;

            background:
                var(--nav-green);

            box-shadow:
                0 0 12px rgba(130, 239, 189, .75);
        }

        .expert-brand-copy {
            display:
                flex;

            flex-direction:
                column;

            line-height:
                1;
        }

        .expert-brand-copy strong {
            color:
                #f6f8fb;

            font-size:
                13px;

            font-weight:
                820;

            letter-spacing:
                -.04em;
        }

        .expert-brand-copy small {
            margin-top:
                5px;

            color:
                #687382;

            font-size:
                7px;

            font-weight:
                800;

            letter-spacing:
                .16em;

            text-transform:
                uppercase;
        }

        .expert-brand-divider {
            width:
                1px;

            height:
                30px;

            background:
                var(--nav-line);
        }

        /*
        | Command trigger
        */

        .expert-command-trigger {
            min-width:
                178px;

            min-height:
                43px;

            padding:
                0 9px;

            display:
                grid;

            grid-template-columns:
                28px minmax(0, 1fr) auto;

            align-items:
                center;

            gap:
                8px;

            border:
                1px solid var(--nav-line);

            border-radius:
                11px;

            color:
                #aeb6c2;

            background:
                rgba(255, 255, 255, .018);

            cursor:
                pointer;

            text-align:
                left;

            transition:
                border-color .2s ease,
                background .2s ease,
                transform .2s ease;
        }

        .expert-command-trigger:hover,
        .expert-command-trigger[aria-expanded="true"] {
            transform:
                translateY(-1px);

            border-color:
                rgba(122, 108, 255, .20);

            background:
                linear-gradient(
                    135deg,
                    rgba(122, 108, 255, .075),
                    rgba(75, 156, 255, .025)
                );
        }

        .expert-command-icon {
            width:
                28px;

            height:
                28px;

            display:
                grid;

            place-items:
                center;

            border:
                1px solid var(--nav-line);

            border-radius:
                8px;

            color:
                #9c94ff;

            background:
                rgba(255, 255, 255, .018);

            font-size:
                10px;
        }

        .expert-command-copy {
            min-width:
                0;

            display:
                flex;

            flex-direction:
                column;
        }

        .expert-command-copy strong {
            color:
                #cfd5de;

            font-size:
                9px;

            font-weight:
                760;
        }

        .expert-command-copy small {
            margin-top:
                2px;

            overflow:
                hidden;

            color:
                #596371;

            font-size:
                6px;

            text-overflow:
                ellipsis;

            white-space:
                nowrap;
        }

        .expert-command-key {
            min-width:
                23px;

            height:
                23px;

            display:
                grid;

            place-items:
                center;

            border:
                1px solid var(--nav-line);

            border-bottom-color:
                rgba(255, 255, 255, .15);

            border-radius:
                6px;

            color:
                #697482;

            background:
                #0c0f15;

            font-size:
                7px;

            font-weight:
                800;
        }

        /*
        | Main nav
        */

        .expert-nav-center {
            min-width:
                0;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            gap:
                4px;
        }

        .expert-nav-link {
            position:
                relative;

            min-height:
                42px;

            padding:
                0 13px;

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            gap:
                8px;

            overflow:
                hidden;

            border:
                1px solid transparent;

            border-radius:
                10px;

            color:
                #7d8795;

            background:
                transparent;

            text-decoration:
                none;

            font-size:
                9px;

            font-weight:
                760;

            transition:
                transform .2s ease,
                color .2s ease,
                border-color .2s ease,
                background .2s ease;
        }

        .expert-nav-link:hover {
            transform:
                translateY(-1px);

            border-color:
                rgba(255, 255, 255, .065);

            color:
                #e0e5ec;

            background:
                rgba(255, 255, 255, .028);
        }

        .expert-nav-link.active {
            border-color:
                rgba(122, 108, 255, .17);

            color:
                #f1f2ff;

            background:
                linear-gradient(
                    135deg,
                    rgba(122, 108, 255, .13),
                    rgba(75, 156, 255, .04)
                );
        }

        .expert-nav-link.active::after {
            content:
                "";

            position:
                absolute;

            left:
                15px;

            right:
                15px;

            bottom:
                3px;

            height:
                1px;

            background:
                linear-gradient(
                    90deg,
                    transparent,
                    #8378ff,
                    #62adff,
                    transparent
                );
        }

        .expert-nav-link-icon {
            color:
                #9991ff;

            font-size:
                9px;
        }

        .expert-nav-badge,
        .expert-nav-ai-badge {
            min-width:
                20px;

            height:
                19px;

            padding:
                0 6px;

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            border:
                1px solid rgba(122, 108, 255, .17);

            border-radius:
                999px;

            color:
                #aca5ff;

            background:
                rgba(122, 108, 255, .07);

            font-size:
                6px;

            font-weight:
                850;
        }

        .expert-nav-link-ai {
            color:
                #a29cff;
        }

        /*
        | Right cluster
        */

        .expert-nav-right {
            display:
                flex;

            align-items:
                center;

            justify-content:
                flex-end;

            gap:
                8px;
        }

        .expert-new-project {
            min-height:
                43px;

            padding:
                0 14px;

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            gap:
                8px;

            border:
                1px solid rgba(122, 108, 255, .28);

            border-radius:
                10px;

            color:
                #ffffff;

            background:
                linear-gradient(
                    135deg,
                    #7766ff,
                    #4d91ff
                );

            box-shadow:
                0 12px 34px rgba(75, 67, 208, .20),
                inset 0 1px 0 rgba(255, 255, 255, .18);

            text-decoration:
                none;

            font-size:
                9px;

            font-weight:
                800;

            transition:
                transform .2s ease,
                box-shadow .2s ease;
        }

        .expert-new-project:hover {
            transform:
                translateY(-2px);

            box-shadow:
                0 17px 43px rgba(75, 67, 208, .29);
        }

        .expert-auth-link {
            min-height:
                42px;

            padding:
                0 13px;

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            border:
                1px solid var(--nav-line);

            border-radius:
                10px;

            color:
                #a9b1bd;

            background:
                rgba(255, 255, 255, .016);

            text-decoration:
                none;

            font-size:
                9px;

            font-weight:
                760;
        }

        .expert-auth-link-primary {
            border-color:
                rgba(122, 108, 255, .24);

            color:
                #ffffff;

            background:
                rgba(122, 108, 255, .09);
        }

        /*
        | Account trigger
        */

        .expert-account-trigger {
            min-height:
                44px;

            max-width:
                230px;

            padding:
                5px 8px 5px 5px;

            display:
                flex;

            align-items:
                center;

            gap:
                9px;

            border:
                1px solid var(--nav-line);

            border-radius:
                11px;

            background:
                rgba(255, 255, 255, .018);
        }

        .expert-account-trigger:hover,
        .expert-account-trigger[aria-expanded="true"] {
            border-color:
                rgba(122, 108, 255, .21);

            background:
                linear-gradient(
                    135deg,
                    rgba(122, 108, 255, .075),
                    rgba(75, 156, 255, .022)
                );
        }

        .expert-account-avatar,
        .expert-account-menu-avatar {
            display:
                grid;

            place-items:
                center;

            overflow:
                hidden;

            border:
                1px solid rgba(122, 108, 255, .20);

            color:
                #ffffff;

            background:
                linear-gradient(
                    145deg,
                    #7968ff,
                    #4f83ea
                );

            font-weight:
                850;
        }

        .expert-account-avatar {
            width:
                32px;

            height:
                32px;

            flex:
                0 0 32px;

            border-radius:
                9px;

            font-size:
                9px;
        }

        .expert-account-avatar img,
        .expert-account-menu-avatar img {
            width:
                100%;

            height:
                100%;

            object-fit:
                cover;
        }

        .expert-account-copy {
            min-width:
                0;

            flex:
                1;

            display:
                flex;

            flex-direction:
                column;

            text-align:
                left;
        }

        .expert-account-copy strong {
            overflow:
                hidden;

            color:
                #dfe4eb;

            font-size:
                9px;

            font-weight:
                760;

            text-overflow:
                ellipsis;

            white-space:
                nowrap;
        }

        .expert-account-copy small {
            margin-top:
                2px;

            color:
                #596472;

            font-size:
                6px;

            text-transform:
                uppercase;

            letter-spacing:
                .09em;
        }

        .expert-account-chevron {
            color:
                #6d7784;

            font-size:
                8px;

            transition:
                transform .2s ease;
        }

        .expert-account-trigger[aria-expanded="true"]
        .expert-account-chevron {
            transform:
                rotate(180deg);
        }

        /*
        | Rich account menu
        */

        .expert-account-menu {
            width:
                min(390px, calc(100vw - 28px));

            overflow:
                hidden;

            border:
                1px solid rgba(122, 108, 255, .13);

            border-radius:
                18px;

            background:
                rgba(8, 10, 15, .985);

            box-shadow:
                0 36px 110px rgba(0, 0, 0, .52);

            backdrop-filter:
                blur(26px)
                saturate(135%);

            -webkit-backdrop-filter:
                blur(26px)
                saturate(135%);
        }

        .expert-account-hero {
            padding:
                18px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            gap:
                16px;

            border-bottom:
                1px solid var(--nav-line);

            background:
                radial-gradient(
                    circle at 100% 0%,
                    rgba(122, 108, 255, .13),
                    transparent 12rem
                );
        }

        .expert-account-identity {
            min-width:
                0;

            display:
                flex;

            align-items:
                center;

            gap:
                12px;
        }

        .expert-account-menu-avatar {
            width:
                45px;

            height:
                45px;

            flex:
                0 0 45px;

            border-radius:
                12px;

            font-size:
                12px;
        }

        .expert-account-identity > span:last-child {
            min-width:
                0;

            display:
                flex;

            flex-direction:
                column;
        }

        .expert-account-identity strong {
            overflow:
                hidden;

            color:
                #eef2f7;

            font-size:
                11px;

            font-weight:
                780;

            text-overflow:
                ellipsis;

            white-space:
                nowrap;
        }

        .expert-account-identity small {
            margin-top:
                3px;

            overflow:
                hidden;

            color:
                #67717f;

            font-size:
                8px;

            text-overflow:
                ellipsis;

            white-space:
                nowrap;
        }

        .expert-plan-chip {
            min-height:
                25px;

            padding:
                0 8px;

            display:
                inline-flex;

            align-items:
                center;

            border:
                1px solid rgba(122, 108, 255, .15);

            border-radius:
                999px;

            color:
                #aba4ff;

            background:
                rgba(122, 108, 255, .06);

            font-size:
                6px;

            font-weight:
                850;

            text-transform:
                uppercase;

            letter-spacing:
                .08em;
        }

        .expert-account-section {
            padding:
                9px;
        }

        .expert-account-section + .expert-account-section {
            border-top:
                1px solid var(--nav-line);
        }

        .expert-account-section-label {
            padding:
                7px 9px 8px;

            color:
                #535e6c;

            font-size:
                7px;

            font-weight:
                850;

            letter-spacing:
                .12em;

            text-transform:
                uppercase;
        }

        .expert-account-item {
            min-height:
                57px;

            padding:
                8px 9px;

            display:
                grid;

            grid-template-columns:
                34px minmax(0, 1fr) auto;

            align-items:
                center;

            gap:
                10px;

            border:
                1px solid transparent;

            border-radius:
                10px;

            text-decoration:
                none;

            transition:
                border-color .18s ease,
                background .18s ease,
                transform .18s ease;
        }

        .expert-account-item:hover {
            transform:
                translateX(2px);

            border-color:
                rgba(122, 108, 255, .10);

            background:
                linear-gradient(
                    90deg,
                    rgba(122, 108, 255, .075),
                    rgba(75, 156, 255, .015)
                );
        }

        .expert-account-item-icon {
            width:
                34px;

            height:
                34px;

            display:
                grid;

            place-items:
                center;

            border:
                1px solid var(--nav-line);

            border-radius:
                9px;

            color:
                #978fff;

            background:
                rgba(255, 255, 255, .018);

            font-size:
                9px;
        }

        .expert-account-item-copy {
            min-width:
                0;

            display:
                flex;

            flex-direction:
                column;
        }

        .expert-account-item-copy strong {
            color:
                #d7dce4;

            font-size:
                9px;

            font-weight:
                760;
        }

        .expert-account-item-copy small {
            margin-top:
                3px;

            color:
                #5e6876;

            font-size:
                7px;
        }

        .expert-account-item-meta {
            color:
                #707b89;

            font-size:
                8px;

            font-weight:
                800;
        }

        .expert-account-footer {
            padding:
                9px;

            border-top:
                1px solid var(--nav-line);
        }

        .expert-account-footer form {
            margin:
                0;
        }

        .expert-logout-button {
            width:
                100%;

            min-height:
                44px;

            padding:
                0 11px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            border:
                1px solid transparent;

            border-radius:
                9px;

            color:
                #db8b99;

            background:
                transparent;

            font-size:
                9px;

            font-weight:
                760;

            cursor:
                pointer;
        }

        .expert-logout-button:hover {
            border-color:
                rgba(255, 135, 155, .11);

            background:
                rgba(255, 135, 155, .045);
        }

        /*
        | Command palette
        */

        .expert-command-overlay {
            position:
                fixed;

            z-index:
                1500;

            inset:
                0;

            opacity:
                0;

            visibility:
                hidden;

            pointer-events:
                none;

            background:
                rgba(2, 3, 6, .72);

            backdrop-filter:
                blur(8px);

            -webkit-backdrop-filter:
                blur(8px);

            transition:
                opacity .2s ease,
                visibility .2s ease;
        }

        .expert-command-overlay.is-open {
            opacity:
                1;

            visibility:
                visible;

            pointer-events:
                auto;
        }

        .expert-command-palette {
            position:
                fixed;

            z-index:
                1510;

            left:
                50%;

            top:
                min(15vh, 130px);

            width:
                min(calc(100% - 28px), 650px);

            transform:
                translate(-50%, -18px)
                scale(.98);

            opacity:
                0;

            visibility:
                hidden;

            pointer-events:
                none;

            transition:
                opacity .22s ease,
                visibility .22s ease,
                transform .22s cubic-bezier(.16, 1, .3, 1);
        }

        .expert-command-palette.is-open {
            transform:
                translate(-50%, 0)
                scale(1);

            opacity:
                1;

            visibility:
                visible;

            pointer-events:
                auto;
        }

        .expert-command-shell {
            overflow:
                hidden;

            border:
                1px solid rgba(122, 108, 255, .16);

            border-radius:
                20px;

            background:
                radial-gradient(
                    circle at 100% 0%,
                    rgba(122, 108, 255, .12),
                    transparent 16rem
                ),
                rgba(8, 10, 15, .985);

            box-shadow:
                0 50px 140px rgba(0, 0, 0, .58);
        }

        .expert-command-head {
            padding:
                18px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            gap:
                20px;
        }

        .expert-command-head > div {
            display:
                flex;

            flex-direction:
                column;
        }

        .expert-command-kicker {
            color:
                #746cff;

            font-size:
                7px;

            font-weight:
                850;

            letter-spacing:
                .14em;

            text-transform:
                uppercase;
        }

        .expert-command-head strong {
            margin-top:
                5px;

            color:
                #f0f3f7;

            font-size:
                14px;

            font-weight:
                780;
        }

        .expert-command-close {
            width:
                34px;

            height:
                34px;

            display:
                grid;

            place-items:
                center;

            border:
                1px solid var(--nav-line);

            border-radius:
                9px;

            color:
                #8f99a6;

            background:
                rgba(255, 255, 255, .018);

            cursor:
                pointer;
        }

        .expert-command-search {
            min-height:
                54px;

            margin:
                0 12px;

            padding:
                0 11px;

            display:
                grid;

            grid-template-columns:
                auto minmax(0, 1fr) auto;

            align-items:
                center;

            gap:
                10px;

            border:
                1px solid var(--nav-line);

            border-radius:
                12px;

            background:
                #0b0e14;

            color:
                #7c8795;
        }

        .expert-command-search input {
            width:
                100%;

            border:
                0;

            outline:
                0;

            color:
                #e6eaf0;

            background:
                transparent;

            font-size:
                11px;
        }

        .expert-command-search input::placeholder {
            color:
                #4f5a68;
        }

        .expert-command-search kbd,
        .expert-command-foot kbd {
            min-width:
                27px;

            height:
                24px;

            padding:
                0 7px;

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            border:
                1px solid var(--nav-line);

            border-bottom-color:
                rgba(255, 255, 255, .15);

            border-radius:
                6px;

            color:
                #697482;

            background:
                #11151c;

            font-family:
                inherit;

            font-size:
                7px;
        }

        .expert-command-groups {
            max-height:
                min(57vh, 490px);

            overflow-y:
                auto;

            padding:
                12px;
        }

        .expert-command-group + .expert-command-group {
            margin-top:
                8px;

            padding-top:
                8px;

            border-top:
                1px solid var(--nav-line);
        }

        .expert-command-group-label {
            padding:
                5px 8px 8px;

            color:
                #505b69;

            font-size:
                7px;

            font-weight:
                850;

            letter-spacing:
                .12em;

            text-transform:
                uppercase;
        }

        .expert-command-item {
            min-height:
                58px;

            padding:
                8px 9px;

            display:
                grid;

            grid-template-columns:
                36px minmax(0, 1fr) auto;

            align-items:
                center;

            gap:
                10px;

            border:
                1px solid transparent;

            border-radius:
                10px;

            text-decoration:
                none;

            transition:
                border-color .16s ease,
                background .16s ease;
        }

        .expert-command-item:hover,
        .expert-command-item.is-selected {
            border-color:
                rgba(122, 108, 255, .10);

            background:
                linear-gradient(
                    90deg,
                    rgba(122, 108, 255, .085),
                    rgba(75, 156, 255, .02)
                );
        }

        .expert-command-item[hidden] {
            display:
                none !important;
        }

        .expert-command-item-icon {
            width:
                36px;

            height:
                36px;

            display:
                grid;

            place-items:
                center;

            border:
                1px solid var(--nav-line);

            border-radius:
                9px;

            color:
                #988fff;

            background:
                rgba(255, 255, 255, .018);
        }

        .expert-command-item > span:nth-child(2) {
            min-width:
                0;

            display:
                flex;

            flex-direction:
                column;
        }

        .expert-command-item strong {
            color:
                #dce1e8;

            font-size:
                10px;

            font-weight:
                760;
        }

        .expert-command-item small {
            margin-top:
                3px;

            color:
                #5f6976;

            font-size:
                7px;
        }

        .expert-command-item-arrow {
            color:
                #65707e;

            font-size:
                8px;
        }

        .expert-command-foot {
            min-height:
                46px;

            padding:
                0 14px;

            display:
                flex;

            align-items:
                center;

            gap:
                16px;

            border-top:
                1px solid var(--nav-line);

            color:
                #596472;

            font-size:
                7px;
        }

        .expert-command-foot span {
            display:
                inline-flex;

            align-items:
                center;

            gap:
                5px;
        }

        /*
        | Mobile drawer
        */

        .expert-mobile-drawer {
            width:
                min(430px, 94vw);

            padding:
                calc(15px + env(safe-area-inset-top))
                calc(15px + env(safe-area-inset-right))
                calc(15px + env(safe-area-inset-bottom))
                15px;

            border-left:
                1px solid rgba(122, 108, 255, .13);

            background:
                radial-gradient(
                    circle at 100% 0%,
                    rgba(122, 108, 255, .13),
                    transparent 20rem
                ),
                rgba(7, 9, 14, .988);

            box-shadow:
                -40px 0 120px rgba(0, 0, 0, .55);
        }

        .expert-mobile-head {
            min-height:
                58px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            gap:
                16px;

            padding-bottom:
                13px;

            border-bottom:
                1px solid var(--nav-line);
        }

        .expert-mobile-brand {
            display:
                flex;

            align-items:
                center;

            gap:
                10px;

            text-decoration:
                none;
        }

        .expert-mobile-brand > span:last-child {
            display:
                flex;

            flex-direction:
                column;
        }

        .expert-mobile-brand strong {
            color:
                #f1f4f8;

            font-size:
                11px;

            font-weight:
                800;
        }

        .expert-mobile-brand small {
            margin-top:
                3px;

            color:
                #5d6876;

            font-size:
                7px;
        }

        .expert-mobile-close {
            width:
                38px;

            height:
                38px;

            display:
                grid;

            place-items:
                center;

            border:
                1px solid var(--nav-line);

            border-radius:
                10px;

            color:
                #98a2af;

            background:
                rgba(255, 255, 255, .02);

            cursor:
                pointer;
        }

        .expert-mobile-upload {
            min-height:
                66px;

            margin-top:
                14px;

            padding:
                10px 12px;

            display:
                grid;

            grid-template-columns:
                38px minmax(0, 1fr) auto;

            align-items:
                center;

            gap:
                10px;

            border:
                1px solid rgba(122, 108, 255, .20);

            border-radius:
                13px;

            color:
                inherit;

            background:
                linear-gradient(
                    135deg,
                    rgba(122, 108, 255, .12),
                    rgba(75, 156, 255, .04)
                );

            text-decoration:
                none;
        }

        .expert-mobile-upload-icon {
            width:
                38px;

            height:
                38px;

            display:
                grid;

            place-items:
                center;

            border-radius:
                10px;

            color:
                #ffffff;

            background:
                linear-gradient(
                    135deg,
                    #7766ff,
                    #4f91ff
                );
        }

        .expert-mobile-upload > span:nth-child(2) {
            display:
                flex;

            flex-direction:
                column;
        }

        .expert-mobile-upload strong {
            color:
                #eef2f7;

            font-size:
                10px;
        }

        .expert-mobile-upload small {
            margin-top:
                3px;

            color:
                #697482;

            font-size:
                7px;
        }

        .expert-mobile-upload > span:last-child {
            color:
                #8e86ff;

            font-size:
                9px;
        }

        .expert-mobile-user {
            margin-top:
                12px;

            padding:
                12px;

            display:
                flex;

            align-items:
                center;

            gap:
                11px;

            border:
                1px solid var(--nav-line);

            border-radius:
                12px;

            background:
                rgba(255, 255, 255, .015);
        }

        .expert-mobile-user-copy {
            min-width:
                0;

            display:
                flex;

            flex-direction:
                column;
        }

        .expert-mobile-user-copy strong {
            overflow:
                hidden;

            color:
                #dfe4ea;

            font-size:
                9px;

            text-overflow:
                ellipsis;

            white-space:
                nowrap;
        }

        .expert-mobile-user-copy small {
            margin-top:
                3px;

            overflow:
                hidden;

            color:
                #606b79;

            font-size:
                7px;

            text-overflow:
                ellipsis;

            white-space:
                nowrap;
        }

        .expert-mobile-nav {
            margin-top:
                13px;

            display:
                grid;

            gap:
                4px;
        }

        .expert-mobile-label {
            margin:
                12px 7px 5px;

            color:
                #4e5967;

            font-size:
                7px;

            font-weight:
                850;

            letter-spacing:
                .12em;

            text-transform:
                uppercase;
        }

        .expert-mobile-link {
            min-height:
                58px;

            padding:
                7px 9px;

            display:
                grid;

            grid-template-columns:
                36px minmax(0, 1fr) auto;

            align-items:
                center;

            gap:
                10px;

            border:
                1px solid transparent;

            border-radius:
                10px;

            color:
                inherit;

            text-decoration:
                none;
        }

        .expert-mobile-link:hover,
        .expert-mobile-link.active {
            border-color:
                rgba(122, 108, 255, .10);

            background:
                linear-gradient(
                    90deg,
                    rgba(122, 108, 255, .08),
                    rgba(75, 156, 255, .015)
                );
        }

        .expert-mobile-link-icon {
            width:
                36px;

            height:
                36px;

            display:
                grid;

            place-items:
                center;

            border:
                1px solid var(--nav-line);

            border-radius:
                9px;

            color:
                #978fff;

            background:
                rgba(255, 255, 255, .018);
        }

        .expert-mobile-link > span:nth-child(2) {
            min-width:
                0;

            display:
                flex;

            flex-direction:
                column;
        }

        .expert-mobile-link strong {
            color:
                #d8dde4;

            font-size:
                9px;
        }

        .expert-mobile-link small {
            margin-top:
                3px;

            color:
                #5e6977;

            font-size:
                7px;
        }

        .expert-mobile-link > span:last-child {
            color:
                #697482;

            font-size:
                8px;
        }

        .expert-mobile-ai {
            min-height:
                20px;

            padding:
                0 6px;

            display:
                inline-flex;

            align-items:
                center;

            border:
                1px solid rgba(122, 108, 255, .16);

            border-radius:
                999px;

            color:
                #aaa3ff !important;

            background:
                rgba(122, 108, 255, .06);
        }

        .expert-mobile-footer {
            margin-top:
                auto;

            padding-top:
                15px;

            display:
                grid;

            gap:
                8px;

            border-top:
                1px solid var(--nav-line);
        }

        .expert-mobile-auth,
        .expert-mobile-logout {
            width:
                100%;

            min-height:
                48px;

            padding:
                0 14px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            border:
                1px solid var(--nav-line);

            border-radius:
                10px;

            color:
                #c9d0da;

            background:
                rgba(255, 255, 255, .018);

            text-decoration:
                none;

            font-size:
                9px;

            font-weight:
                760;
        }

        .expert-mobile-auth-primary {
            border-color:
                rgba(122, 108, 255, .24);

            color:
                #ffffff;

            background:
                linear-gradient(
                    135deg,
                    #7766ff,
                    #4f91ff
                );
        }

        .expert-mobile-logout {
            justify-content:
                space-between;

            color:
                #dd8f9d;

            cursor:
                pointer;
        }

        /*
        | Responsive
        */

        .expert-menu-toggle {
            display:
                none;
        }

        @media (max-width: 1250px) {
            .expert-command-trigger {
                min-width:
                    43px;

                width:
                    43px;

                grid-template-columns:
                    1fr;

                padding:
                    0;
            }

            .expert-command-copy,
            .expert-command-key {
                display:
                    none;
            }

            .expert-command-icon {
                margin:
                    auto;

                border:
                    0;

                background:
                    transparent;
            }
        }

        @media (max-width: 1120px) {
            .expert-nav {
                grid-template-columns:
                    auto 1fr auto;

                gap:
                    14px;
            }

            .expert-nav-center {
                display:
                    none;
            }

            .expert-new-project {
                display:
                    none;
            }

            .expert-menu-toggle {
                display:
                    grid;
            }

            .studio-mobile-overlay,
            .studio-mobile-drawer {
                display:
                    flex;
            }
        }

        @media (max-width: 760px) {
            .expert-brand-divider,
            .expert-command-trigger {
                display:
                    none;
            }

            .expert-nav {
                min-height:
                    70px;

                grid-template-columns:
                    minmax(0, 1fr)
                    auto;
            }

            .expert-account-copy,
            .expert-account-chevron {
                display:
                    none;
            }

            .expert-account-trigger {
                max-width:
                    43px;

                padding:
                    5px;
            }

            .expert-auth-link {
                display:
                    none;
            }

            .expert-auth-link-primary {
                display:
                    inline-flex;
            }
        }

        @media (max-width: 520px) {
            .expert-brand-copy small {
                display:
                    none;
            }

            .expert-auth-link-primary {
                display:
                    none;
            }

            .expert-mobile-drawer {
                width:
                    100vw;

                max-width:
                    100%;

                border-left:
                    0;
            }

            .expert-command-palette {
                top:
                    72px;

                width:
                    calc(100% - 20px);
            }

            .expert-command-foot {
                display:
                    none;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .expert-command-palette,
            .expert-command-overlay,
            .expert-nav-link,
            .expert-new-project,
            .expert-account-item,
            .expert-command-trigger {
                transition:
                    none !important;
            }
        }
    </style>



    <style id="mashal-language-switcher">
        .expert-language-switcher {
            display: inline-flex;
            align-items: center;
            flex-shrink: 0;
            gap: 2px;
            padding: 3px;
            border: 1px solid rgba(122, 108, 255, .20);
            border-radius: 12px;
            background: rgba(10, 13, 20, .9);
            direction: ltr;
        }

        .expert-language-switcher form {
            margin: 0;
            padding: 0;
        }

        .expert-language-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 39px;
            height: 33px;
            padding: 0 9px;
            border: 1px solid transparent;
            border-radius: 9px;
            background: transparent;
            color: #bdc6d6;
            font-family: inherit;
            font-size: 12px;
            font-weight: 800;
            line-height: 1;
            white-space: nowrap;
            cursor: pointer;
            transition: color .15s ease, background .15s ease, border-color .15s ease;
        }

        .expert-language-button[lang="ur"] {
            font-family: "Noto Nastaliq Urdu", "Noto Naskh Arabic", Tahoma, sans-serif;
        }

        .expert-language-button:hover {
            color: #fff;
            background: rgba(122, 108, 255, .12);
        }

        .expert-language-button[aria-pressed="true"] {
            color: #fff;
            border-color: rgba(122, 108, 255, .32);
            background: rgba(122, 108, 255, .22);
        }

        .expert-language-button:focus-visible {
            outline: 2px solid #a99fff;
            outline-offset: 2px;
        }

        @media (max-width: 520px) {
            .expert-language-switcher {
                padding: 2px;
            }

            .expert-language-button {
                min-width: 34px;
                height: 31px;
                padding: 0 6px;
                font-size: 11px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .expert-language-button {
                transition: none;
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
        {{ __('Ga naar inhoud') }}
    </a>

    <div
        class="studio-progress"
        aria-hidden="true"
    >
        <div
            class="studio-progress-bar"
            id="studioProgressBar"
        ></div>
    </div>

    <div
        class="studio-atmosphere"
        aria-hidden="true"
    >
        <span class="studio-orb one"></span>
        <span class="studio-orb two"></span>
        <span class="studio-orb three"></span>
    </div>

    <header
        class="studio-header studio-header-expert"
        id="studioHeader"
    >
        <div class="studio-shell">
            <div class="expert-nav">
                <div class="expert-nav-left">
                    <a
                        class="expert-brand"
                        href="{{ route('home') }}"
                        aria-label="Mashal Studio home"
                    >
                        <span class="expert-brand-mark" aria-hidden="true">
                            <span class="expert-brand-glyph">M</span>
                            <span class="expert-brand-status"></span>
                        </span>

                        <span class="expert-brand-copy">
                            <strong>Mashal</strong>
                            <small>Studio</small>
                        </span>
                    </a>

                    <span class="expert-brand-divider" aria-hidden="true"></span>

                    <button
                        class="expert-command-trigger"
                        id="studioCommandTrigger"
                        type="button"
                        aria-haspopup="dialog"
                        aria-controls="studioCommandPalette"
                        aria-expanded="false"
                    >
                        <span class="expert-command-icon" aria-hidden="true">
                            ⌘
                        </span>

                        <span class="expert-command-copy">
                            <strong>{{ __('Ga naar…') }}</strong>
                            <small>{{ __('Projecten, AI, account') }}</small>
                        </span>

                        <span class="expert-command-key" aria-hidden="true">
                            K
                        </span>
                    </button>
                </div>

                <nav
                    class="expert-nav-center"
                    aria-label="Hoofdnavigatie"
                >
                    <a
                        class="expert-nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                        href="{{ route('home') }}"
                    >
                        <span class="expert-nav-link-icon" aria-hidden="true">
                            ◇
                        </span>

                        <span>Studio</span>
                    </a>

                    @if ($hasLiveCounts)
                        <a
                            class="expert-nav-link {{ request()->routeIs('live-counts.*') || request()->routeIs('tiktok-counter.*') || request()->routeIs('tiktok-follower-counter.*') ? 'active' : '' }}"
                            href="{{ route('live-counts.index') }}"
                        >
                            <span class="expert-nav-link-icon" aria-hidden="true">
                                ◉
                            </span>

                            <span>Live Counts</span>
                        </a>
                    @endif

                    @auth
                        @if ($hasImagesIndex)
                            <a
                                class="expert-nav-link {{ request()->routeIs('images.*') ? 'active' : '' }}"
                                href="{{ route('images.index') }}"
                            >
                                <span class="expert-nav-link-icon" aria-hidden="true">
                                    ▦
                                </span>

                                <span>Library</span>

                                @if ($layoutImageCount !== null)
                                    <span class="expert-nav-badge">
                                        {{ min($layoutImageCount, 999) }}
                                    </span>
                                @endif
                            </a>
                        @endif

                        @if ($hasAiChat)
                            <a
                                class="expert-nav-link expert-nav-link-ai {{ request()->routeIs('ai.chat*') ? 'active' : '' }}"
                                href="{{ route('ai.chat') }}"
                            >
                                <span class="expert-nav-link-icon" aria-hidden="true">
                                    ✦
                                </span>

                                <span>Mashal AI</span>

                                <span class="expert-nav-ai-badge">
                                    AI
                                </span>
                            </a>
                        @endif
                    @endauth
                </nav>

                <div class="expert-nav-right">
                    @include('partials.language-switcher')

                    <a
                        class="expert-new-project"
                        href="{{ route('home') }}#upload"
                        aria-label="Nieuwe afbeelding"
                        title="Nieuwe afbeelding"
                    >
                        <span aria-hidden="true">＋</span>
                    </a>

                    @guest
                        <a
                            class="expert-auth-link"
                            href="{{ route('login') }}"
                        >
                            {{ __('Inloggen') }}
                        </a>

                        <a
                            class="expert-auth-link expert-auth-link-primary"
                            href="{{ route('register') }}"
                        >
                            {{ __('Start gratis') }}
                        </a>
                    @else
                        <div class="studio-account-wrap expert-account-wrap">
                            <button
                                class="studio-account-trigger expert-account-trigger"
                                id="studioAccountTrigger"
                                type="button"
                                aria-expanded="false"
                                aria-controls="studioAccountMenu"
                            >
                                <span class="expert-account-avatar">
                                    @if (
                                        method_exists($layoutUser, 'avatarUrl') &&
                                        $layoutUser->avatarUrl()
                                    )
                                        <img
                                            src="{{ $layoutUser->avatarUrl() }}"
                                            alt="Profielfoto van {{ $layoutUser->name }}"
                                            loading="eager"
                                        >
                                    @else
                                        {{ $layoutInitials }}
                                    @endif
                                </span>

                                <span class="expert-account-copy">
                                    <strong>
                                        {{ $layoutUser->name }}
                                    </strong>

                                    <small>
                                        Workspace
                                    </small>
                                </span>

                                <span
                                    class="expert-account-chevron"
                                    aria-hidden="true"
                                >
                                    ▾
                                </span>
                            </button>

                            <div
                                class="studio-account-menu expert-account-menu"
                                id="studioAccountMenu"
                                aria-hidden="true"
                            >
                                <div class="expert-account-hero">
                                    <div class="expert-account-identity">
                                        <span class="expert-account-menu-avatar">
                                            @if (
                                                method_exists($layoutUser, 'avatarUrl') &&
                                                $layoutUser->avatarUrl()
                                            )
                                                <img
                                                    src="{{ $layoutUser->avatarUrl() }}"
                                                    alt=""
                                                >
                                            @else
                                                {{ $layoutInitials }}
                                            @endif
                                        </span>

                                        <span>
                                            <strong>{{ $layoutUser->name }}</strong>
                                            <small>{{ $layoutUser->email }}</small>
                                        </span>
                                    </div>

                                    <span class="expert-plan-chip">
                                        Workspace
                                    </span>
                                </div>

                                <div class="expert-account-section">
                                    <div class="expert-account-section-label">
                                        Workspace
                                    </div>

                                    @if ($hasImagesIndex)
                                        <a
                                            class="expert-account-item"
                                            href="{{ route('images.index') }}"
                                        >
                                            <span class="expert-account-item-icon">▦</span>

                                            <span class="expert-account-item-copy">
                                                <strong>{{ __('Mijn afbeeldingen') }}</strong>
                                                <small>{{ __('Beheer projecten en versies') }}</small>
                                            </span>

                                            <span class="expert-account-item-meta">
                                                {{ $layoutImageCount ?? '→' }}
                                            </span>
                                        </a>
                                    @endif

                                    @if ($hasAiChat)
                                        <a
                                            class="expert-account-item"
                                            href="{{ route('ai.chat') }}"
                                        >
                                            <span class="expert-account-item-icon">✦</span>

                                            <span class="expert-account-item-copy">
                                                <strong>Mashal AI</strong>
                                                <small>{{ __('Open je AI workspace') }}</small>
                                            </span>

                                            <span class="expert-account-item-meta">
                                                AI
                                            </span>
                                        </a>
                                    @endif
                                </div>

                                <div class="expert-account-section">
                                    <div class="expert-account-section-label">
                                        Account
                                    </div>

                                    @if ($hasAccount)
                                        <a
                                            class="expert-account-item"
                                            href="{{ route('account') }}"
                                        >
                                            <span class="expert-account-item-icon">○</span>

                                            <span class="expert-account-item-copy">
                                                <strong>{{ __('Accountinstellingen') }}</strong>
                                                <small>{{ __('Profiel en voorkeuren') }}</small>
                                            </span>

                                            <span class="expert-account-item-meta">→</span>
                                        </a>
                                    @endif

                                    @if ($hasSecurity)
                                        <a
                                            class="expert-account-item"
                                            href="{{ route('security.index') }}"
                                        >
                                            <span class="expert-account-item-icon">◈</span>

                                            <span class="expert-account-item-copy">
                                                <strong>{{ __('Beveiliging') }}</strong>
                                                <small>{{ __('Login en apparaten') }}</small>
                                            </span>

                                            <span class="expert-account-item-meta">→</span>
                                        </a>
                                    @endif

                                    @if (
                                        $layoutIsAdmin &&
                                        $hasAdmin
                                    )
                                        <a
                                            class="expert-account-item"
                                            href="{{ route('admin.dashboard') }}"
                                        >
                                            <span class="expert-account-item-icon">⌁</span>

                                            <span class="expert-account-item-copy">
                                                <strong>Admin</strong>
                                                <small>{{ __('Beheer de applicatie') }}</small>
                                            </span>

                                            <span class="expert-account-item-meta">→</span>
                                        </a>
                                    @endif
                                </div>

                                <div class="expert-account-footer">
                                    <form
                                        method="POST"
                                        action="{{ route('logout') }}"
                                    >
                                        @csrf

                                        <button
                                            class="expert-logout-button"
                                            type="submit"
                                        >
                                            <span>{{ __('Uitloggen') }}</span>
                                            <span aria-hidden="true">↗</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endguest

                    <button
                        class="studio-menu-toggle expert-menu-toggle"
                        id="studioMenuToggle"
                        type="button"
                        aria-label="Menu openen"
                        aria-expanded="false"
                        aria-controls="studioMobileDrawer"
                    >
                        <span class="studio-menu-orbit" aria-hidden="true"></span>

                        <span class="studio-menu-lines" aria-hidden="true">
                            <i class="studio-menu-line"></i>
                            <i class="studio-menu-line"></i>
                            <i class="studio-menu-line"></i>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <div
        class="expert-command-overlay"
        id="studioCommandOverlay"
        aria-hidden="true"
    ></div>

    <div
        class="expert-command-palette"
        id="studioCommandPalette"
        role="dialog"
        aria-modal="true"
        aria-labelledby="studioCommandTitle"
        aria-hidden="true"
    >
        <div class="expert-command-shell">
            <div class="expert-command-head">
                <div>
                    <span class="expert-command-kicker">
                        Navigation
                    </span>

                    <strong id="studioCommandTitle">
                        {{ __('Waar wil je heen?') }}
                    </strong>
                </div>

                <button
                    class="expert-command-close"
                    id="studioCommandClose"
                    type="button"
                    aria-label="Command menu sluiten"
                >
                    ×
                </button>
            </div>

            <div class="expert-command-search">
                <span aria-hidden="true">⌕</span>

                <input
                    id="studioCommandInput"
                    type="search"
                    placeholder="Zoek in Mashal Studio…"
                    autocomplete="off"
                >

                <kbd>ESC</kbd>
            </div>

            <div class="expert-command-groups" id="studioCommandGroups">
                <div class="expert-command-group">
                    <div class="expert-command-group-label">
                        Studio
                    </div>

                    <a
                        class="expert-command-item"
                        href="{{ route('home') }}"
                        data-command-search="studio home editor"
                    >
                        <span class="expert-command-item-icon">◇</span>
                        <span>
                            <strong>Studio</strong>
                            <small>{{ __('Ga naar de homepage en editor-start') }}</small>
                        </span>
                        <span class="expert-command-item-arrow">↗</span>
                    </a>

                    @if ($hasLiveCounts)
                        <a
                            class="expert-command-item"
                            href="{{ route('live-counts.index') }}"
                            data-command-search="live counts tiktok followers views likes comments shares statistieken"
                        >
                            <span class="expert-command-item-icon">◉</span>
                            <span>
                                <strong>Live Counts</strong>
                                <small>Kies followers of video views</small>
                            </span>
                            <span class="expert-command-item-arrow">↗</span>
                        </a>
                    @endif

                    @if ($hasTikTokFollowerCounter)
                        <a
                            class="expert-command-item"
                            href="{{ route('tiktok-follower-counter.index') }}"
                            data-command-search="tiktok live followers follower count likes following videos"
                        >
                            <span class="expert-command-item-icon">◎</span>
                            <span>
                                <strong>TikTok Live Followers</strong>
                                <small>Volg followers, likes en profielstats</small>
                            </span>
                            <span class="expert-command-item-arrow">↗</span>
                        </a>
                    @endif

                    @if ($hasTikTokCounter)
                        <a
                            class="expert-command-item"
                            href="{{ route('tiktok-counter.index') }}"
                            data-command-search="tiktok video live views likes comments shares"
                        >
                            <span class="expert-command-item-icon">◉</span>
                            <span>
                                <strong>TikTok Video Views</strong>
                                <small>Volg views, likes, comments en shares</small>
                            </span>
                            <span class="expert-command-item-arrow">↗</span>
                        </a>
                    @endif

                    <a
                        class="expert-command-item"
                        href="{{ route('home') }}#upload"
                        data-command-search="upload nieuwe afbeelding image"
                    >
                        <span class="expert-command-item-icon">＋</span>
                        <span>
                            <strong>{{ __('Nieuwe afbeelding') }}</strong>
                            <small>Upload JPG, PNG of WEBP</small>
                        </span>
                        <span class="expert-command-item-arrow">↗</span>
                    </a>

                    @auth
                        @if ($hasImagesIndex)
                            <a
                                class="expert-command-item"
                                href="{{ route('images.index') }}"
                                data-command-search="library afbeeldingen projecten images"
                            >
                                <span class="expert-command-item-icon">▦</span>
                                <span>
                                    <strong>{{ __('Mijn afbeeldingen') }}</strong>
                                    <small>{{ __('Open je projectbibliotheek') }}</small>
                                </span>
                                <span class="expert-command-item-arrow">↗</span>
                            </a>
                        @endif

                        @if ($hasAiChat)
                            <a
                                class="expert-command-item"
                                href="{{ route('ai.chat') }}"
                                data-command-search="mashal ai chat assistant"
                            >
                                <span class="expert-command-item-icon">✦</span>
                                <span>
                                    <strong>Mashal AI</strong>
                                    <small>{{ __('Open de AI-workspace') }}</small>
                                </span>
                                <span class="expert-command-item-arrow">↗</span>
                            </a>
                        @endif
                    @endauth
                </div>

                @auth
                    <div class="expert-command-group">
                        <div class="expert-command-group-label">
                            Account
                        </div>

                        @if ($hasAccount)
                            <a
                                class="expert-command-item"
                                href="{{ route('account') }}"
                                data-command-search="account profiel instellingen voorkeuren"
                            >
                                <span class="expert-command-item-icon">○</span>
                                <span>
                                    <strong>Account</strong>
                                    <small>{{ __('Profiel en voorkeuren') }}</small>
                                </span>
                                <span class="expert-command-item-arrow">↗</span>
                            </a>
                        @endif

                        @if ($hasSecurity)
                            <a
                                class="expert-command-item"
                                href="{{ route('security.index') }}"
                                data-command-search="security beveiliging login apparaten"
                            >
                                <span class="expert-command-item-icon">◈</span>
                                <span>
                                    <strong>{{ __('Beveiliging') }}</strong>
                                    <small>{{ __('Login en apparaten beheren') }}</small>
                                </span>
                                <span class="expert-command-item-arrow">↗</span>
                            </a>
                        @endif
                    </div>
                @endauth
            </div>

            <div class="expert-command-foot">
                <span>
                    <kbd>↑</kbd>
                    <kbd>↓</kbd>
                    navigeren
                </span>

                <span>
                    <kbd>↵</kbd>
                    openen
                </span>

                <span>
                    <kbd>ESC</kbd>
                    sluiten
                </span>
            </div>
        </div>
    </div>

    <div
        class="studio-mobile-overlay expert-mobile-overlay"
        id="studioMobileOverlay"
        aria-hidden="true"
    ></div>

    <aside
        class="studio-mobile-drawer expert-mobile-drawer"
        id="studioMobileDrawer"
        aria-hidden="true"
        aria-label="Mobiele navigatie"
    >
        <div class="expert-mobile-head">
            <a
                class="expert-mobile-brand"
                href="{{ route('home') }}"
            >
                <span class="expert-brand-mark">
                    <span class="expert-brand-glyph">M</span>
                    <span class="expert-brand-status"></span>
                </span>

                <span>
                    <strong>Mashal Studio</strong>
                    <small>Image workspace</small>
                </span>
            </a>

            <button
                class="expert-mobile-close"
                id="studioMobileClose"
                type="button"
                aria-label="Menu sluiten"
            >
                ×
            </button>
        </div>

        <a
            class="expert-mobile-upload"
            href="{{ route('home') }}#upload"
        >
            <span class="expert-mobile-upload-icon">＋</span>

            <span>
                <strong>{{ __('Nieuwe afbeelding') }}</strong>
                <small>JPG, PNG of WEBP uploaden</small>
            </span>

            <span>↗</span>
        </a>

        @auth
            <div class="expert-mobile-user">
                <span class="expert-account-menu-avatar">
                    @if (
                        method_exists($layoutUser, 'avatarUrl') &&
                        $layoutUser->avatarUrl()
                    )
                        <img
                            src="{{ $layoutUser->avatarUrl() }}"
                            alt=""
                        >
                    @else
                        {{ $layoutInitials }}
                    @endif
                </span>

                <span class="expert-mobile-user-copy">
                    <strong>{{ $layoutUser->name }}</strong>
                    <small>{{ $layoutUser->email }}</small>
                </span>
            </div>
        @endauth

        <nav class="expert-mobile-nav">
            <div class="expert-mobile-label">
                Workspace
            </div>

            <a
                class="expert-mobile-link {{ request()->routeIs('home') ? 'active' : '' }}"
                href="{{ route('home') }}"
            >
                <span class="expert-mobile-link-icon">◇</span>
                <span>
                    <strong>Studio</strong>
                    <small>{{ __('Home en editor-start') }}</small>
                </span>
                <span>→</span>
            </a>

            @if ($hasLiveCounts)
                <a
                    class="expert-mobile-link {{ request()->routeIs('live-counts.*') || request()->routeIs('tiktok-counter.*') || request()->routeIs('tiktok-follower-counter.*') ? 'active' : '' }}"
                    href="{{ route('live-counts.index') }}"
                >
                    <span class="expert-mobile-link-icon">◉</span>
                    <span>
                        <strong>Live Counts</strong>
                        <small>{{ __('Kies je live counter') }}</small>
                    </span>
                    <span>→</span>
                </a>
            @endif

            @auth
                @if ($hasImagesIndex)
                    <a
                        class="expert-mobile-link {{ request()->routeIs('images.*') ? 'active' : '' }}"
                        href="{{ route('images.index') }}"
                    >
                        <span class="expert-mobile-link-icon">▦</span>
                        <span>
                            <strong>{{ __('Mijn afbeeldingen') }}</strong>
                            <small>{{ __('Projecten en versies') }}</small>
                        </span>
                        <span>→</span>
                    </a>
                @endif

                @if ($hasAiChat)
                    <a
                        class="expert-mobile-link {{ request()->routeIs('ai.chat*') ? 'active' : '' }}"
                        href="{{ route('ai.chat') }}"
                    >
                        <span class="expert-mobile-link-icon">✦</span>
                        <span>
                            <strong>Mashal AI</strong>
                            <small>AI workspace</small>
                        </span>
                        <span class="expert-mobile-ai">AI</span>
                    </a>
                @endif

                <div class="expert-mobile-label">
                    Account
                </div>

                @if ($hasAccount)
                    <a
                        class="expert-mobile-link {{ request()->routeIs('account') ? 'active' : '' }}"
                        href="{{ route('account') }}"
                    >
                        <span class="expert-mobile-link-icon">○</span>
                        <span>
                            <strong>Account</strong>
                            <small>{{ __('Profiel en voorkeuren') }}</small>
                        </span>
                        <span>→</span>
                    </a>
                @endif

                @if ($hasSecurity)
                    <a
                        class="expert-mobile-link {{ request()->routeIs('security.*') ? 'active' : '' }}"
                        href="{{ route('security.index') }}"
                    >
                        <span class="expert-mobile-link-icon">◈</span>
                        <span>
                            <strong>{{ __('Beveiliging') }}</strong>
                            <small>{{ __('Login en apparaten') }}</small>
                        </span>
                        <span>→</span>
                    </a>
                @endif

                @if (
                    $layoutIsAdmin &&
                    $hasAdmin
                )
                    <a
                        class="expert-mobile-link {{ request()->routeIs('admin.*') ? 'active' : '' }}"
                        href="{{ route('admin.dashboard') }}"
                    >
                        <span class="expert-mobile-link-icon">⌁</span>
                        <span>
                            <strong>Admin</strong>
                            <small>{{ __('Applicatiebeheer') }}</small>
                        </span>
                        <span>→</span>
                    </a>
                @endif
            @endauth
        </nav>

        <div class="expert-mobile-footer">
            @guest
                <a
                    class="expert-mobile-auth"
                    href="{{ route('login') }}"
                >
                    {{ __('Inloggen') }}
                </a>

                <a
                    class="expert-mobile-auth expert-mobile-auth-primary"
                    href="{{ route('register') }}"
                >
                    {{ __('Gratis starten') }}
                </a>
            @else
                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >
                    @csrf

                    <button
                        class="expert-mobile-logout"
                        type="submit"
                    >
                        <span>{{ __('Uitloggen') }}</span>
                        <span>↗</span>
                    </button>
                </form>
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
                            {{ __('Controleer onderstaande gegevens:') }}
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




    <footer class="studio-footer">
        <div class="studio-shell">
            <div class="studio-footer-main">
                <div class="studio-footer-about">
                    <a
                        class="studio-footer-logo"
                        href="{{ route('home') }}"
                    >
                        <span
                            class="studio-brand-mark"
                            aria-hidden="true"
                        >
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
                        {{ __('Een persoonlijke omgeving voor afbeeldingen en AI: upload, bewerk en bewaar je projecten vanuit je eigen workspace en gebruik Mashal AI als aparte assistent onder hetzelfde account.') }}
                    </p>

                    <div class="studio-footer-trust">
                        {{ __('Persoonlijke accountomgeving') }}
                    </div>
                </div>

                <div class="studio-footer-column">
                    <h3>
                        Studio
                    </h3>

                    <div class="studio-footer-links">
                        <a href="{{ route('home') }}">
                            Home
                        </a>

                        @if ($hasLiveCounts)
                            <a href="{{ route('live-counts.index') }}">
                                Live Counts
                            </a>
                        @endif

                        @if ($hasTikTokFollowerCounter)
                            <a href="{{ route('tiktok-follower-counter.index') }}">
                                TikTok Live Followers
                            </a>
                        @endif

                        @if ($hasTikTokCounter)
                            <a href="{{ route('tiktok-counter.index') }}">
                                TikTok Video Views
                            </a>
                        @endif

                        <a href="{{ route('home') }}#upload">
                            {{ __('Upload afbeelding') }}
                        </a>

                        @auth
                            @if ($hasImagesIndex)
                                <a href="{{ route('images.index') }}">
                                    {{ __('Mijn afbeeldingen') }}
                                </a>
                            @endif

                            @if ($hasAiChat)
                                <a href="{{ route('ai.chat') }}">
                                    Mashal AI
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
                            <a href="{{ route('login') }}">
                                {{ __('Inloggen') }}
                            </a>

                            <a href="{{ route('register') }}">
                                {{ __('Registreren') }}
                            </a>
                        @else
                            @if ($hasAccount)
                                <a href="{{ route('account') }}">
                                    {{ __('Accountinstellingen') }}
                                </a>
                            @endif

                            @if ($hasSecurity)
                                <a href="{{ route('security.index') }}">
                                    {{ __('Beveiliging') }}
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
                        <a href="{{ route('home') }}#upload">
                            {{ __('Nieuw project') }}
                        </a>

                        @auth
                            @if ($hasImagesIndex)
                                <a href="{{ route('images.index') }}">
                                    {{ __('Projectbibliotheek') }}
                                </a>
                            @endif

                            @if ($hasAiChat)
                                <a href="{{ route('ai.chat') }}">
                                    {{ __('AI-assistent') }}
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

                @if (
                    $hasAbout ||
                    $hasContact ||
                    $hasPrivacy ||
                    $hasTerms
                )
                    <div class="studio-footer-column">
                        <h3>
                            {{ __('Informatie') }}
                        </h3>

                        <div class="studio-footer-links">
                            @if ($hasAbout)
                                <a href="{{ route('about') }}">
                                    Over ons
                                </a>
                            @endif

                            @if ($hasContact)
                                <a href="{{ route('contact') }}">
                                    Contact
                                </a>
                            @endif

                            @if ($hasPrivacy)
                                <a href="{{ route('privacy') }}">
                                    Privacy
                                </a>
                            @endif

                            @if ($hasTerms)
                                <a href="{{ route('terms') }}">
                                    Voorwaarden
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="studio-footer-bottom">
                <span>
                    © {{ date('Y') }} Mashal Studio
                </span>

                <div class="studio-footer-bottom-links">
                    @if ($hasPrivacy)
                        <a href="{{ route('privacy') }}">
                            Privacy
                        </a>
                    @endif

                    @if ($hasTerms)
                        <a href="{{ route('terms') }}">
                            Voorwaarden
                        </a>
                    @endif

                    <span>
                        Private image & AI workspace
                    </span>

                    <span>
                        JPG · PNG · WEBP
                    </span>
                </div>
            </div>
        </div>
    </footer>


    <div
        class="studio-toast-region"
        id="studioToastRegion"
        aria-live="polite"
        aria-atomic="false"
    ></div>


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


                let mobileMenuScrollY =
                    0;

                const visualViewport =
                    window.visualViewport ||
                    null;

                function updateVisualViewport() {
                    const height =
                        visualViewport?.height ||
                        window.innerHeight;

                    document.documentElement.style.setProperty(
                        '--studio-visual-height',
                        height + 'px'
                    );
                }

                updateVisualViewport();

                visualViewport?.addEventListener(
                    'resize',
                    updateVisualViewport
                );

                /*
                |--------------------------------------------------------------------------
                | Header state
                |--------------------------------------------------------------------------
                */

                function updateHeader() {
                    if (!header) {
                        return;
                    }

                    header.classList.toggle(
                        'is-scrolled',
                        window.scrollY > 12
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Scroll progress
                |--------------------------------------------------------------------------
                */

                function updateProgress() {
                    // Ultra Smooth: progressbar staat uit om layout-metingen
                    // tijdens scrollen volledig te vermijden.
                    return;

                    if (!progressBar) {
                        return;
                    }

                    const documentElement =
                        document.documentElement;

                    const scrollTop =
                        window.scrollY ||
                        documentElement.scrollTop;

                    const scrollable =
                        documentElement.scrollHeight -
                        window.innerHeight;

                    const progress =
                        scrollable > 0
                            ? Math.min(
                                100,
                                Math.max(
                                    0,
                                    (scrollTop / scrollable) * 100
                                )
                            )
                            : 0;

                    progressBar.style.transform =
                        'scaleX(' + (progress / 100) + ')';
                }

                /*
                |--------------------------------------------------------------------------
                | Mobile drawer
                |--------------------------------------------------------------------------
                */

                function setMobileMenu(
                    open
                ) {
                    const nextOpen =
                        Boolean(open);

                    if (
                        nextOpen === mobileMenuOpen
                    ) {
                        return;
                    }

                    mobileMenuOpen =
                        nextOpen;

                    if (menuToggle) {
                        menuToggle.setAttribute(
                            'aria-expanded',
                            mobileMenuOpen
                                ? 'true'
                                : 'false'
                        );

                        menuToggle.setAttribute(
                            'aria-label',
                            mobileMenuOpen
                                ? 'Menu sluiten'
                                : 'Menu openen'
                        );
                    }

                    if (mobileDrawer) {
                        mobileDrawer.classList.toggle(
                            'is-open',
                            mobileMenuOpen
                        );

                        mobileDrawer.setAttribute(
                            'aria-hidden',
                            mobileMenuOpen
                                ? 'false'
                                : 'true'
                        );
                    }

                    if (mobileOverlay) {
                        mobileOverlay.classList.toggle(
                            'is-open',
                            mobileMenuOpen
                        );

                        mobileOverlay.setAttribute(
                            'aria-hidden',
                            mobileMenuOpen
                                ? 'false'
                                : 'true'
                        );
                    }

                    if (mobileMenuOpen) {
                        mobileMenuScrollY =
                            window.scrollY ||
                            document.documentElement.scrollTop ||
                            0;

                        body.style.position =
                            'fixed';

                        body.style.top =
                            '-' +
                            mobileMenuScrollY +
                            'px';

                        body.style.left =
                            '0';

                        body.style.right =
                            '0';

                        body.style.width =
                            '100%';

                        body.classList.add(
                            'studio-menu-open'
                        );

                        window.setTimeout(
                            function () {
                                mobileClose?.focus({
                                    preventScroll: true
                                });
                            },
                            60
                        );
                    } else {
                        body.classList.remove(
                            'studio-menu-open'
                        );

                        body.style.position =
                            '';

                        body.style.top =
                            '';

                        body.style.left =
                            '';

                        body.style.right =
                            '';

                        body.style.width =
                            '';

                        window.scrollTo(
                            0,
                            mobileMenuScrollY
                        );
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Account menu
                |--------------------------------------------------------------------------
                */

                function setAccountMenu(
                    open
                ) {
                    accountMenuOpen =
                        Boolean(open);

                    if (accountTrigger) {
                        accountTrigger.setAttribute(
                            'aria-expanded',
                            accountMenuOpen
                                ? 'true'
                                : 'false'
                        );
                    }

                    if (accountMenu) {
                        accountMenu.classList.toggle(
                            'is-open',
                            accountMenuOpen
                        );

                        accountMenu.setAttribute(
                            'aria-hidden',
                            accountMenuOpen
                                ? 'false'
                                : 'true'
                        );
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Events
                |--------------------------------------------------------------------------
                */

                if (menuToggle) {
                    menuToggle.addEventListener(
                        'click',
                        function (event) {
                            event.preventDefault();
                            event.stopPropagation();

                            setAccountMenu(false);

                            setMobileMenu(
                                !mobileMenuOpen
                            );
                        }
                    );
                }

                if (mobileClose) {
                    mobileClose.addEventListener(
                        'click',
                        function (event) {
                            event.preventDefault();
                            event.stopPropagation();

                            setMobileMenu(false);

                            menuToggle?.focus({
                                preventScroll: true
                            });
                        }
                    );
                }

                if (mobileOverlay) {
                    mobileOverlay.addEventListener(
                        'click',
                        function (event) {
                            event.preventDefault();

                            setMobileMenu(false);
                        }
                    );
                }

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
                        if (event.key !== 'Escape') {
                            return;
                        }

                        setMobileMenu(false);
                        setAccountMenu(false);
                    }
                );

                if (mobileDrawer) {
                    mobileDrawer
                        .querySelectorAll('a[href]')
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
                }

                /*
                |--------------------------------------------------------------------------
                | Flash close
                |--------------------------------------------------------------------------
                */

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

                                    flash.style.opacity =
                                        '0';

                                    flash.style.transform =
                                        'translateY(-6px)';

                                    window.setTimeout(
                                        function () {
                                            flash.remove();
                                        },
                                        180
                                    );
                                }
                            );
                        }
                    );

                /*
                |--------------------------------------------------------------------------
                | Automatic reveal support
                |--------------------------------------------------------------------------
                */

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
                                        if (!entry.isIntersecting) {
                                            return;
                                        }

                                        entry.target
                                            .classList
                                            .add('is-visible');

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

                /*
                |--------------------------------------------------------------------------
                | Safe smooth anchor navigation
                |--------------------------------------------------------------------------
                */

                document
                    .querySelectorAll(
                        'a[href*="#"]'
                    )
                    .forEach(
                        function (anchor) {
                            anchor.addEventListener(
                                'click',
                                function (event) {
                                    const href =
                                        anchor.getAttribute(
                                            'href'
                                        );

                                    if (
                                        !href ||
                                        href === '#'
                                    ) {
                                        return;
                                    }

                                    let hash =
                                        '';

                                    try {
                                        const url =
                                            new URL(
                                                anchor.href,
                                                window.location.href
                                            );

                                        if (
                                            url.pathname !==
                                            window.location.pathname
                                        ) {
                                            return;
                                        }

                                        hash =
                                            url.hash;
                                    } catch (error) {
                                        return;
                                    }

                                    if (
                                        !hash ||
                                        hash === '#'
                                    ) {
                                        return;
                                    }

                                    const target =
                                        document.querySelector(
                                            hash
                                        );

                                    if (!target) {
                                        return;
                                    }

                                    event.preventDefault();

                                    target.scrollIntoView({
                                        behavior:
                                            'smooth',
                                        block:
                                            'start'
                                    });

                                    setMobileMenu(false);
                                }
                            );
                        }
                    );

                /*
                |--------------------------------------------------------------------------
                | Submit loading state
                |--------------------------------------------------------------------------
                */

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
                                        submit.dataset
                                            .noLoading === 'true'
                                    ) {
                                        return;
                                    }

                                    submit.classList.add(
                                        'is-loading'
                                    );
                                }
                            );
                        }
                    );

                /*
                |--------------------------------------------------------------------------
                | Initial state
                |--------------------------------------------------------------------------
                */

                updateHeader();

                let scrollFramePending =
                    false;

                function scheduleScrollUpdate() {
                    if (scrollFramePending) {
                        return;
                    }

                    scrollFramePending =
                        true;

                    window.requestAnimationFrame(
                        function () {
                            updateHeader();

                            scrollFramePending =
                                false;
                        }
                    );
                }

                window.addEventListener(
                    'scroll',
                    scheduleScrollUpdate,
                    {
                        passive:
                            true
                    }
                );

                window.addEventListener(
                    'resize',
                    function () {
                        updateVisualViewport();

                        if (
                            window.innerWidth >
                            1120
                        ) {
                            setMobileMenu(false);
                        }
                    },
                    {
                        passive:
                            true
                    }
                );

                window.addEventListener(
                    'orientationchange',
                    function () {
                        window.setTimeout(
                            function () {
                                updateVisualViewport();
                                setMobileMenu(false);
                                setAccountMenu(false);
                            },
                            120
                        );
                    },
                    {
                        passive:
                            true
                    }
                );
            }
        );
    </script>


    <script id="mashal-expert-navbar-js">
        document.addEventListener(
            'DOMContentLoaded',
            function () {
                const body =
                    document.body;

                const trigger =
                    document.getElementById(
                        'studioCommandTrigger'
                    );

                const palette =
                    document.getElementById(
                        'studioCommandPalette'
                    );

                const overlay =
                    document.getElementById(
                        'studioCommandOverlay'
                    );

                const closeButton =
                    document.getElementById(
                        'studioCommandClose'
                    );

                const input =
                    document.getElementById(
                        'studioCommandInput'
                    );

                const commandItems =
                    [
                        ...document.querySelectorAll(
                            '.expert-command-item'
                        )
                    ];

                let commandOpen =
                    false;

                let selectedIndex =
                    0;

                function visibleItems() {
                    return commandItems.filter(
                        function (item) {
                            return !item.hidden;
                        }
                    );
                }

                function updateSelection() {
                    const items =
                        visibleItems();

                    if (!items.length) {
                        return;
                    }

                    selectedIndex =
                        Math.max(
                            0,
                            Math.min(
                                selectedIndex,
                                items.length - 1
                            )
                        );

                    commandItems.forEach(
                        function (item) {
                            item.classList.remove(
                                'is-selected'
                            );
                        }
                    );

                    items[
                        selectedIndex
                    ]?.classList.add(
                        'is-selected'
                    );

                    items[
                        selectedIndex
                    ]?.scrollIntoView({
                        block:
                            'nearest'
                    });
                }

                function filterCommands() {
                    const query =
                        (
                            input?.value ||
                            ''
                        )
                        .trim()
                        .toLowerCase();

                    commandItems.forEach(
                        function (item) {
                            const search =
                                (
                                    item.getAttribute(
                                        'data-command-search'
                                    ) ||
                                    item.textContent ||
                                    ''
                                )
                                .toLowerCase();

                            item.hidden =
                                Boolean(
                                    query &&
                                    !search.includes(
                                        query
                                    )
                                );
                        }
                    );

                    selectedIndex =
                        0;

                    updateSelection();
                }

                function setCommandOpen(open) {
                    commandOpen =
                        Boolean(open);

                    trigger?.setAttribute(
                        'aria-expanded',
                        commandOpen
                            ? 'true'
                            : 'false'
                    );

                    palette?.classList.toggle(
                        'is-open',
                        commandOpen
                    );

                    overlay?.classList.toggle(
                        'is-open',
                        commandOpen
                    );

                    palette?.setAttribute(
                        'aria-hidden',
                        commandOpen
                            ? 'false'
                            : 'true'
                    );

                    overlay?.setAttribute(
                        'aria-hidden',
                        commandOpen
                            ? 'false'
                            : 'true'
                    );

                    body.classList.toggle(
                        'expert-command-open',
                        commandOpen
                    );

                    if (commandOpen) {
                        window.setTimeout(
                            function () {
                                input?.focus();

                                selectedIndex =
                                    0;

                                updateSelection();
                            },
                            40
                        );
                    } else {
                        if (input) {
                            input.value =
                                '';
                        }

                        filterCommands();

                        trigger?.focus();
                    }
                }

                trigger?.addEventListener(
                    'click',
                    function () {
                        setCommandOpen(
                            !commandOpen
                        );
                    }
                );

                closeButton?.addEventListener(
                    'click',
                    function () {
                        setCommandOpen(
                            false
                        );
                    }
                );

                overlay?.addEventListener(
                    'click',
                    function () {
                        setCommandOpen(
                            false
                        );
                    }
                );

                input?.addEventListener(
                    'input',
                    filterCommands
                );

                commandItems.forEach(
                    function (item) {
                        item.addEventListener(
                            'pointerenter',
                            function () {
                                const items =
                                    visibleItems();

                                selectedIndex =
                                    Math.max(
                                        0,
                                        items.indexOf(
                                            item
                                        )
                                    );

                                updateSelection();
                            }
                        );

                        item.addEventListener(
                            'click',
                            function () {
                                setCommandOpen(
                                    false
                                );
                            }
                        );
                    }
                );

                document.addEventListener(
                    'keydown',
                    function (event) {
                        const active =
                            document.activeElement;

                        const typing =
                            active &&
                            (
                                active.tagName === 'INPUT' ||
                                active.tagName === 'TEXTAREA' ||
                                active.isContentEditable
                            );

                        if (
                            (
                                event.metaKey ||
                                event.ctrlKey
                            ) &&
                            event.key.toLowerCase() === 'k'
                        ) {
                            event.preventDefault();

                            setCommandOpen(
                                !commandOpen
                            );

                            return;
                        }

                        if (
                            !commandOpen &&
                            !typing &&
                            event.key === '/'
                        ) {
                            event.preventDefault();

                            setCommandOpen(
                                true
                            );

                            return;
                        }

                        if (!commandOpen) {
                            return;
                        }

                        if (event.key === 'Escape') {
                            event.preventDefault();

                            setCommandOpen(
                                false
                            );

                            return;
                        }

                        if (event.key === 'ArrowDown') {
                            event.preventDefault();

                            const items =
                                visibleItems();

                            if (!items.length) {
                                return;
                            }

                            selectedIndex =
                                (
                                    selectedIndex +
                                    1
                                ) %
                                items.length;

                            updateSelection();

                            return;
                        }

                        if (event.key === 'ArrowUp') {
                            event.preventDefault();

                            const items =
                                visibleItems();

                            if (!items.length) {
                                return;
                            }

                            selectedIndex =
                                (
                                    selectedIndex -
                                    1 +
                                    items.length
                                ) %
                                items.length;

                            updateSelection();

                            return;
                        }

                        if (event.key === 'Enter') {
                            const items =
                                visibleItems();

                            const selected =
                                items[
                                    selectedIndex
                                ];

                            if (selected) {
                                event.preventDefault();

                                selected.click();
                            }
                        }
                    }
                );

                /*
                 * Desktop magnetic polish.
                 */
                const reducedMotion =
                    window.matchMedia(
                        '(prefers-reduced-motion: reduce)'
                    ).matches;

                const finePointer =
                    window.matchMedia(
                        '(pointer: fine)'
                    ).matches;

                if (
                    !reducedMotion &&
                    finePointer
                ) {
                    document
                        .querySelectorAll(
                            '.expert-nav-link, ' +
                            '.expert-new-project, ' +
                            '.expert-auth-link, ' +
                            '.expert-command-trigger'
                        )
                        .forEach(
                            function (element) {
                                let currentX =
                                    0;

                                let currentY =
                                    0;

                                let targetX =
                                    0;

                                let targetY =
                                    0;

                                let frame =
                                    null;

                                function animate() {
                                    currentX +=
                                        (
                                            targetX -
                                            currentX
                                        ) *
                                        .14;

                                    currentY +=
                                        (
                                            targetY -
                                            currentY
                                        ) *
                                        .14;

                                    element.style.transform =
                                        'translate3d(' +
                                        currentX +
                                        'px,' +
                                        currentY +
                                        'px,0)';

                                    if (
                                        Math.abs(
                                            targetX -
                                            currentX
                                        ) >
                                        .05 ||
                                        Math.abs(
                                            targetY -
                                            currentY
                                        ) >
                                        .05
                                    ) {
                                        frame =
                                            window.requestAnimationFrame(
                                                animate
                                            );
                                    } else {
                                        frame =
                                            null;
                                    }
                                }

                                function start() {
                                    if (frame) {
                                        return;
                                    }

                                    frame =
                                        window.requestAnimationFrame(
                                            animate
                                        );
                                }

                                element.addEventListener(
                                    'pointermove',
                                    function (event) {
                                        const rect =
                                            element.getBoundingClientRect();

                                        targetX =
                                            (
                                                event.clientX -
                                                (
                                                    rect.left +
                                                    rect.width /
                                                    2
                                                )
                                            ) *
                                            .05;

                                        targetY =
                                            (
                                                event.clientY -
                                                (
                                                    rect.top +
                                                    rect.height /
                                                    2
                                                )
                                            ) *
                                            .05;

                                        start();
                                    }
                                );

                                element.addEventListener(
                                    'pointerleave',
                                    function () {
                                        targetX =
                                            0;

                                        targetY =
                                            0;

                                        start();
                                    }
                                );
                            }
                        );
                }
            }
        );
    </script>


    @stack('scripts')

    {{-- 
    |--------------------------------------------------------------------------
    | Device login approval
    |--------------------------------------------------------------------------
    |
    | Alleen tonen aan gebruikers die al zijn ingelogd op dit apparaat.
    | De Route::has-controle voorkomt dat de hele layout crasht tijdens een
    | gedeeltelijke deploy waarin de nieuwe approval-routes nog niet actief
    | zijn.
    |
    --}}

    @auth
        @if (
            \Illuminate\Support\Facades\Route::has('login-approval.pending')
            && \Illuminate\Support\Facades\Route::has('login-approval.respond')
        )
            @include('site.partials.login-approval-prompt')
        @endif
    @endauth

    @include('site.partials.guest-chat')

    @include('partials.auth-success-overlay')
</body>
</html>

