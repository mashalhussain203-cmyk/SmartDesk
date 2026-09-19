@extends('layouts.site-layout')

@section('title', 'Mashal | Registreren')

@push('styles')
<style>
    .register-page {
        position: relative;
        min-height: calc(100vh - 78px);
        overflow: hidden;
        background: #08090b;
    }

    .register-stage {
        min-height: calc(100vh - 78px);
        display: grid;
        grid-template-columns: minmax(0, 1.05fr) minmax(430px, .95fr);
    }

    .register-visual {
        position: relative;
        min-height: 100%;
        overflow: hidden;
        isolation: isolate;
        display: flex;
        align-items: flex-end;
        padding: clamp(34px, 5vw, 72px);
        background: #0a0c0f;
    }

    .register-visual::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -3;
        background:
            linear-gradient(
                180deg,
                rgba(5,6,8,.10),
                rgba(5,6,8,.24) 42%,
                rgba(5,6,8,.94) 100%
            ),
            linear-gradient(
                90deg,
                rgba(5,6,8,.38),
                transparent 58%
            ),
            url('https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1800&q=88')
            center / cover no-repeat;
        transform: scale(1.03);
        animation: registerVisualZoom 18s ease-in-out infinite alternate;
    }

    .register-visual::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -2;
        background:
            radial-gradient(
                circle at 78% 18%,
                rgba(215,164,95,.18),
                transparent 19rem
            ),
            linear-gradient(
                180deg,
                transparent 70%,
                #08090b 100%
            );
        pointer-events: none;
    }

    @keyframes registerVisualZoom {
        from { transform: scale(1.03); }
        to { transform: scale(1.09); }
    }

    .register-visual-content {
        max-width: 690px;
        animation: registerFadeUp .85s ease both;
    }

    .register-brand-kicker {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
        color: #efc985;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .24em;
        text-transform: uppercase;
    }

    .register-brand-kicker::before {
        content: "";
        width: 34px;
        height: 1px;
        background: #d7a45f;
    }

    .register-visual h2 {
        max-width: 680px;
        margin: 0;
        color: #ffffff;
        font-size: clamp(50px, 5.8vw, 86px);
        line-height: .92;
        letter-spacing: -.068em;
        font-weight: 950;
    }

    .register-visual h2 span {
        color: #f0c983;
    }

    .register-visual p {
        max-width: 560px;
        margin: 24px 0 0;
        color: rgba(255,255,255,.68);
        font-size: 14px;
        line-height: 1.85;
    }

    .register-trust-row {
        margin-top: 32px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .register-trust {
        padding: 9px 12px;
        border: 1px solid rgba(255,255,255,.13);
        border-radius: 999px;
        background: rgba(255,255,255,.05);
        backdrop-filter: blur(12px);
        color: rgba(255,255,255,.76);
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .register-panel {
        position: relative;
        min-height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 54px 42px;
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

    .register-panel::before {
        content: "M";
        position: absolute;
        right: -24px;
        top: 12%;
        color: rgba(255,255,255,.016);
        font-size: 300px;
        font-weight: 950;
        line-height: .8;
        pointer-events: none;
        user-select: none;
    }

    .register-shell {
        position: relative;
        z-index: 2;
        width: 100%;
        max-width: 500px;
        animation: registerFadeUp .8s .08s ease both;
    }

    @keyframes registerFadeUp {
        from {
            opacity: 0;
            transform: translateY(28px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .register-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 34px;
    }

    .register-brand-mark {
        width: 44px;
        height: 44px;
        display: grid;
        place-items: center;
        border-radius: 14px;
        background: linear-gradient(145deg, #f0ca86, #b67e3d);
        color: #15110c;
        font-size: 20px;
        font-weight: 950;
        box-shadow: 0 14px 34px rgba(215,164,95,.22);
    }

    .register-brand-copy strong {
        display: block;
        color: #ffffff;
        font-size: 19px;
        line-height: 1;
        letter-spacing: -.03em;
    }

    .register-brand-copy span {
        display: block;
        margin-top: 5px;
        color: #6f757c;
        font-size: 8px;
        font-weight: 850;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .register-kicker {
        display: inline-block;
        margin-bottom: 10px;
        color: #b9894d;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .register-title {
        margin: 0;
        color: #ffffff;
        font-size: clamp(38px, 4vw, 52px);
        line-height: 1;
        letter-spacing: -.055em;
        font-weight: 950;
    }

    .register-subtitle {
        margin: 14px 0 28px;
        color: #7f858c;
        font-size: 13px;
        line-height: 1.8;
    }

    .register-message {
        margin-bottom: 20px;
        padding: 13px 15px;
        border-radius: 14px;
        font-size: 12px;
        line-height: 1.6;
    }

    .register-message.success {
        border: 1px solid rgba(91,214,149,.22);
        background: rgba(91,214,149,.08);
        color: #a9efc8;
    }

    .register-message.error {
        border: 1px solid rgba(241,123,123,.22);
        background: rgba(241,123,123,.08);
        color: #ffc1c1;
    }

    .register-message ul {
        margin: 8px 0 0 18px;
        padding: 0;
    }

    .register-field {
        margin-bottom: 18px;
    }

    .register-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 8px;
    }

    .register-label label {
        color: #b9b9b6;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .field-error {
        color: #f3a1a1;
        font-size: 10px;
        font-weight: 700;
    }

    .register-input-wrap {
        position: relative;
    }

    .register-input {
        width: 100%;
        height: 56px;
        padding: 0 48px 0 16px;
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

    .register-input::placeholder {
        color: #565c63;
    }

    .register-input:focus {
        border-color: rgba(215,164,95,.46);
        background: rgba(215,164,95,.035);
        box-shadow: 0 0 0 4px rgba(215,164,95,.065);
    }

    .password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        min-width: 42px;
        height: 36px;
        padding: 0 8px;
        border: 0;
        border-radius: 10px;
        background: transparent;
        color: #8b9096;
        font-size: 10px;
        font-weight: 800;
        cursor: pointer;
    }

    .password-toggle:hover {
        color: #efc985;
        background: rgba(215,164,95,.06);
    }

    .register-options {
        margin: 4px 0 22px;
    }

    .terms-label {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        color: #7e848b;
        font-size: 10px;
        line-height: 1.65;
        cursor: pointer;
    }

    .terms-label input {
        flex: 0 0 auto;
        width: 15px;
        height: 15px;
        margin-top: 1px;
        accent-color: #d7a45f;
    }

    .register-submit {
        width: 100%;
        min-height: 56px;
        border: 0;
        border-radius: 999px;
        background: linear-gradient(135deg, #f1cc8b, #ca914c);
        color: #14100b;
        font-size: 12px;
        font-weight: 950;
        letter-spacing: .04em;
        cursor: pointer;
        box-shadow: 0 18px 44px rgba(215,164,95,.20);
        transition:
            transform .22s ease,
            box-shadow .22s ease,
            opacity .2s ease;
    }

    .register-submit:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 25px 58px rgba(215,164,95,.30);
    }

    .register-submit:disabled {
        cursor: wait;
        opacity: .72;
        transform: none;
    }

    .register-security-status {
        min-height: 18px;
        margin: 12px 0 0;
        color: #6f757c;
        font-size: 9px;
        line-height: 1.6;
        text-align: center;
    }

    .register-security-status.is-active {
        color: #caa46d;
    }

    .register-divider {
        margin: 28px 0 24px;
        display: flex;
        align-items: center;
        gap: 14px;
        color: #4d5258;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .register-divider::before,
    .register-divider::after {
        content: "";
        flex: 1;
        height: 1px;
        background: rgba(255,255,255,.07);
    }

    .login-card {
        padding: 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 17px;
        background: rgba(255,255,255,.025);
    }

    .login-card-copy strong {
        display: block;
        color: #dcdad5;
        font-size: 12px;
    }

    .login-card-copy span {
        display: block;
        margin-top: 3px;
        color: #686e75;
        font-size: 10px;
        line-height: 1.5;
    }

    .login-card-link {
        flex-shrink: 0;
        min-height: 38px;
        padding: 0 14px;
        display: inline-flex;
        align-items: center;
        border: 1px solid rgba(215,164,95,.18);
        border-radius: 999px;
        background: rgba(215,164,95,.055);
        color: #dfb36d;
        text-decoration: none;
        font-size: 9px;
        font-weight: 900;
    }

    .register-security-note {
        margin-top: 18px;
        padding-top: 16px;
        border-top: 1px solid rgba(255,255,255,.055);
        color: #555b61;
        font-size: 9px;
        line-height: 1.7;
        text-align: center;
    }

    .register-security-note strong {
        color: #8e949b;
    }

    @media (max-width: 1000px) {
        .register-stage {
            grid-template-columns: 1fr;
        }

        .register-visual {
            min-height: 520px;
        }

        .register-panel {
            min-height: auto;
            padding: 70px 32px;
        }
    }

    @media (max-width: 620px) {
        .register-visual {
            min-height: 430px;
            padding: 38px 20px;
        }

        .register-visual h2 {
            font-size: clamp(46px, 15vw, 64px);
        }

        .register-panel {
            padding: 52px 18px 64px;
        }

        .login-card {
            align-items: flex-start;
            flex-direction: column;
        }

        .login-card-link {
            width: 100%;
            justify-content: center;
            box-sizing: border-box;
        }
    }
</style>
@endpush

@section('content')
<section class="register-page">
    <div class="register-stage">

        <div class="register-visual">
            <div class="register-visual-content">
                <span class="register-brand-kicker">
                    Mashal Automotive
                </span>

                <h2>
                    Create your
                    <span>exceptional.</span>
                    account
                </h2>

                <p>
                    Maak je persoonlijke Mashal-account aan en beheer
                    favorieten, bestellingen, accountgegevens en beveiliging
                    vanuit één premium omgeving.
                </p>

                <div class="register-trust-row">
                    <span class="register-trust">Secure registration</span>
                    <span class="register-trust">Protected account</span>
                    <span class="register-trust">Premium experience</span>
                </div>
            </div>
        </div>

        <div class="register-panel">
            <div class="register-shell">

                <div class="register-brand">
                    <div class="register-brand-mark">M</div>

                    <div class="register-brand-copy">
                        <strong>Mashal</strong>
                        <span>Automotive</span>
                    </div>
                </div>

                <span class="register-kicker">
                    New member
                </span>

                <h1 class="register-title">
                    Account aanmaken
                </h1>

                <p class="register-subtitle">
                    Vul je gegevens in om je Mashal-account aan te maken.
                </p>

                @if (session('success'))
                    <div class="register-message success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="register-message error">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="register-message error">
                        <strong>Registreren is niet gelukt.</strong>

                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form
                    id="registerForm"
                    method="POST"
                    action="{{ route('register.submit') }}"
                    data-login-security-form
                >
                    @csrf

                    <div class="register-field">
                        <div class="register-label">
                            <label for="name">Naam</label>

                            @error('name')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="register-input-wrap">
                            <input
                                class="register-input"
                                id="name"
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Jouw volledige naam"
                                autocomplete="name"
                                maxlength="255"
                                required
                                autofocus
                            >
                        </div>
                    </div>

                    <div class="register-field">
                        <div class="register-label">
                            <label for="email">E-mailadres</label>

                            @error('email')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="register-input-wrap">
                            <input
                                class="register-input"
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="naam@example.com"
                                autocomplete="email"
                                maxlength="255"
                                required
                            >
                        </div>
                    </div>

                    <div class="register-field">
                        <div class="register-label">
                            <label for="password">Wachtwoord</label>

                            @error('password')
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="register-input-wrap">
                            <input
                                class="register-input"
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Minimaal 8 tekens"
                                autocomplete="new-password"
                                minlength="8"
                                required
                            >

                            <button
                                class="password-toggle"
                                type="button"
                                data-password-toggle="password"
                            >
                                Toon
                            </button>
                        </div>
                    </div>

                    <div class="register-field">
                        <div class="register-label">
                            <label for="password_confirmation">
                                Wachtwoord herhalen
                            </label>
                        </div>

                        <div class="register-input-wrap">
                            <input
                                class="register-input"
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                placeholder="Herhaal je wachtwoord"
                                autocomplete="new-password"
                                minlength="8"
                                required
                            >

                            <button
                                class="password-toggle"
                                type="button"
                                data-password-toggle="password_confirmation"
                            >
                                Toon
                            </button>
                        </div>
                    </div>

                    <div class="register-options">
                        <label class="terms-label">
                            <input
                                type="checkbox"
                                name="terms"
                                value="1"
                                required
                            >

                            <span>
                                Ik ga akkoord met de voorwaarden en begrijp
                                dat beveiligingsgegevens zoals IP-adres,
                                apparaat, browser en timezone voor
                                accountbeveiliging kunnen worden opgeslagen.
                            </span>
                        </label>
                    </div>

                    <button
                        id="registerSubmitButton"
                        class="register-submit"
                        type="submit"
                    >
                        Account aanmaken
                    </button>

                    <div
                        id="registerSecurityStatus"
                        class="register-security-status"
                        aria-live="polite"
                    ></div>
                </form>

                <div class="register-divider">
                    Heb je al een account?
                </div>

                <div class="login-card">
                    <div class="login-card-copy">
                        <strong>Welkom terug</strong>
                        <span>
                            Log in met wachtwoord, e-mailcode,
                            magic link of social login.
                        </span>
                    </div>

                    <a
                        class="login-card-link"
                        href="{{ route('login') }}"
                    >
                        Inloggen
                    </a>
                </div>

                <div class="register-security-note">
                    <strong>Loginbeveiliging:</strong>
                    browser-timezone wordt vóór registratie opgeslagen.
                    Een precieze browserlocatie wordt alleen gebruikt wanneer
                    je browser daarvoor toestemming geeft. Als locatie wordt
                    geweigerd, blijft registreren gewoon mogelijk.
                </div>

            </div>
        </div>

    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const form = document.getElementById('registerForm');
    const submitButton = document.getElementById('registerSubmitButton');
    const statusElement = document.getElementById('registerSecurityStatus');

    if (!form) {
        return;
    }

    const securityContextUrl = @json(route('login-security.context'));
    const csrfToken = @json(csrf_token());

    const preciseLocationEnabled = @json(
        (bool) config(
            'login-security.precise_location.enabled',
            true
        )
    );

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
                'login-security.precise_location.max_age_ms',
                (int) config(
                    'login-security.precise_location.maximum_age_ms',
                    60000
                )
            )
        ) }}
    };

    let securityContextHandled = false;

    document
        .querySelectorAll('[data-password-toggle]')
        .forEach(function (button) {
            button.addEventListener('click', function () {
                const input = document.getElementById(
                    button.getAttribute('data-password-toggle')
                );

                if (!input) {
                    return;
                }

                const visible = input.type === 'text';

                input.type = visible ? 'password' : 'text';
                button.textContent = visible ? 'Toon' : 'Verberg';
            });
        });

    function setStatus(message, active = false) {
        if (!statusElement) {
            return;
        }

        statusElement.textContent = message || '';
        statusElement.classList.toggle('is-active', Boolean(active));
    }

    function setSubmitting(submitting) {
        if (!submitButton) {
            return;
        }

        submitButton.disabled = Boolean(submitting);
        submitButton.textContent = submitting
            ? 'Beveiliging voorbereiden...'
            : 'Account aanmaken';
    }

    function getBrowserTimezone() {
        try {
            return Intl.DateTimeFormat().resolvedOptions().timeZone || null;
        } catch (error) {
            return null;
        }
    }

    async function storeSecurityContext(payload) {
        try {
            const response = await fetch(securityContextUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });

            return response.ok;
        } catch (error) {
            return false;
        }
    }

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

    function getCurrentLocation() {
        return new Promise(function (resolve) {
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
                        latitude: Number.isFinite(position.coords.latitude)
                            ? position.coords.latitude
                            : null,
                        longitude: Number.isFinite(position.coords.longitude)
                            ? position.coords.longitude
                            : null,
                        accuracy: Number.isFinite(position.coords.accuracy)
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

    async function captureSecurityContext() {
        const payload = {
            browser_timezone: getBrowserTimezone(),
            latitude: null,
            longitude: null,
            location_accuracy: null,
            location_permission: 'unknown'
        };

        if (!preciseLocationEnabled) {
            payload.location_permission = 'unavailable';

            return await storeSecurityContext(payload);
        }

        if (
            !navigator.geolocation ||
            typeof navigator.geolocation.getCurrentPosition !== 'function'
        ) {
            payload.location_permission = 'unsupported';

            return await storeSecurityContext(payload);
        }

        const permissionState = await getPermissionState();

        if (permissionState === 'denied') {
            payload.location_permission = 'denied';

            return await storeSecurityContext(payload);
        }

        if (permissionState === 'prompt') {
            payload.location_permission = 'prompt';
        }

        const location = await getCurrentLocation();

        payload.location_permission = location.permission || 'unknown';
        payload.latitude = location.latitude;
        payload.longitude = location.longitude;
        payload.location_accuracy = location.accuracy;

        return await storeSecurityContext(payload);
    }

    form.addEventListener('submit', async function (event) {
        if (securityContextHandled) {
            return;
        }

        event.preventDefault();

        setSubmitting(true);
        setStatus(
            'Accountbeveiliging wordt voorbereid...',
            true
        );

        try {
            await captureSecurityContext();
        } catch (error) {
            // Security-context mag registratie niet blokkeren.
        }

        securityContextHandled = true;

        setStatus(
            'Registratie wordt verwerkt...',
            true
        );

        HTMLFormElement.prototype.submit.call(form);
    });
});
</script>
@endsection
