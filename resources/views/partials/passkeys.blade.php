@if (config('passkeys.enabled'))
    <section
        class="mashal-passkeys"
        data-passkeys
        data-mode="{{ $passkeyMode }}"
        data-iphone-only="{{ config('passkeys.iphone_only_ui') ? 'true' : 'false' }}"
        data-options="{{ route($passkeyMode === 'login' ? 'passkeys.login.options' : 'passkeys.register.options', [], false) }}"
        data-verify="{{ route($passkeyMode === 'login' ? 'passkeys.login.verify' : 'passkeys.register.verify', [], false) }}"
        data-list="{{ route('passkeys.index', [], false) }}"
        @if ($passkeyMode === 'login') hidden @endif
        aria-label="Passkeys"
    >
        @if ($passkeyMode === 'manage')
            <h2>Face ID &amp; passkeys</h2>

            <p>
                {{ __('Stel op je iPhone een passkey in. Daarmee log je de volgende keer snel in.') }}
            </p>

            <div data-passkey-enroll hidden>
                <label for="passkey-name">
                    {{ __('Naam van je passkey') }}
                </label>

                <input
                    id="passkey-name"
                    data-passkey-name
                    maxlength="80"
                    value="Mijn iPhone"
                    autocomplete="off"
                >

                <button
                    type="button"
                    data-passkey-start
                >
                    {{ __('Passkey instellen') }}
                </button>
            </div>

            <p data-passkey-device-note hidden>
                {{ __('Open deze pagina op je iPhone om een passkey in te stellen.') }}
            </p>

            <ul
                data-passkey-list
                aria-label="Jouw passkeys"
            ></ul>

            <p>
                {{ __('Voor toevoegen of verwijderen moet je in de afgelopen tien minuten opnieuw zijn ingelogd.') }}
            </p>
        @else
            <button
                type="button"
                data-passkey-start
            >
                {{ __('Inloggen met Face ID / passkey') }}
            </button>

            <p>
                {{ __('Nog geen passkey? Log eerst op je gebruikelijke manier in en stel er één in bij Accountbeveiliging.') }}
            </p>
        @endif

        <p class="mashal-passkey-detail">
            {{ __('Je iPhone bevestigt met Face ID, Touch ID of je toestelcode.') }}
        </p>

        <p
            data-passkey-status
            role="status"
            aria-live="polite"
        ></p>
    </section>

    @once
        @push('styles')
            <style>
                .mashal-passkeys {
                    position: relative;
                    isolation: isolate;

                    margin: 18px 0 26px;
                    padding: 20px;

                    overflow: hidden;

                    border: 1px solid rgba(122, 108, 255, .24);
                    border-radius: 18px;

                    color: #f6f8fb;

                    background:
                        radial-gradient(
                            circle at 18% 0%,
                            rgba(122, 108, 255, .11),
                            transparent 18rem
                        ),
                        radial-gradient(
                            circle at 100% 100%,
                            rgba(66, 165, 255, .08),
                            transparent 18rem
                        ),
                        linear-gradient(
                            145deg,
                            rgba(255, 255, 255, .025),
                            rgba(255, 255, 255, .006)
                        ),
                        rgba(9, 11, 16, .92);

                    box-shadow:
                        inset 0 1px 0 rgba(255, 255, 255, .045),
                        0 20px 55px rgba(0, 0, 0, .24);

                    font-size: 14px;
                    line-height: 1.6;
                }

                .mashal-passkeys::before {
                    content: "";

                    position: absolute;
                    z-index: -1;

                    width: 220px;
                    height: 220px;

                    top: -150px;
                    left: -80px;

                    border-radius: 50%;

                    background:
                        rgba(122, 108, 255, .16);

                    filter: blur(52px);

                    pointer-events: none;
                }

                .mashal-passkeys::after {
                    content: "";

                    position: absolute;
                    z-index: -1;

                    width: 180px;
                    height: 180px;

                    right: -100px;
                    bottom: -120px;

                    border-radius: 50%;

                    background:
                        rgba(66, 165, 255, .10);

                    filter: blur(48px);

                    pointer-events: none;
                }

                .mashal-passkeys[hidden],
                .mashal-passkeys [hidden] {
                    display: none !important;
                }

                .mashal-passkeys h2 {
                    margin: 0 0 8px;

                    color: #c7c1ff;

                    font-size: 23px;
                    font-weight: 800;
                    letter-spacing: -.035em;
                }

                .mashal-passkeys p {
                    margin: 10px 0;

                    color: #a3abb8;
                }

                .mashal-passkeys label {
                    display: block;

                    margin-bottom: 6px;

                    color: #c6ccd5;

                    font-size: 12px;
                    font-weight: 700;
                }

                .mashal-passkeys input {
                    box-sizing: border-box;

                    width: 100%;

                    padding: 11px;
                    margin-bottom: 10px;

                    border: 1px solid rgba(255, 255, 255, .11);
                    border-radius: 10px;

                    outline: 0;

                    color: #fff;

                    background:
                        rgba(5, 6, 9, .72);

                    font: inherit;

                    transition:
                        border-color .2s ease,
                        background .2s ease,
                        box-shadow .2s ease;
                }

                .mashal-passkeys input:focus {
                    border-color:
                        rgba(122, 108, 255, .56);

                    background:
                        rgba(122, 108, 255, .035);

                    box-shadow:
                        0 0 0 3px rgba(122, 108, 255, .08);
                }

                .mashal-passkeys button {
                    position: relative;
                    isolation: isolate;

                    padding: 12px 18px;

                    overflow: hidden;

                    border:
                        1px solid rgba(122, 108, 255, .42);

                    border-radius: 11px;

                    color: #fff;

                    background:
                        linear-gradient(
                            135deg,
                            #7a6cff 0%,
                            #5d7cff 48%,
                            #42a5ff 100%
                        );

                    box-shadow:
                        0 16px 38px rgba(81, 70, 214, .22),
                        inset 0 1px 0 rgba(255, 255, 255, .22);

                    font: inherit;
                    font-weight: 800;

                    cursor: pointer;

                    transition:
                        transform .22s cubic-bezier(.2, .8, .2, 1),
                        box-shadow .22s ease,
                        filter .22s ease,
                        border-color .22s ease;
                }

                .mashal-passkeys button::before {
                    content: "";

                    position: absolute;
                    inset: 0;
                    z-index: -1;

                    opacity: 0;

                    background:
                        linear-gradient(
                            115deg,
                            transparent 15%,
                            rgba(255, 255, 255, .18) 48%,
                            transparent 80%
                        );

                    transform:
                        translateX(-130%);

                    transition:
                        transform .65s cubic-bezier(.2, .8, .2, 1),
                        opacity .2s ease;
                }

                .mashal-passkeys button:hover:not(:disabled) {
                    transform:
                        translateY(-2px);

                    border-color:
                        rgba(122, 108, 255, .72);

                    box-shadow:
                        0 20px 48px rgba(81, 70, 214, .30),
                        0 10px 30px rgba(66, 165, 255, .12),
                        inset 0 1px 0 rgba(255, 255, 255, .24);

                    filter:
                        brightness(1.05);
                }

                .mashal-passkeys button:hover:not(:disabled)::before {
                    opacity: 1;

                    transform:
                        translateX(130%);
                }

                .mashal-passkeys button:active:not(:disabled) {
                    transform:
                        translateY(0)
                        scale(.99);
                }

                .mashal-passkeys [data-passkey-start] {
                    width: 100%;
                }

                .mashal-passkeys button:disabled {
                    opacity: .6;

                    cursor: wait;
                }

                .mashal-passkeys button:focus-visible,
                .mashal-passkeys input:focus-visible {
                    outline:
                        2px solid rgba(141, 240, 208, .85);

                    outline-offset:
                        3px;
                }

                .mashal-passkeys ul {
                    padding: 0;

                    list-style: none;
                }

                .mashal-passkeys li {
                    display: flex;
                    flex-wrap: wrap;
                    align-items: center;
                    justify-content: space-between;

                    gap: 12px;

                    padding: 12px 0;

                    border-bottom:
                        1px solid rgba(255, 255, 255, .075);
                }

                .mashal-passkeys .mashal-passkey-detail {
                    font-size: 12px;

                    color: #7d8796;
                }

                .mashal-passkeys [data-passkey-status] {
                    color: #aaa3ff;

                    overflow-wrap: anywhere;
                }

                @media (prefers-reduced-motion: reduce) {
                    .mashal-passkeys *,
                    .mashal-passkeys *::before,
                    .mashal-passkeys *::after {
                        transition-duration: .01ms !important;
                        animation-duration: .01ms !important;
                    }
                }
            </style>
        @endpush

        @push('scripts')
            <script
                src="{{ asset('js/mashal-passkeys.js') }}"
                defer
            ></script>
        @endpush
    @endonce
@endif