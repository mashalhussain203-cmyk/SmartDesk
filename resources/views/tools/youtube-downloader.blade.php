@extends('layouts.site-layout')

@section('title', 'YouTube Downloader | Mashal Studio')
@section('meta_description', 'Analyseer openbare YouTube-video’s en download beschikbare MP4-resoluties of MP3-audio.')

@push('styles')
<style>
.ytd-page{min-height:calc(100vh - 72px);padding:42px 16px 90px;color:#f5f7fb;background:radial-gradient(circle at 50% -120px,rgba(124,105,255,.2),transparent 480px),linear-gradient(180deg,#08090d,#050609)}
.ytd-wrap{max-width:900px;margin:auto}.ytd-hero{text-align:center;margin:0 auto 28px;max-width:760px}
.ytd-pill{display:inline-flex;gap:8px;align-items:center;border:1px solid #39315e;background:#171329;color:#b8adff;border-radius:99px;padding:8px 13px;font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
.ytd-title{font-size:clamp(38px,7vw,66px);line-height:1.02;letter-spacing:-.055em;margin:18px 0 12px;color:white;font-weight:800}.ytd-title span{color:#9b8cff}
.ytd-sub{color:#9299a8;font-size:14px;line-height:1.7}
.ytd-panel{max-width:820px;margin:auto;padding:clamp(16px,4vw,28px);border:1px solid #252936;border-radius:22px;background:linear-gradient(145deg,rgba(22,24,34,.98),rgba(11,13,19,.98));box-shadow:0 24px 80px #0007}
.ytd-inputrow{display:flex;gap:10px}.ytd-input{min-width:0;flex:1;background:#080b11;border:1px solid #303746;border-radius:13px;padding:15px;color:white;outline:none}.ytd-btn{border:0;border-radius:13px;padding:0 22px;background:linear-gradient(135deg,#8976ff,#6250d7);color:white;font-weight:800;cursor:pointer}.ytd-btn:disabled{opacity:.55;cursor:wait}
.ytd-hint{font-size:11px;color:#747e8f;margin:12px 2px 0}.ytd-error{display:none;margin-top:14px;color:#ff9caa;font-size:12px}.ytd-error.show{display:block}
.ytd-result{display:none;margin-top:20px}.ytd-result.show{display:block}.ytd-videohead{display:flex;gap:14px;align-items:center;margin-bottom:18px}.ytd-thumb{width:180px;aspect-ratio:16/9;object-fit:cover;border-radius:10px;background:#161a24}.ytd-videohead h3{font-size:15px;margin:0 0 6px;color:white}.ytd-videohead p{margin:0;color:#8e98a9;font-size:12px}
.ytd-tabs{display:flex;gap:8px;margin:16px 0 12px}.ytd-tab{border:1px solid #303442;background:#11141c;color:#9da5b5;border-radius:12px;padding:10px 17px;font-size:12px;font-weight:800;cursor:pointer}.ytd-tab.active{background:#fff;color:#be315d;border-color:#ddd;box-shadow:0 2px 8px #0003}
.ytd-table-wrap{overflow-x:auto;border:1px solid #2c3340;border-radius:14px;background:#fff;color:#171b26}.ytd-table{width:100%;border-collapse:collapse;min-width:430px}.ytd-table th{text-align:left;padding:14px 16px;background:#f7f8fa;color:#687181;font-size:11px;letter-spacing:.08em}.ytd-table td{padding:14px 16px;border-top:1px solid #e9ebef;font-size:14px;font-weight:650}.ytd-table td:nth-child(2){color:#687181;text-transform:lowercase}.ytd-download{border:0;border-radius:9px;padding:11px 16px;background:#08ad78;color:#fff;font-weight:800;min-width:112px;cursor:pointer}.ytd-download:disabled{opacity:.6}.ytd-empty{padding:18px;color:#7c8491;font-size:13px}
.ytd-info{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:18px}.ytd-info article{border:1px solid #242a36;border-radius:14px;padding:14px;background:#0d1017}.ytd-info strong{display:block;font-size:12px;color:#e8eaf1;margin-bottom:6px}.ytd-info span{font-size:11px;line-height:1.6;color:#818b9b}
.ytd-note{max-width:820px;margin:16px auto 0;color:#737d8e;font-size:11px;line-height:1.7;text-align:center}
@media(max-width:620px){.ytd-inputrow{flex-direction:column}.ytd-btn{height:48px}.ytd-videohead{align-items:flex-start;flex-direction:column}.ytd-thumb{width:100%}.ytd-info{grid-template-columns:1fr}.ytd-tab{flex:1}}
</style>
@endpush

@section('content')
<main class="ytd-page"><div class="ytd-wrap">
<header class="ytd-hero"><div class="ytd-pill">● MASHAL STUDIO · VIDEO TOOLS</div><h1 class="ytd-title">YouTube <span>Downloader</span></h1><p class="ytd-sub">Plak een openbare YouTube-link. Mashal Studio controleert de werkelijk beschikbare video- en audioformaten en toont alleen kwaliteiten die YouTube voor die video teruggeeft.</p></header>
<section class="ytd-panel" aria-label="YouTube downloader">
<form id="ytd-form">@csrf<div class="ytd-inputrow"><input class="ytd-input" id="ytd-url" type="url" placeholder="https://www.youtube.com/watch?v=..." autocomplete="url" required><button class="ytd-btn" id="ytd-submit" type="submit">Kwaliteit ophalen →</button></div><p class="ytd-hint">Ondersteunt youtube.com, youtu.be, Shorts en openbare video's.</p></form>
<p class="ytd-error" id="ytd-error" role="alert"></p>
<div class="ytd-result" id="ytd-result">
<div class="ytd-videohead"><img class="ytd-thumb" id="ytd-thumb" alt="YouTube-videominiatuur"><div><h3 id="ytd-video-title"></h3><p id="ytd-author"></p><p class="ytd-hint" id="ytd-duration"></p></div></div>
<div class="ytd-tabs"><button class="ytd-tab active" type="button" data-mode="video">▣ Video (MP4)</button><button class="ytd-tab" type="button" data-mode="audio">♫ Audio (MP3)</button></div>
<div class="ytd-table-wrap"><table class="ytd-table"><thead id="ytd-head"><tr><th>FILE TYPE</th><th>FORMAT</th><th>ACTION</th></tr></thead><tbody id="ytd-rows"></tbody></table><div class="ytd-empty" id="ytd-empty" hidden>Geen downloadformaten beschikbaar.</div></div>
<p class="ytd-hint" id="ytd-status" aria-live="polite"></p>
</div>
</section>
<section class="ytd-info"><article><strong>1. Link plakken</strong><span>Gebruik een YouTube- of Shorts-link.</span></article><article><strong>2. Kwaliteit ophalen</strong><span>De backend leest de beschikbare streamformaten uit.</span></article><article><strong>3. Downloaden</strong><span>Kies een echte beschikbare resolutie of MP3-audio.</span></article></section>
<p class="ytd-note">Gebruik downloads alleen voor content die van jou is of waarvoor je toestemming hebt. Privévideo’s, DRM en accountbeperkingen worden niet omzeild. Beschikbaarheid kan veranderen door YouTube.</p>
</div></main>
@endsection

@push('scripts')
<script>
(() => {
 const form=document.getElementById('ytd-form'), input=document.getElementById('ytd-url'), submit=document.getElementById('ytd-submit');
 const error=document.getElementById('ytd-error'), result=document.getElementById('ytd-result'), rows=document.getElementById('ytd-rows'), empty=document.getElementById('ytd-empty'), status=document.getElementById('ytd-status');
 const csrf=form.querySelector('input[name="_token"]').value;
 let token=null, qualities=[], hasAudio=false, mode='video', busy=false;
 const showError=(message)=>{error.textContent=message;error.classList.add('show');};
 const duration=(s)=>{s=Number(s||0);if(!s)return '';const h=Math.floor(s/3600),m=Math.floor((s%3600)/60),sec=s%60;return [h,m,sec].map((n,i)=>i===0&&h===0?null:String(n).padStart(2,'0')).filter(Boolean).join(':');};
 const render=()=>{
  document.querySelectorAll('.ytd-tab').forEach(t=>t.classList.toggle('active',t.dataset.mode===mode));
  rows.innerHTML='';
  const items=mode==='video'?qualities.map(q=>({label:q+'p',format:'mp4',quality:q})): (hasAudio?[{label:'Audio · beste beschikbare kwaliteit',format:'mp3',quality:null}]:[]);
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
    if(state.status==='ready'&&state.download_url){window.location.href=state.download_url;status.textContent='Download is klaar.';finished=true;break;}
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
   document.getElementById('ytd-thumb').src=data.thumbnail||'';document.getElementById('ytd-video-title').textContent=data.title||'YouTube-video';document.getElementById('ytd-author').textContent='Kanaal: '+(data.author||'YouTube');document.getElementById('ytd-duration').textContent=duration(data.duration);
   mode='video';render();result.classList.add('show');status.textContent=qualities.length+' echte videokwaliteiten gevonden'+(hasAudio?' · audio beschikbaar':'');
  }catch(e){status.textContent='';showError(e.message||'Er ging iets mis.');}
  finally{submit.disabled=false;submit.textContent='Kwaliteit ophalen →';}
 });
})();
</script>
@endpush
