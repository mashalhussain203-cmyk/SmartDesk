@extends('layouts.site-layout')

@section('title', 'SmartDesk | Catalogus')

@section('content')

<section class="form-page">

    <div class="site-wrap">

        {{-- ========================================================= --}}
        {{-- HEADER                                                     --}}
        {{-- ========================================================= --}}

        <div class="section-title">

            <h2>
                Onze auto's
            </h2>

            <p>
                Ontdek premium auto's voor elke levensstijl,
                elke route en ieder moment.
            </p>

        </div>


        {{-- ========================================================= --}}
        {{-- CATALOG                                                    --}}
        {{-- ========================================================= --}}

        @if (empty($cars))

            <div
                class="feature-card"
                style="
                    text-align: center;
                    padding: 40px 25px;
                "
            >

                <h3 style="margin-top: 0;">
                    Geen auto's beschikbaar
                </h3>

                <p style="margin-bottom: 0;">
                    Er zijn momenteel geen auto's beschikbaar in de catalogus.
                </p>

            </div>

        @else

            <div class="feature-grid">

                @foreach ($cars as $car)

                    <div
                        class="feature-card"
                        style="
                            display: flex;
                            flex-direction: column;
                            height: 100%;
                        "
                    >

                        {{-- CAR IMAGE --}}

                        <a
                            href="{{ route('car', ['id' => $car['id']]) }}"
                            style="
                                display: block;
                                text-decoration: none;
                                color: inherit;
                            "
                        >

                            <img
                                src="{{ $car['image'] }}"
                                alt="{{ $car['brand'] }} {{ $car['model'] }}"
                                loading="lazy"
                                style="
                                    width: 100%;
                                    height: 240px;
                                    object-fit: cover;
                                    border-radius: 16px;
                                    margin-bottom: 16px;
                                    display: block;
                                "
                            >

                        </a>


                        {{-- YEAR --}}

                        <div
                            class="icon"
                            style="
                                margin-bottom: 12px;
                            "
                        >
                            {{ $car['year'] }}
                        </div>


                        {{-- CAR NAME --}}

                        <h3
                            style="
                                margin: 0 0 10px;
                                font-size: 22px;
                            "
                        >
                            {{ $car['brand'] }}
                            {{ $car['model'] }}
                        </h3>


                        {{-- CAR TYPE / FUEL --}}

                        <p
                            style="
                                margin: 0 0 12px;
                                color: #666666;
                            "
                        >
                            {{ $car['type'] }}
                            ·
                            {{ $car['fuel'] }}
                        </p>


                        {{-- SUMMARY --}}

                        <p
                            style="
                                margin: 0 0 18px;
                                line-height: 1.6;
                            "
                        >
                            {{ $car['summary'] }}
                        </p>


                        {{-- PRICE --}}

                        <div
                            style="
                                margin-top: auto;
                                margin-bottom: 18px;
                            "
                        >

                            <div
                                style="
                                    font-size: 13px;
                                    color: #777777;
                                    margin-bottom: 4px;
                                "
                            >
                                Prijs
                            </div>

                            <strong
                                style="
                                    font-size: 24px;
                                    color: #111111;
                                "
                            >
                                €{{ number_format($car['price'], 0, ',', '.') }}
                            </strong>

                        </div>


                        {{-- ACTIONS --}}

                        <div
                            class="hero-actions"
                            style="
                                display: flex;
                                gap: 10px;
                                flex-wrap: wrap;
                            "
                        >

                            <a
                                class="primary-btn"
                                href="{{ route('car', ['id' => $car['id']]) }}"
                            >
                                Bekijk auto
                            </a>


                            <form
                                method="POST"
                                action="{{ route('cart.add', ['id' => $car['id']]) }}"
                                style="margin: 0;"
                            >

                                @csrf

                                <button
                                    class="secondary-btn"
                                    type="submit"
                                >
                                    Toevoegen aan winkelwagen
                                </button>

                            </form>

                        </div>

                    </div>

                @endforeach

            </div>

        @endif

    </div>

</section>

@endsection

