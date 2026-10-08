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

#guest-chat .lc-media-image, #guest-chat .lc-media-video { display:block; max-width:min(260px,100%); max-height:260px; border-radius:10px; object-fit:cover; }

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


/* ==========================================================================
   MASHAL CHAT V3 — PREMIUM PURPLE / RESPONSIVE SYSTEM
   Final visual layer. Existing AI/live-chat behavior stays unchanged.
   ========================================================================== */

#guest-chat {
    --gold: #8d84ff;
    --ink: #f7f8fc;
    --muted: #8d96a6;

    --gc-purple: #7a6cff;
    --gc-purple-2: #9388ff;
    --gc-blue: #42a5ff;
    --gc-mint: #74dfa7;

    --gc-bg: #07090e;
    --gc-surface: #0b0e15;
    --gc-surface-2: #10141d;
    --gc-surface-3: #151a25;

    --gc-line: rgba(255,255,255,.075);
    --gc-line-strong: rgba(255,255,255,.12);
    --gc-accent-line: rgba(122,108,255,.25);

    right: clamp(14px, 1.6vw, 24px);
    bottom: max(16px, env(safe-area-inset-bottom));
    font-family:
        Inter,
        ui-sans-serif,
        system-ui,
        -apple-system,
        BlinkMacSystemFont,
        "Segoe UI",
        sans-serif;
}

/* PANEL ================================================================ */

#guest-chat .guest-chat__panel {
    position: relative;
    isolation: isolate;

    width: min(390px, calc(100vw - 32px));
    height: min(620px, calc(100dvh - 105px));
    max-height: calc(100dvh - 105px);

    margin-bottom: 13px;

    overflow: hidden;

    border: 1px solid rgba(122,108,255,.20);
    border-radius: 22px;

    background:
        radial-gradient(
            circle at 85% 8%,
            rgba(66,165,255,.075),
            transparent 17rem
        ),
        radial-gradient(
            circle at 10% 0%,
            rgba(122,108,255,.11),
            transparent 18rem
        ),
        linear-gradient(
            180deg,
            rgba(13,16,24,.985),
            rgba(7,9,14,.99)
        );

    box-shadow:
        0 34px 95px rgba(0,0,0,.58),
        0 0 0 1px rgba(255,255,255,.015),
        0 0 48px rgba(79,69,210,.08),
        inset 0 1px 0 rgba(255,255,255,.055);

    backdrop-filter: blur(24px) saturate(125%);
    -webkit-backdrop-filter: blur(24px) saturate(125%);

    transform-origin: bottom right;
    animation:
        gcPremiumPanelIn
        .38s
        cubic-bezier(.16,1,.3,1)
        both;
}

#guest-chat .guest-chat__panel::before {
    content: "";
    position: absolute;
    z-index: -1;
    inset: 0;
    pointer-events: none;

    background:
        linear-gradient(
            120deg,
            rgba(255,255,255,.028),
            transparent 22% 74%,
            rgba(122,108,255,.028)
        );
}

/* HEADER =============================================================== */

#guest-chat .guest-chat__header {
    min-height: 74px;
    padding: 13px 13px 13px 14px;

    gap: 11px;

    border-bottom: 1px solid var(--gc-line);

    background:
        linear-gradient(
            135deg,
            rgba(122,108,255,.095),
            rgba(66,165,255,.025) 45%,
            rgba(255,255,255,.012)
        );
}

#guest-chat .guest-chat__header .gc-icon {
    width: 42px;
    height: 42px;

    border: 1px solid rgba(122,108,255,.30);
    border-radius: 13px;

    color: #c6c1ff;

    background:
        linear-gradient(
            145deg,
            rgba(122,108,255,.18),
            rgba(66,165,255,.055)
        );

    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.07),
        0 10px 26px rgba(79,69,210,.12);
}

#guest-chat .guest-chat__header h2 {
    color: #f7f8fc;
    font-size: 15px;
    font-weight: 780;
    letter-spacing: -.025em;
}

#guest-chat .gc-subtitle {
    max-width: 180px;
    margin-top: 2px;

    color: #7f8897;
    font-size: 10px;
    line-height: 1.45;
}

#guest-chat .gc-actions {
    gap: 3px;
}

#guest-chat .gc-action {
    width: 36px;
    height: 36px;

    border: 1px solid transparent;
    border-radius: 10px;

    color: #7d8695;

    transition:
        color .18s ease,
        border-color .18s ease,
        background .18s ease,
        transform .18s ease;
}

#guest-chat .gc-action:hover {
    transform: translateY(-1px);

    border-color: rgba(122,108,255,.15);

    color: #d5d1ff;

    background: rgba(122,108,255,.07);
}

/* BODY ================================================================= */

#guest-chat .gc-body {
    background:
        radial-gradient(
            circle at 15% 0%,
            rgba(122,108,255,.035),
            transparent 18rem
        );

    scrollbar-color:
        rgba(122,108,255,.28)
        transparent;
}

#guest-chat .gc-welcome {
    padding: 25px 21px 12px;

    background: transparent;
}

#guest-chat .gc-eyebrow {
    margin-bottom: 9px;

    color: #9188ff;

    font-size: 9px;
    font-weight: 800;
    letter-spacing: .16em;
}

#guest-chat .gc-eyebrow::before {
    content: "";

    width: 6px;
    height: 6px;

    margin-right: 7px;

    display: inline-block;

    border-radius: 2px;

    background:
        linear-gradient(
            135deg,
            var(--gc-purple),
            var(--gc-blue)
        );

    box-shadow:
        0 0 14px rgba(122,108,255,.55);
}

#guest-chat .gc-welcome h3 {
    max-width: 310px;

    margin-bottom: 11px;

    color: #f7f8fb;

    font-size: clamp(25px, 3vw, 31px);
    font-weight: 690;
    line-height: 1.02;
    letter-spacing: -.055em;
}

#guest-chat .gc-welcome > p:last-of-type {
    max-width: 315px;

    color: #7f8897;

    font-size: 12px;
    line-height: 1.72;
}

/* Suggestions */
#guest-chat .guest-chat__suggestions {
    gap: 7px;

    margin-top: 18px;
}

#guest-chat .guest-chat__suggestions button {
    min-height: 70px;

    padding: 11px;

    border: 1px solid var(--gc-line);
    border-radius: 12px;

    color: #c7ccd5;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.022),
            rgba(255,255,255,.006)
        );

    font-size: 11px;

    transition:
        transform .2s cubic-bezier(.2,.8,.2,1),
        border-color .2s ease,
        background .2s ease,
        box-shadow .2s ease;
}

#guest-chat .guest-chat__suggestions button:hover {
    transform: translateY(-2px);

    border-color: rgba(122,108,255,.28);

    background:
        linear-gradient(
            145deg,
            rgba(122,108,255,.09),
            rgba(66,165,255,.025)
        );

    box-shadow:
        0 10px 26px rgba(0,0,0,.16);
}

#guest-chat .gc-topic {
    margin-bottom: 5px;

    color: #978fff;

    font-size: 8px;
    font-weight: 850;
    letter-spacing: .08em;
}

/* MESSAGE BUBBLES ====================================================== */

#guest-chat .guest-chat__messages {
    padding:
        12px 16px 5px;
}

#guest-chat .gc-speaker {
    color: #717a89;
}

#guest-chat .guest-chat__message {
    max-width: 88%;

    padding: 11px 13px;

    border: 1px solid rgba(255,255,255,.055);
    border-radius: 5px 15px 15px 15px;

    color: #dce1e8;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.045),
            rgba(255,255,255,.014)
        ),
        #11151e;

    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.02);

    font-size: 13px;
    line-height: 1.68;
}

#guest-chat .guest-chat__message--user {
    border: 1px solid rgba(122,108,255,.22);
    border-radius: 15px 5px 15px 15px;

    color: #fff;

    background:
        linear-gradient(
            135deg,
            #7164f4,
            #547dff 58%,
            #439ffc
        );

    box-shadow:
        0 8px 22px rgba(81,70,214,.16),
        inset 0 1px 0 rgba(255,255,255,.16);
}

#guest-chat .gc-copy:hover {
    color: #9d96ff;
}

#guest-chat .gc-pending {
    color: #9189ff;
}

/* BOTTOM / INPUT ======================================================= */

#guest-chat .gc-bottom {
    padding-top: 9px;

    border-top: 1px solid var(--gc-line);

    background:
        linear-gradient(
            180deg,
            rgba(12,15,22,.95),
            rgba(8,10,15,.99)
        );
}

#guest-chat .gc-links {
    padding:
        0 15px 9px;
}

#guest-chat .gc-links a {
    color: #737d8c;

    font-size: 10px;
}

#guest-chat .gc-links a:hover {
    color: #a9a3ff;
}

#guest-chat .guest-chat__form {
    margin:
        0 12px;

    padding: 6px;

    gap: 6px;

    border: 1px solid rgba(255,255,255,.10);
    border-radius: 15px;

    background:
        rgba(5,7,11,.82);

    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.025);

    transition:
        border-color .2s ease,
        box-shadow .2s ease,
        background .2s ease;
}

#guest-chat .guest-chat__form:focus-within {
    border-color: rgba(122,108,255,.46);

    background:
        rgba(8,10,16,.94);

    box-shadow:
        0 0 0 3px rgba(122,108,255,.065),
        inset 0 1px 0 rgba(255,255,255,.035);
}

#guest-chat textarea {
    min-height: 43px;
    height: 43px;

    padding:
        9px 9px;

    color: #edf0f5;

    font-size: 14px;
    line-height: 23px;
}

#guest-chat textarea::placeholder {
    color: #626b78;
}

#guest-chat .guest-chat__send {
    width: 43px;
    height: 43px;

    border: 1px solid rgba(122,108,255,.38);
    border-radius: 11px;

    color: #fff;

    background:
        linear-gradient(
            135deg,
            #7a6cff,
            #5f7dff 50%,
            #42a5ff
        );

    box-shadow:
        0 9px 24px rgba(81,70,214,.24),
        inset 0 1px 0 rgba(255,255,255,.22);

    transition:
        transform .2s cubic-bezier(.2,.8,.2,1),
        filter .2s ease,
        box-shadow .2s ease;
}

#guest-chat .guest-chat__send:hover:not(:disabled) {
    transform: translateY(-2px);

    filter: brightness(1.06);

    box-shadow:
        0 13px 30px rgba(81,70,214,.30),
        inset 0 1px 0 rgba(255,255,255,.24);
}

#guest-chat .guest-chat__send:active:not(:disabled) {
    transform: scale(.94);
}

#guest-chat .guest-chat__notice {
    padding:
        8px 12px 11px;

    color: #555f6d;

    font-size: 9px;
}

/* RESET ================================================================ */

#guest-chat .gc-reset-confirm {
    padding:
        9px 13px;

    border-bottom:
        1px solid var(--gc-line);

    color: #a5adba;

    background:
        rgba(122,108,255,.045);
}

#guest-chat .gc-reset-confirm button {
    border-color: rgba(122,108,255,.14);

    color: #b8b2ff;

    background: rgba(122,108,255,.06);
}

/* AI / LIVE MODE TABS ================================================== */

#guest-chat .lc-modes {
    gap: 5px;

    padding:
        7px 11px;

    border-bottom:
        1px solid var(--gc-line);

    background:
        rgba(8,10,15,.60);
}

#guest-chat .lc-modes button {
    min-height: 36px;

    border-radius: 9px;

    color: #707988;

    font-size: 10px;
    font-weight: 760;
}

#guest-chat .lc-modes button[aria-pressed="true"] {
    border-color: rgba(122,108,255,.20);

    color: #c2bdff;

    background:
        linear-gradient(
            145deg,
            rgba(122,108,255,.12),
            rgba(66,165,255,.035)
        );
}

/* LIVE CHAT ============================================================ */

#guest-chat .lc-status {
    padding:
        10px 15px;

    border-bottom:
        1px solid var(--gc-line);

    color: #818a98;

    background:
        rgba(255,255,255,.008);
}

#guest-chat .lc-log {
    padding: 14px;

    scrollbar-color:
        rgba(122,108,255,.28)
        transparent;
}

#guest-chat .lc-msg {
    max-width: 88%;

    padding:
        10px 11px;

    border:
        1px solid rgba(255,255,255,.055);

    border-radius:
        14px 14px 14px 4px;

    color: #dce1e8;

    background:
        #121720;
}

#guest-chat .lc-msg--visitor {
    margin-left: auto;

    border-color:
        rgba(122,108,255,.22);

    border-radius:
        14px 14px 4px 14px;

    color: #fff;

    background:
        linear-gradient(
            135deg,
            #7164f4,
            #557cff 55%,
            #449df8
        );
}

#guest-chat .lc-avatar {
    background:
        linear-gradient(
            145deg,
            rgba(122,108,255,.28),
            rgba(66,165,255,.12)
        );

    color: #fff;
}

#guest-chat .lc-reopen {
    border-color:
        rgba(122,108,255,.20);

    color: #b6b0ff;

    background:
        rgba(122,108,255,.07);
}

#guest-chat .lc-tool {
    border: 1px solid rgba(255,255,255,.07);

    color: #a9a3ff;

    background:
        #11151e;
}

#guest-chat .lc-tool:hover {
    border-color:
        rgba(122,108,255,.22);

    background:
        rgba(122,108,255,.07);
}

#guest-chat .lc-tool[aria-pressed="true"] {
    border-color:
        rgba(255,95,120,.32);

    color: #fff;

    background:
        rgba(255,80,108,.16);
}

/* Focus ================================================================ */

#guest-chat :is(button,a,textarea):focus-visible {
    outline:
        2px solid rgba(122,108,255,.90);

    outline-offset:
        3px;
}

/* DESKTOP EXPANDED ===================================================== */

@media (min-width: 700px) {
    #guest-chat .guest-chat__panel[data-expanded="true"] {
        width:
            min(610px, calc(100vw - 48px));

        height:
            min(760px, calc(100dvh - 80px));

        max-height:
            calc(100dvh - 80px);
    }
}

/* TABLET / PHONE ======================================================= */

@media (max-width: 699px) {
    #guest-chat {
        right:
            max(8px, env(safe-area-inset-right));

        bottom:
            max(8px, env(safe-area-inset-bottom));

        left:
            auto;
    }

    #guest-chat[data-open="true"] {
        inset:
            0;

        width:
            100%;

        height:
            var(--gc-viewport-height, 100dvh);

        padding:
            max(8px, env(safe-area-inset-top))
            max(8px, env(safe-area-inset-right))
            max(8px, env(safe-area-inset-bottom))
            max(8px, env(safe-area-inset-left));

        display:
            flex;

        align-items:
            stretch;

        justify-content:
            stretch;

        background:
            rgba(3,4,8,.68);

        backdrop-filter:
            blur(12px);

        -webkit-backdrop-filter:
            blur(12px);
    }

    #guest-chat[data-open="true"] .guest-chat__panel {
        width:
            100%;

        height:
            100%;

        max-height:
            none;

        margin:
            0;

        border-radius:
            20px;

        box-shadow:
            0 24px 70px rgba(0,0,0,.52),
            inset 0 1px 0 rgba(255,255,255,.045);
    }

    #guest-chat .guest-chat__header {
        min-height:
            68px;

        padding:
            10px 10px 10px 12px;
    }

    #guest-chat .guest-chat__header .gc-icon {
        width:
            39px;

        height:
            39px;

        border-radius:
            12px;
    }

    #guest-chat .gc-subtitle {
        max-width:
            150px;

        font-size:
            9px;
    }

    #guest-chat .gc-action {
        width:
            38px;

        height:
            38px;
    }

    #guest-chat .gc-welcome {
        padding:
            22px 16px 10px;
    }

    #guest-chat .gc-welcome h3 {
        max-width:
            280px;

        font-size:
            clamp(28px, 8vw, 36px);
    }

    #guest-chat .gc-welcome > p:last-of-type {
        max-width:
            300px;

        font-size:
            12px;
    }

    #guest-chat .guest-chat__suggestions {
        grid-template-columns:
            1fr;

        gap:
            7px;
    }

    #guest-chat .guest-chat__suggestions button {
        min-height:
            58px;
    }

    #guest-chat .guest-chat__messages {
        padding:
            12px 12px 4px;
    }

    #guest-chat .guest-chat__message,
    #guest-chat .lc-msg {
        max-width:
            90%;

        font-size:
            13px;
    }

    #guest-chat .gc-links {
        display:
            none;
    }

    #guest-chat .gc-bottom {
        padding-top:
            8px;

        padding-bottom:
            max(4px, env(safe-area-inset-bottom));
    }

    #guest-chat .guest-chat__form {
        margin:
            0 8px;

        border-radius:
            14px;
    }

    #guest-chat textarea {
        font-size:
            16px;
    }

    #guest-chat .guest-chat__notice {
        padding-bottom:
            8px;
    }

    #guest-chat .lc-log {
        padding:
            12px;
    }

    #guest-chat .lc-form {
        margin:
            0 8px;
    }
}

/* SMALL PHONE ========================================================== */

@media (max-width: 390px) {
    #guest-chat[data-open="true"] {
        padding:
            0;
    }

    #guest-chat[data-open="true"] .guest-chat__panel {
        border:
            0;

        border-radius:
            0;
    }

    #guest-chat .guest-chat__header {
        padding-top:
            max(10px, env(safe-area-inset-top));
    }

    #guest-chat .gc-subtitle {
        display:
            none;
    }

    #guest-chat .guest-chat__suggestions {
        margin-top:
            14px;
    }

    #guest-chat .gc-welcome {
        padding-top:
            18px;
    }
}

/* MOTION =============================================================== */

@keyframes gcPremiumPanelIn {
    from {
        opacity: 0;
        transform:
            translateY(14px)
            scale(.965);
        filter:
            blur(5px);
    }

    to {
        opacity: 1;
        transform:
            translateY(0)
            scale(1);
        filter:
            blur(0);
    }
}

@media (prefers-reduced-motion: reduce) {
    #guest-chat .guest-chat__panel {
        animation:
            none !important;
    }
}


/* ==========================================================================
   MASHAL AI V4 — ULTRA EXPERT
   Conversation-first, premium SaaS, purple/blue, mobile app feel
   ========================================================================== */

#guest-chat {
    --ai-purple: #7a6cff;
    --ai-purple-soft: #9b93ff;
    --ai-blue: #42a5ff;
    --ai-bg: #07090e;
    --ai-surface: #0c0f16;
    --ai-surface-2: #111620;
    --ai-text: #f7f8fb;
    --ai-muted: #7d8796;
    --ai-line: rgba(255,255,255,.075);
}

/* LAUNCHER ------------------------------------------------------------- */

#guest-chat .guest-chat__toggle {
    width: 58px;
    height: 58px;
    min-height: 58px;
    border-radius: 18px;

    border: 1px solid rgba(151,143,255,.38);

    background:
        radial-gradient(circle at 30% 20%, rgba(255,255,255,.20), transparent 28%),
        linear-gradient(145deg, #7567fa 0%, #626fff 46%, #42a5ff 100%);

    box-shadow:
        0 18px 42px rgba(0,0,0,.38),
        0 10px 32px rgba(93,80,230,.28),
        inset 0 1px 0 rgba(255,255,255,.28);
}

#guest-chat .guest-chat__toggle::before {
    inset: -5px;
    border-radius: 22px;
    border-color: rgba(122,108,255,.16);
}

#guest-chat .guest-chat__toggle .gc-icon svg {
    width: 27px;
    height: 27px;
    stroke-width: 1.85;
}

#guest-chat .gc-launch-status {
    right: 3px;
    bottom: 3px;
    width: 11px;
    height: 11px;
    border-width: 3px;
}

/* PANEL SHELL ---------------------------------------------------------- */

#guest-chat .guest-chat__panel {
    width: min(392px, calc(100vw - 28px));
    height: min(640px, calc(100dvh - 96px));
    max-height: calc(100dvh - 96px);

    margin-bottom: 12px;

    border: 1px solid rgba(145,136,255,.18);
    border-radius: 24px;

    background:
        radial-gradient(circle at 92% 3%, rgba(66,165,255,.09), transparent 16rem),
        radial-gradient(circle at 5% 0%, rgba(122,108,255,.12), transparent 18rem),
        linear-gradient(180deg, rgba(12,15,22,.995), rgba(6,8,13,.995));

    box-shadow:
        0 38px 110px rgba(0,0,0,.62),
        0 10px 46px rgba(71,62,190,.10),
        inset 0 1px 0 rgba(255,255,255,.045);
}

#guest-chat .guest-chat__panel::after {
    content: "";
    position: absolute;
    inset: 0;
    pointer-events: none;
    border-radius: inherit;
    box-shadow:
        inset 0 0 0 1px rgba(255,255,255,.012);
}

/* HEADER --------------------------------------------------------------- */

#guest-chat .guest-chat__header {
    min-height: 68px;
    padding: 11px 11px 11px 13px;
    border-bottom: 1px solid rgba(255,255,255,.06);

    background:
        linear-gradient(
            180deg,
            rgba(255,255,255,.018),
            rgba(255,255,255,.004)
        );
}

#guest-chat .guest-chat__header .gc-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;

    border: 1px solid rgba(122,108,255,.24);

    color: #c9c5ff;

    background:
        linear-gradient(
            145deg,
            rgba(122,108,255,.16),
            rgba(66,165,255,.045)
        );

    box-shadow: none;
}

#guest-chat .gc-title-wrap {
    min-width: 0;
    flex: 1;
}

#guest-chat .gc-title-row {
    display: flex;
    align-items: center;
    gap: 8px;
}

#guest-chat .guest-chat__header h2 {
    font-size: 14px;
    font-weight: 760;
    letter-spacing: -.025em;
}

#guest-chat .gc-live-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;

    min-height: 20px;
    padding: 0 7px;

    border: 1px solid rgba(116,223,167,.12);
    border-radius: 999px;

    color: #86cfa5;
    background: rgba(116,223,167,.035);

    font-size: 8px;
    font-weight: 750;
}

#guest-chat .gc-live-pill i {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: #74dfa7;
    box-shadow: 0 0 8px rgba(116,223,167,.48);
}

#guest-chat .gc-subtitle {
    max-width: none;
    margin-top: 1px;
    color: #697382;
    font-size: 9px;
}

#guest-chat .gc-action {
    width: 34px;
    height: 34px;
    border-radius: 9px;
}

/* WELCOME -------------------------------------------------------------- */

#guest-chat .gc-welcome {
    padding: 28px 20px 12px;
}

#guest-chat .gc-eyebrow {
    display: inline-flex;
    align-items: center;
    margin-bottom: 10px;

    color: #8d84ff;

    font-size: 8px;
    font-weight: 850;
    letter-spacing: .18em;
}

#guest-chat .gc-welcome h3 {
    max-width: 300px;
    margin: 0;

    font-size: clamp(31px, 4vw, 38px);
    font-weight: 680;
    line-height: .98;
    letter-spacing: -.065em;
}

#guest-chat .gc-welcome > p:last-of-type {
    max-width: 295px;
    margin-top: 13px;

    color: #737d8b;

    font-size: 11px;
    line-height: 1.7;
}

/* QUICK PROMPTS — no more chunky cards -------------------------------- */

#guest-chat .guest-chat__suggestions {
    grid-template-columns: 1fr;
    gap: 6px;
    margin-top: 22px;
}

#guest-chat .guest-chat__suggestions button {
    position: relative;

    min-height: 48px;
    padding: 10px 36px 10px 12px;

    border: 1px solid rgba(255,255,255,.06);
    border-radius: 12px;

    background: rgba(255,255,255,.014);

    color: #c0c6d0;

    font-size: 10px;
    line-height: 1.35;

    box-shadow: none;
}

#guest-chat .guest-chat__suggestions button::after {
    content: "↗";
    position: absolute;
    right: 13px;
    top: 50%;
    color: #596373;
    transform: translateY(-50%);
    transition: transform .18s ease, color .18s ease;
}

#guest-chat .guest-chat__suggestions button:hover {
    transform: none;

    border-color: rgba(122,108,255,.20);

    background:
        linear-gradient(
            90deg,
            rgba(122,108,255,.075),
            rgba(66,165,255,.018)
        );

    box-shadow: none;
}

#guest-chat .guest-chat__suggestions button:hover::after {
    color: #9d96ff;
    transform: translate(2px,-50%);
}

#guest-chat .gc-topic {
    display: inline;
    margin: 0 7px 0 0;

    color: #8f87ff;

    font-size: 8px;
    font-weight: 820;
}

/* CHAT BODY ------------------------------------------------------------ */

#guest-chat .gc-body {
    scroll-behavior: smooth;
}

#guest-chat .guest-chat__messages {
    padding: 14px 15px 6px;
}

#guest-chat .gc-turn {
    margin-bottom: 17px;
}

#guest-chat .gc-speaker {
    margin-left: 3px;
    margin-bottom: 5px;

    color: #626c7a;

    font-size: 9px;
    font-weight: 700;
}

#guest-chat .guest-chat__message {
    max-width: 86%;
    padding: 11px 13px;

    border-radius: 5px 16px 16px 16px;

    border: 1px solid rgba(255,255,255,.055);

    background:
        rgba(255,255,255,.026);

    color: #d8dde5;

    box-shadow: none;

    font-size: 12px;
    line-height: 1.7;
}

#guest-chat .guest-chat__message--user {
    border: 0;
    border-radius: 16px 5px 16px 16px;

    background:
        linear-gradient(
            135deg,
            #7467f7 0%,
            #5e78ff 55%,
            #449cf3 100%
        );

    color: #fff;

    box-shadow:
        0 8px 22px rgba(74,66,190,.14);
}

/* COMPOSER ------------------------------------------------------------- */

#guest-chat .gc-bottom {
    padding: 9px 0 0;

    background:
        linear-gradient(
            180deg,
            rgba(8,10,15,.88),
            rgba(6,8,12,.99)
        );
}

#guest-chat .gc-links {
    padding: 0 14px 8px;
}

#guest-chat .guest-chat__form {
    position: relative;

    margin: 0 10px;
    padding: 5px;

    border: 1px solid rgba(255,255,255,.095);
    border-radius: 16px;

    background:
        linear-gradient(
            180deg,
            rgba(255,255,255,.02),
            rgba(255,255,255,.007)
        ),
        #080b10;

    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.025);
}

#guest-chat .guest-chat__form:focus-within {
    border-color: rgba(122,108,255,.36);

    box-shadow:
        0 0 0 3px rgba(122,108,255,.055),
        inset 0 1px 0 rgba(255,255,255,.03);
}

#guest-chat textarea {
    height: 45px;
    min-height: 45px;
    max-height: 120px;

    padding: 10px 9px;

    color: #edf0f5;

    font-size: 13px;
}

#guest-chat .guest-chat__send {
    width: 43px;
    height: 43px;

    border-radius: 12px;

    background:
        linear-gradient(
            135deg,
            #796bff,
            #5f7dff 54%,
            #42a5ff
        );
}

/* LIVE CHAT ------------------------------------------------------------ */

#guest-chat .lc-modes {
    padding: 6px 10px;

    background: rgba(5,7,11,.46);
}

#guest-chat .lc-modes button {
    min-height: 34px;
    font-size: 9px;
}

#guest-chat .lc-panel {
    background: transparent;
}

#guest-chat .lc-log {
    padding: 13px;
}

#guest-chat .lc-msg {
    max-width: 86%;
    border-radius: 15px 15px 15px 5px;
    background: rgba(255,255,255,.028);
}

#guest-chat .lc-msg--visitor {
    border: 0;
    border-radius: 15px 15px 5px 15px;

    background:
        linear-gradient(
            135deg,
            #7467f7,
            #5f78ff 55%,
            #449cf3
        );
}

/* INCOMING TOAST ------------------------------------------------------- */

#guest-chat .gc-incoming-toast {
    width: min(306px, calc(100vw - 28px));
    bottom: 72px;

    border-radius: 14px;

    border-color: rgba(122,108,255,.18);

    background:
        linear-gradient(
            145deg,
            rgba(122,108,255,.07),
            rgba(66,165,255,.018)
        ),
        rgba(9,12,18,.98);
}

#guest-chat .gc-incoming-toast__icon {
    border-color: rgba(122,108,255,.18);
    color: #bcb7ff;
    background: rgba(122,108,255,.08);
}

/* DESKTOP EXPANDED ----------------------------------------------------- */

@media (min-width: 700px) {
    #guest-chat .guest-chat__panel[data-expanded="true"] {
        width: min(580px, calc(100vw - 48px));
        height: min(760px, calc(100dvh - 72px));
    }
}

/* MOBILE — APP MODE ---------------------------------------------------- */

@media (max-width: 699px) {
    #guest-chat {
        right: max(10px, env(safe-area-inset-right));
        bottom: max(10px, env(safe-area-inset-bottom));
    }

    #guest-chat[data-open="true"] {
        inset: 0;
        padding: 0;

        background: #05070b;

        backdrop-filter: none;
        -webkit-backdrop-filter: none;
    }

    #guest-chat[data-open="true"] .guest-chat__panel {
        width: 100%;
        height: var(--gc-viewport-height,100dvh);
        max-height: var(--gc-viewport-height,100dvh);

        border: 0;
        border-radius: 0;

        background:
            radial-gradient(circle at 90% 0%, rgba(66,165,255,.08), transparent 15rem),
            radial-gradient(circle at 10% 0%, rgba(122,108,255,.10), transparent 18rem),
            #07090e;

        box-shadow: none;
    }

    #guest-chat .guest-chat__header {
        min-height: 68px;

        padding:
            max(10px, env(safe-area-inset-top))
            10px
            10px
            12px;
    }

    #guest-chat .guest-chat__header .gc-icon {
        width: 38px;
        height: 38px;
    }

    #guest-chat .gc-live-pill {
        display: none;
    }

    #guest-chat .gc-subtitle {
        font-size: 8px;
    }

    #guest-chat .gc-welcome {
        padding: 27px 17px 12px;
    }

    #guest-chat .gc-welcome h3 {
        max-width: 290px;

        font-size: clamp(35px, 10vw, 45px);
        line-height: .95;
    }

    #guest-chat .gc-welcome > p:last-of-type {
        max-width: 300px;
        font-size: 12px;
    }

    #guest-chat .guest-chat__suggestions {
        gap: 6px;
    }

    #guest-chat .guest-chat__suggestions button {
        min-height: 52px;
        padding-left: 13px;
    }

    #guest-chat .guest-chat__messages {
        padding-inline: 12px;
    }

    #guest-chat .guest-chat__message,
    #guest-chat .lc-msg {
        max-width: 90%;
        font-size: 13px;
    }

    #guest-chat .gc-bottom {
        padding-bottom:
            max(6px, env(safe-area-inset-bottom));
    }

    #guest-chat .guest-chat__form {
        margin-inline: 8px;
    }

    #guest-chat .guest-chat__notice {
        padding-bottom: 7px;
    }
}

/* VERY SMALL ----------------------------------------------------------- */

@media (max-width: 380px) {
    #guest-chat .gc-welcome {
        padding-top: 21px;
    }

    #guest-chat .gc-welcome h3 {
        font-size: 35px;
    }

    #guest-chat .gc-subtitle {
        display: none;
    }

    #guest-chat .guest-chat__header {
        min-height: 62px;
    }

    #guest-chat .gc-action {
        width: 32px;
        height: 32px;
    }
}

/* REDUCED MOTION ------------------------------------------------------- */

@media (prefers-reduced-motion: reduce) {
    #guest-chat .guest-chat__panel,
    #guest-chat .guest-chat__toggle,
    #guest-chat .guest-chat__suggestions button {
        animation: none !important;
        transition-duration: .01ms !important;
    }
}


/* ==========================================================================
   FINAL MOBILE EXPERT LAYER
   Deze laag overschrijft alleen mobiel gedrag/design.
   Desktop en bestaande functionaliteit blijven intact.
   ========================================================================== */
@media (max-width: 699px) {
    #guest-chat {
        left: auto;
        right: 14px;
        bottom: max(14px, env(safe-area-inset-bottom));
        z-index: 2147482000;
        --gc-mobile-pad: 14px;
        --gc-mobile-safe-bottom: max(14px, env(safe-area-inset-bottom));
    }

    #guest-chat::before {
        content: "";
        position: fixed;
        inset: 0;
        background:
            linear-gradient(
                180deg,
                rgba(4, 6, 12, .14),
                rgba(4, 6, 12, .58)
            );
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition:
            opacity .22s ease,
            visibility .22s ease;
    }

    #guest-chat[data-open="true"]::before {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    #guest-chat .guest-chat__toggle {
        width: 62px;
        height: 62px;
        min-height: 62px;
        border-radius: 20px;
        box-shadow:
            0 20px 42px rgba(0, 0, 0, .42),
            0 12px 30px rgba(83, 72, 220, .26),
            inset 0 1px 0 rgba(255, 255, 255, .24);
    }

    #guest-chat .guest-chat__toggle .gc-icon svg {
        width: 29px;
        height: 29px;
    }

    #guest-chat[data-open="true"] {
        left: 0;
        right: 0;
        bottom: 0;
    }

    #guest-chat[data-open="true"] .guest-chat__toggle {
        display: none;
    }

    #guest-chat[data-open="true"] .guest-chat__panel {
        width: 100vw;
        max-width: 100vw;
        height: 100dvh;
        max-height: 100dvh;
        margin: 0;
        border-top-left-radius: 26px;
        border-top-right-radius: 26px;
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
        border-left: 0;
        border-right: 0;
        border-bottom: 0;
        box-shadow:
            0 -12px 36px rgba(0, 0, 0, .34),
            0 -1px 0 rgba(255, 255, 255, .04);
    }

    #guest-chat .guest-chat__panel,
    #guest-chat .lc-panel {
        min-height: 0;
    }

    #guest-chat .guest-chat__header {
        position: sticky;
        top: 0;
        z-index: 12;
        padding: 14px 14px 12px;
        gap: 10px;
        border-bottom: 1px solid rgba(255, 255, 255, .07);
        background:
            linear-gradient(
                180deg,
                rgba(12, 15, 24, .98),
                rgba(11, 14, 21, .94)
            );
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
    }

    #guest-chat .guest-chat__header .gc-icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
    }

    #guest-chat .gc-title-wrap {
        min-width: 0;
        flex: 1 1 auto;
    }

    #guest-chat .gc-title-row {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

    #guest-chat .guest-chat__header h2 {
        max-width: 100%;
        margin: 0;
        font-size: 18px;
        line-height: 1.1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    #guest-chat .gc-live-pill {
        flex: 0 0 auto;
        padding: 4px 8px;
        border: 1px solid rgba(116, 223, 167, .18);
        border-radius: 999px;
        font-size: 10px;
        line-height: 1;
        white-space: nowrap;
        background: rgba(116, 223, 167, .08);
    }

    #guest-chat .gc-subtitle {
        margin-top: 4px;
        max-width: 100%;
        font-size: 11px;
        line-height: 1.45;
        color: #8f98a8;
    }

    #guest-chat .gc-actions {
        gap: 6px;
        margin-left: 0;
    }

    #guest-chat .gc-action {
        width: 38px;
        height: 38px;
        border-radius: 11px;
        background: rgba(255, 255, 255, .04);
    }

    #guest-chat .gc-reset-confirm {
        position: sticky;
        top: 72px;
        z-index: 11;
        margin: 0 12px;
        padding: 12px;
        border: 1px solid rgba(255, 255, 255, .07);
        border-radius: 14px;
        background: rgba(23, 26, 36, .95);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }

    #guest-chat .gc-body {
        padding-bottom: 0;
    }

    #guest-chat .gc-welcome {
        padding: 18px 16px 10px;
    }

    #guest-chat .gc-eyebrow {
        margin-bottom: 10px;
        font-size: 10px;
        letter-spacing: 1.8px;
    }

    #guest-chat .gc-welcome h3 {
        margin-bottom: 10px;
        font-size: clamp(28px, 8vw, 36px);
        line-height: 1.04;
        letter-spacing: -.04em;
    }

    #guest-chat .gc-welcome > p:last-of-type {
        max-width: none;
        font-size: 13px;
        line-height: 1.65;
    }

    #guest-chat .guest-chat__suggestions {
        grid-template-columns: 1fr;
        gap: 9px;
        margin-top: 18px;
    }

    #guest-chat .guest-chat__suggestions button {
        min-height: 70px;
        padding: 13px 14px;
        border-radius: 15px;
        font-size: 12px;
    }

    #guest-chat .guest-chat__messages,
    #guest-chat .lc-log {
        padding-left: 14px;
        padding-right: 14px;
    }

    #guest-chat .guest-chat__messages {
        padding-top: 10px;
        padding-bottom: 8px;
    }

    #guest-chat .lc-status {
        padding: 11px 14px;
        font-size: 12px;
        line-height: 1.5;
    }

    #guest-chat .lc-log {
        padding-top: 14px;
        padding-bottom: 10px;
    }

    #guest-chat .lc-msg,
    #guest-chat .guest-chat__message {
        max-width: 100%;
        border-radius: 16px;
        font-size: 14px;
        line-height: 1.7;
    }

    #guest-chat .guest-chat__message--user {
        border-radius: 16px 6px 16px 16px;
    }

    #guest-chat .lc-msg--visitor {
        border-radius: 16px 16px 6px 16px;
    }

    #guest-chat .gc-bottom,
    #guest-chat .lc-panel .guest-chat__form,
    #guest-chat .lc-panel .guest-chat__notice {
        flex-shrink: 0;
    }

    #guest-chat .gc-bottom {
        position: sticky;
        bottom: 0;
        z-index: 10;
        padding:
            10px 0
            calc(var(--gc-mobile-safe-bottom) + 2px);
        border-top: 1px solid rgba(255, 255, 255, .07);
        background:
            linear-gradient(
                180deg,
                rgba(11, 14, 21, .88),
                rgba(8, 10, 16, .98)
            );
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
    }

    #guest-chat .gc-links {
        flex-wrap: wrap;
        justify-content: flex-start;
        gap: 8px;
        padding: 0 14px 10px;
    }

    #guest-chat .gc-links a {
        display: inline-flex;
        align-items: center;
        min-height: 34px;
        padding: 0 11px;
        border: 1px solid rgba(255, 255, 255, .07);
        border-radius: 999px;
        background: rgba(255, 255, 255, .03);
        font-size: 11px;
    }

    #guest-chat .guest-chat__form {
        margin: 0 12px;
        padding: 8px;
        gap: 8px;
        border-radius: 18px;
        background: rgba(5, 7, 12, .92);
        border: 1px solid rgba(255, 255, 255, .08);
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, .03),
            0 10px 24px rgba(0, 0, 0, .18);
    }

    #guest-chat textarea,
    #guest-chat .lc-input {
        min-height: 46px;
        max-height: 140px;
        padding: 11px 10px;
        font-size: 16px;
        line-height: 1.45;
    }

    #guest-chat .guest-chat__send {
        width: 48px;
        height: 48px;
        flex: 0 0 48px;
        border-radius: 14px;
    }

    #guest-chat .guest-chat__notice {
        padding: 10px 16px 0;
        font-size: 10px;
        line-height: 1.55;
    }

    #guest-chat .lc-form {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        margin-top: 10px;
    }

    #guest-chat .lc-tool {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        border-radius: 12px;
    }

    #guest-chat .lc-form .lc-input {
        flex: 1 1 calc(100% - 106px);
        min-width: 0;
    }

    #guest-chat .lc-form .guest-chat__send {
        margin-left: auto;
    }

    #guest-chat .gc-incoming-toast {
        right: 14px;
        left: 14px;
        width: auto;
        bottom: calc(76px + env(safe-area-inset-bottom));
        padding: 13px 14px;
        border-radius: 16px;
    }

    #guest-chat[data-open="true"] .gc-incoming-toast {
        right: 14px;
        left: 14px;
        bottom: calc(var(--gc-mobile-safe-bottom) + 10px);
    }
}

@media (max-width: 420px) {
    #guest-chat .guest-chat__header {
        padding: 12px 12px 10px;
    }

    #guest-chat .gc-title-row {
        align-items: flex-start;
        flex-direction: column;
        gap: 5px;
    }

    #guest-chat .gc-subtitle {
        font-size: 10px;
    }

    #guest-chat .gc-action {
        width: 36px;
        height: 36px;
    }

    #guest-chat .gc-welcome {
        padding-left: 14px;
        padding-right: 14px;
    }

    #guest-chat .guest-chat__messages,
    #guest-chat .lc-log {
        padding-left: 12px;
        padding-right: 12px;
    }

    #guest-chat .guest-chat__form {
        margin: 0 10px;
        padding: 7px;
    }
}


/* ==========================================================================
   MASHAL CHAT — ULTRA PREMIUM V2
   Editorial AI cockpit. Minder "standaard widget", meer eigen product-identiteit.
   ========================================================================== */

#guest-chat {
    --mashal-violet: #7a6cff;
    --mashal-blue: #42a5ff;
    --mashal-ink: #f7f8fc;
    --mashal-muted: #7b8492;
    --mashal-line: rgba(255,255,255,.065);
}

/* SHELL ------------------------------------------------------------------ */

#guest-chat .guest-chat__panel {
    width: min(430px, calc(100vw - 32px));
    height: min(700px, calc(100dvh - 96px));
    max-height: calc(100dvh - 96px);
    overflow: hidden;

    border: 1px solid rgba(139,128,255,.18);
    border-radius: 28px;

    background:
        radial-gradient(
            circle at 100% 0%,
            rgba(66,165,255,.08),
            transparent 18rem
        ),
        radial-gradient(
            circle at 0% 0%,
            rgba(122,108,255,.10),
            transparent 20rem
        ),
        linear-gradient(
            180deg,
            rgba(11,14,21,.985),
            rgba(5,7,11,.995)
        );

    box-shadow:
        0 42px 120px rgba(0,0,0,.64),
        0 14px 50px rgba(49,39,150,.12),
        inset 0 1px 0 rgba(255,255,255,.05);
}

#guest-chat .guest-chat__panel::before {
    content: "";
    position: absolute;
    inset: 0 auto 0 0;
    width: 1px;
    pointer-events: none;
    background:
        linear-gradient(
            180deg,
            transparent,
            rgba(122,108,255,.54) 16%,
            rgba(66,165,255,.24) 58%,
            transparent 92%
        );
}

/* HEADER ----------------------------------------------------------------- */

#guest-chat .guest-chat__header {
    min-height: 72px;
    padding: 14px 14px 12px 16px;
    gap: 12px;

    border-bottom: 1px solid rgba(255,255,255,.055);

    background:
        linear-gradient(
            180deg,
            rgba(255,255,255,.018),
            rgba(255,255,255,0)
        );
}

#guest-chat .guest-chat__header .gc-icon {
    width: 38px;
    height: 38px;
    flex: 0 0 38px;

    border: 1px solid rgba(145,136,255,.23);
    border-radius: 13px;

    background:
        radial-gradient(
            circle at 30% 25%,
            rgba(255,255,255,.12),
            transparent 42%
        ),
        rgba(122,108,255,.08);
}

#guest-chat .gc-title-wrap {
    min-width: 0;
    flex: 1 1 auto;
}

#guest-chat .gc-title-row {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
}

#guest-chat .guest-chat__header h2 {
    min-width: 0;
    margin: 0;

    color: #f8f9fc;
    font-size: 15px;
    font-weight: 760;
    line-height: 1.15;
    letter-spacing: -.025em;

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

#guest-chat .gc-live-pill {
    flex: 0 0 auto;
    min-height: 21px;
    padding: 0 7px;

    border: 1px solid rgba(116,223,167,.14);
    border-radius: 999px;

    color: #91d8ad;
    background: rgba(116,223,167,.045);

    font-size: 8px;
    font-weight: 800;
    letter-spacing: .02em;
}

#guest-chat .gc-subtitle {
    max-width: 230px;
    margin-top: 3px;

    color: #66707f;
    font-size: 9px;
    line-height: 1.35;

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

#guest-chat .gc-actions {
    flex: 0 0 auto;
    gap: 4px;
}

#guest-chat .gc-action {
    width: 34px;
    height: 34px;

    border: 1px solid transparent;
    border-radius: 10px;

    color: #6f7887;
    background: transparent;
}

#guest-chat .gc-action:hover {
    color: #d9d6ff;
    border-color: rgba(122,108,255,.14);
    background: rgba(122,108,255,.055);
}

/* BODY ------------------------------------------------------------------- */

#guest-chat .gc-body {
    position: relative;
    background: transparent;
}

#guest-chat .gc-body::before {
    content: "";
    position: absolute;
    inset: 0;
    pointer-events: none;

    background:
        linear-gradient(
            90deg,
            transparent 0 92%,
            rgba(122,108,255,.035)
        );
}

#guest-chat .gc-welcome {
    padding: 46px 22px 18px;
    background: transparent;
}

#guest-chat .gc-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;

    margin: 0 0 14px;

    color: #948cff;

    font-size: 9px;
    font-weight: 850;
    letter-spacing: .17em;
}

#guest-chat .gc-eyebrow::before {
    content: "";
    width: 17px;
    height: 1px;
    background:
        linear-gradient(
            90deg,
            var(--mashal-violet),
            rgba(122,108,255,.15)
        );
}

#guest-chat .gc-welcome h3 {
    max-width: 330px;
    margin: 0;

    color: #f5f7fb;

    font-size: clamp(35px, 4vw, 46px);
    font-weight: 660;
    line-height: .98;
    letter-spacing: -.065em;
}

#guest-chat .gc-welcome > p:last-of-type {
    max-width: 310px;
    margin-top: 16px;

    color: #77808f;

    font-size: 12px;
    line-height: 1.7;
}

/* QUICK ACTIONS — geen standaard kaartjes meer --------------------------- */

#guest-chat .guest-chat__suggestions {
    display: flex;
    flex-direction: column;
    gap: 0;

    margin-top: 28px;

    border-top: 1px solid var(--mashal-line);
}

#guest-chat .guest-chat__suggestions button {
    position: relative;

    min-height: 56px;
    padding: 12px 40px 12px 2px;

    border: 0;
    border-bottom: 1px solid var(--mashal-line);
    border-radius: 0;

    color: #c9ced7;
    background: transparent;

    text-align: left;
    font-size: 12px;
    line-height: 1.3;

    transition:
        color .18s ease,
        padding-left .2s ease,
        background .18s ease;
}

#guest-chat .guest-chat__suggestions button::after {
    content: "↗";
    position: absolute;
    right: 4px;
    top: 50%;

    color: #525c6c;
    font-size: 14px;

    transform: translateY(-50%);
    transition:
        transform .2s ease,
        color .18s ease;
}

#guest-chat .guest-chat__suggestions button:hover {
    padding-left: 8px;
    color: #f0f2f6;
    background:
        linear-gradient(
            90deg,
            rgba(122,108,255,.055),
            transparent 72%
        );
}

#guest-chat .guest-chat__suggestions button:hover::after {
    color: #9c95ff;
    transform: translate(3px,-50%);
}

#guest-chat .gc-topic {
    margin-bottom: 4px;
    color: #727c8b;
    font-size: 8px;
    letter-spacing: .14em;
}

/* MESSAGES --------------------------------------------------------------- */

#guest-chat .guest-chat__messages,
#guest-chat .lc-log {
    padding-left: 18px;
    padding-right: 18px;
}

#guest-chat .guest-chat__message,
#guest-chat .lc-msg {
    max-width: 86%;
    padding: 11px 13px;

    border: 1px solid rgba(255,255,255,.05);
    border-radius: 5px 16px 16px 16px;

    color: #dfe3e9;
    background: rgba(255,255,255,.035);

    box-shadow: none;
}

#guest-chat .guest-chat__message--user,
#guest-chat .lc-msg--visitor {
    border-color: rgba(122,108,255,.22);
    border-radius: 16px 5px 16px 16px;

    color: #fff;

    background:
        linear-gradient(
            135deg,
            #6f62ef,
            #537fff 58%,
            #42a5ff
        );

    box-shadow:
        0 8px 22px rgba(75,65,199,.14);
}

/* BOTTOM — zwevende composer --------------------------------------------- */

#guest-chat .gc-bottom {
    position: relative;
    padding: 10px 12px 13px;

    border-top: 0;

    background:
        linear-gradient(
            180deg,
            rgba(7,9,14,.15),
            rgba(7,9,14,.98) 28%
        );
}

#guest-chat .gc-bottom::before {
    content: "";
    position: absolute;
    left: 16px;
    right: 16px;
    top: 0;
    height: 1px;

    background:
        linear-gradient(
            90deg,
            transparent,
            rgba(255,255,255,.08),
            transparent
        );
}

#guest-chat .gc-links {
    order: 2;
    padding: 9px 5px 0;
}

#guest-chat .gc-links a {
    color: #535d6d;
    font-size: 9px;
}

#guest-chat .guest-chat__form {
    margin: 0;
    padding: 7px;

    border: 1px solid rgba(140,129,255,.18);
    border-radius: 18px;

    background:
        linear-gradient(
            180deg,
            rgba(18,22,31,.96),
            rgba(10,13,19,.98)
        );

    box-shadow:
        0 16px 40px rgba(0,0,0,.28),
        inset 0 1px 0 rgba(255,255,255,.035);
}

#guest-chat .guest-chat__form:focus-within {
    border-color: rgba(122,108,255,.46);

    box-shadow:
        0 18px 46px rgba(0,0,0,.30),
        0 0 0 3px rgba(122,108,255,.05),
        inset 0 1px 0 rgba(255,255,255,.04);
}

#guest-chat textarea {
    min-height: 46px;
    height: 46px;

    padding: 11px 10px;

    color: #eef1f6;
    font-size: 14px;
}

#guest-chat textarea::placeholder {
    color: #596272;
}

#guest-chat .guest-chat__send {
    width: 46px;
    height: 46px;

    border-radius: 14px;

    background:
        linear-gradient(
            135deg,
            #776aff,
            #527fff 58%,
            #42a5ff
        );

    box-shadow:
        0 10px 26px rgba(74,64,200,.28),
        inset 0 1px 0 rgba(255,255,255,.18);
}

#guest-chat .guest-chat__notice {
    padding: 8px 6px 0;

    color: #424b58;

    font-size: 8px;
    line-height: 1.45;
}

/* LIVE CHAT -------------------------------------------------------------- */

#guest-chat .lc-panel {
    background: transparent;
}

#guest-chat .lc-status {
    margin: 0 14px;
    padding: 10px 0;

    border-bottom: 1px solid var(--mashal-line);

    color: #6f7887;
    background: transparent;

    font-size: 10px;
}

#guest-chat .lc-reopen {
    margin: 10px 14px;
    border-radius: 12px;
}

#guest-chat .lc-form {
    margin: 10px 12px 0;
}

#guest-chat .lc-tool {
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 12px;

    color: #8e87ff;
    background: rgba(255,255,255,.025);
}

/* DESKTOP ================================================================ */

@media (min-width: 700px) {
    #guest-chat .guest-chat__panel[data-expanded="true"] {
        width: min(720px, calc(100vw - 56px));
        height: min(820px, calc(100dvh - 56px));
        max-height: calc(100dvh - 56px);
    }

    #guest-chat .guest-chat__panel[data-expanded="true"] .gc-welcome {
        padding-left: 36px;
        padding-right: 36px;
    }

    #guest-chat .guest-chat__panel[data-expanded="true"] .gc-welcome h3 {
        max-width: 470px;
        font-size: clamp(46px, 5vw, 68px);
    }
}

/* MOBILE — native app gevoel --------------------------------------------- */

@media (max-width: 699px) {
    #guest-chat {
        right: 12px;
        bottom: max(12px, env(safe-area-inset-bottom));
    }

    #guest-chat[data-open="true"] {
        position: fixed;
        inset: 0;
        width: 100%;
        height: 100dvh;
        padding: 0;
    }

    #guest-chat[data-open="true"]::before {
        display: none;
    }

    #guest-chat[data-open="true"] .guest-chat__panel {
        width: 100%;
        max-width: 100%;
        height: 100dvh;
        max-height: 100dvh;
        margin: 0;

        border: 0;
        border-radius: 0;

        background:
            radial-gradient(
                circle at 100% 0%,
                rgba(66,165,255,.07),
                transparent 17rem
            ),
            radial-gradient(
                circle at 0% 0%,
                rgba(122,108,255,.09),
                transparent 18rem
            ),
            #080b11;

        box-shadow: none;
    }

    #guest-chat[data-open="true"] .guest-chat__panel::before {
        display: none;
    }

    #guest-chat .guest-chat__header {
        min-height: 70px;
        padding:
            max(12px, env(safe-area-inset-top))
            12px
            10px;

        border-bottom: 1px solid rgba(255,255,255,.055);

        background:
            rgba(8,11,17,.90);

        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
    }

    #guest-chat .guest-chat__header .gc-icon {
        width: 36px;
        height: 36px;
        flex-basis: 36px;
        border-radius: 12px;
    }

    #guest-chat .guest-chat__header h2 {
        font-size: 15px;
    }

    #guest-chat .gc-subtitle {
        display: none;
    }

    #guest-chat .gc-actions {
        gap: 2px;
    }

    #guest-chat .gc-action {
        width: 36px;
        height: 36px;
        border-radius: 10px;
    }

    #guest-chat .gc-expand {
        display: none;
    }

    #guest-chat .gc-welcome {
        padding:
            32px
            18px
            18px;
    }

    #guest-chat .gc-welcome h3 {
        max-width: 300px;

        font-size: clamp(38px, 11vw, 54px);
        line-height: .95;
        letter-spacing: -.065em;
    }

    #guest-chat .gc-welcome > p:last-of-type {
        max-width: 320px;
        margin-top: 15px;

        font-size: 12px;
    }

    #guest-chat .guest-chat__suggestions {
        margin-top: 26px;
    }

    #guest-chat .guest-chat__suggestions button {
        min-height: 58px;
        padding-right: 34px;
    }

    #guest-chat .guest-chat__messages,
    #guest-chat .lc-log {
        padding-left: 14px;
        padding-right: 14px;
    }

    #guest-chat .guest-chat__message,
    #guest-chat .lc-msg {
        max-width: 92%;
        font-size: 13px;
    }

    #guest-chat .gc-bottom {
        padding:
            10px
            10px
            calc(10px + env(safe-area-inset-bottom));

        background:
            linear-gradient(
                180deg,
                rgba(8,11,17,.18),
                rgba(8,11,17,.98) 24%
            );
    }

    #guest-chat .gc-links {
        display: none;
    }

    #guest-chat .guest-chat__form {
        min-height: 60px;
        padding: 7px;
        border-radius: 20px;
    }

    #guest-chat textarea,
    #guest-chat .lc-input {
        min-height: 46px;
        max-height: 132px;

        padding: 11px 10px;

        font-size: 16px;
        line-height: 1.42;
    }

    #guest-chat .guest-chat__send {
        width: 46px;
        height: 46px;
        flex: 0 0 46px;
        border-radius: 14px;
    }

    #guest-chat .guest-chat__notice {
        display: none;
    }

    #guest-chat .lc-status {
        margin: 0 14px;
        padding: 10px 0;
    }

    #guest-chat .lc-form {
        margin:
            8px
            10px
            calc(10px + env(safe-area-inset-bottom));

        padding: 7px;
    }

    #guest-chat .lc-tool {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
    }

    #guest-chat .lc-form .lc-input {
        min-width: 0;
    }

    #guest-chat .gc-incoming-toast {
        left: 12px;
        right: 12px;
        bottom: calc(78px + env(safe-area-inset-bottom));
        width: auto;
        border-radius: 18px;
    }
}

@media (max-width: 380px) {
    #guest-chat .guest-chat__header {
        padding-left: 10px;
        padding-right: 10px;
    }

    #guest-chat .gc-live-pill {
        display: none;
    }

    #guest-chat .gc-welcome {
        padding-left: 15px;
        padding-right: 15px;
    }

    #guest-chat .gc-welcome h3 {
        font-size: clamp(36px, 11vw, 48px);
    }
}

</style>



<aside id="guest-chat" class="guest-chat" aria-label="Mashal chat" hidden data-mode="ai" data-endpoint="{{ route('guest-chat.message') }}">

    <section id="guest-chat-panel" class="guest-chat__panel" aria-labelledby="guest-chat-title" hidden>

        <header class="guest-chat__header">

            <span class="gc-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"><path d="m12 3 2.6 6.4L21 12l-6.4 2.6L12 21l-2.6-6.4L3 12l6.4-2.6L12 3Z"/></svg></span>

            <div class="gc-title-wrap"><div class="gc-title-row"><h2 id="guest-chat-title">Mashal AI</h2><span class="gc-live-pill"><i></i>Online</span></div><p class="gc-subtitle">{{ __('Slimme hulp, direct in je workspace') }}</p></div>

            <div class="gc-actions">

                <button type="button" class="gc-action gc-reset" aria-label="Nieuw gesprek" title="Nieuw gesprek"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></button>

                <button type="button" class="gc-action gc-expand" aria-label="Chat vergroten" aria-pressed="false" title="Chat vergroten"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4h6v6M20 4l-7 7M10 20H4v-6m0 6 7-7"/></svg></button>

                <button type="button" class="gc-action guest-chat__close" aria-label="Chat sluiten" title="Chat sluiten"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="m6 6 12 12M18 6 6 18"/></svg></button>

            </div>

        </header>

        <div class="gc-reset-confirm" hidden><span>{{ __('Dit gesprek wissen?') }}</span><button type="button" data-reset-confirm>{{ __('Wissen') }}</button><button type="button" data-reset-cancel>{{ __('Annuleren') }}</button></div>

        <div class="gc-body">

            <div class="gc-welcome">

                <p class="gc-eyebrow">{{ __('MASHAL INTELLIGENCE') }}</p>

                <h3>{{ __('Wat wil je') }}<br>{{ __('bereiken?') }}</h3>

                <p>{{ __('Vraag iets, werk een idee uit of krijg direct hulp met Mashal Studio.') }}</p>

                <div class="guest-chat__suggestions" aria-label="Voorbeeldvragen">

                    <button type="button" data-question="Hoe kan ik een afbeelding uploaden en bewerken op Mashal Studio?"><span class="gc-topic">{{ __('AFBEELDINGEN') }}</span>{{ __('Maak meer van je foto') }}</button>

                    <button type="button" data-question="Help mij een professionele e-mail schrijven."><span class="gc-topic">{{ __('SCHRIJVEN') }}</span>{{ __('Vind de juiste woorden') }}</button>

                    <button type="button" data-question="Ik heb hulp nodig bij het inloggen op Mashal Studio."><span class="gc-topic">{{ __('ACCOUNT') }}</span>{{ __('Hulp bij het inloggen') }}</button>

                    <button type="button" data-question="Wat kan ik allemaal doen met Mashal Studio?"><span class="gc-topic">{{ __('ONTDEKKEN') }}</span>{{ __('Leer de website kennen') }}</button>

                </div>

            </div>

            <div class="guest-chat__messages" role="log" aria-live="polite" aria-relevant="additions" aria-label="Chatberichten"></div>

        </div>

        <div class="gc-bottom">

            <nav class="gc-links" aria-label="Handige pagina's"><a href="{{ route('contact') }}">{{ __('Contact opnemen ↗') }}</a><a href="{{ route('privacy') }}">{{ __('Privacy') }}</a></nav>

            <form class="guest-chat__form">

                <textarea rows="1" aria-label="Je bericht aan Mashal AI" placeholder="Vraag het Mashal AI…" maxlength="2000" required></textarea>

                <button type="submit" class="guest-chat__send" aria-label="Bericht versturen"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5m-6 6 6-6 6 6"/></svg></button>

            </form>

            <p class="guest-chat__notice">{{ __('AI kan fouten maken. Controleer belangrijke informatie.') }}</p>

        </div>

        @if (\Illuminate\Support\Facades\Route::has('live-chat.show'))

            <section class="lc-panel" aria-label="Live chat met medewerker" hidden

                data-show="{{ route('live-chat.show') }}" data-store="{{ route('live-chat.store') }}" data-reopen="{{ route('live-chat.reopen') }}">

                <p class="lc-status" role="status">{{ __('Beschikbaarheid controleren…') }}</p>

                <div class="lc-log" role="log" aria-live="polite" aria-relevant="additions" aria-label="Gesprek met medewerker"></div>

                <p class="lc-error" role="status" hidden></p>

                <button type="button" class="lc-reopen" hidden>{{ __('Gesprek opnieuw openen') }}</button>

                <form class="guest-chat__form lc-form">

                    <label class="lc-tool" title="Bestand versturen" aria-label="Bestand versturen">📎<input class="lc-file" type="file" accept="image/jpeg,image/png,image/webp,image/gif,video/*,.mp4,.webm,.mov,.m4v,.avi,.mkv,.mpeg,.mpg,.3gp,.3g2,.ogv,.ts,.mts,.m2ts,.flv,.wmv,application/pdf,text/plain,.doc,.docx,.xls,.xlsx" hidden></label>

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
            <strong>{{ __('Mashal Support') }}</strong>
            <small>{{ __('Stel een vraag of start live chat') }}</small>
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
            <strong>{{ __('Nieuw bericht') }}</strong>
            <span data-chat-toast-text>{{ __('Je hebt een nieuw bericht ontvangen.') }}</span>
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
    let toastTimer = null;
    let notificationAudioContext = null;
    let notificationSoundUnlocked = false;

    const unlockNotificationSound = function () {
        if (notificationSoundUnlocked) {
            return;
        }

        try {
            const AudioContextClass =
                window.AudioContext || window.webkitAudioContext;

            if (!AudioContextClass) {
                return;
            }

            notificationAudioContext =
                notificationAudioContext || new AudioContextClass();

            if (notificationAudioContext.state === 'suspended') {
                const resumePromise = notificationAudioContext.resume();

                if (resumePromise && typeof resumePromise.catch === 'function') {
                    resumePromise.catch(function () {});
                }
            }

            notificationSoundUnlocked = true;
        } catch (error) {}
    };

    const playNotificationSound = function () {
        if (!notificationSoundUnlocked) {
            unlockNotificationSound();
        }

        const context = notificationAudioContext;

        if (!context) {
            return;
        }

        try {
            if (context.state === 'suspended') {
                const resumePromise = context.resume();

                if (
                    resumePromise
                    && typeof resumePromise.catch === 'function'
                ) {
                    resumePromise.catch(function () {});
                }
            }

            const now = context.currentTime;

            /*
             * Zachte moderne chat-notificatie:
             * warme C5 + G5 met een subtiele boventoon.
             * Kort, rustig en niet pieperig.
             */
            const master = context.createGain();

            master.gain.setValueAtTime(0.0001, now);
            master.gain.exponentialRampToValueAtTime(0.045, now + 0.012);
            master.gain.exponentialRampToValueAtTime(0.018, now + 0.12);
            master.gain.exponentialRampToValueAtTime(0.0001, now + 0.52);
            master.connect(context.destination);

            const firstGain = context.createGain();

            firstGain.gain.setValueAtTime(0.0001, now);
            firstGain.gain.exponentialRampToValueAtTime(0.85, now + 0.012);
            firstGain.gain.exponentialRampToValueAtTime(0.0001, now + 0.34);
            firstGain.connect(master);

            const firstTone = context.createOscillator();

            firstTone.type = 'sine';
            firstTone.frequency.setValueAtTime(523.25, now);
            firstTone.frequency.exponentialRampToValueAtTime(
                587.33,
                now + 0.11
            );
            firstTone.connect(firstGain);
            firstTone.start(now);
            firstTone.stop(now + 0.35);

            const secondGain = context.createGain();

            secondGain.gain.setValueAtTime(0.0001, now + 0.085);
            secondGain.gain.exponentialRampToValueAtTime(
                0.62,
                now + 0.105
            );
            secondGain.gain.exponentialRampToValueAtTime(
                0.0001,
                now + 0.46
            );
            secondGain.connect(master);

            const secondTone = context.createOscillator();

            secondTone.type = 'sine';
            secondTone.frequency.setValueAtTime(783.99, now + 0.085);
            secondTone.connect(secondGain);
            secondTone.start(now + 0.085);
            secondTone.stop(now + 0.47);

            const shimmerGain = context.createGain();

            shimmerGain.gain.setValueAtTime(0.0001, now + 0.11);
            shimmerGain.gain.exponentialRampToValueAtTime(
                0.16,
                now + 0.13
            );
            shimmerGain.gain.exponentialRampToValueAtTime(
                0.0001,
                now + 0.31
            );
            shimmerGain.connect(master);

            const shimmerTone = context.createOscillator();

            shimmerTone.type = 'sine';
            shimmerTone.frequency.setValueAtTime(1046.50, now + 0.11);
            shimmerTone.connect(shimmerGain);
            shimmerTone.start(now + 0.11);
            shimmerTone.stop(now + 0.32);
        } catch (error) {}
    };

    document.addEventListener(
        'pointerdown',
        unlockNotificationSound,
        { once: true, passive: true }
    );

    document.addEventListener(
        'keydown',
        unlockNotificationSound,
        { once: true }
    );

    let liveBaselineReady = false;
    let lastLiveAdminCount = 0;
    let lastLiveAdminSignature = '';

    const normalizeText = function (value) {
        return String(value || '')
            .replace(/\s+/g, ' ')
            .trim();
    };

    const isOpen = function () {
        return (
            root.dataset.open === 'true'
            || toggle.getAttribute('aria-expanded') === 'true'
            || panel.hidden === false
        );
    };

    const clearUnread = function () {
        unread = 0;

        if (unreadBadge) {
            unreadBadge.textContent = '0';
            unreadBadge.hidden = true;
            unreadBadge.setAttribute('aria-label', '0 ongelezen berichten');
        }

        toggle.classList.remove('has-unread');
    };

    const addUnread = function () {
        unread += 1;

        if (unreadBadge) {
            unreadBadge.textContent = unread > 9 ? '9+' : String(unread);
            unreadBadge.hidden = false;
            unreadBadge.setAttribute(
                'aria-label',
                unread + (
                    unread === 1
                        ? ' ongelezen bericht'
                        : ' ongelezen berichten'
                )
            );
        }

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
                result.catch(function () {});
            }
        } catch (error) {}
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
                    body: message || 'Je hebt een nieuw bericht ontvangen.',
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
        } catch (error) {}
    };

    const openForIncomingMessage = function () {
        if (!isOpen()) {
            toggle.click();
        }
    };

    const getMessageText = function (node) {
        const text = normalizeText(node?.textContent);

        if (!text) {
            return 'Je hebt een nieuw bericht ontvangen.';
        }

        return text.length > 92
            ? text.slice(0, 89) + '…'
            : text;
    };

    const notifyIncoming = function (node) {
        const message = getMessageText(node);

        addUnread();
        showToast(message);
        playNotificationSound();
        showBrowserNotification(message);
        openForIncomingMessage();
    };

    const getAdminMessages = function () {
        if (!liveLog) {
            return [];
        }

        return Array.from(
            liveLog.querySelectorAll('.lc-msg:not(.lc-msg--visitor)')
        );
    };

    const buildLiveSignature = function (node, index) {
        if (!node) {
            return '';
        }

        const explicitId =
            node.dataset?.messageId
            || node.dataset?.id
            || node.getAttribute('data-message-id')
            || node.getAttribute('data-id');

        if (explicitId) {
            return 'id:' + explicitId;
        }

        return (
            'idx:' + index
            + '|'
            + normalizeText(node.textContent)
        );
    };

    const syncLiveBaseline = function () {
        const messages = getAdminMessages();

        lastLiveAdminCount = messages.length;
        lastLiveAdminSignature =
            messages.length
                ? buildLiveSignature(
                    messages[messages.length - 1],
                    messages.length - 1
                )
                : '';

        liveBaselineReady = true;
    };

    const checkForNewLiveAdminMessage = function () {
        if (!liveBaselineReady) {
            syncLiveBaseline();
            return;
        }

        const messages = getAdminMessages();

        if (!messages.length) {
            lastLiveAdminCount = 0;
            lastLiveAdminSignature = '';
            return;
        }

        const latest = messages[messages.length - 1];
        const latestSignature =
            buildLiveSignature(latest, messages.length - 1);

        const countIncreased =
            messages.length > lastLiveAdminCount;

        const latestChanged =
            latestSignature
            && latestSignature !== lastLiveAdminSignature;

        if (countIncreased || latestChanged) {
            notifyIncoming(latest);
        }

        lastLiveAdminCount = messages.length;
        lastLiveAdminSignature = latestSignature;
    };

    if (liveLog) {
        const liveObserver =
            new MutationObserver(function () {
                window.requestAnimationFrame(
                    checkForNewLiveAdminMessage
                );
            });

        liveObserver.observe(
            liveLog,
            {
                childList: true,
                subtree: true,
                characterData: true
            }
        );
    }

    /*
     * Fallback voor live-chat.js implementations die de hele log opnieuw
     * opbouwen. Hierdoor wordt een adminbericht alsnog binnen ongeveer
     * 1,2 seconde gezien.
     */
    window.setInterval(
        checkForNewLiveAdminMessage,
        1200
    );

    /*
     * Bestaande historie eerst als baseline opslaan, zodat oude berichten
     * de chat niet automatisch openen.
     */
    window.setTimeout(
        syncLiveBaseline,
        2600
    );

    if (aiLog) {
        let aiReady = false;

        const aiObserver =
            new MutationObserver(function (mutations) {
                if (!aiReady) {
                    return;
                }

                for (const mutation of mutations) {
                    for (const addedNode of mutation.addedNodes) {
                        if (!(addedNode instanceof Element)) {
                            continue;
                        }

                        const selector =
                            '.gc-turn:not(.gc-turn--user), ' +
                            '.guest-chat__message:not(.guest-chat__message--user)';

                        const incoming =
                            addedNode.matches(selector)
                                ? addedNode
                                : addedNode.querySelector(selector);

                        if (incoming) {
                            notifyIncoming(incoming);
                            return;
                        }
                    }
                }
            });

        aiObserver.observe(
            aiLog,
            {
                childList: true,
                subtree: true
            }
        );

        window.setTimeout(function () {
            aiReady = true;
        }, 2200);
    }

    toggle.addEventListener(
        'click',
        requestNotificationPermission,
        { once: true }
    );

    toggle.addEventListener(
        'click',
        function () {
            window.setTimeout(function () {
                if (isOpen()) {
                    clearUnread();
                }
            }, 0);
        }
    );

    panel.addEventListener('pointerdown', clearUnread);
    panel.addEventListener('focusin', clearUnread);

    document.addEventListener(
        'visibilitychange',
        function () {
            if (!document.hidden && isOpen()) {
                clearUnread();
            }
        }
    );

    /*
     * ======================================================================
     * BACKGROUND LIVE-CHAT WATCHER
     * ======================================================================
     * Belangrijk: dit draait óók wanneer de chat dicht is.
     * Daardoor kan een adminantwoord de chat automatisch openen terwijl
     * de bezoeker gewoon door de website navigeert.
     */
    const livePanel =
        root.querySelector('.lc-panel[data-show]');

    const liveShowEndpoint =
        livePanel?.dataset?.show || '';

    let serverBaselineReady = false;
    let lastServerAdminSignature = '';
    let backgroundPollBusy = false;

    const getNestedMessages = function (payload) {
        if (Array.isArray(payload)) {
            return payload;
        }

        if (!payload || typeof payload !== 'object') {
            return [];
        }

        const candidates = [
            payload.messages,
            payload.data?.messages,
            payload.conversation?.messages,
            payload.chat?.messages,
            payload.data?.conversation?.messages,
            payload.data?.chat?.messages,
            payload.thread?.messages,
            payload.data?.thread?.messages
        ];

        for (const candidate of candidates) {
            if (Array.isArray(candidate)) {
                return candidate;
            }
        }

        return [];
    };

    const messageLooksLikeAdmin = function (message) {
        if (!message || typeof message !== 'object') {
            return false;
        }

        const senderType =
            String(
                message.sender
                ?? message.sender_type
                ?? message.senderType
                ?? message.role
                ?? message.author_type
                ?? message.authorType
                ?? ''
            ).toLowerCase();

        const senderName =
            String(
                message.sender_name
                ?? message.senderName
                ?? message.author_name
                ?? message.authorName
                ?? ''
            ).toLowerCase();

        const explicitAdmin =
            message.is_admin === true
            || message.isAdmin === true
            || message.from_admin === true
            || message.fromAdmin === true
            || message.admin === true;

        const explicitVisitor =
            message.is_visitor === true
            || message.isVisitor === true
            || message.from_visitor === true
            || message.fromVisitor === true
            || senderType === 'visitor'
            || senderType === 'guest'
            || senderType === 'user'
            || senderType === 'customer';

        if (explicitVisitor) {
            return false;
        }

        return (
            explicitAdmin
            || senderType === 'admin'
            || senderType === 'agent'
            || senderType === 'staff'
            || senderType === 'support'
            || senderType === 'assistant'
            || senderName.includes('admin')
            || senderName.includes('support')
            || senderName.includes('medewerker')
        );
    };

    const getMessageId = function (message, index) {
        return String(
            message?.id
            ?? message?.uuid
            ?? message?.message_id
            ?? message?.messageId
            ?? message?.created_at
            ?? message?.createdAt
            ?? message?.timestamp
            ?? index
        );
    };

    const getServerMessageText = function (message) {
        return normalizeText(
            message?.message
            ?? message?.body
            ?? message?.text
            ?? message?.content
            ?? message?.content_text
            ?? message?.contentText
            ?? 'Je hebt een nieuw bericht ontvangen.'
        );
    };

    const getLatestAdminMessage = function (payload) {
        /*
         * LiveChatService::payload() retourneert:
         * {
         *   messages: [
         *      { id, sender: 'admin'|'visitor', body, ... }
         *   ]
         * }
         */
        const messages =
            getNestedMessages(payload);

        let latest = null;
        let latestIndex = -1;

        messages.forEach(function (message, index) {
            if (messageLooksLikeAdmin(message)) {
                latest = message;
                latestIndex = index;
            }
        });

        if (!latest) {
            return null;
        }

        return {
            message: latest,
            index: latestIndex,
            signature:
                getMessageId(latest, latestIndex)
                + '|'
                + getServerMessageText(latest)
        };
    };

    const openChatForAdminReply = function (messageText) {
        addUnread();
        showToast(messageText);
        playNotificationSound();
        showBrowserNotification(messageText);

        /*
         * CRUCIAAL:
         * De bestaande live-chat.js pollt alleen wanneer:
         *   chat.dataset.mode === 'human'
         *
         * Daarom openen we niet alleen het algemene AI-paneel,
         * maar schakelen we expliciet naar de bestaande human handoff.
         */
        try {
            root.dispatchEvent(
                new CustomEvent(
                    'live-chat:handoff',
                    {
                        detail: {
                            body: ''
                        }
                    }
                )
            );
        } catch (error) {
            /*
             * Fallback wanneer CustomEvent onverwacht niet beschikbaar is.
             */
            root.dataset.mode = 'human';
        }

        /*
         * guest-chat.js houdt open/dicht bij met panel.hidden,
         * data-open en aria-expanded. We zetten die drie bewust gelijk,
         * zodat de bezoeker NIET eerst zelf hoeft te klikken.
         */
        panel.hidden = false;
        root.dataset.open = 'true';
        toggle.setAttribute(
            'aria-expanded',
            'true'
        );

        /*
         * De live sectie direct zichtbaar maken.
         * activateHuman() uit live-chat.js doet dit ook, maar deze
         * fallback voorkomt timingproblemen tussen beide scripts.
         */
        if (livePanel) {
            livePanel.hidden = false;
        }

        const aiBody =
            root.querySelector('.gc-body');

        const aiBottom =
            root.querySelector('.gc-bottom');

        const resetButton =
            root.querySelector('.gc-reset');

        if (aiBody) {
            aiBody.hidden = true;
        }

        if (aiBottom) {
            aiBottom.hidden = true;
        }

        if (resetButton) {
            resetButton.hidden = true;
        }

        /*
         * Op mobiel/desktop meteen naar de nieuwe live-chat scrollpositie.
         */
        window.requestAnimationFrame(function () {
            const liveInput =
                root.querySelector('.lc-input');

            if (
                liveInput
                && !window.matchMedia(
                    '(max-width: 699px)'
                ).matches
            ) {
                liveInput.focus({
                    preventScroll: true
                });
            }
        });

        /*
         * Extra check na de native live-chat poll.
         */
        window.setTimeout(function () {
            if (typeof checkForNewLiveAdminMessage === 'function') {
                checkForNewLiveAdminMessage();
            }
        }, 450);
    };

    const pollLiveChatInBackground = async function () {
        if (
            !liveShowEndpoint
            || backgroundPollBusy
            || document.visibilityState === 'prerender'
        ) {
            return;
        }

        backgroundPollBusy = true;

        try {
            const response =
                await fetch(
                    liveShowEndpoint,
                    {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin',
                        cache: 'no-store'
                    }
                );

            if (!response.ok) {
                return;
            }

            const payload =
                await response.json();

            const latestAdmin =
                getLatestAdminMessage(payload);

            if (!latestAdmin) {
                serverBaselineReady = true;
                return;
            }

            if (!serverBaselineReady) {
                lastServerAdminSignature =
                    latestAdmin.signature;

                serverBaselineReady = true;
                return;
            }

            if (
                latestAdmin.signature
                !== lastServerAdminSignature
            ) {
                lastServerAdminSignature =
                    latestAdmin.signature;

                openChatForAdminReply(
                    getServerMessageText(
                        latestAdmin.message
                    )
                );
            }
        } catch (error) {
            /*
             * Geen console-spam / geen blokkade.
             * De volgende poll probeert opnieuw.
             */
        } finally {
            backgroundPollBusy = false;
        }
    };

    /*
     * Eerste request wordt baseline:
     * bestaande adminberichten openen de chat niet opnieuw.
     */
    window.setTimeout(
        pollLiveChatInBackground,
        900
    );

    /*
     * Ook met gesloten chat blijven controleren.
     * 3 seconden is snel genoeg voor support-chat zonder onnodige load.
     */
    window.setInterval(
        pollLiveChatInBackground,
        2000
    );

    /*
     * Zodra de bezoeker terugkomt op de tab meteen opnieuw controleren.
     */
    document.addEventListener(
        'visibilitychange',
        function () {
            if (!document.hidden) {
                pollLiveChatInBackground();
            }
        }
    );

});
</script>

<script src="{{ asset('js/guest-chat.js') }}?v=7" defer></script>



<script src="{{ asset('js/live-chat.js') }}?v=20" defer></script>

<script src="{{ asset('js/live-chat-calls.js') }}?v=5" defer></script>
