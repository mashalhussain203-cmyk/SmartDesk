@extends('layouts.site-layout')

@section('title', 'Mashal Studio | Image Editor')

@section(
    'meta_description',
    'Professionele live image editor voor resize, crop, rotate, flip, enhance, pasfoto, compress, convert en AI-achtergrondverwijdering. Bekijk wijzigingen direct en sla alleen op wanneer je tevreden bent.'
)

@push('styles')
<style>
    .image-editor-page {
        min-height: calc(100vh - 78px);
        padding: 38px 0 74px;
        color: #f7f7f4;
        background:
            radial-gradient(circle at 15% 12%, rgba(229, 182, 111, .08), transparent 28rem),
            radial-gradient(circle at 88% 18%, rgba(102, 92, 255, .07), transparent 30rem),
            linear-gradient(180deg, #07080b, #090b0f);
    }

    .image-editor-shell {
        width: min(100% - 32px, 1480px);
        margin-inline: auto;
    }

    .editor-topbar {
        margin-bottom: 18px;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: 18px;
        background: rgba(255, 255, 255, .025);
        backdrop-filter: blur(18px);
    }

    .editor-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .editor-brand-mark {
        width: 40px;
        height: 40px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        border: 1px solid rgba(229, 182, 111, .18);
        border-radius: 12px;
        color: #f0ca89;
        background: rgba(229, 182, 111, .06);
        font-size: 13px;
        font-weight: 950;
    }

    .editor-brand-copy {
        min-width: 0;
    }

    .editor-brand-copy strong {
        display: block;
        overflow: hidden;
        color: #f3f4f5;
        font-size: 13px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .editor-brand-copy span {
        display: block;
        margin-top: 2px;
        color: #6c727c;
        font-size: 9px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .editor-top-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .editor-link {
        min-height: 38px;
        padding: 0 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255, 255, 255, .09);
        border-radius: 11px;
        color: #c6c9ce;
        background: rgba(255, 255, 255, .025);
        text-decoration: none;
        font-size: 10px;
        font-weight: 900;
        transition: .2s ease;
    }

    .editor-link:hover {
        transform: translateY(-1px);
        border-color: rgba(229, 182, 111, .16);
        color: #e7e9eb;
    }

    .editor-link.primary {
        border-color: rgba(229, 182, 111, .18);
        color: #161009;
        background: linear-gradient(135deg, #f1d08e, #d49b50);
    }

    .editor-alert {
        margin-bottom: 16px;
        padding: 14px 16px;
        border-radius: 14px;
        font-size: 10px;
        line-height: 1.65;
    }

    .editor-alert.success {
        border: 1px solid rgba(100, 217, 139, .13);
        color: #a9d8b7;
        background: rgba(100, 217, 139, .045);
    }

    .editor-alert.error {
        border: 1px solid rgba(244, 125, 125, .14);
        color: #e2a0a0;
        background: rgba(244, 125, 125, .045);
    }

    .editor-alert ul {
        margin: 8px 0 0 18px;
        padding: 0;
    }

    .editor-workspace {
        min-height: 760px;
        display: grid;
        grid-template-columns: 220px minmax(0, 1fr) 330px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: 26px;
        background: #0b0d12;
        box-shadow: 0 40px 110px rgba(0, 0, 0, .38);
    }

    .editor-sidebar {
        padding: 18px 14px;
        border-right: 1px solid rgba(255, 255, 255, .06);
        background: rgba(255, 255, 255, .008);
    }

    .editor-sidebar.right {
        border-right: 0;
        border-left: 1px solid rgba(255, 255, 255, .06);
        overflow-y: auto;
    }

    .editor-sidebar-label {
        margin: 0 8px 12px;
        color: #555c65;
        font-size: 8px;
        font-weight: 950;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .editor-tool-list {
        display: grid;
        gap: 5px;
    }

    .editor-tool {
        width: 100%;
        min-height: 46px;
        padding: 0 11px;
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid transparent;
        border-radius: 12px;
        color: #727984;
        background: transparent;
        font-size: 10px;
        font-weight: 850;
        text-align: left;
        cursor: pointer;
        transition: .2s ease;
    }

    .editor-tool:hover {
        color: #b5bac1;
        background: rgba(255,255,255,.02);
    }

    .editor-tool.active {
        border-color: rgba(229, 182, 111, .13);
        color: #e3bf82;
        background: rgba(229, 182, 111, .05);
    }

    .tool-icon {
        width: 28px;
        height: 28px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(255, 255, 255, .06);
        border-radius: 8px;
        background: rgba(255, 255, 255, .015);
        font-size: 10px;
    }

    .editor-canvas-area {
        position: relative;
        min-width: 0;
        padding: 26px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background:
            linear-gradient(45deg, rgba(255,255,255,.018) 25%, transparent 25%),
            linear-gradient(-45deg, rgba(255,255,255,.018) 25%, transparent 25%),
            linear-gradient(45deg, transparent 75%, rgba(255,255,255,.018) 75%),
            linear-gradient(-45deg, transparent 75%, rgba(255,255,255,.018) 75%),
            #090b0f;
        background-size: 28px 28px;
        background-position: 0 0, 0 14px, 14px -14px, -14px 0;
    }

    .editor-canvas-toolbar {
        margin-bottom: 14px;
        padding: 11px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        border: 1px solid rgba(255,255,255,.06);
        border-radius: 13px;
        background: rgba(8,10,13,.78);
        backdrop-filter: blur(12px);
    }

    .canvas-toolbar-meta {
        min-width: 0;
    }

    .canvas-toolbar-meta strong {
        display: block;
        overflow: hidden;
        color: #d7dbe0;
        font-size: 10px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .canvas-toolbar-meta span {
        display: block;
        margin-top: 3px;
        color: #5f6670;
        font-size: 8px;
    }

    .canvas-toolbar-actions {
        display: flex;
        gap: 7px;
        flex-wrap: wrap;
    }

    .canvas-small-action {
        min-height: 34px;
        padding: 0 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 9px;
        color: #8e959e;
        background: rgba(255,255,255,.018);
        text-decoration: none;
        font-size: 8px;
        font-weight: 900;
    }

    .editor-stage {
        position: relative;
        min-height: 560px;
        flex: 1 1 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: auto;
        border: 1px solid rgba(255,255,255,.06);
        border-radius: 20px;
        background: rgba(4,6,9,.66);
    }

    .editor-stage-inner {
        position: relative;
        flex: 0 0 auto;
        transform-origin: center center;
    }

    .editor-image-surface {
        position: relative;
        width: 100%;
        height: 100%;
        user-select: none;
        -webkit-user-select: none;
        touch-action: none;
    }

    .editor-preview-image {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: fill;
        border-radius: 4px;
        box-shadow: 0 30px 90px rgba(0,0,0,.45);
        background: transparent;
        pointer-events: none;
        user-select: none;
        -webkit-user-drag: none;
    }

    .visual-crop-overlay {
        position: absolute;
        inset: 0;
        z-index: 4;
        overflow: hidden;
        touch-action: none;
    }

    .visual-crop-overlay[hidden] {
        display: none;
    }

    .visual-crop-shade {
        position: absolute;
        background: rgba(0, 0, 0, .58);
        pointer-events: none;
    }

    .visual-crop-box {
        position: absolute;
        min-width: 1px;
        min-height: 1px;
        border: 2px solid rgba(255, 255, 255, .95);
        box-shadow:
            0 0 0 1px rgba(0, 0, 0, .5),
            0 8px 32px rgba(0, 0, 0, .26);
        cursor: move;
        outline: 0;
        touch-action: none;
    }

    .visual-crop-box:focus-visible {
        box-shadow:
            0 0 0 3px rgba(229, 182, 111, .65),
            0 8px 32px rgba(0, 0, 0, .26);
    }

    .visual-crop-grid {
        position: absolute;
        inset: 0;
        pointer-events: none;
        background:
            linear-gradient(
                to right,
                transparent calc(33.333% - .5px),
                rgba(255, 255, 255, .34) calc(33.333% - .5px),
                rgba(255, 255, 255, .34) calc(33.333% + .5px),
                transparent calc(33.333% + .5px),
                transparent calc(66.666% - .5px),
                rgba(255, 255, 255, .34) calc(66.666% - .5px),
                rgba(255, 255, 255, .34) calc(66.666% + .5px),
                transparent calc(66.666% + .5px)
            ),
            linear-gradient(
                to bottom,
                transparent calc(33.333% - .5px),
                rgba(255, 255, 255, .34) calc(33.333% - .5px),
                rgba(255, 255, 255, .34) calc(33.333% + .5px),
                transparent calc(33.333% + .5px),
                transparent calc(66.666% - .5px),
                rgba(255, 255, 255, .34) calc(66.666% - .5px),
                rgba(255, 255, 255, .34) calc(66.666% + .5px),
                transparent calc(66.666% + .5px)
            );
    }

    .crop-handle {
        position: absolute;
        width: 14px;
        height: 14px;
        border: 2px solid #101216;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .35);
    }

    .crop-handle-nw {
        left: 0;
        top: 0;
        transform: translate(-50%, -50%);
        cursor: nwse-resize;
    }

    .crop-handle-n {
        left: 50%;
        top: 0;
        transform: translate(-50%, -50%);
        cursor: ns-resize;
    }

    .crop-handle-ne {
        right: 0;
        top: 0;
        transform: translate(50%, -50%);
        cursor: nesw-resize;
    }

    .crop-handle-e {
        right: 0;
        top: 50%;
        transform: translate(50%, -50%);
        cursor: ew-resize;
    }

    .crop-handle-se {
        right: 0;
        bottom: 0;
        transform: translate(50%, 50%);
        cursor: nwse-resize;
    }

    .crop-handle-s {
        left: 50%;
        bottom: 0;
        transform: translate(-50%, 50%);
        cursor: ns-resize;
    }

    .crop-handle-sw {
        left: 0;
        bottom: 0;
        transform: translate(-50%, 50%);
        cursor: nesw-resize;
    }

    .crop-handle-w {
        left: 0;
        top: 50%;
        transform: translate(-50%, -50%);
        cursor: ew-resize;
    }

    .visual-crop-size {
        position: absolute;
        left: 50%;
        bottom: 10px;
        transform: translateX(-50%);
        padding: 5px 8px;
        border-radius: 999px;
        color: #fff;
        background: rgba(8, 10, 13, .78);
        backdrop-filter: blur(6px);
        font-size: 8px;
        font-weight: 900;
        letter-spacing: .02em;
        white-space: nowrap;
        pointer-events: none;
    }

    .crop-instructions {
        margin-top: 11px;
        padding: 11px 12px;
        border: 1px solid rgba(229, 182, 111, .12);
        border-radius: 11px;
        color: #9b8b71;
        background: rgba(229, 182, 111, .03);
        font-size: 8px;
        line-height: 1.65;
    }

    .editor-stage-loading {
        position: absolute;
        inset: 0;
        display: none;
        place-items: center;
        background: rgba(5,7,10,.58);
        backdrop-filter: blur(3px);
        color: #c9cdd2;
        font-size: 10px;
        font-weight: 900;
        z-index: 5;
    }

    .editor-stage.loading .editor-stage-loading {
        display: grid;
    }

    .property-card {
        margin-bottom: 11px;
        padding: 15px;
        border: 1px solid rgba(255,255,255,.065);
        border-radius: 15px;
        background: rgba(255,255,255,.015);
    }

    .property-card h3 {
        margin: 0;
        color: #c8ccd2;
        font-size: 10px;
    }

    .property-card p {
        margin: 7px 0 0;
        color: #626973;
        font-size: 9px;
        line-height: 1.65;
    }

    .editor-field {
        margin-top: 12px;
        display: grid;
        gap: 6px;
    }

    .editor-field > span {
        color: #6d747d;
        font-size: 8px;
        font-weight: 900;
    }

    .editor-field input,
    .editor-field select {
        width: 100%;
        min-height: 42px;
        padding: 0 11px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 10px;
        color: #d3d6da;
        background: #0d1015;
        outline: 0;
        font-size: 10px;
    }

    .editor-field input[type="range"] {
        min-height: auto;
        padding: 0;
        border: 0;
        background: transparent;
    }

    .editor-field-grid {
        margin-top: 12px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 7px;
    }

    .editor-field-grid .editor-field {
        margin-top: 0;
    }

    .editor-checkbox {
        margin-top: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #818892;
        font-size: 9px;
        font-weight: 800;
    }

    .editor-checkbox input {
        width: 15px;
        height: 15px;
    }

    .editor-choice-grid {
        margin-top: 12px;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 7px;
    }

    .editor-choice {
        position: relative;
    }

    .editor-choice input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .editor-choice span {
        min-height: 40px;
        padding: 0 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 10px;
        color: #818892;
        background: rgba(255,255,255,.015);
        font-size: 9px;
        font-weight: 900;
        cursor: pointer;
    }

    .editor-choice input:checked + span {
        border-color: rgba(229,182,111,.2);
        color: #e5c183;
        background: rgba(229,182,111,.055);
    }

    .editor-submit {
        width: 100%;
        min-height: 44px;
        margin-top: 14px;
        border: 1px solid rgba(229,182,111,.18);
        border-radius: 11px;
        color: #171009;
        background: linear-gradient(135deg, #efd08e, #d49b50);
        font-size: 9px;
        font-weight: 950;
        cursor: pointer;
    }

    .editor-submit[disabled] {
        opacity: .55;
        cursor: not-allowed;
    }

    .editor-action-stack {
        margin-top: 14px;
        display: grid;
        gap: 8px;
    }

    .editor-action-stack .editor-submit {
        margin-top: 0;
    }

    .editor-secondary-action {
        width: 100%;
        min-height: 42px;
        border: 1px solid rgba(120, 210, 255, .14);
        border-radius: 11px;
        color: #a9d7e8;
        background: rgba(120, 210, 255, .04);
        font-size: 9px;
        font-weight: 950;
        cursor: pointer;
        transition:
            border-color .18s ease,
            background .18s ease,
            color .18s ease,
            transform .18s ease;
    }

    .editor-secondary-action:hover {
        transform: translateY(-1px);
        border-color: rgba(120, 210, 255, .24);
        color: #d2eff9;
        background: rgba(120, 210, 255, .07);
    }

    .editor-secondary-action[disabled] {
        opacity: .55;
        cursor: not-allowed;
        transform: none;
    }

    .editor-operation-panel[hidden] {
        display: none !important;
    }

    .version-list {
        margin-top: 12px;
        display: grid;
        gap: 8px;
        max-height: 540px;
        overflow-y: auto;
    }

    .version-item {
        padding: 10px;
        display: grid;
        grid-template-columns: 52px minmax(0,1fr);
        gap: 10px;
        border: 1px solid rgba(255,255,255,.06);
        border-radius: 12px;
        background: rgba(255,255,255,.012);
    }

    .version-thumb {
        width: 52px;
        height: 52px;
        overflow: hidden;
        border-radius: 9px;
        background: #11151b;
    }

    .version-thumb img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
    }

    .version-copy {
        min-width: 0;
    }

    .version-copy strong {
        display: block;
        overflow: hidden;
        color: #cfd3d8;
        font-size: 9px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .version-copy span,
    .version-copy small {
        display: block;
        margin-top: 3px;
        color: #5f6670;
        font-size: 7px;
        line-height: 1.45;
    }

    .version-actions {
        grid-column: 1 / -1;
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .version-actions button,
    .version-actions a {
        min-height: 30px;
        padding: 0 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 8px;
        color: #8c939c;
        background: rgba(255,255,255,.015);
        text-decoration: none;
        font-size: 7px;
        font-weight: 900;
        cursor: pointer;
    }

    .version-actions .danger {
        border-color: rgba(244,125,125,.12);
        color: #ce8585;
    }


    .editor-stage {
        overflow: auto;
    }

    .editor-stage-inner {
        min-width: 1px;
        min-height: 1px;
        display: grid;
        place-items: center;
        transform-origin: center center;
        transition:
            transform .16s ease,
            width .16s ease,
            height .16s ease;
    }

    .editor-preview-image {
        display: block;
        max-width: none;
        max-height: none;
        object-fit: fill;
        transition:
            width .16s ease,
            height .16s ease;
    }

    .editor-live-resize-status {
        margin: 9px 0 13px;
        color: #6d7480;
        font-size: 8px;
        line-height: 1.55;
    }

    .editor-live-resize-status strong {
        color: #d7ad6d;
        font-weight: 900;
    }

    @media (max-width: 1150px) {
        .editor-workspace {
            grid-template-columns: 190px minmax(0, 1fr) 290px;
        }
    }

    @media (max-width: 980px) {
        .editor-workspace {
            grid-template-columns: 180px minmax(0, 1fr);
        }

        .editor-sidebar.right {
            grid-column: 1 / -1;
            border-left: 0;
            border-top: 1px solid rgba(255,255,255,.06);
        }
    }

    @media (max-width: 760px) {
        .image-editor-page {
            padding-top: 24px;
        }

        .editor-topbar {
            align-items: flex-start;
            flex-direction: column;
        }

        .editor-workspace {
            grid-template-columns: 1fr;
            min-height: auto;
        }

        .editor-sidebar {
            border-right: 0;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }

        .editor-tool-list {
            grid-template-columns: repeat(4, minmax(0,1fr));
        }

        .editor-tool {
            min-height: 58px;
            padding: 7px;
            justify-content: center;
            flex-direction: column;
            text-align: center;
        }

        .editor-canvas-area {
            padding: 14px;
        }

        .editor-stage {
            min-height: 430px;
        }

        .editor-sidebar.right {
            grid-column: auto;
        }
    }

    @media (max-width: 540px) {
        .image-editor-shell {
            width: min(100% - 20px, 1480px);
        }

        .editor-tool-list {
            grid-template-columns: repeat(2, minmax(0,1fr));
        }

        .editor-field-grid,
        .editor-choice-grid {
            grid-template-columns: 1fr;
        }

        .editor-canvas-toolbar {
            align-items: flex-start;
            flex-direction: column;
        }
    }

    .passport-guide {
        position: absolute;
        inset: 0;
        pointer-events: none;
    }

    .passport-head-guide {
        position: absolute;
        left: 50%;
        top: 12%;
        width: 56%;
        height: 68%;
        transform: translateX(-50%);
        border: 2px dashed rgba(239, 208, 145, .9);
        border-radius: 48% 48% 45% 45% / 42% 42% 56% 56%;
        box-shadow: 0 0 0 1px rgba(0, 0, 0, .28);
    }

    .passport-eye-line {
        position: absolute;
        left: 8%;
        right: 8%;
        top: 43%;
        height: 1px;
        border-top: 1px dashed rgba(120, 210, 255, .95);
    }

    .passport-eye-line span {
        position: absolute;
        right: 0;
        top: -18px;
        padding: 3px 6px;
        border-radius: 999px;
        color: #d8f3ff;
        background: rgba(7, 14, 19, .72);
        font-size: 8px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .passport-center-line {
        position: absolute;
        top: 6%;
        bottom: 6%;
        left: 50%;
        width: 1px;
        border-left: 1px dashed rgba(255, 255, 255, .32);
    }

    .visual-crop-box.is-passport .crop-handle-n,
    .visual-crop-box.is-passport .crop-handle-e,
    .visual-crop-box.is-passport .crop-handle-s,
    .visual-crop-box.is-passport .crop-handle-w {
        display: none;
    }

    .passport-preset-info {
        margin-top: 12px;
        padding: 11px 12px;
        border: 1px solid rgba(120, 210, 255, .12);
        border-radius: 11px;
        color: #93b7c8;
        background: rgba(120, 210, 255, .025);
        font-size: 10px;
        line-height: 1.6;
    }

    /*
     * --------------------------------------------------------------------------
     * Mobile editor
     * --------------------------------------------------------------------------
     *
     * Op telefoon werkt de editor als een echte mobiele editor:
     * - canvas blijft compact en bruikbaar;
     * - tools blijven onderaan altijd bereikbaar;
     * - instellingen openen als bottom sheet;
     * - form controls zijn minimaal 16px zodat iOS niet automatisch inzoomt;
     * - knoppen hebben comfortabele touch-targets;
     * - geen lange verticale tocht van tools -> canvas -> properties.
     */

    .mobile-properties-open,
    .mobile-properties-close {
        display: none;
    }

    .editor-properties-heading {
        display: block;
    }

    @media (max-width: 760px) {
        .image-editor-page {
            min-height: calc(100svh - 64px);
            padding: 10px 0 calc(96px + env(safe-area-inset-bottom));
            overflow-x: clip;
        }

        .image-editor-shell {
            width: min(100% - 12px, 1480px);
        }

        .editor-topbar {
            margin-bottom: 9px;
            padding: 10px;
            gap: 9px;
            border-radius: 14px;
            align-items: stretch;
            flex-direction: column;
        }

        .editor-brand {
            gap: 9px;
        }

        .editor-brand-mark {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            font-size: 14px;
        }

        .editor-brand-copy strong {
            font-size: 14px;
        }

        .editor-brand-copy span {
            margin-top: 3px;
            font-size: 10px;
            letter-spacing: .05em;
        }

        .editor-top-actions {
            width: 100%;
            display: flex;
            flex-wrap: nowrap;
            gap: 7px;
            overflow-x: auto;
            overscroll-behavior-x: contain;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }

        .editor-top-actions::-webkit-scrollbar,
        .editor-tool-list::-webkit-scrollbar,
        .canvas-toolbar-actions::-webkit-scrollbar {
            display: none;
        }

        .editor-link {
            min-height: 44px;
            padding: 0 13px;
            flex: 0 0 auto;
            border-radius: 10px;
            font-size: 13px;
            white-space: nowrap;
        }

        .editor-alert {
            margin-bottom: 9px;
            padding: 12px 13px;
            border-radius: 12px;
            font-size: 14px;
            line-height: 1.55;
        }

        .editor-workspace {
            display: block;
            min-height: 0;
            overflow: visible;
            border-radius: 18px;
        }

        /*
         * Onderste toolbalk.
         * Deze blijft altijd bereikbaar en voorkomt terug naar boven swipen.
         */
        .editor-sidebar:not(.right) {
            position: fixed;
            z-index: 100;
            left: 0;
            right: 0;
            bottom: 0;
            padding: 7px 7px calc(7px + env(safe-area-inset-bottom));
            border: 0;
            border-top: 1px solid rgba(255,255,255,.10);
            background: rgba(10, 12, 16, .96);
            box-shadow: 0 -18px 45px rgba(0,0,0,.38);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .editor-sidebar:not(.right) .editor-sidebar-label {
            display: none;
        }

        .editor-tool-list {
            display: flex;
            grid-template-columns: none;
            gap: 5px;
            width: 100%;
            overflow-x: auto;
            overscroll-behavior-x: contain;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }

        .editor-tool {
            width: auto;
            min-width: 72px;
            min-height: 64px;
            padding: 6px 5px;
            flex: 0 0 72px;
            justify-content: center;
            flex-direction: column;
            gap: 4px;
            border-radius: 11px;
            font-size: 11px;
            line-height: 1.1;
            text-align: center;
        }

        .tool-icon {
            width: 30px;
            height: 30px;
            border-radius: 9px;
            font-size: 13px;
        }

        .editor-canvas-area {
            padding: 8px;
            overflow: visible;
        }

        .editor-canvas-toolbar {
            margin-bottom: 8px;
            padding: 10px;
            gap: 9px;
            align-items: stretch;
            flex-direction: column;
            border-radius: 12px;
        }

        .canvas-toolbar-meta strong {
            font-size: 14px;
        }

        .canvas-toolbar-meta span {
            margin-top: 4px;
            font-size: 12px;
            line-height: 1.4;
        }

        .canvas-toolbar-actions {
            width: 100%;
            display: flex;
            flex-wrap: nowrap;
            gap: 6px;
            overflow-x: auto;
            overscroll-behavior-x: contain;
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }

        .canvas-small-action {
            min-height: 43px;
            padding: 0 12px;
            flex: 0 0 auto;
            border-radius: 10px;
            font-size: 12px;
            white-space: nowrap;
        }

        .editor-stage {
            height: 56svh;
            min-height: 330px;
            max-height: 620px;
            border-radius: 14px;
            overflow: auto;
            -webkit-overflow-scrolling: touch;
        }

        .editor-stage-loading {
            font-size: 14px;
        }

        /*
         * Properties als bottom sheet.
         * Hierdoor hoef je niet meer onder het canvas naar de instellingen te scrollen.
         */
        .editor-sidebar.right {
            position: fixed;
            z-index: 110;
            left: 8px;
            right: 8px;
            bottom: calc(80px + env(safe-area-inset-bottom));
            max-height: min(58svh, 570px);
            padding: 0 12px 14px;
            overflow-y: auto;
            overscroll-behavior: contain;
            border: 1px solid rgba(255,255,255,.11);
            border-radius: 19px;
            background: rgba(12, 15, 20, .985);
            box-shadow: 0 -18px 60px rgba(0,0,0,.56);
            backdrop-filter: blur(22px);
            -webkit-backdrop-filter: blur(22px);
            transform: translateY(calc(100% + 120px));
            opacity: 0;
            pointer-events: none;
            transition:
                transform .24s ease,
                opacity .18s ease;
        }

        body.mobile-editor-properties-open {
            overflow: hidden;
        }

        body.mobile-editor-properties-open .editor-sidebar.right {
            transform: translateY(0);
            opacity: 1;
            pointer-events: auto;
        }

        .editor-properties-heading {
            position: sticky;
            z-index: 12;
            top: 0;
            margin: 0 -12px 9px;
            padding: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid rgba(255,255,255,.07);
            background: rgba(12, 15, 20, .985);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .editor-sidebar.right .editor-sidebar-label {
            margin: 0;
            color: #d7dbe0;
            font-size: 14px;
            letter-spacing: .02em;
            text-transform: none;
        }

        .mobile-properties-close {
            min-height: 40px;
            padding: 0 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(229,182,111,.18);
            border-radius: 10px;
            color: #edc786;
            background: rgba(229,182,111,.055);
            font-size: 14px;
            font-weight: 900;
            cursor: pointer;
        }

        .mobile-properties-open {
            position: fixed;
            z-index: 105;
            right: 12px;
            bottom: calc(84px + env(safe-area-inset-bottom));
            min-height: 46px;
            padding: 0 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-color: rgba(229,182,111,.22);
            color: #161009;
            background: linear-gradient(135deg, #efd08e, #d49b50);
            box-shadow: 0 12px 34px rgba(0,0,0,.34);
            font-size: 13px;
        }

        body.mobile-editor-properties-open .mobile-properties-open {
            opacity: 0;
            pointer-events: none;
        }

        .property-card {
            margin-bottom: 10px;
            padding: 16px;
            border-radius: 14px;
        }

        .property-card h3 {
            font-size: 18px;
            line-height: 1.25;
        }

        .property-card p {
            margin-top: 8px;
            font-size: 14px;
            line-height: 1.6;
        }

        .editor-field {
            margin-top: 14px;
            gap: 7px;
        }

        .editor-field > span {
            font-size: 13px;
        }

        /*
         * 16px is bewust: Safari/iOS zoomt niet automatisch in op deze velden.
         */
        .editor-field input,
        .editor-field select {
            min-height: 50px;
            padding: 0 12px;
            border-radius: 11px;
            font-size: 16px;
        }

        .editor-field input[type="range"] {
            min-height: 36px;
            padding: 0;
        }

        .editor-field input[type="range"]::-webkit-slider-thumb {
            width: 24px;
            height: 24px;
        }

        .editor-field-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 9px;
        }

        .editor-checkbox {
            margin-top: 14px;
            gap: 10px;
            font-size: 14px;
            line-height: 1.4;
        }

        .editor-checkbox input {
            width: 22px;
            height: 22px;
            flex: 0 0 22px;
        }

        .editor-choice-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 9px;
        }

        .editor-choice span {
            min-height: 50px;
            padding: 0 11px;
            border-radius: 11px;
            font-size: 14px;
        }

        .editor-submit {
            position: sticky;
            z-index: 8;
            bottom: 0;
            min-height: 52px;
            margin-top: 16px;
            border-radius: 12px;
            box-shadow: 0 -8px 24px rgba(12,15,20,.86);
            font-size: 15px;
        }

        .editor-secondary-action {
            min-height: 50px;
            font-size: 14px;
        }

        .editor-action-stack .editor-submit {
            margin-top: 0;
        }

        .editor-live-resize-status,
        .crop-instructions {
            font-size: 12px;
            line-height: 1.55;
        }

        .visual-crop-size {
            bottom: 8px;
            padding: 6px 9px;
            font-size: 11px;
        }

        .crop-handle {
            width: 22px;
            height: 22px;
            border-width: 3px;
        }

        .version-list {
            max-height: none;
            gap: 10px;
        }

        .version-item {
            padding: 12px;
            grid-template-columns: 62px minmax(0,1fr);
            gap: 11px;
        }

        .version-thumb {
            width: 62px;
            height: 62px;
        }

        .version-copy strong {
            font-size: 14px;
        }

        .version-copy span,
        .version-copy small {
            font-size: 12px;
            line-height: 1.45;
        }

        .version-actions button,
        .version-actions a {
            min-height: 42px;
            padding: 0 12px;
            font-size: 12px;
        }
    }

    @media (max-width: 540px) {
        .image-editor-shell {
            width: min(100% - 8px, 1480px);
        }

        .editor-stage {
            height: 52svh;
            min-height: 290px;
        }

        .editor-canvas-area {
            padding: 5px;
        }

        .editor-canvas-toolbar {
            padding: 9px;
        }

        .editor-sidebar.right {
            left: 5px;
            right: 5px;
            bottom: calc(78px + env(safe-area-inset-bottom));
            max-height: 60svh;
        }

        .mobile-properties-open {
            right: 8px;
            bottom: calc(82px + env(safe-area-inset-bottom));
        }
    }

    @media (max-width: 390px) {
        .editor-field-grid,
        .editor-choice-grid {
            grid-template-columns: 1fr;
        }

        .editor-tool {
            min-width: 68px;
            flex-basis: 68px;
        }

        .editor-stage {
            min-height: 265px;
        }
    }


    /*
     * --------------------------------------------------------------------------
     * Background removal
     * --------------------------------------------------------------------------
     */

    .background-mode-grid {
        margin-top: 12px;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 7px;
    }

    .background-setting[hidden] {
        display: none !important;
    }

    .background-color-row {
        display: grid;
        grid-template-columns: 58px minmax(0, 1fr);
        align-items: end;
        gap: 8px;
    }

    .background-color-row input[type="color"] {
        width: 58px;
        min-height: 46px;
        padding: 4px;
        cursor: pointer;
    }

    .background-api-note {
        margin-top: 12px;
        padding: 11px 12px;
        border: 1px solid rgba(120, 210, 255, .12);
        border-radius: 11px;
        color: #8eaebe;
        background: rgba(120, 210, 255, .025);
        font-size: 9px;
        line-height: 1.6;
    }

    .background-api-note strong {
        color: #c8e8f6;
    }

    @media (max-width: 760px) {
        .background-mode-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 9px;
        }

        .background-api-note {
            font-size: 13px;
        }

        .background-color-row input[type="color"] {
            min-height: 50px;
        }
    }

    @media (max-width: 390px) {
        .background-mode-grid {
            grid-template-columns: 1fr;
        }
    }

</style>
@endpush

@section('content')
<div class="image-editor-page">
    <div class="image-editor-shell">

        <div class="editor-topbar">
            <div class="editor-brand">
                <div class="editor-brand-mark">M</div>

                <div class="editor-brand-copy">
                    <strong>{{ $image->display_name ?? $image->original_name }}</strong>
                    <span>Mashal Image Studio · Project #{{ $image->id }}</span>
                </div>
            </div>

            <div class="editor-top-actions">
                <a class="editor-link" href="{{ route('images.index') }}">
                    Mijn afbeeldingen
                </a>

                <a class="editor-link" href="{{ route('images.download', $image) }}">
                    Origineel downloaden
                </a>

                <a class="editor-link primary" href="{{ route('home') }}#upload">
                    Nieuwe upload
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="editor-alert success" role="status">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="editor-alert error" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="editor-alert error" role="alert">
                <strong>De bewerking kon niet worden uitgevoerd.</strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="editor-workspace">

            <aside class="editor-sidebar">
                <div class="editor-sidebar-label">Editor tools</div>

                <div class="editor-tool-list" role="tablist" aria-label="Editor tools">
                    <button type="button" class="editor-tool active" data-operation="resize" role="tab" aria-selected="true">
                        <span class="tool-icon">↔</span>
                        Resize
                    </button>

                    <button type="button" class="editor-tool" data-operation="crop" role="tab" aria-selected="false">
                        <span class="tool-icon">⌗</span>
                        Crop
                    </button>

                    <button type="button" class="editor-tool" data-operation="rotate" role="tab" aria-selected="false">
                        <span class="tool-icon">↻</span>
                        Rotate
                    </button>

                    <button type="button" class="editor-tool" data-operation="flip" role="tab" aria-selected="false">
                        <span class="tool-icon">↔</span>
                        Flip
                    </button>

                    <button type="button" class="editor-tool" data-operation="enhance" role="tab" aria-selected="false">
                        <span class="tool-icon">✦</span>
                        Enhance
                    </button>

                    <button type="button" class="editor-tool" data-operation="passport" role="tab" aria-selected="false">
                        <span class="tool-icon">▣</span>
                        Pasfoto
                    </button>

                    <button type="button" class="editor-tool" data-operation="background" role="tab" aria-selected="false">
                        <span class="tool-icon">◉</span>
                        Achtergrond
                    </button>

                    <button type="button" class="editor-tool" data-operation="compress" role="tab" aria-selected="false">
                        <span class="tool-icon">↓</span>
                        Compress
                    </button>

                    <button type="button" class="editor-tool" data-operation="convert" role="tab" aria-selected="false">
                        <span class="tool-icon">◇</span>
                        Convert
                    </button>

                    <button type="button" class="editor-tool" data-operation="versions" role="tab" aria-selected="false">
                        <span class="tool-icon">▦</span>
                        Versions
                    </button>
                </div>
            </aside>

            <main class="editor-canvas-area">
                <div class="editor-canvas-toolbar">
                    <div class="canvas-toolbar-meta">
                        <strong id="preview-name">
                            {{ $image->display_name ?? $image->original_name }}
                        </strong>

                        <span id="preview-meta">
                            {{ $image->dimensions_label ?? (($image->width ?? '?') . ' × ' . ($image->height ?? '?')) }}
                            ·
                            {{ $image->format_label ?? strtoupper(str_replace('image/', '', (string) $image->mime_type)) }}
                        </span>
                    </div>

                    <div class="canvas-toolbar-actions">
                        <button
                            id="mobile-properties-open"
                            class="canvas-small-action mobile-properties-open"
                            type="button"
                            aria-controls="editor-properties-sidebar"
                            aria-expanded="false"
                        >
                            Instellingen
                        </button>

                        <button
                            id="preview-fit"
                            class="canvas-small-action"
                            type="button"
                        >
                            Passend
                        </button>

                        <button
                            id="preview-100"
                            class="canvas-small-action"
                            type="button"
                        >
                            100%
                        </button>

                        <button
                            id="preview-reset"
                            class="canvas-small-action"
                            type="button"
                        >
                            Reset preview
                        </button>

                        <a
                            id="preview-download"
                            class="canvas-small-action"
                            href="{{ route('images.download', $image) }}"
                        >
                            Download bron
                        </a>
                    </div>
                </div>

                <div class="editor-stage" id="editor-stage">
                    <div class="editor-stage-loading">
                        Afbeelding laden…
                    </div>

                    <div class="editor-stage-inner">
                        <div class="editor-image-surface" id="editor-image-surface">
                            <img
                                id="editor-preview-image"
                                class="editor-preview-image"
                                src="{{ route('images.file', $image) }}"
                                alt="{{ $image->display_name ?? $image->original_name }}"
                            >

                            <div
                                id="visual-crop-overlay"
                                class="visual-crop-overlay"
                                hidden
                                aria-label="Visueel cropgebied"
                            >
                                <div class="visual-crop-shade visual-crop-shade-top"></div>
                                <div class="visual-crop-shade visual-crop-shade-right"></div>
                                <div class="visual-crop-shade visual-crop-shade-bottom"></div>
                                <div class="visual-crop-shade visual-crop-shade-left"></div>

                                <div
                                    id="visual-crop-box"
                                    class="visual-crop-box"
                                    tabindex="0"
                                    role="application"
                                    aria-label="Sleep om het cropgebied te verplaatsen. Gebruik de handgrepen om het formaat te wijzigen."
                                >
                                    <div class="visual-crop-grid" aria-hidden="true"></div>

                                    <div
                                        id="passport-guide"
                                        class="passport-guide"
                                        hidden
                                        aria-hidden="true"
                                    >
                                        <div class="passport-head-guide"></div>
                                        <div class="passport-eye-line">
                                            <span>ogenlijn</span>
                                        </div>
                                        <div class="passport-center-line"></div>
                                    </div>

                                    <span class="crop-handle crop-handle-nw" data-crop-handle="nw" aria-hidden="true"></span>
                                    <span class="crop-handle crop-handle-n" data-crop-handle="n" aria-hidden="true"></span>
                                    <span class="crop-handle crop-handle-ne" data-crop-handle="ne" aria-hidden="true"></span>
                                    <span class="crop-handle crop-handle-e" data-crop-handle="e" aria-hidden="true"></span>
                                    <span class="crop-handle crop-handle-se" data-crop-handle="se" aria-hidden="true"></span>
                                    <span class="crop-handle crop-handle-s" data-crop-handle="s" aria-hidden="true"></span>
                                    <span class="crop-handle crop-handle-sw" data-crop-handle="sw" aria-hidden="true"></span>
                                    <span class="crop-handle crop-handle-w" data-crop-handle="w" aria-hidden="true"></span>

                                    <span class="visual-crop-size" id="visual-crop-size" aria-hidden="true"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <aside
                class="editor-sidebar right"
                id="editor-properties-sidebar"
                aria-label="Editor instellingen"
            >
                <div class="editor-properties-heading">
                    <div class="editor-sidebar-label">Instellingen</div>

                    <button
                        id="mobile-properties-close"
                        class="mobile-properties-close"
                        type="button"
                        aria-label="Instellingen sluiten"
                    >
                        Gereed
                    </button>
                </div>

                <form
                    id="editor-operation-form"
                    method="POST"
                    action="{{ route('images.process', $image) }}"
                    enctype="multipart/form-data"
                >
                    @csrf

                    <input
                        type="hidden"
                        id="operation-input"
                        name="operation"
                        value="resize"
                    >

                    <div class="property-card">
                        <h3>Bronbestand</h3>

                        <p>
                            Kies het origineel of een eerder gemaakte versie als bron voor de volgende bewerking.
                        </p>

                        <label class="editor-field">
                            <span>Bron</span>

                            <select
                                id="source-version"
                                name="source_version_id"
                            >
                                <option
                                    value=""
                                    data-preview="{{ route('images.file', $image) }}"
                                    data-download="{{ route('images.download', $image) }}"
                                    data-name="{{ $image->display_name ?? $image->original_name }}"
                                    data-width="{{ $image->width }}"
                                    data-height="{{ $image->height }}"
                                    data-format="{{ $image->format_label ?? strtoupper(str_replace('image/', '', (string) $image->mime_type)) }}"
                                    @selected(($activeSourceVersionId ?? null) === null)
                                >
                                    Origineel · {{ $image->width ?? '?' }} × {{ $image->height ?? '?' }}
                                </option>

                                @foreach ($image->versions as $version)
                                    <option
                                        value="{{ $version->id }}"
                                        data-preview="{{ route('images.versions.file', [$image, $version]) }}"
                                        data-download="{{ route('images.versions.download', [$image, $version]) }}"
                                        data-name="{{ $version->display_name ?? $version->file_name }}"
                                        data-width="{{ $version->width }}"
                                        data-height="{{ $version->height }}"
                                        data-format="{{ $version->format_label ?? strtoupper($version->format) }}"
                                        @selected((int) ($activeSourceVersionId ?? 0) === (int) $version->id)
                                    >
                                        #{{ $version->id }}
                                        · {{ $version->operation_label ?? ucfirst((string) $version->operation) }}
                                        · {{ $version->width ?? '?' }} × {{ $version->height ?? '?' }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <div class="editor-source-note">
                            De gekozen bron wordt direct in het midden geladen. Na opslaan wordt de nieuwe versie automatisch de actieve bron; je hoeft niet meer handmatig te wisselen.
                        </div>

                        <div
                            id="editor-live-status"
                            class="editor-live-resize-status"
                            role="status"
                            aria-live="polite"
                        >
                            Live preview gereed.
                        </div>
                    </div>

                    <section
                        class="editor-operation-panel"
                        data-panel="resize"
                    >
                        <div class="property-card">
                            <h3>Resize</h3>

                            <p>
                                Geef één of beide afmetingen op. Met aspect ratio ingeschakeld blijft de verhouding behouden.
                            </p>

                            <div class="editor-field-grid">
                                <label class="editor-field">
                                    <span>Breedte</span>
                                    <input
                                        id="resize-width"
                                        type="number"
                                        name="width"
                                        min="1"
                                        max="12000"
                                        value="{{ old('width') }}"
                                        placeholder="{{ $image->width }}"
                                    >
                                </label>

                                <label class="editor-field">
                                    <span>Hoogte</span>
                                    <input
                                        id="resize-height"
                                        type="number"
                                        name="height"
                                        min="1"
                                        max="12000"
                                        value="{{ old('height') }}"
                                        placeholder="{{ $image->height }}"
                                    >
                                </label>
                            </div>

                            <label class="editor-checkbox">
                                <input
                                    id="resize-keep-aspect"
                                    type="checkbox"
                                    name="keep_aspect"
                                    value="1"
                                    @checked(old('keep_aspect', true))
                                >
                                <span>Beeldverhouding behouden</span>
                            </label>

                            <div class="editor-live-resize-status" id="editorLiveResizeStatus">
                                Live preview gebruikt de huidige bronafmetingen.
                            </div>

                            <button class="editor-submit" type="submit">
                                Opslaan als versie
                            </button>
                        </div>
                    </section>

                    <section
                        class="editor-operation-panel"
                        data-panel="crop"
                        hidden
                    >
                        <div class="property-card">
                            <h3>Crop</h3>

                            <p>
                                Pas het uitsnijgebied rechtstreeks op de afbeelding aan.
                                Sleep het kader om te verplaatsen en gebruik de witte handgrepen
                                om het groter of kleiner te maken.
                            </p>

                            <div class="crop-instructions">
                                Geen nummering nodig. Alles gebeurt visueel op de afbeelding.
                                De exacte waarden worden automatisch op de achtergrond bijgehouden.
                            </div>

                            <input id="crop-x" type="hidden" name="crop_x" value="{{ old('crop_x', 0) }}">
                            <input id="crop-y" type="hidden" name="crop_y" value="{{ old('crop_y', 0) }}">
                            <input id="crop-width" type="hidden" name="crop_width" value="{{ old('crop_width', $image->width) }}">
                            <input id="crop-height" type="hidden" name="crop_height" value="{{ old('crop_height', $image->height) }}">

                            <button class="editor-submit" type="submit">
                                Opslaan als versie
                            </button>
                        </div>
                    </section>

                    <section
                        class="editor-operation-panel"
                        data-panel="rotate"
                        hidden
                    >
                        <div class="property-card">
                            <h3>Rotate</h3>

                            <p>
                                Kies de gewenste rotatiehoek.
                            </p>

                            <div class="editor-choice-grid">
                                @foreach ([-90, 90, 180, -180, 270, -270] as $angle)
                                    <label class="editor-choice">
                                        <input
                                            type="radio"
                                            name="angle"
                                            value="{{ $angle }}"
                                            @checked(old('angle', 90) == $angle)
                                        >

                                        <span>{{ $angle }}°</span>
                                    </label>
                                @endforeach
                            </div>

                            <button class="editor-submit" type="submit">
                                Opslaan als versie
                            </button>
                        </div>
                    </section>

                    <section
                        class="editor-operation-panel"
                        data-panel="flip"
                        hidden
                    >
                        <div class="property-card">
                            <h3>Flip</h3>

                            <p>
                                Spiegel de afbeelding horizontaal of verticaal.
                            </p>

                            <div class="editor-choice-grid">
                                <label class="editor-choice">
                                    <input
                                        type="radio"
                                        name="flip_direction"
                                        value="horizontal"
                                        @checked(old('flip_direction', 'horizontal') === 'horizontal')
                                    >

                                    <span>Horizontaal</span>
                                </label>

                                <label class="editor-choice">
                                    <input
                                        type="radio"
                                        name="flip_direction"
                                        value="vertical"
                                        @checked(old('flip_direction') === 'vertical')
                                    >

                                    <span>Verticaal</span>
                                </label>
                            </div>

                            <button class="editor-submit" type="submit">
                                Opslaan als versie
                            </button>
                        </div>
                    </section>

                    <section
                        class="editor-operation-panel"
                        data-panel="enhance"
                        hidden
                    >
                        <div class="property-card">
                            <h3>Enhance</h3>

                            <p>
                                Verbeter de foto live. De preview verandert direct terwijl je schuift.
                                Opslaan maakt daarna een echte nieuwe versie op de server.
                            </p>

                            <label class="editor-field">
                                <span>
                                    Helderheid:
                                    <strong id="enhance-brightness-value">0</strong>
                                </span>

                                <input
                                    id="enhance-brightness"
                                    type="range"
                                    name="brightness"
                                    min="-100"
                                    max="100"
                                    value="0"
                                >
                            </label>

                            <label class="editor-field">
                                <span>
                                    Contrast:
                                    <strong id="enhance-contrast-value">0</strong>
                                </span>

                                <input
                                    id="enhance-contrast"
                                    type="range"
                                    name="contrast"
                                    min="-100"
                                    max="100"
                                    value="0"
                                >
                            </label>

                            <label class="editor-field">
                                <span>
                                    Blur:
                                    <strong id="enhance-blur-value">0</strong>
                                </span>

                                <input
                                    id="enhance-blur"
                                    type="range"
                                    name="blur"
                                    min="0"
                                    max="6"
                                    step="1"
                                    value="0"
                                >
                            </label>

                            <label class="editor-checkbox">
                                <input
                                    id="enhance-grayscale"
                                    type="checkbox"
                                    name="grayscale"
                                    value="1"
                                >

                                <span>Zwart-wit</span>
                            </label>

                            <label class="editor-checkbox">
                                <input
                                    id="enhance-sepia"
                                    type="checkbox"
                                    name="sepia"
                                    value="1"
                                >

                                <span>Sepia / warme klassieke look</span>
                            </label>

                            <div class="editor-source-note">
                                Tip: combineer lichte contrast- en helderheidsaanpassingen voor een natuurlijk resultaat.
                            </div>

                            <button class="editor-submit" type="submit">
                                Opslaan als versie
                            </button>
                        </div>
                    </section>

                    <section
                        class="editor-operation-panel"
                        data-panel="passport"
                        hidden
                    >
                        <div class="property-card">
                            <h3>Pasfoto / ID-foto</h3>

                            <p>
                                Kies een formaat en positioneer de persoon rechtstreeks in het kader.
                                Het kader behoudt automatisch de juiste verhouding.
                            </p>

                            <label class="editor-field">
                                <span>Formaat</span>

                                <select
                                    id="passport-preset"
                                    name="passport_preset"
                                >
                                    @foreach (config('mashal-image.passport_presets', []) as $presetKey => $preset)
                                        <option
                                            value="{{ $presetKey }}"
                                            data-width="{{ (int) ($preset['width'] ?? 0) }}"
                                            data-height="{{ (int) ($preset['height'] ?? 0) }}"
                                            data-description="{{ $preset['description'] ?? '' }}"
                                            @selected($presetKey === 'nl_35x45')
                                        >
                                            {{ $preset['label'] ?? $presetKey }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <div
                                id="passport-preset-info"
                                class="passport-preset-info"
                                role="status"
                                aria-live="polite"
                            ></div>

                            <div class="crop-instructions">
                                Sleep het kader over de foto en gebruik de hoekhandgrepen.
                                De gestippelde hoofdvorm en ogenlijn zijn visuele hulpmiddelen,
                                geen automatische officiële goedkeuring.
                            </div>

                            <input id="passport-crop-x" type="hidden" name="crop_x" value="0">
                            <input id="passport-crop-y" type="hidden" name="crop_y" value="0">
                            <input id="passport-crop-width" type="hidden" name="crop_width" value="{{ $image->width }}">
                            <input id="passport-crop-height" type="hidden" name="crop_height" value="{{ $image->height }}">

                            <div class="editor-source-note">
                                Controleer altijd de actuele officiële foto-eisen van het document waarvoor je exporteert.
                            </div>

                            <button class="editor-submit" type="submit">
                                Opslaan als versie
                            </button>
                        </div>
                    </section>

                    <section
                        class="editor-operation-panel"
                        data-panel="background"
                        hidden
                    >
                        <div class="property-card">
                            <h3>Achtergrond</h3>

                            <p>
                                Verwijder de bestaande achtergrond met remove.bg en exporteer transparant,
                                met een effen kleur of met een compleet nieuwe achtergrondafbeelding.
                            </p>

                            <div class="background-mode-grid" role="radiogroup" aria-label="Achtergrondmodus">
                                <label class="editor-choice">
                                    <input
                                        type="radio"
                                        name="background_mode"
                                        value="transparent"
                                        checked
                                    >
                                    <span>Transparant</span>
                                </label>

                                <label class="editor-choice">
                                    <input
                                        type="radio"
                                        name="background_mode"
                                        value="white"
                                    >
                                    <span>Wit</span>
                                </label>

                                <label class="editor-choice">
                                    <input
                                        type="radio"
                                        name="background_mode"
                                        value="color"
                                    >
                                    <span>Eigen kleur</span>
                                </label>

                                <label class="editor-choice">
                                    <input
                                        type="radio"
                                        name="background_mode"
                                        value="url"
                                    >
                                    <span>Afbeelding-URL</span>
                                </label>

                                <label class="editor-choice">
                                    <input
                                        type="radio"
                                        name="background_mode"
                                        value="upload"
                                    >
                                    <span>Upload achtergrond</span>
                                </label>
                            </div>

                            <div
                                class="background-setting"
                                data-background-setting="color"
                                hidden
                            >
                                <div class="background-color-row">
                                    <label class="editor-field">
                                        <span>Kleur</span>
                                        <input
                                            id="background-color-picker"
                                            type="color"
                                            value="#ffffff"
                                            aria-label="Achtergrondkleur kiezen"
                                        >
                                    </label>

                                    <label class="editor-field">
                                        <span>Hexkleur</span>
                                        <input
                                            id="background-color"
                                            type="text"
                                            name="background_color"
                                            value="#ffffff"
                                            maxlength="32"
                                            placeholder="#ffffff"
                                            autocomplete="off"
                                        >
                                    </label>
                                </div>
                            </div>

                            <div
                                class="background-setting"
                                data-background-setting="url"
                                hidden
                            >
                                <label class="editor-field">
                                    <span>URL van nieuwe achtergrond</span>
                                    <input
                                        id="background-url"
                                        type="url"
                                        name="background_url"
                                        placeholder="https://voorbeeld.nl/achtergrond.jpg"
                                        autocomplete="off"
                                        inputmode="url"
                                    >
                                </label>
                            </div>

                            <div
                                class="background-setting"
                                data-background-setting="upload"
                                hidden
                            >
                                <label class="editor-field">
                                    <span>Nieuwe achtergrond uploaden</span>
                                    <input
                                        id="background-image"
                                        type="file"
                                        name="background_image"
                                        accept="image/jpeg,image/png,image/webp"
                                    >
                                </label>
                            </div>

                            <div class="editor-field-grid">
                                <label class="editor-field">
                                    <span>Outputformaat</span>

                                    <select
                                        id="background-format"
                                        name="background_format"
                                    >
                                        <option value="png" selected>PNG</option>
                                        <option value="webp">WEBP</option>
                                        <option value="jpg">JPG</option>
                                    </select>
                                </label>

                                <label class="editor-field">
                                    <span>API-resolutie</span>

                                    <select
                                        id="background-size"
                                        name="background_size"
                                    >
                                        <option value="auto" selected>Auto</option>
                                        <option value="preview">Preview</option>
                                        <option value="full">Full</option>
                                        <option value="50mp">Tot 50 MP</option>
                                    </select>
                                </label>
                            </div>

                            <div class="background-api-note">
                                <strong>Live AI-preview:</strong>
                                klik eerst op <em>AI-preview uitvoeren</em>.
                                Het resultaat verschijnt direct op dezelfde foto en wordt nog niet als versie opgeslagen.
                                Als je daarna op <em>Opslaan als versie</em> klikt, gebruikt Laravel dezelfde tijdelijke preview
                                zodat de externe background-API niet onnodig nog een keer wordt aangeroepen.
                            </div>

                            <input
                                id="background-preview-token"
                                type="hidden"
                                name="background_preview_token"
                                value=""
                            >

                            <div class="editor-action-stack">
                                <button
                                    id="background-live-preview"
                                    class="editor-secondary-action"
                                    type="button"
                                >
                                    AI-preview uitvoeren
                                </button>

                                <button class="editor-submit" type="submit">
                                    Opslaan als versie
                                </button>
                            </div>
                        </div>
                    </section>

                    <section
                        class="editor-operation-panel"
                        data-panel="compress"
                        hidden
                    >
                        <div class="property-card">
                            <h3>Compress</h3>

                            <p>
                                Maak een nieuwe versie met een andere exportkwaliteit.
                            </p>

                            <label class="editor-field">
                                <span>
                                    Kwaliteit:
                                    <strong id="compress-quality-value">
                                        {{ old('quality', 82) }}
                                    </strong>%
                                </span>

                                <input
                                    id="compress-quality"
                                    type="range"
                                    name="quality"
                                    min="1"
                                    max="100"
                                    value="{{ old('quality', 82) }}"
                                >
                            </label>

                            <div class="editor-source-note">
                                De preview wordt tijdens het schuiven opnieuw gecodeerd. De uiteindelijke server-export kan enkele bytes verschillen.
                            </div>

                            <button class="editor-submit" type="submit">
                                Opslaan als versie
                            </button>
                        </div>
                    </section>

                    <section
                        class="editor-operation-panel"
                        data-panel="convert"
                        hidden
                    >
                        <div class="property-card">
                            <h3>Convert</h3>

                            <p>
                                Exporteer de gekozen bron als JPG, PNG of WEBP.
                            </p>

                            <label class="editor-field">
                                <span>Doelformaat</span>

                                <select id="convert-format" name="format">
                                    <option value="jpg" @selected(old('format') === 'jpg')>JPG</option>
                                    <option value="png" @selected(old('format') === 'png')>PNG</option>
                                    <option value="webp" @selected(old('format', 'webp') === 'webp')>WEBP</option>
                                </select>
                            </label>

                            <label class="editor-field">
                                <span>
                                    Kwaliteit:
                                    <strong id="convert-quality-value">
                                        {{ old('quality', 88) }}
                                    </strong>%
                                </span>

                                <input
                                    id="convert-quality"
                                    type="range"
                                    name="quality"
                                    min="1"
                                    max="100"
                                    value="{{ old('quality', 88) }}"
                                >
                            </label>

                            <div class="editor-source-note">
                                Het gekozen formaat wordt live in de browser gerenderd. Opslaan maakt daarna de definitieve serverversie.
                            </div>

                            <button class="editor-submit" type="submit">
                                Opslaan als versie
                            </button>
                        </div>
                    </section>

                    <section
                        class="editor-operation-panel"
                        data-panel="versions"
                        hidden
                    >
                        <div class="property-card">
                            <h3>Versiegeschiedenis</h3>

                            <p>
                                Bekijk, gebruik, download of verwijder eerder opgeslagen versies.
                            </p>

                            <div class="version-list">
                                <article class="version-item">
                                    <div class="version-thumb">
                                        <img
                                            src="{{ route('images.file', $image) }}"
                                            alt=""
                                            loading="lazy"
                                        >
                                    </div>

                                    <div class="version-copy">
                                        <strong>Origineel</strong>
                                        <span>
                                            {{ $image->width ?? '?' }} × {{ $image->height ?? '?' }}
                                            ·
                                            {{ $image->format_label ?? strtoupper(str_replace('image/', '', (string) $image->mime_type)) }}
                                        </span>

                                        <small>
                                            {{ $image->formatted_file_size ?? number_format(($image->file_size ?? 0) / 1024, 1) . ' KB' }}
                                        </small>
                                    </div>

                                    <div class="version-actions">
                                        <button
                                            type="button"
                                            data-use-version=""
                                        >
                                            Gebruiken
                                        </button>

                                        <a href="{{ route('images.download', $image) }}">
                                            Download
                                        </a>
                                    </div>
                                </article>

                                @forelse ($image->versions as $version)
                                    <article class="version-item">
                                        <div class="version-thumb">
                                            <img
                                                src="{{ route('images.versions.file', [$image, $version]) }}"
                                                alt=""
                                                loading="lazy"
                                            >
                                        </div>

                                        <div class="version-copy">
                                            <strong>
                                                Versie #{{ $version->id }}
                                            </strong>

                                            <span>
                                                {{ $version->operation_label ?? ucfirst((string) $version->operation) }}
                                                ·
                                                {{ $version->width ?? '?' }} × {{ $version->height ?? '?' }}
                                                ·
                                                {{ $version->format_label ?? strtoupper((string) $version->format) }}
                                            </span>

                                            <small>
                                                {{ $version->formatted_file_size ?? number_format(($version->file_size ?? 0) / 1024, 1) . ' KB' }}
                                                ·
                                                {{ $version->created_at?->format('d-m-Y H:i') }}
                                            </small>
                                        </div>

                                        <div class="version-actions">
                                            <button
                                                type="button"
                                                data-use-version="{{ $version->id }}"
                                            >
                                                Gebruiken
                                            </button>

                                            <a href="{{ route('images.versions.download', [$image, $version]) }}">
                                                Download
                                            </a>

                                            <button
                                                type="button"
                                                class="danger"
                                                data-delete-version-form="delete-version-{{ $version->id }}"
                                            >
                                                Verwijderen
                                            </button>
                                        </div>
                                    </article>
                                @empty
                                    <p>
                                        Er zijn nog geen bewerkte versies opgeslagen.
                                    </p>
                                @endforelse
                            </div>
                        </div>
                    </section>
                </form>

                @foreach ($image->versions as $version)
                    <form
                        id="delete-version-{{ $version->id }}"
                        method="POST"
                        action="{{ route('images.versions.destroy', [$image, $version]) }}"
                        hidden
                    >
                        @csrf
                        @method('DELETE')
                    </form>
                @endforeach
            </aside>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    /*
     * Mashal Studio live image editor
     * --------------------------------
     * Alle bewerkingen worden eerst volledig client-side als preview uitgevoerd.
     * Het bestaande Laravel-formulier blijft verantwoordelijk voor definitief opslaan.
     */

    const MAX_DIMENSION = 12000;
    const MAX_PIXELS = 80000000;

    /*
     * Om de browser bij extreem grote afbeeldingen responsief te houden, renderen
     * we een werkpreview met een begrensde resolutie. De weergegeven doelafmetingen
     * en de backend-save blijven de echte gekozen afmetingen gebruiken.
     */
    const LIVE_RENDER_MAX_DIMENSION = 4096;
    const LIVE_RENDER_MAX_PIXELS = 16000000;

    const tools = Array.from(
        document.querySelectorAll('[data-operation]')
    );

    const panels = Array.from(
        document.querySelectorAll('[data-panel]')
    );

    const form =
        document.getElementById('editor-operation-form');

    const operationInput =
        document.getElementById('operation-input');

    const sourceSelect =
        document.getElementById('source-version');

    const previewImage =
        document.getElementById('editor-preview-image');

    const previewName =
        document.getElementById('preview-name');

    const previewMeta =
        document.getElementById('preview-meta');

    const previewDownload =
        document.getElementById('preview-download');

    const liveStatus =
        document.getElementById('editor-live-status');

    const stage =
        document.getElementById('editor-stage');

    const stageInner =
        stage
            ? stage.querySelector('.editor-stage-inner')
            : null;

    const imageSurface =
        document.getElementById('editor-image-surface');

    const cropOverlay =
        document.getElementById('visual-crop-overlay');

    const cropBox =
        document.getElementById('visual-crop-box');

    const cropSizeLabel =
        document.getElementById('visual-crop-size');

    const cropShadeTop =
        cropOverlay?.querySelector('.visual-crop-shade-top');

    const cropShadeRight =
        cropOverlay?.querySelector('.visual-crop-shade-right');

    const cropShadeBottom =
        cropOverlay?.querySelector('.visual-crop-shade-bottom');

    const cropShadeLeft =
        cropOverlay?.querySelector('.visual-crop-shade-left');

    const fitButton =
        document.getElementById('preview-fit');

    const actualButton =
        document.getElementById('preview-100');

    const resetButton =
        document.getElementById('preview-reset');

    const resizeWidth =
        document.getElementById('resize-width');

    const resizeHeight =
        document.getElementById('resize-height');

    const resizeKeepAspect =
        document.getElementById('resize-keep-aspect');

    const liveResizeStatus =
        document.getElementById('editorLiveResizeStatus');

    const cropX =
        document.getElementById('crop-x');

    const cropY =
        document.getElementById('crop-y');

    const cropWidth =
        document.getElementById('crop-width');

    const cropHeight =
        document.getElementById('crop-height');

    const compressQuality =
        document.getElementById('compress-quality');

    const compressQualityValue =
        document.getElementById('compress-quality-value');

    const convertFormat =
        document.getElementById('convert-format');

    const convertQuality =
        document.getElementById('convert-quality');

    const convertQualityValue =
        document.getElementById('convert-quality-value');

    const enhanceBrightness =
        document.getElementById('enhance-brightness');

    const enhanceBrightnessValue =
        document.getElementById('enhance-brightness-value');

    const enhanceContrast =
        document.getElementById('enhance-contrast');

    const enhanceContrastValue =
        document.getElementById('enhance-contrast-value');

    const enhanceBlur =
        document.getElementById('enhance-blur');

    const enhanceBlurValue =
        document.getElementById('enhance-blur-value');

    const enhanceGrayscale =
        document.getElementById('enhance-grayscale');

    const enhanceSepia =
        document.getElementById('enhance-sepia');

    const passportPreset =
        document.getElementById('passport-preset');

    const passportPresetInfo =
        document.getElementById('passport-preset-info');

    const passportGuide =
        document.getElementById('passport-guide');

    const passportCropX =
        document.getElementById('passport-crop-x');

    const passportCropY =
        document.getElementById('passport-crop-y');

    const passportCropWidth =
        document.getElementById('passport-crop-width');

    const passportCropHeight =
        document.getElementById('passport-crop-height');

    const backgroundModeInputs =
        Array.from(
            document.querySelectorAll(
                'input[name="background_mode"]'
            )
        );

    const backgroundSettings =
        Array.from(
            document.querySelectorAll(
                '[data-background-setting]'
            )
        );

    const backgroundColorPicker =
        document.getElementById('background-color-picker');

    const backgroundColor =
        document.getElementById('background-color');

    const backgroundUrl =
        document.getElementById('background-url');

    const backgroundImage =
        document.getElementById('background-image');

    const backgroundFormat =
        document.getElementById('background-format');

    const backgroundSize =
        document.getElementById('background-size');

    const backgroundPreviewToken =
        document.getElementById('background-preview-token');

    const backgroundLivePreviewButton =
        document.getElementById('background-live-preview');

    const propertiesSidebar =
        document.getElementById('editor-properties-sidebar');

    const mobilePropertiesOpen =
        document.getElementById('mobile-properties-open');

    const mobilePropertiesClose =
        document.getElementById('mobile-properties-close');

    const mobileEditorMedia =
        window.matchMedia('(max-width: 760px)');

    let zoomMode = 'fit';

    let sourceWidth = 1;
    let sourceHeight = 1;
    let sourceFormat = 'IMAGE';
    let sourceUrl = '';
    let sourceName = 'Afbeelding';

    let previewWidth = 1;
    let previewHeight = 1;

    let sourceBitmap = null;
    let previewObjectUrl = null;

    let aspectUpdating = false;
    let renderTimer = null;
    let renderSequence = 0;
    let savingOperation = false;
    let requestingBackgroundPreview = false;

    let cropRect = {
        x: 0,
        y: 0,
        width: 1,
        height: 1,
    };

    let cropPointerState = null;
    let lockedCropAspectRatio = null;

    function positiveNumber(value, fallback = null) {
        const parsed = Number(value);

        if (
            !Number.isFinite(parsed) ||
            parsed <= 0
        ) {
            return fallback;
        }

        return parsed;
    }

    function nonNegativeNumber(value, fallback = 0) {
        const parsed = Number(value);

        if (
            !Number.isFinite(parsed) ||
            parsed < 0
        ) {
            return fallback;
        }

        return parsed;
    }

    function integer(value, fallback = 0) {
        const parsed = Number(value);

        if (!Number.isFinite(parsed)) {
            return fallback;
        }

        return Math.round(parsed);
    }

    function selectedSourceOption() {
        if (!sourceSelect) {
            return null;
        }

        return sourceSelect.options[
            sourceSelect.selectedIndex
        ] || null;
    }

    function activeOperation() {
        return operationInput?.value || 'resize';
    }

    function setStatus(message, isError = false) {
        if (!liveStatus) {
            return;
        }

        liveStatus.textContent = message;
        liveStatus.style.color = isError
            ? '#efaaaa'
            : '';
    }

    function setLoading(loading) {
        stage?.classList.toggle(
            'loading',
            Boolean(loading)
        );
    }

    function releasePreviewObjectUrl() {
        if (previewObjectUrl) {
            URL.revokeObjectURL(
                previewObjectUrl
            );

            previewObjectUrl = null;
        }
    }


    function isMobileEditor() {
        return mobileEditorMedia.matches;
    }

    function openMobileProperties() {
        if (!isMobileEditor()) {
            return;
        }

        document.body.classList.add(
            'mobile-editor-properties-open'
        );

        mobilePropertiesOpen?.setAttribute(
            'aria-expanded',
            'true'
        );

        /*
         * Zorg dat het actieve paneel bovenin zichtbaar begint zonder
         * de hoofdwebpagina te verplaatsen.
         */
        if (propertiesSidebar) {
            propertiesSidebar.scrollTop = 0;
        }
    }

    function closeMobileProperties() {
        document.body.classList.remove(
            'mobile-editor-properties-open'
        );

        mobilePropertiesOpen?.setAttribute(
            'aria-expanded',
            'false'
        );
    }

    mobilePropertiesOpen?.addEventListener(
        'click',
        openMobileProperties
    );

    mobilePropertiesClose?.addEventListener(
        'click',
        closeMobileProperties
    );

    mobileEditorMedia.addEventListener?.(
        'change',
        function (event) {
            if (!event.matches) {
                closeMobileProperties();
            }
        }
    );

    function setPanelControlsEnabled(panel, enabled) {
        panel
            .querySelectorAll(
                'input, select, textarea, button[type="submit"]'
            )
            .forEach(function (control) {
                control.disabled = !enabled;
            });
    }

    function activePanelFor(operation) {
        return panels.find(function (panel) {
            return (
                panel.dataset.panel ===
                operation
            );
        }) || null;
    }

    function clearBackgroundPreviewToken() {
        if (backgroundPreviewToken) {
            backgroundPreviewToken.value = '';
        }
    }

    function invalidateBackgroundPreview(
        restoreSource = true
    ) {
        clearBackgroundPreviewToken();

        if (
            restoreSource &&
            activeOperation() === 'background'
        ) {
            showSourceWithoutTransformation();
        }
    }

    function validateBackgroundSelection() {
        const mode =
            selectedBackgroundMode();

        if (
            mode === 'color' &&
            !backgroundColor?.value.trim()
        ) {
            return 'Kies eerst een achtergrondkleur.';
        }

        if (
            mode === 'url' &&
            !backgroundUrl?.value.trim()
        ) {
            return 'Vul eerst een URL van een achtergrondafbeelding in.';
        }

        if (
            mode === 'upload' &&
            !backgroundImage?.files?.length
        ) {
            return 'Kies eerst een achtergrondafbeelding om te uploaden.';
        }

        if (
            mode === 'transparent' &&
            backgroundFormat?.value === 'jpg'
        ) {
            return 'Kies PNG of WebP voor een transparante achtergrond.';
        }

        return '';
    }

    function prepareActiveFormData(
        operation,
        extra = {}
    ) {
        if (!form) {
            throw new Error(
                'Het editorformulier kon niet worden gevonden.'
            );
        }

        const activePanel =
            activePanelFor(
                operation
            );

        if (!activePanel) {
            throw new Error(
                'De gekozen editorbewerking is niet beschikbaar.'
            );
        }

        panels.forEach(function (panel) {
            setPanelControlsEnabled(
                panel,
                panel === activePanel
            );
        });

        if (operation === 'background') {
            syncBackgroundControls();
        }

        const data =
            new FormData(
                form
            );

        Object.entries(
            extra
        ).forEach(function (entry) {
            data.set(
                entry[0],
                String(
                    entry[1]
                )
            );
        });

        return {
            activePanel,
            data,
        };
    }

    function responseErrorMessage(
        payload,
        fallback
    ) {
        if (
            payload &&
            typeof payload.message === 'string' &&
            payload.message.trim()
        ) {
            return payload.message.trim();
        }

        if (
            payload &&
            payload.errors &&
            typeof payload.errors === 'object'
        ) {
            for (
                const messages
                of Object.values(
                    payload.errors
                )
            ) {
                if (
                    Array.isArray(messages) &&
                    typeof messages[0] === 'string'
                ) {
                    return messages[0];
                }
            }
        }

        return fallback;
    }

    async function readJsonResponse(
        response
    ) {
        try {
            return await response.json();
        } catch (error) {
            return null;
        }
    }

    function updateAddressBarSource(
        versionId
    ) {
        try {
            const url =
                new URL(
                    window.location.href
                );

            url.searchParams.set(
                'source',
                String(
                    versionId
                )
            );

            window.history.replaceState(
                {},
                '',
                url
            );
        } catch (error) {
            // Niet kritisch: de editor blijft gewoon werken.
        }
    }

    async function useSavedVersion(
        version
    ) {
        if (
            !sourceSelect ||
            !version ||
            !version.id
        ) {
            return;
        }

        const value =
            String(
                version.id
            );

        let option =
            Array.from(
                sourceSelect.options
            ).find(function (candidate) {
                return candidate.value === value;
            });

        if (!option) {
            option =
                document.createElement(
                    'option'
                );

            option.value =
                value;

            /*
             * Nieuwe versies bovenaan houden, direct na Origineel.
             */
            if (
                sourceSelect.options.length > 1
            ) {
                sourceSelect.insertBefore(
                    option,
                    sourceSelect.options[1]
                );
            } else {
                sourceSelect.appendChild(
                    option
                );
            }
        }

        option.dataset.preview =
            version.preview_url ||
            '';

        option.dataset.download =
            version.download_url ||
            '';

        option.dataset.name =
            version.name ||
            (
                'Versie #' +
                value
            );

        option.dataset.width =
            String(
                version.width ||
                1
            );

        option.dataset.height =
            String(
                version.height ||
                1
            );

        option.dataset.format =
            version.format_label ||
            (
                version.format
                    ? String(version.format).toUpperCase()
                    : 'IMAGE'
            );

        option.textContent =
            '#' +
            value +
            ' · ' +
            (
                version.operation_label ||
                'Bewerking'
            ) +
            ' · ' +
            (
                version.width ||
                '?'
            ) +
            ' × ' +
            (
                version.height ||
                '?'
            );

        sourceSelect.value =
            value;

        updateAddressBarSource(
            value
        );

        await applySelectedSource();
    }

    async function requestBackgroundLivePreview() {
        if (
            requestingBackgroundPreview ||
            savingOperation
        ) {
            return;
        }

        if (
            activeOperation() !==
            'background'
        ) {
            activateOperation(
                'background'
            );
        }

        const validationMessage =
            validateBackgroundSelection();

        if (validationMessage) {
            setStatus(
                validationMessage,
                true
            );

            return;
        }

        let prepared;

        try {
            prepared =
                prepareActiveFormData(
                    'background',
                    {
                        preview_only: 1,
                    }
                );
        } catch (error) {
            setStatus(
                error instanceof Error
                    ? error.message
                    : 'De AI-preview kon niet worden voorbereid.',
                true
            );

            return;
        }

        requestingBackgroundPreview =
            true;

        clearBackgroundPreviewToken();

        const originalLabel =
            backgroundLivePreviewButton?.textContent ||
            'AI-preview uitvoeren';

        if (backgroundLivePreviewButton) {
            backgroundLivePreviewButton.disabled =
                true;

            backgroundLivePreviewButton.textContent =
                'AI-preview maken…';
        }

        setLoading(
            true
        );

        setStatus(
            'AI-achtergrond wordt verwerkt. Het resultaat verschijnt direct op dezelfde foto…'
        );

        try {
            const response =
                await fetch(
                    form.action,
                    {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept':
                                'application/json, image/*',
                            'X-Requested-With':
                                'XMLHttpRequest',
                        },
                        body:
                            prepared.data,
                    }
                );

            if (!response.ok) {
                const contentType =
                    response.headers.get(
                        'content-type'
                    ) || '';

                let message =
                    'De live achtergrond-preview kon niet worden gemaakt.';

                if (
                    contentType.includes(
                        'application/json'
                    )
                ) {
                    const payload =
                        await readJsonResponse(
                            response
                        );

                    message =
                        responseErrorMessage(
                            payload,
                            message
                        );
                } else {
                    const text =
                        await response.text();

                    if (text.trim()) {
                        message =
                            text.trim();
                    }
                }

                throw new Error(
                    message
                );
            }

            const token =
                response.headers.get(
                    'X-Mashal-Background-Preview-Token'
                ) || '';

            const width =
                positiveNumber(
                    response.headers.get(
                        'X-Mashal-Preview-Width'
                    ),
                    sourceWidth
                );

            const height =
                positiveNumber(
                    response.headers.get(
                        'X-Mashal-Preview-Height'
                    ),
                    sourceHeight
                );

            const format =
                response.headers.get(
                    'X-Mashal-Preview-Format'
                ) ||
                (
                    backgroundFormat?.value ||
                    'png'
                ).toUpperCase();

            const blob =
                await response.blob();

            if (!blob.size) {
                throw new Error(
                    'De AI-preview bevatte geen afbeeldingsdata.'
                );
            }

            releasePreviewObjectUrl();

            previewObjectUrl =
                URL.createObjectURL(
                    blob
                );

            if (previewImage) {
                previewImage.src =
                    previewObjectUrl;

                previewImage.alt =
                    sourceName +
                    ' AI-achtergrond-preview';
            }

            setPreviewDimensions(
                width,
                height
            );

            if (previewMeta) {
                previewMeta.textContent =
                    Math.round(width) +
                    ' × ' +
                    Math.round(height) +
                    ' · ' +
                    format +
                    ' · LIVE AI';
            }

            if (backgroundPreviewToken) {
                backgroundPreviewToken.value =
                    token;
            }

            setStatus(
                'AI-preview gereed. Dit resultaat staat nu live op de foto en is nog niet opgeslagen. Klik op "Opslaan als versie" wanneer je tevreden bent.'
            );
        } catch (error) {
            clearBackgroundPreviewToken();

            showSourceWithoutTransformation();

            setStatus(
                error instanceof Error
                    ? error.message
                    : 'De live achtergrond-preview kon niet worden gemaakt.',
                true
            );
        } finally {
            requestingBackgroundPreview =
                false;

            setLoading(
                false
            );

            if (backgroundLivePreviewButton) {
                backgroundLivePreviewButton.disabled =
                    false;

                backgroundLivePreviewButton.textContent =
                    originalLabel;
            }
        }
    }

    function selectedBackgroundMode() {
        const selected =
            backgroundModeInputs.find(
                function (input) {
                    return input.checked;
                }
            );

        return selected?.value || 'transparent';
    }

    function syncBackgroundControls() {
        const mode =
            selectedBackgroundMode();

        backgroundSettings.forEach(
            function (setting) {
                const active =
                    setting.dataset.backgroundSetting ===
                    mode;

                setting.hidden =
                    !active;

                setting
                    .querySelectorAll(
                        'input, select, textarea'
                    )
                    .forEach(function (control) {
                        control.disabled =
                            !active;
                    });
            }
        );

        if (backgroundFormat) {
            const jpgOption =
                Array.from(
                    backgroundFormat.options
                ).find(
                    function (option) {
                        return option.value === 'jpg';
                    }
                );

            if (jpgOption) {
                jpgOption.disabled =
                    mode === 'transparent';
            }

            if (
                mode === 'transparent' &&
                backgroundFormat.value === 'jpg'
            ) {
                backgroundFormat.value =
                    'png';
            }
        }
    }

    function backgroundModeStatus() {
        const mode =
            selectedBackgroundMode();

        return matchFormat(
            mode,
            {
                transparent: 'Transparant geselecteerd. Klik op AI-preview uitvoeren om het resultaat direct op de foto te bekijken.',
                white: 'Wit geselecteerd. Klik op AI-preview uitvoeren om het resultaat direct op de foto te bekijken.',
                color: 'Kleur geselecteerd. Klik op AI-preview uitvoeren om het resultaat direct op de foto te bekijken.',
                url: 'URL-achtergrond geselecteerd. Klik op AI-preview uitvoeren om het resultaat direct op de foto te bekijken.',
                upload: 'Geüploade achtergrond geselecteerd. Klik op AI-preview uitvoeren om het resultaat direct op de foto te bekijken.',
            },
            'Achtergrondbewerking gereed.'
        );
    }

    function activateOperation(operation) {
        tools.forEach(function (tool) {
            const active =
                tool.dataset.operation === operation;

            tool.classList.toggle(
                'active',
                active
            );

            tool.setAttribute(
                'aria-selected',
                active ? 'true' : 'false'
            );
        });

        panels.forEach(function (panel) {
            const active =
                panel.dataset.panel === operation;

            panel.hidden = !active;

            setPanelControlsEnabled(
                panel,
                active
            );
        });

        if (operationInput) {
            operationInput.value =
                operation === 'versions'
                    ? ''
                    : operation;
        }

        if (operation === 'versions') {
            hideVisualCrop();
            showSourceWithoutTransformation();
            setStatus(
                'Versiegeschiedenis geopend. Kies een versie om die als nieuwe bron te gebruiken.'
            );
            return;
        }

        if (operation === 'crop') {
            lockedCropAspectRatio = null;
            cropBox?.classList.remove('is-passport');

            if (passportGuide) {
                passportGuide.hidden = true;
            }

            showSourceWithoutTransformation();
            showVisualCrop();
            setStatus(
                'Crop is actief. Sleep het kader of de witte handgrepen rechtstreeks op de afbeelding.'
            );
            return;
        }

        if (operation === 'passport') {
            configurePassportMode(true);
            showSourceWithoutTransformation();
            showVisualCrop();

            setStatus(
                'Pasfoto-modus actief. Positioneer de persoon in het kader en gebruik de hoekhandgrepen.'
            );

            return;
        }

        if (operation === 'background') {
            clearBackgroundPreviewToken();
            lockedCropAspectRatio = null;
            cropBox?.classList.remove('is-passport');

            if (passportGuide) {
                passportGuide.hidden = true;
            }

            hideVisualCrop();
            showSourceWithoutTransformation();
            syncBackgroundControls();

            setStatus(
                backgroundModeStatus()
            );

            return;
        }

        lockedCropAspectRatio = null;
        cropBox?.classList.remove('is-passport');

        if (passportGuide) {
            passportGuide.hidden = true;
        }

        hideVisualCrop();
        scheduleLiveRender(true);
    }

    tools.forEach(function (tool) {
        tool.addEventListener(
            'click',
            function () {
                activateOperation(
                    tool.dataset.operation
                );

                openMobileProperties();
            }
        );
    });

    function getStageAvailableSize() {
        return {
            width: stage
                ? Math.max(
                    1,
                    stage.clientWidth - 80
                )
                : 1,

            height: stage
                ? Math.max(
                    1,
                    stage.clientHeight - 80
                )
                : 1,
        };
    }

    function getFitScale() {
        const available =
            getStageAvailableSize();

        return Math.min(
            1,
            available.width /
                Math.max(
                    1,
                    previewWidth
                ),
            available.height /
                Math.max(
                    1,
                    previewHeight
                )
        );
    }

    function applyPreviewGeometry() {
        if (
            !previewImage ||
            !stageInner
        ) {
            return;
        }

        previewImage.style.width =
            previewWidth + 'px';

        previewImage.style.height =
            previewHeight + 'px';

        stageInner.style.width =
            previewWidth + 'px';

        stageInner.style.height =
            previewHeight + 'px';

        if (imageSurface) {
            imageSurface.style.width =
                previewWidth + 'px';

            imageSurface.style.height =
                previewHeight + 'px';
        }

        const scale =
            zoomMode === '100'
                ? 1
                : getFitScale();

        stageInner.style.transform =
            'scale(' + scale + ')';
    }

    function setPreviewDimensions(
        width,
        height
    ) {
        previewWidth = Math.max(
            1,
            Math.round(width)
        );

        previewHeight = Math.max(
            1,
            Math.round(height)
        );

        applyPreviewGeometry();
    }

    function formatBytes(bytes) {
        if (
            !Number.isFinite(bytes) ||
            bytes < 0
        ) {
            return '';
        }

        if (bytes >= 1024 * 1024) {
            return (
                bytes /
                (1024 * 1024)
            ).toFixed(2) + ' MB';
        }

        if (bytes >= 1024) {
            return (
                bytes / 1024
            ).toFixed(1) + ' KB';
        }

        return Math.round(bytes) + ' B';
    }

    function normalizeFormat(format) {
        const value =
            String(format || '')
                .trim()
                .toLowerCase();

        if (
            value === 'jpeg' ||
            value === 'jpg'
        ) {
            return 'jpg';
        }

        if (value === 'png') {
            return 'png';
        }

        if (value === 'webp') {
            return 'webp';
        }

        return 'jpg';
    }

    function formatToMime(format) {
        return matchFormat(
            normalizeFormat(format),
            {
                jpg: 'image/jpeg',
                png: 'image/png',
                webp: 'image/webp',
            },
            'image/jpeg'
        );
    }

    function matchFormat(value, map, fallback) {
        return Object.prototype.hasOwnProperty.call(
            map,
            value
        )
            ? map[value]
            : fallback;
    }

    function currentSourceFormat() {
        return normalizeFormat(
            sourceFormat
        );
    }

    function outputFormatForOperation(operation) {
        if (operation === 'convert') {
            return normalizeFormat(
                convertFormat?.value || 'webp'
            );
        }

        if (operation === 'background') {
            return normalizeFormat(
                backgroundFormat?.value || 'png'
            );
        }

        return currentSourceFormat();
    }

    function qualityForOperation(operation) {
        if (operation === 'compress') {
            return Math.max(
                1,
                Math.min(
                    100,
                    integer(
                        compressQuality?.value,
                        82
                    )
                )
            );
        }

        if (operation === 'convert') {
            return Math.max(
                1,
                Math.min(
                    100,
                    integer(
                        convertQuality?.value,
                        88
                    )
                )
            );
        }

        if (operation === 'background') {
            return 100;
        }

        return 88;
    }

    function validateTargetDimensions(
        width,
        height
    ) {
        if (
            width < 1 ||
            height < 1 ||
            width > MAX_DIMENSION ||
            height > MAX_DIMENSION
        ) {
            throw new Error(
                'Afmetingen moeten tussen 1 en ' +
                MAX_DIMENSION +
                ' pixels liggen.'
            );
        }

        if (
            width * height >
            MAX_PIXELS
        ) {
            throw new Error(
                'Deze afmetingen zijn te groot. Maximaal 80 miljoen pixels.'
            );
        }
    }

    function calculateResizeTarget() {
        let width =
            positiveNumber(
                resizeWidth?.value,
                null
            );

        let height =
            positiveNumber(
                resizeHeight?.value,
                null
            );

        if (
            width === null &&
            height === null
        ) {
            width = sourceWidth;
            height = sourceHeight;
        }

        if (resizeKeepAspect?.checked) {
            if (
                width !== null &&
                height === null
            ) {
                height = Math.max(
                    1,
                    Math.round(
                        sourceHeight *
                        (width / sourceWidth)
                    )
                );
            } else if (
                height !== null &&
                width === null
            ) {
                width = Math.max(
                    1,
                    Math.round(
                        sourceWidth *
                        (height / sourceHeight)
                    )
                );
            } else if (
                width !== null &&
                height !== null
            ) {
                const scale = Math.min(
                    width / sourceWidth,
                    height / sourceHeight
                );

                width = Math.max(
                    1,
                    Math.round(
                        sourceWidth * scale
                    )
                );

                height = Math.max(
                    1,
                    Math.round(
                        sourceHeight * scale
                    )
                );
            }
        } else {
            width ??= sourceWidth;
            height ??= sourceHeight;
        }

        width = Math.round(width);
        height = Math.round(height);

        validateTargetDimensions(
            width,
            height
        );

        return {
            width,
            height,
        };
    }

    function syncHeightFromWidth() {
        if (
            aspectUpdating ||
            !resizeKeepAspect?.checked
        ) {
            return;
        }

        const width =
            positiveNumber(
                resizeWidth?.value,
                null
            );

        if (width === null) {
            return;
        }

        aspectUpdating = true;

        if (resizeHeight) {
            resizeHeight.value =
                Math.max(
                    1,
                    Math.round(
                        sourceHeight *
                        (width / sourceWidth)
                    )
                );
        }

        aspectUpdating = false;
    }

    function syncWidthFromHeight() {
        if (
            aspectUpdating ||
            !resizeKeepAspect?.checked
        ) {
            return;
        }

        const height =
            positiveNumber(
                resizeHeight?.value,
                null
            );

        if (height === null) {
            return;
        }

        aspectUpdating = true;

        if (resizeWidth) {
            resizeWidth.value =
                Math.max(
                    1,
                    Math.round(
                        sourceWidth *
                        (height / sourceHeight)
                    )
                );
        }

        aspectUpdating = false;
    }

    function updateResizeStatus(target) {
        if (!liveResizeStatus) {
            return;
        }

        const widthPercent =
            Math.round(
                (
                    target.width /
                    Math.max(
                        1,
                        sourceWidth
                    )
                ) * 100
            );

        const heightPercent =
            Math.round(
                (
                    target.height /
                    Math.max(
                        1,
                        sourceHeight
                    )
                ) * 100
            );

        liveResizeStatus.innerHTML =
            'Live resultaat: <strong>' +
            target.width +
            ' × ' +
            target.height +
            ' px</strong> · ' +
            widthPercent +
            '% × ' +
            heightPercent +
            '% van de bron.';
    }

    function getPreviewRenderSize(
        targetWidth,
        targetHeight
    ) {
        let scale = Math.min(
            1,
            LIVE_RENDER_MAX_DIMENSION /
                Math.max(
                    targetWidth,
                    targetHeight
                ),
            Math.sqrt(
                LIVE_RENDER_MAX_PIXELS /
                Math.max(
                    1,
                    targetWidth * targetHeight
                )
            )
        );

        if (
            !Number.isFinite(scale) ||
            scale <= 0
        ) {
            scale = 1;
        }

        return {
            width: Math.max(
                1,
                Math.round(
                    targetWidth * scale
                )
            ),
            height: Math.max(
                1,
                Math.round(
                    targetHeight * scale
                )
            ),
            scale,
        };
    }

    function createCanvas(width, height) {
        const canvas =
            document.createElement('canvas');

        canvas.width =
            Math.max(
                1,
                Math.round(width)
            );

        canvas.height =
            Math.max(
                1,
                Math.round(height)
            );

        return canvas;
    }

    function context2d(canvas) {
        const context =
            canvas.getContext(
                '2d',
                {
                    alpha: true,
                    desynchronized: true,
                }
            );

        if (!context) {
            throw new Error(
                'Je browser kan geen 2D afbeeldingspreview maken.'
            );
        }

        context.imageSmoothingEnabled = true;
        context.imageSmoothingQuality = 'high';

        return context;
    }

    function renderBaseCanvas(
        width = sourceWidth,
        height = sourceHeight
    ) {
        if (!sourceBitmap) {
            throw new Error(
                'De bronafbeelding is nog niet geladen.'
            );
        }

        const renderSize =
            getPreviewRenderSize(
                width,
                height
            );

        const canvas =
            createCanvas(
                renderSize.width,
                renderSize.height
            );

        const ctx =
            context2d(
                canvas
            );

        ctx.drawImage(
            sourceBitmap,
            0,
            0,
            canvas.width,
            canvas.height
        );

        return {
            canvas,
            targetWidth: width,
            targetHeight: height,
        };
    }

    function renderResize() {
        const target =
            calculateResizeTarget();

        updateResizeStatus(
            target
        );

        return renderBaseCanvas(
            target.width,
            target.height
        );
    }


    function clamp(value, min, max) {
        return Math.min(
            Math.max(value, min),
            max
        );
    }

    function minimumCropSize() {
        return Math.max(
            1,
            Math.round(
                Math.min(
                    sourceWidth,
                    sourceHeight
                ) * 0.02
            )
        );
    }

    function normalizeCropRect(rect) {
        const minSize =
            minimumCropSize();

        let width =
            clamp(
                Math.round(rect.width),
                minSize,
                sourceWidth
            );

        let height =
            clamp(
                Math.round(rect.height),
                minSize,
                sourceHeight
            );

        let x =
            clamp(
                Math.round(rect.x),
                0,
                sourceWidth - width
            );

        let y =
            clamp(
                Math.round(rect.y),
                0,
                sourceHeight - height
            );

        return {
            x,
            y,
            width,
            height,
        };
    }

    function syncCropInputs() {
        if (cropX) {
            cropX.value =
                Math.round(cropRect.x);
        }

        if (cropY) {
            cropY.value =
                Math.round(cropRect.y);
        }

        if (cropWidth) {
            cropWidth.value =
                Math.round(cropRect.width);
        }

        if (cropHeight) {
            cropHeight.value =
                Math.round(cropRect.height);
        }

        if (passportCropX) {
            passportCropX.value =
                Math.round(cropRect.x);
        }

        if (passportCropY) {
            passportCropY.value =
                Math.round(cropRect.y);
        }

        if (passportCropWidth) {
            passportCropWidth.value =
                Math.round(cropRect.width);
        }

        if (passportCropHeight) {
            passportCropHeight.value =
                Math.round(cropRect.height);
        }
    }

    function updateVisualCrop() {
        if (
            !cropOverlay ||
            !cropBox
        ) {
            return;
        }

        cropRect =
            normalizeCropRect(
                cropRect
            );

        syncCropInputs();

        const left =
            (
                cropRect.x /
                sourceWidth
            ) * 100;

        const top =
            (
                cropRect.y /
                sourceHeight
            ) * 100;

        const width =
            (
                cropRect.width /
                sourceWidth
            ) * 100;

        const height =
            (
                cropRect.height /
                sourceHeight
            ) * 100;

        cropBox.style.left =
            left + '%';

        cropBox.style.top =
            top + '%';

        cropBox.style.width =
            width + '%';

        cropBox.style.height =
            height + '%';

        if (cropSizeLabel) {
            cropSizeLabel.textContent =
                Math.round(cropRect.width) +
                ' × ' +
                Math.round(cropRect.height) +
                ' px';
        }

        if (cropShadeTop) {
            cropShadeTop.style.left = '0';
            cropShadeTop.style.top = '0';
            cropShadeTop.style.width = '100%';
            cropShadeTop.style.height = top + '%';
        }

        if (cropShadeBottom) {
            cropShadeBottom.style.left = '0';
            cropShadeBottom.style.top =
                (top + height) + '%';
            cropShadeBottom.style.width = '100%';
            cropShadeBottom.style.height =
                Math.max(
                    0,
                    100 - top - height
                ) + '%';
        }

        if (cropShadeLeft) {
            cropShadeLeft.style.left = '0';
            cropShadeLeft.style.top = top + '%';
            cropShadeLeft.style.width = left + '%';
            cropShadeLeft.style.height = height + '%';
        }

        if (cropShadeRight) {
            cropShadeRight.style.left =
                (left + width) + '%';
            cropShadeRight.style.top = top + '%';
            cropShadeRight.style.width =
                Math.max(
                    0,
                    100 - left - width
                ) + '%';
            cropShadeRight.style.height = height + '%';
        }
    }

    function selectedPassportPreset() {
        if (!passportPreset) {
            return null;
        }

        return passportPreset.options[
            passportPreset.selectedIndex
        ] || null;
    }

    function passportTarget() {
        const option =
            selectedPassportPreset();

        if (!option) {
            return null;
        }

        const width =
            positiveNumber(
                option.dataset.width,
                null
            );

        const height =
            positiveNumber(
                option.dataset.height,
                null
            );

        if (
            width === null ||
            height === null
        ) {
            return null;
        }

        return {
            width,
            height,
            ratio:
                width / height,
            description:
                option.dataset.description || '',
            label:
                option.textContent?.trim() || 'Pasfoto',
        };
    }

    function updatePassportPresetInfo() {
        const target =
            passportTarget();

        if (
            !target ||
            !passportPresetInfo
        ) {
            return;
        }

        passportPresetInfo.textContent =
            target.label +
            ' · export ' +
            Math.round(target.width) +
            ' × ' +
            Math.round(target.height) +
            ' px' +
            (
                target.description
                    ? ' · ' + target.description
                    : ''
            );
    }

    function initializePassportCrop(force = false) {
        const target =
            passportTarget();

        if (!target) {
            return;
        }

        lockedCropAspectRatio =
            target.ratio;

        cropBox?.classList.add(
            'is-passport'
        );

        if (passportGuide) {
            passportGuide.hidden = false;
        }

        if (
            !force &&
            cropRect.width > 1 &&
            cropRect.height > 1
        ) {
            updateVisualCrop();
            return;
        }

        const maxWidth =
            sourceWidth * 0.84;

        const maxHeight =
            sourceHeight * 0.84;

        let width =
            maxWidth;

        let height =
            width /
            target.ratio;

        if (height > maxHeight) {
            height =
                maxHeight;

            width =
                height *
                target.ratio;
        }

        cropRect = normalizeCropRect({
            x:
                (sourceWidth - width) / 2,
            y:
                (sourceHeight - height) / 2,
            width,
            height,
        });

        updateVisualCrop();
    }

    function configurePassportMode(force = false) {
        updatePassportPresetInfo();
        initializePassportCrop(force);
    }

    function initializeVisualCrop(force = false) {
        if (
            !force &&
            cropRect.width > 1 &&
            cropRect.height > 1 &&
            cropRect.width <= sourceWidth &&
            cropRect.height <= sourceHeight
        ) {
            updateVisualCrop();
            return;
        }

        const inset = 0.08;

        cropRect = {
            x: Math.round(
                sourceWidth * inset
            ),
            y: Math.round(
                sourceHeight * inset
            ),
            width: Math.max(
                1,
                Math.round(
                    sourceWidth *
                    (1 - inset * 2)
                )
            ),
            height: Math.max(
                1,
                Math.round(
                    sourceHeight *
                    (1 - inset * 2)
                )
            ),
        };

        updateVisualCrop();
    }

    function showVisualCrop() {
        if (!cropOverlay) {
            return;
        }

        cropOverlay.hidden = false;

        initializeVisualCrop();

        applyPreviewGeometry();
    }

    function hideVisualCrop() {
        if (!cropOverlay) {
            return;
        }

        cropOverlay.hidden = true;
        cropPointerState = null;
    }

    function clientPointToImage(
        clientX,
        clientY
    ) {
        if (!imageSurface) {
            return null;
        }

        const bounds =
            imageSurface.getBoundingClientRect();

        if (
            bounds.width <= 0 ||
            bounds.height <= 0
        ) {
            return null;
        }

        return {
            x:
                (
                    clientX -
                    bounds.left
                ) /
                bounds.width *
                sourceWidth,

            y:
                (
                    clientY -
                    bounds.top
                ) /
                bounds.height *
                sourceHeight,
        };
    }

    function beginCropPointer(
        event,
        mode,
        handle = null
    ) {
        const point =
            clientPointToImage(
                event.clientX,
                event.clientY
            );

        if (!point) {
            return;
        }

        event.preventDefault();

        cropPointerState = {
            pointerId:
                event.pointerId,
            mode,
            handle,
            startPoint:
                point,
            startRect:
                { ...cropRect },
        };

        cropBox?.setPointerCapture?.(
            event.pointerId
        );
    }

    function moveCropRect(
        deltaX,
        deltaY,
        startRect
    ) {
        cropRect = normalizeCropRect({
            x:
                startRect.x +
                deltaX,

            y:
                startRect.y +
                deltaY,

            width:
                startRect.width,

            height:
                startRect.height,
        });
    }

    function resizeCropRect(
        deltaX,
        deltaY,
        startRect,
        handle
    ) {
        if (
            lockedCropAspectRatio &&
            ['nw', 'ne', 'se', 'sw'].includes(handle)
        ) {
            resizeLockedAspectCropRect(
                deltaX,
                deltaY,
                startRect,
                handle,
                lockedCropAspectRatio
            );

            return;
        }

        const minSize =
            minimumCropSize();

        let left =
            startRect.x;

        let top =
            startRect.y;

        let right =
            startRect.x +
            startRect.width;

        let bottom =
            startRect.y +
            startRect.height;

        if (handle.includes('w')) {
            left =
                clamp(
                    startRect.x +
                    deltaX,
                    0,
                    right - minSize
                );
        }

        if (handle.includes('e')) {
            right =
                clamp(
                    right +
                    deltaX,
                    left + minSize,
                    sourceWidth
                );
        }

        if (handle.includes('n')) {
            top =
                clamp(
                    startRect.y +
                    deltaY,
                    0,
                    bottom - minSize
                );
        }

        if (handle.includes('s')) {
            bottom =
                clamp(
                    bottom +
                    deltaY,
                    top + minSize,
                    sourceHeight
                );
        }

        cropRect = normalizeCropRect({
            x: left,
            y: top,
            width:
                right - left,
            height:
                bottom - top,
        });
    }

    function resizeLockedAspectCropRect(
        deltaX,
        deltaY,
        startRect,
        handle,
        ratio
    ) {
        const minSize =
            minimumCropSize();

        let anchorX;
        let anchorY;
        let horizontalDirection;
        let verticalDirection;

        switch (handle) {
            case 'nw':
                anchorX =
                    startRect.x +
                    startRect.width;
                anchorY =
                    startRect.y +
                    startRect.height;
                horizontalDirection = -1;
                verticalDirection = -1;
                break;

            case 'ne':
                anchorX =
                    startRect.x;
                anchorY =
                    startRect.y +
                    startRect.height;
                horizontalDirection = 1;
                verticalDirection = -1;
                break;

            case 'sw':
                anchorX =
                    startRect.x +
                    startRect.width;
                anchorY =
                    startRect.y;
                horizontalDirection = -1;
                verticalDirection = 1;
                break;

            default:
                anchorX =
                    startRect.x;
                anchorY =
                    startRect.y;
                horizontalDirection = 1;
                verticalDirection = 1;
                break;
        }

        const startCornerX =
            horizontalDirection === 1
                ? startRect.x + startRect.width
                : startRect.x;

        const startCornerY =
            verticalDirection === 1
                ? startRect.y + startRect.height
                : startRect.y;

        const pointerX =
            startCornerX +
            deltaX;

        const pointerY =
            startCornerY +
            deltaY;

        const rawWidth =
            Math.abs(
                pointerX -
                anchorX
            );

        const rawHeight =
            Math.abs(
                pointerY -
                anchorY
            );

        let width =
            Math.max(
                minSize,
                rawWidth,
                rawHeight * ratio
            );

        const maxWidthFromX =
            horizontalDirection === 1
                ? sourceWidth - anchorX
                : anchorX;

        const maxHeightFromY =
            verticalDirection === 1
                ? sourceHeight - anchorY
                : anchorY;

        width =
            Math.min(
                width,
                maxWidthFromX,
                maxHeightFromY * ratio
            );

        width =
            Math.max(
                Math.min(
                    width,
                    sourceWidth
                ),
                Math.min(
                    minSize,
                    maxWidthFromX
                )
            );

        const height =
            width /
            ratio;

        const x =
            horizontalDirection === 1
                ? anchorX
                : anchorX - width;

        const y =
            verticalDirection === 1
                ? anchorY
                : anchorY - height;

        cropRect = normalizeCropRect({
            x,
            y,
            width,
            height,
        });
    }

    function handleCropPointerMove(event) {
        if (
            !cropPointerState ||
            event.pointerId !==
                cropPointerState.pointerId
        ) {
            return;
        }

        const point =
            clientPointToImage(
                event.clientX,
                event.clientY
            );

        if (!point) {
            return;
        }

        event.preventDefault();

        const deltaX =
            point.x -
            cropPointerState.startPoint.x;

        const deltaY =
            point.y -
            cropPointerState.startPoint.y;

        if (
            cropPointerState.mode ===
            'move'
        ) {
            moveCropRect(
                deltaX,
                deltaY,
                cropPointerState.startRect
            );
        } else {
            resizeCropRect(
                deltaX,
                deltaY,
                cropPointerState.startRect,
                cropPointerState.handle
            );
        }

        updateVisualCrop();

        setStatus(
            'Cropgebied: ' +
            Math.round(cropRect.width) +
            ' × ' +
            Math.round(cropRect.height) +
            ' px. Sleep verder of klik op Opslaan als versie.'
        );
    }

    function endCropPointer(event) {
        if (
            !cropPointerState ||
            event.pointerId !==
                cropPointerState.pointerId
        ) {
            return;
        }

        cropPointerState = null;
    }

    cropBox?.addEventListener(
        'pointerdown',
        function (event) {
            const handle =
                event.target.closest?.(
                    '[data-crop-handle]'
                );

            if (handle) {
                beginCropPointer(
                    event,
                    'resize',
                    handle.dataset.cropHandle
                );

                return;
            }

            beginCropPointer(
                event,
                'move'
            );
        }
    );

    cropBox?.addEventListener(
        'pointermove',
        handleCropPointerMove
    );

    cropBox?.addEventListener(
        'pointerup',
        endCropPointer
    );

    cropBox?.addEventListener(
        'pointercancel',
        endCropPointer
    );

    cropBox?.addEventListener(
        'keydown',
        function (event) {
            const step =
                event.shiftKey
                    ? 10
                    : 1;

            let handled = true;

            switch (event.key) {
                case 'ArrowLeft':
                    cropRect.x -= step;
                    break;

                case 'ArrowRight':
                    cropRect.x += step;
                    break;

                case 'ArrowUp':
                    cropRect.y -= step;
                    break;

                case 'ArrowDown':
                    cropRect.y += step;
                    break;

                default:
                    handled = false;
            }

            if (!handled) {
                return;
            }

            event.preventDefault();

            cropRect =
                normalizeCropRect(
                    cropRect
                );

            updateVisualCrop();
        }
    );

    function renderCrop() {
        const x = Math.round(
            nonNegativeNumber(
                cropX?.value,
                0
            )
        );

        const y = Math.round(
            nonNegativeNumber(
                cropY?.value,
                0
            )
        );

        const width = Math.round(
            positiveNumber(
                cropWidth?.value,
                sourceWidth
            )
        );

        const height = Math.round(
            positiveNumber(
                cropHeight?.value,
                sourceHeight
            )
        );

        validateTargetDimensions(
            width,
            height
        );

        if (
            x >= sourceWidth ||
            y >= sourceHeight ||
            x + width > sourceWidth ||
            y + height > sourceHeight
        ) {
            throw new Error(
                'De crop valt buiten de bronafbeelding.'
            );
        }

        const renderSize =
            getPreviewRenderSize(
                width,
                height
            );

        const canvas =
            createCanvas(
                renderSize.width,
                renderSize.height
            );

        const ctx =
            context2d(
                canvas
            );

        ctx.drawImage(
            sourceBitmap,
            x,
            y,
            width,
            height,
            0,
            0,
            canvas.width,
            canvas.height
        );

        return {
            canvas,
            targetWidth: width,
            targetHeight: height,
        };
    }

    function checkedValue(name, fallback = null) {
        const checked =
            form?.querySelector(
                'input[name="' +
                name +
                '"]:checked'
            );

        return checked
            ? checked.value
            : fallback;
    }

    function renderRotate() {
        const angle = integer(
            checkedValue(
                'angle',
                90
            ),
            90
        );

        const normalized =
            (
                (angle % 360) +
                360
            ) % 360;

        const swapsDimensions =
            normalized === 90 ||
            normalized === 270;

        const targetWidth =
            swapsDimensions
                ? sourceHeight
                : sourceWidth;

        const targetHeight =
            swapsDimensions
                ? sourceWidth
                : sourceHeight;

        const renderSize =
            getPreviewRenderSize(
                targetWidth,
                targetHeight
            );

        const canvas =
            createCanvas(
                renderSize.width,
                renderSize.height
            );

        const ctx =
            context2d(
                canvas
            );

        ctx.translate(
            canvas.width / 2,
            canvas.height / 2
        );

        ctx.rotate(
            angle *
            Math.PI /
            180
        );

        const drawWidth =
            swapsDimensions
                ? canvas.height
                : canvas.width;

        const drawHeight =
            swapsDimensions
                ? canvas.width
                : canvas.height;

        ctx.drawImage(
            sourceBitmap,
            -drawWidth / 2,
            -drawHeight / 2,
            drawWidth,
            drawHeight
        );

        return {
            canvas,
            targetWidth,
            targetHeight,
        };
    }

    function renderFlip() {
        const direction =
            checkedValue(
                'flip_direction',
                'horizontal'
            );

        const result =
            renderBaseCanvas(
                sourceWidth,
                sourceHeight
            );

        const canvas =
            createCanvas(
                result.canvas.width,
                result.canvas.height
            );

        const ctx =
            context2d(
                canvas
            );

        if (direction === 'vertical') {
            ctx.translate(
                0,
                canvas.height
            );

            ctx.scale(
                1,
                -1
            );
        } else {
            ctx.translate(
                canvas.width,
                0
            );

            ctx.scale(
                -1,
                1
            );
        }

        ctx.drawImage(
            result.canvas,
            0,
            0
        );

        return {
            canvas,
            targetWidth: sourceWidth,
            targetHeight: sourceHeight,
        };
    }

    function renderEnhance() {
        const result =
            renderBaseCanvas(
                sourceWidth,
                sourceHeight
            );

        const canvas =
            createCanvas(
                result.canvas.width,
                result.canvas.height
            );

        const ctx =
            context2d(
                canvas
            );

        const brightness =
            integer(
                enhanceBrightness?.value,
                0
            );

        const contrast =
            integer(
                enhanceContrast?.value,
                0
            );

        const blur =
            integer(
                enhanceBlur?.value,
                0
            );

        const filters = [
            'brightness(' +
                Math.max(
                    0,
                    100 + brightness
                ) +
                '%)',

            'contrast(' +
                Math.max(
                    0,
                    100 + contrast
                ) +
                '%)',
        ];

        if (enhanceGrayscale?.checked) {
            filters.push(
                'grayscale(100%)'
            );
        }

        if (enhanceSepia?.checked) {
            filters.push(
                'sepia(100%)'
            );
        }

        if (blur > 0) {
            filters.push(
                'blur(' +
                Math.min(
                    12,
                    blur * 0.75
                ) +
                'px)'
            );
        }

        ctx.filter =
            filters.join(' ');

        ctx.drawImage(
            result.canvas,
            0,
            0
        );

        ctx.filter = 'none';

        return {
            canvas,
            targetWidth:
                sourceWidth,
            targetHeight:
                sourceHeight,
        };
    }

    function renderCopy() {
        return renderBaseCanvas(
            sourceWidth,
            sourceHeight
        );
    }

    function renderForOperation(operation) {
        switch (operation) {
            case 'resize':
                return renderResize();

            case 'crop':
                return renderCrop();

            case 'rotate':
                return renderRotate();

            case 'flip':
                return renderFlip();

            case 'enhance':
                return renderEnhance();

            case 'compress':
            case 'convert':
                return renderCopy();

            default:
                return renderCopy();
        }
    }

    function prepareCanvasForFormat(
        canvas,
        format
    ) {
        if (format !== 'jpg') {
            return canvas;
        }

        const flattened =
            createCanvas(
                canvas.width,
                canvas.height
            );

        const ctx =
            context2d(
                flattened
            );

        ctx.fillStyle = '#ffffff';

        ctx.fillRect(
            0,
            0,
            flattened.width,
            flattened.height
        );

        ctx.drawImage(
            canvas,
            0,
            0
        );

        return flattened;
    }

    function canvasToBlob(
        canvas,
        mime,
        quality
    ) {
        return new Promise(function (
            resolve,
            reject
        ) {
            canvas.toBlob(
                function (blob) {
                    if (!blob) {
                        reject(
                            new Error(
                                'De live export kon niet worden gemaakt.'
                            )
                        );

                        return;
                    }

                    resolve(blob);
                },
                mime,
                quality
            );
        });
    }

    async function applyRenderedPreview(
        result,
        operation,
        sequence
    ) {
        const outputFormat =
            outputFormatForOperation(
                operation
            );

        const quality =
            qualityForOperation(
                operation
            );

        const exportCanvas =
            prepareCanvasForFormat(
                result.canvas,
                outputFormat
            );

        const mime =
            formatToMime(
                outputFormat
            );

        const blob =
            await canvasToBlob(
                exportCanvas,
                mime,
                quality / 100
            );

        if (
            sequence !==
            renderSequence
        ) {
            return;
        }

        releasePreviewObjectUrl();

        previewObjectUrl =
            URL.createObjectURL(
                blob
            );

        if (previewImage) {
            previewImage.src =
                previewObjectUrl;

            previewImage.alt =
                sourceName +
                ' live preview';
        }

        setPreviewDimensions(
            result.targetWidth,
            result.targetHeight
        );

        const suffix =
            operation === 'compress' ||
            operation === 'convert'
                ? ' · previewbestand ' +
                    formatBytes(
                        blob.size
                    )
                : '';

        if (previewMeta) {
            previewMeta.textContent =
                result.targetWidth +
                ' × ' +
                result.targetHeight +
                ' · ' +
                outputFormat.toUpperCase() +
                ' · LIVE' +
                suffix;
        }

        const operationLabel = matchFormat(
            operation,
            {
                resize: 'Resize',
                crop: 'Crop',
                rotate: 'Rotatie',
                flip: 'Spiegelen',
                enhance: 'Fotoverbetering',
                passport: 'Pasfoto / ID-foto',
                background: 'Achtergrond',
                compress: 'Compressie',
                convert: 'Conversie',
            },
            'Preview'
        );

        setStatus(
            operationLabel +
            ' live toegepast. Klik op de opslaanknop om deze versie definitief te bewaren.'
        );

        setLoading(
            false
        );
    }

    async function renderLivePreview() {
        if (!sourceBitmap) {
            return;
        }

        const operation =
            activeOperation();

        if (!operation) {
            hideVisualCrop();
            showSourceWithoutTransformation();
            return;
        }

        if (operation === 'crop') {
            showSourceWithoutTransformation();
            showVisualCrop();
            return;
        }

        if (operation === 'passport') {
            showSourceWithoutTransformation();
            configurePassportMode();
            showVisualCrop();
            return;
        }

        if (operation === 'background') {
            hideVisualCrop();
            showSourceWithoutTransformation();
            syncBackgroundControls();

            setStatus(
                backgroundModeStatus()
            );

            return;
        }

        hideVisualCrop();

        const sequence =
            ++renderSequence;

        setLoading(
            true
        );

        setStatus(
            'Live preview wordt bijgewerkt…'
        );

        try {
            const result =
                renderForOperation(
                    operation
                );

            await applyRenderedPreview(
                result,
                operation,
                sequence
            );
        } catch (error) {
            if (
                sequence !==
                renderSequence
            ) {
                return;
            }

            setLoading(
                false
            );

            setStatus(
                error instanceof Error
                    ? error.message
                    : 'De live preview kon niet worden gemaakt.',
                true
            );
        }
    }

    function scheduleLiveRender(immediate = false) {
        if (renderTimer) {
            window.clearTimeout(
                renderTimer
            );
        }

        renderTimer =
            window.setTimeout(
                renderLivePreview,
                immediate
                    ? 0
                    : 90
            );
    }

    function showSourceWithoutTransformation() {
        if (!previewImage) {
            return;
        }

        releasePreviewObjectUrl();

        previewImage.src =
            sourceUrl;

        previewImage.alt =
            sourceName;

        setPreviewDimensions(
            sourceWidth,
            sourceHeight
        );

        if (previewMeta) {
            previewMeta.textContent =
                sourceWidth +
                ' × ' +
                sourceHeight +
                ' · ' +
                sourceFormat;
        }

        setLoading(
            false
        );
    }

    async function loadSourceImage(url) {
        return new Promise(function (
            resolve,
            reject
        ) {
            const image =
                new Image();

            image.decoding =
                'async';

            image.onload =
                function () {
                    resolve(image);
                };

            image.onerror =
                function () {
                    reject(
                        new Error(
                            'De gekozen bronafbeelding kon niet worden geladen.'
                        )
                    );
                };

            image.src =
                url;
        });
    }

    function resetOperationFields() {
        clearBackgroundPreviewToken();

        if (resizeWidth) {
            resizeWidth.value =
                sourceWidth;
        }

        if (resizeHeight) {
            resizeHeight.value =
                sourceHeight;
        }

        cropRect = {
            x: 0,
            y: 0,
            width: sourceWidth,
            height: sourceHeight,
        };

        if (enhanceBrightness) {
            enhanceBrightness.value = '0';
        }

        if (enhanceBrightnessValue) {
            enhanceBrightnessValue.textContent = '0';
        }

        if (enhanceContrast) {
            enhanceContrast.value = '0';
        }

        if (enhanceContrastValue) {
            enhanceContrastValue.textContent = '0';
        }

        if (enhanceBlur) {
            enhanceBlur.value = '0';
        }

        if (enhanceBlurValue) {
            enhanceBlurValue.textContent = '0';
        }

        if (enhanceGrayscale) {
            enhanceGrayscale.checked = false;
        }

        if (enhanceSepia) {
            enhanceSepia.checked = false;
        }

        backgroundModeInputs.forEach(
            function (input) {
                input.checked =
                    input.value === 'transparent';
            }
        );

        if (backgroundColorPicker) {
            backgroundColorPicker.value =
                '#ffffff';
        }

        if (backgroundColor) {
            backgroundColor.value =
                '#ffffff';
        }

        if (backgroundUrl) {
            backgroundUrl.value =
                '';
        }

        if (backgroundImage) {
            backgroundImage.value =
                '';
        }

        if (backgroundFormat) {
            backgroundFormat.value =
                'png';
        }

        if (backgroundSize) {
            backgroundSize.value =
                'auto';
        }

        syncBackgroundControls();
        syncCropInputs();

        if (activeOperation() === 'crop') {
            initializeVisualCrop(true);
        }

        if (activeOperation() === 'passport') {
            configurePassportMode(true);
        }
    }

    async function applySelectedSource() {
        clearBackgroundPreviewToken();

        const option =
            selectedSourceOption();

        if (!option) {
            return;
        }

        const nextUrl =
            option.dataset.preview || '';

        if (!nextUrl) {
            setStatus(
                'De gekozen bron heeft geen previewbestand.',
                true
            );
            return;
        }

        renderSequence++;

        sourceUrl =
            nextUrl;

        sourceName =
            option.dataset.name ||
            'Afbeelding';

        sourceWidth =
            positiveNumber(
                option.dataset.width,
                1
            );

        sourceHeight =
            positiveNumber(
                option.dataset.height,
                1
            );

        sourceFormat =
            option.dataset.format ||
            'IMAGE';

        if (previewName) {
            previewName.textContent =
                sourceName;
        }

        if (previewDownload) {
            previewDownload.href =
                option.dataset.download ||
                '#';
        }

        resetOperationFields();

        setLoading(
            true
        );

        setStatus(
            'Bronafbeelding laden…'
        );

        try {
            sourceBitmap =
                await loadSourceImage(
                    sourceUrl
                );

            /*
             * Vertrouw uiteindelijk op de werkelijk geladen natuurlijke
             * dimensies als database-metadata ontbreekt of afwijkt.
             */
            sourceWidth =
                positiveNumber(
                    sourceBitmap.naturalWidth,
                    sourceWidth
                );

            sourceHeight =
                positiveNumber(
                    sourceBitmap.naturalHeight,
                    sourceHeight
                );

            resetOperationFields();

            if (activeOperation() === 'crop') {
                initializeVisualCrop(true);
                showSourceWithoutTransformation();
                showVisualCrop();
            } else if (activeOperation() === 'passport') {
                configurePassportMode(true);
                showSourceWithoutTransformation();
                showVisualCrop();
            } else if (activeOperation() === 'background') {
                hideVisualCrop();
                showSourceWithoutTransformation();
                syncBackgroundControls();

                setStatus(
                    backgroundModeStatus()
                );
            } else if (activeOperation()) {
                scheduleLiveRender(
                    true
                );
            } else {
                showSourceWithoutTransformation();
            }
        } catch (error) {
            sourceBitmap =
                null;

            showSourceWithoutTransformation();

            setStatus(
                error instanceof Error
                    ? error.message
                    : 'De bronafbeelding kon niet worden geladen.',
                true
            );
        }
    }

    resizeWidth?.addEventListener(
        'input',
        function () {
            syncHeightFromWidth();
            scheduleLiveRender();
        }
    );

    resizeHeight?.addEventListener(
        'input',
        function () {
            syncWidthFromHeight();
            scheduleLiveRender();
        }
    );

    resizeKeepAspect?.addEventListener(
        'change',
        function () {
            if (resizeKeepAspect.checked) {
                syncHeightFromWidth();
            }

            scheduleLiveRender(
                true
            );
        }
    );

    form
        ?.querySelectorAll(
            'input[name="angle"]'
        )
        .forEach(function (input) {
            input.addEventListener(
                'change',
                function () {
                    scheduleLiveRender(
                        true
                    );
                }
            );
        });

    form
        ?.querySelectorAll(
            'input[name="flip_direction"]'
        )
        .forEach(function (input) {
            input.addEventListener(
                'change',
                function () {
                    scheduleLiveRender(
                        true
                    );
                }
            );
        });

    [
        [
            enhanceBrightness,
            enhanceBrightnessValue,
        ],
        [
            enhanceContrast,
            enhanceContrastValue,
        ],
        [
            enhanceBlur,
            enhanceBlurValue,
        ],
    ].forEach(function (entry) {
        const input =
            entry[0];

        const output =
            entry[1];

        input?.addEventListener(
            'input',
            function () {
                if (output) {
                    output.textContent =
                        input.value;
                }

                scheduleLiveRender();
            }
        );
    });

    enhanceGrayscale?.addEventListener(
        'change',
        function () {
            scheduleLiveRender(
                true
            );
        }
    );

    enhanceSepia?.addEventListener(
        'change',
        function () {
            scheduleLiveRender(
                true
            );
        }
    );

    passportPreset?.addEventListener(
        'change',
        function () {
            configurePassportMode(
                true
            );

            showSourceWithoutTransformation();
            showVisualCrop();

            setStatus(
                'Pasfotoformaat gewijzigd. Positioneer de persoon opnieuw indien nodig.'
            );
        }
    );

    compressQuality?.addEventListener(
        'input',
        function () {
            if (compressQualityValue) {
                compressQualityValue.textContent =
                    compressQuality.value;
            }

            scheduleLiveRender();
        }
    );

    convertFormat?.addEventListener(
        'change',
        function () {
            scheduleLiveRender(
                true
            );
        }
    );

    convertQuality?.addEventListener(
        'input',
        function () {
            if (convertQualityValue) {
                convertQualityValue.textContent =
                    convertQuality.value;
            }

            scheduleLiveRender();
        }
    );

    backgroundLivePreviewButton?.addEventListener(
        'click',
        requestBackgroundLivePreview
    );

    backgroundModeInputs.forEach(
        function (input) {
            input.addEventListener(
                'change',
                function () {
                    invalidateBackgroundPreview();
                    syncBackgroundControls();

                    if (
                        activeOperation() ===
                        'background'
                    ) {
                        setStatus(
                            backgroundModeStatus()
                        );
                    }
                }
            );
        }
    );

    backgroundColorPicker?.addEventListener(
        'input',
        function () {
            invalidateBackgroundPreview();

            if (backgroundColor) {
                backgroundColor.value =
                    backgroundColorPicker.value;
            }

            if (
                activeOperation() ===
                'background'
            ) {
                setStatus(
                    backgroundModeStatus()
                );
            }
        }
    );

    backgroundColor?.addEventListener(
        'input',
        function () {
            invalidateBackgroundPreview();

            const value =
                backgroundColor.value.trim();

            if (
                backgroundColorPicker &&
                /^#[0-9a-fA-F]{6}$/.test(
                    value
                )
            ) {
                backgroundColorPicker.value =
                    value;
            }
        }
    );

    backgroundFormat?.addEventListener(
        'change',
        function () {
            invalidateBackgroundPreview();
            syncBackgroundControls();
        }
    );
    backgroundSize?.addEventListener(
        'change',
        function () {
            invalidateBackgroundPreview();
        }
    );


    backgroundUrl?.addEventListener(
        'input',
        function () {
            invalidateBackgroundPreview();

            if (
                activeOperation() ===
                'background'
            ) {
                setStatus(
                    backgroundModeStatus()
                );
            }
        }
    );

    backgroundImage?.addEventListener(
        'change',
        function () {
            invalidateBackgroundPreview();

            if (
                activeOperation() !==
                'background'
            ) {
                return;
            }

            const file =
                backgroundImage.files?.[0];

            setStatus(
                file
                    ? 'Nieuwe achtergrond geselecteerd: ' + file.name
                    : backgroundModeStatus()
            );
        }
    );

    sourceSelect?.addEventListener(
        'change',
        applySelectedSource
    );

    fitButton?.addEventListener(
        'click',
        function () {
            zoomMode =
                'fit';

            applyPreviewGeometry();
        }
    );

    actualButton?.addEventListener(
        'click',
        function () {
            zoomMode =
                '100';

            applyPreviewGeometry();
        }
    );

    resetButton?.addEventListener(
        'click',
        function () {
            resetOperationFields();

            const operation =
                activeOperation();

            if (operation === 'crop') {
                initializeVisualCrop(true);
                showVisualCrop();

                setStatus(
                    'Cropgebied is opnieuw ingesteld. Sleep het kader op de afbeelding.'
                );

                return;
            }

            if (operation === 'passport') {
                configurePassportMode(true);
                showSourceWithoutTransformation();
                showVisualCrop();

                setStatus(
                    'Pasfoto-kader is opnieuw gecentreerd.'
                );

                return;
            }

            if (operation === 'background') {
                hideVisualCrop();
                showSourceWithoutTransformation();
                syncBackgroundControls();

                setStatus(
                    backgroundModeStatus()
                );

                return;
            }

            if (operation) {
                scheduleLiveRender(
                    true
                );
            } else {
                showSourceWithoutTransformation();
            }

            setStatus(
                'Preview-instellingen zijn teruggezet naar de bron.'
            );
        }
    );

    previewImage?.addEventListener(
        'load',
        function () {
            setLoading(
                false
            );

            applyPreviewGeometry();
        }
    );

    window.addEventListener(
        'resize',
        function () {
            applyPreviewGeometry();
        }
    );

    document
        .querySelectorAll(
            '[data-use-version]'
        )
        .forEach(function (button) {
            button.addEventListener(
                'click',
                async function () {
                    if (!sourceSelect) {
                        return;
                    }

                    sourceSelect.value =
                        button.dataset.useVersion ||
                        '';

                    await applySelectedSource();

                    activateOperation(
                        'resize'
                    );

                    if (isMobileEditor()) {
                        openMobileProperties();
                    }
                }
            );
        });

    document
        .querySelectorAll(
            '[data-delete-version-form]'
        )
        .forEach(function (button) {
            button.addEventListener(
                'click',
                function () {
                    const formId =
                        button.dataset.deleteVersionForm;

                    const deleteForm =
                        document.getElementById(
                            formId
                        );

                    if (!deleteForm) {
                        return;
                    }

                    const confirmed =
                        window.confirm(
                            'Deze versie verwijderen? Het origineel blijft behouden.'
                        );

                    if (!confirmed) {
                        return;
                    }

                    button.disabled =
                        true;

                    HTMLFormElement
                        .prototype
                        .submit
                        .call(
                            deleteForm
                        );
                }
            );
        });

    form?.addEventListener(
        'submit',
        async function (event) {
            event.preventDefault();

            if (savingOperation) {
                return;
            }

            const operation =
                activeOperation();

            if (!operation) {
                setStatus(
                    'Kies eerst een bewerking.',
                    true
                );

                return;
            }

            if (
                operation === 'crop' ||
                operation === 'passport'
            ) {
                cropRect =
                    normalizeCropRect(
                        cropRect
                    );

                syncCropInputs();

                if (
                    cropRect.width < 1 ||
                    cropRect.height < 1
                ) {
                    setStatus(
                        'Kies eerst een geldig cropgebied op de afbeelding.',
                        true
                    );

                    return;
                }
            }

            if (operation === 'background') {
                const validationMessage =
                    validateBackgroundSelection();

                if (validationMessage) {
                    setStatus(
                        validationMessage,
                        true
                    );

                    return;
                }
            }

            let prepared;

            try {
                prepared =
                    prepareActiveFormData(
                        operation
                    );
            } catch (error) {
                setStatus(
                    error instanceof Error
                        ? error.message
                        : 'De bewerking kon niet worden voorbereid.',
                    true
                );

                return;
            }

            const submitButton =
                prepared.activePanel.querySelector(
                    '.editor-submit'
                );

            const originalLabel =
                submitButton?.textContent ||
                'Opslaan als versie';

            savingOperation =
                true;

            if (submitButton) {
                submitButton.disabled =
                    true;

                submitButton.textContent =
                    'Versie opslaan…';
            }

            setLoading(
                true
            );

            setStatus(
                'De live preview wordt nu definitief als nieuwe versie opgeslagen…'
            );

            try {
                const response =
                    await fetch(
                        form.action,
                        {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Accept':
                                    'application/json',
                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },
                            body:
                                prepared.data,
                        }
                    );

                const payload =
                    await readJsonResponse(
                        response
                    );

                if (
                    !response.ok ||
                    !payload ||
                    payload.ok !== true
                ) {
                    throw new Error(
                        responseErrorMessage(
                            payload,
                            response.status === 419
                                ? 'Je sessie is verlopen. Vernieuw de pagina en probeer opnieuw.'
                                : 'De versie kon niet worden opgeslagen.'
                        )
                    );
                }

                if (!payload.version) {
                    throw new Error(
                        'De server heeft de versie opgeslagen maar geen versiegegevens teruggestuurd.'
                    );
                }

                clearBackgroundPreviewToken();

                await useSavedVersion(
                    payload.version
                );

                setStatus(
                    (
                        payload.message ||
                        'De nieuwe versie is opgeslagen.'
                    ) +
                    ' De nieuwe versie is direct actief; je hoeft niet handmatig te wisselen.'
                );
            } catch (error) {
                setStatus(
                    error instanceof Error
                        ? error.message
                        : 'De versie kon niet worden opgeslagen.',
                    true
                );
            } finally {
                savingOperation =
                    false;

                setLoading(
                    false
                );

                if (submitButton) {
                    submitButton.disabled =
                        false;

                    submitButton.textContent =
                        originalLabel;
                }
            }
        }
    );

    window.addEventListener(
        'pageshow',
        function () {
            savingOperation = false;
            requestingBackgroundPreview = false;

            const operation =
                activeOperation();

            if (operation) {
                activateOperation(
                    operation
                );
            }
        }
    );

    document.addEventListener(
        'keydown',
        function (event) {
            if (
                event.key === 'Escape' &&
                isMobileEditor() &&
                document.body.classList.contains(
                    'mobile-editor-properties-open'
                )
            ) {
                closeMobileProperties();
            }
        }
    );

    window.addEventListener(
        'beforeunload',
        function () {
            releasePreviewObjectUrl();
        }
    );

    /*
     * Start altijd met Resize als actieve tool en laad daarna de gekozen bron.
     */
    updatePassportPresetInfo();
    syncBackgroundControls();

    activateOperation(
        'resize'
    );

    applySelectedSource();
});
</script>
@endpush
