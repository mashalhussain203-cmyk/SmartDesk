<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <meta
        http-equiv="X-UA-Compatible"
        content="IE=edge"
    >

    <title>
        Je veilige loginlink voor Mashal Automotive
    </title>
</head>

<body
    style="
        margin: 0;
        padding: 0;
        background: #08090b;
        color: #f6f4ef;
        font-family:
            Arial,
            Helvetica,
            sans-serif;
        -webkit-font-smoothing: antialiased;
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
            margin: 0;
            padding: 0;
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
                        max-width: 620px;
                    "
                >
                    {{-- BRAND --}}
                    <tr>
                        <td
                            align="center"
                            style="
                                padding-bottom: 22px;
                            "
                        >
                            <div
                                style="
                                    display: inline-block;
                                    width: 52px;
                                    height: 52px;
                                    line-height: 52px;
                                    border-radius: 16px;
                                    background:
                                        linear-gradient(
                                            145deg,
                                            #f1cc8b,
                                            #b67e3d
                                        );
                                    color: #15110c;
                                    font-size: 22px;
                                    font-weight: 900;
                                    text-align: center;
                                    box-shadow:
                                        0 14px 34px
                                        rgba(215,164,95,.20);
                                "
                            >
                                M
                            </div>

                            <div
                                style="
                                    margin-top: 12px;
                                    color: #ffffff;
                                    font-size: 20px;
                                    font-weight: 800;
                                    letter-spacing: -.4px;
                                "
                            >
                                Mashal Automotive
                            </div>

                            <div
                                style="
                                    margin-top: 4px;
                                    color: #8b9199;
                                    font-size: 10px;
                                    font-weight: 700;
                                    letter-spacing: 1.8px;
                                    text-transform: uppercase;
                                "
                            >
                                Secure account access
                            </div>
                        </td>
                    </tr>

                    {{-- CARD --}}
                    <tr>
                        <td
                            style="
                                border:
                                    1px solid
                                    rgba(255,255,255,.08);
                                border-radius: 24px;
                                overflow: hidden;
                                background: #101318;
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
                                "
                            >
                                {{-- GOLD TOP LINE --}}
                                <tr>
                                    <td
                                        style="
                                            height: 3px;
                                            background:
                                                linear-gradient(
                                                    90deg,
                                                    #9c6d34,
                                                    #f1c983,
                                                    #9c6d34
                                                );
                                        "
                                    ></td>
                                </tr>

                                {{-- CONTENT --}}
                                <tr>
                                    <td
                                        style="
                                            padding:
                                                34px
                                                34px
                                                30px;
                                        "
                                    >
                                        <div
                                            style="
                                                color: #d7a45f;
                                                font-size: 10px;
                                                font-weight: 800;
                                                letter-spacing: 1.7px;
                                                text-transform: uppercase;
                                            "
                                        >
                                            Veilige login
                                        </div>

                                        <h1
                                            style="
                                                margin:
                                                    10px
                                                    0
                                                    0;
                                                color: #ffffff;
                                                font-size: 30px;
                                                line-height: 1.15;
                                                font-weight: 900;
                                                letter-spacing: -1px;
                                            "
                                        >
                                            Log in met één veilige klik
                                        </h1>

                                        <p
                                            style="
                                                margin:
                                                    18px
                                                    0
                                                    0;
                                                color: #a2a7ad;
                                                font-size: 14px;
                                                line-height: 1.75;
                                            "
                                        >
                                            We hebben een veilige loginlink
                                            aangemaakt voor je
                                            Mashal Automotive-account.
                                        </p>

                                        @if (! empty($email))
                                            <div
                                                style="
                                                    margin-top: 18px;
                                                    padding: 14px 16px;
                                                    border:
                                                        1px solid
                                                        rgba(215,164,95,.14);
                                                    border-radius: 14px;
                                                    background:
                                                        rgba(215,164,95,.04);
                                                "
                                            >
                                                <div
                                                    style="
                                                        color: #7f858c;
                                                        font-size: 9px;
                                                        font-weight: 800;
                                                        letter-spacing: 1.2px;
                                                        text-transform: uppercase;
                                                    "
                                                >
                                                    Account
                                                </div>

                                                <div
                                                    style="
                                                        margin-top: 5px;
                                                        color: #efc985;
                                                        font-size: 13px;
                                                        font-weight: 700;
                                                        word-break: break-word;
                                                    "
                                                >
                                                    {{ $email }}
                                                </div>
                                            </div>
                                        @endif

                                        {{-- BUTTON --}}
                                        <table
                                            role="presentation"
                                            width="100%"
                                            cellspacing="0"
                                            cellpadding="0"
                                            border="0"
                                            style="
                                                width: 100%;
                                                margin-top: 26px;
                                            "
                                        >
                                            <tr>
                                                <td
                                                    align="center"
                                                >
                                                    <a
                                                        href="{{ $magicLinkUrl }}"
                                                        style="
                                                            display: inline-block;
                                                            padding:
                                                                16px
                                                                28px;
                                                            border-radius: 999px;
                                                            background:
                                                                linear-gradient(
                                                                    135deg,
                                                                    #f1cc8b,
                                                                    #ca914c
                                                                );
                                                            color: #15110c;
                                                            font-size: 13px;
                                                            font-weight: 900;
                                                            text-decoration: none;
                                                            letter-spacing: .2px;
                                                            box-shadow:
                                                                0 16px 36px
                                                                rgba(215,164,95,.20);
                                                        "
                                                    >
                                                        Inloggen bij Mashal Automotive
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>

                                        <p
                                            style="
                                                margin:
                                                    22px
                                                    0
                                                    0;
                                                color: #7d838a;
                                                font-size: 11px;
                                                line-height: 1.7;
                                                text-align: center;
                                            "
                                        >
                                            Deze link is
                                            <strong
                                                style="
                                                    color: #c8c5be;
                                                "
                                            >
                                                {{ $expiresInMinutes ?? 10 }}
                                                minuten geldig
                                            </strong>
                                            en kan maar één keer worden gebruikt.
                                        </p>

                                        {{-- SECURITY --}}
                                        <div
                                            style="
                                                margin-top: 26px;
                                                padding: 16px;
                                                border:
                                                    1px solid
                                                    rgba(101,213,154,.12);
                                                border-radius: 14px;
                                                background:
                                                    rgba(101,213,154,.035);
                                            "
                                        >
                                            <div
                                                style="
                                                    color: #9ce7bc;
                                                    font-size: 11px;
                                                    font-weight: 800;
                                                "
                                            >
                                                ✓ Beveiligde toegang
                                            </div>

                                            <p
                                                style="
                                                    margin:
                                                        7px
                                                        0
                                                        0;
                                                    color: #8b9199;
                                                    font-size: 10px;
                                                    line-height: 1.65;
                                                "
                                            >
                                                Deel deze e-mail of loginlink
                                                nooit met iemand anders.
                                                Mashal Automotive zal je nooit
                                                vragen om deze link door te sturen.
                                            </p>
                                        </div>

                                        {{-- FALLBACK URL --}}
                                        <div
                                            style="
                                                margin-top: 26px;
                                                padding-top: 22px;
                                                border-top:
                                                    1px solid
                                                    rgba(255,255,255,.07);
                                            "
                                        >
                                            <p
                                                style="
                                                    margin: 0;
                                                    color: #686e76;
                                                    font-size: 10px;
                                                    line-height: 1.65;
                                                "
                                            >
                                                Werkt de knop niet?
                                                Kopieer dan onderstaande link
                                                en plak hem in je browser:
                                            </p>

                                            <p
                                                style="
                                                    margin:
                                                        10px
                                                        0
                                                        0;
                                                    color: #b9894d;
                                                    font-size: 10px;
                                                    line-height: 1.6;
                                                    word-break: break-all;
                                                "
                                            >
                                                {{ $magicLinkUrl }}
                                            </p>
                                        </div>

                                        {{-- IGNORE --}}
                                        <p
                                            style="
                                                margin:
                                                    24px
                                                    0
                                                    0;
                                                color: #62686f;
                                                font-size: 10px;
                                                line-height: 1.7;
                                            "
                                        >
                                            Heb jij deze loginlink niet
                                            aangevraagd? Dan kun je deze e-mail
                                            veilig negeren. Er wordt niets
                                            gewijzigd zolang de link niet wordt
                                            gebruikt.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- FOOTER --}}
                    <tr>
                        <td
                            align="center"
                            style="
                                padding:
                                    24px
                                    18px
                                    8px;
                            "
                        >
                            <div
                                style="
                                    color: #5d6268;
                                    font-size: 10px;
                                    line-height: 1.65;
                                "
                            >
                                Mashal Automotive
                            </div>

                            <div
                                style="
                                    margin-top: 4px;
                                    color: #44494e;
                                    font-size: 9px;
                                    line-height: 1.6;
                                "
                            >
                                Deze e-mail is automatisch verzonden
                                voor beveiligde toegang tot je account.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>