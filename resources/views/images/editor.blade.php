@extends('layouts.site-layout')

@section('title', 'Mashal Studio | Image Editor')

@section(
    'meta_description',
    'Bewerk je afbeelding met resize, crop, rotate, flip, compress en convert. Iedere bewerking wordt als aparte versie opgeslagen.'
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
        padding: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 100%;
        min-height: 100%;
    }

    .editor-preview-image {
        display: block;
        max-width: min(100%, 980px);
        max-height: 650px;
        width: auto;
        height: auto;
        object-fit: contain;
        border-radius: 4px;
        box-shadow: 0 30px 90px rgba(0,0,0,.45);
        background: transparent;
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
                        <img
                            id="editor-preview-image"
                            class="editor-preview-image"
                            src="{{ route('images.file', $image) }}"
                            alt="{{ $image->display_name ?? $image->original_name }}"
                        >
                    </div>
                </div>
            </main>

            <aside class="editor-sidebar right">
                <div class="editor-sidebar-label">Properties</div>

                <form
                    id="editor-operation-form"
                    method="POST"
                    action="{{ route('images.process', $image) }}"
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
                                    >
                                        #{{ $version->id }}
                                        · {{ $version->operation_label ?? ucfirst((string) $version->operation) }}
                                        · {{ $version->width ?? '?' }} × {{ $version->height ?? '?' }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <div class="editor-source-note">
                            Kies het origineel of een opgeslagen versie. De gekozen bron wordt direct in het midden geladen.
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
                                    type="checkbox"
                                    name="keep_aspect"
                                    value="1"
                                    @checked(old('keep_aspect', true))
                                >
                                <span>Beeldverhouding behouden</span>
                            </label>

                            <button class="editor-submit" type="submit">
                                Resize-versie maken
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
                                Vul de positie en grootte van het uitsnijgebied in pixels in.
                            </p>

                            <div class="editor-field-grid">
                                <label class="editor-field">
                                    <span>X</span>
                                    <input type="number" name="crop_x" min="0" value="{{ old('crop_x', 0) }}">
                                </label>

                                <label class="editor-field">
                                    <span>Y</span>
                                    <input type="number" name="crop_y" min="0" value="{{ old('crop_y', 0) }}">
                                </label>

                                <label class="editor-field">
                                    <span>Breedte</span>
                                    <input
                                        id="crop-width"
                                        type="number"
                                        name="crop_width"
                                        min="1"
                                        max="12000"
                                        value="{{ old('crop_width', $image->width) }}"
                                    >
                                </label>

                                <label class="editor-field">
                                    <span>Hoogte</span>
                                    <input
                                        id="crop-height"
                                        type="number"
                                        name="crop_height"
                                        min="1"
                                        max="12000"
                                        value="{{ old('crop_height', $image->height) }}"
                                    >
                                </label>
                            </div>

                            <button class="editor-submit" type="submit">
                                Crop uitvoeren
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
                                Roteren
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
                                Spiegelen
                            </button>
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

                            <button class="editor-submit" type="submit">
                                Comprimeren
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

                                <select name="format">
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

                            <button class="editor-submit" type="submit">
                                Converteren
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

    const stage =
        document.getElementById('editor-stage');

    const stageInner =
        stage
            ? stage.querySelector('.editor-stage-inner')
            : null;

    const cropWidth =
        document.getElementById('crop-width');

    const cropHeight =
        document.getElementById('crop-height');

    const fitButton =
        document.getElementById('preview-fit');

    const actualButton =
        document.getElementById('preview-100');

    const compressQuality =
        document.getElementById('compress-quality');

    const compressQualityValue =
        document.getElementById('compress-quality-value');

    const convertQuality =
        document.getElementById('convert-quality');

    const convertQualityValue =
        document.getElementById('convert-quality-value');

    let zoomMode = 'fit';

    function setPanelControlsEnabled(panel, enabled) {
        panel
            .querySelectorAll(
                'input, select, textarea, button[type="submit"]'
            )
            .forEach(function (control) {
                control.disabled = !enabled;
            });
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
    }

    tools.forEach(function (tool) {
        tool.addEventListener(
            'click',
            function () {
                activateOperation(
                    tool.dataset.operation
                );
            }
        );
    });

    function selectedSourceOption() {
        if (!sourceSelect) {
            return null;
        }

        return sourceSelect.options[
            sourceSelect.selectedIndex
        ] || null;
    }

    function applySelectedSource() {
        const option =
            selectedSourceOption();

        if (!option) {
            return;
        }

        const preview =
            option.dataset.preview || '';

        const download =
            option.dataset.download || '#';

        const name =
            option.dataset.name || 'Afbeelding';

        const width =
            option.dataset.width || '?';

        const height =
            option.dataset.height || '?';

        const format =
            option.dataset.format || 'IMAGE';

        if (previewImage && preview) {
            stage?.classList.add('loading');

            previewImage.src = preview;
            previewImage.alt = name;
        }

        if (previewName) {
            previewName.textContent =
                name;
        }

        if (previewMeta) {
            previewMeta.textContent =
                width + ' × ' +
                height + ' · ' +
                format;
        }

        if (previewDownload) {
            previewDownload.href =
                download;
        }

        if (
            cropWidth &&
            width !== '?'
        ) {
            cropWidth.value =
                width;
        }

        if (
            cropHeight &&
            height !== '?'
        ) {
            cropHeight.value =
                height;
        }

        setZoom('fit');
    }

    function fitScale() {
        if (
            !stage ||
            !previewImage ||
            !previewImage.naturalWidth ||
            !previewImage.naturalHeight
        ) {
            return 1;
        }

        const availableWidth =
            Math.max(
                1,
                stage.clientWidth - 64
            );

        const availableHeight =
            Math.max(
                1,
                stage.clientHeight - 64
            );

        return Math.min(
            1,
            availableWidth /
                previewImage.naturalWidth,
            availableHeight /
                previewImage.naturalHeight
        );
    }

    function setZoom(mode) {
        zoomMode = mode;

        if (!stageInner) {
            return;
        }

        const scale =
            mode === '100'
                ? 1
                : fitScale();

        stageInner.style.transform =
            'scale(' + scale + ')';
    }

    fitButton?.addEventListener(
        'click',
        function () {
            setZoom('fit');
        }
    );

    actualButton?.addEventListener(
        'click',
        function () {
            setZoom('100');
        }
    );

    previewImage?.addEventListener(
        'load',
        function () {
            stage?.classList.remove(
                'loading'
            );

            setZoom(
                zoomMode
            );
        }
    );

    previewImage?.addEventListener(
        'error',
        function () {
            stage?.classList.remove(
                'loading'
            );
        }
    );

    window.addEventListener(
        'resize',
        function () {
            if (
                zoomMode === 'fit'
            ) {
                setZoom('fit');
            }
        }
    );

    sourceSelect?.addEventListener(
        'change',
        applySelectedSource
    );

    document
        .querySelectorAll('[data-use-version]')
        .forEach(function (button) {
            button.addEventListener(
                'click',
                function () {
                    if (!sourceSelect) {
                        return;
                    }

                    sourceSelect.value =
                        button.dataset.useVersion || '';

                    applySelectedSource();

                    activateOperation(
                        'resize'
                    );
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
                        .call(deleteForm);
                }
            );
        });

    compressQuality?.addEventListener(
        'input',
        function () {
            if (compressQualityValue) {
                compressQualityValue.textContent =
                    compressQuality.value;
            }
        }
    );

    convertQuality?.addEventListener(
        'input',
        function () {
            if (convertQualityValue) {
                convertQualityValue.textContent =
                    convertQuality.value;
            }
        }
    );

    form?.addEventListener(
        'submit',
        function (event) {
            const operation =
                operationInput
                    ? operationInput.value
                    : '';

            if (!operation) {
                event.preventDefault();
                return;
            }

            const activePanel =
                panels.find(function (panel) {
                    return (
                        panel.dataset.panel ===
                        operation
                    );
                });

            if (!activePanel) {
                event.preventDefault();
                return;
            }

            panels.forEach(function (panel) {
                setPanelControlsEnabled(
                    panel,
                    panel === activePanel
                );
            });

            form
                .querySelectorAll(
                    '.editor-submit'
                )
                .forEach(function (button) {
                    button.disabled =
                        true;
                });

            const submitButton =
                activePanel.querySelector(
                    '.editor-submit'
                );

            if (submitButton) {
                submitButton.textContent =
                    'Bezig…';
            }
        }
    );

    window.addEventListener(
        'pageshow',
        function () {
            form
                ?.querySelectorAll(
                    '.editor-submit'
                )
                .forEach(function (button) {
                    button.disabled =
                        false;
                });

            const activeTool =
                tools.find(function (tool) {
                    return tool.classList
                        .contains('active');
                });

            activateOperation(
                activeTool
                    ? activeTool.dataset.operation
                    : 'resize'
            );
        }
    );

    activateOperation('resize');
    applySelectedSource();
});
</script>
@endpush
