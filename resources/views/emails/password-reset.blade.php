<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wachtwoord herstellen - SmartDesk</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f5f7; font-family: Arial, Helvetica, sans-serif; color: #222222;">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
           style="width: 100%; background-color: #f3f5f7;">
        <tr>
            <td align="center" style="padding: 40px 16px;">

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                       style="width: 100%; max-width: 640px; background-color: #ffffff; border-radius: 14px;">

                    {{-- Header --}}
                    <tr>
                        <td align="center"
                            style="padding: 32px 24px; background-color: #111111; border-radius: 14px 14px 0 0;">
                            <p style="margin: 0; color: #ffffff; font-size: 30px; font-weight: bold; letter-spacing: 1px;">
                                SmartDesk
                            </p>
                            <p style="margin: 8px 0 0; color: #cccccc; font-size: 13px; line-height: 1.6;">
                                Accountbeveiliging &amp; wachtwoordherstel
                            </p>
                        </td>
                    </tr>

                    {{-- Inhoud --}}
                    <tr>
                        <td style="padding: 32px 24px;">

                            <h1 style="margin: 0 0 24px; text-align: center; font-size: 26px; line-height: 1.3; color: #111111;">
                                Wachtwoord herstellen
                            </h1>

                            <p style="margin: 0 0 18px; font-size: 16px; line-height: 1.7; color: #444444;">
                                Beste {{ $user->name }},
                            </p>

                            <p style="margin: 0 0 18px; font-size: 16px; line-height: 1.7; color: #444444;">
                                We hebben een verzoek ontvangen om het wachtwoord
                                van je SmartDesk-account opnieuw in te stellen.
                            </p>

                            <p style="margin: 0 0 24px; font-size: 15px; line-height: 1.7; color: #555555;">
                                Klik op de onderstaande knop om een nieuw wachtwoord te kiezen.
                            </p>

                            {{-- Resetknop --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" style="padding: 0 0 28px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td align="center" bgcolor="#111111"
                                                    style="background-color: #111111; border-radius: 8px; mso-padding-alt: 16px 28px;">
                                                    <a href="{{ url('/reset-password/' . $token) }}"
                                                       style="display: inline-block; padding: 16px 28px; color: #ffffff; font-size: 16px; font-weight: bold; line-height: 1.4; text-decoration: none; border-radius: 8px;">
                                                        Wachtwoord herstellen
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            {{-- Geldigheidsduur --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding: 18px 20px; background-color: #fff8e6; border-left: 4px solid #e0a100;">
                                        <p style="margin: 0 0 6px; font-size: 14px; font-weight: bold; color: #7a5700;">
                                            Let op
                                        </p>
                                        <p style="margin: 0; font-size: 14px; line-height: 1.7; color: #6b5a2a;">
                                            Deze resetlink is 60 minuten geldig.
                                            Daarna kun je een nieuwe resetlink aanvragen.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            {{-- Alternatieve link --}}
                            <p style="margin: 24px 0 10px; font-size: 14px; line-height: 1.7; color: #666666;">
                                Werkt de knop niet? Kopieer onderstaande link en plak deze in je browser:
                            </p>

                            <p style="margin: 0 0 24px; padding: 12px; background-color: #f7f8fa; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 13px; line-height: 1.7; word-break: break-all; overflow-wrap: anywhere;">
                                <a href="{{ url('/reset-password/' . $token) }}"
                                   style="color: #2457a7; text-decoration: underline; word-break: break-all;">
                                    {{ url('/reset-password/' . $token) }}
                                </a>
                            </p>

                            {{-- Niet aangevraagd --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding: 18px 20px; background-color: #fff4f4; border-left: 4px solid #c62828;">
                                        <p style="margin: 0 0 8px; font-size: 15px; font-weight: bold; color: #a51d1d;">
                                            Heb jij dit niet aangevraagd?
                                        </p>
                                        <p style="margin: 0; font-size: 14px; line-height: 1.7; color: #713030;">
                                            Dan hoef je niets te doen en kun je deze e-mail negeren.
                                            Je huidige wachtwoord blijft actief.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            {{-- Accountgegevens --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="width: 100%;">
                                <tr>
                                    <td style="padding: 24px 0 16px;">
                                        <p style="margin: 0 0 5px; font-size: 13px; color: #666666;">
                                            Accountnaam
                                        </p>
                                        <p style="margin: 0; font-size: 15px; font-weight: bold; color: #222222;">
                                            {{ $user->name }}
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 0 0 24px;">
                                        <p style="margin: 0 0 5px; font-size: 13px; color: #666666;">
                                            E-mailadres
                                        </p>
                                        <p style="margin: 0; font-size: 15px; color: #222222; word-break: break-all;">
                                            {{ $user->email }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            {{-- Veiligheid --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding: 18px 20px; background-color: #f7f8fa; border-left: 4px solid #555555;">
                                        <p style="margin: 0 0 7px; font-size: 14px; font-weight: bold; color: #222222;">
                                            Veiligheid
                                        </p>
                                        <p style="margin: 0; font-size: 14px; line-height: 1.7; color: #555555;">
                                            Deel deze resetlink niet met anderen.
                                            SmartDesk vraagt je nooit om je wachtwoord
                                            of resetlink per e-mail door te sturen.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 28px 0 0; font-size: 15px; line-height: 1.7; color: #555555;">
                                Met vriendelijke groet,<br>
                                <strong style="color: #111111;">Het SmartDesk-team</strong>
                            </p>

                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center"
                            style="padding: 24px; background-color: #f8f9fa; border-top: 1px solid #eeeeee; border-radius: 0 0 14px 14px;">
                            <p style="margin: 0; font-size: 12px; line-height: 1.7; color: #666666;">
                                Deze e-mail is automatisch verzonden omdat er
                                wachtwoordherstel voor je SmartDesk-account is aangevraagd.
                            </p>

                            <p style="margin: 10px 0 0; font-size: 12px; line-height: 1.7; color: #666666;">
                                &copy; {{ date('Y') }} SmartDesk. Alle rechten voorbehouden.
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>