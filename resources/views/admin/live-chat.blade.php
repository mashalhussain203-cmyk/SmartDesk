@extends('layouts.admin-layout')
@section('title', 'Live chat | Mashal Admin')
@section('page-title', 'Live chat')
@section('content')
<style>
.lca { color:#f4f0e8; font:14px/1.6 system-ui,sans-serif; }
.lca * { box-sizing:border-box; }
.lca [hidden] { display:none!important; }
.lca button,.lca textarea,.lca select { font:inherit; }
.lca button,.lca select { cursor:pointer; }
.lca button:disabled { opacity:.4; cursor:default; }
.lca :is(button,textarea,select,input):focus-visible { outline:2px solid #e6bf82; outline-offset:3px; }
.lca-top { display:flex; gap:16px; align-items:center; justify-content:space-between; flex-wrap:wrap; padding:22px; border:1px solid #ffffff14; border-radius:18px; background:#15171b; margin-bottom:18px; }
.lca h1 { margin:0; font-size:24px; }
.lca-top p { margin:4px 0 0; color:#a7a8af; font-size:12px; }
.lca-grid { display:grid; grid-template-columns:300px minmax(0,1fr); min-height:540px; height:70vh; border:1px solid #ffffff14; border-radius:18px; overflow:hidden; background:#111318; }
.lca-inbox { display:flex; flex-direction:column; min-height:0; border-right:1px solid #ffffff14; }
.lca-filter { display:flex; gap:8px; padding:14px; border-bottom:1px solid #ffffff14; }
.lca select,.lca button { color:#e8c792; background:#202127; border:1px solid #ffffff18; border-radius:9px; padding:9px 12px; }
.lca-list { flex:1; overflow:auto; }
.lca-item { display:block; width:100%; padding:16px!important; border:0!important; border-bottom:1px solid #ffffff0b!important; border-radius:0!important; background:transparent!important; color:#eee!important; text-align:left; overflow-wrap:anywhere; }
.lca-item[aria-pressed="true"] { background:#e3b36b16!important; box-shadow:inset 3px 0 #e3b36b; }
.lca-item strong,.lca-item span { display:block; }
.lca-item span { color:#a3a4aa; font-size:12px; }
.lca-pages { display:flex; justify-content:space-between; align-items:center; padding:10px; gap:6px; font-size:12px; }
.lca-detail { display:flex; flex-direction:column; min-width:0; min-height:0; }
.lca-heading { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:16px; border-bottom:1px solid #ffffff14; }
.lca-heading p { margin:0; color:#a7a8af; overflow-wrap:anywhere; font-size:12px; }
.lca-log { flex:1; min-height:0; overflow:auto; padding:18px; }
.lca-message { width:fit-content; max-width:85%; padding:12px 15px; margin:0 0 14px; background:#252832; border-radius:12px; }
.lca-message[data-sender="admin"] { margin-left:auto; background:#e6c18a; color:#221b11; }
.lca-message small { opacity:.7; font-size:10px; }
.lca-message p { margin:4px 0 0; white-space:pre-wrap; overflow-wrap:anywhere; }
.lca-msg-head { display:flex; align-items:center; gap:8px; margin-bottom:7px; }
.lca-avatar { width:30px; height:30px; flex:0 0 30px; border-radius:50%; overflow:hidden; display:grid; place-items:center; background:#363a45; color:#fff; font-size:11px; font-weight:800; }
.lca-avatar img { width:100%; height:100%; object-fit:cover; }
.lca-media-image { display:block; max-width:min(320px,100%); max-height:320px; border-radius:10px; object-fit:cover; }
.lca-message audio { width:min(320px,100%); }
.lca-file-link { color:inherit; text-decoration:underline; }
.lca-delete { margin-top:7px!important; padding:2px 0!important; border:0!important; background:transparent!important; color:inherit!important; opacity:.65; font-size:10px!important; }
.lca-delete:hover { opacity:1; text-decoration:underline; }
.lca-tool { display:grid; place-items:center; width:42px; height:42px; flex:0 0 42px; padding:0!important; }
.lca-tool[aria-pressed="true"] { background:#7c3030!important; color:#fff!important; }
.lca-form { display:flex; gap:10px; align-items:flex-end; padding:14px; border-top:1px solid #ffffff14; }
.lca textarea { flex:1; min-width:0; min-height:80px; max-height:180px; resize:vertical; background:#080a0d; color:#eee; border:1px solid #ffffff25; border-radius:10px; padding:10px; font-size:16px; }
.lca-error { color:#ffb6b6; }
@media(max-width:850px) { .lca-grid { grid-template-columns:1fr; height:auto; } .lca-inbox { max-height:310px; border-right:0; border-bottom:1px solid #ffffff14; } .lca-detail { height:65vh; min-height:420px; } }
</style>
<div class="lca" id="admin-live-chat" data-inbox="{{ route('admin.live-chat.conversations') }}" data-presence="{{ route('admin.live-chat.presence') }}" data-base="{{ route('admin.live-chat.conversations') }}">
    <header class="lca-top"><div><h1>Live gesprekken</h1><p>Gasten en ingelogde bezoekers. Berichten verversen automatisch.</p></div><label><input type="checkbox" id="lca-online" checked> Beschikbaar voor live chat<br><small>Actief zolang dit tabblad zichtbaar is.</small></label></header>
    <p class="lca-error" role="status" hidden></p>
    <div class="lca-grid">
        <aside class="lca-inbox" aria-label="Gesprekken">
            <div class="lca-filter"><select aria-label="Gesprekken filteren"><option value="active">Actieve gesprekken</option><option value="closed">Afgesloten gesprekken</option></select></div>
            <div class="lca-list"><p style="padding:16px">Gesprekken laden…</p></div>
            <div class="lca-pages"><button type="button" data-prev aria-label="Vorige pagina">←</button><span data-page></span><button type="button" data-next aria-label="Volgende pagina">→</button></div>
        </aside>
        <section class="lca-detail" aria-label="Geselecteerd gesprek">
            <header class="lca-heading"><div><strong data-name>Kies een gesprek</strong><p data-email>Hier verschijnen naam en e-mailadres van ingelogde bezoekers.</p><p data-status></p></div><button type="button" data-close hidden>Afsluiten</button></header>
            <div class="lca-log" role="log" aria-live="polite" aria-relevant="additions" aria-label="Berichten"></div>
            <form class="lca-form"><label class="lca-tool" title="Bestand versturen" aria-label="Bestand versturen">📎<input class="lca-file" type="file" accept="image/jpeg,image/png,image/webp,image/gif,application/pdf,text/plain,.doc,.docx,.xls,.xlsx" hidden></label><button type="button" class="lca-tool lca-voice" title="Spraakbericht opnemen" aria-label="Spraakbericht opnemen">🎤</button><textarea aria-label="Antwoord aan bezoeker" placeholder="Typ je antwoord…" maxlength="4000" required disabled></textarea><button type="submit" disabled>Verstuur</button></form>
        </section>
    </div>
</div>
<script src="{{ asset('js/admin-live-chat.js') }}?v=2" defer></script>
@endsection
