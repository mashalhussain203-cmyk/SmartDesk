<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <meta
        name="robots"
        content="noindex, nofollow"
    >

    <meta
        name="color-scheme"
        content="dark"
    >

    <meta
        name="theme-color"
        content="#07080b"
    >

    <title>
        @yield('title', 'Mashal Studio Admin')
    </title>

    @php
        $adminUser = auth()->user();
        $adminIsLoggedIn = auth()->check();

        $adminIsAllowed =
            $adminIsLoggedIn &&
            (bool) ($adminUser->is_admin ?? false);

        $hasAdminDashboard =
            \Illuminate\Support\Facades\Route::has('admin.dashboard');

        $hasUsersIndex =
            \Illuminate\Support\Facades\Route::has('users.index');

        $hasUsersCreate =
            \Illuminate\Support\Facades\Route::has('users.create');

        $hasImagesIndex =
            \Illuminate\Support\Facades\Route::has('images.index');

        $hasAccount =
            \Illuminate\Support\Facades\Route::has('account');

        $hasSecurity =
            \Illuminate\Support\Facades\Route::has('security.index');

        $hasHome =
            \Illuminate\Support\Facades\Route::has('home');

        $hasLogout =
            \Illuminate\Support\Facades\Route::has('logout');

        $hasLogin =
            \Illuminate\Support\Facades\Route::has('login');

        $adminInitials = 'M';
        $adminAvatarUrl = null;
        $adminImageCount = null;
        $adminUserCount = null;

        if ($adminUser) {
            if (
                method_exists($adminUser, 'initials') &&
                $adminUser->initials()
            ) {
                $adminInitials = (string) $adminUser->initials();
            } else {
                $parts = preg_split(
                    '/\s+/',
                    trim((string) $adminUser->name)
                ) ?: [];

                $adminInitials = '';

                foreach (array_slice($parts, 0, 2) as $part) {
                    $adminInitials .= mb_strtoupper(
                        mb_substr($part, 0, 1)
                    );
                }

                if ($adminInitials === '') {
                    $adminInitials = 'M';
                }
            }

            if (method_exists($adminUser, 'avatarUrl')) {
                $adminAvatarUrl = $adminUser->avatarUrl();
            }
        }

        if (
            $adminIsAllowed &&
            class_exists(\App\Models\Image::class)
        ) {
            try {
                $adminImageCount =
                    \App\Models\Image::query()->count();
            } catch (\Throwable $exception) {
                $adminImageCount = null;
            }
        }

        if (
            $adminIsAllowed &&
            class_exists(\App\Models\User::class)
        ) {
            try {
                $adminUserCount =
                    \App\Models\User::query()->count();
            } catch (\Throwable $exception) {
                $adminUserCount = null;
            }
        }

        $adminCurrentRoute =
            request()->route()?->getName();

        $adminCurrentPath =
            request()->path();

        $adminDashboardUrl =
            $hasAdminDashboard
                ? route('admin.dashboard')
                : (
                    $hasHome
                        ? route('home')
                        : '#'
                );
    @endphp


    <style>
        :root {
            color-scheme: dark;

            --admin-bg: #07080b;
            --admin-bg-soft: #0a0c10;

            --admin-panel: #0e1116;
            --admin-panel-2: #12161d;
            --admin-panel-3: #171c24;
            --admin-panel-hover: #191e25;

            --admin-text: #f6f7f8;
            --admin-text-soft: #d6dae0;

            --admin-muted: #8a929c;
            --admin-muted-2: #646c76;
            --admin-muted-3: #4a515a;

            --admin-line:
                rgba(255, 255, 255, .07);

            --admin-line-strong:
                rgba(255, 255, 255, .12);

            --admin-gold: #8f82ff;
            --admin-gold-light: #a99fff;
            --admin-gold-deep: #5f7dff;

            --admin-gold-soft:
                rgba(122, 108, 255, .07);

            --admin-blue: #78b7ff;
            --admin-green: #69d894;
            --admin-red: #f08383;
            --admin-warning: #edc270;

            --admin-sidebar-collapsed: 84px;
            --admin-sidebar-expanded: 286px;

            --admin-radius-sm: 12px;
            --admin-radius: 18px;
            --admin-radius-lg: 25px;

            --admin-shadow:
                0 32px 90px rgba(0, 0, 0, .34);

            --admin-shadow-soft:
                0 18px 48px rgba(0, 0, 0, .22);

            --admin-transition:
                .34s cubic-bezier(.2, .8, .2, 1);

            --admin-font:
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

            background: var(--admin-bg);
        }


        body {
            min-width: 320px;
            min-height: 100vh;

            margin: 0;

            overflow-x: hidden;

            color: var(--admin-text);

            background:
                radial-gradient(
                    circle at 84% 6%,
                    rgba(122, 108, 255, .055),
                    transparent 26rem
                ),
                radial-gradient(
                    circle at 16% 86%,
                    rgba(120, 183, 255, .025),
                    transparent 28rem
                ),
                linear-gradient(
                    180deg,
                    #08090c,
                    #07080b
                );

            font-family: var(--admin-font);

            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }


        body.admin-mobile-open {
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


        img {
            max-width: 100%;
            display: block;
        }


        ::selection {
            color: #171009;
            background: var(--admin-gold-light);
        }


        :focus-visible {
            outline: 2px solid rgba(169, 159, 255, .72);
            outline-offset: 3px;
        }


        /*
        |--------------------------------------------------------------------------
        | SKIP LINK
        |--------------------------------------------------------------------------
        */

        .admin-skip-link {
            position: fixed;

            z-index: 5000;

            top: 12px;
            left: 12px;

            padding: 10px 14px;

            transform: translateY(-180%);

            border-radius: 10px;

            color: #171009;

            background: var(--admin-gold-light);

            text-decoration: none;

            font-size: 9px;
            font-weight: 950;

            transition: transform .2s ease;
        }


        .admin-skip-link:focus {
            transform: translateY(0);
        }


        /*
        |--------------------------------------------------------------------------
        | PAGE PROGRESS
        |--------------------------------------------------------------------------
        */

        .admin-progress {
            position: fixed;

            z-index: 4000;

            top: 0;
            left: 0;

            width: 100%;
            height: 2px;

            pointer-events: none;
        }


        .admin-progress-bar {
            width: 0;
            height: 100%;

            background:
                linear-gradient(
                    90deg,
                    var(--admin-gold-deep),
                    var(--admin-gold-light)
                );

            box-shadow:
                0 0 20px rgba(122, 108, 255, .30);

            transition: width .08s linear;
        }


        /*
        |--------------------------------------------------------------------------
        | APP
        |--------------------------------------------------------------------------
        */

        .admin-app {
            position: relative;

            width: 100%;
            min-height: 100vh;
        }


        /*
        |--------------------------------------------------------------------------
        | FLOATING SIDEBAR
        |--------------------------------------------------------------------------
        */

        .admin-sidebar {
            position: fixed;

            z-index: 1200;

            top: 22px;
            left: 22px;
            bottom: 22px;

            width: var(--admin-sidebar-collapsed);

            display: flex;
            flex-direction: column;

            min-height: 0;

            padding: 12px;

            overflow: hidden;

            border:
                1px solid var(--admin-line);

            border-radius: 25px;

            background:
                radial-gradient(
                    circle at 30% 0%,
                    rgba(122, 108, 255, .09),
                    transparent 15rem
                ),
                linear-gradient(
                    180deg,
                    rgba(17, 20, 25, .985),
                    rgba(9, 11, 15, .985)
                );

            box-shadow:
                0 32px 90px rgba(0, 0, 0, .40),
                inset 0 1px 0 rgba(255, 255, 255, .025);

            backdrop-filter: blur(22px);
            -webkit-backdrop-filter: blur(22px);

            transition:
                width var(--admin-transition),
                transform var(--admin-transition),
                border-color var(--admin-transition),
                box-shadow var(--admin-transition);
        }


        .admin-sidebar:hover,
        .admin-sidebar:focus-within,
        .admin-sidebar.is-pinned {
            width: var(--admin-sidebar-expanded);

            border-color:
                rgba(122, 108, 255, .15);

            box-shadow:
                0 40px 110px rgba(0, 0, 0, .52),
                inset 0 1px 0 rgba(255, 255, 255, .03);
        }


        /*
        |--------------------------------------------------------------------------
        | SIDEBAR BRAND
        |--------------------------------------------------------------------------
        */

        .admin-brand-row {
            min-height: 52px;

            display: flex;
            align-items: center;

            gap: 11px;

            flex-shrink: 0;

            padding: 3px 5px;
        }


        .admin-brand {
            min-width: 0;

            flex: 1;

            display: flex;
            align-items: center;

            gap: 11px;

            color: inherit;

            text-decoration: none;
        }


        .admin-brand-mark {
            position: relative;

            width: 46px;
            height: 46px;

            flex: 0 0 46px;

            display: grid;
            place-items: center;

            overflow: hidden;

            border:
                1px solid rgba(122, 108, 255, .20);

            border-radius: 15px;

            color: var(--admin-gold-light);

            background:
                linear-gradient(
                    145deg,
                    rgba(122, 108, 255, .15),
                    rgba(122, 108, 255, .03)
                );

            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, .04),
                0 14px 32px rgba(0, 0, 0, .24);

            font-size: 14px;
            font-weight: 950;

            letter-spacing: -.06em;
        }


        .admin-brand-mark::after {
            content: "";

            position: absolute;

            right: -12px;
            top: -12px;

            width: 25px;
            height: 25px;

            border-radius: 50%;

            background:
                rgba(255, 255, 255, .13);

            filter: blur(6px);
        }


        .admin-brand-copy {
            min-width: 155px;

            opacity: 0;

            transform: translateX(-7px);

            pointer-events: none;

            transition:
                opacity .15s ease,
                transform .24s ease;
        }


        .admin-sidebar:hover .admin-brand-copy,
        .admin-sidebar:focus-within .admin-brand-copy,
        .admin-sidebar.is-pinned .admin-brand-copy {
            opacity: 1;

            transform: translateX(0);

            pointer-events: auto;

            transition-delay: .06s;
        }


        .admin-brand-copy strong,
        .admin-brand-copy span {
            display: block;
        }


        .admin-brand-copy strong {
            color: #f3f4f5;

            font-size: 12px;
            font-weight: 950;

            letter-spacing: -.025em;

            white-space: nowrap;
        }


        .admin-brand-copy span {
            margin-top: 4px;

            color: #656c76;

            font-size: 7px;
            font-weight: 900;

            letter-spacing: .16em;

            text-transform: uppercase;

            white-space: nowrap;
        }


        /*
        |--------------------------------------------------------------------------
        | PIN BUTTON
        |--------------------------------------------------------------------------
        */

        .admin-sidebar-pin {
            width: 31px;
            height: 31px;

            flex: 0 0 31px;

            display: grid;
            place-items: center;

            padding: 0;

            border:
                1px solid transparent;

            border-radius: 9px;

            color: #697079;

            background:
                rgba(255, 255, 255, .015);

            opacity: 0;

            transform: translateX(8px);

            pointer-events: none;

            cursor: pointer;

            transition:
                opacity .15s ease,
                transform .2s ease,
                color .2s ease,
                border-color .2s ease,
                background .2s ease;
        }


        .admin-sidebar:hover .admin-sidebar-pin,
        .admin-sidebar:focus-within .admin-sidebar-pin,
        .admin-sidebar.is-pinned .admin-sidebar-pin {
            opacity: 1;

            transform: translateX(0);

            pointer-events: auto;
        }


        .admin-sidebar-pin:hover,
        .admin-sidebar.is-pinned .admin-sidebar-pin {
            border-color:
                rgba(122, 108, 255, .14);

            color: var(--admin-gold-light);

            background:
                rgba(122, 108, 255, .07);
        }


        .admin-sidebar-pin svg {
            width: 15px;
            height: 15px;
        }


        /*
        |--------------------------------------------------------------------------
        | PROFILE AT TOP
        |--------------------------------------------------------------------------
        */

        .admin-profile {
            min-height: 64px;

            margin-top: 10px;

            display: flex;
            align-items: center;

            gap: 11px;

            padding: 8px;

            overflow: hidden;

            border:
                1px solid transparent;

            border-radius: 15px;

            transition:
                border-color .2s ease,
                background .2s ease;
        }


        .admin-sidebar:hover .admin-profile,
        .admin-sidebar:focus-within .admin-profile,
        .admin-sidebar.is-pinned .admin-profile {
            border-color:
                rgba(255, 255, 255, .055);

            background:
                rgba(255, 255, 255, .018);
        }


        .admin-profile-avatar {
            width: 46px;
            height: 46px;

            flex: 0 0 46px;

            display: grid;
            place-items: center;

            overflow: hidden;

            border:
                1px solid rgba(122, 108, 255, .17);

            border-radius: 14px;

            color: #a99fff;

            background:
                rgba(122, 108, 255, .055);

            font-size: 12px;
            font-weight: 950;

            box-shadow:
                0 11px 28px rgba(0, 0, 0, .20);
        }


        .admin-profile-avatar img {
            width: 100%;
            height: 100%;

            object-fit: cover;
        }


        .admin-profile-copy {
            min-width: 155px;

            flex: 1;

            opacity: 0;

            transform: translateX(-7px);

            pointer-events: none;

            transition:
                opacity .15s ease,
                transform .24s ease;
        }


        .admin-sidebar:hover .admin-profile-copy,
        .admin-sidebar:focus-within .admin-profile-copy,
        .admin-sidebar.is-pinned .admin-profile-copy {
            opacity: 1;

            transform: translateX(0);

            pointer-events: auto;

            transition-delay: .07s;
        }


        .admin-profile-copy strong,
        .admin-profile-copy span {
            display: block;
        }


        .admin-profile-copy strong {
            max-width: 160px;

            overflow: hidden;

            color: #e6e8ea;

            font-size: 10px;
            font-weight: 900;

            text-overflow: ellipsis;
            white-space: nowrap;
        }


        .admin-profile-copy span {
            max-width: 160px;

            margin-top: 4px;

            overflow: hidden;

            color: #666e77;

            font-size: 7px;

            text-overflow: ellipsis;
            white-space: nowrap;
        }


        .admin-role {
            margin-top: 6px;

            display: inline-flex;
            align-items: center;

            gap: 6px;

            color: #7dc998;

            font-size: 7px;
            font-weight: 950;

            letter-spacing: .08em;

            text-transform: uppercase;
        }


        .admin-role::before {
            content: "";

            width: 5px;
            height: 5px;

            border-radius: 50%;

            background:
                var(--admin-green);

            box-shadow:
                0 0 10px rgba(105, 216, 148, .45);
        }


        /*
        |--------------------------------------------------------------------------
        | DIVIDER
        |--------------------------------------------------------------------------
        */

        .admin-sidebar-divider {
            width: calc(100% - 10px);
            height: 1px;

            flex-shrink: 0;

            margin: 10px 5px;

            background:
                var(--admin-line);
        }


        /*
        |--------------------------------------------------------------------------
        | SIDEBAR SEARCH
        |--------------------------------------------------------------------------
        */

        .admin-sidebar-search {
            position: relative;

            width: 100%;
            height: 45px;

            flex-shrink: 0;

            margin-bottom: 8px;

            overflow: hidden;

            border:
                1px solid transparent;

            border-radius: 13px;

            background: transparent;

            transition:
                border-color .2s ease,
                background .2s ease,
                box-shadow .2s ease;
        }


        .admin-sidebar:hover .admin-sidebar-search,
        .admin-sidebar:focus-within .admin-sidebar-search,
        .admin-sidebar.is-pinned .admin-sidebar-search {
            border-color:
                var(--admin-line);

            background:
                rgba(255, 255, 255, .02);
        }


        .admin-sidebar-search:focus-within {
            border-color:
                rgba(122, 108, 255, .26) !important;

            box-shadow:
                0 0 0 3px
                rgba(122, 108, 255, .05);
        }


        .admin-sidebar-search-icon {
            position: absolute;

            left: 15px;
            top: 50%;

            width: 18px;
            height: 18px;

            transform: translateY(-50%);

            color: #747b84;

            pointer-events: none;
        }


        .admin-sidebar-search input {
            width: 230px;
            height: 100%;

            padding:
                0 14px
                0 46px;

            border: 0;

            outline: none;

            color: #e5e7e9;

            background: transparent;

            font-size: 10px;

            opacity: 0;

            pointer-events: none;

            transition:
                opacity .15s ease;
        }


        .admin-sidebar-search input::placeholder {
            color: #5e656e;
        }


        .admin-sidebar:hover .admin-sidebar-search input,
        .admin-sidebar:focus-within .admin-sidebar-search input,
        .admin-sidebar.is-pinned .admin-sidebar-search input {
            opacity: 1;

            pointer-events: auto;

            transition-delay: .07s;
        }


        /*
        |--------------------------------------------------------------------------
        | SIDEBAR NAV
        |--------------------------------------------------------------------------
        */

        .admin-nav {
            flex: 1;

            min-height: 0;

            overflow-y: auto;
            overflow-x: hidden;

            scrollbar-width: none;

            overscroll-behavior: contain;
        }


        .admin-nav::-webkit-scrollbar {
            display: none;
        }


        .admin-nav-label {
            min-height: 27px;

            display: flex;
            align-items: center;

            padding:
                0 12px;

            overflow: hidden;

            color: #4d545e;

            font-size: 7px;
            font-weight: 950;

            letter-spacing: .17em;

            text-transform: uppercase;

            white-space: nowrap;

            opacity: 0;

            transform: translateX(-6px);

            transition:
                opacity .15s ease,
                transform .22s ease;
        }


        .admin-sidebar:hover .admin-nav-label,
        .admin-sidebar:focus-within .admin-nav-label,
        .admin-sidebar.is-pinned .admin-nav-label {
            opacity: 1;

            transform: translateX(0);
        }


        .admin-nav-link {
            position: relative;

            width: 100%;
            min-height: 47px;

            margin: 3px 0;

            padding:
                0 12px;

            display: flex;
            align-items: center;

            gap: 13px;

            overflow: hidden;

            border:
                1px solid transparent;

            border-radius: 13px;

            color: #808792;

            text-decoration: none;

            transition:
                color .18s ease,
                border-color .18s ease,
                background .18s ease;
        }


        .admin-nav-link:hover,
        .admin-nav-link:focus-visible {
            border-color:
                rgba(255, 255, 255, .045);

            color: #dce0e4;

            background:
                rgba(255, 255, 255, .03);
        }


        .admin-nav-link.active {
            border-color:
                rgba(122, 108, 255, .13);

            color: var(--admin-gold-light);

            background:
                linear-gradient(
                    90deg,
                    rgba(122, 108, 255, .12),
                    rgba(122, 108, 255, .035)
                );
        }


        .admin-nav-link.active::before {
            content: "";

            position: absolute;

            left: 0;
            top: 10px;
            bottom: 10px;

            width: 2px;

            border-radius:
                0 999px 999px 0;

            background:
                var(--admin-gold);

            box-shadow:
                0 0 14px
                rgba(122, 108, 255, .55);
        }


        .admin-nav-icon {
            width: 22px;
            height: 22px;

            flex: 0 0 22px;

            display: grid;
            place-items: center;

            color: currentColor;
        }


        .admin-nav-icon svg {
            width: 19px;
            height: 19px;

            display: block;
        }


        .admin-nav-copy {
            min-width: 160px;

            flex: 1;

            opacity: 0;

            transform: translateX(-6px);

            pointer-events: none;

            transition:
                opacity .14s ease,
                transform .23s ease;
        }


        .admin-sidebar:hover .admin-nav-copy,
        .admin-sidebar:focus-within .admin-nav-copy,
        .admin-sidebar.is-pinned .admin-nav-copy {
            opacity: 1;

            transform: translateX(0);

            pointer-events: auto;

            transition-delay: .06s;
        }


        .admin-nav-copy strong,
        .admin-nav-copy span {
            display: block;
        }


        .admin-nav-copy strong {
            color: inherit;

            font-size: 9px;
            font-weight: 850;

            white-space: nowrap;
        }


        .admin-nav-copy span {
            margin-top: 2px;

            color: #515862;

            font-size: 7px;
            font-weight: 750;

            white-space: nowrap;
        }


        .admin-nav-count {
            position: absolute;

            right: 12px;

            min-width: 22px;
            height: 21px;

            padding:
                0 7px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            border:
                1px solid rgba(122, 108, 255, .12);

            border-radius: 999px;

            color: #8f82ff;

            background:
                rgba(122, 108, 255, .055);

            font-size: 7px;
            font-weight: 950;

            opacity: 0;

            transform: translateX(12px);

            transition:
                opacity .15s ease,
                transform .22s ease;
        }


        .admin-sidebar:hover .admin-nav-count,
        .admin-sidebar:focus-within .admin-nav-count,
        .admin-sidebar.is-pinned .admin-nav-count {
            opacity: 1;

            transform: translateX(0);
        }


        /*
        |--------------------------------------------------------------------------
        | SIDEBAR BOTTOM
        |--------------------------------------------------------------------------
        */

        .admin-sidebar-bottom {
            flex-shrink: 0;

            display: flex;
            flex-direction: column;

            gap: 4px;

            padding-top: 8px;
        }


        .admin-bottom-link,
        .admin-bottom-form button {
            width: 100%;
            height: 45px;

            padding:
                0 12px;

            display: flex;
            align-items: center;

            gap: 13px;

            overflow: hidden;

            border:
                1px solid transparent;

            border-radius: 13px;

            color: #747b84;

            background: transparent;

            text-decoration: none;

            cursor: pointer;

            transition:
                color .18s ease,
                border-color .18s ease,
                background .18s ease;
        }


        .admin-bottom-link:hover,
        .admin-bottom-form button:hover {
            border-color:
                var(--admin-line);

            color: #e1e4e7;

            background:
                rgba(255, 255, 255, .03);
        }


        .admin-bottom-link.primary {
            border-color:
                rgba(122, 108, 255, .09);

            color: var(--admin-gold-light);

            background:
                rgba(122, 108, 255, .05);
        }


        .admin-bottom-link.primary:hover {
            border-color:
                rgba(122, 108, 255, .17);

            background:
                rgba(122, 108, 255, .095);
        }


        .admin-bottom-form {
            margin: 0;
        }


        .admin-bottom-form button:hover {
            border-color:
                rgba(240, 131, 131, .13);

            color: #df9595;

            background:
                rgba(240, 131, 131, .05);
        }


        .admin-bottom-icon {
            width: 22px;
            height: 22px;

            flex: 0 0 22px;

            display: grid;
            place-items: center;
        }


        .admin-bottom-icon svg {
            width: 19px;
            height: 19px;
        }


        .admin-bottom-text {
            min-width: 160px;

            font-size: 9px;
            font-weight: 850;

            text-align: left;

            white-space: nowrap;

            opacity: 0;

            transform: translateX(-6px);

            transition:
                opacity .14s ease,
                transform .23s ease;
        }


        .admin-sidebar:hover .admin-bottom-text,
        .admin-sidebar:focus-within .admin-bottom-text,
        .admin-sidebar.is-pinned .admin-bottom-text {
            opacity: 1;

            transform: translateX(0);

            transition-delay: .06s;
        }


        /*
        |--------------------------------------------------------------------------
        | MAIN CONTENT
        |--------------------------------------------------------------------------
        */

        .admin-main {
            position: relative;

            min-width: 0;
            min-height: 100vh;

            margin-left: 128px;

            padding:
                24px
                clamp(22px, 3vw, 46px)
                60px
                0;

            transition:
                margin-left var(--admin-transition);
        }


        body.admin-sidebar-pinned .admin-main {
            margin-left: 330px;
        }


        /*
        |--------------------------------------------------------------------------
        | TOP BAR
        |--------------------------------------------------------------------------
        */

        .admin-topbar {
            position: relative;

            z-index: 30;

            min-height: 76px;

            margin-bottom: 20px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 24px;

            border-bottom:
                1px solid var(--admin-line);
        }


        .admin-topbar-copy {
            min-width: 0;
        }


        .admin-kicker {
            display: block;

            margin-bottom: 6px;

            color: #7468d8;

            font-size: 7px;
            font-weight: 950;

            letter-spacing: .15em;

            text-transform: uppercase;
        }


        .admin-page-title {
            margin: 0;

            color: #f2f3f4;

            font-size:
                clamp(27px, 3.2vw, 42px);

            line-height: 1;

            font-weight: 950;

            letter-spacing: -.052em;
        }


        .admin-page-subtitle {
            max-width: 720px;

            margin-top: 8px;

            display: block;

            color: #737a84;

            font-size: 9px;

            line-height: 1.65;
        }


        .admin-topbar-actions {
            flex: 0 0 auto;

            display: flex;
            align-items: center;

            gap: 8px;

            flex-wrap: wrap;
        }


        /*
        |--------------------------------------------------------------------------
        | BUTTONS
        |--------------------------------------------------------------------------
        */

        .admin-button {
            min-height: 40px;

            padding:
                0 14px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            gap: 8px;

            border:
                1px solid transparent;

            border-radius: 11px;

            color: #171009;

            background:
                linear-gradient(
                    135deg,
                    var(--admin-gold-light),
                    #4d88ff
                );

            box-shadow:
                0 13px 30px
                rgba(122, 108, 255, .12);

            text-decoration: none;

            font-size: 8px;
            font-weight: 950;

            cursor: pointer;

            transition:
                transform .2s ease,
                box-shadow .2s ease,
                border-color .2s ease,
                background .2s ease,
                color .2s ease;
        }


        .admin-button:hover {
            transform:
                translateY(-1px);

            box-shadow:
                0 17px 38px
                rgba(122, 108, 255, .18);
        }


        .admin-button.secondary {
            border-color:
                var(--admin-line);

            color: #aeb4bc;

            background:
                rgba(255, 255, 255, .018);

            box-shadow: none;
        }


        .admin-button.secondary:hover {
            border-color:
                rgba(122, 108, 255, .13);

            color: #e1e3e6;

            background:
                rgba(122, 108, 255, .03);
        }


        .admin-button.danger {
            border-color:
                rgba(240, 131, 131, .12);

            color: #d98989;

            background:
                rgba(240, 131, 131, .035);

            box-shadow: none;
        }


        /*
        |--------------------------------------------------------------------------
        | MOBILE MENU BUTTON
        |--------------------------------------------------------------------------
        */

        .admin-mobile-toggle {
            width: 46px;
            height: 46px;

            display: none;

            place-items: center;

            padding: 0;

            border:
                1px solid rgba(122, 108, 255, .16);

            border-radius: 13px;

            color: var(--admin-gold-light);

            background:
                linear-gradient(
                    145deg,
                    #15191f,
                    #0e1115
                );

            box-shadow:
                0 14px 36px
                rgba(0, 0, 0, .30);

            cursor: pointer;
        }


        .admin-mobile-toggle-lines,
        .admin-mobile-toggle-lines::before,
        .admin-mobile-toggle-lines::after {
            width: 18px;
            height: 1.5px;

            display: block;

            border-radius: 999px;

            background: currentColor;

            transition:
                transform .2s ease,
                top .2s ease,
                opacity .2s ease;
        }


        .admin-mobile-toggle-lines {
            position: relative;
        }


        .admin-mobile-toggle-lines::before,
        .admin-mobile-toggle-lines::after {
            content: "";

            position: absolute;

            left: 0;
        }


        .admin-mobile-toggle-lines::before {
            top: -6px;
        }


        .admin-mobile-toggle-lines::after {
            top: 6px;
        }


        body.admin-mobile-open
        .admin-mobile-toggle-lines {
            background: transparent;
        }


        body.admin-mobile-open
        .admin-mobile-toggle-lines::before {
            top: 0;

            transform: rotate(45deg);
        }


        body.admin-mobile-open
        .admin-mobile-toggle-lines::after {
            top: 0;

            transform: rotate(-45deg);
        }


        /*
        |--------------------------------------------------------------------------
        | MOBILE OVERLAY
        |--------------------------------------------------------------------------
        */

        .admin-mobile-overlay {
            position: fixed;

            z-index: 1150;

            inset: 0;

            display: none;

            background:
                rgba(0, 0, 0, .66);

            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);

            opacity: 0;

            pointer-events: none;

            transition:
                opacity .2s ease;
        }


        .admin-mobile-overlay.is-open {
            opacity: 1;

            pointer-events: auto;
        }


        /*
        |--------------------------------------------------------------------------
        | BREADCRUMBS
        |--------------------------------------------------------------------------
        */

        .admin-breadcrumbs {
            margin:
                -4px 0 18px;

            display: flex;
            align-items: center;

            gap: 7px;

            flex-wrap: wrap;

            color: #565e68;

            font-size: 7px;
            font-weight: 850;
        }


        .admin-breadcrumbs a {
            color: #747c86;

            text-decoration: none;
        }


        .admin-breadcrumbs a:hover {
            color: #c5c9ce;
        }


        .admin-breadcrumb-separator {
            color: #343a42;
        }


        /*
        |--------------------------------------------------------------------------
        | ALERTS
        |--------------------------------------------------------------------------
        */

        .admin-alert-stack {
            margin-bottom: 18px;

            display: grid;

            gap: 9px;
        }


        .admin-alert {
            position: relative;

            padding:
                14px 42px
                14px 15px;

            display: flex;
            align-items: flex-start;

            gap: 10px;

            border:
                1px solid var(--admin-line);

            border-radius: 13px;

            color: #9fa6af;

            background:
                rgba(255, 255, 255, .015);

            font-size: 9px;

            line-height: 1.65;
        }


        .admin-alert::before {
            content: "";

            width: 7px;
            height: 7px;

            flex: 0 0 7px;

            margin-top: 4px;

            border-radius: 50%;

            background: #7f8791;
        }


        .admin-alert.success {
            border-color:
                rgba(105, 216, 148, .13);

            color: #a4dcb6;

            background:
                rgba(105, 216, 148, .04);
        }


        .admin-alert.success::before {
            background:
                var(--admin-green);
        }


        .admin-alert.error {
            border-color:
                rgba(240, 131, 131, .13);

            color: #dca0a0;

            background:
                rgba(240, 131, 131, .04);
        }


        .admin-alert.error::before {
            background:
                var(--admin-red);
        }


        .admin-alert.warning {
            border-color:
                rgba(237, 194, 112, .13);

            color: #d9bd87;

            background:
                rgba(237, 194, 112, .04);
        }


        .admin-alert.warning::before {
            background:
                var(--admin-warning);
        }


        .admin-alert-close {
            position: absolute;

            right: 9px;
            top: 9px;

            width: 27px;
            height: 27px;

            display: grid;
            place-items: center;

            border:
                1px solid rgba(255, 255, 255, .055);

            border-radius: 8px;

            color: #606873;

            background:
                rgba(255, 255, 255, .012);

            cursor: pointer;
        }


        .admin-alert ul {
            margin:
                7px 0 0 16px;

            padding: 0;
        }


        /*
        |--------------------------------------------------------------------------
        | CONTENT
        |--------------------------------------------------------------------------
        */

        .admin-content {
            min-width: 0;
        }


        /*
        |--------------------------------------------------------------------------
        | GENERIC STATS
        |--------------------------------------------------------------------------
        */

        .admin-stats {
            margin: 18px 0;

            display: grid;

            grid-template-columns:
                repeat(
                    4,
                    minmax(150px, 1fr)
                );

            gap: 13px;
        }


        .admin-stat {
            position: relative;

            min-height: 132px;

            padding: 18px;

            overflow: hidden;

            border:
                1px solid var(--admin-line);

            border-radius: 18px;

            background:
                linear-gradient(
                    145deg,
                    rgba(255, 255, 255, .025),
                    rgba(255, 255, 255, .007)
                ),
                var(--admin-panel);
        }


        .admin-stat::after {
            content: "";

            position: absolute;

            width: 115px;
            height: 115px;

            right: -45px;
            bottom: -55px;

            border:
                1px solid rgba(122, 108, 255, .06);

            border-radius: 50%;
        }


        .admin-stat-label {
            color: #5e6670;

            font-size: 7px;
            font-weight: 950;

            letter-spacing: .13em;

            text-transform: uppercase;
        }


        .admin-stat-value {
            margin-top: 17px;

            color: #eef0f2;

            font-size: 32px;

            line-height: 1;

            font-weight: 950;

            letter-spacing: -.05em;
        }


        .admin-stat-foot {
            margin-top: 9px;

            color: #515862;

            font-size: 8px;

            line-height: 1.5;
        }


        /*
        |--------------------------------------------------------------------------
        | GENERIC PANEL
        |--------------------------------------------------------------------------
        */

        .admin-panel {
            margin-top: 18px;

            padding: 23px;

            border:
                1px solid var(--admin-line);

            border-radius: 22px;

            background:
                linear-gradient(
                    145deg,
                    rgba(255, 255, 255, .025),
                    rgba(255, 255, 255, .006)
                ),
                var(--admin-panel);

            box-shadow:
                0 16px 45px
                rgba(0, 0, 0, .13);
        }


        .admin-section-heading {
            margin-bottom: 19px;

            display: flex;
            align-items: flex-end;
            justify-content: space-between;

            gap: 18px;
        }


        .admin-section-heading h2 {
            margin: 0;

            color: #e5e7e9;

            font-size: 21px;
            font-weight: 950;

            letter-spacing: -.035em;
        }


        .admin-section-heading p {
            max-width: 650px;

            margin: 6px 0 0;

            color: #6c737d;

            font-size: 9px;

            line-height: 1.65;
        }


        /*
        |--------------------------------------------------------------------------
        | EMPTY
        |--------------------------------------------------------------------------
        */

        .admin-empty-state {
            padding: 48px 24px;

            text-align: center;

            border:
                1px dashed rgba(255, 255, 255, .08);

            border-radius: 17px;

            color: #6f7680;

            background:
                rgba(255, 255, 255, .008);
        }


        .admin-empty-state strong {
            display: block;

            margin-bottom: 7px;

            color: #cfd3d7;

            font-size: 13px;
        }


        /*
        |--------------------------------------------------------------------------
        | TABLES
        |--------------------------------------------------------------------------
        */

        .table-wrap {
            width: 100%;

            overflow-x: auto;

            border:
                1px solid var(--admin-line);

            border-radius: 15px;

            background:
                rgba(0, 0, 0, .08);
        }


        table {
            width: 100%;

            border-collapse: collapse;
        }


        th {
            padding: 12px;

            border-bottom:
                1px solid var(--admin-line);

            color: #636a74;

            background:
                rgba(255, 255, 255, .016);

            text-align: left;

            font-size: 7px;
            font-weight: 950;

            letter-spacing: .11em;

            text-transform: uppercase;

            white-space: nowrap;
        }


        td {
            padding:
                13px 12px;

            border-bottom:
                1px solid rgba(255, 255, 255, .04);

            color: #bcc1c8;

            font-size: 9px;

            vertical-align: middle;
        }


        tbody tr:last-child td {
            border-bottom: 0;
        }


        tbody tr:hover {
            background:
                rgba(122, 108, 255, .017);
        }


        /*
        |--------------------------------------------------------------------------
        | BADGES
        |--------------------------------------------------------------------------
        */

        .badge {
            min-height: 26px;

            padding:
                0 9px;

            display: inline-flex;
            align-items: center;

            border:
                1px solid var(--admin-line);

            border-radius: 999px;

            color: #989fa8;

            background:
                rgba(255, 255, 255, .015);

            font-size: 7px;
            font-weight: 900;

            white-space: nowrap;
        }


        .badge.admin {
            border-color:
                rgba(120, 183, 255, .12);

            color: #9ec8f7;

            background:
                rgba(120, 183, 255, .04);
        }


        .badge.success {
            border-color:
                rgba(105, 216, 148, .12);

            color: #99d8af;

            background:
                rgba(105, 216, 148, .04);
        }


        .badge.warning {
            border-color:
                rgba(237, 194, 112, .12);

            color: #d7ba82;

            background:
                rgba(237, 194, 112, .04);
        }


        .badge.danger {
            border-color:
                rgba(240, 131, 131, .12);

            color: #d99595;

            background:
                rgba(240, 131, 131, .04);
        }


        /*
        |--------------------------------------------------------------------------
        | FORMS
        |--------------------------------------------------------------------------
        */

        .form-wrap {
            max-width: 820px;

            padding: 25px;

            border:
                1px solid var(--admin-line);

            border-radius: 21px;

            background:
                var(--admin-panel);
        }


        .form-row {
            margin-bottom: 17px;
        }


        label {
            display: block;

            margin-bottom: 7px;

            color: #818892;

            font-size: 8px;
            font-weight: 900;

            letter-spacing: .08em;
        }


        .admin-content input,
        .admin-content select,
        .admin-content textarea {
            width: 100%;

            padding:
                12px 13px;

            border:
                1px solid var(--admin-line);

            border-radius: 11px;

            outline: none;

            color: #e3e5e8;

            background:
                var(--admin-panel-2);

            font-size: 10px;

            transition:
                border-color .2s ease,
                background .2s ease,
                box-shadow .2s ease;
        }


        .admin-content input:focus,
        .admin-content select:focus,
        .admin-content textarea:focus {
            border-color:
                rgba(122, 108, 255, .28);

            background: #151a21;

            box-shadow:
                0 0 0 4px
                rgba(122, 108, 255, .04);
        }


        .admin-content textarea {
            min-height: 120px;

            resize: vertical;
        }


        /*
        |--------------------------------------------------------------------------
        | ACCESS PAGE
        |--------------------------------------------------------------------------
        */

        .admin-access-page {
            min-height: 100vh;

            padding: 24px;

            display: grid;
            place-items: center;

            background:
                radial-gradient(
                    circle at 50% 0%,
                    rgba(122, 108, 255, .08),
                    transparent 27rem
                ),
                var(--admin-bg);
        }


        .admin-access-card {
            width:
                min(100%, 520px);

            padding: 34px;

            border:
                1px solid var(--admin-line);

            border-radius: 24px;

            background:
                linear-gradient(
                    145deg,
                    rgba(255, 255, 255, .035),
                    rgba(255, 255, 255, .007)
                ),
                var(--admin-panel);

            box-shadow:
                var(--admin-shadow);

            text-align: center;
        }


        .admin-access-icon {
            width: 62px;
            height: 62px;

            margin:
                0 auto 20px;

            display: grid;
            place-items: center;

            border:
                1px solid rgba(122, 108, 255, .15);

            border-radius: 18px;

            color: var(--admin-gold-light);

            background:
                rgba(122, 108, 255, .045);

            font-size: 19px;
            font-weight: 950;
        }


        .admin-access-icon.danger {
            border-color:
                rgba(240, 131, 131, .13);

            color: #dc9696;

            background:
                rgba(240, 131, 131, .04);
        }


        .admin-access-card h1 {
            margin: 0;

            color: #edf0f2;

            font-size: 29px;

            letter-spacing: -.045em;
        }


        .admin-access-card p {
            margin:
                13px 0 23px;

            color: #767d87;

            font-size: 10px;

            line-height: 1.75;
        }


        /*
        |--------------------------------------------------------------------------
        | TOASTS
        |--------------------------------------------------------------------------
        */

        .admin-toast-region {
            position: fixed;

            z-index: 3500;

            right: 17px;
            top: 17px;

            width:
                min(
                    360px,
                    calc(100vw - 34px)
                );

            display: grid;

            gap: 8px;

            pointer-events: none;
        }


        .admin-toast {
            padding:
                13px 15px;

            border:
                1px solid var(--admin-line);

            border-radius: 12px;

            color: #b9bec5;

            background:
                rgba(16, 19, 24, .96);

            box-shadow:
                var(--admin-shadow-soft);

            font-size: 9px;

            line-height: 1.55;

            opacity: 0;

            transform:
                translateY(-6px);

            transition:
                opacity .2s ease,
                transform .2s ease;
        }


        .admin-toast.show {
            opacity: 1;

            transform:
                translateY(0);
        }


        .is-loading {
            opacity: .62 !important;

            pointer-events: none !important;

            cursor: wait !important;
        }


        /*
        |--------------------------------------------------------------------------
        | SCROLLBAR
        |--------------------------------------------------------------------------
        */

        ::-webkit-scrollbar {
            width: 9px;
            height: 9px;
        }


        ::-webkit-scrollbar-track {
            background: #090b0e;
        }


        ::-webkit-scrollbar-thumb {
            border:
                2px solid #090b0e;

            border-radius: 999px;

            background: #2a2f36;
        }


        ::-webkit-scrollbar-thumb:hover {
            background: #383e46;
        }


        /*
        |--------------------------------------------------------------------------
        | RESPONSIVE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 1100px) {
            .admin-stats {
                grid-template-columns:
                    repeat(
                        2,
                        minmax(150px, 1fr)
                    );
            }
        }


        @media (max-width: 900px) {

            .admin-sidebar {
                top: 12px;
                left: 12px;
                bottom: 12px;

                width:
                    min(
                        var(--admin-sidebar-expanded),
                        calc(100vw - 24px)
                    );

                transform:
                    translateX(
                        calc(-100% - 30px)
                    );
            }


            .admin-sidebar:hover,
            .admin-sidebar:focus-within,
            .admin-sidebar.is-pinned {
                width:
                    min(
                        var(--admin-sidebar-expanded),
                        calc(100vw - 24px)
                    );
            }


            .admin-sidebar.is-open {
                transform:
                    translateX(0);
            }


            .admin-sidebar
            .admin-brand-copy,

            .admin-sidebar
            .admin-profile-copy,

            .admin-sidebar
            .admin-nav-label,

            .admin-sidebar
            .admin-nav-copy,

            .admin-sidebar
            .admin-nav-count,

            .admin-sidebar
            .admin-bottom-text,

            .admin-sidebar
            .admin-sidebar-pin,

            .admin-sidebar
            .admin-sidebar-search input {
                opacity: 1;

                transform: none;

                pointer-events: auto;
            }


            .admin-sidebar
            .admin-sidebar-search {
                border-color:
                    var(--admin-line);

                background:
                    rgba(255, 255, 255, .02);
            }


            .admin-mobile-overlay {
                display: block;
            }


            .admin-main,
            body.admin-sidebar-pinned
            .admin-main {
                width: 100%;

                margin-left: 0;

                padding:
                    16px
                    clamp(16px, 3vw, 26px)
                    45px;
            }


            .admin-topbar {
                min-height: 68px;
            }


            .admin-mobile-toggle {
                display: grid;
            }


            .admin-topbar-actions
            .desktop-only {
                display: none;
            }
        }


        @media (max-width: 650px) {

            .admin-main,
            body.admin-sidebar-pinned
            .admin-main {
                padding:
                    14px
                    12px
                    38px;
            }


            .admin-topbar {
                align-items: flex-start;

                flex-direction: column;

                padding:
                    8px 0 15px;
            }


            .admin-topbar-actions {
                width: 100%;
            }


            .admin-mobile-toggle {
                margin-left: 0;
            }


            .admin-stats {
                grid-template-columns:
                    1fr;
            }


            .admin-panel {
                padding: 17px;

                border-radius: 18px;
            }


            .admin-section-heading {
                align-items: flex-start;

                flex-direction: column;
            }


            .admin-sidebar {
                width:
                    calc(100vw - 24px);
            }


            .admin-sidebar:hover,
            .admin-sidebar:focus-within,
            .admin-sidebar.is-pinned {
                width:
                    calc(100vw - 24px);
            }


            .admin-access-card {
                padding:
                    28px 20px;
            }
        }


        @media (prefers-reduced-motion: reduce) {

            html {
                scroll-behavior: auto;
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
        }


        @media print {

            .admin-sidebar,
            .admin-topbar-actions,
            .admin-mobile-toggle,
            .admin-progress,
            .admin-breadcrumbs {
                display:
                    none !important;
            }


            .admin-main {
                margin: 0;

                padding: 0;
            }


            body {
                color: #000;

                background: #fff;
            }
        }
    </style>

    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/mobile-responsive.css') }}?v=20261008-3">
</head>


<body>

    <a
        class="admin-skip-link"
        href="#adminContent"
    >
        {{ __('Ga naar inhoud') }}
    </a>


    <div
        class="admin-progress"
        aria-hidden="true"
    >
        <div
            class="admin-progress-bar"
            id="adminProgressBar"
        ></div>
    </div>


    @auth

        @if ($adminIsAllowed)

            <div class="admin-app">

                {{-- ================================================= --}}
                {{-- MOBILE OVERLAY                                     --}}
                {{-- ================================================= --}}

                <div
                    class="admin-mobile-overlay"
                    id="adminMobileOverlay"
                    aria-hidden="true"
                ></div>


                {{-- ================================================= --}}
                {{-- SIDEBAR                                           --}}
                {{-- ================================================= --}}

                <aside
                    class="admin-sidebar"
                    id="adminSidebar"
                    aria-label="Admin navigatie"
                >

                    {{-- BRAND --}}

                    <div class="admin-brand-row">

                        <a
                            class="admin-brand"
                            href="{{ $adminDashboardUrl }}"
                        >
                            <span class="admin-brand-mark">
                                M
                            </span>

                            <span class="admin-brand-copy">
                                <strong>
                                    Mashal Studio
                                </strong>

                                <span>
                                    {{ __('Admin workspace') }}
                                </span>
                            </span>
                        </a>


                        <button
                            class="admin-sidebar-pin"
                            id="adminSidebarPin"
                            type="button"
                            aria-label="Sidebar vastzetten"
                            aria-pressed="false"
                            title="Sidebar vastzetten"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="m12 17 5 5"/>
                                <path d="M15 4.5 19.5 9l-4 2-4.5 4.5-3-3L12.5 8l2.5-3.5Z"/>
                                <path d="m6 18 3-3"/>
                            </svg>
                        </button>

                    </div>


                    {{-- PROFILE --}}

                    <div class="admin-profile">

                        <div class="admin-profile-avatar">

                            @if ($adminAvatarUrl)

                                <img
                                    src="{{ $adminAvatarUrl }}"
                                    alt="Profielfoto van {{ $adminUser->name }}"
                                    loading="eager"
                                >

                            @else

                                {{ $adminInitials }}

                            @endif

                        </div>


                        <div class="admin-profile-copy">

                            <strong>
                                {{ $adminUser->name }}
                            </strong>

                            <span>
                                {{ $adminUser->email }}
                            </span>

                            <div class="admin-role">
                                {{ __('Administrator') }}
                            </div>

                        </div>

                    </div>


                    <div class="admin-sidebar-divider"></div>


                    {{-- SEARCH --}}

                    <div class="admin-sidebar-search">

                        <svg
                            class="admin-sidebar-search-icon"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            aria-hidden="true"
                        >
                            <circle
                                cx="11"
                                cy="11"
                                r="7"
                            />

                            <path d="m20 20-4-4"/>
                        </svg>


                        <input
                            id="adminSidebarSearch"
                            type="search"
                            placeholder="Zoek in admin..."
                            autocomplete="off"
                            aria-label="Zoeken in adminmenu"
                        >

                    </div>


                    {{-- ================================================= --}}
                    {{-- NAVIGATION                                        --}}
                    {{-- ================================================= --}}

                    <nav class="admin-nav">
                        @if (\Illuminate\Support\Facades\Route::has('admin.live-chat.index'))
                            <a class="admin-nav-link {{ request()->routeIs('admin.live-chat.*') ? 'active' : '' }}" href="{{ route('admin.live-chat.index') }}" data-admin-menu-item data-search="live chat gesprekken bezoekers gasten">
                                <span class="admin-nav-icon" aria-hidden="true">✉</span>
                                <span>{{ __('Live chat') }}</span>
                            </a>
                        @endif


                        <div class="admin-nav-label">
                            {{ __('Overzicht') }}
                        </div>


                        @if ($hasAdminDashboard)

                            <a
                                class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                                href="{{ route('admin.dashboard') }}"
                                data-admin-menu-item
                                data-search="dashboard overzicht platform home"
                                title="Dashboard"
                            >
                                <span class="admin-nav-icon">

                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <rect
                                            x="3"
                                            y="3"
                                            width="7"
                                            height="7"
                                            rx="2"
                                        />

                                        <rect
                                            x="14"
                                            y="3"
                                            width="7"
                                            height="7"
                                            rx="2"
                                        />

                                        <rect
                                            x="3"
                                            y="14"
                                            width="7"
                                            height="7"
                                            rx="2"
                                        />

                                        <rect
                                            x="14"
                                            y="14"
                                            width="7"
                                            height="7"
                                            rx="2"
                                        />
                                    </svg>

                                </span>


                                <span class="admin-nav-copy">

                                    <strong>
                                        {{ __('Dashboard') }}
                                    </strong>

                                    <span>
                                        {{ __('Platformoverzicht') }}
                                    </span>

                                </span>

                            </a>

                        @endif


                        @if (\Illuminate\Support\Facades\Route::has('live.index'))
                            <a
                                class="admin-nav-link {{ request()->routeIs('live.*') ? 'active' : '' }}"
                                href="{{ route('live.index') }}"
                                data-admin-menu-item
                                data-search="live opnames video recorder knock1knock emyii privé"
                                title="Privé live-opnames"
                            >
                                <span class="admin-nav-icon" aria-hidden="true">●</span>
                                <span class="admin-nav-copy">
                                    <strong>{{ __('Live-opnames') }}</strong>
                                    <span>{{ __('Alleen admin · privévideo’s') }}</span>
                                </span>
                            </a>
                        @endif

                        <div class="admin-nav-label">
                            {{ __('Gebruikers') }}
                        </div>


                        @if ($hasUsersIndex)

                            <a
                                class="admin-nav-link {{
                                    request()->routeIs('users.index') ||
                                    request()->routeIs('users.edit') ||
                                    request()->routeIs('users.show')
                                        ? 'active'
                                        : ''
                                }}"
                                href="{{ route('users.index') }}"
                                data-admin-menu-item
                                data-search="gebruikers users accounts rollen leden"
                                title="Gebruikers beheren"
                            >

                                <span class="admin-nav-icon">

                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <circle
                                            cx="9"
                                            cy="8"
                                            r="3"
                                        />

                                        <path d="M3 21v-3a6 6 0 0 1 12 0v3"/>

                                        <path d="M17 5a3 3 0 0 1 0 6"/>

                                        <path d="M18 14a5 5 0 0 1 3 4v3"/>
                                    </svg>

                                </span>


                                <span class="admin-nav-copy">

                                    <strong>
                                        {{ __('Gebruikers beheren') }}
                                    </strong>

                                    <span>
                                        {{ __('Accounts en rollen') }}
                                    </span>

                                </span>


                                @if ($adminUserCount !== null)

                                    <span class="admin-nav-count">
                                        {{
                                            $adminUserCount > 999
                                                ? '999+'
                                                : $adminUserCount
                                        }}
                                    </span>

                                @endif

                            </a>

                        @endif


                        @if ($hasUsersCreate)

                            <a
                                class="admin-nav-link {{ request()->routeIs('users.create') ? 'active' : '' }}"
                                href="{{ route('users.create') }}"
                                data-admin-menu-item
                                data-search="nieuwe gebruiker toevoegen account aanmaken"
                                title="Gebruiker toevoegen"
                            >

                                <span class="admin-nav-icon">

                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <circle
                                            cx="9"
                                            cy="8"
                                            r="3"
                                        />

                                        <path d="M3 21v-3a6 6 0 0 1 12 0v3"/>

                                        <path d="M19 8v6"/>

                                        <path d="M16 11h6"/>
                                    </svg>

                                </span>


                                <span class="admin-nav-copy">

                                    <strong>
                                        {{ __('Gebruiker toevoegen') }}
                                    </strong>

                                    <span>
                                        {{ __('Nieuw account') }}
                                    </span>

                                </span>

                            </a>

                        @endif


                        <div class="admin-nav-label">
                            {{ __('Image Studio') }}
                        </div>


                        @if ($hasImagesIndex)

                            <a
                                class="admin-nav-link {{ request()->routeIs('images.*') ? 'active' : '' }}"
                                href="{{ route('images.index') }}"
                                data-admin-menu-item
                                data-search="afbeeldingen images foto's foto's projecten bibliotheek"
                                title="Afbeeldingen"
                            >

                                <span class="admin-nav-icon">

                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <rect
                                            x="3"
                                            y="3"
                                            width="18"
                                            height="18"
                                            rx="3"
                                        />

                                        <circle
                                            cx="8"
                                            cy="8"
                                            r="1.5"
                                        />

                                        <path d="m3 17 6-6 4 4 3-3 5 5"/>
                                    </svg>

                                </span>


                                <span class="admin-nav-copy">

                                    <strong>
                                        {{ __('Afbeeldingen') }}
                                    </strong>

                                    <span>
                                        {{ __('Projectbibliotheek') }}
                                    </span>

                                </span>


                                @if ($adminImageCount !== null)

                                    <span class="admin-nav-count">
                                        {{
                                            $adminImageCount > 999
                                                ? '999+'
                                                : $adminImageCount
                                        }}
                                    </span>

                                @endif

                            </a>

                        @endif


                        @if ($hasHome)

                            <a
                                class="admin-nav-link"
                                href="{{ route('home') }}#upload"
                                data-admin-menu-item
                                data-search="upload nieuwe afbeelding image studio"
                                title="Nieuwe upload"
                            >

                                <span class="admin-nav-icon">

                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <path d="M12 16V4"/>

                                        <path d="m7 9 5-5 5 5"/>

                                        <path d="M5 20h14"/>
                                    </svg>

                                </span>


                                <span class="admin-nav-copy">

                                    <strong>
                                        {{ __('Nieuwe upload') }}
                                    </strong>

                                    <span>
                                        {{ __('Studio openen') }}
                                    </span>

                                </span>

                            </a>

                        @endif


                        <div class="admin-nav-label">
                            {{ __('Account') }}
                        </div>


                        @if ($hasAccount)

                            <a
                                class="admin-nav-link {{ request()->routeIs('account') ? 'active' : '' }}"
                                href="{{ route('account') }}"
                                data-admin-menu-item
                                data-search="account profiel instellingen"
                                title="Mijn account"
                            >

                                <span class="admin-nav-icon">

                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <circle
                                            cx="12"
                                            cy="8"
                                            r="4"
                                        />

                                        <path d="M4 21v-2a8 8 0 0 1 16 0v2"/>
                                    </svg>

                                </span>


                                <span class="admin-nav-copy">

                                    <strong>
                                        {{ __('Mijn account') }}
                                    </strong>

                                    <span>
                                        {{ __('Profielinstellingen') }}
                                    </span>

                                </span>

                            </a>

                        @endif


                        @if ($hasSecurity)

                            <a
                                class="admin-nav-link {{ request()->routeIs('security.*') ? 'active' : '' }}"
                                href="{{ route('security.index') }}"
                                data-admin-menu-item
                                data-search="beveiliging security login activiteit sessies"
                                title="Beveiliging"
                            >

                                <span class="admin-nav-icon">

                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <path d="m12 3 8 3v6c0 5-8 9-8 9s-8-4-8-9V6l8-3Z"/>

                                        <path d="m8 12 3 3 5-6"/>
                                    </svg>

                                </span>


                                <span class="admin-nav-copy">

                                    <strong>
                                        {{ __('Beveiliging') }}
                                    </strong>

                                    <span>
                                        {{ __('Loginactiviteit') }}
                                    </span>

                                </span>

                            </a>

                        @endif


                        @if ($hasHome)

                            <a
                                class="admin-nav-link"
                                href="{{ route('home') }}"
                                data-admin-menu-item
                                data-search="website mashal studio home frontend"
                                title="Website bekijken"
                            >

                                <span class="admin-nav-icon">

                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <circle
                                            cx="12"
                                            cy="12"
                                            r="9"
                                        />

                                        <path d="M3 12h18"/>

                                        <path d="M12 3c3 3 3 15 0 18"/>

                                        <path d="M12 3c-3 3-3 15 0 18"/>
                                    </svg>

                                </span>


                                <span class="admin-nav-copy">

                                    <strong>
                                        {{ __('Website bekijken') }}
                                    </strong>

                                    <span>
                                        {{ __('Naar Mashal Studio') }}
                                    </span>

                                </span>

                            </a>

                        @endif

                    </nav>


                    <div class="admin-sidebar-divider"></div>


                    {{-- ================================================= --}}
                    {{-- SIDEBAR BOTTOM                                    --}}
                    {{-- ================================================= --}}

                    <div class="admin-sidebar-bottom">

                        @if ($hasUsersCreate)

                            <a
                                class="admin-bottom-link primary"
                                href="{{ route('users.create') }}"
                                title="Nieuwe gebruiker"
                            >

                                <span class="admin-bottom-icon">

                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        aria-hidden="true"
                                    >
                                        <path d="M12 5v14"/>

                                        <path d="M5 12h14"/>
                                    </svg>

                                </span>


                                <span class="admin-bottom-text">
                                    {{ __('Nieuwe gebruiker') }}
                                </span>

                            </a>

                        @endif


                        @if ($hasAccount)

                            <a
                                class="admin-bottom-link"
                                href="{{ route('account') }}"
                                title="Account"
                            >

                                <span class="admin-bottom-icon">

                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <circle
                                            cx="12"
                                            cy="12"
                                            r="3"
                                        />

                                        <path d="M19 12a7 7 0 0 1-.15 1.45l2 1.55-2 3.46-2.45-1a7 7 0 0 1-2.5 1.45L13.55 21h-4.1l-.35-2.09a7 7 0 0 1-2.5-1.45l-2.45 1-2-3.46 2-1.55A7 7 0 0 1 4 12c0-.5.05-.98.15-1.45L2.15 9l2-3.46 2.45 1A7 7 0 0 1 9.1 5.1L9.45 3h4.1l.35 2.1a7 7 0 0 1 2.5 1.44l2.45-1 2 3.46-2 1.55c.1.47.15.95.15 1.45Z"/>
                                    </svg>

                                </span>


                                <span class="admin-bottom-text">
                                    {{ __('Instellingen') }}
                                </span>

                            </a>

                        @endif


                        @if ($hasLogout)

                            <form
                                class="admin-bottom-form"
                                method="POST"
                                action="{{ route('logout') }}"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    title="Uitloggen"
                                >

                                    <span class="admin-bottom-icon">

                                        <svg
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            aria-hidden="true"
                                        >
                                            <path d="M10 17l5-5-5-5"/>

                                            <path d="M15 12H3"/>

                                            <path d="M14 3h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5"/>
                                        </svg>

                                    </span>


                                    <span class="admin-bottom-text">
                                        {{ __('Uitloggen') }}
                                    </span>

                                </button>
                            </form>

                        @endif

                    </div>

                </aside>


                {{-- ================================================= --}}
                {{-- MAIN                                              --}}
                {{-- ================================================= --}}

                <main
                    class="admin-main"
                    id="adminContent"
                >

                    <header class="admin-topbar">

                        <div class="admin-topbar-copy">

                            <span class="admin-kicker">
                                {{ __('Mashal Studio administration') }}
                            </span>


                            <h1 class="admin-page-title">
                                @yield(
                                    'page-title',
                                    'Admin dashboard'
                                )
                            </h1>


                            <span class="admin-page-subtitle">
                                @yield(
                                    'page-subtitle',
                                    'Beheer gebruikers, beeldprojecten en accounttoegang binnen Mashal Studio.'
                                )
                            </span>

                        </div>


                        <div class="admin-topbar-actions">

                            <button
                                class="admin-mobile-toggle"
                                id="adminMobileToggle"
                                type="button"
                                aria-label="Adminmenu openen"
                                aria-expanded="false"
                                aria-controls="adminSidebar"
                            >
                                <span class="admin-mobile-toggle-lines"></span>
                            </button>


                            @if ($hasHome)

                                <a
                                    class="admin-button secondary desktop-only"
                                    href="{{ route('home') }}"
                                >
                                    {{ __('Website') }}
                                </a>

                            @endif


                            @if ($hasImagesIndex)

                                <a
                                    class="admin-button secondary desktop-only"
                                    href="{{ route('images.index') }}"
                                >
                                    {{ __('Afbeeldingen') }}
                                </a>

                            @endif


                            @if ($hasUsersCreate)

                                <a
                                    class="admin-button"
                                    href="{{ route('users.create') }}"
                                >
                                    {{ __('+ Gebruiker') }}
                                </a>

                            @endif

                        </div>

                    </header>


                    {{-- ================================================= --}}
                    {{-- BREADCRUMBS                                       --}}
                    {{-- ================================================= --}}

                    <nav
                        class="admin-breadcrumbs"
                        aria-label="Broodkruimelpad"
                    >

                        @if ($hasAdminDashboard)

                            <a
                                href="{{ route('admin.dashboard') }}"
                            >
                                Admin
                            </a>


                            <span class="admin-breadcrumb-separator">
                                /
                            </span>

                        @endif


                        <span>
                            @yield(
                                'breadcrumb',
                                $adminCurrentRoute ?: $adminCurrentPath
                            )
                        </span>

                    </nav>


                    {{-- ================================================= --}}
                    {{-- ALERTS                                            --}}
                    {{-- ================================================= --}}

                    @if (
                        session('success') ||
                        session('status') ||
                        session('warning') ||
                        session('error') ||
                        $errors->any()
                    )

                        <div class="admin-alert-stack">

                            @if (session('success'))

                                <div
                                    class="admin-alert success"
                                    data-admin-alert
                                >

                                    <div>
                                        {{ session('success') }}
                                    </div>


                                    <button
                                        class="admin-alert-close"
                                        type="button"
                                        aria-label="Melding sluiten"
                                        data-admin-alert-close
                                    >
                                        ×
                                    </button>

                                </div>

                            @endif


                            @if (session('status'))

                                <div
                                    class="admin-alert success"
                                    data-admin-alert
                                >

                                    <div>
                                        {{ session('status') }}
                                    </div>


                                    <button
                                        class="admin-alert-close"
                                        type="button"
                                        aria-label="Melding sluiten"
                                        data-admin-alert-close
                                    >
                                        ×
                                    </button>

                                </div>

                            @endif


                            @if (session('warning'))

                                <div
                                    class="admin-alert warning"
                                    data-admin-alert
                                >

                                    <div>
                                        {{ session('warning') }}
                                    </div>


                                    <button
                                        class="admin-alert-close"
                                        type="button"
                                        aria-label="Melding sluiten"
                                        data-admin-alert-close
                                    >
                                        ×
                                    </button>

                                </div>

                            @endif


                            @if (session('error'))

                                <div
                                    class="admin-alert error"
                                    data-admin-alert
                                >

                                    <div>
                                        {{ session('error') }}
                                    </div>


                                    <button
                                        class="admin-alert-close"
                                        type="button"
                                        aria-label="Melding sluiten"
                                        data-admin-alert-close
                                    >
                                        ×
                                    </button>

                                </div>

                            @endif


                            @if ($errors->any())

                                <div
                                    class="admin-alert error"
                                    data-admin-alert
                                >

                                    <div>

                                        <strong>
                                            {{ __('Controleer onderstaande gegevens:') }}
                                        </strong>


                                        <ul>
                                            @foreach ($errors->all() as $error)

                                                <li>
                                                    {{ $error }}
                                                </li>

                                            @endforeach
                                        </ul>

                                    </div>


                                    <button
                                        class="admin-alert-close"
                                        type="button"
                                        aria-label="Melding sluiten"
                                        data-admin-alert-close
                                    >
                                        ×
                                    </button>

                                </div>

                            @endif

                        </div>

                    @endif


                    {{-- ================================================= --}}
                    {{-- PAGE CONTENT                                      --}}
                    {{-- ================================================= --}}

                    <div class="admin-content">
                        @yield('content')
                    </div>

                </main>

            </div>


        @else

            {{-- ========================================================= --}}
            {{-- LOGGED IN BUT NOT ADMIN                                   --}}
            {{-- ========================================================= --}}

            <main class="admin-access-page">

                <section class="admin-access-card">

                    <div class="admin-access-icon danger">
                        !
                    </div>


                    <h1>
                        {{ __('Geen administratorrechten') }}
                    </h1>


                    <p>
                        {{ __('Je bent ingelogd, maar dit account heeft geen toegang tot de Mashal Studio adminomgeving.') }}
                    </p>


                    @if ($hasAccount)

                        <a
                            class="admin-button"
                            href="{{ route('account') }}"
                        >
                            {{ __('Naar mijn account') }}
                        </a>

                    @elseif ($hasHome)

                        <a
                            class="admin-button"
                            href="{{ route('home') }}"
                        >
                            {{ __('Naar Mashal Studio') }}
                        </a>

                    @endif

                </section>

            </main>

        @endif


    @else

        {{-- ============================================================= --}}
        {{-- NOT LOGGED IN                                                 --}}
        {{-- ============================================================= --}}

        <main class="admin-access-page">

            <section class="admin-access-card">

                <div class="admin-access-icon">
                    M
                </div>


                <h1>
                    {{ __('Mashal Studio Admin') }}
                </h1>


                <p>
                    {{ __('Log in met een administratoraccount om gebruikers, beeldprojecten en de Studio-omgeving te beheren.') }}
                </p>


                @if ($hasLogin)

                    <a
                        class="admin-button"
                        href="{{ route('login') }}"
                    >
                        {{ __('Inloggen') }}
                    </a>

                @endif

            </section>

        </main>

    @endauth


    {{-- ============================================================= --}}
    {{-- TOAST REGION                                                   --}}
    {{-- ============================================================= --}}

    <div
        class="admin-toast-region"
        id="adminToastRegion"
        aria-live="polite"
        aria-atomic="true"
    ></div>


    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {

                const body =
                    document.body;

                const sidebar =
                    document.getElementById(
                        'adminSidebar'
                    );

                const pinButton =
                    document.getElementById(
                        'adminSidebarPin'
                    );

                const toggle =
                    document.getElementById(
                        'adminMobileToggle'
                    );

                const overlay =
                    document.getElementById(
                        'adminMobileOverlay'
                    );

                const progress =
                    document.getElementById(
                        'adminProgressBar'
                    );

                const sidebarSearch =
                    document.getElementById(
                        'adminSidebarSearch'
                    );

                const sidebarMenuItems =
                    Array.from(
                        document.querySelectorAll(
                            '[data-admin-menu-item]'
                        )
                    );

                let menuOpen = false;


                /*
                |--------------------------------------------------------------------------
                | SIDEBAR PIN
                |--------------------------------------------------------------------------
                */

                function applyStoredPinState() {

                    if (!sidebar) {
                        return;
                    }


                    if (window.innerWidth <= 900) {

                        sidebar.classList.remove(
                            'is-pinned'
                        );

                        body.classList.remove(
                            'admin-sidebar-pinned'
                        );

                        return;
                    }


                    const pinned =
                        localStorage.getItem(
                            'mashalAdminSidebarPinned'
                        ) === '1';


                    sidebar.classList.toggle(
                        'is-pinned',
                        pinned
                    );


                    body.classList.toggle(
                        'admin-sidebar-pinned',
                        pinned
                    );


                    if (pinButton) {

                        pinButton.setAttribute(
                            'aria-pressed',
                            String(pinned)
                        );


                        pinButton.setAttribute(
                            'aria-label',
                            pinned
                                ? 'Sidebar losmaken'
                                : 'Sidebar vastzetten'
                        );


                        pinButton.title =
                            pinned
                                ? 'Sidebar losmaken'
                                : 'Sidebar vastzetten';
                    }
                }


                if (pinButton) {

                    pinButton.addEventListener(
                        'click',
                        function () {

                            if (
                                window.innerWidth <= 900 ||
                                !sidebar
                            ) {
                                return;
                            }


                            const pinned =
                                !sidebar.classList.contains(
                                    'is-pinned'
                                );


                            sidebar.classList.toggle(
                                'is-pinned',
                                pinned
                            );


                            body.classList.toggle(
                                'admin-sidebar-pinned',
                                pinned
                            );


                            localStorage.setItem(
                                'mashalAdminSidebarPinned',
                                pinned ? '1' : '0'
                            );


                            pinButton.setAttribute(
                                'aria-pressed',
                                String(pinned)
                            );


                            pinButton.setAttribute(
                                'aria-label',
                                pinned
                                    ? 'Sidebar losmaken'
                                    : 'Sidebar vastzetten'
                            );


                            pinButton.title =
                                pinned
                                    ? 'Sidebar losmaken'
                                    : 'Sidebar vastzetten';
                        }
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | MOBILE MENU
                |--------------------------------------------------------------------------
                */

                function setMenu(open) {

                    menuOpen =
                        Boolean(open);


                    if (sidebar) {

                        sidebar.classList.toggle(
                            'is-open',
                            menuOpen
                        );
                    }


                    if (toggle) {

                        toggle.setAttribute(
                            'aria-expanded',
                            menuOpen
                                ? 'true'
                                : 'false'
                        );


                        toggle.setAttribute(
                            'aria-label',
                            menuOpen
                                ? 'Adminmenu sluiten'
                                : 'Adminmenu openen'
                        );
                    }


                    if (overlay) {

                        overlay.classList.toggle(
                            'is-open',
                            menuOpen
                        );


                        overlay.setAttribute(
                            'aria-hidden',
                            menuOpen
                                ? 'false'
                                : 'true'
                        );
                    }


                    body.classList.toggle(
                        'admin-mobile-open',
                        menuOpen
                    );
                }


                if (toggle) {

                    toggle.addEventListener(
                        'click',
                        function () {

                            setMenu(
                                !menuOpen
                            );
                        }
                    );
                }


                if (overlay) {

                    overlay.addEventListener(
                        'click',
                        function () {

                            setMenu(false);
                        }
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | CLOSE AFTER NAVIGATION ON MOBILE
                |--------------------------------------------------------------------------
                */

                if (sidebar) {

                    sidebar
                        .querySelectorAll('a')
                        .forEach(
                            function (link) {

                                link.addEventListener(
                                    'click',
                                    function () {

                                        if (
                                            window.innerWidth <= 900
                                        ) {

                                            setMenu(false);
                                        }
                                    }
                                );
                            }
                        );
                }


                /*
                |--------------------------------------------------------------------------
                | ESCAPE
                |--------------------------------------------------------------------------
                */

                document.addEventListener(
                    'keydown',
                    function (event) {

                        if (
                            event.key === 'Escape'
                        ) {

                            setMenu(false);
                        }
                    }
                );


                /*
                |--------------------------------------------------------------------------
                | SIDEBAR SEARCH
                |--------------------------------------------------------------------------
                */

                if (sidebarSearch) {

                    sidebarSearch.addEventListener(
                        'input',
                        function () {

                            const query =
                                sidebarSearch
                                    .value
                                    .trim()
                                    .toLowerCase();


                            sidebarMenuItems.forEach(
                                function (item) {

                                    const searchText =
                                        (
                                            item.getAttribute(
                                                'data-search'
                                            ) || ''
                                        )
                                        .toLowerCase();


                                    const visible =
                                        !query ||
                                        searchText.includes(
                                            query
                                        ) ||
                                        item
                                            .textContent
                                            .toLowerCase()
                                            .includes(
                                                query
                                            );


                                    item.style.display =
                                        visible
                                            ? ''
                                            : 'none';
                                }
                            );
                        }
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | PROGRESS BAR
                |--------------------------------------------------------------------------
                */

                function updateProgress() {

                    if (!progress) {
                        return;
                    }


                    const doc =
                        document.documentElement;


                    const scrollTop =
                        window.scrollY ||
                        doc.scrollTop;


                    const scrollable =
                        doc.scrollHeight -
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


                    progress.style.width =
                        percentage + '%';
                }


                /*
                |--------------------------------------------------------------------------
                | TOAST
                |--------------------------------------------------------------------------
                */

                function showToast(message) {

                    const region =
                        document.getElementById(
                            'adminToastRegion'
                        );


                    if (
                        !region ||
                        !message
                    ) {
                        return;
                    }


                    const toast =
                        document.createElement(
                            'div'
                        );


                    toast.className =
                        'admin-toast';


                    toast.textContent =
                        message;


                    region.appendChild(
                        toast
                    );


                    requestAnimationFrame(
                        function () {

                            toast.classList.add(
                                'show'
                            );
                        }
                    );


                    window.setTimeout(
                        function () {

                            toast.classList.remove(
                                'show'
                            );


                            window.setTimeout(
                                function () {

                                    toast.remove();
                                },
                                220
                            );
                        },
                        3200
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | ALERT CLOSE
                |--------------------------------------------------------------------------
                */

                document
                    .querySelectorAll(
                        '[data-admin-alert-close]'
                    )
                    .forEach(
                        function (button) {

                            button.addEventListener(
                                'click',
                                function () {

                                    const alert =
                                        button.closest(
                                            '[data-admin-alert]'
                                        );


                                    if (!alert) {
                                        return;
                                    }


                                    alert.remove();
                                }
                            );
                        }
                    );


                /*
                |--------------------------------------------------------------------------
                | FORM LOADING
                |--------------------------------------------------------------------------
                */

                document
                    .querySelectorAll('form')
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


                /*
                |--------------------------------------------------------------------------
                | COPY HELPER
                |--------------------------------------------------------------------------
                */

                document
                    .querySelectorAll(
                        '[data-copy]'
                    )
                    .forEach(
                        function (button) {

                            button.addEventListener(
                                'click',
                                async function () {

                                    const value =
                                        button.getAttribute(
                                            'data-copy'
                                        );


                                    if (!value) {
                                        return;
                                    }


                                    try {

                                        await navigator
                                            .clipboard
                                            .writeText(
                                                value
                                            );


                                        showToast(
                                            'Gekopieerd naar klembord.'
                                        );

                                    } catch (error) {

                                        showToast(
                                            'Kopiëren is niet gelukt.'
                                        );
                                    }
                                }
                            );
                        }
                    );


                /*
                |--------------------------------------------------------------------------
                | RESIZE
                |--------------------------------------------------------------------------
                */

                window.addEventListener(
                    'resize',
                    function () {

                        if (
                            window.innerWidth > 900
                        ) {

                            setMenu(false);

                            applyStoredPinState();

                        } else {

                            body.classList.remove(
                                'admin-sidebar-pinned'
                            );
                        }
                    }
                );


                /*
                |--------------------------------------------------------------------------
                | INITIAL STATE
                |--------------------------------------------------------------------------
                */

                applyStoredPinState();

                updateProgress();


                window.addEventListener(
                    'scroll',
                    updateProgress,
                    {
                        passive: true
                    }
                );


                /*
                |--------------------------------------------------------------------------
                | BROWSER BACK CACHE
                |--------------------------------------------------------------------------
                */

                window.addEventListener(
                    'pageshow',
                    function () {

                        document
                            .querySelectorAll(
                                '.is-loading'
                            )
                            .forEach(
                                function (element) {

                                    element
                                        .classList
                                        .remove(
                                            'is-loading'
                                        );


                                    element
                                        .removeAttribute(
                                            'aria-busy'
                                        );
                                }
                            );
                    }
                );

            }
        );
    </script>


    @stack('scripts')

    {{-- Keep the operator chat accessible on admin screens without showing the visitor widget. --}}
    @if ($adminIsAllowed && ! request()->routeIs('admin.live-chat.*') && \Illuminate\Support\Facades\Route::has('admin.live-chat.index'))
        @include('site.partials.admin-live-chat-launcher')
    @endif

</body>
</html>
