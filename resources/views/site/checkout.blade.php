@extends('layouts.site-layout')

@section('title', 'SmartDesk | Checkout')

@section('content')

<section class="form-page">

    <div class="site-wrap">

        <div class="form-wrapper">

            {{-- ========================================================= --}}
            {{-- HEADER                                                     --}}
            {{-- ========================================================= --}}

            <div style="margin-bottom: 30px;">

                <h1 class="form-title">
                    Checkout
                </h1>

                <p class="form-sub">
                    Controleer je bestelling voordat je deze definitief plaatst.
                    Je bestelling wordt gekoppeld aan je ingelogde SmartDesk-account.
                </p>

            </div>


            {{-- ========================================================= --}}
            {{-- SUCCESS MESSAGE                                             --}}
            {{-- ========================================================= --}}

            @if (session('success'))

                <div
                    class="success"
                    style="margin-bottom: 25px;"
                >
                    <strong>
                        Gelukt!
                    </strong>

                    <br>

                    {{ session('success') }}
                </div>

            @endif


            {{-- ========================================================= --}}
            {{-- ERROR MESSAGE                                               --}}
            {{-- ========================================================= --}}

            @if ($errors->any())

                <div
                    class="error"
                    style="margin-bottom: 25px;"
                >

                    <strong>
                        Er ging iets mis.
                    </strong>

                    <ul style="margin: 10px 0 0 20px;">

                        @foreach ($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- ========================================================= --}}
            {{-- EMPTY CART                                                  --}}
            {{-- ========================================================= --}}

            @if (empty($cart))

                <div
                    class="feature-card"
                    style="
                        text-align: center;
                        padding: 40px 25px;
                    "
                >

                    <div
                        style="
                            width: 64px;
                            height: 64px;
                            margin: 0 auto 20px;
                            border-radius: 50%;
                            background: #f1f1f1;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 28px;
                        "
                    >
                        🛒
                    </div>

                    <h2 style="margin: 0 0 10px;">
                        Je winkelwagen is leeg
                    </h2>

                    <p
                        class="form-sub"
                        style="
                            margin: 0 auto 25px;
                            max-width: 480px;
                        "
                    >
                        Voeg eerst een auto toe aan je winkelwagen
                        voordat je kunt afrekenen.
                    </p>

                    <a
                        class="primary-btn"
                        href="{{ route('catalog') }}"
                    >
                        Bekijk catalogus
                    </a>

                </div>

            @else

                {{-- ========================================================= --}}
                {{-- ACCOUNT INFORMATION                                        --}}
                {{-- ========================================================= --}}

                <div
                    class="feature-card"
                    style="margin-bottom: 30px;"
                >

                    <h2 style="margin-top: 0;">
                        Accountgegevens
                    </h2>

                    <p
                        class="form-sub"
                        style="margin-bottom: 20px;"
                    >
                        Deze gegevens worden gebruikt voor je bestelling
                        en bestelbevestiging.
                    </p>


                    <div class="form-row">

                        <div>

                            <label>
                                Naam
                            </label>

                            <p style="margin-bottom: 0;">
                                {{ Auth::user()->name }}
                            </p>

                        </div>


                        <div>

                            <label>
                                E-mailadres
                            </label>

                            <p
                                style="
                                    margin-bottom: 0;
                                    word-break: break-word;
                                "
                            >
                                {{ Auth::user()->email }}
                            </p>

                        </div>

                    </div>


                    <div
                        style="
                            margin-top: 20px;
                            padding: 15px;
                            border-radius: 8px;
                            background: #e8f7ee;
                            color: #187a42;
                        "
                    >
                        <strong>
                            ✓ E-mailadres geverifieerd
                        </strong>

                        <div style="margin-top: 5px;">
                            Je bestelling kan met dit account worden geplaatst.
                        </div>
                    </div>

                </div>


                {{-- ========================================================= --}}
                {{-- ORDER ITEMS                                                --}}
                {{-- ========================================================= --}}

                <div style="margin-bottom: 30px;">

                    <h2 style="margin-bottom: 8px;">
                        Je bestelling
                    </h2>

                    <p
                        class="form-sub"
                        style="margin-bottom: 25px;"
                    >
                        Controleer de auto's en aantallen voordat je verdergaat.
                    </p>


                    @foreach ($cart as $item)

                        <div
                            class="feature-card"
                            style="margin-bottom: 15px;"
                        >

                            <div
                                style="
                                    display: flex;
                                    justify-content: space-between;
                                    align-items: center;
                                    gap: 20px;
                                    flex-wrap: wrap;
                                "
                            >

                                {{-- CAR --}}
                                <div
                                    style="
                                        display: flex;
                                        align-items: center;
                                        gap: 18px;
                                        flex: 1;
                                        min-width: 260px;
                                    "
                                >

                                    @if (! empty($item['image']))

                                        <img
                                            src="{{ $item['image'] }}"
                                            alt="{{ $item['brand'] }} {{ $item['model'] }}"
                                            style="
                                                width: 130px;
                                                height: 85px;
                                                object-fit: cover;
                                                border-radius: 12px;
                                                display: block;
                                            "
                                        >

                                    @endif


                                    <div>

                                        <strong
                                            style="
                                                display: block;
                                                margin-bottom: 7px;
                                                font-size: 18px;
                                            "
                                        >
                                            {{ $item['brand'] }}
                                            {{ $item['model'] }}
                                        </strong>

                                        <div
                                            style="
                                                font-size: 14px;
                                                color: #666666;
                                            "
                                        >
                                            {{ $item['qty'] }}
                                            x
                                            €{{ number_format($item['price'], 0, ',', '.') }}
                                        </div>

                                    </div>

                                </div>


                                {{-- SUBTOTAL --}}
                                <div
                                    style="
                                        min-width: 140px;
                                        text-align: right;
                                    "
                                >

                                    <div
                                        style="
                                            margin-bottom: 5px;
                                            font-size: 13px;
                                            color: #777777;
                                        "
                                    >
                                        Subtotaal
                                    </div>

                                    <strong
                                        style="
                                            font-size: 19px;
                                            color: #111111;
                                        "
                                    >
                                        €{{ number_format(
                                            $item['price'] * $item['qty'],
                                            0,
                                            ',',
                                            '.'
                                        ) }}
                                    </strong>

                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>


                {{-- ========================================================= --}}
                {{-- ORDER SUMMARY                                               --}}
                {{-- ========================================================= --}}

                <div
                    class="feature-card"
                    style="
                        margin-bottom: 30px;
                        border-top: 4px solid #111111;
                    "
                >

                    <h2 style="margin-top: 0;">
                        Besteloverzicht
                    </h2>


                    <div
                        style="
                            display: flex;
                            justify-content: space-between;
                            gap: 20px;
                            margin-bottom: 12px;
                        "
                    >
                        <span>
                            Verschillende auto's
                        </span>

                        <strong>
                            {{ count($cart) }}
                        </strong>
                    </div>


                    <div
                        style="
                            display: flex;
                            justify-content: space-between;
                            gap: 20px;
                            margin-bottom: 12px;
                        "
                    >
                        <span>
                            Totaal aantal
                        </span>

                        <strong>
                            {{ collect($cart)->sum('qty') }}
                        </strong>
                    </div>


                    <div
                        style="
                            margin: 20px 0;
                            border-top: 1px solid #e5e5e5;
                        "
                    ></div>


                    <div
                        style="
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            gap: 20px;
                        "
                    >
                        <strong style="font-size: 18px;">
                            Totaal
                        </strong>

                        <strong
                            style="
                                font-size: 26px;
                                color: #111111;
                            "
                        >
                            €{{ number_format($total, 0, ',', '.') }}
                        </strong>
                    </div>

                </div>


                {{-- ========================================================= --}}
                {{-- CHECKOUT FORM                                               --}}
                {{-- ========================================================= --}}

                <form
                    class="reg-form"
                    method="POST"
                    action="{{ route('checkout.submit') }}"
                >

                    @csrf


                    {{-- INFORMATION --}}
                    <div
                        style="
                            margin-bottom: 25px;
                            padding: 16px 18px;
                            border-radius: 8px;
                            background: #f5f5f5;
                        "
                    >

                        <small>
                            <strong>
                                Let op:
                            </strong>

                            door op “Bestelling plaatsen” te klikken,
                            wordt je bestelling definitief opgeslagen.
                            De bestelbevestiging wordt verzonden naar
                            <strong>{{ Auth::user()->email }}</strong>.
                        </small>

                    </div>


                    {{-- ACTIONS --}}
                    <div
                        style="
                            display: flex;
                            gap: 12px;
                            flex-wrap: wrap;
                        "
                    >

                        <a
                            href="{{ route('cart') }}"
                            style="
                                display: inline-block;
                                padding: 12px 18px;
                                border: 1px solid #cccccc;
                                border-radius: 8px;
                                color: #222222;
                                text-decoration: none;
                                font-weight: 600;
                            "
                        >
                            Terug naar winkelwagen
                        </a>


                        <button
                            class="primary-btn"
                            type="submit"
                        >
                            Bestelling plaatsen
                        </button>

                    </div>

                </form>

            @endif

        </div>

    </div>

</section>

@endsection
