```blade
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welkom bij SmartDesk</title>
</head>

<body style="margin: 0; padding: 0; background-color: #f4f6f8; font-family: Arial, Helvetica, sans-serif; color: #222222;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="center" style="padding: 32px 16px;">
                <table
                    role="presentation"
                    width="100%"
                    cellpadding="0"
                    cellspacing="0"
                    border="0"
                    style="max-width: 620px; background-color: #ffffff;"
                >
                    <tr>
                        <td align="center" style="padding: 28px; background-color: #111111;">
                            <p style="margin: 0; color: #ffffff; font-size: 28px; font-weight: bold;">
                                SmartDesk
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 32px 24px;">
                            <h1 style="margin: 0 0 24px; font-size: 26px; line-height: 1.3;">
                                Welkom bij SmartDesk
                            </h1>

                            <p style="font-size: 16px; line-height: 1.7;">
                                Hallo {{ $user->name }},
                            </p>

                            <p style="font-size: 16px; line-height: 1.7;">
                                Een beheerder heeft een SmartDesk-account voor je aangemaakt met het e-mailadres
                                <strong>{{ $user->email }}</strong>.
                            </p>

                            @if($code !== null)
                                <p style="font-size: 15px; line-height: 1.7;">
                                    Bevestig je e-mailadres met deze verificatiecode:
                                </p>

                                <p style="padding: 20px; text-align: center; background-color: #f3f5f7; font-size: 30px; font-weight: bold; letter-spacing: 6px;">
                                    {{ $code }}
                                </p>

                                <p style="font-size: 14px; line-height: 1.7;">
                                    De code is 15 minuten geldig vanaf het moment waarop het account is aangemaakt.
                                    Is de code verlopen? Vraag dan op de verificatiepagina een nieuwe code aan.
                                </p>

                                <p>
                                    <a href="{{ route('verification.notice') }}" style="color: #2457a7;">
                                        E-mailadres bevestigen
                                    </a>
                                </p>
                            @else
                                <p style="font-size: 15px; line-height: 1.7;">
                                    De beheerder heeft je e-mailadres al als geverifieerd gemarkeerd.
                                    Je hoeft daarom geen verificatiecode in te voeren.
                                </p>

                                <p>
                                    <a href="{{ route('login') }}" style="color: #2457a7;">
                                        Inloggen bij SmartDesk
                                    </a>
                                </p>
                            @endif

                            <p style="font-size: 15px; line-height: 1.7;">
                                We versturen geen wachtwoord per e-mail.
                                Ken je je wachtwoord niet? Via
                                <a href="{{ route('password.request') }}" style="color: #2457a7;">
                                    Wachtwoord vergeten
                                </a>
                                kun je een resetlink aanvragen en zelf een nieuw wachtwoord instellen.
                            </p>

                            <p style="font-size: 14px; line-height: 1.7;">
                                Had je dit account niet verwacht? Neem dan contact op met de beheerder van SmartDesk.
                            </p>

                            <p style="margin-top: 28px; font-size: 15px; line-height: 1.7;">
                                Met vriendelijke groet,<br>
                                <strong>Het SmartDesk-team</strong>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td align="center" style="padding: 20px; background-color: #f8f9fa;">
                            <p style="margin: 0; font-size: 12px; color: #666666;">
                                &copy; {{ date('Y') }} SmartDesk
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
```
