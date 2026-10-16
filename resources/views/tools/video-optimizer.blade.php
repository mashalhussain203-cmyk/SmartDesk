@extends('layouts.site-layout')

@section('title', __('TikTok Video Optimizer | Mashal Studio'))
@section('meta_description', __('Optimize MP4 and MOV videos for TikTok with lossless remuxing or high-quality FFmpeg conversion.'))

@push('styles')
<style>
    .vo-page{min-height:80vh;padding:56px 16px 100px;color:#f5f6f9;background:radial-gradient(ellipse at 50% 0,rgba(128,112,255,.13),transparent 55%),#08090e}
    .vo-shell{max-width:780px;margin:auto}
    .vo-eyebrow{text-align:center;font-size:11px;letter-spacing:.19em;text-transform:uppercase;color:#aaa1ff;font-weight:800}
    .vo-title{margin:14px 0 12px;font-size:clamp(33px,6vw,56px);line-height:1.12;text-align:center;font-weight:850;letter-spacing:-.04em}
    .vo-subtitle{max-width:620px;margin:0 auto 30px;color:#a6a7b6;text-align:center;font-size:14px;line-height:1.7}
    .vo-panel{padding:clamp(18px,4vw,32px);border-radius:24px;background:#111119;border:1px solid #30303b;box-shadow:0 24px 90px rgba(0,0,0,.27)}
    .vo-modes{display:grid;grid-template-columns:1fr 1fr;gap:6px;padding:6px;border-radius:44px;border:1px solid #41414a;background:#17171e}
    .vo-mode{min-height:80px;padding:12px 8px;border:0;border-radius:38px;background:transparent;color:#9c9ca9;font-size:clamp(13px,2.5vw,18px);font-weight:850;cursor:pointer}
    .vo-mode small{display:block;font-size:clamp(9px,1.7vw,11px);letter-spacing:.14em;margin-top:5px;font-weight:750}
    .vo-mode[aria-pressed="true"]{background:linear-gradient(105deg,#8a78ff,#aa96ff);color:#0b0915;box-shadow:0 8px 28px rgba(133,118,255,.2)}
    .vo-notice{margin-top:16px;padding:18px 20px;border:1px solid #585142;background:#24221d;border-radius:15px;text-align:center;line-height:1.65;font-size:14px}
    .vo-drop{display:grid;place-items:center;gap:16px;min-height:278px;margin-top:18px;padding:25px;text-align:center;border:2px dashed #393948;border-radius:18px;background:#101017;cursor:pointer;transition:.2s;outline-offset:4px}
    .vo-drop:hover,.vo-drop.is-dragging,.vo-drop:focus-visible{border-color:#ab9aff;background:#181625}
    .vo-drop-icon{height:64px;width:64px;border:1px solid #4d436a;border-radius:17px;display:grid;place-items:center;background:#201d32;color:#b29fff;font-size:34px}
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
    .vo-check{display:flex;align-items:center;gap:11px;margin:20px 0;color:#d9d9e2;font-size:14px;cursor:pointer}
    .vo-check input{accent-color:#9987ff;min-width:19px;height:19px}
    .vo-btn{border:0;width:100%;min-height:64px;border-radius:14px;background:linear-gradient(100deg,#8f7cff,#b5a5ff);color:#100d20;font-size:15px;font-weight:900;letter-spacing:.12em;text-transform:uppercase;cursor:pointer}
    .vo-btn:disabled{opacity:.52;cursor:not-allowed}
    .vo-progress{height:9px;background:#30303d;border-radius:10px;overflow:hidden;margin-top:18px;display:none}
    .vo-progress.is-visible{display:block}
    .vo-progress span{height:100%;width:0;display:block;background:#a996ff;transition:width .25s}
    .vo-status{min-height:24px;margin-top:12px;text-align:center;color:#c3c2d3;font-size:13px}
    .vo-status[role="alert"]{color:#ff9c9c}
    .vo-result{display:none;margin-top:20px;padding:22px;border-radius:18px;border:1px solid rgba(146,233,172,.35);background:#142019;text-align:center}
    .vo-result.is-visible{display:block}
    .vo-result h2{font-size:21px;margin:0 0 9px;color:#b5f0c5}
    .vo-result p{margin:0 0 16px;color:#b6cebd;font-size:13px;line-height:1.6}
    .vo-download{display:block;border-radius:12px;padding:18px;background:#8cd4a1;color:#092013;text-decoration:none;font-size:16px;font-weight:900}
    .vo-more{margin-top:12px;background:transparent;border:1px solid #617e6c;color:#daefe0;border-radius:12px;padding:12px 17px;cursor:pointer}
    .vo-tiktok{display:inline-block;margin-top:16px;color:#c9bfff;font-weight:650}
    .vo-tips{margin-top:22px;border:1px solid #353445;border-radius:16px;padding:20px;color:#b7b6c7;line-height:1.8;font-size:13px}
    .vo-tips strong{color:#f0eff9}
    .vo-tips ul{margin:9px 0 0;padding-inline-start:20px}
    .vo-tips li{margin:6px 0}
    @media(max-width:520px){.vo-page{padding:32px 12px 70px}.vo-panel{border-radius:18px}.vo-drop{min-height:240px}.vo-meta{gap:5px}.vo-meta div{padding:10px 6px}.vo-mode{min-height:74px}.vo-notice{font-size:12px;padding:14px}.vo-btn{font-size:13px;letter-spacing:.05em}}
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
                </div>

                <div class="vo-notice" id="vo-mode-description">{{ __('Original mode keeps the video frames untouched. Choose FFmpeg to re-encode for better compatibility. TikTok may still compress uploads.') }}</div>

                <label class="vo-drop" for="vo-file" id="vo-drop" tabindex="0">
                    <span class="vo-drop-icon" aria-hidden="true">↓</span>
                    <strong>{{ __('Drop your video here') }}</strong>
                    <span>{{ __('or tap to browse · MP4 / MOV — H.264 or H.265 (HEVC)') }}</span>
                    <b>{{ __('Maximum file size: 100 MB · Maximum duration: 15 minutes') }}</b>
                    <input id="vo-file" name="video" type="file" accept=".mp4,.mov,video/mp4,video/quicktime" hidden required>
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

                <label class="vo-check"><input type="checkbox" name="convert_hevc" id="vo-hevc" value="1"> {{ __('Convert H.265 (HEVC) to H.264 (optional)') }}</label>
                <button id="vo-submit" class="vo-btn" type="submit" disabled>⚡ {{ __('Optimize Video') }}</button>
                <div id="vo-progress" class="vo-progress" role="progressbar" aria-label="{{ __('Upload progress') }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><span id="vo-progress-fill"></span></div>
                <div class="vo-status" id="vo-status" role="status" aria-live="polite"></div>
            </form>

            <div class="vo-result" id="vo-result" aria-live="polite">
                <h2>✓ {{ __('Your video is ready') }}</h2>
                <p id="vo-result-note"></p>
                <a class="vo-download" href="#" id="vo-download" download="mashal-optimized-video.mp4">{{ __('Download optimized MP4') }} ↓</a>
                <a class="vo-tiktok" href="https://www.tiktok.com/upload" target="_blank" rel="noopener noreferrer">{{ __('Open TikTok uploader') }} ↗</a>
                <div><button type="button" class="vo-more" id="vo-again">{{ __('Optimize another video') }}</button></div>
            </div>
        </section>

        <div class="vo-tips">
            <strong>{{ __('Important notes') }}</strong>
            <ul>
                <li>{{ __('Original mode remuxes the source without changing the video or audio frames. FFmpeg mode re-encodes and cannot improve missing detail.') }}</li>
                <li>{{ __('We do not artificially turn 30 FPS into 60 or 120 FPS; source timing is preserved.') }}</li>
                <li>{{ __('TikTok decides playback resolution, FPS and compression. No tool can guarantee that TikTok will keep 4K/120 FPS.') }}</li>
                <li>{{ __('Your file is processed privately. Unclaimed exports expire after 1 hour; downloads are deleted after transfer.') }}</li>
                <li>{{ __('Auto-posting requires separate authorization with a supported TikTok publishing service. You can upload manually using the link above.') }}</li>
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
    var labels = @json([
        'choose' => __('Please choose an MP4 or MOV video.'),
        'tooLarge' => __('The video must be no larger than 100 MB.'),
        'upload' => __('Uploading video…'),
        'processing' => __('Processing video… This can take several minutes.'),
        'complete' => __('Optimization complete!'),
        'failure' => __('The video could not be optimized. Please try again.'),
        'network' => __('Network error or processing timeout. Try a shorter video.'),
        'other' => __('FFmpeg mode re-encodes using H.264 with high quality. Original frame timing is retained.'),
        'original' => __('Original mode keeps the video frames untouched. Choose FFmpeg to re-encode for better compatibility. TikTok may still compress uploads.'),
    ]);

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
        result.classList.remove('is-visible');
        progress.classList.remove('is-visible');
        setStatus('', false);
        currentFile = null;
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
            cleanup();
        };
        video.onerror = cleanup;
        video.src = objectUrl;
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
            document.getElementById('vo-mode-description').textContent = modeField.value === 'ffmpeg' ? labels.other : labels.original;
        });
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        if (!currentFile || busy) { return; }
        busy = true;
        submit.disabled = true;
        setProgress(1);
        setStatus(labels.upload, false);
        result.classList.remove('is-visible');

        var data = new FormData(form);
        data.set('video', currentFile, currentFile.name);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', form.action);
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
            if (xhr.status >= 200 && xhr.status < 300 && response.download_url) {
                setProgress(100);
                setStatus(labels.complete, false);
                document.getElementById('vo-download').href = response.download_url;
                document.getElementById('vo-result-note').textContent = response.note || '';
                result.classList.add('is-visible');
                result.scrollIntoView({behavior:'smooth',block:'nearest'});
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

    document.getElementById('vo-again').addEventListener('click', function () {
        result.classList.remove('is-visible');
        progress.classList.remove('is-visible');
        fileInput.value = '';
        currentFile = null;
        selected.classList.remove('is-visible');
        meta.hidden = true;
        submit.disabled = true;
        setStatus('', false);
        drop.scrollIntoView({behavior:'smooth',block:'center'});
    });
}());
</script>
@endsection
