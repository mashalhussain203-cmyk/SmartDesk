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


    /* Photo-2 dashboard styling */
    .ttc-result {
        grid-template-columns: 340px minmax(0, 1fr);
        gap: 20px;
    }

    .ttc-preview {
        padding: 14px;
        border-radius: 24px;
    }

    .ttc-thumb {
        min-height: 560px;
        border-radius: 18px;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,.035);
    }

    .ttc-dashboard {
        padding: 20px;
        border-radius: 24px;
    }

    .ttc-dashboard-head {
        min-height: 28px;
        margin-bottom: 18px;
    }

    .ttc-status {
        color: #9ca6b5;
        font-size: 11px;
    }

    .ttc-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .ttc-stat {
        min-height: 170px;
        padding: 22px;
        border-radius: 18px;
        background:
            radial-gradient(circle at 92% 10%, rgba(123,112,255,.13), transparent 35%),
            linear-gradient(145deg, rgba(255,255,255,.03), rgba(255,255,255,.012));
        box-shadow: inset 0 1px 0 rgba(255,255,255,.025);
    }

    .ttc-stat-label {
        font-size: 11px;
        letter-spacing: .12em;
    }

    .ttc-stat-badge {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        font-size: 15px;
        color: #9b91ff;
        background: rgba(123,112,255,.08);
    }

    .ttc-stat-value {
        margin-top: 18px;
        font-size: clamp(42px, 5vw, 64px);
        line-height: .92;
        font-weight: 760;
        letter-spacing: -.055em;
    }

    .ttc-stat-delta {
        margin-top: 14px;
        font-size: 11px;
        color: #69e6a5;
    }

    .ttc-chart-wrap {
        margin-top: 16px;
        padding: 18px;
        border-radius: 18px;
        min-height: 230px;
        background:
            linear-gradient(180deg, rgba(255,255,255,.02), rgba(255,255,255,.008));
    }

    .ttc-chart {
        height: 170px;
    }

    .ttc-chart-title {
        font-size: 13px;
    }

    .ttc-actions {
        margin-top: 16px;
        grid-template-columns: repeat(2, minmax(0,1fr));
        gap: 12px;
    }

    #ttc-refresh {
        display: none !important;
    }

    .ttc-action {
        min-height: 52px;
        border-radius: 13px;
        font-size: 11px;
    }

    @media (max-width: 980px) {
        .ttc-result {
            grid-template-columns: 1fr;
        }

        .ttc-preview {
            display: grid;
            grid-template-columns: 240px minmax(0,1fr);
            gap: 18px;
        }

        .ttc-thumb {
            min-height: 360px;
        }
    }

    @media (max-width: 640px) {
        .ttc-preview {
            display: block;
        }

        .ttc-thumb {
            min-height: 460px;
        }

        .ttc-stats {
            grid-template-columns: 1fr;
        }

        .ttc-stat-value {
            font-size: 44px;
        }
    }


    .ttc-live-tools {
        margin: 14px 0 4px;
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 8px;
    }

    .ttc-live-tool {
        min-height: 40px;
        padding: 0 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border: 1px solid var(--line);
        border-radius: 10px;
        color: #929ba8;
        background: rgba(255,255,255,.018);
        text-decoration: none;
        font: inherit;
        font-size: 9px;
        font-weight: 800;
        cursor: pointer;
        transition: .18s ease;
    }

    .ttc-live-tool:hover {
        color: #f3f5f7;
        border-color: rgba(123,112,255,.34);
        background: rgba(123,112,255,.07);
    }

    .ttc-advanced {
        margin-top: 14px;
        border: 1px solid rgba(255,255,255,.065);
        border-radius: 15px;
        overflow: hidden;
        background: rgba(255,255,255,.012);
    }

    .ttc-advanced-toggle {
        width: 100%;
        min-height: 46px;
        padding: 0 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 0;
        color: #cfd4dc;
        background: transparent;
        font: inherit;
        font-size: 10px;
        font-weight: 800;
        cursor: pointer;
    }

    .ttc-advanced-body {
        display: none;
        padding: 0 14px 14px;
        grid-template-columns: repeat(2, minmax(0,1fr));
        gap: 10px;
    }

    .ttc-advanced.is-open .ttc-advanced-body {
        display: grid;
    }

    .ttc-advanced-metric {
        padding: 13px;
        border: 1px solid var(--line);
        border-radius: 12px;
        background: rgba(255,255,255,.014);
    }

    .ttc-advanced-label {
        color: #626d7b;
        font-size: 8px;
        font-weight: 850;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .ttc-advanced-value {
        margin-top: 7px;
        color: #f4f6f8;
        font-size: 22px;
        font-weight: 720;
        font-variant-numeric: tabular-nums;
    }

    .ttc-settings-panel {
        display: none;
        margin-top: 10px;
        padding: 12px 14px;
        border: 1px solid rgba(123,112,255,.16);
        border-radius: 12px;
        background: rgba(123,112,255,.035);
    }

    .ttc-settings-panel.is-open {
        display: block;
    }

    .ttc-setting {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        color: #8e98a6;
        font-size: 10px;
    }

    .ttc-setting + .ttc-setting {
        margin-top: 9px;
    }

    .ttc-setting input {
        accent-color: #7b70ff;
    }

    @media (max-width: 900px) {
        .ttc-live-tools {
            grid-template-columns: repeat(3, minmax(0,1fr));
        }
    }

    @media (max-width: 560px) {
        .ttc-live-tools {
            grid-template-columns: repeat(2, minmax(0,1fr));
        }

        .ttc-advanced-body {
            grid-template-columns: 1fr;
        }
    }


    .ttc-direct-livecounts {
        margin-top: 14px;
        min-height: 760px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 18px;
        background:
            radial-gradient(circle at 50% 0%, rgba(123,112,255,.08), transparent 38%),
            #090c12;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.025);
    }

    .ttc-direct-livecounts iframe {
        display: block;
        width: 100%;
        height: 760px;
        border: 0;
        background: transparent;
        color-scheme: dark;
    }

    .ttc-direct-note {
        margin-top: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        color: #697483;
        font-size: 9px;
        line-height: 1.5;
    }

    .ttc-direct-note strong {
        color: #76e6aa;
        font-weight: 800;
    }

    .ttc-direct-note a {
        color: #9188ff;
        text-decoration: none;
        font-weight: 800;
    }

    .ttc-preview-player {
        width: 100%;
        min-height: 560px;
        border: 0;
        border-radius: 18px;
        background: #05070b;
    }

    @media (max-width: 980px) {
        .ttc-direct-livecounts,
        .ttc-direct-livecounts iframe {
            min-height: 680px;
            height: 680px;
        }

        .ttc-preview-player {
            min-height: 420px;
        }
    }

    .ttc-supplemental {
        margin-top: 14px;
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 10px;
    }

    .ttc-supplemental-card {
        min-height: 120px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 16px;
        background:
            radial-gradient(circle at 92% 10%, rgba(123,112,255,.12), transparent 38%),
            rgba(255,255,255,.015);
    }

    .ttc-supplemental-label {
        color: #7e8998;
        font-size: 9px;
        font-weight: 850;
        letter-spacing: .13em;
        text-transform: uppercase;
    }

    .ttc-supplemental-value {
        margin-top: 7px;
        color: #f3f5f8;
        font-size: clamp(34px, 4vw, 52px);
        line-height: 1;
        font-weight: 780;
        letter-spacing: -.045em;
        font-variant-numeric: tabular-nums;
    }

    .ttc-supplemental-source {
        margin-top: 8px;
        color: #65707e;
        font-size: 9px;
    }

    .ttc-supplemental-icon {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        display: grid;
        place-items: center;
        border-radius: 13px;
        color: #aaa4ff;
        background: rgba(123,112,255,.09);
        font-size: 18px;
    }

    .ttc-livecounts-fields {
        margin-top: 10px;
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }

    .ttc-livecounts-field {
        padding: 6px 9px;
        border: 1px solid rgba(110,231,168,.13);
        border-radius: 999px;
        color: #7f8b98;
        background: rgba(110,231,168,.025);
        font-size: 8px;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
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
                data-direct-livecounts="1"
                data-supplemental-endpoint="{{ route('tiktok-counter.supplemental', ['videoId' => $videoId]) }}"
            >
                <aside class="ttc-card ttc-preview">
                    <iframe
                        class="ttc-preview-player"
                        src="https://www.tiktok.com/player/v1/{{ $videoId }}"
                        title="TikTok video"
                        loading="eager"
                        allow="autoplay; encrypted-media; picture-in-picture"
                        allowfullscreen
                    ></iframe>

                    <div class="ttc-video-meta">
                        <div class="ttc-author">TikTok video</div>
                        <p class="ttc-video-title">
                            Video ID {{ $videoId }}
                        </p>
                    </div>
                </aside>

                <div class="ttc-card ttc-dashboard">
                    <div class="ttc-dashboard-head">
                        <div class="ttc-status">
                            <span class="ttc-status-dot" id="ttc-status-dot"></span>
                            <span id="ttc-status-text">Directe Livecounts bron laden…</span>
                        </div>

                        <div class="ttc-updated" id="ttc-updated">
                            Rechtstreeks via Livecounts.io
                        </div>
                    </div>

                    <div class="ttc-live-tools">
                        <button class="ttc-live-tool" type="button" id="ttc-change-user">⌕ Change User</button>
                        <a class="ttc-live-tool" href="https://livecounts.io/compare/tiktok-live-view-counter" target="_blank" rel="noopener noreferrer">⇄ Compare</a>
                        <a class="ttc-live-tool" href="https://livecounts.io/spotlight?service=tiktok-live-view-counter" target="_blank" rel="noopener noreferrer">✦ Spotlight</a>
                        <a class="ttc-live-tool" href="https://livecounts.io/tiktok-live-view-counter/{{ $videoId }}" target="_blank" rel="noopener noreferrer">◉ Open Livecounts</a>
                        <button class="ttc-live-tool" type="button" id="ttc-share">↗ Share</button>
                        <a class="ttc-live-tool" href="{{ $videoUrl }}" target="_blank" rel="noopener noreferrer">♪ Visit TikTok</a>
                    </div>

                    <div class="ttc-direct-livecounts">
                        <iframe
                            id="ttc-livecounts-embed"
                            src="https://livecounts.io/embed/tiktok-live-view-counter/{{ $videoId }}"
                            title="Livecounts TikTok live view counter"
                            loading="eager"
                            referrerpolicy="strict-origin-when-cross-origin"
                            allow="clipboard-read; clipboard-write"
                        ></iframe>
                    </div>

                    <div class="ttc-direct-note">
                        <span><strong>DIRECT</strong> · Deze teller draait rechtstreeks vanaf Livecounts.io. Mashal kopieert of schat de cijfers niet.</span>
                        <a href="https://livecounts.io/tiktok-live-view-counter/{{ $videoId }}" target="_blank" rel="noopener noreferrer">Bron openen ↗</a>
                    </div>

                    <div class="ttc-livecounts-fields" aria-label="Livecounts velden">
                        <span class="ttc-livecounts-field">✓ Views</span>
                        <span class="ttc-livecounts-field">✓ Likes</span>
                        <span class="ttc-livecounts-field">✓ Comments</span>
                        <span class="ttc-livecounts-field">✓ Shares</span>
                    </div>

                    <div class="ttc-supplemental">
                        <div class="ttc-supplemental-card">
                            <div>
                                <div class="ttc-supplemental-label">Favorites</div>
                                <div class="ttc-supplemental-value" id="ttc-favorites-value">—</div>
                                <div class="ttc-supplemental-source" id="ttc-favorites-source">
                                    TikTok publieke videodata laden…
                                </div>
                            </div>
                            <div class="ttc-supplemental-icon">★</div>
                        </div>
                    </div>

                    <div class="ttc-actions">
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

    var statusText = document.getElementById('ttc-status-text');
    var statusDot = document.getElementById('ttc-status-dot');
    var updatedElement = document.getElementById('ttc-updated');
    var embed = document.getElementById('ttc-livecounts-embed');
    var changeUserButton = document.getElementById('ttc-change-user');
    var shareButton = document.getElementById('ttc-share');
    var copyButton = document.getElementById('ttc-copy');
    var supplementalEndpoint = root.getAttribute('data-supplemental-endpoint') || '';
    var videoUrl = root.getAttribute('data-video-url') || '';
    var favoritesValue = document.getElementById('ttc-favorites-value');
    var favoritesSource = document.getElementById('ttc-favorites-source');
    var favoritesTimer = null;

    function loadFavorites() {
        var requestUrl;
        var separator;
        var xhr;

        if (!supplementalEndpoint || !videoUrl || !favoritesValue) {
            return;
        }

        separator = supplementalEndpoint.indexOf('?') === -1 ? '?' : '&';
        requestUrl = supplementalEndpoint + separator
            + 'url=' + encodeURIComponent(videoUrl)
            + '&_=' + encodeURIComponent(String(new Date().getTime()));

        xhr = new XMLHttpRequest();
        xhr.open('GET', requestUrl, true);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('Cache-Control', 'no-cache');
        xhr.timeout = 20000;

        xhr.onreadystatechange = function () {
            var data;
            var value;

            if (xhr.readyState !== 4) {
                return;
            }

            if (xhr.status < 200 || xhr.status >= 300) {
                if (favoritesSource) {
                    favoritesSource.textContent = 'Favorites tijdelijk niet beschikbaar';
                }
                scheduleFavorites();
                return;
            }

            try {
                data = JSON.parse(xhr.responseText || '{}');
            } catch (error) {
                if (favoritesSource) {
                    favoritesSource.textContent = 'Ongeldige Favorites-response';
                }
                scheduleFavorites();
                return;
            }

            value = data && data.stats ? Number(data.stats.favorites) : NaN;

            if (isFinite(value)) {
                try {
                    favoritesValue.textContent = value.toLocaleString('nl-NL');
                } catch (error) {
                    favoritesValue.textContent = String(value);
                }

                if (favoritesSource) {
                    favoritesSource.textContent = 'TikTok public · collectCount · elke 15 sec';
                }
            } else if (favoritesSource) {
                favoritesSource.textContent = 'TikTok public geeft geen Favorites voor deze video';
            }

            scheduleFavorites();
        };

        xhr.onerror = function () {
            if (favoritesSource) {
                favoritesSource.textContent = 'Favorites netwerkfout';
            }
            scheduleFavorites();
        };

        xhr.ontimeout = function () {
            if (favoritesSource) {
                favoritesSource.textContent = 'Favorites ophalen duurde te lang';
            }
            scheduleFavorites();
        };

        xhr.send(null);
    }

    function scheduleFavorites() {
        if (favoritesTimer !== null) {
            window.clearTimeout(favoritesTimer);
        }

        favoritesTimer = window.setTimeout(loadFavorites, 15000);
    }

    function setStatus(text, ok) {
        if (statusText) {
            statusText.textContent = text;
        }

        if (statusDot) {
            statusDot.style.background = ok === false ? '#ff8095' : '#6ee7a8';
            statusDot.style.boxShadow = ok === false
                ? '0 0 13px rgba(255,128,149,.55)'
                : '0 0 13px rgba(110,231,168,.62)';
        }
    }

    function copyCurrentUrl(button) {
        var input = document.createElement('textarea');
        var original = button ? button.textContent : '';

        input.value = window.location.href;
        input.setAttribute('readonly', 'readonly');
        input.style.position = 'fixed';
        input.style.left = '-9999px';
        document.body.appendChild(input);
        input.select();

        try {
            document.execCommand('copy');
            if (button) {
                button.textContent = '✓ Gekopieerd';
            }
        } catch (error) {
            if (button) {
                button.textContent = 'Kopiëren mislukt';
            }
        }

        document.body.removeChild(input);

        if (button) {
            window.setTimeout(function () {
                button.textContent = original;
            }, 1400);
        }
    }

    if (embed) {
        embed.addEventListener('load', function () {
            setStatus('Livecounts direct embed actief', true);
            if (updatedElement) {
                updatedElement.textContent = 'Live · rechtstreeks via Livecounts.io';
            }
        });

        window.setTimeout(function () {
            if (statusText && statusText.textContent.indexOf('laden') !== -1) {
                setStatus('Livecounts embed geladen; teller initialiseert…', true);
            }
        }, 5000);
    }

    if (changeUserButton) {
        changeUserButton.onclick = function () {
            var input = document.querySelector('.ttc-search input[name="url"]');
            if (input) {
                input.scrollIntoView({ behavior: 'smooth', block: 'center' });
                window.setTimeout(function () {
                    input.focus();
                    input.select();
                }, 300);
            }
        };
    }

    if (shareButton) {
        shareButton.onclick = function () {
            if (navigator.share) {
                navigator.share({
                    title: document.title,
                    text: 'TikTok Live Count',
                    url: window.location.href
                }).catch(function () {});
                return;
            }

            copyCurrentUrl(shareButton);
        };
    }

    if (copyButton) {
        copyButton.onclick = function () {
            copyCurrentUrl(copyButton);
        };
    }

    loadFavorites();

    window.addEventListener('beforeunload', function () {
        if (favoritesTimer !== null) {
            window.clearTimeout(favoritesTimer);
        }
    });
}());
</script>
@endpush
@endisset
