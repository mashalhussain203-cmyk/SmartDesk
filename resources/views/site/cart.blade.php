@extends('layouts.site-layout')

@section('title', 'SmartDesk | Winkelwagen')

@section('content')

<section class="form-page">

    <div class="site-wrap">

        <div class="form-wrapper">

            {{-- ========================================================= --}}
            {{-- HEADER                                                     --}}
            {{-- ========================================================= --}}

            <div style="margin-bottom: 30px;">

                <h1 class="form-title">
                    Winkelwagen
                </h1>

                <p class="form-sub">
                    Bekijk je geselecteerde auto's en controleer je bestelling
                    voordat je doorgaat naar de checkout.
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
                        Je hebt nog geen auto's aan je winkelwagen toegevoegd.
                        Bekijk de catalogus en kies een auto die bij je past.
                    </p>

                    <a
                        class="primary-btn"
                        href="{{ route('catalog') }}"
                    >
                        Bekijk auto's
                    </a>

                </div>

            @else

                {{-- ========================================================= --}}
                {{-- CART ITEMS                                                  --}}
                {{-- ========================================================= --}}

                <div style="margin-bottom: 30px;">

                    <h2 style="margin-bottom: 8px;">
                        Jouw selectie
                    </h2>

                    <p
                        class="form-sub"
                        style="margin-bottom: 25px;"
                    >
                        Je hebt {{ count($cart) }}
                        {{ count($cart) === 1 ? 'auto' : "auto's" }}
                        in je winkelwagen.
                    </p>


                    @foreach ($cart as $item)

                        <div
                            class="feature-card"
                            style="margin-bottom: 16px;"
                        >

                            <div
                                style="
                                    display: flex;
                                    align-items: center;
                                    justify-content: space-between;
                                    gap: 25px;
                                    flex-wrap: wrap;
                                "
                            >

                                {{-- VEHICLE --}}
                                <div
                                    style="
                                        display: flex;
                                        align-items: center;
                                        gap: 18px;
                                        flex: 1;
                                        min-width: 260px;
                                    "
                                >

                                    <img
                                        src="{{ $item['image'] }}"
                                        alt="{{ $item['brand'] }} {{ $item['model'] }}"
                                        style="
                                            width: 140px;
                                            height: 95px;
                                            object-fit: cover;
                                            border-radius: 12px;
                                            display: block;
                                        "
                                    >

                                    <div>

                                        <strong
                                            style="
                                                display: block;
                                                margin-bottom: 8px;
                                                font-size: 18px;
                                            "
                                        >
                                            {{ $item['brand'] }}
                                            {{ $item['model'] }}
                                        </strong>

                                        <div
                                            style="
                                                margin-bottom: 5px;
                                                font-size: 14px;
                                                color: #666666;
                                            "
                                        >
                                            Aantal:
                                            <strong style="color: #222222;">
                                                {{ $item['qty'] }}
                                            </strong>
                                        </div>

                                        <div
                                            style="
                                                font-size: 14px;
                                                color: #666666;
                                            "
                                        >
                                            Prijs per stuk:
                                            <strong style="color: #222222;">
                                                €{{ number_format($item['price'], 0, ',', '.') }}
                                            </strong>
                                        </div>

                                    </div>

                                </div>


                                {{-- ITEM TOTAL --}}
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
                                            font-size: 20px;
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
                        margin-bottom: 25px;
                        border-top: 4px solid #111111;
                    "
                >

                    <h2 style="margin-top: 0;">
                        Overzicht
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
                            Aantal verschillende auto's
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
                                font-size: 24px;
                                color: #111111;
                            "
                        >
                            €{{ number_format($total, 0, ',', '.') }}
                        </strong>
                    </div>

                </div>


                {{-- ========================================================= --}}
                {{-- CHECKOUT INFORMATION                                        --}}
                {{-- ========================================================= --}}

                <div
                    style="
                        margin-bottom: 25px;
                        padding: 16px 18px;
                        background: #f5f5f5;
                        border-radius: 8px;
                    "
                >

                    @auth

                        @if (auth()->user()->email_verified_at)

                            <small>
                                <strong>
                                    Klaar om verder te gaan?
                                </strong>

                                Je bent ingelogd en je e-mailadres is geverifieerd.
                                Je kunt doorgaan naar de checkout.
                            </small>

                        @else

                            <small>
                                <strong>
                                    Let op:
                                </strong>

                                je moet eerst je e-mailadres verifiëren voordat
                                je een bestelling kunt plaatsen.
                            </small>

                        @endif

                    @else

                        <small>
                            <strong>
                                Let op:
                            </strong>

                            je moet ingelogd zijn voordat je kunt doorgaan
                            naar de checkout.
                        </small>

                    @endauth

                </div>


                {{-- ========================================================= --}}
                {{-- ACTIONS                                                     --}}
                {{-- ========================================================= --}}

                <div
                    style="
                        display: flex;
                        gap: 12px;
                        flex-wrap: wrap;
                    "
                >

                    <a
                        href="{{ route('catalog') }}"
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
                        Verder winkelen
                    </a>


                    @auth

                        @if (auth()->user()->email_verified_at)

                            <a
                                class="primary-btn"
                                href="{{ route('checkout') }}"
                            >
                                Naar checkout
                            </a>

                        @else

                            <a
                                class="primary-btn"
                                href="{{ route('verification.notice') }}"
                            >
                                Eerst e-mail verifiëren
                            </a>

                        @endif

                    @else

                        <a
                            class="primary-btn"
                            href="{{ route('login') }}"
                        >
                            Inloggen om af te rekenen
                        </a>

                    @endauth

                </div>

            @endif

        </div>

    </div>

</section>

@endsection

