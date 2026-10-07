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
<script type="application/json" id="ttc-live-config">{!! json_encode([
    'endpoint' => route('tiktok-counter.stats', ['videoId' => $videoId]),
    'videoUrl' => $videoUrl,
    'videoId' => (string) $videoId,
    'pollMs' => 4000,
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

<script>
(function () {
    'use strict';

    const configElement = document.getElementById('ttc-live-config');
    const root = document.getElementById('ttc-result');

    if (!configElement || !root) {
        return;
    }

    let config;

    try {
        config = JSON.parse(configElement.textContent || '{}');
    } catch (error) {
        console.error('Live Count config error:', error);
        return;
    }

    const endpoint = String(config.endpoint || '');
    const videoUrl = String(config.videoUrl || '');
    const videoId = String(config.videoId || '');
    const pollMs = Math.max(4000, Number(config.pollMs || 4000));
    const pollSeconds = Math.max(1, Math.round(pollMs / 1000));
    const formatter = new Intl.NumberFormat('nl-NL');
    const statKeys = ['views', 'likes', 'comments', 'shares'];

    const statElements = {};
    const deltaElements = {};

    statKeys.forEach(function (key) {
        statElements[key] = document.querySelector('[data-stat="' + key + '"]');
        deltaElements[key] = document.querySelector('[data-delta="' + key + '"]');
    });

    const statusText = document.getElementById('ttc-status-text');
    const statusDot = document.getElementById('ttc-status-dot');
    const updatedElement = document.getElementById('ttc-updated');
    const authorElement = document.getElementById('ttc-author');
    const titleElement = document.getElementById('ttc-video-title');
    const imageElement = document.getElementById('ttc-thumb-image');
    const refreshButton = document.getElementById('ttc-refresh');
    const copyButton = document.getElementById('ttc-copy');
    const chartLine = document.getElementById('ttc-chart-line');
    const chartArea = document.getElementById('ttc-chart-area');

    let sessionStart = null;
    let lastStats = null;
    let history = [];
    let stopped = false;
    let requestNumber = 0;
    let pollTimerId = null;
    let activeController = null;
    let requestInFlight = false;
    const storageKey = 'mashal:tiktok-live:' + videoId;

    function restoreSession() {
        if (!videoId) {
            return;
        }

        try {
            const raw = window.localStorage.getItem(storageKey);

            if (!raw) {
                return;
            }

            const saved = JSON.parse(raw);
            const savedAt = Number(saved.savedAt || 0);

            // Een oude sessie na 6 uur niet opnieuw gebruiken.
            if (!savedAt || Date.now() - savedAt > 6 * 60 * 60 * 1000) {
                window.localStorage.removeItem(storageKey);
                return;
            }

            if (saved.sessionStart && typeof saved.sessionStart === 'object') {
                sessionStart = saved.sessionStart;
            }

            if (saved.lastStats && typeof saved.lastStats === 'object') {
                lastStats = saved.lastStats;
            }

            if (Array.isArray(saved.history)) {
                history = saved.history.slice(-60);
                renderChart();
            }
        } catch (error) {
            console.warn('Live Count sessie kon niet worden hersteld:', error);
        }
    }

    function persistSession() {
        if (!videoId) {
            return;
        }

        try {
            window.localStorage.setItem(storageKey, JSON.stringify({
                savedAt: Date.now(),
                sessionStart: sessionStart,
                lastStats: lastStats,
                history: history.slice(-60)
            }));
        } catch (error) {
            // localStorage kan geblokkeerd zijn; Live Count blijft dan gewoon werken.
        }
    }

    function toNumber(value) {
        if (value === null || value === undefined || value === '') {
            return null;
        }

        const number = Number(value);
        return Number.isFinite(number) ? number : null;
    }

    function formatNumber(value) {
        return formatter.format(Number(value || 0));
    }

    function setStatus(text, ok) {
        if (statusText) {
            statusText.textContent = text;
        }

        if (statusDot) {
            const success = ok !== false;
            statusDot.style.background = success ? '#6ee7a8' : '#ff8095';
            statusDot.style.boxShadow = success
                ? '0 0 13px rgba(110,231,168,.62)'
                : '0 0 13px rgba(255,128,149,.55)';
        }
    }

    function animateNumber(element, fromValue, toValue) {
        if (!element || !Number.isFinite(toValue)) {
            return;
        }

        const from = Number.isFinite(fromValue) ? fromValue : toValue;
        const difference = toValue - from;
        const duration = 520;
        const startedAt = performance.now();

        function frame(now) {
            const progress = Math.min((now - startedAt) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = Math.round(from + difference * eased);

            element.textContent = formatNumber(current);

            if (progress < 1) {
                requestAnimationFrame(frame);
            }
        }

        requestAnimationFrame(frame);
    }

    function updateStat(key, value) {
        const element = statElements[key];
        const deltaElement = deltaElements[key];

        if (!element) {
            return;
        }

        element.classList.remove('ttc-loading');

        if (!Number.isFinite(value)) {
            element.textContent = '—';

            if (deltaElement) {
                deltaElement.textContent = 'Niet beschikbaar';
                deltaElement.style.color = '#65707e';
            }

            return;
        }

        const previousValue =
            lastStats && Number.isFinite(lastStats[key])
                ? lastStats[key]
                : value;

        animateNumber(element, previousValue, value);

        if (
            deltaElement &&
            sessionStart &&
            Number.isFinite(sessionStart[key])
        ) {
            const delta = value - sessionStart[key];

            deltaElement.textContent =
                (delta >= 0 ? '+' : '') +
                formatNumber(delta) +
                ' deze sessie';

            deltaElement.style.color =
                delta >= 0 ? '#6ee7a8' : '#ff8095';
        }
    }

    function renderChart() {
        if (!chartLine || !chartArea) {
            return;
        }

        const values = history
            .map(function (item) {
                return item.views;
            })
            .filter(function (value) {
                return Number.isFinite(value);
            });

        if (!values.length) {
            chartLine.setAttribute('d', '');
            chartArea.setAttribute('d', '');
            return;
        }

        const width = 800;
        const height = 140;
        const padding = 9;
        const min = Math.min.apply(null, values);
        const max = Math.max.apply(null, values);
        const spread = Math.max(max - min, 1);

        const points = values.map(function (value, index) {
            const x =
                values.length === 1
                    ? 0
                    : (index / (values.length - 1)) * width;

            const y =
                height -
                padding -
                ((value - min) / spread) * (height - padding * 2);

            return [x, y];
        });

        const linePath = points
            .map(function (point, index) {
                const command = index === 0 ? 'M' : 'L';

                return (
                    command +
                    ' ' +
                    point[0].toFixed(2) +
                    ' ' +
                    point[1].toFixed(2)
                );
            })
            .join(' ');

        chartLine.setAttribute('d', linePath);
        chartArea.setAttribute(
            'd',
            linePath +
                ' L ' +
                width +
                ' ' +
                height +
                ' L 0 ' +
                height +
                ' Z'
        );
    }

    function applyPayload(data) {
        const rawStats =
            data &&
            typeof data.stats === 'object' &&
            data.stats !== null
                ? data.stats
                : {};

        const stats = {
            views: toNumber(rawStats.views),
            likes: toNumber(rawStats.likes),
            comments: toNumber(rawStats.comments),
            shares: toNumber(rawStats.shares)
        };

        if (!sessionStart) {
            sessionStart = Object.assign({}, stats);
        }

        statKeys.forEach(function (key) {
            updateStat(key, stats[key]);
        });

        if (authorElement && data.author_name) {
            authorElement.textContent =
                '@' + String(data.author_name).replace(/^@/, '');
        }

        if (titleElement && data.title) {
            titleElement.textContent = data.title;
        }

        if (imageElement && data.thumbnail_url) {
            if (imageElement.src !== data.thumbnail_url) {
                imageElement.src = data.thumbnail_url;
            }

            imageElement.hidden = false;
        }

        if (Number.isFinite(stats.views)) {
            history.push({
                views: stats.views,
                at: Date.now()
            });

            history = history.slice(-60);
            renderChart();
        }

        const availableCount = statKeys.filter(function (key) {
            return Number.isFinite(stats[key]);
        }).length;

        setStatus(
            availableCount === 4
                ? 'Live Count actief · elke ' + pollSeconds + ' sec'
                : 'Live Count actief · ' + availableCount + '/4 beschikbaar',
            availableCount > 0
        );

        if (updatedElement) {
            updatedElement.textContent =
                'Bijgewerkt ' +
                new Date().toLocaleTimeString('nl-NL', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
        }

        lastStats = Object.assign({}, stats);
        persistSession();
    }

    async function fetchLiveStats(force) {
        if (stopped || !endpoint) {
            return;
        }

        // Belangrijk: een automatische poll mag een nog lopende TikTok-request
        // niet afbreken. TikTok kan soms langer dan 4 seconden antwoorden.
        if (requestInFlight && !force) {
            return;
        }

        if (requestInFlight && force && activeController) {
            activeController.abort();
        }

        requestInFlight = true;
        requestNumber += 1;

        activeController = new AbortController();
        const currentController = activeController;

        const params = new URLSearchParams();
        params.set('url', videoUrl);
        params.set('_live', String(Date.now()));
        params.set('_request', String(requestNumber));

        setStatus('Nieuwe TikTok-data ophalen…', true);

        try {
            const response = await fetch(
                endpoint + '?' + params.toString(),
                {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Cache-Control': 'no-cache',
                        'Pragma': 'no-cache'
                    },
                    cache: 'no-store',
                    credentials: 'same-origin',
                    signal: currentController.signal
                }
            );

            let data = null;

            try {
                data = await response.json();
            } catch (jsonError) {
                throw new Error('Ongeldige serverresponse (' + response.status + ')');
            }

            if (!response.ok || data.success === false) {
                throw new Error(
                    data.message ||
                    'Live Count request mislukt (' +
                    response.status +
                    ')'
                );
            }

            if (activeController !== currentController) {
                return;
            }

            applyPayload(data);
        } catch (error) {
            if (error && error.name === 'AbortError') {
                return;
            }

            console.error('TikTok Live Count fout:', error);

            setStatus(
                error && error.message
                    ? error.message
                    : 'Live ophalen mislukt',
                false
            );
        } finally {
            if (activeController === currentController) {
                activeController = null;
                requestInFlight = false;
            }
        }
    }

    function clearPollTimer() {
        if (pollTimerId !== null) {
            window.clearTimeout(pollTimerId);
            pollTimerId = null;
        }
    }

    async function pollOnce() {
        clearPollTimer();

        await fetchLiveStats(false);

        if (!stopped) {
            pollTimerId = window.setTimeout(pollOnce, pollMs);
        }
    }

    function startPolling() {
        clearPollTimer();
        pollOnce();
    }

    if (refreshButton) {
        refreshButton.addEventListener('click', async function () {
            clearPollTimer();
            await fetchLiveStats(true);

            if (!stopped) {
                pollTimerId = window.setTimeout(pollOnce, pollMs);
            }
        });
    }

    if (copyButton) {
        copyButton.addEventListener('click', async function () {
            const originalText = copyButton.textContent;

            try {
                await navigator.clipboard.writeText(window.location.href);
                copyButton.textContent = '✓ Gekopieerd';
            } catch (error) {
                copyButton.textContent = 'Kopiëren mislukt';
            }

            window.setTimeout(function () {
                copyButton.textContent = originalText;
            }, 1400);
        });
    }

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            clearPollTimer();
            pollOnce();
        }
    });

    window.addEventListener('beforeunload', function () {
        stopped = true;

        clearPollTimer();

        if (activeController) {
            activeController.abort();
        }
    });

    restoreSession();
    startPolling();
})();
</script>
@endpush
@endisset
