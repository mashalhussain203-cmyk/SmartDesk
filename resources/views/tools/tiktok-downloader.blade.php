@extends('layouts.site-layout')

@section('title', 'TikTok Downloader | Mashal Studio')
@section('meta_description', 'Download je openbare TikTok-video als MP4 of MP3 en sla beschikbare fotoposts op via Mashal Studio.')

@push('styles')
<style>
    .ttd { min-height: 82vh; padding: 58px 20px 100px; color: #f6f7fc; background: radial-gradient(ellipse 58% 32% at 50% 4%, rgba(75,88,221,.14),transparent 90%); }
    .ttd * { box-sizing: border-box; }
    .ttd-shell { max-width: 1120px; margin: 0 auto; }
    .ttd-back { display: inline-flex; gap: 9px; align-items: center; text-decoration: none; font-size: 13px; color: #aeb6c9; margin-bottom: 32px; transition: color .2s; }
    .ttd-back:hover { color: #fff; }
    .ttd-hero { text-align: center; max-width: 780px; margin: 0 auto 36px; }
    .ttd-kicker { display: inline-flex; align-items: center; gap: 9px; border: 1px solid rgba(147,161,255,.2); background: rgba(91,104,234,.08); border-radius: 40px; padding: 8px 16px; font-size: 12px; letter-spacing: .07em; font-weight: 750; color: #b8c2ff; text-transform: uppercase; }
    .ttd-pulse { height: 7px; width: 7px; border-radius: 50%; background: #89edda; box-shadow: 0 0 10px #54d4d1; }
    .ttd h1 { margin: 21px 0 14px; font-size: clamp(38px,6vw,68px); line-height: 1.04; letter-spacing: -.052em; font-weight: 850; }
    .ttd h1 span { background: linear-gradient(100deg,#b9b4ff 4%,#79dff5 85%); color: transparent; background-clip: text; -webkit-background-clip: text; }
    .ttd-sub { color: #a5acc1; font-size: clamp(15px,2vw,17px); line-height: 1.7; margin: 0; }
    .ttd-panel { max-width: 885px; margin: 0 auto; background: linear-gradient(145deg,rgba(30,34,55,.98),rgba(15,18,30,.99)); border: 1px solid rgba(171,180,230,.17); padding: clamp(22px,4vw,40px); border-radius: 27px; box-shadow: 0 25px 85px rgba(0,0,0,.32), inset 0 1px 0 rgba(255,255,255,.05); }
    .ttd-label { display: block; margin-bottom: 11px; font-size: 13px; font-weight: 750; color: #e6e9f9; }
    .ttd-field { display: flex; align-items: stretch; gap: 8px; padding: 8px; background: rgba(8,11,21,.75); border: 1px solid rgba(190,199,255,.19); border-radius: 16px; }
    .ttd-field:focus-within { border-color: #938eff; box-shadow: 0 0 0 3px rgba(123,112,241,.13); }
    .ttd-input { flex: 1; min-width: 0; min-height: 49px; background: transparent; color: white; border: 0; outline: 0; font-size: 15px; padding: 0 12px; direction: ltr; text-align: left; }
    .ttd-input::placeholder { color: #6f788b; }
    .ttd-btn { display: inline-flex; justify-content: center; align-items: center; gap: 10px; background: linear-gradient(120deg,#8985ff,#6278ed); color: white; border: 0; cursor: pointer; border-radius: 11px; padding: 13px 22px; font-weight: 800; font-size: 14px; text-decoration: none; transition: filter .2s, transform .2s; }
    .ttd-btn:hover { filter: brightness(1.11); transform: translateY(-1px); }
    .ttd-btn:disabled { opacity: .55; cursor: progress; transform: none; }
    .ttd-btn.secondary { background: #22283c; border: 1px solid #3f4760; color: #e5e8ff; font-weight: 650; }
    .ttd-btn.secondary:hover { background: #2a3350; }
    .ttd-note { margin: 15px 0 0; font-size: 12px; line-height: 1.6; color: #8690a9; }
    .ttd-steps { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: 15px; margin: 42px 0 28px; }
    .ttd-step, .ttd-info { padding: 23px; border: 1px solid rgba(161,178,243,.12); background: rgba(22,26,41,.65); border-radius: 19px; }
    .ttd-number { display: inline-flex; height: 35px; width: 35px; border: 1px solid rgba(134,153,253,.32); border-radius: 10px; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; color: #a6acff; margin-bottom: 14px; }
    .ttd-step h3 { font-size: 16px; font-weight: 750; margin: 0 0 9px; color: #f4f6ff; }
    .ttd-step p, .ttd-info p { font-size: 13px; color: #9fa9bc; line-height: 1.7; margin: 0; }
    .ttd-info { margin-top: 20px; }
    .ttd-info h2 { font-size: 19px; margin: 0 0 10px; }
    .ttd-feedback { margin-top: 20px; color: #dce3ff; background: rgba(95,110,223,.1); border: 1px solid rgba(128,144,245,.19); border-radius: 12px; padding: 13px 16px; line-height: 1.6; }
    .ttd-feedback.error { background: rgba(220,74,91,.09); border-color: rgba(255,114,135,.3); color: #ffc2cd; }
    .ttd-result { margin-top: 24px; padding: 19px; border: 1px solid rgba(146,165,228,.15); border-radius: 16px; background: rgba(5,8,18,.5); }
    .ttd-media { display: flex; align-items: center; gap: 18px; }
    .ttd-cover { width: 91px; height: 118px; object-fit: cover; border-radius: 11px; background: #171a2c; flex: 0 0 auto; }
    .ttd-media-text { min-width: 0; }
    .ttd-media-title { font-size: 16px; font-weight: 770; line-height: 1.5; margin: 0 0 6px; overflow-wrap: anywhere; }
    .ttd-media-meta { font-size: 13px; color: #99a5bc; }
    .ttd-formats { display: flex; flex-wrap: wrap; gap: 11px; margin-top: 20px; }
    .ttd-formats .ttd-btn { flex: 1 1 155px; }
    .ttd-result-heading { font-size: 13px; color: #b8c1d5; font-weight: 720; margin: 24px 0 0; }
    .ttd-save { margin-top: 18px; display: inline-flex; }
    .ttd-hint { color: #9aa4b9; font-size: 12px; margin-top: 10px; }
    [hidden].ttd-result, [hidden].ttd-feedback, [hidden].ttd-save { display: none !important; }
    @media (max-width: 660px) {
        .ttd { padding: 32px 14px 64px; }
        .ttd-panel { border-radius: 19px; padding: 18px; }
        .ttd-steps { grid-template-columns: 1fr; margin-top: 25px; gap: 11px; }
        .ttd-field { flex-wrap: wrap; }
        .ttd-field .ttd-input { flex: 1 1 calc(100% - 130px); }
        .ttd-field .ttd-submit { flex: 1 1 100%; }
        .ttd-field .ttd-paste { flex: 0 0 auto; }
        .ttd-media { gap: 12px; }
        .ttd-cover { width: 75px; height: 100px; }
    }
</style>
@endpush

@section('content')
<main class="ttd">
    <div class="ttd-shell">
        <a href="{{ route('live-counts.index') }}" class="ttd-back">← {{ __('Terug naar Live Counts') }}</a>
        <div class="ttd-hero">
            <span class="ttd-kicker"><i class="ttd-pulse"></i> Mashal Studio · Creator tools</span>
            <h1>TikTok <span>Downloader.</span></h1>
            <p class="ttd-sub">{{ __('Plak de link van een openbare TikTok-post. Bekijk de beschikbare opties en download een MP4-video, MP3-audio of foto’s wanneer beschikbaar.') }}</p>
        </div>

        <section class="ttd-panel" aria-label="TikTok download tool">
            <form id="ttd-form">
                <label class="ttd-label" for="ttd-url">{{ __('TikTok videolink') }}</label>
                <div class="ttd-field">
                    <input class="ttd-input" id="ttd-url" name="url" type="text" inputmode="url" autocomplete="off" spellcheck="false" required placeholder="https://www.tiktok.com/@creator/video/..." maxlength="2000">
                    <button class="ttd-btn secondary ttd-paste" id="ttd-paste" type="button">{{ __('Plakken') }}</button>
                    <button class="ttd-btn ttd-submit" id="ttd-analyze" type="submit">{{ __('Download') }} <span aria-hidden="true">→</span></button>
                </div>
            </form>
            <p class="ttd-note">{{ __('Ondersteunt openbare TikTok-video- en fotolinks, inclusief vm.tiktok.com en vt.tiktok.com. Geen account of API-sleutel nodig.') }}</p>
            <div class="ttd-feedback" role="status" aria-live="polite" id="ttd-feedback" hidden></div>
            <div class="ttd-result" id="ttd-result" hidden>
                <div class="ttd-media">
                    <img class="ttd-cover" id="ttd-cover" alt="TikTok preview" hidden>
                    <div class="ttd-media-text">
                        <p class="ttd-media-title" id="ttd-title"></p>
                        <span class="ttd-media-meta" id="ttd-meta"></span>
                    </div>
                </div>
                <div class="ttd-result-heading">{{ __('Kies een beschikbaar formaat') }}</div>
                <div class="ttd-formats" id="ttd-formats"></div>
                <a class="ttd-btn ttd-save" id="ttd-save" hidden>{{ __('Bestand opslaan') }} ↓</a>
                <p class="ttd-hint" id="ttd-save-hint" hidden>{{ __('Op iPhone: tik op Bestand opslaan. Kies vervolgens Bewaar in Bestanden indien Safari dit vraagt.') }}</p>
            </div>
        </section>

        <div class="ttd-steps">
            <article class="ttd-step"><span class="ttd-number">01</span><h3>{{ __('Kopieer de TikTok-link') }}</h3><p>{{ __('Open TikTok, kies de openbare video en tik op Delen → Link kopiëren.') }}</p></article>
            <article class="ttd-step"><span class="ttd-number">02</span><h3>{{ __('Plak en analyseer') }}</h3><p>{{ __('Plak de link hierboven. We tonen de beschikbare bestandsformaten, zonder verzonnen kwaliteitsopties.') }}</p></article>
            <article class="ttd-step"><span class="ttd-number">03</span><h3>{{ __('Download je bestand') }}</h3><p>{{ __('Kies MP4, MP3 of foto’s als ZIP als deze beschikbaar zijn en bewaar het bestand op je apparaat.') }}</p></article>
        </div>

        <div class="ttd-info">
            <h2>{{ __('Hoe werkt dit?') }}</h2>
            <p>{{ __('Mashal Studio gebruikt een eigen downloader voor publiek toegankelijke TikTok-media. Beschikbare kwaliteit en een versie zonder watermerk hangen af van de oorspronkelijke TikTok-bron. Sommige posts of geluiden zijn technisch niet beschikbaar. Download alleen materiaal waarvoor je toestemming of gebruiksrechten hebt. Mashal Studio is niet verbonden aan TikTok of SSSTik.') }}</p>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';
    var form = document.getElementById('ttd-form');
    if (!form) return;
    var input = document.getElementById('ttd-url');
    var feedback = document.getElementById('ttd-feedback');
    var result = document.getElementById('ttd-result');
    var analyzeButton = document.getElementById('ttd-analyze');
    var formats = document.getElementById('ttd-formats');
    var save = document.getElementById('ttd-save');
    var saveHint = document.getElementById('ttd-save-hint');
    var currentToken = null;
    var polling = null;
    var csrf = @json(csrf_token());
    var analyzeUrl = @json(route('tiktok-downloader.analyze'));
    var prepareUrl = @json(route('tiktok-downloader.prepare'));
    var labelMap = {
        video_hd: 'HD MP4 ↓',
        video: 'MP4 video ↓',
        audio: 'MP3 audio ↓',
        photos: "Foto's (ZIP) ↓"
    };
    function showMessage(message, error) {
        feedback.textContent = message;
        feedback.classList.toggle('error', !!error);
        feedback.hidden = !message;
    }
    function resetDownload() {
        if (polling) clearTimeout(polling);
        polling = null;
        currentToken = null;
        result.hidden = true;
        formats.replaceChildren();
        save.hidden = true;
        save.removeAttribute('href');
        saveHint.hidden = true;
    }
    function busy(value) {
        analyzeButton.disabled = value;
        input.disabled = value;
        document.getElementById('ttd-paste').disabled = value;
        formats.querySelectorAll('button').forEach(function (button) { button.disabled = value; });
    }
    async function request(url, body) {
        var response = await fetch(url, {
            method: body ? 'POST' : 'GET',
            credentials: 'same-origin',
            headers: Object.assign({'Accept': 'application/json'}, body ? {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf} : {}),
            body: body ? JSON.stringify(body) : undefined
        });
        var data;
        try { data = await response.json(); }
        catch (_) { throw new Error('De server gaf geen geldig antwoord.'); }
        if (!response.ok) throw new Error(data.message || 'Er ging iets mis. Probeer opnieuw.');
        return data;
    }
    function render(data) {
        currentToken = data.token;
        document.getElementById('ttd-title').textContent = data.title || 'TikTok video';
        var meta = [];
        if (data.creator) meta.push(data.creator);
        if (Number.isFinite(data.duration)) meta.push(Math.floor(data.duration / 60) + ':' + String(data.duration % 60).padStart(2, '0'));
        if (data.photo_count) meta.push(data.photo_count + ' foto’s');
        document.getElementById('ttd-meta').textContent = meta.join(' · ') || 'Publieke TikTok-post';
        var cover = document.getElementById('ttd-cover');
        cover.hidden = !data.thumbnail;
        if (data.thumbnail && /^https:\/\//i.test(data.thumbnail)) cover.src = data.thumbnail;
        else cover.removeAttribute('src');
        formats.replaceChildren();
        (data.modes || []).forEach(function (mode) {
            if (!labelMap[mode]) return;
            var button = document.createElement('button');
            button.className = 'ttd-btn secondary';
            button.type = 'button';
            button.textContent = labelMap[mode];
            button.addEventListener('click', function () { prepare(mode); });
            formats.append(button);
        });
        result.hidden = false;
    }
    async function prepare(mode) {
        if (!currentToken) return;
        busy(true);
        save.hidden = true;
        saveHint.hidden = true;
        showMessage('Bestand wordt voorbereid…', false);
        try {
            var response = await request(prepareUrl, {token: currentToken, format: mode});
            await check(response.status_url, 0);
        } catch (error) {
            showMessage(error.message, true);
            busy(false);
        }
    }
    async function check(url, attempts) {
        if (attempts > 120) {
            busy(false);
            showMessage('De download duurt te lang. Probeer het opnieuw.', true);
            return;
        }
        try {
            var response = await request(url);
            if (response.status === 'ready' && response.download_url) {
                busy(false);
                showMessage('Je bestand staat klaar. Tik op Bestand opslaan.', false);
                save.href = response.download_url;
                save.hidden = false;
                saveHint.hidden = false;
                save.focus();
                return;
            }
            if (response.status === 'failed') throw new Error(response.message || 'Download mislukt.');
            showMessage(attempts < 3 ? 'Video wordt opgehaald…' : 'Download wordt voorbereid, dit kan even duren…', false);
            polling = setTimeout(function () { check(url, attempts + 1); }, 1800);
        } catch (error) {
            busy(false);
            showMessage(error.message, true);
        }
    }
    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        resetDownload();
        busy(true);
        showMessage('Openbare TikTok-post wordt geanalyseerd…', false);
        try {
            var response = await request(analyzeUrl, {url: input.value.trim()});
            render(response);
            showMessage('Video gevonden. Kies het formaat dat je wilt opslaan.', false);
        } catch (error) {
            showMessage(error.message, true);
        } finally {
            busy(false);
        }
    });
    document.getElementById('ttd-paste').addEventListener('click', async function () {
        try {
            input.value = await navigator.clipboard.readText();
            input.focus();
        } catch (_) {
            showMessage('Plakken is geblokkeerd. Houd het tekstveld ingedrukt en kies Plak.', true);
            input.focus();
        }
    });
})();
</script>
@endpush
