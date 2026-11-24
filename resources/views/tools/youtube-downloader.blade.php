@extends('layouts.site-layout')

@section('title', 'YouTube Downloader | Mashal Studio')
@section('meta_description', 'Bekijk YouTube-videogegevens en open de officiële downloadopties voor je eigen uploads.')

@push('styles')
<style>
.ytd-page{min-height:calc(100vh - 72px);padding:52px 16px 90px;color:#f5f7fb;background:radial-gradient(circle at 50% -120px,rgba(124,105,255,.2),transparent 480px),linear-gradient(180deg,#08090d,#050609)}
.ytd-wrap{max-width:980px;margin:auto}.ytd-hero{text-align:center;margin:0 auto 34px;max-width:760px}
.ytd-pill{display:inline-flex;gap:8px;align-items:center;border:1px solid #39315e;background:#171329;color:#b8adff;border-radius:99px;padding:8px 13px;font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
.ytd-title{font-size:clamp(38px,7vw,66px);line-height:1.02;letter-spacing:-.055em;margin:18px 0 12px;color:white;font-weight:800}.ytd-title span{color:#9b8cff}
.ytd-sub{color:#9299a8;font-size:14px;line-height:1.7}
.ytd-panel{max-width:820px;margin:auto;padding:clamp(18px,4vw,32px);border:1px solid #252936;border-radius:24px;background:linear-gradient(145deg,rgba(22,24,34,.98),rgba(11,13,19,.98));box-shadow:0 24px 80px #0007}
.ytd-tabs{display:flex;gap:8px;margin-bottom:18px}.ytd-tab{border:1px solid #303442;background:#11141c;color:#9da5b5;border-radius:12px;padding:10px 14px;font-size:12px;font-weight:750}.ytd-tab.active{background:#292344;color:#c7bdff;border-color:#6556a8}
.ytd-inputrow{display:flex;gap:10px}.ytd-input{min-width:0;flex:1;background:#080b11;border:1px solid #303746;border-radius:13px;padding:16px;color:white;outline:none}.ytd-input:focus{border-color:#8877ff;box-shadow:0 0 0 3px #8877ff22}
.ytd-btn{border:0;border-radius:13px;padding:0 22px;background:linear-gradient(135deg,#8976ff,#6250d7);color:white;font-weight:800;cursor:pointer}.ytd-btn:disabled{opacity:.55;cursor:wait}
.ytd-hint{font-size:11px;color:#747e8f;margin:12px 2px 0}.ytd-result{display:none;margin-top:22px;border:1px solid #2b3140;border-radius:17px;padding:14px;gap:16px;align-items:center;background:#0b0e15}.ytd-result.show{display:flex}.ytd-thumb{width:180px;aspect-ratio:16/9;object-fit:cover;border-radius:10px;background:#161a24}.ytd-result h3{font-size:15px;margin:0 0 6px;color:white}.ytd-result p{margin:0;color:#8e98a9;font-size:12px}.ytd-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:13px}.ytd-link{display:inline-flex;text-decoration:none;border:1px solid #3b4353;color:#d9dce5;border-radius:10px;padding:9px 12px;font-size:11px;font-weight:750}.ytd-link.primary{background:#25203d;border-color:#6657a7;color:#d0c7ff}.ytd-error{display:none;margin-top:14px;color:#ff9caa;font-size:12px}.ytd-error.show{display:block}
.ytd-info{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:22px}.ytd-info article{border:1px solid #242a36;border-radius:15px;padding:16px;background:#0d1017}.ytd-info strong{display:block;font-size:12px;color:#e8eaf1;margin-bottom:6px}.ytd-info span{font-size:11px;line-height:1.6;color:#818b9b}
.ytd-note{max-width:820px;margin:18px auto 0;color:#737d8e;font-size:11px;line-height:1.7;text-align:center}
#ytd-quality,#ytd-format{color:#fff;color-scheme:dark}#ytd-media{padding:12px}
@media(max-width:620px){.ytd-inputrow{flex-direction:column}.ytd-btn{height:48px}.ytd-result{align-items:flex-start;flex-direction:column}.ytd-thumb{width:100%}.ytd-info{grid-template-columns:1fr}.ytd-tabs{flex-wrap:wrap}}
</style>
@endpush

@section('content')
<main class="ytd-page">
 <div class="ytd-wrap">
  <header class="ytd-hero">
   <div class="ytd-pill"><span>●</span> MASHAL STUDIO · VIDEO TOOLS</div>
   <h1 class="ytd-title">YouTube <span>Downloader</span></h1>
   <p class="ytd-sub">Plak een YouTube-link, bekijk de video en ga rechtstreeks naar de officiële downloadmogelijkheden voor content die van jou is of waarvoor je toestemming hebt.</p>
  </header>
  <section class="ytd-panel" aria-label="YouTube video">
   <div class="ytd-tabs"><span class="ytd-tab active">YouTube video</span><span class="ytd-tab">MP4 · eigen uploads</span><span class="ytd-tab">MP3 · eigen audio</span></div>
   <form id="ytd-form">
    @csrf
    <div class="ytd-inputrow">
     <input class="ytd-input" id="ytd-url" type="url" placeholder="https://www.youtube.com/watch?v=..." autocomplete="url" required>
     <button class="ytd-btn" id="ytd-submit" type="submit">Video bekijken&nbsp; →</button>
    </div>
    <p class="ytd-hint">Ondersteunt youtube.com, youtu.be, Shorts en openbare video's.</p>
   </form>
   <p class="ytd-error" id="ytd-error" role="alert"></p>
   <div class="ytd-result" id="ytd-result">
    <img class="ytd-thumb" id="ytd-thumb" alt="YouTube-videominiatuur">
    <div style="min-width:0;flex:1">
     <h3 id="ytd-video-title"></h3><p id="ytd-author"></p>
     <div class="ytd-actions">
      <a class="ytd-link" id="ytd-watch" target="_blank" rel="noopener">Video openen</a>
      <a class="ytd-link primary" id="ytd-studio" href="https://studio.youtube.com/" target="_blank" rel="noopener">Eigen upload downloaden via YouTube Studio ↗</a>
      <button class="ytd-link" id="ytd-go-convert" type="button">Eigen bestand converteren ↓</button>
     </div>
     <p class="ytd-hint" style="margin-top:12px">YouTube geeft bij openbare links geen officiële lijst met downloadbare MP4/MP3-kwaliteiten. Voor je eigen upload open je YouTube Studio. Heb je het videobestand al? Gebruik dan hieronder de converter voor echte kwaliteitsdetectie en downloaden.</p>
    </div>
   </div>
  </section>
  <section class="ytd-panel" id="ytd-upload-panel" style="margin:22px auto 0" aria-label="Echte kwaliteitscontrole en downloaden">
   <h2 style="font-size:22px;margin:0 0 8px;color:#fff">Echte kwaliteit controleren & downloaden</h2>
   <p class="ytd-sub" style="margin:0 0 18px">Upload je eigen video of een bestand dat je mag gebruiken. Mashal Studio leest de bron met FFprobe en toont uitsluitend resoluties die het bestand aankan.</p>
   <form id="ytd-upload-form">
    @csrf
    <div class="ytd-inputrow">
     <input class="ytd-input" id="ytd-media" type="file" accept="video/mp4,video/quicktime,video/webm,video/x-matroska,video/x-msvideo,audio/*" required>
     <button class="ytd-btn" id="ytd-analyze" type="submit">Kwaliteit controleren</button>
    </div>
   </form>
   <p class="ytd-error" id="ytd-upload-error" role="alert"></p>
   <div id="ytd-upload-result" style="display:none;margin-top:18px">
    <p id="ytd-source-info" class="ytd-sub"></p>
    <div class="ytd-inputrow" style="margin-top:12px">
     <select class="ytd-input" id="ytd-quality" aria-label="Beschikbare videokwaliteit"></select>
     <select class="ytd-input" id="ytd-format" aria-label="Downloadformaat"><option value="mp4">MP4-video</option><option value="mp3">MP3-audio</option></select>
     <button class="ytd-btn" id="ytd-convert" type="button">Downloaden</button>
    </div>
    <p class="ytd-hint" id="ytd-quality-note"></p>
   </div>
  </section>
  <section class="ytd-info">
   <article><strong>1. Link plakken</strong><span>Gebruik een normale YouTube-link of een Shorts-link.</span></article>
   <article><strong>2. Video controleren</strong><span>Bekijk titel, kanaal en videovoorbeeld voordat je verdergaat.</span></article>
   <article><strong>3. Officieel opslaan</strong><span>Download je eigen uploads via YouTube Studio. Andere video's alleen met toestemming en via toegestane opties.</span></article>
  </section>
  <p class="ytd-note">Mashal Studio biedt geen omzeiling van YouTube-beperkingen. Downloads van video's van anderen zijn alleen mogelijk wanneer YouTube of de rechthebbende dit uitdrukkelijk toestaat.</p>
 </div>
</main>
@endsection

@push('scripts')
<script>
(() => {
 const form=document.getElementById('ytd-form');
 const input=document.getElementById('ytd-url');
 const button=document.getElementById('ytd-submit');
 const error=document.getElementById('ytd-error');
 const result=document.getElementById('ytd-result');
 const token=form.querySelector('input[name="_token"]').value;
 form.addEventListener('submit',async event=>{
  event.preventDefault();error.classList.remove('show');result.classList.remove('show');
  button.disabled=true;button.textContent='Bezig met controleren…';
  try{
   const response=await fetch(@json(route('youtube-downloader.inspect')),{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':token},body:JSON.stringify({url:input.value.trim()})});
   const data=await response.json();
   if(!response.ok)throw new Error(data.message||'De link kon niet worden gecontroleerd.');
   document.getElementById('ytd-thumb').src=data.thumbnail;
   document.getElementById('ytd-video-title').textContent=data.title;
   document.getElementById('ytd-author').textContent='Kanaal: '+data.author;
   document.getElementById('ytd-watch').href=data.watch_url;
   result.classList.add('show');
   document.getElementById('ytd-go-convert').onclick=()=>document.getElementById('ytd-upload-panel').scrollIntoView({behavior:'smooth',block:'start'});
  }catch(e){error.textContent=e.message||'Er ging iets mis.';error.classList.add('show');}
  finally{button.disabled=false;button.textContent='Video bekijken →';}
 });
})();
</script>
@endpush


@push('scripts')
<script>
(() => {
 const form=document.getElementById('ytd-upload-form');
 if(!form)return;
 const file=document.getElementById('ytd-media'), analyze=document.getElementById('ytd-analyze');
 const error=document.getElementById('ytd-upload-error'), result=document.getElementById('ytd-upload-result');
 const quality=document.getElementById('ytd-quality'), format=document.getElementById('ytd-format');
 const convert=document.getElementById('ytd-convert'), note=document.getElementById('ytd-quality-note');
 let token=null, available=[];
 const csrf=form.querySelector('input[name="_token"]').value;
 form.addEventListener('submit',async e=>{
  e.preventDefault(); error.classList.remove('show');
  if(!file.files.length)return;
  analyze.disabled=true; analyze.textContent='Bronbestand analyseren…';
  try{
   const body=new FormData(); body.append('_token',csrf); body.append('media',file.files[0]);
   const response=await fetch(@json(route('youtube-downloader.upload')),{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf},body});
   const data=await response.json(); if(!response.ok)throw new Error(data.message||'Upload/analyse mislukt.');
   token=data.token; available=data.qualities||[];
   document.getElementById('ytd-source-info').textContent=data.name+' · '+(data.width&&data.height?data.width+' × '+data.height+' pixels':'alleen audio');
   quality.innerHTML='';
   available.forEach(q=>{const o=document.createElement('option');o.value=String(q);o.textContent=q+'p'+(q===720?' HD':q===1080?' Full HD':q===1440?' 2K':q===2160?' 4K':'');quality.appendChild(o);});
   if(!available.length){const o=document.createElement('option');o.value='';o.textContent='Geen videoresoluties beschikbaar';quality.appendChild(o);}
   format.value=data.has_video?'mp4':'mp3';
   format.querySelector('option[value="mp4"]').disabled=!data.has_video;
   quality.disabled=!available.length;
   note.textContent=data.message;
   result.style.display='block';
  }catch(err){error.textContent=err.message;error.classList.add('show');}
  finally{analyze.disabled=false;analyze.textContent='Kwaliteit controleren';}
 });
 format.addEventListener('change',()=>{quality.disabled=format.value==='mp3'||!available.length;});
 convert.addEventListener('click',async()=>{
  if(!token)return;
  convert.disabled=true;convert.textContent='Converteren…';
  try{
   const body=new FormData();body.append('_token',csrf);body.append('token',token);body.append('format',format.value);
   if(format.value==='mp4')body.append('quality',quality.value);
   const response=await fetch(@json(route('youtube-downloader.convert')),{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf},body});
   if(!response.ok){const data=await response.json();throw new Error(data.message||'Download mislukt.');}
   const blob=await response.blob(),url=URL.createObjectURL(blob),a=document.createElement('a');
   a.href=url;a.download=(file.files[0].name.replace(/\.[^.]+$/,'')||'video')+'-'+(format.value==='mp4'?quality.value+'p':'audio')+'.'+format.value;
   document.body.appendChild(a);a.click();a.remove();URL.revokeObjectURL(url);
  }catch(err){error.textContent=err.message;error.classList.add('show');}
  finally{convert.disabled=false;convert.textContent='Downloaden';}
 });
})();
</script>
@endpush
