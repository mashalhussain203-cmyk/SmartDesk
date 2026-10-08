@extends('layouts.site-layout')

@section('title', 'TikTok Live Follower Count | Mashal Studio')
@section('meta_description', 'Volg publieke TikTok followers, likes, following en videos live met Mashal Studio.')

@push('styles')
<link rel="stylesheet" href="/vendor/odometer/odometer-theme-minimal.css?v=20261008-followers-1">
<style>
    .tfc-page {
        min-height: calc(100vh - 72px);
        padding: 42px 0 90px;
        color: #f5f7fa;
        background:
            radial-gradient(circle at 50% -120px, rgba(123,112,255,.15), transparent 430px),
            linear-gradient(180deg, #07080b 0%, #050608 100%);
    }

    .tfc-shell {
        width: min(calc(100% - 30px), 1120px);
        margin: 0 auto;
    }

    .tfc-hero {
        max-width: 760px;
        margin: 0 auto 28px;
        text-align: center;
    }

    .tfc-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 11px;
        border: 1px solid rgba(123,112,255,.2);
        border-radius: 999px;
        color: #aaa3ff;
        background: rgba(123,112,255,.055);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .13em;
        text-transform: uppercase;
    }

    .tfc-eyebrow-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #6ee7a8;
        box-shadow: 0 0 12px rgba(110,231,168,.65);
    }

    .tfc-title {
        margin: 18px 0 0;
        color: #fff;
        font-size: clamp(40px, 6vw, 70px);
        line-height: .98;
        font-weight: 720;
        letter-spacing: -.055em;
    }

    .tfc-title span {
        color: #737b88;
    }

    .tfc-subtitle {
        max-width: 610px;
        margin: 15px auto 0;
        color: #7f8896;
        font-size: 13px;
        line-height: 1.7;
    }

    .tfc-search-wrap {
        max-width: 900px;
        margin: 0 auto 26px;
        padding: 8px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 18px;
        background: rgba(10,12,17,.9);
        box-shadow: 0 24px 70px rgba(0,0,0,.28);
    }

    .tfc-search {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 8px;
        margin: 0;
    }

    .tfc-input {
        min-width: 0;
        height: 54px;
        padding: 0 16px;
        border: 0;
        outline: 0;
        border-radius: 12px;
        color: #eef1f5;
        background: rgba(255,255,255,.025);
        font: inherit;
        font-size: 14px;
    }

    .tfc-input::placeholder {
        color: #525b68;
    }

    .tfc-button {
        min-height: 54px;
        padding: 0 23px;
        border: 1px solid rgba(148,140,255,.44);
        border-radius: 12px;
        color: #fff;
        background: linear-gradient(135deg, #7c70ff, #675cf2);
        box-shadow: 0 12px 28px rgba(89,74,225,.22);
        font: inherit;
        font-size: 11px;
        font-weight: 850;
        cursor: pointer;
    }

    .tfc-search-field {
        position: relative;
        min-width: 0;
    }

    .tfc-search-field .tfc-input {
        width: 100%;
    }

    .tfc-suggestions {
        position: absolute;
        z-index: 80;
        left: 0;
        right: 0;
        top: calc(100% + 9px);
        max-height: 390px;
        overflow-y: auto;
        padding: 7px;
        border: 1px solid rgba(255,255,255,.09);
        border-radius: 16px;
        background: rgba(12,14,19,.985);
        box-shadow: 0 24px 70px rgba(0,0,0,.48);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
    }

    .tfc-suggestions[hidden] {
        display: none !important;
    }

    .tfc-suggestion {
        width: 100%;
        min-height: 64px;
        padding: 8px 10px;
        display: grid;
        grid-template-columns: 44px minmax(0,1fr) auto;
        align-items: center;
        gap: 11px;
        border: 0;
        border-radius: 12px;
        color: inherit;
        background: transparent;
        text-align: left;
        cursor: pointer;
    }

    .tfc-suggestion:hover,
    .tfc-suggestion.is-active {
        background: rgba(123,112,255,.09);
    }

    .tfc-suggestion-avatar {
        width: 44px;
        height: 44px;
        display: block;
        object-fit: cover;
        border-radius: 50%;
        border: 1px solid rgba(255,255,255,.08);
        background: #151922;
    }

    .tfc-suggestion-copy {
        min-width: 0;
    }

    .tfc-suggestion-name {
        display: flex;
        align-items: center;
        gap: 6px;
        min-width: 0;
        color: #f2f4f7;
        font-size: 13px;
        font-weight: 780;
        line-height: 1.25;
    }

    .tfc-suggestion-name span:first-child {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .tfc-suggestion-verified {
        flex: 0 0 auto;
        color: #66a8ff;
        font-size: 12px;
    }

    .tfc-suggestion-handle {
        margin-top: 4px;
        overflow: hidden;
        color: #737d8a;
        font-size: 11px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .tfc-suggestion-arrow {
        color: #8379ff;
        font-size: 16px;
    }

    .tfc-suggestion-state {
        padding: 14px 12px;
        color: #7f8895;
        font-size: 11px;
        text-align: center;
    }

    .tfc-error {
        max-width: 900px;
        margin: -12px auto 22px;
        padding: 12px 14px;
        border: 1px solid rgba(255,128,149,.2);
        border-radius: 12px;
        color: #ffb8c4;
        background: rgba(255,128,149,.055);
        font-size: 12px;
    }

    .tfc-result {
        display: grid;
        grid-template-columns: 280px minmax(0, 1fr);
        gap: 18px;
        align-items: stretch;
    }

    .tfc-card {
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 24px;
        background: linear-gradient(180deg, rgba(17,20,27,.92), rgba(9,11,15,.94));
        box-shadow: 0 26px 70px rgba(0,0,0,.22), inset 0 1px 0 rgba(255,255,255,.025);
    }

    .tfc-profile {
        padding: 24px 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .tfc-avatar-wrap {
        width: 108px;
        height: 108px;
        padding: 5px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.09);
        border-radius: 50%;
        background: rgba(255,255,255,.025);
    }

    .tfc-avatar {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
        border-radius: 50%;
    }

    .tfc-profile-name {
        margin-top: 18px;
        color: #fff;
        font-size: 21px;
        font-weight: 780;
        letter-spacing: -.025em;
    }

    .tfc-profile-handle {
        margin-top: 6px;
        color: #8b94a1;
        font-size: 13px;
        font-weight: 650;
    }

    .tfc-profile-chip {
        margin-top: 16px;
        padding: 7px 10px;
        border: 1px solid rgba(110,231,168,.14);
        border-radius: 999px;
        color: #7fd7a8;
        background: rgba(110,231,168,.045);
        font-size: 9px;
        font-weight: 850;
        letter-spacing: .09em;
        text-transform: uppercase;
    }

    .tfc-dashboard {
        padding: 18px;
    }

    .tfc-followers {
        min-height: 250px;
        padding: 24px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 20px;
        background:
            radial-gradient(circle at 92% 12%, rgba(123,112,255,.13), transparent 34%),
            rgba(255,255,255,.015);
    }

    .tfc-stat-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    .tfc-label {
        color: #89919e;
        font-size: 11px;
        font-weight: 850;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .tfc-icon {
        width: 34px;
        height: 34px;
        display: block;
        object-fit: contain;
    }

    .tfc-main-value {
        margin-top: 24px;
        color: #fff;
        font-size: clamp(64px, 8vw, 104px);
        line-height: .9;
        font-weight: 820;
        letter-spacing: -.055em;
        white-space: nowrap;
        overflow: hidden;
        font-variant-numeric: tabular-nums;
    }

    .tfc-secondary {
        margin-top: 14px;
        display: grid;
        grid-template-columns: repeat(3, minmax(0,1fr));
        gap: 12px;
    }

    .tfc-stat {
        min-width: 0;
        min-height: 150px;
        padding: 18px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 18px;
        background: rgba(255,255,255,.014);
    }

    .tfc-value {
        margin-top: 18px;
        color: #fff;
        font-size: clamp(34px, 4.5vw, 56px);
        line-height: .94;
        font-weight: 780;
        letter-spacing: -.045em;
        white-space: nowrap;
        overflow: hidden;
        font-variant-numeric: tabular-nums;
    }

    .tfc-loading {
        animation: tfcPulse 1.15s ease-in-out infinite;
    }

    @keyframes tfcPulse {
        50% { opacity: .45; }
    }

    .tfc-main-value.odometer,
    .tfc-value.odometer {
        display: block;
        width: 100%;
        font-family: inherit;
        line-height: inherit;
        overflow: visible;
    }

    .tfc-main-value.odometer .odometer-inside,
    .tfc-value.odometer .odometer-inside {
        display: inline-block;
        white-space: nowrap;
        font: inherit;
        line-height: inherit;
    }

    .tfc-main-value.odometer .odometer-value,
    .tfc-value.odometer .odometer-value {
        font: inherit;
        line-height: inherit;
        text-align: center;
    }

    .tfc-main-value.odometer .odometer-formatting-mark,
    .tfc-value.odometer .odometer-formatting-mark {
        display: inline-block;
        margin: 0 .015em;
        color: rgba(255,255,255,.72);
        font-size: .72em;
        line-height: 1;
        transform: translateY(.055em);
    }

    .tfc-empty {
        max-width: 900px;
        margin: 28px auto 0;
        padding: 42px 28px;
        text-align: center;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 22px;
        background: rgba(255,255,255,.014);
    }

    .tfc-empty strong {
        display: block;
        color: #e4e8ee;
        font-size: 15px;
    }

    .tfc-empty span {
        display: block;
        margin-top: 8px;
        color: #747e8b;
        font-size: 11px;
        line-height: 1.7;
    }

    @media (max-width: 880px) {
        .tfc-result {
            grid-template-columns: 1fr;
        }

        .tfc-profile {
            min-height: 220px;
        }
    }

    @media (max-width: 640px) {
        .tfc-page {
            padding-top: 32px;
        }

        .tfc-search {
            grid-template-columns: 1fr;
        }

        .tfc-button {
            width: 100%;
        }

        .tfc-dashboard {
            padding: 12px;
        }

        .tfc-followers {
            min-height: 205px;
            padding: 20px 16px;
        }

        .tfc-main-value {
            font-size: clamp(48px, 14vw, 66px);
        }

        .tfc-secondary {
            grid-template-columns: 1fr;
        }

        .tfc-stat {
            min-height: 135px;
        }

        .tfc-value {
            font-size: clamp(40px, 12vw, 54px);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .tfc-loading {
            animation: none !important;
        }

        .odometer-ribbon-inner {
            transition-duration: 0s !important;
        }
    }
</style>
@endpush

@section('content')
<section class="tfc-page">
    <div class="tfc-shell">
        <header class="tfc-hero">
            <div class="tfc-eyebrow">
                <span class="tfc-eyebrow-dot"></span>
                Mashal Studio · Social Intelligence
            </div>

            <h1 class="tfc-title">Live <span>Followers.</span></h1>

            <p class="tfc-subtitle">
                Volg publieke TikTok followers, likes, following en videos live in één dashboard.
            </p>
        </header>

        <div class="tfc-search-wrap">
            <form
                class="tfc-search"
                id="tfc-search-form"
                method="POST"
                action="{{ route('tiktok-follower-counter.lookup') }}"
                data-search-endpoint="{{ route('tiktok-follower-counter.search') }}"
            >
                @csrf

                <div class="tfc-search-field">
                    <input
                        class="tfc-input"
                        id="tfc-account-search"
                        type="text"
                        name="username"
                        value="{{ old('username', $username ?? '') }}"
                        placeholder="Zoek accounts op naam of @username…"
                        autocomplete="off"
                        required
                        aria-label="Zoek TikTok account"
                        aria-autocomplete="list"
                        aria-controls="tfc-account-suggestions"
                        aria-expanded="false"
                    >

                    <div
                        class="tfc-suggestions"
                        id="tfc-account-suggestions"
                        role="listbox"
                        aria-label="TikTok account suggesties"
                        hidden
                    ></div>
                </div>

                <button class="tfc-button" type="submit">Start Live Followers →</button>
            </form>
        </div>

        @error('username')
            <div class="tfc-error">{{ $message }}</div>
        @enderror

        @isset($username)
            <script>
                window.__tfcInitialStatsPromise = fetch(
                    @json(route('tiktok-follower-counter.livecounts-cards', ['username' => $username]))
                        + '?_=' + encodeURIComponent(String(Date.now())),
                    {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'Cache-Control': 'no-cache'
                        },
                        cache: 'no-store',
                        credentials: 'same-origin'
                    }
                ).then(function (response) {
                    if (!response.ok) {
                        throw new Error('Initial follower stats request failed');
                    }
                    return response.json();
                });
            </script>

            <div
                class="tfc-result"
                id="tfc-result"
                data-ui-build="20261008-instant-typed-result-v9"
                data-endpoint="{{ route('tiktok-follower-counter.livecounts-cards', ['username' => $username]) }}"
            >
                <aside class="tfc-card tfc-profile">
                    <div class="tfc-avatar-wrap">
                        <img
                            class="tfc-avatar"
                            id="tfc-avatar"
                            src="/icons/follower-profile.svg?v=1"
                            alt=""
                            referrerpolicy="no-referrer"
                            data-fallback-src="/icons/follower-profile.svg?v=1"
                        >
                    </div>
                    <div class="tfc-profile-name" id="tfc-display-name">{{ '@'.$username }}</div>
                    <div class="tfc-profile-handle">{{ '@'.$username }}</div>
                    <div class="tfc-profile-chip">Live TikTok profile</div>
                </aside>

                <div class="tfc-card tfc-dashboard">
                    <div class="tfc-followers">
                        <div class="tfc-stat-head">
                            <div class="tfc-label">Followers</div>
                            <img class="tfc-icon" src="/icons/follower-followers.svg?v=1" alt="">
                        </div>
                        <div class="tfc-main-value tfc-loading" data-follower-stat="followers">0</div>
                    </div>

                    <div class="tfc-secondary">
                        <div class="tfc-stat">
                            <div class="tfc-stat-head">
                                <div class="tfc-label">Likes</div>
                                <img class="tfc-icon" src="/icons/live-heart.svg?v=20261007-4" alt="">
                            </div>
                            <div class="tfc-value tfc-loading" data-follower-stat="likes">0</div>
                        </div>

                        <div class="tfc-stat">
                            <div class="tfc-stat-head">
                                <div class="tfc-label">Following</div>
                                <img class="tfc-icon" src="/icons/follower-following.svg?v=1" alt="">
                            </div>
                            <div class="tfc-value tfc-loading" data-follower-stat="following">0</div>
                        </div>

                        <div class="tfc-stat">
                            <div class="tfc-stat-head">
                                <div class="tfc-label">Videos</div>
                                <img class="tfc-icon" src="/icons/follower-videos.svg?v=1" alt="">
                            </div>
                            <div class="tfc-value tfc-loading" data-follower-stat="videos">0</div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="tfc-empty">
                <strong>Start een TikTok Live Follower Count</strong>
                <span>Vul een publieke TikTok username of profiel-URL in.</span>
            </div>
        @endisset
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    var form = document.getElementById('tfc-search-form');
    var input = document.getElementById('tfc-account-search');
    var list = document.getElementById('tfc-account-suggestions');

    if (!form || !input || !list) {
        return;
    }

    var endpoint = form.getAttribute('data-search-endpoint') || '';
    var debounceTimer = null;
    var controller = null;
    var activeIndex = -1;
    var currentItems = [];
    var cachedResults = [];
    var cachedQuery = '';

    function closeList() {
        list.hidden = true;
        list.innerHTML = '';
        input.setAttribute('aria-expanded', 'false');
        currentItems = [];
        activeIndex = -1;
    }

    function showState(text) {
        list.innerHTML = '';

        var state = document.createElement('div');
        state.className = 'tfc-suggestion-state';
        state.textContent = text;

        list.appendChild(state);
        list.hidden = false;
        input.setAttribute('aria-expanded', 'true');
        currentItems = [];
        activeIndex = -1;
    }

    function filterResults(results, query) {
        var needle = String(query || '').toLowerCase();

        return (Array.isArray(results) ? results : []).filter(function (account) {
            var username = String(account.username || '').toLowerCase();
            var displayName = String(account.display_name || '').toLowerCase();

            return username.indexOf(needle) !== -1
                || displayName.indexOf(needle) !== -1;
        });
    }

    function setActive(index) {
        var buttons = list.querySelectorAll('.tfc-suggestion');

        if (!buttons.length) {
            activeIndex = -1;
            return;
        }

        if (index < 0) {
            index = buttons.length - 1;
        }

        if (index >= buttons.length) {
            index = 0;
        }

        activeIndex = index;

        buttons.forEach(function (button, buttonIndex) {
            var selected = buttonIndex === activeIndex;

            button.classList.toggle('is-active', selected);
            button.setAttribute('aria-selected', selected ? 'true' : 'false');
        });

        if (buttons[activeIndex]) {
            buttons[activeIndex].scrollIntoView({ block: 'nearest' });
        }
    }

    function chooseAccount(account) {
        if (!account || !account.username) {
            return;
        }

        input.value = '@' + account.username;
        closeList();

        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            form.submit();
        }
    }

    function renderResults(results) {
        list.innerHTML = '';
        currentItems = Array.isArray(results) ? results : [];
        activeIndex = -1;

        if (!currentItems.length) {
            showState('Geen accounts gevonden');
            return;
        }

        currentItems.forEach(function (account, index) {
            var button = document.createElement('button');
            var avatar = document.createElement('img');
            var copy = document.createElement('span');
            var name = document.createElement('span');
            var nameText = document.createElement('span');
            var handle = document.createElement('span');
            var arrow = document.createElement('span');

            button.type = 'button';
            button.className = 'tfc-suggestion';
            button.setAttribute('role', 'option');
            button.setAttribute('aria-selected', 'false');

            avatar.className = 'tfc-suggestion-avatar';
            avatar.alt = '';
            avatar.referrerPolicy = 'no-referrer';
            avatar.src = account.avatar_url || '/icons/follower-profile.svg?v=1';
            avatar.onerror = function () {
                avatar.onerror = null;
                avatar.src = '/icons/follower-profile.svg?v=1';
            };

            copy.className = 'tfc-suggestion-copy';

            name.className = 'tfc-suggestion-name';
            nameText.textContent = account.display_name || account.username;
            name.appendChild(nameText);

            if (account.verified) {
                var verified = document.createElement('span');
                verified.className = 'tfc-suggestion-verified';
                verified.textContent = '✓';
                verified.setAttribute('aria-label', 'Verified');
                name.appendChild(verified);
            }

            handle.className = 'tfc-suggestion-handle';
            handle.textContent = '@' + account.username;

            arrow.className = 'tfc-suggestion-arrow';
            arrow.textContent = '→';
            arrow.setAttribute('aria-hidden', 'true');

            copy.appendChild(name);
            copy.appendChild(handle);

            button.appendChild(avatar);
            button.appendChild(copy);
            button.appendChild(arrow);

            button.addEventListener('mouseenter', function () {
                setActive(index);
            });

            button.addEventListener('click', function () {
                chooseAccount(account);
            });

            list.appendChild(button);
        });

        list.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    }

    function typedAccount(query) {
        var username = String(query || '').trim().replace(/^@/, '');

        if (!/^[A-Za-z0-9._]{2,24}$/.test(username)) {
            return null;
        }

        return {
            username: username,
            display_name: username,
            avatar_url: '/icons/follower-profile.svg?v=1',
            verified: false,
            provisional: true
        };
    }

    function showTypedAccount(query) {
        var account = typedAccount(query);

        if (!account) {
            return false;
        }

        renderResults([account]);
        return true;
    }

    function runSearch(query) {
        var requestedQuery = String(query || '').trim().replace(/^@/, '');

        if (
            !endpoint
            || requestedQuery.length < 2
            || requestedQuery.length > 40
            || requestedQuery.indexOf('/') !== -1
            || /^https?:/i.test(requestedQuery)
        ) {
            closeList();
            return;
        }

        if (controller && typeof controller.abort === 'function') {
            controller.abort();
        }

        controller = typeof AbortController !== 'undefined'
            ? new AbortController()
            : null;

        if (!currentItems.length && !cachedResults.length) {
            showState('Accounts zoeken…');
        }

        fetch(
            endpoint + '?q=' + encodeURIComponent(requestedQuery),
            {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                },
                cache: 'no-store',
                credentials: 'same-origin',
                signal: controller ? controller.signal : undefined
            }
        )
            .then(function (response) {
                if (!response.ok) {
                    var error = new Error('Search failed');
                    error.status = response.status;
                    throw error;
                }

                return response.json();
            })
            .then(function (data) {
                var currentQuery = input.value.trim().replace(/^@/, '');
                var results;
                var filtered;

                if (!data || data.success === false) {
                    if (currentQuery === requestedQuery) {
                        showTypedAccount(currentQuery);
                    }
                    return;
                }

                results = Array.isArray(data.results) ? data.results : [];
                cachedResults = results.slice();
                cachedQuery = requestedQuery.toLowerCase();

                filtered = filterResults(results, currentQuery);

                if (filtered.length) {
                    renderResults(filtered);
                    return;
                }

                if (currentQuery === requestedQuery) {
                    showTypedAccount(currentQuery);
                    return;
                }

                window.clearTimeout(debounceTimer);
                debounceTimer = window.setTimeout(function () {
                    runSearch(currentQuery);
                }, 180);
            })
            .catch(function (error) {
                if (error && error.name === 'AbortError') {
                    return;
                }

                var currentQuery = input.value.trim().replace(/^@/, '');

                if (currentQuery !== requestedQuery) {
                    return;
                }

                showTypedAccount(currentQuery);
            });
    }

    input.addEventListener('input', function () {
        var query = input.value.trim().replace(/^@/, '');
        var lowerQuery = query.toLowerCase();
        var localMatches = [];

        window.clearTimeout(debounceTimer);

        if (query.length < 2) {
            closeList();
            return;
        }

        if (
            cachedResults.length
            && cachedQuery
            && lowerQuery.indexOf(cachedQuery) === 0
        ) {
            localMatches = filterResults(cachedResults, lowerQuery);

            if (localMatches.length) {
                renderResults(localMatches);
            } else {
                showTypedAccount(query);
            }
        } else {
            // Instant UX: show the typed @username immediately. The real
            // Livecounts search silently enriches/replaces it with avatar,
            // display name and verified status as soon as it returns.
            showTypedAccount(query);
        }

        debounceTimer = window.setTimeout(function () {
            runSearch(query);
        }, query.length === 2 ? 20 : 80);
    });

    input.addEventListener('focus', function () {
        var query = input.value.trim().replace(/^@/, '');
        var matches;

        if (query.length < 2 || !cachedResults.length) {
            return;
        }

        matches = filterResults(cachedResults, query);

        if (matches.length) {
            renderResults(matches);
        }
    });

    input.addEventListener('keydown', function (event) {
        var buttons;

        if (list.hidden) {
            return;
        }

        buttons = list.querySelectorAll('.tfc-suggestion');

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            setActive(activeIndex + 1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            setActive(activeIndex - 1);
        } else if (event.key === 'Escape') {
            event.preventDefault();
            closeList();
        } else if (
            event.key === 'Enter'
            && activeIndex >= 0
            && buttons[activeIndex]
        ) {
            event.preventDefault();
            buttons[activeIndex].click();
        }
    });

    document.addEventListener('click', function (event) {
        if (!form.contains(event.target)) {
            closeList();
        }
    });
}());
</script>
@endpush

@isset($username)
@push('scripts')
<script>window.odometerOptions = { auto: false };</script>
<script src="/vendor/odometer/odometer.min.js?v=20261008-followers-1"></script>
<script>
(function () {
    'use strict';

    var root = document.getElementById('tfc-result');
    if (!root) {
        return;
    }

    var endpoint = root.getAttribute('data-endpoint') || '';
    var timer = null;
    var elements = {
        followers: document.querySelector('[data-follower-stat="followers"]'),
        likes: document.querySelector('[data-follower-stat="likes"]'),
        following: document.querySelector('[data-follower-stat="following"]'),
        videos: document.querySelector('[data-follower-stat="videos"]')
    };

    function animateCounter(element, value) {
        var numericValue = Number(value);

        if (!element || !isFinite(numericValue)) {
            return;
        }

        numericValue = Math.max(0, Math.round(numericValue));
        element.classList.remove('tfc-loading');
        element.setAttribute('aria-label', numericValue.toLocaleString('en-US'));

        if (!window.Odometer) {
            element.textContent = numericValue.toLocaleString('en-US');
            return;
        }

        if (!element._tfcOdometer) {
            element.textContent = '0';
            element._tfcOdometer = new window.Odometer({
                el: element,
                value: 0,
                format: '(,ddd)',
                theme: 'minimal',
                duration: 900
            });

            window.requestAnimationFrame(function () {
                window.requestAnimationFrame(function () {
                    element._tfcOdometer.update(numericValue);
                });
            });
            return;
        }

        element._tfcOdometer.update(numericValue);
    }

    function applyPayload(data) {
        var keys = ['followers', 'likes', 'following', 'videos'];
        var i;
        var key;
        var avatar;
        var displayName;

        if (!data || data.success === false || !data.stats) {
            return false;
        }

        for (i = 0; i < keys.length; i += 1) {
            key = keys[i];
            animateCounter(elements[key], data.stats[key]);
        }

        avatar = document.getElementById('tfc-avatar');
        if (avatar && data.avatar_url) {
            avatar.referrerPolicy = 'no-referrer';
            avatar.onerror = function () {
                var fallback = avatar.getAttribute('data-fallback-src');
                avatar.onerror = null;
                if (fallback) {
                    avatar.src = fallback;
                }
            };
            avatar.src = data.avatar_url;
        }

        displayName = document.getElementById('tfc-display-name');
        if (displayName && data.display_name) {
            displayName.textContent = data.display_name;
        }

        return true;
    }

    function schedule() {
        if (timer !== null) {
            window.clearTimeout(timer);
        }
        timer = window.setTimeout(loadStats, 5000);
    }

    function loadStats() {
        var xhr;

        if (!endpoint) {
            return;
        }

        xhr = new XMLHttpRequest();
        xhr.open(
            'GET',
            endpoint + (endpoint.indexOf('?') === -1 ? '?' : '&')
                + '_=' + encodeURIComponent(String(Date.now())),
            true
        );
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('Cache-Control', 'no-cache');
        xhr.timeout = 32000;

        xhr.onreadystatechange = function () {
            var data;

            if (xhr.readyState !== 4) {
                return;
            }

            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    data = JSON.parse(xhr.responseText || '{}');
                } catch (error) {
                    data = null;
                }

                applyPayload(data);
            }

            schedule();
        };

        xhr.onerror = schedule;
        xhr.ontimeout = schedule;
        xhr.send(null);
    }

    if (
        window.__tfcInitialStatsPromise
        && typeof window.__tfcInitialStatsPromise.then === 'function'
    ) {
        window.__tfcInitialStatsPromise
            .then(function (data) {
                if (applyPayload(data)) {
                    schedule();
                } else {
                    loadStats();
                }
            })
            .catch(loadStats);
    } else {
        loadStats();
    }

    window.addEventListener('beforeunload', function () {
        if (timer !== null) {
            window.clearTimeout(timer);
        }
    });
}());
</script>
@endpush
@endisset
