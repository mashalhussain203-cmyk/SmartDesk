@extends('layouts.admin-layout')
@section('title', 'SmartDesk | Privé livestreamopnames')
@section('page-title', 'Live-opnames')

@section('content')
<div class="live-library">
    <style>
        .live-library { color: #f6f7fa; max-width: 1400px; margin: 0 auto; }
        .live-library * { box-sizing: border-box; }
        .live-head { padding: 28px; background: #11151d; border: 1px solid #303542; border-radius: 22px; margin-bottom: 24px; }
        .live-head h1 { margin: 0 0 7px; font-size: clamp(24px, 3vw, 38px); }
        .live-head p { color: #afb7c6; margin: 0; line-height: 1.65; }
        .live-status-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; margin: 18px 0 25px; }
        .live-status-box { border: 1px solid #303542; border-radius: 18px; padding: 19px; background: #11151d; }
        .live-status-box strong { display: block; font-size: 18px; margin-bottom: 6px; }
        .live-status-box small { color: #b0b9c8; line-height: 1.6; }
        .live-pill { font-size: 12px; font-weight: 750; padding: 5px 10px; border: 1px solid #434b5b; border-radius: 20px; color: #c4cbd7; display: inline-block; margin-bottom: 12px; }
        .live-pill.recording { border-color: #5cd69e; color: #73ebac; }
        .live-pill.live { border-color: #5cd69e; color: #73ebac; }
        .live-pill.offline { border-color: #8a92a3; }
        .live-pill.error, .live-pill.needs_setup { border-color: #f19c91; color: #ffab9d; }
        .live-items { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 320px), 1fr)); gap: 16px; }
        .live-item { border: 1px solid #303542; border-radius: 18px; background: #11151d; overflow: hidden; }
        .live-item video { display: block; width: 100%; aspect-ratio: 16 / 9; background: #080b10; }
        .live-body { padding: 18px; }
        .live-body h3 { overflow-wrap: anywhere; margin: 0 0 8px; font-size: 16px; }
        .live-muted { font-size: 13px; color: #aeb8c5; }
        .live-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 16px; align-items: center; }
        .live-btn { display: inline-flex; align-items: center; justify-content: center; background: #7767e9; border-radius: 10px; border: 0; padding: 10px 14px; text-decoration: none; color: white; font: inherit; font-size: 13px; font-weight: 750; cursor: pointer; }
        .live-btn.danger { background: #513037; }
        .live-item video { min-height: 180px; }
        .live-video-error { display: none; padding: 10px 0; font-size: 13px; color: #ffb3a7; }
        .live-video-error[data-visible="true"] { display: block; }
        @media (max-width: 640px) {
            .live-head { padding: 18px; }
            .live-body { padding: 14px; }
            .live-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
            .live-actions .live-btn { width: 100%; min-height: 48px; text-align: center; }
            .live-actions form { grid-column: 1 / -1; }
            .live-actions form button { width: 100%; }
        }
        .live-empty { padding: 28px; border: 1px dashed #3d4350; border-radius: 18px; color: #b9c2cf; }
        .live-upload { border: 1px solid #303542; background: #11151d; border-radius: 18px; padding: 20px; margin-bottom: 24px; }
        .live-upload h2 { margin: 0 0 8px; font-size: 20px; }
        .live-upload p { color: #b0b9c8; margin: 0 0 14px; }
        .live-upload form { display: flex; gap: 12px; flex-wrap: wrap; align-items: end; }
        .live-upload label { display: grid; gap: 6px; font-size: 13px; color: #cbd2de; }
        .live-upload input, .live-upload select { max-width: 100%; background: #202633; color: #f6f7fa; border: 1px solid #505869; border-radius: 9px; padding: 10px; font: inherit; }
        .live-upload progress { width: 100%; max-width: 440px; height: 12px; margin-top: 12px; }
        .live-upload .live-upload-message { margin-top: 9px; font-size: 13px; color: #cbd2de; }
        .live-capture { border: 1px solid #45506a; background: #141a26; border-radius: 18px; padding: 20px; margin-bottom: 24px; }
        .live-capture h2 { margin: 0 0 8px; font-size: 20px; }
        .live-capture p { color: #c2cad7; margin: 0 0 12px; line-height: 1.55; }
        .live-capture progress { width: 100%; max-width: 440px; height: 12px; margin-top: 10px; }
        .live-capture .live-capture-message { margin: 12px 0 0; font-size: 14px; color: #cbd2de; }
        .live-capture button:disabled, .live-upload button:disabled { opacity: .55; cursor: wait; }
        .live-automatic { padding: 20px 24px; background: #141e1b; border: 1px solid #3d7562; border-radius: 18px; margin: 0 0 24px; }
        .live-automatic h2 { margin: 0 0 10px; font-size: 20px; color: #a5f0c9; }
        .live-automatic p { margin: 0; color: #d1e0d9; line-height: 1.6; }
        .live-automatic .live-automatic-facts { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 14px; }
        .live-automatic-facts span { padding: 7px 11px; background: #233b32; border-radius: 9px; font-size: 13px; color: #d7f5e5; }
        .live-last-check { display: block; margin-top: 8px; color: #8c9bad; font-size: 12px; }
        .live-last-saved { display: block; margin-top: 6px; color: #a5f0c9; font-size: 12px; overflow-wrap: anywhere; }
        .live-capture summary { cursor: pointer; color: #c6d1f5; font-weight: 650; }
        .live-capture .live-capture-inner { padding-top: 14px; }
    </style>

    <div class="live-head">
        <h1>Privé livestreamopnames</h1>
        <p>Automatische privéopnames van jouw livestreams. De Railway-server blijft controleren en opnemen, ook wanneer deze pagina gesloten is. Hieronder zie je de actuele status en de opgeslagen MP4-bestanden.</p>
    </div>

    <section class="live-automatic" aria-label="Automatisch volledige livestream opnemen">
        <h2>Automatisch de volledige livestream opnemen</h2>
        <p>De server controleert <strong>knock1knock, emyii, cutefacebigass, ricasashaa, julesxdann en dellris</strong> elke 60 seconden. Zodra een publieke stream met beeld én geluid afspeelbaar is, start de opname vanzelf en loopt deze door totdat de uitzending stopt. Daarna wordt de MP4 in het privéarchief gecontroleerd en opgeslagen. Je hoeft niets aan te klikken en deze pagina hoeft niet open te blijven. Door de detectietijd of verbindingsproblemen kan het begin ontbreken of een opname onderbroken zijn.</p>
        <div class="live-automatic-facts">
            <span>Automatische achtergrondcontrole: 60 sec.</span>
            <span>Opnameduur: geen limiet van 30 sec.</span>
            <span>Opslag: privé-MP4 na afloop</span>
            <span>Status ververst: 15 sec.</span>
        </div>
    </section>

        @if(!empty($archiveError))
        <p role="alert" style="padding:12px;border:1px solid #f19c91;border-radius:12px;color:#ffab9d;">{{ $archiveError }}</p>
    @endif

    <div class="live-status-grid">
        @foreach($accounts as $account)
            <div class="live-status-box" data-live-account="{{ $account }}">
                <span class="live-pill {{ $statuses[$account]['status'] ?? 'unknown' }}" data-live-state>
                    {{ strtoupper($statuses[$account]['status'] ?? 'unknown') }}
                </span>
                <strong>{{ '@' . $account }}</strong>
                <small data-live-message>{{ $statuses[$account]['message'] ?? '' }}</small>
                <small class="live-last-check" data-live-checked>@if(!empty($statuses[$account]['checked_at']))Laatst gecontroleerd: {{ $statuses[$account]['checked_at'] }}@else Nog geen actuele controle @endif</small>
                <small class="live-last-saved" data-live-saved>@if(!empty($statuses[$account]['last_saved']))Laatst opgeslagen: {{ $statuses[$account]['last_saved'] }}@endif</small>
                <div style="margin-top: 10px;">
                    <a href="https://chaturbate.com/{{ $account }}/" target="_blank" rel="noopener noreferrer" class="live-muted">Bekijk bronpagina ↗</a>
                </div>
            </div>
        @endforeach
    </div>

    <details class="live-capture">
        <summary id="live-capture-title">Extra optie: handmatige 30-secondenopname (niet nodig voor automatische opnames)</summary>
        <div class="live-capture-inner">
        <p>Alleen als reserveoptie; de automatische serverrecorder werkt zonder jouw browser. Open <a href="https://chaturbate.com/knock1knock/" target="_blank" rel="noopener noreferrer">de knock1knock-stream</a> in een andere Chrome-tab en zorg dat de video afspeelt en het geluid aan staat. Klik hieronder en kies bij het delen <strong>Chrome-tab → knock1knock → Tabgeluid delen</strong>. Je browser vraagt eerst toestemming; SmartDesk kan niet zelfstandig je scherm bekijken.</p>
        <button id="live-capture-button" class="live-btn" type="button">Neem 30 seconden op en sla privé op</button>
        <progress id="live-capture-progress" value="0" max="100" hidden aria-label="Opname-uploadvoortgang"></progress>
        <p id="live-capture-message" class="live-capture-message" role="status" aria-live="polite">Alleen jouw gekozen tab wordt opgenomen. Na 30 seconden wordt de opname als MP4 in het privéarchief opgeslagen.</p>
        </div>
    </details>

    <section class="live-upload" aria-labelledby="live-upload-title">
        <h2 id="live-upload-title">MP4 privé toevoegen</h2>
        <p>Heb je zelf een opname gemaakt? Upload het MP4-bestand rechtstreeks naar jouw privéarchief. Maximaal 250 MB. Dit start geen nieuwe livestreamopname.</p>
        <form id="live-manual-upload">
            <label>Account
                <select id="live-upload-account" required>
                    @foreach($accounts as $account)
                        <option value="{{ $account }}" @selected($account === 'knock1knock')>{{ '@' . $account }}</option>
                    @endforeach
                </select>
            </label>
            <label>MP4-bestand
                <input id="live-upload-file" type="file" accept=".mp4,video/mp4" required>
            </label>
            <button id="live-upload-button" type="submit" class="live-btn">Privé opslaan</button>
        </form>
        <progress id="live-upload-progress" value="0" max="100" hidden aria-label="Uploadvoortgang"></progress>
        <p id="live-upload-message" class="live-upload-message" role="status" aria-live="polite"></p>
    </section>

    <h2 style="font-size:22px;margin:0 0 15px;">Opgeslagen video's ({{ count($recordings) }})</h2>
    @if(session('success'))
        <p role="status" style="color:#7ee6b3;">{{ session('success') }}</p>
    @endif

    @forelse($recordings as $recording)
        @if($loop->first)<div class="live-items">@endif
        <article class="live-item">
            <video controls preload="metadata" playsinline webkit-playsinline
                src="{{ route('live.play', ['account' => $recording['account'], 'filename' => $recording['filename']]) }}"
                aria-label="Privéopname van {{ $recording['account'] }}"></video>
            <div class="live-body">
                <h3>{{ $recording['account'] }}</h3>
                <div class="live-muted">
                    {{ date('d-m-Y H:i', $recording['created_at']) }} ·
                    {{ number_format($recording['bytes'] / (1024 * 1024), 1, ',', '.') }} MB
                </div>
                <p class="live-video-error" data-video-error role="status">De videospeler kon dit bestand niet laden. Probeer ‘Open video’ of ‘Download MP4’.</p>
                <div class="live-actions">
                    <a class="live-btn" href="{{ route('live.play', ['account' => $recording['account'], 'filename' => $recording['filename']]) }}" target="_blank" rel="noopener">Open video</a>
                    <a class="live-btn" href="{{ route('live.download', ['account' => $recording['account'], 'filename' => $recording['filename']]) }}" download="{{ $recording['filename'] }}">Download MP4</a>
                    <form action="{{ route('live.destroy', ['account' => $recording['account'], 'filename' => $recording['filename']]) }}" method="POST" onsubmit="return confirm('Deze privéopname definitief verwijderen?');">
                        @csrf
                        @method('DELETE')
                        <button class="live-btn danger" type="submit">Verwijderen</button>
                    </form>
                </div>
            </div>
        </article>
        @if($loop->last)</div>@endif
    @empty
        <div class="live-empty">Nog geen voltooide MP4-opnames. Controleer de recorderstatus en of de livestream toegankelijk is.</div>
    @endforelse
</div>
<script>
    (() => {
        document.querySelectorAll('.live-item video').forEach(video => {
            video.addEventListener('error', () => {
                const error = video.closest('.live-item')?.querySelector('[data-video-error]');
                if (error) error.dataset.visible = 'true';
            });
        });
        const url = @json(route('live.status'));
        const labels = { recording: 'OPNAME LOOPT', uploading: 'BEZIG MET OPSLAAN', live: 'LIVE', offline: 'OFFLINE', unknown: 'ONBEKEND', error: 'FOUT', needs_setup: 'INSTELLEN' };
        async function refresh() {
            try {
                const response = await fetch(url, { credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json' } });
                if (!response.ok) return;
                const statuses = await response.json();
                document.querySelectorAll('[data-live-account]').forEach(card => {
                    const status = statuses[card.dataset.liveAccount];
                    if (!status) return;
                    const pill = card.querySelector('[data-live-state]');
                    pill.className = 'live-pill ' + status.status;
                    pill.textContent = labels[status.status] || 'ONBEKEND';
                    card.querySelector('[data-live-message]').textContent = status.message || '';
                    const checked = card.querySelector('[data-live-checked]');
                    checked.textContent = status.checked_at
                        ? 'Laatst gecontroleerd: ' + new Date(status.checked_at).toLocaleString('nl-NL')
                        : 'Nog geen actuele controle';
                    const saved = card.querySelector('[data-live-saved]');
                    saved.textContent = status.last_saved ? 'Laatst opgeslagen: ' + status.last_saved : '';
                    const filename = status.last_saved ||
                        ((status.message || '').startsWith('Opname opgeslagen: ')
                            ? status.message.slice('Opname opgeslagen: '.length).trim() : '');
                    if (filename) {
                        if (/^[A-Za-z0-9_.-]+\.mp4$/.test(filename)) {
                            const exists = Array.from(document.querySelectorAll('.live-item video')).some(video =>
                                decodeURIComponent(new URL(video.src).pathname).endsWith('/' + filename + '/watch')
                            );
                            const playing = Array.from(document.querySelectorAll('.live-item video')).some(video => !video.paused);
                            if (!exists && !playing) window.location.reload();
                        }
                    }
                });
            } catch (_error) {
                // Keep previous status; do not falsely report OFFLINE on network failure.
            }
        }
        refresh();
        setInterval(refresh, 15000);
    })();
</script>
<script>
    (() => {
        const base = @json(route('live.upload.begin'));
        const csrf = @json(csrf_token());
        const headers = { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' };
        const form = document.getElementById('live-manual-upload');
        const fileInput = document.getElementById('live-upload-file');
        const accountInput = document.getElementById('live-upload-account');
        const button = document.getElementById('live-upload-button');
        const progress = document.getElementById('live-upload-progress');
        const message = document.getElementById('live-upload-message');
        const captureButton = document.getElementById('live-capture-button');
        const captureProgress = document.getElementById('live-capture-progress');
        const captureMessage = document.getElementById('live-capture-message');

        async function checked(response) {
            if (response.ok) return response.json();
            let detail = '';
            try { detail = (await response.json()).message || ''; } catch (_) {}
            throw new Error(detail || 'Upload mislukt (HTTP ' + response.status + ')');
        }

        async function uploadPrivate(file, account, format, onProgress) {
            if (file.size < 1024 || file.size > 250 * 1024 * 1024) {
                throw new Error('Opname moet tussen 1 KB en 250 MB zijn.');
            }
            let uploadId = null;
            try {
                const begin = await checked(await fetch(base, {
                    method: 'POST', credentials: 'same-origin',
                    headers: { ...headers, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ account, bytes: file.size, format }),
                }));
                uploadId = begin.id;
                const chunkSize = begin.chunk_bytes;
                for (let i = 0; i < begin.parts; i++) {
                    const chunk = file.slice(i * chunkSize, Math.min(file.size, (i + 1) * chunkSize));
                    await checked(await fetch(base + '/' + encodeURIComponent(uploadId) + '/parts/' + i, {
                        method: 'PUT', credentials: 'same-origin',
                        headers: { ...headers, 'Content-Type': 'application/octet-stream' },
                        body: chunk,
                    }));
                    onProgress(Math.round((i + 1) / begin.parts * 95), 'Uploaden');
                }
                onProgress(96, format === 'webm' ? 'Omzetten naar MP4 en privé opslaan' : 'Privé opslaan en controleren');
                await checked(await fetch(base + '/' + encodeURIComponent(uploadId) + '/complete', {
                    method: 'POST', credentials: 'same-origin', headers,
                }));
                onProgress(100, 'MP4 veilig opgeslagen');
            } catch (error) {
                if (uploadId) {
                    await fetch(base + '/' + encodeURIComponent(uploadId), {
                        method: 'DELETE', credentials: 'same-origin', headers,
                    }).catch(() => {});
                }
                throw error;
            }
        }

        form?.addEventListener('submit', async (event) => {
            event.preventDefault();
            const file = fileInput.files?.[0];
            if (!file || !/\.mp4$/i.test(file.name)) {
                message.textContent = 'Kies een MP4-bestand.';
                return;
            }
            button.disabled = true;
            progress.hidden = false;
            progress.value = 0;
            message.textContent = 'Privé-upload voorbereiden…';
            try {
                await uploadPrivate(file, accountInput.value, 'mp4', (percent, stage) => {
                    progress.value = percent;
                    message.textContent = stage + ': ' + percent + '%';
                });
                message.textContent = 'MP4 veilig opgeslagen. Archief wordt vernieuwd…';
                window.location.reload();
            } catch (error) {
                message.textContent = 'Upload niet opgeslagen: ' + (error?.message || 'onbekende fout');
            } finally {
                button.disabled = false;
            }
        });

        captureButton?.addEventListener('click', async () => {
            if (!navigator.mediaDevices?.getDisplayMedia || typeof MediaRecorder === 'undefined') {
                captureMessage.textContent = 'Gebruik Chrome op een computer: deze browser ondersteunt geen tab-opname.';
                return;
            }
            captureButton.disabled = true;
            captureProgress.hidden = true;
            let stream = null;
            let stopTimer = null;
            let countdown = null;
            try {
                // Browser permission is always user-initiated. Never auto-select or inspect tabs.
                stream = await navigator.mediaDevices.getDisplayMedia({
                    video: { frameRate: 25 },
                    audio: { echoCancellation: false, noiseSuppression: false },
                    systemAudio: 'include',
                    preferCurrentTab: false,
                    selfBrowserSurface: 'exclude',
                });
                const videoTrack = stream.getVideoTracks()[0];
                if (!videoTrack) throw new Error('Geen videotab geselecteerd.');
                const surface = videoTrack.getSettings()?.displaySurface;
                if (surface && surface !== 'browser') {
                    throw new Error('Kies de Chrome-tab met knock1knock, niet je hele scherm of venster.');
                }
                if (!stream.getAudioTracks().length) {
                    throw new Error('Tabgeluid ontbreekt. Selecteer Chrome-tab en vink Tabgeluid delen aan.');
                }

                const mime = [
                    'video/webm;codecs=vp8,opus',
                    'video/webm;codecs=vp9,opus',
                    'video/webm',
                    'video/mp4;codecs=avc1.42E01E,mp4a.40.2',
                    'video/mp4',
                ].find(type => MediaRecorder.isTypeSupported(type));
                if (!mime) throw new Error('Deze browser kan geen opnamebestand maken. Gebruik Chrome.');
                const recorder = new MediaRecorder(stream, {
                    mimeType: mime, videoBitsPerSecond: 2500000, audioBitsPerSecond: 128000,
                });
                const chunks = [];
                const stopped = new Promise((resolve, reject) => {
                    recorder.addEventListener('dataavailable', event => {
                        if (event.data?.size) chunks.push(event.data);
                    });
                    recorder.addEventListener('error', event => {
                        reject(event.error || new Error('Opname gestopt door browserfout.'));
                    }, { once: true });
                    recorder.addEventListener('stop', resolve, { once: true });
                });
                videoTrack.addEventListener('ended', () => {
                    if (recorder.state !== 'inactive') recorder.stop();
                }, { once: true });
                recorder.start(1000);
                const started = performance.now();
                captureMessage.textContent = 'Opname loopt: nog 30 seconden. Laat de knock1knock-tab afspelen.';
                countdown = setInterval(() => {
                    const remaining = Math.max(0, 30 - Math.floor((performance.now() - started) / 1000));
                    captureMessage.textContent = 'Opname loopt: nog ' + remaining + ' seconden.';
                }, 500);
                stopTimer = setTimeout(() => {
                    if (recorder.state !== 'inactive') recorder.stop();
                }, 30000);
                await stopped;
                if (performance.now() - started < 28500) {
                    throw new Error('Tabdeling voortijdig gestopt; geen onvolledige opname opgeslagen.');
                }
                const recordedMime = recorder.mimeType || mime;
                const format = recordedMime.startsWith('video/mp4') ? 'mp4' : 'webm';
                const blob = new Blob(chunks, { type: recordedMime });
                captureProgress.hidden = false;
                captureProgress.value = 0;
                captureMessage.textContent = '30 seconden opgenomen. MP4 wordt privé opgeslagen…';
                await uploadPrivate(blob, 'knock1knock', format, (percent, stage) => {
                    captureProgress.value = percent;
                    captureMessage.textContent = stage + ': ' + percent + '%';
                });
                captureMessage.textContent = 'Opname opgeslagen. Privéarchief wordt vernieuwd…';
                window.location.reload();
            } catch (error) {
                captureMessage.textContent = error?.name === 'NotAllowedError'
                    ? 'Geen tab gedeeld. Klik opnieuw en geef Chrome toestemming om de tab op te nemen.'
                    : 'Geen opname opgeslagen: ' + (error?.message || 'onbekende fout');
            } finally {
                clearTimeout(stopTimer);
                clearInterval(countdown);
                stream?.getTracks().forEach(track => track.stop());
                captureButton.disabled = false;
            }
        });
    })();
</script>
@endsection
