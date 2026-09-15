@extends('layouts.site-layout')

@section('title', 'SmartDesk | E-mail verifiëren')

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
                E-mailadres verifiëren
            </h1>

            <p class="form-sub">
                Vraag een verificatiecode aan en gebruik deze om
                je e-mailadres voor SmartDesk te bevestigen.
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
        {{-- ERROR MESSAGE                                              --}}
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
        {{-- STEP 1: SEND CODE                                          --}}
        {{-- ========================================================= --}}

        <div
            class="feature-card"
            style="margin-bottom: 30px;"
        >

            <div style="margin-bottom: 20px;">

                <div
                    class="icon"
                    style="margin-bottom: 12px;"
                >
                    01
                </div>

                <h2 style="margin: 0 0 8px;">
                    Verificatiecode aanvragen
                </h2>

                <p
                    class="form-sub"
                    style="margin-bottom: 0;"
                >
                    Vul je e-mailadres in.
                    We sturen daarna een nieuwe verificatiecode naar je inbox.
                </p>

            </div>


            <form
                class="reg-form"
                method="POST"
                action="{{ route('verification.send') }}"
            >

                @csrf


                <div style="margin-bottom: 20px;">

                    <label for="verification_email_send">
                        E-mailadres
                    </label>

                    <input
                        id="verification_email_send"
                        type="email"
                        name="email"
                        value="{{ old('email', auth()->check() ? auth()->user()->email : '') }}"
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


                <button
                    class="primary-btn"
                    type="submit"
                >
                    Verstuur verificatiecode
                </button>

            </form>

        </div>


        {{-- ========================================================= --}}
        {{-- STEP 2: VERIFY CODE                                        --}}
        {{-- ========================================================= --}}

        <div
            class="feature-card"
            style="margin-bottom: 30px;"
        >

            <div style="margin-bottom: 20px;">

                <div
                    class="icon"
                    style="margin-bottom: 12px;"
                >
                    02
                </div>

                <h2 style="margin: 0 0 8px;">
                    Code controleren
                </h2>

                <p
                    class="form-sub"
                    style="margin-bottom: 0;"
                >
                    Vul hetzelfde e-mailadres en de ontvangen
                    6-cijferige verificatiecode in.
                </p>

            </div>


            <form
                class="reg-form"
                method="POST"
                action="{{ route('verification.verify') }}"
            >

                @csrf


                {{-- EMAIL --}}

                <div style="margin-bottom: 20px;">

                    <label for="verification_email_verify">
                        E-mailadres
                    </label>

                    <input
                        id="verification_email_verify"
                        type="email"
                        name="email"
                        value="{{ old('email', auth()->check() ? auth()->user()->email : '') }}"
                        placeholder="naam@example.com"
                        required
                        autocomplete="email"
                    >

                </div>


                {{-- CODE --}}

                <div style="margin-bottom: 25px;">

                    <label for="code">
                        Verificatiecode
                    </label>

                    <input
                        id="code"
                        type="text"
                        name="code"
                        value="{{ old('code') }}"
                        placeholder="123456"
                        required
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        maxlength="6"
                        autocomplete="one-time-code"
                        style="
                            font-size: 22px;
                            letter-spacing: 6px;
                            text-align: center;
                        "
                    >

                    <small
                        style="
                            display: block;
                            margin-top: 6px;
                        "
                    >
                        Vul de 6 cijfers uit de e-mail in.
                    </small>

                    @error('code')

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


                <button
                    class="primary-btn"
                    type="submit"
                >
                    Verificatiecode controleren
                </button>

            </form>

        </div>


        {{-- ========================================================= --}}
        {{-- INFORMATION                                                --}}
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

                je verificatiecode is 15 minuten geldig.
                Is de code verlopen? Vraag hierboven een nieuwe code aan.
            </small>

        </div>


        {{-- ========================================================= --}}
        {{-- SECURITY                                                   --}}
        {{-- ========================================================= --}}

        <div
            class="feature-card"
            style="
                margin-bottom: 30px;
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
                Beveiliging
            </h2>

            <p style="margin-bottom: 0;">
                Deel je verificatiecode nooit met anderen.
                SmartDesk zal je nooit vragen om deze code
                per telefoon, chat of e-mail door te geven.
            </p>

        </div>


        {{-- ========================================================= --}}
        {{-- BACK ACTIONS                                               --}}
        {{-- ========================================================= --}}

        <div
            class="hero-actions"
            style="
                display: flex;
                gap: 12px;
                flex-wrap: wrap;
            "
        >

            <a
                class="secondary-btn"
                href="{{ route('login') }}"
            >
                Terug naar inloggen
            </a>

            @auth

                <a
                    class="secondary-btn"
                    href="{{ route('account') }}"
                >
                    Naar mijn account
                </a>

            @endauth

        </div>

    </div>

</div>
```

</section>

@endsection
