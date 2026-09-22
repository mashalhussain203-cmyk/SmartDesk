@extends('layouts.site-layout')

@section('title', 'Mashal AI Dashboard')

@section(
    'meta_description',
    'Mashal AI dashboard voor chats, projecten, documenten, templates en AI-tools.'
)

@push('styles')
<style>
    :root{
        --ai-bg:#161616;
        --ai-card:#202020;
        --ai-card-2:#252525;
        --ai-border:rgba(255,255,255,.09);
        --ai-text:#f1f1f1;
        --ai-muted:#9f9f9f;
    }
    .studio-page,.studio-page *{box-sizing:border-box}
    .studio-page{min-height:calc(100dvh - 72px);background:var(--ai-bg);color:var(--ai-text);padding:28px 18px 80px}
    .studio-shell{width:min(1180px,100%);margin:0 auto}
    .studio-hero{display:flex;justify-content:space-between;gap:20px;align-items:flex-end;margin-bottom:24px}
    .studio-kicker{color:var(--ai-muted);font-size:12px;font-weight:850;letter-spacing:.09em;text-transform:uppercase}
    .studio-hero h1{margin:5px 0 6px;font-size:clamp(28px,5vw,46px);line-height:1.02}
    .studio-hero p{margin:0;color:var(--ai-muted);max-width:720px;line-height:1.6}
    .studio-primary{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:0 17px;border-radius:12px;background:#fff;color:#111;text-decoration:none;font-weight:900;border:0;cursor:pointer;white-space:nowrap}
    .studio-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
    .studio-stat{background:var(--ai-card);border:1px solid var(--ai-border);border-radius:16px;padding:17px}
    .studio-stat strong{display:block;font-size:27px}
    .studio-stat span{display:block;margin-top:4px;color:var(--ai-muted);font-size:12px}
    .studio-section{margin-top:28px}
    .studio-section-head{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:12px}
    .studio-section-head h2{margin:0;font-size:19px}
    .studio-section-head span{color:var(--ai-muted);font-size:12px}
    .studio-search{display:flex;gap:9px}
    .studio-search input{flex:1;min-width:0;min-height:44px;border:1px solid var(--ai-border);border-radius:12px;background:var(--ai-card);color:var(--ai-text);padding:0 14px;font:inherit}
    .studio-search button{min-height:44px;padding:0 16px;border:0;border-radius:12px;background:#fff;color:#111;font-weight:900;cursor:pointer}
    .template-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:11px}
    .template-card{display:block;text-decoration:none;color:inherit;background:var(--ai-card);border:1px solid var(--ai-border);border-radius:16px;padding:16px;transition:.16s ease}
    .template-card:hover{transform:translateY(-2px);background:var(--ai-card-2)}
    .template-icon{font-size:22px}
    .template-category{margin-top:12px;color:#888;font-size:10px;font-weight:850;text-transform:uppercase;letter-spacing:.08em}
    .template-card strong{display:block;margin-top:4px;font-size:14px}
    .template-card p{margin:6px 0 0;color:var(--ai-muted);font-size:12px;line-height:1.5}
    .split-grid{display:grid;grid-template-columns:1.15fr .85fr;gap:14px}
    .panel{background:var(--ai-card);border:1px solid var(--ai-border);border-radius:16px;overflow:hidden}
    .panel-head{padding:15px 16px;border-bottom:1px solid var(--ai-border);font-weight:850}
    .row-link,.plain-row{display:flex;align-items:center;gap:11px;padding:13px 16px;border-bottom:1px solid rgba(255,255,255,.055);color:inherit;text-decoration:none}
    .row-link:last-child,.plain-row:last-child{border-bottom:0}
    .row-link:hover{background:rgba(255,255,255,.035)}
    .row-main{flex:1;min-width:0}
    .row-main strong{display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:13px}
    .row-main span{display:block;margin-top:3px;color:var(--ai-muted);font-size:11px}
    .badge{font-size:10px;color:#bbb;background:rgba(255,255,255,.07);padding:4px 7px;border-radius:999px}
    .status-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:9px}
    .status-card{background:var(--ai-card);border:1px solid var(--ai-border);border-radius:14px;padding:13px}
    .status-dot{width:8px;height:8px;border-radius:50%;display:inline-block;margin-right:6px;background:#777}
    .status-dot.ok{background:#55d88b}
    .status-card strong{font-size:12px}
    .status-card span{display:block;color:var(--ai-muted);font-size:10px;margin-top:6px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .search-results{margin-top:12px}
    .empty{padding:18px;color:var(--ai-muted);font-size:12px}
    .install-button{display:none;margin-left:8px;min-height:44px;padding:0 14px;border-radius:12px;border:1px solid var(--ai-border);background:transparent;color:#fff;font-weight:800;cursor:pointer}
    @media(max-width:900px){.studio-grid,.template-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.status-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.split-grid{grid-template-columns:1fr}}
    @media(max-width:600px){.studio-page{padding:20px 12px 70px}.studio-hero{align-items:flex-start;flex-direction:column}.studio-grid,.template-grid{grid-template-columns:1fr 1fr}.status-grid{grid-template-columns:1fr}.studio-primary,.install-button{width:100%;margin:0}.studio-hero-actions{width:100%;display:grid;gap:8px}}
</style>
@endpush

@section('content')
<div class="studio-page">
    <div class="studio-shell">
        <section class="studio-hero">
            <div>
                <div class="studio-kicker">Mashal AI Studio</div>
                <h1>Alles op één plek</h1>
                <p>
                    Start een AI-taak, open een project, zoek door je chats en bestanden
                    of controleer welke AI-functies beschikbaar zijn.
                </p>
            </div>

            <div class="studio-hero-actions">
                <a href="{{ route('ai.chat') }}" class="studio-primary">
                    + Nieuwe AI-chat
                </a>
                <button type="button" id="install-ai-app" class="install-button">
                    Installeer app
                </button>
            </div>
        </section>

        <section class="studio-grid">
            <div class="studio-stat">
                <strong>{{ $stats['conversations'] }}</strong>
                <span>Chats</span>
            </div>
            <div class="studio-stat">
                <strong>{{ $stats['projects'] }}</strong>
                <span>Projecten</span>
            </div>
            <div class="studio-stat">
                <strong>{{ $stats['documents'] }}</strong>
                <span>Projectbestanden</span>
            </div>
            <div class="studio-stat">
                <strong>{{ $stats['runs_30d'] }}</strong>
                <span>AI-runs laatste 30 dagen</span>
            </div>
        </section>

        <section class="studio-section">
            <div class="studio-section-head">
                <h2>Zoeken</h2>
                <span>Chats + documenten</span>
            </div>

            <form method="get" action="{{ route('ai.studio.dashboard') }}" class="studio-search">
                <input
                    type="search"
                    name="q"
                    value="{{ $searchQuery }}"
                    placeholder="Zoek in chats en projectbestanden…"
                    autocomplete="off"
                >
                <button type="submit">Zoeken</button>
            </form>

            @if(mb_strlen($searchQuery) >= 2)
                <div class="split-grid search-results">
                    <div class="panel">
                        <div class="panel-head">Gevonden chats</div>
                        @forelse(($searchResults['conversations'] ?? []) as $conversation)
                            <a href="{{ route('ai.chat') }}" class="row-link">
                                <div class="row-main">
                                    <strong>{{ $conversation['title'] }}</strong>
                                    <span>{{ $conversation['updated_at'] ?? '' }}</span>
                                </div>
                                <span class="badge">Chat</span>
                            </a>
                        @empty
                            <div class="empty">Geen chats gevonden.</div>
                        @endforelse
                    </div>

                    <div class="panel">
                        <div class="panel-head">Gevonden bestanden</div>
                        @forelse(($searchResults['documents'] ?? []) as $document)
                            <div class="plain-row">
                                <div class="row-main">
                                    <strong>{{ $document['name'] ?? 'Bestand' }}</strong>
                                    <span>{{ strtoupper($document['extension'] ?? '') }}</span>
                                </div>
                                <span class="badge">Bestand</span>
                            </div>
                        @empty
                            <div class="empty">Geen bestanden gevonden.</div>
                        @endforelse
                    </div>
                </div>
            @endif
        </section>

        <section class="studio-section">
            <div class="studio-section-head">
                <h2>AI Templates</h2>
                <span>{{ count($templates) }} snelle starts</span>
            </div>

            <div class="template-grid">
                @foreach($templates as $template)
                    <a
                        href="{{ route('ai.chat', ['template' => $template['slug']]) }}"
                        class="template-card"
                    >
                        <div class="template-icon">{{ $template['icon'] }}</div>
                        <div class="template-category">{{ $template['category'] }}</div>
                        <strong>{{ $template['title'] }}</strong>
                        <p>{{ $template['description'] }}</p>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="studio-section">
            <div class="studio-section-head">
                <h2>Recente activiteit</h2>
                <span>
                    Web: {{ $stats['web_runs_30d'] }}
                    · Code: {{ $stats['code_runs_30d'] }}
                    @if($stats['avg_duration_ms_30d'] > 0)
                        · Gem. {{ number_format($stats['avg_duration_ms_30d'] / 1000, 1) }}s
                    @endif
                </span>
            </div>

            <div class="split-grid">
                <div class="panel">
                    <div class="panel-head">Recente chats</div>
                    @forelse($recentConversations as $conversation)
                        <a href="{{ route('ai.chat') }}" class="row-link">
                            <div class="row-main">
                                <strong>{{ $conversation->title }}</strong>
                                <span>
                                    {{ optional($conversation->last_message_at ?? $conversation->updated_at)->diffForHumans() }}
                                </span>
                            </div>
                            @if($conversation->pinned)
                                <span class="badge">Vastgezet</span>
                            @else
                                <span class="badge">{{ $conversation->mode }}</span>
                            @endif
                        </a>
                    @empty
                        <div class="empty">Nog geen opgeslagen gesprekken.</div>
                    @endforelse
                </div>

                <div class="panel">
                    <div class="panel-head">Projecten</div>
                    @forelse($projects as $project)
                        <a href="{{ route('ai.chat') }}" class="row-link">
                            <div class="row-main">
                                <strong>{{ $project->name }}</strong>
                                <span>
                                    {{ $project->conversations_count }} chats
                                    · {{ $project->documents_count }} bestanden
                                </span>
                            </div>
                            <span class="badge">Project</span>
                        </a>
                    @empty
                        <div class="empty">Nog geen projecten.</div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="studio-section">
            <div class="studio-section-head">
                <h2>Systeemstatus</h2>
                <span>Configuratie van jouw AI-stack</span>
            </div>

            <div class="status-grid" id="ai-status-grid">
                @foreach($providerStatus as $status)
                    <div class="status-card">
                        <strong>
                            <span class="status-dot {{ $status['configured'] ? 'ok' : '' }}"></span>
                            {{ $status['label'] }}
                        </strong>
                        <span>{{ $status['configured'] ? ($status['model'] ?: 'Actief') : 'Niet geconfigureerd' }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        @if($recentDocuments->isNotEmpty())
        <section class="studio-section">
            <div class="studio-section-head">
                <h2>Recente bestanden</h2>
                <span>Projectkennis</span>
            </div>

            <div class="panel">
                @foreach($recentDocuments as $document)
                    <div class="plain-row">
                        <div class="row-main">
                            <strong>{{ $document->original_name }}</strong>
                            <span>
                                {{ strtoupper((string) $document->extension) }}
                                · {{ number_format($document->size_bytes / 1024, 0) }} KB
                            </span>
                        </div>
                        <span class="badge">{{ $document->analysis ?: 'bestand' }}</span>
                    </div>
                @endforeach
            </div>
        </section>
        @endif
    </div>
</div>

<script>
(function () {
    let deferredInstallPrompt = null;
    const installButton = document.getElementById('install-ai-app');

    if (!document.querySelector('link[rel="manifest"]')) {
        const manifest = document.createElement('link');
        manifest.rel = 'manifest';
        manifest.href = '/manifest.webmanifest';
        document.head.appendChild(manifest);
    }

    const theme = document.createElement('meta');
    theme.name = 'theme-color';
    theme.content = '#161616';
    document.head.appendChild(theme);

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker
                .register('/mashal-ai-sw.js')
                .catch(function () {});
        });
    }

    window.addEventListener('beforeinstallprompt', function (event) {
        event.preventDefault();
        deferredInstallPrompt = event;

        if (installButton) {
            installButton.style.display = 'inline-flex';
        }
    });

    installButton?.addEventListener('click', async function () {
        if (!deferredInstallPrompt) {
            return;
        }

        deferredInstallPrompt.prompt();
        await deferredInstallPrompt.userChoice;
        deferredInstallPrompt = null;
        installButton.style.display = 'none';
    });
})();
</script>
@endsection
