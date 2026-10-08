@extends('layouts.site-layout')

@section('title', 'YouTube Live Subscribers | Mashal Studio')
@section('meta_description', 'Volg publieke YouTube-kanaalstatistieken met een automatisch vernieuwende abonneeteller.')

@push('styles')
<style>
.yts-page{min-height:calc(100vh - 72px);padding:45px 16px 90px;background:radial-gradient(circle at 50% -190px,rgba(255,59,73,.14),transparent 520px),#06080c;color:#f7f8fa}
.yts-shell{max-width:1080px;margin:auto}.yts-back{color:#929eaf;text-decoration:none;font-size:13px}
.yts-heading{text-align:center;margin:40px 0 28px}.yts-tag{display:inline-block;padding:7px 13px;border:1px solid #433036;border-radius:25px;color:#ff9ea5;font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
.yts-heading h1{font-size:clamp(36px,6vw,66px);letter-spacing:-.055em;margin:15px 0 9px;line-height:1.08}.yts-heading h1 span{color:#ff525f}.yts-heading p{font-size:13px;color:#8894a4}
.yts-search{display:flex;gap:10px;max-width:770px;margin:0 auto 28px}.yts-search input{flex:1;min-width:0;border:1px solid #303846;border-radius:15px;background:#11151e;padding:17px;color:#fff;outline:none}.yts-search input:focus{border-color:#fd5d67}.yts-btn{border:0;border-radius:15px;background:#f44352;color:#fff;padding:0 23px;font-size:13px;font-weight:800;cursor:pointer}.yts-btn:disabled{opacity:.5;cursor:wait}
.yts-results{max-width:770px;margin:-10px auto 24px;display:grid;gap:8px}.yts-result{width:100%;text-align:left;display:flex;align-items:center;gap:14px;background:#121720;color:white;border:1px solid #2c333e;border-radius:12px;padding:12px;cursor:pointer}.yts-result:hover{border-color:#f44352}.yts-result img{width:42px;height:42px;border-radius:50%}.yts-result small{display:block;color:#8d98a9;margin-top:4px}
.yts-panel{border:1px solid #282e38;border-radius:28px;background:linear-gradient(160deg,#161b24,#0b0e14);padding:clamp(20px,4vw,46px);box-shadow:0 26px 75px #0006}
.yts-channel{display:flex;align-items:center;justify-content:center;gap:16px;text-align:left}.yts-avatar{width:68px;height:68px;border-radius:50%;object-fit:cover;background:#242b36}.yts-name{font-size:clamp(19px,3vw,26px);font-weight:800}.yts-channel a{font-size:12px;color:#adb8cb}.yts-sub-label{text-align:center;color:#9ca8b6;letter-spacing:.15em;font-weight:800;text-transform:uppercase;font-size:11px;margin-top:48px}
.yts-number{font-variant-numeric:tabular-nums;text-align:center;font-size:clamp(53px,10vw,115px);letter-spacing:-.065em;font-weight:850;line-height:1.3;transition:opacity .18s}.yts-status{text-align:center;font-size:12px;color:#8b96a5;margin-bottom:40px}.yts-status::before{content:'';display:inline-block;background:#5dd79c;width:7px;height:7px;border-radius:50%;margin-right:8px}
.yts-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}.yts-stat{border:1px solid #27303b;border-radius:17px;background:#10151c;text-align:center;padding:22px 10px}.yts-stat span{display:block;font-size:11px;color:#929dac;margin-bottom:7px}.yts-stat strong{font-size:clamp(17px,3vw,26px);font-variant-numeric:tabular-nums}.yts-note{color:#7c8797;text-align:center;font-size:11px;line-height:1.7;margin:20px auto;max-width:700px}
.yts-error{color:#ff9da5;text-align:center;font-size:13px;min-height:21px;margin:0 auto 16px}
@media(max-width:600px){.yts-search{flex-direction:column}.yts-btn{min-height:48px}.yts-stats{grid-template-columns:1fr}.yts-channel{flex-wrap:wrap;text-align:center}.yts-heading{margin-top:25px}}
</style>
@endpush

@section('content')
<section class="yts-page">
 <div class="yts-shell">
  <a class="yts-back" href="{{ route('live-counts.index') }}">← Terug naar Live Counts</a>
  <header class="yts-heading">
   <div class="yts-tag">YouTube • Live Counts</div>
   <h1>Live <span>Subscribers.</span></h1>
   <p>Zoek een kanaal of plak een YouTube-kanaallink om de publieke statistieken te volgen.</p>
  </header>
  <form id="yts-search" class="yts-search">
   <input id="yts-query" type="text" maxlength="255" placeholder="Kanaalnaam, @handle of YouTube-kanaal URL" required autocomplete="off" aria-label="YouTube-kanaal zoeken">
   <button id="yts-submit" class="yts-btn" type="submit">Zoek kanaal</button>
  </form>
  <p id="yts-error" class="yts-error" role="alert" aria-live="polite"></p>
  <div id="yts-results" class="yts-results" aria-live="polite"></div>
  <div class="yts-panel" id="yts-panel" hidden>
   <div class="yts-channel"><img id="yts-avatar" class="yts-avatar" alt=""><div><div id="yts-name" class="yts-name"></div><a id="yts-link" href="#" target="_blank" rel="noopener noreferrer">Open op YouTube ↗</a></div></div>
   <div class="yts-sub-label">Subscribers</div>
   <div id="yts-subscribers" class="yts-number" aria-live="polite">—</div>
   <div id="yts-status" class="yts-status">Ophalen…</div>
   <div class="yts-stats">
    <div class="yts-stat"><span>Total Views</span><strong id="yts-views">—</strong></div>
    <div class="yts-stat"><span>Videos</span><strong id="yts-videos">—</strong></div>
    <div class="yts-stat"><span>Volgende mijlpaal</span><strong id="yts-goal">—</strong></div>
   </div>
  </div>
  <p class="yts-note">De cijfers komen uit de officiële YouTube Data API en vernieuwen maximaal één keer per minuut. YouTube rondt openbare abonnee-aantallen af: de teller kan daarom niet elk individueel abonnement weergeven.</p>
 </div>
</section>
@endsection

@push('scripts')
<script>
(() => {
 const lookupUrl = @json(route('youtube-subscribers.lookup'));
 const statsBase = @json(url('/api/tools/youtube-subscribers'));
 const form=document.getElementById('yts-search'), input=document.getElementById('yts-query');
 const error=document.getElementById('yts-error'),results=document.getElementById('yts-results'),panel=document.getElementById('yts-panel');
 const submit=document.getElementById('yts-submit');
 let channelId=null, timer=null, lastCount=null;
 const fmt=n=>n===null||n===undefined?'—':new Intl.NumberFormat('nl-NL').format(n);
 const goal=n=>{if(n===null||n===undefined)return '—';let step=n<100?10:n<1000?100:n<10000?1000:n<100000?10000:n<1000000?100000:1000000;return fmt((Math.floor(n/step)+1)*step)};
 const setError=msg=>error.textContent=msg||'';
 const safeImage=url=>{try{const u=new URL(url);return u.protocol==='https:'?u.href:''}catch(e){return ''}};
 function display(data){
   panel.hidden=false;document.getElementById('yts-name').textContent=data.title;
   document.getElementById('yts-avatar').src=safeImage(data.avatar)||'';
   document.getElementById('yts-link').href='https://www.youtube.com/channel/'+encodeURIComponent(data.id);
   const el=document.getElementById('yts-subscribers'),next=data.hidden?null:data.subscribers;
   if(lastCount!==null&&next!==null&&lastCount!==next){el.style.opacity='.55';setTimeout(()=>{el.textContent=fmt(next);el.style.opacity='1'},140)}
   else{el.textContent=fmt(next)}
   lastCount=next;
   document.getElementById('yts-views').textContent=fmt(data.views);
   document.getElementById('yts-videos').textContent=fmt(data.videos);
   document.getElementById('yts-goal').textContent=goal(next);
   document.getElementById('yts-status').textContent=data.hidden?'Abonnees zijn verborgen':'YouTube API • elke 60 seconden bijgewerkt';
 }
 async function fetchJSON(url,options={}){
   const response=await fetch(url,{headers:{Accept:'application/json'},...options});
   const data=await response.json();
   if(!response.ok)throw Error(data.message||'Er is iets misgegaan');
   return data;
 }
 async function refresh(){
   if(!channelId||document.hidden)return;
   try{const data=await fetchJSON(statsBase+'/'+encodeURIComponent(channelId));if(data.id===channelId){display(data);setError('')}}catch(e){setError(e.message)}
 }
 function select(data){
   if(timer)clearInterval(timer);
   channelId=data.id;lastCount=null;results.replaceChildren();display(data);setError('');
   history.replaceState(null,'',location.pathname+'?channel='+encodeURIComponent(channelId));
   timer=setInterval(refresh,60000);
 }
 form.addEventListener('submit',async event=>{
   event.preventDefault();setError('');results.replaceChildren();submit.disabled=true;submit.textContent='Zoeken…';
   try{
     const data=await fetchJSON(lookupUrl+'?'+new URLSearchParams({query:input.value.trim()}));
     if(!data.channels.length){setError('Geen kanaal gevonden. Probeer een kanaallink of @handle.');return}
     if(data.channels.length===1){select(data.channels[0]);return}
     data.channels.forEach(ch=>{
       const button=document.createElement('button');button.type='button';button.className='yts-result';
       const avatar=document.createElement('img');avatar.src=safeImage(ch.avatar)||'';avatar.alt='';
       const box=document.createElement('span');box.textContent=ch.title;
       const detail=document.createElement('small');detail.textContent=fmt(ch.subscribers)+' abonnees';box.appendChild(detail);
       button.append(avatar,box);button.addEventListener('click',()=>select(ch));results.appendChild(button);
     });
   }catch(e){setError(e.message)}finally{submit.disabled=false;submit.textContent='Zoek kanaal'}
 });
 const initial=new URLSearchParams(location.search).get('channel');
 if(initial&&/^UC[A-Za-z0-9_-]{22}$/.test(initial)){channelId=initial;refresh();timer=setInterval(refresh,60000)}
})();
</script>
@endpush
