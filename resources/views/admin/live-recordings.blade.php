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
        .live-empty { padding: 28px; border: 1px dashed #3d4350; border-radius: 18px; color: #b9c2cf; }
        .live-upload { border: 1px solid #303542; background: #11151d; border-radius: 18px; padding: 20px; margin-bottom: 24px; }
        .live-upload h2 { margin: 0 0 8px; font-size: 20px; }
        .live-upload p { color: #b0b9c8; margin: 0 0 14px; }
        .live-upload form { display: flex; gap: 12px; flex-wrap: wrap; align-items: end; }
        .live-upload label { display: grid; gap: 6px; font-size: 13px; color: #cbd2de; }
        .live-upload input, .live-upload select { max-width: 100%; background: #202633; color: #f6f7fa; border: 1px solid #505869; border-radius: 9px; padding: 10px; font: inherit; }
        .live-upload progress { width: 100%; max-width: 440px; height: 12px; margin-top: 12px; }
        .live-upload .live-upload-message { margin-top: 9px; font-size: 13px; color: #cbd2de; }
    </style>

    <div class="live-head">
        <h1>Privé livestreamopnames</h1>
        <p>Alleen SmartDesk-beheerders hebben toegang tot deze pagina en de videobestanden. De actieve recorders voor knock1knock en emyii controleren iedere 60 seconden of de stream toegankelijk is en bewaren voltooide opnames privé als MP4. Voor lucycums kun je hieronder zelf een MP4 toevoegen; daarvoor draait nog geen automatische recorder. Een onbekende status betekent niet automatisch offline.</p>
    </div>

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
                <div style="margin-top: 10px;">
                    <a href="https://chaturbate.com/{{ $account }}/" target="_blank" rel="noopener noreferrer" class="live-muted">Bekijk bronpagina ↗</a>
                </div>
            </div>
        @endforeach
    </div>

    <section class="live-upload" aria-labelledby="live-upload-title">
        <h2 id="live-upload-title">MP4 privé toevoegen</h2>
        <p>Heb je zelf een opname gemaakt? Upload het MP4-bestand rechtstreeks naar jouw privéarchief. Maximaal 250 MB. Dit start geen nieuwe livestreamopname.</p>
        <form id="live-manual-upload">
            <label>Account
                <select id="live-upload-account" required>
                    @foreach($accounts as $account)
                        <option value="{{ $account }}" @selected($account === 'lucycums')>{{ '@' . $account }}</option>
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
            <video controls preload="none" playsinline controlsList="nodownload" src="{{ route('live.play', ['account' => $recording['account'], 'filename' => $recording['filename']]) }}"></video>
            <div class="live-body">
                <h3>{{ $recording['account'] }}</h3>
                <div class="live-muted">
                    {{ date('d-m-Y H:i', $recording['created_at']) }} ·
                    {{ number_format($recording['bytes'] / (1024 * 1024), 1, ',', '.') }} MB
                </div>
                <div class="live-actions">
                    <a class="live-btn" href="{{ route('live.download', ['account' => $recording['account'], 'filename' => $recording['filename']]) }}">Download MP4</a>
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
                    if ((status.message || '').startsWith('Opname opgeslagen: ')) {
                        const filename = status.message.slice('Opname opgeslagen: '.length).trim();
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
        setInterval(refresh, 60000);
    })();
</script>
<script>
    (() => {
        const form = document.getElementById('live-manual-upload');
        if (!form) return;
        const base = @json(route('live.upload.begin'));
        const csrf = @json(csrf_token());
        const fileInput = document.getElementById('live-upload-file');
        const accountInput = document.getElementById('live-upload-account');
        const button = document.getElementById('live-upload-button');
        const progress = document.getElementById('live-upload-progress');
        const message = document.getElementById('live-upload-message');
        const headers = { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' };

        async function checked(response) {
            if (response.ok) return response.json();
            let detail = '';
            try { detail = (await response.json()).message || ''; } catch (_) {}
            throw new Error(detail || 'Upload mislukt (HTTP ' + response.status + ')');
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const file = fileInput.files?.[0];
            if (!file || !/\.mp4$/i.test(file.name) || file.size < 1024 || file.size > 250 * 1024 * 1024) {
                message.textContent = 'Kies een MP4-bestand van maximaal 250 MB.';
                return;
            }
            button.disabled = true;
            progress.hidden = false;
            progress.value = 0;
            message.textContent = 'Privé-upload voorbereiden…';
            let uploadId = null;
            try {
                const begin = await checked(await fetch(base, {
                    method: 'POST', credentials: 'same-origin',
                    headers: { ...headers, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ account: accountInput.value, bytes: file.size }),
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
                    progress.value = Math.round((i + 1) / begin.parts * 95);
                    message.textContent = 'Uploaden: ' + progress.value + '%';
                }
                message.textContent = 'MP4 privé opslaan en controleren…';
                await checked(await fetch(base + '/' + encodeURIComponent(uploadId) + '/complete', {
                    method: 'POST', credentials: 'same-origin', headers,
                }));
                progress.value = 100;
                message.textContent = 'MP4 veilig opgeslagen. Archief wordt vernieuwd…';
                window.location.reload();
            } catch (error) {
                message.textContent = 'Upload niet opgeslagen: ' + (error?.message || 'onbekende fout');
                if (uploadId) {
                    fetch(base + '/' + encodeURIComponent(uploadId), {
                        method: 'DELETE', credentials: 'same-origin', headers,
                    }).catch(() => {});
                }
            } finally {
                button.disabled = false;
            }
        });
    })();
</script>
@endsection
