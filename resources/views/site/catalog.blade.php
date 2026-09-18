@extends('layouts.site-layout')

@section('title', 'Mashal | Collectie')

@push('styles')

<style>

    .catalog-page {

        position: relative;

        overflow: hidden;

        padding: 88px 0 110px;

        background:

            radial-gradient(

                circle at 12% 8%,

                rgba(215, 164, 95, .08),

                transparent 22rem

            ),

            radial-gradient(

                circle at 88% 22%,

                rgba(255, 255, 255, .03),

                transparent 26rem

            ),

            #08090b;

    }

    .catalog-page::before {

        content: "MASHAL";

        position: absolute;

        right: -34px;

        top: 46px;

        color: rgba(255,255,255,.018);

        font-size: clamp(120px, 19vw, 300px);

        font-weight: 950;

        line-height: .8;

        letter-spacing: -.08em;

        pointer-events: none;

        user-select: none;

    }

    .catalog-shell {

        position: relative;

        z-index: 2;

        width: min(100% - 48px, 1240px);

        margin-inline: auto;

    }

    /* ========================================================= */

    /* HEADER                                                     */

    /* ========================================================= */

    .catalog-header {

        display: grid;

        grid-template-columns: minmax(0, 1fr) auto;

        align-items: end;

        gap: 40px;

        margin-bottom: 46px;

    }

    .catalog-kicker {

        display: inline-flex;

        align-items: center;

        gap: 10px;

        margin-bottom: 14px;

        color: #d7a45f;

        font-size: 9px;

        font-weight: 900;

        letter-spacing: .22em;

        text-transform: uppercase;

    }

    .catalog-kicker::before {

        content: "";

        width: 30px;

        height: 1px;

        background: #d7a45f;

    }

    .catalog-title {

        margin: 0;

        max-width: 780px;

        color: #ffffff;

        font-size: clamp(48px, 6vw, 82px);

        line-height: .94;

        letter-spacing: -.065em;

        font-weight: 950;

    }

    .catalog-title span {

        color: #efc985;

    }

    .catalog-description {

        max-width: 650px;

        margin: 20px 0 0;

        color: #858b92;

        font-size: 14px;

        line-height: 1.85;

    }

    .catalog-counter {

        min-width: 170px;

        padding: 18px 20px;

        border: 1px solid rgba(255,255,255,.09);

        border-radius: 18px;

        background:

            linear-gradient(

                145deg,

                rgba(255,255,255,.055),

                rgba(255,255,255,.018)

            );

        box-shadow: 0 16px 40px rgba(0,0,0,.18);

        backdrop-filter: blur(16px);

    }

    .catalog-counter small {

        display: block;

        margin-bottom: 4px;

        color: #8c9299;

        font-size: 9px;

        font-weight: 800;

        letter-spacing: .14em;

        text-transform: uppercase;

    }

    .catalog-counter strong {

        color: #ffffff;

        font-size: 26px;

        line-height: 1;

    }

    /* ========================================================= */

    /* TOOLBAR                                                    */

    /* ========================================================= */

    .catalog-toolbar {

        margin-bottom: 24px;

        padding: 14px 16px;

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 18px;

        flex-wrap: wrap;

        border: 1px solid rgba(255,255,255,.07);

        border-radius: 18px;

        background: rgba(255,255,255,.025);

    }

    .catalog-toolbar-text {

        color: #777d84;

        font-size: 11px;

        font-weight: 700;

        letter-spacing: .06em;

        text-transform: uppercase;

    }

    .catalog-toolbar-badges {

        display: flex;

        align-items: center;

        gap: 8px;

        flex-wrap: wrap;

    }

    .catalog-badge {

        padding: 7px 10px;

        border: 1px solid rgba(215,164,95,.16);

        border-radius: 999px;

        background: rgba(215,164,95,.055);

        color: #cba76f;

        font-size: 9px;

        font-weight: 850;

        letter-spacing: .08em;

        text-transform: uppercase;

    }

    /* ========================================================= */

    /* GRID                                                       */

    /* ========================================================= */

    .catalog-grid {

        display: grid;

        grid-template-columns: repeat(2, minmax(0, 1fr));

        gap: 22px;

    }

    .vehicle-card {

        position: relative;

        overflow: hidden;

        border: 1px solid rgba(255,255,255,.08);

        border-radius: 28px;

        background:

            linear-gradient(

                155deg,

                rgba(255,255,255,.045),

                rgba(255,255,255,.016)

            );

        box-shadow: 0 18px 44px rgba(0,0,0,.18);

        transition:

            transform .3s cubic-bezier(.2,.7,.2,1),

            border-color .3s ease,

            box-shadow .3s ease;

    }

    .vehicle-card::before {

        content: "";

        position: absolute;

        left: 0;

        top: 0;

        width: 100%;

        height: 2px;

        z-index: 5;

        opacity: 0;

        background:

            linear-gradient(

                90deg,

                transparent,

                rgba(215,164,95,.85),

                transparent

            );

        transition: opacity .3s ease;

    }

    .vehicle-card:hover {

        transform: translateY(-8px);

        border-color: rgba(215,164,95,.24);

        box-shadow: 0 30px 80px rgba(0,0,0,.34);

    }

    .vehicle-card:hover::before {

        opacity: 1;

    }

    .vehicle-media {

        position: relative;

        aspect-ratio: 16 / 9.5;

        overflow: hidden;

        background: #0b0d10;

    }

    .vehicle-media a {

        display: block;

        width: 100%;

        height: 100%;

    }

    .vehicle-media img {

        width: 100%;

        height: 100%;

        object-fit: cover;

        transform: scale(1.01);

        transition:

            transform .6s cubic-bezier(.2,.7,.2,1),

            filter .3s ease;

    }

    .vehicle-card:hover .vehicle-media img {

        transform: scale(1.065);

        filter: saturate(1.04) contrast(1.02);

    }

    .vehicle-media::after {

        content: "";

        position: absolute;

        inset: 0;

        pointer-events: none;

        background:

            linear-gradient(

                180deg,

                rgba(0,0,0,.03) 0%,

                rgba(0,0,0,.02) 48%,

                rgba(8,9,11,.78) 100%

            );

    }

    .vehicle-year {

        position: absolute;

        left: 18px;

        top: 18px;

        z-index: 3;

        padding: 8px 11px;

        border: 1px solid rgba(255,255,255,.14);

        border-radius: 999px;

        background: rgba(8,9,11,.64);

        backdrop-filter: blur(12px);

        color: #f0c983;

        font-size: 9px;

        font-weight: 900;

        letter-spacing: .12em;

    }

    .vehicle-brand {

        position: absolute;

        right: 18px;

        top: 18px;

        z-index: 3;

        color: rgba(255,255,255,.88);

        font-size: 10px;

        font-weight: 900;

        letter-spacing: .18em;

        text-transform: uppercase;

        text-shadow: 0 2px 14px rgba(0,0,0,.6);

    }

    /* ========================================================= */

    /* CARD BODY                                                  */

    /* ========================================================= */

    .vehicle-body {

        padding: 24px;

    }

    .vehicle-meta {

        display: flex;

        align-items: center;

        gap: 8px;

        flex-wrap: wrap;

        margin-bottom: 10px;

        color: #a37743;

        font-size: 9px;

        font-weight: 900;

        letter-spacing: .14em;

        text-transform: uppercase;

    }

    .vehicle-meta-dot {

        width: 3px;

        height: 3px;

        border-radius: 50%;

        background: #5e6268;

    }

    .vehicle-name {

        margin: 0;

        color: #ffffff;

        font-size: clamp(25px, 2.4vw, 32px);

        line-height: 1.05;

        letter-spacing: -.045em;

    }

    .vehicle-summary {

        min-height: 50px;

        margin: 12px 0 0;

        color: #7f858d;

        font-size: 13px;

        line-height: 1.8;

    }

    .vehicle-divider {

        margin: 22px 0;

        height: 1px;

        background:

            linear-gradient(

                90deg,

                rgba(255,255,255,.08),

                rgba(255,255,255,.035),

                transparent

            );

    }

    .vehicle-bottom {

        display: flex;

        align-items: end;

        justify-content: space-between;

        gap: 18px;

    }

    .vehicle-price small {

        display: block;

        margin-bottom: 2px;

        color: #666c73;

        font-size: 9px;

        font-weight: 800;

        letter-spacing: .12em;

        text-transform: uppercase;

    }

    .vehicle-price strong {

        display: block;

        color: #ffffff;

        font-size: clamp(23px, 2.2vw, 29px);

        line-height: 1.1;

        letter-spacing: -.035em;

    }

    .vehicle-actions {

        display: flex;

        align-items: center;

        gap: 8px;

        flex-wrap: wrap;

        justify-content: flex-end;

    }

    .vehicle-link,

    .vehicle-cart {

        min-height: 43px;

        padding: 0 15px;

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 8px;

        border-radius: 999px;

        text-decoration: none;

        cursor: pointer;

        font-size: 10px;

        font-weight: 900;

        letter-spacing: .03em;

        transition:

            transform .2s ease,

            background .2s ease,

            border-color .2s ease,

            box-shadow .2s ease;

    }

    .vehicle-link {

        border: 1px solid rgba(215,164,95,.18);

        background: rgba(215,164,95,.075);

        color: #e8bc76;

    }

    .vehicle-link:hover {

        transform: translateY(-2px);

        border-color: rgba(215,164,95,.34);

        background: rgba(215,164,95,.12);

    }

    .vehicle-cart {

        border: 0;

        background:

            linear-gradient(

                135deg,

                #f1cc8b,

                #cc9550

            );

        color: #14100b;

        box-shadow: 0 12px 26px rgba(215,164,95,.15);

    }

    .vehicle-cart:hover {

        transform: translateY(-2px);

        box-shadow: 0 18px 36px rgba(215,164,95,.23);

    }

    /* ========================================================= */

    /* EMPTY STATE                                                */

    /* ========================================================= */


    /* ========================================================= */
    /* FAVORIETEN                                                */
    /* ========================================================= */

    .vehicle-favorite-form {
        margin: 0;
        display: inline-flex;
    }

    .vehicle-favorite {
        width: 43px;
        height: 43px;
        flex: 0 0 43px;
        padding: 0;
        display: inline-grid;
        place-items: center;
        border: 1px solid rgba(255,255,255,.11);
        border-radius: 50%;
        background: rgba(255,255,255,.035);
        color: #a1a6ac;
        text-decoration: none;
        cursor: pointer;
        font-size: 19px;
        line-height: 1;
        transition:
            transform .2s ease,
            background .2s ease,
            border-color .2s ease,
            color .2s ease,
            box-shadow .2s ease;
    }

    .vehicle-favorite:hover {
        transform: translateY(-2px);
        border-color: rgba(215,164,95,.34);
        background: rgba(215,164,95,.09);
        color: #f1c983;
    }

    .vehicle-favorite.is-favorite {
        border-color: rgba(215,164,95,.38);
        background:
            linear-gradient(
                145deg,
                rgba(241,201,131,.20),
                rgba(215,164,95,.10)
            );
        color: #f1c983;
        box-shadow:
            0 10px 26px rgba(215,164,95,.13);
    }

    .vehicle-favorite.is-favorite:hover {
        background:
            linear-gradient(
                145deg,
                rgba(241,201,131,.27),
                rgba(215,164,95,.14)
            );
    }

    .catalog-empty {

        padding: 64px 28px;

        text-align: center;

        border: 1px solid rgba(255,255,255,.08);

        border-radius: 28px;

        background:

            linear-gradient(

                145deg,

                rgba(255,255,255,.035),

                rgba(255,255,255,.012)

            );

    }

    .catalog-empty-icon {

        width: 62px;

        height: 62px;

        margin: 0 auto 20px;

        display: grid;

        place-items: center;

        border: 1px solid rgba(215,164,95,.18);

        border-radius: 50%;

        background: rgba(215,164,95,.07);

        color: #e2b66e;

        font-size: 24px;

    }

    .catalog-empty h3 {

        margin: 0 0 8px;

        color: #ffffff;

        font-size: 28px;

        letter-spacing: -.04em;

    }

    .catalog-empty p {

        max-width: 520px;

        margin: 0 auto;

        color: #7f858c;

        font-size: 13px;

        line-height: 1.8;

    }

    /* ========================================================= */

    /* BOTTOM EXPERIENCE STRIP                                    */

    /* ========================================================= */

    .catalog-assurance {

        margin-top: 34px;

        padding: 20px 22px;

        display: grid;

        grid-template-columns: repeat(3, 1fr);

        gap: 10px;

        border: 1px solid rgba(255,255,255,.07);

        border-radius: 20px;

        background: rgba(255,255,255,.02);

    }

    .assurance-item {

        padding: 10px 14px;

        border-right: 1px solid rgba(255,255,255,.06);

    }

    .assurance-item:last-child {

        border-right: 0;

    }

    .assurance-item small {

        display: block;

        margin-bottom: 4px;

        color: #a47741;

        font-size: 8px;

        font-weight: 900;

        letter-spacing: .15em;

        text-transform: uppercase;

    }

    .assurance-item strong {

        color: #d8d8d5;

        font-size: 12px;

        font-weight: 750;

    }

    /* ========================================================= */

    /* RESPONSIVE                                                 */

    /* ========================================================= */

    @media (max-width: 980px) {

        .catalog-header {

            grid-template-columns: 1fr;

        }

        .catalog-counter {

            width: fit-content;

        }

        .catalog-grid {

            grid-template-columns: 1fr;

        }

    }

    @media (max-width: 700px) {

        .catalog-page {

            padding: 64px 0 80px;

        }

        .catalog-shell {

            width: min(100% - 32px, 1240px);

        }

        .catalog-title {

            font-size: clamp(46px, 14vw, 64px);

        }

        .vehicle-body {

            padding: 20px;

        }

        .vehicle-bottom {

            align-items: stretch;

            flex-direction: column;

        }

        .vehicle-actions {

            justify-content: stretch;

        }

        .vehicle-link,

        .vehicle-cart {

            flex: 1;

        }

        .vehicle-actions form {

            flex: 1;

        }

        .vehicle-actions form .vehicle-cart {

            width: 100%;

        }

        .vehicle-favorite-form {
            flex: 0 0 auto !important;
        }

        .vehicle-favorite {
            width: 43px;
            min-width: 43px;
        }

        .catalog-assurance {

            grid-template-columns: 1fr;

        }

        .assurance-item {

            border-right: 0;

            border-bottom: 1px solid rgba(255,255,255,.06);

        }

        .assurance-item:last-child {

            border-bottom: 0;

        }

    }

</style>

@endpush



@section('content')

@php
    $favoriteCarIds = [];

    if (auth()->check()) {
        $favoriteCarIds = \App\Models\Favorite::query()
            ->where('user_id', auth()->id())
            ->pluck('car_id')
            ->map(fn ($carId) => (int) $carId)
            ->all();
    }
@endphp

<section class="catalog-page">

    <div class="catalog-shell">

        {{-- ========================================================= --}}

        {{-- HEADER                                                     --}}

        {{-- ========================================================= --}}

        <header class="catalog-header">

            <div>

                <span class="catalog-kicker">

                    Mashal Collection

                </span>

                <h1 class="catalog-title">

                    Niet zomaar auto's.

                    <span>

                        Een selectie met karakter.

                    </span>

                </h1>

                <p class="catalog-description">

                    Ontdek zorgvuldig geselecteerde modellen

                    waarin design, comfort en rijbeleving samenkomen.

                    Kies jouw volgende auto in een omgeving

                    die net zo premium aanvoelt als de collectie zelf.

                </p>

            </div>



            <div class="catalog-counter">

                <small>

                    Beschikbaar

                </small>

                <strong>

                    {{ count($cars ?? []) }}

                    {{ count($cars ?? []) === 1 ? 'model' : 'modellen' }}

                </strong>

            </div>

        </header>



        {{-- ========================================================= --}}

        {{-- TOOLBAR                                                    --}}

        {{-- ========================================================= --}}

        <div class="catalog-toolbar">

            <div class="catalog-toolbar-text">

                Selected by Mashal Automotive

            </div>

            <div class="catalog-toolbar-badges">

                <span class="catalog-badge">

                    Premium

                </span>

                <span class="catalog-badge">

                    Curated

                </span>

                <span class="catalog-badge">

                    Secure checkout

                </span>

            </div>

        </div>



        {{-- ========================================================= --}}

        {{-- CATALOG                                                    --}}

        {{-- ========================================================= --}}

        @if (empty($cars))

            <div class="catalog-empty">

                <div class="catalog-empty-icon">

                    M

                </div>

                <h3>

                    De collectie wordt voorbereid.

                </h3>

                <p>

                    Er zijn momenteel geen auto's beschikbaar.

                    Zodra nieuwe modellen aan de Mashal-collectie

                    worden toegevoegd, verschijnen ze hier.

                </p>

            </div>

        @else

            <div class="catalog-grid">

                @foreach ($cars as $car)

                    <article class="vehicle-card">

                        {{-- IMAGE --}}

                        <div class="vehicle-media">

                            <a

                                href="{{ route('car', ['id' => $car['id']]) }}"

                                aria-label="Bekijk {{ $car['brand'] }} {{ $car['model'] }}"

                            >

                                <img

                                    src="{{ $car['image'] }}"

                                    alt="{{ $car['brand'] }} {{ $car['model'] }}"

                                    loading="lazy"

                                >

                            </a>



                            <span class="vehicle-year">

                                {{ $car['year'] }}

                            </span>



                            <span class="vehicle-brand">

                                {{ $car['brand'] }}

                            </span>

                        </div>



                        {{-- CONTENT --}}

                        <div class="vehicle-body">

                            <div class="vehicle-meta">

                                <span>

                                    {{ $car['type'] }}

                                </span>

                                <span class="vehicle-meta-dot"></span>

                                <span>

                                    {{ $car['fuel'] }}

                                </span>

                            </div>



                            <h2 class="vehicle-name">

                                {{ $car['brand'] }}

                                {{ $car['model'] }}

                            </h2>



                            <p class="vehicle-summary">

                                {{ $car['summary'] }}

                            </p>



                            <div class="vehicle-divider"></div>



                            <div class="vehicle-bottom">

                                <div class="vehicle-price">

                                    <small>

                                        Vanaf

                                    </small>

                                    <strong>

                                        €{{ number_format($car['price'], 0, ',', '.') }}

                                    </strong>

                                </div>



                                <div class="vehicle-actions">

                                    <a

                                        class="vehicle-link"

                                        href="{{ route('car', ['id' => $car['id']]) }}"

                                    >

                                        Bekijk model

                                        <span aria-hidden="true">→</span>

                                    </a>



                                    @php
                                        $isFavorite = in_array(
                                            (int) $car['id'],
                                            $favoriteCarIds,
                                            true
                                        );
                                    @endphp

                                    @auth
                                        <form
                                            class="vehicle-favorite-form"
                                            method="POST"
                                            action="{{ $isFavorite
                                                ? route('favorites.destroy', ['id' => $car['id']])
                                                : route('favorites.store', ['id' => $car['id']])
                                            }}"
                                        >
                                            @csrf

                                            @if ($isFavorite)
                                                @method('DELETE')
                                            @endif

                                            <button
                                                class="vehicle-favorite {{ $isFavorite ? 'is-favorite' : '' }}"
                                                type="submit"
                                                aria-label="{{ $isFavorite
                                                    ? 'Verwijder ' . $car['brand'] . ' ' . $car['model'] . ' uit favorieten'
                                                    : 'Voeg ' . $car['brand'] . ' ' . $car['model'] . ' toe aan favorieten'
                                                }}"
                                                title="{{ $isFavorite ? 'Verwijder uit favorieten' : 'Opslaan als favoriet' }}"
                                            >
                                                <span aria-hidden="true">
                                                    {{ $isFavorite ? '♥' : '♡' }}
                                                </span>
                                            </button>
                                        </form>
                                    @else
                                        <a
                                            class="vehicle-favorite"
                                            href="{{ route('login') }}"
                                            aria-label="Log in om {{ $car['brand'] }} {{ $car['model'] }} als favoriet op te slaan"
                                            title="Log in om op te slaan"
                                        >
                                            <span aria-hidden="true">
                                                ♡
                                            </span>
                                        </a>
                                    @endauth


                                    <form

                                        method="POST"

                                        action="{{ route('cart.add', ['id' => $car['id']]) }}"

                                        style="margin: 0;"

                                    >

                                        @csrf

                                        <button

                                            class="vehicle-cart"

                                            type="submit"

                                        >

                                            Toevoegen

                                        </button>

                                    </form>

                                </div>

                            </div>

                        </div>

                    </article>

                @endforeach

            </div>

        @endif



        {{-- ========================================================= --}}

        {{-- ASSURANCE                                                  --}}

        {{-- ========================================================= --}}

        <div class="catalog-assurance">

            <div class="assurance-item">

                <small>

                    Account

                </small>

                <strong>

                    Veilig beheer met e-mailverificatie

                </strong>

            </div>



            <div class="assurance-item">

                <small>

                    Selectie

                </small>

                <strong>

                    Duidelijke voertuiggegevens en prijzen

                </strong>

            </div>



            <div class="assurance-item">

                <small>

                    Bestelling

                </small>

                <strong>

                    Bevestiging direct na checkout

                </strong>

            </div>

        </div>

    </div>

</section>

@endsection