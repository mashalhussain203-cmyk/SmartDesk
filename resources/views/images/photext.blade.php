@extends('layouts.site-layout')

@section('title', 'PhoText AI Text Editor | Mashal Studio')
@section('meta_description', 'Bewerk tekst in afbeeldingen met PhoText, rechtstreeks vanuit Mashal Studio.')

@push('styles')
<style>
    .photext-page{min-height:calc(100vh - 80px);padding:34px 0 64px;color:#f4f7f5;background:radial-gradient(circle at 12% 5%,rgba(24,180,112,.12),transparent 30rem),linear-gradient(180deg,#070b09,#0a100d)}
    .photext-shell{width:min(100% - 32px,1500px);margin:auto}
    .photext-head{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin-bottom:20px}
    .photext-kicker{color:#55d99b;font-size:10px;font-weight:900;letter-spacing:.16em;text-transform:uppercase}
    .photext-head h1{margin:10px 0 8px;font-size:clamp(32px,5vw,54px);line-height:1;letter-spacing:-.055em}
    .photext-head p{max-width:720px;margin:0;color:#99a59e;font-size:13px;line-height:1.7}
    .photext-actions{display:flex;gap:9px;flex-wrap:wrap}
    .photext-btn{min-height:42px;padding:0 15px;display:inline-flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,.12);border-radius:11px;background:#111a15;color:#eaf5ee;text-decoration:none;font-size:12px;font-weight:800;cursor:pointer}
    .photext-btn.primary{border-color:#24a96c;background:linear-gradient(135deg,#26b875,#13784e);color:#fff}
    .photext-toolbar{display:flex;align-items:center;gap:12px;flex-wrap:wrap;padding:13px 15px;margin-bottom:12px;border:1px solid rgba(255,255,255,.09);border-radius:15px;background:rgba(255,255,255,.035)}
    .photext-toolbar input{max-width:100%;color:#dbe8df;font-size:12px}
    .photext-status{flex:1;min-width:220px;color:#9eaaa3;font-size:12px}
    .photext-frame-wrap{overflow:hidden;border:1px solid rgba(255,255,255,.1);border-radius:18px;background:#101511;box-shadow:0 28px 80px rgba(0,0,0,.28)}
    .photext-frame{display:block;width:100%;height:min(78vh,980px);min-height:620px;border:0;background:#fff}
    .photext-notice{margin:12px 2px 0;color:#84928a;font-size:11px;line-height:1.65}
    @media(max-width:700px){.photext-page{padding-top:24px}.photext-head{display:block}.photext-actions{margin-top:16px}.photext-frame{height:76vh;min-height:560px}.photext-toolbar{align-items:flex-start}.photext-status{flex-basis:100%}}
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
            <button class="photext-btn primary" id="photext-export" type="button" disabled>Bewerkte afbeelding opslaan</button>
            <span class="photext-status" id="photext-status" role="status" aria-live="polite">Editor wordt geladen. Upload daarna een JPG, PNG, WEBP of BMP.</span>
        </section>

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
    const status = document.getElementById('photext-status');
    let ready = false;
    let queuedFile = null;
    let pendingRequest = null;
    let currentFileName = 'photext-edited.png';

    const setStatus = (message) => { status.textContent = message; };

    function sendImage(file) {
        if (!ready || !file) return;
        const requestId = 'open-' + Date.now();
        currentFileName = (file.name.replace(/\.[^.]+$/, '') || 'image') + '-edited.png';
        setStatus('Afbeelding wordt veilig doorgestuurd naar PhoText…');
        frame.contentWindow.postMessage({
            type: 'editor:open-image',
            requestId,
            blob: file,
            filename: file.name,
            mimeType: file.type
        }, origin);
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

    frame.addEventListener('load', () => {
        frame.contentWindow.postMessage({type:'editor:ping'}, origin);
    });

    window.addEventListener('message', (event) => {
        if (event.origin !== origin || event.source !== frame.contentWindow) return;
        const data = event.data;
        if (!data || typeof data !== 'object') return;

        if (data.type === 'editor:ready') {
            ready = true;
            setStatus('PhoText is klaar. Upload een afbeelding om te beginnen.');
            if (queuedFile) sendImage(queuedFile);
        }
        if (data.type === 'editor:open-image-progress') {
            const percent = Number(data.percentage);
            setStatus('Afbeelding uploaden naar PhoText… ' + (Number.isFinite(percent) ? Math.max(0,Math.min(100,percent)) : 0) + '%');
        }
        if (data.type === 'editor:image-opened') {
            setStatus('Afbeelding geopend. Bewerk de tekst in de editor en klik daarna op “Bewerkte afbeelding opslaan”.');
        }
        if (data.type === 'editor:open-image-error') {
            setStatus('Afbeelding kon niet worden geopend: ' + (data.error || data.code || 'onbekende fout'));
        }
        if (data.type === 'editor:result-error' && pendingRequest === data.requestId) {
            pendingRequest = null;
            exportButton.disabled = false;
            setStatus('Export mislukt: ' + (data.error || data.code || 'onbekende fout'));
        }
        if (data.type === 'editor:result' && pendingRequest === data.requestId) {
            pendingRequest = null;
            exportButton.disabled = false;
            try {
                const link = document.createElement('a');
                link.href = data.dataUrl;
                link.download = currentFileName;
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
        frame.contentWindow.postMessage({
            type:'editor:get-result',
            requestId:pendingRequest,
            format:'png'
        }, origin);
    });

    window.addEventListener('beforeunload', () => {
        queuedFile = null;
        pendingRequest = null;
    });
})();
</script>
@endsection
