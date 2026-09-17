@extends('layouts.admin-layout')

@section('title', 'Mashal Admin | Gebruikers')

@section('page-title', 'Gebruikersbeheer')

@push('styles')

<style>

    .users-page {

        position: relative;

        overflow: hidden;

        padding: 8px 0 36px;

    }

    .users-page::before {

        content: "USERS";

        position: absolute;

        right: -30px;

        top: 18px;

        color: rgba(255,255,255,.012);

        font-size: clamp(110px, 14vw, 230px);

        font-weight: 950;

        line-height: .8;

        letter-spacing: -.06em;

        pointer-events: none;

        user-select: none;

    }

    /* ========================================================= */

    /* HERO                                                       */

    /* ========================================================= */

    .users-hero {

        position: relative;

        z-index: 2;

        margin-bottom: 24px;

        padding: 30px;

        display: grid;

        grid-template-columns: minmax(0,1fr) auto;

        gap: 28px;

        align-items: end;

        border: 1px solid rgba(255,255,255,.08);

        border-radius: 24px;

        background:

            radial-gradient(circle at 88% 10%, rgba(215,164,95,.11), transparent 18rem),

            linear-gradient(145deg, rgba(255,255,255,.045), rgba(255,255,255,.015));

        box-shadow: 0 18px 50px rgba(0,0,0,.18);

        overflow: hidden;

    }

    .users-hero-kicker {

        display: inline-flex;

        align-items: center;

        gap: 9px;

        margin-bottom: 11px;

        color: #d6a45f;

        font-size: 8px;

        font-weight: 900;

        letter-spacing: .19em;

        text-transform: uppercase;

    }

    .users-hero-kicker::before {

        content: "";

        width: 28px;

        height: 1px;

        background: #d6a45f;

    }

    .users-hero h2 {

        margin: 0;

        color: #fff;

        font-size: clamp(30px, 3.6vw, 46px);

        line-height: 1;

        letter-spacing: -.05em;

    }

    .users-hero p {

        max-width: 720px;

        margin: 11px 0 0;

        color: #7d838a;

        font-size: 12px;

        line-height: 1.75;

    }

    .users-create-btn {

        min-height: 44px;

        padding: 0 17px;

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 8px;

        border: 0;

        border-radius: 999px;

        background: linear-gradient(135deg, #f1cc8b, #ca914c);

        color: #14100b;

        text-decoration: none;

        font-size: 9px;

        font-weight: 950;

        letter-spacing: .04em;

        box-shadow: 0 14px 34px rgba(215,164,95,.18);

        transition: transform .2s ease, box-shadow .2s ease;

    }

    .users-create-btn:hover {

        transform: translateY(-2px);

        box-shadow: 0 22px 48px rgba(215,164,95,.28);

    }

    /* ========================================================= */

    /* STATS                                                      */

    /* ========================================================= */

    .users-stats {

        position: relative;

        z-index: 2;

        margin-bottom: 22px;

        display: grid;

        grid-template-columns: repeat(5, minmax(0,1fr));

        gap: 14px;

    }

    .users-stat-card {

        padding: 20px;

        border: 1px solid rgba(255,255,255,.08);

        border-radius: 18px;

        background:

            linear-gradient(145deg, rgba(255,255,255,.04), rgba(255,255,255,.014));

        box-shadow: 0 14px 36px rgba(0,0,0,.14);

    }

    .users-stat-label {

        color: #6d737a;

        font-size: 8px;

        font-weight: 900;

        letter-spacing: .14em;

        text-transform: uppercase;

    }

    .users-stat-value {

        margin-top: 9px;

        color: #fff;

        font-size: 30px;

        line-height: 1;

        letter-spacing: -.05em;

        font-weight: 950;

    }

    .users-stat-foot {

        margin-top: 8px;

        color: #62686f;

        font-size: 9px;

        line-height: 1.6;

    }

    /* ========================================================= */

    /* TOOLBAR                                                    */

    /* ========================================================= */

    .users-toolbar {

        position: relative;

        z-index: 2;

        margin-bottom: 16px;

        padding: 14px;

        display: grid;

        grid-template-columns: minmax(0,1fr) auto;

        gap: 12px;

        align-items: center;

        border: 1px solid rgba(255,255,255,.07);

        border-radius: 17px;

        background: rgba(255,255,255,.022);

    }

    .users-search-wrap {

        position: relative;

    }

    .users-search {

        width: 100%;

        height: 44px;

        padding: 0 42px 0 14px;

        border: 1px solid rgba(255,255,255,.08);

        border-radius: 12px;

        outline: none;

        background: rgba(255,255,255,.025);

        color: #fff;

        font-size: 11px;

    }

    .users-search:focus {

        border-color: rgba(215,164,95,.35);

        box-shadow: 0 0 0 4px rgba(215,164,95,.05);

    }

    .users-search::placeholder {

        color: #545a61;

    }

    .users-search-icon {

        position: absolute;

        right: 14px;

        top: 50%;

        transform: translateY(-50%);

        color: #6b7178;

        font-size: 11px;

    }

    .users-filters {

        display: flex;

        gap: 7px;

        flex-wrap: wrap;

    }

    .users-filter-btn {

        min-height: 38px;

        padding: 0 12px;

        border: 1px solid rgba(255,255,255,.08);

        border-radius: 999px;

        background: rgba(255,255,255,.02);

        color: #858b92;

        font-size: 8px;

        font-weight: 900;

        cursor: pointer;

        letter-spacing: .06em;

        text-transform: uppercase;

        transition: .2s ease;

    }

    .users-filter-btn:hover,

    .users-filter-btn.active {

        border-color: rgba(215,164,95,.22);

        background: rgba(215,164,95,.06);

        color: #e4b773;

    }

    /* ========================================================= */

    /* TABLE                                                      */

    /* ========================================================= */

    .users-table-card {

        position: relative;

        z-index: 2;

        overflow: hidden;

        border: 1px solid rgba(255,255,255,.08);

        border-radius: 22px;

        background:

            linear-gradient(145deg, rgba(255,255,255,.04), rgba(255,255,255,.014));

        box-shadow: 0 16px 44px rgba(0,0,0,.17);

    }

    .users-table-scroll {

        overflow-x: auto;

    }

    .users-table {

        width: 100%;

        min-width: 1140px;

        border-collapse: collapse;

    }

    .users-table thead th {

        padding: 15px 18px;

        border-bottom: 1px solid rgba(255,255,255,.07);

        color: #676d74;

        font-size: 8px;

        font-weight: 900;

        letter-spacing: .14em;

        text-align: left;

        text-transform: uppercase;

        background: rgba(255,255,255,.015);

    }

    .users-table tbody td {

        padding: 17px 18px;

        border-bottom: 1px solid rgba(255,255,255,.055);

        color: #bdbdb9;

        font-size: 11px;

        vertical-align: middle;

    }

    .users-table tbody tr {

        transition: background .2s ease;

    }

    .users-table tbody tr:hover {

        background: rgba(215,164,95,.022);

    }

    .users-table tbody tr:last-child td {

        border-bottom: 0;

    }

    .user-cell {

        display: flex;

        align-items: center;

        gap: 12px;

    }

    .user-avatar {

        flex-shrink: 0;

        width: 42px;

        height: 42px;

        display: grid;

        place-items: center;

        border-radius: 13px;

        background: linear-gradient(145deg, #f0ca86, #b67e3d);

        color: #15110c;

        font-size: 15px;

        font-weight: 950;

    }

    .user-cell-copy strong {

        display: block;

        color: #efede7;

        font-size: 11px;

    }

    .user-cell-copy small {

        display: block;

        margin-top: 3px;

        color: #60666d;

        font-size: 8px;

    }

    .user-self {

        display: inline-block;

        margin-top: 4px;

        color: #9ce7bc !important;

        font-weight: 800;

    }

    .user-email {

        max-width: 240px;

        word-break: break-word;

        color: #aaaead;

    }

    .user-badge {

        display: inline-flex;

        align-items: center;

        gap: 6px;

        padding: 7px 9px;

        border: 1px solid rgba(255,255,255,.08);

        border-radius: 999px;

        background: rgba(255,255,255,.025);

        color: #9ea3a8;

        font-size: 8px;

        font-weight: 900;

        letter-spacing: .05em;

        text-transform: uppercase;

    }

    .user-badge.admin {

        border-color: rgba(113,166,255,.18);

        background: rgba(113,166,255,.055);

        color: #a9c8ff;

    }

    .user-badge.verified {

        border-color: rgba(91,214,149,.18);

        background: rgba(91,214,149,.055);

        color: #9ce7bc;

    }

    .user-badge.pending {

        border-color: rgba(242,198,109,.18);

        background: rgba(242,198,109,.055);

        color: #e9c674;

    }

    .verified-date {

        display: block;

        margin-top: 5px;

        color: #5f656c;

        font-size: 8px;

    }

    .date-main {

        color: #d5d3cd;

        font-weight: 800;

    }

    .date-time {

        display: block;

        margin-top: 3px;

        color: #5f656c;

        font-size: 8px;

    }

    /* ========================================================= */





    /* ========================================================= */

    /* LOGIN PROVIDER                                            */

    /* ========================================================= */

    .user-provider {

        display: inline-flex;

        align-items: center;

        gap: 8px;

        min-height: 34px;

        padding: 6px 10px;

        border: 1px solid rgba(255,255,255,.08);

        border-radius: 999px;

        background: rgba(255,255,255,.025);

        color: #c9c7c1;

        font-size: 8px;

        font-weight: 900;

        white-space: nowrap;

    }

    .user-provider-icon {

        width: 20px;

        height: 20px;

        flex: 0 0 20px;

        display: grid;

        place-items: center;

        overflow: hidden;

        border-radius: 50%;

        font-size: 10px;

        font-weight: 900;

    }

    .user-provider-icon svg {

        width: 14px;

        height: 14px;

        display: block;

    }

    .user-provider.google .user-provider-icon {

        background: #ffffff;

    }

    .user-provider.github .user-provider-icon {

        background: #f5f5f5;

        color: #111111;

    }

    .user-provider.facebook .user-provider-icon {

        background: #1877f2;

        color: #ffffff;

    }

    .user-provider.email_code {

        border-color: rgba(215,164,95,.18);

        background: rgba(215,164,95,.055);

        color: #efc985;

    }

    .user-provider.email_code .user-provider-icon {

        border: 1px solid rgba(215,164,95,.22);

        background: rgba(215,164,95,.08);

        color: #efc985;

    }

    .user-provider.password {

        border-color: rgba(113,166,255,.16);

        background: rgba(113,166,255,.055);

        color: #a9c8ff;

    }

    .user-provider.password .user-provider-icon {

        border: 1px solid rgba(113,166,255,.18);

        background: rgba(113,166,255,.08);

        color: #a9c8ff;

    }

/* ACTIONS                                                    */

    /* ========================================================= */

    .user-actions {

        display: flex;

        align-items: center;

        gap: 7px;

        flex-wrap: wrap;

    }

    .user-action-link,

    .user-action-danger,

    .user-action-self {

        min-height: 34px;

        padding: 0 10px;

        display: inline-flex;

        align-items: center;

        justify-content: center;

        border-radius: 999px;

        font-size: 8px;

        font-weight: 900;

        text-decoration: none;

        white-space: nowrap;

    }

    .user-action-link {

        border: 1px solid rgba(255,255,255,.09);

        background: rgba(255,255,255,.025);

        color: #aaaead;

        transition: .2s ease;

    }

    .user-action-link:hover {

        border-color: rgba(215,164,95,.22);

        background: rgba(215,164,95,.055);

        color: #efc985;

    }

    .user-action-danger {

        border: 1px solid rgba(241,123,123,.18);

        background: rgba(241,123,123,.06);

        color: #f0a0a0;

        cursor: pointer;

        transition: .2s ease;

    }

    .user-action-danger:hover {

        border-color: rgba(241,123,123,.35);

        background: rgba(241,123,123,.11);

    }

    .user-action-self {

        border: 1px solid rgba(255,255,255,.06);

        background: rgba(255,255,255,.018);

        color: #5f656c;

    }

    /* ========================================================= */

    /* EMPTY                                                      */

    /* ========================================================= */

    .users-empty {

        padding: 58px 22px !important;

        text-align: center;

    }

    .users-empty-inner {

        max-width: 440px;

        margin: 0 auto;

    }

    .users-empty-mark {

        width: 56px;

        height: 56px;

        margin: 0 auto 16px;

        display: grid;

        place-items: center;

        border: 1px solid rgba(215,164,95,.16);

        border-radius: 50%;

        background: rgba(215,164,95,.055);

        color: #d9aa68;

        font-weight: 950;

    }

    .users-empty h3 {

        margin: 0 0 8px;

        color: #e4e2dd;

        font-size: 17px;

    }

    .users-empty p {

        margin: 0 0 18px;

        color: #6d737a;

        font-size: 10px;

        line-height: 1.7;

    }

    /* ========================================================= */

    /* ADMIN INFO                                                 */

    /* ========================================================= */

    .users-info {

        position: relative;

        z-index: 2;

        margin-top: 22px;

        padding: 20px;

        display: grid;

        grid-template-columns: auto 1fr;

        gap: 13px;

        border: 1px solid rgba(255,255,255,.07);

        border-radius: 18px;

        background: rgba(255,255,255,.02);

    }

    .users-info-icon {

        width: 34px;

        height: 34px;

        display: grid;

        place-items: center;

        border: 1px solid rgba(215,164,95,.16);

        border-radius: 50%;

        color: #c99b5e;

        font-size: 11px;

    }

    .users-info strong {

        display: block;

        color: #d9d7d2;

        font-size: 11px;

    }

    .users-info p {

        margin: 5px 0 0;

        color: #686e75;

        font-size: 9px;

        line-height: 1.7;

    }

    /* ========================================================= */



    /* ========================================================= */

    /* EXTENDED PROFILE / PHOTO UI                               */

    /* ========================================================= */

    .users-hero-side {

        min-width: 260px;

        padding: 17px;

        border: 1px solid rgba(255,255,255,.08);

        border-radius: 18px;

        background: rgba(255,255,255,.025);

        box-shadow: inset 0 1px 0 rgba(255,255,255,.025);

    }


    .users-hero-profile {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .users-hero-profile-avatar {
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        display: grid;
        place-items: center;
        overflow: hidden;
        border: 1px solid rgba(215,164,95,.20);
        border-radius: 14px;
        background: linear-gradient(145deg, #f0ca86, #b67e3d);
        color: #15110c;
        font-size: 16px;
        font-weight: 950;
    }

    .users-hero-profile-avatar img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
    }

    .users-hero-side-label {

        color: #9a7347;

        font-size: 8px;

        font-weight: 900;

        letter-spacing: .14em;

        text-transform: uppercase;

    }

    .users-hero-side strong {

        display: block;

        margin-top: 7px;

        color: #f0eee9;

        font-size: 14px;

    }

    .users-hero-side span {

        display: block;

        margin-top: 5px;

        color: #6d737a;

        font-size: 9px;

        line-height: 1.55;

    }

    .users-hero-actions {

        margin-top: 14px;

        display: flex;

        gap: 8px;

        flex-wrap: wrap;

    }

    .users-secondary-btn {

        min-height: 38px;

        padding: 0 13px;

        display: inline-flex;

        align-items: center;

        justify-content: center;

        border: 1px solid rgba(255,255,255,.08);

        border-radius: 999px;

        background: rgba(255,255,255,.025);

        color: #aaaead;

        text-decoration: none;

        font-size: 8px;

        font-weight: 900;

        transition: .2s ease;

    }

    .users-secondary-btn:hover {

        border-color: rgba(215,164,95,.22);

        background: rgba(215,164,95,.06);

        color: #efc985;

    }

    .users-stat-card {

        position: relative;

        overflow: hidden;

        transition:

            transform .22s ease,

            border-color .22s ease,

            background .22s ease;

    }

    .users-stat-card:hover {

        transform: translateY(-3px);

        border-color: rgba(215,164,95,.20);

        background: linear-gradient(

            145deg,

            rgba(215,164,95,.045),

            rgba(255,255,255,.016)

        );

    }

    .users-stat-card::after {

        content: "";

        position: absolute;

        right: -26px;

        bottom: -36px;

        width: 92px;

        height: 92px;

        border-radius: 50%;

        background: radial-gradient(

            circle,

            rgba(215,164,95,.08),

            transparent 70%

        );

        pointer-events: none;

    }

    .user-avatar {

        position: relative;

        overflow: hidden;

        box-shadow:

            inset 0 0 0 1px rgba(255,255,255,.025),

            0 10px 24px rgba(0,0,0,.16);

    }

    .user-avatar img {

        width: 100%;

        height: 100%;

        display: block;

        object-fit: cover;

        border-radius: inherit;

    }

    .user-avatar.has-image {

        background: #111419;

        color: transparent;

    }

    .user-photo-source {

        margin-top: 5px;

        display: inline-flex;

        align-items: center;

        gap: 5px;

        color: #6a7077;

        font-size: 7px;

        font-weight: 850;

        letter-spacing: .04em;

        text-transform: uppercase;

    }

    .user-photo-source::before {

        content: "";

        width: 5px;

        height: 5px;

        border-radius: 50%;

        background: #d7a45f;

        box-shadow: 0 0 10px rgba(215,164,95,.35);

    }

    .user-photo-source.custom {

        color: #d7a45f;

    }

    .user-photo-source.social {

        color: #8fb6ec;

    }

    .user-provider.magic_link {

        border-color: rgba(177,132,255,.18);

        background: rgba(177,132,255,.06);

        color: #c9adff;

    }

    .user-provider.magic_link .user-provider-icon {

        border: 1px solid rgba(177,132,255,.20);

        background: rgba(177,132,255,.09);

        color: #c9adff;

    }

    .users-table-footer {

        padding: 14px 16px;

        display: flex;

        justify-content: space-between;

        align-items: center;

        gap: 12px;

        flex-wrap: wrap;

        border-top: 1px solid rgba(255,255,255,.055);

        background: rgba(255,255,255,.012);

    }

    .users-visible-count {

        color: #666c73;

        font-size: 9px;

    }

    .users-no-results {

        display: none;

        padding: 28px 18px;

        text-align: center;

        color: #737980;

        font-size: 10px;

        line-height: 1.7;

        border-top: 1px dashed rgba(215,164,95,.15);

        background: rgba(215,164,95,.02);

    }

/* RESPONSIVE                                                 */

    /* ========================================================= */

    @media (max-width: 1050px) {

        .users-hero {

            grid-template-columns: 1fr;

        }

        .users-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .users-toolbar {

            grid-template-columns: 1fr;

        }

    }

    @media (max-width: 650px) {

        .users-stats {

            grid-template-columns: 1fr;

        }

        .users-hero {

            padding: 22px;

        }

        .users-create-btn,

        .users-secondary-btn {

            width: 100%;

        }

        .users-hero-side {

            min-width: 0;

            width: 100%;

        }

        .users-filters {

            display: grid;

            grid-template-columns: 1fr 1fr;

        }

        .users-filter-btn {

            width: 100%;

        }

    }

</style>

@endpush









@section('content')

@php

    $profilePhotoUsers = $users

        ->filter(fn ($managedUser) => $managedUser->hasProfilePhoto())

        ->count();

    $socialAvatarUsers = $users

        ->filter(

            fn ($managedUser) =>

                ! $managedUser->hasProfilePhoto()

                && filled($managedUser->socialAvatar())

        )

        ->count();

@endphp

<section class="users-page">

    {{-- ========================================================= --}}

    {{-- HERO                                                       --}}

    {{-- ========================================================= --}}

    <div class="users-hero">

        <div>

            <span class="users-hero-kicker">

                Mashal User Management

            </span>

            <h2>

                Gebruikersbeheer

            </h2>

            <p>

                Bekijk en beheer alle geregistreerde Mashal-accounts,

                controleer loginmethodes en verificatiestatussen, pas rollen aan

                en open individuele accounts voor verdere wijzigingen.

            </p>

        </div>









        <div class="users-hero-side">
            <div class="users-hero-profile">
                <div class="users-hero-profile-avatar">
                    @if (auth()->user()->avatarUrl())
                        <img
                            src="{{ auth()->user()->avatarUrl() }}"
                            alt="Profielfoto van {{ auth()->user()->name }}"
                        >
                    @else
                        {{ auth()->user()->initials() }}
                    @endif
                </div>

                <div>
                    <div class="users-hero-side-label">
                        Beheeromgeving
                    </div>

                    <strong>{{ auth()->user()->name }}</strong>

                    <span>
                        Beheer profielen, profielfoto's, loginmethodes,
                        verificatie en rollen vanuit één centrale omgeving.
                    </span>
                </div>
            </div>

            <div class="users-hero-actions">

                <a

                    class="users-create-btn"

                    href="{{ route('users.create') }}"

                >

                    + Nieuwe gebruiker

                </a>

                <a

                    class="users-secondary-btn"

                    href="{{ route('admin.dashboard') }}"

                >

                    Dashboard

                </a>

            </div>

        </div>

    </div>









    {{-- ========================================================= --}}

    {{-- STATISTICS                                                 --}}

    {{-- ========================================================= --}}

    <div class="users-stats">

        <div class="users-stat-card">

            <div class="users-stat-label">

                Totaal gebruikers

            </div>

            <div class="users-stat-value">

                {{ $users->count() }}

            </div>

            <div class="users-stat-foot">

                Alle geregistreerde Mashal-accounts

            </div>

        </div>









        <div class="users-stat-card">

            <div class="users-stat-label">

                Geverifieerd

            </div>

            <div class="users-stat-value">

                {{ $users->whereNotNull('email_verified_at')->count() }}

            </div>

            <div class="users-stat-foot">

                Accounts met bevestigd e-mailadres

            </div>

        </div>









        <div class="users-stat-card">

            <div class="users-stat-label">

                Niet geverifieerd

            </div>

            <div class="users-stat-value">

                {{ $users->whereNull('email_verified_at')->count() }}

            </div>

            <div class="users-stat-foot">

                Accounts waarvoor verificatie nog openstaat

            </div>

        </div>









        <div class="users-stat-card">

            <div class="users-stat-label">

                Administrators

            </div>

            <div class="users-stat-value">

                {{ $users->where('is_admin', true)->count() }}

            </div>

            <div class="users-stat-foot">

                Accounts met toegang tot beheerfuncties

            </div>

        </div>

            <div class="users-stat-card">

            <div class="users-stat-label">

                Profielfoto's

            </div>

            <div class="users-stat-value">

                {{ $profilePhotoUsers }}

            </div>

            <div class="users-stat-foot">

                Accounts met een eigen geüploade profielfoto

            </div>

        </div>

</div>









    {{-- ========================================================= --}}

    {{-- TOOLBAR                                                    --}}

    {{-- ========================================================= --}}

    <div class="users-toolbar">

        <div class="users-search-wrap">

            <input

                class="users-search"

                id="usersSearch"

                type="search"

                placeholder="Zoek op naam, e-mailadres, ID of loginmethode..."

                autocomplete="off"

            >

            <span class="users-search-icon">

                ⌕

            </span>

        </div>









        <div class="users-filters">

            <button

                class="users-filter-btn active"

                type="button"

                data-filter="all"

            >

                Alles

            </button>

            <button

                class="users-filter-btn"

                type="button"

                data-filter="verified"

            >

                Geverifieerd

            </button>

            <button

                class="users-filter-btn"

                type="button"

                data-filter="pending"

            >

                Niet geverifieerd

            </button>

            <button

                class="users-filter-btn"

                type="button"

                data-filter="admin"

            >

                Admins

            </button>

            <button class="users-filter-btn" type="button" data-filter="google">

                Google

            </button>

            <button class="users-filter-btn" type="button" data-filter="github">

                GitHub

            </button>

            <button class="users-filter-btn" type="button" data-filter="facebook">

                Facebook

            </button>

            <button class="users-filter-btn" type="button" data-filter="email_code">

                E-mailcode

            </button>

            <button class="users-filter-btn" type="button" data-filter="magic_link">

                Magic link

            </button>

            <button class="users-filter-btn" type="button" data-filter="password">

                Wachtwoord

            </button>

            <button class="users-filter-btn" type="button" data-filter="photo">

                Met profielfoto

            </button>

            <button class="users-filter-btn" type="button" data-filter="social_avatar">

                Social avatar

            </button>

        </div>

    </div>









    {{-- ========================================================= --}}

    {{-- USERS TABLE                                                --}}

    {{-- ========================================================= --}}

    <div class="users-table-card">

        <div class="users-table-scroll">

            <table class="users-table">

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









                <tbody id="usersTableBody">

                    @forelse ($users as $user)

                        <tr

                            data-user-row

                            data-name="{{ strtolower($user->name) }}"

                            data-email="{{ strtolower($user->email) }}"

                            data-id="{{ $user->id }}"

                            data-admin="{{ $user->is_admin ? '1' : '0' }}"

                            data-verified="{{ $user->email_verified_at ? '1' : '0' }}"

                            data-provider="{{ $user->loginProvider() }}"

                            data-photo="{{ $user->hasProfilePhoto() ? '1' : '0' }}"

                            data-social-avatar="{{ (! $user->hasProfilePhoto() && $user->socialAvatar()) ? '1' : '0' }}"

                        >

                            {{-- USER --}}

                            <td>

                                <div class="user-cell">

                                    @if ($user->avatarUrl())

                                        <span class="user-avatar has-image">

                                            <img

                                                src="{{ $user->avatarUrl() }}"

                                                alt="Profielfoto van {{ $user->name }}"

                                                loading="lazy"

                                            >

                                        </span>

                                    @else

                                        <span class="user-avatar">

                                            {{ $user->initials() }}

                                        </span>

                                    @endif

                                    <div class="user-cell-copy">

                                        <strong>{{ $user->name }}</strong>

                                        <small>ID #{{ $user->id }}</small>

                                        @if ($user->hasProfilePhoto())

                                            <span class="user-photo-source custom">

                                                Eigen profielfoto

                                            </span>

                                        @elseif ($user->socialAvatar())

                                            <span class="user-photo-source social">

                                                Social avatar

                                            </span>

                                        @else

                                            <span class="user-photo-source">

                                                Initialen

                                            </span>

                                        @endif









                                        @if (auth()->id() === $user->id)

                                            <small class="user-self">

                                                Dit ben jij

                                            </small>

                                        @endif

                                    </div>

                                </div>

                            </td>









                            {{-- EMAIL --}}

                            <td>

                                <span class="user-email">

                                    {{ $user->email }}

                                </span>

                            </td>

                            {{-- LOGIN PROVIDER --}}

                            <td>

                                @php

                                    $provider = $user->loginProvider();

                                @endphp

                                <span

                                    class="user-provider {{ $provider }}"

                                    title="Laatste login via {{ $user->loginProviderLabel() }}"

                                >

                                    <span class="user-provider-icon" aria-hidden="true">

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

                                    <span class="user-badge admin">

                                        Administrator

                                    </span>

                                @else

                                    <span class="user-badge">

                                        Gebruiker

                                    </span>

                                @endif

                            </td>









                            {{-- VERIFICATION --}}

                            <td>

                                @if ($user->email_verified_at)

                                    <span class="user-badge verified">

                                        Geverifieerd

                                    </span>

                                    <small class="verified-date">

                                        {{ $user->email_verified_at->format('d-m-Y H:i') }}

                                    </small>

                                @else

                                    <span class="user-badge pending">

                                        Niet geverifieerd

                                    </span>

                                @endif

                            </td>









                            {{-- CREATED --}}

                            <td>

                                @if ($user->created_at)

                                    <span class="date-main">

                                        {{ $user->created_at->format('d-m-Y') }}

                                    </span>

                                    <small class="date-time">

                                        {{ $user->created_at->format('H:i') }}

                                    </small>

                                @else

                                    <span class="date-time">

                                        Onbekend

                                    </span>

                                @endif

                            </td>









                            {{-- ACTIONS --}}

                            <td>

                                <div class="user-actions">

                                    <a

                                        class="user-action-link"

                                        href="{{ route('users.edit', ['user' => $user->id]) }}"

                                    >

                                        Wijzigen

                                    </a>









                                    @if (auth()->id() !== $user->id)

                                        <form

                                            method="POST"

                                            action="{{ route('users.destroy', ['user' => $user->id]) }}"

                                            style="margin: 0;"

                                            onsubmit="return confirm('Weet je zeker dat je dit account definitief wilt verwijderen?');"

                                        >

                                            @csrf

                                            @method('DELETE')

                                            <button

                                                class="user-action-danger"

                                                type="submit"

                                            >

                                                Verwijderen

                                            </button>

                                        </form>

                                    @else

                                        <span class="user-action-self">

                                            Eigen account

                                        </span>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td

                                class="users-empty"

                                colspan="7"

                            >

                                <div class="users-empty-inner">

                                    <div class="users-empty-mark">

                                        M

                                    </div>

                                    <h3>

                                        Nog geen gebruikers

                                    </h3>

                                    <p>

                                        Er zijn momenteel nog geen gebruikers geregistreerd.

                                        Maak de eerste Mashal-gebruiker aan om te beginnen.

                                    </p>

                                    <a

                                        class="users-create-btn"

                                        href="{{ route('users.create') }}"

                                    >

                                        + Eerste gebruiker

                                    </a>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

            <div class="users-table-footer">

            <span class="users-visible-count">

                Zichtbaar:

                <strong id="visibleUsersCount">{{ $users->count() }}</strong>

                van {{ $users->count() }} gebruikers

            </span>

            <span class="users-visible-count">

                {{ $profilePhotoUsers }} eigen profielfoto's ·

                {{ $socialAvatarUsers }} social avatars

            </span>

        </div>

        <div

            class="users-no-results"

            id="usersNoResults"

        >

            Geen gebruikers gevonden met deze zoekopdracht of filter.

        </div>

</div>









    {{-- ========================================================= --}}

    {{-- INFORMATION                                                --}}

    {{-- ========================================================= --}}

    <div class="users-info">

        <div class="users-info-icon">

            i

        </div>

        <div>

            <strong>

                Beheerrechten

            </strong>

            <p>

                Als administrator kun je gebruikers toevoegen,

                profielgegevens wijzigen, wachtwoorden opnieuw instellen,

                e-mailverificatie beheren, administratorrechten aanpassen

                en gebruikers verwijderen. Je eigen administratoraccount

                kan niet vanuit deze lijst worden verwijderd.

            </p>

        </div>

    </div>

</section>

@endsection









@push('scripts')

<script>

    document.addEventListener('DOMContentLoaded', function () {

        const searchInput =

            document.getElementById('usersSearch');

        const rows =

            Array.from(

                document.querySelectorAll('[data-user-row]')

            );

        const filterButtons =

            Array.from(

                document.querySelectorAll('[data-filter]')

            );

        let activeFilter = 'all';

        const visibleUsersCount =

            document.getElementById('visibleUsersCount');

        const usersNoResults =

            document.getElementById('usersNoResults');









        function applyUsersFilter() {

            const query =

                searchInput

                    ? searchInput.value.trim().toLowerCase()

                    : '';

            let visible = 0;

            rows.forEach(function (row) {

                const haystack = [

                    row.dataset.name || '',

                    row.dataset.email || '',

                    row.dataset.id || '',

                    row.dataset.provider || ''

                ].join(' ');

                const matchesSearch =

                    !query || haystack.includes(query);

                let matchesFilter = true;

                if (activeFilter === 'verified') {

                    matchesFilter =

                        row.dataset.verified === '1';

                }

                if (activeFilter === 'pending') {

                    matchesFilter =

                        row.dataset.verified === '0';

                }

                if (activeFilter === 'admin') {

                    matchesFilter =

                        row.dataset.admin === '1';

                }

                if (

                    ['google', 'github', 'facebook', 'email_code', 'magic_link', 'password']

                        .includes(activeFilter)

                ) {

                    matchesFilter =

                        (row.dataset.provider || 'password') === activeFilter;

                }

                                if (activeFilter === 'photo') {

                    matchesFilter =

                        row.dataset.photo === '1';

                }

                if (activeFilter === 'social_avatar') {

                    matchesFilter =

                        row.dataset.socialAvatar === '1';

                }

const shouldShow =

                    matchesSearch && matchesFilter;

                row.style.display =

                    shouldShow

                        ? ''

                        : 'none';

                if (shouldShow) {

                    visible += 1;

                }

            });

            if (visibleUsersCount) {

                visibleUsersCount.textContent =

                    String(visible);

            }

            if (usersNoResults) {

                usersNoResults.style.display =

                    visible === 0

                        ? 'block'

                        : 'none';

            }

        }









        if (searchInput) {

            searchInput.addEventListener(

                'input',

                applyUsersFilter

            );

        }









        filterButtons.forEach(function (button) {

            button.addEventListener('click', function () {

                activeFilter =

                    button.dataset.filter || 'all';

                filterButtons.forEach(function (item) {

                    item.classList.remove('active');

                });

                button.classList.add('active');

                applyUsersFilter();

            });

        });

    });

</script>

@endpush