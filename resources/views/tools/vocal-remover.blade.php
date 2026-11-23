@extends('layouts.site-layout')

@section('title', 'Vocal Remover | Mashal Studio')
@section('meta_description', 'Scheid vocals en instrumentale muziek rechtstreeks in Mashal Studio.')

@push('styles')
<style>
.vr-page{min-height:calc(100vh - 72px);padding:42px 0 80px;background:radial-gradient(ellipse at 50% 0,rgba(38,170,95,.18),transparent 540px),#07100b;color:#effaf2}
.vr-shell{width:min(calc(100% - 30px),1050px);margin:auto}
.vr-top{text-align:center;margin:0 auto 30px;max-width:760px}
.vr-kicker{display:inline-flex;align-items:center;gap:8px;padding:8px 13px;border:1px solid #245d3b;border-radius:99px;background:#0d2115;color:#8ce6a9;font-size:10px;font-weight:850;letter-spacing:.14em;text-transform:uppercase}
.vr-top h1{margin:18px 0 12px;font-size:clamp(38px,7vw,66px);line-height:1;letter-spacing:-.06em;color:#fff}
.vr-top h1 span{color:#6ee79a}
.vr-top p{max-width:610px;margin:auto;color:#a4b8aa;font-size:14px;line-height:1.75}
.vr-panel{padding:clamp(18px,4vw,34px);border:1px solid rgba(116,224,151,.2);border-radius:24px;background:linear-gradient(145deg,rgba(17,43,27,.96),rgba(8,19,12,.98));box-shadow:0 28px 90px rgba(0,0,0,.3)}
.vr-drop{display:flex;min-height:230px;align-items:center;justify-content:center;flex-direction:column;gap:12px;padding:25px;border:1.5px dashed #3b8154;border-radius:18px;background:rgba(25,76,43,.18);text-align:center;cursor:pointer;transition:.2s}
.vr-drop:hover,.vr-drop.drag{border-color:#83efaa;background:rgba(45,130,70,.2)}
.vr-drop svg{width:38px;height:38px;color:#79e59c}
.vr-drop strong{font-size:17px;color:#f4fff6}
.vr-drop small{color:#9bb5a2;font-size:12px}
.vr-file{display:none}
.vr-actions{display:flex;justify-content:center;gap:10px;flex-wrap:wrap;margin-top:20px}
.vr-btn{border:1px solid #356548;border-radius:12px;padding:12px 18px;background:#102719;color:#e9fff0;font-size:13px;font-weight:800;cursor:pointer;text-decoration:none}
.vr-btn.primary{background:linear-gradient(135deg,#168044,#25a65a);border-color:#36bd70;color:white}
.vr-btn:disabled{opacity:.45;cursor:not-allowed}
.vr-meta{margin-top:14px;text-align:center;color:#8da995;font-size:11px}
.vr-status{display:none;margin-top:22px;padding:17px;border:1px solid #285c3b;border-radius:15px;background:#0b1c11}
.vr-status.show{display:block}
.vr-status-line{display:flex;justify-content:space-between;gap:12px;font-size:13px;font-weight:750}
.vr-track{height:7px;margin-top:13px;overflow:hidden;border-radius:99px;background:#203729}
.vr-fill{width:15%;height:100%;border-radius:99px;background:linear-gradient(90deg,#26a85b,#8cf0aa);animation:vrpulse 1.6s ease-in-out infinite alternate}
@keyframes vrpulse{from{width:12%}to{width:88%}}
.vr-results{display:none;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-top:22px}
.vr-results.show{display:grid}
.vr-result{padding:17px;border:1px solid #28583a;border-radius:15px;background:#0b1b11}
.vr-result h3{margin:0 0 12px;font-size:14px}
.vr-result audio{width:100%;height:38px}
.vr-result a{display:inline-flex;margin-top:12px;color:#8de9a8;font-size:12px;font-weight:800;text-decoration:none}
.vr-error{display:none;margin-top:14px;padding:12px 14px;border:1px solid #8b4141;border-radius:12px;background:#2b1414;color:#ffc2c2;font-size:12px}
.vr-error.show{display:block}
.vr-foot{margin:17px auto 0;max-width:760px;color:#7f9886;text-align:center;font-size:11px;line-height:1.7}
@media(max-width:620px){.vr-results.show{grid-template-columns:1fr}.vr-drop{min-height:200px}.vr-page{padding-top:28px}}
</style>
@endpush

@section('content')
<section class="vr-page">
 <div class="vr-shell">
  <header class="vr-top">
   <div class="vr-kicker">♫ &nbsp; Mashal Studio · Audio workspace</div>
   <h1>Vocal <span>Remover.</span></h1>
   <p>{{ __('Upload een nummer en splits het in twee afzonderlijke audiotracks: vocals en instrumentaal. Alles start vanuit Mashal Studio.') }}</p>
  </header>
  <div class="vr-panel">
   <form id="vocalForm" enctype="multipart/form-data">
    @csrf
    <label class="vr-drop" id="dropZone" for="audioFile">
     <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 16V4m0 0L7 9m5-5 5 5M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3"/></svg>
     <strong id="fileTitle">{{ __('Sleep je audiobestand hierheen') }}</strong>
     <small id="fileHint">{{ __('of tik om een bestand te kiezen · MP3, WAV, M4A, FLAC, OGG · max. 1 GB') }}</small>
     <input class="vr-file" id="audioFile" name="audio" type="file" accept=".mp3,.wav,.m4a,.aac,.flac,.ogg,.mp4,.mov,.webm,audio/*">
    </label>
    <div class="vr-actions">
     <button class="vr-btn primary" id="processBtn" type="submit" disabled>{{ __('Splits vocals & instrumentaal') }}</button>
     <button class="vr-btn" id="resetBtn" type="button">{{ __('Opnieuw kiezen') }}</button>
    </div>
    <div class="vr-meta">{{ __('Gratis open-source verwerking · bestanden worden tijdelijk verwerkt en daarna automatisch verwijderd') }}</div>
   </form>
   <div class="vr-error" id="errorBox" role="alert"></div>
   <div class="vr-status" id="statusBox" aria-live="polite">
    <div class="vr-status-line"><span id="statusText">{{ __('Audio wordt verwerkt…') }}</span><span id="statusTime"></span></div>
    <div class="vr-track"><div class="vr-fill"></div></div>
   </div>
   <div class="vr-results" id="resultsBox">
    <div class="vr-result"><h3>🎤 {{ __('Geïsoleerde vocals') }}</h3><audio id="vocalsPlayer" controls preload="none"></audio><a id="vocalsDownload" href="#">{{ __('Download vocals ↓') }}</a></div>
    <div class="vr-result"><h3>🎵 {{ __('Instrumentaal') }}</h3><audio id="instrumentalPlayer" controls preload="none"></audio><a id="instrumentalDownload" href="#">{{ __('Download instrumentaal ↓') }}</a></div>
   </div>
  </div>
  <p class="vr-foot">{{ __('Audio wordt door de eigen Mashal Studio-backend verwerkt. De scheidingsengine is open-source software. Verwerking gebruikt de beschikbare servercapaciteit; upload alleen audio waarvoor je de rechten hebt.') }}</p>
 </div>
</section>
<script>
(() => {
 const form=document.getElementById('vocalForm'),input=document.getElementById('audioFile'),drop=document.getElementById('dropZone'),title=document.getElementById('fileTitle'),hint=document.getElementById('fileHint'),submit=document.getElementById('processBtn'),reset=document.getElementById('resetBtn'),status=document.getElementById('statusBox'),statusText=document.getElementById('statusText'),error=document.getElementById('errorBox'),results=document.getElementById('resultsBox');
 let timer=null,started=0;
 const csrf=form.querySelector('input[name="_token"]').value;
 function fail(message){error.textContent=message;error.classList.add('show');status.classList.remove('show')}
 function clear(){if(timer)clearTimeout(timer);timer=null;form.reset();title.textContent='{{ __('Sleep je audiobestand hierheen') }}';hint.textContent='{{ __('of tik om een bestand te kiezen · MP3, WAV, M4A, FLAC, OGG · max. 1 GB') }}';submit.disabled=true;submit.textContent='{{ __('Splits vocals & instrumentaal') }}';status.classList.remove('show');error.classList.remove('show');results.classList.remove('show');document.getElementById('vocalsPlayer').removeAttribute('src');document.getElementById('instrumentalPlayer').removeAttribute('src')}
 input.addEventListener('change',()=>{const f=input.files[0];if(!f)return;title.textContent=f.name;hint.textContent=(f.size/1048576).toFixed(1)+' MB';submit.disabled=f.size>1024*1048576;if(f.size>1024*1048576)fail('{{ __('Bestand is groter dan 1 GB.') }}');else error.classList.remove('show')});
 ['dragenter','dragover'].forEach(e=>drop.addEventListener(e,x=>{x.preventDefault();drop.classList.add('drag')}));
 ['dragleave','drop'].forEach(e=>drop.addEventListener(e,x=>{x.preventDefault();drop.classList.remove('drag')}));
 drop.addEventListener('drop',e=>{if(e.dataTransfer.files.length){input.files=e.dataTransfer.files;input.dispatchEvent(new Event('change'))}});
 reset.addEventListener('click',clear);
 async function poll(token){try{const r=await fetch('{{ route('vocal-remover.status', ['token'=>'TOKEN']) }}'.replace('TOKEN',token),{headers:{'Accept':'application/json'}});const d=await r.json();if(!r.ok)throw new Error(d.message||'Status niet beschikbaar');if(d.status==='ready'){statusText.textContent='{{ __('Je audio is klaar!') }}';status.classList.remove('show');results.classList.add('show');document.getElementById('vocalsPlayer').src=d.vocals_url;document.getElementById('instrumentalPlayer').src=d.instrumental_url;document.getElementById('vocalsDownload').href=d.vocals_url;document.getElementById('instrumentalDownload').href=d.instrumental_url;submit.disabled=false;submit.textContent='{{ __('Klaar') }}';return}if(d.status==='failed')throw new Error(d.message||'Audioverwerking mislukt');statusText.textContent='{{ __('AI scheidt de zang en instrumenten…') }}';document.getElementById('statusTime').textContent=Math.floor((Date.now()-started)/1000)+'s';timer=setTimeout(()=>poll(token),2500)}catch(e){fail(e.message);submit.disabled=false;submit.textContent='{{ __('Opnieuw proberen') }}'}}
 form.addEventListener('submit',async e=>{e.preventDefault();if(!input.files.length)return;error.classList.remove('show');results.classList.remove('show');status.classList.add('show');statusText.textContent='{{ __('Uploaden en verwerking starten…') }}';submit.disabled=true;submit.textContent='{{ __('Bezig…') }}';started=Date.now();const data=new FormData(form);try{const r=await fetch('{{ route('vocal-remover.start') }}',{method:'POST',body:data,headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf}});const d=await r.json();if(!r.ok)throw new Error(d.message||'Upload mislukt');poll(d.token)}catch(e){fail(e.message);submit.disabled=false;submit.textContent='{{ __('Opnieuw proberen') }}'}});
})();
</script>
@endsection
