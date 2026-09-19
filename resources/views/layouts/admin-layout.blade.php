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
                $adminInitials =
                    (string) $adminUser->initials();
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

            if (
                method_exists($adminUser, 'avatarUrl')
            ) {
                $adminAvatarUrl =
                    $adminUser->avatarUrl();
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
    @endphp

    <style>
        :root {
            --admin-bg: #07080b;
            --admin-bg-soft: #0a0c10;
            --admin-panel: #0e1116;
            --admin-panel-2: #12161d;
            --admin-panel-3: #171c24;

            --admin-text: #f6f7f8;
            --admin-text-soft: #d6dae0;

            --admin-muted: #8a929c;
            --admin-muted-2: #646c76;
            --admin-muted-3: #4a515a;

            --admin-line: rgba(255, 255, 255, .07);
            --admin-line-strong: rgba(255, 255, 255, .12);

            --admin-gold: #e2b36c;
            --admin-gold-light: #f1d194;
            --admin-gold-deep: #b77e3b;
            --admin-gold-soft: rgba(226, 179, 108, .07);

            --admin-blue: #78b7ff;
            --admin-blue-soft: rgba(120, 183, 255, .08);

            --admin-green: #69d894;
            --admin-green-soft: rgba(105, 216, 148, .08);

            --admin-red: #f08383;
            --admin-red-soft: rgba(240, 131, 131, .08);

            --admin-warning: #edc270;
            --admin-warning-soft: rgba(237, 194, 112, .08);

            --admin-sidebar: 286px;

            --admin-radius-sm: 12px;
            --admin-radius: 18px;
            --admin-radius-lg: 25px;

            --admin-shadow:
                0 32px 90px rgba(0, 0, 0, .34);

            --admin-shadow-soft:
                0 18px 48px rgba(0, 0, 0, .22);

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
            color: var(--admin-text);
            background:
                radial-gradient(
                    circle at 84% 6%,
                    rgba(226, 179, 108, .055),
                    transparent 26rem
                ),
                radial-gradient(
                    circle at 16% 86%,
                    rgba(120, 183, 255, .035),
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
            outline: 2px solid rgba(241, 209, 148, .75);
            outline-offset: 3px;
        }

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

        .admin-progress {
            position: fixed;
            z-index: 3000;
            left: 0;
            top: 0;
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
                0 0 20px rgba(226, 179, 108, .30);
            transition: width .08s linear;
        }

        .admin-app {
            min-height: 100vh;
            display: flex;
        }

        .admin-sidebar {
            position: sticky;
            z-index: 1100;
            top: 0;
            width: var(--admin-sidebar);
            height: 100vh;
            flex: 0 0 var(--admin-sidebar);
            padding: 22px 16px 18px;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            overscroll-behavior: contain;
            border-right: 1px solid var(--admin-line);
            background:
                radial-gradient(
                    circle at 24% 0%,
                    rgba(226, 179, 108, .06),
                    transparent 16rem
                ),
                rgba(10, 12, 16, .96);
            box-shadow:
                18px 0 60px rgba(0, 0, 0, .11);
            backdrop-filter: blur(18px);
            scrollbar-width: thin;
            scrollbar-color:
                rgba(226, 179, 108, .18)
                transparent;
        }

        .admin-brand {
            min-height: 58px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--admin-text);
            text-decoration: none;
        }

        .admin-brand-mark {
            position: relative;
            width: 43px;
            height: 43px;
            flex: 0 0 43px;
            display: grid;
            place-items: center;
            overflow: hidden;
            border: 1px solid rgba(226, 179, 108, .18);
            border-radius: 14px;
            color: var(--admin-gold-light);
            background:
                linear-gradient(
                    145deg,
                    rgba(226, 179, 108, .12),
                    rgba(226, 179, 108, .025)
                );
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, .05),
                0 15px 34px rgba(0, 0, 0, .20);
            font-size: 14px;
            font-weight: 950;
            letter-spacing: -.06em;
        }

        .admin-brand-mark::after {
            content: "";
            position: absolute;
            width: 26px;
            height: 26px;
            right: -14px;
            top: -14px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .12);
            filter: blur(6px);
        }

        .admin-brand-copy {
            min-width: 0;
        }

        .admin-brand-copy strong,
        .admin-brand-copy span {
            display: block;
        }

        .admin-brand-copy strong {
            color: #f3f4f5;
            font-size: 13px;
            font-weight: 950;
            letter-spacing: -.025em;
        }

        .admin-brand-copy span {
            margin-top: 4px;
            color: #656c76;
            font-size: 7px;
            font-weight: 900;
            letter-spacing: .17em;
            text-transform: uppercase;
        }

        .admin-workspace-card {
            margin-top: 15px;
            padding: 14px;
            border: 1px solid rgba(226, 179, 108, .09);
            border-radius: 15px;
            background:
                linear-gradient(
                    145deg,
                    rgba(226, 179, 108, .045),
                    rgba(255, 255, 255, .008)
                );
        }

        .admin-workspace-card small,
        .admin-workspace-card strong,
        .admin-workspace-card span {
            display: block;
        }

        .admin-workspace-card small {
            color: #6a5a42;
            font-size: 7px;
            font-weight: 950;
            letter-spacing: .13em;
            text-transform: uppercase;
        }

        .admin-workspace-card strong {
            margin-top: 7px;
            color: #d9dce0;
            font-size: 10px;
        }

        .admin-workspace-card span {
            margin-top: 4px;
            color: #686f79;
            font-size: 8px;
            line-height: 1.55;
        }

        .admin-nav {
            margin-top: 22px;
            display: grid;
            gap: 4px;
        }

        .admin-nav-label {
            margin: 16px 9px 7px;
            color: #4d545e;
            font-size: 7px;
            font-weight: 950;
            letter-spacing: .17em;
            text-transform: uppercase;
        }

        .admin-nav-label:first-child {
            margin-top: 0;
        }

        .admin-nav-link {
            position: relative;
            min-height: 45px;
            padding: 0 11px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid transparent;
            border-radius: 12px;
            color: #808792;
            text-decoration: none;
            font-size: 9px;
            font-weight: 850;
            transition:
                transform .2s ease,
                color .2s ease,
                border-color .2s ease,
                background .2s ease;
        }

        .admin-nav-link:hover {
            transform: translateX(2px);
            border-color: rgba(226, 179, 108, .09);
            color: #d6dade;
            background: rgba(226, 179, 108, .028);
        }

        .admin-nav-link.active {
            border-color: rgba(226, 179, 108, .13);
            color: #e4bc7d;
            background:
                linear-gradient(
                    90deg,
                    rgba(226, 179, 108, .075),
                    rgba(226, 179, 108, .018)
                );
        }

        .admin-nav-link.active::before {
            content: "";
            position: absolute;
            left: -1px;
            top: 10px;
            bottom: 10px;
            width: 2px;
            border-radius: 999px;
            background: var(--admin-gold);
            box-shadow:
                0 0 12px rgba(226, 179, 108, .34);
        }

        .admin-nav-icon {
            width: 29px;
            height: 29px;
            flex: 0 0 29px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, .055);
            border-radius: 9px;
            color: currentColor;
            background: rgba(255, 255, 255, .012);
            font-size: 9px;
            font-weight: 950;
        }

        .admin-nav-link.active .admin-nav-icon {
            border-color: rgba(226, 179, 108, .10);
            background: rgba(226, 179, 108, .035);
        }

        .admin-nav-copy {
            min-width: 0;
            flex: 1;
        }

        .admin-nav-copy strong,
        .admin-nav-copy span {
            display: block;
        }

        .admin-nav-copy strong {
            color: inherit;
            font-size: 9px;
        }

        .admin-nav-copy span {
            margin-top: 1px;
            color: #515862;
            font-size: 7px;
            font-weight: 750;
        }

        .admin-nav-count {
            min-width: 21px;
            height: 21px;
            padding: 0 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(226, 179, 108, .10);
            border-radius: 999px;
            color: #b68d51;
            background: rgba(226, 179, 108, .035);
            font-size: 7px;
            font-weight: 950;
        }

        .admin-sidebar-footer {
            margin-top: auto;
            padding-top: 22px;
        }

        .admin-profile {
            padding: 15px;
            border: 1px solid var(--admin-line);
            border-radius: 17px;
            background:
                linear-gradient(
                    145deg,
                    rgba(255, 255, 255, .025),
                    rgba(255, 255, 255, .008)
                );
        }

        .admin-profile-head {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-profile-avatar {
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            display: grid;
            place-items: center;
            overflow: hidden;
            border: 1px solid rgba(226, 179, 108, .15);
            border-radius: 13px;
            color: #e4ba79;
            background: rgba(226, 179, 108, .055);
            font-size: 12px;
            font-weight: 950;
        }

        .admin-profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .admin-profile-copy {
            min-width: 0;
            flex: 1;
        }

        .admin-profile-copy strong,
        .admin-profile-copy span {
            display: block;
        }

        .admin-profile-copy strong {
            overflow: hidden;
            color: #dfe2e5;
            font-size: 10px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .admin-profile-copy span {
            margin-top: 3px;
            overflow: hidden;
            color: #666d77;
            font-size: 7px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .admin-role {
            margin-top: 11px;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #7dc998;
            font-size: 7px;
            font-weight: 950;
            letter-spacing: .09em;
            text-transform: uppercase;
        }

        .admin-role::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--admin-green);
            box-shadow:
                0 0 0 4px rgba(105, 216, 148, .05);
        }

        .admin-profile-actions {
            margin-top: 13px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 7px;
        }

        .admin-profile-action {
            width: 100%;
            min-height: 37px;
            padding: 0 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, .065);
            border-radius: 10px;
            color: #8c939d;
            background: rgba(255, 255, 255, .015);
            text-decoration: none;
            font-size: 7px;
            font-weight: 900;
            cursor: pointer;
            transition:
                border-color .2s ease,
                color .2s ease,
                background .2s ease;
        }

        .admin-profile-action:hover {
            border-color: rgba(226, 179, 108, .13);
            color: #d3d6da;
            background: rgba(226, 179, 108, .03);
        }

        .admin-profile-action.logout {
            border-color: rgba(240, 131, 131, .08);
            color: #b97474;
            background: rgba(240, 131, 131, .02);
        }

        .admin-profile-action.logout:hover {
            border-color: rgba(240, 131, 131, .14);
            color: #dc9393;
            background: rgba(240, 131, 131, .04);
        }

        .admin-profile-actions form {
            margin: 0;
        }

        .admin-main {
            min-width: 0;
            flex: 1;
            padding:
                24px
                clamp(22px, 3vw, 46px)
                60px;
        }

        .admin-topbar {
            position: relative;
            z-index: 30;
            min-height: 76px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            border-bottom: 1px solid var(--admin-line);
        }

        .admin-topbar-copy {
            min-width: 0;
        }

        .admin-kicker {
            display: block;
            margin-bottom: 6px;
            color: #886a3f;
            font-size: 7px;
            font-weight: 950;
            letter-spacing: .15em;
            text-transform: uppercase;
        }

        .admin-page-title {
            margin: 0;
            color: #f2f3f4;
            font-size: clamp(27px, 3.2vw, 42px);
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

        .admin-button {
            min-height: 40px;
            padding: 0 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid transparent;
            border-radius: 11px;
            color: #171009;
            background:
                linear-gradient(
                    135deg,
                    var(--admin-gold-light),
                    #d49c52
                );
            box-shadow:
                0 13px 30px rgba(226, 179, 108, .12);
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
            transform: translateY(-1px);
            box-shadow:
                0 17px 38px rgba(226, 179, 108, .18);
        }

        .admin-button.secondary {
            border-color: var(--admin-line);
            color: #aeb4bc;
            background: rgba(255, 255, 255, .018);
            box-shadow: none;
        }

        .admin-button.secondary:hover {
            border-color: rgba(226, 179, 108, .13);
            color: #e1e3e6;
            background: rgba(226, 179, 108, .03);
        }

        .admin-button.danger {
            border-color: rgba(240, 131, 131, .12);
            color: #d98989;
            background: rgba(240, 131, 131, .035);
            box-shadow: none;
        }

        .admin-button.danger:hover {
            background: rgba(240, 131, 131, .06);
        }

        .admin-mobile-toggle {
            width: 40px;
            height: 40px;
            display: none;
            place-items: center;
            border: 1px solid var(--admin-line);
            border-radius: 11px;
            color: #d7dade;
            background: rgba(255, 255, 255, .018);
            cursor: pointer;
        }

        .admin-mobile-toggle-lines,
        .admin-mobile-toggle-lines::before,
        .admin-mobile-toggle-lines::after {
            width: 17px;
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

        body.admin-mobile-open .admin-mobile-toggle-lines {
            background: transparent;
        }

        body.admin-mobile-open .admin-mobile-toggle-lines::before {
            top: 0;
            transform: rotate(45deg);
        }

        body.admin-mobile-open .admin-mobile-toggle-lines::after {
            top: 0;
            transform: rotate(-45deg);
        }

        .admin-mobile-overlay {
            position: fixed;
            z-index: 1050;
            inset: 0;
            display: none;
            background: rgba(0, 0, 0, .58);
            backdrop-filter: blur(4px);
            opacity: 0;
            pointer-events: none;
            transition: opacity .2s ease;
        }

        .admin-mobile-overlay.is-open {
            opacity: 1;
            pointer-events: auto;
        }

        .admin-breadcrumbs {
            margin: -4px 0 18px;
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

        .admin-alert-stack {
            margin-bottom: 18px;
            display: grid;
            gap: 9px;
        }

        .admin-alert {
            position: relative;
            padding: 14px 42px 14px 15px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            border: 1px solid var(--admin-line);
            border-radius: 13px;
            color: #9fa6af;
            background: rgba(255, 255, 255, .015);
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
            border-color: rgba(105, 216, 148, .13);
            color: #a4dcb6;
            background: rgba(105, 216, 148, .04);
        }

        .admin-alert.success::before {
            background: var(--admin-green);
        }

        .admin-alert.error {
            border-color: rgba(240, 131, 131, .13);
            color: #dca0a0;
            background: rgba(240, 131, 131, .04);
        }

        .admin-alert.error::before {
            background: var(--admin-red);
        }

        .admin-alert.warning {
            border-color: rgba(237, 194, 112, .13);
            color: #d9bd87;
            background: rgba(237, 194, 112, .04);
        }

        .admin-alert.warning::before {
            background: var(--admin-warning);
        }

        .admin-alert-close {
            position: absolute;
            right: 9px;
            top: 9px;
            width: 27px;
            height: 27px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, .055);
            border-radius: 8px;
            color: #606873;
            background: rgba(255, 255, 255, .012);
            cursor: pointer;
        }

        .admin-alert ul {
            margin: 7px 0 0 16px;
            padding: 0;
        }

        .admin-content {
            min-width: 0;
        }

        .admin-stats {
            margin: 18px 0;
            display: grid;
            grid-template-columns:
                repeat(4, minmax(150px, 1fr));
            gap: 13px;
        }

        .admin-stat {
            position: relative;
            min-height: 132px;
            padding: 18px;
            overflow: hidden;
            border: 1px solid var(--admin-line);
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
            border: 1px solid rgba(226, 179, 108, .06);
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

        .admin-panel {
            margin-top: 18px;
            padding: 23px;
            border: 1px solid var(--admin-line);
            border-radius: 22px;
            background:
                linear-gradient(
                    145deg,
                    rgba(255, 255, 255, .025),
                    rgba(255, 255, 255, .006)
                ),
                var(--admin-panel);
            box-shadow:
                0 16px 45px rgba(0, 0, 0, .13);
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

        .admin-empty-state {
            padding: 48px 24px;
            text-align: center;
            border: 1px dashed rgba(255,255,255,.08);
            border-radius: 17px;
            color: #6f7680;
            background: rgba(255,255,255,.008);
        }

        .admin-empty-state strong {
            display: block;
            margin-bottom: 7px;
            color: #cfd3d7;
            font-size: 13px;
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
            border: 1px solid var(--admin-line);
            border-radius: 15px;
            background: rgba(0, 0, 0, .08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 12px;
            border-bottom: 1px solid var(--admin-line);
            color: #636a74;
            background: rgba(255, 255, 255, .016);
            text-align: left;
            font-size: 7px;
            font-weight: 950;
            letter-spacing: .11em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        td {
            padding: 13px 12px;
            border-bottom: 1px solid rgba(255, 255, 255, .04);
            color: #bcc1c8;
            font-size: 9px;
            vertical-align: middle;
        }

        tbody tr:last-child td {
            border-bottom: 0;
        }

        tbody tr:hover {
            background: rgba(226, 179, 108, .017);
        }

        .badge {
            min-height: 26px;
            padding: 0 9px;
            display: inline-flex;
            align-items: center;
            border: 1px solid var(--admin-line);
            border-radius: 999px;
            color: #989fa8;
            background: rgba(255, 255, 255, .015);
            font-size: 7px;
            font-weight: 900;
            white-space: nowrap;
        }

        .badge.admin {
            border-color: rgba(120, 183, 255, .12);
            color: #9ec8f7;
            background: rgba(120, 183, 255, .04);
        }

        .badge.success {
            border-color: rgba(105, 216, 148, .12);
            color: #99d8af;
            background: rgba(105, 216, 148, .04);
        }

        .badge.warning {
            border-color: rgba(237, 194, 112, .12);
            color: #d7ba82;
            background: rgba(237, 194, 112, .04);
        }

        .badge.danger {
            border-color: rgba(240, 131, 131, .12);
            color: #d99595;
            background: rgba(240, 131, 131, .04);
        }

        .form-wrap {
            max-width: 820px;
            padding: 25px;
            border: 1px solid var(--admin-line);
            border-radius: 21px;
            background: var(--admin-panel);
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

        input,
        select,
        textarea {
            width: 100%;
            padding: 12px 13px;
            border: 1px solid var(--admin-line);
            border-radius: 11px;
            outline: none;
            color: #e3e5e8;
            background: var(--admin-panel-2);
            font-size: 10px;
            transition:
                border-color .2s ease,
                background .2s ease,
                box-shadow .2s ease;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: rgba(226, 179, 108, .28);
            background: #151a21;
            box-shadow:
                0 0 0 4px rgba(226, 179, 108, .04);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .admin-access-page {
            min-height: 100vh;
            padding: 24px;
            display: grid;
            place-items: center;
            background:
                radial-gradient(
                    circle at 50% 0%,
                    rgba(226, 179, 108, .08),
                    transparent 27rem
                ),
                var(--admin-bg);
        }

        .admin-access-card {
            width: min(100%, 520px);
            padding: 34px;
            border: 1px solid var(--admin-line);
            border-radius: 24px;
            background:
                linear-gradient(
                    145deg,
                    rgba(255, 255, 255, .035),
                    rgba(255, 255, 255, .007)
                ),
                var(--admin-panel);
            box-shadow: var(--admin-shadow);
            text-align: center;
        }

        .admin-access-icon {
            width: 62px;
            height: 62px;
            margin: 0 auto 20px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(226, 179, 108, .15);
            border-radius: 18px;
            color: var(--admin-gold-light);
            background: rgba(226, 179, 108, .045);
            font-size: 19px;
            font-weight: 950;
        }

        .admin-access-icon.danger {
            border-color: rgba(240, 131, 131, .13);
            color: #dc9696;
            background: rgba(240, 131, 131, .04);
        }

        .admin-access-card h1 {
            margin: 0;
            color: #edf0f2;
            font-size: 29px;
            letter-spacing: -.045em;
        }

        .admin-access-card p {
            margin: 13px 0 23px;
            color: #767d87;
            font-size: 10px;
            line-height: 1.75;
        }

        .admin-toast-region {
            position: fixed;
            z-index: 2500;
            right: 17px;
            top: 17px;
            width: min(360px, calc(100vw - 34px));
            display: grid;
            gap: 8px;
            pointer-events: none;
        }

        .admin-toast {
            padding: 13px 15px;
            border: 1px solid var(--admin-line);
            border-radius: 12px;
            color: #b9bec5;
            background: rgba(16, 19, 24, .96);
            box-shadow: var(--admin-shadow-soft);
            font-size: 9px;
            line-height: 1.55;
            opacity: 0;
            transform: translateY(-6px);
            transition:
                opacity .2s ease,
                transform .2s ease;
        }

        .admin-toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .is-loading {
            opacity: .62 !important;
            pointer-events: none !important;
            cursor: wait !important;
        }

        @media (max-width: 1100px) {
            .admin-stats {
                grid-template-columns:
                    repeat(2, minmax(150px, 1fr));
            }
        }

        @media (max-width: 900px) {
            .admin-sidebar {
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;
                height: 100vh;
                transform: translateX(-104%);
                transition:
                    transform .25s cubic-bezier(.2, .7, .2, 1);
            }

            .admin-sidebar.is-open {
                transform: translateX(0);
            }

            .admin-mobile-toggle {
                display: grid;
            }

            .admin-mobile-overlay {
                display: block;
            }

            .admin-main {
                width: 100%;
                padding:
                    18px
                    clamp(16px, 3vw, 26px)
                    45px;
            }

            .admin-topbar {
                min-height: 68px;
            }

            .admin-topbar-actions .desktop-only {
                display: none;
            }
        }

        @media (max-width: 650px) {
            .admin-main {
                padding:
                    14px
                    12px
                    38px;
            }

            .admin-topbar {
                align-items: flex-start;
                flex-direction: column;
                padding: 10px 0 16px;
            }

            .admin-topbar-actions {
                width: 100%;
            }

            .admin-topbar-actions .admin-button {
                flex: 1;
            }

            .admin-stats {
                grid-template-columns: 1fr;
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
                width: min(304px, 88vw);
                flex-basis: auto;
            }

            .admin-access-card {
                padding: 28px 20px;
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
        }

        @media print {
            .admin-sidebar,
            .admin-topbar-actions,
            .admin-mobile-toggle,
            .admin-progress,
            .admin-breadcrumbs {
                display: none !important;
            }

            .admin-main {
                padding: 0;
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
        class="admin-skip-link"
        href="#adminContent"
    >
        Ga naar inhoud
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
                <div
                    class="admin-mobile-overlay"
                    id="adminMobileOverlay"
                    aria-hidden="true"
                ></div>

                <aside
                    class="admin-sidebar"
                    id="adminSidebar"
                    aria-label="Admin navigatie"
                >
                    <a
                        class="admin-brand"
                        href="{{ $hasAdminDashboard ? route('admin.dashboard') : ($hasHome ? route('home') : '#') }}"
                    >
                        <span class="admin-brand-mark">
                            M
                        </span>

                        <span class="admin-brand-copy">
                            <strong>
                                Mashal Studio
                            </strong>

                            <span>
                                Admin workspace
                            </span>
                        </span>
                    </a>

                    <div class="admin-workspace-card">
                        <small>
                            Image platform
                        </small>

                        <strong>
                            Beheer Mashal Studio
                        </strong>

                        <span>
                            Gebruikers, beeldprojecten en accounttoegang vanuit één centrale adminomgeving.
                        </span>
                    </div>

                    <nav class="admin-nav">
                        <div class="admin-nav-label">
                            Overzicht
                        </div>

                        @if ($hasAdminDashboard)
                            <a
                                class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                                href="{{ route('admin.dashboard') }}"
                            >
                                <span class="admin-nav-icon">
                                    ◇
                                </span>

                                <span class="admin-nav-copy">
                                    <strong>
                                        Dashboard
                                    </strong>

                                    <span>
                                        Platformoverzicht
                                    </span>
                                </span>
                            </a>
                        @endif

                        <div class="admin-nav-label">
                            Gebruikers
                        </div>

                        @if ($hasUsersIndex)
                            <a
                                class="admin-nav-link {{ request()->routeIs('users.index') || request()->routeIs('users.edit') ? 'active' : '' }}"
                                href="{{ route('users.index') }}"
                            >
                                <span class="admin-nav-icon">
                                    ◎
                                </span>

                                <span class="admin-nav-copy">
                                    <strong>
                                        Gebruikers beheren
                                    </strong>

                                    <span>
                                        Accounts en rollen
                                    </span>
                                </span>

                                @if ($adminUserCount !== null)
                                    <span class="admin-nav-count">
                                        {{ $adminUserCount > 999 ? '999+' : $adminUserCount }}
                                    </span>
                                @endif
                            </a>
                        @endif

                        @if ($hasUsersCreate)
                            <a
                                class="admin-nav-link {{ request()->routeIs('users.create') ? 'active' : '' }}"
                                href="{{ route('users.create') }}"
                            >
                                <span class="admin-nav-icon">
                                    +
                                </span>

                                <span class="admin-nav-copy">
                                    <strong>
                                        Gebruiker toevoegen
                                    </strong>

                                    <span>
                                        Nieuw account
                                    </span>
                                </span>
                            </a>
                        @endif

                        <div class="admin-nav-label">
                            Image Studio
                        </div>

                        @if ($hasImagesIndex)
                            <a
                                class="admin-nav-link {{ request()->routeIs('images.*') ? 'active' : '' }}"
                                href="{{ route('images.index') }}"
                            >
                                <span class="admin-nav-icon">
                                    ▦
                                </span>

                                <span class="admin-nav-copy">
                                    <strong>
                                        Afbeeldingen
                                    </strong>

                                    <span>
                                        Projectbibliotheek
                                    </span>
                                </span>

                                @if ($adminImageCount !== null)
                                    <span class="admin-nav-count">
                                        {{ $adminImageCount > 999 ? '999+' : $adminImageCount }}
                                    </span>
                                @endif
                            </a>
                        @endif

                        @if ($hasHome)
                            <a
                                class="admin-nav-link"
                                href="{{ route('home') }}#upload"
                            >
                                <span class="admin-nav-icon">
                                    ↑
                                </span>

                                <span class="admin-nav-copy">
                                    <strong>
                                        Nieuwe upload
                                    </strong>

                                    <span>
                                        Studio openen
                                    </span>
                                </span>
                            </a>
                        @endif

                        <div class="admin-nav-label">
                            Account
                        </div>

                        @if ($hasAccount)
                            <a
                                class="admin-nav-link {{ request()->routeIs('account') ? 'active' : '' }}"
                                href="{{ route('account') }}"
                            >
                                <span class="admin-nav-icon">
                                    ○
                                </span>

                                <span class="admin-nav-copy">
                                    <strong>
                                        Mijn account
                                    </strong>

                                    <span>
                                        Profielinstellingen
                                    </span>
                                </span>
                            </a>
                        @endif

                        @if ($hasSecurity)
                            <a
                                class="admin-nav-link {{ request()->routeIs('security.*') ? 'active' : '' }}"
                                href="{{ route('security.index') }}"
                            >
                                <span class="admin-nav-icon">
                                    ◈
                                </span>

                                <span class="admin-nav-copy">
                                    <strong>
                                        Beveiliging
                                    </strong>

                                    <span>
                                        Loginactiviteit
                                    </span>
                                </span>
                            </a>
                        @endif

                        @if ($hasHome)
                            <a
                                class="admin-nav-link"
                                href="{{ route('home') }}"
                            >
                                <span class="admin-nav-icon">
                                    ↗
                                </span>

                                <span class="admin-nav-copy">
                                    <strong>
                                        Website bekijken
                                    </strong>

                                    <span>
                                        Naar Mashal Studio
                                    </span>
                                </span>
                            </a>
                        @endif
                    </nav>

                    <div class="admin-sidebar-footer">
                        <div class="admin-profile">
                            <div class="admin-profile-head">
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
                                </div>
                            </div>

                            <div class="admin-role">
                                Administrator
                            </div>

                            <div class="admin-profile-actions">
                                @if ($hasAccount)
                                    <a
                                        class="admin-profile-action"
                                        href="{{ route('account') }}"
                                    >
                                        Account
                                    </a>
                                @endif

                                @if ($hasLogout)
                                    <form
                                        method="POST"
                                        action="{{ route('logout') }}"
                                    >
                                        @csrf

                                        <button
                                            class="admin-profile-action logout"
                                            type="submit"
                                        >
                                            Uitloggen
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </aside>

                <main
                    class="admin-main"
                    id="adminContent"
                >
                    <header class="admin-topbar">
                        <div class="admin-topbar-copy">
                            <span class="admin-kicker">
                                Mashal Studio administration
                            </span>

                            <h1 class="admin-page-title">
                                @yield('page-title', 'Admin dashboard')
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
                                    Website
                                </a>
                            @endif

                            @if ($hasImagesIndex)
                                <a
                                    class="admin-button secondary desktop-only"
                                    href="{{ route('images.index') }}"
                                >
                                    Afbeeldingen
                                </a>
                            @endif

                            @if ($hasUsersCreate)
                                <a
                                    class="admin-button"
                                    href="{{ route('users.create') }}"
                                >
                                    + Gebruiker
                                </a>
                            @endif
                        </div>
                    </header>

                    <nav
                        class="admin-breadcrumbs"
                        aria-label="Broodkruimelpad"
                    >
                        @if ($hasAdminDashboard)
                            <a href="{{ route('admin.dashboard') }}">
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
                                            Controleer onderstaande gegevens:
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

                    <div class="admin-content">
                        @yield('content')
                    </div>
                </main>
            </div>
        @else
            <main class="admin-access-page">
                <section class="admin-access-card">
                    <div class="admin-access-icon danger">
                        !
                    </div>

                    <h1>
                        Geen administratorrechten
                    </h1>

                    <p>
                        Je bent ingelogd, maar dit account heeft geen toegang tot de Mashal Studio adminomgeving.
                    </p>

                    @if ($hasAccount)
                        <a
                            class="admin-button"
                            href="{{ route('account') }}"
                        >
                            Naar mijn account
                        </a>
                    @elseif ($hasHome)
                        <a
                            class="admin-button"
                            href="{{ route('home') }}"
                        >
                            Naar Mashal Studio
                        </a>
                    @endif
                </section>
            </main>
        @endif
    @else
        <main class="admin-access-page">
            <section class="admin-access-card">
                <div class="admin-access-icon">
                    M
                </div>

                <h1>
                    Mashal Studio Admin
                </h1>

                <p>
                    Log in met een administratoraccount om gebruikers, beeldprojecten en de Studio-omgeving te beheren.
                </p>

                @if ($hasLogin)
                    <a
                        class="admin-button"
                        href="{{ route('login') }}"
                    >
                        Inloggen
                    </a>
                @endif
            </section>
        </main>
    @endauth

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

                let menuOpen =
                    false;

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
                        document.createElement('div');

                    toast.className =
                        'admin-toast';

                    toast.textContent =
                        message;

                    region.appendChild(toast);

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

                if (sidebar) {
                    sidebar
                        .querySelectorAll('a')
                        .forEach(
                            function (link) {
                                link.addEventListener(
                                    'click',
                                    function () {
                                        if (
                                            window.innerWidth <=
                                            900
                                        ) {
                                            setMenu(false);
                                        }
                                    }
                                );
                            }
                        );
                }

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

                window.addEventListener(
                    'resize',
                    function () {
                        if (
                            window.innerWidth >
                            900
                        ) {
                            setMenu(false);
                        }
                    }
                );

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
                                        await navigator.clipboard.writeText(
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

                updateProgress();

                window.addEventListener(
                    'scroll',
                    updateProgress,
                    {
                        passive: true
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
            }
        );
    </script>

    @stack('scripts')
</body>
</html>
