@extends('layouts.admin-layout')

@section('title', 'Mashal Admin | Dashboard')

@section('page-title', 'Dashboard')

@section('content')

<style>

    .mashal-dashboard {

        --m-bg: #08090b;

        --m-panel: #101318;

        --m-panel-soft: #15191f;

        --m-panel-hover: #191e25;

        --m-text: #f6f4ef;

        --m-muted: #8b9199;

        --m-muted-2: #686e76;

        --m-line: rgba(255, 255, 255, .075);

        --m-line-strong: rgba(215, 164, 95, .22);

        --m-gold: #d7a45f;

        --m-gold-light: #f1c983;

        --m-gold-dark: #9c6d34;

        --m-green: #65d59a;

        --m-red: #ef8f8f;

        --m-blue: #8fb6ec;

        --m-shadow: 0 28px 80px rgba(0, 0, 0, .24);

        position: relative;

        min-height: 100%;

        color: var(--m-text);

    }

    .mashal-dashboard,

    .mashal-dashboard \* {

        box-sizing: border-box;

    }

    .mashal-dashboard a {

        color: inherit;

    }

    .mashal-dashboard button,

    .mashal-dashboard input {

        font: inherit;

    }

    /* ========================================================= */

    /* HERO                                                       */

    /* ========================================================= */

    .md-hero {

        position: relative;

        overflow: hidden;

        margin-bottom: 22px;

        padding: 34px;

        border: 1px solid var(--m-line);

        border-radius: 28px;

        background:

            radial-gradient(circle at 90% 15%, rgba(215, 164, 95, .14), transparent 18rem),

            linear-gradient(145deg, #11151a, #0c0f13);

        box-shadow: var(--m-shadow);

    }

    .md-hero::after {

        content: "M";

        position: absolute;

        right: -18px;

        bottom: -70px;

        color: rgba(255, 255, 255, .025);

        font-size: clamp(180px, 28vw, 360px);

        line-height: .8;

        font-weight: 950;

        letter-spacing: -.1em;

        pointer-events: none;

    }

    .md-hero-grid {

        position: relative;

        z-index: 1;

        display: grid;

        grid-template-columns: minmax(0, 1.2fr) minmax(300px, .8fr);

        gap: 34px;

        align-items: end;

    }

    .md-eyebrow {

        display: inline-flex;

        align-items: center;

        gap: 9px;

        color: var(--m-gold-light);

        font-size: 9px;

        font-weight: 900;

        letter-spacing: .18em;

        text-transform: uppercase;

    }

    .md-eyebrow::before {

        content: "";

        width: 28px;

        height: 1px;

        background: var(--m-gold);

    }

    .md-hero h1 {

        max-width: 760px;

        margin: 13px 0 0;

        font-size: clamp(38px, 5.3vw, 72px);

        line-height: .98;

        font-weight: 950;

        letter-spacing: -.06em;

    }

    .md-hero h1 span {

        color: var(--m-gold-light);

    }

    .md-hero-copy {

        max-width: 720px;

        margin: 16px 0 0;

        color: var(--m-muted);

        font-size: 13px;

        line-height: 1.8;

    }

    .md-hero-actions {

        margin-top: 24px;

        display: flex;

        gap: 10px;

        flex-wrap: wrap;

    }

    .md-btn {

        min-height: 44px;

        padding: 0 16px;

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 8px;

        border: 1px solid transparent;

        border-radius: 999px;

        background: linear-gradient(135deg, var(--m-gold-light), var(--m-gold));

        color: #17110b !important;

        font-size: 10px;

        font-weight: 900;

        letter-spacing: .02em;

        text-decoration: none;

        cursor: pointer;

        transition:

            transform .2s ease,

            box-shadow .2s ease,

            border-color .2s ease,

            background .2s ease;

    }

    .md-btn:hover {

        transform: translateY(-2px);

        box-shadow: 0 16px 34px rgba(215, 164, 95, .2);

    }

    .md-btn.secondary {

        border-color: var(--m-line);

        background: rgba(255, 255, 255, .035);

        color: #ddd9d1 !important;

    }

    .md-btn.secondary:hover {

        border-color: var(--m-line-strong);

        background: rgba(215, 164, 95, .07);

    }

    .md-btn.danger {

        border-color: rgba(239, 143, 143, .18);

        background: rgba(239, 143, 143, .08);

        color: #efaaaa !important;

    }

    .md-hero-admin {

        padding: 20px;

        border: 1px solid var(--m-line);

        border-radius: 20px;

        background: rgba(255, 255, 255, .025);

        backdrop-filter: blur(12px);

    }

    .md-hero-admin-label {

        color: var(--m-gold-dark);

        font-size: 8px;

        font-weight: 900;

        letter-spacing: .14em;

        text-transform: uppercase;

    }

    .md-hero-admin-name {

        margin-top: 7px;

        color: #fff;

        font-size: 22px;

        font-weight: 900;

        letter-spacing: -.035em;

    }

    .md-hero-admin-email {

        margin-top: 5px;

        color: var(--m-muted);

        font-size: 10px;

        line-height: 1.6;

        word-break: break-word;

    }

    .md-admin-badge {

        margin-top: 15px;

        display: inline-flex;

        align-items: center;

        gap: 7px;

        padding: 8px 10px;

        border: 1px solid rgba(101, 213, 154, .15);

        border-radius: 999px;

        background: rgba(101, 213, 154, .055);

        color: #9ce7bc;

        font-size: 8px;

        font-weight: 900;

        letter-spacing: .05em;

        text-transform: uppercase;

    }

    .md-admin-badge::before {

        content: "";

        width: 6px;

        height: 6px;

        border-radius: 50%;

        background: currentColor;

        box-shadow: 0 0 14px currentColor;

    }

    /* ========================================================= */

    /* STATS                                                      */

    /* ========================================================= */

    .md-stats {

        display: grid;

        grid-template-columns: repeat(5, minmax(0, 1fr));

        gap: 14px;

        margin-bottom: 22px;

    }

    .md-stat-card {

        position: relative;

        overflow: hidden;

        min-height: 166px;

        padding: 20px;

        border: 1px solid var(--m-line);

        border-radius: 20px;

        background: linear-gradient(145deg, var(--m-panel), #0d1014);

        transition:

            transform .22s ease,

            border-color .22s ease,

            background .22s ease;

    }

    .md-stat-card:hover {

        transform: translateY(-4px);

        border-color: var(--m-line-strong);

        background: linear-gradient(145deg, #14181e, #0f1216);

    }

    .md-stat-card::after {

        content: "";

        position: absolute;

        right: -28px;

        bottom: -42px;

        width: 110px;

        height: 110px;

        border-radius: 50%;

        background: radial-gradient(circle, rgba(215,164,95,.08), transparent 68%);

    }

    .md-stat-top {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 12px;

    }

    .md-stat-label {

        color: var(--m-muted);

        font-size: 9px;

        font-weight: 900;

        letter-spacing: .12em;

        text-transform: uppercase;

    }

    .md-stat-icon {

        width: 34px;

        height: 34px;

        display: grid;

        place-items: center;

        border: 1px solid rgba(215,164,95,.16);

        border-radius: 11px;

        background: rgba(215,164,95,.06);

        color: var(--m-gold-light);

        font-size: 10px;

        font-weight: 900;

    }

    .md-stat-number {

        margin-top: 18px;

        color: #fff;

        font-size: 38px;

        line-height: 1;

        font-weight: 950;

        letter-spacing: -.05em;

    }

    .md-stat-foot {

        margin-top: 9px;

        color: var(--m-muted-2);

        font-size: 10px;

        line-height: 1.55;

    }

    /* ========================================================= */

    /* GENERIC PANEL                                              */

    /* ========================================================= */

    .md-panel {

        margin-bottom: 22px;

        padding: 26px;

        border: 1px solid var(--m-line);

        border-radius: 24px;

        background: linear-gradient(145deg, rgba(17,20,25,.97), rgba(12,15,19,.97));

        box-shadow: 0 22px 60px rgba(0, 0, 0, .14);

    }

    .md-section-head {

        margin-bottom: 22px;

        display: flex;

        align-items: flex-end;

        justify-content: space-between;

        gap: 22px;

    }

    .md-section-kicker {

        color: var(--m-gold-dark);

        font-size: 8px;

        font-weight: 900;

        letter-spacing: .15em;

        text-transform: uppercase;

    }

    .md-section-head h2 {

        margin: 7px 0 0;

        color: #fff;

        font-size: 25px;

        line-height: 1.1;

        letter-spacing: -.04em;

    }

    .md-section-head p {

        max-width: 580px;

        margin: 7px 0 0;

        color: var(--m-muted);

        font-size: 11px;

        line-height: 1.7;

    }

    /* ========================================================= */

    /* QUICK ACTIONS                                              */

    /* ========================================================= */

    .md-actions-grid {

        display: grid;

        grid-template-columns: repeat(4, minmax(0, 1fr));

        gap: 14px;

    }

    .md-action-card {

        min-height: 220px;

        padding: 19px;

        display: flex;

        flex-direction: column;

        border: 1px solid var(--m-line);

        border-radius: 18px;

        background: rgba(255,255,255,.022);

        transition:

            transform .22s ease,

            border-color .22s ease,

            background .22s ease;

    }

    .md-action-card:hover {

        transform: translateY(-4px);

        border-color: var(--m-line-strong);

        background: rgba(215,164,95,.045);

    }

    .md-action-icon {

        width: 38px;

        height: 38px;

        display: grid;

        place-items: center;

        border: 1px solid rgba(215,164,95,.15);

        border-radius: 12px;

        color: var(--m-gold-light);

        background: rgba(215,164,95,.055);

        font-size: 11px;

        font-weight: 900;

    }

    .md-action-card h3 {

        margin: 18px 0 0;

        color: #f3f1ec;

        font-size: 16px;

        letter-spacing: -.025em;

    }

    .md-action-card p {

        margin: 8px 0 18px;

        color: var(--m-muted);

        font-size: 10px;

        line-height: 1.7;

    }

    .md-action-card .md-btn {

        margin-top: auto;

        align-self: flex-start;

    }

    /* ========================================================= */

    /* USER TABLE                                                 */

    /* ========================================================= */

    .md-table-tools {

        margin-bottom: 16px;

        display: grid;

        grid-template-columns: minmax(220px, 1fr) auto;

        gap: 12px;

        align-items: center;

    }

    .md-search {

        position: relative;

    }

    .md-search input {

        width: 100%;

        min-height: 44px;

        padding: 0 15px 0 39px;

        border: 1px solid var(--m-line);

        border-radius: 13px;

        outline: none;

        background: rgba(255,255,255,.025);

        color: #eeeae3;

        font-size: 11px;

    }

    .md-search input::placeholder {

        color: #626870;

    }

    .md-search input:focus {

        border-color: rgba(215,164,95,.32);

        box-shadow: 0 0 0 3px rgba(215,164,95,.07);

    }

    .md-search::before {

        content: "⌕";

        position: absolute;

        left: 14px;

        top: 50%;

        transform: translateY(-50%);

        color: var(--m-gold);

        font-size: 17px;

        pointer-events: none;

    }

    .md-filter-bar {

        display: flex;

        gap: 7px;

        flex-wrap: wrap;

    }

    .md-filter {

        min-height: 36px;

        padding: 0 12px;

        border: 1px solid var(--m-line);

        border-radius: 999px;

        background: rgba(255,255,255,.02);

        color: var(--m-muted);

        font-size: 9px;

        font-weight: 800;

        cursor: pointer;

    }

    .md-filter.active,

    .md-filter:hover {

        border-color: rgba(215,164,95,.24);

        background: rgba(215,164,95,.08);

        color: var(--m-gold-light);

    }

    .md-table-wrap {

        overflow-x: auto;

        border: 1px solid var(--m-line);

        border-radius: 18px;

        background: rgba(0,0,0,.09);

    }

    .md-table {

        width: 100%;

        min-width: 1180px;

        border-collapse: collapse;

    }

    .md-table th {

        padding: 13px 14px;

        border-bottom: 1px solid var(--m-line);

        background: rgba(255,255,255,.025);

        color: #737a83;

        font-size: 8px;

        font-weight: 900;

        letter-spacing: .12em;

        text-align: left;

        text-transform: uppercase;

    }

    .md-table td {

        padding: 14px;

        border-bottom: 1px solid rgba(255,255,255,.045);

        color: #c9c7c1;

        font-size: 10px;

        vertical-align: middle;

    }

    .md-table tbody tr {

        transition: background .18s ease;

    }

    .md-table tbody tr:hover {

        background: rgba(215,164,95,.025);

    }

    .md-table tbody tr:last-child td {

        border-bottom: 0;

    }

    .md-user {

        display: flex;

        align-items: center;

        gap: 11px;

        min-width: 190px;

    }

    .md-avatar {

        flex: 0 0 auto;

        width: 38px;

        height: 38px;

        display: grid;

        place-items: center;

        border: 1px solid rgba(215,164,95,.16);

        border-radius: 12px;

        background: linear-gradient(145deg, rgba(215,164,95,.12), rgba(215,164,95,.035));

        color: var(--m-gold-light);

        font-size: 13px;

        font-weight: 950;

    }

    .md-avatar {
        position: relative;
        overflow: hidden;
        box-shadow:
            inset 0 0 0 1px rgba(255,255,255,.025),
            0 10px 26px rgba(0,0,0,.18);
    }

    .md-avatar img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
        border-radius: inherit;
    }

    .md-avatar.has-image {
        background: #111419;
        color: transparent;
    }

    .md-user-meta {
        min-width: 0;
    }

    .md-photo-source {
        margin-top: 5px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        color: #6f757d;
        font-size: 7px;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .md-photo-source::before {
        content: "";
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: var(--m-gold);
        box-shadow: 0 0 10px rgba(215,164,95,.35);
    }

    .md-photo-source.custom {
        color: #d4a861;
    }

    .md-photo-source.social {
        color: #8fb6ec;
    }

    .md-hero-admin-profile {
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 13px;
    }

    .md-hero-admin-avatar {
        width: 54px;
        height: 54px;
        flex: 0 0 54px;
        display: grid;
        place-items: center;
        overflow: hidden;
        border: 1px solid rgba(215,164,95,.22);
        border-radius: 16px;
        background: linear-gradient(
            145deg,
            rgba(215,164,95,.16),
            rgba(215,164,95,.045)
        );
        color: var(--m-gold-light);
        font-size: 17px;
        font-weight: 950;
        box-shadow: 0 14px 34px rgba(0,0,0,.18);
    }

    .md-hero-admin-avatar img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
    }

    .md-hero-admin-identity {
        min-width: 0;
    }

    .md-user strong {

        display: block;

        color: #f3f1ec;

        font-size: 11px;

    }

    .md-user small {

        display: block;

        margin-top: 3px;

        color: var(--m-muted-2);

        font-size: 8px;

    }

    .md-self {

        margin-top: 4px !important;

        color: var(--m-green) !important;

        font-weight: 900;

    }

    .md-badge {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 7px 9px;

        border: 1px solid var(--m-line);

        border-radius: 999px;

        background: rgba(255,255,255,.025);

        color: #b9bdc2;

        font-size: 8px;

        font-weight: 900;

        white-space: nowrap;

    }

    .md-badge.admin {

        border-color: rgba(143,182,236,.16);

        background: rgba(143,182,236,.07);

        color: var(--m-blue);

    }

    .md-badge.verified {

        border-color: rgba(101,213,154,.16);

        background: rgba(101,213,154,.06);

        color: #99e7ba;

    }

    .md-badge.pending {

        border-color: rgba(241,201,131,.16);

        background: rgba(241,201,131,.06);

        color: #edc47d;

    }



    /* ========================================================= */

    /* LOGIN PROVIDER                                            */

    /* ========================================================= */

    .md-provider {

        display: inline-flex;

        align-items: center;

        gap: 8px;

        min-height: 34px;

        padding: 6px 10px;

        border: 1px solid var(--m-line);

        border-radius: 999px;

        background: rgba(255,255,255,.025);

        color: #c9c7c1;

        font-size: 8px;

        font-weight: 900;

        white-space: nowrap;

    }

    .md-provider-icon {

        width: 20px;

        height: 20px;

        flex: 0 0 20px;

        display: grid;

        place-items: center;

        border-radius: 50%;

        overflow: hidden;

        font-size: 10px;

        font-weight: 900;

    }

    .md-provider-icon svg {

        width: 14px;

        height: 14px;

        display: block;

    }

    .md-provider.google .md-provider-icon {

        background: #ffffff;

    }

    .md-provider.github .md-provider-icon {

        background: #f4f4f4;

        color: #111111;

    }

    .md-provider.facebook .md-provider-icon {

        background: #1877f2;

        color: #ffffff;

    }

    .md-provider.email_code {

        border-color: rgba(215,164,95,.18);

        background: rgba(215,164,95,.055);

        color: var(--m-gold-light);

    }

    .md-provider.email_code .md-provider-icon {

        border: 1px solid rgba(215,164,95,.22);

        background: rgba(215,164,95,.09);

        color: var(--m-gold-light);

    }

    .md-provider.magic_link {
        border-color: rgba(177,132,255,.18);
        background: rgba(177,132,255,.06);
        color: #c9adff;
    }

    .md-provider.magic_link .md-provider-icon {
        border: 1px solid rgba(177,132,255,.20);
        background: rgba(177,132,255,.09);
        color: #c9adff;
    }

    .md-provider.password {

        border-color: rgba(143,182,236,.15);

        background: rgba(143,182,236,.055);

        color: var(--m-blue);

    }

    .md-provider.password .md-provider-icon {

        border: 1px solid rgba(143,182,236,.18);

        background: rgba(143,182,236,.08);

        color: var(--m-blue);

    }

    .md-current-provider {

        margin-top: 12px;

    }

    .md-row-actions {

        display: flex;

        gap: 7px;

        flex-wrap: wrap;

    }

    .md-row-actions .md-btn {

        min-height: 34px;

        padding: 0 11px;

        font-size: 8px;

    }

    .md-self-lock {

        display: inline-flex;

        align-items: center;

        min-height: 34px;

        padding: 0 10px;

        border: 1px solid var(--m-line);

        border-radius: 999px;

        color: var(--m-muted);

        background: rgba(255,255,255,.018);

        font-size: 8px;

        font-weight: 800;

    }

    .md-empty {

        padding: 48px 20px;

        text-align: center;

    }

    .md-empty-mark {

        width: 50px;

        height: 50px;

        margin: 0 auto 14px;

        display: grid;

        place-items: center;

        border: 1px solid rgba(215,164,95,.16);

        border-radius: 16px;

        background: rgba(215,164,95,.05);

        color: var(--m-gold-light);

        font-weight: 900;

    }

    .md-empty h3 {

        margin: 0;

        color: #eeeae3;

        font-size: 18px;

    }

    .md-empty p {

        max-width: 430px;

        margin: 7px auto 18px;

        color: var(--m-muted);

        font-size: 10px;

        line-height: 1.7;

    }

    .md-table-footer {

        margin-top: 16px;

        display: flex;

        justify-content: space-between;

        gap: 12px;

        align-items: center;

        flex-wrap: wrap;

    }

    .md-visible-count {

        color: var(--m-muted-2);

        font-size: 9px;

    }

    /* ========================================================= */

    /* ADMIN INFO                                                 */

    /* ========================================================= */

    .md-admin-grid {

        display: grid;

        grid-template-columns: repeat(4, minmax(0, 1fr));

        gap: 14px;

    }

    .md-info-card {

        padding: 18px;

        border: 1px solid var(--m-line);

        border-radius: 17px;

        background: rgba(255,255,255,.02);

    }

    .md-info-card small {

        display: block;

        color: var(--m-gold-dark);

        font-size: 8px;

        font-weight: 900;

        letter-spacing: .12em;

        text-transform: uppercase;

    }

    .md-info-card strong {

        display: block;

        margin-top: 8px;

        color: #f0eee9;

        font-size: 13px;

        line-height: 1.5;

        word-break: break-word;

    }

    .md-info-card p {

        margin: 6px 0 0;

        color: var(--m-muted);

        font-size: 10px;

        line-height: 1.65;

    }

    /* ========================================================= */

    /* NO SEARCH RESULTS                                          */

    /* ========================================================= */

    .md-no-results {

        display: none;

        margin-top: 14px;

        padding: 20px;

        border: 1px dashed var(--m-line-strong);

        border-radius: 14px;

        color: var(--m-muted);

        background: rgba(215,164,95,.025);

        font-size: 10px;

        line-height: 1.7;

        text-align: center;

    }

    /* ========================================================= */

    /* RESPONSIVE                                                 */

    /* ========================================================= */

    @media (max-width: 1180px) {

        .md-stats,

        .md-actions-grid {

            grid-template-columns: repeat(2, minmax(0, 1fr));

        }

    }

    @media (max-width: 900px) {

        .md-hero-grid,

        .md-admin-grid {

            grid-template-columns: 1fr;

        }

        .md-table-tools {

            grid-template-columns: 1fr;

        }

    }

    @media (max-width: 680px) {

        .md-hero,

        .md-panel {

            padding: 20px;

            border-radius: 20px;

        }

        .md-stats,

        .md-actions-grid {

            grid-template-columns: 1fr;

        }

        .md-section-head,

        .md-table-footer {

            align-items: stretch;

            flex-direction: column;

        }

        .md-hero-actions {

            flex-direction: column;

        }

        .md-hero-actions .md-btn,

        .md-section-head > .md-btn {

            width: 100%;

        }

    }

</style>





@php
    $profilePhotoUsers = $users
        ->filter(fn ($dashboardUser) => $dashboardUser->hasProfilePhoto())
        ->count();
@endphp

<div class="mashal-dashboard">

    {{-- ========================================================= --}}

    {{-- HERO                                                       --}}

    {{-- ========================================================= --}}

    <section class="md-hero">

        <div class="md-hero-grid">

            <div>

                <span class="md-eyebrow">

                    Mashal Control Center

                </span>

                <h1>

                    Automotive beheer,

                    <span>professioneel geregeld.</span>

                </h1>

                <p class="md-hero-copy">

                    Beheer gebruikers, verificaties en administratorrechten vanuit één centraal dashboard.

                    De bestaande Mashal-catalogus en website zijn direct bereikbaar vanuit deze omgeving.

                </p>

                <div class="md-hero-actions">

                    <a

                        href="{{ route('users.create') }}"

                        class="md-btn"

                    >

                        + Nieuwe gebruiker

                    </a>

                    <a

                        href="{{ route('users.index') }}"

                        class="md-btn secondary"

                    >

                        Gebruikersbeheer

                    </a>

                    <a

                        href="{{ route('home') }}"

                        class="md-btn secondary"

                    >

                        Website bekijken

                    </a>

                </div>

            </div>





            <aside class="md-hero-admin">

                <div class="md-hero-admin-profile">
                    <div class="md-hero-admin-avatar">
                        @if (auth()->user()->avatarUrl())
                            <img
                                src="{{ auth()->user()->avatarUrl() }}"
                                alt="Profielfoto van {{ auth()->user()->name }}"
                            >
                        @else
                            {{ auth()->user()->initials() }}
                        @endif
                    </div>

                    <div class="md-hero-admin-identity">
                        <div class="md-hero-admin-label">
                            Huidige administrator
                        </div>

                        <div class="md-hero-admin-name">
                            {{ auth()->user()->name }}
                        </div>

                        <div class="md-hero-admin-email">
                            {{ auth()->user()->email }}
                        </div>
                    </div>
                </div>

                <span class="md-admin-badge">

                    Administrator actief

                </span>

                <div class="md-current-provider">

                    @php

                        $currentProvider = auth()->user()->loginProvider();

                    @endphp

                    <span class="md-provider {{ $currentProvider }}">

                        <span class="md-provider-icon" aria-hidden="true">

                            @switch($currentProvider)

                                @case('google')

                                    <svg viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">

                                        <path fill="#4285F4" d="M17.64 9.205c0-.638-.057-1.252-.164-1.841H9v3.483h4.844a4.14 4.14 0 0 1-1.797 2.715v2.258h2.909c1.703-1.568 2.684-3.878 2.684-6.615z"/>

                                        <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.909-2.258c-.806.54-1.835.859-3.047.859-2.344 0-4.328-1.585-5.037-3.715H.955v2.332A9 9 0 0 0 9 18z"/>

                                        <path fill="#FBBC05" d="M3.963 10.706A5.41 5.41 0 0 1 3.682 9c0-.592.102-1.168.281-1.706V4.962H.955A9 9 0 0 0 0 9c0 1.453.347 2.828.955 4.038l3.008-2.332z"/>

                                        <path fill="#EA4335" d="M9 3.579c1.321 0 2.507.454 3.44 1.346l2.581-2.581C13.463.892 11.426 0 9 0A9 9 0 0 0 .955 4.962l3.008 2.332C4.672 5.164 6.656 3.579 9 3.579z"/>

                                    </svg>

                                    @break

                                @case('github')

                                    <svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">

                                        <path d="M12 .7a11.5 11.5 0 0 0-3.64 22.41c.58.11.79-.25.79-.56v-2.2c-3.22.7-3.9-1.37-3.9-1.37-.52-1.34-1.29-1.69-1.29-1.69-1.05-.72.08-.71.08-.71 1.17.08 1.78 1.2 1.78 1.2 1.04 1.78 2.72 1.27 3.38.97.1-.75.41-1.27.74-1.56-2.57-.29-5.27-1.29-5.27-5.68 0-1.25.45-2.28 1.19-3.08-.12-.29-.52-1.47.11-3.05 0 0 .97-.31 3.16 1.18A10.97 10.97 0 0 1 12 6.17c.98 0 1.96.13 2.87.39 2.19-1.49 3.16-1.18 3.16-1.18.63 1.58.23 2.76.11 3.05.74.8 1.19 1.83 1.19 3.08 0 4.41-2.71 5.38-5.29 5.67.42.36.79 1.07.79 2.16v3.21c0 .31.21.67.8.56A11.5 11.5 0 0 0 12 .7Z"/>

                                    </svg>

                                    @break

                                @case('facebook')

                                    <svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">

                                        <path d="M13.6 22v-9h3l.5-3.5h-3.5V7.3c0-1 .3-1.7 1.8-1.7h1.9V2.5c-.3 0-1.5-.1-2.8-.1-2.8 0-4.7 1.7-4.7 4.8v2.3H7v3.5h2.8v9h3.8Z"/>

                                    </svg>

                                    @break

                                @case('email_code')

                                    ✉

                                    @break

                                @case('magic_link')

                                    ↗

                                    @break

                                @default

                                    🔒

                            @endswitch

                        </span>

                        {{ auth()->user()->loginProviderLabel() }}

                    </span>

                </div>

            </aside>

        </div>

    </section>





    {{-- ========================================================= --}}

    {{-- STATISTICS                                                 --}}

    {{-- ========================================================= --}}

    <section class="md-stats">

        <article class="md-stat-card">

            <div class="md-stat-top">

                <span class="md-stat-label">

                    Gebruikers

                </span>

                <span class="md-stat-icon">

                    U

                </span>

            </div>

            <div class="md-stat-number">

                {{ $totalUsers ?? $users->count() }}

            </div>

            <div class="md-stat-foot">

                Totaal geregistreerde accounts

            </div>

        </article>





        <article class="md-stat-card">

            <div class="md-stat-top">

                <span class="md-stat-label">

                    Geverifieerd

                </span>

                <span class="md-stat-icon">

                    ✓

                </span>

            </div>

            <div class="md-stat-number">

                {{ $verifiedUsers ?? $users->whereNotNull('email_verified_at')->count() }}

            </div>

            <div class="md-stat-foot">

                Accounts met bevestigd e-mailadres

            </div>

        </article>





        <article class="md-stat-card">

            <div class="md-stat-top">

                <span class="md-stat-label">

                    Administrators

                </span>

                <span class="md-stat-icon">

                    A

                </span>

            </div>

            <div class="md-stat-number">

                {{ $adminUsers ?? $users->where('is_admin', true)->count() }}

            </div>

            <div class="md-stat-foot">

                Accounts met beheerrechten

            </div>

        </article>





        <article class="md-stat-card">

            <div class="md-stat-top">

                <span class="md-stat-label">

                    Bestellingen

                </span>

                <span class="md-stat-icon">

                    O

                </span>

            </div>

            <div class="md-stat-number">

                {{ $totalOrders ?? 0 }}

            </div>

            <div class="md-stat-foot">

                Totaal geplaatste bestellingen

            </div>

        </article>

        <article class="md-stat-card">

            <div class="md-stat-top">

                <span class="md-stat-label">
                    Profielfoto's
                </span>

                <span class="md-stat-icon">
                    P
                </span>

            </div>

            <div class="md-stat-number">
                {{ $profilePhotoUsers }}
            </div>

            <div class="md-stat-foot">
                Accounts met een eigen geüploade profielfoto
            </div>

        </article>

    </section>





    {{-- ========================================================= --}}

    {{-- QUICK ACTIONS                                               --}}

    {{-- ========================================================= --}}

    <section class="md-panel">

        <div class="md-section-head">

            <div>

                <span class="md-section-kicker">

                    Quick actions

                </span>

                <h2>

                    Snel beheren

                </h2>

                <p>

                    Open direct de onderdelen die je het vaakst nodig hebt binnen Mashal.

                </p>

            </div>

        </div>





        <div class="md-actions-grid">

            <article class="md-action-card">

                <span class="md-action-icon">

                    U

                </span>

                <h3>

                    Gebruikers beheren

                </h3>

                <p>

                    Bekijk bestaande accounts, wijzig accountgegevens

                    en beheer verificatie- en administratorstatussen.

                </p>

                <a

                    class="md-btn secondary"

                    href="{{ route('users.index') }}"

                >

                    Naar gebruikers

                </a>

            </article>





            <article class="md-action-card">

                <span class="md-action-icon">

                    +

                </span>

                <h3>

                    Nieuwe gebruiker

                </h3>

                <p>

                    Maak handmatig een nieuw gebruikers-

                    of administratoraccount aan.

                </p>

                <a

                    class="md-btn"

                    href="{{ route('users.create') }}"

                >

                    Gebruiker toevoegen

                </a>

            </article>





            <article class="md-action-card">

                <span class="md-action-icon">

                    C

                </span>

                <h3>

                    Catalogus bekijken

                </h3>

                <p>

                    Open de actuele Mashal Automotive-collectie

                    zoals deze op de website beschikbaar is.

                </p>

                <a

                    class="md-btn secondary"

                    href="{{ route('catalog') }}"

                >

                    Naar catalogus

                </a>

            </article>





            <article class="md-action-card">

                <span class="md-action-icon">

                    ↗

                </span>

                <h3>

                    Website openen

                </h3>

                <p>

                    Bekijk de publieke Mashal-website

                    zoals bezoekers en klanten die ervaren.

                </p>

                <a

                    class="md-btn secondary"

                    href="{{ route('home') }}"

                >

                    Naar website

                </a>

            </article>

        </div>

    </section>





    {{-- ========================================================= --}}

    {{-- USERS OVERVIEW                                              --}}

    {{-- ========================================================= --}}

    <section class="md-panel">

        <div class="md-section-head">

            <div>

                <span class="md-section-kicker">

                    User management

                </span>

                <h2>

                    Gebruikersoverzicht

                </h2>

                <p>

                    Controleer accounts, rollen, verificatiestatus

                    en registratiedatum vanuit één overzicht.

                </p>

            </div>





            <a

                class="md-btn"

                href="{{ route('users.create') }}"

            >

                + Gebruiker toevoegen

            </a>

        </div>





        @if ($users->isNotEmpty())

            <div class="md-table-tools">

                <div class="md-search">

                    <input

                        id="dashboardUserSearch"

                        type="search"

                        placeholder="Zoek op naam, e-mailadres, ID of loginmethode..."

                        autocomplete="off"

                    >

                </div>





                <div class="md-filter-bar">

                    <button

                        class="md-filter active"

                        type="button"

                        data-filter="all"

                    >

                        Alle

                    </button>

                    <button

                        class="md-filter"

                        type="button"

                        data-filter="verified"

                    >

                        Geverifieerd

                    </button>

                    <button

                        class="md-filter"

                        type="button"

                        data-filter="pending"

                    >

                        Niet geverifieerd

                    </button>

                    <button

                        class="md-filter"

                        type="button"

                        data-filter="admin"

                    >

                        Administrators

                    </button>

                    <button class="md-filter" type="button" data-filter="google">

                        Google

                    </button>

                    <button class="md-filter" type="button" data-filter="github">

                        GitHub

                    </button>

                    <button class="md-filter" type="button" data-filter="facebook">

                        Facebook

                    </button>

                    <button class="md-filter" type="button" data-filter="email_code">

                        E-mailcode

                    </button>

                    <button class="md-filter" type="button" data-filter="magic_link">

                        Magic link

                    </button>

                    <button class="md-filter" type="button" data-filter="password">

                        Wachtwoord

                    </button>

                    <button class="md-filter" type="button" data-filter="photo">

                        Met profielfoto

                    </button>

                </div>

            </div>

        @endif





        <div class="md-table-wrap">

            <table class="md-table">

                <thead>

                    <tr>

                        <th>Gebruiker</th>

                        <th>E-mailadres</th>

                        <th>Login via</th>

                        <th>Rol</th>

                        <th>Verificatie</th>

                        <th>Toegevoegd</th>

                        <th>Acties</th>

                    </tr>

                </thead>





                <tbody id="dashboardUsersBody">

                    @forelse ($users as $user)

                        <tr

                            class="dashboard-user-row"

                            data-name="{{ strtolower($user->name) }}"

                            data-email="{{ strtolower($user->email) }}"

                            data-id="{{ $user->id }}"

                            data-admin="{{ $user->is_admin ? '1' : '0' }}"

                            data-verified="{{ $user->email_verified_at ? '1' : '0' }}"

                            data-provider="{{ $user->loginProvider() }}"
                            data-photo="{{ $user->hasProfilePhoto() ? '1' : '0' }}"

                        >

                            {{-- USER --}}

                            <td>

                                <div class="md-user">

                                    @if ($user->avatarUrl())
                                        <span class="md-avatar has-image">
                                            <img
                                                src="{{ $user->avatarUrl() }}"
                                                alt="Profielfoto van {{ $user->name }}"
                                                loading="lazy"
                                            >
                                        </span>
                                    @else
                                        <span class="md-avatar">
                                            {{ $user->initials() }}
                                        </span>
                                    @endif

                                    <div class="md-user-meta">

                                        <strong>

                                            {{ $user->name }}

                                        </strong>

                                        <small>

                                            ID #{{ $user->id }}

                                        </small>

                                        @if ($user->hasProfilePhoto())
                                            <span class="md-photo-source custom">
                                                Eigen profielfoto
                                            </span>
                                        @elseif ($user->socialAvatar())
                                            <span class="md-photo-source social">
                                                Social avatar
                                            </span>
                                        @else
                                            <span class="md-photo-source">
                                                Initialen
                                            </span>
                                        @endif





                                        @if (auth()->id() === $user->id)

                                            <small class="md-self">

                                                Dit ben jij

                                            </small>

                                        @endif

                                    </div>

                                </div>

                            </td>





                            {{-- EMAIL --}}

                            <td

                                style="

                                    word-break: break-word;

                                "

                            >

                                {{ $user->email }}

                            </td>

                            {{-- LOGIN PROVIDER --}}

                            <td>

                                @php

                                    $provider = $user->loginProvider();

                                @endphp

                                <span

                                    class="md-provider {{ $provider }}"

                                    title="Laatste login via {{ $user->loginProviderLabel() }}"

                                >

                                    <span class="md-provider-icon" aria-hidden="true">

                                        @switch($provider)

                                            @case('google')

                                                <svg viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">

                                                    <path fill="#4285F4" d="M17.64 9.205c0-.638-.057-1.252-.164-1.841H9v3.483h4.844a4.14 4.14 0 0 1-1.797 2.715v2.258h2.909c1.703-1.568 2.684-3.878 2.684-6.615z"/>

                                                    <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.909-2.258c-.806.54-1.835.859-3.047.859-2.344 0-4.328-1.585-5.037-3.715H.955v2.332A9 9 0 0 0 9 18z"/>

                                                    <path fill="#FBBC05" d="M3.963 10.706A5.41 5.41 0 0 1 3.682 9c0-.592.102-1.168.281-1.706V4.962H.955A9 9 0 0 0 0 9c0 1.453.347 2.828.955 4.038l3.008-2.332z"/>

                                                    <path fill="#EA4335" d="M9 3.579c1.321 0 2.507.454 3.44 1.346l2.581-2.581C13.463.892 11.426 0 9 0A9 9 0 0 0 .955 4.962l3.008 2.332C4.672 5.164 6.656 3.579 9 3.579z"/>

                                                </svg>

                                                @break

                                            @case('github')

                                                <svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">

                                                    <path d="M12 .7a11.5 11.5 0 0 0-3.64 22.41c.58.11.79-.25.79-.56v-2.2c-3.22.7-3.9-1.37-3.9-1.37-.52-1.34-1.29-1.69-1.29-1.69-1.05-.72.08-.71.08-.71 1.17.08 1.78 1.2 1.78 1.2 1.04 1.78 2.72 1.27 3.38.97.1-.75.41-1.27.74-1.56-2.57-.29-5.27-1.29-5.27-5.68 0-1.25.45-2.28 1.19-3.08-.12-.29-.52-1.47.11-3.05 0 0 .97-.31 3.16 1.18A10.97 10.97 0 0 1 12 6.17c.98 0 1.96.13 2.87.39 2.19-1.49 3.16-1.18 3.16-1.18.63 1.58.23 2.76.11 3.05.74.8 1.19 1.83 1.19 3.08 0 4.41-2.71 5.38-5.29 5.67.42.36.79 1.07.79 2.16v3.21c0 .31.21.67.8.56A11.5 11.5 0 0 0 12 .7Z"/>

                                                </svg>

                                                @break

                                            @case('facebook')

                                                <svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">

                                                    <path d="M13.6 22v-9h3l.5-3.5h-3.5V7.3c0-1 .3-1.7 1.8-1.7h1.9V2.5c-.3 0-1.5-.1-2.8-.1-2.8 0-4.7 1.7-4.7 4.8v2.3H7v3.5h2.8v9h3.8Z"/>

                                                </svg>

                                                @break

                                            @case('email_code')

                                                ✉

                                                @break

                                            @case('magic_link')

                                                ↗

                                                @break

                                            @default

                                                🔒

                                        @endswitch

                                    </span>

                                    {{ $user->loginProviderLabel() }}

                                </span>

                            </td>





                            {{-- ROLE --}}

                            <td>

                                @if ($user->is_admin)

                                    <span class="md-badge admin">

                                        Administrator

                                    </span>

                                @else

                                    <span class="md-badge">

                                        Gebruiker

                                    </span>

                                @endif

                            </td>





                            {{-- VERIFICATION --}}

                            <td>

                                @if ($user->email_verified_at)

                                    <span class="md-badge verified">

                                        ✓ Geverifieerd

                                    </span>

                                @else

                                    <span class="md-badge pending">

                                        Niet geverifieerd

                                    </span>

                                @endif

                            </td>





                            {{-- CREATED --}}

                            <td>

                                <strong

                                    style="

                                        display: block;

                                        color: #e5e2dc;

                                        font-size: 10px;

                                    "

                                >

                                    {{ $user->created_at->format('d-m-Y') }}

                                </strong>

                                <small

                                    style="

                                        display: block;

                                        margin-top: 4px;

                                        color: var(--m-muted-2);

                                        font-size: 8px;

                                    "

                                >

                                    {{ $user->created_at->format('H:i') }}

                                </small>

                            </td>





                            {{-- ACTIONS --}}

                            <td>

                                <div class="md-row-actions">

                                    <a

                                        class="md-btn secondary"

                                        href="{{ route('users.edit', $user) }}"

                                    >

                                        Wijzigen

                                    </a>





                                    @if (auth()->id() !== $user->id)

                                        <form

                                            method="POST"

                                            action="{{ route('users.destroy', $user) }}"

                                            style="margin: 0;"

                                            onsubmit="return confirm('Weet je zeker dat je {{ $user->name }} definitief wilt verwijderen?');"

                                        >

                                            @csrf

                                            @method('DELETE')

                                            <button

                                                class="md-btn danger"

                                                type="submit"

                                            >

                                                Verwijderen

                                            </button>

                                        </form>

                                    @else

                                        <span class="md-self-lock">

                                            Eigen account

                                        </span>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="7">

                                <div class="md-empty">

                                    <div class="md-empty-mark">

                                        U

                                    </div>

                                    <h3>

                                        Nog geen gebruikers

                                    </h3>

                                    <p>

                                        Er zijn momenteel nog geen gebruikers geregistreerd.

                                        Maak het eerste account aan om te beginnen.

                                    </p>

                                    <a

                                        class="md-btn"

                                        href="{{ route('users.create') }}"

                                    >

                                        + Eerste gebruiker aanmaken

                                    </a>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>





        @if ($users->isNotEmpty())

            <div

                id="dashboardNoResults"

                class="md-no-results"

            >

                Geen gebruikers gevonden voor deze zoekopdracht of filter.

            </div>





            <div class="md-table-footer">

                <span

                    id="dashboardVisibleCount"

                    class="md-visible-count"

                >

                    {{ $users->count() }} gebruikers zichtbaar

                </span>





                <a

                    class="md-btn secondary"

                    href="{{ route('users.index') }}"

                >

                    Volledig gebruikersbeheer

                </a>

            </div>

        @endif

    </section>





    {{-- ========================================================= --}}

    {{-- ADMINISTRATOR INFORMATION                                  --}}

    {{-- ========================================================= --}}

    <section class="md-panel">

        <div class="md-section-head">

            <div>

                <span class="md-section-kicker">

                    Administrator profile

                </span>

                <h2>

                    Jouw beheerderssessie

                </h2>

                <p>

                    Controleer met welk account je bent ingelogd

                    voordat je gevoelige gebruikerswijzigingen uitvoert.

                </p>

            </div>

        </div>





        <div class="md-admin-grid">

            <article class="md-info-card">

                <small>

                    Ingelogd als

                </small>

                <strong>

                    {{ auth()->user()->name }}

                </strong>

                <p>

                    Actieve beheerder van deze sessie.

                </p>

            </article>





            <article class="md-info-card">

                <small>

                    E-mailadres

                </small>

                <strong>

                    {{ auth()->user()->email }}

                </strong>

                <p>

                    Gekoppeld aan je administratoraccount.

                </p>

            </article>





            <article class="md-info-card">

                <small>

                    Rechten

                </small>

                <strong>

                    Volledige administratorrechten

                </strong>

                <p>

                    Je kunt gebruikers bekijken, wijzigen, aanmaken en verwijderen.

                </p>

            </article>

            <article class="md-info-card">

                <small>

                    Ingelogd via

                </small>

                <strong>

                    {{ auth()->user()->loginProviderLabel() }}

                </strong>

                <p>

                    Laatst gebruikte authenticatiemethode voor deze beheerder.

                </p>

            </article>

        </div>

    </section>

</div>





<script>

    document.addEventListener('DOMContentLoaded', function () {

        const searchInput = document.getElementById('dashboardUserSearch');

        const rows = Array.from(document.querySelectorAll('.dashboard-user-row'));

        const filters = Array.from(document.querySelectorAll('.md-filter'));

        const noResults = document.getElementById('dashboardNoResults');

        const visibleCount = document.getElementById('dashboardVisibleCount');

        if (!rows.length) {

            return;

        }

        let activeFilter = 'all';

        function applyDashboardUserFilters() {

            const query = (searchInput?.value || '').trim().toLowerCase();

            let visible = 0;

            rows.forEach(function (row) {

                const name = row.dataset.name || '';

                const email = row.dataset.email || '';

                const id = row.dataset.id || '';

                const isAdmin = row.dataset.admin === '1';

                const isVerified = row.dataset.verified === '1';

                const provider = row.dataset.provider || 'password';
                const hasPhoto = row.dataset.photo === '1';

                const matchesSearch =

                    !query ||

                    name.includes(query) ||

                    email.includes(query) ||

                    id.includes(query) ||

                    provider.includes(query);

                let matchesFilter = true;

                if (activeFilter === 'verified') {

                    matchesFilter = isVerified;

                }

                if (activeFilter === 'pending') {

                    matchesFilter = !isVerified;

                }

                if (activeFilter === 'admin') {

                    matchesFilter = isAdmin;

                }

                if (

                    ['google', 'github', 'facebook', 'email_code', 'magic_link', 'password']

                        .includes(activeFilter)

                ) {

                    matchesFilter = provider === activeFilter;

                }

                if (activeFilter === 'photo') {

                    matchesFilter = hasPhoto;

                }

                const shouldShow = matchesSearch && matchesFilter;

                row.style.display = shouldShow ? '' : 'none';

                if (shouldShow) {

                    visible++;

                }

            });

            if (visibleCount) {

                visibleCount.textContent =

                    visible + (visible === 1 ? ' gebruiker zichtbaar' : ' gebruikers zichtbaar');

            }

            if (noResults) {

                noResults.style.display = visible === 0 ? 'block' : 'none';

            }

        }

        if (searchInput) {

            searchInput.addEventListener('input', applyDashboardUserFilters);

        }

        filters.forEach(function (button) {

            button.addEventListener('click', function () {

                activeFilter = button.dataset.filter || 'all';

                filters.forEach(function (item) {

                    item.classList.toggle('active', item === button);

                });

                applyDashboardUserFilters();

            });

        });

    });

</script>

@endsection