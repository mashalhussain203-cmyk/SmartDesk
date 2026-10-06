@extends('layouts.site-layout')

@section('title', 'TikTok Live Count | Mashal Studio')
@section('meta_description', 'Volg de publieke statistieken van een TikTok-video in Mashal Studio.')

@push('styles')
<style>
    .ttc-page {
        --ttc-bg: #050609;
        --ttc-panel: rgba(14, 17, 23, .86);
        --ttc-panel-strong: #11151d;
        --ttc-line: rgba(255,255,255,.09);
        --ttc-line-strong: rgba(255,255,255,.16);
        --ttc-text: #f7f8fb;
        --ttc-muted: #858d9a;
        --ttc-accent: #7a6cff;
        --ttc-accent-2: #42a5ff;
        --ttc-cyan: #74e7df;
        min-height: calc(100vh - 78px);
        padding: 72px 0 110px;
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(circle at 50% -8%, rgba(122,108,255,.16), transparent 34rem),
            radial-gradient(circle at 85% 35%, rgba(66,165,255,.06), transparent 28rem),
            #050609;
    }

    .ttc-page::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        opacity: .34;
        background-image:
            linear-gradient(rgba(255,255,255,.024) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,.024) 1px, transparent 1px);
        background-size: 64px 64px;
        mask-image: linear-gradient(to bottom, #000, transparent 78%);
    }

    .ttc-shell {
        width: min(calc(100% - 34px), 1180px);
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    .ttc-hero {
        max-width: 820px;
        margin: 0 auto 42px;
        text-align: center;
    }

    .ttc-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 18px;
        color: #a49cff;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .15em;
        text-transform: uppercase;
    }

    .ttc-kicker::before {
        content: "";
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #7a6cff;
        box-shadow: 0 0 20px rgba(122,108,255,.8);
    }

    .ttc-title {
        margin: 0;
        color: #fff;
        font-size: clamp(44px, 7vw, 82px);
        line-height: .95;
        letter-spacing: -.065em;
        font-weight: 680;
    }

    .ttc-title span {
        color: #747b88;
    }

    .ttc-subtitle {
        max-width: 690px;
        margin: 22px auto 0;
        color: var(--ttc-muted);
        font-size: 15px;
        line-height: 1.8;
    }

    .ttc-search {
        max-width: 910px;
        margin: 0 auto 30px;
        padding: 9px;
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 9px;
        border: 1px solid var(--ttc-line-strong);
        border-radius: 18px;
        background: rgba(11,14,19,.88);
        box-shadow: 0 24px 70px rgba(0,0,0,.34), inset 0 1px 0 rgba(255,255,255,.035);
        backdrop-filter: blur(18px);
    }

    .ttc-search input {
        min-width: 0;
        height: 56px;
        padding: 0 16px;
        border: 0;
        outline: 0;
        color: #eef1f6;
        background: transparent;
        font: inherit;
        font-size: 14px;
    }

    .ttc-search input::placeholder { color: #5d6572; }

    .ttc-button {
        min-height: 56px;
        padding: 0 22px;
        border: 1px solid rgba(122,108,255,.45);
        border-radius: 12px;
        color: #fff;
        background: linear-gradient(135deg, #7a6cff, #4d8dff);
        box-shadow: 0 14px 34px rgba(73,74,220,.24), inset 0 1px 0 rgba(255,255,255,.18);
        font: inherit;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        transition: .2s ease;
    }

    .ttc-button:hover { transform: translateY(-1px); filter: brightness(1.06); }

    .ttc-error {
        max-width: 910px;
        margin: -12px auto 24px;
        padding: 12px 15px;
        border: 1px solid rgba(255,108,128,.24);
        border-radius: 12px;
        color: #ffb6c1;
        background: rgba(255,108,128,.06);
        font-size: 13px;
    }

    .ttc-result {
        display: grid;
        grid-template-columns: 310px minmax(0, 1fr);
        gap: 18px;
        align-items: stretch;
    }

    .ttc-card {
        border: 1px solid var(--ttc-line);
        border-radius: 22px;
        background: linear-gradient(180deg, rgba(18,22,30,.88), rgba(10,13,18,.88));
        box-shadow: 0 26px 80px rgba(0,0,0,.24), inset 0 1px 0 rgba(255,255,255,.025);
        backdrop-filter: blur(20px);
    }

    .ttc-preview {
        padding: 14px;
        display: flex;
        flex-direction: column;
    }

    .ttc-thumb {
        position: relative;
        min-height: 465px;
        overflow: hidden;
        border-radius: 15px;
        background:
            radial-gradient(circle at 50% 25%, rgba(122,108,255,.3), transparent 28%),
            linear-gradient(160deg, #171b26, #080a0e 62%);
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
        background: linear-gradient(to top, rgba(4,5,8,.7), transparent 38%);
        pointer-events: none;
    }

    .ttc-play {
        width: 54px;
        height: 54px;
        position: absolute;
        left: 50%;
        top: 50%;
        z-index: 2;
        transform: translate(-50%,-50%);
        display: grid;
        place-items: center;
        border: 1px solid rgba(255,255,255,.19);
        border-radius: 50%;
        color: #fff;
        background: rgba(5,6,9,.5);
        backdrop-filter: blur(10px);
        text-decoration: none;
    }

    .ttc-video-meta { padding: 16px 3px 2px; }
    .ttc-author { color: #a9a1ff; font-size: 12px; font-weight: 800; }
    .ttc-video-title { margin: 7px 0 0; color: #d9dde5; font-size: 13px; line-height: 1.55; }

    .ttc-dashboard { padding: 20px; }

    .ttc-dashboard-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 16px;
    }

    .ttc-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #8f98a7;
        font-size: 11px;
        font-weight: 700;
    }

    .ttc-status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #74dfa7;
        box-shadow: 0 0 14px rgba(116,223,167,.65);
    }

    .ttc-updated { color: #616a77; font-size: 11px; }

    .ttc-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0,1fr));
        gap: 12px;
    }

    .ttc-stat {
        min-height: 145px;
        padding: 18px;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 17px;
        background: rgba(255,255,255,.022);
    }

    .ttc-stat::after {
        content: "";
        width: 90px;
        height: 90px;
        position: absolute;
        right: -26px;
        top: -32px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(122,108,255,.15), transparent 68%);
    }

    .ttc-stat-label {
        color: #747d8a;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .ttc-stat-value {
        margin-top: 13px;
        color: #fff;
        font-size: clamp(34px, 4vw, 54px);
        line-height: 1;
        font-weight: 690;
        letter-spacing: -.05em;
        font-variant-numeric: tabular-nums;
    }

    .ttc-stat-delta { margin-top: 10px; color: #74dfa7; font-size: 11px; font-weight: 750; }

    .ttc-chart-wrap {
        margin-top: 12px;
        padding: 16px 16px 12px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 17px;
        background: rgba(255,255,255,.018);
    }

    .ttc-chart-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 11px;
    }

    .ttc-chart-title { color: #d6dae2; font-size: 12px; font-weight: 800; }
    .ttc-chart-note { color: #626b78; font-size: 10px; }
    .ttc-chart { width: 100%; height: 150px; display: block; }

    .ttc-actions {
        margin-top: 12px;
        display: grid;
        grid-template-columns: repeat(3, minmax(0,1fr));
        gap: 10px;
    }

    .ttc-action {
        min-height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 1px solid var(--ttc-line);
        border-radius: 11px;
        color: #aeb5c0;
        background: rgba(255,255,255,.02);
        text-decoration: none;
        font: inherit;
        font-size: 11px;
        font-weight: 760;
        cursor: pointer;
    }

    .ttc-empty {
        max-width: 910px;
        margin: 36px auto 0;
        padding: 34px;
        text-align: center;
        border: 1px solid var(--ttc-line);
        border-radius: 22px;
        background: rgba(255,255,255,.018);
    }

    .ttc-empty strong { display: block; color: #dfe3e9; font-size: 15px; }
    .ttc-empty span { display: block; max-width: 560px; margin: 8px auto 0; color: #737c89; font-size: 12px; line-height: 1.7; }

    .ttc-loading { animation: ttcPulse 1.25s ease-in-out infinite; }
    @keyframes ttcPulse { 50% { opacity: .45; } }

    @media (max-width: 900px) {
        .ttc-result { grid-template-columns: 1fr; }
        .ttc-preview { display: grid; grid-template-columns: 180px 1fr; gap: 16px; }
        .ttc-thumb { min-height: 280px; }
        .ttc-video-meta { align-self: end; padding: 0 6px 12px 0; }
    }

    @media (max-width: 640px) {
        .ttc-page { padding-top: 48px; }
        .ttc-search { grid-template-columns: 1fr; }
        .ttc-button { width: 100%; }
        .ttc-preview { display: block; }
        .ttc-thumb { min-height: 460px; }
        .ttc-stats { grid-template-columns: 1fr 1fr; }
        .ttc-stat { min-height: 118px; padding: 14px; }
        .ttc-stat-value { font-size: 31px; }
        .ttc-actions { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<section class="ttc-page">
    <div class="ttc-shell">
        <header class="ttc-hero">
            <div class="ttc-kicker">Mashal Social Tools</div>
            <h1 class="ttc-title">TikTok <span>Live Count.</span></h1>
            <p class="ttc-subtitle">Plak een openbare TikTok-video en volg views, likes, reacties en shares vanuit één live dashboard.</p>
        </header>

        <form class="ttc-search" method="POST" action="{{ route('tiktok-counter.lookup') }}">
            @csrf
            <input
                type="url"
                name="url"
                value="{{ old('url', $videoUrl ?? '') }}"
                placeholder="https://www.tiktok.com/@creator/video/..."
                autocomplete="off"
                required
            >
            <button class="ttc-button" type="submit">Start Live Count</button>
        </form>

        @error('url')
            <div class="ttc-error">{{ $message }}</div>
        @enderror

        @isset($videoId)
            <div class="ttc-result" id="ttc-result" data-video-id="{{ $videoId }}" data-video-url="{{ $videoUrl }}">
                <aside class="ttc-card ttc-preview">
                    <div class="ttc-thumb" id="ttc-thumb">
                        <img id="ttc-thumb-image" alt="TikTok thumbnail" hidden>
                        <a class="ttc-play" href="{{ $videoUrl }}" target="_blank" rel="noopener" aria-label="Open op TikTok">▶</a>
                    </div>
                    <div class="ttc-video-meta">
                        <div class="ttc-author" id="ttc-author">TikTok video</div>
                        <p class="ttc-video-title" id="ttc-video-title">Video-informatie laden…</p>
                    </div>
                </aside>

                <div class="ttc-card ttc-dashboard">
                    <div class="ttc-dashboard-head">
                        <div class="ttc-status"><span class="ttc-status-dot" id="ttc-status-dot"></span><span id="ttc-status-text">Verbinden…</span></div>
                        <div class="ttc-updated" id="ttc-updated">Nog niet bijgewerkt</div>
                    </div>

                    <div class="ttc-stats">
                        <div class="ttc-stat">
                            <div class="ttc-stat-label">Views</div>
                            <div class="ttc-stat-value ttc-loading" data-stat="views">—</div>
                            <div class="ttc-stat-delta" data-delta="views">Sessie gestart</div>
                        </div>
                        <div class="ttc-stat">
                            <div class="ttc-stat-label">Likes</div>
                            <div class="ttc-stat-value ttc-loading" data-stat="likes">—</div>
                            <div class="ttc-stat-delta" data-delta="likes">Sessie gestart</div>
                        </div>
                        <div class="ttc-stat">
                            <div class="ttc-stat-label">Comments</div>
                            <div class="ttc-stat-value ttc-loading" data-stat="comments">—</div>
                            <div class="ttc-stat-delta" data-delta="comments">Sessie gestart</div>
                        </div>
                        <div class="ttc-stat">
                            <div class="ttc-stat-label">Shares</div>
                            <div class="ttc-stat-value ttc-loading" data-stat="shares">—</div>
                            <div class="ttc-stat-delta" data-delta="shares">Sessie gestart</div>
                        </div>
                    </div>

                    <div class="ttc-chart-wrap">
                        <div class="ttc-chart-head">
                            <div class="ttc-chart-title">View growth</div>
                            <div class="ttc-chart-note">Deze browsersessie</div>
                        </div>
                        <svg class="ttc-chart" id="ttc-chart" viewBox="0 0 800 150" preserveAspectRatio="none" aria-label="View growth chart">
                            <defs>
                                <linearGradient id="ttcChartFill" x1="0" x2="0" y1="0" y2="1">
                                    <stop offset="0%" stop-color="#7a6cff" stop-opacity=".28"/>
                                    <stop offset="100%" stop-color="#7a6cff" stop-opacity="0"/>
                                </linearGradient>
                            </defs>
                            <path id="ttc-chart-area" fill="url(#ttcChartFill)" d=""></path>
                            <path id="ttc-chart-line" fill="none" stroke="#8f84ff" stroke-width="3" vector-effect="non-scaling-stroke" d=""></path>
                        </svg>
                    </div>

                    <div class="ttc-actions">
                        <button class="ttc-action" type="button" id="ttc-refresh">↻ Vernieuwen</button>
                        <button class="ttc-action" type="button" id="ttc-copy">⧉ Link kopiëren</button>
                        <a class="ttc-action" href="{{ $videoUrl }}" target="_blank" rel="noopener">↗ Open TikTok</a>
                    </div>
                </div>
            </div>
        @else
            <div class="ttc-empty">
                <strong>Start met een TikTok-link</strong>
                <span>Gebruik een normale TikTok-video-URL of een gedeelde korte vm.tiktok.com-link. De counter opent daarna automatisch.</span>
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
    'pollMs' => 4000,
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    const root = document.getElementById('ttc-result');
    const configElement = document.getElementById('ttc-live-config');

    if (!root || !configElement) {
        return;
    }

    let config;

    try {
        config = JSON.parse(configElement.textContent || '{}');
    } catch (error) {
        console.error('Live Count configuratie kon niet worden gelezen:', error);
        return;
    }

    const endpoint = String(config.endpoint || '');
    const videoUrl = String(config.videoUrl || '');
    const pollMs = Math.max(2500, Number(config.pollMs || 4000));
    const numberFormatter = new Intl.NumberFormat('nl-NL');
    const statKeys = ['views', 'likes', 'comments', 'shares'];

    const elements = {
        statusText: document.getElementById('ttc-status-text'),
        statusDot: document.getElementById('ttc-status-dot'),
        updated: document.getElementById('ttc-updated'),
        author: document.getElementById('ttc-author'),
        title: document.getElementById('ttc-video-title'),
        image: document.getElementById('ttc-thumb-image'),
        refresh: document.getElementById('ttc-refresh'),
        copy: document.getElementById('ttc-copy'),
        chartLine: document.getElementById('ttc-chart-line'),
        chartArea: document.getElementById('ttc-chart-area'),
    };

    const statElements = {};
    const deltaElements = {};

    statKeys.forEach((key) => {
        statElements[key] = document.querySelector(`[data-stat="${key}"]`);
        deltaElements[key] = document.querySelector(`[data-delta="${key}"]`);
    });

    let sessionStart = null;
    let lastStats = null;
    let history = [];
    let stopped = false;
    let timer = null;
    let activeController = null;
    let requestNumber = 0;

    function setStatus(text, ok = true) {
        if (elements.statusText) {
            elements.statusText.textContent = text;
        }

        if (elements.statusDot) {
            elements.statusDot.style.background = ok ? '#74dfa7' : '#ff7c91';
            elements.statusDot.style.boxShadow = ok
                ? '0 0 14px rgba(116,223,167,.65)'
                : '0 0 14px rgba(255,124,145,.55)';
        }
    }

    function toNumber(value) {
        const parsed = Number(value);
        return Number.isFinite(parsed) ? parsed : null;
    }

    function formatNumber(value) {
        return numberFormatter.format(Number(value || 0));
    }

    function animateNumber(element, fromValue, toValue, duration = 650) {
        if (!element) {
            return;
        }

        const from = Number.isFinite(fromValue) ? fromValue : toValue;
        const to = Number.isFinite(toValue) ? toValue : 0;
        const startedAt = performance.now();
        const difference = to - from;

        function frame(now) {
            const progress = Math.min((now - startedAt) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = Math.round(from + (difference * eased));

            element.textContent = formatNumber(current);

            if (progress < 1) {
                requestAnimationFrame(frame);
            }
        }

        requestAnimationFrame(frame);
    }

    function renderChart() {
        if (!elements.chartLine || !elements.chartArea) {
            return;
        }

        const values = history
            .map((item) => item.views)
            .filter((value) => Number.isFinite(value));

        if (!values.length) {
            elements.chartLine.setAttribute('d', '');
            elements.chartArea.setAttribute('d', '');
            return;
        }

        const min = Math.min(...values);
        const max = Math.max(...values);
        const spread = Math.max(max - min, 1);
        const width = 800;
        const height = 150;
        const padding = 10;

        const points = values.map((value, index) => {
            const x = values.length === 1
                ? 0
                : (index / (values.length - 1)) * width;

            const y = height - padding
                - ((value - min) / spread) * (height - padding * 2);

            return [x, y];
        });

        const linePath = points
            .map((point, index) => {
                const command = index === 0 ? 'M' : 'L';
                return `${command} ${point[0].toFixed(2)} ${point[1].toFixed(2)}`;
            })
            .join(' ');

        elements.chartLine.setAttribute('d', linePath);
        elements.chartArea.setAttribute(
            'd',
            `${linePath} L ${width} ${height} L 0 ${height} Z`
        );
    }

    function updateStat(key, value) {
        const valueElement = statElements[key];
        const deltaElement = deltaElements[key];

        if (!valueElement) {
            return;
        }

        valueElement.classList.remove('ttc-loading');

        if (!Number.isFinite(value)) {
            valueElement.textContent = '—';

            if (deltaElement) {
                deltaElement.textContent = 'Niet beschikbaar';
                deltaElement.style.color = '#69727f';
            }

            return;
        }

        const previousValue = lastStats && Number.isFinite(lastStats[key])
            ? lastStats[key]
            : value;

        animateNumber(valueElement, previousValue, value);

        if (deltaElement && sessionStart && Number.isFinite(sessionStart[key])) {
            const delta = value - sessionStart[key];
            deltaElement.textContent = `${delta >= 0 ? '+' : ''}${formatNumber(delta)} deze sessie`;
            deltaElement.style.color = delta >= 0 ? '#74dfa7' : '#ff9cad';
        }
    }

    function applyPayload(data) {
        const rawStats = data && typeof data.stats === 'object' && data.stats !== null
            ? data.stats
            : {};

        const stats = {
            views: toNumber(rawStats.views),
            likes: toNumber(rawStats.likes),
            comments: toNumber(rawStats.comments),
            shares: toNumber(rawStats.shares),
        };

        if (!sessionStart) {
            sessionStart = { ...stats };
        }

        statKeys.forEach((key) => updateStat(key, stats[key]));

        if (elements.author) {
            elements.author.textContent = data.author_name
                ? `@${String(data.author_name).replace(/^@/, '')}`
                : 'TikTok video';
        }

        if (elements.title) {
            elements.title.textContent = data.title || 'Openbare TikTok-video';
        }

        if (elements.image && data.thumbnail_url) {
            if (elements.image.src !== data.thumbnail_url) {
                elements.image.src = data.thumbnail_url;
            }

            elements.image.hidden = false;
        }

        const updatedAt = data.updated_at ? new Date(data.updated_at) : new Date();

        if (elements.updated) {
            elements.updated.textContent = `Live bijgewerkt ${updatedAt.toLocaleTimeString('nl-NL', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            })}`;
        }

        const availableCount = statKeys.filter((key) => Number.isFinite(stats[key])).length;

        setStatus(
            availableCount === statKeys.length
                ? `Live Count actief · elke ${Math.round(pollMs / 1000)} sec`
                : `Live Count actief · ${availableCount}/4 statistieken beschikbaar`,
            availableCount > 0
        );

        if (Number.isFinite(stats.views)) {
            history.push({
                views: stats.views,
                at: Date.now(),
            });

            history = history.slice(-60);
            renderChart();
        }

        lastStats = { ...stats };
    }

    function scheduleNext(delay = pollMs) {
        if (stopped) {
            return;
        }

        window.clearTimeout(timer);
        timer = window.setTimeout(fetchLiveStats, delay);
    }

    async function fetchLiveStats() {
        if (stopped || !endpoint) {
            return;
        }

        requestNumber += 1;
        const currentRequest = requestNumber;

        if (activeController) {
            activeController.abort();
        }

        activeController = new AbortController();
        const abortTimer = window.setTimeout(() => {
            activeController?.abort();
        }, 20000);

        const params = new URLSearchParams();
        params.set('url', videoUrl);
        params.set('_live', String(Date.now()));
        params.set('_request', String(currentRequest));

        setStatus('Nieuwe TikTok-data ophalen…', true);

        try {
            const response = await fetch(`${endpoint}?${params.toString()}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Cache-Control': 'no-cache',
                    'Pragma': 'no-cache',
                },
                cache: 'no-store',
                credentials: 'same-origin',
                signal: activeController.signal,
            });

            let data = null;

            try {
                data = await response.json();
            } catch (jsonError) {
                throw new Error(`Ongeldige serverresponse (${response.status})`);
            }

            if (!response.ok || data.success === false) {
                throw new Error(data.message || `Live Count request mislukt (${response.status})`);
            }

            applyPayload(data);
        } catch (error) {
            if (error && error.name === 'AbortError') {
                setStatus('Live request duurde te lang · nieuwe poging volgt', false);
            } else {
                console.error('TikTok Live Count fout:', error);
                setStatus(error?.message || 'Live ophalen mislukt · nieuwe poging volgt', false);
            }

            if (elements.updated) {
                elements.updated.textContent = 'Automatische nieuwe poging volgt';
            }

            document
                .querySelectorAll('.ttc-loading')
                .forEach((element) => element.classList.remove('ttc-loading'));
        } finally {
            window.clearTimeout(abortTimer);
            activeController = null;
            scheduleNext();
        }
    }

    elements.refresh?.addEventListener('click', () => {
        window.clearTimeout(timer);
        fetchLiveStats();
    });

    elements.copy?.addEventListener('click', async (event) => {
        const button = event.currentTarget;
        const originalText = button.textContent;

        try {
            await navigator.clipboard.writeText(window.location.href);
            button.textContent = '✓ Gekopieerd';
        } catch (error) {
            button.textContent = 'Kopiëren mislukt';
        }

        window.setTimeout(() => {
            button.textContent = originalText;
        }, 1400);
    });

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            window.clearTimeout(timer);
            fetchLiveStats();
        }
    });

    window.addEventListener('beforeunload', () => {
        stopped = true;
        window.clearTimeout(timer);
        activeController?.abort();
    });

    // Eerste fetch direct bij het openen van de pagina.
    fetchLiveStats();
});
</script>
@endpush
@endisset
