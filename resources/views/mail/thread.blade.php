<!doctype html>

<html lang="nl">

<head>

    <meta charset="utf-8">



    <meta

        name="viewport"

        content="width=device-width, initial-scale=1"

    >



    <meta

        name="color-scheme"

        content="light"

    >



    <meta

        name="supported-color-schemes"

        content="light"

    >



    <title>{{ $emailSubject ?? 'Mashal Support' }}</title>



    <style>

        /*

         * Alleen voor clients die <style> ondersteunen.

         * De belangrijkste styling staat daarnaast inline.

         */



        body {

            margin: 0 !important;

            padding: 0 !important;

            width: 100% !important;

            background: #eef2f7;

        }



        table {

            border-spacing: 0;

            border-collapse: collapse;

        }



        img {

            border: 0;

            outline: none;

            text-decoration: none;

            display: block;

        }



        a {

            text-decoration: none;

        }



        @media only screen and (max-width: 640px) {

            .email-wrapper {

                padding: 16px 8px !important;

            }



            .email-card {

                border-radius: 14px !important;

            }



            .mobile-padding {

                padding-left: 20px !important;

                padding-right: 20px !important;

            }



            .mobile-hide {

                display: none !important;

            }



            .mobile-full {

                width: 100% !important;

            }



            .mobile-center {

                text-align: center !important;

            }



            .header-title {

                font-size: 20px !important;

            }

        }

    </style>

</head>



<body

    style="

        margin:0;

        padding:0;

        width:100%;

        background:#eef2f7;

        font-family:

            Inter,

            -apple-system,

            BlinkMacSystemFont,

            'Segoe UI',

            Arial,

            Helvetica,

            sans-serif;

        color:#172033;

        -webkit-text-size-adjust:100%;

        -ms-text-size-adjust:100%;

    "

>



    <!--

        Preheader:

        Dit wordt in veel inboxen naast het onderwerp weergegeven.

    -->

    <div

        style="

            display:none !important;

            visibility:hidden;

            opacity:0;

            overflow:hidden;

            color:transparent;

            height:0;

            width:0;

            max-height:0;

            max-width:0;

            font-size:1px;

            line-height:1px;

            mso-hide:all;

        "

    >

        {{ $emailTitle ?? 'Mashal Support' }} — nieuw bericht in gesprek #{{ $conversationId }}.

        Antwoord rechtstreeks op deze e-mail om het gesprek voort te zetten.

    </div>



    <table

        role="presentation"

        width="100%"

        cellspacing="0"

        cellpadding="0"

        border="0"

        style="

            width:100%;

            margin:0;

            padding:0;

            background:#eef2f7;

        "

    >

        <tr>

            <td

                align="center"

                class="email-wrapper"

                style="

                    padding:42px 14px;

                "

            >



                <!-- Outer container -->

                <table

                    role="presentation"

                    width="100%"

                    cellspacing="0"

                    cellpadding="0"

                    border="0"

                    style="

                        width:100%;

                        max-width:660px;

                        margin:0 auto;

                    "

                >



                    <!-- Brand bar above card -->

                    <tr>

                        <td

                            style="

                                padding:

                                    0

                                    8px

                                    14px

                                    8px;

                            "

                        >

                            <table

                                role="presentation"

                                width="100%"

                                cellspacing="0"

                                cellpadding="0"

                                border="0"

                            >

                                <tr>

                                    <td

                                        align="left"

                                        valign="middle"

                                        style="

                                            font-size:12px;

                                            color:#64748b;

                                        "

                                    >

                                        <strong

                                            style="

                                                color:#334155;

                                                font-size:13px;

                                            "

                                        >

                                            Mashal Support

                                        </strong>



                                        <span

                                            style="

                                                margin-left:6px;

                                                color:#94a3b8;

                                            "

                                        >

                                            Klantenservice

                                        </span>

                                    </td>



                                    <td

                                        align="right"

                                        valign="middle"

                                        style="

                                            font-size:12px;

                                            color:#94a3b8;

                                        "

                                    >

                                        Gesprek #{{ $conversationId }}

                                    </td>

                                </tr>

                            </table>

                        </td>

                    </tr>





                    <!-- Main email card -->

                    <tr>

                        <td

                            class="email-card"

                            style="

                                background:#ffffff;

                                border:

                                    1px solid

                                    #dfe6ef;

                                border-radius:22px;

                                overflow:hidden;

                                box-shadow:

                                    0 18px 45px

                                    rgba(15,23,42,.08);

                            "

                        >



                            <table

                                role="presentation"

                                width="100%"

                                cellspacing="0"

                                cellpadding="0"

                                border="0"

                                style="

                                    width:100%;

                                "

                            >



                                <!-- Premium header -->

                                <tr>

                                    <td

                                        class="mobile-padding"

                                        style="

                                            padding:

                                                30px

                                                32px;

                                            background:

                                                #0f172a;

                                            color:#ffffff;

                                        "

                                    >



                                        <table

                                            role="presentation"

                                            width="100%"

                                            cellspacing="0"

                                            cellpadding="0"

                                            border="0"

                                        >

                                            <tr>



                                                <!-- Logo -->

                                                <td

                                                    width="62"

                                                    valign="middle"

                                                >

                                                    <table

                                                        role="presentation"

                                                        width="52"

                                                        height="52"

                                                        cellspacing="0"

                                                        cellpadding="0"

                                                        border="0"

                                                        style="

                                                            width:52px;

                                                            height:52px;

                                                        "

                                                    >

                                                        <tr>

                                                            <td

                                                                align="center"

                                                                valign="middle"

                                                                style="

                                                                    width:52px;

                                                                    height:52px;

                                                                    border-radius:15px;

                                                                    background:#ffffff;

                                                                    color:#0f172a;

                                                                    font-size:23px;

                                                                    font-weight:800;

                                                                    line-height:52px;

                                                                "

                                                            >

                                                                M

                                                            </td>

                                                        </tr>

                                                    </table>

                                                </td>



                                                <!-- Brand title -->

                                                <td

                                                    valign="middle"

                                                    style="

                                                        padding-left:14px;

                                                    "

                                                >

                                                    <div

                                                        class="header-title"

                                                        style="

                                                            font-size:22px;

                                                            line-height:1.25;

                                                            font-weight:750;

                                                            color:#ffffff;

                                                        "

                                                    >

                                                        Mashal Support

                                                    </div>



                                                    <div

                                                        style="

                                                            margin-top:5px;

                                                            font-size:12px;

                                                            line-height:1.5;

                                                            color:#94a3b8;

                                                        "

                                                    >

                                                        Persoonlijke ondersteuning

                                                        via e-mail

                                                    </div>

                                                </td>



                                                <!-- Status -->

                                                <td

                                                    align="right"

                                                    valign="middle"

                                                    class="mobile-hide"

                                                    style="

                                                        padding-left:10px;

                                                    "

                                                >

                                                    <span

                                                        style="

                                                            display:inline-block;

                                                            padding:

                                                                8px

                                                                12px;

                                                            border-radius:999px;

                                                            background:#14392f;

                                                            border:

                                                                1px solid

                                                                #235b49;

                                                            color:#86efac;

                                                            font-size:10px;

                                                            line-height:1;

                                                            letter-spacing:.7px;

                                                            font-weight:800;

                                                        "

                                                    >

                                                        ● ACTIEF

                                                    </span>

                                                </td>



                                            </tr>

                                        </table>



                                    </td>

                                </tr>





                                <!-- Decorative divider -->

                                <tr>

                                    <td

                                        style="

                                            height:4px;

                                            line-height:4px;

                                            font-size:1px;

                                            background:

                                                linear-gradient(

                                                    90deg,

                                                    #2563eb,

                                                    #7c3aed,

                                                    #0ea5e9

                                                );

                                        "

                                    >

                                        &nbsp;

                                    </td>

                                </tr>





                                <!-- Intro -->

                                <tr>

                                    <td

                                        class="mobile-padding"

                                        style="

                                            padding:

                                                34px

                                                34px

                                                10px

                                                34px;

                                        "

                                    >



                                        @if($customerName)

                                            <div

                                                style="

                                                    margin:0 0 7px 0;

                                                    font-size:17px;

                                                    line-height:1.5;

                                                    font-weight:700;

                                                    color:#172033;

                                                "

                                            >

                                                Hallo {{ $customerName }},

                                            </div>

                                        @else

                                            <div

                                                style="

                                                    margin:0 0 7px 0;

                                                    font-size:17px;

                                                    line-height:1.5;

                                                    font-weight:700;

                                                    color:#172033;

                                                "

                                            >

                                                Hallo,

                                            </div>

                                        @endif



                                        <div

                                            style="

                                                font-size:14px;

                                                line-height:1.75;

                                                color:#64748b;

                                            "

                                        >

                                            Er staat een nieuw bericht voor u klaar

                                            van Mashal Support. U kunt rechtstreeks

                                            vanuit uw e-mail reageren.

                                        </div>



                                    </td>

                                </tr>





                                <!-- Meta / conversation information -->

                                <tr>

                                    <td

                                        class="mobile-padding"

                                        style="

                                            padding:

                                                18px

                                                34px

                                                8px

                                                34px;

                                        "

                                    >



                                        <table

                                            role="presentation"

                                            width="100%"

                                            cellspacing="0"

                                            cellpadding="0"

                                            border="0"

                                            style="

                                                width:100%;

                                                background:#f8fafc;

                                                border:

                                                    1px solid

                                                    #e6ebf2;

                                                border-radius:12px;

                                            "

                                        >



                                            <tr>

                                                <td

                                                    style="

                                                        padding:

                                                            13px

                                                            15px;

                                                    "

                                                >



                                                    <table

                                                        role="presentation"

                                                        width="100%"

                                                        cellspacing="0"

                                                        cellpadding="0"

                                                        border="0"

                                                    >

                                                        <tr>



                                                            <td

                                                                style="

                                                                    font-size:11px;

                                                                    line-height:1.4;

                                                                    color:#94a3b8;

                                                                "

                                                            >

                                                                SUPPORTGESPREK

                                                            </td>



                                                            <td

                                                                align="right"

                                                                style="

                                                                    font-size:12px;

                                                                    line-height:1.4;

                                                                    color:#334155;

                                                                    font-weight:700;

                                                                "

                                                            >

                                                                #{{ $conversationId }}

                                                            </td>



                                                        </tr>



                                                        <tr>

                                                            <td

                                                                colspan="2"

                                                                style="

                                                                    padding-top:6px;

                                                                    font-size:12px;

                                                                    color:#64748b;

                                                                "

                                                            >

                                                                Uw gesprek is momenteel

                                                                actief via e-mail.

                                                            </td>

                                                        </tr>

                                                    </table>



                                                </td>

                                            </tr>



                                        </table>



                                    </td>

                                </tr>





                                <!-- Message title -->

                                <tr>

                                    <td

                                        class="mobile-padding"

                                        style="

                                            padding:

                                                24px

                                                34px

                                                8px

                                                34px;

                                        "

                                    >



                                        <table

                                            role="presentation"

                                            cellspacing="0"

                                            cellpadding="0"

                                            border="0"

                                        >

                                            <tr>

                                                <td

                                                    valign="middle"

                                                >

                                                    <table

                                                        role="presentation"

                                                        width="34"

                                                        height="34"

                                                        cellspacing="0"

                                                        cellpadding="0"

                                                        border="0"

                                                    >

                                                        <tr>

                                                            <td

                                                                align="center"

                                                                valign="middle"

                                                                style="

                                                                    width:34px;

                                                                    height:34px;

                                                                    line-height:34px;

                                                                    border-radius:10px;

                                                                    background:#eff6ff;

                                                                    color:#2563eb;

                                                                    font-size:16px;

                                                                    font-weight:800;

                                                                "

                                                            >

                                                                ✉

                                                            </td>

                                                        </tr>

                                                    </table>

                                                </td>



                                                <td

                                                    valign="middle"

                                                    style="

                                                        padding-left:10px;

                                                    "

                                                >

                                                    <div

                                                        style="

                                                            font-size:14px;

                                                            font-weight:700;

                                                            color:#334155;

                                                        "

                                                    >

                                                        {{ $emailTitle ?? 'Nieuw bericht' }}

                                                    </div>



                                                    <div

                                                        style="

                                                            margin-top:2px;

                                                            font-size:11px;

                                                            color:#94a3b8;

                                                        "

                                                    >

                                                        Van Mashal Support

                                                    </div>

                                                </td>

                                            </tr>

                                        </table>



                                    </td>

                                </tr>





                                <!-- Actual message -->

                                <tr>

                                    <td

                                        class="mobile-padding"

                                        style="

                                            padding:

                                                10px

                                                34px

                                                12px

                                                34px;

                                        "

                                    >



                                        <table

                                            role="presentation"

                                            width="100%"

                                            cellspacing="0"

                                            cellpadding="0"

                                            border="0"

                                            style="

                                                width:100%;

                                                border-collapse:separate;

                                            "

                                        >

                                            <tr>

                                                <td

                                                    style="

                                                        padding:

                                                            24px

                                                            24px;

                                                        background:#f8fafc;

                                                        border:

                                                            1px solid

                                                            #e2e8f0;

                                                        border-radius:16px;

                                                        box-shadow:

                                                            inset

                                                            3px

                                                            0

                                                            0

                                                            #2563eb;

                                                    "

                                                >



                                                    <div

                                                        style="

                                                            font-size:15px;

                                                            line-height:1.8;

                                                            color:#1e293b;

                                                            word-break:break-word;

                                                        "

                                                    >{!! nl2br(e($bodyText)) !!}</div>

                                                    @if (!empty($videoLinks))
                                                        <table
                                                            role="presentation"
                                                            width="100%"
                                                            cellspacing="0"
                                                            cellpadding="0"
                                                            border="0"
                                                            style="margin-top:22px;width:100%;"
                                                        >
                                                            @foreach ($videoLinks as $video)
                                                                <tr>
                                                                    <td
                                                                        style="
                                                                            padding:16px;
                                                                            border:1px solid #dbe4ee;
                                                                            border-radius:14px;
                                                                            background:#f8fafc;
                                                                        "
                                                                    >
                                                                        <div
                                                                            style="
                                                                                font-size:15px;
                                                                                line-height:22px;
                                                                                font-weight:700;
                                                                                color:#0f172a;
                                                                                margin-bottom:5px;
                                                                            "
                                                                        >
                                                                            🎬 {{ $video['name'] ?? 'Video uit de chat' }}
                                                                        </div>

                                                                        @if (!empty($video['size']))
                                                                            <div
                                                                                style="
                                                                                    font-size:12px;
                                                                                    line-height:18px;
                                                                                    color:#64748b;
                                                                                    margin-bottom:12px;
                                                                                "
                                                                            >
                                                                                {{ $video['size'] }}
                                                                            </div>
                                                                        @endif

                                                                        <a
                                                                            href="{{ $video['url'] ?? '#' }}"
                                                                            target="_blank"
                                                                            rel="noopener noreferrer"
                                                                            style="
                                                                                display:inline-block;
                                                                                padding:11px 16px;
                                                                                border-radius:10px;
                                                                                background:#0f172a;
                                                                                color:#ffffff;
                                                                                font-size:14px;
                                                                                line-height:18px;
                                                                                font-weight:700;
                                                                                text-decoration:none;
                                                                            "
                                                                        >
                                                                            ▶ Video bekijken / downloaden
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td style="height:10px;line-height:10px;font-size:1px;">&nbsp;</td>
                                                                </tr>
                                                            @endforeach
                                                        </table>
                                                    @endif



                                                </td>

                                            </tr>

                                        </table>



                                    </td>

                                </tr>





                                <!-- Reply CTA -->

                                <tr>

                                    <td

                                        class="mobile-padding"

                                        style="

                                            padding:

                                                18px

                                                34px

                                                10px

                                                34px;

                                        "

                                    >



                                        <table

                                            role="presentation"

                                            width="100%"

                                            cellspacing="0"

                                            cellpadding="0"

                                            border="0"

                                            style="

                                                width:100%;

                                                background:#eef6ff;

                                                border:

                                                    1px solid

                                                    #bfdbfe;

                                                border-radius:16px;

                                            "

                                        >



                                            <tr>



                                                <td

                                                    width="58"

                                                    valign="top"

                                                    style="

                                                        padding:

                                                            20px

                                                            0

                                                            20px

                                                            20px;

                                                    "

                                                >



                                                    <table

                                                        role="presentation"

                                                        width="38"

                                                        height="38"

                                                        cellspacing="0"

                                                        cellpadding="0"

                                                        border="0"

                                                    >

                                                        <tr>

                                                            <td

                                                                align="center"

                                                                valign="middle"

                                                                style="

                                                                    width:38px;

                                                                    height:38px;

                                                                    line-height:38px;

                                                                    border-radius:11px;

                                                                    background:#2563eb;

                                                                    color:#ffffff;

                                                                    font-size:19px;

                                                                    font-weight:700;

                                                                "

                                                            >

                                                                ↩

                                                            </td>

                                                        </tr>

                                                    </table>



                                                </td>



                                                <td

                                                    valign="top"

                                                    style="

                                                        padding:

                                                            19px

                                                            20px

                                                            19px

                                                            10px;

                                                    "

                                                >



                                                    <div

                                                        style="

                                                            margin-bottom:5px;

                                                            font-size:14px;

                                                            line-height:1.4;

                                                            font-weight:750;

                                                            color:#1e3a8a;

                                                        "

                                                    >

                                                        Antwoord rechtstreeks op deze e-mail

                                                    </div>



                                                    <div

                                                        style="

                                                            font-size:13px;

                                                            line-height:1.7;

                                                            color:#475569;

                                                        "

                                                    >

                                                        Gebruik simpelweg de knop

                                                        <strong>

                                                            Beantwoorden

                                                        </strong>

                                                        in Gmail, Outlook of uw

                                                        andere e-mailprogramma.

                                                        Uw reactie wordt automatisch

                                                        toegevoegd aan hetzelfde

                                                        supportgesprek.

                                                    </div>



                                                </td>



                                            </tr>



                                        </table>



                                    </td>

                                </tr>





                                <!-- Thread explanation -->

                                <tr>

                                    <td

                                        class="mobile-padding"

                                        style="

                                            padding:

                                                14px

                                                34px

                                                10px

                                                34px;

                                        "

                                    >



                                        <table

                                            role="presentation"

                                            width="100%"

                                            cellspacing="0"

                                            cellpadding="0"

                                            border="0"

                                        >

                                            <tr>

                                                <td

                                                    style="

                                                        padding:

                                                            16px

                                                            18px;

                                                        border:

                                                            1px dashed

                                                            #cbd5e1;

                                                        border-radius:13px;

                                                        background:#ffffff;

                                                    "

                                                >



                                                    <div

                                                        style="

                                                            margin-bottom:7px;

                                                            font-size:12px;

                                                            font-weight:700;

                                                            color:#475569;

                                                        "

                                                    >

                                                        Eén doorlopend gesprek

                                                    </div>



                                                    <div

                                                        style="

                                                            font-size:12px;

                                                            line-height:1.7;

                                                            color:#64748b;

                                                        "

                                                    >

                                                        U hoeft geen nieuwe e-mail

                                                        of nieuw supportgesprek te

                                                        starten. Wanneer u op deze

                                                        e-mail antwoordt, blijft de

                                                        volledige communicatie

                                                        gekoppeld aan gesprek

                                                        <strong>

                                                            #{{ $conversationId }}

                                                        </strong>.

                                                    </div>



                                                </td>

                                            </tr>

                                        </table>



                                    </td>

                                </tr>





                                <!-- Support details -->

                                <tr>

                                    <td

                                        class="mobile-padding"

                                        style="

                                            padding:

                                                22px

                                                34px

                                                30px

                                                34px;

                                        "

                                    >



                                        <table

                                            role="presentation"

                                            width="100%"

                                            cellspacing="0"

                                            cellpadding="0"

                                            border="0"

                                            style="

                                                border-top:

                                                    1px solid

                                                    #edf1f5;

                                            "

                                        >



                                            <tr>

                                                <td

                                                    style="

                                                        padding-top:18px;

                                                    "

                                                >



                                                    <table

                                                        role="presentation"

                                                        width="100%"

                                                        cellspacing="0"

                                                        cellpadding="0"

                                                        border="0"

                                                    >



                                                        <tr>

                                                            <td

                                                                style="

                                                                    padding:

                                                                        6px

                                                                        0;

                                                                    font-size:12px;

                                                                    color:#94a3b8;

                                                                "

                                                            >

                                                                Gespreksnummer

                                                            </td>



                                                            <td

                                                                align="right"

                                                                style="

                                                                    padding:

                                                                        6px

                                                                        0;

                                                                    font-size:12px;

                                                                    color:#334155;

                                                                    font-weight:700;

                                                                "

                                                            >

                                                                #{{ $conversationId }}

                                                            </td>

                                                        </tr>

                                                        <tr>
                                                            <td
                                                                colspan="2"
                                                                style="
                                                                    padding-top:8px;
                                                                    font-size:12px;
                                                                    line-height:1.5;
                                                                    color:#64748b;
                                                                "
                                                            >
                                                                Onderwerp:
                                                                <strong style="color:#334155;">
                                                                    {{ $emailSubject ?? ('Mashal Support · gesprek #'.$conversationId) }}
                                                                </strong>
                                                            </td>
                                                        </tr>



                                                        <tr>

                                                            <td

                                                                style="

                                                                    padding:

                                                                        6px

                                                                        0;

                                                                    font-size:12px;

                                                                    color:#94a3b8;

                                                                "

                                                            >

                                                                Kanaal

                                                            </td>



                                                            <td

                                                                align="right"

                                                                style="

                                                                    padding:

                                                                        6px

                                                                        0;

                                                                    font-size:12px;

                                                                    color:#334155;

                                                                    font-weight:600;

                                                                "

                                                            >

                                                                E-mail support

                                                            </td>

                                                        </tr>



                                                        <tr>

                                                            <td

                                                                style="

                                                                    padding:

                                                                        6px

                                                                        0;

                                                                    font-size:12px;

                                                                    color:#94a3b8;

                                                                "

                                                            >

                                                                Status

                                                            </td>



                                                            <td

                                                                align="right"

                                                                style="

                                                                    padding:

                                                                        6px

                                                                        0;

                                                                    font-size:12px;

                                                                    color:#15803d;

                                                                    font-weight:700;

                                                                "

                                                            >

                                                                ● Actief

                                                            </td>

                                                        </tr>



                                                    </table>



                                                </td>

                                            </tr>



                                        </table>



                                    </td>

                                </tr>





                                <!-- Bottom support footer inside card -->

                                <tr>

                                    <td

                                        class="mobile-padding"

                                        style="

                                            padding:

                                                20px

                                                34px;

                                            background:#f8fafc;

                                            border-top:

                                                1px solid

                                                #e8edf3;

                                        "

                                    >



                                        <table

                                            role="presentation"

                                            width="100%"

                                            cellspacing="0"

                                            cellpadding="0"

                                            border="0"

                                        >

                                            <tr>

                                                <td

                                                    valign="middle"

                                                >



                                                    <div

                                                        style="

                                                            font-size:12px;

                                                            line-height:1.5;

                                                            font-weight:700;

                                                            color:#475569;

                                                        "

                                                    >

                                                        Mashal Support

                                                    </div>



                                                    <div

                                                        style="

                                                            margin-top:3px;

                                                            font-size:11px;

                                                            line-height:1.5;

                                                            color:#94a3b8;

                                                        "

                                                    >

                                                        Wij helpen u graag verder.

                                                    </div>



                                                </td>



                                                <td

                                                    align="right"

                                                    valign="middle"

                                                    class="mobile-hide"

                                                    style="

                                                        font-size:11px;

                                                        color:#94a3b8;

                                                    "

                                                >

                                                    Support #{{ $conversationId }}

                                                </td>

                                            </tr>

                                        </table>



                                    </td>

                                </tr>



                            </table>



                        </td>

                    </tr>





                    <!-- External footer -->

                    <tr>

                        <td

                            align="center"

                            style="

                                padding:

                                    24px

                                    24px

                                    8px

                                    24px;

                            "

                        >



                            <div

                                style="

                                    font-size:11px;

                                    line-height:1.7;

                                    color:#94a3b8;

                                "

                            >

                                Deze e-mail is onderdeel van uw

                                communicatie met

                                <strong

                                    style="

                                        color:#64748b;

                                    "

                                >

                                    Mashal Support

                                </strong>.

                            </div>



                            <div

                                style="

                                    margin-top:5px;

                                    font-size:11px;

                                    line-height:1.7;

                                    color:#a8b2c1;

                                "

                            >

                                Antwoord rechtstreeks op deze e-mail

                                om uw gesprek voort te zetten.

                            </div>



                            <div

                                style="

                                    margin-top:15px;

                                    font-size:10px;

                                    line-height:1.6;

                                    color:#c0c8d3;

                                "

                            >

                                Gesprek #{{ $conversationId }}

                                &nbsp;•&nbsp;

                                Mashal Support

                            </div>



                        </td>

                    </tr>





                    <!-- Bottom spacing -->

                    <tr>

                        <td

                            style="

                                height:10px;

                                font-size:1px;

                                line-height:1px;

                            "

                        >

                            &nbsp;

                        </td>

                    </tr>



                </table>



            </td>

        </tr>

    </table>



</body>

</html>