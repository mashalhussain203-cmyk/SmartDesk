@extends('layouts.admin-layout')

@section('title', 'Gebruikers')

@section('content')
<section class="main-panel">
    <div class="section-heading">
        <div>
            <h2>Gebruikerslijst</h2>
            <span>Alle geregistreerde gebruikers</span>
        </div>
        <a class="button" href="{{ route('users.create') }}">+ Nieuwe gebruiker</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Naam</th>
                <th>Email</th>
                <th>Account</th>
                <th>Toegevoegd</th>
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
                    <td><span class="badge">Gebruiker</span></td>
                    <td>{{ $user->created_at->format('d-m-Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Er zijn nog geen gebruikers.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</section>
@endsection
