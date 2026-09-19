@php
    use Carbon\CarbonInterface;

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
@endphp

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
        /*
        |--------------------------------------------------------------------------
        | Basis
        |--------------------------------------------------------------------------
        */

        html,
        body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            min-width: 100% !important;
            background: #f2f3f5 !important;
            color: #1d2025 !important;
            font-family:
                Arial,
                Helvetica,
                sans-serif !important;
            -webkit-text-size-adjust: 100% !important;
            -ms-text-size-adjust: 100% !important;
        }

        table {
            border-spacing: 0 !important;
            border-collapse: collapse !important;
        }

        td {
            mso-line-height-rule: exactly;
        }

        img {
            border: 0;
            outline: none;
            text-decoration: none;
            display: block;
        }

        a {
            color: inherit;
        }

        /*
        |--------------------------------------------------------------------------
        | Layout
        |--------------------------------------------------------------------------
        */

        .email-shell {
            width: 100% !important;
            background: #f2f3f5 !important;
        }

        .email-card {
            width: 100%;
            max-width: 720px;
            background: #ffffff !important;
            border: 1px solid #e2e3e6;
        }

        .content-padding {
            padding-left: 34px !important;
            padding-right: 34px !important;
        }

        /*
        |--------------------------------------------------------------------------
        | Datatabellen
        |--------------------------------------------------------------------------
        */

        .data-table {
            width: 100% !important;
            table-layout: fixed !important;
        }

        .data-row {
            width: 100% !important;
        }

        .data-label,
        .data-value {
            border-bottom: 1px solid #dadcdf;
            vertical-align: top !important;
            line-height: 1.5 !important;
        }

        .data-label {
            width: 38%;
            padding: 13px 14px 13px 0 !important;
            color: #73777f !important;
            font-size: 15px !important;
            font-weight: 400 !important;
        }

        .data-value {
            width: 62%;
            padding: 13px 0 13px 14px !important;
            color: #1d2025 !important;
            font-size: 15px !important;
            font-weight: 700 !important;
            text-align: right !important;
            word-break: break-word !important;
            overflow-wrap: anywhere !important;
        }

        .technical-value {
            font-family:
                "Courier New",
                Courier,
                monospace !important;
            font-size: 12px !important;
            line-height: 1.6 !important;
            font-weight: 400 !important;
        }

        /*
        |--------------------------------------------------------------------------
        | Mobiel
        |--------------------------------------------------------------------------
        |
        | Gmail/iPhone en andere smalle clients krijgen labels en waarden
        | onder elkaar. Hierdoor kan tekst niet meer horizontaal in elkaar
        | schuiven zoals bij de oude tweekoloms tabel.
        |
        */

        @media only screen and (max-width: 620px) {
            .email-card {
                width: 100% !important;
                max-width: 100% !important;
                border-left: 0 !important;
                border-right: 0 !important;
            }

            .content-padding {
                padding-left: 20px !important;
                padding-right: 20px !important;
            }

            .mobile-title {
                font-size: 28px !important;
                line-height: 1.15 !important;
            }

            .mobile-intro {
                font-size: 17px !important;
                line-height: 1.65 !important;
            }

            .data-table,
            .data-table tbody,
            .data-row,
            .data-label,
            .data-value {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
            }

            .data-row {
                border-bottom: 1px solid #dadcdf !important;
                padding: 12px 0 !important;
            }

            .data-label {
                border-bottom: 0 !important;
                padding: 0 0 5px 0 !important;
                font-size: 13px !important;
                line-height: 1.35 !important;
            }

            .data-value {
                border-bottom: 0 !important;
                padding: 0 !important;
                font-size: 16px !important;
                line-height: 1.5 !important;
                text-align: left !important;
                font-weight: 700 !important;
            }

            .technical-value {
                font-size: 11px !important;
                line-height: 1.55 !important;
                word-break: break-all !important;
            }

            .cta {
                display: block !important;
                width: 100% !important;
                box-sizing: border-box !important;
                text-align: center !important;
            }

            .section-title {
                font-size: 14px !important;
                line-height: 1.4 !important;
            }

            .footer-copy {
                font-size: 12px !important;
                line-height: 1.65 !important;
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
        background: #f2f3f5;
        color: #1d2025;
        font-family: Arial, Helvetica, sans-serif;
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
        class="email-shell"
        cellpadding="0"
        cellspacing="0"
        border="0"
        style="
            width: 100%;
            background: #f2f3f5;
        "
    >
        <tr>
            <td
                align="center"
                style="
                    padding: 28px 12px;
                "
            >
                <table
                    role="presentation"
                    width="720"
                    class="email-card"
                    cellpadding="0"
                    cellspacing="0"
                    border="0"
                    style="
                        width: 100%;
                        max-width: 720px;
                        background: #ffffff;
                        border: 1px solid #e2e3e6;
                    "
                >
                    <!-- Luxe zwarte bovenbalk -->
                    <tr>
                        <td
                            style="
                                height: 12px;
                                background: #090909;
                                font-size: 0;
                                line-height: 0;
                            "
                        >
                            &nbsp;
                        </td>
                    </tr>

                    <!-- Merk -->
                    <tr>
                        <td
                            class="content-padding"
                            style="
                                padding-top: 22px;
                                padding-bottom: 12px;
                                padding-left: 34px;
                                padding-right: 34px;
                            "
                        >
                            <div
                                style="
                                    color: #c99b55;
                                    font-size: 17px;
                                    line-height: 1.4;
                                    font-weight: 700;
                                    letter-spacing: 4px;
                                    text-transform: uppercase;
                                "
                            >
                                Mashal Automotive Security
                            </div>
                        </td>
                    </tr>

                    <!-- Titel -->
                    <tr>
                        <td
                            class="content-padding"
                            style="
                                padding-top: 20px;
                                padding-bottom: 8px;
                                padding-left: 34px;
                                padding-right: 34px;
                            "
                        >
                            <h1
                                class="mobile-title"
                                style="
                                    margin: 0;
                                    color: #15171a;
                                    font-size: 34px;
                                    line-height: 1.2;
                                    font-weight: 800;
                                "
                            >
                                Nieuwe login gedetecteerd
                            </h1>
                        </td>
                    </tr>

                    <!-- Intro -->
                    <tr>
                        <td
                            class="content-padding"
                            style="
                                padding-top: 12px;
                                padding-bottom: 26px;
                                padding-left: 34px;
                                padding-right: 34px;
                            "
                        >
                            <p
                                class="mobile-intro"
                                style="
                                    margin: 0;
                                    color: #73777f;
                                    font-size: 18px;
                                    line-height: 1.7;
                                "
                            >
                                Hallo {{ $emailName }}, er is een succesvolle login
                                op je Mashal Automotive-account geregistreerd.
                            </p>
                        </td>
                    </tr>

                    <!-- LOGININFORMATIE -->
                    <tr>
                        <td
                            class="content-padding"
                            style="
                                padding-left: 34px;
                                padding-right: 34px;
                            "
                        >
                            <div
                                class="section-title"
                                style="
                                    margin-bottom: 8px;
                                    color: #c99b55;
                                    font-size: 15px;
                                    line-height: 1.4;
                                    font-weight: 800;
                                    letter-spacing: 3px;
                                    text-transform: uppercase;
                                "
                            >
                                Logininformatie
                            </div>

                            <table
                                role="presentation"
                                width="100%"
                                class="data-table"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                style="
                                    width: 100%;
                                    table-layout: fixed;
                                "
                            >
                                <tr class="data-row">
                                    <td class="data-label">
                                        Apparaat
                                    </td>
                                    <td class="data-value">
                                        {{ $deviceLabel }}
                                    </td>
                                </tr>

                                <tr class="data-row">
                                    <td class="data-label">
                                        Apparaattype
                                    </td>
                                    <td class="data-value">
                                        {{ $deviceTypeLabel }}
                                    </td>
                                </tr>

                                <tr class="data-row">
                                    <td class="data-label">
                                        Browser
                                    </td>
                                    <td class="data-value">
                                        {{ $browserLabel }}
                                    </td>
                                </tr>

                                <tr class="data-row">
                                    <td class="data-label">
                                        Besturingssysteem
                                    </td>
                                    <td class="data-value">
                                        {{ $operatingSystemLabel }}
                                    </td>
                                </tr>

                                <tr class="data-row">
                                    <td class="data-label">
                                        Loginmethode
                                    </td>
                                    <td class="data-value">
                                        {{ $resolvedProviderLabel }}
                                    </td>
                                </tr>

                                <tr class="data-row">
                                    <td class="data-label">
                                        Apparaatstatus
                                    </td>
                                    <td class="data-value">
                                        {{ $newDeviceLabel }}
                                    </td>
                                </tr>

                                <tr class="data-row">
                                    <td class="data-label">
                                        Tijdstip
                                    </td>
                                    <td class="data-value">
                                        {{ $resolvedDisplayTime }}
                                    </td>
                                </tr>

                                <tr class="data-row">
                                    <td class="data-label">
                                        Gebruikte timezone
                                    </td>
                                    <td class="data-value">
                                        {{ $timezoneLabel }}
                                    </td>
                                </tr>

                                <tr class="data-row">
                                    <td class="data-label">
                                        UTC-tijd
                                    </td>
                                    <td class="data-value">
                                        {{ $utcTime }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Ruimte -->
                    <tr>
                        <td style="height: 32px; font-size: 0; line-height: 0;">
                            &nbsp;
                        </td>
                    </tr>

                    <!-- NETWERK -->
                    <tr>
                        <td
                            class="content-padding"
                            style="
                                padding-left: 34px;
                                padding-right: 34px;
                            "
                        >
                            <div
                                class="section-title"
                                style="
                                    margin-bottom: 8px;
                                    color: #c99b55;
                                    font-size: 15px;
                                    line-height: 1.4;
                                    font-weight: 800;
                                    letter-spacing: 3px;
                                    text-transform: uppercase;
                                "
                            >
                                Netwerk en geschatte locatie
                            </div>

                            <table
                                role="presentation"
                                width="100%"
                                class="data-table"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                style="
                                    width: 100%;
                                    table-layout: fixed;
                                "
                            >
                                <tr class="data-row">
                                    <td class="data-label">
                                        IP-adres
                                    </td>
                                    <td class="data-value">
                                        {{ $ipAddress }}
                                    </td>
                                </tr>

                                <tr class="data-row">
                                    <td class="data-label">
                                        Geschatte locatie
                                    </td>
                                    <td class="data-value">
                                        {{ $safeLocationLabel }}
                                    </td>
                                </tr>

                                <tr class="data-row">
                                    <td class="data-label">
                                        Stad
                                    </td>
                                    <td class="data-value">
                                        {{ filled($activity->city ?? null) ? $activity->city : 'Niet beschikbaar' }}
                                    </td>
                                </tr>

                                <tr class="data-row">
                                    <td class="data-label">
                                        Regio
                                    </td>
                                    <td class="data-value">
                                        {{ filled($activity->region ?? null) ? $activity->region : 'Niet beschikbaar' }}
                                    </td>
                                </tr>

                                <tr class="data-row">
                                    <td class="data-label">
                                        Land
                                    </td>
                                    <td class="data-value">
                                        {{ $countryLabel !== '' ? $countryLabel : 'Niet beschikbaar' }}
                                    </td>
                                </tr>

                                <tr class="data-row">
                                    <td class="data-label">
                                        Locatiebron
                                    </td>
                                    <td class="data-value">
                                        {{ $locationSourceLabel }}
                                    </td>
                                </tr>

                                <tr class="data-row">
                                    <td class="data-label">
                                        Browser-timezone
                                    </td>
                                    <td class="data-value">
                                        {{ $browserTimezone }}
                                    </td>
                                </tr>

                                <tr class="data-row">
                                    <td class="data-label">
                                        IP-timezone
                                    </td>
                                    <td class="data-value">
                                        {{ $ipTimezone }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Ruimte -->
                    <tr>
                        <td style="height: 32px; font-size: 0; line-height: 0;">
                            &nbsp;
                        </td>
                    </tr>

                    <!-- PRECISE LOCATIE -->
                    <tr>
                        <td
                            class="content-padding"
                            style="
                                padding-left: 34px;
                                padding-right: 34px;
                            "
                        >
                            <div
                                class="section-title"
                                style="
                                    margin-bottom: 8px;
                                    color: #c99b55;
                                    font-size: 15px;
                                    line-height: 1.4;
                                    font-weight: 800;
                                    letter-spacing: 3px;
                                    text-transform: uppercase;
                                "
                            >
                                Precieze browserlocatie
                            </div>

                            <table
                                role="presentation"
                                width="100%"
                                class="data-table"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                style="
                                    width: 100%;
                                    table-layout: fixed;
                                "
                            >
                                <tr class="data-row">
                                    <td class="data-label">
                                        Locatietoestemming
                                    </td>
                                    <td class="data-value">
                                        {{ $locationPermission }}
                                    </td>
                                </tr>

                                <tr class="data-row">
                                    <td class="data-label">
                                        GPS-coördinaten
                                    </td>
                                    <td class="data-value">
                                        {{ $coordinatesLabel }}
                                    </td>
                                </tr>

                                <tr class="data-row">
                                    <td class="data-label">
                                        GPS-nauwkeurigheid
                                    </td>
                                    <td class="data-value">
                                        {{ $accuracyLabel }}
                                    </td>
                                </tr>

                                <tr class="data-row">
                                    <td class="data-label">
                                        Vastgelegd op
                                    </td>
                                    <td class="data-value">
                                        {{ $preciseCapturedAt }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Ruimte -->
                    <tr>
                        <td style="height: 32px; font-size: 0; line-height: 0;">
                            &nbsp;
                        </td>
                    </tr>

                    <!-- TECHNISCH -->
                    <tr>
                        <td
                            class="content-padding"
                            style="
                                padding-left: 34px;
                                padding-right: 34px;
                            "
                        >
                            <div
                                class="section-title"
                                style="
                                    margin-bottom: 8px;
                                    color: #c99b55;
                                    font-size: 15px;
                                    line-height: 1.4;
                                    font-weight: 800;
                                    letter-spacing: 3px;
                                    text-transform: uppercase;
                                "
                            >
                                Technische informatie
                            </div>

                            <table
                                role="presentation"
                                width="100%"
                                class="data-table"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                style="
                                    width: 100%;
                                    table-layout: fixed;
                                "
                            >
                                <tr class="data-row">
                                    <td class="data-label">
                                        Onthouden
                                    </td>
                                    <td class="data-value">
                                        {{ $rememberLabel }}
                                    </td>
                                </tr>

                                <tr class="data-row">
                                    <td class="data-label">
                                        User-Agent
                                    </td>
                                    <td
                                        class="data-value technical-value"
                                        style="
                                            word-break: break-word;
                                            overflow-wrap: anywhere;
                                        "
                                    >
                                        {{ $userAgent }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- CTA -->
                    <tr>
                        <td
                            class="content-padding"
                            style="
                                padding-top: 34px;
                                padding-bottom: 12px;
                                padding-left: 34px;
                                padding-right: 34px;
                            "
                        >
                            <table
                                role="presentation"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                width="100%"
                            >
                                <tr>
                                    <td align="center">
                                        <a
                                            href="{{ $securityUrl }}"
                                            class="cta"
                                            style="
                                                display: inline-block;
                                                padding: 15px 26px;
                                                background: #0b0b0c;
                                                border: 1px solid #c99b55;
                                                color: #ffffff;
                                                font-size: 15px;
                                                line-height: 1.2;
                                                font-weight: 800;
                                                text-decoration: none;
                                                letter-spacing: 0.4px;
                                            "
                                        >
                                            Bekijk loginactiviteit
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Waarschuwing -->
                    <tr>
                        <td
                            class="content-padding"
                            style="
                                padding-top: 22px;
                                padding-bottom: 8px;
                                padding-left: 34px;
                                padding-right: 34px;
                            "
                        >
                            <p
                                style="
                                    margin: 0;
                                    color: #1d2025;
                                    font-size: 14px;
                                    line-height: 1.7;
                                    font-weight: 700;
                                "
                            >
                                Was jij dit?
                            </p>

                            <p
                                style="
                                    margin: 6px 0 0 0;
                                    color: #73777f;
                                    font-size: 14px;
                                    line-height: 1.7;
                                "
                            >
                                Dan hoef je niets te doen. Herken je deze login niet,
                                wijzig dan direct je wachtwoord en controleer je account.
                            </p>
                        </td>
                    </tr>

                    <!-- Privacy-uitleg -->
                    <tr>
                        <td
                            class="content-padding"
                            style="
                                padding-top: 18px;
                                padding-bottom: 8px;
                                padding-left: 34px;
                                padding-right: 34px;
                            "
                        >
                            <p
                                class="footer-copy"
                                style="
                                    margin: 0;
                                    color: #858991;
                                    font-size: 12px;
                                    line-height: 1.75;
                                "
                            >
                                De IP-locatie is een schatting en kan bijvoorbeeld
                                je internetprovider, VPN of mobiele provider tonen.
                                Precieze GPS-locatie wordt alleen opgeslagen wanneer
                                daarvoor in de browser toestemming is gegeven.
                                Browsers geven daarnaast niet altijd het exacte model
                                van een telefoon vrij.
                            </p>
                        </td>
                    </tr>

                    <!-- Gevoelige informatie -->
                    <tr>
                        <td
                            class="content-padding"
                            style="
                                padding-top: 14px;
                                padding-bottom: 30px;
                                padding-left: 34px;
                                padding-right: 34px;
                            "
                        >
                            <p
                                class="footer-copy"
                                style="
                                    margin: 0;
                                    color: #858991;
                                    font-size: 12px;
                                    line-height: 1.75;
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
                            style="
                                background: #0b0b0c;
                                padding: 18px 24px;
                                text-align: center;
                            "
                        >
                            <div
                                style="
                                    color: #c99b55;
                                    font-size: 11px;
                                    line-height: 1.5;
                                    font-weight: 700;
                                    letter-spacing: 2px;
                                    text-transform: uppercase;
                                "
                            >
                                Mashal Automotive
                            </div>

                            <div
                                style="
                                    margin-top: 6px;
                                    color: #9b9da2;
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
