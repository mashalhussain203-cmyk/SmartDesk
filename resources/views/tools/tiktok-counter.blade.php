@extends('layouts.site-layout')

@section('title', 'Live Count | Mashal Studio')
@section('meta_description', 'Volg publieke TikTok-statistieken live met Mashal Studio Live Count.')

@push('styles')
<style>
    .ttc-page {
        --bg: #050608;
        --panel: rgba(13, 16, 22, .86);
        --panel-soft: rgba(255, 255, 255, .025);
        --panel-hover: rgba(255, 255, 255, .045);
        --line: rgba(255, 255, 255, .075);
        --line-strong: rgba(255, 255, 255, .13);
        --text: #f4f6f8;
        --muted: #798291;
        --muted-2: #555e6b;
        --purple: #7b70ff;
        --purple-2: #958cff;
        --green: #6ee7a8;
        --red: #ff8095;
        --blue: #67b7ff;

        min-height: calc(100vh - 72px);
        padding: 46px 0 96px;
        color: var(--text);
        background:
            radial-gradient(circle at 50% -160px, rgba(123, 112, 255, .17), transparent 460px),
            radial-gradient(circle at 92% 25%, rgba(103, 183, 255, .055), transparent 420px),
            linear-gradient(180deg, #06070a 0%, #050608 100%);
        position: relative;
        overflow: hidden;
    }

    .ttc-page::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        opacity: .16;
        background-image:
            linear-gradient(rgba(255,255,255,.028) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,.028) 1px, transparent 1px);
        background-size: 56px 56px;
        mask-image: linear-gradient(to bottom, black 0%, transparent 70%);
    }

    .ttc-shell {
        width: min(calc(100% - 32px), 1220px);
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .ttc-hero {
        max-width: 780px;
        margin: 0 auto 32px;
        text-align: center;
    }

    .ttc-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 9px;
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

    .ttc-eyebrow-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--green);
        box-shadow: 0 0 14px rgba(110,231,168,.75);
    }

    .ttc-title {
        margin: 18px 0 0;
        font-size: clamp(42px, 6vw, 72px);
        line-height: .98;
        letter-spacing: -.055em;
        font-weight: 680;
        color: #fff;
    }

    .ttc-title span {
        color: #737b88;
    }

    .ttc-subtitle {
        max-width: 620px;
        margin: 17px auto 0;
        color: #7f8896;
        font-size: 13px;
        line-height: 1.75;
    }

    .ttc-search-wrap {
        max-width: 930px;
        margin: 0 auto 28px;
        position: relative;
    }

    .ttc-search-wrap::before {
        content: "";
        position: absolute;
        inset: -1px;
        border-radius: 19px;
        padding: 1px;
        background: linear-gradient(110deg, rgba(123,112,255,.42), rgba(255,255,255,.06), rgba(103,183,255,.18));
        -webkit-mask:
            linear-gradient(#000 0 0) content-box,
            linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
    }

    .ttc-search {
        margin: 0;
        padding: 8px;
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 8px;
        border: 1px solid rgba(255,255,255,.045);
        border-radius: 18px;
        background: rgba(10,12,17,.9);
        box-shadow:
            0 26px 70px rgba(0,0,0,.32),
            inset 0 1px 0 rgba(255,255,255,.035);
        backdrop-filter: blur(20px);
    }

    .ttc-input-shell {
        min-width: 0;
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 0 14px;
    }

    .ttc-input-icon {
        width: 28px;
        height: 28px;
        flex: 0 0 28px;
        display: grid;
        place-items: center;
        border: 1px solid var(--line);
        border-radius: 8px;
        color: #8c95a2;
        background: rgba(255,255,255,.025);
        font-size: 12px;
    }

    .ttc-search input {
        min-width: 0;
        width: 100%;
        height: 54px;
        border: 0;
        outline: 0;
        color: #e9edf2;
        background: transparent;
        font: inherit;
        font-size: 13px;
    }

    .ttc-search input::placeholder {
        color: #525b68;
    }

    .ttc-button {
        min-height: 54px;
        padding: 0 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        border: 1px solid rgba(148,140,255,.44);
        border-radius: 12px;
        color: #fff;
        background: linear-gradient(135deg, #7c70ff, #675cf2);
        box-shadow:
            0 12px 28px rgba(89,74,225,.22),
            inset 0 1px 0 rgba(255,255,255,.18);
        font: inherit;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .02em;
        cursor: pointer;
        transition: transform .18s ease, filter .18s ease;
    }

    .ttc-button:hover {
        transform: translateY(-1px);
        filter: brightness(1.06);
    }

    .ttc-error {
        max-width: 930px;
        margin: -14px auto 24px;
        padding: 12px 14px;
        border: 1px solid rgba(255,128,149,.2);
        border-radius: 12px;
        color: #ffb8c4;
        background: rgba(255,128,149,.055);
        font-size: 12px;
    }

    .ttc-result {
        display: grid;
        grid-template-columns: 300px minmax(0,1fr);
        gap: 16px;
        align-items: stretch;
    }

    .ttc-card {
        border: 1px solid var(--line);
        border-radius: 20px;
        background:
            linear-gradient(180deg, rgba(17,20,27,.9), rgba(9,11,15,.92));
        box-shadow:
            0 26px 70px rgba(0,0,0,.22),
            inset 0 1px 0 rgba(255,255,255,.025);
        backdrop-filter: blur(18px);
    }

    .ttc-preview {
        padding: 12px;
        display: flex;
        flex-direction: column;
    }

    .ttc-thumb {
        position: relative;
        min-height: 440px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.055);
        border-radius: 14px;
        background:
            radial-gradient(circle at 50% 20%, rgba(123,112,255,.2), transparent 34%),
            linear-gradient(150deg, #171a22, #090a0e 62%);
    }

    .ttc-thumb img {
        width: 100%;
        height: 100%;
        position: absolute;
        inset: 0;
        object-fit: cover;
    }

    .ttc-thumb::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 1;
        background:
            linear-gradient(to top, rgba(4,5,8,.76), transparent 42%),
            linear-gradient(to bottom, rgba(4,5,8,.18), transparent 28%);
        pointer-events: none;
    }

    .ttc-live-chip {
        position: absolute;
        top: 11px;
        left: 11px;
        z-index: 3;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 9px;
        border: 1px solid rgba(255,255,255,.11);
        border-radius: 9px;
        color: #e8ebef;
        background: rgba(5,7,10,.58);
        backdrop-filter: blur(12px);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .ttc-live-chip i {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--green);
        box-shadow: 0 0 10px rgba(110,231,168,.75);
    }

    .ttc-play {
        width: 52px;
        height: 52px;
        position: absolute;
        left: 50%;
        top: 50%;
        z-index: 3;
        transform: translate(-50%,-50%);
        display: grid;
        place-items: center;
        border: 1px solid rgba(255,255,255,.2);
        border-radius: 50%;
        color: #fff;
        background: rgba(7,8,12,.56);
        backdrop-filter: blur(12px);
        text-decoration: none;
        transition: transform .18s ease, background .18s ease;
    }

    .ttc-play:hover {
        transform: translate(-50%,-50%) scale(1.05);
        background: rgba(12,14,20,.72);
    }

    .ttc-video-meta {
        padding: 14px 5px 3px;
    }

    .ttc-author {
        color: #a8a1ff;
        font-size: 11px;
        font-weight: 800;
    }

    .ttc-video-title {
        margin: 6px 0 0;
        color: #b7bdc7;
        font-size: 12px;
        line-height: 1.55;
    }

    .ttc-dashboard {
        padding: 18px;
        min-width: 0;
    }

    .ttc-dashboard-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 14px;
    }

    .ttc-status {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
        color: #8e97a5;
        font-size: 10px;
        font-weight: 760;
    }

    .ttc-status-dot {
        width: 7px;
        height: 7px;
        flex: 0 0 7px;
        border-radius: 50%;
        background: var(--green);
        box-shadow: 0 0 13px rgba(110,231,168,.62);
    }

    .ttc-updated {
        color: #596270;
        font-size: 10px;
        white-space: nowrap;
    }

    .ttc-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0,1fr));
        gap: 10px;
    }

    .ttc-stat {
        min-width: 0;
        min-height: 138px;
        padding: 17px;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.065);
        border-radius: 15px;
        background:
            linear-gradient(145deg, rgba(255,255,255,.026), rgba(255,255,255,.012));
        transition: border-color .18s ease, background .18s ease;
    }

    .ttc-stat:hover {
        border-color: rgba(255,255,255,.1);
        background: rgba(255,255,255,.035);
    }

    .ttc-stat::after {
        content: "";
        width: 100px;
        height: 100px;
        position: absolute;
        right: -34px;
        top: -36px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(123,112,255,.12), transparent 68%);
        pointer-events: none;
    }

    .ttc-stat-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .ttc-stat-label {
        color: #727b88;
        font-size: 9px;
        font-weight: 850;
        letter-spacing: .13em;
        text-transform: uppercase;
    }

    .ttc-stat-badge {
        width: 26px;
        height: 26px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(255,255,255,.06);
        border-radius: 8px;
        color: #777f8b;
        background: rgba(255,255,255,.02);
        font-size: 10px;
    }

    .ttc-stat-value {
        margin-top: 14px;
        color: #fff;
        font-size: clamp(33px, 4vw, 52px);
        line-height: .95;
        font-weight: 690;
        letter-spacing: -.055em;
        font-variant-numeric: tabular-nums;
        overflow-wrap: anywhere;
    }

    .ttc-stat-delta {
        margin-top: 11px;
        color: var(--green);
        font-size: 10px;
        font-weight: 780;
    }

    .ttc-chart-wrap {
        margin-top: 10px;
        padding: 14px 14px 10px;
        border: 1px solid rgba(255,255,255,.065);
        border-radius: 15px;
        background: rgba(255,255,255,.014);
    }

    .ttc-chart-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 9px;
    }

    .ttc-chart-title {
        color: #d7dbe2;
        font-size: 11px;
        font-weight: 800;
    }

    .ttc-chart-note {
        color: #596270;
        font-size: 9px;
    }

    .ttc-chart {
        width: 100%;
        height: 140px;
        display: block;
    }

    .ttc-actions {
        margin-top: 10px;
        display: grid;
        grid-template-columns: repeat(3, minmax(0,1fr));
        gap: 8px;
    }

    .ttc-action {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border: 1px solid var(--line);
        border-radius: 10px;
        color: #9ca5b1;
        background: rgba(255,255,255,.018);
        text-decoration: none;
        font: inherit;
        font-size: 10px;
        font-weight: 780;
        cursor: pointer;
        transition: .18s ease;
    }

    .ttc-action:hover {
        color: #e8ebef;
        border-color: var(--line-strong);
        background: var(--panel-hover);
    }

    .ttc-empty {
        max-width: 930px;
        margin: 28px auto 0;
        padding: 40px 28px;
        text-align: center;
        border: 1px solid var(--line);
        border-radius: 20px;
        background: rgba(255,255,255,.015);
    }

    .ttc-empty-icon {
        width: 44px;
        height: 44px;
        margin: 0 auto 14px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(123,112,255,.17);
        border-radius: 13px;
        color: #aaa4ff;
        background: rgba(123,112,255,.055);
        font-size: 15px;
    }

    .ttc-empty strong {
        display: block;
        color: #dfe3e9;
        font-size: 14px;
    }

    .ttc-empty span {
        display: block;
        max-width: 520px;
        margin: 8px auto 0;
        color: #6f7885;
        font-size: 11px;
        line-height: 1.7;
    }

    .ttc-loading {
        animation: ttcPulse 1.2s ease-in-out infinite;
    }

    @keyframes ttcPulse {
        50% { opacity: .42; }
    }

    @media (prefers-reduced-motion: reduce) {
        .ttc-loading,
        .ttc-button,
        .ttc-play,
        .ttc-action,
        .ttc-stat {
            animation: none !important;
            transition: none !important;
        }
    }

    @media (max-width: 900px) {
        .ttc-result {
            grid-template-columns: 1fr;
        }

        .ttc-preview {
            display: grid;
            grid-template-columns: 180px minmax(0,1fr);
            gap: 16px;
        }

        .ttc-thumb {
            min-height: 270px;
        }

        .ttc-video-meta {
            align-self: end;
            padding: 0 6px 12px 0;
        }
    }

    @media (max-width: 640px) {
        .ttc-page {
            padding-top: 34px;
        }

        .ttc-hero {
            margin-bottom: 24px;
        }

        .ttc-title {
            font-size: 44px;
        }

        .ttc-search {
            grid-template-columns: 1fr;
        }

        .ttc-button {
            width: 100%;
        }

        .ttc-preview {
            display: block;
        }

        .ttc-thumb {
            min-height: 430px;
        }

        .ttc-dashboard-head {
            align-items: flex-start;
            flex-direction: column;
            gap: 7px;
        }

        .ttc-stats {
            grid-template-columns: 1fr;
        }

        .ttc-stat {
            min-height: 120px;
        }

        .ttc-stat-value {
            font-size: 38px;
        }

        .ttc-actions {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<section class="ttc-page">
    <div class="ttc-shell">
        <header class="ttc-hero">
            <div class="ttc-eyebrow">
                <span class="ttc-eyebrow-dot"></span>
                Mashal Studio · Social Intelligence
            </div>

            <h1 class="ttc-title">
                Live <span>Count.</span>
            </h1>

            <p class="ttc-subtitle">
                Volg publieke TikTok views, likes, comments en shares in één realtime dashboard.
                Nieuwe data wordt automatisch opnieuw opgehaald.
            </p>
        </header>

        <div class="ttc-search-wrap">
            <form class="ttc-search" method="POST" action="{{ route('tiktok-counter.lookup') }}">
                @csrf

                <label class="ttc-input-shell">
                    <span class="ttc-input-icon">↗</span>
                    <input
                        type="url"
                        name="url"
                        value="{{ old('url', $videoUrl ?? '') }}"
                        placeholder="Plak een TikTok-video URL…"
                        autocomplete="off"
                        required
                        aria-label="TikTok video URL"
                    >
                </label>

                <button class="ttc-button" type="submit">
                    Start Live Count
                    <span>→</span>
                </button>
            </form>
        </div>

        @error('url')
            <div class="ttc-error">{{ $message }}</div>
        @enderror

        @isset($videoId)
            <div
                class="ttc-result"
                id="ttc-result"
                data-video-id="{{ $videoId }}"
                data-video-url="{{ $videoUrl }}"
                data-stats-endpoint="{{ route('tiktok-counter.stats', ['videoId' => $videoId]) }}"
                data-poll-ms="4000"
            >
                <aside class="ttc-card ttc-preview">
                    <div class="ttc-thumb">
                        <img id="ttc-thumb-image" alt="TikTok thumbnail" hidden>

                        <span class="ttc-live-chip">
                            <i></i>
                            Live source
                        </span>

                        <a
                            class="ttc-play"
                            href="{{ $videoUrl }}"
                            target="_blank"
                            rel="noopener"
                            aria-label="Open video op TikTok"
                        >▶</a>
                    </div>

                    <div class="ttc-video-meta">
                        <div class="ttc-author" id="ttc-author">TikTok video</div>
                        <p class="ttc-video-title" id="ttc-video-title">
                            Video-informatie laden…
                        </p>
                    </div>
                </aside>

                <div class="ttc-card ttc-dashboard">
                    <div class="ttc-dashboard-head">
                        <div class="ttc-status">
                            <span class="ttc-status-dot" id="ttc-status-dot"></span>
                            <span id="ttc-status-text">Live Count starten…</span>
                        </div>

                        <div class="ttc-updated" id="ttc-updated">
                            Nog niet bijgewerkt
                        </div>
                    </div>

                    <div class="ttc-stats">
                        <div class="ttc-stat">
                            <div class="ttc-stat-head">
                                <div class="ttc-stat-label">Views</div>
                                <div class="ttc-stat-badge">◉</div>
                            </div>
                            <div class="ttc-stat-value ttc-loading" data-stat="views">—</div>
                            <div class="ttc-stat-delta" data-delta="views">Sessie gestart</div>
                        </div>

                        <div class="ttc-stat">
                            <div class="ttc-stat-head">
                                <div class="ttc-stat-label">Likes</div>
                                <div class="ttc-stat-badge">♥</div>
                            </div>
                            <div class="ttc-stat-value ttc-loading" data-stat="likes">—</div>
                            <div class="ttc-stat-delta" data-delta="likes">Sessie gestart</div>
                        </div>

                        <div class="ttc-stat">
                            <div class="ttc-stat-head">
                                <div class="ttc-stat-label">Comments</div>
                                <div class="ttc-stat-badge">◌</div>
                            </div>
                            <div class="ttc-stat-value ttc-loading" data-stat="comments">—</div>
                            <div class="ttc-stat-delta" data-delta="comments">Sessie gestart</div>
                        </div>

                        <div class="ttc-stat">
                            <div class="ttc-stat-head">
                                <div class="ttc-stat-label">Shares</div>
                                <div class="ttc-stat-badge">↗</div>
                            </div>
                            <div class="ttc-stat-value ttc-loading" data-stat="shares">—</div>
                            <div class="ttc-stat-delta" data-delta="shares">Sessie gestart</div>
                        </div>
                    </div>

                    <div class="ttc-chart-wrap">
                        <div class="ttc-chart-head">
                            <div class="ttc-chart-title">View growth</div>
                            <div class="ttc-chart-note">Laatste 60 metingen · deze sessie</div>
                        </div>

                        <svg
                            class="ttc-chart"
                            viewBox="0 0 800 140"
                            preserveAspectRatio="none"
                            aria-label="View growth chart"
                        >
                            <defs>
                                <linearGradient id="ttcChartFill" x1="0" x2="0" y1="0" y2="1">
                                    <stop offset="0%" stop-color="#7b70ff" stop-opacity=".24"/>
                                    <stop offset="100%" stop-color="#7b70ff" stop-opacity="0"/>
                                </linearGradient>
                            </defs>

                            <path id="ttc-chart-area" fill="url(#ttcChartFill)" d=""></path>
                            <path
                                id="ttc-chart-line"
                                fill="none"
                                stroke="#8f86ff"
                                stroke-width="2.5"
                                vector-effect="non-scaling-stroke"
                                d=""
                            ></path>
                        </svg>
                    </div>

                    <div class="ttc-actions">
                        <button class="ttc-action" type="button" id="ttc-refresh">
                            ↻ Vernieuwen
                        </button>

                        <button class="ttc-action" type="button" id="ttc-copy">
                            ⧉ Link kopiëren
                        </button>

                        <a
                            class="ttc-action"
                            href="{{ $videoUrl }}"
                            target="_blank"
                            rel="noopener"
                        >
                            ↗ Open TikTok
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="ttc-empty">
                <div class="ttc-empty-icon">◉</div>
                <strong>Start een nieuwe Live Count</strong>
                <span>
                    Plak hierboven een openbare TikTok-video. Mashal Studio opent daarna
                    automatisch het live dashboard.
                </span>
            </div>
        @endisset
    </div>
</section>
@endsection

@isset($videoId)
@push('scripts')
<script>
(function () {
    'use strict';

    var root = document.getElementById('ttc-result');
    if (!root) {
        return;
    }

    var endpoint = root.getAttribute('data-stats-endpoint') || '';
    var videoUrl = root.getAttribute('data-video-url') || '';
    var videoId = root.getAttribute('data-video-id') || '';
    var pollMs = parseInt(root.getAttribute('data-poll-ms') || '4000', 10);

    if (!pollMs || pollMs < 4000) {
        pollMs = 4000;
    }

    var statKeys = ['views', 'likes', 'comments', 'shares'];
    var statElements = {};
    var deltaElements = {};
    var sessionStart = null;
    var lastStats = null;
    var lastRealStats = null;
    var lastRealAt = 0;
    var growthPerSecond = {
        views: 0,
        likes: 0,
        comments: 0,
        shares: 0
    };
    var displayStats = {
        views: null,
        likes: null,
        comments: null,
        shares: null
    };
    var animationTimer = null;
    var history = [];
    var timer = null;
    var request = null;
    var requestNumber = 0;
    var requestStartedAt = 0;
    var stopped = false;
    var storageKey = 'mashal:tiktok-live:' + videoId;

    var statusText = document.getElementById('ttc-status-text');
    var statusDot = document.getElementById('ttc-status-dot');
    var updatedElement = document.getElementById('ttc-updated');
    var authorElement = document.getElementById('ttc-author');
    var titleElement = document.getElementById('ttc-video-title');
    var imageElement = document.getElementById('ttc-thumb-image');
    var refreshButton = document.getElementById('ttc-refresh');
    var copyButton = document.getElementById('ttc-copy');
    var chartLine = document.getElementById('ttc-chart-line');
    var chartArea = document.getElementById('ttc-chart-area');

    var i;
    for (i = 0; i < statKeys.length; i += 1) {
        statElements[statKeys[i]] = document.querySelector('[data-stat="' + statKeys[i] + '"]');
        deltaElements[statKeys[i]] = document.querySelector('[data-delta="' + statKeys[i] + '"]');
    }

    function isFiniteNumber(value) {
        return typeof value === 'number' && isFinite(value);
    }

    function toNumber(value) {
        var number;
        if (value === null || typeof value === 'undefined' || value === '') {
            return null;
        }
        number = Number(value);
        return isFinite(number) ? number : null;
    }

    function formatNumber(value) {
        var number = Number(value || 0);
        try {
            return number.toLocaleString('nl-NL');
        } catch (error) {
            return String(number);
        }
    }

    function cloneStats(stats) {
        return {
            views: stats.views,
            likes: stats.likes,
            comments: stats.comments,
            shares: stats.shares
        };
    }

    function setStatus(text, ok) {
        if (statusText) {
            statusText.textContent = text;
        }
        if (statusDot) {
            if (ok === false) {
                statusDot.style.background = '#ff8095';
                statusDot.style.boxShadow = '0 0 13px rgba(255,128,149,.55)';
            } else {
                statusDot.style.background = '#6ee7a8';
                statusDot.style.boxShadow = '0 0 13px rgba(110,231,168,.62)';
            }
        }
    }

    function clampGrowth(key, rate) {
        var limits = {
            views: 50000,
            likes: 10000,
            comments: 1000,
            shares: 2000
        };
        var limit = limits[key] || 1000;

        if (!isFiniteNumber(rate) || rate < 0) {
            return 0;
        }

        return Math.min(rate, limit);
    }

    function learnGrowth(stats) {
        var now = new Date().getTime();
        var elapsed;
        var key;
        var delta;
        var rate;

        if (lastRealStats && lastRealAt > 0) {
            elapsed = Math.max((now - lastRealAt) / 1000, 0.25);

            for (i = 0; i < statKeys.length; i += 1) {
                key = statKeys[i];

                if (
                    isFiniteNumber(stats[key])
                    && isFiniteNumber(lastRealStats[key])
                ) {
                    delta = stats[key] - lastRealStats[key];

                    if (delta >= 0) {
                        rate = delta / elapsed;

                        if (rate > 0) {
                            growthPerSecond[key] = clampGrowth(
                                key,
                                growthPerSecond[key] > 0
                                    ? (growthPerSecond[key] * 0.65) + (rate * 0.35)
                                    : rate
                            );
                        }
                    }
                }
            }
        }

        lastRealStats = cloneStats(stats);
        lastRealAt = now;

        for (i = 0; i < statKeys.length; i += 1) {
            key = statKeys[i];
            if (isFiniteNumber(stats[key])) {
                displayStats[key] = stats[key];
            }
        }
    }

    function animateEstimatedCounters() {
        var now = new Date().getTime();
        var secondsSinceReal;
        var key;
        var estimated;

        if (lastRealStats && lastRealAt > 0) {
            secondsSinceReal = Math.max((now - lastRealAt) / 1000, 0);

            for (i = 0; i < statKeys.length; i += 1) {
                key = statKeys[i];

                if (
                    isFiniteNumber(lastRealStats[key])
                    && growthPerSecond[key] > 0
                ) {
                    estimated = lastRealStats[key]
                        + (growthPerSecond[key] * secondsSinceReal);

                    displayStats[key] = Math.max(
                        lastRealStats[key],
                        Math.floor(estimated)
                    );

                    updateStat(key, displayStats[key]);
                }
            }
        }

        animationTimer = window.setTimeout(animateEstimatedCounters, 250);
    }

    function updateStat(key, value) {
        var element = statElements[key];
        var deltaElement = deltaElements[key];
        var delta;

        if (!element) {
            return;
        }

        if (element.classList) {
            element.classList.remove('ttc-loading');
        }

        if (!isFiniteNumber(value)) {
            element.textContent = '-';
            if (deltaElement) {
                deltaElement.textContent = 'Niet beschikbaar';
                deltaElement.style.color = '#65707e';
            }
            return;
        }

        element.textContent = formatNumber(value);

        if (deltaElement && sessionStart && isFiniteNumber(sessionStart[key])) {
            delta = value - sessionStart[key];
            deltaElement.textContent = (delta >= 0 ? '+' : '') + formatNumber(delta) + ' deze sessie';
            deltaElement.style.color = delta >= 0 ? '#6ee7a8' : '#ff8095';
        }
    }

    function renderChart() {
        var values = [];
        var min;
        var max;
        var spread;
        var width = 800;
        var height = 140;
        var padding = 9;
        var points = [];
        var linePath = '';
        var x;
        var y;
        var j;

        if (!chartLine || !chartArea) {
            return;
        }

        for (j = 0; j < history.length; j += 1) {
            if (isFiniteNumber(history[j].views)) {
                values.push(history[j].views);
            }
        }

        if (!values.length) {
            chartLine.setAttribute('d', '');
            chartArea.setAttribute('d', '');
            return;
        }

        min = Math.min.apply(null, values);
        max = Math.max.apply(null, values);
        spread = Math.max(max - min, 1);

        for (j = 0; j < values.length; j += 1) {
            x = values.length === 1 ? 0 : (j / (values.length - 1)) * width;
            y = height - padding - ((values[j] - min) / spread) * (height - padding * 2);
            points.push([x, y]);
        }

        for (j = 0; j < points.length; j += 1) {
            if (j > 0) {
                linePath += ' ';
            }
            linePath += (j === 0 ? 'M ' : 'L ') + points[j][0].toFixed(2) + ' ' + points[j][1].toFixed(2);
        }

        chartLine.setAttribute('d', linePath);
        chartArea.setAttribute('d', linePath + ' L ' + width + ' ' + height + ' L 0 ' + height + ' Z');
    }

    function persistSession() {
        if (!videoId || !window.localStorage) {
            return;
        }
        try {
            window.localStorage.setItem(storageKey, JSON.stringify({
                savedAt: new Date().getTime(),
                sessionStart: sessionStart,
                lastStats: lastStats,
                history: history.slice(-60)
            }));
        } catch (error) {
        }
    }

    function restoreSession() {
        var raw;
        var saved;
        var savedAt;

        if (!videoId || !window.localStorage) {
            return;
        }

        try {
            raw = window.localStorage.getItem(storageKey);
            if (!raw) {
                return;
            }
            saved = JSON.parse(raw);
            savedAt = Number(saved.savedAt || 0);
            if (!savedAt || new Date().getTime() - savedAt > 21600000) {
                window.localStorage.removeItem(storageKey);
                return;
            }
            if (saved.sessionStart) {
                sessionStart = saved.sessionStart;
            }
            if (saved.lastStats) {
                lastStats = saved.lastStats;
            }
            if (saved.history && saved.history.length) {
                history = saved.history.slice(-60);
                renderChart();
            }
        } catch (error) {
        }
    }

    function applyPayload(data) {
        var rawStats = data && data.stats ? data.stats : {};
        var stats = {
            views: toNumber(rawStats.views),
            likes: toNumber(rawStats.likes),
            comments: toNumber(rawStats.comments),
            shares: toNumber(rawStats.shares)
        };
        var available = 0;
        var key;

        if (!sessionStart) {
            sessionStart = cloneStats(stats);
        }

        learnGrowth(stats);

        for (i = 0; i < statKeys.length; i += 1) {
            key = statKeys[i];
            updateStat(
                key,
                isFiniteNumber(displayStats[key]) ? displayStats[key] : stats[key]
            );
            if (isFiniteNumber(stats[key])) {
                available += 1;
            }
        }

        if (authorElement && data.author_name) {
            authorElement.textContent = '@' + String(data.author_name).replace(/^@/, '');
        }
        if (titleElement && data.title) {
            titleElement.textContent = data.title;
        }
        if (imageElement && data.thumbnail_url) {
            imageElement.src = data.thumbnail_url;
            imageElement.hidden = false;
        }

        if (isFiniteNumber(stats.views)) {
            history.push({ views: stats.views, at: new Date().getTime() });
            history = history.slice(-60);
            renderChart();
        }

        if (data.stale_fallback) {
            setStatus(data.last_error ? 'Oude snapshot - ' + data.last_error : 'Oude snapshot - TikTok live refresh mislukt', false);
        } else if (data.precision === 'raw_integer') {
            setStatus('Live teller actief · echte snapshots elke 4 sec · tussendoor geschat', true);
        } else if (available === 4) {
            setStatus('Live teller actief · bron is afgerond · tussendoor geschat', false);
        } else {
            setStatus('Live Count actief - ' + available + '/4 beschikbaar', available > 0);
        }

        if (updatedElement) {
            updatedElement.textContent = 'Bijgewerkt ' + new Date().toLocaleTimeString('nl-NL');
        }

        lastStats = cloneStats(stats);
        persistSession();
    }

    function scheduleNext() {
        var elapsed = requestStartedAt ? (new Date().getTime() - requestStartedAt) : 0;
        var delay = Math.max(0, pollMs - elapsed);

        if (timer !== null) {
            window.clearTimeout(timer);
        }
        if (!stopped) {
            timer = window.setTimeout(loadStats, delay);
        }
    }

    function finishRequest() {
        request = null;
        scheduleNext();
    }

    function loadStats() {
        var separator;
        var requestUrl;

        if (stopped || !endpoint || request !== null) {
            return;
        }

        requestNumber += 1;
        requestStartedAt = new Date().getTime();
        separator = endpoint.indexOf('?') === -1 ? '?' : '&';
        requestUrl = endpoint + separator
            + 'url=' + encodeURIComponent(videoUrl)
            + '&_live=' + encodeURIComponent(String(new Date().getTime()))
            + '&_request=' + encodeURIComponent(String(requestNumber));

        setStatus('Nieuwe TikTok-data ophalen...', true);

        request = new XMLHttpRequest();
        request.open('GET', requestUrl, true);
        request.setRequestHeader('Accept', 'application/json');
        request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        request.setRequestHeader('Cache-Control', 'no-cache');
        request.timeout = 25000;

        request.onreadystatechange = function () {
            var data;
            var message;

            if (!request || request.readyState !== 4) {
                return;
            }

            try {
                data = JSON.parse(request.responseText || '{}');
            } catch (error) {
                setStatus('Ongeldige serverresponse (' + request.status + ')', false);
                finishRequest();
                return;
            }

            if (request.status < 200 || request.status >= 300 || data.success === false) {
                message = data && data.message ? data.message : 'Live Count request mislukt (' + request.status + ')';
                setStatus(message, false);
                finishRequest();
                return;
            }

            applyPayload(data);
            finishRequest();
        };

        request.onerror = function () {
            setStatus('Netwerkfout bij Live Count', false);
            finishRequest();
        };

        request.ontimeout = function () {
            setStatus('TikTok ophalen duurde te lang', false);
            finishRequest();
        };

        request.send(null);
    }

    if (refreshButton) {
        refreshButton.onclick = function () {
            if (timer !== null) {
                window.clearTimeout(timer);
                timer = null;
            }
            if (request === null) {
                loadStats();
            }
        };
    }

    if (copyButton) {
        copyButton.onclick = function () {
            var originalText = copyButton.textContent;
            var input = document.createElement('textarea');
            input.value = window.location.href;
            input.setAttribute('readonly', 'readonly');
            input.style.position = 'fixed';
            input.style.left = '-9999px';
            document.body.appendChild(input);
            input.select();
            try {
                document.execCommand('copy');
                copyButton.textContent = 'Gekopieerd';
            } catch (error) {
                copyButton.textContent = 'Kopieren mislukt';
            }
            document.body.removeChild(input);
            window.setTimeout(function () {
                copyButton.textContent = originalText;
            }, 1400);
        };
    }

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden && request === null) {
            if (timer !== null) {
                window.clearTimeout(timer);
                timer = null;
            }
            loadStats();
        }
    });

    window.addEventListener('beforeunload', function () {
        stopped = true;
        if (timer !== null) {
            window.clearTimeout(timer);
        }
        if (animationTimer !== null) {
            window.clearTimeout(animationTimer);
        }
        if (request !== null) {
            try {
                request.abort();
            } catch (error) {
            }
        }
    });

    restoreSession();
    animateEstimatedCounters();
    loadStats();
}());
</script>
@endpush
@endisset
