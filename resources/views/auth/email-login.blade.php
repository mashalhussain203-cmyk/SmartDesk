@extends('layouts.site-layout')

@section('title', 'Mashal | E-mailcode verifiëren')

@push('styles')
<style>
    .email-verify-page {
        min-height: calc(100vh - 78px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        background:
            radial-gradient(
                circle at 80% 15%,
                rgba(215,164,95,.08),
                transparent 18rem
            ),
            linear-gradient(
                180deg,
                #0b0d10,
                #08090b
            );
    }

    .email-verify-card {
        width: 100%;
        max-width: 520px;
        padding: 34px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 24px;
        background:
            linear-gradient(
                145deg,
                rgba(255,255,255,.045),
                rgba(255,255,255,.018)
            );
        box-shadow:
            0 30px 80px rgba(0,0,0,.35),
            inset 0 1px 0 rgba(255,255,255,.04);
    }

    .email-verify-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 28px;
    }

    .email-verify-logo {
        width: 46px;
        height: 46px;
        display: grid;
        place-items: center;
        border-radius: 14px;
        background:
            linear-gradient(
                145deg,
                #f0ca86,
                #b67e3d
            );
        color: #15110c;
        font-size: 20px;
        font-weight: 950;
        box-shadow: 0 14px 34px rgba(215,164,95,.22);
    }

    .email-verify-brand strong {
        display: block;
        color: #ffffff;
        font-size: 18px;
    }

    .email-verify-brand span {
        display: block;
        margin-top: 4px;
        color: #6f757c;
        font-size: 8px;
        font-weight: 850;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .email-verify-kicker {
        color: #b9894d;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .email-verify-title {
        margin: 10px 0 0;
        color: #ffffff;
        font-size: 38px;
        line-height: 1;
        font-weight: 950;
        letter-spacing: -.04em;
    }

    .email-verify-subtitle {
        margin: 14px 0 28px;
        color: #7f858c;
        font-size: 13px;
        line-height: 1.8;
    }

    .email-message {
        margin-bottom: 18px;
        padding: 13px 15px;
        border-radius: 14px;
        font-size: 12px;
        line-height: 1.6;
    }

    .email-message.success {
        border: 1px solid rgba(91,214,149,.22);
        background: rgba(91,214,149,.08);
        color: #a9efc8;
    }

    .email-message.error {
        border: 1px solid rgba(241,123,123,.22);
        background: rgba(241,123,123,.08);
        color: #ffc1c1;
    }

    .email-field {
        margin-bottom: 18px;
    }

    .email-label {
        display: block;
        margin-bottom: 8px;
        color: #b9b9b6;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .email-input {
        width: 100%;
        height: 56px;
        padding: 0 16px;
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 15px;
        outline: none;
        background: rgba(255,255,255,.035);
        color: #ffffff;
        font-size: 14px;
        box-sizing: border-box;
    }

    .email-input:focus {
        border-color: rgba(215,164,95,.46);
        background: rgba(215,164,95,.035);
        box-shadow: 0 0 0 4px rgba(215,164,95,.065);
    }

    .email-code-input {
        text-align: center;
        font-size: 28px;
        font-weight: 900;
        letter-spacing: 10px;
        color: #f0c983;
    }

    .email-submit {
        width: 100%;
        min-height: 56px;
        border: 0;
        border-radius: 999px;
        background:
            linear-gradient(
                135deg,
                #f1cc8b,
                #ca914c
            );
        color: #14100b;
        font-size: 12px;
        font-weight: 950;
        cursor: pointer;
        box-shadow: 0 18px 44px rgba(215,164,95,.20);
    }

    .email-submit:hover {
        transform: translateY(-1px);
    }

    .email-back {
        margin-top: 18px;
        text-align: center;
    }

    .email-back a {
        color: #caa46d;
        text-decoration: none;
        font-size: 11px;
        font-weight: 800;
    }

    .email-note {
        margin-top: 18px;
        color: #5f656c;
        font-size: 9px;
        line-height: 1.7;
        text-align: center;
    }
</style>
@endpush

@section('content')
<section class="email-verify-page">
    <div class="email-verify-card">

        <div class="email-verify-brand">
            <div class="email-verify-logo">
                M
            </div>

            <div>
                <strong>Mashal</strong>
                <span>Automotive</span>
            </div>
        </div>

        <div class="email-verify-kicker">
            Secure access
        </div>

        <h1 class="email-verify-title">
            Vul je code in
        </h1>

        <p class="email-verify-subtitle">
            We hebben een 6-cijferige code naar je e-mailadres gestuurd.
            Vul de code hieronder in om verder te gaan.
        </p>

        @if (session('success'))
            <div class="email-message success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="email-message error">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="email-message error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('email-login.verify') }}"
        >
            @csrf

            <div class="email-field">
                <label
                    class="email-label"
                    for="email"
                >
                    E-mailadres
                </label>

                <input
                    class="email-input"
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email', $email ?? session('email_login_email')) }}"
                    placeholder="naam@example.com"
                    autocomplete="email"
                    required
                >
            </div>

            <div class="email-field">
                <label
                    class="email-label"
                    for="code"
                >
                    6-cijferige code
                </label>

                <input
                    class="email-input email-code-input"
                    id="code"
                    type="text"
                    name="code"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    maxlength="6"
                    minlength="6"
                    placeholder="000000"
                    autocomplete="one-time-code"
                    required
                    autofocus
                >
            </div>

            <button
                class="email-submit"
                type="submit"
            >
                Code controleren
            </button>
        </form>

        <div class="email-back">
            <a href="{{ route('login') }}">
                ← Terug naar inloggen
            </a>
        </div>

        <div class="email-note">
            De code is {{ $expiresInMinutes ?? 5 }} minuten geldig.
            Deel je code nooit met iemand anders.
        </div>

    </div>
</section>
@endsection