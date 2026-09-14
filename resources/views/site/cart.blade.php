@extends('layouts.site-layout')

@section('title', 'SmartDesk | Winkelwagen')

@section('content')
<section class="form-page">
    <div class="site-wrap">
        <div class="form-wrapper">
            <h1 class="form-title">Winkelwagen</h1>

            @if (session('success'))
                <div class="success">{{ session('success') }}</div>
            @endif

            @if (empty($cart))
                <p class="form-sub">Je winkelwagen is leeg.</p>
                <a class="primary-btn" href="{{ route('catalog') }}">Bekijk auto's</a>
            @else
                @foreach ($cart as $item)
                    <div class="feature-card" style="margin-bottom:14px;">
                        <div class="hero-actions">
                            <img src="{{ $item['image'] }}" alt="" style="width:120px;height:80px;object-fit:cover;border-radius:12px;">
                            <div>
                                <strong>{{ $item['brand'] }} {{ $item['model'] }}</strong>
                                <p>{{ $item['qty'] }} x €{{ number_format($item['price'], 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="feature-card">
                    <p><strong>Totaal:</strong> €{{ number_format($total, 0, ',', '.') }}</p>
                    <a class="primary-btn" href="{{ route('checkout') }}">Checkout</a>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
