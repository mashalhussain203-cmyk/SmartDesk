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
.ytd-result{display:none;margin-top:24px;padding-top:22px;border-top:1px solid rgba(255,255,255,.08)}.ytd-result.show{display:block}
.ytd-videohead{display:flex;gap:16px;align-items:center;margin-bottom:20px}.ytd-thumb{display:block;width:190px;aspect-ratio:16/9;object-fit:cover;border:1px solid rgba(255,255,255,.12);border-radius:13px;background:#11151d;box-shadow:0 10px 28px #0005}.ytd-videohead h3{margin:0 0 7px;color:#f5f6fa;font-size:15px;line-height:1.45}.ytd-videohead p{margin:0;color:#929baa;font-size:12px}
.ytd-tabs{display:flex;gap:8px;margin:18px 0 13px}.ytd-tab{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:42px;padding:0 17px;border:1px solid rgba(255,255,255,.1);border-radius:12px;background:#10131a;color:#a1a9b6;font-size:12px;font-weight:800;cursor:pointer;transition:all .18s}.ytd-tab.active{border-color:rgba(159,140,255,.45);background:linear-gradient(135deg,rgba(143,130,255,.2),rgba(115,215,255,.07));color:#e4e0ff;box-shadow:inset 0 1px rgba(255,255,255,.05)}
.ytd-table-wrap{overflow:hidden;border:1px solid rgba(255,255,255,.09);border-radius:15px;background:rgba(7,9,14,.62)}.ytd-table{width:100%;border-collapse:collapse;min-width:0;color:#e8eaf0}.ytd-table th{padding:13px 16px;background:rgba(255,255,255,.035);color:#798393;font-size:10px;font-weight:850;letter-spacing:.12em;text-align:left}.ytd-table td{padding:13px 16px;border-top:1px solid rgba(255,255,255,.065);font-size:13px;font-weight:650}.ytd-table tbody tr{transition:background .16s}.ytd-table tbody tr:hover{background:rgba(143,130,255,.055)}.ytd-table td:nth-child(2){color:#929baa;font-size:12px;text-transform:uppercase;letter-spacing:.08em}
.ytd-download{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-width:116px;min-height:38px;padding:0 14px;border:1px solid rgba(103,217,144,.24);border-radius:10px;background:linear-gradient(135deg,#168c65,#0c6c50);color:#f2fff8;font-size:11px;font-weight:850;cursor:pointer;box-shadow:0 5px 16px rgba(12,108,80,.16);transition:transform .18s,filter .18s}.ytd-download:hover{filter:brightness(1.12);transform:translateY(-1px)}.ytd-download:disabled{opacity:.45;cursor:wait;transform:none}
.ytd-empty{padding:18px;color:#8993a2;font-size:12px}.ytd-status{display:flex;align-items:center;gap:9px;min-height:22px;margin:12px 2px 0;color:#929baa;font-size:11px}.ytd-status:empty{display:none}.ytd-status:not(:empty):before{content:"";width:7px;height:7px;flex:0 0 7px;border-radius:50%;background:#8f82ff;box-shadow:0 0 12px rgba(143,130,255,.8);animation:ytdPulse 1.3s infinite}
.ytd-info{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:18px}.ytd-info article{min-height:96px;padding:16px;border:1px solid rgba(255,255,255,.075);border-radius:15px;background:linear-gradient(145deg,rgba(17,20,28,.82),rgba(10,12,17,.82))}.ytd-info strong{display:flex;align-items:center;gap:8px;margin-bottom:7px;color:#e8eaf1;font-size:12px}.ytd-info strong:before{content:"";width:6px;height:6px;border-radius:50%;background:#9f8cff;box-shadow:0 0 10px #9f8cff}.ytd-info span{color:#818b9a;font-size:11px;line-height:1.65}.ytd-note{max-width:850px;margin:17px auto 0;color:#687282;font-size:10px;line-height:1.7;text-align:center}
@keyframes ytdPulse{50%{opacity:.35;transform:scale(.8)}}
@media(max-width:620px){.ytd-page{padding:30px 13px 65px}.ytd-inputrow{flex-direction:column}.ytd-btn{width:100%}.ytd-videohead{align-items:flex-start}.ytd-thumb{width:116px;flex:0 0 116px}.ytd-videohead h3{font-size:13px}.ytd-info{grid-template-columns:1fr;gap:9px}.ytd-info article{min-height:0;padding:13px}.ytd-tabs{display:grid;grid-template-columns:1fr 1fr}.ytd-tab{padding:0 9px}.ytd-table th,.ytd-table td{padding:11px 9px;font-size:11px}.ytd-download{min-width:94px;min-height:36px;padding:0 9px;font-size:10px}.ytd-table td:nth-child(2){font-size:10px}}
@media(prefers-reduced-motion:reduce){.ytd-status:not(:empty):before{animation:none}.ytd-btn,.ytd-download{transition:none}}
</style>
@endpush

@section('content')
<main class="ytd-page"><div class="ytd-wrap">
<header class="ytd-hero"><div class="ytd-pill">● MASHAL STUDIO · VIDEO TOOLS</div><h1 class="ytd-title">YouTube <span>Downloader</span></h1><p class="ytd-sub">Plak een openbare YouTube-link. Mashal Studio controleert de werkelijk beschikbare video- en audioformaten en toont alleen kwaliteiten die YouTube voor die video teruggeeft.</p></header>
<section class="ytd-panel" aria-label="YouTube downloader">
<form id="ytd-form">@csrf<div class="ytd-inputrow"><input class="ytd-input" id="ytd-url" type="url" placeholder="Plak je YouTube-videolink hier" autocomplete="url" required><button class="ytd-utility" id="ytd-paste" type="button">Plakken</button><button class="ytd-utility" id="ytd-clear" type="button">Wissen</button><button class="ytd-btn" id="ytd-submit" type="submit">Download</button></div><p class="ytd-hint">Plak een YouTube-video- of Shorts-link en druk op Download. Daarna verschijnen de beschikbare MP4-kwaliteiten.</p></form>
<p class="ytd-error" id="ytd-error" role="alert"></p>
<div id="ytd-entry-stage"><p class="ytd-hint" style="margin:15px 2px 0;text-align:center">Gratis · Geen account nodig · MP4-video en MP3-audio</p></div>
<div class="ytd-result" id="ytd-result">
<div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:17px"><span class="ytd-pill">VIDEO GEVONDEN · KIES FORMAAT</span><button type="button" class="ytd-utility" id="ytd-another">← Andere URL</button></div>
<div class="ytd-videohead"><iframe class="ytd-thumb" id="ytd-preview" title="YouTube-videovoorbeeld" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe><div><h3 id="ytd-video-title"></h3><p id="ytd-author"></p><p class="ytd-hint" id="ytd-duration"></p></div></div>
<div class="ytd-tabs"><button class="ytd-tab active" type="button" data-mode="video">▣ Video (MP4)</button><button class="ytd-tab" type="button" data-mode="audio">♫ Audio (MP3)</button></div>
<div id="ytd-best-wrap" style="display:none;margin:0 0 13px;padding:14px 15px;border:1px solid rgba(103,217,144,.22);border-radius:14px;background:linear-gradient(110deg,rgba(18,111,79,.17),rgba(143,130,255,.07));display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap"><div><strong style="display:block;color:#eafff4;font-size:13px" id="ytd-best-label">Hoogste beschikbare kwaliteit</strong><span style="display:block;margin-top:4px;color:#8fa99c;font-size:11px">Automatisch geselecteerd op basis van de bronvideo</span></div><button class="ytd-download" id="ytd-best-button" type="button">↓ Beste kwaliteit</button></div>
<div class="ytd-table-wrap"><table class="ytd-table"><thead id="ytd-head"><tr><th>FILE TYPE</th><th>FORMAT</th><th>ACTION</th></tr></thead><tbody id="ytd-rows"></tbody></table><div class="ytd-empty" id="ytd-empty" hidden>Geen downloadformaten beschikbaar.</div></div>
<p class="ytd-hint" id="ytd-status" aria-live="polite"></p>
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
 bestWrap.style.display='none';
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

 let token=null, qualities=[], hasAudio=false, mode='video', busy=false;
 const showError=(message)=>{error.textContent=message;error.classList.add('show');};
 const duration=(s)=>{s=Number(s||0);if(!s)return '';const h=Math.floor(s/3600),m=Math.floor((s%3600)/60),sec=s%60;return [h,m,sec].map((n,i)=>i===0&&h===0?null:String(n).padStart(2,'0')).filter(Boolean).join(':');};
 const render=()=>{
  document.querySelectorAll('.ytd-tab').forEach(t=>t.classList.toggle('active',t.dataset.mode===mode));
  rows.innerHTML='';
  const sorted=qualities.slice().sort((a,b)=>b-a), best=sorted[0]||null;
  bestWrap.style.display=(mode==='video'&&best)?'flex':'none';
  if(best){bestLabel.textContent='Automatisch gekozen: '+best+'p · hoogste beschikbare resolutie';bestButton.textContent='↓ Download '+best+'p MP4';bestButton.onclick=()=>startDownload({label:best+'p',format:'mp4',quality:best},bestButton);}
  const items=mode==='video'?sorted.map(q=>({label:q+'p',format:'mp4',quality:q})): (hasAudio?[{label:'Audio · beste beschikbare kwaliteit',format:'mp3',quality:null}]:[]);
  empty.hidden=items.length>0;
  items.forEach(item=>{
   const tr=document.createElement('tr'), name=document.createElement('td'), fmt=document.createElement('td'), action=document.createElement('td'), btn=document.createElement('button');
   name.textContent=item.label;
   if(mode==='video'&&item.quality>=2160){const badge=document.createElement('span');badge.textContent='4K';badge.style.cssText='margin-left:7px;background:#ff9d18;color:white;border-radius:6px;padding:3px 6px;font-size:10px';name.appendChild(badge);}
   else if(mode==='video'&&item.quality>=1440){const badge=document.createElement('span');badge.textContent='2K';badge.style.cssText='margin-left:7px;background:#ff9d18;color:white;border-radius:6px;padding:3px 6px;font-size:10px';name.appendChild(badge);}
   fmt.textContent=item.format;
   btn.className='ytd-download';btn.type='button';btn.textContent='↓ Download';btn.addEventListener('click',()=>startDownload(item,btn));
   action.appendChild(btn);tr.append(name,fmt,action);rows.appendChild(tr);
  });
 };
 document.querySelectorAll('.ytd-tab').forEach(t=>t.addEventListener('click',()=>{mode=t.dataset.mode;render();}));
 async function startDownload(item,button){
  if(busy||!token)return;busy=true;document.querySelectorAll('.ytd-download').forEach(b=>b.disabled=true);button.textContent='Starten…';status.textContent='Download wordt voorbereid…';
  try{
   const body=new FormData();body.append('_token',csrf);body.append('token',token);body.append('format',item.format);if(item.quality)body.append('quality',String(item.quality));
   const response=await fetch(@json(route('youtube-downloader.prepare')),{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf},body});
   const data=await response.json();if(!response.ok)throw new Error(data.message||'Download kon niet starten.');
   status.textContent='Video wordt verwerkt. Dit kan even duren…';
   let finished=false;
   for(let i=0;i<180;i++){
    await new Promise(resolve=>setTimeout(resolve,2000));
    const poll=await fetch(data.status_url,{headers:{'Accept':'application/json'}});
    const state=await poll.json();
    if(!poll.ok)throw new Error(state.message||'Downloadstatus kon niet worden opgehaald.');
    if(state.status==='failed')throw new Error(state.message||'Download mislukt.');
    if(state.status==='ready'&&state.download_url){window.location.assign(state.download_url);status.textContent='Bestand klaar — de download wordt geopend.';finished=true;break;}
   }
   if(!finished)throw new Error('De download duurt te lang. Probeer het opnieuw.');
  }catch(e){status.textContent='';showError(e.message||'Er ging iets mis.');}
  finally{busy=false;document.querySelectorAll('.ytd-download').forEach(b=>b.disabled=false);button.textContent='↓ Download';}
 }
 form.addEventListener('submit',async e=>{
  e.preventDefault();error.classList.remove('show');result.classList.remove('show');status.textContent='YouTube-formaten ophalen…';submit.disabled=true;submit.textContent='Analyseren…';token=null;
  try{
   const response=await fetch(@json(route('youtube-downloader.analyze')),{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({url:input.value.trim()})});
   const data=await response.json();if(!response.ok)throw new Error(data.message||'De YouTube-link kon niet worden geanalyseerd.');
   token=data.token;qualities=data.qualities||[];hasAudio=!!data.has_audio;
   const videoId=(data.watch_url||'').match(/[?&]v=([A-Za-z0-9_-]{11})/);document.getElementById('ytd-preview').src=videoId?'https://www.youtube-nocookie.com/embed/'+videoId[1]+'?rel=0':'';document.getElementById('ytd-video-title').textContent=data.title||'YouTube-video';document.getElementById('ytd-author').textContent='Kanaal: '+(data.author||'YouTube');document.getElementById('ytd-duration').textContent=duration(data.duration);
   mode='video';render();entryStage.style.display='none';form.style.display='none';document.querySelector('.ytd-hint').style.display='none';result.classList.add('show');status.textContent=qualities.length+' echte videokwaliteiten gevonden'+(hasAudio?' · audio beschikbaar':'');result.scrollIntoView({behavior:'smooth',block:'start'});
  }catch(e){status.textContent='';showError(e.message||'Er ging iets mis.');}
  finally{submit.disabled=false;submit.textContent='Download';}
 });
})();
</script>
@endpush
