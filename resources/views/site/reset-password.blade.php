@extends('layouts.site-layout')

@section('title', 'SmartDesk | Nieuw wachtwoord')

@section('content')

<section class="form-page">

```
<div class="site-wrap">

    <div class="form-wrapper">

        {{-- ========================================================= --}}
        {{-- HEADER                                                     --}}
        {{-- ========================================================= --}}

        <div style="margin-bottom: 30px;">

            <h1 class="form-title">
                Nieuw wachtwoord instellen
            </h1>

            <p class="form-sub">
                Vul je e-mailadres in en kies een nieuw wachtwoord
                voor je SmartDesk-account.
            </p>

        </div>


        {{-- ========================================================= --}}
        {{-- ERROR MESSAGE                                               --}}
        {{-- ========================================================= --}}

        @if ($errors->any())

            <div
                class="error"
                style="margin-bottom: 25px;"
            >

                <strong>
                    Er ging iets mis.
                </strong>

                <ul style="margin: 10px 0 0 20px;">

                    @foreach ($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        {{-- ========================================================= --}}
        {{-- RESET FORM                                                  --}}
        {{-- ========================================================= --}}

        <form
            class="reg-form"
            method="POST"
            action="{{ route('password.update', $token) }}"
        >

            @csrf


            {{-- EMAIL --}}

            <div style="margin-bottom: 20px;">

                <label for="email">
                    E-mailadres
                </label>

                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="naam@example.com"
                    required
                    autofocus
                    autocomplete="email"
                >

                @error('email')

                    <small
                        style="
                            display: block;
                            margin-top: 6px;
                            color: #c62828;
                        "
                    >
                        {{ $message }}
                    </small>

                @enderror

            </div>


            {{-- PASSWORD --}}

            <div style="margin-bottom: 20px;">

                <label for="password">
                    Nieuw wachtwoord
                </label>

                <input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="Minimaal 8 tekens"
                    required
                    minlength="8"
                    autocomplete="new-password"
                >

                <small
                    style="
                        display: block;
                        margin-top: 6px;
                    "
                >
                    Gebruik minimaal 8 tekens.
                </small>

                @error('password')

                    <small
                        style="
                            display: block;
                            margin-top: 6px;
                            color: #c62828;
                        "
                    >
                        {{ $message }}
                    </small>

                @enderror

            </div>


            {{-- PASSWORD CONFIRMATION --}}

            <div style="margin-bottom: 25px;">

                <label for="password_confirmation">
                    Nieuw wachtwoord bevestigen
                </label>

                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    placeholder="Herhaal je nieuwe wachtwoord"
                    required
                    minlength="8"
                    autocomplete="new-password"
                >

                @error('password_confirmation')

                    <small
                        style="
                            display: block;
                            margin-top: 6px;
                            color: #c62828;
                        "
                    >
                        {{ $message }}
                    </small>

                @enderror

            </div>


            {{-- ========================================================= --}}
            {{-- SECURITY INFO                                              --}}
            {{-- ========================================================= --}}

            <div
                style="
                    margin-bottom: 25px;
                    padding: 16px 18px;
                    border-radius: 8px;
                    background: #f5f5f5;
                "
            >

                <small>
                    <strong>
                        Veilig wachtwoord:
                    </strong>

                    gebruik bij voorkeur een uniek wachtwoord dat je
                    niet voor andere websites of diensten gebruikt.
                </small>

            </div>


            {{-- ========================================================= --}}
            {{-- RESET LINK INFO                                            --}}
            {{-- ========================================================= --}}

            <div
                style="
                    margin-bottom: 25px;
                    padding: 16px 18px;
                    border-radius: 8px;
                    background: #fff8e6;
                "
            >

                <small>
                    <strong>
                        Let op:
                    </strong>

                    deze wachtwoordresetlink is 60 minuten geldig.
                    Als de link is verlopen, moet je een nieuwe resetlink aanvragen.
                </small>

            </div>


            {{-- ========================================================= --}}
            {{-- ACTIONS                                                    --}}
            {{-- ========================================================= --}}

            <div
                class="hero-actions"
                style="
                    display: flex;
                    gap: 12px;
                    flex-wrap: wrap;
                "
            >

                <button
                    class="primary-btn"
                    type="submit"
                >
                    Wachtwoord opslaan
                </button>

                <a
                    class="secondary-btn"
                    href="{{ route('login') }}"
                >
                    Terug naar inloggen
                </a>

            </div>

        </form>


        {{-- ========================================================= --}}
        {{-- EXTRA SECURITY INFO                                        --}}
        {{-- ========================================================= --}}

        <div
            class="feature-card"
            style="
                margin-top: 30px;
                border-left: 4px solid #555555;
            "
        >

            <h2
                style="
                    margin-top: 0;
                    margin-bottom: 10px;
                    font-size: 18px;
                "
            >
                Accountbeveiliging
            </h2>

            <p style="margin-bottom: 0;">
                Nadat je nieuwe wachtwoord is opgeslagen,
                kan je oude wachtwoord niet meer worden gebruikt.
                Je ontvangt hiervan ook een beveiligingsmail van SmartDesk.
            </p>

        </div>

    </div>

</div>
```

</section>

@endsection
