@extends('layouts.site-layout')

@section('title', 'SmartDesk | Registreren')

@section('content')
<section class="form-page">
    <div class="form-wrapper">
        <h1 class="form-title">Maak een account aan</h1>
        <p class="form-sub">Welkom bij SmartDesk. Vul je gegevens in en start jouw account.</p>

        @if (session('success'))
            <div class="success">
                {{ session('success') }}
            </div>
        @endif

        <form class="reg-form" method="POST" action="{{ route('register.submit') }}">
            @csrf
            <div class="form-row">
                <div>
                    <label for="name">Naam</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Jouw naam" required>
                    @error('name')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                <div>
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="naam@example.com" required>
                    @error('email')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div>
                    <label for="password">Wachtwoord</label>
                    <input id="password" type="password" name="password" placeholder="Minimaal 8 tekens" required>
                    @error('password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation">Bevestig wachtwoord</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Herhaal wachtwoord" required>
                </div>
            </div>

            <div class="form-row">
                <div>
                    <button class="primary-btn" type="submit">Account aanmaken</button>
                </div>
                <div>
                    <a class="secondary-btn" href="{{ route('users.index') }}">Bekijk gebruikers</a>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection
