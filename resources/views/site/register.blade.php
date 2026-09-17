@extends('layouts.site-layout')

@section('title', 'SmartDesk | Registreren')

@section('content')

<style>
    .auth-page {
        position: relative;

        min-height: calc(100vh - 70px);

        display: flex;
        align-items: center;
        justify-content: center;

        padding: 50px 20px;

        background:
            linear-gradient(
                rgba(5, 16, 24, .38),
                rgba(5, 16, 24, .68)
            ),
            url('/images/auth-bg.jpg') center / cover no-repeat fixed;

        overflow: hidden;
    }

    .auth-page::before {
        content: "";

        position: absolute;
        inset: 0;

        background:
            radial-gradient(
                circle at 15% 30%,
                rgba(52, 211, 153, .13),
                transparent 35%
            ),
            radial-gradient(
                circle at 85% 75%,
                rgba(14, 77, 58, .18),
                transparent 40%
            );

        pointer-events: none;
    }

    .auth-card {
        position: relative;
        z-index: 2;

        width: 100%;
        max-width: 520px;

        padding: 40px 38px;

        color: #ffffff;

        background: rgba(8, 24, 35, .48);

        border: 1px solid rgba(255,255,255,.22);

        border-radius: 18px;

        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);

        box-shadow:
            0 30px 80px rgba(0,0,0,.38),
            inset 0 1px 0 rgba(255,255,255,.08);

        animation: registerCardIn .8s ease both;
    }

    @keyframes registerCardIn {
        from {
            opacity: 0;
            transform:
                translateY(35px)
                scale(.97);
        }

        to {
            opacity: 1;
            transform:
                translateY(0)
                scale(1);
        }
    }

    .auth-title {
        margin: 0 0 10px;

        text-align: center;

        font-size: 36px;

        font-weight: 800;

        letter-spacing: -1px;
    }

    .auth-subtitle {
        max-width: 390px;

        margin: 0 auto 30px;

        text-align: center;

        color: rgba(255,255,255,.72);

        font-size: 14px;
        line-height: 1.6;
    }

    .auth-field {
        margin-bottom: 22px;
    }

    .auth-field label {
        display: block;

        margin-bottom: 6px;

        color: rgba(255,255,255,.82);

        font-size: 13px;

        font-weight: 600;
    }

    .auth-input-wrap {
        position: relative;
    }

    .auth-field input {
        width: 100%;

        padding: 13px 45px 11px 0;

        color: #ffffff;

        background: transparent;

        border: 0;

        border-bottom:
            2px solid rgba(255,255,255,.55);

        border-radius: 0;

        outline: none;

        font-size: 16px;

        transition: border-color .25s ease;
    }

    .auth-field input::placeholder {
        color: rgba(255,255,255,.55);
    }

    .auth-field input:focus {
        border-bottom-color: #ffffff;
    }

    .password-toggle {
        position: absolute;

        right: 0;
        top: 50%;

        transform: translateY(-50%);

        padding: 5px;

        border: 0;

        background: transparent;

        color: rgba(255,255,255,.75);

        cursor: pointer;

        font-size: 13px;
    }

    .auth-submit {
        width: 100%;

        margin-top: 8px;

        padding: 15px 20px;

        border: 0;

        border-radius: 8px;

        background: #ffffff;

        color: #101010;

        font-weight: 800;

        font-size: 16px;

        cursor: pointer;

        box-shadow:
            0 12px 30px rgba(0,0,0,.20);

        transition:
            transform .2s ease,
            background .2s ease,
            box-shadow .2s ease;
    }

    .auth-submit:hover {
        transform: translateY(-2px);

        background: #f1f7f5;

        box-shadow:
            0 16px 36px rgba(0,0,0,.28);
    }

    .auth-footer {
        margin-top: 28px;

        text-align: center;

        color: rgba(255,255,255,.78);

        font-size: 14px;
    }

    .auth-footer a {
        color: #ffffff;

        font-weight: 700;

        text-decoration: none;
    }

    .auth-footer a:hover {
        text-decoration: underline;
    }

    .auth-message {
        margin-bottom: 22px;

        padding: 13px 15px;

        border-radius: 10px;

        font-size: 13px;
        line-height: 1.6;
    }

    .auth-message.error {
        color: #fee2e2;

        background: rgba(127,29,29,.32);

        border: 1px solid rgba(252,165,165,.35);
    }

    .auth-message ul {
        margin: 8px 0 0 18px;
        padding: 0;
    }

    .field-error {
        display: block;

        margin-top: 7px;

        color: #fecaca;

        font-size: 12px;
    }

    .password-info {
        margin: -10px 0 20px;

        color: rgba(255,255,255,.62);

        font-size: 12px;
    }

    @media (max-width: 600px) {
        .auth-card {
            padding: 34px 24px;
        }

        .auth-title {
            font-size: 31px;
        }
    }
</style>


<section class="auth-page">

    <div class="auth-card">

        <h1 class="auth-title">
            Registreren
        </h1>

        <p class="auth-subtitle">
            Maak je SmartDesk-account aan
            en beheer daarna eenvoudig je auto's en bestellingen.
        </p>


        @if ($errors->any())

            <div class="auth-message error">

                <strong>
                    Registreren is niet gelukt.
                </strong>

                <ul>

                    @foreach ($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        <form
            method="POST"
            action="{{ route('register.submit') }}"
        >

            @csrf


            <div class="auth-field">

                <label for="name">
                    Naam
                </label>

                <div class="auth-input-wrap">

                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="Vul je naam in"
                        autocomplete="name"
                        required
                        autofocus
                    >

                </div>

                @error('name')
                    <small class="field-error">
                        {{ $message }}
                    </small>
                @enderror

            </div>


            <div class="auth-field">

                <label for="email">
                    E-mailadres
                </label>

                <div class="auth-input-wrap">

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="Vul je e-mailadres in"
                        autocomplete="email"
                        required
                    >

                </div>

                @error('email')
                    <small class="field-error">
                        {{ $message }}
                    </small>
                @enderror

            </div>


            <div class="auth-field">

                <label for="password">
                    Wachtwoord
                </label>

                <div class="auth-input-wrap">

                    <input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="Minimaal 8 tekens"
                        autocomplete="new-password"
                        minlength="8"
                        required
                    >

                    <button
                        class="password-toggle"
                        type="button"
                        data-toggle-password="password"
                    >
                        Tonen
                    </button>

                </div>

                @error('password')
                    <small class="field-error">
                        {{ $message }}
                    </small>
                @enderror

            </div>


            <div class="auth-field">

                <label for="password_confirmation">
                    Wachtwoord bevestigen
                </label>

                <div class="auth-input-wrap">

                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        placeholder="Herhaal je wachtwoord"
                        autocomplete="new-password"
                        minlength="8"
                        required
                    >

                    <button
                        class="password-toggle"
                        type="button"
                        data-toggle-password="password_confirmation"
                    >
                        Tonen
                    </button>

                </div>

            </div>


            <p class="password-info">
                Gebruik minimaal 8 tekens voor je wachtwoord.
            </p>


            <button
                class="auth-submit"
                type="submit"
            >
                Account aanmaken
            </button>

        </form>


        <div class="auth-footer">

            Heb je al een account?

            <a href="{{ route('login') }}">
                Inloggen
            </a>

        </div>

    </div>

</section>


<script>
document.addEventListener('DOMContentLoaded', function () {

    document
        .querySelectorAll('[data-toggle-password]')
        .forEach(function (button) {

            button.addEventListener('click', function () {

                const input =
                    document.getElementById(
                        button.dataset.togglePassword
                    );

                if (!input) {
                    return;
                }

                const show =
                    input.type === 'password';

                input.type =
                    show ? 'text' : 'password';

                button.textContent =
                    show ? 'Verbergen' : 'Tonen';

            });

        });

});
</script>

@endsection