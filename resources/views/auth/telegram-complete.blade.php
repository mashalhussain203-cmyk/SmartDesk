<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <meta name="csrf-token" content="{{ csrf_token() }}">



    <title>Telegram login - {{ config('app.name', 'Mashal Studio') }}</title>



    <script src="https://telegram.org/js/telegram-web-app.js"></script>



    <style>

        :root {

            color-scheme: light dark;

            --bg: #0b1118;

            --card: rgba(20, 29, 39, .92);

            --line: rgba(255, 255, 255, .10);

            --text: #f8fafc;

            --muted: #94a3b8;

            --blue: #229ed9;

            --blue-2: #57c7f4;

            --green: #65d59a;

            --danger: #ff9c9c;

        }



        * {

            box-sizing: border-box;

        }



        html,

        body {

            margin: 0;

            min-height: 100%;

            background: var(--bg);

            color: var(--text);

            font-family:

                Inter,

                ui-sans-serif,

                system-ui,

                -apple-system,

                BlinkMacSystemFont,

                "Segoe UI",

                sans-serif;

        }



        body {

            padding:

                max(22px, env(safe-area-inset-top))

                18px

                max(26px, env(safe-area-inset-bottom));

            background:

                radial-gradient(circle at 50% 0%, rgba(34, 158, 217, .24), transparent 22rem),

                linear-gradient(180deg, #101923, #080d13);

        }



        .wrap {

            width: min(100%, 460px);

            margin: 0 auto;

        }



        .card {

            padding: 28px 22px;

            border: 1px solid var(--line);

            border-radius: 24px;

            background: var(--card);

            box-shadow: 0 24px 70px rgba(0, 0, 0, .25);

            text-align: center;

        }



        .logo {

            width: 92px;

            height: 92px;

            margin: 0 auto 20px;

            display: block;

            object-fit: contain;

            filter: drop-shadow(0 15px 28px rgba(34, 158, 217, .28));

        }



        .logo-fallback {

            width: 82px;

            height: 82px;

            margin: 0 auto 20px;

            display: none;

            place-items: center;

            border-radius: 50%;

            background: linear-gradient(145deg, var(--blue-2), var(--blue));

            box-shadow: 0 15px 35px rgba(34, 158, 217, .26);

        }



        .logo-fallback svg {

            width: 46px;

            height: 46px;

            fill: #fff;

        }



        h1 {

            margin: 0;

            font-size: clamp(1.55rem, 7vw, 2rem);

            letter-spacing: -.035em;

        }



        .copy {

            max-width: 360px;

            margin: 12px auto 0;

            color: var(--muted);

            font-size: .95rem;

            line-height: 1.65;

        }



        .status {

            margin-top: 24px;

            padding: 15px 16px;

            border: 1px solid var(--line);

            border-radius: 15px;

            background: rgba(255, 255, 255, .035);

            color: var(--muted);

            font-size: .9rem;

            line-height: 1.55;

        }



        .status.success {

            border-color: rgba(101, 213, 154, .25);

            color: #a8edc4;

            background: rgba(101, 213, 154, .06);

        }



        .status.error {

            border-color: rgba(255, 156, 156, .24);

            color: var(--danger);

            background: rgba(255, 90, 90, .06);

        }



        .spinner {

            width: 22px;

            height: 22px;

            margin: 0 auto 11px;

            border: 2px solid rgba(255, 255, 255, .15);

            border-top-color: var(--blue-2);

            border-radius: 50%;

            animation: spin .8s linear infinite;

        }



        .continue {

            width: 100%;

            min-height: 52px;

            margin-top: 16px;

            display: none;

            align-items: center;

            justify-content: center;

            gap: 10px;

            border: 0;

            border-radius: 15px;

            color: #fff;

            background: linear-gradient(180deg, #32afe8, #168dca);

            box-shadow: 0 12px 30px rgba(34, 158, 217, .25);

            font: inherit;

            font-weight: 800;

            cursor: pointer;

        }



        .continue.is-visible {

            display: inline-flex;

        }



        .continue svg {

            width: 22px;

            height: 22px;

            fill: currentColor;

        }



        .fine {

            margin: 18px auto 0;

            color: #6f7c8b;

            font-size: .75rem;

            line-height: 1.55;

        }



        @keyframes spin {

            to {

                transform: rotate(360deg);

            }

        }



        @media (prefers-reduced-motion: reduce) {

            .spinner {

                animation: none;

            }

        }

    </style>

</head>

<body>

    <main class="wrap">

        <section class="card">

            <img

                class="logo"

                src="{{ asset('images/social/telegram-logo.png') }}"

                alt="Telegram"

                onerror="this.style.display='none';document.getElementById('telegramLogoFallback').style.display='grid';"

            >



            <div

                id="telegramLogoFallback"

                class="logo-fallback"

                aria-hidden="true"

            >

                <svg viewBox="0 0 24 24">

                    <path d="M21.944 2.506c.281-.107.574.046.477.416l-3.566 16.827c-.084.398-.319.495-.647.309l-5.433-4.005-2.621 2.522c-.29.29-.533.532-1.093.532l.39-5.536L19.53 4.466c.438-.39-.095-.607-.68-.217L6.394 12.09l-5.363-1.676c-.585-.183-.596-.585.122-.866L21.944 2.506Z"/>

                </svg>

            </div>



            <h1>{{ __('Veilig inloggen met Telegram') }}</h1>



            <p class="copy">

                {{ __('We controleren je Telegram-account veilig en maken daarna een korte eenmalige link om terug te gaan naar Mashal Studio.') }}

            </p>



            <div

                id="telegramStatus"

                class="status"

                role="status"

                aria-live="polite"

            >

                <div class="spinner" aria-hidden="true"></div>

                {{ __('Telegram-account controleren…') }}

            </div>



            <button

                id="telegramContinue"

                class="continue"

                type="button"

            >

                <svg viewBox="0 0 24 24" aria-hidden="true">

                    <path d="M21.944 2.506c.281-.107.574.046.477.416l-3.566 16.827c-.084.398-.319.495-.647.309l-5.433-4.005-2.621 2.522c-.29.29-.533.532-1.093.532l.39-5.536L19.53 4.466c.438-.39-.095-.607-.68-.217L6.394 12.09l-5.363-1.676c-.585-.183-.596-.585.122-.866L21.944 2.506Z"/>

                </svg>



                {{ __('Doorgaan naar Mashal Studio') }}

            </button>



            <p class="fine">

                {{ __('Deze pagina gebruikt alleen de door Telegram ondertekende Mini App-logininformatie. Je bot-token blijft uitsluitend op de server.') }}

            </p>

        </section>

    </main>



    <script>

        (() => {

            const status = document.getElementById('telegramStatus');

            const continueButton = document.getElementById('telegramContinue');

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

            const telegram = window.Telegram?.WebApp ?? null;



            let handoffUrl = '';



            const setError = (message) => {

                status.className = 'status error';

                status.textContent = message;

                continueButton.classList.remove('is-visible');

            };



            const setSuccess = (message) => {

                status.className = 'status success';

                status.textContent = message;

                continueButton.classList.add('is-visible');

            };



            const authenticate = async () => {

                if (!telegram || !telegram.initData) {

                    setError(

                        'Open deze pagina vanuit de Telegram-app via de bot om veilig in te loggen.'

                    );

                    return;

                }



                telegram.ready();



                if (typeof telegram.expand === 'function') {

                    telegram.expand();

                }



                try {

                    const response = await fetch(

                        @json(route('auth.telegram.mini-app.auth')),

                        {

                            method: 'POST',

                            credentials: 'same-origin',

                            headers: {

                                'Accept': 'application/json',

                                'Content-Type': 'application/json',

                                'X-CSRF-TOKEN': csrfToken,

                                'X-Requested-With': 'XMLHttpRequest',

                            },

                            body: JSON.stringify({

                                init_data: telegram.initData,

                            }),

                        }

                    );



                    const data = await response.json();



                    if (!response.ok || !data.ok || !data.handoff_url) {

                        throw new Error(

                            data.message || 'Telegram-login kon niet worden bevestigd.'

                        );

                    }



                    handoffUrl = data.handoff_url;



                    setSuccess(

                        data.new_user

                            ? 'Telegram is bevestigd. Tik hieronder om je registratie op Mashal Studio af te ronden.'

                            : 'Telegram is bevestigd. Tik hieronder om veilig terug te gaan naar Mashal Studio.'

                    );

                } catch (error) {

                    setError(

                        error instanceof Error

                            ? error.message

                            : 'Telegram-login kon niet worden bevestigd.'

                    );

                }

            };



            continueButton.addEventListener('click', () => {

                if (!handoffUrl) {

                    return;

                }



                if (telegram && typeof telegram.openLink === 'function') {

                    telegram.openLink(handoffUrl);

                    return;

                }



                window.location.href = handoffUrl;

            });



            authenticate();

        })();

    </script>

</body>

</html>
