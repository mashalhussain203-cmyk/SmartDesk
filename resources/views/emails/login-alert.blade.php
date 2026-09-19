<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <meta
        name="color-scheme"
        content="dark"
    >
    <meta
        name="supported-color-schemes"
        content="dark"
    >
    <title>
        Nieuwe login - Mashal Automotive
    </title>
</head>

<body
    style="
        margin: 0;
        padding: 0;
        background: #08090b;
        color: #f3f1ed;
        font-family: Arial, Helvetica, sans-serif;
        -webkit-text-size-adjust: 100%;
        -ms-text-size-adjust: 100%;
    "
>
    @php
        $deviceLabel = method_exists($activity, 'deviceLabel')
            ? $activity->deviceLabel()
            : ($activity->device ?: 'Onbekend apparaat');

        $deviceTypeLabel = method_exists($activity, 'deviceTypeLabel')
            ? $activity->deviceTypeLabel()
            : ($activity->device_type ?: 'Onbekend type');

        $browserLabel = method_exists($activity, 'browserLabel')
            ? $activity->browserLabel()
            : ($activity->browser ?: 'Onbekende browser');

        $operatingSystemLabel = method_exists($activity, 'operatingSystemLabel')
            ? $activity->operatingSystemLabel()
            : ($activity->operating_system ?: 'Onbekend besturingssysteem');

        $resolvedLocationLabel = $locationLabel
            ?? (
                method_exists($activity, 'locationLabel')
                    ? $activity->locationLabel()
                    : 'Onbekende locatie'
            );

        $resolvedProviderLabel = $providerLabel
            ?? (
                method_exists($activity, 'providerLabel')
                    ? $activity->providerLabel()
                    : ($activity->login_provider ?: 'Onbekend')
            );

        $resolvedTimezone = method_exists($activity, 'timezoneLabel')
            ? $activity->timezoneLabel()
            : (
                $activity->browser_timezone
                    ?: (
                        $activity->timezone
                            ?: config('app.timezone', 'Europe/Amsterdam')
                    )
            );

        $resolvedDisplayTime = $displayTime
            ?? (
                method_exists($activity, 'localLoginTimeLabel')
                    ? $activity->localLoginTimeLabel()
                    : optional($activity->logged_in_at)->timezone($resolvedTimezone)->format('d-m-Y H:i')
            );

        $hasPreciseLocation = method_exists($activity, 'hasPreciseLocation')
            ? $activity->hasPreciseLocation()
            : (
                $activity->latitude !== null &&
                $activity->longitude !== null
            );

        $coordinatesLabel = method_exists($activity, 'coordinatesLabel')
            ? $activity->coordinatesLabel()
            : (
                $hasPreciseLocation
                    ? $activity->latitude . ', ' . $activity->longitude
                    : null
            );

        $accuracyLabel = method_exists($activity, 'accuracyLabel')
            ? $activity->accuracyLabel()
            : (
                $activity->location_accuracy !== null
                    ? '± ' . round((float) $activity->location_accuracy) . ' meter'
                    : null
            );

        $locationSourceLabel = method_exists($activity, 'locationSourceLabel')
            ? $activity->locationSourceLabel()
            : ($activity->location_source ?: 'Onbekend');

        $permissionLabel = match (strtolower((string) $activity->location_permission)) {
            'granted' => 'Toegestaan',
            'denied' => 'Geweigerd',
            'prompt' => 'Nog niet gekozen',
            'unsupported' => 'Niet ondersteund',
            'unavailable' => 'Niet beschikbaar',
            default => 'Onbekend',
        };

        $newDeviceLabel = method_exists($activity, 'newDeviceLabel')
            ? $activity->newDeviceLabel()
            : ($activity->is_new_device ? 'Nieuw apparaat' : 'Bekend apparaat');

        $utcTime = $activity->logged_in_at
            ? $activity->logged_in_at->copy()->timezone('UTC')->format('d-m-Y H:i')
            : null;
    @endphp

    {{-- Preheader --}}
    <div
        style="
            display: none;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            color: transparent;
            mso-hide: all;
        "
    >
        Er is een nieuwe login op je Mashal Automotive-account geregistreerd.
    </div>

    <table
        role="presentation"
        width="100%"
        cellspacing="0"
        cellpadding="0"
        border="0"
        style="
            width: 100%;
            margin: 0;
            padding: 0;
            background: #08090b;
            border-collapse: collapse;
        "
    >
        <tr>
            <td
                align="center"
                style="
                    padding: 34px 16px;
                "
            >
                <table
                    role="presentation"
                    width="100%"
                    cellspacing="0"
                    cellpadding="0"
                    border="0"
                    style="
                        width: 100%;
                        max-width: 680px;
                        border-collapse: separate;
                        border-spacing: 0;
                        border: 1px solid #25272b;
                        border-radius: 24px;
                        background: #101215;
                    "
                >
                    {{-- Header --}}
                    <tr>
                        <td
                            style="
                                padding: 34px 34px 22px;
                                border-bottom: 1px solid #25272b;
                                border-radius: 24px 24px 0 0;
                                background: #111316;
                            "
                        >
                            <div
                                style="
                                    margin-bottom: 12px;
                                    color: #d7a45f;
                                    font-size: 11px;
                                    font-weight: 700;
                                    letter-spacing: 2px;
                                    text-transform: uppercase;
                                "
                            >
                                Mashal Automotive Security
                            </div>

                            <h1
                                style="
                                    margin: 0;
                                    color: #ffffff;
                                    font-size: 30px;
                                    line-height: 1.15;
                                "
                            >
                                Nieuwe login gedetecteerd
                            </h1>

                            <p
                                style="
                                    margin: 14px 0 0;
                                    color: #9ca0a6;
                                    font-size: 14px;
                                    line-height: 1.7;
                                "
                            >
                                Hallo {{ $user->name }},
                                er is een succesvolle login op je
                                Mashal Automotive-account geregistreerd.
                            </p>
                        </td>
                    </tr>

                    {{-- New device warning --}}
                    @if ($activity->is_new_device)
                        <tr>
                            <td
                                style="
                                    padding: 18px 34px 0;
                                "
                            >
                                <div
                                    style="
                                        padding: 14px 16px;
                                        border: 1px solid #5a4225;
                                        border-radius: 14px;
                                        background: #1b160f;
                                        color: #efc985;
                                        font-size: 13px;
                                        line-height: 1.55;
                                    "
                                >
                                    <strong>
                                        Nieuw apparaat
                                    </strong>
                                    <br>
                                    Dit apparaat is nog niet eerder in je recente
                                    Mashal-loginhistorie gezien.
                                </div>
                            </td>
                        </tr>
                    @endif

                    {{-- Login information --}}
                    <tr>
                        <td
                            style="
                                padding: 28px 34px 12px;
                            "
                        >
                            <div
                                style="
                                    margin-bottom: 12px;
                                    color: #d7a45f;
                                    font-size: 10px;
                                    font-weight: 700;
                                    letter-spacing: 1.6px;
                                    text-transform: uppercase;
                                "
                            >
                                Logininformatie
                            </div>

                            <table
                                role="presentation"
                                width="100%"
                                cellspacing="0"
                                cellpadding="0"
                                border="0"
                                style="
                                    width: 100%;
                                    border-collapse: collapse;
                                "
                            >
                                <tr>
                                    <td style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #72777e; font-size: 12px;">
                                        Apparaat
                                    </td>
                                    <td align="right" style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #ffffff; font-size: 13px; font-weight: 700;">
                                        {{ $deviceLabel }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #72777e; font-size: 12px;">
                                        Apparaattype
                                    </td>
                                    <td align="right" style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #ffffff; font-size: 13px; font-weight: 700;">
                                        {{ $deviceTypeLabel }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #72777e; font-size: 12px;">
                                        Browser
                                    </td>
                                    <td align="right" style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #ffffff; font-size: 13px; font-weight: 700;">
                                        {{ $browserLabel }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #72777e; font-size: 12px;">
                                        Besturingssysteem
                                    </td>
                                    <td align="right" style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #ffffff; font-size: 13px; font-weight: 700;">
                                        {{ $operatingSystemLabel }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #72777e; font-size: 12px;">
                                        Loginmethode
                                    </td>
                                    <td align="right" style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #ffffff; font-size: 13px; font-weight: 700;">
                                        {{ $resolvedProviderLabel }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #72777e; font-size: 12px;">
                                        Apparaatstatus
                                    </td>
                                    <td align="right" style="padding: 12px 0; border-bottom: 1px solid #24262a; color: {{ $activity->is_new_device ? '#efc985' : '#ffffff' }}; font-size: 13px; font-weight: 700;">
                                        {{ $newDeviceLabel }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #72777e; font-size: 12px;">
                                        Tijdstip
                                    </td>
                                    <td align="right" style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #ffffff; font-size: 13px; font-weight: 700;">
                                        {{ $resolvedDisplayTime }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #72777e; font-size: 12px;">
                                        Gebruikte timezone
                                    </td>
                                    <td align="right" style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #efc985; font-size: 13px; font-weight: 700;">
                                        {{ $resolvedTimezone }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 12px 0 0; color: #72777e; font-size: 12px;">
                                        UTC-tijd
                                    </td>
                                    <td align="right" style="padding: 12px 0 0; color: #ffffff; font-size: 13px; font-weight: 700;">
                                        {{ $utcTime ?: 'Onbekend' }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Network and approximate location --}}
                    <tr>
                        <td
                            style="
                                padding: 20px 34px 12px;
                            "
                        >
                            <div
                                style="
                                    margin-bottom: 12px;
                                    color: #d7a45f;
                                    font-size: 10px;
                                    font-weight: 700;
                                    letter-spacing: 1.6px;
                                    text-transform: uppercase;
                                "
                            >
                                Netwerk en geschatte locatie
                            </div>

                            <table
                                role="presentation"
                                width="100%"
                                cellspacing="0"
                                cellpadding="0"
                                border="0"
                                style="
                                    width: 100%;
                                    border-collapse: collapse;
                                "
                            >
                                <tr>
                                    <td style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #72777e; font-size: 12px;">
                                        IP-adres
                                    </td>
                                    <td align="right" style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #ffffff; font-size: 13px; font-weight: 700;">
                                        {{ $activity->ip_address ?: 'Onbekend' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #72777e; font-size: 12px;">
                                        Geschatte locatie
                                    </td>
                                    <td align="right" style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #ffffff; font-size: 13px; font-weight: 700;">
                                        {{ $resolvedLocationLabel }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #72777e; font-size: 12px;">
                                        Stad
                                    </td>
                                    <td align="right" style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #ffffff; font-size: 13px; font-weight: 700;">
                                        {{ $activity->city ?: 'Onbekend' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #72777e; font-size: 12px;">
                                        Regio
                                    </td>
                                    <td align="right" style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #ffffff; font-size: 13px; font-weight: 700;">
                                        {{ $activity->region ?: 'Onbekend' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #72777e; font-size: 12px;">
                                        Land
                                    </td>
                                    <td align="right" style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #ffffff; font-size: 13px; font-weight: 700;">
                                        {{ $activity->country ?: 'Onbekend' }}
                                        @if ($activity->country_code)
                                            ({{ strtoupper($activity->country_code) }})
                                        @endif
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #72777e; font-size: 12px;">
                                        Locatiebron
                                    </td>
                                    <td align="right" style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #ffffff; font-size: 13px; font-weight: 700;">
                                        {{ $locationSourceLabel }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #72777e; font-size: 12px;">
                                        Browser-timezone
                                    </td>
                                    <td align="right" style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #ffffff; font-size: 13px; font-weight: 700;">
                                        {{ $activity->browser_timezone ?: 'Niet meegestuurd' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 12px 0 0; color: #72777e; font-size: 12px;">
                                        IP-timezone
                                    </td>
                                    <td align="right" style="padding: 12px 0 0; color: #ffffff; font-size: 13px; font-weight: 700;">
                                        {{ $activity->timezone ?: 'Onbekend' }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Precise browser location --}}
                    <tr>
                        <td
                            style="
                                padding: 20px 34px 12px;
                            "
                        >
                            <div
                                style="
                                    margin-bottom: 12px;
                                    color: #d7a45f;
                                    font-size: 10px;
                                    font-weight: 700;
                                    letter-spacing: 1.6px;
                                    text-transform: uppercase;
                                "
                            >
                                Precieze browserlocatie
                            </div>

                            <table
                                role="presentation"
                                width="100%"
                                cellspacing="0"
                                cellpadding="0"
                                border="0"
                                style="
                                    width: 100%;
                                    border-collapse: collapse;
                                "
                            >
                                <tr>
                                    <td style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #72777e; font-size: 12px;">
                                        Locatietoestemming
                                    </td>
                                    <td align="right" style="padding: 12px 0; border-bottom: 1px solid #24262a; color: {{ strtolower((string) $activity->location_permission) === 'granted' ? '#9fe0ba' : '#ffffff' }}; font-size: 13px; font-weight: 700;">
                                        {{ $permissionLabel }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #72777e; font-size: 12px;">
                                        GPS-coördinaten
                                    </td>
                                    <td align="right" style="padding: 12px 0; border-bottom: 1px solid #24262a; color: {{ $hasPreciseLocation ? '#efc985' : '#ffffff' }}; font-size: 13px; font-weight: 700;">
                                        {{ $coordinatesLabel ?: 'Niet beschikbaar' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #72777e; font-size: 12px;">
                                        GPS-nauwkeurigheid
                                    </td>
                                    <td align="right" style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #ffffff; font-size: 13px; font-weight: 700;">
                                        {{ $accuracyLabel ?: 'Niet beschikbaar' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 12px 0 0; color: #72777e; font-size: 12px;">
                                        Vastgelegd op
                                    </td>
                                    <td align="right" style="padding: 12px 0 0; color: #ffffff; font-size: 13px; font-weight: 700;">
                                        @if ($activity->precise_location_captured_at)
                                            {{ $activity->precise_location_captured_at->copy()->timezone($resolvedTimezone)->format('d-m-Y H:i') }}
                                        @else
                                            Niet beschikbaar
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Technical detail --}}
                    <tr>
                        <td
                            style="
                                padding: 20px 34px 12px;
                            "
                        >
                            <div
                                style="
                                    margin-bottom: 12px;
                                    color: #d7a45f;
                                    font-size: 10px;
                                    font-weight: 700;
                                    letter-spacing: 1.6px;
                                    text-transform: uppercase;
                                "
                            >
                                Technische informatie
                            </div>

                            <table
                                role="presentation"
                                width="100%"
                                cellspacing="0"
                                cellpadding="0"
                                border="0"
                                style="
                                    width: 100%;
                                    border-collapse: collapse;
                                "
                            >
                                <tr>
                                    <td style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #72777e; font-size: 12px;">
                                        Onthouden
                                    </td>
                                    <td align="right" style="padding: 12px 0; border-bottom: 1px solid #24262a; color: #ffffff; font-size: 13px; font-weight: 700;">
                                        {{ $activity->remember ? 'Ja' : 'Nee' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 12px 0 0; color: #72777e; font-size: 12px; vertical-align: top;">
                                        User-Agent
                                    </td>
                                    <td align="right" style="padding: 12px 0 0 18px; color: #ffffff; font-size: 11px; line-height: 1.55; overflow-wrap: anywhere;">
                                        {{ $activity->user_agent ?: 'Niet beschikbaar' }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- CTA --}}
                    <tr>
                        <td
                            style="
                                padding: 24px 34px 30px;
                            "
                        >
                            <a
                                href="{{ $securityUrl }}"
                                style="
                                    display: inline-block;
                                    padding: 14px 20px;
                                    border-radius: 999px;
                                    background: #d7a45f;
                                    color: #17120d;
                                    font-size: 13px;
                                    font-weight: 700;
                                    text-decoration: none;
                                "
                            >
                                Bekijk loginactiviteit
                            </a>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td
                            style="
                                padding: 24px 34px;
                                border-top: 1px solid #25272b;
                                border-radius: 0 0 24px 24px;
                                background: #0c0e10;
                            "
                        >
                            <p
                                style="
                                    margin: 0;
                                    color: #858a91;
                                    font-size: 12px;
                                    line-height: 1.7;
                                "
                            >
                                Was jij dit? Dan hoef je niets te doen.
                                Herken je deze login niet, wijzig dan direct
                                je wachtwoord en controleer je account.
                            </p>

                            <p
                                style="
                                    margin: 12px 0 0;
                                    color: #5f646b;
                                    font-size: 11px;
                                    line-height: 1.6;
                                "
                            >
                                De IP-locatie is een schatting en kan bijvoorbeeld
                                je internetprovider, VPN of mobiele provider tonen.
                                Precieze GPS-locatie wordt alleen opgeslagen wanneer
                                daarvoor in de browser toestemming is gegeven.
                                Browsers geven daarnaast niet altijd het exacte
                                model van een telefoon vrij.
                            </p>

                            <p
                                style="
                                    margin: 12px 0 0;
                                    color: #4f545a;
                                    font-size: 10px;
                                    line-height: 1.6;
                                "
                            >
                                Let op: deze e-mail kan gevoelige beveiligingsinformatie
                                bevatten. Deel hem niet met anderen.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
