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

#guest-chat .lc-modes { display:flex; flex-shrink:0; gap:6px; padding:9px 14px; border-bottom:1px solid #ffffff12; }

#guest-chat .lc-modes button { flex:1; min-height:40px; border:1px solid transparent; border-radius:10px; color:#aaa; background:transparent; }

#guest-chat .lc-modes button[aria-pressed="true"] { color:#f0d4a4; border-color:#e7c28a33; background:#e7c28a12; }

#guest-chat .lc-panel { display:flex; flex:1; min-height:0; flex-direction:column; }

#guest-chat .lc-status { margin:0; padding:12px 18px; border-bottom:1px solid #ffffff0c; color:#bbb6ad; font-size:12px; }

#guest-chat .lc-log { flex:1; min-height:0; overflow:auto; padding:16px; overscroll-behavior:contain; }

#guest-chat .lc-log p { white-space:pre-wrap; overflow-wrap:anywhere; }

#guest-chat .lc-msg { margin:0 0 14px; padding:12px; max-width:94%; border-radius:14px 14px 14px 3px; background:#20232b; }

#guest-chat .lc-msg--visitor { margin-left:auto; border-radius:14px 14px 3px 14px; color:#21180c; background:#e5c18a; }

#guest-chat .lc-msg small { display:block; margin-bottom:5px; font-size:10px; opacity:.75; }

#guest-chat .lc-msg p { margin:0; }

#guest-chat .lc-error { padding:0 16px; color:#f6aeae; font-size:12px; }

#guest-chat .lc-reopen { min-height:44px; margin:8px 16px; border:1px solid #e7c28a55; border-radius:10px; background:#211d16; color:#e7c28a; }



#guest-chat .lc-msg-head { display:flex; align-items:center; gap:8px; margin-bottom:7px; }

#guest-chat .lc-avatar { width:28px; height:28px; border-radius:50%; overflow:hidden; flex:0 0 28px; display:grid; place-items:center; background:#343843; color:#fff; font-size:11px; font-weight:700; }

#guest-chat .lc-avatar img { width:100%; height:100%; object-fit:cover; }

#guest-chat .lc-msg-head small { margin:0; }

#guest-chat .lc-media-image { display:block; max-width:min(260px,100%); max-height:260px; border-radius:10px; object-fit:cover; }

#guest-chat .lc-file-link { color:inherit; text-decoration:underline; overflow-wrap:anywhere; }

#guest-chat .lc-msg audio { width:min(260px,100%); }

#guest-chat .lc-delete { margin-top:7px; padding:2px 0; border:0; background:transparent; color:inherit; opacity:.65; font-size:10px; }

#guest-chat .lc-delete:hover { opacity:1; text-decoration:underline; }

#guest-chat .lc-tool { display:grid; place-items:center; width:38px; height:42px; flex:0 0 38px; border:0; border-radius:9px; background:#20232a; color:#e8c792; cursor:pointer; }

#guest-chat .lc-tool[aria-pressed="true"] { background:#7f3030; color:#fff; }


/* ==========================================================================
   MASHAL CHAT — EXPERT LAUNCHER + INCOMING MESSAGE SYSTEM
   ========================================================================== */

#guest-chat {
    --gc-accent: #7a6cff;
    --gc-accent-2: #42a5ff;
    --gc-accent-soft: rgba(122,108,255,.14);
    --gc-surface: rgba(11,13,19,.94);
}

/* Alleen de floating launcher wordt opnieuw ontworpen.
   De bestaande chatpanel-functionaliteit blijft intact. */
#guest-chat .guest-chat__toggle {
    position: relative;
    isolation: isolate;
    width: 60px;
    height: 60px;
    min-height: 60px;
    padding: 0;
    gap: 0;
    justify-content: center;
    overflow: visible;
    border: 1px solid rgba(122,108,255,.38);
    border-radius: 19px;
    color: #fff;
    background:
        radial-gradient(circle at 30% 20%, rgba(255,255,255,.18), transparent 32%),
        linear-gradient(145deg, #7869ff 0%, #626fff 46%, #42a5ff 100%);
    box-shadow:
        0 18px 46px rgba(0,0,0,.42),
        0 10px 30px rgba(83,72,220,.24),
        inset 0 1px 0 rgba(255,255,255,.28);
    transform: translateZ(0);
    transition:
        transform .24s cubic-bezier(.2,.8,.2,1),
        box-shadow .24s ease,
        border-color .24s ease,
        filter .24s ease;
}

#guest-chat .guest-chat__toggle::before {
    content: "";
    position: absolute;
    inset: -7px;
    z-index: -1;
    border: 1px solid rgba(122,108,255,.20);
    border-radius: 24px;
    opacity: .72;
    transform: scale(.94);
    transition:
        opacity .25s ease,
        transform .25s ease;
}

#guest-chat .guest-chat__toggle::after {
    content: "";
    position: absolute;
    inset: -1px;
    z-index: -2;
    border-radius: inherit;
    opacity: 0;
    background:
        conic-gradient(
            from 0deg,
            transparent 0 18%,
            rgba(122,108,255,.9) 30%,
            transparent 42% 62%,
            rgba(66,165,255,.9) 75%,
            transparent 88% 100%
        );
}

#guest-chat .guest-chat__toggle:hover {
    transform: translateY(-3px) scale(1.035);
    border-color: rgba(151,143,255,.62);
    filter: brightness(1.05);
    box-shadow:
        0 24px 56px rgba(0,0,0,.46),
        0 14px 38px rgba(83,72,220,.30),
        0 0 0 1px rgba(122,108,255,.10),
        inset 0 1px 0 rgba(255,255,255,.30);
}

#guest-chat .guest-chat__toggle:hover::before {
    opacity: 1;
    transform: scale(1);
}

#guest-chat .guest-chat__toggle:active {
    transform: translateY(-1px) scale(.94);
}

/* Launcher-icoon: modern chat bubble i.p.v. goud blok. */
#guest-chat .guest-chat__toggle .gc-icon {
    width: 32px;
    height: 32px;
    border: 0;
    border-radius: 0;
    color: #fff;
    background: transparent;
    box-shadow: none;
}

#guest-chat .guest-chat__toggle .gc-icon svg {
    width: 28px;
    height: 28px;
    filter: drop-shadow(0 4px 10px rgba(18,22,55,.28));
}

/* Hover-label zoals moderne SaaS chat-launchers. */
#guest-chat .gc-launch-copy {
    position: absolute;
    right: 72px;
    top: 50%;
    width: max-content;
    max-width: min(260px, calc(100vw - 110px));
    padding: 10px 13px;
    display: flex;
    flex-direction: column;
    gap: 1px;
    border: 1px solid rgba(255,255,255,.09);
    border-radius: 12px;
    color: #f6f8fb;
    background: rgba(10,12,18,.94);
    box-shadow:
        0 16px 44px rgba(0,0,0,.38),
        inset 0 1px 0 rgba(255,255,255,.045);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transform: translate3d(8px,-50%,0) scale(.96);
    transform-origin: right center;
    transition:
        opacity .18s ease,
        visibility .18s ease,
        transform .24s cubic-bezier(.2,.8,.2,1);
}

#guest-chat .guest-chat__toggle:hover .gc-launch-copy,
#guest-chat .guest-chat__toggle:focus-visible .gc-launch-copy {
    opacity: 1;
    visibility: visible;
    transform: translate3d(0,-50%,0) scale(1);
}

#guest-chat .gc-launch-copy strong {
    color: #f7f8fc;
    font-size: 12px;
    font-weight: 760;
}

#guest-chat .gc-launch-copy small {
    color: #818997;
    font-size: 10px;
}

/* Online-dot. */
#guest-chat .gc-launch-status {
    position: absolute;
    right: 4px;
    bottom: 4px;
    width: 12px;
    height: 12px;
    border: 3px solid #111318;
    border-radius: 50%;
    background: #74dfa7;
    box-shadow: 0 0 12px rgba(116,223,167,.45);
}

/* Unread counter. */
#guest-chat .gc-unread-badge {
    position: absolute;
    right: -6px;
    top: -7px;
    z-index: 5;
    min-width: 22px;
    height: 22px;
    padding: 0 6px;
    display: inline-grid;
    place-items: center;
    border: 2px solid #090b10;
    border-radius: 999px;
    color: #fff;
    background: linear-gradient(135deg, #ff5d7d, #ff3f68);
    box-shadow: 0 6px 16px rgba(255,63,104,.32);
    font-size: 10px;
    font-weight: 900;
    line-height: 1;
}

#guest-chat .gc-unread-badge[hidden] {
    display: none !important;
}

/* Inkomend bericht: korte premium pulse. */
#guest-chat .guest-chat__toggle.has-unread {
    animation: gcLauncherAttention 1.15s cubic-bezier(.2,.8,.2,1) 2;
}

#guest-chat .guest-chat__toggle.has-unread::after {
    opacity: .85;
    animation: gcLauncherOrbit 2.2s linear infinite;
}

/* In-app notificatie boven de launcher. */
#guest-chat .gc-incoming-toast {
    position: absolute;
    right: 0;
    bottom: 76px;
    width: min(320px, calc(100vw - 32px));
    padding: 13px 14px;
    display: grid;
    grid-template-columns: 34px minmax(0,1fr) auto;
    gap: 10px;
    align-items: center;
    border: 1px solid rgba(122,108,255,.20);
    border-radius: 15px;
    color: #eef1f7;
    background:
        linear-gradient(145deg, rgba(122,108,255,.08), transparent 46%),
        rgba(10,12,18,.96);
    box-shadow:
        0 24px 60px rgba(0,0,0,.46),
        0 0 32px rgba(122,108,255,.08);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transform: translateY(10px) scale(.97);
    transition:
        opacity .22s ease,
        visibility .22s ease,
        transform .28s cubic-bezier(.2,.8,.2,1);
}

#guest-chat .gc-incoming-toast.is-visible {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
}

#guest-chat .gc-incoming-toast__icon {
    width: 34px;
    height: 34px;
    display: grid;
    place-items: center;
    border: 1px solid rgba(122,108,255,.22);
    border-radius: 10px;
    color: #b8b2ff;
    background: rgba(122,108,255,.10);
}

#guest-chat .gc-incoming-toast__icon svg {
    width: 18px;
    height: 18px;
}

#guest-chat .gc-incoming-toast strong,
#guest-chat .gc-incoming-toast span {
    display: block;
}

#guest-chat .gc-incoming-toast strong {
    color: #f5f7fb;
    font-size: 11px;
    font-weight: 800;
}

#guest-chat .gc-incoming-toast span {
    margin-top: 2px;
    overflow: hidden;
    color: #838b99;
    font-size: 10px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

#guest-chat .gc-incoming-toast__dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #74dfa7;
    box-shadow: 0 0 12px rgba(116,223,167,.52);
}

/* Ook de focusring van de launcher sluit nu aan op home. */
#guest-chat .guest-chat__toggle:focus-visible {
    outline: 2px solid rgba(122,108,255,.86);
    outline-offset: 4px;
}

@keyframes gcLauncherAttention {
    0%, 100% {
        transform: translateY(0) scale(1);
    }
    34% {
        transform: translateY(-4px) scale(1.055) rotate(-2deg);
    }
    68% {
        transform: translateY(-2px) scale(1.025) rotate(2deg);
    }
}

@keyframes gcLauncherOrbit {
    to {
        transform: rotate(360deg);
    }
}

@media (max-width: 699px) {
    #guest-chat .guest-chat__toggle {
        width: 58px;
        height: 58px;
        min-height: 58px;
        border-radius: 18px;
    }

    #guest-chat .gc-launch-copy {
        display: none;
    }

    #guest-chat .gc-incoming-toast {
        right: 0;
        bottom: 72px;
        width: min(300px, calc(100vw - 32px));
    }
}

@media (prefers-reduced-motion: reduce) {
    #guest-chat .guest-chat__toggle.has-unread,
    #guest-chat .guest-chat__toggle.has-unread::after {
        animation: none !important;
    }

    #guest-chat .gc-incoming-toast {
        transition-duration: .01ms !important;
    }
}

</style>



<aside id="guest-chat" class="guest-chat" aria-label="Mashal chat" hidden data-mode="ai" data-endpoint="{{ route('guest-chat.message') }}">

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

        @if (\Illuminate\Support\Facades\Route::has('live-chat.show'))

            <section class="lc-panel" aria-label="Live chat met medewerker" hidden

                data-show="{{ route('live-chat.show') }}" data-store="{{ route('live-chat.store') }}" data-reopen="{{ route('live-chat.reopen') }}">

                <p class="lc-status" role="status">Beschikbaarheid controleren…</p>

                <div class="lc-log" role="log" aria-live="polite" aria-relevant="additions" aria-label="Gesprek met medewerker"></div>

                <p class="lc-error" role="status" hidden></p>

                <button type="button" class="lc-reopen" hidden>Gesprek opnieuw openen</button>

                <form class="guest-chat__form lc-form">

                    <label class="lc-tool" title="Bestand versturen" aria-label="Bestand versturen">📎<input class="lc-file" type="file" accept="image/jpeg,image/png,image/webp,image/gif,application/pdf,text/plain,.doc,.docx,.xls,.xlsx" hidden></label>

                    <button class="lc-tool lc-voice" type="button" title="Spraakbericht opnemen" aria-label="Spraakbericht opnemen">🎤</button>

                    <textarea class="lc-input" rows="1" maxlength="4000" aria-label="Bericht aan medewerker" placeholder="Schrijf je bericht…" required></textarea>

                    <button class="guest-chat__send" type="submit" aria-label="Bericht aan medewerker versturen">↑</button>

                </form>

                <p class="guest-chat__notice">Je praat met een medewerker. Berichten worden bewaard voor ondersteuning.

                    @guest Bewaar deze browsersessie om antwoorden te ontvangen. @endguest

                </p>

            </section>

        @endif

    </section>

    <button
        type="button"
        class="guest-chat__toggle"
        aria-expanded="false"
        aria-controls="guest-chat-panel"
        aria-label="Open Mashal chat"
    >
        <span class="gc-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 11.5a8 8 0 0 1-11.5 7.2L3 21l1.8-5.7A8 8 0 1 1 20 11.5Z"/>
                <path d="M8.5 11.5h7"/>
                <path d="M12 8v7"/>
            </svg>
        </span>

        <span class="gc-launch-copy">
            <strong>Mashal Support</strong>
            <small>Stel een vraag of start live chat</small>
        </span>

        <span class="gc-launch-status" aria-hidden="true"></span>

        <span
            class="gc-unread-badge"
            data-chat-unread
            aria-label="0 ongelezen berichten"
            hidden
        >0</span>
    </button>

    <div
        class="gc-incoming-toast"
        data-chat-toast
        role="status"
        aria-live="polite"
        aria-atomic="true"
    >
        <span class="gc-incoming-toast__icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 11.5a8 8 0 0 1-11.5 7.2L3 21l1.8-5.7A8 8 0 1 1 20 11.5Z"/>
            </svg>
        </span>

        <span>
            <strong>Nieuw bericht</strong>
            <span data-chat-toast-text>Je hebt een nieuw bericht ontvangen.</span>
        </span>

        <span class="gc-incoming-toast__dot" aria-hidden="true"></span>
    </div>

</aside>


<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const root = document.getElementById('guest-chat');

    if (!root) {
        return;
    }

    const toggle = root.querySelector('.guest-chat__toggle');
    const panel = root.querySelector('#guest-chat-panel');
    const unreadBadge = root.querySelector('[data-chat-unread]');
    const toast = root.querySelector('[data-chat-toast]');
    const toastText = root.querySelector('[data-chat-toast-text]');
    const liveLog = root.querySelector('.lc-log');
    const aiLog = root.querySelector('.guest-chat__messages');

    if (!toggle || !panel) {
        return;
    }

    let unread = 0;
    let armed = false;
    let toastTimer = null;

    const isOpen = function () {
        return (
            root.dataset.open === 'true'
            || toggle.getAttribute('aria-expanded') === 'true'
            || panel.hidden === false
        );
    };

    const clearUnread = function () {
        unread = 0;
        unreadBadge.textContent = '0';
        unreadBadge.hidden = true;
        unreadBadge.setAttribute(
            'aria-label',
            '0 ongelezen berichten'
        );

        toggle.classList.remove('has-unread');
    };

    const addUnread = function () {
        unread += 1;

        unreadBadge.textContent =
            unread > 9
                ? '9+'
                : String(unread);

        unreadBadge.hidden = false;

        unreadBadge.setAttribute(
            'aria-label',
            unread + (
                unread === 1
                    ? ' ongelezen bericht'
                    : ' ongelezen berichten'
            )
        );

        toggle.classList.remove('has-unread');

        window.requestAnimationFrame(function () {
            toggle.classList.add('has-unread');
        });
    };

    const showToast = function (message) {
        if (!toast) {
            return;
        }

        if (toastText) {
            toastText.textContent =
                message || 'Je hebt een nieuw bericht ontvangen.';
        }

        toast.classList.add('is-visible');

        window.clearTimeout(toastTimer);

        toastTimer = window.setTimeout(function () {
            toast.classList.remove('is-visible');
        }, 4200);
    };

    const requestNotificationPermission = function () {
        if (
            !('Notification' in window)
            || Notification.permission !== 'default'
        ) {
            return;
        }

        try {
            const result = Notification.requestPermission();

            if (result && typeof result.catch === 'function') {
                result.catch(function () {
                    // Browsermelding is optioneel; in-app melding blijft werken.
                });
            }
        } catch (error) {
            // Geen blokkade wanneer browsernotificaties niet beschikbaar zijn.
        }
    };

    const showBrowserNotification = function (message) {
        if (
            !('Notification' in window)
            || Notification.permission !== 'granted'
            || !document.hidden
        ) {
            return;
        }

        try {
            const notification = new Notification(
                'Nieuw bericht · Mashal Studio',
                {
                    body:
                        message
                        || 'Je hebt een nieuw bericht ontvangen.',
                    tag: 'mashal-chat-message'
                }
            );

            notification.onclick = function () {
                window.focus();

                if (!isOpen()) {
                    toggle.click();
                }

                clearUnread();
                notification.close();
            };

            window.setTimeout(function () {
                notification.close();
            }, 6500);
        } catch (error) {
            // In-app notificatie blijft beschikbaar.
        }
    };

    const openForIncomingMessage = function () {
        if (!isOpen()) {
            toggle.click();
        }
    };

    const getMessageText = function (node) {
        const text =
            (node?.textContent || '')
                .replace(/\s+/g, ' ')
                .trim();

        if (!text) {
            return 'Je hebt een nieuw bericht ontvangen.';
        }

        return text.length > 92
            ? text.slice(0, 89) + '…'
            : text;
    };

    const notifyIncoming = function (node) {
        if (!armed) {
            return;
        }

        const message =
            getMessageText(node);

        addUnread();
        showToast(message);
        showBrowserNotification(message);
        openForIncomingMessage();
    };

    const containsIncomingLiveMessage = function (node) {
        if (!(node instanceof Element)) {
            return null;
        }

        if (
            node.matches('.lc-msg:not(.lc-msg--visitor)')
        ) {
            return node;
        }

        return node.querySelector(
            '.lc-msg:not(.lc-msg--visitor)'
        );
    };

    const containsIncomingAiMessage = function (node) {
        if (!(node instanceof Element)) {
            return null;
        }

        if (
            node.matches(
                '.gc-turn:not(.gc-turn--user), ' +
                '.guest-chat__message:not(.guest-chat__message--user)'
            )
        ) {
            return node;
        }

        return node.querySelector(
            '.gc-turn:not(.gc-turn--user), ' +
            '.guest-chat__message:not(.guest-chat__message--user)'
        );
    };

    const watchLog = function (
        target,
        detector
    ) {
        if (!target) {
            return;
        }

        const observer =
            new MutationObserver(function (mutations) {
                for (const mutation of mutations) {
                    for (const addedNode of mutation.addedNodes) {
                        const incoming =
                            detector(addedNode);

                        if (incoming) {
                            notifyIncoming(incoming);
                            return;
                        }
                    }
                }
            });

        observer.observe(
            target,
            {
                childList: true,
                subtree: true
            }
        );
    };

    watchLog(
        liveLog,
        containsIncomingLiveMessage
    );

    watchLog(
        aiLog,
        containsIncomingAiMessage
    );

    /*
     * Geef bestaande scripts eerst tijd om opgeslagen chatgeschiedenis
     * in de DOM te zetten. Daarna worden alleen nieuwe mutaties gemeld.
     */
    window.setTimeout(function () {
        armed = true;
    }, 2200);

    /*
     * Browsers staan notificatie-permissie alleen betrouwbaar toe
     * na een echte gebruikersactie.
     */
    toggle.addEventListener(
        'click',
        requestNotificationPermission,
        { once: true }
    );

    toggle.addEventListener(
        'click',
        function () {
            if (isOpen()) {
                clearUnread();
            }
        }
    );

    panel.addEventListener(
        'pointerdown',
        clearUnread
    );

    panel.addEventListener(
        'focusin',
        clearUnread
    );

    document.addEventListener(
        'visibilitychange',
        function () {
            if (
                !document.hidden
                && isOpen()
            ) {
                clearUnread();
            }
        }
    );
});
</script>


<script src="{{ asset('js/guest-chat.js') }}?v=7" defer></script>



<script src="{{ asset('js/live-chat.js') }}?v=2" defer></script>
