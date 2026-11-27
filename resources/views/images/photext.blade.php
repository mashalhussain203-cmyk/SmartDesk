@extends('layouts.site-layout')

@section('title', 'PhoText AI Text Editor | Mashal Studio')
@section('meta_description', 'Bewerk tekst in afbeeldingen met PhoText, rechtstreeks vanuit Mashal Studio.')

@push('styles')
<style>
    .photext-page{
        position:relative;isolation:isolate;min-height:calc(100vh - 78px);overflow:hidden;
        padding:58px 0 88px;color:var(--studio-text,#f7f7f4);
        background:
            radial-gradient(circle at 12% 4%,rgba(143,130,255,.12),transparent 30rem),
            radial-gradient(circle at 88% 10%,rgba(115,215,255,.055),transparent 34rem),
            linear-gradient(180deg,#07080b 0%,#090b0f 54%,#07080b 100%);
    }
    .photext-page::before{
        content:"";position:absolute;inset:0;z-index:-1;pointer-events:none;opacity:.12;
        background-image:linear-gradient(rgba(255,255,255,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.025) 1px,transparent 1px);
        background-size:72px 72px;mask-image:linear-gradient(to bottom,#000,transparent 78%);
    }
    .photext-shell{position:relative;z-index:1;width:min(100% - 38px,1360px);margin-inline:auto}
    .photext-head{display:flex;align-items:flex-end;justify-content:space-between;gap:28px;margin-bottom:26px}
    .photext-head-copy{min-width:0}
    .photext-kicker{display:inline-flex;align-items:center;gap:9px;color:var(--studio-gold-light,#a99fff);font-size:9px;font-weight:950;letter-spacing:.19em;text-transform:uppercase}
    .photext-kicker::before{content:"";width:7px;height:7px;border-radius:50%;background:var(--studio-gold,#8f82ff);box-shadow:0 0 16px rgba(143,130,255,.75)}
    .photext-head h1{margin:12px 0 9px;color:#f7f7f4;font-size:clamp(34px,5vw,58px);font-weight:850;line-height:1.02;letter-spacing:-.06em}
    .photext-head h1 span{color:var(--studio-gold-light,#a99fff)!important}
    .photext-head p{max-width:710px;margin:0;color:var(--studio-muted,#8d949e);font-size:13px;line-height:1.8}
    .photext-actions{display:flex;align-items:center;gap:9px;flex-wrap:wrap}
    .photext-btn{min-height:43px;padding:0 16px;display:inline-flex;align-items:center;justify-content:center;gap:8px;border:1px solid var(--studio-line-strong,rgba(255,255,255,.13));border-radius:12px;background:rgba(18,22,28,.88);color:var(--studio-text-soft,#d8dbe0);text-decoration:none;font:800 11px/1 var(--studio-font,Inter,system-ui,sans-serif);letter-spacing:.015em;cursor:pointer;transition:transform .18s ease,border-color .18s ease,background .18s ease,box-shadow .18s ease}
    .photext-btn:hover:not(:disabled){transform:translateY(-1px);border-color:rgba(169,159,255,.55);background:#171c24;box-shadow:0 9px 28px rgba(0,0,0,.2)}
    .photext-btn:focus-visible,.photext-toolbar select:focus-visible,.photext-toolbar input:focus-visible{outline:2px solid var(--studio-gold-light,#a99fff);outline-offset:3px}
    .photext-btn.primary{border-color:rgba(143,130,255,.75);background:linear-gradient(135deg,#8f82ff,#5f7dff);color:#fff;box-shadow:0 8px 25px rgba(95,125,255,.17)}
    .photext-btn:disabled{opacity:.42;cursor:not-allowed;transform:none;box-shadow:none}
    .photext-toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:14px;margin-bottom:13px;border:1px solid var(--studio-line,rgba(255,255,255,.075));border-radius:17px;background:linear-gradient(135deg,rgba(18,22,28,.94),rgba(12,15,20,.88));box-shadow:var(--studio-shadow-soft,0 20px 55px rgba(0,0,0,.25));backdrop-filter:blur(18px)}
    .photext-toolbar select{max-width:100%;padding:0 2px;background:transparent;color:inherit;border:0;font:inherit;cursor:pointer}
    .photext-toolbar select option{background:#12161c;color:#f7f7f4}
    .photext-toolbar input[type=range]{width:78px;accent-color:var(--studio-gold,#8f82ff);vertical-align:middle}
    .photext-toolbar input[type=file]{max-width:100%;color:var(--studio-muted,#8d949e);font:11px var(--studio-font,Inter,system-ui,sans-serif)}
    .photext-status{flex:1 1 230px;min-width:180px;padding:0 5px;color:var(--studio-muted,#8d949e);font-size:11px;line-height:1.6}
    .photext-frame-top{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 16px;border:1px solid var(--studio-line,rgba(255,255,255,.075));border-bottom:0;border-radius:19px 19px 0 0;background:linear-gradient(135deg,#12161c,#0e1116)}
    .photext-frame-brand{display:flex;align-items:center;gap:10px;min-width:0}
    .photext-brand-mark{width:29px;height:29px;display:grid;place-items:center;border:1px solid rgba(143,130,255,.38);border-radius:9px;background:rgba(143,130,255,.12);color:#b4acff;font-size:12px;font-weight:950}
    .photext-frame-brand strong{display:block;color:#f7f7f4;font-size:11px;letter-spacing:.02em}
    .photext-frame-brand small{display:block;color:var(--studio-muted,#8d949e);font-size:9px}
    .photext-frame-label{padding:5px 9px;border:1px solid var(--studio-line,rgba(255,255,255,.075));border-radius:999px;color:var(--studio-muted,#8d949e);font-size:9px;font-weight:800;letter-spacing:.1em;text-transform:uppercase}
    .photext-frame-wrap{overflow:hidden;border:1px solid var(--studio-line-strong,rgba(255,255,255,.13));border-radius:0 0 19px 19px;background:#0e1116;box-shadow:var(--studio-shadow,0 38px 110px rgba(0,0,0,.42))}
    .photext-frame{display:block;width:100%;height:min(78vh,980px);min-height:620px;border:0;background:#fff}
    .photext-notice{display:flex;align-items:flex-start;gap:9px;margin:14px 3px 0;color:var(--studio-muted-2,#656d77);font-size:10px;line-height:1.7}
    .photext-notice::before{content:"i";flex:0 0 17px;width:17px;height:17px;display:grid;place-items:center;border:1px solid var(--studio-line-strong);border-radius:50%;color:var(--studio-muted);font-size:10px;font-weight:900}
    @media(max-width:760px){
        .photext-page{padding:34px 0 60px}.photext-shell{width:min(100% - 26px,1360px)}
        .photext-head{display:block;margin-bottom:20px}.photext-actions{margin-top:17px}
        .photext-toolbar{gap:8px;padding:11px}.photext-toolbar .photext-btn{flex:1 1 auto}
        .photext-status{flex-basis:100%;padding:5px 2px 0}
        .photext-frame-top{padding:10px 12px}.photext-frame{height:76vh;min-height:560px}
    }
    @media(max-width:420px){.photext-btn{min-height:41px;padding-inline:11px;font-size:10px}.photext-frame-label{font-size:8px}}
</style>
@endpush

@section('content')
<main class="photext-page">
    <div class="photext-shell">
        <header class="photext-head">
            <div>
                <div class="photext-kicker">Mashal Studio · AI image tools</div>
                <h1>PhoText <span style="color:#55d99b">AI Text Editor</span></h1>
                <p>Upload een afbeelding en bewerk de tekst rechtstreeks op de afbeelding. Gebruik de PhoText-editor hieronder en haal daarna je bewerkte afbeelding op.</p>
            </div>
            <div class="photext-actions">
                <a class="photext-btn" href="{{ route('images.index') }}">← Mijn afbeeldingen</a>
                <a class="photext-btn" href="https://photext.ai" target="_blank" rel="noopener noreferrer">PhoText openen ↗</a>
            </div>
        </header>

        <section class="photext-toolbar" aria-label="PhoText besturing">
            <label class="photext-btn" for="photext-file">Afbeelding uploaden</label>
            <input id="photext-file" type="file" accept="image/jpeg,image/png,image/webp,image/bmp" hidden>
            <button class="photext-btn" id="photext-reopen" type="button" disabled>Afbeelding opnieuw openen</button>
            <label class="photext-btn" for="photext-format">Formaat <select id="photext-format" style="margin-left:8px;background:#111a15;color:#eaf5ee;border:0"><option value="png">PNG</option><option value="jpeg">JPG</option><option value="webp">WEBP</option></select></label>
            <label class="photext-btn" for="photext-quality">Kwaliteit <input id="photext-quality" type="range" min="50" max="100" value="92" style="width:82px;vertical-align:middle;margin-left:6px"></label>
            <button class="photext-btn primary" id="photext-export" type="button" disabled>Bewerkte afbeelding opslaan</button>
            <span class="photext-status" id="photext-status" role="status" aria-live="polite">Editor wordt geladen. Upload daarna een JPG, PNG, WEBP of BMP.</span>
        </section>

        <div class="photext-frame-top">
            <div class="photext-frame-brand"><span class="photext-brand-mark">M</span><span><strong>Mashal Studio Editor</strong><small>AI-powered image text editing</small></span></div>
            <span class="photext-frame-label">Secure workspace</span>
        </div>
        <div class="photext-frame-wrap">
            <iframe
                id="photext-frame"
                class="photext-frame"
                src="https://photext.ai/en/editor?responsive=true&hide_header=true"
                title="PhoText AI image text editor"
                allow="clipboard-read; clipboard-write"
                referrerpolicy="strict-origin-when-cross-origin"
            ></iframe>
        </div>
        <p class="photext-notice">Privacy: wanneer je een afbeelding uploadt, wordt deze rechtstreeks naar PhoText verzonden voor verwerking volgens hun voorwaarden en privacybeleid. Upload alleen afbeeldingen die je mag bewerken. De export wordt op je apparaat opgeslagen; deze pagina bewaart geen kopie in je Mashal Studio-bibliotheek.</p>
    </div>
</main>

<script>
(() => {
    'use strict';
    const origin = 'https://photext.ai';
    const frame = document.getElementById('photext-frame');
    const fileInput = document.getElementById('photext-file');
    const exportButton = document.getElementById('photext-export');
    const reopenButton = document.getElementById('photext-reopen');
    const formatInput = document.getElementById('photext-format');
    const qualityInput = document.getElementById('photext-quality');
    const status = document.getElementById('photext-status');
    let ready = false;
    let queuedFile = null;
    let pendingRequest = null;
    let readyPoll = null;
    let currentFileName = 'photext-edited.png';
    let currentRecordId = null;
    let pendingUploadRequest = null;
    let uploadUsedBlob = false;
    let exportTimeout = null;

    const setStatus = (message) => { status.textContent = message; };

    async function sendImage(file) {
        if (!ready || !file) return;
        const requestId = 'open-' + Date.now();
        pendingUploadRequest = requestId;
        currentFileName = (file.name.replace(/\.[^.]+$/, '') || 'image') + '-edited.png';
        currentRecordId = null;
        reopenButton.disabled = true;
        exportButton.disabled = true;
        setStatus('Afbeelding voorbereiden voor PhoText…');
        try {
            // PhoText documents Blob/File as the preferred cross-origin payload.
            // Only fall back to a transferable ArrayBuffer if Blob transfer throws.
            if (pendingUploadRequest !== requestId || !frame.contentWindow) return;
            setStatus('Afbeelding uploaden naar PhoText…');
            try {
                uploadUsedBlob = true;
                frame.contentWindow.postMessage({
                    type: 'editor:open-image',
                    requestId,
                    blob: file,
                    filename: file.name,
                    mimeType: file.type || 'application/octet-stream'
                }, origin);
            } catch (blobError) {
                const buffer = await file.arrayBuffer();
                if (pendingUploadRequest !== requestId || !frame.contentWindow) return;
                frame.contentWindow.postMessage({
                    type: 'editor:open-image',
                    requestId,
                    arrayBuffer: buffer,
                    filename: file.name,
                    mimeType: file.type || 'application/octet-stream'
                }, origin, [buffer]);
            }
        } catch (error) {
            pendingUploadRequest = null;
            setStatus('De afbeelding kon niet worden voorbereid. Probeer een JPG-, PNG- of WEBP-bestand opnieuw.');
        }
    }

    fileInput.addEventListener('change', () => {
        const file = fileInput.files && fileInput.files[0];
        if (!file) return;
        if (!/^image\/(jpeg|png|webp|bmp)$/.test(file.type)) {
            setStatus('Kies een JPG-, PNG-, WEBP- of BMP-afbeelding.');
            fileInput.value = '';
            return;
        }
        if (file.size > 20 * 1024 * 1024) {
            setStatus('Deze afbeelding is groter dan 20 MB. Kies een kleiner bestand.');
            fileInput.value = '';
            return;
        }
        queuedFile = file;
        if (ready) sendImage(file);
        else setStatus('Afbeelding gekozen. PhoText wordt nog geladen…');
    });

    function pingEditor() {
        if (ready) {
            if (readyPoll) window.clearInterval(readyPoll);
            readyPoll = null;
            return;
        }
        frame.contentWindow.postMessage({type:'editor:ping'}, origin);
    }

    frame.addEventListener('load', () => {
        pingEditor();
        if (readyPoll) window.clearInterval(readyPoll);
        readyPoll = window.setInterval(pingEditor, 600);
    });

    window.addEventListener('message', (event) => {
        if (event.origin !== origin || event.source !== frame.contentWindow) return;
        const data = event.data;
        if (!data || typeof data !== 'object') return;

        if (data.type === 'editor:ready') {
            ready = true;
            if (readyPoll) window.clearInterval(readyPoll);
            readyPoll = null;
            setStatus('PhoText is klaar. Upload een afbeelding om te beginnen.');
            if (queuedFile) sendImage(queuedFile);
        }
        if (data.type === 'editor:open-image-progress' && (!data.requestId || data.requestId === pendingUploadRequest)) {
            const percent = Number(data.percentage);
            exportButton.disabled = true;
            setStatus('Afbeelding uploaden naar PhoText… ' + (Number.isFinite(percent) ? Math.max(0,Math.min(100,percent)) : 0) + '%');
        }
        if (data.type === 'editor:image-opened' && (!data.requestId || data.requestId === pendingUploadRequest)) {
            pendingUploadRequest = null;
            currentRecordId = typeof data.recordId === 'string' ? data.recordId : null;
            exportButton.disabled = false;
            reopenButton.disabled = !currentRecordId;
            setStatus('Afbeelding geopend. Bewerk de tekst en sla daarna het resultaat op.');
        }
        if (data.type === 'editor:open-image-error' && (!data.requestId || data.requestId === pendingUploadRequest)) {
            if (uploadUsedBlob && data.code === 'bad_payload' && queuedFile) {
                const failedRequestId = pendingUploadRequest;
                uploadUsedBlob = false;
                setStatus('PhoText vraagt een alternatieve upload. Opnieuw proberen…');
                queuedFile.arrayBuffer().then((buffer) => {
                    if (pendingUploadRequest !== failedRequestId || !frame.contentWindow) return;
                    frame.contentWindow.postMessage({
                        type: 'editor:open-image',
                        requestId: failedRequestId,
                        arrayBuffer: buffer,
                        filename: queuedFile.name,
                        mimeType: queuedFile.type || 'application/octet-stream'
                    }, origin, [buffer]);
                }).catch(() => {
                    pendingUploadRequest = null;
                    exportButton.disabled = true;
                    setStatus('Afbeelding kon niet worden voorbereid. Probeer het opnieuw.');
                });
                return;
            }
            pendingUploadRequest = null;
            exportButton.disabled = true;
            reopenButton.disabled = true;
            setStatus('Afbeelding kon niet worden geopend: ' + (data.error || data.code || 'onbekende fout'));
        }
        if (data.type === 'editor:result-error' && pendingRequest === data.requestId) {
            pendingRequest = null;
            if (exportTimeout) window.clearTimeout(exportTimeout);
            exportTimeout = null;
            exportButton.disabled = false;
            setStatus('Export mislukt: ' + (data.error || data.code || 'onbekende fout'));
        }
        if (data.type === 'editor:result' && pendingRequest === data.requestId) {
            pendingRequest = null;
            if (exportTimeout) window.clearTimeout(exportTimeout);
            exportTimeout = null;
            exportButton.disabled = false;
            if (typeof data.dataUrl !== 'string' || !/^data:image\/(png|jpeg|webp);base64,/i.test(data.dataUrl) || data.dataUrl.length > 60 * 1024 * 1024) {
                setStatus('PhoText gaf geen geldige afbeeldings-export terug. Probeer opnieuw.');
                return;
            }
            try {
                const link = document.createElement('a');
                link.href = data.dataUrl;
                const ext = formatInput.value === 'jpeg' ? 'jpg' : formatInput.value;
                link.download = currentFileName.replace(/\.[^.]+$/, '') + '.' + ext;
                document.body.appendChild(link);
                link.click();
                link.remove();
                setStatus('Klaar. De bewerkte afbeelding is gedownload naar je apparaat.');
            } catch (error) {
                setStatus('De export kon niet worden gedownload. Probeer het opnieuw.');
            }
        }
    });

    exportButton.addEventListener('click', () => {
        if (!ready || pendingRequest) return;
        pendingRequest = 'result-' + Date.now();
        exportButton.disabled = true;
        setStatus('Bewerkte afbeelding wordt klaargemaakt…');
        const format = formatInput.value;
        const quality = Math.max(0.5, Math.min(1, Number(qualityInput.value) / 100));
        frame.contentWindow.postMessage({type:'editor:get-result', requestId:pendingRequest, format, quality}, origin);
        exportTimeout = window.setTimeout(() => {
            if (!pendingRequest) return;
            pendingRequest = null;
            exportButton.disabled = false;
            setStatus('Export duurt te lang. Controleer of PhoText klaar is en probeer opnieuw.');
        }, 60000);
    });

    reopenButton.addEventListener('click', () => {
        if (!ready || !currentRecordId) return;
        exportButton.disabled = true;
        frame.contentWindow.postMessage({type:'editor:open-record', requestId:'reopen-' + Date.now(), recordId:currentRecordId}, origin);
        setStatus('Opgeslagen PhoText-afbeelding wordt opnieuw geopend…');
    });

    window.addEventListener('beforeunload', () => {
        queuedFile = null;
        pendingRequest = null;
        if (readyPoll) window.clearInterval(readyPoll);
        if (exportTimeout) window.clearTimeout(exportTimeout);
    });
})();
</script>
@endsection
