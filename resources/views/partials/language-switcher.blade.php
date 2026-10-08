{{-- Visible in the desktop and mobile header. Each form uses Laravel's CSRF protection. --}}
<div
    class="expert-language-switcher"
    role="group"
    aria-label="{{ __('Taal kiezen') }}"
    dir="ltr"
>
    <form method="POST" action="{{ route('language.switch') }}">
        @csrf
        <input type="hidden" name="locale" value="nl">
        <button
            class="expert-language-button"
            type="submit"
            lang="nl"
            title="Nederlands"
            aria-label="{{ __('Schakel over naar Nederlands') }}"
            aria-pressed="{{ app()->getLocale() === 'nl' ? 'true' : 'false' }}"
        >NL</button>
    </form>
    <form method="POST" action="{{ route('language.switch') }}">
        @csrf
        <input type="hidden" name="locale" value="ur">
        <button
            class="expert-language-button"
            type="submit"
            lang="ur"
            dir="rtl"
            title="اردو"
            aria-label="{{ __('Schakel over naar Urdu') }}"
            aria-pressed="{{ app()->getLocale() === 'ur' ? 'true' : 'false' }}"
        >اردو</button>
    </form>
</div>
