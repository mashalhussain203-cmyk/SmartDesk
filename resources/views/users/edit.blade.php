@extends('layouts.admin-layout')

@section('title', 'Mashal Admin | Gebruiker wijzigen')

@section('page-title', 'Gebruiker wijzigen')

@push('styles')

<style>

    .user-edit-page {

        position: relative;

        overflow: hidden;

        padding: 6px 0 36px;

    }

    .user-edit-page::before {

        content: "M";

        position: absolute;

        right: -40px;

        top: 18px;

        color: rgba(255,255,255,.016);

        font-size: 270px;

        font-weight: 950;

        line-height: .8;

        pointer-events: none;

        user-select: none;

    }

    /* ========================================================= */

    /* HERO                                                       */

    /* ========================================================= */

    .edit-hero {

        position: relative;

        z-index: 2;

        margin-bottom: 24px;

        padding: 28px;

        display: grid;

        grid-template-columns: minmax(0, 1fr) auto;

        gap: 28px;

        align-items: end;

        overflow: hidden;

        border: 1px solid rgba(255,255,255,.08);

        border-radius: 24px;

        background:

            radial-gradient(

                circle at 88% 10%,

                rgba(215,164,95,.10),

                transparent 18rem

            ),

            linear-gradient(

                145deg,

                rgba(255,255,255,.045),

                rgba(255,255,255,.015)

            );

        box-shadow: 0 18px 50px rgba(0,0,0,.18);

    }

    .edit-hero-kicker {

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

    .edit-hero-kicker::before {

        content: "";

        width: 28px;

        height: 1px;

        background: #d6a45f;

    }

    .edit-hero h2 {

        margin: 0;

        color: #fff;

        font-size: clamp(30px, 3.5vw, 46px);

        line-height: 1;

        letter-spacing: -.05em;

    }

    .edit-hero p {

        max-width: 700px;

        margin: 11px 0 0;

        color: #7d838a;

        font-size: 12px;

        line-height: 1.75;

    }

    .hero-back {

        min-height: 42px;

        padding: 0 15px;

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 8px;

        border: 1px solid rgba(255,255,255,.10);

        border-radius: 999px;

        background: rgba(255,255,255,.025);

        color: #b8b7b2;

        text-decoration: none;

        font-size: 9px;

        font-weight: 900;

        letter-spacing: .04em;

        transition:

            transform .2s ease,

            border-color .2s ease,

            background .2s ease,

            color .2s ease;

    }

    .hero-back:hover {

        transform: translateY(-2px);

        border-color: rgba(215,164,95,.25);

        background: rgba(215,164,95,.055);

        color: #efc985;

    }

    /* ========================================================= */

    /* STATUS STRIP                                               */

    /* ========================================================= */

    .edit-status-strip {

        position: relative;

        z-index: 2;

        margin-bottom: 22px;

        display: grid;

        grid-template-columns: repeat(5, minmax(0,1fr));

        border: 1px solid rgba(255,255,255,.07);

        border-radius: 18px;

        overflow: hidden;

        background: rgba(255,255,255,.02);

    }

    .edit-status-item {

        padding: 17px 18px;

        border-right: 1px solid rgba(255,255,255,.06);

    }

    .edit-status-item:last-child {

        border-right: 0;

    }

    .edit-status-item small {

        display: block;

        margin-bottom: 5px;

        color: #696f76;

        font-size: 7px;

        font-weight: 900;

        letter-spacing: .13em;

        text-transform: uppercase;

    }

    .edit-status-item strong {

        display: block;

        color: #dcdad5;

        font-size: 11px;

        line-height: 1.45;

        word-break: break-word;

    }

    .status-good {

        color: #9ce7bc !important;

    }

    .status-warn {

        color: #e9c674 !important;

    }

    /* ========================================================= */

    /* MESSAGES                                                   */

    /* ========================================================= */

    .edit-message {

        position: relative;

        z-index: 2;

        margin-bottom: 18px;

        padding: 14px 16px;

        border-radius: 14px;

        font-size: 12px;

        line-height: 1.6;

    }

    .edit-message.success {

        border: 1px solid rgba(91,214,149,.20);

        background: rgba(91,214,149,.07);

        color: #a9efc8;

    }

    .edit-message.error {

        border: 1px solid rgba(241,123,123,.20);

        background: rgba(241,123,123,.07);

        color: #ffc1c1;

    }

    .edit-message ul {

        margin: 8px 0 0 18px;

        padding: 0;

    }

    /* ========================================================= */

    /* LAYOUT                                                     */

    /* ========================================================= */

    .edit-layout {

        position: relative;

        z-index: 2;

        display: grid;

        grid-template-columns: minmax(0, 1.2fr) minmax(300px, .8fr);

        gap: 22px;

        align-items: start;

    }

    .edit-form-card,

    .edit-aside-card,

    .danger-card {

        border: 1px solid rgba(255,255,255,.08);

        border-radius: 22px;

        background:

            linear-gradient(

                145deg,

                rgba(255,255,255,.04),

                rgba(255,255,255,.014)

            );

        box-shadow: 0 16px 44px rgba(0,0,0,.17);

    }

    .edit-form-card {

        padding: 26px;

    }

    .edit-aside {

        position: sticky;

        top: 92px;

        display: grid;

        gap: 14px;

    }

    .edit-aside-card {

        padding: 20px;

    }

    /* ========================================================= */

    /* FORM SECTIONS                                              */

    /* ========================================================= */

    .edit-section + .edit-section {

        margin-top: 30px;

        padding-top: 26px;

        border-top: 1px solid rgba(255,255,255,.07);

    }

    .edit-section-head {

        margin-bottom: 19px;

    }

    .edit-section-kicker {

        display: block;

        margin-bottom: 6px;

        color: #9e7442;

        font-size: 8px;

        font-weight: 900;

        letter-spacing: .16em;

        text-transform: uppercase;

    }

    .edit-section-head h3 {

        margin: 0;

        color: #fff;

        font-size: 20px;

        letter-spacing: -.03em;

    }

    .edit-section-head p {

        margin: 7px 0 0;

        color: #70767d;

        font-size: 10px;

        line-height: 1.7;

    }

    .edit-form-grid {

        display: grid;

        grid-template-columns: repeat(2, minmax(0,1fr));

        gap: 15px;

    }

    .edit-field {

        margin-bottom: 16px;

    }

    .edit-field.full {

        grid-column: 1 / -1;

    }

    .edit-label {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 12px;

        margin-bottom: 8px;

    }

    .edit-label label {

        color: #b8b9b6;

        font-size: 8px;

        font-weight: 900;

        letter-spacing: .14em;

        text-transform: uppercase;

    }

    .field-error {

        color: #f3a1a1;

        font-size: 9px;

        font-weight: 750;

    }

    .input-wrap {

        position: relative;

    }

    .edit-input {

        width: 100%;

        height: 52px;

        padding: 0 46px 0 15px;

        border: 1px solid rgba(255,255,255,.10);

        border-radius: 14px;

        outline: none;

        background: rgba(255,255,255,.03);

        color: #fff;

        font-size: 12px;

        transition:

            border-color .2s ease,

            background .2s ease,

            box-shadow .2s ease;

    }

    .edit-input::placeholder {

        color: #565c63;

    }

    .edit-input:focus {

        border-color: rgba(215,164,95,.42);

        background: rgba(215,164,95,.03);

        box-shadow: 0 0 0 4px rgba(215,164,95,.06);

    }

    .input-icon {

        position: absolute;

        right: 15px;

        top: 50%;

        transform: translateY(-50%);

        color: #6d737a;

        font-size: 11px;

        pointer-events: none;

    }

    .password-toggle {

        position: absolute;

        right: 8px;

        top: 50%;

        transform: translateY(-50%);

        height: 34px;

        min-width: 42px;

        padding: 0 8px;

        border: 0;

        border-radius: 10px;

        background: transparent;

        color: #7d838a;

        font-size: 8px;

        font-weight: 850;

        cursor: pointer;

        transition: color .2s ease, background .2s ease;

    }

    .password-toggle:hover {

        color: #efc985;

        background: rgba(215,164,95,.06);

    }

    .field-help {

        display: block;

        margin-top: 7px;

        color: #62686f;

        font-size: 9px;

        line-height: 1.5;

    }

    /* ========================================================= */

    /* PASSWORD STRENGTH                                         */

    /* ========================================================= */

    .password-strength {

        margin-top: 9px;

    }

    .password-strength-bars {

        display: grid;

        grid-template-columns: repeat(4,1fr);

        gap: 5px;

    }

    .password-strength-bar {

        height: 4px;

        border-radius: 999px;

        background: rgba(255,255,255,.07);

    }

    .password-strength[data-level="1"] .password-strength-bar:nth-child(1) {

        background: #d65f5f;

    }

    .password-strength[data-level="2"] .password-strength-bar:nth-child(-n+2) {

        background: #d59a50;

    }

    .password-strength[data-level="3"] .password-strength-bar:nth-child(-n+3) {

        background: #d8bd63;

    }

    .password-strength[data-level="4"] .password-strength-bar:nth-child(-n+4) {

        background: #63c98f;

    }

    .password-strength-text {

        margin-top: 7px;

        color: #62686f;

        font-size: 8px;

    }

    /* ========================================================= */

    /* PERMISSIONS                                                */

    /* ========================================================= */

    .permission-grid {

        display: grid;

        grid-template-columns: 1fr 1fr;

        gap: 12px;

    }

    .permission-card {

        padding: 17px;

        border: 1px solid rgba(255,255,255,.075);

        border-radius: 16px;

        background: rgba(255,255,255,.022);

        transition:

            border-color .2s ease,

            background .2s ease,

            transform .2s ease;

    }

    .permission-card:hover {

        transform: translateY(-2px);

        border-color: rgba(215,164,95,.18);

    }

    .permission-label {

        display: flex;

        align-items: flex-start;

        gap: 11px;

        margin: 0;

        cursor: pointer;

        text-transform: none;

        letter-spacing: normal;

    }

    .permission-label input {

        flex-shrink: 0;

        width: 17px;

        height: 17px;

        margin: 2px 0 0;

        accent-color: #d7a45f;

    }

    .permission-label input:disabled {

        opacity: .5;

        cursor: not-allowed;

    }

    .permission-copy strong {

        display: block;

        color: #dedcd7;

        font-size: 11px;

        line-height: 1.4;

    }

    .permission-copy span {

        display: block;

        margin-top: 5px;

        color: #6c7279;

        font-size: 9px;

        line-height: 1.65;

    }

    /* ========================================================= */

    /* ACTIONS                                                    */

    /* ========================================================= */

    .edit-actions {

        margin-top: 26px;

        padding-top: 22px;

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 14px;

        flex-wrap: wrap;

        border-top: 1px solid rgba(255,255,255,.07);

    }

    .edit-primary,

    .edit-secondary {

        min-height: 48px;

        padding: 0 18px;

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 8px;

        border-radius: 999px;

        font-size: 9px;

        font-weight: 950;

        letter-spacing: .04em;

        text-decoration: none;

        cursor: pointer;

        transition:

            transform .2s ease,

            box-shadow .2s ease,

            border-color .2s ease,

            background .2s ease;

    }

    .edit-primary {

        border: 0;

        background:

            linear-gradient(

                135deg,

                #f1cc8b,

                #ca914c

            );

        color: #14100b;

        box-shadow: 0 14px 34px rgba(215,164,95,.18);

    }

    .edit-primary:hover {

        transform: translateY(-2px);

        box-shadow: 0 22px 48px rgba(215,164,95,.28);

    }

    .edit-secondary {

        border: 1px solid rgba(255,255,255,.09);

        background: rgba(255,255,255,.025);

        color: #a9aca9;

    }

    .edit-secondary:hover {

        transform: translateY(-2px);

        border-color: rgba(215,164,95,.22);

        color: #efc985;

        background: rgba(215,164,95,.05);

    }

    /* ========================================================= */

    /* ASIDE                                                      */

    /* ========================================================= */

    .aside-kicker {

        display: block;

        margin-bottom: 7px;

        color: #9e7442;

        font-size: 8px;

        font-weight: 900;

        letter-spacing: .15em;

        text-transform: uppercase;

    }

    .edit-aside-card h3 {

        margin: 0 0 8px;

        color: #fff;

        font-size: 17px;

        letter-spacing: -.025em;

    }

    .edit-aside-card p {

        margin: 0;

        color: #6f757c;

        font-size: 10px;

        line-height: 1.75;

    }

    .preview-user {

        display: flex;

        align-items: center;

        gap: 12px;

    }

    .preview-avatar {

        width: 50px;

        height: 50px;

        display: grid;

        place-items: center;

        border-radius: 15px;

        background:

            linear-gradient(

                145deg,

                #f0ca86,

                #b67e3d

            );

        color: #15110c;

        font-size: 18px;

        font-weight: 950;

    }

    .preview-copy strong {

        display: block;

        color: #e4e2dd;

        font-size: 11px;

    }

    .preview-copy span {

        display: block;

        margin-top: 3px;

        color: #666c73;

        font-size: 9px;

        word-break: break-word;

    }

    .preview-tags {

        margin-top: 14px;

        display: flex;

        gap: 7px;

        flex-wrap: wrap;

    }

    .preview-tag {

        padding: 6px 8px;

        border: 1px solid rgba(255,255,255,.08);

        border-radius: 999px;

        background: rgba(255,255,255,.025);

        color: #8d9298;

        font-size: 7px;

        font-weight: 900;

        letter-spacing: .08em;

        text-transform: uppercase;

    }

    .preview-tag.active {

        border-color: rgba(215,164,95,.17);

        background: rgba(215,164,95,.055);

        color: #d8ac6a;

    }

    .aside-list {

        margin-top: 14px;

        display: grid;

        gap: 10px;

    }

    .aside-list-item {

        display: flex;

        align-items: flex-start;

        gap: 9px;

        color: #858a90;

        font-size: 9px;

        line-height: 1.6;

    }

    .aside-list-mark {

        flex-shrink: 0;

        width: 20px;

        height: 20px;

        display: grid;

        place-items: center;

        border: 1px solid rgba(215,164,95,.14);

        border-radius: 50%;

        color: #c8995a;

        font-size: 8px;

    }

    .self-account-note {

        border-color: rgba(113,166,255,.16);

        background:

            linear-gradient(

                145deg,

                rgba(113,166,255,.055),

                rgba(113,166,255,.018)

            );

    }

    .self-account-note h3 {

        color: #adcaff;

    }

    /* ========================================================= */

    /* DANGER ZONE                                                */

    /* ========================================================= */

    .danger-card {

        position: relative;

        z-index: 2;

        margin-top: 22px;

        padding: 24px;

        border-color: rgba(241,123,123,.16);

        background:

            linear-gradient(

                145deg,

                rgba(241,123,123,.045),

                rgba(241,123,123,.012)

            );

    }

    .danger-card-head {

        display: flex;

        align-items: flex-start;

        justify-content: space-between;

        gap: 20px;

    }

    .danger-kicker {

        display: block;

        margin-bottom: 6px;

        color: #bf6464;

        font-size: 8px;

        font-weight: 900;

        letter-spacing: .15em;

        text-transform: uppercase;

    }

    .danger-card h3 {

        margin: 0;

        color: #f2a1a1;

        font-size: 19px;

        letter-spacing: -.03em;

    }

    .danger-card p {

        max-width: 700px;

        margin: 8px 0 0;

        color: #80696b;

        font-size: 10px;

        line-height: 1.75;

    }

    .danger-button {

        min-height: 44px;

        padding: 0 15px;

        border: 1px solid rgba(241,123,123,.22);

        border-radius: 999px;

        background: rgba(241,123,123,.08);

        color: #f0a0a0;

        font-size: 9px;

        font-weight: 900;

        cursor: pointer;

        transition:

            transform .2s ease,

            background .2s ease,

            border-color .2s ease;

    }

    .danger-button:hover {

        transform: translateY(-2px);

        border-color: rgba(241,123,123,.40);

        background: rgba(241,123,123,.13);

    }

    .locked-delete {

        color: #747a81;

        font-size: 10px;

        line-height: 1.7;

    }

    /* ========================================================= */

    
    /* ========================================================= */
    /* PROFILE PHOTO / IDENTITY                                  */
    /* ========================================================= */

    .edit-hero-identity {
        display: flex;
        align-items: center;
        gap: 18px;
        min-width: 0;
    }

    .edit-hero-avatar {
        width: 72px;
        height: 72px;
        flex: 0 0 72px;
        display: grid;
        place-items: center;
        overflow: hidden;
        border: 1px solid rgba(215,164,95,.24);
        border-radius: 21px;
        background: linear-gradient(145deg, #f0ca86, #b67e3d);
        color: #15110c;
        font-size: 24px;
        font-weight: 950;
        box-shadow: 0 16px 38px rgba(215,164,95,.15);
    }

    .edit-hero-avatar img,
    .preview-avatar img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
    }

    .edit-hero-avatar.has-image,
    .preview-avatar.has-image {
        background: #111419;
        color: transparent;
    }

    .edit-hero-copy {
        min-width: 0;
    }

    .edit-avatar-source {
        margin-top: 9px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #747a81;
        font-size: 8px;
        font-weight: 850;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .edit-avatar-source::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #d7a45f;
        box-shadow: 0 0 12px rgba(215,164,95,.38);
    }

    .edit-avatar-source.custom {
        color: #d7aa69;
    }

    .edit-avatar-source.social {
        color: #9fc2ff;
    }

    .preview-avatar {
        position: relative;
        overflow: hidden;
        box-shadow: 0 12px 28px rgba(0,0,0,.18);
    }

    .preview-source {
        margin-top: 7px !important;
        color: #8a704e !important;
        font-size: 8px !important;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .provider-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-top: 8px;
        padding: 7px 9px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 999px;
        background: rgba(255,255,255,.025);
        color: #a9adb2;
        font-size: 8px;
        font-weight: 900;
    }

/* RESPONSIVE                                                 */

    /* ========================================================= */

    @media (max-width: 1050px) {

        .edit-hero,

        .edit-layout {

            grid-template-columns: 1fr;

        }

        .edit-aside {

            position: static;

            grid-template-columns: repeat(2, 1fr);

        }

        .edit-status-strip {

            grid-template-columns: 1fr 1fr;

        }
        .edit-status-item {
            border-bottom: 1px solid rgba(255,255,255,.06);
        }

        .edit-status-item:last-child {
            border-right: 0;
            border-bottom: 0;
        }

    }

    @media (max-width: 720px) {

        .edit-form-grid,

        .permission-grid,

        .edit-aside,

        .edit-status-strip {

            grid-template-columns: 1fr;

        }

        .edit-status-item {

            border-right: 0;

            border-bottom: 1px solid rgba(255,255,255,.06);

        }

        .edit-status-item:last-child {

            border-bottom: 0;

        }

        .edit-field.full {

            grid-column: auto;

        }

        .edit-form-card {

            padding: 21px;

        }

        .edit-hero {

            padding: 22px;

        }

        .edit-actions {

            align-items: stretch;

            flex-direction: column-reverse;

        }

        .edit-primary,

        .edit-secondary {

            width: 100%;

        }

        .danger-card-head {

            flex-direction: column;

        }

        .danger-button {

            width: 100%;

        }

    }

</style>

@endpush



@section('content')

<section class="user-edit-page">

    {{-- ========================================================= --}}

    {{-- HERO                                                       --}}

    {{-- ========================================================= --}}

    <div class="edit-hero">

        <div class="edit-hero-identity">
            <div class="edit-hero-avatar {{ $user->avatarUrl() ? 'has-image' : '' }}">
                @if ($user->avatarUrl())
                    <img
                        src="{{ $user->avatarUrl() }}"
                        alt="Profielfoto van {{ $user->name }}"
                    >
                @else
                    {{ $user->initials() }}
                @endif
            </div>

            <div class="edit-hero-copy">
                <span class="edit-hero-kicker">Mashal User Management</span>

                <h2>{{ $user->name }}</h2>

                <p>
                    Wijzig profielgegevens, accountbeveiliging,
                    e-mailverificatie en administratorrechten
                    vanuit één overzichtelijke beheeromgeving.
                </p>

                @if ($user->hasProfilePhoto())
                    <span class="edit-avatar-source custom">Eigen profielfoto</span>
                @elseif ($user->socialAvatar())
                    <span class="edit-avatar-source social">Social avatar</span>
                @else
                    <span class="edit-avatar-source">Initialen</span>
                @endif
            </div>
        </div>



        <a

            class="hero-back"

            href="{{ route('users.index') }}"

        >

            ← Terug naar gebruikers

        </a>

    </div>



    {{-- ========================================================= --}}

    {{-- STATUS STRIP                                               --}}

    {{-- ========================================================= --}}

    <div class="edit-status-strip">

        <div class="edit-status-item">

            <small>

                User ID

            </small>

            <strong>

                #{{ $user->id }}

            </strong>

        </div>



        <div class="edit-status-item">

            <small>

                E-mailstatus

            </small>

            <strong class="{{ $user->email_verified_at ? 'status-good' : 'status-warn' }}">

                {{ $user->email_verified_at ? 'Geverifieerd' : 'Niet geverifieerd' }}

            </strong>

        </div>



        <div class="edit-status-item">

            <small>

                Rol

            </small>

            <strong>

                {{ $user->is_admin ? 'Administrator' : 'Gebruiker' }}

            </strong>

        </div>



        <div class="edit-status-item">

            <small>

                Laatste wijziging

            </small>

            <strong>

                {{ optional($user->updated_at)->format('d-m-Y H:i') }}

            </strong>

        </div>

        <div class="edit-status-item">
            <small>Laatste login via</small>
            <strong>{{ $user->loginProviderLabel() }}</strong>
        </div>

    </div>



    {{-- ========================================================= --}}

    {{-- MESSAGES                                                   --}}

    {{-- ========================================================= --}}

    @if (session('success'))

        <div class="edit-message success">

            <strong>

                Wijzigingen opgeslagen.

            </strong>

            {{ session('success') }}

        </div>

    @endif



    @if ($errors->any())

        <div class="edit-message error">

            <strong>

                De wijzigingen konden niet worden opgeslagen.

            </strong>

            <ul>

                @foreach ($errors->all() as $error)

                    <li>

                        {{ $error }}

                    </li>

                @endforeach

            </ul>

        </div>

    @endif



    <div class="edit-layout">

        {{-- ========================================================= --}}

        {{-- MAIN FORM                                                  --}}

        {{-- ========================================================= --}}

        <div class="edit-form-card">

            <form

                method="POST"

                action="{{ route('users.update', ['user' => $user->id]) }}"

                id="editUserForm"

            >

                @csrf

                @method('PUT')



                {{-- ================================================= --}}

                {{-- IDENTITY                                          --}}

                {{-- ================================================= --}}

                <section class="edit-section">

                    <div class="edit-section-head">

                        <span class="edit-section-kicker">

                            01 / Identity

                        </span>

                        <h3>

                            Persoonlijke gegevens

                        </h3>

                        <p>

                            Werk de naam en het e-mailadres van deze gebruiker bij.

                        </p>

                    </div>



                    <div class="edit-form-grid">

                        {{-- NAME --}}

                        <div class="edit-field">

                            <div class="edit-label">

                                <label for="name">

                                    Naam

                                </label>

                                @error('name')

                                    <span class="field-error">

                                        {{ $message }}

                                    </span>

                                @enderror

                            </div>



                            <div class="input-wrap">

                                <input

                                    class="edit-input"

                                    id="name"

                                    type="text"

                                    name="name"

                                    value="{{ old('name', $user->name) }}"

                                    autocomplete="name"

                                    required

                                    autofocus

                                >

                                <span class="input-icon">

                                    ◇

                                </span>

                            </div>

                        </div>



                        {{-- EMAIL --}}

                        <div class="edit-field">

                            <div class="edit-label">

                                <label for="email">

                                    E-mailadres

                                </label>

                                @error('email')

                                    <span class="field-error">

                                        {{ $message }}

                                    </span>

                                @enderror

                            </div>



                            <div class="input-wrap">

                                <input

                                    class="edit-input"

                                    id="email"

                                    type="email"

                                    name="email"

                                    value="{{ old('email', $user->email) }}"

                                    autocomplete="email"

                                    required

                                >

                                <span class="input-icon">

                                    @

                                </span>

                            </div>

                        </div>

                    </div>

                </section>



                {{-- ================================================= --}}

                {{-- PASSWORD                                          --}}

                {{-- ================================================= --}}

                <section class="edit-section">

                    <div class="edit-section-head">

                        <span class="edit-section-kicker">

                            02 / Credentials

                        </span>

                        <h3>

                            Wachtwoord wijzigen

                        </h3>

                        <p>

                            Laat beide velden leeg als het huidige wachtwoord

                            behouden moet blijven.

                        </p>

                    </div>



                    <div class="edit-form-grid">

                        <div class="edit-field">

                            <div class="edit-label">

                                <label for="password">

                                    Nieuw wachtwoord

                                </label>

                                @error('password')

                                    <span class="field-error">

                                        {{ $message }}

                                    </span>

                                @enderror

                            </div>



                            <div class="input-wrap">

                                <input

                                    class="edit-input"

                                    id="password"

                                    type="password"

                                    name="password"

                                    minlength="8"

                                    autocomplete="new-password"

                                    placeholder="Leeg laten om niet te wijzigen"

                                >

                                <button

                                    class="password-toggle"

                                    type="button"

                                    data-toggle-password="password"

                                    aria-label="Wachtwoord tonen of verbergen"

                                >

                                    Tonen

                                </button>

                            </div>



                            <div

                                class="password-strength"

                                id="passwordStrength"

                                data-level="0"

                            >

                                <div class="password-strength-bars">

                                    <span class="password-strength-bar"></span>

                                    <span class="password-strength-bar"></span>

                                    <span class="password-strength-bar"></span>

                                    <span class="password-strength-bar"></span>

                                </div>

                                <div

                                    class="password-strength-text"

                                    id="passwordStrengthText"

                                >

                                    Optioneel. Minimaal 8 tekens wanneer ingevuld.

                                </div>

                            </div>

                        </div>



                        <div class="edit-field">

                            <div class="edit-label">

                                <label for="password_confirmation">

                                    Nieuw wachtwoord bevestigen

                                </label>

                            </div>



                            <div class="input-wrap">

                                <input

                                    class="edit-input"

                                    id="password_confirmation"

                                    type="password"

                                    name="password_confirmation"

                                    minlength="8"

                                    autocomplete="new-password"

                                    placeholder="Herhaal nieuw wachtwoord"

                                >

                                <button

                                    class="password-toggle"

                                    type="button"

                                    data-toggle-password="password_confirmation"

                                    aria-label="Wachtwoordbevestiging tonen of verbergen"

                                >

                                    Tonen

                                </button>

                            </div>

                        </div>

                    </div>

                </section>



                {{-- ================================================= --}}

                {{-- ACCESS                                            --}}

                {{-- ================================================= --}}

                <section class="edit-section">

                    <div class="edit-section-head">

                        <span class="edit-section-kicker">

                            03 / Access

                        </span>

                        <h3>

                            Verificatie & administratorrechten

                        </h3>

                        <p>

                            Pas de status zorgvuldig aan.

                            Wijzigingen hier kunnen direct invloed hebben

                            op toegang tot checkout en beheer.

                        </p>

                    </div>



                    <div class="permission-grid">

                        {{-- VERIFIED --}}

                        <div class="permission-card">

                            <label

                                class="permission-label"

                                for="email_verified"

                            >

                                <input

                                    id="email_verified"

                                    type="checkbox"

                                    name="email_verified"

                                    value="1"

                                    {{ old(

                                        'email_verified',

                                        $user->email_verified_at ? 1 : 0

                                    ) ? 'checked' : '' }}

                                >

                                <span class="permission-copy">

                                    <strong>

                                        E-mailadres geverifieerd

                                    </strong>

                                    <span>

                                        Schakel uit om het account opnieuw

                                        als niet-geverifieerd te markeren.

                                    </span>

                                </span>

                            </label>

                            @error('email_verified')

                                <span class="field-error">

                                    {{ $message }}

                                </span>

                            @enderror

                        </div>



                        {{-- ADMIN --}}

                        <div class="permission-card">

                            <label

                                class="permission-label"

                                for="is_admin"

                            >

                                <input

                                    id="is_admin"

                                    type="checkbox"

                                    name="is_admin"

                                    value="1"

                                    {{ old(

                                        'is_admin',

                                        $user->is_admin ? 1 : 0

                                    ) ? 'checked' : '' }}

                                    @if (auth()->id() === $user->id)

                                        disabled

                                    @endif

                                >

                                <span class="permission-copy">

                                    <strong>

                                        Administratorrechten

                                    </strong>

                                    <span>

                                        Geeft toegang tot beheerfuncties

                                        en gevoelige gebruikersacties.

                                    </span>

                                </span>

                            </label>



                            @if (auth()->id() === $user->id)

                                <input

                                    type="hidden"

                                    name="is_admin"

                                    value="1"

                                >

                                <small class="field-help">

                                    Je kunt je eigen administratorrechten

                                    niet uitschakelen.

                                </small>

                            @endif



                            @error('is_admin')

                                <span class="field-error">

                                    {{ $message }}

                                </span>

                            @enderror

                        </div>

                    </div>

                </section>



                {{-- ================================================= --}}

                {{-- ACTIONS                                           --}}

                {{-- ================================================= --}}

                <div class="edit-actions">

                    <a

                        class="edit-secondary"

                        href="{{ route('users.index') }}"

                    >

                        Annuleren

                    </a>



                    <button

                        class="edit-primary"

                        type="submit"

                    >

                        Wijzigingen opslaan

                        <span aria-hidden="true">→</span>

                    </button>

                </div>

            </form>

        </div>



        {{-- ========================================================= --}}

        {{-- SIDEBAR                                                    --}}

        {{-- ========================================================= --}}

        <aside class="edit-aside">

            {{-- LIVE PREVIEW --}}

            <div class="edit-aside-card">

                <span class="aside-kicker">

                    Live account preview

                </span>

                <div class="preview-user">
                    <div
                        class="preview-avatar {{ $user->avatarUrl() ? 'has-image' : '' }}"
                        id="previewAvatar"
                        data-has-image="{{ $user->avatarUrl() ? '1' : '0' }}"
                    >
                        @if ($user->avatarUrl())
                            <img
                                id="previewAvatarImage"
                                src="{{ $user->avatarUrl() }}"
                                alt="Profielfoto van {{ $user->name }}"
                            >
                        @else
                            <span id="previewAvatarInitials">
                                {{ $user->initials() }}
                            </span>
                        @endif
                    </div>

                    <div class="preview-copy">
                        <strong id="previewName">{{ $user->name }}</strong>
                        <span id="previewEmail">{{ $user->email }}</span>

                        @if ($user->hasProfilePhoto())
                            <span class="preview-source">Eigen profielfoto</span>
                        @elseif ($user->socialAvatar())
                            <span class="preview-source">Social avatar</span>
                        @else
                            <span class="preview-source">Initialen</span>
                        @endif
                    </div>
                </div>



                <div class="preview-tags">

                    <span

                        class="preview-tag {{ old('email_verified', $user->email_verified_at ? 1 : 0) ? 'active' : '' }}"

                        id="previewVerified"

                    >

                        {{ old('email_verified', $user->email_verified_at ? 1 : 0) ? 'Geverifieerd' : 'Niet geverifieerd' }}

                    </span>

                    <span

                        class="preview-tag {{ old('is_admin', $user->is_admin ? 1 : 0) ? 'active' : '' }}"

                        id="previewAdmin"

                    >

                        {{ old('is_admin', $user->is_admin ? 1 : 0) ? 'Administrator' : 'Gebruiker' }}

                    </span>

                </div>

            </div>



            {{-- ACCOUNT HISTORY --}}

            <div class="edit-aside-card">

                <span class="aside-kicker">

                    Account history

                </span>

                <h3>

                    Accountinformatie

                </h3>

                <div class="provider-pill">
                    Login via {{ $user->loginProviderLabel() }}
                </div>

                <div class="aside-list">

                    <div class="aside-list-item">

                        <span class="aside-list-mark">

                            ID

                        </span>

                        <span>

                            Gebruikers-ID:

                            <strong>#{{ $user->id }}</strong>

                        </span>

                    </div>



                    <div class="aside-list-item">

                        <span class="aside-list-mark">

                            +

                        </span>

                        <span>

                            Geregistreerd:

                            <strong>

                                {{ optional($user->created_at)->format('d-m-Y H:i') }}

                            </strong>

                        </span>

                    </div>



                    <div class="aside-list-item">

                        <span class="aside-list-mark">

                            ↻

                        </span>

                        <span>

                            Laatst gewijzigd:

                            <strong>

                                {{ optional($user->updated_at)->format('d-m-Y H:i') }}

                            </strong>

                        </span>

                    </div>

                </div>

            </div>



            {{-- SELF ACCOUNT NOTICE --}}

            @if (auth()->id() === $user->id)

                <div class="edit-aside-card self-account-note">

                    <span class="aside-kicker">

                        Protected admin account

                    </span>

                    <h3>

                        Dit is jouw eigen account

                    </h3>

                    <p>

                        Om te voorkomen dat je jezelf uit het beheer sluit,

                        kun je via deze pagina je eigen administratorrechten

                        niet uitschakelen of je eigen account verwijderen.

                    </p>

                </div>

            @else

                <div class="edit-aside-card">

                    <span class="aside-kicker">

                        Change impact

                    </span>

                    <h3>

                        Controleer wijzigingen

                    </h3>

                    <p>

                        Het wijzigen van e-mail, verificatie of rechten

                        kan direct invloed hebben op wat deze gebruiker

                        binnen Mashal kan doen.

                    </p>

                </div>

            @endif

        </aside>

    </div>



    {{-- ========================================================= --}}

    {{-- DANGER ZONE                                                --}}

    {{-- ========================================================= --}}

    @if (auth()->id() !== $user->id)

        <section class="danger-card">

            <div class="danger-card-head">

                <div>

                    <span class="danger-kicker">

                        Danger zone

                    </span>

                    <h3>

                        Gebruiker definitief verwijderen

                    </h3>

                    <p>

                        Hiermee wordt het Mashal-account van

                        <strong>{{ $user->name }}</strong>

                        definitief verwijderd.

                        Deze actie moet bewust worden uitgevoerd.

                    </p>

                </div>



                <form

                    method="POST"

                    action="{{ route('users.destroy', ['user' => $user->id]) }}"

                    onsubmit="return confirm('Weet je zeker dat je deze gebruiker definitief wilt verwijderen?');"

                >

                    @csrf

                    @method('DELETE')

                    <button

                        class="danger-button"

                        type="submit"

                    >

                        Gebruiker verwijderen

                    </button>

                </form>

            </div>

        </section>

    @else

        <section class="danger-card">

            <span class="danger-kicker">

                Account protection

            </span>

            <h3>

                Verwijderen geblokkeerd

            </h3>

            <p class="locked-delete">

                Je eigen administratoraccount kan via deze pagina

                niet worden verwijderd.

            </p>

        </section>

    @endif

</section>

@endsection



@push('scripts')

<script>

    document.addEventListener('DOMContentLoaded', function () {

        const nameInput =

            document.getElementById('name');

        const emailInput =

            document.getElementById('email');

        const verifiedInput =

            document.getElementById('email_verified');

        const adminInput =

            document.getElementById('is_admin');

        const previewAvatar =

            document.getElementById('previewAvatar');

        const previewName =

            document.getElementById('previewName');

        const previewEmail =

            document.getElementById('previewEmail');

        const previewVerified =

            document.getElementById('previewVerified');

        const previewAdmin =

            document.getElementById('previewAdmin');



        function updatePreview() {

            const name =

                nameInput && nameInput.value.trim()

                    ? nameInput.value.trim()

                    : '{{ addslashes($user->name) }}';

            const email =

                emailInput && emailInput.value.trim()

                    ? emailInput.value.trim()

                    : '{{ addslashes($user->email) }}';

            if (previewName) {

                previewName.textContent = name;

            }

            if (previewEmail) {

                previewEmail.textContent = email;

            }

            if (
                previewAvatar &&
                previewAvatar.dataset.hasImage !== '1'
            ) {
                const initials = name
                    .trim()
                    .split(/\s+/)
                    .filter(Boolean)
                    .slice(0, 2)
                    .map(function (part) {
                        return part.charAt(0).toUpperCase();
                    })
                    .join('');

                previewAvatar.textContent =
                    initials || 'M';
            }

            if (

                previewVerified &&

                verifiedInput

            ) {

                previewVerified.textContent =

                    verifiedInput.checked

                        ? 'Geverifieerd'

                        : 'Niet geverifieerd';

                previewVerified.classList.toggle(

                    'active',

                    verifiedInput.checked

                );

            }

            if (

                previewAdmin &&

                adminInput

            ) {

                previewAdmin.textContent =

                    adminInput.checked

                        ? 'Administrator'

                        : 'Gebruiker';

                previewAdmin.classList.toggle(

                    'active',

                    adminInput.checked

                );

            }

        }



        [

            nameInput,

            emailInput

        ].forEach(function (input) {

            if (input) {

                input.addEventListener(

                    'input',

                    updatePreview

                );

            }

        });



        [

            verifiedInput,

            adminInput

        ].forEach(function (input) {

            if (input) {

                input.addEventListener(

                    'change',

                    updatePreview

                );

            }

        });



        updatePreview();



        // Show / hide passwords.

        document

            .querySelectorAll('[data-toggle-password]')

            .forEach(function (button) {

                button.addEventListener('click', function () {

                    const inputId =

                        button.getAttribute('data-toggle-password');

                    const input =

                        document.getElementById(inputId);

                    if (!input) {

                        return;

                    }

                    const hidden =

                        input.type === 'password';

                    input.type =

                        hidden ? 'text' : 'password';

                    button.textContent =

                        hidden ? 'Verberg' : 'Tonen';

                });

            });



        // Visual password strength.

        const passwordInput =

            document.getElementById('password');

        const strengthBox =

            document.getElementById('passwordStrength');

        const strengthText =

            document.getElementById('passwordStrengthText');



        if (

            passwordInput &&

            strengthBox &&

            strengthText

        ) {

            passwordInput.addEventListener('input', function () {

                const value =

                    passwordInput.value;

                if (!value) {

                    strengthBox.dataset.level = '0';

                    strengthText.textContent =

                        'Optioneel. Minimaal 8 tekens wanneer ingevuld.';

                    return;

                }

                let score = 0;

                if (value.length >= 8) {

                    score++;

                }

                if (

                    /[A-Z]/.test(value) &&

                    /[a-z]/.test(value)

                ) {

                    score++;

                }

                if (/\d/.test(value)) {

                    score++;

                }

                if (

                    /[^A-Za-z0-9]/.test(value) &&

                    value.length >= 10

                ) {

                    score++;

                }

                strengthBox.dataset.level =

                    String(score);

                const labels = {

                    0: 'Te kort.',

                    1: 'Basiswachtwoord.',

                    2: 'Redelijk wachtwoord.',

                    3: 'Sterk wachtwoord.',

                    4: 'Zeer sterk wachtwoord.'

                };

                strengthText.textContent =

                    labels[score] || labels[0];

            });

        }

    });

</script>

@endpush