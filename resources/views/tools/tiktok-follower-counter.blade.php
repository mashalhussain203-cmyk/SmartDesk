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
            <form class="tfc-search" method="POST" action="{{ route('tiktok-follower-counter.lookup') }}">
                @csrf
                <input
                    class="tfc-input"
                    type="text"
                    name="username"
                    value="{{ old('username', $username ?? '') }}"
                    placeholder="@username of TikTok profiel-URL"
                    autocomplete="off"
                    required
                    aria-label="TikTok username of profiel URL"
                >
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
                data-ui-build="20261008-tiktok-followers-v1"
                data-endpoint="{{ route('tiktok-follower-counter.livecounts-cards', ['username' => $username]) }}"
            >
                <aside class="tfc-card tfc-profile">
                    <div class="tfc-avatar-wrap">
                        <img
                            class="tfc-avatar"
                            id="tfc-avatar"
                            src="/icons/follower-profile.svg?v=1"
                            alt=""
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
