<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Bestelbevestiging {{ $orderNumber }} - SmartDesk</title>
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
                    max-width: 680px;
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
                            Bedankt voor je bestelling
                        </div>
                    </td>
                </tr>

                <!-- CONTENT -->
                <tr>
                    <td style="padding: 42px 40px 35px;">

                        <!-- SUCCESS ICON -->
                        <div
                            style="
                                width: 64px;
                                height: 64px;
                                margin: 0 auto 25px;
                                border-radius: 50%;
                                background-color: #e8f7ee;
                                text-align: center;
                                line-height: 64px;
                                font-size: 30px;
                                color: #187a42;
                            "
                        >
                            ✓
                        </div>

                        <!-- TITLE -->
                        <h1
                            style="
                                margin: 0 0 18px;
                                text-align: center;
                                font-size: 27px;
                                line-height: 1.3;
                                color: #111111;
                            "
                        >
                            Je bestelling is ontvangen
                        </h1>

                        <!-- GREETING -->
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
                            Bedankt voor je bestelling bij SmartDesk.
                            We hebben je bestelling goed ontvangen en hieronder
                            vind je een overzicht van de gegevens.
                        </p>

                        <!-- ORDER NUMBER -->
                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="
                                width: 100%;
                                margin: 0 0 28px;
                                background-color: #f0faf4;
                                border: 1px solid #ccebd8;
                                border-radius: 10px;
                            "
                        >
                            <tr>
                                <td
                                    style="
                                        padding: 24px;
                                        text-align: center;
                                    "
                                >
                                    <div
                                        style="
                                            margin-bottom: 7px;
                                            font-size: 12px;
                                            font-weight: 700;
                                            text-transform: uppercase;
                                            letter-spacing: 1px;
                                            color: #5f7769;
                                        "
                                    >
                                        Bestelnummer
                                    </div>

                                    <div
                                        style="
                                            font-size: 25px;
                                            font-weight: 700;
                                            color: #187a42;
                                            word-break: break-word;
                                        "
                                    >
                                        {{ $orderNumber }}
                                    </div>

                                    <div
                                        style="
                                            margin-top: 8px;
                                            font-size: 14px;
                                            color: #5f7769;
                                        "
                                    >
                                        Geplaatst op {{ $orderDate }}
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <!-- ORDER TITLE -->
                        <h2
                            style="
                                margin: 0 0 14px;
                                font-size: 19px;
                                color: #111111;
                            "
                        >
                            Je bestelling
                        </h2>

                        <!-- ORDER ITEMS -->
                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="
                                width: 100%;
                                margin-bottom: 20px;
                                border-collapse: collapse;
                            "
                        >
                            <thead>
                                <tr>
                                    <th
                                        align="left"
                                        style="
                                            padding: 12px 8px;
                                            border-bottom: 2px solid #e5e7eb;
                                            font-size: 13px;
                                            color: #666666;
                                            text-transform: uppercase;
                                            letter-spacing: 0.5px;
                                        "
                                    >
                                        Auto
                                    </th>

                                    <th
                                        align="center"
                                        style="
                                            padding: 12px 8px;
                                            border-bottom: 2px solid #e5e7eb;
                                            font-size: 13px;
                                            color: #666666;
                                            text-transform: uppercase;
                                            letter-spacing: 0.5px;
                                        "
                                    >
                                        Aantal
                                    </th>

                                    <th
                                        align="right"
                                        style="
                                            padding: 12px 8px;
                                            border-bottom: 2px solid #e5e7eb;
                                            font-size: 13px;
                                            color: #666666;
                                            text-transform: uppercase;
                                            letter-spacing: 0.5px;
                                        "
                                    >
                                        Prijs
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($items as $item)
                                    <tr>
                                        <td
                                            style="
                                                padding: 14px 8px;
                                                border-bottom: 1px solid #eeeeee;
                                                font-size: 14px;
                                                color: #222222;
                                            "
                                        >
                                            <strong>
                                                {{ $item['brand'] }} {{ $item['model'] }}
                                            </strong>
                                        </td>

                                        <td
                                            align="center"
                                            style="
                                                padding: 14px 8px;
                                                border-bottom: 1px solid #eeeeee;
                                                font-size: 14px;
                                                color: #555555;
                                            "
                                        >
                                            {{ $item['qty'] }}
                                        </td>

                                        <td
                                            align="right"
                                            style="
                                                padding: 14px 8px;
                                                border-bottom: 1px solid #eeeeee;
                                                font-size: 14px;
                                                font-weight: 600;
                                                color: #222222;
                                            "
                                        >
                                            €{{ number_format(
                                                $item['price'] * $item['qty'],
                                                0,
                                                ',',
                                                '.'
                                            ) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- TOTAL -->
                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="
                                width: 100%;
                                margin: 0 0 28px;
                                background-color: #f7f8fa;
                                border-radius: 8px;
                            "
                        >
                            <tr>
                                <td
                                    style="
                                        padding: 18px 20px;
                                        font-size: 16px;
                                        font-weight: 700;
                                        color: #111111;
                                    "
                                >
                                    Totaal
                                </td>

                                <td
                                    align="right"
                                    style="
                                        padding: 18px 20px;
                                        font-size: 22px;
                                        font-weight: 700;
                                        color: #111111;
                                    "
                                >
                                    €{{ number_format($total, 0, ',', '.') }}
                                </td>
                            </tr>
                        </table>

                        <!-- NEXT STEPS -->
                        <h2
                            style="
                                margin: 0 0 12px;
                                font-size: 19px;
                                color: #111111;
                            "
                        >
                            Wat gebeurt er nu?
                        </h2>

                        <p
                            style="
                                margin: 0 0 28px;
                                font-size: 15px;
                                line-height: 1.7;
                                color: #555555;
                            "
                        >
                            We nemen zo snel mogelijk contact met je op
                            over de verdere afhandeling van je bestelling.
                            Bewaar deze e-mail voor je administratie.
                        </p>

                        <!-- ACCOUNT INFORMATION -->
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
                                            margin-bottom: 5px;
                                            font-size: 13px;
                                            color: #888888;
                                        "
                                    >
                                        Klant
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

                            <tr>
                                <td style="padding: 0 0 18px;">

                                    <div
                                        style="
                                            margin-bottom: 5px;
                                            font-size: 13px;
                                            color: #888888;
                                        "
                                    >
                                        Bestelnummer
                                    </div>

                                    <div
                                        style="
                                            font-size: 15px;
                                            font-weight: 600;
                                            color: #222222;
                                        "
                                    >
                                        {{ $orderNumber }}
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <!-- INFORMATION BOX -->
                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="
                                width: 100%;
                                margin-bottom: 28px;
                                background-color: #eef5ff;
                                border-left: 4px solid #2563eb;
                            "
                        >
                            <tr>
                                <td style="padding: 18px 20px;">

                                    <div
                                        style="
                                            margin-bottom: 7px;
                                            font-size: 14px;
                                            font-weight: 700;
                                            color: #174ea6;
                                        "
                                    >
                                        Goed om te weten
                                    </div>

                                    <div
                                        style="
                                            font-size: 14px;
                                            line-height: 1.7;
                                            color: #3f5f8f;
                                        "
                                    >
                                        Deze bestelling is gekoppeld aan je
                                        SmartDesk-account. Je kunt de bestelling
                                        ook terugvinden via je accountpagina.
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <!-- SECURITY -->
                        <table
                            width="100%"
                            cellpadding="0"
                            cellspacing="0"
                            border="0"
                            style="
                                width: 100%;
                                margin-bottom: 28px;
                                background-color: #fff8e6;
                                border-left: 4px solid #e0a100;
                            "
                        >
                            <tr>
                                <td style="padding: 18px 20px;">

                                    <div
                                        style="
                                            margin-bottom: 7px;
                                            font-size: 14px;
                                            font-weight: 700;
                                            color: #7a5700;
                                        "
                                    >
                                        Herken je deze bestelling niet?
                                    </div>

                                    <div
                                        style="
                                            font-size: 14px;
                                            line-height: 1.7;
                                            color: #6b5a2a;
                                        "
                                    >
                                        Neem dan zo snel mogelijk contact op met
                                        SmartDesk en controleer de beveiliging
                                        van je account.
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <!-- CLOSING -->
                        <p
                            style="
                                margin: 0 0 18px;
                                font-size: 15px;
                                line-height: 1.7;
                                color: #555555;
                            "
                        >
                            Bedankt voor je bestelling en voor je vertrouwen
                            in SmartDesk.
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
                            Deze e-mail is automatisch verzonden nadat
                            je bestelling bij SmartDesk is geplaatst.
                        </div>

                        <div
                            style="
                                margin-top: 8px;
                                font-size: 12px;
                                line-height: 1.6;
                                color: #888888;
                            "
                        >
                            Bewaar dit bericht voor je administratie.
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

