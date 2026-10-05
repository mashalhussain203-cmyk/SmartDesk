<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">

    <title>Nieuw contactbericht</title>
</head>

<body
    style="
        margin:0;
        padding:30px;
        background:#f4f5f8;
        font-family:Arial,sans-serif;
        color:#222;
    "
>

<div
    style="
        max-width:650px;
        margin:0 auto;
        padding:30px;
        background:#ffffff;
        border-radius:16px;
    "
>

    <div
        style="
            margin-bottom:24px;
            color:#8f82ff;
            font-size:13px;
            font-weight:700;
        "
    >
        SMARTDESK
    </div>

    <h1
        style="
            margin:0 0 24px;
            font-size:26px;
        "
    >
        Nieuw contactbericht
    </h1>

    <p>
        Er is een nieuw bericht verzonden via het contactformulier.
    </p>

    <table
        role="presentation"
        style="
            width:100%;
            margin-top:25px;
            border-collapse:collapse;
        "
    >
        <tr>
            <td
                style="
                    padding:10px 0;
                    font-weight:bold;
                    width:140px;
                "
            >
                Voornaam
            </td>

            <td style="padding:10px 0;">
                {{ $data['first_name'] }}
            </td>
        </tr>

        <tr>
            <td
                style="
                    padding:10px 0;
                    font-weight:bold;
                "
            >
                Achternaam
            </td>

            <td style="padding:10px 0;">
                {{ $data['last_name'] }}
            </td>
        </tr>

        <tr>
            <td
                style="
                    padding:10px 0;
                    font-weight:bold;
                "
            >
                E-mailadres
            </td>

            <td style="padding:10px 0;">
                <a
                    href="mailto:{{ $data['email'] }}"
                    style="
                        color:#6f63e8;
                        text-decoration:none;
                    "
                >
                    {{ $data['email'] }}
                </a>
            </td>
        </tr>
    </table>

    <div
        style="
            margin-top:25px;
            padding:22px;
            background:#f5f3ff;
            border-left:4px solid #8f82ff;
            border-radius:10px;
        "
    >
        <strong>
            Toelichting
        </strong>

        <div
            style="
                margin-top:12px;
                line-height:1.7;
            "
        >
            {!! nl2br(e($data['message'])) !!}
        </div>
    </div>

    <p
        style="
            margin-top:25px;
            color:#777;
            font-size:13px;
            line-height:1.6;
        "
    >
        Klik in Gmail op Beantwoorden om rechtstreeks te reageren
        naar {{ $data['first_name'] }} {{ $data['last_name'] }}.
    </p>

</div>

</body>
</html>