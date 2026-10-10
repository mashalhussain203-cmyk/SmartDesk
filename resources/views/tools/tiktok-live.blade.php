@extends('layouts.site-layout')

@section('title', 'TikTok LIVE delen | Live Counts | Mashal Studio')
@section('meta_description', 'Deel een TikTok LIVE-link met echte kijkers via WhatsApp, Telegram en je apparaat.')

@push('styles')
<style>
    .live-share-page { min-height:calc(100vh - 72px);padding:56px 16px 104px;color:#f5f7fa;background:radial-gradient(circle at 50% -160px,rgba(123,112,255,.18),transparent 540px),linear-gradient(180deg,#07080b,#050608); }
    .live-share-shell { max-width:820px;margin:0 auto; }
    .live-share-back { display:inline-flex;align-items:center;gap:9px;color:#a9a4ff;text-decoration:none;font-size:13px;font-weight:700; }
    .live-share-back:hover { color:#d5d1ff; }
    .live-share-hero { text-align:center;margin:42px auto 36px; }
    .live-share-eyebrow {display:inline-block;font-size:11px;font-weight:850;letter-spacing:.13em;text-transform:uppercase;color:#a9a4ff;}
    .live-share-hero h1 {margin:16px 0 12px;font-size:clamp(35px,6vw,60px);line-height:1.05;letter-spacing:-.05em;font-weight:780;color:#fff;}
    .live-share-hero p {margin:0 auto;max-width:580px;font-size:14px;line-height:1.8;color:#9ca4b3;}
    .live-share-panel {border:1px solid rgba(255,255,255,.1);border-radius:23px;padding:28px;background:linear-gradient(155deg,rgba(23,25,34,.98),rgba(11,13,19,.98));box-shadow:0 20px 55px rgba(0,0,0,.24);}
    .live-share-panel label {display:block;font-size:13px;color:#e9ebf2;font-weight:750;margin-bottom:10px;}
    .live-share-input-row {display:flex;gap:10px;}
    .live-share-input {min-width:0;flex:1;padding:15px 16px;color:#f5f7fa;font-size:14px;border:1px solid rgba(255,255,255,.16);border-radius:12px;background:#090b10;outline:none;}
    .live-share-input:focus {border-color:#a9a4ff;box-shadow:0 0 0 3px rgba(123,112,255,.18);}
    .live-share-primary {border:0;background:#8178ff;color:white;border-radius:12px;padding:13px 20px;font-weight:800;font-size:13px;cursor:pointer;white-space:nowrap;}
    .live-share-primary:hover {background:#938bff;}
    .live-share-help {color:#8f98a7;font-size:12px;line-height:1.6;margin:12px 0 0;}
    .live-share-error {margin:12px 0 0;color:#ffb6b6;font-size:13px;font-weight:650;}
    .live-share-result {margin-top:18px;padding:22px;border:1px solid rgba(139,128,255,.3);border-radius:18px;background:rgba(123,112,255,.06);}
    .live-share-result[hidden] {display:none;}
    .live-share-result strong {font-size:20px;color:white;display:block;word-break:break-all;}
    .live-share-url {margin:9px 0 16px;color:#b6b2ff;word-break:break-all;font-size:13px;line-height:1.6;}
    .live-share-actions {display:flex;flex-wrap:wrap;gap:10px;}
    .live-share-action {display:inline-flex;align-items:center;justify-content:center;padding:12px 15px;border-radius:11px;border:1px solid rgba(255,255,255,.2);background:#171a23;color:#fff;text-decoration:none;font-size:12px;font-weight:750;cursor:pointer;}
    .live-share-action:hover {background:#242736;}
    .live-share-note {padding:18px 4px 0;color:#929ba8;font-size:12px;line-height:1.8;}
    .live-share-feedback {font-size:12px;color:#9ef0c2;margin:14px 0 0;min-height:16px;}
    @media(max-width:580px){.live-share-page{padding-top:32px}.live-share-hero{margin:30px auto}.live-share-panel{padding:18px}.live-share-input-row{flex-direction:column}.live-share-input,.live-share-primary{width:100%;box-sizing:border-box}.live-share-actions > *{flex:1 1 120px}}
</style>
@endpush

@section('content')
<section class="live-share-page">
    <div class="live-share-shell">
        <a class="live-share-back" href="{{ route('live-counts.index') }}">← {{ __('Terug naar Live Counts') }}</a>
        <header class="live-share-hero">
            <span class="live-share-eyebrow">Mashal Studio · Live Counts</span>
            <h1>{{ __('TikTok LIVE delen') }}</h1>
            <p>{{ __('Plak de link van een TikTok LIVE en deel deze eenvoudig met echte kijkers. Je andere Live Counts-tools blijven ongewijzigd.') }}</p>
        </header>

        <div class="live-share-panel">
            <form id="live-share-form" novalidate>
                <label for="live-share-input">{{ __('TikTok LIVE-link') }}</label>
                <div class="live-share-input-row">
                    <input class="live-share-input" id="live-share-input" type="url" placeholder="https://vm.tiktok.com/t/..." autocomplete="url" required maxlength="2048" aria-describedby="live-share-help live-share-error">
                    <button class="live-share-primary" type="submit">{{ __('Link instellen') }}</button>
                </div>
                <p class="live-share-help" id="live-share-help">{{ __('TikTok-profiel-, LIVE- en verkorte vm.tiktok.com- of vt.tiktok.com-links worden ondersteund.') }}</p>
                <p class="live-share-error" id="live-share-error" role="alert" hidden></p>
            </form>

            <section class="live-share-result" id="live-share-result" hidden aria-label="{{ __('Link delen') }}">
                <strong id="live-share-account"></strong>
                <p class="live-share-url" id="live-share-url"></p>
                <div class="live-share-actions">
                    <a class="live-share-action" id="live-share-open" href="#" target="_blank" rel="noopener noreferrer">{{ __('Open TikTok LIVE ↗') }}</a>
                    <button class="live-share-action" id="live-share-copy" type="button">{{ __('Kopieer link') }}</button>
                    <button class="live-share-action" id="live-share-native" type="button" hidden>{{ __('Delen') }}</button>
                    <a class="live-share-action" id="live-share-whatsapp" href="#" target="_blank" rel="noopener noreferrer">WhatsApp ↗</a>
                    <a class="live-share-action" id="live-share-telegram" href="#" target="_blank" rel="noopener noreferrer">Telegram ↗</a>
                </div>
                <p class="live-share-feedback" id="live-share-feedback" role="status" aria-live="polite"></p>
            </section>
            <p class="live-share-note">{{ __('Deze tool deelt je link met mensen die zelf besluiten te kijken. Bij verkorte links controleren we niet of de bestemming LIVE is. TikTok toont zelf de echte LIVE-status en het kijkersaantal; deze pagina voegt geen automatische kijkers toe.') }}</p>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(() => {
    'use strict';
    const form = document.getElementById('live-share-form');
    const input = document.getElementById('live-share-input');
    const result = document.getElementById('live-share-result');
    const error = document.getElementById('live-share-error');
    const account = document.getElementById('live-share-account');
    const shownUrl = document.getElementById('live-share-url');
    const openLink = document.getElementById('live-share-open');
    const copyButton = document.getElementById('live-share-copy');
    const shareButton = document.getElementById('live-share-native');
    const whatsappLink = document.getElementById('live-share-whatsapp');
    const telegramLink = document.getElementById('live-share-telegram');
    const feedback = document.getElementById('live-share-feedback');
    let currentUrl = '';

    function canonicalize(value) {
        try {
            const url = new URL(value.trim());
            if (url.protocol !== 'https:' || url.username || url.password || url.port) return null;
            const hostname = url.hostname.toLowerCase();

            // TikTok-sharelinks are redirects; preserve them without guessing their destination.
            if (['vm.tiktok.com', 'vt.tiktok.com'].includes(hostname)) {
                if (!/^\/(?:t\/)?[A-Za-z0-9_-]{6,128}\/?$/.test(url.pathname)) return null;
                return { name: 'TikTok-link (verkort)', url: url.origin + url.pathname };
            }

            if (!['tiktok.com', 'www.tiktok.com', 'm.tiktok.com'].includes(hostname)) return null;
            const match = url.pathname.match(/^\/@([A-Za-z0-9._]{2,24})(?:\/live)?\/?$/);
            return match ? { name: '@' + match[1], url: 'https://www.tiktok.com/@' + match[1] + '/live' } : null;
        } catch (_) {
            return null;
        }
    }

    function renderLink() {
        const parsed = canonicalize(input.value);
        feedback.textContent = '';
        if (!parsed) {
            currentUrl = '';
            result.hidden = true;
            error.textContent = 'Vul een geldige TikTok-profiel-, LIVE- of verkorte TikTok-link in.';
            error.hidden = false;
            return;
        }
        error.hidden = true;
        currentUrl = parsed.url;
        account.textContent = parsed.name;
        shownUrl.textContent = currentUrl;
        openLink.href = currentUrl;
        const invitation = 'Bekijk deze TikTok LIVE: ' + currentUrl;
        whatsappLink.href = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(invitation);
        telegramLink.href = 'https://t.me/share/url?url=' + encodeURIComponent(currentUrl) + '&text=' + encodeURIComponent('Bekijk deze TikTok LIVE');
        shareButton.hidden = typeof navigator.share !== 'function';
        result.hidden = false;
        const next = new URL(window.location.href);
        next.searchParams.set('live', currentUrl);
        history.replaceState(null, '', next.pathname + next.search);
    }

    form.addEventListener('submit', event => {
        event.preventDefault();
        renderLink();
    });

    copyButton.addEventListener('click', async () => {
        if (!currentUrl) return;
        try {
            if (!navigator.clipboard || !navigator.clipboard.writeText) throw new Error('Clipboard unavailable');
            await navigator.clipboard.writeText(currentUrl);
            feedback.textContent = 'Link gekopieerd.';
        } catch (_) {
            input.value = currentUrl;
            input.focus();
            input.select();
            feedback.textContent = 'Selecteer en kopieer de link hierboven.';
        }
    });

    shareButton.addEventListener('click', async () => {
        if (!currentUrl || typeof navigator.share !== 'function') return;
        try {
            await navigator.share({ title: 'TikTok LIVE', text: 'Bekijk deze TikTok LIVE', url: currentUrl });
        } catch (_) { /* A cancelled share requires no error. */ }
    });

    const initial = new URLSearchParams(window.location.search).get('live');
    if (initial) {
        input.value = initial;
        renderLink();
    }
})();
</script>
@endpush
