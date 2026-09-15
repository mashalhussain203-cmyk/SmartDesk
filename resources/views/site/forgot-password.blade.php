@extends('layouts.site-layout')

@section('title', 'SmartDesk | Wachtwoord herstellen')

@section('content')

<section class="form-page">

    <div class="site-wrap">

        <div class="form-wrapper">

            {{-- ========================================================= --}}
            {{-- HEADER                                                     --}}
            {{-- ========================================================= --}}

            <div style="margin-bottom: 30px;">

                <h1 class="form-title">
                    Wachtwoord herstellen
                </h1>

                <p class="form-sub">
                    Vul het e-mailadres van je SmartDesk-account in.
                    We sturen je daarna een link waarmee je een nieuw wachtwoord kunt instellen.
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
            {{-- PASSWORD RESET FORM                                         --}}
            {{-- ========================================================= --}}

            <form
                class="reg-form"
                method="POST"
                action="{{ route('password.email') }}"
            >

                @csrf


                <div style="margin-bottom: 20px;">

                    <label for="email">
                        E-mailadres
                    </label>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="jouw@email.nl"
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


                {{-- ========================================================= --}}
                {{-- INFORMATION                                                --}}
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
                            Hoe werkt het?
                        </strong>

                        Als het e-mailadres bij een SmartDesk-account hoort,
                        ontvang je een wachtwoordresetlink via e-mail.
                        Deze link is 60 minuten geldig.
                    </small>

                </div>


                <button
                    class="primary-btn"
                    type="submit"
                    style="width: 100%;"
                >
                    Verstuur resetlink
                </button>

            </form>


            {{-- ========================================================= --}}
            {{-- SECURITY INFORMATION                                       --}}
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
                    Beveiliging
                </h2>

                <p style="margin-bottom: 0;">
                    SmartDesk zal je nooit vragen om je wachtwoord per e-mail
                    door te sturen. Deel ook nooit je wachtwoordresetlink met anderen.
                </p>

            </div>


            {{-- ========================================================= --}}
            {{-- BACK TO LOGIN                                               --}}
            {{-- ========================================================= --}}

            <div
                style="
                    margin-top: 25px;
                    text-align: center;
                "
            >

                <a
                    href="{{ route('login') }}"
                    style="
                        color: inherit;
                        font-weight: 600;
                    "
                >
                    ← Terug naar inloggen
                </a>

            </div>

        </div>

    </div>

</section>

@endsection
