@extends('layouts.site-layout')

@section('title', 'YouTube Live Views | Mashal Studio')
@section('meta_description', 'Volg publieke YouTube-video views, likes, dislikes en comments live met Mashal Studio.')

@push('styles')
<link rel="stylesheet" href="/vendor/odometer/odometer-theme-minimal.css?v=20261007-2">
<style>
    .ytv-page {
        --ytv-bg: #050608;
        --ytv-panel: rgba(13,16,22,.9);
        --ytv-line: rgba(255,255,255,.075);
        --ytv-text: #f4f6f8;
        --ytv-muted: #77808d;
        --ytv-red: #ff4656;
        --ytv-purple: #7b70ff;
        --ytv-green: #6ee7a8;
        min-height: calc(100vh - 72px);
        padding: 44px 0 96px;
        color: var(--ytv-text);
        background:
            radial-gradient(circle at 50% -150px, rgba(255,70,86,.10), transparent 430px),
            radial-gradient(circle at 88% 20%, rgba(123,112,255,.07), transparent 380px),
            linear-gradient(180deg,#06070a 0%,#050608 100%);
    }
    .ytv-shell { width:min(calc(100% - 32px),1180px); margin:0 auto; }
    .ytv-back {
        display:inline-flex; margin-bottom:24px; color:#6f7885; text-decoration:none;
        font-size:9px; font-weight:800; letter-spacing:.04em;
    }
    .ytv-hero { max-width:760px; margin:0 auto 30px; text-align:center; }
    .ytv-eyebrow {
        display:inline-flex; align-items:center; gap:8px; padding:7px 11px;
        border:1px solid rgba(255,70,86,.18); border-radius:999px;
        color:#ff8e9b; background:rgba(255,70,86,.045);
        font-size:10px; font-weight:850; letter-spacing:.13em; text-transform:uppercase;
    }
    .ytv-eyebrow-dot {
        width:6px; height:6px; border-radius:50%; background:var(--ytv-green);
        box-shadow:0 0 12px rgba(110,231,168,.65);
    }
    .ytv-title {
        margin:18px 0 0; color:#fff; font-size:clamp(42px,6vw,72px);
        line-height:.98; font-weight:720; letter-spacing:-.055em;
    }
    .ytv-title span { color:#747d89; }
    .ytv-copy {
        max-width:650px; margin:17px auto 0; color:#7f8896;
        font-size:13px; line-height:1.75;
    }

    .ytv-search-wrap { max-width:930px; margin:0 auto 20px; position:relative; }
    .ytv-search {
        padding:8px; display:grid; grid-template-columns:minmax(0,1fr) auto; gap:8px;
        border:1px solid var(--ytv-line); border-radius:18px;
        background:rgba(10,12,17,.92);
        box-shadow:0 26px 70px rgba(0,0,0,.28);
    }
    .ytv-input-shell {
        min-width:0; padding:0 14px; display:flex; align-items:center; gap:11px;
    }
    .ytv-input-icon {
        width:29px; height:29px; flex:0 0 29px; display:grid; place-items:center;
        border:1px solid rgba(255,255,255,.07); border-radius:9px;
        color:var(--ytv-red); background:rgba(255,255,255,.02); font-size:12px;
    }
    .ytv-input {
        width:100%; min-width:0; height:54px; border:0; outline:0;
        color:#edf0f4; background:transparent; font-size:13px;
    }
    .ytv-input::placeholder { color:#525b68; }
    .ytv-button {
        min-height:54px; padding:0 22px; border:1px solid rgba(255,83,99,.42);
        border-radius:12px; color:#fff;
        background:linear-gradient(135deg,#ff5363,#d93b4a);
        font-size:11px; font-weight:850; cursor:pointer;
    }
    .ytv-button:disabled { opacity:.55; cursor:wait; }

    .ytv-message {
        max-width:930px; min-height:18px; margin:0 auto 8px;
        color:#7c8592; font-size:9px; text-align:center;
    }
    .ytv-results {
        max-width:930px; margin:0 auto 18px; display:grid; gap:7px;
    }
    .ytv-result {
        width:100%; min-width:0; padding:8px 10px; display:grid;
        grid-template-columns:92px minmax(0,1fr) auto; align-items:center; gap:12px;
        border:1px solid rgba(255,255,255,.07); border-radius:14px;
        color:inherit; background:rgba(12,15,20,.92); text-align:left; cursor:pointer;
    }
    .ytv-result:hover { border-color:rgba(255,83,99,.22); background:rgba(18,21,28,.96); }
    .ytv-result-thumb {
        width:92px; aspect-ratio:16/9; border-radius:10px; object-fit:cover;
        background:#171c24;
    }
    .ytv-result-copy { min-width:0; }
    .ytv-result-copy strong {
        display:block; overflow:hidden; color:#eef1f5; font-size:10px;
        text-overflow:ellipsis; white-space:nowrap;
    }
    .ytv-result-copy small {
        display:block; margin-top:4px; overflow:hidden; color:#697281; font-size:8px;
        text-overflow:ellipsis; white-space:nowrap;
    }
    .ytv-result-arrow { color:#ff7584; font-size:18px; }

    .ytv-panel {
        max-width:1040px; margin:0 auto; display:grid;
        grid-template-columns:320px minmax(0,1fr); gap:16px;
    }
    .ytv-card {
        border:1px solid var(--ytv-line); border-radius:24px;
        background:
            radial-gradient(circle at 100% 0%, rgba(255,70,86,.07), transparent 38%),
            linear-gradient(180deg,rgba(15,18,24,.95),rgba(9,11,15,.97));
        box-shadow:0 24px 60px rgba(0,0,0,.22);
    }
    .ytv-profile { padding:18px; }
    .ytv-thumb-wrap {
        width:100%; aspect-ratio:16/9; overflow:hidden;
        border:1px solid rgba(255,255,255,.07); border-radius:16px;
        background:#11151b;
    }
    .ytv-thumb { width:100%; height:100%; object-fit:cover; }
    .ytv-video-title {
        margin-top:16px; color:#eff2f5; font-size:13px; line-height:1.45;
        font-weight:800;
    }
    .ytv-video-id { margin-top:7px; color:#68717e; font-size:8px; word-break:break-all; }
    .ytv-channel { margin-top:6px; color:#8b94a0; font-size:9px; }
    .ytv-video-link {
        margin-top:15px; min-height:38px; display:inline-flex; align-items:center;
        color:#ff8995; text-decoration:none; font-size:9px; font-weight:800;
    }

    .ytv-dashboard { padding:22px; }
    .ytv-status {
        display:flex; align-items:center; justify-content:space-between; gap:12px;
        color:#68717e; font-size:9px;
    }
    .ytv-status strong { color:#76dca1; font-weight:850; }
    .ytv-main {
        padding:30px 0 24px; text-align:center; border-bottom:1px solid var(--ytv-line);
    }
    .ytv-main-label {
        color:#777f8c; font-size:10px; font-weight:850;
        text-transform:uppercase; letter-spacing:.11em;
    }
    .ytv-main-value {
        margin-top:9px; color:#fff; font-size:clamp(50px,8vw,78px);
        line-height:1; font-weight:780; letter-spacing:-.055em;
    }
    .ytv-stats {
        padding-top:18px; display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px;
    }
    .ytv-stat {
        min-height:128px; padding:16px; border:1px solid rgba(255,255,255,.06);
        border-radius:16px; background:rgba(255,255,255,.016);
    }
    .ytv-stat-label {
        color:#737c89; font-size:9px; font-weight:800;
        text-transform:uppercase; letter-spacing:.06em;
    }
    .ytv-stat-value {
        margin-top:27px; color:#f3f5f7; font-size:clamp(27px,4vw,40px);
        line-height:1; font-weight:760; letter-spacing:-.04em;
    }
    .ytv-loading { opacity:.58; }

    .ytv-actions {
        max-width:1040px; margin:14px auto 0; display:flex;
        justify-content:center; flex-wrap:wrap; gap:8px;
    }
    .ytv-action {
        min-height:40px; padding:0 14px; border:1px solid rgba(255,255,255,.08);
        border-radius:11px; color:#a4abb5; background:rgba(255,255,255,.025);
        font-size:9px; font-weight:820; cursor:pointer;
    }
    .ytv-action:hover { color:#f0f2f5; border-color:rgba(255,83,99,.24); }

    .ytv-tool-panel {
        max-width:1040px; margin:14px auto 0; padding:18px;
        border:1px solid var(--ytv-line); border-radius:18px;
        background:rgba(12,15,20,.92);
    }
    .ytv-tool-head {
        display:flex; align-items:center; justify-content:space-between; gap:12px;
        margin-bottom:13px;
    }
    .ytv-tool-head strong { color:#eef1f5; font-size:11px; font-weight:850; }
    .ytv-tool-head span { color:#68717e; font-size:8px; }

    .ytv-compare-search, .ytv-embed-row {
        display:grid; grid-template-columns:minmax(0,1fr) auto; gap:8px;
    }
    .ytv-compare-input, .ytv-embed-code {
        width:100%; min-height:44px; padding:0 12px;
        border:1px solid rgba(255,255,255,.075); border-radius:11px;
        outline:0; color:#e9edf2; background:rgba(255,255,255,.02); font-size:10px;
    }
    .ytv-compare-results { margin-top:9px; display:grid; gap:6px; }
    .ytv-compare-result {
        width:100%; min-height:54px; padding:7px 10px; display:grid;
        grid-template-columns:70px minmax(0,1fr); gap:10px; align-items:center;
        border:1px solid rgba(255,255,255,.06); border-radius:11px;
        color:#dfe3e8; background:rgba(255,255,255,.015); text-align:left; cursor:pointer;
    }
    .ytv-compare-result img {
        width:70px; aspect-ratio:16/9; border-radius:7px; object-fit:cover; background:#171c24;
    }
    .ytv-compare-result strong { display:block; font-size:9px; }
    .ytv-compare-result small { display:block; margin-top:2px; color:#697281; font-size:7px; }
    .ytv-compare-board {
        margin-top:14px; display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px;
    }
    .ytv-compare-side {
        min-width:0; padding:14px; border:1px solid rgba(255,255,255,.06);
        border-radius:14px; background:rgba(255,255,255,.015);
    }
    .ytv-compare-side span {
        display:block; overflow:hidden; color:#7a8491; font-size:8px;
        text-overflow:ellipsis; white-space:nowrap;
    }
    .ytv-compare-side strong {
        display:block; margin-top:8px; color:#fff;
        font-size:clamp(24px,4vw,38px); line-height:1; letter-spacing:-.04em;
    }
    .ytv-compare-delta { margin-top:10px; color:#7adea4; font-size:9px; font-weight:800; text-align:center; }

    .ytv-embed-stack { display:grid; gap:14px; }
    .ytv-embed-group { display:grid; gap:7px; }
    .ytv-embed-label {
        color:#707986; font-size:8px; font-weight:850;
        letter-spacing:.08em; text-transform:uppercase;
    }
    textarea.ytv-embed-code {
        min-height:72px; padding-top:10px; padding-bottom:10px;
        resize:vertical; line-height:1.45;
    }

    .ytv-extras {
        max-width:1040px; margin:16px auto 0; display:grid;
        grid-template-columns:2fr 1fr; gap:16px;
    }
    .ytv-extra { padding:18px; }
    .ytv-extra-head {
        display:flex; align-items:center; justify-content:space-between; gap:12px;
        margin-bottom:16px;
    }
    .ytv-extra-head strong { color:#eef1f5; font-size:11px; font-weight:850; }
    .ytv-extra-head span { color:#68717e; font-size:8px; }
    .ytv-chart { width:100%; height:190px; overflow:visible; }
    .ytv-chart-grid { stroke:rgba(255,255,255,.05); stroke-width:1; }
    .ytv-chart-line {
        fill:none; stroke:currentColor; color:#ff6574; stroke-width:3;
        stroke-linejoin:round; stroke-linecap:round; vector-effect:non-scaling-stroke;
    }
    .ytv-chart-empty {
        margin-top:-105px; color:#596371; font-size:9px;
        text-align:center; pointer-events:none;
    }
    .ytv-advanced { display:grid; gap:9px; }
    .ytv-advanced-row {
        min-height:58px; padding:11px 12px; display:flex; align-items:center;
        justify-content:space-between; gap:12px; border:1px solid rgba(255,255,255,.055);
        border-radius:13px; background:rgba(255,255,255,.014);
    }
    .ytv-advanced-row span {
        color:#6e7784; font-size:8px; font-weight:800;
        text-transform:uppercase; letter-spacing:.06em;
    }
    .ytv-advanced-row strong { color:#f1f3f6; font-size:14px; font-weight:820; }

    .ytv-about {
        max-width:1040px; margin:16px auto 0; padding:20px;
    }
    .ytv-about h3 { margin:0; color:#eef1f5; font-size:14px; font-weight:850; }
    .ytv-about p {
        margin:10px 0 0; color:#737d8a; font-size:10px; line-height:1.7; white-space:pre-line;
    }
    .ytv-empty {
        max-width:930px; margin:18px auto 0; padding:32px 20px;
        border:1px dashed rgba(255,255,255,.08); border-radius:20px;
        color:#66707d; text-align:center; font-size:10px;
    }
    .odometer { font:inherit !important; line-height:inherit !important; }

    @media (max-width:860px) {
        .ytv-panel { grid-template-columns:1fr; }
        .ytv-extras { grid-template-columns:1fr; }
    }
    @media (max-width:680px) {
        .ytv-page { padding-top:34px; }
        .ytv-search, .ytv-compare-search, .ytv-embed-row, .ytv-compare-board {
            grid-template-columns:1fr;
        }
        .ytv-button { width:100%; }
        .ytv-stats { grid-template-columns:1fr; }
        .ytv-result { grid-template-columns:74px minmax(0,1fr) auto; }
        .ytv-result-thumb { width:74px; }
    }
</style>
@endpush

@section('content')
<section class="ytv-page" data-ui-build="20261008-youtube-direct-url-v2">
    <div class="ytv-shell">
        <a class="ytv-back" href="{{ route('live-counts.index') }}">{{ __('← Live Counts') }}</a>

        <header class="ytv-hero">
            <div class="ytv-eyebrow">
                <span class="ytv-eyebrow-dot"></span>
                Mashal Studio · YouTube Live
            </div>
            <h1 class="ytv-title">{{ __('Live') }} <span>{{ __('Views.') }}</span></h1>
            <p class="ytv-copy">
                {{ __('Plak een YouTube-video URL of zoek op titel. Volg daarna views, likes, dislikes en comments automatisch in dezelfde live teller.') }}
            </p>
        </header>

        <div class="ytv-search-wrap">
            <form class="ytv-search" id="ytv-search">
                <div class="ytv-input-shell">
                    <span class="ytv-input-icon" aria-hidden="true">▶</span>
                    <input
                        class="ytv-input"
                        id="ytv-query"
                        type="text"
                        maxlength="255"
                        placeholder="Video URL, titel of zoekterm…"
                        autocomplete="off"
                        required
                        aria-label="YouTube video zoeken"
                    >
                </div>
                <button class="ytv-button" id="ytv-submit" type="submit">{{ __('Zoek video →') }}</button>
            </form>
        </div>

        <div class="ytv-message" id="ytv-message" role="status" aria-live="polite"></div>
        <div class="ytv-results" id="ytv-results" aria-live="polite"></div>

        <div class="ytv-panel" id="ytv-panel" hidden>
            <aside class="ytv-card ytv-profile">
                <div class="ytv-thumb-wrap">
                    <img
                        class="ytv-thumb"
                        id="ytv-thumb"
                        src="/icons/follower-profile.svg?v=1"
                        alt=""
                        referrerpolicy="no-referrer"
                    >
                </div>
                <div class="ytv-video-title" id="ytv-title">{{ __('YouTube video') }}</div>
                <div class="ytv-channel" id="ytv-channel"></div>
                <div class="ytv-video-id" id="ytv-video-id"></div>
                <a
                    class="ytv-video-link"
                    id="ytv-link"
                    href="#"
                    target="_blank"
                    rel="noopener noreferrer"
                >{{ __('Open op YouTube ↗') }}</a>
            </aside>

            <div class="ytv-card ytv-dashboard">
                <div class="ytv-status">
                    <span>{{ __('Live video statistics') }}</span>
                    <strong id="ytv-status">{{ __('Live') }}</strong>
                </div>

                <div class="ytv-main">
                    <div class="ytv-main-label">{{ __('Views') }}</div>
                    <div class="ytv-main-value ytv-loading" id="ytv-views">0</div>
                </div>

                <div class="ytv-stats">
                    <div class="ytv-stat">
                        <div class="ytv-stat-label">{{ __('Likes') }}</div>
                        <div class="ytv-stat-value ytv-loading" id="ytv-likes">0</div>
                    </div>
                    <div class="ytv-stat">
                        <div class="ytv-stat-label">{{ __('Dislikes') }}</div>
                        <div class="ytv-stat-value ytv-loading" id="ytv-dislikes">0</div>
                    </div>
                    <div class="ytv-stat">
                        <div class="ytv-stat-label">{{ __('Comments') }}</div>
                        <div class="ytv-stat-value ytv-loading" id="ytv-comments">0</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="ytv-actions" id="ytv-actions" hidden>
            <button class="ytv-action" id="ytv-change" type="button">{{ __('↺ Change Video') }}</button>
            <button class="ytv-action" id="ytv-compare-toggle" type="button">{{ __('⇄ Compare') }}</button>
            <button class="ytv-action" id="ytv-embed-toggle" type="button">{{ __('&lt;/&gt; Embed') }}</button>
            <button class="ytv-action" id="ytv-advanced-toggle" type="button">{{ __('▦ Advanced Metrics') }}</button>
        </div>

        <section class="ytv-tool-panel" id="ytv-compare-panel" hidden>
            <div class="ytv-tool-head">
                <strong>{{ __('Compare videos') }}</strong>
                <span>{{ __('Live view counts') }}</span>
            </div>
            <div class="ytv-compare-search">
                <input
                    class="ytv-compare-input"
                    id="ytv-compare-query"
                    type="text"
                    maxlength="255"
                    placeholder="Zoek tweede video…"
                    autocomplete="off"
                >
                <button class="ytv-action" id="ytv-compare-search" type="button">{{ __('Zoek →') }}</button>
            </div>
            <div class="ytv-compare-results" id="ytv-compare-results"></div>
            <div class="ytv-compare-board" id="ytv-compare-board" hidden>
                <div class="ytv-compare-side">
                    <span id="ytv-compare-a-name">{{ __('Current video') }}</span>
                    <strong id="ytv-compare-a-count">0</strong>
                </div>
                <div class="ytv-compare-side">
                    <span id="ytv-compare-b-name">{{ __('Second video') }}</span>
                    <strong id="ytv-compare-b-count">0</strong>
                </div>
            </div>
            <div class="ytv-compare-delta" id="ytv-compare-delta"></div>
        </section>

        <section class="ytv-tool-panel" id="ytv-embed-panel" hidden>
            <div class="ytv-tool-head">
                <strong>{{ __('Embed live view count') }}</strong>
                <span>{{ __('Website of OBS Browser Source') }}</span>
            </div>
            <div class="ytv-embed-stack">
                <div class="ytv-embed-group">
                    <span class="ytv-embed-label">{{ __('Website embed') }}</span>
                    <div class="ytv-embed-row">
                        <textarea class="ytv-embed-code" id="ytv-embed-code" readonly></textarea>
                        <button class="ytv-action" id="ytv-embed-copy" type="button">{{ __('Copy') }}</button>
                    </div>
                </div>
                <div class="ytv-embed-group">
                    <span class="ytv-embed-label">{{ __('OBS / Browser Source URL') }}</span>
                    <div class="ytv-embed-row">
                        <input class="ytv-embed-code" id="ytv-embed-url" type="text" readonly>
                        <button class="ytv-action" id="ytv-embed-url-copy" type="button">{{ __('Copy') }}</button>
                    </div>
                </div>
            </div>
        </section>

        <div class="ytv-extras" id="ytv-extras" hidden>
            <div class="ytv-card ytv-extra">
                <div class="ytv-extra-head">
                    <strong>{{ __('View history') }}</strong>
                    <span>{{ __('Live samples from this session') }}</span>
                </div>
                <svg class="ytv-chart" viewBox="0 0 640 190" preserveAspectRatio="none">
                    <line class="ytv-chart-grid" x1="0" y1="48" x2="640" y2="48"></line>
                    <line class="ytv-chart-grid" x1="0" y1="95" x2="640" y2="95"></line>
                    <line class="ytv-chart-grid" x1="0" y1="142" x2="640" y2="142"></line>
                    <polyline class="ytv-chart-line" id="ytv-chart-line" points=""></polyline>
                </svg>
                <div class="ytv-chart-empty" id="ytv-chart-empty">{{ __('Wachten op live samples…') }}</div>
            </div>

            <div class="ytv-card ytv-extra" id="ytv-advanced-card" hidden>
                <div class="ytv-extra-head">
                    <strong>{{ __('Advanced Metrics') }}</strong>
                    <span>{{ __('Current session') }}</span>
                </div>
                <div class="ytv-advanced">
                    <div class="ytv-advanced-row"><span>{{ __('Gained') }}</span><strong id="ytv-gained">0</strong></div>
                    <div class="ytv-advanced-row"><span>{{ __('Per minute') }}</span><strong id="ytv-per-minute">0</strong></div>
                    <div class="ytv-advanced-row"><span>{{ __('Samples') }}</span><strong id="ytv-samples">0</strong></div>
                </div>
            </div>
        </div>

        <div class="ytv-card ytv-about" id="ytv-about" hidden>
            <h3 id="ytv-about-title">{{ __('About this video') }}</h3>
            <p id="ytv-about-copy"></p>
        </div>

        <div class="ytv-empty" id="ytv-empty">
            {{ __('Zoek een YouTube-video en kies het juiste resultaat. Daarna blijven de cijfers automatisch verversen.') }}
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="/vendor/odometer/odometer.min.js?v=20261007-2"></script>
<script>
(function () {
    'use strict';

    var lookupUrl = @json(route('youtube-views.lookup'));
    var statsBase = @json(url('/api/tools/youtube-views'));
    var showBase = @json(url('/tools/youtube-views'));
    var embedBase = @json(url('/embed/youtube-views'));
    var initialVideoId = @json($initialVideoId ?? null);

    var form = document.getElementById('ytv-search');
    var input = document.getElementById('ytv-query');
    var submit = document.getElementById('ytv-submit');
    var message = document.getElementById('ytv-message');
    var results = document.getElementById('ytv-results');
    var panel = document.getElementById('ytv-panel');
    var actions = document.getElementById('ytv-actions');
    var extras = document.getElementById('ytv-extras');
    var empty = document.getElementById('ytv-empty');
    var status = document.getElementById('ytv-status');
    var thumb = document.getElementById('ytv-thumb');
    var title = document.getElementById('ytv-title');
    var channel = document.getElementById('ytv-channel');
    var videoIdEl = document.getElementById('ytv-video-id');
    var link = document.getElementById('ytv-link');
    var about = document.getElementById('ytv-about');
    var aboutTitle = document.getElementById('ytv-about-title');
    var aboutCopy = document.getElementById('ytv-about-copy');
    var chartLine = document.getElementById('ytv-chart-line');
    var chartEmpty = document.getElementById('ytv-chart-empty');
    var gainedEl = document.getElementById('ytv-gained');
    var perMinuteEl = document.getElementById('ytv-per-minute');
    var samplesEl = document.getElementById('ytv-samples');
    var advancedCard = document.getElementById('ytv-advanced-card');
    var comparePanel = document.getElementById('ytv-compare-panel');
    var embedPanel = document.getElementById('ytv-embed-panel');
    var compareQuery = document.getElementById('ytv-compare-query');
    var compareResults = document.getElementById('ytv-compare-results');
    var compareBoard = document.getElementById('ytv-compare-board');
    var compareAName = document.getElementById('ytv-compare-a-name');
    var compareACount = document.getElementById('ytv-compare-a-count');
    var compareBName = document.getElementById('ytv-compare-b-name');
    var compareBCount = document.getElementById('ytv-compare-b-count');
    var compareDelta = document.getElementById('ytv-compare-delta');
    var embedCode = document.getElementById('ytv-embed-code');
    var embedUrl = document.getElementById('ytv-embed-url');

    var currentVideoId = null;
    var currentStats = null;
    var compareVideoId = null;
    var compareStats = null;
    var timer = null;
    var compareTimer = null;
    var history = [];
    var odometers = {};

    function format(value) {
        var number = Number(value);
        return Number.isFinite(number)
            ? Math.max(0, Math.trunc(number)).toLocaleString('en-US')
            : '0';
    }

    function setNumber(key, elementId, value) {
        var element = document.getElementById(elementId);
        var number = Number(value);

        if (!element || !Number.isFinite(number)) return;

        element.classList.remove('ytv-loading');

        if (typeof window.Odometer === 'function') {
            if (!odometers[key]) {
                element.textContent = '0';
                odometers[key] = new window.Odometer({
                    el: element,
                    value: 0,
                    format: '(,ddd)',
                    duration: 900
                });
            }

            window.requestAnimationFrame(function () {
                odometers[key].update(Math.max(0, Math.trunc(number)));
            });
        } else {
            element.textContent = format(number);
        }
    }

    function setMessage(text) {
        message.textContent = text || '';
    }

    function renderSearchResults(videos, target, onPick) {
        target.innerHTML = '';

        if (!Array.isArray(videos) || !videos.length) {
            var none = document.createElement('div');
            none.className = 'ytv-message';
            none.textContent = 'Geen video’s gevonden.';
            target.appendChild(none);
            return;
        }

        videos.forEach(function (video) {
            var button = document.createElement('button');
            var image = document.createElement('img');
            var copy = document.createElement('span');
            var strong = document.createElement('strong');
            var small = document.createElement('small');
            var arrow = document.createElement('span');

            button.type = 'button';
            button.className = target === results
                ? 'ytv-result'
                : 'ytv-compare-result';

            image.src = video.thumbnail || ('https://i.ytimg.com/vi/' + video.id + '/hqdefault.jpg');
            image.alt = '';
            image.referrerPolicy = 'no-referrer';
            image.className = target === results ? 'ytv-result-thumb' : '';

            strong.textContent = video.title || 'YouTube-video';
            small.textContent = video.channel || video.id;
            copy.className = 'ytv-result-copy';
            copy.appendChild(strong);
            copy.appendChild(small);

            button.appendChild(image);
            button.appendChild(copy);

            if (target === results) {
                arrow.className = 'ytv-result-arrow';
                arrow.textContent = '→';
                button.appendChild(arrow);
            }

            button.addEventListener('click', function () {
                onPick(video);
            });

            target.appendChild(button);
        });
    }

    function searchVideos(query, target, onPick) {
        var q = String(query || '').trim();

        if (q.length < 2) {
            return Promise.resolve([]);
        }

        return fetch(lookupUrl + '?query=' + encodeURIComponent(q), {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            cache: 'no-store',
            credentials: 'same-origin'
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Search failed');
                return response.json();
            })
            .then(function (data) {
                var videos = data && data.success ? (data.videos || []) : [];
                renderSearchResults(videos, target, onPick);
                return videos;
            });
    }

    function updateHistory(value) {
        var numeric = Number(value);
        if (!Number.isFinite(numeric)) return;

        history.push({ t: Date.now(), v: numeric });
        if (history.length > 60) history.shift();

        samplesEl.textContent = String(history.length);

        if (history.length < 2) {
            chartLine.setAttribute('points', '');
            chartEmpty.hidden = false;
            gainedEl.textContent = '0';
            perMinuteEl.textContent = '0';
            return;
        }

        chartEmpty.hidden = true;

        var min = Math.min.apply(null, history.map(function (x) { return x.v; }));
        var max = Math.max.apply(null, history.map(function (x) { return x.v; }));
        var span = Math.max(1, max - min);

        var points = history.map(function (point, index) {
            var x = history.length === 1 ? 0 : (index / (history.length - 1)) * 640;
            var y = 174 - ((point.v - min) / span) * 150;
            return x.toFixed(1) + ',' + y.toFixed(1);
        }).join(' ');

        chartLine.setAttribute('points', points);

        var first = history[0];
        var last = history[history.length - 1];
        var gained = last.v - first.v;
        var minutes = Math.max((last.t - first.t) / 60000, 1 / 60);
        var perMinute = gained / minutes;

        gainedEl.textContent = format(gained);
        perMinuteEl.textContent = format(Math.round(perMinute));
    }

    function updateEmbed() {
        if (!currentVideoId) return;

        var url = embedBase + '/' + encodeURIComponent(currentVideoId);
        embedUrl.value = url;
        embedCode.value = '<iframe src="' + url + '" width="500" height="180" frameborder="0"></iframe>';
    }

    function applyStats(data) {
        if (!data || data.success !== true) {
            throw new Error('Invalid stats');
        }

        currentStats = data;

        setNumber('views', 'ytv-views', data.views);
        setNumber('likes', 'ytv-likes', data.likes);
        setNumber('dislikes', 'ytv-dislikes', data.dislikes);
        setNumber('comments', 'ytv-comments', data.comments);

        title.textContent = data.title || title.textContent || 'YouTube video';
        channel.textContent = data.channel || '';
        videoIdEl.textContent = data.id || currentVideoId || '';
        link.href = data.url || ('https://www.youtube.com/watch?v=' + currentVideoId);

        if (data.thumbnail) {
            thumb.src = data.thumbnail;
        }

        if (data.description) {
            about.hidden = false;
            aboutTitle.textContent = 'About ' + (data.title || 'this video');
            aboutCopy.textContent = data.description;
        } else {
            about.hidden = true;
        }

        compareAName.textContent = data.title || currentVideoId || 'Current video';
        compareACount.textContent = format(data.views);
        status.textContent = 'Live';

        updateHistory(data.views);
        updateEmbed();

        if (compareStats) {
            updateCompareBoard();
        }
    }

    function loadStats() {
        if (!currentVideoId) return Promise.resolve();

        status.textContent = 'Refreshing…';

        return fetch(
            statsBase + '/' + encodeURIComponent(currentVideoId)
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
        )
            .then(function (response) {
                if (!response.ok) throw new Error('Stats failed');
                return response.json();
            })
            .then(applyStats)
            .catch(function () {
                status.textContent = 'Retrying…';
            });
    }

    function openVideo(video) {
        currentVideoId = video.id;
        currentStats = null;
        compareVideoId = null;
        compareStats = null;
        history = [];

        results.innerHTML = '';
        setMessage('');

        panel.hidden = false;
        actions.hidden = false;
        extras.hidden = false;
        empty.hidden = true;
        comparePanel.hidden = true;
        embedPanel.hidden = true;
        advancedCard.hidden = true;

        title.textContent = video.title || 'YouTube video';
        channel.textContent = video.channel || '';
        videoIdEl.textContent = video.id;
        thumb.src = video.thumbnail || ('https://i.ytimg.com/vi/' + video.id + '/hqdefault.jpg');
        link.href = video.url || ('https://www.youtube.com/watch?v=' + video.id);

        ['views','likes','dislikes','comments'].forEach(function (key) {
            var el = document.getElementById('ytv-' + key);
            if (el) {
                el.textContent = '0';
                el.classList.add('ytv-loading');
            }
        });

        try {
            window.history.replaceState(
                {},
                '',
                showBase + '/' + encodeURIComponent(video.id)
            );
        } catch (e) {}

        if (timer) window.clearInterval(timer);
        loadStats();
        timer = window.setInterval(loadStats, 3000);
    }

    function updateCompareBoard() {
        if (!currentStats || !compareStats) return;

        compareBoard.hidden = false;
        compareAName.textContent = currentStats.title || currentVideoId;
        compareBName.textContent = compareStats.title || compareVideoId;
        compareACount.textContent = format(currentStats.views);
        compareBCount.textContent = format(compareStats.views);

        var delta = Number(currentStats.views) - Number(compareStats.views);
        var leader = delta >= 0
            ? (currentStats.title || 'Current video')
            : (compareStats.title || 'Second video');

        compareDelta.textContent =
            leader + ' leads by ' + format(Math.abs(delta)) + ' views';
    }

    function loadCompareStats() {
        if (!compareVideoId) return;

        fetch(
            statsBase + '/' + encodeURIComponent(compareVideoId)
                + '?_=' + encodeURIComponent(String(Date.now())),
            {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
                cache: 'no-store',
                credentials: 'same-origin'
            }
        )
            .then(function (response) {
                if (!response.ok) throw new Error('Compare stats failed');
                return response.json();
            })
            .then(function (data) {
                if (!data || data.success !== true) return;
                compareStats = data;
                updateCompareBoard();
            })
            .catch(function () {});
    }

    function copyValue(element, button) {
        var value = element.value || '';

        if (!value) return;

        navigator.clipboard.writeText(value).then(function () {
            var old = button.textContent;
            button.textContent = 'Copied';
            window.setTimeout(function () { button.textContent = old; }, 1200);
        }).catch(function () {});
    }

    function directVideoId(query) {
        var value = String(query || '').trim();
        var match;

        if (/^[A-Za-z0-9_-]{11}$/.test(value)) {
            return value;
        }

        match = value.match(
            /(?:youtu\.be\/|youtube\.com\/(?:watch\?[^#]*v=|shorts\/|embed\/|live\/))([A-Za-z0-9_-]{11})/i
        );

        return match ? match[1] : null;
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        var query = input.value.trim();
        if (query.length < 2) return;

        submit.disabled = true;
        setMessage('Video’s zoeken…');
        results.innerHTML = '';

        searchVideos(query, results, openVideo)
            .then(function (videos) {
                var exactId = directVideoId(query);

                if (
                    exactId
                    && videos.length
                    && videos[0]
                    && videos[0].id === exactId
                ) {
                    openVideo(videos[0]);
                    return;
                }

                setMessage(
                    videos.length
                        ? 'Kies de juiste video.'
                        : 'Geen video’s gevonden.'
                );
            })
            .catch(function () {
                setMessage('Video’s konden niet worden geladen.');
            })
            .finally(function () {
                submit.disabled = false;
            });
    });

    document.getElementById('ytv-change').addEventListener('click', function () {
        input.focus();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    document.getElementById('ytv-advanced-toggle').addEventListener('click', function () {
        advancedCard.hidden = !advancedCard.hidden;
    });

    document.getElementById('ytv-compare-toggle').addEventListener('click', function () {
        comparePanel.hidden = !comparePanel.hidden;
        if (!comparePanel.hidden) compareQuery.focus();
    });

    document.getElementById('ytv-embed-toggle').addEventListener('click', function () {
        embedPanel.hidden = !embedPanel.hidden;
        if (!embedPanel.hidden) updateEmbed();
    });

    document.getElementById('ytv-compare-search').addEventListener('click', function () {
        var query = compareQuery.value.trim();
        if (query.length < 2) return;

        compareResults.innerHTML = '';

        searchVideos(query, compareResults, function (video) {
            compareVideoId = video.id;
            compareStats = null;
            compareResults.innerHTML = '';

            if (compareTimer) window.clearInterval(compareTimer);
            loadCompareStats();
            compareTimer = window.setInterval(loadCompareStats, 3000);
        }).catch(function () {});
    });

    document.getElementById('ytv-embed-copy').addEventListener('click', function () {
        copyValue(embedCode, this);
    });

    document.getElementById('ytv-embed-url-copy').addEventListener('click', function () {
        copyValue(embedUrl, this);
    });

    if (initialVideoId && /^[A-Za-z0-9_-]{11}$/.test(initialVideoId)) {
        openVideo({
            id: initialVideoId,
            title: 'YouTube video',
            thumbnail: 'https://i.ytimg.com/vi/' + initialVideoId + '/hqdefault.jpg',
            url: 'https://www.youtube.com/watch?v=' + initialVideoId
        });
    }
}());
</script>
@endpush
