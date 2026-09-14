<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bestelbevestiging {{ $orderNumber }}</title>
</head>
<body style="margin:0;background:#f2f6f4;color:#17352b;font-family:Arial,sans-serif;line-height:1.5;">
    <div style="max-width:640px;margin:32px auto;background:#ffffff;border:1px solid #dce9e2;">
        <div style="padding:28px 32px;background:#0e4d3a;color:#ffffff;">
            <div style="font-size:24px;font-weight:700;">SmartDesk</div>
            <div style="margin-top:8px;color:#dff5ed;">Bedankt voor je bestelling</div>
        </div>
        <div style="padding:32px;">
            <h1 style="margin:0 0 12px;font-size:26px;color:#17352b;">Hallo {{ $user->name }},</h1>
            <p style="margin:0 0 24px;">We hebben je bestelling goed ontvangen.</p>
            <div style="padding:18px;background:#eef8f4;border-left:4px solid #0d7458;margin-bottom:28px;">
                <div style="font-size:12px;color:#60716d;text-transform:uppercase;letter-spacing:1px;">Bestelnummer</div>
                <div style="font-size:24px;font-weight:700;color:#0e4d3a;">{{ $orderNumber }}</div>
                <div style="margin-top:6px;font-size:14px;color:#60716d;">Geplaatst op {{ $orderDate }}</div>
            </div>
            <h2 style="font-size:18px;margin:0 0 12px;">Je bestelling</h2>
            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <thead>
                    <tr style="border-bottom:2px solid #dce9e2;text-align:left;">
                        <th style="padding:10px 4px;">Auto</th>
                        <th style="padding:10px 4px;text-align:center;">Aantal</th>
                        <th style="padding:10px 4px;text-align:right;">Prijs</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr style="border-bottom:1px solid #edf2ef;">
                            <td style="padding:12px 4px;">{{ $item['brand'] }} {{ $item['model'] }}</td>
                            <td style="padding:12px 4px;text-align:center;">{{ $item['qty'] }}</td>
                            <td style="padding:12px 4px;text-align:right;">€{{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="padding-top:20px;text-align:right;font-size:20px;font-weight:700;">Totaal: €{{ number_format($total, 0, ',', '.') }}</div>
            <p style="margin:28px 0 0;">We nemen zo snel mogelijk contact met je op over de verdere afhandeling.</p>
            <p style="margin:20px 0 0;color:#60716d;font-size:13px;">Deze bestelling is geplaatst met {{ $user->email }}.</p>
        </div>
        <div style="padding:20px 32px;background:#f7f9f8;color:#60716d;font-size:12px;">SmartDesk · Bewaar dit bericht voor je administratie.</div>
    </div>
</body>
</html>