```blade
@extends('layouts.admin-layout')

@section('title', 'SmartDesk Admin | Nieuwe gebruiker')

@section('page-title', 'Nieuwe gebruiker')

@section('content')

<section class="main-panel">

    {{-- ========================================================= --}}
    {{-- HEADER                                                     --}}
    {{-- ========================================================= --}}

    <div class="section-heading">

        <div>

            <h2>
                Nieuwe gebruiker aanmaken
            </h2>

            <span>
                Maak handmatig een nieuw SmartDesk-account aan
                en bepaal direct de rechten en verificatiestatus.
            </span>

        </div>


        <a
            class="button secondary"
            href="{{ route('users.index') }}"
        >
            Terug naar gebruikers
        </a>

    </div>


    {{-- ========================================================= --}}
    {{-- SUCCESS MESSAGE                                            --}}
    {{-- ========================================================= --}}

    @if (session('success'))

        <div
            class="success"
            style="margin-bottom: 24px;"
        >
            <strong>
                Gelukt!
            </strong>

            <br>

            {{ session('success') }}
        </div>

    @endif


    {{-- ========================================================= --}}
    {{-- GENERAL ERRORS                                             --}}
    {{-- ========================================================= --}}

    @if ($errors->any())

        <div
            class="error"
            style="margin-bottom: 24px;"
        >

            <strong>
                De gebruiker kon niet worden aangemaakt.
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
    {{-- FORM                                                       --}}
    {{-- ========================================================= --}}

    <div class="form-wrap">

        <form
            method="POST"
            action="{{ route('users.store') }}"
        >

            @csrf


            {{-- ========================================================= --}}
            {{-- NAME                                                       --}}
            {{-- ========================================================= --}}

            <div class="form-row">

                <label for="name">
                    Naam
                </label>

                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Voornaam en achternaam"
                    required
                    autofocus
                    autocomplete="name"
                >

                @error('name')

                    <span
                        style="
                            display: block;
                            margin-top: 6px;
                            color: var(--danger);
                            font-size: 12px;
                        "
                    >
                        {{ $message }}
                    </span>

                @enderror

            </div>


            {{-- ========================================================= --}}
            {{-- EMAIL                                                      --}}
            {{-- ========================================================= --}}

            <div class="form-row">

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

                    <span
                        style="
                            display: block;
                            margin-top: 6px;
                            color: var(--danger);
                            font-size: 12px;
                        "
                    >
                        {{ $message }}
                    </span>

                @enderror

            </div>


            {{-- ========================================================= --}}
            {{-- PASSWORD                                                   --}}
            {{-- ========================================================= --}}

            <div class="form-row">

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
                        color: var(--muted);
                    "
                >
                    Gebruik minimaal 8 tekens.
                </small>

                @error('password')

                    <span
                        style="
                            display: block;
                            margin-top: 6px;
                            color: var(--danger);
                            font-size: 12px;
                        "
                    >
                        {{ $message }}
                    </span>

                @enderror

            </div>


            {{-- ========================================================= --}}
            {{-- PASSWORD CONFIRMATION                                      --}}
            {{-- ========================================================= --}}

            <div class="form-row">

                <label for="password_confirmation">
                    Bevestig wachtwoord
                </label>

                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    placeholder="Herhaal het wachtwoord"
                    required
                    minlength="8"
                    autocomplete="new-password"
                >

                @error('password_confirmation')

                    <span
                        style="
                            display: block;
                            margin-top: 6px;
                            color: var(--danger);
                            font-size: 12px;
                        "
                    >
                        {{ $message }}
                    </span>

                @enderror

            </div>


            {{-- ========================================================= --}}
            {{-- EMAIL VERIFIED                                            --}}
            {{-- ========================================================= --}}

            <div
                class="form-row"
                style="
                    padding: 16px;
                    border: 1px solid var(--line);
                    border-radius: 12px;
                    background: var(--panel);
                "
            >

                <label
                    for="email_verified"
                    style="
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        margin: 0;
                        cursor: pointer;
                        text-transform: none;
                        letter-spacing: normal;
                        font-size: 14px;
                    "
                >

                    <input
                        id="email_verified"
                        type="checkbox"
                        name="email_verified"
                        value="1"
                        {{ old('email_verified') ? 'checked' : '' }}
                        style="
                            width: auto;
                            margin: 0;
                        "
                    >

                    <span>
                        E-mailadres direct als geverifieerd markeren
                    </span>

                </label>

                <small
                    style="
                        display: block;
                        margin-top: 10px;
                        color: var(--muted);
                    "
                >
                    Als dit niet is aangevinkt,
                    moet de gebruiker het e-mailadres zelf verifiëren.
                </small>

            </div>


            {{-- ========================================================= --}}
            {{-- ADMIN RIGHTS                                               --}}
            {{-- ========================================================= --}}

            <div
                class="form-row"
                style="
                    padding: 16px;
                    border: 1px solid var(--line);
                    border-radius: 12px;
                    background: var(--panel);
                "
            >

                <label
                    for="is_admin"
                    style="
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        margin: 0;
                        cursor: pointer;
                        text-transform: none;
                        letter-spacing: normal;
                        font-size: 14px;
                    "
                >

                    <input
                        id="is_admin"
                        type="checkbox"
                        name="is_admin"
                        value="1"
                        {{ old('is_admin') ? 'checked' : '' }}
                        style="
                            width: auto;
                            margin: 0;
                        "
                    >

                    <span>
                        Administratorrechten geven
                    </span>

                </label>

                <small
                    style="
                        display: block;
                        margin-top: 10px;
                        color: var(--muted);
                    "
                >
                    Administrators krijgen toegang tot het beheer,
                    kunnen gebruikers bekijken, toevoegen,
                    wijzigen en verwijderen.
                </small>

            </div>


            {{-- ========================================================= --}}
            {{-- ACCOUNT INFO                                               --}}
            {{-- ========================================================= --}}

            <div
                style="
                    margin: 24px 0;
                    padding: 18px;
                    border-radius: 12px;
                    background: var(--panel-soft);
                    border: 1px solid var(--line);
                "
            >

                <strong
                    style="
                        display: block;
                        margin-bottom: 8px;
                    "
                >
                    Accountinformatie
                </strong>

                <p
                    style="
                        margin: 0;
                        color: var(--muted);
                        font-size: 13px;
                        line-height: 1.7;
                    "
                >
                    De gebruiker wordt direct opgeslagen in SmartDesk.
                    Controleer naam, e-mailadres, wachtwoord,
                    verificatiestatus en administratorrechten
                    voordat je het account aanmaakt.
                </p>

            </div>


            {{-- ========================================================= --}}
            {{-- SECURITY INFO                                              --}}
            {{-- ========================================================= --}}

            <div
                style="
                    margin-bottom: 24px;
                    padding: 18px;
                    border-radius: 12px;
                    background: #fff8e6;
                    border: 1px solid #f0dfb9;
                "
            >

                <strong
                    style="
                        display: block;
                        margin-bottom: 8px;
                    "
                >
                    Let op bij administratorrechten
                </strong>

                <p
                    style="
                        margin: 0;
                        color: #735200;
                        font-size: 13px;
                        line-height: 1.7;
                    "
                >
                    Geef administratorrechten alleen aan personen
                    die daadwerkelijk toegang mogen hebben tot het beheer.
                    Een administrator kan gevoelige gebruikersgegevens wijzigen
                    en accounts verwijderen.
                </p>

            </div>


            {{-- ========================================================= --}}
            {{-- ACTIONS                                                    --}}
            {{-- ========================================================= --}}

            <div
                class="topbar-actions"
                style="
                    display: flex;
                    gap: 12px;
                    flex-wrap: wrap;
                "
            >

                <button
                    class="button"
                    type="submit"
                >
                    Gebruiker aanmaken
                </button>


                <a
                    class="button secondary"
                    href="{{ route('users.index') }}"
                >
                    Annuleren
                </a>

            </div>

        </form>

    </div>

</section>

@endsection
```
