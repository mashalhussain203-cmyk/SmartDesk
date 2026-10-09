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
    </style>

    <div class="live-head">
        <h1>Privé livestreamopnames</h1>
        <p>Alleen SmartDesk-beheerders hebben toegang tot deze pagina en de videobestanden. De Chrome-recorder controleert iedere 60 seconden of je livestream actief is en bewaart voltooide opnames privé als MP4. Het eerste minuutje kan ontbreken. Een onbekende status is niet hetzelfde als offline.</p>
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
        <div class="live-empty">Nog geen voltooide MP4-opnames. Controleer of de Chrome-worker actief is en of er een livestream in de browser wordt afgespeeld.</div>
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
@endsection
