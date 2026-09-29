<style>
    .guest-chat { position: fixed; right: 20px; bottom: max(20px, env(safe-area-inset-bottom)); z-index: 1000; font: 14px/1.5 system-ui, sans-serif; color: #f7f7f4; }
    .guest-chat * { box-sizing: border-box; }
    .guest-chat [hidden] { display: none !important; }
    .guest-chat button, .guest-chat input { font: inherit; }
    .guest-chat button { cursor: pointer; }
    .guest-chat button:focus-visible, .guest-chat a:focus-visible, .guest-chat input:focus-visible { outline: 2px solid #f3d69a; outline-offset: 3px; }
    .guest-chat__toggle, .guest-chat__send { border: 0; border-radius: 24px; background: #e3b36b; color: #17120c; padding: 12px 18px; font-weight: 700 !important; }
    .guest-chat__toggle { display: block; margin-left: auto; box-shadow: 0 8px 30px #0006; }
    .guest-chat__panel { width: min(370px, calc(100vw - 32px)); max-height: min(600px, calc(100dvh - 110px)); margin-bottom: 12px; display: flex; flex-direction: column; overflow: hidden; background: #0d1015; border: 1px solid #55504a; border-radius: 20px; box-shadow: 0 16px 60px #0008; }
    .guest-chat__header { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px; border-bottom: 1px solid #ffffff20; }
    .guest-chat__header h2 { font: 700 16px/1.4 system-ui, sans-serif; margin: 0; color: #f7f7f4; }
    .guest-chat__header p, .guest-chat__notice { margin: 4px 0 0; font-size: 12px; color: #b8bdc6; }
    .guest-chat__close { border: 0; background: transparent; color: #f7f7f4; min-width: 44px; min-height: 44px; font-size: 24px !important; }
    .guest-chat__messages { overflow-y: auto; padding: 16px; min-height: 90px; overscroll-behavior: contain; }
    .guest-chat__message { padding: 11px 13px; margin: 0 0 10px; border-radius: 14px; background: #1c222c; overflow-wrap: anywhere; white-space: pre-wrap; }
    .guest-chat__message--user { margin-left: 24px; background: #e3b36b; color: #17120c; }
    .guest-chat__message a { display: block; margin-top: 8px; color: #f3d69a; text-decoration: underline; }
    .guest-chat__suggestions { display: flex; gap: 8px; flex-wrap: wrap; padding: 0 16px 12px; }
    .guest-chat__suggestions button { border: 1px solid #55504a; border-radius: 18px; color: #f3d69a; background: transparent; padding: 7px 11px; }
    .guest-chat__form { display: flex; gap: 8px; padding: 12px 16px; border-top: 1px solid #ffffff20; }
    .guest-chat__form input { min-width: 0; width: 100%; border: 1px solid #55504a; border-radius: 12px; padding: 10px; background: #07080b; color: #fff; font-size: 16px; }
    .guest-chat__notice { padding: 0 16px 14px; }
    @media (max-width: 480px) { .guest-chat { right: 16px; bottom: max(16px, env(safe-area-inset-bottom)); } }
</style>

<aside class="guest-chat" id="guest-chat" aria-label="Hulp voor bezoekers" hidden
    data-login-url="{{ route('login') }}"
    data-register-url="{{ route('register') }}"
    data-password-url="{{ route('password.request') }}"
    data-contact-url="{{ route('contact') }}"
    data-home-url="{{ route('home') }}">
    <section class="guest-chat__panel" id="guest-chat-panel" aria-labelledby="guest-chat-title" hidden>
        <header class="guest-chat__header">
            <div>
                <h2 id="guest-chat-title">Mashal hulpassistent</h2>
                <p>Automatische hulp · geen login nodig</p>
            </div>
            <button type="button" class="guest-chat__close" aria-label="Chat sluiten">×</button>
        </header>
        <div class="guest-chat__messages" role="log" aria-live="polite" aria-relevant="additions" aria-label="Chatberichten">
            <p class="guest-chat__message">Hoi! Ik help je met inloggen, registreren en afbeeldingen. Waar wil je meer over weten?</p>
        </div>
        <div class="guest-chat__suggestions" aria-label="Veelgestelde vragen">
            <button type="button" data-question="Hoe kan ik inloggen?">Inloggen</button>
            <button type="button" data-question="Hoe maak ik een account?">Account maken</button>
            <button type="button" data-question="Hoe bewerk ik een afbeelding?">Afbeeldingen</button>
        </div>
        <form class="guest-chat__form">
            <input type="text" aria-label="Je vraag" placeholder="Stel je vraag…" maxlength="500" autocomplete="off" required>
            <button class="guest-chat__send" type="submit">Stuur</button>
        </form>
        <p class="guest-chat__notice">Vaste antwoorden, geen live medewerker. Deel geen wachtwoorden of codes. Deze chat wordt niet opgeslagen of verstuurd.</p>
    </section>
    <button type="button" class="guest-chat__toggle" aria-expanded="false" aria-controls="guest-chat-panel">Hulp nodig?</button>
</aside>
<script src="{{ asset('js/guest-chat.js') }}" defer></script>
