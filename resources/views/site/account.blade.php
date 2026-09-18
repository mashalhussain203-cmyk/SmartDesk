@extends('layouts.site-layout')

@section('title', 'Mashal | Mijn account')

@push('styles')

<style>

    .account-page {

        position: relative;

        overflow: hidden;

        padding: 76px 0 112px;

        background:

            radial-gradient(

                circle at 10% 8%,

                rgba(215, 164, 95, .075),

                transparent 24rem

            ),

            radial-gradient(

                circle at 88% 18%,

                rgba(255, 255, 255, .025),

                transparent 28rem

            ),

            #08090b;

    }

    .account-page::before {

        content: "MASHAL";

        position: absolute;

        right: -50px;

        top: 42px;

        color: rgba(255, 255, 255, .013);

        font-size: clamp(125px, 18vw, 290px);

        font-weight: 950;

        line-height: .8;

        letter-spacing: -.08em;

        pointer-events: none;

        user-select: none;

    }

    .account-shell {

        position: relative;

        z-index: 2;

        width: min(100% - 48px, 1240px);

        margin-inline: auto;

    }

    /* ========================================================= */

    /* HERO                                                       */

    /* ========================================================= */

    .account-hero {

        display: grid;

        grid-template-columns: minmax(0, 1fr) auto;

        align-items: end;

        gap: 40px;

        margin-bottom: 32px;

    }

    .account-kicker {

        display: inline-flex;

        align-items: center;

        gap: 10px;

        margin-bottom: 14px;

        color: #d7a45f;

        font-size: 9px;

        font-weight: 900;

        letter-spacing: .22em;

        text-transform: uppercase;

    }

    .account-kicker::before {

        content: "";

        width: 30px;

        height: 1px;

        background: #d7a45f;

    }

    .account-title {

        margin: 0;

        color: #ffffff;

        font-size: clamp(48px, 6vw, 82px);

        line-height: .94;

        letter-spacing: -.065em;

        font-weight: 950;

    }

    .account-title span {

        color: #efc985;

    }

    .account-intro {

        max-width: 700px;

        margin: 18px 0 0;

        color: #858b92;

        font-size: 14px;

        line-height: 1.85;

    }

    .account-status-card {

        min-width: 230px;

        padding: 18px 20px;

        border: 1px solid rgba(255, 255, 255, .09);

        border-radius: 18px;

        background:

            linear-gradient(

                145deg,

                rgba(255, 255, 255, .055),

                rgba(255, 255, 255, .018)

            );

        box-shadow: 0 16px 40px rgba(0, 0, 0, .17);

        backdrop-filter: blur(16px);

    }

    .account-status-card small {

        display: block;

        margin-bottom: 5px;

        color: #747a81;

        font-size: 8px;

        font-weight: 900;

        letter-spacing: .14em;

        text-transform: uppercase;

    }

    .account-status-card strong {

        display: block;

        color: #ffffff;

        font-size: 16px;

        line-height: 1.3;

    }

    .account-status-card span {

        display: block;

        margin-top: 5px;

        color: #71777e;

        font-size: 10px;

        line-height: 1.5;

    }

    /* ========================================================= */

    /* MESSAGES                                                   */

    /* ========================================================= */

    .account-message {

        margin-bottom: 22px;

        padding: 14px 16px;

        border-radius: 14px;

        font-size: 12px;

        line-height: 1.6;

        backdrop-filter: blur(12px);

    }

    .account-message.success {

        border: 1px solid rgba(91, 214, 149, .22);

        background: rgba(91, 214, 149, .08);

        color: #a9efc8;

    }

    .account-message.error {

        border: 1px solid rgba(241, 123, 123, .22);

        background: rgba(241, 123, 123, .08);

        color: #ffc1c1;

    }

    .account-message ul {

        margin: 8px 0 0 18px;

        padding: 0;

    }

    /* ========================================================= */

    /* DASHBOARD LAYOUT                                           */

    /* ========================================================= */

    .account-layout {

        display: grid;

        grid-template-columns: 300px minmax(0, 1fr);

        gap: 24px;

        align-items: start;

    }

    .account-sidebar {

        position: sticky;

        top: 106px;

        display: grid;

        gap: 16px;

    }

    .profile-card,

    .quick-card,

    .account-panel {

        border: 1px solid rgba(255, 255, 255, .08);

        border-radius: 24px;

        background:

            linear-gradient(

                145deg,

                rgba(255, 255, 255, .043),

                rgba(255, 255, 255, .015)

            );

        box-shadow: 0 18px 50px rgba(0, 0, 0, .20);

    }

    .profile-card {

        padding: 24px;

    }

    .profile-avatar {

        width: 68px;

        height: 68px;

        margin-bottom: 18px;

        display: grid;

        place-items: center;

        border-radius: 20px;

        background:

            linear-gradient(

                145deg,

                #f1cc8b,

                #b9803e

            );

        color: #15110c;

        font-size: 25px;

        font-weight: 950;

        box-shadow: 0 16px 38px rgba(215, 164, 95, .22);

    }

    .profile-avatar {
        position: relative;
        overflow: hidden;
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
        border-radius: inherit;
    }

    .profile-avatar.has-image {
        background: #111419;
        color: transparent;
    }

    .profile-photo-editor {
        grid-column: 1 / -1;
        margin-bottom: 4px;
        padding: 18px;
        display: grid;
        grid-template-columns: 92px minmax(0, 1fr);
        gap: 18px;
        align-items: center;
        border: 1px solid rgba(215, 164, 95, .13);
        border-radius: 18px;
        background:
            linear-gradient(
                145deg,
                rgba(215, 164, 95, .045),
                rgba(255, 255, 255, .018)
            );
    }

    .profile-photo-preview {
        width: 92px;
        height: 92px;
        display: grid;
        place-items: center;
        overflow: hidden;
        border: 1px solid rgba(215, 164, 95, .18);
        border-radius: 24px;
        background:
            linear-gradient(
                145deg,
                #f1cc8b,
                #b9803e
            );
        color: #15110c;
        font-size: 28px;
        font-weight: 950;
        box-shadow: 0 16px 38px rgba(215, 164, 95, .18);
    }

    .profile-photo-preview img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
    }

    .profile-photo-content {
        min-width: 0;
    }

    .profile-photo-title {
        margin: 0;
        color: #f1eee8;
        font-size: 13px;
        font-weight: 900;
    }

    .profile-photo-text {
        margin: 5px 0 14px;
        color: #737980;
        font-size: 10px;
        line-height: 1.65;
    }

    .profile-photo-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .profile-photo-input {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        clip: rect(0 0 0 0);
        white-space: nowrap;
        clip-path: inset(50%);
    }

    .profile-photo-button {
        min-height: 40px;
        padding: 0 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border: 1px solid rgba(215, 164, 95, .20);
        border-radius: 999px;
        background: rgba(215, 164, 95, .065);
        color: #e2b66f;
        font-size: 9px;
        font-weight: 900;
        letter-spacing: .03em;
        cursor: pointer;
        transition:
            transform .2s ease,
            border-color .2s ease,
            background .2s ease;
    }

    .profile-photo-button:hover {
        transform: translateY(-1px);
        border-color: rgba(215, 164, 95, .36);
        background: rgba(215, 164, 95, .11);
    }

    .profile-photo-remove {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #8a8f95;
        font-size: 9px;
        cursor: pointer;
    }

    .profile-photo-remove input {
        width: 14px;
        height: 14px;
        accent-color: #d7a45f;
    }

    .profile-photo-filename {
        margin-top: 9px;
        color: #656b72;
        font-size: 9px;
        line-height: 1.5;
        word-break: break-word;
    }

    .profile-photo-error {
        margin-top: 8px;
        color: #f3a1a1;
        font-size: 10px;
        font-weight: 700;
    }

    .profile-card h2 {

        margin: 0;

        color: #ffffff;

        font-size: 22px;

        letter-spacing: -.035em;

        word-break: break-word;

    }

    .profile-email {

        margin-top: 5px;

        color: #71777e;

        font-size: 11px;

        word-break: break-word;

    }

    .profile-status {

        margin-top: 18px;

        padding-top: 16px;

        border-top: 1px solid rgba(255,255,255,.07);

    }

    .status-pill {

        display: inline-flex;

        align-items: center;

        gap: 7px;

        padding: 8px 10px;

        border-radius: 999px;

        font-size: 9px;

        font-weight: 900;

        letter-spacing: .05em;

        text-transform: uppercase;

    }

    .status-pill.verified {

        border: 1px solid rgba(91, 214, 149, .18);

        background: rgba(91, 214, 149, .06);

        color: #9ce7bc;

    }

    .status-pill.pending {

        border: 1px solid rgba(242, 198, 109, .18);

        background: rgba(242, 198, 109, .06);

        color: #e9c674;

    }

    .quick-card {

        padding: 16px;

    }

    .quick-card-title {

        margin-bottom: 10px;

        color: #666c73;

        font-size: 8px;

        font-weight: 900;

        letter-spacing: .15em;

        text-transform: uppercase;

    }

    .quick-links {

        display: grid;

        gap: 5px;

    }

    .quick-link {

        padding: 10px 12px;

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 12px;

        border-radius: 12px;

        color: #a6a7a4;

        text-decoration: none;

        font-size: 11px;

        font-weight: 800;

        transition:

            color .2s ease,

            background .2s ease,

            transform .2s ease;

    }

    .quick-link:hover {

        color: #efc985;

        background: rgba(215, 164, 95, .055);

        transform: translateX(2px);

    }

    /* ========================================================= */

    /* MAIN CONTENT                                               */

    /* ========================================================= */

    .account-content {

        display: grid;

        gap: 20px;

    }

    .account-panel {

        padding: 28px;

    }

    .panel-head {

        display: flex;

        align-items: flex-start;

        justify-content: space-between;

        gap: 20px;

        margin-bottom: 24px;

    }

    .panel-kicker {

        display: block;

        margin-bottom: 6px;

        color: #9e7442;

        font-size: 8px;

        font-weight: 900;

        letter-spacing: .16em;

        text-transform: uppercase;

    }

    .panel-title {

        margin: 0;

        color: #ffffff;

        font-size: 25px;

        letter-spacing: -.04em;

    }

    .panel-copy {

        max-width: 650px;

        margin: 8px 0 0;

        color: #747a81;

        font-size: 12px;

        line-height: 1.8;

    }

    .panel-badge {

        flex-shrink: 0;

        padding: 8px 10px;

        border: 1px solid rgba(215, 164, 95, .16);

        border-radius: 999px;

        background: rgba(215, 164, 95, .055);

        color: #d7aa69;

        font-size: 8px;

        font-weight: 900;

        letter-spacing: .09em;

        text-transform: uppercase;

    }

    /* ========================================================= */

    /* FORMS                                                      */

    /* ========================================================= */

    .account-form-grid {

        display: grid;

        grid-template-columns: repeat(2, minmax(0, 1fr));

        gap: 16px;

    }

    .account-field {

        margin-bottom: 18px;

    }

    .account-field.full {

        grid-column: 1 / -1;

    }

    .account-label {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 14px;

        margin-bottom: 8px;

    }

    .account-label label {

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

    .input-wrap {

        position: relative;

    }

    .account-input {

        width: 100%;

        height: 54px;

        padding: 0 46px 0 15px;

        border: 1px solid rgba(255, 255, 255, .10);

        border-radius: 14px;

        outline: none;

        background: rgba(255, 255, 255, .035);

        color: #ffffff;

        font-size: 13px;

        transition:

            border-color .2s ease,

            background .2s ease,

            box-shadow .2s ease;

    }

    .account-input::placeholder {

        color: #565c63;

    }

    .account-input:focus {

        border-color: rgba(215, 164, 95, .46);

        background: rgba(215, 164, 95, .035);

        box-shadow: 0 0 0 4px rgba(215, 164, 95, .065);

    }

    .field-icon {

        position: absolute;

        right: 15px;

        top: 50%;

        transform: translateY(-50%);

        color: #6c7279;

        font-size: 12px;

        pointer-events: none;

    }

    .password-toggle {

        position: absolute;

        right: 8px;

        top: 50%;

        transform: translateY(-50%);

        min-width: 42px;

        height: 34px;

        padding: 0 8px;

        border: 0;

        border-radius: 10px;

        background: transparent;

        color: #7c8288;

        font-size: 9px;

        font-weight: 850;

        cursor: pointer;

        transition: color .2s ease, background .2s ease;

    }

    .password-toggle:hover {

        color: #efc985;

        background: rgba(215, 164, 95, .06);

    }

    .form-note {

        margin-top: 4px;

        padding: 14px 15px;

        display: flex;

        align-items: flex-start;

        gap: 10px;

        border: 1px solid rgba(215, 164, 95, .13);

        border-radius: 14px;

        background: rgba(215, 164, 95, .045);

        color: #77716a;

        font-size: 10px;

        line-height: 1.7;

    }

    .form-note-mark {

        flex-shrink: 0;

        width: 24px;

        height: 24px;

        display: grid;

        place-items: center;

        border: 1px solid rgba(215, 164, 95, .16);

        border-radius: 50%;

        color: #d3a463;

        font-size: 9px;

        font-weight: 900;

    }

    .account-submit {

        min-height: 50px;

        padding: 0 20px;

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 9px;

        border: 0;

        border-radius: 999px;

        background:

            linear-gradient(

                135deg,

                #f1cc8b,

                #ca914c

            );

        color: #14100b;

        font-size: 10px;

        font-weight: 950;

        letter-spacing: .03em;

        cursor: pointer;

        box-shadow: 0 14px 34px rgba(215, 164, 95, .18);

        transition:

            transform .2s ease,

            box-shadow .2s ease;

    }

    .account-submit:hover {

        transform: translateY(-2px);

        box-shadow: 0 22px 48px rgba(215, 164, 95, .28);

    }

    /* ========================================================= */

    /* VERIFICATION                                               */

    /* ========================================================= */

    .verification-box {

        padding: 19px;

        display: flex;

        align-items: flex-start;

        justify-content: space-between;

        gap: 22px;

        border-radius: 17px;

    }

    .verification-box.verified {

        border: 1px solid rgba(91, 214, 149, .16);

        background: rgba(91, 214, 149, .05);

    }

    .verification-box.pending {

        border: 1px solid rgba(242, 198, 109, .16);

        background: rgba(242, 198, 109, .05);

    }

    .verification-copy {

        display: flex;

        align-items: flex-start;

        gap: 12px;

    }

    .verification-icon {

        flex-shrink: 0;

        width: 38px;

        height: 38px;

        display: grid;

        place-items: center;

        border: 1px solid rgba(255,255,255,.10);

        border-radius: 50%;

        color: #d7aa69;

        font-size: 13px;

        font-weight: 900;

    }

    .verification-box.verified .verification-icon {

        color: #9ce7bc;

    }

    .verification-copy strong {

        display: block;

        margin-bottom: 4px;

        color: #dcdad5;

        font-size: 12px;

    }

    .verification-copy p {

        margin: 0;

        color: #71777e;

        font-size: 10px;

        line-height: 1.7;

    }

    .verification-link {

        flex-shrink: 0;

        min-height: 40px;

        padding: 0 14px;

        display: inline-flex;

        align-items: center;

        justify-content: center;

        border: 1px solid rgba(215, 164, 95, .18);

        border-radius: 999px;

        background: rgba(215, 164, 95, .055);

        color: #dfb36d;

        text-decoration: none;

        font-size: 9px;

        font-weight: 900;

        transition:

            transform .2s ease,

            border-color .2s ease,

            background .2s ease;

    }

    .verification-link:hover {

        transform: translateY(-1px);

        border-color: rgba(215, 164, 95, .34);

        background: rgba(215, 164, 95, .10);

    }

    /* ========================================================= */

    /* ACCOUNT INFO                                               */

    /* ========================================================= */

    .info-grid {

        display: grid;

        grid-template-columns: repeat(4, minmax(0, 1fr));

        gap: 12px;

    }

    .info-card {

        padding: 16px;

        border: 1px solid rgba(255,255,255,.07);

        border-radius: 15px;

        background: rgba(255,255,255,.022);

    }

    .info-card small {

        display: block;

        margin-bottom: 6px;

        color: #656b72;

        font-size: 7px;

        font-weight: 900;

        letter-spacing: .13em;

        text-transform: uppercase;

    }

    .info-card strong {

        display: block;

        color: #d9d7d2;

        font-size: 12px;

        line-height: 1.5;

        word-break: break-word;

    }

    /* ========================================================= */

    /* ORDERS                                                     */

    /* ========================================================= */

    .orders-list {

        display: grid;

        gap: 12px;

    }

    .order-card {

        padding: 18px;

        display: grid;

        grid-template-columns: minmax(0, 1fr) auto;

        gap: 20px;

        align-items: center;

        border: 1px solid rgba(255,255,255,.07);

        border-radius: 18px;

        background: rgba(255,255,255,.022);

        transition:

            transform .22s ease,

            border-color .22s ease,

            background .22s ease;

    }

    .order-card:hover {

        transform: translateY(-3px);

        border-color: rgba(215, 164, 95, .18);

        background: rgba(215, 164, 95, .022);

    }

    .order-number {

        color: #ffffff;

        font-size: 16px;

        font-weight: 850;

        letter-spacing: -.02em;

    }

    .order-meta {

        margin-top: 7px;

        display: flex;

        align-items: center;

        gap: 14px;

        flex-wrap: wrap;

        color: #71777e;

        font-size: 10px;

    }

    .order-meta strong {

        color: #c8c6c1;

    }

    .order-status {

        padding: 8px 11px;

        border-radius: 999px;

        font-size: 8px;

        font-weight: 900;

        letter-spacing: .08em;

        text-transform: uppercase;

        white-space: nowrap;

    }

    .order-status.status-placed {

        border: 1px solid rgba(215,164,95,.18);

        background: rgba(215,164,95,.055);

        color: #dcb06c;

    }

    .order-status.status-paid,

    .order-status.status-completed {

        border: 1px solid rgba(91,214,149,.18);

        background: rgba(91,214,149,.055);

        color: #9ce7bc;

    }

    .order-status.status-processing {

        border: 1px solid rgba(113,166,255,.18);

        background: rgba(113,166,255,.055);

        color: #9fc2ff;

    }

    .order-status.status-shipped {

        border: 1px solid rgba(177,132,255,.18);

        background: rgba(177,132,255,.055);

        color: #c2a2ff;

    }

    .order-status.status-cancelled {

        border: 1px solid rgba(241,123,123,.18);

        background: rgba(241,123,123,.055);

        color: #f5a1a1;

    }

    .order-status.status-default {

        border: 1px solid rgba(255,255,255,.10);

        background: rgba(255,255,255,.035);

        color: #a6abb1;

    }

    .orders-empty {

        padding: 34px 22px;

        text-align: center;

        border: 1px dashed rgba(255,255,255,.10);

        border-radius: 18px;

        background: rgba(255,255,255,.015);

    }

    .orders-empty-mark {

        width: 50px;

        height: 50px;

        margin: 0 auto 14px;

        display: grid;

        place-items: center;

        border: 1px solid rgba(215,164,95,.16);

        border-radius: 50%;

        background: rgba(215,164,95,.055);

        color: #dfb36d;

        font-weight: 900;

    }

    .orders-empty strong {

        display: block;

        color: #dedcd7;

        font-size: 14px;

    }

    .orders-empty p {

        max-width: 500px;

        margin: 7px auto 18px;

        color: #737980;

        font-size: 10px;

        line-height: 1.7;

    }

    /* ========================================================= */

    /* SECURITY STRIP                                             */

    /* ========================================================= */

    .account-security-strip {

        margin-top: 26px;

        display: grid;

        grid-template-columns: repeat(3, 1fr);

        border: 1px solid rgba(255,255,255,.07);

        border-radius: 20px;

        overflow: hidden;

        background: rgba(255,255,255,.02);

    }

    .security-item {

        padding: 20px;

        border-right: 1px solid rgba(255,255,255,.06);

    }

    .security-item:last-child {

        border-right: 0;

    }

    .security-item small {

        display: block;

        margin-bottom: 5px;

        color: #9d7547;

        font-size: 8px;

        font-weight: 900;

        letter-spacing: .14em;

        text-transform: uppercase;

    }

    .security-item strong {

        display: block;

        color: #d4d3cf;

        font-size: 11px;

        line-height: 1.5;

    }

    .security-item span {

        display: block;

        margin-top: 4px;

        color: #646a71;

        font-size: 9px;

        line-height: 1.6;

    }

    /* ========================================================= */

    /* RESPONSIVE                                                 */

    /* ========================================================= */

    @media (max-width: 1060px) {

        .account-hero,

        .account-layout {

            grid-template-columns: 1fr;

        }

        .account-status-card {

            width: fit-content;

        }

        .account-sidebar {

            position: static;

            grid-template-columns: 1fr 1fr;

        }

    }

    @media (max-width: 820px) {

        .account-form-grid,

        .info-grid {

            grid-template-columns: 1fr 1fr;

        }

        .account-security-strip {

            grid-template-columns: 1fr;

        }

        .security-item {

            border-right: 0;

            border-bottom: 1px solid rgba(255,255,255,.06);

        }

        .security-item:last-child {

            border-bottom: 0;

        }

    }

    @media (max-width: 640px) {

        .account-page {

            padding: 56px 0 80px;

        }

        .account-shell {

            width: min(100% - 32px, 1240px);

        }

        .account-sidebar {

            grid-template-columns: 1fr;

        }

        .account-panel {

            padding: 22px;

            border-radius: 22px;

        }

        .panel-head {

            flex-direction: column;

        }

        .account-form-grid,

        .info-grid {

            grid-template-columns: 1fr;

        }

        .account-field.full {

            grid-column: auto;

        }

        .verification-box,

        .order-card {

            grid-template-columns: 1fr;

        }

        .verification-box {

            align-items: stretch;

            flex-direction: column;

        }

        .verification-link {

            width: 100%;

        }

        .profile-photo-editor {
            grid-template-columns: 1fr;
        }

        .profile-photo-preview {
            width: 82px;
            height: 82px;
        }

        .profile-photo-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .profile-photo-button {
            width: 100%;
        }

    }

</style>

@endpush



@section('content')

<section class="account-page">

    <div class="account-shell">

        {{-- ========================================================= --}}

        {{-- HERO                                                       --}}

        {{-- ========================================================= --}}

        <header class="account-hero">

            <div>

                <span class="account-kicker">

                    Mashal Member Area

                </span>

                <h1 class="account-title">

                    Welkom terug,

                    <span>{{ $user->name }}.</span>

                </h1>

                <p class="account-intro">

                    Beheer je profiel, beveiliging, e-mailverificatie

                    en bestellingen vanuit één persoonlijke Mashal-omgeving.

                </p>

            </div>



            <div class="account-status-card">

                <small>

                    Accountstatus

                </small>

                <strong>

                    Actief

                </strong>

                <span>

                    Lid sinds

                    {{ optional($user->created_at)->format('d-m-Y') }}

                </span>

            </div>

        </header>



        {{-- ========================================================= --}}

        {{-- MESSAGES                                                   --}}

        {{-- ========================================================= --}}

        @if (session('success'))

            <div class="account-message success">

                <strong>

                    Gelukt.

                </strong>

                {{ session('success') }}

            </div>

        @endif



        @if (session('error'))

            <div class="account-message error">
                <strong>
                    Er ging iets mis.
                </strong>
                {{ session('error') }}
            </div>

        @endif


        @if ($errors->any())

            <div class="account-message error">

                <strong>

                    Er ging iets mis.

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



        {{-- ========================================================= --}}

        {{-- DASHBOARD                                                  --}}

        {{-- ========================================================= --}}

        <div class="account-layout">

            {{-- ===================================================== --}}

            {{-- SIDEBAR                                               --}}

            {{-- ===================================================== --}}

            <aside class="account-sidebar">

                <div class="profile-card">

                    @if ($user->avatarUrl())
                        <div class="profile-avatar has-image">
                            <img
                                src="{{ $user->avatarUrl() }}"
                                alt="Profielfoto van {{ $user->name }}"
                            >
                        </div>
                    @else
                        <div class="profile-avatar">
                            {{ $user->initials() }}
                        </div>
                    @endif

                    <h2>

                        {{ $user->name }}

                    </h2>

                    <div class="profile-email">

                        {{ $user->email }}

                    </div>



                    <div class="profile-status">

                        @if ($user->email_verified_at)

                            <span class="status-pill verified">

                                ✓ E-mail geverifieerd

                            </span>

                        @else

                            <span class="status-pill pending">

                                ! Verificatie vereist

                            </span>

                        @endif

                    </div>

                </div>



                <div class="quick-card">

                    <div class="quick-card-title">

                        Snel navigeren

                    </div>

                    <div class="quick-links">

                        <a

                            class="quick-link"

                            href="#profile"

                        >

                            <span>

                                Persoonlijke gegevens

                            </span>

                            <span>

                                →

                            </span>

                        </a>

                        <a

                            class="quick-link"

                            href="{{ route('security.index') }}"

                        >

                            <span>

                                Beveiliging

                            </span>

                            <span>

                                →

                            </span>

                        </a>

                        <a

                            class="quick-link"

                            href="{{ route('favorites.index') }}"

                        >

                            <span>

                                Mijn favorieten

                            </span>

                            <span>

                                →

                            </span>

                        </a>

                        <a

                            class="quick-link"

                            href="#orders"

                        >

                            <span>

                                Bestellingen

                            </span>

                            <span>

                                →

                            </span>

                        </a>

                        <a

                            class="quick-link"

                            href="{{ route('catalog') }}"

                        >

                            <span>

                                Collectie bekijken

                            </span>

                            <span>

                                →

                            </span>

                        </a>

                    </div>

                </div>

            </aside>



            {{-- ===================================================== --}}

            {{-- CONTENT                                               --}}

            {{-- ===================================================== --}}

            <div class="account-content">

                {{-- ================================================= --}}

                {{-- PERSONAL INFORMATION                              --}}

                {{-- ================================================= --}}

                <section

                    class="account-panel"

                    id="profile"

                >

                    <div class="panel-head">

                        <div>

                            <span class="panel-kicker">

                                01 / Profile

                            </span>

                            <h2 class="panel-title">

                                Persoonlijke gegevens

                            </h2>

                            <p class="panel-copy">

                                Beheer je profielfoto, naam en e-mailadres.

                                Wijzigingen aan je e-mailadres vereisen

                                opnieuw verificatie.

                            </p>

                        </div>

                        <span class="panel-badge">

                            Personal

                        </span>

                    </div>



                    <form
                        method="POST"
                        action="{{ route('account.update') }}"
                        enctype="multipart/form-data"
                    >

                        @csrf

                        @method('PUT')



                        <div class="account-form-grid">

                            {{-- PROFILE PHOTO --}}
                            <div class="profile-photo-editor">
                                <div
                                    class="profile-photo-preview"
                                    id="profilePhotoPreview"
                                >
                                    @if ($user->avatarUrl())
                                        <img
                                            id="profilePhotoPreviewImage"
                                            src="{{ $user->avatarUrl() }}"
                                            alt="Huidige profielfoto"
                                        >
                                    @else
                                        <span id="profilePhotoPreviewFallback">
                                            {{ $user->initials() }}
                                        </span>
                                    @endif
                                </div>

                                <div class="profile-photo-content">
                                    <h3 class="profile-photo-title">
                                        Profielfoto
                                    </h3>

                                    <p class="profile-photo-text">
                                        Upload een JPG, PNG of WEBP-afbeelding van maximaal 5 MB.
                                        Jouw eigen profielfoto krijgt voorrang op een Google-,
                                        GitHub- of Facebook-avatar.
                                    </p>

                                    <div class="profile-photo-actions">
                                        <label
                                            class="profile-photo-button"
                                            for="profile_photo"
                                        >
                                            <span aria-hidden="true">＋</span>
                                            Kies profielfoto
                                        </label>

                                        <input
                                            class="profile-photo-input"
                                            id="profile_photo"
                                            type="file"
                                            name="profile_photo"
                                            accept="image/jpeg,image/png,image/webp"
                                        >

                                        @if ($user->hasProfilePhoto())
                                            <label class="profile-photo-remove">
                                                <input
                                                    id="remove_profile_photo"
                                                    type="checkbox"
                                                    name="remove_profile_photo"
                                                    value="1"
                                                    {{ old('remove_profile_photo') ? 'checked' : '' }}
                                                >
                                                <span>
                                                    Eigen profielfoto verwijderen
                                                </span>
                                            </label>
                                        @endif
                                    </div>

                                    <div
                                        class="profile-photo-filename"
                                        id="profilePhotoFilename"
                                    >
                                        Geen nieuw bestand geselecteerd.
                                    </div>

                                    @error('profile_photo')
                                        <div class="profile-photo-error">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            {{-- NAME --}}

                            <div class="account-field">

                                <div class="account-label">

                                    <label for="name">

                                        Naam

                                    </label>

                                    @error('name')

                                        <span class="field-error">

                                            {{ $message }}

                                        </span>

                                    @enderror

                                </div>



                                <div class="input-wrap">

                                    <input

                                        class="account-input"

                                        id="name"

                                        type="text"

                                        name="name"

                                        value="{{ old('name', $user->name) }}"

                                        autocomplete="name"

                                        required

                                    >

                                    <span class="field-icon">

                                        ◇

                                    </span>

                                </div>

                            </div>



                            {{-- EMAIL --}}

                            <div class="account-field">

                                <div class="account-label">

                                    <label for="email">

                                        E-mailadres

                                    </label>

                                    @error('email')

                                        <span class="field-error">

                                            {{ $message }}

                                        </span>

                                    @enderror

                                </div>



                                <div class="input-wrap">

                                    <input

                                        class="account-input"

                                        id="email"

                                        type="email"

                                        name="email"

                                        value="{{ old('email', $user->email) }}"

                                        autocomplete="email"

                                        required

                                    >

                                    <span class="field-icon">

                                        @

                                    </span>

                                </div>

                            </div>



                            <div class="account-field full">

                                <div class="form-note">

                                    <span class="form-note-mark">

                                        i

                                    </span>

                                    <span>

                                        Wanneer je je e-mailadres wijzigt,

                                        wordt het nieuwe adres opnieuw geverifieerd.

                                        Mashal stuurt daarvoor een nieuwe verificatiecode.

                                    </span>

                                </div>

                            </div>

                        </div>



                        <button

                            class="account-submit"

                            type="submit"

                        >

                            Gegevens opslaan

                            <span aria-hidden="true">→</span>

                        </button>

                    </form>

                </section>



                {{-- ================================================= --}}

                {{-- VERIFICATION                                      --}}

                {{-- ================================================= --}}

                <section class="account-panel">

                    <div class="panel-head">

                        <div>

                            <span class="panel-kicker">

                                02 / Verification

                            </span>

                            <h2 class="panel-title">

                                E-mailverificatie

                            </h2>

                            <p class="panel-copy">

                                Een geverifieerd e-mailadres is nodig

                                om bestellingen veilig te kunnen plaatsen.

                            </p>

                        </div>

                        <span class="panel-badge">

                            Security

                        </span>

                    </div>



                    @if ($user->email_verified_at)

                        <div class="verification-box verified">

                            <div class="verification-copy">

                                <span class="verification-icon">

                                    ✓

                                </span>

                                <div>

                                    <strong>

                                        E-mailadres bevestigd

                                    </strong>

                                    <p>

                                        Je e-mailadres is geverifieerd op

                                        {{ optional($user->email_verified_at)->format('d-m-Y H:i') }}.

                                        Je account is klaar voor checkout.

                                    </p>

                                </div>

                            </div>

                        </div>

                    @else

                        <div class="verification-box pending">

                            <div class="verification-copy">

                                <span class="verification-icon">

                                    !

                                </span>

                                <div>

                                    <strong>

                                        Verificatie nog vereist

                                    </strong>

                                    <p>

                                        Verifieer je e-mailadres om alle

                                        Mashal-functies te gebruiken,

                                        waaronder het plaatsen van bestellingen.

                                    </p>

                                </div>

                            </div>



                            <a

                                class="verification-link"

                                href="{{ route('verification.notice') }}"

                            >

                                Nu verifiëren

                            </a>

                        </div>

                    @endif

                </section>



                {{-- ================================================= --}}

                {{-- PASSWORD                                          --}}

                {{-- ================================================= --}}

                <section

                    class="account-panel"

                    id="security"

                >

                    <div class="panel-head">

                        <div>

                            <span class="panel-kicker">

                                03 / Security

                            </span>

                            <h2 class="panel-title">

                                Wachtwoord wijzigen

                            </h2>

                            <p class="panel-copy">

                                Gebruik een sterk en uniek wachtwoord

                                dat je niet op andere websites gebruikt.

                            </p>

                        </div>

                        <span class="panel-badge">

                            Protected

                        </span>

                    </div>



                    <form

                        method="POST"

                        action="{{ route('account.password.update') }}"

                    >

                        @csrf

                        @method('PUT')



                        <div class="account-form-grid">

                            {{-- CURRENT PASSWORD --}}

                            <div class="account-field full">

                                <div class="account-label">

                                    <label for="current_password">

                                        Huidig wachtwoord

                                    </label>

                                    @error('current_password')

                                        <span class="field-error">

                                            {{ $message }}

                                        </span>

                                    @enderror

                                </div>



                                <div class="input-wrap">

                                    <input

                                        class="account-input"

                                        id="current_password"

                                        type="password"

                                        name="current_password"

                                        autocomplete="current-password"

                                        required

                                    >

                                    <button

                                        class="password-toggle"

                                        type="button"

                                        data-toggle-password="current_password"

                                    >

                                        Tonen

                                    </button>

                                </div>

                            </div>



                            {{-- NEW PASSWORD --}}

                            <div class="account-field">

                                <div class="account-label">

                                    <label for="password">

                                        Nieuw wachtwoord

                                    </label>

                                    @error('password')

                                        <span class="field-error">

                                            {{ $message }}

                                        </span>

                                    @enderror

                                </div>



                                <div class="input-wrap">

                                    <input

                                        class="account-input"

                                        id="password"

                                        type="password"

                                        name="password"

                                        minlength="8"

                                        autocomplete="new-password"

                                        required

                                    >

                                    <button

                                        class="password-toggle"

                                        type="button"

                                        data-toggle-password="password"

                                    >

                                        Tonen

                                    </button>

                                </div>

                            </div>



                            {{-- CONFIRM PASSWORD --}}

                            <div class="account-field">

                                <div class="account-label">

                                    <label for="password_confirmation">

                                        Wachtwoord bevestigen

                                    </label>

                                    @error('password_confirmation')

                                        <span class="field-error">

                                            {{ $message }}

                                        </span>

                                    @enderror

                                </div>



                                <div class="input-wrap">

                                    <input

                                        class="account-input"

                                        id="password_confirmation"

                                        type="password"

                                        name="password_confirmation"

                                        minlength="8"

                                        autocomplete="new-password"

                                        required

                                    >

                                    <button

                                        class="password-toggle"

                                        type="button"

                                        data-toggle-password="password_confirmation"

                                    >

                                        Tonen

                                    </button>

                                </div>

                            </div>



                            <div class="account-field full">

                                <div class="form-note">

                                    <span class="form-note-mark">

                                        ✓

                                    </span>

                                    <span>

                                        Gebruik minimaal 8 tekens.

                                        Een langer wachtwoord met verschillende

                                        tekentypen is doorgaans sterker.

                                    </span>

                                </div>

                            </div>

                        </div>



                        <button

                            class="account-submit"

                            type="submit"

                        >

                            Wachtwoord wijzigen

                            <span aria-hidden="true">→</span>

                        </button>

                    </form>

                </section>



                {{-- ================================================= --}}

                {{-- ACCOUNT INFORMATION                                --}}

                {{-- ================================================= --}}

                <section class="account-panel">

                    <div class="panel-head">

                        <div>

                            <span class="panel-kicker">

                                04 / Overview

                            </span>

                            <h2 class="panel-title">

                                Accountinformatie

                            </h2>

                            <p class="panel-copy">

                                Een compact overzicht van je huidige

                                Mashal-account en verificatiestatus.

                            </p>

                        </div>

                    </div>



                    <div class="info-grid">

                        <div class="info-card">

                            <small>

                                Naam

                            </small>

                            <strong>

                                {{ $user->name }}

                            </strong>

                        </div>



                        <div class="info-card">

                            <small>

                                E-mailadres

                            </small>

                            <strong>

                                {{ $user->email }}

                            </strong>

                        </div>



                        <div class="info-card">

                            <small>

                                E-mailstatus

                            </small>

                            <strong>

                                {{ $user->email_verified_at ? 'Geverifieerd' : 'Nog niet geverifieerd' }}

                            </strong>

                        </div>



                        <div class="info-card">

                            <small>

                                Account aangemaakt

                            </small>

                            <strong>

                                {{ optional($user->created_at)->format('d-m-Y H:i') }}

                            </strong>

                        </div>

                    </div>

                </section>



                {{-- ================================================= --}}

                {{-- ORDERS                                            --}}

                {{-- ================================================= --}}

                <section

                    class="account-panel"

                    id="orders"

                >

                    <div class="panel-head">

                        <div>

                            <span class="panel-kicker">

                                05 / Orders

                            </span>

                            <h2 class="panel-title">

                                Mijn bestellingen

                            </h2>

                            <p class="panel-copy">

                                Bekijk je recente Mashal-bestellingen,

                                totaalbedragen en actuele status.

                            </p>

                        </div>

                        <span class="panel-badge">

                            {{ count($orders) }}

                            {{ count($orders) === 1 ? 'order' : 'orders' }}

                        </span>

                    </div>



                    @forelse ($orders as $order)

                        @php

                            $statusLabels = [

                                'placed' => 'Geplaatst',

                                'paid' => 'Betaald',

                                'processing' => 'In behandeling',

                                'shipped' => 'Verzonden',

                                'completed' => 'Voltooid',

                                'cancelled' => 'Geannuleerd',

                            ];

                            $statusLabel =

                                $statusLabels[$order->status]

                                ?? ucfirst($order->status);

                            $statusClass =

                                in_array(

                                    $order->status,

                                    [

                                        'placed',

                                        'paid',

                                        'processing',

                                        'shipped',

                                        'completed',

                                        'cancelled'

                                    ],

                                    true

                                )

                                    ? 'status-' . $order->status

                                    : 'status-default';

                        @endphp



                        <div class="order-card">

                            <div>

                                <div class="order-number">

                                    Bestelling {{ $order->order_number }}

                                </div>

                                <div class="order-meta">

                                    <span>

                                        Totaal:

                                        <strong>

                                            €{{ number_format($order->total, 0, ',', '.') }}

                                        </strong>

                                    </span>

                                    <span>

                                        Geplaatst:

                                        <strong>

                                            {{ \Carbon\Carbon::parse($order->created_at)->format('d-m-Y H:i') }}

                                        </strong>

                                    </span>

                                </div>

                            </div>



                            <span class="order-status {{ $statusClass }}">

                                {{ $statusLabel }}

                            </span>

                        </div>

                    @empty

                        <div class="orders-empty">

                            <div class="orders-empty-mark">

                                M

                            </div>

                            <strong>

                                Nog geen bestellingen

                            </strong>

                            <p>

                                Je hebt nog geen bestelling geplaatst.

                                Ontdek de Mashal-collectie wanneer je klaar bent

                                om je eerste voertuig te selecteren.

                            </p>

                            <a

                                class="primary-btn"

                                href="{{ route('catalog') }}"

                            >

                                Ontdek de collectie

                            </a>

                        </div>

                    @endforelse

                </section>

            </div>

        </div>



        {{-- ========================================================= --}}

        {{-- SECURITY STRIP                                             --}}

        {{-- ========================================================= --}}

        <div class="account-security-strip">

            <div class="security-item">

                <small>

                    Verification

                </small>

                <strong>

                    Beveiligde e-mailverificatie

                </strong>

                <span>

                    Nieuwe of gewijzigde e-mailadressen

                    worden opnieuw gecontroleerd.

                </span>

            </div>



            <div class="security-item">

                <small>

                    Password

                </small>

                <strong>

                    Persoonlijk wachtwoordbeheer

                </strong>

                <span>

                    Je kunt je wachtwoord zelf

                    vanuit je account wijzigen.

                </span>

            </div>



            <div class="security-item">

                <small>

                    Orders

                </small>

                <strong>

                    Bestellingen gekoppeld aan jou

                </strong>

                <span>

                    Je geplaatste orders blijven zichtbaar

                    vanuit je persoonlijke Mashal-account.

                </span>

            </div>

        </div>

    </div>

</section>

@endsection



@push('scripts')

<script>

    document.addEventListener('DOMContentLoaded', function () {

        const photoInput =
            document.getElementById('profile_photo');

        const photoPreview =
            document.getElementById('profilePhotoPreview');

        const photoFilename =
            document.getElementById('profilePhotoFilename');

        const removePhoto =
            document.getElementById('remove_profile_photo');

        if (photoInput && photoPreview) {
            photoInput.addEventListener('change', function () {
                const file =
                    photoInput.files &&
                    photoInput.files.length > 0
                        ? photoInput.files[0]
                        : null;

                if (!file) {
                    if (photoFilename) {
                        photoFilename.textContent =
                            'Geen nieuw bestand geselecteerd.';
                    }

                    return;
                }

                if (photoFilename) {
                    photoFilename.textContent =
                        'Geselecteerd: ' + file.name;
                }

                const reader =
                    new FileReader();

                reader.addEventListener('load', function (event) {
                    photoPreview.innerHTML = '';

                    const image =
                        document.createElement('img');

                    image.src =
                        event.target.result;

                    image.alt =
                        'Voorbeeld van nieuwe profielfoto';

                    image.id =
                        'profilePhotoPreviewImage';

                    photoPreview.appendChild(
                        image
                    );
                });

                reader.readAsDataURL(
                    file
                );

                if (removePhoto) {
                    removePhoto.checked = false;
                }
            });
        }


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