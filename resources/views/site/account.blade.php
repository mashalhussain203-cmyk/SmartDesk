@extends('layouts.site-layout')

@section('title', 'SmartDesk | Mijn account')

@section('content')
<section class="form-page">
    <div class="form-wrapper">

        {{-- HEADER --}}
        <div style="margin-bottom: 30px;">
            <h1 class="form-title">Mijn account</h1>
            <p class="form-sub">
                Welkom terug, {{ $user->name }}. Beheer hier je persoonlijke gegevens,
                beveiliging en bestellingen.
            </p>
        </div>

        {{-- SUCCESS MESSAGE --}}
        @if (session('success'))
            <div class="success" style="margin-bottom: 25px;">
                <strong>Gelukt!</strong><br>
                {{ session('success') }}
            </div>
        @endif

        {{-- ERROR MESSAGE --}}
        @if ($errors->any())
            <div class="error" style="margin-bottom: 25px;">
                <strong>Er ging iets mis.</strong>

                <ul style="margin: 10px 0 0 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        {{-- ACCOUNT STATUS --}}
        <div class="feature-card" style="margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap;">

                <div>
                    <strong style="font-size: 20px;">
                        Account actief
                    </strong>

                    <p style="margin: 8px 0 0;">
                        Je SmartDesk-account is succesvol gekoppeld aan de database.
                    </p>
                </div>

                <div>
                    @if ($user->email_verified_at)
                        <span style="
                            display: inline-block;
                            padding: 8px 14px;
                            border-radius: 20px;
                            background: #e8f7ee;
                            color: #187a42;
                            font-weight: 600;
                        ">
                            ✓ E-mail geverifieerd
                        </span>
                    @else
                        <span style="
                            display: inline-block;
                            padding: 8px 14px;
                            border-radius: 20px;
                            background: #fff4df;
                            color: #9a6500;
                            font-weight: 600;
                        ">
                            ⚠ E-mail nog niet geverifieerd
                        </span>
                    @endif
                </div>

            </div>
        </div>


        {{-- PERSONAL INFORMATION --}}
        <div style="margin-bottom: 40px;">

            <h2 style="margin-bottom: 8px;">
                Persoonlijke gegevens
            </h2>

            <p class="form-sub" style="margin-bottom: 25px;">
                Pas hieronder je naam of e-mailadres aan.
            </p>

            <form method="POST" action="{{ route('account.update') }}">
                @csrf
                @method('PUT')

                <div class="form-row">

                    <div>
                        <label for="name">Naam</label>

                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            required
                            autocomplete="name"
                        >

                        @error('name')
                            <small style="color: #c62828;">
                                {{ $message }}
                            </small>
                        @enderror
                    </div>

                    <div>
                        <label for="email">E-mailadres</label>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            required
                            autocomplete="email"
                        >

                        @error('email')
                            <small style="color: #c62828;">
                                {{ $message }}
                            </small>
                        @enderror
                    </div>

                </div>

                <div style="
                    margin-top: 15px;
                    padding: 15px;
                    border-radius: 8px;
                    background: #f5f5f5;
                ">
                    <small>
                        <strong>Let op:</strong>
                        Wanneer je je e-mailadres wijzigt, wordt je nieuwe e-mailadres
                        opnieuw geverifieerd. Je ontvangt hiervoor een nieuwe verificatiecode.
                    </small>
                </div>

                <button
                    type="submit"
                    class="primary-btn"
                    style="margin-top: 20px;"
                >
                    Gegevens opslaan
                </button>
            </form>

        </div>


        {{-- EMAIL VERIFICATION --}}
        @if (! $user->email_verified_at)

            <div class="feature-card" style="margin-bottom: 40px;">

                <h2 style="margin-top: 0;">
                    E-mailadres verifiëren
                </h2>

                <p>
                    Je e-mailadres is nog niet geverifieerd.
                    Verifieer je e-mailadres om alle functies van SmartDesk te kunnen gebruiken,
                    waaronder het plaatsen van bestellingen.
                </p>

                <a
                    class="primary-btn"
                    href="{{ route('verification.notice') }}"
                    style="display: inline-block; margin-top: 10px;"
                >
                    E-mailadres verifiëren
                </a>

            </div>

        @else

            <div class="feature-card" style="
                margin-bottom: 40px;
                border-left: 4px solid #187a42;
            ">

                <h2 style="margin-top: 0;">
                    E-mailadres bevestigd
                </h2>

                <p>
                    Je e-mailadres is geverifieerd op
                    <strong>
                        {{ optional($user->email_verified_at)->format('d-m-Y H:i') }}
                    </strong>.
                </p>

            </div>

        @endif


        {{-- PASSWORD --}}
        <div style="margin-bottom: 45px;">

            <h2 style="margin-bottom: 8px;">
                Wachtwoord wijzigen
            </h2>

            <p class="form-sub" style="margin-bottom: 25px;">
                Gebruik een sterk wachtwoord dat je niet voor andere websites gebruikt.
            </p>

            <form method="POST" action="{{ route('account.password') }}">
                @csrf
                @method('PUT')

                <div style="margin-bottom: 20px;">

                    <label for="current_password">
                        Huidig wachtwoord
                    </label>

                    <input
                        id="current_password"
                        type="password"
                        name="current_password"
                        required
                        autocomplete="current-password"
                    >

                    @error('current_password')
                        <small style="color: #c62828;">
                            {{ $message }}
                        </small>
                    @enderror

                </div>


                <div class="form-row">

                    <div>

                        <label for="password">
                            Nieuw wachtwoord
                        </label>

                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            minlength="8"
                            autocomplete="new-password"
                        >

                        <small style="display: block; margin-top: 6px;">
                            Minimaal 8 tekens.
                        </small>

                        @error('password')
                            <small style="color: #c62828;">
                                {{ $message }}
                            </small>
                        @enderror

                    </div>


                    <div>

                        <label for="password_confirmation">
                            Nieuw wachtwoord bevestigen
                        </label>

                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            minlength="8"
                            autocomplete="new-password"
                        >

                    </div>

                </div>


                <button
                    type="submit"
                    class="primary-btn"
                    style="margin-top: 20px;"
                >
                    Wachtwoord wijzigen
                </button>

            </form>

        </div>


        {{-- ACCOUNT INFORMATION --}}
        <div class="feature-card" style="margin-bottom: 45px;">

            <h2 style="margin-top: 0;">
                Accountinformatie
            </h2>

            <div class="form-row">

                <div>
                    <label>Naam</label>
                    <p>
                        {{ $user->name }}
                    </p>
                </div>

                <div>
                    <label>E-mailadres</label>
                    <p>
                        {{ $user->email }}
                    </p>
                </div>

            </div>


            <div class="form-row">

                <div>
                    <label>E-mailstatus</label>

                    <p>
                        @if ($user->email_verified_at)
                            Geverifieerd
                        @else
                            Nog niet geverifieerd
                        @endif
                    </p>
                </div>

                <div>
                    <label>Account aangemaakt</label>

                    <p>
                        {{ optional($user->created_at)->format('d-m-Y H:i') }}
                    </p>
                </div>

            </div>

        </div>


        {{-- ORDERS --}}
        <div>

            <h2 style="margin-bottom: 8px;">
                Mijn bestellingen
            </h2>

            <p class="form-sub" style="margin-bottom: 25px;">
                Bekijk hier je recente SmartDesk-bestellingen en de actuele status.
            </p>


            @forelse ($orders as $order)

                <div
                    class="feature-card"
                    style="margin-bottom: 15px;"
                >

                    <div style="
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        gap: 20px;
                        flex-wrap: wrap;
                    ">

                        <div>

                            <strong style="font-size: 18px;">
                                Bestelling {{ $order->order_number }}
                            </strong>

                            <p style="margin: 8px 0 4px;">
                                Totaal:
                                <strong>
                                    €{{ number_format($order->total, 0, ',', '.') }}
                                </strong>
                            </p>

                            <small>
                                Geplaatst op:
                                {{ optional($order->created_at)->format('d-m-Y H:i') }}
                            </small>

                        </div>


                        <div>

                            @php
                                $statusLabels = [
                                    'placed' => 'Geplaatst',
                                    'paid' => 'Betaald',
                                    'processing' => 'In behandeling',
                                    'shipped' => 'Verzonden',
                                    'completed' => 'Voltooid',
                                    'cancelled' => 'Geannuleerd',
                                ];

                                $statusLabel = $statusLabels[$order->status] ?? ucfirst($order->status);
                            @endphp

                            <span style="
                                display: inline-block;
                                padding: 8px 14px;
                                border-radius: 20px;
                                background: #f1f1f1;
                                font-weight: 600;
                            ">
                                {{ $statusLabel }}
                            </span>

                        </div>

                    </div>

                </div>

            @empty

                <div class="feature-card">

                    <strong>
                        Nog geen bestellingen
                    </strong>

                    <p style="margin-bottom: 0;">
                        Je hebt nog geen bestelling geplaatst.
                        Bekijk onze catalogus wanneer je klaar bent om je eerste auto te bekijken.
                    </p>

                </div>

            @endforelse

        </div>

    </div>
</section>
@endsection
