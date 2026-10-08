@extends('layouts.site-layout')

@section('title', 'Mashal Studio | Intelligent Image Workspace')

@section(
    'meta_description',
    'Upload JPG, PNG of WEBP-afbeeldingen, bewerk ze in Mashal Studio en bewaar originelen en versies in je persoonlijke workspace.'
)

@push('styles')
<style>
/* ==========================================================================
   MASHAL STUDIO — SIGNATURE HOME
   Cinematic product landing + functional upload workspace
   ========================================================================== */

:root {
    --ms-bg: #050609;
    --ms-bg-2: #090b10;
    --ms-panel: #0d1016;
    --ms-panel-2: #121620;
    --ms-panel-3: #171c27;
    --ms-text: #f6f8fb;
    --ms-text-2: #bec5d1;
    --ms-muted: #7d8796;
    --ms-muted-2: #505866;
    --ms-line: rgba(255,255,255,.075);
    --ms-line-2: rgba(255,255,255,.13);
    --ms-accent: #7a6cff;
    --ms-accent-2: #42a5ff;
    --ms-accent-3: #8df0d0;
    --ms-danger: #ff7c91;
    --ms-success: #74dfa7;
    --ms-warm: #a99fff;
    --ms-radius-xs: 8px;
    --ms-radius-sm: 12px;
    --ms-radius-md: 18px;
    --ms-radius-lg: 26px;
    --ms-radius-xl: 36px;
    --ms-ease: cubic-bezier(.2,.8,.2,1);
    --ms-ease-out: cubic-bezier(.16,1,.3,1);
    --ms-shadow: 0 40px 120px rgba(0,0,0,.42);
}

*,
*::before,
*::after {
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
    background: var(--ms-bg);
}

body {
    background: var(--ms-bg);
}

.ms-home {
    position: relative;
    min-height: 100vh;
    overflow: clip;
    color: var(--ms-text);
    background:
        radial-gradient(circle at 72% -5%, rgba(122,108,255,.15), transparent 31rem),
        radial-gradient(circle at 20% 20%, rgba(66,165,255,.065), transparent 29rem),
        linear-gradient(180deg, #050609 0%, #07090d 45%, #050609 100%);
}

.ms-home::before {
    content: "";
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 0;
    opacity: .32;
    background-image:
        linear-gradient(rgba(255,255,255,.026) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.026) 1px, transparent 1px);
    background-size: 64px 64px;
    mask-image: linear-gradient(to bottom, #000 0%, rgba(0,0,0,.45) 52%, transparent 100%);
}

.ms-home::after {
    content: "";
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 0;
    opacity: .7;
    background:
        radial-gradient(circle at var(--mx, 70%) var(--my, 12%), rgba(122,108,255,.095), transparent 18rem);
    transition: opacity .2s ease;
}

.ms-shell {
    position: relative;
    z-index: 2;
    width: min(calc(100% - 42px), 1380px);
    margin-inline: auto;
}

.ms-section {
    position: relative;
    z-index: 2;
    padding: 132px 0;
}

.ms-section--tight {
    padding: 92px 0;
}

.ms-section--soft {
    border-top: 1px solid var(--ms-line);
    border-bottom: 1px solid var(--ms-line);
    background:
        linear-gradient(180deg, rgba(255,255,255,.018), rgba(255,255,255,.006)),
        rgba(255,255,255,.006);
}

.ms-kicker {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: #9f96ff;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .16em;
    text-transform: uppercase;
}

.ms-kicker::before {
    content: "";
    width: 7px;
    height: 7px;
    border-radius: 2px;
    background: linear-gradient(135deg, var(--ms-accent), var(--ms-accent-2));
    box-shadow: 0 0 18px rgba(122,108,255,.6);
}

.ms-heading {
    margin: 18px 0 0;
    color: #fff;
    font-size: clamp(44px, 5.7vw, 82px);
    line-height: .95;
    font-weight: 670;
    letter-spacing: -.065em;
    text-wrap: balance;
}

.ms-heading em {
    color: #777f8d;
    font-style: normal;
}

.ms-copy {
    max-width: 640px;
    margin: 20px 0 0;
    color: var(--ms-muted);
    font-size: 14px;
    line-height: 1.85;
}

.ms-button {
    --btn-bg: rgba(255,255,255,.025);
    --btn-color: #dce1e8;
    --btn-border: var(--ms-line-2);
    min-height: 52px;
    padding: 0 20px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    position: relative;
    overflow: hidden;
    border: 1px solid var(--btn-border);
    border-radius: 12px;
    color: var(--btn-color);
    background: var(--btn-bg);
    text-decoration: none;
    font-size: 12px;
    font-weight: 760;
    cursor: pointer;
    isolation: isolate;
    transition:
        transform .24s var(--ms-ease),
        border-color .24s ease,
        background .24s ease,
        box-shadow .24s ease;
}

.ms-button::before {
    content: "";
    position: absolute;
    inset: 0;
    z-index: -1;
    opacity: 0;
    background: linear-gradient(120deg, transparent, rgba(255,255,255,.1), transparent);
    transform: translateX(-120%);
    transition:
        transform .65s var(--ms-ease),
        opacity .25s ease;
}

.ms-button:hover {
    transform: translateY(-2px);
    border-color: rgba(255,255,255,.22);
    background: rgba(255,255,255,.045);
}

.ms-button:hover::before {
    opacity: 1;
    transform: translateX(120%);
}

.ms-button--primary {
    --btn-border: rgba(122,108,255,.4);
    --btn-bg: linear-gradient(135deg, #7a6cff, #4e8eff);
    --btn-color: #fff;
    box-shadow:
        0 18px 44px rgba(81,70,214,.28),
        inset 0 1px 0 rgba(255,255,255,.2);
}

.ms-button--ghost {
    background: rgba(255,255,255,.02);
}

.ms-icon-button {
    width: 40px;
    height: 40px;
    display: grid;
    place-items: center;
    border: 1px solid var(--ms-line);
    border-radius: 10px;
    color: #b4bbca;
    background: rgba(255,255,255,.025);
    cursor: pointer;
}

/* ==========================================================================
   HERO
   ========================================================================== */

.ms-hero {
    position: relative;
    min-height: 920px;
    padding: 108px 0 90px;
    display: flex;
    align-items: center;
    overflow: hidden;
}

.ms-hero-orbit {
    position: absolute;
    width: 720px;
    height: 720px;
    right: -180px;
    top: -210px;
    border: 1px solid rgba(122,108,255,.08);
    border-radius: 50%;
    pointer-events: none;
}

.ms-hero-orbit::before,
.ms-hero-orbit::after {
    content: "";
    position: absolute;
    inset: 80px;
    border: 1px solid rgba(66,165,255,.06);
    border-radius: inherit;
}

.ms-hero-orbit::after {
    inset: 170px;
}

.ms-hero-grid {
    width: 100%;
    display: grid;
    grid-template-columns: minmax(0,.9fr) minmax(560px,1.1fr);
    gap: 78px;
    align-items: center;
}

.ms-hero-copy {
    position: relative;
    z-index: 2;
    max-width: 670px;
}

.ms-hero-title {
    margin: 24px 0 0;
    max-width: 780px;
    color: #fff;
    font-size: clamp(64px, 7vw, 108px);
    line-height: .89;
    font-weight: 690;
    letter-spacing: -.078em;
}

.ms-hero-title .muted {
    display: block;
    color: #707784;
}

.ms-hero-title .gradient {
    display: block;
    color: transparent;
    background:
        linear-gradient(120deg, #ffffff 0%, #c7c1ff 42%, #80c7ff 72%, #8df0d0 100%);
    -webkit-background-clip: text;
    background-clip: text;
}

.ms-hero-lead {
    max-width: 585px;
    margin: 28px 0 0;
    color: #929ba8;
    font-size: 16px;
    line-height: 1.85;
}

.ms-hero-actions {
    margin-top: 32px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.ms-proof {
    margin-top: 30px;
    display: flex;
    align-items: center;
    gap: 16px 20px;
    flex-wrap: wrap;
}

.ms-proof-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #6f7783;
    font-size: 10px;
    font-weight: 650;
}

.ms-proof-item::before {
    content: "";
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: var(--ms-success);
    box-shadow: 0 0 0 4px rgba(116,223,167,.06);
}

/* ==========================================================================
   INTERACTIVE STUDIO
   ========================================================================== */

.ms-studio-wrap {
    position: relative;
    perspective: 1400px;
}

.ms-studio-glow {
    position: absolute;
    inset: 10% 8% -5% 8%;
    z-index: -1;
    filter: blur(70px);
    opacity: .42;
    background:
        radial-gradient(circle at 30% 10%, rgba(122,108,255,.38), transparent 44%),
        radial-gradient(circle at 78% 82%, rgba(66,165,255,.24), transparent 42%);
}

.ms-studio-card {
    position: relative;
    padding: 9px;
    border: 1px solid rgba(255,255,255,.11);
    border-radius: 24px;
    background:
        linear-gradient(145deg, rgba(255,255,255,.07), rgba(255,255,255,.012));
    box-shadow:
        0 55px 130px rgba(0,0,0,.5),
        inset 0 1px 0 rgba(255,255,255,.045);
    transform-style: preserve-3d;
    will-change: transform;
}

.ms-studio-window {
    overflow: hidden;
    border: 1px solid rgba(255,255,255,.055);
    border-radius: 17px;
    background: #0b0d12;
}

.ms-windowbar {
    min-height: 56px;
    padding: 0 16px;
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;
    gap: 14px;
    border-bottom: 1px solid var(--ms-line);
    background: #11141a;
}

.ms-window-dots {
    display: flex;
    gap: 6px;
}

.ms-window-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: rgba(255,255,255,.14);
}

.ms-window-dot:nth-child(1) {
    background: #7a6cff;
}

.ms-window-dot:nth-child(2) {
    background: #42a5ff;
}

.ms-window-dot:nth-child(3) {
    background: #74dfa7;
}

.ms-window-title {
    overflow: hidden;
    color: #666e7c;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .13em;
    text-align: center;
    text-overflow: ellipsis;
    text-transform: uppercase;
    white-space: nowrap;
}

.ms-window-secure {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: #77808d;
    font-size: 9px;
    font-weight: 700;
}

.ms-window-secure::before {
    content: "";
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--ms-success);
}

.ms-window-body {
    padding: 14px;
}

.ms-alert {
    margin-bottom: 12px;
    padding: 12px 14px;
    border-radius: 10px;
    font-size: 10px;
    line-height: 1.7;
}

.ms-alert--success {
    border: 1px solid rgba(116,223,167,.16);
    color: #ace9c4;
    background: rgba(116,223,167,.05);
}

.ms-alert--error {
    border: 1px solid rgba(255,124,145,.16);
    color: #f2a8b5;
    background: rgba(255,124,145,.05);
}

.ms-file-input {
    position: fixed;
    left: -10000px;
    width: 1px;
    height: 1px;
    overflow: hidden;
    opacity: 0;
}

.ms-dropzone {
    position: relative;
    min-height: 470px;
    padding: 32px;
    display: grid;
    place-items: center;
    overflow: hidden;
    border: 1px dashed rgba(122,108,255,.34);
    border-radius: 13px;
    background:
        radial-gradient(circle at 50% 20%, rgba(122,108,255,.115), transparent 18rem),
        linear-gradient(180deg, rgba(255,255,255,.012), rgba(255,255,255,.003));
    cursor: pointer;
    transition:
        border-color .25s ease,
        background .25s ease,
        transform .25s var(--ms-ease);
}

.ms-dropzone::before {
    content: "";
    position: absolute;
    inset: 0;
    pointer-events: none;
    opacity: .23;
    background-image:
        linear-gradient(rgba(255,255,255,.035) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,.035) 1px, transparent 1px);
    background-size: 28px 28px;
    mask-image: radial-gradient(circle at 50% 48%, #000, transparent 72%);
}

.ms-dropzone::after {
    content: "";
    position: absolute;
    width: 160px;
    height: 160px;
    border: 1px solid rgba(122,108,255,.12);
    border-radius: 50%;
    opacity: .6;
    transform: scale(.8);
    transition:
        transform .35s var(--ms-ease-out),
        opacity .35s ease;
}

.ms-dropzone:hover,
.ms-dropzone.is-dragging {
    border-color: rgba(122,108,255,.72);
    background:
        radial-gradient(circle at 50% 20%, rgba(122,108,255,.17), transparent 19rem),
        linear-gradient(180deg, rgba(255,255,255,.02), rgba(255,255,255,.004));
}

.ms-dropzone:hover::after,
.ms-dropzone.is-dragging::after {
    transform: scale(2.5);
    opacity: 0;
}

.ms-dropzone.has-file {
    border-style: solid;
    border-color: rgba(116,223,167,.24);
}

.ms-upload-empty {
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: 440px;
    text-align: center;
}

.ms-upload-icon {
    width: 76px;
    height: 76px;
    margin: 0 auto 22px;
    display: grid;
    place-items: center;
    position: relative;
    border: 1px solid rgba(122,108,255,.22);
    border-radius: 18px;
    color: #b4adff;
    background:
        linear-gradient(145deg, rgba(122,108,255,.13), rgba(66,165,255,.035));
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.06),
        0 18px 48px rgba(0,0,0,.22);
    font-size: 26px;
}

.ms-upload-icon::after {
    content: "";
    position: absolute;
    inset: -10px;
    border: 1px solid rgba(122,108,255,.08);
    border-radius: 24px;
}

.ms-upload-empty h2 {
    margin: 0;
    color: #f5f6f8;
    font-size: 29px;
    line-height: 1.08;
    font-weight: 660;
    letter-spacing: -.045em;
}

.ms-upload-empty p {
    max-width: 370px;
    margin: 12px auto 0;
    color: #747d89;
    font-size: 12px;
    line-height: 1.75;
}

.ms-upload-choose {
    min-height: 48px;
    margin-top: 23px;
    padding: 0 20px;
    border: 1px solid rgba(122,108,255,.35);
    border-radius: 10px;
    color: #fff;
    background:
        linear-gradient(135deg, #7867ff, #4f8fff);
    box-shadow:
        0 15px 38px rgba(81,69,211,.22),
        inset 0 1px 0 rgba(255,255,255,.18);
    font-size: 11px;
    font-weight: 800;
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
    min-height: 26px;
    padding: 0 8px;
    display: inline-flex;
    align-items: center;
    border: 1px solid var(--ms-line);
    border-radius: 7px;
    color: #596270;
    background: rgba(255,255,255,.015);
    font-size: 8px;
    font-weight: 800;
    letter-spacing: .08em;
}

.ms-preview {
    position: relative;
    z-index: 2;
    display: none;
    width: 100%;
}

.ms-dropzone.has-file .ms-upload-empty {
    display: none;
}

.ms-dropzone.has-file .ms-preview {
    display: block;
}

.ms-preview-frame {
    position: relative;
    overflow: hidden;
    aspect-ratio: 16 / 10;
    border: 1px solid var(--ms-line);
    border-radius: 10px;
    background:
        linear-gradient(45deg, #151821 25%, transparent 25%),
        linear-gradient(-45deg, #151821 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, #151821 75%),
        linear-gradient(-45deg, transparent 75%, #151821 75%),
        #0e1117;
    background-size: 22px 22px;
    background-position: 0 0, 0 11px, 11px -11px, -11px 0;
}

.ms-preview-frame img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: contain;
}

.ms-preview-badge {
    position: absolute;
    left: 10px;
    top: 10px;
    min-height: 26px;
    padding: 0 8px;
    display: inline-flex;
    align-items: center;
    border: 1px solid var(--ms-line-2);
    border-radius: 7px;
    color: #d7dce4;
    background: rgba(7,9,13,.82);
    font-size: 8px;
    font-weight: 800;
    letter-spacing: .08em;
}

.ms-preview-meta {
    margin-top: 13px;
    display: grid;
    grid-template-columns: minmax(0,1fr) auto;
    gap: 14px;
    align-items: center;
}

.ms-preview-file {
    min-width: 0;
}

.ms-preview-file strong {
    display: block;
    overflow: hidden;
    color: #eef1f5;
    font-size: 12px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.ms-preview-file span {
    display: block;
    margin-top: 4px;
    color: #626b78;
    font-size: 9px;
}

.ms-preview-actions {
    display: flex;
    gap: 7px;
}

.ms-preview-change,
.ms-preview-submit {
    min-height: 41px;
    padding: 0 13px;
    border-radius: 8px;
    font-size: 9px;
    font-weight: 800;
    cursor: pointer;
}

.ms-preview-change {
    border: 1px solid var(--ms-line);
    color: #b6beca;
    background: rgba(255,255,255,.025);
}

.ms-preview-submit {
    border: 0;
    color: #fff;
    background: linear-gradient(135deg, #7867ff, #4f8fff);
}

.ms-preview-submit:disabled {
    opacity: .56;
    cursor: wait;
}

.ms-upload-foot {
    padding: 13px 3px 2px;
    display: flex;
    justify-content: space-between;
    gap: 12px;
    color: #525b68;
    font-size: 9px;
}

/* ==========================================================================
   SIGNAL STRIP
   ========================================================================== */

.ms-signal {
    border-top: 1px solid var(--ms-line);
    border-bottom: 1px solid var(--ms-line);
    background: rgba(255,255,255,.009);
}

.ms-signal-grid {
    display: grid;
    grid-template-columns: repeat(6,1fr);
}

.ms-signal-item {
    min-height: 94px;
    padding: 20px 18px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.ms-signal-item + .ms-signal-item {
    border-left: 1px solid var(--ms-line);
}

.ms-signal-icon {
    width: 36px;
    height: 36px;
    flex: 0 0 36px;
    display: grid;
    place-items: center;
    border: 1px solid var(--ms-line);
    border-radius: 9px;
    color: #958cff;
    background: rgba(255,255,255,.02);
}

.ms-signal-item strong {
    display: block;
    color: #d8dde5;
    font-size: 10px;
}

.ms-signal-item span {
    display: block;
    margin-top: 3px;
    color: #5f6875;
    font-size: 8px;
}

/* ==========================================================================
   LIVE PRODUCT SHOWCASE
   ========================================================================== */

.ms-showcase-head {
    max-width: 850px;
    margin-bottom: 56px;
}

.ms-editor-shell {
    position: relative;
    min-height: 620px;
    display: grid;
    grid-template-columns: 230px minmax(0,1fr) 270px;
    overflow: hidden;
    border: 1px solid var(--ms-line);
    border-radius: 22px;
    background: #0b0d12;
    box-shadow: var(--ms-shadow);
}

.ms-editor-sidebar,
.ms-editor-inspector {
    background: #10131a;
}

.ms-editor-sidebar {
    padding: 18px;
    border-right: 1px solid var(--ms-line);
}

.ms-editor-inspector {
    padding: 18px;
    border-left: 1px solid var(--ms-line);
}

.ms-editor-label {
    margin-bottom: 16px;
    color: #5e6774;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: .15em;
    text-transform: uppercase;
}

.ms-editor-tool {
    min-height: 48px;
    padding: 0 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    position: relative;
    border-radius: 9px;
    color: #7d8692;
    font-size: 10px;
    font-weight: 700;
    cursor: default;
}

.ms-editor-tool + .ms-editor-tool {
    margin-top: 3px;
}

.ms-editor-tool.active {
    color: #f0f2f6;
    background:
        linear-gradient(90deg, rgba(122,108,255,.14), rgba(122,108,255,.03));
}

.ms-editor-tool.active::after {
    content: "";
    position: absolute;
    left: 0;
    top: 9px;
    bottom: 9px;
    width: 2px;
    border-radius: 999px;
    background: linear-gradient(180deg, var(--ms-accent), var(--ms-accent-2));
}

.ms-editor-tool-icon {
    width: 27px;
    height: 27px;
    display: grid;
    place-items: center;
    border: 1px solid var(--ms-line);
    border-radius: 7px;
    color: #9890ff;
    background: rgba(255,255,255,.02);
}

.ms-editor-canvas {
    position: relative;
    padding: 52px;
    display: grid;
    place-items: center;
    overflow: hidden;
    background:
        linear-gradient(45deg, #11141a 25%, transparent 25%),
        linear-gradient(-45deg, #11141a 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, #11141a 75%),
        linear-gradient(-45deg, transparent 75%, #11141a 75%),
        #0a0c10;
    background-size: 30px 30px;
    background-position: 0 0, 0 15px, 15px -15px, -15px 0;
}

.ms-canvas-toolbar {
    position: absolute;
    left: 50%;
    top: 16px;
    transform: translateX(-50%);
    min-height: 38px;
    padding: 0 8px;
    display: flex;
    align-items: center;
    gap: 5px;
    border: 1px solid var(--ms-line);
    border-radius: 10px;
    background: rgba(15,18,24,.9);
    box-shadow: 0 12px 30px rgba(0,0,0,.2);
}

.ms-canvas-tool {
    min-width: 29px;
    height: 28px;
    padding: 0 7px;
    display: grid;
    place-items: center;
    border-radius: 7px;
    color: #707987;
    font-size: 8px;
}

.ms-canvas-tool.active {
    color: #dfe4eb;
    background: rgba(255,255,255,.06);
}

.ms-demo-image {
    position: relative;
    width: min(100%, 520px);
    aspect-ratio: 4 / 3;
    border-radius: 6px;
    overflow: hidden;
    box-shadow:
        0 35px 90px rgba(0,0,0,.48),
        0 0 0 1px rgba(255,255,255,.06);
    transform: translateZ(0);
}

.ms-demo-image::before {
    content: "";
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 25% 30%, rgba(255,255,255,.34), transparent 12%),
        radial-gradient(circle at 68% 30%, rgba(132,112,255,.7), transparent 28%),
        radial-gradient(circle at 45% 75%, rgba(66,165,255,.42), transparent 33%),
        linear-gradient(135deg, #192234 0%, #34306d 48%, #10141d 100%);
}

.ms-demo-image::after {
    content: "";
    position: absolute;
    inset: 12%;
    border: 1px solid rgba(255,255,255,.11);
    border-radius: 50%;
    transform: rotate(-12deg);
}

.ms-crop-guide {
    position: absolute;
    inset: 14%;
    border: 1px solid rgba(255,255,255,.65);
}

.ms-crop-guide::before,
.ms-crop-guide::after {
    content: "";
    position: absolute;
    background: rgba(255,255,255,.25);
}

.ms-crop-guide::before {
    top: 33.33%;
    left: 0;
    right: 0;
    height: 1px;
    box-shadow: 0  calc(33.33% + 44px) 0 rgba(255,255,255,.25);
}

.ms-crop-guide::after {
    left: 33.33%;
    top: 0;
    bottom: 0;
    width: 1px;
    box-shadow: calc(33.33% + 60px) 0 0 rgba(255,255,255,.25);
}

.ms-inspector-title {
    margin-bottom: 20px;
    color: #e3e7ed;
    font-size: 12px;
    font-weight: 750;
}

.ms-field {
    margin-bottom: 17px;
}

.ms-field label {
    display: block;
    margin-bottom: 7px;
    color: #616b78;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
}

.ms-fake-input {
    min-height: 42px;
    padding: 0 11px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border: 1px solid var(--ms-line);
    border-radius: 8px;
    color: #ccd2dc;
    background: #0c0f14;
    font-size: 10px;
}

.ms-fake-input small {
    color: #5d6673;
    font-size: 8px;
}

.ms-fake-toggle {
    min-height: 42px;
    padding: 0 11px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border: 1px solid var(--ms-line);
    border-radius: 8px;
    background: #0c0f14;
    color: #bbc2cd;
    font-size: 10px;
}

.ms-toggle-dot {
    width: 28px;
    height: 16px;
    padding: 2px;
    display: flex;
    justify-content: flex-end;
    border-radius: 999px;
    background: linear-gradient(90deg, #6e60ff, #4d8fff);
}

.ms-toggle-dot::after {
    content: "";
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #fff;
}

.ms-create-version {
    min-height: 44px;
    width: 100%;
    border: 0;
    border-radius: 9px;
    color: #fff;
    background: linear-gradient(135deg, #7867ff, #4f8fff);
    font-size: 10px;
    font-weight: 800;
}

/* ==========================================================================
   FEATURE BENTO
   ========================================================================== */

.ms-bento {
    display: grid;
    grid-template-columns: repeat(12,1fr);
    gap: 12px;
}

.ms-bento-card {
    position: relative;
    min-height: 280px;
    padding: 26px;
    overflow: hidden;
    border: 1px solid var(--ms-line);
    border-radius: 18px;
    background:
        linear-gradient(145deg, rgba(255,255,255,.022), rgba(255,255,255,.004)),
        #0d1016;
    transition:
        transform .28s var(--ms-ease),
        border-color .28s ease,
        background .28s ease;
}

.ms-bento-card:hover {
    transform: translateY(-5px);
    border-color: rgba(122,108,255,.2);
    background:
        linear-gradient(145deg, rgba(122,108,255,.055), rgba(255,255,255,.004)),
        #0d1016;
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

.ms-bento-index {
    color: #56606e;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .14em;
}

.ms-bento-card h3 {
    margin: 54px 0 9px;
    max-width: 520px;
    color: #e7ebf0;
    font-size: 22px;
    font-weight: 650;
    letter-spacing: -.035em;
}

.ms-bento-card p {
    max-width: 520px;
    margin: 0;
    color: #737d89;
    font-size: 11px;
    line-height: 1.8;
}

.ms-bento-visual {
    position: absolute;
    right: 18px;
    bottom: 18px;
    width: 160px;
    height: 110px;
    opacity: .9;
}

.ms-version-stack {
    position: relative;
    width: 100%;
    height: 100%;
}

.ms-version-stack span {
    position: absolute;
    right: 0;
    bottom: 0;
    width: 116px;
    height: 76px;
    border: 1px solid var(--ms-line);
    border-radius: 9px;
    background:
        radial-gradient(circle at 70% 20%, rgba(122,108,255,.3), transparent 3rem),
        #151924;
    box-shadow: 0 16px 34px rgba(0,0,0,.22);
}

.ms-version-stack span:nth-child(1) {
    transform: translate(-40px,-24px) rotate(-7deg);
    opacity: .46;
}

.ms-version-stack span:nth-child(2) {
    transform: translate(-20px,-12px) rotate(-3deg);
    opacity: .68;
}

.ms-version-stack span:nth-child(3) {
    transform: none;
}

.ms-bento-orbit {
    position: absolute;
    right: -80px;
    bottom: -100px;
    width: 250px;
    height: 250px;
    border: 1px solid rgba(122,108,255,.08);
    border-radius: 50%;
}

.ms-bento-orbit::after {
    content: "";
    position: absolute;
    inset: 44px;
    border: 1px solid rgba(66,165,255,.07);
    border-radius: inherit;
}

/* ==========================================================================
   FLOW
   ========================================================================== */

.ms-flow {
    display: grid;
    grid-template-columns: .75fr 1.25fr;
    gap: 90px;
    align-items: start;
}

.ms-flow-copy {
    position: sticky;
    top: 110px;
}

.ms-flow-copy h2 {
    margin: 18px 0 16px;
    color: #f4f6f9;
    font-size: clamp(44px,4.7vw,68px);
    line-height: .98;
    font-weight: 650;
    letter-spacing: -.06em;
}

.ms-flow-copy p {
    max-width: 430px;
    margin: 0;
    color: #757f8c;
    font-size: 12px;
    line-height: 1.85;
}

.ms-flow-list {
    display: grid;
}

.ms-flow-step {
    position: relative;
    min-height: 132px;
    padding: 30px 0 30px 78px;
    border-top: 1px solid var(--ms-line);
}

.ms-flow-step:last-child {
    border-bottom: 1px solid var(--ms-line);
}

.ms-flow-number {
    position: absolute;
    left: 0;
    top: 28px;
    width: 46px;
    height: 46px;
    display: grid;
    place-items: center;
    border: 1px solid var(--ms-line);
    border-radius: 10px;
    color: #9189ff;
    background: #10131a;
    font-size: 9px;
    font-weight: 800;
}

.ms-flow-step h3 {
    margin: 0;
    color: #dee3ea;
    font-size: 19px;
    font-weight: 650;
    letter-spacing: -.02em;
}

.ms-flow-step p {
    max-width: 650px;
    margin: 8px 0 0;
    color: #6d7683;
    font-size: 11px;
    line-height: 1.8;
}

/* ==========================================================================
   WORKSPACE
   ========================================================================== */

.ms-workspace {
    position: relative;
    overflow: hidden;
    padding: 58px;
    display: grid;
    grid-template-columns: 1fr .92fr;
    gap: 70px;
    align-items: center;
    border: 1px solid var(--ms-line);
    border-radius: 24px;
    background:
        radial-gradient(circle at 92% 10%, rgba(122,108,255,.12), transparent 24rem),
        linear-gradient(145deg, rgba(255,255,255,.026), rgba(255,255,255,.006)),
        #0d1016;
    box-shadow: var(--ms-shadow);
}

.ms-workspace h2 {
    margin: 18px 0 16px;
    color: #f4f6f8;
    font-size: clamp(44px,4.6vw,66px);
    line-height: .97;
    font-weight: 650;
    letter-spacing: -.06em;
}

.ms-workspace p {
    max-width: 590px;
    margin: 0;
    color: #798391;
    font-size: 12px;
    line-height: 1.85;
}

.ms-workspace-actions {
    margin-top: 26px;
    display: flex;
    gap: 9px;
    flex-wrap: wrap;
}

.ms-library-window {
    padding: 15px;
    border: 1px solid var(--ms-line);
    border-radius: 16px;
    background: rgba(7,9,13,.88);
    box-shadow: 0 30px 80px rgba(0,0,0,.34);
    transform: rotate(1.2deg);
}

.ms-library-head {
    padding-bottom: 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    border-bottom: 1px solid var(--ms-line);
}

.ms-library-head strong {
    color: #dfe4ea;
    font-size: 10px;
}

.ms-library-head span {
    color: #5f6875;
    font-size: 8px;
}

.ms-library-grid {
    margin-top: 12px;
    display: grid;
    grid-template-columns: repeat(3,1fr);
    gap: 7px;
}

.ms-library-thumb {
    position: relative;
    overflow: hidden;
    aspect-ratio: 1;
    border: 1px solid var(--ms-line);
    border-radius: 8px;
    background:
        radial-gradient(circle at 30% 30%, rgba(122,108,255,.38), transparent 4rem),
        linear-gradient(145deg, #1c2230, #10131a);
}

.ms-library-thumb:nth-child(2),
.ms-library-thumb:nth-child(5) {
    background:
        radial-gradient(circle at 70% 25%, rgba(66,165,255,.35), transparent 4rem),
        linear-gradient(145deg, #182430, #0f1319);
}

.ms-library-thumb:nth-child(3),
.ms-library-thumb:nth-child(6) {
    background:
        radial-gradient(circle at 38% 72%, rgba(141,240,208,.24), transparent 4rem),
        linear-gradient(145deg, #16231e, #0e1411);
}

.ms-library-tag {
    position: absolute;
    left: 7px;
    bottom: 7px;
    min-height: 21px;
    padding: 0 6px;
    display: inline-flex;
    align-items: center;
    border-radius: 6px;
    color: #dce1e8;
    background: rgba(7,9,13,.65);
    font-size: 6px;
    font-weight: 800;
}

/* ==========================================================================
   FAQ
   ========================================================================== */

.ms-faq-layout {
    display: grid;
    grid-template-columns: .7fr 1.3fr;
    gap: 84px;
    align-items: start;
}

.ms-faq-side {
    position: sticky;
    top: 110px;
}

.ms-faq-side h2 {
    margin: 18px 0 14px;
    color: #f3f5f8;
    font-size: clamp(42px,4.5vw,64px);
    line-height: .98;
    font-weight: 650;
    letter-spacing: -.06em;
}

.ms-faq-side p {
    max-width: 390px;
    margin: 0;
    color: #707a87;
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
    border-radius: 12px;
    background: rgba(255,255,255,.01);
}

.ms-faq-question {
    width: 100%;
    min-height: 68px;
    padding: 0 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    border: 0;
    color: #d7dce4;
    background: transparent;
    font-size: 11px;
    font-weight: 700;
    text-align: left;
    cursor: pointer;
}

.ms-faq-icon {
    position: relative;
    width: 26px;
    height: 26px;
    flex: 0 0 26px;
    border: 1px solid var(--ms-line);
    border-radius: 7px;
    background: rgba(255,255,255,.02);
}

.ms-faq-icon::before,
.ms-faq-icon::after {
    content: "";
    position: absolute;
    left: 50%;
    top: 50%;
    width: 9px;
    height: 1px;
    background: #9189ff;
    transform: translate(-50%,-50%);
    transition: transform .22s ease;
}

.ms-faq-icon::after {
    transform: translate(-50%,-50%) rotate(90deg);
}

.ms-faq-item.open .ms-faq-icon::after {
    transform: translate(-50%,-50%) rotate(0);
}

.ms-faq-answer {
    display: grid;
    grid-template-rows: 0fr;
    transition: grid-template-rows .26s var(--ms-ease);
}

.ms-faq-answer > div {
    overflow: hidden;
}

.ms-faq-answer-inner {
    padding: 0 18px 18px;
    color: #6d7683;
    font-size: 10px;
    line-height: 1.85;
}

.ms-faq-item.open .ms-faq-answer {
    grid-template-rows: 1fr;
}

/* ==========================================================================
   FINAL CTA
   ========================================================================== */

.ms-final {
    position: relative;
    z-index: 2;
    padding: 0 0 110px;
}

.ms-final-panel {
    position: relative;
    min-height: 440px;
    padding: 64px;
    display: flex;
    align-items: center;
    overflow: hidden;
    border: 1px solid var(--ms-line);
    border-radius: 28px;
    background:
        radial-gradient(circle at 82% 18%, rgba(122,108,255,.18), transparent 20rem),
        radial-gradient(circle at 68% 110%, rgba(66,165,255,.1), transparent 26rem),
        linear-gradient(145deg,#121620,#0b0d12);
    box-shadow: 0 45px 120px rgba(0,0,0,.3);
}

.ms-final-panel::before {
    content: "M";
    position: absolute;
    right: -30px;
    bottom: -150px;
    color: rgba(255,255,255,.018);
    font-size: 500px;
    font-weight: 900;
    line-height: .8;
}

.ms-final-copy {
    position: relative;
    z-index: 2;
    max-width: 780px;
}

.ms-final-copy h2 {
    margin: 18px 0 18px;
    color: #f6f8fb;
    font-size: clamp(50px,5.8vw,82px);
    line-height: .93;
    font-weight: 650;
    letter-spacing: -.068em;
}

.ms-final-copy p {
    max-width: 620px;
    margin: 0;
    color: #7e8794;
    font-size: 13px;
    line-height: 1.85;
}

.ms-final-actions {
    margin-top: 28px;
    display: flex;
    gap: 9px;
    flex-wrap: wrap;
}

/* ==========================================================================
   SCROLL PROGRESS
   ========================================================================== */

.ms-scroll-progress {
    position: fixed;
    left: 0;
    top: 0;
    z-index: 1100;
    width: 100%;
    height: 2px;
    pointer-events: none;
    background: rgba(255,255,255,.03);
}

.ms-scroll-progress > span {
    display: block;
    width: 0%;
    height: 100%;
    background: linear-gradient(90deg, var(--ms-accent), var(--ms-accent-2), var(--ms-accent-3));
    box-shadow: 0 0 18px rgba(122,108,255,.42);
}


/* ==========================================================================
   REVEAL STATES (JS)
   ========================================================================== */

[data-reveal] {
    opacity: 0;
    transform: translateY(34px);
    filter: blur(8px);
    transition:
        opacity .8s var(--ms-ease-out),
        transform .8s var(--ms-ease-out),
        filter .8s ease;
}

[data-reveal].is-visible {
    opacity: 1;
    transform: translateY(0);
    filter: blur(0);
}

[data-reveal="left"] {
    transform: translateX(-34px);
}

[data-reveal="left"].is-visible {
    transform: translateX(0);
}

[data-reveal="right"] {
    transform: translateX(34px);
}

[data-reveal="right"].is-visible {
    transform: translateX(0);
}

[data-stagger] > * {
    opacity: 0;
    transform: translateY(20px);
}

[data-stagger].is-visible > * {
    animation: msStaggerIn .7s var(--ms-ease-out) forwards;
}

[data-stagger].is-visible > *:nth-child(1) { animation-delay: .02s; }
[data-stagger].is-visible > *:nth-child(2) { animation-delay: .08s; }
[data-stagger].is-visible > *:nth-child(3) { animation-delay: .14s; }
[data-stagger].is-visible > *:nth-child(4) { animation-delay: .20s; }
[data-stagger].is-visible > *:nth-child(5) { animation-delay: .26s; }
[data-stagger].is-visible > *:nth-child(6) { animation-delay: .32s; }

@keyframes msStaggerIn {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes msFloat {
    0%,100% { transform: translate3d(0,0,0); }
    50% { transform: translate3d(0,-8px,0); }
}

@keyframes msPulse {
    0%,100% { opacity: .45; transform: scale(1); }
    50% { opacity: .82; transform: scale(1.08); }
}

/* ==========================================================================
   RESPONSIVE
   ========================================================================== */

@media (max-width: 1180px) {
    .ms-hero {
        min-height: auto;
    }

    .ms-hero-grid {
        grid-template-columns: 1fr;
        gap: 62px;
    }

    .ms-hero-copy {
        max-width: 820px;
    }

    .ms-studio-wrap {
        max-width: 860px;
    }

    .ms-editor-shell {
        grid-template-columns: 200px minmax(0,1fr);
    }

    .ms-editor-inspector {
        display: none;
    }

    .ms-flow,
    .ms-workspace {
        grid-template-columns: 1fr;
    }

    .ms-flow-copy,
    .ms-faq-side {
        position: static;
    }

    .ms-workspace {
        gap: 44px;
    }
}

@media (max-width: 900px) {
    .ms-signal-grid {
        grid-template-columns: repeat(3,1fr);
    }

    .ms-signal-item:nth-child(4) {
        border-left: 0;
    }

    .ms-signal-item:nth-child(n+4) {
        border-top: 1px solid var(--ms-line);
    }

    .ms-bento-card:nth-child(n) {
        grid-column: span 6;
    }

    .ms-faq-layout {
        grid-template-columns: 1fr;
        gap: 36px;
    }
}

@media (max-width: 680px) {
    .ms-shell {
        width: calc(100% - 24px);
    }

    .ms-home::before {
        opacity: .17;
        background-size: 44px 44px;
    }

    .ms-hero {
        padding: 62px 0 74px;
    }

    .ms-hero-title {
        font-size: clamp(52px,15vw,70px);
    }

    .ms-hero-lead {
        font-size: 14px;
    }

    .ms-hero-actions,
    .ms-workspace-actions,
    .ms-final-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .ms-button {
        width: 100%;
    }

    .ms-studio-card {
        padding: 6px;
        border-radius: 17px;
    }

    .ms-studio-window {
        border-radius: 12px;
    }

    .ms-window-title {
        display: none;
    }

    .ms-dropzone {
        min-height: 390px;
        padding: 22px 14px;
    }

    .ms-preview-meta {
        grid-template-columns: 1fr;
    }

    .ms-preview-actions {
        width: 100%;
    }

    .ms-preview-change,
    .ms-preview-submit {
        flex: 1;
    }

    .ms-signal-grid {
        grid-template-columns: 1fr 1fr;
    }

    .ms-signal-item:nth-child(n) {
        border-left: 0;
        border-top: 0;
        border-bottom: 1px solid var(--ms-line);
    }

    .ms-signal-item:nth-child(even) {
        border-left: 1px solid var(--ms-line);
    }

    .ms-section {
        padding: 88px 0;
    }

    .ms-editor-shell {
        grid-template-columns: 1fr;
        min-height: auto;
    }

    .ms-editor-sidebar {
        display: none;
    }

    .ms-editor-canvas {
        min-height: 440px;
        padding: 32px 18px;
    }

    .ms-bento-card:nth-child(n) {
        grid-column: span 12;
    }

    .ms-workspace {
        padding: 30px 22px;
    }

    .ms-final-panel {
        min-height: auto;
        padding: 48px 22px;
        border-radius: 22px;
    }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        scroll-behavior: auto !important;
        animation-duration: .001ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: .001ms !important;
    }

    [data-reveal],
    [data-stagger] > * {
        opacity: 1 !important;
        transform: none !important;
        filter: none !important;
    }
}
</style>
@endpush

@section('content')
<div class="ms-home" id="msHome">
    <div class="ms-scroll-progress" aria-hidden="true">
        <span id="msScrollProgress"></span>
    </div>

    <section class="ms-hero" id="top">
        <div class="ms-hero-orbit" aria-hidden="true"></div>

        <div class="ms-shell">
            <div class="ms-hero-grid">
                <div class="ms-hero-copy" data-reveal>
                    <span class="ms-kicker">
                        Mashal Image Workspace
                    </span>

                    <h1 class="ms-hero-title">
                        {{ __('Edit images.') }}
                        <span class="muted">
                            {{ __('Keep the original.') }}
                        </span>
                        <span class="gradient">
                            {{ __('Build better versions.') }}
                        </span>
                    </h1>

                    <p class="ms-hero-lead">
                        {{ __('Eén snelle workspace voor resize, crop, rotate, flip, compress en convert. Elke bewerking wordt een nieuwe versie, zodat je altijd terug kunt naar het origineel.') }}
                    </p>

                    <div class="ms-hero-actions">
                        <a
                            class="ms-button ms-button--primary js-magnetic"
                            href="#upload"
                        >
                            {{ __('Start met een afbeelding') }}
                            <span aria-hidden="true">↗</span>
                        </a>

                        @auth
                            @if (\Illuminate\Support\Facades\Route::has('images.index'))
                                <a
                                    class="ms-button ms-button--ghost js-magnetic"
                                    href="{{ route('images.index') }}"
                                >
                                    {{ __('Mijn afbeeldingen') }}
                                </a>
                            @endif
                        @else
                            @if (\Illuminate\Support\Facades\Route::has('register'))
                                <a
                                    class="ms-button ms-button--ghost js-magnetic"
                                    href="{{ route('register') }}"
                                >
                                    {{ __('Gratis account') }}
                                </a>
                            @endif
                        @endauth
                    </div>

                    <div class="ms-proof" data-stagger>
                        <span class="ms-proof-item">
                            JPG · PNG · WEBP
                        </span>

                        <span class="ms-proof-item">
                            {{ __('Maximaal 20 MB') }}
                        </span>

                        <span class="ms-proof-item">
                            {{ __('Versies blijven apart') }}
                        </span>
                    </div>
                </div>

                <div
                    class="ms-studio-wrap"
                    id="upload"
                    data-reveal="right"
                >
                    <div class="ms-studio-glow" aria-hidden="true"></div>

                    <div
                        class="ms-studio-card"
                        id="msStudioCard"
                    >
                        <div class="ms-studio-window">
                            <div class="ms-windowbar">
                                <div class="ms-window-dots" aria-hidden="true">
                                    <span class="ms-window-dot"></span>
                                    <span class="ms-window-dot"></span>
                                    <span class="ms-window-dot"></span>
                                </div>

                                <div class="ms-window-title">
                                    Mashal / New project
                                </div>

                                <div class="ms-window-secure">
                                    {{ __('Secure upload') }}
                                </div>
                            </div>

                            <div class="ms-window-body">
                                @if (session('success'))
                                    <div class="ms-alert ms-alert--success">
                                        {{ session('success') }}
                                    </div>
                                @endif

                                @if (session('error'))
                                    <div class="ms-alert ms-alert--error">
                                        {{ session('error') }}
                                    </div>
                                @endif

                                @if ($errors->any())
                                    <div class="ms-alert ms-alert--error">
                                        <strong>{{ __('Upload controleren') }}</strong>

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
                                                id="msUploadIcon"
                                                aria-hidden="true"
                                            >
                                                ↑
                                            </div>

                                            <h2>
                                                {{ __('Drop je afbeelding hier') }}
                                            </h2>

                                            <p>
                                                {{ __('Selecteer een JPG, PNG of WEBP. Je ziet eerst een preview voordat de upload begint.') }}
                                            </p>

                                            <button
                                                class="ms-upload-choose js-magnetic"
                                                id="chooseImageButton"
                                                type="button"
                                            >
                                                {{ __('Kies afbeelding') }}
                                            </button>

                                            <div class="ms-format-row">
                                                <span class="ms-format">JPG</span>
                                                <span class="ms-format">PNG</span>
                                                <span class="ms-format">WEBP</span>
                                                <span class="ms-format">≤ 20 MB</span>
                                            </div>
                                        </div>

                                        <div class="ms-preview">
                                            <div class="ms-preview-frame">
                                                <img
                                                    id="imagePreview"
                                                    src=""
                                                    alt="Voorbeeld van geselecteerde afbeelding"
                                                >

                                                <span
                                                    class="ms-preview-badge"
                                                    id="previewBadge"
                                                >
                                                    {{ __('PREVIEW') }}
                                                </span>
                                            </div>

                                            <div class="ms-preview-meta">
                                                <div class="ms-preview-file">
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
                                                        {{ __('Wijzigen') }}
                                                    </button>

                                                    <button
                                                        class="ms-preview-submit"
                                                        id="submitImageButton"
                                                        type="submit"
                                                    >
                                                        {{ __('Open editor') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                                <div class="ms-upload-foot">
                                    <span>{{ __('Client + servervalidatie') }}</span>
                                    <span>{{ __('Origineel blijft behouden') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="ms-signal">
        <div class="ms-shell">
            <div class="ms-signal-grid" data-stagger>
                <div class="ms-signal-item">
                    <div class="ms-signal-icon">↔</div>
                    <div>
                        <strong>{{ __('Resize') }}</strong>
                        <span>{{ __('Exacte afmetingen') }}</span>
                    </div>
                </div>

                <div class="ms-signal-item">
                    <div class="ms-signal-icon">⌗</div>
                    <div>
                        <strong>{{ __('Crop') }}</strong>
                        <span>{{ __('Snijd gericht uit') }}</span>
                    </div>
                </div>

                <div class="ms-signal-item">
                    <div class="ms-signal-icon">↻</div>
                    <div>
                        <strong>{{ __('Rotate') }}</strong>
                        <span>{{ __('Draai zonder verlies') }}</span>
                    </div>
                </div>

                <div class="ms-signal-item">
                    <div class="ms-signal-icon">⇆</div>
                    <div>
                        <strong>{{ __('Flip') }}</strong>
                        <span>{{ __('Horizontaal / verticaal') }}</span>
                    </div>
                </div>

                <div class="ms-signal-item">
                    <div class="ms-signal-icon">↓</div>
                    <div>
                        <strong>{{ __('Compress') }}</strong>
                        <span>{{ __('Kleinere export') }}</span>
                    </div>
                </div>

                <div class="ms-signal-item">
                    <div class="ms-signal-icon">◇</div>
                    <div>
                        <strong>{{ __('Convert') }}</strong>
                        <span>JPG · PNG · WEBP</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="ms-section">
        <div class="ms-shell">
            <header class="ms-showcase-head" data-reveal>
                <span class="ms-kicker">
                    {{ __('Product experience') }}
                </span>

                <h2 class="ms-heading">
                    {{ __('Niet zes losse tools.') }}
                    <em>{{ __('Eén editor.') }}</em>
                </h2>

                <p class="ms-copy">
                    {{ __('De interface voelt als één echte beeldworkspace: bron selecteren, bewerking kiezen, instellingen aanpassen en een nieuwe versie opslaan.') }}
                </p>
            </header>

            <div class="ms-editor-shell" data-reveal>
                <aside class="ms-editor-sidebar">
                    <div class="ms-editor-label">
                        {{ __('Tools') }}
                    </div>

                    <div class="ms-editor-tool active">
                        <span class="ms-editor-tool-icon">↔</span>
                        {{ __('Resize') }}
                    </div>

                    <div class="ms-editor-tool">
                        <span class="ms-editor-tool-icon">⌗</span>
                        {{ __('Crop') }}
                    </div>

                    <div class="ms-editor-tool">
                        <span class="ms-editor-tool-icon">↻</span>
                        {{ __('Rotate') }}
                    </div>

                    <div class="ms-editor-tool">
                        <span class="ms-editor-tool-icon">⇆</span>
                        {{ __('Flip') }}
                    </div>

                    <div class="ms-editor-tool">
                        <span class="ms-editor-tool-icon">↓</span>
                        {{ __('Compress') }}
                    </div>

                    <div class="ms-editor-tool">
                        <span class="ms-editor-tool-icon">◇</span>
                        {{ __('Convert') }}
                    </div>
                </aside>

                <div class="ms-editor-canvas" id="msEditorCanvas">
                    <div class="ms-canvas-toolbar" aria-hidden="true">
                        <span class="ms-canvas-tool">−</span>
                        <span class="ms-canvas-tool active">100%</span>
                        <span class="ms-canvas-tool">+</span>
                    </div>

                    <div class="ms-demo-image" id="msDemoImage">
                        <div class="ms-crop-guide"></div>
                    </div>
                </div>

                <aside class="ms-editor-inspector">
                    <div class="ms-inspector-title">
                        {{ __('Resize image') }}
                    </div>

                    <div class="ms-field">
                        <label>{{ __('Width') }}</label>

                        <div class="ms-fake-input">
                            <span>1920</span>
                            <small>px</small>
                        </div>
                    </div>

                    <div class="ms-field">
                        <label>{{ __('Height') }}</label>

                        <div class="ms-fake-input">
                            <span>1080</span>
                            <small>px</small>
                        </div>
                    </div>

                    <div class="ms-field">
                        <label>{{ __('Aspect ratio') }}</label>

                        <div class="ms-fake-toggle">
                            <span>{{ __('Keep ratio') }}</span>
                            <span class="ms-toggle-dot"></span>
                        </div>
                    </div>

                    <div class="ms-field">
                        <label>{{ __('Source') }}</label>

                        <div class="ms-fake-input">
                            <span>{{ __('Original') }}</span>
                            <small>⌄</small>
                        </div>
                    </div>

                    <button
                        class="ms-create-version"
                        type="button"
                        tabindex="-1"
                    >
                        {{ __('Create version') }}
                    </button>
                </aside>
            </div>
        </div>
    </section>

    <section class="ms-section ms-section--soft">
        <div class="ms-shell">
            <header class="ms-showcase-head" data-reveal>
                <span class="ms-kicker">
                    {{ __('Version-first') }}
                </span>

                <h2 class="ms-heading">
                    {{ __('Bewerk vrij.') }}
                    <em>{{ __('Verlies niets.') }}</em>
                </h2>

                <p class="ms-copy">
                    {{ __('Mashal Studio behandelt elke bewerking als een nieuwe versie. Daardoor kun je blijven experimenteren zonder je bron te overschrijven.') }}
                </p>
            </header>

            <div class="ms-bento" data-stagger>
                <article class="ms-bento-card">
                    <span class="ms-bento-index">01 / ORIGINAL</span>

                    <h3>
                        {{ __('Eén origineel als veilige basis.') }}
                    </h3>

                    <p>
                        {{ __('Je upload blijft beschikbaar terwijl je nieuwe varianten maakt. Geen destructieve edits, geen twijfel over welke versie de bron was.') }}
                    </p>

                    <div class="ms-bento-visual" aria-hidden="true">
                        <div class="ms-version-stack">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </article>

                <article class="ms-bento-card">
                    <span class="ms-bento-index">02 / HISTORY</span>

                    <h3>
                        {{ __('Bouw verder op eerdere versies.') }}
                    </h3>

                    <p>
                        {{ __('Gebruik later opnieuw het origineel of een bestaande versie als bron voor je volgende edit.') }}
                    </p>

                    <div class="ms-bento-orbit" aria-hidden="true"></div>
                </article>

                <article class="ms-bento-card">
                    <span class="ms-bento-index">03 / EXPORT</span>

                    <h3>
                        {{ __('JPG, PNG en WEBP.') }}
                    </h3>

                    <p>
                        {{ __('Kies het formaat dat bij je volgende gebruiksmoment past.') }}
                    </p>
                </article>

                <article class="ms-bento-card">
                    <span class="ms-bento-index">04 / PRIVATE</span>

                    <h3>
                        {{ __('Persoonlijke workspace.') }}
                    </h3>

                    <p>
                        {{ __('Projecten worden gekoppeld aan jouw account en blijven overzichtelijk bij elkaar.') }}
                    </p>
                </article>

                <article class="ms-bento-card">
                    <span class="ms-bento-index">05 / DOWNLOAD</span>

                    <h3>
                        {{ __('Elke versie apart downloaden.') }}
                    </h3>

                    <p>
                        {{ __('Pak precies het bestand dat je nodig hebt zonder het project te verliezen.') }}
                    </p>
                </article>
            </div>
        </div>
    </section>

    <section class="ms-section">
        <div class="ms-shell">
            <div class="ms-flow">
                <div class="ms-flow-copy" data-reveal="left">
                    <span class="ms-kicker">
                        Workflow
                    </span>

                    <h2>
                        {{ __('Van upload naar versie zonder omwegen.') }}
                    </h2>

                    <p>
                        {{ __('De workflow is bewust lineair. Upload, kies een bewerking, maak een nieuwe versie en ga verder vanuit je bibliotheek.') }}
                    </p>
                </div>

                <div class="ms-flow-list" data-stagger>
                    <article class="ms-flow-step">
                        <div class="ms-flow-number">01</div>
                        <h3>{{ __('Upload je bronbestand') }}</h3>
                        <p>
                            {{ __('Kies een JPG, PNG of WEBP-afbeelding tot maximaal 20 MB.') }}
                        </p>
                    </article>

                    <article class="ms-flow-step">
                        <div class="ms-flow-number">02</div>
                        <h3>{{ __('Koppel aan je workspace') }}</h3>
                        <p>
                            {{ __('Wanneer login nodig is, wordt je tijdelijke upload na authenticatie aan je account gekoppeld.') }}
                        </p>
                    </article>

                    <article class="ms-flow-step">
                        <div class="ms-flow-number">03</div>
                        <h3>{{ __('Bewerk in de editor') }}</h3>
                        <p>
                            {{ __('Resize, crop, rotate, flip, compress of convert vanuit één consistente editorflow.') }}
                        </p>
                    </article>

                    <article class="ms-flow-step">
                        <div class="ms-flow-number">04</div>
                        <h3>{{ __('Maak een nieuwe versie') }}</h3>
                        <p>
                            {{ __('Het resultaat komt naast je bestaande bestanden te staan en kan later opnieuw als bron dienen.') }}
                        </p>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section class="ms-section ms-section--soft">
        <div class="ms-shell">
            <div class="ms-workspace" data-reveal>
                <div>
                    <span class="ms-kicker">
                        {{ __('Personal workspace') }}
                    </span>

                    <h2>
                        {{ __('Al je beeldprojecten op één plek.') }}
                    </h2>

                    <p>
                        {{ __('Open je originelen, bekijk gemaakte versies, download specifieke bestanden of ga terug de editor in wanneer je verder wilt werken.') }}
                    </p>

                    <div class="ms-workspace-actions">
                        @auth
                            @if (\Illuminate\Support\Facades\Route::has('images.index'))
                                <a
                                    class="ms-button ms-button--primary js-magnetic"
                                    href="{{ route('images.index') }}"
                                >
                                    {{ __('Mijn afbeeldingen') }}
                                </a>
                            @endif

                            @if (\Illuminate\Support\Facades\Route::has('account'))
                                <a
                                    class="ms-button js-magnetic"
                                    href="{{ route('account') }}"
                                >
                                    {{ __('Mijn account') }}
                                </a>
                            @endif
                        @else
                            @if (\Illuminate\Support\Facades\Route::has('register'))
                                <a
                                    class="ms-button ms-button--primary js-magnetic"
                                    href="{{ route('register') }}"
                                >
                                    {{ __('Gratis registreren') }}
                                </a>
                            @endif

                            @if (\Illuminate\Support\Facades\Route::has('login'))
                                <a
                                    class="ms-button js-magnetic"
                                    href="{{ route('login') }}"
                                >
                                    {{ __('Inloggen') }}
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>

                <div class="ms-library-window" aria-hidden="true">
                    <div class="ms-library-head">
                        <strong>{{ __('Mijn afbeeldingen') }}</strong>
                        <span>{{ __('Private library') }}</span>
                    </div>

                    <div class="ms-library-grid">
                        @for ($i = 1; $i <= 6; $i++)
                            <div class="ms-library-thumb">
                                <span class="ms-library-tag">
                                    Project {{ str_pad((string) $i, 2, '0', STR_PAD_LEFT) }}
                                </span>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="ms-section">
        <div class="ms-shell">
            <div class="ms-faq-layout">
                <div class="ms-faq-side" data-reveal="left">
                    <span class="ms-kicker">
                        FAQ
                    </span>

                    <h2>
                        {{ __('Kort. Duidelijk.') }}
                    </h2>

                    <p>
                        {{ __('Alles wat je moet weten over bestanden, versies en je persoonlijke workspace.') }}
                    </p>
                </div>

                <div class="ms-faq-list" data-stagger>
                    <article class="ms-faq-item open">
                        <button
                            class="ms-faq-question"
                            type="button"
                            aria-expanded="true"
                        >
                            <span>{{ __('Welke bestanden kan ik uploaden?') }}</span>
                            <span class="ms-faq-icon" aria-hidden="true"></span>
                        </button>

                        <div class="ms-faq-answer">
                            <div>
                                <div class="ms-faq-answer-inner">
                                    {{ __('JPG/JPEG, PNG en WEBP tot maximaal 20 MB.') }}
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
                            <span>{{ __('Wordt mijn origineel overschreven?') }}</span>
                            <span class="ms-faq-icon" aria-hidden="true"></span>
                        </button>

                        <div class="ms-faq-answer">
                            <div>
                                <div class="ms-faq-answer-inner">
                                    {{ __('Nee. Elke edit wordt als aparte versie opgeslagen.') }}
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
                            <span>{{ __('Kan ik een eerdere versie opnieuw bewerken?') }}</span>
                            <span class="ms-faq-icon" aria-hidden="true"></span>
                        </button>

                        <div class="ms-faq-answer">
                            <div>
                                <div class="ms-faq-answer-inner">
                                    {{ __('Ja. Zowel je origineel als bestaande versies kunnen opnieuw als bron worden gebruikt.') }}
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
                            <span>{{ __('Waar vind ik mijn projecten terug?') }}</span>
                            <span class="ms-faq-icon" aria-hidden="true"></span>
                        </button>

                        <div class="ms-faq-answer">
                            <div>
                                <div class="ms-faq-answer-inner">
                                    {{ __('Na login vind je ze onder “Mijn afbeeldingen”.') }}
                                </div>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section class="ms-final">
        <div class="ms-shell">
            <div class="ms-final-panel" data-reveal>
                <div class="ms-final-copy">
                    <span class="ms-kicker">
                        {{ __('Start editing') }}
                    </span>

                    <h2>
                        {{ __('Eén upload. Daarna ben je vertrokken.') }}
                    </h2>

                    <p>
                        {{ __('Upload je afbeelding, open de editor en bouw je eigen versiegeschiedenis op binnen Mashal Studio.') }}
                    </p>

                    <div class="ms-final-actions">
                        <a
                            class="ms-button ms-button--primary js-magnetic"
                            href="#upload"
                        >
                            {{ __('Upload afbeelding') }}
                        </a>

                        @guest
                            @if (\Illuminate\Support\Facades\Route::has('register'))
                                <a
                                    class="ms-button js-magnetic"
                                    href="{{ route('register') }}"
                                >
                                    {{ __('Account maken') }}
                                </a>
                            @endif
                        @else
                            @if (\Illuminate\Support\Facades\Route::has('images.index'))
                                <a
                                    class="ms-button js-magnetic"
                                    href="{{ route('images.index') }}"
                                >
                                    {{ __('Mijn bibliotheek') }}
                                </a>
                            @endif
                        @endguest
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const doc =
        document;

    const root =
        doc.documentElement;

    const home =
        doc.getElementById('msHome');

    const form =
        doc.getElementById('imageUploadForm');

    const input =
        doc.getElementById('imageInput');

    const dropzone =
        doc.getElementById('imageDropzone');

    const preview =
        doc.getElementById('imagePreview');

    const previewBadge =
        doc.getElementById('previewBadge');

    const fileName =
        doc.getElementById('previewFileName');

    const fileMeta =
        doc.getElementById('previewFileMeta');

    const chooseButton =
        doc.getElementById('chooseImageButton');

    const changeButton =
        doc.getElementById('changeImageButton');

    const submitButton =
        doc.getElementById('submitImageButton');

    const scrollProgress =
        doc.getElementById('msScrollProgress');

    const studioCard =
        doc.getElementById('msStudioCard');

    const editorCanvas =
        doc.getElementById('msEditorCanvas');

    const demoImage =
        doc.getElementById('msDemoImage');

    const prefersReducedMotion =
        window.matchMedia(
            '(prefers-reduced-motion: reduce)'
        );

    const maxBytes =
        20 * 1024 * 1024;

    const allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    let previewUrl =
        null;

    let rafPending =
        false;

    /* =========================================================
       Helpers
       ========================================================= */

    function clamp(value, min, max) {
        return Math.min(
            Math.max(
                value,
                min
            ),
            max
        );
    }

    function lerp(start, end, amount) {
        return (
            start +
            (end - start) *
            amount
        );
    }

    function formatBytes(bytes) {
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
                    mb >= 10
                        ? 1
                        : 2
                ) +
                ' MB'
            );
        }

        return (
            Math.max(
                1,
                Math.round(
                    bytes / 1024
                )
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
        doc
            .getElementById(
                'clientUploadError'
            )
            ?.remove();
    }

    function showClientError(message) {
        clearClientError();

        if (!form) {
            return;
        }

        const alert =
            doc.createElement(
                'div'
            );

        alert.id =
            'clientUploadError';

        alert.className =
            'ms-alert ms-alert--error';

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

        animateError(
            alert
        );
    }

    function animateError(element) {
        if (
            !element ||
            prefersReducedMotion.matches
        ) {
            return;
        }

        element.animate(
            [
                {
                    opacity: 0,
                    transform: 'translateY(-8px)'
                },
                {
                    opacity: 1,
                    transform: 'translateY(0)'
                }
            ],
            {
                duration: 260,
                easing: 'cubic-bezier(.16,1,.3,1)'
            }
        );
    }

    function revokePreviewUrl() {
        if (!previewUrl) {
            return;
        }

        URL.revokeObjectURL(
            previewUrl
        );

        previewUrl =
            null;
    }

    function clearSelection() {
        revokePreviewUrl();

        if (input) {
            input.value =
                '';
        }

        if (preview) {
            preview.removeAttribute(
                'src'
            );
        }

        dropzone?.classList.remove(
            'has-file'
        );
    }

    function animatePreviewIn() {
        const panel =
            dropzone?.querySelector(
                '.ms-preview'
            );

        if (
            !panel ||
            prefersReducedMotion.matches
        ) {
            return;
        }

        panel.animate(
            [
                {
                    opacity: 0,
                    transform: 'scale(.985) translateY(8px)'
                },
                {
                    opacity: 1,
                    transform: 'scale(1) translateY(0)'
                }
            ],
            {
                duration: 420,
                easing: 'cubic-bezier(.16,1,.3,1)'
            }
        );
    }

    function renderFile(file) {
        clearClientError();

        if (!file) {
            return;
        }

        if (
            !allowedTypes.includes(
                file.type
            )
        ) {
            clearSelection();

            showClientError(
                'Gebruik alleen een JPG, PNG of WEBP-afbeelding.'
            );

            return;
        }

        if (
            file.size >
            maxBytes
        ) {
            clearSelection();

            showClientError(
                'Deze afbeelding is groter dan 20 MB.'
            );

            return;
        }

        revokePreviewUrl();

        previewUrl =
            URL.createObjectURL(
                file
            );

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
                formatBytes(
                    file.size
                ) +
                ' · ' +
                mimeLabel(
                    file.type
                );
        }

        if (previewBadge) {
            previewBadge.textContent =
                mimeLabel(
                    file.type
                );
        }

        dropzone?.classList.add(
            'has-file'
        );

        animatePreviewIn();
    }

    function openFilePicker() {
        input?.click();
    }

    /* =========================================================
       Upload interactions
       ========================================================= */

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
                input.files?.[0] ??
                null
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
    ].forEach(
        function (eventName) {
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
        }
    );

    [
        'dragleave',
        'drop'
    ].forEach(
        function (eventName) {
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
        }
    );

    dropzone?.addEventListener(
        'drop',
        function (event) {
            const file =
                event.dataTransfer
                    ?.files
                    ?.[0];

            if (
                !file ||
                !input
            ) {
                return;
            }

            try {
                const transfer =
                    new DataTransfer();

                transfer.items.add(
                    file
                );

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

            renderFile(
                file
            );
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

    /* =========================================================
       FAQ
       ========================================================= */

    doc
        .querySelectorAll(
            '.ms-faq-item'
        )
        .forEach(
            function (item) {
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

                        doc
                            .querySelectorAll(
                                '.ms-faq-item'
                            )
                            .forEach(
                                function (other) {
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
                                }
                            );

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
            }
        );

    /* =========================================================
       Reveal engine
       ========================================================= */

    const revealTargets =
        [
            ...doc.querySelectorAll(
                '[data-reveal], [data-stagger]'
            )
        ];

    if (
        'IntersectionObserver' in window &&
        !prefersReducedMotion.matches
    ) {
        const revealObserver =
            new IntersectionObserver(
                function (entries) {
                    entries.forEach(
                        function (entry) {
                            if (
                                !entry.isIntersecting
                            ) {
                                return;
                            }

                            entry.target.classList.add(
                                'is-visible'
                            );

                            revealObserver.unobserve(
                                entry.target
                            );
                        }
                    );
                },
                {
                    rootMargin:
                        '0px 0px -8% 0px',
                    threshold:
                        .12
                }
            );

        revealTargets.forEach(
            function (target) {
                revealObserver.observe(
                    target
                );
            }
        );
    } else {
        revealTargets.forEach(
            function (target) {
                target.classList.add(
                    'is-visible'
                );
            }
        );
    }

    /* =========================================================
       Scroll progress + sticky upload
       ========================================================= */

    function updateScrollProgress() {
        if (!scrollProgress) {
            return;
        }

        const scrollTop =
            window.scrollY ||
            doc.documentElement.scrollTop;

        const maxScroll =
            doc.documentElement.scrollHeight -
            window.innerHeight;

        const progress =
            maxScroll > 0
                ? clamp(
                    scrollTop /
                    maxScroll,
                    0,
                    1
                )
                : 0;

        scrollProgress.style.width =
            (
                progress *
                100
            ) +
            '%';
    }


    function scheduleScrollWork() {
        if (rafPending) {
            return;
        }

        rafPending =
            true;

        window.requestAnimationFrame(
            function () {
                updateScrollProgress();

                rafPending =
                    false;
            }
        );
    }

    window.addEventListener(
        'scroll',
        scheduleScrollWork,
        {
            passive: true
        }
    );

    /* =========================================================
       Pointer glow
       ========================================================= */

    if (
        home &&
        !prefersReducedMotion.matches
    ) {
        let targetX =
            window.innerWidth *
            .7;

        let targetY =
            120;

        let currentX =
            targetX;

        let currentY =
            targetY;

        window.addEventListener(
            'pointermove',
            function (event) {
                targetX =
                    event.clientX;

                targetY =
                    event.clientY;
            },
            {
                passive: true
            }
        );

        function updatePointerGlow() {
            currentX =
                lerp(
                    currentX,
                    targetX,
                    .08
                );

            currentY =
                lerp(
                    currentY,
                    targetY,
                    .08
                );

            root.style.setProperty(
                '--mx',
                currentX +
                'px'
            );

            root.style.setProperty(
                '--my',
                currentY +
                'px'
            );

            window.requestAnimationFrame(
                updatePointerGlow
            );
        }

        updatePointerGlow();
    }

    /* =========================================================
       Studio 3D tilt
       ========================================================= */

    if (
        studioCard &&
        !prefersReducedMotion.matches &&
        window.matchMedia(
            '(pointer: fine)'
        ).matches
    ) {
        let targetRX =
            0;

        let targetRY =
            0;

        let currentRX =
            0;

        let currentRY =
            0;

        studioCard.addEventListener(
            'pointermove',
            function (event) {
                const rect =
                    studioCard.getBoundingClientRect();

                const x =
                    (
                        event.clientX -
                        rect.left
                    ) /
                    rect.width;

                const y =
                    (
                        event.clientY -
                        rect.top
                    ) /
                    rect.height;

                targetRY =
                    (
                        x -
                        .5
                    ) *
                    5.5;

                targetRX =
                    (
                        .5 -
                        y
                    ) *
                    4.5;
            }
        );

        studioCard.addEventListener(
            'pointerleave',
            function () {
                targetRX =
                    0;

                targetRY =
                    0;
            }
        );

        function animateStudioTilt() {
            currentRX =
                lerp(
                    currentRX,
                    targetRX,
                    .08
                );

            currentRY =
                lerp(
                    currentRY,
                    targetRY,
                    .08
                );

            studioCard.style.transform =
                'rotateX(' +
                currentRX +
                'deg) rotateY(' +
                currentRY +
                'deg)';

            window.requestAnimationFrame(
                animateStudioTilt
            );
        }

        animateStudioTilt();
    }

    /* =========================================================
       Magnetic buttons
       ========================================================= */

    if (
        !prefersReducedMotion.matches &&
        window.matchMedia(
            '(pointer: fine)'
        ).matches
    ) {
        doc
            .querySelectorAll(
                '.js-magnetic'
            )
            .forEach(
                function (button) {
                    let x =
                        0;

                    let y =
                        0;

                    let tx =
                        0;

                    let ty =
                        0;

                    button.addEventListener(
                        'pointermove',
                        function (event) {
                            const rect =
                                button.getBoundingClientRect();

                            tx =
                                (
                                    event.clientX -
                                    (
                                        rect.left +
                                        rect.width /
                                        2
                                    )
                                ) *
                                .12;

                            ty =
                                (
                                    event.clientY -
                                    (
                                        rect.top +
                                        rect.height /
                                        2
                                    )
                                ) *
                                .12;
                        }
                    );

                    button.addEventListener(
                        'pointerleave',
                        function () {
                            tx =
                                0;

                            ty =
                                0;
                        }
                    );

                    function animateMagnetic() {
                        x =
                            lerp(
                                x,
                                tx,
                                .12
                            );

                        y =
                            lerp(
                                y,
                                ty,
                                .12
                            );

                        button.style.transform =
                            'translate3d(' +
                            x +
                            'px,' +
                            y +
                            'px,0)';

                        window.requestAnimationFrame(
                            animateMagnetic
                        );
                    }

                    animateMagnetic();
                }
            );
    }

    /* =========================================================
       Product demo parallax
       ========================================================= */

    if (
        editorCanvas &&
        demoImage &&
        !prefersReducedMotion.matches &&
        window.matchMedia(
            '(pointer: fine)'
        ).matches
    ) {
        let demoX =
            0;

        let demoY =
            0;

        let targetDemoX =
            0;

        let targetDemoY =
            0;

        editorCanvas.addEventListener(
            'pointermove',
            function (event) {
                const rect =
                    editorCanvas.getBoundingClientRect();

                const nx =
                    (
                        event.clientX -
                        rect.left
                    ) /
                    rect.width -
                    .5;

                const ny =
                    (
                        event.clientY -
                        rect.top
                    ) /
                    rect.height -
                    .5;

                targetDemoX =
                    nx *
                    10;

                targetDemoY =
                    ny *
                    10;
            }
        );

        editorCanvas.addEventListener(
            'pointerleave',
            function () {
                targetDemoX =
                    0;

                targetDemoY =
                    0;
            }
        );

        function animateDemo() {
            demoX =
                lerp(
                    demoX,
                    targetDemoX,
                    .08
                );

            demoY =
                lerp(
                    demoY,
                    targetDemoY,
                    .08
                );

            demoImage.style.transform =
                'translate3d(' +
                demoX +
                'px,' +
                demoY +
                'px,0)';

            window.requestAnimationFrame(
                animateDemo
            );
        }

        animateDemo();
    }

    /* =========================================================
       Smooth internal anchors
       ========================================================= */

    doc
        .querySelectorAll(
            'a[href^="#"]'
        )
        .forEach(
            function (anchor) {
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
                            doc.querySelector(
                                href
                            );

                        if (!target) {
                            return;
                        }

                        event.preventDefault();

                        target.scrollIntoView({
                            behavior:
                                prefersReducedMotion.matches
                                    ? 'auto'
                                    : 'smooth',
                            block:
                                'start'
                        });
                    }
                );
            }
        );

    /* =========================================================
       Lifecycle
       ========================================================= */

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

    updateScrollProgress();
});
</script>
@endpush
