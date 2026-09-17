@extends('layouts.site-layout')

@section('title', 'Mashal | ' . $car['brand'] . ' ' . $car['model'])

@push('styles')
<style>
    .vehicle-page {
        position: relative;
        overflow: hidden;
        padding: 72px 0 110px;
        background:
            radial-gradient(
                circle at 14% 6%,
                rgba(215, 164, 95, .075),
                transparent 24rem
            ),
            radial-gradient(
                circle at 88% 18%,
                rgba(255, 255, 255, .025),
                transparent 28rem
            ),
            #08090b;
    }

    .vehicle-page::before {
        content: "{{ strtoupper($car['brand']) }}";
        position: absolute;
        right: -20px;
        top: 22px;
        color: rgba(255,255,255,.015);
        font-size: clamp(120px, 18vw, 290px);
        font-weight: 950;
        line-height: .8;
        letter-spacing: -.07em;
        pointer-events: none;
        user-select: none;
    }

    .vehicle-shell {
        position: relative;
        z-index: 2;
        width: min(100% - 48px, 1240px);
        margin-inline: auto;
    }

    /* ========================================================= */
    /* BREADCRUMB                                                 */
    /* ========================================================= */

    .vehicle-breadcrumb {
        display: flex;
        align-items: center;
        gap: 9px;
        flex-wrap: wrap;
        margin-bottom: 24px;
        color: #6f757c;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .vehicle-breadcrumb a {
        color: #9a9fa5;
        text-decoration: none;
        transition: color .2s ease;
    }

    .vehicle-breadcrumb a:hover {
        color: #e5b86f;
    }

    .breadcrumb-separator {
        color: #44484d;
    }

    /* ========================================================= */
    /* HERO / DETAIL                                              */
    /* ========================================================= */

    .vehicle-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(360px, .8fr);
        gap: 24px;
        align-items: stretch;
    }

    .vehicle-visual,
    .vehicle-info-card {
        border: 1px solid rgba(255,255,255,.085);
        border-radius: 30px;
        background:
            linear-gradient(
                145deg,
                rgba(255,255,255,.048),
                rgba(255,255,255,.016)
            );
        box-shadow: 0 24px 70px rgba(0,0,0,.24);
    }

    .vehicle-visual {
        position: relative;
        min-height: 610px;
        overflow: hidden;
        isolation: isolate;
    }

    .vehicle-visual img {
        width: 100%;
        height: 100%;
        min-height: 610px;
        object-fit: cover;
        transition: transform .75s cubic-bezier(.2,.7,.2,1);
    }

    .vehicle-visual:hover img {
        transform: scale(1.045);
    }

    .vehicle-visual::after {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        background:
            linear-gradient(
                180deg,
                rgba(0,0,0,.02) 0%,
                rgba(0,0,0,.08) 58%,
                rgba(6,7,9,.75) 100%
            );
    }

    .vehicle-visual-top {
        position: absolute;
        left: 20px;
        right: 20px;
        top: 20px;
        z-index: 3;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
    }

    .visual-badge {
        padding: 8px 12px;
        border: 1px solid rgba(255,255,255,.16);
        border-radius: 999px;
        background: rgba(6,7,9,.62);
        backdrop-filter: blur(14px);
        color: #efc985;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .visual-id {
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(6,7,9,.45);
        color: rgba(255,255,255,.72);
        font-size: 9px;
        font-weight: 850;
        letter-spacing: .12em;
        text-transform: uppercase;
        backdrop-filter: blur(12px);
    }

    .vehicle-visual-caption {
        position: absolute;
        left: 28px;
        right: 28px;
        bottom: 26px;
        z-index: 3;
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 22px;
    }

    .visual-caption-copy small {
        display: block;
        margin-bottom: 7px;
        color: #d7a45f;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .visual-caption-copy strong {
        display: block;
        color: #ffffff;
        font-size: clamp(24px, 3vw, 38px);
        line-height: 1.05;
        letter-spacing: -.045em;
    }

    .visual-caption-meta {
        color: rgba(255,255,255,.72);
        font-size: 11px;
        font-weight: 700;
        text-align: right;
    }

    /* ========================================================= */
    /* INFO CARD                                                  */
    /* ========================================================= */

    .vehicle-info-card {
        position: relative;
        padding: 32px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        overflow: hidden;
    }

    .vehicle-info-card::before {
        content: "";
        position: absolute;
        right: -80px;
        top: -90px;
        width: 240px;
        height: 240px;
        border-radius: 50%;
        background: rgba(215,164,95,.07);
        filter: blur(2px);
        pointer-events: none;
    }

    .vehicle-kicker {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
        color: #d9aa68;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .vehicle-kicker::before {
        content: "";
        width: 28px;
        height: 1px;
        background: #d7a45f;
    }

    .vehicle-title {
        margin: 0;
        color: #ffffff;
        font-size: clamp(38px, 4.5vw, 62px);
        line-height: .96;
        letter-spacing: -.06em;
        font-weight: 950;
    }

    .vehicle-title span {
        display: block;
        color: #efc985;
    }

    .vehicle-summary {
        margin: 20px 0 0;
        color: #898f96;
        font-size: 14px;
        line-height: 1.85;
    }

    .vehicle-spec-list {
        margin-top: 28px;
        border-top: 1px solid rgba(255,255,255,.07);
    }

    .vehicle-spec-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 15px 0;
        border-bottom: 1px solid rgba(255,255,255,.07);
    }

    .vehicle-spec-row span {
        color: #696f76;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .10em;
        text-transform: uppercase;
    }

    .vehicle-spec-row strong {
        color: #e7e5df;
        font-size: 13px;
        text-align: right;
    }

    /* ========================================================= */
    /* PRICE                                                      */
    /* ========================================================= */

    .vehicle-price-box {
        margin-top: 28px;
        padding: 22px;
        border: 1px solid rgba(215,164,95,.17);
        border-radius: 20px;
        background:
            linear-gradient(
                145deg,
                rgba(215,164,95,.085),
                rgba(215,164,95,.025)
            );
    }

    .vehicle-price-box small {
        display: block;
        margin-bottom: 4px;
        color: #a37743;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .vehicle-price-box strong {
        display: block;
        color: #ffffff;
        font-size: 36px;
        line-height: 1;
        letter-spacing: -.045em;
    }

    .vehicle-price-note {
        margin-top: 8px;
        color: #747a81;
        font-size: 10px;
        line-height: 1.5;
    }

    /* ========================================================= */
    /* ACTIONS                                                    */
    /* ========================================================= */

    .vehicle-actions {
        margin-top: 22px;
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .vehicle-actions form {
        margin: 0;
    }

    .vehicle-buy-btn,
    .vehicle-back-btn {
        width: 100%;
        min-height: 54px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .03em;
        cursor: pointer;
        text-decoration: none;
        transition:
            transform .2s ease,
            box-shadow .2s ease,
            border-color .2s ease,
            background .2s ease;
    }

    .vehicle-buy-btn {
        border: 0;
        background:
            linear-gradient(
                135deg,
                #f1cc8b,
                #cc9550
            );
        color: #15100b;
        box-shadow: 0 16px 40px rgba(215,164,95,.20);
    }

    .vehicle-buy-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 24px 54px rgba(215,164,95,.30);
    }

    .vehicle-back-btn {
        border: 1px solid rgba(255,255,255,.11);
        background: rgba(255,255,255,.035);
        color: #d9d7d2;
    }

    .vehicle-back-btn:hover {
        transform: translateY(-2px);
        border-color: rgba(215,164,95,.28);
        background: rgba(215,164,95,.06);
    }

    /* ========================================================= */
    /* FEATURE BAR                                                */
    /* ========================================================= */

    .vehicle-feature-bar {
        margin-top: 26px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 22px;
        background: rgba(255,255,255,.02);
        overflow: hidden;
    }

    .vehicle-feature {
        padding: 22px;
        border-right: 1px solid rgba(255,255,255,.06);
    }

    .vehicle-feature:last-child {
        border-right: 0;
    }

    .vehicle-feature-icon {
        width: 40px;
        height: 40px;
        margin-bottom: 13px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(215,164,95,.18);
        border-radius: 12px;
        background: rgba(215,164,95,.06);
        color: #e2b56d;
        font-size: 16px;
        font-weight: 900;
    }

    .vehicle-feature h3 {
        margin: 0 0 6px;
        color: #e7e5df;
        font-size: 15px;
    }

    .vehicle-feature p {
        margin: 0;
        color: #737980;
        font-size: 11px;
        line-height: 1.7;
    }

    /* ========================================================= */
    /* EXPERIENCE SECTION                                        */
    /* ========================================================= */

    .vehicle-experience {
        margin-top: 90px;
    }

    .experience-head {
        display: grid;
        grid-template-columns: .8fr 1.2fr;
        gap: 56px;
        align-items: start;
        margin-bottom: 34px;
    }

    .experience-label {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        color: #d7a45f;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .20em;
        text-transform: uppercase;
    }

    .experience-label::before {
        content: "";
        width: 28px;
        height: 1px;
        background: #d7a45f;
    }

    .experience-head h2 {
        margin: 14px 0 0;
        color: #ffffff;
        font-size: clamp(38px, 4.8vw, 62px);
        line-height: 1;
        letter-spacing: -.055em;
    }

    .experience-head p {
        margin: 0;
        color: #80868d;
        font-size: 14px;
        line-height: 1.9;
    }

    .experience-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
    }

    .experience-card {
        position: relative;
        min-height: 250px;
        padding: 26px;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 24px;
        background:
            linear-gradient(
                145deg,
                rgba(255,255,255,.042),
                rgba(255,255,255,.014)
            );
        transition:
            transform .28s ease,
            border-color .28s ease;
    }

    .experience-card:hover {
        transform: translateY(-6px);
        border-color: rgba(215,164,95,.23);
    }

    .experience-card::before {
        content: "";
        position: absolute;
        right: -36px;
        top: -36px;
        width: 130px;
        height: 130px;
        border-radius: 50%;
        background: rgba(215,164,95,.05);
    }

    .experience-index {
        margin-bottom: auto;
        color: #7f603a;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .16em;
    }

    .experience-card h3 {
        margin: 0 0 8px;
        color: #eceae4;
        font-size: 20px;
        letter-spacing: -.03em;
    }

    .experience-card p {
        margin: 0;
        color: #737980;
        font-size: 12px;
        line-height: 1.75;
    }

    /* ========================================================= */
    /* TRUST / CTA                                                */
    /* ========================================================= */

    .vehicle-cta {
        margin-top: 90px;
        padding: 44px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 28px;
        border: 1px solid rgba(215,164,95,.14);
        border-radius: 28px;
        background:
            radial-gradient(
                circle at 80% 20%,
                rgba(215,164,95,.09),
                transparent 18rem
            ),
            linear-gradient(
                145deg,
                rgba(255,255,255,.04),
                rgba(255,255,255,.012)
            );
    }

    .vehicle-cta h2 {
        margin: 0 0 8px;
        color: #ffffff;
        font-size: clamp(28px, 3.4vw, 42px);
        line-height: 1.05;
        letter-spacing: -.045em;
    }

    .vehicle-cta p {
        max-width: 650px;
        margin: 0;
        color: #7d838a;
        font-size: 13px;
        line-height: 1.8;
    }

    .vehicle-cta-actions {
        display: flex;
        gap: 10px;
        flex-shrink: 0;
    }

    /* ========================================================= */
    /* RESPONSIVE                                                 */
    /* ========================================================= */

    @media (max-width: 1040px) {
        .vehicle-hero,
        .experience-head {
            grid-template-columns: 1fr;
        }

        .vehicle-visual {
            min-height: 520px;
        }

        .vehicle-visual img {
            min-height: 520px;
        }

        .vehicle-info-card {
            padding: 28px;
        }

        .vehicle-cta {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    @media (max-width: 760px) {
        .vehicle-page {
            padding: 52px 0 80px;
        }

        .vehicle-shell {
            width: min(100% - 32px, 1240px);
        }

        .vehicle-visual {
            min-height: 390px;
            border-radius: 22px;
        }

        .vehicle-visual img {
            min-height: 390px;
        }

        .vehicle-info-card {
            border-radius: 22px;
            padding: 22px;
        }

        .vehicle-feature-bar,
        .experience-grid {
            grid-template-columns: 1fr;
        }

        .vehicle-feature {
            border-right: 0;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }

        .vehicle-feature:last-child {
            border-bottom: 0;
        }

        .vehicle-visual-caption {
            align-items: flex-start;
            flex-direction: column;
        }

        .visual-caption-meta {
            text-align: left;
        }

        .vehicle-cta {
            padding: 28px 22px;
        }

        .vehicle-cta-actions {
            width: 100%;
            flex-direction: column;
        }

        .vehicle-cta-actions .primary-btn,
        .vehicle-cta-actions .secondary-btn {
            width: 100%;
        }
    }
</style>
@endpush


@section('content')

<section class="vehicle-page">

    <div class="vehicle-shell">

        {{-- ========================================================= --}}
        {{-- BREADCRUMB                                                 --}}
        {{-- ========================================================= --}}

        <nav
            class="vehicle-breadcrumb"
            aria-label="Breadcrumb"
        >
            <a href="{{ route('home') }}">
                Mashal
            </a>

            <span class="breadcrumb-separator">
                /
            </span>

            <a href="{{ route('catalog') }}">
                Collectie
            </a>

            <span class="breadcrumb-separator">
                /
            </span>

            <span>
                {{ $car['brand'] }}
                {{ $car['model'] }}
            </span>
        </nav>


        {{-- ========================================================= --}}
        {{-- SUCCESS / ERROR                                            --}}
        {{-- ========================================================= --}}

        @if (session('success'))

            <div
                class="success"
                style="margin-bottom: 24px;"
            >
                <strong>
                    Gelukt.
                </strong>

                {{ session('success') }}
            </div>

        @endif


        @if ($errors->any())

            <div
                class="error"
                style="margin-bottom: 24px;"
            >
                <strong>
                    Er ging iets mis.
                </strong>

                <ul
                    style="
                        margin: 8px 0 0 18px;
                        padding: 0;
                    "
                >
                    @foreach ($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach
                </ul>
            </div>

        @endif


        {{-- ========================================================= --}}
        {{-- MAIN VEHICLE HERO                                          --}}
        {{-- ========================================================= --}}

        <div class="vehicle-hero">

            {{-- LEFT / IMAGE --}}

            <div class="vehicle-visual">

                <img
                    src="{{ $car['image'] }}"
                    alt="{{ $car['brand'] }} {{ $car['model'] }}"
                >


                <div class="vehicle-visual-top">

                    <span class="visual-badge">
                        Mashal Selection
                    </span>

                    <span class="visual-id">
                        Model #{{ str_pad((string) $car['id'], 2, '0', STR_PAD_LEFT) }}
                    </span>

                </div>


                <div class="vehicle-visual-caption">

                    <div class="visual-caption-copy">

                        <small>
                            {{ $car['brand'] }}
                        </small>

                        <strong>
                            {{ $car['model'] }}
                        </strong>

                    </div>


                    <div class="visual-caption-meta">
                        {{ $car['year'] }}
                        ·
                        {{ $car['type'] }}
                        ·
                        {{ $car['fuel'] }}
                    </div>

                </div>

            </div>


            {{-- RIGHT / INFO --}}

            <aside class="vehicle-info-card">

                <div>

                    <span class="vehicle-kicker">
                        Premium automotive
                    </span>

                    <h1 class="vehicle-title">
                        {{ $car['brand'] }}
                        <span>
                            {{ $car['model'] }}
                        </span>
                    </h1>

                    <p class="vehicle-summary">
                        {{ $car['summary'] }}
                    </p>


                    <div class="vehicle-spec-list">

                        <div class="vehicle-spec-row">

                            <span>
                                Merk
                            </span>

                            <strong>
                                {{ $car['brand'] }}
                            </strong>

                        </div>


                        <div class="vehicle-spec-row">

                            <span>
                                Model
                            </span>

                            <strong>
                                {{ $car['model'] }}
                            </strong>

                        </div>


                        <div class="vehicle-spec-row">

                            <span>
                                Bouwjaar
                            </span>

                            <strong>
                                {{ $car['year'] }}
                            </strong>

                        </div>


                        <div class="vehicle-spec-row">

                            <span>
                                Carrosserie
                            </span>

                            <strong>
                                {{ $car['type'] }}
                            </strong>

                        </div>


                        <div class="vehicle-spec-row">

                            <span>
                                Aandrijving
                            </span>

                            <strong>
                                {{ $car['fuel'] }}
                            </strong>

                        </div>

                    </div>


                    <div class="vehicle-price-box">

                        <small>
                            Mashal prijs
                        </small>

                        <strong>
                            €{{ number_format($car['price'], 0, ',', '.') }}
                        </strong>

                        <div class="vehicle-price-note">
                            Prijs zoals weergegeven in de huidige Mashal-collectie.
                        </div>

                    </div>

                </div>


                <div class="vehicle-actions">

                    <form
                        method="POST"
                        action="{{ route('cart.add', ['id' => $car['id']]) }}"
                    >
                        @csrf

                        <button
                            class="vehicle-buy-btn"
                            type="submit"
                        >
                            Toevoegen aan winkelwagen
                            <span aria-hidden="true">→</span>
                        </button>
                    </form>


                    <a
                        class="vehicle-back-btn"
                        href="{{ route('catalog') }}"
                    >
                        ← Terug naar collectie
                    </a>

                </div>

            </aside>

        </div>


        {{-- ========================================================= --}}
        {{-- FEATURE BAR                                                --}}
        {{-- ========================================================= --}}

        <div class="vehicle-feature-bar">

            <div class="vehicle-feature">

                <div class="vehicle-feature-icon">
                    ✓
                </div>

                <h3>
                    Duidelijke selectie
                </h3>

                <p>
                    Bekijk alle voertuiggegevens en de actuele prijs
                    voordat je een keuze maakt.
                </p>

            </div>


            <div class="vehicle-feature">

                <div class="vehicle-feature-icon">
                    ◇
                </div>

                <h3>
                    Veilig account
                </h3>

                <p>
                    Bestellen verloopt via je persoonlijke
                    en geverifieerde Mashal-account.
                </p>

            </div>


            <div class="vehicle-feature">

                <div class="vehicle-feature-icon">
                    ↗
                </div>

                <h3>
                    Direct bevestigd
                </h3>

                <p>
                    Na een voltooide bestelling ontvang je automatisch
                    een bevestiging per e-mail.
                </p>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- EXPERIENCE                                                 --}}
        {{-- ========================================================= --}}

        <section class="vehicle-experience">

            <div class="experience-head">

                <div>

                    <span class="experience-label">
                        The Mashal experience
                    </span>

                    <h2>
                        Rustig kiezen.
                        Zeker bestellen.
                    </h2>

                </div>


                <p>
                    Deze pagina is ontworpen om één ding goed te doen:
                    je precies de informatie geven die je nodig hebt
                    zonder onnodige afleiding. Van voertuiggegevens
                    tot prijs en bestelling blijft iedere stap overzichtelijk.
                </p>

            </div>


            <div class="experience-grid">

                <article class="experience-card">

                    <div class="experience-index">
                        01 / VEHICLE
                    </div>

                    <h3>
                        Alles op één plek
                    </h3>

                    <p>
                        Merk, model, bouwjaar, carrosserie,
                        brandstof en prijs zijn direct zichtbaar.
                    </p>

                </article>


                <article class="experience-card">

                    <div class="experience-index">
                        02 / ACCOUNT
                    </div>

                    <h3>
                        Persoonlijk & beveiligd
                    </h3>

                    <p>
                        Je bestelling wordt gekoppeld aan jouw
                        persoonlijke Mashal-account.
                    </p>

                </article>


                <article class="experience-card">

                    <div class="experience-index">
                        03 / ORDER
                    </div>

                    <h3>
                        Van keuze naar checkout
                    </h3>

                    <p>
                        Voeg het voertuig toe aan je winkelwagen
                        en rond je selectie daarna veilig af.
                    </p>

                </article>

            </div>

        </section>


        {{-- ========================================================= --}}
        {{-- FINAL CTA                                                  --}}
        {{-- ========================================================= --}}

        <section class="vehicle-cta">

            <div>

                <h2>
                    Past deze {{ $car['brand'] }} bij jouw volgende rit?
                </h2>

                <p>
                    Voeg het model toe aan je winkelwagen
                    of bekijk eerst de rest van de Mashal-collectie.
                    Je kunt je selectie altijd controleren
                    voordat je de bestelling afrondt.
                </p>

            </div>


            <div class="vehicle-cta-actions">

                <form
                    method="POST"
                    action="{{ route('cart.add', ['id' => $car['id']]) }}"
                    style="margin: 0;"
                >
                    @csrf

                    <button
                        class="primary-btn"
                        type="submit"
                    >
                        Toevoegen
                    </button>
                </form>


                <a
                    class="secondary-btn"
                    href="{{ route('catalog') }}"
                >
                    Meer modellen
                </a>

            </div>

        </section>

    </div>

</section>

@endsection
