@extends('layouts.site-layout')

@section('title', 'Mashal | Mijn favorieten')

@push('styles')
<style>
    .favorites-page {
        position: relative;
        overflow: hidden;
        min-height: 72vh;
        padding: 88px 0 110px;
        background:
            radial-gradient(
                circle at 12% 8%,
                rgba(215, 164, 95, .09),
                transparent 23rem
            ),
            radial-gradient(
                circle at 88% 18%,
                rgba(255, 255, 255, .025),
                transparent 28rem
            ),
            #08090b;
    }

    .favorites-page::before {
        content: "FAVORITES";
        position: absolute;
        right: -48px;
        top: 58px;
        color: rgba(255,255,255,.016);
        font-size: clamp(100px, 17vw, 260px);
        font-weight: 950;
        line-height: .78;
        letter-spacing: -.075em;
        pointer-events: none;
        user-select: none;
    }

    .favorites-shell {
        position: relative;
        z-index: 2;
        width: min(100% - 48px, 1240px);
        margin-inline: auto;
    }

    .favorites-header {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: end;
        gap: 36px;
        margin-bottom: 40px;
    }

    .favorites-kicker {
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

    .favorites-kicker::before {
        content: "";
        width: 30px;
        height: 1px;
        background: #d7a45f;
    }

    .favorites-title {
        margin: 0;
        color: #fff;
        font-size: clamp(48px, 6vw, 82px);
        line-height: .94;
        letter-spacing: -.065em;
        font-weight: 950;
    }

    .favorites-title span {
        color: #efc985;
    }

    .favorites-copy {
        max-width: 680px;
        margin: 20px 0 0;
        color: #858b92;
        font-size: 14px;
        line-height: 1.85;
    }

    .favorites-counter {
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

    .favorites-counter small {
        display: block;
        margin-bottom: 5px;
        color: #8c9299;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .favorites-counter strong {
        color: #fff;
        font-size: 26px;
        line-height: 1;
    }

    .favorites-toolbar {
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

    .favorites-toolbar span {
        color: #777d84;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .favorites-toolbar a {
        color: #e7bd7b;
        font-size: 10px;
        font-weight: 900;
        text-decoration: none;
    }

    .favorites-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px;
    }

    .favorite-card {
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

    .favorite-card:hover {
        transform: translateY(-7px);
        border-color: rgba(215,164,95,.24);
        box-shadow: 0 30px 80px rgba(0,0,0,.34);
    }

    .favorite-media {
        position: relative;
        aspect-ratio: 16 / 9.5;
        overflow: hidden;
        background: #0b0d10;
    }

    .favorite-media::after {
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

    .favorite-media a {
        display: block;
        width: 100%;
        height: 100%;
    }

    .favorite-media img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
        transform: scale(1.01);
        transition: transform .6s cubic-bezier(.2,.7,.2,1);
    }

    .favorite-card:hover .favorite-media img {
        transform: scale(1.065);
    }

    .favorite-year,
    .favorite-brand {
        position: absolute;
        top: 18px;
        z-index: 3;
    }

    .favorite-year {
        left: 18px;
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

    .favorite-brand {
        right: 18px;
        color: rgba(255,255,255,.88);
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .18em;
        text-transform: uppercase;
        text-shadow: 0 2px 14px rgba(0,0,0,.6);
    }

    .favorite-body {
        padding: 24px;
    }

    .favorite-meta {
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

    .favorite-meta i {
        width: 3px;
        height: 3px;
        border-radius: 50%;
        background: #5e6268;
    }

    .favorite-name {
        margin: 0;
        color: #fff;
        font-size: clamp(25px, 2.4vw, 32px);
        line-height: 1.05;
        letter-spacing: -.045em;
    }

    .favorite-summary {
        min-height: 50px;
        margin: 12px 0 0;
        color: #7f858d;
        font-size: 13px;
        line-height: 1.8;
    }

    .favorite-divider {
        height: 1px;
        margin: 22px 0;
        background:
            linear-gradient(
                90deg,
                rgba(255,255,255,.08),
                rgba(255,255,255,.035),
                transparent
            );
    }

    .favorite-bottom {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 18px;
    }

    .favorite-price small {
        display: block;
        margin-bottom: 2px;
        color: #666c73;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .favorite-price strong {
        display: block;
        color: #fff;
        font-size: clamp(23px, 2.2vw, 29px);
        line-height: 1.1;
        letter-spacing: -.035em;
    }

    .favorite-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .favorite-actions form {
        margin: 0;
    }

    .favorite-link,
    .favorite-cart,
    .favorite-remove,
    .favorites-empty-link {
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
            box-shadow .2s ease,
            color .2s ease;
    }

    .favorite-link {
        border: 1px solid rgba(215,164,95,.18);
        background: rgba(215,164,95,.075);
        color: #e8bc76;
    }

    .favorite-link:hover,
    .favorite-remove:hover {
        transform: translateY(-2px);
    }

    .favorite-cart {
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

    .favorite-cart:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 36px rgba(215,164,95,.23);
    }

    .favorite-remove {
        width: 43px;
        padding: 0;
        border: 1px solid rgba(239,143,143,.18);
        background: rgba(239,143,143,.055);
        color: #efaaaa;
        font-size: 16px;
    }

    .favorite-remove:hover {
        border-color: rgba(239,143,143,.32);
        background: rgba(239,143,143,.10);
    }

    .favorites-empty {
        padding: 72px 28px;
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

    .favorites-empty-mark {
        width: 70px;
        height: 70px;
        margin: 0 auto 22px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(215,164,95,.22);
        border-radius: 50%;
        background: rgba(215,164,95,.07);
        color: #efc985;
        font-size: 28px;
    }

    .favorites-empty h2 {
        margin: 0;
        color: #fff;
        font-size: 30px;
        letter-spacing: -.04em;
    }

    .favorites-empty p {
        max-width: 560px;
        margin: 12px auto 24px;
        color: #7f858c;
        font-size: 13px;
        line-height: 1.8;
    }

    .favorites-empty-link {
        border: 0;
        background:
            linear-gradient(
                135deg,
                #f1cc8b,
                #cc9550
            );
        color: #15110c;
        box-shadow: 0 12px 28px rgba(215,164,95,.15);
    }

    .favorites-empty-link:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 36px rgba(215,164,95,.22);
    }

    .favorites-assurance {
        margin-top: 34px;
        padding: 20px 22px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 20px;
        background: rgba(255,255,255,.02);
    }

    .favorites-assurance div {
        padding: 10px 14px;
        border-right: 1px solid rgba(255,255,255,.06);
    }

    .favorites-assurance div:last-child {
        border-right: 0;
    }

    .favorites-assurance small {
        display: block;
        margin-bottom: 4px;
        color: #a47741;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .15em;
        text-transform: uppercase;
    }

    .favorites-assurance strong {
        color: #d8d8d5;
        font-size: 12px;
        font-weight: 750;
    }

    @media (max-width: 980px) {
        .favorites-header {
            grid-template-columns: 1fr;
        }

        .favorites-counter {
            width: fit-content;
        }

        .favorites-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 700px) {
        .favorites-page {
            padding: 64px 0 80px;
        }

        .favorites-shell {
            width: min(100% - 32px, 1240px);
        }

        .favorites-title {
            font-size: clamp(46px, 14vw, 64px);
        }

        .favorite-body {
            padding: 20px;
        }

        .favorite-bottom {
            align-items: stretch;
            flex-direction: column;
        }

        .favorite-actions {
            justify-content: stretch;
        }

        .favorite-link,
        .favorite-cart {
            flex: 1;
        }

        .favorite-actions form:not(.favorite-remove-form) {
            flex: 1;
        }

        .favorite-actions form:not(.favorite-remove-form) .favorite-cart {
            width: 100%;
        }

        .favorites-assurance {
            grid-template-columns: 1fr;
        }

        .favorites-assurance div {
            border-right: 0;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }

        .favorites-assurance div:last-child {
            border-bottom: 0;
        }
    }
</style>
@endpush

@section('content')

<section class="favorites-page">
    <div class="favorites-shell">

        <header class="favorites-header">
            <div>
                <span class="favorites-kicker">
                    Mashal Member Collection
                </span>

                <h1 class="favorites-title">
                    Mijn
                    <span>favorieten.</span>
                </h1>

                <p class="favorites-copy">
                    Bewaar modellen die je interesseren en vergelijk je selectie
                    op je eigen tempo. Vanuit hier kun je direct het model openen,
                    aan je winkelwagen toevoegen of uit je favorieten verwijderen.
                </p>
            </div>

            <div class="favorites-counter">
                <small>
                    Opgeslagen
                </small>

                <strong>
                    {{ $favoriteCount }}
                    {{ $favoriteCount === 1 ? 'model' : 'modellen' }}
                </strong>
            </div>
        </header>

        <div class="favorites-toolbar">
            <span>
                Persoonlijk opgeslagen voor {{ auth()->user()->name }}
            </span>

            <a href="{{ route('catalog') }}">
                + Meer modellen bekijken
            </a>
        </div>

        @if (empty($cars))
            <div class="favorites-empty">
                <div class="favorites-empty-mark">
                    ♡
                </div>

                <h2>
                    Nog geen favorieten
                </h2>

                <p>
                    Je hebt nog geen auto opgeslagen.
                    Open de Mashal-collectie en klik op het hartje
                    bij een model dat je wilt bewaren.
                </p>

                <a
                    class="favorites-empty-link"
                    href="{{ route('catalog') }}"
                >
                    Ontdek de collectie
                    <span aria-hidden="true">→</span>
                </a>
            </div>
        @else
            <div class="favorites-grid">
                @foreach ($cars as $car)
                    <article class="favorite-card">

                        <div class="favorite-media">
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

                            <span class="favorite-year">
                                {{ $car['year'] }}
                            </span>

                            <span class="favorite-brand">
                                {{ $car['brand'] }}
                            </span>
                        </div>

                        <div class="favorite-body">

                            <div class="favorite-meta">
                                <span>{{ $car['type'] }}</span>
                                <i></i>
                                <span>{{ $car['fuel'] }}</span>
                            </div>

                            <h2 class="favorite-name">
                                {{ $car['brand'] }}
                                {{ $car['model'] }}
                            </h2>

                            <p class="favorite-summary">
                                {{ $car['summary'] }}
                            </p>

                            <div class="favorite-divider"></div>

                            <div class="favorite-bottom">

                                <div class="favorite-price">
                                    <small>
                                        Vanaf
                                    </small>

                                    <strong>
                                        €{{ number_format($car['price'], 0, ',', '.') }}
                                    </strong>
                                </div>

                                <div class="favorite-actions">

                                    <a
                                        class="favorite-link"
                                        href="{{ route('car', ['id' => $car['id']]) }}"
                                    >
                                        Bekijk model
                                        <span aria-hidden="true">→</span>
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('cart.add', ['id' => $car['id']]) }}"
                                    >
                                        @csrf

                                        <button
                                            class="favorite-cart"
                                            type="submit"
                                        >
                                            Toevoegen
                                        </button>
                                    </form>

                                    <form
                                        class="favorite-remove-form"
                                        method="POST"
                                        action="{{ route('favorites.destroy', ['id' => $car['id']]) }}"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            class="favorite-remove"
                                            type="submit"
                                            aria-label="Verwijder {{ $car['brand'] }} {{ $car['model'] }} uit favorieten"
                                            title="Verwijder uit favorieten"
                                        >
                                            ♥
                                        </button>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        <div class="favorites-assurance">
            <div>
                <small>
                    Persoonlijk
                </small>

                <strong>
                    Favorieten zijn gekoppeld aan jouw account
                </strong>
            </div>

            <div>
                <small>
                    Bewaard
                </small>

                <strong>
                    Je selectie blijft beschikbaar na uitloggen
                </strong>
            </div>

            <div>
                <small>
                    Flexibel
                </small>

                <strong>
                    Verwijder of voeg een model op ieder moment toe
                </strong>
            </div>
        </div>

    </div>
</section>

@endsection
