@extends('layouts.site-layout')

@section('title', 'SmartDesk | Login')

@section('content')
<section class="form-page">
    <div class="form-wrapper">
        <h1 class="form-title">Inloggen</h1>
        <p class="form-sub">Log in op jouw SmartDesk account.</p>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        @if (session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif

        <form class="reg-form" method="POST" action="{{ route('login.submit') }}">
            @csrf
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="naam@example.com" required>

            <label for="password">Wachtwoord</label>
            <input id="password" type="password" name="password" placeholder="••••••••" required>

            <label style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" name="remember" value="1"> Onthoud mij
            </label>

            <p><a href="{{ route('password.request') }}">Wachtwoord vergeten?</a></p>

            <div class="hero-actions">
                <button class="primary-btn" type="submit">Inloggen</button>
                <a class="secondary-btn" href="{{ route('register') }}">Maak account</a>
            </div>
        </form>
    </div>
</section>
@endsection
