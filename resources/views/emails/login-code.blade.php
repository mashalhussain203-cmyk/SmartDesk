<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Je inlogcode | Mashal Automotive</title>
</head>

<body
    style="
        margin:0;
        padding:0;
        background:#08090b;
        font-family:Arial, Helvetica, sans-serif;
        color:#ffffff;
    "
>
    <table
        role="presentation"
        width="100%"
        cellspacing="0"
        cellpadding="0"
        border="0"
        style="
            width:100%;
            margin:0;
            padding:0;
            background:#08090b;
        "
    >
        <tr>
            <td
                align="center"
                style="
                    padding:40px 16px;
                "
            >
                <table
                    role="presentation"
                    width="100%"
                    cellspacing="0"
                    cellpadding="0"
                    border="0"
                    style="
                        width:100%;
                        max-width:620px;
                        border-collapse:separate;
                        background:#0d0f12;
                        border:1px solid rgba(255,255,255,.08);
                        border-radius:22px;
                        overflow:hidden;
                    "
                >
                    <tr>
                        <td
                            style="
                                padding:0;
                            "
                        >
                            <div
                                style="
                                    padding:34px 34px 28px;
                                    background:
                                        linear-gradient(
                                            145deg,
                                            rgba(215,164,95,.11),
                                            rgba(255,255,255,.02)
                                        );
                                    border-bottom:1px solid rgba(255,255,255,.07);
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
                                            valign="middle"
                                            style="
                                                width:56px;
                                            "
                                        >
                                            <div
                                                style="
                                                    width:48px;
                                                    height:48px;
                                                    line-height:48px;
                                                    text-align:center;
                                                    border-radius:14px;
                                                    background:
                                                        linear-gradient(
                                                            145deg,
                                                            #f0ca86,
                                                            #b67e3d
                                                        );
                                                    color:#15110c;
                                                    font-size:22px;
                                                    font-weight:900;
                                                "
                                            >
                                                M
                                            </div>
                                        </td>

                                        <td
                                            valign="middle"
                                            style="
                                                padding-left:12px;
                                            "
                                        >
                                            <div
                                                style="
                                                    color:#ffffff;
                                                    font-size:20px;
                                                    font-weight:800;
                                                    line-height:1.1;
                                                "
                                            >
                                                Mashal Automotive
                                            </div>

                                            <div
                                                style="
                                                    margin-top:5px;
                                                    color:#8a9097;
                                                    font-size:10px;
                                                    font-weight:700;
                                                    letter-spacing:.14em;
                                                    text-transform:uppercase;
                                                "
                                            >
                                                Secure sign-in
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td
                            style="
                                padding:38px 34px 34px;
                            "
                        >
                            <div
                                style="
                                    color:#c89c5d;
                                    font-size:10px;
                                    font-weight:800;
                                    letter-spacing:.16em;
                                    text-transform:uppercase;
                                    margin-bottom:10px;
                                "
                            >
                                Inlogverificatie
                            </div>

                            <h1
                                style="
                                    margin:0;
                                    color:#ffffff;
                                    font-size:30px;
                                    line-height:1.15;
                                    font-weight:900;
                                    letter-spacing:-.03em;
                                "
                            >
                                Je inlogcode
                            </h1>

                            <p
                                style="
                                    margin:18px 0 0;
                                    color:#a2a7ad;
                                    font-size:14px;
                                    line-height:1.8;
                                "
                            >
                                Gebruik onderstaande 6-cijferige code om veilig
                                in te loggen op je Mashal Automotive-account.
                            </p>

                            <div
                                style="
                                    margin:30px 0;
                                    padding:24px 20px;
                                    text-align:center;
                                    border:1px solid rgba(215,164,95,.20);
                                    border-radius:18px;
                                    background:
                                        linear-gradient(
                                            145deg,
                                            rgba(215,164,95,.08),
                                            rgba(255,255,255,.025)
                                        );
                                "
                            >
                                <div
                                    style="
                                        margin-bottom:10px;
                                        color:#7d838a;
                                        font-size:9px;
                                        font-weight:800;
                                        letter-spacing:.15em;
                                        text-transform:uppercase;
                                    "
                                >
                                    Jouw code
                                </div>

                                <div
                                    style="
                                        color:#f1cc8b;
                                        font-size:42px;
                                        line-height:1;
                                        font-weight:900;
                                        letter-spacing:10px;
                                    "
                                >
                                    {{ $code }}
                                </div>
                            </div>

                            <p
                                style="
                                    margin:0;
                                    color:#a2a7ad;
                                    font-size:13px;
                                    line-height:1.8;
                                "
                            >
                                Deze code is
                                <strong
                                    style="
                                        color:#e6c07d;
                                    "
                                >
                                    {{ $expiresInMinutes }} minuten
                                </strong>
                                geldig.
                            </p>

                            <div
                                style="
                                    margin-top:26px;
                                    padding:16px;
                                    border:1px solid rgba(255,255,255,.07);
                                    border-radius:14px;
                                    background:rgba(255,255,255,.025);
                                "
                            >
                                <div
                                    style="
                                        color:#d9d6d0;
                                        font-size:12px;
                                        font-weight:800;
                                        margin-bottom:6px;
                                    "
                                >
                                    Heb jij deze code niet aangevraagd?
                                </div>

                                <div
                                    style="
                                        color:#777d84;
                                        font-size:11px;
                                        line-height:1.7;
                                    "
                                >
                                    Dan hoef je niets te doen. Negeer deze e-mail.
                                    Deel deze code nooit met iemand anders.
                                </div>
                            </div>

                            @if (!empty($email))
                                <p
                                    style="
                                        margin:22px 0 0;
                                        color:#666c73;
                                        font-size:10px;
                                        line-height:1.7;
                                    "
                                >
                                    Deze code is aangevraagd voor:
                                    <strong
                                        style="
                                            color:#979da3;
                                        "
                                    >
                                        {{ $email }}
                                    </strong>
                                </p>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td
                            style="
                                padding:22px 34px 28px;
                                border-top:1px solid rgba(255,255,255,.06);
                                background:#0a0c0f;
                            "
                        >
                            <p
                                style="
                                    margin:0;
                                    color:#5f656c;
                                    font-size:10px;
                                    line-height:1.7;
                                    text-align:center;
                                "
                            >
                                Mashal Automotive vraagt je nooit om je
                                inlogcode of wachtwoord via chat, telefoon
                                of sociale media te delen.
                            </p>

                            <p
                                style="
                                    margin:12px 0 0;
                                    color:#494f55;
                                    font-size:9px;
                                    line-height:1.6;
                                    text-align:center;
                                "
                            >
                                © {{ date('Y') }} Mashal Automotive.
                                Alle rechten voorbehouden.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>