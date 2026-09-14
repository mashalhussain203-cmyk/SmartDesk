@extends('layouts.site-layout')

@section('title', 'SmartDesk | Mijn account')

@section('content')
<section class="form-page">
    <div class="form-wrapper">
        <h1 class="form-title">Mijn account</h1>
        <p class="form-sub">Je bent ingelogd als {{ $user->name }}.</p>

        <div class="success">
            <strong>Account actief</strong><br>
            Je account is gekoppeld aan de database.
        </div>

        <div class="form-row">
            <div>
                <label>Naam</label>
                <p>{{ $user->name }}</p>
            </div>
            <div>
                <label>E-mailadres</label>
                <p>{{ $user->email }}</p>
            </div>
        </div>

        <div class="form-row">
            <div>
                <label>E-mailstatus</label>
                <p>{{ $user->email_verified_at ? 'Geverifieerd' : 'Nog niet geverifieerd' }}</p>
            </div>
            <div>
                <label>Account aangemaakt</label>
                <p>{{ optional($user->created_at)->format('d-m-Y H:i') }}</p>
            </div>
        </div>

        @if (! $user->email_verified_at)
            <a class="primary-btn" href="{{ route('verification.notice') }}">E-mail verifiëren</a>
        @endif

            <h2>Mijn bestellingen</h2>
            @forelse ($orders as $order)
                <div class="feature-card">
                    <strong>Bestelling {{ $order->order_number }}</strong>
                    <p>Status: {{ $order->status }} · Totaal: €{{ number_format($order->total, 0, ',', '.') }}</p>
                    <small>{{ $order->created_at }}</small>
                </div>
            @empty
                <p>Nog geen bestellingen geplaatst.</p>
            @endforelse
    </div>
</section>
@endsection
