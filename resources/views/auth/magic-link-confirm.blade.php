@extends('layouts.site-layout')

@section('title', 'Mashal | Veilige login bevestigen')

@push('styles')
<style>
    .magic-confirm-page {
        position: relative;
        min-height: calc(100vh - 78px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 44px 20px;
        overflow: hidden;
        background:
            radial-gradient(
                circle at 14% 10%,
                rgba(215,164,95,.10),
                transparent 23rem
            ),
            radial-gradient(
                circle at 88% 20%,
                rgba(255,255,255,.025),
                transparent 26rem
            ),
            linear-gradient(
                180deg,
                #0b0d10,
                #08090b
            );
    }

    .magic-confirm-page::before {
        content: "M";
        position: absolute;
        right: -30px;
        top: 10%;
        color: rgba(255,255,255,.015);
        font-size: 320px;
        line-height: .8;
        font-weight: 950;
        pointer-events: none;
        user-select: none;
    }

    .magic-confirm-card {
        position: relative;
        z-index: 2;
        width: 100%;
        max-width: 560px;
        padding: 34px;
        border: 1px solid rgba(255,255,255,.085);
        border-radius: 26px;
        background:
            linear-gradient(
                145deg,
                rgba(255,255,255,.045),
                rgba(255,255,255,.016)
            );
        box-shadow:
            0 34px 90px rgba(0,0,0,.38),
            inset 0 1px 0 rgba(255,255,255,.045);
    }

    .magic-confirm-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 30px;
    }

    .magic-confirm-logo {
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

    .magic-confirm-brand-copy strong {
        display: block;
        color: #ffffff;
        font-size: 18px;
        line-height: 1;
    }

    .magic-confirm-brand-copy span {
        display: block;
        margin-top: 5px;
        color: #6f757c;
        font-size: 8px;
        font-weight: 850;
        letter-spacing: .17em;
        text-transform: uppercase;
    }

    .magic-confirm-kicker {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        color: #b9894d;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .magic-confirm-kicker::before {
        content: "";
        width: 24px;
        height: 1px;
        background: #b9894d;
    }

    .magic-confirm-title {
        margin: 12px 0 0;
        color: #ffffff;
        font-size: clamp(36px, 7vw, 50px);
        line-height: 1;
        letter-spacing: -.05em;
        font-weight: 950;
    }

    .magic-confirm-subtitle {
        margin: 16px 0 0;
        color: #858b92;
        font-size: 13px;
        line-height: 1.8;
    }

    .magic-confirm-email {
        margin-top: 24px;
        padding: 15px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        border: 1px solid rgba(215,164,95,.15);
        border-radius: 16px;
        background: rgba(215,164,95,.045);
    }

    .magic-confirm-email-copy small {
        display: block;
        color: #72777e;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .magic-confirm-email-copy strong {
        display: block;
        margin-top: 5px;
        color: #efc985;
        font-size: 13px;
        line-height: 1.4;
        word-break: break-word;
    }

    .magic-confirm-email-mark {
        flex: 0 0 auto;
        width: 34px;
        height: 34px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: rgba(215,164,95,.09);
        color: #efc985;
        font-size: 14px;
        font-weight: 900;
    }

    .magic-confirm-info {
        margin-top: 18px;
        padding: 16px;
        border: 1px solid rgba(255,255,255,.065);
        border-radius: 16px;
        background: rgba(255,255,255,.02);
    }

    .magic-confirm-info strong {
        display: block;
        color: #d7d8d5;
        font-size: 11px;
    }

    .magic-confirm-info p {
        margin: 6px 0 0;
        color: #6f757c;
        font-size: 10px;
        line-height: 1.7;
    }

    .magic-confirm-status {
        min-height: 20px;
        margin: 14px 0 0;
        color: #6f757c;
        font-size: 10px;
        line-height: 1.6;
        text-align: center;
    }

    .magic-confirm-status.is-active {
        color: #caa46d;
    }

    .magic-confirm-status.is-success {
        color: #9fe0ba;
    }

    .magic-confirm-status.is-warning {
        color: #e8bd78;
    }

    .magic-confirm-submit {
        position: relative;
        width: 100%;
        min-height: 58px;
        margin-top: 20px;
        padding: 0 54px;
        overflow: hidden;
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
        letter-spacing: .035em;
        cursor: pointer;
        box-shadow: 0 18px 44px rgba(215,164,95,.20);
        transition:
            transform .2s ease,
            opacity .2s ease,
            box-shadow .2s ease;
    }

    .magic-confirm-submit::after {
        content: "→";
        position: absolute;
        right: 22px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 17px;
    }

    .magic-confirm-submit:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 24px 54px rgba(215,164,95,.28);
    }

    .magic-confirm-submit:disabled {
        cursor: wait;
        opacity: .70;
        transform: none;
    }

    .magic-confirm-back {
        margin-top: 18px;
        text-align: center;
    }

    .magic-confirm-back a {
        color: #caa46d;
        text-decoration: none;
        font-size: 11px;
        font-weight: 800;
        transition: color .2s ease;
    }

    .magic-confirm-back a:hover {
        color: #f0c983;
    }

    .magic-confirm-note {
        margin-top: 18px;
        padding-top: 16px;
        border-top: 1px solid rgba(255,255,255,.055);
        color: #555b61;
        font-size: 9px;
        line-height: 1.75;
        text-align: center;
    }

    .magic-confirm-note strong {
        color: #8e949b;
        font-weight: 850;
    }

    .magic-confirm-error {
        margin-bottom: 18px;
        padding: 13px 15px;
        border: 1px solid rgba(241,123,123,.22);
        border-radius: 14px;
        background: rgba(241,123,123,.08);
        color: #ffc1c1;
        font-size: 12px;
        line-height: 1.6;
    }

    @media (max-width: 560px) {
        .magic-confirm-page {
            padding: 26px 14px;
        }

        .magic-confirm-card {
            padding: 26px 20px;
            border-radius: 21px;
        }

        .magic-confirm-email {
            align-items: flex-start;
        }

        .magic-confirm-page::before {
            font-size: 230px;
        }
    }
</style>
@endpush


@section('content')
<section class="magic-confirm-page">
    <div class="magic-confirm-card">

        <div class="magic-confirm-brand">
            <div
                class="magic-confirm-logo"
                aria-hidden="true"
            >
                M
            </div>

            <div class="magic-confirm-brand-copy">
                <strong>
                    Mashal
                </strong>

                <span>
                    Automotive
                </span>
            </div>
        </div>


        @if (session('error'))
            <div class="magic-confirm-error">
                {{ session('error') }}
            </div>
        @endif


        @if ($errors->any())
            <div class="magic-confirm-error">
                @foreach ($errors->all() as $error)
                    <div>
                        {{ $error }}
                    </div>
                @endforeach
            </div>
        @endif


        <span class="magic-confirm-kicker">
            Secure magic link
        </span>


        <h1 class="magic-confirm-title">
            Bevestig je login
        </h1>


        <p class="magic-confirm-subtitle">
            Je veilige loginlink is gecontroleerd. We leggen nu de
            beveiligingsgegevens vast van het apparaat waarop je deze link
            daadwerkelijk hebt geopend. Daarna wordt je login afgerond.
        </p>


        <div class="magic-confirm-email">
            <div class="magic-confirm-email-copy">
                <small>
                    Inloggen als
                </small>

                <strong>
                    {{ $maskedEmail ?? $email ?? 'Onbekend e-mailadres' }}
                </strong>
            </div>

            <div
                class="magic-confirm-email-mark"
                aria-hidden="true"
            >
                ✓
            </div>
        </div>


        <div class="magic-confirm-info">
            <strong>
                Wat wordt gecontroleerd?
            </strong>

            <p>
                Browser-timezone, apparaat, browser, besturingssysteem,
                IP-adres en geschatte IP-locatie worden gebruikt voor je
                loginhistorie. Een precieze browserlocatie wordt alleen
                opgeslagen wanneer je daarvoor toestemming geeft.
            </p>
        </div>


        <form
            id="magicLinkCompleteForm"
            method="POST"
            action="{{ route('email-login.link.complete') }}"
        >
            @csrf

            <input
                type="hidden"
                name="login_provider"
                value="magic_link"
            >

            <button
                id="magicLinkCompleteButton"
                class="magic-confirm-submit"
                type="submit"
            >
                Veilig inloggen
            </button>

            <div
                id="magicLinkSecurityStatus"
                class="magic-confirm-status"
                aria-live="polite"
            ></div>
        </form>


        <div class="magic-confirm-back">
            <a href="{{ route('login') }}">
                ← Terug naar inloggen
            </a>
        </div>


        <div class="magic-confirm-note">
            De loginlink is maximaal
            <strong>{{ $expiresInMinutes ?? 10 }} minuten</strong>
            geldig en kan maar één keer worden gebruikt.
            Als je browser locatie weigert of niet ondersteunt, gaat de
            login gewoon verder met de overige beveiligingsgegevens.
        </div>

    </div>
</section>


<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const form =
        document.getElementById(
            'magicLinkCompleteForm'
        );

    const submitButton =
        document.getElementById(
            'magicLinkCompleteButton'
        );

    const statusElement =
        document.getElementById(
            'magicLinkSecurityStatus'
        );

    if (!form) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Instellingen
    |--------------------------------------------------------------------------
    */

    const securityContextUrl =
        @json(
            route(
                'login-security.context'
            )
        );

    const csrfToken =
        @json(
            csrf_token()
        );

    const preciseLocationEnabled =
        @json(
            (bool) config(
                'login-security.precise_location.enabled',
                true
            )
        );

    const geolocationOptions = {
        enableHighAccuracy:
            @json(
                (bool) config(
                    'login-security.precise_location.high_accuracy',
                    true
                )
            ),

        timeout:
            {{ max(
                1000,
                (int) config(
                    'login-security.precise_location.timeout_ms',
                    10000
                )
            ) }},

        maximumAge:
            {{ max(
                0,
                (int) config(
                    'login-security.precise_location.max_age_ms',
                    (int) config(
                        'login-security.precise_location.maximum_age_ms',
                        60000
                    )
                )
            ) }}
    };

    let securityContextHandled = false;


    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    function setStatus(
        message,
        state = 'active'
    ) {
        if (!statusElement) {
            return;
        }

        statusElement.textContent =
            message || '';

        statusElement.classList.remove(
            'is-active',
            'is-success',
            'is-warning'
        );

        if (!message) {
            return;
        }

        if (state === 'success') {
            statusElement.classList.add(
                'is-success'
            );

            return;
        }

        if (state === 'warning') {
            statusElement.classList.add(
                'is-warning'
            );

            return;
        }

        statusElement.classList.add(
            'is-active'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Submitknop
    |--------------------------------------------------------------------------
    */

    function setSubmitting(
        submitting
    ) {
        if (!submitButton) {
            return;
        }

        submitButton.disabled =
            Boolean(
                submitting
            );

        submitButton.textContent =
            submitting
                ? 'Beveiliging controleren...'
                : 'Veilig inloggen';
    }


    /*
    |--------------------------------------------------------------------------
    | Browser-timezone
    |--------------------------------------------------------------------------
    */

    function getBrowserTimezone() {
        try {
            return Intl
                .DateTimeFormat()
                .resolvedOptions()
                .timeZone || null;
        } catch (error) {
            return null;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Security-context naar Laravel sturen
    |--------------------------------------------------------------------------
    */

    async function storeSecurityContext(
        payload
    ) {
        try {
            const response =
                await fetch(
                    securityContextUrl,
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
                                'XMLHttpRequest'
                        },

                        body:
                            JSON.stringify(
                                payload
                            )
                    }
                );

            return response.ok;
        } catch (error) {
            return false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Huidige browser-permission proberen te lezen
    |--------------------------------------------------------------------------
    */

    async function getPermissionState() {
        if (
            ! navigator.permissions ||
            typeof navigator.permissions.query !== 'function'
        ) {
            return null;
        }

        try {
            const permission =
                await navigator.permissions.query({
                    name: 'geolocation'
                });

            return permission.state || null;
        } catch (error) {
            return null;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | GPS ophalen
    |--------------------------------------------------------------------------
    */

    function getCurrentLocation() {
        return new Promise(function (resolve) {
            if (
                ! navigator.geolocation ||
                typeof navigator.geolocation.getCurrentPosition !== 'function'
            ) {
                resolve({
                    permission:
                        'unsupported',

                    latitude:
                        null,

                    longitude:
                        null,

                    accuracy:
                        null
                });

                return;
            }

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    resolve({
                        permission:
                            'granted',

                        latitude:
                            Number.isFinite(
                                position.coords.latitude
                            )
                                ? position.coords.latitude
                                : null,

                        longitude:
                            Number.isFinite(
                                position.coords.longitude
                            )
                                ? position.coords.longitude
                                : null,

                        accuracy:
                            Number.isFinite(
                                position.coords.accuracy
                            )
                                ? position.coords.accuracy
                                : null
                    });
                },

                function (error) {
                    let permission =
                        'unavailable';

                    if (
                        error &&
                        error.code === error.PERMISSION_DENIED
                    ) {
                        permission =
                            'denied';
                    }

                    resolve({
                        permission:
                            permission,

                        latitude:
                            null,

                        longitude:
                            null,

                        accuracy:
                            null
                    });
                },

                geolocationOptions
            );
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Volledige browser-securitycontext verzamelen
    |--------------------------------------------------------------------------
    */

    async function captureSecurityContext() {
        const payload = {
            browser_timezone:
                getBrowserTimezone(),

            latitude:
                null,

            longitude:
                null,

            location_accuracy:
                null,

            location_permission:
                'unknown'
        };


        /*
        |--------------------------------------------------------------------------
        | GPS-functionaliteit uitgeschakeld
        |--------------------------------------------------------------------------
        */

        if (!preciseLocationEnabled) {
            payload.location_permission =
                'unavailable';

            return await storeSecurityContext(
                payload
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Browser ondersteunt geen GPS
        |--------------------------------------------------------------------------
        */

        if (
            ! navigator.geolocation ||
            typeof navigator.geolocation.getCurrentPosition !== 'function'
        ) {
            payload.location_permission =
                'unsupported';

            return await storeSecurityContext(
                payload
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Bestaande toestemming bekijken
        |--------------------------------------------------------------------------
        */

        const permissionState =
            await getPermissionState();

        if (
            permissionState === 'denied'
        ) {
            payload.location_permission =
                'denied';

            return await storeSecurityContext(
                payload
            );
        }

        if (
            permissionState === 'prompt'
        ) {
            payload.location_permission =
                'prompt';
        }


        /*
        |--------------------------------------------------------------------------
        | Browserlocatie aanvragen
        |--------------------------------------------------------------------------
        |
        | De browser bepaalt zelf of een toestemmingsvenster nodig is.
        |
        */

        setStatus(
            'Browserbeveiliging en locatie worden gecontroleerd...',
            'active'
        );

        const location =
            await getCurrentLocation();

        payload.location_permission =
            location.permission || 'unknown';

        payload.latitude =
            location.latitude;

        payload.longitude =
            location.longitude;

        payload.location_accuracy =
            location.accuracy;

        return await storeSecurityContext(
            payload
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Definitieve magic-link login
    |--------------------------------------------------------------------------
    */

    form.addEventListener(
        'submit',
        async function (event) {
            if (securityContextHandled) {
                return;
            }

            event.preventDefault();

            setSubmitting(
                true
            );

            setStatus(
                'Loginbeveiliging wordt voorbereid...',
                'active'
            );

            let stored = false;

            try {
                stored =
                    await captureSecurityContext();
            } catch (error) {
                stored =
                    false;
            }


            /*
            |--------------------------------------------------------------------------
            | Security-context mag de login niet blokkeren
            |--------------------------------------------------------------------------
            */

            if (stored) {
                setStatus(
                    'Beveiligingsgegevens opgeslagen. Login wordt afgerond...',
                    'success'
                );
            } else {
                setStatus(
                    'Niet alle beveiligingsgegevens konden worden opgeslagen. De login gaat verder.',
                    'warning'
                );
            }

            securityContextHandled =
                true;

            /*
            |--------------------------------------------------------------------------
            | Native submit
            |--------------------------------------------------------------------------
            |
            | Hierdoor loopt deze event-listener niet opnieuw.
            |
            */

            HTMLFormElement
                .prototype
                .submit
                .call(
                    form
                );
        }
    );
});
</script>
@endsection
