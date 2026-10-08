<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">



    <meta

        name="viewport"

        content="width=device-width, initial-scale=1, viewport-fit=cover"

    >



    <meta

        name="csrf-token"

        content="{{ csrf_token() }}"

    >



    <title>Telegram login | {{ config('app.name', 'Mashal Studio') }}</title>



    <script src="https://telegram.org/js/telegram-web-app.js"></script>



    <style>

        :root {

            color-scheme: dark;



            --tg-bg: #080d13;

            --tg-card: rgba(17, 25, 35, .96);

            --tg-line: rgba(255,255,255,.10);

            --tg-text: #f8fafc;

            --tg-muted: #94a3b8;



            --tg-blue: #229ed9;

            --tg-blue-light: #62cbf5;



            --tg-success: #9be8bc;

            --tg-success-bg: rgba(71,192,126,.075);

            --tg-success-line: rgba(101,213,154,.22);



            --tg-danger: #ffb1b1;

            --tg-danger-bg: rgba(255,80,80,.07);

            --tg-danger-line: rgba(255,120,120,.22);

        }



        * {

            box-sizing: border-box;

        }



        html,

        body {

            margin: 0;

            min-height: 100%;

        }



        body {

            min-width: 0;

            padding:

                max(24px, env(safe-area-inset-top))

                16px

                max(28px, env(safe-area-inset-bottom));



            color: var(--tg-text);



            background:

                radial-gradient(

                    circle at 50% 0%,

                    rgba(34,158,217,.24),

                    transparent 28rem

                ),

                linear-gradient(

                    180deg,

                    #101923,

                    var(--tg-bg)

                );



            font-family:

                Inter,

                ui-sans-serif,

                system-ui,

                -apple-system,

                BlinkMacSystemFont,

                "Segoe UI",

                sans-serif;

        }



        button {

            -webkit-tap-highlight-color: transparent;

        }



        .telegram-mini-app {

            width: min(100%, 470px);

            margin: 0 auto;

        }



        .telegram-mini-card {

            position: relative;

            overflow: hidden;



            width: 100%;

            padding: 30px 22px 24px;



            border: 1px solid var(--tg-line);

            border-radius: 26px;



            background:

                linear-gradient(

                    145deg,

                    rgba(255,255,255,.045),

                    transparent 34%

                ),

                var(--tg-card);



            box-shadow:

                0 24px 70px rgba(0,0,0,.28),

                inset 0 1px 0 rgba(255,255,255,.035);



            text-align: center;

        }



        .telegram-mini-logo-shell {

            width: 102px;

            height: 102px;



            margin: 0 auto 20px;



            display: grid;

            place-items: center;



            border: 1px solid rgba(108,207,248,.24);

            border-radius: 50%;



            background:

                radial-gradient(

                    circle at 35% 24%,

                    rgba(117,216,255,.24),

                    rgba(34,158,217,.07) 54%,

                    transparent 75%

                );



            box-shadow:

                0 17px 38px rgba(34,158,217,.18);

        }



        .telegram-mini-logo {

            width: 92px;

            height: 92px;



            display: block;



            object-fit: contain;

            border-radius: 50%;



            filter:

                drop-shadow(

                    0 11px 22px rgba(34,158,217,.20)

                );

        }



        .telegram-mini-logo-fallback {

            width: 82px;

            height: 82px;



            display: none;

            place-items: center;



            border-radius: 50%;



            background:

                linear-gradient(

                    145deg,

                    var(--tg-blue-light),

                    var(--tg-blue)

                );



            box-shadow:

                inset 0 1px 0 rgba(255,255,255,.32),

                0 14px 32px rgba(34,158,217,.22);

        }



        .telegram-mini-logo-fallback svg {

            width: 46px;

            height: 46px;

            fill: #fff;

        }



        .telegram-mini-kicker {

            margin: 0 0 9px;



            color: #74c8ec;



            font-size: 10px;

            font-weight: 900;

            letter-spacing: .14em;

            text-transform: uppercase;

        }



        .telegram-mini-title {

            margin: 0;



            color: #f9fbfc;



            font-size: clamp(26px, 8vw, 34px);

            font-weight: 900;

            line-height: 1.05;

            letter-spacing: -.045em;



            text-wrap: balance;

        }



        .telegram-mini-copy {

            max-width: 370px;



            margin: 13px auto 0;



            color: var(--tg-muted);



            font-size: 13px;

            line-height: 1.7;

        }



        .telegram-mini-status {

            margin-top: 24px;

            padding: 16px 15px;



            border: 1px solid var(--tg-line);

            border-radius: 16px;



            color: var(--tg-muted);

            background: rgba(255,255,255,.035);



            font-size: 12px;

            line-height: 1.55;

        }



        .telegram-mini-status.is-success {

            border-color: var(--tg-success-line);

            color: var(--tg-success);

            background: var(--tg-success-bg);

        }



        .telegram-mini-status.is-error {

            border-color: var(--tg-danger-line);

            color: var(--tg-danger);

            background: var(--tg-danger-bg);

        }



        .telegram-mini-spinner {

            width: 24px;

            height: 24px;



            margin: 0 auto 11px;



            border: 2px solid rgba(255,255,255,.14);

            border-top-color: var(--tg-blue-light);

            border-radius: 50%;



            animation: telegramMiniSpin .8s linear infinite;

        }



        .telegram-mini-button {

            width: 100%;

            min-height: 54px;



            margin-top: 16px;

            padding: 0 17px;



            display: none;

            align-items: center;

            justify-content: center;

            gap: 10px;



            border: 0;

            border-radius: 15px;



            color: #fff;



            background:

                linear-gradient(

                    180deg,

                    #35b0e9,

                    #178fcb

                );



            box-shadow:

                0 13px 30px rgba(34,158,217,.24),

                inset 0 1px 0 rgba(255,255,255,.22);



            font: inherit;

            font-size: 14px;

            font-weight: 900;



            cursor: pointer;

        }



        .telegram-mini-button.is-visible {

            display: inline-flex;

        }



        .telegram-mini-button:disabled {

            cursor: wait;

            opacity: .65;

        }



        .telegram-mini-button svg {

            width: 22px;

            height: 22px;

            fill: currentColor;

        }



        .telegram-mini-retry {

            min-height: 42px;



            margin-top: 12px;

            padding: 0 15px;



            display: none;

            align-items: center;

            justify-content: center;



            border: 1px solid rgba(255,255,255,.10);

            border-radius: 12px;



            color: #cbd5e1;

            background: rgba(255,255,255,.035);



            font: inherit;

            font-size: 12px;

            font-weight: 850;



            cursor: pointer;

        }



        .telegram-mini-retry.is-visible {

            display: inline-flex;

        }



        .telegram-mini-note {

            margin: 18px auto 0;



            color: #6f7d8d;



            font-size: 10px;

            line-height: 1.65;

        }



        .telegram-mini-debug {

            margin-top: 12px;

            padding-top: 12px;



            display: none;



            border-top: 1px solid rgba(255,255,255,.06);



            color: #64748b;



            font-size: 9px;

            line-height: 1.5;

            text-align: left;

            word-break: break-word;

        }



        .telegram-mini-debug.is-visible {

            display: block;

        }



        @keyframes telegramMiniSpin {

            to {

                transform: rotate(360deg);

            }

        }



        @media (max-width: 380px) {

            body {

                padding-inline: 12px;

            }



            .telegram-mini-card {

                padding: 26px 17px 22px;

                border-radius: 22px;

            }



            .telegram-mini-logo-shell {

                width: 92px;

                height: 92px;

            }



            .telegram-mini-logo {

                width: 84px;

                height: 84px;

            }

        }



        @media (prefers-reduced-motion: reduce) {

            .telegram-mini-spinner {

                animation: none !important;

            }

        }

    </style>

</head>



<body>

    <main class="telegram-mini-app">

        <section class="telegram-mini-card">

            <div

                class="telegram-mini-logo-shell"

                aria-hidden="true"

            >

                <img

                    class="telegram-mini-logo"

                    id="telegramMiniLogo"

                    src="{{ asset('images/social/telegram-logo.png') }}"

                    alt=""

                    loading="eager"

                    decoding="async"

                >



                <div

                    class="telegram-mini-logo-fallback"

                    id="telegramMiniLogoFallback"

                >

                    <svg viewBox="0 0 24 24">

                        <path

                            d="M21.944 2.506c.281-.107.574.046.477.416l-3.566 16.827c-.084.398-.319.495-.647.309l-5.433-4.005-2.621 2.522c-.29.29-.533.532-1.093.532l.39-5.536L19.53 4.466c.438-.39-.095-.607-.68-.217L6.394 12.09l-5.363-1.676c-.585-.183-.596-.585.122-.866L21.944 2.506Z"

                        ></path>

                    </svg>

                </div>

            </div>



            <p class="telegram-mini-kicker">

                Telegram Secure Login

            </p>



            <h1 class="telegram-mini-title">

                Inloggen bij Mashal Studio

            </h1>



            <p class="telegram-mini-copy">

                Telegram bevestigt eerst je account.

                Daarna maken we een korte eenmalige link

                waarmee je veilig teruggaat naar Mashal Studio.

            </p>



            <div

                class="telegram-mini-status"

                id="telegramMiniStatus"

                role="status"

                aria-live="polite"

            >

                <div

                    class="telegram-mini-spinner"

                    id="telegramMiniSpinner"

                    aria-hidden="true"

                ></div>



                <span id="telegramMiniStatusText">

                    Telegram-account controleren…

                </span>

            </div>



            <button

                class="telegram-mini-button"

                id="telegramMiniContinue"

                type="button"

            >

                <svg

                    viewBox="0 0 24 24"

                    aria-hidden="true"

                >

                    <path

                        d="M21.944 2.506c.281-.107.574.046.477.416l-3.566 16.827c-.084.398-.319.495-.647.309l-5.433-4.005-2.621 2.522c-.29.29-.533.532-1.093.532l.39-5.536L19.53 4.466c.438-.39-.095-.607-.68-.217L6.394 12.09l-5.363-1.676c-.585-.183-.596-.585.122-.866L21.944 2.506Z"

                    ></path>

                </svg>



                <span id="telegramMiniContinueText">

                    Doorgaan naar Mashal Studio

                </span>

            </button>



            <button

                class="telegram-mini-retry"

                id="telegramMiniRetry"

                type="button"

            >

                {{ __('Opnieuw proberen') }}

            </button>



            <p class="telegram-mini-note">

                Je bot-token wordt nooit naar deze pagina gestuurd.

                De Telegram-handtekening wordt uitsluitend

                door Laravel op de server gecontroleerd.

            </p>



            <div

                class="telegram-mini-debug"

                id="telegramMiniDebug"

                aria-hidden="true"

            ></div>

        </section>

    </main>



    <script>

        (function () {

            'use strict';



            /*

            |--------------------------------------------------------------------------

            | Belangrijk

            |--------------------------------------------------------------------------

            | Hier gebruiken we bewust GEEN Blade route('...') helper.

            | Daardoor kan deze view gewoon renderen, ook als route-cache nog oud is.

            */

            const endpoint =

                '/auth/telegram/mini-app/auth';



            const statusBox =

                document.getElementById(

                    'telegramMiniStatus'

                );



            const statusText =

                document.getElementById(

                    'telegramMiniStatusText'

                );



            const spinner =

                document.getElementById(

                    'telegramMiniSpinner'

                );



            const continueButton =

                document.getElementById(

                    'telegramMiniContinue'

                );



            const continueText =

                document.getElementById(

                    'telegramMiniContinueText'

                );



            const retryButton =

                document.getElementById(

                    'telegramMiniRetry'

                );



            const debugBox =

                document.getElementById(

                    'telegramMiniDebug'

                );



            const logo =

                document.getElementById(

                    'telegramMiniLogo'

                );



            const logoFallback =

                document.getElementById(

                    'telegramMiniLogoFallback'

                );



            const csrfMeta =

                document.querySelector(

                    'meta[name="csrf-token"]'

                );



            const csrfToken =

                csrfMeta

                    ? csrfMeta.getAttribute('content')

                    : '';



            const telegram =

                window.Telegram

                && window.Telegram.WebApp

                    ? window.Telegram.WebApp

                    : null;



            let handoffUrl = '';

            let authenticating = false;



            function showFallbackLogo() {

                if (logo) {

                    logo.style.display = 'none';

                }



                if (logoFallback) {

                    logoFallback.style.display = 'grid';

                }

            }



            function resetDebug() {

                if (!debugBox) {

                    return;

                }



                debugBox.textContent = '';

                debugBox.classList.remove(

                    'is-visible'

                );

            }



            function setLoading(message) {

                statusBox.className =

                    'telegram-mini-status';



                statusText.textContent =

                    message

                    || 'Telegram-account controleren…';



                if (spinner) {

                    spinner.style.display = 'block';

                }



                continueButton.classList.remove(

                    'is-visible'

                );



                retryButton.classList.remove(

                    'is-visible'

                );

            }



            function setSuccess(

                message,

                buttonLabel

            ) {

                statusBox.className =

                    'telegram-mini-status is-success';



                statusText.textContent =

                    message;



                if (spinner) {

                    spinner.style.display = 'none';

                }



                continueText.textContent =

                    buttonLabel

                    || 'Doorgaan naar Mashal Studio';



                continueButton.classList.add(

                    'is-visible'

                );



                retryButton.classList.remove(

                    'is-visible'

                );

            }



            function setError(

                message,

                debugMessage

            ) {

                statusBox.className =

                    'telegram-mini-status is-error';



                statusText.textContent =

                    message;



                if (spinner) {

                    spinner.style.display = 'none';

                }



                continueButton.classList.remove(

                    'is-visible'

                );



                retryButton.classList.add(

                    'is-visible'

                );



                if (

                    debugMessage

                    && debugBox

                ) {

                    debugBox.textContent =

                        debugMessage;



                    debugBox.classList.add(

                        'is-visible'

                    );

                }

            }



            async function readResponse(response) {

                const contentType =

                    response.headers.get(

                        'content-type'

                    ) || '';



                if (

                    contentType.includes(

                        'application/json'

                    )

                ) {

                    return await response.json();

                }



                const raw =

                    await response.text();



                throw new Error(

                    'HTTP '

                    + response.status

                    + ': de server gaf geen JSON terug.'

                    + (

                        raw

                            ? ' Controleer de Laravel/Railway logs.'

                            : ''

                    )

                );

            }



            async function authenticate() {

                if (authenticating) {

                    return;

                }



                authenticating = true;

                handoffUrl = '';



                resetDebug();



                setLoading(

                    'Telegram-account controleren…'

                );



                if (

                    !telegram

                    || !telegram.initData

                ) {

                    authenticating = false;



                    setError(

                        'Open deze pagina vanuit de Telegram-app via jouw bot.',

                        'Telegram.WebApp.initData ontbreekt. '

                        + 'Open de Mini App vanuit Telegram, niet rechtstreeks in een gewone browser.'

                    );



                    return;

                }



                try {

                    if (

                        typeof telegram.ready

                        === 'function'

                    ) {

                        telegram.ready();

                    }



                    if (

                        typeof telegram.expand

                        === 'function'

                    ) {

                        telegram.expand();

                    }



                    const response =

                        await fetch(

                            endpoint,

                            {

                                method: 'POST',



                                credentials:

                                    'same-origin',



                                headers: {

                                    'Accept':

                                        'application/json',



                                    'Content-Type':

                                        'application/json',



                                    'X-CSRF-TOKEN':

                                        csrfToken,



                                    'X-Requested-With':

                                        'XMLHttpRequest',

                                },



                                body:

                                    JSON.stringify({

                                        init_data:

                                            telegram.initData,

                                    }),

                            }

                        );



                    const data =

                        await readResponse(

                            response

                        );



                    if (

                        !response.ok

                        || !data

                        || data.ok !== true

                        || !data.handoff_url

                    ) {

                        throw new Error(

                            data

                            && data.message

                                ? data.message

                                : 'Telegram-login kon niet veilig worden bevestigd.'

                        );

                    }



                    handoffUrl =

                        String(

                            data.handoff_url

                        );



                    if (data.new_user) {

                        setSuccess(

                            'Telegram is bevestigd. '

                            + 'Tik hieronder om je registratie '

                            + 'bij Mashal Studio af te ronden.',

                            'Registratie afronden'

                        );

                    } else {

                        setSuccess(

                            'Telegram is bevestigd. '

                            + 'Tik hieronder om veilig '

                            + 'terug te gaan naar Mashal Studio.',

                            'Doorgaan naar Mashal Studio'

                        );

                    }

                } catch (error) {

                    setError(

                        'Telegram-login kon niet worden bevestigd.',

                        error instanceof Error

                            ? error.message

                            : 'Onbekende fout tijdens Telegram-login.'

                    );

                } finally {

                    authenticating = false;

                }

            }



            function openHandoff() {

                if (!handoffUrl) {

                    return;

                }



                continueButton.disabled = true;



                continueText.textContent =

                    'Mashal Studio openen…';



                try {

                    if (

                        telegram

                        && typeof telegram.openLink

                            === 'function'

                    ) {

                        telegram.openLink(

                            handoffUrl

                        );



                        window.setTimeout(

                            function () {

                                continueButton.disabled =

                                    false;



                                continueText.textContent =

                                    'Doorgaan naar Mashal Studio';

                            },

                            1800

                        );



                        return;

                    }

                } catch (error) {

                    // Valt hieronder terug op normale browsernavigatie.

                }



                window.location.assign(

                    handoffUrl

                );

            }



            if (logo) {

                logo.addEventListener(

                    'error',

                    showFallbackLogo,

                    {

                        once: true,

                    }

                );

            }



            continueButton.addEventListener(

                'click',

                openHandoff

            );



            retryButton.addEventListener(

                'click',

                authenticate

            );



            authenticate();

        })();

    </script>

</body>

</html>
