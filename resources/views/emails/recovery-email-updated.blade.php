<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Beveiligingswijziging | Mashal Studio</title>
</head>
<body style="margin:0;padding:0;background:#070707;color:#f6f6f6;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#070707;padding:32px 14px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#111114;border:1px solid #2d2d31;border-radius:18px;">
                <tr>
                    <td style="padding:30px;">
                        <div style="font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#e6bd62;">
                            Mashal Studio Security
                        </div>

                        <h1 style="margin:14px 0 12px;font-size:26px;line-height:1.15;color:#ffffff;">
                            Herstel-e-mailadres gewijzigd
                        </h1>

                        <p style="margin:0;color:#9b9ba1;font-size:14px;line-height:1.7;">
                            Hallo {{ $user->name ?? 'Mashal Studio gebruiker' }},
                        </p>

                        @if (($action ?? '') === 'removed')
                            <p style="margin:12px 0 0;color:#9b9ba1;font-size:14px;line-height:1.7;">
                                Het herstel-e-mailadres
                                <strong style="color:#d9d9dc;">{{ $oldRecoveryEmail }}</strong>
                                is van je account verwijderd.
                            </p>
                        @else
                            <p style="margin:12px 0 0;color:#9b9ba1;font-size:14px;line-height:1.7;">
                                Je nieuwe herstel-e-mailadres is succesvol geverifieerd:
                            </p>

                            <div style="margin-top:16px;padding:14px;border:1px solid #4d4822;border-radius:12px;background:#17170d;color:#f4ee1f;font-size:15px;font-weight:800;word-break:break-all;">
                                {{ $newRecoveryEmail }}
                            </div>
                        @endif

                        <p style="margin:18px 0 0;color:#77777d;font-size:12px;line-height:1.7;">
                            Heb jij deze wijziging niet uitgevoerd? Wijzig dan direct je wachtwoord en controleer je accountbeveiliging.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
