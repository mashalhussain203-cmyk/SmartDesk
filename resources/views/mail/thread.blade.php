<!doctype html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mashal Support</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f6f8;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e5e7eb;">
                    <tr>
                        <td style="padding:22px 24px;background:#111827;color:#ffffff;">
                            <strong style="font-size:18px;">Mashal Support</strong>
                            <div style="margin-top:4px;font-size:12px;opacity:.75;">Gesprek #{{ $conversationId }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:26px 24px;">
                            @if($customerName)
                                <p style="margin:0 0 18px;">Hallo {{ $customerName }},</p>
                            @endif

                            <div style="white-space:pre-wrap;font-size:15px;line-height:1.6;">{{ $bodyText }}</div>

                            <div style="margin-top:28px;padding:14px 16px;border-radius:10px;background:#f3f4f6;font-size:13px;line-height:1.5;color:#4b5563;">
                                Antwoord gewoon op deze e-mail. Uw antwoord verschijnt automatisch in hetzelfde supportgesprek bij de medewerker.
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 24px;border-top:1px solid #e5e7eb;font-size:11px;color:#6b7280;">
                            Dit bericht hoort bij uw gesprek met Mashal Support.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
