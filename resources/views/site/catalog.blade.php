@extends('layouts.site-layout')

@section('title', 'SmartDesk | Catalog')

@section('content')
<section class="form-page">
    <div class="site-wrap">
        <div class="section-title">
            <h2>Onze Auto's</h2>
            <p>Ontdek premium auto's voor elke levensstijl en elke route.</p>
        </div>

        <div class="feature-grid">
            @foreach ($cars as $car)
                <div class="feature-card">
                    <img src="{{ $car['image'] }}" alt="{{ $car['brand'] }}" style="width:100%;height:240px;object-fit:cover;border-radius:16px;margin-bottom:16px;">
                    <div class="icon">{{ $car['year'] }}</div>
                    <h3>{{ $car['brand'] }} {{ $car['model'] }}</h3>
                    <p>{{ $car['type'] }} · {{ $car['fuel'] }}</p>
                    <p>{{ $car['summary'] }}</p>
                    <p><strong>€{{ number_format($car['price'], 0, ',', '.') }}</strong></p>
                    <div class="hero-actions">
                        <a class="primary-btn" href="{{ route('car', ['id' => $car['id']]) }}">Bekijk</a>
                        <form method="POST" action="{{ route('cart.add', ['id' => $car['id']]) }}">
                            @csrf
                            <button class="secondary-btn" type="submit">Kopen</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
