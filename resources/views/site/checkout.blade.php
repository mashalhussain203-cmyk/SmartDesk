@extends('layouts.site-layout')

@section('title', 'SmartDesk | Checkout')

@section('content')
<section class="form-page">
    <div class="form-wrapper">
        <h1 class="form-title">Checkout</h1>
        <p class="form-sub">Je bestelling wordt geplaatst met het e-mailadres van je ingelogde account.</p>

        @if (empty($cart))
            <div class="error">Je winkelwagen is leeg.</div>
            <a class="primary-btn" href="{{ route('catalog') }}">Bekijk catalogus</a>
        @else
            <form class="reg-form" method="POST" action="{{ route('checkout.submit') }}">
                @csrf
                <label for="name">Naam</label>
                <input id="name" type="text" value="{{ Auth::user()->name }}" readonly>

                <label for="email">E-mail</label>
                <input id="email" type="email" value="{{ Auth::user()->email }}" readonly>

                <div class="feature-card">
                    <p><strong>Totaal:</strong> €{{ number_format($total, 0, ',', '.') }}</p>
                    @foreach ($cart as $item)
                        <p>{{ $item['brand'] }} {{ $item['model'] }} · {{ $item['qty'] }} x €{{ number_format($item['price'], 0, ',', '.') }}</p>
                    @endforeach
                </div>

                <button class="primary-btn" type="submit">Bestelling plaatsen</button>
            </form>
        @endif
    </div>
</section>
@endsection
