@extends('layouts.site-layout')

@section('title', 'TikTok Engagement | Mashal Studio')
@section('meta_description', 'Bekijk publieke TikTok hearts, comments en favorites in een rustige live interface.')

@push('styles')
<link rel="stylesheet" href="/vendor/odometer/odometer-theme-minimal.css?v=20261007-2">
<style>
    .teg-page {
        --teg-bg: #050608;
        --teg-panel: rgba(13,16,22,.9);
        --teg-line: rgba(255,255,255,.08);
        --teg-line-strong: rgba(255,255,255,.14);
        --teg-text: #f4f6f8;
        --teg-muted: #7b8492;
        --teg-purple: #7b70ff;
        --teg-green: #6ee7a8;
        --teg-red: #ff718c;
        --teg-blue: #67b7ff;
        --teg-yellow: #f4c96b;

        min-height: calc(100vh - 72px);
        padding: 48px 0 96px;
        color: var(--teg-text);
        background:
            radial-gradient(circle at 50% -160px, rgba(123,112,255,.18), transparent 460px),
            radial-gradient(circle at 92% 20%, rgba(103,183,255,.055), transparent 400px),
            linear-gradient(180deg, #06070a 0%, #050608 100%);
    }

    .teg-shell {
        width: min(calc(100% - 32px), 1180px);
        margin: 0 auto;
    }

    .teg-hero {
        max-width: 760px;
        margin: 0 auto 32px;
        text-align: center;
    }

    .teg-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 11px;
        border: 1px solid rgba(123,112,255,.2);
        border-radius: 999px;
        color: #aaa3ff;
        background: rgba(123,112,255,.055);
        font-size: 10px;
        font-weight: 850;
        letter-spacing: .13em;
        text-transform: uppercase;
    }

    .teg-eyebrow i {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--teg-green);
        box-shadow: 0 0 12px rgba(110,231,168,.65);
    }

    .teg-title {
        margin: 18px 0 0;
        color: #fff;
        font-size: clamp(42px, 6vw, 72px);
        line-height: .98;
        font-weight: 720;
        letter-spacing: -.055em;
    }

    .teg-title span { color: #737b88; }

    .teg-subtitle {
        max-width: 620px;
        margin: 17px auto 0;
        color: var(--teg-muted);
        font-size: 13px;
        line-height: 1.75;
    }

    .teg-services {
        max-width: 900px;
        margin: 0 auto 18px;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .teg-service {
        position: relative;
        min-width: 0;
    }

    .teg-service input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .teg-service-card {
        min-height: 122px;
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        border: 1px solid var(--teg-line);
        border-radius: 18px;
        background:
            radial-gradient(circle at 100% 0%, rgba(123,112,255,.08), transparent 40%),
            rgba(12,15,20,.82);
        cursor: pointer;
        transition: transform .18s ease, border-color .18s ease, background .18s ease;
    }

    .teg-service-card:hover {
        transform: translateY(-2px);
        border-color: rgba(149,140,255,.22);
    }

    .teg-service input:checked + .teg-service-card {
        border-color: rgba(149,140,255,.5);
        background:
            radial-gradient(circle at 100% 0%, rgba(123,112,255,.16), transparent 45%),
            rgba(15,18,25,.96);
        box-shadow: 0 18px 42px rgba(0,0,0,.2);
    }

    .teg-service-icon {
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        display: grid;
        place-items: center;
        border-radius: 14px;
        border: 1px solid rgba(255,255,255,.07);
        background: rgba(255,255,255,.025);
    }

    .teg-service-icon img {
        width: 28px;
        height: 28px;
        object-fit: contain;
    }

    .teg-service:nth-child(1) .teg-service-icon { color: var(--teg-red); }
    .teg-service:nth-child(2) .teg-service-icon { color: var(--teg-blue); }
    .teg-service:nth-child(3) .teg-service-icon { color: var(--teg-yellow); }

    .teg-service-copy strong {
        display: block;
        color: #eef1f5;
        font-size: 13px;
        font-weight: 800;
    }

    .teg-service-copy span {
        display: block;
        margin-top: 4px;
        color: #697281;
        font-size: 9px;
        line-height: 1.5;
    }

    .teg-search-wrap {
        max-width: 900px;
        margin: 0 auto 28px;
    }

    .teg-search {
        padding: 8px;
        display: grid;
        grid-template-columns: minmax(0,1fr) auto;
        gap: 8px;
        border: 1px solid var(--teg-line);
        border-radius: 18px;
        background: rgba(10,12,17,.9);
        box-shadow: 0 26px 70px rgba(0,0,0,.28);
    }

    .teg-input-shell {
        min-width: 0;
        padding: 0 14px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .teg-input-shell span {
        color: #7d8693;
        font-size: 12px;
        font-weight: 900;
    }

    .teg-input {
        width: 100%;
        height: 54px;
        border: 0;
        outline: 0;
        color: #edf0f4;
        background: transparent;
        font-size: 13px;
    }

    .teg-input::placeholder { color: #525b68; }

    .teg-button {
        min-height: 54px;
        padding: 0 22px;
        border: 1px solid rgba(149,140,255,.45);
        border-radius: 12px;
        color: #fff;
        background: linear-gradient(135deg, #7b70ff, #6157e9);
        font-size: 11px;
        font-weight: 850;
        cursor: pointer;
    }

    .teg-error {
        max-width: 900px;
        margin: -12px auto 24px;
        padding: 11px 14px;
        border: 1px solid rgba(244,125,125,.18);
        border-radius: 12px;
        color: #f5a0a0;
        background: rgba(244,125,125,.06);
        font-size: 10px;
    }

    .teg-note {
        max-width: 900px;
        margin: 0 auto 26px;
        color: #606978;
        font-size: 9px;
        line-height: 1.6;
        text-align: center;
    }

    .teg-result {
        max-width: 1040px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 300px minmax(0,1fr);
        gap: 16px;
    }

    .teg-card {
        border: 1px solid var(--teg-line);
        border-radius: 24px;
        background:
            radial-gradient(circle at 100% 0%, rgba(123,112,255,.08), transparent 38%),
            linear-gradient(180deg, rgba(15,18,24,.95), rgba(9,11,15,.97));
        box-shadow: 0 24px 60px rgba(0,0,0,.22);
    }

    .teg-video {
        min-height: 410px;
        padding: 18px;
        display: flex;
        flex-direction: column;
    }

    .teg-thumb {
        aspect-ratio: 9 / 13;
        width: 100%;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 16px;
        background: rgba(255,255,255,.025);
    }

    .teg-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .teg-video-title {
        margin-top: 16px;
        color: #e9edf2;
        font-size: 11px;
        line-height: 1.55;
        font-weight: 760;
    }

    .teg-author {
        margin-top: 6px;
        color: #737c89;
        font-size: 9px;
    }

    .teg-dashboard {
        padding: 22px;
    }

    .teg-status {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        color: #66707e;
        font-size: 9px;
    }

    .teg-status strong {
        color: #77dca2;
        font-weight: 850;
    }

    .teg-main {
        padding: 28px 0 22px;
        text-align: center;
        border-bottom: 1px solid var(--teg-line);
    }

    .teg-main-label {
        color: #767f8d;
        font-size: 10px;
        font-weight: 850;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .teg-main-value {
        margin-top: 8px;
        color: #fff;
        font-size: clamp(48px, 8vw, 76px);
        line-height: 1;
        font-weight: 780;
        letter-spacing: -.05em;
    }

    .teg-stats {
        padding-top: 18px;
        display: grid;
        grid-template-columns: repeat(3, minmax(0,1fr));
        gap: 10px;
    }

    .teg-stat {
        min-height: 130px;
        padding: 16px;
        border: 1px solid rgba(255,255,255,.065);
        border-radius: 16px;
        background: rgba(255,255,255,.018);
    }

    .teg-stat-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .teg-stat-label {
        color: #737c89;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .teg-stat img {
        width: 23px;
        height: 23px;
    }

    .teg-stat-value {
        margin-top: 24px;
        color: #f4f6f8;
        font-size: clamp(28px, 4vw, 42px);
        font-weight: 760;
        letter-spacing: -.04em;
    }

    .teg-empty {
        max-width: 900px;
        margin: 12px auto 0;
        padding: 34px 20px;
        border: 1px dashed rgba(255,255,255,.08);
        border-radius: 20px;
        text-align: center;
        color: #6a7380;
        font-size: 11px;
    }

    .teg-loading {
        opacity: .6;
    }

    .odometer {
        font: inherit !important;
        line-height: inherit !important;
    }

    @media (max-width: 820px) {
        .teg-services { grid-template-columns: 1fr; }
        .teg-search { grid-template-columns: 1fr; }
        .teg-button { width: 100%; }
        .teg-result { grid-template-columns: 1fr; }
        .teg-video { min-height: 0; }
        .teg-thumb { max-height: 420px; }
    }

    @media (max-width: 620px) {
        .teg-page { padding-top: 36px; }
        .teg-stats { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<section class="teg-page">
    <div class="teg-shell">
        <header class="teg-hero">
            <div class="teg-eyebrow"><i></i> Mashal Studio · TikTok Engagement</div>
            <h1 class="teg-title">TikTok <span>Engagement.</span></h1>
            <p class="teg-subtitle">
                Kies Hearts, Comments of Favorites, plak een publieke TikTok-video en bekijk
                de actuele publieke engagementcijfers in één dashboard.
            </p>
        </header>

        <form class="teg-flow" method="POST" action="{{ route('tiktok-engagement.lookup') }}">
            @csrf

            @php
                $currentService = old('service', $selectedService ?? 'hearts');
            @endphp

            <div class="teg-services">
                <label class="teg-service">
                    <input type="radio" name="service" value="hearts" {{ $currentService === 'hearts' ? 'checked' : '' }}>
                    <span class="teg-service-card">
                        <span class="teg-service-icon">
                            <img src="/icons/live-heart.svg?v=20261007-4" alt="">
                        </span>
                        <span class="teg-service-copy">
                            <strong>Hearts</strong>
                            <span>Bekijk de publieke like count van de video.</span>
                        </span>
                    </span>
                </label>

                <label class="teg-service">
                    <input type="radio" name="service" value="comments" {{ $currentService === 'comments' ? 'checked' : '' }}>
                    <span class="teg-service-card">
                        <span class="teg-service-icon">
                            <img src="/icons/live-comment.svg?v=20261007-4" alt="">
                        </span>
                        <span class="teg-service-copy">
                            <strong>Comments</strong>
                            <span>Bekijk het actuele publieke aantal comments.</span>
                        </span>
                    </span>
                </label>

                <label class="teg-service">
                    <input type="radio" name="service" value="favorites" {{ $currentService === 'favorites' ? 'checked' : '' }}>
                    <span class="teg-service-card">
                        <span class="teg-service-icon">
                            <img src="/icons/live-favorite.svg?v=1" alt="">
                        </span>
                        <span class="teg-service-copy">
                            <strong>Favorites</strong>
                            <span>Bekijk hoe vaak de video publiek is opgeslagen.</span>
                        </span>
                    </span>
                </label>
            </div>

            <div class="teg-search-wrap">
                <div class="teg-search">
                    <div class="teg-input-shell">
                        <span>↗</span>
                        <input
                            class="teg-input"
                            type="url"
                            name="url"
                            value="{{ old('url', $videoUrl ?? '') }}"
                            placeholder="Plak TikTok video URL…"
                            required
                            autocomplete="off"
                        >
                    </div>

                    <button class="teg-button" type="submit">Search video →</button>
                </div>
            </div>
        </form>

        @error('url')
            <div class="teg-error">{{ $message }}</div>
        @enderror

        <div class="teg-note">
            Deze tool leest alleen publieke TikTok-videostatistieken. Hij verstuurt geen kunstmatige engagementacties.
        </div>

        @isset($videoId)
            <script>
                window.__tegInitialStatsPromise = fetch(
                    @json(route('tiktok-engagement.stats', ['videoId' => $videoId]))
                        + '?url=' + encodeURIComponent(@json($videoUrl))
                        + '&_=' + encodeURIComponent(String(Date.now())),
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
                        throw new Error('Initial engagement request failed');
                    }
                    return response.json();
                });
            </script>

            <div
                class="teg-result"
                id="teg-result"
                data-endpoint="{{ route('tiktok-engagement.stats', ['videoId' => $videoId]) }}"
                data-video-url="{{ $videoUrl }}"
                data-selected-service="{{ $currentService }}"
                data-ui-build="20261008-tiktok-engagement-v1"
            >
                <aside class="teg-card teg-video">
                    <div class="teg-thumb">
                        <img
                            id="teg-thumbnail"
                            src="/icons/follower-profile.svg?v=1"
                            alt=""
                            referrerpolicy="no-referrer"
                        >
                    </div>
                    <div class="teg-video-title" id="teg-video-title">TikTok video</div>
                    <div class="teg-author" id="teg-author">Publieke video</div>
                </aside>

                <div class="teg-card teg-dashboard">
                    <div class="teg-status">
                        <span>Public TikTok data</span>
                        <strong id="teg-status">Loading…</strong>
                    </div>

                    <div class="teg-main">
                        <div class="teg-main-label" id="teg-main-label">
                            {{ ucfirst($currentService) }}
                        </div>
                        <div class="teg-main-value teg-loading" id="teg-main-value">0</div>
                    </div>

                    <div class="teg-stats">
                        <div class="teg-stat">
                            <div class="teg-stat-head">
                                <span class="teg-stat-label">Hearts</span>
                                <img src="/icons/live-heart.svg?v=20261007-4" alt="">
                            </div>
                            <div class="teg-stat-value teg-loading" data-teg-stat="hearts">0</div>
                        </div>

                        <div class="teg-stat">
                            <div class="teg-stat-head">
                                <span class="teg-stat-label">Comments</span>
                                <img src="/icons/live-comment.svg?v=20261007-4" alt="">
                            </div>
                            <div class="teg-stat-value teg-loading" data-teg-stat="comments">0</div>
                        </div>

                        <div class="teg-stat">
                            <div class="teg-stat-head">
                                <span class="teg-stat-label">Favorites</span>
                                <img src="/icons/live-favorite.svg?v=1" alt="">
                            </div>
                            <div class="teg-stat-value teg-loading" data-teg-stat="favorites">0</div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="teg-empty">
                Kies een metric en plak een publieke TikTok-video om de engagementdata te openen.
            </div>
        @endisset
    </div>
</section>
@endsection

@isset($videoId)
@push('scripts')
<script src="/vendor/odometer/odometer.min.js?v=20261007-2"></script>
<script>
(function () {
    'use strict';

    var root = document.getElementById('teg-result');

    if (!root) {
        return;
    }

    var endpoint = root.getAttribute('data-endpoint') || '';
    var videoUrl = root.getAttribute('data-video-url') || '';
    var selectedService = root.getAttribute('data-selected-service') || 'hearts';
    var statusEl = document.getElementById('teg-status');
    var mainLabel = document.getElementById('teg-main-label');
    var mainValue = document.getElementById('teg-main-value');
    var titleEl = document.getElementById('teg-video-title');
    var authorEl = document.getElementById('teg-author');
    var thumbEl = document.getElementById('teg-thumbnail');
    var odometers = {};

    var labels = {
        hearts: 'Hearts',
        comments: 'Comments',
        favorites: 'Favorites'
    };

    function formatNumber(value) {
        var number = Number(value);
        return Number.isFinite(number)
            ? Math.max(0, Math.trunc(number)).toLocaleString('en-US')
            : '0';
    }

    function ensureOdometer(key, element) {
        if (!element || typeof window.Odometer !== 'function') {
            return null;
        }

        if (!odometers[key]) {
            element.textContent = '0';
            odometers[key] = new window.Odometer({
                el: element,
                value: 0,
                format: '(,ddd)',
                duration: 900
            });
        }

        return odometers[key];
    }

    function updateNumber(key, element, value) {
        if (!element) {
            return;
        }

        var number = Number(value);

        if (!Number.isFinite(number)) {
            return;
        }

        element.classList.remove('teg-loading');

        var odometer = ensureOdometer(key, element);

        if (odometer) {
            window.requestAnimationFrame(function () {
                odometer.update(Math.max(0, Math.trunc(number)));
            });
        } else {
            element.textContent = formatNumber(number);
        }
    }

    function applyData(data) {
        if (!data || data.success !== true || !data.stats) {
            throw new Error('Invalid engagement response');
        }

        ['hearts', 'comments', 'favorites'].forEach(function (key) {
            updateNumber(
                key,
                root.querySelector('[data-teg-stat="' + key + '"]'),
                data.stats[key]
            );
        });

        mainLabel.textContent = labels[selectedService] || 'Hearts';
        updateNumber('main', mainValue, data.stats[selectedService]);

        if (data.title) {
            titleEl.textContent = data.title;
        }

        if (data.author_name) {
            authorEl.textContent = '@' + String(data.author_name).replace(/^@/, '');
        }

        if (data.thumbnail_url) {
            thumbEl.src = data.thumbnail_url;
        }

        statusEl.textContent = 'Live';
    }

    function loadStats() {
        if (!endpoint || !videoUrl) {
            return Promise.resolve();
        }

        statusEl.textContent = 'Refreshing…';

        return fetch(
            endpoint
                + '?url=' + encodeURIComponent(videoUrl)
                + '&_=' + encodeURIComponent(String(Date.now())),
            {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Cache-Control': 'no-cache'
                },
                cache: 'no-store',
                credentials: 'same-origin'
            }
        )
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Engagement request failed');
                }
                return response.json();
            })
            .then(applyData)
            .catch(function () {
                statusEl.textContent = 'Retrying…';
            });
    }

    if (window.__tegInitialStatsPromise) {
        window.__tegInitialStatsPromise
            .then(applyData)
            .catch(function () {
                return loadStats();
            });
    } else {
        loadStats();
    }

    window.setInterval(loadStats, 15000);
}());
</script>
@endpush
@endisset
