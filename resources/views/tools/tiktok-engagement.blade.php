@extends('layouts.site-layout')

@php
    $serviceMap = [
        'hearts' => [
            'label' => 'Hearts',
            'icon' => '/icons/live-heart.svg?v=20261007-4',
        ],
        'comments' => [
            'label' => 'Comments Hearts',
            'icon' => '/icons/live-comment.svg?v=20261007-4',
        ],
        'favorites' => [
            'label' => 'Favorites',
            'icon' => '/icons/live-favorite.svg?v=1',
        ],
    ];

    $currentService = $selectedService ?? '';
    $currentLabel = $serviceMap[$currentService]['label'] ?? 'TikTok Services';
@endphp

@section('title', $currentLabel.' | Mashal Studio')
@section('meta_description', 'Gebruik de publieke Zefoy-flow vanuit Mashal Studio voor TikTok service-status, lookup en cooldowninformatie.')

@push('styles')
<style>
    .zf-page {
        min-height: calc(100vh - 72px);
        padding: 42px 0 96px;
        color: #f3f5f7;
        background:
            radial-gradient(circle at 50% -150px, rgba(123,112,255,.16), transparent 460px),
            linear-gradient(180deg, #07080b 0%, #050608 100%);
    }

    .zf-shell {
        width: min(calc(100% - 30px), 960px);
        margin: 0 auto;
    }

    .zf-top {
        margin-bottom: 22px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    .zf-back {
        color: #929aa6;
        text-decoration: none;
        font-size: 11px;
        font-weight: 760;
    }

    .zf-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 10px;
        border: 1px solid rgba(123,112,255,.2);
        border-radius: 999px;
        color: #a9a2ff;
        background: rgba(123,112,255,.06);
        font-size: 9px;
        font-weight: 850;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .zf-pill i {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #6ee7a8;
        box-shadow: 0 0 10px rgba(110,231,168,.7);
    }

    .zf-hero {
        margin: 0 auto 28px;
        text-align: center;
    }

    .zf-title {
        margin: 0;
        color: #fff;
        font-size: clamp(38px, 6vw, 64px);
        line-height: 1;
        font-weight: 740;
        letter-spacing: -.05em;
    }

    .zf-subtitle {
        max-width: 610px;
        margin: 13px auto 0;
        color: #737c89;
        font-size: 11px;
        line-height: 1.65;
    }

    .zf-services {
        display: grid;
        gap: 12px;
    }

    .zf-service {
        min-height: 142px;
        padding: 20px;
        position: relative;
        display: grid;
        grid-template-columns: minmax(0,1fr) 56px;
        align-items: center;
        gap: 18px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 20px;
        color: inherit;
        background:
            radial-gradient(circle at 100% 0%, rgba(123,112,255,.09), transparent 36%),
            linear-gradient(180deg, rgba(15,18,24,.94), rgba(9,11,15,.96));
        text-decoration: none;
        box-shadow: 0 18px 44px rgba(0,0,0,.18);
    }

    .zf-service.is-disabled {
        opacity: .56;
        pointer-events: none;
    }

    .zf-service-name {
        color: #f4f6f8;
        font-size: 26px;
        line-height: 1;
        font-weight: 700;
        letter-spacing: -.035em;
    }

    .zf-service-copy {
        margin-top: 9px;
        color: #707986;
        font-size: 10px;
        line-height: 1.6;
    }

    .zf-service-status {
        margin-top: 14px;
        display: inline-flex;
        align-items: center;
        padding: 6px 9px;
        border-radius: 8px;
        color: #9ea6b1;
        background: rgba(255,255,255,.04);
        font-size: 8px;
        font-weight: 850;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .zf-service-status.is-live {
        color: #75dda1;
        background: rgba(110,231,168,.08);
    }

    .zf-service-status.is-off {
        color: #ef9393;
        background: rgba(244,125,125,.08);
    }

    .zf-arrow {
        width: 56px;
        height: 56px;
        display: grid;
        place-items: center;
        border: 1px solid rgba(149,140,255,.3);
        border-radius: 15px;
        color: #fff;
        background: linear-gradient(135deg, #7b70ff, #6258e8);
        font-size: 28px;
        font-weight: 700;
    }

    .zf-panel {
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 22px;
        background:
            radial-gradient(circle at 100% 0%, rgba(123,112,255,.08), transparent 38%),
            linear-gradient(180deg, rgba(15,18,24,.95), rgba(9,11,15,.98));
        box-shadow: 0 24px 64px rgba(0,0,0,.24);
    }

    .zf-panel-head {
        padding: 25px 22px 18px;
        text-align: center;
        border-bottom: 1px solid rgba(255,255,255,.065);
    }

    .zf-panel-title {
        margin: 0;
        color: #fff;
        font-size: 30px;
        font-weight: 720;
        letter-spacing: -.04em;
    }

    .zf-search {
        padding: 20px;
        display: grid;
        grid-template-columns: minmax(0,1fr) auto;
        gap: 9px;
    }

    .zf-input {
        width: 100%;
        min-width: 0;
        height: 56px;
        padding: 0 15px;
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 12px;
        outline: 0;
        color: #eef1f5;
        background: rgba(255,255,255,.025);
        font-size: 12px;
    }

    .zf-input::placeholder {
        color: #5f6876;
    }

    .zf-search-button {
        min-height: 56px;
        padding: 0 21px;
        border: 1px solid rgba(149,140,255,.4);
        border-radius: 12px;
        color: #fff;
        background: linear-gradient(135deg, #7b70ff, #6157e9);
        font-size: 11px;
        font-weight: 850;
        cursor: pointer;
    }

    .zf-error {
        margin: 0 20px 20px;
        padding: 11px 13px;
        border: 1px solid rgba(244,125,125,.16);
        border-radius: 11px;
        color: #ef9b9b;
        background: rgba(244,125,125,.055);
        font-size: 10px;
    }

    .zf-result {
        margin: 0 20px 20px;
        padding: 20px;
        border: 1px solid rgba(255,255,255,.075);
        border-radius: 16px;
        background: rgba(255,255,255,.018);
    }

    .zf-loading {
        color: #8490a0;
        text-align: center;
        font-size: 11px;
        font-weight: 750;
    }

    .zf-cooldown {
        color: #7eaef5;
        text-align: center;
        font-size: 18px;
        line-height: 1.45;
        font-weight: 780;
    }

    .zf-blocked {
        color: #ec9a9a;
        text-align: center;
        font-size: 12px;
        line-height: 1.55;
        font-weight: 700;
    }

    .zf-video-card {
        text-align: center;
    }

    .zf-username {
        color: #8f83ff;
        font-size: 19px;
        font-weight: 850;
    }

    .zf-caption {
        max-width: 560px;
        margin: 9px auto 0;
        color: #d9dde3;
        font-size: 11px;
        line-height: 1.55;
    }

    .zf-age {
        margin-top: 7px;
        color: #7c8592;
        font-size: 10px;
    }

    .zf-hearts {
        margin-top: 9px;
        color: #6ee7a8;
        font-size: 15px;
        font-weight: 800;
    }

    .zf-limits {
        margin-top: 18px;
    }

    .zf-limit-select {
        width: 100%;
        height: 52px;
        padding: 0 13px;
        border: 1px solid rgba(255,255,255,.10);
        border-radius: 11px;
        color: #eef1f5;
        background: #0d1117;
        font-size: 11px;
    }

    .zf-readonly {
        margin-top: 11px;
        color: #5e6875;
        font-size: 8px;
        line-height: 1.55;
    }

    @media (max-width: 620px) {
        .zf-page {
            padding-top: 30px;
        }

        .zf-search {
            grid-template-columns: 1fr;
        }

        .zf-search-button {
            width: 100%;
        }

        .zf-service {
            min-height: 128px;
        }

        .zf-service-name {
            font-size: 23px;
        }
    }
</style>
@endpush

@section('content')
<section class="zf-page">
    <div class="zf-shell">
        <div class="zf-top">
            <a class="zf-back" href="{{ route('live-counts.index') }}">← Live Counts</a>
            <div class="zf-pill"><i></i> Zefoy public flow</div>
        </div>

        @if($currentService === '')
            <header class="zf-hero">
                <h1 class="zf-title">TikTok Services.</h1>
                <p class="zf-subtitle">
                    De status hieronder wordt opgehaald via de publieke Zefoy-pagina.
                    Kies een ondersteunde service om dezelfde lookup/cooldown-flow vanuit Mashal te gebruiken.
                </p>
            </header>

            <div
                class="zf-services"
                id="zf-services"
                data-status-endpoint="{{ route('tiktok-engagement.services') }}"
            >
                @php
                    $cards = [
                        ['key' => 'followers', 'label' => 'Followers', 'enabled' => false, 'copy' => 'Zefoy service status'],
                        ['key' => 'hearts', 'label' => 'Hearts', 'enabled' => true, 'copy' => 'Open de publieke Zefoy Hearts lookup'],
                        ['key' => 'comments', 'label' => 'Comments Hearts', 'enabled' => true, 'copy' => 'Open de publieke Zefoy Comments Hearts lookup'],
                        ['key' => 'views', 'label' => 'Views', 'enabled' => false, 'copy' => 'Zefoy service status'],
                        ['key' => 'shares', 'label' => 'Shares', 'enabled' => false, 'copy' => 'Zefoy service status'],
                        ['key' => 'favorites', 'label' => 'Favorites', 'enabled' => true, 'copy' => 'Open de publieke Zefoy Favorites lookup'],
                    ];
                @endphp

                @foreach($cards as $card)
                    @if($card['enabled'])
                        <a
                            class="zf-service"
                            href="{{ route('tiktok-engagement.index', ['service' => $card['key']]) }}"
                            data-zefoy-service="{{ $card['key'] }}"
                        >
                    @else
                        <div
                            class="zf-service is-disabled"
                            data-zefoy-service="{{ $card['key'] }}"
                        >
                    @endif
                            <div>
                                <div class="zf-service-name">{{ $card['label'] }}</div>
                                <div class="zf-service-copy">{{ $card['copy'] }}</div>
                                <span class="zf-service-status" data-zefoy-status>Checking Zefoy…</span>
                            </div>
                            <div class="zf-arrow" aria-hidden="true">→</div>
                    @if($card['enabled'])
                        </a>
                    @else
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <header class="zf-hero">
                <h1 class="zf-title">{{ $currentLabel }}</h1>
                <p class="zf-subtitle">
                    Plak de TikTok-video. Mashal opent daarna de publieke Zefoy-flow,
                    voert daar alleen de lookup uit en toont hier hun cooldown of video-resultaat.
                </p>
            </header>

            <div class="zf-panel">
                <div class="zf-panel-head">
                    <h2 class="zf-panel-title">{{ $currentLabel }}</h2>
                </div>

                <form
                    class="zf-search"
                    method="POST"
                    action="{{ route('tiktok-engagement.lookup') }}"
                >
                    @csrf
                    <input type="hidden" name="service" value="{{ $currentService }}">
                    <input
                        class="zf-input"
                        type="url"
                        name="url"
                        value="{{ old('url', $videoUrl ?? '') }}"
                        placeholder="Enter Video URL"
                        autocomplete="off"
                        required
                    >
                    <button class="zf-search-button" type="submit">⌕ Search</button>
                </form>

                @error('url')
                    <div class="zf-error">{{ $message }}</div>
                @enderror

                @isset($videoId)
                    <div
                        class="zf-result"
                        id="zf-result"
                        data-endpoint="{{ route('tiktok-engagement.stats', ['videoId' => $videoId]) }}"
                        data-video-url="{{ $videoUrl }}"
                        data-service="{{ $currentService }}"
                    >
                        <div class="zf-loading">Checking Zefoy…</div>
                    </div>
                @endisset
            </div>
        @endif
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    var servicesRoot = document.getElementById('zf-services');

    if (servicesRoot) {
        var statusEndpoint = servicesRoot.getAttribute('data-status-endpoint') || '';

        if (statusEndpoint) {
            fetch(statusEndpoint, {
                headers: { 'Accept': 'application/json' },
                cache: 'no-store',
                credentials: 'same-origin'
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Zefoy status failed');
                    }
                    return response.json();
                })
                .then(function (data) {
                    if (!data || !Array.isArray(data.services)) {
                        return;
                    }

                    data.services.forEach(function (service) {
                        var card = servicesRoot.querySelector(
                            '[data-zefoy-service="' + service.key + '"]'
                        );

                        if (!card) {
                            return;
                        }

                        var status = card.querySelector('[data-zefoy-status]');

                        if (!status) {
                            return;
                        }

                        status.textContent = service.status
                            || (service.state === 'available' ? 'Available' : service.state);

                        status.classList.toggle(
                            'is-live',
                            service.state === 'available'
                        );
                        status.classList.toggle(
                            'is-off',
                            service.state === 'unavailable'
                        );
                    });
                })
                .catch(function () {
                    servicesRoot
                        .querySelectorAll('[data-zefoy-status]')
                        .forEach(function (status) {
                            status.textContent = 'Zefoy unavailable';
                            status.classList.add('is-off');
                        });
                });
        }
    }

    var result = document.getElementById('zf-result');

    if (!result) {
        return;
    }

    var endpoint = result.getAttribute('data-endpoint') || '';
    var videoUrl = result.getAttribute('data-video-url') || '';
    var service = result.getAttribute('data-service') || 'comments';

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderPayload(data) {
        var html = '';

        if (!data) {
            result.innerHTML = '<div class="zf-blocked">Geen Zefoy-response ontvangen.</div>';
            return;
        }

        if (data.state === 'cooldown' && data.cooldown) {
            html = '<div class="zf-cooldown">'
                + escapeHtml(data.cooldown.message || 'Please wait before trying again.')
                + '</div>';
            result.innerHTML = html;
            return;
        }

        if (data.state === 'blocked') {
            result.innerHTML =
                '<div class="zf-blocked">Zefoy vraagt browserverificatie voor deze serversessie. '
                + 'Mashal omzeilt die verificatie niet.</div>';
            return;
        }

        if (data.state === 'unavailable') {
            result.innerHTML =
                '<div class="zf-blocked">Deze Zefoy-service is momenteel niet beschikbaar.</div>';
            return;
        }

        if (data.success !== true) {
            result.innerHTML =
                '<div class="zf-blocked">'
                + escapeHtml(data.message || 'Zefoy lookup kon niet worden uitgevoerd.')
                + '</div>';
            return;
        }

        var video = data.video || {};
        var limits = Array.isArray(data.limits) ? data.limits : [];

        html += '<div class="zf-video-card">';

        if (video.username) {
            html += '<div class="zf-username">@' + escapeHtml(video.username) + '</div>';
        }

        if (video.caption) {
            html += '<div class="zf-caption">' + escapeHtml(video.caption) + '</div>';
        }

        if (video.age) {
            html += '<div class="zf-age">' + escapeHtml(video.age) + '</div>';
        }

        if (video.hearts != null) {
            html += '<div class="zf-hearts">' + escapeHtml(video.hearts) + ' ♥</div>';
        }

        if (limits.length) {
            html += '<div class="zf-limits">';
            html += '<select class="zf-limit-select" aria-label="Zefoy limit">';
            html += '<option value="">Select Limit</option>';

            limits.forEach(function (limit) {
                html += '<option value="' + escapeHtml(limit) + '">'
                    + escapeHtml(limit)
                    + '</option>';
            });

            html += '</select>';
            html += '</div>';
        }

        if (!video.username && !limits.length && data.message) {
            html += '<div class="zf-caption">' + escapeHtml(data.message) + '</div>';
        }

        html += '<div class="zf-readonly">'
            + 'Lookup, cooldown en beschikbare limieten komen uit de publieke Zefoy-flow. '
            + 'De laatste send/boost-stap wordt niet automatisch uitgevoerd.'
            + '</div>';

        html += '</div>';

        result.innerHTML = html;
    }

    fetch(
        endpoint
            + '?url=' + encodeURIComponent(videoUrl)
            + '&service=' + encodeURIComponent(service)
            + '&_=' + encodeURIComponent(String(Date.now())),
        {
            headers: {
                'Accept': 'application/json',
                'Cache-Control': 'no-cache'
            },
            cache: 'no-store',
            credentials: 'same-origin'
        }
    )
        .then(function (response) {
            return response.json().then(function (body) {
                if (!response.ok && !body) {
                    throw new Error('Zefoy lookup failed');
                }
                return body;
            });
        })
        .then(renderPayload)
        .catch(function () {
            result.innerHTML =
                '<div class="zf-blocked">Zefoy lookup kon niet worden geladen.</div>';
        });
}());
</script>
@endpush
