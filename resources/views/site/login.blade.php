@extends('layouts.site-layout')

@section('title', 'SmartDesk | Inloggen')

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
                rgba(5, 16, 24, 0.38),
                rgba(5, 16, 24, 0.65)
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
                circle at 20% 30%,
                rgba(52, 211, 153, 0.13),
                transparent 35%
            ),
            radial-gradient(
                circle at 80% 70%,
                rgba(14, 77, 58, 0.15),
                transparent 40%
            );

        pointer-events: none;
    }

    .auth-card {
        position: relative;
        z-index: 2;

        width: 100%;
        max-width: 460px;

        padding: 42px 38px;

        background: rgba(8, 24, 35, 0.48);

        border: 1px solid rgba(255, 255, 255, 0.22);

        border-radius: 18px;

        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);

        box-shadow:
            0 30px 80px rgba(0, 0, 0, 0.38),
            inset 0 1px 0 rgba(255, 255, 255, 0.08);

        color: #ffffff;

        animation: authCardIn .8s ease both;
    }

    @keyframes authCardIn {
        from {
            opacity: 0;
            transform: translateY(35px) scale(.97);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .auth-title {
        margin: 0 0 10px;

        text-align: center;

        font-size: 38px;
        line-height: 1.1;

        font-weight: 800;

        letter-spacing: -1px;
    }

    .auth-subtitle {
        margin: 0 0 32px;

        text-align: center;

        color: rgba(255,255,255,.72);

        font-size: 14px;
        line-height: 1.6;
    }

    .auth-field {
        position: relative;

        margin-bottom: 26px;
    }

    .auth-field label {
        display: block;

        margin-bottom: 7px;

        color: rgba(255,255,255,.80);

        font-size: 13px;
        font-weight: 600;
    }

    .auth-input-wrap {
        position: relative;
    }

    .auth-field input[type="email"],
    .auth-field input[type="password"],
    .auth-field input[type="text"] {
        width: 100%;

        padding: 14px 44px 12px 0;

        color: #ffffff;

        background: transparent;

        border: 0;
        border-bottom: 2px solid rgba(255,255,255,.55);

        border-radius: 0;

        outline: none;

        font-size: 16px;

        transition:
            border-color .25s ease,
            transform .25s ease,
            background .25s ease;
    }

    .auth-field input::placeholder {
        color: rgba(255,255,255,.58);
    }

    .auth-field input:focus {
        border-bottom-color: #ffffff;
    }

    .password-toggle {
        position: absolute;

        right: 0;
        top: 50%;

        transform: translateY(-50%);

        border: 0;
        background: transparent;

        color: rgba(255,255,255,.75);

        cursor: pointer;

        padding: 6px;

        font-size: 13px;
    }

    .auth-options {
        display: flex;
        justify-content: space-between;
        align-items: center;

        gap: 16px;

        margin: 2px 0 28px;

        font-size: 14px;
    }

    .remember-label {
        display: flex;
        align-items: center;

        gap: 8px;

        margin: 0;

        cursor: pointer;

        color: rgba(255,255,255,.90);
    }

    .remember-label input {
        width: 16px;
        height: 16px;

        accent-color: #ffffff;
    }

    .auth-link {
        color: rgba(255,255,255,.90);

        text-decoration: none;

        transition:
            color .2s ease,
            opacity .2s ease;
    }

    .auth-link:hover {
        color: #ffffff;
        text-decoration: underline;
    }

    .auth-submit {
        width: 100%;

        padding: 15px 20px;

        border: 0;
        border-radius: 8px;

        background: #ffffff;

        color: #101010;

        font-weight: 800;
        font-size: 16px;

        cursor: pointer;

        box-shadow: 0 12px 30px rgba(0,0,0,.20);

        transition:
            transform .2s ease,
            box-shadow .2s ease,
            background .2s ease;
    }

    .auth-submit:hover {
        transform: translateY(-2px);

        background: #f1f7f5;

        box-shadow: 0 16px 36px rgba(0,0,0,.28);
    }

    .auth-submit:active {
        transform: translateY(0);
    }

    .auth-footer {
        margin-top: 30px;

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
        margin-bottom: 24px;

        padding: 13px 15px;

        border-radius: 10px;

        font-size: 13px;
        line-height: 1.6;

        animation: authMessageIn .4s ease both;
    }

    @keyframes authMessageIn {
        from {
            opacity: 0;
            transform: translateY(-8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .auth-message.success {
        color: #dcfce7;

        background: rgba(22, 101, 52, .32);

        border: 1px solid rgba(134, 239, 172, .35);
    }

    .auth-message.error {
        color: #fee2e2;

        background: rgba(127, 29, 29, .32);

        border: 1px solid rgba(252, 165, 165, .35);
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

    @media (max-width: 600px) {
        .auth-page {
            padding: 30px 15px;
        }

        .auth-card {
            padding: 34px 24px;

            border-radius: 15px;
        }

        .auth-title {
            font-size: 32px;
        }

        .auth-options {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>


<section class="auth-page">

    <div class="auth-card">

        <h1 class="auth-title">
            Inloggen
        </h1>

        <p class="auth-subtitle">
            Welkom terug bij SmartDesk.
            Log in om je account en bestellingen te beheren.
        </p>


        @if (session('success'))

            <div class="auth-message success">
                {{ session('success') }}
            </div>

        @endif


        @if ($errors->any())

            <div class="auth-message error">

                <strong>
                    Inloggen is niet gelukt.
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
            action="{{ route('login.submit') }}"
        >

            @csrf


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
                        autofocus
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
                        placeholder="Vul je wachtwoord in"
                        autocomplete="current-password"
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


            <div class="auth-options">

                <label
                    class="remember-label"
                    for="remember"
                >

                    <input
                        id="remember"
                        type="checkbox"
                        name="remember"
                        value="1"
                        {{ old('remember') ? 'checked' : '' }}
                    >

                    <span>
                        Onthoud mij
                    </span>

                </label>


                <a
                    class="auth-link"
                    href="{{ route('password.request') }}"
                >
                    Wachtwoord vergeten?
                </a>

            </div>


            <button
                class="auth-submit"
                type="submit"
            >
                Inloggen
            </button>

        </form>


        <div class="auth-footer">

            Nog geen account?

            <a href="{{ route('register') }}">
                Registreren
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

                const inputId =
                    button.getAttribute('data-toggle-password');

                const input =
                    document.getElementById(inputId);

                if (!input) {
                    return;
                }

                const hidden =
                    input.type === 'password';

                input.type =
                    hidden ? 'text' : 'password';

                button.textContent =
                    hidden ? 'Verbergen' : 'Tonen';
            });

        });

});
</script>

@endsection