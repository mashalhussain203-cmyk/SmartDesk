@extends('layouts.site-layout')

@section('title', 'Mashal | Winkelwagen')

@push('styles')
<style>
    .cart-page {
        position: relative;
        overflow: hidden;
        padding: 76px 0 112px;
        background:
            radial-gradient(
                circle at 9% 6%,
                rgba(215, 164, 95, .075),
                transparent 24rem
            ),
            radial-gradient(
                circle at 88% 16%,
                rgba(255, 255, 255, .025),
                transparent 28rem
            ),
            #08090b;
    }

    .cart-page::before {
        content: "MASHAL";
        position: absolute;
        right: -42px;
        top: 34px;
        color: rgba(255, 255, 255, .014);
        font-size: clamp(120px, 18vw, 285px);
        font-weight: 950;
        line-height: .8;
        letter-spacing: -.08em;
        pointer-events: none;
        user-select: none;
    }

    .cart-shell {
        position: relative;
        z-index: 2;
        width: min(100% - 48px, 1240px);
        margin-inline: auto;
    }

    /* ========================================================= */
    /* HEADER                                                     */
    /* ========================================================= */

    .cart-header {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: end;
        gap: 40px;
        margin-bottom: 34px;
    }

    .cart-kicker {
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

    .cart-kicker::before {
        content: "";
        width: 30px;
        height: 1px;
        background: #d7a45f;
    }

    .cart-title {
        max-width: 820px;
        margin: 0;
        color: #ffffff;
        font-size: clamp(48px, 6vw, 82px);
        line-height: .94;
        letter-spacing: -.065em;
        font-weight: 950;
    }

    .cart-title span {
        color: #efc985;
    }

    .cart-description {
        max-width: 680px;
        margin: 18px 0 0;
        color: #858b92;
        font-size: 14px;
        line-height: 1.85;
    }

    .cart-count-card {
        min-width: 180px;
        padding: 18px 20px;
        border: 1px solid rgba(255, 255, 255, .09);
        border-radius: 18px;
        background:
            linear-gradient(
                145deg,
                rgba(255, 255, 255, .055),
                rgba(255, 255, 255, .018)
            );
        box-shadow: 0 16px 40px rgba(0, 0, 0, .16);
        backdrop-filter: blur(16px);
    }

    .cart-count-card small {
        display: block;
        margin-bottom: 5px;
        color: #7f858c;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .cart-count-card strong {
        display: block;
        color: #ffffff;
        font-size: 25px;
        line-height: 1;
        letter-spacing: -.035em;
    }

    /* ========================================================= */
    /* PROGRESS                                                   */
    /* ========================================================= */

    .cart-progress {
        margin-bottom: 28px;
        padding: 14px 16px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        border: 1px solid rgba(255, 255, 255, .07);
        border-radius: 18px;
        background: rgba(255, 255, 255, .024);
    }

    .progress-step {
        padding: 10px 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #676d74;
        font-size: 10px;
        font-weight: 850;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .progress-number {
        width: 28px;
        height: 28px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: 50%;
        background: rgba(255, 255, 255, .025);
        color: #747a81;
        font-size: 9px;
    }

    .progress-step.active {
        color: #d8d6d1;
    }

    .progress-step.active .progress-number {
        border-color: rgba(215, 164, 95, .25);
        background: rgba(215, 164, 95, .09);
        color: #edc47f;
    }

    /* ========================================================= */
    /* MESSAGES                                                   */
    /* ========================================================= */

    .cart-message {
        margin-bottom: 22px;
        padding: 14px 16px;
        border-radius: 14px;
        font-size: 12px;
        line-height: 1.6;
        backdrop-filter: blur(12px);
    }

    .cart-message.success {
        border: 1px solid rgba(91, 214, 149, .22);
        background: rgba(91, 214, 149, .08);
        color: #a9efc8;
    }

    .cart-message.error {
        border: 1px solid rgba(241, 123, 123, .22);
        background: rgba(241, 123, 123, .08);
        color: #ffc1c1;
    }

    .cart-message ul {
        margin: 8px 0 0 18px;
        padding: 0;
    }

    /* ========================================================= */
    /* LAYOUT                                                     */
    /* ========================================================= */

    .cart-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.18fr) minmax(340px, .82fr);
        gap: 24px;
        align-items: start;
    }

    .cart-main,
    .cart-summary {
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: 28px;
        background:
            linear-gradient(
                145deg,
                rgba(255, 255, 255, .043),
                rgba(255, 255, 255, .015)
            );
        box-shadow: 0 20px 55px rgba(0, 0, 0, .22);
    }

    .cart-main {
        padding: 28px;
    }

    .cart-summary {
        position: sticky;
        top: 106px;
        padding: 26px;
    }

    .cart-section-kicker {
        display: inline-block;
        margin-bottom: 7px;
        color: #a37743;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .17em;
        text-transform: uppercase;
    }

    .cart-section-title {
        margin: 0;
        color: #ffffff;
        font-size: 25px;
        letter-spacing: -.04em;
    }

    .cart-section-copy {
        margin: 8px 0 23px;
        color: #747a81;
        font-size: 12px;
        line-height: 1.8;
    }

    /* ========================================================= */
    /* VEHICLE ITEMS                                              */
    /* ========================================================= */

    .cart-items {
        display: grid;
        gap: 14px;
    }

    .cart-item {
        position: relative;
        padding: 14px;
        display: grid;
        grid-template-columns: 170px minmax(0, 1fr) auto;
        align-items: center;
        gap: 20px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, .07);
        border-radius: 20px;
        background: rgba(255, 255, 255, .022);
        transition:
            transform .24s ease,
            border-color .24s ease,
            background .24s ease;
    }

    .cart-item::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        width: 2px;
        height: 100%;
        opacity: 0;
        background:
            linear-gradient(
                180deg,
                transparent,
                #d7a45f,
                transparent
            );
        transition: opacity .24s ease;
    }

    .cart-item:hover {
        transform: translateY(-3px);
        border-color: rgba(215, 164, 95, .18);
        background: rgba(215, 164, 95, .025);
    }

    .cart-item:hover::before {
        opacity: 1;
    }

    .cart-item-media {
        position: relative;
        aspect-ratio: 16 / 10;
        overflow: hidden;
        border-radius: 15px;
        background: #0b0d10;
    }

    .cart-item-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .45s ease;
    }

    .cart-item:hover .cart-item-media img {
        transform: scale(1.045);
    }

    .cart-item-badge {
        position: absolute;
        left: 10px;
        top: 10px;
        padding: 6px 8px;
        border: 1px solid rgba(255, 255, 255, .12);
        border-radius: 999px;
        background: rgba(5, 6, 8, .65);
        backdrop-filter: blur(8px);
        color: #efc985;
        font-size: 7px;
        font-weight: 900;
        letter-spacing: .11em;
        text-transform: uppercase;
    }

    .cart-item-content small {
        display: block;
        margin-bottom: 6px;
        color: #9e7442;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .13em;
        text-transform: uppercase;
    }

    .cart-item-content h3 {
        margin: 0;
        color: #ffffff;
        font-size: 21px;
        line-height: 1.15;
        letter-spacing: -.035em;
    }

    .cart-item-meta {
        margin-top: 10px;
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }

    .cart-meta-block span {
        display: block;
        color: #5f656c;
        font-size: 7px;
        font-weight: 900;
        letter-spacing: .11em;
        text-transform: uppercase;
    }

    .cart-meta-block strong {
        display: block;
        margin-top: 2px;
        color: #b9b7b2;
        font-size: 11px;
    }

    .cart-item-total {
        min-width: 130px;
        text-align: right;
    }

    .cart-item-total span {
        display: block;
        margin-bottom: 4px;
        color: #646a71;
        font-size: 8px;
        font-weight: 850;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .cart-item-total strong {
        color: #ffffff;
        font-size: 21px;
        letter-spacing: -.035em;
    }

    /* ========================================================= */
    /* ACCOUNT READINESS                                          */
    /* ========================================================= */

    .account-readiness {
        margin-top: 28px;
        padding-top: 28px;
        border-top: 1px solid rgba(255, 255, 255, .07);
    }

    .readiness-card {
        padding: 17px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        border-radius: 16px;
    }

    .readiness-card.ready {
        border: 1px solid rgba(91, 214, 149, .17);
        background: rgba(91, 214, 149, .055);
    }

    .readiness-card.warning {
        border: 1px solid rgba(242, 198, 109, .17);
        background: rgba(242, 198, 109, .055);
    }

    .readiness-card.login {
        border: 1px solid rgba(215, 164, 95, .17);
        background: rgba(215, 164, 95, .055);
    }

    .readiness-icon {
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(255, 255, 255, .10);
        border-radius: 50%;
        color: #d9aa68;
        font-size: 12px;
        font-weight: 900;
    }

    .readiness-card.ready .readiness-icon {
        color: #9ce7bc;
    }

    .readiness-copy strong {
        display: block;
        margin-bottom: 4px;
        color: #dcdad5;
        font-size: 12px;
    }

    .readiness-copy p {
        margin: 0;
        color: #72787f;
        font-size: 10px;
        line-height: 1.7;
    }

    /* ========================================================= */
    /* SUMMARY                                                    */
    /* ========================================================= */

    .summary-kicker {
        color: #a37743;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .summary-title {
        margin: 7px 0 22px;
        color: #ffffff;
        font-size: 26px;
        letter-spacing: -.04em;
    }

    .summary-list {
        display: grid;
        gap: 13px;
    }

    .summary-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        color: #777d84;
        font-size: 11px;
    }

    .summary-row strong {
        color: #d9d7d2;
        font-size: 12px;
    }

    .summary-divider {
        height: 1px;
        margin: 21px 0;
        background:
            linear-gradient(
                90deg,
                rgba(255, 255, 255, .08),
                rgba(255, 255, 255, .03),
                transparent
            );
    }

    .summary-total {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 18px;
    }

    .summary-total span {
        color: #d7d4cf;
        font-size: 12px;
        font-weight: 850;
    }

    .summary-total strong {
        color: #ffffff;
        font-size: 32px;
        line-height: 1;
        letter-spacing: -.045em;
    }

    .summary-caption {
        margin-top: 11px;
        color: #5d6369;
        font-size: 9px;
        line-height: 1.6;
    }

    /* ========================================================= */
    /* ACTIONS                                                    */
    /* ========================================================= */

    .cart-actions {
        margin-top: 24px;
        display: grid;
        gap: 10px;
    }

    .cart-primary,
    .cart-secondary {
        width: 100%;
        min-height: 52px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        border-radius: 999px;
        text-decoration: none;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .03em;
        transition:
            transform .2s ease,
            box-shadow .2s ease,
            border-color .2s ease,
            background .2s ease;
    }

    .cart-primary {
        background:
            linear-gradient(
                135deg,
                #f1cc8b,
                #ca914c
            );
        color: #14100b;
        box-shadow: 0 16px 40px rgba(215, 164, 95, .20);
    }

    .cart-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 24px 56px rgba(215, 164, 95, .30);
    }

    .cart-secondary {
        border: 1px solid rgba(255, 255, 255, .10);
        background: rgba(255, 255, 255, .03);
        color: #b6b4af;
    }

    .cart-secondary:hover {
        transform: translateY(-2px);
        border-color: rgba(215, 164, 95, .24);
        background: rgba(215, 164, 95, .05);
    }

    .summary-security {
        margin-top: 16px;
        display: flex;
        align-items: flex-start;
        gap: 9px;
        color: #555b61;
        font-size: 9px;
        line-height: 1.6;
    }

    .summary-security-mark {
        flex-shrink: 0;
        width: 20px;
        height: 20px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(255, 255, 255, .07);
        border-radius: 50%;
        color: #8d6b42;
        font-size: 8px;
    }

    /* ========================================================= */
    /* EMPTY STATE                                                */
    /* ========================================================= */

    .cart-empty {
        padding: 70px 28px;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: 28px;
        background:
            linear-gradient(
                145deg,
                rgba(255, 255, 255, .04),
                rgba(255, 255, 255, .014)
            );
        box-shadow: 0 20px 55px rgba(0, 0, 0, .20);
    }

    .cart-empty-mark {
        width: 70px;
        height: 70px;
        margin: 0 auto 22px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(215, 164, 95, .18);
        border-radius: 50%;
        background: rgba(215, 164, 95, .065);
        color: #e2b56d;
        font-size: 22px;
        font-weight: 950;
    }

    .cart-empty h2 {
        margin: 0 0 10px;
        color: #ffffff;
        font-size: 32px;
        line-height: 1.05;
        letter-spacing: -.045em;
    }

    .cart-empty p {
        max-width: 560px;
        margin: 0 auto 26px;
        color: #7c8289;
        font-size: 13px;
        line-height: 1.8;
    }

    /* ========================================================= */
    /* EXPERIENCE STRIP                                           */
    /* ========================================================= */

    .cart-benefits {
        margin-top: 32px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        border: 1px solid rgba(255, 255, 255, .07);
        border-radius: 20px;
        overflow: hidden;
        background: rgba(255, 255, 255, .02);
    }

    .benefit {
        padding: 21px;
        border-right: 1px solid rgba(255, 255, 255, .06);
    }

    .benefit:last-child {
        border-right: 0;
    }

    .benefit small {
        display: block;
        margin-bottom: 5px;
        color: #9d7547;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .benefit strong {
        display: block;
        color: #d4d3cf;
        font-size: 11px;
        line-height: 1.5;
    }

    .benefit span {
        display: block;
        margin-top: 4px;
        color: #666c73;
        font-size: 9px;
        line-height: 1.6;
    }

    /* ========================================================= */
    /* RESPONSIVE                                                 */
    /* ========================================================= */

    @media (max-width: 1040px) {
        .cart-header,
        .cart-layout {
            grid-template-columns: 1fr;
        }

        .cart-count-card {
            width: fit-content;
        }

        .cart-summary {
            position: static;
        }
    }

    @media (max-width: 760px) {
        .cart-page {
            padding: 56px 0 80px;
        }

        .cart-shell {
            width: min(100% - 32px, 1240px);
        }

        .cart-progress {
            grid-template-columns: 1fr;
        }

        .cart-main,
        .cart-summary {
            padding: 22px;
            border-radius: 22px;
        }

        .cart-item {
            grid-template-columns: 105px minmax(0, 1fr);
            gap: 14px;
        }

        .cart-item-total {
            grid-column: 1 / -1;
            min-width: 0;
            padding-top: 12px;
            text-align: left;
            border-top: 1px solid rgba(255, 255, 255, .06);
        }

        .cart-benefits {
            grid-template-columns: 1fr;
        }

        .benefit {
            border-right: 0;
            border-bottom: 1px solid rgba(255, 255, 255, .06);
        }

        .benefit:last-child {
            border-bottom: 0;
        }
    }

    @media (max-width: 520px) {
        .cart-item {
            grid-template-columns: 1fr;
        }

        .cart-item-media {
            aspect-ratio: 16 / 9;
        }

        .cart-item-total {
            grid-column: auto;
        }
    }
</style>
@endpush


@section('content')

<section class="cart-page">

    <div class="cart-shell">

        {{-- ========================================================= --}}
        {{-- HEADER                                                     --}}
        {{-- ========================================================= --}}

        <header class="cart-header">

            <div>

                <span class="cart-kicker">
                    Mashal Selection
                </span>

                <h1 class="cart-title">
                    Jouw selectie.
                    <span>
                        Klaar voor de volgende stap.
                    </span>
                </h1>

                <p class="cart-description">
                    Bekijk de modellen die je hebt geselecteerd,
                    controleer aantallen en totaalbedrag
                    en ga daarna veilig verder naar de Mashal-checkout.
                </p>

            </div>


            @if (! empty($cart))

                <div class="cart-count-card">

                    <small>
                        In jouw selectie
                    </small>

                    <strong>
                        {{ collect($cart)->sum('qty') }}
                        {{ collect($cart)->sum('qty') === 1 ? 'auto' : "auto's" }}
                    </strong>

                </div>

            @endif

        </header>


        {{-- ========================================================= --}}
        {{-- PROGRESS                                                   --}}
        {{-- ========================================================= --}}

        <div class="cart-progress">

            <div class="progress-step active">

                <span class="progress-number">
                    01
                </span>

                <span>
                    Selectie
                </span>

            </div>


            <div class="progress-step">

                <span class="progress-number">
                    02
                </span>

                <span>
                    Account
                </span>

            </div>


            <div class="progress-step">

                <span class="progress-number">
                    03
                </span>

                <span>
                    Checkout
                </span>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- MESSAGES                                                   --}}
        {{-- ========================================================= --}}

        @if (session('success'))

            <div class="cart-message success">

                <strong>
                    Gelukt.
                </strong>

                {{ session('success') }}

            </div>

        @endif


        @if ($errors->any())

            <div class="cart-message error">

                <strong>
                    Er ging iets mis.
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


        {{-- ========================================================= --}}
        {{-- EMPTY CART                                                 --}}
        {{-- ========================================================= --}}

        @if (empty($cart))

            <div class="cart-empty">

                <div class="cart-empty-mark">
                    M
                </div>

                <h2>
                    Jouw selectie wacht nog.
                </h2>

                <p>
                    Je hebt nog geen auto toegevoegd.
                    Ontdek de Mashal-collectie en kies een model
                    dat past bij jouw stijl, route en volgende hoofdstuk.
                </p>

                <a
                    class="primary-btn"
                    href="{{ route('catalog') }}"
                >
                    Ontdek de collectie
                </a>

            </div>

        @else

            <div class="cart-layout">

                {{-- ========================================================= --}}
                {{-- LEFT / CART ITEMS                                          --}}
                {{-- ========================================================= --}}

                <div class="cart-main">

                    <span class="cart-section-kicker">
                        01 / Selected vehicles
                    </span>

                    <h2 class="cart-section-title">
                        Jouw voertuigen
                    </h2>

                    <p class="cart-section-copy">
                        Je hebt
                        {{ count($cart) }}
                        {{ count($cart) === 1 ? 'verschillend model' : 'verschillende modellen' }}
                        geselecteerd.
                    </p>


                    <div class="cart-items">

                        @foreach ($cart as $item)

                            <article class="cart-item">

                                <div class="cart-item-media">

                                    <img
                                        src="{{ $item['image'] }}"
                                        alt="{{ $item['brand'] }} {{ $item['model'] }}"
                                        loading="lazy"
                                    >

                                    <span class="cart-item-badge">
                                        Mashal Selection
                                    </span>

                                </div>


                                <div class="cart-item-content">

                                    <small>
                                        Selected vehicle
                                    </small>

                                    <h3>
                                        {{ $item['brand'] }}
                                        {{ $item['model'] }}
                                    </h3>


                                    <div class="cart-item-meta">

                                        <div class="cart-meta-block">

                                            <span>
                                                Aantal
                                            </span>

                                            <strong>
                                                {{ $item['qty'] }}
                                            </strong>

                                        </div>


                                        <div class="cart-meta-block">

                                            <span>
                                                Prijs per stuk
                                            </span>

                                            <strong>
                                                €{{ number_format($item['price'], 0, ',', '.') }}
                                            </strong>

                                        </div>

                                    </div>

                                </div>


                                <div class="cart-item-total">

                                    <span>
                                        Subtotaal
                                    </span>

                                    <strong>
                                        €{{ number_format(
                                            $item['price'] * $item['qty'],
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </strong>

                                </div>

                            </article>

                        @endforeach

                    </div>


                    {{-- ========================================================= --}}
                    {{-- ACCOUNT READINESS                                           --}}
                    {{-- ========================================================= --}}

                    <div class="account-readiness">

                        <span class="cart-section-kicker">
                            02 / Account readiness
                        </span>

                        @auth

                            @if (auth()->user()->email_verified_at)

                                <div class="readiness-card ready">

                                    <span class="readiness-icon">
                                        ✓
                                    </span>

                                    <div class="readiness-copy">

                                        <strong>
                                            Je account is klaar voor checkout.
                                        </strong>

                                        <p>
                                            Je bent ingelogd en je e-mailadres is geverifieerd.
                                            Je kunt direct doorgaan naar de volgende stap.
                                        </p>

                                    </div>

                                </div>

                            @else

                                <div class="readiness-card warning">

                                    <span class="readiness-icon">
                                        !
                                    </span>

                                    <div class="readiness-copy">

                                        <strong>
                                            Verifieer eerst je e-mailadres.
                                        </strong>

                                        <p>
                                            Je account is ingelogd,
                                            maar e-mailverificatie is nog vereist
                                            voordat je een bestelling kunt plaatsen.
                                        </p>

                                    </div>

                                </div>

                            @endif

                        @else

                            <div class="readiness-card login">

                                <span class="readiness-icon">
                                    M
                                </span>

                                <div class="readiness-copy">

                                    <strong>
                                        Log in om verder te gaan.
                                    </strong>

                                    <p>
                                        Je selectie blijft zichtbaar,
                                        maar je hebt een Mashal-account nodig
                                        om de checkout te kunnen afronden.
                                    </p>

                                </div>

                            </div>

                        @endauth

                    </div>

                </div>


                {{-- ========================================================= --}}
                {{-- RIGHT / SUMMARY                                            --}}
                {{-- ========================================================= --}}

                <aside class="cart-summary">

                    <span class="summary-kicker">
                        Selection summary
                    </span>

                    <h2 class="summary-title">
                        Overzicht
                    </h2>


                    <div class="summary-list">

                        <div class="summary-row">

                            <span>
                                Verschillende modellen
                            </span>

                            <strong>
                                {{ count($cart) }}
                            </strong>

                        </div>


                        <div class="summary-row">

                            <span>
                                Totaal aantal
                            </span>

                            <strong>
                                {{ collect($cart)->sum('qty') }}
                            </strong>

                        </div>


                        <div class="summary-row">

                            <span>
                                Checkoutstatus
                            </span>

                            <strong>
                                @auth
                                    {{ auth()->user()->email_verified_at ? 'Beschikbaar' : 'Verificatie nodig' }}
                                @else
                                    Inloggen vereist
                                @endauth
                            </strong>

                        </div>

                    </div>


                    <div class="summary-divider"></div>


                    <div class="summary-total">

                        <span>
                            Totaal
                        </span>

                        <strong>
                            €{{ number_format($total, 0, ',', '.') }}
                        </strong>

                    </div>


                    <div class="summary-caption">
                        Dit is het totaal van je huidige Mashal-selectie.
                    </div>


                    <div class="cart-actions">

                        @auth

                            @if (auth()->user()->email_verified_at)

                                <a
                                    class="cart-primary"
                                    href="{{ route('checkout') }}"
                                >
                                    Naar checkout
                                    <span aria-hidden="true">→</span>
                                </a>

                            @else

                                <a
                                    class="cart-primary"
                                    href="{{ route('verification.notice') }}"
                                >
                                    E-mail verifiëren
                                    <span aria-hidden="true">→</span>
                                </a>

                            @endif

                        @else

                            <a
                                class="cart-primary"
                                href="{{ route('login') }}"
                            >
                                Inloggen om af te rekenen
                                <span aria-hidden="true">→</span>
                            </a>

                        @endauth


                        <a
                            class="cart-secondary"
                            href="{{ route('catalog') }}"
                        >
                            ← Verder winkelen
                        </a>

                    </div>


                    <div class="summary-security">

                        <span class="summary-security-mark">
                            ✓
                        </span>

                        <span>
                            Bestellingen worden gekoppeld
                            aan je beveiligde Mashal-account.
                        </span>

                    </div>

                </aside>

            </div>

        @endif


        {{-- ========================================================= --}}
        {{-- BENEFITS                                                   --}}
        {{-- ========================================================= --}}

        <div class="cart-benefits">

            <div class="benefit">

                <small>
                    Curated
                </small>

                <strong>
                    Premium voertuigselectie
                </strong>

                <span>
                    Alleen modellen uit de huidige Mashal-collectie.
                </span>

            </div>


            <div class="benefit">

                <small>
                    Verified
                </small>

                <strong>
                    Beveiligde accountflow
                </strong>

                <span>
                    E-mailverificatie voordat een bestelling wordt geplaatst.
                </span>

            </div>


            <div class="benefit">

                <small>
                    Confirmation
                </small>

                <strong>
                    Directe bestelbevestiging
                </strong>

                <span>
                    Na checkout ontvang je automatisch een overzicht per e-mail.
                </span>

            </div>

        </div>

    </div>

</section>

@endsection
