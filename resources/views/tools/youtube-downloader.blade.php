@extends('layouts.site-layout')

@section('title', 'YouTube Downloader | Mashal Studio')
@section('meta_description', 'Analyseer openbare YouTube-video’s en download beschikbare MP4-resoluties of MP3-audio.')

@push('styles')
<style>
.ytd-page{position:relative;isolation:isolate;min-height:calc(100vh - 78px);padding:clamp(34px,6vw,68px) 16px 90px;color:var(--studio-text,#f7f7f4);background:radial-gradient(ellipse at 50% -10%,rgba(143,130,255,.16),transparent 38rem),radial-gradient(ellipse at 92% 38%,rgba(115,215,255,.055),transparent 30rem),linear-gradient(180deg,#07080b 0%,#090b10 55%,#07080b 100%)}
.ytd-wrap{width:min(100%,960px);margin:0 auto}.ytd-hero{text-align:center;margin:0 auto 30px;max-width:760px}
.ytd-pill{display:inline-flex;align-items:center;gap:9px;padding:8px 13px;border:1px solid rgba(159,140,255,.24);border-radius:99px;background:rgba(122,108,255,.075);color:#b8adff;font-size:10px;font-weight:850;letter-spacing:.15em;text-transform:uppercase}
.ytd-pill:before{content:"";width:7px;height:7px;border-radius:50%;background:#8f82ff;box-shadow:0 0 14px #8f82ff}
.ytd-title{margin:19px 0 12px;color:#f8f8fb;font-size:clamp(38px,7vw,66px);font-weight:850;letter-spacing:-.065em;line-height:1.02}.ytd-title span{background:linear-gradient(105deg,#a99fff 10%,#73d7ff 92%);-webkit-background-clip:text;background-clip:text;color:transparent}
.ytd-sub{max-width:680px;margin:0 auto;color:#929aa8;font-size:14px;line-height:1.75}
.ytd-panel{position:relative;overflow:hidden;max-width:850px;margin:auto;padding:clamp(17px,4vw,30px);border:1px solid rgba(255,255,255,.105);border-radius:25px;background:linear-gradient(145deg,rgba(19,22,30,.96),rgba(10,12,17,.98));box-shadow:0 32px 90px rgba(0,0,0,.38),inset 0 1px rgba(255,255,255,.035)}
.ytd-panel:before{content:"";position:absolute;top:0;left:10%;right:10%;height:1px;background:linear-gradient(90deg,transparent,rgba(169,159,255,.55),rgba(115,215,255,.35),transparent)}
.ytd-inputrow{display:flex;gap:10px}.ytd-utility{min-height:54px;padding:0 15px;border:1px solid rgba(255,255,255,.12);border-radius:14px;background:#11151d;color:#c5cbd6;font-size:12px;font-weight:800;cursor:pointer}.ytd-utility:hover{border-color:rgba(169,159,255,.5);color:#fff}.ytd-input{min-width:0;flex:1;height:54px;padding:0 17px;border:1px solid rgba(255,255,255,.12);border-radius:14px;outline:none;background:#080a0f;color:#f6f7fa;font-size:14px;transition:border-color .2s,box-shadow .2s}.ytd-input::placeholder{color:#596270}.ytd-input:focus{border-color:rgba(169,159,255,.65);box-shadow:0 0 0 4px rgba(143,130,255,.1)}
.ytd-btn{min-height:54px;padding:0 22px;border:1px solid rgba(189,180,255,.28);border-radius:14px;background:linear-gradient(135deg,#9184ff,#6456d8);color:#fff;font-size:12px;font-weight:850;letter-spacing:.01em;cursor:pointer;box-shadow:0 8px 26px rgba(100,86,216,.22);transition:transform .2s,filter .2s}.ytd-btn:hover{filter:brightness(1.1);transform:translateY(-1px)}.ytd-btn:disabled{opacity:.55;cursor:wait;transform:none}
.ytd-hint{margin:11px 2px 0;color:#737d8c;font-size:11px;line-height:1.6}.ytd-error{display:none;margin-top:14px;padding:12px 14px;border:1px solid rgba(244,125,125,.2);border-radius:12px;background:rgba(244,125,125,.07);color:#ff9ca6;font-size:12px;line-height:1.5}.ytd-error.show{display:block}
.ytd-result{display:none;margin-top:24px;padding-top:22px;border-top:1px solid rgba(255,255,255,.08)}
.ytd-result-grid{display:grid;grid-template-columns:minmax(0,1.12fr) minmax(320px,.88fr);gap:18px;align-items:start}.ytd-preview-panel,.ytd-format-panel{min-width:0;padding:16px;border:1px solid rgba(255,255,255,.085);border-radius:18px;background:linear-gradient(145deg,rgba(17,20,28,.86),rgba(8,10,15,.92))}.ytd-videohead{display:block;margin:0}.ytd-thumb{width:100%;height:auto;aspect-ratio:16/9;object-fit:cover}.ytd-video-meta{padding:15px 2px 2px}.ytd-video-meta h3{font-size:16px}.ytd-video-meta .ytd-hint{margin-top:8px}.ytd-format-panel .ytd-tabs{margin-top:0}.ytd-format-panel .ytd-table-wrap{margin-top:12px}.ytd-format-panel .ytd-table th,.ytd-format-panel .ytd-table td{padding:12px 10px}.ytd-format-panel .ytd-status:not(:empty){padding:12px 13px;border:1px solid rgba(143,130,255,.16);border-radius:12px;background:rgba(143,130,255,.055)}.ytd-status:not(:empty):after{content:"";display:block;position:absolute;left:0;bottom:0;width:36%;height:2px;border-radius:2px;background:linear-gradient(90deg,#9184ff,#73d7ff);animation:ytdProgress 1.4s ease-in-out infinite alternate}.ytd-status{position:relative;overflow:hidden}
@keyframes ytdProgress{from{transform:translateX(-10%)}to{transform:translateX(190%)}}.ytd-result.show{display:block}
.ytd-videohead{display:flex;gap:16px;align-items:center;margin-bottom:20px}.ytd-thumb{display:block;width:190px;aspect-ratio:16/9;object-fit:cover;border:1px solid rgba(255,255,255,.12);border-radius:13px;background:#11151d;box-shadow:0 10px 28px #0005}.ytd-videohead h3{margin:0 0 7px;color:#f5f6fa;font-size:15px;line-height:1.45}.ytd-videohead p{margin:0;color:#929baa;font-size:12px}
.ytd-tabs{display:flex;gap:8px;margin:18px 0 13px}.ytd-tab{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:42px;padding:0 17px;border:1px solid rgba(255,255,255,.1);border-radius:12px;background:#10131a;color:#a1a9b6;font-size:12px;font-weight:800;cursor:pointer;transition:all .18s}.ytd-tab.active{border-color:rgba(159,140,255,.45);background:linear-gradient(135deg,rgba(143,130,255,.2),rgba(115,215,255,.07));color:#e4e0ff;box-shadow:inset 0 1px rgba(255,255,255,.05)}
.ytd-table-wrap{overflow:hidden;border:1px solid rgba(255,255,255,.09);border-radius:15px;background:rgba(7,9,14,.62)}.ytd-table{width:100%;border-collapse:collapse;min-width:0;color:#e8eaf0}.ytd-table th{padding:13px 16px;background:rgba(255,255,255,.035);color:#798393;font-size:10px;font-weight:850;letter-spacing:.12em;text-align:left}.ytd-table td{padding:13px 16px;border-top:1px solid rgba(255,255,255,.065);font-size:13px;font-weight:650}.ytd-table tbody tr{transition:background .16s}.ytd-table tbody tr:hover{background:rgba(143,130,255,.055)}.ytd-table td:nth-child(2){color:#929baa;font-size:12px;text-transform:uppercase;letter-spacing:.08em}
.ytd-download{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-width:116px;min-height:38px;padding:0 14px;border:1px solid rgba(103,217,144,.24);border-radius:10px;background:linear-gradient(135deg,#168c65,#0c6c50);color:#f2fff8;font-size:11px;font-weight:850;cursor:pointer;box-shadow:0 5px 16px rgba(12,108,80,.16);transition:transform .18s,filter .18s}.ytd-download:hover{filter:brightness(1.12);transform:translateY(-1px)}.ytd-download:disabled{opacity:.45;cursor:wait;transform:none}
.ytd-empty{padding:18px;color:#8993a2;font-size:12px}.ytd-status{display:flex;align-items:center;gap:9px;min-height:22px;margin:12px 2px 0;color:#929baa;font-size:11px}.ytd-status:empty{display:none}.ytd-status:not(:empty):before{content:"";width:7px;height:7px;flex:0 0 7px;border-radius:50%;background:#8f82ff;box-shadow:0 0 12px rgba(143,130,255,.8);animation:ytdPulse 1.3s infinite}
.ytd-info{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:18px}.ytd-info article{min-height:96px;padding:16px;border:1px solid rgba(255,255,255,.075);border-radius:15px;background:linear-gradient(145deg,rgba(17,20,28,.82),rgba(10,12,17,.82))}.ytd-info strong{display:flex;align-items:center;gap:8px;margin-bottom:7px;color:#e8eaf1;font-size:12px}.ytd-info strong:before{content:"";width:6px;height:6px;border-radius:50%;background:#9f8cff;box-shadow:0 0 10px #9f8cff}.ytd-info span{color:#818b9a;font-size:11px;line-height:1.65}.ytd-note{max-width:850px;margin:17px auto 0;color:#687282;font-size:10px;line-height:1.7;text-align:center}
@keyframes ytdPulse{50%{opacity:.35;transform:scale(.8)}}
@media(max-width:760px){.ytd-result-grid{grid-template-columns:1fr}.ytd-preview-panel,.ytd-format-panel{padding:13px}}
@media(max-width:620px){.ytd-page{padding:30px 13px 65px}.ytd-inputrow{flex-direction:column}.ytd-btn{width:100%}.ytd-videohead{align-items:flex-start}.ytd-thumb{width:100%;flex:0 0 auto}.ytd-videohead h3{font-size:14px}.ytd-info{grid-template-columns:1fr;gap:9px}.ytd-info article{min-height:0;padding:13px}.ytd-tabs{display:grid;grid-template-columns:1fr 1fr}.ytd-tab{padding:0 9px}.ytd-table th,.ytd-table td{padding:11px 9px;font-size:11px}.ytd-download{min-width:94px;min-height:36px;padding:0 9px;font-size:10px}.ytd-table td:nth-child(2){font-size:10px}}
@media(prefers-reduced-motion:reduce){.ytd-status:not(:empty):before{animation:none}.ytd-btn,.ytd-download{transition:none}}

.ytd-faq{display:grid;gap:9px}.ytd-faq details{padding:14px 16px;border:1px solid rgba(255,255,255,.09);border-radius:13px;background:rgba(7,9,14,.55)}.ytd-faq summary{color:#e8eaf1;font-size:13px;font-weight:750;cursor:pointer}.ytd-faq p{margin:10px 0 0;color:#929baa;font-size:12px;line-height:1.7}
/* Final YTDown-style result layout overrides */
.ytd-result-grid{display:grid;grid-template-columns:minmax(0,1.12fr) minmax(320px,.88fr);gap:18px;align-items:start}.ytd-preview-panel,.ytd-format-panel{min-width:0;padding:16px;border:1px solid rgba(255,255,255,.085);border-radius:18px;background:linear-gradient(145deg,rgba(17,20,28,.86),rgba(8,10,15,.92))}.ytd-preview-panel .ytd-videohead{display:block;margin:0}.ytd-preview-panel .ytd-thumb{display:block;width:100%;height:auto;aspect-ratio:16/9;object-fit:cover}.ytd-video-meta{padding:15px 2px 2px}.ytd-video-meta h3{font-size:16px}.ytd-format-panel .ytd-tabs{margin:0 0 13px}.ytd-format-panel .ytd-table-wrap{margin-top:12px}.ytd-format-panel .ytd-table th,.ytd-format-panel .ytd-table td{padding:12px 10px}.ytd-format-panel .ytd-status:not(:empty){padding:12px 13px;border:1px solid rgba(143,130,255,.16);border-radius:12px;background:rgba(143,130,255,.055)}
@media(max-width:760px){.ytd-result-grid{grid-template-columns:1fr}.ytd-preview-panel,.ytd-format-panel{padding:13px}}

/* Screenshot-matched YTDown mobile result card */
.ytd-page.yd-dark{background:#111217;color:#f5f5f7}.ytd-page.yd-dark .ytd-panel,.ytd-page.yd-dark .yd-howto{background:#202127;color:#f5f5f7}.ytd-page.yd-dark .yd-title{color:#f5f5f7}.ytd-page.yd-dark .yd-download-card,.ytd-page.yd-dark .yd-another,.ytd-page.yd-dark .yd-select-wrap select,.ytd-page.yd-dark .yd-stat{background:#30313a;color:#f5f5f7}

.yd-sitebar{height:72px;padding:0 max(22px,calc((100vw - 760px)/2));display:flex;align-items:center;justify-content:space-between;background:#e7e6eb;color:#53525a}.yd-brand{display:flex;align-items:center;gap:12px;font-size:22px;color:#25252b}.yd-brand-icon{width:45px;height:33px;border-radius:8px;background:#ef202b;color:white;display:grid;place-items:center;font-size:21px}.yd-tools{display:flex;align-items:center;gap:19px;font-size:17px;font-weight:800}.yd-tools button{border:0;background:none;color:inherit;font-size:18px;font-weight:800}.yd-hero{display:none}@media(max-width:520px){.yd-sitebar{height:72px;padding:0 22px}.yd-tools{gap:14px;font-size:15px}.yd-tools button{font-size:15px}}

.ytd-page{background:#f1f0f5;color:#24242a;padding:24px 13px 70px}.ytd-wrap{max-width:760px}.ytd-hero{text-align:left;margin:0 auto 18px}.ytd-hero .ytd-pill{display:none}.ytd-title{font-size:clamp(30px,7vw,44px);color:#24242a;letter-spacing:-.04em}.ytd-title span{background:none;color:inherit}.ytd-sub{color:#62616b}.ytd-panel{background:#fff;color:#25252b;border:0;border-radius:24px;box-shadow:0 8px 30px #20202a0c}.ytd-panel:before{display:none}.ytd-result{border:0;padding:0;margin:0}.yd-card{padding:18px 0 0}.yd-review{display:flex;gap:12px;align-items:center;color:#67666f;font-size:14px;font-weight:600;line-height:1.45;margin:0 0 28px}.yd-video-image{position:relative;overflow:hidden;border-radius:15px;background:#e5e5e8;aspect-ratio:16/9}.yd-video-image img{width:100%;height:100%;object-fit:cover;display:block}.yd-play{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:54px;height:54px;border-radius:50%;display:grid;place-items:center;color:#fff;background:#0008;font-size:21px}.yd-title{margin:17px 0 18px;color:#17171b;font-size:25px;line-height:1.2;font-weight:850;letter-spacing:-.035em}.yd-stats{display:flex;gap:9px;flex-wrap:wrap;margin:0 0 30px}.yd-stat{display:inline-flex;align-items:center;gap:7px;padding:8px 13px;border-radius:30px;background:#ededf0;color:#55545c;font-size:13px;font-weight:750}.yd-select-wrap{display:block;position:relative;margin-bottom:13px}.yd-select-wrap select{appearance:none;width:100%;height:78px;padding:0 52px 0 36px;border:0;border-radius:20px;background:#e7e6eb;color:#25252b;font-size:23px;font-weight:800}.yd-chevron{position:absolute;right:24px;top:50%;transform:translateY(-50%);font-size:24px;pointer-events:none}.yd-download-card{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:17px 25px;border-radius:19px;background:#efeff3}.yd-download-info{display:flex;flex-direction:column;gap:8px;color:#595861;font-size:17px}.yd-process{align-self:flex-start;padding:7px 13px;border-radius:20px;background:#e1e1e7;color:#595963;font-weight:800;font-size:14px}.yd-download{min-width:0;min-height:52px;padding:0 24px;border:0;border-radius:30px;background:#17171b;color:white;font-size:16px;font-weight:800;white-space:nowrap}.yd-another{width:100%;margin-top:31px;min-height:72px;border:0;border-radius:20px;background:#efeff3;color:#17171b;font-size:20px;font-weight:800}.yd-sr{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0)}.yd-howto{margin:24px auto 0;padding:24px;border-radius:24px;background:#fff;color:#202027}.yd-howto h2{margin:0 0 18px;font-size:23px}.yd-howto p{color:#55545c;line-height:1.65}.ytd-info article{background:#fff;border-color:#e6e5eb}.ytd-info strong{color:#24242a}.ytd-info span{color:#666}.ytd-faq details{background:#f8f8fa;border-color:#e6e5eb}.ytd-faq summary{color:#25252b}.ytd-faq p{color:#666}
@media(max-width:520px){.ytd-page{padding:16px 13px 60px}.ytd-panel{padding:16px;border-radius:23px}.yd-card{padding-top:2px}.yd-title{font-size:24px}.yd-select-wrap select{height:78px;font-size:21px;padding-left:24px}.yd-download-card{padding:15px 17px}.yd-download{padding:0 17px;font-size:15px}.yd-another{min-height:70px;font-size:19px}.yd-stats{gap:7px}.yd-stat{padding:8px 11px}}

</style>
@endpush

@section('content')
<div class="yd-sitebar"><div class="yd-brand"><span class="yd-brand-icon">⬇</span><strong>YTDown</strong></div><div class="yd-tools"><span>◎ EN</span><button type="button" id="yd-theme" aria-label="Thema wijzigen">◐</button><button type="button" id="yd-menu">☰ Menu</button></div></div>
<main class="ytd-page"><div class="ytd-wrap">
<header class="ytd-hero"><div class="ytd-pill">MASHAL STUDIO · VIDEO TOOLS</div><h1 class="ytd-title">YouTube Video Downloader</h1><p class="ytd-sub">Free. No signup. Download now.</p></header>
<section class="ytd-panel" aria-label="YouTube downloader"><h2 style="margin:0 0 16px;color:#f5f6fa;font-size:clamp(21px,3vw,28px)">Download a YouTube video</h2>
<form id="ytd-form">@csrf<div class="ytd-inputrow"><label for="ytd-url" style="display:block;position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0)">YouTube link</label><input class="ytd-input" id="ytd-url" type="url" placeholder="YouTube link" autocomplete="url" required><button class="ytd-utility" id="ytd-paste" type="button">Plakken</button><button class="ytd-utility" id="ytd-clear" type="button">Wissen</button><button class="ytd-btn" id="ytd-submit" type="submit">Download</button></div><p class="ytd-hint">Videos, Shorts and YouTube Music links work. Paste the link, then press Download.</p><p class="ytd-hint" style="text-align:center;margin-top:14px">How did YTDown work for you? Leave a review on Trustpilot.</p></form>
<p class="ytd-error" id="ytd-error" role="alert"></p>
<div id="ytd-entry-stage"><p class="ytd-hint" style="margin:15px 2px 0;text-align:center">Free · No signup · Download now</p></div>
<div class="ytd-result" id="ytd-result">
 <div class="yd-card">
  <div class="yd-review">♡ <span>How did YTDown work for you? <u>Leave a review on Trustpilot</u></span></div>
  <div class="yd-video-image"><img id="ytd-preview-image" alt="Videominiatuur" loading="lazy"><span class="yd-play">▶</span></div>
  <h2 class="yd-title" id="ytd-video-title"></h2>
  <div class="yd-stats" id="ytd-stats"></div>
  <label class="yd-select-wrap"><span class="yd-sr">Kies videokwaliteit</span><select id="ytd-quality-select" aria-label="Videokwaliteit"></select><span class="yd-chevron">⌄</span></label>
  <div class="yd-download-card"><div class="yd-download-info"><span class="yd-process" id="ytd-process">Needs processing</span><span id="ytd-format-description">MP4 · HD</span></div><button class="yd-download" id="ytd-card-download" type="button">↓ Download</button></div>
  <p class="ytd-status" id="ytd-status" aria-live="polite"></p>
  <button type="button" class="yd-another" id="ytd-another">↗　Download another video</button>
 </div>
</div>
</section>
<section class="ytd-info"><article><strong>1. Link plakken</strong><span>Gebruik een YouTube- of Shorts-link.</span></article><article><strong>2. Kwaliteit ophalen</strong><span>De backend leest de beschikbare streamformaten uit.</span></article><article><strong>3. Downloaden</strong><span>Kies een echte beschikbare resolutie of MP3-audio.</span></article></section>

<section class="ytd-panel" style="margin:22px auto 0" aria-label="Eigen videobestand converteren">
 <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px"><span class="ytd-pill">MASHAL STUDIO · LOCAL FILE</span></div>
 <h2 style="margin:12px 0 8px;color:#f5f6fa;font-size:clamp(21px,4vw,29px);letter-spacing:-.035em">Download je eigen videobestand</h2>
 <p class="ytd-sub" style="text-align:left;margin:0 0 18px">Upload een video die van jou is of waarvoor je toestemming hebt. De bronresolutie wordt automatisch gelezen; de hoogste passende MP4-kwaliteit wordt voorgeselecteerd.</p>
 <div class="ytd-inputrow" style="align-items:center"><input class="ytd-input" id="ytd-file" type="file" accept="video/mp4,video/quicktime,video/webm,video/x-matroska,video/x-msvideo,audio/*"><button class="ytd-btn" id="ytd-file-analyze" type="button">Bestand analyseren →</button></div>
 <p class="ytd-hint">Maximaal 500 MB. De browser uploadt het bestand veilig naar je SmartDesk-server voor verwerking.</p>
 <div class="ytd-error" id="ytd-file-error" role="alert"></div>
 <div id="ytd-file-result" style="display:none;margin-top:18px;padding-top:18px;border-top:1px solid rgba(255,255,255,.08)">
  <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:14px"><div><strong id="ytd-file-name" style="display:block;color:#f5f6fa;font-size:14px;overflow-wrap:anywhere"></strong><span id="ytd-file-meta" class="ytd-hint"></span></div><span id="ytd-file-source" class="ytd-pill"></span></div>
  <div class="ytd-inputrow"><select id="ytd-file-quality" class="ytd-input" aria-label="MP4-resolutie"></select><button class="ytd-btn" id="ytd-file-download" type="button">MP4 downloaden ↓</button></div>
  <p class="ytd-status" id="ytd-file-status" aria-live="polite"></p>
 </div>
</section>
<section class="ytd-panel" style="margin:22px auto 0" aria-label="Downloader informatie">
 <div class="ytd-pill">YTDOWN-STYLE · NOTICES</div>
 <h2 style="margin:13px 0 9px;color:#f5f6fa;font-size:clamp(21px,4vw,29px)">Best YouTube Downloader</h2>
 <p class="ytd-sub" style="text-align:left;margin:0">Mashal Studio biedt een eenvoudige downloader-interface voor video’s en audio. Plak een link, controleer de beschikbare formaten en kies zelf de kwaliteit. De werkelijke opties zijn afhankelijk van de bron en de technische beschikbaarheid.</p>
 <div class="ytd-info" style="margin-top:18px">
  <article><strong>Fast and simple</strong><span>Geen account of aparte software nodig om de link te analyseren.</span></article>
  <article><strong>Android and iOS</strong><span>De pagina past zich aan mobiele telefoons, tablets en desktop aan.</span></article>
  <article><strong>HD video options</strong><span>Beschikbare resoluties worden uit de broninformatie gelezen; niet iedere video biedt dezelfde kwaliteit.</span></article>
 </div>
 <h3 style="margin:25px 0 12px;color:#f5f6fa;font-size:18px">Frequently Asked Questions</h3>
 <div class="ytd-faq">
  <details><summary>Where are downloads saved?</summary><p>Downloads worden doorgaans opgeslagen in de standaard Downloads-map van je browser of apparaat. Op iPhone kun je ze terugvinden in Bestanden, afhankelijk van je browserinstellingen.</p></details>
  <details><summary>How do I download audio?</summary><p>Plak de link, druk op Download en kies daarna Audio (MP3), als de bron een geschikte audiostream aanbiedt.</p></details>
  <details><summary>How long does processing take?</summary><p>Dat hangt af van de lengte, kwaliteit, bestandsgrootte en de bereikbaarheid van de bron. De voortgang verschijnt tijdens de verwerking.</p></details>
  <details><summary>Why is a video or quality unavailable?</summary><p>Niet elke video biedt dezelfde formaten. Leeftijdsbeperkingen, privacy-instellingen, regiobeperkingen, auteursrechtelijke beperkingen of weigering door de bron kunnen downloads verhinderen.</p></details>
  <details><summary>Can I use this on iPhone or Android?</summary><p>Ja, de interface is responsive. De browser bepaalt hoe het bestand na een succesvolle download wordt opgeslagen.</p></details>
 </div>
 <h3 style="margin:25px 0 10px;color:#f5f6fa;font-size:18px">Notices</h3>
 <p class="ytd-hint" style="margin:0">Mashal Studio is geen onderdeel van YouTube en wordt niet door YouTube onderschreven. Download alleen materiaal dat van jou is of waarvoor je toestemming hebt. Auteursrechtelijk beschermde content waarvoor je geen toestemming hebt, is niet bedoeld om via deze tool te downloaden. De gebruiker is verantwoordelijk voor naleving van toepasselijke wetgeving en platformvoorwaarden.</p>
</section>
<section class="yd-howto"><h2>How to download a YouTube video</h2><p>1. Open YouTube and copy the link of a public video.</p><p>2. Paste the link in the downloader above and press Download.</p><p>3. Choose an available resolution and press Download to process your file.</p></section>
<p class="ytd-note">Gebruik downloads alleen voor content die van jou is of waarvoor je toestemming hebt. Privévideo’s, DRM en accountbeperkingen worden niet omzeild. Beschikbaarheid kan veranderen door YouTube.</p>
</div></main>
@endsection

@push('scripts')
<script>
(() => {
 const form=document.getElementById('ytd-form'), input=document.getElementById('ytd-url'), submit=document.getElementById('ytd-submit');
 const error=document.getElementById('ytd-error'), result=document.getElementById('ytd-result'), entryStage=document.getElementById('ytd-entry-stage'), rows=document.getElementById('ytd-rows'), empty=document.getElementById('ytd-empty'), status=document.getElementById('ytd-status');
 const csrf=form.querySelector('input[name="_token"]').value;
 const pasteButton=document.getElementById('ytd-paste'),clearButton=document.getElementById('ytd-clear'),anotherButton=document.getElementById('ytd-another');
 pasteButton.addEventListener('click',async()=>{try{const value=await navigator.clipboard.readText();if(value){input.value=value;input.focus();}}catch(_){showError('Plakken is niet beschikbaar. Houd het invoerveld ingedrukt en kies Plak.');}});
 clearButton.addEventListener('click',()=>{input.value='';input.focus();error.classList.remove('show');result.classList.remove('show');entryStage.style.display='block';status.textContent='';});
 anotherButton.addEventListener('click',()=>{input.value='';token=null;qualities=[];result.classList.remove('show');entryStage.style.display='block';form.style.display='block';document.querySelector('.ytd-hint').style.display='block';error.classList.remove('show');status.textContent='';submit.disabled=false;submit.textContent='Download';input.focus();window.scrollTo({top:0,behavior:'smooth'});});
 const bestWrap=document.getElementById('ytd-best-wrap'), bestButton=document.getElementById('ytd-best-button'), bestLabel=document.getElementById('ytd-best-label');

 const fileInput=document.getElementById('ytd-file'), fileAnalyze=document.getElementById('ytd-file-analyze');
 const fileError=document.getElementById('ytd-file-error'), fileResult=document.getElementById('ytd-file-result');
 const fileQuality=document.getElementById('ytd-file-quality'), fileDownload=document.getElementById('ytd-file-download');
 const fileStatus=document.getElementById('ytd-file-status');
 let uploadToken=null, uploadBusy=false;
 const fileFail=(message)=>{fileError.textContent=message;fileError.classList.add('show');};
 fileAnalyze.addEventListener('click',async()=>{
  fileError.classList.remove('show');fileResult.style.display='none';
  const file=fileInput.files&&fileInput.files[0];
  if(!file){fileFail('Kies eerst een video- of audiobestand.');return;}
  if(file.size>500*1024*1024){fileFail('Dit bestand is groter dan 500 MB.');return;}
  fileAnalyze.disabled=true;fileAnalyze.textContent='Analyseren…';
  try{
   const body=new FormData();body.append('_token',csrf);body.append('media',file);
   const response=await fetch(@json(route('youtube-downloader.upload')),{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf},body});
   const data=await response.json();if(!response.ok)throw new Error(data.message||'Bestand kon niet worden geanalyseerd.');
   uploadToken=data.token;
   document.getElementById('ytd-file-name').textContent=data.name||file.name;
   document.getElementById('ytd-file-meta').textContent=data.width&&data.height?data.width+' × '+data.height+' px':'Audiobestand';
   document.getElementById('ytd-file-source').textContent=data.width&&data.height?'BRON '+Math.min(data.width,data.height)+'p':'AUDIO';
   fileQuality.innerHTML='';
   (data.qualities||[]).slice().sort((a,b)=>b-a).forEach((q,index)=>{
    const option=document.createElement('option');option.value=String(q);option.textContent=q+'p MP4'+(index===0?' · hoogste bronkwaliteit':'');fileQuality.appendChild(option);
   });
   if(!(data.qualities||[]).length)throw new Error('Dit bestand bevat geen videostream met een ondersteunde resolutie.');
   fileQuality.value=String(Math.max(...data.qualities));
   fileResult.style.display='block';fileStatus.textContent='Bron gecontroleerd. Hoogste beschikbare resolutie automatisch geselecteerd.';
  }catch(e){fileFail(e.message||'Analyseren mislukt.');}
  finally{fileAnalyze.disabled=false;fileAnalyze.textContent='Bestand analyseren →';}
 });
 fileDownload.addEventListener('click',async()=>{
  if(!uploadToken||uploadBusy)return;uploadBusy=true;fileDownload.disabled=true;fileDownload.textContent='Converteren…';fileError.classList.remove('show');
  try{
   const body=new FormData();body.append('_token',csrf);body.append('token',uploadToken);body.append('format','mp4');body.append('quality',fileQuality.value);
   fileStatus.textContent='MP4 wordt opgebouwd en gecontroleerd…';
   const response=await fetch(@json(route('youtube-downloader.convert')),{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf},body});
   if(!response.ok){let data={};try{data=await response.json();}catch(_){}throw new Error(data.message||'MP4-conversie mislukt.');}
   const blob=await response.blob();if(!blob.size)throw new Error('De server heeft een leeg bestand teruggegeven.');
   const disposition=response.headers.get('Content-Disposition')||'';const match=disposition.match(/filename="?([^";]+)"?/i);
   const name=match?match[1]:'mashal-studio-'+fileQuality.value+'p.mp4';
   const objectUrl=URL.createObjectURL(blob),link=document.createElement('a');link.href=objectUrl;link.download=name;document.body.appendChild(link);link.click();link.remove();setTimeout(()=>URL.revokeObjectURL(objectUrl),60000);
   fileStatus.textContent='Klaar. MP4-bestand is gegenereerd en de download is gestart.';
  }catch(e){fileFail(e.message||'Download mislukt.');fileStatus.textContent='';}
  finally{uploadBusy=false;fileDownload.disabled=false;fileDownload.textContent='MP4 downloaden ↓';}
 });

 let token=null, qualities=[], hasAudio=false, mode='video', busy=false, currentDuration=0, currentViews=null, currentLikes=null, currentComments=null, currentSizes={};
 const showError=(message)=>{error.textContent=message;error.classList.add('show');};
 const duration=(s)=>{s=Number(s||0);if(!s)return '';const h=Math.floor(s/3600),m=Math.floor((s%3600)/60),sec=s%60;return [h,m,sec].map((n,i)=>i===0&&h===0?null:String(n).padStart(2,'0')).filter(Boolean).join(':');};
 document.getElementById('yd-theme').addEventListener('click',()=>document.querySelector('.ytd-page').classList.toggle('yd-dark'));
 document.getElementById('yd-menu').addEventListener('click',()=>document.querySelector('.yd-howto')?.scrollIntoView({behavior:'smooth'}));
 const qualitySelect=document.getElementById('ytd-quality-select'), cardDownload=document.getElementById('ytd-card-download'), formatDescription=document.getElementById('ytd-format-description');
 const render=()=>{
  qualitySelect.innerHTML='';
  qualities.slice().sort((a,b)=>b-a).forEach(q=>{const option=document.createElement('option');option.value=String(q);const size=currentSizes[q];option.textContent=q+'p · MP4'+(q>=2160?' · 4K':q>=1440?' · 2K':q>=1080?' · FHD':q>=720?' · HD':'')+(size&&size.bytes?' · '+(size.approx?'~':'')+(Number(size.bytes)/1000000).toFixed(2)+' MB':'');qualitySelect.appendChild(option);});
  if(hasAudio){const audio=document.createElement('option');audio.value='audio';audio.textContent='Audio · MP3';qualitySelect.appendChild(audio);}
  updateSelected();
  const stats=document.getElementById('ytd-stats');stats.innerHTML='';
  [[ '◷',duration(currentDuration) ],[ '◉',formatCount(currentViews) ],[ '♡',formatCount(currentLikes) ],[ '◯',formatCount(currentComments) ]].forEach(([icon,value])=>{if(!value)return;const item=document.createElement('span');item.className='yd-stat';item.textContent=icon+' '+value;stats.appendChild(item);});
 };
 function formatCount(value){const n=Number(value||0);if(!n)return '';if(n>=1000000)return (n/1000000).toFixed(1).replace('.0','')+'M';if(n>=1000)return (n/1000).toFixed(1).replace('.0','')+'K';return String(n);}
 function updateSelected(){
  const value=qualitySelect.value;
  if(value==='audio'){formatDescription.textContent='MP3 · Audio';cardDownload.onclick=()=>startDownload({label:'Audio',format:'mp3',quality:null},cardDownload);return;}
  const q=Number(value||qualities[0]||0),label=q>=2160?'4K':q>=1440?'2K':q>=1080?'FHD':q>=720?'HD':q+'p';
  const size=currentSizes[q];formatDescription.textContent='MP4 · '+label+(size&&size.bytes?' · '+(size.approx?'~':'')+(Number(size.bytes)/1000000).toFixed(2)+' MB':'');
  cardDownload.onclick=()=>startDownload({label:q+'p',format:'mp4',quality:q},cardDownload);
 }
 qualitySelect.addEventListener('change',updateSelected);

 document.querySelectorAll('.ytd-tab').forEach(t=>t.addEventListener('click',()=>{mode=t.dataset.mode;render();}));
 async function startDownload(item,button){
  if(busy||!token)return;busy=true;cardDownload.disabled=true;button.textContent='Starten…';status.textContent='Download wordt voorbereid…';document.getElementById('ytd-process').textContent='Needs processing';
  try{
   const body=new FormData();body.append('_token',csrf);body.append('token',token);body.append('format',item.format);if(item.quality)body.append('quality',String(item.quality));
   const response=await fetch(@json(route('youtube-downloader.prepare')),{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf},body});
   const data=await response.json();if(!response.ok)throw new Error(data.message||'Download kon niet starten.');
   status.textContent='Video wordt verwerkt. Dit kan even duren…';document.getElementById('ytd-process').textContent='Processing…';
   let finished=false;
   for(let i=0;i<180;i++){
    await new Promise(resolve=>setTimeout(resolve,2000));
    const poll=await fetch(data.status_url,{headers:{'Accept':'application/json'}});
    const state=await poll.json();
    if(!poll.ok)throw new Error(state.message||'Downloadstatus kon niet worden opgehaald.');
    if(state.status==='failed')throw new Error(state.message||'Download mislukt.');
    if(state.status==='ready'&&state.download_url){window.location.assign(state.download_url);status.textContent='Bestand klaar — de download wordt geopend.';document.getElementById('ytd-process').textContent='Ready';finished=true;break;}
   }
   if(!finished)throw new Error('De download duurt te lang. Probeer het opnieuw.');
  }catch(e){status.textContent='';document.getElementById('ytd-process').textContent='Needs processing';showError(e.message||'Er ging iets mis.');}
  finally{busy=false;cardDownload.disabled=false;button.textContent='↓ Download';}
 }
 form.addEventListener('submit',async e=>{
  e.preventDefault();error.classList.remove('show');result.classList.remove('show');status.textContent='YouTube-formaten ophalen…';submit.disabled=true;submit.textContent='Analyseren…';token=null;
  try{
   const response=await fetch(@json(route('youtube-downloader.analyze')),{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({url:input.value.trim()})});
   const data=await response.json();if(!response.ok)throw new Error(data.message||'De YouTube-link kon niet worden geanalyseerd.');
   token=data.token;qualities=data.qualities||[];hasAudio=!!data.has_audio;currentDuration=Number(data.duration||0);currentViews=data.view_count;currentLikes=data.like_count;currentComments=data.comment_count;currentSizes=data.quality_sizes||{};
   document.getElementById('ytd-preview-image').src=data.thumbnail||'';document.getElementById('ytd-video-title').textContent=data.title||'YouTube-video';
   mode='video';render();entryStage.style.display='none';form.style.display='none';document.querySelector('.ytd-hint').style.display='none';result.classList.add('show');status.textContent=qualities.length+' beschikbare videokwaliteiten gevonden'+(hasAudio?' · audio beschikbaar':'');result.scrollIntoView({behavior:'smooth',block:'start'});
  }catch(e){status.textContent='';showError(e.message||'Er ging iets mis.');}
  finally{submit.disabled=false;submit.textContent='Download';}
 });
})();
</script>
@endpush
