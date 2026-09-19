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
            'privacy_disabled' => 'Niet opgeslagen',
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

    $securityUrl = $securityUrl ?? route('security.index');

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

    $isNewDevice = (bool) ($activity->is_new_device ?? false);
@endphp

<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark light">
    <meta name="supported-color-schemes" content="dark light">

    <title>Nieuwe login gedetecteerd</title>

    <style>
        html,
        body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            min-width: 100% !important;
            background: #08090b !important;
            -webkit-text-size-adjust: 100% !important;
            -ms-text-size-adjust: 100% !important;
        }

        table {
            border-collapse: collapse !important;
            border-spacing: 0 !important;
        }

        img {
            border: 0 !important;
            outline: none !important;
            text-decoration: none !important;
        }

        a {
            text-decoration: none !important;
        }

        .hybrid-card {
            display: inline-block !important;
            width: 100% !important;
            max-width: 196px !important;
            vertical-align: top !important;
        }

        @media only screen and (max-width: 620px) {
            .page-pad {
                padding: 0 !important;
            }

            .shell {
                border-left: 0 !important;
                border-right: 0 !important;
            }

            .content-pad {
                padding-left: 18px !important;
                padding-right: 18px !important;
            }

            .brand-name {
                font-size: 21px !important;
                letter-spacing: 4px !important;
            }

            .hero-title {
                font-size: 31px !important;
                line-height: 1.08 !important;
            }

            .hero-copy {
                font-size: 15px !important;
                line-height: 1.65 !important;
            }

            .hybrid-card {
                max-width: 100% !important;
            }

            .section-title {
                font-size: 13px !important;
                letter-spacing: 1.7px !important;
            }

            .cta {
                display: block !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }

            .technical {
                font-size: 10px !important;
            }
        }
    </style>
</head>

<body
    bgcolor="#08090b"
    style="
        margin: 0;
        padding: 0;
        width: 100%;
        min-width: 100%;
        background: #08090b;
        color: #ffffff;
        font-family: Arial, Helvetica, sans-serif;
    "
>
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
        Nieuwe succesvolle login op je Mashal Automotive-account via {{ $resolvedProviderLabel }}.
    </div>

    <table
        role="presentation"
        width="100%"
        cellpadding="0"
        cellspacing="0"
        border="0"
        bgcolor="#08090b"
        style="
            width: 100%;
            background: #08090b;
        "
    >
        <tr>
            <td
                class="page-pad"
                align="center"
                style="
                    padding: 24px 12px;
                "
            >
                <table
                    role="presentation"
                    width="680"
                    cellpadding="0"
                    cellspacing="0"
                    border="0"
                    bgcolor="#0d0f12"
                    class="shell"
                    style="
                        width: 100%;
                        max-width: 680px;
                        background: #0d0f12;
                        border: 1px solid #2b2d31;
                    "
                >
                    <!-- GOLD TOP LINE -->
                    <tr>
                        <td
                            bgcolor="#cfa557"
                            style="
                                height: 4px;
                                background: #cfa557;
                                font-size: 0;
                                line-height: 0;
                            "
                        >
                            &nbsp;
                        </td>
                    </tr>

                    <!-- BRAND HEADER -->
                    <tr>
                        <td
                            class="content-pad"
                            bgcolor="#0a0b0d"
                            style="
                                padding: 28px 30px 26px 30px;
                                background: #0a0b0d;
                            "
                        >
                            <table
                                role="presentation"
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                            >
                                <tr>
                                    <td
                                        width="58"
                                        valign="middle"
                                        style="
                                            width: 58px;
                                            vertical-align: middle;
                                        "
                                    >
                                        <table
                                            role="presentation"
                                            width="48"
                                            height="48"
                                            cellpadding="0"
                                            cellspacing="0"
                                            border="0"
                                            bgcolor="#cfa557"
                                            style="
                                                width: 48px;
                                                height: 48px;
                                                background: #cfa557;
                                            "
                                        >
                                            <tr>
                                                <td
                                                    align="center"
                                                    valign="middle"
                                                    style="
                                                        color: #0a0b0d;
                                                        font-size: 30px;
                                                        line-height: 48px;
                                                        font-weight: 900;
                                                        text-align: center;
                                                        vertical-align: middle;
                                                    "
                                                >
                                                    M
                                                </td>
                                            </tr>
                                        </table>
                                    </td>

                                    <td
                                        valign="middle"
                                        style="
                                            vertical-align: middle;
                                        "
                                    >
                                        <div
                                            class="brand-name"
                                            style="
                                                color: #ffffff;
                                                font-size: 24px;
                                                line-height: 1.1;
                                                font-weight: 800;
                                                letter-spacing: 5px;
                                                text-transform: uppercase;
                                            "
                                        >
                                            Mashal
                                        </div>

                                        <div
                                            style="
                                                margin-top: 5px;
                                                color: #cfa557;
                                                font-size: 10px;
                                                line-height: 1.3;
                                                font-weight: 700;
                                                letter-spacing: 4px;
                                                text-transform: uppercase;
                                            "
                                        >
                                            Automotive
                                        </div>
                                    </td>

                                    <td
                                        align="right"
                                        valign="top"
                                        style="
                                            color: #9b9ea5;
                                            font-size: 9px;
                                            line-height: 1.4;
                                            letter-spacing: 1.7px;
                                            text-transform: uppercase;
                                            vertical-align: top;
                                        "
                                    >
                                        Security notification
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- HERO -->
                    <tr>
                        <td
                            class="content-pad"
                            bgcolor="#0d0f12"
                            style="
                                padding: 34px 30px 8px 30px;
                                background: #0d0f12;
                            "
                        >
                            <h1
                                class="hero-title"
                                style="
                                    margin: 0;
                                    color: #ffffff;
                                    font-size: 38px;
                                    line-height: 1.08;
                                    font-weight: 900;
                                    letter-spacing: -0.7px;
                                "
                            >
                                Nieuwe login
                                <span style="color: #cfa557;">
                                    gedetecteerd
                                </span>
                            </h1>
                        </td>
                    </tr>

                    <tr>
                        <td
                            class="content-pad"
                            bgcolor="#0d0f12"
                            style="
                                padding: 8px 30px 28px 30px;
                                background: #0d0f12;
                            "
                        >
                            <p
                                class="hero-copy"
                                style="
                                    margin: 0;
                                    color: #c9cbd0;
                                    font-size: 16px;
                                    line-height: 1.65;
                                "
                            >
                                Hallo {{ $emailName }}, er is een succesvolle login op je
                                Mashal Automotive-account geregistreerd.
                            </p>
                        </td>
                    </tr>

                    <!-- 3 HYBRID CARDS -->
                    <tr>
                        <td
                            class="content-pad"
                            align="center"
                            style="
                                padding: 0 24px 30px 24px;
                                font-size: 0;
                                text-align: center;
                            "
                        >
                            <!--[if mso]>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                            <td width="33.33%" valign="top">
                            <![endif]-->

                            <div class="hybrid-card" style="display:inline-block;width:100%;max-width:196px;vertical-align:top;">
                                <table
                                    role="presentation"
                                    width="100%"
                                    cellpadding="0"
                                    cellspacing="0"
                                    border="0"
                                    bgcolor="#121417"
                                    style="
                                        width: 100%;
                                        background: #121417;
                                        border: 1px solid #34363b;
                                    "
                                >
                                    <tr>
                                        <td
                                            style="
                                                padding: 18px 16px;
                                                text-align: left;
                                            "
                                        >
                                            <div
                                                style="
                                                    color: #cfa557;
                                                    font-size: 10px;
                                                    line-height: 1.3;
                                                    font-weight: 800;
                                                    letter-spacing: 1.5px;
                                                    text-transform: uppercase;
                                                "
                                            >
                                                Apparaat
                                            </div>

                                            <div
                                                style="
                                                    margin-top: 9px;
                                                    color: #ffffff;
                                                    font-size: 17px;
                                                    line-height: 1.35;
                                                    font-weight: 800;
                                                "
                                            >
                                                {{ $deviceLabel }}
                                            </div>

                                            <div
                                                style="
                                                    margin-top: 7px;
                                                    color: #a9acb2;
                                                    font-size: 12px;
                                                    line-height: 1.55;
                                                "
                                            >
                                                {{ $deviceTypeLabel }}
                                                <br>
                                                {{ $browserLabel }}
                                                <br>
                                                {{ $operatingSystemLabel }}
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!--[if mso]>
                            </td>
                            <td width="33.33%" valign="top">
                            <![endif]-->

                            <div class="hybrid-card" style="display:inline-block;width:100%;max-width:196px;vertical-align:top;">
                                <table
                                    role="presentation"
                                    width="100%"
                                    cellpadding="0"
                                    cellspacing="0"
                                    border="0"
                                    bgcolor="#121417"
                                    style="
                                        width: 100%;
                                        background: #121417;
                                        border: 1px solid #34363b;
                                    "
                                >
                                    <tr>
                                        <td
                                            style="
                                                padding: 18px 16px;
                                                text-align: left;
                                            "
                                        >
                                            <div
                                                style="
                                                    color: #cfa557;
                                                    font-size: 10px;
                                                    line-height: 1.3;
                                                    font-weight: 800;
                                                    letter-spacing: 1.5px;
                                                    text-transform: uppercase;
                                                "
                                            >
                                                Loginmethode
                                            </div>

                                            <div
                                                style="
                                                    margin-top: 9px;
                                                    color: #ffffff;
                                                    font-size: 17px;
                                                    line-height: 1.35;
                                                    font-weight: 800;
                                                "
                                            >
                                                {{ $resolvedProviderLabel }}
                                            </div>

                                            <div
                                                style="
                                                    display: inline-block;
                                                    margin-top: 9px;
                                                    padding: 5px 8px;
                                                    background: {{ $isNewDevice ? '#6e4e20' : '#224b31' }};
                                                    color: {{ $isNewDevice ? '#f2cc88' : '#9de0b3' }};
                                                    font-size: 10px;
                                                    line-height: 1.3;
                                                    font-weight: 800;
                                                "
                                            >
                                                {{ $newDeviceLabel }}
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!--[if mso]>
                            </td>
                            <td width="33.33%" valign="top">
                            <![endif]-->

                            <div class="hybrid-card" style="display:inline-block;width:100%;max-width:196px;vertical-align:top;">
                                <table
                                    role="presentation"
                                    width="100%"
                                    cellpadding="0"
                                    cellspacing="0"
                                    border="0"
                                    bgcolor="#121417"
                                    style="
                                        width: 100%;
                                        background: #121417;
                                        border: 1px solid #34363b;
                                    "
                                >
                                    <tr>
                                        <td
                                            style="
                                                padding: 18px 16px;
                                                text-align: left;
                                            "
                                        >
                                            <div
                                                style="
                                                    color: #cfa557;
                                                    font-size: 10px;
                                                    line-height: 1.3;
                                                    font-weight: 800;
                                                    letter-spacing: 1.5px;
                                                    text-transform: uppercase;
                                                "
                                            >
                                                Tijdstip
                                            </div>

                                            <div
                                                style="
                                                    margin-top: 9px;
                                                    color: #ffffff;
                                                    font-size: 16px;
                                                    line-height: 1.4;
                                                    font-weight: 800;
                                                "
                                            >
                                                {{ $resolvedDisplayTime }}
                                            </div>

                                            <div
                                                style="
                                                    margin-top: 7px;
                                                    color: #a9acb2;
                                                    font-size: 12px;
                                                    line-height: 1.55;
                                                "
                                            >
                                                {{ $timezoneLabel }}
                                                <br>
                                                UTC: {{ $utcTime }}
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!--[if mso]>
                            </td>
                            </tr>
                            </table>
                            <![endif]-->
                        </td>
                    </tr>

                    <!-- NETWORK SECTION -->
                    <tr>
                        <td
                            class="content-pad"
                            style="
                                padding: 0 30px 10px 30px;
                            "
                        >
                            <div
                                class="section-title"
                                style="
                                    color: #cfa557;
                                    font-size: 14px;
                                    line-height: 1.4;
                                    font-weight: 800;
                                    letter-spacing: 2px;
                                    text-transform: uppercase;
                                "
                            >
                                Netwerk en geschatte locatie
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td
                            class="content-pad"
                            style="
                                padding: 0 30px 28px 30px;
                            "
                        >
                            <table
                                role="presentation"
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                bgcolor="#111316"
                                style="
                                    width: 100%;
                                    background: #111316;
                                    border: 1px solid #34363b;
                                "
                            >
                                <tr>
                                    <td style="padding: 18px 18px 8px 18px;">
                                        <div
                                            style="
                                                color: #8f939a;
                                                font-size: 10px;
                                                line-height: 1.3;
                                                font-weight: 700;
                                                letter-spacing: 1.3px;
                                                text-transform: uppercase;
                                            "
                                        >
                                            IP-adres
                                        </div>

                                        <div
                                            style="
                                                margin-top: 4px;
                                                color: #ffffff;
                                                font-size: 16px;
                                                line-height: 1.4;
                                                font-weight: 800;
                                            "
                                        >
                                            {{ $ipAddress }}
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 8px 18px;">
                                        <div
                                            style="
                                                color: #8f939a;
                                                font-size: 10px;
                                                line-height: 1.3;
                                                font-weight: 700;
                                                letter-spacing: 1.3px;
                                                text-transform: uppercase;
                                            "
                                        >
                                            Geschatte locatie
                                        </div>

                                        <div
                                            style="
                                                margin-top: 4px;
                                                color: #ffffff;
                                                font-size: 16px;
                                                line-height: 1.45;
                                                font-weight: 800;
                                            "
                                        >
                                            {{ $safeLocationLabel }}
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 8px 18px;">
                                        <div
                                            style="
                                                color: #c3c6cb;
                                                font-size: 13px;
                                                line-height: 1.7;
                                            "
                                        >
                                            <strong style="color:#ffffff;">Stad:</strong>
                                            {{ $cityLabel }}
                                            <br>

                                            <strong style="color:#ffffff;">Regio:</strong>
                                            {{ $regionLabel }}
                                            <br>

                                            <strong style="color:#ffffff;">Land:</strong>
                                            {{ $countryDisplay }}
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding: 8px 18px 18px 18px;">
                                        <div
                                            style="
                                                color: #c3c6cb;
                                                font-size: 13px;
                                                line-height: 1.7;
                                            "
                                        >
                                            <strong style="color:#ffffff;">Locatiebron:</strong>
                                            {{ $locationSourceLabel }}
                                            <br>

                                            <strong style="color:#ffffff;">Browser-timezone:</strong>
                                            {{ $browserTimezone }}
                                            <br>

                                            <strong style="color:#ffffff;">IP-timezone:</strong>
                                            {{ $ipTimezone }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- PRECISE LOCATION -->
                    <tr>
                        <td
                            class="content-pad"
                            style="
                                padding: 0 30px 10px 30px;
                            "
                        >
                            <div
                                class="section-title"
                                style="
                                    color: #cfa557;
                                    font-size: 14px;
                                    line-height: 1.4;
                                    font-weight: 800;
                                    letter-spacing: 2px;
                                    text-transform: uppercase;
                                "
                            >
                                Precieze browserlocatie
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td
                            class="content-pad"
                            style="
                                padding: 0 30px 28px 30px;
                            "
                        >
                            <table
                                role="presentation"
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                bgcolor="#111316"
                                style="
                                    width: 100%;
                                    background: #111316;
                                    border: 1px solid #34363b;
                                "
                            >
                                <tr>
                                    <td style="padding: 18px;">
                                        <div
                                            style="
                                                color: #c3c6cb;
                                                font-size: 13px;
                                                line-height: 1.8;
                                            "
                                        >
                                            <strong style="color:#ffffff;">Locatietoestemming:</strong>
                                            <span
                                                style="
                                                    display: inline-block;
                                                    padding: 3px 7px;
                                                    background: {{ ($activity->location_permission ?? null) === 'granted' ? '#1f5631' : '#503520' }};
                                                    color: {{ ($activity->location_permission ?? null) === 'granted' ? '#a9edbb' : '#f2cb90' }};
                                                    font-size: 11px;
                                                    font-weight: 800;
                                                "
                                            >
                                                {{ $locationPermission }}
                                            </span>

                                            <br>

                                            <strong style="color:#ffffff;">GPS-coördinaten:</strong>
                                            {{ $coordinatesLabel }}

                                            <br>

                                            <strong style="color:#ffffff;">GPS-nauwkeurigheid:</strong>
                                            {{ $accuracyLabel }}

                                            <br>

                                            <strong style="color:#ffffff;">Vastgelegd op:</strong>
                                            {{ $preciseCapturedAt }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- TECHNICAL -->
                    <tr>
                        <td
                            class="content-pad"
                            style="
                                padding: 0 30px 10px 30px;
                            "
                        >
                            <div
                                class="section-title"
                                style="
                                    color: #cfa557;
                                    font-size: 14px;
                                    line-height: 1.4;
                                    font-weight: 800;
                                    letter-spacing: 2px;
                                    text-transform: uppercase;
                                "
                            >
                                Technische informatie
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td
                            class="content-pad"
                            style="
                                padding: 0 30px 30px 30px;
                            "
                        >
                            <table
                                role="presentation"
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                bgcolor="#111316"
                                style="
                                    width: 100%;
                                    background: #111316;
                                    border: 1px solid #34363b;
                                "
                            >
                                <tr>
                                    <td style="padding: 18px;">
                                        <div
                                            style="
                                                color: #c3c6cb;
                                                font-size: 13px;
                                                line-height: 1.7;
                                            "
                                        >
                                            <strong style="color:#ffffff;">Onthouden:</strong>
                                            {{ $rememberLabel }}
                                        </div>

                                        <div
                                            class="technical"
                                            style="
                                                margin-top: 12px;
                                                padding-top: 12px;
                                                border-top: 1px solid #2d3035;
                                                color: #8e9299;
                                                font-family: 'Courier New', Courier, monospace;
                                                font-size: 11px;
                                                line-height: 1.6;
                                                word-break: break-all;
                                                overflow-wrap: anywhere;
                                            "
                                        >
                                            {{ $userAgent }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- CTA -->
                    <tr>
                        <td
                            class="content-pad"
                            align="center"
                            style="
                                padding: 0 30px 28px 30px;
                                text-align: center;
                            "
                        >
                            <a
                                href="{{ $securityUrl }}"
                                class="cta"
                                style="
                                    display: inline-block;
                                    padding: 15px 26px;
                                    background: #d4aa5c;
                                    border: 1px solid #e1bd78;
                                    color: #0a0b0d;
                                    font-size: 14px;
                                    line-height: 1.2;
                                    font-weight: 900;
                                    letter-spacing: 0.2px;
                                "
                            >
                                Bekijk loginactiviteit →
                            </a>
                        </td>
                    </tr>

                    <!-- WAS JIJ DIT -->
                    <tr>
                        <td
                            class="content-pad"
                            style="
                                padding: 0 30px 28px 30px;
                            "
                        >
                            <table
                                role="presentation"
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                bgcolor="#101216"
                                style="
                                    width: 100%;
                                    background: #101216;
                                    border: 1px solid #34363b;
                                "
                            >
                                <tr>
                                    <td style="padding: 18px;">
                                        <div
                                            style="
                                                color: #ffffff;
                                                font-size: 15px;
                                                line-height: 1.4;
                                                font-weight: 800;
                                            "
                                        >
                                            Was jij dit?
                                        </div>

                                        <div
                                            style="
                                                margin-top: 6px;
                                                color: #aeb1b7;
                                                font-size: 12px;
                                                line-height: 1.65;
                                            "
                                        >
                                            Dan hoef je niets te doen. Herken je deze login niet,
                                            wijzig dan direct je wachtwoord en controleer je account.
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- PRIVACY -->
                    <tr>
                        <td
                            class="content-pad"
                            style="
                                padding: 0 30px 28px 30px;
                            "
                        >
                            <p
                                style="
                                    margin: 0;
                                    color: #777b82;
                                    font-size: 11px;
                                    line-height: 1.7;
                                "
                            >
                                De IP-locatie is een schatting en kan bijvoorbeeld je
                                internetprovider, VPN of mobiele provider tonen. Precieze
                                GPS-locatie wordt alleen opgeslagen wanneer daarvoor in de
                                browser toestemming is gegeven. Browsers geven daarnaast niet
                                altijd het exacte model van een telefoon vrij.
                            </p>

                            <p
                                style="
                                    margin: 12px 0 0 0;
                                    color: #777b82;
                                    font-size: 11px;
                                    line-height: 1.7;
                                "
                            >
                                Let op: deze e-mail kan gevoelige beveiligingsinformatie
                                bevatten. Deel hem niet met anderen.
                            </p>
                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td
                            bgcolor="#090a0c"
                            style="
                                padding: 22px 24px;
                                background: #090a0c;
                                border-top: 1px solid #cfa557;
                                text-align: center;
                            "
                        >
                            <div
                                style="
                                    color: #cfa557;
                                    font-size: 10px;
                                    line-height: 1.4;
                                    font-weight: 800;
                                    letter-spacing: 3px;
                                    text-transform: uppercase;
                                "
                            >
                                Mashal Automotive
                            </div>

                            <div
                                style="
                                    margin-top: 7px;
                                    color: #777b82;
                                    font-size: 10px;
                                    line-height: 1.5;
                                "
                            >
                                Veilig. Betrouwbaar. Altijd onderweg.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
