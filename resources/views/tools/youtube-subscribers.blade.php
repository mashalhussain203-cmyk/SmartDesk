@extends('layouts.site-layout')

@section('title', 'YouTube Live Subscribers | Mashal Studio')
@section('meta_description', 'Volg publieke YouTube subscriber-, view- en videostatistieken live met Mashal Studio.')

@push('styles')
<link rel="stylesheet" href="/vendor/odometer/odometer-theme-minimal.css?v=20261007-2">
<style>
    .yts-page {
        --yts-panel: rgba(13,16,22,.88);
        --yts-line: rgba(255,255,255,.075);
        --yts-text: #f4f6f8;
        --yts-muted: #7a8492;
        --yts-red: #ff5363;
        --yts-green: #6ee7a8;

        min-height: calc(100vh - 72px);
        padding: 46px 0 96px;
        color: var(--yts-text);
        background:
            radial-gradient(circle at 50% -170px, rgba(255,73,87,.13), transparent 470px),
            radial-gradient(circle at 90% 20%, rgba(123,112,255,.055), transparent 390px),
            linear-gradient(180deg, #06070a 0%, #050608 100%);
    }

    .yts-shell {
        width: min(calc(100% - 32px), 1180px);
        margin: 0 auto;
    }

    .yts-back {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #7e8795;
        text-decoration: none;
        font-size: 10px;
        font-weight: 760;
    }

    .yts-hero {
        max-width: 780px;
        margin: 26px auto 32px;
        text-align: center;
    }

    .yts-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 11px;
        border: 1px solid rgba(255,83,99,.2);
        border-radius: 999px;
        color: #ff9ba4;
        background: rgba(255,83,99,.045);
        font-size: 10px;
        font-weight: 850;
        letter-spacing: .13em;
        text-transform: uppercase;
    }

    .yts-eyebrow-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--yts-green);
        box-shadow: 0 0 12px rgba(110,231,168,.7);
    }

    .yts-title {
        margin: 18px 0 0;
        color: #fff;
        font-size: clamp(42px, 6vw, 72px);
        line-height: .98;
        font-weight: 720;
        letter-spacing: -.055em;
    }

    .yts-title span {
        color: #737b88;
    }

    .yts-copy {
        max-width: 620px;
        margin: 17px auto 0;
        color: var(--yts-muted);
        font-size: 13px;
        line-height: 1.75;
    }

    .yts-search-wrap {
        max-width: 900px;
        margin: 0 auto 16px;
    }

    .yts-search {
        padding: 8px;
        display: grid;
        grid-template-columns: minmax(0,1fr) auto;
        gap: 8px;
        border: 1px solid var(--yts-line);
        border-radius: 18px;
        background: rgba(10,12,17,.91);
        box-shadow:
            0 26px 70px rgba(0,0,0,.28),
            inset 0 1px 0 rgba(255,255,255,.025);
    }

    .yts-input-shell {
        min-width: 0;
        padding: 0 14px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .yts-input-icon {
        width: 28px;
        height: 28px;
        flex: 0 0 28px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 8px;
        color: #ff6a77;
        background: rgba(255,255,255,.02);
        font-size: 11px;
    }

    .yts-input {
        min-width: 0;
        width: 100%;
        height: 54px;
        border: 0;
        outline: 0;
        color: #edf0f4;
        background: transparent;
        font-size: 13px;
    }

    .yts-input::placeholder {
        color: #525b68;
    }

    .yts-button {
        min-height: 54px;
        padding: 0 22px;
        border: 1px solid rgba(255,83,99,.42);
        border-radius: 12px;
        color: #fff;
        background: linear-gradient(135deg, #f34c5c, #d83d4c);
        font-size: 11px;
        font-weight: 850;
        cursor: pointer;
    }

    .yts-button:disabled {
        opacity: .6;
        cursor: wait;
    }

    .yts-message {
        min-height: 18px;
        max-width: 900px;
        margin: 0 auto 10px;
        color: #f29aa2;
        font-size: 10px;
        text-align: center;
    }

    .yts-results {
        max-width: 900px;
        margin: 0 auto 26px;
        display: grid;
        gap: 7px;
    }

    .yts-result {
        width: 100%;
        padding: 10px 12px;
        display: flex;
        align-items: center;
        gap: 12px;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 13px;
        color: inherit;
        background: rgba(14,17,23,.93);
        text-align: left;
        cursor: pointer;
        transition: transform .16s ease, border-color .16s ease;
    }

    .yts-result:hover {
        transform: translateY(-1px);
        border-color: rgba(255,83,99,.25);
    }

    .yts-result-avatar {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        border-radius: 50%;
        object-fit: cover;
        background: #1b2028;
    }

    .yts-result-copy {
        min-width: 0;
        flex: 1;
    }

    .yts-result-title {
        overflow: hidden;
        color: #edf0f4;
        font-size: 11px;
        font-weight: 820;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .yts-result-id {
        margin-top: 3px;
        overflow: hidden;
        color: #68717e;
        font-size: 8px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .yts-result-arrow {
        color: #ff7782;
        font-size: 17px;
    }

    .yts-panel {
        max-width: 1040px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 280px minmax(0,1fr);
        gap: 16px;
    }

    .yts-card {
        border: 1px solid var(--yts-line);
        border-radius: 24px;
        background:
            radial-gradient(circle at 100% 0%, rgba(255,83,99,.07), transparent 38%),
            linear-gradient(180deg, rgba(15,18,24,.95), rgba(9,11,15,.97));
        box-shadow: 0 24px 64px rgba(0,0,0,.22);
    }

    .yts-profile {
        min-height: 360px;
        padding: 24px 18px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .yts-avatar {
        width: 112px;
        height: 112px;
        border: 1px solid rgba(255,255,255,.09);
        border-radius: 50%;
        object-fit: cover;
        background: #181d25;
        box-shadow: 0 18px 45px rgba(0,0,0,.28);
    }

    .yts-channel-name {
        width: 100%;
        margin-top: 18px;
        overflow: hidden;
        color: #fff;
        font-size: 20px;
        font-weight: 820;
        letter-spacing: -.03em;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .yts-channel-id {
        width: 100%;
        margin-top: 5px;
        overflow: hidden;
        color: #697281;
        font-size: 8px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .yts-channel-link {
        margin-top: 18px;
        color: #ff8992;
        font-size: 9px;
        font-weight: 760;
        text-decoration: none;
    }

    .yts-dashboard {
        padding: 22px;
    }

    .yts-status {
        min-height: 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        color: #697281;
        font-size: 9px;
    }

    .yts-status strong {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #7adea4;
        font-weight: 850;
    }

    .yts-status strong::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #6ee7a8;
        box-shadow: 0 0 10px rgba(110,231,168,.65);
    }

    .yts-main {
        padding: 28px 0 24px;
        text-align: center;
        border-bottom: 1px solid var(--yts-line);
    }

    .yts-main-label {
        color: #78818e;
        font-size: 10px;
        font-weight: 850;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .yts-main-value {
        margin-top: 7px;
        color: #fff;
        font-size: clamp(52px, 8vw, 82px);
        line-height: 1;
        font-weight: 780;
        letter-spacing: -.055em;
    }

    .yts-stats {
        padding-top: 18px;
        display: grid;
        grid-template-columns: repeat(3, minmax(0,1fr));
        gap: 10px;
    }

    .yts-stat {
        min-height: 126px;
        padding: 15px;
        border: 1px solid rgba(255,255,255,.06);
        border-radius: 16px;
        background: rgba(255,255,255,.016);
    }

    .yts-stat-label {
        color: #737c89;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .yts-stat-value {
        margin-top: 26px;
        color: #f4f6f8;
        font-size: clamp(25px, 3vw, 38px);
        line-height: 1;
        font-weight: 760;
        letter-spacing: -.04em;
    }

    .yts-loading {
        opacity: .56;
    }

    .yts-empty {
        max-width: 900px;
        margin: 12px auto 0;
        padding: 34px 20px;
        border: 1px dashed rgba(255,255,255,.08);
        border-radius: 20px;
        color: #697281;
        text-align: center;
        font-size: 10px;
        line-height: 1.7;
    }

    .odometer {
        font: inherit !important;
        line-height: inherit !important;
    }

    .yts-banner {
        width: 100%;
        height: 92px;
        margin-bottom: -42px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 16px;
        background: rgba(255,255,255,.025);
    }

    .yts-banner img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .yts-profile .yts-avatar {
        position: relative;
        z-index: 1;
        border: 4px solid #10131a;
    }

    .yts-extras {
        max-width: 1040px;
        margin: 16px auto 0;
        display: grid;
        grid-template-columns: minmax(0,1.6fr) minmax(260px,.8fr);
        gap: 16px;
    }

    .yts-extra-card {
        padding: 20px;
    }

    .yts-extra-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
    }

    .yts-extra-head strong {
        color: #e7eaf0;
        font-size: 11px;
        font-weight: 850;
    }

    .yts-extra-head span {
        color: #68717e;
        font-size: 8px;
    }

    .yts-chart {
        width: 100%;
        height: 190px;
        display: block;
        overflow: visible;
    }

    .yts-chart-grid {
        stroke: rgba(255,255,255,.06);
        stroke-width: 1;
    }

    .yts-chart-line {
        fill: none;
        stroke: #ff6573;
        stroke-width: 2.25;
        stroke-linecap: round;
        stroke-linejoin: round;
        vector-effect: non-scaling-stroke;
    }

    .yts-chart-empty {
        color: #596371;
        font-size: 9px;
        text-align: center;
        margin-top: -105px;
        pointer-events: none;
    }

    .yts-advanced {
        display: grid;
        gap: 9px;
    }

    .yts-advanced-row {
        min-height: 58px;
        padding: 11px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border: 1px solid rgba(255,255,255,.055);
        border-radius: 13px;
        background: rgba(255,255,255,.014);
    }

    .yts-advanced-row span {
        color: #6e7784;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .yts-advanced-row strong {
        color: #f1f3f6;
        font-size: 14px;
        font-weight: 820;
    }

    .yts-about {
        max-width: 1040px;
        margin: 16px auto 0;
        padding: 20px;
    }

    .yts-about h3 {
        margin: 0;
        color: #eef1f5;
        font-size: 14px;
        font-weight: 850;
    }

    .yts-about p {
        margin: 10px 0 0;
        color: #737d8a;
        font-size: 10px;
        line-height: 1.7;
        white-space: pre-line;
    }

    .yts-counter-actions {
        max-width: 1040px;
        margin: 14px auto 0;
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    .yts-action-button {
        min-height: 40px;
        padding: 0 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 11px;
        color: #a4abb5;
        background: rgba(255,255,255,.025);
        font-size: 9px;
        font-weight: 820;
        cursor: pointer;
    }

    .yts-action-button:hover {
        border-color: rgba(255,83,99,.24);
        color: #f0f2f5;
    }

    .yts-tool-panel {
        max-width: 1040px;
        margin: 14px auto 0;
        padding: 18px;
        border: 1px solid var(--yts-line);
        border-radius: 18px;
        background: rgba(12,15,20,.92);
    }

    .yts-tool-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 13px;
    }

    .yts-tool-panel-head strong {
        color: #eef1f5;
        font-size: 11px;
        font-weight: 850;
    }

    .yts-tool-panel-head span {
        color: #68717e;
        font-size: 8px;
    }

    .yts-compare-search {
        display: grid;
        grid-template-columns: minmax(0,1fr) auto;
        gap: 8px;
    }

    .yts-compare-input,
    .yts-embed-code {
        width: 100%;
        min-height: 44px;
        padding: 0 12px;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 11px;
        outline: 0;
        color: #e9edf2;
        background: rgba(255,255,255,.02);
        font-size: 10px;
    }

    .yts-compare-results {
        margin-top: 9px;
        display: grid;
        gap: 6px;
    }

    .yts-compare-result {
        width: 100%;
        min-height: 50px;
        padding: 7px 10px;
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid rgba(255,255,255,.06);
        border-radius: 11px;
        color: #dfe3e8;
        background: rgba(255,255,255,.015);
        text-align: left;
        cursor: pointer;
    }

    .yts-compare-result img {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        object-fit: cover;
        background: #171c24;
    }

    .yts-compare-result strong {
        display: block;
        font-size: 10px;
    }

    .yts-compare-result small {
        display: block;
        margin-top: 2px;
        color: #697281;
        font-size: 7px;
    }

    .yts-compare-board {
        margin-top: 14px;
        display: grid;
        grid-template-columns: repeat(2, minmax(0,1fr));
        gap: 10px;
    }

    .yts-compare-side {
        min-width: 0;
        padding: 14px;
        border: 1px solid rgba(255,255,255,.06);
        border-radius: 14px;
        background: rgba(255,255,255,.015);
    }

    .yts-compare-side span {
        display: block;
        overflow: hidden;
        color: #7a8491;
        font-size: 8px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .yts-compare-side strong {
        display: block;
        margin-top: 8px;
        color: #fff;
        font-size: clamp(24px, 4vw, 38px);
        line-height: 1;
        letter-spacing: -.04em;
    }

    .yts-compare-delta {
        margin-top: 10px;
        color: #7adea4;
        font-size: 9px;
        font-weight: 800;
        text-align: center;
    }

    .yts-embed-row {
        display: grid;
        grid-template-columns: minmax(0,1fr) auto;
        gap: 8px;
    }

    .yts-embed-code {
        padding-top: 10px;
        padding-bottom: 10px;
        resize: vertical;
        min-height: 72px;
        line-height: 1.45;
    }

    @media (max-width: 780px) {
        .yts-search {
            grid-template-columns: 1fr;
        }

        .yts-button {
            width: 100%;
        }

        .yts-panel {
            grid-template-columns: 1fr;
        }

        .yts-compare-search,
        .yts-embed-row,
        .yts-compare-board {
            grid-template-columns: 1fr;
        }

        .yts-extras {
            grid-template-columns: 1fr;
        }

        .yts-profile {
            min-height: 280px;
        }
    }

    @media (max-width: 620px) {
        .yts-page {
            padding-top: 34px;
        }

        .yts-stats {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<section class="yts-page">
    <div class="yts-shell">
        <a class="yts-back" href="{{ route('live-counts.index') }}">← Live Counts</a>

        <header class="yts-hero">
            <div class="yts-eyebrow">
                <span class="yts-eyebrow-dot"></span>
                Mashal Studio · YouTube Live
            </div>

            <h1 class="yts-title">Live <span>Subscribers.</span></h1>

            <p class="yts-copy">
                Zoek een YouTube-kanaal en volg subscribers, totale views,
                video's en de volgende goal automatisch.
            </p>
        </header>

        <div class="yts-search-wrap">
            <form class="yts-search" id="yts-search">
                <div class="yts-input-shell">
                    <span class="yts-input-icon" aria-hidden="true">▶</span>
                    <input
                        class="yts-input"
                        id="yts-query"
                        type="text"
                        maxlength="255"
                        placeholder="Kanaalnaam, @handle of YouTube-kanaal URL…"
                        autocomplete="off"
                        required
                        aria-label="YouTube kanaal zoeken"
                    >
                </div>

                <button class="yts-button" id="yts-submit" type="submit">
                    Zoek kanaal →
                </button>
            </form>
        </div>

        <div class="yts-message" id="yts-message" role="status" aria-live="polite"></div>
        <div class="yts-results" id="yts-results" aria-live="polite"></div>

        <div class="yts-panel" id="yts-panel" hidden>
            <aside class="yts-card yts-profile">
                <div class="yts-banner" id="yts-banner-wrap" hidden>
                    <img id="yts-banner" src="" alt="" referrerpolicy="no-referrer">
                </div>

                <img
                    class="yts-avatar"
                    id="yts-avatar"
                    src="/icons/follower-profile.svg?v=1"
                    alt=""
                    referrerpolicy="no-referrer"
                >
                <div class="yts-channel-name" id="yts-name">YouTube channel</div>
                <div class="yts-channel-id" id="yts-channel-id"></div>
                <a
                    class="yts-channel-link"
                    id="yts-link"
                    href="#"
                    target="_blank"
                    rel="noopener noreferrer"
                >Open op YouTube ↗</a>
            </aside>

            <div class="yts-card yts-dashboard">
                <div class="yts-status">
                    <span>Public channel statistics</span>
                    <strong id="yts-status">Live</strong>
                </div>

                <div class="yts-main">
                    <div class="yts-main-label">Subscribers</div>
                    <div class="yts-main-value yts-loading" id="yts-subscribers">0</div>
                </div>

                <div class="yts-stats">
                    <div class="yts-stat">
                        <div class="yts-stat-label">Channel Views</div>
                        <div class="yts-stat-value yts-loading" id="yts-views">0</div>
                    </div>

                    <div class="yts-stat">
                        <div class="yts-stat-label">Videos</div>
                        <div class="yts-stat-value yts-loading" id="yts-videos">0</div>
                    </div>

                    <div class="yts-stat">
                        <div class="yts-stat-label">Goal</div>
                        <div class="yts-stat-value yts-loading" id="yts-goal">0</div>
                    </div>
                </div>

                <div class="yts-status" style="margin-top:16px">
                    <span>Remaining to goal</span>
                    <strong id="yts-remaining">0</strong>
                </div>
            </div>
        </div>

        <div class="yts-counter-actions" id="yts-counter-actions" hidden>
            <button class="yts-action-button" id="yts-change-user" type="button">↺ Change User</button>
            <button class="yts-action-button" id="yts-compare-toggle" type="button">⇄ Compare</button>
            <button class="yts-action-button" id="yts-embed-toggle" type="button">&lt;/&gt; Embed</button>
        </div>

        <section class="yts-tool-panel" id="yts-compare-panel" hidden>
            <div class="yts-tool-panel-head">
                <strong>Compare channels</strong>
                <span>Live subscriber counts</span>
            </div>

            <div class="yts-compare-search">
                <input
                    class="yts-compare-input"
                    id="yts-compare-query"
                    type="text"
                    maxlength="255"
                    placeholder="Zoek tweede kanaal…"
                    autocomplete="off"
                >
                <button class="yts-action-button" id="yts-compare-search" type="button">
                    Zoek →
                </button>
            </div>

            <div class="yts-compare-results" id="yts-compare-results"></div>

            <div class="yts-compare-board" id="yts-compare-board" hidden>
                <div class="yts-compare-side">
                    <span id="yts-compare-a-name">Current channel</span>
                    <strong id="yts-compare-a-count">0</strong>
                </div>
                <div class="yts-compare-side">
                    <span id="yts-compare-b-name">Second channel</span>
                    <strong id="yts-compare-b-count">0</strong>
                </div>
            </div>
            <div class="yts-compare-delta" id="yts-compare-delta"></div>
        </section>

        <section class="yts-tool-panel" id="yts-embed-panel" hidden>
            <div class="yts-tool-panel-head">
                <strong>Embed live subscriber count</strong>
                <span>Gebruik je eigen Mashal counter</span>
            </div>

            <div class="yts-embed-row">
                <textarea class="yts-embed-code" id="yts-embed-code" readonly></textarea>
                <button class="yts-action-button" id="yts-embed-copy" type="button">
                    Copy
                </button>
            </div>
        </section>

        <div class="yts-extras" id="yts-extras" hidden>
            <div class="yts-card yts-extra-card">
                <div class="yts-extra-head">
                    <strong>Subscriber history</strong>
                    <span>Live samples from this session</span>
                </div>
                <svg
                    class="yts-chart"
                    id="yts-chart"
                    viewBox="0 0 640 190"
                    preserveAspectRatio="none"
                    aria-label="Subscriber history chart"
                >
                    <line class="yts-chart-grid" x1="0" y1="48" x2="640" y2="48"></line>
                    <line class="yts-chart-grid" x1="0" y1="95" x2="640" y2="95"></line>
                    <line class="yts-chart-grid" x1="0" y1="142" x2="640" y2="142"></line>
                    <polyline class="yts-chart-line" id="yts-chart-line" points=""></polyline>
                </svg>
                <div class="yts-chart-empty" id="yts-chart-empty">
                    Wachten op live samples…
                </div>
            </div>

            <div class="yts-card yts-extra-card">
                <div class="yts-extra-head">
                    <strong>Advanced Metrics</strong>
                    <span>Current session</span>
                </div>
                <div class="yts-advanced">
                    <div class="yts-advanced-row">
                        <span>Gained</span>
                        <strong id="yts-gained">0</strong>
                    </div>
                    <div class="yts-advanced-row">
                        <span>Per minute</span>
                        <strong id="yts-per-minute">0</strong>
                    </div>
                    <div class="yts-advanced-row">
                        <span>Samples</span>
                        <strong id="yts-samples">0</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="yts-card yts-about" id="yts-about" hidden>
            <h3 id="yts-about-title">About this channel</h3>
            <p id="yts-about-copy"></p>
        </div>

        <div class="yts-empty" id="yts-empty">
            Zoek een kanaal en kies het juiste resultaat. Daarna blijven de
            publieke cijfers automatisch verversen.
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="/vendor/odometer/odometer.min.js?v=20261007-2"></script>
<script>
(function () {
    'use strict';

    var lookupUrl = @json(route('youtube-subscribers.lookup'));
    var statsBase = @json(url('/api/tools/youtube-subscribers'));
    var form = document.getElementById('yts-search');
    var input = document.getElementById('yts-query');
    var submit = document.getElementById('yts-submit');
    var message = document.getElementById('yts-message');
    var results = document.getElementById('yts-results');
    var panel = document.getElementById('yts-panel');
    var extras = document.getElementById('yts-extras');
    var empty = document.getElementById('yts-empty');
    var status = document.getElementById('yts-status');
    var avatar = document.getElementById('yts-avatar');
    var banner = document.getElementById('yts-banner');
    var bannerWrap = document.getElementById('yts-banner-wrap');
    var name = document.getElementById('yts-name');
    var channelIdEl = document.getElementById('yts-channel-id');
    var channelLink = document.getElementById('yts-link');
    var remaining = document.getElementById('yts-remaining');
    var about = document.getElementById('yts-about');
    var aboutTitle = document.getElementById('yts-about-title');
    var aboutCopy = document.getElementById('yts-about-copy');
    var chartLine = document.getElementById('yts-chart-line');
    var chartEmpty = document.getElementById('yts-chart-empty');
    var gainedEl = document.getElementById('yts-gained');
    var perMinuteEl = document.getElementById('yts-per-minute');
    var samplesEl = document.getElementById('yts-samples');
    var counterActions = document.getElementById('yts-counter-actions');
    var changeUserButton = document.getElementById('yts-change-user');
    var compareToggle = document.getElementById('yts-compare-toggle');
    var embedToggle = document.getElementById('yts-embed-toggle');
    var comparePanel = document.getElementById('yts-compare-panel');
    var compareQuery = document.getElementById('yts-compare-query');
    var compareSearchButton = document.getElementById('yts-compare-search');
    var compareResults = document.getElementById('yts-compare-results');
    var compareBoard = document.getElementById('yts-compare-board');
    var compareAName = document.getElementById('yts-compare-a-name');
    var compareACount = document.getElementById('yts-compare-a-count');
    var compareBName = document.getElementById('yts-compare-b-name');
    var compareBCount = document.getElementById('yts-compare-b-count');
    var compareDelta = document.getElementById('yts-compare-delta');
    var embedPanel = document.getElementById('yts-embed-panel');
    var embedCode = document.getElementById('yts-embed-code');
    var embedCopy = document.getElementById('yts-embed-copy');

    var currentChannel = null;
    var refreshTimer = null;
    var searchTimer = null;
    var searchBusy = false;
    var queuedSearch = '';
    var lastSearch = '';
    var odometers = {};
    var historyPoints = [];
    var sessionStartedAt = null;
    var firstSubscriberValue = null;
    var compareChannel = null;
    var compareTimer = null;

    function safeImage(url) {
        try {
            var parsed = new URL(String(url || ''));
            return parsed.protocol === 'https:' ? parsed.href : '';
        } catch (error) {
            return '';
        }
    }

    function setMessage(text) {
        message.textContent = text || '';
    }

    function formatSigned(value) {
        var number = Number(value) || 0;
        var rounded = Math.trunc(number);

        return (rounded > 0 ? '+' : '') + rounded.toLocaleString('en-US');
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

    function updateNumber(key, value) {
        var element = document.getElementById('yts-' + key);
        var number = Number(value);

        if (!element || !Number.isFinite(number)) {
            return;
        }

        element.classList.remove('yts-loading');
        number = Math.max(0, Math.trunc(number));

        var odometer = ensureOdometer(key, element);

        if (odometer) {
            window.requestAnimationFrame(function () {
                window.requestAnimationFrame(function () {
                    odometer.update(number);
                });
            });
        } else {
            element.textContent = number.toLocaleString('en-US');
        }
    }

    function fetchJson(url) {
        return fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Cache-Control': 'no-cache'
            },
            cache: 'no-store',
            credentials: 'same-origin'
        }).then(function (response) {
            return response.json().catch(function () {
                return {};
            }).then(function (data) {
                if (!response.ok || data.success === false) {
                    throw new Error(data.message || 'Ophalen mislukt');
                }

                return data;
            });
        });
    }

    function resetSessionMetrics() {
        historyPoints = [];
        sessionStartedAt = Date.now();
        firstSubscriberValue = null;
        chartLine.setAttribute('points', '');
        chartEmpty.hidden = false;
        gainedEl.textContent = '0';
        perMinuteEl.textContent = '0';
        samplesEl.textContent = '0';
    }

    function drawChart() {
        if (!historyPoints.length) {
            chartLine.setAttribute('points', '');
            chartEmpty.hidden = false;
            return;
        }

        var values = historyPoints.map(function (point) {
            return point.value;
        });
        var min = Math.min.apply(Math, values);
        var max = Math.max.apply(Math, values);

        if (min === max) {
            min -= 1;
            max += 1;
        }

        var width = 640;
        var height = 190;
        var padY = 18;
        var usableHeight = height - (padY * 2);

        var points = historyPoints.map(function (point, index) {
            var x = historyPoints.length === 1
                ? width / 2
                : (index / (historyPoints.length - 1)) * width;
            var ratio = (point.value - min) / (max - min);
            var y = height - padY - (ratio * usableHeight);

            return x.toFixed(2) + ',' + y.toFixed(2);
        }).join(' ');

        chartLine.setAttribute('points', points);
        chartEmpty.hidden = historyPoints.length > 1;
    }

    function recordSubscriberSample(value) {
        var subscribers = Number(value);

        if (!Number.isFinite(subscribers)) {
            return;
        }

        subscribers = Math.max(0, Math.trunc(subscribers));

        if (firstSubscriberValue === null) {
            firstSubscriberValue = subscribers;
            sessionStartedAt = Date.now();
        }

        var now = Date.now();
        var previous = historyPoints.length
            ? historyPoints[historyPoints.length - 1]
            : null;

        if (!previous || previous.value !== subscribers || now - previous.at >= 4500) {
            historyPoints.push({ value: subscribers, at: now });
        }

        if (historyPoints.length > 80) {
            historyPoints.shift();
        }

        var gained = subscribers - firstSubscriberValue;
        var elapsedMinutes = Math.max(
            (now - sessionStartedAt) / 60000,
            1 / 60
        );
        var perMinute = gained / elapsedMinutes;

        gainedEl.textContent = formatSigned(gained);
        perMinuteEl.textContent = formatSigned(Math.round(perMinute));
        samplesEl.textContent = String(historyPoints.length);

        drawChart();
    }

    function buildEmbedCode(channelId) {
        if (!channelId) {
            return '';
        }

        var src = window.location.origin
            + '/embed/youtube-subscribers/'
            + encodeURIComponent(channelId);

        return '<iframe src="' + src
            + '" width="720" height="240" frameborder="0"'
            + ' loading="lazy" allowtransparency="true"></iframe>';
    }

    function updateCompareDelta(a, b) {
        var left = Number(a);
        var right = Number(b);

        if (!Number.isFinite(left) || !Number.isFinite(right)) {
            compareDelta.textContent = '';
            return;
        }

        var difference = Math.abs(Math.trunc(left - right));

        compareDelta.textContent = difference.toLocaleString('en-US')
            + ' subscribers verschil';
    }

    function loadCompareStats() {
        if (!currentChannel || !compareChannel || document.hidden) {
            return Promise.resolve();
        }

        return Promise.all([
            fetchJson(
                statsBase + '/' + encodeURIComponent(currentChannel)
                    + '?_=' + encodeURIComponent(String(Date.now()))
            ),
            fetchJson(
                statsBase + '/' + encodeURIComponent(compareChannel.id)
                    + '?_=' + encodeURIComponent(String(Date.now()))
            )
        ]).then(function (pair) {
            var first = pair[0];
            var second = pair[1];

            compareAName.textContent = first.title || name.textContent || currentChannel;
            compareBName.textContent = second.title
                || compareChannel.title
                || compareChannel.id;
            compareACount.textContent = Number(first.subscribers || 0)
                .toLocaleString('en-US');
            compareBCount.textContent = Number(second.subscribers || 0)
                .toLocaleString('en-US');
            updateCompareDelta(first.subscribers, second.subscribers);
            compareBoard.hidden = false;
        }).catch(function () {});
    }

    function selectCompareChannel(channel) {
        if (!channel || !channel.id || channel.id === currentChannel) {
            return;
        }

        compareChannel = channel;
        compareResults.innerHTML = '';
        compareBName.textContent = channel.title || channel.id;
        compareBoard.hidden = false;

        if (compareTimer) {
            window.clearInterval(compareTimer);
        }

        loadCompareStats();
        compareTimer = window.setInterval(loadCompareStats, 5000);
    }

    function renderCompareResults(channels) {
        compareResults.innerHTML = '';

        (Array.isArray(channels) ? channels : []).forEach(function (channel) {
            if (!channel || !channel.id || channel.id === currentChannel) {
                return;
            }

            var button = document.createElement('button');
            var image = document.createElement('img');
            var copy = document.createElement('span');
            var title = document.createElement('strong');
            var id = document.createElement('small');

            button.type = 'button';
            button.className = 'yts-compare-result';

            image.alt = '';
            image.referrerPolicy = 'no-referrer';
            image.src = safeImage(channel.avatar)
                || '/icons/follower-profile.svg?v=1';

            title.textContent = channel.title || 'YouTube channel';
            id.textContent = channel.id;

            copy.appendChild(title);
            copy.appendChild(id);
            button.appendChild(image);
            button.appendChild(copy);

            button.addEventListener('click', function () {
                selectCompareChannel(channel);
            });

            compareResults.appendChild(button);
        });
    }

    function searchCompareChannel() {
        var query = compareQuery.value.trim();

        if (query.length < 2) {
            return;
        }

        compareSearchButton.disabled = true;
        compareSearchButton.textContent = 'Zoeken…';

        fetchJson(
            lookupUrl + '?' + new URLSearchParams({ query: query }).toString()
        )
            .then(function (data) {
                renderCompareResults(data.channels || []);
            })
            .catch(function () {
                compareResults.innerHTML = '';
            })
            .finally(function () {
                compareSearchButton.disabled = false;
                compareSearchButton.textContent = 'Zoek →';
            });
    }

    function renderChannel(channel) {
        if (!channel || !channel.id) {
            return;
        }

        panel.hidden = false;
        extras.hidden = false;
        empty.hidden = true;
        currentChannel = channel.id;
        counterActions.hidden = false;
        embedCode.value = buildEmbedCode(channel.id);

        if (compareChannel && compareChannel.id === channel.id) {
            compareChannel = null;
            compareBoard.hidden = true;
            compareDelta.textContent = '';
        }

        if (channel.title) {
            name.textContent = channel.title;
        }

        channelIdEl.textContent = channel.id;
        channelLink.href = channel.url
            || ('https://www.youtube.com/channel/' + encodeURIComponent(channel.id));

        var image = safeImage(channel.avatar);
        if (image) {
            avatar.src = image;
        }

        var bannerImage = safeImage(channel.banner);
        if (bannerImage) {
            banner.src = bannerImage;
            bannerWrap.hidden = false;
        }

        if (channel.description) {
            about.hidden = false;
            aboutTitle.textContent = 'About ' + (channel.title || 'this channel');
            aboutCopy.textContent = channel.description;
        }

        if (channel.subscribers !== undefined) {
            updateNumber('subscribers', channel.subscribers);
            recordSubscriberSample(channel.subscribers);
        }
        if (channel.views !== undefined) {
            updateNumber('views', channel.views);
        }
        if (channel.videos !== undefined) {
            updateNumber('videos', channel.videos);
        }
        if (channel.goal !== undefined) {
            updateNumber('goal', channel.goal);
        }

        if (
            channel.goal !== undefined
            && channel.subscribers !== undefined
            && Number.isFinite(Number(channel.goal))
            && Number.isFinite(Number(channel.subscribers))
        ) {
            remaining.textContent = Math.max(
                0,
                Math.trunc(Number(channel.goal) - Number(channel.subscribers))
            ).toLocaleString('en-US');
        }
    }

    function renderResults(channels) {
        results.innerHTML = '';

        if (!Array.isArray(channels) || !channels.length) {
            setMessage('Geen kanaal gevonden.');
            return;
        }

        channels.forEach(function (channel) {
            var button = document.createElement('button');
            var img = document.createElement('img');
            var copy = document.createElement('span');
            var title = document.createElement('span');
            var id = document.createElement('span');
            var arrow = document.createElement('span');

            button.type = 'button';
            button.className = 'yts-result';

            img.className = 'yts-result-avatar';
            img.alt = '';
            img.referrerPolicy = 'no-referrer';
            img.src = safeImage(channel.avatar)
                || '/icons/follower-profile.svg?v=1';
            img.onerror = function () {
                img.onerror = null;
                img.src = '/icons/follower-profile.svg?v=1';
            };

            copy.className = 'yts-result-copy';

            title.className = 'yts-result-title';
            title.textContent = channel.title || 'YouTube-kanaal';

            id.className = 'yts-result-id';
            id.textContent = channel.id || '';

            arrow.className = 'yts-result-arrow';
            arrow.textContent = '→';
            arrow.setAttribute('aria-hidden', 'true');

            copy.appendChild(title);
            copy.appendChild(id);
            button.appendChild(img);
            button.appendChild(copy);
            button.appendChild(arrow);

            button.addEventListener('click', function () {
                selectChannel(channel);
            });

            results.appendChild(button);
        });
    }

    function runSearch(query, fromSubmit) {
        query = String(query || '').trim();

        if (query.length < 2) {
            return Promise.resolve();
        }

        if (searchBusy) {
            queuedSearch = query;
            return Promise.resolve();
        }

        searchBusy = true;
        queuedSearch = '';
        lastSearch = query;

        if (fromSubmit) {
            submit.disabled = true;
            submit.textContent = 'Zoeken…';
        }

        setMessage('Kanalen zoeken…');

        return fetchJson(
            lookupUrl + '?' + new URLSearchParams({ query: query }).toString()
        )
            .then(function (data) {
                var currentQuery = input.value.trim();
                var channels = Array.isArray(data.channels)
                    ? data.channels
                    : [];

                if (currentQuery !== query && !fromSubmit) {
                    queuedSearch = currentQuery;
                    return;
                }

                setMessage('');
                renderResults(channels);

                if (channels.length === 1 && fromSubmit) {
                    selectChannel(channels[0]);
                }
            })
            .catch(function () {
                if (input.value.trim() === query) {
                    setMessage('Kanalen konden niet worden geladen.');
                }
            })
            .finally(function () {
                searchBusy = false;

                if (fromSubmit) {
                    submit.disabled = false;
                    submit.textContent = 'Zoek kanaal →';
                }

                var next = queuedSearch;
                queuedSearch = '';

                if (next && next !== lastSearch && next.length >= 2) {
                    window.setTimeout(function () {
                        runSearch(next, false);
                    }, 0);
                }
            });
    }

    function loadStats() {
        if (!currentChannel || document.hidden) {
            return Promise.resolve();
        }

        status.textContent = 'Refreshing…';

        return fetchJson(
            statsBase
                + '/'
                + encodeURIComponent(currentChannel)
                + '?_='
                + encodeURIComponent(String(Date.now()))
        )
            .then(function (data) {
                if (data.id !== currentChannel) {
                    return;
                }

                renderChannel(data);
                status.textContent = 'Live';
                setMessage('');
            })
            .catch(function () {
                status.textContent = 'Retrying…';
            });
    }

    function selectChannel(channel) {
        if (refreshTimer) {
            window.clearInterval(refreshTimer);
        }

        results.innerHTML = '';
        setMessage('');
        resetSessionMetrics();
        about.hidden = true;
        bannerWrap.hidden = true;
        renderChannel(channel);

        history.replaceState(
            null,
            '',
            location.pathname + '?channel=' + encodeURIComponent(channel.id)
        );

        loadStats();
        refreshTimer = window.setInterval(loadStats, 5000);
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        var query = input.value.trim();

        if (query.length < 2) {
            setMessage('Vul minimaal 2 tekens in.');
            return;
        }

        window.clearTimeout(searchTimer);
        runSearch(query, true);
    });

    input.addEventListener('input', function () {
        var query = input.value.trim();

        window.clearTimeout(searchTimer);

        if (query.length < 2) {
            results.innerHTML = '';
            setMessage('');
            return;
        }

        searchTimer = window.setTimeout(function () {
            if (query !== lastSearch) {
                runSearch(query, false);
            }
        }, 420);
    });

    changeUserButton.addEventListener('click', function () {
        input.focus();
        input.select();
        window.scrollTo({
            top: Math.max(0, input.getBoundingClientRect().top + window.scrollY - 120),
            behavior: 'smooth'
        });
    });

    compareToggle.addEventListener('click', function () {
        comparePanel.hidden = !comparePanel.hidden;
        embedPanel.hidden = true;

        if (!comparePanel.hidden) {
            compareQuery.focus();
            if (compareChannel) {
                loadCompareStats();
            }
        }
    });

    embedToggle.addEventListener('click', function () {
        embedPanel.hidden = !embedPanel.hidden;
        comparePanel.hidden = true;

        if (!embedPanel.hidden && currentChannel) {
            embedCode.value = buildEmbedCode(currentChannel);
        }
    });

    compareSearchButton.addEventListener('click', searchCompareChannel);

    compareQuery.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            searchCompareChannel();
        }
    });

    embedCopy.addEventListener('click', function () {
        if (!embedCode.value) {
            return;
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(embedCode.value)
                .then(function () {
                    embedCopy.textContent = 'Copied';
                    window.setTimeout(function () {
                        embedCopy.textContent = 'Copy';
                    }, 1200);
                })
                .catch(function () {
                    embedCode.focus();
                    embedCode.select();
                });
            return;
        }

        embedCode.focus();
        embedCode.select();
    });

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden && currentChannel) {
            loadStats();

            if (compareChannel) {
                loadCompareStats();
            }
        }
    });

    var initial = new URLSearchParams(location.search).get('channel');

    if (initial && /^UC[A-Za-z0-9_-]{22}$/.test(initial)) {
        resetSessionMetrics();
        renderChannel({
            id: initial,
            title: 'YouTube channel',
            avatar: '/icons/follower-profile.svg?v=1'
        });
        loadStats();
        refreshTimer = window.setInterval(loadStats, 5000);
    }
}());
</script>
@endpush
