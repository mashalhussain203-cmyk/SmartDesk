@extends('layouts.admin-layout')

@section('title', 'SmartDesk Admin | Gebruikers')

@section('page-title', 'Gebruikersbeheer')

@section('content')

<section class="main-panel">

    {{-- ========================================================= --}}
    {{-- HEADER                                                    --}}
    {{-- ========================================================= --}}

    <div class="section-heading">

        <div>
            <h2>
                Alle gebruikers
            </h2>

            <span>
                Bekijk, beheer, wijzig en verwijder geregistreerde SmartDesk-accounts.
            </span>
        </div>


        <a
            class="button"
            href="{{ route('users.create') }}"
        >
            + Nieuwe gebruiker
        </a>

    </div>


    {{-- ========================================================= --}}
    {{-- STATISTIEKEN                                              --}}
    {{-- ========================================================= --}}

    <div
        style="
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        "
    >

        {{-- TOTAAL --}}

        <div class="stat-card">

            <div class="stat-label">
                Totaal gebruikers
            </div>

            <div class="stat-num">
                {{ $users->count() }}
            </div>

            <div class="stat-foot">
                Alle geregistreerde accounts
            </div>

        </div>


        {{-- GEVERIFIEERD --}}

        <div class="stat-card">

            <div class="stat-label">
                Geverifieerd
            </div>

            <div class="stat-num">
                {{ $users->whereNotNull('email_verified_at')->count() }}
            </div>

            <div class="stat-foot">
                Accounts met bevestigd e-mailadres
            </div>

        </div>


        {{-- NIET GEVERIFIEERD --}}

        <div class="stat-card">

            <div class="stat-label">
                Niet geverifieerd
            </div>

            <div class="stat-num">
                {{ $users->whereNull('email_verified_at')->count() }}
            </div>

            <div class="stat-foot">
                Accounts die nog verificatie nodig hebben
            </div>

        </div>


        {{-- ADMINS --}}

        <div class="stat-card">

            <div class="stat-label">
                Administrators
            </div>

            <div class="stat-num">
                {{ $users->where('is_admin', true)->count() }}
            </div>

            <div class="stat-foot">
                Accounts met beheerrechten
            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- GEBRUIKERSTABEL                                           --}}
    {{-- ========================================================= --}}

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

                        {{-- ============================================= --}}
                        {{-- GEBRUIKER                                     --}}
                        {{-- ============================================= --}}

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


                        {{-- ============================================= --}}
                        {{-- E-MAIL                                        --}}
                        {{-- ============================================= --}}

                        <td>

                            <span
                                style="
                                    word-break: break-word;
                                "
                            >
                                {{ $user->email }}
                            </span>

                        </td>


                        {{-- ============================================= --}}
                        {{-- ROL                                           --}}
                        {{-- ============================================= --}}

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


                        {{-- ============================================= --}}
                        {{-- VERIFICATIE                                   --}}
                        {{-- ============================================= --}}

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


                                <small
                                    style="
                                        display: block;
                                        margin-top: 6px;
                                        color: var(--muted);
                                    "
                                >
                                    {{ $user->email_verified_at->format('d-m-Y H:i') }}
                                </small>

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


                        {{-- ============================================= --}}
                        {{-- AANGEMAAKT                                    --}}
                        {{-- ============================================= --}}

                        <td>

                            @if ($user->created_at)

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

                            @else

                                <span style="color: var(--muted);">
                                    Onbekend
                                </span>

                            @endif

                        </td>


                        {{-- ============================================= --}}
                        {{-- ACTIES                                        --}}
                        {{-- ============================================= --}}

                        <td>

                            <div
                                style="
                                    display: flex;
                                    align-items: center;
                                    gap: 8px;
                                    flex-wrap: wrap;
                                "
                            >

                                {{-- ===================================== --}}
                                {{-- WIJZIGEN                              --}}
                                {{-- ===================================== --}}
                                {{--
                                    BELANGRIJK:

                                    Deze knop moet naar:

                                    GET /admin/users/{user}/edit

                                    en NIET naar:

                                    /admin/users/{user}
                                --}}

                                <a
                                    class="button secondary"
                                    href="{{ route('users.edit', ['user' => $user->id]) }}"
                                >
                                    Wijzigen
                                </a>


                                {{-- ===================================== --}}
                                {{-- VERWIJDEREN                           --}}
                                {{-- ===================================== --}}

                                @if (auth()->id() !== $user->id)

                                    <form
                                        method="POST"
                                        action="{{ route('users.destroy', ['user' => $user->id]) }}"
                                        style="margin: 0;"
                                        onsubmit="return confirm('Weet je zeker dat je dit account definitief wilt verwijderen?');"
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
                                padding: 50px 20px;
                                text-align: center;
                            "
                        >

                            <div
                                style="
                                    max-width: 440px;
                                    margin: 0 auto;
                                "
                            >

                                <h3
                                    style="
                                        margin: 0 0 10px;
                                    "
                                >
                                    Nog geen gebruikers
                                </h3>


                                <p
                                    style="
                                        margin: 0 0 22px;
                                        color: var(--muted);
                                        line-height: 1.6;
                                    "
                                >
                                    Er zijn momenteel nog geen gebruikers geregistreerd.
                                    Maak een gebruiker aan om te beginnen.
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


    {{-- ========================================================= --}}
    {{-- ADMIN INFORMATIE                                          --}}
    {{-- ========================================================= --}}

    <div
        style="
            margin-top: 28px;
            padding: 18px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: var(--panel-soft);
        "
    >

        <strong
            style="
                display: block;
                margin-bottom: 8px;
            "
        >
            Beheerrechten
        </strong>


        <p
            style="
                margin: 0;
                color: var(--muted);
                font-size: 13px;
                line-height: 1.7;
            "
        >
            Als administrator kun je gebruikers toevoegen,
            accountgegevens wijzigen, wachtwoorden opnieuw instellen,
            e-mailverificatie beheren, administratorrechten aanpassen
            en gebruikers verwijderen.

            Je eigen administratoraccount kan niet vanuit deze lijst
            worden verwijderd.
        </p>

    </div>

</section>

@endsection