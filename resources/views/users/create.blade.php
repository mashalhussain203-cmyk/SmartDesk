@extends('layouts.admin-layout')

@section('title', 'Nieuwe gebruiker')

@section('content')
<section class="main-panel">
    <div class="section-heading">
        <div>
            <h2>Nieuwe gebruiker aanmaken</h2>
            <span>Gebruik onderstaande gegevens om een account op te slaan.</span>
        </div>
        <a class="button secondary" href="{{ route('admin.dashboard') }}">Terug naar dashboard</a>
    </div>

    <div class="form-wrap">
        <form method="POST" action="{{ route('users.store') }}">
            @csrf

            <div class="form-row">
                <label for="name">Naam</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Voornaam en achternaam" required>
                @error('name')
                    <span style="color: var(--danger); font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-row">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="naam@example.com" required>
                @error('email')
                    <span style="color: var(--danger); font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-row">
                <label for="password">Wachtwoord</label>
                <input type="password" id="password" name="password" placeholder="Minimaal 8 tekens" required>
                @error('password')
                    <span style="color: var(--danger); font-size: 12px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-row">
                <label for="password_confirmation">Bevestig wachtwoord</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Herhaal wachtwoord" required>
            </div>

            <div class="topbar-actions">
                <button class="button" type="submit">Gebruiker opslaan</button>
                <a class="button secondary" href="{{ route('users.index') }}">Annuleren</a>
            </div>
        </form>
    </div>
</section>
@endsection
