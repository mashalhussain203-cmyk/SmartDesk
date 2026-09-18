<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
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
    "
>
    <div
        style="
            display: none;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            color: transparent;
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
            background: #08090b;
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
                        max-width: 640px;
                        overflow: hidden;
                        border: 1px solid #25272b;
                        border-radius: 24px;
                        background: #101215;
                    "
                >
                    <tr>
                        <td
                            style="
                                padding: 34px 34px 22px;
                                border-bottom: 1px solid #25272b;
                                background:
                                    linear-gradient(
                                        135deg,
                                        #151719,
                                        #0d0f11
                                    );
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
                                    Dit apparaat is nog niet eerder
                                    in je recente Mashal-loginhistorie gezien.
                                </div>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td
                            style="
                                padding: 28px 34px;
                            "
                        >
                            <table
                                role="presentation"
                                width="100%"
                                cellspacing="0"
                                cellpadding="0"
                                border="0"
                            >
                                <tr>
                                    <td
                                        style="
                                            padding: 13px 0;
                                            border-bottom: 1px solid #24262a;
                                            color: #72777e;
                                            font-size: 12px;
                                        "
                                    >
                                        Apparaat
                                    </td>

                                    <td
                                        align="right"
                                        style="
                                            padding: 13px 0;
                                            border-bottom: 1px solid #24262a;
                                            color: #ffffff;
                                            font-size: 13px;
                                            font-weight: 700;
                                        "
                                    >
                                        {{ $activity->deviceLabel() }}
                                    </td>
                                </tr>

                                <tr>
                                    <td
                                        style="
                                            padding: 13px 0;
                                            border-bottom: 1px solid #24262a;
                                            color: #72777e;
                                            font-size: 12px;
                                        "
                                    >
                                        Browser
                                    </td>

                                    <td
                                        align="right"
                                        style="
                                            padding: 13px 0;
                                            border-bottom: 1px solid #24262a;
                                            color: #ffffff;
                                            font-size: 13px;
                                            font-weight: 700;
                                        "
                                    >
                                        {{ $activity->browser }}
                                    </td>
                                </tr>

                                <tr>
                                    <td
                                        style="
                                            padding: 13px 0;
                                            border-bottom: 1px solid #24262a;
                                            color: #72777e;
                                            font-size: 12px;
                                        "
                                    >
                                        Besturingssysteem
                                    </td>

                                    <td
                                        align="right"
                                        style="
                                            padding: 13px 0;
                                            border-bottom: 1px solid #24262a;
                                            color: #ffffff;
                                            font-size: 13px;
                                            font-weight: 700;
                                        "
                                    >
                                        {{ $activity->operating_system }}
                                    </td>
                                </tr>

                                <tr>
                                    <td
                                        style="
                                            padding: 13px 0;
                                            border-bottom: 1px solid #24262a;
                                            color: #72777e;
                                            font-size: 12px;
                                        "
                                    >
                                        Geschatte locatie
                                    </td>

                                    <td
                                        align="right"
                                        style="
                                            padding: 13px 0;
                                            border-bottom: 1px solid #24262a;
                                            color: #ffffff;
                                            font-size: 13px;
                                            font-weight: 700;
                                        "
                                    >
                                        {{ $locationLabel }}
                                    </td>
                                </tr>

                                <tr>
                                    <td
                                        style="
                                            padding: 13px 0;
                                            border-bottom: 1px solid #24262a;
                                            color: #72777e;
                                            font-size: 12px;
                                        "
                                    >
                                        IP-adres
                                    </td>

                                    <td
                                        align="right"
                                        style="
                                            padding: 13px 0;
                                            border-bottom: 1px solid #24262a;
                                            color: #ffffff;
                                            font-size: 13px;
                                            font-weight: 700;
                                        "
                                    >
                                        {{ $activity->ip_address ?: 'Onbekend' }}
                                    </td>
                                </tr>

                                <tr>
                                    <td
                                        style="
                                            padding: 13px 0;
                                            border-bottom: 1px solid #24262a;
                                            color: #72777e;
                                            font-size: 12px;
                                        "
                                    >
                                        Loginmethode
                                    </td>

                                    <td
                                        align="right"
                                        style="
                                            padding: 13px 0;
                                            border-bottom: 1px solid #24262a;
                                            color: #ffffff;
                                            font-size: 13px;
                                            font-weight: 700;
                                        "
                                    >
                                        {{ $providerLabel }}
                                    </td>
                                </tr>

                                <tr>
                                    <td
                                        style="
                                            padding: 13px 0 0;
                                            color: #72777e;
                                            font-size: 12px;
                                        "
                                    >
                                        Tijdstip
                                    </td>

                                    <td
                                        align="right"
                                        style="
                                            padding: 13px 0 0;
                                            color: #ffffff;
                                            font-size: 13px;
                                            font-weight: 700;
                                        "
                                    >
                                        {{ $displayTime }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td
                            style="
                                padding: 0 34px 30px;
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

                    <tr>
                        <td
                            style="
                                padding: 24px 34px;
                                border-top: 1px solid #25272b;
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
                                De locatie is een schatting op basis van het
                                IP-adres en kan afwijken van je werkelijke locatie.
                                Het exacte model van een apparaat is niet altijd
                                beschikbaar via de browser.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
