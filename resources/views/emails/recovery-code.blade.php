<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Herstelcode | Mashal Studio</title>
</head>
<body style="margin:0;padding:0;background:#070707;color:#f6f6f6;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#070707;padding:32px 14px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#111114;border:1px solid #2d2d31;border-radius:18px;overflow:hidden;">
                <tr>
                    <td style="padding:30px 30px 14px;">
                        <div style="font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#e6bd62;">
                            Mashal Studio
                        </div>

                        <h1 style="margin:14px 0 10px;font-size:28px;line-height:1.1;color:#ffffff;">
                            @if (($purpose ?? '') === 'forgot_email')
                                Account recovery
                            @else
                                Herstel-e-mailadres bevestigen
                            @endif
                        </h1>

                        <p style="margin:0;color:#9b9ba1;font-size:14px;line-height:1.7;">
                            Hallo {{ $user->name ?? 'Mashal Studio gebruiker' }},
                        </p>

                        <p style="margin:12px 0 0;color:#9b9ba1;font-size:14px;line-height:1.7;">
                            @if (($purpose ?? '') === 'forgot_email')
                                Gebruik onderstaande code om je Mashal Studio-account veilig terug te vinden.
                            @else
                                Gebruik onderstaande code om dit e-mailadres als hersteladres voor je Mashal Studio-account te bevestigen.
                            @endif
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:18px 30px;">
                        <div style="padding:20px;border:1px solid #4d4822;border-radius:14px;background:#17170d;text-align:center;">
                            <div style="font-size:11px;color:#9d975d;text-transform:uppercase;letter-spacing:.14em;">
                                Verificatiecode
                            </div>

                            <div style="margin-top:10px;color:#f4ee1f;font-size:36px;font-weight:900;letter-spacing:.20em;">
                                {{ $code }}
                            </div>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="padding:8px 30px 30px;">
                        <p style="margin:0;color:#77777d;font-size:12px;line-height:1.7;">
                            Deze code is {{ $minutes ?? 10 }} minuten geldig.
                            Deel deze code nooit met iemand.
                        </p>

                        <p style="margin:10px 0 0;color:#77777d;font-size:12px;line-height:1.7;">
                            Heb je dit niet aangevraagd? Dan hoef je niets te doen.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
