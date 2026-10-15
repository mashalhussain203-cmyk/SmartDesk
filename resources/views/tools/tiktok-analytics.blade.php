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
    .ms-analysis-kicker { color: #a69cff; font-size: 11px; font-weight: 850; letter-spacing: .16em; text-transform: uppercase; }
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
    .ms-action { white-space: nowrap; border: 0; cursor: pointer; border-radius: 12px; padding: 0 29px; min-height: 49px; background: #9285ff; color: #090b10; font: inherit; font-size: 12px; font-weight: 900; letter-spacing: .04em; }
    .ms-action:hover { background: #b0a8ff; }
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
    .ms-spec-grid { display: grid; gap: 12px; grid-template-columns: repeat(3, minmax(0,1fr)); }
    .ms-spec { border: 1px solid #2c3040; border-radius: 13px; padding: 15px; background: rgba(255,255,255,.018); min-width: 0; }
    .ms-spec small { color: #8f98ad; display: block; font-size: 11px; }
    .ms-spec strong { color: #f0f0f8; display: block; margin-top: 8px; font-size: 14px; overflow-wrap: anywhere; }
    .ms-note { border: 1px solid #343447; background: #151626; border-radius: 12px; padding: 15px 17px; margin-top: 22px; color: #b4b6d0; font-size: 12px; line-height: 1.7; }
    .ms-times-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 18px; margin-bottom: 22px; flex-wrap: wrap; }
    .ms-times-top strong { color: #fff; font-size: 19px; display: block; }
    .ms-times-top p { margin: 5px 0 0; color: #a1a8b9; font-size: 12px; }
    .ms-timezone { display: inline-flex; border-radius: 9px; background: #272941; padding: 8px 11px; color: #c7c4ff; font-weight: 700; font-size: 11px; }
    .ms-best-list { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 12px; }
    .ms-best { padding: 15px 17px; background: #0c1018; border: 1px solid #292e40; border-radius: 13px; }
    .ms-best-top { display: flex; justify-content: space-between; gap: 8px; align-items: center; }
    .ms-best strong { font-size: 15px; }
    .ms-best small { color: #9b9fb0; }
    .ms-best p { font-size: 11px; color: #b9becb; margin: 9px 0; }
    .ms-progress { background: #252a39; border-radius: 100px; overflow: hidden; height: 5px; }
    .ms-progress > span { display: block; height: 100%; background: #9c8aff; border-radius: 100px; }
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
@endphp
<main class="ms-analysis">
    <div class="ms-analysis-shell">
        <a class="ms-back" href="{{ route('live-counts.index') }}">← {{ __('Back to Live Counts') }}</a>
        <div class="ms-analysis-kicker">Mashal Studio · TikTok Tools</div>
        <h1>
            @if($isVideo) TikTok <span>Analyzer.</span> @else Best Upload <span>Times.</span> @endif
        </h1>
        <p class="ms-analysis-intro">
            @if($isVideo)
                {{ __('Controleer publieke TikTok-videocijfers en bekijk technische gegevens die TikTok openbaar beschikbaar maakt.') }}
            @else
                {{ __('Ontdek op welke dagen en uren jouw recente openbare TikTok-video’s gemiddeld het beste presteerden.') }}
            @endif
        </p>
        <nav class="ms-tabs" aria-label="TikTok Analytics tools">
            <a class="ms-tab {{ $isVideo ? 'active' : '' }}" href="{{ route('tiktok-analytics.video') }}">◉ &nbsp; TikTok Video Analyzer</a>
            <a class="ms-tab {{ !$isVideo ? 'active' : '' }}" href="{{ route('tiktok-analytics.times') }}">◷ &nbsp; Best Upload Times</a>
        </nav>
        <section class="ms-panel">
            <h2>
                {{ $isVideo ? 'TikTok Video Analyzer' : 'Best Upload Times' }}
                <span class="ms-version">v2.0</span>
            </h2>
            <p class="ms-panel-desc">
                {{ $isVideo
                    ? __('Plak een TikTok-videolink om de beschikbare kwaliteit, FPS, regio en engagement te bekijken.')
                    : __('Enter your TikTok username to see when your videos perform best.') }}
            </p>
            <form class="ms-form" method="GET" action="{{ $isVideo ? route('tiktok-analytics.video') : route('tiktok-analytics.times') }}">
                <input class="ms-field" aria-label="{{ $isVideo ? 'TikTok video URL' : 'TikTok username' }}"
                       type="text" name="{{ $isVideo ? 'url' : 'username' }}"
                       placeholder="{{ $isVideo ? 'https://www.tiktok.com/@user/video/...' : '@yourusername' }}"
                       value="{{ $input }}" required autocomplete="off" maxlength="{{ $isVideo ? '2048' : '255' }}">
                @unless($isVideo)
                    <input type="hidden" name="timezone" id="ms-timezone-input" value="{{ $result['timezone'] ?? '' }}">
                @endunless
                <button class="ms-action" type="submit">{{ $isVideo ? 'ANALYZE' : 'CHECK' }} →</button>
            </form>
            <p class="ms-hint">{{ $isVideo
                ? __('Alleen openbaar beschikbare data; niet-gepubliceerde technische metadata en shadowban-status zijn niet te bevestigen.')
                : __('We vergelijken de views van je recente openbare video’s op basis van hun plaatsingsmoment. Geen TikTok-login nodig.') }}</p>

            @if($error)
                <div class="ms-error" role="alert">{{ $error }}</div>
            @endif

            @if($result && $isVideo)
                <div class="ms-results">
                    <div class="ms-identity">
                        @if($result['thumbnail'])
                            <img class="ms-cover" src="{{ $result['thumbnail'] }}" alt="TikTok thumbnail" loading="lazy">
                        @endif
                        <div>
                            <strong>{{ $result['author'] ? '@'.ltrim($result['author'], '@') : 'TikTok video' }}</strong>
                            <p>{{ $result['title'] ?: __('Geen publieke beschrijving beschikbaar') }}</p>
                            <a href="{{ $result['video_url'] }}" target="_blank" rel="noopener noreferrer">Bekijk video op TikTok ↗</a>
                        </div>
                    </div>
                    <h3 class="ms-results-title">{{ __('Engagement statistics') }}</h3>
                    <div class="ms-metrics">
                        @foreach(['views' => 'Views', 'likes' => 'Likes', 'comments' => 'Comments', 'shares' => 'Shares'] as $field => $label)
                            <div class="ms-metric">
                                <small>{{ $label }}</small>
                                <strong>{{ $result[$field] !== null ? number_format($result[$field], 0, ',', '.') : '—' }}</strong>
                            </div>
                        @endforeach
                    </div>
                    <h3 class="ms-subhead">{{ __('Video details') }}</h3>
                    <div class="ms-spec-grid">
                        <div class="ms-spec"><small>Resolution</small><strong>{{ $result['resolution'] ?? 'Niet beschikbaar' }}</strong></div>
                        <div class="ms-spec"><small>FPS</small><strong>{{ $result['fps'] !== null ? $result['fps'].' FPS' : 'Niet beschikbaar' }}</strong></div>
                        <div class="ms-spec"><small>Video duration</small><strong>{{ $result['duration'] !== null ? $result['duration'].' sec' : 'Niet beschikbaar' }}</strong></div>
                        <div class="ms-spec"><small>Bitrate</small><strong>{{ $result['bitrate'] !== null ? number_format($result['bitrate'] / 1000000, 2, ',', '.').' Mbps' : 'Niet beschikbaar' }}</strong></div>
                        <div class="ms-spec"><small>Codec</small><strong>{{ $result['codec'] ?? 'Niet beschikbaar' }}</strong></div>
                        <div class="ms-spec"><small>File size</small><strong>{{ $result['file_size'] !== null ? number_format($result['file_size'] / 1048576, 2, ',', '.').' MB' : 'Niet beschikbaar' }}</strong></div>
                        <div class="ms-spec"><small>Region</small><strong>{{ $result['region'] ?? 'Niet beschikbaar' }}</strong></div>
                        <div class="ms-spec"><small>Engagement rate</small><strong>{{ $result['engagement'] !== null ? number_format($result['engagement'], 2, ',', '.').'%' : 'Niet beschikbaar' }}</strong></div>
                        <div class="ms-spec"><small>Shadow ban</small><strong>Niet betrouwbaar meetbaar</strong></div>
                    </div>
                    <div class="ms-note">
                        <strong>{{ $result['availability'] }}</strong> · {{ __('Bijgewerkt') }} {{ $result['checked_at'] }}.
                        {{ __('TikTok toont niet altijd FPS, bitrate of werkelijke afspeelresolutie. Ontbrekende gegevens betekenen niet dat een video lage kwaliteit heeft. Een shadowban kan niet uit deze gegevens worden vastgesteld.') }}
                    </div>
                </div>
            @elseif($result && !$isVideo)
                <div class="ms-results">
                    <div class="ms-times-top">
                        <div>
                            <strong>Resultaten voor &#64;{{ $result['username'] }}</strong>
                            <p>{{ $result['post_count'] }} {{ __('openbare video’s geanalyseerd') }} · {{ __('Bijgewerkt') }} {{ $result['checked_at'] }}</p>
                        </div>
                        <span class="ms-timezone">🕓 {{ $result['timezone'] }}</span>
                    </div>
                    <h3 class="ms-subhead">{{ __('Best performing upload times') }}</h3>
                    <div class="ms-best-list">
                        @foreach($result['best'] as $i => $slot)
                            <div class="ms-best">
                                <div class="ms-best-top">
                                    <strong>#{{ $i + 1 }} {{ $weekDays[$slot['day']] }} · {{ str_pad($slot['hour'], 2, '0', STR_PAD_LEFT) }}:00</strong>
                                    <small>{{ $slot['count'] }} {{ $slot['count'] === 1 ? 'video' : "video's" }}</small>
                                </div>
                                <p>{{ number_format($slot['average'], 0, ',', '.') }} {{ __('gemiddelde views') }}</p>
                                <div class="ms-progress"><span style="width: {{ round($slot['average'] * 100 / max(1, $result['best'][0]['average'])) }}%"></span></div>
                            </div>
                        @endforeach
                    </div>
                    <h3 class="ms-subhead">{{ __('Performance per day and hour') }}</h3>
                    <p class="ms-hint">{{ __('Elke gekleurde cel heeft minimaal één video. Meer paars betekent hogere gemiddelde views. Lege cellen bevatten geen waargenomen upload.') }}</p>
                    <div class="ms-heat-scroll">
                        <table class="ms-heatmap">
                            <thead><tr><th scope="col">{{ __('Dag') }}</th>
                                @for($hour = 0; $hour < 24; $hour++)
                                    <th scope="col">{{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}</th>
                                @endfor
                            </tr></thead>
                            <tbody>
                            @foreach($weekDays as $dayIndex => $dayName)
                                <tr><th scope="row">{{ mb_substr($dayName, 0, 2) }}</th>
                                    @for($hour = 0; $hour < 24; $hour++)
                                        @php($cell = $result['matrix'][$dayIndex][$hour])
                                        <td class="{{ $cell['count'] ? 'has-post' : '' }}"
                                            style="--opacity:{{ number_format($cell['intensity'] / 100, 2, '.', '') }}"
                                            title="{{ $dayName }} {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:00 · {{ $cell['count'] }} video's · {{ $cell['average'] !== null ? number_format($cell['average'], 0, ',', '.') : '—' }} views"></td>
                                    @endfor
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="ms-note">
                        {{ __('Dit zijn historische resultaten van openbare posts, geen garantie voor toekomstige views. Eén virale video kan een tijdslot sterk beïnvloeden. De tijden zijn omgerekend naar de getoonde tijdzone, niet naar de tijdzone van je TikTok-publiek.') }}
                        @if($result['post_count'] < 10)
                            <strong>{{ __('Er zijn weinig video’s beschikbaar, dus deze schatting is minder betrouwbaar.') }}</strong>
                        @endif
                    </div>
                </div>
            @endif
        </section>
    </div>
</main>
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
