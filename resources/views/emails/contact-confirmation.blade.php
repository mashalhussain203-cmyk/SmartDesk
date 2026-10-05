<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>We hebben uw bericht ontvangen | SmartDesk</title>
</head>

<body
    style="
        margin:0;
        padding:30px 14px;
        background:#f4f5f8;
        font-family:
            Arial,
            Helvetica,
            sans-serif;
        color:#222222;
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
        border-collapse:collapse;
    "
>
    <tr>
        <td align="center">

            <table
                role="presentation"
                width="100%"
                cellspacing="0"
                cellpadding="0"
                border="0"
                style="
                    width:100%;
                    max-width:650px;
                    margin:0 auto;
                    border-collapse:separate;
                    overflow:hidden;
                    background:#ffffff;
                    border-radius:18px;
                    box-shadow:0 16px 40px rgba(15,23,42,.08);
                "
            >

                <tr>
                    <td
                        style="
                            padding:30px;
                            background:
                                linear-gradient(
                                    135deg,
                                    #8f82ff,
                                    #4d88ff
                                );
                            color:#ffffff;
                        "
                    >

                        <div
                            style="
                                margin-bottom:9px;
                                font-size:12px;
                                line-height:1.4;
                                font-weight:800;
                                letter-spacing:.12em;
                                text-transform:uppercase;
                            "
                        >
                            SMARTDESK
                        </div>

                        <h1
                            style="
                                margin:0;
                                font-size:27px;
                                line-height:1.25;
                                font-weight:800;
                                color:#ffffff;
                            "
                        >
                            We hebben uw bericht ontvangen
                        </h1>

                    </td>
                </tr>

                <tr>
                    <td
                        style="
                            padding:32px 30px;
                        "
                    >

                        <p
                            style="
                                margin:0 0 18px;
                                font-size:16px;
                                line-height:1.7;
                                color:#222222;
                            "
                        >
                            Beste {{ $data['first_name'] }},
                        </p>

                        <p
                            style="
                                margin:0 0 16px;
                                font-size:14px;
                                line-height:1.8;
                                color:#555555;
                            "
                        >
                            Bedankt dat u contact heeft opgenomen met SmartDesk.
                        </p>

                        <p
                            style="
                                margin:0 0 22px;
                                font-size:14px;
                                line-height:1.8;
                                color:#555555;
                            "
                        >
                            Uw bericht is succesvol ontvangen.
                            We nemen zo snel mogelijk contact met u op via
                            <strong
                                style="
                                    color:#222222;
                                "
                            >
                                {{ $data['email'] }}
                            </strong>.
                        </p>

                        <table
                            role="presentation"
                            width="100%"
                            cellspacing="0"
                            cellpadding="0"
                            border="0"
                            style="
                                width:100%;
                                margin:24px 0;
                                border-collapse:separate;
                            "
                        >
                            <tr>
                                <td
                                    style="
                                        padding:22px;
                                        border:1px solid #e4e0ff;
                                        border-radius:14px;
                                        background:#f5f3ff;
                                    "
                                >

                                    <div
                                        style="
                                            margin-bottom:10px;
                                            color:#6f63e8;
                                            font-size:13px;
                                            font-weight:800;
                                        "
                                    >
                                        Uw bericht
                                    </div>

                                    <div
                                        style="
                                            color:#555555;
                                            font-size:14px;
                                            line-height:1.8;
                                            word-break:break-word;
                                        "
                                    >
                                        {!! nl2br(e($data['message'])) !!}
                                    </div>

                                </td>
                            </tr>
                        </table>

                        <p
                            style="
                                margin:0 0 16px;
                                font-size:14px;
                                line-height:1.8;
                                color:#555555;
                            "
                        >
                            U hoeft uw bericht niet opnieuw te versturen.
                            Wij hebben uw aanvraag ontvangen.
                        </p>

                        <p
                            style="
                                margin:0 0 26px;
                                font-size:14px;
                                line-height:1.8;
                                color:#555555;
                            "
                        >
                            We doen ons best om zo snel mogelijk te reageren.
                        </p>

                        <p
                            style="
                                margin:0;
                                font-size:14px;
                                line-height:1.8;
                                color:#222222;
                            "
                        >
                            Met vriendelijke groet,
                            <br>
                            <strong>
                                SmartDesk
                            </strong>
                        </p>

                    </td>
                </tr>

                <tr>
                    <td
                        style="
                            padding:20px 30px;
                            border-top:1px solid #edf0f5;
                            background:#fafafa;
                        "
                    >

                        <div
                            style="
                                font-size:11px;
                                line-height:1.7;
                                color:#999999;
                                text-align:center;
                            "
                        >
                            Deze e-mail is automatisch verzonden ter bevestiging
                            van uw contactaanvraag bij SmartDesk.
                        </div>

                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>