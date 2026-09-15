@extends('layouts.admin-layout')

@section('title', 'SmartDesk Admin | Dashboard')

@section('page-title', 'Dashboard')

@section('content')

{{-- ========================================================= --}}
{{-- STATISTICS                                                 --}}
{{-- ========================================================= --}}

<section class="stats-grid">

    {{-- TOTAL USERS --}}

    <div class="stat-card">

        <div class="stat-label">
            Gebruikers
        </div>

        <div class="stat-num">
            {{ $totalUsers ?? $users->count() }}
        </div>

        <div class="stat-foot">
            Totaal geregistreerde accounts
        </div>

    </div>


    {{-- VERIFIED USERS --}}

    <div class="stat-card">

        <div class="stat-label">
            Geverifieerd
        </div>

        <div class="stat-num">
            {{ $verifiedUsers ?? $users->whereNotNull('email_verified_at')->count() }}
        </div>

        <div class="stat-foot">
            Accounts met bevestigd e-mailadres
        </div>

    </div>


    {{-- ADMIN USERS --}}

    <div class="stat-card">

        <div class="stat-label">
            Administrators
        </div>

        <div class="stat-num">
            {{ $adminUsers ?? $users->where('is_admin', true)->count() }}
        </div>

        <div class="stat-foot">
            Accounts met beheerrechten
        </div>

    </div>


    {{-- ORDERS --}}

    <div class="stat-card">

        <div class="stat-label">
            Bestellingen
        </div>

        <div class="stat-num">
            {{ $totalOrders ?? 0 }}
        </div>

        <div class="stat-foot">
            Totaal geplaatste bestellingen
        </div>

    </div>

</section>


{{-- ========================================================= --}}
{{-- QUICK ACTIONS                                               --}}
{{-- ========================================================= --}}

<section class="main-panel">

    <div class="section-heading">

        <div>

            <h2>
                Snel beheren
            </h2>

            <span>
                Open direct de belangrijkste onderdelen van SmartDesk.
            </span>

        </div>

    </div>


    <div
        style="
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        "
    >

        {{-- USERS --}}

        <div
            style="
                padding: 20px;
                border: 1px solid var(--line);
                border-radius: 16px;
                background: var(--panel-soft);
            "
        >

            <strong
                style="
                    display: block;
                    margin-bottom: 8px;
                    font-size: 16px;
                "
            >
                Gebruikers beheren
            </strong>

            <p
                style="
                    margin: 0 0 16px;
                    color: var(--muted);
                    font-size: 13px;
                    line-height: 1.6;
                "
            >
                Bekijk, wijzig of verwijder bestaande SmartDesk-accounts.
            </p>

            <a
                class="button secondary"
                href="{{ route('users.index') }}"
            >
                Naar gebruikers
            </a>

        </div>


        {{-- CREATE USER --}}

        <div
            style="
                padding: 20px;
                border: 1px solid var(--line);
                border-radius: 16px;
                background: var(--panel-soft);
            "
        >

            <strong
                style="
                    display: block;
                    margin-bottom: 8px;
                    font-size: 16px;
                "
            >
                Nieuwe gebruiker
            </strong>

            <p
                style="
                    margin: 0 0 16px;
                    color: var(--muted);
                    font-size: 13px;
                    line-height: 1.6;
                "
            >
                Maak een gebruiker of administrator handmatig aan.
            </p>

            <a
                class="button"
                href="{{ route('users.create') }}"
            >
                Gebruiker toevoegen
            </a>

        </div>


        {{-- CATALOG --}}

        <div
            style="
                padding: 20px;
                border: 1px solid var(--line);
                border-radius: 16px;
                background: var(--panel-soft);
            "
        >

            <strong
                style="
                    display: block;
                    margin-bottom: 8px;
                    font-size: 16px;
                "
            >
                Catalogus bekijken
            </strong>

            <p
                style="
                    margin: 0 0 16px;
                    color: var(--muted);
                    font-size: 13px;
                    line-height: 1.6;
                "
            >
                Open de huidige SmartDesk-autocatalogus.
            </p>

            <a
                class="button secondary"
                href="{{ route('catalog') }}"
            >
                Naar catalogus
            </a>

        </div>


        {{-- WEBSITE --}}

        <div
            style="
                padding: 20px;
                border: 1px solid var(--line);
                border-radius: 16px;
                background: var(--panel-soft);
            "
        >

            <strong
                style="
                    display: block;
                    margin-bottom: 8px;
                    font-size: 16px;
                "
            >
                Website openen
            </strong>

            <p
                style="
                    margin: 0 0 16px;
                    color: var(--muted);
                    font-size: 13px;
                    line-height: 1.6;
                "
            >
                Bekijk SmartDesk zoals gewone bezoekers de website zien.
            </p>

            <a
                class="button secondary"
                href="{{ route('home') }}"
            >
                Naar website
            </a>

        </div>

    </div>

</section>


{{-- ========================================================= --}}
{{-- USERS OVERVIEW                                              --}}
{{-- ========================================================= --}}

<section class="main-panel">

    <div class="section-heading">

        <div>

            <h2>
                Gebruikersbeheer
            </h2>

            <span>
                Overzicht van alle geregistreerde accounts.
            </span>

        </div>


        <a
            class="button"
            href="{{ route('users.create') }}"
        >
            + Gebruiker toevoegen
        </a>

    </div>


    <div class="table-wrap">

        <table>

            <thead>

                <tr>

                    <th>
                        Gebruiker
                    </th>

                    <th>
                        E-mailadres
                    </th>

                    <th>
                        Rol
                    </th>

                    <th>
                        Verificatie
                    </th>

                    <th>
                        Toegevoegd
                    </th>

                    <th>
                        Acties
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse ($users as $user)

                    <tr>

                        {{-- USER --}}

                        <td>

                            <div class="user-name">

                                <span class="avatar">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </span>


                                <div>

                                    <strong
                                        style="
                                            display: block;
                                            margin-bottom: 3px;
                                        "
                                    >
                                        {{ $user->name }}
                                    </strong>


                                    <small
                                        style="
                                            color: var(--muted);
                                        "
                                    >
                                        ID #{{ $user->id }}
                                    </small>


                                    @if (auth()->id() === $user->id)

                                        <small
                                            style="
                                                display: block;
                                                margin-top: 4px;
                                                color: var(--green);
                                                font-weight: 700;
                                            "
                                        >
                                            Dit ben jij
                                        </small>

                                    @endif

                                </div>

                            </div>

                        </td>


                        {{-- EMAIL --}}

                        <td>

                            <span
                                style="
                                    word-break: break-word;
                                "
                            >
                                {{ $user->email }}
                            </span>

                        </td>


                        {{-- ROLE --}}

                        <td>

                            @if ($user->is_admin)

                                <span
                                    class="badge"
                                    style="
                                        background: #dcecff;
                                        color: #174a82;
                                    "
                                >
                                    Administrator
                                </span>

                            @else

                                <span class="badge">
                                    Gebruiker
                                </span>

                            @endif

                        </td>


                        {{-- VERIFICATION --}}

                        <td>

                            @if ($user->email_verified_at)

                                <span
                                    class="badge"
                                    style="
                                        background: #e8f7ee;
                                        color: #187a42;
                                    "
                                >
                                    Geverifieerd
                                </span>

                            @else

                                <span
                                    class="badge"
                                    style="
                                        background: #fff4df;
                                        color: #9a6500;
                                    "
                                >
                                    Niet geverifieerd
                                </span>

                            @endif

                        </td>


                        {{-- CREATED --}}

                        <td>

                            <strong>
                                {{ $user->created_at->format('d-m-Y') }}
                            </strong>

                            <small
                                style="
                                    display: block;
                                    margin-top: 4px;
                                    color: var(--muted);
                                "
                            >
                                {{ $user->created_at->format('H:i') }}
                            </small>

                        </td>


                        {{-- ACTIONS --}}

                        <td>

                            <div
                                style="
                                    display: flex;
                                    gap: 8px;
                                    flex-wrap: wrap;
                                "
                            >

                                <a
                                    class="button secondary"
                                    href="{{ route('users.edit', $user) }}"
                                >
                                    Wijzigen
                                </a>


                                @if (auth()->id() !== $user->id)

                                    <form
                                        method="POST"
                                        action="{{ route('users.destroy', $user) }}"
                                        style="margin: 0;"
                                        onsubmit="return confirm('Weet je zeker dat je {{ $user->name }} definitief wilt verwijderen?');"
                                    >

                                        @csrf
                                        @method('DELETE')


                                        <button
                                            class="button danger"
                                            type="submit"
                                        >
                                            Verwijderen
                                        </button>

                                    </form>

                                @else

                                    <span
                                        style="
                                            display: inline-block;
                                            padding: 8px 10px;
                                            border-radius: 8px;
                                            background: var(--panel-soft);
                                            color: var(--muted);
                                            font-size: 11px;
                                            font-weight: 700;
                                        "
                                    >
                                        Eigen account
                                    </span>

                                @endif

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="6"
                            style="
                                padding: 45px 20px;
                                text-align: center;
                            "
                        >

                            <div
                                style="
                                    max-width: 420px;
                                    margin: 0 auto;
                                "
                            >

                                <h3
                                    style="
                                        margin: 0 0 8px;
                                    "
                                >
                                    Nog geen gebruikers
                                </h3>

                                <p
                                    style="
                                        margin: 0 0 20px;
                                        color: var(--muted);
                                    "
                                >
                                    Er zijn momenteel nog geen gebruikers geregistreerd.
                                </p>

                                <a
                                    class="button"
                                    href="{{ route('users.create') }}"
                                >
                                    + Eerste gebruiker aanmaken
                                </a>

                            </div>

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    {{-- ALL USERS --}}

    @if ($users->isNotEmpty())

        <div
            style="
                margin-top: 22px;
                display: flex;
                justify-content: flex-end;
            "
        >

            <a
                class="button secondary"
                href="{{ route('users.index') }}"
            >
                Volledig gebruikersbeheer
            </a>

        </div>

    @endif

</section>


{{-- ========================================================= --}}
{{-- ADMIN INFORMATION                                          --}}
{{-- ========================================================= --}}

<section class="main-panel">

    <div class="section-heading">

        <div>

            <h2>
                Administrator
            </h2>

            <span>
                Informatie over je huidige beheerdersaccount.
            </span>

        </div>

    </div>


    <div
        style="
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        "
    >

        <div
            style="
                padding: 18px;
                border: 1px solid var(--line);
                border-radius: 14px;
                background: var(--panel-soft);
            "
        >

            <strong>
                Ingelogd als
            </strong>

            <p
                style="
                    margin: 8px 0 0;
                    color: var(--muted);
                "
            >
                {{ auth()->user()->name }}
            </p>

        </div>


        <div
            style="
                padding: 18px;
                border: 1px solid var(--line);
                border-radius: 14px;
                background: var(--panel-soft);
            "
        >

            <strong>
                E-mailadres
            </strong>

            <p
                style="
                    margin: 8px 0 0;
                    color: var(--muted);
                    word-break: break-word;
                "
            >
                {{ auth()->user()->email }}
            </p>

        </div>


        <div
            style="
                padding: 18px;
                border: 1px solid var(--line);
                border-radius: 14px;
                background: var(--panel-soft);
            "
        >

            <strong>
                Rechten
            </strong>

            <p
                style="
                    margin: 8px 0 0;
                    color: var(--muted);
                "
            >
                Volledige administratorrechten
            </p>

        </div>

    </div>

</section>

@endsection
