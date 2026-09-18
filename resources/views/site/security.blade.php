@extends('layouts.site-layout')

@section('title', 'Mashal | Accountbeveiliging')

@push('styles')
<style>
    .security-page {
        min-height: 74vh;
        padding: 86px 0 110px;
        background:
            radial-gradient(
                circle at 10% 4%,
                rgba(215,164,95,.09),
                transparent 24rem
            ),
            radial-gradient(
                circle at 92% 18%,
                rgba(255,255,255,.025),
                transparent 28rem
            ),
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
        max-width: 700px;
        margin: 18px 0 0;
        color: #858b92;
        font-size: 14px;
        line-height: 1.85;
    }

    .security-summary {
        min-width: 210px;
        padding: 18px 20px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 18px;
        background: rgba(255,255,255,.03);
    }

    .security-summary small {
        display: block;
        color: #777d84;
        font-size: 9px;
        font-weight: 850;
        letter-spacing: .13em;
        text-transform: uppercase;
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
    }

    .security-flash {
        margin-bottom: 22px;
        padding: 14px 16px;
        border: 1px solid rgba(124,203,153,.22);
        border-radius: 14px;
        background: rgba(124,203,153,.07);
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
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 16px;
        background: rgba(255,255,255,.02);
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
    }

    .security-clear {
        min-height: 39px;
        padding: 0 14px;
        border: 1px solid rgba(239,143,143,.18);
        border-radius: 999px;
        background: rgba(239,143,143,.05);
        color: #eeb0b0;
        cursor: pointer;
        font-size: 10px;
        font-weight: 850;
    }

    .security-clear:hover {
        border-color: rgba(239,143,143,.34);
        background: rgba(239,143,143,.09);
    }

    .login-list {
        display: grid;
        gap: 14px;
    }

    .login-card {
        position: relative;
        display: grid;
        grid-template-columns: 64px minmax(0, 1fr) auto;
        align-items: center;
        gap: 18px;
        padding: 20px;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 22px;
        background:
            linear-gradient(
                145deg,
                rgba(255,255,255,.038),
                rgba(255,255,255,.014)
            );
        box-shadow: 0 14px 36px rgba(0,0,0,.16);
    }

    .login-card.is-new-device {
        border-color: rgba(215,164,95,.25);
        background:
            linear-gradient(
                145deg,
                rgba(215,164,95,.075),
                rgba(255,255,255,.014)
            );
    }

    .login-icon {
        width: 64px;
        height: 64px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(215,164,95,.19);
        border-radius: 20px;
        background: rgba(215,164,95,.065);
        color: #efc985;
        font-size: 25px;
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

    .login-side {
        min-width: 160px;
        text-align: right;
    }

    .login-provider {
        display: inline-flex;
        padding: 7px 10px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 999px;
        background: rgba(255,255,255,.025);
        color: #d7d8d5;
        font-size: 9px;
        font-weight: 850;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .new-device-badge {
        display: inline-flex;
        margin-left: 6px;
        padding: 7px 10px;
        border: 1px solid rgba(215,164,95,.24);
        border-radius: 999px;
        background: rgba(215,164,95,.08);
        color: #efc985;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .login-date {
        display: block;
        margin-top: 9px;
        color: #686e75;
        font-size: 10px;
    }

    .security-empty {
        padding: 70px 24px;
        text-align: center;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 24px;
        background: rgba(255,255,255,.02);
    }

    .security-empty div {
        width: 66px;
        height: 66px;
        margin: 0 auto 18px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: rgba(215,164,95,.07);
        color: #efc985;
        font-size: 25px;
    }

    .security-empty h2 {
        margin: 0;
        color: #fff;
        font-size: 24px;
    }

    .security-empty p {
        max-width: 520px;
        margin: 10px auto 0;
        color: #7e848b;
        font-size: 13px;
        line-height: 1.75;
    }

    .security-note {
        margin-top: 24px;
        padding: 18px 20px;
        border: 1px solid rgba(255,255,255,.065);
        border-radius: 18px;
        background: rgba(255,255,255,.018);
        color: #747a81;
        font-size: 11px;
        line-height: 1.75;
    }

    .security-note strong {
        color: #c7c8c5;
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
            border-top: 1px solid rgba(255,255,255,.06);
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

        .login-card {
            padding: 16px;
            gap: 14px;
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

        <header class="security-hero">
            <div>
                <span class="security-kicker">
                    Mashal Account Security
                </span>

                <h1 class="security-title">
                    Login
                    <span>beveiliging.</span>
                </h1>

                <p class="security-copy">
                    Bekijk vanaf welke apparaten op je account is ingelogd,
                    via welke loginmethode, met welk IP-adres en vanuit welke
                    geschatte locatie.
                </p>
            </div>

            <aside class="security-summary">
                <small>
                    Recente logins
                </small>

                <strong>
                    {{ $activities->count() }}
                </strong>

                <em>
                    {{ $newDeviceCount }}
                    {{ $newDeviceCount === 1 ? 'nieuw apparaat' : 'nieuwe apparaten' }}
                </em>
            </aside>
        </header>

        @if (session('success'))
            <div class="security-flash">
                {{ session('success') }}
            </div>
        @endif

        <div class="security-toolbar">
            <div class="security-toolbar-copy">
                <strong>
                    Loginactiviteit van {{ auth()->user()->name }}
                </strong>

                <span>
                    Geschatte locatiegegevens kunnen afwijken van je echte locatie.
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
                <div aria-hidden="true">
                    ◇
                </div>

                <h2>
                    Nog geen loginhistorie
                </h2>

                <p>
                    Na je volgende succesvolle login verschijnt hier het
                    apparaat, de browser, loginmethode, het IP-adres en de
                    geschatte locatie.
                </p>
            </div>
        @else
            <div class="login-list">
                @foreach ($activities as $activity)
                    <article
                        class="login-card {{ $activity->is_new_device ? 'is-new-device' : '' }}"
                    >
                        <div
                            class="login-icon"
                            aria-hidden="true"
                        >
                            @if ($activity->device_type === 'mobile')
                                ◫
                            @elseif ($activity->device_type === 'tablet')
                                ▣
                            @else
                                ◇
                            @endif
                        </div>

                        <div>
                            <h2>
                                {{ $activity->deviceLabel() }}
                            </h2>

                            <div class="login-meta">
                                <span>
                                    {{ $activity->browser ?: 'Onbekende browser' }}
                                </span>

                                <span>
                                    {{ $activity->operating_system ?: 'Onbekend OS' }}
                                </span>

                                <span>
                                    {{ $activity->locationLabel() }}
                                </span>

                                <span>
                                    IP:
                                    {{ $activity->ip_address ?: 'Onbekend' }}
                                </span>
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
                                datetime="{{ optional($activity->logged_in_at)->toIso8601String() }}"
                            >
                                {{ optional($activity->logged_in_at)->format('d-m-Y H:i') }}
                            </time>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        <div class="security-note">
            <strong>Privacy en nauwkeurigheid.</strong>
            Een IP-locatie is slechts een technische schatting en kan bijvoorbeeld
            de locatie van je internetprovider tonen. Browsers geven ook niet altijd
            het exacte model van een telefoon vrij. Deze gegevens worden daarom
            uitsluitend als accountinformatie getoond en niet gebruikt om automatisch
            toegang tot je account te blokkeren.
        </div>

    </div>
</section>

@endsection
