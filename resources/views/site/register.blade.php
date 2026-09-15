@extends('layouts.site-layout')

@section('title', 'SmartDesk | Registreren')

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
                Maak een account aan
            </h1>

            <p class="form-sub">
                Welkom bij SmartDesk.
                Vul je gegevens in, bevestig je e-mailadres
                en beheer daarna je account en bestellingen.
            </p>

        </div>


        {{-- ========================================================= --}}
        {{-- SUCCESS MESSAGE                                            --}}
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
        {{-- GENERAL ERROR MESSAGE                                      --}}
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
        {{-- REGISTER FORM                                              --}}
        {{-- ========================================================= --}}

        <form
            class="reg-form"
            method="POST"
            action="{{ route('register.submit') }}"
        >

            @csrf


            {{-- NAME + EMAIL --}}

            <div class="form-row">

                <div>

                    <label for="name">
                        Naam
                    </label>

                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="Jouw naam"
                        required
                        autofocus
                        autocomplete="name"
                    >

                    @error('name')

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


                <div>

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

            </div>


            {{-- PASSWORD + CONFIRMATION --}}

            <div class="form-row">

                <div>

                    <label for="password">
                        Wachtwoord
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


                <div>

                    <label for="password_confirmation">
                        Bevestig wachtwoord
                    </label>

                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        placeholder="Herhaal je wachtwoord"
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

            </div>


            {{-- ========================================================= --}}
            {{-- VERIFICATION INFO                                          --}}
            {{-- ========================================================= --}}

            <div
                style="
                    margin-top: 20px;
                    margin-bottom: 25px;
                    padding: 16px 18px;
                    border-radius: 8px;
                    background: #f5f5f5;
                "
            >

                <small>
                    <strong>
                        E-mailverificatie:
                    </strong>

                    na het aanmaken van je account sturen we een
                    verificatiecode naar je e-mailadres.
                    Deze code is 15 minuten geldig.
                </small>

            </div>


            {{-- ========================================================= --}}
            {{-- SECURITY INFO                                              --}}
            {{-- ========================================================= --}}

            <div
                style="
                    margin-bottom: 25px;
                    padding: 16px 18px;
                    border-radius: 8px;
                    background: #eef5ff;
                "
            >

                <small>
                    <strong>
                        Veiligheid:
                    </strong>

                    gebruik een uniek wachtwoord dat je niet op andere
                    websites gebruikt. SmartDesk zal je wachtwoord nooit
                    per e-mail opvragen.
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
                    Account aanmaken
                </button>

                <a
                    class="secondary-btn"
                    href="{{ route('login') }}"
                >
                    Ik heb al een account
                </a>

            </div>

        </form>


        {{-- ========================================================= --}}
        {{-- LOGIN INFO                                                 --}}
        {{-- ========================================================= --}}

        <div
            style="
                margin-top: 25px;
                text-align: center;
            "
        >

            <p style="margin: 0;">

                Heb je al een SmartDesk-account?

                <a
                    href="{{ route('login') }}"
                    style="
                        font-weight: 700;
                        color: inherit;
                    "
                >
                    Log hier in
                </a>

            </p>

        </div>

    </div>

</div>
```

</section>

@endsection
