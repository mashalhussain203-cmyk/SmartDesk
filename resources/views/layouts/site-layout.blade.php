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
        $hasAccount = \Illuminate\Support\Facades\Route::has('account');
        $hasSecurity = \Illuminate\Support\Facades\Route::has('security.index');
        $hasAdmin = \Illuminate\Support\Facades\Route::has('admin.dashboard');

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

        $layoutImageCount = null;

        if (
            $layoutUser &&
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
                #e3b36b;

            --studio-gold-light:
                #f3d69a;

            --studio-gold-deep:
                #b77d38;

            --studio-gold-soft:
                rgba(227, 179, 107, .08);

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
                1px solid rgba(227, 179, 107, .30);

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
                2px solid rgba(243, 214, 154, .82);

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
                    rgba(227, 179, 107, .075),
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
                blur(95px);

            opacity:
                .16;

            animation:
                studioOrbDrift 18s ease-in-out infinite alternate;
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
                rgba(227, 179, 107, .18);
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
                0;

            height:
                100%;

            border-radius:
                999px;

            background:
                linear-gradient(
                    90deg,
                    var(--studio-gold-deep),
                    var(--studio-gold-light),
                    #fff0c5
                );

            box-shadow:
                0 0 22px rgba(227, 179, 107, .38);

            transition:
                width .06s linear;
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
                blur(24px)
                saturate(135%);

            -webkit-backdrop-filter:
                blur(24px)
                saturate(135%);

            transition:
                background .24s ease,
                border-color .24s ease,
                box-shadow .24s ease;
        }

        .studio-header.is-scrolled {
            border-color:
                rgba(227, 179, 107, .11);

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
                1px solid rgba(227, 179, 107, .19);

            border-radius:
                14px;

            color:
                var(--studio-gold-light);

            background:
                linear-gradient(
                    145deg,
                    rgba(227, 179, 107, .13),
                    rgba(227, 179, 107, .025)
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
                rgba(227, 179, 107, .11);

            color:
                #e3bd81;

            background:
                rgba(227, 179, 107, .045);
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
                    rgba(227, 179, 107, .025)
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
                    rgba(227, 179, 107, .04)
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
                    rgba(227, 179, 107, .045)
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
                1px solid rgba(227, 179, 107, .14);

            border-radius:
                999px;

            color:
                #d6aa69;

            background:
                rgba(227, 179, 107, .055);

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
                rgba(227, 179, 107, .16);

            color:
                #eceef0;

            background:
                rgba(227, 179, 107, .035);
        }

        .studio-action-link.primary,
        .studio-action-button.primary {
            border-color:
                rgba(227, 179, 107, .18);

            color:
                #171009;

            background:
                linear-gradient(
                    135deg,
                    var(--studio-gold-light),
                    #d39a4f
                );

            box-shadow:
                0 14px 35px rgba(227, 179, 107, .13);
        }

        .studio-action-link.primary:hover,
        .studio-action-button.primary:hover {
            color:
                #171009;

            background:
                linear-gradient(
                    135deg,
                    #f7dfab,
                    #dcaa64
                );

            box-shadow:
                0 18px 45px rgba(227, 179, 107, .20);
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
                rgba(227, 179, 107, .18);

            background:
                rgba(227, 179, 107, .035);

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
                1px solid rgba(227, 179, 107, .15);

            border-radius:
                50%;

            color:
                #e4ba79;

            background:
                linear-gradient(
                    145deg,
                    rgba(227, 179, 107, .14),
                    rgba(227, 179, 107, .035)
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
                    rgba(227, 179, 107, .045),
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
                1px solid rgba(227, 179, 107, .15);

            border-radius:
                15px;

            color:
                #e6bc7b;

            background:
                rgba(227, 179, 107, .06);

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
                rgba(227, 179, 107, .09);

            color:
                #d6d9dd;

            background:
                rgba(227, 179, 107, .035);
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
        | Mobile toggle
        |--------------------------------------------------------------------------
        */

        .studio-menu-toggle {
            width:
                43px;

            height:
                43px;

            display:
                none;

            place-items:
                center;

            border:
                1px solid rgba(255, 255, 255, .075);

            border-radius:
                12px;

            color:
                #fff;

            background:
                rgba(255, 255, 255, .018);

            cursor:
                pointer;
        }

        .studio-menu-lines,
        .studio-menu-lines::before,
        .studio-menu-lines::after {
            width:
                18px;

            height:
                1.5px;

            display:
                block;

            border-radius:
                999px;

            background:
                currentColor;

            transition:
                transform .2s ease,
                opacity .2s ease,
                top .2s ease;
        }

        .studio-menu-lines {
            position:
                relative;
        }

        .studio-menu-lines::before,
        .studio-menu-lines::after {
            content:
                "";

            position:
                absolute;

            left:
                0;
        }

        .studio-menu-lines::before {
            top:
                -6px;
        }

        .studio-menu-lines::after {
            top:
                6px;
        }

        .studio-menu-toggle[aria-expanded="true"]
        .studio-menu-lines {
            background:
                transparent;
        }

        .studio-menu-toggle[aria-expanded="true"]
        .studio-menu-lines::before {
            top:
                0;

            transform:
                rotate(45deg);
        }

        .studio-menu-toggle[aria-expanded="true"]
        .studio-menu-lines::after {
            top:
                0;

            transform:
                rotate(-45deg);
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

            transition:
                opacity .2s ease;
        }

        .studio-mobile-overlay.is-open {
            opacity:
                1;
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
                translateX(105%);

            transition:
                transform .25s cubic-bezier(.2, .7, .2, 1);
        }

        .studio-mobile-drawer.is-open {
            transform:
                translateX(0);
        }

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
                1px solid rgba(227, 179, 107, .09);

            border-radius:
                15px;

            background:
                rgba(227, 179, 107, .025);
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
                rgba(227, 179, 107, .10);

            color:
                #dfb979;

            background:
                rgba(227, 179, 107, .035);
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
                calc(100vh - var(--studio-header-height));
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
        | Quick upload
        |--------------------------------------------------------------------------
        */

        .studio-quick-upload {
            position:
                fixed;

            z-index:
                800;

            right:
                20px;

            bottom:
                20px;

            min-height:
                50px;

            padding:
                0 18px 0 12px;

            display:
                inline-flex;

            align-items:
                center;

            gap:
                10px;

            border:
                1px solid rgba(227, 179, 107, .20);

            border-radius:
                999px;

            color:
                #181109;

            background:
                linear-gradient(
                    135deg,
                    var(--studio-gold-light),
                    #d19a51
                );

            box-shadow:
                0 20px 55px rgba(0, 0, 0, .35),
                0 10px 30px rgba(227, 179, 107, .14);

            text-decoration:
                none;

            font-size:
                9px;

            font-weight:
                950;

            transform:
                translateY(0);

            transition:
                transform .2s ease,
                box-shadow .2s ease,
                opacity .2s ease;
        }

        .studio-quick-upload:hover {
            transform:
                translateY(-3px);

            box-shadow:
                0 26px 66px rgba(0, 0, 0, .38),
                0 14px 38px rgba(227, 179, 107, .19);
        }

        .studio-quick-upload-icon {
            width:
                30px;

            height:
                30px;

            display:
                grid;

            place-items:
                center;

            border:
                1px solid rgba(24, 17, 9, .11);

            border-radius:
                50%;

            background:
                rgba(255, 255, 255, .19);

            font-size:
                13px;
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

            border-top:
                1px solid rgba(255, 255, 255, .055);

            background:
                linear-gradient(
                    180deg,
                    rgba(10, 12, 16, .96),
                    #060709
                );
        }

        .studio-footer-main {
            padding:
                64px 0 42px;

            display:
                grid;

            grid-template-columns:
                minmax(0, 1.3fr)
                repeat(3, minmax(140px, .7fr));

            gap:
                50px;
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
                #f0f1f2;

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
        }

        .studio-footer-about p {
            margin:
                18px 0 0;

            color:
                #676e78;

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
                #69717a;

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
                var(--studio-success);

            box-shadow:
                0 0 0 5px rgba(103, 217, 144, .05);
        }

        .studio-footer-column h3 {
            margin:
                0 0 15px;

            color:
                #c7cbd1;

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
                #6f7680;

            text-decoration:
                none;

            font-size:
                9px;

            font-weight:
                800;

            transition:
                color .2s ease,
                transform .2s ease;
        }

        .studio-footer-links a:hover {
            color:
                #cfa566;

            transform:
                translateX(2px);
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
                1px solid rgba(255, 255, 255, .045);

            color:
                #4f5660;

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
                #565d67;

            text-decoration:
                none;
        }

        .studio-footer-bottom-links a:hover {
            color:
                #b99257;
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
                    2 / -1;
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

            .studio-quick-upload {
                right:
                    10px;

                bottom:
                    10px;

                min-height:
                    48px;

                padding-right:
                    14px;
            }

            .studio-quick-upload span:last-child {
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
            .studio-quick-upload,
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
        class="studio-header"
        id="studioHeader"
    >
        <div class="studio-shell">
            <div class="studio-nav">
                {{-- Brand --}}
                <a
                    class="studio-brand"
                    href="{{ route('home') }}"
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

                {{-- Desktop navigation --}}
                <nav
                    class="studio-nav-center"
                    aria-label="Hoofdnavigatie"
                >
                    <a
                        class="studio-nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                        href="{{ route('home') }}"
                    >
                        <span class="studio-nav-icon" aria-hidden="true">
                            ◇
                        </span>

                        Studio
                    </a>

                    @auth
                        @if ($hasImagesIndex)
                            <a
                                class="studio-nav-link {{ request()->routeIs('images.*') ? 'active' : '' }}"
                                href="{{ route('images.index') }}"
                            >
                                <span class="studio-nav-icon" aria-hidden="true">
                                    ▦
                                </span>

                                Mijn afbeeldingen

                                @if ($layoutImageCount !== null)
                                    <span class="studio-nav-count">
                                        {{ min($layoutImageCount, 999) }}
                                    </span>
                                @endif
                            </a>
                        @endif

                        @if ($hasAiChat)
                            <a
                                class="studio-nav-link ai-link {{ request()->routeIs('ai.chat*') ? 'active' : '' }}"
                                href="{{ route('ai.chat') }}"
                            >
                                <span class="studio-nav-icon" aria-hidden="true">
                                    ✦
                                </span>

                                Mashal AI

                                <span class="studio-ai-badge">
                                    AI
                                </span>
                            </a>
                        @endif

                        @if ($hasAccount)
                            <a
                                class="studio-nav-link {{ request()->routeIs('account') ? 'active' : '' }}"
                                href="{{ route('account') }}"
                            >
                                <span class="studio-nav-icon" aria-hidden="true">
                                    ○
                                </span>

                                Account
                            </a>
                        @endif

                        @if ($hasSecurity)
                            <a
                                class="studio-nav-link {{ request()->routeIs('security.*') ? 'active' : '' }}"
                                href="{{ route('security.index') }}"
                            >
                                <span class="studio-nav-icon" aria-hidden="true">
                                    ◈
                                </span>

                                Beveiliging
                            </a>
                        @endif
                    @endauth

                    <a
                        class="studio-nav-link"
                        href="{{ route('home') }}#upload"
                    >
                        <span class="studio-nav-icon" aria-hidden="true">
                            ↑
                        </span>

                        Upload
                    </a>
                </nav>

                {{-- Right actions --}}
                <div class="studio-nav-actions">
                    @guest
                        <a
                            class="studio-action-link desktop-secondary"
                            href="{{ route('login') }}"
                        >
                            Inloggen
                        </a>

                        <a
                            class="studio-action-link primary"
                            href="{{ route('register') }}"
                        >
                            Gratis starten
                        </a>
                    @else
                        <div class="studio-account-wrap">
                            <button
                                class="studio-account-trigger"
                                id="studioAccountTrigger"
                                type="button"
                                aria-expanded="false"
                                aria-controls="studioAccountMenu"
                            >
                                <span class="studio-account-avatar">
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

                                    @if ($hasAiChat)
                                        <a
                                            class="studio-account-menu-link"
                                            href="{{ route('ai.chat') }}"
                                        >
                                            <span>
                                                Mashal AI
                                            </span>

                                            <span aria-hidden="true">
                                                ✦
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
                                                Loginbeveiliging
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
                                                Admin
                                            </span>

                                            <span>
                                                →
                                            </span>
                                        </a>
                                    @endif

                                    <div class="studio-account-menu-divider"></div>

                                    <form
                                        class="studio-account-menu-form"
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

                @if ($hasAiChat)
                    <a
                        class="studio-mobile-link {{ request()->routeIs('ai.chat*') ? 'active' : '' }}"
                        href="{{ route('ai.chat') }}"
                    >
                        <span>
                            Mashal AI
                        </span>

                        <span aria-hidden="true">
                            ✦ AI
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
                            Loginbeveiliging
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
                            Admin
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
                <a
                    class="studio-action-link"
                    href="{{ route('login') }}"
                >
                    Inloggen
                </a>

                <a
                    class="studio-action-link primary"
                    href="{{ route('register') }}"
                >
                    Gratis account maken
                </a>
            @else
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


    @unless (request()->routeIs('home'))
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
    @endunless



    <footer class="studio-footer">
        <div class="studio-shell">
            <div class="studio-footer-main">
                <div class="studio-footer-about">
                    <a
                        class="studio-footer-logo"
                        href="{{ route('home') }}"
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
                        Een persoonlijke omgeving voor afbeeldingen en AI:
                        upload, bewerk en bewaar je projecten vanuit je eigen
                        workspace en gebruik Mashal AI als aparte assistent
                        onder hetzelfde account.
                    </p>

                    <div class="studio-footer-trust">
                        Persoonlijke accountomgeving
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

                        <a href="{{ route('home') }}#upload">
                            Upload afbeelding
                        </a>

                        @auth
                            @if ($hasImagesIndex)
                                <a href="{{ route('images.index') }}">
                                    Mijn afbeeldingen
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
                                Inloggen
                            </a>

                            <a href="{{ route('register') }}">
                                Registreren
                            </a>
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
                        <a href="{{ route('home') }}#upload">
                            Nieuw project
                        </a>

                        @auth
                            @if ($hasImagesIndex)
                                <a href="{{ route('images.index') }}">
                                    Projectbibliotheek
                                </a>
                            @endif

                            @if ($hasAiChat)
                                <a href="{{ route('ai.chat') }}">
                                    AI-assistent
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

                    progressBar.style.width =
                        progress + '%';
                }

                /*
                |--------------------------------------------------------------------------
                | Mobile drawer
                |--------------------------------------------------------------------------
                */

                function setMobileMenu(
                    open
                ) {
                    mobileMenuOpen =
                        Boolean(open);

                    if (menuToggle) {
                        menuToggle.setAttribute(
                            'aria-expanded',
                            mobileMenuOpen
                                ? 'true'
                                : 'false'
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

                    body.classList.toggle(
                        'studio-menu-open',
                        mobileMenuOpen
                    );
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
                        function () {
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
                        function () {
                            setMobileMenu(false);
                        }
                    );
                }

                if (mobileOverlay) {
                    mobileOverlay.addEventListener(
                        'click',
                        function () {
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
                        .querySelectorAll('a')
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
                updateProgress();

                window.addEventListener(
                    'scroll',
                    function () {
                        updateHeader();
                        updateProgress();
                    },
                    {
                        passive:
                            true
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
