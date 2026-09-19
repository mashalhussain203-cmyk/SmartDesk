@extends('layouts.site-layout')

@section('title', 'Mashal Studio | Image Editor')

@push('styles')
<style>
    .image-editor-page {
        min-height: calc(100vh - 78px);
        padding: 42px 0 70px;
        background:
            radial-gradient(circle at 15% 12%, rgba(229, 182, 111, .08), transparent 28rem),
            radial-gradient(circle at 88% 18%, rgba(102, 92, 255, .07), transparent 30rem),
            #07080b;
        color: #f7f7f4;
    }

    .image-editor-shell {
        width: min(100% - 32px, 1380px);
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
        width: 38px;
        height: 38px;
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
    }

    .editor-link.primary {
        border-color: rgba(229, 182, 111, .18);
        color: #161009;
        background: linear-gradient(135deg, #f1d08e, #d49b50);
    }

    .editor-workspace {
        min-height: 720px;
        display: grid;
        grid-template-columns: 230px minmax(0, 1fr) 280px;
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
        padding: 40px;
        display: grid;
        place-items: center;
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

    .editor-canvas-card {
        width: min(100%, 760px);
        min-height: 460px;
        padding: 34px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        border: 1px solid rgba(255, 255, 255, .10);
        border-radius: 24px;
        background:
            radial-gradient(circle at 76% 18%, rgba(229, 182, 111, .12), transparent 18rem),
            linear-gradient(145deg, rgba(255, 255, 255, .045), rgba(255, 255, 255, .01)),
            #101319;
        box-shadow: 0 35px 90px rgba(0, 0, 0, .34);
    }

    .canvas-kicker {
        color: #d3a45f;
        font-size: 9px;
        font-weight: 950;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .editor-canvas-card h1 {
        margin: 13px 0 0;
        color: #f2f3f4;
        font-size: clamp(34px, 4vw, 56px);
        line-height: 1;
        letter-spacing: -.055em;
        word-break: break-word;
    }

    .editor-canvas-card > p {
        max-width: 610px;
        margin: 18px 0 0;
        color: #858b95;
        font-size: 12px;
        line-height: 1.8;
    }

    .image-meta-grid {
        margin-top: 28px;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 10px;
    }

    .image-meta-card {
        min-width: 0;
        padding: 15px;
        border: 1px solid rgba(255,255,255,.065);
        border-radius: 14px;
        background: rgba(255,255,255,.018);
    }

    .image-meta-card small {
        display: block;
        color: #585f69;
        font-size: 7px;
        font-weight: 900;
        letter-spacing: .10em;
        text-transform: uppercase;
    }

    .image-meta-card strong {
        display: block;
        margin-top: 5px;
        overflow: hidden;
        color: #cdd1d6;
        font-size: 11px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .editor-status {
        margin-top: 24px;
        padding: 14px 16px;
        display: flex;
        align-items: flex-start;
        gap: 11px;
        border: 1px solid rgba(100, 217, 139, .13);
        border-radius: 14px;
        color: #a9d8b7;
        background: rgba(100, 217, 139, .045);
        font-size: 10px;
        line-height: 1.65;
    }

    .editor-status-dot {
        flex: 0 0 auto;
        width: 7px;
        height: 7px;
        margin-top: 5px;
        border-radius: 50%;
        background: #6bdc91;
        box-shadow: 0 0 0 5px rgba(107, 220, 145, .07);
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

    .fake-field-grid {
        margin-top: 12px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 7px;
    }

    .fake-field {
        padding: 10px;
        border: 1px solid rgba(255,255,255,.06);
        border-radius: 10px;
        background: rgba(255,255,255,.015);
    }

    .fake-field small {
        display: block;
        color: #515862;
        font-size: 7px;
        font-weight: 900;
        text-transform: uppercase;
    }

    .fake-field strong {
        display: block;
        margin-top: 4px;
        color: #bfc4cb;
        font-size: 10px;
    }

    .property-note {
        margin-top: 12px;
        color: #5f6670;
        font-size: 8px;
        line-height: 1.6;
    }

    @media (max-width: 1050px) {
        .editor-workspace {
            grid-template-columns: 200px minmax(0, 1fr);
        }

        .editor-sidebar.right {
            display: none;
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
            display: none;
        }

        .editor-canvas-area {
            padding: 18px;
        }

        .editor-canvas-card {
            min-height: 420px;
            padding: 24px 18px;
        }

        .image-meta-grid {
            grid-template-columns: 1fr 1fr;
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
                    <strong>{{ $image->original_name }}</strong>
                    <span>Mashal Image Studio · Project #{{ $image->id }}</span>
                </div>
            </div>

            <div class="editor-top-actions">
                <a class="editor-link" href="{{ route('home') }}">
                    Nieuwe upload
                </a>

                <a class="editor-link" href="{{ route('account') }}">
                    Mijn account
                </a>

                <a class="editor-link primary" href="{{ route('home') }}#upload">
                    Andere afbeelding
                </a>
            </div>
        </div>

        <div class="editor-workspace">
            <aside class="editor-sidebar">
                <div class="editor-sidebar-label">Editor tools</div>

                <div class="editor-tool-list">
                    <div class="editor-tool active">
                        <span class="tool-icon">↔</span>
                        Resize
                    </div>

                    <div class="editor-tool">
                        <span class="tool-icon">⌗</span>
                        Crop
                    </div>

                    <div class="editor-tool">
                        <span class="tool-icon">↻</span>
                        Rotate
                    </div>

                    <div class="editor-tool">
                        <span class="tool-icon">↔</span>
                        Flip
                    </div>

                    <div class="editor-tool">
                        <span class="tool-icon">↓</span>
                        Compress
                    </div>

                    <div class="editor-tool">
                        <span class="tool-icon">◇</span>
                        Convert
                    </div>

                    <div class="editor-tool">
                        <span class="tool-icon">▦</span>
                        Versions
                    </div>
                </div>
            </aside>

            <main class="editor-canvas-area">
                <section class="editor-canvas-card">
                    <span class="canvas-kicker">Upload gekoppeld aan jouw account</span>

                    <h1>{{ $image->original_name }}</h1>

                    <p>
                        De afbeelding is succesvol opgeslagen als jouw project.
                        Deze pagina is nu de basis van de echte editor. De volgende technische stap
                        is het veilig tonen van het private originele bestand en daarna echte
                        resize-, crop-, rotate-, compress- en convert-acties toevoegen.
                    </p>

                    <div class="image-meta-grid">
                        <div class="image-meta-card">
                            <small>Breedte</small>
                            <strong>{{ $image->width ? number_format($image->width) . ' px' : 'Onbekend' }}</strong>
                        </div>

                        <div class="image-meta-card">
                            <small>Hoogte</small>
                            <strong>{{ $image->height ? number_format($image->height) . ' px' : 'Onbekend' }}</strong>
                        </div>

                        <div class="image-meta-card">
                            <small>Bestand</small>
                            <strong>{{ number_format(($image->file_size ?? 0) / 1024, 1) }} KB</strong>
                        </div>

                        <div class="image-meta-card">
                            <small>Type</small>
                            <strong>{{ $image->mime_type ?? 'Onbekend' }}</strong>
                        </div>
                    </div>

                    <div class="editor-status">
                        <span class="editor-status-dot"></span>
                        <span>
                            Project #{{ $image->id }} hoort bij gebruiker #{{ $image->user_id }}.
                            Je bent veilig door de upload → login → claim → editor-flow gekomen.
                        </span>
                    </div>
                </section>
            </main>

            <aside class="editor-sidebar right">
                <div class="editor-sidebar-label">Properties</div>

                <div class="property-card">
                    <h3>Original dimensions</h3>

                    <div class="fake-field-grid">
                        <div class="fake-field">
                            <small>Width</small>
                            <strong>{{ $image->width ?? '—' }}</strong>
                        </div>

                        <div class="fake-field">
                            <small>Height</small>
                            <strong>{{ $image->height ?? '—' }}</strong>
                        </div>
                    </div>

                    <div class="property-note">
                        De resize-controls worden in de volgende stap functioneel gemaakt.
                    </div>
                </div>

                <div class="property-card">
                    <h3>Storage</h3>
                    <p>
                        Dit project gebruikt private Laravel local storage voor het originele bestand.
                    </p>
                </div>

                <div class="property-card">
                    <h3>Output versions</h3>
                    <p>
                        Nieuwe versies gaan later naar de image_versions-tabel die we al hebben gemaakt.
                    </p>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection
