@extends('layouts.site-layout')

@section('title', 'SmartDesk | ' . $car['brand'] . ' ' . $car['model'])

@section('content')
<section class="form-page">
    <div class="site-wrap">
        <div class="feature-grid">
            <div class="feature-card">
                <img src="{{ $car['image'] }}" alt="{{ $car['brand'] }}" style="width:100%;height:420px;object-fit:cover;border-radius:16px;">
            </div>
            <div class="feature-card">
                <span class="hero-kicker">{{ $car['year'] }} · {{ $car['type'] }}</span>
                <h1 class="form-title">{{ $car['brand'] }} {{ $car['model'] }}</h1>
                <p class="form-sub">{{ $car['summary'] }}</p>
                <p><strong>Brandstof:</strong> {{ $car['fuel'] }}</p>
                <p><strong>Prijs:</strong> €{{ number_format($car['price'], 0, ',', '.') }}</p>
                <div class="hero-actions">
                    <form method="POST" action="{{ route('cart.add', ['id' => $car['id']]) }}">
                        @csrf
                        <button class="primary-btn" type="submit">Kopen</button>
                    </form>
                    <a class="secondary-btn" href="{{ route('catalog') }}">Terug naar catalogus</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
