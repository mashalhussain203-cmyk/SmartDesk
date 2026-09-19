@extends('layouts.site-layout')

@section('title', 'Mashal | E-mailcode verifiëren')

@push('styles')
<style>
    .email-verify-page {
        min-height: calc(100vh - 78px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        background:
            radial-gradient(
                circle at 80% 15%,
                rgba(215,164,95,.08),
                transparent 18rem
            ),
            linear-gradient(
                180deg,
                #0b0d10,
                #08090b
            );
    }

    .email-verify-card {
        width: 100%;
        max-width: 520px;
        padding: 34px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 24px;
        background:
            linear-gradient(
                145deg,
                rgba(255,255,255,.045),
                rgba(255,255,255,.018)
            );
        box-shadow:
            0 30px 80px rgba(0,0,0,.35),
            inset 0 1px 0 rgba(255,255,255,.04);
    }

    .email-verify-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 28px;
    }

    .email-verify-logo {
        width: 46px;
        height: 46px;
        display: grid;
        place-items: center;
        border-radius: 14px;
        background:
            linear-gradient(
                145deg,
                #f0ca86,
                #b67e3d
            );
        color: #15110c;
        font-size: 20px;
        font-weight: 950;
        box-shadow: 0 14px 34px rgba(215,164,95,.22);
    }

    .email-verify-brand strong {
        display: block;
        color: #ffffff;
        font-size: 18px;
    }

    .email-verify-brand span {
        display: block;
        margin-top: 4px;
        color: #6f757c;
        font-size: 8px;
        font-weight: 850;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .email-verify-kicker {
        color: #b9894d;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .email-verify-title {
        margin: 10px 0 0;
        color: #ffffff;
        font-size: 38px;
        line-height: 1;
        font-weight: 950;
        letter-spacing: -.04em;
    }

    .email-verify-subtitle {
        margin: 14px 0 28px;
        color: #7f858c;
        font-size: 13px;
        line-height: 1.8;
    }

    .email-message {
        margin-bottom: 18px;
        padding: 13px 15px;
        border-radius: 14px;
        font-size: 12px;
        line-height: 1.6;
    }

    .email-message.success {
        border: 1px solid rgba(91,214,149,.22);
        background: rgba(91,214,149,.08);
        color: #a9efc8;
    }

    .email-message.error {
        border: 1px solid rgba(241,123,123,.22);
        background: rgba(241,123,123,.08);
        color: #ffc1c1;
    }

    .email-field {
        margin-bottom: 18px;
    }

    .email-label {
        display: block;
        margin-bottom: 8px;
        color: #b9b9b6;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .email-input {
        width: 100%;
        height: 56px;
        padding: 0 16px;
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 15px;
        outline: none;
        background: rgba(255,255,255,.035);
        color: #ffffff;
        font-size: 14px;
        box-sizing: border-box;
        transition:
            border-color .2s ease,
            background .2s ease,
            box-shadow .2s ease;
    }

    .email-input:focus {
        border-color: rgba(215,164,95,.46);
        background: rgba(215,164,95,.035);
        box-shadow: 0 0 0 4px rgba(215,164,95,.065);
    }

    .email-code-input {
        text-align: center;
        font-size: 28px;
        font-weight: 900;
        letter-spacing: 10px;
        color: #f0c983;
    }

    .email-submit {
        width: 100%;
        min-height: 56px;
        border: 0;
        border-radius: 999px;
        background:
            linear-gradient(
                135deg,
                #f1cc8b,
                #ca914c
            );
        color: #14100b;
        font-size: 12px;
        font-weight: 950;
        cursor: pointer;
        box-shadow: 0 18px 44px rgba(215,164,95,.20);
        transition:
            transform .2s ease,
            opacity .2s ease,
            box-shadow .2s ease;
    }

    .email-submit:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 22px 50px rgba(215,164,95,.26);
    }

    .email-submit:disabled {
        cursor: wait;
        opacity: .72;
        transform: none;
    }

    .email-security-status {
        min-height: 18px;
        margin: 12px 0 0;
        color: #747a81;
        font-size: 9px;
        line-height: 1.6;
        text-align: center;
    }

    .email-security-status.is-active {
        color: #caa46d;
    }

    .email-back {
        margin-top: 18px;
        text-align: center;
    }

    .email-back a {
        color: #caa46d;
        text-decoration: none;
        font-size: 11px;
        font-weight: 800;
        transition: color .2s ease;
    }

    .email-back a:hover {
        color: #f0c983;
    }

    .email-note {
        margin-top: 18px;
        color: #5f656c;
        font-size: 9px;
        line-height: 1.7;
        text-align: center;
    }

    .email-security-note {
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid rgba(255,255,255,.055);
        color: #555b61;
        font-size: 9px;
        line-height: 1.7;
        text-align: center;
    }

    .email-security-note strong {
        color: #8e949b;
        font-weight: 850;
    }

    @media (max-width: 560px) {
        .email-verify-page {
            padding: 24px 14px;
        }

        .email-verify-card {
            padding: 26px 20px;
            border-radius: 20px;
        }

        .email-verify-title {
            font-size: 34px;
        }

        .email-code-input {
            font-size: 24px;
            letter-spacing: 8px;
        }
    }
</style>
@endpush


@section('content')
<section class="email-verify-page">
    <div class="email-verify-card">

        <div class="email-verify-brand">
            <div class="email-verify-logo">
                M
            </div>

            <div>
                <strong>Mashal</strong>
                <span>Automotive</span>
            </div>
        </div>


        <div class="email-verify-kicker">
            Secure access
        </div>


        <h1 class="email-verify-title">
            Vul je code in
        </h1>


        <p class="email-verify-subtitle">
            We hebben een 6-cijferige code naar je e-mailadres gestuurd.
            Vul de code hieronder in om verder te gaan.
        </p>


        {{-- SUCCESS --}}
        @if (session('success'))
            <div class="email-message success">
                {{ session('success') }}
            </div>
        @endif


        {{-- SESSION ERROR --}}
        @if (session('error'))
            <div class="email-message error">
                {{ session('error') }}
            </div>
        @endif


        {{-- VALIDATION ERRORS --}}
        @if ($errors->any())
            <div class="email-message error">
                @foreach ($errors->all() as $error)
                    <div>
                        {{ $error }}
                    </div>
                @endforeach
            </div>
        @endif


        {{-- VERIFY FORM --}}
        <form
            id="emailCodeVerifyForm"
            method="POST"
            action="{{ route('email-login.verify') }}"
            data-login-security-form
        >
            @csrf

            <div class="email-field">
                <label
                    class="email-label"
                    for="email"
                >
                    E-mailadres
                </label>

                <input
                    class="email-input"
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email', $email ?? session('email_login_email')) }}"
                    placeholder="naam@example.com"
                    autocomplete="email"
                    required
                >
            </div>


            <div class="email-field">
                <label
                    class="email-label"
                    for="code"
                >
                    6-cijferige code
                </label>

                <input
                    class="email-input email-code-input"
                    id="code"
                    type="text"
                    name="code"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    maxlength="6"
                    minlength="6"
                    placeholder="000000"
                    autocomplete="one-time-code"
                    required
                    autofocus
                >
            </div>


            <button
                id="emailCodeSubmitButton"
                class="email-submit"
                type="submit"
            >
                Code controleren
            </button>


            <div
                id="emailSecurityStatus"
                class="email-security-status"
                aria-live="polite"
            ></div>
        </form>


        <div class="email-back">
            <a href="{{ route('login') }}">
                ← Terug naar inloggen
            </a>
        </div>


        <div class="email-note">
            De code is {{ $expiresInMinutes ?? 5 }} minuten geldig.
            Deel je code nooit met iemand anders.
        </div>


        <div class="email-security-note">
            <strong>Loginbeveiliging:</strong>
            bij een succesvolle login registreren we onder andere
            apparaat, browser, IP-adres en timezone.
            Een precieze locatie wordt alleen opgeslagen wanneer je browser
            daar toestemming voor geeft.
        </div>

    </div>
</section>


<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const form = document.getElementById('emailCodeVerifyForm');
    const submitButton = document.getElementById('emailCodeSubmitButton');
    const statusElement = document.getElementById('emailSecurityStatus');

    if (!form) {
        return;
    }

    let securityContextHandled = false;

    const securityContextUrl = @json(route('login-security.context'));
    const csrfToken = @json(csrf_token());

    const geolocationOptions = {
        enableHighAccuracy: @json(
            (bool) config(
                'login-security.precise_location.high_accuracy',
                true
            )
        ),

        timeout: {{ max(
            1000,
            (int) config(
                'login-security.precise_location.timeout_ms',
                10000
            )
        ) }},

        maximumAge: {{ max(
            0,
            (int) config(
                'login-security.precise_location.maximum_age_ms',
                60000
            )
        ) }}
    };


    /*
    |--------------------------------------------------------------------------
    | Status weergeven
    |--------------------------------------------------------------------------
    */

    function setStatus(message, active = false) {
        if (!statusElement) {
            return;
        }

        statusElement.textContent = message || '';
        statusElement.classList.toggle(
            'is-active',
            Boolean(active)
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Submitknop blokkeren tijdens beveiligingscontrole
    |--------------------------------------------------------------------------
    */

    function setSubmitting(submitting) {
        if (!submitButton) {
            return;
        }

        submitButton.disabled = Boolean(submitting);

        submitButton.textContent = submitting
            ? 'Beveiliging controleren...'
            : 'Code controleren';
    }


    /*
    |--------------------------------------------------------------------------
    | Browser timezone
    |--------------------------------------------------------------------------
    */

    function getBrowserTimezone() {
        try {
            return Intl.DateTimeFormat()
                .resolvedOptions()
                .timeZone || null;
        } catch (error) {
            return null;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Browsercontext naar Laravel sturen
    |--------------------------------------------------------------------------
    */

    async function storeSecurityContext(payload) {
        try {
            const response = await fetch(
                securityContextUrl,
                {
                    method: 'POST',

                    credentials: 'same-origin',

                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },

                    body: JSON.stringify(payload)
                }
            );

            return response.ok;
        } catch (error) {
            /*
            |--------------------------------------------------------------------------
            | Security-context mag login nooit blokkeren
            |--------------------------------------------------------------------------
            */

            return false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Locatietoestemming bepalen
    |--------------------------------------------------------------------------
    */

    async function getPermissionState() {
        if (
            !navigator.permissions ||
            typeof navigator.permissions.query !== 'function'
        ) {
            return null;
        }

        try {
            const result = await navigator.permissions.query({
                name: 'geolocation'
            });

            return result.state || null;
        } catch (error) {
            return null;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | GPS-locatie ophalen
    |--------------------------------------------------------------------------
    */

    function getCurrentLocation() {
        return new Promise(function (resolve) {
            /*
            |--------------------------------------------------------------------------
            | Browser ondersteunt geen geolocation
            |--------------------------------------------------------------------------
            */

            if (
                !navigator.geolocation ||
                typeof navigator.geolocation.getCurrentPosition !== 'function'
            ) {
                resolve({
                    permission: 'unsupported',
                    latitude: null,
                    longitude: null,
                    accuracy: null
                });

                return;
            }


            navigator.geolocation.getCurrentPosition(
                function (position) {
                    resolve({
                        permission: 'granted',

                        latitude:
                            Number.isFinite(position.coords.latitude)
                                ? position.coords.latitude
                                : null,

                        longitude:
                            Number.isFinite(position.coords.longitude)
                                ? position.coords.longitude
                                : null,

                        accuracy:
                            Number.isFinite(position.coords.accuracy)
                                ? position.coords.accuracy
                                : null
                    });
                },

                function (error) {
                    let permission = 'unavailable';

                    if (
                        error &&
                        error.code === error.PERMISSION_DENIED
                    ) {
                        permission = 'denied';
                    }

                    resolve({
                        permission: permission,
                        latitude: null,
                        longitude: null,
                        accuracy: null
                    });
                },

                geolocationOptions
            );
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Volledige security-context verzamelen
    |--------------------------------------------------------------------------
    */

    async function captureLoginSecurityContext() {
        const payload = {
            browser_timezone: getBrowserTimezone(),

            latitude: null,

            longitude: null,

            location_accuracy: null,

            location_permission: 'unknown'
        };


        /*
        |--------------------------------------------------------------------------
        | Geen geolocation beschikbaar
        |--------------------------------------------------------------------------
        */

        if (
            !navigator.geolocation ||
            typeof navigator.geolocation.getCurrentPosition !== 'function'
        ) {
            payload.location_permission = 'unsupported';

            await storeSecurityContext(payload);

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Bestaande permission-status proberen te lezen
        |--------------------------------------------------------------------------
        */

        const permissionState = await getPermissionState();

        if (permissionState === 'denied') {
            payload.location_permission = 'denied';

            await storeSecurityContext(payload);

            return;
        }


        if (permissionState === 'prompt') {
            payload.location_permission = 'prompt';
        }


        /*
        |--------------------------------------------------------------------------
        | Browserlocatie aanvragen
        |--------------------------------------------------------------------------
        |
        | Als toestemming nog niet is gegeven, toont de browser zelf
        | de officiële toestemmingsvraag.
        |
        */

        const location = await getCurrentLocation();

        payload.location_permission =
            location.permission || 'unknown';

        payload.latitude =
            location.latitude;

        payload.longitude =
            location.longitude;

        payload.location_accuracy =
            location.accuracy;


        /*
        |--------------------------------------------------------------------------
        | Context opslaan
        |--------------------------------------------------------------------------
        */

        await storeSecurityContext(
            payload
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Form submit
    |--------------------------------------------------------------------------
    */

    form.addEventListener(
        'submit',
        async function (event) {
            /*
            |--------------------------------------------------------------------------
            | Context is al verwerkt
            |--------------------------------------------------------------------------
            */

            if (securityContextHandled) {
                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Normale submit tijdelijk stoppen
            |--------------------------------------------------------------------------
            */

            event.preventDefault();

            setSubmitting(true);

            setStatus(
                'Loginbeveiliging wordt voorbereid...',
                true
            );


            /*
            |--------------------------------------------------------------------------
            | Security-context verzamelen
            |--------------------------------------------------------------------------
            |
            | Een fout hierin mag het inloggen nooit blokkeren.
            |
            */

            try {
                await captureLoginSecurityContext();
            } catch (error) {
                //
            }


            /*
            |--------------------------------------------------------------------------
            | Login alsnog uitvoeren
            |--------------------------------------------------------------------------
            */

            securityContextHandled = true;

            setStatus(
                'Code wordt gecontroleerd...',
                true
            );

            /*
            |--------------------------------------------------------------------------
            | Native submit
            |--------------------------------------------------------------------------
            |
            | Hiermee voorkomen we dat dezelfde submit-listener opnieuw
            | wordt uitgevoerd.
            |
            */

            HTMLFormElement.prototype.submit.call(
                form
            );
        }
    );
});
</script>
@endsection