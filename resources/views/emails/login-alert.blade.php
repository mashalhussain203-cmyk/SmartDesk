@php
    $resolvedProviderLabel = $providerLabel
        ?? (
            method_exists($activity, 'providerLabel')
                ? $activity->providerLabel()
                : ucfirst((string) ($activity->login_provider ?? 'Onbekend'))
        );

    $resolvedLocationLabel = $locationLabel
        ?? (
            method_exists($activity, 'locationLabel')
                ? $activity->locationLabel()
                : collect([
                    $activity->city ?? null,
                    $activity->region ?? null,
                    $activity->country ?? null,
                ])->filter()->implode(', ')
        );

    $effectiveTimezone = method_exists($activity, 'effectiveTimezone')
        ? $activity->effectiveTimezone()
        : (
            $activity->browser_timezone
            ?? $activity->timezone
            ?? config('app.timezone', 'Europe/Amsterdam')
        );

    $resolvedDisplayTime = $displayTime
        ?? (
            method_exists($activity, 'localLoginTimeLabel')
                ? $activity->localLoginTimeLabel()
                : (
                    $activity->logged_in_at
                        ? $activity->logged_in_at
                            ->copy()
                            ->timezone($effectiveTimezone)
                            ->format('d-m-Y H:i')
                        : 'Onbekend'
                )
        );

    $utcTime = $activity->logged_in_at
        ? $activity->logged_in_at
            ->copy()
            ->timezone('UTC')
            ->format('d-m-Y H:i')
        : 'Onbekend';

    $deviceLabel = method_exists($activity, 'deviceLabel')
        ? $activity->deviceLabel()
        : ((string) ($activity->device ?? 'Onbekend apparaat'));

    $deviceTypeLabel = method_exists($activity, 'deviceTypeLabel')
        ? $activity->deviceTypeLabel()
        : match ((string) ($activity->device_type ?? 'unknown')) {
            'desktop' => 'Computer',
            'mobile' => 'Telefoon',
            'tablet' => 'Tablet',
            'bot' => 'Automatisch systeem',
            default => 'Onbekend',
        };

    $browserLabel = method_exists($activity, 'browserLabel')
        ? $activity->browserLabel()
        : ((string) ($activity->browser ?? 'Onbekende browser'));

    $operatingSystemLabel = method_exists($activity, 'operatingSystemLabel')
        ? $activity->operatingSystemLabel()
        : ((string) ($activity->operating_system ?? 'Onbekend besturingssysteem'));

    $newDeviceLabel = method_exists($activity, 'newDeviceLabel')
        ? $activity->newDeviceLabel()
        : (
            (bool) ($activity->is_new_device ?? false)
                ? 'Nieuw apparaat'
                : 'Bekend apparaat'
        );

    $timezoneLabel = method_exists($activity, 'timezoneLabel')
        ? $activity->timezoneLabel()
        : $effectiveTimezone;

    $locationSourceLabel = method_exists($activity, 'locationSourceLabel')
        ? $activity->locationSourceLabel()
        : match ((string) ($activity->location_source ?? 'unavailable')) {
            'ipapi.co' => 'IP-geolocatie',
            'ip' => 'IP-geolocatie',
            'disabled' => 'Uitgeschakeld',
            'privacy_disabled' => 'Niet opgeslagen wegens privacy-instelling',
            default => 'Niet beschikbaar',
        };

    $hasPreciseLocation = method_exists($activity, 'hasPreciseLocation')
        ? $activity->hasPreciseLocation()
        : (
            $activity->latitude !== null
            && $activity->longitude !== null
        );

    $coordinatesLabel = method_exists($activity, 'coordinatesLabel')
        ? $activity->coordinatesLabel()
        : (
            $hasPreciseLocation
                ? number_format((float) $activity->latitude, 6, '.', '')
                    . ', '
                    . number_format((float) $activity->longitude, 6, '.', '')
                : 'Niet beschikbaar'
        );

    $accuracyLabel = method_exists($activity, 'accuracyLabel')
        ? $activity->accuracyLabel()
        : (
            $activity->location_accuracy !== null
                ? '± ' . number_format((float) $activity->location_accuracy, 0, ',', '.') . ' meter'
                : 'Niet beschikbaar'
        );

    $locationPermission = match ((string) ($activity->location_permission ?? 'unknown')) {
        'granted' => 'Toegestaan',
        'denied' => 'Geweigerd',
        'prompt' => 'Nog niet gekozen',
        'unsupported' => 'Niet ondersteund',
        'unavailable' => 'Niet beschikbaar',
        default => 'Onbekend',
    };

    $preciseCapturedAt = $activity->precise_location_captured_at
        ? $activity->precise_location_captured_at
            ->copy()
            ->timezone($effectiveTimezone)
            ->format('d-m-Y H:i')
        : 'Niet beschikbaar';

    $countryLabel = trim(
        collect([
            $activity->country ?? null,
            filled($activity->country_code ?? null)
                ? '(' . strtoupper((string) $activity->country_code) . ')'
                : null,
        ])->filter()->implode(' ')
    );

    $rememberLabel = (bool) ($activity->remember ?? false)
        ? 'Ja'
        : 'Nee';

    $securityUrl = $securityUrl
        ?? route('security.index');

    $userAgent = filled($activity->user_agent ?? null)
        ? (string) $activity->user_agent
        : 'Niet opgeslagen';

    $emailName = filled($user->name ?? null)
        ? (string) $user->name
        : 'gebruiker';

    $safeLocationLabel = filled($resolvedLocationLabel)
        ? $resolvedLocationLabel
        : 'Niet beschikbaar';

    $ipAddress = filled($activity->ip_address ?? null)
        ? (string) $activity->ip_address
        : 'Niet opgeslagen';

    $browserTimezone = filled($activity->browser_timezone ?? null)
        ? (string) $activity->browser_timezone
        : 'Niet beschikbaar';

    $ipTimezone = filled($activity->timezone ?? null)
        ? (string) $activity->timezone
        : 'Niet beschikbaar';

    $cityLabel = filled($activity->city ?? null)
        ? (string) $activity->city
        : 'Niet beschikbaar';

    $regionLabel = filled($activity->region ?? null)
        ? (string) $activity->region
        : 'Niet beschikbaar';

    $countryDisplay = $countryLabel !== ''
        ? $countryLabel
        : 'Niet beschikbaar';

    $statusAccent = (bool) ($activity->is_new_device ?? false)
        ? '#d8a44e'
        : '#76b98d';
@endphp

<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >
    <meta
        name="color-scheme"
        content="light"
    >
    <meta
        name="supported-color-schemes"
        content="light"
    >

    <title>
        Nieuwe login gedetecteerd
    </title>

    <style>
        html,
        body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            min-width: 100% !important;
            background: #eceef1 !important;
            -webkit-text-size-adjust: 100% !important;
            -ms-text-size-adjust: 100% !important;
        }

        table {
            border-collapse: collapse !important;
            border-spacing: 0 !important;
        }

        img {
            border: 0;
            outline: none;
            text-decoration: none;
        }

        a {
            text-decoration: none;
        }

        @media only screen and (max-width: 620px) {
            .page-pad {
                padding-left: 10px !important;
                padding-right: 10px !important;
            }

            .content-pad {
                padding-left: 20px !important;
                padding-right: 20px !important;
            }

            .hero-title {
                font-size: 30px !important;
                line-height: 1.12 !important;
            }

            .hero-copy {
                font-size: 16px !important;
                line-height: 1.65 !important;
            }

            .section-title {
                font-size: 13px !important;
                letter-spacing: 2px !important;
            }

            .detail-label {
                font-size: 11px !important;
            }

            .detail-value {
                font-size: 17px !important;
            }

            .technical-value {
                font-size: 11px !important;
            }

            .cta {
                display: block !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }
        }
    </style>
</head>

<body
    style="
        margin: 0;
        padding: 0;
        width: 100%;
        min-width: 100%;
        background: #eceef1;
        font-family: Arial, Helvetica, sans-serif;
        color: #17191d;
    "
>
    <!-- Preheader -->
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
        Nieuwe succesvolle login op je Mashal Automotive-account via
        {{ $resolvedProviderLabel }}.
    </div>

    <table
        role="presentation"
        width="100%"
        cellpadding="0"
        cellspacing="0"
        border="0"
        bgcolor="#eceef1"
        style="
            width: 100%;
            background: #eceef1;
        "
    >
        <tr>
            <td
                align="center"
                class="page-pad"
                style="
                    padding: 26px 12px;
                "
            >
                <table
                    role="presentation"
                    width="680"
                    cellpadding="0"
                    cellspacing="0"
                    border="0"
                    bgcolor="#ffffff"
                    style="
                        width: 100%;
                        max-width: 680px;
                        background: #ffffff;
                        border: 1px solid #dfe2e6;
                    "
                >
                    <!-- Premium topbar -->
                    <tr>
                        <td
                            bgcolor="#090a0c"
                            style="
                                background: #090a0c;
                                padding: 0;
                                height: 12px;
                                font-size: 0;
                                line-height: 0;
                            "
                        >
                            &nbsp;
                        </td>
                    </tr>

                    <!-- Brand -->
                    <tr>
                        <td
                            bgcolor="#111214"
                            class="content-pad"
                            style="
                                background: #111214;
                                padding: 25px 32px 22px 32px;
                            "
                        >
                            <div
                                style="
                                    color: #d1a15b;
                                    font-size: 15px;
                                    line-height: 1.3;
                                    font-weight: 800;
                                    letter-spacing: 3px;
                                    text-transform: uppercase;
                                "
                            >
                                Mashal Automotive Security
                            </div>

                            <div
                                style="
                                    margin-top: 12px;
                                    width: 52px;
                                    height: 3px;
                                    background: #d1a15b;
                                    font-size: 0;
                                    line-height: 0;
                                "
                            >
                                &nbsp;
                            </div>
                        </td>
                    </tr>

                    <!-- Hero -->
                    <tr>
                        <td
                            class="content-pad"
                            style="
                                padding: 34px 32px 18px 32px;
                            "
                        >
                            <h1
                                class="hero-title"
                                style="
                                    margin: 0;
                                    color: #111214;
                                    font-size: 38px;
                                    line-height: 1.12;
                                    font-weight: 800;
                                    letter-spacing: -0.8px;
                                "
                            >
                                Nieuwe login gedetecteerd
                            </h1>
                        </td>
                    </tr>

                    <tr>
                        <td
                            class="content-pad"
                            style="
                                padding: 0 32px 28px 32px;
                            "
                        >
                            <p
                                class="hero-copy"
                                style="
                                    margin: 0;
                                    color: #6e737c;
                                    font-size: 17px;
                                    line-height: 1.7;
                                "
                            >
                                Hallo {{ $emailName }}, er is een succesvolle login
                                op je Mashal Automotive-account geregistreerd.
                            </p>
                        </td>
                    </tr>

                    <!-- Status card -->
                    <tr>
                        <td
                            class="content-pad"
                            style="
                                padding: 0 32px 30px 32px;
                            "
                        >
                            <table
                                role="presentation"
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                bgcolor="#f7f4ee"
                                style="
                                    width: 100%;
                                    background: #f7f4ee;
                                    border: 1px solid #e7dcc8;
                                "
                            >
                                <tr>
                                    <td
                                        width="6"
                                        bgcolor="{{ $statusAccent }}"
                                        style="
                                            width: 6px;
                                            background: {{ $statusAccent }};
                                            font-size: 0;
                                            line-height: 0;
                                        "
                                    >
                                        &nbsp;
                                    </td>

                                    <td
                                        style="
                                            padding: 18px 18px;
                                        "
                                    >
                                        <div
                                            style="
                                                color: #a57736;
                                                font-size: 11px;
                                                line-height: 1.3;
                                                font-weight: 800;
                                                letter-spacing: 2px;
                                                text-transform: uppercase;
                                            "
                                        >
                                            Apparaatstatus
                                        </div>

                                        <div
                                            style="
                                                margin-top: 5px;
                                                color: #17191d;
                                                font-size: 19px;
                                                line-height: 1.4;
                                                font-weight: 800;
                                            "
                                        >
                                            {{ $newDeviceLabel }}
                                        </div>

                                        <div
                                            style="
                                                margin-top: 4px;
                                                color: #747982;
                                                font-size: 13px;
                                                line-height: 1.5;
                                            "
                                        >
                                            {{ $resolvedProviderLabel }}
                                            ·
                                            {{ $resolvedDisplayTime }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Section: Login -->
                    <tr>
                        <td
                            class="content-pad"
                            style="
                                padding: 0 32px 10px 32px;
                            "
                        >
                            <div
                                class="section-title"
                                style="
                                    color: #b98743;
                                    font-size: 13px;
                                    line-height: 1.4;
                                    font-weight: 800;
                                    letter-spacing: 2.5px;
                                    text-transform: uppercase;
                                "
                            >
                                Logininformatie
                            </div>
                        </td>
                    </tr>

                    <!-- Apparaat -->
                    <tr>
                        <td class="content-pad" style="padding: 0 32px 10px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#111214"
                                style="width:100%; background:#111214; border:1px solid #24262b;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <div class="detail-label"
                                            style="color:#c89a58; font-size:11px; line-height:1.3; font-weight:800; letter-spacing:1.6px; text-transform:uppercase;">
                                            Apparaat
                                        </div>
                                        <div class="detail-value"
                                            style="margin-top:5px; color:#ffffff; font-size:18px; line-height:1.45; font-weight:800;">
                                            {{ $deviceLabel }}
                                        </div>
                                        <div
                                            style="margin-top:4px; color:#a6a9af; font-size:13px; line-height:1.5;">
                                            {{ $deviceTypeLabel }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Browser -->
                    <tr>
                        <td class="content-pad" style="padding: 0 32px 10px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#111214"
                                style="width:100%; background:#111214; border:1px solid #24262b;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <div class="detail-label"
                                            style="color:#c89a58; font-size:11px; line-height:1.3; font-weight:800; letter-spacing:1.6px; text-transform:uppercase;">
                                            Browser & besturingssysteem
                                        </div>
                                        <div class="detail-value"
                                            style="margin-top:5px; color:#ffffff; font-size:18px; line-height:1.45; font-weight:800;">
                                            {{ $browserLabel }}
                                        </div>
                                        <div
                                            style="margin-top:4px; color:#a6a9af; font-size:13px; line-height:1.5;">
                                            {{ $operatingSystemLabel }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Methode -->
                    <tr>
                        <td class="content-pad" style="padding: 0 32px 10px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#111214"
                                style="width:100%; background:#111214; border:1px solid #24262b;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <div class="detail-label"
                                            style="color:#c89a58; font-size:11px; line-height:1.3; font-weight:800; letter-spacing:1.6px; text-transform:uppercase;">
                                            Loginmethode
                                        </div>
                                        <div class="detail-value"
                                            style="margin-top:5px; color:#ffffff; font-size:18px; line-height:1.45; font-weight:800;">
                                            {{ $resolvedProviderLabel }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Tijd -->
                    <tr>
                        <td class="content-pad" style="padding: 0 32px 30px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#111214"
                                style="width:100%; background:#111214; border:1px solid #24262b;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <div class="detail-label"
                                            style="color:#c89a58; font-size:11px; line-height:1.3; font-weight:800; letter-spacing:1.6px; text-transform:uppercase;">
                                            Tijdstip
                                        </div>
                                        <div class="detail-value"
                                            style="margin-top:5px; color:#ffffff; font-size:18px; line-height:1.45; font-weight:800;">
                                            {{ $resolvedDisplayTime }}
                                        </div>
                                        <div
                                            style="margin-top:5px; color:#a6a9af; font-size:13px; line-height:1.55;">
                                            {{ $timezoneLabel }}
                                            <br>
                                            UTC: {{ $utcTime }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Section: Network -->
                    <tr>
                        <td
                            class="content-pad"
                            style="
                                padding: 0 32px 10px 32px;
                            "
                        >
                            <div
                                class="section-title"
                                style="
                                    color: #b98743;
                                    font-size: 13px;
                                    line-height: 1.4;
                                    font-weight: 800;
                                    letter-spacing: 2.5px;
                                    text-transform: uppercase;
                                "
                            >
                                Netwerk en geschatte locatie
                            </div>
                        </td>
                    </tr>

                    <!-- IP -->
                    <tr>
                        <td class="content-pad" style="padding: 0 32px 10px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f7f8f9"
                                style="width:100%; background:#f7f8f9; border:1px solid #e3e5e8;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <div class="detail-label"
                                            style="color:#8b6b3c; font-size:11px; line-height:1.3; font-weight:800; letter-spacing:1.6px; text-transform:uppercase;">
                                            IP-adres
                                        </div>
                                        <div class="detail-value"
                                            style="margin-top:5px; color:#17191d; font-size:18px; line-height:1.45; font-weight:800;">
                                            {{ $ipAddress }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Estimated location -->
                    <tr>
                        <td class="content-pad" style="padding: 0 32px 10px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f7f8f9"
                                style="width:100%; background:#f7f8f9; border:1px solid #e3e5e8;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <div class="detail-label"
                                            style="color:#8b6b3c; font-size:11px; line-height:1.3; font-weight:800; letter-spacing:1.6px; text-transform:uppercase;">
                                            Geschatte locatie
                                        </div>
                                        <div class="detail-value"
                                            style="margin-top:5px; color:#17191d; font-size:18px; line-height:1.45; font-weight:800;">
                                            {{ $safeLocationLabel }}
                                        </div>
                                        <div
                                            style="margin-top:5px; color:#747982; font-size:13px; line-height:1.55;">
                                            Stad: {{ $cityLabel }}
                                            <br>
                                            Regio: {{ $regionLabel }}
                                            <br>
                                            Land: {{ $countryDisplay }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Source/timezones -->
                    <tr>
                        <td class="content-pad" style="padding: 0 32px 30px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f7f8f9"
                                style="width:100%; background:#f7f8f9; border:1px solid #e3e5e8;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <div class="detail-label"
                                            style="color:#8b6b3c; font-size:11px; line-height:1.3; font-weight:800; letter-spacing:1.6px; text-transform:uppercase;">
                                            Locatiebron & timezones
                                        </div>
                                        <div
                                            style="margin-top:6px; color:#17191d; font-size:15px; line-height:1.65; font-weight:700;">
                                            {{ $locationSourceLabel }}
                                            <br>
                                            Browser: {{ $browserTimezone }}
                                            <br>
                                            IP: {{ $ipTimezone }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Section: Precise location -->
                    <tr>
                        <td
                            class="content-pad"
                            style="
                                padding: 0 32px 10px 32px;
                            "
                        >
                            <div
                                class="section-title"
                                style="
                                    color: #b98743;
                                    font-size: 13px;
                                    line-height: 1.4;
                                    font-weight: 800;
                                    letter-spacing: 2.5px;
                                    text-transform: uppercase;
                                "
                            >
                                Precieze browserlocatie
                            </div>
                        </td>
                    </tr>

                    <!-- Permission -->
                    <tr>
                        <td class="content-pad" style="padding: 0 32px 10px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#111214"
                                style="width:100%; background:#111214; border:1px solid #24262b;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <div class="detail-label"
                                            style="color:#c89a58; font-size:11px; line-height:1.3; font-weight:800; letter-spacing:1.6px; text-transform:uppercase;">
                                            Locatietoestemming
                                        </div>
                                        <div class="detail-value"
                                            style="margin-top:5px; color:#ffffff; font-size:18px; line-height:1.45; font-weight:800;">
                                            {{ $locationPermission }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- GPS -->
                    <tr>
                        <td class="content-pad" style="padding: 0 32px 10px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#111214"
                                style="width:100%; background:#111214; border:1px solid #24262b;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <div class="detail-label"
                                            style="color:#c89a58; font-size:11px; line-height:1.3; font-weight:800; letter-spacing:1.6px; text-transform:uppercase;">
                                            GPS-coördinaten
                                        </div>
                                        <div class="detail-value"
                                            style="margin-top:5px; color:#ffffff; font-size:18px; line-height:1.45; font-weight:800; word-break:break-word;">
                                            {{ $coordinatesLabel }}
                                        </div>
                                        <div
                                            style="margin-top:5px; color:#a6a9af; font-size:13px; line-height:1.55;">
                                            Nauwkeurigheid: {{ $accuracyLabel }}
                                            <br>
                                            Vastgelegd: {{ $preciseCapturedAt }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Privacy note -->
                    <tr>
                        <td class="content-pad" style="padding: 0 32px 30px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#fbf7ef"
                                style="width:100%; background:#fbf7ef; border:1px solid #eee0c7;">
                                <tr>
                                    <td style="padding:14px 16px;">
                                        <div
                                            style="color:#725c3c; font-size:12px; line-height:1.65;">
                                            Precieze GPS-locatie wordt alleen opgeslagen wanneer
                                            daarvoor in de browser toestemming is gegeven.
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Section: Technical -->
                    <tr>
                        <td
                            class="content-pad"
                            style="
                                padding: 0 32px 10px 32px;
                            "
                        >
                            <div
                                class="section-title"
                                style="
                                    color: #b98743;
                                    font-size: 13px;
                                    line-height: 1.4;
                                    font-weight: 800;
                                    letter-spacing: 2.5px;
                                    text-transform: uppercase;
                                "
                            >
                                Technische informatie
                            </div>
                        </td>
                    </tr>

                    <!-- Remember -->
                    <tr>
                        <td class="content-pad" style="padding: 0 32px 10px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f7f8f9"
                                style="width:100%; background:#f7f8f9; border:1px solid #e3e5e8;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <div class="detail-label"
                                            style="color:#8b6b3c; font-size:11px; line-height:1.3; font-weight:800; letter-spacing:1.6px; text-transform:uppercase;">
                                            Ingelogd blijven
                                        </div>
                                        <div class="detail-value"
                                            style="margin-top:5px; color:#17191d; font-size:18px; line-height:1.45; font-weight:800;">
                                            {{ $rememberLabel }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- User agent -->
                    <tr>
                        <td class="content-pad" style="padding: 0 32px 32px 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f7f8f9"
                                style="width:100%; background:#f7f8f9; border:1px solid #e3e5e8;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <div class="detail-label"
                                            style="color:#8b6b3c; font-size:11px; line-height:1.3; font-weight:800; letter-spacing:1.6px; text-transform:uppercase;">
                                            User-Agent
                                        </div>
                                        <div
                                            class="technical-value"
                                            style="
                                                margin-top:6px;
                                                color:#5c6068;
                                                font-family:'Courier New', Courier, monospace;
                                                font-size:11px;
                                                line-height:1.6;
                                                font-weight:400;
                                                word-break:break-all;
                                                overflow-wrap:anywhere;
                                            "
                                        >
                                            {{ $userAgent }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Security CTA -->
                    <tr>
                        <td
                            class="content-pad"
                            style="
                                padding: 0 32px 14px 32px;
                            "
                        >
                            <table
                                role="presentation"
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                bgcolor="#111214"
                                style="
                                    width: 100%;
                                    background: #111214;
                                    border: 1px solid #24262b;
                                "
                            >
                                <tr>
                                    <td
                                        style="
                                            padding: 22px 20px;
                                            text-align: center;
                                        "
                                    >
                                        <div
                                            style="
                                                color: #ffffff;
                                                font-size: 17px;
                                                line-height: 1.4;
                                                font-weight: 800;
                                            "
                                        >
                                            Herken je deze login?
                                        </div>

                                        <div
                                            style="
                                                margin-top: 7px;
                                                color: #a9acb2;
                                                font-size: 13px;
                                                line-height: 1.6;
                                            "
                                        >
                                            Als jij dit was, hoef je niets te doen.
                                            Was jij dit niet, controleer dan direct je account.
                                        </div>

                                        <div style="height:18px; font-size:0; line-height:0;">
                                            &nbsp;
                                        </div>

                                        <a
                                            href="{{ $securityUrl }}"
                                            class="cta"
                                            style="
                                                display: inline-block;
                                                padding: 14px 24px;
                                                background: #d1a15b;
                                                border: 1px solid #d1a15b;
                                                color: #111214;
                                                font-size: 14px;
                                                line-height: 1.2;
                                                font-weight: 800;
                                                text-decoration: none;
                                                letter-spacing: 0.3px;
                                            "
                                        >
                                            Bekijk loginactiviteit
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Explanatory text -->
                    <tr>
                        <td
                            class="content-pad"
                            style="
                                padding: 12px 32px 26px 32px;
                            "
                        >
                            <p
                                style="
                                    margin: 0;
                                    color: #7c818a;
                                    font-size: 12px;
                                    line-height: 1.7;
                                "
                            >
                                De IP-locatie is een schatting en kan bijvoorbeeld
                                je internetprovider, VPN of mobiele provider tonen.
                                Browsers geven daarnaast niet altijd het exacte model
                                van een telefoon vrij.
                            </p>

                            <p
                                style="
                                    margin: 13px 0 0 0;
                                    color: #7c818a;
                                    font-size: 12px;
                                    line-height: 1.7;
                                "
                            >
                                Let op: deze e-mail kan gevoelige beveiligingsinformatie
                                bevatten. Deel hem niet met anderen.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td
                            bgcolor="#090a0c"
                            style="
                                background: #090a0c;
                                padding: 20px 24px;
                                text-align: center;
                            "
                        >
                            <div
                                style="
                                    color: #d1a15b;
                                    font-size: 11px;
                                    line-height: 1.4;
                                    font-weight: 800;
                                    letter-spacing: 2px;
                                    text-transform: uppercase;
                                "
                            >
                                Mashal Automotive
                            </div>

                            <div
                                style="
                                    margin-top: 5px;
                                    color: #8c9097;
                                    font-size: 11px;
                                    line-height: 1.5;
                                "
                            >
                                Automatische beveiligingsmelding
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
