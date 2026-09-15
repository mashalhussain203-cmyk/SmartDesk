@extends('layouts.site-layout')

@section('title', 'SmartDesk | Inloggen')

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
                Inloggen
            </h1>

            <p class="form-sub">
                Log in op je SmartDesk-account om je gegevens,
                bestellingen en winkelwagen te beheren.
            </p>

        </div>


        {{-- ========================================================= --}}
        {{-- SUCCESS MESSAGE                                             --}}
        {{-- ========================================================= --}}

        @if (session('success'))

            <div
                class="success"
                style="margin-bottom: 25px;"
            >
                <strong>
                    Gelukt!
                </strong>

                <br>

                {{ session('success') }}
            </div>

        @endif


        {{-- ========================================================= --}}
        {{-- ERROR MESSAGE                                               --}}
        {{-- ========================================================= --}}

        @if ($errors->any())

            <div
                class="error"
                style="margin-bottom: 25px;"
            >

                <strong>
                    Inloggen is niet gelukt.
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
        {{-- LOGIN FORM                                                  --}}
        {{-- ========================================================= --}}

        <form
            class="reg-form"
            method="POST"
            action="{{ route('login.submit') }}"
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
                    Wachtwoord
                </label>

                <input
                    id="password"
                    type="password"
                    name="password"
                    placeholder="••••••••"
                    required
                    autocomplete="current-password"
                >

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


            {{-- REMEMBER + FORGOT PASSWORD --}}

            <div
                style="
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    gap: 15px;
                    flex-wrap: wrap;
                    margin-bottom: 25px;
                "
            >

                <label
                    for="remember"
                    style="
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        margin: 0;
                        cursor: pointer;
                    "
                >

                    <input
                        id="remember"
                        type="checkbox"
                        name="remember"
                        value="1"
                        {{ old('remember') ? 'checked' : '' }}
                        style="
                            width: auto;
                            margin: 0;
                        "
                    >

                    <span>
                        Onthoud mij
                    </span>

                </label>


                <a
                    href="{{ route('password.request') }}"
                    style="
                        font-weight: 600;
                        color: inherit;
                    "
                >
                    Wachtwoord vergeten?
                </a>

            </div>


            {{-- ACTIONS --}}

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
                    Inloggen
                </button>

                <a
                    class="secondary-btn"
                    href="{{ route('register') }}"
                >
                    Account aanmaken
                </a>

            </div>

        </form>


        {{-- ========================================================= --}}
        {{-- EXTRA INFORMATION                                          --}}
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
                Veilig inloggen
            </h2>

            <p style="margin-bottom: 0;">
                Controleer altijd of je je wachtwoord alleen op SmartDesk invoert.
                Deel je wachtwoord nooit met anderen.
            </p>

        </div>


        {{-- ========================================================= --}}
        {{-- REGISTER INFO                                               --}}
        {{-- ========================================================= --}}

        <div
            style="
                margin-top: 25px;
                text-align: center;
            "
        >

            <p style="margin: 0;">

                Nog geen SmartDesk-account?

                <a
                    href="{{ route('register') }}"
                    style="
                        font-weight: 700;
                        color: inherit;
                    "
                >
                    Maak er één aan
                </a>

            </p>

        </div>

    </div>

</div>
```

</section>

@endsection
