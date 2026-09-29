<style>
#guest-chat { --gold:#e7c28a; --ink:#f4f1eb; --muted:#a0a3ad; position:fixed; right:24px; bottom:max(24px,env(safe-area-inset-bottom)); z-index:1000; font:14px/1.6 system-ui,-apple-system,sans-serif; color:var(--ink); color-scheme:dark; text-align:left; }
#guest-chat, #guest-chat * { box-sizing:border-box; }
#guest-chat[hidden], #guest-chat [hidden] { display:none!important; }
#guest-chat button, #guest-chat textarea { font:inherit; }
#guest-chat button { cursor:pointer; }
#guest-chat button:disabled { opacity:.45; cursor:wait; }
#guest-chat :is(button,a,textarea):focus-visible { outline:2px solid var(--gold); outline-offset:3px; }
#guest-chat svg { width:20px; height:20px; display:block; flex-shrink:0; }
#guest-chat .gc-icon { display:grid; place-items:center; width:42px; height:42px; flex-shrink:0; border:1px solid #ffe5b44d; border-radius:14px; color:#251b0e; background:linear-gradient(135deg,#f5deaf,#cf9d54); box-shadow:inset 0 1px #fff5; }
#guest-chat .guest-chat__toggle { display:flex; align-items:center; gap:12px; min-height:64px; margin-left:auto; padding:10px 20px 10px 10px; border:1px solid #d8b77b55; border-radius:22px; color:var(--ink); background:linear-gradient(125deg,#24221e,#111318); box-shadow:0 12px 40px #0008; text-align:left; transition:transform .2s; }
#guest-chat .guest-chat__toggle:hover { transform:translateY(-3px); }
#guest-chat .gc-launch-copy { display:flex; flex-direction:column; }
#guest-chat .gc-launch-copy strong { font-size:14px; font-weight:650; }
#guest-chat .gc-launch-copy small { color:#b8b0a2; font-size:11px; }
#guest-chat .guest-chat__panel { display:flex; flex-direction:column; width:min(410px,calc(100vw - 32px)); height:650px; max-height:calc(100dvh - 118px); overflow:hidden; margin-bottom:14px; border:1px solid #e7c28a38; border-radius:24px; background:#101216; box-shadow:0 28px 90px #000a,inset 0 1px #ffffff0a; }
#guest-chat .guest-chat__header { display:flex; align-items:center; gap:11px; flex-shrink:0; padding:17px 16px; border-bottom:1px solid #ffffff0c; background:linear-gradient(120deg,#242119,#15171c); }
#guest-chat .guest-chat__header h2 { margin:0; color:var(--ink); font:650 16px/1.4 system-ui,sans-serif; letter-spacing:-.35px; }
#guest-chat .gc-subtitle { margin:3px 0 0; color:#b6b2a9; font-size:11px; }
#guest-chat .gc-actions { display:flex; gap:4px; margin-left:auto; }
#guest-chat .gc-action { display:grid; place-items:center; width:40px; height:44px; padding:0; border:0; border-radius:11px; color:#bbbcbf; background:transparent; }
#guest-chat .gc-action:hover { color:#fff; background:#ffffff0b; }
#guest-chat .gc-body { flex:1; min-height:0; overflow-y:auto; overscroll-behavior:contain; scrollbar-width:thin; scrollbar-color:#484239 transparent; }
#guest-chat .gc-welcome { padding:25px 22px 12px; background:radial-gradient(ellipse at 20% 0%,#dfba7610,transparent 75%); }
#guest-chat .gc-eyebrow { margin:0 0 10px; color:var(--gold); font-size:10px; font-weight:600; letter-spacing:1.6px; text-transform:uppercase; }
#guest-chat .gc-welcome h3 { margin:0 0 10px; color:#f6f2e9; font:550 28px/1.2 system-ui,sans-serif; letter-spacing:-1px; }
#guest-chat .gc-welcome > p:last-of-type { margin:0; max-width:310px; font-size:13px; color:var(--muted); line-height:1.7; }
#guest-chat .guest-chat__suggestions { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:21px; }
#guest-chat .guest-chat__suggestions button { min-height:78px; padding:12px; border:1px solid #ffffff12; border-radius:13px; background:#ffffff03; color:#e2dfd8; text-align:left; font-size:12px; transition:background .15s,border-color .15s; }
#guest-chat .guest-chat__suggestions button:hover { border-color:#e7c28a66; background:#e7c28a0a; }
#guest-chat .gc-topic { display:block; margin-bottom:4px; color:#e7c28a; font-size:10px; letter-spacing:.2px; }
#guest-chat .guest-chat__messages { padding:12px 18px 4px; }
#guest-chat .gc-turn { margin-bottom:20px; }
#guest-chat .gc-speaker { display:block; margin:0 0 5px 2px; color:#9a9da6; font-size:10px; letter-spacing:.4px; }
#guest-chat .guest-chat__message { width:fit-content; max-width:96%; margin:0; padding:12px 14px; border:1px solid #ffffff0b; border-radius:4px 16px 16px; background:#1b1e25; color:#e6e5e1; white-space:pre-wrap; overflow-wrap:anywhere; font-size:14px; line-height:1.75; }
#guest-chat .gc-turn--user { display:flex; flex-direction:column; align-items:flex-end; }
#guest-chat .guest-chat__message--user { border:0; border-radius:16px 4px 16px 16px; background:#e5c18a; color:#261c0e; }
#guest-chat .gc-copy { margin-top:6px; padding:5px 7px; border:0; border-radius:6px; color:#a2a5af; background:transparent; font-size:11px; }
#guest-chat .gc-copy:hover { color:var(--gold); background:#ffffff08; }
#guest-chat .gc-pending { margin:0 0 14px; padding:10px 0; color:var(--gold); font-size:12px; }
#guest-chat .gc-bottom { flex-shrink:0; padding-top:9px; border-top:1px solid #ffffff09; background:#111318; }
#guest-chat .gc-links { display:flex; justify-content:space-between; gap:8px; padding:0 19px 10px; }
#guest-chat .gc-links a { color:#aaa7a0; font-size:11px; text-decoration:none; }
#guest-chat .gc-links a:hover { color:var(--gold); text-decoration:underline; }
#guest-chat .guest-chat__form { display:flex; align-items:flex-end; gap:7px; margin:0 14px; padding:7px; border:1px solid #ffffff24; border-radius:16px; background:#090b0e; }
#guest-chat .guest-chat__form:focus-within { border-color:#e7c28a88; }
#guest-chat textarea { display:block; resize:none; width:100%; min-width:0; height:44px; max-height:110px; padding:10px 8px; border:0; border-radius:8px; background:transparent; color:var(--ink); font-size:16px; line-height:24px; }
#guest-chat textarea::placeholder { color:#898d97; }
#guest-chat .guest-chat__send { display:grid; place-items:center; width:44px; height:44px; padding:0; flex-shrink:0; border:0; border-radius:11px; background:linear-gradient(135deg,#f2d6a1,#d3a359); color:#261b0b; }
#guest-chat .guest-chat__notice { margin:0; padding:9px 14px 12px; text-align:center; color:#8f939c; font-size:10px; }
#guest-chat .gc-reset-confirm { display:flex; align-items:center; gap:8px; padding:10px 16px; background:#252119; font-size:12px; }
#guest-chat .gc-reset-confirm button { padding:7px 10px; border:1px solid #ffffff20; border-radius:8px; background:#111318; color:#edcf9a; }
@media(min-width:700px) { #guest-chat .guest-chat__panel[data-expanded="true"] { width:min(640px,calc(100vw - 48px)); height:760px; } }
@media(max-width:699px) {
 #guest-chat { right:16px; bottom:max(16px,env(safe-area-inset-bottom)); }
 #guest-chat .gc-expand { display:none; }
 #guest-chat[data-open="true"] { bottom:var(--gc-keyboard-bottom,16px); }
 #guest-chat[data-open="true"] .guest-chat__toggle { display:none; }
 #guest-chat .guest-chat__panel { width:calc(100vw - 32px); height:640px; max-height:calc(var(--gc-viewport-height,100dvh) - 32px); margin-bottom:0; }
 #guest-chat .guest-chat__header { padding:12px 14px; }
 #guest-chat .gc-welcome { padding:20px 18px 10px; }
}
@media(prefers-reduced-motion:reduce) { #guest-chat * { transition:none!important; } }
</style>

<aside id="guest-chat" class="guest-chat" aria-label="Mashal AI-chat" hidden data-endpoint="{{ route('guest-chat.message') }}">
    <section id="guest-chat-panel" class="guest-chat__panel" aria-labelledby="guest-chat-title" hidden>
        <header class="guest-chat__header">
            <span class="gc-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"><path d="m12 3 2.6 6.4L21 12l-6.4 2.6L12 21l-2.6-6.4L3 12l6.4-2.6L12 3Z"/></svg></span>
            <div><h2 id="guest-chat-title">Mashal AI</h2><p class="gc-subtitle">Je assistent voor ideeën & antwoorden</p></div>
            <div class="gc-actions">
                <button type="button" class="gc-action gc-reset" aria-label="Nieuw gesprek" title="Nieuw gesprek"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></button>
                <button type="button" class="gc-action gc-expand" aria-label="Chat vergroten" aria-pressed="false" title="Chat vergroten"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4h6v6M20 4l-7 7M10 20H4v-6m0 6 7-7"/></svg></button>
                <button type="button" class="gc-action guest-chat__close" aria-label="Chat sluiten" title="Chat sluiten"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="m6 6 12 12M18 6 6 18"/></svg></button>
            </div>
        </header>
        <div class="gc-reset-confirm" hidden><span>Dit gesprek wissen?</span><button type="button" data-reset-confirm>Wissen</button><button type="button" data-reset-cancel>Annuleren</button></div>
        <div class="gc-body">
            <div class="gc-welcome">
                <p class="gc-eyebrow">Een vraag. Een goed begin.</p>
                <h3>Waar kan ik je<br>mee helpen?</h3>
                <p>Van een eerste idee tot hulp bij Mashal Studio. Stel je vraag, dan denken we samen verder.</p>
                <div class="guest-chat__suggestions" aria-label="Voorbeeldvragen">
                    <button type="button" data-question="Hoe kan ik een afbeelding uploaden en bewerken op Mashal Studio?"><span class="gc-topic">AFBEELDINGEN</span>Maak meer van je foto</button>
                    <button type="button" data-question="Help mij een professionele e-mail schrijven."><span class="gc-topic">SCHRIJVEN</span>Vind de juiste woorden</button>
                    <button type="button" data-question="Ik heb hulp nodig bij het inloggen op Mashal Studio."><span class="gc-topic">ACCOUNT</span>Hulp bij het inloggen</button>
                    <button type="button" data-question="Wat kan ik allemaal doen met Mashal Studio?"><span class="gc-topic">ONTDEKKEN</span>Leer de website kennen</button>
                </div>
            </div>
            <div class="guest-chat__messages" role="log" aria-live="polite" aria-relevant="additions" aria-label="Chatberichten"></div>
        </div>
        <div class="gc-bottom">
            <nav class="gc-links" aria-label="Handige pagina's"><a href="{{ route('contact') }}">Contact opnemen ↗</a><a href="{{ route('privacy') }}">Privacy</a></nav>
            <form class="guest-chat__form">
                <textarea rows="1" aria-label="Je bericht aan Mashal AI" placeholder="Vraag het Mashal AI…" maxlength="2000" required></textarea>
                <button type="submit" class="guest-chat__send" aria-label="Bericht versturen"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5m-6 6 6-6 6 6"/></svg></button>
            </form>
            <p class="guest-chat__notice">AI kan fouten maken. Controleer belangrijke informatie.</p>
        </div>
    </section>
    <button type="button" class="guest-chat__toggle" aria-expanded="false" aria-controls="guest-chat-panel" aria-label="Chat met Mashal AI">
        <span class="gc-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20 11.5a8 8 0 0 1-11.5 7.2L3 21l1.8-5.7A8 8 0 1 1 20 11.5Z"/><path d="m12 7 1 2.5 2.5 1L13 11.5 12 14l-1-2.5-2.5-1 2.5-1L12 7Z"/></svg></span>
        <span class="gc-launch-copy"><strong>Chat met Mashal AI</strong><small>Een slimme hulp, dichtbij</small></span>
    </button>
</aside>
<script src="{{ asset('js/guest-chat.js') }}?v=6" defer></script>
