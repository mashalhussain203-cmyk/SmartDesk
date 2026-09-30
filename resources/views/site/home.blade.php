:root {
    --bg: #0a0a0b;
    --surface: #101012;
    --surface-2: #151518;

    --line: rgba(255,255,255,.09);
    --line-strong: rgba(255,255,255,.15);

    --text: #f5f5f5;
    --muted: #8d8d96;
    --muted-2: #5d5d66;

    --accent: #7c6cff;
    --accent-2: #5b8cff;
    --accent-soft: rgba(124,108,255,.12);

    --success: #59d98e;

    --radius: 12px;
}


/* =========================================================
   PAGE
   ========================================================= */

.home-page {
    min-height: 100vh;
    overflow: hidden;

    color: var(--text);

    background:
        radial-gradient(
            circle at 70% 15%,
            rgba(92,78,255,.12),
            transparent 35rem
        ),
        #0a0a0b;
}

.home-page::before {
    content: "";
    position: absolute;
    inset: 0;

    pointer-events: none;

    background-image:
        linear-gradient(
            rgba(255,255,255,.025) 1px,
            transparent 1px
        ),
        linear-gradient(
            90deg,
            rgba(255,255,255,.025) 1px,
            transparent 1px
        );

    background-size: 64px 64px;

    mask-image:
        linear-gradient(
            to bottom,
            rgba(0,0,0,.7),
            transparent 65%
        );
}

.home-shell {
    position: relative;
    z-index: 2;

    width: min(calc(100% - 48px), 1320px);
    margin-inline: auto;
}


/* =========================================================
   KICKER
   ========================================================= */

.home-kicker {
    display: inline-flex;
    align-items: center;
    gap: 10px;

    color: #8d83ff;

    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .06em;

    text-transform: uppercase;
}

.home-kicker::before {
    content: "";

    width: 6px;
    height: 6px;

    border-radius: 2px;

    background: var(--accent);

    box-shadow:
        0 0 20px rgba(124,108,255,.75);
}


/* =========================================================
   HERO
   ========================================================= */

.hero {
    position: relative;

    min-height: 820px;

    display: flex;
    align-items: center;

    padding: 110px 0 120px;
}

.hero-grid {
    width: 100%;

    display: grid;
    grid-template-columns:
        minmax(0,.88fr)
        minmax(520px,1.12fr);

    gap: 80px;

    align-items: center;
}


/* =========================================================
   HERO COPY
   ========================================================= */

.hero-copy {
    max-width: 620px;
}

.hero-title {
    margin: 28px 0 0;

    max-width: 680px;

    color: #fff;

    font-size: clamp(60px,6.6vw,98px);
    line-height: .91;
    font-weight: 650;
    letter-spacing: -.075em;
}

.hero-title .soft {
    display: block;

    color: #85858d;
}

.hero-title .accent {
    display: block;

    color: #fff;
}

.hero-lead {
    max-width: 510px;

    margin: 32px 0 0;

    color: #9898a1;

    font-size: 16px;
    line-height: 1.75;
}


/* =========================================================
   BUTTONS
   ========================================================= */

.hero-actions {
    margin-top: 34px;

    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.home-button {
    min-height: 50px;

    padding: 0 20px;

    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;

    border: 1px solid var(--line);
    border-radius: 9px;

    color: #c7c7cd;
    background: #101012;

    text-decoration: none;

    font-size: 12px;
    font-weight: 600;

    transition:
        background .16s ease,
        border-color .16s ease,
        transform .16s ease;
}

.home-button:hover {
    transform: translateY(-1px);

    border-color: var(--line-strong);

    background: #151518;
}

.home-button.primary {
    border-color: transparent;

    color: #fff;

    background:
        linear-gradient(
            135deg,
            #7c6cff,
            #5b8cff
        );

    box-shadow:
        0 12px 35px
        rgba(87,75,255,.22);
}

.home-button.primary:hover {
    background:
        linear-gradient(
            135deg,
            #897bff,
            #6c99ff
        );
}


/* =========================================================
   TRUST
   ========================================================= */

.hero-trust {
    margin-top: 29px;

    display: flex;
    gap: 20px;

    flex-wrap: wrap;
}

.hero-trust-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;

    color: #74747d;

    font-family: ui-monospace, monospace;
    font-size: 10px;
    font-weight: 500;
}

.hero-trust-item::before {
    content: "";

    width: 5px;
    height: 5px;

    border-radius: 50%;

    background: var(--success);
}


/* =========================================================
   NOTE
   ========================================================= */

.hero-note {
    max-width: 510px;

    margin-top: 26px;
    padding: 0;

    display: flex;
    gap: 10px;

    border: 0;

    color: #61616b;
    background: transparent;

    font-size: 11px;
    line-height: 1.7;
}

.hero-note-mark {
    width: 20px;
    height: 20px;

    flex: 0 0 20px;

    display: grid;
    place-items: center;

    border: 1px solid var(--line);
    border-radius: 5px;

    color: #85858f;
    background: #101012;

    font-size: 9px;
}


/* =========================================================
   UPLOAD — APP WINDOW
   ========================================================= */

.upload-card {
    position: relative;

    padding: 0;

    border: 1px solid rgba(255,255,255,.11);
    border-radius: 14px;

    background: #101012;

    box-shadow:
        0 50px 120px rgba(0,0,0,.55),
        0 0 0 1px rgba(255,255,255,.02);
}

.upload-card::before {
    content: "";

    position: absolute;

    inset: -1px;

    z-index: -1;

    border-radius: 15px;

    background:
        linear-gradient(
            140deg,
            rgba(124,108,255,.4),
            transparent 25%,
            transparent 70%,
            rgba(91,140,255,.18)
        );
}

.upload-window {
    overflow: hidden;

    border: 0;
    border-radius: inherit;

    background: #0d0d0f;
}


/* =========================================================
   WINDOW BAR
   ========================================================= */

.upload-window-bar {
    min-height: 52px;

    padding: 0 16px;

    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: center;

    border-bottom: 1px solid var(--line);

    background: #121214;
}

.window-dots {
    gap: 5px;
}

.window-dot {
    width: 7px;
    height: 7px;

    background: #39393f;
}

.window-dot:first-child {
    background: #776cff;
}

.window-title {
    color: #55555f;

    font-family: ui-monospace, monospace;
    font-size: 9px;
    font-weight: 500;
    letter-spacing: .06em;
}

.window-secure {
    color: #64646d;

    font-family: ui-monospace, monospace;
    font-size: 9px;
}

.window-secure::before {
    background: var(--success);
}


/* =========================================================
   DROPZONE
   ========================================================= */

.upload-window-body {
    padding: 14px;
}

.dropzone {
    position: relative;

    min-height: 470px;

    padding: 40px;

    display: grid;
    place-items: center;

    overflow: hidden;

    border: 1px dashed rgba(124,108,255,.35);
    border-radius: 10px;

    background:
        radial-gradient(
            circle at 50% 30%,
            rgba(124,108,255,.1),
            transparent 16rem
        ),
        #0b0b0d;

    cursor: pointer;

    transition:
        border-color .18s ease,
        background .18s ease;
}

.dropzone::before {
    content: "";

    position: absolute;
    inset: 0;

    opacity: .35;

    background-image:
        linear-gradient(
            rgba(255,255,255,.03) 1px,
            transparent 1px
        ),
        linear-gradient(
            90deg,
            rgba(255,255,255,.03) 1px,
            transparent 1px
        );

    background-size: 28px 28px;

    mask-image:
        radial-gradient(
            circle at center,
            black,
            transparent 70%
        );
}

.dropzone:hover,
.dropzone.is-dragging {
    transform: none;

    border-color: rgba(124,108,255,.8);

    background:
        radial-gradient(
            circle at 50% 30%,
            rgba(124,108,255,.16),
            transparent 18rem
        ),
        #0c0c0f;
}

.upload-icon {
    width: 64px;
    height: 64px;

    margin-bottom: 22px;

    border: 1px solid rgba(124,108,255,.28);
    border-radius: 12px;

    color: #a9a2ff;

    background:
        rgba(124,108,255,.09);

    box-shadow:
        0 0 50px rgba(124,108,255,.08);

    font-size: 22px;
}

.dropzone h2 {
    color: #f3f3f5;

    font-size: 27px;
    font-weight: 600;

    letter-spacing: -.045em;
}

.dropzone p {
    max-width: 350px;

    margin-top: 10px;

    color: #72727c;

    font-size: 12px;
    line-height: 1.7;
}

.upload-choose {
    min-height: 46px;

    margin-top: 24px;

    padding: 0 18px;

    border: 1px solid rgba(124,108,255,.35);
    border-radius: 8px;

    color: #fff;

    background:
        linear-gradient(
            135deg,
            #7164f3,
            #537fe9
        );

    box-shadow:
        0 14px 35px rgba(80,67,220,.19);

    font-size: 11px;
    font-weight: 600;
}

.upload-formats {
    margin-top: 18px;
}

.format-pill {
    min-height: 25px;

    padding: 0 8px;

    border: 1px solid var(--line);
    border-radius: 5px;

    color: #575761;
    background: #0e0e11;

    font-family: ui-monospace, monospace;
    font-size: 8px;
    font-weight: 500;
}


/* =========================================================
   FOOTER INSIDE UPLOAD
   ========================================================= */

.upload-foot {
    padding: 14px 3px 1px;

    color: #4e4e57;

    font-family: ui-monospace, monospace;
    font-size: 9px;
}


/* =========================================================
   FEATURE RAIL
   ========================================================= */

.feature-rail {
    border-top: 1px solid var(--line);
    border-bottom: 1px solid var(--line);

    background: #0c0c0e;
}

.rail-grid {
    grid-template-columns: repeat(4,1fr);
}

.rail-item {
    min-height: 100px;

    padding: 25px;

    gap: 13px;
}

.rail-item + .rail-item {
    border-left: 1px solid var(--line);
}

.rail-icon {
    width: 36px;
    height: 36px;

    flex: 0 0 36px;

    border: 1px solid var(--line);
    border-radius: 8px;

    color: #9087ff;

    background: #111114;
}

.rail-item strong {
    color: #d4d4d8;

    font-size: 11px;
    font-weight: 600;
}

.rail-item span {
    color: #606069;

    font-size: 9px;
}


/* =========================================================
   SECTIONS
   ========================================================= */

.home-section {
    padding: 130px 0;
}

.home-section.alt {
    border-color: var(--line);

    background:
        #0c0c0e;
}

.section-head {
    max-width: 760px;

    margin-bottom: 60px;
}

.section-head h2 {
    margin-top: 20px;

    font-size: clamp(46px,5vw,72px);
    font-weight: 600;
    line-height: .98;

    letter-spacing: -.065em;
}

.section-head p {
    margin-top: 20px;

    color: #777780;

    font-size: 14px;
    line-height: 1.75;
}


/* =========================================================
   TOOL GRID
   ========================================================= */

.tool-grid {
    display: grid;

    grid-template-columns:
        repeat(12,1fr);

    gap: 12px;
}

.tool-card {
    min-height: 235px;

    padding: 24px;

    border: 1px solid var(--line);
    border-radius: 10px;

    background:
        #0f0f12;

    box-shadow: none;
}

.tool-card:nth-child(1) {
    grid-column: span 6;
}

.tool-card:nth-child(2) {
    grid-column: span 3;
}

.tool-card:nth-child(3) {
    grid-column: span 3;
}

.tool-card:nth-child(4),
.tool-card:nth-child(5),
.tool-card:nth-child(6) {
    grid-column: span 4;
}

.tool-card.featured {
    grid-column: span 6;

    background:
        linear-gradient(
            135deg,
            rgba(124,108,255,.08),
            transparent
        ),
        #101013;
}

.tool-card:hover {
    transform: translateY(-2px);

    border-color:
        rgba(124,108,255,.3);

    box-shadow:
        0 20px 50px rgba(0,0,0,.22);
}

.tool-card::after {
    display: none;
}

.tool-index {
    color: #575760;

    font-family: ui-monospace, monospace;
    font-size: 9px;
}

.tool-status {
    top: 18px;
    right: 18px;

    border-color:
        rgba(89,217,142,.14);

    color: #70cf93;

    background:
        rgba(89,217,142,.05);

    font-family: ui-monospace, monospace;
}

.tool-icon {
    width: 42px;
    height: 42px;

    margin-top: 50px;

    border: 1px solid var(--line);
    border-radius: 8px;

    color: #9189ff;
    background: #141419;
}

.tool-card h3 {
    margin-top: 19px;

    color: #e5e5e7;

    font-size: 20px;
    font-weight: 600;
}

.tool-card p {
    margin-top: 8px;

    color: #6e6e77;

    font-size: 11px;
    line-height: 1.7;
}


/* =========================================================
   WORKFLOW
   ========================================================= */

.workflow-grid {
    grid-template-columns: .8fr 1.2fr;

    gap: 100px;
}

.workflow-copy h2 {
    margin-top: 20px;

    font-size: clamp(46px,4.8vw,66px);

    font-weight: 600;
}

.workflow-copy p {
    margin-top: 20px;

    color: #74747d;

    font-size: 13px;
    line-height: 1.8;
}

.workflow-item {
    padding:
        30px 0
        30px 72px;

    border-color: var(--line);
}

.workflow-number {
    top: 27px;

    width: 42px;
    height: 42px;

    border: 1px solid var(--line);
    border-radius: 8px;

    color: #9189ff;

    background: #111114;

    font-family: ui-monospace, monospace;
}

.workflow-item h3 {
    font-size: 18px;
    font-weight: 600;
}

.workflow-item p {
    margin-top: 7px;

    color: #6b6b74;

    font-size: 11px;
}


/* =========================================================
   WORKSPACE
   ========================================================= */

.workspace-panel {
    padding: 56px;

    border: 1px solid var(--line);
    border-radius: 14px;

    background: #0f0f12;

    box-shadow:
        0 40px 100px rgba(0,0,0,.3);
}

.workspace-panel::before {
    display: none;
}

.workspace-copy h2 {
    margin-top: 20px;

    font-size: clamp(46px,4.6vw,65px);

    font-weight: 600;
}

.workspace-copy p {
    margin-top: 20px;

    color: #777780;

    font-size: 13px;
    line-height: 1.75;
}

.library-window {
    border-radius: 10px;

    background: #09090b;

    transform: rotate(0deg);

    box-shadow:
        0 30px 80px rgba(0,0,0,.45);
}

.library-thumb {
    border-radius: 5px;
}


/* =========================================================
   SECURITY
   ========================================================= */

.security-grid {
    margin-top: 12px;

    gap: 12px;
}

.security-card {
    min-height: 180px;

    padding: 23px;

    border: 1px solid var(--line);
    border-radius: 10px;

    background: #0f0f12;
}

.security-icon {
    border-radius: 7px;

    color: #938aff;

    background: #141419;
}

.security-card h3 {
    font-size: 14px;
}

.security-card p {
    color: #686871;

    font-size: 10px;
}


/* =========================================================
   FAQ
   ========================================================= */

.faq-wrap {
    gap: 100px;
}

.faq-side h2 {
    margin-top: 20px;

    font-size: clamp(44px,4.6vw,64px);

    font-weight: 600;
}

.faq-side p {
    font-size: 12px;
}

.faq-list {
    gap: 7px;
}

.faq-item {
    border: 1px solid var(--line);
    border-radius: 8px;

    background: #0f0f12;
}

.faq-question {
    min-height: 64px;

    padding: 0 18px;

    font-size: 12px;
    font-weight: 500;
}

.faq-icon {
    border-radius: 5px;

    background: #141419;
}

.faq-icon::before,
.faq-icon::after {
    background: #9189ff;
}

.faq-answer-inner {
    color: #6e6e77;

    font-size: 11px;
}


/* =========================================================
   FINAL
   ========================================================= */

.final-panel {
    min-height: 390px;

    padding: 60px;

    border: 1px solid var(--line);
    border-radius: 14px;

    background:
        radial-gradient(
            circle at 80% 20%,
            rgba(124,108,255,.12),
            transparent 20rem
        ),
        #101013;

    box-shadow:
        0 40px 100px rgba(0,0,0,.3);
}

.final-panel::before {
    content: "M";

    color: rgba(255,255,255,.018);
}

.final-copy h2 {
    font-size: clamp(48px,5.6vw,78px);

    font-weight: 600;
}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 1120px) {

    .hero-grid {
        grid-template-columns: 1fr;

        gap: 60px;
    }

    .upload-card {
        max-width: 780px;
    }

    .tool-grid {
        grid-template-columns: repeat(2,1fr);
    }

    .tool-card,
    .tool-card.featured,
    .tool-card:nth-child(n) {
        grid-column: auto;
    }
}


@media (max-width: 640px) {

    .home-shell {
        width: calc(100% - 26px);
    }

    .hero {
        padding:
            60px 0
            80px;
    }

    .hero-title {
        font-size: clamp(50px,15vw,67px);
    }

    .hero-lead {
        font-size: 14px;
    }

    .hero-actions {
        flex-direction: column;
    }

    .home-button {
        width: 100%;
    }

    .upload-card {
        border-radius: 11px;
    }

    .upload-window {
        border-radius: 10px;
    }

    .dropzone {
        min-height: 390px;

        padding: 24px 16px;
    }

    .home-section {
        padding: 90px 0;
    }

    .tool-grid {
        grid-template-columns: 1fr;
    }

    .workspace-panel {
        padding: 28px 20px;
    }

    .final-panel {
        padding: 45px 22px;
    }
}