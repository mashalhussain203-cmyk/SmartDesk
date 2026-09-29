@php
    $admin = auth()->user();

    $dashboardUrl = \Illuminate\Support\Facades\Route::has('admin.dashboard')
        ? route('admin.dashboard')
        : (
            \Illuminate\Support\Facades\Route::has('dashboard')
                ? route('dashboard')
                : url('/admin')
        );

    $totalUsersSidebar = isset($totalUsers)
        ? $totalUsers
        : (
            isset($users)
                ? $users->count()
                : null
        );

    $totalOrdersSidebar = isset($totalOrders)
        ? $totalOrders
        : null;
@endphp

<style>
    :root {
        --mas-sidebar-small: 84px;
        --mas-sidebar-large: 270px;

        --mas-bg: #08090b;
        --mas-panel: #101318;
        --mas-panel-soft: #15191f;
        --mas-panel-hover: #191e25;

        --mas-text: #f6f4ef;
        --mas-muted: #858b93;
        --mas-muted-2: #626870;

        --mas-gold: #d7a45f;
        --mas-gold-light: #f1c983;
        --mas-gold-dark: #9c6d34;

        --mas-red: #ef8f8f;
        --mas-green: #65d59a;
        --mas-blue: #8fb6ec;

        --mas-line: rgba(255, 255, 255, .075);
        --mas-line-strong: rgba(215, 164, 95, .22);

        --mas-shadow:
            0 32px 90px rgba(0, 0, 0, .44);

        --mas-transition:
            .34s cubic-bezier(.2, .8, .2, 1);
    }

    .mas-admin-sidebar,
    .mas-admin-sidebar *,
    .mas-mobile-menu,
    .mas-mobile-menu * {
        box-sizing: border-box;
    }

    /*
    |--------------------------------------------------------------------------
    | SIDEBAR
    |--------------------------------------------------------------------------
    */

    .mas-admin-sidebar {
        position: fixed;

        z-index: 1050;

        top: 22px;
        left: 22px;
        bottom: 22px;

        width: var(--mas-sidebar-small);

        display: flex;
        flex-direction: column;

        min-height: 0;

        padding: 12px;

        overflow: hidden;

        border: 1px solid var(--mas-line);
        border-radius: 25px;

        background:
            radial-gradient(
                circle at 20% 0%,
                rgba(215, 164, 95, .10),
                transparent 220px
            ),
            linear-gradient(
                180deg,
                rgba(18, 21, 26, .985),
                rgba(11, 13, 17, .985)
            );

        box-shadow:
            var(--mas-shadow),
            inset 0 1px 0 rgba(255,255,255,.03);

        backdrop-filter: blur(22px);
        -webkit-backdrop-filter: blur(22px);

        transition:
            width var(--mas-transition),
            transform var(--mas-transition),
            box-shadow var(--mas-transition),
            border-color var(--mas-transition);
    }

    .mas-admin-sidebar:hover,
    .mas-admin-sidebar:focus-within,
    .mas-admin-sidebar.is-pinned {
        width: var(--mas-sidebar-large);

        border-color: rgba(215, 164, 95, .14);

        box-shadow:
            0 40px 110px rgba(0, 0, 0, .52),
            inset 0 1px 0 rgba(255,255,255,.035);
    }

    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */

    .mas-profile {
        position: relative;

        min-height: 58px;

        display: flex;
        align-items: center;

        gap: 12px;

        flex-shrink: 0;

        padding: 6px;
    }

    .mas-avatar {
        width: 46px;
        height: 46px;

        flex: 0 0 46px;

        display: grid;
        place-items: center;

        overflow: hidden;

        border: 1px solid rgba(215,164,95,.22);
        border-radius: 15px;

        background:
            linear-gradient(
                145deg,
                rgba(215,164,95,.18),
                rgba(215,164,95,.04)
            );

        color: var(--mas-gold-light);

        font-size: 14px;
        font-weight: 950;

        letter-spacing: -.03em;

        box-shadow:
            0 12px 28px rgba(0,0,0,.28),
            inset 0 0 0 1px rgba(255,255,255,.025);
    }

    .mas-avatar img {
        width: 100%;
        height: 100%;

        display: block;

        object-fit: cover;
    }

    .mas-profile-content {
        min-width: 0;

        flex: 1;

        opacity: 0;

        transform: translateX(-8px);

        pointer-events: none;

        transition:
            opacity .16s ease,
            transform .24s ease;
    }

    .mas-admin-sidebar:hover .mas-profile-content,
    .mas-admin-sidebar:focus-within .mas-profile-content,
    .mas-admin-sidebar.is-pinned .mas-profile-content {
        opacity: 1;

        transform: translateX(0);

        pointer-events: auto;

        transition-delay: .07s;
    }

    .mas-profile-name {
        display: block;

        max-width: 145px;

        overflow: hidden;

        color: #fff;

        font-size: 12px;
        font-weight: 900;

        line-height: 1.25;

        white-space: nowrap;
        text-overflow: ellipsis;
    }

    .mas-profile-role {
        display: block;

        margin-top: 4px;

        color: var(--mas-muted);

        font-size: 7px;
        font-weight: 900;

        letter-spacing: .11em;

        text-transform: uppercase;

        white-space: nowrap;
    }

    /*
    |--------------------------------------------------------------------------
    | PIN
    |--------------------------------------------------------------------------
    */

    .mas-pin {
        width: 30px;
        height: 30px;

        flex: 0 0 30px;

        display: grid;
        place-items: center;

        padding: 0;

        border: 1px solid transparent;
        border-radius: 9px;

        background: rgba(255,255,255,.02);

        color: #707780;

        cursor: pointer;

        opacity: 0;

        transform: translateX(8px);

        transition:
            opacity .15s ease,
            transform .2s ease,
            background .2s ease,
            border-color .2s ease,
            color .2s ease;
    }

    .mas-admin-sidebar:hover .mas-pin,
    .mas-admin-sidebar:focus-within .mas-pin,
    .mas-admin-sidebar.is-pinned .mas-pin {
        opacity: 1;

        transform: translateX(0);
    }

    .mas-pin:hover,
    .mas-admin-sidebar.is-pinned .mas-pin {
        border-color: rgba(215,164,95,.14);

        background: rgba(215,164,95,.08);

        color: var(--mas-gold-light);
    }

    .mas-pin svg {
        width: 15px;
        height: 15px;

        display: block;
    }

    /*
    |--------------------------------------------------------------------------
    | DIVIDER
    |--------------------------------------------------------------------------
    */

    .mas-divider {
        width: calc(100% - 12px);
        height: 1px;

        flex-shrink: 0;

        margin: 9px 6px 12px;

        background: var(--mas-line);
    }

    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    .mas-sidebar-search {
        position: relative;

        width: 100%;
        height: 45px;

        flex-shrink: 0;

        margin-bottom: 10px;

        overflow: hidden;

        border: 1px solid transparent;
        border-radius: 13px;

        background: transparent;

        transition:
            border-color .2s ease,
            background .2s ease,
            box-shadow .2s ease;
    }

    .mas-admin-sidebar:hover .mas-sidebar-search,
    .mas-admin-sidebar:focus-within .mas-sidebar-search,
    .mas-admin-sidebar.is-pinned .mas-sidebar-search {
        border-color: var(--mas-line);

        background: rgba(255,255,255,.025);
    }

    .mas-sidebar-search:focus-within {
        border-color: rgba(215,164,95,.30) !important;

        box-shadow:
            0 0 0 3px rgba(215,164,95,.06);
    }

    .mas-sidebar-search > svg {
        position: absolute;

        top: 50%;
        left: 15px;

        width: 18px;
        height: 18px;

        transform: translateY(-50%);

        color: #787e86;

        pointer-events: none;
    }

    .mas-sidebar-search input {
        width: 230px;
        height: 100%;

        padding: 0 15px 0 46px;

        border: 0;
        outline: 0;

        background: transparent;

        color: #ece9e3;

        font: inherit;
        font-size: 10px;
        font-weight: 600;

        opacity: 0;

        pointer-events: none;

        transition: opacity .15s ease;
    }

    .mas-sidebar-search input::placeholder {
        color: #626870;
    }

    .mas-admin-sidebar:hover .mas-sidebar-search input,
    .mas-admin-sidebar:focus-within .mas-sidebar-search input,
    .mas-admin-sidebar.is-pinned .mas-sidebar-search input {
        opacity: 1;

        pointer-events: auto;

        transition-delay: .07s;
    }

    /*
    |--------------------------------------------------------------------------
    | NAVIGATION
    |--------------------------------------------------------------------------
    */

    .mas-sidebar-navigation {
        flex: 1;

        min-height: 0;

        overflow-y: auto;
        overflow-x: hidden;

        overscroll-behavior: contain;

        scrollbar-width: none;
    }

    .mas-sidebar-navigation::-webkit-scrollbar {
        display: none;
    }

    .mas-sidebar-section-title {
        height: 28px;

        display: flex;
        align-items: center;

        padding: 0 12px;

        overflow: hidden;

        color: #5f656d;

        font-size: 7px;
        font-weight: 950;

        letter-spacing: .16em;

        text-transform: uppercase;

        opacity: 0;

        transform: translateX(-5px);

        white-space: nowrap;

        transition:
            opacity .15s ease,
            transform .22s ease;
    }

    .mas-admin-sidebar:hover .mas-sidebar-section-title,
    .mas-admin-sidebar:focus-within .mas-sidebar-section-title,
    .mas-admin-sidebar.is-pinned .mas-sidebar-section-title {
        opacity: 1;

        transform: translateX(0);
    }

    .mas-nav-item {
        position: relative;

        width: 100%;
        min-height: 47px;

        display: flex;
        align-items: center;

        gap: 13px;

        margin: 3px 0;

        padding: 0 12px;

        overflow: hidden;

        border: 1px solid transparent;
        border-radius: 13px;

        color: #818790;

        text-decoration: none;

        outline: none;

        transition:
            color .18s ease,
            border-color .18s ease,
            background .18s ease,
            transform .18s ease;
    }

    .mas-nav-item:hover,
    .mas-nav-item:focus-visible {
        border-color: rgba(255,255,255,.045);

        background: rgba(255,255,255,.035);

        color: #e7e3dc;
    }

    .mas-nav-item.active {
        border-color: rgba(215,164,95,.14);

        background:
            linear-gradient(
                90deg,
                rgba(215,164,95,.13),
                rgba(215,164,95,.045)
            );

        color: var(--mas-gold-light);
    }

    .mas-nav-item.active::before {
        content: "";

        position: absolute;

        left: 0;
        top: 10px;
        bottom: 10px;

        width: 2px;

        border-radius: 0 999px 999px 0;

        background: var(--mas-gold);

        box-shadow:
            0 0 14px rgba(215,164,95,.65);
    }

    .mas-nav-icon {
        width: 22px;
        height: 22px;

        flex: 0 0 22px;

        display: grid;
        place-items: center;
    }

    .mas-nav-icon svg {
        width: 19px;
        height: 19px;

        display: block;
    }

    .mas-nav-text {
        min-width: 155px;

        color: inherit;

        font-size: 10px;
        font-weight: 800;

        line-height: 1;

        opacity: 0;

        transform: translateX(-6px);

        white-space: nowrap;

        transition:
            opacity .14s ease,
            transform .23s ease;
    }

    .mas-admin-sidebar:hover .mas-nav-text,
    .mas-admin-sidebar:focus-within .mas-nav-text,
    .mas-admin-sidebar.is-pinned .mas-nav-text {
        opacity: 1;

        transform: translateX(0);

        transition-delay: .06s;
    }

    /*
    |--------------------------------------------------------------------------
    | BADGES
    |--------------------------------------------------------------------------
    */

    .mas-nav-badge {
        position: absolute;

        right: 13px;

        min-width: 22px;
        height: 21px;

        display: grid;
        place-items: center;

        padding: 0 7px;

        border: 1px solid rgba(215,164,95,.15);
        border-radius: 999px;

        background: rgba(215,164,95,.12);

        color: var(--mas-gold-light);

        font-size: 8px;
        font-weight: 950;

        opacity: 0;

        transform: translateX(15px);

        transition:
            opacity .15s ease,
            transform .22s ease;
    }

    .mas-admin-sidebar:hover .mas-nav-badge,
    .mas-admin-sidebar:focus-within .mas-nav-badge,
    .mas-admin-sidebar.is-pinned .mas-nav-badge {
        opacity: 1;

        transform: translateX(0);
    }

    /*
    |--------------------------------------------------------------------------
    | BOTTOM
    |--------------------------------------------------------------------------
    */

    .mas-sidebar-bottom {
        flex-shrink: 0;

        display: flex;
        flex-direction: column;

        gap: 4px;

        padding-top: 8px;
    }

    .mas-bottom-item,
    .mas-bottom-form button {
        position: relative;

        width: 100%;
        height: 45px;

        display: flex;
        align-items: center;

        gap: 13px;

        padding: 0 12px;

        overflow: hidden;

        border: 1px solid transparent;
        border-radius: 13px;

        background: transparent;

        color: #747b83;

        font: inherit;

        text-decoration: none;

        cursor: pointer;

        outline: none;

        transition:
            background .18s ease,
            color .18s ease,
            border-color .18s ease;
    }

    .mas-bottom-item:hover,
    .mas-bottom-item:focus-visible,
    .mas-bottom-form button:hover,
    .mas-bottom-form button:focus-visible {
        border-color: var(--mas-line);

        background: rgba(255,255,255,.035);

        color: #eeeae4;
    }

    .mas-bottom-item.primary {
        border-color: rgba(215,164,95,.10);

        background: rgba(215,164,95,.055);

        color: var(--mas-gold-light);
    }

    .mas-bottom-item.primary:hover {
        border-color: rgba(215,164,95,.20);

        background: rgba(215,164,95,.11);
    }

    .mas-bottom-form {
        margin: 0;
    }

    .mas-bottom-form button:hover {
        border-color: rgba(239,143,143,.15);

        background: rgba(239,143,143,.06);

        color: #efa2a2;
    }

    .mas-bottom-icon {
        width: 22px;
        height: 22px;

        flex: 0 0 22px;

        display: grid;
        place-items: center;
    }

    .mas-bottom-icon svg {
        width: 19px;
        height: 19px;

        display: block;
    }

    .mas-bottom-text {
        min-width: 155px;

        font-size: 10px;
        font-weight: 800;

        text-align: left;

        opacity: 0;

        transform: translateX(-6px);

        white-space: nowrap;

        transition:
            opacity .14s ease,
            transform .23s ease;
    }

    .mas-admin-sidebar:hover .mas-bottom-text,
    .mas-admin-sidebar:focus-within .mas-bottom-text,
    .mas-admin-sidebar.is-pinned .mas-bottom-text {
        opacity: 1;

        transform: translateX(0);

        transition-delay: .06s;
    }

    /*
    |--------------------------------------------------------------------------
    | TOOLTIP WHEN COLLAPSED
    |--------------------------------------------------------------------------
    */

    .mas-nav-item[data-label]::after,
    .mas-bottom-item[data-label]::after {
        content: attr(data-label);

        position: fixed;

        left: 116px;

        z-index: 2000;

        padding: 8px 10px;

        border: 1px solid var(--mas-line);
        border-radius: 9px;

        background: #15191f;

        color: #eeeae4;

        font-size: 9px;
        font-weight: 800;

        box-shadow: 0 10px 30px rgba(0,0,0,.35);

        opacity: 0;

        pointer-events: none;

        transform: translateX(-4px);

        transition:
            opacity .15s ease,
            transform .15s ease;
    }

    .mas-admin-sidebar:not(:hover):not(:focus-within):not(.is-pinned)
    .mas-nav-item[data-label]:hover::after,
    .mas-admin-sidebar:not(:hover):not(:focus-within):not(.is-pinned)
    .mas-bottom-item[data-label]:hover::after {
        opacity: 1;

        transform: translateX(0);
    }

    /*
    |--------------------------------------------------------------------------
    | MOBILE BUTTON
    |--------------------------------------------------------------------------
    */

    .mas-mobile-menu {
        display: none;

        position: fixed;

        z-index: 1200;

        top: 14px;
        left: 14px;

        width: 48px;
        height: 48px;

        padding: 0;

        place-items: center;

        border: 1px solid rgba(215,164,95,.18);
        border-radius: 14px;

        background:
            linear-gradient(
                145deg,
                #15191f,
                #0e1115
            );

        color: var(--mas-gold-light);

        box-shadow:
            0 16px 38px rgba(0,0,0,.4);

        cursor: pointer;
    }

    .mas-mobile-menu svg {
        width: 21px;
        height: 21px;

        display: block;
    }

    /*
    |--------------------------------------------------------------------------
    | BACKDROP
    |--------------------------------------------------------------------------
    */

    .mas-sidebar-backdrop {
        display: none;

        position: fixed;

        inset: 0;

        z-index: 1040;

        background: rgba(0,0,0,.68);

        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
    }

    /*
    |--------------------------------------------------------------------------
    | PAGE CONTENT
    |--------------------------------------------------------------------------
    */

    .mas-admin-content {
        width: auto;

        min-width: 0;

        margin-left: 128px;

        transition:
            margin-left var(--mas-transition);
    }

    body.mas-sidebar-pinned .mas-admin-content {
        margin-left: 314px;
    }

    /*
    |--------------------------------------------------------------------------
    | MOBILE
    |--------------------------------------------------------------------------
    */

    @media (max-width: 900px) {

        .mas-mobile-menu {
            display: grid;
        }

        .mas-admin-sidebar {
            top: 12px;
            left: 12px;
            bottom: 12px;

            width: min(
                var(--mas-sidebar-large),
                calc(100vw - 24px)
            );

            transform:
                translateX(calc(-100% - 30px));
        }

        .mas-admin-sidebar:hover,
        .mas-admin-sidebar:focus-within,
        .mas-admin-sidebar.is-pinned {
            width: min(
                var(--mas-sidebar-large),
                calc(100vw - 24px)
            );
        }

        .mas-admin-sidebar.is-mobile-open {
            transform: translateX(0);
        }

        .mas-admin-sidebar .mas-profile-content,
        .mas-admin-sidebar .mas-nav-text,
        .mas-admin-sidebar .mas-bottom-text,
        .mas-admin-sidebar .mas-sidebar-section-title,
        .mas-admin-sidebar .mas-sidebar-search input,
        .mas-admin-sidebar .mas-pin,
        .mas-admin-sidebar .mas-nav-badge {
            opacity: 1;

            transform: none;

            pointer-events: auto;
        }

        .mas-admin-sidebar .mas-sidebar-search {
            border-color: var(--mas-line);

            background: rgba(255,255,255,.025);
        }

        .mas-sidebar-backdrop.is-visible {
            display: block;
        }

        .mas-admin-content,
        body.mas-sidebar-pinned .mas-admin-content {
            margin-left: 0;
        }

        .mas-nav-item[data-label]::after,
        .mas-bottom-item[data-label]::after {
            display: none;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SMALL MOBILE
    |--------------------------------------------------------------------------
    */

    @media (max-width: 480px) {

        .mas-admin-sidebar {
            width: calc(100vw - 24px);
        }

        .mas-admin-sidebar:hover,
        .mas-admin-sidebar:focus-within,
        .mas-admin-sidebar.is-pinned {
            width: calc(100vw - 24px);
        }

        .mas-mobile-menu {
            width: 44px;
            height: 44px;
        }
    }
</style>


{{-- ============================================================= --}}
{{-- MOBILE OPEN BUTTON                                              --}}
{{-- ============================================================= --}}

<button
    type="button"
    class="mas-mobile-menu"
    id="masMobileSidebarButton"
    aria-label="Adminmenu openen"
    aria-expanded="false"
    aria-controls="masAdminSidebar"
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
        <path d="M4 7h16"/>
        <path d="M4 12h16"/>
        <path d="M4 17h16"/>
    </svg>
</button>


{{-- ============================================================= --}}
{{-- MOBILE BACKDROP                                                 --}}
{{-- ============================================================= --}}

<div
    class="mas-sidebar-backdrop"
    id="masSidebarBackdrop"
></div>


{{-- ============================================================= --}}
{{-- SIDEBAR                                                         --}}
{{-- ============================================================= --}}

<aside
    class="mas-admin-sidebar"
    id="masAdminSidebar"
    aria-label="Administrator navigatie"
>

    {{-- ========================================================= --}}
    {{-- PROFILE                                                    --}}
    {{-- ========================================================= --}}

    <div class="mas-profile">

        <div class="mas-avatar">
            @if ($admin && method_exists($admin, 'avatarUrl') && $admin->avatarUrl())
                <img
                    src="{{ $admin->avatarUrl() }}"
                    alt="Profielfoto van {{ $admin->name }}"
                >
            @else
                @if ($admin && method_exists($admin, 'initials'))
                    {{ $admin->initials() }}
                @else
                    {{ strtoupper(substr($admin?->name ?? 'M', 0, 1)) }}
                @endif
            @endif
        </div>


        <div class="mas-profile-content">

            <span class="mas-profile-name">
                {{ $admin?->name ?? 'Administrator' }}
            </span>

            <span class="mas-profile-role">
                Mashal Administrator
            </span>

        </div>


        <button
            type="button"
            class="mas-pin"
            id="masSidebarPin"
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


    <div class="mas-divider"></div>


    {{-- ========================================================= --}}
    {{-- SEARCH                                                     --}}
    {{-- ========================================================= --}}

    <div class="mas-sidebar-search">

        <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linecap="round"
            aria-hidden="true"
        >
            <circle cx="11" cy="11" r="7"/>
            <path d="m20 20-4-4"/>
        </svg>

        <input
            type="search"
            id="masSidebarSearch"
            placeholder="Zoek in admin..."
            autocomplete="off"
            aria-label="Zoek in het adminmenu"
        >

    </div>


    {{-- ========================================================= --}}
    {{-- NAVIGATION                                                 --}}
    {{-- ========================================================= --}}

    <nav class="mas-sidebar-navigation">

        {{-- OVERZICHT --}}

        <div class="mas-sidebar-section-title">
            Overzicht
        </div>


        <a
            href="{{ $dashboardUrl }}"
            class="
                mas-nav-item
                {{
                    request()->routeIs('admin.dashboard') ||
                    request()->routeIs('dashboard')
                        ? 'active'
                        : ''
                }}
            "
            data-label="Dashboard"
            data-sidebar-search="dashboard overzicht home beheer"
        >
            <span class="mas-nav-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <rect x="3" y="3" width="7" height="7" rx="2"/>
                    <rect x="14" y="3" width="7" height="7" rx="2"/>
                    <rect x="3" y="14" width="7" height="7" rx="2"/>
                    <rect x="14" y="14" width="7" height="7" rx="2"/>
                </svg>
            </span>

            <span class="mas-nav-text">
                Dashboard
            </span>
        </a>


        {{-- BEHEER --}}

        <div class="mas-sidebar-section-title">
            Beheer
        </div>


        @if (\Illuminate\Support\Facades\Route::has('users.index'))
            <a
                href="{{ route('users.index') }}"
                class="
                    mas-nav-item
                    {{
                        request()->routeIs('users.index') ||
                        request()->routeIs('users.edit') ||
                        request()->routeIs('users.show')
                            ? 'active'
                            : ''
                    }}
                "
                data-label="Gebruikers"
                data-sidebar-search="gebruikers accounts klanten users leden"
            >
                <span class="mas-nav-icon">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <circle cx="9" cy="8" r="3"/>
                        <path d="M3 21v-3a6 6 0 0 1 12 0v3"/>
                        <path d="M17 5a3 3 0 0 1 0 6"/>
                        <path d="M18 14a5 5 0 0 1 3 4v3"/>
                    </svg>
                </span>

                <span class="mas-nav-text">
                    Gebruikers
                </span>

                @if (!is_null($totalUsersSidebar))
                    <span class="mas-nav-badge">
                        {{ $totalUsersSidebar }}
                    </span>
                @endif
            </a>
        @endif


        @if (\Illuminate\Support\Facades\Route::has('users.create'))
            <a
                href="{{ route('users.create') }}"
                class="
                    mas-nav-item
                    {{ request()->routeIs('users.create') ? 'active' : '' }}
                "
                data-label="Gebruiker toevoegen"
                data-sidebar-search="nieuwe gebruiker toevoegen account maken"
            >
                <span class="mas-nav-icon">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <circle cx="9" cy="8" r="3"/>
                        <path d="M3 21v-3a6 6 0 0 1 12 0v3"/>
                        <path d="M19 8v6"/>
                        <path d="M16 11h6"/>
                    </svg>
                </span>

                <span class="mas-nav-text">
                    Gebruiker toevoegen
                </span>
            </a>
        @endif


        @if (\Illuminate\Support\Facades\Route::has('orders.index'))
            <a
                href="{{ route('orders.index') }}"
                class="
                    mas-nav-item
                    {{ request()->routeIs('orders.*') ? 'active' : '' }}
                "
                data-label="Bestellingen"
                data-sidebar-search="bestellingen orders aankopen verkopen"
            >
                <span class="mas-nav-icon">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="m4 7 8-4 8 4-8 4-8-4Z"/>
                        <path d="m4 7 8 4 8-4v10l-8 4-8-4V7Z"/>
                        <path d="M12 11v10"/>
                    </svg>
                </span>

                <span class="mas-nav-text">
                    Bestellingen
                </span>

                @if (!is_null($totalOrdersSidebar))
                    <span class="mas-nav-badge">
                        {{ $totalOrdersSidebar }}
                    </span>
                @endif
            </a>
        @endif


        @if (\Illuminate\Support\Facades\Route::has('catalog'))
            <a
                href="{{ route('catalog') }}"
                class="
                    mas-nav-item
                    {{ request()->routeIs('catalog') ? 'active' : '' }}
                "
                data-label="Catalogus"
                data-sidebar-search="catalogus auto's autos voertuigen collectie"
            >
                <span class="mas-nav-icon">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="m5 7 2-4h10l2 4"/>
                        <path d="M5 7h14l2 4v7H3v-7l2-4Z"/>
                        <path d="M3 12h18"/>
                        <circle cx="7" cy="15" r="1"/>
                        <circle cx="17" cy="15" r="1"/>
                    </svg>
                </span>

                <span class="mas-nav-text">
                    Catalogus
                </span>
            </a>
        @endif


        {{-- WEBSITE --}}

        <div class="mas-sidebar-section-title">
            Website
        </div>


        @if (\Illuminate\Support\Facades\Route::has('home'))
            <a
                href="{{ route('home') }}"
                class="mas-nav-item"
                data-label="Website bekijken"
                data-sidebar-search="website publieke site frontend home"
                target="_blank"
                rel="noopener noreferrer"
            >
                <span class="mas-nav-icon">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M3 12h18"/>
                        <path d="M12 3c3 3 3 15 0 18"/>
                        <path d="M12 3c-3 3-3 15 0 18"/>
                    </svg>
                </span>

                <span class="mas-nav-text">
                    Website bekijken
                </span>
            </a>
        @endif

    </nav>


    {{-- ========================================================= --}}
    {{-- BOTTOM ACTIONS                                             --}}
    {{-- ========================================================= --}}

    <div class="mas-sidebar-bottom">

        @if (\Illuminate\Support\Facades\Route::has('users.create'))
            <a
                href="{{ route('users.create') }}"
                class="mas-bottom-item primary"
                data-label="Nieuwe gebruiker"
            >
                <span class="mas-bottom-icon">
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

                <span class="mas-bottom-text">
                    Nieuwe gebruiker
                </span>
            </a>
        @endif


        @if (\Illuminate\Support\Facades\Route::has('profile.edit'))
            <a
                href="{{ route('profile.edit') }}"
                class="
                    mas-bottom-item
                    {{ request()->routeIs('profile.*') ? 'active' : '' }}
                "
                data-label="Instellingen"
            >
                <span class="mas-bottom-icon">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.86 2.86-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21H9.6v-.1a1.7 1.7 0 0 0-.4-1.1 1.7 1.7 0 0 0-1-.6 1.7 1.7 0 0 0-1.88.34l-.06.06-2.86-2.86.06-.06A1.7 1.7 0 0 0 3.8 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.1-.4H2V9.6h.1a1.7 1.7 0 0 0 1.1-.4 1.7 1.7 0 0 0 .6-1 1.7 1.7 0 0 0-.34-1.88l-.06-.06L6.26 3.4l.06.06A1.7 1.7 0 0 0 8.2 3.8a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V2h4v.1a1.7 1.7 0 0 0 .4 1.1 1.7 1.7 0 0 0 1 .6 1.7 1.7 0 0 0 1.88-.34l.06-.06 2.86 2.86-.06.06A1.7 1.7 0 0 0 19.4 8.2a1.7 1.7 0 0 0 .6 1 1.7 1.7 0 0 0 1.1.4h.1v4h-.1a1.7 1.7 0 0 0-1.1.4 1.7 1.7 0 0 0-.6 1Z"/>
                    </svg>
                </span>

                <span class="mas-bottom-text">
                    Instellingen
                </span>
            </a>
        @endif


        @if (\Illuminate\Support\Facades\Route::has('logout'))
            <form
                method="POST"
                action="{{ route('logout') }}"
                class="mas-bottom-form"
            >
                @csrf

                <button
                    type="submit"
                    aria-label="Uitloggen"
                >
                    <span class="mas-bottom-icon">
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

                    <span class="mas-bottom-text">
                        Uitloggen
                    </span>
                </button>
            </form>
        @endif

    </div>

</aside>


{{-- ============================================================= --}}
{{-- SIDEBAR JAVASCRIPT                                             --}}
{{-- ============================================================= --}}

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const sidebar =
            document.getElementById('masAdminSidebar');

        const pinButton =
            document.getElementById('masSidebarPin');

        const mobileButton =
            document.getElementById('masMobileSidebarButton');

        const backdrop =
            document.getElementById('masSidebarBackdrop');

        const search =
            document.getElementById('masSidebarSearch');

        const navigationItems =
            Array.from(
                document.querySelectorAll(
                    '.mas-nav-item[data-sidebar-search]'
                )
            );


        if (!sidebar) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | PIN STATUS
        |--------------------------------------------------------------------------
        */

        const storedPinned =
            localStorage.getItem(
                'mashalAdminSidebarPinned'
            );

        const sidebarIsPinned =
            storedPinned === '1';


        if (sidebarIsPinned && window.innerWidth > 900) {

            sidebar.classList.add(
                'is-pinned'
            );

            document.body.classList.add(
                'mas-sidebar-pinned'
            );

            pinButton?.setAttribute(
                'aria-pressed',
                'true'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | PIN BUTTON
        |--------------------------------------------------------------------------
        */

        pinButton?.addEventListener(
            'click',
            function () {

                if (window.innerWidth <= 900) {
                    return;
                }


                const pinned =
                    sidebar.classList.toggle(
                        'is-pinned'
                    );


                document.body.classList.toggle(
                    'mas-sidebar-pinned',
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


                pinButton.title =
                    pinned
                        ? 'Sidebar losmaken'
                        : 'Sidebar vastzetten';
            }
        );


        /*
        |--------------------------------------------------------------------------
        | MOBILE FUNCTIONS
        |--------------------------------------------------------------------------
        */

        function openMobileSidebar() {

            sidebar.classList.add(
                'is-mobile-open'
            );

            backdrop?.classList.add(
                'is-visible'
            );

            mobileButton?.setAttribute(
                'aria-expanded',
                'true'
            );

            document.body.style.overflow =
                'hidden';
        }


        function closeMobileSidebar() {

            sidebar.classList.remove(
                'is-mobile-open'
            );

            backdrop?.classList.remove(
                'is-visible'
            );

            mobileButton?.setAttribute(
                'aria-expanded',
                'false'
            );

            document.body.style.overflow =
                '';
        }


        /*
        |--------------------------------------------------------------------------
        | MOBILE BUTTON
        |--------------------------------------------------------------------------
        */

        mobileButton?.addEventListener(
            'click',
            function () {

                const isOpen =
                    sidebar.classList.contains(
                        'is-mobile-open'
                    );


                if (isOpen) {
                    closeMobileSidebar();
                } else {
                    openMobileSidebar();
                }
            }
        );


        backdrop?.addEventListener(
            'click',
            closeMobileSidebar
        );


        /*
        |--------------------------------------------------------------------------
        | ESCAPE CLOSE
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'keydown',
            function (event) {

                if (
                    event.key === 'Escape'
                ) {
                    closeMobileSidebar();
                }
            }
        );


        /*
        |--------------------------------------------------------------------------
        | CLOSE MOBILE AFTER CLICKING LINK
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '.mas-nav-item, .mas-bottom-item'
            )
            .forEach(function (item) {

                item.addEventListener(
                    'click',
                    function () {

                        if (
                            window.innerWidth <= 900
                        ) {
                            closeMobileSidebar();
                        }
                    }
                );
            });


        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        search?.addEventListener(
            'input',
            function () {

                const query =
                    search.value
                        .trim()
                        .toLowerCase();


                navigationItems.forEach(
                    function (item) {

                        const keywords =
                            (
                                item.dataset
                                    .sidebarSearch || ''
                            )
                            .toLowerCase();


                        const itemText =
                            item
                                .textContent
                                .trim()
                                .toLowerCase();


                        const visible =
                            !query ||
                            keywords.includes(
                                query
                            ) ||
                            itemText.includes(
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


        /*
        |--------------------------------------------------------------------------
        | WINDOW RESIZE
        |--------------------------------------------------------------------------
        */

        window.addEventListener(
            'resize',
            function () {

                if (
                    window.innerWidth > 900
                ) {

                    closeMobileSidebar();


                    const pinned =
                        localStorage.getItem(
                            'mashalAdminSidebarPinned'
                        ) === '1';


                    sidebar.classList.toggle(
                        'is-pinned',
                        pinned
                    );


                    document.body.classList.toggle(
                        'mas-sidebar-pinned',
                        pinned
                    );


                    pinButton?.setAttribute(
                        'aria-pressed',
                        String(pinned)
                    );

                } else {

                    document.body.classList.remove(
                        'mas-sidebar-pinned'
                    );
                }
            }
        );

    });
</script>
