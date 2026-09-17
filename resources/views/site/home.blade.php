@extends('layouts.site-layout')

@section('title', 'Mashal | Premium Automotive Experience')

@push('styles')
<style>
    .mashal-home {
        overflow: hidden;
    }

    /* ========================================================= */
    /* CINEMATIC HERO                                             */
    /* ========================================================= */

    .mashal-hero {
        position: relative;
        min-height: calc(100vh - 78px);
        display: flex;
        align-items: center;
        overflow: hidden;
        isolation: isolate;
        background: #08090b;
    }

    .mashal-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -3;
        background:
            linear-gradient(
                90deg,
                rgba(4, 5, 7, .98) 0%,
                rgba(4, 5, 7, .92) 35%,
                rgba(4, 5, 7, .55) 68%,
                rgba(4, 5, 7, .70) 100%
            ),
            linear-gradient(
                180deg,
                rgba(0,0,0,.05),
                rgba(0,0,0,.65)
            ),
            url('https://images.unsplash.com/photo-1503736334956-4c8f8e92946d?auto=format&fit=crop&w=2200&q=90')
            center 54% / cover no-repeat;
        transform: scale(1.03);
        animation: heroZoom 16s ease-in-out infinite alternate;
    }

    .mashal-hero::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -2;
        background:
            radial-gradient(
                circle at 80% 38%,
                rgba(215, 164, 95, .14),
                transparent 24rem
            ),
            linear-gradient(
                180deg,
                transparent 72%,
                #08090b 100%
            );
        pointer-events: none;
    }

    @keyframes heroZoom {
        from { transform: scale(1.03); }
        to { transform: scale(1.09); }
    }

    .hero-shell {
        width: min(100% - 48px, 1240px);
        margin-inline: auto;
        padding: 90px 0 80px;
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(300px, .65fr);
        align-items: end;
        gap: 70px;
    }

    .hero-content {
        max-width: 820px;
        animation: heroIn .85s cubic-bezier(.2,.7,.2,1) both;
    }

    @keyframes heroIn {
        from {
            opacity: 0;
            transform: translateY(34px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .hero-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 22px;
        color: #efc985;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .26em;
        text-transform: uppercase;
    }

    .hero-eyebrow::before {
        content: "";
        width: 42px;
        height: 1px;
        background: linear-gradient(90deg, #d7a45f, transparent);
    }

    .hero-title {
        max-width: 900px;
        margin: 0;
        font-size: clamp(58px, 7vw, 108px);
        line-height: .90;
        letter-spacing: -.075em;
        font-weight: 950;
        text-wrap: balance;
    }

    .hero-title .line-soft {
        color: rgba(255,255,255,.76);
    }

    .hero-title .line-gold {
        position: relative;
        color: #f0c781;
        text-shadow: 0 8px 40px rgba(215,164,95,.12);
    }

    .hero-description {
        max-width: 660px;
        margin: 30px 0 0;
        color: #b8babd;
        font-size: clamp(15px, 1.4vw, 18px);
        line-height: 1.9;
    }

    .hero-buttons {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 34px;
    }

    .hero-primary,
    .hero-secondary {
        min-height: 54px;
        padding: 0 26px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        border-radius: 999px;
        text-decoration: none;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .03em;
        transition:
            transform .22s ease,
            box-shadow .22s ease,
            background .22s ease,
            border-color .22s ease;
    }

    .hero-primary {
        background: linear-gradient(135deg, #f1cc8b, #cc9550);
        color: #120f0b;
        box-shadow: 0 18px 50px rgba(215,164,95,.24);
    }

    .hero-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 26px 70px rgba(215,164,95,.34);
    }

    .hero-secondary {
        border: 1px solid rgba(255,255,255,.20);
        background: rgba(255,255,255,.05);
        color: #fff;
        backdrop-filter: blur(12px);
    }

    .hero-secondary:hover {
        transform: translateY(-3px);
        border-color: rgba(215,164,95,.42);
        background: rgba(215,164,95,.08);
    }

    .hero-side {
        align-self: end;
        display: grid;
        gap: 12px;
        animation: heroIn .85s .15s cubic-bezier(.2,.7,.2,1) both;
    }

    .hero-stat {
        position: relative;
        padding: 22px 22px 21px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.13);
        border-radius: 18px;
        background: linear-gradient(
            135deg,
            rgba(255,255,255,.10),
            rgba(255,255,255,.03)
        );
        backdrop-filter: blur(18px);
        box-shadow: 0 16px 45px rgba(0,0,0,.22);
    }

    .hero-stat::before {
        content: "";
        position: absolute;
        width: 90px;
        height: 90px;
        right: -28px;
        top: -34px;
        border-radius: 50%;
        background: rgba(215,164,95,.09);
        filter: blur(2px);
    }

    .hero-stat small {
        display: block;
        margin-bottom: 6px;
        color: #c49a62;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .20em;
        text-transform: uppercase;
    }

    .hero-stat strong {
        display: block;
        color: #fff;
        font-size: 24px;
        line-height: 1.1;
        letter-spacing: -.03em;
    }

    .hero-stat span {
        display: block;
        margin-top: 6px;
        color: #8d9299;
        font-size: 11px;
        line-height: 1.5;
    }

    .hero-scroll {
        position: absolute;
        left: 50%;
        bottom: 28px;
        transform: translateX(-50%);
        display: flex;
        align-items: center;
        gap: 10px;
        color: rgba(255,255,255,.42);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .hero-scroll::before {
        content: "";
        width: 36px;
        height: 1px;
        background: rgba(255,255,255,.25);
    }

    /* ========================================================= */
    /* BRAND STRIP                                                */
    /* ========================================================= */

    .brand-strip {
        position: relative;
        z-index: 3;
        border-top: 1px solid rgba(255,255,255,.05);
        border-bottom: 1px solid rgba(255,255,255,.07);
        background: #0a0c0f;
    }

    .brand-strip-inner {
        width: min(100% - 48px, 1240px);
        margin-inline: auto;
        min-height: 92px;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        align-items: center;
        gap: 20px;
    }

    .brand-name {
        text-align: center;
        color: #70757c;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .12em;
        text-transform: uppercase;
        transition: color .2s ease, transform .2s ease;
    }

    .brand-name:hover {
        color: #d6a560;
        transform: translateY(-2px);
    }

    /* ========================================================= */
    /* INTRO                                                      */
    /* ========================================================= */

    .intro-section {
        position: relative;
        padding: 110px 0;
        overflow: hidden;
    }

    .intro-section::before {
        content: "M";
        position: absolute;
        right: -50px;
        top: -110px;
        color: rgba(255,255,255,.015);
        font-size: 440px;
        font-weight: 950;
        line-height: 1;
        pointer-events: none;
    }

    .intro-grid {
        width: min(100% - 48px, 1240px);
        margin-inline: auto;
        display: grid;
        grid-template-columns: .78fr 1.22fr;
        gap: 80px;
        align-items: start;
    }

    .section-label {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: #d7a45f;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .22em;
        text-transform: uppercase;
    }

    .section-label::before {
        content: "";
        width: 28px;
        height: 1px;
        background: #d7a45f;
    }

    .intro-heading {
        margin: 16px 0 0;
        font-size: clamp(38px, 4.8vw, 64px);
        line-height: 1.02;
        letter-spacing: -.055em;
    }

    .intro-copy {
        max-width: 720px;
    }

    .intro-copy .lead {
        margin: 0;
        color: #d8d8d5;
        font-size: clamp(20px, 2.1vw, 29px);
        line-height: 1.5;
        letter-spacing: -.025em;
    }

    .intro-copy p:not(.lead) {
        max-width: 650px;
        margin: 24px 0 0;
        color: #858b92;
        font-size: 14px;
        line-height: 1.9;
    }

    /* ========================================================= */
    /* PREMIUM FEATURE CARDS                                      */
    /* ========================================================= */

    .experience-section {
        padding: 0 0 110px;
    }

    .experience-grid {
        width: min(100% - 48px, 1240px);
        margin-inline: auto;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .experience-card {
        position: relative;
        min-height: 370px;
        padding: 30px;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 26px;
        background: #101318;
        transition:
            transform .3s cubic-bezier(.2,.7,.2,1),
            border-color .3s ease,
            box-shadow .3s ease;
    }

    .experience-card:hover {
        transform: translateY(-8px);
        border-color: rgba(215,164,95,.25);
        box-shadow: 0 28px 70px rgba(0,0,0,.30);
    }

    .experience-card::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 0;
        background:
            linear-gradient(
                180deg,
                rgba(0,0,0,.02),
                rgba(0,0,0,.88) 88%
            );
    }

    .experience-card.one {
        background:
            url('https://images.unsplash.com/photo-1494905998402-395d579af36f?auto=format&fit=crop&w=1200&q=85')
            center / cover no-repeat;
    }

    .experience-card.two {
        background:
            url('https://images.unsplash.com/photo-1511919884226-fd3cad34687c?auto=format&fit=crop&w=1200&q=85')
            center / cover no-repeat;
    }

    .experience-card.three {
        background:
            url('https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&w=1200&q=85')
            center / cover no-repeat;
    }

    .experience-content {
        position: relative;
        z-index: 2;
    }

    .experience-number {
        margin-bottom: 20px;
        color: #d7a45f;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .20em;
    }

    .experience-card h3 {
        margin: 0 0 10px;
        font-size: 27px;
        line-height: 1.08;
        letter-spacing: -.04em;
    }

    .experience-card p {
        margin: 0;
        color: #a0a4a9;
        font-size: 13px;
        line-height: 1.75;
    }

    /* ========================================================= */
    /* FEATURED COLLECTION                                       */
    /* ========================================================= */

    .collection-section {
        padding: 110px 0;
        background:
            linear-gradient(
                180deg,
                rgba(255,255,255,.018),
                rgba(255,255,255,.006)
            );
        border-top: 1px solid rgba(255,255,255,.05);
        border-bottom: 1px solid rgba(255,255,255,.05);
    }

    .collection-head {
        width: min(100% - 48px, 1240px);
        margin-inline: auto;
        margin-bottom: 40px;
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 30px;
    }

    .collection-head h2 {
        max-width: 700px;
        margin: 12px 0 0;
        font-size: clamp(40px, 5vw, 68px);
        line-height: 1;
        letter-spacing: -.06em;
    }

    .collection-head p {
        max-width: 430px;
        margin: 0;
        color: #858b92;
        font-size: 13px;
        line-height: 1.8;
    }

    .car-grid {
        width: min(100% - 48px, 1240px);
        margin-inline: auto;
        display: grid;
        grid-template-columns: repeat(3, minmax(0,1fr));
        gap: 18px;
    }

    .car-card {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 24px;
        background: #0e1115;
        transition:
            transform .28s ease,
            border-color .28s ease,
            box-shadow .28s ease;
    }

    .car-card:hover {
        transform: translateY(-8px);
        border-color: rgba(215,164,95,.26);
        box-shadow: 0 28px 75px rgba(0,0,0,.34);
    }

    .car-image {
        position: relative;
        aspect-ratio: 16 / 10;
        overflow: hidden;
        background: #0b0d10;
    }

    .car-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .55s cubic-bezier(.2,.7,.2,1);
    }

    .car-card:hover .car-image img {
        transform: scale(1.06);
    }

    .car-image::after {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(
                180deg,
                transparent 45%,
                rgba(8,9,11,.42)
            );
    }

    .car-badge {
        position: absolute;
        left: 16px;
        top: 16px;
        z-index: 3;
        padding: 7px 10px;
        border: 1px solid rgba(255,255,255,.14);
        border-radius: 999px;
        background: rgba(8,9,11,.66);
        backdrop-filter: blur(10px);
        color: #f1c983;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .car-body {
        padding: 22px;
    }

    .car-body small {
        color: #a17643;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .17em;
        text-transform: uppercase;
    }

    .car-body h3 {
        margin: 7px 0 8px;
        font-size: 23px;
        letter-spacing: -.035em;
    }

    .car-body p {
        min-height: 46px;
        margin: 0;
        color: #7e848b;
        font-size: 12px;
        line-height: 1.7;
    }

    .car-footer {
        margin-top: 20px;
        padding-top: 17px;
        border-top: 1px solid rgba(255,255,255,.07);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
    }

    .car-price span {
        display: block;
        color: #62676e;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .12em;
    }

    .car-price strong {
        display: block;
        margin-top: 2px;
        color: #fff;
        font-size: 20px;
    }

    .car-link {
        width: 42px;
        height: 42px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(215,164,95,.22);
        border-radius: 50%;
        color: #edc47f;
        text-decoration: none;
        font-size: 17px;
        transition: background .2s ease, transform .2s ease;
    }

    .car-link:hover {
        transform: translateX(2px);
        background: rgba(215,164,95,.10);
    }

    .collection-action {
        width: min(100% - 48px, 1240px);
        margin: 34px auto 0;
        text-align: center;
    }

    /* ========================================================= */
    /* PROCESS                                                    */
    /* ========================================================= */

    .process-section {
        padding: 110px 0;
    }

    .process-shell {
        width: min(100% - 48px, 1240px);
        margin-inline: auto;
        display: grid;
        grid-template-columns: .75fr 1.25fr;
        gap: 70px;
        align-items: start;
    }

    .process-title {
        position: sticky;
        top: 120px;
    }

    .process-title h2 {
        margin: 14px 0 18px;
        font-size: clamp(40px, 5vw, 66px);
        line-height: 1;
        letter-spacing: -.055em;
    }

    .process-title p {
        max-width: 410px;
        margin: 0;
        color: #7e848b;
        font-size: 13px;
        line-height: 1.85;
    }

    .process-list {
        display: grid;
    }

    .process-item {
        position: relative;
        padding: 30px 0 30px 78px;
        border-top: 1px solid rgba(255,255,255,.08);
    }

    .process-item:last-child {
        border-bottom: 1px solid rgba(255,255,255,.08);
    }

    .process-step {
        position: absolute;
        left: 0;
        top: 31px;
        width: 50px;
        height: 50px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(215,164,95,.20);
        border-radius: 50%;
        background: rgba(215,164,95,.07);
        color: #e5b96e;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .08em;
    }

    .process-item h3 {
        margin: 0 0 8px;
        font-size: 23px;
        letter-spacing: -.035em;
    }

    .process-item p {
        max-width: 620px;
        margin: 0;
        color: #7f858c;
        font-size: 13px;
        line-height: 1.8;
    }

    /* ========================================================= */
    /* FINAL CTA                                                  */
    /* ========================================================= */

    .final-cta {
        position: relative;
        min-height: 560px;
        display: flex;
        align-items: center;
        overflow: hidden;
        isolation: isolate;
        border-top: 1px solid rgba(255,255,255,.06);
    }

    .final-cta::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -2;
        background:
            linear-gradient(
                90deg,
                rgba(5,6,8,.96),
                rgba(5,6,8,.72) 55%,
                rgba(5,6,8,.60)
            ),
            url('https://images.unsplash.com/photo-1517524008697-84bbe3c3fd98?auto=format&fit=crop&w=2200&q=88')
            center / cover no-repeat;
    }

    .final-cta::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -1;
        background:
            linear-gradient(
                180deg,
                #08090b 0%,
                transparent 20%,
                transparent 78%,
                #08090b 100%
            );
    }

    .final-inner {
        width: min(100% - 48px, 1240px);
        margin-inline: auto;
        padding: 90px 0;
    }

    .final-copy {
        max-width: 720px;
    }

    .final-copy h2 {
        margin: 14px 0 20px;
        font-size: clamp(48px, 6vw, 82px);
        line-height: .96;
        letter-spacing: -.065em;
    }

    .final-copy p {
        max-width: 570px;
        margin: 0;
        color: #a5a9ae;
        font-size: 15px;
        line-height: 1.85;
    }

    /* ========================================================= */
    /* RESPONSIVE                                                 */
    /* ========================================================= */

    @media (max-width: 980px) {
        .hero-shell,
        .intro-grid,
        .process-shell {
            grid-template-columns: 1fr;
        }

        .hero-shell {
            align-items: start;
        }

        .hero-side {
            max-width: 620px;
            grid-template-columns: repeat(3, 1fr);
        }

        .intro-grid {
            gap: 34px;
        }

        .experience-grid,
        .car-grid {
            grid-template-columns: 1fr 1fr;
        }

        .process-title {
            position: static;
        }

        .collection-head {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    @media (max-width: 700px) {
        .mashal-hero {
            min-height: 720px;
        }

        .hero-shell,
        .brand-strip-inner,
        .intro-grid,
        .experience-grid,
        .collection-head,
        .car-grid,
        .collection-action,
        .process-shell,
        .final-inner {
            width: min(100% - 32px, 1240px);
        }

        .hero-shell {
            padding: 70px 0 74px;
            gap: 42px;
        }

        .hero-title {
            font-size: clamp(52px, 15vw, 72px);
        }

        .hero-side {
            grid-template-columns: 1fr;
        }

        .hero-scroll {
            display: none;
        }

        .brand-strip-inner {
            grid-template-columns: 1fr 1fr;
            padding: 18px 0;
        }

        .intro-section,
        .collection-section,
        .process-section {
            padding: 80px 0;
        }

        .experience-section {
            padding-bottom: 80px;
        }

        .experience-grid,
        .car-grid {
            grid-template-columns: 1fr;
        }

        .experience-card {
            min-height: 330px;
        }

        .process-item {
            padding-left: 66px;
        }

        .hero-buttons {
            align-items: stretch;
            flex-direction: column;
        }

        .hero-primary,
        .hero-secondary {
            width: 100%;
        }
    }
</style>
@endpush


@section('content')

<div class="mashal-home">

    {{-- ========================================================= --}}
    {{-- CINEMATIC HERO                                             --}}
    {{-- ========================================================= --}}

    <section class="mashal-hero">

        <div class="hero-shell">

            <div class="hero-content">

                <span class="hero-eyebrow">
                    Mashal Automotive
                </span>

                <h1 class="hero-title">
                    Drive
                    <span class="line-soft">beyond</span>
                    <span class="line-gold">ordinary.</span>
                </h1>

                <p class="hero-description">
                    Geen gewone autowebsite.
                    Mashal brengt premium modellen, persoonlijk accountbeheer
                    en een verfijnde digitale aankoopervaring samen
                    in één exclusief platform.
                </p>


                <div class="hero-buttons">

                    <a
                        class="hero-primary"
                        href="{{ route('catalog') }}"
                    >
                        Ontdek de collectie
                        <span aria-hidden="true">→</span>
                    </a>

                    @guest

                        <a
                            class="hero-secondary"
                            href="{{ route('register') }}"
                        >
                            Word onderdeel van Mashal
                        </a>

                    @else

                        <a
                            class="hero-secondary"
                            href="{{ route('account') }}"
                        >
                            Open mijn account
                        </a>

                    @endguest

                </div>

            </div>


            <aside class="hero-side">

                <div class="hero-stat">
                    <small>
                        Curated collection
                    </small>

                    <strong>
                        04 premium
                    </strong>

                    <span>
                        Zorgvuldig geselecteerde modellen
                    </span>
                </div>


                <div class="hero-stat">
                    <small>
                        Digital experience
                    </small>

                    <strong>
                        100% online
                    </strong>

                    <span>
                        Van selectie tot bestelling
                    </span>
                </div>


                <div class="hero-stat">
                    <small>
                        Mashal account
                    </small>

                    <strong>
                        Secure
                    </strong>

                    <span>
                        Met e-mailverificatie en beveiligd beheer
                    </span>
                </div>

            </aside>

        </div>


        <div class="hero-scroll">
            Scroll to explore
        </div>

    </section>


    {{-- ========================================================= --}}
    {{-- BRAND STRIP                                                --}}
    {{-- ========================================================= --}}

    <section class="brand-strip">

        <div class="brand-strip-inner">

            <div class="brand-name">
                Audi
            </div>

            <div class="brand-name">
                Mercedes-Benz
            </div>

            <div class="brand-name">
                BMW
            </div>

            <div class="brand-name">
                Volkswagen
            </div>

        </div>

    </section>


    {{-- ========================================================= --}}
    {{-- INTRO                                                      --}}
    {{-- ========================================================= --}}

    <section class="intro-section">

        <div class="intro-grid">

            <div>

                <span class="section-label">
                    The Mashal standard
                </span>

                <h2 class="intro-heading">
                    Meer dan auto's.
                    Een complete ervaring.
                </h2>

            </div>


            <div class="intro-copy">

                <p class="lead">
                    Mashal is ontworpen voor mensen
                    die waarde hechten aan uitstraling,
                    eenvoud en vertrouwen.
                </p>

                <p>
                    Ontdek voertuigen in een rustige, luxe omgeving.
                    Vergelijk modellen, voeg jouw keuze toe aan de winkelwagen
                    en beheer alles vanuit één persoonlijk account.
                    Elk onderdeel is ontworpen om premium aan te voelen,
                    zonder het proces ingewikkeld te maken.
                </p>

            </div>

        </div>

    </section>


    {{-- ========================================================= --}}
    {{-- EXPERIENCE                                                 --}}
    {{-- ========================================================= --}}

    <section class="experience-section">

        <div class="experience-grid">

            <article class="experience-card one">

                <div class="experience-content">

                    <div class="experience-number">
                        01 / DISCOVER
                    </div>

                    <h3>
                        Ontdek zonder afleiding.
                    </h3>

                    <p>
                        Premium modellen gepresenteerd met duidelijke
                        specificaties, prijzen en een sterke visuele ervaring.
                    </p>

                </div>

            </article>


            <article class="experience-card two">

                <div class="experience-content">

                    <div class="experience-number">
                        02 / SELECT
                    </div>

                    <h3>
                        Kies met vertrouwen.
                    </h3>

                    <p>
                        Voeg jouw favoriete model toe aan je winkelwagen
                        en houd jouw selectie overzichtelijk bij.
                    </p>

                </div>

            </article>


            <article class="experience-card three">

                <div class="experience-content">

                    <div class="experience-number">
                        03 / EXPERIENCE
                    </div>

                    <h3>
                        Alles vanuit één account.
                    </h3>

                    <p>
                        Beheer gegevens, beveiliging en bestellingen
                        vanuit jouw persoonlijke Mashal-omgeving.
                    </p>

                </div>

            </article>

        </div>

    </section>


    {{-- ========================================================= --}}
    {{-- FEATURED COLLECTION                                        --}}
    {{-- ========================================================= --}}

    <section
        class="collection-section"
        id="collection"
    >

        <div class="collection-head">

            <div>

                <span class="section-label">
                    Selected for Mashal
                </span>

                <h2>
                    Een collectie met karakter.
                </h2>

            </div>

            <p>
                Vier verschillende auto's.
                Eén standaard: uitstraling, kwaliteit en rijbeleving.
            </p>

        </div>


        <div class="car-grid">

            <article class="car-card">

                <div class="car-image">

                    <span class="car-badge">
                        Premium hybrid
                    </span>

                    <img
                        src="https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1200&q=85"
                        alt="Audi A6 Sportback"
                        loading="lazy"
                    >

                </div>

                <div class="car-body">

                    <small>
                        Audi · 2024
                    </small>

                    <h3>
                        A6 Sportback
                    </h3>

                    <p>
                        Premium comfort voor dagelijks rijden
                        en lange reizen.
                    </p>

                    <div class="car-footer">

                        <div class="car-price">
                            <span>
                                Vanaf
                            </span>

                            <strong>
                                €48.990
                            </strong>
                        </div>

                        <a
                            class="car-link"
                            href="{{ route('car', ['id' => 1]) }}"
                            aria-label="Bekijk Audi A6 Sportback"
                        >
                            →
                        </a>

                    </div>

                </div>

            </article>


            <article class="car-card">

                <div class="car-image">

                    <span class="car-badge">
                        Executive
                    </span>

                    <img
                        src="https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=1200&q=85"
                        alt="BMW X5"
                        loading="lazy"
                    >

                </div>

                <div class="car-body">

                    <small>
                        BMW · 2023
                    </small>

                    <h3>
                        X5
                    </h3>

                    <p>
                        Ruimte, stijl en krachtige prestaties
                        voor iedere route.
                    </p>

                    <div class="car-footer">

                        <div class="car-price">
                            <span>
                                Vanaf
                            </span>

                            <strong>
                                €67.850
                            </strong>
                        </div>

                        <a
                            class="car-link"
                            href="{{ route('car', ['id' => 3]) }}"
                            aria-label="Bekijk BMW X5"
                        >
                            →
                        </a>

                    </div>

                </div>

            </article>


            <article class="car-card">

                <div class="car-image">

                    <span class="car-badge">
                        Performance
                    </span>

                    <img
                        src="https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1200&q=85"
                        alt="Volkswagen Golf GTI"
                        loading="lazy"
                    >

                </div>

                <div class="car-body">

                    <small>
                        Volkswagen · 2024
                    </small>

                    <h3>
                        Golf GTI
                    </h3>

                    <p>
                        Sportieve stijl voor dagelijks gebruik
                        en de weekendtrip.
                    </p>

                    <div class="car-footer">

                        <div class="car-price">
                            <span>
                                Vanaf
                            </span>

                            <strong>
                                €34.990
                            </strong>
                        </div>

                        <a
                            class="car-link"
                            href="{{ route('car', ['id' => 4]) }}"
                            aria-label="Bekijk Volkswagen Golf GTI"
                        >
                            →
                        </a>

                    </div>

                </div>

            </article>

        </div>


        <div class="collection-action">

            <a
                class="primary-btn"
                href="{{ route('catalog') }}"
            >
                Bekijk de volledige collectie
            </a>

        </div>

    </section>


    {{-- ========================================================= --}}
    {{-- PROCESS                                                    --}}
    {{-- ========================================================= --}}

    <section class="process-section">

        <div class="process-shell">

            <div class="process-title">

                <span class="section-label">
                    Simple by design
                </span>

                <h2>
                    Van eerste blik
                    tot bestelling.
                </h2>

                <p>
                    Een premium ervaring hoeft niet ingewikkeld te zijn.
                    Mashal houdt iedere stap duidelijk en beheersbaar.
                </p>

            </div>


            <div class="process-list">

                <div class="process-item">

                    <div class="process-step">
                        01
                    </div>

                    <h3>
                        Ontdek jouw model
                    </h3>

                    <p>
                        Bekijk de collectie en open een voertuig
                        voor uitgebreide informatie over model,
                        bouwjaar, brandstof en prijs.
                    </p>

                </div>


                <div class="process-item">

                    <div class="process-step">
                        02
                    </div>

                    <h3>
                        Bouw jouw selectie
                    </h3>

                    <p>
                        Voeg een auto toe aan je winkelwagen
                        en controleer jouw keuze voordat je verdergaat.
                    </p>

                </div>


                <div class="process-item">

                    <div class="process-step">
                        03
                    </div>

                    <h3>
                        Beveilig jouw account
                    </h3>

                    <p>
                        Bevestig je e-mailadres met de unieke
                        verificatiecode van Mashal.
                    </p>

                </div>


                <div class="process-item">

                    <div class="process-step">
                        04
                    </div>

                    <h3>
                        Plaats je bestelling
                    </h3>

                    <p>
                        Rond de checkout af en ontvang automatisch
                        een bestelbevestiging met je bestelnummer
                        en volledige overzicht.
                    </p>

                </div>

            </div>

        </div>

    </section>


    {{-- ========================================================= --}}
    {{-- FINAL CTA                                                  --}}
    {{-- ========================================================= --}}

    <section class="final-cta">

        <div class="final-inner">

            <div class="final-copy">

                <span class="section-label">
                    Your next drive
                </span>

                @guest

                    <h2>
                        Jouw volgende
                        hoofdstuk begint hier.
                    </h2>

                    <p>
                        Maak je Mashal-account aan,
                        verifieer je e-mailadres
                        en ontdek de volledige collectie.
                    </p>


                    <div class="hero-buttons">

                        <a
                            class="hero-primary"
                            href="{{ route('register') }}"
                        >
                            Account aanmaken
                            <span aria-hidden="true">→</span>
                        </a>

                        <a
                            class="hero-secondary"
                            href="{{ route('catalog') }}"
                        >
                            Eerst de collectie bekijken
                        </a>

                    </div>

                @else

                    <h2>
                        Welkom terug,
                        {{ Auth::user()->name }}.
                    </h2>

                    <p>
                        Ontdek de collectie of open je account
                        om je gegevens en bestellingen te beheren.
                    </p>


                    <div class="hero-buttons">

                        <a
                            class="hero-primary"
                            href="{{ route('catalog') }}"
                        >
                            Ontdek de collectie
                            <span aria-hidden="true">→</span>
                        </a>

                        <a
                            class="hero-secondary"
                            href="{{ route('account') }}"
                        >
                            Mijn account
                        </a>

                    </div>

                @endguest

            </div>

        </div>

    </section>

</div>

@endsection
