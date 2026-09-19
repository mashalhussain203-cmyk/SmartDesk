@extends('layouts.site-layout')

@section('title', 'Mijn afbeeldingen | Mashal Studio')

@section(
    'meta_description',
    'Beheer je persoonlijke afbeeldingen, open projecten in de editor, download originelen en beheer opgeslagen versies.'
)

@push('styles')
<style>
    .library-page {
        position: relative;
        min-height: 100vh;
        overflow: hidden;
        padding: 68px 0 110px;
        color: #f7f7f4;
        background:
            radial-gradient(
                circle at 10% 4%,
                rgba(228, 180, 109, .085),
                transparent 29rem
            ),
            radial-gradient(
                circle at 88% 9%,
                rgba(104, 91, 255, .055),
                transparent 34rem
            ),
            linear-gradient(
                180deg,
                #07080b 0%,
                #090b0f 52%,
                #07080b 100%
            );
    }

    .library-page::before {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        opacity: .11;
        background-image:
            linear-gradient(
                rgba(255, 255, 255, .025) 1px,
                transparent 1px
            ),
            linear-gradient(
                90deg,
                rgba(255, 255, 255, .025) 1px,
                transparent 1px
            );
        background-size: 72px 72px;
        mask-image:
            linear-gradient(
                to bottom,
                #000,
                transparent 76%
            );
    }

    .library-shell {
        position: relative;
        z-index: 2;
        width: min(100% - 38px, 1360px);
        margin-inline: auto;
    }

    /*
    |--------------------------------------------------------------------------
    | Header
    |--------------------------------------------------------------------------
    */

    .library-head {
        margin-bottom: 30px;
        display: grid;
        grid-template-columns:
            minmax(0, 1fr)
            auto;
        gap: 38px;
        align-items: end;
    }

    .library-head-copy {
        min-width: 0;
    }

    .library-kicker {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: #d2a15d;
        font-size: 9px;
        font-weight: 950;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .library-kicker::before {
        content: "";
        width: 30px;
        height: 1px;
        background:
            linear-gradient(
                90deg,
                #e1b36c,
                transparent
            );
    }

    .library-head h1 {
        max-width: 920px;
        margin: 14px 0 0;
        color: #f7f8f8;
        font-size: clamp(46px, 5.6vw, 78px);
        line-height: .94;
        letter-spacing: -.068em;
        font-weight: 950;
        text-wrap: balance;
    }

    .library-head h1 span {
        color: #f0ca8b;
    }

    .library-head p {
        max-width: 720px;
        margin: 18px 0 0;
        color: #828993;
        font-size: 12px;
        line-height: 1.85;
    }

    .library-head-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 9px;
        flex-wrap: wrap;
    }

    .library-action {
        min-height: 46px;
        padding: 0 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        flex: 0 0 auto;
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: 12px;
        color: #bdc2c9;
        background: rgba(255, 255, 255, .018);
        text-decoration: none;
        font-size: 9px;
        font-weight: 950;
        transition:
            transform .2s ease,
            border-color .2s ease,
            background .2s ease,
            color .2s ease,
            box-shadow .2s ease;
    }

    .library-action:hover {
        transform: translateY(-2px);
        border-color: rgba(228, 180, 109, .15);
        color: #e6e8eb;
        background: rgba(228, 180, 109, .035);
    }

    .library-action.primary {
        border-color: rgba(228, 180, 109, .18);
        color: #171009;
        background:
            linear-gradient(
                135deg,
                #f1d191,
                #d39a4f
            );
        box-shadow:
            0 16px 38px rgba(228, 180, 109, .14);
    }

    .library-action.primary:hover {
        color: #171009;
        box-shadow:
            0 22px 48px rgba(228, 180, 109, .21);
    }

    /*
    |--------------------------------------------------------------------------
    | Alerts
    |--------------------------------------------------------------------------
    */

    .library-alert {
        margin-bottom: 22px;
        padding: 15px 17px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        border-radius: 15px;
        font-size: 10px;
        line-height: 1.65;
    }

    .library-alert.success {
        border: 1px solid rgba(103, 217, 144, .14);
        color: #a8dfba;
        background: rgba(103, 217, 144, .045);
    }

    .library-alert.error {
        border: 1px solid rgba(244, 125, 125, .15);
        color: #e7a3a3;
        background: rgba(244, 125, 125, .045);
    }

    .library-alert-icon {
        width: 28px;
        height: 28px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        border-radius: 9px;
        background: rgba(255, 255, 255, .035);
        font-size: 12px;
    }

    .library-alert strong {
        display: block;
        margin-bottom: 2px;
        color: #eef0f2;
        font-size: 10px;
    }

    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */

    .library-summary {
        margin-bottom: 20px;
        padding: 15px 17px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        flex-wrap: wrap;
        border: 1px solid rgba(255, 255, 255, .065);
        border-radius: 16px;
        background:
            linear-gradient(
                145deg,
                rgba(255, 255, 255, .025),
                rgba(255, 255, 255, .006)
            ),
            #0b0e12;
    }

    .library-summary-left {
        display: flex;
        align-items: center;
        gap: 10px 18px;
        flex-wrap: wrap;
    }

    .library-summary-item {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #747b85;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .04em;
    }

    .library-summary-item strong {
        color: #d9dde1;
        font-size: 10px;
    }

    .library-summary-right {
        color: #555d67;
        font-size: 8px;
        font-weight: 800;
    }

    /*
    |--------------------------------------------------------------------------
    | Grid
    |--------------------------------------------------------------------------
    */

    .image-grid {
        display: grid;
        grid-template-columns:
            repeat(
                3,
                minmax(0, 1fr)
            );
        gap: 16px;
    }

    /*
    |--------------------------------------------------------------------------
    | Card
    |--------------------------------------------------------------------------
    */

    .image-card {
        position: relative;
        min-width: 0;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, .07);
        border-radius: 23px;
        background:
            linear-gradient(
                145deg,
                rgba(255, 255, 255, .032),
                rgba(255, 255, 255, .006)
            ),
            #0c0f14;
        box-shadow:
            0 18px 52px rgba(0, 0, 0, .14);
        transition:
            transform .24s ease,
            border-color .24s ease,
            box-shadow .24s ease;
    }

    .image-card:hover {
        transform: translateY(-5px);
        border-color: rgba(228, 180, 109, .18);
        box-shadow:
            0 30px 70px rgba(0, 0, 0, .25);
    }

    /*
    |--------------------------------------------------------------------------
    | Preview
    |--------------------------------------------------------------------------
    */

    .image-preview {
        position: relative;
        aspect-ratio: 16 / 10;
        overflow: hidden;
        background:
            radial-gradient(
                circle at 72% 25%,
                rgba(228, 180, 109, .13),
                transparent 11rem
            ),
            radial-gradient(
                circle at 26% 74%,
                rgba(90, 105, 255, .10),
                transparent 12rem
            ),
            #10141a;
    }

    .image-preview-link {
        position: absolute;
        inset: 0;
        display: block;
        text-decoration: none;
    }

    .library-image {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
        transition:
            transform .35s ease,
            filter .35s ease;
    }

    .image-card:hover .library-image {
        transform: scale(1.028);
    }

    .image-preview::after {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background:
            linear-gradient(
                180deg,
                transparent 48%,
                rgba(5, 7, 10, .46)
            );
    }

    .image-preview-top {
        position: absolute;
        z-index: 3;
        top: 13px;
        left: 13px;
        right: 13px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        pointer-events: none;
    }

    .image-format-badge,
    .image-version-badge {
        min-height: 28px;
        padding: 0 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255, 255, 255, .09);
        border-radius: 9px;
        color: #bfc4ca;
        background: rgba(7, 9, 12, .72);
        backdrop-filter: blur(12px);
        font-size: 7px;
        font-weight: 950;
        letter-spacing: .07em;
        text-transform: uppercase;
    }

    .image-version-badge.has-versions {
        border-color: rgba(228, 180, 109, .15);
        color: #e0b978;
        background: rgba(28, 21, 12, .72);
    }

    .image-dimensions {
        position: absolute;
        z-index: 3;
        left: 13px;
        bottom: 13px;
        min-height: 28px;
        padding: 0 9px;
        display: inline-flex;
        align-items: center;
        border: 1px solid rgba(255, 255, 255, .09);
        border-radius: 9px;
        color: #a3a9b1;
        background: rgba(7, 9, 12, .76);
        backdrop-filter: blur(12px);
        font-size: 8px;
        font-weight: 900;
    }

    .image-open-indicator {
        position: absolute;
        z-index: 3;
        right: 13px;
        bottom: 13px;
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(255, 255, 255, .09);
        border-radius: 10px;
        color: #d7dade;
        background: rgba(7, 9, 12, .74);
        backdrop-filter: blur(12px);
        font-size: 13px;
        opacity: 0;
        transform: translateY(4px);
        transition:
            opacity .22s ease,
            transform .22s ease;
    }

    .image-card:hover .image-open-indicator {
        opacity: 1;
        transform: translateY(0);
    }

    /*
    |--------------------------------------------------------------------------
    | Card body
    |--------------------------------------------------------------------------
    */

    .image-card-body {
        padding: 18px;
    }

    .image-card-heading {
        min-width: 0;
    }

    .image-card-heading h2 {
        margin: 0;
        overflow: hidden;
        color: #e1e4e7;
        font-size: 14px;
        line-height: 1.3;
        letter-spacing: -.025em;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .image-created {
        margin-top: 5px;
        color: #555d67;
        font-size: 8px;
        font-weight: 750;
    }

    /*
    |--------------------------------------------------------------------------
    | Metadata
    |--------------------------------------------------------------------------
    */

    .image-meta {
        margin-top: 15px;
        display: grid;
        grid-template-columns:
            repeat(
                2,
                minmax(0, 1fr)
            );
        gap: 8px;
    }

    .image-meta-item {
        min-width: 0;
        padding: 10px;
        border: 1px solid rgba(255, 255, 255, .05);
        border-radius: 11px;
        background: rgba(255, 255, 255, .012);
    }

    .image-meta-item small {
        display: block;
        color: #4e5660;
        font-size: 6px;
        font-weight: 950;
        letter-spacing: .10em;
        text-transform: uppercase;
    }

    .image-meta-item strong {
        display: block;
        margin-top: 4px;
        overflow: hidden;
        color: #9ca2aa;
        font-size: 9px;
        font-weight: 850;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    .image-card-actions {
        margin-top: 16px;
        padding-top: 15px;
        display: grid;
        grid-template-columns:
            minmax(0, 1fr)
            auto
            auto;
        gap: 7px;
        align-items: stretch;
        border-top: 1px solid rgba(255, 255, 255, .055);
    }

    .image-card-actions form {
        margin: 0;
    }

    .image-edit,
    .image-download,
    .image-delete {
        min-height: 40px;
        padding: 0 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border-radius: 10px;
        text-decoration: none;
        font-size: 8px;
        font-weight: 950;
        white-space: nowrap;
        transition:
            transform .18s ease,
            border-color .18s ease,
            color .18s ease,
            background .18s ease;
    }

    .image-edit:hover,
    .image-download:hover,
    .image-delete:hover {
        transform: translateY(-1px);
    }

    .image-edit {
        border: 1px solid rgba(214, 161, 89, .18);
        color: #171009;
        background:
            linear-gradient(
                135deg,
                #e8bd78,
                #cd9145
            );
    }

    .image-download {
        border: 1px solid rgba(255, 255, 255, .075);
        color: #8e959f;
        background: rgba(255, 255, 255, .018);
    }

    .image-download:hover {
        border-color: rgba(255, 255, 255, .13);
        color: #d2d6da;
        background: rgba(255, 255, 255, .035);
    }

    .image-delete {
        width: 100%;
        border: 1px solid rgba(255, 105, 105, .10);
        color: #c97f7f;
        background: rgba(255, 105, 105, .025);
        cursor: pointer;
    }

    .image-delete:hover {
        border-color: rgba(255, 105, 105, .20);
        color: #ef9a9a;
        background: rgba(255, 105, 105, .045);
    }

    /*
    |--------------------------------------------------------------------------
    | Empty state
    |--------------------------------------------------------------------------
    */

    .empty-library {
        position: relative;
        overflow: hidden;
        padding: 82px 30px;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, .07);
        border-radius: 27px;
        background:
            radial-gradient(
                circle at 50% 0%,
                rgba(228, 180, 109, .08),
                transparent 24rem
            ),
            rgba(255, 255, 255, .012);
    }

    .empty-library::before {
        content: "";
        position: absolute;
        width: 260px;
        height: 260px;
        left: 50%;
        top: -170px;
        transform: translateX(-50%);
        border: 1px solid rgba(228, 180, 109, .08);
        border-radius: 50%;
        box-shadow:
            0 0 0 55px rgba(228, 180, 109, .012),
            0 0 0 110px rgba(228, 180, 109, .007);
    }

    .empty-library-icon {
        position: relative;
        z-index: 2;
        width: 58px;
        height: 58px;
        margin: 0 auto 20px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(228, 180, 109, .14);
        border-radius: 17px;
        color: #dfb472;
        background: rgba(228, 180, 109, .055);
        font-size: 21px;
    }

    .empty-library h2 {
        position: relative;
        z-index: 2;
        margin: 0;
        color: #eceef0;
        font-size: clamp(30px, 4vw, 46px);
        line-height: 1;
        letter-spacing: -.05em;
    }

    .empty-library p {
        position: relative;
        z-index: 2;
        max-width: 580px;
        margin: 15px auto 25px;
        color: #737a84;
        font-size: 11px;
        line-height: 1.8;
    }

    .empty-library .library-action {
        position: relative;
        z-index: 2;
    }

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    .pagination-wrap {
        margin-top: 32px;
        padding-top: 22px;
        border-top: 1px solid rgba(255, 255, 255, .045);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessibility
    |--------------------------------------------------------------------------
    */

    .image-edit:focus-visible,
    .image-download:focus-visible,
    .image-delete:focus-visible,
    .library-action:focus-visible,
    .image-preview-link:focus-visible {
        outline: 2px solid rgba(241, 209, 145, .82);
        outline-offset: 3px;
    }

    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1080px) {
        .image-grid {
            grid-template-columns:
                repeat(
                    2,
                    minmax(0, 1fr)
                );
        }
    }

    @media (max-width: 820px) {
        .library-page {
            padding-top: 48px;
        }

        .library-head {
            grid-template-columns: 1fr;
            gap: 24px;
        }

        .library-head-actions {
            justify-content: flex-start;
        }
    }

    @media (max-width: 640px) {
        .library-shell {
            width: min(100% - 24px, 1360px);
        }

        .library-page {
            padding-top: 38px;
            padding-bottom: 80px;
        }

        .library-head h1 {
            font-size: clamp(42px, 15vw, 62px);
        }

        .library-head p {
            font-size: 11px;
        }

        .library-head-actions {
            width: 100%;
        }

        .library-action {
            flex: 1 1 auto;
        }

        .image-grid {
            grid-template-columns: 1fr;
        }

        .image-card-actions {
            grid-template-columns:
                minmax(0, 1fr)
                auto;
        }

        .image-delete-form {
            grid-column: 1 / -1;
        }

        .image-delete {
            min-width: 100%;
        }

        .library-summary {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    @media (max-width: 420px) {
        .image-meta {
            grid-template-columns: 1fr;
        }

        .image-card-actions {
            grid-template-columns: 1fr;
        }

        .image-download,
        .image-delete {
            width: 100%;
        }

        .image-delete-form {
            grid-column: auto;
        }
    }
</style>
@endpush


@section('content')

<div class="library-page">
    <div class="library-shell">

        {{-- ============================================================
             Header
        ============================================================ --}}

        <header class="library-head">
            <div class="library-head-copy">
                <span class="library-kicker">
                    Private image library
                </span>

                <h1>
                    Mijn
                    <span>afbeeldingen.</span>
                </h1>

                <p>
                    Beheer hier alle afbeeldingen die aan jouw account zijn
                    gekoppeld. Open een project in de editor, download het
                    origineel of verwijder een compleet project inclusief
                    alle opgeslagen bewerkingen.
                </p>
            </div>

            <div class="library-head-actions">
                <a
                    class="library-action"
                    href="{{ route('home') }}"
                >
                    ← Home
                </a>

                <a
                    class="library-action primary"
                    href="{{ route('home') }}#upload"
                >
                    + Nieuwe afbeelding
                </a>
            </div>
        </header>


        {{-- ============================================================
             Flash messages
        ============================================================ --}}

        @if (session('status'))
            <div
                class="library-alert success"
                role="status"
            >
                <div class="library-alert-icon">
                    ✓
                </div>

                <div>
                    <strong>
                        Gelukt
                    </strong>

                    {{ session('status') }}
                </div>
            </div>
        @endif


        @if (session('error'))
            <div
                class="library-alert error"
                role="alert"
            >
                <div class="library-alert-icon">
                    !
                </div>

                <div>
                    <strong>
                        Er ging iets mis
                    </strong>

                    {{ session('error') }}
                </div>
            </div>
        @endif


        {{-- ============================================================
             Library
        ============================================================ --}}

        @if ($images->count())

            {{-- ========================================================
                 Summary
            ======================================================== --}}

            <section class="library-summary">
                <div class="library-summary-left">
                    <div class="library-summary-item">
                        Projecten

                        <strong>
                            {{ $images->total() }}
                        </strong>
                    </div>

                    <div class="library-summary-item">
                        Deze pagina

                        <strong>
                            {{ $images->count() }}
                        </strong>
                    </div>

                    <div class="library-summary-item">
                        Pagina

                        <strong>
                            {{ $images->currentPage() }}
                            /
                            {{ $images->lastPage() }}
                        </strong>
                    </div>
                </div>

                <div class="library-summary-right">
                    Alleen jij kunt deze bestanden bekijken.
                </div>
            </section>


            {{-- ========================================================
                 Image grid
            ======================================================== --}}

            <div class="image-grid">

                @foreach ($images as $image)

                    @php
                        $versionCount = (int) ($image->versions_count ?? 0);

                        $formatLabel = $image->format_label
                            ?? strtoupper(
                                str_replace(
                                    'image/',
                                    '',
                                    (string) $image->mime_type
                                )
                            );

                        $fileSizeLabel = $image->formatted_file_size
                            ?? number_format(
                                ((int) ($image->file_size ?? 0)) / 1024,
                                1
                            ) . ' KB';

                        $dimensionsLabel = $image->dimensions_label
                            ?? sprintf(
                                '%s × %s',
                                $image->width ?? '?',
                                $image->height ?? '?'
                            );

                        $displayName = $image->display_name
                            ?? $image->original_name
                            ?? ('Afbeelding #' . $image->id);
                    @endphp


                    <article class="image-card">

                        {{-- =================================================
                             Preview
                        ================================================= --}}

                        <div class="image-preview">

                            <a
                                class="image-preview-link"
                                href="{{ route('images.editor', $image) }}"
                                aria-label="Open {{ $displayName }} in de editor"
                            >
                                <img
                                    class="library-image"
                                    src="{{ route('images.file', $image) }}"
                                    alt="{{ $displayName }}"
                                    loading="lazy"
                                    decoding="async"
                                >
                            </a>


                            <div class="image-preview-top">
                                <span class="image-format-badge">
                                    {{ $formatLabel }}
                                </span>

                                <span
                                    class="
                                        image-version-badge
                                        {{ $versionCount > 0 ? 'has-versions' : '' }}
                                    "
                                >
                                    @if ($versionCount === 1)
                                        1 versie
                                    @else
                                        {{ $versionCount }} versies
                                    @endif
                                </span>
                            </div>


                            <span class="image-dimensions">
                                {{ $dimensionsLabel }}
                            </span>


                            <span
                                class="image-open-indicator"
                                aria-hidden="true"
                            >
                                ↗
                            </span>

                        </div>


                        {{-- =================================================
                             Body
                        ================================================= --}}

                        <div class="image-card-body">

                            <div class="image-card-heading">
                                <h2 title="{{ $displayName }}">
                                    {{ $displayName }}
                                </h2>

                                <div class="image-created">
                                    Toegevoegd
                                    {{ $image->created_at?->format('d-m-Y \o\m H:i') }}
                                </div>
                            </div>


                            {{-- =================================================
                                 Metadata
                            ================================================= --}}

                            <div class="image-meta">

                                <div class="image-meta-item">
                                    <small>
                                        Formaat
                                    </small>

                                    <strong>
                                        {{ $formatLabel }}
                                    </strong>
                                </div>


                                <div class="image-meta-item">
                                    <small>
                                        Bestandsgrootte
                                    </small>

                                    <strong>
                                        {{ $fileSizeLabel }}
                                    </strong>
                                </div>


                                <div class="image-meta-item">
                                    <small>
                                        Afmetingen
                                    </small>

                                    <strong>
                                        {{ $dimensionsLabel }}
                                    </strong>
                                </div>


                                <div class="image-meta-item">
                                    <small>
                                        Bewerkingen
                                    </small>

                                    <strong>
                                        {{ $versionCount }}
                                        {{ $versionCount === 1 ? 'versie' : 'versies' }}
                                    </strong>
                                </div>

                            </div>


                            {{-- =================================================
                                 Actions
                            ================================================= --}}

                            <div class="image-card-actions">

                                <a
                                    class="image-edit"
                                    href="{{ route('images.editor', $image) }}"
                                >
                                    Open editor
                                </a>


                                <a
                                    class="image-download"
                                    href="{{ route('images.download', $image) }}"
                                    title="Download origineel"
                                >
                                    ↓
                                    Download
                                </a>


                                <form
                                    class="image-delete-form"
                                    method="POST"
                                    action="{{ route('images.destroy', $image) }}"
                                    onsubmit="
                                        return confirm(
                                            'Weet je zeker dat je deze afbeelding en alle opgeslagen versies wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt.'
                                        );
                                    "
                                >
                                    @csrf

                                    @method('DELETE')

                                    <button
                                        class="image-delete"
                                        type="submit"
                                        title="Project verwijderen"
                                    >
                                        Verwijderen
                                    </button>
                                </form>

                            </div>

                        </div>

                    </article>

                @endforeach

            </div>


            {{-- ========================================================
                 Pagination
            ======================================================== --}}

            @if ($images->hasPages())
                <div class="pagination-wrap">
                    {{ $images->links() }}
                </div>
            @endif

        @else

            {{-- ========================================================
                 Empty state
            ======================================================== --}}

            <section class="empty-library">

                <div
                    class="empty-library-icon"
                    aria-hidden="true"
                >
                    ◫
                </div>

                <h2>
                    Je bibliotheek is nog leeg.
                </h2>

                <p>
                    Upload je eerste afbeelding om een nieuw project te
                    starten. Na het uploaden wordt het origineel privé aan
                    jouw account gekoppeld en kun je het openen in de editor,
                    bewerken en als meerdere versies opslaan.
                </p>

                <a
                    class="library-action primary"
                    href="{{ route('home') }}#upload"
                >
                    + Eerste afbeelding uploaden
                </a>

            </section>

        @endif

    </div>
</div>

@endsection

