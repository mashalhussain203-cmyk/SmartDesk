@extends('layouts.site-layout')

@section('title', 'Mashal Studio | Resize, Edit & Save Images')

@section(
    'meta_description',
    'Upload JPG, PNG of WEBP-afbeeldingen, bewerk ze in Mashal Studio en bewaar nieuwe versies in je persoonlijke image workspace.'
)

@push('styles')
<style>
    :root {
        --ms-bg: #09090b;
        --ms-panel: #111114;
        --ms-panel-soft: #151519;
        --ms-panel-strong: #1a1a1f;

        --ms-text: #f7f7f8;
        --ms-text-soft: #c5c5cc;
        --ms-muted: #85858f;
        --ms-muted-2: #5e5e68;

        --ms-line: rgba(255,255,255,.08);
        --ms-line-strong: rgba(255,255,255,.14);

        --ms-accent: #735cff;
        --ms-accent-2: #4986ff;
        --ms-accent-soft: rgba(115,92,255,.12);

        --ms-green: #61d38f;
        --ms-red: #ff7b7b;

        --ms-radius-sm: 10px;
        --ms-radius-md: 16px;
        --ms-radius-lg: 24px;
        --ms-radius-xl: 32px;
    }

    * {
        box-sizing: border-box;
    }

    html {
        scroll-behavior: smooth;
    }

    .ms-home {
        min-height: 100vh;
        overflow: hidden;
        color: var(--ms-text);
        background:
            radial-gradient(
                circle at 80% -5%,
                rgba(115,92,255,.12),
                transparent 32rem
            ),
            radial-gradient(
                circle at 5% 32%,
                rgba(73,134,255,.05),
                transparent 28rem
            ),
            var(--ms-bg);
    }

    .ms-shell {
        width: min(calc(100% - 40px), 1280px);
        margin-inline: auto;
    }

    .ms-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 9px;

        color: #9287ff;

        font-size: 10px;
        font-weight: 750;
        letter-spacing: .13em;
        text-transform: uppercase;
    }

    .ms-eyebrow::before {
        content: "";
        width: 7px;
        height: 7px;
        border-radius: 2px;
        background: var(--ms-accent);
        box-shadow: 0 0 20px rgba(115,92,255,.6);
    }

    .ms-button {
        min-height: 48px;
        padding: 0 18px;

        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;

        border: 1px solid var(--ms-line);
        border-radius: 10px;

        color: #d4d4da;
        background: rgba(255,255,255,.025);

        text-decoration: none;

        font-size: 12px;
        font-weight: 700;

        cursor: pointer;

        transition:
            transform .18s ease,
            border-color .18s ease,
            background .18s ease;
    }

    .ms-button:hover {
        transform: translateY(-1px);
        border-color: var(--ms-line-strong);
        background: rgba(255,255,255,.045);
    }

    .ms-button-primary {
        border-color: transparent;
        color: #fff;
        background:
            linear-gradient(
                135deg,
                #765fff,
                #4e86ff
            );
        box-shadow:
            0 14px 34px rgba(89,73,224,.22);
    }

    .ms-button-primary:hover {
        background:
            linear-gradient(
                135deg,
                #826dff,
                #6193ff
            );
    }

    /* =========================================================
       HERO
       ========================================================= */

    .ms-hero {
        position: relative;
        padding: 110px 0 95px;
    }

    .ms-hero-grid {
        display: grid;
        grid-template-columns:
            minmax(0,.83fr)
            minmax(520px,1.17fr);

        gap: 72px;
        align-items: center;
    }

    .ms-hero-copy {
        max-width: 590px;
    }

    .ms-hero-title {
        margin: 22px 0 0;

        color: #fff;

        font-size: clamp(58px,6.4vw,96px);
        line-height: .91;
        font-weight: 650;
        letter-spacing: -.075em;
    }

    .ms-hero-title span {
        display: block;
        color: #777780;
    }

    .ms-hero-description {
        max-width: 520px;
        margin: 28px 0 0;

        color: #9999a2;

        font-size: 16px;
        line-height: 1.75;
    }

    .ms-hero-actions {
        margin-top: 32px;

        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .ms-trust-row {
        margin-top: 29px;

        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .ms-trust-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;

        color: #6d6d76;

        font-size: 10px;
        font-weight: 600;
    }

    .ms-trust-item::before {
        content: "";
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: var(--ms-green);
    }

    /* =========================================================
       PRODUCT WINDOW
       ========================================================= */

    .ms-studio {
        position: relative;
        padding: 8px;

        border: 1px solid rgba(255,255,255,.1);
        border-radius: 20px;

        background:
            linear-gradient(
                145deg,
                rgba(255,255,255,.06),
                rgba(255,255,255,.012)
            );

        box-shadow:
            0 50px 120px rgba(0,0,0,.5);
    }

    .ms-studio::before {
        content: "";
        position: absolute;
        inset: -1px;
        z-index: -1;

        border-radius: inherit;

        background:
            linear-gradient(
                140deg,
                rgba(115,92,255,.28),
                transparent 26%,
                transparent 74%,
                rgba(73,134,255,.14)
            );
    }

    .ms-window {
        overflow: hidden;

        border-radius: 14px;

        background: #0e0e11;
    }

    .ms-window-top {
        min-height: 52px;
        padding: 0 15px;

        display: grid;
        grid-template-columns: auto 1fr auto;
        align-items: center;

        border-bottom: 1px solid var(--ms-line);

        background: #131316;
    }

    .ms-window-dots {
        display: flex;
        gap: 6px;
    }

    .ms-window-dot {
        width: 7px;
        height: 7px;

        border-radius: 50%;

        background: #39393f;
    }

    .ms-window-dot:first-child {
        background: #7764ff;
    }

    .ms-window-title {
        color: #575761;

        font-size: 9px;
        font-weight: 700;
        letter-spacing: .12em;
        text-align: center;
        text-transform: uppercase;
    }

    .ms-secure {
        display: inline-flex;
        align-items: center;
        gap: 7px;

        color: #72727b;

        font-size: 9px;
        font-weight: 650;
    }

    .ms-secure::before {
        content: "";

        width: 6px;
        height: 6px;

        border-radius: 50%;

        background: var(--ms-green);
    }

    .ms-window-body {
        padding: 12px;
    }

    /* =========================================================
       UPLOAD
       ========================================================= */

    .ms-alert {
        margin-bottom: 12px;
        padding: 12px 14px;

        border-radius: 9px;

        font-size: 11px;
        line-height: 1.6;
    }

    .ms-alert-success {
        border: 1px solid rgba(97,211,143,.16);
        color: #9fe1b7;
        background: rgba(97,211,143,.05);
    }

    .ms-alert-error {
        border: 1px solid rgba(255,123,123,.16);
        color: #f2aaaa;
        background: rgba(255,123,123,.05);
    }

    .ms-file-input {
        position: fixed;
        left: -10000px;

        width: 1px;
        height: 1px;

        opacity: 0;
        overflow: hidden;
    }

    .ms-dropzone {
        position: relative;

        min-height: 455px;
        padding: 34px;

        display: grid;
        place-items: center;

        overflow: hidden;

        border: 1px dashed rgba(115,92,255,.32);
        border-radius: 11px;

        background:
            radial-gradient(
                circle at 50% 25%,
                rgba(115,92,255,.1),
                transparent 15rem
            ),
            #0b0b0d;

        cursor: pointer;

        transition:
            border-color .18s ease,
            background .18s ease;
    }

    .ms-dropzone::before {
        content: "";
        position: absolute;
        inset: 0;

        opacity: .4;
        pointer-events: none;

        background-image:
            linear-gradient(
                rgba(255,255,255,.025) 1px,
                transparent 1px
            ),
            linear-gradient(
                90deg,
                rgba(255,255,255,.025) 1px,
                transparent 1px
            );

        background-size: 30px 30px;

        mask-image:
            radial-gradient(
                circle at center,
                #000,
                transparent 68%
            );
    }

    .ms-dropzone:hover,
    .ms-dropzone.is-dragging {
        border-color: rgba(115,92,255,.7);

        background:
            radial-gradient(
                circle at 50% 25%,
                rgba(115,92,255,.15),
                transparent 17rem
            ),
            #0c0c0f;
    }

    .ms-dropzone.has-file {
        border-style: solid;
        border-color: rgba(97,211,143,.22);
    }

    .ms-upload-empty {
        position: relative;
        z-index: 2;

        width: 100%;
        max-width: 430px;

        text-align: center;
    }

    .ms-upload-icon {
        width: 68px;
        height: 68px;

        margin: 0 auto 23px;

        display: grid;
        place-items: center;

        border: 1px solid rgba(115,92,255,.22);
        border-radius: 14px;

        color: #a69eff;

        background: rgba(115,92,255,.07);

        font-size: 24px;
    }

    .ms-upload-empty h2 {
        margin: 0;

        color: #f4f4f6;

        font-size: 28px;
        line-height: 1.08;
        font-weight: 620;
        letter-spacing: -.045em;
    }

    .ms-upload-empty p {
        max-width: 350px;
        margin: 12px auto 0;

        color: #74747e;

        font-size: 12px;
        line-height: 1.75;
    }

    .ms-upload-choose {
        min-height: 46px;

        margin-top: 24px;
        padding: 0 18px;

        border: 0;
        border-radius: 9px;

        color: #fff;

        background:
            linear-gradient(
                135deg,
                #735cff,
                #4e86ff
            );

        box-shadow:
            0 14px 35px rgba(76,62,207,.2);

        font-size: 11px;
        font-weight: 750;

        cursor: pointer;
    }

    .ms-format-row {
        margin-top: 18px;

        display: flex;
        justify-content: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .ms-format {
        min-height: 25px;
        padding: 0 8px;

        display: inline-flex;
        align-items: center;

        border: 1px solid var(--ms-line);
        border-radius: 6px;

        color: #595963;
        background: #101013;

        font-size: 8px;
        font-weight: 750;
        letter-spacing: .08em;
    }

    /* =========================================================
       PREVIEW
       ========================================================= */

    .ms-preview {
        position: relative;
        z-index: 3;

        display: none;
        width: 100%;
    }

    .ms-dropzone.has-file .ms-upload-empty {
        display: none;
    }

    .ms-dropzone.has-file .ms-preview {
        display: block;
    }

    .ms-preview-canvas {
        position: relative;

        overflow: hidden;

        aspect-ratio: 16 / 10;

        border: 1px solid var(--ms-line);
        border-radius: 9px;

        background:
            linear-gradient(
                45deg,
                #151519 25%,
                transparent 25%
            ),
            linear-gradient(
                -45deg,
                #151519 25%,
                transparent 25%
            ),
            linear-gradient(
                45deg,
                transparent 75%,
                #151519 75%
            ),
            linear-gradient(
                -45deg,
                transparent 75%,
                #151519 75%
            ),
            #0f0f12;

        background-size: 20px 20px;
        background-position:
            0 0,
            0 10px,
            10px -10px,
            -10px 0;
    }

    .ms-preview-canvas img {
        width: 100%;
        height: 100%;

        display: block;

        object-fit: contain;
    }

    .ms-preview-label {
        position: absolute;
        left: 10px;
        top: 10px;

        min-height: 25px;
        padding: 0 8px;

        display: inline-flex;
        align-items: center;

        border: 1px solid var(--ms-line);
        border-radius: 6px;

        color: #d2d2d7;
        background: rgba(10,10,12,.82);

        font-size: 8px;
        font-weight: 750;
    }

    .ms-preview-bottom {
        margin-top: 13px;

        display: grid;
        grid-template-columns: minmax(0,1fr) auto;
        gap: 16px;
        align-items: center;
    }

    .ms-preview-info {
        min-width: 0;
    }

    .ms-preview-info strong {
        display: block;

        overflow: hidden;

        color: #eeeef0;

        font-size: 12px;
        font-weight: 650;

        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ms-preview-info span {
        display: block;
        margin-top: 4px;

        color: #64646e;

        font-size: 9px;
    }

    .ms-preview-actions {
        display: flex;
        gap: 7px;
    }

    .ms-preview-change,
    .ms-preview-submit {
        min-height: 40px;
        padding: 0 13px;

        border-radius: 8px;

        font-size: 9px;
        font-weight: 750;

        cursor: pointer;
    }

    .ms-preview-change {
        border: 1px solid var(--ms-line);

        color: #b8b8bf;
        background: #151518;
    }

    .ms-preview-submit {
        border: 0;

        color: #fff;

        background:
            linear-gradient(
                135deg,
                #735cff,
                #4f84ff
            );
    }

    .ms-preview-submit:disabled {
        opacity: .55;
        cursor: wait;
    }

    .ms-upload-meta {
        padding: 13px 3px 2px;

        display: flex;
        justify-content: space-between;
        gap: 12px;

        color: #51515a;

        font-size: 9px;
    }

    /* =========================================================
       TOOL STRIP
       ========================================================= */

    .ms-tools-strip {
        border-top: 1px solid var(--ms-line);
        border-bottom: 1px solid var(--ms-line);

        background: rgba(255,255,255,.01);
    }

    .ms-tools-grid {
        display: grid;
        grid-template-columns: repeat(6,1fr);
    }

    .ms-tool-chip {
        min-height: 92px;
        padding: 18px;

        display: flex;
        align-items: center;
        gap: 12px;
    }

    .ms-tool-chip + .ms-tool-chip {
        border-left: 1px solid var(--ms-line);
    }

    .ms-tool-chip-icon {
        width: 35px;
        height: 35px;
        flex: 0 0 35px;

        display: grid;
        place-items: center;

        border: 1px solid var(--ms-line);
        border-radius: 8px;

        color: #978eff;
        background: #111114;
    }

    .ms-tool-chip strong {
        display: block;

        color: #d6d6da;

        font-size: 10px;
    }

    .ms-tool-chip span {
        display: block;
        margin-top: 4px;

        color: #5e5e67;

        font-size: 8px;
    }

    /* =========================================================
       PRODUCT SECTION
       ========================================================= */

    .ms-section {
        padding: 120px 0;
    }

    .ms-section-soft {
        border-top: 1px solid var(--ms-line);
        border-bottom: 1px solid var(--ms-line);

        background: #0c0c0f;
    }

    .ms-section-head {
        max-width: 760px;
        margin-bottom: 48px;
    }

    .ms-section-head h2 {
        margin: 16px 0 0;

        color: #f4f4f5;

        font-size: clamp(42px,4.7vw,66px);
        line-height: .98;
        font-weight: 620;
        letter-spacing: -.06em;
    }

    .ms-section-head p {
        max-width: 590px;
        margin: 18px 0 0;

        color: #777781;

        font-size: 13px;
        line-height: 1.8;
    }

    /* =========================================================
       PRODUCT DEMO
       ========================================================= */

    .ms-product-demo {
        display: grid;
        grid-template-columns: 240px minmax(0,1fr) 250px;

        min-height: 530px;

        overflow: hidden;

        border: 1px solid var(--ms-line);
        border-radius: 16px;

        background: #0e0e11;

        box-shadow:
            0 35px 90px rgba(0,0,0,.26);
    }

    .ms-product-sidebar {
        padding: 18px;

        border-right: 1px solid var(--ms-line);

        background: #111114;
    }

    .ms-product-sidebar-title {
        margin-bottom: 16px;

        color: #65656e;

        font-size: 8px;
        font-weight: 750;
        letter-spacing: .13em;
        text-transform: uppercase;
    }

    .ms-editor-tool {
        min-height: 46px;
        padding: 0 12px;

        display: flex;
        align-items: center;
        gap: 10px;

        border-radius: 8px;

        color: #898991;

        font-size: 10px;
        font-weight: 650;
    }

    .ms-editor-tool.active {
        color: #eeeef1;
        background: rgba(115,92,255,.1);
    }

    .ms-editor-tool-icon {
        width: 26px;
        height: 26px;

        display: grid;
        place-items: center;

        border: 1px solid var(--ms-line);
        border-radius: 6px;

        color: #9188ff;
    }

    .ms-product-canvas {
        position: relative;

        padding: 46px;

        display: grid;
        place-items: center;

        background:
            linear-gradient(
                45deg,
                #121216 25%,
                transparent 25%
            ),
            linear-gradient(
                -45deg,
                #121216 25%,
                transparent 25%
            ),
            linear-gradient(
                45deg,
                transparent 75%,
                #121216 75%
            ),
            linear-gradient(
                -45deg,
                transparent 75%,
                #121216 75%
            ),
            #0b0b0d;

        background-size: 28px 28px;
        background-position:
            0 0,
            0 14px,
            14px -14px,
            -14px 0;
    }

    .ms-demo-art {
        width: min(100%, 430px);

        aspect-ratio: 4 / 3;

        border-radius: 4px;

        background:
            radial-gradient(
                circle at 28% 28%,
                rgba(255,255,255,.32),
                transparent 5rem
            ),
            radial-gradient(
                circle at 75% 30%,
                rgba(115,92,255,.8),
                transparent 10rem
            ),
            linear-gradient(
                135deg,
                #1b2333,
                #2f235f 52%,
                #15161d
            );

        box-shadow:
            0 25px 80px rgba(0,0,0,.48);
    }

    .ms-product-settings {
        padding: 18px;

        border-left: 1px solid var(--ms-line);

        background: #111114;
    }

    .ms-settings-title {
        margin-bottom: 20px;

        color: #d8d8dd;

        font-size: 11px;
        font-weight: 700;
    }

    .ms-field {
        margin-bottom: 18px;
    }

    .ms-field label {
        display: block;
        margin-bottom: 7px;

        color: #666670;

        font-size: 8px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .ms-input-fake {
        min-height: 40px;
        padding: 0 11px;

        display: flex;
        align-items: center;
        justify-content: space-between;

        border: 1px solid var(--ms-line);
        border-radius: 7px;

        color: #c5c5ca;
        background: #0d0d10;

        font-size: 10px;
    }

    .ms-settings-button {
        min-height: 42px;
        width: 100%;

        border: 0;
        border-radius: 8px;

        color: #fff;
        background:
            linear-gradient(
                135deg,
                #735cff,
                #4e86ff
            );

        font-size: 10px;
        font-weight: 750;
    }

    /* =========================================================
       BENTO
       ========================================================= */

    .ms-bento {
        display: grid;
        grid-template-columns: repeat(12,1fr);
        gap: 12px;
    }

    .ms-bento-card {
        position: relative;

        min-height: 250px;
        padding: 24px;

        overflow: hidden;

        border: 1px solid var(--ms-line);
        border-radius: 14px;

        background: #101013;
    }

    .ms-bento-card:nth-child(1) {
        grid-column: span 7;
    }

    .ms-bento-card:nth-child(2) {
        grid-column: span 5;
    }

    .ms-bento-card:nth-child(3),
    .ms-bento-card:nth-child(4),
    .ms-bento-card:nth-child(5) {
        grid-column: span 4;
    }

    .ms-bento-number {
        color: #575761;

        font-size: 9px;
        font-weight: 750;
        letter-spacing: .1em;
    }

    .ms-bento-card h3 {
        margin: 48px 0 9px;

        color: #e9e9ec;

        font-size: 20px;
        font-weight: 620;
        letter-spacing: -.03em;
    }

    .ms-bento-card p {
        max-width: 460px;
        margin: 0;

        color: #6f6f78;

        font-size: 11px;
        line-height: 1.75;
    }

    .ms-bento-glow {
        position: absolute;
        right: -70px;
        bottom: -90px;

        width: 220px;
        height: 220px;

        border-radius: 50%;

        background: rgba(115,92,255,.12);

        filter: blur(40px);
    }

    /* =========================================================
       WORKFLOW
       ========================================================= */

    .ms-flow {
        display: grid;
        grid-template-columns: .75fr 1.25fr;
        gap: 80px;
        align-items: start;
    }

    .ms-flow-copy {
        position: sticky;
        top: 100px;
    }

    .ms-flow-copy h2 {
        margin: 16px 0 16px;

        font-size: clamp(42px,4.7vw,64px);
        line-height: .98;
        font-weight: 620;
        letter-spacing: -.06em;
    }

    .ms-flow-copy p {
        max-width: 410px;
        margin: 0;

        color: #767680;

        font-size: 12px;
        line-height: 1.8;
    }

    .ms-flow-list {
        display: grid;
    }

    .ms-flow-step {
        position: relative;

        min-height: 120px;
        padding: 28px 0 28px 72px;

        border-top: 1px solid var(--ms-line);
    }

    .ms-flow-step:last-child {
        border-bottom: 1px solid var(--ms-line);
    }

    .ms-flow-step-index {
        position: absolute;
        left: 0;
        top: 26px;

        width: 42px;
        height: 42px;

        display: grid;
        place-items: center;

        border: 1px solid var(--ms-line);
        border-radius: 8px;

        color: #9087ff;
        background: #111114;

        font-size: 9px;
        font-weight: 750;
    }

    .ms-flow-step h3 {
        margin: 0;

        color: #dedee2;

        font-size: 18px;
        font-weight: 620;
    }

    .ms-flow-step p {
        max-width: 620px;
        margin: 8px 0 0;

        color: #6d6d76;

        font-size: 11px;
        line-height: 1.75;
    }

    /* =========================================================
       WORKSPACE CTA
       ========================================================= */

    .ms-workspace {
        padding: 55px;

        display: grid;
        grid-template-columns: 1fr .85fr;
        gap: 65px;
        align-items: center;

        border: 1px solid var(--ms-line);
        border-radius: 18px;

        background:
            radial-gradient(
                circle at 85% 20%,
                rgba(115,92,255,.1),
                transparent 20rem
            ),
            #101013;
    }

    .ms-workspace h2 {
        margin: 16px 0 17px;

        font-size: clamp(42px,4.5vw,62px);
        line-height: .98;
        font-weight: 620;
        letter-spacing: -.06em;
    }

    .ms-workspace p {
        max-width: 570px;
        margin: 0;

        color: #797982;

        font-size: 12px;
        line-height: 1.8;
    }

    .ms-workspace-actions {
        margin-top: 25px;

        display: flex;
        gap: 9px;
        flex-wrap: wrap;
    }

    .ms-library-preview {
        padding: 14px;

        border: 1px solid var(--ms-line);
        border-radius: 12px;

        background: #0b0b0e;

        box-shadow:
            0 30px 80px rgba(0,0,0,.35);
    }

    .ms-library-head {
        padding: 2px 2px 13px;

        display: flex;
        justify-content: space-between;
        gap: 10px;

        border-bottom: 1px solid var(--ms-line);
    }

    .ms-library-head strong {
        font-size: 10px;
    }

    .ms-library-head span {
        color: #5f5f69;
        font-size: 8px;
    }

    .ms-library-grid {
        margin-top: 11px;

        display: grid;
        grid-template-columns: repeat(3,1fr);
        gap: 7px;
    }

    .ms-library-thumb {
        position: relative;

        aspect-ratio: 1;

        overflow: hidden;

        border: 1px solid var(--ms-line);
        border-radius: 7px;

        background:
            radial-gradient(
                circle at 35% 30%,
                rgba(115,92,255,.42),
                transparent 4rem
            ),
            linear-gradient(
                145deg,
                #20202a,
                #101014
            );
    }

    .ms-library-thumb:nth-child(2),
    .ms-library-thumb:nth-child(5) {
        background:
            radial-gradient(
                circle at 70% 25%,
                rgba(73,134,255,.4),
                transparent 4rem
            ),
            linear-gradient(
                145deg,
                #19212d,
                #0e1116
            );
    }

    .ms-library-thumb:nth-child(3),
    .ms-library-thumb:nth-child(6) {
        background:
            radial-gradient(
                circle at 42% 70%,
                rgba(97,211,143,.27),
                transparent 4rem
            ),
            linear-gradient(
                145deg,
                #18201d,
                #0d1110
            );
    }

    /* =========================================================
       FAQ
       ========================================================= */

    .ms-faq-layout {
        display: grid;
        grid-template-columns: .72fr 1.28fr;
        gap: 80px;
        align-items: start;
    }

    .ms-faq-intro {
        position: sticky;
        top: 100px;
    }

    .ms-faq-intro h2 {
        margin: 16px 0 15px;

        font-size: clamp(42px,4.5vw,62px);
        line-height: .98;
        font-weight: 620;
        letter-spacing: -.06em;
    }

    .ms-faq-intro p {
        max-width: 390px;
        margin: 0;

        color: #71717a;

        font-size: 11px;
        line-height: 1.8;
    }

    .ms-faq-list {
        display: grid;
        gap: 8px;
    }

    .ms-faq-item {
        overflow: hidden;

        border: 1px solid var(--ms-line);
        border-radius: 10px;

        background: #101013;
    }

    .ms-faq-question {
        width: 100%;
        min-height: 64px;
        padding: 0 17px;

        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;

        border: 0;

        color: #d8d8dd;
        background: transparent;

        font-size: 11px;
        font-weight: 650;
        text-align: left;

        cursor: pointer;
    }

    .ms-faq-plus {
        position: relative;

        width: 25px;
        height: 25px;
        flex: 0 0 25px;

        border: 1px solid var(--ms-line);
        border-radius: 6px;

        background: #151519;
    }

    .ms-faq-plus::before,
    .ms-faq-plus::after {
        content: "";

        position: absolute;
        left: 50%;
        top: 50%;

        width: 9px;
        height: 1px;

        background: #8c83ff;

        transform: translate(-50%,-50%);
        transition: transform .2s ease;
    }

    .ms-faq-plus::after {
        transform:
            translate(-50%,-50%)
            rotate(90deg);
    }

    .ms-faq-item.open .ms-faq-plus::after {
        transform:
            translate(-50%,-50%)
            rotate(0);
    }

    .ms-faq-answer {
        display: grid;
        grid-template-rows: 0fr;

        transition:
            grid-template-rows .22s ease;
    }

    .ms-faq-answer > div {
        overflow: hidden;
    }

    .ms-faq-answer-inner {
        padding: 0 17px 18px;

        color: #6b6b75;

        font-size: 10px;
        line-height: 1.8;
    }

    .ms-faq-item.open .ms-faq-answer {
        grid-template-rows: 1fr;
    }

    /* =========================================================
       FINAL CTA
       ========================================================= */

    .ms-final {
        padding: 0 0 100px;
    }

    .ms-final-panel {
        position: relative;

        min-height: 410px;
        padding: 60px;

        display: flex;
        align-items: center;

        overflow: hidden;

        border: 1px solid var(--ms-line);
        border-radius: 18px;

        background:
            radial-gradient(
                circle at 80% 20%,
                rgba(115,92,255,.17),
                transparent 21rem
            ),
            radial-gradient(
                circle at 70% 110%,
                rgba(73,134,255,.09),
                transparent 24rem
            ),
            #101013;
    }

    .ms-final-panel::after {
        content: "M";

        position: absolute;
        right: -20px;
        bottom: -135px;

        color: rgba(255,255,255,.018);

        font-size: 450px;
        font-weight: 900;
        line-height: 1;
    }

    .ms-final-copy {
        position: relative;
        z-index: 2;

        max-width: 760px;
    }

    .ms-final-copy h2 {
        margin: 16px 0 17px;

        font-size: clamp(48px,5.7vw,78px);
        line-height: .94;
        font-weight: 620;
        letter-spacing: -.065em;
    }

    .ms-final-copy p {
        max-width: 590px;

        color: #7b7b85;

        font-size: 13px;
        line-height: 1.8;
    }

    .ms-final-actions {
        margin-top: 26px;

        display: flex;
        gap: 9px;
        flex-wrap: wrap;
    }

    /* =========================================================
       STICKY
       ========================================================= */

    .ms-sticky {
        position: fixed;
        left: 50%;
        bottom: 16px;
        z-index: 900;

        width: min(calc(100% - 28px), 520px);

        transform:
            translateX(-50%)
            translateY(140%);

        opacity: 0;
        pointer-events: none;

        transition:
            transform .25s ease,
            opacity .25s ease;
    }

    .ms-sticky.visible {
        transform:
            translateX(-50%)
            translateY(0);

        opacity: 1;
        pointer-events: auto;
    }

    .ms-sticky-inner {
        min-height: 58px;
        padding: 8px 8px 8px 15px;

        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;

        border: 1px solid var(--ms-line-strong);
        border-radius: 14px;

        background: rgba(17,17,20,.96);

        box-shadow:
            0 22px 60px rgba(0,0,0,.45);
    }

    .ms-sticky-copy {
        min-width: 0;
    }

    .ms-sticky-copy strong {
        display: block;

        color: #e3e3e6;

        font-size: 10px;
    }

    .ms-sticky-copy span {
        display: block;
        margin-top: 3px;

        color: #64646d;

        font-size: 8px;
    }

    .ms-sticky-link {
        min-height: 40px;
        padding: 0 15px;

        display: inline-flex;
        align-items: center;

        border-radius: 8px;

        color: #fff;

        background:
            linear-gradient(
                135deg,
                #735cff,
                #4e86ff
            );

        text-decoration: none;

        font-size: 9px;
        font-weight: 750;
    }

    /* =========================================================
       RESPONSIVE
       ========================================================= */

    @media (max-width: 1100px) {
        .ms-hero-grid {
            grid-template-columns: 1fr;
            gap: 60px;
        }

        .ms-hero-copy {
            max-width: 760px;
        }

        .ms-studio {
            max-width: 790px;
        }

        .ms-tools-grid {
            grid-template-columns: repeat(3,1fr);
        }

        .ms-tool-chip:nth-child(4) {
            border-left: 0;
        }

        .ms-tool-chip:nth-child(n+4) {
            border-top: 1px solid var(--ms-line);
        }

        .ms-product-demo {
            grid-template-columns: 190px minmax(0,1fr);
        }

        .ms-product-settings {
            display: none;
        }

        .ms-flow,
        .ms-workspace {
            grid-template-columns: 1fr;
        }

        .ms-flow-copy,
        .ms-faq-intro {
            position: static;
        }
    }

    @media (max-width: 820px) {
        .ms-bento-card:nth-child(n) {
            grid-column: span 6;
        }

        .ms-faq-layout {
            grid-template-columns: 1fr;
            gap: 35px;
        }
    }

    @media (max-width: 640px) {
        .ms-shell {
            width: calc(100% - 24px);
        }

        .ms-hero {
            padding: 60px 0 70px;
        }

        .ms-hero-title {
            font-size: clamp(50px,15vw,66px);
        }

        .ms-hero-description {
            font-size: 14px;
        }

        .ms-hero-actions,
        .ms-workspace-actions,
        .ms-final-actions {
            flex-direction: column;
        }

        .ms-button {
            width: 100%;
        }

        .ms-studio {
            padding: 5px;
            border-radius: 14px;
        }

        .ms-window {
            border-radius: 10px;
        }

        .ms-window-title {
            display: none;
        }

        .ms-dropzone {
            min-height: 390px;
            padding: 22px 14px;
        }

        .ms-preview-bottom {
            grid-template-columns: 1fr;
        }

        .ms-preview-actions {
            width: 100%;
        }

        .ms-preview-change,
        .ms-preview-submit {
            flex: 1;
        }

        .ms-tools-grid {
            grid-template-columns: 1fr 1fr;
        }

        .ms-tool-chip:nth-child(n) {
            border-top: 0;
            border-left: 0;
            border-bottom: 1px solid var(--ms-line);
        }

        .ms-tool-chip:nth-child(even) {
            border-left: 1px solid var(--ms-line);
        }

        .ms-section {
            padding: 85px 0;
        }

        .ms-product-demo {
            grid-template-columns: 1fr;
            min-height: auto;
        }

        .ms-product-sidebar {
            display: none;
        }

        .ms-product-canvas {
            min-height: 380px;
            padding: 30px 20px;
        }

        .ms-bento-card:nth-child(n) {
            grid-column: span 12;
        }

        .ms-workspace {
            padding: 30px 22px;
        }

        .ms-flow-step {
            padding-left: 62px;
        }

        .ms-final-panel {
            min-height: auto;
            padding: 46px 22px;
        }

        .ms-sticky-copy span {
            display: none;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            animation-duration: .001ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .001ms !important;
            scroll-behavior: auto !important;
        }
    }
</style>
@endpush

@section('content')
<div class="ms-home">

    {{-- =====================================================
         HERO
         ===================================================== --}}

    <section class="ms-hero" id="top">
        <div class="ms-shell">
            <div class="ms-hero-grid">

                <div class="ms-hero-copy">
                    <span class="ms-eyebrow">
                        Mashal Studio
                    </span>

                    <h1 class="ms-hero-title">
                        Bewerk beelden.
                        <span>
                            Houd je flow.
                        </span>
                    </h1>

                    <p class="ms-hero-description">
                        Resize, crop, rotate, flip, comprimeer en converteer
                        afbeeldingen vanuit één persoonlijke workspace.
                        Je origineel blijft altijd behouden.
                    </p>

                    <div class="ms-hero-actions">
                        <a
                            class="ms-button ms-button-primary"
                            href="#upload"
                        >
                            Upload afbeelding
                            <span aria-hidden="true">↗</span>
                        </a>

                        @auth
                            @if (\Illuminate\Support\Facades\Route::has('images.index'))
                                <a
                                    class="ms-button"
                                    href="{{ route('images.index') }}"
                                >
                                    Mijn afbeeldingen
                                </a>
                            @endif
                        @else
                            @if (\Illuminate\Support\Facades\Route::has('register'))
                                <a
                                    class="ms-button"
                                    href="{{ route('register') }}"
                                >
                                    Account maken
                                </a>
                            @endif
                        @endauth
                    </div>

                    <div class="ms-trust-row">
                        <span class="ms-trust-item">
                            JPG · PNG · WEBP
                        </span>

                        <span class="ms-trust-item">
                            Max. 20 MB
                        </span>

                        <span class="ms-trust-item">
                            Origineel blijft behouden
                        </span>
                    </div>
                </div>

                {{-- =====================================================
                     UPLOAD PRODUCT WINDOW
                     ===================================================== --}}

                <div
                    class="ms-studio"
                    id="upload"
                >
                    <div class="ms-window">

                        <div class="ms-window-top">
                            <div
                                class="ms-window-dots"
                                aria-hidden="true"
                            >
                                <span class="ms-window-dot"></span>
                                <span class="ms-window-dot"></span>
                                <span class="ms-window-dot"></span>
                            </div>

                            <div class="ms-window-title">
                                New image
                            </div>

                            <div class="ms-secure">
                                Secure
                            </div>
                        </div>

                        <div class="ms-window-body">

                            @if (session('success'))
                                <div class="ms-alert ms-alert-success">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="ms-alert ms-alert-error">
                                    {{ session('error') }}
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="ms-alert ms-alert-error">
                                    <strong>
                                        Controleer je upload
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

                            <form
                                id="imageUploadForm"
                                method="POST"
                                action="{{ route('images.upload') }}"
                                enctype="multipart/form-data"
                            >
                                @csrf

                                <input
                                    class="ms-file-input"
                                    id="imageInput"
                                    type="file"
                                    name="image"
                                    accept="image/jpeg,image/png,image/webp"
                                >

                                <div
                                    class="ms-dropzone"
                                    id="imageDropzone"
                                    role="button"
                                    tabindex="0"
                                    aria-label="Selecteer of sleep een afbeelding hierheen"
                                >
                                    <div class="ms-upload-empty">

                                        <div
                                            class="ms-upload-icon"
                                            aria-hidden="true"
                                        >
                                            ↑
                                        </div>

                                        <h2>
                                            Sleep je afbeelding hierheen
                                        </h2>

                                        <p>
                                            Of kies een bestand van je apparaat.
                                            Je ziet eerst een preview voordat de upload start.
                                        </p>

                                        <button
                                            class="ms-upload-choose"
                                            id="chooseImageButton"
                                            type="button"
                                        >
                                            Kies afbeelding
                                        </button>

                                        <div class="ms-format-row">
                                            <span class="ms-format">JPG</span>
                                            <span class="ms-format">PNG</span>
                                            <span class="ms-format">WEBP</span>
                                            <span class="ms-format">≤ 20 MB</span>
                                        </div>
                                    </div>

                                    <div class="ms-preview">

                                        <div class="ms-preview-canvas">
                                            <img
                                                id="imagePreview"
                                                src=""
                                                alt="Voorbeeld van geselecteerde afbeelding"
                                            >

                                            <span
                                                class="ms-preview-label"
                                                id="previewBadge"
                                            >
                                                Preview
                                            </span>
                                        </div>

                                        <div class="ms-preview-bottom">

                                            <div class="ms-preview-info">
                                                <strong id="previewFileName">
                                                    —
                                                </strong>

                                                <span id="previewFileMeta">
                                                    —
                                                </span>
                                            </div>

                                            <div class="ms-preview-actions">
                                                <button
                                                    class="ms-preview-change"
                                                    id="changeImageButton"
                                                    type="button"
                                                >
                                                    Wijzigen
                                                </button>

                                                <button
                                                    class="ms-preview-submit"
                                                    id="submitImageButton"
                                                    type="submit"
                                                >
                                                    Open editor
                                                </button>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </form>

                            <div class="ms-upload-meta">
                                <span>
                                    Client + servervalidatie
                                </span>

                                <span>
                                    Private workspace
                                </span>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- =====================================================
         TOOL STRIP
         ===================================================== --}}

    <section class="ms-tools-strip">
        <div class="ms-shell">
            <div class="ms-tools-grid">

                <div class="ms-tool-chip">
                    <div class="ms-tool-chip-icon">↔</div>
                    <div>
                        <strong>Resize</strong>
                        <span>Formaat wijzigen</span>
                    </div>
                </div>

                <div class="ms-tool-chip">
                    <div class="ms-tool-chip-icon">⌗</div>
                    <div>
                        <strong>Crop</strong>
                        <span>Exact uitsnijden</span>
                    </div>
                </div>

                <div class="ms-tool-chip">
                    <div class="ms-tool-chip-icon">↻</div>
                    <div>
                        <strong>Rotate</strong>
                        <span>Draaien</span>
                    </div>
                </div>

                <div class="ms-tool-chip">
                    <div class="ms-tool-chip-icon">⇆</div>
                    <div>
                        <strong>Flip</strong>
                        <span>Spiegelen</span>
                    </div>
                </div>

                <div class="ms-tool-chip">
                    <div class="ms-tool-chip-icon">↓</div>
                    <div>
                        <strong>Compress</strong>
                        <span>Kleiner exporteren</span>
                    </div>
                </div>

                <div class="ms-tool-chip">
                    <div class="ms-tool-chip-icon">◇</div>
                    <div>
                        <strong>Convert</strong>
                        <span>JPG · PNG · WEBP</span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- =====================================================
         PRODUCT PREVIEW
         ===================================================== --}}

    <section class="ms-section">
        <div class="ms-shell">

            <header class="ms-section-head">
                <span class="ms-eyebrow">
                    Editor
                </span>

                <h2>
                    Een echte workspace.
                    Geen losse tools.
                </h2>

                <p>
                    Open een afbeelding, kies je bewerking en maak nieuwe
                    versies zonder je oorspronkelijke bestand te vervangen.
                </p>
            </header>

            <div
                class="ms-product-demo"
                aria-hidden="true"
            >

                <aside class="ms-product-sidebar">
                    <div class="ms-product-sidebar-title">
                        Tools
                    </div>

                    <div class="ms-editor-tool active">
                        <span class="ms-editor-tool-icon">↔</span>
                        Resize
                    </div>

                    <div class="ms-editor-tool">
                        <span class="ms-editor-tool-icon">⌗</span>
                        Crop
                    </div>

                    <div class="ms-editor-tool">
                        <span class="ms-editor-tool-icon">↻</span>
                        Rotate
                    </div>

                    <div class="ms-editor-tool">
                        <span class="ms-editor-tool-icon">⇆</span>
                        Flip
                    </div>

                    <div class="ms-editor-tool">
                        <span class="ms-editor-tool-icon">↓</span>
                        Compress
                    </div>

                    <div class="ms-editor-tool">
                        <span class="ms-editor-tool-icon">◇</span>
                        Convert
                    </div>
                </aside>

                <div class="ms-product-canvas">
                    <div class="ms-demo-art"></div>
                </div>

                <aside class="ms-product-settings">

                    <div class="ms-settings-title">
                        Resize image
                    </div>

                    <div class="ms-field">
                        <label>
                            Width
                        </label>

                        <div class="ms-input-fake">
                            <span>1920</span>
                            <span>px</span>
                        </div>
                    </div>

                    <div class="ms-field">
                        <label>
                            Height
                        </label>

                        <div class="ms-input-fake">
                            <span>1080</span>
                            <span>px</span>
                        </div>
                    </div>

                    <div class="ms-field">
                        <label>
                            Aspect ratio
                        </label>

                        <div class="ms-input-fake">
                            <span>Locked</span>
                            <span>✓</span>
                        </div>
                    </div>

                    <button
                        class="ms-settings-button"
                        type="button"
                        tabindex="-1"
                    >
                        Create version
                    </button>

                </aside>

            </div>
        </div>
    </section>

    {{-- =====================================================
         BENTO FEATURES
         ===================================================== --}}

    <section class="ms-section ms-section-soft">
        <div class="ms-shell">

            <header class="ms-section-head">
                <span class="ms-eyebrow">
                    Built for versions
                </span>

                <h2>
                    Bewerk zonder iets kwijt te raken.
                </h2>

                <p>
                    Mashal Studio werkt rond één origineel en meerdere
                    afzonderlijke versies.
                </p>
            </header>

            <div class="ms-bento">

                <article class="ms-bento-card">
                    <span class="ms-bento-number">
                        01 / Original
                    </span>

                    <h3>
                        Je origineel blijft intact.
                    </h3>

                    <p>
                        Elke bewerking wordt opgeslagen als nieuw bestand.
                        Je bronbestand blijft beschikbaar als veilige basis.
                    </p>

                    <div class="ms-bento-glow"></div>
                </article>

                <article class="ms-bento-card">
                    <span class="ms-bento-number">
                        02 / Versions
                    </span>

                    <h3>
                        Bouw verder op eerdere versies.
                    </h3>

                    <p>
                        Selecteer later opnieuw het origineel of een eerder
                        resultaat als bron voor de volgende bewerking.
                    </p>
                </article>

                <article class="ms-bento-card">
                    <span class="ms-bento-number">
                        03 / Formats
                    </span>

                    <h3>
                        JPG, PNG en WEBP.
                    </h3>

                    <p>
                        Upload en exporteer de formaten die je dagelijks gebruikt.
                    </p>
                </article>

                <article class="ms-bento-card">
                    <span class="ms-bento-number">
                        04 / Private
                    </span>

                    <h3>
                        Persoonlijke bibliotheek.
                    </h3>

                    <p>
                        Projecten worden gekoppeld aan je account en blijven
                        overzichtelijk bij elkaar.
                    </p>
                </article>

                <article class="ms-bento-card">
                    <span class="ms-bento-number">
                        05 / Export
                    </span>

                    <h3>
                        Download wanneer jij wilt.
                    </h3>

                    <p>
                        Download het origineel of één specifieke versie uit je project.
                    </p>
                </article>

            </div>
        </div>
    </section>

    {{-- =====================================================
         WORKFLOW
         ===================================================== --}}

    <section class="ms-section">
        <div class="ms-shell">
            <div class="ms-flow">

                <div class="ms-flow-copy">
                    <span class="ms-eyebrow">
                        Workflow
                    </span>

                    <h2>
                        Van bestand naar eindversie.
                    </h2>

                    <p>
                        De flow blijft bewust kort. Geen ingewikkelde projectsetup,
                        gewoon uploaden en verder werken.
                    </p>
                </div>

                <div class="ms-flow-list">

                    <article class="ms-flow-step">
                        <div class="ms-flow-step-index">
                            01
                        </div>

                        <h3>
                            Upload
                        </h3>

                        <p>
                            Kies een JPG, PNG of WEBP-afbeelding tot maximaal 20 MB.
                        </p>
                    </article>

                    <article class="ms-flow-step">
                        <div class="ms-flow-step-index">
                            02
                        </div>

                        <h3>
                            Open de editor
                        </h3>

                        <p>
                            Het bestand wordt aan je persoonlijke image-project gekoppeld
                            zodra authenticatie nodig is afgerond.
                        </p>
                    </article>

                    <article class="ms-flow-step">
                        <div class="ms-flow-step-index">
                            03
                        </div>

                        <h3>
                            Maak een bewerking
                        </h3>

                        <p>
                            Gebruik resize, crop, rotate, flip, compress of convert
                            op je gekozen bron.
                        </p>
                    </article>

                    <article class="ms-flow-step">
                        <div class="ms-flow-step-index">
                            04
                        </div>

                        <h3>
                            Bewaar als versie
                        </h3>

                        <p>
                            Het resultaat komt naast je bestaande bestanden te staan
                            en kan later opnieuw worden gebruikt of gedownload.
                        </p>
                    </article>

                </div>

            </div>
        </div>
    </section>

    {{-- =====================================================
         WORKSPACE
         ===================================================== --}}

    <section class="ms-section ms-section-soft">
        <div class="ms-shell">

            <div class="ms-workspace">

                <div>
                    <span class="ms-eyebrow">
                        Your workspace
                    </span>

                    <h2>
                        Alles terugvinden op één plek.
                    </h2>

                    <p>
                        Originelen, bewerkte versies en downloads zitten bij elkaar
                        in je persoonlijke bibliotheek. Open een project opnieuw
                        wanneer je verder wilt werken.
                    </p>

                    <div class="ms-workspace-actions">

                        @auth
                            @if (\Illuminate\Support\Facades\Route::has('images.index'))
                                <a
                                    class="ms-button ms-button-primary"
                                    href="{{ route('images.index') }}"
                                >
                                    Mijn afbeeldingen
                                </a>
                            @endif

                            @if (\Illuminate\Support\Facades\Route::has('account'))
                                <a
                                    class="ms-button"
                                    href="{{ route('account') }}"
                                >
                                    Mijn account
                                </a>
                            @endif
                        @else
                            @if (\Illuminate\Support\Facades\Route::has('register'))
                                <a
                                    class="ms-button ms-button-primary"
                                    href="{{ route('register') }}"
                                >
                                    Account maken
                                </a>
                            @endif

                            @if (\Illuminate\Support\Facades\Route::has('login'))
                                <a
                                    class="ms-button"
                                    href="{{ route('login') }}"
                                >
                                    Inloggen
                                </a>
                            @endif
                        @endauth

                    </div>
                </div>

                <div
                    class="ms-library-preview"
                    aria-hidden="true"
                >
                    <div class="ms-library-head">
                        <strong>
                            Mijn afbeeldingen
                        </strong>

                        <span>
                            6 projecten
                        </span>
                    </div>

                    <div class="ms-library-grid">
                        @for ($i = 1; $i <= 6; $i++)
                            <div class="ms-library-thumb"></div>
                        @endfor
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- =====================================================
         FAQ
         ===================================================== --}}

    <section class="ms-section">
        <div class="ms-shell">

            <div class="ms-faq-layout">

                <div class="ms-faq-intro">
                    <span class="ms-eyebrow">
                        FAQ
                    </span>

                    <h2>
                        Even duidelijk.
                    </h2>

                    <p>
                        De belangrijkste punten over uploaden,
                        versies en je persoonlijke workspace.
                    </p>
                </div>

                <div class="ms-faq-list">

                    <article class="ms-faq-item open">
                        <button
                            class="ms-faq-question"
                            type="button"
                            aria-expanded="true"
                        >
                            <span>
                                Welke bestanden kan ik uploaden?
                            </span>

                            <span
                                class="ms-faq-plus"
                                aria-hidden="true"
                            ></span>
                        </button>

                        <div class="ms-faq-answer">
                            <div>
                                <div class="ms-faq-answer-inner">
                                    Mashal Studio accepteert JPG/JPEG, PNG en WEBP
                                    met een maximale bestandsgrootte van 20 MB.
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="ms-faq-item">
                        <button
                            class="ms-faq-question"
                            type="button"
                            aria-expanded="false"
                        >
                            <span>
                                Wordt mijn originele bestand overschreven?
                            </span>

                            <span
                                class="ms-faq-plus"
                                aria-hidden="true"
                            ></span>
                        </button>

                        <div class="ms-faq-answer">
                            <div>
                                <div class="ms-faq-answer-inner">
                                    Nee. Nieuwe bewerkingen worden als aparte
                                    versies opgeslagen.
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="ms-faq-item">
                        <button
                            class="ms-faq-question"
                            type="button"
                            aria-expanded="false"
                        >
                            <span>
                                Kan ik een eerdere versie opnieuw bewerken?
                            </span>

                            <span
                                class="ms-faq-plus"
                                aria-hidden="true"
                            ></span>
                        </button>

                        <div class="ms-faq-answer">
                            <div>
                                <div class="ms-faq-answer-inner">
                                    Ja. Zowel het origineel als bestaande versies
                                    kunnen opnieuw als bron worden geselecteerd.
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="ms-faq-item">
                        <button
                            class="ms-faq-question"
                            type="button"
                            aria-expanded="false"
                        >
                            <span>
                                Waar staan mijn projecten?
                            </span>

                            <span
                                class="ms-faq-plus"
                                aria-hidden="true"
                            ></span>
                        </button>

                        <div class="ms-faq-answer">
                            <div>
                                <div class="ms-faq-answer-inner">
                                    Na login vind je je projecten onder
                                    “Mijn afbeeldingen”.
                                </div>
                            </div>
                        </div>
                    </article>

                </div>

            </div>
        </div>
    </section>

    {{-- =====================================================
         FINAL CTA
         ===================================================== --}}

    <section class="ms-final">
        <div class="ms-shell">

            <div class="ms-final-panel">

                <div class="ms-final-copy">
                    <span class="ms-eyebrow">
                        Start editing
                    </span>

                    <h2>
                        Je volgende afbeelding begint hier.
                    </h2>

                    <p>
                        Upload je bestand, maak de bewerking die je nodig hebt
                        en bewaar elke versie overzichtelijk in Mashal Studio.
                    </p>

                    <div class="ms-final-actions">

                        <a
                            class="ms-button ms-button-primary"
                            href="#upload"
                        >
                            Upload afbeelding
                        </a>

                        @guest
                            @if (\Illuminate\Support\Facades\Route::has('register'))
                                <a
                                    class="ms-button"
                                    href="{{ route('register') }}"
                                >
                                    Gratis account
                                </a>
                            @endif
                        @else
                            @if (\Illuminate\Support\Facades\Route::has('images.index'))
                                <a
                                    class="ms-button"
                                    href="{{ route('images.index') }}"
                                >
                                    Mijn bibliotheek
                                </a>
                            @endif
                        @endguest

                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- =====================================================
         STICKY CTA
         ===================================================== --}}

    <div
        class="ms-sticky"
        id="stickyUpload"
    >
        <div class="ms-sticky-inner">

            <div class="ms-sticky-copy">
                <strong>
                    Nieuwe afbeelding bewerken?
                </strong>

                <span>
                    JPG, PNG of WEBP · max. 20 MB
                </span>
            </div>

            <a
                class="ms-sticky-link"
                href="#upload"
            >
                Upload
            </a>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form =
        document.getElementById('imageUploadForm');

    const input =
        document.getElementById('imageInput');

    const dropzone =
        document.getElementById('imageDropzone');

    const preview =
        document.getElementById('imagePreview');

    const previewBadge =
        document.getElementById('previewBadge');

    const fileName =
        document.getElementById('previewFileName');

    const fileMeta =
        document.getElementById('previewFileMeta');

    const chooseButton =
        document.getElementById('chooseImageButton');

    const changeButton =
        document.getElementById('changeImageButton');

    const submitButton =
        document.getElementById('submitImageButton');

    const maxBytes =
        20 * 1024 * 1024;

    const allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    let previewUrl = null;

    function formatBytes(bytes) {
        if (
            !Number.isFinite(bytes) ||
            bytes <= 0
        ) {
            return '0 KB';
        }

        const mb =
            bytes / (1024 * 1024);

        if (mb >= 1) {
            return (
                mb.toFixed(mb >= 10 ? 1 : 2) +
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

    function mimeLabel(type) {
        if (type === 'image/jpeg') {
            return 'JPG';
        }

        if (type === 'image/png') {
            return 'PNG';
        }

        if (type === 'image/webp') {
            return 'WEBP';
        }

        return 'IMAGE';
    }

    function clearClientError() {
        document
            .getElementById('clientUploadError')
            ?.remove();
    }

    function showClientError(message) {
        clearClientError();

        if (!form) {
            return;
        }

        const alert =
            document.createElement('div');

        alert.id =
            'clientUploadError';

        alert.className =
            'ms-alert ms-alert-error';

        alert.setAttribute(
            'role',
            'alert'
        );

        alert.textContent =
            message;

        form.parentNode?.insertBefore(
            alert,
            form
        );
    }

    function revokePreviewUrl() {
        if (!previewUrl) {
            return;
        }

        URL.revokeObjectURL(previewUrl);

        previewUrl = null;
    }

    function clearSelection() {
        revokePreviewUrl();

        if (input) {
            input.value = '';
        }

        if (preview) {
            preview.removeAttribute('src');
        }

        dropzone?.classList.remove(
            'has-file'
        );
    }

    function renderFile(file) {
        clearClientError();

        if (!file) {
            return;
        }

        if (!allowedTypes.includes(file.type)) {
            clearSelection();

            showClientError(
                'Gebruik alleen een JPG, PNG of WEBP-afbeelding.'
            );

            return;
        }

        if (file.size > maxBytes) {
            clearSelection();

            showClientError(
                'Deze afbeelding is groter dan 20 MB.'
            );

            return;
        }

        revokePreviewUrl();

        previewUrl =
            URL.createObjectURL(file);

        if (preview) {
            preview.src =
                previewUrl;
        }

        if (fileName) {
            fileName.textContent =
                file.name;
        }

        if (fileMeta) {
            fileMeta.textContent =
                formatBytes(file.size) +
                ' · ' +
                mimeLabel(file.type);
        }

        if (previewBadge) {
            previewBadge.textContent =
                mimeLabel(file.type);
        }

        dropzone?.classList.add(
            'has-file'
        );
    }

    function openFilePicker() {
        input?.click();
    }

    chooseButton?.addEventListener(
        'click',
        function (event) {
            event.preventDefault();
            event.stopPropagation();

            openFilePicker();
        }
    );

    changeButton?.addEventListener(
        'click',
        function (event) {
            event.preventDefault();
            event.stopPropagation();

            openFilePicker();
        }
    );

    input?.addEventListener(
        'change',
        function () {
            renderFile(
                input.files?.[0] ?? null
            );
        }
    );

    dropzone?.addEventListener(
        'click',
        function (event) {
            if (
                event.target.closest(
                    'button, a, input, label'
                )
            ) {
                return;
            }

            openFilePicker();
        }
    );

    dropzone?.addEventListener(
        'keydown',
        function (event) {
            if (
                event.key !== 'Enter' &&
                event.key !== ' '
            ) {
                return;
            }

            event.preventDefault();

            openFilePicker();
        }
    );

    [
        'dragenter',
        'dragover'
    ].forEach(function (eventName) {
        dropzone?.addEventListener(
            eventName,
            function (event) {
                event.preventDefault();
                event.stopPropagation();

                dropzone.classList.add(
                    'is-dragging'
                );
            }
        );
    });

    [
        'dragleave',
        'drop'
    ].forEach(function (eventName) {
        dropzone?.addEventListener(
            eventName,
            function (event) {
                event.preventDefault();
                event.stopPropagation();

                dropzone.classList.remove(
                    'is-dragging'
                );
            }
        );
    });

    dropzone?.addEventListener(
        'drop',
        function (event) {
            const file =
                event.dataTransfer?.files?.[0];

            if (!file || !input) {
                return;
            }

            try {
                const transfer =
                    new DataTransfer();

                transfer.items.add(file);

                input.files =
                    transfer.files;
            } catch (error) {
                //
            }

            if (
                !input.files ||
                !input.files.length
            ) {
                showClientError(
                    'Drag & drop wordt in deze browser niet volledig ondersteund. Kies het bestand via de knop.'
                );

                return;
            }

            renderFile(file);
        }
    );

    form?.addEventListener(
        'submit',
        function (event) {
            if (
                !input?.files ||
                !input.files.length
            ) {
                event.preventDefault();

                showClientError(
                    'Kies eerst een afbeelding.'
                );

                openFilePicker();

                return;
            }

            if (submitButton) {
                submitButton.disabled =
                    true;

                submitButton.textContent =
                    'Uploaden…';
            }
        }
    );

    document
        .querySelectorAll('.ms-faq-item')
        .forEach(function (item) {
            const question =
                item.querySelector(
                    '.ms-faq-question'
                );

            question?.addEventListener(
                'click',
                function () {
                    const isOpen =
                        item.classList.contains(
                            'open'
                        );

                    document
                        .querySelectorAll(
                            '.ms-faq-item'
                        )
                        .forEach(function (other) {
                            other.classList.remove(
                                'open'
                            );

                            other
                                .querySelector(
                                    '.ms-faq-question'
                                )
                                ?.setAttribute(
                                    'aria-expanded',
                                    'false'
                                );
                        });

                    if (!isOpen) {
                        item.classList.add(
                            'open'
                        );

                        question.setAttribute(
                            'aria-expanded',
                            'true'
                        );
                    }
                }
            );
        });

    const stickyUpload =
        document.getElementById(
            'stickyUpload'
        );

    const uploadSection =
        document.getElementById(
            'upload'
        );

    let uploadVisible =
        true;

    function updateSticky() {
        if (!stickyUpload) {
            return;
        }

        stickyUpload.classList.toggle(
            'visible',
            window.scrollY > 720 &&
            !uploadVisible
        );
    }

    if (
        uploadSection &&
        'IntersectionObserver' in window
    ) {
        const observer =
            new IntersectionObserver(
                function (entries) {
                    uploadVisible =
                        Boolean(
                            entries[0]?.isIntersecting
                        );

                    updateSticky();
                },
                {
                    threshold: 0
                }
            );

        observer.observe(
            uploadSection
        );
    }

    let rafPending =
        false;

    window.addEventListener(
        'scroll',
        function () {
            if (rafPending) {
                return;
            }

            rafPending =
                true;

            requestAnimationFrame(
                function () {
                    updateSticky();

                    rafPending =
                        false;
                }
            );
        },
        {
            passive: true
        }
    );

    document
        .querySelectorAll('a[href^="#"]')
        .forEach(function (anchor) {
            anchor.addEventListener(
                'click',
                function (event) {
                    const href =
                        anchor.getAttribute(
                            'href'
                        );

                    if (
                        !href ||
                        href === '#'
                    ) {
                        return;
                    }

                    const target =
                        document.querySelector(
                            href
                        );

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

    window.addEventListener(
        'pageshow',
        function () {
            if (!submitButton) {
                return;
            }

            submitButton.disabled =
                false;

            submitButton.textContent =
                'Open editor';
        }
    );

    updateSticky();
});
</script>
@endpush