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
    const POLL_MS = side === 'visitor' ? 5000 : 2000;

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
        localVideoActive: false,
        remoteVideoActive: false,
        facingMode: 'user',
        incomingShownId: null,
        busy: false,
        sound: { context: null, loopTimer: null, kind: null },
        mediaSyncTimer: null,
        mutedAudioTrack: null,
        muting: false,
        agentOnline: false,
        conversationAvailable: false,
    };

    const css = `
.lcc-call-btn{display:inline-grid;place-items:center;width:42px;height:42px;border:1px solid rgba(255,255,255,.10);border-radius:12px;background:rgba(255,255,255,.045);color:#eaf0ff;cursor:pointer;transition:.18s ease;font-size:18px;line-height:1}.lcc-call-btn:hover:not(:disabled){background:rgba(122,108,255,.16);border-color:rgba(139,126,255,.5);transform:translateY(-1px)}.lcc-call-btn:disabled{opacity:.35;cursor:not-allowed}.lcc-overlay{position:fixed;inset:0;z-index:2147483000;display:grid;place-items:center;padding:20px;background:rgba(2,5,11,.82);backdrop-filter:blur(18px);font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}.lcc-card{position:relative;width:min(920px,100%);height:min(680px,calc(100dvh - 40px));overflow:hidden;border:1px solid rgba(255,255,255,.11);border-radius:28px;background:linear-gradient(145deg,#111728,#070a11 75%);box-shadow:0 35px 120px rgba(0,0,0,.62)}.lcc-stage{position:absolute;inset:0;display:grid;place-items:center;background:radial-gradient(circle at 50% 20%,rgba(122,108,255,.18),transparent 42%),#080b12}.lcc-remote{width:100%;height:100%;object-fit:cover;background:#05070b}.lcc-local{position:absolute;right:22px;bottom:112px;width:min(210px,28vw);aspect-ratio:3/4;object-fit:cover;border:1px solid rgba(255,255,255,.18);border-radius:20px;background:#111827;box-shadow:0 18px 50px rgba(0,0,0,.45);transform:scaleX(-1)}.lcc-audio-avatar{display:grid;place-items:center;width:128px;height:128px;border-radius:50%;background:linear-gradient(135deg,#786cff,#3e7bff);box-shadow:0 0 0 16px rgba(122,108,255,.08),0 0 0 32px rgba(122,108,255,.04);font-size:46px;font-weight:800;color:white}.lcc-top{position:absolute;left:0;right:0;top:0;z-index:3;display:flex;align-items:flex-start;justify-content:space-between;padding:24px;background:linear-gradient(180deg,rgba(0,0,0,.64),transparent)}.lcc-title{margin:0;color:#fff;font-size:18px;font-weight:750}.lcc-status{margin:6px 0 0;color:#bbc4d7;font-size:13px}.lcc-timer{min-width:76px;text-align:right;color:#fff;font-variant-numeric:tabular-nums;font-size:13px}.lcc-controls{position:absolute;left:50%;bottom:26px;z-index:4;display:flex;gap:12px;transform:translateX(-50%);padding:11px;border:1px solid rgba(255,255,255,.09);border-radius:22px;background:rgba(8,11,18,.72);backdrop-filter:blur(16px)}.lcc-control{display:grid;place-items:center;width:54px;height:54px;border:0;border-radius:18px;background:rgba(255,255,255,.09);color:white;font-size:21px;cursor:pointer}.lcc-control:hover{background:rgba(255,255,255,.15)}.lcc-control[data-active="false"]{background:rgba(239,68,68,.22);color:#fecaca}.lcc-control--end{background:#ef4444}.lcc-control--end:hover{background:#dc2626}.lcc-incoming{position:fixed;right:24px;bottom:24px;z-index:2147483001;width:min(390px,calc(100vw - 32px));padding:18px;border:1px solid rgba(255,255,255,.13);border-radius:22px;background:linear-gradient(145deg,#151b2a,#0a0e17);box-shadow:0 24px 80px rgba(0,0,0,.56);font-family:Inter,ui-sans-serif,system-ui,-apple-system,sans-serif;color:white}.lcc-incoming__head{display:flex;gap:13px;align-items:center}.lcc-incoming__icon{display:grid;place-items:center;width:50px;height:50px;border-radius:16px;background:rgba(122,108,255,.16);font-size:22px}.lcc-incoming strong{display:block;font-size:15px}.lcc-incoming p{margin:4px 0 0;color:#9eabc0;font-size:12px}.lcc-incoming__actions{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:16px}.lcc-incoming button{min-height:44px;border:0;border-radius:13px;font-weight:750;cursor:pointer}.lcc-accept{background:#22c55e;color:#04130a}.lcc-decline{background:#ef4444;color:white}.lcc-toast{position:fixed;left:50%;bottom:28px;z-index:2147483002;transform:translateX(-50%);padding:11px 16px;border-radius:13px;background:#111827;color:#f8fafc;box-shadow:0 12px 40px rgba(0,0,0,.45);font:600 13px/1.35 system-ui,sans-serif}.lcc-hidden{display:none!important}@media(max-width:700px){.lcc-overlay{padding:0}.lcc-card{width:100%;height:100dvh;border:0;border-radius:0}.lcc-local{right:14px;bottom:calc(112px + env(safe-area-inset-bottom));width:120px;border-radius:16px}.lcc-controls{left:12px;right:12px;bottom:calc(14px + env(safe-area-inset-bottom));transform:none;width:auto;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;padding:9px}.lcc-control{width:100%;min-width:0;height:52px;border-radius:16px}.lcc-control[data-lcc-camera],.lcc-control[data-lcc-mute]{display:grid!important;visibility:visible!important;opacity:1}.lcc-top{padding:calc(18px + env(safe-area-inset-top)) 18px 18px}.lcc-incoming{right:16px;bottom:calc(16px + env(safe-area-inset-bottom))}.lcc-visitor-call-dock{position:relative;z-index:30;display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:10px 14px 8px;padding:8px;border:1px solid rgba(255,255,255,.09);border-radius:18px;background:rgba(10,14,23,.92);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);box-shadow:0 10px 28px rgba(0,0,0,.28)}.lcc-visitor-call-dock[hidden]{display:none!important}.lcc-visitor-call-dock .lcc-mobile-call{min-height:48px;border:0;border-radius:14px;font:700 14px/1 system-ui,-apple-system,sans-serif;color:#fff;background:rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;gap:8px}.lcc-visitor-call-dock .lcc-mobile-call:disabled{opacity:.4}.lcc-visitor-call-dock .lcc-mobile-call--video{background:rgba(90,100,255,.18)}}`;

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

    function bytesToBase64(text) {
        const bytes = new TextEncoder().encode(String(text || ''));
        let binary = '';
        const chunk = 0x8000;
        for (let i = 0; i < bytes.length; i += chunk) {
            binary += String.fromCharCode(...bytes.subarray(i, i + chunk));
        }
        return btoa(binary);
    }

    function base64ToText(value) {
        const binary = atob(String(value || ''));
        const bytes = new Uint8Array(binary.length);
        for (let i = 0; i < binary.length; i += 1) bytes[i] = binary.charCodeAt(i);
        return new TextDecoder().decode(bytes);
    }

    function packDescription(description, expectedType) {
        const type = String(description?.type || expectedType || '').trim();
        const sdp = typeof description?.sdp === 'string' ? description.sdp : '';
        if (type !== expectedType || !sdp.startsWith('v=0')) {
            throw new Error(`Ongeldige ${expectedType} SDP.`);
        }
        return { type, sdp_b64: bytesToBase64(sdp) };
    }

    function normalizeDescription(description, expectedType = null) {
        if (!description || typeof description !== 'object') {
            throw new Error('Ongeldige WebRTC session description ontvangen.');
        }

        const type = String(description.type || expectedType || '').trim();
        let sdp = '';

        if (typeof description.sdp_b64 === 'string' && description.sdp_b64) {
            try {
                sdp = base64ToText(description.sdp_b64);
            } catch {
                throw new Error('SDP base64 kon niet worden gelezen.');
            }
        } else if (typeof description.sdp === 'string') {
            // Alleen voor oude records / oudere browserscripts.
            sdp = description.sdp;
            if (!/[\r\n]/.test(sdp) && /\\r\\n|\\n/.test(sdp)) {
                sdp = sdp.replace(/\\r\\n/g, '\r\n').replace(/\\n/g, '\r\n');
            }
        }

        sdp = sdp.replace(/^\uFEFF/, '').replace(/\u0000/g, '');

        if (!['offer', 'answer'].includes(type)) {
            throw new Error('Ongeldig SDP-type ontvangen.');
        }
        if (!sdp.startsWith('v=0')) {
            throw new Error('Ongeldige SDP ontvangen: eerste regel is geen v=0.');
        }

        return new RTCSessionDescription({ type, sdp });
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
            const tracks = event.streams?.[0]?.getTracks?.() || [event.track];
            for (const track of tracks) {
                if (!remoteStream.getTracks().some(item => item.id === track.id)) {
                    remoteStream.addTrack(track);
                }

                if (track.kind === 'video') {
                    const showRemoteVideo = () => {
                        state.remoteVideoActive = true;
                        updateVideoUi();
                    };
                    const hideRemoteVideo = () => {
                        state.remoteVideoActive = false;
                        updateVideoUi();
                    };
                    track.addEventListener('unmute', showRemoteVideo);
                    track.addEventListener('mute', hideRemoteVideo);
                    track.addEventListener('ended', hideRemoteVideo);
                    if (!track.muted && track.readyState === 'live') showRemoteVideo();
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
                startMediaSync();
            }

            if (pc.connectionState === 'failed') {
                void finishCall(true, 'Verbinding mislukt');
            }
        });

        state.peer = pc;
        return pc;
    }

    function addLocalTracks(pc, stream) {
        stream.getTracks().forEach(track => pc.addTrack(track, stream));
    }

    async function waitForIce(pc, timeoutMs = 8000) {
        if (!pc || pc.signalingState === 'closed') return;
        if (pc.iceGatheringState === 'complete') return;

        await new Promise(resolve => {
            let finished = false;
            let timer = null;

            const cleanup = () => {
                if (timer) clearTimeout(timer);
                pc.removeEventListener('icegatheringstatechange', onStateChange);
                pc.removeEventListener('icecandidate', onIceCandidate);
            };

            const done = () => {
                if (finished) return;
                finished = true;
                cleanup();
                resolve();
            };

            const onStateChange = () => {
                if (pc.iceGatheringState === 'complete') done();
            };

            const onIceCandidate = event => {
                if (!event.candidate) done();
            };

            pc.addEventListener('icegatheringstatechange', onStateChange);
            pc.addEventListener('icecandidate', onIceCandidate);

            // Safari/iOS geeft niet altijd netjes een laatste null-candidate terug.
            // Een timeout voorkomt dat de call daardoor permanent blijft hangen.
            timer = setTimeout(done, timeoutMs);

            // Nogmaals controleren nadat listeners zijn gekoppeld om een race te vermijden.
            if (pc.iceGatheringState === 'complete') done();
        });
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
                    <button type="button" class="lcc-control lcc-control--end" data-lcc-end title="Ophangen">â</button>
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

        state.localVideoActive = mode === 'video' && Boolean(state.localStream?.getVideoTracks().length);
        state.cameraOff = !state.localVideoActive;
        updateVideoUi();

        muteButton.addEventListener('click', toggleMute);
        cameraButton.addEventListener('click', () => void toggleVideoMode());
        switchButton.addEventListener('click', () => void switchCamera());
        overlay.querySelector('[data-lcc-end]').addEventListener('click', () => void finishCall(true, 'Oproep beëindigd'));
        syncMediaElements();
        startMediaSync();
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

    function audioSender() {
        if (!state.peer) return null;
        return state.peer.getSenders?.().find(sender => sender.track?.kind === 'audio')
            || state.peer.getTransceivers?.().find(item => item.receiver?.track?.kind === 'audio')?.sender
            || null;
    }

    async function setSenderAudioActive(sender, active) {
        if (!sender?.getParameters || !sender?.setParameters) return;
        try {
            const parameters = sender.getParameters();
            if (!Array.isArray(parameters.encodings) || !parameters.encodings.length) return;
            parameters.encodings = parameters.encodings.map(encoding => ({ ...encoding, active }));
            await sender.setParameters(parameters);
        } catch (error) {
            console.debug('[LiveChatCall] audio sender active fallback', error?.name, error?.message);
        }
    }

    async function toggleMute() {
        if (state.muting) return;
        state.muting = true;
        if (muteButton) muteButton.disabled = true;

        const wantMuted = !state.muted;
        const sender = audioSender();
        const localTrack = state.localStream?.getAudioTracks?.()[0] || sender?.track || state.mutedAudioTrack || null;

        try {
            if (wantMuted) {
                state.mutedAudioTrack = localTrack || state.mutedAudioTrack;

                // iPhone Safari is betrouwbaarder als de sender tijdelijk geen
                // audiotrack verstuurt. We houden de track zelf levend zodat
                // unmute geen nieuwe microfoon-permissie nodig heeft.
                if (localTrack) localTrack.enabled = false;
                state.localStream?.getAudioTracks?.().forEach(track => { track.enabled = false; });
                await setSenderAudioActive(sender, false);
                if (sender?.replaceTrack) {
                    try { await sender.replaceTrack(null); } catch (error) {
                        console.warn('[LiveChatCall] mute replaceTrack(null) failed', error?.name, error?.message);
                    }
                }
            } else {
                const restoreTrack = state.mutedAudioTrack || state.localStream?.getAudioTracks?.()[0] || null;
                if (!restoreTrack || restoreTrack.readyState === 'ended') {
                    throw new Error('De microfoontrack is niet meer beschikbaar. Start de oproep opnieuw.');
                }

                restoreTrack.enabled = true;
                state.localStream?.getAudioTracks?.().forEach(track => { track.enabled = true; });
                if (sender?.replaceTrack) await sender.replaceTrack(restoreTrack);
                await setSenderAudioActive(sender, true);
            }

            state.muted = wantMuted;
            const enabled = !state.muted;
            muteButton?.setAttribute('data-active', String(enabled));
            muteButton?.setAttribute('aria-pressed', String(state.muted));
            if (muteButton) {
                muteButton.textContent = state.muted ? '🔇' : '🎙';
                muteButton.title = state.muted ? 'Microfoon inschakelen' : 'Microfoon dempen';
                muteButton.setAttribute('aria-label', muteButton.title);
            }
            toast(state.muted ? 'Microfoon gedempt' : 'Microfoon ingeschakeld');
            console.info('[LiveChatCall] mute state', { muted: state.muted, senderTrack: sender?.track?.kind || null });
        } catch (error) {
            console.error('[LiveChatCall] mute toggle failed', error);
            toast(error?.message || 'Microfoon kon niet worden gewijzigd.');
        } finally {
            state.muting = false;
            if (muteButton) muteButton.disabled = false;
        }
    }

    function syncRemoteReceivers() {
        if (!state.peer || !state.remoteStream) return;

        let hasLiveVideo = false;
        for (const receiver of state.peer.getReceivers?.() || []) {
            const track = receiver.track;
            if (!track || track.readyState === 'ended') continue;

            if (!state.remoteStream.getTracks().some(item => item.id === track.id)) {
                try { state.remoteStream.addTrack(track); } catch {}
            }

            if (track.kind === 'video') {
                // Safari/iOS vuurt bij replaceTrack niet altijd opnieuw een
                // `unmute` event af. Een live receiver betekent dat de video
                // wel beschikbaar is; laat het element daarom zien en spelen.
                hasLiveVideo = true;
            }
        }

        if (hasLiveVideo !== state.remoteVideoActive) {
            state.remoteVideoActive = hasLiveVideo;
            updateVideoUi();
        }

        syncMediaElements();
        if (remoteVideo && hasLiveVideo) {
            remoteVideo.playsInline = true;
            remoteVideo.autoplay = true;
            remoteVideo.muted = false;
            remoteVideo.volume = 1;
            remoteVideo.play().catch(() => {});
        }
    }

    function startMediaSync() {
        if (state.mediaSyncTimer) return;
        syncRemoteReceivers();
        state.mediaSyncTimer = window.setInterval(syncRemoteReceivers, 650);
    }

    function stopMediaSync() {
        if (state.mediaSyncTimer) window.clearInterval(state.mediaSyncTimer);
        state.mediaSyncTimer = null;
    }

    function updateVideoUi() {
        if (!overlay) return;
        const avatar = overlay.querySelector('.lcc-audio-avatar');
        const anyVideo = state.localVideoActive || state.remoteVideoActive;

        if (remoteVideo) remoteVideo.classList.toggle('lcc-hidden', !state.remoteVideoActive);
        if (localVideo) localVideo.classList.toggle('lcc-hidden', !state.localVideoActive);
        avatar?.classList.toggle('lcc-hidden', anyVideo);

        if (cameraButton) {
            cameraButton.classList.remove('lcc-hidden');
            cameraButton.setAttribute('data-active', String(state.localVideoActive));
            cameraButton.textContent = state.localVideoActive ? '📷' : '🎥';
            cameraButton.dataset.videoToggle = state.localVideoActive ? 'on' : 'off';
            cameraButton.title = state.localVideoActive ? 'Video uitzetten' : 'Overschakelen naar video';
            cameraButton.setAttribute('aria-label', cameraButton.title);
        }
        if (switchButton) {
            switchButton.classList.toggle('lcc-hidden', !state.localVideoActive);
        }

        const title = overlay.querySelector('.lcc-title');
        if (title) title.textContent = anyVideo ? 'Videogesprek' : 'Audiogesprek';
    }

    function videoSender() {
        if (!state.peer) return null;
        return state.peer.getSenders().find(sender => sender.track?.kind === 'video')
            || state.peer.getTransceivers().find(item => item.receiver?.track?.kind === 'video')?.sender
            || null;
    }

    async function getVideoOnlyStream(facingMode = 'user') {
        const attempts = [
            {
                audio: false,
                video: {
                    facingMode,
                    width: { ideal: 1280, max: 1920 },
                    height: { ideal: 720, max: 1080 },
                },
            },
            { audio: false, video: { facingMode } },
            { audio: false, video: true },
        ];

        let lastError = null;
        for (const constraints of attempts) {
            try {
                const stream = await navigator.mediaDevices.getUserMedia(constraints);
                if (stream.getVideoTracks().length) return stream;
                stream.getTracks().forEach(track => track.stop());
            } catch (error) {
                lastError = error;
                console.warn('[LiveChatCall] camera attempt failed', error?.name, error?.message);
                if (error?.name === 'NotAllowedError' || error?.name === 'SecurityError') throw error;
            }
        }

        throw lastError || new Error('Geen camera beschikbaar.');
    }

    async function playLocalPreview() {
        if (!localVideo || !state.localStream) return;
        localVideo.srcObject = state.localStream;
        localVideo.muted = true;
        localVideo.playsInline = true;
        try {
            await localVideo.play();
        } catch (error) {
            console.warn('[LiveChatCall] local preview play failed', error?.name, error?.message);
        }
    }

    async function enableVideo() {
        if (!state.peer || state.peer.signalingState === 'closed') return;
        let stream = null;
        try {
            stream = await getVideoOnlyStream(state.facingMode);
            const track = stream.getVideoTracks()[0];
            if (!track) throw new Error('Geen camera beschikbaar.');

            const sender = videoSender();
            if (!sender) {
                track.stop();
                throw new Error('Deze oproep is gestart vóór de video-switch update. Start een nieuwe audiocall.');
            }

            const transceiver = state.peer.getTransceivers().find(item => item.sender === sender);
            if (transceiver && transceiver.direction !== 'sendrecv') {
                try { transceiver.direction = 'sendrecv'; } catch {}
            }

            await sender.replaceTrack(track);
            window.setTimeout(syncRemoteReceivers, 120);
            window.setTimeout(syncRemoteReceivers, 700);

            state.localStream?.getVideoTracks().forEach(oldTrack => {
                if (oldTrack.id !== track.id) oldTrack.stop();
            });
            const audioTracks = state.localStream?.getAudioTracks() || [];
            state.localStream = new MediaStream([...audioTracks, track]);
            state.localVideoActive = true;
            state.cameraOff = false;
            syncMediaElements();
            updateVideoUi();
            await playLocalPreview();
            setStatus('Video ingeschakeld');
            console.info('[LiveChatCall] camera enabled', {
                facingMode: state.facingMode,
                readyState: track.readyState,
                enabled: track.enabled,
                muted: track.muted,
                settings: track.getSettings?.() || {},
            });
        } catch (error) {
            stream?.getTracks?.().forEach(track => track.stop());
            console.error('[LiveChatCall] enable video failed', error);
            toast(error.name === 'NotAllowedError'
                ? 'Camera is geblokkeerd. Sta cameratoegang toe in Safari en probeer opnieuw.'
                : error.name === 'NotFoundError'
                    ? 'Geen camera gevonden op dit apparaat.'
                    : error.name === 'NotReadableError'
                        ? 'De camera wordt al door een andere app of tab gebruikt.'
                        : error.message || 'Video kon niet worden ingeschakeld.');
        }
    }

    async function disableVideo() {
        const sender = videoSender();
        try {
            if (sender) await sender.replaceTrack(null);
        } catch {}
        state.localStream?.getVideoTracks().forEach(track => track.stop());
        const audioTracks = state.localStream?.getAudioTracks() || [];
        state.localStream = new MediaStream(audioTracks);
        state.localVideoActive = false;
        state.cameraOff = true;
        syncMediaElements();
        updateVideoUi();
        setStatus('Alleen audio');
    }

    async function toggleVideoMode() {
        if (state.busy || !state.call || !state.peer) return;
        state.busy = true;
        try {
            if (state.localVideoActive) await disableVideo();
            else await enableVideo();
        } finally {
            state.busy = false;
        }
    }

    async function switchCamera() {
        if (!state.localVideoActive || !state.peer) return;
        state.facingMode = state.facingMode === 'user' ? 'environment' : 'user';
        try {
            const replacement = await getVideoOnlyStream(state.facingMode);
            const newTrack = replacement.getVideoTracks()[0];
            const sender = state.peer.getSenders().find(item => item.track?.kind === 'video');
            if (sender && newTrack) await sender.replaceTrack(newTrack);
            state.localStream?.getVideoTracks().forEach(track => track.stop());
            const audioTracks = state.localStream?.getAudioTracks() || [];
            state.localStream = new MediaStream([...audioTracks, newTrack]);
            syncMediaElements();
            await playLocalPreview();
        } catch {
            toast('Camera wisselen is niet beschikbaar op dit apparaat.');
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
        state.localVideoActive = false;
        state.remoteVideoActive = false;
        state.cameraOff = false;
        stopTimer();
        stopMediaSync();
        overlay?.remove();
        overlay = null;
        remoteVideo = localVideo = statusNode = timerNode = muteButton = cameraButton = switchButton = null;
    }

    async function startOutgoing(mode) {
        if (state.busy || state.call) return;
        const conversationId = side === 'admin' ? selectedConversationId() : null;
        if (side === 'admin' && !conversationId) {
            toast('Selecteer eerst een gesprek.');
            return;
        }

        state.busy = true;
        try {
            const stream = await getMedia(mode, state.facingMode);
            state.localStream = stream;
            const pc = await createPeer(mode);
            // Reserveer bij een audiocall vanaf het begin een video m-line.
            // Daardoor kan later met replaceTrack() naar video worden geschakeld
            // zonder de call opnieuw te onderhandelen of opnieuw op te nemen.
            if (mode === 'audio') {
                pc.addTransceiver('video', { direction: 'sendrecv' });
            }
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
                offer: packDescription(pc.localDescription, 'offer'),
            });

            state.call = data.call;
            state.call.conversation_id = Number(state.call.conversation_id || conversationId || 0);
            state.isCaller = true;
            beginActivePolling();
        } catch (error) {
            cleanupMedia();
            toast(error.name === 'NotAllowedError'
                ? 'Geef toegang tot microfoon/camera om te bellen.'
                : error.message || 'Oproep kon niet worden gestart.');
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
            if (call.mode === 'audio') {
                const videoTransceiver = pc.getTransceivers().find(item => item.receiver?.track?.kind === 'video');
                if (videoTransceiver) videoTransceiver.direction = 'sendrecv';
            }
            addLocalTracks(pc, stream);
            ensureOverlay(call.mode);
            setStatus('Verbinden…');

            const answer = await pc.createAnswer();
            await pc.setLocalDescription(answer);
            await waitForIce(pc);

            const ep = endpoints(call.conversation_id, call.id);
            const data = await request(ep.answer, 'POST', {
                answer: packDescription(pc.localDescription, 'answer'),
            });
            state.call = data.call || call;
            state.call.conversation_id = Number(state.call.conversation_id || call.conversation_id);
            // iOS/Safari kan een reeds lopende incoming-poll pas na het tikken
            // op Opnemen afronden en daardoor de oude popup opnieuw tekenen.
            // Verwijder hem na succesvolle answer daarom nogmaals geforceerd.
            removeIncoming();
            beginActivePolling();
        } catch (error) {
            const failedCall = state.call || call;
            if (failedCall?.id) {
                try {
                    await request(endpoints(failedCall.conversation_id, failedCall.id).end, 'POST', {});
                } catch {}
            }
            cleanupMedia();
            state.call = null;
            state.isCaller = false;
            toast(error.name === 'NotAllowedError'
                ? 'Geef toegang tot microfoon/camera om op te nemen.'
                : error.message || 'Oproep kon niet worden opgenomen.');
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
                await finishCall(false, state.peer?.connectionState === 'connected' ? 'Oproep beëindigd' : 'Geen antwoord');
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
                await finishCall(false, 'Sessie verlopen');
                return;
            }
            await finishCall(true, error.message || 'Oproepverbinding mislukt');
        }
    }

    function beginActivePolling() {
        if (state.pollTimer) clearInterval(state.pollTimer);
        state.pollTimer = setInterval(() => void pollActive(), POLL_MS);
        void pollActive();
    }

    let incomingNode = null;

    function showIncoming(call) {
        if (!call?.id || state.busy || state.call || state.incomingShownId === call.id) return;
        state.incomingShownId = call.id;
        incomingNode?.remove();
        incomingNode = document.createElement('section');
        incomingNode.className = 'lcc-incoming';
        const caller = side === 'admin' ? (call.caller_name || 'Bezoeker') : 'Mashal Support';
        incomingNode.innerHTML = `
            <div class="lcc-incoming__head">
                <div class="lcc-incoming__icon">${call.mode === 'video' ? '🎥' : '📞'}</div>
                <div><strong>${escapeHtml(caller)} belt je</strong><p>${call.mode === 'video' ? 'Inkomend videogesprek' : 'Inkomend audiogesprek'}</p></div>
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

    function refreshVisitorCallButtons() {
        if (side !== 'visitor') return;

        const audioButtons = Array.from(root.querySelectorAll('[data-lcc-audio]'));
        const videoButtons = Array.from(root.querySelectorAll('[data-lcc-video]'));
        if (!audioButtons.length || !videoButtons.length) return;

        const livePanel = root.querySelector('.lc-panel');
        const humanChatVisible = Boolean(livePanel && !livePanel.hidden);
        const mode = String(root.dataset.mode || '').toLowerCase();
        const humanMode = mode === 'human' || mode === 'live';

        // Voor echte ingelogde gebruikers en gasten gebruiken we primair het
        // zichtbare medewerkerpaneel. data-mode blijft alleen een fallback.
        const available = (humanChatVisible || humanMode)
            && state.agentOnline
            && state.conversationAvailable;

        const dock = root.querySelector('[data-lcc-mobile-dock]');
        if (dock) dock.hidden = !available;

        audioButtons.forEach((audio) => {
            const inDock = Boolean(audio.closest('[data-lcc-mobile-dock]'));
            // De dock-knoppen zijn de hoofdknoppen voor de medewerkerchat op
            // telefoon én desktop. Headerknoppen houden we alleen als fallback.
            audio.hidden = inDock ? !available : available;
            audio.disabled = !available || Boolean(state.call) || state.busy;
            audio.title = state.agentOnline
                ? 'Bellen met beschikbare medewerker'
                : 'Er is momenteel geen medewerker beschikbaar';
        });

        videoButtons.forEach((video) => {
            const inDock = Boolean(video.closest('[data-lcc-mobile-dock]'));
            video.hidden = inDock ? !available : available;
            video.disabled = !available || Boolean(state.call) || state.busy;
            video.title = state.agentOnline
                ? 'Videobellen met beschikbare medewerker'
                : 'Er is momenteel geen medewerker beschikbaar';
        });
    }

    async function pollIncoming() {
        if (state.call || state.busy) return;
        try {
            let data;
            if (side === 'admin') {
                data = await request(endpoints().incoming);
            } else {
                data = await request(endpoints().current);
                state.agentOnline = Boolean(data.agent_online);
                state.conversationAvailable = Boolean(data.conversation_available);
                refreshVisitorCallButtons();
            }

            // Een request kan gestart zijn voordat de gebruiker op Opnemen tikte.
            // Als de call intussen wordt verwerkt, mag een stale response de
            // incoming UI niet opnieuw zichtbaar maken.
            if (state.busy || state.call) {
                if (incomingNode) removeIncoming();
                return;
            }

            const call = data.call;
            const incoming = call
                && call.status === 'ringing'
                && ((side === 'admin' && call.initiated_by === 'visitor')
                    || (side === 'visitor' && call.initiated_by === 'admin'));

            if (incoming) showIncoming(call);
            else if (incomingNode) removeIncoming();
        } catch (error) {
            // Laat pollingfouten zichtbaar zijn in DevTools; op iOS waren deze
            // eerder volledig stil, waardoor een 401/419/500 eruitzag alsof
            // er simpelweg geen inkomende oproep bestond.
            console.warn('[LiveChatCall] incoming poll failed', error);
        }
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
            setInterval(refresh, 3000);
            refresh();
            return;
        }

        const actions = root.querySelector('.gc-actions');
        if (!actions) return;

        if (!root.querySelector('[data-lcc-mobile-dock]')) {
            const dock = document.createElement('div');
            dock.className = 'lcc-visitor-call-dock';
            dock.dataset.lccMobileDock = '';
            dock.hidden = true;
            dock.innerHTML = `
                <button type="button" class="lcc-mobile-call" data-lcc-audio aria-label="Audiobellen met support"><span>📞</span><span>Bellen</span></button>
                <button type="button" class="lcc-mobile-call lcc-mobile-call--video" data-lcc-video aria-label="Videobellen met support"><span>🎥</span><span>Video</span></button>
            `;
            const livePanel = root.querySelector('.lc-panel');
            const liveForm = livePanel?.querySelector('.lc-form');
            if (livePanel && liveForm) {
                livePanel.insertBefore(dock, liveForm);
            } else {
                root.appendChild(dock);
            }
            dock.querySelector('[data-lcc-audio]').addEventListener('click', () => void startOutgoing('audio'));
            dock.querySelector('[data-lcc-video]').addEventListener('click', () => void startOutgoing('video'));
        }

        if (actions.querySelector('[data-lcc-audio]')) {
            refreshVisitorCallButtons();
            return;
        }

        const audio = document.createElement('button');
        audio.type = 'button';
        audio.className = 'gc-action';
        audio.dataset.lccAudio = '';
        audio.title = 'Audiobellen met support';
        audio.setAttribute('aria-label', 'Audiobellen met support');
        audio.textContent = '📞';

        const video = document.createElement('button');
        video.type = 'button';
        video.className = 'gc-action';
        video.dataset.lccVideo = '';
        video.title = 'Videobellen met support';
        video.setAttribute('aria-label', 'Videobellen met support');
        video.textContent = '🎥';

        actions.prepend(video);
        actions.prepend(audio);
        audio.addEventListener('click', () => void startOutgoing('audio'));
        video.addEventListener('click', () => void startOutgoing('video'));

        const refresh = () => refreshVisitorCallButtons();
        new MutationObserver(refresh).observe(root, {
            attributes: true,
            subtree: true,
            attributeFilter: ['data-mode', 'hidden'],
        });
        refresh();
    }

    // iOS/Safari laat WebAudio pas spelen nadat de bezoeker minimaal één
    // interactie met de pagina heeft gehad. Ontgrendel de AudioContext bij de
    // eerste tap zodat een inkomende ringtone daarna wel hoorbaar is.
    const unlockAudio = () => {
        const ctx = audioContext();
        if (ctx?.state === 'suspended') ctx.resume().catch(() => {});
    };
    document.addEventListener('touchstart', unlockAudio, { passive: true, once: true });
    document.addEventListener('pointerdown', unlockAudio, { passive: true, once: true });
    document.addEventListener('click', unlockAudio, { passive: true, once: true });

    window.addEventListener('resize', () => refreshVisitorCallButtons(), { passive: true });

    installButtons();
    window.setTimeout(installButtons, 800);
    window.setTimeout(installButtons, 2200);

    // Voorgrondpolling. iOS mag timers in de achtergrond pauzeren, maar zodra
    // Safari weer actief is controleren we direct opnieuw via de events hieronder.
    setInterval(() => void pollIncoming(), POLL_MS);
    void pollIncoming();

    const wakeIncomingPoll = () => {
        window.setTimeout(() => void pollIncoming(), 0);
        window.setTimeout(() => void pollIncoming(), 350);
    };

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) wakeIncomingPoll();
    });
    window.addEventListener('pageshow', wakeIncomingPoll);
    window.addEventListener('focus', wakeIncomingPoll);
    window.addEventListener('online', wakeIncomingPoll);

    window.addEventListener('beforeunload', () => {
        stopCallSound();
        state.localStream?.getTracks().forEach(track => track.stop());
        try { state.peer?.close(); } catch {}
    });
})();
