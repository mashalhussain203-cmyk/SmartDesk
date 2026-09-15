@extends('layouts.site-layout')

@section('title', 'SmartDesk | ' . $car['brand'] . ' ' . $car['model'])

@section('content')

<section class="form-page">

```
<div class="site-wrap">

    {{-- ========================================================= --}}
    {{-- SUCCESS MESSAGE                                            --}}
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
    {{-- ERROR MESSAGE                                              --}}
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
    {{-- PRODUCT DETAIL                                             --}}
    {{-- ========================================================= --}}

    <div class="feature-grid">

        {{-- ========================================================= --}}
        {{-- PRODUCT IMAGE                                              --}}
        {{-- ========================================================= --}}

        <div class="feature-card">

            <img
                src="{{ $car['image'] }}"
                alt="{{ $car['brand'] }} {{ $car['model'] }}"
                style="
                    width: 100%;
                    height: 420px;
                    object-fit: cover;
                    border-radius: 16px;
                    display: block;
                "
            >

        </div>


        {{-- ========================================================= --}}
        {{-- PRODUCT INFORMATION                                        --}}
        {{-- ========================================================= --}}

        <div
            class="feature-card"
            style="
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            "
        >

            <div>

                {{-- META --}}
                <span class="hero-kicker">
                    {{ $car['year'] }}
                    ·
                    {{ $car['type'] }}
                </span>


                {{-- TITLE --}}
                <h1
                    class="form-title"
                    style="margin-top: 12px;"
                >
                    {{ $car['brand'] }}
                    {{ $car['model'] }}
                </h1>


                {{-- SUMMARY --}}
                <p
                    class="form-sub"
                    style="
                        margin-bottom: 25px;
                        line-height: 1.7;
                    "
                >
                    {{ $car['summary'] }}
                </p>


                {{-- ========================================================= --}}
                {{-- VEHICLE DETAILS                                            --}}
                {{-- ========================================================= --}}

                <div
                    style="
                        margin-bottom: 25px;
                        border-top: 1px solid #eeeeee;
                        border-bottom: 1px solid #eeeeee;
                    "
                >

                    <div
                        style="
                            display: flex;
                            justify-content: space-between;
                            gap: 20px;
                            padding: 15px 0;
                            border-bottom: 1px solid #eeeeee;
                        "
                    >
                        <span style="color: #777777;">
                            Merk
                        </span>

                        <strong>
                            {{ $car['brand'] }}
                        </strong>
                    </div>


                    <div
                        style="
                            display: flex;
                            justify-content: space-between;
                            gap: 20px;
                            padding: 15px 0;
                            border-bottom: 1px solid #eeeeee;
                        "
                    >
                        <span style="color: #777777;">
                            Model
                        </span>

                        <strong>
                            {{ $car['model'] }}
                        </strong>
                    </div>


                    <div
                        style="
                            display: flex;
                            justify-content: space-between;
                            gap: 20px;
                            padding: 15px 0;
                            border-bottom: 1px solid #eeeeee;
                        "
                    >
                        <span style="color: #777777;">
                            Bouwjaar
                        </span>

                        <strong>
                            {{ $car['year'] }}
                        </strong>
                    </div>


                    <div
                        style="
                            display: flex;
                            justify-content: space-between;
                            gap: 20px;
                            padding: 15px 0;
                            border-bottom: 1px solid #eeeeee;
                        "
                    >
                        <span style="color: #777777;">
                            Type
                        </span>

                        <strong>
                            {{ $car['type'] }}
                        </strong>
                    </div>


                    <div
                        style="
                            display: flex;
                            justify-content: space-between;
                            gap: 20px;
                            padding: 15px 0;
                        "
                    >
                        <span style="color: #777777;">
                            Brandstof
                        </span>

                        <strong>
                            {{ $car['fuel'] }}
                        </strong>
                    </div>

                </div>


                {{-- ========================================================= --}}
                {{-- PRICE                                                      --}}
                {{-- ========================================================= --}}

                <div
                    style="
                        margin-bottom: 25px;
                        padding: 20px;
                        background: #f7f8fa;
                        border-radius: 10px;
                    "
                >

                    <div
                        style="
                            margin-bottom: 5px;
                            font-size: 13px;
                            color: #777777;
                        "
                    >
                        Prijs
                    </div>

                    <strong
                        style="
                            font-size: 30px;
                            color: #111111;
                        "
                    >
                        €{{ number_format($car['price'], 0, ',', '.') }}
                    </strong>

                </div>

            </div>


            {{-- ========================================================= --}}
            {{-- ACTIONS                                                    --}}
            {{-- ========================================================= --}}

            <div
                class="hero-actions"
                style="
                    display: flex;
                    gap: 12px;
                    flex-wrap: wrap;
                "
            >

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
                        Toevoegen aan winkelwagen
                    </button>

                </form>


                <a
                    class="secondary-btn"
                    href="{{ route('catalog') }}"
                >
                    Terug naar catalogus
                </a>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- EXTRA INFORMATION                                         --}}
    {{-- ========================================================= --}}

    <div
        class="feature-grid"
        style="margin-top: 30px;"
    >

        <div class="feature-card">

            <div class="icon">
                ✓
            </div>

            <h3>
                Overzichtelijk bestellen
            </h3>

            <p>
                Voeg deze auto toe aan je winkelwagen en controleer
                je bestelling voordat je doorgaat naar de checkout.
            </p>

        </div>


        <div class="feature-card">

            <div class="icon">
                🔐
            </div>

            <h3>
                Veilig account
            </h3>

            <p>
                Voor het plaatsen van een bestelling moet je ingelogd zijn
                en moet je e-mailadres geverifieerd zijn.
            </p>

        </div>


        <div class="feature-card">

            <div class="icon">
                ✉
            </div>

            <h3>
                Bestelbevestiging
            </h3>

            <p>
                Na het plaatsen van je bestelling ontvang je automatisch
                een bevestiging op het e-mailadres van je SmartDesk-account.
            </p>

        </div>

    </div>

</div>
```

</section>

@endsection
