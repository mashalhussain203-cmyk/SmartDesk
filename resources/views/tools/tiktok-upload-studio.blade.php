@extends('layouts.site-layout')

@section('title', 'TikTok Upload Studio | Mashal Studio')
@section('meta_description', 'Upload videoconcepten naar TikTok, voeg je eigen muziek toe en werk je publicatie af in TikTok.')

@push('styles')
<style>
.ts-page{min-height:90vh;padding:42px 18px 100px;color:#f4f5fc;background:radial-gradient(circle at 8% 0%,rgba(70,199,240,.10),transparent 40%),radial-gradient(circle at 89% 9%,rgba(253,64,115,.09),transparent 32%),#07090e}
.ts-container{max-width:1180px;margin:0 auto}.ts-back{display:inline-flex;gap:8px;align-items:center;text-decoration:none;color:#9ba7ba;font-size:13px;margin-bottom:28px}.ts-back:hover{color:white}
.ts-eyebrow{color:#69d8ee;letter-spacing:.19em;text-transform:uppercase;font-size:11px;font-weight:800}.ts-head{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin-bottom:30px}
.ts-head h1{margin:9px 0;font-size:clamp(38px,6vw,66px);letter-spacing:-.06em;line-height:1.03;font-weight:850}.ts-head h1 span{color:#7c90a8}
.ts-head p{max-width:670px;color:#8e9db1;font-size:14px;line-height:1.75;margin:12px 0 0}
.ts-pill{display:inline-flex;padding:10px 14px;border:1px solid #294957;background:#0e2730;color:#8de8d8;border-radius:999px;font-size:12px;font-weight:800;white-space:nowrap}
.ts-pill.off{border-color:#484359;background:#17151e;color:#d7b0c9}
.ts-grid{display:grid;grid-template-columns:minmax(0,1fr) 350px;gap:18px;align-items:start}
.ts-card{background:linear-gradient(160deg,#111823,#0d111b);border:1px solid #293242;border-radius:22px;box-shadow:0 20px 65px rgba(0,0,0,.24);padding:25px;margin-bottom:18px}
.ts-card h2{font-size:19px;margin:0 0 9px;letter-spacing:-.025em}.ts-card p{color:#99a8bd;font-size:12px;line-height:1.75;margin:4px 0 18px}
.ts-label{font-size:12px;font-weight:800;color:#e9eff8;display:block;margin:17px 0 8px}
.ts-drop{display:grid;place-items:center;gap:10px;border:1.5px dashed #577187;border-radius:17px;background:#101e2c;min-height:160px;text-align:center;padding:20px;cursor:pointer}
.ts-drop:hover,.ts-drop:focus-within{border-color:#78d6eb;background:#142b3b}.ts-drop svg{width:42px;height:42px;stroke:#67cee7;fill:none;stroke-width:1.5}
.ts-drop strong{font-size:15px}.ts-drop small{color:#90a6b9;font-size:12px}.ts-drop input{max-width:240px;width:100%;font-size:12px;color:#c5d9e8}
.ts-video{display:none;max-width:100%;width:100%;max-height:400px;background:black;border-radius:14px;margin-top:15px;object-fit:contain}.ts-video.is-visible{display:block}
.ts-pick{display:grid;grid-template-columns:1fr 1fr;gap:10px}.ts-choice{border:1px solid #3b4c5b;padding:15px;border-radius:13px;cursor:pointer;background:#141d28;display:flex;gap:10px;align-items:flex-start;font-size:13px}
.ts-choice input{accent-color:#58d6e8;margin-top:2px}.ts-choice strong{display:block}.ts-choice small{display:block;color:#9cabbc;font-size:11px;margin-top:6px;line-height:1.5}
.ts-choice:has(input:checked){border-color:#61d5e9;background:#142e38}
.ts-my-audio[hidden]{display:none}.ts-range-row{display:flex;justify-content:space-between;gap:12px;align-items:center;margin-top:15px;color:#b8c4d1;font-size:12px}
.ts-range-row input{accent-color:#69d9e6;width:min(50%,170px)}
.ts-field{display:block;width:100%;box-sizing:border-box;border:1px solid #354256;border-radius:12px;padding:14px;color:white;background:#0d1420;font:inherit;font-size:13px;outline:none;resize:vertical}
.ts-field:focus{border-color:#61d5e9}.ts-explain{font-size:11px;color:#9ba9bd;line-height:1.7;margin-top:8px}
.ts-check{display:flex;gap:10px;align-items:flex-start;font-size:12px;color:#c5cedb;line-height:1.6;margin-top:15px}.ts-check input{accent-color:#58d6e8;margin-top:4px;flex-shrink:0}
.ts-primary{border:0;width:100%;padding:17px 20px;border-radius:12px;color:#00161a;background:linear-gradient(105deg,#6ad9ee,#a0e7ea);font-weight:900;cursor:pointer;margin-top:22px;font-size:14px}
.ts-primary:disabled{opacity:.45;cursor:not-allowed}.ts-secondary{border:1px solid #486579;background:#15212e;color:#d9effc;padding:11px 16px;border-radius:10px;font-size:12px;font-weight:800;cursor:pointer;text-decoration:none;display:inline-block}
.ts-secondary:hover{border-color:#79cde1}.ts-side-item{display:flex;gap:12px;align-items:flex-start;border-top:1px solid #263345;padding:15px 0;font-size:12px;color:#aebbc9;line-height:1.65}
.ts-side-item strong{display:block;color:#edf3ff;font-size:13px}.ts-num{flex:0 0 30px;height:30px;display:grid;place-items:center;border:1px solid #41616e;border-radius:10px;color:#8ddae6;font-weight:900}
.ts-notice{border:1px solid #4b4255;border-radius:14px;padding:14px;background:#241b2a;color:#e7cadc;font-size:12px;line-height:1.65;margin:14px 0}
.ts-notice.info{border-color:#354f67;background:#112333;color:#c2dced}
.ts-account{display:flex;gap:13px;align-items:center;justify-content:space-between;flex-wrap:wrap}
.ts-progress{margin:17px 0;border:1px solid #355467;border-radius:13px;padding:16px;color:#cae6f0;background:#10232b;font-size:13px;line-height:1.65;white-space:pre-wrap}
.ts-progress[hidden]{display:none}.ts-cta-row{display:flex;flex-wrap:wrap;gap:10px;margin-top:14px}
.ts-status{font-size:12px;color:#a3c3d7;line-height:1.6;margin-top:15px}
@media(max-width:920px){.ts-grid{grid-template-columns:1fr}.ts-head{align-items:flex-start;flex-direction:column}.ts-side{display:grid;grid-template-columns:1fr 1fr;gap:16px}.ts-side>.ts-card{margin-bottom:0}}
@media(max-width:610px){.ts-page{padding:30px 12px 70px}.ts-card{padding:18px}.ts-pick,.ts-side{grid-template-columns:1fr}.ts-head h1{font-size:40px}}
</style>
@endpush

@section('content')
<section class="ts-page">
  <div class="ts-container">
    <a class="ts-back" href="{{ route('live-counts.index') }}">← {{ __('Terug naar Live Counts') }}</a>
    <header class="ts-head">
      <div>
        <div class="ts-eyebrow">Mashal Studio / Creator tools</div>
        <h1>TikTok <span>Upload Studio.</span></h1>
        <p>{{ __('Maak je video klaar, voeg eigen muziek toe, schrijf je tekst en stuur de video veilig naar je TikTok-inbox om hem daar verder te bewerken.') }}</p>
      </div>
      <span class="ts-pill {{ $connected ? '' : 'off' }}">{{ $connected ? '● TikTok gekoppeld' : '○ TikTok niet gekoppeld' }}</span>
    </header>

    @if ($errors->any())
      <div class="ts-notice" role="alert">{{ $errors->first() }}</div>
    @endif
    @if (session('success'))
      <div class="ts-notice info" role="status">{{ session('success') }}</div>
    @endif

    <div class="ts-grid">
      <div>
        <section class="ts-card">
          <h2>01 — {{ __('TikTok koppelen') }}</h2>
          <div class="ts-account">
            <p style="margin:0;flex:1;min-width:220px">
              {{ $connected ? __('Je TikTok-account is verbonden. De Studio mag videoconcepten naar jouw TikTok sturen.') : __('Log in bij Mashal Studio en geef via TikTok toestemming voor het versturen van videoconcepten.') }}
            </p>
            @guest
              <a href="{{ route('login') }}" class="ts-secondary">{{ __('Inloggen op Mashal Studio') }}</a>
            @else
              @if ($connected)
                <form method="post" action="{{ route('tiktok-studio.disconnect') }}">@csrf<button type="submit" class="ts-secondary">{{ __('Ontkoppelen') }}</button></form>
              @else
                @if ($configured)
                  <form method="post" action="{{ route('tiktok-studio.connect') }}">@csrf<button type="submit" class="ts-secondary">{{ __('TikTok koppelen →') }}</button></form>
                @else
                  <span class="ts-notice" style="margin:0">{{ __('TikTok API-instellingen ontbreken nog op de server.') }}</span>
                @endif
              @endif
            @endguest
          </div>
        </section>

        <form id="tiktokStudioForm" class="ts-card" method="post" enctype="multipart/form-data" action="{{ route('tiktok-studio.upload') }}">
          @csrf
          <h2>02 — {{ __('Video & geluid') }}</h2>
          <p>{{ __('Kies een MP4, MOV of WebM. Je kunt hier je eigen audio mengen, of later een TikTok-geluid selecteren in de TikTok-editor.') }}</p>
          <label class="ts-drop" for="tsVideo">
            <svg viewBox="0 0 48 48" aria-hidden="true"><path d="M24 34V8m-9 9 9-9 9 9M7 29v9a3 3 0 0 0 3 3h28a3 3 0 0 0 3-3v-9"/></svg>
            <strong>{{ __('Video kiezen') }}</strong>
            <small>{{ __('MP4 / MOV / WebM · maximaal 50 MB') }}</small>
            <input id="tsVideo" type="file" name="video" accept="video/mp4,video/quicktime,video/webm" required>
          </label>
          <video id="tsPreview" class="ts-video" controls playsinline></video>

          <span class="ts-label">{{ __('Geluid kiezen') }}</span>
          <div class="ts-pick">
            <label class="ts-choice"><input type="radio" name="sound_mode" value="tiktok" checked><span><strong>{{ __('TikTok-muziek') }}</strong><small>{{ __('Kies Favorieten of zoek een sound in TikTok nadat het concept is verzonden.') }}</small></span></label>
            <label class="ts-choice"><input type="radio" name="sound_mode" value="own"><span><strong>{{ __('Eigen audiobestand') }}</strong><small>{{ __('Meng muziek waarop je de benodigde rechten hebt direct met jouw video.') }}</small></span></label>
          </div>
          <div id="tsOwnAudio" class="ts-my-audio" hidden>
            <label class="ts-label" for="tsAudio">{{ __('Jouw muziekbestand') }}</label>
            <input id="tsAudio" class="ts-field" type="file" name="audio" accept=".mp3,.wav,.m4a,.aac,.ogg,.mp4,audio/*">
            <div class="ts-range-row"><label for="tsMusicVolume">{{ __('Muziekvolume') }} <output id="tsMusicValue">80%</output></label><input id="tsMusicVolume" type="range" name="music_volume" min="0" max="1" step=".05" value=".8"></div>
            <div class="ts-range-row"><label for="tsOriginalVolume">{{ __('Origineel geluid') }} <output id="tsOriginalValue">40%</output></label><input id="tsOriginalVolume" type="range" name="original_volume" min="0" max="1" step=".05" value=".4"></div>
            <label class="ts-label" for="tsMusicStart">{{ __('Start in muziekbestand (seconden)') }}</label>
            <input id="tsMusicStart" class="ts-field" type="number" name="music_start" min="0" max="3600" step=".1" value="0">
          </div>
          <div class="ts-notice info">{{ __('TikTok heeft geen openbare API om op deze website in al zijn geluiden te zoeken of een favoriet geluid rechtstreeks aan een video toe te wijzen. Voor TikTok-sounds kies je de muziek in de officiële TikTok-editor.') }}</div>

          <h2 style="margin-top:30px">03 — {{ __('Tekst & hashtags') }}</h2>
          <label class="ts-label" for="tsCaption">{{ __('Beschrijving of begeleidende tekst') }}</label>
          <textarea id="tsCaption" name="caption" class="ts-field" maxlength="2200" rows="4" placeholder="Waar gaat jouw video over?"></textarea>
          <label class="ts-label" for="tsHashtags">{{ __('Hashtags') }}</label>
          <input id="tsHashtags" name="hashtags" class="ts-field" maxlength="600" placeholder="#fyp #music #mashalstudio">
          <p class="ts-explain">{{ __('De inbox-upload van TikTok neemt beschrijving en hashtags niet automatisch over. Na versturen kun je de tekst met één klik kopiëren en in TikTok plakken.') }}</p>
          <label class="ts-check"><input type="checkbox" name="rights_confirmed" value="1" required><span>{{ __('Ik heb het recht om de geüploade video en eventuele eigen muziek te gebruiken.') }}</span></label>
          <label class="ts-check"><input type="checkbox" name="consent" value="1" required><span>{{ __('Ik geef toestemming om de video naar mijn gekoppelde TikTok-account te versturen. Ik publiceer zelf in TikTok.') }}</span></label>
          <button class="ts-primary" type="submit" id="tsSubmit" {{ $connected ? '' : 'disabled' }}>{{ __('Verstuur videoconcept naar TikTok →') }}</button>
          <div class="ts-progress" id="tsResult" role="status" aria-live="polite" hidden></div>
          <div class="ts-cta-row" id="tsAfter" hidden>
            <button type="button" class="ts-secondary" id="tsCopy">{{ __('Kopieer bijschrift + hashtags') }}</button>
            <button type="button" class="ts-secondary" id="tsCheck">{{ __('Controleer TikTok-status') }}</button>
            <a class="ts-secondary" href="https://www.tiktok.com/" target="_blank" rel="noopener noreferrer">{{ __('Open TikTok ↗') }}</a>
          </div>
          <div class="ts-status" id="tsStatus" role="status"></div>
        </form>
      </div>

      <aside class="ts-side">
        <section class="ts-card">
          <h2>{{ __('Zo werkt het') }}</h2>
          <div class="ts-side-item"><span class="ts-num">1</span><span><strong>{{ __('Koppel je account') }}</strong>{{ __('Log veilig in via TikTok. Je wachtwoord blijft bij TikTok.') }}</span></div>
          <div class="ts-side-item"><span class="ts-num">2</span><span><strong>{{ __('Upload je video') }}</strong>{{ __('Voeg optioneel je eigen muziek toe en pas volumes aan.') }}</span></div>
          <div class="ts-side-item"><span class="ts-num">3</span><span><strong>{{ __('TikTok-inbox') }}</strong>{{ __('Open de TikTok-melding, kies daar muziek en plak je tekst.') }}</span></div>
          <div class="ts-side-item"><span class="ts-num">4</span><span><strong>{{ __('Publiceer in TikTok') }}</strong>{{ __('Je bepaalt zelf het moment en de privacy van je bericht.') }}</span></div>
        </section>
        <section class="ts-card">
          <h2>{{ __('Jouw laatste concept') }}</h2>
          @if (is_array($lastUpload) && filled($lastUpload['publish_id'] ?? null))
            <p>{{ __('Verstuurd op') }} {{ $lastUpload['sent_at'] ?? '' }}. {{ __('Open TikTok om te bewerken of controleer hieronder de status.') }}</p>
            <button type="button" id="tsLastStatus" class="ts-secondary">{{ __('Status ophalen') }}</button>
          @else
            <p>{{ __('Er is nog geen video via deze uploadstudio verzonden in deze sessie.') }}</p>
          @endif
        </section>
      </aside>
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
(() => {
    'use strict';
    const form = document.getElementById('tiktokStudioForm');
    if (!form) return;
    const videoInput = document.getElementById('tsVideo');
    const preview = document.getElementById('tsPreview');
    const audioInput = document.getElementById('tsAudio');
    const ownAudio = document.getElementById('tsOwnAudio');
    const submit = document.getElementById('tsSubmit');
    const result = document.getElementById('tsResult');
    const after = document.getElementById('tsAfter');
    const status = document.getElementById('tsStatus');
    const csrf = form.querySelector('input[name="_token"]').value;
    let videoUrl;
    let copyText = @json(trim((string) (($lastUpload['caption'] ?? '') . ' ' . ($lastUpload['hashtags'] ?? ''))));
    const setResult = message => { result.textContent = message; result.hidden = false; };
    document.querySelectorAll('input[name="sound_mode"]').forEach(radio => radio.addEventListener('change', () => {
        const enabled = form.querySelector('input[name="sound_mode"]:checked').value === 'own';
        ownAudio.hidden = !enabled;
        audioInput.disabled = !enabled;
        if (!enabled) audioInput.value = '';
    }));
    audioInput.disabled = true;
    videoInput.addEventListener('change', () => {
        if (videoUrl) URL.revokeObjectURL(videoUrl);
        const file = videoInput.files && videoInput.files[0];
        preview.classList.toggle('is-visible', Boolean(file));
        if (file) { videoUrl = URL.createObjectURL(file); preview.src = videoUrl; }
        else preview.removeAttribute('src');
    });
    [['tsMusicVolume','tsMusicValue'],['tsOriginalVolume','tsOriginalValue']].forEach(([inputId, outputId]) => {
        document.getElementById(inputId).addEventListener('input', e => {
            document.getElementById(outputId).textContent = Math.round(Number(e.target.value) * 100) + '%';
        });
    });
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (!form.reportValidity() || submit.disabled) return;
        const video = videoInput.files && videoInput.files[0];
        if (!video || video.size > 50 * 1024 * 1024) { setResult('Kies een video van maximaal 50 MB.'); return; }
        submit.disabled = true;
        after.hidden = true;
        setResult('Video wordt voorbereid en veilig naar TikTok verzonden. Sluit deze pagina niet.');
        try {
            const response = await fetch(form.action, {method:'POST', body:new FormData(form), credentials:'same-origin', headers:{'Accept':'application/json'}});
            const body = await response.json();
            if (!response.ok) {
                const errors = body.errors ? Object.values(body.errors).flat().join(' ') : '';
                throw new Error(errors || body.message || 'Upload mislukt.');
            }
            setResult(body.message || 'Video is verstuurd.');
            copyText = body.caption || '';
            after.hidden = false;
        } catch (error) {
            setResult(error.message || 'De upload is niet gelukt.');
        } finally {
            submit.disabled = false;
        }
    });
    async function checkStatus() {
        status.textContent = 'TikTok-status ophalen…';
        try {
            const response = await fetch(@json(route('tiktok-studio.status')), {method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},body:'{}'});
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Status niet beschikbaar.');
            const labels = {PROCESSING_UPLOAD:'TikTok verwerkt de video.',SEND_TO_USER_INBOX:'Concept staat klaar in je TikTok-inbox.',PUBLISH_COMPLETE:'De video is gepubliceerd in TikTok.',FAILED:'TikTok heeft de upload afgewezen.'};
            status.textContent = (labels[data.status] || ('TikTok-status: ' + data.status)) + (data.fail_reason ? ' Reden: ' + data.fail_reason : '');
        } catch (error) { status.textContent = error.message || 'Kan status niet ophalen.'; }
    }
    document.getElementById('tsCheck').addEventListener('click', checkStatus);
    document.getElementById('tsLastStatus')?.addEventListener('click', checkStatus);
    document.getElementById('tsCopy').addEventListener('click', async () => {
        if (!copyText.trim()) { status.textContent = 'Je hebt nog geen bijschrift ingevuld.'; return; }
        try { await navigator.clipboard.writeText(copyText); status.textContent = 'Beschrijving en hashtags gekopieerd. Plak ze in TikTok.'; }
        catch { status.textContent = 'Kopiëren is niet gelukt. Selecteer de tekst in het formulier.'; }
    });
})();
</script>
@endpush
