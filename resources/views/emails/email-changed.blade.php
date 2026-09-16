<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Je SmartDesk-e-mailadres is gewijzigd</title>
</head>

<body style="
    margin: 0;
    padding: 0;
    background-color: #f3f5f7;
    font-family: Arial, Helvetica, sans-serif;
    color: #222222;
">

<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="
        width: 100%;
        background-color: #f3f5f7;
        padding: 45px 15px;
    "
>
    <tr>
        <td align="center">

            <!-- MAIN EMAIL CONTAINER -->
            <table
                width="100%"
                cellpadding="0"
                cellspacing="0"
                border="0"
                style="
                    width: 100%;
                    max-width: 640px;
                    background-color: #ffffff;
                    border-radius: 14px;
                    overflow: hidden;
                    box-shadow: 0 6px 25px rgba(0, 0, 0, 0.08);
                "
            >

                <!-- HEADER -->
                <tr>
                    <td
                        style="
                            background-color: #111111;
                            padding: 32px 35px;
                            text-align: center;
                        "
                    >
                        <div
                            style="
                                color: #ffffff;
                                font-size: 30px;
                                font-weight: 700;
                                letter-spacing: 1px;
                            "
                        >
                            SmartDesk
                        </div>

                        <div
                            style="
                                margin-top: 8px;
                                color: #aaaaaa;
                                font-size: 13px;
                                letter-spacing: 0.3px;
                            "
                        >
                            Accountbeveiliging &amp; persoonlijke gegevens
                        </div>
                    </td>
                </tr>

                <!-- CONTENT -->
                <tr>
                    <td style="padding: 42px 40px 35px;">

                        <h1
                            style="
                                margin: 0 0 18px;
                                font-size: 27px;
                                line-height: 1.3;
                                color: #111111;
                            "
                        >
                            Je e-mailadres is gewijzigd
                        </h1>

                        <p
                            style="
                                margin: 0 0 18px;
                                font-size: 16px;
                                line-height: 1.7;
                                color: #444444;
                            "
                        >
                            Hallo {{ $user->name }},
                        </p>

                        <p
                            style="
                                margin: 0 0 28px;
                                font-size: 16px;
                                line-height: 1.7;
                                color: #444444;
                            "
                        >
                            Het e-mailadres van je SmartDesk-account is zojuist gewijzigd.
                            Hieronder zie je welke wijziging is uitgevoerd.
                        </p>

                        <!-- CHANGE OVERVIEW -->
                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="
                                width: 100%;
                                margin: 0 0 28px;
                                background-color: #f7f8fa;
                                border: 1px solid #e3e6e8;
                                border-radius: 10px;
                            "
                        >
                            <tr>
                                <td style="padding: 24px;">

                                    <div
                                        style="
                                            margin-bottom: 20px;
                                            font-size: 15px;
                                            font-weight: 700;
                                            color: #111111;
                                        "
                                    >
                                        Wijziging van je e-mailadres
                                    </div>

                                    <!-- OLD EMAIL -->
                                    <table
                                        width="100%"
                                        cellpadding="0"
                                        cellspacing="0"
                                        border="0"
                                        style="margin-bottom: 14px;"
                                    >
                                        <tr>
                                            <td
                                                valign="top"
                                                style="
                                                    width: 42%;
                                                    padding: 8px 0;
                                                    font-size: 14px;
                                                    color: #777777;
                                                "
                                            >
                                                Oud e-mailadres
                                            </td>

                                            <td
                                                valign="top"
                                                align="right"
                                                style="
                                                    padding: 8px 0;
                                                    font-size: 14px;
                                                    font-weight: 600;
                                                    color: #333333;
                                                    word-break: break-word;
                                                "
                                            >
                                                {{ $oldEmail }}
                                            </td>
                                        </tr>
                                    </table>

                                    <!-- SEPARATOR -->
                                    <div
                                        style="
                                            height: 1px;
                                            background-color: #e1e4e7;
                                            margin: 2px 0 14px;
                                        "
                                    ></div>

                                    <!-- NEW EMAIL -->
                                    <table
                                        width="100%"
                                        cellpadding="0"
                                        cellspacing="0"
                                        border="0"
                                    >
                                        <tr>
                                            <td
                                                valign="top"
                                                style="
                                                    width: 42%;
                                                    padding: 8px 0;
                                                    font-size: 14px;
                                                    color: #777777;
                                                "
                                            >
                                                Nieuw e-mailadres
                                            </td>

                                            <td
                                                valign="top"
                                                align="right"
                                                style="
                                                    padding: 8px 0;
                                                    font-size: 14px;
                                                    font-weight: 700;
                                                    color: #111111;
                                                    word-break: break-word;
                                                "
                                            >
                                                {{ $newEmail }}
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <!-- VERIFICATION NOTICE -->
                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="
                                width: 100%;
                                margin: 0 0 28px;
                                background-color: #eef5ff;
                                border-left: 4px solid #2563eb;
                            "
                        >
                            <tr>
                                <td style="padding: 18px 20px;">

                                    <div
                                        style="
                                            margin-bottom: 7px;
                                            font-size: 15px;
                                            font-weight: 700;
                                            color: #174ea6;
                                        "
                                    >
                                        Nieuwe verificatie vereist
                                    </div>

                                    <div
                                        style="
                                            font-size: 14px;
                                            line-height: 1.7;
                                            color: #3f5f8f;
                                        "
                                    >
                                        Omdat je e-mailadres is gewijzigd, moet je
                                        het nieuwe e-mailadres opnieuw verifiëren.
                                        SmartDesk heeft hiervoor een verificatiecode
                                        naar je nieuwe e-mailadres gestuurd.
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <h2
                            style="
                                margin: 0 0 12px;
                                font-size: 19px;
                                color: #111111;
                            "
                        >
                            Wat betekent dit?
                        </h2>

                        <p
                            style="
                                margin: 0 0 15px;
                                font-size: 15px;
                                line-height: 1.7;
                                color: #555555;
                            "
                        >
                            Je SmartDesk-account gebruikt vanaf nu het nieuwe
                            e-mailadres voor accountcommunicatie en belangrijke meldingen.
                        </p>

                        <p
                            style="
                                margin: 0 0 28px;
                                font-size: 15px;
                                line-height: 1.7;
                                color: #555555;
                            "
                        >
                            Belangrijke berichten, zoals bestelbevestigingen
                            en beveiligingsmeldingen, worden voortaan naar
                            je nieuwe e-mailadres verzonden.
                        </p>

                        <!-- SECURITY WARNING -->
                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="
                                width: 100%;
                                margin: 0 0 28px;
                                background-color: #fff4f4;
                                border: 1px solid #f1cccc;
                                border-left: 4px solid #c62828;
                            "
                        >
                            <tr>
                                <td style="padding: 20px;">

                                    <div
                                        style="
                                            margin-bottom: 8px;
                                            font-size: 15px;
                                            font-weight: 700;
                                            color: #a51d1d;
                                        "
                                    >
                                        Heb jij dit niet gedaan?
                                    </div>

                                    <div
                                        style="
                                            font-size: 14px;
                                            line-height: 1.7;
                                            color: #713030;
                                        "
                                    >
                                        Als jij je e-mailadres niet hebt gewijzigd,
                                        kan iemand anders toegang hebben tot je account.
                                        Wijzig in dat geval onmiddellijk je wachtwoord
                                        en neem contact op met SmartDesk.
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <!-- ACCOUNT DETAILS -->
                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="
                                width: 100%;
                                margin-bottom: 28px;
                                border-top: 1px solid #eeeeee;
                                border-bottom: 1px solid #eeeeee;
                            "
                        >
                            <tr>
                                <td style="padding: 18px 0;">

                                    <div
                                        style="
                                            font-size: 13px;
                                            color: #888888;
                                            margin-bottom: 5px;
                                        "
                                    >
                                        Accountnaam
                                    </div>

                                    <div
                                        style="
                                            font-size: 15px;
                                            font-weight: 600;
                                            color: #222222;
                                        "
                                    >
                                        {{ $user->name }}
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding: 0 0 18px;">

                                    <div
                                        style="
                                            font-size: 13px;
                                            color: #888888;
                                            margin-bottom: 5px;
                                        "
                                    >
                                        Oud e-mailadres
                                    </div>

                                    <div
                                        style="
                                            font-size: 15px;
                                            font-weight: 600;
                                            color: #222222;
                                            word-break: break-word;
                                        "
                                    >
                                        {{ $oldEmail }}
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td style="padding: 0 0 18px;">

                                    <div
                                        style="
                                            font-size: 13px;
                                            color: #888888;
                                            margin-bottom: 5px;
                                        "
                                    >
                                        Nieuw e-mailadres
                                    </div>

                                    <div
                                        style="
                                            font-size: 15px;
                                            font-weight: 600;
                                            color: #222222;
                                            word-break: break-word;
                                        "
                                    >
                                        {{ $newEmail }}
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <p
                            style="
                                margin: 0 0 18px;
                                font-size: 15px;
                                line-height: 1.7;
                                color: #555555;
                            "
                        >
                            Als je deze wijziging zelf hebt uitgevoerd,
                            hoef je verder niets te doen. Je hoeft alleen nog
                            je nieuwe e-mailadres te verifiëren.
                        </p>

                        <p
                            style="
                                margin: 0;
                                font-size: 15px;
                                line-height: 1.7;
                                color: #555555;
                            "
                        >
                            Met vriendelijke groet,<br>

                            <strong style="color: #111111;">
                                Het SmartDesk-team
                            </strong>
                        </p>
                    </td>
                </tr>

                <!-- FOOTER -->
                <tr>
                    <td
                        style="
                            padding: 27px 35px;
                            background-color: #f8f9fa;
                            border-top: 1px solid #eeeeee;
                            text-align: center;
                        "
                    >
                        <div
                            style="
                                font-size: 12px;
                                line-height: 1.6;
                                color: #888888;
                            "
                        >
                            Deze e-mail is automatisch verzonden omdat
                            het e-mailadres van je SmartDesk-account is gewijzigd.
                        </div>

                        <div
                            style="
                                margin-top: 10px;
                                font-size: 12px;
                                color: #aaaaaa;
                            "
                        >
                            © {{ date('Y') }} SmartDesk.
                            Alle rechten voorbehouden.
                        </div>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>

