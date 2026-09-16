<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Je verificatiecode voor SmartDesk
    </title>
</head>

<body
    style="
        margin: 0;
        padding: 0;
        background-color: #f3f5f7;
        font-family: Arial, Helvetica, sans-serif;
        color: #222222;
    "
>

<table
    role="presentation"
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

            <table
                role="presentation"
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

                {{-- ========================================================= --}}
                {{-- HEADER                                                     --}}
                {{-- ========================================================= --}}

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
                            E-mailverificatie &amp; accountbeveiliging
                        </div>

                    </td>
                </tr>


                {{-- ========================================================= --}}
                {{-- CONTENT                                                    --}}
                {{-- ========================================================= --}}

                <tr>
                    <td
                        style="
                            padding: 42px 40px 35px;
                        "
                    >

                        {{-- ICON --}}

                        <div
                            style="
                                width: 64px;
                                height: 64px;
                                margin: 0 auto 25px;
                                border-radius: 50%;
                                background-color: #eef5ff;
                                text-align: center;
                                line-height: 64px;
                                font-size: 30px;
                            "
                        >
                            ✉
                        </div>


                        {{-- TITLE --}}

                        <h1
                            style="
                                margin: 0 0 18px;
                                text-align: center;
                                font-size: 27px;
                                line-height: 1.3;
                                color: #111111;
                            "
                        >
                            Bevestig je e-mailadres
                        </h1>


                        {{-- GREETING --}}

                        <p
                            style="
                                margin: 0 0 18px;
                                font-size: 16px;
                                line-height: 1.7;
                                color: #444444;
                            "
                        >
                            Beste {{ $user->name }},
                        </p>


                        {{-- INTRO --}}

                        <p
                            style="
                                margin: 0 0 25px;
                                font-size: 16px;
                                line-height: 1.7;
                                color: #444444;
                            "
                        >
                            Gebruik de onderstaande verificatiecode om je
                            e-mailadres voor SmartDesk te bevestigen.
                        </p>


                        {{-- ========================================================= --}}
                        {{-- VERIFICATION CODE                                          --}}
                        {{-- ========================================================= --}}

                        <table
                            role="presentation"
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
                                <td
                                    style="
                                        padding: 28px 20px;
                                        text-align: center;
                                    "
                                >

                                    <div
                                        style="
                                            margin-bottom: 10px;
                                            font-size: 13px;
                                            font-weight: 600;
                                            text-transform: uppercase;
                                            letter-spacing: 1px;
                                            color: #777777;
                                        "
                                    >
                                        Jouw verificatiecode
                                    </div>

                                    <div
                                        style="
                                            font-size: 36px;
                                            line-height: 1.2;
                                            font-weight: 700;
                                            letter-spacing: 8px;
                                            color: #111111;
                                        "
                                    >
                                        {{ $code }}
                                    </div>

                                </td>
                            </tr>
                        </table>


                        {{-- ========================================================= --}}
                        {{-- EXPIRATION NOTICE                                          --}}
                        {{-- ========================================================= --}}

                        <table
                            role="presentation"
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="
                                width: 100%;
                                margin: 0 0 28px;
                                background-color: #fff8e6;
                                border-left: 4px solid #e0a100;
                            "
                        >
                            <tr>
                                <td
                                    style="
                                        padding: 18px 20px;
                                    "
                                >

                                    <div
                                        style="
                                            margin-bottom: 7px;
                                            font-size: 14px;
                                            font-weight: 700;
                                            color: #7a5700;
                                        "
                                    >
                                        Let op
                                    </div>

                                    <div
                                        style="
                                            font-size: 14px;
                                            line-height: 1.7;
                                            color: #6b5a2a;
                                        "
                                    >
                                        Deze verificatiecode is 15 minuten geldig.
                                        Daarna moet je een nieuwe code aanvragen.
                                    </div>

                                </td>
                            </tr>
                        </table>


                        {{-- ========================================================= --}}
                        {{-- INSTRUCTIES                                                --}}
                        {{-- ========================================================= --}}

                        <h2
                            style="
                                margin: 0 0 12px;
                                font-size: 19px;
                                color: #111111;
                            "
                        >
                            Wat moet je doen?
                        </h2>

                        <p
                            style="
                                margin: 0 0 15px;
                                font-size: 15px;
                                line-height: 1.7;
                                color: #555555;
                            "
                        >
                            Ga naar de verificatiepagina van SmartDesk en vul
                            daar je e-mailadres en de bovenstaande code in.
                        </p>

                        <p
                            style="
                                margin: 0 0 28px;
                                font-size: 15px;
                                line-height: 1.7;
                                color: #555555;
                            "
                        >
                            Zodra de code correct is ingevoerd, wordt je
                            e-mailadres als geverifieerd gemarkeerd.
                        </p>


                        {{-- ========================================================= --}}
                        {{-- ACCOUNT DETAILS                                            --}}
                        {{-- ========================================================= --}}

                        <table
                            role="presentation"
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
                                <td
                                    style="
                                        padding: 18px 0;
                                    "
                                >

                                    <div
                                        style="
                                            margin-bottom: 5px;
                                            font-size: 13px;
                                            color: #888888;
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
                                <td
                                    style="
                                        padding: 0 0 18px;
                                    "
                                >

                                    <div
                                        style="
                                            margin-bottom: 5px;
                                            font-size: 13px;
                                            color: #888888;
                                        "
                                    >
                                        E-mailadres
                                    </div>

                                    <div
                                        style="
                                            font-size: 15px;
                                            font-weight: 600;
                                            color: #222222;
                                            word-break: break-word;
                                        "
                                    >
                                        {{ $user->email }}
                                    </div>

                                </td>
                            </tr>

                        </table>


                        {{-- ========================================================= --}}
                        {{-- SECURITY WARNING                                           --}}
                        {{-- ========================================================= --}}

                        <table
                            role="presentation"
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
                                <td
                                    style="
                                        padding: 20px;
                                    "
                                >

                                    <div
                                        style="
                                            margin-bottom: 8px;
                                            font-size: 15px;
                                            font-weight: 700;
                                            color: #a51d1d;
                                        "
                                    >
                                        Heb jij deze code niet aangevraagd?
                                    </div>

                                    <div
                                        style="
                                            font-size: 14px;
                                            line-height: 1.7;
                                            color: #713030;
                                        "
                                    >
                                        Als jij geen verificatiecode hebt aangevraagd,
                                        kun je deze e-mail negeren.

                                        Deel deze code nooit met anderen.
                                    </div>

                                </td>
                            </tr>
                        </table>


                        {{-- ========================================================= --}}
                        {{-- SECURITY INFO                                              --}}
                        {{-- ========================================================= --}}

                        <table
                            role="presentation"
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="
                                width: 100%;
                                margin-bottom: 28px;
                                background-color: #f7f8fa;
                                border-left: 4px solid #555555;
                            "
                        >
                            <tr>
                                <td
                                    style="
                                        padding: 18px 20px;
                                    "
                                >

                                    <div
                                        style="
                                            margin-bottom: 7px;
                                            font-size: 14px;
                                            font-weight: 700;
                                            color: #222222;
                                        "
                                    >
                                        Veiligheid
                                    </div>

                                    <div
                                        style="
                                            font-size: 14px;
                                            line-height: 1.7;
                                            color: #666666;
                                        "
                                    >
                                        SmartDesk zal je nooit vragen om je
                                        verificatiecode via e-mail, chat of telefoon
                                        met iemand te delen.
                                    </div>

                                </td>
                            </tr>
                        </table>


                        {{-- ========================================================= --}}
                        {{-- CLOSING                                                    --}}
                        {{-- ========================================================= --}}

                        <p
                            style="
                                margin: 0 0 18px;
                                font-size: 15px;
                                line-height: 1.7;
                                color: #555555;
                            "
                        >
                            Gebruik de code hierboven om je registratie of
                            wijziging van je e-mailadres af te ronden.
                        </p>

                        <p
                            style="
                                margin: 0;
                                font-size: 15px;
                                line-height: 1.7;
                                color: #555555;
                            "
                        >
                            Met vriendelijke groet,
                            <br>

                            <strong
                                style="
                                    color: #111111;
                                "
                            >
                                Het SmartDesk-team
                            </strong>
                        </p>

                    </td>
                </tr>


                {{-- ========================================================= --}}
                {{-- FOOTER                                                     --}}
                {{-- ========================================================= --}}

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
                            er een verificatiecode voor je SmartDesk-account
                            is aangevraagd.
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

