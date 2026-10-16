@extends('layouts.site-layout')

@section('title', __('TikTok Video Optimizer | Mashal Studio'))
@section('meta_description', __('Optimize MP4 and MOV videos for TikTok with lossless remuxing or high-quality FFmpeg conversion.'))

@php
    $videoOptimizerLabels = [
        'choose' => __('Please choose an MP4 or MOV video.'),
        'tooLarge' => __('The video must be no larger than 100 MB.'),
        'upload' => __('Uploading video…'),
        'processing' => __('Processing video… This can take several minutes.'),
        'complete' => __('Optimization complete!'),
        'failure' => __('The video could not be optimized. Please try again.'),
        'network' => __('Network error or processing timeout. Try a shorter video.'),
        'other' => __('FFmpeg mode re-encodes using H.264 with high quality. Original frame timing is retained.'),
        'fourK' => __('Create 4K at 120 FPS using motion-compensated frame interpolation and sharpened Lanczos scaling. No slow motion. CPU rendering can take hours; max 3 minutes.'),
        'rendering' => __('Rendering 4K 120 FPS. You can keep this page open to follow the progress.'),
        'renderFailed' => __('4K processing could not finish. Check the reported server error and try again.'),
        'tooLong' => __('The 4K / 120 FPS mode supports videos of up to 3 minutes (180 seconds).'),
        'unverified' => __('Export failed verification: this download is not 4K at 120 FPS.'),
        'exportOriginal' => __('Original export: this mode does not upgrade resolution or FPS.'),
        'original' => __('Original mode keeps the video frames untouched. Choose FFmpeg to re-encode for better compatibility. TikTok may still compress uploads.'),
    ];
    $videoOptimizerPostLabels = [
        'missingKey' => __('Enter your Zernio API key first.'),
        'connecting' => __('Checking connected TikTok accounts…'),
        'noAccount' => __('No active TikTok account is connected to Zernio.'),
        'connected' => __('Choose your connected TikTok account and review your caption.'),
        'posting' => __('Uploading and submitting your video to Zernio…'),
        'error' => __('Could not contact Zernio. Please try again.'),
    ];
@endphp

@push('styles')
<style>
    .vo-page{min-height:80vh;padding:56px 16px 100px;color:#f5f6f9;background:radial-gradient(ellipse at 50% 0,rgba(132,194,25,.12),transparent 55%),#08090e}
    .vo-shell{max-width:780px;margin:auto}
    .vo-eyebrow{text-align:center;font-size:11px;letter-spacing:.19em;text-transform:uppercase;color:#b1e643;font-weight:800}
    .vo-title{margin:14px 0 12px;font-size:clamp(33px,6vw,56px);line-height:1.12;text-align:center;font-weight:850;letter-spacing:-.04em}
    .vo-subtitle{max-width:620px;margin:0 auto 30px;color:#a6a7b6;text-align:center;font-size:14px;line-height:1.7}
    .vo-panel{padding:clamp(18px,4vw,32px);border-radius:24px;background:#111119;border:1px solid #30303b;box-shadow:0 24px 90px rgba(0,0,0,.27)}
     .vo-modes{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:6px;padding:6px;border-radius:44px;border:1px solid #41414a;background:#17171e}
    .vo-mode{min-width:0;min-height:80px;padding:12px 8px;border:0;border-radius:38px;background:transparent;color:#9c9ca9;font-size:clamp(13px,2.5vw,18px);font-weight:850;cursor:pointer}
    .vo-mode small{display:block;font-size:clamp(9px,1.7vw,11px);letter-spacing:.14em;margin-top:5px;font-weight:750}
    .vo-mode[aria-pressed="true"]{background:linear-gradient(105deg,#80cc0a,#a6ee21);color:#0b0915;box-shadow:0 8px 28px rgba(133,118,255,.2)}
    .vo-notice{margin-top:16px;padding:18px 20px;border:1px solid #585142;background:#24221d;border-radius:15px;text-align:center;line-height:1.65;font-size:14px}
    .vo-drop{display:grid;place-items:center;gap:16px;min-height:278px;margin-top:18px;padding:25px;text-align:center;border:2px dashed #393948;border-radius:18px;background:#101017;cursor:pointer;transition:.2s;outline-offset:4px}
    .vo-drop:hover,.vo-drop.is-dragging,.vo-drop:focus-visible{border-color:#a1da2b;background:#1a2112}
    .vo-drop-icon{height:64px;width:64px;border:1px solid #4e6825;border-radius:17px;display:grid;place-items:center;background:#202910;color:#a9e62f;font-size:34px}
    .vo-drop strong{font-size:clamp(18px,3vw,23px);letter-spacing:-.02em}
    .vo-drop span{font-size:13px;line-height:1.65;color:#9d9daa}
    .vo-drop b{font-size:13px;line-height:1.65;color:#dedee5}
    .vo-selected{margin:17px 0 5px;padding:13px 16px;background:#181822;border:1px solid #333342;border-radius:12px;display:none;align-items:center;gap:10px;overflow-wrap:anywhere;font-size:13px}
    .vo-selected.is-visible{display:flex}
    .vo-selected-name{flex:1;min-width:0}
    .vo-meta[hidden]{display:none!important}
    .vo-meta{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:9px;margin-top:16px}
    .vo-meta div{min-width:0;background:#181821;border:1px solid #2c2c38;border-radius:12px;padding:11px;text-align:center}
    .vo-meta small{color:#9e9caa;display:block;font-size:10px;text-transform:uppercase;letter-spacing:.1em}
    .vo-meta strong{font-size:13px;display:block;margin-top:6px;overflow-wrap:anywhere}
     .vo-4k-note{border:1px solid #5a751a;border-radius:12px;color:#dae8c3;font-size:12px;line-height:1.65;padding:12px;margin:13px 0}.vo-4k-note[hidden]{display:none!important}
    .vo-check[hidden]{display:none!important}
    .vo-check{display:flex;align-items:center;gap:11px;margin:20px 0;color:#d9d9e2;font-size:14px;cursor:pointer}
    .vo-check input{accent-color:#9987ff;min-width:19px;height:19px}
    .vo-btn{border:0;width:100%;min-height:64px;border-radius:14px;background:linear-gradient(100deg,#80ca08,#a9e91c);color:#102000;font-size:15px;font-weight:900;letter-spacing:.12em;text-transform:uppercase;cursor:pointer}
    .vo-btn:disabled{opacity:.52;cursor:not-allowed}
    .vo-progress{height:9px;background:#30303d;border-radius:10px;overflow:hidden;margin-top:18px;display:none}
    .vo-progress.is-visible{display:block}
    .vo-progress span{height:100%;width:0;display:block;background:#95d60d;transition:width .25s}
    .vo-status{min-height:24px;margin-top:12px;text-align:center;color:#c3c2d3;font-size:13px}
    .vo-status[role="alert"]{color:#ff9c9c}
    .vo-result{display:none;margin-top:20px;padding:22px;border-radius:18px;border:1px solid rgba(146,233,172,.35);background:#142019;text-align:center}
    .vo-result.is-visible{display:block}
    .vo-result h2{font-size:21px;margin:0 0 9px;color:#b5f0c5}
    .vo-result p{margin:0 0 16px;color:#b6cebd;font-size:13px;line-height:1.6}
    .vo-result .vo-verified-spec{color:#eaf8cf;font-size:15px;font-weight:800}
    .vo-result .vo-verified-proof{color:#bad1dd;font-size:12px;overflow-wrap:anywhere}
    .vo-phone-link{display:block;padding:14px 16px;margin-top:12px;border-radius:12px;background:#202e42;border:1px solid #5f8ac5;color:#e4efff;font-weight:800;text-align:center;text-decoration:none}
    .vo-phone-link[hidden]{display:none!important}
    .vo-download{display:block;border-radius:12px;padding:18px;background:#95d60d;color:#152500;text-decoration:none;font-size:16px;font-weight:900}
    .vo-more{margin-top:12px;background:transparent;border:1px solid #617e6c;color:#daefe0;border-radius:12px;padding:12px 17px;cursor:pointer}
    .vo-tiktok{display:inline-block;margin-top:16px;color:#c9bfff;font-weight:650}
    .vo-autopost{margin-top:20px;padding:15px;text-align:start;border:1px solid #46504a;border-radius:13px}
    .vo-autopost summary{cursor:pointer;color:#d5e7d8;font-weight:800}
    .vo-autopost p{font-size:12px;line-height:1.7}
    .vo-post-label{display:block;margin:12px 0 5px;color:#d8e8db;font-size:12px}
    .vo-post-input{width:100%;box-sizing:border-box;padding:12px;color:#f6f6f7;background:#101914;border:1px solid #496050;border-radius:9px;font:inherit;min-height:43px}
    .vo-post-actions{display:flex;align-items:center;gap:10px;margin-top:12px;flex-wrap:wrap}
    .vo-post-btn{padding:12px 15px;background:#a1dcb2;color:#102515;border:0;border-radius:9px;font-weight:850;cursor:pointer}
    .vo-post-btn:disabled{opacity:.45;cursor:not-allowed}
    .vo-post-consent{display:flex;gap:9px;align-items:flex-start;margin:12px 0;color:#c9d8cb;font-size:12px;line-height:1.7}
    .vo-post-consent input{accent-color:#99dcaa;min-width:17px;margin-top:4px}
    .vo-post-feedback{color:#d3e4d7;font-size:12px;line-height:1.7;margin-top:12px}
    .vo-tips{margin-top:22px;border:1px solid #353445;border-radius:16px;padding:20px;color:#b7b6c7;line-height:1.8;font-size:13px}
    .vo-tips strong{color:#f0eff9}
    .vo-tips ul{margin:9px 0 0;padding-inline-start:20px}
    .vo-tips li{margin:6px 0}
    @media(max-width:520px){.vo-mode{font-size:12px;padding:8px 3px}.vo-mode small{font-size:8px;letter-spacing:0} .vo-page{padding:32px 12px 70px}.vo-panel{border-radius:18px}.vo-drop{min-height:240px}.vo-meta{gap:5px}.vo-meta div{padding:10px 6px}.vo-mode{min-height:74px}.vo-notice{font-size:12px;padding:14px}.vo-btn{font-size:13px;letter-spacing:.05em}}
</style>
@endpush

@section('content')
<section class="vo-page">
    <div class="vo-shell">
        <div class="vo-eyebrow">Mashal Studio · {{ __('Video tools') }}</div>
        <h1 class="vo-title">{{ __('TikTok Video Optimizer') }}</h1>
        <p class="vo-subtitle">{{ __('Upload an MP4/MOV video and choose between lossless stream copying and high-quality FFmpeg processing. Original resolution and playback speed are preserved.') }}</p>

        <section class="vo-panel" aria-label="{{ __('Video optimization') }}">
            <form id="vo-form" action="{{ route('video-optimizer.optimize') }}" method="post" enctype="multipart/form-data">
                @csrf
                <input id="vo-mode-value" type="hidden" name="mode" value="original">
                <div class="vo-modes" role="group" aria-label="{{ __('Processing method') }}">
                    <button class="vo-mode" type="button" data-vo-mode="original" aria-pressed="true">
                        {{ __('Original') }}
                        <small>{{ __('NO RE-ENCODING') }}</small>
                    </button>
                    <button class="vo-mode" type="button" data-vo-mode="ffmpeg" aria-pressed="false">
                        {{ __('FFmpeg') }}
                        <small>{{ __('HIGH QUALITY') }}</small>
                    </button>
                    <button class="vo-mode" type="button" data-vo-mode="4k120" aria-pressed="false">
                        4K / 120 FPS
                        <small>{{ __('ENHANCE') }}</small>
                    </button>
                </div>

                <div class="vo-notice" id="vo-mode-description">{{ __('Original mode keeps the video frames untouched. Choose FFmpeg to re-encode for better compatibility. TikTok may still compress uploads.') }}</div>

                <label class="vo-drop" for="vo-file" id="vo-drop" tabindex="0">
                    <span class="vo-drop-icon" aria-hidden="true">↓</span>
                    <strong>{{ __('Drop your video here') }}</strong>
                    <span>{{ __('or tap to browse · MP4 / MOV — H.264 or H.265 (HEVC)') }}</span>
                    <b>{{ __('Max 100 MB · 4K/120 FPS: max 3 minutes · Other modes: max 15 minutes') }}</b>
                    <input id="vo-file" name="video" type="file" accept=".mp4,.mov,video/mp4,video/quicktime" hidden>
                </label>

                <div id="vo-selected" class="vo-selected" aria-live="polite">
                    <span aria-hidden="true">▣</span><strong class="vo-selected-name" id="vo-filename"></strong>
                    <span id="vo-filesize"></span>
                </div>
                <div class="vo-meta" id="vo-meta" hidden>
                    <div><small>{{ __('Resolution') }}</small><strong id="vo-resolution">—</strong></div>
                    <div><small>{{ __('Duration') }}</small><strong id="vo-duration">—</strong></div>
                    <div><small>{{ __('File size') }}</small><strong id="vo-size">—</strong></div>
                </div>

                <p class="vo-4k-note" id="vo-4k-note" hidden>{{ __('4K 120 FPS uses motion estimation rather than blended/duplicated frames, with denoising and sharpening. This improves appearance but cannot recreate original detail lost in a low-resolution source. Maximum 3 minutes / 100 MB. Long clips require substantially more RAM, CPU and disk space than this server has.') }}</p>
                <label class="vo-check" id="vo-hevc-row"><input type="checkbox" name="convert_hevc" id="vo-hevc" value="1"> {{ __('Convert H.265 (HEVC) to H.264 (optional)') }}</label>
                <button id="vo-submit" class="vo-btn" type="submit" disabled>⚡ {{ __('Optimize Video') }}</button>
                <div id="vo-progress" class="vo-progress" role="progressbar" aria-label="{{ __('Upload progress') }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><span id="vo-progress-fill"></span></div>
                <div class="vo-status" id="vo-status" role="status" aria-live="polite"></div>
                <a id="vo-phone-ready" class="vo-phone-link" hidden href="#">{{ __('Download iPhone-compatible video (1080p / 60 FPS)') }} ↓</a>
            </form>

            <div class="vo-result" id="vo-result" aria-live="polite">
                <h2>✓ {{ __('Your video is ready') }}</h2>
                <p id="vo-result-note"></p>
                <p class="vo-verified-spec" id="vo-verified-spec" aria-live="polite"></p>
                <p class="vo-verified-proof" id="vo-verified-proof" aria-live="polite"></p>
                <a class="vo-download" href="#" id="vo-download" download="mashal-optimized-video.mp4">{{ __('Download optimized MP4') }} ↓</a>
                <a class="vo-phone-link" href="#" hidden id="vo-phone-download">{{ __('Download iPhone-compatible video (1080p / 60 FPS)') }} ↓</a>
                <a class="vo-tiktok" href="https://www.tiktok.com/upload" target="_blank" rel="noopener noreferrer">{{ __('Open TikTok uploader') }} ↗</a>
                <details class="vo-autopost" id="vo-auto-post">
                    <summary>{{ __('Auto Post via Zernio (optional)') }}</summary>
                    <p>{{ __('Connect your own Zernio API key and TikTok account. Your key is only used for this action, not stored by Mashal Studio. Posting is public and requires your explicit consent.') }}</p>
                    <label class="vo-post-label" for="vo-api-key">{{ __('Zernio API key') }}</label>
                    <input class="vo-post-input" id="vo-api-key" type="password" autocomplete="off" spellcheck="false" placeholder="sk_…">
                    <div class="vo-post-actions"><button type="button" id="vo-connect" class="vo-post-btn">{{ __('Connect Zernio') }}</button><a class="vo-tiktok" href="https://zernio.com/" target="_blank" rel="noopener noreferrer">{{ __('Get API key') }} ↗</a></div>
                    <div id="vo-post-settings" hidden>
                        <label class="vo-post-label" for="vo-account">{{ __('TikTok account') }}</label>
                        <select class="vo-post-input" id="vo-account"></select>
                        <label class="vo-post-label" for="vo-caption">{{ __('Caption (max 2200 characters)') }}</label>
                        <textarea class="vo-post-input" id="vo-caption" maxlength="2200" rows="3"></textarea>
                        <label class="vo-post-consent"><input id="vo-ai" type="checkbox"> {{ __('Mark as AI-generated content if applicable') }}</label>
                        <label class="vo-post-consent"><input id="vo-consent" type="checkbox"> {{ __('I reviewed the final video and consent to publishing it publicly on the selected TikTok account.') }}</label>
                        <button type="button" class="vo-post-btn" id="vo-publish" disabled>{{ __('Post to TikTok publicly') }}</button>
                    </div>
                    <p class="vo-post-feedback" id="vo-post-feedback" role="status" aria-live="polite"></p>
                </details>
                <div><button type="button" class="vo-more" id="vo-again">{{ __('Optimize another video') }}</button></div>
            </div>
        </section>

        <div class="vo-tips">
            <strong>{{ __('Important notes') }}</strong>
            <ul>
                <li>{{ __('Original mode remuxes the source without changing the video or audio frames. FFmpeg mode re-encodes and cannot improve missing detail.') }}</li>
                <li>{{ __('4K 120 FPS uses motion-compensated frames and Lanczos scaling with mild sharpening; it is not Real-ESRGAN AI super-resolution. Source detail cannot be guaranteed. The normal modes leave FPS unchanged.') }}</li>
                <li>{{ __('4K/120 FPS MP4 may be unsupported by iPhone Photos. Download the separate 1080p/60 FPS iPhone-compatible MP4 for saving to your camera roll and sharing.') }}</li>
                <li>{{ __('Video uploads to chat and social apps may be compressed to 30 FPS and a smaller resolution. To check the original export, inspect the MP4 downloaded directly from this website in Files, not an app-generated preview.') }}</li>
                <li>{{ __('TikTok decides playback resolution, FPS and compression. No tool can guarantee that TikTok will keep 4K/120 FPS.') }}</li>
                <li>{{ __('Your video is processed privately. 4K exports remain temporarily available for up to 6 hours.') }}</li>
                <li>{{ __('Auto-posting requires a Zernio API key and your separate consent. You can also upload manually using the link above.') }}</li>
            </ul>
        </div>
    </div>
</section>
<script>
(function () {
    'use strict';
    var form = document.getElementById('vo-form');
    var fileInput = document.getElementById('vo-file');
    var drop = document.getElementById('vo-drop');
    var submit = document.getElementById('vo-submit');
    var status = document.getElementById('vo-status');
    var selected = document.getElementById('vo-selected');
    var meta = document.getElementById('vo-meta');
    var result = document.getElementById('vo-result');
    var progress = document.getElementById('vo-progress');
    var fill = document.getElementById('vo-progress-fill');
    var modeField = document.getElementById('vo-mode-value');
    var currentFile = null;
    var busy = false;
    var optimizedToken = null;
    var renderTimer = null;
    var measuredDuration = null;
    var labels = @json($videoOptimizerLabels);
    var phoneInProgress = document.getElementById('vo-phone-ready');
    var phoneFinal = document.getElementById('vo-phone-download');
    function showPhoneDownload(response) {
        if (!response || !response.iphone_download_url) { return; }
        phoneInProgress.href = response.iphone_download_url;
        phoneInProgress.hidden = false;
        phoneInProgress.download = 'mashal-iphone-compatible-1080p60.mp4';
        phoneFinal.href = response.iphone_download_url;
        phoneFinal.hidden = false;
        phoneFinal.download = 'mashal-iphone-compatible-1080p60.mp4';
    }
    function resetPhoneLinks() {
        phoneInProgress.hidden = true;
        phoneFinal.hidden = true;
        phoneInProgress.removeAttribute('href');
        phoneFinal.removeAttribute('href');
    }

    function setStatus(text, error) {
        status.textContent = text || '';
        status.setAttribute('role', error ? 'alert' : 'status');
    }

    function setProgress(value) {
        progress.classList.add('is-visible');
        fill.style.width = value + '%';
        progress.setAttribute('aria-valuenow', String(value));
    }

    function showFile(file) {
        if (busy) { return; }
        resetPhoneLinks();
        lastVerifiedToken = null;
        result.classList.remove('is-visible');
        progress.classList.remove('is-visible');
        setStatus('', false);
        currentFile = null;
        optimizedToken = null;
        measuredDuration = null;
        submit.disabled = true;
        selected.classList.remove('is-visible');
        meta.hidden = true;

        if (!file) { return; }
        if (!/\.(mp4|mov)$/i.test(file.name)) {
            setStatus(labels.choose, true);
            return;
        }
        if (file.size > 100 * 1024 * 1024) {
            setStatus(labels.tooLarge, true);
            return;
        }

        currentFile = file;
        submit.disabled = false;
        selected.classList.add('is-visible');
        document.getElementById('vo-filename').textContent = file.name;
        document.getElementById('vo-filesize').textContent = (file.size / 1048576).toFixed(1) + ' MB';
        document.getElementById('vo-size').textContent = (file.size / 1048576).toFixed(1) + ' MB';

        var video = document.createElement('video');
        var objectUrl = URL.createObjectURL(file);
        var done = false;
        function cleanup() { if (done) { return; } done = true; video.removeAttribute('src'); video.load(); URL.revokeObjectURL(objectUrl); }
        video.preload = 'metadata';
        video.onloadedmetadata = function () {
            document.getElementById('vo-resolution').textContent = video.videoWidth + ' × ' + video.videoHeight;
            document.getElementById('vo-duration').textContent = Number.isFinite(video.duration) ? Math.round(video.duration) + 's' : '—';
            meta.hidden = false;
            measuredDuration = Number.isFinite(video.duration) ? video.duration : null;
            checkLength();
            cleanup();
        };
        video.onerror = cleanup;
        video.src = objectUrl;
    }

    function checkLength() {
        if (!currentFile || busy) { return; }
        var tooLong = modeField.value === '4k120' && measuredDuration !== null && measuredDuration > 180;
        submit.disabled = tooLong;
        if (tooLong) {
            setStatus(labels.tooLong, true);
        } else if (status.textContent === labels.tooLong) {
            setStatus('', false);
        }
    }

    fileInput.addEventListener('change', function () { showFile(fileInput.files && fileInput.files[0]); });
    drop.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); fileInput.click(); }
    });
    drop.addEventListener('dragover', function (event) { event.preventDefault(); drop.classList.add('is-dragging'); });
    drop.addEventListener('dragleave', function () { drop.classList.remove('is-dragging'); });
    drop.addEventListener('drop', function (event) {
        event.preventDefault();
        drop.classList.remove('is-dragging');
        if (event.dataTransfer && event.dataTransfer.files.length) { showFile(event.dataTransfer.files[0]); }
    });

    document.querySelectorAll('[data-vo-mode]').forEach(function (button) {
        button.addEventListener('click', function () {
            if (busy) { return; }
            modeField.value = button.getAttribute('data-vo-mode');
            document.querySelectorAll('[data-vo-mode]').forEach(function (other) {
                other.setAttribute('aria-pressed', other === button ? 'true' : 'false');
            });
            document.getElementById('vo-mode-description').textContent =
                modeField.value === '4k120' ? labels.fourK : (modeField.value === 'ffmpeg' ? labels.other : labels.original);
            document.getElementById('vo-4k-note').hidden = modeField.value !== '4k120';
            document.getElementById('vo-hevc-row').hidden = modeField.value === '4k120';
            checkLength();
        });
    });

    var lastVerifiedToken = null;

    function showVerifiedResult(response, checked) {
        var width = Number(response.width || 0);
        var height = Number(response.height || 0);
        var fps = Number(response.fps || 0);
        var is4kJob = !!response.verify_url;
        var is4k120 = ((width === 2160 && height === 3840) ||
                      (width === 3840 && height === 2160)) && Math.abs(fps - 120) < 0.1;

        busy = false;
        submit.disabled = !currentFile;
        checkLength();

        if (width <= 0 || height <= 0 || fps <= 0 || (is4kJob && (!checked || !checked.verified || !is4k120))) {
            result.classList.remove('is-visible');
            progress.classList.remove('is-visible');
            setStatus(labels.unverified, true);
            return;
        }

        optimizedToken = response.token || null;
        setProgress(100);
        setStatus(labels.complete, false);
        document.getElementById('vo-download').href = response.download_url;
        document.getElementById('vo-download').download = is4kJob
            ? 'mashal-4k-120fps.mp4'
            : 'mashal-optimized-original.mp4';
        document.getElementById('vo-result-note').textContent = response.note || '';
        document.getElementById('vo-verified-spec').textContent =
            width + ' × ' + height + ' · ' + fps.toFixed(2).replace(/\.00$/, '') + ' FPS' +
            (is4k120 ? ' · 4K / 120 FPS ✓' : ' · ' + labels.exportOriginal);
        document.getElementById('vo-verified-proof').textContent = checked
            ? ('Checked directly from the MP4 on the server · ' + (checked.bytes / 1048576).toFixed(1) +
               ' MB · SHA-256: ' + checked.sha256.slice(0, 24) + '…')
            : '';
        result.classList.add('is-visible');
        result.scrollIntoView({behavior:'smooth',block:'nearest'});
    }

    function ready(response) {
        showPhoneDownload(response);
        if (renderTimer) { clearInterval(renderTimer); renderTimer = null; }
        if (!response.verify_url) {
            showVerifiedResult(response, null);
            return;
        }
        if (lastVerifiedToken === response.token) { return; }
        lastVerifiedToken = response.token;
        busy = true;
        submit.disabled = true;
        setStatus('Verifying the actual MP4 on the server…', false);
        fetch(response.verify_url, {
            credentials: 'same-origin', cache: 'no-store',
            headers: {'Accept': 'application/json'}
        }).then(function (r) {
            return r.json().then(function (body) {
                if (!r.ok || !body.verified) {
                    throw new Error(body.message || labels.unverified);
                }
                return body;
            });
        }).then(function (proof) {
            response.width = proof.width;
            response.height = proof.height;
            response.fps = proof.fps;
            showVerifiedResult(response, proof);
        }).catch(function (error) {
            busy = false;
            submit.disabled = !currentFile;
            lastVerifiedToken = null;
            progress.classList.remove('is-visible');
            result.classList.remove('is-visible');
            setStatus(error.message || labels.unverified, true);
        });
    }

    function pollRender(url) {
        var polling = false;
        var errors = 0;

        function check() {
            if (polling) { return; }
            polling = true;
            fetch(url, {
                credentials: 'same-origin', cache: 'no-store',
                headers: {'Accept': 'application/json'}
            }).then(function (response) {
                if (!response.ok) { throw new Error(labels.network); }
                return response.json();
            }).then(function (state) {
                errors = 0;
                showPhoneDownload(state);
                if (state.status === 'ready' && state.download_url) {
                    ready(state);
                } else if (state.status === 'failed') {
                    clearInterval(renderTimer);
                    renderTimer = null;
                    busy = false;
                    submit.disabled = !currentFile;
                    progress.classList.remove('is-visible');
                    setStatus(state.message || labels.renderFailed, true);
                } else {
                    setProgress(Math.min(99, Math.max(90, 90 + Math.round((state.progress || 0) * .09))));
                    setStatus(labels.rendering + ' ' + (state.progress || 0) + '%', false);
                }
            }).catch(function () {
                errors += 1;
                if (errors >= 6) {
                    clearInterval(renderTimer);
                    renderTimer = null;
                    busy = false;
                    submit.disabled = !currentFile;
                    progress.classList.remove('is-visible');
                    setStatus(labels.network, true);
                }
            }).finally(function () { polling = false; });
        }

        check();
        renderTimer = setInterval(check, 4000);
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        if (!currentFile || busy) { return; }
        if (modeField.value === '4k120' && measuredDuration !== null && measuredDuration > 180) {
            setStatus(labels.tooLong, true);
            return;
        }
        busy = true;
        submit.disabled = true;
        setProgress(1);
        setStatus(labels.upload, false);
        result.classList.remove('is-visible');
        resetPhoneLinks();

        lastVerifiedToken = null;
        var data = new FormData(form);
        data.set('video', currentFile, currentFile.name);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', form.action);
        xhr.timeout = 120000;
        xhr.responseType = 'json';
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.upload.onprogress = function (event) {
            if (event.lengthComputable) { setProgress(Math.min(90, Math.round(event.loaded / event.total * 90))); }
        };
        xhr.upload.onload = function () { setProgress(90); setStatus(labels.processing, false); };
        xhr.onload = function () {
            busy = false;
            submit.disabled = !currentFile;
            var response = xhr.response || {};
            if (xhr.status === 202 && response.processing && response.status_url) {
                busy = true;
                submit.disabled = true;
                setProgress(90);
                setStatus(labels.rendering, false);
                pollRender(response.status_url);
            } else if (xhr.status >= 200 && xhr.status < 300 && response.download_url) {
                ready(response);
            } else {
                progress.classList.remove('is-visible');
                var errorText = response.message || labels.failure;
                if (response.errors) {
                    var first = Object.keys(response.errors)[0];
                    if (first && response.errors[first] && response.errors[first][0]) { errorText = response.errors[first][0]; }
                }
                setStatus(errorText, true);
            }
        };
        xhr.onerror = xhr.ontimeout = function () {
            busy = false;
            submit.disabled = !currentFile;
            progress.classList.remove('is-visible');
            setStatus(labels.network, true);
        };
        xhr.send(data);
    });


    var apiKey = document.getElementById('vo-api-key');
    var account = document.getElementById('vo-account');
    var settings = document.getElementById('vo-post-settings');
    var connect = document.getElementById('vo-connect');
    var publish = document.getElementById('vo-publish');
    var consent = document.getElementById('vo-consent');
    var feedback = document.getElementById('vo-post-feedback');
    var postLabels = @json($videoOptimizerPostLabels);

    function postMessage(message, error) {
        feedback.textContent = message;
        feedback.style.color = error ? '#ffaaaa' : '#d3e4d7';
    }

    function postRequest(url, data) {
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        }).then(function (response) {
            return response.json().then(function (body) {
                return { ok: response.ok, data: body };
            });
        });
    }

    apiKey.addEventListener('input', function () {
        settings.hidden = true;
        consent.checked = false;
        publish.disabled = true;
        postMessage('', false);
    });

    consent.addEventListener('change', function () {
        publish.disabled = !consent.checked || !account.value || !optimizedToken;
    });

    connect.addEventListener('click', function () {
        if (!apiKey.value.trim()) { postMessage(postLabels.missingKey, true); return; }
        connect.disabled = true;
        settings.hidden = true;
        postMessage(postLabels.connecting, false);
        postRequest(@json(route('video-optimizer.zernio.accounts')), {api_key: apiKey.value.trim()})
            .then(function (result) {
                if (!result.ok) { throw new Error(result.data.message || postLabels.error); }
                account.replaceChildren();
                (result.data.accounts || []).forEach(function (item) {
                    var option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = '@' + item.name;
                    account.appendChild(option);
                });
                if (!account.options.length) { throw new Error(postLabels.noAccount); }
                settings.hidden = false;
                publish.disabled = true;
                consent.checked = false;
                postMessage(postLabels.connected, false);
            })
            .catch(function (error) { postMessage(error.message || postLabels.error, true); })
            .finally(function () { connect.disabled = false; });
    });

    publish.addEventListener('click', function () {
        if (!optimizedToken || !consent.checked || !account.value) { return; }
        publish.disabled = true;
        connect.disabled = true;
        postMessage(postLabels.posting, false);
        postRequest(@json(route('video-optimizer.zernio.publish')), {
            api_key: apiKey.value.trim(),
            token: optimizedToken,
            account_id: account.value,
            caption: document.getElementById('vo-caption').value,
            ai_generated: document.getElementById('vo-ai').checked,
            consent: true
        }).then(function (result) {
            if (!result.ok) { throw new Error(result.data.message || postLabels.error); }
            postMessage(result.data.message || '', false);
        }).catch(function (error) {
            postMessage(error.message || postLabels.error, true);
        }).finally(function () {
            connect.disabled = false;
            publish.disabled = !consent.checked || !optimizedToken;
        });
    });

    fetch(@json(route('video-optimizer.active')), {
        credentials: 'same-origin', cache: 'no-store',
        headers: {'Accept': 'application/json'}
    }).then(function (response) {
        if (!response.ok) { return null; }
        return response.json();
    }).then(function (state) {
        if (!state || !state.active || !state.status_url || busy) { return; }
        busy = true;
        submit.disabled = true;
        setProgress(90);
        setStatus(labels.rendering, false);
        pollRender(state.status_url);
    }).catch(function () {
        // Recovery is optional; normal new uploads remain available.
    });

    document.getElementById('vo-again').addEventListener('click', function () {
        resetPhoneLinks();
        if (renderTimer) { clearInterval(renderTimer); renderTimer = null; }
        result.classList.remove('is-visible');
        progress.classList.remove('is-visible');
        fileInput.value = '';
        currentFile = null;
        optimizedToken = null;
        measuredDuration = null;
        selected.classList.remove('is-visible');
        meta.hidden = true;
        submit.disabled = true;
        setStatus('', false);
        settings.hidden = true;
        consent.checked = false;
        publish.disabled = true;
        postMessage('', false);
        drop.scrollIntoView({behavior:'smooth',block:'center'});
    });
}());
</script>
@endsection
