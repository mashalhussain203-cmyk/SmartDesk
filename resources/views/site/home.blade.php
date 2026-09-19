@extends('layouts.site-layout')

@section('title', 'Mashal Studio | Resize, Edit & Save Images')

@section(
    'meta_description',
    'Upload JPG, PNG of WEBP-afbeeldingen, bewerk ze in Mashal Studio en bewaar nieuwe versies in je persoonlijke image workspace.'
)

@push('styles')
<style>
    :root {
        --home-bg: #07080b;
        --home-panel: #0d1015;
        --home-panel-2: #12161d;
        --home-text: #f7f7f4;
        --home-muted: #858c96;
        --home-muted-2: #606873;
        --home-line: rgba(255, 255, 255, .072);
        --home-gold: #e3b36b;
        --home-gold-light: #f3d69a;
        --home-gold-deep: #b77d38;
        --home-success: #67d990;
        --home-danger: #f47d7d;
    }

    .home-page {
        position: relative;
        overflow: hidden;
        min-height: 100vh;
        color: var(--home-text);
        background:
            radial-gradient(circle at 12% 7%, rgba(227,179,107,.075), transparent 30rem),
            radial-gradient(circle at 88% 10%, rgba(95,86,255,.055), transparent 32rem),
            linear-gradient(180deg, #07080b, #090b0f 50%, #07080b);
    }

    .home-page::before {
        content: "";
        position: fixed;
        inset: 0;
        z-index: 0;
        pointer-events: none;
        opacity: .11;
        background-image:
            linear-gradient(rgba(255,255,255,.021) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,.021) 1px, transparent 1px);
        background-size: 72px 72px;
        mask-image: linear-gradient(to bottom, #000, transparent 82%);
    }

    .home-shell {
        position: relative;
        z-index: 2;
        width: min(calc(100% - 40px), 1360px);
        margin-inline: auto;
    }

    .home-kicker {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: var(--home-gold);
        font-size: 9px;
        font-weight: 950;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .home-kicker::before {
        content: "";
        width: 30px;
        height: 1px;
        background: linear-gradient(90deg, var(--home-gold), transparent);
    }

    /*
    |--------------------------------------------------------------------------
    | Hero
    |--------------------------------------------------------------------------
    */

    .hero {
        position: relative;
        min-height: 800px;
        display: flex;
        align-items: center;
        padding: 76px 0 100px;
    }

    .hero::after {
        content: "";
        position: absolute;
        left: 50%;
        bottom: -150px;
        width: 900px;
        height: 300px;
        transform: translateX(-50%);
        border-radius: 50%;
        background: rgba(227,179,107,.06);
        filter: blur(95px);
        pointer-events: none;
    }

    .hero-grid {
        width: 100%;
        display: grid;
        grid-template-columns: minmax(0,.94fr) minmax(520px,1.06fr);
        gap: 70px;
        align-items: center;
    }

    .hero-copy {
        max-width: 670px;
    }

    .hero-title {
        margin: 20px 0 0;
        max-width: 780px;
        color: #fff;
        font-size: clamp(58px, 6.7vw, 102px);
        line-height: .91;
        font-weight: 950;
        letter-spacing: -.072em;
        text-wrap: balance;
    }

    .hero-title .soft {
        display: block;
        color: rgba(255,255,255,.72);
    }

    .hero-title .accent {
        display: block;
        color: var(--home-gold-light);
        text-shadow: 0 20px 70px rgba(227,179,107,.13);
    }

    .hero-lead {
        max-width: 600px;
        margin: 28px 0 0;
        color: #aeb3bb;
        font-size: clamp(15px, 1.35vw, 18px);
        line-height: 1.9;
    }

    .hero-actions {
        margin-top: 30px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .home-button {
        min-height: 50px;
        padding: 0 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        border: 1px solid rgba(255,255,255,.09);
        border-radius: 999px;
        color: #d4d8dd;
        background: rgba(255,255,255,.022);
        text-decoration: none;
        font-size: 10px;
        font-weight: 950;
        cursor: pointer;
        transition:
            transform .2s ease,
            border-color .2s ease,
            background .2s ease,
            box-shadow .2s ease;
    }

    .home-button:hover {
        transform: translateY(-2px);
        border-color: rgba(227,179,107,.18);
        background: rgba(227,179,107,.035);
    }

    .home-button.primary {
        border-color: rgba(227,179,107,.18);
        color: #171009;
        background: linear-gradient(135deg, #f2d393, #d49c52);
        box-shadow: 0 16px 40px rgba(227,179,107,.15);
    }

    .home-button.primary:hover {
        background: linear-gradient(135deg, #f6dda8, #dca85f);
        box-shadow: 0 22px 50px rgba(227,179,107,.22);
    }

    .hero-trust {
        margin-top: 29px;
        display: flex;
        gap: 11px 18px;
        flex-wrap: wrap;
    }

    .hero-trust-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #7d848e;
        font-size: 9px;
        font-weight: 850;
    }

    .hero-trust-item::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--home-success);
        box-shadow: 0 0 0 4px rgba(103,217,144,.06);
    }

    .hero-note {
        max-width: 570px;
        margin-top: 25px;
        padding: 14px 16px;
        display: flex;
        align-items: flex-start;
        gap: 11px;
        border: 1px solid rgba(227,179,107,.11);
        border-radius: 14px;
        color: #777e88;
        background: rgba(227,179,107,.025);
        font-size: 9px;
        line-height: 1.7;
    }

    .hero-note-mark {
        width: 27px;
        height: 27px;
        flex: 0 0 27px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(227,179,107,.13);
        border-radius: 9px;
        color: #d4a665;
        background: rgba(227,179,107,.04);
        font-size: 9px;
        font-weight: 950;
    }

    /*
    |--------------------------------------------------------------------------
    | Upload
    |--------------------------------------------------------------------------
    */

    .upload-card {
        position: relative;
        padding: 14px;
        border: 1px solid rgba(255,255,255,.085);
        border-radius: 31px;
        background:
            linear-gradient(145deg, rgba(255,255,255,.055), rgba(255,255,255,.012)),
            rgba(12,14,19,.88);
        box-shadow:
            0 40px 110px rgba(0,0,0,.42),
            inset 0 1px 0 rgba(255,255,255,.035);
        backdrop-filter: blur(20px);
    }

    .upload-card::before {
        content: "";
        position: absolute;
        inset: -1px;
        z-index: -1;
        border-radius: inherit;
        background:
            linear-gradient(
                145deg,
                rgba(243,214,154,.20),
                transparent 34%,
                transparent 72%,
                rgba(95,86,255,.10)
            );
    }

    .upload-window {
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.065);
        border-radius: 23px;
        background: #0b0d12;
    }

    .upload-window-bar {
        min-height: 55px;
        padding: 0 17px;
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 14px;
        align-items: center;
        border-bottom: 1px solid rgba(255,255,255,.06);
        background: rgba(255,255,255,.015);
    }

    .window-dots {
        display: flex;
        gap: 6px;
    }

    .window-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: rgba(255,255,255,.15);
    }

    .window-dot:first-child {
        background: rgba(227,179,107,.68);
    }

    .window-title {
        min-width: 0;
        overflow: hidden;
        color: #707782;
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .13em;
        text-align: center;
        text-overflow: ellipsis;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .window-secure {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #727983;
        font-size: 8px;
        font-weight: 850;
    }

    .window-secure::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--home-success);
    }

    .upload-window-body {
        padding: 18px;
    }

    .upload-alert {
        margin-bottom: 14px;
        padding: 13px 14px;
        border-radius: 13px;
        font-size: 9px;
        line-height: 1.65;
    }

    .upload-alert.success {
        border: 1px solid rgba(103,217,144,.14);
        color: #a8dfb9;
        background: rgba(103,217,144,.045);
    }

    .upload-alert.error {
        border: 1px solid rgba(244,125,125,.15);
        color: #e5a6a6;
        background: rgba(244,125,125,.045);
    }

    .dropzone {
        position: relative;
        min-height: 420px;
        padding: 30px;
        display: grid;
        place-items: center;
        overflow: hidden;
        border: 1px dashed rgba(227,179,107,.28);
        border-radius: 20px;
        background:
            radial-gradient(circle at 50% 12%, rgba(227,179,107,.095), transparent 17rem),
            linear-gradient(180deg, rgba(255,255,255,.016), rgba(255,255,255,.006));
        cursor: pointer;
        transition:
            transform .2s ease,
            border-color .2s ease,
            background .2s ease;
    }

    .dropzone::before {
        content: "";
        position: absolute;
        inset: 0;
        opacity: .11;
        pointer-events: none;
        background-image:
            linear-gradient(45deg, #fff 25%, transparent 25%),
            linear-gradient(-45deg, #fff 25%, transparent 25%),
            linear-gradient(45deg, transparent 75%, #fff 75%),
            linear-gradient(-45deg, transparent 75%, #fff 75%);
        background-size: 18px 18px;
        background-position: 0 0, 0 9px, 9px -9px, -9px 0;
        mask-image: linear-gradient(to bottom, transparent, #000 48%, transparent);
    }

    .dropzone:hover,
    .dropzone.is-dragging {
        transform: translateY(-2px);
        border-color: rgba(243,214,154,.62);
        background:
            radial-gradient(circle at 50% 15%, rgba(227,179,107,.14), transparent 17rem),
            linear-gradient(180deg, rgba(255,255,255,.023), rgba(255,255,255,.008));
    }

    .dropzone.has-file {
        border-style: solid;
        border-color: rgba(103,217,144,.24);
    }

    .dropzone-content {
        position: relative;
        z-index: 2;
        width: 100%;
        max-width: 430px;
        text-align: center;
    }

    .upload-icon {
        width: 82px;
        height: 82px;
        margin: 0 auto 21px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(227,179,107,.17);
        border-radius: 25px;
        color: var(--home-gold-light);
        background:
            linear-gradient(145deg, rgba(227,179,107,.12), rgba(227,179,107,.025));
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,.06),
            0 20px 50px rgba(0,0,0,.23);
        font-size: 28px;
        font-weight: 300;
    }

    .dropzone h2 {
        margin: 0;
        color: #faf9f6;
        font-size: 26px;
        line-height: 1.1;
        letter-spacing: -.035em;
    }

    .dropzone p {
        max-width: 370px;
        margin: 11px auto 0;
        color: #777e88;
        font-size: 10px;
        line-height: 1.75;
    }

    .upload-choose {
        min-height: 49px;
        margin-top: 22px;
        padding: 0 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        border: 0;
        border-radius: 999px;
        color: #161009;
        background: linear-gradient(135deg, #f3d69a, #d6a157);
        box-shadow: 0 16px 44px rgba(227,179,107,.17);
        font-size: 10px;
        font-weight: 950;
        cursor: pointer;
    }

    .upload-formats {
        margin-top: 17px;
        display: flex;
        justify-content: center;
        gap: 7px;
        flex-wrap: wrap;
    }

    .format-pill {
        min-height: 27px;
        padding: 0 9px;
        display: inline-flex;
        align-items: center;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 999px;
        color: #686f79;
        background: rgba(255,255,255,.018);
        font-size: 7px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .file-input {
        position: fixed;
        left: -10000px;
        width: 1px;
        height: 1px;
        overflow: hidden;
        opacity: 0;
    }

    .preview-panel {
        position: relative;
        z-index: 2;
        display: none;
        width: 100%;
    }

    .dropzone.has-file .dropzone-content {
        display: none;
    }

    .dropzone.has-file .preview-panel {
        display: block;
    }

    .preview-frame {
        position: relative;
        overflow: hidden;
        aspect-ratio: 16 / 10;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 16px;
        background:
            linear-gradient(45deg, #12151b 25%, transparent 25%),
            linear-gradient(-45deg, #12151b 25%, transparent 25%),
            linear-gradient(45deg, transparent 75%, #12151b 75%),
            linear-gradient(-45deg, transparent 75%, #12151b 75%),
            #0d1015;
        background-size: 20px 20px;
        background-position: 0 0, 0 10px, 10px -10px, -10px 0;
    }

    .preview-frame img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: contain;
    }

    .preview-badge {
        position: absolute;
        left: 12px;
        top: 12px;
        min-height: 28px;
        padding: 0 9px;
        display: inline-flex;
        align-items: center;
        border: 1px solid rgba(255,255,255,.11);
        border-radius: 999px;
        color: #d9dde3;
        background: rgba(8,10,14,.76);
        backdrop-filter: blur(10px);
        font-size: 7px;
        font-weight: 950;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .preview-info {
        margin-top: 14px;
        display: grid;
        grid-template-columns: minmax(0,1fr) auto;
        gap: 16px;
        align-items: center;
    }

    .preview-name {
        min-width: 0;
    }

    .preview-name strong {
        display: block;
        overflow: hidden;
        color: #eef0f2;
        font-size: 11px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .preview-name span {
        display: block;
        margin-top: 4px;
        color: #686f79;
        font-size: 8px;
    }

    .preview-actions {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .preview-change,
    .preview-submit {
        min-height: 40px;
        padding: 0 13px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 8px;
        font-weight: 950;
        cursor: pointer;
    }

    .preview-change {
        border: 1px solid rgba(255,255,255,.08);
        color: #b9bec5;
        background: rgba(255,255,255,.025);
    }

    .preview-submit {
        border: 0;
        color: #17110a;
        background: linear-gradient(135deg, #f1d08d, #d59d52);
        box-shadow: 0 14px 32px rgba(227,179,107,.14);
    }

    .preview-submit:disabled {
        opacity: .55;
        cursor: wait;
    }

    .upload-foot {
        padding: 15px 3px 1px;
        display: flex;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        color: #5d646e;
        font-size: 8px;
        font-weight: 750;
    }

    /*
    |--------------------------------------------------------------------------
    | Rail
    |--------------------------------------------------------------------------
    */

    .feature-rail {
        position: relative;
        z-index: 2;
        border-top: 1px solid rgba(255,255,255,.055);
        border-bottom: 1px solid rgba(255,255,255,.055);
        background: rgba(255,255,255,.01);
    }

    .rail-grid {
        display: grid;
        grid-template-columns: repeat(4,minmax(0,1fr));
    }

    .rail-item {
        min-height: 92px;
        padding: 23px 25px;
        display: flex;
        align-items: center;
        gap: 13px;
    }

    .rail-item + .rail-item {
        border-left: 1px solid rgba(255,255,255,.05);
    }

    .rail-icon {
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(227,179,107,.11);
        border-radius: 11px;
        color: var(--home-gold);
        background: rgba(227,179,107,.035);
        font-size: 12px;
    }

    .rail-item strong {
        display: block;
        color: #d7dade;
        font-size: 9px;
    }

    .rail-item span {
        display: block;
        margin-top: 3px;
        color: #606873;
        font-size: 8px;
        line-height: 1.45;
    }

    /*
    |--------------------------------------------------------------------------
    | Shared sections
    |--------------------------------------------------------------------------
    */

    .home-section {
        position: relative;
        z-index: 2;
        padding: 112px 0;
    }

    .home-section.alt {
        border-top: 1px solid rgba(255,255,255,.045);
        border-bottom: 1px solid rgba(255,255,255,.045);
        background:
            linear-gradient(180deg, rgba(255,255,255,.01), rgba(255,255,255,.002));
    }

    .section-head {
        max-width: 800px;
        margin-bottom: 42px;
    }

    .section-head.center {
        margin-inline: auto;
        text-align: center;
    }

    .section-head h2 {
        margin: 14px 0 0;
        color: #f6f7f5;
        font-size: clamp(42px, 5vw, 70px);
        line-height: .98;
        letter-spacing: -.06em;
        text-wrap: balance;
    }

    .section-head p {
        max-width: 650px;
        margin: 18px 0 0;
        color: #7f8690;
        font-size: 12px;
        line-height: 1.85;
    }

    .section-head.center p {
        margin-inline: auto;
    }

    /*
    |--------------------------------------------------------------------------
    | Tools
    |--------------------------------------------------------------------------
    */

    .tool-grid {
        display: grid;
        grid-template-columns: repeat(3,minmax(0,1fr));
        gap: 14px;
    }

    .tool-card {
        position: relative;
        min-height: 270px;
        padding: 25px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.065);
        border-radius: 22px;
        background:
            linear-gradient(145deg, rgba(255,255,255,.03), rgba(255,255,255,.006)),
            #0c0f14;
        transition:
            transform .24s ease,
            border-color .24s ease,
            box-shadow .24s ease;
    }

    .tool-card:hover {
        transform: translateY(-5px);
        border-color: rgba(227,179,107,.15);
        box-shadow: 0 28px 70px rgba(0,0,0,.26);
    }

    .tool-card::after {
        content: "";
        position: absolute;
        width: 180px;
        height: 180px;
        right: -80px;
        bottom: -95px;
        border: 1px solid rgba(227,179,107,.055);
        border-radius: 50%;
    }

    .tool-card.featured {
        grid-column: span 2;
        background:
            radial-gradient(circle at 85% 15%, rgba(227,179,107,.09), transparent 16rem),
            linear-gradient(145deg, rgba(255,255,255,.034), rgba(255,255,255,.006)),
            #0c0f14;
    }

    .tool-index {
        color: #775d3a;
        font-size: 8px;
        font-weight: 950;
        letter-spacing: .16em;
    }

    .tool-icon {
        width: 48px;
        height: 48px;
        margin-top: 43px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(227,179,107,.12);
        border-radius: 15px;
        color: var(--home-gold-light);
        background: rgba(227,179,107,.045);
        font-size: 16px;
    }

    .tool-card h3 {
        margin: 17px 0 8px;
        color: #e7e9eb;
        font-size: 21px;
        letter-spacing: -.035em;
    }

    .tool-card p {
        max-width: 470px;
        margin: 0;
        color: #707781;
        font-size: 10px;
        line-height: 1.8;
    }

    .tool-status {
        position: absolute;
        top: 18px;
        right: 18px;
        min-height: 26px;
        padding: 0 8px;
        display: inline-flex;
        align-items: center;
        border: 1px solid rgba(103,217,144,.12);
        border-radius: 999px;
        color: #86dba2;
        background: rgba(103,217,144,.04);
        font-size: 7px;
        font-weight: 950;
        letter-spacing: .09em;
        text-transform: uppercase;
    }

    /*
    |--------------------------------------------------------------------------
    | Workflow
    |--------------------------------------------------------------------------
    */

    .workflow-grid {
        display: grid;
        grid-template-columns: .75fr 1.25fr;
        gap: 70px;
        align-items: start;
    }

    .workflow-copy {
        position: sticky;
        top: 110px;
    }

    .workflow-copy h2 {
        margin: 14px 0 16px;
        color: #f5f6f4;
        font-size: clamp(42px,4.8vw,66px);
        line-height: .98;
        letter-spacing: -.06em;
    }

    .workflow-copy p {
        max-width: 430px;
        margin: 0;
        color: #7b828c;
        font-size: 11px;
        line-height: 1.85;
    }

    .workflow-list {
        display: grid;
    }

    .workflow-item {
        position: relative;
        padding: 29px 0 29px 78px;
        border-top: 1px solid rgba(255,255,255,.07);
    }

    .workflow-item:last-child {
        border-bottom: 1px solid rgba(255,255,255,.07);
    }

    .workflow-number {
        position: absolute;
        left: 0;
        top: 26px;
        width: 50px;
        height: 50px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(227,179,107,.14);
        border-radius: 16px;
        color: var(--home-gold);
        background: rgba(227,179,107,.035);
        font-size: 8px;
        font-weight: 950;
    }

    .workflow-item h3 {
        margin: 0;
        color: #dfe2e6;
        font-size: 19px;
        letter-spacing: -.03em;
    }

    .workflow-item p {
        max-width: 640px;
        margin: 7px 0 0;
        color: #6f7680;
        font-size: 10px;
        line-height: 1.8;
    }

    /*
    |--------------------------------------------------------------------------
    | Account / security
    |--------------------------------------------------------------------------
    */

    .workspace-panel {
        position: relative;
        overflow: hidden;
        padding: 48px;
        display: grid;
        grid-template-columns: 1fr .9fr;
        gap: 60px;
        align-items: center;
        border: 1px solid rgba(227,179,107,.12);
        border-radius: 30px;
        background:
            radial-gradient(circle at 88% 10%, rgba(227,179,107,.10), transparent 24rem),
            linear-gradient(145deg, rgba(255,255,255,.04), rgba(255,255,255,.008)),
            #0c0e13;
        box-shadow: 0 38px 100px rgba(0,0,0,.26);
    }

    .workspace-panel::before {
        content: "";
        position: absolute;
        width: 400px;
        height: 400px;
        right: -120px;
        bottom: -210px;
        border: 1px solid rgba(227,179,107,.07);
        border-radius: 50%;
    }

    .workspace-copy {
        position: relative;
        z-index: 2;
    }

    .workspace-copy h2 {
        margin: 14px 0 17px;
        color: #f4f5f3;
        font-size: clamp(42px,4.6vw,64px);
        line-height: .98;
        letter-spacing: -.06em;
    }

    .workspace-copy p {
        max-width: 590px;
        margin: 0;
        color: #818892;
        font-size: 11px;
        line-height: 1.9;
    }

    .workspace-actions {
        margin-top: 26px;
        display: flex;
        gap: 9px;
        flex-wrap: wrap;
    }

    .workspace-visual {
        position: relative;
        min-height: 330px;
    }

    .library-window {
        position: absolute;
        inset: 0;
        padding: 16px;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 22px;
        background: rgba(8,10,14,.84);
        box-shadow: 0 28px 80px rgba(0,0,0,.38);
        backdrop-filter: blur(18px);
        transform: rotate(1.5deg);
    }

    .library-top {
        padding-bottom: 13px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        border-bottom: 1px solid rgba(255,255,255,.055);
    }

    .library-top strong {
        color: #dfe2e5;
        font-size: 10px;
    }

    .library-top span {
        color: #606873;
        font-size: 8px;
    }

    .library-grid {
        margin-top: 13px;
        display: grid;
        grid-template-columns: repeat(3,1fr);
        gap: 8px;
    }

    .library-thumb {
        position: relative;
        overflow: hidden;
        aspect-ratio: 1;
        border: 1px solid rgba(255,255,255,.055);
        border-radius: 11px;
        background:
            radial-gradient(circle at 35% 28%, rgba(227,179,107,.26), transparent 3.8rem),
            linear-gradient(145deg, #20242c, #11141a);
    }

    .library-thumb:nth-child(2),
    .library-thumb:nth-child(5) {
        background:
            radial-gradient(circle at 70% 28%, rgba(112,88,255,.23), transparent 4rem),
            linear-gradient(145deg, #181c25, #0d1015);
    }

    .library-thumb:nth-child(3),
    .library-thumb:nth-child(6) {
        background:
            radial-gradient(circle at 40% 70%, rgba(103,217,144,.17), transparent 4rem),
            linear-gradient(145deg, #171d1c, #0c1011);
    }

    .library-status {
        position: absolute;
        left: 8px;
        bottom: 8px;
        min-height: 22px;
        padding: 0 6px;
        display: inline-flex;
        align-items: center;
        border-radius: 7px;
        color: #d7dae0;
        background: rgba(6,8,11,.66);
        font-size: 6px;
        font-weight: 900;
    }

    .security-grid {
        margin-top: 38px;
        display: grid;
        grid-template-columns: repeat(3,minmax(0,1fr));
        gap: 12px;
    }

    .security-card {
        min-height: 210px;
        padding: 23px;
        border: 1px solid rgba(255,255,255,.06);
        border-radius: 20px;
        background:
            linear-gradient(145deg, rgba(255,255,255,.027), rgba(255,255,255,.006)),
            #0c0f14;
    }

    .security-icon {
        width: 42px;
        height: 42px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(227,179,107,.12);
        border-radius: 13px;
        color: #d8ab68;
        background: rgba(227,179,107,.04);
        font-size: 13px;
    }

    .security-card h3 {
        margin: 23px 0 7px;
        color: #dfe2e5;
        font-size: 15px;
        letter-spacing: -.025em;
    }

    .security-card p {
        margin: 0;
        color: #6b727c;
        font-size: 9px;
        line-height: 1.75;
    }

    /*
    |--------------------------------------------------------------------------
    | FAQ
    |--------------------------------------------------------------------------
    */

    .faq-wrap {
        display: grid;
        grid-template-columns: .72fr 1.28fr;
        gap: 60px;
        align-items: start;
    }

    .faq-side {
        position: sticky;
        top: 110px;
    }

    .faq-side h2 {
        margin: 14px 0 0;
        color: #f3f4f2;
        font-size: clamp(40px,4.6vw,64px);
        line-height: .98;
        letter-spacing: -.06em;
    }

    .faq-side p {
        max-width: 400px;
        margin: 15px 0 0;
        color: #707781;
        font-size: 10px;
        line-height: 1.8;
    }

    .faq-list {
        display: grid;
        gap: 8px;
    }

    .faq-item {
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.06);
        border-radius: 16px;
        background: rgba(255,255,255,.01);
    }

    .faq-question {
        width: 100%;
        min-height: 62px;
        padding: 0 17px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        border: 0;
        color: #d7dade;
        background: transparent;
        font-size: 10px;
        font-weight: 850;
        text-align: left;
        cursor: pointer;
    }

    .faq-icon {
        position: relative;
        width: 24px;
        height: 24px;
        flex: 0 0 24px;
        border: 1px solid rgba(255,255,255,.065);
        border-radius: 8px;
        background: rgba(255,255,255,.015);
    }

    .faq-icon::before,
    .faq-icon::after {
        content: "";
        position: absolute;
        left: 50%;
        top: 50%;
        width: 9px;
        height: 1px;
        background: #a17b45;
        transform: translate(-50%,-50%);
        transition: transform .2s ease;
    }

    .faq-icon::after {
        transform: translate(-50%,-50%) rotate(90deg);
    }

    .faq-item.open .faq-icon::after {
        transform: translate(-50%,-50%) rotate(0);
    }

    .faq-answer {
        display: grid;
        grid-template-rows: 0fr;
        transition: grid-template-rows .24s ease;
    }

    .faq-answer > div {
        overflow: hidden;
    }

    .faq-answer-inner {
        padding: 0 17px 17px;
        color: #6d747e;
        font-size: 9px;
        line-height: 1.8;
    }

    .faq-item.open .faq-answer {
        grid-template-rows: 1fr;
    }

    /*
    |--------------------------------------------------------------------------
    | Final CTA
    |--------------------------------------------------------------------------
    */

    .final-cta {
        position: relative;
        z-index: 2;
        padding: 0 0 110px;
    }

    .final-panel {
        position: relative;
        min-height: 430px;
        padding: 62px;
        display: flex;
        align-items: center;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 32px;
        background:
            radial-gradient(circle at 82% 24%, rgba(227,179,107,.14), transparent 20rem),
            radial-gradient(circle at 66% 92%, rgba(112,88,255,.08), transparent 24rem),
            linear-gradient(145deg,#12151b,#0a0c10);
        box-shadow: 0 40px 110px rgba(0,0,0,.28);
    }

    .final-panel::before {
        content: "M";
        position: absolute;
        right: -35px;
        bottom: -135px;
        pointer-events: none;
        color: rgba(255,255,255,.017);
        font-size: 470px;
        font-weight: 950;
        line-height: .8;
    }

    .final-copy {
        position: relative;
        z-index: 2;
        max-width: 760px;
    }

    .final-copy h2 {
        margin: 14px 0 18px;
        color: #f6f7f5;
        font-size: clamp(48px,5.8vw,80px);
        line-height: .93;
        letter-spacing: -.068em;
        text-wrap: balance;
    }

    .final-copy p {
        max-width: 610px;
        margin: 0;
        color: #858c96;
        font-size: 12px;
        line-height: 1.85;
    }

    .final-actions {
        margin-top: 27px;
        display: flex;
        gap: 9px;
        flex-wrap: wrap;
    }

    /*
    |--------------------------------------------------------------------------
    | Sticky upload
    |--------------------------------------------------------------------------
    */

    .sticky-upload {
        position: fixed;
        z-index: 850;
        left: 50%;
        bottom: 18px;
        width: min(calc(100% - 30px), 560px);
        transform: translateX(-50%) translateY(140%);
        opacity: 0;
        pointer-events: none;
        transition:
            transform .28s cubic-bezier(.2,.7,.2,1),
            opacity .28s ease;
    }

    .sticky-upload.visible {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
        pointer-events: auto;
    }

    .sticky-upload-inner {
        min-height: 60px;
        padding: 8px 9px 8px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        border: 1px solid rgba(227,179,107,.15);
        border-radius: 999px;
        background: rgba(11,13,18,.91);
        box-shadow: 0 22px 70px rgba(0,0,0,.44);
        backdrop-filter: blur(20px);
    }

    .sticky-upload-copy {
        min-width: 0;
    }

    .sticky-upload-copy strong {
        display: block;
        color: #e4e6e9;
        font-size: 9px;
    }

    .sticky-upload-copy span {
        display: block;
        margin-top: 2px;
        overflow: hidden;
        color: #656c76;
        font-size: 7px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .sticky-upload-link {
        min-height: 42px;
        padding: 0 16px;
        display: inline-flex;
        align-items: center;
        flex: 0 0 auto;
        border-radius: 999px;
        color: #171109;
        background: linear-gradient(135deg,#f2d393,#d49c52);
        text-decoration: none;
        font-size: 8px;
        font-weight: 950;
    }

    @media (max-width: 1120px) {
        .hero {
            min-height: auto;
        }

        .hero-grid {
            grid-template-columns: 1fr;
            gap: 52px;
        }

        .hero-copy {
            max-width: 830px;
        }

        .upload-card {
            max-width: 780px;
        }

        .tool-grid {
            grid-template-columns: repeat(2,minmax(0,1fr));
        }

        .tool-card.featured {
            grid-column: span 2;
        }

        .workflow-grid,
        .workspace-panel {
            grid-template-columns: 1fr;
        }

        .workflow-copy {
            position: static;
        }

        .workspace-visual {
            min-height: 370px;
            max-width: 650px;
        }
    }

    @media (max-width: 860px) {
        .rail-grid {
            grid-template-columns: 1fr 1fr;
        }

        .rail-item:nth-child(3) {
            border-left: 0;
            border-top: 1px solid rgba(255,255,255,.05);
        }

        .rail-item:nth-child(4) {
            border-top: 1px solid rgba(255,255,255,.05);
        }

        .security-grid {
            grid-template-columns: 1fr;
        }

        .faq-wrap {
            grid-template-columns: 1fr;
            gap: 32px;
        }

        .faq-side {
            position: static;
        }
    }

    @media (max-width: 640px) {
        .home-shell {
            width: min(calc(100% - 24px), 1360px);
        }

        .hero {
            padding: 44px 0 76px;
        }

        .hero-title {
            font-size: clamp(48px,16vw,66px);
        }

        .hero-lead {
            font-size: 14px;
        }

        .hero-actions,
        .final-actions,
        .workspace-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .home-button {
            width: 100%;
        }

        .upload-card {
            padding: 9px;
            border-radius: 23px;
        }

        .upload-window {
            border-radius: 17px;
        }

        .upload-window-bar {
            min-height: 49px;
            padding: 0 13px;
        }

        .window-title {
            display: none;
        }

        .upload-window-body {
            padding: 11px;
        }

        .dropzone {
            min-height: 380px;
            padding: 20px 14px;
            border-radius: 15px;
        }

        .preview-info {
            grid-template-columns: 1fr;
        }

        .preview-actions {
            width: 100%;
        }

        .preview-change,
        .preview-submit {
            flex: 1;
        }

        .rail-grid,
        .tool-grid {
            grid-template-columns: 1fr;
        }

        .rail-item + .rail-item,
        .rail-item:nth-child(3),
        .rail-item:nth-child(4) {
            border-left: 0;
            border-top: 1px solid rgba(255,255,255,.05);
        }

        .tool-card.featured {
            grid-column: auto;
        }

        .home-section {
            padding: 82px 0;
        }

        .workflow-item {
            padding-left: 68px;
        }

        .workflow-number {
            width: 44px;
            height: 44px;
        }

        .workspace-panel,
        .final-panel {
            padding: 28px 20px;
            border-radius: 24px;
        }

        .workspace-visual {
            min-height: 300px;
        }

        .sticky-upload {
            bottom: 10px;
        }

        .sticky-upload-copy span {
            display: none;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .01ms !important;
            scroll-behavior: auto !important;
        }
    }
</style>
@endpush

@section('content')
<div class="home-page">

    <section class="hero" id="top">
        <div class="home-shell">
            <div class="hero-grid">

                <div class="hero-copy studio-reveal">
                    <span class="home-kicker">
                        Mashal Image Studio
                    </span>

                    <h1 class="hero-title">
                        Maak elk beeld
                        <span class="soft">
                            precies goed.
                        </span>
                        <span class="accent">
                            In seconden.
                        </span>
                    </h1>

                    <p class="hero-lead">
                        Upload een JPG, PNG of WEBP-afbeelding en werk daarna
                        verder met resize, crop, rotate, flip, compress en
                        conversie. Ingelogde gebruikers bewaren originelen
                        en nieuwe versies in hun persoonlijke workspace.
                    </p>

                    <div class="hero-actions">
                        <a
                            class="home-button primary"
                            href="#upload"
                        >
                            Afbeelding uploaden
                            <span aria-hidden="true">↑</span>
                        </a>

                        @auth
                            @if (\Illuminate\Support\Facades\Route::has('images.index'))
                                <a
                                    class="home-button"
                                    href="{{ route('images.index') }}"
                                >
                                    Mijn afbeeldingen
                                </a>
                            @endif
                        @else
                            @if (\Illuminate\Support\Facades\Route::has('register'))
                                <a
                                    class="home-button"
                                    href="{{ route('register') }}"
                                >
                                    Gratis account maken
                                </a>
                            @endif
                        @endauth
                    </div>

                    <div class="hero-trust">
                        <span class="hero-trust-item">
                            JPG · PNG · WEBP
                        </span>

                        <span class="hero-trust-item">
                            Maximaal 20 MB
                        </span>

                        <span class="hero-trust-item">
                            Private workspace
                        </span>
                    </div>

                    <div class="hero-note">
                        <span class="hero-note-mark">
                            i
                        </span>

                        <span>
                            Je kunt direct een bestand kiezen. Wanneer authenticatie
                            nodig is, kan Mashal Studio de tijdelijke upload daarna
                            aan je account koppelen.
                        </span>
                    </div>
                </div>

                <div
                    class="upload-card studio-reveal"
                    id="upload"
                >
                    <div class="upload-window">
                        <div class="upload-window-bar">
                            <div
                                class="window-dots"
                                aria-hidden="true"
                            >
                                <span class="window-dot"></span>
                                <span class="window-dot"></span>
                                <span class="window-dot"></span>
                            </div>

                            <div class="window-title">
                                New image project
                            </div>

                            <div class="window-secure">
                                Secure upload
                            </div>
                        </div>

                        <div class="upload-window-body">
                            @if (session('success'))
                                <div class="upload-alert success">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="upload-alert error">
                                    {{ session('error') }}
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="upload-alert error">
                                    <strong>
                                        Upload controleren
                                    </strong>

                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
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
                                    class="file-input"
                                    id="imageInput"
                                    type="file"
                                    name="image"
                                    accept="image/jpeg,image/png,image/webp"
                                >

                                <div
                                    class="dropzone"
                                    id="imageDropzone"
                                    role="button"
                                    tabindex="0"
                                    aria-label="Selecteer of sleep een afbeelding hierheen"
                                >
                                    <div class="dropzone-content">
                                        <div
                                            class="upload-icon"
                                            aria-hidden="true"
                                        >
                                            ↑
                                        </div>

                                        <h2>
                                            Upload je afbeelding
                                        </h2>

                                        <p>
                                            Sleep je bestand hierheen of kies een
                                            afbeelding vanaf je apparaat.
                                        </p>

                                        <button
                                            class="upload-choose"
                                            id="chooseImageButton"
                                            type="button"
                                        >
                                            Kies afbeelding
                                        </button>

                                        <div class="upload-formats">
                                            <span class="format-pill">JPG</span>
                                            <span class="format-pill">PNG</span>
                                            <span class="format-pill">WEBP</span>
                                            <span class="format-pill">≤ 20 MB</span>
                                        </div>
                                    </div>

                                    <div class="preview-panel">
                                        <div class="preview-frame">
                                            <img
                                                id="imagePreview"
                                                src=""
                                                alt="Voorbeeld van geselecteerde afbeelding"
                                            >

                                            <span
                                                class="preview-badge"
                                                id="previewBadge"
                                            >
                                                Preview
                                            </span>
                                        </div>

                                        <div class="preview-info">
                                            <div class="preview-name">
                                                <strong id="previewFileName">
                                                    —
                                                </strong>

                                                <span id="previewFileMeta">
                                                    —
                                                </span>
                                            </div>

                                            <div class="preview-actions">
                                                <button
                                                    class="preview-change"
                                                    id="changeImageButton"
                                                    type="button"
                                                >
                                                    Wijzigen
                                                </button>

                                                <button
                                                    class="preview-submit"
                                                    id="submitImageButton"
                                                    type="submit"
                                                >
                                                    Verder
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <div class="upload-foot">
                                <span>
                                    Client- én servervalidatie
                                </span>

                                <span>
                                    Origineel blijft behouden
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section class="feature-rail">
        <div class="home-shell">
            <div class="rail-grid">
                <article class="rail-item">
                    <div class="rail-icon">↔</div>
                    <div>
                        <strong>Resize</strong>
                        <span>Nieuwe afmetingen met aspect ratio.</span>
                    </div>
                </article>

                <article class="rail-item">
                    <div class="rail-icon">⌗</div>
                    <div>
                        <strong>Crop</strong>
                        <span>Snijd exact het gewenste gebied uit.</span>
                    </div>
                </article>

                <article class="rail-item">
                    <div class="rail-icon">◇</div>
                    <div>
                        <strong>Convert</strong>
                        <span>Exporteer naar JPG, PNG of WEBP.</span>
                    </div>
                </article>

                <article class="rail-item">
                    <div class="rail-icon">▦</div>
                    <div>
                        <strong>Versions</strong>
                        <span>Bewaar bewerkingen naast je origineel.</span>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="home-section">
        <div class="home-shell">
            <header class="section-head studio-reveal">
                <span class="home-kicker">
                    Editor tools
                </span>

                <h2>
                    Alles wat je nodig hebt voor een snelle beeldworkflow.
                </h2>

                <p>
                    Elke bewerking maakt een nieuwe versie aan. Zo kun je
                    experimenteren zonder het oorspronkelijke bestand te overschrijven.
                </p>
            </header>

            <div class="tool-grid">
                <article class="tool-card featured studio-reveal">
                    <span class="tool-index">01</span>
                    <span class="tool-status">Available</span>
                    <div class="tool-icon">↔</div>
                    <h3>Resize</h3>
                    <p>
                        Pas breedte en hoogte aan en behoud optioneel de oorspronkelijke
                        beeldverhouding.
                    </p>
                </article>

                <article class="tool-card studio-reveal">
                    <span class="tool-index">02</span>
                    <span class="tool-status">Available</span>
                    <div class="tool-icon">⌗</div>
                    <h3>Crop</h3>
                    <p>
                        Snijd een gebied uit op basis van positie, breedte en hoogte.
                    </p>
                </article>

                <article class="tool-card studio-reveal">
                    <span class="tool-index">03</span>
                    <span class="tool-status">Available</span>
                    <div class="tool-icon">↻</div>
                    <h3>Rotate</h3>
                    <p>
                        Draai beelden naar de gewenste oriëntatie zonder het origineel te wijzigen.
                    </p>
                </article>

                <article class="tool-card studio-reveal">
                    <span class="tool-index">04</span>
                    <span class="tool-status">Available</span>
                    <div class="tool-icon">⇆</div>
                    <h3>Flip</h3>
                    <p>
                        Spiegel horizontaal of verticaal en sla het resultaat als nieuwe versie op.
                    </p>
                </article>

                <article class="tool-card studio-reveal">
                    <span class="tool-index">05</span>
                    <span class="tool-status">Available</span>
                    <div class="tool-icon">↓</div>
                    <h3>Compress</h3>
                    <p>
                        Verminder bestandsgrootte door de exportkwaliteit gecontroleerd aan te passen.
                    </p>
                </article>

                <article class="tool-card studio-reveal">
                    <span class="tool-index">06</span>
                    <span class="tool-status">Available</span>
                    <div class="tool-icon">◇</div>
                    <h3>Convert</h3>
                    <p>
                        Zet je bron om naar JPG, PNG of WEBP en bewaar het resultaat apart.
                    </p>
                </article>
            </div>
        </div>
    </section>

    <section class="home-section alt">
        <div class="home-shell">
            <div class="workflow-grid">
                <div class="workflow-copy studio-reveal">
                    <span class="home-kicker">
                        Workflow
                    </span>

                    <h2>
                        Van upload naar versie in vier stappen.
                    </h2>

                    <p>
                        Mashal Studio houdt de flow bewust eenvoudig:
                        één origineel, meerdere veilige versies en een duidelijke
                        private bibliotheek.
                    </p>
                </div>

                <div class="workflow-list">
                    <article class="workflow-item studio-reveal">
                        <div class="workflow-number">01</div>
                        <h3>Upload je origineel</h3>
                        <p>
                            Kies een JPG, PNG of WEBP-afbeelding van maximaal 20 MB.
                        </p>
                    </article>

                    <article class="workflow-item studio-reveal">
                        <div class="workflow-number">02</div>
                        <h3>Koppel het aan je account</h3>
                        <p>
                            Wanneer login nodig is, wordt de tijdelijke upload na authenticatie
                            aan jouw persoonlijke account gekoppeld.
                        </p>
                    </article>

                    <article class="workflow-item studio-reveal">
                        <div class="workflow-number">03</div>
                        <h3>Bewerk in de editor</h3>
                        <p>
                            Gebruik resize, crop, rotate, flip, compress of convert
                            op het origineel of op een eerdere versie.
                        </p>
                    </article>

                    <article class="workflow-item studio-reveal">
                        <div class="workflow-number">04</div>
                        <h3>Bewaar en download versies</h3>
                        <p>
                            Elke uitvoer blijft apart beschikbaar in je versiegeschiedenis.
                        </p>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section class="home-section">
        <div class="home-shell">
            <div class="workspace-panel studio-reveal">
                <div class="workspace-copy">
                    <span class="home-kicker">
                        Private workspace
                    </span>

                    <h2>
                        Eén account. Al je beeldprojecten.
                    </h2>

                    <p>
                        In je bibliotheek vind je de originelen die aan jouw account zijn
                        gekoppeld. Vanuit ieder project kun je de editor openen, versies
                        downloaden of het volledige project verwijderen.
                    </p>

                    <div class="workspace-actions">
                        @auth
                            @if (\Illuminate\Support\Facades\Route::has('images.index'))
                                <a
                                    class="home-button primary"
                                    href="{{ route('images.index') }}"
                                >
                                    Open mijn bibliotheek
                                </a>
                            @endif

                            @if (\Illuminate\Support\Facades\Route::has('account'))
                                <a
                                    class="home-button"
                                    href="{{ route('account') }}"
                                >
                                    Mijn account
                                </a>
                            @endif
                        @else
                            @if (\Illuminate\Support\Facades\Route::has('register'))
                                <a
                                    class="home-button primary"
                                    href="{{ route('register') }}"
                                >
                                    Gratis registreren
                                </a>
                            @endif

                            @if (\Illuminate\Support\Facades\Route::has('login'))
                                <a
                                    class="home-button"
                                    href="{{ route('login') }}"
                                >
                                    Inloggen
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>

                <div
                    class="workspace-visual"
                    aria-hidden="true"
                >
                    <div class="library-window">
                        <div class="library-top">
                            <strong>Mijn afbeeldingen</strong>
                            <span>Private library</span>
                        </div>

                        <div class="library-grid">
                            @for ($i = 1; $i <= 6; $i++)
                                <div class="library-thumb">
                                    <span class="library-status">
                                        Project {{ str_pad((string) $i, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>

            <div class="security-grid">
                <article class="security-card studio-reveal">
                    <div class="security-icon">✓</div>
                    <h3>Private ownership</h3>
                    <p>
                        Image-projecten worden gekoppeld aan de ingelogde gebruiker.
                    </p>
                </article>

                <article class="security-card studio-reveal">
                    <div class="security-icon">◇</div>
                    <h3>Origineel behouden</h3>
                    <p>
                        Editorbewerkingen worden als nieuwe bestanden en databaseversies opgeslagen.
                    </p>
                </article>

                <article class="security-card studio-reveal">
                    <div class="security-icon">↗</div>
                    <h3>Direct downloaden</h3>
                    <p>
                        Download het oorspronkelijke bestand of een specifieke gemaakte versie.
                    </p>
                </article>
            </div>
        </div>
    </section>

    <section class="home-section alt">
        <div class="home-shell">
            <div class="faq-wrap">
                <div class="faq-side studio-reveal">
                    <span class="home-kicker">
                        FAQ
                    </span>

                    <h2>
                        Veelgestelde vragen.
                    </h2>

                    <p>
                        De belangrijkste onderdelen van de huidige Mashal Studio-flow
                        kort uitgelegd.
                    </p>
                </div>

                <div class="faq-list">
                    <article class="faq-item open studio-reveal">
                        <button
                            class="faq-question"
                            type="button"
                            aria-expanded="true"
                        >
                            <span>
                                Welke afbeeldingen kan ik uploaden?
                            </span>

                            <span class="faq-icon" aria-hidden="true"></span>
                        </button>

                        <div class="faq-answer">
                            <div>
                                <div class="faq-answer-inner">
                                    De huidige uploadflow accepteert JPG/JPEG, PNG en WEBP,
                                    met een maximale bestandsgrootte van 20 MB.
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="faq-item studio-reveal">
                        <button
                            class="faq-question"
                            type="button"
                            aria-expanded="false"
                        >
                            <span>
                                Wordt mijn origineel overschreven?
                            </span>

                            <span class="faq-icon" aria-hidden="true"></span>
                        </button>

                        <div class="faq-answer">
                            <div>
                                <div class="faq-answer-inner">
                                    Nee. Bewerkingen worden als aparte versies opgeslagen,
                                    zodat het oorspronkelijke bestand beschikbaar blijft.
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="faq-item studio-reveal">
                        <button
                            class="faq-question"
                            type="button"
                            aria-expanded="false"
                        >
                            <span>
                                Kan ik een eerdere versie opnieuw bewerken?
                            </span>

                            <span class="faq-icon" aria-hidden="true"></span>
                        </button>

                        <div class="faq-answer">
                            <div>
                                <div class="faq-answer-inner">
                                    Ja. In de editor kun je het origineel of een opgeslagen
                                    versie als bron selecteren voor de volgende bewerking.
                                </div>
                            </div>
                        </div>
                    </article>

                    <article class="faq-item studio-reveal">
                        <button
                            class="faq-question"
                            type="button"
                            aria-expanded="false"
                        >
                            <span>
                                Waar vind ik mijn projecten terug?
                            </span>

                            <span class="faq-icon" aria-hidden="true"></span>
                        </button>

                        <div class="faq-answer">
                            <div>
                                <div class="faq-answer-inner">
                                    Na login vind je je projecten onder “Mijn afbeeldingen”.
                                    Vanuit daar kun je een project openen, downloaden of verwijderen.
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section class="final-cta">
        <div class="home-shell">
            <div class="final-panel studio-reveal">
                <div class="final-copy">
                    <span class="home-kicker">
                        Start editing
                    </span>

                    <h2>
                        Klaar voor je volgende afbeelding?
                    </h2>

                    <p>
                        Start met één upload. Bewerk daarna gecontroleerd verder
                        en bouw je eigen versiegeschiedenis op binnen Mashal Studio.
                    </p>

                    <div class="final-actions">
                        <a
                            class="home-button primary"
                            href="#upload"
                        >
                            Upload afbeelding
                        </a>

                        @guest
                            @if (\Illuminate\Support\Facades\Route::has('register'))
                                <a
                                    class="home-button"
                                    href="{{ route('register') }}"
                                >
                                    Account maken
                                </a>
                            @endif
                        @else
                            @if (\Illuminate\Support\Facades\Route::has('account'))
                                <a
                                    class="home-button"
                                    href="{{ route('account') }}"
                                >
                                    Open mijn account
                                </a>
                            @endif
                        @endguest
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div
        class="sticky-upload"
        id="stickyUpload"
    >
        <div class="sticky-upload-inner">
            <div class="sticky-upload-copy">
                <strong>
                    Klaar om een afbeelding te bewerken?
                </strong>

                <span>
                    JPG, PNG of WEBP · maximaal 20 MB
                </span>
            </div>

            <a
                class="sticky-upload-link"
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
            'upload-alert error';

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

        URL.revokeObjectURL(
            previewUrl
        );

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
                /*
                 * Sommige browsers laten input.files niet programmatisch
                 * aanpassen. De preview kan nog steeds worden getoond,
                 * maar zonder inputbestand kan het formulier niet submitten.
                 */
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
        .querySelectorAll('.faq-item')
        .forEach(function (item) {
            const question =
                item.querySelector(
                    '.faq-question'
                );

            question?.addEventListener(
                'click',
                function () {
                    const wasOpen =
                        item.classList.contains(
                            'open'
                        );

                    document
                        .querySelectorAll(
                            '.faq-item'
                        )
                        .forEach(
                            function (other) {
                                other.classList.remove(
                                    'open'
                                );

                                other
                                    .querySelector(
                                        '.faq-question'
                                    )
                                    ?.setAttribute(
                                        'aria-expanded',
                                        'false'
                                    );
                            }
                        );

                    if (!wasOpen) {
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

    function updateStickyUpload() {
        if (
            !stickyUpload ||
            !uploadSection
        ) {
            return;
        }

        const rect =
            uploadSection.getBoundingClientRect();

        const uploadVisible =
            rect.bottom > 0 &&
            rect.top < window.innerHeight;

        const shouldShow =
            window.scrollY > 760 &&
            !uploadVisible;

        stickyUpload.classList.toggle(
            'visible',
            shouldShow
        );
    }

    updateStickyUpload();

    window.addEventListener(
        'scroll',
        updateStickyUpload,
        {
            passive: true
        }
    );

    window.addEventListener(
        'resize',
        updateStickyUpload
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
                'Verder';
        }
    );
});
</script>
@endpush
