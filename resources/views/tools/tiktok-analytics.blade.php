@extends('layouts.site-layout')

@section('title', ($mode === 'video' ? 'TikTok Video Analyzer' : 'Best Upload Times') . ' | Mashal Studio')
@section('meta_description', 'Analyzeer publieke TikTok-videostatistieken en ontdek op basis van je eigen recente posts je beste uploadtijden.')

@push('styles')
<style>
    .ms-analysis {
        min-height: calc(100vh - 72px);
        padding: 44px 18px 100px;
        background: radial-gradient(ellipse at 48% -150px, rgba(112,101,255,.18), transparent 520px), #07090d;
        color: #f2f3f9;
    }
    .ms-analysis * { box-sizing: border-box; }
    .ms-analysis-shell { width: min(100%, 1060px); margin: 0 auto; }
    .ms-back { display: inline-flex; gap: 9px; align-items: center; margin-bottom: 32px; color: #979fb2; text-decoration: none; font-size: 12px; }
    .ms-back:hover { color: #fff; }
    .ms-analysis-kicker { color: #97d32f; font-size: 11px; font-weight: 850; letter-spacing: .16em; text-transform: uppercase; }
    .ms-analysis h1 { margin: 12px 0 12px; color: #fff; font-size: clamp(38px, 6vw, 62px); line-height: 1.05; letter-spacing: -.055em; }
    .ms-analysis h1 span { color: #9097a4; }
    .ms-analysis-intro { max-width: 690px; color: #99a1b2; font-size: 14px; line-height: 1.75; margin-bottom: 27px; }
    .ms-tabs { display: flex; flex-wrap: wrap; gap: 9px; margin: 30px 0 25px; }
    .ms-tab { display: inline-flex; align-items: center; padding: 11px 18px; border: 1px solid #242837; border-radius: 12px; background: #11151d; color: #aeb7ca; text-decoration: none; font-size: 12px; font-weight: 800; }
    .ms-tab.active, .ms-tab:hover { background: #282641; border-color: #7470d9; color: #fff; }
    .ms-panel { border: 1px solid #272b39; border-radius: 24px; background: linear-gradient(145deg, #141821, #0d1016); padding: clamp(19px,4vw,32px); box-shadow: 0 24px 80px rgba(0,0,0,.23); }
    .ms-panel h2 { font-size: 20px; letter-spacing: -.025em; margin: 0 0 9px; }
    .ms-panel-desc { font-size: 12px; color: #99a1b2; line-height: 1.65; margin: 0 0 20px; }
    .ms-version { display: inline-block; vertical-align: middle; font-size: 10px; letter-spacing: .06em; font-weight: 850; color: #bcb7ff; background: #292542; border: 1px solid #393355; border-radius: 8px; padding: 4px 7px; margin-left: 6px; }
    .ms-form { display: flex; gap: 10px; align-items: stretch; }
    .ms-field { width: 100%; min-width: 0; border: 1px solid #383f52; background: #090c13; color: #fff; border-radius: 12px; padding: 15px 16px; font: inherit; font-size: 13px; outline: none; }
    .ms-field:focus { border-color: #9d8cff; box-shadow: 0 0 0 3px rgba(149,134,255,.12); }
    .ms-action { white-space: nowrap; border: 0; cursor: pointer; border-radius: 12px; padding: 0 29px; min-height: 49px; background: #8bd211; color: #080b05; font: inherit; font-size: 12px; font-weight: 900; letter-spacing: .04em; }
    .ms-action:hover { background: #a8ed35; }
    .ms-hint { margin: 12px 0 0; font-size: 11px; line-height: 1.6; color: #878fa0; }
    .ms-error { background: rgba(250,89,101,.08); border: 1px solid rgba(255,114,127,.37); color: #ffb0b7; border-radius: 12px; padding: 15px 17px; font-size: 12px; margin: 18px 0 0; }
    .ms-results { margin-top: 24px; }
    .ms-results-title { font-size: 19px; margin: 0 0 14px; color: #fff; }
    .ms-identity { display: flex; gap: 20px; align-items: center; margin-bottom: 25px; min-width: 0; }
    .ms-cover { flex: 0 0 100px; width: 100px; height: 130px; object-fit: cover; border-radius: 13px; background: #202533; }
    .ms-identity strong { display: block; font-size: 18px; overflow-wrap: anywhere; }
    .ms-identity p { font-size: 12px; line-height: 1.65; color: #aeb6c7; }
    .ms-identity a, .ms-link { color: #b9b0ff; font-size: 12px; text-decoration: none; overflow-wrap: anywhere; }
    .ms-identity a:hover, .ms-link:hover { text-decoration: underline; }
    .ms-metrics { display: grid; gap: 12px; grid-template-columns: repeat(4, minmax(0,1fr)); }
    .ms-metric { border: 1px solid #2c3040; border-radius: 16px; background: #0c1018; padding: 19px 17px; min-width: 0; }
    .ms-metric small { color: #9ca4b7; display: block; font-size: 11px; }
    .ms-metric strong { color: #fff; font-size: clamp(20px, 2.8vw, 29px); display: block; padding-top: 8px; font-weight: 850; overflow-wrap: anywhere; }
    .ms-subhead { margin: 29px 0 15px; font-size: 17px; }
    .ms-spec-grid { display: grid; gap: 12px; grid-template-columns: repeat(2, minmax(0,1fr)); }
    .ms-spec { border: 1px solid #2c3040; border-radius: 13px; padding: 15px; background: rgba(255,255,255,.018); min-width: 0; }
    .ms-spec small { color: #8f98ad; display: block; font-size: 11px; }
    .ms-spec strong { color: #f0f0f8; display: block; margin-top: 8px; font-size: 14px; overflow-wrap: anywhere; }

    .ms-profile-card {
        display: flex; align-items: center; gap: 15px;
        padding: 17px; border: 1px solid #292d3b; border-radius: 18px;
        background: #0b0d13; margin: 22px 0 14px;
    }
    .ms-profile-card .ms-cover { width: 79px; height: 100px; flex-basis: 79px; }
    .ms-profile-card h3 { font-size: 21px; color: #fff; margin: 0 0 5px; letter-spacing: -.03em; overflow-wrap: anywhere; }
    .ms-profile-card .ms-handle { color: #a4a6b2; font-size: 12px; }
    .ms-badge-public { display: inline-flex; gap: 6px; align-items: center; margin-top: 10px; padding: 6px 11px; border: 1px solid #375018; background: #1c280c; color: #92db27; border-radius: 999px; font-size: 12px; font-weight: 850; }
    .ms-date-line { display: flex; flex-wrap: wrap; gap: 16px; padding: 6px 3px 18px; color: #9ea4b4; font-size: 12px; }
    .ms-caption-box { border: 1px solid #282b37; background: #0a0c11; border-radius: 17px; padding: 19px; margin: 0 0 21px; }
    .ms-caption-box p { margin: 0; color: #dedee5; line-height: 1.75; font-size: 13px; overflow-wrap: anywhere; }
    .ms-tags { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
    .ms-tags span { display: inline-flex; align-items: center; color: #101710; background: #6ee7a0; font-size: 11px; font-weight: 820; padding: 7px 10px; border-radius: 100px; }
    .ms-spec-grid .ms-spec { min-height: 98px; border-radius: 18px; padding: 21px; }
    .ms-spec-grid .ms-spec small:first-child { text-transform: uppercase; letter-spacing: .16em; font-size: 10px; font-weight: 850; color: #95939e; }
    .ms-spec-grid .ms-spec strong { font-size: clamp(15px, 2vw, 20px); margin-top: 16px; line-height: 1.45; }
    .ms-spec-grid .ms-spec strong.ms-success { color: #95d42f; }
    .ms-spec-grid .ms-spec .ms-flag { margin-right: 7px; white-space: nowrap; font-size: 23px; vertical-align: -2px; }
    .ms-spec-grid .ms-spec .ms-subtext { margin-top: 8px; display: block; color: #81899b; line-height: 1.6; font-size: 10px; }
    .ms-fetch-indicator { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 168px; gap: 17px; margin-top: 16px; border: 1px solid #292d3b; border-radius: 19px; background: #0b0d13; text-align: center; color: #c5cad6; }
    .ms-fetch-indicator[hidden] { display: none !important; }
    .ms-fetch-spinner { width: 49px; height: 49px; border: 3px solid #293021; border-bottom-color: #8bd211; border-left-color: #8bd211; border-radius: 50%; animation: ms-fetch-spin .9s linear infinite; }
    .ms-fetch-label { font-size: 14px; font-weight: 650; }
    .ms-action.is-fetching { position: relative; pointer-events: none; opacity: .87; }
    .ms-action.is-fetching::before { content: ''; display: inline-block; width: 14px; height: 14px; vertical-align: -2px; margin-right: 9px; border: 2px solid rgba(9,15,5,.24); border-top-color: #0b1206; border-radius: 50%; animation: ms-fetch-spin .75s linear infinite; }
    .ms-analysis.is-fetching .ms-results { opacity: .45; }
    @keyframes ms-fetch-spin { to { transform: rotate(360deg); } }
    @media (prefers-reduced-motion: reduce) { .ms-fetch-spinner, .ms-action.is-fetching::before { animation-duration: 2s; } }
    .ms-download { display: flex; align-items: center; justify-content: center; width: 100%; background: #89cc07; color: #080a06; border-radius: 18px; min-height: 62px; margin: 24px 0 0; padding: 14px 17px; font-weight: 900; font-size: clamp(13px,2vw,18px); text-decoration: none; text-align: center; }
    .ms-download:hover { background: #a5ee30; color: #080a06; }
    .ms-download-note { color: #8d94a3; font-size: 11px; text-align: center; line-height: 1.65; margin: 10px 0 0; }
    @media(max-width:480px) {
       .ms-spec-grid { gap: 10px; }
       .ms-spec-grid .ms-spec { min-height: 108px; padding: 14px 13px; }
       .ms-profile-card h3 { font-size: 18px; }
       .ms-profile-card .ms-cover { width: 62px; height: 82px; flex-basis: 62px; }
       .ms-spec-grid .ms-spec strong { font-size: 15px; overflow-wrap: anywhere; }
    }

    .ms-note { border: 1px solid #343447; background: #151626; border-radius: 12px; padding: 15px 17px; margin-top: 22px; color: #b4b6d0; font-size: 12px; line-height: 1.7; }
    .ms-times-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 18px; margin-bottom: 22px; flex-wrap: wrap; }
    .ms-times-top strong { color: #fff; font-size: 19px; display: block; }
    .ms-times-top p { margin: 5px 0 0; color: #a1a8b9; font-size: 12px; }
    .ms-timezone { display: inline-flex; border-radius: 9px; background: #272941; padding: 8px 11px; color: #c7c4ff; font-weight: 700; font-size: 11px; }
    .ms-best-list { display: grid; grid-template-columns: 1fr; gap: 19px; margin-top: 22px; }
    .ms-best { padding: 0; min-width: 0; }
    .ms-best-top { display: flex; justify-content: space-between; gap: 13px; align-items: center; margin-bottom: 11px; }
    .ms-best strong { font-size: 14px; font-weight: 750; }
    .ms-best .ms-best-score { font-size: 17px; font-weight: 850; color: #f4f3ff; white-space: nowrap; }
    .ms-progress { background: #28252e; border-radius: 100px; overflow: hidden; height: 10px; }
    .ms-progress > span { display: block; height: 100%; background: #9465f6; border-radius: 100px; }
    .ms-times-confidence { margin-top: 7px; color: #9093a2; font-size: 12px; }
    .ms-times-provenance { margin-top: 19px; font-size: 12px; color: #adb1c2; }
    .ms-times-provenance summary { cursor: pointer; color: #b6a7f3; }
    .ms-times-provenance .ms-note { margin-top: 10px; }
    .ms-results.is-times .ms-times-top { margin-bottom: 9px; }
    .ms-results.is-times .ms-times-top > div > strong { display: none; }
    .ms-heat-scroll { margin-top: 12px; width: 100%; overflow-x: auto; }
    .ms-heatmap { width: 100%; min-width: 800px; border-collapse: separate; border-spacing: 5px; table-layout: fixed; }
    .ms-heatmap th { font-weight: 700; font-size: 10px; color: #9fa7b9; padding: 8px 2px; }
    .ms-heatmap th:first-child { width: 55px; text-align: left; }
    .ms-heatmap td { height: 23px; background: #1b202c; border: 1px solid #262b39; border-radius: 4px; }
    .ms-heatmap td.has-post { background: rgba(150,133,255,var(--opacity)); border-color: #7d74d1; }
    .ms-days { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 15px; }
    .ms-days span { border: 1px solid #303546; border-radius: 9px; padding: 8px 10px; font-size: 11px; color: #c3c8d4; }
    @media (max-width: 680px) {
        .ms-form { flex-direction: column; }
        .ms-action { min-height: 48px; }
        .ms-metrics { grid-template-columns: repeat(2,minmax(0,1fr)); }
        .ms-spec-grid { grid-template-columns: repeat(2,minmax(0,1fr)); }
        .ms-best-list { grid-template-columns: 1fr; }
        .ms-cover { width: 70px; height: 95px; flex-basis: 70px; }
    }
    @media (prefers-reduced-motion: reduce) { .ms-tab, .ms-action { transition: none; } }
</style>
@endpush

@section('content')
@php
    $isVideo = $mode === 'video';
    $weekDays = ['Maandag', 'Dinsdag', 'Woensdag', 'Donderdag', 'Vrijdag', 'Zaterdag', 'Zondag'];
    $weekDaysEN = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
@endphp
<main class="ms-analysis">
    <div class="ms-analysis-shell">
        <a class="ms-back" href="{{ route('live-counts.index') }}">← {{ __('Back to Live Counts') }}</a>
        <div class="ms-analysis-kicker">Mashal Studio · TikTok Tools</div>
        <h1>
            @if($isVideo) TikTok <span>{{ __('Analyzer.') }}</span> @else {{ __('Best Upload') }} <span>{{ __('Times.') }}</span> @endif
        </h1>
        <p class="ms-analysis-intro">
            @if($isVideo)
                {{ __('Controleer publieke TikTok-videocijfers en bekijk technische gegevens die TikTok openbaar beschikbaar maakt.') }}
            @else
                {{ __('Ontdek op welke dagen en uren jouw recente openbare TikTok-video’s gemiddeld het beste presteerden.') }}
            @endif
        </p>
        <nav class="ms-tabs" aria-label="TikTok Analytics tools">
            <a class="ms-tab {{ $isVideo ? 'active' : '' }}" href="{{ route('tiktok-analytics.video') }}">◉ &nbsp; {{ __('TikTok Video Analyzer') }}</a>
            <a class="ms-tab {{ !$isVideo ? 'active' : '' }}" href="{{ route('tiktok-analytics.times') }}">◷ &nbsp; {{ __('Best Upload Times') }}</a>
        </nav>
        <section class="ms-panel">
            <h2>
                {{ $isVideo ? __('TikTok Video Analyzer') : __('Best Upload Times') }}
                <span class="ms-version">v2.0</span>
            </h2>
            <p class="ms-panel-desc">
                {{ $isVideo
                    ? __('Plak een TikTok-videolink om de beschikbare kwaliteit, FPS, regio en engagement te bekijken.')
                    : __('Enter your TikTok username to see when your videos perform best.') }}
            </p>
            <form id="ms-primary-form" class="ms-form" method="GET" action="{{ $isVideo ? route('tiktok-analytics.video') : route('tiktok-analytics.times') }}">
                <input class="ms-field" aria-label="{{ $isVideo ? __('TikTok video URL') : __('TikTok username') }}"
                       type="text" name="{{ $isVideo ? 'url' : 'username' }}"
                       placeholder="{{ $isVideo ? 'https://www.tiktok.com/@user/video/...' : '@yourusername' }}"
                       value="{{ $input }}" required autocomplete="off" maxlength="{{ $isVideo ? '2048' : '255' }}">
                @unless($isVideo)
                    <input type="hidden" name="timezone" id="ms-timezone-input" value="{{ $result['timezone'] ?? 'UTC' }}">
                @endunless
                <button class="ms-action" type="submit">{{ $isVideo ? __('ANALYZE') : __('CHECK') }} →</button>
            </form>
            <div class="ms-fetch-indicator" id="ms-primary-loading" hidden role="status" aria-live="polite">
                <span class="ms-fetch-spinner" aria-hidden="true"></span>
                <span class="ms-fetch-label">{{ $isVideo ? __('Fetching video data...') : __('Fetching upload time data...') }}</span>
            </div>
            <p class="ms-hint">{{ $isVideo
                ? __('De framerate komt uit publieke videometadata of een echte meting met ffprobe. Zonder toegang tot de mediastream kunnen we geen FPS bevestigen.')
                : __('Deze tool gebruikt uitsluitend verzamelde openbare posts. Het zijn historische weergavecijfers per uploadmoment, niet de officiële volgersactiviteit uit TikTok Studio. Zonder beschikbare posts tonen we geen verzonnen tijden.') }}</p>

            @if($error)
                <div class="ms-error" role="alert">{{ $error }}</div>
            @endif

            @if($result && $isVideo)
                <div class="ms-results">
                    <div class="ms-profile-card">
                        @if($result['thumbnail'])
                            <img class="ms-cover" src="{{ $result['thumbnail'] }}" alt="TikTok-video omslag" loading="lazy">
                        @endif
                        <div style="min-width:0">
                            <h3>{{ $result['nickname'] ?: ($result['author'] ?: 'TikTok creator') }}</h3>
                            @if($result['author'])
                                <div class="ms-handle">&#64;{{ ltrim($result['author'], '@') }}</div>
                            @endif
                            <span class="ms-badge-public">{{ $result['public_visibility'] === 'Public' ? '● PUBLIC' : '● ONBEKEND' }}</span>
                            <div style="margin-top:10px">
                                <a class="ms-link" href="{{ $result['video_url'] }}" rel="noopener noreferrer" target="_blank">{{ __("Bekijk op TikTok ↗") }}</a>
                            </div>
                        </div>
                    </div>
                    <div class="ms-date-line">
                        @if($result['duration'] !== null)
                            <span>◷ {{ gmdate('i:s', (int) $result['duration']) }} {{ __('video') }}</span>
                        @endif
                        @if($result['published_at'])
                            <span>▣ {{ $result['published_at'] }} {{ $result['published_estimated'] ? '(geschat)' : '(NL)' }}</span>
                        @endif
                    </div>
                    @if($result['title'] || $result['hashtags'])
                        <div class="ms-caption-box">
                            <p>{{ $result['title'] ?: 'TikTok video' }}</p>
                            @if(count($result['hashtags']))
                                <div class="ms-tags">
                                    @foreach($result['hashtags'] as $hashtag)
                                        <span>{{ $hashtag }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                    <h3 class="ms-results-title">{{ __('Video analysis') }}</h3>
                    <div class="ms-spec-grid">
                        <div class="ms-spec">
                            <small>{{ __("Resolution") }}</small>
                            <strong>{{ $result['quality'] ? $result['quality'].' · ' : '' }}{{ $result['resolution'] ?? __('Niet beschikbaar') }}</strong>
                            <span class="ms-subtext">{{ $result['video_version'] }} · {{ $result['technical_source'] }}</span>
                        </div>
                        <div class="ms-spec">
                            <small>FPS</small>
                            <strong class="{{ $result['fps'] !== null ? 'ms-success' : '' }}">{{ $result['fps'] !== null ? number_format($result['fps'], (floor($result['fps']) == $result['fps'] ? 0 : 2), ',', '.').' fps' : __('FPS niet beschikbaar') }}</strong>
                            <span class="ms-subtext">{{ $result['fps_source'] ?? __('Geen FPS beschikbaar in de openbare videometadata of onderzochte stream.') }}</span>
                        </div>
                        <div class="ms-spec"><small>{{ __("Engine / encodeerformaat") }}</small><strong>{{ $result['engine'] ?? __('Niet beschikbaar') }}</strong></div>
                        <div class="ms-spec"><small>{{ __("Bitrate") }}</small><strong>{{ $result['bitrate'] !== null ? number_format($result['bitrate'] / 1000, 0, ',', '.').' Kbps' : __('Niet beschikbaar') }}</strong><span class="ms-subtext">{{ __("Van de geselecteerde videoversie, indien gemeten") }}</span></div>
                        <div class="ms-spec"><small>{{ __("File size") }}</small><strong>{{ $result['file_size'] !== null ? number_format($result['file_size'] / 1048576, 1, ',', '.').' MB' : __('Niet beschikbaar') }}</strong><span class="ms-subtext">{{ __("Beschikbare streamingversie, niet noodzakelijk het uploadbestand") }}</span></div>
                        <div class="ms-spec"><small>{{ __("Codec") }}</small><strong>{{ $result['codec'] ?? __('Niet beschikbaar') }}</strong></div>
                        <div class="ms-spec">
                            <small>{{ __("Region") }}</small>
                            <strong>
                                @if($result['region_flag'])<span class="ms-flag" aria-hidden="true">{{ $result['region_flag'] }}</span>@endif
                                {{ $result['region_name'] ?? __('Niet bekend') }}
                            </strong>
                            <span class="ms-subtext">{{ $result['region_source'] ?? __('TikTok heeft voor deze post geen landcode gedeeld') }}</span>
                        </div>
                        <div class="ms-spec">
                            <small>{{ __("Openbare zichtbaarheid") }}</small>
                            <strong class="{{ $result['public_visibility'] === 'Public' ? 'ms-success' : '' }}">{{ __($result['public_visibility_label']) }}</strong>
                            <span class="ms-subtext">{{ __($result['public_visibility_detail']) }}</span>
                            <span class="ms-subtext">{{ __('Bron:') }} {{ __($result['public_visibility_source']) }}</span>
                        </div>
                        <div class="ms-spec">
                            <small>{{ __("Upload date") }}</small>
                            <strong>{{ $result['published_at'] ?? __('Niet beschikbaar') }}</strong>
                            <span class="ms-subtext">{{ __('Tijdzone Nederland') }} {{ $result['published_estimated'] ? '· '.__('datum afgeleid uit video-ID') : '' }}</span>
                        </div>
                        <div class="ms-spec">
                            <small>{{ __("Video ID") }}</small>
                            <strong dir="ltr">{{ $result['video_id'] }}</strong>
                            <span class="ms-subtext">{{ __('TikTok openbaar video-ID · gecontroleerd') }} {{ $result['checked_at'] }}</span>
                        </div>
                    </div>
                    <h3 class="ms-subhead">{{ __('Public video statistics') }}</h3>
                    <div class="ms-metrics">
                        @foreach(['views' => 'Views', 'likes' => 'Likes', 'comments' => 'Comments', 'shares' => 'Shares'] as $field => $label)
                            <div class="ms-metric">
                                <small>{{ __($label) }}</small>
                                <strong>{{ $result[$field] !== null ? number_format($result[$field], 0, ',', '.') : '—' }}</strong>
                            </div>
                        @endforeach
                    </div>
                    @if($result['engagement'] !== null)
                        <div class="ms-note">{{ __('Engagement rate') }}: <strong>{{ number_format($result['engagement'], 2, ',', '.') }}%</strong> (likes + comments + shares / views).</div>
                    @endif
                    @if($result['download_url'])
                        <a class="ms-download" href="{{ $result['download_url'] }}" target="_blank" rel="nofollow noopener noreferrer">
                            ↓ {{ __('Download HD Video') }}{{ $result['download_quality'] ? ' ('.$result['download_quality'].' · '.__('beschikbare versie').')' : '' }}
                        </a>
                        <p class="ms-download-note">{{ __('Beste beschikbare publieke videoversie. Dit is niet gegarandeerd het originele uploadbestand; sommige browsers openen de video eerst.') }}</p>
                    @else
                        <p class="ms-download-note">{{ __('Voor deze video is geen gecontroleerde publieke downloadlink beschikbaar. Je kunt de video wel op TikTok bekijken.') }}</p>
                    @endif
                    <div class="ms-note">
                        <strong>{{ __($result['availability']) }}</strong> · {{ __('Bijgewerkt') }} {{ $result['checked_at'] }}.
                        {{ __('Metadata verschilt per video en bron. Resolutie en bestandsgrootte kunnen betrekking hebben op de beschikbare streamingversie, niet op het oorspronkelijke uploadbestand. Landvlaggen worden alleen getoond bij een echte landcode uit openbare videometadata.') }}
                    </div>
                </div>
            @elseif($result && !$isVideo)
                <div class="ms-results is-times">
                    <div class="ms-times-top">
                        <div>
                            <strong>{{ __('Resultaten voor') }} &#64;{{ $result['username'] }}</strong>
                            <p>{{ $result['post_count'] }} {{ __('openbare video’s geanalyseerd') }} · {{ __('Bijgewerkt') }} {{ $result['checked_at'] }}</p>
                        </div>
                        <span class="ms-timezone">🕓 {{ $result['timezone'] }}</span>
                    </div>
                    <div class="ms-times-confidence">
                        {{ __('Based on') }} {{ $result['post_count'] }} {{ __('recent videos') }} ·
                        {{ $result['sample_reliable'] ? __('high confidence') : __('limited sample') }}
                    </div>
                    <div class="ms-best-list">
                        @foreach($result['best'] as $slot)
                            <div class="ms-best">
                                <div class="ms-best-top">
                                    <strong>{{ __($weekDaysEN[$slot['day']]) }} <bdi dir="ltr">{{ str_pad($slot['hour'], 2, '0', STR_PAD_LEFT) }}:00–{{ str_pad(($slot['hour'] + 1) % 24, 2, '0', STR_PAD_LEFT) }}:00 {{ $result['timezone'] }}</bdi></strong>
                                    <span class="ms-best-score" dir="ltr">{{ number_format($slot['score_percent'], 2, '.', '') }}%</span>
                                </div>
                                <div class="ms-progress" role="img" aria-label="{{ $slot['score_percent'] }} {{ __('procent historisch relatief prestatieniveau') }}"><span style="width: {{ round($slot['score_percent'] / max(1, $result['score_scale']) * 100, 1) }}%"></span></div>
                            </div>
                        @endforeach
                    </div>
                    <details class="ms-times-provenance">
                        <summary>{{ __("Hoe zijn de tijden berekend? · brongegevens") }}</summary>
                        <div class="ms-note">
                            {{ __('Deze percentages zijn berekend uit echte openbare videoweergaven: het aandeel van de gemiddelde weergaven per uploaduur binnen alle waargenomen uren. Het is geen kans op viraliteit en geen officiële activiteit van TikTok-volgers.') }}
                            <div style="margin-top:8px">
                                {{ $result['estimated_dates'] }} {{ __("uploaddatums zijn uit video-ID's afgeleid;") }}
                                {{ $result['repeated_slots'] }} {{ __('uploaduren komen meerdere keren voor.') }}
                                @foreach($result['source_counts'] as $source => $count)
                                    <span style="display:inline-block; margin:3px 8px 0 0">{{ $source }}: {{ $count }}</span>
                                @endforeach
                            </div>
                            @unless($result['sample_reliable'])
                                <strong>{{ __("Deze steekproef is beperkt. Gebruik de uitkomst alleen als indicatie.") }}</strong>
                            @endunless
                        </div>
                    </details>
                </div>
            @endif
        </section>
        @if($isVideo)
            <section class="ms-panel" style="margin-top:24px">
                <h2>{{ __('Best Upload Times') }} <span class="ms-version">v2.0</span></h2>
                <p class="ms-panel-desc">{{ __("Enter your TikTok username to see when your public videos historically performed best.") }}</p>
                <form id="ms-secondary-form" method="GET" class="ms-form" action="{{ route('tiktok-analytics.times') }}">
                    <input class="ms-field" type="text" name="username" placeholder="&#64;yourusername" required maxlength="255" aria-label="TikTok username">
                    <input type="hidden" name="timezone" value="UTC">
                    <button class="ms-action" type="submit">{{ __('CHECK') }} →</button>
                </form>
                <div class="ms-fetch-indicator" id="ms-secondary-loading" hidden role="status" aria-live="polite">
                    <span class="ms-fetch-spinner" aria-hidden="true"></span>
                    <span class="ms-fetch-label">{{ __("Fetching upload time data...") }}</span>
                </div>
                <p class="ms-hint">{{ __("Resultaten tonen de gegevensbron en steekproefbetrouwbaarheid. Dit zijn geen officiële TikTok-volgersactiviteitstijden.") }}</p>
            </section>
        @endif
    </div>
</main>
<script>
(function () {
    function bindFetchAnimation(formId, indicatorId) {
        const form = document.getElementById(formId);
        const indicator = document.getElementById(indicatorId);
        if (!form || !indicator) return;
        const button = form.querySelector('button[type="submit"]');
        function reset() {
            indicator.hidden = true;
            document.querySelector('.ms-analysis')?.classList.remove('is-fetching');
            if (button) {
                button.disabled = false;
                button.classList.remove('is-fetching');
                if (button.dataset.originalLabel) button.textContent = button.dataset.originalLabel;
            }
        }
        form.addEventListener('submit', function () {
            indicator.hidden = false;
            document.querySelector('.ms-analysis')?.classList.add('is-fetching');
            if (button) {
                button.dataset.originalLabel = button.textContent;
                button.textContent = @json(__('Loading...'));
                button.classList.add('is-fetching');
                button.disabled = true;
            }
        });
        window.addEventListener('pageshow', reset);
    }
    bindFetchAnimation('ms-primary-form', 'ms-primary-loading');
    bindFetchAnimation('ms-secondary-form', 'ms-secondary-loading');
})();
</script>
@unless($isVideo)
<script>
(function () {
    var input = document.getElementById('ms-timezone-input');
    if (!input) return;
    var detected = 'UTC';
    try { detected = Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC'; } catch (e) {}
    if (!input.value) input.value = detected;
    if (input.form) input.form.addEventListener('submit', function () {
        input.value = detected;
    });
})();
</script>
@endunless
@endsection
