```blade
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Je SmartDesk-account is gewijzigd</title>
</head>

<body style="
    margin: 0;
    padding: 0;
    background-color: #f4f6f8;
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
        background-color: #f4f6f8;
        padding: 40px 15px;
    "
>
    <tr>
        <td align="center">

            <!-- MAIN CONTAINER -->
            <table
                width="100%"
                cellpadding="0"
                cellspacing="0"
                border="0"
                style="
                    width: 100%;
                    max-width: 620px;
                    background-color: #ffffff;
                    border-radius: 12px;
                    overflow: hidden;
                    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
                "
            >

                <!-- HEADER -->
                <tr>
                    <td
                        style="
                            background-color: #111111;
                            padding: 30px 35px;
                            text-align: center;
                        "
                    >
                        <div
                            style="
                                font-size: 28px;
                                font-weight: 700;
                                letter-spacing: 1px;
                                color: #ffffff;
                            "
                        >
                            SmartDesk
                        </div>

                        <div
                            style="
                                margin-top: 8px;
                                font-size: 13px;
                                color: #bdbdbd;
                            "
                        >
                            Jouw account, jouw gegevens
                        </div>
                    </td>
                </tr>

                <!-- CONTENT -->
                <tr>
                    <td style="padding: 40px 40px 30px;">

                        <h1
                            style="
                                margin: 0 0 18px;
                                font-size: 26px;
                                line-height: 1.3;
                                color: #111111;
                            "
                        >
                            Je accountgegevens zijn gewijzigd
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
                                margin: 0 0 25px;
                                font-size: 16px;
                                line-height: 1.7;
                                color: #444444;
                            "
                        >
                            Je SmartDesk-accountgegevens zijn zojuist succesvol aangepast.
                            Hieronder zie je een overzicht van de wijziging.
                        </p>

                        <!-- ACCOUNT CHANGE CARD -->
                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="
                                width: 100%;
                                margin: 25px 0;
                                background-color: #f7f8fa;
                                border: 1px solid #e5e7eb;
                                border-radius: 8px;
                            "
                        >
                            <tr>
                                <td style="padding: 22px;">

                                    <div
                                        style="
                                            margin-bottom: 15px;
                                            font-size: 14px;
                                            font-weight: 700;
                                            color: #111111;
                                        "
                                    >
                                        Wijziging van je account
                                    </div>

                                    <table
                                        width="100%"
                                        cellpadding="0"
                                        cellspacing="0"
                                        border="0"
                                        style="width: 100%;"
                                    >

                                        <!-- NAME -->
                                        @if(isset($oldName) && $oldName !== $user->name)
                                            <tr>
                                                <td
                                                    style="
                                                        padding: 7px 0;
                                                        font-size: 14px;
                                                        color: #777777;
                                                    "
                                                >
                                                    Oude naam
                                                </td>

                                                <td
                                                    align="right"
                                                    style="
                                                        padding: 7px 0;
                                                        font-size: 14px;
                                                        font-weight: 600;
                                                        color: #222222;
                                                    "
                                                >
                                                    {{ $oldName }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <td
                                                    style="
                                                        padding: 7px 0;
                                                        font-size: 14px;
                                                        color: #777777;
                                                    "
                                                >
                                                    Nieuwe naam
                                                </td>

                                                <td
                                                    align="right"
                                                    style="
                                                        padding: 7px 0;
                                                        font-size: 14px;
                                                        font-weight: 600;
                                                        color: #222222;
                                                    "
                                                >
                                                    {{ $user->name }}
                                                </td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td
                                                    style="
                                                        padding: 7px 0;
                                                        font-size: 14px;
                                                        color: #777777;
                                                    "
                                                >
                                                    Naam
                                                </td>

                                                <td
                                                    align="right"
                                                    style="
                                                        padding: 7px 0;
                                                        font-size: 14px;
                                                        font-weight: 600;
                                                        color: #222222;
                                                    "
                                                >
                                                    {{ $user->name }}
                                                </td>
                                            </tr>
                                        @endif

                                        <!-- EMAIL -->
                                        @if(isset($oldEmail) && strtolower($oldEmail) !== strtolower($user->email))
                                            <tr>
                                                <td
                                                    style="
                                                        padding: 7px 0;
                                                        font-size: 14px;
                                                        color: #777777;
                                                    "
                                                >
                                                    Oud e-mailadres
                                                </td>

                                                <td
                                                    align="right"
                                                    style="
                                                        padding: 7px 0;
                                                        font-size: 14px;
                                                        font-weight: 600;
                                                        color: #222222;
                                                        word-break: break-word;
                                                    "
                                                >
                                                    {{ $oldEmail }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <td
                                                    style="
                                                        padding: 7px 0;
                                                        font-size: 14px;
                                                        color: #777777;
                                                    "
                                                >
                                                    Nieuw e-mailadres
                                                </td>

                                                <td
                                                    align="right"
                                                    style="
                                                        padding: 7px 0;
                                                        font-size: 14px;
                                                        font-weight: 600;
                                                        color: #222222;
                                                        word-break: break-word;
                                                    "
                                                >
                                                    {{ $user->email }}
                                                </td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td
                                                    style="
                                                        padding: 7px 0;
                                                        font-size: 14px;
                                                        color: #777777;
                                                    "
                                                >
                                                    E-mailadres
                                                </td>

                                                <td
                                                    align="right"
                                                    style="
                                                        padding: 7px 0;
                                                        font-size: 14px;
                                                        font-weight: 600;
                                                        color: #222222;
                                                        word-break: break-word;
                                                    "
                                                >
                                                    {{ $user->email }}
                                                </td>
                                            </tr>
                                        @endif
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <p
                            style="
                                margin: 0 0 20px;
                                font-size: 15px;
                                line-height: 1.7;
                                color: #555555;
                            "
                        >
                            Was jij het die deze wijziging heeft uitgevoerd?
                            Dan hoef je niets te doen.
                        </p>

                        <!-- EMAIL VERIFICATION MESSAGE -->
                        @if(isset($emailChanged) && $emailChanged)
                            <table
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                style="
                                    width: 100%;
                                    margin: 25px 0;
                                    background-color: #eef6ff;
                                    border-left: 4px solid #2563eb;
                                "
                            >
                                <tr>
                                    <td style="padding: 16px 18px;">

                                        <div
                                            style="
                                                font-size: 14px;
                                                font-weight: 700;
                                                color: #1e40af;
                                                margin-bottom: 6px;
                                            "
                                        >
                                            E-mailadres gewijzigd
                                        </div>

                                        <div
                                            style="
                                                font-size: 14px;
                                                line-height: 1.6;
                                                color: #374f7a;
                                            "
                                        >
                                            Je e-mailadres is gewijzigd.
                                            Daarom moet je nieuwe e-mailadres opnieuw worden geverifieerd.
                                            Controleer je nieuwe inbox voor de verificatiecode van SmartDesk.
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        @endif

                        <!-- SECURITY WARNING -->
                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="
                                width: 100%;
                                margin: 25px 0;
                                background-color: #fff8e6;
                                border-left: 4px solid #e0a100;
                            "
                        >
                            <tr>
                                <td style="padding: 16px 18px;">

                                    <div
                                        style="
                                            font-size: 14px;
                                            font-weight: 700;
                                            color: #7a5700;
                                            margin-bottom: 6px;
                                        "
                                    >
                                        Beveiligingsmelding
                                    </div>

                                    <div
                                        style="
                                            font-size: 14px;
                                            line-height: 1.6;
                                            color: #6b5a2a;
                                        "
                                    >
                                        Heb je deze wijziging niet zelf uitgevoerd?
                                        Wijzig dan zo snel mogelijk je wachtwoord
                                        en neem contact op met SmartDesk.
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <p
                            style="
                                margin: 25px 0 0;
                                font-size: 14px;
                                line-height: 1.6;
                                color: #777777;
                            "
                        >
                            Let op: deze e-mail gaat alleen over wijzigingen
                            van je accountgegevens. Je wachtwoord wordt nooit
                            in een e-mail weergegeven.
                        </p>

                        <p
                            style="
                                margin: 25px 0 0;
                                font-size: 15px;
                                line-height: 1.7;
                                color: #555555;
                            "
                        >
                            Bedankt dat je gebruikmaakt van SmartDesk.
                        </p>

                        <p
                            style="
                                margin: 20px 0 0;
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
                            background-color: #f8f9fa;
                            border-top: 1px solid #eeeeee;
                            padding: 25px 35px;
                            text-align: center;
                        "
                    >
                        <div
                            style="
                                font-size: 13px;
                                color: #777777;
                                line-height: 1.6;
                            "
                        >
                            Deze e-mail is automatisch verzonden
                            omdat je SmartDesk-account is gewijzigd.
                        </div>

                        <div
                            style="
                                margin-top: 10px;
                                font-size: 12px;
                                color: #999999;
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
```
