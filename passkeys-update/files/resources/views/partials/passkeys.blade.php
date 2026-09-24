@if (config('passkeys.enabled'))
    <section class="mashal-passkeys" data-passkeys
        data-mode="{{ $passkeyMode }}"
        data-iphone-only="{{ config('passkeys.iphone_only_ui') ? 'true' : 'false' }}"
        data-options="{{ route($passkeyMode === 'login' ? 'passkeys.login.options' : 'passkeys.register.options', [], false) }}"
        data-verify="{{ route($passkeyMode === 'login' ? 'passkeys.login.verify' : 'passkeys.register.verify', [], false) }}"
        data-list="{{ route('passkeys.index', [], false) }}"
        @if ($passkeyMode === 'login') hidden @endif
        aria-label="Passkeys">
        @if ($passkeyMode === 'manage')
            <h2>Face ID &amp; passkeys</h2>
            <p>Stel op je iPhone een passkey in. Daarmee log je de volgende keer snel in.</p>
            <div data-passkey-enroll hidden>
                <label for="passkey-name">Naam van je passkey</label>
                <input id="passkey-name" data-passkey-name maxlength="80" value="Mijn iPhone" autocomplete="off">
                <button type="button" data-passkey-start>Passkey instellen</button>
            </div>
            <p data-passkey-device-note hidden>Open deze pagina op je iPhone om een passkey in te stellen.</p>
            <ul data-passkey-list aria-label="Jouw passkeys"></ul>
            <p>Voor toevoegen of verwijderen moet je in de afgelopen tien minuten opnieuw zijn ingelogd.</p>
        @else
            <button type="button" data-passkey-start>Inloggen met Face ID / passkey</button>
            <p>Nog geen passkey? Log eerst op je gebruikelijke manier in en stel er één in bij Accountbeveiliging.</p>
        @endif
        <p class="mashal-passkey-detail">Je iPhone bevestigt met Face ID, Touch ID of je toestelcode.</p>
        <p data-passkey-status role="status" aria-live="polite"></p>
    </section>
    @once
        @push('styles')
            <style>
                .mashal-passkeys {margin: 18px 0 26px; padding: 20px; border: 1px solid #615035; border-radius: 18px; background: #171719; color: #f5f2ec; font-size: 14px; line-height: 1.6;}
                .mashal-passkeys[hidden], .mashal-passkeys [hidden] {display: none !important;}
                .mashal-passkeys h2 {margin: 0 0 8px; font-size: 23px; color: #efc985;}
                .mashal-passkeys p {margin: 10px 0; color: #d5d0c7;}
                .mashal-passkeys label {display: block; margin-bottom: 6px;}
                .mashal-passkeys input {box-sizing: border-box; width: 100%; padding: 11px; margin-bottom: 10px; border: 1px solid #786443; border-radius: 9px; background: #101012; color: #fff; font: inherit;}
                .mashal-passkeys button {padding: 12px 18px; border: 1px solid #e4b878; border-radius: 10px; background: #e4b878; color: #201a12; font: inherit; font-weight: 700; cursor: pointer;}
                .mashal-passkeys [data-passkey-start] {width: 100%;}
                .mashal-passkeys button:disabled {opacity: .6; cursor: wait;}
                .mashal-passkeys button:focus-visible, .mashal-passkeys input:focus-visible {outline: 3px solid #fff; outline-offset: 3px;}
                .mashal-passkeys ul {padding: 0; list-style: none;}
                .mashal-passkeys li {display: flex; flex-wrap: wrap; align-items: center; gap: 12px; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #514431;}
                .mashal-passkeys .mashal-passkey-detail {font-size: 12px;}
                .mashal-passkeys [data-passkey-status] {color: #ffe1b0; overflow-wrap: anywhere;}
            </style>
        @endpush
        @push('scripts')
            <script src="{{ asset('js/mashal-passkeys.js') }}" defer></script>
        @endpush
    @endonce
@endif
