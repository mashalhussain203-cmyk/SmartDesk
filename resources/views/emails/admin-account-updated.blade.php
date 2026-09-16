<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Je SmartDesk-account is gewijzigd
    </title>
</head>

<body
    style="
        margin: 0;
        padding: 0;
        background-color: #f4f7f6;
        font-family: Arial, Helvetica, sans-serif;
        color: #1f2f2a;
    "
>

    <table
        role="presentation"
        width="100%"
        cellspacing="0"
        cellpadding="0"
        border="0"
        style="
            width: 100%;
            background-color: #f4f7f6;
            padding: 32px 16px;
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
                        max-width: 640px;
                        width: 100%;
                        background-color: #ffffff;
                        border-radius: 18px;
                        overflow: hidden;
                        border: 1px solid #dfe8e4;
                    "
                >

                    {{-- HEADER --}}
                    <tr>
                        <td
                            style="
                                padding: 28px 32px;
                                background-color: #0f513f;
                                color: #ffffff;
                            "
                        >

                            <div
                                style="
                                    font-size: 13px;
                                    font-weight: 700;
                                    letter-spacing: 1.2px;
                                    text-transform: uppercase;
                                    opacity: 0.85;
                                    margin-bottom: 8px;
                                "
                            >
                                SmartDesk
                            </div>

                            <h1
                                style="
                                    margin: 0;
                                    font-size: 26px;
                                    line-height: 1.3;
                                    color: #ffffff;
                                "
                            >
                                Je account is gewijzigd
                            </h1>

                        </td>
                    </tr>


                    {{-- CONTENT --}}
                    <tr>
                        <td
                            style="
                                padding: 32px;
                            "
                        >

                            <p
                                style="
                                    margin: 0 0 18px;
                                    font-size: 16px;
                                    line-height: 1.7;
                                "
                            >
                                Hallo {{ $newName ?? $user->name }},
                            </p>


                            <p
                                style="
                                    margin: 0 0 24px;
                                    font-size: 15px;
                                    line-height: 1.7;
                                    color: #5f716b;
                                "
                            >
                                Een SmartDesk-administrator heeft wijzigingen
                                aangebracht aan je account.
                                Hieronder zie je precies wat er is aangepast.
                            </p>


                            {{-- NAAM --}}
                            @if (!empty($nameChanged) && $nameChanged)

                                <table
                                    role="presentation"
                                    width="100%"
                                    cellspacing="0"
                                    cellpadding="0"
                                    border="0"
                                    style="
                                        margin-bottom: 16px;
                                        background-color: #f7faf9;
                                        border: 1px solid #e1e9e5;
                                        border-radius: 12px;
                                    "
                                >
                                    <tr>
                                        <td style="padding: 18px;">

                                            <strong
                                                style="
                                                    display: block;
                                                    margin-bottom: 10px;
                                                    font-size: 15px;
                                                    color: #183b30;
                                                "
                                            >
                                                Gebruikersnaam gewijzigd
                                            </strong>

                                            <p
                                                style="
                                                    margin: 0 0 6px;
                                                    font-size: 14px;
                                                    line-height: 1.6;
                                                "
                                            >
                                                Oud:
                                                <strong>
                                                    {{ $oldName }}
                                                </strong>
                                            </p>

                                            <p
                                                style="
                                                    margin: 0;
                                                    font-size: 14px;
                                                    line-height: 1.6;
                                                "
                                            >
                                                Nieuw:
                                                <strong>
                                                    {{ $newName }}
                                                </strong>
                                            </p>

                                        </td>
                                    </tr>
                                </table>

                            @endif


                            {{-- EMAIL --}}
                            @if (!empty($emailChanged) && $emailChanged)

                                <table
                                    role="presentation"
                                    width="100%"
                                    cellspacing="0"
                                    cellpadding="0"
                                    border="0"
                                    style="
                                        margin-bottom: 16px;
                                        background-color: #f7faf9;
                                        border: 1px solid #e1e9e5;
                                        border-radius: 12px;
                                    "
                                >
                                    <tr>
                                        <td style="padding: 18px;">

                                            <strong
                                                style="
                                                    display: block;
                                                    margin-bottom: 10px;
                                                    font-size: 15px;
                                                    color: #183b30;
                                                "
                                            >
                                                E-mailadres gewijzigd
                                            </strong>

                                            <p
                                                style="
                                                    margin: 0 0 6px;
                                                    font-size: 14px;
                                                    line-height: 1.6;
                                                "
                                            >
                                                Oud:
                                                <strong>
                                                    {{ $oldEmail }}
                                                </strong>
                                            </p>

                                            <p
                                                style="
                                                    margin: 0;
                                                    font-size: 14px;
                                                    line-height: 1.6;
                                                "
                                            >
                                                Nieuw:
                                                <strong>
                                                    {{ $newEmail }}
                                                </strong>
                                            </p>

                                        </td>
                                    </tr>
                                </table>

                            @endif


                            {{-- WACHTWOORD --}}
                            @if (!empty($passwordChanged) && $passwordChanged)

                                <table
                                    role="presentation"
                                    width="100%"
                                    cellspacing="0"
                                    cellpadding="0"
                                    border="0"
                                    style="
                                        margin-bottom: 16px;
                                        background-color: #fff7e8;
                                        border: 1px solid #f0d7a3;
                                        border-radius: 12px;
                                    "
                                >
                                    <tr>
                                        <td style="padding: 18px;">

                                            <strong
                                                style="
                                                    display: block;
                                                    margin-bottom: 8px;
                                                    font-size: 15px;
                                                    color: #8a5a00;
                                                "
                                            >
                                                Wachtwoord gewijzigd
                                            </strong>

                                            <p
                                                style="
                                                    margin: 0;
                                                    font-size: 14px;
                                                    line-height: 1.6;
                                                    color: #725b2b;
                                                "
                                            >
                                                Je wachtwoord is door een administrator gewijzigd.
                                                Om veiligheidsredenen wordt het wachtwoord
                                                nooit in een e-mail weergegeven.
                                            </p>

                                        </td>
                                    </tr>
                                </table>

                            @endif


                            {{-- ADMINRECHTEN --}}
                            @if (!empty($adminChanged) && $adminChanged)

                                <table
                                    role="presentation"
                                    width="100%"
                                    cellspacing="0"
                                    cellpadding="0"
                                    border="0"
                                    style="
                                        margin-bottom: 16px;
                                        background-color: #eef5ff;
                                        border: 1px solid #cbdcf3;
                                        border-radius: 12px;
                                    "
                                >
                                    <tr>
                                        <td style="padding: 18px;">

                                            <strong
                                                style="
                                                    display: block;
                                                    margin-bottom: 8px;
                                                    font-size: 15px;
                                                    color: #224f87;
                                                "
                                            >
                                                Accountrol gewijzigd
                                            </strong>

                                            <p
                                                style="
                                                    margin: 0 0 6px;
                                                    font-size: 14px;
                                                    line-height: 1.6;
                                                "
                                            >
                                                Oude rol:
                                                <strong>
                                                    {{ !empty($oldIsAdmin) ? 'Administrator' : 'Gebruiker' }}
                                                </strong>
                                            </p>

                                            <p
                                                style="
                                                    margin: 0;
                                                    font-size: 14px;
                                                    line-height: 1.6;
                                                "
                                            >
                                                Nieuwe rol:
                                                <strong>
                                                    {{ !empty($newIsAdmin) ? 'Administrator' : 'Gebruiker' }}
                                                </strong>
                                            </p>

                                        </td>
                                    </tr>
                                </table>

                            @endif


                            {{-- VERIFICATIE --}}
                            @if (!empty($verifiedChanged) && $verifiedChanged)

                                <table
                                    role="presentation"
                                    width="100%"
                                    cellspacing="0"
                                    cellpadding="0"
                                    border="0"
                                    style="
                                        margin-bottom: 16px;
                                        background-color: #f7faf9;
                                        border: 1px solid #e1e9e5;
                                        border-radius: 12px;
                                    "
                                >
                                    <tr>
                                        <td style="padding: 18px;">

                                            <strong
                                                style="
                                                    display: block;
                                                    margin-bottom: 8px;
                                                    font-size: 15px;
                                                    color: #183b30;
                                                "
                                            >
                                                E-mailverificatie gewijzigd
                                            </strong>

                                            <p
                                                style="
                                                    margin: 0 0 6px;
                                                    font-size: 14px;
                                                    line-height: 1.6;
                                                "
                                            >
                                                Oude status:
                                                <strong>
                                                    {{ !empty($oldVerified) ? 'Geverifieerd' : 'Niet geverifieerd' }}
                                                </strong>
                                            </p>

                                            <p
                                                style="
                                                    margin: 0;
                                                    font-size: 14px;
                                                    line-height: 1.6;
                                                "
                                            >
                                                Nieuwe status:
                                                <strong>
                                                    {{ !empty($newVerified) ? 'Geverifieerd' : 'Niet geverifieerd' }}
                                                </strong>
                                            </p>

                                        </td>
                                    </tr>
                                </table>

                            @endif


                            {{-- WIJZIGINGSTIJD --}}
                            @if (!empty($changedAt))

                                <p
                                    style="
                                        margin: 22px 0 0;
                                        font-size: 13px;
                                        color: #7a8b85;
                                    "
                                >
                                    Gewijzigd op:
                                    <strong>
                                        {{ $changedAt }}
                                    </strong>
                                </p>

                            @endif


                            {{-- SECURITY WARNING --}}
                            <table
                                role="presentation"
                                width="100%"
                                cellspacing="0"
                                cellpadding="0"
                                border="0"
                                style="
                                    margin-top: 28px;
                                    background-color: #fff1f1;
                                    border: 1px solid #efc9c9;
                                    border-radius: 12px;
                                "
                            >
                                <tr>
                                    <td style="padding: 18px;">

                                        <strong
                                            style="
                                                display: block;
                                                margin-bottom: 8px;
                                                font-size: 15px;
                                                color: #9b3131;
                                            "
                                        >
                                            Herken je deze wijziging niet?
                                        </strong>

                                        <p
                                            style="
                                                margin: 0;
                                                font-size: 14px;
                                                line-height: 1.6;
                                                color: #7b4d4d;
                                            "
                                        >
                                            Neem dan zo snel mogelijk contact op met
                                            de SmartDesk-beheerder en wijzig je wachtwoord.
                                        </p>

                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>


                    {{-- FOOTER --}}
                    <tr>
                        <td
                            style="
                                padding: 22px 32px;
                                background-color: #f7faf9;
                                border-top: 1px solid #e5ece9;
                                text-align: center;
                            "
                        >

                            <p
                                style="
                                    margin: 0;
                                    font-size: 12px;
                                    line-height: 1.6;
                                    color: #7a8b85;
                                "
                            >
                                Deze e-mail is automatisch verzonden door SmartDesk.
                            </p>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>