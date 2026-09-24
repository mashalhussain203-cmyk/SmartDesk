(() => {
    'use strict';

    function decode(value) {
        const base64 = value.replace(/-/g, '+').replace(/_/g, '/');
        return Uint8Array.from(atob(base64.padEnd(Math.ceil(base64.length / 4) * 4, '=')), c => c.charCodeAt(0));
    }

    function encode(buffer) {
        return btoa(Array.from(new Uint8Array(buffer), b => String.fromCharCode(b)).join(''))
            .replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
    }

    async function api(url, body, method = 'POST') {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        const response = await fetch(url, {
            method, credentials: 'same-origin', cache: 'no-store',
            headers: {Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token || ''},
            ...(method === 'GET' ? {} : {body: JSON.stringify(body || {})}),
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            if (response.status === 419) throw new Error('Je sessie is verlopen. Vernieuw de pagina en probeer opnieuw.');
            if (response.status === 429) throw new Error('Te veel pogingen. Wacht een minuut en probeer opnieuw.');
            if (response.status === 401) throw new Error('Log opnieuw in om je passkeys te beheren.');
            if (response.status >= 500) throw new Error('Passkeys zijn tijdelijk niet beschikbaar. Gebruik een andere inlogmethode.');
            throw new Error(Object.values(data.errors || {}).flat()[0] || data.message || 'Dit is niet gelukt. Probeer opnieuw.');
        }
        return data;
    }

    function message(error) {
        if (error.name === 'NotAllowedError' || error.name === 'AbortError') {
            return 'De aanvraag is geannuleerd of verlopen. Probeer opnieuw, of gebruik een andere inlogmethode.';
        }
        if (error.name === 'InvalidStateError') return 'Er staat al een passkey voor dit account op je apparaat.';
        if (error.name === 'SecurityError') return 'Passkeys werken alleen op het ingestelde beveiligde websiteadres.';
        if (error.name === 'NotSupportedError') return 'Dit apparaat kan hier geen passkey gebruiken. Kies een andere inlogmethode.';
        return error.message || 'Dit is niet gelukt. Probeer opnieuw.';
    }

    async function initialize(root) {
        const manage = root.dataset.mode === 'manage';
        const status = root.querySelector('[data-passkey-status]');
        const start = root.querySelector('[data-passkey-start]');
        const isIPhone = /iPhone/i.test(navigator.userAgent);
        const eligible = root.dataset.iphoneOnly !== 'true' || isIPhone;
        let supported = Boolean(window.isSecureContext && window.PublicKeyCredential && navigator.credentials);
        if (supported) {
            try {
                supported = await PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable();
            } catch {
                supported = false;
            }
        }

        async function refresh() {
            const {passkeys} = await api(root.dataset.list, null, 'GET');
            const list = root.querySelector('[data-passkey-list]');
            list.replaceChildren();
            if (!passkeys.length) {
                const empty = document.createElement('li');
                empty.textContent = 'Je hebt nog geen passkey ingesteld.';
                list.append(empty);
            }
            for (const key of passkeys) {
                const item = document.createElement('li');
                const label = document.createElement('span');
                label.textContent = key.name + (key.last_used_at
                    ? ' · laatst gebruikt ' + new Date(key.last_used_at).toLocaleDateString('nl-NL') : ' · nog niet gebruikt');
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.textContent = 'Verwijderen';
                remove.setAttribute('aria-label', 'Verwijder passkey ' + key.name);
                remove.addEventListener('click', async () => {
                    if (!window.confirm('Passkey verwijderen? Je kunt daarna nog inloggen met je andere inlogmethoden.')) return;
                    remove.disabled = true;
                    try {
                        const result = await api(root.dataset.list + '/' + key.id, {}, 'DELETE');
                        status.textContent = result.message;
                        await refresh();
                    } catch (error) {
                        status.textContent = message(error);
                        remove.disabled = false;
                    }
                });
                item.append(label, remove);
                list.append(item);
            }
        }

        if (manage) {
            root.querySelector('[data-passkey-enroll]').hidden = !(eligible && supported);
            const note = root.querySelector('[data-passkey-device-note]');
            note.hidden = eligible && supported;
            if (eligible && !supported) note.textContent = 'Open deze pagina via HTTPS in een browser met passkey-ondersteuning.';
            try { await refresh(); } catch (error) { status.textContent = message(error); }
        } else {
            root.hidden = !(eligible && supported);
        }

        start.addEventListener('click', async () => {
            if (start.disabled) return;
            start.disabled = true;
            status.textContent = 'Bevestig de aanvraag op je apparaat…';
            try {
                const name = manage ? root.querySelector('[data-passkey-name]').value.trim() : '';
                if (manage && !name) throw new Error('Geef je passkey een naam.');
                const {publicKey} = await api(root.dataset.options);
                publicKey.challenge = decode(publicKey.challenge);
                if (manage) {
                    publicKey.user.id = decode(publicKey.user.id);
                    publicKey.excludeCredentials = (publicKey.excludeCredentials || []).map(key => ({...key, id: decode(key.id)}));
                }
                const credential = manage
                    ? await navigator.credentials.create({publicKey})
                    : await navigator.credentials.get({publicKey});
                if (!credential) throw new Error('Er is geen passkey geselecteerd.');
                const response = {clientDataJSON: encode(credential.response.clientDataJSON)};
                if (manage) {
                    response.attestationObject = encode(credential.response.attestationObject);
                } else {
                    response.authenticatorData = encode(credential.response.authenticatorData);
                    response.signature = encode(credential.response.signature);
                    response.userHandle = credential.response.userHandle ? encode(credential.response.userHandle) : null;
                }
                const result = await api(root.dataset.verify, {id: encode(credential.rawId), type: credential.type, name, response});
                if (manage) {
                    status.textContent = result.message;
                    await refresh();
                } else {
                    status.textContent = 'Je bent ingelogd. Je wordt doorgestuurd…';
                    window.location.assign(result.redirect);
                }
            } catch (error) {
                status.textContent = message(error);
            } finally {
                start.disabled = false;
            }
        });
    }

    const boot = () => document.querySelectorAll('[data-passkeys]').forEach(root => {
        initialize(root).catch(error => {
            root.querySelector('[data-passkey-status]').textContent = message(error);
        });
    });
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();
})();
