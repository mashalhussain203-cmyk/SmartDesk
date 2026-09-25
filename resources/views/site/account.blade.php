@extends('layouts.site-layout')

@section('title', 'Mijn account | Mashal Studio')

@section(
    'meta_description',
    'Beheer je Mashal Studio-profiel, beveiliging, afbeeldingen en persoonlijke accountinstellingen.'
)

@push('styles')
<style>
    .account-page {
        position: relative;
        min-height: 100vh;
        padding: 64px 0 110px;
        overflow: hidden;
        color: #f7f7f4;
    }

    .account-page::before {
        content: "ACCOUNT";
        position: absolute;
        top: 18px;
        right: -60px;
        pointer-events: none;
        user-select: none;
        color: rgba(255,255,255,.012);
        font-size: clamp(130px, 17vw, 280px);
        font-weight: 950;
        line-height: .8;
        letter-spacing: -.085em;
    }

    .account-shell {
        position: relative;
        z-index: 2;
        width: min(calc(100% - 40px), 1320px);
        margin-inline: auto;
    }

    .account-hero {
        position: relative;
        overflow: hidden;
        margin-bottom: 18px;
        padding: 38px;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 36px;
        align-items: end;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 30px;
        background:
            radial-gradient(circle at 92% 12%, rgba(227,179,107,.13), transparent 21rem),
            radial-gradient(circle at 72% 100%, rgba(98,94,255,.055), transparent 24rem),
            linear-gradient(145deg, rgba(255,255,255,.043), rgba(255,255,255,.007)),
            #0c0f14;
        box-shadow: 0 34px 100px rgba(0,0,0,.28);
    }

    .account-hero::after {
        content: "";
        position: absolute;
        width: 280px;
        height: 280px;
        right: -125px;
        bottom: -160px;
        border: 1px solid rgba(227,179,107,.065);
        border-radius: 50%;
        box-shadow:
            0 0 0 55px rgba(227,179,107,.012),
            0 0 0 110px rgba(227,179,107,.007);
        pointer-events: none;
    }

    .account-kicker {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: #d9aa65;
        font-size: 9px;
        font-weight: 950;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .account-kicker::before {
        content: "";
        width: 30px;
        height: 1px;
        background: linear-gradient(90deg, #e0b16b, transparent);
    }

    .account-title {
        max-width: 860px;
        margin: 14px 0 0;
        color: #f8f8f5;
        font-size: clamp(48px, 6.2vw, 82px);
        line-height: .94;
        letter-spacing: -.068em;
        font-weight: 950;
        text-wrap: balance;
    }

    .account-title span {
        color: #f0ca8b;
    }

    .account-intro {
        max-width: 720px;
        margin: 18px 0 0;
        color: #858c96;
        font-size: 12px;
        line-height: 1.85;
    }

    .account-hero-actions {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 9px;
        flex-wrap: wrap;
    }

    .account-action {
        min-height: 44px;
        padding: 0 15px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 12px;
        color: #b9bec5;
        background: rgba(255,255,255,.018);
        text-decoration: none;
        font-size: 8px;
        font-weight: 950;
        cursor: pointer;
        transition:
            transform .2s ease,
            border-color .2s ease,
            color .2s ease,
            background .2s ease,
            box-shadow .2s ease;
    }

    .account-action:hover {
        transform: translateY(-2px);
        border-color: rgba(227,179,107,.16);
        color: #e4e6e9;
        background: rgba(227,179,107,.035);
    }

    .account-action.primary {
        border-color: rgba(227,179,107,.18);
        color: #171009;
        background: linear-gradient(135deg, #f2d393, #d49c52);
        box-shadow: 0 16px 38px rgba(227,179,107,.15);
    }

    .account-action.primary:hover {
        color: #171009;
        box-shadow: 0 22px 48px rgba(227,179,107,.22);
    }

    .account-message {
        margin-bottom: 16px;
        padding: 14px 16px;
        border-radius: 13px;
        font-size: 9px;
        line-height: 1.65;
    }

    .account-message.success {
        border: 1px solid rgba(103,217,144,.13);
        color: #9dd8b0;
        background: rgba(103,217,144,.04);
    }

    .account-message.error {
        border: 1px solid rgba(240,131,131,.13);
        color: #dca0a0;
        background: rgba(240,131,131,.04);
    }

    .account-message ul {
        margin: 7px 0 0 16px;
        padding: 0;
    }

    .account-stats {
        margin-bottom: 18px;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .stat-card {
        position: relative;
        min-height: 128px;
        padding: 19px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.065);
        border-radius: 19px;
        background:
            linear-gradient(145deg, rgba(255,255,255,.028), rgba(255,255,255,.006)),
            #0c0f14;
        transition:
            transform .22s ease,
            border-color .22s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        border-color: rgba(227,179,107,.15);
    }

    .stat-card::after {
        content: "";
        position: absolute;
        width: 120px;
        height: 120px;
        right: -45px;
        bottom: -58px;
        border: 1px solid rgba(227,179,107,.055);
        border-radius: 50%;
    }

    .stat-card small {
        display: block;
        color: #59616b;
        font-size: 7px;
        font-weight: 950;
        letter-spacing: .13em;
        text-transform: uppercase;
    }

    .stat-value {
        margin-top: 14px;
        color: #ebedef;
        font-size: 29px;
        line-height: 1;
        font-weight: 950;
        letter-spacing: -.05em;
    }

    .stat-value.compact {
        font-size: 21px;
    }

    .stat-foot {
        margin-top: 8px;
        color: #565d67;
        font-size: 8px;
        line-height: 1.5;
    }

    .account-layout {
        display: grid;
        grid-template-columns: 300px minmax(0, 1fr);
        gap: 20px;
        align-items: start;
    }

    .account-sidebar {
        position: sticky;
        top: 96px;
        display: grid;
        gap: 14px;
    }

    .profile-card,
    .account-nav-card,
    .account-panel {
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 22px;
        background:
            linear-gradient(145deg, rgba(255,255,255,.03), rgba(255,255,255,.006)),
            #0c0f14;
        box-shadow: 0 18px 50px rgba(0,0,0,.15);
    }

    .profile-card {
        padding: 23px;
    }

    .profile-avatar {
        width: 76px;
        height: 76px;
        display: grid;
        place-items: center;
        overflow: hidden;
        border: 1px solid rgba(227,179,107,.16);
        border-radius: 22px;
        color: #e5bc7c;
        background:
            linear-gradient(145deg, rgba(227,179,107,.15), rgba(227,179,107,.035));
        box-shadow: 0 16px 38px rgba(0,0,0,.20);
        font-size: 23px;
        font-weight: 950;
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-name {
        margin: 18px 0 0;
        color: #eef0f2;
        font-size: 21px;
        line-height: 1.1;
        letter-spacing: -.04em;
        word-break: break-word;
    }

    .profile-email {
        margin-top: 6px;
        color: #69717b;
        font-size: 9px;
        line-height: 1.55;
        word-break: break-word;
    }

    .profile-status {
        margin-top: 15px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #85cf9d;
        font-size: 7px;
        font-weight: 950;
        letter-spacing: .09em;
        text-transform: uppercase;
    }

    .profile-status.pending {
        color: #d3ad69;
    }

    .profile-status::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
        box-shadow: 0 0 0 4px rgba(255,255,255,.025);
    }

    .profile-meta {
        margin-top: 17px;
        padding-top: 16px;
        display: grid;
        gap: 9px;
        border-top: 1px solid rgba(255,255,255,.055);
    }

    .profile-meta-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        color: #676e78;
        font-size: 8px;
    }

    .profile-meta-row strong {
        color: #a7adb5;
        font-size: 8px;
    }

    .account-nav-card {
        padding: 12px;
    }

    .account-nav-title {
        margin: 4px 7px 10px;
        color: #4f5660;
        font-size: 7px;
        font-weight: 950;
        letter-spacing: .15em;
        text-transform: uppercase;
    }

    .account-nav {
        display: grid;
        gap: 4px;
    }

    .account-nav-link {
        min-height: 44px;
        padding: 0 11px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 13px;
        border: 1px solid transparent;
        border-radius: 11px;
        color: #7d848e;
        text-decoration: none;
        font-size: 9px;
        font-weight: 850;
        transition:
            transform .2s ease,
            color .2s ease,
            border-color .2s ease,
            background .2s ease;
    }

    .account-nav-link:hover {
        transform: translateX(2px);
        border-color: rgba(227,179,107,.09);
        color: #d6b075;
        background: rgba(227,179,107,.03);
    }

    .account-content {
        min-width: 0;
        display: grid;
        gap: 18px;
    }

    .account-panel {
        scroll-margin-top: 98px;
        padding: 28px;
    }

    .panel-head {
        margin-bottom: 24px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
    }

    .panel-index {
        display: block;
        margin-bottom: 6px;
        color: #946e3e;
        font-size: 7px;
        font-weight: 950;
        letter-spacing: .15em;
        text-transform: uppercase;
    }

    .panel-title {
        margin: 0;
        color: #e9ebed;
        font-size: 25px;
        line-height: 1.1;
        letter-spacing: -.04em;
    }

    .panel-copy {
        max-width: 680px;
        margin: 8px 0 0;
        color: #6f7680;
        font-size: 10px;
        line-height: 1.75;
    }

    .panel-badge {
        flex: 0 0 auto;
        min-height: 29px;
        padding: 0 9px;
        display: inline-flex;
        align-items: center;
        border: 1px solid rgba(227,179,107,.11);
        border-radius: 999px;
        color: #af854c;
        background: rgba(227,179,107,.035);
        font-size: 7px;
        font-weight: 950;
        letter-spacing: .09em;
        text-transform: uppercase;
    }

    .photo-editor {
        margin-bottom: 18px;
        padding: 18px;
        display: grid;
        grid-template-columns: 100px minmax(0, 1fr);
        gap: 18px;
        align-items: center;
        border: 1px solid rgba(227,179,107,.10);
        border-radius: 17px;
        background:
            linear-gradient(145deg, rgba(227,179,107,.035), rgba(255,255,255,.009));
    }

    .photo-preview {
        width: 100px;
        height: 100px;
        display: grid;
        place-items: center;
        overflow: hidden;
        border: 1px solid rgba(227,179,107,.16);
        border-radius: 25px;
        color: #e7bd7b;
        background:
            linear-gradient(145deg, rgba(227,179,107,.14), rgba(227,179,107,.035));
        box-shadow: 0 16px 36px rgba(0,0,0,.20);
        font-size: 25px;
        font-weight: 950;
    }

    .photo-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .photo-copy h3 {
        margin: 0;
        color: #dfe2e5;
        font-size: 13px;
    }

    .photo-copy p {
        max-width: 610px;
        margin: 6px 0 13px;
        color: #686f79;
        font-size: 9px;
        line-height: 1.65;
    }

    .photo-actions {
        display: flex;
        align-items: center;
        gap: 9px;
        flex-wrap: wrap;
    }

    .photo-input {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        opacity: 0;
        pointer-events: none;
    }

    .photo-button {
        min-height: 39px;
        padding: 0 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border: 1px solid rgba(227,179,107,.14);
        border-radius: 10px;
        color: #d8ad6c;
        background: rgba(227,179,107,.04);
        font-size: 8px;
        font-weight: 900;
        cursor: pointer;
    }

    .photo-remove {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #737a84;
        font-size: 8px;
        cursor: pointer;
    }

    .photo-remove input {
        width: 14px;
        height: 14px;
        accent-color: #d7a45f;
    }

    .photo-filename {
        margin-top: 9px;
        color: #555d67;
        font-size: 8px;
        line-height: 1.5;
        word-break: break-word;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 15px;
    }

    .field {
        min-width: 0;
    }

    .field.full {
        grid-column: 1 / -1;
    }

    .field-label {
        margin-bottom: 7px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .field-label label {
        margin: 0;
        color: #9da3ab;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .05em;
    }

    .field-error {
        color: #e99595;
        font-size: 8px;
        font-weight: 800;
    }

    .input-wrap {
        position: relative;
    }

    .account-input {
        width: 100%;
        min-height: 51px;
        padding: 0 14px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 12px;
        outline: none;
        color: #e5e7e9;
        background: rgba(255,255,255,.018);
        font-size: 10px;
        transition:
            border-color .2s ease,
            background .2s ease,
            box-shadow .2s ease;
    }

    .account-input.with-toggle {
        padding-right: 80px;
    }

    .account-input:focus {
        border-color: rgba(227,179,107,.29);
        background: rgba(227,179,107,.022);
        box-shadow: 0 0 0 4px rgba(227,179,107,.04);
    }

    .password-toggle {
        position: absolute;
        right: 8px;
        top: 50%;
        min-height: 34px;
        padding: 0 10px;
        transform: translateY(-50%);
        border: 1px solid rgba(255,255,255,.055);
        border-radius: 9px;
        color: #6c737d;
        background: #101319;
        font-size: 7px;
        font-weight: 900;
        cursor: pointer;
    }

    .form-note {
        padding: 13px 14px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        border: 1px solid rgba(227,179,107,.09);
        border-radius: 12px;
        color: #746957;
        background: rgba(227,179,107,.022);
        font-size: 8px;
        line-height: 1.65;
    }

    .form-note-mark {
        width: 22px;
        height: 22px;
        flex: 0 0 22px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(227,179,107,.12);
        border-radius: 50%;
        color: #b88a4c;
        font-size: 7px;
        font-weight: 950;
    }

    .account-submit {
        min-height: 46px;
        margin-top: 19px;
        padding: 0 17px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 0;
        border-radius: 11px;
        color: #171009;
        background: linear-gradient(135deg, #f0d08f, #d29a50);
        box-shadow: 0 14px 32px rgba(227,179,107,.13);
        font-size: 8px;
        font-weight: 950;
        cursor: pointer;
        transition:
            transform .2s ease,
            box-shadow .2s ease;
    }

    .account-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 20px 42px rgba(227,179,107,.20);
    }

    #recovery .verification {
        margin-bottom: 0;
    }

    #recovery .form-note strong {
        color: #b8a27f;
        font-weight: 900;
        overflow-wrap: anywhere;
    }

    .verification {
        padding: 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        border-radius: 16px;
    }

    .verification.verified {
        border: 1px solid rgba(103,217,144,.12);
        background: rgba(103,217,144,.035);
    }

    .verification.pending {
        border: 1px solid rgba(237,194,112,.12);
        background: rgba(237,194,112,.035);
    }

    .verification-main {
        min-width: 0;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .verification-icon {
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 12px;
        color: #d2aa6b;
        background: rgba(255,255,255,.015);
        font-size: 11px;
        font-weight: 950;
    }

    .verification.verified .verification-icon {
        color: #86d29e;
    }

    .verification-copy strong {
        display: block;
        color: #cfd3d8;
        font-size: 10px;
    }

    .verification-copy p {
        max-width: 660px;
        margin: 5px 0 0;
        color: #686f79;
        font-size: 8px;
        line-height: 1.65;
    }

    .overview-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }

    .overview-card {
        min-width: 0;
        padding: 15px;
        border: 1px solid rgba(255,255,255,.055);
        border-radius: 14px;
        background: rgba(255,255,255,.012);
    }

    .overview-card small {
        display: block;
        color: #565d67;
        font-size: 7px;
        font-weight: 900;
        letter-spacing: .09em;
        text-transform: uppercase;
    }

    .overview-card strong {
        display: block;
        margin-top: 6px;
        overflow: hidden;
        color: #cdd1d6;
        font-size: 9px;
        line-height: 1.55;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .library-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 11px;
    }

    .project-card {
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.06);
        border-radius: 16px;
        background: rgba(255,255,255,.012);
        transition:
            transform .22s ease,
            border-color .22s ease;
    }

    .project-card:hover {
        transform: translateY(-4px);
        border-color: rgba(227,179,107,.14);
    }

    .project-preview {
        position: relative;
        aspect-ratio: 16 / 10;
        overflow: hidden;
        background:
            radial-gradient(circle at 70% 28%, rgba(227,179,107,.16), transparent 8rem),
            #11151b;
    }

    .project-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .3s ease;
    }

    .project-card:hover .project-preview img {
        transform: scale(1.025);
    }

    .project-size {
        position: absolute;
        left: 11px;
        bottom: 11px;
        min-height: 25px;
        padding: 0 8px;
        display: inline-flex;
        align-items: center;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 8px;
        color: #a0a7af;
        background: rgba(7,9,12,.76);
        backdrop-filter: blur(10px);
        font-size: 7px;
        font-weight: 900;
    }

    .project-format {
        position: absolute;
        right: 11px;
        top: 11px;
        min-height: 25px;
        padding: 0 8px;
        display: inline-flex;
        align-items: center;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 8px;
        color: #d8b170;
        background: rgba(7,9,12,.76);
        backdrop-filter: blur(10px);
        font-size: 7px;
        font-weight: 950;
    }

    .project-body {
        padding: 14px;
    }

    .project-name {
        overflow: hidden;
        color: #d3d7dc;
        font-size: 10px;
        font-weight: 850;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .project-meta {
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 7px;
        flex-wrap: wrap;
        color: #555d67;
        font-size: 7px;
        font-weight: 800;
    }

    .project-actions {
        margin-top: 11px;
        display: flex;
        gap: 7px;
        flex-wrap: wrap;
    }

    .project-link {
        min-height: 35px;
        padding: 0 11px;
        display: inline-flex;
        align-items: center;
        border: 1px solid rgba(227,179,107,.10);
        border-radius: 9px;
        color: #c89b59;
        background: rgba(227,179,107,.025);
        text-decoration: none;
        font-size: 7px;
        font-weight: 950;
    }

    .empty-library {
        padding: 38px 20px;
        text-align: center;
        border: 1px dashed rgba(255,255,255,.08);
        border-radius: 16px;
        background: rgba(255,255,255,.01);
    }

    .empty-library-mark {
        width: 48px;
        height: 48px;
        margin: 0 auto 13px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(227,179,107,.12);
        border-radius: 15px;
        color: #d8ac6a;
        background: rgba(227,179,107,.035);
        font-size: 15px;
        font-weight: 950;
    }

    .empty-library strong {
        display: block;
        color: #d5d9dd;
        font-size: 12px;
    }

    .empty-library p {
        max-width: 530px;
        margin: 7px auto 16px;
        color: #656d77;
        font-size: 8px;
        line-height: 1.7;
    }

    .security-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .security-card {
        padding: 17px;
        border: 1px solid rgba(255,255,255,.055);
        border-radius: 14px;
        background: rgba(255,255,255,.012);
    }

    .security-card small {
        display: block;
        color: #86663d;
        font-size: 7px;
        font-weight: 950;
        letter-spacing: .11em;
        text-transform: uppercase;
    }

    .security-card strong {
        display: block;
        margin-top: 7px;
        color: #c9cdd2;
        font-size: 9px;
    }

    .security-card p {
        margin: 5px 0 0;
        color: #59616b;
        font-size: 8px;
        line-height: 1.6;
    }

    @media (max-width: 1080px) {
        .account-hero,
        .account-layout {
            grid-template-columns: 1fr;
        }

        .account-sidebar {
            position: static;
            grid-template-columns: 1fr 1fr;
        }

        .account-stats {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 850px) {
        .library-grid {
            grid-template-columns: 1fr 1fr;
        }

        .overview-grid {
            grid-template-columns: 1fr 1fr;
        }

        .security-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .account-page {
            padding: 44px 0 80px;
        }

        .account-shell {
            width: min(calc(100% - 22px), 1320px);
        }

        .account-hero {
            padding: 24px 20px;
            border-radius: 23px;
        }

        .account-title {
            font-size: clamp(44px, 14vw, 62px);
        }

        .account-hero-actions {
            width: 100%;
            align-items: stretch;
            flex-direction: column;
        }

        .account-action {
            width: 100%;
        }

        .account-stats,
        .account-sidebar,
        .form-grid,
        .overview-grid,
        .library-grid {
            grid-template-columns: 1fr;
        }

        .field.full {
            grid-column: auto;
        }

        .account-panel {
            padding: 20px;
            border-radius: 19px;
        }

        .panel-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .photo-editor {
            grid-template-columns: 1fr;
        }

        .photo-preview {
            width: 86px;
            height: 86px;
        }

        .photo-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .photo-button {
            width: 100%;
        }

        .verification {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
@php
    $user = $user ?? auth()->user();

    abort_unless($user, 401);

    $hasImagesIndex =
        \Illuminate\Support\Facades\Route::has('images.index');

    $hasImagesEditor =
        \Illuminate\Support\Facades\Route::has('images.editor');

    $hasImagesFile =
        \Illuminate\Support\Facades\Route::has('images.file');

    $hasImagesDownload =
        \Illuminate\Support\Facades\Route::has('images.download');

    $hasSecurity =
        \Illuminate\Support\Facades\Route::has('security.index');

    $hasVerification =
        \Illuminate\Support\Facades\Route::has('verification.notice');

    $accountInitials = 'M';

    if (
        method_exists($user, 'initials') &&
        $user->initials()
    ) {
        $accountInitials =
            (string) $user->initials();
    } else {
        $parts = preg_split(
            '/\s+/',
            trim((string) $user->name)
        ) ?: [];

        $accountInitials = '';

        foreach (array_slice($parts, 0, 2) as $part) {
            $accountInitials .= mb_strtoupper(
                mb_substr($part, 0, 1)
            );
        }

        if ($accountInitials === '') {
            $accountInitials = 'M';
        }
    }

    $accountAvatarUrl = null;

    if (
        method_exists($user, 'avatarUrl')
    ) {
        $accountAvatarUrl =
            $user->avatarUrl();
    }

    $hasOwnProfilePhoto =
        method_exists($user, 'hasProfilePhoto')
            ? (bool) $user->hasProfilePhoto()
            : false;

    $imageCount = 0;
    $versionCount = 0;
    $latestImages = collect();

    try {
        if (class_exists(\App\Models\Image::class)) {
            $imageCount =
                \App\Models\Image::query()
                    ->where('user_id', $user->id)
                    ->count();

            $latestImages =
                \App\Models\Image::query()
                    ->where('user_id', $user->id)
                    ->withCount('versions')
                    ->latest('id')
                    ->take(6)
                    ->get();
        }

        if (class_exists(\App\Models\ImageVersion::class)) {
            $versionCount =
                \App\Models\ImageVersion::query()
                    ->where('user_id', $user->id)
                    ->count();
        }
    } catch (\Throwable $exception) {
        $imageCount = 0;
        $versionCount = 0;
        $latestImages = collect();
    }

    $memberSince =
        optional($user->created_at)->format('d-m-Y')
        ?? 'Onbekend';

    $verifiedAt =
        optional($user->email_verified_at)->format('d-m-Y H:i');

    $recoveryEmail = strtolower(
        trim(
            (string) ($user->recovery_email ?? '')
        )
    );

    $hasRecoveryEmail =
        $recoveryEmail !== '';

    $hasVerifiedRecoveryEmail =
        $hasRecoveryEmail
        && $user->recovery_email_verified_at !== null;

    $recoveryVerifiedAt =
        optional(
            $user->recovery_email_verified_at
        )->format('d-m-Y H:i');

    $accountProvider =
        $user->login_provider
        ?? 'password';

    $accountProviderLabel =
        match (strtolower((string) $accountProvider)) {
            'google' => 'Google',
            'github' => 'GitHub',
            'facebook' => 'Facebook',
            'tiktok' => 'TikTok',
            default => 'E-mail / wachtwoord',
        };
@endphp

<section class="account-page">
    <div class="account-shell">

        <header class="account-hero studio-reveal">
            <div>
                <span class="account-kicker">
                    Mashal Studio account
                </span>

                <h1 class="account-title">
                    Welkom terug,
                    <span>{{ $user->name }}.</span>
                </h1>

                <p class="account-intro">
                    Beheer je profiel, beveiliging en persoonlijke
                    afbeeldingsprojecten vanuit één centrale workspace.
                    Je originelen en opgeslagen bewerkingsversies blijven
                    gekoppeld aan jouw account.
                </p>
            </div>

            <div class="account-hero-actions">
                <a
                    class="account-action primary"
                    href="{{ route('home') }}#upload"
                >
                    + Nieuwe afbeelding
                </a>

                @if ($hasImagesIndex)
                    <a
                        class="account-action"
                        href="{{ route('images.index') }}"
                    >
                        Mijn afbeeldingen
                    </a>
                @endif

                @if ($hasSecurity)
                    <a
                        class="account-action"
                        href="{{ route('security.index') }}"
                    >
                        Beveiliging
                    </a>
                @endif
            </div>
        </header>

        @if (session('success'))
            <div class="account-message success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="account-message error">
                {{ session('error') }}
            </div>
        @endif

        <div class="account-stats studio-reveal">
            <article class="stat-card">
                <small>Afbeeldingen</small>
                <div class="stat-value">{{ number_format($imageCount) }}</div>
                <div class="stat-foot">Originelen gekoppeld aan jouw account</div>
            </article>

            <article class="stat-card">
                <small>Opgeslagen versies</small>
                <div class="stat-value">{{ number_format($versionCount) }}</div>
                <div class="stat-foot">Bewerkte uitvoeringen van je projecten</div>
            </article>

            <article class="stat-card">
                <small>E-mailstatus</small>
                <div class="stat-value compact">
                    {{ $user->email_verified_at ? 'Verified' : 'Pending' }}
                </div>
                <div class="stat-foot">
                    {{ $user->email_verified_at
                        ? 'Je accountadres is bevestigd'
                        : 'Verificatie is nog vereist' }}
                </div>
            </article>

            <article class="stat-card">
                <small>Account</small>
                <div class="stat-value">#{{ $user->id }}</div>
                <div class="stat-foot">Lid sinds {{ $memberSince }}</div>
            </article>
        </div>

        <div class="account-layout">
            <aside class="account-sidebar">
                <section class="profile-card studio-reveal">
                    <div class="profile-avatar">
                        @if ($accountAvatarUrl)
                            <img
                                src="{{ $accountAvatarUrl }}"
                                alt="Profielfoto van {{ $user->name }}"
                            >
                        @else
                            {{ $accountInitials }}
                        @endif
                    </div>

                    <h2 class="profile-name">
                        {{ $user->name }}
                    </h2>

                    <div class="profile-email">
                        {{ $user->email }}
                    </div>

                    <div class="profile-status {{ $user->email_verified_at ? '' : 'pending' }}">
                        {{ $user->email_verified_at
                            ? 'Account verified'
                            : 'Verification pending' }}
                    </div>

                    <div class="profile-meta">
                        <div class="profile-meta-row">
                            <span>Projecten</span>
                            <strong>{{ $imageCount }}</strong>
                        </div>

                        <div class="profile-meta-row">
                            <span>Versies</span>
                            <strong>{{ $versionCount }}</strong>
                        </div>

                        <div class="profile-meta-row">
                            <span>Login</span>
                            <strong>{{ $accountProviderLabel }}</strong>
                        </div>

                        <div class="profile-meta-row">
                            <span>Recovery</span>
                            <strong>
                                @if ($hasVerifiedRecoveryEmail)
                                    Verified
                                @elseif ($hasRecoveryEmail)
                                    Pending
                                @else
                                    Niet ingesteld
                                @endif
                            </strong>
                        </div>

                        <div class="profile-meta-row">
                            <span>Account ID</span>
                            <strong>#{{ $user->id }}</strong>
                        </div>
                    </div>
                </section>

                <section class="account-nav-card studio-reveal">
                    <div class="account-nav-title">
                        Account navigatie
                    </div>

                    <nav class="account-nav">
                        <a class="account-nav-link" href="#profile">
                            <span>Profielgegevens</span>
                            <span>→</span>
                        </a>

                        <a class="account-nav-link" href="#verification">
                            <span>E-mailverificatie</span>
                            <span>→</span>
                        </a>

                        <a class="account-nav-link" href="#recovery">
                            <span>Herstel-e-mailadres</span>
                            <span>→</span>
                        </a>

                        <a class="account-nav-link" href="#password">
                            <span>Wachtwoord</span>
                            <span>→</span>
                        </a>

                        <a class="account-nav-link" href="#overview">
                            <span>Accountinformatie</span>
                            <span>→</span>
                        </a>

                        <a class="account-nav-link" href="#library">
                            <span>Recente afbeeldingen</span>
                            <span>{{ $imageCount }}</span>
                        </a>

                        <a class="account-nav-link" href="#security">
                            <span>Beveiliging</span>
                            <span>→</span>
                        </a>
                    </nav>
                </section>
            </aside>

            <div class="account-content">

                <section
                    class="account-panel studio-reveal"
                    id="profile"
                >
                    <div class="panel-head">
                        <div>
                            <span class="panel-index">
                                01 / Profile
                            </span>

                            <h2 class="panel-title">
                                Profielgegevens
                            </h2>

                            <p class="panel-copy">
                                Beheer je naam, e-mailadres en profielfoto.
                                Deze gegevens worden gebruikt binnen je
                                persoonlijke Mashal Studio-workspace.
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

                        <div class="photo-editor">
                            <div
                                class="photo-preview"
                                id="profilePhotoPreview"
                            >
                                @if ($accountAvatarUrl)
                                    <img
                                        src="{{ $accountAvatarUrl }}"
                                        alt="Huidige profielfoto"
                                    >
                                @else
                                    <span>
                                        {{ $accountInitials }}
                                    </span>
                                @endif
                            </div>

                            <div class="photo-copy">
                                <h3>
                                    Profielfoto
                                </h3>

                                <p>
                                    Upload een JPG, PNG of WEBP-afbeelding van
                                    maximaal 5 MB. De nieuwe afbeelding wordt
                                    vooraf lokaal in je browser weergegeven.
                                </p>

                                <div class="photo-actions">
                                    <label
                                        class="photo-button"
                                        for="profile_photo"
                                    >
                                        + Kies profielfoto
                                    </label>

                                    <input
                                        class="photo-input"
                                        id="profile_photo"
                                        type="file"
                                        name="profile_photo"
                                        accept="image/jpeg,image/png,image/webp"
                                    >

                                    @if ($hasOwnProfilePhoto)
                                        <label class="photo-remove">
                                            <input
                                                id="remove_profile_photo"
                                                type="checkbox"
                                                name="remove_profile_photo"
                                                value="1"
                                                @checked(old('remove_profile_photo'))
                                            >

                                            <span>
                                                Eigen profielfoto verwijderen
                                            </span>
                                        </label>
                                    @endif
                                </div>

                                <div
                                    class="photo-filename"
                                    id="profilePhotoFilename"
                                >
                                    Geen nieuw bestand geselecteerd.
                                </div>

                                @error('profile_photo')
                                    <div class="field-error">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="field">
                                <div class="field-label">
                                    <label for="name">
                                        Naam
                                    </label>

                                    @error('name')
                                        <span class="field-error">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <input
                                    class="account-input"
                                    id="name"
                                    type="text"
                                    name="name"
                                    value="{{ old('name', $user->name) }}"
                                    autocomplete="name"
                                    required
                                >
                            </div>

                            <div class="field">
                                <div class="field-label">
                                    <label for="email">
                                        E-mailadres
                                    </label>

                                    @error('email')
                                        <span class="field-error">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <input
                                    class="account-input"
                                    id="email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email', $user->email) }}"
                                    autocomplete="email"
                                    required
                                >
                            </div>

                            <div class="field full">
                                <div class="form-note">
                                    <span class="form-note-mark">i</span>

                                    <span>
                                        Als je je e-mailadres wijzigt, kan het nieuwe
                                        adres opnieuw geverifieerd moeten worden.
                                    </span>
                                </div>
                            </div>
                        </div>

                        <button
                            class="account-submit"
                            type="submit"
                        >
                            Profiel opslaan
                            <span aria-hidden="true">→</span>
                        </button>
                    </form>
                </section>

                <section
                    class="account-panel studio-reveal"
                    id="verification"
                >
                    <div class="panel-head">
                        <div>
                            <span class="panel-index">
                                02 / Verification
                            </span>

                            <h2 class="panel-title">
                                E-mailverificatie
                            </h2>

                            <p class="panel-copy">
                                Een bevestigd e-mailadres helpt je accounttoegang
                                betrouwbaar en controleerbaar te houden.
                            </p>
                        </div>

                        <span class="panel-badge">
                            Security
                        </span>
                    </div>

                    @if ($user->email_verified_at)
                        <div class="verification verified">
                            <div class="verification-main">
                                <span class="verification-icon">✓</span>

                                <div class="verification-copy">
                                    <strong>
                                        E-mailadres bevestigd
                                    </strong>

                                    <p>
                                        Je e-mailadres is
                                        @if ($verifiedAt)
                                            geverifieerd op {{ $verifiedAt }}.
                                        @else
                                            geverifieerd.
                                        @endif
                                        Je account is klaar voor je persoonlijke workspace.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="verification pending">
                            <div class="verification-main">
                                <span class="verification-icon">!</span>

                                <div class="verification-copy">
                                    <strong>
                                        Verificatie nog vereist
                                    </strong>

                                    <p>
                                        Bevestig je e-mailadres om je accountstatus
                                        volledig te activeren.
                                    </p>
                                </div>
                            </div>

                            @if ($hasVerification)
                                <a
                                    class="account-action"
                                    href="{{ route('verification.notice') }}"
                                >
                                    Nu verifiëren
                                </a>
                            @endif
                        </div>
                    @endif
                </section>

                <section
                    class="account-panel studio-reveal"
                    id="recovery"
                >
                    <div class="panel-head">
                        <div>
                            <span class="panel-index">
                                03 / Account recovery
                            </span>

                            <h2 class="panel-title">
                                Herstel-e-mailadres
                            </h2>

                            <p class="panel-copy">
                                Een hersteladres wordt pas actief nadat je de
                                6-cijferige verificatiecode hebt bevestigd.
                                Zo kan alleen een e-mailadres waar jij toegang
                                toe hebt worden gebruikt voor account recovery.
                            </p>
                        </div>

                        <span class="panel-badge">
                            {{ $hasVerifiedRecoveryEmail
                                ? 'Verified'
                                : 'Recovery' }}
                        </span>
                    </div>

                    <div class="verification {{ $hasVerifiedRecoveryEmail ? 'verified' : 'pending' }}">
                        <div class="verification-main">
                            <span class="verification-icon">
                                {{ $hasVerifiedRecoveryEmail ? '✓' : '!' }}
                            </span>

                            <div class="verification-copy">
                                <strong>
                                    @if ($hasVerifiedRecoveryEmail)
                                        Herstel-e-mailadres geverifieerd
                                    @elseif ($hasRecoveryEmail)
                                        Bestaand hersteladres moet nog worden geverifieerd
                                    @else
                                        Nog geen geverifieerd herstel-e-mailadres
                                    @endif
                                </strong>

                                <p>
                                    @if ($hasVerifiedRecoveryEmail)
                                        {{ $recoveryEmail }}
                                        @if ($recoveryVerifiedAt)
                                            · bevestigd op {{ $recoveryVerifiedAt }}
                                        @endif
                                    @elseif ($hasRecoveryEmail)
                                        Voer hieronder {{ $recoveryEmail }} in en
                                        verstuur een verificatiecode om het adres
                                        veilig te activeren.
                                    @else
                                        Voeg hieronder een apart e-mailadres toe.
                                        Het mag niet hetzelfde zijn als
                                        {{ $user->email }}.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('account.recovery-email.send') }}"
                        style="margin-top: 18px;"
                    >
                        @csrf

                        <div class="form-grid">
                            <div class="field full">
                                <div class="field-label">
                                    <label for="recovery_email">
                                        {{ $hasVerifiedRecoveryEmail
                                            ? 'Nieuw herstel-e-mailadres'
                                            : 'Herstel-e-mailadres' }}
                                    </label>

                                    @error('recovery_email')
                                        <span class="field-error">
                                            {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <input
                                    class="account-input"
                                    id="recovery_email"
                                    type="email"
                                    name="recovery_email"
                                    value="{{ old('recovery_email', $recoveryEmail) }}"
                                    placeholder="bijvoorbeeld: herstel@example.com"
                                    autocomplete="email"
                                    inputmode="email"
                                    autocapitalize="none"
                                    spellcheck="false"
                                    maxlength="255"
                                    required
                                >
                            </div>

                            <div class="field full">
                                <div class="form-note">
                                    <span class="form-note-mark">i</span>

                                    <span>
                                        Na verzenden ontvang je op dit adres een
                                        tijdelijke 6-cijferige code. Je huidige
                                        geverifieerde hersteladres blijft actief
                                        totdat een nieuw adres succesvol is bevestigd.
                                    </span>
                                </div>
                            </div>
                        </div>

                        <button
                            class="account-submit"
                            type="submit"
                        >
                            {{ $hasVerifiedRecoveryEmail
                                ? 'Nieuw adres verifiëren'
                                : 'Verificatiecode sturen' }}
                            <span aria-hidden="true">→</span>
                        </button>
                    </form>

                    @if ($hasVerifiedRecoveryEmail)
                        <form
                            method="POST"
                            action="{{ route('account.recovery-email.destroy') }}"
                            style="margin-top: 10px;"
                            onsubmit="return confirm('Herstel-e-mailadres verwijderen?');"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                class="account-action"
                                type="submit"
                            >
                                Herstel-e-mailadres verwijderen
                            </button>
                        </form>
                    @endif
                </section>

                <section
                    class="account-panel studio-reveal"
                    id="password"
                >
                    <div class="panel-head">
                        <div>
                            <span class="panel-index">
                                04 / Password
                            </span>

                            <h2 class="panel-title">
                                Wachtwoord wijzigen
                            </h2>

                            <p class="panel-copy">
                                Gebruik een sterk en uniek wachtwoord voor je
                                Mashal Studio-account.
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

                        <div class="form-grid">
                            <div class="field full">
                                <div class="field-label">
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
                                        class="account-input with-toggle"
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

                            <div class="field">
                                <div class="field-label">
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
                                        class="account-input with-toggle"
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

                            <div class="field">
                                <div class="field-label">
                                    <label for="password_confirmation">
                                        Wachtwoord bevestigen
                                    </label>
                                </div>

                                <div class="input-wrap">
                                    <input
                                        class="account-input with-toggle"
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

                            <div class="field full">
                                <div class="form-note">
                                    <span class="form-note-mark">✓</span>

                                    <span>
                                        Gebruik minimaal 8 tekens en kies bij voorkeur
                                        een lang wachtwoord dat je nergens anders gebruikt.
                                    </span>
                                </div>
                            </div>
                        </div>

                        <button
                            class="account-submit"
                            type="submit"
                        >
                            Wachtwoord bijwerken
                            <span aria-hidden="true">→</span>
                        </button>
                    </form>
                </section>

                <section
                    class="account-panel studio-reveal"
                    id="overview"
                >
                    <div class="panel-head">
                        <div>
                            <span class="panel-index">
                                05 / Overview
                            </span>

                            <h2 class="panel-title">
                                Accountinformatie
                            </h2>

                            <p class="panel-copy">
                                Een compact overzicht van je huidige account,
                                loginmethode en registratiestatus.
                            </p>
                        </div>
                    </div>

                    <div class="overview-grid">
                        <div class="overview-card">
                            <small>Naam</small>
                            <strong title="{{ $user->name }}">
                                {{ $user->name }}
                            </strong>
                        </div>

                        <div class="overview-card">
                            <small>E-mailadres</small>
                            <strong title="{{ $user->email }}">
                                {{ $user->email }}
                            </strong>
                        </div>

                        <div class="overview-card">
                            <small>Loginmethode</small>
                            <strong>{{ $accountProviderLabel }}</strong>
                        </div>

                        <div class="overview-card">
                            <small>Recovery</small>
                            <strong
                                title="{{ $hasRecoveryEmail ? $recoveryEmail : 'Niet ingesteld' }}"
                            >
                                @if ($hasVerifiedRecoveryEmail)
                                    Verified · {{ $recoveryEmail }}
                                @elseif ($hasRecoveryEmail)
                                    Pending verification
                                @else
                                    Niet ingesteld
                                @endif
                            </strong>
                        </div>

                        <div class="overview-card">
                            <small>Aangemaakt</small>
                            <strong>
                                {{ optional($user->created_at)->format('d-m-Y H:i') ?? 'Onbekend' }}
                            </strong>
                        </div>
                    </div>
                </section>

                <section
                    class="account-panel studio-reveal"
                    id="library"
                >
                    <div class="panel-head">
                        <div>
                            <span class="panel-index">
                                06 / Image library
                            </span>

                            <h2 class="panel-title">
                                Recente afbeeldingen
                            </h2>

                            <p class="panel-copy">
                                Je zes meest recente afbeeldingsprojecten,
                                inclusief echte private previews en opgeslagen versies.
                            </p>
                        </div>

                        <span class="panel-badge">
                            {{ $imageCount }}
                            {{ $imageCount === 1 ? 'project' : 'projects' }}
                        </span>
                    </div>

                    @if ($latestImages->count())
                        <div class="library-grid">
                            @foreach ($latestImages as $image)
                                @php
                                    $imageName =
                                        $image->display_name
                                        ?? $image->original_name
                                        ?? ('Afbeelding #' . $image->id);

                                    $imageFormat =
                                        $image->format_label
                                        ?? strtoupper(
                                            str_replace(
                                                'image/',
                                                '',
                                                (string) $image->mime_type
                                            )
                                        );

                                    $imageSize =
                                        $image->formatted_file_size
                                        ?? number_format(
                                            ((int) ($image->file_size ?? 0)) / 1024,
                                            1
                                        ) . ' KB';
                                @endphp

                                <article class="project-card">
                                    <div class="project-preview">
                                        @if ($hasImagesFile)
                                            <img
                                                src="{{ route('images.file', $image) }}"
                                                alt="{{ $imageName }}"
                                                loading="lazy"
                                                decoding="async"
                                            >
                                        @endif

                                        <span class="project-format">
                                            {{ $imageFormat }}
                                        </span>

                                        <span class="project-size">
                                            {{ $image->width ?? '?' }}
                                            ×
                                            {{ $image->height ?? '?' }}
                                        </span>
                                    </div>

                                    <div class="project-body">
                                        <div
                                            class="project-name"
                                            title="{{ $imageName }}"
                                        >
                                            {{ $imageName }}
                                        </div>

                                        <div class="project-meta">
                                            <span>{{ $imageSize }}</span>
                                            <span>{{ $image->versions_count ?? 0 }} versies</span>
                                            <span>{{ $image->created_at?->format('d-m-Y') }}</span>
                                        </div>

                                        <div class="project-actions">
                                            @if ($hasImagesEditor)
                                                <a
                                                    class="project-link"
                                                    href="{{ route('images.editor', $image) }}"
                                                >
                                                    Open editor
                                                </a>
                                            @endif

                                            @if ($hasImagesDownload)
                                                <a
                                                    class="project-link"
                                                    href="{{ route('images.download', $image) }}"
                                                >
                                                    Download
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        @if ($hasImagesIndex)
                            <div style="margin-top: 16px;">
                                <a
                                    class="account-action"
                                    href="{{ route('images.index') }}"
                                >
                                    Bekijk volledige bibliotheek
                                    <span aria-hidden="true">→</span>
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="empty-library">
                            <div class="empty-library-mark">
                                M
                            </div>

                            <strong>
                                Nog geen afbeeldingsprojecten
                            </strong>

                            <p>
                                Upload je eerste afbeelding vanaf de Studio-homepage.
                                Na het uploaden verschijnt het project automatisch
                                in jouw persoonlijke bibliotheek.
                            </p>

                            <a
                                class="account-action primary"
                                href="{{ route('home') }}#upload"
                            >
                                Eerste afbeelding uploaden
                            </a>
                        </div>
                    @endif
                </section>

                <section
                    class="account-panel studio-reveal"
                    id="security"
                >
                    <div class="panel-head">
                        <div>
                            <span class="panel-index">
                                07 / Security
                            </span>

                            <h2 class="panel-title">
                                Accountbeveiliging
                            </h2>

                            <p class="panel-copy">
                                Je account vormt de toegangspoort tot je persoonlijke
                                originelen en opgeslagen bewerkingsversies.
                            </p>
                        </div>

                        @if ($hasSecurity)
                            <a
                                class="account-action"
                                href="{{ route('security.index') }}"
                            >
                                Open security
                            </a>
                        @endif
                    </div>

                    <div class="security-grid">
                        <article class="security-card">
                            <small>Recovery</small>
                            <strong>
                                {{ $hasVerifiedRecoveryEmail
                                    ? 'Hersteladres geverifieerd'
                                    : 'Hersteladres niet geverifieerd' }}
                            </strong>
                            <p>
                                {{ $hasVerifiedRecoveryEmail
                                    ? 'Je kunt de account-recoveryflow gebruiken als je je login-e-mailadres vergeet.'
                                    : 'Verifieer hierboven eerst een herstel-e-mailadres voordat account recovery beschikbaar wordt.' }}
                            </p>
                        </article>

                        <article class="security-card">
                            <small>Verification</small>
                            <strong>
                                {{ $user->email_verified_at
                                    ? 'E-mail bevestigd'
                                    : 'Verificatie vereist' }}
                            </strong>
                            <p>
                                Je e-mailstatus wordt gebruikt als extra controle
                                voor de betrouwbaarheid van je account.
                            </p>
                        </article>

                        <article class="security-card">
                            <small>Password</small>
                            <strong>Persoonlijk wachtwoord</strong>
                            <p>
                                Je kunt je wachtwoord hierboven wijzigen zonder
                                afbeeldingsprojecten of versies kwijt te raken.
                            </p>
                        </article>

                        <article class="security-card">
                            <small>Image ownership</small>
                            <strong>Projecten gekoppeld aan jouw account</strong>
                            <p>
                                Afbeeldingen en bewerkingsversies blijven gekoppeld
                                aan de ingelogde gebruiker.
                            </p>
                        </article>
                    </div>
                </section>

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

    let profilePreviewUrl = null;

    function revokePreviewUrl() {
        if (!profilePreviewUrl) {
            return;
        }

        URL.revokeObjectURL(profilePreviewUrl);
        profilePreviewUrl = null;
    }

    function humanFileSize(bytes) {
        if (
            !Number.isFinite(bytes) ||
            bytes <= 0
        ) {
            return '0 KB';
        }

        const mb =
            bytes /
            (1024 * 1024);

        if (mb >= 1) {
            return (
                mb.toFixed(
                    mb >= 10 ? 1 : 2
                ) +
                ' MB'
            );
        }

        return (
            Math.max(
                1,
                Math.round(bytes / 1024)
            ) +
            ' KB'
        );
    }

    photoInput?.addEventListener(
        'change',
        function () {
            const file =
                photoInput.files?.[0];

            if (!file) {
                if (photoFilename) {
                    photoFilename.textContent =
                        'Geen nieuw bestand geselecteerd.';
                }

                return;
            }

            const allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            if (!allowedTypes.includes(file.type)) {
                photoInput.value = '';

                if (photoFilename) {
                    photoFilename.textContent =
                        'Gebruik een JPG, PNG of WEBP-afbeelding.';
                }

                return;
            }

            const maxSize =
                5 *
                1024 *
                1024;

            if (file.size > maxSize) {
                photoInput.value = '';

                if (photoFilename) {
                    photoFilename.textContent =
                        'De profielfoto mag maximaal 5 MB zijn.';
                }

                return;
            }

            revokePreviewUrl();

            profilePreviewUrl =
                URL.createObjectURL(file);

            if (photoPreview) {
                photoPreview.innerHTML = '';

                const image =
                    document.createElement('img');

                image.src =
                    profilePreviewUrl;

                image.alt =
                    'Voorbeeld van nieuwe profielfoto';

                photoPreview.appendChild(image);
            }

            if (photoFilename) {
                photoFilename.textContent =
                    file.name +
                    ' · ' +
                    humanFileSize(file.size);
            }

            if (removePhoto) {
                removePhoto.checked = false;
            }
        }
    );

    removePhoto?.addEventListener(
        'change',
        function () {
            if (
                removePhoto.checked &&
                photoInput
            ) {
                photoInput.value = '';

                if (photoFilename) {
                    photoFilename.textContent =
                        'Eigen profielfoto wordt verwijderd na opslaan.';
                }
            }
        }
    );

    document
        .querySelectorAll('[data-toggle-password]')
        .forEach(function (button) {
            button.addEventListener(
                'click',
                function () {
                    const inputId =
                        button.getAttribute(
                            'data-toggle-password'
                        );

                    const input =
                        document.getElementById(inputId);

                    if (!input) {
                        return;
                    }

                    const isHidden =
                        input.type === 'password';

                    input.type =
                        isHidden
                            ? 'text'
                            : 'password';

                    button.textContent =
                        isHidden
                            ? 'Verbergen'
                            : 'Tonen';
                }
            );
        });

    document
        .querySelectorAll('.account-nav-link[href^="#"]')
        .forEach(function (anchor) {
            anchor.addEventListener(
                'click',
                function (event) {
                    const href =
                        anchor.getAttribute('href');

                    if (
                        !href ||
                        href === '#'
                    ) {
                        return;
                    }

                    const target =
                        document.querySelector(href);

                    if (!target) {
                        return;
                    }

                    event.preventDefault();

                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            );
        });

    window.addEventListener(
        'beforeunload',
        revokePreviewUrl
    );
});
</script>
@endpush
