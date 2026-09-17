@extends('layouts.site-layout')

@section('title', 'Mashal | Inloggen')

@push('styles')

<style>

    .login-page {

        position: relative;

        min-height: calc(100vh - 78px);

        overflow: hidden;

        background: #08090b;

    }

    .login-stage {

        min-height: calc(100vh - 78px);

        display: grid;

        grid-template-columns: minmax(0, 1.05fr) minmax(430px, .95fr);

    }

    /* ========================================================= */

    /* LEFT — CINEMATIC BRAND PANEL                              */

    /* ========================================================= */

    .login-visual {

        position: relative;

        min-height: 100%;

        overflow: hidden;

        isolation: isolate;

        display: flex;

        align-items: flex-end;

        padding: clamp(34px, 5vw, 72px);

        background: #0a0c0f;

    }

    .login-visual::before {

        content: "";

        position: absolute;

        inset: 0;

        z-index: -3;

        background:

            linear-gradient(

                180deg,

                rgba(5,6,8,.12),

                rgba(5,6,8,.22) 42%,

                rgba(5,6,8,.92) 100%

            ),

            linear-gradient(

                90deg,

                rgba(5,6,8,.35),

                transparent 56%

            ),

            url('https://images.unsplash.com/photo-1503736334956-4c8f8e92946d?auto=format&fit=crop&w=1800&q=88')

            center / cover no-repeat;

        transform: scale(1.03);

        animation: loginVisualZoom 18s ease-in-out infinite alternate;

    }

    .login-visual::after {

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

    @keyframes loginVisualZoom {

        from { transform: scale(1.03); }

        to { transform: scale(1.09); }

    }

    .login-visual-content {

        max-width: 690px;

        animation: loginFadeUp .85s ease both;

    }

    .login-brand-kicker {

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

    .login-brand-kicker::before {

        content: "";

        width: 34px;

        height: 1px;

        background: #d7a45f;

    }

    .login-visual h2 {

        max-width: 680px;

        margin: 0;

        color: #ffffff;

        font-size: clamp(50px, 5.8vw, 86px);

        line-height: .92;

        letter-spacing: -.068em;

        font-weight: 950;

    }

    .login-visual h2 span {

        color: #f0c983;

    }

    .login-visual p {

        max-width: 560px;

        margin: 24px 0 0;

        color: rgba(255,255,255,.68);

        font-size: 14px;

        line-height: 1.85;

    }

    .visual-trust-row {

        margin-top: 32px;

        display: flex;

        gap: 10px;

        flex-wrap: wrap;

    }

    .visual-trust {

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

    /* ========================================================= */

    /* RIGHT — AUTH PANEL                                         */

    /* ========================================================= */

    .login-panel {

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

    .login-panel::before {

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

    .auth-shell {

        position: relative;

        z-index: 2;

        width: 100%;

        max-width: 470px;

        animation: loginFadeUp .8s .08s ease both;

    }

    @keyframes loginFadeUp {

        from {

            opacity: 0;

            transform: translateY(28px);

        }

        to {

            opacity: 1;

            transform: translateY(0);

        }

    }

    .auth-brand {

        display: flex;

        align-items: center;

        gap: 12px;

        margin-bottom: 36px;

    }

    .auth-brand-mark {

        width: 44px;

        height: 44px;

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

    .auth-brand-copy strong {

        display: block;

        color: #ffffff;

        font-size: 19px;

        line-height: 1;

        letter-spacing: -.03em;

    }

    .auth-brand-copy span {

        display: block;

        margin-top: 5px;

        color: #6f757c;

        font-size: 8px;

        font-weight: 850;

        letter-spacing: .18em;

        text-transform: uppercase;

    }

    .auth-kicker {

        display: inline-block;

        margin-bottom: 10px;

        color: #b9894d;

        font-size: 9px;

        font-weight: 900;

        letter-spacing: .18em;

        text-transform: uppercase;

    }

    .auth-title {

        margin: 0;

        color: #ffffff;

        font-size: clamp(38px, 4vw, 52px);

        line-height: 1;

        letter-spacing: -.055em;

        font-weight: 950;

    }

    .auth-subtitle {

        margin: 14px 0 30px;

        color: #7f858c;

        font-size: 13px;

        line-height: 1.8;

    }

    /* ========================================================= */

    /* MESSAGES                                                   */

    /* ========================================================= */

    .auth-message {

        margin-bottom: 20px;

        padding: 13px 15px;

        border-radius: 14px;

        font-size: 12px;

        line-height: 1.6;

        backdrop-filter: blur(12px);

        animation: loginFadeUp .35s ease both;

    }

    .auth-message.success {

        border: 1px solid rgba(91,214,149,.22);

        background: rgba(91,214,149,.08);

        color: #a9efc8;

    }

    .auth-message.error {

        border: 1px solid rgba(241,123,123,.22);

        background: rgba(241,123,123,.08);

        color: #ffc1c1;

    }

    .auth-message ul {

        margin: 8px 0 0 18px;

        padding: 0;

    }









    /* ========================================================= */
    /* E-MAILCODE LOGIN                                         */
    /* ========================================================= */

    .email-code-auth {
        margin: 0 0 24px;
        padding: 18px;
        border: 1px solid rgba(215,164,95,.16);
        border-radius: 18px;
        background:
            linear-gradient(180deg, rgba(215,164,95,.065), rgba(255,255,255,.022));
        box-shadow: inset 0 1px 0 rgba(255,255,255,.035);
    }

    .email-code-head {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 14px;
    }

    .email-code-icon {
        flex-shrink: 0;
        width: 38px;
        height: 38px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        background: linear-gradient(145deg, #f0ca86, #b67e3d);
        color: #15110c;
        font-size: 17px;
        font-weight: 950;
        box-shadow: 0 12px 28px rgba(215,164,95,.18);
    }

    .email-code-copy strong {
        display: block;
        color: #f4f1eb;
        font-size: 12px;
        font-weight: 950;
    }

    .email-code-copy span {
        display: block;
        margin-top: 4px;
        color: #747a81;
        font-size: 9px;
        line-height: 1.6;
    }

    .email-code-form {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 10px;
    }

    .email-code-input {
        width: 100%;
        min-width: 0;
        height: 48px;
        padding: 0 14px;
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 13px;
        outline: none;
        background: rgba(255,255,255,.035);
        color: #ffffff;
        font-size: 13px;
        transition: border-color .2s ease, background .2s ease, box-shadow .2s ease;
    }

    .email-code-input::placeholder {
        color: #565c63;
    }

    .email-code-input:focus {
        border-color: rgba(215,164,95,.46);
        background: rgba(215,164,95,.035);
        box-shadow: 0 0 0 4px rgba(215,164,95,.065);
    }

    .email-code-submit {
        min-height: 48px;
        padding: 0 18px;
        border: 0;
        border-radius: 13px;
        background: linear-gradient(135deg, #f1cc8b, #ca914c);
        color: #14100b;
        font-size: 10px;
        font-weight: 950;
        cursor: pointer;
        white-space: nowrap;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .email-code-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 30px rgba(215,164,95,.22);
    }

    .email-code-note {
        margin: 10px 2px 0;
        color: #555b61;
        font-size: 9px;
        line-height: 1.6;
    }

    @media (max-width: 520px) {
        .email-code-form {
            grid-template-columns: 1fr;
        }

        .email-code-submit {
            width: 100%;
        }
    }

    /* ========================================================= */

    /* GOOGLE OAUTH                                              */

    /* ========================================================= */

    .google-auth {

        margin: 0 0 24px;

    }

    .google-auth-button {

        position: relative;

        width: 100%;

        min-height: 58px;

        display: flex;

        align-items: center;

        justify-content: center;

        gap: 13px;

        padding: 0 58px;

        overflow: hidden;

        border: 1px solid rgba(255,255,255,.12);

        border-radius: 999px;

        background:

            linear-gradient(

                180deg,

                rgba(255,255,255,.075),

                rgba(255,255,255,.032)

            );

        color: #f4f1eb;

        text-decoration: none;

        box-shadow:

            inset 0 1px 0 rgba(255,255,255,.06),

            0 14px 36px rgba(0,0,0,.20);

        transition:

            transform .22s ease,

            border-color .22s ease,

            background .22s ease,

            box-shadow .22s ease;

    }

    .google-auth-button::before {

        content: "";

        position: absolute;

        inset: 0;

        background:

            linear-gradient(

                110deg,

                transparent 20%,

                rgba(255,255,255,.05) 48%,

                transparent 76%

            );

        transform: translateX(-130%);

        transition: transform .7s ease;

        pointer-events: none;

    }

    .google-auth-button:hover {

        transform: translateY(-2px);

        border-color: rgba(215,164,95,.32);

        background:

            linear-gradient(

                180deg,

                rgba(215,164,95,.09),

                rgba(255,255,255,.038)

            );

        box-shadow:

            inset 0 1px 0 rgba(255,255,255,.08),

            0 22px 50px rgba(0,0,0,.25);

    }

    .google-auth-button:hover::before {

        transform: translateX(130%);

    }

    .google-auth-icon {

        position: absolute;

        left: 17px;

        width: 25px;

        height: 25px;

        display: grid;

        place-items: center;

        border-radius: 50%;

        background: #ffffff;

        box-shadow: 0 8px 22px rgba(0,0,0,.22);

    }

    .google-auth-icon svg {

        width: 16px;

        height: 16px;

        display: block;

    }

    .google-auth-copy {

        position: relative;

        z-index: 1;

        display: flex;

        flex-direction: column;

        align-items: center;

        gap: 2px;

        text-align: center;

    }

    .google-auth-copy strong {

        color: #f4f1eb;

        font-size: 12px;

        line-height: 1.2;

        font-weight: 950;

        letter-spacing: .01em;

    }

    .google-auth-copy small {

        color: #747a81;

        font-size: 8px;

        line-height: 1.4;

        font-weight: 750;

        letter-spacing: .035em;

    }

    .google-auth-note {

        margin-top: 10px;

        text-align: center;

        color: #555b61;

        font-size: 9px;

        line-height: 1.65;

    }

    .google-auth-note strong {

        color: #8e949b;

        font-weight: 850;

    }

    /* ========================================================= */

    /* GITHUB OAUTH                                              */

    /* ========================================================= */

    .github-auth {

        margin: -12px 0 24px;

    }

    .github-auth-button {

        position: relative;

        width: 100%;

        min-height: 58px;

        display: flex;

        align-items: center;

        justify-content: center;

        gap: 13px;

        padding: 0 58px;

        overflow: hidden;

        border: 1px solid rgba(255,255,255,.12);

        border-radius: 999px;

        background:

            linear-gradient(

                180deg,

                rgba(255,255,255,.07),

                rgba(255,255,255,.025)

            ),

            #0d1117;

        color: #f4f1eb;

        text-decoration: none;

        box-shadow:

            inset 0 1px 0 rgba(255,255,255,.055),

            0 14px 36px rgba(0,0,0,.22);

        transition:

            transform .22s ease,

            border-color .22s ease,

            background .22s ease,

            box-shadow .22s ease;

    }

    .github-auth-button::before {

        content: "";

        position: absolute;

        inset: 0;

        background:

            linear-gradient(

                110deg,

                transparent 20%,

                rgba(255,255,255,.05) 48%,

                transparent 76%

            );

        transform: translateX(-130%);

        transition: transform .7s ease;

        pointer-events: none;

    }

    .github-auth-button:hover {

        transform: translateY(-2px);

        border-color: rgba(215,164,95,.32);

        background:

            linear-gradient(

                180deg,

                rgba(215,164,95,.075),

                rgba(255,255,255,.028)

            ),

            #0d1117;

        box-shadow:

            inset 0 1px 0 rgba(255,255,255,.07),

            0 22px 50px rgba(0,0,0,.26);

    }

    .github-auth-button:hover::before {

        transform: translateX(130%);

    }

    .github-auth-icon {

        position: absolute;

        left: 17px;

        width: 25px;

        height: 25px;

        display: grid;

        place-items: center;

        border-radius: 50%;

        background: #f6f4ef;

        color: #0d1117;

        box-shadow: 0 8px 22px rgba(0,0,0,.22);

    }

    .github-auth-icon svg {

        width: 16px;

        height: 16px;

        display: block;

        fill: currentColor;

    }

    .github-auth-copy {

        position: relative;

        z-index: 1;

        display: flex;

        flex-direction: column;

        align-items: center;

        gap: 2px;

        text-align: center;

    }

    .github-auth-copy strong {

        color: #f4f1eb;

        font-size: 12px;

        line-height: 1.2;

        font-weight: 950;

        letter-spacing: .01em;

    }

    .github-auth-copy small {

        color: #747a81;

        font-size: 8px;

        line-height: 1.4;

        font-weight: 750;

        letter-spacing: .035em;

    }

    .github-auth-note {

        margin-top: 10px;

        text-align: center;

        color: #555b61;

        font-size: 9px;

        line-height: 1.65;

    }

    .github-auth-note strong {

        color: #8e949b;

        font-weight: 850;

    }





    /* ========================================================= */

    /* FACEBOOK OAUTH                                           */

    /* ========================================================= */

    .facebook-auth {

        margin: -12px 0 24px;

    }

    .facebook-auth-button {

        position: relative;

        width: 100%;

        min-height: 58px;

        display: flex;

        align-items: center;

        justify-content: center;

        gap: 13px;

        padding: 0 58px;

        overflow: hidden;

        border: 1px solid rgba(24,119,242,.34);

        border-radius: 999px;

        background:

            linear-gradient(

                180deg,

                rgba(24,119,242,.16),

                rgba(24,119,242,.07)

            ),

            #0b1018;

        color: #f4f1eb;

        text-decoration: none;

        box-shadow:

            inset 0 1px 0 rgba(255,255,255,.055),

            0 14px 36px rgba(0,0,0,.22);

        transition:

            transform .22s ease,

            border-color .22s ease,

            background .22s ease,

            box-shadow .22s ease;

    }

    .facebook-auth-button::before {

        content: "";

        position: absolute;

        inset: 0;

        background:

            linear-gradient(

                110deg,

                transparent 20%,

                rgba(255,255,255,.05) 48%,

                transparent 76%

            );

        transform: translateX(-130%);

        transition: transform .7s ease;

        pointer-events: none;

    }

    .facebook-auth-button:hover {

        transform: translateY(-2px);

        border-color: rgba(24,119,242,.58);

        background:

            linear-gradient(

                180deg,

                rgba(24,119,242,.22),

                rgba(24,119,242,.09)

            ),

            #0b1018;

        box-shadow:

            inset 0 1px 0 rgba(255,255,255,.07),

            0 22px 50px rgba(0,0,0,.26);

    }

    .facebook-auth-button:hover::before {

        transform: translateX(130%);

    }

    .facebook-auth-icon {

        position: absolute;

        left: 17px;

        width: 25px;

        height: 25px;

        display: grid;

        place-items: center;

        border-radius: 50%;

        background: #1877f2;

        color: #ffffff;

        box-shadow: 0 8px 22px rgba(0,0,0,.22);

    }

    .facebook-auth-icon svg {

        width: 15px;

        height: 15px;

        display: block;

        fill: currentColor;

    }

    .facebook-auth-copy {

        position: relative;

        z-index: 1;

        display: flex;

        flex-direction: column;

        align-items: center;

        gap: 2px;

        text-align: center;

    }

    .facebook-auth-copy strong {

        color: #f4f1eb;

        font-size: 12px;

        line-height: 1.2;

        font-weight: 950;

        letter-spacing: .01em;

    }

    .facebook-auth-copy small {

        color: #747a81;

        font-size: 8px;

        line-height: 1.4;

        font-weight: 750;

        letter-spacing: .035em;

    }

    .facebook-auth-note {

        margin-top: 10px;

        text-align: center;

        color: #555b61;

        font-size: 9px;

        line-height: 1.65;

    }

    .facebook-auth-note strong {

        color: #8e949b;

        font-weight: 850;

    }







    .oauth-divider {

        margin: 20px 0 24px;

        display: flex;

        align-items: center;

        gap: 14px;

        color: #4d5258;

        font-size: 8px;

        font-weight: 900;

        letter-spacing: .14em;

        text-transform: uppercase;

    }

    .oauth-divider::before,

    .oauth-divider::after {

        content: "";

        flex: 1;

        height: 1px;

        background:

            linear-gradient(

                90deg,

                transparent,

                rgba(255,255,255,.09),

                transparent

            );

    }

    /* ========================================================= */

    /* FIELDS                                                     */

    /* ========================================================= */

    .auth-field {

        margin-bottom: 19px;

    }

    .auth-label {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 14px;

        margin-bottom: 8px;

    }

    .auth-label label {

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

    .auth-input-wrap {

        position: relative;

    }

    .auth-input {

        width: 100%;

        height: 56px;

        padding: 0 48px 0 16px;

        border: 1px solid rgba(255,255,255,.10);

        border-radius: 15px;

        outline: none;

        background: rgba(255,255,255,.035);

        color: #ffffff;

        font-size: 14px;

        transition:

            border-color .2s ease,

            background .2s ease,

            box-shadow .2s ease,

            transform .2s ease;

    }

    .auth-input::placeholder {

        color: #565c63;

    }

    .auth-input:focus {

        border-color: rgba(215,164,95,.46);

        background: rgba(215,164,95,.035);

        box-shadow: 0 0 0 4px rgba(215,164,95,.065);

    }

    .auth-input-icon {

        position: absolute;

        right: 16px;

        top: 50%;

        transform: translateY(-50%);

        color: #747a81;

        font-size: 14px;

        pointer-events: none;

    }

    .password-toggle {

        position: absolute;

        right: 10px;

        top: 50%;

        transform: translateY(-50%);

        min-width: 38px;

        height: 36px;

        padding: 0 8px;

        border: 0;

        border-radius: 10px;

        background: transparent;

        color: #8b9096;

        font-size: 10px;

        font-weight: 800;

        cursor: pointer;

        transition:

            color .2s ease,

            background .2s ease;

    }

    .password-toggle:hover {

        color: #efc985;

        background: rgba(215,164,95,.06);

    }

    /* ========================================================= */

    /* OPTIONS                                                    */

    /* ========================================================= */

    .auth-options {

        margin: 5px 0 24px;

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 14px;

    }

    .remember-label {

        display: inline-flex;

        align-items: center;

        gap: 8px;

        color: #8d9298;

        font-size: 11px;

        cursor: pointer;

    }

    .remember-label input {

        width: 15px;

        height: 15px;

        accent-color: #d7a45f;

    }

    .auth-link {

        color: #caa46d;

        text-decoration: none;

        font-size: 11px;

        font-weight: 800;

        transition: color .2s ease;

    }

    .auth-link:hover {

        color: #f0c983;

    }

    /* ========================================================= */

    /* SUBMIT                                                     */

    /* ========================================================= */

    .auth-submit {

        position: relative;

        width: 100%;

        min-height: 56px;

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

        letter-spacing: .04em;

        cursor: pointer;

        box-shadow: 0 18px 44px rgba(215,164,95,.20);

        transition:

            transform .22s ease,

            box-shadow .22s ease;

    }

    .auth-submit::after {

        content: "→";

        position: absolute;

        right: 22px;

        top: 50%;

        transform: translateY(-50%);

        font-size: 17px;

        transition: transform .22s ease;

    }

    .auth-submit:hover {

        transform: translateY(-2px);

        box-shadow: 0 25px 58px rgba(215,164,95,.30);

    }

    .auth-submit:hover::after {

        transform: translate(4px, -50%);

    }

    /* ========================================================= */

    /* FOOTER / REGISTER                                          */

    /* ========================================================= */

    .auth-divider {

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

    .auth-divider::before,

    .auth-divider::after {

        content: "";

        flex: 1;

        height: 1px;

        background: rgba(255,255,255,.07);

    }

    .register-card {

        padding: 18px;

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 18px;

        border: 1px solid rgba(255,255,255,.08);

        border-radius: 17px;

        background: rgba(255,255,255,.025);

    }

    .register-card-copy strong {

        display: block;

        color: #dcdad5;

        font-size: 12px;

    }

    .register-card-copy span {

        display: block;

        margin-top: 3px;

        color: #686e75;

        font-size: 10px;

        line-height: 1.5;

    }

    .register-card-link {

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

        transition:

            background .2s ease,

            border-color .2s ease,

            transform .2s ease;

    }

    .register-card-link:hover {

        transform: translateY(-1px);

        border-color: rgba(215,164,95,.34);

        background: rgba(215,164,95,.10);

    }

    .security-note {

        margin-top: 18px;

        display: flex;

        align-items: flex-start;

        gap: 10px;

        color: #555b61;

        font-size: 9px;

        line-height: 1.6;

    }

    .security-note-mark {

        flex-shrink: 0;

        width: 20px;

        height: 20px;

        display: grid;

        place-items: center;

        border: 1px solid rgba(255,255,255,.07);

        border-radius: 50%;

        color: #8b6a40;

        font-size: 9px;

    }

    /* ========================================================= */

    /* RESPONSIVE                                                 */

    /* ========================================================= */

    @media (max-width: 1000px) {

        .login-stage {

            grid-template-columns: 1fr;

        }

        .login-visual {

            min-height: 520px;

        }

        .login-panel {

            min-height: auto;

            padding: 70px 32px;

        }

    }

    @media (max-width: 620px) {

        .login-page,

        .login-stage {

            min-height: auto;

        }

        .login-visual {

            min-height: 430px;

            padding: 38px 20px;

        }

        .login-visual h2 {

            font-size: clamp(46px, 15vw, 64px);

        }

        .login-panel {

            padding: 52px 18px 64px;

        }

        .auth-brand {

            margin-bottom: 30px;

        }

        .auth-options {

            align-items: flex-start;

            flex-direction: column;

        }

        .register-card {

            align-items: flex-start;

            flex-direction: column;

        }

        .register-card-link {

            width: 100%;

            justify-content: center;

        }

    }

</style>

@endpush

















@section('content')

<section class="login-page">

    <div class="login-stage">

        {{-- ========================================================= --}}

        {{-- LEFT / BRAND EXPERIENCE                                    --}}

        {{-- ========================================================= --}}

        <div class="login-visual">

            <div class="login-visual-content">

                <span class="login-brand-kicker">

                    Mashal Automotive

                </span>

                <h2>

                    Welcome back

                    to something

                    <span>exceptional.</span>

                </h2>

                <p>

                    Log in op je persoonlijke Mashal-account

                    en ga verder waar je gebleven bent:

                    jouw selectie, bestellingen en accountbeheer

                    op één plek.

                </p>

















                <div class="visual-trust-row">

                    <span class="visual-trust">

                        Secure account

                    </span>

                    <span class="visual-trust">

                        Verified access

                    </span>

                    <span class="visual-trust">

                        Premium experience

                    </span>

                </div>

            </div>

        </div>

















        {{-- ========================================================= --}}

        {{-- RIGHT / LOGIN                                             --}}

        {{-- ========================================================= --}}

        <div class="login-panel">

            <div class="auth-shell">

                <div class="auth-brand">

                    <div class="auth-brand-mark">

                        M

                    </div>

                    <div class="auth-brand-copy">

                        <strong>

                            Mashal

                        </strong>

                        <span>

                            Automotive

                        </span>

                    </div>

                </div>

















                <span class="auth-kicker">

                    Member access

                </span>

                <h1 class="auth-title">

                    Inloggen

                </h1>

                <p class="auth-subtitle">
                    Log veilig in met Google, GitHub, Facebook, een eenmalige e-mailcode
                    of gebruik je e-mailadres en wachtwoord voor jouw Mashal-account.
                </p>

















                {{-- SUCCESS --}}

                @if (session('success'))

                    <div class="auth-message success">

                        {{ session('success') }}

                    </div>

                @endif









                {{-- SESSION ERROR --}}

                @if (session('error'))

                    <div class="auth-message error">

                        {{ session('error') }}

                    </div>

                @endif









                {{-- ERRORS --}}

                @if ($errors->any())

                    <div class="auth-message error">

                        <strong>

                            Inloggen is niet gelukt.

                        </strong>

                        <ul>

                            @foreach ($errors->all() as $error)

                                <li>

                                    {{ $error }}

                                </li>

                            @endforeach

                        </ul>

                    </div>

                @endif

























                {{-- E-MAILCODE LOGIN --}}
                <div class="email-code-auth">
                    <div class="email-code-head">
                        <div class="email-code-icon" aria-hidden="true">
                            @
                        </div>

                        <div class="email-code-copy">
                            <strong>Inloggen met e-mailcode</strong>
                            <span>Ontvang een eenmalige 6-cijferige code. De code is 5 minuten geldig.</span>
                        </div>
                    </div>

                    <form
                        class="email-code-form"
                        method="POST"
                        action="{{ route('email-login.send') }}"
                    >
                        @csrf

                        <input
                            class="email-code-input"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="naam@example.com"
                            autocomplete="email"
                            aria-label="E-mailadres voor e-mailcode"
                            required
                        >

                        <button
                            class="email-code-submit"
                            type="submit"
                        >
                            Stuur code
                        </button>
                    </form>

                    <div class="email-code-note">
                        Je wachtwoord is niet nodig. Na het aanvragen vul je de ontvangen code in om veilig in te loggen.
                    </div>
                </div>

                <div class="oauth-divider">
                    Of ga verder met
                </div>

                {{-- GOOGLE OAUTH --}}

                <div class="google-auth">

                    <a

                        class="google-auth-button"

                        href="{{ route('google.redirect') }}"

                        aria-label="Doorgaan met Google"

                    >

                        <span

                            class="google-auth-icon"

                            aria-hidden="true"

                        >

                            <svg

                                viewBox="0 0 18 18"

                                xmlns="http://www.w3.org/2000/svg"

                            >

                                <path

                                    fill="#4285F4"

                                    d="M17.64 9.205c0-.638-.057-1.252-.164-1.841H9v3.483h4.844a4.14 4.14 0 0 1-1.797 2.715v2.258h2.909c1.703-1.568 2.684-3.878 2.684-6.615z"

                                />

                                <path

                                    fill="#34A853"

                                    d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.909-2.258c-.806.54-1.835.859-3.047.859-2.344 0-4.328-1.585-5.037-3.715H.955v2.332A9 9 0 0 0 9 18z"

                                />

                                <path

                                    fill="#FBBC05"

                                    d="M3.963 10.706A5.41 5.41 0 0 1 3.682 9c0-.592.102-1.168.281-1.706V4.962H.955A9 9 0 0 0 0 9c0 1.453.347 2.828.955 4.038l3.008-2.332z"

                                />

                                <path

                                    fill="#EA4335"

                                    d="M9 3.579c1.321 0 2.507.454 3.44 1.346l2.581-2.581C13.463.892 11.426 0 9 0A9 9 0 0 0 .955 4.962l3.008 2.332C4.672 5.164 6.656 3.579 9 3.579z"

                                />

                            </svg>

                        </span>

                        <span class="google-auth-copy">

                            <strong>

                                Doorgaan met Google

                            </strong>

                            <small>

                                Veilig inloggen met je Google-account

                            </small>

                        </span>

                    </a>

                    <div class="google-auth-note">

                        Je wordt doorgestuurd naar <strong>Google</strong>.

                        Mashal ontvangt nooit je Google-wachtwoord.

                    </div>

                </div>

                {{-- GITHUB OAUTH --}}

                <div class="github-auth">

                    <a

                        class="github-auth-button"

                        href="{{ route('github.redirect') }}"

                        aria-label="Doorgaan met GitHub"

                    >

                        <span

                            class="github-auth-icon"

                            aria-hidden="true"

                        >

                            <svg

                                viewBox="0 0 24 24"

                                xmlns="http://www.w3.org/2000/svg"

                                role="img"

                            >

                                <path d="M12 .7a11.5 11.5 0 0 0-3.64 22.41c.58.11.79-.25.79-.56v-2.2c-3.22.7-3.9-1.37-3.9-1.37-.52-1.34-1.29-1.69-1.29-1.69-1.05-.72.08-.71.08-.71 1.17.08 1.78 1.2 1.78 1.2 1.04 1.78 2.72 1.27 3.38.97.1-.75.41-1.27.74-1.56-2.57-.29-5.27-1.29-5.27-5.68 0-1.25.45-2.28 1.19-3.08-.12-.29-.52-1.47.11-3.05 0 0 .97-.31 3.16 1.18A10.97 10.97 0 0 1 12 6.17c.98 0 1.96.13 2.87.39 2.19-1.49 3.16-1.18 3.16-1.18.63 1.58.23 2.76.11 3.05.74.8 1.19 1.83 1.19 3.08 0 4.41-2.71 5.38-5.29 5.67.42.36.79 1.07.79 2.16v3.21c0 .31.21.67.8.56A11.5 11.5 0 0 0 12 .7Z"/>

                            </svg>

                        </span>

                        <span class="github-auth-copy">

                            <strong>

                                Doorgaan met GitHub

                            </strong>

                            <small>

                                Veilig inloggen met je GitHub-account

                            </small>

                        </span>

                    </a>

                    <div class="github-auth-note">

                        Je wordt doorgestuurd naar <strong>GitHub</strong>.

                        Mashal ontvangt nooit je GitHub-wachtwoord.

                    </div>

                </div>





                {{-- FACEBOOK OAUTH --}}

                <div class="facebook-auth">

                    <a

                        class="facebook-auth-button"

                        href="{{ route('facebook.redirect') }}"

                        aria-label="Doorgaan met Facebook"

                    >

                        <span

                            class="facebook-auth-icon"

                            aria-hidden="true"

                        >

                            <svg

                                viewBox="0 0 24 24"

                                xmlns="http://www.w3.org/2000/svg"

                                role="img"

                            >

                                <path d="M13.5 8H16V5h-2.5C10.7 5 9 6.7 9 9.5V12H6v3h3v7h3.5v-7H16l.5-3h-4V9.8c0-1.2.4-1.8 1-1.8Z"/>

                            </svg>

                        </span>

                        <span class="facebook-auth-copy">

                            <strong>

                                Doorgaan met Facebook

                            </strong>

                            <small>

                                Veilig inloggen met je Facebook-account

                            </small>

                        </span>

                    </a>

                    <div class="facebook-auth-note">

                        Je wordt doorgestuurd naar <strong>Facebook</strong>.

                        Mashal ontvangt nooit je Facebook-wachtwoord.

                    </div>

                </div>











                <div class="oauth-divider">

                    Of gebruik je wachtwoord

                </div>









                {{-- FORM --}}

                <form

                    method="POST"

                    action="{{ route('login.submit') }}"

                >

                    @csrf

















                    {{-- EMAIL --}}

                    <div class="auth-field">

                        <div class="auth-label">

                            <label for="email">

                                E-mailadres

                            </label>

                            @error('email')

                                <span class="field-error">

                                    {{ $message }}

                                </span>

                            @enderror

                        </div>

















                        <div class="auth-input-wrap">

                            <input

                                class="auth-input"

                                id="email"

                                type="email"

                                name="email"

                                value="{{ old('email') }}"

                                placeholder="naam@example.com"

                                autocomplete="email"

                                required

                                autofocus

                            >

                            <span class="auth-input-icon">

                                @

                            </span>

                        </div>

                    </div>

















                    {{-- PASSWORD --}}

                    <div class="auth-field">

                        <div class="auth-label">

                            <label for="password">

                                Wachtwoord

                            </label>

                            @error('password')

                                <span class="field-error">

                                    {{ $message }}

                                </span>

                            @enderror

                        </div>

















                        <div class="auth-input-wrap">

                            <input

                                class="auth-input"

                                id="password"

                                type="password"

                                name="password"

                                placeholder="Vul je wachtwoord in"

                                autocomplete="current-password"

                                required

                            >

                            <button

                                class="password-toggle"

                                type="button"

                                data-toggle-password="password"

                                aria-label="Wachtwoord tonen of verbergen"

                            >

                                Tonen

                            </button>

                        </div>

                    </div>

















                    {{-- OPTIONS --}}

                    <div class="auth-options">

                        <label

                            class="remember-label"

                            for="remember"

                        >

                            <input

                                id="remember"

                                type="checkbox"

                                name="remember"

                                value="1"

                                {{ old('remember') ? 'checked' : '' }}

                            >

                            <span>

                                Onthoud mij

                            </span>

                        </label>

















                        <a

                            class="auth-link"

                            href="{{ route('password.request') }}"

                        >

                            Wachtwoord vergeten?

                        </a>

                    </div>

















                    {{-- SUBMIT --}}

                    <button

                        class="auth-submit"

                        type="submit"

                    >

                        Inloggen

                    </button>

                </form>

















                <div class="auth-divider">

                    Nieuw bij Mashal?

                </div>

















                <div class="register-card">

                    <div class="register-card-copy">

                        <strong>

                            Nog geen account?

                        </strong>

                        <span>

                            Maak gratis een account aan

                            en beheer daarna jouw selectie en bestellingen.

                        </span>

                    </div>

















                    <a

                        class="register-card-link"

                        href="{{ route('register') }}"

                    >

                        Registreren

                    </a>

                </div>

















                <div class="security-note">

                    <span class="security-note-mark">

                        ✓

                    </span>

                    <span>

                        Mashal vraagt je nooit om je wachtwoord

                        via e-mail, chat of telefoon te delen.

                    </span>

                </div>

            </div>

        </div>

    </div>

</section>

@endsection

















@push('scripts')

<script>

    document.addEventListener('DOMContentLoaded', function () {

        document

            .querySelectorAll('[data-toggle-password]')

            .forEach(function (button) {

                button.addEventListener('click', function () {

                    const inputId =

                        button.getAttribute('data-toggle-password');

                    const input =

                        document.getElementById(inputId);

                    if (!input) {

                        return;

                    }

                    const isHidden =

                        input.type === 'password';

                    input.type =

                        isHidden ? 'text' : 'password';

                    button.textContent =

                        isHidden ? 'Verberg' : 'Tonen';

                });

            });

    });

</script>

@endpush