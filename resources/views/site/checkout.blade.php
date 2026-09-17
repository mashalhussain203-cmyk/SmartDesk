@extends('layouts.site-layout')

@section('title', 'Mashal | Checkout')

@push('styles')
<style>
    .checkout-page {
        position: relative;
        overflow: hidden;
        padding: 76px 0 110px;
        background:
            radial-gradient(
                circle at 12% 8%,
                rgba(215,164,95,.075),
                transparent 24rem
            ),
            radial-gradient(
                circle at 88% 16%,
                rgba(255,255,255,.022),
                transparent 28rem
            ),
            #08090b;
    }

    .checkout-page::before {
        content: "CHECKOUT";
        position: absolute;
        right: -24px;
        top: 24px;
        color: rgba(255,255,255,.014);
        font-size: clamp(110px, 16vw, 260px);
        font-weight: 950;
        line-height: .8;
        letter-spacing: -.07em;
        pointer-events: none;
        user-select: none;
    }

    .checkout-shell {
        position: relative;
        z-index: 2;
        width: min(100% - 48px, 1240px);
        margin-inline: auto;
    }

    /* ========================================================= */
    /* HEADER                                                     */
    /* ========================================================= */

    .checkout-header {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: end;
        gap: 40px;
        margin-bottom: 34px;
    }

    .checkout-kicker {
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

    .checkout-kicker::before {
        content: "";
        width: 30px;
        height: 1px;
        background: #d7a45f;
    }

    .checkout-title {
        margin: 0;
        color: #ffffff;
        font-size: clamp(46px, 5.8vw, 78px);
        line-height: .95;
        letter-spacing: -.065em;
        font-weight: 950;
    }

    .checkout-title span {
        color: #efc985;
    }

    .checkout-subtitle {
        max-width: 680px;
        margin: 18px 0 0;
        color: #858b92;
        font-size: 14px;
        line-height: 1.85;
    }

    .checkout-status {
        min-width: 190px;
        padding: 18px 20px;
        border: 1px solid rgba(91,214,149,.16);
        border-radius: 18px;
        background:
            linear-gradient(
                145deg,
                rgba(91,214,149,.06),
                rgba(91,214,149,.02)
            );
        box-shadow: 0 16px 40px rgba(0,0,0,.16);
    }

    .checkout-status small {
        display: block;
        margin-bottom: 5px;
        color: #6f8f7d;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .checkout-status strong {
        display: block;
        color: #b8efcf;
        font-size: 14px;
    }

    /* ========================================================= */
    /* PROGRESS                                                   */
    /* ========================================================= */

    .checkout-progress {
        margin-bottom: 28px;
        padding: 14px 16px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 18px;
        background: rgba(255,255,255,.024);
    }

    .progress-step {
        position: relative;
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
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 50%;
        background: rgba(255,255,255,.025);
        color: #747a81;
        font-size: 9px;
    }

    .progress-step.done,
    .progress-step.active {
        color: #d5d6d2;
    }

    .progress-step.done .progress-number {
        border-color: rgba(91,214,149,.20);
        background: rgba(91,214,149,.07);
        color: #9ce7bc;
    }

    .progress-step.active .progress-number {
        border-color: rgba(215,164,95,.25);
        background: rgba(215,164,95,.09);
        color: #edc47f;
    }

    /* ========================================================= */
    /* LAYOUT                                                     */
    /* ========================================================= */

    .checkout-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(340px, .85fr);
        gap: 24px;
        align-items: start;
    }

    .checkout-main,
    .checkout-summary-card,
    .checkout-empty {
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 28px;
        background:
            linear-gradient(
                145deg,
                rgba(255,255,255,.043),
                rgba(255,255,255,.015)
            );
        box-shadow: 0 18px 50px rgba(0,0,0,.20);
    }

    .checkout-main {
        padding: 28px;
    }

    .checkout-summary-card {
        position: sticky;
        top: 106px;
        padding: 26px;
    }

    .checkout-section + .checkout-section {
        margin-top: 34px;
        padding-top: 30px;
        border-top: 1px solid rgba(255,255,255,.07);
    }

    .checkout-section-label {
        display: inline-block;
        margin-bottom: 7px;
        color: #a37743;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .17em;
        text-transform: uppercase;
    }

    .checkout-section h2 {
        margin: 0 0 8px;
        color: #ffffff;
        font-size: 24px;
        letter-spacing: -.035em;
    }

    .checkout-section-intro {
        margin: 0 0 22px;
        color: #747a81;
        font-size: 12px;
        line-height: 1.8;
    }

    /* ========================================================= */
    /* ACCOUNT CARD                                               */
    /* ========================================================= */

    .account-card {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .account-field {
        padding: 17px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 16px;
        background: rgba(255,255,255,.025);
    }

    .account-field small {
        display: block;
        margin-bottom: 5px;
        color: #62686f;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .13em;
        text-transform: uppercase;
    }

    .account-field strong {
        display: block;
        color: #e1dfda;
        font-size: 13px;
        word-break: break-word;
    }

    .verified-box {
        margin-top: 12px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 11px;
        border: 1px solid rgba(91,214,149,.18);
        border-radius: 15px;
        background: rgba(91,214,149,.055);
        color: #9ce7bc;
        font-size: 11px;
        line-height: 1.5;
    }

    .verified-icon {
        flex-shrink: 0;
        width: 28px;
        height: 28px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(91,214,149,.20);
        border-radius: 50%;
        background: rgba(91,214,149,.06);
        font-size: 11px;
        font-weight: 900;
    }

    /* ========================================================= */
    /* ORDER ITEMS                                                */
    /* ========================================================= */

    .order-list {
        display: grid;
        gap: 12px;
    }

    .order-item {
        padding: 14px;
        display: grid;
        grid-template-columns: 120px minmax(0, 1fr) auto;
        gap: 16px;
        align-items: center;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 18px;
        background: rgba(255,255,255,.022);
        transition:
            border-color .2s ease,
            background .2s ease;
    }

    .order-item:hover {
        border-color: rgba(215,164,95,.18);
        background: rgba(215,164,95,.025);
    }

    .order-item-media {
        aspect-ratio: 16 / 10;
        overflow: hidden;
        border-radius: 13px;
        background: #0b0d10;
    }

    .order-item-media img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .order-item-media.placeholder {
        display: grid;
        place-items: center;
        color: #6a7077;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .order-item-copy small {
        display: block;
        margin-bottom: 5px;
        color: #9f7544;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .order-item-copy strong {
        display: block;
        color: #ffffff;
        font-size: 17px;
        line-height: 1.2;
        letter-spacing: -.025em;
    }

    .order-item-meta {
        margin-top: 6px;
        color: #737980;
        font-size: 11px;
    }

    .order-item-total {
        min-width: 120px;
        text-align: right;
    }

    .order-item-total small {
        display: block;
        margin-bottom: 3px;
        color: #656b72;
        font-size: 8px;
        font-weight: 850;
        letter-spacing: .11em;
        text-transform: uppercase;
    }

    .order-item-total strong {
        color: #e8e6e0;
        font-size: 17px;
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
        font-size: 25px;
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
        margin: 20px 0;
        background:
            linear-gradient(
                90deg,
                rgba(255,255,255,.08),
                rgba(255,255,255,.03),
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
        font-size: 31px;
        line-height: 1;
        letter-spacing: -.045em;
    }

    .summary-note {
        margin-top: 18px;
        padding: 13px 14px;
        border: 1px solid rgba(215,164,95,.13);
        border-radius: 14px;
        background: rgba(215,164,95,.045);
        color: #7c756d;
        font-size: 9px;
        line-height: 1.7;
    }

    /* ========================================================= */
    /* CHECKOUT FORM / ACTIONS                                    */
    /* ========================================================= */

    .checkout-form-card {
        margin-top: 18px;
    }

    .checkout-alert {
        padding: 14px 15px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 14px;
        background: rgba(255,255,255,.025);
    }

    .checkout-alert-icon {
        flex-shrink: 0;
        width: 26px;
        height: 26px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(215,164,95,.16);
        border-radius: 50%;
        color: #d9aa68;
        font-size: 10px;
        font-weight: 900;
    }

    .checkout-alert strong {
        color: #d8d6d1;
        font-size: 10px;
    }

    .checkout-alert p {
        margin: 3px 0 0;
        color: #6d737a;
        font-size: 9px;
        line-height: 1.7;
    }

    .checkout-actions {
        margin-top: 14px;
        display: grid;
        gap: 10px;
    }

    .checkout-submit,
    .checkout-back {
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
        cursor: pointer;
        transition:
            transform .2s ease,
            box-shadow .2s ease,
            border-color .2s ease,
            background .2s ease;
    }

    .checkout-submit {
        border: 0;
        background:
            linear-gradient(
                135deg,
                #f1cc8b,
                #ca914c
            );
        color: #14100b;
        box-shadow: 0 16px 40px rgba(215,164,95,.20);
    }

    .checkout-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 24px 56px rgba(215,164,95,.30);
    }

    .checkout-back {
        border: 1px solid rgba(255,255,255,.10);
        background: rgba(255,255,255,.03);
        color: #b6b4af;
    }

    .checkout-back:hover {
        transform: translateY(-2px);
        border-color: rgba(215,164,95,.24);
        background: rgba(215,164,95,.05);
    }

    .checkout-security {
        margin-top: 14px;
        display: flex;
        align-items: flex-start;
        gap: 9px;
        color: #555b61;
        font-size: 9px;
        line-height: 1.6;
    }

    .checkout-security-mark {
        flex-shrink: 0;
        width: 20px;
        height: 20px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 50%;
        color: #8d6b42;
        font-size: 8px;
    }

    /* ========================================================= */
    /* EMPTY STATE                                                */
    /* ========================================================= */

    .checkout-empty {
        padding: 64px 28px;
        text-align: center;
    }

    .checkout-empty-icon {
        width: 66px;
        height: 66px;
        margin: 0 auto 20px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(215,164,95,.18);
        border-radius: 50%;
        background: rgba(215,164,95,.06);
        color: #e1b46d;
        font-size: 20px;
        font-weight: 900;
    }

    .checkout-empty h2 {
        margin: 0 0 8px;
        color: #ffffff;
        font-size: 30px;
        letter-spacing: -.04em;
    }

    .checkout-empty p {
        max-width: 520px;
        margin: 0 auto 24px;
        color: #7b8188;
        font-size: 13px;
        line-height: 1.8;
    }

    /* ========================================================= */
    /* TRUST STRIP                                                */
    /* ========================================================= */

    .checkout-trust {
        margin-top: 30px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 20px;
        background: rgba(255,255,255,.02);
        overflow: hidden;
    }

    .checkout-trust-item {
        padding: 20px;
        border-right: 1px solid rgba(255,255,255,.06);
    }

    .checkout-trust-item:last-child {
        border-right: 0;
    }

    .checkout-trust-item small {
        display: block;
        margin-bottom: 5px;
        color: #9d7547;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .checkout-trust-item strong {
        color: #d4d3cf;
        font-size: 11px;
        line-height: 1.5;
    }

    /* ========================================================= */
    /* MESSAGES                                                   */
    /* ========================================================= */

    .checkout-message {
        margin-bottom: 22px;
        padding: 14px 16px;
        border-radius: 14px;
        font-size: 12px;
        line-height: 1.6;
    }

    .checkout-message.success {
        border: 1px solid rgba(91,214,149,.22);
        background: rgba(91,214,149,.08);
        color: #a9efc8;
    }

    .checkout-message.error {
        border: 1px solid rgba(241,123,123,.22);
        background: rgba(241,123,123,.08);
        color: #ffc1c1;
    }

    .checkout-message ul {
        margin: 8px 0 0 18px;
        padding: 0;
    }

    /* ========================================================= */
    /* RESPONSIVE                                                 */
    /* ========================================================= */

    @media (max-width: 1040px) {
        .checkout-header,
        .checkout-layout {
            grid-template-columns: 1fr;
        }

        .checkout-status {
            width: fit-content;
        }

        .checkout-summary-card {
            position: static;
        }
    }

    @media (max-width: 760px) {
        .checkout-page {
            padding: 56px 0 80px;
        }

        .checkout-shell {
            width: min(100% - 32px, 1240px);
        }

        .checkout-progress {
            grid-template-columns: 1fr;
        }

        .checkout-main,
        .checkout-summary-card {
            padding: 22px;
            border-radius: 22px;
        }

        .account-card {
            grid-template-columns: 1fr;
        }

        .order-item {
            grid-template-columns: 96px minmax(0,1fr);
        }

        .order-item-total {
            grid-column: 1 / -1;
            text-align: left;
            padding-top: 10px;
            border-top: 1px solid rgba(255,255,255,.06);
        }

        .checkout-trust {
            grid-template-columns: 1fr;
        }

        .checkout-trust-item {
            border-right: 0;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }

        .checkout-trust-item:last-child {
            border-bottom: 0;
        }
    }
</style>
@endpush


@section('content')

<section class="checkout-page">

    <div class="checkout-shell">

        {{-- ========================================================= --}}
        {{-- HEADER                                                     --}}
        {{-- ========================================================= --}}

        <header class="checkout-header">

            <div>

                <span class="checkout-kicker">
                    Mashal Secure Checkout
                </span>

                <h1 class="checkout-title">
                    Controleer.
                    Bevestig.
                    <span>Rijd verder.</span>
                </h1>

                <p class="checkout-subtitle">
                    Controleer je account, voertuigen en totaalbedrag
                    voordat je de bestelling definitief plaatst.
                    Alles wordt gekoppeld aan jouw persoonlijke Mashal-account.
                </p>

            </div>


            @if (! empty($cart))

                <div class="checkout-status">

                    <small>
                        Accountstatus
                    </small>

                    <strong>
                        ✓ Klaar voor bestelling
                    </strong>

                </div>

            @endif

        </header>


        {{-- ========================================================= --}}
        {{-- PROGRESS                                                   --}}
        {{-- ========================================================= --}}

        <div class="checkout-progress">

            <div class="progress-step done">

                <span class="progress-number">
                    ✓
                </span>

                <span>
                    Selectie
                </span>

            </div>


            <div class="progress-step done">

                <span class="progress-number">
                    ✓
                </span>

                <span>
                    Account
                </span>

            </div>


            <div class="progress-step active">

                <span class="progress-number">
                    03
                </span>

                <span>
                    Bevestigen
                </span>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- SUCCESS / ERROR                                            --}}
        {{-- ========================================================= --}}

        @if (session('success'))

            <div class="checkout-message success">

                <strong>
                    Gelukt.
                </strong>

                {{ session('success') }}

            </div>

        @endif


        @if ($errors->any())

            <div class="checkout-message error">

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

            <div class="checkout-empty">

                <div class="checkout-empty-icon">
                    M
                </div>

                <h2>
                    Je selectie is nog leeg.
                </h2>

                <p>
                    Voeg eerst een model toe aan je winkelwagen
                    voordat je de Mashal-checkout kunt afronden.
                </p>

                <a
                    class="primary-btn"
                    href="{{ route('catalog') }}"
                >
                    Ontdek de collectie
                </a>

            </div>

        @else

            <div class="checkout-layout">

                {{-- ========================================================= --}}
                {{-- LEFT COLUMN                                                --}}
                {{-- ========================================================= --}}

                <div class="checkout-main">

                    {{-- ACCOUNT --}}

                    <section class="checkout-section">

                        <span class="checkout-section-label">
                            01 / Account
                        </span>

                        <h2>
                            Jouw gegevens
                        </h2>

                        <p class="checkout-section-intro">
                            Deze gegevens worden gebruikt
                            voor je bestelling en bestelbevestiging.
                        </p>


                        <div class="account-card">

                            <div class="account-field">

                                <small>
                                    Naam
                                </small>

                                <strong>
                                    {{ Auth::user()->name }}
                                </strong>

                            </div>


                            <div class="account-field">

                                <small>
                                    E-mailadres
                                </small>

                                <strong>
                                    {{ Auth::user()->email }}
                                </strong>

                            </div>

                        </div>


                        <div class="verified-box">

                            <span class="verified-icon">
                                ✓
                            </span>

                            <span>
                                Je e-mailadres is geverifieerd.
                                Dit account kan de bestelling plaatsen.
                            </span>

                        </div>

                    </section>


                    {{-- ITEMS --}}

                    <section class="checkout-section">

                        <span class="checkout-section-label">
                            02 / Selection
                        </span>

                        <h2>
                            Jouw bestelling
                        </h2>

                        <p class="checkout-section-intro">
                            Controleer de geselecteerde auto's en aantallen
                            voordat je verdergaat.
                        </p>


                        <div class="order-list">

                            @foreach ($cart as $item)

                                <article class="order-item">

                                    @if (! empty($item['image']))

                                        <div class="order-item-media">

                                            <img
                                                src="{{ $item['image'] }}"
                                                alt="{{ $item['brand'] }} {{ $item['model'] }}"
                                                loading="lazy"
                                            >

                                        </div>

                                    @else

                                        <div class="order-item-media placeholder">
                                            Mashal
                                        </div>

                                    @endif


                                    <div class="order-item-copy">

                                        <small>
                                            Selected vehicle
                                        </small>

                                        <strong>
                                            {{ $item['brand'] }}
                                            {{ $item['model'] }}
                                        </strong>

                                        <div class="order-item-meta">
                                            {{ $item['qty'] }}
                                            ×
                                            €{{ number_format($item['price'], 0, ',', '.') }}
                                        </div>

                                    </div>


                                    <div class="order-item-total">

                                        <small>
                                            Subtotaal
                                        </small>

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

                    </section>


                    {{-- CONFIRMATION --}}

                    <section class="checkout-section">

                        <span class="checkout-section-label">
                            03 / Confirmation
                        </span>

                        <h2>
                            Laatste controle
                        </h2>

                        <p class="checkout-section-intro">
                            Zodra je de bestelling plaatst,
                            wordt deze definitief opgeslagen.
                        </p>


                        <div class="checkout-alert">

                            <span class="checkout-alert-icon">
                                i
                            </span>

                            <div>

                                <strong>
                                    Bestelbevestiging
                                </strong>

                                <p>
                                    De bevestiging wordt verzonden naar
                                    <strong>{{ Auth::user()->email }}</strong>.
                                    Controleer daarom nog één keer of alles klopt.
                                </p>

                            </div>

                        </div>

                    </section>

                </div>


                {{-- ========================================================= --}}
                {{-- RIGHT / SUMMARY                                            --}}
                {{-- ========================================================= --}}

                <aside class="checkout-summary-card">

                    <span class="summary-kicker">
                        Order summary
                    </span>

                    <h2 class="summary-title">
                        Besteloverzicht
                    </h2>


                    <div class="summary-list">

                        <div class="summary-row">

                            <span>
                                Verschillende auto's
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
                                Account
                            </span>

                            <strong>
                                Geverifieerd
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


                    <div class="summary-note">
                        Je ziet hier het volledige totaal
                        van de huidige winkelwagen.
                    </div>


                    <div class="checkout-form-card">

                        <form
                            method="POST"
                            action="{{ route('checkout.submit') }}"
                        >
                            @csrf

                            <div class="checkout-actions">

                                <button
                                    class="checkout-submit"
                                    type="submit"
                                >
                                    Bestelling plaatsen
                                    <span aria-hidden="true">→</span>
                                </button>


                                <a
                                    class="checkout-back"
                                    href="{{ route('cart') }}"
                                >
                                    ← Terug naar winkelwagen
                                </a>

                            </div>

                        </form>


                        <div class="checkout-security">

                            <span class="checkout-security-mark">
                                ✓
                            </span>

                            <span>
                                Je bestelling wordt opgeslagen
                                onder je beveiligde Mashal-account.
                            </span>

                        </div>

                    </div>

                </aside>

            </div>

        @endif


        {{-- ========================================================= --}}
        {{-- TRUST STRIP                                                --}}
        {{-- ========================================================= --}}

        <div class="checkout-trust">

            <div class="checkout-trust-item">

                <small>
                    Verified
                </small>

                <strong>
                    Alleen geverifieerde accounts kunnen bestellen
                </strong>

            </div>


            <div class="checkout-trust-item">

                <small>
                    Confirmation
                </small>

                <strong>
                    Automatische bevestiging na plaatsing
                </strong>

            </div>


            <div class="checkout-trust-item">

                <small>
                    Mashal account
                </small>

                <strong>
                    Bestellingen blijven gekoppeld aan jouw account
                </strong>

            </div>

        </div>

    </div>

</section>

@endsection
