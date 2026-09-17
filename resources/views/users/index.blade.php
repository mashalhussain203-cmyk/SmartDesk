@extends('layouts.admin-layout')

@section('title', 'Mashal Admin | Gebruikers')
@section('page-title', 'Gebruikersbeheer')

@push('styles')
<style>
    .users-page {
        position: relative;
        overflow: hidden;
        padding: 8px 0 36px;
    }

    .users-page::before {
        content: "USERS";
        position: absolute;
        right: -30px;
        top: 18px;
        color: rgba(255,255,255,.012);
        font-size: clamp(110px, 14vw, 230px);
        font-weight: 950;
        line-height: .8;
        letter-spacing: -.06em;
        pointer-events: none;
        user-select: none;
    }

    /* ========================================================= */
    /* HERO                                                       */
    /* ========================================================= */

    .users-hero {
        position: relative;
        z-index: 2;
        margin-bottom: 24px;
        padding: 30px;
        display: grid;
        grid-template-columns: minmax(0,1fr) auto;
        gap: 28px;
        align-items: end;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 24px;
        background:
            radial-gradient(circle at 88% 10%, rgba(215,164,95,.11), transparent 18rem),
            linear-gradient(145deg, rgba(255,255,255,.045), rgba(255,255,255,.015));
        box-shadow: 0 18px 50px rgba(0,0,0,.18);
        overflow: hidden;
    }

    .users-hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 11px;
        color: #d6a45f;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .19em;
        text-transform: uppercase;
    }

    .users-hero-kicker::before {
        content: "";
        width: 28px;
        height: 1px;
        background: #d6a45f;
    }

    .users-hero h2 {
        margin: 0;
        color: #fff;
        font-size: clamp(30px, 3.6vw, 46px);
        line-height: 1;
        letter-spacing: -.05em;
    }

    .users-hero p {
        max-width: 720px;
        margin: 11px 0 0;
        color: #7d838a;
        font-size: 12px;
        line-height: 1.75;
    }

    .users-create-btn {
        min-height: 44px;
        padding: 0 17px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 0;
        border-radius: 999px;
        background: linear-gradient(135deg, #f1cc8b, #ca914c);
        color: #14100b;
        text-decoration: none;
        font-size: 9px;
        font-weight: 950;
        letter-spacing: .04em;
        box-shadow: 0 14px 34px rgba(215,164,95,.18);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .users-create-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 22px 48px rgba(215,164,95,.28);
    }

    /* ========================================================= */
    /* STATS                                                      */
    /* ========================================================= */

    .users-stats {
        position: relative;
        z-index: 2;
        margin-bottom: 22px;
        display: grid;
        grid-template-columns: repeat(4, minmax(0,1fr));
        gap: 14px;
    }

    .users-stat-card {
        padding: 20px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 18px;
        background:
            linear-gradient(145deg, rgba(255,255,255,.04), rgba(255,255,255,.014));
        box-shadow: 0 14px 36px rgba(0,0,0,.14);
    }

    .users-stat-label {
        color: #6d737a;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .users-stat-value {
        margin-top: 9px;
        color: #fff;
        font-size: 30px;
        line-height: 1;
        letter-spacing: -.05em;
        font-weight: 950;
    }

    .users-stat-foot {
        margin-top: 8px;
        color: #62686f;
        font-size: 9px;
        line-height: 1.6;
    }

    /* ========================================================= */
    /* TOOLBAR                                                    */
    /* ========================================================= */

    .users-toolbar {
        position: relative;
        z-index: 2;
        margin-bottom: 16px;
        padding: 14px;
        display: grid;
        grid-template-columns: minmax(0,1fr) auto;
        gap: 12px;
        align-items: center;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 17px;
        background: rgba(255,255,255,.022);
    }

    .users-search-wrap {
        position: relative;
    }

    .users-search {
        width: 100%;
        height: 44px;
        padding: 0 42px 0 14px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 12px;
        outline: none;
        background: rgba(255,255,255,.025);
        color: #fff;
        font-size: 11px;
    }

    .users-search:focus {
        border-color: rgba(215,164,95,.35);
        box-shadow: 0 0 0 4px rgba(215,164,95,.05);
    }

    .users-search::placeholder {
        color: #545a61;
    }

    .users-search-icon {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7178;
        font-size: 11px;
    }

    .users-filters {
        display: flex;
        gap: 7px;
        flex-wrap: wrap;
    }

    .users-filter-btn {
        min-height: 38px;
        padding: 0 12px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 999px;
        background: rgba(255,255,255,.02);
        color: #858b92;
        font-size: 8px;
        font-weight: 900;
        cursor: pointer;
        letter-spacing: .06em;
        text-transform: uppercase;
        transition: .2s ease;
    }

    .users-filter-btn:hover,
    .users-filter-btn.active {
        border-color: rgba(215,164,95,.22);
        background: rgba(215,164,95,.06);
        color: #e4b773;
    }

    /* ========================================================= */
    /* TABLE                                                      */
    /* ========================================================= */

    .users-table-card {
        position: relative;
        z-index: 2;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 22px;
        background:
            linear-gradient(145deg, rgba(255,255,255,.04), rgba(255,255,255,.014));
        box-shadow: 0 16px 44px rgba(0,0,0,.17);
    }

    .users-table-scroll {
        overflow-x: auto;
    }

    .users-table {
        width: 100%;
        min-width: 980px;
        border-collapse: collapse;
    }

    .users-table thead th {
        padding: 15px 18px;
        border-bottom: 1px solid rgba(255,255,255,.07);
        color: #676d74;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .14em;
        text-align: left;
        text-transform: uppercase;
        background: rgba(255,255,255,.015);
    }

    .users-table tbody td {
        padding: 17px 18px;
        border-bottom: 1px solid rgba(255,255,255,.055);
        color: #bdbdb9;
        font-size: 11px;
        vertical-align: middle;
    }

    .users-table tbody tr {
        transition: background .2s ease;
    }

    .users-table tbody tr:hover {
        background: rgba(215,164,95,.022);
    }

    .users-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .user-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-avatar {
        flex-shrink: 0;
        width: 42px;
        height: 42px;
        display: grid;
        place-items: center;
        border-radius: 13px;
        background: linear-gradient(145deg, #f0ca86, #b67e3d);
        color: #15110c;
        font-size: 15px;
        font-weight: 950;
    }

    .user-cell-copy strong {
        display: block;
        color: #efede7;
        font-size: 11px;
    }

    .user-cell-copy small {
        display: block;
        margin-top: 3px;
        color: #60666d;
        font-size: 8px;
    }

    .user-self {
        display: inline-block;
        margin-top: 4px;
        color: #9ce7bc !important;
        font-weight: 800;
    }

    .user-email {
        max-width: 240px;
        word-break: break-word;
        color: #aaaead;
    }

    .user-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 9px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 999px;
        background: rgba(255,255,255,.025);
        color: #9ea3a8;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    .user-badge.admin {
        border-color: rgba(113,166,255,.18);
        background: rgba(113,166,255,.055);
        color: #a9c8ff;
    }

    .user-badge.verified {
        border-color: rgba(91,214,149,.18);
        background: rgba(91,214,149,.055);
        color: #9ce7bc;
    }

    .user-badge.pending {
        border-color: rgba(242,198,109,.18);
        background: rgba(242,198,109,.055);
        color: #e9c674;
    }

    .verified-date {
        display: block;
        margin-top: 5px;
        color: #5f656c;
        font-size: 8px;
    }

    .date-main {
        color: #d5d3cd;
        font-weight: 800;
    }

    .date-time {
        display: block;
        margin-top: 3px;
        color: #5f656c;
        font-size: 8px;
    }

    /* ========================================================= */
    /* ACTIONS                                                    */
    /* ========================================================= */

    .user-actions {
        display: flex;
        align-items: center;
        gap: 7px;
        flex-wrap: wrap;
    }

    .user-action-link,
    .user-action-danger,
    .user-action-self {
        min-height: 34px;
        padding: 0 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        font-size: 8px;
        font-weight: 900;
        text-decoration: none;
        white-space: nowrap;
    }

    .user-action-link {
        border: 1px solid rgba(255,255,255,.09);
        background: rgba(255,255,255,.025);
        color: #aaaead;
        transition: .2s ease;
    }

    .user-action-link:hover {
        border-color: rgba(215,164,95,.22);
        background: rgba(215,164,95,.055);
        color: #efc985;
    }

    .user-action-danger {
        border: 1px solid rgba(241,123,123,.18);
        background: rgba(241,123,123,.06);
        color: #f0a0a0;
        cursor: pointer;
        transition: .2s ease;
    }

    .user-action-danger:hover {
        border-color: rgba(241,123,123,.35);
        background: rgba(241,123,123,.11);
    }

    .user-action-self {
        border: 1px solid rgba(255,255,255,.06);
        background: rgba(255,255,255,.018);
        color: #5f656c;
    }

    /* ========================================================= */
    /* EMPTY                                                      */
    /* ========================================================= */

    .users-empty {
        padding: 58px 22px !important;
        text-align: center;
    }

    .users-empty-inner {
        max-width: 440px;
        margin: 0 auto;
    }

    .users-empty-mark {
        width: 56px;
        height: 56px;
        margin: 0 auto 16px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(215,164,95,.16);
        border-radius: 50%;
        background: rgba(215,164,95,.055);
        color: #d9aa68;
        font-weight: 950;
    }

    .users-empty h3 {
        margin: 0 0 8px;
        color: #e4e2dd;
        font-size: 17px;
    }

    .users-empty p {
        margin: 0 0 18px;
        color: #6d737a;
        font-size: 10px;
        line-height: 1.7;
    }

    /* ========================================================= */
    /* ADMIN INFO                                                 */
    /* ========================================================= */

    .users-info {
        position: relative;
        z-index: 2;
        margin-top: 22px;
        padding: 20px;
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 13px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 18px;
        background: rgba(255,255,255,.02);
    }

    .users-info-icon {
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(215,164,95,.16);
        border-radius: 50%;
        color: #c99b5e;
        font-size: 11px;
    }

    .users-info strong {
        display: block;
        color: #d9d7d2;
        font-size: 11px;
    }

    .users-info p {
        margin: 5px 0 0;
        color: #686e75;
        font-size: 9px;
        line-height: 1.7;
    }

    /* ========================================================= */
    /* RESPONSIVE                                                 */
    /* ========================================================= */

    @media (max-width: 1050px) {
        .users-hero {
            grid-template-columns: 1fr;
        }

        .users-stats {
            grid-template-columns: 1fr 1fr;
        }

        .users-toolbar {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 650px) {
        .users-stats {
            grid-template-columns: 1fr;
        }

        .users-hero {
            padding: 22px;
        }

        .users-create-btn {
            width: 100%;
        }

        .users-filters {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        .users-filter-btn {
            width: 100%;
        }
    }
</style>
@endpush


@section('content')

<section class="users-page">

    {{-- ========================================================= --}}
    {{-- HERO                                                       --}}
    {{-- ========================================================= --}}

    <div class="users-hero">

        <div>

            <span class="users-hero-kicker">
                Mashal User Management
            </span>

            <h2>
                Gebruikersbeheer
            </h2>

            <p>
                Bekijk en beheer alle geregistreerde Mashal-accounts,
                controleer verificatiestatussen, pas rollen aan
                en open individuele accounts voor verdere wijzigingen.
            </p>

        </div>


        <a
            class="users-create-btn"
            href="{{ route('users.create') }}"
        >
            + Nieuwe gebruiker
        </a>

    </div>


    {{-- ========================================================= --}}
    {{-- STATISTICS                                                 --}}
    {{-- ========================================================= --}}

    <div class="users-stats">

        <div class="users-stat-card">

            <div class="users-stat-label">
                Totaal gebruikers
            </div>

            <div class="users-stat-value">
                {{ $users->count() }}
            </div>

            <div class="users-stat-foot">
                Alle geregistreerde Mashal-accounts
            </div>

        </div>


        <div class="users-stat-card">

            <div class="users-stat-label">
                Geverifieerd
            </div>

            <div class="users-stat-value">
                {{ $users->whereNotNull('email_verified_at')->count() }}
            </div>

            <div class="users-stat-foot">
                Accounts met bevestigd e-mailadres
            </div>

        </div>


        <div class="users-stat-card">

            <div class="users-stat-label">
                Niet geverifieerd
            </div>

            <div class="users-stat-value">
                {{ $users->whereNull('email_verified_at')->count() }}
            </div>

            <div class="users-stat-foot">
                Accounts waarvoor verificatie nog openstaat
            </div>

        </div>


        <div class="users-stat-card">

            <div class="users-stat-label">
                Administrators
            </div>

            <div class="users-stat-value">
                {{ $users->where('is_admin', true)->count() }}
            </div>

            <div class="users-stat-foot">
                Accounts met toegang tot beheerfuncties
            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- TOOLBAR                                                    --}}
    {{-- ========================================================= --}}

    <div class="users-toolbar">

        <div class="users-search-wrap">

            <input
                class="users-search"
                id="usersSearch"
                type="search"
                placeholder="Zoek op naam, e-mailadres of ID..."
                autocomplete="off"
            >

            <span class="users-search-icon">
                ⌕
            </span>

        </div>


        <div class="users-filters">

            <button
                class="users-filter-btn active"
                type="button"
                data-filter="all"
            >
                Alles
            </button>

            <button
                class="users-filter-btn"
                type="button"
                data-filter="verified"
            >
                Geverifieerd
            </button>

            <button
                class="users-filter-btn"
                type="button"
                data-filter="pending"
            >
                Niet geverifieerd
            </button>

            <button
                class="users-filter-btn"
                type="button"
                data-filter="admin"
            >
                Admins
            </button>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- USERS TABLE                                                --}}
    {{-- ========================================================= --}}

    <div class="users-table-card">

        <div class="users-table-scroll">

            <table class="users-table">

                <thead>
                    <tr>
                        <th>Gebruiker</th>
                        <th>E-mailadres</th>
                        <th>Rol</th>
                        <th>Verificatie</th>
                        <th>Toegevoegd</th>
                        <th>Acties</th>
                    </tr>
                </thead>


                <tbody id="usersTableBody">

                    @forelse ($users as $user)

                        <tr
                            data-user-row
                            data-name="{{ strtolower($user->name) }}"
                            data-email="{{ strtolower($user->email) }}"
                            data-id="{{ $user->id }}"
                            data-admin="{{ $user->is_admin ? '1' : '0' }}"
                            data-verified="{{ $user->email_verified_at ? '1' : '0' }}"
                        >

                            {{-- USER --}}

                            <td>

                                <div class="user-cell">

                                    <span class="user-avatar">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </span>


                                    <div class="user-cell-copy">

                                        <strong>
                                            {{ $user->name }}
                                        </strong>

                                        <small>
                                            ID #{{ $user->id }}
                                        </small>


                                        @if (auth()->id() === $user->id)

                                            <small class="user-self">
                                                Dit ben jij
                                            </small>

                                        @endif

                                    </div>

                                </div>

                            </td>


                            {{-- EMAIL --}}

                            <td>
                                <span class="user-email">
                                    {{ $user->email }}
                                </span>
                            </td>


                            {{-- ROLE --}}

                            <td>

                                @if ($user->is_admin)

                                    <span class="user-badge admin">
                                        Administrator
                                    </span>

                                @else

                                    <span class="user-badge">
                                        Gebruiker
                                    </span>

                                @endif

                            </td>


                            {{-- VERIFICATION --}}

                            <td>

                                @if ($user->email_verified_at)

                                    <span class="user-badge verified">
                                        Geverifieerd
                                    </span>

                                    <small class="verified-date">
                                        {{ $user->email_verified_at->format('d-m-Y H:i') }}
                                    </small>

                                @else

                                    <span class="user-badge pending">
                                        Niet geverifieerd
                                    </span>

                                @endif

                            </td>


                            {{-- CREATED --}}

                            <td>

                                @if ($user->created_at)

                                    <span class="date-main">
                                        {{ $user->created_at->format('d-m-Y') }}
                                    </span>

                                    <small class="date-time">
                                        {{ $user->created_at->format('H:i') }}
                                    </small>

                                @else

                                    <span class="date-time">
                                        Onbekend
                                    </span>

                                @endif

                            </td>


                            {{-- ACTIONS --}}

                            <td>

                                <div class="user-actions">

                                    <a
                                        class="user-action-link"
                                        href="{{ route('users.edit', ['user' => $user->id]) }}"
                                    >
                                        Wijzigen
                                    </a>


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
                                                class="user-action-danger"
                                                type="submit"
                                            >
                                                Verwijderen
                                            </button>

                                        </form>

                                    @else

                                        <span class="user-action-self">
                                            Eigen account
                                        </span>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                class="users-empty"
                                colspan="6"
                            >

                                <div class="users-empty-inner">

                                    <div class="users-empty-mark">
                                        M
                                    </div>

                                    <h3>
                                        Nog geen gebruikers
                                    </h3>

                                    <p>
                                        Er zijn momenteel nog geen gebruikers geregistreerd.
                                        Maak de eerste Mashal-gebruiker aan om te beginnen.
                                    </p>

                                    <a
                                        class="users-create-btn"
                                        href="{{ route('users.create') }}"
                                    >
                                        + Eerste gebruiker
                                    </a>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- INFORMATION                                                --}}
    {{-- ========================================================= --}}

    <div class="users-info">

        <div class="users-info-icon">
            i
        </div>

        <div>

            <strong>
                Beheerrechten
            </strong>

            <p>
                Als administrator kun je gebruikers toevoegen,
                profielgegevens wijzigen, wachtwoorden opnieuw instellen,
                e-mailverificatie beheren, administratorrechten aanpassen
                en gebruikers verwijderen. Je eigen administratoraccount
                kan niet vanuit deze lijst worden verwijderd.
            </p>

        </div>

    </div>

</section>

@endsection


@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {

        const searchInput =
            document.getElementById('usersSearch');

        const rows =
            Array.from(
                document.querySelectorAll('[data-user-row]')
            );

        const filterButtons =
            Array.from(
                document.querySelectorAll('[data-filter]')
            );

        let activeFilter = 'all';


        function applyUsersFilter() {

            const query =
                searchInput
                    ? searchInput.value.trim().toLowerCase()
                    : '';

            rows.forEach(function (row) {

                const haystack = [
                    row.dataset.name || '',
                    row.dataset.email || '',
                    row.dataset.id || ''
                ].join(' ');

                const matchesSearch =
                    !query || haystack.includes(query);

                let matchesFilter = true;

                if (activeFilter === 'verified') {
                    matchesFilter =
                        row.dataset.verified === '1';
                }

                if (activeFilter === 'pending') {
                    matchesFilter =
                        row.dataset.verified === '0';
                }

                if (activeFilter === 'admin') {
                    matchesFilter =
                        row.dataset.admin === '1';
                }

                row.style.display =
                    matchesSearch && matchesFilter
                        ? ''
                        : 'none';

            });

        }


        if (searchInput) {

            searchInput.addEventListener(
                'input',
                applyUsersFilter
            );

        }


        filterButtons.forEach(function (button) {

            button.addEventListener('click', function () {

                activeFilter =
                    button.dataset.filter || 'all';

                filterButtons.forEach(function (item) {
                    item.classList.remove('active');
                });

                button.classList.add('active');

                applyUsersFilter();

            });

        });

    });
</script>
@endpush
