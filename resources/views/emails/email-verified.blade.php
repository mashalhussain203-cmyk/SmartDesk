<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Je e-mailadres is geverifieerd - SmartDesk</title>
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

            {{-- MAIN EMAIL CONTAINER --}}
            <table
                width="100%"
                cellpadding="0"
                cellspacing="0"
                border="0"
                style="
                    max-width: 640px;
                    width: 100%;
                    background-color: #ffffff;
                    border-radius: 14px;
                    overflow: hidden;
                    box-shadow: 0 6px 25px rgba(0,0,0,0.08);
                "
            >

                {{-- HEADER --}}
                <tr>
                    <td
                        style="
                            background-color: #111111;
                            padding: 32px 35px;
                            text-align: center;
                        "
                    >

                        <div style="
                            color: #ffffff;
                            font-size: 30px;
                            font-weight: 700;
                            letter-spacing: 1px;
                        ">
                            SmartDesk
                        </div>

                        <div style="
                            margin-top: 8px;
                            color: #aaaaaa;
                            font-size: 13px;
                            letter-spacing: 0.3px;
                        ">
                            Accountbeveiliging & persoonlijke gegevens
                        </div>

                    </td>
                </tr>


                {{-- CONTENT --}}
                <tr>
                    <td style="
                        padding: 42px 40px 35px;
                    ">

                        {{-- SUCCESS ICON --}}
                        <div style="
                            width: 64px;
                            height: 64px;
                            margin: 0 auto 25px;
                            border-radius: 50%;
                            background-color: #e8f7ee;
                            text-align: center;
                            line-height: 64px;
                            font-size: 30px;
                            color: #187a42;
                        ">
                            ✓
                        </div>


                        {{-- TITLE --}}
                        <h1 style="
                            margin: 0 0 18px;
                            text-align: center;
                            font-size: 27px;
                            line-height: 1.3;
                            color: #111111;
                        ">
                            Je e-mailadres is geverifieerd
                        </h1>


                        {{-- GREETING --}}
                        <p style="
                            margin: 0 0 18px;
                            font-size: 16px;
                            line-height: 1.7;
                            color: #444444;
                        ">
                            Hallo {{ $user->name }},
                        </p>


                        <p style="
                            margin: 0 0 28px;
                            font-size: 16px;
                            line-height: 1.7;
                            color: #444444;
                        ">
                            Goed nieuws! Je e-mailadres is succesvol geverifieerd.
                            Je SmartDesk-account is nu volledig bevestigd.
                        </p>


                        {{-- VERIFIED STATUS --}}
                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="
                                margin: 0 0 28px;
                                background-color: #f0faf4;
                                border: 1px solid #ccebd8;
                                border-radius: 10px;
                            "
                        >
                            <tr>
                                <td style="
                                    padding: 24px;
                                    text-align: center;
                                ">

                                    <div style="
                                        font-size: 16px;
                                        font-weight: 700;
                                        color: #187a42;
                                        margin-bottom: 8px;
                                    ">
                                        ✓ E-mailadres geverifieerd
                                    </div>

                                    <div style="
                                        font-size: 14px;
                                        line-height: 1.6;
                                        color: #4d705b;
                                        word-break: break-word;
                                    ">
                                        {{ $user->email }}
                                    </div>

                                </td>
                            </tr>
                        </table>


                        {{-- WHAT THIS MEANS --}}
                        <h2 style="
                            margin: 0 0 12px;
                            font-size: 19px;
                            color: #111111;
                        ">
                            Wat betekent dit?
                        </h2>


                        <p style="
                            margin: 0 0 15px;
                            font-size: 15px;
                            line-height: 1.7;
                            color: #555555;
                        ">
                            SmartDesk weet nu dat je toegang hebt tot het
                            e-mailadres dat aan je account gekoppeld is.
                        </p>


                        <p style="
                            margin: 0 0 28px;
                            font-size: 15px;
                            line-height: 1.7;
                            color: #555555;
                        ">
                            Je kunt vanaf nu gebruikmaken van functies waarvoor
                            een geverifieerd e-mailadres vereist is, waaronder
                            het plaatsen van bestellingen.
                        </p>


                        {{-- ACCOUNT DETAILS --}}
                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="
                                margin-bottom: 28px;
                                border-top: 1px solid #eeeeee;
                                border-bottom: 1px solid #eeeeee;
                            "
                        >

                            <tr>
                                <td style="
                                    padding: 18px 0;
                                ">

                                    <div style="
                                        margin-bottom: 5px;
                                        font-size: 13px;
                                        color: #888888;
                                    ">
                                        Accountnaam
                                    </div>

                                    <div style="
                                        font-size: 15px;
                                        font-weight: 600;
                                        color: #222222;
                                    ">
                                        {{ $user->name }}
                                    </div>

                                </td>
                            </tr>


                            <tr>
                                <td style="
                                    padding: 0 0 18px;
                                ">

                                    <div style="
                                        margin-bottom: 5px;
                                        font-size: 13px;
                                        color: #888888;
                                    ">
                                        Geverifieerd e-mailadres
                                    </div>

                                    <div style="
                                        font-size: 15px;
                                        font-weight: 600;
                                        color: #222222;
                                        word-break: break-word;
                                    ">
                                        {{ $user->email }}
                                    </div>

                                </td>
                            </tr>


                            @if ($user->email_verified_at)

                                <tr>
                                    <td style="
                                        padding: 0 0 18px;
                                    ">

                                        <div style="
                                            margin-bottom: 5px;
                                            font-size: 13px;
                                            color: #888888;
                                        ">
                                            Verificatiedatum
                                        </div>

                                        <div style="
                                            font-size: 15px;
                                            font-weight: 600;
                                            color: #222222;
                                        ">
                                            {{ $user->email_verified_at->format('d-m-Y H:i') }}
                                        </div>

                                    </td>
                                </tr>

                            @endif

                        </table>


                        {{-- SECURITY INFORMATION --}}
                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="
                                margin: 0 0 28px;
                                background-color: #f7f8fa;
                                border-left: 4px solid #555555;
                            "
                        >
                            <tr>
                                <td style="
                                    padding: 18px 20px;
                                ">

                                    <div style="
                                        margin-bottom: 7px;
                                        font-size: 14px;
                                        font-weight: 700;
                                        color: #222222;
                                    ">
                                        Beveiliging
                                    </div>

                                    <div style="
                                        font-size: 14px;
                                        line-height: 1.7;
                                        color: #666666;
                                    ">
                                        Heb je deze verificatie niet zelf uitgevoerd?
                                        Controleer dan je account en wijzig indien
                                        nodig je wachtwoord.
                                    </div>

                                </td>
                            </tr>
                        </table>


                        {{-- CLOSING --}}
                        <p style="
                            margin: 0 0 18px;
                            font-size: 15px;
                            line-height: 1.7;
                            color: #555555;
                        ">
                            Je account is klaar voor gebruik.
                            Bedankt dat je voor SmartDesk kiest.
                        </p>


                        <p style="
                            margin: 0;
                            font-size: 15px;
                            line-height: 1.7;
                            color: #555555;
                        ">
                            Met vriendelijke groet,<br>

                            <strong style="
                                color: #111111;
                            ">
                                Het SmartDesk-team
                            </strong>
                        </p>

                    </td>
                </tr>


                {{-- FOOTER --}}
                <tr>
                    <td style="
                        padding: 27px 35px;
                        background-color: #f8f9fa;
                        border-top: 1px solid #eeeeee;
                        text-align: center;
                    ">

                        <div style="
                            font-size: 12px;
                            line-height: 1.6;
                            color: #888888;
                        ">
                            Deze e-mail is automatisch verzonden nadat je
                            e-mailadres succesvol is geverifieerd.
                        </div>

                        <div style="
                            margin-top: 10px;
                            font-size: 12px;
                            color: #aaaaaa;
                        ">
                            © {{ date('Y') }} SmartDesk
                        </div>

                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>
