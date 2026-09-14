@extends('layouts.admin-layout')

@section('title', 'Admin Dashboard')

@section('content')
    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Gebruikers</div>
            <div class="stat-num">{{ count($users) }}</div>
            <div class="stat-foot">Actieve accounts</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Website</div>
            <div class="stat-num">01</div>
            <div class="stat-foot">Online versie</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Laatst</div>
            <div class="stat-num">24h</div>
            <div class="stat-foot">Bijgewerkt</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Status</div>
            <div class="stat-num">Live</div>
            <div class="stat-foot">Systeem goed</div>
        </div>
    </section>

    <section class="main-panel">
        <div class="section-heading">
            <div>
                <h2>Gebruikersbeheer</h2>
                <span>Overzicht van alle accounts</span>
            </div>
            <a class="button" href="{{ route('users.create') }}">Gebruiker toevoegen</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Naam</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>
                            <div class="user-name">
                                <span class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                {{ $user->name }}
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td><span class="badge">Admin</span></td>
                        <td><span class="badge">Actief</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">Nog geen gebruikers gevonden.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
