@extends('layouts.site-layout')

@section('title', 'Vocal Remover | Mashal Studio')
@section('meta_description', 'Open Vocal Remover vanuit Mashal Studio om zang en instrumentale muziek te scheiden.')

@push('styles')
<style>
    .vocal-page{min-height:calc(100vh - 72px);padding:38px 0 70px;background:radial-gradient(circle at 50% 0,rgba(103,217,144,.12),transparent 460px),#07090b;color:#f5f7fa}
    .vocal-shell{width:min(calc(100% - 32px),1200px);margin:auto}
    .vocal-head{text-align:center;margin:0 auto 26px;max-width:760px}
    .vocal-kicker{display:inline-flex;gap:8px;align-items:center;padding:7px 12px;border:1px solid rgba(103,217,144,.2);border-radius:999px;color:#86e3aa;background:rgba(103,217,144,.06);font-size:10px;font-weight:850;letter-spacing:.12em;text-transform:uppercase}
    .vocal-head h1{margin:17px 0 0;font-size:clamp(38px,6vw,68px);line-height:1;letter-spacing:-.06em;color:#fff}
    .vocal-head p{max-width:650px;margin:14px auto 0;color:#8b96a1;font-size:13px;line-height:1.75}
    .vocal-actions{display:flex;justify-content:center;flex-wrap:wrap;gap:10px;margin:22px 0}
    .vocal-button{min-height:44px;padding:0 18px;display:inline-flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,.12);border-radius:12px;color:#eaf5ee;background:#111a15;text-decoration:none;font-size:12px;font-weight:800}
    .vocal-button.primary{border-color:rgba(103,217,144,.35);background:#17452d;color:#eafff0}
    .vocal-frame{overflow:hidden;border:1px solid rgba(255,255,255,.1);border-radius:20px;background:#fff;box-shadow:0 28px 80px rgba(0,0,0,.3)}
    .vocal-frame iframe{display:block;width:100%;height:min(78vh,900px);min-height:620px;border:0;background:#fff}
    .vocal-note{margin:14px auto 0;max-width:900px;color:#7d8791;font-size:11px;line-height:1.7;text-align:center}
    @media(max-width:640px){.vocal-page{padding-top:28px}.vocal-frame{border-radius:13px}.vocal-frame iframe{min-height:680px;height:78vh}}
</style>
@endpush

@section('content')
<section class="vocal-page">
    <div class="vocal-shell">
        <header class="vocal-head">
            <div class="vocal-kicker">♫ &nbsp; Mashal Studio · Audio workspace</div>
            <h1>Vocal <span style="color:#75d99b">Remover.</span></h1>
            <p>{{ __('Scheid zang en muziek, maak een instrumentale karaokeversie of haal de vocals apart uit een track. De tool wordt aangeboden door VocalRemover.org.') }}</p>
            <div class="vocal-actions">
                <a class="vocal-button primary" href="https://vocalremover.org/" target="_blank" rel="noopener noreferrer">{{ __('Open Vocal Remover') }} ↗</a>
                <a class="vocal-button" href="{{ route('live-counts.index') }}">← {{ __('Terug naar Live Counts') }}</a>
            </div>
        </header>

        <div class="vocal-frame">
            <iframe src="https://vocalremover.org/" title="Vocal Remover" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="microphone; autoplay; clipboard-read; clipboard-write" allowfullscreen></iframe>
        </div>

        <p class="vocal-note">{{ __('Let op: VocalRemover.org is een externe dienst. Audio die je uploadt kan naar hun servers worden verzonden voor verwerking. Upload alleen bestanden die je daar wilt laten verwerken en waarvoor je de benodigde rechten hebt. Als de externe website niet in dit venster mag laden, gebruik dan de knop Open Vocal Remover.') }}</p>
    </div>
</section>
@endsection
