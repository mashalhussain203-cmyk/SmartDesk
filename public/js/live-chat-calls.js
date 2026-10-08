(() => {
    'use strict';

    if (window.__smartdeskLiveChatCallsLoaded) return;
    window.__smartdeskLiveChatCallsLoaded = true;

    const adminRoot = document.getElementById('admin-live-chat');
    const guestRoot = document.getElementById('guest-chat');
    const side = adminRoot ? 'admin' : (guestRoot ? 'visitor' : null);

    if (!side || !navigator.mediaDevices || !window.RTCPeerConnection) {
        return;
    }

    const root = side === 'admin' ? adminRoot : guestRoot;
    const POLL_MS = 1200;

    const state = {
        call: null,
        peer: null,
        localStream: null,
        remoteStream: null,
        isCaller: false,
        connectedAt: 0,
        pollTimer: null,
        timerInterval: null,
        iceServers: null,
        muted: false,
        cameraOff: false,
        facingMode: 'user',
        incomingShownId: null,
        busy: false,
        sound: { context: null, loopTimer: null, kind: null },
    };

    const css = `
.lcc-call-btn{display:inline-grid;place-items:center;width:42px;height:42px;border:1px solid rgba(255,255,255,.10);border-radius:12px;background:rgba(255,255,255,.045);color:#eaf0ff;cursor:pointer;transition:.18s ease;font-size:18px;line-height:1}.lcc-call-btn:hover:not(:disabled){background:rgba(122,108,255,.16);border-color:rgba(139,126,255,.5);transform:translateY(-1px)}.lcc-call-btn:disabled{opacity:.35;cursor:not-allowed}.lcc-overlay{position:fixed;inset:0;z-index:2147483000;display:grid;place-items:center;padding:20px;background:rgba(2,5,11,.82);backdrop-filter:blur(18px);font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}.lcc-card{position:relative;width:min(920px,100%);height:min(680px,calc(100dvh - 40px));overflow:hidden;border:1px solid rgba(255,255,255,.11);border-radius:28px;background:linear-gradient(145deg,#111728,#070a11 75%);box-shadow:0 35px 120px rgba(0,0,0,.62)}.lcc-stage{position:absolute;inset:0;display:grid;place-items:center;background:radial-gradient(circle at 50% 20%,rgba(122,108,255,.18),transparent 42%),#080b12}.lcc-remote{width:100%;height:100%;object-fit:cover;background:#05070b}.lcc-local{position:absolute;right:22px;bottom:112px;width:min(210px,28vw);aspect-ratio:3/4;object-fit:cover;border:1px solid rgba(255,255,255,.18);border-radius:20px;background:#111827;box-shadow:0 18px 50px rgba(0,0,0,.45);transform:scaleX(-1)}.lcc-audio-avatar{display:grid;place-items:center;width:128px;height:128px;border-radius:50%;background:linear-gradient(135deg,#786cff,#3e7bff);box-shadow:0 0 0 16px rgba(122,108,255,.08),0 0 0 32px rgba(122,108,255,.04);font-size:46px;font-weight:800;color:white}.lcc-top{position:absolute;left:0;right:0;top:0;z-index:3;display:flex;align-items:flex-start;justify-content:space-between;padding:24px;background:linear-gradient(180deg,rgba(0,0,0,.64),transparent)}.lcc-title{margin:0;color:#fff;font-size:18px;font-weight:750}.lcc-status{margin:6px 0 0;color:#bbc4d7;font-size:13px}.lcc-timer{min-width:76px;text-align:right;color:#fff;font-variant-numeric:tabular-nums;font-size:13px}.lcc-controls{position:absolute;left:50%;bottom:26px;z-index:4;display:flex;gap:12px;transform:translateX(-50%);padding:11px;border:1px solid rgba(255,255,255,.09);border-radius:22px;background:rgba(8,11,18,.72);backdrop-filter:blur(16px)}.lcc-control{display:grid;place-items:center;width:54px;height:54px;border:0;border-radius:18px;background:rgba(255,255,255,.09);color:white;font-size:21px;cursor:pointer}.lcc-control:hover{background:rgba(255,255,255,.15)}.lcc-control[data-active="false"]{background:rgba(239,68,68,.22);color:#fecaca}.lcc-control--end{background:#ef4444}.lcc-control--end:hover{background:#dc2626}.lcc-incoming{position:fixed;right:24px;bottom:24px;z-index:2147483001;width:min(390px,calc(100vw - 32px));padding:18px;border:1px solid rgba(255,255,255,.13);border-radius:22px;background:linear-gradient(145deg,#151b2a,#0a0e17);box-shadow:0 24px 80px rgba(0,0,0,.56);font-family:Inter,ui-sans-serif,system-ui,-apple-system,sans-serif;color:white}.lcc-incoming__head{display:flex;gap:13px;align-items:center}.lcc-incoming__icon{display:grid;place-items:center;width:50px;height:50px;border-radius:16px;background:rgba(122,108,255,.16);font-size:22px}.lcc-incoming strong{display:block;font-size:15px}.lcc-incoming p{margin:4px 0 0;color:#9eabc0;font-size:12px}.lcc-incoming__actions{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:16px}.lcc-incoming button{min-height:44px;border:0;border-radius:13px;font-weight:750;cursor:pointer}.lcc-accept{background:#22c55e;color:#04130a}.lcc-decline{background:#ef4444;color:white}.lcc-toast{position:fixed;left:50%;bottom:28px;z-index:2147483002;transform:translateX(-50%);padding:11px 16px;border-radius:13px;background:#111827;color:#f8fafc;box-shadow:0 12px 40px rgba(0,0,0,.45);font:600 13px/1.35 system-ui,sans-serif}.lcc-hidden{display:none!important}@media(max-width:700px){.lcc-overlay{padding:0}.lcc-card{width:100%;height:100dvh;border:0;border-radius:0}.lcc-local{right:14px;bottom:102px;width:120px;border-radius:16px}.lcc-controls{bottom:18px}.lcc-top{padding:18px}.lcc-incoming{right:16px;bottom:16px}}`;

    const style = document.createElement('style');
    style.textContent = css;
    document.head.append(style);

    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

    async function request(url, method = 'GET', data = undefined) {
        const options = {
            method,
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrf(),
            },
        };

        if (data !== undefined) {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(data);
        }

        const response = await fetch(url, options);
        let payload = null;
        try { payload = await response.json(); } catch {}

        if (!response.ok) {
            const error = new Error(payload?.message || `Oproepfout (${response.status})`);
            error.status = response.status;
            throw error;
        }

        return payload || {};
    }

    function selectedConversationId() {
        if (side !== 'admin') return null;
        const selected = root.querySelector('.lca-item[aria-pressed="true"]');
        const id = Number(selected?.dataset?.id || 0);
        return Number.isFinite(id) && id > 0 ? id : null;
    }

    function endpoints(conversationId = null, callId = null) {
        if (side === 'visitor') {
            return {
                config: '/live-chat/calls/config',
                current: '/live-chat/calls/current',
                start: '/live-chat/calls',
                answer: callId ? `/live-chat/calls/${callId}/answer` : null,
                decline: callId ? `/live-chat/calls/${callId}/decline` : null,
                end: callId ? `/live-chat/calls/${callId}/end` : null,
            };
        }

        const c = Number(conversationId || selectedConversationId() || 0);
        return {
            config: '/live-chat/calls/config',
            incoming: '/admin/live-chat/calls/incoming',
            current: c ? `/admin/live-chat/conversations/${c}/calls/current` : null,
            start: c ? `/admin/live-chat/conversations/${c}/calls` : null,
            answer: c && callId ? `/admin/live-chat/conversations/${c}/calls/${callId}/answer` : null,
            decline: c && callId ? `/admin/live-chat/conversations/${c}/calls/${callId}/decline` : null,
            end: c && callId ? `/admin/live-chat/conversations/${c}/calls/${callId}/end` : null,
        };
    }

    function toast(message) {
        const node = document.createElement('div');
        node.className = 'lcc-toast';
        node.textContent = message;
        document.body.append(node);
        window.setTimeout(() => node.remove(), 3200);
    }

    function audioContext() {
        const Ctx = window.AudioContext || window.webkitAudioContext;
        if (!Ctx) return null;
        if (!state.sound.context) state.sound.context = new Ctx();
        if (state.sound.context.state === 'suspended') {
            state.sound.context.resume().catch(() => {});
        }
        return state.sound.context;
    }

    function tone(frequency = 440, duration = 0.16, volume = 0.045, delay = 0) {
        const ctx = audioContext();
        if (!ctx) return;
        const start = ctx.currentTime + delay;
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(frequency, start);
        gain.gain.setValueAtTime(0.0001, start);
        gain.gain.exponentialRampToValueAtTime(Math.max(0.0001, volume), start + 0.015);
        gain.gain.exponentialRampToValueAtTime(0.0001, start + duration);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(start);
        osc.stop(start + duration + 0.03);
    }

    function stopCallSound() {
        if (state.sound.loopTimer) clearInterval(state.sound.loopTimer);
        state.sound.loopTimer = null;
        state.sound.kind = null;
    }

    function playIncomingRing() {
        if (state.sound.kind === 'incoming') return;
        stopCallSound();
        state.sound.kind = 'incoming';
        const ring = () => {
            tone(880, 0.16, 0.05, 0);
            tone(1046, 0.16, 0.05, 0.22);
            tone(880, 0.16, 0.05, 0.44);
        };
        ring();
        state.sound.loopTimer = setInterval(ring, 2600);
    }

    function playOutgoingRing() {
        if (state.sound.kind === 'outgoing') return;
        stopCallSound();
        state.sound.kind = 'outgoing';
        const ring = () => {
            tone(440, 0.42, 0.035, 0);
            tone(480, 0.42, 0.035, 0.48);
        };
        ring();
        state.sound.loopTimer = setInterval(ring, 3000);
    }

    function playConnectedSound() {
        stopCallSound();
        tone(660, 0.10, 0.035, 0);
        tone(880, 0.13, 0.035, 0.11);
    }

    function playEndedSound() {
        stopCallSound();
        tone(440, 0.12, 0.035, 0);
        tone(330, 0.16, 0.035, 0.13);
    }

    async function getIceServers() {
        if (state.iceServers) return state.iceServers;
        const data = await request(endpoints().config);
        state.iceServers = Array.isArray(data.ice_servers) ? data.ice_servers : [];
        return state.iceServers;
    }

    function normalizeDescription(description, expectedType = null) {
        if (!description || typeof description !== 'object') {
            throw new Error((window.smartDeskTranslate ? window.smartDeskTranslate("Ongeldige WebRTC session description ontvangen.") : "Ongeldige WebRTC session description ontvangen."));
        }

        const type = String(description.type || expectedType || '').trim();
        let sdp = typeof description.sdp === 'string' ? description.sdp : '';

        if (!type || !['offer', 'answer'].includes(type)) {
            throw new Error((window.smartDeskTranslate ? window.smartDeskTranslate("Ongeldig SDP-type ontvangen.") : "Ongeldig SDP-type ontvangen."));
        }

        // Soms komt SDP via JSON/database terug met letterlijke escaped newlines.
        // Zet die alleen om wanneer er geen echte regeleinden aanwezig zijn.
        if (!/[\r\n]/.test(sdp) && /\\r\\n|\\n/.test(sdp)) {
            sdp = sdp.replace(/\\r\\n/g, '\n').replace(/\\n/g, '\n');
        }

        // Verwijder BOM/NUL en normaliseer alle regeleinden naar CRLF, zoals SDP vereist.
        sdp = sdp
            .replace(/^\uFEFF/, '')
            .replace(/\u0000/g, '')
            .replace(/\r\n/g, '\n')
            .replace(/\r/g, '\n')
            .split('\n')
            .map(line => line.trimEnd())
            .join('\r\n')
            .trim();

        if (!sdp.startsWith('v=0')) {
            throw new Error('Ongeldige SDP ontvangen: eerste regel is geen v=0.');
        }

        if (!sdp.endsWith('\r\n')) {
            sdp += '\r\n';
        }

        return new RTCSessionDescription({ type, sdp });
    }

    function waitForIce(pc) {
        if (pc.iceGatheringState === 'complete') return Promise.resolve();
        return new Promise(resolve => {
            const timeout = setTimeout(resolve, 6500);
            const handler = () => {
                if (pc.iceGatheringState === 'complete') {
                    clearTimeout(timeout);
                    pc.removeEventListener('icegatheringstatechange', handler);
                    resolve();
                }
            };
            pc.addEventListener('icegatheringstatechange', handler);
        });
    }

    async function getMedia(mode, facingMode = 'user') {
        return navigator.mediaDevices.getUserMedia({
            audio: {
                echoCancellation: true,
                noiseSuppression: true,
                autoGainControl: true,
            },
            video: mode === 'video'
                ? {
                    facingMode,
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                }
                : false,
        });
    }

    async function createPeer(mode) {
        const pc = new RTCPeerConnection({ iceServers: await getIceServers() });
        const remoteStream = new MediaStream();
        state.remoteStream = remoteStream;

        pc.addEventListener('track', event => {
            for (const track of event.streams?.[0]?.getTracks?.() || [event.track]) {
                if (!remoteStream.getTracks().some(item => item.id === track.id)) {
                    remoteStream.addTrack(track);
                }
            }
            syncMediaElements();
            if (remoteVideo) {
                remoteVideo.muted = false;
                remoteVideo.volume = 1;
                remoteVideo.play().catch(() => {});
            }
        });

        pc.addEventListener('connectionstatechange', () => {
            if (pc.connectionState === 'connected') {
                state.connectedAt = state.connectedAt || Date.now();
                setStatus('Verbonden');
                playConnectedSound();
                startTimer();
            }

            if (['failed', 'closed'].includes(pc.connectionState)) {
                void finishCall(false, (window.smartDeskTranslate ? window.smartDeskTranslate("Verbinding beëindigd") : "Verbinding beëindigd"));
            }
        });

        state.peer = pc;
        return pc;
    }

    function addLocalTracks(pc, stream) {
        stream.getTracks().forEach(track => pc.addTrack(track, stream));
    }

    let overlay = null;
    let remoteVideo = null;
    let localVideo = null;
    let statusNode = null;
    let timerNode = null;
    let muteButton = null;
    let cameraButton = null;
    let switchButton = null;

    function ensureOverlay(mode) {
        if (overlay) return;

        overlay = document.createElement('div');
        overlay.className = 'lcc-overlay';
        overlay.innerHTML = `
            <section class="lcc-card" role="dialog" aria-modal="true" aria-label="Live oproep">
                <div class="lcc-stage">
                    <video class="lcc-remote" autoplay playsinline></video>
                    <div class="lcc-audio-avatar">${side === 'admin' ? 'U' : 'M'}</div>
                    <video class="lcc-local" autoplay playsinline muted></video>
                </div>
                <div class="lcc-top">
                    <div><h2 class="lcc-title">${mode === 'video' ? 'Videogesprek' : 'Audiogesprek'}</h2><p class="lcc-status">Verbinden…</p></div>
                    <div class="lcc-timer">00:00</div>
                </div>
                <div class="lcc-controls">
                    <button type="button" class="lcc-control" data-lcc-mute title="Microfoon">🎙</button>
                    <button type="button" class="lcc-control" data-lcc-camera title="Camera">📷</button>
                    <button type="button" class="lcc-control" data-lcc-switch title="Camera wisselen">🔄</button>
                    <button type="button" class="lcc-control lcc-control--end" data-lcc-end title="Ophangen">☎</button>
                </div>
            </section>`;
        document.body.append(overlay);

        remoteVideo = overlay.querySelector('.lcc-remote');
        localVideo = overlay.querySelector('.lcc-local');
        statusNode = overlay.querySelector('.lcc-status');
        timerNode = overlay.querySelector('.lcc-timer');
        muteButton = overlay.querySelector('[data-lcc-mute]');
        cameraButton = overlay.querySelector('[data-lcc-camera]');
        switchButton = overlay.querySelector('[data-lcc-switch]');

        const avatar = overlay.querySelector('.lcc-audio-avatar');
        if (mode !== 'video') {
            remoteVideo.classList.add('lcc-hidden');
            localVideo.classList.add('lcc-hidden');
            cameraButton.classList.add('lcc-hidden');
            switchButton.classList.add('lcc-hidden');
            avatar.classList.remove('lcc-hidden');
        } else {
            avatar.classList.add('lcc-hidden');
        }

        muteButton.addEventListener('click', toggleMute);
        cameraButton.addEventListener('click', toggleCamera);
        switchButton.addEventListener('click', () => void switchCamera());
        overlay.querySelector('[data-lcc-end]').addEventListener('click', () => void finishCall(true, 'Oproep beëindigd'));
        syncMediaElements();
    }

    function setStatus(text) {
        if (statusNode) statusNode.textContent = text;
    }

    function syncMediaElements() {
        if (localVideo && state.localStream && localVideo.srcObject !== state.localStream) {
            localVideo.srcObject = state.localStream;
        }
        if (remoteVideo && state.remoteStream && remoteVideo.srcObject !== state.remoteStream) {
            remoteVideo.srcObject = state.remoteStream;
            remoteVideo.muted = false;
            remoteVideo.volume = 1;
            remoteVideo.play().catch(() => {});
        }
    }

    function startTimer() {
        if (state.timerInterval) return;
        state.timerInterval = setInterval(() => {
            if (!timerNode || !state.connectedAt) return;
            const seconds = Math.max(0, Math.floor((Date.now() - state.connectedAt) / 1000));
            const mins = String(Math.floor(seconds / 60)).padStart(2, '0');
            const secs = String(seconds % 60).padStart(2, '0');
            timerNode.textContent = `${mins}:${secs}`;
        }, 1000);
    }

    function stopTimer() {
        if (state.timerInterval) clearInterval(state.timerInterval);
        state.timerInterval = null;
        state.connectedAt = 0;
    }

    function toggleMute() {
        state.muted = !state.muted;
        state.localStream?.getAudioTracks().forEach(track => { track.enabled = !state.muted; });
        muteButton?.setAttribute('data-active', String(!state.muted));
        if (muteButton) muteButton.textContent = state.muted ? '🔇' : '🎙';
    }

    function toggleCamera() {
        state.cameraOff = !state.cameraOff;
        state.localStream?.getVideoTracks().forEach(track => { track.enabled = !state.cameraOff; });
        cameraButton?.setAttribute('data-active', String(!state.cameraOff));
        if (cameraButton) cameraButton.textContent = state.cameraOff ? '🚫' : '📷';
    }

    async function switchCamera() {
        if (state.call?.mode !== 'video' || !state.peer) return;
        state.facingMode = state.facingMode === 'user' ? 'environment' : 'user';
        try {
            const replacement = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: state.facingMode } },
                audio: false,
            });
            const newTrack = replacement.getVideoTracks()[0];
            const sender = state.peer.getSenders().find(item => item.track?.kind === 'video');
            if (sender && newTrack) await sender.replaceTrack(newTrack);
            state.localStream?.getVideoTracks().forEach(track => track.stop());
            const audioTracks = state.localStream?.getAudioTracks() || [];
            state.localStream = new MediaStream([...audioTracks, newTrack]);
            syncMediaElements();
        } catch {
            toast((window.smartDeskTranslate ? window.smartDeskTranslate("Camera wisselen is niet beschikbaar op dit apparaat.") : "Camera wisselen is niet beschikbaar op dit apparaat."));
        }
    }

    function cleanupMedia() {
        stopCallSound();
        try { state.peer?.close(); } catch {}
        state.peer = null;
        state.localStream?.getTracks().forEach(track => track.stop());
        state.remoteStream?.getTracks().forEach(track => track.stop());
        state.localStream = null;
        state.remoteStream = null;
        stopTimer();
        overlay?.remove();
        overlay = null;
        remoteVideo = localVideo = statusNode = timerNode = muteButton = cameraButton = switchButton = null;
    }

    async function startOutgoing(mode) {
        if (state.busy || state.call) return;
        const conversationId = side === 'admin' ? selectedConversationId() : null;
        if (side === 'admin' && !conversationId) {
            toast((window.smartDeskTranslate ? window.smartDeskTranslate("Selecteer eerst een gesprek.") : "Selecteer eerst een gesprek."));
            return;
        }

        state.busy = true;
        try {
            const stream = await getMedia(mode, state.facingMode);
            state.localStream = stream;
            const pc = await createPeer(mode);
            addLocalTracks(pc, stream);
            ensureOverlay(mode);
            setStatus('Bellen…');
            playOutgoingRing();

            const offer = await pc.createOffer();
            await pc.setLocalDescription(offer);
            await waitForIce(pc);

            const ep = endpoints(conversationId);
            const data = await request(ep.start, 'POST', {
                mode,
                offer: pc.localDescription.toJSON(),
            });

            state.call = data.call;
            state.call.conversation_id = Number(state.call.conversation_id || conversationId || 0);
            state.isCaller = true;
            beginActivePolling();
        } catch (error) {
            cleanupMedia();
            toast(error.name === 'NotAllowedError'
                ? (window.smartDeskTranslate ? window.smartDeskTranslate("Geef toegang tot microfoon/camera om te bellen.") : "Geef toegang tot microfoon/camera om te bellen.")
                : error.message || (window.smartDeskTranslate ? window.smartDeskTranslate("Oproep kon niet worden gestart.") : "Oproep kon niet worden gestart."));
        } finally {
            state.busy = false;
        }
    }

    async function acceptIncoming(call) {
        if (state.busy || state.call) return;
        state.busy = true;
        stopCallSound();
        removeIncoming();
        try {
            const stream = await getMedia(call.mode, state.facingMode);
            state.localStream = stream;
            state.call = call;
            state.isCaller = false;
            const pc = await createPeer(call.mode);
            await pc.setRemoteDescription(normalizeDescription(call.offer, 'offer'));
            addLocalTracks(pc, stream);
            ensureOverlay(call.mode);
            setStatus('Verbinden…');

            const answer = await pc.createAnswer();
            await pc.setLocalDescription(answer);
            await waitForIce(pc);

            const ep = endpoints(call.conversation_id, call.id);
            const data = await request(ep.answer, 'POST', {
                answer: pc.localDescription.toJSON(),
            });
            state.call = data.call || call;
            state.call.conversation_id = Number(state.call.conversation_id || call.conversation_id);
            beginActivePolling();
        } catch (error) {
            cleanupMedia();
            state.call = null;
            toast(error.name === 'NotAllowedError'
                ? (window.smartDeskTranslate ? window.smartDeskTranslate("Geef toegang tot microfoon/camera om op te nemen.") : "Geef toegang tot microfoon/camera om op te nemen.")
                : error.message || (window.smartDeskTranslate ? window.smartDeskTranslate("Oproep kon niet worden opgenomen.") : "Oproep kon niet worden opgenomen."));
        } finally {
            state.busy = false;
        }
    }

    async function declineIncoming(call) {
        stopCallSound();
        playEndedSound();
        removeIncoming();
        try {
            await request(endpoints(call.conversation_id, call.id).decline, 'POST', {});
        } catch {}
    }

    async function finishCall(sendEnd = true, message = '') {
        const call = state.call;
        if (sendEnd && call?.id) {
            try {
                await request(endpoints(call.conversation_id, call.id).end, 'POST', {});
            } catch {}
        }
        state.call = null;
        state.isCaller = false;
        cleanupMedia();
        playEndedSound();
        if (message) toast(message);
    }

    async function pollActive() {
        if (!state.call?.id) return;
        try {
            const ep = endpoints(state.call.conversation_id, state.call.id);
            if (!ep.current) return;
            const data = await request(ep.current);
            const call = data.call;

            if (!call || call.id !== state.call.id) {
                await finishCall(false, state.peer?.connectionState === 'connected' ? (window.smartDeskTranslate ? window.smartDeskTranslate("Oproep beëindigd") : "Oproep beëindigd") : (window.smartDeskTranslate ? window.smartDeskTranslate("Geen antwoord") : "Geen antwoord"));
                return;
            }

            state.call = call;

            if (state.isCaller && call.answer && state.peer && !state.peer.remoteDescription) {
                await state.peer.setRemoteDescription(normalizeDescription(call.answer, 'answer'));
                setStatus('Verbinden…');
            }

            if (call.status === 'accepted' && state.peer?.connectionState !== 'connected') {
                setStatus('Verbinden…');
            }
        } catch (error) {
            if (error.status === 401 || error.status === 403 || error.status === 419) {
                await finishCall(false, (window.smartDeskTranslate ? window.smartDeskTranslate("Sessie verlopen") : "Sessie verlopen"));
            }
        }
    }

    function beginActivePolling() {
        if (state.pollTimer) clearInterval(state.pollTimer);
        state.pollTimer = setInterval(() => void pollActive(), POLL_MS);
        void pollActive();
    }

    let incomingNode = null;

    function showIncoming(call) {
        if (!call?.id || state.call || state.incomingShownId === call.id) return;
        state.incomingShownId = call.id;
        incomingNode?.remove();
        incomingNode = document.createElement('section');
        incomingNode.className = 'lcc-incoming';
        const caller = side === 'admin' ? (call.caller_name || 'Bezoeker') : (window.smartDeskTranslate ? window.smartDeskTranslate("Mashal Support") : "Mashal Support");
        incomingNode.innerHTML = `
            <div class="lcc-incoming__head">
                <div class="lcc-incoming__icon">${call.mode === 'video' ? '🎥' : '📞'}</div>
                <div><strong>${escapeHtml(caller)} belt je</strong><p>${call.mode === 'video' ? (window.smartDeskTranslate ? window.smartDeskTranslate("Inkomend videogesprek") : "Inkomend videogesprek") : (window.smartDeskTranslate ? window.smartDeskTranslate("Inkomend audiogesprek") : "Inkomend audiogesprek")}</p></div>
            </div>
            <div class="lcc-incoming__actions">
                <button type="button" class="lcc-decline">Weigeren</button>
                <button type="button" class="lcc-accept">Opnemen</button>
            </div>`;
        document.body.append(incomingNode);
        playIncomingRing();
        incomingNode.querySelector('.lcc-accept').addEventListener('click', () => void acceptIncoming(call));
        incomingNode.querySelector('.lcc-decline').addEventListener('click', () => void declineIncoming(call));
    }

    function removeIncoming() {
        if (state.sound.kind === 'incoming') stopCallSound();
        incomingNode?.remove();
        incomingNode = null;
        state.incomingShownId = null;
    }

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>'"]/g, char => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;',
        })[char]);
    }

    async function pollIncoming() {
        if (state.call || state.busy || document.hidden) return;
        try {
            let data;
            if (side === 'admin') {
                data = await request(endpoints().incoming);
            } else {
                data = await request(endpoints().current);
            }

            const call = data.call;
            const incoming = call
                && call.status === 'ringing'
                && ((side === 'admin' && call.initiated_by === 'visitor')
                    || (side === 'visitor' && call.initiated_by === 'admin'));

            if (incoming) showIncoming(call);
            else if (incomingNode) removeIncoming();
        } catch {}
    }

    function installButtons() {
        if (side === 'admin') {
            const actions = root.querySelector('.lca-heading__actions');
            if (!actions || actions.querySelector('[data-lcc-audio]')) return;

            const audio = document.createElement('button');
            audio.type = 'button';
            audio.className = 'lcc-call-btn';
            audio.dataset.lccAudio = '';
            audio.title = 'Audiobellen';
            audio.setAttribute('aria-label', 'Audiobellen');
            audio.textContent = '📞';

            const video = document.createElement('button');
            video.type = 'button';
            video.className = 'lcc-call-btn';
            video.dataset.lccVideo = '';
            video.title = 'Videobellen';
            video.setAttribute('aria-label', 'Videobellen');
            video.textContent = '🎥';

            actions.prepend(video);
            actions.prepend(audio);
            audio.addEventListener('click', () => void startOutgoing('audio'));
            video.addEventListener('click', () => void startOutgoing('video'));

            const refresh = () => {
                const enabled = Boolean(selectedConversationId()) && !state.call;
                audio.disabled = !enabled;
                video.disabled = !enabled;
            };
            new MutationObserver(refresh).observe(root, { subtree: true, attributes: true, attributeFilter: ['aria-pressed'] });
            setInterval(refresh, 1000);
            refresh();
            return;
        }

        const actions = root.querySelector('.gc-actions');
        if (!actions || actions.querySelector('[data-lcc-audio]')) return;

        const audio = document.createElement('button');
        audio.type = 'button';
        audio.className = 'gc-action';
        audio.dataset.lccAudio = '';
        audio.title = (window.smartDeskTranslate ? window.smartDeskTranslate("Audiobellen met support") : "Audiobellen met support");
        audio.setAttribute('aria-label', (window.smartDeskTranslate ? window.smartDeskTranslate("Audiobellen met support") : "Audiobellen met support"));
        audio.textContent = '📞';

        const video = document.createElement('button');
        video.type = 'button';
        video.className = 'gc-action';
        video.dataset.lccVideo = '';
        video.title = (window.smartDeskTranslate ? window.smartDeskTranslate("Videobellen met support") : "Videobellen met support");
        video.setAttribute('aria-label', (window.smartDeskTranslate ? window.smartDeskTranslate("Videobellen met support") : "Videobellen met support"));
        video.textContent = '🎥';

        actions.prepend(video);
        actions.prepend(audio);
        audio.addEventListener('click', () => void startOutgoing('audio'));
        video.addEventListener('click', () => void startOutgoing('video'));

        const refresh = () => {
            const live = root.dataset.mode === 'live';
            audio.hidden = !live;
            video.hidden = !live;
            audio.disabled = !live || Boolean(state.call);
            video.disabled = !live || Boolean(state.call);
        };
        new MutationObserver(refresh).observe(root, { attributes: true, attributeFilter: ['data-mode'] });
        setInterval(refresh, 1000);
        refresh();
    }

    installButtons();
    setInterval(installButtons, 2500);
    setInterval(() => void pollIncoming(), POLL_MS);
    void pollIncoming();

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) void pollIncoming();
    });

    window.addEventListener('beforeunload', () => {
        stopCallSound();
        state.localStream?.getTracks().forEach(track => track.stop());
        try { state.peer?.close(); } catch {}
    });
})();
