@extends('layouts.site-layout')

@section('title', 'Live Count | Mashal Studio')
@section('meta_description', 'Volg publieke TikTok-statistieken live met Mashal Studio Live Count.')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/odometer/odometer-theme-minimal.css') }}">
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

        .ttc-stats {
            grid-template-columns: 1fr;
        }

        .ttc-stat {
            min-height: 120px;
        }

        .ttc-stat-value {
            font-size: 38px;
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

    #ttc-refresh {
        display: none !important;
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
    }

    @media (max-width: 560px) {

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

    /* Livecounts-style rolling number animation. */
    .ttc-odometer {
        display: inline-flex;
        align-items: baseline;
        white-space: nowrap;
        font: inherit;
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }

    .ttc-odo-digit {
        width: .62em;
        height: 1em;
        position: relative;
        display: inline-block;
        overflow: hidden;
        vertical-align: top;
    }

    .ttc-odo-reel {
        position: absolute;
        inset: 0 0 auto;
        display: flex;
        flex-direction: column;
        will-change: transform;
        transform: translate3d(0, 0, 0);
        transition-property: transform;
        transition-duration: var(--odo-duration, 620ms);
        transition-timing-function: cubic-bezier(.16, 1, .3, 1);
    }

    .ttc-odo-number {
        width: 100%;
        height: 1em;
        flex: 0 0 1em;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .ttc-odo-separator {
        width: .34em;
        height: 1em;
        display: inline-flex;
        align-items: flex-end;
        justify-content: center;
        line-height: 1;
        opacity: .82;
    }

    .ttc-odo-flash {
        animation: ttcOdoFlash .72s ease-out;
    }

    @keyframes ttcOdoFlash {
        0% {
            text-shadow: 0 0 0 rgba(149,140,255,0);
        }
        35% {
            text-shadow: 0 0 18px rgba(149,140,255,.38);
        }
        100% {
            text-shadow: 0 0 0 rgba(149,140,255,0);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .ttc-odo-reel {
            transition: none !important;
        }

        .ttc-odo-flash {
            animation: none !important;
        }
    }


    /* Final counter sizing: deliberately larger, Live Count-like. */
    .ttc-stat {
        min-height: 210px;
        padding: 26px 24px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .ttc-stat-head {
        margin-bottom: 12px;
    }

    .ttc-stat-label {
        font-size: 12px;
        letter-spacing: .14em;
    }

    .ttc-stat-value {
        margin-top: 12px;
        font-size: clamp(60px, 6.2vw, 82px) !important;
        line-height: .9;
        font-weight: 800;
        letter-spacing: -.075em;
        white-space: nowrap;
        overflow: visible;
    }

    .ttc-odometer {
        line-height: .9;
        letter-spacing: -.075em;
    }

    .ttc-odo-digit {
        width: .52em;
        height: 1em;
        overflow: hidden;
        -webkit-mask-image: linear-gradient(
            to bottom,
            transparent 0%,
            #000 10%,
            #000 90%,
            transparent 100%
        );
        mask-image: linear-gradient(
            to bottom,
            transparent 0%,
            #000 10%,
            #000 90%,
            transparent 100%
        );
    }

    .ttc-odo-separator {
        width: .20em;
        opacity: .72;
        transform: translateY(-.01em);
    }

    .ttc-odo-reel {
        transition-timing-function: cubic-bezier(.12,.78,.18,1);
        backface-visibility: hidden;
    }

    @media (max-width: 980px) {
        .ttc-stat-value {
            font-size: clamp(58px, 8vw, 78px) !important;
        }
    }

    @media (max-width: 640px) {
        .ttc-stat {
            min-height: 180px;
            padding: 22px 20px;
        }

        .ttc-stat-value {
            font-size: clamp(54px, 15vw, 72px) !important;
        }
    }

    /* Final polished counter layout */
    .ttc-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .ttc-stat {
        min-width: 0;
        min-height: 184px;
        padding: 22px 20px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 22px;
        background:
            radial-gradient(circle at 88% 8%, rgba(123,112,255,.14), transparent 32%),
            linear-gradient(180deg, rgba(255,255,255,.027), rgba(255,255,255,.011));
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,.025),
            0 12px 34px rgba(0,0,0,.18);
    }

    .ttc-stat-head {
        margin: 0 0 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .ttc-stat-label {
        color: #89919e;
        font-size: 11px;
        font-weight: 850;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .ttc-stat-badge {
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        display: grid;
        place-items: center;
        border-radius: 14px;
        color: #9c8cff;
        border: 1px solid rgba(156,140,255,.18);
        background: rgba(132,116,255,.08);
        box-shadow: inset 0 0 22px rgba(150,136,255,.035);
    }

    .ttc-stat-badge svg {
        width: 22px;
        height: 22px;
        display: block;
    }

    .ttc-stat-value {
        width: 100%;
        margin: 4px 0 0;
        color: #fff;
        font-size: clamp(46px, 5.2vw, 70px) !important;
        line-height: .94;
        font-weight: 800;
        letter-spacing: -.045em;
        white-space: nowrap;
        overflow: hidden;
        font-variant-numeric: tabular-nums;
    }

    .ttc-odometer {
        max-width: 100%;
        display: inline-flex;
        align-items: baseline;
        white-space: nowrap;
        line-height: .94;
        letter-spacing: -.045em;
        font: inherit;
    }

    .ttc-odo-digit {
        width: .56em;
        height: 1em;
        position: relative;
        display: inline-block;
        overflow: hidden;
        vertical-align: top;
        -webkit-mask-image: linear-gradient(to bottom, transparent 0%, #000 8%, #000 92%, transparent 100%);
        mask-image: linear-gradient(to bottom, transparent 0%, #000 8%, #000 92%, transparent 100%);
    }

    .ttc-odo-reel {
        position: absolute;
        inset: 0 0 auto;
        display: flex;
        flex-direction: column;
        will-change: transform;
        backface-visibility: hidden;
        transition-property: transform;
        transition-duration: var(--odo-duration, 480ms);
        transition-timing-function: cubic-bezier(.15,.82,.22,1);
    }

    .ttc-odo-number {
        width: 100%;
        height: 1em;
        flex: 0 0 1em;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    .ttc-odo-separator {
        width: .24em;
        height: 1em;
        display: inline-flex;
        align-items: flex-end;
        justify-content: center;
        padding-bottom: .10em;
        font-size: .52em;
        line-height: 1;
        font-weight: 800;
        letter-spacing: 0;
        opacity: .72;
    }

    @media (max-width: 900px) {
        .ttc-stats {
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .ttc-stat {
            min-height: 156px;
            padding: 18px 18px;
            border-radius: 20px;
        }

        .ttc-stat-head {
            margin-bottom: 12px;
        }

        .ttc-stat-value {
            font-size: clamp(38px, 10.5vw, 58px) !important;
            letter-spacing: -.035em;
        }

        .ttc-odometer {
            letter-spacing: -.035em;
        }

        .ttc-stat-badge {
            width: 42px;
            height: 42px;
            flex-basis: 42px;
            border-radius: 13px;
        }

        .ttc-stat-badge svg {
            width: 20px;
            height: 20px;
        }
    }

    @media (max-width: 420px) {
        .ttc-stat {
            min-height: 148px;
            padding: 17px 16px;
        }

        .ttc-stat-value {
            font-size: clamp(36px, 10vw, 52px) !important;
        }
    }


    /* HubSpot Odometer integration: same ribbon/slide engine used for the live counter feel. */
    .ttc-stat-value.odometer {
        display: block;
        width: 100%;
        position: relative;
        vertical-align: baseline;
        overflow: visible;
        font-family: inherit;
        line-height: .94;
    }

    .ttc-stat-value.odometer .odometer-inside {
        display: inline-block;
        white-space: nowrap;
        font: inherit;
        line-height: inherit;
    }

    .ttc-stat-value.odometer .odometer-digit {
        vertical-align: baseline;
    }

    .ttc-stat-value.odometer .odometer-digit-spacer {
        vertical-align: baseline;
    }

    .ttc-stat-value.odometer .odometer-value {
        font: inherit;
        line-height: inherit;
        text-align: center;
    }

    .ttc-stat-value.odometer .odometer-formatting-mark {
        display: inline-block;
        margin: 0 .015em;
        color: rgba(255,255,255,.72);
        font-size: .72em;
        font-weight: 800;
        line-height: 1;
        transform: translateY(.06em);
    }

    .ttc-stat-value.odometer.odometer-animating-up .odometer-ribbon-inner,
    .ttc-stat-value.odometer.odometer-animating-down.odometer-animating .odometer-ribbon-inner {
        transition-duration: 2s;
        transition-timing-function: ease;
    }

    @media (prefers-reduced-motion: reduce) {
        .ttc-stat-value.odometer .odometer-ribbon-inner {
            transition-duration: 0s !important;
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
                data-direct-livecounts="1"
                data-livecounts-endpoint="{{ route('tiktok-counter.livecounts-cards', ['videoId' => $videoId]) }}"
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
                                                            <div class="ttc-stats" id="ttc-livecounts-cards">
                        <div class="ttc-stat ttc-stat--views">
                            <div class="ttc-stat-head">
                                <div class="ttc-stat-label">Views</div>
                                <div class="ttc-stat-badge" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <path d="M2 12s3.7-6 10-6 10 6 10 6-3.7 6-10 6S2 12 2 12Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                        <circle cx="12" cy="12" r="3.2" fill="currentColor"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ttc-stat-value ttc-loading" data-livecounts-stat="views">—</div>
                        </div>

                        <div class="ttc-stat ttc-stat--likes">
                            <div class="ttc-stat-head">
                                <div class="ttc-stat-label">Likes</div>
                                <div class="ttc-stat-badge" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 20.5S4 15.8 4 9.6C4 6.7 5.9 5 8.2 5c1.7 0 3 1 3.8 2.2C12.8 6 14.1 5 15.8 5 18.1 5 20 6.7 20 9.6c0 6.2-8 10.9-8 10.9Z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ttc-stat-value ttc-loading" data-livecounts-stat="likes">—</div>
                        </div>

                        <div class="ttc-stat ttc-stat--comments">
                            <div class="ttc-stat-head">
                                <div class="ttc-stat-label">Comments</div>
                                <div class="ttc-stat-badge" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <path d="M5 5.5h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H10l-5 3v-3a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2Z" fill="currentColor"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ttc-stat-value ttc-loading" data-livecounts-stat="comments">—</div>
                        </div>

                        <div class="ttc-stat ttc-stat--shares">
                            <div class="ttc-stat-head">
                                <div class="ttc-stat-label">Shares</div>
                                <div class="ttc-stat-badge" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M13.5 4 21 11.5 13.5 19v-4.1c-4.9.2-8 1.8-10.5 5.1 1-6.2 4.5-10.1 10.5-10.7V4Z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="ttc-stat-value ttc-loading" data-livecounts-stat="shares">—</div>
                        </div>
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
<script>window.odometerOptions = { auto: false };</script>
<script src="{{ asset('vendor/odometer/odometer.min.js') }}"></script>
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
    var livecountsEndpoint = root.getAttribute('data-livecounts-endpoint') || '';
    var livecountsElements = {
        views: document.querySelector('[data-livecounts-stat="views"]'),
        likes: document.querySelector('[data-livecounts-stat="likes"]'),
        comments: document.querySelector('[data-livecounts-stat="comments"]'),
        shares: document.querySelector('[data-livecounts-stat="shares"]')
    };
    var livecountsTimer = null;


    function animateOdometer(element, value) {
        var numericValue = Number(value);
        var formatted;

        if (!element || !isFinite(numericValue)) {
            return;
        }

        numericValue = Math.max(0, Math.round(numericValue));

        try {
            formatted = numericValue.toLocaleString('nl-NL');
        } catch (error) {
            formatted = String(numericValue);
        }

        element.setAttribute('aria-label', formatted);
        element.classList.remove('ttc-loading');

        if (!window.Odometer) {
            element.textContent = formatted;
            return;
        }

        if (!element._mashalOdometer) {
            element.textContent = '0';

            element._mashalOdometer = new window.Odometer({
                el: element,
                value: 0,
                format: '(.ddd)',
                theme: 'minimal',
                duration: 2000
            });

            window.requestAnimationFrame(function () {
                window.requestAnimationFrame(function () {
                    element._mashalOdometer.update(numericValue);
                });
            });

            return;
        }

        element._mashalOdometer.update(numericValue);
    }

    function formatCount(value) {
        var number = Number(value);
        if (!isFinite(number)) {
            return '—';
        }
        try {
            return number.toLocaleString('nl-NL');
        } catch (error) {
            return String(number);
        }
    }

    function scheduleLivecounts() {
        if (livecountsTimer !== null) {
            window.clearTimeout(livecountsTimer);
        }
        livecountsTimer = window.setTimeout(loadLivecountsCards, 5000);
    }

    function loadLivecountsCards() {
        var xhr;

        if (!livecountsEndpoint) {
            return;
        }

        xhr = new XMLHttpRequest();
        xhr.open(
            'GET',
            livecountsEndpoint
                + (livecountsEndpoint.indexOf('?') === -1 ? '?' : '&')
                + '_=' + encodeURIComponent(String(new Date().getTime())),
            true
        );
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('Cache-Control', 'no-cache');
        xhr.timeout = 40000;

        xhr.onreadystatechange = function () {
            var data;
            var stats;
            var keys = ['views', 'likes', 'comments', 'shares'];
            var i;
            var key;
            var element;

            if (xhr.readyState !== 4) {
                return;
            }

            if (xhr.status < 200 || xhr.status >= 300) {
                setStatus('Livecounts-data kon niet worden opgehaald', false);
                scheduleLivecounts();
                return;
            }

            try {
                data = JSON.parse(xhr.responseText || '{}');
            } catch (error) {
                setStatus('Ongeldige Livecounts-response', false);
                scheduleLivecounts();
                return;
            }

            if (!data || data.success === false || !data.stats) {
                setStatus(data && data.message ? data.message : 'Livecounts gaf geen stats terug', false);
                scheduleLivecounts();
                return;
            }

            stats = data.stats;

            for (i = 0; i < keys.length; i += 1) {
                key = keys[i];
                element = livecountsElements[key];
                if (!element) {
                    continue;
                }
                animateOdometer(element, stats[key]);
            }

            setStatus('Livecounts actief · Views, Likes, Comments en Shares', true);
            if (updatedElement) {
                updatedElement.textContent = 'Bijgewerkt ' + new Date().toLocaleTimeString('nl-NL');
            }

            scheduleLivecounts();
        };

        xhr.onerror = function () {
            setStatus('Netwerkfout bij Livecounts', false);
            scheduleLivecounts();
        };

        xhr.ontimeout = function () {
            setStatus('Livecounts ophalen duurde te lang', false);
            scheduleLivecounts();
        };

        xhr.send(null);
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

    loadLivecountsCards();

    window.addEventListener('beforeunload', function () {
        if (livecountsTimer !== null) {
            window.clearTimeout(livecountsTimer);
        }
    });
}());
</script>
@endpush
@endisset
