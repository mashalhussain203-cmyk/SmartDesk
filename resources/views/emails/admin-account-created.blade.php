<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Je SmartDesk-account is aangemaakt</title>
</head>

<body style="
    margin:0;
    padding:0;
    background:#f5f7f6;
    font-family:Arial, Helvetica, sans-serif;
    color:#14251f;
">

<table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 15px;">
    <tr>
        <td align="center">

            <table width="100%" cellpadding="0" cellspacing="0" style="
                max-width:620px;
                background:#ffffff;
                border-radius:18px;
                overflow:hidden;
                border:1px solid #dce9e2;
            ">

                <tr>
                    <td style="
                        padding:28px;
                        background:#0e4d3a;
                        color:#ffffff;
                    ">
                        <h1 style="
                            margin:0;
                            font-size:26px;
                        ">
                            SmartDesk
                        </h1>

                        <p style="
                            margin:8px 0 0;
                            color:#dff5ed;
                        ">
                            Je account is aangemaakt
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:32px;">

                        <p>
                            Hallo {{ $user->name }},
                        </p>

                        <p>
                            Een SmartDesk-administrator heeft een account voor je aangemaakt.
                        </p>

                        <table width="100%" cellpadding="0" cellspacing="0" style="
                            margin:24px 0;
                            background:#f7f9f8;
                            border-radius:12px;
                        ">
                            <tr>
                                <td style="padding:18px;">

                                    <strong>E-mailadres</strong><br>
                                    {{ $user->email }}

                                    <br><br>

                                    <strong>Accounttype</strong><br>

                                    @if($isAdmin)
                                        Administrator
                                    @else
                                        Gebruiker
                                    @endif

                                    <br><br>

                                    <strong>E-mailstatus</strong><br>

                                    @if($isVerified)
                                        Geverifieerd
                                    @else
                                        Nog niet geverifieerd
                                    @endif

                                    <br><br>

                                    <strong>Aangemaakt op</strong><br>
                                    {{ $createdAt }}

                                </td>
                            </tr>
                        </table>

                        @if(!$isVerified)
                            <p>
                                Je ontvangt ook een aparte e-mail met een verificatiecode.
                            </p>
                        @endif

                        <p style="
                            margin-top:28px;
                            color:#60716d;
                            font-size:13px;
                        ">
                            Om veiligheidsredenen wordt je wachtwoord niet per e-mail verzonden.
                        </p>

                    </td>
                </tr>

                <tr>
                    <td style="
                        padding:20px 32px;
                        background:#f7f9f8;
                        color:#60716d;
                        font-size:12px;
                    ">
                        © {{ date('Y') }} SmartDesk
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>

</body>
</html>

