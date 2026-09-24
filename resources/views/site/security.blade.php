@extends('layouts.site-layout')

@section('title', 'Mashal Studio | Accountbeveiliging')

@push('styles')
<style>
    .security-page {
        min-height: 74vh;
        padding: 86px 0 110px;
        background:
            radial-gradient(circle at 10% 4%, rgba(215, 164, 95, .09), transparent 24rem),
            radial-gradient(circle at 92% 18%, rgba(255, 255, 255, .025), transparent 28rem),
            #08090b;
    }

    .security-shell {
        width: min(100% - 48px, 1180px);
        margin-inline: auto;
    }

    .security-hero {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: end;
        gap: 34px;
        margin-bottom: 34px;
    }

    .security-kicker {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
        color: #d7a45f;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .2em;
        text-transform: uppercase;
    }

    .security-kicker::before {
        content: "";
        width: 30px;
        height: 1px;
        background: #d7a45f;
    }

    .security-title {
        margin: 0;
        color: #fff;
        font-size: clamp(44px, 6vw, 78px);
        line-height: .95;
        letter-spacing: -.06em;
        font-weight: 950;
    }

    .security-title span {
        color: #efc985;
    }

    .security-copy {
        max-width: 760px;
        margin: 18px 0 0;
        color: #858b92;
        font-size: 14px;
        line-height: 1.85;
    }

    .security-summary {
        min-width: 230px;
        padding: 18px 20px;
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: 18px;
        background: rgba(255, 255, 255, .03);
    }

    .security-summary small,
    .security-stat span,
    .login-detail small {
        display: block;
        color: #777d84;
        font-weight: 850;
        text-transform: uppercase;
    }

    .security-summary small {
        font-size: 9px;
        letter-spacing: .13em;
    }

    .security-summary strong {
        display: block;
        margin-top: 6px;
        color: #fff;
        font-size: 24px;
        line-height: 1;
    }

    .security-summary em {
        display: block;
        margin-top: 8px;
        color: #a1a6ac;
        font-size: 11px;
        font-style: normal;
        line-height: 1.55;
    }

    .security-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 22px;
    }

    .security-stat {
        padding: 16px 17px;
        border: 1px solid rgba(255, 255, 255, .07);
        border-radius: 17px;
        background: linear-gradient(145deg, rgba(255, 255, 255, .035), rgba(255, 255, 255, .012));
    }

    .security-stat span {
        font-size: 8px;
        letter-spacing: .13em;
    }

    .security-stat strong {
        display: block;
        margin-top: 8px;
        color: #fff;
        font-size: 23px;
        line-height: 1;
    }

    .security-stat small {
        display: block;
        margin-top: 6px;
        color: #777d84;
        font-size: 9px;
        line-height: 1.5;
    }

    .security-flash {
        margin-bottom: 22px;
        padding: 14px 16px;
        border: 1px solid rgba(124, 203, 153, .22);
        border-radius: 14px;
        background: rgba(124, 203, 153, .07);
        color: #bce8cb;
        font-size: 13px;
    }

    .security-toolbar {
        margin-bottom: 18px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        border: 1px solid rgba(255, 255, 255, .07);
        border-radius: 16px;
        background: rgba(255, 255, 255, .02);
    }

    .security-toolbar-copy strong {
        display: block;
        color: #e6e6e3;
        font-size: 12px;
    }

    .security-toolbar-copy span {
        display: block;
        margin-top: 3px;
        color: #6e747b;
        font-size: 10px;
        line-height: 1.55;
    }

    .security-clear {
        min-height: 39px;
        padding: 0 14px;
        border: 1px solid rgba(239, 143, 143, .18);
        border-radius: 999px;
        background: rgba(239, 143, 143, .05);
        color: #eeb0b0;
        cursor: pointer;
        font-size: 10px;
        font-weight: 850;
        transition:
            border-color .2s ease,
            background .2s ease,
            transform .2s ease;
    }

    .security-clear:hover {
        transform: translateY(-1px);
        border-color: rgba(239, 143, 143, .34);
        background: rgba(239, 143, 143, .09);
    }

    .login-list {
        display: grid;
        gap: 14px;
    }

    .login-card {
        position: relative;
        display: grid;
        grid-template-columns: 64px minmax(0, 1fr) auto;
        align-items: start;
        gap: 18px;
        padding: 20px;
        border: 1px solid rgba(255, 255, 255, .075);
        border-radius: 22px;
        background: linear-gradient(145deg, rgba(255, 255, 255, .038), rgba(255, 255, 255, .014));
        box-shadow: 0 14px 36px rgba(0, 0, 0, .16);
    }

    .login-card.is-new-device {
        border-color: rgba(215, 164, 95, .25);
        background: linear-gradient(145deg, rgba(215, 164, 95, .075), rgba(255, 255, 255, .014));
    }

    .login-icon {
        width: 64px;
        height: 64px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(215, 164, 95, .19);
        border-radius: 20px;
        background: rgba(215, 164, 95, .065);
        color: #efc985;
        font-size: 25px;
    }

    .login-main {
        min-width: 0;
    }

    .login-card h2 {
        margin: 0;
        color: #fff;
        font-size: 18px;
        letter-spacing: -.025em;
    }

    .login-meta {
        margin-top: 8px;
        display: flex;
        gap: 8px 12px;
        flex-wrap: wrap;
        color: #858b92;
        font-size: 11px;
        line-height: 1.5;
    }

    .login-meta span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .login-meta span::before {
        content: "";
        width: 3px;
        height: 3px;
        flex: 0 0 3px;
        border-radius: 50%;
        background: #6d7177;
    }

    .login-meta span:first-child::before {
        display: none;
    }

    .login-details {
        margin-top: 16px;
        padding-top: 15px;
        border-top: 1px solid rgba(255, 255, 255, .055);
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .login-detail {
        min-width: 0;
        padding: 11px 12px;
        border: 1px solid rgba(255, 255, 255, .055);
        border-radius: 13px;
        background: rgba(255, 255, 255, .018);
    }

    .login-detail small {
        margin-bottom: 5px;
        color: #62686f;
        font-size: 7px;
        letter-spacing: .11em;
    }

    .login-detail strong {
        display: block;
        overflow-wrap: anywhere;
        color: #c9cbc8;
        font-size: 10px;
        line-height: 1.55;
        font-weight: 780;
    }

    .login-detail strong.is-gold {
        color: #efc985;
    }

    .login-detail strong.is-success {
        color: #9fe0ba;
    }

    .login-detail strong.is-error {
        color: #efaaaa;
    }

    .login-detail-wide {
        grid-column: span 2;
    }

    .login-side {
        min-width: 170px;
        text-align: right;
    }

    .login-provider,
    .new-device-badge {
        display: inline-flex;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 850;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .login-provider {
        border: 1px solid rgba(255, 255, 255, .08);
        background: rgba(255, 255, 255, .025);
        color: #d7d8d5;
    }

    .new-device-badge {
        margin-left: 6px;
        border: 1px solid rgba(215, 164, 95, .24);
        background: rgba(215, 164, 95, .08);
        color: #efc985;
        font-weight: 900;
    }

    .login-date {
        display: block;
        margin-top: 9px;
        color: #8d939a;
        font-size: 10px;
        line-height: 1.55;
    }

    .login-date strong {
        display: block;
        color: #d6d7d4;
        font-size: 11px;
        font-weight: 850;
    }

    .login-date small {
        display: block;
        margin-top: 3px;
        color: #5f656c;
        font-size: 8px;
    }

    .security-empty {
        padding: 70px 24px;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, .07);
        border-radius: 24px;
        background: rgba(255, 255, 255, .02);
    }

    .security-empty-icon {
        width: 66px;
        height: 66px;
        margin: 0 auto 18px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: rgba(215, 164, 95, .07);
        color: #efc985;
        font-size: 25px;
    }

    .security-empty h2 {
        margin: 0;
        color: #fff;
        font-size: 24px;
    }

    .security-empty p {
        max-width: 580px;
        margin: 10px auto 0;
        color: #7e848b;
        font-size: 13px;
        line-height: 1.75;
    }

    .security-note {
        margin-top: 24px;
        padding: 18px 20px;
        border: 1px solid rgba(255, 255, 255, .065);
        border-radius: 18px;
        background: rgba(255, 255, 255, .018);
        color: #747a81;
        font-size: 11px;
        line-height: 1.75;
    }

    .security-note strong {
        color: #c7c8c5;
    }

    @media (max-width: 1050px) {
        .security-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .login-details {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 850px) {
        .security-hero {
            grid-template-columns: 1fr;
        }

        .security-summary {
            width: fit-content;
        }

        .login-card {
            grid-template-columns: 56px minmax(0, 1fr);
        }

        .login-icon {
            width: 56px;
            height: 56px;
            border-radius: 17px;
        }

        .login-side {
            grid-column: 1 / -1;
            padding-top: 12px;
            border-top: 1px solid rgba(255, 255, 255, .06);
            text-align: left;
        }
    }

    @media (max-width: 620px) {
        .security-page {
            padding: 62px 0 82px;
        }

        .security-shell {
            width: min(100% - 30px, 1180px);
        }

        .security-title {
            font-size: clamp(42px, 13vw, 60px);
        }

        .security-stats {
            grid-template-columns: 1fr 1fr;
        }

        .login-card {
            padding: 16px;
            gap: 14px;
        }

        .login-details {
            grid-template-columns: 1fr;
        }

        .login-detail-wide {
            grid-column: auto;
        }

        .security-toolbar {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
<section class="security-page">
    <div class="security-shell">
        @include('partials.passkeys', ['passkeyMode' => 'manage'])
        <header class="security-hero">
            <div>
                <span class="security-kicker">Mashal Studio Security</span>

                <h1 class="security-title">
                    Login <span>beveiliging.</span>
                </h1>

                <p class="security-copy">
                    Bekijk je recente logins met apparaat, browser, besturingssysteem,
                    loginmethode, IP-adres, geschatte IP-locatie, browser-timezone en,
                    wanneer je daarvoor toestemming hebt gegeven, de precieze
                    browserlocatie met GPS-nauwkeurigheid.
                </p>
            </div>

            <aside class="security-summary">
                <small>Opgeslagen loginactiviteit</small>

                <strong>
                    {{ $totalActivityCount ?? $activities->count() }}
                </strong>

                <em>
                    {{ $newDeviceCount ?? 0 }}
                    {{ ($newDeviceCount ?? 0) === 1 ? 'nieuw apparaat' : 'nieuwe apparaten' }}
                    in de huidige lijst.
                </em>
            </aside>
        </header>

        <div class="security-stats">
            <div class="security-stat">
                <span>Bekende apparaten</span>
                <strong>{{ $knownDeviceCount ?? 0 }}</strong>
                <small>Logins die niet als nieuw apparaat zijn gemarkeerd.</small>
            </div>

            <div class="security-stat">
                <span>Precieze locaties</span>
                <strong>{{ $preciseLocationCount ?? 0 }}</strong>
                <small>GPS/browserlocaties die met toestemming zijn opgeslagen.</small>
            </div>

            <div class="security-stat">
                <span>Beveiligingsmails</span>
                <strong>{{ $notificationSentCount ?? 0 }}</strong>
                <small>Succesvol via Brevo verzonden.</small>
            </div>

            <div class="security-stat">
                <span>Mailfouten</span>
                <strong>{{ $notificationFailedCount ?? 0 }}</strong>
                <small>Mislukte beveiligingsmeldingen in deze lijst.</small>
            </div>
        </div>

        @if (session('success'))
            <div class="security-flash">
                {{ session('success') }}
            </div>
        @endif

        <div class="security-toolbar">
            <div class="security-toolbar-copy">
                <strong>
                    Loginactiviteit van {{ auth()->user()?->name ?? 'je account' }}
                </strong>

                <span>
                    Maximaal {{ $historyLimit ?? 50 }} recente regels worden hier getoond.
                    IP-locatie is een schatting. GPS verschijnt alleen wanneer je browser
                    locatie heeft toegestaan.
                </span>
            </div>

            @if ($activities->isNotEmpty())
                <form
                    method="POST"
                    action="{{ route('security.history.destroy') }}"
                    onsubmit="return confirm('Weet je zeker dat je jouw loginhistorie wilt verwijderen?');"
                >
                    @csrf
                    @method('DELETE')

                    <button
                        class="security-clear"
                        type="submit"
                    >
                        Geschiedenis wissen
                    </button>
                </form>
            @endif
        </div>

        @if ($activities->isEmpty())
            <div class="security-empty">
                <div class="security-empty-icon" aria-hidden="true">◇</div>

                <h2>Nog geen loginhistorie</h2>

                <p>
                    Na je volgende succesvolle login verschijnt hier het apparaat,
                    de browser, het besturingssysteem, de loginmethode, het IP-adres,
                    de lokale tijd en beschikbare locatie-informatie.
                </p>
            </div>
        @else
            <div class="login-list">
                @foreach ($activities as $activity)
                    @php
                        $hasPreciseLocation = $activity->hasPreciseLocation();
                        $coordinates = $activity->coordinatesLabel();
                        $accuracy = $activity->accuracyLabel();
                        $localTime = $activity->localLoginTimeLabel();
                        $effectiveTimezone = $activity->timezoneLabel();
                        $locationSource = $activity->locationSourceLabel();
                        $mailStatus = $activity->notificationStatusLabel();

                        $mailClass = $activity->notificationWasSent()
                            ? 'is-success'
                            : ($activity->notificationFailed() ? 'is-error' : '');

                        $permissionLabel = match (strtolower((string) $activity->location_permission)) {
                            'granted' => 'Toegestaan',
                            'denied' => 'Geweigerd',
                            'prompt' => 'Nog niet gekozen',
                            'unsupported' => 'Niet ondersteund',
                            'unavailable' => 'Niet beschikbaar',
                            default => 'Onbekend',
                        };

                        $deviceSymbol = match (strtolower((string) $activity->device_type)) {
                            'mobile' => '◫',
                            'tablet' => '▣',
                            'bot' => '⟐',
                            default => '◇',
                        };
                    @endphp

                    <article class="login-card {{ $activity->is_new_device ? 'is-new-device' : '' }}">
                        <div class="login-icon" aria-hidden="true">
                            {{ $deviceSymbol }}
                        </div>

                        <div class="login-main">
                            <h2>{{ $activity->deviceLabel() }}</h2>

                            <div class="login-meta">
                                <span>{{ $activity->deviceTypeLabel() }}</span>
                                <span>{{ $activity->browserLabel() }}</span>
                                <span>{{ $activity->operatingSystemLabel() }}</span>
                                <span>{{ $activity->locationLabel() }}</span>
                            </div>

                            <div class="login-details">
                                <div class="login-detail">
                                    <small>IP-adres</small>
                                    <strong>{{ $activity->ip_address ?: 'Onbekend' }}</strong>
                                </div>

                                <div class="login-detail">
                                    <small>Stad</small>
                                    <strong>{{ $activity->city ?: 'Onbekend' }}</strong>
                                </div>

                                <div class="login-detail">
                                    <small>Regio</small>
                                    <strong>{{ $activity->region ?: 'Onbekend' }}</strong>
                                </div>

                                <div class="login-detail">
                                    <small>Land</small>
                                    <strong>
                                        {{ $activity->country ?: 'Onbekend' }}
                                        @if ($activity->country_code)
                                            ({{ strtoupper($activity->country_code) }})
                                        @endif
                                    </strong>
                                </div>

                                <div class="login-detail">
                                    <small>Locatiebron</small>
                                    <strong>{{ $locationSource }}</strong>
                                </div>

                                <div class="login-detail">
                                    <small>Locatietoestemming</small>
                                    <strong class="{{ strtolower((string) $activity->location_permission) === 'granted' ? 'is-success' : '' }}">
                                        {{ $permissionLabel }}
                                    </strong>
                                </div>

                                <div class="login-detail login-detail-wide">
                                    <small>GPS-coördinaten</small>
                                    <strong class="{{ $hasPreciseLocation ? 'is-gold' : '' }}">
                                        {{ $coordinates ?: 'Niet beschikbaar' }}
                                    </strong>
                                </div>

                                <div class="login-detail">
                                    <small>GPS-nauwkeurigheid</small>
                                    <strong>{{ $accuracy ?: 'Niet beschikbaar' }}</strong>
                                </div>

                                <div class="login-detail">
                                    <small>Browser-timezone</small>
                                    <strong>{{ $activity->browser_timezone ?: 'Niet meegestuurd' }}</strong>
                                </div>

                                <div class="login-detail">
                                    <small>IP-timezone</small>
                                    <strong>{{ $activity->timezone ?: 'Onbekend' }}</strong>
                                </div>

                                <div class="login-detail">
                                    <small>Gebruikte timezone</small>
                                    <strong class="is-gold">{{ $effectiveTimezone }}</strong>
                                </div>

                                <div class="login-detail">
                                    <small>Loginmethode</small>
                                    <strong>{{ $activity->providerLabel() }}</strong>
                                </div>

                                <div class="login-detail">
                                    <small>Apparaatstatus</small>
                                    <strong class="{{ $activity->is_new_device ? 'is-gold' : '' }}">
                                        {{ $activity->newDeviceLabel() }}
                                    </strong>
                                </div>

                                <div class="login-detail">
                                    <small>Onthouden</small>
                                    <strong>{{ $activity->remember ? 'Ja' : 'Nee' }}</strong>
                                </div>

                                <div class="login-detail login-detail-wide">
                                    <small>Beveiligingsmail</small>
                                    <strong class="{{ $mailClass }}">
                                        {{ $mailStatus }}
                                    </strong>
                                </div>

                                <div class="login-detail">
                                    <small>Mail verzonden</small>
                                    <strong>
                                        @if ($activity->notification_sent_at)
                                            {{ $activity->notification_sent_at->copy()->timezone($effectiveTimezone)->format('d-m-Y H:i') }}
                                        @else
                                            Niet verzonden
                                        @endif
                                    </strong>
                                </div>

                                <div class="login-detail">
                                    <small>Mailfout</small>
                                    <strong>
                                        @if ($activity->notification_failed_at)
                                            {{ $activity->notification_failed_at->copy()->timezone($effectiveTimezone)->format('d-m-Y H:i') }}
                                        @else
                                            Geen fout
                                        @endif
                                    </strong>
                                </div>

                                <div class="login-detail login-detail-wide">
                                    <small>User-Agent</small>
                                    <strong>{{ $activity->user_agent ?: 'Niet beschikbaar' }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="login-side">
                            <span class="login-provider">
                                {{ $activity->providerLabel() }}
                            </span>

                            @if ($activity->is_new_device)
                                <span class="new-device-badge">
                                    Nieuw apparaat
                                </span>
                            @endif

                            <time
                                class="login-date"
                                @if ($activity->logged_in_at)
                                    datetime="{{ $activity->logged_in_at->toIso8601String() }}"
                                @endif
                            >
                                <strong>{{ $localTime }}</strong>
                                <small>{{ $effectiveTimezone }}</small>

                                @if ($activity->logged_in_at)
                                    <small>
                                        UTC:
                                        {{ $activity->logged_in_at->copy()->timezone('UTC')->format('d-m-Y H:i') }}
                                    </small>
                                @endif
                            </time>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        <div class="security-note">
            <strong>Privacy en nauwkeurigheid.</strong>
            Een IP-locatie is een technische schatting en kan bijvoorbeeld de
            locatie van je internetprovider, VPN of mobiele provider tonen.
            Precieze GPS/browserlocatie wordt alleen opgeslagen wanneer je daar
            in de browser toestemming voor geeft. Browsers geven daarnaast niet
            altijd het exacte model van een telefoon vrij. Deze gegevens worden
            uitsluitend als beveiligingsinformatie bij je account getoond en
            worden niet als zelfstandig authenticatiemiddel gebruikt.
        </div>
    </div>
</section>
@endsection
