@extends('layouts.site-layout')

@section('title', 'Live Counts | Mashal Studio')
@section('meta_description', 'Open Mashal Studio live social counters voor TikTok en YouTube.')

@push('styles')
<style>
    .livehub-page {
        min-height: calc(100vh - 72px);
        padding: 54px 0 96px;
        color: #f5f7fa;
        background:
            radial-gradient(circle at 50% -140px, rgba(123,112,255,.17), transparent 470px),
            radial-gradient(circle at 90% 18%, rgba(69,171,255,.06), transparent 380px),
            linear-gradient(180deg, #07080b 0%, #050608 100%);
    }

    .livehub-shell {
        width: min(calc(100% - 32px), 1120px);
        margin: 0 auto;
    }

    .livehub-hero {
        max-width: 780px;
        margin: 0 auto 42px;
        text-align: center;
    }

    .livehub-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 11px;
        border: 1px solid rgba(123,112,255,.2);
        border-radius: 999px;
        color: #aaa3ff;
        background: rgba(123,112,255,.055);
        font-size: 10px;
        font-weight: 850;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .livehub-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #6ee7a8;
        box-shadow: 0 0 12px rgba(110,231,168,.7);
    }

    .livehub-title {
        margin: 18px 0 0;
        color: #fff;
        font-size: clamp(44px, 7vw, 78px);
        line-height: .96;
        font-weight: 760;
        letter-spacing: -.06em;
    }

    .livehub-title span {
        color: #747d89;
    }

    .livehub-copy {
        max-width: 620px;
        margin: 18px auto 0;
        color: #7f8896;
        font-size: 13px;
        line-height: 1.75;
    }

    .livehub-features {
        max-width: 900px;
        margin: 0 auto 34px;
        display: grid;
        grid-template-columns: repeat(3, minmax(0,1fr));
        gap: 10px;
    }

    .livehub-feature {
        padding: 14px 16px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 14px;
        background: rgba(255,255,255,.018);
    }

    .livehub-feature strong {
        display: block;
        color: #dfe3e9;
        font-size: 11px;
        font-weight: 800;
    }

    .livehub-feature span {
        display: block;
        margin-top: 4px;
        color: #626c79;
        font-size: 9px;
        line-height: 1.55;
    }

    .livehub-section-head {
        margin-bottom: 16px;
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 20px;
    }

    .livehub-section-head h2 {
        margin: 0;
        color: #f5f7fa;
        font-size: 22px;
        font-weight: 760;
        letter-spacing: -.03em;
    }

    .livehub-section-head p {
        margin: 0;
        color: #68717e;
        font-size: 10px;
    }

    .livehub-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0,1fr));
        gap: 16px;
    }

    .livehub-tool {
        min-width: 0;
        min-height: 270px;
        padding: 24px;
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 24px;
        color: inherit;
        background:
            radial-gradient(circle at 92% 8%, rgba(123,112,255,.12), transparent 34%),
            linear-gradient(180deg, rgba(18,21,28,.94), rgba(9,11,15,.96));
        box-shadow: 0 24px 64px rgba(0,0,0,.22), inset 0 1px 0 rgba(255,255,255,.025);
        text-decoration: none;
        transition: transform .2s ease, border-color .2s ease, background .2s ease;
    }

    .livehub-tool:hover {
        transform: translateY(-3px);
        border-color: rgba(149,140,255,.22);
        background:
            radial-gradient(circle at 92% 8%, rgba(123,112,255,.17), transparent 36%),
            linear-gradient(180deg, rgba(21,24,32,.96), rgba(10,12,17,.98));
    }

    .livehub-tool-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
    }

    .livehub-icon-wrap {
        width: 58px;
        height: 58px;
        display: grid;
        place-items: center;
        border-radius: 17px;
        background: rgba(255,255,255,.035);
        border: 1px solid rgba(255,255,255,.075);
    }

    .livehub-icon-wrap img {
        width: 34px;
        height: 34px;
        display: block;
        object-fit: contain;
    }

    .livehub-live {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #79d9a3;
        font-size: 9px;
        font-weight: 850;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .livehub-live i {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #6ee7a8;
        box-shadow: 0 0 10px rgba(110,231,168,.65);
    }

    .livehub-tool h3 {
        margin: 26px 0 0;
        color: #fff;
        font-size: 28px;
        line-height: 1.05;
        font-weight: 760;
        letter-spacing: -.04em;
    }

    .livehub-tool p {
        margin: 10px 0 0;
        max-width: 440px;
        color: #737c89;
        font-size: 11px;
        line-height: 1.65;
    }

    .livehub-metrics {
        margin-top: auto;
        padding-top: 26px;
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }

    .livehub-metrics span {
        padding: 6px 9px;
        border: 1px solid rgba(255,255,255,.065);
        border-radius: 999px;
        color: #929aa6;
        background: rgba(255,255,255,.018);
        font-size: 8px;
        font-weight: 780;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .livehub-arrow {
        position: absolute;
        right: 24px;
        bottom: 22px;
        color: #9b91ff;
        font-size: 20px;
        transition: transform .2s ease;
    }

    .livehub-tool:hover .livehub-arrow {
        transform: translateX(3px);
    }

    @media (max-width: 980px) {
        .livehub-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 760px) {
        .livehub-page {
            padding-top: 38px;
        }

        .livehub-features {
            grid-template-columns: 1fr;
        }

        .livehub-grid {
            grid-template-columns: 1fr;
        }

        .livehub-section-head {
            align-items: flex-start;
            flex-direction: column;
            gap: 5px;
        }

        .livehub-tool {
            min-height: 250px;
        }
    }
</style>
@endpush

@section('content')
<section class="livehub-page">
    <div class="livehub-shell">
        <header class="livehub-hero">
            <div class="livehub-eyebrow">
                <span class="livehub-dot"></span>
                Mashal Studio · Live Social Analytics
            </div>

            <h1 class="livehub-title">{{ __('Live') }} <span>{{ __('Counts.') }}</span></h1>

            <p class="livehub-copy">
                {{ __('Kies een live counter en volg publieke TikTok- en YouTube-statistieken zonder handmatig te refreshen. De cijfers bewegen automatisch zodra nieuwe data binnenkomt.') }}
            </p>
        </header>

        <div class="livehub-features">
            <div class="livehub-feature">
                <strong>{{ __('Exacte cijfers') }}</strong>
                <span>{{ __('Volledige publieke waarden in plaats van afgeronde miljoenen.') }}</span>
            </div>
            <div class="livehub-feature">
                <strong>{{ __('Automatisch live') }}</strong>
                <span>{{ __('Nieuwe snapshots worden vanzelf opgehaald en geanimeerd.') }}</span>
            </div>
            <div class="livehub-feature">
                <strong>{{ __('Rustige interface') }}</strong>
                <span>{{ __('Donkere Mashal UI met de belangrijkste statistieken voorop.') }}</span>
            </div>
        </div>

        <div class="livehub-section-head">
            <h2>{{ __('Choose a live counter') }}</h2>
            <p>{{ __('TikTok &amp; YouTube tools') }}</p>
        </div>

        <div class="livehub-grid">
            <a class="livehub-tool" href="{{ route('youtube-subscribers.index') }}">
                <div class="livehub-tool-top">
                    <div class="livehub-icon-wrap">
                        <span style="font-size:31px;color:#ff4656;font-weight:900" aria-hidden="true">▶</span>
                    </div>
                    <div class="livehub-live"><i></i> {{ __('Live') }}</div>
                </div>
                <h3>{{ __('YouTube Live Subscribers') }}</h3>
                <p>{{ __('Zoek een YouTube-kanaal en volg abonnees, weergaven en video\'s met een automatisch vernieuwende teller.') }}</p>
                <div class="livehub-metrics">
                    <span>{{ __('Subscribers') }}</span>
                    <span>{{ __('Views') }}</span>
                    <span>{{ __('Videos') }}</span>
                    <span>{{ __('Goal') }}</span>
                </div>
                <span class="livehub-arrow" aria-hidden="true">→</span>
            </a>

            <a class="livehub-tool" href="{{ route('youtube-views.index') }}">
                <div class="livehub-tool-top">
                    <div class="livehub-icon-wrap">
                        <span style="font-size:31px;color:#ff4656;font-weight:900" aria-hidden="true">▶</span>
                    </div>
                    <div class="livehub-live"><i></i> {{ __('Live') }}</div>
                </div>
                <h3>{{ __('YouTube Live Views') }}</h3>
                <p>
                    {{ __('Zoek een YouTube-video of plak de URL en volg views, likes, dislikes en comments met een automatisch vernieuwende teller.') }}
                </p>
                <div class="livehub-metrics">
                    <span>{{ __('Views') }}</span>
                    <span>{{ __('Likes') }}</span>
                    <span>{{ __('Dislikes') }}</span>
                    <span>{{ __('Comments') }}</span>
                </div>
                <span class="livehub-arrow" aria-hidden="true">→</span>
            </a>

            <a class="livehub-tool" href="{{ route('tiktok-follower-counter.index') }}">
                <div class="livehub-tool-top">
                    <div class="livehub-icon-wrap">
                        <img src="/icons/follower-followers.svg?v=1" alt="">
                    </div>
                    <div class="livehub-live"><i></i> {{ __('Live') }}</div>
                </div>

                <h3>TikTok Live Followers</h3>
                <p>
                    {{ __('Volg de follower count van een publiek TikTok-account samen met likes, following en het aantal videos.') }}
                </p>

                <div class="livehub-metrics">
                    <span>{{ __('Followers') }}</span>
                    <span>{{ __('Likes') }}</span>
                    <span>{{ __('Following') }}</span>
                    <span>{{ __('Videos') }}</span>
                </div>

                <span class="livehub-arrow" aria-hidden="true">→</span>
            </a>

            <a class="livehub-tool" href="{{ route('tiktok-counter.index') }}">
                <div class="livehub-tool-top">
                    <div class="livehub-icon-wrap">
                        <img src="/icons/live-eye.svg?v=20261007-5" alt="">
                    </div>
                    <div class="livehub-live"><i></i> {{ __('Live') }}</div>
                </div>

                <h3>{{ __('TikTok Video Live Views') }}</h3>
                <p>
                    {{ __('Open een publieke TikTok-video en volg views, likes, comments en shares met de live odometer.') }}
                </p>

                <div class="livehub-metrics">
                    <span>{{ __('Views') }}</span>
                    <span>{{ __('Likes') }}</span>
                    <span>{{ __('Comments') }}</span>
                    <span>{{ __('Shares') }}</span>
                </div>

                <span class="livehub-arrow" aria-hidden="true">→</span>
            </a>

        </div>
    </div>
</section>
@endsection
